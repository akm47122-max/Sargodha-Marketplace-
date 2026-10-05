<?php
/**
 * Sargodha Mandi - Contact Us & Payment Helpline
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();
    $sent = true;
}

$pageTitle = 'Contact & Support - Sargodha Mandi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border shadow-sm p-4 p-md-5 bg-white rounded-3">
                <span class="badge bg-success mb-2 px-3 py-1">Customer Support</span>
                <h3 class="fw-bold mb-2">Contact & Helpline</h3>
                <?php
                $contactFee = getSetting('activation_fee', 1000);
                $contactName = getSetting('payment_account_title', 'Muhammad Akram Tayyab');
                $contactWa = getSetting('official_whatsapp', '03127453108');
                $cleanContactWa = preg_replace('/[^0-9]/', '', $contactWa);
                if (str_starts_with($cleanContactWa, '0')) {
                    $cleanContactWa = '92' . substr($cleanContactWa, 1);
                }
                $contactAddress = getSetting('official_address', 'Trust Plaza / Club Road, Sargodha, Punjab, Pakistan');
                ?>
                <p class="text-muted small mb-4">Have questions regarding your Rs. <?= e($contactFee) ?> activation fee, verification, or reporting a suspicious ad? Reach out to us directly.</p>

                <!-- Official Payment & Helpline Info -->
                <div class="p-3 bg-light rounded border mb-4">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-person-badge text-success me-1"></i> Authorized Contact Person</h6>
                    <div class="row g-2 small">
                        <div class="col-12 col-sm-6">
                            <span class="text-muted d-block">Account Name:</span>
                            <strong class="fs-6"><?= e($contactName) ?></strong>
                        </div>
                        <div class="col-12 col-sm-6">
                            <span class="text-muted d-block">WhatsApp / Call Helpline:</span>
                            <a href="https://wa.me/<?= e($cleanContactWa) ?>" target="_blank" class="fs-6 fw-bold text-success text-decoration-none">
                                <i class="bi bi-whatsapp"></i> <?= e($contactWa) ?>
                            </a>
                        </div>
                        <div class="col-12 mt-2 pt-2 border-top">
                            <span class="text-muted">Office Location:</span>
                            <strong><?= e($contactAddress) ?></strong>
                        </div>
                    </div>
                </div>

                <?php if ($sent): ?>
                    <div class="alert alert-success shadow-sm">
                        <i class="bi bi-check-circle me-1"></i> Thank you! Your message has been sent to support. We will reply via WhatsApp or phone shortly.
                    </div>
                <?php endif; ?>

                <form method="POST" action="/contact.php">
                    <?= getCsrfField() ?>
                    <div class="row g-3 mb-3">
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Your Name *</label>
                            <input type="text" name="name" class="form-control" placeholder="Muhammad Ali" required>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="form-label small fw-bold">Mobile Number (WhatsApp) *</label>
                            <input type="text" name="phone" class="form-control" placeholder="03127453108" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Subject</label>
                        <select name="subject" class="form-select form-select-sm">
                            <option value="Payment Inquiry">Payment Verification (Rs. 500 Listing Fee)</option>
                            <option value="Ad Issues">Problem with my Ad</option>
                            <option value="Fraud Report">Report Fraudulent Seller</option>
                            <option value="General Question">General Inquiry</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="form-label small fw-bold">Your Message *</label>
                        <textarea name="message" class="form-control" rows="4" placeholder="Include your Ad ID or Transaction ID if related to a payment..." required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary-green px-4 fw-bold">
                        <i class="bi bi-send me-1"></i> Send Message
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
