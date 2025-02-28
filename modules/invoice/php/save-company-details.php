<?php
session_start();
header('Content-Type: application/json');
include('../../../php/db.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $conn->beginTransaction();

        // Collect POST data
        $companyId = !empty($_POST['companyId']) ? $_POST['companyId'] : null;
        $companyName = !empty($_POST['companyName']) ? $_POST['companyName'] : null;
        $companyTaxNo = !empty($_POST['companyTaxNo']) ? $_POST['companyTaxNo'] : null;
        $companyType = !empty($_POST['companyType']) ? $_POST['companyType'] : null;
        $companyRegisNo = !empty($_POST['companyRegisNo']) ? $_POST['companyRegisNo'] : null;
        $companyIndustry = !empty($_POST['companyIndustry']) ? $_POST['companyIndustry'] : null;
        $companyWebsite = !empty($_POST['companyWebsite']) ? $_POST['companyWebsite'] : null;
        $companyPhone = !empty($_POST['companyPhone']) ? $_POST['companyPhone'] : null;
        $companyEmail = !empty($_POST['companyEmail']) ? $_POST['companyEmail'] : null;

        $addrLine1 = !empty($_POST['addrLine1']) ? $_POST['addrLine1'] : null;
        $addrLine2 = !empty($_POST['addrLine2']) ? $_POST['addrLine2'] : null;
        $suburb = !empty($_POST['suburb']) ? $_POST['suburb'] : null;
        $city = !empty($_POST['city']) ? $_POST['city'] : null;
        $province = !empty($_POST['province']) ? $_POST['province'] : null;
        $country = !empty($_POST['country']) ? $_POST['country'] : null;
        $postcode = !empty($_POST['postcode']) ? $_POST['postcode'] : null;

        $contactName = !empty($_POST['contactName']) ? $_POST['contactName'] : null;
        $contactEmail = !empty($_POST['contactEmail']) ? $_POST['contactEmail'] : null;
        $contactPhone = !empty($_POST['contactPhone']) ? $_POST['contactPhone'] : null;
        $contactPosition = !empty($_POST['contactPosition']) ? $_POST['contactPosition'] : null;

        $updatedAt = date('Y-m-d H:i:s');

        // Update company details
        try {
            $sqlCompany = "UPDATE company SET 
                company_name = :companyName,
                company_tax_no = :companyTaxNo,
                company_type = :companyType,
                company_regis_no = :companyRegisNo,
                industry = :companyIndustry,
                website = :companyWebsite,
                company_tell = :companyPhone,
                company_email = :companyEmail,
                updated_at = :updatedAt
            WHERE company_id = :companyId";

            $stmt = $conn->prepare($sqlCompany);
            $stmt->execute([
                ':companyName' => $companyName,
                ':companyTaxNo' => $companyTaxNo,
                ':companyType' => $companyType,
                ':companyRegisNo' => $companyRegisNo,
                ':companyIndustry' => $companyIndustry,
                ':companyWebsite' => $companyWebsite,
                ':companyPhone' => $companyPhone,
                ':companyEmail' => $companyEmail,
                ':updatedAt' => $updatedAt,
                ':companyId' => $companyId
            ]);
        } catch (Exception $e) {
            throw new Exception("Error updating company: " . $e->getMessage());
        }

        // Insert address details
        try {
            $sqlAddress = "
            INSERT INTO address (
                addr_line_1, addr_line_2, suburb, city, province, country, postcode, updated_at
            ) 
            VALUES (
                :addrLine1, :addrLine2, :suburb, :city, :province, :country, :postcode, :updatedAt
            ) 
            RETURNING addr_id";

            $stmt = $conn->prepare($sqlAddress);
            $stmt->execute([
                ':addrLine1' => $addrLine1,
                ':addrLine2' => $addrLine2,
                ':suburb' => $suburb,
                ':province' => $province,
                ':country' => $country,
                ':postcode' => $postcode,
                ':updatedAt' => $updatedAt,
                ':city' => $city
            ]);
        } catch (Exception $e) {
            throw new Exception("Error inserting address: " . $e->getMessage());
        }

        $addrId = $stmt->fetchColumn();

        // Link company with address
        $sqlCompanyAddress = "INSERT INTO company_address (company_id, addr_id) 
        VALUES (:companyId, :addrId) 
        ON CONFLICT (company_id) DO UPDATE 
        SET addr_id = EXCLUDED.addr_id";

        $stmt = $conn->prepare($sqlCompanyAddress);
        $stmt->execute([
            ':companyId' => $companyId,
            ':addrId' => $addrId
        ]);

        // Manage contact details
        try {
            // Check if the contact exists
            $sqlCheck = "SELECT contact_id FROM company_contacts WHERE company_id = :companyId AND contact_email = :contactEmail";
            $stmt = $conn->prepare($sqlCheck);
            $stmt->execute([':companyId' => $companyId, ':contactEmail' => $contactEmail]);
            $contactId = $stmt->fetchColumn(); // Get the existing contact ID
        
            if ($contactId) {
                // Update existing contact
                $sqlUpdate = "UPDATE company_contacts SET 
                    contact_name = :contactName,
                    contact_phone = :contactPhone,
                    position = :contactPosition,
                    updated_at = :updatedAt
                    WHERE contact_id = :contactId";
        
                $stmt = $conn->prepare($sqlUpdate);
                $stmt->execute([
                    ':contactName' => $contactName,
                    ':contactPhone' => $contactPhone,
                    ':contactPosition' => $contactPosition,
                    ':updatedAt' => $updatedAt,
                    ':contactId' => $contactId
                ]);
        
                $responseMessage = ["status" => "success", "message" => "Company updated successfully"];
            } else {
                // Insert new contact
                $sqlInsert = "INSERT INTO company_contacts (
                    company_id, contact_name, contact_email, contact_phone, position, updated_at
                ) VALUES (
                    :companyId, :contactName, :contactEmail, :contactPhone, :contactPosition, :updatedAt
                )";
        
                $stmt = $conn->prepare($sqlInsert);
                $stmt->execute([
                    ':companyId' => $companyId,
                    ':contactName' => $contactName,
                    ':contactEmail' => $contactEmail,
                    ':contactPhone' => $contactPhone,
                    ':contactPosition' => $contactPosition,
                    ':updatedAt' => $updatedAt
                ]);
        
                $responseMessage = ["status" => "success", "message" => "New contact added successfully"];
            }
        } catch (Exception $e) {
            throw new Exception("Error handling contact: " . $e->getMessage());
        }

        // Commit transaction
        $conn->commit();
    } catch (Exception $e) {
        // Rollback transaction on failure
        $conn->rollBack();
        $responseMessage = [
            "status" => "error",
            "message" => "Error: " . $e->getMessage()
        ];
    }
    echo json_encode($responseMessage);
}
?>
