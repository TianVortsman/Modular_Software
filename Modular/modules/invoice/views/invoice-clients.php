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
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
    <script src="../../../public/assets/js/sidebar.js"></script>
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
                            <option value="100">100</option>
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
                                <tbody id="client-body-private">
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
                                <tbody id="client-body-business">
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
<?php include('../../../src/UI/loading-modal.php') ?>
<?php include('../../../src/UI/response-modal.php') ?>
<script type="module" src="../js/client-screen.js"></script>
</body>
</html>
