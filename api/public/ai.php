<?php
/**
 * SARGODHAMART - Public Customer AI Assistant API
 * Privacy-safe, rate-limited endpoint for visitors and logged-in members.
 */

header('Content-Type: application/json; charset=utf-8');

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/db.php';
require_once __DIR__ . '/../../includes/functions.php';
require_once __DIR__ . '/../../services/AI/PublicAIAssistant.php';

// Check if Public AI is enabled globally
if (!PublicAIAssistant::isEnabled()) {
    echo json_encode([
        'success' => false,
        'error' => 'Public AI Assistant is currently disabled in site settings.'
    ]);
    exit;
}

// Rate Limiting (15 requests/min per IP/session)
$clientIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
if (!PublicAIAssistant::checkRateLimit($clientIp, 15)) {
    http_response_code(429);
    echo json_encode([
        'success' => false,
        'error' => 'Rate limit exceeded. Please wait a moment before sending another message.'
    ]);
    exit;
}

// Read message payload
$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$message = trim($input['message'] ?? '');

if (empty($message)) {
    echo json_encode([
        'success' => false,
        'error' => 'Please provide a message.'
    ]);
    exit;
}

// Identify user strictly from server-side session (never from client params!)
$authenticatedUserId = isLoggedIn() ? (int)currentUser()['id'] : null;
$sessionId = session_id();

try {
    $response = PublicAIAssistant::reply($message, $authenticatedUserId, $sessionId);
    echo json_encode($response);
} catch (Throwable $e) {
    echo json_encode([
        'success' => false,
        'error' => 'AI Assistant temporarily unavailable. Please try again shortly.'
    ]);
}
