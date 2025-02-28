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

// Use cursor-based pagination instead of offset
$lastId = isset($_GET['lastId']) ? (int)$_GET['lastId'] : 0;

// Cache key for this query
$cacheKey = md5("invoice_customers_{$customerType}_{$searchTerm}_{$page}_{$limit}_{$lastId}");
$cacheFile = "../cache/{$cacheKey}.json";
$cacheTime = 300; // 5 minutes cache

// Check if we have a valid cache
if (file_exists($cacheFile) && (time() - filemtime($cacheFile) < $cacheTime)) {
    header('Content-Type: application/json');
    echo file_get_contents($cacheFile);
    exit;
}

// Prepare search conditions without wildcards for better index usage
$searchConditions = [];
$params = [];

if (!empty($searchTerm)) {
    // For exact matches on IDs (much faster than ILIKE with wildcards)
    if (is_numeric($searchTerm)) {
        if ($customerType == 1) {
            $searchConditions[] = "c.cust_id = :custId";
            $params[':custId'] = (int)$searchTerm;
        } else {
            $searchConditions[] = "comp.company_id = :compId";
            $params[':compId'] = (int)$searchTerm;
        }
    } else {
        // For text searches - use prefix matching instead of wildcards
        if ($customerType == 1) {
            $searchConditions[] = "c.cust_fname ILIKE :namePrefix";
            $searchConditions[] = "c.cust_email ILIKE :emailPrefix";
            $params[':namePrefix'] = "$searchTerm%"; // Only use trailing wildcard
            $params[':emailPrefix'] = "$searchTerm%";
        } else {
            $searchConditions[] = "comp.company_name ILIKE :compNamePrefix";
            $searchConditions[] = "cc.contact_email ILIKE :contactEmailPrefix";
            $params[':compNamePrefix'] = "$searchTerm%";
            $params[':contactEmailPrefix'] = "$searchTerm%";
        }
    }
}

// Query to count total customers (with search filter) - limit to 1000 for performance
if ($customerType == 1) {
    $searchSql = "
        SELECT COUNT(*) FROM (
            SELECT c.cust_id
            FROM customers c
            WHERE 1=1 " . 
            (!empty($searchConditions) ? " AND (" . implode(" OR ", $searchConditions) . ")" : "") . "
            LIMIT 1000
        ) AS filtered_customers
    ";
} else {
    $searchSql = "
        SELECT COUNT(*) FROM (
            SELECT comp.company_id
            FROM company comp
            LEFT JOIN (
                SELECT company_id, contact_email
                FROM company_contacts
                WHERE contact_id IN (
                    SELECT MIN(contact_id) FROM company_contacts GROUP BY company_id
                )
            ) cc ON cc.company_id = comp.company_id
            WHERE 1=1 " . 
            (!empty($searchConditions) ? " AND (" . implode(" OR ", $searchConditions) . ")" : "") . "
            LIMIT 1000
        ) AS filtered_companies
    ";
}

$stmt = $conn->prepare($searchSql);
$stmt->execute($params);
$totalCustomers = $stmt->fetchColumn();

// Determine if we need to cap the total for UI purposes
$cappedTotal = $totalCustomers;
if ($totalCustomers >= 1000) {
    $cappedTotal = "1000+";
}

// Query to fetch customer details with invoice summary
if ($customerType == 1) {
    // Private customers query
    $sql = "
    SELECT 
        c.cust_id,
        c.cust_fname, 
        c.cust_email,
        c.cust_cell,
        MAX(ci.inv_date) AS last_invoice_date, 
        COALESCE(SUM(ci.total_amount), 0) AS total_invoice_amount,
        COUNT(ci.cust_inv_id) AS invoice_count,
        CASE 
            WHEN COUNT(ci.status) = 0 THEN 'No Invoices'
            WHEN SUM(CASE WHEN ci.status = 'Overdue' THEN 1 ELSE 0 END) > 0 THEN 'Overdue'
            WHEN SUM(CASE WHEN ci.status = 'In Arrears' THEN 1 ELSE 0 END) > 0 THEN 'In Arrears'
            ELSE 'Paid'
        END AS customer_status
    FROM customers c
    LEFT JOIN customer_invoice ci ON c.cust_id = ci.po_id
    WHERE c.cust_id > :lastId " . 
    (!empty($searchConditions) ? " AND (" . implode(" OR ", $searchConditions) . ")" : "") . "
    GROUP BY c.cust_id, c.cust_fname, c.cust_email, c.cust_cell
    ORDER BY c.cust_id ASC
    LIMIT :limit
";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':lastId', $lastId, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    
    // Bind search parameters if they exist
    foreach ($params as $key => $value) {
        if (is_int($value)) {
            $stmt->bindParam($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindParam($key, $value, PDO::PARAM_STR);
        }
    }
    
} else {
    // Business customers query
    $sql = "
    SELECT 
        comp.company_id,
        comp.company_name,
        cc.contact_email,
        comp.company_tell,
        MAX(ci.inv_date) AS last_invoice_date,
        COALESCE(SUM(ci.total_amount), 0) AS total_invoice_amount,
        COUNT(DISTINCT ci.cust_inv_id) AS invoice_count,
        CASE 
            WHEN COUNT(ci.status) = 0 THEN 'No Invoices'
            WHEN SUM(CASE WHEN ci.status = 'Overdue' THEN 1 ELSE 0 END) > 0 THEN 'Overdue'
            WHEN SUM(CASE WHEN ci.status = 'In Arrears' THEN 1 ELSE 0 END) > 0 THEN 'In Arrears'
            ELSE 'Paid'
        END AS comp_status
    FROM company comp
    LEFT JOIN (
        SELECT company_id, contact_email
        FROM company_contacts
        WHERE contact_id IN (
            SELECT MIN(contact_id) FROM company_contacts GROUP BY company_id
        )
    ) cc ON cc.company_id = comp.company_id
    LEFT JOIN customers c ON c.company_id = comp.company_id
    LEFT JOIN customer_invoice ci ON c.cust_id = ci.po_id
    WHERE comp.company_id > :lastId " . 
    (!empty($searchConditions) ? " AND (" . implode(" OR ", $searchConditions) . ")" : "") . "
    GROUP BY comp.company_id, comp.company_name, cc.contact_email, comp.company_tell
    ORDER BY comp.company_id ASC
    LIMIT :limit
";
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':lastId', $lastId, PDO::PARAM_INT);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    
    // Bind search parameters if they exist
    foreach ($params as $key => $value) {
        if (is_int($value)) {
            $stmt->bindParam($key, $value, PDO::PARAM_INT);
        } else {
            $stmt->bindParam($key, $value, PDO::PARAM_STR);
        }
    }
}

$stmt->execute();
$customers = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Get the last ID for cursor-based pagination
$lastId = !empty($customers) ? end($customers)[$customerType == 1 ? 'cust_id' : 'company_id'] : 0;

// Prepare response
$response = [
    'customers' => $customers,
    'total' => $cappedTotal,
    'lastId' => $lastId,
    'hasMore' => count($customers) >= $limit
];

// Cache the result
if (!is_dir("../cache")) {
    mkdir("../cache", 0755, true);
}
file_put_contents($cacheFile, json_encode($response));

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
?>