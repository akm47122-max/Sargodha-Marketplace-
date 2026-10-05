<?php
/**
 * Sargodha Mandi - All Categories Directory
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$cats = $db->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM listings WHERE category_id = c.id AND status = 'Published') as ad_count
    FROM categories c 
    WHERE c.is_active = 1 
    ORDER BY c.sort_order ASC
")->fetchAll();

$pageTitle = 'All Categories - Sargodha Mandi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <div class="mb-4">
        <h3 class="fw-bold mb-1">Browse by Category</h3>
        <p class="text-muted small">Explore products, livestock, vehicles and services available across Sargodha, Shaheenabad & Sillanwali</p>
    </div>

    <div class="row g-4">
        <?php foreach ($cats as $cat): ?>
            <?php
            // Fetch subcategories
            $subStmt = $db->prepare("
                SELECT sc.*, 
                       (SELECT COUNT(*) FROM listings WHERE subcategory_id = sc.id AND status = 'Published') as sub_ad_count
                FROM subcategories sc 
                WHERE sc.category_id = :cid AND sc.is_active = 1 
                ORDER BY sc.name ASC
            ");
            $subStmt->execute([':cid' => $cat['id']]);
            $subs = $subStmt->fetchAll();
            ?>
            <div class="col-12 col-md-6 col-lg-4">
                <div class="card h-100 border shadow-sm">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle fs-4 d-flex align-items-center justify-content-center" style="width: 54px; height: 54px;">
                                <i class="bi <?= e($cat['icon'] ?: 'bi-tag') ?>"></i>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0">
                                    <a href="/search.php?category=<?= urlencode($cat['slug']) ?>" class="text-dark text-decoration-none">
                                        <?= e($cat['name']) ?>
                                    </a>
                                </h6>
                                <span class="badge bg-secondary-subtle text-secondary small"><?= $cat['ad_count'] ?> ads</span>
                            </div>
                        </div>

                        <p class="small text-muted mb-3"><?= e($cat['description']) ?></p>

                        <?php if (!empty($subs)): ?>
                            <ul class="list-unstyled small mb-0 d-flex flex-wrap gap-2">
                                <?php foreach ($subs as $sub): ?>
                                    <li>
                                        <a href="/search.php?category=<?= urlencode($cat['slug']) ?>&subcategory=<?= $sub['id'] ?>" class="text-decoration-none text-muted border px-2 py-1 rounded bg-light">
                                            <?= e($sub['name']) ?> <span class="text-secondary">(<?= $sub['sub_ad_count'] ?>)</span>
                                        </a>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                    <div class="card-footer bg-white border-0 pt-0 pb-3">
                        <a href="/search.php?category=<?= urlencode($cat['slug']) ?>" class="btn btn-outline-success btn-sm w-100">
                            View All in <?= e($cat['name']) ?> &rarr;
                        </a>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
