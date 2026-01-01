<?php
require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->load();

// Self-signed JWT secret key
$jwtSecretKey = $_ENV['JWT_SECRET_KEY'];

/**
 * Require authentication by validating JWT.
 * Returns decoded token payload.
 */
function requireAuth() {
    global $jwtSecretKey;

    // Get JWT from Authorization header or cookie
    $headers = getallheaders();
    $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? '';
    $jwt = '';

    if ($authHeader && str_starts_with($authHeader, 'Bearer ')) {
        $jwt = str_replace('Bearer ', '', $authHeader);
    } elseif (isset($_COOKIE['jwt'])) {
        $jwt = $_COOKIE['jwt'];
    }

    // If request looks like API client (has Authorization header), return JSON 401
    $isApiClient = !empty($authHeader);

    if (!$jwt) {
        if ($isApiClient) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Access denied. No token provided.']);
            exit;
        }
        // Browser: redirect to login with reason
        header('Location: /Login.html?reason=missing_token');
        exit;
    }

    try {
        $decoded = JWT::decode($jwt, new Key($jwtSecretKey, 'HS256'));
        return $decoded;
    } catch (\Exception $e) {
        app_log('warn', 'JWT decode failed: ' . $e->getMessage());
        if ($isApiClient) {
            http_response_code(401);
            header('Content-Type: application/json');
            echo json_encode(['error' => 'Access denied. Invalid token.']);
            exit;
        }
        header('Location: /Login.html?reason=invalid_token');
        exit;
    }
}
?>
