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
    <title>Invoice Payments</title>
    <link rel="stylesheet" href="../../../public/assets/css/reset.css">
    <link rel="stylesheet" href="../../../public/assets/css/root.css">
    <link rel="stylesheet" href="../../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="../css/invoice-payments.css">
    <link rel="stylesheet" href="../css/invoice-modal.css">
    <link rel="stylesheet" href="../css/payment-modal.css">
    <link rel="stylesheet" href="../css/document-modal.css">
    <link rel="stylesheet" href="../css/client-search-dropdown.css">
    <link rel="stylesheet" href="../../../public/assets/css/table.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
    <script src="../../../public/assets/js/sidebar.js"></script>
    <script src="../../../public/assets/js/table.js"></script>
    <script src="../../../public/assets/js/helpers.js"></script>
    <script src="../js/document-api.js"></script>
    <script src="../js/document-modal.js"></script>
    <script src="../js/payment-modal.js"></script>
    <script src="../js/payments-screen.js"></script>
    <script src="../js/invoice-data.js"></script>
</head>
<body id="payments" >
<?php include('../../../src/UI/sidebar.php') ?>
<div class="container" id="payments-container">

<div class="analytics-summary">
    <div class="analytics-card">
        <div class="card-icon">
            <i class="fas fa-credit-card"></i> <!-- You can replace this with any suitable icon -->
        </div>
        <div class="card-content">
            <h3>Total Payments Received</h3>
            <p>R5000.00</p>
        </div>
    </div>

    <div class="analytics-card">
        <div class="card-icon">
            <i class="fas fa-clock"></i> <!-- You can replace this with any suitable icon -->
        </div>
        <div class="card-content">
            <h3>Total Pending Payments</h3>
            <p>R2000.00</p>
        </div>
    </div>

    <div class="analytics-card">
        <div class="card-icon">
            <i class="fas fa-times-circle"></i> <!-- You can replace this with any suitable icon -->
        </div>
        <div class="card-content">
            <h3>Failed Transactions</h3>
            <p>R500.00</p>
        </div>
    </div>
</div>

    
    <!-- Tab Navigation -->
    <div class="tabs">
        <button class="tab-btn active" data-tab="incoming-payments">Incoming Payments</button>
        <button class="tab-btn" data-tab="credit-notes">Credit Notes</button>
        <button class="tab-btn" data-tab="refunds">Refunds</button>
    </div>

    <!-- Incoming Payments Tab -->
    <div id="incoming-payments" class="tab-content active">
        <div class="tab-header">
            <h2>Incoming Payments</h2>
            <div class="tab-actions">
                <button class="btn btn-primary" onclick="openPaymentModal('create', null)">
                    <i class="material-icons">add</i> Add Payment
                </button>
            </div>
        </div>

        <!-- Nova Table Container for Payments -->
        <div id="nova-table-payments" class="nova-table-container"></div>

        <!-- Recurring Payments Section -->
        <div class="recurring-payments">
            <h3>Recurring Payments</h3>
            <button class="btn-collapsible">Manage Recurring Payments</button>
        </div>
    </div>

    <!-- Credit Notes Tab -->
    <div id="credit-notes" class="tab-content">
        <div class="tab-header">
            <h2>Credit Notes</h2>
            <div class="tab-actions">
                <button class="btn btn-primary" onclick="showInvoiceSelectionForDocument('credit-note')">
                    <i class="material-icons">receipt_long</i> Add Credit Note
                </button>
            </div>
        </div>

        <!-- Nova Table Container for Credit Notes -->
        <div id="nova-table-credit-notes" class="nova-table-container"></div>

        <!-- Credit Note Summary -->
        <div class="credit-note-summary">
            <h3>Summary</h3>
            <p>Unused: R200.00</p>
            <p>Expired: R50.00</p>
            <p>Applied: R150.00</p>
        </div>
    </div>

    <!-- Refunds Tab -->
    <div id="refunds" class="tab-content">
        <div class="tab-header">
            <h2>Refunds</h2>
            <div class="tab-actions">
                <button class="btn btn-primary" onclick="showInvoiceSelectionForDocument('refund')">
                    <i class="material-icons">money_off</i> Add Refund
                </button>
            </div>
        </div>

        <!-- Nova Table Container for Refunds -->
        <div id="nova-table-refunds" class="nova-table-container"></div>

        <!-- Refunds Summary -->
        <div class="refunds-summary">
            <h3>Summary</h3>
            <p>Total Refunded: R1000.00</p>
            <p>Pending Refunds: R500.00</p>
            <p>Most Refunded Customer: John Doe</p>
        </div>
    </div>

    <!-- Payment Allocations Tab -->
    <div id="payment-allocations" class="tab-content">
        <h2>Payment Allocations</h2>

        <!-- Allocation Table -->
        <table class="allocation-table table">
            <thead>
                <tr>
                    <th>Allocation ID</th>
                    <th>Customer</th>
                    <th>Payment/Credit Note ID</th>
                    <th>Invoice ID</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example Allocation Row -->
                <tr>
                    <td>ALLOC001</td>
                    <td>John Doe</td>
                    <td>001</td>
                    <td>INV123</td>
                    <td>R50.00</td>
                    <td>Allocated</td>
                    <td><button class="btn-reallocate">Reallocate</button></td>
                </tr>
                <!-- More allocation rows go here -->
            </tbody>
        </table>

        <!-- Filters/Search Section -->
        <div class="filters">
            <input type="text" placeholder="Search by Customer or Invoice" id="search-allocation">
        </div>
    </div>

</div>

<!-- Include Document Modal -->
<?php include('../modals/document-modal.php'); ?>

<!-- Include Payment Modal -->
<?php include('../modals/payment-modal.php'); ?>

<!-- Include Response Modal -->
<?php include('../../../src/UI/response-modal.php'); ?>

<!-- Include Loading Modal -->
<?php include('../../../src/UI/loading-modal.php'); ?>

</body>
</html>
