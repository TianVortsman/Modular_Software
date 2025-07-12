<?php
namespace App\modules\product\controllers;

use PDO;
use Exception;

// Get all active tax rates for dropdown
function getTaxRates() {
    global $conn;
    try {
        $stmt = $conn->query('SELECT tax_rate_id, tax_name, rate FROM core.tax_rates WHERE is_active = true ORDER BY rate');
        $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [ 'success' => true, 'data' => $rates ];
    } catch (Exception $e) {
        return [ 'success' => false, 'error' => $e->getMessage() ];
    }
}

function listProducts(array $options = []): array {
    global $conn;

    try {
        // Sanitize input
        $search       = $options['search']    ?? null;
        $typeId       = $options['type']      ?? null;
        $categoryId   = $options['category']  ?? null;
        $subcategoryId = $options['subcategory'] ?? null;

        $page         = max(1, (int)($options['page'] ?? 1));
        $limit        = max(1, (int)($options['limit'] ?? 20));
        $sortBy       = $options['sort_by']   ?? 'p.product_name';
        $sortDir      = strtolower($options['sort_dir'] ?? 'asc');

        // Allow only valid sort fields
        $allowedSortFields = [
            'p.product_name', 'p.product_price', 'p.product_status', 'p.created_at'
        ];
        if (!in_array($sortBy, $allowedSortFields)) {
            $sortBy = 'p.product_name';
        }

        $allowedSortDir = ['asc', 'desc'];
        if (!in_array($sortDir, $allowedSortDir)) {
            $sortDir = 'asc';
        }

        $offset = ($page - 1) * $limit;
        $params = [];

        // Build SQL
        $sql = "
            SELECT 
                p.*, 
                pt.product_type_name, 
                pc.category_name, 
                psc.subcategory_name, 
                pi.stock_quantity, 
                pi.reorder_level, 
                pi.lead_time, 
                pi.product_weight, 
                pi.dimensions, 
                pi.brand, 
                pi.manufacturer, 
                pi.warranty_period, 
                tr.rate AS tax_rate
            FROM core.product p
            LEFT JOIN core.product_types pt ON p.product_type_id = pt.product_type_id
            LEFT JOIN core.product_categories pc ON p.category_id = pc.category_id
            LEFT JOIN core.product_subcategories psc ON p.subcategory_id = psc.subcategory_id
            LEFT JOIN inventory.product_inventory pi ON p.product_id = pi.product_id
            LEFT JOIN core.tax_rates tr ON p.tax_rate_id = tr.tax_rate_id
            WHERE 1 = 1
        ";

        // Optional filters
        if (!empty($typeId)) {
            $sql .= " AND p.product_type_id = :type_id";
            $params[':type_id'] = $typeId;
        }

        if (!empty($categoryId)) {
            $sql .= " AND p.category_id = :category_id";
            $params[':category_id'] = $categoryId;
        }

        if (!empty($subcategoryId)) {
            $sql .= " AND p.subcategory_id = :subcategory_id";
            $params[':subcategory_id'] = $subcategoryId;
        }

        if (!empty($search)) {
            $sql .= " AND (
                p.product_name ILIKE :search OR
                p.product_description ILIKE :search OR
                pi.brand ILIKE :search OR
                pi.manufacturer ILIKE :search
            )";
            $params[':search'] = '%' . $search . '%';
        }

        $sql .= " ORDER BY $sortBy $sortDir LIMIT :limit OFFSET :offset";

        $stmt = $conn->prepare($sql);

        // Bind numeric pagination
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);

        // Bind dynamic filters
        foreach ($params as $key => $value) {
            $stmt->bindValue($key, $value);
        }

        $stmt->execute();
        $products = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Append image URLs
        $accountNumber = $_SESSION['account_number'] ?? 'ACC002';
        $docRoot = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
        $extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        foreach ($products as &$product) {
            $type = strtolower($product['product_type_name'] ?? 'product');
            $imagePath = null;

            foreach ($extensions as $ext) {
                $tryPath = "$docRoot/Uploads/$accountNumber/products/$type/{$product['product_id']}.$ext";
                if (file_exists($tryPath)) {
                    $imagePath = "/Uploads/$accountNumber/products/$type/{$product['product_id']}.$ext";
                    break;
                }
            }

            $product['image_url'] = $imagePath;
        }

        return [
            'success' => true,
            'message' => 'Products retrieved successfully',
            'data'    => $products
        ];

    } catch (PDOException $e) {
        error_log('[listProducts] DB Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to fetch products',
            'data'    => null
        ];
    } catch (Throwable $e) {
        error_log('[listProducts] General Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Unexpected error occurred',
            'data'    => null
        ];
    }
}


