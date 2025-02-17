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
    <title>Clients</title>
    <link rel="stylesheet" href="../../../css/reset.css">
    <link rel="stylesheet" href="../../../css/root.css">
    <link rel="stylesheet" href="../../../css/sidebar.css">
    <link rel="stylesheet" href="../css/invoice-clients.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="icon" href="../../path/to/favicon.ico" type="image/x-icon">
    <script src="../../../js/toggle-theme.js" type="module"></script>
    <script src="../../../js/sidebar.js"></script>
</head>
<body id="invoice-clients">
<div class="screen-container">
    <?php include('../../../main/sidebar.php') ?>
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

                    <!-- Search Container -->
                    <div class="search-container">
                        <span class="material-icons search-icon">search</span>
                        <input type="text" id="client-search" placeholder="Search by Client Name or ID...">
                    </div>
                
                <!-- Table for Client Data -->
                <div class="client-section1 active" id="client-section1">
                    <label for="rows-per-page">Show:</label>
                        <select id="rows-per-page" class="rows-per-page">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    <div class="table-container">
                        <div class="client-table-container">
                            <table class="client-table">
                                <thead>
                                    <tr>
                                        <th>Client ID</th>
                                        <th>Client Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Last Invoice Date</th>
                                        <th>Outstanding Balance</th>
                                        <th>Total Invoices</th>
                                    </tr>
                                </thead>
                                <tbody id="client-body">
                                </tbody>
                                <div id="pagination-container1" class="pagination-container"></div>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="client-section2" id="client-section2">
                <label for="rows-per-page">Show:</label>
                        <select id="rows-per-page" class="rows-per-page">
                            <option value="5">5</option>
                            <option value="10" selected>10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                        </select>
                    <div class="table-container">
                        <div class="client-table-container">
                            <table class="client-table">
                                <thead>
                                    <tr>
                                        <th>Company ID</th>
                                        <th>Company Name</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Last Invoice Date</th>
                                        <th>Outstanding Balance</th>
                                        <th>Total Invoices</th>
                                    </tr>
                                </thead>
                                <tbody id="client-body">
                                </tbody>
                                <div id="pagination-container2" class="pagination-container"></div>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php include('../modals/clientModals.php') ?>
<?php include('../../../php/loading-modal.php') ?>
<?php include('../../../php/response-modal.php') ?>
<script src="../js/invoice-clients.js"></script>
</body>
</html>
