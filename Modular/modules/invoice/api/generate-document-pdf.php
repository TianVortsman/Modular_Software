<?php
// generate-document-pdf.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';
use App\Core\Database\ClientDatabase;

use Mpdf\Mpdf;

header('Content-Type: application/json');

try {
    if (!isset($_SESSION['account_number'])) {
        error_log('[INVOICE PDF ERROR] User session not found');
        throw new Exception('User session not found');
    }

    // Only allow POST
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception('POST method required');
    }

    $rawData = file_get_contents('php://input');
    $data = json_decode($rawData, true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception('Invalid JSON data received');
    }

    // Transform document modal data to PDF format
    $pdfData = transformDocumentDataToPdfFormat($data);
    
    // Validate required fields
    if (empty($pdfData['client']) || empty($pdfData['items']) || empty($pdfData['totals'])) {
        error_log('[INVOICE PDF ERROR] Missing required invoice data after transformation');
        throw new Exception('Missing required invoice data');
    }

    // Fetch all settings in one query
    $db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
    $pdo = $db->connect();
    $stmt = $pdo->prepare('SELECT * FROM settings.invoice_settings WHERE id = 1');
    $stmt->execute();
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $pdfData['company'] = [
        'name' => $settings['company_name'] ?? '',
        'address' => $settings['company_address'] ?? '',
        'phone' => $settings['company_phone'] ?? '',
        'email' => $settings['company_email'] ?? '',
        'vat_number' => $settings['vat_number'] ?? '',
        'registration_number' => $settings['registration_number'] ?? ''
    ];
    
    $pdfData['bank'] = [
        'bank_name' => $settings['bank_name'] ?? '',
        'bank_branch' => $settings['bank_branch'] ?? '',
        'account_number' => $settings['account_number'] ?? '',
        'swift_code' => $settings['swift_code'] ?? ''
    ];
    
    $template = $pdfData['template'] ?? ($settings['template_name'] ?? 'modern-blue');

    // Generate PDF
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'format' => 'A4',
        'margin_left' => 15,
        'margin_right' => 15,
        'margin_top' => 15,
        'margin_bottom' => 15
    ]);

    $html = generateInvoiceHTML($pdfData, $template);
    $mpdf->WriteHTML($html);

    // Generate filename
    $isPreview = isset($data['preview']) && $data['preview'];
    
    if ($isPreview) {
        // For preview, output directly to browser without saving
        $filename = 'preview_' . time() . '.pdf';
        header('Content-Type: application/pdf');
        header('Content-Disposition: inline; filename="' . $filename . '"');
        header('Cache-Control: no-cache, no-store, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');
        
        $mpdf->Output($filename, 'I'); // 'I' for inline display
        exit;
    } else {
        // For final documents, save to file
        $filename = $pdfData['document_number'] . '.pdf';
        
        // Save to documents directory
        $uploadDir = __DIR__ . '/../../../Uploads/documents/';
        if (!is_dir($uploadDir)) {
            mkdir($uploadDir, 0755, true);
        }
        
        $filepath = $uploadDir . $filename;
        $mpdf->Output($filepath, 'F');

        // Return success response
        $url = '/Uploads/documents/' . $filename;
        echo json_encode([
            'success' => true,
            'message' => 'PDF generated successfully',
            'url' => $url,
            'filename' => $filename
        ]);
    }

} catch (Exception $e) {
    error_log('[INVOICE PDF ERROR] ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Failed to generate PDF: ' . $e->getMessage(),
        'error_code' => 'PDF_GENERATION_ERROR'
    ]);
}

/**
 * Transform document modal data to PDF format
 */
function transformDocumentDataToPdfFormat($data) {
    $pdfData = [];
    
    // Client information
    $pdfData['client'] = [
        'name' => $data['client_name'] ?? '',
        'email' => $data['client_email'] ?? '',
        'phone' => $data['client_phone'] ?? '',
        'vat_number' => $data['vat_number'] ?? '',
        'registration_number' => $data['registration_number'] ?? '',
        'address1' => $data['address1'] ?? '',
        'address2' => $data['address2'] ?? ''
    ];
    
    // Document information
    $pdfData['document'] = [
        'number' => $data['document_number'] ?? '',
        'type' => $data['document_type'] ?? 'invoice',
        'date' => $data['issue_date'] ?? date('Y-m-d'),
        'due_date' => $data['due_date'] ?? '',
        'status' => $data['document_status'] ?? 'Draft',
        'purchase_order' => $data['client_purchase_order_number'] ?? '',
        'salesperson' => $data['salesperson_name'] ?? ''
    ];
    
    // Items
    $pdfData['items'] = [];
    if (isset($data['items']) && is_array($data['items'])) {
        foreach ($data['items'] as $item) {
            $pdfData['items'][] = [
                'description' => $item['product_description'] ?? '',
                'quantity' => $item['quantity'] ?? 1,
                'unit_price' => $item['unit_price'] ?? '0.00',
                'tax_rate' => $item['tax_percentage'] ?? 0,
                'total' => $item['line_total'] ?? '0.00'
            ];
        }
    }
    
    // Totals
    $pdfData['totals'] = [
        'subtotal' => $data['subtotal'] ?? '0.00',
        'tax_amount' => $data['tax_amount'] ?? '0.00',
        'total_amount' => $data['total_amount'] ?? '0.00',
        'discount_amount' => $data['discount_amount'] ?? '0.00'
    ];
    
    // Notes
    $pdfData['notes'] = [
        'public' => $data['public_note'] ?? '',
        'private' => $data['private_note'] ?? '',
        'footer' => $data['foot_note'] ?? ''
    ];
    
    return $pdfData;
}

