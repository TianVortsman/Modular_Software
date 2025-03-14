<?php
session_start();
require_once('../../../php/db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Get account number from session
$account_number = $_SESSION['account_number'] ?? null;
if (!$account_number) {
    http_response_code(400);
    echo json_encode(['error' => 'Account number not found']);
    exit;
}

// Handle different HTTP methods
switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        try {
            $stmt = $conn->prepare("
                SELECT id, employee_number, badge_number, first_name, last_name, 
                       pay_period, period_start_date, period_end_date, period_days,
                       schedule_template_id, status
                FROM employees 
                WHERE account_number = :account_number 
                AND status = 'active'
                ORDER BY first_name, last_name
            ");
            $stmt->execute(['account_number' => $account_number]);
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode($employees);
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error']);
        }
        break;

    case 'POST':
        // Validate CSRF token
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid CSRF token']);
            exit;
        }

        try {
            // Validate required fields
            $required_fields = ['employee_number', 'badge_number', 'first_name', 'last_name', 'pay_period'];
            foreach ($required_fields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            // Validate pay period
            $valid_pay_periods = ['weekly', 'biweekly', 'monthly', 'custom'];
            if (!in_array($data['pay_period'], $valid_pay_periods)) {
                throw new Exception('Invalid pay period');
            }

            // Validate custom period details if pay period is custom
            if ($data['pay_period'] === 'custom') {
                if (empty($data['period_start_date']) || empty($data['period_end_date']) || empty($data['period_days'])) {
                    throw new Exception('Custom period details are required');
                }
            }

            // Check if employee number already exists
            $stmt = $conn->prepare("SELECT id FROM employees WHERE employee_number = :employee_number AND account_number = :account_number");
            $stmt->execute([
                'employee_number' => $data['employee_number'],
                'account_number' => $account_number
            ]);
            if ($stmt->fetch()) {
                throw new Exception('Employee number already exists');
            }

            // Insert new employee
            $stmt = $conn->prepare("
                INSERT INTO employees (
                    account_number, employee_number, badge_number, first_name, last_name,
                    pay_period, period_start_date, period_end_date, period_days,
                    schedule_template_id, status
                ) VALUES (
                    :account_number, :employee_number, :badge_number, :first_name, :last_name,
                    :pay_period, :period_start_date, :period_end_date, :period_days,
                    :schedule_template_id, 'active'
                )
            ");

            $stmt->execute([
                'account_number' => $account_number,
                'employee_number' => $data['employee_number'],
                'badge_number' => $data['badge_number'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'pay_period' => $data['pay_period'],
                'period_start_date' => $data['period_start_date'] ?? null,
                'period_end_date' => $data['period_end_date'] ?? null,
                'period_days' => $data['period_days'] ?? null,
                'schedule_template_id' => $data['schedule_template_id'] ?? null
            ]);

            echo json_encode(['success' => true, 'message' => 'Employee added successfully']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'DELETE':
        // Validate CSRF token
        $data = json_decode(file_get_contents('php://input'), true);
        if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
            http_response_code(403);
            echo json_encode(['error' => 'Invalid CSRF token']);
            exit;
        }

        try {
            if (!isset($data['id'])) {
                throw new Exception('Employee ID is required');
            }

            $stmt = $conn->prepare("
                UPDATE employees 
                SET status = 'inactive' 
                WHERE id = :id 
                AND account_number = :account_number
            ");
            $stmt->execute([
                'id' => $data['id'],
                'account_number' => $account_number
            ]);

            if ($stmt->rowCount() === 0) {
                throw new Exception('Employee not found');
            }

            echo json_encode(['success' => true, 'message' => 'Employee deleted successfully']);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
} 