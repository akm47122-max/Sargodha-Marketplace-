<?php
/**
 * Database Connection using PDO with Prepared Statements
 * Supports MySQL / MariaDB (Production Hostinger) with graceful SQLite fallback
 */

require_once __DIR__ . '/config.php';

function getDB(): PDO {
    static $pdo = null;

    if ($pdo !== null) {
        return $pdo;
    }

    $host = DB_HOST;
    $port = DB_PORT;
    $dbname = DB_NAME;
    $user = DB_USER;
    $pass = DB_PASS;
    $charset = DB_CHARSET;

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    try {
        // Primary: MySQL / MariaDB connection
        $dsn = "mysql:host={$host};port={$port};dbname={$dbname};charset={$charset}";
        $pdo = new PDO($dsn, $user, $pass, $options);
        return $pdo;
    } catch (PDOException $e) {
        // If MySQL fails, log error and try SQLite fallback for local test portability
        error_log("MySQL Connection failed: " . $e->getMessage() . " - Attempting SQLite fallback...");

        $sqlitePath = ROOT_PATH . '/database.sqlite';
        try {
            $pdo = new PDO("sqlite:" . $sqlitePath, null, null, $options);
            return $pdo;
        } catch (PDOException $se) {
            die("Database connection failed. Please ensure MySQL is running or configure credentials in config/config.php. Error: " . htmlspecialchars($e->getMessage()));
        }
    }
}
