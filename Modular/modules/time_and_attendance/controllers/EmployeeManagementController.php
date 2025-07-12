<?php
namespace Modules\TimeAndAttendance\Controllers;

// Fix imports to use the correct App namespace
use \PDO;
use \PDOException;
use \Exception;
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
            $offset = ($page - 1) * $perPage;
            
            // Base query with joins to related tables
            $query = "
                SELECT 
                    e.employee_id,
                    e.first_name,
                    e.last_name,
                    e.employee_number,
                    e.is_sales,
                    ec.email,
                    ec.phone,
                    ee.hire_date,
                    ee.termination_date,
                    p.position_name as position,
                    d.department_name as department,
                    dv.division_name as division,
                    g.group_name as group,
                    cc.cost_center_name as cost_center,
                    et.employment_type_name as employment_type,
                    ee.employment_status,
                    a.employee_address_id,
                    a.address_line_1,
                    a.address_line_2,
                    a.suburb,
                    a.city,
                    a.province,
                    a.country,
                    a.postcode,
                    CASE 
                        WHEN ee.employment_id IS NULL THEN 'incomplete'
                        WHEN ee.termination_date < CURRENT_DATE THEN 'terminated'
                        WHEN ee.employment_status = 'active' THEN 'active'
                        ELSE LOWER(ee.employment_status)
                    END as display_status
                FROM core.employees e
                LEFT JOIN core.employee_contact ec ON e.employee_id = ec.employee_id
                LEFT JOIN core.employee_employment ee ON e.employee_id = ee.employee_id
                LEFT JOIN core.positions p ON ee.position_id = p.position_id
                LEFT JOIN core.departments d ON ee.department_id = d.department_id
                LEFT JOIN core.divisions dv ON ee.division_id = dv.division_id
                LEFT JOIN core.groups g ON ee.group_id = g.group_id
                LEFT JOIN core.cost_centers cc ON ee.cost_center_id = cc.cost_center_id
                LEFT JOIN core.employment_types et ON ee.employment_type_id = et.employment_type_id
                LEFT JOIN core.employee_address a ON ec.address_id = a.employee_address_id
                WHERE 1=1
            ";
            
            $params = [];
            
            // Add filters
            if (!empty($filters['status'])) {
                if ($filters['status'] === 'incomplete') {
                    $query .= " AND ee.employment_id IS NULL";
                } else if ($filters['status'] === 'terminated') {
                    $query .= " AND ee.termination_date < CURRENT_DATE";
                } else {
                    $query .= " AND LOWER(ee.employment_status) = LOWER(:status)";
                $params[':status'] = $filters['status'];
            }
            }
            
            if (!empty($filters['department_id'])) {
                $query .= " AND ee.department_id = :department_id";
                $params[':department_id'] = $filters['department_id'];
            }
            
            if (!empty($filters['employment_type'])) {
                $query .= " AND LOWER(et.employment_type_name) = LOWER(:employment_type)";
                $params[':employment_type'] = $filters['employment_type'];
            }
            
            if (!empty($filters['search'])) {
                $searchTerm = '%' . $filters['search'] . '%';
                $query .= " AND (
                    LOWER(e.first_name) LIKE LOWER(:search) OR
                    LOWER(e.last_name) LIKE LOWER(:search) OR
                    LOWER(e.employee_number) LIKE LOWER(:search) OR
                    LOWER(p.position_name) LIKE LOWER(:search) OR
                    LOWER(d.department_name) LIKE LOWER(:search)
                )";
                $params[':search'] = $searchTerm;
            }
            
            // Get total count for pagination
            $countQuery = "SELECT COUNT(*) as total FROM ($query) as subquery";
            $countStmt = $this->db->executeQuery($countQuery, $params);
            $totalCount = $this->db->fetchRow($countStmt)['total'];
            
            // If no employees found, return empty result with pagination
            if ($totalCount === 0) {
                return [
                    'employees' => [],
                    'pagination' => [
                        'current_page' => $page,
                        'per_page' => $perPage,
                        'total_pages' => 0,
                        'total_items' => 0
                    ],
                    'message' => 'No employees found'
                ];
            }
            
            // Add pagination
            $query .= " ORDER BY e.last_name, e.first_name LIMIT :limit OFFSET :offset";
            $params[':limit'] = $perPage;
            $params[':offset'] = $offset;
            
            // Execute main query
            $stmt = $this->db->executeQuery($query, $params);
            $employees = $this->db->fetchAll($stmt);
            
            return [
                'employees' => $employees,
                'pagination' => [
                    'current_page' => $page,
                    'per_page' => $perPage,
                    'total_pages' => ceil($totalCount / $perPage),
                    'total_items' => $totalCount
                ]
            ];
        } catch (\PDOException $e) {
            $this->handleDatabaseError($e, 'getting employees list', false);
            return [
                'employees' => [],
                'pagination' => [
                    'current_page' => 1,
                    'per_page' => $perPage,
                    'total_pages' => 0,
                    'total_items' => 0
                ],
                'message' => 'No employees found'
            ];
        }
    }
    
    /**
     * Get employee statistics including total, by status, and by department
     *
     * @return array Employee statistics
     */
    public function getEmployeeStats()
    {
        try {
            $stats = [];
            
            // Get total employees
            $query = "SELECT COUNT(*) as count FROM core.employees";
            $stmt = $this->db->executeQuery($query);
            $stats['total'] = $this->db->fetchRow($stmt)['count'];

            // Get employees by status
            $query = "
                SELECT ee.employment_status, COUNT(*) as count 
                FROM core.employees e
                LEFT JOIN core.employee_employment ee ON e.employee_id = ee.employee_id
                GROUP BY ee.employment_status
            ";
            $stmt = $this->db->executeQuery($query);
            $stats['by_status'] = $this->db->fetchAll($stmt);

            // Get employees by department
            $query = "
                SELECT d.department_name, COUNT(*) as count 
                FROM core.employees e
                LEFT JOIN core.employee_employment ee ON e.employee_id = ee.employee_id
                LEFT JOIN core.departments d ON ee.department_id = d.department_id
                GROUP BY d.department_name
            ";
            $stmt = $this->db->executeQuery($query);
            $stats['by_department'] = $this->db->fetchAll($stmt);
            
            // Get employees by employment type
            $query = "
                SELECT et.employment_type_name, COUNT(*) as count 
                FROM core.employees e
                LEFT JOIN core.employee_employment ee ON e.employee_id = ee.employee_id
                LEFT JOIN core.employment_types et ON ee.employment_type_id = et.employment_type_id
                GROUP BY et.employment_type_id
            ";
            $stmt = $this->db->executeQuery($query);
            $stats['by_employment_type'] = $this->db->fetchAll($stmt);
            
            return $stats;
        } catch (\PDOException $e) {
            $this->handleDatabaseError($e, 'getting employee statistics', false);
            return [
                'total' => 0,
                'by_status' => [],
                'by_department' => [],
                'by_employment_type' => []
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
            $query = "
                SELECT 
                    -- Core employee data
                    e.employee_id,
                    e.first_name,
                    e.last_name,
                    e.employee_number,
                    e.clock_number,
                    e.is_sales,
                    
                    -- Contact information
                    ec.email,
                    ec.phone,
                    
                    -- Emergency contact
                    eec.contact_name as emergency_contact_name,
                    eec.contact_phone as emergency_contact_phone,
                    eec.contact_relation as emergency_contact_relation,
                    eec.contact_email as emergency_contact_email,
                    
                    -- Personal information
                    ep.title,
                    ep.id_number,
                    ep.date_of_birth,
                    ep.gender,
                    
                    -- Employment information
                    ee.hire_date,
                    ee.termination_date,
                    ee.employment_status,
                    ee.manager_id,
                    
                    -- Organizational structure
                    p.position_name as position,
                    d.department_name as department,
                    dv.division_name as division,
                    g.group_name as \"group\",
                    cc.cost_center_name as cost_center,
                    s.site_name as site,
                    t.team_name as team,
                    
                    -- Employment details
                    et.employment_type_name as employment_type,
                    ct.contract_type_name as contract_type,
                    pp.period_name as pay_period,
                    al.level_name as access_level,
                    tc.category_name as time_category,
                    st.shift_type_name as shift_type,
                    
                    -- Address information
                    a.employee_address_id,
                    a.address_line_1,
                    a.address_line_2,
                    a.suburb,
                    a.city,
                    a.province,
                    a.country,
                    a.postcode,
                    
                    -- Roles
                    er.role_name,
                    er.module,
                    er.active as role_active
                    
                FROM core.employees e
                LEFT JOIN core.employee_contact ec ON e.employee_id = ec.employee_id
                LEFT JOIN core.employee_emergency_contact eec ON e.employee_id = eec.employee_id
                LEFT JOIN core.employee_personal ep ON e.employee_id = ep.employee_id
                LEFT JOIN core.employee_employment ee ON e.employee_id = ee.employee_id
                LEFT JOIN core.positions p ON ee.position_id = p.position_id
                LEFT JOIN core.departments d ON ee.department_id = d.department_id
                LEFT JOIN core.divisions dv ON ee.division_id = dv.division_id
                LEFT JOIN core.groups g ON ee.group_id = g.group_id
                LEFT JOIN core.cost_centers cc ON ee.cost_center_id = cc.cost_center_id
                LEFT JOIN core.sites s ON ee.site_id = s.site_id
                LEFT JOIN core.teams t ON ee.team_id = t.team_id
                LEFT JOIN core.employment_types et ON ee.employment_type_id = et.employment_type_id
                LEFT JOIN core.contract_types ct ON ee.contract_type_id = ct.contract_type_id
                LEFT JOIN core.pay_periods pp ON ee.pay_period_id = pp.period_id
                LEFT JOIN core.access_levels al ON ee.access_level_id = al.level_id
                LEFT JOIN core.time_categories tc ON ee.time_category_id = tc.category_id
                LEFT JOIN core.shift_types st ON ee.shift_type_id = st.shift_type_id
                LEFT JOIN core.employee_address a ON ec.address_id = a.employee_address_id
                LEFT JOIN core.employee_roles er ON e.employee_id = er.employee_id
                WHERE e.employee_id = :employee_id
            ";

            $stmt = $this->db->executeQuery($query, [':employee_id' => $employeeId]);
            $employee = $this->db->fetchRow($stmt);

            if ($employee) {
                return $this->formatEmployeeData($employee);
            }
            return null;
        } catch (\PDOException $e) {
            $this->handleDatabaseError($e, 'fetching employee details', false);
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
        // Format address data
        $address = null;
        if ($employee['address_line_1']) {
            $address = [
                'line1' => $employee['address_line_1'],
                'line2' => $employee['address_line_2'],
                'suburb' => $employee['suburb'],
                'city' => $employee['city'],
                'province' => $employee['province'],
                'country' => $employee['country'],
                'postcode' => $employee['postcode']
            ];
        }

        // Format emergency contact data
        $emergencyContact = null;
        if ($employee['emergency_contact_name']) {
            $emergencyContact = [
                'name' => $employee['emergency_contact_name'],
                'phone' => $employee['emergency_contact_phone'],
                'relationship' => $employee['emergency_contact_relation'],
                'email' => $employee['emergency_contact_email']
            ];
        }

        // Remove address and emergency contact fields from main data
        $addressFields = ['address_line_1', 'address_line_2', 'suburb', 'city', 'province', 'country', 'postcode'];
        $emergencyFields = ['emergency_contact_name', 'emergency_contact_phone', 'emergency_contact_relation', 'emergency_contact_email'];
        $fieldsToRemove = array_merge($addressFields, $emergencyFields);

        foreach ($fieldsToRemove as $field) {
            unset($employee[$field]);
        }

        // Add formatted address and emergency contact
        $employee['address'] = $address;
        $employee['emergencyContact'] = $emergencyContact;

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
        error_log('addEmployee called');
        try {
            // Check for duplicate employee number or clock number
            $checkQuery = "
                SELECT employee_number, clock_number 
                FROM core.employees 
                WHERE employee_number = :employee_number 
                   OR (clock_number IS NOT NULL AND clock_number = :clock_number)
            ";
            $checkStmt = $this->db->executeQuery($checkQuery, [
                ':employee_number' => $employeeData['employee_number'],
                ':clock_number' => $employeeData['clock_number'] ?? null
            ]);
            $duplicates = $this->db->fetchAll($checkStmt);
            $duplicateFields = [];
            foreach ($duplicates as $dup) {
                if ($dup['employee_number'] === $employeeData['employee_number']) {
                    $duplicateFields[] = 'employee_number';
                }
                if (!empty($employeeData['clock_number']) && $dup['clock_number'] === $employeeData['clock_number']) {
                    $duplicateFields[] = 'clock_number';
                }
            }
            if (!empty($duplicateFields)) {
                $fields = implode(' and ', array_unique($duplicateFields));
                return [
                    'success' => false,
                    'message' => "Duplicate $fields found. Please use a different value."
                ];
            }

            // Helper function to convert to boolean
            $toBoolean = function($value) {
                if (is_bool($value)) return $value;
                if (is_string($value)) {
                    $value = strtolower(trim($value));
                    return $value === 'true' || $value === '1' || $value === 'yes';
                }
                return (bool)$value;
            };

            // Convert is_sales to proper boolean
            $isSales = $toBoolean($employeeData['is_sales'] ?? false);

            // Log the values for debugging
            error_log("Employee data before insert: " . json_encode($employeeData));
            error_log("is_sales value after conversion: " . ($isSales ? 'true' : 'false'));

            // Insert new employee
            $query = "
                INSERT INTO core.employees (
                    first_name, 
                    last_name, 
                    employee_number, 
                    clock_number,
                    is_sales
                ) VALUES (
                    :first_name, 
                    :last_name, 
                    :employee_number, 
                    :clock_number,
                    :is_sales
                ) RETURNING employee_id
            ";
            
            $params = [
                ':first_name' => $employeeData['first_name'],
                ':last_name' => $employeeData['last_name'],
                ':employee_number' => $employeeData['employee_number'],
                ':clock_number' => $employeeData['clock_number'] ?? null,
                ':is_sales' => $isSales ? 'true' : 'false'  // Convert to PostgreSQL boolean string
            ];
            
            // Log the SQL parameters for debugging
            error_log("SQL parameters: " . json_encode($params));
            
            $stmt = $this->db->executeQuery($query, $params);
            $employeeId = $this->db->fetchRow($stmt)['employee_id'];
            
            // Insert into core.employee_employment with contract_type_id
            $employmentTypeName = $employeeData['employment_type'] ?? $employeeData['employmentType'] ?? 'Permanent';
            $getEmploymentTypeId = "SELECT employment_type_id FROM core.employment_types WHERE employment_type_name = :employment_type_name";
            $employmentTypeStmt = $this->db->executeQuery($getEmploymentTypeId, [':employment_type_name' => $employmentTypeName]);
            $employmentTypeId = $this->db->fetchRow($employmentTypeStmt)['employment_type_id'] ?? null;

            $employment_status = $employeeData['employment_status'] ?? 'active';
            $hire_date = date('Y-m-d');

            $insertEmployment = "
                INSERT INTO core.employee_employment (
                    employee_id, employment_status, employment_type_id, hire_date, created_at, updated_at
                ) VALUES (
                    :employee_id, :employment_status, :employment_type_id, :hire_date, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP
                )
            ";
            $this->db->executeQuery($insertEmployment, [
                ':employee_id' => $employeeId,
                ':employment_status' => $employment_status,
                ':employment_type_id' => $employmentTypeId,
                ':hire_date' => $hire_date
            ]);

            return [
                'success' => true,
                'employee_id' => $employeeId,
                'message' => 'Employee added successfully'
            ];
        } catch (\PDOException $e) {
            error_log("Database error adding employee: " . $e->getMessage());
            return [
                'success' => false,
                'message' => 'An error occurred while adding the employee. Please try again.'
            ];
        } catch (\Exception $e) {
            error_log("Error adding employee: " . $e->getMessage());
            return [
                'success' => false,
                'message' => $e->getMessage()
            ];
        }
    }
    
    /**
     * Transform frontend data to match database schema
     */
    private function transformEmployeeData($data) {
        try {
            // Parse address if it's a JSON string
            $address = null;
            if (isset($data['address'])) {
                if (is_string($data['address'])) {
                    $address = json_decode($data['address'], true);
                } else if (is_array($data['address'])) {
                    $address = $data['address'];
                }
            }
            
            // Parse emergency contact if it's a JSON string
            $emergencyContact = null;
            if (isset($data['emergency_contact'])) {
                if (is_string($data['emergency_contact'])) {
                    $emergencyContact = json_decode($data['emergency_contact'], true);
                } else if (is_array($data['emergency_contact'])) {
                    $emergencyContact = $data['emergency_contact'];
                }
            }
            
            // Helper function to convert to boolean
            $toBoolean = function($value) {
                if (is_bool($value)) return $value;
                if (is_string($value)) {
                    $value = strtolower(trim($value));
                    return $value === 'true' || $value === '1' || $value === 'yes';
                }
                return (bool)$value;
            };
            
            // Transform the data to match the schema
            $transformed = [
                // core.employees fields
                'first_name' => $data['first_name'] ?? null,
                'last_name' => $data['last_name'] ?? null,
                'employee_number' => $data['employee_number'] ?? null,
                'clock_number' => $data['clock_number'] ?? null,
                'is_sales' => $toBoolean($data['is_sales'] ?? false),
                
                // core.employee_contact fields
                'email' => $data['email'] ?? null,
                'phone' => $data['phone_number'] ?? null,
                
                // core.employee_personal fields
                'date_of_birth' => $data['date_of_birth'] ?? null,
                'gender' => $data['gender'] ?? null,
                'id_number' => $data['id_number'] ?? null,
                
                // core.employee_employment fields
                'hire_date' => $data['hire_date'] ?? null,
                'termination_date' => $data['termination_date'] ?? null,
                'employment_status' => $data['status'] ?? 'active',
                
                // core.employee_address fields
                'address' => $address ? [
                    'line1' => $address['line1'] ?? null,
                    'line2' => $address['line2'] ?? null,
                    'suburb' => $address['suburb'] ?? null,
                    'city' => $address['city'] ?? null,
                    'province' => $address['province'] ?? null,
                    'country' => $address['country'] ?? null,
                    'postcode' => $address['postcode'] ?? null
                ] : null,
                
                // core.employee_emergency_contact fields
                'emergency_contact' => $emergencyContact ? [
                    'name' => $emergencyContact['name'] ?? null,
                    'phone' => $emergencyContact['phone'] ?? null,
                    'relation' => $emergencyContact['relation'] ?? null,
                    'email' => $emergencyContact['email'] ?? null
                ] : null
            ];
            
            // Log the transformed data for debugging
            error_log("Transformed employee data: " . json_encode($transformed));
            
            return $transformed;
        } catch (Exception $e) {
            error_log("Error transforming employee data: " . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update employee information
     * 
     * @param int $employeeId Employee ID
     * @param array $employeeData Employee data to update
     * @return array Result status
     */
    public function updateEmployee($employeeId, $employeeData)
    {
        try {
            $this->db->beginTransaction();

            // Helper function to convert to PostgreSQL boolean
            $toPostgresBoolean = function($value) {
                if (is_bool($value)) return $value ? 'true' : 'false';
                if (is_string($value)) {
                    $value = strtolower(trim($value));
                    return ($value === 'true' || $value === '1' || $value === 'yes') ? 'true' : 'false';
                }
                return $value ? 'true' : 'false';
            };

            // Helper function to handle dates
            $handleDate = function($date) {
                if (empty($date) || $date === '' || $date === null) {
                    return null;
                }
                if ($date instanceof \DateTime) {
                    return $date->format('Y-m-d');
                }
                try {
                    $dateObj = new \DateTime($date);
                    return $dateObj->format('Y-m-d');
                } catch (\Exception $e) {
                    error_log("Invalid date format: " . $date);
                    return null;
                }
            };

            // Helper function to handle email
            $handleEmail = function($email) {
                if (empty($email) || $email === '' || $email === null) {
                    return null;
                }
                $email = trim(strtolower($email));
                if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return $email;
                }
                error_log("Invalid email format: " . $email);
                return null;
            };

            // First, check if employee_contact record exists
            $checkContactQuery = "
                SELECT contact_id 
                FROM core.employee_contact 
                WHERE employee_id = :employee_id
            ";
            
            $checkContactStmt = $this->db->executeQuery($checkContactQuery, [':employee_id' => $employeeId]);
            $existingContact = $this->db->fetchRow($checkContactStmt);

            if ($existingContact) {
                // Update existing contact
                $query = "
                    UPDATE core.employee_contact 
                    SET 
                        email = :email,
                        phone = :phone,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE employee_id = :employee_id
                ";
            } else {
                // Insert new contact
                $query = "
                    INSERT INTO core.employee_contact 
                    (employee_id, email, phone, created_at, updated_at)
                    VALUES 
                    (:employee_id, :email, :phone, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ";
            }
            
            $params = [
                ':employee_id' => $employeeId,
                ':email' => $handleEmail($employeeData['email'] ?? null),
                ':phone' => $employeeData['phone'] ?? null
            ];
            
            $this->db->executeQuery($query, $params);

            // Update core employee data
            $query = "
                UPDATE core.employees 
                SET 
                    first_name = :first_name,
                    last_name = :last_name,
                    employee_number = :employee_number,
                    clock_number = :clock_number,
                    is_sales = :is_sales
                WHERE employee_id = :employee_id
            ";
            
            $params = [
                ':employee_id' => $employeeId,
                ':first_name' => $employeeData['first_name'],
                ':last_name' => $employeeData['last_name'],
                ':employee_number' => $employeeData['employee_number'],
                ':clock_number' => $employeeData['clock_number'] ?? null,
                ':is_sales' => $toPostgresBoolean($employeeData['is_sales'] ?? false)
            ];
            
            $this->db->executeQuery($query, $params);

            // Update employment_type_id in core.employee_employment
            if (!empty($employeeData['employment_type'])) {
                $getEmploymentTypeId = "SELECT employment_type_id FROM core.employment_types WHERE employment_type_name = :employment_type_name";
                $employmentTypeStmt = $this->db->executeQuery($getEmploymentTypeId, [':employment_type_name' => $employeeData['employment_type']]);
                $employmentTypeId = $this->db->fetchRow($employmentTypeStmt)['employment_type_id'] ?? null;
                if ($employmentTypeId) {
                    $updateEmploymentType = "UPDATE core.employee_employment SET employment_type_id = :employment_type_id WHERE employee_id = :employee_id";
                    $this->db->executeQuery($updateEmploymentType, [
                        ':employment_type_id' => $employmentTypeId,
                        ':employee_id' => $employeeId
                    ]);
                }
            }

            // Update or insert emergency contact
            $checkEmergencyQuery = "
                SELECT emergency_contact_id 
                FROM core.employee_emergency_contact 
                WHERE employee_id = :employee_id
            ";
            
            $checkEmergencyStmt = $this->db->executeQuery($checkEmergencyQuery, [':employee_id' => $employeeId]);
            $existingEmergency = $this->db->fetchRow($checkEmergencyStmt);

            if ($existingEmergency) {
                $query = "
                    UPDATE core.employee_emergency_contact 
                    SET 
                        contact_name = :contact_name,
                        contact_phone = :contact_phone,
                        contact_relation = :contact_relation,
                        contact_email = :contact_email,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE employee_id = :employee_id
                ";
            } else {
                $query = "
                    INSERT INTO core.employee_emergency_contact 
                    (employee_id, contact_name, contact_phone, contact_relation, contact_email, created_at, updated_at)
                    VALUES 
                    (:employee_id, :contact_name, :contact_phone, :contact_relation, :contact_email, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ";
            }
            
            $params = [
                ':employee_id' => $employeeId,
                ':contact_name' => $employeeData['emergency_contact_name'] ?? null,
                ':contact_phone' => $employeeData['emergency_contact_phone'] ?? null,
                ':contact_relation' => $employeeData['emergency_contact_relation'] ?? null,
                ':contact_email' => $handleEmail($employeeData['emergency_contact_email'] ?? null)
            ];
            
            $this->db->executeQuery($query, $params);

            // Update or insert personal information
            $checkPersonalQuery = "
                SELECT personal_id 
                FROM core.employee_personal 
                WHERE employee_id = :employee_id
            ";
            
            $checkPersonalStmt = $this->db->executeQuery($checkPersonalQuery, [':employee_id' => $employeeId]);
            $existingPersonal = $this->db->fetchRow($checkPersonalStmt);

            if ($existingPersonal) {
                $query = "
                    UPDATE core.employee_personal 
                    SET 
                        title = :title,
                        id_number = :id_number,
                        date_of_birth = :date_of_birth,
                        gender = :gender,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE employee_id = :employee_id
                ";
            } else {
                $query = "
                    INSERT INTO core.employee_personal 
                    (employee_id, title, id_number, date_of_birth, gender, created_at, updated_at)
                    VALUES 
                    (:employee_id, :title, :id_number, :date_of_birth, :gender, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ";
            }
            
            $params = [
                ':employee_id' => $employeeId,
                ':title' => $employeeData['title'] ?? null,
                ':id_number' => $employeeData['id_number'] ?? null,
                ':date_of_birth' => $handleDate($employeeData['date_of_birth'] ?? null),
                ':gender' => $employeeData['gender'] ?? 'male'
            ];
            
            $this->db->executeQuery($query, $params);

            // Update or insert employment information
            $checkEmploymentQuery = "
                SELECT employment_id 
                FROM core.employee_employment 
                WHERE employee_id = :employee_id
            ";
            
            $checkEmploymentStmt = $this->db->executeQuery($checkEmploymentQuery, [':employee_id' => $employeeId]);
            $existingEmployment = $this->db->fetchRow($checkEmploymentStmt);

            // Get IDs for organizational structure
            $getPositionId = "
                SELECT position_id FROM core.positions WHERE position_name = :position_name
            ";
            $positionStmt = $this->db->executeQuery($getPositionId, [':position_name' => $employeeData['position_name'] ?? '']);
            $positionId = $this->db->fetchRow($positionStmt)['position_id'] ?? null;

            $getDivisionId = "
                SELECT division_id FROM core.divisions WHERE division_name = :division_name
            ";
            $divisionStmt = $this->db->executeQuery($getDivisionId, [':division_name' => $employeeData['division_name'] ?? '']);
            $divisionId = $this->db->fetchRow($divisionStmt)['division_id'] ?? null;

            $getDepartmentId = "
                SELECT department_id FROM core.departments WHERE department_name = :department_name
            ";
            $departmentStmt = $this->db->executeQuery($getDepartmentId, [':department_name' => $employeeData['department_name'] ?? '']);
            $departmentId = $this->db->fetchRow($departmentStmt)['department_id'] ?? null;

            $getGroupId = "
                SELECT group_id FROM core.groups WHERE group_name = :group_name
            ";
            $groupStmt = $this->db->executeQuery($getGroupId, [':group_name' => $employeeData['group_name'] ?? '']);
            $groupId = $this->db->fetchRow($groupStmt)['group_id'] ?? null;

            $getCostCenterId = "
                SELECT cost_center_id FROM core.cost_centers WHERE cost_center_name = :cost_center_name
            ";
            $costCenterStmt = $this->db->executeQuery($getCostCenterId, [':cost_center_name' => $employeeData['cost_center_name'] ?? '']);
            $costCenterId = $this->db->fetchRow($costCenterStmt)['cost_center_id'] ?? null;

            $getSiteId = "
                SELECT site_id FROM core.sites WHERE site_name = :site_name
            ";
            $siteStmt = $this->db->executeQuery($getSiteId, [':site_name' => $employeeData['site_name'] ?? '']);
            $siteId = $this->db->fetchRow($siteStmt)['site_id'] ?? null;

            $getTeamId = "
                SELECT team_id FROM core.teams WHERE team_name = :team_name
            ";
            $teamStmt = $this->db->executeQuery($getTeamId, [':team_name' => $employeeData['team_name'] ?? '']);
            $teamId = $this->db->fetchRow($teamStmt)['team_id'] ?? null;

            $getManagerId = "
                SELECT employee_id FROM core.employees 
                WHERE first_name = :manager_first_name 
                AND last_name = :manager_last_name
            ";
            $managerStmt = $this->db->executeQuery($getManagerId, [
                ':manager_first_name' => $employeeData['manager_first_name'] ?? '',
                ':manager_last_name' => $employeeData['manager_last_name'] ?? ''
            ]);
            $managerId = $this->db->fetchRow($managerStmt)['employee_id'] ?? null;

            if ($existingEmployment) {
                $query = "
                    UPDATE core.employee_employment 
                    SET 
                        hire_date = :hire_date,
                        termination_date = :termination_date,
                        position_id = :position_id,
                        division_id = :division_id,
                        department_id = :department_id,
                        group_id = :group_id,
                        cost_center_id = :cost_center_id,
                        site_id = :site_id,
                        team_id = :team_id,
                        manager_id = :manager_id,
                        employment_status = :employment_status,
                        updated_at = CURRENT_TIMESTAMP
                    WHERE employee_id = :employee_id
                ";
            } else {
                $query = "
                    INSERT INTO core.employee_employment 
                    (employee_id, hire_date, termination_date, position_id, division_id, department_id, 
                    group_id, cost_center_id, site_id, team_id, manager_id, employment_status, created_at, updated_at)
                    VALUES 
                    (:employee_id, :hire_date, :termination_date, :position_id, :division_id, :department_id,
                    :group_id, :cost_center_id, :site_id, :team_id, :manager_id, :employment_status, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ";
            }
            
            $params = [
                ':employee_id' => $employeeId,
                ':hire_date' => $handleDate($employeeData['hire_date'] ?? null),
                ':termination_date' => $handleDate($employeeData['termination_date'] ?? null),
                ':position_id' => $positionId,
                ':division_id' => $divisionId,
                ':department_id' => $departmentId,
                ':group_id' => $groupId,
                ':cost_center_id' => $costCenterId,
                ':site_id' => $siteId,
                ':team_id' => $teamId,
                ':manager_id' => $managerId,
                ':employment_status' => $employeeData['employment_status'] ?? 'active'
            ];
            
            $this->db->executeQuery($query, $params);

            // Update or insert roles
            if (isset($employeeData['roles']) && is_array($employeeData['roles'])) {
                // First, delete existing roles
                $deleteRolesQuery = "
                    DELETE FROM core.employee_roles 
                    WHERE employee_id = :employee_id
                ";
                $this->db->executeQuery($deleteRolesQuery, [':employee_id' => $employeeId]);

                // Then insert new roles
                $insertRoleQuery = "
                    INSERT INTO core.employee_roles 
                    (employee_id, role_name, module, active, created_at, updated_at)
                    VALUES 
                    (:employee_id, :role_name, :module, :active, CURRENT_TIMESTAMP, CURRENT_TIMESTAMP)
                ";

                foreach ($employeeData['roles'] as $role) {
                    $roleParams = [
                        ':employee_id' => $employeeId,
                        ':role_name' => $role['role_name'],
                        ':module' => $role['module'],
                        ':active' => $toPostgresBoolean($role['active'] ?? true)
                    ];
                    $this->db->executeQuery($insertRoleQuery, $roleParams);
                }
            }

            $this->db->commit();
            
            return [
                'success' => true,
                'message' => 'Employee updated successfully'
            ];
        } catch (\PDOException $e) {
            $this->db->rollBack();
            $this->handleDatabaseError($e, 'updating employee', false);
            return [
                'success' => false,
                'message' => 'Failed to update employee: ' . $e->getMessage()
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
        $addressData = [];
        $addrId = $employeeData['address']['employee_address_id'] ?? null; // Capture addr_id if exists
        
        // Direct mapping of employee fields from frontend data
        $employeeFieldsMap = [
            'employee_number', 'clock_number', 'first_name', 'last_name',
            'date_of_birth', 'gender', 'email', 'phone_number', 'badge_number',
            'department', 'position', 'division', 'group', 'cost_center',
            'employment_type', 'work_week', 'employment_status',
            'hire_date', 'title', 'id_number', 'rate_type', 'rate',
            'overtime', 'pay_period'
        ];

        foreach ($employeeFieldsMap as $field) {
            if (isset($employeeData[$field])) {
                $result[$field] = $employeeData[$field];
            }
        }

        // Emergency Contact fields
        if (isset($employeeData['emergency_contact'])) {
            $emergencyContact = $employeeData['emergency_contact'];
            $result['emergency_contact_name'] = $emergencyContact['name'] ?? null;
            $result['emergency_contact_phone'] = $emergencyContact['phone'] ?? null;
            $result['emergency_contact_relation'] = $emergencyContact['relation'] ?? null;
            $result['emergency_contact_email'] = $emergencyContact['email'] ?? null;
        }

        // Address fields
        if (isset($employeeData['address'])) {
            $address = $employeeData['address'];
            $addressData['address_line_1'] = $address['line1'] ?? null;
            $addressData['address_line_2'] = $address['line2'] ?? null;
            $addressData['suburb'] = $address['suburb'] ?? null;
            $addressData['city'] = $address['city'] ?? null;
            $addressData['province'] = $address['province'] ?? null;
            $addressData['country'] = $address['country'] ?? null;
            $addressData['postcode'] = $address['postcode'] ?? null;
        }

        // Filter out empty strings and nulls for addressData to avoid updating with empty values
        $addressData = array_filter($addressData, function($value) { return $value !== null && $value !== ''; });
        
        return [
            'employee' => $result,
            'address' => $addressData,
            'employee_address_id' => $addrId
        ];
    }

    public function createEmployee($data) {
        try {
            $this->db->beginTransaction();

            // Log the incoming data
            error_log("Creating employee with data: " . json_encode($data));

            // Insert into employees table
            $query = "
                INSERT INTO core.employees (
                    first_name,
                    last_name,
                    employee_number,
                    is_sales
                ) VALUES (
                    :first_name,
                    :last_name,
                    :employee_number,
                    :is_sales
                ) RETURNING employee_id
            ";

            $params = [
                ':first_name' => $data['first_name'],
                ':last_name' => $data['last_name'],
                ':employee_number' => $data['employee_number'],
                ':is_sales' => $data['is_sales'] ?? false
            ];

            error_log("Executing employee insert with params: " . json_encode($params));
            $stmt = $this->db->executeQuery($query, $params);
            $employeeId = $this->db->fetchRow($stmt)['employee_id'];
            error_log("Created employee with ID: " . $employeeId);

            // Insert into employee_employment table
            $query = "
                INSERT INTO core.employee_employment (
                    employee_id,
                    employment_status,
                    hire_date
                ) VALUES (
                    :employee_id,
                    'active',
                    CURRENT_DATE
                )
            ";

            $employmentParams = [':employee_id' => $employeeId];
            error_log("Executing employment insert with params: " . json_encode($employmentParams));
            $this->db->executeQuery($query, $employmentParams);
            error_log("Created employment record for employee ID: " . $employeeId);

            $this->db->commit();
            error_log("Transaction committed successfully");
            return ['success' => true, 'employee_id' => $employeeId];

        } catch (PDOException $e) {
            $this->db->rollBack();
            error_log("Error creating employee: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return [
                'success' => false, 
                'message' => 'Failed to create employee: ' . $e->getMessage()
            ];
        } catch (Exception $e) {
            $this->db->rollBack();
            error_log("Unexpected error creating employee: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return [
                'success' => false, 
                'message' => 'An unexpected error occurred: ' . $e->getMessage()
            ];
        }
    }
} 