<?php
session_start();
require_once('../../../php/db.php');

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
            // Check if specific employee ID is requested
            if (isset($_GET['id'])) {
                $stmt = $conn->prepare("
                    SELECT * FROM employees 
                    WHERE employee_id = :id
                ");
                $stmt->execute([
                    'id' => $_GET['id']
                ]);
                $employee = $stmt->fetch(PDO::FETCH_ASSOC);
                
                if (!$employee) {
                    http_response_code(404);
                    echo json_encode(['error' => 'Employee not found']);
                    exit;
                }
                
                echo json_encode($employee);
            } else {
                // Get all employees with optional status filter
                $status = $_GET['status'] ?? 'active';
                $stmt = $conn->prepare("
                    SELECT e.employee_id, e.employee_number, e.first_name, e.last_name, 
                           e.division, e.group_name, e.department, e.cost_center,
                           p.title as position, e.hire_date, e.email, e.phone_number,
                           e.employment_type, e.work_schedule_type, e.status
                    FROM employees e
                    LEFT JOIN positions p ON e.position_id = p.position_id
                    WHERE (:status = 'all' OR e.status = :status)
                    ORDER BY e.first_name, e.last_name
                ");
                $stmt->execute([
                    'status' => $status
                ]);
                $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
                echo json_encode($employees);
            }
        } catch (PDOException $e) {
            http_response_code(500);
            echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
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
            $required_fields = [
                'employee_number', 'clock_number', 'first_name', 'last_name',
                'department', 'position', 'hire_date', 'email', 'phone',
                'pay_period'
            ];
            
            foreach ($required_fields as $field) {
                if (!isset($data[$field]) || empty($data[$field])) {
                    throw new Exception("Missing required field: $field");
                }
            }

            // Validate email format
            if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
                throw new Exception('Invalid email format');
            }

            // Check if employee number already exists
            $stmt = $conn->prepare("
                SELECT id FROM employees 
                WHERE employee_number = :employee_number 
                AND account_number = :account_number
            ");
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
                    account_number, employee_number, clock_number, first_name, last_name,
                    department, position, hire_date, email, phone,
                    pay_period, period_start_date, period_end_date, period_days,
                    schedule_template_id, status
                ) VALUES (
                    :account_number, :employee_number, :clock_number, :first_name, :last_name,
                    :department, :position, :hire_date, :email, :phone,
                    :pay_period, :period_start_date, :period_end_date, :period_days,
                    :schedule_template_id, 'active'
                )
            ");

            $stmt->execute([
                'account_number' => $account_number,
                'employee_number' => $data['employee_number'],
                'clock_number' => $data['clock_number'],
                'first_name' => $data['first_name'],
                'last_name' => $data['last_name'],
                'department' => $data['department'],
                'position' => $data['position'],
                'hire_date' => $data['hire_date'],
                'email' => $data['email'],
                'phone' => $data['phone'],
                'pay_period' => $data['pay_period'],
                'period_start_date' => $data['period_start_date'] ?? null,
                'period_end_date' => $data['period_end_date'] ?? null,
                'period_days' => $data['period_days'] ?? null,
                'schedule_template_id' => $data['schedule_template_id'] ?? null
            ]);

            $newEmployeeId = $conn->lastInsertId();
            
            // Return the newly created employee
            $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
            $stmt->execute([$newEmployeeId]);
            $newEmployee = $stmt->fetch(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'message' => 'Employee added successfully',
                'employee' => $newEmployee
            ]);
        } catch (Exception $e) {
            http_response_code(400);
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'PUT':
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

            // Verify employee belongs to account
            $stmt = $conn->prepare("
                SELECT id FROM employees 
                WHERE id = :id 
                AND account_number = :account_number
            ");
            $stmt->execute([
                'id' => $data['id'],
                'account_number' => $account_number
            ]);
            if (!$stmt->fetch()) {
                throw new Exception('Employee not found');
            }

            // Build update query dynamically based on provided fields
            $updateFields = [];
            $params = ['id' => $data['id'], 'account_number' => $account_number];
            
            $allowedFields = [
                'employee_number', 'clock_number', 'first_name', 'last_name',
                'department', 'position', 'hire_date', 'email', 'phone',
                'pay_period', 'period_start_date', 'period_end_date', 'period_days',
                'schedule_template_id', 'status'
            ];

            foreach ($allowedFields as $field) {
                if (isset($data[$field])) {
                    $updateFields[] = "$field = :$field";
                    $params[$field] = $data[$field];
                }
            }

            if (empty($updateFields)) {
                throw new Exception('No fields to update');
            }

            $stmt = $conn->prepare("
                UPDATE employees 
                SET " . implode(', ', $updateFields) . "
                WHERE id = :id 
                AND account_number = :account_number
            ");
            
            $stmt->execute($params);

            // Fetch and return updated employee data
            $stmt = $conn->prepare("SELECT * FROM employees WHERE id = ?");
            $stmt->execute([$data['id']]);
            $updatedEmployee = $stmt->fetch(PDO::FETCH_ASSOC);

            echo json_encode([
                'success' => true,
                'message' => 'Employee updated successfully',
                'employee' => $updatedEmployee
            ]);
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

            // Soft delete by updating status to 'terminated'
            $stmt = $conn->prepare("
                UPDATE employees 
                SET status = 'terminated',
                    termination_date = CURRENT_DATE
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

            echo json_encode([
                'success' => true,
                'message' => 'Employee terminated successfully'
            ]);
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