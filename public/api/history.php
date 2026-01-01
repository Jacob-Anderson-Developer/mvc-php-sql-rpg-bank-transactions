<?php
// public/api/history.php — JSON API for transaction history

require_once __DIR__ . '/../../src/bootstrap.php';
require_once __DIR__ . '/../../src/authMiddleware.php';
require_once __DIR__ . '/../../src/models/TransactionModel.php';

header('Content-Type: application/json');

try {
    // Enforce authentication
    $decodedToken = requireAuth();
    $user_name = $decodedToken->user_name ?? null;

    if (!$user_name) {
        http_response_code(401);
        echo json_encode(['error' => 'Unauthorized']);
        exit;
    }

    // Create PDO connection
    $dsn = $_ENV['DB_DSN'];
    $dbUser = $_ENV['DB_USER'];
    $dbPassword = $_ENV['DB_PASSWORD'];
    $pdo = new PDO($dsn, $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch transactions for authenticated user
    $model = new TransactionModel($pdo);
    $records = $model->getTransactions($user_name);

    // Also fetch current balance
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

    // Return JSON response with transactions and balance
    echo json_encode([
        'success' => true,
        'transactions' => $records,
        'balance' => $balance ? number_format($balance, 2) : '0.00'
    ]);

} catch (Exception $e) {
    app_log('error', 'History API error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => 'Failed to retrieve transactions: ' . $e->getMessage()
    ]);
}
?>
