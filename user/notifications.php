<?php
/**
 * Sargodha Mandi - Notifications Center
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user = currentUser();
$db = getDB();

// Mark all as read if requested
if (isset($_POST['mark_all_read'])) {
    verifyCsrfOrDie();
    $db->prepare("UPDATE notifications SET is_read = 1 WHERE user_id = :uid")->execute([':uid' => $user['id']]);
    redirect('/user/notifications.php', 'All notifications marked as read.', 'success');
}

// Fetch user notifications
$stmt = $db->prepare("SELECT * FROM notifications WHERE user_id = :uid ORDER BY id DESC LIMIT 50");
$stmt->execute([':uid' => $user['id']]);
$notifications = $stmt->fetchAll();

$pageTitle = 'Notifications - Sargodha Mandi';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Notifications</h3>
            <p class="text-muted small mb-0">Updates regarding your listing approvals, fee verification, messages and reviews</p>
        </div>
        <?php if (!empty($notifications)): ?>
            <form method="POST" action="/user/notifications.php">
                <?= getCsrfField() ?>
                <button type="submit" name="mark_all_read" value="1" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-check-all me-1"></i> Mark All as Read
                </button>
            </form>
        <?php endif; ?>
    </div>

    <?php if (empty($notifications)): ?>
        <div class="card border p-5 text-center bg-white shadow-sm">
            <i class="bi bi-bell-slash fs-1 text-muted mb-2"></i>
            <h5 class="fw-bold">No notifications yet</h5>
            <p class="text-muted small">You will receive notifications here when your ads are verified, fees approved, or when buyers message you.</p>
        </div>
    <?php else: ?>
        <div class="card border shadow-sm bg-white overflow-hidden">
            <div class="list-group list-group-flush">
                <?php foreach ($notifications as $n): ?>
                    <a href="<?= e($n['link'] ?: '#') ?>" class="list-group-item list-group-item-action p-3 <?= !$n['is_read'] ? 'bg-light border-start border-success border-4' : '' ?>">
                        <div class="d-flex justify-content-between align-items-start mb-1">
                            <h6 class="fw-bold mb-0 text-dark">
                                <?php if (!$n['is_read']): ?>
                                    <span class="badge bg-success me-1">New</span>
                                <?php endif; ?>
                                <?= e($n['title']) ?>
                            </h6>
                            <small class="text-muted"><?= timeAgo($n['created_at']) ?></small>
                        </div>
                        <p class="small text-secondary mb-0"><?= e($n['message']) ?></p>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
