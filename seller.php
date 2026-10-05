<?php
/**
 * Sargodha Mandi - Public Seller Profile
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$sellerId = (int)($_GET['id'] ?? 0);
if ($sellerId <= 0) {
    redirect('/index.php', 'Invalid seller ID.', 'danger');
}

$db = getDB();

// Fetch Seller Info
$stmt = $db->prepare("SELECT id, full_name, city, area, created_at, profile_image, role, status FROM users WHERE id = :id AND status = 'active' LIMIT 1");
$stmt->execute([':id' => $sellerId]);
$seller = $stmt->fetch();

if (!$seller) {
    redirect('/index.php', 'Seller not found or account is not active.', 'danger');
}

// Fetch Active Listings by this seller
$lStmt = $db->prepare("
    SELECT l.*, c.name as category_name,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id ORDER BY is_primary DESC, id ASC LIMIT 1) as primary_image
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    WHERE l.user_id = :uid AND l.status = 'Published'
    ORDER BY l.id DESC
");
$lStmt->execute([':uid' => $sellerId]);
$sellerListings = $lStmt->fetchAll();

// Fetch Rating Stats
$rateStmt = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE seller_id = :sid AND status = 'published'");
$rateStmt->execute([':sid' => $sellerId]);
$ratingData = $rateStmt->fetch();
$avgRating = $ratingData['avg_rating'] ? round((float)$ratingData['avg_rating'], 1) : null;
$totalReviews = (int)$ratingData['total_reviews'];

// Fetch Reviews
$revStmt = $db->prepare("
    SELECT r.*, u.full_name as reviewer_name, u.city as reviewer_city
    FROM reviews r
    JOIN users u ON r.reviewer_id = u.id
    WHERE r.seller_id = :sid AND r.status = 'published'
    ORDER BY r.id DESC
");
$revStmt->execute([':sid' => $sellerId]);
$reviews = $revStmt->fetchAll();

$pageTitle = $seller['full_name'] . ' - Seller Profile | Sargodha Mandi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="row g-4">
        <!-- Sidebar Profile -->
        <div class="col-12 col-md-4">
            <div class="card border shadow-sm p-4 text-center">
                <div class="mx-auto mb-3 bg-success text-white rounded-circle d-flex align-items-center justify-content-center fs-2 fw-bold" style="width: 80px; height: 80px;">
                    <?= strtoupper(substr($seller['full_name'], 0, 1)) ?>
                </div>

                <h4 class="fw-bold mb-1"><?= e($seller['full_name']) ?></h4>
                <div class="text-muted small mb-2">
                    <i class="bi bi-geo-alt-fill text-danger me-1"></i> <?= e($seller['area']) ?>, <strong><?= e($seller['city']) ?></strong>
                </div>

                <div class="mb-3">
                    <?php if ($avgRating): ?>
                        <span class="badge bg-warning text-dark px-2 py-1 fs-6">
                            ⭐ <?= $avgRating ?> / 5
                        </span>
                        <small class="text-muted d-block mt-1"><?= $totalReviews ?> community reviews</small>
                    <?php else: ?>
                        <span class="badge bg-light text-secondary border">No reviews yet</span>
                    <?php endif; ?>
                </div>

                <div class="border-top pt-3 text-start small text-muted">
                    <div class="d-flex justify-content-between mb-1">
                        <span>Member Since:</span>
                        <strong class="text-dark"><?= date('d M Y', strtotime($seller['created_at'])) ?></strong>
                    </div>
                    <div class="d-flex justify-content-between mb-1">
                        <span>Active Listings:</span>
                        <strong class="text-success"><?= count($sellerListings) ?> ads</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Account Status:</span>
                        <span class="badge bg-success-subtle text-success">Verified Active</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Content: Active Ads & Reviews -->
        <div class="col-12 col-md-8">
            <!-- Tabs -->
            <ul class="nav nav-pills mb-4" id="sellerTab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active fw-semibold" id="ads-tab" data-bs-toggle="pill" data-bs-target="#ads" type="button" role="tab">
                        Active Ads (<?= count($sellerListings) ?>)
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link fw-semibold" id="reviews-tab" data-bs-toggle="pill" data-bs-target="#reviews" type="button" role="tab">
                        Reviews & Ratings (<?= $totalReviews ?>)
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="sellerTabContent">
                <!-- Active Ads Tab -->
                <div class="tab-pane fade show active" id="ads" role="tabpanel">
                    <?php if (empty($sellerListings)): ?>
                        <div class="card border p-4 text-center text-muted">
                            No active ads from this seller at the moment.
                        </div>
                    <?php else: ?>
                        <div class="row row-cols-1 row-cols-sm-2 g-3">
                            <?php foreach ($sellerListings as $item): ?>
                                <div class="col">
                                    <div class="listing-card">
                                        <?php if ($item['is_featured']): ?>
                                            <span class="featured-ribbon">⭐ Featured</span>
                                        <?php endif; ?>
                                        <a href="/product.php?id=<?= $item['id'] ?>" class="listing-thumb-wrap">
                                            <img src="/<?= e($item['primary_image'] ?: 'assets/images/placeholder.jpg') ?>" 
                                                 alt="<?= e($item['title']) ?>" 
                                                 class="listing-thumb"
                                                 onerror="this.src='/uploads/products/listing_smartphone_1790803033699.jpg'">
                                        </a>
                                        <div class="listing-card-body">
                                            <div class="listing-price tabular-nums"><?= formatPKR($item['price']) ?></div>
                                            <a href="/product.php?id=<?= $item['id'] ?>" class="listing-title text-decoration-none">
                                                <?= e($item['title']) ?>
                                            </a>
                                            <div class="small text-muted mb-2">
                                                <i class="bi bi-tag"></i> <?= e($item['category_name']) ?> · <?= e($item['item_condition']) ?>
                                            </div>
                                            <div class="listing-meta border-top pt-2">
                                                <span><i class="bi bi-geo-alt text-success"></i> <?= e($item['city']) ?></span>
                                                <span><?= timeAgo($item['created_at']) ?></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Reviews Tab -->
                <div class="tab-pane fade" id="reviews" role="tabpanel">
                    <div class="card border shadow-sm p-4">
                        <h6 class="fw-bold mb-3">All Feedback for <?= e($seller['full_name']) ?></h6>
                        <?php if (empty($reviews)): ?>
                            <p class="text-muted small mb-0">No reviews available.</p>
                        <?php else: ?>
                            <div class="d-flex flex-column gap-3">
                                <?php foreach ($reviews as $rev): ?>
                                    <div class="border-bottom pb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <div>
                                                <strong><?= e($rev['reviewer_name']) ?></strong>
                                                <span class="text-muted small">· <?= e($rev['reviewer_city']) ?></span>
                                            </div>
                                            <div class="text-warning">
                                                <?= str_repeat('⭐', (int)$rev['rating']) ?>
                                            </div>
                                        </div>
                                        <p class="small text-secondary mb-1"><?= e($rev['review_text']) ?></p>
                                        <small class="text-muted"><?= date('d M Y', strtotime($rev['created_at'])) ?></small>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
