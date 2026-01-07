<?php
/**
 * Configuration File
 * Simple CRM System
 */

// Load environment variables from .env if exists
if (file_exists(dirname(__DIR__) . '/.env')) {
    $lines = file(dirname(__DIR__) . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0)
            continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
        putenv(trim($name) . '=' . trim($value));
    }
}

function env($key, $default = null)
{
    return $_ENV[$key] ?? getenv($key) ?: $default;
}

// Database Configuration
define('DB_HOST', env('DB_HOST', '127.0.0.1'));
define('DB_PORT', env('DB_PORT', '3306'));
define('DB_NAME', env('DB_NAME', 'crm_db'));
define('DB_USER', env('DB_USER', 'root'));
define('DB_PASS', env('DB_PASS', ''));
define('DB_CHARSET', env('DB_CHARSET', 'utf8mb4'));

// Application Configuration
define('APP_NAME', env('APP_NAME', 'CRM'));
define('APP_VERSION', '1.0.0');
define('APP_URL', env('APP_URL', 'http://localhost/CRM/public'));
define('APP_DEBUG', env('APP_DEBUG', 'false') === 'true' || env('APP_DEBUG') === true);

// Path Configuration
define('ROOT_PATH', dirname(__DIR__));
define('APP_PATH', ROOT_PATH . '/app');
define('PUBLIC_PATH', ROOT_PATH . '/public');
define('UPLOAD_PATH', PUBLIC_PATH . '/uploads');

// Security Configuration
define('SESSION_LIFETIME', (int) env('SESSION_LIFETIME', 3600)); // 1 hour in seconds
define('SESSION_IDLE_TIMEOUT', (int) env('SESSION_IDLE_TIMEOUT', 1800)); // 30 minutes
define('CSRF_TOKEN_NAME', 'csrf_token');
define('PASSWORD_HASH_ALGO', PASSWORD_BCRYPT);
define('PASSWORD_HASH_COST', 12); // Increased cost for better security

// HTTPS Enforcement
define('ENFORCE_HTTPS', env('ENFORCE_HTTPS', 'false') === 'true');
if (ENFORCE_HTTPS && (!isset($_SERVER['HTTPS']) || $_SERVER['HTTPS'] === 'off')) {
    header('Location: https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']);
    exit;
}

// Pagination
define('ITEMS_PER_PAGE', 10);

// Date & Time
define('TIMEZONE', 'UTC');
date_default_timezone_set(TIMEZONE);
define('DATE_FORMAT', 'Y-m-d');
define('DATETIME_FORMAT', 'Y-m-d H:i:s');

// Currency
define('CURRENCY_SYMBOL', '$');
define('CURRENCY_CODE', 'USD');

// Session Configuration (must be set BEFORE session_start)
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', ENFORCE_HTTPS ? 1 : 0);
    ini_set('session.cookie_samesite', 'Strict'); // More secure than Lax
    ini_set('session.gc_maxlifetime', SESSION_LIFETIME);
    ini_set('session.use_strict_mode', 1);
}

// Google OAuth Configuration
define('GOOGLE_CLIENT_ID', env('GOOGLE_CLIENT_ID', ''));
define('GOOGLE_CLIENT_SECRET', env('GOOGLE_CLIENT_SECRET', ''));
define('GOOGLE_REDIRECT_URI', env('GOOGLE_REDIRECT_URI', APP_URL . '/auth/google/callback'));

// Encryption Configuration (for SMTP passwords)
define('ENCRYPTION_KEY', env('ENCRYPTION_KEY', 'your-32-character-secret-key-here!!'));
define('ENCRYPTION_IV', env('ENCRYPTION_IV', '1234567890123456'));

// Error Reporting (Set to 0 in production)
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', ROOT_PATH . '/logs/error.log');
}