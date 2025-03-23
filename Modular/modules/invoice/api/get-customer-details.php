<?php
session_start();
header('Content-Type: application/json');
include('../../../php/db.php');

// Get cust_id (required)
if (!isset($_GET['cust_id']) || empty($_GET['cust_id'])) {
    echo json_encode(["error" => "Customer ID is required"]);
    exit;
}

$custId = (int)$_GET['cust_id'];

$sql = "
    SELECT 
        -- Customer details
        c.cust_id,
        c.cust_init,
        c.cust_title,
        c.cust_fname,
        c.cust_lname,
        c.cust_tel,
        c.cust_cell,
        c.cust_email,

        -- Address details
        addr.addr_line_1,
        addr.addr_line_2,
        addr.suburb,
        addr.city,
        addr.province,
        addr.country,
        addr.postcode,

        -- Invoice details
        ci.inv_no,
        ci.inv_date,
        ci.total_amount,
        ci.status

    FROM customers c
    LEFT JOIN customer_address ca ON ca.cust_id = c.cust_id
    LEFT JOIN address addr ON addr.addr_id = ca.addr_id
    LEFT JOIN customer_invoice ci ON c.cust_id = ci.po_id
    WHERE c.cust_id = :custId
";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':custId', $custId, PDO::PARAM_INT);
    $stmt->execute();

    $customerData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($customerData ?: []);
} catch (PDOException $e) {
    echo json_encode(["error" => "Failed to get customer data: " . $e->getMessage()]);
}
?>
