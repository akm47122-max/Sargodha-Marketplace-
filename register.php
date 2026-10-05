<?php
/**
 * Sargodha Mandi - User Registration
 * Free Account Creation
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect('/user/dashboard.php');
}

$db = getDB();
$errors = [];
$name = '';
$mobile = '';
$email = '';
$city = 'Sargodha';
$area = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();

    $name = trim($_POST['full_name'] ?? '');
    $mobile = trim($_POST['mobile_number'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    $city = trim($_POST['city'] ?? 'Sargodha');
    $area = trim($_POST['area'] ?? '');
    $agreeTerms = !empty($_POST['agree_terms']);

    // Validation
    if (empty($name) || strlen($name) < 3) {
        $errors[] = 'Full Name must be at least 3 characters.';
    }

    // Clean mobile number (accepts 03001234567 or 0300-1234567)
    $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
    if (!preg_match('/^03[0-9]{9}$/', $cleanMobile)) {
        $errors[] = 'Please enter a valid 11-digit Pakistani mobile number starting with 03 (e.g. 03127453108).';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Please enter a valid email address.';
    }

    if (strlen($password) < 6) {
        $errors[] = 'Password must be at least 6 characters.';
    }

    if ($password !== $confirmPassword) {
        $errors[] = 'Passwords do not match.';
    }

    if (!in_array($city, ['Sargodha', 'Shaheenabad', 'Sillanwali'], true)) {
        $errors[] = 'Please select a valid hub (Sargodha, Shaheenabad, or Sillanwali).';
    }

    if (empty($area)) {
        $errors[] = 'Please provide your local area/mohallah/bazaar.';
    }

    if (!$agreeTerms) {
        $errors[] = 'You must agree to the Marketplace Terms & Conditions.';
    }

    // Check unique email and mobile in DB
    if (empty($errors)) {
        $checkStmt = $db->prepare("SELECT id, email, mobile_number FROM users WHERE email = :email OR mobile_number = :mobile LIMIT 1");
        $checkStmt->execute([':email' => $email, ':mobile' => $cleanMobile]);
        $existing = $checkStmt->fetch();

        if ($existing) {
            if ($existing['email'] === $email) {
                $errors[] = 'An account with this email address already exists. Please login.';
            } else {
                $errors[] = 'This mobile number is already registered with another account.';
            }
        }
    }

    // Insert new user
    if (empty($errors)) {
        $passwordHash = password_hash($password, PASSWORD_BCRYPT);
        $insertStmt = $db->prepare("
            INSERT INTO users (full_name, mobile_number, email, password, city, area, role, status)
            VALUES (:name, :mobile, :email, :pass, :city, :area, 'user', 'active')
        ");
        $insertStmt->execute([
            ':name' => $name,
            ':mobile' => $cleanMobile,
            ':email' => $email,
            ':pass' => $passwordHash,
            ':city' => $city,
            ':area' => $area
        ]);

        $newUserId = (int)$db->lastInsertId();

        // Create Welcome Notification
        createNotification($newUserId, 'Welcome to Sargodha Mandi!', 'Your free account has been created. Post your first ad today!', '/post-ad.php', 'welcome');

        // Send Welcome Email via Resend
        if (class_exists('ResendMailer')) {
            ResendMailer::sendWelcomeEmail($email, $name);
        }

        // Log AI Monitoring Event
        if (file_exists(__DIR__ . '/services/AI/AIEventLogger.php')) {
            require_once __DIR__ . '/services/AI/AIEventLogger.php';
            AIEventLogger::logEvent('USER_REGISTERED', 'user', $newUserId, $newUserId, [
                'city' => $city,
                'area' => $area
            ]);
        }

        // Automatically log in
        $_SESSION['user_id'] = $newUserId;
        $_SESSION['user_name'] = $name;
        $_SESSION['user_role'] = 'user';

        redirect('/user/dashboard.php', 'Welcome to Sargodha Mandi! Your account registration is complete.', 'success');
    }
}

$pageTitle = 'Free User Registration - Sargodha Mandi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-6">
            <div class="card border shadow-sm p-4 bg-white rounded-3">
                <div class="text-center mb-4">
                    <span class="text-success fs-1"><i class="bi bi-person-plus-fill"></i></span>
                    <h3 class="fw-bold mb-1">Create Free Account</h3>
                    <p class="text-muted small">Join buyers & sellers across Sargodha, Shaheenabad and Sillanwali</p>
                    <div class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1">
                        <i class="bi bi-check-circle me-1"></i> Registration is 100% Free
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger shadow-sm small">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/register.php">
                    <?= getCsrfField() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name *</label>
                        <input type="text" name="full_name" class="form-control" placeholder="e.g. Muhammad Akram" value="<?= e($name) ?>" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Mobile Number (WhatsApp) *</label>
                            <input type="text" name="mobile_number" class="form-control" placeholder="03127453108" value="<?= e($mobile) ?>" required>
                            <small class="text-muted" style="font-size: 0.72rem;">Buyers will contact you on this number</small>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Email Address *</label>
                            <input type="email" name="email" class="form-control" placeholder="name@example.com" value="<?= e($email) ?>" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">City / Location Hub *</label>
                            <select name="city" class="form-select" required>
                                <option value="Sargodha" <?= $city === 'Sargodha' ? 'selected' : '' ?>>Sargodha</option>
                                <option value="Shaheenabad" <?= $city === 'Shaheenabad' ? 'selected' : '' ?>>Shaheenabad</option>
                                <option value="Sillanwali" <?= $city === 'Sillanwali' ? 'selected' : '' ?>>Sillanwali</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Area / Bazaar / Mohallah *</label>
                            <input type="text" name="area" class="form-control" placeholder="e.g. Satellite Town / Main Bazar" value="<?= e($area) ?>" required>
                        </div>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Password *</label>
                            <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Confirm Password *</label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                        </div>
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="agree_terms" value="1" id="termsCheck" required>
                        <label class="form-check-label small text-muted" for="termsCheck">
                            I agree to the <a href="/terms.php" target="_blank" class="text-success text-decoration-none">Terms & Conditions</a>, <a href="/rules.php" target="_blank" class="text-success text-decoration-none">Listing Rules</a>, and standard Rs. 500 fee per product ad.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary-green w-100 py-2 fw-bold">
                        Create Free Account
                    </button>
                </form>

                <div class="text-center mt-4 border-top pt-3 small text-muted">
                    Already have an account? <a href="/login.php" class="text-success fw-bold text-decoration-none">Login here</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
