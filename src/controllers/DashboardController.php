<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../authMiddleware.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../config');
$dotenv->load();

// Enforce authentication
$decodedToken = requireAuth();
$user_name = $decodedToken->user_name ?? 'User';

// Include the view
require __DIR__ . '/../views/dashboard.php';
?>
