<?php
ob_start();
header('Content-Type: text/html'); // redirect on success

require_once __DIR__ . '/../vendor/autoload.php';

use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../config');
$dotenv->load();

$jwtSecretKey = $_ENV['JWT_SECRET_KEY'];
$dsn = $_ENV['DB_DSN'];
$user = $_ENV['DB_USER'];
$dbPassword = $_ENV['DB_PASSWORD'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $user_name = $_POST['user_name'] ?? null;
    $password = $_POST['password'] ?? null;

    if (!$user_name || !$password) {
        echo "<p>Username and password are required. <a href='../Login.html'>Try again</a>.</p>";
        exit;
    }

    try {
        $pdo = new PDO($dsn, $user, $dbPassword);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $library = $_ENV['DB_LIBRARY'];
        $stmt = $pdo->prepare("SELECT username, pword FROM {$library}.user_logins WHERE LOWER(username) = LOWER(:user_name)");
        $stmt->bindParam(':user_name', $user_name, PDO::PARAM_STR);
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($row && password_verify($password, $row['PWORD'])) {
            // JWT payload
            $accessTtl = intval($_ENV['JWT_ACCESS_TTL'] ?? 900); // default TTL: 15 minutes (seconds)
            $now = time();
            $payload = [
                'iss' => 'http://localhost',
                'aud' => 'http://localhost',
                'iat' => $now,
                'exp' => $now + $accessTtl,
                'user_name' => $row['USERNAME']
            ];

            $jwt = JWT::encode($payload, $jwtSecretKey, 'HS256');

            // Set JWT cookie with secure attributes when possible
            $secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']==443);
            setcookie('jwt', $jwt, [
                'expires' => $now + $accessTtl,
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            // Create and store a refresh token (rotateable)
            require_once __DIR__ . '/lib/refresh_token_store.php';
            $refreshToken = bin2hex(random_bytes(32));
            $refreshTtl = intval($_ENV['JWT_REFRESH_TTL'] ?? 60*60*24*30);
            $expiresAt = time() + $refreshTtl;
            store_refresh_token($refreshToken, $row['USERNAME'], $expiresAt);
            setcookie('refresh_token', $refreshToken, [
                'expires' => $expiresAt,
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => 'Strict'
            ]);

            // Redirect to route-based dashboard
            header('Location: /dashboard');
            exit;
        } else {
            echo "<p>Invalid username or password. <a href='/Login.html'>Try again</a>.</p>";
        }
    } catch (PDOException $e) {
        echo "<p>Database error: " . htmlspecialchars($e->getMessage()) . "</p>";
    } finally {
        $pdo = null;
    }
}
?>
