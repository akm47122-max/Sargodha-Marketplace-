<?php
/**
 * SARGODHAMART - Post Job or Worker Request
 * "I Need a Job / Mujhe Job Chahiye" or "I Need a Worker / Mujhe Banda Chahiye"
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth_check.php';
require_once __DIR__ . '/services/AI/AIEventLogger.php';

$user = currentUser();
$db = getDB();

// Only verified active sellers can post (or active members per site settings)
if ($user['activation_status'] !== 'approved' && !isAdmin()) {
    redirect('/activate-seller.php', 'Please complete your one-time Rs. 1,000 lifetime seller activation to post job opportunities and worker inquiries.', 'warning');
}

$error = null;
$postType = cleanInput($_GET['type'] ?? 'need_worker');
if (!in_array($postType, ['need_worker', 'need_job'])) {
    $postType = 'need_worker';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();

    $postType = cleanInput($_POST['post_type'] ?? 'need_worker');
    $title = trim($_POST['title'] ?? '');
    $category = trim($_POST['category'] ?? '');
    $skills = trim($_POST['skills'] ?? '');
    $experience = trim($_POST['experience'] ?? '');
    $workingHours = trim($_POST['working_hours'] ?? '');
    $salary = trim($_POST['salary_or_payment'] ?? '');
    $city = trim($_POST['city'] ?? 'Sargodha');
    $area = trim($_POST['area'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $phone = trim($_POST['phone_number'] ?? $user['phone']);
    $whatsapp = trim($_POST['whatsapp_number'] ?? $user['phone']);

    if (strlen($title) < 5) {
        $error = 'Please enter a descriptive title (at least 5 characters).';
    } elseif (empty($skills)) {
        $error = 'Please specify required skills or capabilities.';
    } elseif (empty($description)) {
        $error = 'Please provide details about the job or work requirements.';
    } else {
        $stmt = $db->prepare("
            INSERT INTO jobs (user_id, post_type, title, category, skills, experience, working_hours, salary_or_payment, city, area, description, phone_number, whatsapp_number, status, created_at)
            VALUES (:uid, :ptype, :title, :cat, :skills, :exp, :hours, :salary, :city, :area, :desc, :phone, :wa, 'published', NOW())
        ");
        $stmt->execute([
            ':uid' => $user['id'],
            ':ptype' => $postType,
            ':title' => $title,
            ':cat' => $category ?: 'General Services',
            ':skills' => $skills,
            ':exp' => $experience ?: null,
            ':hours' => $workingHours ?: null,
            ':salary' => $salary ?: null,
            ':city' => $city,
            ':area' => $area,
            ':desc' => $description,
            ':phone' => $phone,
            ':wa' => $whatsapp
        ]);

        $newJobId = (int)$db->lastInsertId();

        // Log AI event
        AIEventLogger::logEvent('JOB_CREATED', 'job', $newJobId, (int)$user['id'], [
            'title' => $title,
            'type' => $postType,
            'city' => $city
        ]);

        redirect("/jobs.php?id={$newJobId}", 'Your employment post is now LIVE on SargodhaMart Jobs Hub!', 'success');
    }
}

$pageTitle = 'Post a Job / Seek Work - ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border shadow-sm p-4 p-md-5 bg-white rounded-4">
                <div class="d-flex align-items-center gap-2 mb-4 pb-2 border-bottom">
                    <span class="fs-3 text-primary"><i class="bi bi-briefcase-fill"></i></span>
                    <div>
                        <h4 class="fw-bold mb-0">Post to Jobs & Rozgar Portal</h4>
                        <small class="text-muted">Hire local skilled workers or advertise your services in Sargodha, Shaheenabad & Sillanwali</small>
                    </div>
                </div>

                <?php if ($error): ?>
                    <div class="alert alert-danger small py-2"><?= e($error) ?></div>
                <?php endif; ?>

                <form method="POST" action="/post-job.php">
                    <?= getCsrfField() ?>

                    <!-- Posting Intent Type -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-dark">What do you want to post?</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <label class="card p-3 border rounded-3 cursor-pointer text-center h-100 <?= $postType === 'need_worker' ? 'border-primary bg-primary-subtle' : '' ?>">
                                    <input type="radio" name="post_type" value="need_worker" class="d-none" <?= $postType === 'need_worker' ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <div class="fs-4 text-primary mb-1">🏢</div>
                                    <strong class="d-block small text-dark">I Need a Worker</strong>
                                    <span class="text-muted" style="font-size: 0.7rem;">Mujhe Banda Chahiye (Hiring)</span>
                                </label>
                            </div>
                            <div class="col-6">
                                <label class="card p-3 border rounded-3 cursor-pointer text-center h-100 <?= $postType === 'need_job' ? 'border-info bg-info-subtle' : '' ?>">
                                    <input type="radio" name="post_type" value="need_job" class="d-none" <?= $postType === 'need_job' ? 'checked' : '' ?> onchange="this.form.submit()">
                                    <div class="fs-4 text-info mb-1">👤</div>
                                    <strong class="d-block small text-dark">I Need a Job</strong>
                                    <span class="text-muted" style="font-size: 0.7rem;">Mujhe Job Chahiye (Seeking Work)</span>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Post Title *</label>
                        <input type="text" name="title" class="form-control" placeholder="<?= $postType === 'need_worker' ? 'e.g. Experienced Tractor Driver Needed in Sillanwali Farm' : 'e.g. Expert Electrician & AC Technician Available for Daily/Monthly Work' ?>" required>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Category / Trade *</label>
                            <input type="text" name="category" class="form-control" placeholder="e.g. Agriculture, Driving, Electrician, Sales, Shop Staff" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Skills / Capabilities *</label>
                            <input type="text" name="skills" class="form-control" placeholder="Comma separated, e.g. Driving, Mechanical, Welding" required>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">City / Hub *</label>
                            <select name="city" class="form-select" required>
                                <option value="Sargodha">Sargodha City</option>
                                <option value="Shaheenabad">Shaheenabad</option>
                                <option value="Sillanwali">Sillanwali</option>
                            </select>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Area / Locality *</label>
                            <input type="text" name="area" class="form-control" placeholder="e.g. Satellite Town, Mandi, Shaheen Chowk" required>
                        </div>
                        <div class="col-12 col-md-4">
                            <label class="form-label small fw-bold">Compensation / Salary (PKR)</label>
                            <input type="text" name="salary_or_payment" class="form-control" placeholder="e.g. Rs. 35,000/month or Negotiable">
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Experience (Optional)</label>
                            <input type="text" name="experience" class="form-control" placeholder="e.g. 3+ Years Experience">
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Working Hours (Optional)</label>
                            <input type="text" name="working_hours" class="form-control" placeholder="e.g. 9:00 AM - 6:00 PM">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Full Details / Description *</label>
                        <textarea name="description" rows="4" class="form-control" placeholder="Describe the responsibilities, requirements, benefits, or qualifications..." required></textarea>
                    </div>

                    <div class="row g-3 mb-4">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Phone Number for Calls *</label>
                            <input type="text" name="phone_number" class="form-control" value="<?= e($user['phone']) ?>" required>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">WhatsApp Number *</label>
                            <input type="text" name="whatsapp_number" class="form-control" value="<?= e($user['phone']) ?>" required>
                        </div>
                    </div>

                    <div class="d-flex justify-content-between align-items-center">
                        <a href="/jobs.php" class="btn btn-outline-secondary btn-sm">Cancel</a>
                        <button type="submit" class="btn btn-primary px-4 fw-bold shadow-sm">
                            <i class="bi bi-send-check me-1"></i> Publish Job Post
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
