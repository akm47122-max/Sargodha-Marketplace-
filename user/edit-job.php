<?php
/**
 * SARGODHAMART - User Portal: Edit Job Post
 * Strict ownership verification & field updates for both Job Seeker and Employer posts.
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user = currentUser();
$db = getDB();

$jobId = (int)($_GET['id'] ?? 0);
if ($jobId <= 0) {
    redirect('/user/my-jobs.php', 'Invalid job post ID.', 'error');
}

// Fetch job post
$stmt = $db->prepare("SELECT * FROM jobs WHERE id = :id LIMIT 1");
$stmt->execute([':id' => $jobId]);
$job = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$job) {
    redirect('/user/my-jobs.php', 'Job post not found.', 'error');
}

// Strict Ownership Verification (403 Forbidden if mismatched)
if ((int)$job['user_id'] !== (int)$user['id'] && !isAdmin()) {
    http_response_code(403);
    die("<div style='font-family:sans-serif;padding:40px;text-align:center;'><h2>403 Forbidden</h2><p>You are not authorized to edit this job post.</p><a href='/user/my-jobs.php'>Return to My Jobs</a></div>");
}

$error = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();

    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $workingHours = trim($_POST['working_hours'] ?? '');
    $salary = trim($_POST['salary_or_payment'] ?? '');
    $city = trim($_POST['city'] ?? 'Sargodha');
    $area = trim($_POST['area'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $phone = trim($_POST['phone_number'] ?? '');
    $whatsapp = trim($_POST['whatsapp_number'] ?? '');

    if (strlen($title) < 5) {
        $error = 'Title must be at least 5 characters.';
    } elseif (empty($skills)) {
        $error = 'Skills cannot be empty.';
    } elseif (empty($description)) {
        $error = 'Description cannot be empty.';
    } else {
        $stmt = $db->prepare("
            UPDATE jobs SET
                title = :title,
                category = :cat,
                skills = :skills,
                experience = :exp,
                working_hours = :hours,
                salary_or_payment = :salary,
                city = :city,
                area = :area,
                description = :desc,
                phone_number = :phone,
                whatsapp_number = :wa,
                updated_at = NOW()
            WHERE id = :id AND user_id = :uid
        ");
        $stmt->execute([
            ':title' => $title,
            ':cat' => $category,
            ':skills' => $skills,
            ':exp' => $experience ?: null,
            ':hours' => $workingHours ?: null,
            ':salary' => $salary ?: null,
            ':city' => $city,
            ':area' => $area,
            ':desc' => $description,
            ':phone' => $phone,
            ':wa' => $whatsapp,
            ':id' => $jobId,
            ':uid' => $user['id']
        ]);

        redirect('/user/my-jobs.php', 'Your job post has been updated successfully.', 'success');
    }
}

$pageTitle = 'Edit Job Post - ' . e($job['title']) . ' - ' . SITE_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="/index.php" class="text-decoration-none text-success">Home</a></li>
            <li class="breadcrumb-item"><a href="/user/dashboard.php" class="text-decoration-none text-success">Dashboard</a></li>
            <li class="breadcrumb-item"><a href="/user/my-jobs.php" class="text-decoration-none text-success">My Jobs</a></li>
            <li class="breadcrumb-item active" aria-current="page">Edit Post</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border shadow-sm p-4 p-md-5 bg-white rounded-4">
                <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
                    <div>
                        <h4 class="fw-bold mb-0">Edit Job Post</h4>
                        <small class="text-muted">Type: <?= $job['post_type'] === 'need_worker' ? '🏢 I Need a Worker' : '👤 I Need a Job' ?></small>
                    </div>
                    <span class="badge <?= $job['post_type'] === 'need_worker' ? 'bg-primary' : 'bg-info text-dark' ?> px-3 py-2 rounded-pill">
                        <?= $job['post_type'] === 'need_worker' ? 'Employer Post' : 'Job Seeker Post' ?>
                    </span>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger small py-2"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/user/edit-job.php?id=<?= $job['id'] ?>">
                    <?= getCsrfField() ?>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Post Title *</label>
                        <input type="text" name="title" class="form-control" value="<?= e($job['title']) ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Category / Trade *</label>
                            <input type="text" name="category" class="form-control" value="<?= e($job['category']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Skills / Capabilities *</label>
                            <input type="text" name="skills" class="form-control" value="<?= e($job['skills']) ?>" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">City *</label>
                            <select name="city" class="form-select" required>
                                <option value="Sargodha" <?= $job['city'] === 'Sargodha' ? 'selected' : '' ?>>Sargodha City</option>
                                <option value="Shaheenabad" <?= $job['city'] === 'Shaheenabad' ? 'selected' : '' ?>>Shaheenabad</option>
                                <option value="Sillanwali" <?= $job['city'] === 'Sillanwali' ? 'selected' : '' ?>>Sillanwali</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Area / Locality *</label>
                            <input type="text" name="area" class="form-control" value="<?= e($job['area']) ?>" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Compensation / Salary</label>
                            <input type="text" name="salary_or_payment" class="form-control" value="<?= e($job['salary_or_payment'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Experience</label>
                            <input type="text" name="experience" class="form-control" value="<?= e($job['experience'] ?? '') ?>">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Working Hours</label>
                            <input type="text" name="working_hours" class="form-control" value="<?= e($job['working_hours'] ?? '') ?>">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Details / Description *</label>
                        <textarea name="description" rows="4" class="form-control" required><?= e($job['description']) ?></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Phone Number *</label>
                            <input type="text" name="phone_number" class="form-control" value="<?= e($job['phone_number']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">WhatsApp Number *</label>
                            <input type="text" name="whatsapp_number" class="form-control" value="<?= e($job['whatsapp_number']) ?>" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/user/my-jobs.php" class="btn btn-outline-secondary btn-sm">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                            <i class="bi bi-check-lg me-1"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
