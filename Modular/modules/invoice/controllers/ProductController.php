<?php
namespace App\modules\invoice\controllers;

use PDO;
use Exception;
use PDOException;
use Throwable;

require_once __DIR__ . '/../../../src/Helpers/helpers.php';

// Get all active tax rates for dropdown
function getTaxRates() {
    global $conn;
    try {
        $stmt = $conn->query('SELECT tax_rate_id, tax_name, rate FROM core.tax_rates WHERE is_active = true ORDER BY rate');
        $rates = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [ 'success' => true, 'message' => 'Tax rates loaded successfully', 'data' => $rates ];
    } catch (Exception $e) {
        $msg = 'Failed to load tax rates.';
        error_log('[getTaxRates] ' . $e->getMessage());
        log_user_action($_SESSION['user_id'] ?? null, 'getTaxRates', null, $e->getMessage());
        return [ 'success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'TAX_RATE_ERROR' ];
    }
}

function list_products(array $options = []): array {
    global $conn;

    try {
        // Sanitize input
        $search       = $options['search']    ?? null;
        $typeId       = $options['type']      ?? null;
        $categoryId   = $options['category']  ?? null;
        $subcategoryId = $options['subcategory'] ?? null;
        $supplierId   = $options['supplier_id'] ?? ($options['supplier'] ?? null);

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
                pi.product_stock_quantity, 
                pi.product_reorder_level, 
                pi.product_lead_time, 
                pi.product_weight, 
                pi.product_dimensions, 
                pi.product_brand, 
                pi.product_manufacturer, 
                pi.warranty_period, 
                pi.product_material, 
                tr.rate AS tax_rate, 
                ps.product_supplier_id, 
                ps.supplier_id
            FROM core.products p
            LEFT JOIN core.product_types pt ON p.product_type_id = pt.product_type_id
            LEFT JOIN core.product_categories pc ON p.category_id = pc.category_id
            LEFT JOIN core.product_subcategories psc ON p.subcategory_id = psc.subcategory_id
            LEFT JOIN inventory.product_inventory pi ON p.product_id = pi.product_id
            LEFT JOIN core.tax_rates tr ON p.tax_rate_id = tr.tax_rate_id
            LEFT JOIN inventory.product_supplier ps ON p.product_id = ps.product_id
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

        // Filter by supplier_id if provided
        if (!empty($supplierId)) {
            $sql .= " AND ps.supplier_id = :supplier_id";
            $params[':supplier_id'] = $supplierId;
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
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        $msg = get_friendly_error($e->getMessage());
        error_log('[listProducts] DB Error: ' . $e->getMessage());
        log_user_action($_SESSION['user_id'] ?? null, 'listProducts', null, $e->getMessage());
        return [
            'success' => false,
            'message' => $msg,
            'data'    => null,
            'error_code' => 'PRODUCT_LIST_DB_ERROR'
        ];
    } catch (Throwable $e) {
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        $msg = get_friendly_error($e->getMessage());
        error_log('[listProducts] General Error: ' . $e->getMessage());
        log_user_action($_SESSION['user_id'] ?? null, 'listProducts', null, $e->getMessage());
        return [
            'success' => false,
            'message' => $msg,
            'data'    => null,
            'error_code' => 'PRODUCT_LIST_ERROR'
        ];
    }
}

/**
 * Validates product data for add/update.
 * Only product_name, product_price, and product_status are required for add.
 * Returns [true, null] if valid, or [false, error_message] if not.
 */
function validate_product_data(array $data, $is_update = false): array {
    $required = ['product_name', 'product_price', 'product_status'];
    foreach ($required as $field) {
        if (empty($data[$field]) && !$is_update) {
            $msg = "Missing required field: $field";
            error_log('[validate_product_data] ' . $msg);
            log_user_action($_SESSION['user_id'] ?? null, 'validate_product_data', null, $msg);
            return [false, $msg];
        }
    }
    if (isset($data['product_price']) && !is_numeric($data['product_price'])) {
        $msg = 'Product price must be a number';
        error_log('[validate_product_data] ' . $msg);
        log_user_action($_SESSION['user_id'] ?? null, 'validate_product_data', null, $msg);
        return [false, $msg];
    }
    // Optionally: add more validation rules here
    return [true, null];
}

function update_product(array $data, int $user_id): array {
    global $conn;
    // Validate input (for update, allow missing fields)
    list($valid, $error) = validate_product_data($data, true);
    if (!$valid) {
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        $msg = get_friendly_error($error);
        error_log('[update_product] ' . $msg);
        log_user_action($user_id, 'update_product', $data['product_id'] ?? null, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data'    => null,
            'error_code' => 'PRODUCT_UPDATE_VALIDATION'
        ];
    }
    // Permission check
    if (!check_user_permission($user_id, 'update_document')) {
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        $msg = get_friendly_error("Permission denied for user $user_id to update product");
        error_log('[update_product] ' . $msg);
        log_user_action($user_id, 'update_product', $data['product_id'] ?? null, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data'    => null,
            'error_code' => 'PERMISSION_DENIED'
        ];
    }
    try {
        $productId = $data['product_id'] ?? null;
        if (!$productId) {
            $msg = 'Product ID is required';
            error_log('[update_product] ' . $msg);
            log_user_action($user_id, 'update_product', null, $msg);
            return [
                'success' => false,
                'message' => $msg,
                'data'    => null,
                'error_code' => 'PRODUCT_ID_REQUIRED'
            ];
        }
        if (!$conn->inTransaction()) {
            $conn->beginTransaction();
        }
        // For now, just update the core product table
        $sql = "UPDATE core.products SET
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
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'product_id'         => $productId,
            'product_name'       => $data['product_name'] ?? '',
            'product_description'=> $data['product_description'] ?? null,
            'product_price'      => $data['product_price'] !== '' ? $data['product_price'] : 0,
            'product_status'     => $data['product_status'] ?? 'active',
            'sku'                => $data['sku'] ?? null,
            'barcode'            => $data['barcode'] ?? null,
            'product_type_id'    => is_numeric($data['product_type_id'] ?? null) ? $data['product_type_id'] : null,
            'category_id'        => is_numeric($data['category_id'] ?? null) ? $data['category_id'] : null,
            'subcategory_id'     => is_numeric($data['subcategory_id'] ?? null) ? $data['subcategory_id'] : null,
            'tax_rate_id'        => $data['tax_rate_id'] ?? null,
            'discount'           => $data['discount'] !== '' ? $data['discount'] : 0,
            'notes'              => $data['notes'] ?? null
        ]);
        // Update inventory if any inventory field is present
        $inventoryFields = [
            'product_stock_quantity', 'product_reorder_level', 'product_lead_time',
            'product_weight', 'product_dimensions', 'product_brand',
            'product_manufacturer', 'warranty_period', 'product_material'
        ];
        $hasInventoryData = false;
        foreach ($inventoryFields as $field) {
            if (isset($data[$field])) {
                $hasInventoryData = true;
                break;
            }
        }
        if ($hasInventoryData) {
            $updateInventoryData = [
                'product_stock_quantity' => $data['product_stock_quantity'] ?? null,
                'product_reorder_level' => $data['product_reorder_level'] ?? 0,
                'product_lead_time' => $data['product_lead_time'] ?? null,
                'product_weight' => $data['product_weight'] ?? null,
                'product_dimensions' => $data['product_dimensions'] ?? null,
                'product_brand' => $data['product_brand'] ?? null,
                'product_manufacturer' => $data['product_manufacturer'] ?? null,
                'warranty_period' => $data['warranty_period'] ?? null,
                'product_material' => $data['product_material'] ?? null,
            ];
            if (!update_product_inventory($productId, $updateInventoryData)) {
                $conn->rollBack();
                $msg = 'Failed to update product inventory';
                error_log('[update_product] ' . $msg);
                log_user_action($user_id, 'update_product', $productId, $msg);
                return [
                    'success' => false,
                    'message' => $msg,
                    'data'    => null,
                    'error_code' => 'PRODUCT_INVENTORY_UPDATE_FAILED'
                ];
            }
        }
        $conn->commit();
        // Logging and notification
        log_user_action($user_id, 'update_product', $productId, json_encode($data));
        send_notification($user_id, "Product #$productId updated successfully.");
        return [
            'success' => true,
            'message' => 'Product updated successfully',
            'data'    => ['product_id' => $productId]
        ];
    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        $msg = get_friendly_error($e->getMessage());
        error_log('[update_product] ' . $msg);
        log_user_action($user_id, 'update_product', $data['product_id'] ?? null, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data'    => null,
            'error_code' => 'PRODUCT_UPDATE_ERROR'
        ];
    }
}

