<?php
/**
 * Sargodha Mandi - User Dashboard
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user = currentUser();
$db = getDB();

// Statistics
$totalAds = (int)$db->prepare("SELECT COUNT(*) FROM listings WHERE user_id = :uid")->execute([':uid' => $user['id']]) ? $db->query("SELECT COUNT(*) FROM listings WHERE user_id = {$user['id']}")->fetchColumn() : 0;
$activeAds = (int)$db->query("SELECT COUNT(*) FROM listings WHERE user_id = {$user['id']} AND status = 'Published'")->fetchColumn();
$pendingAds = (int)$db->query("SELECT COUNT(*) FROM listings WHERE user_id = {$user['id']} AND status = 'Payment Pending'")->fetchColumn();
$rejectedAds = (int)$db->query("SELECT COUNT(*) FROM listings WHERE user_id = {$user['id']} AND status IN ('Rejected', 'Payment Rejected')")->fetchColumn();
$totalJobs = (int)$db->query("SELECT COUNT(*) FROM jobs WHERE user_id = {$user['id']}")->fetchColumn();
$favCount = (int)$db->query("SELECT COUNT(*) FROM favorites WHERE user_id = {$user['id']}")->fetchColumn();
$msgCount = getUnreadMessageCount($user['id']);
$notifCount = getUnreadNotificationCount($user['id']);

// Recent 5 listings
$stmt = $db->prepare("
    SELECT l.*, c.name as category_name,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id ORDER BY is_primary DESC, id ASC LIMIT 1) as primary_image
    FROM listings l
    JOIN categories c ON l.category_id = c.id
    WHERE l.user_id = :uid
    ORDER BY l.id DESC
    LIMIT 5
");
$stmt->execute([':uid' => $user['id']]);
$recentAds = $stmt->fetchAll();

$pageTitle = 'Dashboard - ' . SITE_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <!-- Welcome Header -->
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
        <div>
            <h3 class="fw-bold mb-1">Welcome, <?= e($user['full_name']) ?>!</h3>
            <p class="text-muted small mb-0">
                <i class="bi bi-geo-alt-fill text-success me-1"></i> <?= e($user['area']) ?>, <strong><?= e($user['city']) ?></strong> · Member since <?= date('M Y', strtotime($user['created_at'])) ?>
            </p>
        </div>
        <div class="d-flex flex-wrap gap-2">
            <a href="/post-ad.php" class="btn btn-primary-green btn-sm px-3 py-2 fw-semibold">
                <i class="bi bi-plus-circle me-1"></i> Post Ad
            </a>
            <a href="/post-job.php" class="btn btn-primary btn-sm px-3 py-2 fw-semibold">
                <i class="bi bi-briefcase me-1"></i> Post Job
            </a>
            <a href="/user/profile.php" class="btn btn-outline-secondary btn-sm px-3 py-2">
                <i class="bi bi-person-gear"></i> Settings
            </a>
        </div>
    </div>

    <!-- Quick Stats Metric Grid -->
    <div class="row row-cols-2 row-cols-md-5 g-3 mb-4">
        <div class="col">
            <div class="card border shadow-sm p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">My Products</div>
                        <div class="fs-4 fw-bold tabular-nums"><?= $totalAds ?></div>
                    </div>
                    <span class="fs-3 text-primary"><i class="bi bi-card-list"></i></span>
                </div>
                <a href="/user/my-listings.php" class="small text-primary text-decoration-none mt-2 d-inline-block">Manage ads &rarr;</a>
            </div>
        </div>

        <div class="col">
            <div class="card border shadow-sm p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">My Jobs</div>
                        <div class="fs-4 fw-bold text-info tabular-nums"><?= $totalJobs ?></div>
                    </div>
                    <span class="fs-3 text-info"><i class="bi bi-briefcase"></i></span>
                </div>
                <a href="/user/my-jobs.php" class="small text-info text-decoration-none mt-2 d-inline-block">Manage jobs &rarr;</a>
            </div>
        </div>

        <div class="col">
            <div class="card border shadow-sm p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Published Ads</div>
                        <div class="fs-4 fw-bold text-success tabular-nums"><?= $activeAds ?></div>
                    </div>
                    <span class="fs-3 text-success"><i class="bi bi-check2-circle"></i></span>
                </div>
                <a href="/user/my-listings.php?status=Published" class="small text-success text-decoration-none mt-2 d-inline-block">Live on site &rarr;</a>
            </div>
        </div>

        <div class="col">
            <div class="card border shadow-sm p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Verification</div>
                        <div class="fs-5 fw-bold text-<?= $user['activation_status'] === 'approved' ? 'success' : ($user['activation_status'] === 'pending' ? 'warning' : 'secondary') ?>">
                            <?= ucfirst($user['activation_status'] ?: 'Standard') ?>
                        </div>
                    </div>
                    <span class="fs-3 text-<?= $user['activation_status'] === 'approved' ? 'success' : 'warning' ?>"><i class="bi bi-shield-check"></i></span>
                </div>
                <a href="/activate-seller.php" class="small text-decoration-none mt-2 d-inline-block">View status &rarr;</a>
            </div>
        </div>

        <div class="col">
            <div class="card border shadow-sm p-3 bg-white h-100">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Messages</div>
                        <div class="fs-4 fw-bold text-secondary tabular-nums"><?= $msgCount ?></div>
                    </div>
                    <span class="fs-3 text-secondary"><i class="bi bi-chat-dots"></i></span>
                </div>
                <a href="/user/messages.php" class="small text-secondary text-decoration-none mt-2 d-inline-block">Open chats &rarr;</a>
            </div>
        </div>
    </div>
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <div class="text-muted small">Unread Messages</div>
                        <div class="fs-4 fw-bold text-info tabular-nums"><?= $msgCount ?></div>
                    </div>
                    <span class="fs-3 text-info"><i class="bi bi-chat-dots"></i></span>
                </div>
                <a href="/user/messages.php" class="small text-info text-decoration-none mt-2 d-inline-block">Open chats &rarr;</a>
            </div>
        </div>
    </div>

    <!-- Main Dashboard Section -->
    <div class="row g-4">
        <!-- Recent Ads Table -->
        <div class="col-12 col-lg-8">
            <div class="card border shadow-sm p-4 bg-white rounded-3">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">My Recent Ads</h5>
                    <a href="/user/my-listings.php" class="small text-success fw-semibold text-decoration-none">View All Ads &rarr;</a>
                </div>

                <?php if (empty($recentAds)): ?>
                    <div class="text-center py-5 text-muted">
                        <i class="bi bi-plus-square fs-1 text-muted mb-2"></i>
                        <p class="mb-2">You haven't posted any product ads yet.</p>
                        <a href="/post-ad.php" class="btn btn-primary-green btn-sm">Post an Ad Now (Rs. 500)</a>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle small mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Item</th>
                                    <th>Price</th>
                                    <th>Status</th>
                                    <th>Posted</th>
                                    <th class="text-end">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentAds as $ad): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center gap-2">
                                                <img src="/<?= e($ad['primary_image'] ?: 'assets/images/placeholder.jpg') ?>" 
                                                     alt="Ad thumb" class="rounded" style="width: 44px; height: 44px; object-fit: cover;"
                                                     onerror="this.src='/uploads/products/listing_smartphone_1790803033699.jpg'">
                                                <div class="text-truncate" style="max-width: 220px;">
                                                    <a href="/product.php?id=<?= $ad['id'] ?>" class="text-dark fw-semibold text-decoration-none d-block text-truncate">
                                                        <?= e($ad['title']) ?>
                                                    </a>
                                                    <small class="text-muted"><?= e($ad['category_name']) ?> · <?= e($ad['city']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="fw-bold tabular-nums text-nowrap"><?= formatPKR($ad['price']) ?></td>
                                        <td>
                                            <?php if ($ad['status'] === 'Published'): ?>
                                                <span class="badge bg-success-subtle text-success">Published</span>
                                            <?php elseif ($ad['status'] === 'Payment Pending'): ?>
                                                <span class="badge bg-warning-subtle text-warning">Payment Pending</span>
                                            <?php else: ?>
                                                <span class="badge bg-danger-subtle text-danger"><?= e($ad['status']) ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="text-muted text-nowrap"><?= timeAgo($ad['created_at']) ?></td>
                                        <td class="text-end text-nowrap">
                                            <?php if ($ad['status'] === 'Payment Pending'): ?>
                                                <a href="/user/submit-payment.php?listing_id=<?= $ad['id'] ?>" class="btn btn-warning btn-sm py-0 px-2" title="Submit Fee">
                                                    Pay Fee
                                                </a>
                                            <?php else: ?>
                                                <a href="/product.php?id=<?= $ad['id'] ?>" class="btn btn-outline-secondary btn-sm py-0 px-2">
                                                    View
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Quick Links & Account Status -->
        <div class="col-12 col-lg-4">
            <div class="card border shadow-sm p-4 bg-white rounded-3 mb-4">
                <h6 class="fw-bold mb-3">Quick Navigation</h6>
                <div class="list-group list-group-flush small">
                    <a href="/user/my-listings.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-card-list me-2 text-primary"></i> Manage All Ads</span>
                        <span class="badge bg-light text-dark"><?= $totalAds ?></span>
                    </a>
                    <a href="/user/payments.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-receipt me-2 text-success"></i> My Payments & Receipts</span>
                        <span class="badge bg-warning text-dark"><?= $pendingAds ?> pending</span>
                    </a>
                    <a href="/user/messages.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-chat-dots me-2 text-info"></i> In-App Chat Messages</span>
                        <?php if ($msgCount > 0): ?><span class="badge bg-info text-white"><?= $msgCount ?></span><?php endif; ?>
                    </a>
                    <a href="/user/favorites.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-heart me-2 text-danger"></i> Saved Favorite Ads</span>
                        <span class="badge bg-light text-dark"><?= $favCount ?></span>
                    </a>
                    <a href="/user/notifications.php" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center px-0">
                        <span><i class="bi bi-bell me-2 text-secondary"></i> Notifications</span>
                        <?php if ($notifCount > 0): ?><span class="badge bg-danger rounded-pill"><?= $notifCount ?></span><?php endif; ?>
                    </a>
                </div>
            </div>

            <!-- Payment Helpline reminder -->
            <div class="p-3 bg-light rounded border small">
                <div class="fw-bold text-dark mb-1"><i class="bi bi-info-circle text-primary me-1"></i> Listing Fee Help</div>
                <p class="text-muted mb-2">Need help with your EasyPaisa / JazzCash Rs. 500 payment? Contact the verified administrator:</p>
                <div class="fw-bold text-dark">Muhammad Akram Tayyab</div>
                <div class="text-success fw-bold"><i class="bi bi-whatsapp"></i> 03127453108</div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
