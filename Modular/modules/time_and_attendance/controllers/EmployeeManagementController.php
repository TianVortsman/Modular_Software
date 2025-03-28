<?php
namespace Modules\TimeAndAttendance\Controllers;

// Fix imports to use the correct App namespace
use \PDO;
use App\Services\DatabaseService;
use App\Core\Exception\DatabaseException;

/**
 * Base Controller with shared database connection and utility methods
 */
abstract class BaseEmployeeController
{
    protected $db;
    
    public function __construct()
    {
        try {
            // CHANGE: First check if session is started
            if (session_status() === PHP_SESSION_NONE) {
                session_start();
            }
            
            // Use ClientDatabase if account number is available, otherwise fall back to main
            if (isset($_SESSION['account_number'])) {
                $accountNumber = $_SESSION['account_number'];
                $userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');
                
                // Get the client-specific database based on account number
                $this->db = DatabaseService::getClientDatabase($accountNumber, $userName);
            } else {
                // For testing or when no account number is available, fall back to current database
                // This is not ideal for production, but allows development/testing
                $this->db = DatabaseService::getCurrentDatabase();
                
                // Log this fallback for debugging
                error_log("Warning: Using fallback database because no account_number in session");
            }
        } catch (\Exception $e) {
            // Convert to a JSON-friendly exception instead of letting PHP error display
            throw new \Exception("Failed to connect to database: " . $e->getMessage());
        }
    }
    
    /**
     * Common error handling for database operations
     */
    protected function handleDatabaseError(\PDOException $e, $operation, $rollback = true)
    {
        if ($rollback && method_exists($this->db, 'rollback')) {
            // Check transaction status safely
            try {
                // Only rollback if in transaction
                $this->db->rollback();
            } catch (\Exception $rollbackException) {
                // Ignore rollback errors
            }
        }
        // Convert to a JSON-friendly exception instead of DatabaseException
        throw new \Exception("Error $operation: " . $e->getMessage());
    }
}

/**
 * Handles employee listing and pagination
 */
class EmployeeListController extends BaseEmployeeController
{
    /**
     * Get employees with optional filtering and pagination
     * 
     * @param array $filters Filter criteria
     * @param int $page Page number
     * @param int $perPage Items per page
     * @return array Employees and pagination info
     */
    public function getEmployees($filters = [], $page = 1, $perPage = 20)
    {
        try {
            // Calculate offset for pagination
            $offset = ($page - 1) * $perPage;
            
            // Build query conditions based on filters
            $conditions = [];
            $params = [];
            
            if (!empty($filters['status'])) {
                $conditions[] = "e.status = :status";
                $params[':status'] = $filters['status'];
            }
            
            // Modified filter for department which is a string column, not a foreign key
            if (!empty($filters['department'])) {
                $conditions[] = "e.department LIKE :department";
                $params[':department'] = '%' . $filters['department'] . '%';
            }
            
            if (!empty($filters['search'])) {
                $conditions[] = "(e.first_name LIKE :search OR e.last_name LIKE :search OR e.employee_number LIKE :search)";
                $params[':search'] = '%' . $filters['search'] . '%';
            }
            
            // Build WHERE clause
            $whereClause = empty($conditions) ? "" : "WHERE " . implode(" AND ", $conditions);
            
            // Get total count for pagination using executeQuery instead of prepare
            $countQuery = "SELECT COUNT(*) FROM employees e $whereClause";
            $countStmt = $this->db->executeQuery($countQuery, $params);
            $totalCount = $countStmt->fetchColumn();
            
            // Modified query - removed joins to non-existent tables
            $query = "
                SELECT e.*
                FROM employees e
                $whereClause
                ORDER BY e.last_name, e.first_name
                LIMIT :limit OFFSET :offset
            ";
            
            // Add pagination params
            $allParams = array_merge($params, [
                ':limit' => $perPage,
                ':offset' => $offset
            ]);
            
            // Use executeQuery instead of prepare+execute
            $stmt = $this->db->executeQuery($query, $allParams);
            
            // Use fetchAll method from Database class
            $employees = $this->db->fetchAll($stmt);
            
            // Calculate pagination info
            $totalPages = ceil($totalCount / $perPage);
            
            return [
                'employees' => $employees,
                'pagination' => [
                    'total' => $totalCount,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => $totalPages,
                    'has_more' => $page < $totalPages
                ]
            ];
        } catch (\PDOException $e) {
            $this->handleDatabaseError($e, 'listing employees', false);
            return [
                'employees' => [],
                'pagination' => [
                    'total' => 0,
                    'per_page' => $perPage,
                    'current_page' => $page,
                    'total_pages' => 0,
                    'has_more' => false
                ]
            ];
        }
    }
    
