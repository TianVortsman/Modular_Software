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
        // Get all shifts for the account
        $stmt = $conn->prepare("SELECT * FROM shifts WHERE account_number = :account_number");
        $stmt->execute(['account_number' => $account_number]);
        
        $shifts = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($shifts);
        break;

    case 'POST':
        // Create or update shift
        $data = json_decode(file_get_contents('php://input'), true);
        
        if (!isset($data['name']) || !isset($data['startTime']) || !isset($data['endTime'])) {
            http_response_code(400);
            echo json_encode(['error' => 'Missing required fields']);
            exit;
        }

        if (isset($data['id'])) {
            // Update existing shift
            $stmt = $conn->prepare("UPDATE shifts SET name = :name, start_time = :start_time, end_time = :end_time, break_time = :break_time, color = :color WHERE id = :id AND account_number = :account_number");
            $params = [
                'name' => $data['name'],
                'start_time' => $data['startTime'],
                'end_time' => $data['endTime'],
                'break_time' => $data['breakTime'],
                'color' => $data['color'],
                'id' => $data['id'],
                'account_number' => $account_number
            ];
        } else {
            // Create new shift
            $stmt = $conn->prepare("INSERT INTO shifts (name, start_time, end_time, break_time, color, account_number) VALUES (:name, :start_time, :end_time, :break_time, :color, :account_number)");
            $params = [
                'name' => $data['name'],
                'start_time' => $data['startTime'],
                'end_time' => $data['endTime'],
                'break_time' => $data['breakTime'],
                'color' => $data['color'],
                'account_number' => $account_number
            ];
        }

        if ($stmt->execute($params)) {
            $shift_id = isset($data['id']) ? $data['id'] : $conn->lastInsertId();
            echo json_encode([
                'success' => true,
                'id' => $shift_id
            ]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to save shift']);
        }
        break;

    case 'DELETE':
        // Delete shift
        $shift_id = $_GET['id'] ?? null;
        if (!$shift_id) {
            http_response_code(400);
            echo json_encode(['error' => 'Shift ID not provided']);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM shifts WHERE id = :id AND account_number = :account_number");
        $stmt->execute([
            'id' => $shift_id,
            'account_number' => $account_number
        ]);
        
        if ($stmt->rowCount() > 0) {
            echo json_encode(['success' => true]);
        } else {
            http_response_code(500);
            echo json_encode(['error' => 'Failed to delete shift']);
        }
        break;

    default:
        http_response_code(405);
        echo json_encode(['error' => 'Method not allowed']);
        break;
} 