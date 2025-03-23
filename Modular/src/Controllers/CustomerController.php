<?php
namespace App\Controllers;

use App\Core\Database\MainDatabase;
use App\Services\DatabaseService;

/**
 * Customer Controller
 * Handles all customer management operations for the techlogin page
 */
class CustomerController
{
    private $db;

    /**
     * Constructor - initializes database connection
     */
    public function __construct()
    {
        $this->db = DatabaseService::getMainDatabase();
    }

    /**
     * Get details for a specific customer by ID
     * 
     * @param int $id Customer ID
     * @return array Customer details or error
     */
    public function getCustomerDetails($id)
    {
        try {
            $this->db->connect();
            $query = "
                SELECT 
                    customer_id,
                    company_name,
                    email,
                    account_number,
                    client_db,
                    created_at,
                    status,
                    last_login
                FROM 
                    customers
                WHERE customer_id = $1
            ";
            
            $params = [$id];
            $result = $this->db->query($query, $params);
            
            if (!$result) {
                throw new \Exception("Failed to retrieve customer: " . $this->db->getLastError());
            }
            
            $customer = $this->db->fetchRow($result);
            
            if (!$customer) {
                return [
                    'success' => false,
                    'error' => "Customer not found with ID: $id"
                ];
            }
            
            return [
                'success' => true,
                'customer' => $customer
            ];
        } catch (\Exception $e) {
            error_log("Error in CustomerController->getCustomerDetails: " . $e->getMessage());
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get customer users by customer ID
     * 
     * @param int $customerId The customer ID
     * @return array Response with customer users
     */
    public function getCustomerUsers($customerId)
    {
        try {
            if (!$customerId) {
                throw new \Exception("No customer ID provided");
            }

            $usersSql = "
                SELECT 
                    id,
                    name,
                    email,
                    role,
                    status,
                    last_login
                FROM users 
                WHERE customer_id = :id
                ORDER BY name ASC
            ";

            $params = [':id' => $customerId];
            $result = $this->db->query($usersSql, $params);
            $users = $this->db->fetchAll($result);

            return [
                'success' => true,
                'users' => $users
            ];

        } catch (\Exception $e) {
            // Log the error
            error_log("CustomerController->getCustomerUsers error: " . $e->getMessage());
            
            // Return error response
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get customer modules by customer ID
     * 
     * @param int $customerId The customer ID
     * @return array Response with customer modules
     */
    public function getCustomerModules($customerId)
    {
        try {
            if (!$customerId) {
                throw new \Exception("No customer ID provided");
            }

            $modulesSql = "
                SELECT 
                    module_name,
                    module_type,
                    status,
                    expiry_date
                FROM customer_modules 
                WHERE customer_id = :id
            ";

            $params = [':id' => $customerId];
            $result = $this->db->query($modulesSql, $params);
            $allModules = $this->db->fetchAll($result);

            // Organize modules by type
            $coreModules = [];
            $additionalFeatures = [];
            $mobileFeatures = [];

            foreach ($allModules as $module) {
                switch ($module['module_type']) {
                    case 'core':
                        $coreModules[] = [
                            'name' => $module['module_name'],
                            'status' => $module['status'],
                            'expiry' => $module['expiry_date']
                        ];
                        break;
                    case 'additional':
                        $additionalFeatures[] = [
                            'name' => $module['module_name'],
                            'status' => $module['status'],
                            'expiry' => $module['expiry_date']
                        ];
                        break;
                    case 'mobile':
                        $mobileFeatures[] = [
                            'name' => $module['module_name'],
                            'status' => $module['status'],
                            'expiry' => $module['expiry_date']
                        ];
                        break;
                }
            }

            // If no modules found, provide defaults
            if (empty($coreModules)) {
                $coreModules = [
                    ['name' => 'Time & Attendance', 'status' => 'active', 'expiry' => null],
                    ['name' => 'User Management', 'status' => 'active', 'expiry' => null],
                    ['name' => 'Basic Reporting', 'status' => 'active', 'expiry' => null]
                ];
            }

            if (empty($additionalFeatures)) {
                $additionalFeatures = [
                    ['name' => 'Advanced Reporting', 'status' => 'inactive', 'expiry' => null],
                    ['name' => 'Payroll Integration', 'status' => 'inactive', 'expiry' => null],
                    ['name' => 'API Access', 'status' => 'inactive', 'expiry' => null]
                ];
            }

            if (empty($mobileFeatures)) {
                $mobileFeatures = [
                    ['name' => 'Mobile App Access', 'status' => 'inactive', 'expiry' => null],
                    ['name' => 'Mobile Clock In/Out', 'status' => 'inactive', 'expiry' => null]
                ];
            }

            return [
                'success' => true,
                'core_modules' => $coreModules,
                'additional_features' => $additionalFeatures,
                'mobile_features' => $mobileFeatures
            ];

        } catch (\Exception $e) {
            // Log the error
            error_log("CustomerController->getCustomerModules error: " . $e->getMessage());
            
            // Return error response
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Get all customers with optional search filter
     * 
     * @param string $searchTerm Optional search term
     * @param int $page Page number for pagination
     * @param int $perPage Items per page
     * @param string $sortBy Column to sort by
     * @param string $sortDirection Sort direction (asc/desc)
     * @return array Response with customers list
     */
    public function getAllCustomers($searchTerm = '', $page = 1, $perPage = 10, $sortBy = 'company_name', $sortDirection = 'asc')
    {
        try {
            // Ensure connection is established
            $this->db->connect();
            $conn = $this->db->getConnection();
            
            if (!$conn) {
                throw new \Exception("Failed to connect to database");
            }
            
            // Validate and sanitize inputs for direct use in query
            $page = max(1, (int)$page);
            $perPage = max(1, min(100, (int)$perPage));
            $offset = ($page - 1) * $perPage;
            
            // Validate sort column (whitelist approach)
            $allowedSortColumns = [
                'company_name' => 'c.company_name',
                'email' => 'c.email',
                'account_number' => 'c.account_number',
                'status' => 'c.status',
                'last_login' => 'c.last_login',
                'user_count' => 'user_count'
            ];
            
            if (!array_key_exists($sortBy, $allowedSortColumns)) {
                $sortBy = 'company_name'; // Default
            }
            
            // Translate to actual column name
            $sortColumn = $allowedSortColumns[$sortBy];
            
            // Validate sort direction
            $sortDirection = strtoupper($sortDirection) === 'DESC' ? 'DESC' : 'ASC';
            
            // Build the search condition
            $searchCondition = '';
            $params = [];
            $paramIndex = 1;
            
            if (!empty($searchTerm)) {
                $searchCondition = " AND (
                    c.company_name ILIKE $" . $paramIndex . " OR 
                    c.email ILIKE $" . $paramIndex . " OR 
                    c.account_number ILIKE $" . $paramIndex . "
                )";
                $params[] = '%' . $searchTerm . '%';
                $paramIndex++;
            }
            
            // Count total customers query
            $countQuery = "
                SELECT COUNT(*) as total 
                FROM customers c
                WHERE 1=1 $searchCondition
            ";
            
            $countResult = pg_query_params($conn, $countQuery, $params);
            if (!$countResult) {
                throw new \Exception("Failed to count customers: " . pg_last_error($conn));
            }
            
            $totalRow = pg_fetch_assoc($countResult);
            $totalCount = (int)$totalRow['total'];
            $totalPages = ceil($totalCount / $perPage);
            
            // Fetch customers data
            $customerParams = $params;
            $customerParams[] = $perPage;
            $customerParams[] = $offset;
            
            $query = "
                SELECT 
                    c.customer_id,
                    c.company_name,
                    c.email,
                    c.account_number,
                    c.status,
                    c.last_login,
                    (SELECT COUNT(*) FROM users WHERE customer_id = c.customer_id) as user_count,
                    (SELECT COUNT(*) FROM users WHERE customer_id = c.customer_id AND status = 'active') as active_users
                FROM customers c
                WHERE 1=1 $searchCondition
                ORDER BY $sortColumn $sortDirection
                LIMIT $" . ($paramIndex++) . " OFFSET $" . ($paramIndex++) . "
            ";
            
            $result = pg_query_params($conn, $query, $customerParams);
            if (!$result) {
                throw new \Exception("Failed to fetch customers: " . pg_last_error($conn));
            }
            
            $customers = pg_fetch_all($result);
            if (!$customers) {
                $customers = []; // Ensure we always return an array
            }
            
            // Calculate pagination info
            $showing = [
                'start' => $totalCount > 0 ? $offset + 1 : 0,
                'end' => min($offset + $perPage, $totalCount),
                'total' => $totalCount
            ];
            
            return [
                'success' => true,
                'customers' => $customers,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => $totalPages,
                    'total_items' => $totalCount,
                    'showing' => $showing
                ]
            ];
        } catch (\Exception $e) {
            // Log the error
            error_log("CustomerController->getAllCustomers error: " . $e->getMessage());
            
            // Return error response
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
} 