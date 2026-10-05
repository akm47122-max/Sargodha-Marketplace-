<?php
/**
 * Sargodha Mandi - Search & Filters
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();

// Input parameters
$q = trim($_GET['q'] ?? '');
$city = trim($_GET['city'] ?? '');
$categorySlug = trim($_GET['category'] ?? '');
$subcatId = (int)($_GET['subcategory'] ?? 0);
$condition = trim($_GET['condition'] ?? '');
$minPrice = isset($_GET['min_price']) && is_numeric($_GET['min_price']) ? (float)$_GET['min_price'] : null;
$maxPrice = isset($_GET['max_price']) && is_numeric($_GET['max_price']) ? (float)$_GET['max_price'] : null;
$featuredOnly = !empty($_GET['featured']);
$sort = trim($_GET['sort'] ?? 'newest');

// Fetch categories for sidebar
$categories = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();

// Resolve category ID if slug provided
$selectedCatId = null;
if (!empty($categorySlug)) {
    $cStmt = $db->prepare("SELECT id, name FROM categories WHERE slug = :slug LIMIT 1");
    $cStmt->execute([':slug' => $categorySlug]);
    $catRow = $cStmt->fetch();
    if ($catRow) {
        $selectedCatId = (int)$catRow['id'];
    }
}

// Fetch subcategories for selected category
$subcategories = [];
if ($selectedCatId) {
    $sStmt = $db->prepare("SELECT id, name FROM subcategories WHERE category_id = :cid AND is_active = 1 ORDER BY name ASC");
    $sStmt->execute([':cid' => $selectedCatId]);
    $subcategories = $sStmt->fetchAll();
}

// Build query
$where = ["l.status = 'Published'"];
$params = [];

if ($q !== '') {
    $where[] = "(l.title LIKE :q OR l.description LIKE :q OR l.area LIKE :q)";
    $params[':q'] = "%{$q}%";
}

if (!empty($city) && in_array($city, ['Sargodha', 'Shaheenabad', 'Sillanwali'])) {
    $where[] = "l.city = :city";
    $params[':city'] = $city;
}

if ($selectedCatId) {
    $where[] = "l.category_id = :cat_id";
    $params[':cat_id'] = $selectedCatId;
}

if ($subcatId > 0) {
    $where[] = "l.subcategory_id = :subcat_id";
    $params[':subcat_id'] = $subcatId;
}

if (!empty($condition) && in_array($condition, ['New', 'Used', 'Refurbished'])) {
    $where[] = "l.item_condition = :cond";
    $params[':cond'] = $condition;
}

if ($minPrice !== null) {
    $where[] = "l.price >= :min_price";
    $params[':min_price'] = $minPrice;
}

if ($maxPrice !== null) {
    $where[] = "l.price <= :max_price";
    $params[':max_price'] = $maxPrice;
}

if ($featuredOnly) {
    $where[] = "l.is_featured = 1";
}

// Sorting
$orderBy = "l.id DESC";
if ($sort === 'price_asc') {
    $orderBy = "l.price ASC";
} elseif ($sort === 'price_desc') {
    $orderBy = "l.price DESC";
}

$whereClause = implode(" AND ", $where);
$sql = "
    SELECT l.*, c.name as category_name, u.full_name as seller_name,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id ORDER BY is_primary DESC, id ASC LIMIT 1) as primary_image
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    JOIN users u ON l.user_id = u.id
    WHERE {$whereClause}
    ORDER BY l.is_featured DESC, {$orderBy}
    LIMIT 60
";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$listings = $stmt->fetchAll();
$totalFound = count($listings);

$pageTitle = 'Search Listings - Sargodha Mandi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container">
    <!-- Breadcrumb & Top search summary -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3">
        <div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-1 small">
                    <li class="breadcrumb-item"><a href="/index.php" class="text-decoration-none">Home</a></li>
                    <li class="breadcrumb-item active" aria-current="page">Search</li>
                    <?php if ($city): ?>
                        <li class="breadcrumb-item active"><?= e($city) ?></li>
                    <?php endif; ?>
                </ol>
            </nav>
            <h4 class="fw-bold mb-0">
                <?php if ($q): ?>
                    Search Results for "<?= e($q) ?>"
                <?php elseif ($categorySlug && $catRow): ?>
                    <?= e($catRow['name']) ?> in Sargodha District
                <?php elseif ($featuredOnly): ?>
                    ⭐ Featured Promoted Ads
                <?php else: ?>
                    All Marketplace Ads
                <?php endif; ?>
                <span class="text-muted fs-6 fw-normal">(<?= $totalFound ?> found)</span>
            </h4>
        </div>

        <!-- Sort Select -->
        <div class="d-flex align-items-center gap-2">
            <span class="small text-muted text-nowrap">Sort By:</span>
            <form method="GET" id="sortForm" class="d-inline">
                <?php foreach ($_GET as $key => $val): ?>
                    <?php if ($key !== 'sort'): ?>
                        <input type="hidden" name="<?= e($key) ?>" value="<?= e($val) ?>">
                    <?php endif; ?>
                <?php endforeach; ?>
                <select name="sort" class="form-select form-select-sm" onchange="document.getElementById('sortForm').submit();">
                    <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest Ads</option>
                    <option value="price_asc" <?= $sort === 'price_asc' ? 'selected' : '' ?>>Price: Low to High</option>
                    <option value="price_desc" <?= $sort === 'price_desc' ? 'selected' : '' ?>>Price: High to Low</option>
                </select>
            </form>
        </div>
    </div>

    <div class="row g-4">
        <!-- Sidebar Filters -->
        <div class="col-12 col-lg-3">
            <div class="bg-white border rounded p-3 shadow-sm">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h6 class="fw-bold mb-0"><i class="bi bi-funnel me-1"></i> Filters</h6>
                    <a href="/search.php" class="text-danger small text-decoration-none">Reset</a>
                </div>

                <form method="GET" action="/search.php">
                    <?php if ($q): ?>
                        <input type="hidden" name="q" value="<?= e($q) ?>">
                    <?php endif; ?>

                    <!-- City Filter -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">City / Location</label>
                        <select name="city" class="form-select form-select-sm">
                            <option value="">All Areas</option>
                            <option value="Sargodha" <?= $city === 'Sargodha' ? 'selected' : '' ?>>Sargodha City</option>
                            <option value="Shaheenabad" <?= $city === 'Shaheenabad' ? 'selected' : '' ?>>Shaheenabad</option>
                            <option value="Sillanwali" <?= $city === 'Sillanwali' ? 'selected' : '' ?>>Sillanwali</option>
                        </select>
                    </div>

                    <!-- Category Filter -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Category</label>
                        <select name="category" id="category_id" class="form-select form-select-sm">
                            <option value="">All Categories</option>
                            <?php foreach ($categories as $c): ?>
                                <option value="<?= e($c['slug']) ?>" <?= $categorySlug === $c['slug'] ? 'selected' : '' ?>>
                                    <?= e($c['name']) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <!-- Subcategory Filter -->
                    <?php if (!empty($subcategories)): ?>
                        <div class="mb-3">
                            <label class="form-label small fw-bold">Subcategory</label>
                            <select name="subcategory" id="subcategory_id" class="form-select form-select-sm">
                                <option value="">All Subcategories</option>
                                <?php foreach ($subcategories as $sc): ?>
                                    <option value="<?= $sc['id'] ?>" <?= $subcatId === (int)$sc['id'] ? 'selected' : '' ?>>
                                        <?= e($sc['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    <?php endif; ?>

                    <!-- Price Range -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Price Range (PKR)</label>
                        <div class="input-group input-group-sm mb-2">
                            <span class="input-group-text">Min</span>
                            <input type="number" name="min_price" class="form-control" placeholder="0" value="<?= $minPrice !== null ? e($minPrice) : '' ?>">
                        </div>
                        <div class="input-group input-group-sm">
                            <span class="input-group-text">Max</span>
                            <input type="number" name="max_price" class="form-control" placeholder="Any" value="<?= $maxPrice !== null ? e($maxPrice) : '' ?>">
                        </div>
                    </div>

                    <!-- Condition -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold">Condition</label>
                        <select name="condition" class="form-select form-select-sm">
                            <option value="">All Conditions</option>
                            <option value="New" <?= $condition === 'New' ? 'selected' : '' ?>>Brand New</option>
                            <option value="Used" <?= $condition === 'Used' ? 'selected' : '' ?>>Used</option>
                            <option value="Refurbished" <?= $condition === 'Refurbished' ? 'selected' : '' ?>>Refurbished</option>
                        </select>
                    </div>

                    <!-- Featured checkbox -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="featured" value="1" id="featuredCheck" <?= $featuredOnly ? 'checked' : '' ?>>
                        <label class="form-check-label small" for="featuredCheck">
                            ⭐ Featured Ads Only
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary-green btn-sm w-100 py-2">
                        Apply Filters
                    </button>
                </form>
            </div>
        </div>

        <!-- Listings Grid -->
        <div class="col-12 col-lg-9">
            <?php if (empty($listings)): ?>
                <div class="card border p-5 text-center bg-white shadow-sm">
                    <i class="bi bi-search fs-1 text-muted mb-3"></i>
                    <h5 class="fw-bold">No ads found matching your criteria</h5>
                    <p class="text-muted small">Try broadening your search keywords, clearing price range, or selecting another nearby hub like Shaheenabad or Sillanwali.</p>
                    <div class="mt-2">
                        <a href="/search.php" class="btn btn-outline-success btn-sm me-2">Clear All Filters</a>
                        <a href="/post-ad.php" class="btn btn-primary-green btn-sm">Post an Ad Here</a>
                    </div>
                </div>
            <?php else: ?>
                <div class="row row-cols-1 row-cols-sm-2 row-cols-md-3 g-3">
                    <?php foreach ($listings as $item): ?>
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
                                        <span class="text-truncate" style="max-width: 130px;">
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
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
