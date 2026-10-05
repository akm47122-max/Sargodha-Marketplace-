<?php
/**
 * SARGODHAMART - Admin Activation Payments Review
 * Review one-time Rs. 500 seller account activation fees
 */

$adminTitle = 'Seller Activation Payments - Admin Panel';
require_once __DIR__ . '/includes/admin_header.php';

// Handle Actions (Approve / Reject)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();
    $action = $_POST['action'] ?? '';
    $paymentId = (int)($_POST['payment_id'] ?? 0);
    $adminNote = trim($_POST['admin_note'] ?? '');

    if ($paymentId > 0) {
        $stmt = $db->prepare("SELECT p.*, u.full_name as user_name, u.email as user_email FROM activation_payments p JOIN users u ON p.user_id = u.id WHERE p.id = :id LIMIT 1");
        $stmt->execute([':id' => $paymentId]);
        $payment = $stmt->fetch();

        if ($payment) {
            if ($action === 'approve') {
                // 1. Mark activation payment approved
                $up = $db->prepare("
                    UPDATE activation_payments 
                    SET status = 'approved', admin_note = :note, verified_at = NOW(), verified_by = :aid 
                    WHERE id = :id
                ");
                $up->execute([':note' => $adminNote ?: 'Verified in EasyPaisa/JazzCash account.', ':aid' => $admin['id'], ':id' => $paymentId]);

                // 2. Activate user seller privileges (unlimited free listings)
                $db->prepare("UPDATE users SET activation_status = 'active', activated_at = NOW() WHERE id = :uid")
                   ->execute([':uid' => $payment['user_id']]);

                // 3. User notification
                createNotification(
                    $payment['user_id'],
                    'Seller Privileges Activated!',
                    "Your one-time Rs. 1,000 activation fee has been approved. You can now post unlimited normal listings for free!",
                    "/post-ad.php",
                    'account_activation'
                );

                // 4. Send Approval Email via Resend
                if (class_exists('ResendMailer') && !empty($payment['user_email'])) {
                    ResendMailer::sendActivationApprovedEmail($payment['user_email'], $payment['user_name'] ?? 'Seller');
                }

                if (file_exists(__DIR__ . '/../services/AI/AIEventLogger.php')) {
                    require_once __DIR__ . '/../services/AI/AIEventLogger.php';
                    AIEventLogger::logEvent('ACTIVATION_APPROVED', 'payment', $paymentId, (int)$payment['user_id'], [
                        'admin_id' => $admin['id']
                    ]);
                }

                logAdminAction($admin['id'], 'approve_activation', 'activation_payments', $paymentId, "Activated seller account #{$payment['user_id']}");
                redirect('/admin/payments.php', 'Seller activated successfully! User can now post unlimited listings.', 'success');

            } elseif ($action === 'reject') {
                if (empty($adminNote)) {
                    setFlash('Please enter a rejection reason for the seller.', 'danger');
                } else {
                    $up = $db->prepare("
                        UPDATE activation_payments 
                        SET status = 'rejected', admin_note = :note, verified_at = NOW(), verified_by = :aid 
                        WHERE id = :id
                    ");
                    $up->execute([':note' => $adminNote, ':aid' => $admin['id'], ':id' => $paymentId]);

                    $db->prepare("UPDATE users SET activation_status = 'rejected' WHERE id = :uid")
                       ->execute([':uid' => $payment['user_id']]);

                    createNotification(
                        $payment['user_id'],
                        'Activation Payment Rejected',
                        "Your seller activation payment was rejected: {$adminNote}. Please submit corrected details.",
                        "/activate-seller.php",
                        'payment_rejected'
                    );

                    // Send Rejection Email via Resend
                    if (class_exists('ResendMailer') && !empty($payment['user_email'])) {
                        ResendMailer::sendActivationRejectedEmail($payment['user_email'], $payment['user_name'] ?? 'Seller', $adminNote);
                    }

                    if (file_exists(__DIR__ . '/../services/AI/AIEventLogger.php')) {
                        require_once __DIR__ . '/../services/AI/AIEventLogger.php';
                        AIEventLogger::logEvent('ACTIVATION_REJECTED', 'payment', $paymentId, (int)$payment['user_id'], [
                            'admin_id' => $admin['id'],
                            'reason' => $adminNote
                        ]);
                    }

                    logAdminAction($admin['id'], 'reject_activation', 'activation_payments', $paymentId, $adminNote);
                    redirect('/admin/payments.php', 'Activation payment rejected and user notified.', 'warning');
                }
            }
        }
    }
}

// Fetch all activation payments
$payments = $db->query("
    SELECT p.*, u.full_name as user_name, u.mobile_number as user_phone, u.city as user_city, u.activation_status as user_activation
    FROM activation_payments p
    JOIN users u ON p.user_id = u.id
    ORDER BY (p.status = 'pending') DESC, p.id DESC
")->fetchAll();
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="fw-bold mb-1">One-Time Seller Activation Payments</h3>
        <p class="text-muted small mb-0">Verify Rs. 500 payments to unlock unlimited free product listings for local sellers</p>
    </div>
    <div class="p-2 px-3 bg-white rounded border small">
        <span class="text-muted">Account Receiver:</span>
        <strong>Muhammad Akram Tayyab (03127453108)</strong>
    </div>
</div>

<div class="card border-0 shadow-sm bg-white overflow-hidden">
    <div class="table-responsive">
        <table class="table table-hover align-middle small mb-0">
            <thead class="table-light">
                <tr>
                    <th>ID</th>
                    <th>Seller</th>
                    <th>Town</th>
                    <th>Fee Amount</th>
                    <th>Method & Sender</th>
                    <th>Transaction ID (TRX ID)</th>
                    <th>Screenshot</th>
                    <th>Status</th>
                    <th>Date</th>
                    <th class="text-end">Verification Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($payments)): ?>
                    <tr><td colspan="10" class="text-center py-4 text-muted">No activation payments submitted yet.</td></tr>
                <?php else: ?>
                    <?php foreach ($payments as $p): ?>
                        <tr class="<?= $p['status'] === 'pending' ? 'table-warning bg-opacity-25' : '' ?>">
                            <td class="text-muted">#<?= $p['id'] ?></td>
                            <td>
                                <strong><?= e($p['user_name']) ?></strong><br>
                                <small class="text-muted"><?= e($p['user_phone']) ?></small>
                            </td>
                            <td><?= e($p['user_city']) ?></td>
                            <td class="fw-bold text-success tabular-nums"><?= formatPKR($p['amount']) ?></td>
                            <td>
                                <span class="badge bg-light text-dark border"><?= e($p['payment_method']) ?></span><br>
                                <small class="text-muted"><?= e($p['sender_number']) ?></small>
                            </td>
                            <td>
                                <code class="text-dark bg-light px-2 py-1 rounded fw-bold fs-6"><?= e($p['transaction_id']) ?></code>
                            </td>
                            <td>
                                <?php if ($p['payment_screenshot']): ?>
                                    <a href="/<?= e($p['payment_screenshot']) ?>" target="_blank">
                                        <img src="/<?= e($p['payment_screenshot']) ?>" alt="Receipt" class="rounded border" style="width: 40px; height: 40px; object-fit: cover;">
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">-</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($p['status'] === 'approved'): ?>
                                    <span class="badge bg-success-subtle text-success">Approved (Active)</span>
                                <?php elseif ($p['status'] === 'rejected'): ?>
                                    <span class="badge bg-danger-subtle text-danger">Rejected</span>
                                <?php else: ?>
                                    <span class="badge bg-warning-subtle text-warning">Pending Review</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-muted text-nowrap"><?= timeAgo($p['created_at']) ?></td>
                            <td class="text-end text-nowrap">
                                <?php if ($p['status'] === 'pending'): ?>
                                    <form method="POST" action="/admin/payments.php" class="d-inline" onsubmit="return confirm('Confirm Rs. <?= $p['amount'] ?> received in EasyPaisa/JazzCash account? This activates the seller for unlimited free listings.');">
                                        <?= getCsrfField() ?>
                                        <input type="hidden" name="action" value="approve">
                                        <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                        <button type="submit" class="btn btn-success btn-sm py-0 px-2 fw-semibold">
                                            <i class="bi bi-check-lg"></i> Approve & Activate
                                        </button>
                                    </form>

                                    <button type="button" class="btn btn-outline-danger btn-sm py-0 px-2 ms-1" data-bs-toggle="modal" data-bs-target="#rejectModal<?= $p['id'] ?>">
                                        <i class="bi bi-x-lg"></i> Reject
                                    </button>

                                    <div class="modal fade" id="rejectModal<?= $p['id'] ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content text-start">
                                                <div class="modal-header">
                                                    <h6 class="modal-title fw-bold text-danger">Reject Activation Payment #<?= $p['id'] ?></h6>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="/admin/payments.php">
                                                    <?= getCsrfField() ?>
                                                    <input type="hidden" name="action" value="reject">
                                                    <input type="hidden" name="payment_id" value="<?= $p['id'] ?>">
                                                    <div class="modal-body">
                                                        <label class="form-label small fw-bold">Rejection Reason *</label>
                                                        <textarea name="admin_note" class="form-control form-control-sm" rows="3" placeholder="e.g. Transaction ID not found on EasyPaisa 03127453108." required></textarea>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger btn-sm">Confirm Rejection</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php else: ?>
                                    <span class="text-muted small"><?= e($p['admin_note'] ?: 'Processed') ?></span>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
