<?php
/**
 * Notifications API Endpoint
 * 
 * This API provides notification data and actions for the Time and Attendance module
 * It handles fetching notifications, marking them as read, and getting notification counts
 */

// Include necessary files
require_once '../../../config/config.php';
require_once '../../../php/auth_check.php';
require_once '../../../php/db_connect.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Get the action parameter
$action = isset($_GET['action']) ? $_GET['action'] : 'list';

// Handle different actions
switch ($action) {
    case 'list':
        // Get notifications list
        getNotifications();
        break;
    case 'count':
        // Get unread notification count
        getNotificationCount();
        break;
    case 'mark_read':
        // Mark a notification as read
        markNotificationAsRead();
        break;
    case 'mark_all_read':
        // Mark all notifications as read
        markAllNotificationsAsRead();
        break;
    default:
        // Invalid action
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid action'
        ]);
        exit;
}

/**
 * Get notifications list
 */
function getNotifications() {
    global $conn;
    
    // Get request parameters
    $tab = isset($_GET['tab']) ? $_GET['tab'] : 'all';
    $page = isset($_GET['page']) ? intval($_GET['page']) : 1;
    $limit = 10;
    $offset = ($page - 1) * $limit;
    
    // Get user and account info
    $userId = $_SESSION['user_id'];
    $accountId = $_SESSION['account_id'] ?? 1;
    
    // In a real implementation, this would query the database
    // For now, we'll return sample data
    
    // Sample query (commented out)
    /*
    $whereClause = "WHERE n.account_id = ?";
    $params = [$accountId];
    $types = "i";
    
    if ($tab === 'unread') {
        $whereClause .= " AND n.is_read = 0";
    } else if ($tab === 'system') {
        $whereClause .= " AND n.type = 'system'";
    } else if ($tab === 'alerts') {
        $whereClause .= " AND n.type = 'alert'";
    }
    
    $query = "SELECT 
                n.id,
                n.title,
                n.message,
                n.created_at,
                n.is_read,
                n.type as source,
                n.action_url
              FROM notifications n
              $whereClause
              ORDER BY n.created_at DESC
              LIMIT ?, ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param($types . "ii", ...$params, $offset, $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $notifications = [];
    while ($row = $result->fetch_assoc()) {
        $notifications[] = $row;
    }
    
    // Get total count for pagination
    $countQuery = "SELECT COUNT(*) as total FROM notifications n $whereClause";
    $stmt = $conn->prepare($countQuery);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $countResult = $stmt->get_result();
    $totalCount = $countResult->fetch_assoc()['total'];
    
    $hasMore = ($offset + $limit) < $totalCount;
    */
    
    // Sample data for demonstration
    $notifications = [];
    $hasMore = false;
    
    // Generate different notifications based on the tab
    if ($tab === 'all' || $tab === 'unread') {
        $notifications = array_merge($notifications, [
            [
                'id' => 1,
                'title' => 'Late Arrival Alert',
                'message' => 'John Smith was 15 minutes late today',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 hours')),
                'is_read' => false,
                'source' => 'alert',
                'action_url' => '/modular1/modules/timeandatt/employee-details.php?id=1'
            ],
            [
                'id' => 2,
                'title' => 'Absence Report',
                'message' => '5 employees are absent today',
                'created_at' => date('Y-m-d H:i:s', strtotime('-5 hours')),
                'is_read' => $tab === 'all',
                'source' => 'system',
                'action_url' => '/modular1/modules/timeandatt/dashboard-TA.php'
            ]
        ]);
    }
    
    if ($tab === 'all' || $tab === 'system') {
        $notifications = array_merge($notifications, [
            [
                'id' => 3,
                'title' => 'Overtime Approval Needed',
                'message' => '3 overtime requests need your approval',
                'created_at' => date('Y-m-d H:i:s', strtotime('-1 day')),
                'is_read' => true,
                'source' => 'system',
                'action_url' => '/modular1/modules/timeandatt/overtime-approval.php'
            ],
            [
                'id' => 4,
                'title' => 'Weekly Attendance Report',
                'message' => 'Weekly attendance report is now available',
                'created_at' => date('Y-m-d H:i:s', strtotime('-2 days')),
                'is_read' => true,
                'source' => 'system',
                'action_url' => '/modular1/modules/timeandatt/reports.php'
            ]
        ]);
    }
    
    if ($tab === 'all' || $tab === 'alerts') {
        $notifications = array_merge($notifications, [
            [
                'id' => 5,
                'title' => 'Excessive Absence Alert',
                'message' => 'Sarah Johnson has been absent 4 days this month',
                'created_at' => date('Y-m-d H:i:s', strtotime('-3 days')),
                'is_read' => true,
                'source' => 'alert',
                'action_url' => '/modular1/modules/timeandatt/employee-details.php?id=6'
            ],
            [
                'id' => 6,
                'title' => 'Overtime Threshold Exceeded',
                'message' => 'Carlos Sanchez has exceeded the overtime threshold',
                'created_at' => date('Y-m-d H:i:s', strtotime('-4 days')),
                'is_read' => false,
                'source' => 'alert',
                'action_url' => '/modular1/modules/timeandatt/employee-details.php?id=16'
            ]
        ]);
    }
    
    // If page > 1, add more sample notifications
    if ($page > 1) {
        for ($i = 1; $i <= 5; $i++) {
            $id = 6 + ($page - 2) * 5 + $i;
            $daysAgo = 4 + $i + ($page - 2) * 5;
            $notifications[] = [
                'id' => $id,
                'title' => 'Notification #' . $id,
                'message' => 'This is an older notification for testing pagination',
                'created_at' => date('Y-m-d H:i:s', strtotime("-$daysAgo days")),
                'is_read' => true,
                'source' => $i % 2 === 0 ? 'system' : 'alert',
                'action_url' => '#'
            ];
        }
        
        // Set hasMore to true for pages 2-3 to test pagination
        $hasMore = $page < 3;
    } else {
        // First page has more
        $hasMore = true;
    }
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'notifications' => $notifications,
        'has_more' => $hasMore
    ]);
    exit;
}

