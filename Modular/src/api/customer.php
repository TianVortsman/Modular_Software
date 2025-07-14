<?php
/**
 * Customer API Endpoint
 * 
 * Handles all customer-related API requests for the techlogin page
 * Uses PSR-4 autoloading for class imports
 */

// Set appropriate headers for JSON response
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST');
header('Access-Control-Allow-Headers: Content-Type');

// Include autoloader
require_once __DIR__ . '/../../vendor/autoload.php';

// Create a customer controller instance
$customerController = new \App\Controllers\CustomerController();

// Get the action parameter
$action = $_GET['action'] ?? '';

// Process based on action
try {
    switch ($action) {
        case 'details':
            // Get customer details
            $customerId = isset($_GET['id']) ? (int)$_GET['id'] : null;
            $result = $customerController->getCustomerDetails($customerId);
            break;
            
        case 'users':
            // Get customer users
            $customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
            $result = $customerController->getCustomerUsers($customerId);
            break;
            
        case 'modules':
            // Get customer modules
            $customerId = isset($_GET['customer_id']) ? (int)$_GET['customer_id'] : null;
            $result = $customerController->getCustomerModules($customerId);
            break;
            
        case 'update_modules':
            // Update modules for a customer
            $input = json_decode(file_get_contents('php://input'), true);
            $result = $customerController->updateCustomerModules($input);
            break;
            
        case 'list':
            // Get all customers with optional search/filter/pagination
            $searchTerm = $_GET['search'] ?? '';
            $page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
            $perPage = isset($_GET['per_page']) ? (int)$_GET['per_page'] : 10;
            $sortBy = $_GET['sort_by'] ?? 'company_name';
            $sortDirection = $_GET['sort_direction'] ?? 'asc';
            
            // Log parameters for debugging
            error_log("Customer list API called with: search=$searchTerm, page=$page, perPage=$perPage, sortBy=$sortBy, sortDirection=$sortDirection");
            
            $result = $customerController->getAllCustomers(
                $searchTerm,
                $page,
                $perPage,
                $sortBy,
                $sortDirection
            );
            break;
            
        case 'add_user':
            // Add a new user for a client
            $input = json_decode(file_get_contents('php://input'), true);
            $result = $customerController->addUser($input);
            break;
            
        default:
            // Unknown action
            $result = [
                'success' => false,
                'error' => 'Invalid action specified'
            ];
            break;
    }
} catch (Exception $e) {
    // Handle any exceptions
    $result = [
        'success' => false,
        'error' => 'API error: ' . $e->getMessage()
    ];
    
    // Log the error
    error_log('Customer API error: ' . $e->getMessage());
}

// Output the result as JSON
echo json_encode($result); 