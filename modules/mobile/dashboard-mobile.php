<?php
session_start();
require_once('../../php/db.php');

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    header("Location: /modular1/index.php");
    exit;
}

// Get account number from session
$account_number = $_SESSION['account_number'] ?? null;
if (!$account_number) {
    header("Location: /modular1/index.php");
    exit;
}

try {
    // Initialize PDO connection
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch Mobile App statistics
    $stats = [
        'active_users' => 0,
        'total_devices' => 0,
        'sync_errors' => 0,
        'app_version' => '1.0.0'
    ];

    // Active Mobile Users
    $stmt = $pdo->prepare("
        SELECT COUNT(DISTINCT user_id) 
        FROM mobile_sessions 
        WHERE account_number = ? 
        AND last_activity >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
    ");
    $stmt->execute([$account_number]);
    $stats['active_users'] = $stmt->fetchColumn();

    // Total Registered Devices
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM mobile_devices WHERE account_number = ?");
    $stmt->execute([$account_number]);
    $stats['total_devices'] = $stmt->fetchColumn();

    // Sync Errors (Last 24 hours)
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM mobile_sync_logs 
        WHERE account_number = ? 
        AND sync_time >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        AND status = 'error'
    ");
    $stmt->execute([$account_number]);
    $stats['sync_errors'] = $stmt->fetchColumn();

    // Current App Version
    $stmt = $pdo->prepare("
        SELECT version_number 
        FROM mobile_app_versions 
        WHERE account_number = ? 
        ORDER BY release_date DESC 
        LIMIT 1
    ");
    $stmt->execute([$account_number]);
    $stats['app_version'] = $stmt->fetchColumn() ?: '1.0.0';

} catch (PDOException $e) {
    error_log("Error in Mobile Dashboard: " . $e->getMessage());
    $error_message = "An error occurred while fetching dashboard data.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mobile App Dashboard</title>
    <link rel="stylesheet" href="/modular1/css/root.css">
    <link rel="stylesheet" href="/modular1/css/sidebar.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/modular1/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/modular1/js/toggle-theme.js"></script>
</head>
<body id="mobile-dashboard">
    <div class="dashboard-container">
        <?php 
        include '../../includes/sidebar.php';
        include '../../includes/response-modal.php';
        include '../../includes/loading-modal.php';
        ?>
        
        <main class="main-content">
            <div class="dashboard-header">
                <h1>Mobile App Dashboard</h1>
                <div class="header-actions">
                    <button onclick="window.location.href='pages/notifications.php'" class="btn-primary">
                        <i class="material-icons">notifications</i> Send Notification
                    </button>
                    <button onclick="window.location.href='pages/reports.php'" class="btn-secondary">
                        <i class="material-icons">description</i> Generate Report
                    </button>
                </div>
            </div>

            <div class="stats-container">
                <div class="stat-card">
                    <i class="material-icons">people</i>
                    <div class="stat-info">
                        <h3>Active Users</h3>
                        <p><?php echo $stats['active_users']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="material-icons">devices</i>
                    <div class="stat-info">
                        <h3>Total Devices</h3>
                        <p><?php echo $stats['total_devices']; ?></p>
                    </div>
                </div>
                <div class="stat-card warning">
                    <i class="material-icons">sync_problem</i>
                    <div class="stat-info">
                        <h3>Sync Errors</h3>
                        <p><?php echo $stats['sync_errors']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="material-icons">system_update</i>
                    <div class="stat-info">
                        <h3>App Version</h3>
                        <p><?php echo $stats['app_version']; ?></p>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h2>User Activity Trend</h2>
                    <canvas id="activityChart"></canvas>
                </div>
                <div class="dashboard-card">
                    <h2>Recent Sync Events</h2>
                    <div class="sync-list">
                        <?php
                        try {
                            $stmt = $pdo->prepare("
                                SELECT device_id, sync_type, status, sync_time 
                                FROM mobile_sync_logs 
                                WHERE account_number = ? 
                                ORDER BY sync_time DESC 
                                LIMIT 5
                            ");
                            $stmt->execute([$account_number]);
                            while ($sync = $stmt->fetch()) {
                                $statusClass = $sync['status'] === 'success' ? 'success' : 'error';
                                echo "<div class='sync-item {$statusClass}'>";
                                echo "<span class='sync-device'>" . htmlspecialchars($sync['device_id']) . "</span>";
                                echo "<span class='sync-type'>" . htmlspecialchars($sync['sync_type']) . "</span>";
                                echo "<span class='sync-status'>" . ucfirst($sync['status']) . "</span>";
                                echo "<span class='sync-time'>" . date('M d, H:i', strtotime($sync['sync_time'])) . "</span>";
                                echo "</div>";
                            }
                        } catch (PDOException $e) {
                            echo "<p>Error loading sync events.</p>";
                        }
                        ?>
                    </div>
                </div>
                <div class="dashboard-card">
                    <h2>Device Distribution</h2>
                    <canvas id="deviceChart"></canvas>
                </div>
                <div class="dashboard-card">
                    <h2>Recent Notifications</h2>
                    <div class="notification-list">
                        <?php
                        try {
                            $stmt = $pdo->prepare("
                                SELECT title, message, sent_at, delivery_status
                                FROM mobile_notifications
                                WHERE account_number = ? 
                                ORDER BY sent_at DESC
                                LIMIT 5
                            ");
                            $stmt->execute([$account_number]);
                            while ($notification = $stmt->fetch()) {
                                $statusClass = $notification['delivery_status'] === 'delivered' ? 'success' : 'pending';
                                echo "<div class='notification-item {$statusClass}'>";
                                echo "<span class='notification-title'>" . htmlspecialchars($notification['title']) . "</span>";
                                echo "<span class='notification-message'>" . htmlspecialchars($notification['message']) . "</span>";
                                echo "<span class='notification-status'>" . ucfirst($notification['delivery_status']) . "</span>";
                                echo "<span class='notification-time'>" . date('M d, H:i', strtotime($notification['sent_at'])) . "</span>";
                                echo "</div>";
                            }
                        } catch (PDOException $e) {
                            echo "<p>Error loading notifications.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // User Activity Chart
        const activityCtx = document.getElementById('activityChart').getContext('2d');
        new Chart(activityCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Active Users',
                    data: [120, 150, 180, 165, 190, 140, 130],
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

        // Device Distribution Chart
        const deviceCtx = document.getElementById('deviceChart').getContext('2d');
        new Chart(deviceCtx, {
            type: 'doughnut',
            data: {
                labels: ['Android', 'iOS', 'Web App'],
                datasets: [{
                    data: [60, 35, 5],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)'
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
</body>
</html> 