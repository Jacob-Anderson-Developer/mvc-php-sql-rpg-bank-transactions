<?php
// Endpoint to exchange a refresh token for a new access token (and rotate refresh token)
require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../lib/refresh_token_store.php';

use Firebase\JWT\JWT;

$jwtSecretKey = $_ENV['JWT_SECRET_KEY'] ?? null;
$accessTtl = intval($_ENV['JWT_ACCESS_TTL'] ?? 900); // default TTL: 15 minutes (seconds)
$refreshTtl = intval($_ENV['JWT_REFRESH_TTL'] ?? 60*60*24*30); // 30 days default for refresh tokens

// Accept POST with refresh_token in cookie or JSON body
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true) ?: $_POST;
$refreshToken = $_COOKIE['refresh_token'] ?? ($input['refresh_token'] ?? null);
if (!$refreshToken) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'No refresh token provided']);
    exit;
}

// Optional: client may send username for additional check
$username = $input['user_name'] ?? null;

// Prune expired tokens first
prune_expired_refresh_tokens();

$entry = verify_and_consume_refresh_token($refreshToken, $username);
if (!$entry) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Invalid or expired refresh token']);
    exit;
}

// Create new access token (short lived)
$now = time();
$payload = [
    'iss' => 'http://localhost',
    'aud' => 'http://localhost',
    'iat' => $now,
    'exp' => $now + $accessTtl,
    'user_name' => $entry['username']
];
$newJwt = JWT::encode($payload, $jwtSecretKey, 'HS256');

// Create new refresh token (rotate)
$newRefreshToken = bin2hex(random_bytes(32));
$expiresAt = $now + $refreshTtl;
store_refresh_token($newRefreshToken, $entry['username'], $expiresAt);

// Set cookies for new refresh + access cookie (access in cookie is optional; we set jwt cookie for browser convenience)
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']==443);
setcookie('jwt', $newJwt, [
    'expires' => $now + $accessTtl,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Strict'
]);
setcookie('refresh_token', $newRefreshToken, [
    'expires' => $expiresAt,
    'path' => '/',
    'secure' => $secure,
    'httponly' => true,
    'samesite' => 'Strict'
]);

header('Content-Type: application/json');
echo json_encode(['access_token' => $newJwt, 'expires_in' => $accessTtl]);
exit;
?>