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

    // Fetch HR statistics
    $stats = [
        'total_employees' => 0,
        'active_recruitments' => 0,
        'pending_reviews' => 0,
        'training_programs' => 0
    ];

    // Total Employees
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE account_number = ?");
    $stmt->execute([$account_number]);
    $stats['total_employees'] = $stmt->fetchColumn();

    // Active Recruitments
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM recruitment_posts WHERE account_number = ? AND status = 'active'");
    $stmt->execute([$account_number]);
    $stats['active_recruitments'] = $stmt->fetchColumn();

    // Pending Reviews
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM performance_reviews WHERE account_number = ? AND status = 'pending'");
    $stmt->execute([$account_number]);
    $stats['pending_reviews'] = $stmt->fetchColumn();

    // Training Programs
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM training_programs WHERE account_number = ? AND status = 'active'");
    $stmt->execute([$account_number]);
    $stats['training_programs'] = $stmt->fetchColumn();

} catch (PDOException $e) {
    error_log("Error in HR Dashboard: " . $e->getMessage());
    $error_message = "An error occurred while fetching dashboard data.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>HR Management Dashboard</title>
    <link rel="stylesheet" href="/modular1/css/root.css">
    <link rel="stylesheet" href="/modular1/css/sidebar.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/modular1/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/modular1/js/toggle-theme.js"></script>
</head>
<body id="hr-dashboard">
    <div class="dashboard-container">
        <?php 
        include '../../includes/sidebar.php';
        include '../../includes/response-modal.php';
        include '../../includes/loading-modal.php';
        ?>
        
        <main class="main-content">
            <div class="dashboard-header">
                <h1>HR Management Dashboard</h1>
                <div class="header-actions">
                    <button onclick="window.location.href='pages/employees.php'" class="btn-primary">
                        <i class="material-icons">person_add</i> Add Employee
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
                        <h3>Total Employees</h3>
                        <p><?php echo $stats['total_employees']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="material-icons">person_search</i>
                    <div class="stat-info">
                        <h3>Active Recruitments</h3>
                        <p><?php echo $stats['active_recruitments']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="material-icons">rate_review</i>
                    <div class="stat-info">
                        <h3>Pending Reviews</h3>
                        <p><?php echo $stats['pending_reviews']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="material-icons">school</i>
                    <div class="stat-info">
                        <h3>Training Programs</h3>
                        <p><?php echo $stats['training_programs']; ?></p>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h2>Department Distribution</h2>
                    <canvas id="departmentChart"></canvas>
                </div>
                <div class="dashboard-card">
                    <h2>Recent Activities</h2>
                    <div class="activity-list">
                        <?php
                        try {
                            $stmt = $pdo->prepare("
                                SELECT activity_type, description, created_at 
                                FROM hr_activities 
                                WHERE account_number = ? 
                                ORDER BY created_at DESC 
                                LIMIT 5
                            ");
                            $stmt->execute([$account_number]);
                            while ($activity = $stmt->fetch()) {
                                echo "<div class='activity-item'>";
                                echo "<span class='activity-time'>" . date('M d, H:i', strtotime($activity['created_at'])) . "</span>";
                                echo "<span class='activity-desc'>" . htmlspecialchars($activity['description']) . "</span>";
                                echo "</div>";
                            }
                        } catch (PDOException $e) {
                            echo "<p>Error loading recent activities.</p>";
                        }
                        ?>
                    </div>
                </div>
                <div class="dashboard-card">
                    <h2>Upcoming Reviews</h2>
                    <div class="review-list">
                        <?php
                        try {
                            $stmt = $pdo->prepare("
                                SELECT e.name, pr.review_date 
                                FROM performance_reviews pr
                                JOIN employees e ON pr.employee_id = e.id
                                WHERE pr.account_number = ? AND pr.status = 'scheduled'
                                ORDER BY pr.review_date ASC
                                LIMIT 5
                            ");
                            $stmt->execute([$account_number]);
                            while ($review = $stmt->fetch()) {
                                echo "<div class='review-item'>";
                                echo "<span class='review-name'>" . htmlspecialchars($review['name']) . "</span>";
                                echo "<span class='review-date'>" . date('M d, Y', strtotime($review['review_date'])) . "</span>";
                                echo "</div>";
                            }
                        } catch (PDOException $e) {
                            echo "<p>Error loading upcoming reviews.</p>";
                        }
                        ?>
                    </div>
                </div>
                <div class="dashboard-card">
                    <h2>Training Status</h2>
                    <canvas id="trainingChart"></canvas>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Department Distribution Chart
        const deptCtx = document.getElementById('departmentChart').getContext('2d');
        new Chart(deptCtx, {
            type: 'pie',
            data: {
                labels: ['IT', 'HR', 'Finance', 'Operations', 'Sales', 'Marketing'],
                datasets: [{
                    data: [12, 8, 10, 15, 20, 10],
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 159, 64, 0.8)'
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

        // Training Status Chart
        const trainingCtx = document.getElementById('trainingChart').getContext('2d');
        new Chart(trainingCtx, {
            type: 'bar',
            data: {
                labels: ['Completed', 'In Progress', 'Not Started'],
                datasets: [{
                    label: 'Employees',
                    data: [25, 15, 10],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
</body>
</html> 