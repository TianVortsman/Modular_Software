<?php
header('Content-Type: application/json');
require_once __DIR__ . '/../Core/Database/MainDatabase.php';
use App\Core\Database\MainDatabase;

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method not allowed']);
    exit;
}

// Parse JSON input
$input = json_decode(file_get_contents('php://input'), true);
$name = trim($input['name'] ?? '');
$email = trim($input['email'] ?? '');
$password = $input['password'] ?? '';
$role = (int)($input['role'] ?? 1);

// Validate
if (!$name || !$email || !$password || !$role) {
    echo json_encode(['success' => false, 'error' => 'All fields are required.']);
    exit;
}
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    echo json_encode(['success' => false, 'error' => 'Invalid email address.']);
    exit;
}
if (strlen($password) < 6) {
    echo json_encode(['success' => false, 'error' => 'Password must be at least 6 characters.']);
    exit;
}

try {
    $db = MainDatabase::getInstance();
    $conn = $db->connect();
    if (!$conn) {
        throw new Exception('Database connection failed.');
    }
    // Check for duplicate email
    $check = $conn->prepare('SELECT id FROM technicians WHERE email = ?');
    $check->execute([$email]);
    if ($check->fetch()) {
        echo json_encode(['success' => false, 'error' => 'A technician with this email already exists.']);
        exit;
    }
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
    $sql = 'INSERT INTO technicians (email, password, name, role, created_at) VALUES (?, ?, ?, ?, NOW())';
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([$email, $hashedPassword, $name, $role]);
    if ($result) {
        echo json_encode(['success' => true, 'message' => 'Technician added successfully!']);
    } else {
        $errorInfo = $stmt->errorInfo();
        echo json_encode(['success' => false, 'error' => $errorInfo[2] ?? 'Unknown error']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
} 