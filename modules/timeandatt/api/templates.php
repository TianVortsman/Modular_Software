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
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);
    if (!isset($data['csrf_token']) || $data['csrf_token'] !== $_SESSION['csrf_token']) {
        http_response_code(403);
        echo json_encode(['error' => 'Invalid CSRF token']);
        exit;
    }
}

// Get account number from session
$account_number = $_SESSION['account_number'] ?? null;
if (!$account_number) {
    http_response_code(400);
    echo json_encode(['error' => 'Account number not found']);
    exit;
}

switch ($_SERVER['REQUEST_METHOD']) {
    case 'GET':
        // Get all templates for the account
        $stmt = $conn->prepare("SELECT * FROM schedule_templates WHERE account_number = ?");
        $stmt->bind_param("s", $account_number);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $templates = [];
        while ($row = $result->fetch_assoc()) {
            $template = [
                'id' => $row['id'],
                'name' => $row['name'],
                'shifts' => json_decode($row['shifts'], true)
            ];
            $templates[] = $template;
        }
        
        echo json_encode($templates);
        break;

    case 'POST':
        // Create or update template
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name']) || !isset($data['shifts'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        if (isset($data['id'])) {
            // Update existing template
            $stmt = $conn->prepare("UPDATE schedule_templates SET name = ?, shifts = ? WHERE id = ? AND account_number = ?");
            $shifts_json = json_encode($data['shifts']);
            $stmt->bind_param("ssis", $data['name'], $shifts_json, $data['id'], $account_number);
        } else {
            // Create new template
            $stmt = $conn->prepare("INSERT INTO schedule_templates (name, shifts, account_number) VALUES (?, ?, ?)");
            $shifts_json = json_encode($data['shifts']);
            $stmt->bind_param("sss", $data['name'], $shifts_json, $account_number);
        }

        if ($stmt->execute()) {
            $template_id = isset($data['id']) ? $data['id'] : $conn->insert_id;
            echo json_encode([
                'success' => true,
                'id' => $template_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save template']);
        }
        break;

    case 'DELETE':
        // Delete template
        $template_id = $_GET['id'] ?? null;
        if (!$template_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Template ID not provided']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM schedule_templates WHERE id = ? AND account_number = ?");
        $stmt->bind_param("is", $template_id, $account_number);
        
        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete template']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
} 