<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../authMiddleware.php';
require_once __DIR__ . '/../models/TransactionModel.php';

use Dotenv\Dotenv;

// Load environment variables
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../config');
$dotenv->load();

// Enforce authentication
$decodedToken = requireAuth();
$user_name = $decodedToken->user_name ?? 'User';

$dsn = $_ENV['DB_DSN'];
$dbUser = $_ENV['DB_USER'];
$dbPassword = $_ENV['DB_PASSWORD'];

$balance = 0;
$balanceDisplay = '';
$message = '';
$records = [];

try {
    $pdo = new PDO($dsn, $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Get transactions via model (filtered by logged-in user)
    $model = new TransactionModel($pdo);
    $records = $model->getTransactions($user_name);

    // Get current balance (call stored procedure with amount = 0)
    $library = $_ENV['DB_LIBRARY'];
    $sql = "CALL {$library}.ProcessTransaction(?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $amount = 0.00;
    $type = '';
    $balance = 0;
    $procMessage = '';

    $stmt->bindParam(1, $amount, PDO::PARAM_STR);
    $stmt->bindParam(2, $type, PDO::PARAM_STR);
    $stmt->bindParam(3, $user_name, PDO::PARAM_STR);
    $stmt->bindParam(4, $balance, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
    $stmt->bindParam(5, $procMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);

    $stmt->execute();

    if ($balance !== null && $balance !== '') {
        $balanceDisplay = number_format($balance, 2);
    }

} catch (PDOException $e) {
    $message = "Database error: " . htmlspecialchars($e->getMessage());
}

// Include the view
require __DIR__ . '/../views/history.php';
?>
