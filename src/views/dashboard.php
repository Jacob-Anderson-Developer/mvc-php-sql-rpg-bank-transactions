<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link rel="stylesheet" href="/styles.css">
    <style>
        body {
            max-width: 600px;
        }
        h2 {
            color: #333;
        }
        .nav-section {
            margin: 20px 0;
            padding: 15px;
            border: 1px solid #ddd;
            border-radius: 5px;
            background-color: #f9f9f9;
        }
        .nav-section p {
            margin: 10px 0;
        }
        a {
            display: inline-block;
            padding: 10px 20px;
            margin: 5px 0;
            font-weight: bold;
        }
        a[href*="logout"] {
            float: right;
            margin-top: 20px;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>
    <h2>Welcome <?= htmlspecialchars($user_name) ?>!</h2>

    <div class="nav-section">
        <p>
            <a href="/transact">Withdraw or Deposit</a>
        </p>
        <p>
            <a href="/history">View Transaction History</a>
        </p>
    </div>

    <a href="/logout">Logout</a>
    <div class="clear"></div>
</body>
</html>