function updateProduct(): array {
    try {
        $productId = $_POST['product_id'] ?? null;
        if (!$productId) {
            return [
                'success' => false,
                'message' => 'Product ID is required',
                'data'    => null
            ];
        }

        if (!$this->db->inTransaction()) {
            $this->db->beginTransaction();
        }

        $this->updateCoreProduct($productId);
        $this->updateProductInventory($productId);
        $this->updateProductSupplier($productId);

        $this->db->commit();

        return [
            'success' => true,
            'message' => 'Product updated successfully',
            'data'    => ['product_id' => $productId]
        ];
    } catch (\Throwable $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }

        error_log('[updateProduct] Error: ' . $e->getMessage());

        return [
            'success' => false,
            'message' => 'Failed to update product',
            'data'    => null
        ];
    }
}


function addProduct(): array {
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
            'data'    => ['product_id' => $productId]
        ];
    } catch (\Throwable $e) {
        if ($this->db->inTransaction()) {
            $this->db->rollBack();
        }

        error_log('[addProduct] Error: ' . $e->getMessage());

        return [
            'success' => false,
            'message' => 'Failed to add product',
            'data'    => null
        ];
    }
}


function ge_product_details(int $productId): array {
    try {
        if (!$productId) {
            return [
                'success' => false,
                'message' => 'Product ID is required',
                'data'    => null
            ];
        }

        $sql = "
            SELECT 
                p.*, 
                pt.product_type_name, 
                pc.category_name, 
                psc.subcategory_name, 
                pi.stock_quantity, 
                pi.reorder_level, 
                pi.lead_time, 
                pi.product_weight, 
                pi.dimensions, 
                pi.brand, 
                pi.manufacturer, 
                pi.warranty_period, 
                tr.rate AS tax_rate, 
                ps.supplier_id
            FROM core.product p
            LEFT JOIN core.product_types pt ON p.product_type_id = pt.product_type_id
            LEFT JOIN core.product_categories pc ON p.category_id = pc.category_id
            LEFT JOIN core.product_subcategories psc ON p.subcategory_id = psc.subcategory_id
            LEFT JOIN inventory.product_inventory pi ON p.product_id = pi.product_id
            LEFT JOIN core.tax_rates tr ON p.tax_rate_id = tr.tax_rate_id
            LEFT JOIN inventory.product_supplier ps ON p.product_id = ps.product_id
            WHERE p.product_id = :product_id
        ";

        $stmt = $this->db->prepare($sql);
        $stmt->execute(['product_id' => $productId]);

        $product = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$product) {
            return [
                'success' => false,
                'message' => 'Product not found',
                'data'    => null
            ];
        }

        // Image URL lookup
        $accountNumber = $_SESSION['account_number'] ?? 'ACC002';
        $type          = strtolower($product['product_type_name'] ?? 'product');
        $docRoot       = rtrim($_SERVER['DOCUMENT_ROOT'], '/\\');
        $imagePath     = null;
        $extensions    = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

        foreach ($extensions as $ext) {
            $filePath = "$docRoot/Uploads/$accountNumber/products/$type/{$product['product_id']}.$ext";
            if (file_exists($filePath)) {
                $imagePath = "/Uploads/$accountNumber/products/$type/{$product['product_id']}.$ext";
                break;
            }
        }

        $product['image_url'] = $imagePath;

        return [
            'success' => true,
            'message' => 'Product retrieved successfully',
            'data'    => $product
        ];

    } catch (\PDOException $e) {
        error_log('[getProduct] DB Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Database error occurred',
            'data'    => null
        ];
    } catch (\Throwable $e) {
        error_log('[getProduct] Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Unexpected error occurred',
            'data'    => null
        ];
    }
}