/**
 * Generate HTML for the invoice
 */
function generateInvoiceHTML($data, $template) {
    $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
    $html .= '<style>
        body { font-family: "Segoe UI", Arial, sans-serif; color: #222; background: #f4f6fa; margin: 0; padding: 0; }
        .invoice-box {
            max-width: 900px;
            margin: 40px auto;
            padding: 32px 36px 28px 36px;
            background: #fff;
            border-radius: 14px;
            box-shadow: 0 4px 32px rgba(30,60,120,0.10), 0 1.5px 6px rgba(30,60,120,0.07);
            border: 1px solid #e3eafc;
            page-break-inside: avoid;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 32px;
        }
        .logo {
            width: 110px; height: 60px; background: #e3eafc; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.2em; color: #1a73e8; font-weight: bold; margin-bottom: 8px;
        }
        .company {
            font-size: 1.25em;
            font-weight: 600;
            color: #1a73e8;
            margin-bottom: 2px;
        }
        .company-details {
            font-size: 1em;
            color: #444;
            margin-bottom: 8px;
        }
        .invoice-meta {
            text-align: right;
            font-size: 1em;
            color: #333;
        }
        .invoice-title {
            font-size: 2.1em;
            color: #1a73e8;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .section-title {
            font-weight: 600;
            font-size: 1.08em;
            margin-top: 22px;
            margin-bottom: 8px;
            color: #1a73e8;
            letter-spacing: 0.5px;
        }
        .info-table {
            width: 100%;
            margin-bottom: 18px;
            border-collapse: separate;
            border-spacing: 0;
        }
        .info-table td {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }
        .info-table td:first-child {
            font-weight: 600;
            color: #555;
            width: 120px;
        }
        .items-table {
            width: 100%;
            border-collapse: collapse;
            margin: 24px 0;
        }
        .items-table th {
            background: #f8f9fa;
            padding: 12px 8px;
            text-align: left;
            font-weight: 600;
            color: #555;
            border-bottom: 2px solid #e9ecef;
        }
        .items-table td {
            padding: 12px 8px;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: top;
        }
        .items-table tr:nth-child(even) {
            background: #fafbfc;
        }
        .totals-table {
            width: 100%;
            margin-top: 24px;
        }
        .totals-table td {
            padding: 8px 0;
            border-bottom: 1px solid #f0f0f0;
        }
        .totals-table td:first-child {
            text-align: right;
            font-weight: 600;
            color: #555;
        }
        .totals-table td:last-child {
            text-align: right;
            font-weight: 600;
            color: #1a73e8;
        }
        .total-row {
            font-size: 1.2em;
            font-weight: 700;
            color: #1a73e8;
            border-top: 2px solid #1a73e8 !important;
        }
        .notes {
            margin-top: 32px;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 8px;
            border-left: 4px solid #1a73e8;
        }
        .notes h4 {
            margin: 0 0 8px 0;
            color: #1a73e8;
            font-size: 1em;
        }
        .notes p {
            margin: 0;
            color: #555;
            line-height: 1.5;
        }
        .footer {
            margin-top: 32px;
            text-align: center;
            color: #666;
            font-size: 0.9em;
            border-top: 1px solid #e9ecef;
            padding-top: 16px;
        }
    </style></head><body>';
    
    $html .= '<div class="invoice-box">';
    
    // Header
    $html .= '<div class="header">';
    $html .= '<div>';
    $html .= '<div class="logo">LOGO</div>';
    $html .= '<div class="company">' . htmlspecialchars($data['company']['name']) . '</div>';
    $html .= '<div class="company-details">' . htmlspecialchars($data['company']['address']) . '</div>';
    $html .= '<div class="company-details">Phone: ' . htmlspecialchars($data['company']['phone']) . '</div>';
    $html .= '<div class="company-details">Email: ' . htmlspecialchars($data['company']['email']) . '</div>';
    $html .= '</div>';
    
    $html .= '<div class="invoice-meta">';
    $html .= '<div class="invoice-title">' . strtoupper($data['document']['type']) . '</div>';
    $html .= '<div><strong>Number:</strong> ' . htmlspecialchars($data['document']['number']) . '</div>';
    $html .= '<div><strong>Date:</strong> ' . htmlspecialchars($data['document']['date']) . '</div>';
    if ($data['document']['due_date']) {
        $html .= '<div><strong>Due Date:</strong> ' . htmlspecialchars($data['document']['due_date']) . '</div>';
    }
    $html .= '<div><strong>Status:</strong> ' . htmlspecialchars($data['document']['status']) . '</div>';
    $html .= '</div>';
    $html .= '</div>';
    
    // Client Information
    $html .= '<div class="section-title">Bill To:</div>';
    $html .= '<table class="info-table">';
    $html .= '<tr><td>Name:</td><td>' . htmlspecialchars($data['client']['name']) . '</td></tr>';
    if ($data['client']['email']) {
        $html .= '<tr><td>Email:</td><td>' . htmlspecialchars($data['client']['email']) . '</td></tr>';
    }
    if ($data['client']['phone']) {
        $html .= '<tr><td>Phone:</td><td>' . htmlspecialchars($data['client']['phone']) . '</td></tr>';
    }
    if ($data['client']['address1']) {
        $html .= '<tr><td>Address:</td><td>' . htmlspecialchars($data['client']['address1']);
        if ($data['client']['address2']) {
            $html .= '<br>' . htmlspecialchars($data['client']['address2']);
        }
        $html .= '</td></tr>';
    }
    if ($data['client']['vat_number']) {
        $html .= '<tr><td>VAT Number:</td><td>' . htmlspecialchars($data['client']['vat_number']) . '</td></tr>';
    }
    $html .= '</table>';
    
    // Items Table
    $html .= '<div class="section-title">Items:</div>';
    $html .= '<table class="items-table">';
    $html .= '<thead><tr>';
    $html .= '<th>Description</th>';
    $html .= '<th>Qty</th>';
    $html .= '<th>Unit Price</th>';
    $html .= '<th>Tax</th>';
    $html .= '<th>Total</th>';
    $html .= '</tr></thead><tbody>';
    
    foreach ($data['items'] as $item) {
        $html .= '<tr>';
        $html .= '<td>' . htmlspecialchars($item['description']) . '</td>';
        $html .= '<td>' . htmlspecialchars($item['quantity']) . '</td>';
        $html .= '<td>R' . htmlspecialchars($item['unit_price']) . '</td>';
        $html .= '<td>' . htmlspecialchars($item['tax_rate']) . '%</td>';
        $html .= '<td>R' . htmlspecialchars($item['total']) . '</td>';
        $html .= '</tr>';
    }
    
    $html .= '</tbody></table>';
    
    // Totals
    $html .= '<table class="totals-table">';
    $html .= '<tr><td>Subtotal:</td><td>R' . htmlspecialchars($data['totals']['subtotal']) . '</td></tr>';
    if ($data['totals']['discount_amount'] && $data['totals']['discount_amount'] != '0.00') {
        $html .= '<tr><td>Discount:</td><td>-R' . htmlspecialchars($data['totals']['discount_amount']) . '</td></tr>';
    }
    $html .= '<tr><td>Tax:</td><td>R' . htmlspecialchars($data['totals']['tax_amount']) . '</td></tr>';
    $html .= '<tr class="total-row"><td>Total:</td><td>R' . htmlspecialchars($data['totals']['total_amount']) . '</td></tr>';
    $html .= '</table>';
    
    // Notes
    if ($data['notes']['public'] || $data['notes']['footer']) {
        $html .= '<div class="notes">';
        if ($data['notes']['public']) {
            $html .= '<h4>Notes:</h4><p>' . nl2br(htmlspecialchars($data['notes']['public'])) . '</p>';
        }
        if ($data['notes']['footer']) {
            $html .= '<h4>Terms & Conditions:</h4><p>' . nl2br(htmlspecialchars($data['notes']['footer'])) . '</p>';
        }
        $html .= '</div>';
    }
    
    // Footer
    $html .= '<div class="footer">';
    $html .= 'Thank you for your business!<br>';
    $html .= htmlspecialchars($data['company']['name']) . ' | ' . htmlspecialchars($data['company']['address']);
    $html .= '</div>';
    
    $html .= '</div></body></html>';
    
    return $html;
}

 