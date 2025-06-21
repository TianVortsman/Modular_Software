<?php
require_once '../config/database.php';
require_once '../utils/response.php';

// Use the correct namespace for Database class
use App\Config\Database;

// Get action from request
$action = $_GET['action'] ?? '';
$account = $_GET['account'] ?? '';

// Validate account number
if (empty($account)) {
    httpResponse(400, 'Missing account number');
    exit;
}

// Connect to the specific customer database
try {
    // Get the client configuration
    $config = Database::getClientConfig($account);
    
    // Create PDO connection
    $dsn = "pgsql:host={$config['host']};port={$config['port']};dbname={$config['dbname']}";
    $conn = new PDO($dsn, $config['username'], $config['password']);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (Exception $e) {
    httpResponse(500, 'Database connection error: ' . $e->getMessage());
    exit;
}

// Handle different actions
switch ($action) {
    case 'get_recent_activity':
        getRecentActivity($conn);
        break;
    
    case 'get_access_activity':
        getAccessActivity($conn);
        break;
    
    case 'get_dashboard_stats':
        getDashboardStats($conn);
        break;
    
    // Add other actions as needed
    
    default:
        httpResponse(400, 'Invalid action');
        break;
}

/**
 * Get recent activity from attendance_records and unknown_clockings
 */
function getRecentActivity($conn) {
    try {
        // First, get recent attendance records
        $attendanceQuery = "
            SELECT 
                ta.attendance_id as id,
                ta.attendance_date as date_time,
                tc.clocking_time,
                tc.clocking_method as device_id,
                tc.clocking_type as verify_mode,
                tc.clocking_status as verify_status,
                tc.clocking_type as major_event_type,
                tc.clocking_method as minor_event_type,
                ta.attendance_status as status,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name
            FROM 
                time.time_attendance ta
            LEFT JOIN 
                core.employees e ON ta.employee_id = e.employee_id
            LEFT JOIN
                time.time_clocking tc ON ta.employee_id = tc.employee_id AND ta.attendance_date = tc.clocking_date
            ORDER BY 
                ta.attendance_date DESC, tc.clocking_time DESC
            LIMIT 20
        ";
        
        $attendanceStmt = $conn->prepare($attendanceQuery);
        $attendanceStmt->execute();
        $attendanceRecords = $attendanceStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Next, get recent unknown clockings
        $unknownQuery = "
            SELECT 
                uc.id,
                uc.date_time,
                uc.clock_number,
                uc.device_id,
                uc.verify_mode,
                uc.verify_status,
                uc.major_event_type,
                uc.minor_event_type,
                'Unknown' as status,
                NULL as employee_name
            FROM 
                time.unknown_clockings uc
            ORDER BY 
                uc.date_time DESC
            LIMIT 20
        ";
        
        $unknownStmt = $conn->prepare($unknownQuery);
        $unknownStmt->execute();
        $unknownRecords = $unknownStmt->fetchAll(PDO::FETCH_ASSOC);
        
        // Combine and sort records
        $allRecords = array_merge($attendanceRecords, $unknownRecords);
        
        // Sort by date_time descending
        usort($allRecords, function($a, $b) {
            return strtotime($b['date_time']) - strtotime($a['date_time']);
        });
        
        // Take only the most recent 30 records
        $recentActivities = array_slice($allRecords, 0, 30);
        
        // Return success response
        httpResponse(200, 'Recent activity retrieved successfully', [
            'success' => true,
            'activities' => $recentActivities
        ]);
    } catch (PDOException $e) {
        httpResponse(500, 'Database error: ' . $e->getMessage());
    }
}

/**
 * Get dashboard statistics for Time and Attendance
 */
function getDashboardStats($conn) {
    try {
        // Current date for today's stats
        $today = date('Y-m-d');
        
        // Get total employees
        $employeeQuery = "
            SELECT COUNT(*) as total 
            FROM core.employees e
            LEFT JOIN core.employee_employment ee ON e.employee_id = ee.employee_id
            WHERE ee.status = 'active'
        ";
        $employeeStmt = $conn->prepare($employeeQuery);
        $employeeStmt->execute();
        $totalEmployees = $employeeStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get clocked in today count
        $clockedInQuery = "
            SELECT COUNT(DISTINCT employee_id) as total 
            FROM time.time_attendance 
            WHERE attendance_date = :today 
            AND attendance_status = 'Present'
        ";
        $clockedInStmt = $conn->prepare($clockedInQuery);
        $clockedInStmt->bindParam(':today', $today);
        $clockedInStmt->execute();
        $clockedInToday = $clockedInStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get late arrivals today
        $lateQuery = "
            SELECT COUNT(DISTINCT ta.employee_id) as total 
            FROM time.time_attendance ta
            JOIN core.employees e ON ta.employee_id = e.employee_id
            JOIN time.time_clocking tc ON ta.employee_id = tc.employee_id AND ta.attendance_date = tc.clocking_date
            WHERE ta.attendance_date = :today
            AND tc.clocking_time > '08:00:00'::time
            AND ta.attendance_status = 'Present'
        ";
        $lateStmt = $conn->prepare($lateQuery);
        $lateStmt->bindParam(':today', $today);
        $lateStmt->execute();
        $lateArrivals = $lateStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get average check-in time for today
        $avgTimeQuery = "
            SELECT TO_CHAR(AVG(tc.clocking_time::time), 'HH24:MI') as avg_time
            FROM time.time_clocking tc
            WHERE tc.clocking_date = :today
            AND tc.clocking_time IS NOT NULL
        ";
        $avgTimeStmt = $conn->prepare($avgTimeQuery);
        $avgTimeStmt->bindParam(':today', $today);
        $avgTimeStmt->execute();
        $avgCheckinTime = $avgTimeStmt->fetch(PDO::FETCH_ASSOC)['avg_time'] ?? '--:--';
        
        // Calculate absences (employees who haven't clocked in today)
        $absentQuery = "
            SELECT COUNT(*) as total
            FROM core.employees e
            JOIN core.employee_employment ee ON e.employee_id = ee.employee_id
            WHERE ee.status = 'active'
            AND e.employee_id NOT IN (
                SELECT DISTINCT employee_id
                FROM time.time_attendance
                WHERE attendance_date = :today
            )
        ";
        $absentStmt = $conn->prepare($absentQuery);
        $absentStmt->bindParam(':today', $today);
        $absentStmt->execute();
        $absentToday = $absentStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Build the response data
        $stats = [
            'total_employees' => (int)$totalEmployees,
            'clocked_in_today' => (int)$clockedInToday,
            'late_arrivals' => (int)$lateArrivals,
            'average_checkin' => $avgCheckinTime,
            'absent_today' => (int)$absentToday
        ];
        
        httpResponse(200, 'Dashboard stats retrieved successfully', [
            'success' => true,
            'stats' => $stats
        ]);
    } catch (PDOException $e) {
        httpResponse(500, 'Database error: ' . $e->getMessage());
    }
}

/**
 * Get recent access control activity
 */
function getAccessActivity($conn) {
    try {
        $query = "
            SELECT 
                ae.id,
                ae.date_time,
                ae.device_id,
                ae.major_event_type,
                ae.minor_event_type,
                ae.raw_data,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name
            FROM 
                access.access_events ae
            LEFT JOIN 
                core.employees e ON ae.raw_data->>'employee_id' = e.employee_id::text
            ORDER BY 
                ae.date_time DESC
            LIMIT 20
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        httpResponse(200, 'Access activity retrieved successfully', [
            'success' => true,
            'activities' => $activities
        ]);
    } catch (PDOException $e) {
        httpResponse(500, 'Database error: ' . $e->getMessage());
    }
} 