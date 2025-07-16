<?php
namespace App\modules\time_and_attendance\controllers;

use PDO;
use Exception;

require_once __DIR__ . '/../../../src/Helpers/helpers.php';


/**
 * List employees with optional filters (status, department, etc.)
 * @param array $options
 * @return array
 */
function list_employees(array $options = []): array {
    global $conn;
    $where = [];
    $params = [];

    // Filtering
    if (!empty($options['status'])) {
        $where[] = "e.status = :status";
        $params[':status'] = $options['status'];
    }
    if (!empty($options['department_id'])) {
        $where[] = "e.department_id = :department_id";
        $params[':department_id'] = $options['department_id'];
    }
    if (!empty($options['search'])) {
        $where[] = "(e.first_name ILIKE :search OR e.last_name ILIKE :search OR e.employee_number ILIKE :search)";
        $params[':search'] = '%' . $options['search'] . '%';
    }

    $sql = "SELECT 
                e.employee_id,
                e.employee_number,
                e.first_name,
                e.last_name,
                e.status,
                e.department_id,
                e.position_id,
                e.email,
                e.cell,
                e.hire_date,
                e.termination_date,
                e.created_at,
                e.updated_at
            FROM core.employees e";
    if ($where) {
        $sql .= " WHERE " . implode(' AND ', $where);
    }
    $sql .= " ORDER BY e.last_name, e.first_name";

    try {
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [
            'success' => true,
            'data' => $employees
        ];
    } catch (\PDOException $e) {
        error_log("list_employees error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to fetch employees',
            'data' => null,
            'error_code' => 'EMPLOYEE_LIST_ERROR'
        ];
    }
}



/**
 * Get details for a single employee
 * @param int $employee_id
 * @return array
 */
function get_employee_details(int $employee_id): array {
    global $conn;
    $sql = "SELECT 
                e.*,
                d.department_name,
                p.position_name
            FROM core.employees e
            LEFT JOIN core.departments d ON e.department_id = d.department_id
            LEFT JOIN core.positions p ON e.position_id = p.position_id
            WHERE e.employee_id = :employee_id";
    try {
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':employee_id', $employee_id, PDO::PARAM_INT);
        $stmt->execute();
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$employee) {
            return [
                'success' => false,
                'message' => 'Employee not found',
                'data' => null,
                'error_code' => 'EMPLOYEE_NOT_FOUND'
            ];
        }
        return [
            'success' => true,
            'data' => $employee
        ];
    } catch (\PDOException $e) {
        error_log("get_employee_details error: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to fetch employee details',
            'data' => null,
            'error_code' => 'EMPLOYEE_DETAILS_ERROR'
        ];
    }
}

/**
 * Create a new employee
 * @param array $data
 * @return array
 */
function create_employee(array $data): array {
    global $conn;
    $user_id = $data['created_by'] ?? ($_SESSION['tech_id'] ?? null);
    if (!check_user_permission($user_id, 'create_employee')) {
        $msg = "Permission denied for user $user_id to create employee";
        error_log($msg);
        log_user_action($user_id, 'create_employee', null, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'PERMISSION_DENIED'
        ];
    }
    try {
        $conn->beginTransaction();
        $fields = [
            'employee_number',
            'first_name',
            'last_name',
            'status',
            'department_id',
            'position_id',
            'email',
            'cell',
            'hire_date',
            'termination_date'
        ];
        $insertFields = [];
        $insertValues = [];
        $params = [];
        foreach ($fields as $field) {
            if (isset($data[$field])) {
                $insertFields[] = $field;
                $insertValues[] = ':' . $field;
                $params[':' . $field] = $data[$field];
            }
        }
        $sql = "INSERT INTO core.employees (" . implode(', ', $insertFields) . ")
                VALUES (" . implode(', ', $insertValues) . ")";
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $employee_id = $conn->lastInsertId();
        $conn->commit();
        log_user_action($user_id, 'create_employee', $employee_id, json_encode($data));
        send_notification($user_id, "Employee #$employee_id created successfully.");
        return [
            'success' => true,
            'message' => 'Employee created successfully',
            'data' => ['employee_id' => $employee_id]
        ];
    } catch (\PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $msg = "create_employee error: " . $e->getMessage();
        error_log($msg);
        log_user_action($user_id, 'create_employee', null, $msg);
        return [
            'success' => false,
            'message' => 'Failed to create employee',
            'data' => null,
            'error_code' => 'EMPLOYEE_CREATE_ERROR'
        ];
    }
}

/**
 * Update an existing employee
 * @param int $employee_id
 * @param array $data
 * @return array
 */
