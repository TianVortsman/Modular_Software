<?php
session_start();
require_once('../../../php/db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

// Check CSRF token
$data = json_decode(file_get_contents('php://input'), true);
if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

// Get account number from session
$account_number = $_SESSION['account_number'] ?? null;
if (!$account_number) {
    http_response_code(400);
    echo json_encode(['error' => 'Account number not found']);
    exit;
}

// Validate input
if (!isset($data['templateId']) || !isset($data['employeeIds']) || !is_array($data['employeeIds'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Missing required fields']);
    exit;
}

// Start transaction
$conn->begin_transaction();

try {
    // First, remove existing assignments for these employees
    $stmt = $conn->prepare("DELETE FROM employee_schedules WHERE employee_id IN (" . implode(',', array_fill(0, count($data['employeeIds']), '?')) . ")");
    $types = str_repeat('i', count($data['employeeIds']));
    $stmt->bind_param($types, ...$data['employeeIds']);
    $stmt->execute();

    // Get template details
    $stmt = $conn->prepare("SELECT shifts FROM schedule_templates WHERE id = ? AND account_number = ?");
    $stmt->bind_param("is", $data['templateId'], $account_number);
    $stmt->execute();
    $result = $stmt->get_result();
    $template = $result->fetch_assoc();

    if (!$template) {
        throw new Exception('Template not found');
    }

    $shifts = json_decode($template['shifts'], true);

    // Insert new assignments
    $stmt = $conn->prepare("INSERT INTO employee_schedules (employee_id, template_id, shifts) VALUES (?, ?, ?)");
    $shifts_json = json_encode($shifts);

    foreach ($data['employeeIds'] as $employee_id) {
        $stmt->bind_param("iis", $employee_id, $data['templateId'], $shifts_json);
        $stmt->execute();
    }

    // Commit transaction
    $conn->commit();
    echo json_encode(['success' => true]);

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
} 