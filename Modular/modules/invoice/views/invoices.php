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
    <link rel="stylesheet" href="../css/invoices.css">
    <link rel="stylesheet" href="../css/invoice-modal.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
    <script src="../../../public/assets/js/sidebar.js"></script>
    <script src="../js/invoice-modal.js"></script>
    <script src="../js/invoice-data.js"></script>
</head>
<body id="invoices">
  <?php include('../../../src/UI/sidebar.php'); ?>
<div class="screen-container">
    <div class="invoices-screen">
        <h2>Invoices</h2>
        
        <!-- Quick Actions -->
            <div class="actions-container">
                <div class="invoices-actions">
                    <button class="open-invoice-modal">+ New Invoice</button>
                    <button class="action-button">Send Reminder</button>
                    <button class="action-button">Export Data</button>
                </div>

            <!-- Filter Options -->
                <div class="filter-container">
                    <div class="invoice-filter">
                        <label for="date-from">From:</label>
                        <input type="date" id="date-from">
                        <label for="date-to">To:</label>
                        <input type="date" id="date-to">
                        <label for="client">Client:</label>
                        <input type="text" id="client-filter" placeholder="Filter by client...">
                    </div>

                <!-- Tabs for Invoice Status -->
                <div class="tabs-container">
                    <div class="invoice-tabs">
                        <button class="tab-button active" data-tab="all">All</button>
                        <button class="tab-button" data-tab="paid">Paid</button>
                        <button class="tab-button" data-tab="unpaid">Unpaid</button>
                        <button class="tab-button" data-tab="overdue">Overdue</button>
                        <button class="tab-button" data-tab="recurring">Recurring</button>
                        <div class="search-container">
                            <span class="material-icons search-icon">search</span>
                            <input type="text" id="invoice-search" placeholder="Search by Invoice Number or Client...">
                        </div>
                    </div>
                </div>

                <!-- Table for Invoice Data -->
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
                                    <th>Actions</th>
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
</div>
<script>
    document.addEventListener("DOMContentLoaded", function() {
    const tabs = document.querySelectorAll(".tab-button");
    const invoiceBody = document.getElementById("invoice-body");

    // Sample Data for Invoices
    const invoices = [
        { number: "INV001", client: "ABC Corp", created: "2023-10-01", modified: "2023-10-10", status: "Paid", total: "R1000", due: "2023-10-15" },
        { number: "INV002", client: "XYZ Ltd", created: "2023-09-15", modified: "2023-09-20", status: "Unpaid", total: "R2000", due: "2023-09-30" },
        { number: "INV003", client: "LMN Inc", created: "2023-10-05", modified: "2023-10-10", status: "Overdue", total: "R500", due: "2023-10-07" },
        { number: "INV004", client: "OPQ Group", created: "2023-10-02", modified: "2023-10-03", status: "Recurring", total: "R1500", due: "2023-11-02" }
    ];

    // Function to load invoice rows into the table
    function loadInvoices(filter) {
        invoiceBody.innerHTML = "";
        const filteredInvoices = invoices.filter(invoice => filter === "all" || invoice.status.toLowerCase() === filter);

        filteredInvoices.forEach(invoice => {
            const row = document.createElement("tr");
            row.innerHTML = `
                <td>${invoice.number}</td>
                <td>${invoice.client}</td>
                <td>${invoice.created}</td>
                <td>${invoice.modified}</td>
                <td>${invoice.status}</td>
                <td>${invoice.total}</td>
                <td>${invoice.due}</td>
                <td class="action-icons">
                    <span class="icon">🖊️</span>
                    <span class="icon">🗑️</span>
                </td>
            `;
            invoiceBody.appendChild(row);
        });
    }

    // Default load of all invoices
    loadInvoices("all");

    // Tab switching logic
    tabs.forEach(tab => {
        tab.addEventListener("click", function() {
            tabs.forEach(t => t.classList.remove("active"));
            tab.classList.add("active");
            const filter = tab.getAttribute("data-tab");
            loadInvoices(filter);
        });
    });
});
</script>

</body>
</html>