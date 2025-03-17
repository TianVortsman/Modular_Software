<?php
session_start();
require_once '../../../php/db.php';

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header('Content-Type: application/json');
    echo json_encode([
        'success' => false,
        'message' => 'Unauthorized access'
    ]);
    exit;
}

// Get request parameters
$period = isset($_GET['period']) ? $_GET['period'] : 'month';
$tab = isset($_GET['tab']) ? $_GET['tab'] : null;
$startDate = isset($_GET['start_date']) ? $_GET['start_date'] : null;
$endDate = isset($_GET['end_date']) ? $_GET['end_date'] : null;

// Validate and set date range based on period
$dateRange = getDateRange($period, $startDate, $endDate);
$fromDate = $dateRange['from'];
$toDate = $dateRange['to'];

// Initialize response data
$response = [
    'success' => true,
    'period' => $period,
    'date_range' => [
        'from' => $fromDate,
        'to' => $toDate
    ],
    'summary' => []
];

// Get data based on requested tab
if ($tab) {
    // Get data for specific tab
    switch ($tab) {
        case 'late':
            $response['late'] = getLateArrivalsData($fromDate, $toDate);
            break;
        case 'absent':
            $response['absent'] = getAbsenteeismData($fromDate, $toDate);
            break;
        case 'perfect':
            $response['perfect'] = getPerfectAttendanceData($fromDate, $toDate);
            break;
        case 'overtime':
            $response['overtime'] = getOvertimeData($fromDate, $toDate);
            break;
    }
} else {
    // Get data for all tabs
    $response['late'] = getLateArrivalsData($fromDate, $toDate);
    $response['absent'] = getAbsenteeismData($fromDate, $toDate);
    $response['perfect'] = getPerfectAttendanceData($fromDate, $toDate);
    $response['overtime'] = getOvertimeData($fromDate, $toDate);
}

// Get summary data for dashboard widgets
$response['summary'] = getDashboardSummary();

// Return JSON response
header('Content-Type: application/json');
echo json_encode($response);
exit;

/**
 * Get date range based on period
 * 
 * @param string $period Period (week, month, quarter, year, custom)
 * @param string $startDate Custom start date (Y-m-d)
 * @param string $endDate Custom end date (Y-m-d)
 * @return array Date range with from and to dates
 */
function getDateRange($period, $startDate = null, $endDate = null) {
    $today = date('Y-m-d');
    $from = '';
    $to = $today;
    
    switch ($period) {
        case 'week':
            // Current week (last 7 days)
            $from = date('Y-m-d', strtotime('-7 days'));
            break;
        case 'month':
            // Current month (last 30 days)
            $from = date('Y-m-d', strtotime('-30 days'));
            break;
        case 'quarter':
            // Current quarter (last 90 days)
            $from = date('Y-m-d', strtotime('-90 days'));
            break;
        case 'year':
            // Current year (last 365 days)
            $from = date('Y-m-d', strtotime('-365 days'));
            break;
        case 'custom':
            // Custom date range
            if ($startDate && $endDate) {
                $from = date('Y-m-d', strtotime($startDate));
                $to = date('Y-m-d', strtotime($endDate));
            } else {
                // Default to last 30 days if custom dates are invalid
                $from = date('Y-m-d', strtotime('-30 days'));
            }
            break;
        default:
            // Default to last 30 days
            $from = date('Y-m-d', strtotime('-30 days'));
    }
    
    return [
        'from' => $from,
        'to' => $to
    ];
}

/**
 * Get late arrivals data
 * 
 * @param string $fromDate Start date (Y-m-d)
 * @param string $toDate End date (Y-m-d)
 * @return array Late arrivals data
 */
