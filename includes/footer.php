</main>

<?php
$footerSiteName = getSetting('website_name', SITE_NAME);
$footerSiteTagline = getSetting('tagline', SITE_TAGLINE);
$footerActivationFee = getSetting('activation_fee', ONE_TIME_ACTIVATION_FEE);
$footerPayAccount = getSetting('payment_account_title', 'Muhammad Akram Tayyab');
$footerPayNumber = getSetting('easypaisa_number', '03127453108');
$footerCopyright = getSetting('copyright_text', '© ' . date('Y') . ' ' . $footerSiteName . '. Built for Sargodha, Shaheenabad & Sillanwali.');
$footerSocials = getSocialLinksPHP();
?>
<!-- Footer -->
<footer class="bg-dark text-white pt-5 pb-4 mt-5 border-top border-secondary">
    <div class="container">
        <div class="row g-4">
            <!-- Brand & Local Hubs -->
            <div class="col-12 col-md-4">
                <div class="d-flex align-items-center gap-2 mb-2">
                    <span class="text-success fs-3"><i class="bi bi-shop"></i></span>
                    <h5 class="mb-0 fw-bold tracking-tight"><?= e($footerSiteName) ?></h5>
                </div>
                <p class="text-secondary small mb-3">
                    <?= e($footerSiteTagline) ?>. The trusted local buy & sell platform dedicated to Sargodha, Shaheenabad, and Sillanwali. We connect verified local buyers and sellers across automobiles, livestock, citrus agriculture, electronics, and real estate.
                </p>
                <div class="p-3 bg-black bg-opacity-50 rounded border border-secondary mb-3 small">
                    <div class="fw-bold text-success mb-1"><i class="bi bi-shield-check"></i> One-Time Seller Activation Fee: Rs. <?= e($footerActivationFee) ?></div>
                    <div class="text-white-50">Account registration is 100% FREE. Pay Rs. <?= e($footerActivationFee) ?> once to activate your seller privileges. After approval, post unlimited normal listings completely free!</div>
                </div>
                <?php if (!empty($footerSocials)): ?>
                    <div class="d-flex gap-2">
                        <?php foreach ($footerSocials as $soc): ?>
                            <a href="<?= e($soc['url']) ?>" target="_blank" class="btn btn-outline-secondary btn-sm rounded-circle p-2 text-white" title="<?= e($soc['title']) ?>">
                                <?php if (stripos($soc['platform'], 'whatsapp') !== false): ?>
                                    <i class="bi bi-whatsapp text-success"></i>
                                <?php elseif (stripos($soc['platform'], 'facebook') !== false): ?>
                                    <i class="bi bi-facebook text-primary"></i>
                                <?php elseif (stripos($soc['platform'], 'instagram') !== false): ?>
                                    <i class="bi bi-instagram text-danger"></i>
                                <?php elseif (stripos($soc['platform'], 'youtube') !== false): ?>
                                    <i class="bi bi-youtube text-danger"></i>
                                <?php elseif (stripos($soc['platform'], 'tiktok') !== false): ?>
                                    <i class="bi bi-tiktok text-light"></i>
                                <?php else: ?>
                                    <i class="bi bi-link-45deg"></i>
                                <?php endif; ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Quick Categories -->
            <div class="col-6 col-md-2">
                <h6 class="text-uppercase text-success fw-bold small mb-3">Top Categories</h6>
                <ul class="list-unstyled small text-secondary d-flex flex-column gap-2">
                    <li><a href="/search.php?category=animals-livestock" class="text-secondary text-decoration-none">Livestock & Cattle</a></li>
                    <li><a href="/search.php?category=agriculture" class="text-secondary text-decoration-none">Kinnow & Agriculture</a></li>
                    <li><a href="/search.php?category=animal-feed-wanda" class="text-secondary text-decoration-none">Dairy Wanda Feed</a></li>
                    <li><a href="/search.php?category=bikes" class="text-secondary text-decoration-none">Bikes & Honda 125</a></li>
                    <li><a href="/search.php?category=mobiles" class="text-secondary text-decoration-none">Mobiles & Tablets</a></li>
                    <li><a href="/search.php?category=property" class="text-secondary text-decoration-none">Property & Plots</a></li>
                    <li><a href="/jobs.php" class="text-secondary text-decoration-none"><i class="bi bi-briefcase text-info me-1"></i> Jobs Hub</a></li>
                </ul>
            </div>

            <!-- Local Cities & Hubs -->
            <div class="col-6 col-md-2">
                <h6 class="text-uppercase text-success fw-bold small mb-3">Local Areas</h6>
                <ul class="list-unstyled small text-secondary d-flex flex-column gap-2">
                    <li><a href="/search.php?city=Sargodha" class="text-secondary text-decoration-none"><i class="bi bi-geo-alt text-success"></i> Sargodha City</a></li>
                    <li><a href="/search.php?city=Shaheenabad" class="text-secondary text-decoration-none"><i class="bi bi-geo-alt text-success"></i> Shaheenabad</a></li>
                    <li><a href="/search.php?city=Sillanwali" class="text-secondary text-decoration-none"><i class="bi bi-geo-alt text-success"></i> Sillanwali</a></li>
                    <li><a href="/search.php?featured=1" class="text-secondary text-decoration-none"><i class="bi bi-star-fill text-warning"></i> Featured Ads</a></li>
                    <li><a href="/categories.php" class="text-secondary text-decoration-none"><i class="bi bi-grid"></i> All Categories</a></li>
                </ul>
            </div>

            <!-- Payment Helpline & Safety -->
            <div class="col-12 col-md-4">
                <h6 class="text-uppercase text-success fw-bold small mb-3">Official Payment & Helpline</h6>
                <div class="bg-black bg-opacity-40 p-3 rounded border border-secondary small mb-3">
                    <div class="text-white-50">Authorized Receiver:</div>
                    <div class="fw-bold text-white fs-6"><?= e($footerPayAccount) ?></div>
                    <div class="text-white-50 mt-1">EasyPaisa / JazzCash Number:</div>
                    <div class="d-flex align-items-center justify-content-between mt-1">
                        <span class="fw-bold text-warning fs-5"><?= e($footerPayNumber) ?></span>
                        <button class="btn btn-outline-light btn-sm btn-copy-payment py-0 px-2" data-copy="<?= e($footerPayNumber) ?>">
                            <i class="bi bi-clipboard"></i> Copy
                        </button>
                    </div>
                </div>
                <div class="d-flex flex-wrap gap-3 small text-secondary">
                    <a href="/terms.php" class="text-secondary text-decoration-none">Terms</a>
                    <span>·</span>
                    <a href="/privacy.php" class="text-secondary text-decoration-none">Privacy</a>
                    <span>·</span>
                    <a href="/rules.php" class="text-secondary text-decoration-none">Listing Rules</a>
                    <span>·</span>
                    <a href="/about.php" class="text-secondary text-decoration-none">About Us</a>
                    <span>·</span>
                    <a href="/contact.php" class="text-secondary text-decoration-none">Contact Support</a>
                </div>
            </div>
        </div>

        <hr class="border-secondary my-4">

        <div class="d-flex flex-column flex-sm-row justify-content-between align-items-center gap-2 small text-secondary pb-5 pb-md-0">
            <div>
                <?= e($footerCopyright) ?>
            </div>
            <div class="text-muted">
                Always inspect products and verify sellers in person before making payments.
            </div>
        </div>
    </div>