function add_product(array $data, int $user_id): array {
    global $conn;
    // Validate input
    list($valid, $error) = validate_product_data($data, false);
    if (!$valid) {
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        $msg = get_friendly_error($error);
        error_log('[add_product] ' . $msg);
        log_user_action($user_id, 'add_product', null, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data'    => null,
            'error_code' => 'PRODUCT_ADD_VALIDATION'
        ];
    }
    // Permission check
    if (!check_user_permission($user_id, 'create_document')) {
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        $msg = get_friendly_error("Permission denied for user $user_id to add product");
        error_log('[add_product] ' . $msg);
        log_user_action($user_id, 'add_product', null, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data'    => null,
            'error_code' => 'PERMISSION_DENIED'
        ];
    }
    try {
        if (!$conn->inTransaction()) {
            $conn->beginTransaction();
        }
        // Insert core product
        $productId = insert_product($data);
        if (!$productId) {
            $msg = 'Failed to insert product';
            error_log('[add_product] ' . $msg);
            log_user_action($user_id, 'add_product', null, $msg);
            throw new Exception($msg);
        }
        // Insert inventory
        if (!insert_product_inventory($productId, $data)) {
            $conn->rollBack();
            $msg = 'Failed to insert product inventory';
            error_log('[add_product] ' . $msg);
            log_user_action($user_id, 'add_product', $productId, $msg);
            throw new Exception($msg);
        }
        $conn->commit();
        // Logging and notification
        log_user_action($user_id, 'add_product', $productId, json_encode($data));
        send_notification($user_id, "Product #$productId added successfully.");
        return [
            'success' => true,
            'message' => 'Product added successfully',
            'data'    => ['product_id' => $productId]
        ];
    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        $msg = get_friendly_error($e->getMessage());
        error_log('[add_product] ' . $msg);
        log_user_action($user_id, 'add_product', null, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data'    => null,
            'error_code' => 'PRODUCT_ADD_ERROR'
        ];
    }
}


