<?php
/**
 * SARGODHAMART - Central Website Settings System (Admin Panel)
 * Professional 11-Section Modular Settings Management
 * Allows the website owner to update settings without touching PHP files.
 */

$adminTitle = 'Central Website Settings - Admin Control Center';
require_once __DIR__ . '/includes/admin_header.php';

$activeTab = cleanInput($_GET['tab'] ?? 'general');
$validTabs = [
    'general',
    'whatsapp',
    'announcements',
    'social',
    'seo',
    'homepage',
    'marketplace',
    'jobs',
    'activation',
    'notifications',
    'maintenance',
    'telegram',
    'email'
];

if (!in_array($activeTab, $validTabs, true)) {
    $activeTab = 'general';
}

// -------------------------------------------------------------
// Form Processing & Persistence
// -------------------------------------------------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    verifyCsrfOrDie();

    $action = $_POST['action'] ?? 'save_settings';

    // Helper to update setting key-values safely
    $saveKeyVal = function(array $items, string $group) use ($db) {
        foreach ($items as $k => $v) {
            $stmt = $db->prepare("
                INSERT INTO site_settings (setting_key, setting_value, setting_group) 
                VALUES (:k, :v, :g) 
                ON DUPLICATE KEY UPDATE setting_value = :v2, setting_group = :g2, updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([':k' => $k, ':v' => (string)$v, ':g' => $group, ':v2' => (string)$v, ':g2' => $group]);
        }
    };

    // 1. GENERAL SETTINGS
    if ($action === 'save_general') {
        $generalData = [
            'website_name' => trim($_POST['website_name'] ?? 'SargodhaMart'),
            'site_name' => trim($_POST['website_name'] ?? 'SargodhaMart'),
            'logo_text' => trim($_POST['logo_text'] ?? 'SARGODHAMART'),
            'logo_image_url' => trim($_POST['logo_image_url'] ?? ''),
            'favicon_url' => trim($_POST['favicon_url'] ?? ''),
            'tagline' => trim($_POST['tagline'] ?? 'Buy • Sell • Jobs • Grow'),
            'site_tagline' => trim($_POST['tagline'] ?? 'Buy • Sell • Jobs • Grow'),
            'website_description' => trim($_POST['website_description'] ?? ''),
            'contact_email' => trim($_POST['contact_email'] ?? 'support@sargodhamart.com'),
            'official_phone' => trim($_POST['official_phone'] ?? '03127453108'),
            'contact_phone' => trim($_POST['official_phone'] ?? '03127453108'),
            'official_whatsapp' => trim($_POST['official_whatsapp'] ?? '03127453108'),
            'official_whatsapp_number' => trim($_POST['official_whatsapp'] ?? '03127453108'),
            'official_address' => trim($_POST['official_address'] ?? 'Trust Plaza / Club Road, Sargodha, Punjab, Pakistan'),
            'copyright_text' => trim($_POST['copyright_text'] ?? '© 2026 SARGODHAMART. All rights reserved.'),
        ];
        $saveKeyVal($generalData, 'general');
        logAdminAction($admin['id'], 'update_settings', 'site_settings', 0, 'Updated General Website Settings');
        redirect('/admin/settings.php?tab=general', 'General website settings successfully saved!', 'success');
    }

    // 2. WHATSAPP & CONTACT SETTINGS
    if ($action === 'save_whatsapp') {
        $whatsappData = [
            'whatsapp_channel_url' => trim($_POST['whatsapp_channel_url'] ?? 'https://whatsapp.com/channel/0029Vb8bmhAGk1Fzze6iTX0g'),
            'whatsapp_group_url' => trim($_POST['whatsapp_group_url'] ?? ''),
            'official_whatsapp_number' => trim($_POST['official_whatsapp_number'] ?? '03127453108'),
            'official_whatsapp' => trim($_POST['official_whatsapp_number'] ?? '03127453108'),
            'official_call_number' => trim($_POST['official_call_number'] ?? '03127453108'),
            'whatsapp_channel_name' => trim($_POST['whatsapp_channel_name'] ?? 'SargodhaMart Official Channel'),
            'whatsapp_group_name' => trim($_POST['whatsapp_group_name'] ?? 'Sargodha Local Community Group'),
            'whatsapp_btn_text' => trim($_POST['whatsapp_btn_text'] ?? 'WhatsApp'),
            'call_btn_text' => trim($_POST['call_btn_text'] ?? 'Call'),
            'follow_channel_btn_text' => trim($_POST['follow_channel_btn_text'] ?? 'Follow Official Channel'),
            'join_group_btn_text' => trim($_POST['join_group_btn_text'] ?? 'Join WhatsApp Group'),
            'is_whatsapp_channel_enabled' => isset($_POST['is_whatsapp_channel_enabled']) ? '1' : '0',
            'is_whatsapp_group_enabled' => isset($_POST['is_whatsapp_group_enabled']) ? '1' : '0',
            'is_whatsapp_contact_enabled' => isset($_POST['is_whatsapp_contact_enabled']) ? '1' : '0',
            'is_call_button_enabled' => isset($_POST['is_call_button_enabled']) ? '1' : '0',
        ];
        $saveKeyVal($whatsappData, 'whatsapp');
        logAdminAction($admin['id'], 'update_settings', 'site_settings', 0, 'Updated WhatsApp & Contact Settings');
        redirect('/admin/settings.php?tab=whatsapp', 'WhatsApp and helpline contact settings updated!', 'success');
    }

    // 3. ANNOUNCEMENTS SETTINGS
    if ($action === 'create_announcement') {
        $title = trim($_POST['ann_title'] ?? '');
        $message = trim($_POST['ann_message'] ?? '');
        $btnText = trim($_POST['ann_btn_text'] ?? '');
        $btnUrl = trim($_POST['ann_btn_url'] ?? '');
        $imgUrl = trim($_POST['ann_img_url'] ?? '');
        $location = trim($_POST['ann_location'] ?? 'top_bar');
        $highlight = isset($_POST['ann_highlighted']) ? 1 : 0;
        $active = isset($_POST['ann_active']) ? 1 : 0;
        $start = !empty($_POST['ann_start']) ? $_POST['ann_start'] : null;
        $expiry = !empty($_POST['ann_expiry']) ? $_POST['ann_expiry'] : null;

        if (!empty($title) && !empty($message)) {
            $stmt = $db->prepare("
                INSERT INTO announcements 
                (title, message, image_url, button_text, button_url, display_location, is_highlighted, is_active, start_date, expiry_date) 
                VALUES (:t, :m, :img, :bt, :bu, :loc, :hi, :ac, :st, :ex)
            ");
            $stmt->execute([
                ':t' => $title,
                ':m' => $message,
                ':img' => $imgUrl ?: null,
                ':bt' => $btnText ?: null,
                ':bu' => $btnUrl ?: null,
                ':loc' => in_array($location, ['top_bar', 'homepage_banner', 'popup'], true) ? $location : 'top_bar',
                ':hi' => $highlight,
                ':ac' => $active,
                ':st' => $start,
                ':ex' => $expiry,
            ]);
            logAdminAction($admin['id'], 'create_announcement', 'announcements', (int)$db->lastInsertId(), "Created announcement: {$title}");
            redirect('/admin/settings.php?tab=announcements', 'New announcement published successfully!', 'success');
        }
    }

    if ($action === 'delete_announcement') {
        $annId = (int)($_POST['announcement_id'] ?? 0);
        if ($annId > 0) {
            $db->prepare("DELETE FROM announcements WHERE id = :id")->execute([':id' => $annId]);
            logAdminAction($admin['id'], 'delete_announcement', 'announcements', $annId, "Deleted announcement ID {$annId}");
            redirect('/admin/settings.php?tab=announcements', 'Announcement removed.', 'info');
        }
    }

    if ($action === 'toggle_announcement') {
        $annId = (int)($_POST['announcement_id'] ?? 0);
        if ($annId > 0) {
            $db->prepare("UPDATE announcements SET is_active = IF(is_active = 1, 0, 1) WHERE id = :id")->execute([':id' => $annId]);
            redirect('/admin/settings.php?tab=announcements', 'Announcement status toggled.', 'success');
        }
    }

    // 4. SOCIAL MEDIA SETTINGS
    if ($action === 'save_social') {
        $socialData = [
            'social_facebook' => trim($_POST['social_facebook'] ?? ''),
            'social_instagram' => trim($_POST['social_instagram'] ?? ''),
            'social_youtube' => trim($_POST['social_youtube'] ?? ''),
            'social_tiktok' => trim($_POST['social_tiktok'] ?? ''),
            'social_whatsapp_channel' => trim($_POST['social_whatsapp_channel'] ?? ''),
            'is_facebook_enabled' => isset($_POST['is_facebook_enabled']) ? '1' : '0',
            'is_instagram_enabled' => isset($_POST['is_instagram_enabled']) ? '1' : '0',
            'is_youtube_enabled' => isset($_POST['is_youtube_enabled']) ? '1' : '0',
            'is_tiktok_enabled' => isset($_POST['is_tiktok_enabled']) ? '1' : '0',
            'is_whatsapp_social_enabled' => isset($_POST['is_whatsapp_social_enabled']) ? '1' : '0',
        ];
        $saveKeyVal($socialData, 'social');
        logAdminAction($admin['id'], 'update_settings', 'site_settings', 0, 'Updated Social Media Settings');
        redirect('/admin/settings.php?tab=social', 'Social media links updated successfully!', 'success');
    }

    // 5. SEO SETTINGS
    if ($action === 'save_seo') {
        $seoData = [
            'default_seo_title' => trim($_POST['default_seo_title'] ?? 'SargodhaMart - Buy • Sell • Jobs • Grow'),
            'default_seo_description' => trim($_POST['default_seo_description'] ?? ''),
            'default_keywords' => trim($_POST['default_keywords'] ?? 'sargodha classifieds, sargodha buy sell, sillanwali kinnow, shaheenabad mandi, sargodha jobs'),
            'og_title' => trim($_POST['og_title'] ?? 'SargodhaMart - Buy • Sell • Jobs • Grow'),
            'og_description' => trim($_POST['og_description'] ?? ''),
            'og_image_url' => trim($_POST['og_image_url'] ?? ''),
            'twitter_title' => trim($_POST['twitter_title'] ?? ''),
            'twitter_description' => trim($_POST['twitter_description'] ?? ''),
            'twitter_image_url' => trim($_POST['twitter_image_url'] ?? ''),
        ];
        $saveKeyVal($seoData, 'seo');
        logAdminAction($admin['id'], 'update_settings', 'site_settings', 0, 'Updated SEO & OpenGraph Settings');
        redirect('/admin/settings.php?tab=seo', 'SEO and OpenGraph metadata configuration saved!', 'success');
    }

    // 6. HOMEPAGE SETTINGS
    if ($action === 'save_homepage') {
        $homepageData = [
            'hero_heading' => trim($_POST['hero_heading'] ?? 'Buy • Sell • Jobs • Grow'),
            'hero_heading_highlight' => trim($_POST['hero_heading_highlight'] ?? 'Your Local Online Marketplace'),
            'hero_description' => trim($_POST['hero_description'] ?? ''),
            'hero_button_text' => trim($_POST['hero_button_text'] ?? 'Find Products & Jobs'),
            'hero_banner_image_url' => trim($_POST['hero_banner_image_url'] ?? ''),
            'is_featured_products_enabled' => isset($_POST['is_featured_products_enabled']) ? '1' : '0',
            'is_latest_products_enabled' => isset($_POST['is_latest_products_enabled']) ? '1' : '0',
            'is_jobs_section_enabled' => isset($_POST['is_jobs_section_enabled']) ? '1' : '0',
            'is_categories_section_enabled' => isset($_POST['is_categories_section_enabled']) ? '1' : '0',
            'is_workflow_guide_enabled' => isset($_POST['is_workflow_guide_enabled']) ? '1' : '0',
            'whatsapp_cta_text' => trim($_POST['whatsapp_cta_text'] ?? 'Join 5,000+ local citizens on our WhatsApp Channel for instant verified deals!'),
        ];
        $saveKeyVal($homepageData, 'homepage');
        logAdminAction($admin['id'], 'update_settings', 'site_settings', 0, 'Updated Homepage Elements Configuration');
        redirect('/admin/settings.php?tab=homepage', 'Homepage configuration updated!', 'success');
    }

    // 7. MARKETPLACE SETTINGS
    if ($action === 'save_marketplace') {
        $marketplaceData = [
            'max_images_per_listing' => (string)max(1, min(15, (int)($_POST['max_images_per_listing'] ?? 8))),
            'allow_price_negotiable' => isset($_POST['allow_price_negotiable']) ? '1' : '0',
            'default_listing_duration_days' => (string)max(7, min(365, (int)($_POST['default_listing_duration_days'] ?? 90))),
            'listing_rules_text' => trim($_POST['listing_rules_text'] ?? ''),
            'report_reasons' => trim($_POST['report_reasons'] ?? "Scam / Fraud\nFake product\nWrong price or info\nDuplicate\nProhibited item\nOffensive content\nOther"),
        ];
        $saveKeyVal($marketplaceData, 'marketplace');
        logAdminAction($admin['id'], 'update_settings', 'site_settings', 0, 'Updated Marketplace Rules');
        redirect('/admin/settings.php?tab=marketplace', 'Marketplace rules and safeguards saved!', 'success');
    }

    // 8. JOBS SETTINGS
    if ($action === 'save_jobs') {
        $jobsData = [
            'is_jobs_enabled' => isset($_POST['is_jobs_enabled']) ? '1' : '0',
            'is_jobs_section_enabled' => isset($_POST['is_jobs_enabled']) ? '1' : '0',
            'allow_need_job' => isset($_POST['allow_need_job']) ? '1' : '0',
            'allow_need_worker' => isset($_POST['allow_need_worker']) ? '1' : '0',
            'job_rules_text' => trim($_POST['job_rules_text'] ?? ''),
            'job_salary_guidance' => trim($_POST['job_salary_guidance'] ?? 'Daily: Rs. 1,000 - 3,500 | Monthly: Rs. 25,000 - 85,000'),
            'job_seo_pattern' => trim($_POST['job_seo_pattern'] ?? '{title} – {city} | SargodhaMart Jobs'),
            'job_published_msg' => trim($_POST['job_published_msg'] ?? 'Your job vacancy / worker requirement is now directly published!'),
        ];
        $saveKeyVal($jobsData, 'jobs');
        logAdminAction($admin['id'], 'update_settings', 'site_settings', 0, 'Updated Jobs Portal Settings');
        redirect('/admin/settings.php?tab=jobs', 'Jobs portal configuration saved!', 'success');
    }

    // 9. SELLER ACTIVATION SETTINGS
    if ($action === 'save_activation') {
        $activationFee = (string)max(0, (int)($_POST['activation_fee'] ?? 1000));
        $activationData = [
            'activation_fee' => $activationFee,
            'seller_activation_fee' => $activationFee,
            'payment_account_title' => trim($_POST['payment_account_title'] ?? 'Muhammad Akram Tayyab'),
            'payment_account_name' => trim($_POST['payment_account_title'] ?? 'Muhammad Akram Tayyab'),
            'easypaisa_number' => trim($_POST['easypaisa_number'] ?? '03127453108'),
            'jazzcash_number' => trim($_POST['jazzcash_number'] ?? '03127453108'),
            'payment_number' => trim($_POST['easypaisa_number'] ?? '03127453108'),
            'payment_bank_name' => trim($_POST['payment_bank_name'] ?? 'EasyPaisa / JazzCash'),
            'bank_details' => trim($_POST['bank_details'] ?? 'Bank of Punjab (BOP) - Sargodha Main Branch | A/C: 03127453108'),
            'payment_instructions' => trim($_POST['payment_instructions'] ?? 'Transfer Rs. 1,000 ONE TIME ONLY to 03127453108 (Muhammad Akram Tayyab). Upload payment screenshot and official WhatsApp channel follow screenshot.'),
            'transaction_id_instructions' => trim($_POST['transaction_id_instructions'] ?? 'Enter the 10-12 digit TRX ID from your payment confirmation SMS.'),
            'is_payment_screenshot_required' => isset($_POST['is_payment_screenshot_required']) ? '1' : '0',
            'is_whatsapp_follow_screenshot_required' => isset($_POST['is_whatsapp_follow_screenshot_required']) ? '1' : '0',
            'activation_under_review_notice' => trim($_POST['activation_under_review_notice'] ?? 'Your proofs are under review by the SargodhaMart verification team. Approvals are typically completed within 15-30 minutes.'),
        ];
        $saveKeyVal($activationData, 'activation');
        logAdminAction($admin['id'], 'update_settings', 'site_settings', 0, 'Updated Lifetime Seller Activation Settings');
        redirect('/admin/settings.php?tab=activation', 'Seller activation and payment gateway settings saved!', 'success');
    }

    // 10. NOTIFICATIONS SETTINGS
    if ($action === 'save_notifications') {
        $notificationsData = [
            'registration_success_msg' => trim($_POST['registration_success_msg'] ?? 'Account created! Please complete Rs. 1,000 activation & WhatsApp follow to start posting.'),
            'activation_submitted_msg' => trim($_POST['activation_submitted_msg'] ?? 'Payment & WhatsApp follow proofs submitted! Awaiting admin verification.'),
            'activation_approved_msg' => trim($_POST['activation_approved_msg'] ?? 'Account ACTIVATED! You can now post unlimited free products & jobs directly.'),
            'activation_rejected_msg' => trim($_POST['activation_rejected_msg'] ?? 'Activation request rejected. Please verify your transfer details or contact WhatsApp support.'),
            'listing_published_msg' => trim($_POST['listing_published_msg'] ?? 'Your product is now LIVE directly in SargodhaMart!'),
            'job_published_msg' => trim($_POST['job_published_msg'] ?? 'Your job requirement / work post is now directly published!'),
        ];
        $saveKeyVal($notificationsData, 'notifications');
        logAdminAction($admin['id'], 'update_settings', 'site_settings', 0, 'Updated System Messages & Notifications');
        redirect('/admin/settings.php?tab=notifications', 'Notification messages updated!', 'success');
    }

    // 11. MAINTENANCE SETTINGS
    if ($action === 'save_maintenance') {
        $maintenanceData = [
            'is_maintenance_mode' => isset($_POST['is_maintenance_mode']) ? '1' : '0',
            'maintenance_title' => trim($_POST['maintenance_title'] ?? 'SargodhaMart Scheduled System Upgrade'),
            'maintenance_message' => trim($_POST['maintenance_message'] ?? 'We are performing routine maintenance to improve your marketplace experience. We will be back online shortly. Authorized administrators can still access the portal.'),
            'is_new_registration_enabled' => isset($_POST['is_new_registration_enabled']) ? '1' : '0',
            'is_new_listing_enabled' => isset($_POST['is_new_listing_enabled']) ? '1' : '0',
            'is_job_posting_enabled' => isset($_POST['is_job_posting_enabled']) ? '1' : '0',
        ];
        $saveKeyVal($maintenanceData, 'maintenance');
        logAdminAction($admin['id'], 'update_settings', 'site_settings', 0, 'Updated Maintenance & Access Mode');
        redirect('/admin/settings.php?tab=maintenance', 'Maintenance mode configuration saved!', 'success');
    }

    // 12. TELEGRAM AUTOMATION SETTINGS
    if ($action === 'save_telegram') {
        $telegramData = [
            'telegram_enabled' => isset($_POST['telegram_enabled']) ? '1' : '0',
            'telegram_channel_id' => trim($_POST['telegram_channel_id'] ?? '-1003328935535'),
            'telegram_auto_publish_products' => isset($_POST['telegram_auto_publish_products']) ? '1' : '0',
            'telegram_auto_publish_jobs' => isset($_POST['telegram_auto_publish_jobs']) ? '1' : '0',
            'telegram_auto_publish_announcements' => isset($_POST['telegram_auto_publish_announcements']) ? '1' : '0',
        ];
        $saveKeyVal($telegramData, 'telegram');

        $newToken = trim($_POST['telegram_bot_token'] ?? '');
        if (!empty($newToken) && $newToken !== '••••••••••••••••') {
            $stmt = $db->prepare("
                INSERT INTO site_settings (setting_key, setting_value, setting_group) 
                VALUES ('telegram_bot_token', :tok, 'telegram') 
                ON DUPLICATE KEY UPDATE setting_value = :tok2, updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([':tok' => $newToken, ':tok2' => $newToken]);
        }

        logAdminAction($admin['id'], 'update_settings', 'site_settings', 0, 'Updated Telegram Bot Automation Settings');
        redirect('/admin/settings.php?tab=telegram', 'Telegram automation settings updated successfully!', 'success');
    }

    // 13. RESEND EMAIL INTEGRATION SETTINGS
    if ($action === 'save_email_settings') {
        $emailData = [
            'email_notifications_enabled' => isset($_POST['email_notifications_enabled']) ? '1' : '0',
            'email_sender_name' => trim($_POST['email_sender_name'] ?? 'SargodhaMart'),
            'email_sender_email' => trim($_POST['email_sender_email'] ?? 'noreply@sargodhamart.com'),
            'email_admin_notification_email' => trim($_POST['email_admin_notification_email'] ?? 'admin@sargodhamart.com'),
            'notify_admin_on_activation' => isset($_POST['notify_admin_on_activation']) ? '1' : '0',
            'notify_admin_on_listing' => isset($_POST['notify_admin_on_listing']) ? '1' : '0',
            'notify_admin_on_report' => isset($_POST['notify_admin_on_report']) ? '1' : '0',
        ];
        $saveKeyVal($emailData, 'email');

        // Optional API key override from Admin UI if admin wants to store in DB
        $newApiKey = trim($_POST['resend_api_key_override'] ?? '');
        if (!empty($newApiKey) && !str_contains($newApiKey, '••••')) {
            $stmt = $db->prepare("
                INSERT INTO site_settings (setting_key, setting_value, setting_group) 
                VALUES ('resend_api_key', :k, 'email') 
                ON DUPLICATE KEY UPDATE setting_value = :k2, updated_at = CURRENT_TIMESTAMP
            ");
            $stmt->execute([':k' => $newApiKey, ':k2' => $newApiKey]);
        }

        logAdminAction($admin['id'], 'update_settings', 'site_settings', 0, 'Updated Resend Email Integration Settings');
        redirect('/admin/settings.php?tab=email', 'Resend Email settings saved successfully!', 'success');
    }

    // 14. SEND RESEND TEST EMAIL
    if ($action === 'send_test_email') {
        $testTo = trim($_POST['test_email_recipient'] ?? '');
        if (empty($testTo) || !filter_var($testTo, FILTER_VALIDATE_EMAIL)) {
            redirect('/admin/settings.php?tab=email', 'Please provide a valid recipient email address for testing.', 'danger');
        }

        if (!class_exists('ResendMailer') || !ResendMailer::isConfigured()) {
            redirect('/admin/settings.php?tab=email', 'Resend is not configured yet. Please configure the RESEND_API_KEY environment variable or save the key below.', 'warning');
        }

        $result = ResendMailer::sendTestEmail($testTo);
        if ($result['success']) {
            redirect('/admin/settings.php?tab=email', "Test email successfully sent to {$testTo} via Resend! (Resend ID: {$result['id']})", 'success');
        } else {
            redirect('/admin/settings.php?tab=email', "Resend Test Failed: " . htmlspecialchars($result['error'] ?? 'Unknown error'), 'danger');
        }
    }
}

// -------------------------------------------------------------
// Fetch Fresh Settings & Related Records
// -------------------------------------------------------------
$allSettings = [];
try {
    $allSettings = $db->query("SELECT setting_key, setting_value FROM site_settings")->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
} catch (Exception $e) {}

// Announcements list
$announcementsList = [];
try {
    $announcementsList = $db->query("SELECT * FROM announcements ORDER BY is_highlighted DESC, id DESC")->fetchAll() ?: [];
} catch (Exception $e) {}
?>

<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3 mb-4">
    <div>
        <div class="d-flex align-items-center gap-2">
            <h3 class="fw-bold mb-0 text-dark">Central Website Settings System</h3>
            <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 small">
                Live Configuration
            </span>
        </div>
        <p class="text-muted small mb-0 mt-1">
            Manage all website settings, numbers, links, announcements, SEO, and maintenance without modifying PHP source code.
        </p>
    </div>
    <div class="d-flex align-items-center gap-2">
        <a href="/index.php" target="_blank" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-box-arrow-up-right me-1"></i> Preview Live Website
        </a>
    </div>
</div>

<!-- 11-Tab Settings Navigation (Clean & Responsive) -->
<div class="card border-0 shadow-sm rounded-3 mb-4 bg-white overflow-hidden">
    <div class="card-body p-2">
        <div class="d-flex flex-wrap gap-1">
            <a href="/admin/settings.php?tab=general" class="btn btn-sm <?= $activeTab === 'general' ? 'btn-success fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-globe me-1"></i> General
            </a>
            <a href="/admin/settings.php?tab=whatsapp" class="btn btn-sm <?= $activeTab === 'whatsapp' ? 'btn-success fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-whatsapp me-1"></i> WhatsApp & Contact
            </a>
            <a href="/admin/settings.php?tab=announcements" class="btn btn-sm <?= $activeTab === 'announcements' ? 'btn-success fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-megaphone me-1"></i> Announcements <span class="badge bg-dark ms-1"><?= count($announcementsList) ?></span>
            </a>
            <a href="/admin/settings.php?tab=social" class="btn btn-sm <?= $activeTab === 'social' ? 'btn-success fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-share me-1"></i> Social Media
            </a>
            <a href="/admin/settings.php?tab=seo" class="btn btn-sm <?= $activeTab === 'seo' ? 'btn-success fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-search me-1"></i> SEO
            </a>
            <a href="/admin/settings.php?tab=homepage" class="btn btn-sm <?= $activeTab === 'homepage' ? 'btn-success fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-house-door me-1"></i> Homepage
            </a>
            <a href="/admin/settings.php?tab=marketplace" class="btn btn-sm <?= $activeTab === 'marketplace' ? 'btn-success fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-shop me-1"></i> Marketplace
            </a>
            <a href="/admin/settings.php?tab=jobs" class="btn btn-sm <?= $activeTab === 'jobs' ? 'btn-success fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-briefcase me-1"></i> Jobs
            </a>
            <a href="/admin/settings.php?tab=activation" class="btn btn-sm <?= $activeTab === 'activation' ? 'btn-success fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-shield-check me-1"></i> Seller Activation
            </a>
            <a href="/admin/settings.php?tab=notifications" class="btn btn-sm <?= $activeTab === 'notifications' ? 'btn-success fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-bell me-1"></i> Notifications
            </a>
            <a href="/admin/settings.php?tab=maintenance" class="btn btn-sm <?= $activeTab === 'maintenance' ? 'btn-danger fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-tools me-1"></i> Maintenance
            </a>
            <a href="/admin/settings.php?tab=telegram" class="btn btn-sm <?= $activeTab === 'telegram' ? 'btn-info fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-send-check me-1"></i> Telegram Bot
            </a>
            <a href="/admin/settings.php?tab=email" class="btn btn-sm <?= $activeTab === 'email' ? 'btn-success fw-bold text-white shadow-sm' : 'btn-light text-secondary' ?> rounded-pill px-3 py-2">
                <i class="bi bi-envelope-check me-1"></i> Resend Email
            </a>
        </div>
    </div>
</div>

<!-- ========================================================= -->
<!-- TAB CONTENT CONTAINER -->
<!-- ========================================================= -->

<!-- 1. GENERAL SETTINGS -->
<?php if ($activeTab === 'general'): ?>
<div class="card border-0 shadow-sm bg-white rounded-3 p-4">
    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
        <span class="fs-4 text-success"><i class="bi bi-globe"></i></span>
        <div>
            <h5 class="fw-bold mb-0">1. General Website Settings</h5>
            <small class="text-muted">Configure website identity, branding, contact details, and copyright</small>
        </div>
    </div>

    <form method="POST" action="/admin/settings.php?tab=general">
        <?= getCsrfField() ?>
        <input type="hidden" name="action" value="save_general">

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Website Name *</label>
                <input type="text" name="website_name" class="form-control" value="<?= e($allSettings['website_name'] ?? $allSettings['site_name'] ?? 'SargodhaMart') ?>" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Website Tagline</label>
                <input type="text" name="tagline" class="form-control" value="<?= e($allSettings['tagline'] ?? $allSettings['site_tagline'] ?? 'Buy • Sell • Jobs • Grow') ?>">
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Logo Text</label>
                <input type="text" name="logo_text" class="form-control" value="<?= e($allSettings['logo_text'] ?? 'SARGODHAMART') ?>">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Logo Image URL (Optional)</label>
                <input type="url" name="logo_image_url" class="form-control" placeholder="https://..." value="<?= e($allSettings['logo_image_url'] ?? '') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Favicon URL (Optional)</label>
            <input type="url" name="favicon_url" class="form-control" placeholder="https://..." value="<?= e($allSettings['favicon_url'] ?? '') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Website Description</label>
            <textarea name="website_description" rows="3" class="form-control"><?= e($allSettings['website_description'] ?? 'Premier local marketplace and employment hub for Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp trading with verified lifetime seller activation.') ?></textarea>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-4">
                <label class="form-label small fw-bold">Contact Email *</label>
                <input type="email" name="contact_email" class="form-control" value="<?= e($allSettings['contact_email'] ?? 'support@sargodhamart.com') ?>" required>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label small fw-bold">Official Phone Number *</label>
                <input type="text" name="official_phone" class="form-control" value="<?= e($allSettings['official_phone'] ?? '03127453108') ?>" required>
            </div>
            <div class="col-12 col-md-4">
                <label class="form-label small fw-bold">Official WhatsApp Number *</label>
                <input type="text" name="official_whatsapp" class="form-control" value="<?= e($allSettings['official_whatsapp'] ?? '03127453108') ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Official Physical Address</label>
            <input type="text" name="official_address" class="form-control" value="<?= e($allSettings['official_address'] ?? 'Trust Plaza / Club Road, Sargodha, Punjab, Pakistan') ?>">
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">Footer Copyright Text</label>
            <input type="text" name="copyright_text" class="form-control" value="<?= e($allSettings['copyright_text'] ?? '© 2026 SARGODHAMART. All rights reserved.') ?>">
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success px-4 py-2 fw-bold">
                <i class="bi bi-save me-1"></i> Save General Settings
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- 2. WHATSAPP & CONTACT SETTINGS -->
<?php if ($activeTab === 'whatsapp'): ?>
<div class="card border-0 shadow-sm bg-white rounded-3 p-4">
    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
        <span class="fs-4 text-success"><i class="bi bi-whatsapp"></i></span>
        <div>
            <h5 class="fw-bold mb-0">2. WhatsApp & Contact Settings</h5>
            <small class="text-muted">Manage official WhatsApp Channel, Groups, helpline numbers, and button labels</small>
        </div>
    </div>

    <form method="POST" action="/admin/settings.php?tab=whatsapp">
        <?= getCsrfField() ?>
        <input type="hidden" name="action" value="save_whatsapp">

        <!-- ON/OFF Controls -->
        <div class="p-3 bg-light rounded-3 mb-4 border">
            <h6 class="fw-bold text-dark mb-2">Feature Toggles (ON/OFF)</h6>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_whatsapp_channel_enabled" id="switchCh" <?= ($allSettings['is_whatsapp_channel_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="switchCh">WhatsApp Channel</label>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_whatsapp_group_enabled" id="switchGrp" <?= ($allSettings['is_whatsapp_group_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="switchGrp">WhatsApp Group</label>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_whatsapp_contact_enabled" id="switchWa" <?= ($allSettings['is_whatsapp_contact_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="switchWa">WhatsApp Contact</label>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_call_button_enabled" id="switchCall" <?= ($allSettings['is_call_button_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="switchCall">Call Button</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Official WhatsApp Channel URL *</label>
                <input type="url" name="whatsapp_channel_url" class="form-control" value="<?= e($allSettings['whatsapp_channel_url'] ?? 'https://whatsapp.com/channel/0029Vb8bmhAGk1Fzze6iTX0g') ?>" required>
                <small class="text-muted" style="font-size: 0.72rem;">Followed by sellers during account activation.</small>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Official WhatsApp Group URL</label>
                <input type="url" name="whatsapp_group_url" class="form-control" value="<?= e($allSettings['whatsapp_group_url'] ?? 'https://chat.whatsapp.com/sample-sargodha-community') ?>">
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Official WhatsApp Number (For Direct Chats) *</label>
                <input type="text" name="official_whatsapp_number" class="form-control" value="<?= e($allSettings['official_whatsapp_number'] ?? '03127453108') ?>" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Official Call Helpline Number *</label>
                <input type="text" name="official_call_number" class="form-control" value="<?= e($allSettings['official_call_number'] ?? '03127453108') ?>" required>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">WhatsApp Channel Display Name</label>
                <input type="text" name="whatsapp_channel_name" class="form-control" value="<?= e($allSettings['whatsapp_channel_name'] ?? 'SargodhaMart Official Channel') ?>">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">WhatsApp Group Display Name</label>
                <input type="text" name="whatsapp_group_name" class="form-control" value="<?= e($allSettings['whatsapp_group_name'] ?? 'Sargodha Local Community Group') ?>">
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold">WhatsApp Button Text</label>
                <input type="text" name="whatsapp_btn_text" class="form-control" value="<?= e($allSettings['whatsapp_btn_text'] ?? 'WhatsApp') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold">Call Button Text</label>
                <input type="text" name="call_btn_text" class="form-control" value="<?= e($allSettings['call_btn_text'] ?? 'Call') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold">Follow Channel Button Text</label>
                <input type="text" name="follow_channel_btn_text" class="form-control" value="<?= e($allSettings['follow_channel_btn_text'] ?? 'Follow Official Channel') ?>">
            </div>
            <div class="col-6 col-md-3">
                <label class="form-label small fw-bold">Join Group Button Text</label>
                <input type="text" name="join_group_btn_text" class="form-control" value="<?= e($allSettings['join_group_btn_text'] ?? 'Join WhatsApp Group') ?>">
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success px-4 py-2 fw-bold">
                <i class="bi bi-save me-1"></i> Save WhatsApp & Contact Settings
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- 3. ANNOUNCEMENT SYSTEM -->
<?php if ($activeTab === 'announcements'): ?>
<div class="card border-0 shadow-sm bg-white rounded-3 p-4 mb-4">
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <span class="fs-4 text-warning"><i class="bi bi-megaphone"></i></span>
            <div>
                <h5 class="fw-bold mb-0">3. Announcements System</h5>
                <small class="text-muted">Broadcast notices to visitors via Top Bar, Homepage Banner, or Popup Modal</small>
            </div>
        </div>
        <button class="btn btn-success btn-sm fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#newAnnForm">
            <i class="bi bi-plus-circle me-1"></i> Add Announcement
        </button>
    </div>

    <!-- Create Form (Collapse) -->
    <div class="collapse mb-4" id="newAnnForm">
        <div class="card card-body bg-light border-success p-4 rounded-3">
            <h6 class="fw-bold text-success mb-3"><i class="bi bi-plus-lg me-1"></i> Create New Broadcast Notice</h6>
            <form method="POST" action="/admin/settings.php?tab=announcements">
                <?= getCsrfField() ?>
                <input type="hidden" name="action" value="create_announcement">

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-8">
                        <label class="form-label small fw-bold">Announcement Title *</label>
                        <input type="text" name="ann_title" class="form-control" placeholder="e.g. 📢 Official WhatsApp Channel Now Live!" required>
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold">Display Placement *</label>
                        <select name="ann_location" class="form-select" required>
                            <option value="top_bar">Top Bar (Sticky Announcement)</option>
                            <option value="homepage_banner">Homepage Hero Banner</option>
                            <option value="popup">Visitor Popup Modal</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold">Announcement Message *</label>
                    <textarea name="ann_message" rows="3" class="form-control" placeholder="Enter broadcast text for visitors..." required></textarea>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold">Button Text (Optional)</label>
                        <input type="text" name="ann_btn_text" class="form-control" placeholder="e.g. Join Channel">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold">Button Link URL (Optional)</label>
                        <input type="url" name="ann_btn_url" class="form-control" placeholder="https://...">
                    </div>
                    <div class="col-12 col-md-4">
                        <label class="form-label small fw-bold">Optional Banner/Image URL</label>
                        <input type="url" name="ann_img_url" class="form-control" placeholder="https://...">
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold">Start Date & Time</label>
                        <input type="datetime-local" name="ann_start" class="form-control">
                    </div>
                    <div class="col-6 col-md-3">
                        <label class="form-label small fw-bold">Expiry Date & Time</label>
                        <input type="datetime-local" name="ann_expiry" class="form-control">
                    </div>
                    <div class="col-6 col-md-3 d-flex align-items-center pt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="ann_highlighted" id="annHi" value="1">
                            <label class="form-check-label small fw-bold" for="annHi">Highlight / Important</label>
                        </div>
                    </div>
                    <div class="col-6 col-md-3 d-flex align-items-center pt-3">
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="ann_active" id="annAc" value="1" checked>
                            <label class="form-check-label small fw-bold text-success" for="annAc">Active Immediately</label>
                        </div>
                    </div>
                </div>

                <div class="text-end">
                    <button type="submit" class="btn btn-success px-4 fw-bold">
                        <i class="bi bi-broadcast me-1"></i> Publish Announcement
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Announcements List -->
    <?php if (empty($announcementsList)): ?>
        <div class="text-center py-5 text-muted">
            <i class="bi bi-bell-slash fs-1 d-block mb-2 text-secondary opacity-50"></i>
            <p class="mb-0">No announcements added yet. Click <strong>Add Announcement</strong> to create one.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light small">
                    <tr>
                        <th>Title & Message</th>
                        <th>Placement</th>
                        <th>Status</th>
                        <th>Action Button</th>
                        <th class="text-end">Controls</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php foreach ($announcementsList as $ann): ?>
                        <tr>
                            <td>
                                <div class="fw-bold text-dark d-flex align-items-center gap-1">
                                    <?php if ($ann['is_highlighted']): ?>
                                        <span class="badge bg-warning text-dark"><i class="bi bi-star-fill"></i> Important</span>
                                    <?php endif; ?>
                                    <span><?= e($ann['title']) ?></span>
                                </div>
                                <div class="text-muted mt-1" style="max-width: 450px;"><?= e($ann['message']) ?></div>
                            </td>
                            <td>
                                <span class="badge bg-secondary">
                                    <?= e(str_replace('_', ' ', strtoupper($ann['display_location']))) ?>
                                </span>
                            </td>
                            <td>
                                <?php if ($ann['is_active']): ?>
                                    <span class="badge bg-success">Active</span>
                                <?php else: ?>
                                    <span class="badge bg-secondary">Inactive</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!empty($ann['button_text']) && !empty($ann['button_url'])): ?>
                                    <a href="<?= e($ann['button_url']) ?>" target="_blank" class="btn btn-outline-dark btn-sm py-0 px-2" style="font-size: 0.75rem;">
                                        <?= e($ann['button_text']) ?> <i class="bi bi-arrow-up-right"></i>
                                    </a>
                                <?php else: ?>
                                    <span class="text-muted">—</span>
                                <?php endif; ?>
                            </td>
                            <td class="text-end">
                                <form method="POST" action="/admin/settings.php?tab=announcements" class="d-inline">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="toggle_announcement">
                                    <input type="hidden" name="announcement_id" value="<?= $ann['id'] ?>">
                                    <button type="submit" class="btn btn-outline-secondary btn-sm py-1 px-2" title="Toggle Active">
                                        <i class="bi bi-power"></i>
                                    </button>
                                </form>
                                <form method="POST" action="/admin/settings.php?tab=announcements" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this announcement?');">
                                    <?= getCsrfField() ?>
                                    <input type="hidden" name="action" value="delete_announcement">
                                    <input type="hidden" name="announcement_id" value="<?= $ann['id'] ?>">
                                    <button type="submit" class="btn btn-outline-danger btn-sm py-1 px-2" title="Delete">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>

<!-- 4. SOCIAL MEDIA SETTINGS -->
<?php if ($activeTab === 'social'): ?>
<div class="card border-0 shadow-sm bg-white rounded-3 p-4">
    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
        <span class="fs-4 text-primary"><i class="bi bi-share"></i></span>
        <div>
            <h5 class="fw-bold mb-0">4. Social Media Channels</h5>
            <small class="text-muted">Manage official social links displayed in header, footer, and sharing cards</small>
        </div>
    </div>

    <form method="POST" action="/admin/settings.php?tab=social">
        <?= getCsrfField() ?>
        <input type="hidden" name="action" value="save_social">

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-bold mb-0"><i class="bi bi-whatsapp text-success me-1"></i> WhatsApp Channel</label>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="is_whatsapp_social_enabled" <?= ($allSettings['is_whatsapp_social_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                    </div>
                </div>
                <input type="url" name="social_whatsapp_channel" class="form-control" value="<?= e($allSettings['social_whatsapp_channel'] ?? $allSettings['whatsapp_channel_url'] ?? 'https://whatsapp.com/channel/0029Vb8bmhAGk1Fzze6iTX0g') ?>" placeholder="https://whatsapp.com/channel/...">
            </div>

            <div class="col-12 col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-bold mb-0"><i class="bi bi-facebook text-primary me-1"></i> Facebook Page</label>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="is_facebook_enabled" <?= ($allSettings['is_facebook_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                    </div>
                </div>
                <input type="url" name="social_facebook" class="form-control" value="<?= e($allSettings['social_facebook'] ?? 'https://facebook.com/sargodhamart') ?>" placeholder="https://facebook.com/...">
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-bold mb-0"><i class="bi bi-instagram text-danger me-1"></i> Instagram Profile</label>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="is_instagram_enabled" <?= ($allSettings['is_instagram_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                    </div>
                </div>
                <input type="url" name="social_instagram" class="form-control" value="<?= e($allSettings['social_instagram'] ?? 'https://instagram.com/sargodhamart') ?>" placeholder="https://instagram.com/...">
            </div>

            <div class="col-12 col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-bold mb-0"><i class="bi bi-youtube text-danger me-1"></i> YouTube Channel</label>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="is_youtube_enabled" <?= ($allSettings['is_youtube_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                    </div>
                </div>
                <input type="url" name="social_youtube" class="form-control" value="<?= e($allSettings['social_youtube'] ?? 'https://youtube.com/@sargodhamart') ?>" placeholder="https://youtube.com/@...">
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <label class="form-label small fw-bold mb-0"><i class="bi bi-tiktok text-dark me-1"></i> TikTok Account</label>
                    <div class="form-check form-switch m-0">
                        <input class="form-check-input" type="checkbox" name="is_tiktok_enabled" <?= ($allSettings['is_tiktok_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                    </div>
                </div>
                <input type="url" name="social_tiktok" class="form-control" value="<?= e($allSettings['social_tiktok'] ?? 'https://tiktok.com/@sargodhamart') ?>" placeholder="https://tiktok.com/@...">
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success px-4 py-2 fw-bold">
                <i class="bi bi-save me-1"></i> Save Social Media Links
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- 5. SEO SETTINGS -->
<?php if ($activeTab === 'seo'): ?>
<div class="card border-0 shadow-sm bg-white rounded-3 p-4">
    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
        <span class="fs-4 text-info"><i class="bi bi-search"></i></span>
        <div>
            <h5 class="fw-bold mb-0">5. SEO & OpenGraph Social Sharing</h5>
            <small class="text-muted">Configure default metadata for Google, WhatsApp, Facebook, and Twitter cards</small>
        </div>
    </div>

    <form method="POST" action="/admin/settings.php?tab=seo">
        <?= getCsrfField() ?>
        <input type="hidden" name="action" value="save_seo">

        <div class="mb-3">
            <label class="form-label small fw-bold">Default Page Title *</label>
            <input type="text" name="default_seo_title" class="form-control" value="<?= e($allSettings['default_seo_title'] ?? 'SargodhaMart - Buy • Sell • Jobs • Grow (Sargodha | Shaheenabad | Sillanwali)') ?>" required>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Default Meta Description *</label>
            <textarea name="default_seo_description" rows="3" class="form-control" required><?= e($allSettings['default_seo_description'] ?? 'Dedicated local online marketplace and employment hub for Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp trading with verified Rs. 1,000 lifetime activation.') ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Keywords (Comma Separated)</label>
            <input type="text" name="default_keywords" class="form-control" value="<?= e($allSettings['default_keywords'] ?? 'sargodha classifieds, sargodha buy sell, sillanwali kinnow, shaheenabad mandi, sargodha jobs, rozgar sargodha') ?>">
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">OpenGraph Social Title</label>
                <input type="text" name="og_title" class="form-control" value="<?= e($allSettings['og_title'] ?? 'SargodhaMart - Buy • Sell • Jobs • Grow') ?>">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">OpenGraph Social Share Image URL</label>
                <input type="url" name="og_image_url" class="form-control" value="<?= e($allSettings['og_image_url'] ?? 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=80') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">OpenGraph Description</label>
            <textarea name="og_description" rows="2" class="form-control"><?= e($allSettings['og_description'] ?? 'Premier local marketplace & employment platform for Sargodha, Shaheenabad, and Sillanwali.') ?></textarea>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Twitter/X Card Title</label>
                <input type="text" name="twitter_title" class="form-control" value="<?= e($allSettings['twitter_title'] ?? 'SargodhaMart - Buy • Sell • Jobs • Grow') ?>">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Twitter/X Share Image URL</label>
                <input type="url" name="twitter_image_url" class="form-control" value="<?= e($allSettings['twitter_image_url'] ?? 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=80') ?>">
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success px-4 py-2 fw-bold">
                <i class="bi bi-save me-1"></i> Save SEO Configuration
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- 6. HOMEPAGE SETTINGS -->
<?php if ($activeTab === 'homepage'): ?>
<div class="card border-0 shadow-sm bg-white rounded-3 p-4">
    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
        <span class="fs-4 text-success"><i class="bi bi-house-door"></i></span>
        <div>
            <h5 class="fw-bold mb-0">6. Homepage Elements & Section Controls</h5>
            <small class="text-muted">Configure hero headings, buttons, and toggle visible sections on the public homepage</small>
        </div>
    </div>

    <form method="POST" action="/admin/settings.php?tab=homepage">
        <?= getCsrfField() ?>
        <input type="hidden" name="action" value="save_homepage">

        <!-- Section Toggles -->
        <div class="p-3 bg-light rounded-3 mb-4 border">
            <h6 class="fw-bold text-dark mb-2">Homepage Sections (ON/OFF)</h6>
            <div class="row g-3">
                <div class="col-6 col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_featured_products_enabled" id="swFeat" <?= ($allSettings['is_featured_products_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="swFeat">Featured Products</label>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_latest_products_enabled" id="swLate" <?= ($allSettings['is_latest_products_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="swLate">Latest Products</label>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_jobs_section_enabled" id="swJob" <?= ($allSettings['is_jobs_section_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="swJob">Jobs Section</label>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_categories_section_enabled" id="swCat" <?= ($allSettings['is_categories_section_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="swCat">Categories Section</label>
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_workflow_guide_enabled" id="swGuide" <?= ($allSettings['is_workflow_guide_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="swGuide">4-Step Guide Bar</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Hero Heading Primary *</label>
                <input type="text" name="hero_heading" class="form-control" value="<?= e($allSettings['hero_heading'] ?? 'Buy • Sell • Jobs • Grow') ?>" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Hero Highlight Gradient Text</label>
                <input type="text" name="hero_heading_highlight" class="form-control" value="<?= e($allSettings['hero_heading_highlight'] ?? 'Your Local Online Marketplace') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Hero Subtitle / Description</label>
            <textarea name="hero_description" rows="3" class="form-control"><?= e($allSettings['hero_description'] ?? 'The premier trading & employment portal for Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp dealing, verified Rs. 1,000 lifetime seller activation, zero middleman fees.') ?></textarea>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Hero Button Text</label>
                <input type="text" name="hero_button_text" class="form-control" value="<?= e($allSettings['hero_button_text'] ?? 'Find Products & Jobs') ?>">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Hero Banner Background Image URL (Optional)</label>
                <input type="url" name="hero_banner_image_url" class="form-control" value="<?= e($allSettings['hero_banner_image_url'] ?? '') ?>" placeholder="https://...">
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">WhatsApp Callout / CTA Text</label>
            <input type="text" name="whatsapp_cta_text" class="form-control" value="<?= e($allSettings['whatsapp_cta_text'] ?? 'Join 5,000+ local citizens on our WhatsApp Channel for instant verified deals!') ?>">
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success px-4 py-2 fw-bold">
                <i class="bi bi-save me-1"></i> Save Homepage Settings
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- 7. MARKETPLACE SETTINGS -->
<?php if ($activeTab === 'marketplace'): ?>
<div class="card border-0 shadow-sm bg-white rounded-3 p-4">
    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
        <span class="fs-4 text-primary"><i class="bi bi-shop"></i></span>
        <div>
            <h5 class="fw-bold mb-0">7. Marketplace Listing Rules & Safeguards</h5>
            <small class="text-muted">Configure listing parameters, photo limits, durations, and reporting reasons</small>
        </div>
    </div>

    <form method="POST" action="/admin/settings.php?tab=marketplace">
        <?= getCsrfField() ?>
        <input type="hidden" name="action" value="save_marketplace">

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Maximum Images per Product Listing *</label>
                <input type="number" name="max_images_per_listing" min="1" max="15" class="form-control" value="<?= e($allSettings['max_images_per_listing'] ?? '8') ?>" required>
                <small class="text-muted" style="font-size: 0.72rem;">Default recommendation is 3 to 8 photos for mobile performance.</small>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Default Listing Expiry Duration (Days) *</label>
                <input type="number" name="default_listing_duration_days" min="7" max="365" class="form-control" value="<?= e($allSettings['default_listing_duration_days'] ?? '90') ?>" required>
            </div>
        </div>

        <div class="mb-3">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="allow_price_negotiable" id="swNeg" <?= ($allSettings['allow_price_negotiable'] ?? '1') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label small fw-bold" for="swNeg">Allow "Negotiable" badge on product prices</label>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Marketplace Terms & Rules Notice</label>
            <textarea name="listing_rules_text" rows="3" class="form-control"><?= e($allSettings['listing_rules_text'] ?? 'Only genuine products from Sargodha, Shaheenabad, and Sillanwali. No weapons, counterfeit currency, or prohibited goods. Every ad must have authentic local contact.') ?></textarea>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">Report Reasons (One Per Line)</label>
            <textarea name="report_reasons" rows="4" class="form-control"><?= e($allSettings['report_reasons'] ?? "Scam / Fraud\nFake product\nWrong price or info\nDuplicate\nProhibited item\nOffensive content\nOther") ?></textarea>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success px-4 py-2 fw-bold">
                <i class="bi bi-save me-1"></i> Save Marketplace Rules
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- 8. JOBS SETTINGS -->
<?php if ($activeTab === 'jobs'): ?>
<div class="card border-0 shadow-sm bg-white rounded-3 p-4">
    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
        <span class="fs-4 text-primary"><i class="bi bi-briefcase"></i></span>
        <div>
            <h5 class="fw-bold mb-0">8. Jobs & Employment Hub Settings (Rozgar Portal)</h5>
            <small class="text-muted">Configure worker employment, employer recruitment, and dynamic Job SEO</small>
        </div>
    </div>

    <form method="POST" action="/admin/settings.php?tab=jobs">
        <?= getCsrfField() ?>
        <input type="hidden" name="action" value="save_jobs">

        <!-- Toggles -->
        <div class="p-3 bg-light rounded-3 mb-4 border">
            <h6 class="fw-bold text-dark mb-2">Job Post Types (ON/OFF)</h6>
            <div class="row g-3">
                <div class="col-12 col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_jobs_enabled" id="swJbActive" <?= ($allSettings['is_jobs_enabled'] ?? $allSettings['is_jobs_section_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="swJbActive">Enable Jobs Module</label>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="allow_need_job" id="swNeedJb" <?= ($allSettings['allow_need_job'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="swNeedJb">Allow "Seeking Work" (Need Job)</label>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="allow_need_worker" id="swNeedWrk" <?= ($allSettings['allow_need_worker'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="swNeedWrk">Allow "Hiring" (Need Worker)</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Wage & Salary Guidance Placeholder</label>
                <input type="text" name="job_salary_guidance" class="form-control" value="<?= e($allSettings['job_salary_guidance'] ?? 'Daily: Rs. 1,000 - 3,500 | Monthly: Rs. 25,000 - 85,000') ?>">
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Dynamic Job SEO Title Pattern</label>
                <input type="text" name="job_seo_pattern" class="form-control font-monospace" value="<?= e($allSettings['job_seo_pattern'] ?? '{title} – {city} | SargodhaMart Jobs') ?>">
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Job Posting Rules & Safety Guidelines</label>
            <textarea name="job_rules_text" rows="3" class="form-control"><?= e($allSettings['job_rules_text'] ?? 'Only genuine local employment opportunities across Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp contact only. No illegal recruitment fees.') ?></textarea>
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">Job Post Published Message</label>
            <input type="text" name="job_published_msg" class="form-control" value="<?= e($allSettings['job_published_msg'] ?? 'Your job vacancy / worker requirement is now directly published!') ?>">
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success px-4 py-2 fw-bold">
                <i class="bi bi-save me-1"></i> Save Jobs Configuration
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- 9. SELLER ACTIVATION SETTINGS -->
<?php if ($activeTab === 'activation'): ?>
<div class="card border-0 shadow-sm bg-white rounded-3 p-4">
    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
        <span class="fs-4 text-success"><i class="bi bi-shield-check"></i></span>
        <div>
            <h5 class="fw-bold mb-0">9. Lifetime Seller Activation & Payment Gateways</h5>
            <small class="text-muted">Manage one-time activation fee, receiving mobile accounts, and proof verification rules</small>
        </div>
    </div>

    <form method="POST" action="/admin/settings.php?tab=activation">
        <?= getCsrfField() ?>
        <input type="hidden" name="action" value="save_activation">

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">One-Time Activation Fee (PKR) *</label>
                <div class="input-group">
                    <span class="input-group-text">Rs.</span>
                    <input type="number" name="activation_fee" class="form-control fw-bold" value="<?= e($allSettings['activation_fee'] ?? $allSettings['seller_activation_fee'] ?? '1000') ?>" required>
                </div>
                <small class="text-muted" style="font-size: 0.72rem;">User pays once for lifetime unlimited free listing & job privileges.</small>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Official Account Title / Receiver Name *</label>
                <input type="text" name="payment_account_title" class="form-control" value="<?= e($allSettings['payment_account_title'] ?? $allSettings['payment_account_name'] ?? 'Muhammad Akram Tayyab') ?>" required>
            </div>
        </div>

        <div class="row g-3 mb-3">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">EasyPaisa Account Number *</label>
                <input type="text" name="easypaisa_number" class="form-control font-monospace fw-bold" value="<?= e($allSettings['easypaisa_number'] ?? $allSettings['payment_number'] ?? '03127453108') ?>" required>
            </div>
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">JazzCash Account Number *</label>
                <input type="text" name="jazzcash_number" class="form-control font-monospace fw-bold" value="<?= e($allSettings['jazzcash_number'] ?? '03127453108') ?>" required>
            </div>
        </div>

        <div class="p-3 bg-light rounded-3 mb-3 border">
            <h6 class="fw-bold text-dark mb-2">Screenshot Proof Requirements (ON/OFF)</h6>
            <div class="row g-3">
                <div class="col-12 col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_payment_screenshot_required" id="swPayProof" <?= ($allSettings['is_payment_screenshot_required'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="swPayProof">Require Rs. 1,000 Payment Receipt Screenshot</label>
                    </div>
                </div>
                <div class="col-12 col-md-6">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="is_whatsapp_follow_screenshot_required" id="swWaProof" <?= ($allSettings['is_whatsapp_follow_screenshot_required'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="swWaProof">Require WhatsApp Channel Follow Screenshot</label>
                    </div>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Bank Transfer Details (Alternative)</label>
            <input type="text" name="bank_details" class="form-control" value="<?= e($allSettings['bank_details'] ?? 'Bank of Punjab (BOP) - Sargodha Main Branch | A/C: 03127453108') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Payment Instructions Shown to Sellers</label>
            <textarea name="payment_instructions" rows="3" class="form-control"><?= e($allSettings['payment_instructions'] ?? 'Transfer Rs. 1,000 ONE TIME ONLY to 03127453108 (Muhammad Akram Tayyab) via EasyPaisa or JazzCash. Upload your transfer receipt and WhatsApp Channel follow screenshot.') ?></textarea>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">TRX ID Help Instructions</label>
            <input type="text" name="transaction_id_instructions" class="form-control" value="<?= e($allSettings['transaction_id_instructions'] ?? 'Enter the 10-12 digit TRX ID from your payment confirmation SMS.') ?>">
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">Under Review Notice (Shown to Users while Awaiting Approval)</label>
            <textarea name="activation_under_review_notice" rows="2" class="form-control"><?= e($allSettings['activation_under_review_notice'] ?? 'Your proofs are under review by the SargodhaMart verification team. Approvals are typically completed within 15-30 minutes.') ?></textarea>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success px-4 py-2 fw-bold">
                <i class="bi bi-save me-1"></i> Save Activation Settings
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- 10. NOTIFICATION & MESSAGE SETTINGS -->
<?php if ($activeTab === 'notifications'): ?>
<div class="card border-0 shadow-sm bg-white rounded-3 p-4">
    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
        <span class="fs-4 text-warning"><i class="bi bi-bell"></i></span>
        <div>
            <h5 class="fw-bold mb-0">10. Notification & Toast Message Texts</h5>
            <small class="text-muted">Customize user alert toasts and system feedback dialogs</small>
        </div>
    </div>

    <form method="POST" action="/admin/settings.php?tab=notifications">
        <?= getCsrfField() ?>
        <input type="hidden" name="action" value="save_notifications">

        <div class="mb-3">
            <label class="form-label small fw-bold">Registration Success Message</label>
            <input type="text" name="registration_success_msg" class="form-control" value="<?= e($allSettings['registration_success_msg'] ?? 'Account created! Please complete Rs. 1,000 activation & WhatsApp follow to start posting.') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Activation Proofs Submitted Message</label>
            <input type="text" name="activation_submitted_msg" class="form-control" value="<?= e($allSettings['activation_submitted_msg'] ?? 'Payment & WhatsApp follow proofs submitted! Awaiting admin verification.') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Activation Approved Notification</label>
            <input type="text" name="activation_approved_msg" class="form-control" value="<?= e($allSettings['activation_approved_msg'] ?? 'Account ACTIVATED! You can now post unlimited free products & jobs directly.') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Activation Rejected Notification</label>
            <input type="text" name="activation_rejected_msg" class="form-control" value="<?= e($allSettings['activation_rejected_msg'] ?? 'Activation request rejected. Please verify your transfer details or contact WhatsApp support.') ?>">
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Listing Approved / Published Message</label>
            <input type="text" name="listing_published_msg" class="form-control" value="<?= e($allSettings['listing_published_msg'] ?? 'Your product is now LIVE directly in SargodhaMart!') ?>">
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">Job Post Published Message</label>
            <input type="text" name="job_published_msg" class="form-control" value="<?= e($allSettings['job_published_msg'] ?? 'Your job vacancy / worker requirement is now directly published!') ?>">
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-success px-4 py-2 fw-bold">
                <i class="bi bi-save me-1"></i> Save Notification Messages
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- 11. MAINTENANCE SETTINGS -->
<?php if ($activeTab === 'maintenance'): ?>
<div class="card border-0 shadow-sm bg-white rounded-3 p-4">
    <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
        <span class="fs-4 text-danger"><i class="bi bi-tools"></i></span>
        <div>
            <h5 class="fw-bold mb-0 text-danger">11. Emergency Maintenance & Portal Access Controls</h5>
            <small class="text-muted">Control public marketplace downtime while keeping the Admin Panel accessible</small>
        </div>
    </div>

    <form method="POST" action="/admin/settings.php?tab=maintenance">
        <?= getCsrfField() ?>
        <input type="hidden" name="action" value="save_maintenance">

        <!-- Critical Toggle -->
        <div class="p-3 bg-danger-subtle rounded-3 mb-4 border border-danger-subtle">
            <div class="form-check form-switch">
                <input class="form-check-input" type="checkbox" name="is_maintenance_mode" id="swMaint" <?= ($allSettings['is_maintenance_mode'] ?? '0') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label fw-bold text-danger" for="swMaint">
                    Enable System Maintenance Mode
                </label>
            </div>
            <small class="text-danger d-block mt-1">
                When active, normal public visitors are presented with the maintenance downtime page. Authorized administrators can still log in and use the Admin Panel normally.
            </small>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_new_registration_enabled" id="swReg" <?= ($allSettings['is_new_registration_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label small fw-bold" for="swReg">Allow New User Registrations</label>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_new_listing_enabled" id="swList" <?= ($allSettings['is_new_listing_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label small fw-bold" for="swList">Allow New Product Submissions</label>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" name="is_job_posting_enabled" id="swJobPost" <?= ($allSettings['is_job_posting_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                    <label class="form-check-label small fw-bold" for="swJobPost">Allow New Job Submissions</label>
                </div>
            </div>
        </div>

        <div class="mb-3">
            <label class="form-label small fw-bold">Maintenance Page Title</label>
            <input type="text" name="maintenance_title" class="form-control" value="<?= e($allSettings['maintenance_title'] ?? 'SargodhaMart Scheduled System Upgrade') ?>">
        </div>

        <div class="mb-4">
            <label class="form-label small fw-bold">Maintenance Notice Displayed to Public</label>
            <textarea name="maintenance_message" rows="3" class="form-control"><?= e($allSettings['maintenance_message'] ?? 'We are performing routine maintenance to improve your marketplace experience. We will be back online shortly. Authorized administrators can still access the portal.') ?></textarea>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-danger px-4 py-2 fw-bold">
                <i class="bi bi-save me-1"></i> Save Maintenance Settings
            </button>
        </div>
    </form>
</div>
<?php endif; ?>

<!-- 12. TELEGRAM AUTOMATION SETTINGS -->
<?php if ($activeTab === 'telegram'): 
    $tgTokenEnv = getenv('TELEGRAM_BOT_TOKEN');
    $tgTokenDb = $allSettings['telegram_bot_token'] ?? '';
    $hasServerTgToken = (!empty($tgTokenEnv) && $tgTokenEnv !== 'YOUR_TELEGRAM_BOT_TOKEN') || !empty($tgTokenDb);
    $tgLogs = [];
    try {
        $tgLogs = $db->query("SELECT * FROM telegram_publication_logs ORDER BY id DESC LIMIT 20")->fetchAll() ?: [];
    } catch (Exception $e) {}
?>
<div class="card border-0 shadow-sm bg-white rounded-3 p-4">
    <div class="d-flex align-items-center justify-content-between mb-4 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <span class="fs-4 text-info"><i class="bi bi-send-check"></i></span>
            <div>
                <h5 class="fw-bold mb-0">12. Telegram Bot Automation & Channel Broadcast</h5>
                <small class="text-muted">Automate instant deal & job broadcasting to verified Telegram Channel <code>-1003328935535</code></small>
            </div>
        </div>
        <span class="badge <?= ($allSettings['telegram_enabled'] ?? '0') === '1' ? 'bg-success' : 'bg-secondary' ?> px-3 py-2">
            <?= ($allSettings['telegram_enabled'] ?? '0') === '1' ? 'Active' : 'Disabled' ?>
        </span>
    </div>

    <!-- Security Information Box -->
    <div class="alert alert-dark border-0 p-3 mb-4 rounded-3 shadow-sm d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
        <div>
            <div class="d-flex align-items-center gap-2 mb-1">
                <i class="bi bi-shield-lock-fill text-warning fs-5"></i>
                <strong class="text-white">Server-Side Secret Token Protection</strong>
                <span class="badge bg-success-subtle text-success border border-success-subtle small">Zero Client Exposure</span>
            </div>
            <small class="text-secondary">
                The Telegram Bot Token is stored strictly server-side (as environment variable <code>TELEGRAM_BOT_TOKEN</code> or secure database entry). It is never sent in HTML or browser client code.
            </small>
        </div>
        <div>
            <?php if ($hasServerTgToken): ?>
                <span class="badge bg-success px-3 py-2"><i class="bi bi-check-circle me-1"></i> Token Active on Server</span>
            <?php else: ?>
                <span class="badge bg-warning text-dark px-3 py-2"><i class="bi bi-exclamation-triangle me-1"></i> No Token Configured</span>
            <?php endif; ?>
        </div>
    </div>

    <form method="POST" action="/admin/settings.php?tab=telegram">
        <?= getCsrfField() ?>
        <input type="hidden" name="action" value="save_telegram">

        <!-- Master Switch -->
        <div class="p-3 bg-light rounded-3 border mb-4">
            <div class="form-check form-switch mb-1">
                <input class="form-check-input" type="checkbox" name="telegram_enabled" id="tgEnabled" <?= ($allSettings['telegram_enabled'] ?? '0') === '1' ? 'checked' : '' ?>>
                <label class="form-check-label fw-bold" for="tgEnabled">Enable Telegram Channel Automation</label>
            </div>
            <small class="text-muted">When enabled, approved products, jobs, and announcements can be published to your channel with interactive buttons.</small>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Telegram Channel ID *</label>
                <input type="text" name="telegram_channel_id" class="form-control font-monospace" value="<?= e($allSettings['telegram_channel_id'] ?? '-1003328935535') ?>" required>
                <small class="text-muted">Target Channel ID. Ensure your bot is added as an <strong>Administrator</strong> with post messages permission.</small>
            </div>

            <div class="col-12 col-md-6">
                <label class="form-label small fw-bold">Update Telegram Bot Token (from @BotFather)</label>
                <input type="password" name="telegram_bot_token" class="form-control font-monospace" placeholder="<?= $hasServerTgToken ? '•••••••••••••••• (Leave blank to keep active)' : 'e.g. 7123456789:ABCDef...' ?>">
                <small class="text-muted">Saved securely on server. Token value is never echoed back to the browser.</small>
            </div>
        </div>

        <div class="p-3 bg-light rounded-3 border mb-4">
            <h6 class="fw-bold mb-2 small text-uppercase text-secondary">Automatic Publishing Triggers</h6>
            <div class="row g-2">
                <div class="col-12 col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="telegram_auto_publish_products" id="tgProd" <?= ($allSettings['telegram_auto_publish_products'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="tgProd">Auto-publish Approved Products</label>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="telegram_auto_publish_jobs" id="tgJobs" <?= ($allSettings['telegram_auto_publish_jobs'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="tgJobs">Auto-publish Approved Jobs</label>
                    </div>
                </div>
                <div class="col-12 col-md-4">
                    <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" name="telegram_auto_publish_announcements" id="tgAnn" <?= ($allSettings['telegram_auto_publish_announcements'] ?? '1') === '1' ? 'checked' : '' ?>>
                        <label class="form-check-label small fw-bold" for="tgAnn">Auto-publish Active Announcements</label>
                    </div>
                </div>
            </div>
            <div class="mt-2 text-muted small">
                <i class="bi bi-info-circle text-primary me-1"></i> Safety guarantee: Pending, rejected, or unapproved content will NEVER be broadcasted.
            </div>
        </div>

        <!-- Interactive Testing Card -->
        <div class="card border border-info-subtle p-3 mb-4 rounded-3 bg-white shadow-xs">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
                <div>
                    <h6 class="fw-bold mb-1"><i class="bi bi-broadcast text-info me-1"></i> Interactive Test Tools</h6>
                    <small class="text-muted">Test your Bot API token and send a real test broadcast message to channel <code>-1003328935535</code>.</small>
                </div>
                <div class="d-flex gap-2">
                    <button type="button" id="btnTestTgConn" class="btn btn-outline-secondary btn-sm fw-bold">
                        <i class="bi bi-arrow-repeat me-1"></i> Test Connection
                    </button>
                    <button type="button" id="btnSendTgTest" class="btn btn-info btn-sm text-white fw-bold">
                        <i class="bi bi-send me-1"></i> Send Test Message
                    </button>
                </div>
            </div>
            <div id="tgTestResult" class="mt-3 d-none"></div>
        </div>

        <div class="text-end mb-4">
            <button type="submit" class="btn btn-info text-white px-4 py-2 fw-bold">
                <i class="bi bi-save me-1"></i> Save Telegram Settings
            </button>
        </div>
    </form>

    <!-- Recent Publication Logs Table -->
    <div class="mt-4 pt-3 border-top">
        <div class="d-flex align-items-center justify-content-between mb-3">
            <h6 class="fw-bold mb-0"><i class="bi bi-journal-text me-1"></i> Recent Telegram Publication Logs</h6>
            <small class="text-muted">Duplicate prevention: items record message ID to prevent double posting</small>
        </div>

        <div class="table-responsive">
            <table class="table table-hover table-sm align-middle small mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Type</th>
                        <th>Content ID</th>
                        <th>Channel</th>
                        <th>Status</th>
                        <th>Message ID / Error</th>
                        <th>Date & Time</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($tgLogs)): ?>
                        <tr>
                            <td colspan="7" class="text-center text-muted py-3">No publications recorded yet.</td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($tgLogs as $log): ?>
                            <tr>
                                <td><?= (int)$log['id'] ?></td>
                                <td><span class="badge bg-secondary"><?= e(ucfirst($log['content_type'])) ?></span></td>
                                <td>#<?= (int)$log['content_id'] ?></td>
                                <td><code><?= e($log['channel_id']) ?></code></td>
                                <td>
                                    <span class="badge <?= $log['status'] === 'success' ? 'bg-success' : 'bg-danger' ?>">
                                        <?= e($log['status']) ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($log['message_id']): ?>
                                        <code class="text-success font-monospace">Msg #<?= e($log['message_id']) ?></code>
                                    <?php else: ?>
                                        <span class="text-danger"><?= e($log['error_message'] ?? 'Failed') ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?= e($log['published_at']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const resBox = document.getElementById('tgTestResult');
    const btnTest = document.getElementById('btnTestTgConn');
    const btnSend = document.getElementById('btnSendTgTest');

    if (btnTest) {
        btnTest.addEventListener('click', async function() {
            btnTest.disabled = true;
            btnTest.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Testing...';
            resBox.className = 'mt-3 alert alert-info py-2 small';
            resBox.textContent = 'Contacting Telegram API getMe...';
            resBox.classList.remove('d-none');

            try {
                const res = await fetch('/api/telegram.php?action=test_connection');
                const data = await res.json();
                if (data.success) {
                    resBox.className = 'mt-3 alert alert-success py-2 small';
                    resBox.innerHTML = '<strong>Success!</strong> ' + (data.message || 'Bot is operational.');
                } else {
                    resBox.className = 'mt-3 alert alert-danger py-2 small';
                    resBox.innerHTML = '<strong>Error:</strong> ' + (data.error || 'Failed to verify bot.');
                }
            } catch (err) {
                resBox.className = 'mt-3 alert alert-danger py-2 small';
                resBox.textContent = 'Network error contacting Telegram API endpoint.';
            } finally {
                btnTest.disabled = false;
                btnTest.innerHTML = '<i class="bi bi-arrow-repeat me-1"></i> Test Connection';
            }
        });
    }

    if (btnSend) {
        btnSend.addEventListener('click', async function() {
            btnSend.disabled = true;
            btnSend.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Sending...';
            resBox.className = 'mt-3 alert alert-info py-2 small';
            resBox.textContent = 'Sending test broadcast to channel -1003328935535...';
            resBox.classList.remove('d-none');

            try {
                const res = await fetch('/api/telegram.php?action=send_test', { method: 'POST' });
                const data = await res.json();
                if (data.success) {
                    resBox.className = 'mt-3 alert alert-success py-2 small';
                    resBox.innerHTML = '<strong>Delivered!</strong> ' + (data.message || 'Test broadcast delivered.');
                } else {
                    resBox.className = 'mt-3 alert alert-danger py-2 small';
                    resBox.innerHTML = '<strong>Failed:</strong> ' + (data.error || 'Could not deliver test broadcast.');
                }
            } catch (err) {
                resBox.className = 'mt-3 alert alert-danger py-2 small';
                resBox.textContent = 'Network error contacting server.';
            } finally {
                btnSend.disabled = false;
                btnSend.innerHTML = '<i class="bi bi-send me-1"></i> Send Test Message';
            }
        });
    }
});
</script>
<?php endif; ?>

<!-- 13. RESEND EMAIL SETTINGS -->
<?php if ($activeTab === 'email'): 
    $isResendConfigured = class_exists('ResendMailer') && ResendMailer::isConfigured();
    $maskedKey = class_exists('ResendMailer') ? ResendMailer::getMaskedApiKey() : 'Not Available';
    $senderAddress = class_exists('ResendMailer') ? ResendMailer::getSender() : 'SargodhaMart <noreply@sargodhamart.com>';
    $adminEmail = class_exists('ResendMailer') ? ResendMailer::getAdminEmail() : 'admin@sargodhamart.com';
    $emailLogs = class_exists('ResendMailer') ? ResendMailer::getRecentLogs(25) : [];
?>
<div class="space-y-4">
    <!-- Status Header Banner -->
    <div class="card border-0 shadow-sm bg-white rounded-3 p-4 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-3">
            <div class="d-flex align-items-center gap-3">
                <div class="bg-success-subtle text-success p-3 rounded-3 fs-3">
                    <i class="bi bi-envelope-paper-heart"></i>
                </div>
                <div>
                    <div class="d-flex align-items-center gap-2">
                        <h5 class="fw-bold mb-0">Resend Transactional Email Service</h5>
                        <?php if ($isResendConfigured): ?>
                            <span class="badge bg-success"><i class="bi bi-check-circle-fill me-1"></i> Configured & Ready</span>
                        <?php else: ?>
                            <span class="badge bg-warning text-dark"><i class="bi bi-exclamation-triangle-fill me-1"></i> Not Configured</span>
                        <?php endif; ?>
                    </div>
                    <small class="text-muted">
                        Official HTTP API integration for user registration, seller activation workflows, listing moderation, and password resets.
                    </small>
                </div>
            </div>
            <div>
                <span class="badge bg-light text-secondary border px-3 py-2">
                    <i class="bi bi-shield-lock me-1"></i> API Key: <code class="text-dark fw-bold"><?= e($maskedKey) ?></code>
                </span>
            </div>
        </div>
    </div>

    <!-- Row: Settings Form & Quick Test Dispatcher -->
    <div class="row g-4 mb-4">
        <!-- Configuration Form -->
        <div class="col-12 col-lg-8">
            <div class="card border-0 shadow-sm bg-white rounded-3 p-4 h-100">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <span class="fs-5 text-success"><i class="bi bi-sliders"></i></span>
                    <div>
                        <h6 class="fw-bold mb-0">Email Configuration & Sender Identities</h6>
                        <small class="text-muted">Configure verified sending domain, sender name, and admin notification routing</small>
                    </div>
                </div>

                <form method="POST" action="/admin/settings.php?tab=email">
                    <?= getCsrfField() ?>
                    <input type="hidden" name="action" value="save_email_settings">

                    <!-- Global Toggle -->
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input" type="checkbox" name="email_notifications_enabled" id="emailNotificationsEnabled" <?= ($allSettings['email_notifications_enabled'] ?? '1') === '1' ? 'checked' : '' ?>>
                            <label class="form-check-label fw-bold small text-dark" for="emailNotificationsEnabled">
                                Master Email Dispatcher: Enable Transactional Email Sending
                            </label>
                        </div>
                        <small class="text-muted d-block mt-1" style="font-size: 0.78rem;">
                            When enabled, transactional emails will be dispatched automatically for registrations, activations, listings, and password resets.
                        </small>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Sender Display Name *</label>
                            <input type="text" name="email_sender_name" class="form-control" value="<?= e($allSettings['email_sender_name'] ?? 'SargodhaMart') ?>" required>
                            <small class="text-muted" style="font-size: 0.74rem;">The name shown in user inboxes (e.g. SargodhaMart).</small>
                        </div>
                        <div class="col-12 col-md-6">
                            <label class="form-label small fw-bold">Sender Email Address *</label>
                            <input type="email" name="email_sender_email" class="form-control" value="<?= e($allSettings['email_sender_email'] ?? 'noreply@sargodhamart.com') ?>" required>
                            <small class="text-muted" style="font-size: 0.74rem;">Must match your verified sending domain in your Resend account.</small>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Admin Notification Recipient Email *</label>
                        <input type="email" name="email_admin_notification_email" class="form-control" value="<?= e($allSettings['email_admin_notification_email'] ?? 'admin@sargodhamart.com') ?>" required>
                        <small class="text-muted" style="font-size: 0.74rem;">Receives alerts for new seller activations, moderation queues, and user reports.</small>
                    </div>

                    <!-- Event Subscriptions -->
                    <div class="p-3 bg-light rounded-3 mb-3 border">
                        <label class="form-label small fw-bold text-dark mb-2">Admin Notification Triggers</label>
                        <div class="row g-2">
                            <div class="col-12 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_admin_on_activation" id="notifyActivation" <?= ($allSettings['notify_admin_on_activation'] ?? '1') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="notifyActivation">New Seller Activation</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_admin_on_listing" id="notifyListing" <?= ($allSettings['notify_admin_on_listing'] ?? '1') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="notifyListing">New Listing Submitted</label>
                                </div>
                            </div>
                            <div class="col-12 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="notify_admin_on_report" id="notifyReport" <?= ($allSettings['notify_admin_on_report'] ?? '1') === '1' ? 'checked' : '' ?>>
                                    <label class="form-check-label small" for="notifyReport">Urgent Content Report</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Secure API Key input -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold">Resend API Key (Optional Database Secret Override)</label>
                        <input type="password" name="resend_api_key_override" class="form-control" placeholder="<?= $isResendConfigured ? '•••••••••••••••••••••••••••••••• (Leave blank to keep existing key)' : 're_1234567890abcdef...' ?>" autocomplete="new-password">
                        <small class="text-muted d-block mt-1" style="font-size: 0.74rem;">
                            Security Note: Storing in <code>RESEND_API_KEY</code> environment variable or <code>.env</code> is recommended. Any key entered here is masked and never exposed to visitors or client-side JavaScript.
                        </small>
                    </div>

                    <div class="d-flex justify-content-end">
                        <button type="submit" class="btn btn-primary-green px-4 fw-bold">
                            <i class="bi bi-check-lg me-1"></i> Save Email Settings
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Test Email Dispatcher -->
        <div class="col-12 col-lg-4">
            <div class="card border-0 shadow-sm bg-white rounded-3 p-4 mb-4">
                <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
                    <span class="fs-5 text-primary"><i class="bi bi-send-check"></i></span>
                    <div>
                        <h6 class="fw-bold mb-0">Test Email Dispatcher</h6>
                        <small class="text-muted">Verify live API delivery via Resend</small>
                    </div>
                </div>

                <p class="small text-muted mb-3">
                    Send an instantaneous test email using the official Resend HTTP API to verify DNS records, sender domain, and deliverability.
                </p>

                <form method="POST" action="/admin/settings.php?tab=email">
                    <?= getCsrfField() ?>
                    <input type="hidden" name="action" value="send_test_email">

                    <div class="mb-3">
                        <label class="form-label small fw-bold">Recipient Email Address</label>
                        <input type="email" name="test_email_recipient" class="form-control" value="<?= e($adminEmail) ?>" required placeholder="you@domain.com">
                    </div>

                    <button type="submit" class="btn btn-outline-primary w-100 fw-bold py-2" <?= !$isResendConfigured ? 'title="Configure API key first"' : '' ?>>
                        <i class="bi bi-envelope-arrow-up me-1"></i> Send Test Email via Resend
                    </button>
                </form>

                <?php if (!$isResendConfigured): ?>
                    <div class="alert alert-warning py-2 px-3 small mt-3 mb-0">
                        <i class="bi bi-info-circle me-1"></i> Configure <code>RESEND_API_KEY</code> to enable live delivery.
                    </div>
                <?php endif; ?>
            </div>

            <!-- Verified Domain Note -->
            <div class="card border-0 shadow-sm bg-light rounded-3 p-3">
                <h6 class="fw-bold small mb-2 text-dark"><i class="bi bi-shield-check text-success me-1"></i> Resend Verified Domain</h6>
                <p class="small text-muted mb-1" style="font-size: 0.78rem;">
                    For reliable inbox delivery, add your domain in the <strong>Resend Dashboard &rarr; Domains</strong> and add the 3 DNS records (DKIM, SPF, DMARC) in your domain DNS manager.
                </p>
                <div class="small fw-semibold text-secondary" style="font-size: 0.74rem;">
                    Free testing domain: <code>onboarding@resend.dev</code> (can send only to your verified Resend account email).
                </div>
            </div>
        </div>
    </div>

    <!-- Email Activity Logs Table -->
    <div class="card border-0 shadow-sm bg-white rounded-3 p-4 mb-4">
        <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center gap-2 mb-3 pb-2 border-bottom">
            <div class="d-flex align-items-center gap-2">
                <span class="fs-5 text-secondary"><i class="bi bi-journal-text"></i></span>
                <div>
                    <h6 class="fw-bold mb-0">Recent Transactional Email Logs</h6>
                    <small class="text-muted">Real-time audit log of emails dispatched through Resend</small>
                </div>
            </div>
            <div>
                <span class="badge bg-light text-secondary border">Showing latest <?= count($emailLogs) ?> records</span>
            </div>
        </div>

        <?php if (empty($emailLogs)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary opacity-50"></i>
                <div class="fw-semibold">No email logs recorded yet</div>
                <small>Send a test email above or perform a user registration to see activity here.</small>
            </div>
        <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover align-middle small mb-0">
                    <thead class="table-light">
                        <tr>
                            <th scope="col" style="width: 60px;">ID</th>
                            <th scope="col">Recipient</th>
                            <th scope="col">Subject</th>
                            <th scope="col">Type</th>
                            <th scope="col">Resend ID</th>
                            <th scope="col">Status</th>
                            <th scope="col">Sent At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($emailLogs as $log): ?>
                            <tr>
                                <td class="text-muted">#<?= (int)$log['id'] ?></td>
                                <td>
                                    <span class="fw-semibold text-dark"><?= e($log['recipient_email']) ?></span>
                                </td>
                                <td>
                                    <span class="text-truncate d-inline-block" style="max-width: 260px;" title="<?= e($log['subject']) ?>">
                                        <?= e($log['subject']) ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border"><?= e($log['email_type']) ?></span>
                                </td>
                                <td>
                                    <?php if (!empty($log['resend_id'])): ?>
                                        <code class="text-primary" style="font-size: 0.72rem;"><?= e(substr($log['resend_id'], 0, 16)) ?>...</code>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($log['status'] === 'sent'): ?>
                                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                                            <i class="bi bi-check2 me-1"></i> Sent
                                        </span>
                                    <?php else: ?>
                                        <span class="badge bg-danger-subtle text-danger border border-danger-subtle" title="<?= e($log['error_message'] ?? '') ?>">
                                            <i class="bi bi-x me-1"></i> Failed
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted" style="font-size: 0.75rem;">
                                    <?= date('M d, H:i', strtotime($log['created_at'])) ?>
                                </td>
                            </tr>
                            <?php if ($log['status'] === 'failed' && !empty($log['error_message'])): ?>
                                <tr class="table-danger border-0">
                                    <td colspan="7" class="py-1 px-3 text-danger" style="font-size: 0.74rem;">
                                        <strong>Error:</strong> <?= e($log['error_message']) ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Hostinger Deployment Guide Accordion -->
    <div class="card border-0 shadow-sm bg-white rounded-3 p-4">
        <div class="d-flex align-items-center gap-2 mb-3 pb-2 border-bottom">
            <span class="fs-5 text-dark"><i class="bi bi-server"></i></span>
            <div>
                <h6 class="fw-bold mb-0">Hostinger & Production Deployment Setup Guide</h6>
                <small class="text-muted">How to configure Resend API Key and verified domain on Hostinger cPanel / hPanel</small>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12 col-md-6">
                <div class="p-3 bg-light rounded-3 h-100 border">
                    <h6 class="fw-bold small text-dark mb-2"><i class="bi bi-key-fill text-warning me-1"></i> 1. Setting Environment Variable on Hostinger</h6>
                    <ol class="small text-muted ps-3 mb-0" style="line-height: 1.6;">
                        <li>Log in to your <strong>Hostinger hPanel</strong> or cPanel.</li>
                        <li>Navigate to <strong>Advanced &rarr; Environment Variables</strong> (or edit your server <code>.env</code> file in root directory).</li>
                        <li>Add a new variable:
                            <div class="bg-dark text-white p-2 rounded mt-1 font-monospace" style="font-size: 0.75rem;">
                                RESEND_API_KEY=re_your_api_key_here
                            </div>
                        </li>
                        <li>Ensure <code>.env</code> permissions are set to <code>600</code> or <code>640</code> so it is not publicly accessible.</li>
                    </ol>
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="p-3 bg-light rounded-3 h-100 border">
                    <h6 class="fw-bold small text-dark mb-2"><i class="bi bi-globe2 text-primary me-1"></i> 2. Domain DNS Verification in Resend</h6>
                    <ol class="small text-muted ps-3 mb-0" style="line-height: 1.6;">
                        <li>In your Resend account, add your domain (e.g. <code>sargodhamart.com</code>).</li>
                        <li>Add the <strong>DKIM (TXT)</strong> record provided by Resend in Hostinger DNS Zone Editor.</li>
                        <li>Add the <strong>SPF (TXT)</strong> record: <code>v=spf1 include:resend.com ~all</code></li>
                        <li>Add the <strong>DMARC (TXT)</strong> record: <code>v=DMARC1; p=none;</code></li>
                        <li>Click <strong>Verify DNS Records</strong> in Resend. Once verified, configure Sender Email above to match.</li>
                    </ol>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/admin_footer.php'; ?>
