<?php
/**
 * Sargodha Mandi - User Profile & Account Settings
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user = currentUser();
$db = getDB();
$errors = [];
$successMsg = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();

    $action = $_POST['form_action'] ?? 'update_profile';

    if ($action === 'update_profile') {
        $name = trim($_POST['full_name'] ?? '');
        $mobile = trim($_POST['mobile_number'] ?? '');
        $city = trim($_POST['city'] ?? 'Sargodha');
        $area = trim($_POST['area'] ?? '');

        if (strlen($name) < 3) {
            $errors[] = 'Full name must be at least 3 characters.';
        }

        $cleanMobile = preg_replace('/[^0-9]/', '', $mobile);
        if (!preg_match('/^03[0-9]{9}$/', $cleanMobile)) {
            $errors[] = 'Please enter a valid 11-digit Pakistani mobile number (03xx-xxxxxxx).';
        }

        if (!in_array($city, ['Sargodha', 'Shaheenabad', 'Sillanwali'], true)) {
            $errors[] = 'Invalid city selection.';
        }

        if (empty($area)) {
            $errors[] = 'Please provide your local area/bazaar.';
        }

        // Check mobile unique
        if (empty($errors)) {
            $mCheck = $db->prepare("SELECT id FROM users WHERE mobile_number = :m AND id != :uid LIMIT 1");
            $mCheck->execute([':m' => $cleanMobile, ':uid' => $user['id']]);
            if ($mCheck->fetch()) {
                $errors[] = 'This mobile number is already in use by another user.';
            }
        }

        if (empty($errors)) {
            $up = $db->prepare("UPDATE users SET full_name = :n, mobile_number = :m, city = :c, area = :a WHERE id = :id");
            $up->execute([
                ':n' => $name,
                ':m' => $cleanMobile,
                ':c' => $city,
                ':a' => $area,
                ':id' => $user['id']
            ]);

            if (file_exists(__DIR__ . '/../services/AI/AIEventLogger.php')) {
                require_once __DIR__ . '/../services/AI/AIEventLogger.php';
                AIEventLogger::logEvent('USER_PROFILE_UPDATED', 'user', (int)$user['id'], (int)$user['id'], [
                    'city' => $city,
                    'area' => $area
                ]);
            }

            $successMsg = 'Profile information updated successfully!';
            $user = currentUser(); // refresh
        }
    } elseif ($action === 'change_password') {
        $oldPass = $_POST['current_password'] ?? '';
        $newPass = $_POST['new_password'] ?? '';
        $confirmPass = $_POST['confirm_password'] ?? '';

        if (!password_verify($oldPass, $user['password'])) {
            $errors[] = 'Current password is incorrect.';
        } elseif (strlen($newPass) < 6) {
            $errors[] = 'New password must be at least 6 characters.';
        } elseif ($newPass !== $confirmPass) {
            $errors[] = 'New password and confirmation do not match.';
        } else {
            $hash = password_hash($newPass, PASSWORD_BCRYPT);
            $db->prepare("UPDATE users SET password = :p WHERE id = :id")->execute([':p' => $hash, ':id' => $user['id']]);
            $successMsg = 'Password successfully changed!';
        }
    }
}

$pageTitle = 'Profile Settings - Sargodha Mandi';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-8 col-lg-7">
            <h3 class="fw-bold mb-1">Account & Profile Settings</h3>
            <p class="text-muted small mb-4">Manage your public seller details and security credentials</p>

            <?php if ($successMsg): ?>
                <div class="alert alert-success shadow-sm small">
                    <i class="bi bi-check-circle me-1"></i> <?= e($successMsg) ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger shadow-sm small">
                    <ul class="mb-0 ps-3">
                        <?php foreach ($errors as $e): ?>
                            <li><?= e($e) ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <!-- Profile Info Card -->
            <div class="card border shadow-sm p-4 bg-white rounded-3 mb-4">
                <h5 class="fw-bold mb-3">General Information</h5>
                <form method="POST" action="/user/profile.php">
                    <?= getCsrfField() ?>
                    <input type="hidden" name="form_action" value="update_profile">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Name</label>
                        <input type="text" name="full_name" class="form-control" value="<?= e($user['full_name']) ?>" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Mobile Number (WhatsApp)</label>
                            <input type="text" name="mobile_number" class="form-control" value="<?= e($user['mobile_number']) ?>" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Email Address</label>
                            <input type="email" class="form-control bg-light" value="<?= e($user['email']) ?>" readonly disabled>
                            <small class="text-muted" style="font-size: 0.7rem;">Email cannot be changed directly</small>
                        </div>
                    </div>

                    <div class="row g-2 mb-4">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">City / Location Hub</label>
                            <select name="city" class="form-select" required>
                                <option value="Sargodha" <?= $user['city'] === 'Sargodha' ? 'selected' : '' ?>>Sargodha</option>
                                <option value="Shaheenabad" <?= $user['city'] === 'Shaheenabad' ? 'selected' : '' ?>>Shaheenabad</option>
                                <option value="Sillanwali" <?= $user['city'] === 'Sillanwali' ? 'selected' : '' ?>>Sillanwali</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Area / Bazaar</label>
                            <input type="text" name="area" class="form-control" value="<?= e($user['area']) ?>" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary-green btn-sm px-4 py-2">
                        Save Changes
                    </button>
                </form>
            </div>

            <!-- Change Password Card -->
            <div class="card border shadow-sm p-4 bg-white rounded-3">
                <h5 class="fw-bold mb-3">Change Password</h5>
                <form method="POST" action="/user/profile.php">
                    <?= getCsrfField() ?>
                    <input type="hidden" name="form_action" value="change_password">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Current Password</label>
                        <input type="password" name="current_password" class="form-control" required>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">New Password</label>
                            <input type="password" name="new_password" class="form-control" placeholder="Min. 6 characters" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Confirm New Password</label>
                            <input type="password" name="confirm_password" class="form-control" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-outline-dark btn-sm px-4 py-2">
                        Update Password
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
