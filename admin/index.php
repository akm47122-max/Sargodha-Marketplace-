<?php
/**
 * Sargodha Mandi - Administrator Dashboard
 */

$adminTitle = 'Admin Dashboard - Sargodha Mandi';
require_once __DIR__ . '/includes/admin_header.php';

// Fetch Overall Statistics
$totalUsers = (int)$db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$totalListings = (int)$db->query("SELECT COUNT(*) FROM listings")->fetchColumn();
$activeListings = (int)$db->query("SELECT COUNT(*) FROM listings WHERE status = 'Published'")->fetchColumn();
$pendingListings = (int)$db->query("SELECT COUNT(*) FROM listings WHERE status = 'Payment Pending'")->fetchColumn();
$featuredListings = (int)$db->query("SELECT COUNT(*) FROM listings WHERE is_featured = 1")->fetchColumn();

$pendingPayments = (int)$db->query("SELECT COUNT(*) FROM listing_payments WHERE status = 'pending'")->fetchColumn();
$approvedPayments = (int)$db->query("SELECT COUNT(*) FROM listing_payments WHERE status = 'approved'")->fetchColumn();
$totalRevenue = (float)$db->query("SELECT COALESCE(SUM(amount), 0) FROM listing_payments WHERE status = 'approved'")->fetchColumn();

$reportedListings = (int)$db->query("SELECT COUNT(*) FROM reports WHERE status = 'pending'")->fetchColumn();

