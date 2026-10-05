<?php
/**
 * SARGODHAMART - AI Event Logger
 * Event-based architecture for platform activity monitoring and AI alert triggers.
 *
 * PRIVACY GUARANTEE:
 * - Passwords, hashes, tokens, and payment secrets are NEVER logged.
 * - Handles DB failures silently without breaking user transactions.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';

class AIEventLogger
{
    /**
     * Ensure ai_agent_events table exists.
     */
    private static function ensureTable(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        try {
            if (!function_exists('getDB')) return;
            $db = getDB();
            $db->exec("
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
            ");
        } catch (Throwable $e) {
            // Silently ignore if DB is offline
        }
    }

    /**
     * Log a high-value platform event.
     */
    public static function logEvent(
        string $eventType,
        string $entityType,
        ?int $entityId = null,
        ?int $userId = null,
        array $metadata = []
    ): bool {
        try {
            if (!function_exists('getDB')) return false;
            self::ensureTable();

            // Sanitize metadata: remove forbidden keys
            $forbidden = ['password', 'password_hash', 'token', 'reset_token', 'api_key', 'csrf', 'session_id'];
            $cleanMeta = [];
            foreach ($metadata as $k => $v) {
                $low = strtolower($k);
                $isForbidden = false;
                foreach ($forbidden as $f) {
                    if (str_contains($low, $f)) {
                        $isForbidden = true;
                        break;
                    }
                }
                if (!$isForbidden) {
                    // Mask phone numbers if present
                    if (is_string($v) && preg_match('/^03[0-9]{9}$/', $v)) {
                        $v = substr($v, 0, 4) . '****' . substr($v, -3);
                    }
                    $cleanMeta[$k] = $v;
                }
            }

            $db = getDB();
            $stmt = $db->prepare("
                INSERT INTO ai_agent_events (event_type, entity_type, entity_id, user_id, metadata_json, processed, created_at)
                VALUES (:evt, :ent_type, :ent_id, :uid, :meta, 0, NOW())
            ");
            return $stmt->execute([
                ':evt' => $eventType,
                ':ent_type' => $entityType,
                ':ent_id' => $entityId,
                ':uid' => $userId,
                ':meta' => !empty($cleanMeta) ? json_encode($cleanMeta, JSON_UNESCAPED_UNICODE) : null
            ]);
        } catch (Throwable $e) {
            // Fail silently so normal user flow never crashes
            error_log("[AIEventLogger Error] " . $e->getMessage());
            return false;
        }
    }
}
