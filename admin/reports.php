<?php
/**
 * Sargodha Mandi - Flagged Reports Moderation
 */

$adminTitle = 'Reports Moderation - Admin Panel';
require_once __DIR__ . '/includes/admin_header.php';

// Handle Actions (Resolve, Dismiss, Delete Listing, Suspend Seller)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();
    $action = $_POST['action'] ?? '';
    $reportId = (int)($_POST['report_id'] ?? 0);

    if ($reportId > 0) {
        $stmt = $db->prepare("SELECT r.*, l.user_id as seller_id FROM reports r JOIN listings l ON r.listing_id = l.id WHERE r.id = :id LIMIT 1");
        $stmt->execute([':id' => $reportId]);
        $report = $stmt->fetch();

        if ($report) {
            if ($action === 'resolve') {
                $db->prepare("UPDATE reports SET status = 'resolved' WHERE id = :id")->execute([':id' => $reportId]);
                logAdminAction($admin['id'], 'resolve_report', 'reports', $reportId);
                redirect('/admin/reports.php', 'Report marked as resolved.', 'success');

            } elseif ($action === 'dismiss') {
                $db->prepare("UPDATE reports SET status = 'dismissed' WHERE id = :id")->execute([':id' => $reportId]);
                redirect('/admin/reports.php', 'Report dismissed.', 'info');

            } elseif ($action === 'delete_listing') {
                $db->prepare("DELETE FROM listings WHERE id = :id")->execute([':id' => $report['listing_id']]);
                $db->prepare("UPDATE reports SET status = 'resolved', admin_notes = 'Listing removed' WHERE id = :id")->execute([':id' => $reportId]);
                logAdminAction($admin['id'], 'delete_listing_from_report', 'listings', $report['listing_id']);
                redirect('/admin/reports.php', 'Flagged listing deleted and report resolved.', 'danger');

            } elseif ($action === 'suspend_seller') {
                $db->prepare("UPDATE users SET status = 'suspended' WHERE id = :id")->execute([':id' => $report['seller_id']]);
                $db->prepare("UPDATE listings SET status = 'Suspended' WHERE user_id = :uid")->execute([':uid' => $report['seller_id']]);
                $db->prepare("UPDATE reports SET status = 'resolved', admin_notes = 'Seller suspended' WHERE id = :id")->execute([':id' => $reportId]);
                logAdminAction($admin['id'], 'suspend_seller_from_report', 'users', $report['seller_id']);
                redirect('/admin/reports.php', 'Seller suspended and all their listings paused.', 'warning');
            }
        }
    }
}

// Fetch all reports
$reports = $db->query("
    SELECT r.*, l.title as listing_title, l.city as listing_city, l.user_id as seller_id,
           u_rep.full_name as reporter_name, u_sell.full_name as seller_name, u_sell.mobile_number as seller_phone
    FROM reports r
    JOIN listings l ON r.listing_id = l.id
    LEFT JOIN users u_rep ON r.reporter_id = u_rep.id
    JOIN users u_sell ON l.user_id = u_sell.id
    ORDER BY (r.status = 'pending') DESC, r.id DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">Reported Listings</h3>
        <p class="text-muted small mb-0">Investigate user reports for scam prevention in Sargodha, Shaheenabad & Sillanwali</p>
    </div>
</div>

<?php if (empty($reports)): ?>
    <div class="card border-0 shadow-sm p-5 text-center bg-white">
        <i class="bi bi-shield-check fs-1 text-success mb-2"></i>
        <h5 class="fw-bold">No reports flagged</h5>
        <p class="text-muted small">All marketplace listings are operating without pending complaints.</p>
    </div>
<?php else: ?>
    <div class="card border-0 shadow-sm bg-white overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Reason</th>
                        <th>Listing</th>
                        <th>Report Details</th>
                        <th>Reporter</th>
                        <th>Seller</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th class="text-end">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($reports as $r): ?>
                        <tr class="<?= $r['status'] === 'pending' ? 'table-danger bg-opacity-25' : '' ?>">
                            <td class="text-muted">#<?= $r['id'] ?></td>
                            <td>
                                <span class="badge bg-danger"><?= e($r['reason']) ?></span>
                            </td>
                            <td>
                                <a href="/product.php?id=<?= $r['listing_id'] ?>" target="_blank" class="fw-bold text-dark text-decoration-none d-block text-truncate" style="max-width: 170px;">
                                    <?= e($r['listing_title']) ?>
                                </a>
                                <small class="text-muted"><?= e($r['listing_city']) ?></small>
                            </td>
                            <td style="max-width: 200px;">
                                <?= e($r['details']) ?>
                            </td>
                            <td>
                                <?= e($r['reporter_name'] ?: 'Guest Visitor') ?>
                            </td>
                            <td>
                                <strong><?= e($r['seller_name']) ?></strong><br>
                                <small class="text-muted"><?= e($r['seller_phone']) ?></small>
                            </td>
                            <td>
                                <?php if ($r['status'] === 'pending'): ?>
                                    <span class="badge bg-danger">Pending</span>
                                <?php elseif ($r['status'] === 'resolved'): ?>
                                    <span class="badge bg-success">Resolved</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary"><?= e($r['status']) ?></span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted text-nowrap"><?= timeAgo($r['created_at']) ?></td>
                            <td class="text-end text-nowrap">
                                <a href="/product.php?id=<?= $r['listing_id'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm py-0 px-2" title="Inspect">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <?php if ($r['status'] === 'pending'): ?>
                                    <form method="POST" action="/admin/reports.php" class="d-inline">
                                        <?= getCsrfField() ?>
                                        <input type="hidden" name="action" value="resolve">
                                        <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="btn btn-outline-success btn-sm py-0 px-2" title="Mark Resolved">
                                            <i class="bi bi-check-lg"></i>
                                        </button>
                                    </form>

                                    <form method="POST" action="/admin/reports.php" class="d-inline" onsubmit="return confirm('Remove listing permanently?');">
                                        <?= getCsrfField() ?>
                                        <input type="hidden" name="action" value="delete_listing">
                                        <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="btn btn-danger btn-sm py-0 px-2" title="Remove Listing">
                                            Remove Ad
                                        </button>
                                    </form>

                                    <form method="POST" action="/admin/reports.php" class="d-inline" onsubmit="return confirm('Suspend this seller account?');">
                                        <?= getCsrfField() ?>
                                        <input type="hidden" name="action" value="suspend_seller">
                                        <input type="hidden" name="report_id" value="<?= $r['id'] ?>">
                                        <button type="submit" class="btn btn-outline-dark btn-sm py-0 px-2" title="Suspend Seller">
                                            Suspend User
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
