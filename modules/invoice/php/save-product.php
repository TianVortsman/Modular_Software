<?php
session_start();
header('Content-Type: application/json');
include('../../../php/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        
        // Collect and sanitize input data
        $productId = isset($_POST['prod_id']) ? intval($_POST['prod_id']) : null;
        $productData = [
            'prod_name' => isset($_POST['prod_name']) ? trim($_POST['prod_name']) : null,
            'prod_descr' => isset($_POST['prod_descr']) ? trim($_POST['prod_descr']) : null,
            'prod_price' => isset($_POST['prod_price']) ? floatval($_POST['prod_price']) : null,
            'stock_quantity' => isset($_POST['stock_quantity']) ? trim($_POST['stock_quantity']) : null,
            'barcode' => isset($_POST['barcode']) ? trim($_POST['barcode']) : null,
            'product_type' => isset($_POST['product_type']) ? trim($_POST['product_type']) : null,
            'brand' => isset($_POST['brand']) ? trim($_POST['brand']) : null,
            'manufacturer' => isset($_POST['manufacturer']) ? trim($_POST['manufacturer']) : null,
            'weight' => isset($_POST['weight']) ? floatval($_POST['weight']) : null,
            'dimensions' => isset($_POST['dimensions']) ? trim($_POST['dimensions']) : null,
            'warranty_period' => isset($_POST['warranty_period']) ? trim($_POST['warranty_period']) : null,
            'tax_rate' => isset($_POST['tax_rate']) ? floatval($_POST['tax_rate']) : null,
            'discount' => isset($_POST['discount']) ? floatval($_POST['discount']) : null,
            'status' => isset($_POST['status']) ? trim($_POST['status']) : null,
            'sku' => isset($_POST['sku']) && trim($_POST['sku']) !== '' ? trim($_POST['sku']) : null,
            'category' => isset($_POST['category']) ? trim($_POST['category']) : null,
            'sub_category' => isset($_POST['sub_category']) ? trim($_POST['sub_category']) : null,
            'reorder_level' => isset($_POST['reorder_level']) ? intval($_POST['reorder_level']) : null,
            'lead_time' => isset($_POST['lead_time']) && $_POST['lead_time'] !== '' ? intval($_POST['lead_time']) : null,
            'oem_part_number' => isset($_POST['oem_part_number']) ? trim($_POST['oem_part_number']) : null,
            'compatible_vehicles' => isset($_POST['compatible_vehicles']) ? trim($_POST['compatible_vehicles']) : null,
            'material' => isset($_POST['material']) ? trim($_POST['material']) : null,
            'labor_cost' => isset($_POST['labor_cost']) ? floatval($_POST['labor_cost']) : null,
            'estimated_time' => isset($_POST['estimated_time']) && $_POST['estimated_time'] !== '' ? trim($_POST['estimated_time']) : null,
            'service_frequency' => isset($_POST['service_frequency']) ? trim($_POST['service_frequency']) : null,
            'bundle_items' => isset($_POST['bundle_items']) ? trim($_POST['bundle_items']) : null,
            'installation_required' => isset($_POST['installation_required']) && 
                          ($_POST['installation_required'] === 'true' || 
                           $_POST['installation_required'] === '1' || 
                           $_POST['installation_required'] === true) ? true : false
        ];
        
        // Validate required fields
        if (empty($productData['prod_name'])) {
            throw new Exception("Product name is required");
        }
        
        if ($productData['prod_price'] <= 0) {
            throw new Exception("Product price must be greater than zero");
        }
        
        $updatedAt = date('Y-m-d H:i:s');
        
        // Handle image upload if present
        $imageUrl = null;
        if (isset($_FILES['item_image']) && $_FILES['item_image']['error'] === 0) {
            $uploadDir = '../../../uploads/products/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            $fileExtension = pathinfo($_FILES['item_image']['name'], PATHINFO_EXTENSION);
            $newFilename = 'product_' . time() . '_' . rand(1000, 9999) . '.' . $fileExtension;
            $targetFile = $uploadDir . $newFilename;
            
            if (move_uploaded_file($_FILES['item_image']['tmp_name'], $targetFile)) {
                $imageUrl = 'uploads/products/' . $newFilename;
            }
        }
        
        // Add image URL to product data if uploaded
        if ($imageUrl) {
            $productData['image_url'] = $imageUrl;
        }
        
        // Check if we're updating or creating a new product
        if ($productId) {
            // UPDATE existing product
            $sql = "UPDATE product SET 
                prod_name = :prod_name,
                prod_descr = :prod_descr,
                prod_price = :prod_price,
                stock_quantity = :stock_quantity,
                barcode = :barcode,
                product_type = :product_type,
                brand = :brand,
                manufacturer = :manufacturer,
                weight = :weight,
                dimensions = :dimensions,
                warranty_period = :warranty_period,
                tax_rate = :tax_rate,
                discount = :discount,
                status = :status,
                sku = :sku,
                category = :category,
                sub_category = :sub_category,
                reorder_level = :reorder_level,
                lead_time = :lead_time,
                oem_part_number = :oem_part_number,
                compatible_vehicles = :compatible_vehicles,
                material = :material,
                labor_cost = :labor_cost,
                estimated_time = :estimated_time,
                service_frequency = :service_frequency,
                bundle_items = :bundle_items,
                installation_required = :installation_required,
                updated_at = :updated_at";
                
            // Add image_url to update only if a new image was uploaded
            if (isset($productData['image_url'])) {
                $sql .= ", image_url = :image_url";
            }
            
            $sql .= " WHERE prod_id = :prod_id";
            
            $stmt = $conn->prepare($sql);
            
            // Bind all parameters
            foreach ($productData as $key => $value) {
                if ($value === null) {
                    $stmt->bindValue(":$key", null, PDO::PARAM_NULL);
                } else if ($key === 'installation_required') {
                    $stmt->bindValue(":$key", $value, PDO::PARAM_BOOL);
                } else {
                    $stmt->bindValue(":$key", $value);
                }
            }
            
            $stmt->bindValue(":updated_at", $updatedAt);
            $stmt->bindValue(":prod_id", $productId);
            
            $stmt->execute();
            
            $responseMessage = ["status" => "success", "message" => "Product details saved successfully. Product ID: " . $productId];
        } else {
            // INSERT new product
            $columns = array_keys($productData);
            $columns[] = 'image_url';
            $columns[] = 'created_at';
            $columns[] = 'updated_at';
            
            $placeholders = array_map(function($col) { return ":$col"; }, $columns);
            
            $sql = "INSERT INTO product (" . implode(", ", $columns) . ") 
                    VALUES (" . implode(", ", $placeholders) . ")";
            
            $stmt = $conn->prepare($sql);
            
            // Bind all parameters
            foreach ($productData as $key => $value) {
                if ($value === null) {
                    $stmt->bindValue(":$key", null, PDO::PARAM_NULL);
                } else if ($key === 'installation_required') {
                    $stmt->bindValue(":$key", $value, PDO::PARAM_BOOL);
                } else {
                    $stmt->bindValue(":$key", $value);
                }
            }
            
            if (isset($productData['image_url'])) {
                $stmt->bindValue(":image_url", $productData['image_url']);
            }
            
            $stmt->bindValue(":created_at", $updatedAt);
            $stmt->bindValue(":updated_at", $updatedAt);
            
            $stmt->execute();
            $productId = $conn->lastInsertId();
            
            $responseMessage = ["status" => "success", "message" => "Product details saved successfully. Product ID: " . $productId];
        }
        
        $conn->commit();
        echo json_encode($responseMessage);
        
    } catch (PDOException $e) {
        $conn->rollBack();
        echo json_encode([
            'status' => 'error', 
            'message' => "Database error: " . $e->getMessage()
        ]);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            'status' => 'error', 
            'message' => $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'status' => 'error', 
        'message' => 'Invalid request method. Only POST requests are allowed.'
    ]);
}
?>
