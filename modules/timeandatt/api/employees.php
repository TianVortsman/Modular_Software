<?php
session_start();

if (!isset($_SESSION['account_number'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Unauthorized']);
    exit;
}

// Include database connection
require_once '../../../php/db.php';

// Set response header to JSON
header('Content-Type: application/json');

// Handle GET request to fetch employees
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        // Check if specific employee ID is requested
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("
                SELECT * FROM employees 
                WHERE employee_id = :id AND deleted_at IS NULL
            ");
            $stmt->execute(['id' => $_GET['id']]);
            $employee = $stmt->fetch(PDO::FETCH_ASSOC);
            
            if (!$employee) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Employee not found'
                ]);
                exit;
            }
            
            echo json_encode([
                'success' => true,
                'data' => $employee
            ]);
            exit;
        } else {
            // Get all employees with optional status filter
            $status = $_GET['status'] ?? 'active';
            $stmt = $conn->prepare("
                SELECT e.employee_id, e.employee_number, e.first_name, e.last_name, 
                       e.division, e.department, e.position, e.hire_date, 
                       e.employment_type, e.status
                FROM employees e
                WHERE e.deleted_at IS NULL
                AND (:status = 'all' OR e.status = :status)
                ORDER BY 
                    -- Extract numeric part and cast to integer for proper numeric sorting
                    CAST(REGEXP_REPLACE(e.employee_number, '[^0-9]', '', 'g') AS INTEGER),
                    e.employee_number
            ");
            $stmt->execute(['status' => $status]);
            $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            echo json_encode([
                'success' => true,
                'data' => $employees
            ]);
            exit;
        }
    } catch (PDOException $e) {
        error_log("Database Error: " . $e->getMessage());
        echo json_encode([
            'success' => false,
            'message' => 'Failed to fetch employees',
            'error' => $e->getMessage()
        ]);
        exit;
    }
}

// Handle POST request to create employee
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get JSON data
    $data = json_decode(file_get_contents('php://input'), true);
    
    // Verify CSRF token
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
        exit;
    }
    
    // Validate required fields
    $required_fields = ['employee_number', 'clock_number', 'first_name', 'last_name'];
    $errors = [];
    
    foreach ($required_fields as $field) {
        if (!isset($data[$field]) || empty(trim($data[$field]))) {
            $errors[] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
        }
    }
    
    if (!empty($errors)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Validation failed', 'errors' => $errors]);
        exit;
    }
    
    try {
        // Begin transaction
        $conn->beginTransaction();
        
        // Check for existing employee number first
        $checkStmt = $conn->prepare("SELECT COUNT(*) FROM employees WHERE employee_number = ?");
        $checkStmt->execute([$data['employee_number']]);
        if ($checkStmt->fetchColumn() > 0) {
            throw new PDOException('Employee number already exists', '23505');
        }
        
        // Insert employee
        $stmt = $conn->prepare("
            INSERT INTO employees (
                employee_number,
                clock_number,
                first_name,
                last_name,
                status,
                created_at,
                updated_at
            ) VALUES (?, ?, ?, ?, 'active', NOW(), NOW())
        ");
        
        $stmt->execute([
            $data['employee_number'],
            $data['clock_number'],
            $data['first_name'],
            $data['last_name']
        ]);
        
        // Commit transaction
        $conn->commit();
        
        echo json_encode([
            'success' => true,
            'message' => 'Employee added successfully',
            'employee_id' => $conn->lastInsertId()
        ]);
        exit;
        
    } catch (PDOException $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        // Check for duplicate entry
        if ($e->getCode() == '23505' || $e->getMessage() === 'Employee number already exists') {
            http_response_code(400);
            echo json_encode([
                'success' => false,
                'message' => 'Employee number already exists'
            ]);
        } else {
            error_log("Database Error: " . $e->getMessage());
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'message' => 'Failed to add employee'
            ]);
        }
        exit;
    }
}

// Handle different HTTP methods
switch ($_SERVER['REQUEST_METHOD']) {
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
                'account_number' => $_SESSION['account_number']
            ]);
            if (!$stmt->fetch()) {
                throw new Exception('Employee not found');
            }

            // Build update query dynamically based on provided fields
            $updateFields = [];
            $params = ['id' => $data['id'], 'account_number' => $_SESSION['account_number']];
            
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
                'account_number' => $_SESSION['account_number']
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