<?php
/**
 * Application Entry Point
 * CRM System
 */

// Load configuration first (contains session settings)
require_once __DIR__ . '/../config/config.php';

// Force error reporting in case server silences it
if (APP_DEBUG) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    ini_set('display_startup_errors', 1);
}

// Quick Health Check bypassing router
if (isset($_GET['check_health'])) {
    require_once __DIR__ . '/../check_health.php';
    exit;
}

// Start session after config is loaded
session_start();

// Autoload core classes
spl_autoload_register(function ($class) {
    if (file_exists(APP_PATH . '/core/ErrorHandler.php')) {
        require_once APP_PATH . '/core/ErrorHandler.php';
        if ($class === 'ErrorHandler')
            return;
    }

    $paths = [
        APP_PATH . '/core/' . $class . '.php',
        APP_PATH . '/helpers/' . $class . '.php',
        APP_PATH . '/models/' . $class . '.php',
        APP_PATH . '/controllers/' . $class . '.php'
    ];

    foreach ($paths as $path) {
        if (file_exists($path)) {
            require_once $path;
            return;
        }
    }
});

// Register Global Error Handler
ErrorHandler::register();

// Initialize router
$router = new Router();
