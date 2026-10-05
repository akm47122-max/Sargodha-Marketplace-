<?php
/**
 * SARGODHAMART - Safety Guidelines & Buyer Protection
 * Target local areas: Sargodha | Shaheenabad | Sillanwali
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'Safety Guidance & Scam Prevention - ' . SITE_NAME;
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <!-- Breadcrumb -->
    <nav aria-label="breadcrumb" class="mb-3">
        <ol class="breadcrumb small">
            <li class="breadcrumb-item"><a href="/index.php" class="text-decoration-none text-success">Home</a></li>
            <li class="breadcrumb-item active" aria-current="page">Safety Tips</li>
        </ol>
    </nav>

    <!-- Header Banner -->
    <div class="card border-0 bg-success text-white rounded-4 p-4 p-md-5 mb-4 shadow-sm">
        <div class="row align-items-center">
            <div class="col-md-8">
                <span class="badge bg-white text-success px-3 py-1 mb-2 fw-bold text-uppercase">Trust & Safety</span>
                <h1 class="fw-bold mb-2">Safe Trading Guide for SargodhaMart</h1>
                <p class="mb-0 text-white-50 fs-6">
                    Buy, sell, and connect safely across Sargodha, Shaheenabad, and Sillanwali. Follow these practical rules to protect yourself from fraud.
                </p>
            </div>
            <div class="col-md-4 text-md-end mt-3 mt-md-0">
                <span class="display-3"><i class="bi bi-shield-check"></i></span>
            </div>
        </div>
    </div>

    <!-- Key Safety Pillars -->
    <div class="row g-4 mb-5">
        <div class="col-12 col-md-4">
            <div class="card h-100 border rounded-3 p-4 shadow-sm">
                <div class="text-success fs-1 mb-3"><i class="bi bi-geo-alt-fill"></i></div>
                <h5 class="fw-bold text-dark mb-2">1. Meet in Safe Public Places</h5>
                <p class="text-secondary small mb-0">
                    Always arrange physical meetings in bustling, well-lit local public areas such as Club Road or Fatima Jinnah Road in Sargodha, Main Bazaar Shaheenabad, or Railway Road Sillanwali. Never meet in secluded or unfamiliar locations.
                </p>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card h-100 border rounded-3 p-4 shadow-sm">
                <div class="text-success fs-1 mb-3"><i class="bi bi-cash-stack"></i></div>
                <h5 class="fw-bold text-dark mb-2">2. Inspect Before You Pay</h5>
                <p class="text-secondary small mb-0">
                    Never send advance payments via EasyPaisa, JazzCash, or bank transfer before physically verifying the product. For vehicles, verify original CPLC/Excise registration; for livestock, inspect health and milk yield in person.
                </p>
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="card h-100 border rounded-3 p-4 shadow-sm">
                <div class="text-success fs-1 mb-3"><i class="bi bi-lock-fill"></i></div>
                <h5 class="fw-bold text-dark mb-2">3. Keep Private Data Safe</h5>
                <p class="text-secondary small mb-0">
                    Never share your account passwords, OTP codes, CNIC photocopies, or bank pin numbers with anyone claiming to be a buyer or SargodhaMart official. Our staff will never request your banking passwords.
                </p>
            </div>
        </div>
    </div>

    <!-- Category Specific Safety Advice -->
    <div class="card border rounded-3 p-4 mb-4 shadow-sm">
        <h4 class="fw-bold text-dark mb-3"><i class="bi bi-exclamation-triangle text-warning me-2"></i>Category-Specific Guidance</h4>
        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold text-success"><i class="bi bi-truck me-1"></i> Cars & Motorcycles (Honda 125, etc.)</h6>
                    <ul class="small text-secondary ps-3 mb-0">
                        <li>Verify engine and chassis numbers against the original Punjab Excise registration book.</li>
                        <li>Test drive only in daylight and bring a trusted mechanic along.</li>
                        <li>Always execute a formal stamped vehicle sale deed with valid CNIC copies.</li>
                    </ul>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold text-success"><i class="bi bi-tag me-1"></i> Livestock & Dairy Cattle</h6>
                    <ul class="small text-secondary ps-3 mb-0">
                        <li>Conduct on-site morning and evening milking trials for dairy cows or buffalos.</li>
                        <li>Check animal vaccination records and inspect teeth and hooves.</li>
                        <li>Agree on local transportation responsibility before finalizing the deal.</li>
                    </ul>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold text-success"><i class="bi bi-phone me-1"></i> Mobiles & Electronics</h6>
                    <ul class="small text-secondary ps-3 mb-0">
                        <li>Check PTA approval status by texting the 15-digit IMEI to 8484 or via PTA DVS app.</li>
                        <li>Verify that iCloud/Google accounts are completely signed out and formatted.</li>
                        <li>Match IMEI on the physical mobile with the original retail box.</li>
                    </ul>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="p-3 bg-light rounded-3 border">
                    <h6 class="fw-bold text-success"><i class="bi bi-house me-1"></i> Property & Agricultural Land</h6>
                    <ul class="small text-secondary ps-3 mb-0">
                        <li>Verify Fard Malkiat and Registry documents at the local Land Record Center (Arazi Record Center Sargodha/Sillanwali).</li>
                        <li>Inspect physical land boundaries and water turn (Wara-Bandi) rights.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>

    <!-- Official Helpline & Report Notice -->
    <div class="p-4 bg-light rounded-3 border text-center">
        <h5 class="fw-bold text-dark mb-2">Spot Suspicious Activity?</h5>
        <p class="text-secondary small mb-3 max-w-lg mx-auto">
            Use the "Report Listing" button on any suspicious ad, or contact SargodhaMart administration immediately.
        </p>
        <div class="d-flex justify-content-center gap-3">
            <a href="https://wa.me/923127453108" target="_blank" class="btn btn-success fw-bold">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp: 03127453108
            </a>
            <a href="/contact.php" class="btn btn-outline-secondary fw-bold">
                Contact Support
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
