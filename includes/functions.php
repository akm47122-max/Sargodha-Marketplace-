<?php
/**
 * Global Helper Functions & Business Logic
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../config/csrf.php';
require_once __DIR__ . '/ResendMailer.php';

// Safe HTML escaping
function e(?string $string): string {
    return htmlspecialchars($string ?? '', ENT_QUOTES, 'UTF-8');
}

// Format Pakistani Rupee currency
function formatPKR($amount): string {
    return 'Rs. ' . number_format((float)$amount, 0);
}

// Human readable time ago
function timeAgo($datetime): string {
    if (!$datetime) return '';
    $timestamp = strtotime($datetime);
    $diff = time() - $timestamp;

    if ($diff < 60) return 'Just now';
    if ($diff < 3600) return floor($diff / 60) . ' mins ago';
    if ($diff < 86400) return floor($diff / 3600) . ' hours ago';
    if ($diff < 604800) return floor($diff / 86400) . ' days ago';
    return date('d M Y', $timestamp);
}

// Authentication Helpers
function isLoggedIn(): bool {
    return !empty($_SESSION['user_id']);
}

function currentUser(): ?array {
    if (!isLoggedIn()) return null;

    static $cachedUser = null;
    if ($cachedUser !== null) return $cachedUser;

    $db = getDB();
    $stmt = $db->prepare("SELECT * FROM users WHERE id = :id AND status = 'active' LIMIT 1");
    $stmt->execute([':id' => $_SESSION['user_id']]);
    $cachedUser = $stmt->fetch();
    return $cachedUser ?: null;
}

function isAdmin(): bool {
    $user = currentUser();
    return $user && in_array($user['role'], ['admin', 'super_admin'], true);
}

function isSuperAdmin(): bool {
    $user = currentUser();
    return $user && $user['role'] === 'super_admin';
}

// Flash Messaging
function setFlash(string $message, string $type = 'success'): void {
    $_SESSION['flash'] = [
        'message' => $message,
        'type' => $type // success, danger, warning, info
    ];
}

function getFlash(): ?array {
    if (!empty($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}

// Redirect Helper
function redirect(string $url, ?string $flashMessage = null, string $flashType = 'success'): void {
    if ($flashMessage) {
        setFlash($flashMessage, $flashType);
    }
    header("Location: $url");
    exit;
}

// Site Settings Cache with Intelligent Alias Fallbacks
function getSetting(string $key, $default = null) {
    static $settings = null;
    if ($settings === null) {
        try {
            $db = getDB();
            $stmt = $db->query("SELECT setting_key, setting_value FROM site_settings");
            $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR) ?: [];
        } catch (Exception $e) {
            $settings = [];
        }
    }

    if (isset($settings[$key]) && $settings[$key] !== '') {
        return $settings[$key];
    }

    // Aliases & fallback mappings
    $aliases = [
        'whatsapp_number' => ['official_whatsapp_number', 'official_whatsapp', 'payment_number'],
        'official_whatsapp_number' => ['whatsapp_number', 'official_whatsapp', 'payment_number'],
        'official_whatsapp' => ['whatsapp_number', 'official_whatsapp_number', 'payment_number'],
        'whatsapp_channel_url' => ['official_whatsapp_channel_url'],
        'website_name' => ['site_name'],
        'site_name' => ['website_name'],
        'tagline' => ['site_tagline'],
        'site_tagline' => ['tagline'],
        'activation_fee' => ['seller_activation_fee', 'listing_fee'],
        'seller_activation_fee' => ['activation_fee'],
        'official_phone' => ['contact_phone'],
        'contact_phone' => ['official_phone'],
        'official_call_number' => ['official_phone', 'contact_phone'],
        'contact_email' => ['support_email'],
    ];

    if (!empty($aliases[$key])) {
        foreach ($aliases[$key] as $aliasKey) {
            if (isset($settings[$aliasKey]) && $settings[$aliasKey] !== '') {
                return $settings[$aliasKey];
            }
        }
    }

    // Defined PHP Constants fallback
    $constantMap = [
        'website_name' => 'SITE_NAME',
        'site_name' => 'SITE_NAME',
        'tagline' => 'SITE_TAGLINE',
        'site_tagline' => 'SITE_TAGLINE',
        'activation_fee' => 'ONE_TIME_ACTIVATION_FEE',
        'seller_activation_fee' => 'ONE_TIME_ACTIVATION_FEE',
        'whatsapp_channel_url' => 'OFFICIAL_WHATSAPP_CHANNEL_URL',
        'whatsapp_number' => 'OFFICIAL_PAYMENT_NUMBER',
        'official_whatsapp' => 'OFFICIAL_PAYMENT_NUMBER',
        'payment_number' => 'OFFICIAL_PAYMENT_NUMBER',
        'payment_account_name' => 'OFFICIAL_ACCOUNT_NAME',
        'payment_account_title' => 'OFFICIAL_ACCOUNT_NAME',
        'contact_email' => 'SUPPORT_EMAIL',
        'contact_phone' => 'SUPPORT_PHONE',
    ];

    if (isset($constantMap[$key]) && defined($constantMap[$key])) {
        return constant($constantMap[$key]);
    }

    return $default;
}

// Fetch Active Announcements
function getActiveAnnouncementsPHP(?string $location = null): array {
    try {
        $db = getDB();
        $sql = "SELECT * FROM announcements WHERE is_active = 1";
        $params = [];
        if ($location) {
            $sql .= " AND display_location = :loc";
            $params[':loc'] = $location;
        }
        $sql .= " AND (start_date IS NULL OR start_date <= NOW())";
        $sql .= " AND (expiry_date IS NULL OR expiry_date >= NOW())";
        $sql .= " ORDER BY is_highlighted DESC, id DESC";
        $stmt = $db->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll() ?: [];
    } catch (Exception $e) {
        return [];
    }
}

// Fetch Enabled Social Media Links
function getSocialLinksPHP(): array {
    try {
        $db = getDB();
        $stmt = $db->query("SELECT * FROM social_media_links WHERE is_enabled = 1 ORDER BY display_order ASC, id ASC");
        return $stmt->fetchAll() ?: [];
    } catch (Exception $e) {
        return [];
    }
}

// Check Maintenance Mode
function isMaintenanceModePHP(): bool {
    $mode = getSetting('is_maintenance_mode', '0');
    return $mode === '1' || $mode === 'true' || $mode === 1 || $mode === true;
}

// Secure Image Upload Validator
function validateAndSaveImage(array $file, string $subfolder = 'products'): array {
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errors = [
            UPLOAD_ERR_INI_SIZE => 'File exceeds server upload size limit.',
            UPLOAD_ERR_FORM_SIZE => 'File exceeds form size limit.',
            UPLOAD_ERR_PARTIAL => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_FILE => 'No file was uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION => 'File upload stopped by PHP extension.'
        ];
        return ['success' => false, 'error' => $errors[$file['error']] ?? 'Upload error.'];
    }

    // 1. Max size: 5MB
    $maxSize = 5 * 1024 * 1024;
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'error' => 'File size exceeds maximum 5MB limit.'];
    }

    // 2. MIME type verification via finfo (cannot be spoofed by file extension)
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);

    $allowedMimes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/webp' => 'webp'
    ];

    if (!isset($allowedMimes[$mimeType])) {
        return ['success' => false, 'error' => 'Invalid image format. Only JPG, PNG, and WebP images are allowed.'];
    }

    // 3. Image dimension check (ensures file is actually an image and not an executable disguised as an image)
    $imageInfo = @getimagesize($file['tmp_name']);
    if (!$imageInfo) {
        return ['success' => false, 'error' => 'File is corrupted or not a valid image.'];
    }

    // 4. Generate random safe filename with proper extension
    $extension = $allowedMimes[$mimeType];
    $randomName = bin2hex(random_bytes(16)) . '_' . time() . '.' . $extension;

    $targetDir = UPLOAD_PATH . '/' . $subfolder;
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0755, true);
    }

    $targetFilePath = $targetDir . '/' . $randomName;
    if (!move_uploaded_file($file['tmp_name'], $targetFilePath)) {
        return ['success' => false, 'error' => 'Could not save file to disk. Check permissions.'];
    }

    $relativePath = 'uploads/' . $subfolder . '/' . $randomName;
    return ['success' => true, 'path' => $relativePath];
}

// Notification Creator
function createNotification(int $userId, string $title, string $message, ?string $link = null, string $type = 'general'): bool {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO notifications (user_id, title, message, link, type) VALUES (:uid, :title, :msg, :link, :type)");
        return $stmt->execute([
            ':uid' => $userId,
            ':title' => $title,
            ':msg' => $message,
            ':link' => $link,
            ':type' => $type
        ]);
    } catch (Exception $e) {
        return false;
    }
}

// Unread Counters
function getUnreadNotificationCount(int $userId): int {
    try {
        $db = getDB();
        $stmt = $db->prepare("SELECT COUNT(*) FROM notifications WHERE user_id = :uid AND is_read = 0");
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

function getUnreadMessageCount(int $userId): int {
    try {
        $db = getDB();
        $stmt = $db->prepare("
            SELECT COUNT(*) 
            FROM messages m
            JOIN conversations c ON m.conversation_id = c.id
            WHERE (c.buyer_id = :uid OR c.seller_id = :uid)
              AND m.sender_id != :uid
              AND m.is_read = 0
        ");
        $stmt->execute([':uid' => $userId]);
        return (int)$stmt->fetchColumn();
    } catch (Exception $e) {
        return 0;
    }
}

// Admin Audit Logging
function logAdminAction(int $adminId, string $action, string $targetType, int $targetId, ?string $details = null): void {
    try {
        $db = getDB();
        $stmt = $db->prepare("INSERT INTO admin_logs (admin_id, action, target_type, target_id, details, ip_address) VALUES (:aid, :act, :tt, :tid, :det, :ip)");
        $stmt->execute([
            ':aid' => $adminId,
            ':act' => $action,
            ':tt' => $targetType,
            ':tid' => $targetId,
            ':det' => $details,
            ':ip' => $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1'
        ]);

        if (file_exists(__DIR__ . '/../services/AI/AIEventLogger.php')) {
            require_once __DIR__ . '/../services/AI/AIEventLogger.php';
            AIEventLogger::logEvent('ADMIN_ACTION', $targetType, $targetId, $adminId, [
                'action' => $action,
                'details' => $details
            ]);
        }
    } catch (Exception $e) {
        // Silent fail
    }
}

// -------------------------------------------------------------------------
// Telegram Bot Channel Automation Helpers (Server-side Only)
// -------------------------------------------------------------------------
if (!function_exists('getTelegramToken')) {
    function getTelegramToken(): string {
        $token = getenv('TELEGRAM_BOT_TOKEN');
        if ($token && $token !== 'YOUR_TELEGRAM_BOT_TOKEN') {
            return trim($token);
        }
        $dbToken = getSetting('telegram_bot_token');
        return $dbToken ? trim($dbToken) : '';
    }
}

if (!function_exists('callTelegramAPI')) {
    function callTelegramAPI(string $endpoint, array $payload = []): array {
        $token = getTelegramToken();
        if (empty($token)) {
            return ['ok' => false, 'description' => 'TELEGRAM_BOT_TOKEN is not configured on the server.'];
        }

        $url = "https://api.telegram.org/bot{$token}/{$endpoint}";
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_TIMEOUT, 12);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError) {
            return ['ok' => false, 'description' => "cURL Network Error: {$curlError}"];
        }

        $json = json_decode($response, true);
        return is_array($json) ? $json : ['ok' => false, 'description' => 'Invalid JSON from Telegram API.'];
    }
}

if (!function_exists('broadcastListingToTelegramPHP')) {
    function broadcastListingToTelegramPHP(int $listingId, ?string $channelId = null): array {
        try {
            $db = getDB();
            $stmt = $db->prepare("SELECT * FROM listings WHERE id = :id AND status = 'published' LIMIT 1");
            $stmt->execute([':id' => $listingId]);
            $listing = $stmt->fetch();
            if (!$listing) {
                return ['success' => false, 'error' => 'Listing not found or not published.'];
            }

            $targetChannel = $channelId ?: getSetting('telegram_channel_id', '-1003328935535');
            $price = function_exists('formatPKR') ? formatPKR($listing['price']) : 'Rs. ' . number_format($listing['price']);
            $location = htmlspecialchars("{$listing['area']}, {$listing['city']}");
            $title = htmlspecialchars($listing['title']);
            $desc = htmlspecialchars(mb_strimwidth(strip_tags($listing['description']), 0, 200, '...'));
            $productUrl = (defined('SITE_URL') ? SITE_URL : 'http://localhost:3000') . "/product.php?id={$listing['id']}";

            $messageText = "🛍️ <b>NEW VERIFIED LISTING ON SARGODHAMART</b>\n\n" .
                "📦 <b>{$title}</b>\n" .
                "💰 <b>Price:</b> {$price}\n" .
                "📍 <b>Location:</b> {$location}\n" .
                "🏷️ <b>Condition:</b> {$listing['item_condition']}\n\n" .
                "📝 <i>{$desc}</i>\n\n" .
                "🛡️ <i>Direct verified seller from Sargodha District</i>";

            $inlineKeyboard = [
                [['text' => '🔍 View Product on Website', 'url' => $productUrl]]
            ];

            $contactRow = [];
            if (!empty($listing['phone_number'])) {
                $contactRow[] = ['text' => "📞 Call ({$listing['phone_number']})", 'url' => "tel:{$listing['phone_number']}"];
                $cleanWa = preg_replace('/[^0-9]/', '', $listing['whatsapp_number'] ?: $listing['phone_number']);
                if (str_starts_with($cleanWa, '0')) $cleanWa = '92' . substr($cleanWa, 1);
                $contactRow[] = ['text' => '💬 WhatsApp', 'url' => "https://wa.me/{$cleanWa}"];
            }
            if (!empty($contactRow)) $inlineKeyboard[] = $contactRow;

            $payload = [
                'chat_id' => $targetChannel,
                'text' => $messageText,
                'parse_mode' => 'HTML',
                'reply_markup' => ['inline_keyboard' => $inlineKeyboard],
            ];

            $result = callTelegramAPI('sendMessage', $payload);
            if (!empty($result['ok'])) {
                $msgId = $result['result']['message_id'] ?? 0;
                $logStmt = $db->prepare("INSERT INTO telegram_publication_logs (content_type, content_id, channel_id, message_id, status) VALUES ('product', :cid, :chid, :mid, 'success')");
                $logStmt->execute([':cid' => $listingId, ':chid' => $targetChannel, ':mid' => $msgId]);
                return ['success' => true, 'message_id' => $msgId];
            } else {
                $err = $result['description'] ?? 'Telegram API error';
                $logStmt = $db->prepare("INSERT INTO telegram_publication_logs (content_type, content_id, channel_id, status, error_message) VALUES ('product', :cid, :chid, 'failed', :err)");
                $logStmt->execute([':cid' => $listingId, ':chid' => $targetChannel, ':err' => $err]);
                return ['success' => false, 'error' => $err];
            }
        } catch (Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}

