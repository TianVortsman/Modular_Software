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
include('../../php/db.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Accounting Dashboard</title>
    <link rel="stylesheet" href="../../css/reset.css">
    <link rel="stylesheet" href="../../css/root.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="css/accounting-dashboard.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
    <script src="../../js/toggle-theme.js" type="module"></script>
    <script src="../../js/sidebar.js"></script>
    <script src="js/accounting-dashboard.js"></script>
</head>
<body id="accounting-dashboard">
    <div class="dashboard-container">
        <!-- Include Sidebar -->
        <?php include '../../main/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="dashboard-main">
            <!-- Header Section -->
            <header class="dashboard-header">
                <h1>Accounting Dashboard</h1>
            </header>

            <!-- Key Metrics Section -->
            <section class="key-metrics">
                <div class="metric-card">
                    <h3>Total Revenue</h3>
                    <p>$50,000</p>
                </div>
                <div class="metric-card">
                    <h3>Net Profit</h3>
                    <p>$25,000</p>
                </div>
                <div class="metric-card">
                    <h3>Expenses</h3>
                    <p>$12,500</p>
                </div>
                <div class="metric-card">
                    <h3>Accounts Receivable</h3>
                    <p>$15,000</p>
                </div>
            </section>

            <!-- Interactive Graphs Section -->
            <section class="graphs">
                <div class="graph-container">
                    <h2>Profit and Loss</h2>
                    <div class="chart-placeholder" id="profit-loss-chart"></div>
                </div>
                <div class="graph-container">
                    <h2>Cash Flow</h2>
                    <div class="chart-placeholder" id="revenue-chart"></div>
                </div>
                <div class="graph-container">
                    <h2>Expense Breakdown</h2>
                    <div class="chart-placeholder" id="expenses-chart"></div>
                </div>
            </section>

            <!-- Recent Transactions Table -->
            <section class="recent-transactions">
                <h2>Recent Transactions</h2>
                <table class="transactions-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Account</th>
                            <th>Description</th>
                            <th>Debit</th>
                            <th>Credit</th>
                            <th>Balance</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2025-01-10</td>
                            <td>Sales Revenue</td>
                            <td>Customer Payment</td>
                            <td>$0</td>
                            <td>$5,000</td>
                            <td>$50,000</td>
                        </tr>
                        <tr>
                            <td>2025-01-12</td>
                            <td>Utilities Expense</td>
                            <td>Electricity Bill</td>
                            <td>$200</td>
                            <td>$0</td>
                            <td>$49,800</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Accounts Overview Section -->
            <section class="accounts-overview">
                <h2>Accounts Overview</h2>
                <table class="accounts-table">
                    <thead>
                        <tr>
                            <th>Account</th>
                            <th>Balance</th>
                            <th>Type</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>Cash on Hand</td>
                            <td>$15,000</td>
                            <td>Asset</td>
                        </tr>
                        <tr>
                            <td>Accounts Receivable</td>
                            <td>$12,500</td>
                            <td>Asset</td>
                        </tr>
                        <tr>
                            <td>Accounts Payable</td>
                            <td>$8,000</td>
                            <td>Liability</td>
                        </tr>
                        <tr>
                            <td>Retained Earnings</td>
                            <td>$35,000</td>
                            <td>Equity</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- To-Do List / Reminders Section -->
            <section class="to-do">
                <h2>Reminders</h2>
                <ul class="to-do-list">
                    <li>File quarterly tax returns by Jan 31</li>
                    <li>Reconcile bank statements for December</li>
                    <li>Approve payroll for February</li>
                </ul>
            </section>
        </main>
    </div>
</body>
</html>
