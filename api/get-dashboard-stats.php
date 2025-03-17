<?php
// Enable error reporting for debugging
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Set content type to JSON
header('Content-Type: application/json');

// Start the session to get the account number
session_start();

// Check if account number is available in session
if (!isset($_SESSION['account_number'])) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => 'No account number found in session'
    ]);
    exit;
}

$account_number = $_SESSION['account_number'];

try {
    // Include the database connection for the specific account
    // This assumes db.php creates a connection using the account number from session
    include('../php/db.php');
    
    // Get today's date
    $today = date('Y-m-d');
    
    // Get total employees clocked in today
    $stmt = $conn->prepare("
        SELECT COUNT(DISTINCT employee_id) as clocked_in
        FROM attendance_records
        WHERE date = ?
    ");
    $stmt->execute([$today]);
    $clockedIn = $stmt->fetch(PDO::FETCH_ASSOC)['clocked_in'] ?? 0;
    
    // Get average check-in time
    $stmt = $conn->prepare("
        SELECT AVG(EXTRACT(HOUR FROM check_in) * 60 + EXTRACT(MINUTE FROM check_in)) as avg_minutes
        FROM attendance_records
        WHERE date = ?
    ");
    $stmt->execute([$today]);
    $avgMinutes = $stmt->fetch(PDO::FETCH_ASSOC)['avg_minutes'] ?? 0;
    
    // Convert minutes to time format
    $avgHours = floor($avgMinutes / 60);
    $avgMins = round($avgMinutes % 60);
    $avgCheckinTime = sprintf('%02d:%02d', $avgHours, $avgMins);
    
    // Get total overtime hours (for today)
    $stmt = $conn->prepare("
        SELECT COALESCE(SUM(hours), 0) as total_hours
        FROM overtime_records
        WHERE date = ?
    ");
    $stmt->execute([$today]);
    $totalOvertime = $stmt->fetch(PDO::FETCH_ASSOC)['total_hours'] ?? 0;
    
    // Get late arrivals (assuming shift starts at 09:00)
    $stmt = $conn->prepare("
        SELECT COUNT(*) as late_count
        FROM attendance_records ar
        JOIN shifts s ON ar.shift_id = s.id
        WHERE ar.date = ? 
        AND EXTRACT(HOUR FROM ar.check_in) * 60 + EXTRACT(MINUTE FROM ar.check_in) > 
        EXTRACT(HOUR FROM s.start_time) * 60 + EXTRACT(MINUTE FROM s.start_time) + 5
    ");
    $stmt->execute([$today]);
    $lateArrivals = $stmt->fetch(PDO::FETCH_ASSOC)['late_count'] ?? 0;
    
    // Get mobile usage percentage
    $stmt = $conn->prepare("
        SELECT 
            COUNT(*) as total_records,
            SUM(CASE WHEN source = 'mobile' THEN 1 ELSE 0 END) as mobile_records
        FROM attendance_records
        WHERE date = ?
    ");
    $stmt->execute([$today]);
    $mobileData = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $mobileUsage = 0;
    if ($mobileData && $mobileData['total_records'] > 0) {
        $mobileUsage = round(($mobileData['mobile_records'] / $mobileData['total_records']) * 100);
    }
    
    // Get device status
    $stmt = $conn->prepare("
        SELECT name, status, last_check
        FROM clock_devices
        WHERE status != 'deleted'
        ORDER BY last_check DESC
    ");
    $stmt->execute();
    $devices = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Handle case where devices table might not exist
    if (!$devices) {
        $devices = [];
    }
    
    // Format device data
    $formattedDevices = [];
    foreach ($devices as $device) {
        $formattedDevices[] = [
            'name' => $device['name'],
            'status' => $device['status'],
            'lastCheck' => (new DateTime($device['last_check']))->format('Y-m-d H:i')
        ];
    }
    
    // Return all statistics
    echo json_encode([
        'success' => true,
        'stats' => [
            'clockedIn' => $clockedIn,
            'avgCheckin' => $avgCheckinTime,
            'overtime' => $totalOvertime,
            'lateArrivals' => $lateArrivals,
            'mobileUsage' => $mobileUsage . '%',
            'mobileSuccess' => '98%', // Placeholder value
            'devices' => $formattedDevices
        ]
    ]);
    
} catch (PDOException $e) {
    http_response_code(500);
    error_log("Database error in dashboard stats: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => 'Database error occurred',
        'details' => $e->getMessage()
    ]);
} catch (Exception $e) {
    http_response_code(500);
    error_log("General error in dashboard stats: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => 'An error occurred',
        'details' => $e->getMessage()
    ]);
} finally {
    $conn = null;
}
?> 