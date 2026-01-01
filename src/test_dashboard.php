<?php
// src/test_dashboard.php

require_once __DIR__ . '/../vendor/autoload.php';
use Firebase\JWT\JWT;

// Config
$jwtSecretKey = 'your-secret-key';

// Generate a test JWT
$payload = [
    'uid' => '12345',
    'email' => 'testuser@example.com',
    'iat' => time(),
    'exp' => time() + 3600
];

$jwt = JWT::encode($payload, $jwtSecretKey, 'HS256');

// Redirect to dashboard.php with token as GET parameter
// (Only for local testing!)
header('Location: dashboard.php?jwt=' . $jwt);
exit;
