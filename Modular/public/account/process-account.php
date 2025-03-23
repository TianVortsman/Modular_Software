<?php
session_start();

// Ensure the user is logged in and the account number is passed
if (!isset($_SESSION['user_logged_in']) || !isset($_POST['account_number'])) {
    header("Location: ../index.php");
    exit();
}

// Retrieve the selected account number
$accountNumber = $_POST['account_number'];

// Set the account number in the session
$_SESSION['account_number'] = $accountNumber;

// Redirect to the dashboard
header("Location: ../views/dashboard.php");
exit();
