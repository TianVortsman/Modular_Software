<?php
session_start();

// Check if account number is in the query parameters
if (isset($_GET['account_number'])) {
    $account_number = $_GET['account_number'];

    // Store the account number in the session
    $_SESSION['account_number'] = $account_number;

    // Optionally, redirect to remove the query parameter from the URL
    exit;
}

// If the account number is already in the session, use it
if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
} else {
    // Redirect to login or show an error if no account number is found
    header("Location: index.php");
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
    <title>Products Screen</title>
    <link rel="stylesheet" href="../../../css/reset.css">
    <link rel="stylesheet" href="../../../css/root.css">
    <link rel="stylesheet" href="../../../css/sidebar.css">
    <link rel="stylesheet" href="../css/invoice-products.css">
    <link rel="stylesheet" href="../css/vehicle-details.css">
    <link rel="stylesheet" href="../css/add-vehicle.css">
    <link rel="stylesheet" href="../css/product-modals.css">
    <script src="../../../js/toggle-theme.js" type="module"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="../../../js/sidebar.js"></script>
</head>
<body id="invoice-products">
<?php include('../../../main/sidebar.php') ?>
<div class="products-container">
        <header class="header">
            <div class="header-left">
                <h1>Products</h1>
            </div>
        </header>
        
        <div class="tabs-content">
            <div class="header-right">
                <span class="material-icons search-icon">search</span>
                <input type="text" placeholder="Search for products..." class="search-input" id="search-input">
                <button id="openProductsInvoicesModalButton" class="modal-products-invoices-open-button">Products Invoices</button>
            </div>

            <!-- Products Section -->
            <div class="tab-content active" id="products">
            <button onclick="openAddProductModal()" class="add-products-open-btn">Add Products</button>
                <h2>Products</h2>
                <div class="products-grid" id="products-grid">

                </div>
            </div>

            <!-- Vehicles Section -->
            <div class="tab-content" id="vehicles">
            <button onclick="openAddVehicleModal()" class="add-vehicle-open-btn">Add Vehicle</button>
                <h2>Vehicles</h2>
                <div class="products-grid" id="vehicles-grid">

                </div>
            </div>

            <!-- Parts Section -->
            <div class="tab-content" id="parts">
            <button onclick="openAddPartModal()" class="add-parts-open-btn">Add Parts</button>
                <h2>Parts</h2>
                <div class="products-grid" id="parts-grid">

                </div>
            </div>

            <!-- Extras Section -->
            <div class="tab-content" id="extras">
            <button onclick="openAddExtraModal()" class="add-extras-open-btn">Add Extras</button>
                <h2>Extras</h2>
                <div class="products-grid" id="extras-grid">

                </div>
            </div>

            <!-- Services Section -->
            <div class="tab-content" id="services">
            <button onclick="openAddServiceModal()" class="add-services-open-btn">Add Services</button>
                <h2>Services</h2>
                <div class="products-grid" id="services-grid">
                    
                </div>
            </div>
        </div>
    </div>

    <!-- Modal HTML -->
    <div id="modal-upload-pdf" class="modal-upload-pdf" style="display:none;" >
    <div class="modal-upload-pdf-content">
        <span class="modal-upload-pdf-close">&times;</span>
        <h2>Upload Invoice</h2>
        
        <!-- Dropzone for PDF upload -->
        <div class="modal-upload-pdf-dropzone" id="pdf-dropzone">
            Drop PDF here or click to upload
        </div>
        <input type="file" id="pdf-file-input" style="display: none;" accept="application/pdf" />
        
        <!-- Table for displaying extracted items -->
        <div class="modal-upload-pdf-items">
            <table class="modal-upload-pdf-table">
                <thead>
                    <tr>
                        <th>Product ID</th>
                        <th>Product Name</th>
                        <th>Item Code</th>
                        <th>Description</th>
                        <th>Unit Price</th>
                        <th>Cost Price</th>
                        <th>Tax Percentage</th>
                        <th>Stock Quantity</th>
                    </tr>
                </thead>
                <tbody id="extracted-items">
                    <!-- Rows dynamically generated here after PDF upload and processing -->
                </tbody>
            </table>
        </div>

        <!-- Button to import the data -->
        <button class="modal-upload-pdf-import-button">Import Data</button>
    </div>
</div>
</div>
<?php include('../modals/vehicle-details-modal.php') ?>
<?php include('../modals/add-vehicle-modal.php') ?>
<?php include('../../../php/loading-modal.php') ?>
<?php include('../../../php/response-modal.php') ?>
<?php include('../modals/product-modals.php') ?>
<script src="../js/vehicle-modal.js"></script>
<script src="../js/add-vehicle.js"></script>
<script src="../js/product-modals.js"></script>
<script src="../js/fetch-products.js"></script>
</body>
</html>
