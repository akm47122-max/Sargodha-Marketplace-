<?php
/**
 * SARGODHAMART - Post an Ad Form
 * Unlimited Free Listings for Activated Sellers
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';

$user = currentUser();
$db = getDB();

// Enforce One-Time Seller Activation Rule
if ($user['activation_status'] !== 'active') {
    redirect(
        '/activate-seller.php', 
        'Please complete your one-time Rs. 500 seller account activation first. Once activated, you can post unlimited product listings for free!', 
        'warning'
    );
}

$errors = [];
$maxImages = (int)getSetting('max_images_per_listing', 8);

// Fetch active categories
$cats = $db->query("SELECT * FROM categories WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();

$title = '';
$catId = '';
$subcatId = '';
$price = '';
$description = '';
$condition = 'Used';
$city = $user['city'] ?? 'Sargodha';
$area = $user['area'] ?? '';
$exactLocation = '';
$phone = $user['mobile_number'] ?? '';
$whatsapp = $user['mobile_number'] ?? '';
$contactPref = 'Both';
$videoUrl = '';

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
    $contactPref = trim($_POST['contact_preference'] ?? 'Both');
    $videoUrl = trim($_POST['video_url'] ?? '');

    if (strlen($title) < 5 || strlen($title) > 200) {
        $errors[] = 'Title must be between 5 and 200 characters.';
    }

    if ($catId <= 0) {
        $errors[] = 'Please select a valid category.';
    }

    if (!is_numeric($price) || (float)$price < 0) {
        $errors[] = 'Please enter a valid price in PKR.';
    }

    if (strlen($description) < 15) {
        $errors[] = 'Description must be at least 15 characters.';
    }

    if (!in_array($condition, ['New', 'Used', 'Refurbished'])) {
        $errors[] = 'Invalid item condition.';
    }

    if (!in_array($city, ['Sargodha', 'Shaheenabad', 'Sillanwali'])) {
        $errors[] = 'Please select a valid city hub.';
    }

    if (empty($area)) {
        $errors[] = 'Please specify your local area/bazaar.';
    }

    $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
    if (!preg_match('/^03[0-9]{9}$/', $cleanPhone)) {
        $errors[] = 'Phone number must be an 11-digit Pakistani number.';
    }

    $cleanWhatsapp = preg_replace('/[^0-9]/', '', $whatsapp);
    if (!preg_match('/^03[0-9]{9}$/', $cleanWhatsapp)) {
        $errors[] = 'WhatsApp number must be an 11-digit Pakistani number.';
    }

    // Images
    $uploadedFiles = [];
    if (!empty($_FILES['images']['name'][0])) {
        $fileCount = count($_FILES['images']['name']);
        if ($fileCount > $maxImages) {
            $errors[] = "You can upload a maximum of {$maxImages} photos.";
        } else {
            for ($i = 0; $i < $fileCount; $i++) {
                if ($_FILES['images']['error'][$i] === UPLOAD_ERR_NO_FILE) continue;

                $fileData = [
                    'name' => $_FILES['images']['name'][$i],
                    'type' => $_FILES['images']['type'][$i],
                    'tmp_name' => $_FILES['images']['tmp_name'][$i],
                    'error' => $_FILES['images']['error'][$i],
                    'size' => $_FILES['images']['size'][$i]
                ];

                $uploadResult = validateAndSaveImage($fileData, 'products');
                if (!$uploadResult['success']) {
                    $errors[] = "Image #" . ($i + 1) . ": " . $uploadResult['error'];
                    break;
                } else {
                    $uploadedFiles[] = $uploadResult['path'];
                }
            }
        }
    } else {
        $errors[] = 'Please upload at least 1 clear photo of your product.';
    }

    if (empty($errors)) {
        $baseSlug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title), '-'));
        $slug = substr($baseSlug, 0, 180) . '-' . time();

        // Normal listings are published free of charge for activated sellers
        $stmt = $db->prepare("
            INSERT INTO listings 
            (user_id, category_id, subcategory_id, title, slug, price, description, item_condition, 
             city, area, exact_location, phone_number, whatsapp_number, contact_preference, video_url, status)
            VALUES 
            (:uid, :cat, :subcat, :title, :slug, :price, :desc, :cond, 
             :city, :area, :exact, :phone, :wa, :pref, :vid, 'published')
        ");

        $stmt->execute([
            ':uid' => $user['id'],
            ':cat' => $catId,
            ':subcat' => $subcatId ?: null,
            ':title' => $title,
            ':slug' => $slug,
            ':price' => (float)$price,
            ':desc' => $description,
            ':cond' => $condition,
            ':city' => $city,
            ':area' => $area,
            ':exact' => $exactLocation ?: null,
            ':phone' => $cleanPhone,
            ':wa' => $cleanWhatsapp,
            ':pref' => $contactPref,
            ':vid' => $videoUrl ?: null
        ]);

        $newListingId = (int)$db->lastInsertId();

        foreach ($uploadedFiles as $idx => $imgPath) {
            $imgStmt = $db->prepare("INSERT INTO listing_images (listing_id, image_path, is_primary, sort_order) VALUES (:lid, :path, :primary, :sort)");
            $imgStmt->execute([
                ':lid' => $newListingId,
                ':path' => $imgPath,
                ':primary' => ($idx === 0) ? 1 : 0,
                ':sort' => $idx
            ]);
        }

        createNotification($user['id'], 'Listing Published Successfully!', "Your ad '{$title}' is now live on SARGODHAMART.", "/product.php?id={$newListingId}", 'listing_published');

        if (class_exists('ResendMailer')) {
            if (!empty($user['email'])) {
                ResendMailer::sendListingApprovedEmail($user['email'], $user['full_name'] ?? 'Seller', $title, $newListingId);
            }
            ResendMailer::sendAdminNotificationEmail('new_listing', [
                'title' => $title,
                'sellerName' => $user['full_name'] ?? 'Seller',
                'price' => (float)$price,
                'city' => $city,
                'area' => $area
            ]);
        }

        if (file_exists(__DIR__ . '/services/AI/AIEventLogger.php')) {
            require_once __DIR__ . '/services/AI/AIEventLogger.php';
            AIEventLogger::logEvent('LISTING_CREATED', 'listing', $newListingId, (int)$user['id'], [
                'title' => $title,
                'price' => (float)$price,
                'city' => $city,
                'area' => $area,
                'images_count' => count($uploadedFiles)
            ]);
            AIEventLogger::logEvent('LISTING_SUBMITTED_FOR_REVIEW', 'listing', $newListingId, (int)$user['id'], [
                'title' => $title
            ]);
        }

        redirect("/product.php?id={$newListingId}", 'Ad published successfully and is now live on the marketplace!', 'success');
    }
}

$pageTitle = 'Post Free Ad - SARGODHAMART';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-3">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-9">
            <div class="card border border-success bg-success bg-opacity-10 p-3 mb-4 rounded-3">
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-success rounded-circle p-2 fs-5"><i class="bi bi-check2"></i></span>
                    <div>
                        <h6 class="fw-bold mb-0 text-success">Activated Seller Account (Unlimited Free Listings)</h6>
                        <small class="text-secondary">Your one-time Rs. 500 activation fee is verified. Normal listings are 100% FREE!</small>
                    </div>
                </div>
            </div>

            <div class="card border shadow-sm p-4 bg-white rounded-3">
                <h4 class="fw-bold mb-1">Post a Product Listing</h4>
                <p class="text-muted small mb-4">Reach local buyers across Sargodha, Shaheenabad & Sillanwali</p>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger small shadow-sm">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form method="POST" action="/post-ad.php" enctype="multipart/form-data">
                    <?= getCsrfField() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Category *</label>
                            <select name="category_id" id="category_id" class="form-select" required>
                                <option value="">-- Choose Category --</option>
                                <?php foreach ($cats as $c): ?>
                                    <option value="<?= $c['id'] ?>" <?= $catId == $c['id'] ? 'selected' : '' ?>><?= e($c['name']) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Subcategory</label>
                            <select name="subcategory_id" id="subcategory_id" class="form-select">
                                <option value="">Select Category First</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Ad Title *</label>
                        <input type="text" name="title" class="form-control" placeholder="e.g. Sahiwal Dairy Cow / Honda 125 2024 / iPhone 15 Pro Max" value="<?= e($title) ?>" required maxlength="200">
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Price (PKR) *</label>
                            <div class="input-group">
                                <span class="input-group-text">Rs.</span>
                                <input type="number" name="price" class="form-control" placeholder="250000" value="<?= e($price) ?>" required min="0">
                            </div>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Condition *</label>
                            <select name="condition" class="form-select" required>
                                <option value="Used" <?= $condition === 'Used' ? 'selected' : '' ?>>Used</option>
                                <option value="New" <?= $condition === 'New' ? 'selected' : '' ?>>Brand New</option>
                                <option value="Refurbished" <?= $condition === 'Refurbished' ? 'selected' : '' ?>>Refurbished</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Detailed Description *</label>
                        <textarea name="description" class="form-control" rows="5" placeholder="Include all specifications, warranty, lactation, documents, PTA status..." required><?= e($description) ?></textarea>
                    </div>

                    <div class="mb-4 p-3 bg-light rounded border">
                        <label class="form-label small fw-bold mb-1">Product Images * (Max <?= $maxImages ?> photos)</label>
                        <input type="file" name="images[]" id="listing_images_input" class="form-control" multiple accept="image/jpeg,image/png,image/webp" data-max="<?= $maxImages ?>" required>
                        <div class="row g-2 mt-2" id="image_preview_grid"></div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">City / Location Hub *</label>
                            <select name="city" class="form-select" required>
                                <option value="Sargodha" <?= $city === 'Sargodha' ? 'selected' : '' ?>>Sargodha</option>
                                <option value="Shaheenabad" <?= $city === 'Shaheenabad' ? 'selected' : '' ?>>Shaheenabad</option>
                                <option value="Sillanwali" <?= $city === 'Sillanwali' ? 'selected' : '' ?>>Sillanwali</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Bazaar / Area *</label>
                            <input type="text" name="area" class="form-control" placeholder="e.g. Canal Colony / Kutchery Bazaar" value="<?= e($area) ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Exact Meeting Spot</label>
                            <input type="text" name="exact_location" class="form-control" placeholder="e.g. Near Trust Plaza Gate #2" value="<?= e($exactLocation) ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Phone Number *</label>
                            <input type="text" name="phone_number" class="form-control" value="<?= e($phone) ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">WhatsApp Number *</label>
                            <input type="text" name="whatsapp_number" class="form-control" value="<?= e($whatsapp) ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Contact Preference *</label>
                            <select name="contact_preference" class="form-select" required>
                                <option value="Both" <?= $contactPref === 'Both' ? 'selected' : '' ?>>Calls & WhatsApp</option>
                                <option value="WhatsApp Only" <?= $contactPref === 'WhatsApp Only' ? 'selected' : '' ?>>WhatsApp Only</option>
                                <option value="Call Only" <?= $contactPref === 'Call Only' ? 'selected' : '' ?>>Call Only</option>
                                <option value="Chat Only" <?= $contactPref === 'Chat Only' ? 'selected' : '' ?>>In-App Chat Only</option>
                            </select>
                        </div>
                    </div>

                    <div class="border-top pt-3 text-end">
                        <button type="submit" class="btn btn-primary-green px-5 py-2 fw-bold">
                            Publish Ad for Free &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
