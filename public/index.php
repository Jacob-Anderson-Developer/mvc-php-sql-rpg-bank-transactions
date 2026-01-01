<?php
// Simple router / front-controller.
// If you run the built-in server with document root = public, set $base = ''.
// If you serve the app under /BankApplication, set $base = '/BankApplication'.
$base = ''; // change to '/BankApplication' if you need that prefix

// Bootstrap: load env, error handlers, logger
require_once __DIR__ . '/../src/bootstrap.php';

// Fallback MIME resolver for environments without mime_content_type
if (!function_exists('mime_content_type')) {
    function mime_content_type($filename) {
        $map = [
            'html' => 'text/html',
            'htm'  => 'text/html',
            'css'  => 'text/css',
            'js'   => 'application/javascript',
            'json' => 'application/json',
            'png'  => 'image/png',
            'jpg'  => 'image/jpeg',
            'jpeg' => 'image/jpeg',
            'gif'  => 'image/gif',
            'svg'  => 'image/svg+xml',
            'txt'  => 'text/plain'
        ];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        return $map[$ext] ?? 'application/octet-stream';
    }
}

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
if ($base && strpos($uri, $base) === 0) {
    $uri = substr($uri, strlen($base));
}
$path = '/' . trim($uri, '/');
if ($path === '/' || $path === '') {
    // default: show login page
    header('Location: ' . ($base ?: '') . '/Login.html');
    exit;
}

$routes = [
    '/transact'   => __DIR__ . '/../src/controllers/TransactController.php',
    '/history'    => __DIR__ . '/../src/controllers/HistoryController.php',
    '/dashboard'  => __DIR__ . '/../src/controllers/DashboardController.php',
    '/logout'     => __DIR__ . '/../src/logout.php',
    '/login'      => __DIR__ . '/../src/Login_Process.php',
    '/token/refresh' => __DIR__ . '/../src/controllers/RefreshTokenController.php',
    '/api/signup' => __DIR__ . '/api/signup.php',
    '/api/history' => __DIR__ . '/api/history.php',
];

// route to PHP handlers
if (isset($routes[$path])) {
    require $routes[$path];
    exit;
}

// serve static files from public/
$static = __DIR__ . $path;
if (is_file($static)) {
    header('Content-Type: ' . (mime_content_type($static) ?: 'text/plain'));
    readfile($static);
    exit;
}

http_response_code(404);
echo 'Not found';