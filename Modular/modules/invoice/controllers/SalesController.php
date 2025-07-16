<?php
namespace App\modules\invoice\controllers;

use PDO;
use Exception;

require_once __DIR__ . '/../../../src/Helpers/helpers.php';

/**
 * List all salespeople (employees with is_sales = true)
 */
function list_salespeople(): array {
    global $conn;
    try {
        $sql = "SELECT employee_id, employee_first_name, employee_last_name, employee_number, clock_number FROM core.employees WHERE is_sales = TRUE ORDER BY employee_last_name, employee_first_name";
        $stmt = $conn->query($sql);
        $salespeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [
            'success' => true,
            'message' => 'Salespeople retrieved successfully',
            'data' => $salespeople
        ];
    } catch (Exception $e) {
        $msg = '[list_salespeople] ' . $e->getMessage();
        error_log($msg);
        log_user_action(null, 'list_salespeople', null, $msg);
        return [
            'success' => false,
            'message' => 'Failed to retrieve salespeople',
            'data' => null,
            'error_code' => 'SALESPEOPLE_LIST_ERROR'
        ];
    }
}
function search_salesperson(string $search = ''): array {
    global $conn;
    try {
        $sql = "SELECT employee_id, employee_first_name, employee_last_name 
                FROM core.employees 
                WHERE is_sales = TRUE";
        $params = [];
        if (!empty($search)) {
            $sql .= " AND (employee_first_name ILIKE :search OR employee_last_name ILIKE :search OR CAST(employee_id AS TEXT) ILIKE :search)";
            $params[':search'] = '%' . $search . '%';
        }
        $sql .= " ORDER BY employee_last_name, employee_first_name";
        $stmt = $conn->prepare($sql);
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }
        $stmt->execute();
        $salespeople = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return [
            'success' => true,
            'message' => 'Salespeople search successful',
            'data' => $salespeople
        ];
    } catch (Exception $e) {
        $msg = '[search_salesperson] ' . $e->getMessage();
        error_log($msg);
        log_user_action(null, 'search_salesperson', null, $msg);
        return [
            'success' => false,
            'message' => 'Failed to search salespeople',
            'data' => null,
            'error_code' => 'SALESPERSON_SEARCH_ERROR'
        ];
    }
}

