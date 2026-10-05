<?php
/**
 * Sargodha Mandi - AJAX Toggle Favorite
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    echo json_encode(['success' => false, 'redirect' => '/login.php']);
    exit;
}

$user = currentUser();
$db = getDB();

$raw = file_get_contents('php://input');
$data = json_decode($raw, true) ?: [];
$listingId = (int)($data['listing_id'] ?? 0);

if ($listingId <= 0) {
    echo json_encode(['success' => false, 'error' => 'Invalid listing ID']);
    exit;
}

// Check if already favorited
$chk = $db->prepare("SELECT id FROM favorites WHERE user_id = :uid AND listing_id = :lid");
$chk->execute([':uid' => $user['id'], ':lid' => $listingId]);
$favId = $chk->fetchColumn();

if ($favId) {
    // Remove
    $db->prepare("DELETE FROM favorites WHERE id = :id")->execute([':id' => $favId]);
    echo json_encode(['success' => true, 'is_favorite' => false]);
} else {
    // Add
    $db->prepare("INSERT INTO favorites (user_id, listing_id) VALUES (:uid, :lid)")
       ->execute([':uid' => $user['id'], ':lid' => $listingId]);
    echo json_encode(['success' => true, 'is_favorite' => true]);
}
