<?php
/**
 * SARGODHAMART - AI Alert Service
 * Pattern detection, suspicious activity identification, and multi-channel alerting.
 *
 * CRITICAL SAFETY RULES:
 * - Uses cautious language: "This activity may require admin review because..."
 * - Evaluates confidence: LOW / MEDIUM / HIGH.
 * - Never takes punitive or destructive actions automatically.
 * - Respects Admin notification preferences (Email & Telegram toggles).
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/ResendMailer.php';
require_once __DIR__ . '/AITools.php';

class AIAlertService
{
    /**
     * Ensure ai_agent_alerts table exists.
     */
    public static function ensureTable(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        try {
            if (!function_exists('getDB')) return;
            $db = getDB();
            $db->exec("
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
            ");
        } catch (Throwable $e) {}
    }

    /**
     * Create an AI alert with duplicate prevention.
     */
    public static function createAlert(
        string $severity,
        string $title,
        string $message,
        string $reason,
        string $confidence = 'MEDIUM',
        ?string $entityType = null,
        ?int $entityId = null
    ): ?int {
        try {
            self::ensureTable();
            $db = getDB();

            // Avoid duplicate active alert for same entity within last 24h
            if ($entityType && $entityId) {
                $stmt = $db->prepare("
                    SELECT id FROM ai_agent_alerts 
                    WHERE entity_type = :et AND entity_id = :eid AND status = 'pending' AND created_at >= NOW() - INTERVAL 24 HOUR
                    LIMIT 1
                ");
                $stmt->execute([':et' => $entityType, ':eid' => $entityId]);
                if ($stmt->fetch()) {
                    return null; // Duplicate prevented
                }
            }

            $stmt = $db->prepare("
                INSERT INTO ai_agent_alerts 
                (severity, title, message, reason, confidence, entity_type, entity_id, status, created_at)
                VALUES (:sev, :tit, :msg, :rea, :conf, :et, :eid, 'pending', NOW())
            ");
            $stmt->execute([
                ':sev' => strtoupper($severity),
                ':tit' => $title,
                ':msg' => $message,
                ':rea' => $reason,
                ':conf' => strtoupper($confidence),
                ':et' => $entityType,
                ':eid' => $entityId
            ]);

            $alertId = (int)$db->lastInsertId();

            // Check if admin email notifications are enabled
            self::dispatchAlertNotifications($alertId, $severity, $title, $message, $reason);

            return $alertId;
        } catch (Throwable $e) {
            error_log("[AIAlertService Error] " . $e->getMessage());
            return null;
        }
    }

    /**
     * Scan database for pattern anomalies and generate alerts.
     */
    public static function runPatternScan(): array
    {
        self::ensureTable();
        $db = getDB();
        $alertsGenerated = [];

        // 1. Check for listings with multiple pending reports
        $reportQuery = $db->query("
            SELECT r.listing_id, COUNT(*) as report_count, l.title, l.city, l.user_id
            FROM reports r
            JOIN listings l ON r.listing_id = l.id
            WHERE r.status = 'pending'
            GROUP BY r.listing_id
            HAVING report_count >= 2
        ");
        while ($row = $reportQuery->fetch(PDO::FETCH_ASSOC)) {
            $lid = (int)$row['listing_id'];
            $cnt = (int)$row['report_count'];
            $id = self::createAlert(
                'MEDIUM',
                "Listing received multiple user reports: \"{$row['title']}\"",
                "The product listing in {$row['city']} has accumulated {$cnt} unaddressed moderation flags from visitors.",
                "This activity may require admin review because multiple community flags suggest potential policy violation or inaccurate details.",
                'HIGH',
                'listing',
                $lid
            );
            if ($id) $alertsGenerated[] = $id;
        }

        // 2. Check for duplicate transaction IDs across activation payments
        $trxQuery = $db->query("
            SELECT transaction_id, COUNT(*) as trx_count, GROUP_CONCAT(user_id) as user_ids
            FROM activation_payments
            WHERE transaction_id IS NOT NULL AND transaction_id != ''
            GROUP BY transaction_id
            HAVING trx_count > 1
        ");
        while ($row = $trxQuery->fetch(PDO::FETCH_ASSOC)) {
            $trx = $row['transaction_id'];
            $cnt = (int)$row['trx_count'];
            $id = self::createAlert(
                'HIGH',
                "Duplicate payment transaction ID submitted: {$trx}",
                "The TRX ID {$trx} was submitted across {$cnt} different activation payment requests.",
                "This activity may require admin review because a single payment transaction reference cannot be reused for multiple accounts.",
                'HIGH',
                'payment',
                null
            );
            if ($id) $alertsGenerated[] = $id;
        }

        // 3. Check for pending activation requests older than 24 hours
        $oldActivations = (int)$db->query("
            SELECT COUNT(*) FROM activation_payments 
            WHERE status = 'pending' AND created_at <= NOW() - INTERVAL 24 HOUR
        ")->fetchColumn();
        if ($oldActivations > 0) {
            $id = self::createAlert(
                'LOW',
                "{$oldActivations} seller activation request(s) pending over 24 hours",
                "Prospective sellers in Sargodha are awaiting verification to start posting ad listings.",
                "This activity may require admin review to maintain fast onboarding turnaround times.",
                'MEDIUM',
                'activation_queue',
                null
            );
            if ($id) $alertsGenerated[] = $id;
        }

        return $alertsGenerated;
    }

    /**
     * Dispatch alert notifications via Resend and/or Telegram if enabled.
     */
    private static function dispatchAlertNotifications(
        int $alertId,
        string $severity,
        string $title,
        string $message,
        string $reason
    ): void {
        if (!function_exists('getSetting')) return;

        $emailEnabled = getSetting('ai_email_alerts_enabled', '0') === '1';
        $telegramEnabled = getSetting('ai_telegram_alerts_enabled', '0') === '1';

        // Only send MEDIUM, HIGH, or CRITICAL alerts by notification
        if (!in_array($severity, ['MEDIUM', 'HIGH', 'CRITICAL'])) {
            return;
        }

        // Send Email via Resend
        if ($emailEnabled && class_exists('ResendMailer') && ResendMailer::isConfigured()) {
            $adminEmail = ResendMailer::getAdminEmail();
            $subject = "[SargodhaMart AI Alert: {$severity}] {$title}";
            $body = "
                <div style='font-family:sans-serif;max-width:600px;margin:auto;'>
                    <h2 style='color:#0f172a;'>SargodhaMart AI Security & Platform Alert</h2>
                    <div style='background:#fef2f2;border-left:4px solid #ef4444;padding:12px;margin:16px 0;'>
                        <strong style='color:#991b1b;'>Severity: {$severity}</strong><br>
                        <strong>{$title}</strong>
                    </div>
                    <p style='color:#334155;'>{$message}</p>
                    <div style='background:#f8fafc;padding:12px;border-radius:6px;border:1px solid #e2e8f0;'>
                        <strong>AI Observation:</strong> {$reason}
                    </div>
                    <p style='margin-top:20px;'><a href='" . SITE_URL . "/admin/ai-assistant.php' style='background:#059669;color:#fff;padding:8px 16px;text-decoration:none;border-radius:4px;'>Open Admin AI Control</a></p>
                </div>
            ";
            ResendMailer::sendEmail($adminEmail, $subject, $body, null, 'ai_alert');
        }

        // Send Telegram Broadcast if enabled
        if ($telegramEnabled && function_exists('sendTelegramNotification')) {
            $tgText = "🚨 <b>[AI Alert: {$severity}]</b>\n<b>{$title}</b>\n\n{$message}\n\n<i>Observation:</i> {$reason}";
            sendTelegramNotification($tgText);
        }
    }

    /**
     * Get recent alerts for Admin UI.
     */
    public static function getRecentAlerts(int $limit = 20, string $status = 'all'): array
    {
        self::ensureTable();
        $db = getDB();
        $sql = "SELECT * FROM ai_agent_alerts";
        $params = [];

        if ($status !== 'all') {
            $sql .= " WHERE status = :st";
            $params[':st'] = $status;
        }
        $sql .= " ORDER BY (status = 'pending') DESC, id DESC LIMIT {$limit}";

        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
    }

    /**
     * Mark an alert as reviewed or dismissed.
     */
    public static function updateAlertStatus(int $alertId, string $status, int $adminId): bool
    {
        self::ensureTable();
        $db = getDB();
        $stmt = $db->prepare("
            UPDATE ai_agent_alerts 
            SET status = :st, reviewed_at = NOW(), reviewed_by = :aid 
            WHERE id = :id
        ");
        return $stmt->execute([
            ':st' => in_array($status, ['reviewed', 'dismissed']) ? $status : 'reviewed',
            ':aid' => $adminId,
            ':id' => $alertId
        ]);
    }
}
