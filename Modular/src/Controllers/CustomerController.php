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
                WHERE customer_id = ?
            ";
            $params = [$id];
            $result = $this->db->executeQuery('get_customer_details', $query, $params);
            if (!$result) {
                throw new \Exception("Failed to retrieve customer: " . $this->db->getLastError());
            }
            $customer = $this->db->fetchRow($result);
            if (!$customer) {
                return [
                    'success' => false,
                    'message' => "Customer not found with ID: $id",
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
                'message' => $e->getMessage(),
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
                'message' => $e->getMessage(),
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

            // Get all modules (master list)
            $pdo = $this->db->getConnection();
            $allSql = "SELECT module_id, module_name, display_name FROM modules ORDER BY module_id";
            $allStmt = $pdo->query($allSql);
            $allModules = $allStmt->fetchAll(\PDO::FETCH_ASSOC);

            // Technician override: if tech is logged in, return all modules as active
            if (!empty($_SESSION['tech_logged_in']) && $_SESSION['tech_logged_in'] === true) {
                $modules = [];
                foreach ($allModules as $mod) {
                    $modules[] = [
                        'module_name' => $mod['module_name'],
                        'display_name' => $mod['display_name'],
                        'active' => true
                    ];
                }
                return [
                    'success' => true,
                    'modules' => $modules
                ];
            }

            // Get customer modules and their active state
            $custSql = "SELECT module_id, active FROM customer_modules WHERE customer_id = :id";
            $custStmt = $pdo->prepare($custSql);
            $custStmt->execute([':id' => $customerId]);
            $custModules = $custStmt->fetchAll(\PDO::FETCH_ASSOC);
            $custMap = [];
            foreach ($custModules as $cm) {
                $custMap[$cm['module_id']] = (bool)$cm['active'];
            }

            // Merge: for each module, set active if in customer_modules
            $modules = [];
            foreach ($allModules as $mod) {
                $modules[] = [
                    'module_name' => $mod['module_name'],
                    'display_name' => $mod['display_name'],
                    'active' => isset($custMap[$mod['module_id']]) ? $custMap[$mod['module_id']] : false
                ];
            }

            return [
                'success' => true,
                'modules' => $modules
            ];

        } catch (\Exception $e) {
            error_log("CustomerController->getCustomerModules error: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
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
            $countResult = $this->db->executeQuery('count_customers', $countQuery, $params);
            if (!$countResult) {
                throw new \Exception("Failed to count customers: " . $this->db->getLastError());
            }
            $totalRow = $this->db->fetchRow($countResult);
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
                LIMIT ? OFFSET ?
            ";
            $result = $this->db->executeQuery('fetch_customers', $query, $customerParams);
            if (!$result) {
                throw new \Exception("Failed to fetch customers: " . $this->db->getLastError());
            }
            $customers = $this->db->fetchAll($result);
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
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Add a new user for a client (main DB + client DB)
     * @param array $data
     * @return array
     */
    public function addUser($data)
    {
        try {
            // Validate input
            $name = trim($data['name'] ?? '');
            $email = trim($data['email'] ?? '');
            $role = trim($data['role'] ?? '');
            $customerId = (int)($data['customer_id'] ?? 0);
            if (!$name || !$email || !$role || !$customerId) {
                throw new \Exception('All fields are required.');
            }
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception('Invalid email address.');
            }
            if (strtolower($role) === 'technician') {
                throw new \Exception('Technician role cannot be assigned from this modal.');
            }

            // Check if email already exists for this customer
            $checkSql = "SELECT id FROM users WHERE email = ? AND customer_id = ?";
            $checkRes = $this->db->executeQuery('check_user_email', $checkSql, [$email, $customerId]);
            if ($checkRes && $this->db->numRows($checkRes) > 0) {
                throw new \Exception('A user with this email already exists for this customer.');
            }

            // Insert user into main DB (public.users)
            $defaultPassword = 'changeme123!';
            $hashedPassword = password_hash($defaultPassword, PASSWORD_BCRYPT);
            $insertSql = "INSERT INTO users (name, email, role, customer_id, password, status, is_first_login, created_at) VALUES (?, ?, ?, ?, ?, 'active', true, NOW()) RETURNING id";
            $insertRes = $this->db->executeQuery('insert_user', $insertSql, [$name, $email, $role, $customerId, $hashedPassword]);
            if (!$insertRes) {
                throw new \Exception('Failed to insert user in main DB: ' . $this->db->getLastError());
            }
            $userRow = $this->db->fetchRow($insertRes);
            $userId = $userRow['id'] ?? null;
            if (!$userId) {
                throw new \Exception('Failed to retrieve new user ID.');
            }

            // Get customer account_number and client_db
            $custSql = "SELECT account_number, client_db FROM customers WHERE customer_id = ?";
            $custRes = $this->db->executeQuery('get_cust_db', $custSql, [$customerId]);
            $custRow = $this->db->fetchRow($custRes);
            if (!$custRow || !$custRow['account_number'] || !$custRow['client_db']) {
                throw new \Exception('Could not find client DB for this customer.');
            }
            $accountNumber = $custRow['account_number'];
            $clientDbName = $custRow['client_db'];

            // Connect to client DB
            $clientDb = \App\Services\DatabaseService::getClientDatabase($accountNumber, $name);
            $clientConn = $clientDb->connect();
            if (!$clientConn) {
                throw new \Exception('Failed to connect to client DB.');
            }

            // Insert user into client DB (core.users)
            $clientInsertSql = "INSERT INTO core.users (user_id, user_name, role, created_at) VALUES (?, ?, ?, NOW())";
            $clientDb->executeQuery($clientInsertSql, [$userId, $name, $role]);

            // Assign all active modules for the customer to the user in client DB (core.user_modules)
            $modulesSql = "SELECT m.module_name FROM customer_modules cm JOIN modules m ON cm.module_id = m.module_id WHERE cm.customer_id = ? AND cm.active = true";
            $modulesRes = $this->db->executeQuery('get_active_modules', $modulesSql, [$customerId]);
            $modules = $this->db->fetchAll($modulesRes);
            foreach ($modules as $mod) {
                $modName = $mod['module_name'];
                $clientDb->executeQuery("INSERT INTO core.user_modules (user_id, module_name, enabled) VALUES (?, ?, true)", [$userId, $modName]);
            }

            return [
                'success' => true,
                'message' => 'User added successfully.'
            ];
        } catch (\Exception $e) {
            error_log('Add user error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }

    /**
     * Update modules for a customer
     * @param array $data
     * @return array
     */
    public function updateCustomerModules($data)
    {
        try {
            $customerId = $data['customer_id'] ?? null;
            $modules = $data['modules'] ?? null;
            if (!$customerId || !is_array($modules)) {
                throw new \Exception('Missing customer_id or modules array');
            }
            $pdo = $this->db->getConnection();
            $pdo->beginTransaction();
            foreach ($modules as $mod) {
                $name = $mod['module_name'] ?? '';
                $active = isset($mod['active']) ? (bool)$mod['active'] : false;
                // Look up module_id from modules table
                $modIdSql = "SELECT module_id FROM modules WHERE module_name = :mname";
                $modIdStmt = $pdo->prepare($modIdSql);
                $modIdStmt->execute([':mname' => $name]);
                $moduleId = $modIdStmt->fetchColumn();
                if (!$moduleId) {
                    throw new \Exception("Module not found: $name");
                }
                // Check if module exists for this customer
                $existsSql = "SELECT COUNT(*) FROM customer_modules WHERE customer_id = :cid AND module_id = :mid";
                $existsParams = [':cid' => $customerId, ':mid' => $moduleId];
                $existsStmt = $pdo->prepare($existsSql);
                $existsStmt->execute($existsParams);
                $exists = $existsStmt->fetchColumn();
                if ($exists) {
                    // Update
                    $updateSql = "UPDATE customer_modules SET active = :active WHERE customer_id = :cid AND module_id = :mid";
                    $updateStmt = $pdo->prepare($updateSql);
                    $updateStmt->bindValue(':active', $active, \PDO::PARAM_BOOL);
                    $updateStmt->bindValue(':cid', $customerId, \PDO::PARAM_INT);
                    $updateStmt->bindValue(':mid', $moduleId, \PDO::PARAM_INT);
                    $updateStmt->execute();
                } else {
                    // Insert
                    $insertSql = "INSERT INTO customer_modules (customer_id, module_id, active) VALUES (:cid, :mid, :active)";
                    $insertStmt = $pdo->prepare($insertSql);
                    $insertStmt->bindValue(':cid', $customerId, \PDO::PARAM_INT);
                    $insertStmt->bindValue(':mid', $moduleId, \PDO::PARAM_INT);
                    $insertStmt->bindValue(':active', $active, \PDO::PARAM_BOOL);
                    $insertStmt->execute();
                }
            }
            $pdo->commit();
            return ['success' => true];
        } catch (\Exception $e) {
            if (isset($pdo)) {
                $pdo->rollBack();
            }
            error_log('updateCustomerModules error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'error' => $e->getMessage()
            ];
        }
    }
} 