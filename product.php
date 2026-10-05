<?php
/**
 * SARGODHAMART - Product Details & Social Media Sharing
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('/index.php', 'Invalid product listing ID.', 'danger');
}

$db = getDB();
$currentUser = currentUser();

// Increment views count safely
try {
    $db->prepare("UPDATE listings SET views_count = views_count + 1 WHERE id = :id")->execute([':id' => $id]);
} catch (Exception $e) {}

// Fetch Listing Details
$stmt = $db->prepare("
    SELECT l.*, c.name as category_name, c.slug as category_slug, sc.name as subcategory_name,
           u.id as seller_id, u.full_name as seller_name, u.city as seller_city, u.area as seller_area,
           u.created_at as seller_joined, u.profile_image as seller_avatar
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    LEFT JOIN subcategories sc ON l.subcategory_id = sc.id
    JOIN users u ON l.user_id = u.id
    WHERE l.id = :id
    LIMIT 1
");
$stmt->execute([':id' => $id]);
$listing = $stmt->fetch();

if (!$listing) {
    redirect('/index.php', 'Listing not found.', 'danger');
}

$isOwner = $currentUser && $currentUser['id'] == $listing['user_id'];
$isSiteAdmin = isAdmin();

if ($listing['status'] !== 'published' && !$isOwner && !$isSiteAdmin) {
    redirect('/index.php', 'This listing is pending moderation or inactive.', 'warning');
}

// Fetch Images
$imgStmt = $db->prepare("SELECT * FROM listing_images WHERE listing_id = :id ORDER BY is_primary DESC, id ASC");
$imgStmt->execute([':id' => $id]);
$images = $imgStmt->fetchAll();

// Rating & Review summary
$rateStmt = $db->prepare("SELECT AVG(rating) as avg_rating, COUNT(*) as total_reviews FROM reviews WHERE seller_id = :sid AND status = 'published'");
$rateStmt->execute([':sid' => $listing['seller_id']]);
$ratingData = $rateStmt->fetch();
$avgRating = $ratingData['avg_rating'] ? round((float)$ratingData['avg_rating'], 1) : null;
$totalReviews = (int)$ratingData['total_reviews'];

// Reviews
$revStmt = $db->prepare("
    SELECT r.*, u.full_name as reviewer_name, u.city as reviewer_city
    FROM reviews r
    JOIN users u ON r.reviewer_id = u.id
    WHERE r.seller_id = :sid AND r.status = 'published'
    ORDER BY r.id DESC LIMIT 10
");
$revStmt->execute([':sid' => $listing['seller_id']]);
$reviews = $revStmt->fetchAll();

// Favorite check
$isFavorited = false;
if ($currentUser) {
    $favCheck = $db->prepare("SELECT 1 FROM favorites WHERE user_id = :uid AND listing_id = :lid");
    $favCheck->execute([':uid' => $currentUser['id'], ':lid' => $id]);
    $isFavorited = (bool)$favCheck->fetchColumn();
}

// Clean contact numbers
$cleanPhone = preg_replace('/[^0-9]/', '', $listing['phone_number']);
$cleanWhatsApp = preg_replace('/[^0-9]/', '', $listing['whatsapp_number']);
if (str_starts_with($cleanWhatsApp, '0')) {
    $cleanWhatsApp = '92' . substr($cleanWhatsApp, 1);
}

$currentUrl = SITE_URL . "/product.php?id=" . $listing['id'];
$shareText = "{$listing['title']} for Sale in {$listing['city']}\nPrice: " . formatPKR($listing['price']) . "\nLocation: {$listing['area']}, {$listing['city']}\n\nView on SARGODHAMART:\n{$currentUrl}";
$encodedShareText = urlencode($shareText);
$encodedUrl = urlencode($currentUrl);

// 6. Dynamic Product SEO & OpenGraph Metadata
$siteBrand = getSetting('website_name', 'SargodhaMart');
$pageTitle = "{$listing['title']} – " . formatPKR($listing['price']) . " | {$siteBrand}";
$metaDescription = mb_strimwidth(strip_tags($listing['description']), 0, 160, '...');
$canonicalUrl = $currentUrl;
$ogTitle = "{$listing['title']} – " . formatPKR($listing['price']) . " | {$siteBrand}";
$ogDescription = $metaDescription;
$ogUrl = $canonicalUrl;
$ogType = 'product';
if (!empty($images)) {
    $ogImage = str_starts_with($images[0]['image_path'], 'http') ? $images[0]['image_path'] : SITE_URL . '/' . ltrim($images[0]['image_path'], '/');
} else {
    $ogImage = getSetting('og_image_url', '');
}
$twitterTitle = $ogTitle;
$twitterDescription = $ogDescription;
$twitterImage = $ogImage;

require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-3">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="/index.php" class="text-decoration-none">Home</a></li>
            <li class="breadcrumb-item"><a href="/search.php?category=<?= urlencode($listing['category_slug']) ?>" class="text-decoration-none"><?= e($listing['category_name']) ?></a></li>
            <li class="breadcrumb-item"><a href="/search.php?city=<?= urlencode($listing['city']) ?>" class="text-decoration-none"><?= e($listing['city']) ?></a></li>
            <li class="breadcrumb-item active text-truncate" style="max-width: 300px;"><?= e($listing['title']) ?></li>
        </ol>
    </nav>

    <div class="row g-4">
        <!-- Left: Image Gallery & Description -->
        <div class="col-12 col-lg-8">
            <div class="bg-white border rounded p-3 shadow-sm mb-4">
                <?php $mainImg = !empty($images) ? $images[0]['image_path'] : 'assets/images/placeholder.jpg'; ?>
                <div class="product-gallery-main">
                    <img id="main_gallery_image" src="/<?= e($mainImg) ?>" alt="<?= e($listing['title']) ?>" onerror="this.src='/uploads/products/listing_smartphone_1790803033699.jpg'">
                </div>

                <?php if (count($images) > 1): ?>
                    <div class="product-thumbs-grid">
                        <?php foreach ($images as $idx => $img): ?>
                            <div class="product-thumb-item <?= $idx === 0 ? 'active' : '' ?>" data-src="/<?= e($img['image_path']) ?>">
                                <img src="/<?= e($img['image_path']) ?>" alt="Thumbnail">
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Product Details -->
            <div class="bg-white border rounded p-4 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-start gap-2 mb-3">
                    <div>
                        <div class="small text-muted mb-1">
                            <span class="fw-bold text-success"><?= e($listing['category_name']) ?></span>
                            <?php if ($listing['subcategory_name']): ?>
                                <span> / <?= e($listing['subcategory_name']) ?></span>
                            <?php endif; ?>
                            <span> · Posted <?= timeAgo($listing['created_at']) ?></span>
                        </div>
                        <h3 class="fw-bold mb-1"><?= e($listing['title']) ?></h3>
                        <div class="text-muted small">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i>
                            <?= e($listing['area']) ?>, <strong><?= e($listing['city']) ?></strong>
                            <?php if ($listing['exact_location']): ?>
                                <span>(<?= e($listing['exact_location']) ?>)</span>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Action buttons: Save & Share -->
                    <div class="d-flex gap-2">
                        <button class="btn <?= $isFavorited ? 'btn-danger text-white' : 'btn-outline-danger' ?> btn-fav-toggle btn-sm" 
                                data-listing-id="<?= $listing['id'] ?>" title="Save Favorite">
                            <i class="bi <?= $isFavorited ? 'bi-heart-fill' : 'bi-heart' ?>"></i>
                            <span class="d-none d-sm-inline ms-1"><?= $isFavorited ? 'Saved' : 'Save' ?></span>
                        </button>

                        <!-- Share Button Trigger Modal -->
                        <button type="button" class="btn btn-outline-secondary btn-sm" data-bs-toggle="modal" data-bs-target="#shareModal">
                            <i class="bi bi-share-fill me-1"></i> Share
                        </button>
                    </div>
                </div>

                <hr>

                <div class="row g-3 py-2 text-center text-sm-start mb-3">
                    <div class="col-4">
                        <div class="p-2 bg-light rounded border">
                            <small class="text-muted d-block">Condition</small>
                            <strong><?= e($listing['item_condition']) ?></strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-light rounded border">
                            <small class="text-muted d-block">Location Hub</small>
                            <strong><?= e($listing['city']) ?></strong>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="p-2 bg-light rounded border">
                            <small class="text-muted d-block">Total Views</small>
                            <strong class="tabular-nums"><?= $listing['views_count'] ?></strong>
                        </div>
                    </div>
                </div>

                <h5 class="fw-bold mt-4 mb-2">Description</h5>
                <div class="text-secondary lh-lg" style="white-space: pre-line;">
                    <?= e($listing['description']) ?>
                </div>

                <div class="d-flex justify-content-between align-items-center mt-4 pt-3 border-top">
                    <span class="text-muted small">Ad ID: #SM-<?= $listing['id'] ?></span>
                    <button type="button" class="btn btn-outline-secondary btn-sm text-danger" data-bs-toggle="modal" data-bs-target="#reportModal">
                        <i class="bi bi-flag me-1"></i> Report this Ad
                    </button>
                </div>
            </div>

            <!-- Reviews Section -->
            <div class="bg-white border rounded p-4 shadow-sm mb-4">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Seller Reviews & Feedback</h5>
                    <?php if ($avgRating): ?>
                        <div class="text-warning fw-bold fs-6">
                            ⭐ <?= $avgRating ?> / 5 (<?= $totalReviews ?> reviews)
                        </div>
                    <?php endif; ?>
                </div>

                <?php if ($currentUser && $currentUser['id'] != $listing['seller_id']): ?>
                    <div class="p-3 bg-light rounded border mb-4">
                        <h6 class="fw-bold mb-2">Leave a Review for this Seller</h6>
                        <form action="/api/review.php" method="POST">
                            <?= getCsrfField() ?>
                            <input type="hidden" name="seller_id" value="<?= $listing['seller_id'] ?>">
                            <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">

                            <div class="row g-2 mb-2">
                                <div class="col-12 col-sm-4">
                                    <select name="rating" class="form-select form-select-sm" required>
                                        <option value="5">⭐⭐⭐⭐⭐ (5 - Excellent)</option>
                                        <option value="4">⭐⭐⭐⭐ (4 - Very Good)</option>
                                        <option value="3">⭐⭐⭐ (3 - Average)</option>
                                        <option value="2">⭐⭐ (2 - Fair)</option>
                                        <option value="1">⭐ (1 - Poor)</option>
                                    </select>
                                </div>
                                <div class="col-12 col-sm-8">
                                    <input type="text" name="review_text" class="form-control form-control-sm" placeholder="Write feedback regarding dealing..." required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary-green btn-sm px-3">Submit Review</button>
                        </form>
                    </div>
                <?php endif; ?>

                <?php if (empty($reviews)): ?>
                    <p class="text-muted small mb-0">No reviews yet for this seller.</p>
                <?php else: ?>
                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($reviews as $rev): ?>
                            <div class="border-bottom pb-2">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <strong class="small"><?= e($rev['reviewer_name']) ?> (<?= e($rev['reviewer_city']) ?>)</strong>
                                    <span class="text-warning small"><?= str_repeat('⭐', (int)$rev['rating']) ?></span>
                                </div>
                                <p class="small text-secondary mb-1"><?= e($rev['review_text']) ?></p>
                                <span class="text-muted" style="font-size: 0.72rem;"><?= timeAgo($rev['created_at']) ?></span>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Right: Price, Seller & Contact -->
        <div class="col-12 col-lg-4">
            <div class="bg-white border rounded p-4 shadow-sm mb-4 sticky-lg-top" style="top: 80px; z-index: 10;">
                <span class="text-muted small">Asking Price</span>
                <div class="fs-2 fw-bold text-success tabular-nums mb-3">
                    <?= formatPKR($listing['price']) ?>
                </div>

                <div class="d-flex flex-column gap-2 mb-4">
                    <a href="https://wa.me/<?= $cleanWhatsApp ?>?text=<?= urlencode("Assalam o Alaikum, I saw your ad on SARGODHAMART: '{$listing['title']}'. Is it still available?") ?>" target="_blank" class="btn btn-whatsapp py-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-whatsapp fs-5"></i>
                        <span>WhatsApp: <?= e($listing['whatsapp_number']) ?></span>
                    </a>

                    <a href="tel:<?= e($listing['phone_number']) ?>" class="btn btn-call py-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-telephone-fill"></i>
                        <span>Call: <?= e($listing['phone_number']) ?></span>
                    </a>

                    <a href="/user/messages.php?listing_id=<?= $listing['id'] ?>&seller_id=<?= $listing['seller_id'] ?>" class="btn btn-primary-green py-2 d-flex align-items-center justify-content-center gap-2">
                        <i class="bi bi-chat-dots-fill"></i>
                        <span>In-App Message Seller</span>
                    </a>

                    <!-- Social Share Button on Sidebar -->
                    <button type="button" class="btn btn-outline-dark py-2 d-flex align-items-center justify-content-center gap-2" data-bs-toggle="modal" data-bs-target="#shareModal">
                        <i class="bi bi-share"></i>
                        <span>Share this Product</span>
                    </button>
                </div>

                <div class="p-3 bg-light rounded border mb-3">
                    <div class="d-flex align-items-center gap-3 mb-2">
                        <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fs-5" style="width: 44px; height: 44px;">
                            <?= strtoupper(substr($listing['seller_name'], 0, 1)) ?>
                        </div>
                        <div>
                            <h6 class="fw-bold mb-0">
                                <a href="/seller.php?id=<?= $listing['seller_id'] ?>" class="text-dark text-decoration-none">
                                    <?= e($listing['seller_name']) ?>
                                </a>
                            </h6>
                            <small class="text-muted"><i class="bi bi-geo-alt"></i> <?= e($listing['seller_area']) ?>, <?= e($listing['seller_city']) ?></small>
                        </div>
                    </div>
                    <div class="d-flex justify-content-between small text-muted border-top pt-2">
                        <span>Member Since:</span>
                        <strong><?= date('M Y', strtotime($listing['seller_joined'])) ?></strong>
                    </div>
                </div>

                <div class="border rounded p-3 bg-white small text-muted">
                    <div class="fw-bold text-dark mb-1"><i class="bi bi-shield-check text-success me-1"></i> Safety Reminders</div>
                    <ul class="ps-3 mb-0">
                        <li>Do not send advance payments without seeing the product.</li>
                        <li>Inspect livestock, vehicles and phones in person.</li>
                        <li>Meet in populated locations in Sargodha.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Social Media Share Modal (Section 13) -->
<div class="modal fade" id="shareModal" tabindex="-1" aria-labelledby="shareModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold" id="shareModalLabel"><i class="bi bi-share-fill text-primary me-2"></i> Share Product</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center py-4">
                <h6 class="fw-bold mb-1 text-truncate"><?= e($listing['title']) ?></h6>
                <div class="text-success fw-bold tabular-nums mb-3"><?= formatPKR($listing['price']) ?> · <?= e($listing['city']) ?></div>

                <div class="row row-cols-3 g-3 mb-4">
                    <!-- WhatsApp -->
                    <div class="col">
                        <a href="https://api.whatsapp.com/send?text=<?= $encodedShareText ?>" target="_blank" class="btn btn-outline-success w-100 p-2 d-flex flex-column align-items-center gap-1">
                            <i class="bi bi-whatsapp fs-4"></i>
                            <span class="small">WhatsApp</span>
                        </a>
                    </div>
                    <!-- Facebook -->
                    <div class="col">
                        <a href="https://www.facebook.com/sharer/sharer.php?u=<?= $encodedUrl ?>" target="_blank" class="btn btn-outline-primary w-100 p-2 d-flex flex-column align-items-center gap-1">
                            <i class="bi bi-facebook fs-4"></i>
                            <span class="small">Facebook</span>
                        </a>
                    </div>
                    <!-- Messenger -->
                    <div class="col">
                        <a href="fb-messenger://share/?link=<?= $encodedUrl ?>" target="_blank" class="btn btn-outline-info w-100 p-2 d-flex flex-column align-items-center gap-1">
                            <i class="bi bi-messenger fs-4"></i>
                            <span class="small">Messenger</span>
                        </a>
                    </div>
                    <!-- Telegram -->
                    <div class="col">
                        <a href="https://t.me/share/url?url=<?= $encodedUrl ?>&text=<?= $encodedShareText ?>" target="_blank" class="btn btn-outline-primary w-100 p-2 d-flex flex-column align-items-center gap-1">
                            <i class="bi bi-telegram fs-4"></i>
                            <span class="small">Telegram</span>
                        </a>
                    </div>
                    <!-- X / Twitter -->
                    <div class="col">
                        <a href="https://twitter.com/intent/tweet?text=<?= $encodedShareText ?>" target="_blank" class="btn btn-outline-dark w-100 p-2 d-flex flex-column align-items-center gap-1">
                            <i class="bi bi-twitter-x fs-4"></i>
                            <span class="small">X (Twitter)</span>
                        </a>
                    </div>
                    <!-- Native Web Share API -->
                    <div class="col">
                        <button type="button" class="btn btn-outline-secondary w-100 p-2 d-flex flex-column align-items-center gap-1" onclick="if (navigator.share) { navigator.share({ title: '<?= addslashes($listing['title']) ?>', text: '<?= addslashes($shareText) ?>', url: '<?= $currentUrl ?>' }); } else { alert('Native share not supported on this browser.'); }">
                            <i class="bi bi-phone fs-4"></i>
                            <span class="small">Device Share</span>
                        </button>
                    </div>
                </div>

                <!-- Copy Link Field -->
                <div class="input-group">
                    <input type="text" class="form-control form-control-sm" id="shareUrlInput" value="<?= e($currentUrl) ?>" readonly>
                    <button class="btn btn-primary-green btn-sm" type="button" onclick="navigator.clipboard.writeText(document.getElementById('shareUrlInput').value); this.innerText = 'Copied!'; setTimeout(() => this.innerText = 'Copy Link', 2000);">
                        Copy Link
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Report Modal -->
<div class="modal fade" id="reportModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h6 class="modal-title fw-bold text-danger"><i class="bi bi-flag me-1"></i> Report Listing</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="/api/report.php" method="POST">
                <?= getCsrfField() ?>
                <input type="hidden" name="listing_id" value="<?= $listing['id'] ?>">
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Reason for Report *</label>
                        <select name="reason" class="form-select form-select-sm" required>
                            <option value="Scam/Fraud">Scam / Fraud</option>
                            <option value="Fake product">Fake Product</option>
                            <option value="Wrong information">Wrong or Misleading Information</option>
                            <option value="Duplicate">Duplicate Listing</option>
                            <option value="Prohibited item">Prohibited or Illegal Item</option>
                            <option value="Offensive content">Offensive Content</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Details *</label>
                        <textarea name="details" class="form-control form-control-sm" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-danger btn-sm">Submit Report</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
