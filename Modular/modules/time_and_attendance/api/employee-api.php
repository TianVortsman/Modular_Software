<?php
// Ensure we start a session first (if not already started)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Set content type to JSON first to ensure we always return JSON
header('Content-Type: application/json');

// Use the correct absolute path based on Docker container configuration
require_once '/var/www/html/vendor/autoload.php';

// Manually include the controller files since they're not in the PSR-4 autoloader configuration
require_once __DIR__ . '/../Controllers/EmployeeManagementController.php';

// If DatabaseException isn't found, create a simple compatibility class
if (!class_exists('App\Core\Exception\DatabaseException')) {
    class CompatibilityDatabaseException extends \Exception {}
    class_alias('CompatibilityDatabaseException', 'App\Core\Exception\DatabaseException');
}

use Modules\TimeAndAttendance\Controllers\EmployeeListController;
use Modules\TimeAndAttendance\Controllers\EmployeeDetailsController;
use Modules\TimeAndAttendance\Controllers\EmployeeActionController;
use App\Core\Exception\DatabaseException;

// Enable CORS if needed
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, Authorization');

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Extract API endpoint action
$action = $_GET['action'] ?? 'list';

try {
    // Route to the appropriate controller based on action
    switch ($action) {
        case 'list':
            // Get employees list with pagination
            $controller = new EmployeeListController();
            
            // Extract pagination params
            $page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
            $perPage = isset($_GET['per_page']) ? min(100, max(10, (int)$_GET['per_page'])) : 20;
            
            // Build filters
            $filters = [];
            if (isset($_GET['status'])) $filters['status'] = $_GET['status'];
            if (isset($_GET['department_id'])) $filters['department_id'] = $_GET['department_id'];
            if (isset($_GET['search'])) $filters['search'] = $_GET['search'];
            
            $result = $controller->getEmployees($filters, $page, $perPage);
            echo json_encode([
                'success' => true,
                'data' => $result['employees'],
                'pagination' => $result['pagination']
            ]);
            break;
            
        case 'details':
            // Get detailed employee information
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Employee ID is required'
                ]);
                exit;
            }
            
            $employeeId = (int)$_GET['id'];
            $controller = new EmployeeDetailsController();
            $employeeDetails = $controller->getEmployeeDetails($employeeId);
            
            if ($employeeDetails) {
                echo json_encode([
                    'success' => true,
                    'data' => $employeeDetails
                ]);
            } else {
                http_response_code(404);
                echo json_encode([
                    'success' => false,
                    'message' => 'Employee not found'
                ]);
            }
            break;
            
        case 'add':
            // Handle add employee
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode([
                    'success' => false,
                    'message' => 'Method not allowed. Use POST for adding employees'
                ]);
                exit;
            }
            
            // Get JSON input
            $jsonInput = file_get_contents('php://input');
            $employeeData = json_decode($jsonInput, true);
            
            // Validate CSRF token (if implemented)
            if (isset($employeeData['csrf_token'])) {
                unset($employeeData['csrf_token']);
            }
            
            // Validate required fields
            $requiredFields = ['employee_number', 'first_name', 'last_name'];
            foreach ($requiredFields as $field) {
                if (!isset($employeeData[$field]) || empty(trim($employeeData[$field]))) {
                    http_response_code(400);
                    echo json_encode([
                        'success' => false,
                        'message' => "Missing required field: $field"
                    ]);
                    exit;
                }
            }
            
            $controller = new EmployeeActionController();
            $result = $controller->addEmployee($employeeData);
            echo json_encode($result);
            break;
            
        case 'update':
            // Handle update employee
            if ($_SERVER['REQUEST_METHOD'] !== 'PUT' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode([
                    'success' => false,
                    'message' => 'Method not allowed. Use PUT for updating employees'
                ]);
                exit;
            }
            
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Employee ID is required'
                ]);
                exit;
            }
            
            $employeeId = (int)$_GET['id'];
            
            // Get JSON input
            $jsonInput = file_get_contents('php://input');
            $employeeData = json_decode($jsonInput, true);
            
            // Validate CSRF token (if implemented)
            if (isset($employeeData['csrf_token'])) {
                unset($employeeData['csrf_token']);
            }
            
            $controller = new EmployeeActionController();
            $result = $controller->updateEmployee($employeeId, $employeeData);
            echo json_encode($result);
            break;
            
        case 'delete':
            // Handle delete employee
            if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
                http_response_code(405);
                echo json_encode([
                    'success' => false,
                    'message' => 'Method not allowed. Use DELETE for removing employees'
                ]);
                exit;
            }
            
            if (!isset($_GET['id'])) {
                http_response_code(400);
                echo json_encode([
                    'success' => false,
                    'message' => 'Employee ID is required'
                ]);
                exit;
            }
            
            $employeeId = (int)$_GET['id'];
            
            $controller = new EmployeeActionController();
            $result = $controller->deleteEmployee($employeeId);
            echo json_encode($result);
            break;
            
        case 'stats':
            // Get employee statistics
            $controller = new EmployeeListController();
            $stats = $controller->getEmployeeStats();
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            break;
            
        default:
            http_response_code(404);
            echo json_encode([
                'success' => false,
                'message' => 'Unknown action'
            ]);
            break;
    }
} catch (DatabaseException $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (\InvalidArgumentException $e) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
} catch (\Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'An unexpected error occurred: ' . $e->getMessage()
    ]);
} 