function getProductTypes(): array {
    try {
        $stmt = $this->db->query('SELECT * FROM core.product_types ORDER BY product_type_name');
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'data'    => $types
        ];
    } catch (\PDOException $e) {
        error_log('[getProductTypes] DB Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to fetch product types',
            'data'    => null
        ];
    }
}

function getProductCategories(?int $productTypeId = null): array {
    try {
        $sql = 'SELECT * FROM core.product_categories';
        $params = [];

        if ($productTypeId !== null) {
            $sql .= ' WHERE product_type_id = :product_type_id';
            $params['product_type_id'] = $productTypeId;
        }

        $sql .= ' ORDER BY category_name';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'data'    => $categories
        ];
    } catch (\PDOException $e) {
        error_log('[getProductCategories] DB Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to fetch product categories',
            'data'    => null
        ];
    }
}

function getProductSubcategories(?int $categoryId = null): array {
    try {
        $sql = 'SELECT * FROM core.product_subcategories';
        $params = [];

        if ($categoryId !== null) {
            $sql .= ' WHERE category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        $sql .= ' ORDER BY subcategory_name';
        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'data'    => $subcategories
        ];
    } catch (\PDOException $e) {
        error_log('[getProductSubcategories] DB Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to fetch product subcategories',
            'data'    => null
        ];
    }
}



