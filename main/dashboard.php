<?php
session_start();

// Check if account number is in the query parameters
if (isset($_GET['account_number'])) {
    $account_number = $_GET['account_number'];

    // Store the account number in the session
    $_SESSION['account_number'] = $account_number;

    // Optionally, redirect to remove the query parameter from the URL
    header("Location: dashboard.php");
    exit;
}

// If the account number is already in the session, use it
if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
} else {
    // Redirect to login or show an error if no account number is found
    header("Location: techlogin.php");
    exit;
}

$userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');

// Include the database connection
include('../php/db.php');
$multiple_accounts = isset($_SESSION['multiple_accounts']) ? $_SESSION['multiple_accounts'] : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <link rel="stylesheet" href="../css/root.css">
    <link rel="stylesheet" href="../css/reset.css">
    <link rel="stylesheet" href="../css/dashboard.css">
    <link rel="stylesheet" href="../css/sidebar.css">
    <script src="../js/toggle-theme.js" type="module"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body id="dashboard">
    <!-- Sidebar -->
     <?php include('sidebar.php'); ?>
    <!-- Main Content -->
    <div class="modular-main-content">
        <header class="modular-header">
            <h1>Welcome to Modular Software</h1>
            <p>Empowering Your Vision, One Module at a Time</p>
        </header>

        <div class="module-container">
            <a href="#" class="module-card">
                <h2>Time & Attendance</h2>
                <p>Track employee attendance and manage shifts.</p>
            </a>
            <a href="../modules/invoice/invoice-dashboard.php" class="module-card">
                <h2>Invoicing & Billing</h2>
                <p>Automate billing and generate invoices.</p>
            </a>
            <a href="#" class="module-card">
                <h2>Payroll</h2>
                <p>Manage payroll, taxes, and salary calculations.</p>
            </a>
            <a href="#" class="module-card">
                <h2>Inventory Management</h2>
                <p>Track stock levels and automate reorders.</p>
            </a>
            <a href="#" class="module-card">
                <h2>CRM</h2>
                <p>Manage customer relationships and sales leads.</p>
            </a>
            <a href="#" class="module-card">
                <h2>Project Management</h2>
                <p>Track tasks, timelines, and project progress.</p>
            </a>
            <a href="../modules/accounting/accounting-dashboard.php" class="module-card">
                <h2>Accounting</h2>
                <p>Manage finances, ledger, and reporting.</p>
            </a>
            <a href="#" class="module-card">
                <h2>HR Management</h2>
                <p>Manage employee records, recruitment, and evaluations.</p>
            </a>
            <a href="#" class="module-card">
                <h2>Support Module</h2>
                <p>Streamlined support for customer inquiries and resolutions.</p>
            </a>
            <a href="#" class="module-card">
                <h2>Fleet Management</h2>
                <p>Oversee your fleet operations effectively.</p>
            </a>
            <a href="#" class="module-card">
                <h2>Asset Management</h2>
                <p>Maximize asset efficiency for improved operational performance.</p>

            </a>
            <a href="#" class="module-card">
                <h2>Access Control</h2>
                <p>Ensure secure access to protect your valuable assets.</p>
            </a>
        </div>

        <footer class="modular-footer">
            <p>&copy; 2024 Modular Software. All rights reserved.</p>
        </footer>
    </div>

    <script src="../js/sidebar.js"></script>
    <script src="/modular1/js/page-transitions.js"></script>
    <script>
    // Store PHP session value in JavaScript
    var multipleAccounts = <?= json_encode($multiple_accounts); ?>; 

    function checkMultipleAccounts() {
        if (multipleAccounts) {
            window.location.href = "choose-account.php"; // Redirect if session variable is set
        }
    }
</script>
</body>
</html>