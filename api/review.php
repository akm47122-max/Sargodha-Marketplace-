<?php
/**
 * Sargodha Mandi - Submit Seller Rating & Review
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/functions.php';
require_once __DIR__ . '/../includes/auth_check.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    redirect('/index.php');
}

verifyCsrfOrDie();

$user = currentUser();
$sellerId = (int)($_POST['seller_id'] ?? 0);
$listingId = (int)($_POST['listing_id'] ?? 0);
$rating = (int)($_POST['rating'] ?? 5);
$reviewText = trim($_POST['review_text'] ?? '');

$redirectUrl = $listingId > 0 ? "/product.php?id={$listingId}" : "/seller.php?id={$sellerId}";

// Prevent self-review
if ($sellerId === $user['id']) {
    redirect($redirectUrl, 'You cannot review yourself.', 'danger');
}

if ($rating < 1 || $rating > 5 || empty($reviewText)) {
    redirect($redirectUrl, 'Please select a valid rating (1-5 stars) and write your review.', 'danger');
}

$db = getDB();

// Prevent duplicate reviews by same reviewer
$chk = $db->prepare("SELECT id FROM reviews WHERE seller_id = :sid AND reviewer_id = :rid LIMIT 1");
$chk->execute([':sid' => $sellerId, ':rid' => $user['id']]);
if ($chk->fetch()) {
    redirect($redirectUrl, 'You have already submitted a review for this seller.', 'warning');
}

// Insert review
$stmt = $db->prepare("
    INSERT INTO reviews (listing_id, seller_id, reviewer_id, rating, review_text, status) 
    VALUES (:lid, :sid, :rid, :rat, :rev, 'published')
");
$stmt->execute([
    ':lid' => $listingId ?: null,
    ':sid' => $sellerId,
    ':rid' => $user['id'],
    ':rat' => $rating,
    ':rev' => $reviewText
]);

// Notification to seller
createNotification(
    $sellerId,
    'New Rating & Review Received!',
    "{$user['full_name']} gave you a {$rating}-star rating: '{$reviewText}'",
    $redirectUrl,
    'review'
);

redirect($redirectUrl, 'Thank you! Your review has been published.', 'success');
