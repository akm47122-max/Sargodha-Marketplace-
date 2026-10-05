<?php
/**
 * Sargodha Mandi - Buyer-Seller In-App Chat
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

$user = currentUser();
$db = getDB();

// Handle new conversation initiation from product page: ?listing_id=X&seller_id=Y
$targetListingId = (int)($_GET['listing_id'] ?? 0);
$targetSellerId = (int)($_GET['seller_id'] ?? 0);
$activeConvId = (int)($_GET['conv'] ?? 0);

if ($targetListingId > 0 && $targetSellerId > 0 && $targetSellerId !== $user['id']) {
    // Check if conversation already exists
    $chk = $db->prepare("SELECT id FROM conversations WHERE listing_id = :lid AND buyer_id = :bid AND seller_id = :sid LIMIT 1");
    $chk->execute([':lid' => $targetListingId, ':bid' => $user['id'], ':sid' => $targetSellerId]);
    $existingId = $chk->fetchColumn();

    if ($existingId) {
        $activeConvId = (int)$existingId;
    } else {
        // Create conversation
        $ins = $db->prepare("INSERT INTO conversations (listing_id, buyer_id, seller_id) VALUES (:lid, :bid, :sid)");
        $ins->execute([':lid' => $targetListingId, ':bid' => $user['id'], ':sid' => $targetSellerId]);
        $activeConvId = (int)$db->lastInsertId();
    }
}

// Fetch all conversations user participates in
$convStmt = $db->prepare("
    SELECT c.*, l.title as listing_title, l.price as listing_price,
           (SELECT image_path FROM listing_images WHERE listing_id = l.id ORDER BY is_primary DESC, id ASC LIMIT 1) as listing_thumb,
           u_other.id as other_user_id, u_other.full_name as other_name, u_other.city as other_city,
           (SELECT message FROM messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_message,
           (SELECT created_at FROM messages WHERE conversation_id = c.id ORDER BY id DESC LIMIT 1) as last_msg_time,
           (SELECT COUNT(*) FROM messages WHERE conversation_id = c.id AND sender_id != :uid AND is_read = 0) as unread_count
    FROM conversations c
    JOIN listings l ON c.listing_id = l.id
    JOIN users u_other ON (CASE WHEN c.buyer_id = :uid THEN c.seller_id ELSE c.buyer_id END) = u_other.id
    WHERE c.buyer_id = :uid OR c.seller_id = :uid
    ORDER BY c.updated_at DESC
");
$convStmt->execute([':uid' => $user['id']]);
$conversations = $convStmt->fetchAll();

// If no active conversation specified, default to first in list
if ($activeConvId <= 0 && !empty($conversations)) {
    $activeConvId = (int)$conversations[0]['id'];
}

// Fetch messages for active conversation
$activeConv = null;
$messages = [];

if ($activeConvId > 0) {
    // Security check: user MUST be buyer or seller in this conversation
    $authConv = $db->prepare("
        SELECT c.*, l.title as listing_title, l.price as listing_price, l.id as listing_id,
               u_other.id as other_id, u_other.full_name as other_name, u_other.city as other_city, u_other.mobile_number as other_phone
        FROM conversations c
        JOIN listings l ON c.listing_id = l.id
        JOIN users u_other ON (CASE WHEN c.buyer_id = :uid THEN c.seller_id ELSE c.buyer_id END) = u_other.id
        WHERE c.id = :cid AND (c.buyer_id = :uid OR c.seller_id = :uid)
        LIMIT 1
    ");
    $authConv->execute([':cid' => $activeConvId, ':uid' => $user['id']]);
    $activeConv = $authConv->fetch();

    if ($activeConv) {
        // Mark messages as read
        $db->prepare("UPDATE messages SET is_read = 1 WHERE conversation_id = :cid AND sender_id != :uid")
           ->execute([':cid' => $activeConvId, ':uid' => $user['id']]);

        // Get all messages
        $mStmt = $db->prepare("SELECT * FROM messages WHERE conversation_id = :cid ORDER BY id ASC");
        $mStmt->execute([':cid' => $activeConvId]);
        $messages = $mStmt->fetchAll();
    }
}

$pageTitle = 'Messages & Chats - Sargodha Mandi';
require_once __DIR__ . '/../includes/header.php';
?>

<div class="container py-4">
    <div class="mb-3">
        <h3 class="fw-bold mb-1">Buyer-Seller Messages</h3>
        <p class="text-muted small">Chat safely with local sellers and buyers in Sargodha, Shaheenabad & Sillanwali</p>
    </div>

    <div class="row g-3">
        <!-- Left: Conversations List -->
        <div class="col-12 col-md-5 col-lg-4">
            <div class="card border shadow-sm bg-white overflow-hidden" style="height: 600px; display: flex; flex-direction: column;">
                <div class="p-3 border-bottom bg-light">
                    <h6 class="fw-bold mb-0">Conversations (<?= count($conversations) ?>)</h6>
                </div>

                <div class="overflow-y-auto flex-grow-1">
                    <?php if (empty($conversations)): ?>
                        <div class="text-center p-4 text-muted small">
                            <i class="bi bi-chat-square-dots fs-3 text-muted mb-2 d-block"></i>
                            No active conversations yet. Visit any product listing and click <strong>"Send In-App Message"</strong> to start talking to sellers.
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($conversations as $c): ?>
                                <a href="/user/messages.php?conv=<?= $c['id'] ?>" 
                                   class="list-group-item list-group-item-action p-3 <?= ($c['id'] == $activeConvId) ? 'bg-light border-start border-success border-4' : '' ?>">
                                    <div class="d-flex justify-content-between align-items-start mb-1">
                                        <strong class="text-dark small text-truncate" style="max-width: 160px;"><?= e($c['other_name']) ?></strong>
                                        <small class="text-muted" style="font-size: 0.7rem;"><?= timeAgo($c['last_msg_time'] ?: $c['updated_at']) ?></small>
                                    </div>
                                    <div class="text-success small fw-semibold text-truncate mb-1">
                                        <i class="bi bi-tag"></i> <?= e($c['listing_title']) ?>
                                    </div>
                                    <div class="d-flex justify-content-between align-items-center">
                                        <small class="text-muted text-truncate" style="max-width: 180px;">
                                            <?= e($c['last_message'] ?: 'Started conversation') ?>
                                        </small>
                                        <?php if ($c['unread_count'] > 0): ?>
                                            <span class="badge bg-danger rounded-pill"><?= $c['unread_count'] ?></span>
                                        <?php endif; ?>
                                    </div>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Active Chat Window -->
        <div class="col-12 col-md-7 col-lg-8">
            <?php if (!$activeConv): ?>
                <div class="card border shadow-sm bg-white p-5 text-center text-muted" style="height: 600px; display: flex; align-items: center; justify-content: center;">
                    <i class="bi bi-chat-left-dots fs-1 mb-3 text-secondary"></i>
                    <h5>Select a conversation to start chatting</h5>
                    <p class="small text-muted">Choose an existing inquiry on the left or reach out to sellers directly from ads.</p>
                </div>
            <?php else: ?>
                <div class="chat-window shadow-sm" style="height: 600px;">
                    <!-- Chat Header -->
                    <div class="p-3 border-bottom bg-white d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center gap-2">
                            <div class="bg-success text-white rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 40px; height: 40px;">
                                <?= strtoupper(substr($activeConv['other_name'], 0, 1)) ?>
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0"><?= e($activeConv['other_name']) ?></h6>
                                <small class="text-muted">
                                    <i class="bi bi-geo-alt"></i> <?= e($activeConv['other_city']) ?> · 
                                    <a href="/product.php?id=<?= $activeConv['listing_id'] ?>" class="text-success text-decoration-none">
                                        <?= e($activeConv['listing_title']) ?> (<?= formatPKR($activeConv['listing_price']) ?>)
                                    </a>
                                </small>
                            </div>
                        </div>

                        <div>
                            <a href="/product.php?id=<?= $activeConv['listing_id'] ?>" class="btn btn-outline-secondary btn-sm" title="View Listing">
                                <i class="bi bi-box-arrow-up-right"></i>
                            </a>
                        </div>
                    </div>

                    <!-- Chat Messages Box (Polled via AJAX every 3.5s) -->
                    <div class="chat-messages" id="chat_messages_box">
                        <?php if (empty($messages)): ?>
                            <div class="text-center text-muted small my-auto">
                                Say hello! Inquire about condition, inspection time in <?= e($activeConv['other_city']) ?>, or price negotiations.
                            </div>
                        <?php else: ?>
                            <?php foreach ($messages as $m): ?>
                                <div class="chat-bubble <?= ($m['sender_id'] == $user['id']) ? 'mine' : 'other' ?>">
                                    <div><?= e($m['message']) ?></div>
                                    <div class="chat-time"><?= timeAgo($m['created_at']) ?></div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Chat Input Form -->
                    <div class="p-3 bg-white border-top">
                        <form id="chat_send_form" class="d-flex gap-2">
                            <input type="hidden" id="conversation_id" value="<?= $activeConv['id'] ?>">
                            <input type="text" id="chat_message_input" class="form-control" placeholder="Type a message to <?= e($activeConv['other_name']) ?>..." required autocomplete="off">
                            <button type="submit" class="btn btn-primary-green px-4 text-nowrap">
                                <i class="bi bi-send-fill me-1"></i> Send
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