function insert_product(array $data): ?int {
    global $conn;

    $sql = "INSERT INTO core.product (
                product_name, product_description, product_price, product_status,
                sku, barcode, product_type_id, category_id, subcategory_id,
                tax_rate_id, discount, notes
            ) VALUES (
                :product_name, :product_description, :product_price, :product_status,
                :sku, :barcode, :product_type_id, :category_id, :subcategory_id,
                :tax_rate_id, :discount, :notes
            )
            RETURNING product_id";

    try {
        $stmt = $conn->prepare($sql);

        $params = [
            'product_name'       => $data['product_name'] ?? '',
            'product_description'=> $data['product_description'] ?? null,
            'product_price'      => $data['product_price'] !== '' ? $data['product_price'] : 0,
            'product_status'     => $data['product_status'] ?? 'active',
            'sku'                => !empty($data['sku']) ? $data['sku'] : null,
            'barcode'            => !empty($data['barcode']) ? $data['barcode'] : null,
            'product_type_id'    => is_numeric($data['product_type_id'] ?? null) ? $data['product_type_id'] : null,
            'category_id'        => is_numeric($data['category_id'] ?? null) ? $data['category_id'] : null,
            'subcategory_id'     => is_numeric($data['subcategory_id'] ?? null) ? $data['subcategory_id'] : null,
            'tax_rate_id'        => !empty($data['tax_rate_id']) ? $data['tax_rate_id'] : null,
            'discount'           => !empty($data['discount']) ? $data['discount'] : 0,
            'notes'              => $data['notes'] ?? null
        ];

        $stmt->execute($params);

        $productId = $stmt->fetchColumn();
        if (!$productId) {
            throw new Exception('Failed to insert product: No ID returned');
        }

        return (int)$productId;

    } catch (PDOException $e) {
        error_log('[insert_product] DB Error: ' . $e->getMessage());
        return null;
    } catch (Throwable $e) {
        error_log('[insert_product] General Error: ' . $e->getMessage());
        return null;
    }
}


 function update_product(int $productId): bool {
    try {
        // 1. Fetch old type
        $stmt = $this->db->prepare("
            SELECT p.product_type_id, pt.product_type_name 
            FROM core.product p 
            LEFT JOIN core.product_types pt ON p.product_type_id = pt.product_type_id 
            WHERE p.product_id = :product_id
        ");
        $stmt->execute(['product_id' => $productId]);
        $old = $stmt->fetch(PDO::FETCH_ASSOC);
        $oldTypeId = $old['product_type_id'] ?? null;
        $oldTypeName = $old['product_type_name'] ?? null;

        // 2. Determine new type name (if changed)
        $newTypeId = $_POST['product_type_id'] ?? null;
        $newTypeName = $oldTypeName;

        if ($newTypeId && $newTypeId != $oldTypeId) {
            $stmt = $this->db->prepare("
                SELECT product_type_name 
                FROM core.product_types 
                WHERE product_type_id = :product_type_id
            ");
            $stmt->execute(['product_type_id' => $newTypeId]);
            $row = $stmt->fetch(PDO::FETCH_ASSOC);
            if ($row && $row['product_type_name']) {
                $newTypeName = $row['product_type_name'];
            }
        }

        // 3. Update product
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
            'product_id'         => $productId,
            'product_name'       => $_POST['product_name'],
            'product_description'=> $_POST['product_description'] ?? null,
            'product_price'      => $_POST['product_price'] !== '' ? $_POST['product_price'] : 0,
            'product_status'     => $_POST['product_status'] ?? 'active',
            'sku'                => $_POST['sku'] !== '' ? $_POST['sku'] : $this->getCurrentSku($productId),
            'barcode'            => $_POST['barcode'] !== '' ? $_POST['barcode'] : $this->getCurrentBarcode($productId),
            'product_type_id'    => is_numeric($newTypeId) ? $newTypeId : null,
            'category_id'        => is_numeric($_POST['category_id'] ?? null) ? $_POST['category_id'] : null,
            'subcategory_id'     => is_numeric($_POST['subcategory_id'] ?? null) ? $_POST['subcategory_id'] : null,
            'tax_rate_id'        => $_POST['tax_rate_id'] ?? null,
            'discount'           => $_POST['discount'] !== '' ? $_POST['discount'] : 0,
            'notes'              => $_POST['notes'] ?? null
        ]);

        // 4. Move image if type folder changed
        if ($oldTypeName && $newTypeName && strtolower($oldTypeName) !== strtolower($newTypeName)) {
            $this->moveProductImageToNewTypeFolder($productId, $oldTypeName, $newTypeName);
        }

        return true;

    } catch (\PDOException $e) {
        error_log('[updateCoreProduct] DB Error: ' . $e->getMessage());
        return false;
    } catch (\Throwable $e) {
        error_log('[updateCoreProduct] General Error: ' . $e->getMessage());
        return false;
    }
}


function updateProductStatus(): array {
    global $conn;

    try {
        $productId = $_POST['product_id'] ?? null;
        $status    = $_POST['product_status'] ?? null;

        if (!$productId || !$status) {
            throw new Exception('Product ID and status are required');
        }

        $allowedStatuses = ['active', 'inactive', 'discontinued'];
        if (!in_array($status, $allowedStatuses, true)) {
            throw new Exception('Invalid status');
        }

        $sql = "UPDATE core.product 
                SET product_status = :status, updated_at = CURRENT_TIMESTAMP 
                WHERE product_id = :product_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':product_id', (int)$productId, PDO::PARAM_INT);
        $stmt->execute();

        return [
            'success' => true,
            'message' => 'Product status updated',
            'data'    => [
                'product_id'     => $productId,
                'product_status' => $status
            ]
        ];
    } catch (Exception $e) {
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'data'    => null
        ];
    }
}




// Optional helper to avoid repetition
 function error(string $message, $context = null): array {
    if ($context) {
        error_log("[ImageUpload] $message | Context: " . print_r($context, true));
    } else {
        error_log("[ImageUpload] $message");
    }

    return [
        'success' => false,
        'message' => $message,
        'data' => null
    ];
}


