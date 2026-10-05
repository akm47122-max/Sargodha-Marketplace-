<?php
/**
 * SARGODHAMART - Local Marketplace Configuration
 * Tagline: Buy • Sell • Connect
 * Production-ready for Hostinger / cPanel / MariaDB & MySQL
 */

// Start session securely if not already started
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_samesite', 'Lax');
    session_start();
}

// Environment & Debugging (Set to false on production)
define('APP_ENV', 'development');
define('APP_DEBUG', true);

if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Site Branding
define('SITE_NAME', 'SARGODHAMART');
define('SITE_TAGLINE', 'Buy • Sell • Jobs • Grow');
define('SITE_URL', 'http://' . ($_SERVER['HTTP_HOST'] ?? 'localhost:3000'));
define('ROOT_PATH', dirname(__DIR__));
define('UPLOAD_PATH', ROOT_PATH . '/uploads');

// Business Model: One-Time Lifetime Seller Activation Fee (Rs. 1,000)
// Normal product listings & job posts are 100% FREE & DIRECTLY PUBLIC after activation!
define('ONE_TIME_ACTIVATION_FEE', 1000); // PKR
define('OFFICIAL_PAYMENT_NUMBER', '03127453108');
define('OFFICIAL_ACCOUNT_NAME', 'Muhammad Akram Tayyab');
define('OFFICIAL_WHATSAPP_CHANNEL_URL', 'https://whatsapp.com/channel/0029Vb8bmhAGk1Fzze6iTX0g');
define('SUPPORT_PHONE', '03127453108');
define('SUPPORT_WHATSAPP', '923127453108');
define('SUPPORT_EMAIL', 'admin@sargodhamart.com');

// Supported Local Hubs
$LOCAL_CITIES = ['Sargodha', 'Shaheenabad', 'Sillanwali'];

// Database Credentials (Configure for Hostinger / cPanel here or via ENV)
define('DB_HOST', getenv('DB_HOST') ?: '127.0.0.1');
define('DB_PORT', getenv('DB_PORT') ?: '3306');
define('DB_NAME', getenv('DB_NAME') ?: 'sargodha_mandi');
define('DB_USER', getenv('DB_USER') ?: 'marketplace');
define('DB_PASS', getenv('DB_PASS') ?: 'Marketplace123!');
define('DB_CHARSET', 'utf8mb4');
