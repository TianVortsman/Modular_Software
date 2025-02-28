<?php 
session_start();
header('Content-Type: application/json');
include('../../../php/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        // Collect POST data
        $customerId = !empty($_POST['customerId']) ? $_POST['customerId'] : null;
        $customerInitials = !empty($_POST['customerInitials']) ? $_POST['customerInitials'] : null;
        $customerTitle = !empty($_POST['customerTitle']) ? $_POST['customerTitle'] : null;
        $customerName = !empty($_POST['customerName']) ? $_POST['customerName'] : null;
        $customerSurname = !empty($_POST['customerSurname']) ? $_POST['customerSurname'] : null;
        $customerEmail = !empty($_POST['customerEmail']) ? $_POST['customerEmail'] : null;
        $customerCell = !empty($_POST['customerCell']) ? $_POST['customerCell'] : null;
        $customerTel = !empty($_POST['customerTel']) ? $_POST['customerTel'] : null;

        $custAddrLine1 = !empty($_POST['custAddrLine1']) ? $_POST['custAddrLine1'] : null;
        $custAddrLine2 = !empty($_POST['custAddrLine2']) ? $_POST['custAddrLine2'] : null;
        $custCity = !empty($_POST['custCity']) ? $_POST['custCity'] : null;
        $custProvince = !empty($_POST['custProvince']) ? $_POST['custProvince'] : null;
        $custPostalCode = !empty($_POST['custPostalCode']) ? $_POST['custPostalCode'] : null;
        $custCountry = !empty($_POST['custCountry']) ? $_POST['custCountry'] : null;
        $custSuburb = !empty($_POST['custSuburb']) ? $_POST['custSuburb'] : null;

        $updatedAt = date('Y-m-d H:i:s');

        // Use UPDATE instead of INSERT for customers
        $sqlCustomer = "UPDATE customers SET 
            cust_fname = :customerName,
            cust_lname = :customerSurname,
            cust_init = :customerInitials,
            cust_title = :customerTitle,
            cust_email = :customerEmail,
            cust_tel = :customerTel,
            cust_cell = :customerCell,
            updated_at = :updatedAt
        WHERE cust_id = :customerId";

        $stmt = $conn->prepare($sqlCustomer);
        $stmt->execute([
            ':customerName' => $customerName,
            ':customerSurname' => $customerSurname,
            ':customerInitials' => $customerInitials,
            ':customerTitle' => $customerTitle,
            ':customerEmail' => $customerEmail,
            ':customerTel' => $customerTel,
            ':customerCell' => $customerCell,
            ':updatedAt' => $updatedAt,
            ':customerId' => $customerId
        ]);

        // Check if address exists before updating
        $sqlCheckAddress = "SELECT addr_id FROM customer_address WHERE cust_id = :customerId";
        $stmt = $conn->prepare($sqlCheckAddress);
        $stmt->execute([':customerId' => $customerId]);
        $addrId = $stmt->fetchColumn();

        if ($addrId) {
            // If address exists, update it
            $sqlAddress = "UPDATE address SET 
            addr_line_1 = :custAddrLine1,
            addr_line_2 = :custAddrLine2,
            suburb = :custSuburb,
            city = :custCity,
            province = :custProvince,
            country = :custCountry,
            postcode = :custPostalCode,
            updated_at = :updatedAt
            WHERE addr_id = :addrId";

            $stmt = $conn->prepare($sqlAddress);
            $stmt->execute([
            ':custAddrLine1' => $custAddrLine1,
            ':custAddrLine2' => $custAddrLine2,
            ':custSuburb' => $custSuburb,
            ':custCity' => $custCity,
            ':custProvince' => $custProvince,
            ':custCountry' => $custCountry,
            ':custPostalCode' => $custPostalCode,
            ':updatedAt' => $updatedAt,
            ':addrId' => $addrId
            ]);
        } else {
            // If address does not exist, insert a new one
            $sqlInsertAddress = "INSERT INTO address (addr_line_1, addr_line_2, suburb, city, province, country, postcode, updated_at)
            VALUES (:custAddrLine1, :custAddrLine2, :custSuburb, :custCity, :custProvince, :custCountry, :custPostalCode, :updatedAt)
            RETURNING addr_id";

            $stmt = $conn->prepare($sqlInsertAddress);
            $stmt->execute([
            ':custAddrLine1' => $custAddrLine1,
            ':custAddrLine2' => $custAddrLine2,
            ':custSuburb' => $custSuburb,
            ':custCity' => $custCity,
            ':custProvince' => $custProvince,
            ':custCountry' => $custCountry,
            ':custPostalCode' => $custPostalCode,
            ':updatedAt' => $updatedAt
            ]);

            $addrId = $stmt->fetchColumn();

            // Link new address to customer
            $sqlCustomerAddress = "INSERT INTO customer_address (cust_id, addr_id, updated_at) 
            VALUES (:customerId, :addrId, :updatedAt)";

            $stmt = $conn->prepare($sqlCustomerAddress);
            $stmt->execute([
            ':customerId' => $customerId,
            ':addrId' => $addrId,
            ':updatedAt' => $updatedAt
            ]);
        }

        $conn->commit();
        echo json_encode(['status' => 'success', 'message' => 'Customer details updated successfully.']);
        } catch (Exception $e) {
        $conn->rollBack();
        echo json_encode(['status' => 'error', 'message' => 'Transaction failed: ' . $e->getMessage()]);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid request method.']);
    }
    ?>
