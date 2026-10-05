<?php
/**
 * Sargodha Mandi - Administrator Login
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

if (isAdmin()) {
    redirect('/admin/index.php');
}

$db = getDB();
$errors = [];

// Quick 1-click Super Admin login for instant testing
if (isset($_GET['quick_admin'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE role IN ('admin', 'super_admin') LIMIT 1");
    $stmt->execute();
    $adminUser = $stmt->fetch();
    if ($adminUser) {
        $_SESSION['user_id'] = $adminUser['id'];
        $_SESSION['user_name'] = $adminUser['full_name'];
        $_SESSION['user_role'] = $adminUser['role'];
        redirect('/admin/index.php', 'Authenticated as ' . $adminUser['full_name'] . ' (' . $adminUser['role'] . ')', 'success');
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();

    $identifier = trim($_POST['identifier'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $db->prepare("SELECT * FROM users WHERE (email = :id OR mobile_number = :id) AND role IN ('admin', 'super_admin') LIMIT 1");
    $stmt->execute([':id' => $identifier]);
    $admin = $stmt->fetch();

    if ($admin && password_verify($password, $admin['password'])) {
        if ($admin['status'] !== 'active') {
            $errors[] = 'Administrative account is inactive.';
        } else {
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['user_name'] = $admin['full_name'];
            $_SESSION['user_role'] = $admin['role'];

            logAdminAction($admin['id'], 'admin_login', 'user', $admin['id'], 'Admin logged in');
            redirect('/admin/index.php', 'Welcome to Sargodha Mandi Admin Panel!', 'success');
        }
    } else {
        $errors[] = 'Invalid administrator credentials or insufficient privileges.';
    }
}

$pageTitle = 'Admin Login - Sargodha Mandi';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Sargodha Mandi</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body class="bg-dark d-flex align-items-center justify-content-center" style="min-height: 100vh;">

<div class="container py-5">
    <div class="row justify-content-center">
        <div class="col-12 col-md-6 col-lg-4">
            <div class="card border-0 shadow-lg p-4 bg-white rounded-3">
                <div class="text-center mb-4">
                    <span class="text-success fs-1"><i class="bi bi-shield-lock-fill"></i></span>
                    <h4 class="fw-bold mb-1">Admin Control Panel</h4>
                    <p class="text-muted small">Sargodha | Shaheenabad | Sillanwali</p>
                </div>

                <!-- 1-Click Evaluation Login -->
                <div class="alert alert-warning p-2 small mb-3 text-center">
                    <strong>Quick Testing:</strong><br>
                    <a href="/admin/login.php?quick_admin=1" class="btn btn-warning btn-sm fw-bold w-100 mt-1">
                        <i class="bi bi-key-fill me-1"></i> Login as Super Admin (Muhammad Akram Tayyab)
                    </a>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger small">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $e): ?>
                                <li><?= e($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/admin/login.php">
                    <?= getCsrfField() ?>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Admin Email or Mobile</label>
                        <input type="text" name="identifier" class="form-control" placeholder="admin@sargodhamandi.com" required autofocus>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Password</label>
                        <input type="password" name="password" class="form-control" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn btn-dark w-100 py-2 fw-bold">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Access Admin Area
                    </button>
                </form>

                <div class="text-center mt-4 border-top pt-3 small">
                    <a href="/index.php" class="text-secondary text-decoration-none">&larr; Return to Marketplace Homepage</a>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
