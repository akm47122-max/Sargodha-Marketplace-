<?php
/**
 * Sargodha Mandi - Admin Listings Management
 */

$adminTitle = 'Manage Listings - Admin Panel';
require_once __DIR__ . '/includes/admin_header.php';

// Handle Actions (Approve, Reject, Feature, Delete, Status change)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();
    $action = $_POST['action'] ?? '';
    $listingId = (int)($_POST['listing_id'] ?? 0);

    if ($listingId > 0) {
        if ($action === 'approve') {
            $db->prepare("UPDATE listings SET status = 'Published' WHERE id = :id")->execute([':id' => $listingId]);
            $listingInfo = $db->query("SELECT l.title, l.user_id, u.email, u.full_name FROM listings l JOIN users u ON l.user_id = u.id WHERE l.id = {$listingId}")->fetch();
            $uid = $listingInfo['user_id'] ?? 0;
            createNotification((int)$uid, 'Listing Published', 'Your listing was reviewed and published by an administrator.', "/product.php?id={$listingId}", 'listing_approved');
            logAdminAction($admin['id'], 'approve_listing', 'listings', $listingId);

            if (file_exists(__DIR__ . '/../services/AI/AIEventLogger.php')) {
                require_once __DIR__ . '/../services/AI/AIEventLogger.php';
                AIEventLogger::logEvent('LISTING_APPROVED', 'listing', $listingId, (int)$uid, [
                    'title' => $listingInfo['title'] ?? '',
                    'admin_id' => (int)$admin['id']
                ]);
            }

            // Send Listing Approved Email via Resend
            if ($listingInfo && class_exists('ResendMailer') && !empty($listingInfo['email'])) {
                ResendMailer::sendListingApprovedEmail($listingInfo['email'], $listingInfo['full_name'] ?? 'Seller', $listingInfo['title'], $listingId);
            }

            // Telegram Auto-Publishing if enabled
            if (getSetting('telegram_enabled', '0') === '1' && getSetting('telegram_auto_publish_products', '1') === '1') {
                if (function_exists('broadcastListingToTelegramPHP')) {
                    broadcastListingToTelegramPHP($listingId);
                }
            }

            redirect('/admin/listings.php', 'Listing marked as Published.', 'success');

        } elseif ($action === 'publish_telegram') {
            if (function_exists('broadcastListingToTelegramPHP')) {
                $res = broadcastListingToTelegramPHP($listingId);
                if (!empty($res['success'])) {
                    redirect('/admin/listings.php', 'Product broadcasted to Telegram Channel (ID: #' . ($res['message_id'] ?? '') . ')!', 'success');
                } else {
                    redirect('/admin/listings.php', 'Telegram error: ' . ($res['error'] ?? 'Failed to broadcast.'), 'warning');
                }
            } else {
                redirect('/admin/listings.php', 'Telegram helper is unavailable.', 'warning');
            }

        } elseif ($action === 'reject') {
            $reason = trim($_POST['rejection_reason'] ?? 'Did not meet listing guidelines');
            $db->prepare("UPDATE listings SET status = 'Rejected', rejection_reason = :r WHERE id = :id")
               ->execute([':r' => $reason, ':id' => $listingId]);
            $listingInfo = $db->query("SELECT l.title, l.user_id, u.email, u.full_name FROM listings l JOIN users u ON l.user_id = u.id WHERE l.id = {$listingId}")->fetch();
            $uid = $listingInfo['user_id'] ?? 0;
            createNotification((int)$uid, 'Listing Rejected', "Your ad was rejected: {$reason}", "/user/my-listings.php", 'listing_rejected');
            logAdminAction($admin['id'], 'reject_listing', 'listings', $listingId, $reason);

            if (file_exists(__DIR__ . '/../services/AI/AIEventLogger.php')) {
                require_once __DIR__ . '/../services/AI/AIEventLogger.php';
                AIEventLogger::logEvent('LISTING_REJECTED', 'listing', $listingId, (int)$uid, [
                    'title' => $listingInfo['title'] ?? '',
                    'reason' => $reason,
                    'admin_id' => (int)$admin['id']
                ]);
            }

            // Send Listing Rejected Email via Resend
            if ($listingInfo && class_exists('ResendMailer') && !empty($listingInfo['email'])) {
                ResendMailer::sendListingRejectedEmail($listingInfo['email'], $listingInfo['full_name'] ?? 'Seller', $listingInfo['title'], $reason);
            }

            redirect('/admin/listings.php', 'Listing marked as Rejected.', 'warning');

        } elseif ($action === 'toggle_featured') {
            $curr = $db->query("SELECT is_featured FROM listings WHERE id = {$listingId}")->fetchColumn();
            $newVal = $curr ? 0 : 1;
            $db->prepare("UPDATE listings SET is_featured = :f WHERE id = :id")->execute([':f' => $newVal, ':id' => $listingId]);
            redirect('/admin/listings.php', $newVal ? 'Ad featured!' : 'Ad unfeatured.', 'success');

        } elseif ($action === 'delete') {
            $db->prepare("DELETE FROM listings WHERE id = :id")->execute([':id' => $listingId]);
            logAdminAction($admin['id'], 'delete_listing', 'listings', $listingId);
            redirect('/admin/listings.php', 'Listing permanently deleted.', 'info');
        }
    }
}

