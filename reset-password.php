<?php
/**
 * Sargodha Mandi - Set New Password
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$token = trim($_GET['token'] ?? '');
$db = getDB();
$error = null;

if (empty($token)) {
    redirect('/login.php', 'Invalid password reset token.', 'danger');
}

$stmt = $db->prepare("SELECT id, full_name FROM users WHERE reset_token = :token AND reset_expires_at > NOW() LIMIT 1");
$stmt->execute([':token' => $token]);
$user = $stmt->fetch();

if (!$user) {
    redirect('/forgot-password.php', 'This reset link has expired or is invalid. Please request a new one.', 'danger');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();
    $password = $_POST['password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';

    if (strlen($password) < 6) {
        $error = 'Password must be at least 6 characters.';
    } elseif ($password !== $confirmPassword) {
        $error = 'Passwords do not match.';
    } else {
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $db->prepare("UPDATE users SET password = :p, reset_token = NULL, reset_expires_at = NULL WHERE id = :id")
           ->execute([':p' => $hash, ':id' => $user['id']]);

        redirect('/login.php', 'Password successfully updated! You can now log in with your new password.', 'success');
    }
}

$pageTitle = 'Set New Password - Sargodha Mandi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card border shadow-sm p-4 bg-white rounded-3">
                <div class="text-center mb-4">
                    <h4 class="fw-bold mb-1">Set New Password</h4>
                    <p class="text-muted small">For user: <?= e($user['full_name']) ?></p>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger small shadow-sm"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/reset-password.php?token=<?= urlencode($token) ?>">
                    <?= getCsrfField() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">New Password</label>
                        <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required autofocus>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Confirm New Password</label>
                        <input type="password" name="confirm_password" class="form-control" placeholder="Repeat password" required>
                    </div>
                    <button type="submit" class="btn btn-primary-green w-100 py-2 fw-bold">Update Password</button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
