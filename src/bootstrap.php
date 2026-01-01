<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Dotenv\Dotenv;

// Load environment variables once
$dotenv = Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->safeLoad();

// Control error display by environment (default production)
if (($_ENV['APP_ENV'] ?? 'production') !== 'development') {
    ini_set('display_errors', '0');
    ini_set('display_startup_errors', '0');
} else {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
}
error_reporting(E_ALL);

// Simple logger
function app_log($level, $message) {
    $logDir = __DIR__ . '/../logs';
    if (!is_dir($logDir)) {
        @mkdir($logDir, 0755, true);
    }
    $logFile = $logDir . '/app.log';
    $entry = sprintf("[%s] %s: %s\n", date('Y-m-d H:i:s'), strtoupper($level), $message);
    @file_put_contents($logFile, $entry, FILE_APPEND | LOCK_EX);
}

// Convert PHP errors to exceptions so the exception handler catches them
set_error_handler(function($severity, $message, $file, $line) {
    if (!(error_reporting() & $severity)) {
        return false; // respect @ operator
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

// Global exception handler: log and show friendly message
set_exception_handler(function($e) {
    if (function_exists('app_log')) {
        app_log('error', $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine());
    } else {
        error_log($e->getMessage());
    }

    http_response_code(500);
    $isJson = !empty($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false;
    $isDev = (($_ENV['APP_ENV'] ?? 'production') === 'development');

    if ($isJson) {
        header('Content-Type: application/json');
        if ($isDev) {
            echo json_encode(['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        } else {
            echo json_encode(['error' => 'Internal server error']);
        }
    } else {
        if ($isDev) {
            echo '<pre>Exception: ' . htmlspecialchars($e->getMessage()) . "\n\n" . htmlspecialchars($e->getTraceAsString()) . '</pre>';
        } else {
            echo "<h1>Something went wrong</h1><p>An unexpected error occurred. Please try again later.</p>";
        }
    }
    exit;
});

?>