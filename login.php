<?php
/**
 * Sargodha Mandi - User Login
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

if (isLoggedIn()) {
    redirect('/user/dashboard.php');
}

$db = getDB();
$errors = [];
$identifier = '';
$redirectUrl = $_GET['redirect'] ?? '/user/dashboard.php';

// Quick 1-Click Demo Login Handler
if (isset($_GET['demo'])) {
    $demoRole = $_GET['demo'];
    $demoEmails = [
        'admin' => 'admin@sargodhamandi.com',
        'shaheenabad' => 'tariq@sargodha.com',
        'sillanwali' => 'naveed@sillanwali.com',
        'sargodha' => 'usman@sargodha.com'
    ];

    if (isset($demoEmails[$demoRole])) {
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :email LIMIT 1");
        $stmt->execute([':email' => $demoEmails[$demoRole]]);
        $u = $stmt->fetch();
        if ($u) {
            $_SESSION['user_id'] = $u['id'];
            $_SESSION['user_name'] = $u['full_name'];
            $_SESSION['user_role'] = $u['role'];
            $dest = in_array($u['role'], ['admin', 'super_admin']) ? '/admin/index.php' : '/user/dashboard.php';
            redirect($dest, 'Logged in as ' . $u['full_name'] . ' (' . ucfirst($u['role']) . ')', 'success');
        }
    }
}

// Standard Login Form Processing
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();

    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';
    $cleanMobile = preg_replace('/[^0-9]/', '', $identifier);

    if (empty($identifier) || empty($password)) {
        $errors[] = 'Please enter both your email/mobile and password.';
    } else {
        // Query user by email OR clean mobile
        $stmt = $db->prepare("SELECT * FROM users WHERE email = :id_email OR mobile_number = :id_mobile LIMIT 1");
        $stmt->execute([':id_email' => $identifier, ':id_mobile' => $cleanMobile]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            if ($user['status'] === 'suspended') {
                $errors[] = 'Your account has been suspended by administration. Contact helpline 03127453108 for details.';
            } else {
                // Success
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['full_name'];
                $_SESSION['user_role'] = $user['role'];

                // Update remember token if checked
                if (!empty($_POST['remember'])) {
                    $token = bin2hex(random_bytes(32));
                    $db->prepare("UPDATE users SET remember_token = :t WHERE id = :id")->execute([':t' => $token, ':id' => $user['id']]);
                    setcookie('sm_remember', $token, time() + (86400 * 30), '/', '', false, true);
                }

                $target = $redirectUrl;
                if (in_array($user['role'], ['admin', 'super_admin']) && $redirectUrl === '/user/dashboard.php') {
                    $target = '/admin/index.php';
                }

                if (file_exists(__DIR__ . '/services/AI/AIEventLogger.php')) {
                    require_once __DIR__ . '/services/AI/AIEventLogger.php';
                    AIEventLogger::logEvent('USER_LOGIN', 'user', (int)$user['id'], (int)$user['id'], [
                        'role' => $user['role'],
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
                    ]);
                }

                redirect($target, 'Welcome back, ' . $user['full_name'] . '!', 'success');
            }
        } else {
            $errors[] = 'Invalid login credentials. Please check your password or email/mobile.';
        }
    }
}

$pageTitle = 'Login - Sargodha Mandi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-7 col-lg-5">
            <!-- Demo Quick Login Bar for testing convenience -->
            <div class="card border border-warning bg-warning bg-opacity-10 p-3 mb-4 rounded-3 shadow-sm">
                <div class="d-flex align-items-center justify-content-between mb-2">
                    <span class="small fw-bold text-dark"><i class="bi bi-lightning-charge-fill text-warning me-1"></i> Quick 1-Click Evaluation Login:</span>
                </div>
                <div class="d-flex flex-wrap gap-1">
                    <a href="/login.php?demo=admin" class="btn btn-dark btn-sm flex-fill" style="font-size: 0.76rem;">
                        <i class="bi bi-shield-lock"></i> Super Admin
                    </a>
                    <a href="/login.php?demo=shaheenabad" class="btn btn-outline-success btn-sm flex-fill bg-white" style="font-size: 0.76rem;">
                        Shaheenabad Seller
                    </a>
                    <a href="/login.php?demo=sillanwali" class="btn btn-outline-success btn-sm flex-fill bg-white" style="font-size: 0.76rem;">
                        Sillanwali Seller
                    </a>
                    <a href="/login.php?demo=sargodha" class="btn btn-outline-success btn-sm flex-fill bg-white" style="font-size: 0.76rem;">
                        Sargodha Mobile Shop
                    </a>
                </div>
            </div>

            <div class="card border shadow-sm p-4 bg-white rounded-3">
                <div class="text-center mb-4">
                    <span class="text-success fs-1"><i class="bi bi-box-arrow-in-right"></i></span>
                    <h3 class="fw-bold mb-1">Account Login</h3>
                    <p class="text-muted small">Access your ads, messages, favorites & payment history</p>
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

                <form method="POST" action="/login.php<?= $redirectUrl ? '?redirect=' . urlencode($redirectUrl) : '' ?>">
                    <?= getCsrfField() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Email Address or Mobile Number</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-person"></i></span>
                            <input type="text" name="identifier" class="form-control" placeholder="03127453108 or email@..." value="<?= e($identifier) ?>" required autofocus>
                        </div>
                    </div>

                    <div class="mb-3">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="form-label small fw-bold mb-0">Password</label>
                            <a href="/forgot-password.php" class="text-success small text-decoration-none">Forgot password?</a>
                        </div>
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-lock"></i></span>
                            <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                        </div>
                    </div>

                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="remember" value="1" id="rememberMe">
                        <label class="form-check-label small text-muted" for="rememberMe">
                            Keep me logged in
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary-green w-100 py-2 fw-bold">
                        Login to Account
                    </button>
                </form>

                <div class="text-center mt-4 border-top pt-3 small text-muted">
                    Don't have an account yet? <a href="/register.php" class="text-success fw-bold text-decoration-none">Register for Free</a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
