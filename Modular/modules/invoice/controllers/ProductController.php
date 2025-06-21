<?php
namespace App\modules\invoice\controllers;

use PDO;
use PDOException;
use Exception;

class ProductController {
    private $db;
    private $uploadDir = '/var/www/html/Uploads/';

    public function __construct($db) {
        if (!$db instanceof PDO) {
            throw new Exception('Invalid database connection');
        }
        $this->db = $db;
    }

    public function handleRequest() {
        $method = $_SERVER['REQUEST_METHOD'];
        $action = $_GET['action'] ?? '';

        try {
            switch ($action) {
                case 'add':
                    if ($method === 'POST') return $this->addProduct();
                    break;
                case 'edit':
                    if ($method === 'PUT') return $this->updateProduct();
                    break;
                case 'get':
                    if ($method === 'GET') return $this->getProduct();
                    break;
                case 'list':
                    if ($method === 'GET') return $this->getAllProducts();
                    break;
                case 'list_types':
                    if ($method === 'GET') return $this->getProductTypes();
                    break;
                case 'list_categories':
                    if ($method === 'GET') return $this->getProductCategories();
                    break;
                case 'list_subcategories':
                    if ($method === 'GET') return $this->getProductSubcategories();
                    break;
                case 'upload_image':
                    if ($method === 'POST') return $this->handleImageUploadAPI();
                    break;
                case 'delete':
                    if ($method === 'DELETE') return $this->deleteProduct();
                    break;
            }
            throw new Exception('Invalid request method or action');
        } catch (Exception $e) {
            return $this->sendResponse(false, $e->getMessage());
        }
    }