    /**
     * Get employee distribution statistics
     * 
     * @return array Stats about employee distribution
     */
    public function getEmployeeStats()
    {
        try {
            // Get total count
            $countStmt = $this->db->query("SELECT COUNT(*) FROM employees");
            $totalCount = $countStmt->fetchColumn();
            
            // Get counts by status
            $statusStmt = $this->db->query("
                SELECT status, COUNT(*) as count
                FROM employees
                GROUP BY status
            ");
            $statusCounts = $statusStmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            // Get counts by department
            $deptStmt = $this->db->query("
                SELECT d.department_name, COUNT(*) as count
                FROM employees e
                JOIN departments d ON e.department_id = d.department_id
                GROUP BY d.department_name
                ORDER BY count DESC
            ");
            $departmentCounts = $deptStmt->fetchAll(PDO::FETCH_KEY_PAIR);
            
            return [
                'total' => $totalCount,
                'by_status' => $statusCounts,
                'by_department' => $departmentCounts
            ];
        } catch (\PDOException $e) {
            $this->handleDatabaseError($e, 'getting employee statistics', false);
            return [
                'total' => 0,
                'by_status' => [],
                'by_department' => []
            ];
        }
    }
}

/**
 * Handles detailed employee information
 */
class EmployeeDetailsController extends BaseEmployeeController
{
    /**
     * Get detailed employee information
     * 
     * @param int $employeeId Employee ID
     * @return array|null Detailed employee data
     */
    public function getEmployeeDetails($employeeId)
    {
        try {
            // Updated query to include date_of_birth, gender, badge_number and other fields
            $query = "
                SELECT e.employee_id, e.employee_number, e.clock_number, 
                    e.first_name, e.last_name, e.hire_date, e.date_of_birth, e.gender,
                    e.email, e.phone_number, e.badge_number,
                    e.department, e.position, e.division, e.group_name, e.cost_center,
                    e.employment_type, e.work_schedule_type, e.status, e.biometric_id,
                    e.emergency_contact_name, e.emergency_contact_phone, 
                    e.emergency_contact_relation, e.emergency_contact_email,
                    a.addr_id, a.addr_line_1, a.addr_line_2, a.suburb, a.city, a.province, 
                    a.country, a.postcode
                FROM employees e
                LEFT JOIN employee_address ea ON e.employee_id = ea.employee_id
                LEFT JOIN address a ON ea.addr_id = a.addr_id
                WHERE e.employee_id = :employee_id
            ";
            
            // Use executeQuery instead of prepare + bindParam + execute
            $stmt = $this->db->executeQuery($query, [':employee_id' => $employeeId]);
            $employee = $this->db->fetchRow($stmt);
            
            if (!$employee) {
                return null;
            }
            
            // Format date of birth to ensure it's in the right format for the frontend
            if (!empty($employee['date_of_birth'])) {
                // Ensure date is in YYYY-MM-DD format
                $date = new \DateTime($employee['date_of_birth']);
                $employee['date_of_birth'] = $date->format('Y-m-d');
            }
            
            // Format address into a structured object
            if (!empty($employee['addr_id'])) {
                $employee['address'] = [
                    'id' => $employee['addr_id'],
                    'line1' => $employee['addr_line_1'],
                    'line2' => $employee['addr_line_2'],
                    'suburb' => $employee['suburb'],
                    'city' => $employee['city'],
                    'state' => $employee['province'],
                    'country' => $employee['country'],
                    'postal' => $employee['postcode']
                ];
                
                // Remove individual address fields from the employee object
                unset($employee['addr_id']);
                unset($employee['addr_line_1']);
                unset($employee['addr_line_2']);
                unset($employee['suburb']);
                unset($employee['city']);
                unset($employee['province']);
                unset($employee['country']);
                unset($employee['postcode']);
            } else {
                $employee['address'] = null;
            }
            
            // Format emergency contact info into a structured object
            if (!empty($employee['emergency_contact_name'])) {
                $employee['emergencyContact'] = [
                    'name' => $employee['emergency_contact_name'],
                    'phone' => $employee['emergency_contact_phone'],
                    'relationship' => $employee['emergency_contact_relation'],
                    'email' => $employee['emergency_contact_email']
                ];
                
                // Remove individual emergency contact fields
                unset($employee['emergency_contact_name']);
                unset($employee['emergency_contact_phone']);
                unset($employee['emergency_contact_relation']);
                unset($employee['emergency_contact_email']);
            } else {
                $employee['emergencyContact'] = null;
            }
            
            // Get leave balances if the table exists
            try {
                $leaveQuery = "
                    SELECT lt.leave_type_name as leave_type, lb.balance
                    FROM leave_balances lb
                    JOIN leave_types lt ON lb.leave_type_id = lt.leave_type_id
                    WHERE lb.employee_id = :employee_id
                ";
                $leaveStmt = $this->db->executeQuery($leaveQuery, [':employee_id' => $employeeId]);
                $employee['leave_balances'] = $this->db->fetchAll($leaveStmt);
            } catch (\Exception $e) {
                // Table might not exist yet, just provide empty data
                $employee['leave_balances'] = [];
            }
            
            // Get leave history if the table exists
            try {
                $historyQuery = "
                    SELECT lt.leave_type_name as leave_type, lr.start_date, lr.end_date, lr.status
                    FROM leave_requests lr
                    JOIN leave_types lt ON lr.leave_type_id = lt.leave_type_id
                    WHERE lr.employee_id = :employee_id
                    ORDER BY lr.start_date DESC
                ";
                $historyStmt = $this->db->executeQuery($historyQuery, [':employee_id' => $employeeId]);
                $employee['leave_history'] = $this->db->fetchAll($historyStmt);
            } catch (\Exception $e) {
                // Table might not exist yet, just provide empty data
                $employee['leave_history'] = [];
            }
            
            // Get devices if the table exists
            try {
                $deviceQuery = "
                    SELECT device_name, device_type
                    FROM devices
                    WHERE assigned_to = :employee_id
                ";
                $deviceStmt = $this->db->executeQuery($deviceQuery, [':employee_id' => $employeeId]);
                $employee['devices'] = $this->db->fetchAll($deviceStmt);
            } catch (\Exception $e) {
                // Table might not exist yet, just provide empty data
                $employee['devices'] = [];
            }
            
            // Apply consistent formatting before returning
            return $this->formatEmployeeData($employee);
        } catch (\PDOException $e) {
            // Handle error
            error_log('Error fetching employee details: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Format employee data for frontend consistency
     * 
     * @param array $employee Raw employee data
     * @return array Formatted employee data
     */
    private function formatEmployeeData($employee)
    {
        // Ensure all expected fields exist even if null
        $defaults = [
            'employee_id' => null,
            'employee_number' => null,
            'clock_number' => null,
            'first_name' => null,
            'last_name' => null,
            'date_of_birth' => null,
            'hire_date' => null,
            'email' => null,
            'phone_number' => null,
            'gender' => 'male',
            'badge_number' => null,
            'department' => null,
            'position' => null,
            'division' => null,
            'group_name' => null,
            'cost_center' => null,
            'status' => 'active',
            'employment_type' => 'Permanent',
            'work_schedule_type' => 'Open',
            'biometric_id' => null,
            'address' => null,
            'emergencyContact' => null,
            'leave_balances' => [],
            'leave_history' => [],
            'devices' => []
        ];
        
        // Merge with defaults
        $employee = array_merge($defaults, $employee);
        
        return $employee;
    }
}

/**
 * Handles adding, updating, and deleting employees
 */
class EmployeeActionController extends BaseEmployeeController
{
    /**
     * Add a new employee
     * 
     * @param array $employeeData Employee data
     * @return array Result with employee ID
     */
    public function addEmployee($employeeData)
    {
        try {
            // Check if employee with same number already exists
            $checkQuery = "SELECT COUNT(*) FROM employees WHERE employee_number = :employee_number";
            $checkStmt = $this->db->executeQuery($checkQuery, [
                ':employee_number' => $employeeData['employee_number']
            ]);
            $exists = (int)$checkStmt->fetchColumn();
            
            if ($exists > 0) {
                return [
                    'success' => false,
                    'message' => 'Employee number already exists'
                ];
            }
            
            $this->db->beginTransaction();
            
            // Process address data if provided
            $addrId = null;
            if (!empty($employeeData['address'])) {
                $addressData = [
                    'addr_line_1' => $employeeData['address']['line1'] ?? '',
                    'addr_line_2' => $employeeData['address']['line2'] ?? '',
                    'suburb' => $employeeData['address']['suburb'] ?? ($employeeData['address']['neighborhood'] ?? ''),
                    'city' => $employeeData['address']['city'] ?? '',
                    'province' => $employeeData['address']['state'] ?? '',
                    'country' => $employeeData['address']['country'] ?? '',
                    'postcode' => $employeeData['address']['postal'] ?? ''
                ];
                
                // First create address
                $addrFields = array_keys($addressData);
                $addrPlaceholders = array_map(function($field) {
                    return ":$field";
                }, $addrFields);
                
                $addrInsertQuery = "INSERT INTO address (" . implode(", ", $addrFields) . ") 
                                   VALUES (" . implode(", ", $addrPlaceholders) . ") RETURNING addr_id";
                
                $addrParams = [];
                foreach ($addressData as $field => $value) {
                    $addrParams[":$field"] = $value;
                }
                
                $addrStmt = $this->db->executeQuery($addrInsertQuery, $addrParams);
                $addrId = $addrStmt->fetchColumn();
            }
            
            // Insert employee data
            $query = "
                INSERT INTO employees (
                    employee_number, clock_number, first_name, last_name, 
                    department, position, status, employment_type
                ) VALUES (
                    :employee_number, :clock_number, :first_name, :last_name,
                    :department, :position, :status, :employment_type
                ) RETURNING employee_id
            ";
            
            // Set default values
            $department = $employeeData['department'] ?? '';
            $position = $employeeData['position'] ?? '';
            $status = $employeeData['status'] ?? 'active';
            $employmentType = $employeeData['employment_type'] ?? 'Permanent';
            
            // Use executeQuery instead of prepare/bindParam/execute
            $params = [
                ':employee_number' => $employeeData['employee_number'],
                ':clock_number' => $employeeData['clock_number'],
                ':first_name' => $employeeData['first_name'],
                ':last_name' => $employeeData['last_name'],
                ':department' => $department,
                ':position' => $position,
                ':status' => $status,
                ':employment_type' => $employmentType
            ];
            
            $stmt = $this->db->executeQuery($query, $params);
            $employeeId = $stmt->fetchColumn();
            
            // Link address to employee if we have an address
            if ($addrId) {
                $linkQuery = "INSERT INTO employee_address (employee_id, addr_id) VALUES (:employee_id, :addr_id)";
                $this->db->executeQuery($linkQuery, [
                    ':employee_id' => $employeeId,
                    ':addr_id' => $addrId
                ]);
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'employee_id' => $employeeId,
                'message' => 'Employee added successfully'
            ];
        } catch (\PDOException $e) {
            if (method_exists($this->db, 'rollback')) {
                $this->db->rollback();
            }
            return [
                'success' => false,
                'message' => 'Failed to add employee: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Update an employee
     * 
     * @param int $employeeId Employee ID
     * @param array $employeeData Updated data
     * @return array Result status
     */
    public function updateEmployee($employeeId, $employeeData)
    {
        try {
            $this->db->beginTransaction();
            
            // Get flattened data with separated employee and address info
            $data = $this->flattenEmployeeData($employeeData);
            $employeeFields = $data['employee'];
            $addressData = $data['address'];
            
            // Update employee data
            if (!empty($employeeFields)) {
                $updateFields = [];
                $params = [];
                
                foreach ($employeeFields as $field => $value) {
                    // Skip fields that don't map directly to database columns
                    if ($field !== 'employee_id' && $field !== 'csrf_token' && $field !== 'fullName') {
                        // Allow null values for emergency contact fields
                        if (strpos($field, 'emergency_contact_') === 0 || 
                            ($field === 'date_of_birth' || $field === 'hire_date') && ($value === '' || $value === null)) {
                            if ($value === '') {
                                $value = null;
                            }
                        }
                        
                        $updateFields[] = "$field = :$field";
                        $params[":$field"] = $value;
                    }
                }
                
                if (!empty($updateFields)) {
                    $updateQuery = "UPDATE employees SET " . implode(", ", $updateFields) . " WHERE employee_id = :employee_id";
                    $params[':employee_id'] = $employeeId;
                    
                    // Add this before executing the query in updateEmployee()
                    error_log("Update Query: " . $updateQuery);
                    error_log("Parameters: " . print_r($params, true));
                    
                    $this->db->executeQuery($updateQuery, $params);
                }
            }
            
            // Handle address data
            if (!empty($addressData)) {
                // Check if employee already has an address
                $checkQuery = "SELECT ea.employee_address_id, ea.addr_id 
                              FROM employee_address ea 
                              WHERE ea.employee_id = :employee_id 
                              AND ea.deleted_at IS NULL";
                $checkStmt = $this->db->executeQuery($checkQuery, [':employee_id' => $employeeId]);
                $existingAddress = $this->db->fetchRow($checkStmt);
                
                if ($existingAddress) {
                    // Update existing address
                    $addrId = $existingAddress['addr_id'];
                    
                    $addrUpdateFields = [];
                    $addrParams = [];
                    
                    foreach ($addressData as $field => $value) {
                        $addrUpdateFields[] = "$field = :$field";
                        $addrParams[":$field"] = $value;
                    }
                    
                    if (!empty($addrUpdateFields)) {
                        $addrUpdateQuery = "UPDATE address SET " . implode(", ", $addrUpdateFields) . ", updated_at = NOW() WHERE addr_id = :addr_id";
                        $addrParams[':addr_id'] = $addrId;
                        
                        $this->db->executeQuery($addrUpdateQuery, $addrParams);
                    }
                } else {
                    // Insert new address and link to employee
                    
                    // First create address
                    $addrFields = array_keys($addressData);
                    $addrPlaceholders = array_map(function($field) {
                        return ":$field";
                    }, $addrFields);
                    
                    $addrInsertQuery = "INSERT INTO address (" . implode(", ", $addrFields) . ") 
                                       VALUES (" . implode(", ", $addrPlaceholders) . ") RETURNING addr_id";
                    
                    $addrParams = [];
                    foreach ($addressData as $field => $value) {
                        $addrParams[":$field"] = $value;
                    }
                    
                    $addrStmt = $this->db->executeQuery($addrInsertQuery, $addrParams);
                    $addrId = $addrStmt->fetchColumn();
                    
                    // Then link to employee
                    $linkQuery = "INSERT INTO employee_address (employee_id, addr_id) VALUES (:employee_id, :addr_id)";
                    $this->db->executeQuery($linkQuery, [
                        ':employee_id' => $employeeId,
                        ':addr_id' => $addrId
                    ]);
                }
            }
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Employee updated successfully'
            ];
        } catch (\Exception $e) {
            if (method_exists($this->db, 'rollback')) {
                $this->db->rollback();
            }
            return [
                'success' => false,
                'message' => 'Error updating employee: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Delete an employee
     * 
     * @param int $employeeId Employee ID
     * @return array Result status
     */
    public function deleteEmployee($employeeId)
    {
        try {
            $this->db->beginTransaction();
            
            // Use executeQuery instead of prepare/bindParam/execute
            $this->db->executeQuery("DELETE FROM employees WHERE employee_id = :employee_id", [
                ':employee_id' => $employeeId
            ]);
            
            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Employee deleted successfully'
            ];
        } catch (\PDOException $e) {
            $this->handleDatabaseError($e, 'deleting employee');
            return [
                'success' => false,
                'message' => 'Failed to delete employee: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Helper to flatten the nested employee data from frontend and handle address separately
     */
    private function flattenEmployeeData($employeeData) {
        $result = [];
        $addressData = null;
        
        // Process personal details
        if (isset($employeeData['personalDetails'])) {
            $personal = $employeeData['personalDetails'];
            
            // Only include non-empty fields
            if (!empty($personal['email'])) {
                $result['email'] = $personal['email'];
            }
            
            if (!empty($personal['phone'])) {
                $result['phone_number'] = $personal['phone'];
            }
            
            // Extract address data to be inserted into address table
            if (isset($personal['address']) && !empty($personal['address']['line1'])) {
                $addressData = [
                    'addr_line_1' => $personal['address']['line1'] ?? '',
                    'addr_line_2' => $personal['address']['line2'] ?? '',
                    'suburb' => $personal['address']['suburb'] ?? ($personal['address']['neighborhood'] ?? ''),
                    'city' => $personal['address']['city'] ?? '',
                    'province' => $personal['address']['state'] ?? '',
                    'country' => $personal['address']['country'] ?? '',
                    'postcode' => $personal['address']['postal'] ?? ''
                ];
            }
            
            // Date of birth if provided and not empty
            if (isset($personal['dob']) && !empty($personal['dob'])) {
                $result['date_of_birth'] = $personal['dob'];
            }
            
            // Emergency contact details - process even if some fields are empty
            if (isset($personal['emergencyContact'])) {
                $result['emergency_contact_name'] = $personal['emergencyContact']['name'] ?? null;
                $result['emergency_contact_phone'] = $personal['emergencyContact']['phone'] ?? null;
                $result['emergency_contact_relation'] = $personal['emergencyContact']['relationship'] ?? null;
                $result['emergency_contact_email'] = $personal['emergencyContact']['email'] ?? null;
            }
        }
        
        // Process employment details
        if (isset($employeeData['employmentDetails'])) {
            $employment = $employeeData['employmentDetails'];
            
            // Only include non-empty fields
            if (!empty($employment['jobTitle'])) {
                $result['position'] = $employment['jobTitle'];
            }
            
            if (!empty($employment['department'])) {
                $result['department'] = $employment['department'];
            }
            
            if (!empty($employment['hireDate'])) {
                $result['hire_date'] = $employment['hireDate'];
            }
            
            if (!empty($employment['status'])) {
                $result['status'] = $employment['status'];
            }
            
            if (!empty($employment['employmentType'])) {
                $result['employment_type'] = $employment['employmentType'];
            }
        }
        
        // Return both employee data and address data
        return [
            'employee' => $result,
            'address' => $addressData
        ];
    }
} 