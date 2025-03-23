<?php
// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fallback for getallheaders function in case it's not available
if (!function_exists('getallheaders')) {
    function getallheaders() {
        $headers = [];
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

// Display server and session information
header('Content-Type: text/plain');

echo "Technician Session Debug\n";
echo "=======================\n\n";

echo "PHP Version: " . phpversion() . "\n";
echo "Session Status: " . (session_status() === PHP_SESSION_ACTIVE ? "Active" : "Not Active") . "\n";
echo "Session ID: " . session_id() . "\n\n";

echo "Session Data:\n";
print_r($_SESSION);

echo "\nTechnician Login Check:\n";
echo "tech_logged_in: " . (isset($_SESSION['tech_logged_in']) ? ($_SESSION['tech_logged_in'] ? "TRUE" : "FALSE") : "NOT SET") . "\n";
echo "user_logged_in: " . (isset($_SESSION['user_logged_in']) ? ($_SESSION['user_logged_in'] ? "TRUE" : "FALSE") : "NOT SET") . "\n";
echo "user_type: " . (isset($_SESSION['user_type']) ? $_SESSION['user_type'] : "NOT SET") . "\n";

// Check if the technician is logged in according to old format
$isTechnicianLoggedInOld = isset($_SESSION['tech_logged_in']) && $_SESSION['tech_logged_in'] === true;
echo "\nTechnician Login Status (Old Format): " . ($isTechnicianLoggedInOld ? "LOGGED IN" : "NOT LOGGED IN") . "\n";

// Check if the technician is logged in according to new format
$isTechnicianLoggedInNew = isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'technician';
echo "Technician Login Status (New Format): " . ($isTechnicianLoggedInNew ? "LOGGED IN" : "NOT LOGGED IN") . "\n";

// Check for specific technician information
echo "\nTechnician ID (Old): " . (isset($_SESSION['tech_id']) ? $_SESSION['tech_id'] : "NOT SET") . "\n";
echo "Technician ID (New): " . (isset($_SESSION['user_id']) ? $_SESSION['user_id'] : "NOT SET") . "\n";

// Check if we have a token
echo "\nAccess Token: " . (isset($_SESSION['tech_access_token']) ? substr($_SESSION['tech_access_token'], 0, 12) . "..." : "NOT SET") . "\n";
echo "Token Expiration: " . (isset($_SESSION['tech_access_expiration']) ? date('Y-m-d H:i:s', $_SESSION['tech_access_expiration']) : "NOT SET") . "\n";

echo "\nHeaders Information:\n";
$headers = getallheaders();
foreach ($headers as $name => $value) {
    echo "$name: $value\n";
}

echo "\nServer Variables:\n";
echo "REMOTE_ADDR: " . $_SERVER['REMOTE_ADDR'] . "\n";
echo "HTTP_USER_AGENT: " . $_SERVER['HTTP_USER_AGENT'] . "\n";

echo "\nDebug completed at: " . date('Y-m-d H:i:s') . "\n";
?>