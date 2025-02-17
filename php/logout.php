<?php
session_start();

var_dump($_SESSION); // Debugging - Check session data

if (isset($_SESSION['user_logged_in']) && $_SESSION['user_logged_in'] === true) {
    // Regular User Logout - Destroy session completely
    $_SESSION['user_logged_in'] = false; // Mark user as logged out
    session_unset();
    session_destroy();
    header("Location: ../index.php");
    exit();
} elseif (isset($_SESSION['tech_logged_in']) && $_SESSION['tech_logged_in'] === true) {
    header("Location: ../main/techlogin.php");
    exit();
}

// If neither are set, just redirect to login to prevent errors
header("Location: ../index.php");
exit();
?>
