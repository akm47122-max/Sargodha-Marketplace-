<?php
/**
 * Auth Check Middleware
 * Ensures user is authenticated before accessing protected pages
 */

require_once __DIR__ . '/functions.php';

if (!isLoggedIn()) {
    $redirectUrl = $_SERVER['REQUEST_URI'] ?? '/user/dashboard.php';
    redirect('/login.php?redirect=' . urlencode($redirectUrl), 'Please login to your account to continue.', 'warning');
}
