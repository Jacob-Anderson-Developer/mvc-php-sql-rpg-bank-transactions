<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction History</title>
    <link rel="stylesheet" href="/styles.css">
</head>
<body>
    <h1>Transaction History</h1>
    <p>Welcome, <?= htmlspecialchars($user_name) ?>!</p>

    <?php if ($message): ?>
        <div class="message error">
            <?= htmlspecialchars($message) ?>
        </div>
    <?php endif; ?>

    <?php if (!empty($records)): ?>
        <table>
            <thead>
                <tr>
                    <th>Type</th>
                    <th>Amount</th>
                    <th>Date</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($records as $record): ?>
                    <?php
                        $rawType = $record['Type'] ?? '';
                        $typeLower = strtolower(trim($rawType));
                        // Determine if this is a withdrawal (matches withdraw, withdrawal, debit, or starts with 'w')
                        $isWithdrawal = (bool) preg_match('/\b(withdraw|withdrawal|debit)\b/i', $rawType) || (isset($typeLower[0]) && $typeLower[0] === 'w');
                        $typeLabel = $isWithdrawal ? 'Withdrawal' : 'Deposit';

                        $amount = floatval($record['Amount'] ?? 0);
                        $absAmount = number_format(abs($amount), 2);
                        $amountDisplay = ($isWithdrawal ? '-' : '') . '$' . $absAmount;
                        $amountClass = $isWithdrawal ? 'withdrawal' : 'deposit';

                        $dt = strtotime($record['Date']);
                        if ($dt !== false) {
                            $dateDisplay = date('M j, Y g:i:s A', $dt);
                        } else {
                            $dateDisplay = $record['Date'];
                        }
                    ?>
                    <tr>
                        <td class="<?= htmlspecialchars($amountClass) ?>"><?= htmlspecialchars($typeLabel) ?></td>
                        <td><span class="<?= htmlspecialchars($amountClass) ?>"><?= htmlspecialchars($amountDisplay) ?></span></td>
                        <td><?= htmlspecialchars($dateDisplay) ?></td>
                    </tr>
                <?php endforeach; ?>
                <tr class="balance-row">
                    <td>Balance</td>
                    <td style="text-align: right;">$<?= htmlspecialchars($balanceDisplay) ?></td>
                    <td></td>
                </tr>
            </tbody>
        </table>
    <?php else: ?>
        <p>No transactions found.</p>
    <?php endif; ?>

    <div>
        <a href="/dashboard">Back to Dashboard</a>
        <a href="/transact">Withdraw or Deposit</a>
        <a href="/logout">Logout</a>
    </div>
</body>
</html>