function get_product_details(int $productId): array {
    global $conn;
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
                pi.product_stock_quantity, 
                pi.product_reorder_level, 
                pi.product_lead_time, 
                pi.product_weight, 
                pi.product_dimensions, 
                pi.product_brand, 
                pi.product_manufacturer, 
                pi.warranty_period, 
                pi.product_material, 
                tr.rate AS tax_rate, 
                ps.product_supplier_id, 
                ps.supplier_id
            FROM core.products p
            LEFT JOIN core.product_types pt ON p.product_type_id = pt.product_type_id
            LEFT JOIN core.product_categories pc ON p.category_id = pc.category_id
            LEFT JOIN core.product_subcategories psc ON p.subcategory_id = psc.subcategory_id
            LEFT JOIN inventory.product_inventory pi ON p.product_id = pi.product_id
            LEFT JOIN core.tax_rates tr ON p.tax_rate_id = tr.tax_rate_id
            LEFT JOIN inventory.product_supplier ps ON p.product_id = ps.product_id
            WHERE p.product_id = :product_id
        ";

        $stmt = $conn->prepare($sql);
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

    } catch (PDOException $e) {
        error_log('[getProduct] DB Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Database error occurred',
            'data'    => null
        ];
    } catch (Throwable $e) {
        error_log('[getProduct] Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Unexpected error occurred',
            'data'    => null
        ];
    }
}

function get_product_types(): array {
    global $conn;
    try {
        $stmt = $conn->query('SELECT * FROM core.product_types ORDER BY product_type_name');
        $types = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'data'    => $types
        ];
    } catch (PDOException $e) {
        error_log('[getProductTypes] DB Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to fetch product types',
            'data'    => null
        ];
    }
}

