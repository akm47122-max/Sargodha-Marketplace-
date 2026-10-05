<?php
/**
 * Sargodha Mandi - Review Moderation
 */

$adminTitle = 'Moderate Reviews - Admin Panel';
require_once __DIR__ . '/includes/admin_header.php';

// Handle Action
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();
    $action = $_POST['action'] ?? '';
    $revId = (int)($_POST['review_id'] ?? 0);

    if ($revId > 0) {
        if ($action === 'delete') {
            $db->prepare("DELETE FROM reviews WHERE id = :id")->execute([':id' => $revId]);
            redirect('/admin/reviews.php', 'Review removed.', 'info');
        } elseif ($action === 'hide') {
            $db->prepare("UPDATE reviews SET status = 'hidden' WHERE id = :id")->execute([':id' => $revId]);
            redirect('/admin/reviews.php', 'Review hidden.', 'warning');
        } elseif ($action === 'publish') {
            $db->prepare("UPDATE reviews SET status = 'published' WHERE id = :id")->execute([':id' => $revId]);
            redirect('/admin/reviews.php', 'Review published.', 'success');
        }
    }
}

$reviews = $db->query("
    SELECT r.*, 
           u_sell.full_name as seller_name, u_sell.city as seller_city,
           u_rev.full_name as reviewer_name
    FROM reviews r
    JOIN users u_sell ON r.seller_id = u_sell.id
    JOIN users u_rev ON r.reviewer_id = u_rev.id
    ORDER BY r.id DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Seller Reviews Moderation</h3>
        <p class="text-muted small mb-0">Ensure authentic feedback across buyers and sellers</p>
    </div>
</div>

<div class="card border-0 shadow-sm bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Seller</th>
                    <th>Reviewer</th>
                    <th>Rating</th>
                    <th>Review Content</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($reviews as $rev): ?>
                    <tr>
                        <td class="text-muted">#<?= $rev['id'] ?></td>
                        <td>
                            <strong><?= e($rev['seller_name']) ?></strong><br>
                            <small class="text-muted"><?= e($rev['seller_city']) ?></small>
                        </td>
                        <td><?= e($rev['reviewer_name']) ?></td>
                        <td>
                            <span class="text-warning fw-bold">⭐ <?= $rev['rating'] ?></span>
                        </td>
                        <td style="max-width: 250px;"><?= e($rev['review_text']) ?></td>
                        <td>
                            <?php if ($rev['status'] === 'published'): ?>
                                <span class="badge bg-success-subtle text-success">Published</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary">Hidden</span>
                            <?php endif; ?>
                        </td>
                        <td class="text-muted text-nowrap"><?= date('d M Y', strtotime($rev['created_at'])) ?></td>
                        <td class="text-end text-nowrap">
                            <form method="POST" action="/admin/reviews.php" class="d-inline">
                                <?= getCsrfField() ?>
                                <input type="hidden" name="action" value="<?= $rev['status'] === 'published' ? 'hide' : 'publish' ?>">
                                <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                <button type="submit" class="btn btn-outline-secondary btn-sm py-0 px-2">
                                    <?= $rev['status'] === 'published' ? 'Hide' : 'Unhide' ?>
                                </button>
                            </form>
                            <form method="POST" action="/admin/reviews.php" class="d-inline" onsubmit="return confirm('Delete review permanently?');">
                                <?= getCsrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="review_id" value="<?= $rev['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
