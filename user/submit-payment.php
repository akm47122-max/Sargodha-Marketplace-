<?php
/**
 * Sargodha Mandi - Listing Fee Payment Submission
 * Rs. 500 Listing Fee Step
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user = currentUser();
$db = getDB();

$listingId = (int)($_GET['listing_id'] ?? 0);
if ($listingId <= 0) {
    redirect('/user/my-listings.php', 'Invalid listing ID.', 'danger');
}

// Fetch listing & verify ownership
$stmt = $db->prepare("SELECT * FROM listings WHERE id = :id AND user_id = :uid LIMIT 1");
$stmt->execute([':id' => $listingId, ':uid' => $user['id']]);
$listing = $stmt->fetch();

if (!$listing) {
    redirect('/user/my-listings.php', 'Listing not found or access denied.', 'danger');
}

// Fetch latest payment record for this listing if any
$pStmt = $db->prepare("SELECT * FROM listing_payments WHERE listing_id = :lid ORDER BY id DESC LIMIT 1");
$pStmt->execute([':lid' => $listingId]);
$lastPayment = $pStmt->fetch();

// Payment settings
$listingFee = (float)getSetting('listing_fee', 500);
$paymentNumber = getSetting('payment_number', '03127453108');
$accountName = getSetting('payment_account_name', 'Muhammad Akram Tayyab');
$bankName = getSetting('payment_bank_name', 'EasyPaisa / JazzCash');
$instructions = getSetting('payment_instructions', 'Please transfer Rs. 500 to 03127453108 (Muhammad Akram Tayyab).');

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();

    $paymentMethod = trim($_POST['payment_method'] ?? 'EasyPaisa');
    $senderNumber = trim($_POST['sender_number'] ?? '');
    $transactionId = trim($_POST['transaction_id'] ?? '');

    if (empty($transactionId)) {
        $errors[] = 'Please enter your Transaction ID (TRX ID).';
    }

    if (empty($senderNumber)) {
        $errors[] = 'Please enter the sender mobile number or account title.';
    }

    // Screenshot validation
    $screenshotPath = null;
    if (!empty($_FILES['screenshot']['name'])) {
        $uploadResult = validateAndSaveImage($_FILES['screenshot'], 'payments');
        if (!$uploadResult['success']) {
            $errors[] = 'Screenshot: ' . $uploadResult['error'];
        } else {
            $screenshotPath = $uploadResult['path'];
        }
    } else {
        // If retrying, can reuse previous screenshot or require new one
        if (!$lastPayment || empty($lastPayment['payment_screenshot'])) {
            $errors[] = 'Please upload a screenshot or photo of your payment receipt.';
        } else {
            $screenshotPath = $lastPayment['payment_screenshot'];
        }
    }

    if (empty($errors)) {
        // Insert new payment record
        $ins = $db->prepare("
            INSERT INTO listing_payments 
            (user_id, listing_id, payment_type, amount, payment_method, payment_number, account_name, sender_number, transaction_id, payment_screenshot, status)
            VALUES 
            (:uid, :lid, 'listing_fee', :amt, :method, :paynum, :accname, :sender, :trx, :scr, 'pending')
        ");
        $ins->execute([
            ':uid' => $user['id'],
            ':lid' => $listingId,
            ':amt' => $listingFee,
            ':method' => $paymentMethod,
            ':paynum' => $paymentNumber,
            ':accname' => $accountName,
            ':sender' => $senderNumber,
            ':trx' => $transactionId,
            ':scr' => $screenshotPath
        ]);

        // Update listing status back to 'Payment Pending'
        $db->prepare("UPDATE listings SET status = 'Payment Pending' WHERE id = :id")->execute([':id' => $listingId]);

        // Notify user
        createNotification(
            $user['id'], 
            'Payment Submitted for Verification', 
            "Your listing fee payment of Rs. {$listingFee} (Trx ID: {$transactionId}) has been received and is awaiting admin approval.",
            "/user/payments.php",
            'payment'
        );

        redirect('/user/payments.php', 'Payment submitted successfully! Your ad will be published as soon as an administrator verifies the transaction.', 'success');
    }
}

$pageTitle = 'Submit Listing Fee Payment - Sargodha Mandi';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7">
            <!-- Header Card -->
            <div class="card border shadow-sm p-4 bg-white rounded-3 mb-4">
                <div class="d-flex align-items-center justify-content-between mb-3 border-bottom pb-3">
                    <div>
                        <span class="badge bg-warning text-dark mb-1">Step 2: Payment Verification</span>
                        <h4 class="fw-bold mb-0">Product Listing Fee: Rs. <?= number_format($listingFee, 0) ?></h4>
                    </div>
                    <span class="fs-1 text-success"><i class="bi bi-receipt"></i></span>
                </div>

                <div class="mb-4">
                    <p class="small text-muted mb-2">You are publishing the ad:</p>
                    <div class="p-3 bg-light rounded border d-flex align-items-center justify-content-between">
                        <div>
                            <strong class="text-dark"><?= e($listing['title']) ?></strong>
                            <div class="small text-muted"><?= e($listing['city']) ?> · Price: <?= formatPKR($listing['price']) ?></div>
                        </div>
                        <span class="badge bg-secondary"><?= e($listing['status']) ?></span>
                    </div>
                </div>

                <!-- If previous payment was rejected -->
                <?php if ($lastPayment && $lastPayment['status'] === 'rejected'): ?>
                    <div class="alert alert-danger shadow-sm mb-4">
                        <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Previous Payment Rejected</h6>
                        <p class="small mb-1"><strong>Admin Reason:</strong> <?= e($lastPayment['admin_note'] ?: 'Payment could not be verified with given transaction ID.') ?></p>
                        <small>Please check your EasyPaisa / JazzCash receipt and enter the corrected Transaction ID and screenshot below.</small>
                    </div>
                <?php endif; ?>

                <!-- Official Payment Account Details -->
                <div class="p-3 bg-success bg-opacity-10 border border-success rounded-3 mb-4">
                    <h6 class="fw-bold text-success mb-2"><i class="bi bi-wallet2 me-1"></i> Official Payment Details (Manual Transfer)</h6>
                    <div class="row g-2 small">
                        <div class="col-12 col-sm-6">
                            <span class="text-muted d-block">Account Title:</span>
                            <strong class="fs-6 text-dark"><?= e($accountName) ?></strong>
                        </div>
                        <div class="col-12 col-sm-6">
                            <span class="text-muted d-block">EasyPaisa / JazzCash Number:</span>
                            <div class="d-flex align-items-center gap-2">
                                <strong class="fs-5 text-success tabular-nums"><?= e($paymentNumber) ?></strong>
                                <button type="button" class="btn btn-outline-success btn-sm py-0 px-2 btn-copy-payment" data-copy="<?= e($paymentNumber) ?>">
                                    <i class="bi bi-clipboard"></i> Copy
                                </button>
                            </div>
                        </div>
                        <div class="col-12 mt-2 pt-2 border-top border-success-subtle">
                            <span class="text-muted">Required Amount:</span>
                            <strong class="text-dark">Exactly Rs. <?= number_format($listingFee, 0) ?> PKR</strong>
                        </div>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger small shadow-sm">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Payment Proof Submission Form -->
                <form method="POST" action="/user/submit-payment.php?listing_id=<?= $listingId ?>" enctype="multipart/form-data">
                    <?= getCsrfField() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Payment Method Used *</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="EasyPaisa">EasyPaisa</option>
                                <option value="JazzCash">JazzCash</option>
                                <option value="Bank Transfer">Online Bank Transfer</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Your Sender Mobile / Account *</label>
                            <input type="text" name="sender_number" class="form-control" placeholder="e.g. 03001234567" value="<?= e($user['mobile_number']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Transaction ID (TRX ID / TID) *</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="e.g. EP9823412095 or JC8472910382" required>
                        <small class="text-muted" style="font-size: 0.74rem;">Found on your SMS receipt from EasyPaisa / JazzCash</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Upload Payment Screenshot / Receipt *</label>
                        <input type="file" name="screenshot" class="form-control" accept="image/jpeg,image/png,image/webp" <?= ($lastPayment && $lastPayment['payment_screenshot']) ? '' : 'required' ?>>
                        <small class="text-muted" style="font-size: 0.74rem;">Attach screenshot of successful transfer (Max 5MB)</small>
                    </div>

                    <div class="d-flex justify-content-between align-items-center border-top pt-3">
                        <a href="/user/my-listings.php" class="btn btn-outline-secondary btn-sm">Pay Later / Back to Ads</a>
                        <button type="submit" class="btn btn-primary-green px-4 fw-bold">
                            Submit Payment Details &rarr;
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
