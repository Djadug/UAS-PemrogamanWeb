<?php
// Prevent direct access to this file
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}

// Load environment variables
require_once __DIR__ . '/../vendor/autoload.php';
$dotenv = Dotenv\Dotenv::createImmutable(__DIR__ . '/..');
$dotenv->load();

// Base Configuration
define('SITE_NAME', 'EcoTrack');
define('SITE_URL', 'http://localhost/EcoTrack'); // Pastikan ini benar
define('ADMIN_EMAIL', 'nicoadmin@ecotrack.com');

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ecotrack');

// Email Configuration
define('SMTP_HOST', $_ENV['SMTP_HOST']);
define('SMTP_USER', $_ENV['SMTP_USER']);
define('SMTP_PASS', $_ENV['SMTP_PASS']);
define('SMTP_PORT', $_ENV['SMTP_PORT']);

// Path Configuration


// Session Configuration
define('SESSION_LIFETIME', 7200); // 2 hours
define('SESSION_NAME', 'ecotrack_session');

// Security Configuration
define('HASH_COST', 12); // For password hashing
define('TOKEN_LIFETIME', 3600); // 1 hour for reset tokens

// Application Settings
define('ITEMS_PER_PAGE', 10);
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['jpg', 'jpeg', 'png', 'gif']);

// Error Reporting
if ($_ENV['APP_ENV'] === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
}

// Time Zone
date_default_timezone_set('Asia/Jakarta');

// Global Functions
function url($path = '') {
    return SITE_URL . '/' . ltrim($path, '/');
}

function asset($path = '') {
    return url('assets/' . ltrim($path, '/'));
}

function redirect($path = '') {
    header('Location: ' . url($path));
    exit;
}

function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

// Initialize Session
session_start([
    'cookie_lifetime' => SESSION_LIFETIME,
    'name' => SESSION_NAME,
    'cookie_httponly' => true,
    'cookie_secure' => $_ENV['APP_ENV'] === 'production',
    'cookie_samesite' => 'Lax'
]);

// Set Default Headers
header('X-Frame-Options: SAMEORIGIN');
header('X-XSS-Protection: 1; mode=block');
header('X-Content-Type-Options: nosniff');

// Error Handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    if (!(error_reporting() & $errno)) {
        return false;
    }
    
    $error = [
        'type' => $errno,
        'message' => $errstr,
        'file' => $errfile,
        'line' => $errline
    ];
    
    if ($_ENV['APP_ENV'] === 'development') {
        throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
    } else {
        error_log(json_encode($error));
    }
    
    return true;
});
