<?php
session_start();
require_once('../../php/db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: ../../login.php");
    exit;
}

// Get account number from session
$account_number = $_SESSION['account_number'] ?? null;
if (!$account_number) {
    header("Location: ../../login.php");
    exit;
}

// try {
//     // Initialize PDO connection
//     $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
//     $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

//     // Fetch Access Control statistics
//     $stats = [
//         'total_users' => 0,
//         'active_sessions' => 0,
//         'failed_attempts' => 0,
//         'pending_requests' => 0
//     ];

//     // Total Users
//     $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE account_number = ?");
//     $stmt->execute([$account_number]);
//     $stats['total_users'] = $stmt->fetchColumn();

//     // Active Sessions
//     $stmt = $pdo->prepare("
//         SELECT COUNT(*) 
//         FROM user_sessions 
//         WHERE account_number = ? 
//         AND last_activity >= DATE_SUB(NOW(), INTERVAL 30 MINUTE)
//     ");
//     $stmt->execute([$account_number]);
//     $stats['active_sessions'] = $stmt->fetchColumn();

//     // Failed Login Attempts (Last 24 hours)
//     $stmt = $pdo->prepare("
//         SELECT COUNT(*) 
//         FROM login_attempts 
//         WHERE account_number = ? 
//         AND attempt_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
//         AND status = 'failed'
//     ");
//     $stmt->execute([$account_number]);
//     $stats['failed_attempts'] = $stmt->fetchColumn();

//     // Pending Access Requests
//     $stmt = $pdo->prepare("
//         SELECT COUNT(*) 
//         FROM access_requests 
//         WHERE account_number = ? 
//         AND status = 'pending'
//     ");
//     $stmt->execute([$account_number]);
//     $stats['pending_requests'] = $stmt->fetchColumn();

// } catch (PDOException $e) {
//     error_log("Error in Access Control Dashboard: " . $e->getMessage());
//     $error_message = "An error occurred while fetching dashboard data.";
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Access Control Dashboard</title>
    <link rel="stylesheet" href="../../css/root.css">
    <link rel="stylesheet" href="../../css/reset.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="css/dashboard-access.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/modular1/js/toggle-theme.js"></script>
</head>
<body id="access-dashboard">
    <?php include('../../main/sidebar.php'); ?>
    
    <div class="dashboard-content">
        <header class="dashboard-header">
            <h1>Access Control Dashboard</h1>
            <div class="header-actions">
                <button onclick="window.location.href='pages/users.php'" class="btn-primary">
                    <i class="material-icons">person_add</i> Add User
                </button>
                <button onclick="window.location.href='pages/reports.php'" class="btn-secondary">
                    <i class="material-icons">description</i> Generate Report
                </button>
            </div>
        </header>

        <div class="stats-container">
            <div class="stat-card">
                <i class="material-icons">people</i>
                <div class="stat-info">
                    <h3>Total Users</h3>
                    <p><?php echo $stats['total_users']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">devices</i>
                <div class="stat-info">
                    <h3>Active Sessions</h3>
                    <p><?php echo $stats['active_sessions']; ?></p>
                </div>
            </div>
            <div class="stat-card warning">
                <i class="material-icons">warning</i>
                <div class="stat-info">
                    <h3>Failed Attempts</h3>
                    <p><?php echo $stats['failed_attempts']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">pending_actions</i>
                <div class="stat-info">
                    <h3>Pending Requests</h3>
                    <p><?php echo $stats['pending_requests']; ?></p>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>User Activity Overview</h2>
                <canvas id="activityChart"></canvas>
            </div>
            <div class="dashboard-card">
                <h2>Recent Security Events</h2>
                <div class="security-list">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT event_type, description, ip_address, created_at 
                            FROM security_events 
                            WHERE account_number = ? 
                            ORDER BY created_at DESC 
                            LIMIT 5
                        ");
                        $stmt->execute([$account_number]);
                        while ($event = $stmt->fetch()) {
                            $eventClass = strtolower($event['event_type']);
                            echo "<div class='security-item {$eventClass}'>";
                            echo "<span class='event-type'>" . htmlspecialchars($event['event_type']) . "</span>";
                            echo "<span class='event-desc'>" . htmlspecialchars($event['description']) . "</span>";
                            echo "<span class='event-ip'>" . htmlspecialchars($event['ip_address']) . "</span>";
                            echo "<span class='event-time'>" . date('M d, H:i', strtotime($event['created_at'])) . "</span>";
                            echo "</div>";
                        }
                    } catch (PDOException $e) {
                        echo "<p>Error loading security events.</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="dashboard-card">
                <h2>Role Distribution</h2>
                <canvas id="roleChart"></canvas>
            </div>
            <div class="dashboard-card">
                <h2>Access Requests</h2>
                <div class="request-list">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT ar.request_type, ar.status, u.username, ar.created_at
                            FROM access_requests ar
                            JOIN users u ON ar.user_id = u.id
                            WHERE ar.account_number = ? 
                            ORDER BY ar.created_at DESC
                            LIMIT 5
                        ");
                        $stmt->execute([$account_number]);
                        while ($request = $stmt->fetch()) {
                            echo "<div class='request-item'>";
                            echo "<span class='request-user'>" . htmlspecialchars($request['username']) . "</span>";
                            echo "<span class='request-type'>" . htmlspecialchars($request['request_type']) . "</span>";
                            echo "<span class='request-status'>" . ucfirst($request['status']) . "</span>";
                            echo "<span class='request-time'>" . date('M d, H:i', strtotime($request['created_at'])) . "</span>";
                            echo "</div>";
                        }
                    } catch (PDOException $e) {
                        echo "<p>Error loading access requests.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // User Activity Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: ['00:00', '04:00', '08:00', '12:00', '16:00', '20:00'],
                datasets: [{
                    label: 'Active Users',
                    data: [10, 5, 15, 25, 30, 20],
                    borderColor: 'rgba(75, 192, 192, 1)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Users'
                        }
                    }
                }
            }
        });

        // Role Distribution Chart
        const roleCtx = document.getElementById('roleChart').getContext('2d');
        new Chart(roleCtx, {
            type: 'pie',
            data: {
                labels: ['Admin', 'Manager', 'Staff', 'Viewer', 'Guest'],
                datasets: [{
                    data: [5, 15, 40, 25, 15],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom'
                    }
                }
            }
        });
    </script>
    <script src="../../js/sidebar.js"></script>
    <script src="js/dashboard-access.js"></script>
</body>
</html> 