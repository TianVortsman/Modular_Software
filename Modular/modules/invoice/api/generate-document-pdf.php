<?php
// generate_invoice_pdf.php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../../../vendor/autoload.php';
require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';
use App\Core\Database\ClientDatabase;

use Mpdf\Mpdf;

header('Content-Type: application/json');

try {
    // Debug: Check if autoload.php exists and is readable
    $autoloadPath = __DIR__ . '/../../../vendor/autoload.php';
    error_log('[INVOICE PDF DEBUG] Checking autoload.php at: ' . $autoloadPath);
    if (!file_exists($autoloadPath)) {
        error_log('[INVOICE PDF DEBUG] autoload.php does NOT exist!');
    } else if (!is_readable($autoloadPath)) {
        error_log('[INVOICE PDF DEBUG] autoload.php is NOT readable!');
    } else {
        error_log('[INVOICE PDF DEBUG] autoload.php exists and is readable.');
    }
    // Debug: List vendor directory
    $vendorDir = __DIR__ . '/../../../vendor';
    if (is_dir($vendorDir)) {
        $files = scandir($vendorDir);
        error_log('[INVOICE PDF DEBUG] vendor dir: ' . implode(', ', $files));
    } else {
        error_log('[INVOICE PDF DEBUG] vendor dir does NOT exist!');
    }
    // Debug: Try to include autoload.php and log result
    $includeResult = @include_once $autoloadPath;
    error_log('[INVOICE PDF DEBUG] include_once result: ' . var_export($includeResult, true));

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

    // If preview mode, use sample data
    if (!empty($data['preview'])) {
        $data = [
            'client' => [
                'name' => 'Sample Client',
                'email' => 'client@example.com',
                'phone' => '+1 555-1234'
            ],
            'items' => [
                [ 'qty' => 2, 'item_code' => 'PRD-001', 'description' => 'Sample Product A', 'unit_price' => 'R500.00', 'tax' => '15%', 'total' => 'R1150.00' ],
                [ 'qty' => 1, 'item_code' => 'PRD-002', 'description' => 'Sample Product B', 'unit_price' => 'R300.00', 'tax' => '15%', 'total' => 'R345.00' ]
            ],
            'totals' => [
                'subtotal' => 'R800.00',
                'tax' => 'R120.00',
                'total' => 'R920.00'
            ],
            'notes' => ['This is a sample invoice preview.'],
            'invoice_number' => 'PREVIEW-0001'
        ];
    }

    // Validate required fields (client, items, totals, etc.)
    if (empty($data['client']) || empty($data['items']) || empty($data['totals'])) {
        throw new Exception('Missing required invoice data');
    }

    // Fetch all settings in one query
    $db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
    $pdo = $db->connect();
    $stmt = $pdo->prepare('SELECT * FROM settings.invoice_settings WHERE id = 1');
    $stmt->execute();
    $settings = $stmt->fetch(PDO::FETCH_ASSOC);
    $data['company'] = [
        'name' => $settings['company_name'] ?? '',
        'address' => $settings['company_address'] ?? '',
        'phone' => $settings['company_phone'] ?? '',
        'email' => $settings['company_email'] ?? '',
        'vat_number' => $settings['vat_number'] ?? '',
        'registration_number' => $settings['registration_number'] ?? ''
    ];
    $data['bank'] = [
        'bank_name' => $settings['bank_name'] ?? '',
        'bank_branch' => $settings['bank_branch'] ?? '',
        'account_number' => $settings['account_number'] ?? '',
        'swift_code' => $settings['swift_code'] ?? ''
    ];
    $template = $data['template'] ?? ($settings['template_name'] ?? 'modern-blue');

    $html = '';
    switch ($template) {
        case 'modern-blue':
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
                    border-spacing: 0 2px;
                }
                .info-table td {
                    padding: 2px 8px 2px 0;
                    vertical-align: top;
                    font-size: 1em;
                }
                .items-table {
                    width: 100%;
                    border-collapse: separate;
                    border-spacing: 0;
                    margin-bottom: 24px;
                    font-size: 1em;
                }
                .items-table th {
                    background: #e3eafc;
                    color: #1a73e8;
                    font-weight: 600;
                    border: none;
                    padding: 12px 8px;
                    text-align: left;
                    font-size: 1.05em;
                }
                .items-table td {
                    border: none;
                    padding: 10px 8px;
                    background: #fff;
                    border-bottom: 1px solid #f0f3fa;
                }
                .items-table tr:nth-child(even) td {
                    background: #f7faff;
                }
                .totals {
                    width: 320px;
                    float: right;
                    margin-top: 18px;
                    font-size: 1.08em;
                }
                .totals td {
                    padding: 8px 0;
                }
                .totals .label {
                    text-align: left;
                    color: #555;
                }
                .totals .value {
                    text-align: right;
                    font-weight: bold;
                }
                .notes {
                    margin-top: 36px;
                    font-size: 1em;
                    color: #666;
                }
                .hr {
                    border-top: 1.5px solid #e3eafc;
                    margin: 36px 0 18px 0;
                }
                .bank-details {
                    margin-top: 18px;
                    padding: 16px 18px;
                    background: #f7faff;
                    border-radius: 8px;
                    font-size: 1em;
                    color: #222;
                }
                .footer {
                    margin-top: 48px;
                    text-align: center;
                    color: #aaa;
                    font-size: 1em;
                }
                @media print {
                    .invoice-box { box-shadow: none; border: 1px solid #e3eafc; }
                    .footer { color: #888; }
                }
                /* Prevent page break inside main invoice box */
                .invoice-box, .header, .totals, .bank-details, .footer { page-break-inside: avoid; }
            </style></head><body>';
            $html .= '<div class="invoice-box">';
            $html .= '<div class="header">';
            // Logo placeholder (replace with <img src="..."> if you want a logo)
            $html .= '<div style="flex:1 1 0;min-width:220px;">';
            $html .= '<div class="logo">LOGO</div>'; // <-- LOGO PLACEHOLDER
            $html .= '<div class="company">' . htmlspecialchars($data['company']['name']) . '</div>';
            $html .= '<div class="company-details">' . htmlspecialchars($data['company']['address']) . '<br>';
            $html .= 'Phone: ' . htmlspecialchars($data['company']['phone']) . '<br>';
            $html .= 'Email: ' . htmlspecialchars($data['company']['email']) . '<br>';
            $html .= 'VAT: ' . htmlspecialchars($data['company']['vat_number']) . ' | Reg: ' . htmlspecialchars($data['company']['registration_number']) . '</div>';
            $html .= '</div>';
            $html .= '<div class="invoice-meta" style="flex:0 0 220px;">';
            $html .= '<div class="invoice-title">INVOICE</div>';
            $html .= '<b>Invoice #:</b> ' . (isset($data['invoice_number']) ? htmlspecialchars($data['invoice_number']) : 'Auto') . '<br>';
            $html .= '<b>Date:</b> ' . (isset($data['invoice_date']) ? htmlspecialchars($data['invoice_date']) : date('Y-m-d')) . '<br>';
            $html .= '<b>Salesperson:</b> ' . htmlspecialchars($data['salesperson']['name'] ?? '-') . '<br>';
            if (!empty($data['salesperson']['email'])) $html .= '<span style="font-size:0.95em;color:#555">' . htmlspecialchars($data['salesperson']['email']) . '</span><br>';
            $html .= '</div>';
            $html .= '</div>';
            $html .= '<div class="section-title">Billed To</div>';
            $html .= '<table class="info-table"><tr>';
            $html .= '<td>' . htmlspecialchars($data['client']['name']) . '<br>';
            $html .= htmlspecialchars($data['client']['address1'] ?? '') . '<br>';
            $html .= htmlspecialchars($data['client']['address2'] ?? '') . '<br>';
            $html .= 'Email: ' . htmlspecialchars($data['client']['email']) . '<br>';
            $html .= 'Phone: ' . htmlspecialchars($data['client']['phone']) . '<br>';
            $html .= 'VAT: ' . htmlspecialchars($data['client']['vat_number']) . ' | Reg: ' . htmlspecialchars($data['client']['registration_number']) . '</td>';
            $html .= '</tr></table>';
            $html .= '<div class="section-title">Invoice Items</div>';
            $html .= '<table class="items-table"><thead><tr>';
            $html .= '<th>Qty</th><th>Item Code</th><th>Description</th><th>Unit Price</th><th>Tax</th><th>Total</th>';
            $html .= '</tr></thead><tbody>';
            foreach ($data['items'] as $item) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($item['qty']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['item_code']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['description']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['unit_price']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['tax']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['total']) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
            $html .= '<table class="totals">';
            $html .= '<tr><td class="label">Subtotal:</td><td class="value">' . htmlspecialchars($data['totals']['subtotal']) . '</td></tr>';
            $html .= '<tr><td class="label">Tax:</td><td class="value">' . htmlspecialchars($data['totals']['tax']) . '</td></tr>';
            $html .= '<tr><td class="label">Total:</td><td class="value">' . htmlspecialchars($data['totals']['total']) . '</td></tr>';
            $html .= '</table>';
            if (!empty($data['notes'])) {
                $html .= '<div class="notes"><b>Notes:</b><br>';
                foreach ($data['notes'] as $note) {
                    if (trim($note)) $html .= htmlspecialchars($note) . '<br>';
                }
                $html .= '</div>';
            }
            $html .= '<div class="hr"></div>';
            $html .= '<div class="section-title">Banking Details</div>';
            $html .= '<div class="bank-details">';
            $html .= '<b>Bank Name:</b> ' . htmlspecialchars($data['bank']['bank_name']) . '<br>';
            $html .= '<b>Branch:</b> ' . htmlspecialchars($data['bank']['bank_branch']) . '<br>';
            $html .= '<b>Account Number:</b> ' . htmlspecialchars($data['bank']['account_number']) . '<br>';
            $html .= '<b>SWIFT Code:</b> ' . htmlspecialchars($data['bank']['swift_code']) . '<br>';
            $html .= '</div>';
            $html .= '<div class="footer">Thank you for your business!</div>';
            $html .= '</div></body></html>';
            break;
        case 'classic-grey':
            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
            $html .= '<style>body{font-family:Georgia,serif;color:#222;background:#f4f4f4;margin:0;padding:0}.invoice-box{max-width:800px;margin:30px auto;padding:30px;border:1px solid #bbb;box-shadow:0 0 10px #ccc;background:#fff;border-radius:8px}.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}.company{font-size:1.5em;font-weight:bold;color:#444}.invoice-title{font-size:2em;color:#444;font-weight:700}.info-table{width:100%;margin-bottom:30px}.info-table td{padding:4px 8px}.items-table{width:100%;border-collapse:collapse;margin-bottom:30px}.items-table th,.items-table td{border:1px solid #bbb;padding:10px;text-align:left}.items-table th{background:#eee;color:#333;font-weight:600}.items-table tr:nth-child(even){background:#f9f9f9}.totals{float:right;width:300px;margin-top:20px}.totals td{padding:8px}.totals .label{text-align:left;color:#555}.totals .value{text-align:right;font-weight:bold}.notes{margin-top:40px;font-size:.95em;color:#666}.footer{margin-top:60px;text-align:center;color:#aaa;font-size:.9em}</style></head><body>';
            $html .= '<div class="invoice-box">';
            $html .= '<div class="header">';
            $html .= '<div class="company">Your Company Name</div>';
            $html .= '<div class="invoice-title">INVOICE</div>';
            $html .= '</div>';
            $html .= '<table class="info-table"><tr>';
            $html .= '<td><b>Billed To:</b><br>' . htmlspecialchars($data['client']['name']) . '<br>';
            if (!empty($data['client']['email'])) $html .= htmlspecialchars($data['client']['email']) . '<br>';
            if (!empty($data['client']['phone'])) $html .= htmlspecialchars($data['client']['phone']) . '<br>';
            $html .= '</td>';
            $html .= '<td style="text-align:right;"><b>Date:</b> ' . date('Y-m-d') . '<br>';
            $html .= '<b>Invoice #:</b> ' . (isset($data['invoice_number']) ? htmlspecialchars($data['invoice_number']) : 'Auto') . '<br>';
            $html .= '</td></tr></table>';
            $html .= '<table class="items-table"><thead><tr>';
            $html .= '<th>Qty</th><th>Item Code</th><th>Description</th><th>Unit Price</th><th>Tax</th><th>Total</th>';
            $html .= '</tr></thead><tbody>';
            foreach ($data['items'] as $item) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($item['qty']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['item_code']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['description']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['unit_price']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['tax']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['total']) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
            $html .= '<table class="totals">';
            $html .= '<tr><td class="label">Subtotal:</td><td class="value">' . htmlspecialchars($data['totals']['subtotal']) . '</td></tr>';
            $html .= '<tr><td class="label">Tax:</td><td class="value">' . htmlspecialchars($data['totals']['tax']) . '</td></tr>';
            $html .= '<tr><td class="label">Total:</td><td class="value">' . htmlspecialchars($data['totals']['total']) . '</td></tr>';
            $html .= '</table>';
            if (!empty($data['notes'])) {
                $html .= '<div class="notes"><b>Notes:</b><br>';
                foreach ($data['notes'] as $note) {
                    if (trim($note)) $html .= htmlspecialchars($note) . '<br>';
                }
                $html .= '</div>';
            }
            $html .= '<div class="footer">Thank you for your business!</div>';
            $html .= '</div></body></html>';
            break;
        case 'elegant-dark':
            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
            $html .= '<style>body{font-family:Montserrat,Arial,sans-serif;color:#eee;background:#23272b;margin:0;padding:0}.invoice-box{max-width:800px;margin:30px auto;padding:30px;border:1px solid #444;box-shadow:0 0 10px #111;background:#2c3136;border-radius:8px}.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}.company{font-size:1.5em;font-weight:bold;color:#f7c873}.invoice-title{font-size:2em;color:#f7c873;font-weight:700}.info-table{width:100%;margin-bottom:30px}.info-table td{padding:4px 8px;color:#eee}.items-table{width:100%;border-collapse:collapse;margin-bottom:30px}.items-table th,.items-table td{border:1px solid #444;padding:10px;text-align:left}.items-table th{background:#23272b;color:#f7c873;font-weight:600}.items-table tr:nth-child(even){background:#23272b}.totals{float:right;width:300px;margin-top:20px}.totals td{padding:8px;color:#eee}.totals .label{text-align:left;color:#f7c873}.totals .value{text-align:right;font-weight:bold}.notes{margin-top:40px;font-size:.95em;color:#f7c873}.footer{margin-top:60px;text-align:center;color:#888;font-size:.9em}</style></head><body>';
            $html .= '<div class="invoice-box">';
            $html .= '<div class="header">';
            $html .= '<div class="company">Your Company Name</div>';
            $html .= '<div class="invoice-title">INVOICE</div>';
            $html .= '</div>';
            $html .= '<table class="info-table"><tr>';
            $html .= '<td><b>Billed To:</b><br>' . htmlspecialchars($data['client']['name']) . '<br>';
            if (!empty($data['client']['email'])) $html .= htmlspecialchars($data['client']['email']) . '<br>';
            if (!empty($data['client']['phone'])) $html .= htmlspecialchars($data['client']['phone']) . '<br>';
            $html .= '</td>';
            $html .= '<td style="text-align:right;"><b>Date:</b> ' . date('Y-m-d') . '<br>';
            $html .= '<b>Invoice #:</b> ' . (isset($data['invoice_number']) ? htmlspecialchars($data['invoice_number']) : 'Auto') . '<br>';
            $html .= '</td></tr></table>';
            $html .= '<table class="items-table"><thead><tr>';
            $html .= '<th>Qty</th><th>Item Code</th><th>Description</th><th>Unit Price</th><th>Tax</th><th>Total</th>';
            $html .= '</tr></thead><tbody>';
            foreach ($data['items'] as $item) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($item['qty']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['item_code']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['description']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['unit_price']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['tax']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['total']) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
            $html .= '<table class="totals">';
            $html .= '<tr><td class="label">Subtotal:</td><td class="value">' . htmlspecialchars($data['totals']['subtotal']) . '</td></tr>';
            $html .= '<tr><td class="label">Tax:</td><td class="value">' . htmlspecialchars($data['totals']['tax']) . '</td></tr>';
            $html .= '<tr><td class="label">Total:</td><td class="value">' . htmlspecialchars($data['totals']['total']) . '</td></tr>';
            $html .= '</table>';
            if (!empty($data['notes'])) {
                $html .= '<div class="notes"><b>Notes:</b><br>';
                foreach ($data['notes'] as $note) {
                    if (trim($note)) $html .= htmlspecialchars($note) . '<br>';
                }
                $html .= '</div>';
            }
            $html .= '<div class="footer">Thank you for your business!</div>';
            $html .= '</div></body></html>';
            break;
        case 'modern-clean':
            $html = '<!DOCTYPE html><html lang="en"><head><meta charset="UTF-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0"/><title>Invoice</title><style>body{font-family:\'Segoe UI\',sans-serif;margin:0;padding:2rem;background:#f8f9fa;color:#333}.invoice-box{background:#fff;padding:2rem;border:1px solid #dee2e6;max-width:800px;margin:auto;border-radius:8px;box-shadow:0 0 10px rgba(0,0,0,0.05)}h1{font-size:24px;margin-bottom:1rem}.company-details,.client-details{margin-bottom:1rem}.invoice-details{text-align:right}table{width:100%;border-collapse:collapse;margin-top:1rem}th,td{border:1px solid #dee2e6;padding:0.75rem;text-align:left}th{background-color:#f1f3f5}.totals{text-align:right;margin-top:1rem}.totals td{border:none;padding:0.5rem 0}</style></head><body><div class="invoice-box">';
            $html .= '<h1>Invoice</h1>';
            $html .= '<div class="company-details"><strong>' . htmlspecialchars($data['company']['name'] ?? 'Your Company') . '</strong><br>';
            $html .= htmlspecialchars($data['company']['address'] ?? '') . '<br>';
            $html .= htmlspecialchars($data['company']['email'] ?? '') . ' | ' . htmlspecialchars($data['company']['phone'] ?? '') . '</div>';
            $html .= '<div class="client-details"><strong>Billed To:</strong><br>';
            $html .= htmlspecialchars($data['client']['name'] ?? '') . '<br>';
            $html .= htmlspecialchars($data['client']['address'] ?? '') . '<br>';
            $html .= htmlspecialchars($data['client']['email'] ?? '') . '</div>';
            $html .= '<div class="invoice-details">Invoice #: <strong>' . htmlspecialchars($data['invoice_number'] ?? 'Auto') . '</strong><br>';
            $html .= 'Date: ' . (isset($data['invoice_date']) ? htmlspecialchars($data['invoice_date']) : date('Y-m-d')) . '<br>';
            $html .= 'Due: ' . (isset($data['due_date']) ? htmlspecialchars($data['due_date']) : '-') . '</div>';
            $html .= '<table><thead><tr><th>Description</th><th>Qty</th><th>Unit Price</th><th>Total</th></tr></thead><tbody>';
            foreach ($data['items'] as $item) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($item['description']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['qty']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['unit_price']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['total']) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
            $html .= '<table class="totals">';
            $html .= '<tr><td>Subtotal:</td><td><strong>' . htmlspecialchars($data['totals']['subtotal'] ?? '') . '</strong></td></tr>';
            $html .= '<tr><td>VAT (' . htmlspecialchars($data['totals']['vat_percent'] ?? '15') . '%):</td><td>' . htmlspecialchars($data['totals']['tax'] ?? '') . '</td></tr>';
            $html .= '<tr><td>Total:</td><td><strong>' . htmlspecialchars($data['totals']['total'] ?? '') . '</strong></td></tr>';
            $html .= '</table>';
            $html .= '</div></body></html>';
            break;
        default:
            // Modern default (as before)
            $html = '<!DOCTYPE html><html><head><meta charset="UTF-8">';
            $html .= '<style>body{font-family:Segoe UI,Arial,sans-serif;color:#222;background:#fff;margin:0;padding:0}.invoice-box{max-width:800px;margin:30px auto;padding:30px;border:1px solid #eee;box-shadow:0 0 10px #eee;background:#fff;border-radius:8px}.header{display:flex;justify-content:space-between;align-items:center;margin-bottom:30px}.company{font-size:1.5em;font-weight:bold;color:#2a3f54}.invoice-title{font-size:2em;color:#1a73e8;font-weight:700}.info-table{width:100%;margin-bottom:30px}.info-table td{padding:4px 8px}.items-table{width:100%;border-collapse:collapse;margin-bottom:30px}.items-table th,.items-table td{border:1px solid #eee;padding:10px;text-align:left}.items-table th{background:#f5f7fa;color:#333;font-weight:600}.items-table tr:nth-child(even){background:#fafbfc}.totals{float:right;width:300px;margin-top:20px}.totals td{padding:8px}.totals .label{text-align:left;color:#555}.totals .value{text-align:right;font-weight:bold}.notes{margin-top:40px;font-size:.95em;color:#666}.footer{margin-top:60px;text-align:center;color:#aaa;font-size:.9em}</style></head><body>';
            $html .= '<div class="invoice-box">';
            $html .= '<div class="header">';
            $html .= '<div class="company">Your Company Name</div>';
            $html .= '<div class="invoice-title">INVOICE</div>';
            $html .= '</div>';
            $html .= '<table class="info-table"><tr>';
            $html .= '<td><b>Billed To:</b><br>' . htmlspecialchars($data['client']['name']) . '<br>';
            if (!empty($data['client']['email'])) $html .= htmlspecialchars($data['client']['email']) . '<br>';
            if (!empty($data['client']['phone'])) $html .= htmlspecialchars($data['client']['phone']) . '<br>';
            $html .= '</td>';
            $html .= '<td style="text-align:right;"><b>Date:</b> ' . date('Y-m-d') . '<br>';
            $html .= '<b>Invoice #:</b> ' . (isset($data['invoice_number']) ? htmlspecialchars($data['invoice_number']) : 'Auto') . '<br>';
            $html .= '</td></tr></table>';
            $html .= '<table class="items-table"><thead><tr>';
            $html .= '<th>Qty</th><th>Item Code</th><th>Description</th><th>Unit Price</th><th>Tax</th><th>Total</th>';
            $html .= '</tr></thead><tbody>';
            foreach ($data['items'] as $item) {
                $html .= '<tr>';
                $html .= '<td>' . htmlspecialchars($item['qty']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['item_code']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['description']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['unit_price']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['tax']) . '</td>';
                $html .= '<td>' . htmlspecialchars($item['total']) . '</td>';
                $html .= '</tr>';
            }
            $html .= '</tbody></table>';
            $html .= '<table class="totals">';
            $html .= '<tr><td class="label">Subtotal:</td><td class="value">' . htmlspecialchars($data['totals']['subtotal']) . '</td></tr>';
            $html .= '<tr><td class="label">Tax:</td><td class="value">' . htmlspecialchars($data['totals']['tax']) . '</td></tr>';
            $html .= '<tr><td class="label">Total:</td><td class="value">' . htmlspecialchars($data['totals']['total']) . '</td></tr>';
            $html .= '</table>';
            if (!empty($data['notes'])) {
                $html .= '<div class="notes"><b>Notes:</b><br>';
                foreach ($data['notes'] as $note) {
                    if (trim($note)) $html .= htmlspecialchars($note) . '<br>';
                }
                $html .= '</div>';
            }
            $html .= '<div class="footer">Thank you for your business!</div>';
            $html .= '</div></body></html>';
            break;
    }

    // Generate PDF
    $mpdf = new Mpdf();
    $mpdf->WriteHTML($html);

    // Add watermark or note for preview
    if (!empty($_POST['preview']) || !empty($data['preview'])) {
        $mpdf->SetWatermarkText('PREVIEW', 0.1);
        $mpdf->showWatermarkText = true;
    }

    // Save PDF to a file (e.g., in /Uploads/invoices/)
    $account = $_SESSION['account_number'];
    $invoicesDir = __DIR__ . "/../../../Uploads/$account/invoices/";
    if (!is_dir($invoicesDir)) {
        if (!mkdir($invoicesDir, 0777, true)) {
            error_log('[INVOICE PDF ERROR] Failed to create invoices directory: ' . $invoicesDir);
            throw new Exception('Failed to create invoices directory.');
        }
    }
    if (!is_writable($invoicesDir)) {
        error_log('[INVOICE PDF ERROR] Invoices directory is not writable: ' . $invoicesDir);
        throw new Exception('Invoices directory is not writable.');
    }
    $filename = 'invoice_' . time() . '_' . rand(1000,9999) . '.pdf';
    $filepath = $invoicesDir . $filename;
    $mpdf->Output($filepath, \Mpdf\Output\Destination::FILE);

    // Debug: Log file path and check existence
    error_log('[INVOICE PDF DEBUG] PDF file path: ' . $filepath);
    if (!file_exists($filepath) || !is_readable($filepath)) {
        error_log('[INVOICE PDF ERROR] PDF file was not created or is not readable: ' . $filepath);
        throw new Exception('PDF file was not created. Please check server permissions and disk space.');
    }

    // Return the URL to the PDF
    $url = "/Uploads/$account/invoices/$filename";
    echo json_encode([
        'success' => true,
        'message' => 'Invoice PDF generated successfully',
        'url' => $url
    ]);
} catch (Exception $e) {
    error_log('[INVOICE PDF ERROR] ' . $e->getMessage() . "\n" . $e->getTraceAsString());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage(),
        'url' => null
    ]);
} 