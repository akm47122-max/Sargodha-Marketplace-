<?php
/**
 * SARGODHAMART - User Portal: Account Settings
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user = currentUser();
$db = getDB();

$errors = [];
$success = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();

    $action = $_POST['action'] ?? '';

    if ($action === 'update_profile') {
        $fullName = trim($_POST['full_name'] ?? '');
        $phone = trim($_POST['mobile_number'] ?? '');
        $city = trim($_POST['city'] ?? 'Sargodha');
        $area = trim($_POST['area'] ?? '');

        if (empty($fullName)) {
            $errors[] = 'Full name cannot be empty.';
        }
        if (empty($phone)) {
            $errors[] = 'Phone number cannot be empty.';
        }

        if (empty($errors)) {
            $stmt = $db->prepare("UPDATE users SET full_name = :name, mobile_number = :phone, city = :city, area = :area, updated_at = NOW() WHERE id = :id");
            $stmt->execute([
                ':name' => $fullName,
                ':phone' => $phone,
                ':city' => $city,
                ':area' => $area,
                ':id' => $user['id']
            ]);
            $success = 'Profile details updated successfully!';
            $user = currentUser(true); // reload
        }
    } elseif ($action === 'change_password') {
        $currentPassword = $_POST['current_password'] ?? '';
        $newPassword = $_POST['new_password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if (!password_verify($currentPassword, $user['password_hash'])) {
            $errors[] = 'Your current password is incorrect.';
        } elseif (strlen($newPassword) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($newPassword !== $confirmPassword) {
            $errors[] = 'New password confirmation does not match.';
        } else {
            $newHash = password_hash($newPassword, PASSWORD_DEFAULT);
            $db->prepare("UPDATE users SET password_hash = :p, updated_at = NOW() WHERE id = :id")->execute([':p' => $newHash, ':id' => $user['id']]);
            $success = 'Password changed successfully!';
        }
    }
}

$pageTitle = 'Account Settings - ' . SITE_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="/index.php" class="text-decoration-none text-success">Home</a></li>
            <li class="breadcrumb-item"><a href="/user/dashboard.php" class="text-decoration-none text-success">User Portal</a></li>
            <li class="breadcrumb-item active" aria-current="page">Settings</li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Settings Nav Column -->
        <div class="col-12 col-md-4 col-lg-3">
            <div class="card border rounded-3 p-3 bg-white shadow-sm mb-3">
                <div class="d-flex align-items-center gap-3 pb-3 border-bottom mb-3">
                    <div class="w-12 h-12 rounded-circle bg-success text-white d-flex align-items-center justify-center fs-4 fw-bold" style="width: 48px; height: 48px;">
                        <?= strtoupper(substr($user['full_name'], 0, 1)) ?>
                    </div>
                    <div>
                        <strong class="d-block text-dark"><?= e($user['full_name']) ?></strong>
                        <span class="badge bg-<?= $user['activation_status'] === 'active' ? 'success' : 'warning text-dark' ?>" style="font-size: 0.65rem;">
                            <?= strtoupper($user['activation_status']) ?> SELLER
                        </span>
                    </div>
                </div>

                <div class="nav flex-column nav-pills gap-1">
                    <a href="/user/dashboard.php" class="nav-link text-secondary"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a>
                    <a href="/user/my-listings.php" class="nav-link text-secondary"><i class="bi bi-card-list me-2"></i> My Ads</a>
                    <a href="/user/post-ad.php" class="nav-link text-success fw-bold"><i class="bi bi-plus-circle-fill me-2"></i> Post Free Ad</a>
                    <a href="/user/payments.php" class="nav-link text-secondary"><i class="bi bi-credit-card me-2"></i> Payments</a>
                    <a href="/user/messages.php" class="nav-link text-secondary"><i class="bi bi-chat-dots me-2"></i> Messages</a>
                    <a href="/user/settings.php" class="nav-link active bg-success text-white"><i class="bi bi-gear-fill me-2"></i> Settings</a>
                    <a href="/logout.php" class="nav-link text-danger"><i class="bi bi-box-arrow-right me-2"></i> Logout</a>
                </div>
            </div>
        </div>

        <!-- Main Settings Form Column -->
        <div class="col-12 col-md-8 col-lg-9 space-y-4">
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger small shadow-sm mb-4">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $e): ?>
                            <li><?= e($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <?php if ($success): ?>
                <div class="alert alert-success small shadow-sm mb-4">
                    <i class="bi bi-check-circle-fill me-1"></i> <?= e($success) ?>
                </div>
            <?php endif; ?>

            <!-- Profile Info Form -->
            <div class="card border rounded-3 p-4 bg-white shadow-sm mb-4">
                <h5 class="fw-bold mb-3"><i class="bi bi-person-fill text-success me-1"></i> Personal Profile Information</h5>
                <form method="POST" action="/user/settings.php">
                    <?= getCsrfField() ?>
                    <input type="hidden" name="action" value="update_profile">

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Full Name</label>
                            <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Email Address (Read-only)</label>
                            <input type="email" class="form-control bg-light" value="<?= e($user['email']) ?>" readonly>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Mobile Phone Number</label>
                            <input type="text" name="mobile_number" class="form-control" value="<?= e($user['mobile_number']) ?>" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">City / Local Hub</label>
                            <select name="city" class="form-select" required>
                                <option value="Sargodha" <?= $user['city'] === 'Sargodha' ? 'selected' : '' ?>>Sargodha</option>
                                <option value="Shaheenabad" <?= $user['city'] === 'Shaheenabad' ? 'selected' : '' ?>>Shaheenabad</option>
                                <option value="Sillanwali" <?= $user['city'] === 'Sillanwali' ? 'selected' : '' ?>>Sillanwali</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Area / Town</label>
                        <input type="text" name="area" class="form-control" value="<?= e($user['area']) ?>" placeholder="e.g. Satellite Town, Main Bazaar, Fatima Jinnah Road">
                    </div>

                    <button type="submit" class="btn btn-primary-green fw-bold px-4">
                        Update Profile
                    </button>
                </form>
            </div>

            <!-- Password Change Form -->
            <div class="card border rounded-3 p-4 bg-white shadow-sm">
                <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock-fill text-warning me-1"></i> Change Password</h5>
                <form method="POST" action="/user/settings.php">
                    <?= getCsrfField() ?>
                    <input type="hidden" name="action" value="change_password">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">New Password</label>
                            <input type="password" name="new_password" class="form-control" required minlength="6">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required minlength="6">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-outline-dark fw-bold px-4">
                        Change Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
