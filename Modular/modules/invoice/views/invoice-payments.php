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
    <link rel="stylesheet" href="../css/client-search-dropdown.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
    <script src="../../../public/assets/js/sidebar.js"></script>
    <script src="../js/invoice-modal.js"></script>
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
        <h2>Incoming Payments</h2>

        <!-- Filters/Search Section -->
        <div class="filters">
            <input type="text" placeholder="Search by Customer Name" id="search-customer">
            <input type="date" id="filter-start-date"> to <input type="date" id="filter-end-date">
            <select id="filter-status">
                <option value="">Status</option>
                <option value="paid">Paid</option>
                <option value="pending">Pending</option>
                <option value="failed">Failed</option>
            </select>
            <select id="filter-method">
                <option value="">Payment Method</option>
                <option value="credit-card">Credit Card</option>
                <option value="bank-transfer">Bank Transfer</option>
                <option value="paypal">PayPal</option>
            </select>
        </div>

        <!-- Payments Table -->
        <table class="payments-table table">
            <thead>
                <tr>
                    <th><input type="checkbox"></th>
                    <th>Payment ID</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Payment Method</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example Payment Row -->
                <tr>
                    <td><input type="checkbox"></td>
                    <td>001</td>
                    <td>2025-01-20</td>
                    <td>John Doe</td>
                    <td>Credit Card</td>
                    <td>R100.00</td>
                    <td><span class="badge paid">Paid</span></td>
                    <td>Payment for Invoice #123</td>
                    <td>
                        <button class="btn-allocate">Allocate to Invoice</button>
                        <button class="btn-edit">Edit</button>
                    </td>
                </tr>
                <!-- More payment rows go here -->
            </tbody>
        </table>

        <!-- Add Payment Button -->
        <button class="btn-add-payment" id="add-payment-btn">Add Payment</button>

        <!-- Recurring Payments Section -->
        <div class="recurring-payments">
            <h3>Recurring Payments</h3>
            <button class="btn-collapsible">Manage Recurring Payments</button>
        </div>
    </div>

    <!-- Credit Notes Tab -->
    <div id="credit-notes" class="tab-content">
        <h2>Credit Notes</h2>

        <!-- Filters/Search Section -->
        <div class="filters">
            <input type="text" placeholder="Search by Customer Name" id="search-credit-note">
            <input type="date" id="filter-credit-note-start-date"> to <input type="date" id="filter-credit-note-end-date">
            <select id="filter-credit-note-status">
                <option value="">Status</option>
                <option value="unused">Unused</option>
                <option value="partially-used">Partially Used</option>
                <option value="expired">Expired</option>
            </select>
        </div>

        <!-- Credit Notes Table -->
        <table class="credit-notes-table table">
            <thead>
                <tr>
                    <th>Credit Note ID</th>
                    <th>Date Issued</th>
                    <th>Customer</th>
                    <th>Amount</th>
                    <th>Status</th>
                    <th>Remaining Time</th>
                    <th>Notes</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example Credit Note Row -->
                <tr>
                    <td>CN001</td>
                    <td>2025-01-20</td>
                    <td>John Doe</td>
                    <td>R50.00</td>
                    <td><span class="badge unused">Unused</span></td>
                    <td>30 days</td>
                    <td>Credit for returned item</td>
                    <td><button class="btn-apply-credit-note">Apply to Invoice</button></td>
                </tr>
                <!-- More credit note rows go here -->
            </tbody>
        </table>

        <!-- Add Credit Note Button -->
        <button class="btn-add-credit-note" id="add-credit-note-btn">Add Credit Note</button>

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
        <h2>Refunds</h2>

        <!-- Filters/Search Section -->
        <div class="filters">
            <input type="text" placeholder="Search by Customer Name" id="search-refund">
            <input type="date" id="filter-refund-start-date"> to <input type="date" id="filter-refund-end-date">
            <select id="filter-refund-status">
                <option value="">Status</option>
                <option value="pending">Pending</option>
                <option value="approved">Approved</option>
                <option value="completed">Completed</option>
            </select>
        </div>

        <!-- Refunds Table -->
        <table class="refunds-table table">
            <thead>
                <tr>
                    <th>Refund ID</th>
                    <th>Date</th>
                    <th>Customer Name</th>
                    <th>Refund Method</th>
                    <th>Amount</th>
                    <th>Linked Invoice ID</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <!-- Example Refund Row -->
                <tr>
                    <td>RF001</td>
                    <td>2025-01-20</td>
                    <td>John Doe</td>
                    <td>PayPal</td>
                    <td>R50.00</td>
                    <td>INV123</td>
                    <td><span class="badge completed">Completed</span></td>
                    <td><button class="btn-approve-refund">Approve</button></td>
                </tr>
                <!-- More refund rows go here -->
            </tbody>
        </table>

        <!-- Add Refund Button -->
        <button class="btn-add-refund" id="add-refund-btn">Add Refund</button>

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

<!-- Add Payment Modal -->
<div id="payment-modal" class="modal">
    <div class="modal-content">
        <h3>Add Payment</h3>
        <label for="customer">Customer</label>
        <input type="text" id="customer">
        <label for="amount">Amount</label>
        <input type="number" id="amount">
        <label for="payment-method">Payment Method</label>
        <select id="payment-method">
            <option value="credit-card">Credit Card</option>
            <option value="bank-transfer">Bank Transfer</option>
            <option value="paypal">PayPal</option>
        </select>
        <label for="reference">Reference</label>
        <input type="text" id="reference">
        <label for="notes">Notes</label>
        <textarea id="notes"></textarea>
        <button id="save-payment">Save</button>
        <button id="cancel-payment">Cancel</button>
    </div>
</div>

<!-- Add Credit Note Modal -->
<div id="credit-note-modal" class="modal">
    <div class="modal-content">
        <h3>Add Credit Note</h3>
        <label for="credit-customer">Customer</label>
        <input type="text" id="credit-customer">
        <label for="credit-amount">Amount</label>
        <input type="number" id="credit-amount">
        <label for="credit-issue-date">Issue Date</label>
        <input type="date" id="credit-issue-date">
        <label for="credit-expiry-date">Expiry Date (Optional)</label>
        <input type="date" id="credit-expiry-date">
        <label for="credit-notes">Notes</label>
        <textarea id="credit-notes"></textarea>
        <button id="save-credit-note">Save</button>
        <button id="cancel-credit-note">Cancel</button>
    </div>
</div>

<!-- Add Refund Modal -->
<div id="refund-modal" class="modal">
    <div class="modal-content">
        <h3>Add Refund</h3>
        <label for="refund-customer">Customer</label>
        <input type="text" id="refund-customer">
        <label for="refund-amount">Amount</label>
        <input type="number" id="refund-amount">
        <label for="refund-method">Refund Method</label>
        <select id="refund-method">
            <option value="bank-transfer">Bank Transfer</option>
            <option value="credit-card">Credit Card</option>
            <option value="paypal">PayPal</option>
        </select>
        <label for="refund-notes">Notes</label>
        <textarea id="refund-notes"></textarea>
        <button id="save-refund">Save</button>
        <button id="cancel-refund">Cancel</button>
    </div>
</div>

<script src="../js/invoice-payments.js"></script>
</body>
</html>
