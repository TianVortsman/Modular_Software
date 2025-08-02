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
    <title>Invoices</title>
    <link rel="stylesheet" href="../../../public/assets/css/reset.css">
    <link rel="stylesheet" href="../../../public/assets/css/root.css">
    <link rel="stylesheet" href="../../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="../css/invoice-modal.css">
    <link rel="stylesheet" href="../css/client-search-dropdown.css">
    <link rel="stylesheet" href="../css/invoices.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
    <script src="../../../public/assets/js/sidebar.js"></script>
</head>
<body id="documents">
  <?php include('../../../src/UI/sidebar.php'); ?>
<div class="screen-container">
    <div class="invoices-screen">
        <h2>Documents</h2>
        <div class="document-tabs-container">
            <button class="tab-button active" data-section="invoices-section">Invoices</button>
            <button class="tab-button" data-section="recurring-invoices-section">Recurring Invoices</button>
            <button class="tab-button" data-section="quotations-section">Quotations</button>
            <button class="tab-button" data-section="vehicle-quotations-section">Vehicle Quotations</button>
            <button class="tab-button" data-section="vehicle-invoices-section">Vehicle Invoices</button>
        </div>
        <!-- Invoices Section -->
        <div class="document-section" id="invoices-section">
            <h3>Invoices</h3>
            <div class="actions-container">
                <div class="filter-container">
                    <div class="invoice-filter">
                        <label for="date-from">From:</label>
                        <input type="date" id="date-from">
                        <label for="date-to">To:</label>
                        <input type="date" id="date-to">
                        <label for="client-name-invoices">Client:</label>
                        <input type="text" id="client-name-invoices" placeholder="Filter by client...">
                        <input type="hidden" id="client-id-invoices">
                        <div id="search-results-client-invoices" class="search-results-client"></div>
                    </div>
                    <div class="tabs-container">
                        <div class="subtab-row">
                            <button class="subtab-button active" data-status="all">All</button>
                            <button class="subtab-button" data-status="paid">Paid</button>
                            <button class="subtab-button" data-status="unpaid">Unpaid</button>
                            <button class="subtab-button" data-status="overdue">Overdue</button>
                        </div>
                    </div>
                    <div class="table-container">
                        <div class="invoice-table-container">
                            <table class="invoice-table">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Client</th>
                                        <th>Date Created</th>
                                        <th>Last Modified</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                        <th>Due Date</th>
                                    </tr>
                                </thead>
                                <tbody id="invoice-body">
                                    <!-- Table rows dynamically filled based on tab -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recurring Invoices Section -->
        <div class="document-section" id="recurring-invoices-section" style="display:none;">
            <h3>Recurring Invoices</h3>
            <div class="actions-container">
                <div class="filter-container">
                    <div class="invoice-filter">
                        <label for="recurring-date-from">From:</label>
                        <input type="date" id="recurring-date-from">
                        <label for="recurring-date-to">To:</label>
                        <input type="date" id="recurring-date-to">
                        <label for="client-name-recurring">Client:</label>
                        <input type="text" id="client-name-recurring" placeholder="Filter by client...">
                        <input type="hidden" id="client-id-recurring">
                        <div id="search-results-client-recurring" class="search-results-client"></div>
                    </div>
                    <div class="tabs-container">
                        <div class="subtab-row">
                            <button class="subtab-button active" data-status="all">All</button>
                            <button class="subtab-button" data-status="active">Active</button>
                            <button class="subtab-button" data-status="paused">Paused</button>
                            <button class="subtab-button" data-status="cancelled">Cancelled</button>
                        </div>
                    </div>
                    <div class="table-container">
                        <div class="invoice-table-container">
                            <table class="invoice-table">
                                <thead>
                                    <tr>
                                        <th>Invoice #</th>
                                        <th>Client</th>
                                        <th>Start Date</th>
                                        <th>Next Generation</th>
                                        <th>Frequency</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="recurring-invoice-body">
                                    <!-- Recurring invoice rows dynamically filled -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Quotations Section -->
        <div class="document-section" id="quotations-section" style="display:none;">
            <h3>Quotations</h3>
            <div class="actions-container">
                <div class="filter-container">
                    <div class="invoice-filter">
                        <label for="quotation-date-from">From:</label>
                        <input type="date" id="quotation-date-from">
                        <label for="quotation-date-to">To:</label>
                        <input type="date" id="quotation-date-to">
                        <label for="client-name-quotations">Client:</label>
                        <input type="text" id="client-name-quotations" placeholder="Filter by client...">
                        <input type="hidden" id="client-id-quotations">
                        <div id="search-results-client-quotations" class="search-results-client"></div>
                    </div>
                    <div class="tabs-container">
                        <div class="subtab-row">
                            <button class="subtab-button active" data-status="all">All</button>
                            <button class="subtab-button" data-status="approved">Approved</button>
                            <button class="subtab-button" data-status="pending">Pending</button>
                            <button class="subtab-button" data-status="rejected">Rejected</button>
                        </div>
                    </div>
                    <div class="table-container">
                        <div class="invoice-table-container">
                            <table class="invoice-table">
                                <thead>
                                    <tr>
                                        <th>Quotation #</th>
                                        <th>Client</th>
                                        <th>Date Created</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody id="quotation-body">
                                    <!-- Quotation rows dynamically filled -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Quotations Section -->
        <div class="document-section" id="vehicle-quotations-section" style="display:none;">
            <h3>Vehicle Quotations</h3>
            <div class="actions-container">
                <div class="filter-container">
                    <div class="invoice-filter">
                        <label for="vehicle-quotation-date-from">From:</label>
                        <input type="date" id="vehicle-quotation-date-from">
                        <label for="vehicle-quotation-date-to">To:</label>
                        <input type="date" id="vehicle-quotation-date-to">
                        <label for="client-name-vehicle-quotations">Client:</label>
                        <input type="text" id="client-name-vehicle-quotations" placeholder="Filter by client...">
                        <input type="hidden" id="client-id-vehicle-quotations">
                        <div id="search-results-client-vehicle-quotations" class="search-results-client"></div>
                    </div>
                    <div class="tabs-container">
                        <div class="subtab-row">
                            <button class="subtab-button active" data-status="all">All</button>
                            <button class="subtab-button" data-status="approved">Approved</button>
                            <button class="subtab-button" data-status="pending">Pending</button>
                            <button class="subtab-button" data-status="rejected">Rejected</button>
                        </div>
                    </div>
                    <div class="table-container">
                        <div class="invoice-table-container">
                            <table class="invoice-table">
                                <thead>
                                    <tr>
                                        <th>Vehicle Quotation #</th>
                                        <th>Client</th>
                                        <th>Vehicle</th>
                                        <th>Date Created</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody id="vehicle-quotation-body">
                                    <!-- Vehicle quotation rows dynamically filled -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Vehicle Invoices Section -->
        <div class="document-section" id="vehicle-invoices-section" style="display:none;">
            <h3>Vehicle Invoices</h3>
            <div class="actions-container">
                <div class="filter-container">
                    <div class="invoice-filter">
                        <label for="vehicle-invoice-date-from">From:</label>
                        <input type="date" id="vehicle-invoice-date-from">
                        <label for="vehicle-invoice-date-to">To:</label>
                        <input type="date" id="vehicle-invoice-date-to">
                        <label for="client-name-vehicle-invoices">Client:</label>
                        <input type="text" id="client-name-vehicle-invoices" placeholder="Filter by client...">
                        <input type="hidden" id="client-id-vehicle-invoices">
                        <div id="search-results-client-vehicle-invoices" class="search-results-client"></div>
                    </div>
                    <div class="tabs-container">
                        <div class="subtab-row">
                            <button class="subtab-button active" data-status="all">All</button>
                            <button class="subtab-button" data-status="paid">Paid</button>
                            <button class="subtab-button" data-status="unpaid">Unpaid</button>
                            <button class="subtab-button" data-status="overdue">Overdue</button>
                        </div>
                    </div>
                    <div class="table-container">
                        <div class="invoice-table-container">
                            <table class="invoice-table">
                                <thead>
                                    <tr>
                                        <th>Vehicle Invoice #</th>
                                        <th>Client</th>
                                        <th>Vehicle</th>
                                        <th>Date Created</th>
                                        <th>Status</th>
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody id="vehicle-invoice-body">
                                    <!-- Vehicle invoice rows dynamically filled -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
<script type="module" src="../js/document-api.js"></script>
<script type="module" src="../js/document-form.js"></script>
<script type="module" src="../js/document-screen.js"></script>
<script type="module" src="../js/document-modal.js"></script>
<script src="https://unpkg.com/lucide@latest/dist/umd/lucide.js"></script>
<?php include('../modals/document-modal.php')?>
<?php include('../../../src/UI/response-modal.php'); ?>
<?php include('../../../src/UI/loading-modal.php'); ?>
</body>
</html>