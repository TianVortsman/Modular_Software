<?php
require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';
session_start();
header('Content-Type: application/json');
try {
    if (!isset($_SESSION['account_number'])) throw new Exception('No account');
    $db = App\Core\Database\ClientDatabase::getInstance($_SESSION['account_number']);
    $conn = $db->connect();
    $action = $_GET['action'] ?? '';
    switch ($action) {
        case 'list':
            $stmt = $conn->query('SELECT * FROM inventory.supplier ORDER BY supplier_name');
            $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
            $formattedSuppliers = [];
            foreach ($suppliers as $supplier) {
                $formattedSuppliers[] = [
                    'supplier_id' => $supplier['supplier_id'],
                    'supplier_name' => $supplier['supplier_name'],
                ];
            }
            echo json_encode(['success' => true, 'data' => $formattedSuppliers]);
            break;
        default:
            echo json_encode(['success' => false, 'message' => 'Invalid action']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
} 