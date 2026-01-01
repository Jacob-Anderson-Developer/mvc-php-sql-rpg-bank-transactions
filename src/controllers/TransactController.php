<?php

require_once __DIR__ . '/../../vendor/autoload.php';
require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../authMiddleware.php';
require_once __DIR__ . '/../utils/csrf.php';

use Dotenv\Dotenv;

// Load environment variables (bootstrap already loaded but safe to ensure)
$dotenv = Dotenv::createImmutable(__DIR__ . '/../../config');
$dotenv->safeLoad();

// Enforce authentication
$decodedToken = requireAuth();
$user_name = $decodedToken->user_name ?? 'User';

// Prepare CSRF token for form
$csrf_token = generate_csrf_token();

$dsn = $_ENV['DB_DSN'];
$dbUser = $_ENV['DB_USER'];
$dbPassword = $_ENV['DB_PASSWORD'];

$balanceDisplay = '';
$message = '';
$messageSuccess = false;  // flag for message styling
$amountInput = '';
$amount = 0.00;
$type = '';
$balance = 0;

try {
    $pdo = new PDO($dsn, $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Handle POST submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Validate CSRF token first
        $postedToken = $_POST['csrf_token'] ?? null;
        if (!validate_csrf_token($postedToken)) {
            $message = 'Invalid form submission (CSRF).';
        } else {
            $amount = $_POST['amount'] ?? null;
            $typeRaw = $_POST['transactionType'] ?? null;

            // Map form values to single-character type codes
            $typeMap = ['deposit' => 'D', 'withdrawal' => 'W'];
            $type = $typeMap[strtolower($typeRaw)] ?? '';

            if (!$amount || !$type) {
                $message = "Amount and transaction type are required.";
            } elseif (!is_numeric($amount) || $amount <= 0) {
                $message = "Amount must be a positive number.";
                $amountInput = htmlspecialchars($amount);
            } else {
            // Call stored procedure
            $library = $_ENV['DB_LIBRARY'];
            $sql = "CALL {$library}.ProcessTransaction(?, ?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);

            $balance = 0;
            $procMessage = '';
            $stmt->bindParam(1, $amount, PDO::PARAM_STR);
            $stmt->bindParam(2, $type, PDO::PARAM_STR);
            $stmt->bindParam(3, $user_name, PDO::PARAM_STR);
            $stmt->bindParam(4, $balance, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);
            $stmt->bindParam(5, $procMessage, PDO::PARAM_STR | PDO::PARAM_INPUT_OUTPUT);

            $stmt->execute();

            // Set message and determine success based on message content
            if ($balance !== null && $balance !== '') {
                $balanceDisplay = number_format($balance, 2);
                $message = trim($procMessage) ?: "Transaction successful! New balance: $" . $balanceDisplay;
            } else {
                $message = trim($procMessage) ?: "Transaction failed";
            }
            
            // Check message text for error keywords (case-insensitive)
            $messageSuccess = !(
                stripos($message, 'error') !== false ||
                stripos($message, 'failed') !== false ||
                stripos($message, 'insufficient') !== false ||
                stripos($message, 'exceeds') !== false ||
                stripos($message, 'invalid') !== false
            );
            $amountInput = ''; // Clear field on success
        }
    }
    }

    // Get current balance (call with amount = 0)
    $library = $_ENV['DB_LIBRARY'];
    $sql = "CALL {$library}.ProcessTransaction(?, ?, ?, ?, ?)";
    $stmt = $pdo->prepare($sql);
    $queryAmount = 0.00;
    $queryType = '';
    $balance = 0;
    $procMessage = '';

    $stmt->bindParam(1, $queryAmount, PDO::PARAM_STR);
    $stmt->bindParam(2, $queryType, PDO::PARAM_STR);
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
require __DIR__ . '/../views/transact.php';
?>
