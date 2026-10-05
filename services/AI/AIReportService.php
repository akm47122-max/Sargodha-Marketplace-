<?php
/**
 * SARGODHAMART - AI Report Service
 * Automated Daily & Weekly executive summary reports for administrators.
 */

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/ResendMailer.php';
require_once __DIR__ . '/AITools.php';

class AIReportService
{
    /**
     * Generate structured Daily AI Report.
     */
    public static function generateDailyReport(bool $sendNotifications = false): array
    {
        $daily = AITools::get_daily_summary();
        $date = $daily['date'];

        $recommendations = [];
        if ($daily['activations']['pending_review'] > 0) {
            $recommendations[] = "Review {$daily['activations']['pending_review']} pending seller activation submission(s) in Admin Payments.";
        }
        if ($daily['marketplace']['reports_today'] > 0) {
            $recommendations[] = "Investigate {$daily['marketplace']['reports_today']} newly submitted content report(s) in Admin Reports.";
        }
        if (empty($recommendations)) {
            $recommendations[] = "System activity is nominal. No urgent moderation bottlenecks detected.";
        }

        $reportText = "📊 **SARGODHAMART DAILY AI REPORT — {$date}**\n\n";
        $reportText .= "👤 **Users & Sellers:**\n";
        $reportText .= "• New Member Registrations: {$daily['users']['new_registrations']}\n";
        $reportText .= "• Total Verified Active Sellers: {$daily['users']['total_active_sellers']}\n\n";

        $reportText .= "💳 **Seller Activations (Rs. 1,000 Lifetime):**\n";
        $reportText .= "• Pending Verification: {$daily['activations']['pending_review']}\n";
        $reportText .= "• Approved Today: {$daily['activations']['approved_today']}\n";
        $reportText .= "• Rejected Today: {$daily['activations']['rejected_today']}\n\n";

        $reportText .= "🛍️ **Marketplace Listings:**\n";
        $reportText .= "• New Listings Created: {$daily['marketplace']['new_listings']}\n";
        $reportText .= "• Published Directly: {$daily['marketplace']['published_today']}\n";
        $reportText .= "• Moderation Flags/Reports: {$daily['marketplace']['reports_today']}\n\n";

        $reportText .= "💼 **Jobs & Rozgar Hub:**\n";
        $reportText .= "• New Employment Posts: {$daily['jobs']['total_new_jobs']}\n";
        $reportText .= "• Employer Worker Inquiries: {$daily['jobs']['employer_worker_requests']}\n";
        $reportText .= "• Candidate Job Seekers: {$daily['jobs']['candidate_job_seekers']}\n\n";

        $reportText .= "💡 **Administrative Recommendations:**\n";
        foreach ($recommendations as $rec) {
            $reportText .= "• {$rec}\n";
        }

        // Record run in ai_agent_runs
        self::recordRun('daily_report', $reportText);

        if ($sendNotifications) {
            self::sendReportDispatches("Daily AI Report ({$date})", $reportText);
        }

        return [
            'type' => 'daily',
            'date' => $date,
            'data' => $daily,
            'recommendations' => $recommendations,
            'markdown' => $reportText
        ];
    }

    /**
     * Generate structured Weekly AI Report.
     */
    public static function generateWeeklyReport(bool $sendNotifications = false): array
    {
        $weekly = AITools::get_weekly_summary();
        $date = date('Y-m-d');

        $reportText = "📈 **SARGODHAMART WEEKLY AI REPORT — {$date}**\n\n";
        $reportText .= "• New Users Registered (7 Days): {$weekly['new_users']}\n";
        $reportText .= "• New Listings Added: {$weekly['new_listings']}\n";
        $reportText .= "• New Job Postings: {$weekly['new_jobs']}\n";
        $reportText .= "• Approved Seller Activations: {$weekly['approved_activations']}\n";
        $reportText .= "• Total Community Reports: {$weekly['content_reports']}\n";
        $reportText .= "• Leading Activity Hub: {$weekly['most_active_location']}\n\n";
        $reportText .= "🔍 **Analysis:** Growth in Sargodha Division remains steady with local agriculture, livestock, and electronics trading.";

        self::recordRun('weekly_report', $reportText);

        if ($sendNotifications) {
            self::sendReportDispatches("Weekly AI Report ({$date})", $reportText);
        }

        return [
            'type' => 'weekly',
            'date' => $date,
            'data' => $weekly,
            'markdown' => $reportText
        ];
    }

    /**
     * Log report execution in ai_agent_runs.
     */
    private static function recordRun(string $type, string $summary): void
    {
        try {
            if (!function_exists('getDB')) return;
            $db = getDB();
            $db->exec("
                CREATE TABLE IF NOT EXISTS `ai_agent_runs` (
                    `id` INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    `run_type` VARCHAR(50) NOT NULL,
                    `status` ENUM('running', 'completed', 'failed') NOT NULL DEFAULT 'completed',
                    `started_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `completed_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    `summary` TEXT NULL,
                    `error_message` TEXT NULL
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");

            $stmt = $db->prepare("
                INSERT INTO ai_agent_runs (run_type, status, summary, completed_at)
                VALUES (:typ, 'completed', :sum, NOW())
            ");
            $stmt->execute([':typ' => $type, ':sum' => $summary]);
        } catch (Throwable $e) {}
    }

    /**
     * Dispatch reports to configured email and/or Telegram destinations.
     */
    private static function sendReportDispatches(string $title, string $content): void
    {
        if (!function_exists('getSetting')) return;

        // Email via Resend
        if (getSetting('ai_email_reports_enabled', '0') === '1' && class_exists('ResendMailer') && ResendMailer::isConfigured()) {
            $adminEmail = ResendMailer::getAdminEmail();
            $subject = "[SargodhaMart AI] {$title}";
            $html = "
                <div style='font-family:sans-serif;max-width:600px;margin:auto;'>
                    <h2 style='color:#059669;'>SargodhaMart AI Report</h2>
                    <div style='background:#f8fafc;padding:16px;border-radius:8px;white-space:pre-wrap;font-size:14px;color:#1e293b;border:1px solid #e2e8f0;'>" . htmlspecialchars($content) . "</div>
                    <p style='margin-top:16px;'><a href='" . SITE_URL . "/admin/ai-assistant.php' style='color:#059669;font-weight:bold;'>View in Admin AI Dashboard &rarr;</a></p>
                </div>
            ";
            ResendMailer::sendEmail($adminEmail, $subject, $html, null, 'ai_report');
        }

        // Telegram
        if (getSetting('ai_telegram_reports_enabled', '0') === '1' && function_exists('sendTelegramNotification')) {
            sendTelegramNotification("📊 <b>{$title}</b>\n\n" . strip_tags($content));
        }
    }
}
