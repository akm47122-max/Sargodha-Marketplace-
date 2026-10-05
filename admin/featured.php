<?php
/**
 * SARGODHAMART - Admin Portal: Featured / Promoted Listings
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/admin_check.php';

$admin = currentUser();
$db = getDB();

// Handle Actions (Feature / Unfeature / Extend)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();

    $action = $_POST['action'] ?? '';
    $listingId = (int)($_POST['listing_id'] ?? 0);

    if ($action === 'unfeature' && $listingId > 0) {
        $db->prepare("UPDATE listings SET is_featured = 0 WHERE id = :id")->execute([':id' => $listingId]);
        $db->prepare("DELETE FROM featured_listings WHERE listing_id = :id")->execute([':id' => $listingId]);
        logAdminAction($admin['id'], 'unfeature_listing', 'listing', $listingId, 'Admin removed featured status');
        redirect('/admin/featured.php', 'Listing removed from featured section.', 'success');
    } elseif ($action === 'feature' && $listingId > 0) {
        $days = (int)($_POST['days'] ?? 15);
        $db->prepare("UPDATE listings SET is_featured = 1 WHERE id = :id")->execute([':id' => $listingId]);
        $db->prepare("
            INSERT INTO featured_listings (listing_id, start_date, end_date, created_at)
            VALUES (:id, NOW(), DATE_ADD(NOW(), INTERVAL :days DAY), NOW())
            ON DUPLICATE KEY UPDATE end_date = DATE_ADD(NOW(), INTERVAL :days DAY)
        ")->execute([':id' => $listingId, ':days' => $days]);
        logAdminAction($admin['id'], 'feature_listing', 'listing', $listingId, "Promoted to featured for {$days} days");
        redirect('/admin/featured.php', 'Listing promoted to featured section successfully!', 'success');
    }
}

// Fetch currently featured listings
$featuredListings = $db->query("
    SELECT l.*, u.full_name as seller_name, u.mobile_number as seller_phone, c.name as category_name,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id ORDER BY is_primary DESC, id ASC LIMIT 1) as cover_image
    FROM listings l
    JOIN users u ON l.user_id = u.id
    JOIN categories c ON l.category_id = c.id
    WHERE l.is_featured = 1
    ORDER BY l.id DESC
")->fetchAll();

// Fetch published normal listings available for promotion
$eligibleListings = $db->query("
    SELECT l.id, l.title, l.price, l.city, u.full_name as seller_name
    FROM listings l
    JOIN users u ON l.user_id = u.id
    WHERE l.status = 'published' AND l.is_featured = 0
    ORDER BY l.id DESC LIMIT 50
")->fetchAll();

$pageTitle = 'Featured Listings - ' . SITE_NAME . ' Admin';
require_once __DIR__ . '/includes/header.php';
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Featured & Promoted Ads</h3>
        <p class="text-muted small mb-0">Promote listings to top carousel and high-visibility placements</p>
    </div>
    <span class="badge bg-warning text-dark px-3 py-2 fs-6">
        <i class="bi bi-star-fill me-1"></i> <?= count($featuredListings) ?> Currently Featured
    </span>
</div>

<!-- Promo Pricing Notice -->
<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="card border rounded-3 p-3 bg-white shadow-sm">
            <span class="text-muted small">Standard Feature Price</span>
            <h4 class="fw-bold text-success mb-1">Rs. 1,000 PKR</h4>
            <span class="text-secondary small">15 Days Homepage Top Sticky</span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border rounded-3 p-3 bg-white shadow-sm">
            <span class="text-muted small">Seller Activation Rule</span>
            <h4 class="fw-bold text-dark mb-1">Rs. 500 (One-Time)</h4>
            <span class="text-secondary small">Normal Ads are ALWAYS Free</span>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border rounded-3 p-3 bg-white shadow-sm">
            <span class="text-muted small">Promoted Impressions</span>
            <h4 class="fw-bold text-primary mb-1">5x Views</h4>
            <span class="text-secondary small">Highlighted across Sargodha District</span>
        </div>
    </div>
</div>

<!-- Quick Feature an Existing Ad -->
<div class="card border rounded-3 p-4 bg-white shadow-sm mb-4">
    <h5 class="fw-bold mb-3"><i class="bi bi-plus-circle text-success me-1"></i> Feature a Published Listing</h5>
    <form method="POST" action="/admin/featured.php" class="row g-3 align-items-end">
        <?= getCsrfField() ?>
        <input type="hidden" name="action" value="feature">

        <div class="col-12 col-md-6">
            <label class="form-label small fw-bold">Select Eligible Listing</label>
            <select name="listing_id" class="form-select" required>
                <option value="">-- Choose a published listing --</option>
                <?php foreach ($eligibleListings as $el): ?>
                    <option value="<?= $el['id'] ?>">
                        #<?= $el['id'] ?> - <?= e($el['title']) ?> (Rs. <?= number_format($el['price']) ?> - <?= e($el['city']) ?>) - Seller: <?= e($el['seller_name']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="col-6 col-md-3">
            <label class="form-label small fw-bold">Duration (Days)</label>
            <select name="days" class="form-select">
                <option value="7">7 Days</option>
                <option value="15" selected>15 Days</option>
                <option value="30">30 Days</option>
            </select>
        </div>

        <div class="col-6 col-md-3">
            <button type="submit" class="btn btn-warning fw-bold w-100">
                <i class="bi bi-star-fill me-1"></i> Mark as Featured
            </button>
        </div>
    </form>
</div>

<!-- Active Featured Ads Table -->
<div class="card border rounded-3 bg-white shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0">Currently Featured Listings</h5>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0 small">
            <thead class="table-light">
                <tr>
                    <th>Ad</th>
                    <th>Price</th>
                    <th>Category</th>
                    <th>Location</th>
                    <th>Seller</th>
                    <th>Views</th>
                    <th class="text-end">Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($featuredListings)): ?>
                    <tr>
                        <td colspan="7" class="text-center py-4 text-muted">No listings currently marked as featured.</td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($featuredListings as $fl): ?>
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-2">
                                    <img src="<?= e($fl['cover_image'] ?: '/assets/images/placeholder.jpg') ?>" class="rounded" style="width: 45px; height: 45px; object-fit: cover;">
                                    <div>
                                        <a href="/product.php?id=<?= $fl['id'] ?>" target="_blank" class="fw-bold text-dark text-decoration-none">
                                            <?= e($fl['title']) ?>
                                        </a>
                                        <div class="text-muted" style="font-size: 0.7rem;">ID: #<?= $fl['id'] ?></div>
                                    </div>
                                </div>
                            </td>
                            <td class="fw-bold text-success">Rs. <?= number_format($fl['price']) ?></td>
                            <td><?= e($fl['category_name']) ?></td>
                            <td><span class="badge bg-light text-dark border"><?= e($fl['city']) ?></span></td>
                            <td><?= e($fl['seller_name']) ?></td>
                            <td><span class="badge bg-secondary"><?= (int)$fl['views'] ?> views</span></td>
                            <td class="text-end">
                                <form method="POST" action="/admin/featured.php" onsubmit="return confirm('Remove this listing from featured section?');" class="d-inline">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="unfeature">
                                    <input type="hidden" name="listing_id" value="<?= $fl['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2">
                                        <i class="bi bi-x-circle me-1"></i> Unfeature
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