function getLateArrivalsData($fromDate, $toDate) {
    global $conn;
    
    // Get late arrivals statistics
    $stats = [
        'employeeCount' => 0,
        'occurrenceCount' => 0,
        'minutesAvg' => 0
    ];
    
    // In a real implementation, this would query the database
    // For now, we'll return sample data
    
    // Sample query (commented out)
    /*
    $statsQuery = "SELECT 
                    COUNT(DISTINCT employee_id) as employee_count,
                    COUNT(*) as occurrence_count,
                    AVG(TIMESTAMPDIFF(MINUTE, scheduled_time, check_in_time)) as minutes_avg
                  FROM attendance
                  WHERE check_in_time > scheduled_time
                  AND DATE(check_in_date) BETWEEN ? AND ?";
    
    $stmt = $conn->prepare($statsQuery);
    $stmt->bind_param("ss", $fromDate, $toDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($row = $result->fetch_assoc()) {
        $stats['employeeCount'] = $row['employee_count'];
        $stats['occurrenceCount'] = $row['occurrence_count'];
        $stats['minutesAvg'] = round($row['minutes_avg'], 1);
    }
    */
    
    // Sample data for demonstration
    $stats['employeeCount'] = 12;
    $stats['occurrenceCount'] = 37;
    $stats['minutesAvg'] = 15.3;
    
    // Get employees with late arrivals
    $employees = [];
    
    // Sample query (commented out)
    /*
    $employeesQuery = "SELECT 
                        e.id,
                        e.name,
                        d.name as department,
                        COUNT(*) as late_count,
                        AVG(TIMESTAMPDIFF(MINUTE, a.scheduled_time, a.check_in_time)) as avg_minutes_late,
                        MAX(a.check_in_date) as last_late
                      FROM employees e
                      JOIN attendance a ON e.id = a.employee_id
                      JOIN departments d ON e.department_id = d.id
                      WHERE a.check_in_time > a.scheduled_time
                      AND DATE(a.check_in_date) BETWEEN ? AND ?
                      GROUP BY e.id
                      ORDER BY late_count DESC
                      LIMIT 20";
    
    $stmt = $conn->prepare($employeesQuery);
    $stmt->bind_param("ss", $fromDate, $toDate);
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $employees[] = [
            'id' => $row['id'],
            'name' => $row['name'],
            'department' => $row['department'],
            'lateCount' => $row['late_count'],
            'avgMinutesLate' => round($row['avg_minutes_late'], 1),
            'lastLate' => date('M d, Y', strtotime($row['last_late']))
        ];
    }
    */
    
    // Sample data for demonstration
    $employees = [
        [
            'id' => 1,
            'name' => 'John Smith',
            'department' => 'Sales',
            'lateCount' => 5,
            'avgMinutesLate' => 12.5,
            'lastLate' => 'May 15, 2023'
        ],
        [
            'id' => 2,
            'name' => 'Jane Doe',
            'department' => 'Marketing',
            'lateCount' => 4,
            'avgMinutesLate' => 18.2,
            'lastLate' => 'May 17, 2023'
        ],
        [
            'id' => 3,
            'name' => 'Robert Johnson',
            'department' => 'IT',
            'lateCount' => 3,
            'avgMinutesLate' => 8.7,
            'lastLate' => 'May 10, 2023'
        ],
        [
            'id' => 4,
            'name' => 'Emily Wilson',
            'department' => 'HR',
            'lateCount' => 3,
            'avgMinutesLate' => 22.3,
            'lastLate' => 'May 12, 2023'
        ],
        [
            'id' => 5,
            'name' => 'Michael Brown',
            'department' => 'Finance',
            'lateCount' => 2,
            'avgMinutesLate' => 5.5,
            'lastLate' => 'May 5, 2023'
        ]
    ];
    
    return [
        'stats' => $stats,
        'employees' => $employees
    ];
}

/**
 * Get absenteeism data
 * 
 * @param string $fromDate Start date (Y-m-d)
 * @param string $toDate End date (Y-m-d)
 * @return array Absenteeism data
 */
function getAbsenteeismData($fromDate, $toDate) {
    global $conn;
    
    // Get absenteeism statistics
    $stats = [
        'employeeCount' => 0,
        'daysCount' => 0,
        'rate' => '0%'
    ];
    
    // In a real implementation, this would query the database
    // For now, we'll return sample data
    
    // Sample data for demonstration
    $stats['employeeCount'] = 8;
    $stats['daysCount'] = 22;
    $stats['rate'] = '3.2%';
    
    // Get employees with absences
    $employees = [
        [
            'id' => 6,
            'name' => 'Sarah Johnson',
            'department' => 'Sales',
            'absentDays' => 4,
            'absentRate' => '16.7%',
            'lastAbsent' => 'May 16, 2023'
        ],
        [
            'id' => 7,
            'name' => 'David Lee',
            'department' => 'IT',
            'absentDays' => 3,
            'absentRate' => '12.5%',
            'lastAbsent' => 'May 10, 2023'
        ],
        [
            'id' => 8,
            'name' => 'Lisa Chen',
            'department' => 'Marketing',
            'absentDays' => 3,
            'absentRate' => '12.5%',
            'lastAbsent' => 'May 12, 2023'
        ],
        [
            'id' => 9,
            'name' => 'James Wilson',
            'department' => 'Finance',
            'absentDays' => 2,
            'absentRate' => '8.3%',
            'lastAbsent' => 'May 5, 2023'
        ],
        [
            'id' => 10,
            'name' => 'Amanda Garcia',
            'department' => 'HR',
            'absentDays' => 2,
            'absentRate' => '8.3%',
            'lastAbsent' => 'May 3, 2023'
        ]
    ];
    
    return [
        'stats' => $stats,
        'employees' => $employees
    ];
}

