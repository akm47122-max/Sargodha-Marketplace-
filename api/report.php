<?php
/**
 * Sargodha Mandi - Report Listing Action
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}

verifyCsrfOrDie();

$listingId = (int)($_POST['listing_id'] ?? 0);
$reason = trim($_POST['reason'] ?? '');
$details = trim($_POST['details'] ?? '');
$reporterId = isLoggedIn() ? currentUser()['id'] : null;

$allowedReasons = ['Scam/Fraud', 'Wrong information', 'Duplicate listing', 'Prohibited item', 'Offensive content', 'Other'];

if ($listingId <= 0 || !in_array($reason, $allowedReasons, true) || empty($details)) {
    redirect("/product.php?id={$listingId}", 'Please select a valid report reason and provide details.', 'danger');
}

$db = getDB();
$stmt = $db->prepare("INSERT INTO reports (listing_id, reporter_id, reason, details, status) VALUES (:lid, :rid, :reason, :details, 'pending')");
$stmt->execute([
    ':lid' => $listingId,
    ':rid' => $reporterId,
    ':reason' => $reason,
    ':details' => $details
]);

if (file_exists(__DIR__ . '/../services/AI/AIEventLogger.php')) {
    require_once __DIR__ . '/../services/AI/AIEventLogger.php';
    AIEventLogger::logEvent('LISTING_REPORTED', 'listing', $listingId, (int)($reporterId ?? 0), [
        'reason' => $reason,
        'details' => mb_substr($details, 0, 200)
    ]);
}

// Dispatch admin email notification for reported content via Resend
if (class_exists('ResendMailer')) {
    $listingStmt = $db->prepare("SELECT title FROM listings WHERE id = :id LIMIT 1");
    $listingStmt->execute([':id' => $listingId]);
    $listingTitle = $listingStmt->fetchColumn() ?: "Listing #{$listingId}";

    $reporterName = 'Anonymous Visitor';
    if ($reporterId) {
        $user = currentUser();
        if ($user) $reporterName = $user['full_name'] . ' (' . $user['email'] . ')';
    }

    ResendMailer::sendAdminNotificationEmail('new_report', [
        'targetTitle' => $listingTitle,
        'reason' => $reason,
        'reporterName' => $reporterName,
        'details' => $details
    ]);
}

redirect("/product.php?id={$listingId}", 'Thank you. Your report has been submitted to the administration for review.', 'success');
