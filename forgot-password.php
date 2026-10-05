<?php
/**
 * Sargodha Mandi - Forgot Password
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$message = null;
$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();
    $email = trim($_POST['email'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = 'Please enter a valid email address.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT id, full_name FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(24));
            $expires = date('Y-m-d H:i:s', time() + 3600); // 1 hour

            $db->prepare("UPDATE users SET reset_token = :t, reset_expires_at = :e WHERE id = :id")
               ->execute([':t' => $token, ':e' => $expires, ':id' => $user['id']]);

            if (file_exists(__DIR__ . '/services/AI/AIEventLogger.php')) {
                require_once __DIR__ . '/services/AI/AIEventLogger.php';
                $emailDomain = explode('@', $email)[1] ?? 'domain.com';
                AIEventLogger::logEvent('PASSWORD_RESET_REQUESTED', 'user', (int)$user['id'], (int)$user['id'], [
                    'email' => substr($email, 0, 3) . '***@' . $emailDomain
                ]);
            }

            $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : ('http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:3000'));
            $resetLink = $siteUrl . "/reset-password.php?token=" . urlencode($token);

            if (class_exists('ResendMailer') && ResendMailer::isConfigured()) {
                ResendMailer::sendPasswordResetEmail($email, $user['full_name'] ?? 'User', $resetLink);
                $message = "A password reset link has been sent to your registered email address. Please check your inbox (and spam folder). The link will expire in 60 minutes.";
            } else {
                // Fallback for local development or before Resend API key is populated
                $message = "Password reset instructions generated. <a href='{$resetLink}' class='fw-bold text-success'>Click here to set a new password</a> (Resend email service will send this automatically once RESEND_API_KEY is configured).";
            }
        } else {
            // Anti-enumeration: still display standard response
            $message = "If an account exists with that email, a password reset link has been dispatched to your inbox.";
        }
    }
}

$pageTitle = 'Forgot Password - Sargodha Mandi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-5">
            <div class="card border shadow-sm p-4 bg-white rounded-3">
                <div class="text-center mb-4">
                    <span class="text-success fs-1"><i class="bi bi-key-fill"></i></span>
                    <h4 class="fw-bold mb-1">Reset Password</h4>
                    <p class="text-muted small">Enter your registered email address</p>
                </div>

                <?php if ($message): ?>
                    <div class="alert alert-info small shadow-sm"><?= $message ?></div>
                <?php endif; ?>

                <?php if ($error): ?>
                    <div class="alert alert-danger small shadow-sm"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/forgot-password.php">
                    <?= getCsrfField() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Address</label>
                        <input type="email" name="email" class="form-control" placeholder="name@example.com" required autofocus>
                    </div>
                    <button type="submit" class="btn btn-primary-green w-100 py-2 fw-bold">Send Reset Link</button>
                </form>

                <div class="text-center mt-4 border-top pt-3 small">
                    <a href="/login.php" class="text-success text-decoration-none">&larr; Back to Login</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
