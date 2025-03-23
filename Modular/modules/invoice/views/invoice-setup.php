<?php
session_start();
require '../../../php/db.php'; // Ensure this file initializes $conn
require_once '../../../vendor/autoload.php';
require_once '../../../php/import_functions.php'; // Add the new import functions file

use PhpOffice\PhpSpreadsheet\IOFactory;

// Ensure PDO throws exceptions (if not already set in db.php)
$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

// Initialize global messages arrays
$errors = [];
$successMessages = [];

// Verify database connection
try {
    $conn->query('SELECT 1');
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if an Excel file was uploaded
if (isset($_FILES['excel_file']) && is_uploaded_file($_FILES['excel_file']['tmp_name'])) {
    $file = $_FILES['excel_file']['tmp_name'];
    try {
        $spreadsheet = IOFactory::load($file);
        
        // Import functions will be called from the centralized location
        $errors = array_merge($errors, importProducts($spreadsheet, $conn));
        $errors = array_merge($errors, importCompanies($spreadsheet, $conn));
        $errors = array_merge($errors, importCustomers($spreadsheet, $conn));
        $errors = array_merge($errors, importVehicles($spreadsheet, $conn));
    } catch (Exception $e) {
        $errors[] = "Error loading Excel file: " . $e->getMessage();
    }
} else {
    $errors[] = "No Excel file uploaded.";
}

// Session management for account number and user
if (isset($_GET['account_number'])) {
    // Sanitize the input
    $account_number = filter_input(INPUT_GET, 'account_number', FILTER_SANITIZE_STRING);
    $_SESSION['account_number'] = $account_number;
    // Redirect to remove the query parameter from the URL
    header("Location: invoice-setup.php");
    exit;
}

if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
} else {
    // Redirect to login if no account number is found
    header("Location: index.php");
    exit;
}

$userName = $_SESSION['user_name'] ?? (($_SESSION['tech_logged_in'] ?? false) ? $_SESSION['tech_name'] : 'Guest');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Import Products &amp; Display</title>
    <link rel="stylesheet" href="../../../css/root.css">
    <link rel="stylesheet" href="../../../css/sidebar.css">
    <link rel="stylesheet" href="../css/invoice-setup.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="../../../js/toggle-theme.js"></script>
    <script src="../../../js/sidebar.js"></script>
    <!-- Include the improved JavaScript file or embed the script below -->
    <script src="path/to/your/improved.js" defer></script>
    <style>
        /* Optional inline CSS for error display and grids */
        .js-error-log { color: red; margin-top: 20px; }
        .grid { margin-top: 20px; display: flex; flex-wrap: wrap; gap: 10px; }
        .product-card { border: 1px solid #ccc; padding: 10px; width: 200px; }
    </style>
</head>
<body id="invoice-setup">
    <?php include('../../../main/sidebar.php'); ?>
    <div class="container">
        <h1>Import Products from Excel</h1>
        <div class="form-box">
            <form action="invoice-setup.php" method="post" enctype="multipart/form-data">
                <div class="input-group">
                    <input type="file" name="excel_file" accept=".xlsx, .xls">
                </div>
                <div class="button-group">
                    <button type="submit">Import</button>
                </div>
            </form>
        </div>
        <!-- PHP error and success logs -->
        <?php if (!empty(array_filter($errors))): ?>
            <div class="error-log">
                <h2>Import Errors</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Error Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($errors as $error): ?>
                            <?php if (!empty($error)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($error); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <?php if (!empty(array_filter($successMessages))): ?>
            <div class="success-log">
                <h2>Import Successes</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Success Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($successMessages as $message): ?>
                            <?php if (!empty($message)): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($message); ?></td>
                                </tr>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        <!-- Container for JavaScript error messages -->
        <div id="js-error-log" class="js-error-log">
            <h2>JavaScript Errors</h2>
        </div>
    </div>
</body>
</html>

