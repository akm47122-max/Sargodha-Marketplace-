<?php
/**
 * SARGODHAMART - Jobs & Employment Hub & Individual Job Pages
 * Buy • Sell • Jobs • Grow
 * Sargodha | Shaheenabad | Sillanwali
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';

$db = getDB();
$siteBrand = getSetting('website_name', 'SargodhaMart');
$jobId = (int)($_GET['id'] ?? 0);

// ====================================================================
// CASE 1: INDIVIDUAL JOB DETAIL PAGE (DYNAMIC JOB SEO)
// ====================================================================
if ($jobId > 0) {
    $stmt = $db->prepare("
        SELECT j.*, u.full_name as poster_name, u.city as user_city, u.activation_status 
        FROM jobs j 
        JOIN users u ON j.user_id = u.id 
        WHERE j.id = :id AND j.status = 'published'
        LIMIT 1
    ");
    $stmt->execute([':id' => $jobId]);
    $job = $stmt->fetch();

    if (!$job) {
        redirect('/jobs.php', 'Job posting not found or expired.', 'warning');
    }

    // Dynamic Job SEO & OpenGraph Configuration
    $pageTitle = "{$job['title']} – {$job['city']} | {$siteBrand} Jobs";
    $metaDescription = mb_strimwidth(strip_tags($job['description']), 0, 160, '...');
    $canonicalUrl = SITE_URL . "/jobs.php?id=" . $job['id'];
    $ogTitle = "{$job['title']} – {$job['city']} | {$siteBrand} Jobs";
    $ogDescription = $metaDescription;
    $ogUrl = $canonicalUrl;
    $ogType = 'article';
    $ogImage = getSetting('og_image_url', 'https://images.unsplash.com/photo-1521737711867-e3b97375f902?auto=format&fit=crop&w=1200&q=80');
    $twitterTitle = $ogTitle;
    $twitterDescription = $ogDescription;
    $twitterImage = $ogImage;

    // Contact sanitization
    $cleanPhone = preg_replace('/[^0-9]/', '', $job['phone_number']);
    $cleanWa = preg_replace('/[^0-9]/', '', $job['whatsapp_number']);
    if (str_starts_with($cleanWa, '0')) {
        $cleanWa = '92' . substr($cleanWa, 1);
    }

    include __DIR__ . '/includes/header.php';
    ?>
    <div class="container py-4">
        <!-- Breadcrumbs -->
        <nav aria-label="breadcrumb" class="mb-3">
            <ol class="breadcrumb small">
                <li class="breadcrumb-item"><a href="/index.php" class="text-decoration-none">Home</a></li>
                <li class="breadcrumb-item"><a href="/jobs.php" class="text-decoration-none">Jobs Hub</a></li>
                <li class="breadcrumb-item"><a href="/jobs.php?city=<?= urlencode($job['city']) ?>" class="text-decoration-none"><?= e($job['city']) ?></a></li>
                <li class="breadcrumb-item active text-truncate" style="max-width: 300px;"><?= e($job['title']) ?></li>
            </ol>
        </nav>

        <div class="row g-4">
            <!-- Left: Job Details -->
            <div class="col-12 col-lg-8">
                <div class="card border shadow-sm p-4 p-md-5 bg-white rounded-3 mb-4">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
                        <span class="badge <?= $job['post_type'] === 'need_worker' ? 'bg-primary' : 'bg-info text-dark' ?> px-3 py-2 rounded-pill fs-6">
                            <?= $job['post_type'] === 'need_worker' ? '🏢 Employer Hiring' : '👤 Worker Seeking Employment' ?>
                        </span>
                        <span class="text-muted small">
                            <i class="bi bi-clock me-1"></i> Posted <?= timeAgo($job['created_at']) ?>
                        </span>
                    </div>

                    <h2 class="fw-bold text-dark mb-3"><?= e($job['title']) ?></h2>

                    <div class="p-3 bg-light rounded-3 mb-4 d-flex flex-wrap gap-3 small border">
                        <div>
                            <span class="text-muted d-block">Location:</span>
                            <strong class="text-dark"><i class="bi bi-geo-alt text-danger me-1"></i><?= e($job['city']) ?> (<?= e($job['area']) ?>)</strong>
                        </div>
                        <?php if (!empty($job['category'])): ?>
                            <div class="border-start ps-3">
                                <span class="text-muted d-block">Industry / Category:</span>
                                <strong class="text-dark"><?= e($job['category']) ?></strong>
                            </div>
                        <?php endif; ?>
                        <?php if (!empty($job['salary_or_payment'])): ?>
                            <div class="border-start ps-3">
                                <span class="text-muted d-block">Compensation / Wage:</span>
                                <strong class="text-success fs-6"><?= e($job['salary_or_payment']) ?></strong>
                            </div>
                        <?php endif; ?>
                    </div>

                    <h5 class="fw-bold mb-2">Job Description & Scope of Work</h5>
                    <div class="text-secondary mb-4 lh-base" style="font-size: 0.95rem;">
                        <?= nl2br(e($job['description'])) ?>
                    </div>

                    <h6 class="fw-bold mb-2">Required Skills / Capabilities</h6>
                    <div class="d-flex flex-wrap gap-2 mb-4">
                        <?php foreach (explode(',', $job['skills']) as $skill): ?>
                            <?php if (trim($skill)): ?>
                                <span class="badge bg-secondary-subtle text-secondary border px-3 py-2 rounded-pill">
                                    <i class="bi bi-check2 me-1 text-success"></i><?= e(trim($skill)) ?>
                                </span>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>

                    <div class="p-3 bg-warning-subtle border border-warning-subtle rounded-3 small">
                        <i class="bi bi-shield-exclamation text-warning me-1"></i>
                        <strong>Safety Notice:</strong> SargodhaMart facilitates direct local connections. Never pay advance recruitment or registration fees to unverified parties.
                    </div>
                </div>
            </div>

            <!-- Right: Contact Card -->
            <div class="col-12 col-lg-4">
                <div class="card border shadow-sm p-4 bg-white rounded-3 sticky-top" style="top: 80px;">
                    <h5 class="fw-bold mb-3"><i class="bi bi-person-check text-success me-1"></i> Direct Contact Details</h5>
                    
                    <div class="mb-3">
                        <span class="text-muted small d-block">Posted By:</span>
                        <strong class="fs-6"><?= e($job['poster_name']) ?></strong>
                        <span class="badge bg-success-subtle text-success border border-success-subtle ms-1" style="font-size: 0.7rem;">Verified Local</span>
                    </div>

                    <div class="d-grid gap-2 mb-3">
                        <a href="https://wa.me/<?= e($cleanWa) ?>?text=<?= urlencode("Salam! Saw your job post '{$job['title']}' on SargodhaMart. I am interested.") ?>" 
                           target="_blank" class="btn btn-success py-2 fw-bold shadow-sm">
                            <i class="bi bi-whatsapp me-2"></i> Contact on WhatsApp
                        </a>
                        <a href="tel:<?= e($cleanPhone) ?>" class="btn btn-outline-dark py-2 fw-bold">
                            <i class="bi bi-telephone me-2"></i> Direct Call (<?= e($job['phone_number']) ?>)
                        </a>
                    </div>

                    <hr class="my-3">

                    <!-- Share -->
                    <div class="small">
                        <span class="text-muted d-block mb-2 font-bold">Share this job post:</span>
                        <div class="d-flex gap-2">
                            <a href="https://api.whatsapp.com/send?text=<?= urlencode("Check this job on SargodhaMart: {$job['title']} in {$job['city']} - {$canonicalUrl}") ?>" 
                               target="_blank" class="btn btn-outline-success btn-sm flex-fill">
                                <i class="bi bi-whatsapp"></i> WhatsApp
                            </a>
                            <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($canonicalUrl) ?>" 
                               target="_blank" class="btn btn-outline-primary btn-sm flex-fill">
                                <i class="bi bi-facebook"></i> Facebook
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php
    include __DIR__ . '/includes/footer.php';
    exit;
}

// ====================================================================
// CASE 2: JOBS DIRECTORY & LISTINGS VIEW
// ====================================================================
$pageTitle = "Jobs & Employment in Sargodha, Shaheenabad & Sillanwali - {$siteBrand}";
$metaDescription = "Find local jobs or hire skilled workers across Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp contact with verified local workers and employers.";

// Filters
$typeFilter = cleanInput($_GET['type'] ?? '');
$cityFilter = cleanInput($_GET['city'] ?? '');
$categoryFilter = cleanInput($_GET['category'] ?? '');
$searchQuery = cleanInput($_GET['q'] ?? '');

$sql = "SELECT j.*, u.full_name as poster_name, u.city as user_city, u.activation_status 
        FROM jobs j 
        JOIN users u ON j.user_id = u.id 
        WHERE j.status = 'published'";
$params = [];

if (!empty($typeFilter)) {
    $sql .= " AND j.post_type = :post_type";
    $params[':post_type'] = $typeFilter;
}

if (!empty($cityFilter)) {
    $sql .= " AND j.city = :city";
    $params[':city'] = $cityFilter;
}

if (!empty($categoryFilter)) {
    $sql .= " AND j.category = :category";
    $params[':category'] = $categoryFilter;
}

if (!empty($searchQuery)) {
    $sql .= " AND (j.title LIKE :q OR j.skills LIKE :q OR j.description LIKE :q)";
    $params[':q'] = '%' . $searchQuery . '%';
}

$sql .= " ORDER BY j.created_at DESC";

$stmt = $db->prepare($sql);
$stmt->execute($params);
$jobs = $stmt->fetchAll();

include __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <!-- Header Banner -->
    <div class="p-4 p-md-5 mb-4 rounded-4 bg-dark text-white border border-secondary shadow-lg position-relative overflow-hidden" 
         style="background: linear-gradient(135deg, #070b14 0%, #0d1b2a 50%, #1b263b 100%);">
        <div class="position-relative z-1 max-w-3xl">
            <span class="badge bg-primary bg-opacity-25 text-info border border-info border-opacity-25 px-3 py-2 rounded-pill mb-3">
                <i class="bi bi-briefcase-fill me-1"></i> Jobs & Employment Portal
            </span>
            <h1 class="display-6 fw-bold tracking-tight text-white mb-2">Local Work & Hiring Hub</h1>
            <p class="text-secondary lead fs-6 mb-4">
                Connect directly with job seekers and employers in Sargodha, Shaheenabad, and Sillanwali. No middlemen, direct Call & WhatsApp communication.
            </p>
            <div class="d-flex flex-wrap gap-2">
                <a href="/jobs.php?type=need_job" class="btn btn-outline-info rounded-pill px-4 <?= $typeFilter === 'need_job' ? 'active' : '' ?>">
                    <i class="bi bi-person-badge me-1"></i> People Seeking Work
                </a>
                <a href="/jobs.php?type=need_worker" class="btn btn-outline-primary rounded-pill px-4 <?= $typeFilter === 'need_worker' ? 'active' : '' ?>">
                    <i class="bi bi-building me-1"></i> Employers Hiring
                </a>
                <a href="/post-job.php" class="btn btn-primary rounded-pill px-4 ms-auto shadow-sm">
                    <i class="bi bi-plus-circle me-1"></i> Post a Job / Seek Work
                </a>
            </div>
        </div>
    </div>

    <!-- Filter Bar -->
    <form method="GET" action="/jobs.php" class="card card-body bg-dark text-white border-secondary mb-4 shadow-sm">
        <div class="row g-2 align-items-center">
            <div class="col-md-4">
                <div class="input-group">
                    <span class="input-group-text bg-black text-secondary border-secondary"><i class="bi bi-search"></i></span>
                    <input type="text" name="q" value="<?= e($searchQuery) ?>" class="form-control bg-black text-white border-secondary" placeholder="Search by job title or skill...">
                </div>
            </div>
            <div class="col-md-3">
                <select name="city" class="form-select bg-black text-white border-secondary">
                    <option value="">All Locations (Sargodha District)</option>
                    <option value="Sargodha" <?= $cityFilter === 'Sargodha' ? 'selected' : '' ?>>Sargodha City</option>
                    <option value="Shaheenabad" <?= $cityFilter === 'Shaheenabad' ? 'selected' : '' ?>>Shaheenabad</option>
                    <option value="Sillanwali" <?= $cityFilter === 'Sillanwali' ? 'selected' : '' ?>>Sillanwali</option>
                </select>
            </div>
            <div class="col-md-3">
                <select name="type" class="form-select bg-black text-white border-secondary">
                    <option value="">All Job Posts</option>
                    <option value="need_worker" <?= $typeFilter === 'need_worker' ? 'selected' : '' ?>>I Need a Worker (Hiring)</option>
                    <option value="need_job" <?= $typeFilter === 'need_job' ? 'selected' : '' ?>>I Need a Job (Work Wanted)</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Filter</button>
            </div>
        </div>
    </form>

    <!-- Jobs Grid -->
    <div class="row g-3">
        <?php if (empty($jobs)): ?>
            <div class="col-12 py-5 text-center text-secondary">
                <i class="bi bi-briefcase fs-1 d-block mb-3 opacity-50"></i>
                <h5>No job posts found matching your criteria</h5>
                <p>Try adjusting your search or be the first to post a job opportunity!</p>
                <a href="/post-job.php" class="btn btn-primary rounded-pill px-4 mt-2">Post Job Now</a>
            </div>
        <?php else: ?>
            <?php foreach ($jobs as $job): ?>
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 bg-dark text-white border-secondary shadow-sm hover-shadow transition-all">
                        <div class="card-body d-flex flex-column">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <span class="badge <?= $job['post_type'] === 'need_worker' ? 'bg-primary' : 'bg-info text-dark' ?> rounded-pill">
                                    <?= $job['post_type'] === 'need_worker' ? '🏢 Hiring Worker' : '👤 Seeking Work' ?>
                                </span>
                                <small class="text-secondary"><i class="bi bi-geo-alt"></i> <?= e($job['city']) ?> (<?= e($job['area']) ?>)</small>
                            </div>
                            <h5 class="card-title fw-bold text-white mb-2">
                                <a href="/jobs.php?id=<?= $job['id'] ?>" class="text-white text-decoration-none">
                                    <?= e($job['title']) ?>
                                </a>
                            </h5>
                            <p class="card-text text-secondary small flex-grow-1">
                                <?= nl2br(e(mb_strimwidth($job['description'], 0, 140, '...'))) ?>
                            </p>
                            <div class="mb-3">
                                <span class="text-white-50 small d-block mb-1"><strong>Skills:</strong> <?= e($job['skills']) ?></span>
                                <?php if (!empty($job['salary_or_payment'])): ?>
                                    <span class="text-success small fw-semibold d-block"><strong>Compensation:</strong> <?= e($job['salary_or_payment']) ?></span>
                                <?php endif; ?>
                            </div>
                            <div class="pt-3 border-top border-secondary d-flex gap-2">
                                <a href="/jobs.php?id=<?= $job['id'] ?>" class="btn btn-sm btn-outline-info flex-fill">
                                    <i class="bi bi-eye me-1"></i> Details
                                </a>
                                <a href="https://wa.me/<?= preg_replace('/[^0-9]/', '', $job['whatsapp_number']) ?>?text=<?= urlencode('Salam! Saw your job post "' . $job['title'] . '" on SargodhaMart.') ?>" 
                                   target="_blank" class="btn btn-sm btn-success flex-fill">
                                    <i class="bi bi-whatsapp me-1"></i> WhatsApp
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
</div>

<?php include __DIR__ . '/includes/footer.php'; ?>