// Filters & Query
$cityFilter = $_GET['city'] ?? '';
$statusFilter = $_GET['status'] ?? '';
$search = trim($_GET['search'] ?? '');

$where = ["1=1"];
$params = [];

if (!empty($cityFilter)) {
    $where[] = "l.city = :city";
    $params[':city'] = $cityFilter;
}

if (!empty($statusFilter)) {
    $where[] = "l.status = :status";
    $params[':status'] = $statusFilter;
}

if (!empty($search)) {
    $where[] = "(l.title LIKE :s OR u.full_name LIKE :s OR l.area LIKE :s)";
    $params[':s'] = "%{$search}%";
}

$whereClause = implode(" AND ", $where);
$stmt = $db->prepare("
    SELECT l.*, c.name as category_name, u.full_name as seller_name, u.mobile_number as seller_phone,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id ORDER BY is_primary DESC, id ASC LIMIT 1) as primary_image
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    JOIN users u ON l.user_id = u.id
    WHERE {$whereClause}
    ORDER BY l.id DESC
");
$stmt->execute($params);
$allListings = $stmt->fetchAll();
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
    <div>
        <h3 class="fw-bold mb-1">Manage All Product Ads</h3>
        <p class="text-muted small mb-0">Approve, reject, feature, or remove marketplace listings</p>
    </div>
</div>

<!-- Filters -->
<div class="card border-0 shadow-sm p-3 bg-white mb-4">
    <form method="GET" action="/admin/listings.php" class="row g-2 align-items-center">
        <div class="col-12 col-md-4">
            <input type="text" name="search" class="form-control form-control-sm" placeholder="Search title, seller, area..." value="<?= e($search) ?>">
        </div>
        <div class="col-6 col-md-3">
            <select name="city" class="form-select form-select-sm">
                <option value="">All Hubs</option>
                <option value="Sargodha" <?= $cityFilter === 'Sargodha' ? 'selected' : '' ?>>Sargodha</option>
                <option value="Shaheenabad" <?= $cityFilter === 'Shaheenabad' ? 'selected' : '' ?>>Shaheenabad</option>
                <option value="Sillanwali" <?= $cityFilter === 'Sillanwali' ? 'selected' : '' ?>>Sillanwali</option>
            </select>
        </div>
        <div class="col-6 col-md-3">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <option value="Published" <?= $statusFilter === 'Published' ? 'selected' : '' ?>>Published</option>
                <option value="Payment Pending" <?= $statusFilter === 'Payment Pending' ? 'selected' : '' ?>>Payment Pending</option>
                <option value="Suspended" <?= $statusFilter === 'Suspended' ? 'selected' : '' ?>>Suspended</option>
                <option value="Rejected" <?= $statusFilter === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
            </select>
        </div>
        <div class="col-12 col-md-2">
            <button type="submit" class="btn btn-dark btn-sm w-100">Filter</button>
        </div>
    </form>
