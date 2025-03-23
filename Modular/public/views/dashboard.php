<?php
// Start session if not already started
session_start();

// Include autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Check for account number or token
$hasToken = isset($_GET['token']) && !empty($_GET['token']);
$hasAccountNumber = isset($_GET['account_number']) && !empty($_GET['account_number']);

// If token is provided, verify it using the TechnicianAuthManager
if ($hasToken) {
    $token = $_GET['token'];
    $techAuthManager = new \App\Core\Auth\TechnicianAuthManager();
    
    // Verify the token
    if (!$techAuthManager->verifyAccessToken($token)) {
        // Token is invalid, redirect to login page
        header('Location: ../login.php?error=invalid_token');
        exit;
    }
    
    // Token is valid, set account number from session
    $accountNumber = $_SESSION['tech_account_number'];
} 
// Otherwise, look for direct account number and normal login
elseif ($hasAccountNumber) {
    // For backward compatibility - allow direct account number if user is logged in as admin or tech
    if (
        !(isset($_SESSION['user_type']) && ($_SESSION['user_type'] === 'admin' || $_SESSION['user_type'] === 'technician')) &&
        !(isset($_SESSION['tech_logged_in']) && $_SESSION['tech_logged_in'] === true)
    ) {
        // Not logged in as technician, redirect to login page
        header('Location: ../login.php?error=access_denied');
        exit;
    }
    
    $accountNumber = $_GET['account_number'];
} 
// Neither token nor account number provided
else {
    // If the account number is in the session, use it
    if (isset($_SESSION['account_number']) && !empty($_SESSION['account_number'])) {
        $accountNumber = $_SESSION['account_number'];
    } else if (isset($_SESSION['tech_account_number']) && !empty($_SESSION['tech_account_number'])) {
        // Try to use tech account number if available
        $accountNumber = $_SESSION['tech_account_number'];
    } else {
        // No account number found anywhere, redirect to login
        header("Location: ../admin/techlogin.php");
        exit;
    }
}

// Set the account_number in session if not already set
if (!isset($_SESSION['account_number']) || $_SESSION['account_number'] !== $accountNumber) {
    $_SESSION['account_number'] = $accountNumber;
}

$userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');
$multiple_accounts = isset($_SESSION['multiple_accounts']) ? $_SESSION['multiple_accounts'] : false;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Main Page</title>
    <link rel="stylesheet" href="../assets/css/root.css">
    <link rel="stylesheet" href="../assets/css/reset.css">
    <link rel="stylesheet" href="../assets/css/dashboard.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <script src="../assets/js/toggle-theme.js" type="module"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body id="dashboard">
    <!-- Sidebar -->
     <?php include('../../src/UI/sidebar.php'); ?>
    <!-- Main Content -->
    <div class="modular-main-content">
        <header class="modular-header">
            <h1>Welcome to Modular Software</h1>
            <p>Empowering Your Vision, One Module at a Time</p>
        </header>

        <div class="module-container">
            <a href="../../modules/time_and_attendance/views/dashboard-TA.php" class="module-card">
                <h2>Time & Attendance</h2>
                <p>Track employee attendance and manage shifts.</p>
            </a>
            <a href="../../modules/invoice/views/invoice-dashboard.php" class="module-card">
                <h2>Invoicing & Billing</h2>
                <p>Automate billing and generate invoices.</p>
            </a>
            <a href="../../modules/payroll/views/dashboard-payroll.php" class="module-card">
                <h2>Payroll</h2>
                <p>Manage payroll, taxes, and salary calculations.</p>
            </a>
            <a href="../../modules/inventory/views/dashboard-inventory.php" class="module-card">
                <h2>Inventory Management</h2>
                <p>Track stock levels and automate reorders.</p>
            </a>
            <a href="../../modules/crm/views/dashboard-crm.php" class="module-card">
                <h2>CRM</h2>
                <p>Manage customer relationships and sales leads.</p>
            </a>
            <a href="../../modules/project/views/dashboard-project.php" class="module-card">
                <h2>Project Management</h2>
                <p>Track tasks, timelines, and project progress.</p>
            </a>
            <a href="../../modules/accounting/views/accounting-dashboard.php" class="module-card">
                <h2>Accounting</h2>
                <p>Manage finances, ledger, and reporting.</p>
            </a>
            <a href="../../modules/hr/views/dashboard-hr.php" class="module-card">
                <h2>HR Management</h2>
                <p>Manage employee records, recruitment, and evaluations.</p>
            </a>
            <a href="../../modules/support/views/dashboard-support.php" class="module-card">
                <h2>Support Module</h2>
                <p>Streamlined support for customer inquiries and resolutions.</p>
            </a>
            <a href="../../modules/fleet/views/dashboard-fleet.php" class="module-card">
                <h2>Fleet Management</h2>
                <p>Oversee your fleet operations effectively.</p>
            </a>
            <a href="../../modules/asset/views/dashboard-asset.php" class="module-card">
                <h2>Asset Management</h2>
                <p>Maximize asset efficiency for improved operational performance.</p>
            </a>
            <a href="../../modules/access/views/dashboard-access.php" class="module-card">
                <h2>Access Control</h2>
                <p>Ensure secure access to protect your valuable assets.</p>
            </a>
        </div>

        <footer class="modular-footer">
            <p>&copy; 2024 Modular Software. All rights reserved.</p>
        </footer>
    </div>

    <script src="../assets/js/sidebar.js"></script>
    <script>var multipleAccounts = <?= json_encode($multiple_accounts); ?>; </script>
</body>
</html>