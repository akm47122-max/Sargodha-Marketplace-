-- ====================================================================
-- SARGODHAMART DATABASE SCHEMA & SAFE MIGRATION SCRIPT
-- Central Website Settings System, Announcements & Social Media
-- Compatible with MySQL 5.7+, MySQL 8.0, MariaDB 10.3+, cPanel, Hostinger
-- ====================================================================

-- 1. CENTRAL SITE SETTINGS TABLE (Key-Value & Grouped)
CREATE TABLE IF NOT EXISTS `site_settings` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `setting_key` VARCHAR(100) NOT NULL UNIQUE,
  `setting_value` TEXT NULL,
  `setting_type` ENUM('string', 'number', 'boolean', 'json', 'text') DEFAULT 'string',
  `setting_group` VARCHAR(50) NOT NULL DEFAULT 'general',
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_setting_group` (`setting_group`),
  INDEX `idx_setting_key` (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. ANNOUNCEMENTS SYSTEM TABLE
CREATE TABLE IF NOT EXISTS `announcements` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `image_url` VARCHAR(500) NULL,
  `button_text` VARCHAR(100) NULL,
  `button_url` VARCHAR(500) NULL,
  `display_location` ENUM('top_bar', 'homepage_banner', 'popup') NOT NULL DEFAULT 'top_bar',
  `is_highlighted` TINYINT(1) NOT NULL DEFAULT 0,
  `is_active` TINYINT(1) NOT NULL DEFAULT 1,
  `start_date` DATETIME NULL,
  `expiry_date` DATETIME NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_active_location` (`is_active`, `display_location`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. SOCIAL MEDIA LINKS TABLE
CREATE TABLE IF NOT EXISTS `social_media_links` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `platform` VARCHAR(50) NOT NULL,
  `title` VARCHAR(100) NOT NULL,
  `url` VARCHAR(500) NOT NULL,
  `is_enabled` TINYINT(1) NOT NULL DEFAULT 1,
  `display_order` INT NOT NULL DEFAULT 0,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. INSERT OR UPDATE DEFAULT SETTINGS (Safe Idempotent Inserts)
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`) VALUES
-- 1. General Settings
('website_name', 'SargodhaMart', 'string', 'general'),
('site_name', 'SargodhaMart', 'string', 'general'),
('logo_text', 'SARGODHAMART', 'string', 'general'),
('logo_image_url', '', 'string', 'general'),
('favicon_url', '', 'string', 'general'),
('tagline', 'Buy • Sell • Jobs • Grow', 'string', 'general'),
('site_tagline', 'Buy • Sell • Jobs • Grow', 'string', 'general'),
('website_description', 'Dedicated local online marketplace and employment hub for Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp trading with verified Rs. 1,000 lifetime seller activation.', 'text', 'general'),
('contact_email', 'support@sargodhamart.com', 'string', 'general'),
('official_phone', '03127453108', 'string', 'general'),
('contact_phone', '03127453108', 'string', 'general'),
('official_whatsapp', '03127453108', 'string', 'general'),
('official_whatsapp_number', '03127453108', 'string', 'general'),
('official_address', 'Trust Plaza / Club Road, Sargodha, Punjab, Pakistan', 'string', 'general'),
('copyright_text', '© 2026 SARGODHAMART. All rights reserved. Production-Ready for Hostinger / cPanel.', 'string', 'general'),

-- 2. WhatsApp & Contact Settings
('whatsapp_channel_url', 'https://whatsapp.com/channel/0029Vb8bmhAGk1Fzze6iTX0g', 'string', 'whatsapp'),
('whatsapp_group_url', 'https://chat.whatsapp.com/sample-sargodha-community', 'string', 'whatsapp'),
('whatsapp_channel_name', 'SargodhaMart Official Channel', 'string', 'whatsapp'),
('whatsapp_group_name', 'Sargodha Local Community Group', 'string', 'whatsapp'),
('whatsapp_btn_text', 'WhatsApp', 'string', 'whatsapp'),
('call_btn_text', 'Call', 'string', 'whatsapp'),
('follow_channel_btn_text', 'Follow Official Channel', 'string', 'whatsapp'),
('join_group_btn_text', 'Join WhatsApp Group', 'string', 'whatsapp'),
('is_whatsapp_channel_enabled', '1', 'boolean', 'whatsapp'),
('is_whatsapp_group_enabled', '1', 'boolean', 'whatsapp'),
('is_whatsapp_contact_enabled', '1', 'boolean', 'whatsapp'),
('is_call_button_enabled', '1', 'boolean', 'whatsapp'),

-- 3. Social Media Settings
('social_facebook', 'https://facebook.com/sargodhamart', 'string', 'social'),
('social_instagram', 'https://instagram.com/sargodhamart', 'string', 'social'),
('social_youtube', 'https://youtube.com/@sargodhamart', 'string', 'social'),
('social_tiktok', 'https://tiktok.com/@sargodhamart', 'string', 'social'),
('is_facebook_enabled', '1', 'boolean', 'social'),
('is_instagram_enabled', '1', 'boolean', 'social'),
('is_youtube_enabled', '1', 'boolean', 'social'),
('is_tiktok_enabled', '1', 'boolean', 'social'),
('is_whatsapp_social_enabled', '1', 'boolean', 'social'),

-- 4. SEO Settings
('default_seo_title', 'SargodhaMart - Buy • Sell • Jobs • Grow (Sargodha | Shaheenabad | Sillanwali)', 'string', 'seo'),
('default_seo_description', 'Dedicated local online marketplace and employment hub for Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp trading with verified Rs. 1,000 lifetime activation.', 'text', 'seo'),
('default_keywords', 'sargodha classifieds, sargodha buy sell, sillanwali kinnow, shaheenabad mandi, sargodha jobs, rozgar sargodha', 'string', 'seo'),
('og_title', 'SargodhaMart - Buy • Sell • Jobs • Grow', 'string', 'seo'),
('og_description', 'Premier local marketplace & employment platform for Sargodha, Shaheenabad, and Sillanwali.', 'text', 'seo'),
('og_image_url', 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=80', 'string', 'seo'),
('twitter_title', 'SargodhaMart - Buy • Sell • Jobs • Grow', 'string', 'seo'),
('twitter_description', 'Premier local marketplace & employment platform for Sargodha, Shaheenabad, and Sillanwali.', 'text', 'seo'),
('twitter_image_url', 'https://images.unsplash.com/photo-1542838132-92c53300491e?auto=format&fit=crop&w=1200&q=80', 'string', 'seo'),

-- 5. Homepage Settings
('hero_heading', 'Buy • Sell • Jobs • Grow', 'string', 'homepage'),
('hero_heading_highlight', 'Your Local Online Marketplace', 'string', 'homepage'),
('hero_description', 'The premier trading & employment portal for Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp dealing, verified Rs. 1,000 lifetime seller activation, zero middleman fees.', 'text', 'homepage'),
('hero_button_text', 'Find Products & Jobs', 'string', 'homepage'),
('hero_banner_image_url', '', 'string', 'homepage'),
('is_featured_products_enabled', '1', 'boolean', 'homepage'),
('is_latest_products_enabled', '1', 'boolean', 'homepage'),
('is_jobs_section_enabled', '1', 'boolean', 'homepage'),
('is_categories_section_enabled', '1', 'boolean', 'homepage'),
('is_workflow_guide_enabled', '1', 'boolean', 'homepage'),
('whatsapp_cta_text', 'Join 5,000+ local citizens on our WhatsApp Channel for instant verified deals!', 'string', 'homepage'),

-- 6. Marketplace Settings
('max_images_per_listing', '8', 'number', 'marketplace'),
('allow_price_negotiable', '1', 'boolean', 'marketplace'),
('default_listing_duration_days', '90', 'number', 'marketplace'),
('listing_rules_text', 'Only genuine products from Sargodha, Shaheenabad, and Sillanwali. No weapons, counterfeit currency, or prohibited goods. Every ad must have authentic local contact.', 'text', 'marketplace'),
('report_reasons', 'Scam / Fraud\nFake product\nWrong price or info\nDuplicate\nProhibited item\nOffensive content\nOther', 'text', 'marketplace'),

-- 7. Jobs Settings
('is_jobs_enabled', '1', 'boolean', 'jobs'),
('allow_need_job', '1', 'boolean', 'jobs'),
('allow_need_worker', '1', 'boolean', 'jobs'),
('job_rules_text', 'Only genuine local employment opportunities across Sargodha, Shaheenabad, and Sillanwali. Direct call & WhatsApp contact only. No illegal recruitment fees.', 'text', 'jobs'),
('job_salary_guidance', 'Daily: Rs. 1,000 - 3,500 | Monthly: Rs. 25,000 - 85,000', 'string', 'jobs'),
('job_seo_pattern', '{title} – {city} | SargodhaMart Jobs', 'string', 'jobs'),
('job_published_msg', 'Your job vacancy / worker requirement is now directly published!', 'string', 'jobs'),

-- 8. Seller Activation Settings
('activation_fee', '1000', 'number', 'activation'),
('seller_activation_fee', '1000', 'number', 'activation'),
('payment_account_title', 'Muhammad Akram Tayyab', 'string', 'activation'),
('payment_account_name', 'Muhammad Akram Tayyab', 'string', 'activation'),
('easypaisa_number', '03127453108', 'string', 'activation'),
('jazzcash_number', '03127453108', 'string', 'activation'),
('payment_number', '03127453108', 'string', 'activation'),
('payment_bank_name', 'EasyPaisa / JazzCash', 'string', 'activation'),
('bank_details', 'Bank of Punjab (BOP) - Sargodha Main Branch | A/C: 03127453108', 'string', 'activation'),
('payment_instructions', 'Transfer Rs. 1,000 ONE TIME ONLY to 03127453108 (Muhammad Akram Tayyab) via EasyPaisa or JazzCash. Upload your transfer receipt and WhatsApp Channel follow screenshot.', 'text', 'activation'),
('transaction_id_instructions', 'Enter the 10-12 digit TRX ID from your payment confirmation SMS.', 'string', 'activation'),
('is_payment_screenshot_required', '1', 'boolean', 'activation'),
('is_whatsapp_follow_screenshot_required', '1', 'boolean', 'activation'),
('activation_under_review_notice', 'Your proofs are under review by the SargodhaMart verification team. Approvals are typically completed within 15-30 minutes.', 'text', 'activation'),

-- 9. Notification Settings
('registration_success_msg', 'Account created! Please complete Rs. 1,000 activation & WhatsApp follow to start posting.', 'string', 'notifications'),
('activation_submitted_msg', 'Payment & WhatsApp follow proofs submitted! Awaiting admin verification.', 'string', 'notifications'),
('activation_approved_msg', 'Account ACTIVATED! You can now post unlimited free products & jobs directly.', 'string', 'notifications'),
('activation_rejected_msg', 'Activation request rejected. Please verify your transfer details or contact WhatsApp support.', 'string', 'notifications'),
('listing_published_msg', 'Your product is now LIVE directly in SargodhaMart!', 'string', 'notifications'),

-- 10. Maintenance Settings
('is_maintenance_mode', '0', 'boolean', 'maintenance'),
('maintenance_title', 'SargodhaMart Scheduled System Upgrade', 'string', 'maintenance'),
('maintenance_message', 'We are performing routine maintenance to improve your marketplace experience. We will be back online shortly. Authorized administrators can still access the portal.', 'text', 'maintenance'),
('is_new_registration_enabled', '1', 'boolean', 'maintenance'),
('is_new_listing_enabled', '1', 'boolean', 'maintenance'),
('is_job_posting_enabled', '1', 'boolean', 'maintenance'),

-- 11. Telegram Automation Settings
('telegram_enabled', '0', 'boolean', 'telegram'),
('telegram_channel_id', '-1003328935535', 'string', 'telegram'),
('telegram_auto_publish_products', '1', 'boolean', 'telegram'),
('telegram_auto_publish_jobs', '1', 'boolean', 'telegram'),
('telegram_auto_publish_announcements', '1', 'boolean', 'telegram'),

-- 12. Resend Transactional Email Settings
('email_notifications_enabled', '1', 'boolean', 'email'),
('email_sender_name', 'SargodhaMart', 'string', 'email'),
('email_sender_email', 'noreply@sargodhamart.com', 'string', 'email'),
('email_admin_notification_email', 'admin@sargodhamart.com', 'string', 'email'),
('notify_admin_on_activation', '1', 'boolean', 'email'),
('notify_admin_on_listing', '1', 'boolean', 'email'),
('notify_admin_on_report', '1', 'boolean', 'email')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- 5. RESEND TRANSACTIONAL EMAIL LOGS TABLE
CREATE TABLE IF NOT EXISTS `email_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `recipient_email` VARCHAR(255) NOT NULL,
  `subject` VARCHAR(255) NOT NULL,
  `email_type` VARCHAR(50) NOT NULL DEFAULT 'general',
  `resend_id` VARCHAR(100) NULL,
  `status` ENUM('sent', 'failed') NOT NULL DEFAULT 'sent',
  `error_message` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_email_recipient` (`recipient_email`),
  INDEX `idx_email_status` (`status`),
  INDEX `idx_email_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. TELEGRAM PUBLICATION LOGS TABLE (For Tracking & Duplicate Prevention)
CREATE TABLE IF NOT EXISTS `telegram_publication_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `content_type` ENUM('product', 'job', 'announcement', 'test') NOT NULL,
  `content_id` INT UNSIGNED DEFAULT NULL,
  `channel_id` VARCHAR(100) NOT NULL DEFAULT '-1003328935535',
  `message_id` BIGINT NULL,
  `status` ENUM('success', 'failed') NOT NULL DEFAULT 'success',
  `error_message` TEXT NULL,
  `published_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_content` (`content_type`, `content_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. INITIAL ANNOUNCEMENTS INSERT
INSERT INTO `announcements` (`id`, `title`, `message`, `button_text`, `button_url`, `display_location`, `is_highlighted`, `is_active`) VALUES
(1, '📢 Official WhatsApp Channel Now Live!', 'Join 5,000+ local citizens across Sargodha, Shaheenabad & Sillanwali for daily updates & alerts.', 'Join Channel', 'https://whatsapp.com/channel/0029Vb8bmhAGk1Fzze6iTX0g', 'top_bar', 1, 1),
(2, '🍊 Citrus & Kinnow Season Opening in Sillanwali Mandi', 'Orchard owners and commission agents can post seasonal fruit deals and labor requirements with zero commission.', 'View Citrus Deals', '/search.php?category=agriculture', 'homepage_banner', 0, 1)
ON DUPLICATE KEY UPDATE `title` = VALUES(`title`);

-- 6. INITIAL SOCIAL MEDIA LINKS INSERT
INSERT INTO `social_media_links` (`id`, `platform`, `title`, `url`, `is_enabled`, `display_order`) VALUES
(1, 'WhatsApp Channel', 'Official Channel', 'https://whatsapp.com/channel/0029Vb8bmhAGk1Fzze6iTX0g', 1, 1),
(2, 'Facebook', 'Facebook Page', 'https://facebook.com/sargodhamart', 1, 2),
(3, 'Instagram', 'Instagram Handle', 'https://instagram.com/sargodhamart', 1, 3),
(4, 'YouTube', 'YouTube Channel', 'https://youtube.com/@sargodhamart', 1, 4),
(5, 'TikTok', 'TikTok Profile', 'https://tiktok.com/@sargodhamart', 1, 5)
ON DUPLICATE KEY UPDATE `url` = VALUES(`url`);

-- 7. SARGODHAMART AI AGENT & INTELLIGENCE TABLES
CREATE TABLE IF NOT EXISTS `ai_agent_events` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `event_type` VARCHAR(50) NOT NULL,
  `entity_type` VARCHAR(50) NOT NULL,
  `entity_id` INT NULL,
  `user_id` INT NULL,
  `metadata_json` TEXT NULL,
  `processed` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_evt_type` (`event_type`),
  INDEX `idx_evt_entity` (`entity_type`, `entity_id`),
  INDEX `idx_evt_user` (`user_id`),
  INDEX `idx_evt_processed` (`processed`),
  INDEX `idx_evt_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_agent_alerts` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `severity` ENUM('INFO', 'LOW', 'MEDIUM', 'HIGH', 'CRITICAL') NOT NULL DEFAULT 'INFO',
  `title` VARCHAR(255) NOT NULL,
  `message` TEXT NOT NULL,
  `reason` TEXT NOT NULL,
  `confidence` ENUM('LOW', 'MEDIUM', 'HIGH') NOT NULL DEFAULT 'MEDIUM',
  `entity_type` VARCHAR(50) NULL,
  `entity_id` INT NULL,
  `status` ENUM('pending', 'reviewed', 'dismissed') NOT NULL DEFAULT 'pending',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `reviewed_at` TIMESTAMP NULL,
  `reviewed_by` INT NULL,
  INDEX `idx_alert_severity` (`severity`),
  INDEX `idx_alert_status` (`status`),
  INDEX `idx_alert_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_agent_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `admin_id` INT NULL,
  `action` VARCHAR(100) NOT NULL,
  `tool_name` VARCHAR(100) NULL,
  `request_summary` TEXT NULL,
  `response_summary` TEXT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_ail_action` (`action`),
  INDEX `idx_ail_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `ai_system_versions` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `version_tag` VARCHAR(50) NOT NULL,
  `description` TEXT NOT NULL,
  `created_by` INT NULL,
  `changes_json` TEXT NULL,
  `is_active` TINYINT(1) NOT NULL DEFAULT 0,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_ver_tag` (`version_tag`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- AI Default Settings
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`) VALUES
('ai_agent_enabled', '1', 'boolean', 'ai'),
('public_ai_enabled', '1', 'boolean', 'ai'),
('ai_alerts_enabled', '1', 'boolean', 'ai'),
('ai_email_alerts_enabled', '0', 'boolean', 'ai'),
('ai_telegram_alerts_enabled', '0', 'boolean', 'ai'),
('ai_daily_report_enabled', '1', 'boolean', 'ai'),
('ai_weekly_report_enabled', '1', 'boolean', 'ai'),
('ai_email_reports_enabled', '0', 'boolean', 'ai'),
('ai_telegram_reports_enabled', '0', 'boolean', 'ai'),
('ai_alert_threshold', 'MEDIUM', 'string', 'ai'),
('re_moderate_edited_listings', '0', 'boolean', 'ai')
ON DUPLICATE KEY UPDATE `setting_value` = VALUES(`setting_value`);

