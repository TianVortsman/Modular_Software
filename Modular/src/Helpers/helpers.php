<?php
/**
 * Checks if a user has permission for a given action in the invoicing or product module.
 * If the user is a technician (tech_logged_in), always returns true (master permission).
 * Product actions are mapped to invoicing permission columns for now.
 * @param int $user_id
 * @param string $action (e.g. 'view', 'create', 'edit', 'delete', 'finalize', 'approve', 'view_product', 'create_product', ...)
 * @param int|null $resource_id (optional, for future resource-level checks)
 * @return bool
 */
function check_user_permission($user_id, $action, $resource_id = null) {
    global $conn;
    // Master permission for technicians
    if (!empty($_SESSION['tech_logged_in'])) {
        return true;
    }
    // Map product actions to invoicing permission columns
    $colMap = [
        'view' => 'can_view',
        'create' => 'can_create',
        'edit' => 'can_edit',
        'delete' => 'can_delete',
        'finalize' => 'can_finalize',
        'approve' => 'can_approve',
        // Product-specific actions mapped to invoicing permissions
        'view_product' => 'can_view',
        'create_product' => 'can_create',
        'update_product' => 'can_edit',
        'delete_product' => 'can_delete',
        'finalize_product' => 'can_finalize',
        'approve_product' => 'can_approve',
        // Legacy mappings for document actions
        'create_document' => 'can_create',
        'update_document' => 'can_edit',
        'delete_document' => 'can_delete',
        'change_document_status' => 'can_edit',
    ];
    $col = $colMap[$action] ?? null;
    if (!$col) return false;
    try {
        $sql = "SELECT $col FROM Permissions.invoicing WHERE user_id = :user_id";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->execute();
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return !empty($result) && !empty($result[$col]);
    } catch (Exception $e) {
        error_log("check_user_permission error: " . $e->getMessage());
        return false;
    }
}

/**
 * Sends a notification to a user (core.notifications table)
 * @param int $user_id
 * @param string $message
 * @param string $title (optional)
 * @param string $module (optional)
 * @param string $type (optional)
 * @param int|null $related_id (optional)
 * @param string|null $related_type (optional)
 * @param string|null $url (optional)
 */
function send_notification($user_id, $message, $title = 'Notification', $module = 'invoicing', $type = 'info', $related_id = null, $related_type = null, $url = null) {
    global $conn;
    try {
        $sql = "INSERT INTO core.notifications (user_id, module, type, title, message, url, related_id, related_type) VALUES (:user_id, :module, :type, :title, :message, :url, :related_id, :related_type)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':module', $module);
        $stmt->bindValue(':type', $type);
        $stmt->bindValue(':title', $title);
        $stmt->bindValue(':message', $message);
        $stmt->bindValue(':url', $url);
        $stmt->bindValue(':related_id', $related_id, PDO::PARAM_INT);
        $stmt->bindValue(':related_type', $related_type);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("send_notification error: " . $e->getMessage());
    }
}

/**
 * Logs a user action to the audit.user_actions table
 * @param int $user_id
 * @param string $action
 * @param int|null $related_id
 * @param mixed $details (optional, can be string or array)
 * @param string $module (default 'invoicing')
 * @param string $related_type (optional)
 * @param array $old_data (optional)
 * @param array $new_data (optional)
 */
function log_user_action($user_id, $action, $related_id = null, $details = null, $module = 'invoicing', $related_type = 'invoice', $old_data = null, $new_data = null) {
    global $conn;
    // Ensure user_id is set from session if not provided
    if (empty($user_id) && isset($_SESSION['user_id'])) {$user_id = $_SESSION['user_id']; }
    $tech_id = isset($_SESSION['tech_id']) ? $_SESSION['tech_id'] : null;
    try {
        $sql = "INSERT INTO audit.user_actions (user_id, tech_id, module, action, related_type, related_id, old_data, new_data, details, ip_address, user_agent, session_id) VALUES (:user_id, :tech_id, :module, :action, :related_type, :related_id, :old_data, :new_data, :details, :ip_address, :user_agent, :session_id)";
        $stmt = $conn->prepare($sql);
        $stmt->bindValue(':user_id', $user_id, PDO::PARAM_INT);
        $stmt->bindValue(':tech_id', $tech_id, PDO::PARAM_INT);
        $stmt->bindValue(':module', $module);
        $stmt->bindValue(':action', $action);
        $stmt->bindValue(':related_type', $related_type);
        $stmt->bindValue(':related_id', $related_id, PDO::PARAM_INT);
        $stmt->bindValue(':old_data', $old_data ? json_encode($old_data) : null);
        $stmt->bindValue(':new_data', $new_data ? json_encode($new_data) : null);
        $stmt->bindValue(':details', is_array($details) ? json_encode($details) : $details);
        $stmt->bindValue(':ip_address', $_SERVER['REMOTE_ADDR'] ?? null);
        $stmt->bindValue(':user_agent', $_SERVER['HTTP_USER_AGENT'] ?? null);
        $stmt->bindValue(':session_id', session_id() ?: null);
        $stmt->execute();
    } catch (Exception $e) {
        error_log("log_user_action error: " . $e->getMessage());
    }
}

/**
 * Returns a user-friendly error message using the AI error handler
 * @param string $error
 * @return string
 */
function get_friendly_error($error) {
    require_once __DIR__ . '/../Utils/errorHandler.php';
    $aiMessage = getFriendlyMessageFromAI($error);
    return $aiMessage ?: 'Please contact Modular Software Support.';
}
