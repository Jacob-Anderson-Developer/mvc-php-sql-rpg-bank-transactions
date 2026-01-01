<?php
// DB-backed refresh token store using PDO.
// Functions provided (same API as before):
// - store_refresh_token($token, $username, $expiresAt)
// - verify_and_consume_refresh_token($token, $username=null)
// - remove_refresh_token($token)
// - prune_expired_refresh_tokens()

require_once __DIR__ . '/../bootstrap.php';

use Dotenv\Dotenv;

// Ensure env loaded
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../config');
$dotenv->safeLoad();

function get_refresh_pdo() {
    static $pdo = null;
    if ($pdo !== null) return $pdo;

    $dsn = $_ENV['DB_DSN'] ?? null;
    $user = $_ENV['DB_USER'] ?? null;
    $password = $_ENV['DB_PASSWORD'] ?? null;

    if (!$dsn) {
        throw new RuntimeException('DB_DSN not configured for refresh token store');
    }

    // Create PDO. Use ATTR_ERRMODE EXCEPTION
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Table creation is managed externally for this environment
    // Ensure your DBA has created `<your-library>.refresh_tokens` with appropriate types.

    return $pdo;
}

function store_refresh_token($token, $username, $expiresAt) {
    $pdo = get_refresh_pdo();
    $hash = hash('sha256', $token);
    $now = time();

    $library = $_ENV['DB_LIBRARY'];
    $sql = "INSERT INTO {$library}.refresh_tokens (token_hash, username, expires_at, created_at) VALUES (:h, :u, :e, :c)";
    $stmt = $pdo->prepare($sql);
    try {
        $stmt->execute([':h' => $hash, ':u' => $username, ':e' => intval($expiresAt), ':c' => $now]);
    } catch (PDOException $e) {
        // If insertion fails due to duplicate key, replace the existing row (upsert) where supported
        app_log('warn', 'store_refresh_token insert failed: ' . $e->getMessage());
        try {
            // Try update fallback
            $upSql = "UPDATE {$library}.refresh_tokens SET username = :u, expires_at = :e, created_at = :c WHERE token_hash = :h";
            $up = $pdo->prepare($upSql);
            $up->execute([':h'=>$hash, ':u'=>$username, ':e'=>intval($expiresAt), ':c'=>$now]);
        } catch (PDOException $e2) {
            app_log('error', 'store_refresh_token upsert failed: ' . $e2->getMessage());
            throw $e2;
        }
    }
}

function verify_and_consume_refresh_token($token, $username=null) {
    $pdo = get_refresh_pdo();
    $hash = hash('sha256', $token);

    // Use transaction to read+delete atomically
    try {
        $pdo->beginTransaction();
        $library = $_ENV['DB_LIBRARY'];
        $sql = "SELECT username, expires_at, created_at FROM {$library}.refresh_tokens WHERE token_hash = :h FOR UPDATE";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':h' => $hash]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$row) {
            $pdo->rollBack();
            return false;
        }
        if ($username !== null && $row['username'] !== $username) {
            $pdo->rollBack();
            return false;
        }
        if (time() > intval($row['expires_at'])) {
            // expired - remove
            $del = $pdo->prepare("DELETE FROM {$library}.refresh_tokens WHERE token_hash = :h");
            $del->execute([':h' => $hash]);
            $pdo->commit();
            return false;
        }

        // consume (delete) to rotate single-use tokens
        $del = $pdo->prepare("DELETE FROM {$library}.refresh_tokens WHERE token_hash = :h");
        $del->execute([':h' => $hash]);
        $pdo->commit();

        return ['username' => $row['username'], 'expires_at' => intval($row['expires_at']), 'created_at' => intval($row['created_at'])];
    } catch (PDOException $e) {
        if ($pdo->inTransaction()) $pdo->rollBack();
        app_log('error', 'verify_and_consume_refresh_token error: ' . $e->getMessage());
        return false;
    }
}

function remove_refresh_token($token) {
    $pdo = get_refresh_pdo();
    $hash = hash('sha256', $token);
    $library = $_ENV['DB_LIBRARY'];
    $stmt = $pdo->prepare("DELETE FROM {$library}.refresh_tokens WHERE token_hash = :h");
    $stmt->execute([':h' => $hash]);
}

function prune_expired_refresh_tokens() {
    $pdo = get_refresh_pdo();
    try {
        $library = $_ENV['DB_LIBRARY'];
        $stmt = $pdo->prepare("DELETE FROM {$library}.refresh_tokens WHERE expires_at < :now");
        $stmt->execute([':now' => time()]);
    } catch (PDOException $e) {
        app_log('warn', 'prune_expired_refresh_tokens failed: ' . $e->getMessage());
    }
}

?>