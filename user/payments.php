<?php
/**
 * Sargodha Mandi - User Payments History
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user = currentUser();
$db = getDB();

$stmt = $db->prepare("
    SELECT p.*, l.title as listing_title, l.status as listing_status
    FROM listing_payments p
    JOIN listings l ON p.listing_id = l.id
    WHERE p.user_id = :uid
    ORDER BY p.id DESC
");
$stmt->execute([':uid' => $user['id']]);
$payments = $stmt->fetchAll();

$pageTitle = 'My Payments - Sargodha Mandi';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-4">
        <div>
            <h3 class="fw-bold mb-1">My Listing Fee Payments</h3>
            <p class="text-muted small mb-0">Track verification of your Rs. 500 listing fee submissions</p>
        </div>
        <div class="p-2 px-3 bg-light rounded border small">
            <span class="text-muted">Official Receiver:</span>
            <strong>Muhammad Akram Tayyab (03127453108)</strong>
        </div>
    </div>

    <?php if (empty($payments)): ?>
        <div class="card border p-5 text-center bg-white shadow-sm">
            <i class="bi bi-receipt fs-1 text-muted mb-2"></i>
            <h5 class="fw-bold">No payment submissions found</h5>
            <p class="text-muted small">When you create a product listing and submit the Rs. 500 fee details, your records will appear here.</p>
            <div class="mt-2">
                <a href="/post-ad.php" class="btn btn-primary-green btn-sm">Post an Ad</a>
            </div>
        </div>
    <?php else: ?>
        <div class="card border shadow-sm bg-white">
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ID</th>
                            <th>Associated Ad</th>
                            <th>Amount</th>
                            <th>Channel & Sender</th>
                            <th>Transaction ID</th>
                            <th>Receipt</th>
                            <th>Status</th>
                            <th>Date</th>
                            <th>Admin Remarks</th>
                            <th class="text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payments as $p): ?>
                            <tr>
                                <td class="text-muted">#<?= $p['id'] ?></td>
                                <td>
                                    <a href="/product.php?id=<?= $p['listing_id'] ?>" class="text-dark fw-semibold text-decoration-none">
                                        <?= e($p['listing_title']) ?>
                                    </a>
                                </td>
                                <td class="fw-bold text-success tabular-nums"><?= formatPKR($p['amount']) ?></td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= e($p['payment_method']) ?></span><br>
                                    <small class="text-muted"><?= e($p['sender_number']) ?></small>
                                </td>
                                <td>
                                    <code class="text-dark bg-light px-2 py-1 rounded"><?= e($p['transaction_id']) ?></code>
                                </td>
                                <td>
                                    <?php if ($p['payment_screenshot']): ?>
                                        <a href="/<?= e($p['payment_screenshot']) ?>" target="_blank">
                                            <img src="/<?= e($p['payment_screenshot']) ?>" alt="Receipt" class="rounded border" style="width: 36px; height: 36px; object-fit: cover;">
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">None</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($p['status'] === 'approved'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-check-circle me-1"></i> Approved
                                        </span>
                                    <?php elseif ($p['status'] === 'rejected'): ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle">
                                            <i class="bi bi-x-circle me-1"></i> Rejected
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-warning-subtle text-warning border border-warning-subtle">
                                            <i class="bi bi-hourglass-split me-1"></i> Pending Verification
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted text-nowrap"><?= date('d M Y, h:i A', strtotime($p['created_at'])) ?></td>
                                <td>
                                    <?php if ($p['admin_note']): ?>
                                        <span class="text-secondary"><?= e($p['admin_note']) ?></span>
                                    <?php else: ?>
                                        <span class="text-muted italic">Awaiting review</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-end text-nowrap">
                                    <?php if ($p['status'] === 'rejected'): ?>
                                        <a href="/user/submit-payment.php?listing_id=<?= $p['listing_id'] ?>" class="btn btn-warning btn-sm py-0 px-2 fw-semibold">
                                            Re-submit Details
                                        </a>
                                    <?php elseif ($p['status'] === 'approved'): ?>
                                        <span class="text-success small fw-semibold"><i class="bi bi-check2"></i> Published</span>
                                    <?php else: ?>
                                        <span class="text-muted small">In Review</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
