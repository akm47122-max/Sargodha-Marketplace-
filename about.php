<?php
/**
 * Sargodha Mandi - About Us
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$pageTitle = 'About Us - Sargodha Mandi';
require_once __DIR__ . '/includes/header.php';
?>

<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-12 col-lg-8">
            <div class="card border shadow-sm p-4 p-md-5 bg-white rounded-3">
                <span class="badge bg-success mb-2 px-3 py-1">About Our Platform</span>
                <h3 class="fw-bold mb-3">Connecting Sargodha, Shaheenabad & Sillanwali</h3>
                
                <p class="lead fs-6 text-muted mb-4">
                    Sargodha Mandi is the premier hyper-local classifieds marketplace tailored specifically to the needs of residents, farmers, shopkeepers, and traders in Sargodha, Shaheenabad, and Sillanwali.
                </p>

                <h5 class="fw-bold text-dark mt-3 mb-2">Our Mission</h5>
                <p class="small text-secondary lh-lg mb-4">
                    Most national classified platforms are flooded with spam, out-of-province scams, and irrelevant listings hundreds of miles away. Sargodha Mandi was founded by <strong>Muhammad Akram Tayyab</strong> to provide a transparent, dedicated platform where local deals can be made face-to-face within our community.
                </p>

                <div class="row g-3 mb-4">
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light rounded border h-100">
                            <h6 class="fw-bold text-success mb-1">🍊 Sillanwali & Agriculture</h6>
                            <p class="small text-muted mb-0">World-famous Kinnow citrus, agricultural tractors, implements, and livestock cattle feed (wanda).</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light rounded border h-100">
                            <h6 class="fw-bold text-success mb-1">🐄 Shaheenabad & Livestock</h6>
                            <p class="small text-muted mb-0">Prize Sahiwal cows, Nili-Ravi dairy buffaloes, goats, dairy supplies, and rural trade.</p>
                        </div>
                    </div>
                    <div class="col-12 col-md-4">
                        <div class="p-3 bg-light rounded border h-100">
                            <h6 class="fw-bold text-success mb-1">🏙️ Sargodha City Hub</h6>
                            <p class="small text-muted mb-0">Trust Plaza mobile markets, Fatima Jinnah road motorcycles, real estate plots, and professional services.</p>
                        </div>
                    </div>
                </div>

                <div class="p-3 bg-success bg-opacity-10 border border-success rounded text-center">
                    <h6 class="fw-bold text-success mb-1">Have questions or want to partner?</h6>
                    <p class="small text-secondary mb-2">Contact administrator Muhammad Akram Tayyab on WhatsApp:</p>
                    <a href="https://wa.me/923127453108" class="btn btn-whatsapp btn-sm fw-bold">
                        <i class="bi bi-whatsapp me-1"></i> WhatsApp: 03127453108
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
