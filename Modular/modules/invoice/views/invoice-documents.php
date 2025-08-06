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
    <title>Documents</title>
    <link rel="stylesheet" href="../../../public/assets/css/reset.css">
    <link rel="stylesheet" href="../../../public/assets/css/root.css">
    <link rel="stylesheet" href="../../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="../css/client-search-dropdown.css">
    <link rel="stylesheet" href="../css/invoices.css">
    <link rel="stylesheet" href="../css/document-modal.css">
    <link rel="stylesheet" href="../css/payment-modal.css">
    <link rel="stylesheet" href="../../../public/assets/css/table.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
    <script src="../../../public/assets/js/sidebar.js"></script>
    <script src="../../../public/assets/js/table.js"></script>
</head>
<body id="documents">
  <?php include('../../../src/UI/sidebar.php'); ?>
<div class="screen-container">
    <div class="invoices-screen">
        <h2>Documents</h2>
        
        <!-- Integrated Tab System -->
        <div class="integrated-tab-system">
            <div class="main-tabs">
                <button class="tab-button active" data-section="invoices-section">Invoices</button>
                <button class="tab-button" data-section="recurring-invoices-section">Recurring Invoices</button>
                <button class="tab-button" data-section="quotations-section">Quotations</button>
                <button class="tab-button" data-section="vehicle-quotations-section">Vehicle Quotations</button>
                <button class="tab-button" data-section="vehicle-invoices-section">Vehicle Invoices</button>
            </div>
            
            <!-- Status Subtabs Container -->
            <div class="status-subtabs-container">
                <div class="status-subtabs" id="invoices-subtabs">
                    <button class="subtab-button active" data-status="all">All</button>
                    <button class="subtab-button" data-status="paid">Paid</button>
                    <button class="subtab-button" data-status="unpaid">Unpaid</button>
                    <button class="subtab-button" data-status="overdue">Overdue</button>
                </div>
                <div class="status-subtabs" id="recurring-invoices-subtabs" style="display:none;">
                    <button class="subtab-button active" data-status="all">All</button>
                    <button class="subtab-button" data-status="active">Active</button>
                    <button class="subtab-button" data-status="paused">Paused</button>
                    <button class="subtab-button" data-status="cancelled">Cancelled</button>
                </div>
                <div class="status-subtabs" id="quotations-subtabs" style="display:none;">
                    <button class="subtab-button active" data-status="all">All</button>
                    <button class="subtab-button" data-status="approved">Approved</button>
                    <button class="subtab-button" data-status="pending">Pending</button>
                    <button class="subtab-button" data-status="rejected">Rejected</button>
                </div>
                <div class="status-subtabs" id="vehicle-quotations-subtabs" style="display:none;">
                    <button class="subtab-button active" data-status="all">All</button>
                    <button class="subtab-button" data-status="approved">Approved</button>
                    <button class="subtab-button" data-status="pending">Pending</button>
                    <button class="subtab-button" data-status="rejected">Rejected</button>
                </div>
                <div class="status-subtabs" id="vehicle-invoices-subtabs" style="display:none;">
                    <button class="subtab-button active" data-status="all">All</button>
                    <button class="subtab-button" data-status="paid">Paid</button>
                    <button class="subtab-button" data-status="unpaid">Unpaid</button>
                    <button class="subtab-button" data-status="overdue">Overdue</button>
                </div>
            </div>
        </div>
        
        <!-- Invoices Section -->
        <div class="document-section" id="invoices-section">
            <div id="nova-table-invoices" class="nova-table-container"></div>
        </div>

        <!-- Recurring Invoices Section -->
        <div class="document-section" id="recurring-invoices-section" style="display:none;">
            <div id="nova-table-recurring-invoices" class="nova-table-container"></div>
        </div>

        <!-- Quotations Section -->
        <div class="document-section" id="quotations-section" style="display:none;">
            <div id="nova-table-quotations" class="nova-table-container"></div>
        </div>

        <!-- Vehicle Quotations Section -->
        <div class="document-section" id="vehicle-quotations-section" style="display:none;">
            <div id="nova-table-vehicle-quotations" class="nova-table-container"></div>
        </div>

        <!-- Vehicle Invoices Section -->
        <div class="document-section" id="vehicle-invoices-section" style="display:none;">
            <div id="nova-table-vehicle-invoices" class="nova-table-container"></div>
        </div>
    </div>
    
        <script src="../../../public/assets/js/helpers.js"></script>
        <script src="../js/document-api.js"></script>
        <script src="../js/document-form.js"></script>
        <script src="../js/document-screen.js"></script>
        <script src="../js/document-modal.js"></script>
        <script src="../js/payment-modal.js"></script>
        <script src="../../../public/assets/js/document-sending-service.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<?php include('../modals/document-modal.php')?>
<?php include('../modals/payment-modal.php')?>
<?php include('../../../src/UI/response-modal.php'); ?>
<?php include('../../../src/UI/loading-modal.php'); ?>
</body>
</html>