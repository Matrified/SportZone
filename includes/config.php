<?php
/**
 * SportZone - Global Configuration
 * Include this file FIRST on every page (before any HTML output).
 */

// Start session for cart/auth state management
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Error reporting (turn off display_errors in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Site-wide constants
define('SITE_NAME', 'SportZone');
define('BASE_URL', '/SportZone/');           // adjust if your XAMPP folder name differs
define('UPLOAD_PATH', __DIR__ . '/../assets/images/products/');
define('UPLOAD_URL', BASE_URL . 'assets/images/products/');
define('SHIPPING_FEE', 10.00);
define('CURRENCY', 'RM');

// Database credentials (XAMPP defaults)
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'sportzone_db');