/**
 * Get unread notification count
 */
function getNotificationCount() {
    global $conn;
    
    // Get user and account info
    $userId = $_SESSION['user_id'];
    $accountId = $_SESSION['account_id'] ?? 1;
    
    // In a real implementation, this would query the database
    // For now, we'll return sample data
    
    // Sample query (commented out)
    /*
    $query = "SELECT COUNT(*) as count 
              FROM notifications 
              WHERE account_id = ? AND is_read = 0";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $accountId);
    $stmt->execute();
    $result = $stmt->get_result();
    $count = $result->fetch_assoc()['count'];
    */
    
    // Sample data for demonstration
    $count = 3;
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => true,
        'count' => $count
    ]);
    exit;
}

/**
 * Mark a notification as read
 */
function markNotificationAsRead() {
    global $conn;
    
    // Get notification ID
    $notificationId = isset($_GET['id']) ? intval($_GET['id']) : 0;
    
    if ($notificationId <= 0) {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => false,
            'message' => 'Invalid notification ID'
        ]);
        exit;
    }
    
    // Get user and account info
    $userId = $_SESSION['user_id'];
    $accountId = $_SESSION['account_id'] ?? 1;
    
    // In a real implementation, this would update the database
    // For now, we'll just return success
    
    // Sample query (commented out)
    /*
    $query = "UPDATE notifications 
              SET is_read = 1 
              WHERE id = ? AND account_id = ?";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $notificationId, $accountId);
    $stmt->execute();
    
    $success = $stmt->affected_rows > 0;
    */
    
    // Sample data for demonstration
    $success = true;
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'Notification marked as read' : 'Failed to mark notification as read'
    ]);
    exit;
}

/**
 * Mark all notifications as read
 */
function markAllNotificationsAsRead() {
    global $conn;
    
    // Get user and account info
    $userId = $_SESSION['user_id'];
    $accountId = $_SESSION['account_id'] ?? 1;
    
    // In a real implementation, this would update the database
    // For now, we'll just return success
    
    // Sample query (commented out)
    /*
    $query = "UPDATE notifications 
              SET is_read = 1 
              WHERE account_id = ? AND is_read = 0";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $accountId);
    $stmt->execute();
    
    $success = $stmt->affected_rows > 0;
    */
    
    // Sample data for demonstration
    $success = true;
    
    // Return JSON response
    header('Content-Type: application/json');
    echo json_encode([
        'success' => $success,
        'message' => $success ? 'All notifications marked as read' : 'No unread notifications to mark'
    ]);
    exit;
}
?> 