// Recent 5 Pending Payments
$pStmt = $db->query("
    SELECT p.*, l.title as listing_title, u.full_name as user_name, u.city as user_city
    FROM listing_payments p
    JOIN listings l ON p.listing_id = l.id
    JOIN users u ON p.user_id = u.id
    ORDER BY (p.status = 'pending') DESC, p.id DESC
    LIMIT 6
");
$recentPayments = $pStmt->fetchAll();

// Recent 5 Reported Items
$rStmt = $db->query("
    SELECT r.*, l.title as listing_title
    FROM reports r
    JOIN listings l ON r.listing_id = l.id
    WHERE r.status = 'pending'
    ORDER BY r.id DESC
    LIMIT 5
");
$recentReports = $rStmt->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-0">Marketplace Administration</h3>
        <p class="text-muted small mb-0">Overview for Sargodha, Shaheenabad & Sillanwali Operations</p>
    </div>
    <div class="d-flex gap-2">
        <a href="/admin/payments.php" class="btn btn-warning btn-sm fw-bold">
            <i class="bi bi-cash-coin me-1"></i> Verify Payments (<?= $pendingPayments ?>)
        </a>
        <a href="/admin/settings.php" class="btn btn-dark btn-sm">
            <i class="bi bi-gear me-1"></i> Settings
        </a>
    </div>
</div>

<!-- Stats Counter Grid -->
<div class="row row-cols-2 row-cols-md-4 g-3 mb-4">
    <!-- Revenue -->
    <div class="col">
        <div class="card border-0 shadow-sm p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Verified Revenue</div>
                    <div class="fs-4 fw-bold text-success tabular-nums"><?= formatPKR($totalRevenue) ?></div>
                </div>
                <div class="bg-success bg-opacity-10 text-success p-2 rounded"><i class="bi bi-wallet2 fs-4"></i></div>
            </div>
            <small class="text-muted mt-2 d-block"><?= $approvedPayments ?> approved payments</small>
        </div>
    </div>

    <!-- Pending Payments Alert -->
    <div class="col">
        <div class="card border-0 shadow-sm p-3 bg-white border-start border-warning border-4">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Pending Payments</div>
                    <div class="fs-4 fw-bold text-warning tabular-nums"><?= $pendingPayments ?></div>
                </div>
                <div class="bg-warning bg-opacity-10 text-warning p-2 rounded"><i class="bi bi-hourglass-split fs-4"></i></div>
            </div>
            <a href="/admin/payments.php" class="small text-warning fw-semibold text-decoration-none mt-2 d-block">Approve now &rarr;</a>
        </div>
    </div>

    <!-- Active Listings -->
    <div class="col">
        <div class="card border-0 shadow-sm p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Published Ads</div>
                    <div class="fs-4 fw-bold text-primary tabular-nums"><?= $activeListings ?></div>
                </div>
                <div class="bg-primary bg-opacity-10 text-primary p-2 rounded"><i class="bi bi-check2-circle fs-4"></i></div>
            </div>
            <small class="text-muted mt-2 d-block">Total ads: <?= $totalListings ?> (<?= $pendingListings ?> pending)</small>
        </div>
    </div>

    <!-- Users -->
    <div class="col">
        <div class="card border-0 shadow-sm p-3 bg-white">
            <div class="d-flex align-items-center justify-content-between">
                <div>
                    <div class="text-muted small">Registered Users</div>
                    <div class="fs-4 fw-bold text-dark tabular-nums"><?= $totalUsers ?></div>
                </div>
                <div class="bg-dark bg-opacity-10 text-dark p-2 rounded"><i class="bi bi-people fs-4"></i></div>
            </div>
            <a href="/admin/users.php" class="small text-dark text-decoration-none mt-2 d-block">Manage users &rarr;</a>
        </div>
    </div>
</div>

<div class="row g-4">
    <!-- Recent Payment Submissions (Crucial for fee verification flow) -->
    <div class="col-12 col-lg-8">
        <div class="card border-0 shadow-sm bg-white p-4 rounded-3">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="fw-bold mb-0">Recent Listing Fee Submissions (Rs. 500)</h5>
                <a href="/admin/payments.php" class="text-success small fw-semibold text-decoration-none">View All Payments &rarr;</a>
            </div>

            <?php if (empty($recentPayments)): ?>
                <div class="text-center py-4 text-muted small">No payment submissions yet.</div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle small mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Ad & User</th>
                                <th>Method</th>
                                <th>TRX ID</th>
                                <th>Status</th>
                                <th>Screenshot</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recentPayments as $p): ?>
                                <tr>
                                    <td>
                                        <a href="/product.php?id=<?= $p['listing_id'] ?>" target="_blank" class="fw-semibold text-dark text-decoration-none d-block text-truncate" style="max-width: 200px;">
                                            <?= e($p['listing_title']) ?>
                                        </a>
                                        <small class="text-muted"><?= e($p['user_name']) ?> (<?= e($p['user_city']) ?>)</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border"><?= e($p['payment_method']) ?></span>
                                    </td>
                                    <td>
                                        <code class="text-dark bg-light px-1 rounded"><?= e($p['transaction_id']) ?></code>
                                    </td>
                                    <td>
                                        <?php if ($p['status'] === 'approved'): ?>
                                            <span class="badge bg-success-subtle text-success">Approved</span>
                                        <?php elseif ($p['status'] === 'rejected'): ?>
                                            <span class="badge bg-danger-subtle text-danger">Rejected</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning">Pending</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($p['payment_screenshot']): ?>
                                            <a href="/<?= e($p['payment_screenshot']) ?>" target="_blank">
                                                <img src="/<?= e($p['payment_screenshot']) ?>" alt="Receipt" class="rounded border" style="width: 32px; height: 32px; object-fit: cover;">
                                            </a>
                                        <?php else: ?>
                                            <span class="text-muted">-</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-end">
                                        <a href="/admin/payments.php?id=<?= $p['id'] ?>" class="btn btn-outline-primary btn-sm py-0 px-2">
                                            Review
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Right: Pending Reports & Site Config Status -->
    <div class="col-12 col-lg-4">
        <!-- Reports -->
        <div class="card border-0 shadow-sm bg-white p-4 rounded-3 mb-4">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0">Flagged Reports (<?= $reportedListings ?>)</h6>
                <a href="/admin/reports.php" class="text-danger small fw-semibold text-decoration-none">Manage &rarr;</a>
            </div>

            <?php if (empty($recentReports)): ?>
                <div class="text-muted small text-center py-3">No pending reports! System is clean.</div>
            <?php else: ?>
                <div class="list-group list-group-flush small">
                    <?php foreach ($recentReports as $rep): ?>
                        <div class="list-group-item px-0">
                            <div class="d-flex justify-content-between align-items-start mb-1">
                                <span class="badge bg-danger"><?= e($rep['reason']) ?></span>
                                <small class="text-muted"><?= timeAgo($rep['created_at']) ?></small>
                            </div>
                            <div class="fw-semibold text-truncate mb-1"><?= e($rep['listing_title']) ?></div>
                            <p class="text-muted small mb-0"><?= e($rep['details']) ?></p>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Official Fee Notice Box -->
        <div class="p-3 bg-light rounded border small">
            <h6 class="fw-bold text-dark mb-2"><i class="bi bi-shield-check text-success me-1"></i> Current Fee Configuration</h6>
            <div class="d-flex justify-content-between mb-1">
                <span>Standard Listing Fee:</span>
                <strong>Rs. <?= getSetting('listing_fee', '500') ?> PKR</strong>
            </div>
            <div class="d-flex justify-content-between mb-1">
                <span>Featured Ad Fee:</span>
                <strong>Rs. <?= getSetting('featured_fee', '1000') ?> PKR</strong>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Account Number:</span>
                <strong class="text-success"><?= getSetting('payment_number', '03127453108') ?></strong>
            </div>
            <div class="text-end">
                <a href="/admin/settings.php" class="btn btn-outline-dark btn-sm py-0 px-2">Edit Settings</a>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
