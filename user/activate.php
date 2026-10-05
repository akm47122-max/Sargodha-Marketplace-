<?php
/**
 * SARGODHAMART - User Portal: Seller Activation (One-Time Rs. 500)
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user = currentUser();
$db = getDB();

// If already active, inform user and redirect to post-ad
if ($user['activation_status'] === 'active') {
    redirect('/user/post-ad.php', 'Your seller account is already active! You can post unlimited free listings.', 'success');
}

// Fetch latest payment attempt
$stmt = $db->prepare("SELECT * FROM activation_payments WHERE user_id = :uid ORDER BY id DESC LIMIT 1");
$stmt->execute([':uid' => $user['id']]);
$lastPayment = $stmt->fetch();

$errors = [];
$activationFee = ONE_TIME_ACTIVATION_FEE;
$paymentNumber = OFFICIAL_PAYMENT_NUMBER;
$accountName = OFFICIAL_ACCOUNT_NAME;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();

    $paymentMethod = trim($_POST['payment_method'] ?? 'EasyPaisa');
    $senderNumber = trim($_POST['sender_number'] ?? '');
    $transactionId = trim($_POST['transaction_id'] ?? '');

    if (empty($senderNumber)) {
        $errors[] = 'Please provide your sender mobile number.';
    }
    if (empty($transactionId)) {
        $errors[] = 'Please enter your Transaction ID (TRX ID).';
    }

    $screenshotPath = $lastPayment['payment_screenshot'] ?? '';
    if (!empty($_FILES['screenshot']['name'])) {
        $uploadResult = validateImageUpload($_FILES['screenshot'], 'payments');
        if ($uploadResult['success']) {
            $screenshotPath = $uploadResult['path'];
        } else {
            $errors[] = $uploadResult['error'];
        }
    } elseif (empty($screenshotPath)) {
        $errors[] = 'Please upload a screenshot or photo of your payment receipt.';
    }

    if (empty($errors)) {
        // Record payment submission
        $stmt = $db->prepare("
            INSERT INTO activation_payments (user_id, amount, payment_method, sender_number, transaction_id, payment_screenshot, status, created_at)
            VALUES (:uid, :amount, :method, :sender, :trx, :screenshot, 'pending', NOW())
        ");
        $stmt->execute([
            ':uid' => $user['id'],
            ':amount' => $activationFee,
            ':method' => $paymentMethod,
            ':sender' => $senderNumber,
            ':trx' => $transactionId,
            ':screenshot' => $screenshotPath
        ]);

        // Update user activation_status to pending
        $db->prepare("UPDATE users SET activation_status = 'pending' WHERE id = :id")->execute([':id' => $user['id']]);

        // Create in-app notification
        createNotification(
            $user['id'],
            'Seller Activation Payment Submitted',
            "Your Rs. {$activationFee} payment proof (TRX: {$transactionId}) has been submitted. Admin will verify shortly.",
            '/user/dashboard.php',
            'activation'
        );

        redirect('/user/dashboard.php', 'Payment proof submitted! Once verified by admin, your seller account will be activated for unlimited free ads.', 'success');
    }
}

$pageTitle = 'Activate Seller Account - ' . SITE_NAME;
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="/index.php" class="text-decoration-none text-success">Home</a></li>
            <li class="breadcrumb-item"><a href="/user/dashboard.php" class="text-decoration-none text-success">User Portal</a></li>
            <li class="breadcrumb-item active" aria-current="page">Activate Seller</li>
        </ol>
    </nav>

    <div class="row justify-content-center">
        <div class="col-12 col-md-9 col-lg-7">
            <div class="card border rounded-3 p-4 p-md-5 bg-white shadow-sm">
                <div class="text-center mb-4">
                    <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-1 mb-2 fw-bold text-uppercase">
                        One-Time Seller Activation
                    </span>
                    <h2 class="fw-bold mb-1">Activate Your Seller Account</h2>
                    <p class="text-secondary small">
                        Pay <strong>Rs. <?= number_format($activationFee, 0) ?> ONE TIME ONLY</strong> to activate your seller privileges. After approval, you can create <strong>UNLIMITED normal product listings for free forever!</strong>
                    </p>
                </div>

                <!-- Fee Value Proposition Box -->
                <div class="p-3 bg-light rounded-3 border mb-4">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <span class="text-muted small">One-Time Activation Fee:</span>
                        <span class="fs-4 fw-bold text-success tabular-nums">Rs. <?= number_format($activationFee, 0) ?> PKR</span>
                    </div>
                    <ul class="small text-secondary ps-3 mb-0">
                        <li><strong>ONE TIME ONLY:</strong> You never pay listing fees per product again.</li>
                        <li><strong>Unlimited Free Listings:</strong> Post 1, 10, 50, or 100+ ads with zero additional charges.</li>
                        <li><strong>Anti-Spam Verification:</strong> Ensures genuine sellers across Sargodha, Shaheenabad & Sillanwali.</li>
                    </ul>
                </div>

                <!-- If Pending or Rejected -->
                <?php if ($lastPayment && $lastPayment['status'] === 'pending'): ?>
                    <div class="alert alert-warning small mb-4 shadow-sm">
                        <h6 class="fw-bold mb-1"><i class="bi bi-hourglass-split me-1"></i> Verification in Progress</h6>
                        Your payment submission (Trx ID: <code><?= e($lastPayment['transaction_id']) ?></code>) is currently being reviewed by an administrator. You will receive an alert once approved.
                    </div>
                <?php elseif ($lastPayment && $lastPayment['status'] === 'rejected'): ?>
                    <div class="alert alert-danger small mb-4 shadow-sm">
                        <h6 class="fw-bold mb-1"><i class="bi bi-exclamation-triangle-fill me-1"></i> Previous Payment Rejected</h6>
                        <p class="mb-1"><strong>Reason:</strong> <?= e($lastPayment['admin_note'] ?: 'Transaction ID could not be found.') ?></p>
                        Please re-check your EasyPaisa / JazzCash receipt and submit corrected details below.
                    </div>
                <?php endif; ?>

                <!-- Official Payment Account Details with Dynamic QR Code -->
                <div class="p-4 bg-success bg-opacity-10 border border-success rounded-3 mb-4">
                    <div class="row align-items-center g-3">
                        <div class="col-12 col-sm-8">
                            <span class="text-success fw-bold small text-uppercase tracking-wider d-block mb-1">
                                <i class="bi bi-shield-check me-1"></i> Official EasyPaisa / JazzCash Account
                            </span>
                            <div class="mb-2">
                                <small class="text-muted d-block">Account Title:</small>
                                <strong class="fs-6 text-dark"><?= e($accountName) ?></strong>
                            </div>
                            <div>
                                <small class="text-muted d-block">Account Mobile Number:</small>
                                <div class="d-flex align-items-center gap-2">
                                    <strong class="fs-5 text-success tabular-nums"><?= e($paymentNumber) ?></strong>
                                    <button type="button" class="btn btn-outline-success btn-sm py-0 px-2 btn-copy-payment" data-copy="<?= e($paymentNumber) ?>">
                                        <i class="bi bi-clipboard"></i> Copy
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Dynamic QR Code Visualization -->
                        <div class="col-12 col-sm-4 text-center">
                            <div class="p-2 bg-white rounded border d-inline-block shadow-sm">
                                <img id="php-qr-img" src="https://api.qrserver.com/v1/create-qr-code/?size=140x140&color=047857&data=<?= urlencode($paymentNumber) ?>" alt="Scan to pay <?= e($paymentNumber) ?>" class="img-fluid" style="width: 110px; height: 110px; object-fit: contain;">
                            </div>
                            <small class="text-muted d-block mt-1 fw-bold" style="font-size: 0.7rem;"><i class="bi bi-qr-code"></i> Scan EasyPaisa QR</small>
                            <div class="btn-group btn-group-sm mt-2" role="group">
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1" style="font-size: 0.65rem;" onclick="document.getElementById('php-qr-img').src='https://api.qrserver.com/v1/create-qr-code/?size=140x140&color=047857&data=<?= urlencode($paymentNumber) ?>'">Number</button>
                                <button type="button" class="btn btn-outline-secondary btn-sm py-0 px-1" style="font-size: 0.65rem;" onclick="document.getElementById('php-qr-img').src='https://api.qrserver.com/v1/create-qr-code/?size=140x140&color=047857&data=tel:<?= urlencode($paymentNumber) ?>'">Dial</button>
                                <a href="https://api.qrserver.com/v1/create-qr-code/?size=300x300&color=047857&data=<?= urlencode($paymentNumber) ?>" download="sargodhamart-qr.png" target="_blank" class="btn btn-outline-secondary btn-sm py-0 px-1" style="font-size: 0.65rem;" title="Download QR"><i class="bi bi-download"></i></a>
                            </div>
                        </div>
                    </div>
                </div>

                <?php if (!empty($errors)): ?>
                    <div class="alert alert-danger small shadow-sm mb-4">
                        <ul class="mb-0 ps-3">
                            <?php foreach ($errors as $err): ?>
                                <li><?= e($err) ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <!-- Submission Form -->
                <form method="POST" action="/user/activate.php" enctype="multipart/form-data">
                    <?= getCsrfField() ?>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Payment Method Used *</label>
                            <select name="payment_method" class="form-select" required>
                                <option value="EasyPaisa">EasyPaisa</option>
                                <option value="JazzCash">JazzCash</option>
                                <option value="Bank Transfer">Bank Transfer</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Your Sender Mobile Number *</label>
                            <input type="text" name="sender_number" class="form-control" placeholder="03001234567" value="<?= e($user['mobile_number']) ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Transaction ID (TRX ID) *</label>
                        <input type="text" name="transaction_id" class="form-control" placeholder="e.g. EP9823412095" required>
                        <small class="text-muted" style="font-size: 0.72rem;">Found on your confirmation SMS receipt</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Upload Payment Screenshot / Receipt *</label>
                        <input type="file" name="screenshot" class="form-control" accept="image/jpeg,image/png,image/webp" <?= ($lastPayment && $lastPayment['payment_screenshot']) ? '' : 'required' ?>>
                        <small class="text-muted" style="font-size: 0.72rem;">Attach screenshot of successful Rs. <?= $activationFee ?> transfer</small>
                    </div>

                    <button type="submit" class="btn btn-primary-green w-100 py-2.5 fw-bold">
                        Submit Activation Proof (Rs. <?= $activationFee ?>) &rarr;
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
