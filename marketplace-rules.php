<?php
/**
 * SARGODHAMART - Marketplace Rules & Listing Policies
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Marketplace Rules & Posting Policies - ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="/index.php" class="text-decoration-none text-success">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Marketplace Rules</li>
        </ol>
    </nav>

    <div class="card border rounded-3 p-4 p-md-5 bg-white shadow-sm mb-4">
        <div class="border-bottom pb-3 mb-4">
            <span class="badge bg-success bg-opacity-10 text-success border border-success px-3 py-1 mb-2 fw-bold text-uppercase">Policies</span>
            <h2 class="fw-bold mb-1">SargodhaMart Listing & Seller Rules</h2>
            <p class="text-secondary small mb-0">Transparent guidelines for maintaining a verified, scam-free local marketplace.</p>
        </div>

        <!-- 1. The Business Model: One-Time Rs. 500 Activation Fee -->
        <div class="mb-4 p-3 bg-light rounded-3 border">
            <h5 class="fw-bold text-success mb-2"><i class="bi bi-patch-check-fill me-1"></i> 1. One-Time Seller Activation Fee (Rs. 500)</h5>
            <p class="small text-secondary mb-2">
                To eliminate fraudulent bots, fake spam accounts, and unserious posters, SargodhaMart requires a <strong>one-time account activation payment of Rs. 500 PKR</strong>.
            </p>
            <ul class="small text-secondary ps-3 mb-0">
                <li><strong>One Time Only:</strong> The Rs. 500 fee attaches to the user account, NEVER to individual products.</li>
                <li><strong>Unlimited Normal Listings:</strong> Once approved by the administrator, the seller can publish unlimited normal product listings completely FREE forever.</li>
                <li><strong>Official Receiver:</strong> EasyPaisa / JazzCash <code>03127453108</code> (Muhammad Akram Tayyab).</li>
            </ul>
        </div>

        <!-- 2. Prohibited Items -->
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-x-circle-fill text-danger me-1"></i> 2. Strictly Prohibited Listings</h5>
            <p class="small text-secondary mb-2">The following items are forbidden and will lead to immediate account suspension:</p>
            <div class="row g-2 small text-secondary">
                <div class="col-md-6">&bull; Weapons, firearms, ammunition, or fireworks</div>
                <div class="col-md-6">&bull; Stolen property or non-custom-paid vehicles</div>
                <div class="col-md-6">&bull; Non-PTA approved phones without explicit disclaimer</div>
                <div class="col-md-6">&bull; Prescription drugs, narcotics, or unregulated chemicals</div>
                <div class="col-md-6">&bull; Counterfeit goods or pirated software</div>
                <div class="col-md-6">&bull; Multi-level marketing (MLM), pyramid schemes, or fake online job scams</div>
            </div>
        </div>

        <!-- 3. Ad Content & Accuracy -->
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-card-checklist text-primary me-1"></i> 3. Accurate Descriptions & Genuine Photos</h5>
            <ul class="small text-secondary ps-3 mb-0">
                <li>Upload real photos taken by yourself in daylight. Stock photos from the internet are prohibited.</li>
                <li>Provide honest descriptions of defects, mileage, or repair history.</li>
                <li>Set realistic prices in PKR. Do not enter fake Rs. 1 or Rs. 999999 placeholder prices.</li>
                <li>Assign the correct category and select your accurate local area (Sargodha, Shaheenabad, or Sillanwali).</li>
            </ul>
        </div>

        <!-- 4. Admin Moderation & Audit -->
        <div class="mb-4">
            <h5 class="fw-bold text-dark mb-2"><i class="bi bi-shield-lock-fill text-warning me-1"></i> 4. Administrator Moderation Rights</h5>
            <p class="small text-secondary mb-0">
                Our administration actively monitors submissions. SargodhaMart reserves the right to edit, suspend, hide, or delete any listing that violates our local trade standards or receives verified scam reports.
            </p>
        </div>

        <div class="pt-3 border-top d-flex flex-wrap gap-2">
            <a href="/register.php" class="btn btn-success fw-bold">Register as Seller</a>
            <a href="/safety.php" class="btn btn-outline-secondary">View Safety Tips</a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
