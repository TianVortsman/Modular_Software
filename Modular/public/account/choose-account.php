<?php
session_start();

// Ensure user is logged in (redirect if necessary)
if (!isset($_SESSION['user_logged_in'])) {
    header("Location: ../index.php?error=not_logged_in");
    exit();
}

// Check if accounts are stored in session
if (!isset($_SESSION['multiple_accounts']) || !is_array($_SESSION['multiple_accounts']) || empty($_SESSION['multiple_accounts'])) {
    // For debugging
    echo "<pre>Debug: No multiple accounts found in session.\n";
    echo "Session data:\n";
    print_r($_SESSION);
    echo "</pre>";
    
    // After displaying debug info, redirect to dashboard
    header("Location: ../views/dashboard.php");
    exit();
}

// Fetch accounts from session
$accounts = $_SESSION['multiple_accounts'];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Select Account</title>
    <link rel="stylesheet" href="../assets/css/root.css">
    <link rel="stylesheet" href="../assets/css/choose-account.css">
    <script src="../assets/js/toggle-theme.js"></script>
</head>
<body>
    <div class="container">
        <div class="account-selection-block">
            <h2>Please choose an account to proceed</h2>
            <ul id="accountList">
                <?php foreach ($accounts as $account): ?>
                    <li class="account-item" onclick="selectAccount('<?php echo $account['account_number']; ?>')">
                        <strong>Account:</strong> <?php echo $account['account_number']; ?>
                        <?php if (isset($account['company_name'])): ?>
                            <br><span class="company-name"><?php echo $account['company_name']; ?></span>
                        <?php endif; ?>
                    </li>
                <?php endforeach; ?>
            </ul>
            <div class="action-buttons">
                <a href="../views/dashboard.php" class="cancel-button">Cancel</a>
            </div>
        </div>
    </div>

    <script>
        // Handle the selection of an account
        function selectAccount(accountNumber) {
            console.log("Selected account: " + accountNumber);
            
            // Store the selected account number in session or as hidden input
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'process-account.php';  // The file that processes the selected account

            const accountInput = document.createElement('input');
            accountInput.type = 'hidden';
            accountInput.name = 'account_number';
            accountInput.value = accountNumber;

            form.appendChild(accountInput);
            document.body.appendChild(form);
            form.submit(); // Submit the form with the selected account
        }
    </script>
    
    <style>
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: var(--color-background-secondary);
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        
        .account-selection-block {
            width: 100%;
        }
        
        h2 {
            color: var(--color-text);
            margin-bottom: 20px;
            text-align: center;
        }
        
        #accountList {
            list-style: none;
            padding: 0;
            margin: 20px 0;
        }
        
        .account-item {
            padding: 15px;
            margin-bottom: 10px;
            background-color: var(--color-background);
            border-radius: 6px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.05);
        }
        
        .account-item:hover {
            background-color: var(--color-background-hover);
        }
        
        .company-name {
            color: var(--color-text-secondary);
            font-size: 14px;
        }
        
        .action-buttons {
            display: flex;
            justify-content: center;
            margin-top: 20px;
        }
        
        .cancel-button {
            padding: 10px 15px;
            background-color: var(--color-background);
            color: var(--color-text);
            text-decoration: none;
            border-radius: 4px;
            transition: background-color 0.3s ease;
        }
        
        .cancel-button:hover {
            background-color: var(--color-background-hover);
        }
    </style>
</body>
</html>
