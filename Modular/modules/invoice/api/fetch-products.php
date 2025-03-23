<?php
session_start();
header('Content-Type: application/json');
require_once '../../../php/db.php'; // Ensure this connects to your customer database

global $conn;

// Fetch query parameters
$category = isset($_GET['category']) ? $_GET['category'] : 'products';
$searchTerm = isset($_GET['searchTerm']) ? '%' . trim($_GET['searchTerm']) . '%' : '%';
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 100;
$offset = ($page - 1) * $limit;

// Map category to the correct table and column names
$categoryMapping = [
    'products' => 'product',
    'parts' => 'product',
    'services' => 'product',
    'vehicles' => 'vehicles',
    'extras' => 'product'
];

// Ensure the category is valid
if (!array_key_exists($category, $categoryMapping)) {
    echo json_encode(['error' => 'Invalid category']);
    exit;
}

$tableName = $categoryMapping[$category];

// Try-catch block to safely query the database
try {
    $conn->beginTransaction();

    // Construct the SQL query based on category
    $sql = "";
    if ($category === 'vehicles') {
        $sql = "SELECT veh_id AS id, make || ' ' || model AS name, year, vin, status FROM vehicles 
                WHERE (make ILIKE :search OR model ILIKE :search) LIMIT :limit OFFSET :offset";
    } else {
        $sql = "SELECT 
            prod_id, 
            prod_name, 
            prod_descr, 
            prod_price,
            sku,
            product_type,
            barcode,
            brand,
            manufacturer,
            weight,
            dimensions,
            warranty_period,
            tax_rate,
            discount,
            image_url,
            status
        FROM product
        WHERE product_type = :category 
        AND (prod_name ILIKE :search OR prod_descr ILIKE :search) 
        LIMIT :limit OFFSET :offset";
    }
    
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':category', $category, PDO::PARAM_STR);
    $stmt->bindParam(':search', $searchTerm, PDO::PARAM_STR);
    $stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
    $stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
    $stmt->execute();

    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $conn->commit();

    // Return the results as JSON
    echo json_encode(['category' => $category, 'results' => $results]);
} catch (PDOException $e) {
    $conn->rollBack();
    
    // Check for duplicate key violation
    if ($e->getCode() == '23505' && strpos($e->getMessage(), 'product_barcode_key') !== false) {
        echo json_encode([
            "success" => false,
            "message" => "A product with this barcode already exists."
        ]);
    } else {
        echo json_encode([
            "success" => false,
            "message" => "Error: " . $e->getMessage()
        ]);
    }
    exit;
}
?>