function get_product_categories(?int $productTypeId = null): array {
    global $conn;
    try {
        $sql = 'SELECT c.*, t.product_type_name FROM core.product_categories c LEFT JOIN core.product_types t ON c.product_type_id = t.product_type_id';
        $params = [];

        if ($productTypeId !== null) {
            $sql .= ' WHERE c.product_type_id = :product_type_id';
            $params['product_type_id'] = $productTypeId;
        }

        $sql .= ' ORDER BY c.category_name';
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $categories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'data'    => $categories
        ];
    } catch (PDOException $e) {
        error_log('[getProductCategories] DB Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to fetch product categories',
            'data'    => null
        ];
    }
}

function get_product_subcategories(?int $categoryId = null): array {
    global $conn;
    try {
        $sql = 'SELECT s.*, c.category_name, t.product_type_name FROM core.product_subcategories s LEFT JOIN core.product_categories c ON s.category_id = c.category_id LEFT JOIN core.product_types t ON c.product_type_id = t.product_type_id';
        $params = [];

        if ($categoryId !== null) {
            $sql .= ' WHERE s.category_id = :category_id';
            $params['category_id'] = $categoryId;
        }

        $sql .= ' ORDER BY s.subcategory_name';
        $stmt = $conn->prepare($sql);
        $stmt->execute($params);

        $subcategories = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'success' => true,
            'data'    => $subcategories
        ];
    } catch (PDOException $e) {
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

    $sql = "INSERT INTO core.products (
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

function update_product_status(int $productId, string $status, int $user_id): array {
    global $conn;

    // Permission check
    if (!check_user_permission($user_id, 'update_document')) {
        error_log("Permission denied for user $user_id to update product status");
        return [
            'success' => false,
            'message' => 'Permission denied',
            'data'    => null
        ];
    }

    try {
        if (!$productId || !$status) {
            throw new Exception('Product ID and status are required');
        }

        $allowedStatuses = ['active', 'inactive', 'discontinued'];
        if (!in_array($status, $allowedStatuses, true)) {
            throw new Exception('Invalid status');
        }

        $sql = "UPDATE core.products 
                SET product_status = :status, updated_at = CURRENT_TIMESTAMP 
                WHERE product_id = :product_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':status', $status);
        $stmt->bindValue(':product_id', (int)$productId, PDO::PARAM_INT);
        $stmt->execute();

        // Logging and notification
        log_user_action($user_id, 'update_product_status', $productId, $status);
        send_notification($user_id, "Product #$productId status changed to $status.");

        return [
            'success' => true,
            'message' => 'Product status updated',
            'data'    => [
                'product_id'     => $productId,
                'product_status' => $status
            ]
        ];
    } catch (Exception $e) {
        error_log('[update_product_status] Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => $e->getMessage(),
            'data'    => null
        ];
    }
}

function delete_product(int $productId, int $user_id): array {
    global $conn;

    // Permission check
    if (!check_user_permission($user_id, 'delete_document')) {
        error_log("Permission denied for user $user_id to delete product");
        return [
            'success' => false,
            'message' => 'Permission denied',
            'data'    => null
        ];
    }

    try {
        // Soft delete - update status to 'deleted' instead of actually deleting
        $sql = "UPDATE core.products 
                SET product_status = 'deleted', updated_at = CURRENT_TIMESTAMP 
                WHERE product_id = :product_id";

        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':product_id', (int)$productId, PDO::PARAM_INT);
        $stmt->execute();

        if ($stmt->rowCount() === 0) {
            return [
                'success' => false,
                'message' => 'Product not found',
                'data'    => null
            ];
        }

        // Logging and notification
        log_user_action($user_id, 'delete_product', $productId);
        send_notification($user_id, "Product #$productId deleted successfully.");

        return [
            'success' => true,
            'message' => 'Product deleted successfully',
            'data'    => [
                'product_id' => $productId
            ]
        ];
    } catch (Exception $e) {
        error_log('[delete_product] Error: ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to delete product',
            'data'    => null
        ];
    }
}

