<?php
/**
 * SARGODHAMART - User Portal: My Job Posts
 * View, Edit, Pause, and Delete own job advertisements and worker requests.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user = currentUser();
$db = getDB();

// Handle Delete Action with Strict Ownership Check
if (isset($_POST['action']) && $_POST['action'] === 'delete') {
    verifyCsrfOrDie();
    $deleteId = (int)($_POST['job_id'] ?? 0);
    $delStmt = $db->prepare("DELETE FROM jobs WHERE id = :id AND user_id = :uid");
    $delStmt->execute([':id' => $deleteId, ':uid' => $user['id']]);
    redirect('/user/my-jobs.php', 'Job post deleted successfully.', 'info');
}

// Handle Status Toggle (Pause / Resume)
if (isset($_POST['action']) && $_POST['action'] === 'toggle_pause') {
    verifyCsrfOrDie();
    $toggleId = (int)($_POST['job_id'] ?? 0);
    $stmt = $db->prepare("SELECT status FROM jobs WHERE id = :id AND user_id = :uid LIMIT 1");
    $stmt->execute([':id' => $toggleId, ':uid' => $user['id']]);
    $currentStatus = $stmt->fetchColumn();

    if ($currentStatus === 'published') {
        $db->prepare("UPDATE jobs SET status = 'disabled' WHERE id = :id AND user_id = :uid")->execute([':id' => $toggleId, ':uid' => $user['id']]);
        redirect('/user/my-jobs.php', 'Job post has been paused.', 'warning');
    } elseif ($currentStatus === 'disabled') {
        $db->prepare("UPDATE jobs SET status = 'published' WHERE id = :id AND user_id = :uid")->execute([':id' => $toggleId, ':uid' => $user['id']]);
        redirect('/user/my-jobs.php', 'Job post has been resumed and is now live!', 'success');
    }
}

// Fetch user's job postings
$stmt = $db->prepare("
    SELECT * FROM jobs 
    WHERE user_id = :uid 
    ORDER BY id DESC
");
$stmt->execute([':uid' => $user['id']]);
$myJobs = $stmt->fetchAll(PDO::FETCH_ASSOC);

$pageTitle = 'My Jobs & Rozgar Posts - ' . SITE_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <!-- Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold mb-1">My Jobs & Employment Posts</h3>
            <p class="text-muted small mb-0">Manage your employment listings: I Need a Job & I Need a Worker</p>
        </div>
        <div class="d-flex gap-2">
            <a href="/user/my-listings.php" class="btn btn-outline-secondary btn-sm px-3 py-2 fw-semibold">
                <i class="bi bi-shop me-1"></i> My Product Ads
            </a>
            <a href="/post-job.php" class="btn btn-primary btn-sm px-3 py-2 fw-semibold">
                <i class="bi bi-plus-circle me-1"></i> Post New Job
            </a>
        </div>
    </div>

    <!-- Navigation Pills -->
    <div class="d-flex gap-2 mb-4">
        <a href="/user/dashboard.php" class="btn btn-sm btn-outline-secondary">Dashboard</a>
        <a href="/user/my-listings.php" class="btn btn-sm btn-outline-secondary">My Products</a>
        <a href="/user/my-jobs.php" class="btn btn-sm btn-dark fw-bold">My Jobs (<?= count($myJobs) ?>)</a>
    </div>

    <?php if (empty($myJobs)): ?>
        <div class="card border-0 shadow-sm rounded-4 p-5 text-center bg-white">
            <div class="fs-1 text-muted mb-2">💼</div>
            <h5 class="fw-bold">You haven't posted any jobs yet</h5>
            <p class="text-muted small mb-3">Looking to hire workers or seeking local work in Sargodha, Shaheenabad or Sillanwali?</p>
            <div>
                <a href="/post-job.php" class="btn btn-primary rounded-pill px-4 fw-bold">Post a Job Now</a>
            </div>
        </div>
    <?php else: ?>
        <div class="d-flex flex-column gap-3">
            <?php foreach ($myJobs as $job): ?>
                <div class="card border shadow-sm p-4 bg-white rounded-3">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-md-8">
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge <?= $job['post_type'] === 'need_worker' ? 'bg-primary' : 'bg-info text-dark' ?> rounded-pill">
                                    <?= $job['post_type'] === 'need_worker' ? '🏢 I Need a Worker (Hiring)' : '👤 I Need a Job (Work Wanted)' ?>
                                </span>
                                <?php if ($job['status'] === 'published'): ?>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle">
                                        <i class="bi bi-check-circle me-1"></i> Live
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary-subtle text-secondary border">
                                        Paused
                                    </span>
                                <?php endif; ?>
                                <span class="text-muted small">· <?= e($job['city']) ?> (<?= e($job['area']) ?>)</span>
                            </div>

                            <h5 class="fw-bold mb-1">
                                <a href="/jobs.php?id=<?= $job['id'] ?>" class="text-dark text-decoration-none">
                                    <?= e($job['title']) ?>
                                </a>
                            </h5>

                            <p class="text-muted small mb-2 text-truncate" style="max-width: 600px;">
                                <?= e($job['description']) ?>
                            </p>

                            <div class="small text-secondary">
                                <span class="me-3"><strong>Trade:</strong> <?= e($job['category']) ?></span>
                                <span class="me-3"><strong>Compensation:</strong> <?= e($job['salary_or_payment'] ?: 'Open') ?></span>
                                <span><strong>Posted:</strong> <?= timeAgo($job['created_at']) ?></span>
                            </div>
                        </div>

                        <!-- Action Buttons: [View] [Edit] [Delete] -->
                        <div class="col-12 col-md-4 text-md-end d-flex flex-md-column gap-2 justify-content-end">
                            <a href="/jobs.php?id=<?= $job['id'] ?>" class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-eye me-1"></i> View Post
                            </a>

                            <a href="/user/edit-job.php?id=<?= $job['id'] ?>" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-pencil-square me-1"></i> Edit Post
                            </a>

                            <!-- Pause / Resume -->
                            <form method="POST" action="/user/my-jobs.php" class="d-inline">
                                <?= getCsrfField() ?>
                                <input type="hidden" name="action" value="toggle_pause">
                                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                <button type="submit" class="btn btn-outline-warning btn-sm w-100">
                                    <?= $job['status'] === 'published' ? '<i class="bi bi-pause"></i> Pause' : '<i class="bi bi-play"></i> Resume' ?>
                                </button>
                            </form>

                            <!-- Delete -->
                            <form method="POST" action="/user/my-jobs.php" class="d-inline" onsubmit="return confirm('Are you sure you want to permanently delete this job post?');">
                                <?= getCsrfField() ?>
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="job_id" value="<?= $job['id'] ?>">
                                <button type="submit" class="btn btn-outline-danger btn-sm w-100">
                                    <i class="bi bi-trash"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
