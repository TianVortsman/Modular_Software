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
                case 'list_by_status':
                    if ($method === 'GET') return $this->getProductsByStatus();
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
            $this->insertProductSupplier($productId);
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
            $this->updateProductSupplier($productId);
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
            $sql = "SELECT p.*, pt.product_type_name AS product_type_name, pc.category_name, psc.subcategory_name, pi.stock_quantity, pi.reorder_level, pi.lead_time, pi.product_weight, pi.dimensions, pi.brand, pi.manufacturer, pi.warranty_period, tr.rate AS tax_rate
                    FROM core.product p
                    LEFT JOIN core.product_types pt ON p.product_type_id = pt.product_type_id
                    LEFT JOIN core.product_categories pc ON p.category_id = pc.category_id
                    LEFT JOIN core.product_subcategories psc ON p.subcategory_id = psc.subcategory_id
                    LEFT JOIN inventory.product_inventory pi ON p.product_id = pi.product_id
                    LEFT JOIN core.tax_rates tr ON p.tax_rate_id = tr.tax_rate_id
                    ORDER BY p.product_name";
            $stmt = $this->db->query($sql);
            $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
            // Add image_url for each product
            $accountNumber = $_SESSION['account_number'] ?? 'ACC002';
            $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
            $supportedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            foreach ($products as &$product) {
                $type = strtolower($product['product_type_name'] ?? 'product');
                $imgPath = null;
                foreach ($supportedExts as $ext) {
                    $tryDocPath = "$docRoot/Uploads/$accountNumber/products/$type/{$product['product_id']}.$ext";
                    if (file_exists($tryDocPath)) {
                        $imgPath = "/Uploads/$accountNumber/products/$type/{$product['product_id']}.$ext";
                        break;
                    }
                }
                $product['image_url'] = $imgPath;
            }
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
            $sql = "SELECT p.*, pt.product_type_name AS product_type_name, pc.category_name, psc.subcategory_name, pi.stock_quantity, pi.reorder_level, pi.lead_time, pi.product_weight, pi.dimensions, pi.brand, pi.manufacturer, pi.warranty_period, tr.rate AS tax_rate, ps.supplier_id
                    FROM core.product p
                    LEFT JOIN core.product_types pt ON p.product_type_id = pt.product_type_id
                    LEFT JOIN core.product_categories pc ON p.category_id = pc.category_id
                    LEFT JOIN core.product_subcategories psc ON p.subcategory_id = psc.subcategory_id
                    LEFT JOIN inventory.product_inventory pi ON p.product_id = pi.product_id
                    LEFT JOIN core.tax_rates tr ON p.tax_rate_id = tr.tax_rate_id
                    LEFT JOIN inventory.product_supplier ps ON p.product_id = ps.product_id
                    WHERE p.product_id = :product_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['product_id' => $productId]);
            $product = $stmt->fetch(PDO::FETCH_ASSOC);
            if (!$product) {
                throw new Exception('Product not found');
            }
            // Add image_url logic
            $accountNumber = $_SESSION['account_number'] ?? 'ACC002';
            $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
            $type = strtolower($product['product_type_name'] ?? 'product');
            $supportedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            $imgPath = null;
            foreach ($supportedExts as $ext) {
                $tryDocPath = "$docRoot/Uploads/$accountNumber/products/$type/{$product['product_id']}.$ext";
                if (file_exists($tryDocPath)) {
                    $imgPath = "/Uploads/$accountNumber/products/$type/{$product['product_id']}.$ext";
                    break;
                }
            }
            $product['image_url'] = $imgPath;
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
        $stmt = $this->db->query('SELECT * FROM core.product_types ORDER BY product_type_name');
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [ 'success' => true, 'data' => $types ];
    }
    public function getProductCategories() {
        $typeId = $_GET['product_type_id'] ?? null;
        $sql = 'SELECT * FROM core.product_categories';
        $params = [];
        if ($typeId) {
            $sql .= ' WHERE product_type_id = :product_type_id';
            $params['product_type_id'] = $typeId;
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
            product_name, product_description, product_price, product_status,
            sku, barcode, product_type_id, category_id, subcategory_id, tax_rate_id, discount, notes
        ) VALUES (
            :product_name, :product_description, :product_price, :product_status,
            :sku, :barcode, :product_type_id, :category_id, :subcategory_id, :tax_rate_id, :discount, :notes
        ) RETURNING product_id";
        $stmt = $this->db->prepare($sql);
        $params = [
            'product_name' => $_POST['product_name'] ?? '',
            'product_description' => $_POST['product_description'] ?? null,
            'product_price' => !empty($_POST['product_price']) ? $_POST['product_price'] : 0,
            'product_status' => $_POST['product_status'] ?? 'active',
            'sku' => !empty($_POST['sku']) ? $_POST['sku'] : null,
            'barcode' => !empty($_POST['barcode']) ? $_POST['barcode'] : null,
            'product_type_id' => (isset($_POST['product_type_id']) && is_numeric($_POST['product_type_id']) ? $_POST['product_type_id'] : null),
            'category_id' => (isset($_POST['category_id']) && is_numeric($_POST['category_id']) ? $_POST['category_id'] : null),
            'subcategory_id' => (isset($_POST['subcategory_id']) && is_numeric($_POST['subcategory_id']) ? $_POST['subcategory_id'] : null),
            'tax_rate_id' => !empty($_POST['tax_rate_id']) ? $_POST['tax_rate_id'] : null,
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
        // Fetch old product_type_id and product_type_name
        $stmt = $this->db->prepare('SELECT p.product_type_id, pt.product_type_name FROM core.product p LEFT JOIN core.product_types pt ON p.product_type_id = pt.product_type_id WHERE p.product_id = :product_id');
        $stmt->execute(['product_id' => $productId]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC);
        $oldTypeId = $old['product_type_id'] ?? null;
        $oldTypeName = $old['product_type_name'] ?? null;

        // Get new product_type_id and product_type_name
        $newTypeId = isset($_POST['product_type_id']) && $_POST['product_type_id'] !== '' ? $_POST['product_type_id'] : null;
        $newTypeName = $oldTypeName;
        if ($newTypeId && $newTypeId != $oldTypeId) {
            // Lookup new product_type_name
            $stmt2 = $this->db->prepare('SELECT product_type_name FROM core.product_types WHERE product_type_id = :product_type_id');
            $stmt2->execute(['product_type_id' => $newTypeId]);
            $row = $stmt2->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['product_type_name']) {
                $newTypeName = $row['product_type_name'];
            }
        }

        // Update product as before
        $sql = "UPDATE core.product SET
            product_name = :product_name,
            product_description = :product_description,
            product_price = :product_price,
            product_status = :product_status,
            sku = :sku,
            barcode = :barcode,
            product_type_id = :product_type_id,
            category_id = :category_id,
            subcategory_id = :subcategory_id,
            tax_rate_id = :tax_rate_id,
            discount = :discount,
            notes = :notes,
            updated_at = CURRENT_TIMESTAMP
        WHERE product_id = :product_id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([
            'product_id' => $productId,
            'product_name' => $_POST['product_name'],
            'product_description' => $_POST['product_description'] ?? null,
            'product_price' => !empty($_POST['product_price']) ? $_POST['product_price'] : 0,
            'product_status' => $_POST['product_status'] ?? 'active',
            'sku' => isset($_POST['sku']) && $_POST['sku'] !== '' ? $_POST['sku'] : $this->getCurrentSku($productId),
            'barcode' => isset($_POST['barcode']) && $_POST['barcode'] !== '' ? $_POST['barcode'] : $this->getCurrentBarcode($productId),
            'product_type_id' => (isset($_POST['product_type_id']) && is_numeric($_POST['product_type_id']) ? $_POST['product_type_id'] : null),
            'category_id' => (isset($_POST['category_id']) && is_numeric($_POST['category_id']) ? $_POST['category_id'] : null),
            'subcategory_id' => (isset($_POST['subcategory_id']) && is_numeric($_POST['subcategory_id']) ? $_POST['subcategory_id'] : null),
            'tax_rate_id' => !empty($_POST['tax_rate_id']) ? $_POST['tax_rate_id'] : null,
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

    public function handleImageUploadAPI() {
        try {
            $productId = $_POST['product_id'] ?? null;
            error_log('[ImageUpload] Incoming POST: ' . print_r($_POST, true));
            // Support upload by barcode
            if (!$productId && !empty($_POST['barcode'])) {
                error_log('[ImageUpload] No product_id, looking up by barcode: ' . $_POST['barcode']);
                $stmt = $this->db->prepare('SELECT product_id FROM core.product WHERE barcode = :barcode');
                $stmt->execute(['barcode' => $_POST['barcode']]);
                $productId = $stmt->fetchColumn();
                if (!$productId) {
                    error_log('[ImageUpload] No product found for barcode: ' . $_POST['barcode']);
                    return [
                        'success' => false,
                        'message' => 'No product found for barcode',
                        'data' => null
                    ];
                }
                error_log('[ImageUpload] Found product_id: ' . $productId);
            }
            if (!$productId) {
                error_log('[ImageUpload] No product_id or barcode provided');
                return [
                    'success' => false,
                    'message' => 'No product_id or barcode provided',
                    'data' => null
                ];
            }
            $imageUrl = $this->handleImageUpload($productId);
            error_log('[ImageUpload] handleImageUpload returned: ' . print_r($imageUrl, true));
            return [
                'success' => true,
                'message' => 'Image uploaded successfully',
                'url' => $imageUrl,
                'path' => $imageUrl
            ];
        } catch (Exception $e) {
            error_log('[ImageUpload] Exception: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    private function handleImageUpload($productId = null) {
        try {
            error_log('[ImageUpload] handleImageUpload called for product_id: ' . print_r($productId, true));
            if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
                error_log('[ImageUpload] No image file or upload error. FILES: ' . print_r($_FILES, true));
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
            error_log('[ImageUpload] Upload path: ' . $uploadPath);
            if (!is_dir($uploadPath)) {
                if (!mkdir($uploadPath, 0777, true) && !is_dir($uploadPath)) {
                    error_log('[ImageUpload] Failed to create directory: ' . $uploadPath);
                    return null;
                }
                error_log('[ImageUpload] Created directory: ' . $uploadPath);
            }
            $srcExt = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $supportedExts = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            if (!in_array($srcExt, $supportedExts)) {
                error_log('[ImageUpload] Unsupported image type: ' . $srcExt);
                return null;
            }
            $filename = $productId ? $productId . '.' . $srcExt : uniqid() . '.' . $srcExt;
            $targetPath = $uploadPath . $filename;
            if (!move_uploaded_file($file['tmp_name'], $targetPath)) {
                error_log('[ImageUpload] Failed to move uploaded file.');
                return null;
            }
            error_log('[ImageUpload] File saved successfully as original: ' . $targetPath);
            return "Uploads/{$accountNumber}/products/{$category}/" . $filename;
        } catch (Exception $e) {
            error_log('[ImageUpload] Exception in handleImageUpload: ' . $e->getMessage());
            return null;
        }
    }

    private function insertProductInventory($productId) {
        try {
            error_log("Inserting product inventory for product ID: " . $productId);
            
            $sql = "INSERT INTO inventory.product_inventory (
                product_id, stock_quantity, reorder_level, lead_time,
                product_weight, dimensions, brand, manufacturer, warranty_period
            ) VALUES (
                :product_id, :stock_quantity, :reorder_level, :lead_time,
                :product_weight, :dimensions, :brand, :manufacturer, :warranty_period
            )";

            $stmt = $this->db->prepare($sql);
            
            $params = [
                'product_id' => $productId,
                'stock_quantity' => !empty($_POST['stock_quantity']) ? $_POST['stock_quantity'] : '0',
                'reorder_level' => !empty($_POST['reorder_level']) ? $_POST['reorder_level'] : 0,
                'lead_time' => !empty($_POST['lead_time']) ? $_POST['lead_time'] : 0,
                'product_weight' => !empty($_POST['product_weight']) ? $_POST['product_weight'] : 0,
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
            product_weight = :product_weight,
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
            'product_weight' => !empty($_POST['product_weight']) ? $_POST['product_weight'] : 0,
            'dimensions' => $_POST['dimensions'] ?? null,
            'brand' => $_POST['brand'] ?? null,
            'manufacturer' => $_POST['manufacturer'] ?? null,
            'warranty_period' => $_POST['warranty_period'] ?? null
        ]);
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
            // Delete product image file(s)
            $accountNumber = $_SESSION['account_number'] ?? 'ACC002';
            $types = ['product', 'part', 'service', 'extra', 'bundle'];
            $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
            foreach ($types as $type) {
                foreach ($extensions as $ext) {
                    $imgPath = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\') . "/Uploads/{$accountNumber}/products/{$type}/{$productId}.{$ext}";
                    if (file_exists($imgPath)) {
                        @unlink($imgPath);
                    }
                }
            }
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

    public function getProductsByStatus() {
        $status = $_GET['status'] ?? 'active';
        $sql = "SELECT p.*, pt.product_type_name AS product_type_name, pc.category_name, psc.subcategory_name, pi.stock_quantity, pi.reorder_level, pi.lead_time, pi.product_weight, pi.dimensions, pi.brand, pi.manufacturer, pi.warranty_period, tr.rate
                FROM core.product p
                LEFT JOIN core.product_types pt ON p.product_type_id = pt.product_type_id
                LEFT JOIN core.product_categories pc ON p.category_id = pc.category_id
                LEFT JOIN core.product_subcategories psc ON p.subcategory_id = psc.subcategory_id
                LEFT JOIN inventory.product_inventory pi ON p.product_id = pi.product_id
                LEFT JOIN core.tax_rates tr ON p.tax_rate_id = tr.tax_rate_id
                WHERE p.status = :product_status
                ORDER BY p.product_name";
        $stmt = $this->db->prepare($sql);
        $stmt->execute(['status' => $status]);
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data' => $products
        ];
    }

    private function insertProductSupplier($productId) {
        $supplier_id = $_POST['supplier_id'] ?? $_POST['supplier_id'] ?? null;
        if (!$supplier_id) return; // Skip if no supplier
        try {
            $sql = "INSERT INTO inventory.product_supplier (product_id, supplier_id) VALUES (:product_id, :supplier_id)";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['product_id' => $productId, 'supplier_id' => $supplier_id]);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    private function updateProductSupplier($productId) {
        $supplier_id = $_POST['supplier_id'] ?? $_POST['supplier_id'] ?? null;
        if (!$supplier_id) return; // Skip if no supplier
        try {
            $sql = "UPDATE inventory.product_supplier SET supplier_id = :supplier_id WHERE product_id = :product_id";
            $stmt = $this->db->prepare($sql);
            $stmt->execute(['product_id' => $productId, 'supplier_id' => $supplier_id]);
        } catch (PDOException $e) {
            throw $e;
        }
    }

    public function updateProductStatus() {
        try {
            $productId = $_POST['product_id'] ?? null;
            $status = $_POST['product_status'] ?? null;
            if (!$productId || !$status) {
                throw new Exception('Product ID and status are required');
            }
            $allowed = ['active', 'inactive', 'discontinued'];
            if (!in_array($status, $allowed)) {
                throw new Exception('Invalid status');
            }
            $stmt = $this->db->prepare('UPDATE core.product SET product_status = :status, updated_at = CURRENT_TIMESTAMP WHERE product_id = :product_id');
            $stmt->execute(['status' => $status, 'product_id' => $productId]);
            return [
                'success' => true,
                'message' => 'Product status updated',
                'data' => ['product_id' => $productId, 'product_status' => $status]
            ];
        } catch (Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'data' => null
            ];
        }
    }

    // Add: Get all active tax rates for dropdown
    public function getTaxRates() {
        $stmt = $this->db->query('SELECT tax_rate_id, tax_name, rate FROM core.tax_rates WHERE is_active = true ORDER BY rate');
        $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [ 'success' => true, 'data' => $rates ];
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