</div>

<div class="card border-0 shadow-sm bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>Item</th>
                    <th>Seller & City</th>
                    <th>Category</th>
                    <th>Price</th>
                    <th>Status</th>
                    <th>Featured</th>
                    <th>Views</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($allListings as $item): ?>
                    <tr>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <img src="/<?= e($item['primary_image'] ?: 'assets/images/placeholder.jpg') ?>" 
                                     alt="Thumb" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;"
                                     onerror="this.src='/uploads/products/listing_smartphone_1790803033699.jpg'">
                                <div class="text-truncate" style="max-width: 200px;">
                                    <a href="/product.php?id=<?= $item['id'] ?>" target="_blank" class="fw-semibold text-dark text-decoration-none">
                                        <?= e($item['title']) ?>
                                    </a>
                                    <small class="text-muted d-block">ID #<?= $item['id'] ?></small>
                                </div>
                            </div>
                        </td>
                        <td>
                            <strong><?= e($item['seller_name']) ?></strong><br>
                            <small class="text-muted"><?= e($item['city']) ?> (<?= e($item['area']) ?>)</small>
                        </td>
                        <td><?= e($item['category_name']) ?></td>
                        <td class="fw-bold tabular-nums text-success"><?= formatPKR($item['price']) ?></td>
                        <td>
                            <?php if ($item['status'] === 'Published'): ?>
                                <span class="badge bg-success-subtle text-success">Published</span>
                            <?php elseif ($item['status'] === 'Payment Pending'): ?>
                                <span class="badge bg-warning-subtle text-warning">Payment Pending</span>
                            <?php else: ?>
                                <span class="badge bg-secondary-subtle text-secondary"><?= e($item['status']) ?></span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <form method="POST" action="/admin/listings.php" class="d-inline">
                                <?= getCsrfField() ?>
                                <input type="hidden" name="action" value="toggle_featured">
                                <input type="hidden" name="listing_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-sm py-0 px-1 border-0" title="Toggle Featured">
                                    <i class="bi <?= $item['is_featured'] ? 'bi-star-fill text-warning fs-5' : 'bi-star text-muted fs-5' ?>"></i>
                                </button>
                            </form>
                        </td>
                        <td class="tabular-nums text-muted"><?= $item['views_count'] ?></td>
                        <td class="text-end text-nowrap">
                            <a href="/product.php?id=<?= $item['id'] ?>" target="_blank" class="btn btn-outline-secondary btn-sm py-0 px-2" title="Preview">
                                <i class="bi bi-eye"></i>
                            </a>

                            <?php if ($item['status'] === 'Published'): ?>
                                <form method="POST" action="/admin/listings.php" class="d-inline">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="publish_telegram">
                                    <input type="hidden" name="listing_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-outline-info btn-sm py-0 px-2" title="Broadcast to Telegram Channel">
                                        <i class="bi bi-send text-info"></i>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <?php if ($item['status'] !== 'Published'): ?>
                                <form method="POST" action="/admin/listings.php" class="d-inline">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="approve">
                                    <input type="hidden" name="listing_id" value="<?= $item['id'] ?>">
                                    <button type="submit" class="btn btn-success btn-sm py-0 px-2" title="Publish">
                                        <i class="bi bi-check-lg"></i>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <form method="POST" action="/admin/listings.php" class="d-inline" onsubmit="return confirm('Permanently delete this listing?');">
                                <?= getCsrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="listing_id" value="<?= $item['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm py-0 px-2" title="Delete">
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
