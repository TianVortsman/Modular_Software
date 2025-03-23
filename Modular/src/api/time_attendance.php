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
                ar.attendance_id as id,
                ar.date_time,
                ar.clock_number,
                ar.device_id,
                ar.verify_mode,
                ar.verify_status,
                ar.major_event_type,
                ar.minor_event_type,
                ar.status,
                CONCAT(e.first_name, ' ', e.last_name) as employee_name
            FROM 
                attendance_records ar
            LEFT JOIN 
                employees e ON ar.employee_id = e.employee_id
            ORDER BY 
                ar.date_time DESC
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
                unknown_clockings uc
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
        $employeeQuery = "SELECT COUNT(*) as total FROM employees WHERE status = 'active'";
        $employeeStmt = $conn->prepare($employeeQuery);
        $employeeStmt->execute();
        $totalEmployees = $employeeStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get clocked in today count
        $clockedInQuery = "
            SELECT COUNT(DISTINCT employee_id) as total 
            FROM attendance_records 
            WHERE date = :today 
            AND status = 'Present'
        ";
        $clockedInStmt = $conn->prepare($clockedInQuery);
        $clockedInStmt->bindParam(':today', $today);
        $clockedInStmt->execute();
        $clockedInToday = $clockedInStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get late arrivals today
        // Assuming shift start time is stored somewhere or using a default of 8:00 AM
        $lateQuery = "
            SELECT COUNT(DISTINCT employee_id) as total 
            FROM attendance_records ar
            JOIN employees e ON ar.employee_id = e.employee_id
            WHERE ar.date = :today
            AND ar.time_in > '08:00:00'::time
            AND ar.status = 'Present'
        ";
        $lateStmt = $conn->prepare($lateQuery);
        $lateStmt->bindParam(':today', $today);
        $lateStmt->execute();
        $lateArrivals = $lateStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Get average check-in time for today
        $avgTimeQuery = "
            SELECT TO_CHAR(AVG(time_in::time), 'HH24:MI') as avg_time
            FROM attendance_records
            WHERE date = :today
            AND time_in IS NOT NULL
        ";
        $avgTimeStmt = $conn->prepare($avgTimeQuery);
        $avgTimeStmt->bindParam(':today', $today);
        $avgTimeStmt->execute();
        $avgCheckinTime = $avgTimeStmt->fetch(PDO::FETCH_ASSOC)['avg_time'] ?? '--:--';
        
        // Calculate absences (employees who haven't clocked in today)
        $absentQuery = "
            SELECT COUNT(*) as total
            FROM employees e
            WHERE e.status = 'active'
            AND e.employee_id NOT IN (
                SELECT DISTINCT employee_id
                FROM attendance_records
                WHERE date = :today
            )
        ";
        $absentStmt = $conn->prepare($absentQuery);
        $absentStmt->bindParam(':today', $today);
        $absentStmt->execute();
        $absentToday = $absentStmt->fetch(PDO::FETCH_ASSOC)['total'];
        
        // Build the response data
        $stats = [
            'totalEmployees' => (int)$totalEmployees,
            'clockedInToday' => (int)$clockedInToday,
            'lateArrivals' => (int)$lateArrivals,
            'overtimeHours' => 0, // Placeholder, calculate if you have overtime data
            'avgCheckinTime' => $avgCheckinTime,
            'absentToday' => (int)$absentToday,
            'onLeave' => 0, // Placeholder, calculate if you have leave tracking
        ];
        
        // Return success response with stats
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
        // First check if access_events table exists
        $tableCheck = "
            SELECT EXISTS (
                SELECT FROM information_schema.tables 
                WHERE table_name = 'access_events'
            ) as table_exists
        ";
        
        $checkStmt = $conn->prepare($tableCheck);
        $checkStmt->execute();
        $tableExists = $checkStmt->fetch(PDO::FETCH_ASSOC)['table_exists'];
        
        if ($tableExists === 't' || $tableExists === true || $tableExists === '1') {
            // First check if card_number column exists
            $columnCheck = "
                SELECT EXISTS (
                    SELECT column_name FROM information_schema.columns 
                    WHERE table_name = 'access_events' AND column_name = 'card_number'
                ) as column_exists
            ";
            
            $columnStmt = $conn->prepare($columnCheck);
            $columnStmt->execute();
            $hasCardNumber = $columnStmt->fetch(PDO::FETCH_ASSOC)['column_exists'];
            
            if ($hasCardNumber === 't' || $hasCardNumber === true || $hasCardNumber === '1') {
                // Use card_number if it exists
                $query = "
                    SELECT 
                        ae.id as access_id,
                        e.employee_id,
                        CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                        e.clock_number,
                        ae.card_number,
                        ae.date_time,
                        ae.major_event_type,
                        ae.minor_event_type,
                        ae.device_id,
                        COALESCE(ae.device_name, 'Unknown Device') as device_name,
                        COALESCE(ae.location, 'Unknown Location') as location
                    FROM 
                        access_events ae
                    LEFT JOIN 
                        employees e ON ae.card_number = e.clock_number
                    ORDER BY 
                        ae.date_time DESC
                    LIMIT 20
                ";
            } else {
                // No card_number column, try to extract data from raw_data JSON field
                $query = "
                    SELECT 
                        ae.id as access_id,
                        (raw_data->>'employeeId')::integer as employee_id,
                        COALESCE(
                            (SELECT CONCAT(e.first_name, ' ', e.last_name) 
                             FROM employees e 
                             WHERE e.employee_id = (raw_data->>'employeeId')::integer
                             OR e.clock_number::text = (raw_data->>'cardNo')::text
                             OR e.clock_number::text = (raw_data->>'employeeNoString')::text
                            ),
                            COALESCE(raw_data->>'personName', 'Unknown')
                        ) as employee_name,
                        COALESCE(
                            raw_data->>'employeeNoString', 
                            raw_data->>'cardNo', 
                            NULL
                        ) as clock_number,
                        COALESCE(
                            raw_data->>'cardNo', 
                            raw_data->>'employeeNoString', 
                            NULL
                        ) as card_number,
                        ae.date_time,
                        ae.major_event_type,
                        ae.minor_event_type,
                        ae.device_id,
                        COALESCE(raw_data->>'deviceName', 'Unknown Device') as device_name,
                        COALESCE(raw_data->>'doorName', raw_data->>'deviceName', 'Unknown Location') as location
                    FROM 
                        access_events ae
                    ORDER BY 
                        ae.date_time DESC
                    LIMIT 20
                ";
            }
            
            $stmt = $conn->prepare($query);
            $stmt->execute();
            $activities = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            // Return success response with activities
            httpResponse(200, 'Access activity retrieved successfully', [
                'success' => true,
                'activities' => $activities
            ]);
        } else {
            // Check if we can use attendance_records with major_event_type = 1 as fallback
            $fallbackQuery = "
                SELECT 
                    ar.attendance_id as access_id,
                    ar.employee_id,
                    CONCAT(e.first_name, ' ', e.last_name) as employee_name,
                    ar.clock_number,
                    ar.clock_number as card_number, 
                    ar.date_time,
                    ar.major_event_type,
                    ar.minor_event_type,
                    ar.device_id,
                    'Main Entry' as device_name,
                    'Front Door' as location
                FROM 
                    attendance_records ar
                LEFT JOIN 
                    employees e ON ar.employee_id = e.employee_id
                WHERE 
                    ar.major_event_type = 1 
                ORDER BY 
                    ar.date_time DESC
                LIMIT 20
            ";
            
            try {
                $fallbackStmt = $conn->prepare($fallbackQuery);
                $fallbackStmt->execute();
                $activities = $fallbackStmt->fetchAll(PDO::FETCH_ASSOC);
                
                if (count($activities) > 0) {
                    // If we found access events in attendance_records
                    httpResponse(200, 'Access activity retrieved from attendance records', [
                        'success' => true,
                        'activities' => $activities
                    ]);
                    return;
                }
            } catch (PDOException $e) {
                // Ignore fallback errors and proceed to mock data
            }
            
            // No access_events table or fallback data, provide sample data for development
            // Get a list of employees to use in mock data
            $employeeQuery = "
                SELECT 
                    employee_id, 
                    CONCAT(first_name, ' ', last_name) as employee_name,
                    clock_number
                FROM 
                    employees 
                LIMIT 5
            ";
            
            $empStmt = $conn->prepare($employeeQuery);
            $empStmt->execute();
            $employees = $empStmt->fetchAll(PDO::FETCH_ASSOC);
            
            // If no employees found, create mock employees
            if (empty($employees)) {
                $employees = [
                    ['employee_id' => 1, 'employee_name' => 'John Smith', 'clock_number' => '1001'],
                    ['employee_id' => 2, 'employee_name' => 'Jane Doe', 'clock_number' => '1002'],
                    ['employee_id' => 3, 'employee_name' => 'Robert Johnson', 'clock_number' => '1003']
                ];
            }
            
            // Generate mock access events using real employees
            $mockActivities = [];
            $deviceNames = ['Main Entrance', 'Side Door', 'Parking Garage', 'Office Floor 1', 'Office Floor 2'];
            $locations = ['Front Desk', 'Employee Entrance', 'Parking Level B1', 'Reception', 'Executive Suite'];
            $eventTypes = [
                ['major' => 1, 'minor' => 1, 'name' => 'Card Swipe'],
                ['major' => 1, 'minor' => 2, 'name' => 'Door Open'],
                ['major' => 1, 'minor' => 4, 'name' => 'Access Granted'],
                ['major' => 1, 'minor' => 5, 'name' => 'Access Denied']
            ];
            
            // Current timestamp for generating recent events
            $now = time();
            
            for ($i = 0; $i < 20; $i++) {
                // Pick a random employee
                $employee = $employees[array_rand($employees)];
                // Pick a random event type
                $eventType = $eventTypes[array_rand($eventTypes)];
                // Generate a random time in the last 24 hours
                $timeOffset = rand(0, 24 * 60 * 60);
                $eventTime = date('Y-m-d H:i:s', $now - $timeOffset);
                // Pick random device and location
                $deviceIndex = array_rand($deviceNames);
                
                $mockActivities[] = [
                    'access_id' => $i + 1,
                    'employee_id' => $employee['employee_id'],
                    'employee_name' => $employee['employee_name'],
                    'clock_number' => $employee['clock_number'],
                    'card_number' => $employee['clock_number'], // Using clock number as card number
                    'date_time' => $eventTime,
                    'major_event_type' => $eventType['major'],
                    'minor_event_type' => $eventType['minor'],
                    'device_id' => 'DEV' . str_pad($deviceIndex + 1, 3, '0', STR_PAD_LEFT),
                    'device_name' => $deviceNames[$deviceIndex],
                    'location' => $locations[$deviceIndex]
                ];
            }
            
            // Sort by date_time descending to show most recent first
            usort($mockActivities, function($a, $b) {
                return strtotime($b['date_time']) - strtotime($a['date_time']);
            });
            
            httpResponse(200, 'Simulated access activity (development mode)', [
                'success' => true,
                'activities' => $mockActivities,
                'note' => 'Using mock data as access_events table was not found'
            ]);
        }
    } catch (PDOException $e) {
        httpResponse(500, 'Database error: ' . $e->getMessage());
    }
} 