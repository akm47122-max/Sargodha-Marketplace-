<?php
/**
 * Sargodha Mandi - Subcategories Dropdown API
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

header('Content-Type: application/json');

$catId = (int)($_GET['category_id'] ?? 0);
if ($catId <= 0) {
    echo json_encode(['success' => false, 'subcategories' => []]);
    exit;
}

$db = getDB();
$stmt = $db->prepare("SELECT id, name, slug FROM subcategories WHERE category_id = :cid AND is_active = 1 ORDER BY name ASC");
$stmt->execute([':cid' => $catId]);
$subs = $stmt->fetchAll();

echo json_encode(['success' => true, 'subcategories' => $subs]);
