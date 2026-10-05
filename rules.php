<?php
/**
 * Sargodha Mandi - Listing Rules & Fee Policy
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Listing Rules & Fee Policy - Sargodha Mandi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border shadow-sm p-4 p-md-5 bg-white rounded-3">
                <span class="badge bg-success mb-2 px-3 py-1">Community Guidelines</span>
                <h3 class="fw-bold mb-3">Product Listing Rules & Fee Policy</h3>
                <p class="lead fs-6 text-muted mb-4">
                    Sargodha Mandi is designed to keep local buy and sell safe, honest and free from fraudulent spam across Sargodha, Shaheenabad, and Sillanwali.
                </p>

                <!-- Listing Fee Policy Box -->
                <div class="p-3 bg-success bg-opacity-10 border border-success rounded-3 mb-4">
                    <h5 class="fw-bold text-success mb-2"><i class="bi bi-shield-check me-1"></i> Rs. 500 Product Listing Fee</h5>
                    <p class="small text-secondary mb-2">
                        Account creation and browsing are <strong>100% Free</strong>. To guarantee genuine sellers and eliminate fake ads, every published ad requires a standard <strong>Rs. 500 listing fee</strong>.
                    </p>
                    <ul class="small text-secondary mb-0 ps-3">
                        <li>Payment is made manually to <strong>03127453108 (Muhammad Akram Tayyab)</strong> via EasyPaisa or JazzCash.</li>
                        <li>An administrator personally verifies the Transaction ID and payment screenshot before publishing.</li>
                        <li>Payments cannot be refunded once an ad is approved and published.</li>
                    </ul>
                </div>

                <h5 class="fw-bold mt-4 mb-2">Listing Rules</h5>
                <ul class="small text-secondary mb-4 ps-3 lh-lg">
                    <li><strong>Real Photos:</strong> Only upload genuine photos of the actual item for sale. Downloaded internet stock photos for used goods or livestock are not allowed.</li>
                    <li><strong>Accurate Location:</strong> Select the correct location hub (Sargodha, Shaheenabad, or Sillanwali) and specify your local bazaar or neighborhood.</li>
                    <li><strong>Accurate Price:</strong> Post the real asking price. Misleading prices such as "Rs. 1" or "Free" are subject to rejection.</li>
                    <li><strong>One Item per Ad:</strong> Do not create duplicate ads for the same item.</li>
                </ul>

                <h5 class="fw-bold text-danger mt-4 mb-2"><i class="bi bi-slash-circle me-1"></i> Prohibited Items</h5>
                <p class="small text-muted mb-2">The following items are strictly prohibited and will be removed immediately:</p>
                <div class="row row-cols-1 row-cols-sm-2 g-2 small text-secondary mb-4">
                    <div class="col"><i class="bi bi-x-circle text-danger me-1"></i> Weapons, firearms, ammunition</div>
                    <div class="col"><i class="bi bi-x-circle text-danger me-1"></i> Non-PTA / Non-custom paid devices</div>
                    <div class="col"><i class="bi bi-x-circle text-danger me-1"></i> Stolen goods & counterfeit products</div>
                    <div class="col"><i class="bi bi-x-circle text-danger me-1"></i> Illegal drugs & prescription narcotics</div>
                    <div class="col"><i class="bi bi-x-circle text-danger me-1"></i> Endangered wildlife without permits</div>
                    <div class="col"><i class="bi bi-x-circle text-danger me-1"></i> Fake currency or financial pyramid schemes</div>
                </div>

                <div class="border-top pt-3 text-center">
                    <a href="/post-ad.php" class="btn btn-primary-green px-4 fw-bold">
                        <i class="bi bi-plus-circle me-1"></i> Post an Ad Now (Rs. 500)
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
