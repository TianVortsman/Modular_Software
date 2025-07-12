<?php
// invoice_template_pref.php
header('Content-Type: application/json');
require_once __DIR__ . '/../../../src/Core/Database/ClientDatabase.php';
use App\Core\Database\ClientDatabase;

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    if (!isset($_SESSION['account_number'])) {
        throw new Exception('User session not found');
    }
    $db = ClientDatabase::getInstance($_SESSION['account_number'], $_SESSION['user_name'] ?? 'Guest');
    $pdo = $db->connect();
    $action = $_GET['action'] ?? '';
    switch ($action) {
        case 'get':
            $stmt = $pdo->prepare('SELECT template_name FROM settings.invoice_settings WHERE id = 1');
            $stmt->execute();
            $row = $stmt->fetch();
            echo json_encode(['success' => true, 'template' => $row ? $row['template_name'] : null]);
            break;
        case 'save':
            $data = json_decode(file_get_contents('php://input'), true);
            $template = $data['template'] ?? '';
            if (!in_array($template, ['modern-blue','classic-grey','elegant-dark','modern-clean'])) {
                throw new Exception('Invalid template');
            }
            $stmt = $pdo->prepare('UPDATE settings.invoice_settings SET template_name = :tpl, updated_at = NOW() WHERE id = 1');
            $stmt->execute(['tpl' => $template]);
            echo json_encode(['success' => true]);
            break;
        default:
            throw new Exception('Invalid action');
    }
} catch (Exception $e) {
    error_log('[INVOICE TEMPLATE PREF ERROR] ' . $e->getMessage());
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}