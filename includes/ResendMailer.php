<?php
/**
 * SARGODHAMART - Resend Transactional Mail Service
 * Production-ready for Hostinger, cPanel, and cloud hosting.
 *
 * CRITICAL SECURITY:
 * - API Key is NEVER hard-coded or exposed in frontend or client payloads.
 * - Sourced strictly from server-side environment variable RESEND_API_KEY.
 * - Handles API failures gracefully without interrupting core DB transactions.
 */

require_once __DIR__ . '/../config/config.php';

class ResendMailer
{
    private const RESEND_API_ENDPOINT = 'https://api.resend.com/emails';

    /**
     * Retrieve the Resend API Key strictly from server-side secrets.
     */
    public static function getApiKey(): ?string
    {
        // 1. Check getenv / $_ENV / $_SERVER
        $key = getenv('RESEND_API_KEY');
        if (!empty($key)) {
            return trim($key);
        }

        if (!empty($_ENV['RESEND_API_KEY'])) {
            return trim($_ENV['RESEND_API_KEY']);
        }

        if (!empty($_SERVER['RESEND_API_KEY'])) {
            return trim($_SERVER['RESEND_API_KEY']);
        }

        // 2. Parse .env file if present in ROOT_PATH or parent directory
        $envPaths = [
            defined('ROOT_PATH') ? ROOT_PATH . '/.env' : null,
            dirname(__DIR__) . '/.env',
            __DIR__ . '/../.env'
        ];

        foreach ($envPaths as $envFile) {
            if ($envFile && file_exists($envFile) && is_readable($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                if ($lines !== false) {
                    foreach ($lines as $line) {
                        $line = trim($line);
                        if (str_starts_with($line, '#')) continue;
                        if (str_starts_with($line, 'RESEND_API_KEY=')) {
                            $val = substr($line, 15);
                            $val = trim($val, " \t\n\r\0\x0B\"'");
                            if (!empty($val)) {
                                return $val;
                            }
                        }
                    }
                }
            }
        }

        // 3. Fallback check in site_settings table if stored by admin
        if (function_exists('getSetting')) {
            $dbKey = getSetting('resend_api_key');
            if (!empty($dbKey)) {
                return trim($dbKey);
            }
        }

        return null;
    }

    /**
     * Check if Resend email integration is configured.
     */
    public static function isConfigured(): bool
    {
        $key = self::getApiKey();
        return !empty($key) && strlen($key) > 8;
    }

    /**
     * Get masked display representation of the API key (e.g. re_1234...9xyz).
     * NEVER returns full secret.
     */
    public static function getMaskedApiKey(): string
    {
        $key = self::getApiKey();
        if (empty($key)) return 'Not Configured';
        $len = strlen($key);
        if ($len <= 8) return '••••••••';
        return substr($key, 0, 5) . '••••••••' . substr($key, -4);
    }

    /**
     * Get configured Sender Address (e.g. "SargodhaMart <noreply@mydomain.com>").
     */
    public static function getSender(): string
    {
        $name = function_exists('getSetting') ? getSetting('email_sender_name', 'SargodhaMart') : 'SargodhaMart';
        $email = function_exists('getSetting') ? getSetting('email_sender_email', 'noreply@sargodhamart.com') : 'noreply@sargodhamart.com';

        if (empty($name)) $name = 'SargodhaMart';
        if (empty($email)) $email = 'onboarding@resend.dev';

        return "{$name} <{$email}>";
    }

    /**
     * Get configured Admin Notification Email.
     */
    public static function getAdminEmail(): string
    {
        if (function_exists('getSetting')) {
            $adminEmail = getSetting('email_admin_notification_email');
            if (!empty($adminEmail) && filter_var($adminEmail, FILTER_VALIDATE_EMAIL)) {
                return trim($adminEmail);
            }
        }

        if (defined('SUPPORT_EMAIL') && filter_var(SUPPORT_EMAIL, FILTER_VALIDATE_EMAIL)) {
            return SUPPORT_EMAIL;
        }

        return 'admin@sargodhamart.com';
    }

    /**
     * Core email dispatch method using Resend official HTTP API via cURL.
     */
    public static function sendEmail(string $to, string $subject, string $htmlContent, ?string $textAlternative = null, string $emailType = 'general'): array
    {
        $to = trim($to);
        if (empty($to) || !filter_var($to, FILTER_VALIDATE_EMAIL)) {
            self::logEmail($to, $subject, $emailType, null, 'failed', 'Invalid recipient email address format.');
            return ['success' => false, 'error' => 'Invalid recipient email address.'];
        }

        $apiKey = self::getApiKey();
        if (empty($apiKey)) {
            $msg = 'Resend API key is not configured. Set RESEND_API_KEY environment variable.';
            self::logEmail($to, $subject, $emailType, null, 'failed', $msg);
            error_log("[ResendMailer] {$msg}");
            return ['success' => false, 'error' => $msg];
        }

        // Global master toggle check
        if (function_exists('getSetting')) {
            $enabled = getSetting('email_notifications_enabled', '1');
            if ($enabled === '0' && $emailType !== 'test') {
                return ['success' => false, 'error' => 'Email notifications are disabled in Admin Settings.'];
            }
        }

        $sender = self::getSender();
        $payload = [
            'from' => $sender,
            'to' => [$to],
            'subject' => $subject,
            'html' => $htmlContent,
            'text' => $textAlternative ?: strip_tags(str_replace(['<br>', '<br/>', '</p>'], "\n", $htmlContent))
        ];

        try {
            $ch = curl_init(self::RESEND_API_ENDPOINT);
            curl_setopt_array($ch, [
                CURLOPT_POST => true,
                CURLOPT_POSTFIELDS => json_encode($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_TIMEOUT => 15,
                CURLOPT_CONNECTTIMEOUT => 8,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $apiKey,
                    'Content-Type: application/json',
                    'User-Agent: SargodhaMart-PHP/1.0'
                ]
            ]);

            $responseBody = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlError = curl_error($ch);
            curl_close($ch);

            if ($curlError) {
                self::logEmail($to, $subject, $emailType, null, 'failed', "cURL Error: {$curlError}");
                return ['success' => false, 'error' => "Network error connecting to Resend: {$curlError}"];
            }

            $decoded = json_decode($responseBody, true) ?: [];

            if ($httpCode >= 200 && $httpCode < 300 && !empty($decoded['id'])) {
                $resendId = $decoded['id'];
                self::logEmail($to, $subject, $emailType, $resendId, 'sent', null);
                return ['success' => true, 'id' => $resendId];
            }

            // Failed response from Resend
            $errorMessage = $decoded['message'] ?? $decoded['error'] ?? "HTTP {$httpCode}: {$responseBody}";
            self::logEmail($to, $subject, $emailType, null, 'failed', $errorMessage);
            error_log("[ResendMailer Error] Failed to send email to {$to}: {$errorMessage}");

            return ['success' => false, 'error' => $errorMessage];

        } catch (Throwable $t) {
            $err = $t->getMessage();
            self::logEmail($to, $subject, $emailType, null, 'failed', "Exception: {$err}");
            error_log("[ResendMailer Exception] {$err}");
            return ['success' => false, 'error' => $err];
        }
    }

    /**
     * Ensure email_logs table exists.
     */
    private static function ensureLogTable(): void
    {
        static $checked = false;
        if ($checked) return;
        $checked = true;

        try {
            if (!function_exists('getDB')) return;
            $db = getDB();
            $db->exec("
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
            ");
        } catch (Throwable $e) {
            // Ignore if DB not ready
        }
    }

    /**
     * Safe internal logger to email_logs table without storing secrets.
     */
    private static function logEmail(string $recipient, string $subject, string $type, ?string $resendId, string $status, ?string $error): void
    {
        try {
            if (!function_exists('getDB')) return;
            self::ensureLogTable();
            $db = getDB();

            $stmt = $db->prepare("
                INSERT INTO email_logs (recipient_email, subject, email_type, resend_id, status, error_message, created_at)
                VALUES (:rec, :sub, :typ, :rid, :stat, :err, NOW())
            ");
            $stmt->execute([
                ':rec' => $recipient,
                ':sub' => mb_substr($subject, 0, 255),
                ':typ' => $type,
                ':rid' => $resendId,
                ':stat' => $status === 'sent' ? 'sent' : 'failed',
                ':err' => $error ? mb_substr($error, 0, 1000) : null
            ]);

            if (file_exists(__DIR__ . '/../services/AI/AIEventLogger.php')) {
                require_once __DIR__ . '/../services/AI/AIEventLogger.php';
                $domain = explode('@', $recipient)[1] ?? 'domain.com';
                AIEventLogger::logEvent('EMAIL_EVENT', 'email', null, null, [
                    'type' => $type,
                    'status' => $status,
                    'recipient' => substr($recipient, 0, 3) . '***@' . $domain
                ]);
            }
        } catch (Throwable $e) {
            // Silently ignore logging failures to not disrupt application
        }
    }

    /**
     * Get recent email logs for Admin Panel.
     */
    public static function getRecentLogs(int $limit = 20): array
    {
        try {
            if (!function_exists('getDB')) return [];
            self::ensureLogTable();
            $db = getDB();
            $stmt = $db->query("SELECT * FROM email_logs ORDER BY id DESC LIMIT {$limit}");
            return $stmt->fetchAll(PDO::FETCH_ASSOC) ?: [];
        } catch (Throwable $e) {
            return [];
        }
    }

    // =========================================================================
    // 1. USER REGISTRATION WELCOME EMAIL
    // =========================================================================
    public static function sendWelcomeEmail(string $to, string $userName): array
    {
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'SargodhaMart';
        $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'http://localhost:3000';
        $subject = "Welcome to {$siteName}, {$userName}! 🎉";

        $html = self::renderTemplate([
            'title' => "Welcome to {$siteName}",
            'badgeText' => "Free Account Ready",
            'badgeType' => "success",
            'greeting' => "Salam, " . htmlspecialchars($userName) . "!",
            'leadMessage' => "Welcome to {$siteName} — the dedicated local online marketplace and employment hub for Sargodha, Shaheenabad, and Sillanwali.",
            'detailRows' => [
                'Account Name' => htmlspecialchars($userName),
                'Registered Email' => htmlspecialchars($to),
                'Membership' => 'Standard Free Member',
                'Posting Privileges' => 'Unlimited free product listings after one-time Rs. 1,000 verification',
            ],
            'actionButton' => [
                'text' => 'Explore SargodhaMart',
                'url' => "{$siteUrl}/index.php"
            ],
            'extraNotice' => "Tip: To start posting products or job advertisements, complete your one-time lifetime seller verification in your account dashboard."
        ]);

        return self::sendEmail($to, $subject, $html, null, 'welcome');
    }

    // =========================================================================
    // 2. SELLER ACTIVATION SUBMISSION EMAIL
    // =========================================================================
    public static function sendActivationPendingEmail(string $to, string $userName, string $transactionId, int|float $amount = 1000, string $method = 'EasyPaisa'): array
    {
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'SargodhaMart';
        $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'http://localhost:3000';
        $subject = "Seller Activation Request Received — Pending Verification (TRX: {$transactionId})";

        $html = self::renderTemplate([
            'title' => "Seller Activation Under Review",
            'badgeText' => "Verification Pending",
            'badgeType' => "warning",
            'greeting' => "Salam, " . htmlspecialchars($userName) . "!",
            'leadMessage' => "Thank you for submitting your Rs. " . number_format($amount) . " one-time lifetime seller activation payment. Our verification team has received your submission and is reviewing your proofs.",
            'detailRows' => [
                'Payment Method' => htmlspecialchars($method),
                'Transaction ID (TRX ID)' => "<code style='color:#0284c7;font-weight:bold;'>" . htmlspecialchars($transactionId) . "</code>",
                'Amount Submitted' => "Rs. " . number_format($amount) . " (One-Time Lifetime)",
                'Review Time' => "Typically 15 to 30 minutes during business hours",
                'Current Status' => "<strong style='color:#d97706;'>Under Admin Review</strong>"
            ],
            'actionButton' => [
                'text' => 'Check Verification Status',
                'url' => "{$siteUrl}/user/dashboard.php"
            ],
            'extraNotice' => "Once approved, your account will be permanently unlocked to post unlimited free product ads and job listings with zero commission."
        ]);

        // Send confirmation to user
        $result = self::sendEmail($to, $subject, $html, null, 'activation_pending');

        // Also notify admin
        self::sendAdminNotificationEmail('new_activation', [
            'userName' => $userName,
            'userEmail' => $to,
            'transactionId' => $transactionId,
            'amount' => $amount,
            'method' => $method
        ]);

        return $result;
    }

    // =========================================================================
    // 3. ADMIN APPROVAL EMAIL
    // =========================================================================
    public static function sendActivationApprovedEmail(string $to, string $userName): array
    {
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'SargodhaMart';
        $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'http://localhost:3000';
        $subject = "Congratulations! Your {$siteName} Seller Account is ACTIVE 🌟";

        $html = self::renderTemplate([
            'title' => "Seller Account Activated!",
            'badgeText' => "ACCOUNT ACTIVE",
            'badgeType' => "success",
            'greeting' => "Congratulations, " . htmlspecialchars($userName) . "!",
            'leadMessage' => "Your Rs. 1,000 one-time seller activation fee has been officially verified and APPROVED by our administrator team.",
            'detailRows' => [
                'Account Status' => "<strong style='color:#059669;'>ACTIVE (Verified Seller)</strong>",
                'Activation Type' => "Lifetime / One-Time Only",
                'Product Postings' => "Unlimited Free Direct Listings",
                'Job & Rozgar Posts' => "Unlimited Free Employment Postings",
                'Commission' => "0% — Direct call & WhatsApp trading"
            ],
            'actionButton' => [
                'text' => 'Post Your First Product Ad Now',
                'url' => "{$siteUrl}/post-ad.php"
            ],
            'extraNotice' => "You will never be asked to pay another activation fee. You can now login anytime to manage your active listings and receive direct calls from local buyers."
        ]);

        return self::sendEmail($to, $subject, $html, null, 'activation_approved');
    }

    // =========================================================================
    // 4. ADMIN REJECTION EMAIL
    // =========================================================================
    public static function sendActivationRejectedEmail(string $to, string $userName, string $rejectionReason = ''): array
    {
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'SargodhaMart';
        $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'http://localhost:3000';
        $subject = "Update Regarding Your {$siteName} Seller Activation Request";

        $reasonText = !empty($rejectionReason) ? htmlspecialchars($rejectionReason) : "Payment transaction details or receipt screenshot could not be confirmed in the official account records.";

        $html = self::renderTemplate([
            'title' => "Activation Details Need Revision",
            'badgeText' => "ACTION REQUIRED",
            'badgeType' => "danger",
            'greeting' => "Salam, " . htmlspecialchars($userName) . ",",
            'leadMessage' => "We were unable to verify your seller activation payment request at this time.",
            'detailRows' => [
                'Review Outcome' => "<strong style='color:#dc2626;'>Verification Unsuccessful</strong>",
                'Rejection Reason' => "<span style='color:#991b1b;font-weight:600;'>{$reasonText}</span>",
                'Next Step' => "Please verify your 10-12 digit TRX ID or upload a clear screenshot of your transfer SMS/receipt."
            ],
            'actionButton' => [
                'text' => 'Resubmit Payment Verification Details',
                'url' => "{$siteUrl}/activate-seller.php"
            ],
            'extraNotice' => "Need assistance? Contact our official Sargodha helpline directly on WhatsApp at 03127453108."
        ]);

        return self::sendEmail($to, $subject, $html, null, 'activation_rejected');
    }

    // =========================================================================
    // 5. LISTING APPROVAL EMAIL
    // =========================================================================
    public static function sendListingApprovedEmail(string $to, string $userName, string $listingTitle, int|string $listingId): array
    {
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'SargodhaMart';
        $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'http://localhost:3000';
        $subject = "Your Product Listing is Now LIVE: {$listingTitle} ✅";

        $html = self::renderTemplate([
            'title' => "Product Listing Published",
            'badgeText' => "LIVE ON MARKETPLACE",
            'badgeType' => "success",
            'greeting' => "Salam, " . htmlspecialchars($userName) . "!",
            'leadMessage' => "Great news! Your product listing has been reviewed by administrators and is now LIVE and searchable across Sargodha Division.",
            'detailRows' => [
                'Listing Title' => "<strong>" . htmlspecialchars($listingTitle) . "</strong>",
                'Status' => "<strong style='color:#059669;'>Published & Active</strong>",
                'Market Visibility' => "Sargodha, Shaheenabad & Sillanwali",
                'Inquiries' => "Direct phone calls and WhatsApp messages"
            ],
            'actionButton' => [
                'text' => 'View Your Live Product Listing',
                'url' => "{$siteUrl}/product.php?id={$listingId}"
            ],
            'extraNotice' => "Keep your phone reachable. Interested local buyers will contact you directly via phone call or WhatsApp."
        ]);

        return self::sendEmail($to, $subject, $html, null, 'listing_approved');
    }

    // =========================================================================
    // 6. LISTING REJECTION EMAIL
    // =========================================================================
    public static function sendListingRejectedEmail(string $to, string $userName, string $listingTitle, string $reason = ''): array
    {
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'SargodhaMart';
        $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'http://localhost:3000';
        $subject = "Notice Regarding Your Listing: {$listingTitle}";

        $reasonText = !empty($reason) ? htmlspecialchars($reason) : "The listing does not comply with our local marketplace posting standards or contains insufficient details/photos.";

        $html = self::renderTemplate([
            'title' => "Listing Requires Modification",
            'badgeText' => "LISTING NOT APPROVED",
            'badgeType' => "warning",
            'greeting' => "Salam, " . htmlspecialchars($userName) . ",",
            'leadMessage' => "Your product listing could not be published on the public marketplace in its current form.",
            'detailRows' => [
                'Listing Title' => "<strong>" . htmlspecialchars($listingTitle) . "</strong>",
                'Moderation Status' => "<strong style='color:#dc2626;'>Rejected</strong>",
                'Reason Given' => "<span style='color:#991b1b;font-weight:600;'>{$reasonText}</span>",
                'Guideline Advice' => "Ensure genuine photos, accurate pricing in PKR, clear descriptions, and valid local location."
            ],
            'actionButton' => [
                'text' => 'Review & Edit Your Listings',
                'url' => "{$siteUrl}/user/my-listings.php"
            ],
            'extraNotice' => "You can edit your listing with corrected information or create a new ad following our community guidelines."
        ]);

        return self::sendEmail($to, $subject, $html, null, 'listing_rejected');
    }

    // =========================================================================
    // 7. PASSWORD RESET EMAIL
    // =========================================================================
    public static function sendPasswordResetEmail(string $to, string $userName, string $resetUrl): array
    {
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'SargodhaMart';
        $subject = "Reset Your {$siteName} Password";

        $html = self::renderTemplate([
            'title' => "Password Reset Request",
            'badgeText' => "SECURITY ACTION",
            'badgeType' => "info",
            'greeting' => "Salam, " . htmlspecialchars($userName) . ",",
            'leadMessage' => "We received a request to reset the password for your {$siteName} account. Click the button below to set a new password:",
            'detailRows' => [
                'Account Email' => htmlspecialchars($to),
                'Security Status' => "Encrypted 1-Time Expiring Token",
                'Link Validity' => "Valid for 60 minutes only"
            ],
            'actionButton' => [
                'text' => 'Set New Password',
                'url' => $resetUrl
            ],
            'extraNotice' => "SECURITY NOTICE: If you did not request this password reset, please ignore this email. Your current password remains secure and will not change without this link."
        ]);

        return self::sendEmail($to, $subject, $html, null, 'password_reset');
    }

    // =========================================================================
    // 8. ADMIN NOTIFICATIONS EMAIL
    // =========================================================================
    public static function sendAdminNotificationEmail(string $eventType, array $data): array
    {
        $adminEmail = self::getAdminEmail();
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'SargodhaMart';
        $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'http://localhost:3000';

        if ($eventType === 'new_activation') {
            $subject = "[{$siteName} Admin] New Seller Activation Awaiting Verification (TRX: " . ($data['transactionId'] ?? 'N/A') . ")";
            $html = self::renderTemplate([
                'title' => "New Seller Verification Request",
                'badgeText' => "ADMIN ACTION NEEDED",
                'badgeType' => "warning",
                'greeting' => "Administrator Alert,",
                'leadMessage' => "A seller has submitted a new one-time Rs. " . number_format($data['amount'] ?? 1000) . " activation fee for verification.",
                'detailRows' => [
                    'Seller Name' => htmlspecialchars($data['userName'] ?? 'Unknown'),
                    'Seller Email' => htmlspecialchars($data['userEmail'] ?? 'N/A'),
                    'Payment Method' => htmlspecialchars($data['method'] ?? 'EasyPaisa'),
                    'Transaction ID (TRX ID)' => "<strong style='color:#0284c7;'>" . htmlspecialchars($data['transactionId'] ?? 'N/A') . "</strong>",
                    'Amount' => "Rs. " . number_format($data['amount'] ?? 1000)
                ],
                'actionButton' => [
                    'text' => 'Review in Admin Payments Queue',
                    'url' => "{$siteUrl}/admin/payments.php"
                ]
            ]);
            return self::sendEmail($adminEmail, $subject, $html, null, 'admin_notification');

        } elseif ($eventType === 'new_listing') {
            $subject = "[{$siteName} Admin] New Listing Awaiting Review: " . ($data['title'] ?? 'Product');
            $html = self::renderTemplate([
                'title' => "New Product Listing Submitted",
                'badgeText' => "MODERATION QUEUE",
                'badgeType' => "info",
                'greeting' => "Administrator Alert,",
                'leadMessage' => "A seller has posted a new product listing that requires review.",
                'detailRows' => [
                    'Listing Title' => htmlspecialchars($data['title'] ?? 'N/A'),
                    'Seller' => htmlspecialchars($data['sellerName'] ?? 'Seller'),
                    'Price' => "Rs. " . number_format($data['price'] ?? 0),
                    'Location' => htmlspecialchars($data['city'] ?? 'Sargodha') . ' - ' . htmlspecialchars($data['area'] ?? '')
                ],
                'actionButton' => [
                    'text' => 'Review Listings in Admin',
                    'url' => "{$siteUrl}/admin/listings.php"
                ]
            ]);
            return self::sendEmail($adminEmail, $subject, $html, null, 'admin_notification');

        } elseif ($eventType === 'new_report') {
            $subject = "[{$siteName} Admin] Urgent Report Submitted: " . ($data['targetTitle'] ?? 'Listing');
            $html = self::renderTemplate([
                'title' => "Content Report Received",
                'badgeText' => "REPORT PENDING",
                'badgeType' => "danger",
                'greeting' => "Administrator Alert,",
                'leadMessage' => "A user has reported a listing or job posting for potential policy violation.",
                'detailRows' => [
                    'Reported Item' => htmlspecialchars($data['targetTitle'] ?? 'N/A'),
                    'Reason' => "<strong style='color:#dc2626;'>" . htmlspecialchars($data['reason'] ?? 'Violation') . "</strong>",
                    'Reporter' => htmlspecialchars($data['reporterName'] ?? 'Anonymous'),
                    'Details' => htmlspecialchars($data['details'] ?? 'No further details provided')
                ],
                'actionButton' => [
                    'text' => 'Investigate Report in Admin',
                    'url' => "{$siteUrl}/admin/reports.php"
                ]
            ]);
            return self::sendEmail($adminEmail, $subject, $html, null, 'admin_notification');
        }

        return ['success' => false, 'error' => 'Unknown event type'];
    }

    // =========================================================================
    // 9. TEST EMAIL DISPATCH
    // =========================================================================
    public static function sendTestEmail(string $to): array
    {
        $siteName = defined('SITE_NAME') ? SITE_NAME : 'SargodhaMart';
        $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'http://localhost:3000';
        $subject = "✅ [Test] Resend Integration Connected — {$siteName}";

        $html = self::renderTemplate([
            'title' => "Resend Connection Verified",
            'badgeText' => "TEST SUCCESSFUL",
            'badgeType' => "success",
            'greeting' => "Administrator Test Message,",
            'leadMessage' => "Congratulations! Your Resend email service has been successfully configured and is actively communicating with the {$siteName} backend.",
            'detailRows' => [
                'Provider' => 'Resend (Official HTTP API)',
                'Sender Identity' => htmlspecialchars(self::getSender()),
                'Recipient' => htmlspecialchars($to),
                'API Key Status' => htmlspecialchars(self::getMaskedApiKey()),
                'Server Timestamp' => date('Y-m-d H:i:s T'),
                'Hosting Compatibility' => 'Hostinger / cPanel / MariaDB Ready'
            ],
            'actionButton' => [
                'text' => 'Open Website Admin',
                'url' => "{$siteUrl}/admin/settings.php?tab=email"
            ],
            'extraNotice' => "Automated email notifications for Registration, Seller Activation, Listing Moderation, and Password Resets are now live."
        ]);

        return self::sendEmail($to, $subject, $html, null, 'test');
    }

    // =========================================================================
    // PREMIUM RESPONSIVE SARGODHAMART EMAIL TEMPLATE
    // =========================================================================
    private static function renderTemplate(array $params): string
    {
        $title = $params['title'] ?? 'SargodhaMart Notification';
        $badgeText = $params['badgeText'] ?? null;
        $badgeType = $params['badgeType'] ?? 'success';
        $greeting = $params['greeting'] ?? 'Salam!';
        $leadMessage = $params['leadMessage'] ?? '';
        $detailRows = $params['detailRows'] ?? [];
        $actionButton = $params['actionButton'] ?? null;
        $extraNotice = $params['extraNotice'] ?? null;

        $siteName = defined('SITE_NAME') ? SITE_NAME : 'SargodhaMart';
        $siteTagline = defined('SITE_TAGLINE') ? SITE_TAGLINE : 'Buy • Sell • Jobs • Grow';
        $siteUrl = defined('SITE_URL') ? rtrim(SITE_URL, '/') : 'http://localhost:3000';
        $currentYear = date('Y');

        // Badge styling
        $badgeBg = '#ecfdf5';
        $badgeColor = '#065f46';
        $badgeBorder = '#a7f3d0';

        if ($badgeType === 'danger') {
            $badgeBg = '#fef2f2';
            $badgeColor = '#991b1b';
            $badgeBorder = '#fecaca';
        } elseif ($badgeType === 'warning') {
            $badgeBg = '#fffbeb';
            $badgeColor = '#92400e';
            $badgeBorder = '#fde68a';
        } elseif ($badgeType === 'info') {
            $badgeBg = '#f0f9ff';
            $badgeColor = '#075985';
            $badgeBorder = '#bae6fd';
        }

        // Build details table
        $detailsHtml = '';
        if (!empty($detailRows)) {
            $detailsHtml .= '<table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin:20px 0;background-color:#f8fafc;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden;">';
            $i = 0;
            foreach ($detailRows as $label => $val) {
                $bg = ($i % 2 === 0) ? '#f8fafc' : '#ffffff';
                $detailsHtml .= "<tr style='background-color:{$bg};'>";
                $detailsHtml .= "<td style='padding:10px 16px;font-size:12px;font-weight:bold;color:#475569;width:38%;border-bottom:1px solid #e2e8f0;'>{$label}</td>";
                $detailsHtml .= "<td style='padding:10px 16px;font-size:13px;color:#0f172a;border-bottom:1px solid #e2e8f0;'>{$val}</td>";
                $detailsHtml .= "</tr>";
                $i++;
            }
            $detailsHtml .= '</table>';
        }

        // Action button
        $btnHtml = '';
        if (!empty($actionButton) && !empty($actionButton['text']) && !empty($actionButton['url'])) {
            $btnText = htmlspecialchars($actionButton['text']);
            $btnUrl = htmlspecialchars($actionButton['url']);
            $btnHtml = "
                <div style='text-align:center;margin:28px 0 20px 0;'>
                    <a href='{$btnUrl}' target='_blank' style='display:inline-block;padding:14px 28px;background:linear-gradient(135deg, #059669 0%, #0d9488 100%);color:#ffffff;text-decoration:none;font-size:14px;font-weight:bold;border-radius:12px;box-shadow:0 4px 14px rgba(5,150,105,0.3);letter-spacing:0.3px;'>
                        {$btnText} &rarr;
                    </a>
                </div>
            ";
        }

        // Extra notice
        $noticeHtml = '';
        if (!empty($extraNotice)) {
            $noticeHtml = "
                <div style='background-color:#f0fdf4;border-left:4px solid #10b981;padding:12px 16px;border-radius:6px;font-size:12px;color:#166534;margin:20px 0;line-height:1.5;'>
                    {$extraNotice}
                </div>
            ";
        }

        return <<<HTML
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{$title}</title>
</head>
<body style="margin:0;padding:0;background-color:#f1f5f9;font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,Helvetica,Arial,sans-serif;color:#1e293b;-webkit-font-smoothing:antialiased;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background-color:#f1f5f9;padding:30px 15px;">
        <tr>
            <td align="center">
                <!-- Main Container -->
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width:580px;background-color:#ffffff;border-radius:20px;overflow:hidden;box-shadow:0 10px 30px rgba(0,0,0,0.06);border:1px solid #e2e8f0;">
                    
                    <!-- Header Banner -->
                    <tr>
                        <td style="background:linear-gradient(135deg, #064e3b 0%, #047857 50%, #0d9488 100%);padding:28px 30px;text-align:center;">
                            <div style="font-size:24px;font-weight:900;color:#ffffff;letter-spacing:1px;text-transform:uppercase;">
                                {$siteName}
                            </div>
                            <div style="font-size:11px;color:#a7f3d0;font-weight:600;letter-spacing:1.5px;text-transform:uppercase;margin-top:4px;">
                                {$siteTagline} • Sargodha • Shaheenabad • Sillanwali
                            </div>
                        </td>
                    </tr>

                    <!-- Body Content -->
                    <tr>
                        <td style="padding:32px 30px 24px 30px;">
                            
                            <!-- Status Badge -->
                            <div style="display:inline-block;padding:4px 12px;background-color:{$badgeBg};color:{$badgeColor};border:1px solid {$badgeBorder};border-radius:20px;font-size:11px;font-weight:800;letter-spacing:0.5px;text-transform:uppercase;margin-bottom:16px;">
                                {$badgeText}
                            </div>

                            <!-- Greeting & Lead -->
                            <h2 style="margin:0 0 12px 0;font-size:19px;font-weight:800;color:#0f172a;line-height:1.3;">
                                {$greeting}
                            </h2>
                            <p style="margin:0 0 16px 0;font-size:14px;color:#475569;line-height:1.6;">
                                {$leadMessage}
                            </p>

                            <!-- Structured Details -->
                            {$detailsHtml}

                            <!-- Call to Action Button -->
                            {$btnHtml}

                            <!-- Extra Notice Box -->
                            {$noticeHtml}

                        </td>
                    </tr>

                    <!-- Footer -->
                    <tr>
                        <td style="background-color:#f8fafc;padding:22px 30px;border-top:1px solid #e2e8f0;text-align:center;">
                            <div style="font-size:12px;font-weight:700;color:#334155;margin-bottom:4px;">
                                {$siteName} Local Marketplace & Employment Hub
                            </div>
                            <div style="font-size:11px;color:#64748b;line-height:1.5;margin-bottom:10px;">
                                Trust Plaza / Club Road, Sargodha, Punjab, Pakistan<br>
                                Official Helpline: <strong>03127453108</strong> • <a href="{$siteUrl}" style="color:#059669;text-decoration:none;font-weight:bold;">Visit SargodhaMart</a>
                            </div>
                            <div style="font-size:10px;color:#94a3b8;border-top:1px solid #e2e8f0;padding-top:10px;">
                                &copy; {$currentYear} {$siteName}. All rights reserved. Automated transactional email sent via Resend.
                            </div>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
    }
}
