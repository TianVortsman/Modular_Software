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
    <title>Invoicing & Billing Dashboard</title>
    <link rel="stylesheet" href="../../../public/assets/css/reset.css">
    <link rel="stylesheet" href="../../../public/assets/css/root.css">
    <link rel="stylesheet" href="../../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="../css/invoice-dashboard.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
    <link rel="stylesheet" href="../css/invoice-modal.css">
    <link rel="stylesheet" href="../css/client-search-dropdown.css">
</head>
<body id="invoice-dashboard">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include('../../../src/UI/sidebar.php'); ?>
        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <div class="header">
                <h1>Invoicing & Billing Dashboard</h1>
            </div>

            <div class="dashboard-widgets">
                <!-- Date Range and Chart Type Selectors -->
                <div class="dashboard-controls" style="display: flex; gap: 16px; align-items: center; margin-bottom: 16px;">
                    <label for="dashboard-range-selector">Date Range:</label>
                    <select id="dashboard-range-selector">
                        <option value="this_month" selected>This Month</option>
                        <option value="3months">Last 3 Months</option>
                        <option value="quarter">Quarterly</option>
                        <option value="year">Yearly</option>
                    </select>
                    <label for="dashboard-chart-months">Chart Months:</label>
                    <select id="dashboard-chart-months">
                        <option value="3">Last 3 Months</option>
                        <option value="6" selected>Last 6 Months</option>
                        <option value="9">Last 9 Months</option>
                    </select>
                    <label for="dashboard-chart-type">Chart Type:</label>
                    <select id="dashboard-chart-type">
                        <option value="bar" selected>Bar</option>
                        <option value="line">Line</option>
                        <option value="pie">Pie</option>
                    </select>
                </div>

                <!-- Top Widgets -->
                <div class="widgets">
                    <div class="widget" id="total-invoices-widget">
                        <h3>Total Invoices</h3>
                        <p></p>
                    </div>
                    <div class="widget" id="total-revenue-widget">
                        <h3>Total Revenue</h3>
                        <p></p>
                    </div>
                    <div class="widget" id="total-unpaid-invoices-widget">
                        <h3>Unpaid Invoices</h3>
                        <p></p>
                    </div>
                    <div class="widget" id="pending-payments-widget">
                        <h3>Pending Payments</h3>
                        <p></p>
                    </div>
                    <div class="widget" id="month-expenses-widget">
                        <h3>Expenses This Month</h3>
                        <p></p>
                    </div>
                    <div class="widget" id="taxes-due-widget">
                        <h3>Taxes Due</h3>
                        <p></p>
                    </div>
                    <div class="widget" id="total-recurring-invoices-widget">
                        <h3>Recurring Invoices</h3>
                        <p></p>
                    </div>
                </div>

                <!-- Recent Invoices -->
                <div class="recent-invoices">
                    <h2>Recent Invoices</h2>
                    <table class="invoice-dashboard-table">
                        <thead>
                            <tr class="invoice-dashboard-table-header">
                                <th>Invoice #</th>
                                <th>Client</th>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Total</th>
                                <th>Due Date</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recent-invoices-tbody">
                        </tbody>
                    </table>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h2>Quick Actions</h2>
                    <button class="open-invoice-modal" id="open-invoice-modal-btn">Create New Invoice</button>
                    <button class="action-button">Send Payment Reminder</button>
                    <button class="action-button">Add Expense</button>
                </div>

                <!-- Invoice Analytics (New Section) -->
                <div class="invoice-analytics">
                    <h2>Invoice Analytics</h2>
                    <div class="analytics-graph">
                        <canvas id="invoiceChart" class="analytics-image dashboard-chart-canvas" width="900" height="350"></canvas>
                    </div>
                    <div class="analytics-summary">
                        <p><strong>Average Payment Time:</strong> <span class="analytics-value">15 days</span></p>
                        <p><strong>Overdue Invoices:</strong> <span class="analytics-value">8</span></p>
                    </div>
                </div>

                <!-- Recurring Invoice Management (New Section) -->
                <div class="recurring-invoices-management">
                    <h2>Recent Recurring Invoices</h2>
                    <table class="invoice-dashboard-table">
                        <thead>
                            <tr>
                                <th>Invoice #</th>
                                <th>Client</th>
                                <th>Start Date</th>
                                <th>Next Invoice</th>
                                <th>Interval</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody id="recurring-invoices-tbody">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Modular Software. All rights reserved.</p>
    </footer>
    <?php include('../modals/document-modal.php'); ?>
    <?php include '../../../src/UI/response-modal.php'; ?>
    <?php include '../../../src/UI/loading-modal.php'; ?>
    <script type="module" src="../js/dashboard.js"></script>
    <script src="../../../public/assets/js/sidebar.js"></script>
    <script>var multipleAccounts = <?= json_encode($multiple_accounts); ?>; </script>
</body>
</html>