function insert_product_inventory(int $productId, array $data): bool {
    global $conn;
    $sql = "INSERT INTO inventory.product_inventory (
                product_id, product_stock_quantity, product_reorder_level, product_lead_time, product_weight, product_dimensions, product_brand, product_manufacturer, warranty_period, product_material
            ) VALUES (
                :product_id, :product_stock_quantity, :product_reorder_level, :product_lead_time, :product_weight, :product_dimensions, :product_brand, :product_manufacturer, :warranty_period, :product_material
            )";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'product_id' => $productId,
            'product_stock_quantity' => $data['product_stock_quantity'] ?? null,
            'product_reorder_level' => $data['product_reorder_level'] ?? 0,
            'product_lead_time' => $data['product_lead_time'] ?? null,
            'product_weight' => $data['product_weight'] ?? null,
            'product_dimensions' => $data['product_dimensions'] ?? null,
            'product_brand' => $data['product_brand'] ?? null,
            'product_manufacturer' => $data['product_manufacturer'] ?? null,
            'warranty_period' => $data['warranty_period'] ?? null,
            'product_material' => $data['product_material'] ?? null,
        ]);
        return true;
    } catch (Throwable $e) {
        error_log('[insert_product_inventory] Error: ' . $e->getMessage());
        return false;
    }
}

function update_product_inventory(int $productId, array $data): bool {
    global $conn;
    // Fetch current inventory row
    $stmt = $conn->prepare("SELECT * FROM inventory.product_inventory WHERE product_id = :product_id");
    $stmt->execute(['product_id' => $productId]);
    $current = $stmt->fetch(PDO::FETCH_ASSOC);
    if (!$current) {
        // If no row exists, insert new
        return insert_product_inventory($productId, $data);
    }
    $fields = [
        'product_stock_quantity',
        'product_reorder_level',
        'product_lead_time',
        'product_weight',
        'product_dimensions',
        'product_brand',
        'product_manufacturer',
        'warranty_period',
        'product_material',
    ];
    $update = [];
    foreach ($fields as $field) {
        if (array_key_exists($field, $data)) {
            $update[$field] = $data[$field];
        } else {
            $update[$field] = $current[$field];
        }
    }
    $sql = "UPDATE inventory.product_inventory SET
                product_stock_quantity = :product_stock_quantity,
                product_reorder_level = :product_reorder_level,
                product_lead_time = :product_lead_time,
                product_weight = :product_weight,
                product_dimensions = :product_dimensions,
                product_brand = :product_brand,
                product_manufacturer = :product_manufacturer,
                warranty_period = :warranty_period,
                product_material = :product_material,
                updated_at = CURRENT_TIMESTAMP
            WHERE product_id = :product_id";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'product_id' => $productId,
            'product_stock_quantity' => $update['product_stock_quantity'],
            'product_reorder_level' => $update['product_reorder_level'],
            'product_lead_time' => $update['product_lead_time'],
            'product_weight' => $update['product_weight'],
            'product_dimensions' => $update['product_dimensions'],
            'product_brand' => $update['product_brand'],
            'product_manufacturer' => $update['product_manufacturer'],
            'warranty_period' => $update['warranty_period'],
            'product_material' => $update['product_material'],
        ]);
        return true;
    } catch (Throwable $e) {
        error_log('[update_product_inventory] Error: ' . $e->getMessage());
        return false;
    }
}

/**
 * Bulk soft-delete products by ID array.
 */
