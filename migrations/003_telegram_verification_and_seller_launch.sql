-- ====================================================================
-- SARGODHAMART DATABASE MIGRATION: 003_telegram_verification_and_seller_launch.sql
-- Description:
--   1. User Telegram Number & Admin Approval System (PENDING, APPROVED, REJECTED)
--   2. User Telegram Audit Trail & Public Contact Privacy Settings
--   3. Seller Account Activation Launch Deal (FIRST 20 SELLERS FREE Lifetime)
--   4. Telegram Bot Publication Logs & Duplicate Prevention
-- Compatibility: MySQL 5.7+, MySQL 8.0+, MariaDB 10.4+, cPanel, Hostinger
-- Author: SargodhaMart Core Engineering Team
-- Date: 2026-10-05
-- ====================================================================

-- --------------------------------------------------------------------
-- 1. UPGRADE USERS TABLE WITH TELEGRAM & CONTACT PRIVACY FIELDS
-- --------------------------------------------------------------------
-- Use stored procedure or IF NOT EXISTS style check for safe re-runs

SET @dbname = DATABASE();

-- 1.1 Add telegram_number to users if not exists
SET @query = IF(
    EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = @dbname AND table_name = 'users' AND column_name = 'telegram_number'),
    'SELECT "telegram_number already exists" AS status;',
    'ALTER TABLE users ADD COLUMN telegram_number VARCHAR(30) NULL DEFAULT NULL AFTER mobile_number;'
);
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 1.2 Add telegram_verification_status
SET @query = IF(
    EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = @dbname AND table_name = 'users' AND column_name = 'telegram_verification_status'),
    'SELECT "telegram_verification_status already exists" AS status;',
    "ALTER TABLE users ADD COLUMN telegram_verification_status ENUM('NONE', 'PENDING', 'APPROVED', 'REJECTED') NOT NULL DEFAULT 'NONE' AFTER telegram_number;"
);
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 1.3 Add telegram audit timestamps and admin tracking
SET @query = IF(
    EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = @dbname AND table_name = 'users' AND column_name = 'telegram_submitted_at'),
    'SELECT "telegram_submitted_at already exists" AS status;',
    'ALTER TABLE users ADD COLUMN telegram_submitted_at DATETIME NULL DEFAULT NULL AFTER telegram_verification_status;'
);
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @query = IF(
    EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = @dbname AND table_name = 'users' AND column_name = 'telegram_reviewed_by'),
    'SELECT "telegram_reviewed_by already exists" AS status;',
    'ALTER TABLE users ADD COLUMN telegram_reviewed_by INT NULL DEFAULT NULL AFTER telegram_submitted_at;'
);
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @query = IF(
    EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = @dbname AND table_name = 'users' AND column_name = 'telegram_reviewed_at'),
    'SELECT "telegram_reviewed_at already exists" AS status;',
    'ALTER TABLE users ADD COLUMN telegram_reviewed_at DATETIME NULL DEFAULT NULL AFTER telegram_reviewed_by;'
);
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @query = IF(
    EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = @dbname AND table_name = 'users' AND column_name = 'telegram_rejection_reason'),
    'SELECT "telegram_rejection_reason already exists" AS status;',
    'ALTER TABLE users ADD COLUMN telegram_rejection_reason TEXT NULL DEFAULT NULL AFTER telegram_reviewed_at;'
);
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- 1.4 Add contact privacy & telegram visibility
SET @query = IF(
    EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = @dbname AND table_name = 'users' AND column_name = 'telegram_visibility'),
    'SELECT "telegram_visibility already exists" AS status;',
    "ALTER TABLE users ADD COLUMN telegram_visibility ENUM('HIDDEN', 'VISIBLE') NOT NULL DEFAULT 'HIDDEN' AFTER telegram_rejection_reason;"
);
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @query = IF(
    EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = @dbname AND table_name = 'users' AND column_name = 'contact_privacy'),
    'SELECT "contact_privacy already exists" AS status;',
    "ALTER TABLE users ADD COLUMN contact_privacy ENUM('SHOW_ALL', 'WHATSAPP_ONLY', 'CALL_WHATSAPP', 'HIDE_PHONE') NOT NULL DEFAULT 'SHOW_ALL' AFTER telegram_visibility;"
);
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------------------
-- 2. USER TELEGRAM VERIFICATION AUDIT TRAIL TABLE
-- --------------------------------------------------------------------
-- Records every submission, admin approval, admin rejection, and invalidation event.
CREATE TABLE IF NOT EXISTS `user_telegram_verifications` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `user_id` INT NOT NULL,
  `telegram_number` VARCHAR(30) NOT NULL,
  `status` ENUM('PENDING', 'APPROVED', 'REJECTED', 'REVOKED') NOT NULL DEFAULT 'PENDING',
  `action_type` ENUM('SUBMIT', 'APPROVE', 'REJECT', 'REVOKE', 'CHANGE_INVALIDATED') NOT NULL,
  `reviewed_by` INT NULL DEFAULT NULL,
  `reviewed_at` DATETIME NULL DEFAULT NULL,
  `rejection_reason` TEXT NULL DEFAULT NULL,
  `ip_address` VARCHAR(45) NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_utv_user` (`user_id`),
  INDEX `idx_utv_status` (`status`),
  INDEX `idx_utv_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 3. UPGRADE ACTIVATION_PAYMENTS TABLE FOR FIRST 20 SELLERS FREE DEAL
