<?php
/**
 * Sargodha Mandi - Homepage
 * Sargodha | Shaheenabad | Sillanwali Local Marketplace
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Sargodha Mandi - Buy & Sell in Sargodha, Shaheenabad & Sillanwali';
$db = getDB();

// Fetch Active Categories
$catStmt = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC LIMIT 12");
$categories = $catStmt->fetchAll();

// Fetch Featured Listings
$featStmt = $db->query("
    SELECT l.*, c.name as category_name, u.full_name as seller_name,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id ORDER BY is_primary DESC, id ASC LIMIT 1) as primary_image
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    JOIN users u ON l.user_id = u.id
    WHERE l.status = 'Published' AND l.is_featured = 1
    ORDER BY l.id DESC
    LIMIT 4
");
$featuredListings = $featStmt->fetchAll();

// Fetch Latest Published Listings
$latestStmt = $db->query("
    SELECT l.*, c.name as category_name, u.full_name as seller_name,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id ORDER BY is_primary DESC, id ASC LIMIT 1) as primary_image
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    JOIN users u ON l.user_id = u.id
    WHERE l.status = 'Published'
    ORDER BY l.id DESC
    LIMIT 8
");
$latestListings = $latestStmt->fetchAll();

// Total counts
$totalAds = (int)$db->query("SELECT COUNT(*) FROM listings WHERE status = 'Published'")->fetchColumn();

require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <!-- Hero Section with Search -->
    <div class="hero-banner position-relative">
        <img src="/uploads/products/hero_sargodha_bazaar_1790802971175.jpg" alt="Sargodha Market Bazaar" class="hero-bg-img" onerror="this.style.display='none'">
        <div class="hero-content">
            <span class="badge bg-success bg-opacity-75 text-white mb-2 px-3 py-1">
                <i class="bi bi-geo-alt-fill me-1"></i> Sargodha · Shaheenabad · Sillanwali
            </span>
            <h1 class="display-6 fw-bold mb-2 text-white">Find Authentic Deals from Verified Local Sellers</h1>
            <p class="lead fs-6 text-white-50 mb-4">
                Buy and sell livestock, citrus crops & wanda, Honda 125s, mobiles, property, and services directly within your local community.
            </p>

            <!-- Search Bar Form -->
            <form action="/search.php" method="GET" class="bg-white p-2 rounded shadow-lg">
                <div class="row g-2 align-items-center">
                    <div class="col-12 col-md-5">
                        <div class="input-group">
                            <span class="input-group-text bg-white border-0 text-muted"><i class="bi bi-search"></i></span>
                            <input type="text" name="q" class="form-control border-0 shadow-none ps-0" placeholder="Search mobiles, cows, wanda, bikes...">
                        </div>
                    </div>
                    <div class="col-6 col-md-3 border-start">
                        <select name="city" class="form-select border-0 shadow-none">
                            <option value="">All 3 Hubs</option>
                            <option value="Sargodha">Sargodha</option>
                            <option value="Shaheenabad">Shaheenabad</option>
                            <option value="Sillanwali">Sillanwali</option>
                        </select>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="d-flex gap-2">
                            <select name="category" class="form-select border-0 shadow-none d-none d-lg-block">
                                <option value="">All Categories</option>
                                <?php foreach ($categories as $cat): ?>
                                    <option value="<?= e($cat['slug']) ?>"><?= e($cat['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                            <button type="submit" class="btn btn-primary-green px-4 text-nowrap w-100">
                                Search Ads
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Quick Category Bubbles -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="fw-bold mb-0">Popular Categories</h5>
            <a href="/categories.php" class="text-success text-decoration-none small fw-semibold">View All 15 Categories &rarr;</a>
        </div>
        <div class="row row-cols-2 row-cols-sm-3 row-cols-md-4 row-cols-lg-6 g-3">
            <?php foreach ($categories as $cat): ?>
                <div class="col">
                    <a href="/search.php?category=<?= urlencode($cat['slug']) ?>" class="category-card h-100">
                        <i class="bi <?= e($cat['icon'] ?: 'bi-tag') ?> category-icon"></i>
                        <span class="small fw-semibold text-truncate w-100"><?= e($cat['name']) ?></span>
                    </a>
                </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- Featured Listings Section -->
    <?php if (!empty($featuredListings)): ?>
        <div class="mb-5">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <div>
                    <h5 class="fw-bold mb-0 text-dark">
                        <span class="text-warning"><i class="bi bi-star-fill"></i></span> Featured Listings
                    </h5>
                    <small class="text-muted">Promoted by verified local dealers in Sargodha & surrounding areas</small>
                </div>
                <a href="/search.php?featured=1" class="text-success text-decoration-none small fw-semibold">View All Featured &rarr;</a>
            </div>

            <div class="row g-3">
                <?php foreach ($featuredListings as $item): ?>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="listing-card">
                            <span class="featured-ribbon">⭐ Featured</span>
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
                                    <span class="text-truncate" style="max-width: 140px;">
                                        <i class="bi bi-geo-alt text-success"></i> <?= e($item['city']) ?>
                                    </span>
                                    <span><?= timeAgo($item['created_at']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Latest Listings Section -->
    <div class="mb-5">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <div>
                <h5 class="fw-bold mb-0">Latest Listings</h5>
                <small class="text-muted">Recent ads posted across Sargodha, Shaheenabad & Sillanwali</small>
            </div>
            <a href="/search.php" class="text-success text-decoration-none small fw-semibold">See More (<?= $totalAds ?>) &rarr;</a>
        </div>

        <?php if (empty($latestListings)): ?>
            <div class="card border p-5 text-center bg-white">
                <i class="bi bi-box-seam fs-1 text-muted mb-2"></i>
                <h6>No listings published yet.</h6>
                <p class="text-muted small">Be the first to list an item in Sargodha!</p>
                <div class="mt-2">
                    <a href="/post-ad.php" class="btn btn-primary-green btn-sm">Post First Ad</a>
                </div>
            </div>
        <?php else: ?>
            <div class="row g-3">
                <?php foreach ($latestListings as $item): ?>
                    <div class="col-12 col-sm-6 col-lg-3">
                        <div class="listing-card">
                            <?php if ($item['is_featured']): ?>
                                <span class="featured-ribbon">⭐ Featured</span>
                            <?php endif; ?>
                            <a href="/product.php?id=<?= $item['id'] ?>" class="listing-thumb-wrap">
                                <img src="/<?= e($item['primary_image'] ?: 'assets/images/placeholder.jpg') ?>" 
                                     alt="<?= e($item['title']) ?>" 
                                     class="listing-thumb"
                                     onerror="this.src='/uploads/products/listing_motorcycle_1790803020640.jpg'">
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
                                    <span class="text-truncate" style="max-width: 140px;">
                                        <i class="bi bi-geo-alt text-success"></i> <?= e($item['city']) ?>
                                    </span>
                                    <span><?= timeAgo($item['created_at']) ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- How It Works & Trust Information -->
    <div class="bg-white border rounded p-4 p-md-5 mb-5 shadow-sm">
        <div class="row align-items-center mb-4">
            <div class="col-12 col-md-8">
                <span class="text-uppercase text-success fw-bold small">Transparent Local Commerce</span>
                <h3 class="fw-bold mt-1">How Sargodha Mandi Works</h3>
                <p class="text-muted mb-0">
                    A secure, scam-free local marketplace. Account registration is 100% free; an affordable Rs. 500 fee is required per product listing to guarantee real sellers and verified manual approval.
                </p>
            </div>
            <div class="col-12 col-md-4 text-md-end mt-3 mt-md-0">
                <a href="/post-ad.php" class="btn btn-primary-green px-4 py-2">
                    <i class="bi bi-plus-circle me-1"></i> Post Your Ad (Rs. 500)
                </a>
            </div>
        </div>

        <div class="row g-3">
            <div class="col-12 col-md-4">
                <div class="trust-step">
                    <div class="step-num">1</div>
                    <h6 class="fw-bold">Free Account & Ad Details</h6>
                    <p class="small text-muted mb-0">Create your free account with your mobile number. Fill in title, photos, price, condition, and specify your area in Sargodha, Shaheenabad or Sillanwali.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="trust-step">
                    <div class="step-num">2</div>
                    <h6 class="fw-bold">Pay Rs. 500 Listing Fee</h6>
                    <p class="small text-muted mb-0">Send Rs. 500 to official number <strong>03127453108</strong> (Muhammad Akram Tayyab) via EasyPaisa or JazzCash. Submit your Transaction ID and screenshot.</p>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="trust-step">
                    <div class="step-num">3</div>
                    <h6 class="fw-bold">Admin Verifies & Publishes</h6>
                    <p class="small text-muted mb-0">Our local team checks the payment and content. Once approved, your ad is published instantly and local buyers can call, WhatsApp, or chat with you!</p>
                </div>
            </div>
        </div>

        <div class="mt-4 p-3 bg-light rounded border small text-muted d-flex align-items-center gap-3">
            <i class="bi bi-shield-exclamation text-warning fs-3"></i>
            <div>
                <strong>Important Buyer Safety Notice:</strong> Always meet sellers in safe public locations (such as Kutchery Bazaar, Trust Plaza, Canal Colony, or local Police Khidmat Markaz). Verify products, bio-metrics, and livestock in person before handing over money.
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
