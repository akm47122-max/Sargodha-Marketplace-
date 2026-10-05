<?php
/**
 * Sargodha Mandi - My Favorites
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user = currentUser();
$db = getDB();

// Handle Remove Favorite
if (isset($_POST['remove_fav'])) {
    verifyCsrfOrDie();
    $listingId = (int)$_POST['listing_id'];
    $db->prepare("DELETE FROM favorites WHERE user_id = :uid AND listing_id = :lid")
       ->execute([':uid' => $user['id'], ':lid' => $listingId]);
    redirect('/user/favorites.php', 'Listing removed from favorites.', 'info');
}

// Fetch Favorites
$stmt = $db->prepare("
    SELECT l.*, c.name as category_name, f.created_at as saved_at,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id ORDER BY is_primary DESC, id ASC LIMIT 1) as primary_image
    FROM favorites f
    JOIN listings l ON f.listing_id = l.id
    JOIN categories c ON l.category_id = c.id
    WHERE f.user_id = :uid
    ORDER BY f.id DESC
");
$stmt->execute([':uid' => $user['id']]);
$favorites = $stmt->fetchAll();

$pageTitle = 'My Saved Favorites - Sargodha Mandi';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Saved Favorite Ads</h3>
        <p class="text-muted small">Quick access to listings you have shortlisted across Sargodha, Shaheenabad & Sillanwali</p>
    </div>

    <?php if (empty($favorites)): ?>
        <div class="card border p-5 text-center bg-white shadow-sm">
            <i class="bi bi-heart fs-1 text-muted mb-2"></i>
            <h5 class="fw-bold">No favorite ads saved yet</h5>
            <p class="text-muted small">Click the "Save" heart icon on any ad to bookmark it here.</p>
            <div class="mt-2">
                <a href="/search.php" class="btn btn-primary-green btn-sm">Explore Marketplace</a>
            </div>
        </div>
    <?php else: ?>
        <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
            <?php foreach ($favorites as $item): ?>
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
                                <i class="bi bi-tag"></i> <?= e($item['category_name']) ?> · <?= e($item['city']) ?>
                            </div>
                            <div class="d-flex justify-content-between align-items-center border-top pt-2 mt-auto">
                                <a href="/product.php?id=<?= $item['id'] ?>" class="btn btn-outline-success btn-sm py-0 px-2">View</a>
                                <form method="POST" action="/user/favorites.php" class="d-inline">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="listing_id" value="<?= $item['id'] ?>">
                                    <button type="submit" name="remove_fav" value="1" class="btn btn-link text-danger text-decoration-none p-0 small">
                                        <i class="bi bi-trash"></i> Remove
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
