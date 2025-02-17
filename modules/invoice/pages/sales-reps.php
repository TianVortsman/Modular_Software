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
include('../../../php/db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Representatives</title>
    <link rel="stylesheet" href="../../../css/reset.css">
    <link rel="stylesheet" href="../../../css/root.css">
    <link rel="stylesheet" href="../../../css/sidebar.css">
    <link rel="stylesheet" href="../css/sales-reps.css">
    <link rel="stylesheet" href="../css/sales-reps-modal.css">
    <link rel="stylesheet" href="../css/add-sales-rep-modal.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" href="../../path/to/favicon.ico" type="image/x-icon">
    <script src="../../../js/toggle-theme.js" type="module"></script>
    <script src="../../../js/sidebar.js"></script>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<!-- ApexCharts -->
    <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>

</head>
<body class="dashboard" id="sales-reps">
    <!-- Sidebar -->
     <?php include('../../../main/sidebar.php'); ?> 
    <div class="sales-content">
        <header class="header">
            <div class="logo">📊</div>
            <h1>Sales Manager Dashboard</h1>
        </header>

        <main>
            <!-- Key Metrics Section -->
            <section class="key-metrics">
                <div class="metric-card">
                    <h2>Deals Won Value vs Target</h2>
                    <div class="radial-gauge"></div>
                </div>
                <div class="metric-card">
                    <h2>Forecasted New Revenue</h2>
                    <p class="metric-value">$250,000</p>
                </div>
                <div class="metric-card">
                    <h2>Inbound Revenue</h2>
                    <p class="metric-percentage">65%</p>
                </div>
                <div class="metric-card">
                    <h2>Outbound Revenue</h2>
                    <p class="metric-percentage">35%</p>
                </div>
            </section>

            <!-- Leaderboard Section -->
             <div class="leaderboard-container">
                <section class="leaderboard">
                    <h2>Top Sales Reps</h2>
                    <div class="rep-card">
                        <div class="rep-rank one">1st</div>
                        <img src="../img/face-placeholder.jpg" alt="Representative Photo" srcset="" class="rep-photo gold-border">
                        <p class="rep-name">John Doe</p>
                        <p class="rep-revenue">R120,000</p>
                    </div>
                    <div class="rep-card">
                        <div class="rep-rank two">2nd</div>
                        <img src="../img/face-placeholder.jpg" alt="Representative Photo" srcset="" class="rep-photo silver-border">
                        <p class="rep-name">Jane Smith</p>
                        <p class="rep-revenue">R95,000</p>
                    </div>
                    <div class="rep-card">
                        <div class="rep-rank three">3rd</div>
                        <img src="../img/face-placeholder.jpg" alt="Representative Photo" srcset="" class="rep-photo bronze-border">
                        <p class="rep-name">Alan Brown</p>
                        <p class="rep-revenue">R85,000</p>
                    </div>
                </section>

                <!-- Pie Chart Section -->
                <section class="bar-chart">
                    <h2>Upcoming Deals</h2>
                    <div class="chart-container">
                        <!-- Placeholder for pie chart -->
                        <div class="bar-chart-placeholder-1"></div>
                        <ul class="chart-legend">
                            <li>John: 12</li>
                            <li>Jane: 8</li>
                            <li>Alan: 5</li>
                        </ul>
                    </div>
                </section>
            </div>

            <!-- Table Section -->
            <section class="employee-table">
                <h2>Sales Reps</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Won Deals</th>
                            <th>Lost Deals</th>
                            <th>Amount Invoiced</th>
                            <th>Quotes</th>
                            <th>Team</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>John Doe</td>
                            <td>45</td>
                            <td>8</td>
                            <td>R120,000</td>
                            <td>12</td>
                            <td>Uk</td>
                        </tr>
                        <tr>
                            <td>Jane Smith</td>
                            <td>38</td>
                            <td>10</td>
                            <td>R95,000</td>
                            <td>8</td>
                            <td>US</td>
                        </tr>
                        <tr>
                            <td>Alan Brown</td>
                            <td>30</td>
                            <td>12</td>
                            <td>R85,000</td>
                            <td>5</td>
                            <td>IT</td>
                        </tr>
                    </tbody>
                </table>
            </section>

            <!-- Bar Chart Section -->
            <section class="bar-chart">
                <h2>Quotes by Team</h2>
                <div class="bar-chart-placeholder">
                    <!-- Placeholder for bar chart -->
                </div>
            </section>
        </main>
    </div>
    <?php include('../modals/addSalesRepModal.php'); ?>
    <script src="../js/sales-reps.js"></script>
</body>
</body>
</html>
