<?php
/**
 * SARGODHAMART - Telegram Automation Backend API
 * Connects Telegram Bot with SargodhaMart for instant channel broadcasts.
 * Target Channel ID: -1003328935535
 */

header('Content-Type: application/json; charset=UTF-8');

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../includes/functions.php';

// Telegram Bot Token retrieved from server environment (never hardcoded in frontend)
function getTelegramToken(): string {
    $token = getenv('TELEGRAM_BOT_TOKEN');
    if ($token && $token !== 'YOUR_TELEGRAM_BOT_TOKEN') {
        return trim($token);
    }
    // Alternatively check secure setting
    $dbToken = getSetting('telegram_bot_token');
    return $dbToken ? trim($dbToken) : '';
}

// Calls Telegram API via cURL
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

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. GET STATUS
if ($action === 'status') {
    $token = getTelegramToken();
    $hasToken = !empty($token) && strlen($token) > 10;
    $channelId = getSetting('telegram_channel_id', '-1003328935535');
    $enabled = getSetting('telegram_enabled', '0') === '1';

    $botUsername = '';
    if ($hasToken) {
        $me = callTelegramAPI('getMe');
        if (!empty($me['ok'])) {
            $botUsername = $me['result']['username'] ?? '';
        }
    }

    echo json_encode([
        'success' => true,
        'hasTokenConfigured' => $hasToken,
        'channelId' => $channelId,
        'isEnabled' => $enabled,
        'botUsername' => $botUsername,
    ]);
    exit;
}

// Check admin for management actions
if (!isAdmin()) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Unauthorized admin access required.']);
    exit;
}

// 2. TEST CONNECTION
if ($action === 'test_connection') {
    $me = callTelegramAPI('getMe');
    if (!empty($me['ok'])) {
        echo json_encode([
            'success' => true,
            'botUsername' => $me['result']['username'] ?? 'Bot',
            'firstName' => $me['result']['first_name'] ?? 'Bot',
            'message' => "Bot @{$me['result']['username']} connected successfully!",
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $me['description'] ?? 'Unable to connect to Telegram Bot API. Verify token.',
        ]);
    }
    exit;
}

// 3. SEND TEST MESSAGE
if ($action === 'send_test') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $targetChannel = $input['channel_id'] ?? getSetting('telegram_channel_id', '-1003328935535');
    $timestamp = date('d M Y, h:i:s A');

    $text = "📢 <b>SargodhaMart Telegram Automation Test</b>\n\n" .
        "✅ <b>Status:</b> Telegram Bot integration is successfully connected and operational!\n" .
        "📍 <b>Target Channel:</b> <code>{$targetChannel}</code>\n" .
        "🕒 <b>Server Time:</b> {$timestamp}\n\n" .
        "🛍️ <i>Direct trading & verified local employment for Sargodha, Shaheenabad, and Sillanwali.</i>";

    $payload = [
        'chat_id' => $targetChannel,
        'text' => $text,
        'parse_mode' => 'HTML',
        'reply_markup' => [
            'inline_keyboard' => [
                [
                    ['text' => '🌐 Open SargodhaMart', 'url' => SITE_URL],
                    ['text' => '💬 WhatsApp Channel', 'url' => getSetting('whatsapp_channel_url', 'https://whatsapp.com/channel/0029Vb8bmhAGk1Fzze6iTX0g')],
                ],
            ],
        ],
    ];

    $result = callTelegramAPI('sendMessage', $payload);
    if (!empty($result['ok'])) {
        $msgId = $result['result']['message_id'] ?? 0;
        echo json_encode([
            'success' => true,
            'messageId' => $msgId,
            'message' => "Test message delivered to channel {$targetChannel} (Message ID: {$msgId})",
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $result['description'] ?? 'Failed to deliver test message.',
        ]);
    }
    exit;
}

