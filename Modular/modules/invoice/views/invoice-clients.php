<?php
session_start();

// Debug session
error_log("Session contents: " . print_r($_SESSION, true));

// Check if account number is in the query parameters
if (isset($_GET['account_number'])) {
    $account_number = $_GET['account_number'];
    error_log("Account number from GET: " . $account_number);

    // Store the account number in the session
    $_SESSION['account_number'] = $account_number;
    error_log("Account number stored in session");

    // Redirect to remove the query parameter from the URL
    header("Location: dashboard-TA.php");
    exit;
}

// If the account number is already in the session, use it
if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
    error_log("Account number from session: " . $account_number);
} else {
    error_log("No account number found in session");
    // Redirect to login or show an error if no account number is found
    header("Location: ../../index.php");
    exit;
}

$userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');
error_log("User name: " . $userName);
$multiple_accounts = isset($_SESSION['multiple_accounts']) ? $_SESSION['multiple_accounts'] : false;
error_log("Multiple accounts: " . ($multiple_accounts ? 'true' : 'false'));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoice Clients</title>
    <link rel="stylesheet" href="../../../public/assets/css/reset.css">
    <link rel="stylesheet" href="../../../public/assets/css/root.css">
    <link rel="stylesheet" href="../../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="../css/invoice-clients.css">
    <link rel="stylesheet" href="../css/invoice-modal.css">
    <link rel="stylesheet" href="../css/client-search-dropdown.css">
    <link rel="stylesheet" href="../../../public/assets/css/table.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
    <script src="../../../public/assets/js/sidebar.js"></script>
    <script src="../../../public/assets/js/table.js"></script>
</head>
<body id="invoice-clients">
<div class="screen-container">
    <?php include('../../../src/UI/sidebar.php') ?>
    <div class="clients-screen">
        <!-- Quick Actions -->
        <div class="actions-container" id="actions-container">
            <div class="clients-actions">
            <h2>Clients</h2>
            </div>

            <!-- Filter Options -->
                <div class="filter-container">
                <button class="clientSectionButton1 active" id="clientSectionButton1">Private</button>
                <button class="clientSectionButton2" id="clientSectionButton2" >Business</button>
                </div>

            <!-- NOVA Table Containers -->
            <div class="client-section1 active" id="client-section1">
                <div id="nova-table-private" class="nova-table-container"></div>
            </div>
            <div class="client-section2" id="client-section2">
                <div id="nova-table-business" class="nova-table-container"></div>
            </div>
            </div>
        </div>
    </div>
</div>
<?php include('../modals/clientModals.php') ?>
<?php include('../../../src/UI/loading-modal.php') ?>
<?php include('../../../src/UI/response-modal.php') ?>
        <script src="../../../public/assets/js/helpers.js"></script>
        <script src="../js/client-api.js"></script>
        <script src="../js/client-modal.js"></script>
        <script src="../js/client-form.js"></script>
        <script src="../js/client-screen.js"></script>
</body>
</html>
