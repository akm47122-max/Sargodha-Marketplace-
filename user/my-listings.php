<?php
/**
 * Sargodha Mandi - My Listings Management
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user = currentUser();
$db = getDB();

// Handle Delete Action
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    verifyCsrfOrDie();
    $deleteId = (int)$_POST['listing_id'];
    $delStmt = $db->prepare("DELETE FROM listings WHERE id = :id AND user_id = :uid");
    $delStmt->execute([':id' => $deleteId, ':uid' => $user['id']]);
    redirect('/user/my-listings.php', 'Listing deleted successfully.', 'info');
}

// Handle Status Toggle (Pause / Resume)
if (isset($_POST['action']) && $_POST['action'] === 'toggle_pause') {
    verifyCsrfOrDie();
    $toggleId = (int)$_POST['listing_id'];
    $stmt = $db->prepare("SELECT status FROM listings WHERE id = :id AND user_id = :uid LIMIT 1");
    $stmt->execute([':id' => $toggleId, ':uid' => $user['id']]);
    $currentStatus = $stmt->fetchColumn();

    if ($currentStatus === 'Published') {
        $db->prepare("UPDATE listings SET status = 'Suspended' WHERE id = :id AND user_id = :uid")
           ->execute([':id' => $toggleId, ':uid' => $user['id']]);
        redirect('/user/my-listings.php', 'Listing has been paused.', 'warning');
    } elseif ($currentStatus === 'Suspended') {
        $db->prepare("UPDATE listings SET status = 'Published' WHERE id = :id AND user_id = :uid")
           ->execute([':id' => $toggleId, ':uid' => $user['id']]);
        redirect('/user/my-listings.php', 'Listing has been resumed and is now live!', 'success');
    }
}

// Filter by status if requested
$filterStatus = $_GET['status'] ?? '';
$where = ["l.user_id = :uid"];
$params = [':uid' => $user['id']];

if (!empty($filterStatus)) {
    $where[] = "l.status = :status";
    $params[':status'] = $filterStatus;
}

$whereClause = implode(" AND ", $where);
$stmt = $db->prepare("
    SELECT l.*, c.name as category_name,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id ORDER BY is_primary DESC, id ASC LIMIT 1) as primary_image,
           (SELECT status FROM listing_payments WHERE listing_id = l.id ORDER BY id DESC LIMIT 1) as payment_status,
           (SELECT admin_note FROM listing_payments WHERE listing_id = l.id ORDER BY id DESC LIMIT 1) as payment_note
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    WHERE {$whereClause}
    ORDER BY l.id DESC
");
$stmt->execute($params);
$myAds = $stmt->fetchAll();

$pageTitle = 'My Ads - Sargodha Mandi';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold mb-1">My Product Listings</h3>
            <p class="text-muted small mb-0">Manage, edit, pause, and check verification statuses of your ads</p>
        </div>
        <div>
            <a href="/post-ad.php" class="btn btn-primary-green btn-sm px-3 py-2 fw-semibold">
                <i class="bi bi-plus-circle me-1"></i> Post New Ad (Rs. 500)
            </a>
        </div>
    </div>

    <!-- Filter Tabs -->
    <div class="d-flex flex-wrap gap-2 mb-4">
        <a href="/user/my-listings.php" class="btn btn-sm <?= empty($filterStatus) ? 'btn-dark' : 'btn-outline-secondary' ?>">
            All Ads
        </a>
        <a href="/user/my-listings.php?status=Published" class="btn btn-sm <?= $filterStatus === 'Published' ? 'btn-success' : 'btn-outline-secondary' ?>">
            Published (Live)
        </a>
        <a href="/user/my-listings.php?status=Payment Pending" class="btn btn-sm <?= $filterStatus === 'Payment Pending' ? 'btn-warning' : 'btn-outline-secondary' ?>">
            Payment Pending
        </a>
        <a href="/user/my-listings.php?status=Suspended" class="btn btn-sm <?= $filterStatus === 'Suspended' ? 'btn-secondary' : 'btn-outline-secondary' ?>">
            Paused
        </a>
        <a href="/user/my-listings.php?status=Rejected" class="btn btn-sm <?= $filterStatus === 'Rejected' ? 'btn-danger' : 'btn-outline-secondary' ?>">
            Rejected
        </a>
    </div>

    <?php if (empty($myAds)): ?>
        <div class="card border p-5 text-center bg-white shadow-sm">
            <i class="bi bi-card-list fs-1 text-muted mb-2"></i>
            <h5 class="fw-bold">No ads found in this tab</h5>
            <p class="text-muted small">You don't have any ads matching this filter.</p>
            <div class="mt-2">
                <a href="/post-ad.php" class="btn btn-primary-green btn-sm">Post an Ad Now</a>
            </div>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($myAds as $ad): ?>
                <div class="card border shadow-sm bg-white p-3">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-sm-2 text-center text-sm-start">
                            <img src="/<?= e($ad['primary_image'] ?: 'assets/images/placeholder.jpg') ?>" 
                                 alt="<?= e($ad['title']) ?>" class="rounded w-100" style="max-height: 110px; object-fit: cover;"
                                 onerror="this.src='/uploads/products/listing_smartphone_1790803033699.jpg'">
                        </div>

                        <div class="col-12 col-sm-6">
                            <div class="small text-muted mb-1">
                                <?= e($ad['category_name']) ?> · <i class="bi bi-geo-alt"></i> <?= e($ad['city']) ?> (<?= e($ad['area']) ?>)
                            </div>
                            <h5 class="fw-bold mb-1">
                                <a href="/product.php?id=<?= $ad['id'] ?>" class="text-dark text-decoration-none">
                                    <?= e($ad['title']) ?>
                                </a>
                            </h5>
                            <div class="text-success fw-bold tabular-nums fs-6 mb-2">
                                <?= formatPKR($ad['price']) ?>
                            </div>

                            <div class="d-flex flex-wrap align-items-center gap-2 small">
                                <?php if ($ad['status'] === 'Published'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i> Live on Marketplace
                                    </span>
                                <?php elseif ($ad['status'] === 'Payment Pending'): ?>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                        <i class="bi bi-hourglass-split me-1"></i> Payment Pending (Rs. 500)
                                    </span>
                                <?php elseif ($ad['status'] === 'Suspended'): ?>
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        <i class="bi bi-pause-circle me-1"></i> Paused
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                        <?= e($ad['status']) ?>
                                    </span>
                                <?php endif; ?>

                                <?php if ($ad['is_featured']): ?>
                                    <span class="badge bg-warning text-dark">⭐ Featured</span>
                                <?php endif; ?>

                                <span class="text-muted"><i class="bi bi-eye"></i> <?= $ad['views_count'] ?> views</span>
                                <span class="text-muted">· Posted <?= timeAgo($ad['created_at']) ?></span>
                            </div>

                            <?php if ($ad['payment_status'] === 'rejected'): ?>
                                <div class="alert alert-danger p-2 small mt-2 mb-0">
                                    <strong>Fee Rejected:</strong> <?= e($ad['payment_note'] ?: 'Please correct your transaction details.') ?>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="col-12 col-sm-4 text-sm-end d-flex flex-sm-column gap-2 justify-content-end">
                            <?php if ($ad['status'] === 'Payment Pending' || $ad['payment_status'] === 'rejected'): ?>
                                <a href="/user/submit-payment.php?listing_id=<?= $ad['id'] ?>" class="btn btn-warning btn-sm fw-bold">
                                    <i class="bi bi-wallet2 me-1"></i> Submit Payment (Rs. 500)
                                </a>
                            <?php endif; ?>

                            <a href="/product.php?id=<?= $ad['id'] ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-eye me-1"></i> View Ad
                            </a>

                            <a href="/user/edit-listing.php?id=<?= $ad['id'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil-square me-1"></i> Edit Ad
                            </a>

                            <!-- Pause / Resume Toggle -->
                            <?php if (in_array($ad['status'], ['Published', 'Suspended'])): ?>
                                <form method="POST" action="/user/my-listings.php" class="d-inline">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="toggle_pause">
                                    <input type="hidden" name="listing_id" value="<?= $ad['id'] ?>">
                                    <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                                        <?= $ad['status'] === 'Published' ? '<i class="bi bi-pause"></i> Pause Ad' : '<i class="bi bi-play"></i> Resume Ad' ?>
                                    </button>
                                </form>
                            <?php endif; ?>

                            <!-- Delete Form -->
                            <form method="POST" action="/user/my-listings.php" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this ad?');">
                                <?= getCsrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="listing_id" value="<?= $ad['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
