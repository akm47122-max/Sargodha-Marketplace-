<?php
/**
 * Global Header Component
 * Universal Top Bar Contract: Brand · 4-5 Navigation Links · Primary Action (Post Ad)
 */
require_once __DIR__ . '/functions.php';

$user = currentUser();
$unreadNotifs = $user ? getUnreadNotificationCount($user['id']) : 0;
$unreadMsgs = $user ? getUnreadMessageCount($user['id']) : 0;
$flash = getFlash();

$siteName = getSetting('website_name', SITE_NAME);
$siteTagline = getSetting('tagline', SITE_TAGLINE);
$whatsappNum = getSetting('official_whatsapp', '03127453108');
$cleanWaNum = preg_replace('/[^0-9]/', '', $whatsappNum);
if (str_starts_with($cleanWaNum, '0')) {
    $cleanWaNum = '92' . substr($cleanWaNum, 1);
}

$pageTitle = $pageTitle ?? ($siteName . ' - ' . $siteTagline . ' (Sargodha | Shaheenabad | Sillanwali)');
$metaDesc = $metaDescription ?? getSetting('default_seo_description', 'Dedicated local online marketplace and employment hub for Sargodha, Shaheenabad, and Sillanwali.');
$ogTitleFinal = $ogTitle ?? $pageTitle;
$ogDescFinal = $ogDescription ?? $metaDesc;
$ogUrlFinal = $ogUrl ?? $canonicalUrl ?? (SITE_URL . ($_SERVER['REQUEST_URI'] ?? '/'));
$ogImageFinal = $ogImage ?? getSetting('og_image_url', 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=80');
$ogTypeFinal = $ogType ?? 'website';

// Active Top Bar Announcements
$topAnnouncements = getActiveAnnouncementsPHP('top_bar');

// Maintenance Mode Enforcement (Admins bypass maintenance)
if (isMaintenanceModePHP() && !isAdmin() && !str_contains($_SERVER['REQUEST_URI'] ?? '', '/admin')) {
    $maintTitle = getSetting('maintenance_title', 'SargodhaMart Scheduled System Upgrade');
    $maintMsg = getSetting('maintenance_message', 'We are performing routine maintenance to improve your marketplace experience. We will be back online shortly.');
    ?>
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title><?= e($maintTitle) ?> - <?= e($siteName) ?></title>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    </head>
    <body class="bg-light d-flex align-items-center min-vh-100">
        <div class="container text-center py-5">
            <div class="card border-0 shadow-lg mx-auto p-4 p-md-5 rounded-4" style="max-width: 600px;">
                <div class="mb-3 text-warning fs-1"><i class="bi bi-tools"></i></div>
                <h2 class="fw-bold mb-3"><?= e($maintTitle) ?></h2>
                <p class="text-muted lead fs-6 mb-4"><?= nl2br(e($maintMsg)) ?></p>
                <div class="d-flex justify-content-center gap-2">
                    <a href="https://wa.me/<?= e($cleanWaNum) ?>" target="_blank" class="btn btn-success rounded-pill px-4">
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp Helpline
                    </a>
                    <a href="/admin/login.php" class="btn btn-outline-secondary rounded-pill px-4">
                        <i class="bi bi-shield-lock me-1"></i> Admin Login
                    </a>
                </div>
            </div>
        </div>
    </body>
    </html>
    <?php
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= e($pageTitle) ?></title>
    <meta name="description" content="<?= e($metaDesc) ?>">
    <?php if (!empty($canonicalUrl)): ?>
        <link rel="canonical" href="<?= e($canonicalUrl) ?>">
    <?php endif; ?>

    <!-- OpenGraph Social Sharing Metadata -->
    <meta property="og:title" content="<?= e($ogTitleFinal) ?>">
    <meta property="og:description" content="<?= e($ogDescFinal) ?>">
    <meta property="og:url" content="<?= e($ogUrlFinal) ?>">
    <meta property="og:type" content="<?= e($ogTypeFinal) ?>">
    <meta property="og:site_name" content="<?= e($siteName) ?>">
    <?php if (!empty($ogImageFinal)): ?>
        <meta property="og:image" content="<?= e($ogImageFinal) ?>">
    <?php endif; ?>

    <!-- Twitter / X Card Metadata -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="<?= e($twitterTitle ?? $ogTitleFinal) ?>">
    <meta name="twitter:description" content="<?= e($twitterDescription ?? $ogDescFinal) ?>">
    <?php if (!empty($ogImageFinal)): ?>
        <meta name="twitter:image" content="<?= e($twitterImage ?? $ogImageFinal) ?>">
    <?php endif; ?>

    <!-- Bootstrap 5 CSS & Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">

    <!-- Custom Marketplace Styles -->
    <link rel="stylesheet" href="/assets/css/style.css">
</head>
<body>

<?php if (!empty($topAnnouncements)): ?>
    <!-- Top Bar Live Broadcast Announcements -->
    <?php foreach ($topAnnouncements as $ann): ?>
        <div class="alert <?= $ann['is_highlighted'] ? 'alert-warning border-warning' : 'alert-success border-success' ?> mb-0 text-center py-2 px-3 rounded-0 small fw-bold d-flex justify-content-center align-items-center gap-2">
            <span><?= e($ann['title']) ?></span>
            <span class="d-none d-md-inline text-muted fw-normal">| <?= e($ann['message']) ?></span>
            <?php if (!empty($ann['button_text']) && !empty($ann['button_url'])): ?>
                <a href="<?= e($ann['button_url']) ?>" target="_blank" class="btn btn-sm <?= $ann['is_highlighted'] ? 'btn-dark' : 'btn-success' ?> py-0 px-2 rounded-pill ms-2" style="font-size: 0.72rem;">
                    <?= e($ann['button_text']) ?> <i class="bi bi-arrow-right"></i>
                </a>
            <?php endif; ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Regional Local Strip -->
<div class="city-strip text-center">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="d-none d-md-block text-truncate">
            <i class="bi bi-shield-check text-success me-1"></i> Verified Local Marketplace for Sargodha District
        </div>
        <div class="mx-auto mx-md-0">
            <span class="text-uppercase tracking-wider me-2 text-white-50">Local Hubs:</span>
            <a href="/search.php?city=Sargodha" class="city-badge me-2"><i class="bi bi-geo-alt"></i> Sargodha</a>
            <span class="text-white-50">·</span>
            <a href="/search.php?city=Shaheenabad" class="city-badge mx-2"><i class="bi bi-geo-alt"></i> Shaheenabad</a>
            <span class="text-white-50">·</span>
            <a href="/search.php?city=Sillanwali" class="city-badge ms-2"><i class="bi bi-geo-alt"></i> Sillanwali</a>
        </div>
        <div class="d-none d-lg-block">
            <span class="text-white-50">Helpline:</span> <a href="https://wa.me/<?= e($cleanWaNum) ?>" target="_blank" class="text-success text-decoration-none fw-bold"><i class="bi bi-whatsapp"></i> <?= e($whatsappNum) ?></a>
        </div>
    </div>
</div>

<!-- Main Top Navigation -->
<nav class="navbar navbar-expand-lg navbar-custom sticky-top">
    <div class="container">
        <!-- Zone 1: Wordmark Brand -->
        <a class="brand-mark d-flex align-items-center gap-2 text-decoration-none" href="/index.php">
            <span class="text-success fs-3"><i class="bi bi-shop"></i></span>
            <div>
                <span class="fw-bold fs-5 text-dark d-block lh-1"><?= e($siteName) ?></span>
                <small class="text-success fw-bold text-uppercase d-block" style="font-size: 0.65rem; letter-spacing: 0.5px;"><?= e($siteTagline) ?></small>
            </div>
        </a>

        <!-- Mobile Toggler -->
        <button class="navbar-toggler border-0 p-1" type="button" data-bs-toggle="collapse" data-bs-target="#navContent" aria-controls="navContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Zone 2: Navigation Links -->
        <div class="collapse navbar-collapse" id="navContent">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 gap-lg-1">
                <li class="nav-item">
                    <a class="nav-link fw-semibold px-2" href="/search.php">Browse All Ads</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold px-2" href="/categories.php">Categories</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold px-2" href="/search.php?featured=1">⭐ Featured</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold px-2" href="/rules.php">Fee & Rules</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link fw-semibold px-2" href="/contact.php">Helpline</a>
                </li>
            </ul>

            <!-- Zone 3: Actions & Account Menu -->
            <div class="d-flex align-items-center gap-2">
                <a href="/post-ad.php" class="btn btn-primary-green btn-sm px-3 py-2 text-nowrap shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Post an Ad
                </a>

                <?php if ($user): ?>
                    <!-- Logged In User Dropdown -->
                    <div class="dropdown">
                        <button class="btn btn-outline-secondary btn-sm dropdown-toggle d-flex align-items-center gap-2" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            <span class="badge bg-success rounded-circle p-1"><i class="bi bi-person text-white"></i></span>
                            <span class="d-none d-sm-inline text-truncate" style="max-width: 110px;"><?= e(explode(' ', $user['full_name'])[0]) ?></span>
                            <?php if ($unreadNotifs + $unreadMsgs > 0): ?>
                                <span class="badge bg-danger rounded-pill"><?= $unreadNotifs + $unreadMsgs ?></span>
                            <?php endif; ?>
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end shadow-sm border-light">
                            <li class="dropdown-header text-truncate">
                                <strong><?= e($user['full_name']) ?></strong><br>
                                <small class="text-muted"><?= e($user['city']) ?> (<?= e($user['role']) ?>)</small>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="/user/dashboard.php"><i class="bi bi-speedometer2 me-2"></i> Dashboard</a></li>
                            <li><a class="dropdown-item" href="/user/my-listings.php"><i class="bi bi-card-list me-2"></i> My Ads</a></li>
                            <li><a class="dropdown-item" href="/user/payments.php"><i class="bi bi-receipt me-2"></i> Payments & Fees</a></li>
                            <li>
                                <a class="dropdown-item d-flex justify-content-between align-items-center" href="/user/messages.php">
                                    <span><i class="bi bi-chat-dots me-2"></i> Messages</span>
                                    <?php if ($unreadMsgs > 0): ?><span class="badge bg-primary rounded-pill"><?= $unreadMsgs ?></span><?php endif; ?>
                                </a>
                            </li>
                            <li>
                                <a class="dropdown-item d-flex justify-content-between align-items-center" href="/user/notifications.php">
                                    <span><i class="bi bi-bell me-2"></i> Notifications</span>
                                    <?php if ($unreadNotifs > 0): ?><span class="badge bg-danger rounded-pill"><?= $unreadNotifs ?></span><?php endif; ?>
                                </a>
                            </li>
                            <li><a class="dropdown-item" href="/user/favorites.php"><i class="bi bi-heart me-2"></i> My Favorites</a></li>
                            <li><a class="dropdown-item" href="/user/profile.php"><i class="bi bi-person-gear me-2"></i> Profile Settings</a></li>
                            
                            <?php if (isAdmin()): ?>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item text-danger fw-bold" href="/admin/index.php"><i class="bi bi-shield-lock me-2"></i> Admin Panel</a></li>
                            <?php endif; ?>

                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="/logout.php"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <!-- Guest Links -->
                    <a href="/login.php" class="btn btn-outline-dark btn-sm px-3 py-2 text-nowrap">Login</a>
                    <a href="/register.php" class="btn btn-outline-secondary btn-sm px-3 py-2 text-nowrap d-none d-sm-inline-block">Register</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</nav>

<!-- Global Flash Messages -->
<?php if ($flash): ?>
    <div class="container mt-3">
        <div class="alert alert-<?= e($flash['type']) ?> alert-dismissible fade show shadow-sm" role="alert">
            <i class="bi bi-info-circle me-2"></i> <?= e($flash['message']) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    </div>
<?php endif; ?>

<main class="py-4">