function update_employee(int $employee_id, array $data): array {
    global $conn;
    $user_id = $data['updated_by'] ?? ($_SESSION['tech_id'] ?? null);
    if (!check_user_permission($user_id, 'update_employee', $employee_id)) {
        $msg = "Permission denied for user $user_id to update employee $employee_id";
        error_log($msg);
        log_user_action($user_id, 'update_employee', $employee_id, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'PERMISSION_DENIED'
        ];
    }
    try {
        $conn->beginTransaction();
        $fields = [
            'employee_number',
            'first_name',
            'last_name',
            'status',
            'department_id',
            'position_id',
            'email',
            'cell',
            'hire_date',
            'termination_date'
        ];
        $updateFields = [];
        $params = [];
        foreach ($fields as $field) {
            if (array_key_exists($field, $data)) {
                $updateFields[] = "$field = :$field";
                $params[":$field"] = $data[$field];
            }
        }
        if (!empty($updateFields)) {
            $sql = "UPDATE core.employees SET " . implode(', ', $updateFields) . ", updated_at = CURRENT_TIMESTAMP WHERE employee_id = :employee_id";
            $params[':employee_id'] = $employee_id;
            $stmt = $conn->prepare($sql);
            foreach ($params as $key => $val) {
                $stmt->bindValue($key, $val);
            }
            $stmt->execute();
        }
        $conn->commit();
        log_user_action($user_id, 'update_employee', $employee_id, json_encode($data));
        send_notification($user_id, "Employee #$employee_id updated successfully.");
        return [
            'success' => true,
            'message' => 'Employee updated successfully',
            'data' => ['employee_id' => $employee_id]
        ];
    } catch (\PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $msg = "update_employee error: " . $e->getMessage();
        error_log($msg);
        log_user_action($user_id, 'update_employee', $employee_id, $msg);
        return [
            'success' => false,
            'message' => 'Failed to update employee',
            'data' => null,
            'error_code' => 'EMPLOYEE_UPDATE_ERROR'
        ];
    }
}

/**
 * Delete (soft delete) an employee
 * @param int $employee_id
 * @param int $deleted_by
 * @return array
 */
function delete_employee(int $employee_id, int $deleted_by): array {
    $user_id = $deleted_by ?? ($_SESSION['tech_id'] ?? null);
    if (!check_user_permission($user_id, 'delete_employee', $employee_id)) {
        $msg = "Permission denied for user $user_id to delete employee $employee_id";
        error_log($msg);
        log_user_action($user_id, 'delete_employee', $employee_id, $msg);
        return [
            'success' => false,
            'message' => $msg,
            'data' => null,
            'error_code' => 'PERMISSION_DENIED'
        ];
    }
    global $conn;
    try {
        $conn->beginTransaction();
        // Check for linked attendance/clocking records
        $sqlCheck = "SELECT COUNT(*) FROM time.time_attendance WHERE employee_id = :employee_id";
        $stmtCheck = $conn->prepare($sqlCheck);
        $stmtCheck->bindValue(':employee_id', $employee_id, PDO::PARAM_INT);
        $stmtCheck->execute();
        $attendanceCount = (int)$stmtCheck->fetchColumn();
        if ($attendanceCount > 0) {
            $msg = 'Employee has linked attendance records, cannot delete';
            error_log($msg);
            log_user_action($user_id, 'delete_employee', $employee_id, $msg);
            return [
                'success' => false,
                'message' => $msg,
                'data' => null,
                'error_code' => 'EMPLOYEE_LINKED_ATTENDANCE'
            ];
        }
        $sqlDelete = "UPDATE core.employees SET status = 'Inactive', deleted_at = NOW() WHERE employee_id = :employee_id";
        $stmtDelete = $conn->prepare($sqlDelete);
        $stmtDelete->bindValue(':employee_id', $employee_id, PDO::PARAM_INT);
        $stmtDelete->execute();
        $conn->commit();
        log_user_action($user_id, 'delete_employee', $employee_id);
        send_notification($user_id, "Employee #$employee_id deleted.");
        return [
            'success' => true,
            'message' => 'Employee deleted successfully',
            'data' => ['employee_id' => $employee_id]
        ];
    } catch (\PDOException $e) {
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $msg = "delete_employee error: " . $e->getMessage();
        error_log($msg);
        log_user_action($user_id, 'delete_employee', $employee_id, $msg);
        return [
            'success' => false,
            'message' => 'Failed to delete employee',
            'data' => null,
            'error_code' => 'EMPLOYEE_DELETE_ERROR'
        ];
    }
}

