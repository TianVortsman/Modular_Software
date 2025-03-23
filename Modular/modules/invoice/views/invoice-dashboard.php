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
$multiple_accounts = isset($_SESSION['multiple_accounts']) ? $_SESSION['multiple_accounts'] : false;

// Include the database connection
include('../../php/db.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invoicing & Billing Dashboard</title>
    <link rel="stylesheet" href="../../css/reset.css">
    <link rel="stylesheet" href="../../css/root.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="css/invoice-dashboard.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../js/toggle-theme.js" type="module"></script>
    <link rel="stylesheet" href="css/invoice-modal.css">
</head>
<body id="invoice-dashboard">
    <div class="dashboard-container">
        <!-- Sidebar -->
        <?php include('../../main/sidebar.php'); ?>
        <!-- Main Content -->
        <div class="main-content" id="main-content">
            <div class="header">
                <h1>Invoicing & Billing Dashboard</h1>
            </div>

            <div class="dashboard-widgets">
                <!-- Top Widgets -->
                <div class="widgets">
                    <div class="widget" id="total-invoices-widget">
                        <h3>Total Invoices</h3>
                        <p>128</p>
                    </div>
                    <div class="widget" id="total-revenue-widget">
                        <h3>Total Revenue</h3>
                        <p>R23,456</p>
                    </div>
                    <div class="widget" id="total-unpaid-invoices-widget">
                        <h3>Unpaid Invoices</h3>
                        <p>12</p>
                    </div>
                    <div class="widget" id="pending-payments-widget">
                        <h3>Pending Payments</h3>
                        <p>R4,500</p>
                    </div>
                    <div class="widget" id="month-expenses-widget">
                        <h3>Expenses This Month</h3> <!-- New -->
                        <p>R1,250</p>
                    </div>
                    <div class="widget" id="taxes-due-widget">
                        <h3>Taxes Due</h3> <!-- New -->
                        <p>R3,200</p>
                    </div>
                    <div class="widget" id="total-recurring-invoices-widget">
                        <h3>Recurring Invoices</h3> <!-- New -->
                        <p>5 Active</p>
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
                                <th>Due Date</th> <!-- New -->
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="invoice-dashboard-table-row">
                                <td>INV-001</td>
                                <td>Acme Corp</td>
                                <td>2024-10-10</td>
                                <td><span class="status paid">Paid</span></td>
                                <td>R1,500</td>
                                <td>2024-10-25</td> <!-- New -->
                                <td><button class="action-button">View</button></td>
                            </tr>
                            <tr>
                                <td>INV-002</td>
                                <td>Tech Solutions</td>
                                <td>2024-10-08</td>
                                <td><span class="status unpaid">Unpaid</span></td>
                                <td>R800</td>
                                <td>2024-10-20</td> <!-- New -->
                                <td><button class="action-button">View</button></td>
                            </tr>
                            <tr class="invoice-dashboard-table-row">
                                <td>INV-001</td>
                                <td>Acme Corp</td>
                                <td>2024-10-10</td>
                                <td><span class="status paid">Paid</span></td>
                                <td>R1,500</td>
                                <td>2024-10-25</td> <!-- New -->
                                <td><button class="action-button">View</button></td>
                            </tr>
                            <tr>
                                <td>INV-002</td>
                                <td>Tech Solutions</td>
                                <td>2024-10-08</td>
                                <td><span class="status unpaid">Unpaid</span></td>
                                <td>R800</td>
                                <td>2024-10-20</td> <!-- New -->
                                <td><button class="action-button">View</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <h2>Quick Actions</h2>
                    <button class="open-invoice-modal" onclick="openInvoiceModal()">Create New Invoice</button>
                    <button class="action-button">Send Payment Reminder</button>
                    <button class="action-button">Add Expense</button> <!-- New -->
                </div>

                <!-- Invoice Analytics (New Section) -->
                <div class="invoice-analytics">
                    <h2>Invoice Analytics</h2>
                    <div class="analytics-graph">
                        <canvas id="invoiceChart" class="analytics-image"></canvas>
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
                        <tbody>
                            <tr>
                                <td>REC-001</td>
                                <td>Global Tech</td>
                                <td>2024-09-01</td>
                                <td>2024-11-01</td>
                                <td>Monthly</td>
                                <td><button class="action-button">Edit</button></td>
                            </tr>
                            <tr>
                                <td>REC-002</td>
                                <td>Future Innovations</td>
                                <td>2024-05-15</td>
                                <td>2024-11-15</td>
                                <td>Quarterly</td>
                                <td><button class="action-button">Edit</button></td>
                            </tr>
                            <tr>
                                <td>REC-001</td>
                                <td>Global Tech</td>
                                <td>2024-09-01</td>
                                <td>2024-11-01</td>
                                <td>Monthly</td>
                                <td><button class="action-button">Edit</button></td>
                            </tr>
                            <tr>
                                <td>REC-002</td>
                                <td>Future Innovations</td>
                                <td>2024-05-15</td>
                                <td>2024-11-15</td>
                                <td>Quarterly</td>
                                <td><button class="action-button">Edit</button></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <footer>
        <p>&copy; 2024 Modular Software. All rights reserved.</p>
    </footer>
    <?php include('modals/invoice-modal.php'); ?>
    <script src="js/invoice-modal.js"></script>
    <script src="../../js/sidebar.js"></script>
    <script src="js/invoice-charts.js" type="module"></script>
    <script src="js/invoice-data.js"></script>
    <script>var multipleAccounts = <?= json_encode($multiple_accounts); ?>; </script>
</body>
</html>