    public function addProduct() {
        try {
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }
            $productId = $this->insertCoreProduct();
            $this->insertProductInventory($productId);
            $this->db->commit();
            return [
                'success' => true,
                'message' => 'Product added successfully',
                'data' => ['product_id' => $productId]
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function updateProduct() {
        try {
            if (!$this->db->inTransaction()) {
                $this->db->beginTransaction();
            }
            $productId = $_POST['product_id'] ?? null;
            if (!$productId) {
                throw new Exception('Product ID is required');
            }
            $this->updateCoreProduct($productId);
            $this->updateProductInventory($productId);
            $this->db->commit();
            return [
                'success' => true,
                'message' => 'Product updated successfully',
                'data' => ['product_id' => $productId]
            ];
        } catch (Exception $e) {
            if ($this->db->inTransaction()) {
                $this->db->rollBack();
            }
            throw $e;
        }
    }

    public function getAllProducts() {
        try {
            $sql = "SELECT p.*, pt.type_name, pc.category_name, psc.subcategory_name, pi.stock_quantity, pi.reorder_level, pi.lead_time, pi.weight, pi.dimensions, pi.brand, pi.manufacturer, pi.warranty_period
                    FROM core.product p
                    LEFT JOIN core.product_types pt ON p.type_id = pt.type_id
                    LEFT JOIN core.product_categories pc ON p.category_id = pc.category_id
                    LEFT JOIN core.product_subcategories psc ON p.subcategory_id = psc.subcategory_id
                    LEFT JOIN inventory.product_inventory pi ON p.product_id = pi.product_id
                    ORDER BY p.product_name";
            $stmt = $this->db->query($sql);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            return [
                'success' => true,
                'message' => 'Products retrieved successfully',
                'data' => $products
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getProduct() {
        try {
            $productId = $_GET['product_id'] ?? null;
            if (!$productId) {
                throw new Exception('Product ID is required');
            }
            $sql = "SELECT p.*, pt.type_name, pc.category_name, psc.subcategory_name, pi.stock_quantity, pi.reorder_level, pi.lead_time, pi.weight, pi.dimensions, pi.brand, pi.manufacturer, pi.warranty_period
                    FROM core.product p
                    LEFT JOIN core.product_types pt ON p.type_id = pt.type_id
                    LEFT JOIN core.product_categories pc ON p.category_id = pc.category_id
                    LEFT JOIN core.product_subcategories psc ON p.subcategory_id = psc.subcategory_id
                    LEFT JOIN inventory.product_inventory pi ON p.product_id = pi.product_id
                    WHERE p.product_id = :product_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['product_id' => $productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                throw new Exception('Product not found');
            }
            return [
                'success' => true,
                'message' => 'Product retrieved successfully',
                'data' => $product
            ];
        } catch (Exception $e) {
            throw $e;
        }
    }

    public function getProductTypes() {
        $stmt = $this->db->query('SELECT * FROM core.product_types ORDER BY type_name');
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [ 'success' => true, 'data' => $types ];
    }
    public function getProductCategories() {
        $typeId = $_GET['type_id'] ?? null;
        $sql = 'SELECT * FROM core.product_categories';
        $params = [];
        if ($typeId) {
            $sql .= ' WHERE type_id = :type_id';
            $params['type_id'] = $typeId;
        }
        $sql .= ' ORDER BY category_name';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [ 'success' => true, 'data' => $categories ];
    }
    public function getProductSubcategories() {
        $categoryId = $_GET['category_id'] ?? null;
        $sql = 'SELECT * FROM core.product_subcategories';
        $params = [];
        if ($categoryId) {
            $sql .= ' WHERE category_id = :category_id';
            $params['category_id'] = $categoryId;
        }
        $sql .= ' ORDER BY subcategory_name';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);
        $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [ 'success' => true, 'data' => $subcategories ];
    }

    private function insertCoreProduct() {
        $sql = "INSERT INTO core.product (
            product_name, product_description, product_price, status,
            sku, barcode, type_id, category_id, subcategory_id, tax_rate, discount, notes
        ) VALUES (
            :name, :description, :price, :status,
            :sku, :barcode, :type_id, :category_id, :subcategory_id, :tax_rate, :discount, :notes
        ) RETURNING product_id";
        $stmt = $this->db->prepare($sql);
        $params = [
            'name' => $_POST['prod_name'] ?? '',
            'description' => $_POST['prod_descr'] ?? null,
            'price' => !empty($_POST['prod_price']) ? $_POST['prod_price'] : 0,
            'status' => $_POST['status'] ?? 'active',
            'sku' => !empty($_POST['sku']) ? $_POST['sku'] : null,
            'barcode' => !empty($_POST['barcode']) ? $_POST['barcode'] : null,
            'type_id' => $_POST['type_id'] ?? null,
            'category_id' => $_POST['category_id'] ?? null,
            'subcategory_id' => $_POST['subcategory_id'] ?? null,
            'tax_rate' => !empty($_POST['tax_rate']) ? $_POST['tax_rate'] : 0,
            'discount' => !empty($_POST['discount']) ? $_POST['discount'] : 0,
            'notes' => $_POST['notes'] ?? null
        ];
        $stmt->execute($params);
        $productId = $stmt->fetchColumn();
        if (!$productId) {
            throw new Exception('Failed to insert product: No ID returned');
        }
        return $productId;
    }

    private function updateCoreProduct($productId) {
        // Fetch old type_id and type_name
        $stmt = $this->db->prepare('SELECT p.type_id, pt.type_name FROM core.product p LEFT JOIN core.product_types pt ON p.type_id = pt.type_id WHERE p.product_id = :product_id');
        $stmt->execute(['product_id' => $productId]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC);
        $oldTypeId = $old['type_id'] ?? null;
        $oldTypeName = $old['type_name'] ?? null;

        // Get new type_id and type_name
        $newTypeId = isset($_POST['type_id']) && $_POST['type_id'] !== '' ? $_POST['type_id'] : null;
        $newTypeName = $oldTypeName;
        if ($newTypeId && $newTypeId != $oldTypeId) {
            // Lookup new type_name
            $stmt2 = $this->db->prepare('SELECT type_name FROM core.product_types WHERE type_id = :type_id');
            $stmt2->execute(['type_id' => $newTypeId]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['type_name']) {
                $newTypeName = $row['type_name'];
            }
        }

        // Update product as before
        $sql = "UPDATE core.product SET
            product_name = :name,
            product_description = :description,
            product_price = :price,
            status = :status,
            sku = :sku,
            barcode = :barcode,
            type_id = :type_id,
            category_id = :category_id,
            subcategory_id = :subcategory_id,
            tax_rate = :tax_rate,
            discount = :discount,
            notes = :notes,
            updated_at = CURRENT_TIMESTAMP
        WHERE product_id = :product_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'product_id' => $productId,
            'name' => $_POST['prod_name'],
            'description' => $_POST['prod_descr'] ?? null,
            'price' => !empty($_POST['prod_price']) ? $_POST['prod_price'] : 0,
            'status' => $_POST['status'] ?? 'active',
            'sku' => isset($_POST['sku']) && $_POST['sku'] !== '' ? $_POST['sku'] : $this->getCurrentSku($productId),
            'barcode' => isset($_POST['barcode']) && $_POST['barcode'] !== '' ? $_POST['barcode'] : $this->getCurrentBarcode($productId),
            'type_id' => $newTypeId,
            'category_id' => (isset($_POST['category_id']) && $_POST['category_id'] !== '' ? $_POST['category_id'] : null),
            'subcategory_id' => (isset($_POST['subcategory_id']) && $_POST['subcategory_id'] !== '' ? $_POST['subcategory_id'] : null),
            'tax_rate' => !empty($_POST['tax_rate']) ? $_POST['tax_rate'] : 0,
            'discount' => !empty($_POST['discount']) ? $_POST['discount'] : 0,
            'notes' => $_POST['notes'] ?? null
        ]);

        // Move image if type changed
        if ($oldTypeName && $newTypeName && strtolower($oldTypeName) !== strtolower($newTypeName)) {
            $accountNumber = $_SESSION['account_number'] ?? 'ACC002';
            $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
            $oldFolder = "$docRoot/Uploads/$accountNumber/products/" . strtolower($oldTypeName);
            $newFolder = "$docRoot/Uploads/$accountNumber/products/" . strtolower($newTypeName);
            $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $found = false;
            foreach ($extensions as $ext) {
                $oldPath = "$oldFolder/$productId.$ext";
                if (file_exists($oldPath)) {
                    if (!is_dir($newFolder)) mkdir($newFolder, 0777, true);
                    $newPath = "$newFolder/$productId.$ext";
                    rename($oldPath, $newPath);
                    $found = true;
                    break;
                }
            }
            // Optionally: remove all other extensions in old folder
        }
    }

    private function handleImageUpload($productId = null) {
        try {
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                return null;
            }
            $file = $_FILES['image'];
            $accountNumber = $_SESSION['account_number'] ?? 'ACC002';
            $category = $_POST['category'] ?? 'product';
            $category = strtolower($category);
            if (!$category) {
                $category = 'product';
            }
            $uploadPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . "/Uploads/{$accountNumber}/products/{$category}/";
            if (!is_dir($uploadPath)) {
                mkdir($uploadPath, 0777, true);
            }
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $filename = $productId ? $productId . '.' . $extension : uniqid() . '.' . $extension;
            $targetPath = $uploadPath . $filename;
            if (move_uploaded_file($file['tmp_name'], $targetPath)) {
                return "Uploads/{$accountNumber}/products/{$category}/" . $filename;
            }
            return null;
        } catch (Exception $e) {
            error_log("Error in handleImageUpload: " . $e->getMessage());
            return null;
        }
    }