function bulk_delete_products(array $productIds, int $user_id): array {
    global $conn;
    $errors = [];
    $deleted = [];
    if (!check_user_permission($user_id, 'delete_document')) {
        $msg = 'Permission denied';
        error_log("[bulk_delete_products] $msg | user_id=$user_id");
        log_user_action($user_id, 'bulk_delete_products', null, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'PERMISSION_DENIED',
            'errors' => []
        ];
    }
    try {
        if (!$conn->inTransaction()) {
            $conn->beginTransaction();
        }
        foreach ($productIds as $pid) {
            try {
                $sql = "UPDATE core.products SET product_status = 'deleted', updated_at = CURRENT_TIMESTAMP WHERE product_id = :product_id";
                $stmt = $conn->prepare($sql);
                $stmt->bindValue(':product_id', (int)$pid, PDO::PARAM_INT);
                $stmt->execute();
                if ($stmt->rowCount() === 0) {
                    $err = "Product $pid not found or already deleted";
                    $errors[] = ['product_id' => $pid, 'reason' => $err];
                    error_log("[bulk_delete_products] $err");
                    log_user_action($user_id, 'bulk_delete_products', $pid, $err);
                } else {
                    $deleted[] = $pid;
                    log_user_action($user_id, 'delete_product', $pid, 'Bulk delete');
                }
            } catch (Throwable $e) {
                $err = $e->getMessage();
                $errors[] = ['product_id' => $pid, 'reason' => $err];
                error_log("[bulk_delete_products] $err");
                log_user_action($user_id, 'bulk_delete_products', $pid, $err);
            }
        }
        $conn->commit();
        $msg = count($deleted) . " products deleted" . ($errors ? ", some errors occurred" : "");
        return [
            'success' => count($deleted) > 0,
            'message' => $msg,
            'data' => ['deleted' => $deleted],
            'error_code' => $errors ? 'PARTIAL_FAILURE' : null,
            'errors' => $errors
        ];
    } catch (Throwable $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $err = $e->getMessage();
        error_log("[bulk_delete_products] $err");
        log_user_action($user_id, 'bulk_delete_products', null, $err);
        return [
            'success' => false,
            'message' => 'Bulk delete failed: ' . $err,
            'data' => null,
            'error_code' => 'BULK_DELETE_FAILED',
            'errors' => [['reason' => $err]]
        ];
    }
}

// Save (add or update) a product category
function save_product_category(array $data): array {
    global $conn;
    $category_id = $data['category_id'] ?? null;
    $name = trim($data['category_name'] ?? '');
    $desc = trim($data['category_description'] ?? '');
    $type_id = $data['product_type_id'] ?? null;
    if ($name === '' || !$type_id) {
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        return ['success' => false, 'message' => get_friendly_error('Category name and type are required')];
    }
    try {
        if ($category_id) {
            // Update
            $sql = "UPDATE core.product_categories SET category_name = :name, category_description = :desc, product_type_id = :type_id, updated_at = NOW() WHERE category_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'name' => $name,
                'desc' => $desc,
                'type_id' => $type_id,
                'id' => $category_id
            ]);
            return ['success' => true, 'message' => 'Category updated'];
        } else {
            // Insert
            $sql = "INSERT INTO core.product_categories (category_name, category_description, product_type_id, created_at) VALUES (:name, :desc, :type_id, NOW()) RETURNING category_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'name' => $name,
                'desc' => $desc,
                'type_id' => $type_id
            ]);
            $new_id = $stmt->fetchColumn();
            return ['success' => true, 'message' => 'Category added', 'data' => ['category_id' => $new_id]];
        }
    } catch (Throwable $e) {
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        error_log('[save_product_category] ' . $e->getMessage());
        return ['success' => false, 'message' => get_friendly_error($e->getMessage())];
    }
}

// Save (add or update) a product subcategory
function save_product_subcategory(array $data): array {
    global $conn;
    $subcategory_id = $data['subcategory_id'] ?? null;
    $name = trim($data['subcategory_name'] ?? '');
    $desc = trim($data['subcategory_description'] ?? '');
    $category_id = $data['category_id'] ?? null;
    if ($name === '' || !$category_id) {
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        return ['success' => false, 'message' => get_friendly_error('Subcategory name and category are required')];
    }
    try {
        if ($subcategory_id) {
            // Update
            $sql = "UPDATE core.product_subcategories SET subcategory_name = :name, subcategory_description = :desc, category_id = :category_id, updated_at = NOW() WHERE subcategory_id = :id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'name' => $name,
                'desc' => $desc,
                'category_id' => $category_id,
                'id' => $subcategory_id
            ]);
            return ['success' => true, 'message' => 'Subcategory updated'];
        } else {
            // Insert
            $sql = "INSERT INTO core.product_subcategories (subcategory_name, subcategory_description, category_id, created_at) VALUES (:name, :desc, :category_id, NOW()) RETURNING subcategory_id";
            $stmt = $conn->prepare($sql);
            $stmt->execute([
                'name' => $name,
                'desc' => $desc,
                'category_id' => $category_id
            ]);
            $new_id = $stmt->fetchColumn();
            return ['success' => true, 'message' => 'Subcategory added', 'data' => ['subcategory_id' => $new_id]];
        }
    } catch (Throwable $e) {
        require_once __DIR__ . '/../../../src/Helpers/helpers.php';
        error_log('[save_product_subcategory] ' . $e->getMessage());
        return ['success' => false, 'message' => get_friendly_error($e->getMessage())];
    }
}