-- --------------------------------------------------------------------
SET @query = IF(
    EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = @dbname AND table_name = 'activation_payments' AND column_name = 'activation_type'),
    'SELECT "activation_type already exists" AS status;',
    "ALTER TABLE activation_payments ADD COLUMN activation_type ENUM('FREE', 'PAID') NOT NULL DEFAULT 'PAID' AFTER amount;"
);
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @query = IF(
    EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = @dbname AND table_name = 'activation_payments' AND column_name = 'is_free_slot'),
    'SELECT "is_free_slot already exists" AS status;',
    'ALTER TABLE activation_payments ADD COLUMN is_free_slot TINYINT(1) NOT NULL DEFAULT 0 AFTER activation_type;'
);
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @query = IF(
    EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = @dbname AND table_name = 'activation_payments' AND column_name = 'rejection_reason'),
    'SELECT "rejection_reason already exists" AS status;',
    'ALTER TABLE activation_payments ADD COLUMN rejection_reason TEXT NULL DEFAULT NULL AFTER admin_note;'
);
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @query = IF(
    EXISTS(SELECT * FROM information_schema.columns WHERE table_schema = @dbname AND table_name = 'activation_payments' AND column_name = 'approved_at'),
    'SELECT "approved_at already exists" AS status;',
    'ALTER TABLE activation_payments ADD COLUMN approved_at DATETIME NULL DEFAULT NULL AFTER verified_at;'
);
PREPARE stmt FROM @query; EXECUTE stmt; DEALLOCATE PREPARE stmt;

-- --------------------------------------------------------------------
-- 4. TELEGRAM PUBLICATION LOGS TABLE (DUPLICATE PREVENTION & AUDIT)
-- --------------------------------------------------------------------
CREATE TABLE IF NOT EXISTS `telegram_publication_logs` (
  `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `content_type` VARCHAR(50) NOT NULL,
  `content_id` INT NOT NULL,
  `channel_id` VARCHAR(100) NOT NULL DEFAULT '-1003328935535',
  `message_id` VARCHAR(50) NOT NULL,
  `status` ENUM('published', 'failed', 'retried') NOT NULL DEFAULT 'published',
  `error_message` TEXT NULL DEFAULT NULL,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  INDEX `idx_tpl_content` (`content_type`, `content_id`),
  INDEX `idx_tpl_channel` (`channel_id`),
  INDEX `idx_tpl_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------------------
-- 5. LAUNCH DEAL SETTINGS INSERTION
-- --------------------------------------------------------------------
INSERT INTO `site_settings` (`setting_key`, `setting_value`, `setting_type`, `setting_group`)
VALUES
  ('free_seller_activation_limit', '20', 'number', 'activation'),
  ('is_free_seller_offer_active', '1', 'boolean', 'activation')
ON DUPLICATE KEY UPDATE `updated_at` = CURRENT_TIMESTAMP;

-- --------------------------------------------------------------------
-- 6. AUDIT LOG INITIALIZATION FOR EXISTING ADMIN ACCOUNT
-- --------------------------------------------------------------------
UPDATE users SET 
  telegram_number = '03127453108',
  telegram_verification_status = 'APPROVED',
  telegram_submitted_at = NOW(),
  telegram_reviewed_at = NOW(),
  telegram_visibility = 'VISIBLE',
  contact_privacy = 'SHOW_ALL'
WHERE role = 'super_admin' AND (telegram_number IS NULL OR telegram_number = '');
