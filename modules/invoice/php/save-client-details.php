<?php
session_start();
header('Content-Type: application/json');
include('../../../php/db.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conn->beginTransaction();
        $clientType = $_POST['clientType'];
        $updatedAt  = date('Y-m-d H:i:s');
        
        if ($clientType === 'company') {
            // -----------------------------
            // Company Branch: Insert New Company
            // -----------------------------
            // Collect POST data for company (new company, so no companyId is provided)
            $companyName     = !empty($_POST['addCompanyName']) ? $_POST['addCompanyName'] : null;
            $companyTaxNo    = !empty($_POST['addCompanyTaxNo']) ? $_POST['addCompanyTaxNo'] : null;
            $companyType     = !empty($_POST['addCompanyType']) ? $_POST['addCompanyType'] : null;
            $companyRegisNo  = !empty($_POST['addCompanyRegisNo']) ? $_POST['addCompanyRegisNo'] : null;
            $companyIndustry = !empty($_POST['addCompanyIndustry']) ? $_POST['addCompanyIndustry'] : null;
            $companyWebsite  = !empty($_POST['addCompanyWebsite']) ? $_POST['addCompanyWebsite'] : null;
            $companyPhone    = !empty($_POST['addCompanyPhone']) ? $_POST['addCompanyPhone'] : null;
            $companyEmail    = !empty($_POST['addCompanyEmail']) ? $_POST['addCompanyEmail'] : null;
            
            // Address details for company
            $addrLine1 = !empty($_POST['addCompanyAddr1']) ? $_POST['addCompanyAddr1'] : null;
            $addrLine2 = !empty($_POST['addCompanyAddr2']) ? $_POST['addCompanyAddr2'] : null;
            $suburb    = !empty($_POST['addCompanySuburb']) ? $_POST['addCompanySuburb'] : null;
            $city      = !empty($_POST['addCompanyCity']) ? $_POST['addCompanyCity'] : null;
            $province  = !empty($_POST['addCompanyProvince']) ? $_POST['addCompanyProvince'] : null;
            $country   = !empty($_POST['addCompanyCountry']) ? $_POST['addCompanyCountry'] : null;
            $postcode  = !empty($_POST['addCompanyPostcode']) ? $_POST['addCompanyPostcode'] : null;
            
            // Contact details for company
            $contactName     = !empty($_POST['addCompanyContactName']) ? $_POST['addCompanyContactName'] : null;
            $contactEmail    = !empty($_POST['addCompanyContactEmail']) ? $_POST['addCompanyContactEmail'] : null;
            $contactPhone    = !empty($_POST['addCompanyContactPhone']) ? $_POST['addCompanyContactPhone'] : null;
            $contactPosition = !empty($_POST['addCompanyContactPosition']) ? $_POST['addCompanyContactPosition'] : null;
            
            // Insert new company details and retrieve the new company_id
            $sqlCompany = "INSERT INTO company (
                company_name,
                company_tax_no,
                company_type,
                company_regis_no,
                industry,
                website,
                company_tell,
                company_email,
                updated_at
            ) VALUES (
                :companyName,
                :companyTaxNo,
                :companyType,
                :companyRegisNo,
                :companyIndustry,
                :companyWebsite,
                :companyPhone,
                :companyEmail,
                :updatedAt
            ) RETURNING company_id";
            
            $stmt = $conn->prepare($sqlCompany);
            $stmt->execute([
                ':companyName'     => $companyName,
                ':companyTaxNo'    => $companyTaxNo,
                ':companyType'     => $companyType,
                ':companyRegisNo'  => $companyRegisNo,
                ':companyIndustry' => $companyIndustry,
                ':companyWebsite'  => $companyWebsite,
                ':companyPhone'    => $companyPhone,
                ':companyEmail'    => $companyEmail,
                ':updatedAt'       => $updatedAt
            ]);
            $newCompanyId = $stmt->fetchColumn();
            
            // Insert address details and retrieve the new addr_id
            $sqlAddress = "INSERT INTO address (
                addr_line_1,
                addr_line_2,
                suburb,
                city,
                province,
                country,
                postcode,
                updated_at
            ) VALUES (
                :addrLine1,
                :addrLine2,
                :suburb,
                :city,
                :province,
                :country,
                :postcode,
                :updatedAt
            ) RETURNING addr_id";
            
            $stmt = $conn->prepare($sqlAddress);
            $stmt->execute([
                ':addrLine1' => $addrLine1,
                ':addrLine2' => $addrLine2,
                ':suburb'    => $suburb,
                ':city'      => $city,
                ':province'  => $province,
                ':country'   => $country,
                ':postcode'  => $postcode,
                ':updatedAt' => $updatedAt
            ]);
            $addrId = $stmt->fetchColumn();
            
            // Link company with address
            $sqlCompanyAddress = "INSERT INTO company_address (
                company_id, addr_id
            ) VALUES (
                :companyId, :addrId
            )";
            
            $stmt = $conn->prepare($sqlCompanyAddress);
            $stmt->execute([
                ':companyId' => $newCompanyId,
                ':addrId'    => $addrId
            ]);
            
            // Insert contact details for the company
            $sqlInsertContact = "INSERT INTO company_contacts (
                company_id, contact_name, contact_email, contact_phone, position, updated_at
            ) VALUES (
                :companyId, :contactName, :contactEmail, :contactPhone, :contactPosition, :updatedAt
            )";
            
            $stmt = $conn->prepare($sqlInsertContact);
            $stmt->execute([
                ':companyId'       => $newCompanyId,
                ':contactName'     => $contactName,
                ':contactEmail'    => $contactEmail,
                ':contactPhone'    => $contactPhone,
                ':contactPosition' => $contactPosition,
                ':updatedAt'       => $updatedAt
            ]);
            
            $responseMessage = ["status" => "success", "message" => "Company details saved successfully. Company ID: " . $newCompanyId];
            $finalResponseMessage = $responseMessage;

        } else if ($clientType === 'customer') {
            // -----------------------------
            // Customer Branch: Insert New Customer
            // -----------------------------
            // Collect POST data for a new customer
            $customerInitials = !empty($_POST['addCustomerInitials']) ? $_POST['addCustomerInitials'] : null;
            $customerTitle    = !empty($_POST['addCustomerTitle']) ? $_POST['addCustomerTitle'] : null;
            $customerName     = !empty($_POST['addCustomerFirstName']) ? $_POST['addCustomerFirstName'] : null;
            $customerSurname  = !empty($_POST['addCustomerLastName']) ? $_POST['addCustomerLastName'] : null;
            $customerEmail    = !empty($_POST['addCustomerEmail']) ? $_POST['addCustomerEmail'] : null;
            $customerCell     = !empty($_POST['addCustomerCell']) ? $_POST['addCustomerCell'] : null;
            $customerTel      = !empty($_POST['addCustomerTel']) ? $_POST['addCustomerTel'] : null;
            
            // Address details for customer
            $custAddrLine1    = !empty($_POST['addCustomerAddr1']) ? $_POST['addCustomerAddr1'] : null;
            $custAddrLine2    = !empty($_POST['addCustomerAddr2']) ? $_POST['addCustomerAddr2'] : null;
            $custCity         = !empty($_POST['addCustomerCity']) ? $_POST['addCustomerCity'] : null;
            $custProvince     = !empty($_POST['addCustomerProvince']) ? $_POST['addCustomerProvince'] : null;
            $custPostalCode   = !empty($_POST['addCustomerPostalCode']) ? $_POST['addCustomerPostalCode'] : null;
            $custCountry      = !empty($_POST['addCustomerCountry']) ? $_POST['addCustomerCountry'] : null;
            $custSuburb       = !empty($_POST['addCustomerSuburb']) ? $_POST['addCustomerSuburb'] : null;
            
            // Additional details for customer (DOB, Gender, Loyalty, Notes)
            $customerDOB      = !empty($_POST['addCustomerDOB']) ? $_POST['addCustomerDOB'] : null;
            $customerGender   = !empty($_POST['addCustomerGender']) ? $_POST['addCustomerGender'] : null;
            $customerLoyalty  = !empty($_POST['addCustomerLoyalty']) ? $_POST['addCustomerLoyalty'] : null;
            $customerNotes    = !empty($_POST['addCustomerNotes']) ? $_POST['addCustomerNotes'] : null;
            
            // Insert new customer details and retrieve the new cust_id
            $sqlCustomer = "INSERT INTO customers (
                cust_fname,
                cust_lname,
                cust_init,
                cust_title,
                cust_email,
                cust_tel,
                cust_cell,
                updated_at
            ) VALUES (
                :customerName,
                :customerSurname,
                :customerInitials,
                :customerTitle,
                :customerEmail,
                :customerTel,
                :customerCell,
                :updatedAt
            ) RETURNING cust_id";
            
            $stmt = $conn->prepare($sqlCustomer);
            $stmt->execute([
                ':customerName'     => $customerName,
                ':customerSurname'  => $customerSurname,
                ':customerInitials' => $customerInitials,
                ':customerTitle'    => $customerTitle,
                ':customerEmail'    => $customerEmail,
                ':customerTel'      => $customerTel,
                ':customerCell'     => $customerCell,
                ':updatedAt'        => $updatedAt,
            ]);
            $newCustomerId = $stmt->fetchColumn();
            
            // Insert address details for customer
            // Here we set the suburb to NULL (adjust if you want to include it)
            $sqlAddress = "INSERT INTO address (
                addr_line_1,
                addr_line_2,
                suburb,
                city,
                province,
                country,
                postcode,
                updated_at
            ) VALUES (
                :custAddrLine1,
                :custAddrLine2,
                :custSuburb,
                :custCity,
                :custProvince,
                :custCountry,
                :custPostalCode,
                :updatedAt
            ) RETURNING addr_id";
            
            $stmt = $conn->prepare($sqlAddress);
            $stmt->execute([
                ':custAddrLine1'  => $custAddrLine1,
                ':custAddrLine2'  => $custAddrLine2,
                ':custSuburb'     => $custSuburb,
                ':custCity'       => $custCity,
                ':custProvince'   => $custProvince,
                ':custCountry'    => $custCountry,
                ':custPostalCode' => $custPostalCode,
                ':updatedAt'      => $updatedAt
            ]);
            $addrId = $stmt->fetchColumn();
            
            // Link customer with address
            $sqlCustomerAddress = "INSERT INTO customer_address (
                cust_id,
                addr_id,
                updated_at
            ) VALUES (
                :customerId,
                :addrId,
                :updatedAt
            )";
            
            $stmt = $conn->prepare($sqlCustomerAddress);
            $stmt->execute([
                ':customerId' => $newCustomerId,
                ':addrId'     => $addrId,
                ':updatedAt'  => $updatedAt
            ]);
            
            $responseMessage = ["status" => "success", "message" => "Customer details saved successfully. Customer ID: " . $newCustomerId];
            $finalResponseMessage = $responseMessage;
        } else {
            $responseMessage = ["status" => "error", "message" => "Invalid client type provided."];
            echo json_encode($responseMessage);
            exit;
        }
        
        $conn->commit();
        if (isset($finalResponseMessage)) {
            echo json_encode($finalResponseMessage);
        }
    } catch (Exception $e) {
        $conn->rollBack();
        $responseMessage = ["status" => "error", "message" => "Transaction failed: " . $e->getMessage()];
        echo json_encode($responseMessage);
    }
} else {
    $responseMessage = ["status" => "error", "message" => "Invalid request method. Only POST requests are allowed."];
    echo json_encode($responseMessage);
}
?>
