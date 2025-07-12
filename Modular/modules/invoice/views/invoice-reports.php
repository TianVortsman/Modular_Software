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
    <title>Invoice Reports</title>
    <link rel="stylesheet" href="../../../public/assets/css/reset.css">
    <link rel="stylesheet" href="../../../public/assets/css/root.css">
    <link rel="stylesheet" href="../../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="../css/invoice-reports.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
    <script src="../../../public/assets/js/sidebar.js"></script>
</head>
<body id="invoice-reports">
    <?php include ('../../../src/UI/sidebar.php') ?>
        <div class="reports-section active"  id="sales-reports">
            <section class="sales-reports">
                <div class="reports-container">
                    <ul class="reports">
                        <li>Sales Report 1</li>
                        <li>Sales Report 2</li>
                        <li>Sales Report 3</li>
                        <li>Sales Report 4</li>
                    </ul>
                </div>
            </section>
            <div class="report-actions">
                <div class="report-filters">
                    <div>
                        <select name="" id="">
                            <option value="">Select a report</option>
                            <option value="customer">Sales by Customer</option>
                            <option value="product">Sales by Product</option>
                            <option value="date">Sales by Date</option>
                            <option value="invoice">Sales by Invoice</option>
                        </select>
                    </div>
                    <div class="date-filters">
                        <label for="start-date">Start Date</label>
                        <input type="date" name="start-date" id="start-date">
                        <label for="end-date">End Date</label>
                        <input type="date" name="end-date" id="end-date">
                    </div>
                </div>
            </div>
        </div>

        <div class="reports-section"  id="tax-reports">
            <section class="tax-reports">
                <div class="reports-container">
                    <ul class="reports">
                        <li>Tax Report 1</li>
                        <li>Tax Report 2</li>
                        <li>Tax Report 3</li>
                        <li>Tax Report 4</li>
                    </ul>
                </div>
            </section>
            <div class="report-actions">
                <div class="report-filters">
                    <div>
                        <select name="" id="">
                            <option value="">Select a report</option>
                            <option value="customer">Sales by Customer</option>
                            <option value="product">Sales by Product</option>
                            <option value="date">Sales by Date</option>
                            <option value="invoice">Sales by Invoice</option>
                        </select>
                    </div>
                    <div class="date-filters">
                        <label for="start-date">Start Date</label>
                        <input type="date" name="start-date" id="start-date">
                        <label for="end-date">End Date</label>
                        <input type="date" name="end-date" id="end-date">
                    </div>
                </div>
            </div>
        </div>
        <div class="reports-section"  id="income-reports">
            <section class="income-reports">
                <div class="reports-container">
                    <ul class="reports">
                        <li>Income Report 1</li>
                        <li>Income Report 2</li>
                        <li>Income Report 3</li>
                        <li>Income Report 4</li>
                    </ul>
                </div>
            </section>
            <div class="report-actions">
                <div class="report-filters">
                    <div>
                        <select name="" id="">
                            <option value="">Select a report</option>
                            <option value="customer">Sales by Customer</option>
                            <option value="product">Sales by Product</option>
                            <option value="date">Sales by Date</option>
                            <option value="invoice">Sales by Invoice</option>
                        </select>
                    </div>
                    <div class="date-filters">
                        <label for="start-date">Start Date</label>
                        <input type="date" name="start-date" id="start-date">
                        <label for="end-date">End Date</label>
                        <input type="date" name="end-date" id="end-date">
                    </div>
                </div>
            </div>
        </div>
        <div class="reports-section" id="expenses-reports">
            <section class="expenses-reports">
                <div class="reports-container">
                    <ul class="reports">
                        <li>Expenses Report 1</li>
                        <li>Expenses Report 2</li>
                        <li>Expenses Report 3</li>
                        <li>Expenses Report 4</li>
                    </ul>
                </div>
            </section>
            <div class="report-actions">
                <div class="report-filters">
                    <div>
                        <select name="" id="">
                            <option value="">Select a report</option>
                            <option value="customer">Sales by Customer</option>
                            <option value="product">Sales by Product</option>
                            <option value="date">Sales by Date</option>
                            <option value="invoice">Sales by Invoice</option>
                        </select>
                    </div>
                    <div class="date-filters">
                        <label for="start-date">Start Date</label>
                        <input type="date" name="start-date" id="start-date">
                        <label for="end-date">End Date</label>
                        <input type="date" name="end-date" id="end-date">
                    </div>
                </div>
            </div>
        </div>
        <div class="reports-section" id="general-reports">
            <section class="general-reports">
                <div class="reports-container">
                    <ul class="reports">
                        <li>General Report 1</li>
                        <li>General Report 2</li>
                        <li>General Report 3</li>
                        <li>General Report 4</li>
                    </ul>
                </div>
            </section>
            <div class="report-actions">
                <div class="report-filters">
                    <div>
                        <select name="" id="">
                            <option value="">Select a report</option>
                            <option value="customer">Sales by Customer</option>
                            <option value="product">Sales by Product</option>
                            <option value="date">Sales by Date</option>
                            <option value="invoice">Sales by Invoice</option>
                        </select>
                    </div>
                    <div class="date-filters">
                        <label for="start-date">Start Date</label>
                        <input type="date" name="start-date" id="start-date">
                        <label for="end-date">End Date</label>
                        <input type="date" name="end-date" id="end-date">
                    </div>
                </div>
            </div>
        </div>
        <script src="../js/invoice-reports.js"></script>
</body>
</html>