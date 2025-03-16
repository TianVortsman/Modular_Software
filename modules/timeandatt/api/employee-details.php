<?php
// Start the session
session_start();

// Set error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Set headers
header('Content-Type: application/json');

// Check session variables first
if (!isset($_SESSION['account_number'])) {
    error_log('Account number not set in session');
    echo json_encode([
        'success' => false,
        'message' => 'Session error: Account number not set'
    ]);
    exit;
}

if (!isset($_SESSION['user_logged_in']) && !isset($_SESSION['tech_logged_in'])) {
    error_log('User not logged in');
    echo json_encode([
        'success' => false,
        'message' => 'Authentication error: Please log in'
    ]);
    exit;
}

// Include database configuration
try {
    require_once '../../../php/db.php';
} catch (Exception $e) {
    error_log('Failed to include database configuration: ' . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Database configuration error'
    ]);
    exit;
}

// Check if database connection is established
if (!isset($conn)) {
    error_log('Database connection not established after including db.php');
    echo json_encode([
        'success' => false,
        'message' => 'Database connection error'
    ]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['id'])) {
    try {
        // Use the existing connection
        $db = $conn;
        
        // Log the query attempt
        error_log('Attempting to fetch employee data for ID: ' . $_GET['id']);

        // Prepare the main employee query
        $stmt = $db->prepare("
            SELECT 
                e.employee_id,
                e.first_name,
                e.last_name,
                e.email,
                e.phone_number,
                e.hire_date,
                e.division,
                e.group_name,
                e.department,
                e.cost_center,
                e.position,
                e.employee_number,
                e.status,
                e.employment_type,
                e.work_schedule_type,
                e.biometric_id,
                e.emergency_contact_name,
                e.emergency_contact_phone,
                e.address,
                e.created_at,
                e.updated_at,
                e.clock_number
            FROM employees e
            WHERE e.employee_id = :id
            AND e.deleted_at IS NULL
        ");
        
        $stmt->execute(['id' => $_GET['id']]); 
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($employee) {
            try {
                // Get leave balances
                $leaveStmt = $db->prepare("
                    SELECT leave_type, balance
                    FROM employee_leave_balances
                    WHERE employee_id = :id
                ");
                $leaveStmt->execute(['id' => $_GET['id']]);
                $employee['leave_balances'] = $leaveStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log('Error fetching leave balances: ' . $e->getMessage());
                $employee['leave_balances'] = [];
            }

            try {
                // Get recent leave history
                $leaveHistoryStmt = $db->prepare("
                    SELECT leave_type, start_date, end_date, status
                    FROM employee_leave_history
                    WHERE employee_id = :id
                    ORDER BY start_date DESC
                    LIMIT 5
                ");
                $leaveHistoryStmt->execute(['id' => $_GET['id']]);
                $employee['leave_history'] = $leaveHistoryStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log('Error fetching leave history: ' . $e->getMessage());
                $employee['leave_history'] = [];
            }

            try {
                // Get assigned devices
                $devicesStmt = $db->prepare("
                    SELECT device_id, device_name, device_type
                    FROM employee_devices
                    WHERE employee_id = :id
                ");
                $devicesStmt->execute(['id' => $_GET['id']]);
                $employee['devices'] = $devicesStmt->fetchAll(PDO::FETCH_ASSOC);
            } catch (PDOException $e) {
                error_log('Error fetching devices: ' . $e->getMessage());
                $employee['devices'] = [];
            }

            echo json_encode([
                'success' => true,
                'data' => $employee
            ]);
        } else {
            error_log('No employee found with ID: ' . $_GET['id']);
            echo json_encode([
                'success' => false,
                'message' => 'Employee not found'
            ]);
        }
    } catch (PDOException $e) {
        error_log('Database error in employee-details.php: ' . $e->getMessage());
        error_log('SQL State: ' . $e->getCode());
        echo json_encode([
            'success' => false,
            'message' => 'Database error: ' . $e->getMessage()
        ]);
    }
} else {
    echo json_encode([
        'success' => false,
        'message' => 'Invalid request'
    ]);
} 