</footer>

<?php
$currentUri = $_SERVER['REQUEST_URI'] ?? '/';
$isAdminPortal = str_contains($currentUri, '/admin');
$isUserPortal = str_contains($currentUri, '/user');
?>

<!-- Sticky Mobile-First Bottom Navigation (Touch screens: < 768px) -->
<nav class="d-md-none fixed-bottom bg-white border-top shadow-lg py-1 px-2 z-3">
    <div class="d-flex justify-content-around align-items-center text-center">
        <?php if ($isAdminPortal): ?>
            <!-- Admin Portal Bottom Nav -->
            <a href="/admin/index.php" class="text-decoration-none py-1 px-2 <?= $currentUri === '/admin/index.php' || $currentUri === '/admin/' ? 'text-success fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-speedometer2 d-block fs-5"></i>
                <span style="font-size: 0.65rem;">Dashboard</span>
            </a>
            <a href="/admin/users.php" class="text-decoration-none py-1 px-2 <?= str_contains($currentUri, 'users') ? 'text-success fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-people d-block fs-5"></i>
                <span style="font-size: 0.65rem;">Users</span>
            </a>
            <a href="/admin/listings.php" class="text-decoration-none py-1 px-2 <?= str_contains($currentUri, 'listings') ? 'text-success fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-card-list d-block fs-5"></i>
                <span style="font-size: 0.65rem;">Ads</span>
            </a>
            <a href="/admin/activation-payments.php" class="text-decoration-none py-1 px-2 <?= str_contains($currentUri, 'payment') ? 'text-success fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-credit-card d-block fs-5"></i>
                <span style="font-size: 0.65rem;">Payments</span>
            </a>
            <a href="/admin/settings.php" class="text-decoration-none py-1 px-2 <?= str_contains($currentUri, 'settings') || str_contains($currentUri, 'logs') ? 'text-success fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-three-dots d-block fs-5"></i>
                <span style="font-size: 0.65rem;">More</span>
            </a>
        <?php elseif ($isUserPortal): ?>
            <!-- User Portal Bottom Nav -->
            <a href="/user/dashboard.php" class="text-decoration-none py-1 px-2 <?= $currentUri === '/user/dashboard.php' ? 'text-success fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-speedometer2 d-block fs-5"></i>
                <span style="font-size: 0.65rem;">Dashboard</span>
            </a>
            <a href="/user/my-listings.php" class="text-decoration-none py-1 px-2 <?= str_contains($currentUri, 'my-listings') ? 'text-success fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-collection d-block fs-5"></i>
                <span style="font-size: 0.65rem;">My Ads</span>
            </a>
            <!-- Highlighted Post Ad touch target -->
            <a href="/user/post-ad.php" class="text-decoration-none position-relative" style="top: -12px;">
                <div class="bg-success text-white rounded-circle shadow d-flex align-items-center justify-content-center mx-auto" style="width: 48px; height: 48px;">
                    <i class="bi bi-plus-lg fs-4"></i>
                </div>
                <span class="text-success fw-bold d-block mt-1" style="font-size: 0.65rem;">Post Ad</span>
            </a>
            <a href="/user/messages.php" class="text-decoration-none py-1 px-2 <?= str_contains($currentUri, 'messages') ? 'text-success fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-chat-dots d-block fs-5"></i>
                <span style="font-size: 0.65rem;">Messages</span>
            </a>
            <a href="/user/profile.php" class="text-decoration-none py-1 px-2 <?= str_contains($currentUri, 'profile') || str_contains($currentUri, 'settings') ? 'text-success fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-person d-block fs-5"></i>
                <span style="font-size: 0.65rem;">Profile</span>
            </a>
        <?php else: ?>
            <!-- Public Marketplace Portal Bottom Nav -->
            <a href="/index.php" class="text-decoration-none py-1 px-2 <?= $currentUri === '/' || $currentUri === '/index.php' ? 'text-success fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-house-door d-block fs-5"></i>
                <span style="font-size: 0.65rem;">Home</span>
            </a>
            <a href="/categories.php" class="text-decoration-none py-1 px-2 <?= str_contains($currentUri, 'categories') ? 'text-success fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-grid d-block fs-5"></i>
                <span style="font-size: 0.65rem;">Categories</span>
            </a>
            <a href="/search.php" class="text-decoration-none py-1 px-2 <?= str_contains($currentUri, 'search') ? 'text-success fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-search d-block fs-5"></i>
                <span style="font-size: 0.65rem;">Search</span>
            </a>
            <a href="/user/favorites.php" class="text-decoration-none py-1 px-2 <?= str_contains($currentUri, 'favorites') ? 'text-success fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-heart d-block fs-5"></i>
                <span style="font-size: 0.65rem;">Favorites</span>
            </a>
            <a href="<?= isLoggedIn() ? '/user/dashboard.php' : '/login.php' ?>" class="text-decoration-none py-1 px-2 <?= str_contains($currentUri, 'login') || str_contains($currentUri, 'user') ? 'text-success fw-bold' : 'text-secondary' ?>">
                <i class="bi bi-person-circle d-block fs-5"></i>
                <span style="font-size: 0.65rem;"><?= isLoggedIn() ? 'Account' : 'Login' ?></span>
            </a>
        <?php endif; ?>
    </div>
</nav>

<!-- SargodhaMart Public AI Assistant Floating Widget -->
<?php if (file_exists(__DIR__ . '/../components/ai-assistant/chat-widget.php')): ?>
    <?php include_once __DIR__ . '/../components/ai-assistant/chat-widget.php'; ?>
<?php endif; ?>

<!-- Bootstrap 5 Bundle JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<!-- Custom JavaScript -->
<script src="/assets/js/app.js"></script>
</body>
</html>