    private function insertProductInventory($productId) {
        try {
            error_log("Inserting product inventory for product ID: " . $productId);
            
            $sql = "INSERT INTO inventory.product_inventory (
                product_id, stock_quantity, reorder_level, lead_time,
                weight, dimensions, brand, manufacturer, warranty_period
            ) VALUES (
                :product_id, :stock_quantity, :reorder_level, :lead_time,
                :weight, :dimensions, :brand, :manufacturer, :warranty_period
            )";

            $stmt = $this->db->prepare($sql);
            
            $params = [
                'product_id' => $productId,
                'stock_quantity' => !empty($_POST['stock_quantity']) ? $_POST['stock_quantity'] : '0',
                'reorder_level' => !empty($_POST['reorder_level']) ? $_POST['reorder_level'] : 0,
                'lead_time' => !empty($_POST['lead_time']) ? $_POST['lead_time'] : 0,
                'weight' => !empty($_POST['weight']) ? $_POST['weight'] : 0,
                'dimensions' => $_POST['dimensions'] ?? null,
                'brand' => $_POST['brand'] ?? null,
                'manufacturer' => $_POST['manufacturer'] ?? null,
                'warranty_period' => $_POST['warranty_period'] ?? null
            ];

            error_log("Executing SQL with params: " . print_r($params, true));
            
            $stmt->execute($params);
            error_log("Successfully inserted product inventory");
        } catch (PDOException $e) {
            error_log("Database error in insertProductInventory: " . $e->getMessage());
            error_log("SQL State: " . $e->getCode());
            error_log("Error Info: " . print_r($e->errorInfo, true));
            throw $e;
        }
    }

    private function getCurrentSku($productId) {
        $stmt = $this->db->prepare('SELECT sku FROM core.product WHERE product_id = :product_id');
        $stmt->execute(['product_id' => $productId]);
        return $stmt->fetchColumn();
    }

    private function getCurrentBarcode($productId) {
        $stmt = $this->db->prepare('SELECT barcode FROM core.product WHERE product_id = :product_id');
        $stmt->execute(['product_id' => $productId]);
        return $stmt->fetchColumn();
    }

    private function updateProductInventory($productId) {
        $sql = "UPDATE inventory.product_inventory SET
            stock_quantity = :stock_quantity,
            reorder_level = :reorder_level,
            lead_time = :lead_time,
            weight = :weight,
            dimensions = :dimensions,
            brand = :brand,
            manufacturer = :manufacturer,
            warranty_period = :warranty_period,
            updated_at = CURRENT_TIMESTAMP
        WHERE product_id = :product_id";

        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'product_id' => $productId,
            'stock_quantity' => !empty($_POST['stock_quantity']) ? $_POST['stock_quantity'] : '0',
            'reorder_level' => !empty($_POST['reorder_level']) ? $_POST['reorder_level'] : 0,
            'lead_time' => !empty($_POST['lead_time']) ? $_POST['lead_time'] : 0,
            'weight' => !empty($_POST['weight']) ? $_POST['weight'] : 0,
            'dimensions' => $_POST['dimensions'] ?? null,
            'brand' => $_POST['brand'] ?? null,
            'manufacturer' => $_POST['manufacturer'] ?? null,
            'warranty_period' => $_POST['warranty_period'] ?? null
        ]);
    }

    public function handleImageUploadAPI() {
        try {
            $productId = $_POST['product_id'] ?? null;
            $imageUrl = $this->handleImageUpload($productId);
            return [
                'success' => true,
                'message' => 'Image uploaded successfully',
                'url' => $imageUrl,
                'path' => $imageUrl
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    public function deleteProduct() {
        try {
            $productId = $_POST['product_id'] ?? null;
            if (!$productId) {
                throw new Exception('Product ID is required');
            }
            $this->db->beginTransaction();
            // Delete from inventory.product_inventory first
            $this->db->exec("DELETE FROM inventory.product_inventory WHERE product_id = $productId");
            // Delete from core.product
            $this->db->exec("DELETE FROM core.product WHERE product_id = $productId");
            $this->db->commit();
            return [
                'success' => true,
                'message' => 'Product deleted successfully',
                'data' => null
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    private function sendResponse($success, $message, $data = null) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message,
            'data' => $data
        ]);
        exit;
    }
} 