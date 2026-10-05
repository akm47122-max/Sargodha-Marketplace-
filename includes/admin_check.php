<?php
/**
 * Admin Role Authorization Middleware
 * Ensures user is logged in AND has admin or super_admin role
 */

require_once __DIR__ . '/functions.php';

if (!isLoggedIn()) {
    redirect('/admin/login.php', 'Please login with administrator credentials.', 'warning');
}

if (!isAdmin()) {
    http_response_code(403);
    die("Access Denied: You do not have administrative privileges to access this area.");
}
