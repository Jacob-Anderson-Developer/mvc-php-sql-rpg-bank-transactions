<?php
// src/models/TransactionModel.php

require_once __DIR__ . '/../../vendor/autoload.php';
use Dotenv\Dotenv;

class TransactionModel {

    private $pdo;

    /**
     * Accept an existing PDO (preferred) or create one from env when omitted.
     */
    public function __construct(?\PDO $pdo = null) {
        if ($pdo instanceof PDO) {
            $this->pdo = $pdo;
            return;
        }

        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../config');
        $dotenv->load();

        $dsn = $_ENV['DB_DSN'];
        $user = $_ENV['DB_USER'];
        $password = $_ENV['DB_PASSWORD'];

        $this->pdo = new PDO($dsn, $user, $password);
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    /**
     * Return transactions for a specific user with keys matching the view: Type, Amount, Date
     */
    public function getTransactions(string $username): array {
        $library = $_ENV['DB_LIBRARY'];
        $sql = "SELECT TRANSTYPE AS \"Type\", AMOUNT AS \"Amount\", TRANSTIME AS \"Date\" FROM {$library}.BalanceTab WHERE LOWER(USERNAME) = LOWER(:username) ORDER BY TRANSTIME DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->bindParam(':username', $username, PDO::PARAM_STR);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
