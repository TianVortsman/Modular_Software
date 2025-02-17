<?php
session_start();

// Ensure user is logged in (redirect if necessary)
if (!isset($_SESSION['user_logged_in'])) {
    exit();
}

// Fetch accounts from session or database
// Assuming accounts are stored in session from previous step
$accounts = isset($_SESSION['multiple_accounts']) ? $_SESSION['multiple_accounts'] : [];

if (empty($accounts)) {
    echo "No accounts available.";
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Account</title>
    <link rel="stylesheet" href="../css/root.css">
    <link rel="stylesheet" href="../css/choose-account.css">
    <script src="../js/toggle-theme.js"></script>
</head>
<body>
    <div class="container">
        <div class="account-selection-block">
            <h2>Please choose an account to proceed</h2>
            <ul id="accountList">
                <?php foreach ($accounts as $account): ?>
                    <li class="account-item" onclick="selectAccount('<?php echo $account['account_number']; ?>')">
                        Account Number: <?php echo $account['account_number']; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>
    </div>

    <script>
        // Handle the selection of an account
        function selectAccount(accountNumber) {
            // Store the selected account number in session or as hidden input
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '../php/process-account.php';  // The file that processes the selected account

            const accountInput = document.createElement('input');
            accountInput.type = 'hidden';
            accountInput.name = 'account_number';
            accountInput.value = accountNumber;

            form.appendChild(accountInput);
            document.body.appendChild(form);
            form.submit(); // Submit the form with the selected account
        }
    </script>
</body>
</html>
