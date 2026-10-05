<?php
/**
 * SARGODHAMART - User Portal: Edit Listing
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user = currentUser();
$db = getDB();

$id = (int)($_GET['id'] ?? 0);
if ($id <= 0) {
    redirect('/user/my-listings.php', 'Invalid listing ID.', 'error');
}

// Fetch listing
$stmt = $db->prepare("SELECT * FROM listings WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $id]);
$listing = $stmt->fetch();

if (!$listing) {
    redirect('/user/my-listings.php', 'Listing not found.', 'error');
}

// Strict Ownership Verification (403 Forbidden if mismatched)
if ((int)$listing['user_id'] !== (int)$user['id'] && !isAdmin()) {
    http_response_code(403);
    die("<div style='font-family:sans-serif;padding:40px;text-align:center;'><h2>403 Forbidden</h2><p>You are not authorized to edit this listing.</p><a href='/user/my-listings.php'>Return to My Listings</a></div>");
}

// Handle Single Image Deletion
if (isset($_GET['delete_img']) && (int)$_GET['delete_img'] > 0) {
    verifyCsrfOrDie();
    $delImgId = (int)$_GET['delete_img'];
    $db->prepare("DELETE FROM listing_images WHERE id = :img_id AND listing_id = :lid")->execute([':img_id' => $delImgId, ':lid' => $id]);
    redirect("/user/edit-listing.php?id={$id}", 'Image removed successfully.', 'info');
}

// Ensure listing_edit_history table exists
try {
    $db->exec("
        CREATE TABLE IF NOT EXISTS `listing_edit_history` (
            `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
            `listing_id` INT NOT NULL,
            `user_id` INT NOT NULL,
            `changed_fields` TEXT NOT NULL,
            `previous_values` TEXT NULL,
            `new_values` TEXT NULL,
            `moderation_status` VARCHAR(50) DEFAULT 'published',
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            INDEX `idx_leh_listing` (`listing_id`),
            INDEX `idx_leh_user` (`user_id`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
} catch (Throwable $e) {}

// Fetch existing images
$stmt = $db->prepare("SELECT * FROM listing_images WHERE listing_id = :lid ORDER BY is_primary DESC, sort_order ASC");
$stmt->execute([':lid' => $id]);
$images = $stmt->fetchAll();

// Fetch categories
$cats = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();

    $title = trim($_POST['title'] ?? '');
    $catId = (int)($_POST['category_id'] ?? 0);
    $subcatId = !empty($_POST['subcategory_id']) ? (int)$_POST['subcategory_id'] : null;
    $price = trim($_POST['price'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $condition = trim($_POST['condition'] ?? 'Used');
    $city = trim($_POST['city'] ?? 'Sargodha');
    $area = trim($_POST['area'] ?? '');
    $exactLocation = trim($_POST['exact_location'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    $whatsapp = trim($_POST['whatsapp_number'] ?? '');

    if (strlen($title) < 5 || strlen($title) > 200) {
        $errors[] = 'Title must be between 5 and 200 characters.';
    }
    if ($catId <= 0) {
        $errors[] = 'Please select a valid category.';
    }
    if (!is_numeric($price) || $price < 0) {
        $errors[] = 'Please enter a valid price in PKR.';
    }
    if (empty($phone)) {
        $errors[] = 'Contact phone number is required.';
    }

    if (empty($errors)) {
        // Track which fields changed
        $changed = [];
        $prev = [];
        $new = [];

        if ($listing['title'] !== $title) { $changed[] = 'title'; $prev['title'] = $listing['title']; $new['title'] = $title; }
        if ((float)$listing['price'] !== (float)$price) { $changed[] = 'price'; $prev['price'] = $listing['price']; $new['price'] = $price; }
        if ($listing['description'] !== $description) { $changed[] = 'description'; $prev['description'] = mb_substr($listing['description'], 0, 100); $new['description'] = mb_substr($description, 0, 100); }
        if ((int)$listing['category_id'] !== $catId) { $changed[] = 'category'; $prev['category'] = $listing['category_id']; $new['category'] = $catId; }
        if ($listing['city'] !== $city || $listing['area'] !== $area) { $changed[] = 'location'; $prev['location'] = "{$listing['city']}, {$listing['area']}"; $new['location'] = "{$city}, {$area}"; }

        // Determine if re-moderation is triggered
        $requireModeration = function_exists('getSetting') ? getSetting('re_moderate_edited_listings', '0') === '1' : false;
        $newStatus = $requireModeration && !empty($changed) ? 'pending' : $listing['status'];

        $stmt = $db->prepare("
            UPDATE listings SET
                title = :title,
                category_id = :cat,
                subcategory_id = :subcat,
                price = :price,
                description = :desc,
                `condition` = :cond,
                city = :city,
                area = :area,
                exact_location = :loc,
                phone_number = :phone,
                whatsapp_number = :whatsapp,
                status = :status,
                updated_at = NOW()
            WHERE id = :id AND user_id = :uid
        ");
        $stmt->execute([
            ':title' => $title,
            ':cat' => $catId,
            ':subcat' => $subcatId,
            ':price' => $price,
            ':desc' => $description,
            ':cond' => $condition,
            ':city' => $city,
            ':area' => $area,
            ':loc' => $exactLocation,
            ':phone' => $phone,
            ':whatsapp' => $whatsapp,
            ':status' => $newStatus,
            ':id' => $id,
            ':uid' => $user['id']
        ]);

        // Upload additional images enforcing MAX 3 TOTAL IMAGES
        $currentImgCount = (int)$db->query("SELECT COUNT(*) FROM listing_images WHERE listing_id = {$id}")->fetchColumn();
        if (!empty($_FILES['images']['name'][0])) {
            $totalFiles = count($_FILES['images']['name']);
            for ($i = 0; $i < $totalFiles; $i++) {
                if ($currentImgCount >= 3) {
                    break; // Maximum 3 product images enforced
                }
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_OK) {
                    $file = [
                        'name' => $_FILES['images']['name'][$i],
                        'type' => $_FILES['images']['type'][$i],
                        'tmp_name' => $_FILES['images']['tmp_name'][$i],
                        'error' => $_FILES['images']['error'][$i],
                        'size' => $_FILES['images']['size'][$i]
                    ];
                    $res = validateImageUpload($file, 'products');
                    if ($res['success']) {
                        $db->prepare("
                            INSERT INTO listing_images (listing_id, image_path, is_primary, created_at)
                            VALUES (:lid, :path, 0, NOW())
                        ")->execute([':lid' => $id, ':path' => $res['path']]);
                        $currentImgCount++;
                        $changed[] = 'images';
                    }
                }
            }
        }

        // Record audit trail in listing_edit_history
        if (!empty($changed)) {
            $stmtHist = $db->prepare("
                INSERT INTO listing_edit_history 
                (listing_id, user_id, changed_fields, previous_values, new_values, moderation_status, created_at)
                VALUES (:lid, :uid, :chg, :prev, :new, :mod, NOW())
            ");
            $stmtHist->execute([
                ':lid' => $id,
                ':uid' => $user['id'],
                ':chg' => implode(', ', $changed),
                ':prev' => json_encode($prev, JSON_UNESCAPED_UNICODE),
                ':new' => json_encode($new, JSON_UNESCAPED_UNICODE),
                ':mod' => $newStatus
            ]);
        }

        if ($newStatus === 'pending') {
            redirect('/user/my-listings.php', 'Your changes have been saved and submitted for admin review.', 'warning');
        } else {
            redirect('/user/my-listings.php', 'Your post has been updated successfully.', 'success');
        }
    }
}

$pageTitle = 'Edit Listing - ' . e($listing['title']) . ' - ' . SITE_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="/index.php" class="text-decoration-none text-success">Home</a></li>
            <li class="breadcrumb-item"><a href="/user/dashboard.php" class="text-decoration-none text-success">User Portal</a></li>
            <li class="breadcrumb-item"><a href="/user/my-listings.php" class="text-decoration-none text-success">My Listings</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Listing</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border rounded-3 p-4 bg-white shadow-sm">
                <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-4">
                    <div>
                        <h4 class="fw-bold mb-1">Edit Listing</h4>
                        <p class="text-secondary small mb-0">Update your product details and photos</p>
                    </div>
                    <span class="badge bg-<?= $listing['status'] === 'published' ? 'success' : 'secondary' ?> text-uppercase">
                        Status: <?= e($listing['status']) ?>
                    </span>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger small shadow-sm mb-4">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $e): ?>
                                <li><?= e($e) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/user/edit-listing.php?id=<?= $id ?>" enctype="multipart/form-data">
                    <?= getCsrfField() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Product Title *</label>
                        <input type="text" name="title" class="form-control" value="<?= e($listing['title']) ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Category *</label>
                            <select name="category_id" id="category-select" class="form-select" required>
                                <?php foreach ($cats as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $listing['category_id'] == $c['id'] ? 'selected' : '' ?>>
                                        <?= e($c['name']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Condition *</label>
                            <select name="condition" class="form-select" required>
                                <option value="Used" <?= $listing['condition'] === 'Used' ? 'selected' : '' ?>>Used</option>
                                <option value="New" <?= $listing['condition'] === 'New' ? 'selected' : '' ?>>New</option>
                                <option value="Refurbished" <?= $listing['condition'] === 'Refurbished' ? 'selected' : '' ?>>Refurbished</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Price (PKR) *</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" name="price" class="form-control" value="<?= (int)$listing['price'] ?>" required>
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">City / Local Hub *</label>
                            <select name="city" class="form-select" required>
                                <option value="Sargodha" <?= $listing['city'] === 'Sargodha' ? 'selected' : '' ?>>Sargodha</option>
                                <option value="Shaheenabad" <?= $listing['city'] === 'Shaheenabad' ? 'selected' : '' ?>>Shaheenabad</option>
                                <option value="Sillanwali" <?= $listing['city'] === 'Sillanwali' ? 'selected' : '' ?>>Sillanwali</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Town / Area</label>
                            <input type="text" name="area" class="form-control" value="<?= e($listing['area']) ?>" placeholder="e.g. Satellite Town, Canal Colony">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Exact Location / Landmark</label>
                            <input type="text" name="exact_location" class="form-control" value="<?= e($listing['exact_location']) ?>" placeholder="e.g. Near Fatima Jinnah Hall">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Contact Phone *</label>
                            <input type="text" name="phone_number" class="form-control" value="<?= e($listing['phone_number']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">WhatsApp Number</label>
                            <input type="text" name="whatsapp_number" class="form-control" value="<?= e($listing['whatsapp_number']) ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Description *</label>
                        <textarea name="description" rows="5" class="form-control" required><?= e($listing['description']) ?></textarea>
                    </div>

                    <!-- Current Photos -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold d-block">Current Photos</label>
                        <div class="d-flex flex-wrap gap-2 mb-2">
                            <?php foreach ($images as $img): ?>
                                <div class="position-relative border rounded p-1 bg-light">
                                    <img src="<?= e($img['image_path']) ?>" alt="Photo" class="rounded" style="width: 80px; height: 80px; object-fit: cover;">
                                    <?php if ($img['is_primary']): ?>
                                        <span class="badge bg-success position-absolute top-0 start-0 m-1" style="font-size: 0.6rem;">Main</span>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <label class="form-label small text-secondary">Upload Additional Photos (Optional)</label>
                        <input type="file" name="images[]" multiple class="form-control" accept="image/jpeg,image/png,image/webp">
                    </div>

                    <div class="d-flex justify-content-between pt-3 border-top">
                        <a href="/user/my-listings.php" class="btn btn-outline-secondary">Cancel</a>
                        <button type="submit" class="btn btn-primary-green fw-bold px-4">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
