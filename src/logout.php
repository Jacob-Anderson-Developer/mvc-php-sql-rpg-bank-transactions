<?php
// Clear session if any
if (session_status() === PHP_SESSION_ACTIVE) {
	$_SESSION = [];
	session_destroy();
}

// Destroy the JWT cookie with same attributes used to set it
$secure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT']==443);
setcookie('jwt', '', [
	'expires' => time() - 3600,
	'path' => '/',
	'secure' => $secure,
	'httponly' => true,
	'samesite' => 'Strict'
]);

// Remove server-side refresh token if present and clear cookie
$oldRefresh = $_COOKIE['refresh_token'] ?? null;
if ($oldRefresh) {
	require_once __DIR__ . '/lib/refresh_token_store.php';
	remove_refresh_token($oldRefresh);
	setcookie('refresh_token', '', [
		'expires' => time() - 3600,
		'path' => '/',
		'secure' => $secure,
		'httponly' => true,
		'samesite' => 'Strict'
	]);
}

// Redirect to login page
header('Location: /Login.html');
exit;
?>