/**
 * Get perfect attendance data
 * 
 * @param string $fromDate Start date (Y-m-d)
 * @param string $toDate End date (Y-m-d)
 * @return array Perfect attendance data
 */
function getPerfectAttendanceData($fromDate, $toDate) {
    global $conn;
    
    // Get perfect attendance statistics
    $stats = [
        'employeeCount' => 0,
        'percentage' => '0%',
        'avgCheckin' => '--:--'
    ];
    
    // In a real implementation, this would query the database
    // For now, we'll return sample data
    
    // Sample data for demonstration
    $stats['employeeCount'] = 15;
    $stats['percentage'] = '25.4%';
    $stats['avgCheckin'] = '08:52';
    
    // Get employees with perfect attendance
    $employees = [
        [
            'id' => 11,
            'name' => 'Thomas Anderson',
            'department' => 'IT',
            'daysPresent' => 30,
            'avgCheckin' => '08:45',
            'lastCheckin' => 'May 18, 2023'
        ],
        [
            'id' => 12,
            'name' => 'Maria Rodriguez',
            'department' => 'Sales',
            'daysPresent' => 30,
            'avgCheckin' => '08:50',
            'lastCheckin' => 'May 18, 2023'
        ],
        [
            'id' => 13,
            'name' => 'Kevin Patel',
            'department' => 'Finance',
            'daysPresent' => 30,
            'avgCheckin' => '08:30',
            'lastCheckin' => 'May 18, 2023'
        ],
        [
            'id' => 14,
            'name' => 'Jennifer Kim',
            'department' => 'Marketing',
            'daysPresent' => 29,
            'avgCheckin' => '08:55',
            'lastCheckin' => 'May 18, 2023'
        ],
        [
            'id' => 15,
            'name' => 'Daniel Martinez',
            'department' => 'HR',
            'daysPresent' => 29,
            'avgCheckin' => '09:00',
            'lastCheckin' => 'May 18, 2023'
        ]
    ];
    
    return [
        'stats' => $stats,
        'employees' => $employees
    ];
}

/**
 * Get overtime data
 * 
 * @param string $fromDate Start date (Y-m-d)
 * @param string $toDate End date (Y-m-d)
 * @return array Overtime data
 */
function getOvertimeData($fromDate, $toDate) {
    global $conn;
    
    // Get overtime statistics
    $stats = [
        'employeeCount' => 0,
        'hoursTotal' => 0,
        'cost' => '$0'
    ];
    
    // In a real implementation, this would query the database
    // For now, we'll return sample data
    
    // Sample data for demonstration
    $stats['employeeCount'] = 10;
    $stats['hoursTotal'] = 87.5;
    $stats['cost'] = '$2,625';
    
    // Get employees with overtime
    $employees = [
        [
            'id' => 16,
            'name' => 'Carlos Sanchez',
            'department' => 'Production',
            'overtimeHours' => 12.5,
            'overtimeRate' => '$30/hr',
            'lastOvertime' => 'May 17, 2023'
        ],
        [
            'id' => 17,
            'name' => 'Michelle Wong',
            'department' => 'IT',
            'overtimeHours' => 10.0,
            'overtimeRate' => '$35/hr',
            'lastOvertime' => 'May 16, 2023'
        ],
        [
            'id' => 18,
            'name' => 'Ryan Taylor',
            'department' => 'Sales',
            'overtimeHours' => 8.5,
            'overtimeRate' => '$28/hr',
            'lastOvertime' => 'May 15, 2023'
        ],
        [
            'id' => 19,
            'name' => 'Sophia Johnson',
            'department' => 'Marketing',
            'overtimeHours' => 7.0,
            'overtimeRate' => '$32/hr',
            'lastOvertime' => 'May 14, 2023'
        ],
        [
            'id' => 20,
            'name' => 'William Davis',
            'department' => 'Finance',
            'overtimeHours' => 6.5,
            'overtimeRate' => '$40/hr',
            'lastOvertime' => 'May 12, 2023'
        ]
    ];
    
    return [
        'stats' => $stats,
        'employees' => $employees
    ];
}

/**
 * Get dashboard summary data
 * 
 * @return array Dashboard summary data
 */
function getDashboardSummary() {
    global $conn;
    
    // In a real implementation, this would query the database
    // For now, we'll return sample data
    
    return [
        'totalEmployees' => 59,
        'clockedInToday' => 42,
        'lateArrivalsToday' => 3,
        'absentToday' => 5,
        'onLeave' => 2,
        'overtimeHours' => 87.5,
        'avgCheckinTime' => '08:52'
    ];
}
?> 