<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Processor</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <h1>Transaction Processor</h1>
    <h2>Bank Transaction Form</h2>
    <p>Welcome, <?= htmlspecialchars($user_name) ?>!</p>

    <?php if ($message): ?>
        <div class="message <?= $messageSuccess ? 'success' : 'error' ?>">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <form method="post">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
        <label for="amount">Amount:</label>
        <input type="number" id="amount" name="amount" step="0.01" min="0" value="<?= htmlspecialchars($amountInput) ?>" required>

        <label for="transactionType">Transaction Type:</label>
        <select id="transactionType" name="transactionType" required>
            <option value="">-- Select --</option>
            <option value="deposit">Deposit</option>
            <option value="withdrawal">Withdrawal</option>
        </select>

        <button type="submit">Submit Transaction</button>
    </form>

    <?php if ($balanceDisplay): ?>
        <div class="balance">
            Current Balance: $<?= htmlspecialchars($balanceDisplay) ?>
        </div>
    <?php endif; ?>

    <div>
        <a href="/dashboard">Back to Dashboard</a>
        <a href="/history">View History</a>
        <a href="/logout">Logout</a>
    </div>
</body>
</html>
