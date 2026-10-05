<?php
/**
 * Sargodha Mandi - Privacy Policy
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Privacy Policy - Sargodha Mandi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border shadow-sm p-4 p-md-5 bg-white rounded-3">
                <h3 class="fw-bold mb-3">Privacy Policy</h3>
                <small class="text-muted d-block mb-4">Last Updated: September 2026</small>

                <div class="small text-secondary lh-lg">
                    <h6 class="fw-bold text-dark">1. Information We Collect</h6>
                    <p>We collect your full name, mobile number, email address, password hash, and general location (city and area) during registration. When you post an ad, we display your chosen contact preference, phone, and location to facilitate buyer inquiries.</p>

                    <h6 class="fw-bold text-dark mt-3">2. How Your Information Is Used</h6>
                    <p>Your mobile number is used to allow buyers to contact you via Call or WhatsApp. We never sell, rent, or trade your personal information to third parties or marketing brokers.</p>

                    <h6 class="fw-bold text-dark mt-3">3. Payment Information Security</h6>
                    <p>When you submit payment screenshots and Transaction IDs for the Rs. 500 listing fee, they are stored securely in protected directories accessible only to authorized administrators for transaction verification.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