// 4. PUBLISH CONTENT
if ($action === 'publish') {
    $input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
    $type = $input['type'] ?? '';
    $id = (int)($input['id'] ?? 0);
    $targetChannel = $input['channel_id'] ?? getSetting('telegram_channel_id', '-1003328935535');

    $db = getDB();
    $messageText = '';
    $inlineKeyboard = [];

    if ($type === 'product' && $id > 0) {
        $stmt = $db->prepare("SELECT * FROM listings WHERE id = :id AND status = 'published' LIMIT 1");
        $stmt->execute([':id' => $id]);
        $listing = $stmt->fetch();

        if (!$listing) {
            echo json_encode(['success' => false, 'error' => 'Product listing not found or not published.']);
            exit;
        }

        $price = formatPKR($listing['price']);
        $location = htmlspecialchars("{$listing['area']}, {$listing['city']}");
        $title = htmlspecialchars($listing['title']);
        $desc = htmlspecialchars(mb_strimwidth(strip_tags($listing['description']), 0, 200, '...'));
        $productUrl = SITE_URL . "/product.php?id={$listing['id']}";

        $messageText = "🛍️ <b>NEW VERIFIED LISTING ON SARGODHAMART</b>\n\n" .
            "📦 <b>{$title}</b>\n" .
            "💰 <b>Price:</b> {$price}\n" .
            "📍 <b>Location:</b> {$location}\n" .
            "🏷️ <b>Condition:</b> {$listing['item_condition']}\n\n" .
            "📝 <i>{$desc}</i>\n\n" .
            "🛡️ <i>Direct verified seller from Sargodha District</i>";

        $inlineKeyboard[] = [['text' => '🔍 View Product on Website', 'url' => $productUrl]];
        $contactRow = [];
        if (!empty($listing['phone_number'])) {
            $contactRow[] = ['text' => "📞 Call ({$listing['phone_number']})", 'url' => "tel:{$listing['phone_number']}"];
            $cleanWa = preg_replace('/[^0-9]/', '', $listing['whatsapp_number'] ?: $listing['phone_number']);
            if (str_starts_with($cleanWa, '0')) $cleanWa = '92' . substr($cleanWa, 1);
            $contactRow[] = ['text' => '💬 WhatsApp', 'url' => "https://wa.me/{$cleanWa}"];
        }
        if (!empty($contactRow)) $inlineKeyboard[] = $contactRow;
    }

    if ($type === 'job' && $id > 0) {
        $stmt = $db->prepare("SELECT * FROM jobs WHERE id = :id AND status = 'published' LIMIT 1");
        $stmt->execute([':id' => $id]);
        $job = $stmt->fetch();

        if (!$job) {
            echo json_encode(['success' => false, 'error' => 'Job posting not found or not published.']);
            exit;
        }

        $typeLabel = $job['post_type'] === 'need_worker' ? '🏢 Employer Hiring' : '👤 Worker Seeking Employment';
        $location = htmlspecialchars("{$job['area']}, {$job['city']}");
        $title = htmlspecialchars($job['title']);
        $desc = htmlspecialchars(mb_strimwidth(strip_tags($job['description']), 0, 180, '...'));
        $jobUrl = SITE_URL . "/jobs.php?id={$job['id']}";

        $messageText = "💼 <b>NEW LOCAL JOB / ROZGAR OPPORTUNITY</b>\n\n" .
            "📌 <b>{$title}</b>\n" .
            "📋 <b>Category:</b> {$typeLabel}\n" .
            "📍 <b>Location:</b> {$location}\n" .
            (!empty($job['salary_or_payment']) ? "💵 <b>Compensation:</b> " . htmlspecialchars($job['salary_or_payment']) . "\n" : '') .
            "🛠️ <b>Skills:</b> " . htmlspecialchars($job['skills']) . "\n\n" .
            "📝 <i>{$desc}</i>\n\n" .
            "🛡️ <i>SargodhaMart Local Employment Hub</i>";

        $inlineKeyboard[] = [['text' => '💼 View Job Details', 'url' => $jobUrl]];
        $contactRow = [];
        if (!empty($job['phone_number'])) {
            $contactRow[] = ['text' => "📞 Call ({$job['phone_number']})", 'url' => "tel:{$job['phone_number']}"];
            $cleanWa = preg_replace('/[^0-9]/', '', $job['whatsapp_number'] ?: $job['phone_number']);
            if (str_starts_with($cleanWa, '0')) $cleanWa = '92' . substr($cleanWa, 1);
            $contactRow[] = ['text' => '💬 WhatsApp', 'url' => "https://wa.me/{$cleanWa}"];
        }
        if (!empty($contactRow)) $inlineKeyboard[] = $contactRow;
    }

    if (empty($messageText)) {
        echo json_encode(['success' => false, 'error' => 'Invalid content payload.']);
        exit;
    }

    $payload = [
        'chat_id' => $targetChannel,
        'text' => $messageText,
        'parse_mode' => 'HTML',
    ];
    if (!empty($inlineKeyboard)) {
        $payload['reply_markup'] = ['inline_keyboard' => $inlineKeyboard];
    }

    $result = callTelegramAPI('sendMessage', $payload);
    if (!empty($result['ok'])) {
        $msgId = $result['result']['message_id'] ?? 0;
        // Record in telegram log
        try {
            $logStmt = $db->prepare("INSERT INTO telegram_publication_logs (content_type, content_id, channel_id, message_id, status) VALUES (:type, :cid, :chid, :mid, 'published')");
            $logStmt->execute([':type' => $type, ':cid' => $id, ':chid' => $targetChannel, ':mid' => $msgId]);

            if (file_exists(__DIR__ . '/../services/AI/AIEventLogger.php')) {
                require_once __DIR__ . '/../services/AI/AIEventLogger.php';
                AIEventLogger::logEvent('TELEGRAM_PUBLICATION', $type, $id, null, [
                    'channel_id' => $targetChannel,
                    'message_id' => $msgId
                ]);
            }
        } catch (Exception $e) {}

        echo json_encode([
            'success' => true,
            'messageId' => $msgId,
            'message' => "Successfully broadcasted to Telegram channel {$targetChannel} (Message ID: {$msgId})",
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'error' => $result['description'] ?? 'Telegram publication failed.',
        ]);
    }
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action.']);
