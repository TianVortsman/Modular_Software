<?php
// Comprehensive login test script
// Start session first to avoid warnings
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/Core/Auth/Authentication.php';
require_once __DIR__ . '/Services/DatabaseService.php';

header('Content-Type: text/plain');

echo "Login Test\n";
echo "==========\n\n";

// Test database connection first
echo "Testing database connection...\n";
$db = App\Services\DatabaseService::getMainDatabase();
if ($db->testConnection()) {
    echo "Database connection successful.\n\n";
} else {
    echo "ERROR: Database connection failed.\n\n";
    exit;
}

try {
    // Create standard Authentication instance
    $auth = new App\Core\Auth\Authentication();
    
    // Check if the tech user exists and verify password hash format
    echo "Checking technician account...\n";
    $techEmail = 'tianryno01@gmail.com';
    $sql = "SELECT * FROM technicians WHERE email = $1";
    $result = $db->executeQuery("check_tech_direct", $sql, array($techEmail));
    
    if ($result && $db->numRows($result) > 0) {
        $tech = $db->fetchRow($result);
        echo "Technician found: " . $tech['name'] . "\n";
        echo "Password hash: " . substr($tech['password'], 0, 13) . "...[truncated]\n";
        echo "Hash format check: " . (str_starts_with($tech['password'], '$2y$') ? "Valid bcrypt hash" : "INVALID HASH FORMAT") . "\n\n";
        
        // Try to verify with our known password
        $techPassword = 'Modul@rdev@2024';
        $verified = password_verify($techPassword, $tech['password']);
        echo "Password verification test: " . ($verified ? "SUCCESS" : "FAILED") . "\n\n";
        
        if (!$verified) {
            echo "Updating technician password for testing...\n";
            $newHash = password_hash($techPassword, PASSWORD_DEFAULT);
            $updateSql = "UPDATE technicians SET password = $1 WHERE email = $2";
            $updateResult = $db->executeQuery("update_tech_pwd", $updateSql, array($newHash, $techEmail));
            echo "Password update: " . ($updateResult ? "SUCCESS" : "FAILED") . "\n\n";
        }
    } else {
        echo "Technician not found or query failed.\n\n";
    }
    
    // Check if the regular user exists and verify password hash format
    echo "Checking regular user account...\n";
    $userEmail = 'tian@uniclox.com';
    $sql = "SELECT * FROM users WHERE email = $1";
    $result = $db->executeQuery("check_user_direct", $sql, array($userEmail));
    
    if ($result && $db->numRows($result) > 0) {
        $user = $db->fetchRow($result);
        echo "User found: " . $user['name'] . "\n";
        echo "Password hash: " . substr($user['password'], 0, 13) . "...[truncated]\n";
        echo "Hash format check: " . (str_starts_with($user['password'], '$2y$') ? "Valid bcrypt hash" : "INVALID HASH FORMAT") . "\n\n";
        
        // Try to verify with our known password
        $userPassword = 'H3llOnline2001';
        $verified = password_verify($userPassword, $user['password']);
        echo "Password verification test: " . ($verified ? "SUCCESS" : "FAILED") . "\n\n";
        
        if (!$verified) {
            echo "Updating user password for testing...\n";
            $newHash = password_hash($userPassword, PASSWORD_DEFAULT);
            $updateSql = "UPDATE users SET password = $1 WHERE email = $2";
            $updateResult = $db->executeQuery("update_user_pwd", $updateSql, array($newHash, $userEmail));
            echo "Password update: " . ($updateResult ? "SUCCESS" : "FAILED") . "\n\n";
        }
    } else {
        echo "User not found or query failed.\n\n";
    }
    
    // Test login for technician
    echo "Testing login with technician credentials...\n";
    $techResult = $auth->login($techEmail, $techPassword);
    echo "Technician login result:\n";
    echo "- Success: " . ($techResult['success'] ? 'Yes' : 'No') . "\n";
    echo "- Message: " . $techResult['message'] . "\n";
    if ($techResult['success']) {
        echo "- Redirect: " . $techResult['redirect'] . "\n";
    }
    
    echo "\n";
    
    // Test login for regular user
    echo "Testing login with regular user credentials...\n";
    $userResult = $auth->login($userEmail, $userPassword);
    echo "User login result:\n";
    echo "- Success: " . ($userResult['success'] ? 'Yes' : 'No') . "\n";
    echo "- Message: " . $userResult['message'] . "\n";
    if ($userResult['success']) {
        echo "- Redirect: " . $userResult['redirect'] . "\n";
    }
    
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . " (Line: " . $e->getLine() . ")\n";
}

echo "\nTest completed at: " . date('Y-m-d H:i:s') . "\n";
?> 