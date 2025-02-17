<?php
session_start();
include('../../../php/db.php'); // Database connection

// Get customerType from the query string (check if it's set and sanitize it)
$customerType = isset($_GET['customerType']) ? (int)$_GET['customerType'] : null;

if ($customerType === null) {
    die(json_encode(["error" => "Customer type is required"]));
}

// Get search term (if any)
$searchTerm = isset($_GET['searchTerm']) ? $_GET['searchTerm'] : '';

// Get pagination parameters
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Query to count total customers (with search filter)
if ($customerType == 1) {
    $searchSql = "
        SELECT COUNT(DISTINCT c.cust_id)
        FROM customers c
        LEFT JOIN customer_invoice ci ON c.cust_id = ci.po_id
        WHERE (c.cust_fname ILIKE :searchTerm 
           OR c.cust_email ILIKE :searchTerm 
           OR CAST(c.cust_id AS TEXT) ILIKE :searchTerm)
    ";
} else {
    $searchSql = "
    SELECT COUNT(DISTINCT comp.company_id)
    FROM company comp
    LEFT JOIN (
        SELECT DISTINCT ON (company_id)
            company_id,
            contact_email
        FROM company_contacts
        ORDER BY company_id, contact_id
    ) cc ON cc.company_id = comp.company_id
    LEFT JOIN customers c ON c.company_id = comp.company_id 
    LEFT JOIN customer_invoice ci ON c.cust_id = ci.po_id
    WHERE (comp.company_name ILIKE :searchTerm 
           OR cc.contact_email ILIKE :searchTerm 
           OR CAST(comp.company_id AS TEXT) ILIKE :searchTerm)
    ";
}

$stmt = $conn->prepare($searchSql);
$stmt->execute([':searchTerm' => "%$searchTerm%"]);
$totalCustomers = $stmt->fetchColumn();

// Query to fetch customer details with invoice summary
$sql = "
    SELECT 
        c.cust_id,
        c.cust_fname, 
        c.cust_email,
        c.cust_cell,
        MAX(ci.inv_date) AS last_invoice_date, 
        COALESCE(SUM(ci.total_amount), 0) AS total_invoice_amount,
        CASE 
            WHEN COUNT(ci.status) = 0 THEN 'No Invoices'
            WHEN SUM(CASE WHEN ci.status = 'Overdue' THEN 1 ELSE 0 END) > 0 THEN 'Overdue'
            WHEN SUM(CASE WHEN ci.status = 'In Arrears' THEN 1 ELSE 0 END) > 0 THEN 'In Arrears'
            ELSE 'Paid'
        END AS customer_status
    FROM customers c
    LEFT JOIN customer_invoice ci ON c.cust_id = ci.po_id
    WHERE (c.cust_fname ILIKE :searchTerm 
           OR c.cust_email ILIKE :searchTerm 
           OR CAST(c.cust_id AS TEXT) ILIKE :searchTerm)
    GROUP BY c.cust_id, c.cust_fname, c.cust_email, c.cust_cell
    ORDER BY c.cust_id ASC
    LIMIT :limit OFFSET :offset
";

// Placeholder for business customer query (cust_type = 2)
$sql_business = "
    SELECT 
        comp.company_id,
        comp.company_name, 
        comp.company_email,
        comp.company_tell,
        MAX(ci.inv_date) AS last_invoice_date, 
        COALESCE(SUM(CASE WHEN ci.status = 'Unpaid' THEN ci.total_amount ELSE 0 END), 0) AS total_outstanding_amount,
        COUNT(ci.cust_inv_id) AS total_invoice_count
    FROM company comp
    LEFT JOIN customers c ON c.company_id = comp.company_id 
    LEFT JOIN customer_invoice ci ON c.cust_id = ci.po_id
    WHERE comp.company_name ILIKE :searchTerm 
       OR comp.company_email ILIKE :searchTerm 
       OR CAST(comp.company_id AS TEXT) ILIKE :searchTerm
    GROUP BY comp.company_id, comp.company_name, comp.company_email, comp.company_tell
    ORDER BY comp.company_id ASC
    LIMIT :limit OFFSET :offset
";

// Decide which query to use based on the customer type
if ($customerType == 1) {
    $stmt = $conn->prepare($sql);
} elseif ($customerType == 2) {
    $stmt = $conn->prepare($sql_business);
} else {
    die(json_encode(["error" => "Invalid customer type"]));
}

$stmt->bindValue(':searchTerm', "%$searchTerm%", PDO::PARAM_STR);
$stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();

// Fetch all customer data
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Return the customer data and total count as JSON response
echo json_encode([
    'totalCustomers' => $totalCustomers,
    'customers' => $customers
]);
?>
