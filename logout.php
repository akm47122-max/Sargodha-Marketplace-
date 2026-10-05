<?php
/**
 * Sargodha Mandi - Logout
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/functions.php';

$_SESSION = [];

if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

session_destroy();

if (isset($_COOKIE['sm_remember'])) {
    setcookie('sm_remember', '', time() - 3600, '/');
}

redirect('/index.php', 'You have been logged out securely.', 'info');
