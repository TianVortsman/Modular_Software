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

try {
    // Initialize PDO connection
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch Support statistics
    $stats = [
        'open_tickets' => 0,
        'avg_response_time' => 0,
        'resolved_today' => 0,
        'satisfaction_rate' => 0
    ];

    // Open Tickets
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM support_tickets WHERE account_number = ? AND status = 'open'");
    $stmt->execute([$account_number]);
    $stats['open_tickets'] = $stmt->fetchColumn();

    // Average Response Time (in hours)
    $stmt = $pdo->prepare("
        SELECT AVG(TIMESTAMPDIFF(HOUR, created_at, first_response_at)) 
        FROM support_tickets 
        WHERE account_number = ? AND first_response_at IS NOT NULL
    ");
    $stmt->execute([$account_number]);
    $stats['avg_response_time'] = round($stmt->fetchColumn(), 1);

    // Tickets Resolved Today
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM support_tickets 
        WHERE account_number = ? 
        AND status = 'resolved' 
        AND DATE(resolved_at) = CURDATE()
    ");
    $stmt->execute([$account_number]);
    $stats['resolved_today'] = $stmt->fetchColumn();

    // Customer Satisfaction Rate
    $stmt = $pdo->prepare("
        SELECT AVG(satisfaction_rating) * 20 
        FROM support_tickets 
        WHERE account_number = ? 
        AND satisfaction_rating IS NOT NULL
    ");
    $stmt->execute([$account_number]);
    $stats['satisfaction_rate'] = round($stmt->fetchColumn(), 1);

} catch (PDOException $e) {
    error_log("Error in Support Dashboard: " . $e->getMessage());
    $error_message = "An error occurred while fetching dashboard data.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Support Dashboard</title>
    <link rel="stylesheet" href="../../css/root.css">
    <link rel="stylesheet" href="../../css/reset.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="css/dashboard-support.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/modular1/js/toggle-theme.js"></script>
</head>
<body id="support-dashboard">
    <?php include('../../main/sidebar.php'); ?>
    
    <div class="dashboard-content">
        <header class="dashboard-header">
            <h1>Support Dashboard</h1>
            <div class="header-actions">
                <button onclick="window.location.href='pages/tickets.php'" class="btn-primary">
                    <i class="material-icons">add</i> New Ticket
                </button>
                <button onclick="window.location.href='pages/reports.php'" class="btn-secondary">
                    <i class="material-icons">description</i> Generate Report
                </button>
            </div>
        </header>

        <div class="stats-container">
            <div class="stat-card">
                <i class="material-icons">confirmation_number</i>
                <div class="stat-info">
                    <h3>Open Tickets</h3>
                    <p><?php echo $stats['open_tickets']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">timer</i>
                <div class="stat-info">
                    <h3>Avg Response Time</h3>
                    <p><?php echo $stats['avg_response_time']; ?> hrs</p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">done_all</i>
                <div class="stat-info">
                    <h3>Resolved Today</h3>
                    <p><?php echo $stats['resolved_today']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">sentiment_satisfied</i>
                <div class="stat-info">
                    <h3>Satisfaction Rate</h3>
                    <p><?php echo $stats['satisfaction_rate']; ?>%</p>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Ticket Status Distribution</h2>
                <canvas id="ticketStatusChart"></canvas>
            </div>
            <div class="dashboard-card">
                <h2>Recent Tickets</h2>
                <div class="ticket-list">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT id, subject, priority, status, created_at 
                            FROM support_tickets 
                            WHERE account_number = ? 
                            ORDER BY created_at DESC 
                            LIMIT 5
                        ");
                        $stmt->execute([$account_number]);
                        while ($ticket = $stmt->fetch()) {
                            $priorityClass = strtolower($ticket['priority']);
                            echo "<div class='ticket-item {$priorityClass}'>";
                            echo "<span class='ticket-id'>#" . $ticket['id'] . "</span>";
                            echo "<span class='ticket-subject'>" . htmlspecialchars($ticket['subject']) . "</span>";
                            echo "<span class='ticket-status'>" . ucfirst($ticket['status']) . "</span>";
                            echo "<span class='ticket-time'>" . date('M d, H:i', strtotime($ticket['created_at'])) . "</span>";
                            echo "</div>";
                        }
                    } catch (PDOException $e) {
                        echo "<p>Error loading recent tickets.</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="dashboard-card">
                <h2>Response Time Trend</h2>
                <canvas id="responseTimeChart"></canvas>
            </div>
            <div class="dashboard-card">
                <h2>Category Distribution</h2>
                <canvas id="categoryChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Ticket Status Chart
        const statusCtx = document.getElementById('ticketStatusChart').getContext('2d');
        new Chart(statusCtx, {
            type: 'doughnut',
            data: {
                labels: ['Open', 'In Progress', 'Pending', 'Resolved'],
                datasets: [{
                    data: [15, 10, 5, 20],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)'
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

        // Response Time Trend Chart
        const responseCtx = document.getElementById('responseTimeChart').getContext('2d');
        new Chart(responseCtx, {
            type: 'line',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'],
                datasets: [{
                    label: 'Average Response Time (hours)',
                    data: [4.2, 3.8, 4.5, 3.2, 3.9, 2.8, 3.5],
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
                            text: 'Hours'
                        }
                    }
                }
            }
        });

        // Category Distribution Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'pie',
            data: {
                labels: ['Technical', 'Billing', 'Account', 'Feature Request', 'Other'],
                datasets: [{
                    data: [35, 25, 20, 15, 5],
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
    <script src="js/dashboard-support.js"></script>
</body>
</html> 