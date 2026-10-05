<?php
/**
 * Sargodha Mandi - AJAX Chat API
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'error' => 'Authentication required']);
    exit;
}

$user = currentUser();
$db = getDB();
$action = $_GET['action'] ?? 'fetch';

if ($action === 'send') {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true) ?: [];

    $convId = (int)($data['conversation_id'] ?? 0);
    $text = trim($data['message'] ?? '');

    if ($convId <= 0 || empty($text)) {
        echo json_encode(['success' => false, 'error' => 'Missing message or conversation ID']);
        exit;
    }

    // Verify participant
    $auth = $db->prepare("SELECT * FROM conversations WHERE id = :cid AND (buyer_id = :uid OR seller_id = :uid) LIMIT 1");
    $auth->execute([':cid' => $convId, ':uid' => $user['id']]);
    $conv = $auth->fetch();

    if (!$conv) {
        echo json_encode(['success' => false, 'error' => 'Access denied to this conversation']);
        exit;
    }

    // Insert message
    $stmt = $db->prepare("INSERT INTO messages (conversation_id, sender_id, message, is_read) VALUES (:cid, :uid, :msg, 0)");
    $stmt->execute([':cid' => $convId, ':uid' => $user['id'], ':msg' => $text]);

    // Update conversation timestamp
    $db->prepare("UPDATE conversations SET updated_at = NOW() WHERE id = :cid")->execute([':cid' => $convId]);

    // Send notification to recipient
    $recipientId = ($conv['buyer_id'] == $user['id']) ? $conv['seller_id'] : $conv['buyer_id'];
    createNotification(
        $recipientId,
        'New Message from ' . $user['full_name'],
        mb_strimwidth($text, 0, 80, '...'),
        '/user/messages.php?conv=' . $convId,
        'message'
    );

    echo json_encode(['success' => true]);
    exit;
}

if ($action === 'fetch') {
    $convId = (int)($_GET['conversation_id'] ?? 0);
    if ($convId <= 0) {
        echo json_encode(['success' => false, 'error' => 'Invalid conversation']);
        exit;
    }

    // Verify participant
    $auth = $db->prepare("SELECT * FROM conversations WHERE id = :cid AND (buyer_id = :uid OR seller_id = :uid) LIMIT 1");
    $auth->execute([':cid' => $convId, ':uid' => $user['id']]);
    $conv = $auth->fetch();

    if (!$conv) {
        echo json_encode(['success' => false, 'error' => 'Access denied']);
        exit;
    }

    // Mark unread messages from other user as read
    $db->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = :cid AND sender_id != :uid")
       ->execute([':cid' => $convId, ':uid' => $user['id']]);

    $stmt = $db->prepare("SELECT * FROM messages WHERE conversation_id = :cid ORDER BY id ASC");
    $stmt->execute([':cid' => $convId]);
    $rows = $stmt->fetchAll();

    $output = [];
    foreach ($rows as $r) {
        $output[] = [
            'id' => $r['id'],
            'message' => $r['message'],
            'is_mine' => ($r['sender_id'] == $user['id']),
            'time_ago' => timeAgo($r['created_at'])
        ];
    }

    echo json_encode(['success' => true, 'messages' => $output]);
    exit;
}

echo json_encode(['success' => false, 'error' => 'Unknown action']);