// Add helper for linking product to supplier using product_supplier_id
function link_product_to_supplier_by_id(int $product_id, int $supplier_id, int $user_id): array {
    global $conn;
    if (!check_user_permission($user_id, 'update_product')) {
        $msg = 'Permission denied';
        error_log('[link_product_to_supplier_by_id] ' . $msg);
        log_user_action($user_id, 'link_product_to_supplier_by_id', $product_id, $msg);
        return ['success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'PERMISSION_DENIED'];
    }
    try {
        $sql = "INSERT INTO inventory.product_supplier (product_id, supplier_id) VALUES (:product_id, :supplier_id) RETURNING product_supplier_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['product_id' => $product_id, 'supplier_id' => $supplier_id]);
        $product_supplier_id = $stmt->fetchColumn();
        log_user_action($user_id, 'link_product_to_supplier_by_id', $product_id, $product_supplier_id);
        return ['success' => true, 'message' => 'Product linked to supplier', 'data' => ['product_supplier_id' => $product_supplier_id]];
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        error_log('[link_product_to_supplier_by_id] ' . $msg);
        log_user_action($user_id, 'link_product_to_supplier_by_id', $product_id, $msg);
        return ['success' => false, 'message' => 'Failed to link product to supplier', 'data' => null, 'error_code' => 'PRODUCT_SUPPLIER_LINK_ERROR'];
    }
}
// Add helper for unlinking by product_supplier_id
function unlink_product_supplier_by_id(int $product_supplier_id, int $user_id): array {
    global $conn;
    if (!check_user_permission($user_id, 'update_product')) {
        $msg = 'Permission denied';
        error_log('[unlink_product_supplier_by_id] ' . $msg);
        log_user_action($user_id, 'unlink_product_supplier_by_id', $product_supplier_id, $msg);
        return ['success' => false, 'message' => $msg, 'data' => null, 'error_code' => 'PERMISSION_DENIED'];
    }
    try {
        $sql = "DELETE FROM inventory.product_supplier WHERE product_supplier_id = :product_supplier_id";
        $stmt = $conn->prepare($sql);
        $stmt->execute(['product_supplier_id' => $product_supplier_id]);
        log_user_action($user_id, 'unlink_product_supplier_by_id', $product_supplier_id);
        return ['success' => true, 'message' => 'Product-supplier link removed', 'data' => ['product_supplier_id' => $product_supplier_id]];
    } catch (Throwable $e) {
        $msg = $e->getMessage();
        error_log('[unlink_product_supplier_by_id] ' . $msg);
        log_user_action($user_id, 'unlink_product_supplier_by_id', $product_supplier_id, $msg);
        return ['success' => false, 'message' => 'Failed to unlink product-supplier', 'data' => null, 'error_code' => 'PRODUCT_SUPPLIER_UNLINK_ERROR'];
    }
}

// --- API: Get all suppliers and stock info for a product ---
function get_product_suppliers_and_stock(int $productId): array {
    global $conn;
    try {
        // 1. Get all product_supplier links for this product
        $sql = "SELECT ps.product_supplier_id, s.supplier_id, s.supplier_name, s.supplier_contact, s.supplier_email, s.website_url
                FROM inventory.product_supplier ps
                JOIN inventory.supplier s ON ps.supplier_id = s.supplier_id
                WHERE ps.product_id = :product_id AND s.deleted_at IS NULL";
        $stmt = $conn->prepare($sql);
        $stmt->execute([':product_id' => $productId]);
        $suppliers = $stmt->fetchAll(PDO::FETCH_ASSOC);
        $result = [];
        foreach ($suppliers as $supplier) {
            $product_supplier_id = $supplier['product_supplier_id'];
            // 2. Get all stock entries for this product_supplier_id (FIFO table)
            $sqlStock = "SELECT stock_entry_id, quantity, remaining_quantity, cost_per_unit, received_at, notes
                         FROM inventory.product_stock_entries
                         WHERE product_supplier_id = :psid
                         ORDER BY received_at DESC";
            $stmtStock = $conn->prepare($sqlStock);
            $stmtStock->execute([':psid' => $product_supplier_id]);
            $fifo_entries = $stmtStock->fetchAll(PDO::FETCH_ASSOC);
            // 3. Calculate total stock (sum of remaining_quantity)
            $total_stock = 0;
            $last_restock = null;
            $last_price = null;
            if ($fifo_entries) {
                foreach ($fifo_entries as $entry) {
                    $total_stock += (int)$entry['remaining_quantity'];
                }
                // Last restock = most recent received_at
                $last_restock = $fifo_entries[0]['received_at'];
                $last_price = $fifo_entries[0]['cost_per_unit'];
            }
            $result[] = [
                'supplier_id' => $supplier['supplier_id'],
                'supplier_name' => $supplier['supplier_name'],
                'supplier_contact' => $supplier['supplier_contact'],
                'supplier_email' => $supplier['supplier_email'],
                'website_url' => $supplier['website_url'],
                'product_supplier_id' => $supplier['product_supplier_id'],
                'total_stock' => $total_stock,
                'last_restock' => $last_restock,
                'last_price' => $last_price,
                'fifo_entries' => $fifo_entries
            ];
        }
        return [
            'success' => true,
            'data' => $result
        ];
    } catch (\Throwable $e) {
        error_log('[get_product_suppliers_and_stock] ' . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to fetch suppliers and stock info',
            'error' => $e->getMessage(),
            'data' => null
        ];
    }
}

// --- API: Adjust product stock (manual adjustment, admin only) ---
function adjust_product_stock($product_supplier_id, $quantity, $cost_per_unit = null, $notes = null, $user_id = null): array {
    global $conn;
    require_once __DIR__ . '/../../../src/Helpers/helpers.php';
    // Permission check
    if (!check_user_permission($user_id, 'update_product')) {
        $msg = get_friendly_error('Permission denied for user to adjust stock');
        error_log('[adjust_product_stock] ' . $msg);
        log_user_action($user_id, 'adjust_product_stock', $product_supplier_id, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'PERMISSION_DENIED'
        ];
    }
    // Validate input
    if (!$product_supplier_id || !is_numeric($quantity) || $quantity == 0) {
        $msg = get_friendly_error('Invalid stock adjustment: Quantity must not be zero. Please enter a positive or negative value to increase or decrease stock.');
        error_log('[adjust_product_stock] ' . $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'VALIDATION_ERROR'
        ];
    }
    try {
        $sql = "INSERT INTO inventory.product_stock_entries (product_supplier_id, quantity, remaining_quantity, cost_per_unit, notes) VALUES (:psid, :qty, :rem, :cost, :notes)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            ':psid' => $product_supplier_id,
            ':qty' => $quantity,
            ':rem' => $quantity, // For adjustments, remaining = quantity (can be negative)
            ':cost' => $cost_per_unit,
            ':notes' => $notes
        ]);
        log_user_action($user_id, 'adjust_product_stock', $product_supplier_id, json_encode(['quantity' => $quantity, 'cost_per_unit' => $cost_per_unit, 'notes' => $notes]));
        return [
            'success' => true,
            'message' => 'Stock adjustment recorded.',
            'data' => null
        ];
    } catch (\Throwable $e) {
        $msg = get_friendly_error($e->getMessage());
        error_log('[adjust_product_stock] ' . $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'STOCK_ADJUST_ERROR'
        ];
    }
}


