<?php
// Modified logout script that handles technician users differently
session_start();

// Check if the user is a technician 
if (isset($_SESSION['tech_logged_in']) && $_SESSION['tech_logged_in'] === true) {
    // For technicians, redirect to techlogin without clearing session
    header("Location: ../admin/techlogin.php");
    exit();
} else {
    // For regular users, proceed with full logout
    header("Location: ../../src/auth.php?action=logout");
    exit();
}
?> 