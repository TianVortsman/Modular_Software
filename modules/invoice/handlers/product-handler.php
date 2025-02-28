<?php
session_start();
header('Content-Type: application/json');
include('../../../php/db.php');

// Check if the request is a POST request
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();
        
        // Determine if this is a JSON request or a form with file upload
        $isJsonRequest = isset($_SERVER['CONTENT_TYPE']) && 
                         strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false;
        
        if ($isJsonRequest) {
            // Handle JSON input (for product updates without image)
            $input = json_decode(file_get_contents('php://input'), true);
        } else {
            // Handle form data (for new products with image)
            $input = $_POST;
        }

        // Collect data
        $productId          = !empty($input['prod_id']) ? $input['prod_id'] : null;
        $productName        = !empty($input['prod_name']) ? $input['prod_name'] : null;
        $productDescr       = !empty($input['prod_descr']) ? $input['prod_descr'] : null;
        $productPrice       = !empty($input['prod_price']) ? $input['prod_price'] : null;
        $productSKU         = !empty($input['sku']) ? $input['sku'] : null;
        $productBarcode     = !empty($input['barcode']) ? $input['barcode'] : null;
        $productBrand       = !empty($input['brand']) ? $input['brand'] : null;
        $productManufacturer= !empty($input['manufacturer']) ? $input['manufacturer'] : null;
        $productWeight      = !empty($input['weight']) ? $input['weight'] : null;
        $productDimensions  = !empty($input['dimensions']) ? $input['dimensions'] : null;
        $productWarranty    = !empty($input['warranty_period']) ? $input['warranty_period'] : null;
        $productTaxRate     = !empty($input['tax_rate']) ? $input['tax_rate'] : null;
        $productDiscount    = !empty($input['discount']) ? $input['discount'] : null;
        $productStatus      = !empty($input['status']) ? $input['status'] : 'active';
        $productType        = !empty($input['prod_type']) ? $input['prod_type'] : 'products';

        // Validate required fields
        if (empty($productName) || empty($productPrice)) {
            throw new Exception("Product name and price are required fields.");
        }

        $updatedAt = date('Y-m-d H:i:s');
        
        // Handle image upload
        $imagePath = null;
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] == 0) {
            // Create upload directory if it doesn't exist
            $uploadDir = '../../../uploads/products/';
            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Generate unique filename
            $fileExtension = pathinfo($_FILES['product_image']['name'], PATHINFO_EXTENSION);
            $uniqueId = uniqid();
            $fileName = $uniqueId . '.' . $fileExtension;
            $targetFilePath = $uploadDir . $fileName;
            
            // Move uploaded file
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $targetFilePath)) {
                // Store relative path in database
                $imagePath = 'uploads/products/' . $fileName;
            } else {
                throw new Exception("Failed to upload image");
            }
        }

        if ($productId) {
            // Update existing product
            $sql = "
                UPDATE product
                SET
                    prod_name        = :productName,
                    prod_descr       = :productDescr,
                    prod_price       = :productPrice,
                    sku              = :productSKU,
                    barcode          = :productBarcode,
                    brand            = :productBrand,
                    manufacturer     = :productManufacturer,
                    weight           = :productWeight,
                    dimensions       = :productDimensions,
                    warranty_period  = :productWarranty,
                    tax_rate         = :productTaxRate,
                    discount         = :productDiscount,
                    status           = :productStatus,
                    product_type     = :productType,
                    updated_at       = :updatedAt
            ";
            
            // Only update image if a new one was uploaded
            if ($imagePath) {
                $sql .= ", image_url = :imagePath";
            }
            
            $sql .= " WHERE prod_id = :productId";

            $stmt = $conn->prepare($sql);
            $params = [
                ':productName'      => $productName,
                ':productDescr'     => $productDescr,
                ':productPrice'     => $productPrice,
                ':productSKU'       => $productSKU,
                ':productBarcode'   => $productBarcode,
                ':productBrand'     => $productBrand,
                ':productManufacturer' => $productManufacturer,
                ':productWeight'    => $productWeight,
                ':productDimensions'=> $productDimensions,
                ':productWarranty'  => $productWarranty,
                ':productTaxRate'   => $productTaxRate,
                ':productDiscount'  => $productDiscount,
                ':productStatus'    => $productStatus,
                ':productType'      => $productType,
                ':updatedAt'        => $updatedAt,
                ':productId'        => $productId
            ];
            
            if ($imagePath) {
                $params[':imagePath'] = $imagePath;
            }
            
            $stmt->execute($params);

            $responseMessage = [
                "success"   => true,
                "message"   => "Product updated successfully",
                "productId" => $productId
            ];
        } else {
            // Insert new product
            $sql = "
                INSERT INTO product (
                    prod_name,
                    prod_descr,
                    prod_price,
                    sku,
                    barcode,
                    brand,
                    manufacturer,
                    weight,
                    dimensions,
                    warranty_period,
                    tax_rate,
                    discount,
                    status,
                    product_type,
                    created_at,
                    updated_at
            ";
            
            // Add image_url column if an image was uploaded
            if ($imagePath) {
                $sql .= ", image_url";
            }
            
            $sql .= ") VALUES (
                    :productName,
                    :productDescr,
                    :productPrice,
                    :productSKU,
                    :productBarcode,
                    :productBrand,
                    :productManufacturer,
                    :productWeight,
                    :productDimensions,
                    :productWarranty,
                    :productTaxRate,
                    :productDiscount,
                    :productStatus,
                    :productType,
                    :createdAt,
                    :updatedAt
            ";
            
            // Add image path value if an image was uploaded
            if ($imagePath) {
                $sql .= ", :imagePath";
            }
            
            $sql .= ") RETURNING prod_id";

            $stmt = $conn->prepare($sql);
            $params = [
                ':productName'         => $productName,
                ':productDescr'        => $productDescr,
                ':productPrice'        => $productPrice,
                ':productSKU'          => $productSKU,
                ':productBarcode'      => $productBarcode,
                ':productBrand'        => $productBrand,
                ':productManufacturer' => $productManufacturer,
                ':productWeight'       => $productWeight,
                ':productDimensions'   => $productDimensions,
                ':productWarranty'     => $productWarranty,
                ':productTaxRate'      => $productTaxRate,
                ':productDiscount'     => $productDiscount,
                ':productStatus'       => $productStatus,
                ':productType'         => $productType,
                ':createdAt'           => $updatedAt,
                ':updatedAt'           => $updatedAt
            ];
            
            if ($imagePath) {
                $params[':imagePath'] = $imagePath;
            }
            
            $stmt->execute($params);
            $newProductId = $stmt->fetchColumn();

            $responseMessage = [
                "success"   => true,
                "message"   => "Product created successfully",
                "productId" => $newProductId,
                "imagePath" => $imagePath
            ];
        }

        $conn->commit();
        echo json_encode($responseMessage);
    } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode([
            "success" => false,
            "message" => "Error: " . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        "success" => false,
        "message" => "Invalid request method. Only POST requests are allowed."
    ]);
}
?>