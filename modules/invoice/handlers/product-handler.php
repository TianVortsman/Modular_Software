<?php
session_start();
header('Content-Type: application/json');
include('../../../php/db.php');

// Decode JSON input
$input = json_decode(file_get_contents('php://input'), true);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        // Collect POST data from JSON input
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

        $updatedAt = date('Y-m-d H:i:s');

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
                    updated_at       = :updatedAt
                WHERE prod_id = :productId
            ";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
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
                ':updatedAt'        => $updatedAt,
                ':productId'        => $productId
            ]);

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
                    created_at,
                    updated_at
                ) VALUES (
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
                    :createdAt,
                    :updatedAt
                )
                RETURNING prod_id
            ";

            $stmt = $conn->prepare($sql);
            $stmt->execute([
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
                ':createdAt'           => $updatedAt,
                ':updatedAt'           => $updatedAt
            ]);

            $newProductId = $stmt->fetchColumn();

            $responseMessage = [
                "success"   => true,
                "message"   => "Product created successfully",
                "productId" => $newProductId
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
