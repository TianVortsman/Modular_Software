<?php
session_start();
$accountNumber = $_SESSION['account_number'] ?? 'ACC002';

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
    <meta name="account-number" content="<?php echo htmlspecialchars($accountNumber); ?>">
    <title>Invoice Products</title>
    <link rel="stylesheet" href="../../../public/assets/css/reset.css">
    <link rel="stylesheet" href="../../../public/assets/css/root.css">
    <link rel="stylesheet" href="../../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="../css/invoice-products.css">
    <link rel="stylesheet" href="../css/vehicle-details.css">
    <link rel="stylesheet" href="../css/add-vehicle.css">
    <link rel="stylesheet" href="../css/product-modals.css">
    <link rel="stylesheet" href="../css/invoice-modal.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
    <script src="../../../public/assets/js/sidebar.js"></script>
</head>
<body id="invoice-products">
<?php include('../../../src/UI/sidebar.php') ?>
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
                
                <!-- Filter Container -->
                <div class="filter-container">
                    <select id="category-filter" class="filter-dropdown">
                        <option value="">All Categories</option>
                    </select>
                    <select id="subcategory-filter" class="filter-dropdown">
                        <option value="">All Subcategories</option>
                    </select>
                    <button id="clear-filters" class="clear-filters-btn">Clear Filters</button>
                </div>
                
                <button id="openProductsInvoicesModalButton" class="modal-products-invoices-open-button">Products Invoices</button>
            </div>

            <!-- Products Section -->
            <div class="tab-content active" id="products">
            <button class="add-products-open-btn">Add Products</button>
                <h2>Products</h2>
                <div class="products-grid" id="products-grid">

                </div>
            </div>

            <!-- Vehicles Section -->
            <div class="tab-content" id="vehicles">
            <button class="add-vehicle-open-btn">Add Vehicle</button>
                <h2>Vehicles</h2>
                <div class="products-grid" id="vehicles-grid">

                </div>
            </div>

            <!-- Parts Section -->
            <div class="tab-content" id="parts">
            <button class="add-parts-open-btn">Add Parts</button>
                <h2>Parts</h2>
                <div class="products-grid" id="parts-grid">

                </div>
            </div>

            <!-- Extras Section -->
            <div class="tab-content" id="extras">
            <button class="add-extras-open-btn">Add Extras</button>
                <h2>Extras</h2>
                <div class="products-grid" id="extras-grid">

                </div>
            </div>

            <!-- Services Section -->
            <div class="tab-content" id="services">
            <button class="add-services-open-btn">Add Services</button>
                <h2>Services</h2>
                <div class="products-grid" id="services-grid">
                    
                </div>
            </div>

            <!-- Discontinued Section -->
            <div class="tab-content" id="discontinued">
            <button class="add-discontinued-open-btn">Add Discontinued Product</button>
                <h2>Discontinued</h2>
                <div class="products-grid" id="discontinued-grid"></div>
            </div>

            <!-- Disabled Section -->
            <div class="tab-content" id="disabled">
            <button class="add-disabled-open-btn">Add Disabled Product</button>
                <h2>Disabled</h2>
                <div class="products-grid" id="disabled-grid"></div>
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
<?php include('../../../src/UI/loading-modal.php') ?>
<?php include('../../../src/UI/response-modal.php') ?>
<?php include('../modals/product-modals.php') ?>
<script type="module" src="../js/product-api.js"></script>
<script type="module" src="../js/product-form.js"></script>
<script type="module" src="../js/product-screen.js"></script>
<script type="module" src="../js/product-modals.js"></script>
</body>
</html>
