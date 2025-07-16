<?php
header('Content-Type: application/json');
$action = isset($_GET['action']) ? $_GET['action'] : '';

switch ($action) {
    case 'get_dashboard_cards':
        echo json_encode([
            'success' => true,
            'total_invoices' => 42,
            'total_revenue' => 123456.78,
            'unpaid_invoices' => 5,
            'pending_payments' => 23456.78,
            'expenses_this_month' => 3456.78,
            'taxes_due' => 1234.56,
            'recurring_invoices' => 3
        ]);
        break;
    case 'get_recent_invoices':
        echo json_encode([
            'success' => true,
            'data' => [
                [
                    'document_id' => 1,
                    'invoice_number' => 'INV-001',
                    'client_name' => 'Acme Corp',
                    'invoice_date' => '2024-06-01',
                    'status_name' => 'Draft',
                    'total_amount' => 1000.00,
                    'due_date' => '2024-06-15'
                ],
                [
                    'document_id' => 2,
                    'invoice_number' => 'INV-002',
                    'client_name' => 'Beta LLC',
                    'invoice_date' => '2024-06-02',
                    'status_name' => 'Paid',
                    'total_amount' => 2000.00,
                    'due_date' => '2024-06-16'
                ]
            ]
        ]);
        break;
    case 'get_recurring_invoices':
        echo json_encode([
            [
                'invoice_number' => 'REC-001',
                'company_id' => 1,
                'start_date' => '2024-05-01',
                'next_generation' => '2024-07-01',
                'frequency' => 'Monthly'
            ],
            [
                'invoice_number' => 'REC-002',
                'company_id' => 2,
                'start_date' => '2024-05-15',
                'next_generation' => '2024-07-15',
                'frequency' => 'Monthly'
            ]
        ]);
        break;
    case 'get_invoice_chart_data':
        echo json_encode([
            ['month' => '2024-02', 'paid' => 5, 'unpaid' => 2, 'recurring' => 1],
            ['month' => '2024-03', 'paid' => 7, 'unpaid' => 1, 'recurring' => 2],
            ['month' => '2024-04', 'paid' => 6, 'unpaid' => 3, 'recurring' => 2],
            ['month' => '2024-05', 'paid' => 8, 'unpaid' => 0, 'recurring' => 3],
            ['month' => '2024-06', 'paid' => 4, 'unpaid' => 2, 'recurring' => 1]
        ]);
        break;
    default:
        echo json_encode(['success' => false, 'message' => 'Unknown action']);
        break;
} 