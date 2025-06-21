<?php
session_start();

// Check if account number is in the query parameters
if (isset($_GET['account_number'])) {
    $account_number = $_GET['account_number'];

    // Store the account number in the session
    $_SESSION['account_number'] = $account_number;

    // Redirect to remove the query parameter from the URL
    header("Location: dashboard-TA.php");
    exit;
}

// If the account number is already in the session, use it
if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
} else {
    // Redirect to login or show an error if no account number is found
    header("Location: ../../index.php");
    exit;
}

$userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');
$multiple_accounts = isset($_SESSION['multiple_accounts']) ? $_SESSION['multiple_accounts'] : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Setup</title>
    <link rel="stylesheet" href="../../../public/assets/css/reset.css">
    <link rel="stylesheet" href="../../../public/assets/css/root.css">
    <link rel="stylesheet" href="../../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="../css/invoice-setup.css">
    <link rel="stylesheet" href="../css/invoice-modal.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
    <script src="../../../public/assets/js/sidebar.js"></script>
    <script src="../js/invoice-modal.js"></script>
    <script src="../js/invoice-data.js"></script>
    <style>
        /* Optional inline CSS for error display and grids */
        .js-error-log { color: red; margin-top: 20px; }
        .grid { margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px; }
        .product-card { border: 1px solid #ccc; padding: 10px; width: 200px; }
    </style>
</head>
<body id="invoice-setup">
    <?php include('../../../src/UI/sidebar.php'); ?>
    <div class="container">
        <h1>Import Products from Excel</h1>
        <div class="form-box">
            <form action="invoice-setup.php" method="post" enctype="multipart/form-data">
                <div class="input-group">
                    <input type="file" name="excel_file" accept=".xlsx, .xls">
                </div>
                <div class="button-group">
                    <button type="submit">Import</button>
                </div>
            </form>
        </div>
        <!-- PHP error and success logs -->
        <?php if (!empty(array_filter($errors))): ?>
            <div class="error-log">
                <h2>Import Errors</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Error Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($errors as $error): ?>
                            <?php if (!empty($error)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($error); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php if (!empty(array_filter($successMessages))): ?>
            <div class="success-log">
                <h2>Import Successes</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Success Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($successMessages as $message): ?>
                            <?php if (!empty($message)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($message); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <!-- Container for JavaScript error messages -->
        <div id="js-error-log" class="js-error-log">
            <h2>JavaScript Errors</h2>
        </div>
    </div>
</body>
</html>

