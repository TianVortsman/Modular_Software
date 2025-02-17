<?php
session_start();
header('Content-Type: application/json');
include('../../../php/db.php');

// Get company_id (required)
if (!isset($_GET['company_id']) || empty($_GET['company_id'])) {
    echo json_encode(["error" => "Company ID is required"]);
    exit;
}

$companyId = (int)$_GET['company_id'];

$sql = "
    SELECT 
        -- Company details
        comp.company_id,
        comp.company_name,
        comp.company_tax_no,
        comp.company_regis_no,
        comp.company_type,
        comp.industry,
        comp.website,
        comp.company_tell,
        comp.company_email,

        -- Address details
        addr.addr_line_1,
        addr.addr_line_2,
        addr.suburb,
        addr.city,
        addr.province,
        addr.country,
        addr.postcode,

        -- Latest contact details per company
        cc.contact_name,
        cc.contact_email,
        cc.contact_phone,
        cc.position,

        -- Invoice details
        ci.inv_no,
        ci.inv_date,
        ci.total_amount,
        ci.status

    FROM company comp
    LEFT JOIN company_address ca ON ca.company_id = comp.company_id
    LEFT JOIN address addr ON addr.addr_id = ca.addr_id
    LEFT JOIN (
        SELECT DISTINCT ON (company_id) 
            company_id, 
            contact_name, 
            contact_email,
            contact_phone,
            position
        FROM company_contacts
        ORDER BY company_id, contact_id DESC
    ) cc ON cc.company_id = comp.company_id
    LEFT JOIN customers c ON c.company_id = comp.company_id
    LEFT JOIN customer_invoice ci ON c.cust_id = ci.po_id
    WHERE comp.company_id = :companyId
";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':companyId', $companyId, PDO::PARAM_INT);
    $stmt->execute();

    $companyData = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($companyData ?: []);
} catch (PDOException $e) {
    echo json_encode(["error" => "Failed to get company data: " . $e->getMessage()]);
}
?>
