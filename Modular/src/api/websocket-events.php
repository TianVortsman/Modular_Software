<?php
/**
 * WebSocket Events API
 * Allows PHP modules to trigger events that will be processed by the WebSocket server
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../Config/Database.php';
require_once __DIR__ . '/../Utils/response.php';

use App\Config\Database;

header('Content-Type: application/json');

class WebSocketEventsAPI {
    private $db;
    private $websocketUrl;
    
    public function __construct() {
        $this->db = new Database();
        $this->websocketUrl = getenv('WEBSOCKET_SERVER_URL') ?: 'http://localhost:3001';
    }
    
    /**
     * Trigger a WebSocket event
     */
    public function triggerEvent($eventType, $eventData) {
        try {
            // Validate session
            if (!isset($_SESSION['account_number']) || !isset($_SESSION['user_id'])) {
                throw new Exception('User session not found');
            }
            
            $customerId = $_SESSION['account_number'];
            $userId = $_SESSION['user_id'];
            $sessionId = session_id();
            
            // Get connection token from WebSocket server
            $token = $this->getConnectionToken($customerId, $userId, $sessionId);
            
            // Connect to WebSocket server and emit event
            $result = $this->emitWebSocketEvent($token, $eventType, $eventData);
            
            return [
                'success' => true,
                'event_id' => $result['event_id'] ?? null,
                'message' => 'Event triggered successfully'
            ];
            
        } catch (Exception $e) {
            error_log('WebSocket event error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to trigger event: ' . $e->getMessage()
            ];
        }
    }
    
    /**
     * Get connection token from WebSocket server
     */
    private function getConnectionToken($customerId, $userId, $sessionId) {
        $url = $this->websocketUrl . '/auth/token';
        
        $data = [
            'customer_id' => $customerId,
            'user_id' => $userId,
            'session_id' => $sessionId
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Content-Length: ' . strlen(json_encode($data))
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to get connection token: HTTP ' . $httpCode);
        }
        
        $result = json_decode($response, true);
        if (!isset($result['token'])) {
            throw new Exception('Invalid token response from WebSocket server');
        }
        
        return $result['token'];
    }
    
    /**
     * Emit event via WebSocket server
     */
    private function emitWebSocketEvent($token, $eventType, $eventData) {
        // For now, we'll use a simple HTTP POST to the WebSocket server
        // In a production environment, you might want to use a proper WebSocket client
        
        $url = $this->websocketUrl . '/emit-event';
        
        $data = [
            'event_type' => $eventType,
            'event_data' => $eventData
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Authorization: Bearer ' . $token,
            'Content-Length: ' . strlen(json_encode($data))
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode !== 200) {
            throw new Exception('Failed to emit event: HTTP ' . $httpCode);
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Store event directly in database (fallback method)
     */
    public function storeEventDirectly($eventType, $eventData) {
        try {
            if (!isset($_SESSION['account_number'])) {
                throw new Exception('User session not found');
            }
            
            $customerId = $_SESSION['account_number'];
            $conn = $this->db->getConnection();
            
            $sql = "INSERT INTO websocket_events (customer_id, event_type, event_data, status) 
                    VALUES (:customer_id, :event_type, :event_data, 'pending') 
                    RETURNING id";
            
            $stmt = $conn->prepare($sql);
            $stmt->bindParam(':customer_id', $customerId);
            $stmt->bindParam(':event_type', $eventType);
            $stmt->bindParam(':event_data', json_encode($eventData));
            $stmt->execute();
            
            $eventId = $stmt->fetchColumn();
            
            return [
                'success' => true,
                'event_id' => $eventId,
                'message' => 'Event stored successfully'
            ];
            
        } catch (Exception $e) {
            error_log('Store event error: ' . $e->getMessage());
            return [
                'success' => false,
                'message' => 'Failed to store event: ' . $e->getMessage()
            ];
        }
    }
}

// Handle API requests
$api = new WebSocketEventsAPI();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    
    if (!$input) {
        errorResponse('Invalid JSON data');
        exit;
    }
    
    $eventType = $input['event_type'] ?? null;
    $eventData = $input['event_data'] ?? [];
    
    if (!$eventType) {
        errorResponse('Event type is required');
        exit;
    }
    
    // Try WebSocket server first, fallback to direct database storage
    $result = $api->triggerEvent($eventType, $eventData);
    
    if (!$result['success']) {
        // Fallback to direct database storage
        $result = $api->storeEventDirectly($eventType, $eventData);
    }
    
    if ($result['success']) {
        successResponse($result['message'], $result);
    } else {
        errorResponse($result['message']);
    }
    
} else {
    errorResponse('POST method required');
} 