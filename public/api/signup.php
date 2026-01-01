<?php
require_once __DIR__ . '/../../src/bootstrap.php';

header('Content-Type: application/json');

// Read JSON input
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || !isset($input['user_name'], $input['password'], $input['email'], $input['first_name'], $input['last_name'])) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

$user_name = $input['user_name'];
$password = $input['password'];
$email = $input['email'];
$first_name = $input['first_name'];
$last_name = $input['last_name'];

try {
    $dsn = $_ENV['DB_DSN'];
    $dbUser = $_ENV['DB_USER'];
    $dbPassword = $_ENV['DB_PASSWORD'];

    $pdo = new PDO($dsn, $dbUser, $dbPassword);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Check if username already exists
    $library = $_ENV['DB_LIBRARY'];
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM {$library}.user_logins WHERE LOWER(username) = LOWER(:user_name)");
    $stmt->bindParam(':user_name', $user_name, PDO::PARAM_STR);
    $stmt->execute();
    $count = $stmt->fetchColumn();

    if ($count > 0) {
        http_response_code(409);
        echo json_encode(['status' => 'error', 'message' => 'Username already exists']);
        exit;
    }

    // Hash password and insert new user
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $stmt = $pdo->prepare("INSERT INTO {$library}.user_logins (username, pword, email, first_name, last_name) VALUES (:user_name, :password, :email, :first_name, :last_name)");
    $stmt->bindParam(':user_name', $user_name, PDO::PARAM_STR);
    $stmt->bindParam(':password', $hashedPassword, PDO::PARAM_STR);
    $stmt->bindParam(':email', $email, PDO::PARAM_STR);
    $stmt->bindParam(':first_name', $first_name, PDO::PARAM_STR);
    $stmt->bindParam(':last_name', $last_name, PDO::PARAM_STR);
    $stmt->execute();

    echo json_encode(['status' => 'success', 'message' => 'User registered successfully']);

} catch (PDOException $e) {
    app_log('error', 'Signup error: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'Database error occurred']);
}
?>
