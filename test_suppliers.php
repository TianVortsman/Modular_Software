<?php
session_start();
$_SESSION['account_number'] = 'ACC002';
$_SESSION['user_name'] = 'Test User';

// Now call the API
$url = 'http://localhost/modules/invoice/api/setup-api.php?action=getSuppliers';
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_COOKIE, session_name() . '=' . session_id());
$response = curl_exec($ch);
curl_close($ch);

echo "Response: " . $response;
?> 