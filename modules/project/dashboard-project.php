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

    // Fetch Project statistics
    $stats = [
        'total_projects' => 0,
        'active_projects' => 0,
        'pending_tasks' => 0,
        'team_members' => 0
    ];

    // Total Projects
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE account_number = ?");
    $stmt->execute([$account_number]);
    $stats['total_projects'] = $stmt->fetchColumn();

    // Active Projects
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM projects WHERE account_number = ? AND status = 'active'");
    $stmt->execute([$account_number]);
    $stats['active_projects'] = $stmt->fetchColumn();

    // Pending Tasks
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM project_tasks WHERE account_number = ? AND status = 'pending'");
    $stmt->execute([$account_number]);
    $stats['pending_tasks'] = $stmt->fetchColumn();

    // Team Members
    $stmt = $pdo->prepare("SELECT COUNT(DISTINCT team_member_id) FROM project_team_members WHERE account_number = ?");
    $stmt->execute([$account_number]);
    $stats['team_members'] = $stmt->fetchColumn();

} catch (PDOException $e) {
    error_log("Error in Project Dashboard: " . $e->getMessage());
    $error_message = "An error occurred while fetching dashboard data.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Management Dashboard</title>
    <link rel="stylesheet" href="../../css/root.css">
    <link rel="stylesheet" href="../../css/reset.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="css/dashboard-project.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body id="project-dashboard">
    <?php include('../../main/sidebar.php'); ?>
    
    <div class="dashboard-content">
        <header class="dashboard-header">
            <h1>Project Management Dashboard</h1>
        </header>

        <div class="stats-container">
            <div class="stat-card">
                <i class="material-icons">folder</i>
                <div class="stat-info">
                    <h3>Total Projects</h3>
                    <p><?php echo $stats['total_projects']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">work</i>
                <div class="stat-info">
                    <h3>Active Projects</h3>
                    <p><?php echo $stats['active_projects']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">assignment</i>
                <div class="stat-info">
                    <h3>Pending Tasks</h3>
                    <p><?php echo $stats['pending_tasks']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">groups</i>
                <div class="stat-info">
                    <h3>Team Members</h3>
                    <p><?php echo $stats['team_members']; ?></p>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Project Status Overview</h2>
                <canvas id="projectStatusChart"></canvas>
            </div>
            <div class="dashboard-card">
                <h2>Recent Activities</h2>
                <div class="activity-list">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT activity_type, description, created_at 
                            FROM project_activities 
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
                <h2>Upcoming Deadlines</h2>
                <div class="deadline-list">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT p.name as project_name, t.name as task_name, t.due_date 
                            FROM project_tasks t
                            JOIN projects p ON t.project_id = p.id
                            WHERE t.account_number = ? AND t.status != 'completed'
                            ORDER BY t.due_date ASC
                            LIMIT 5
                        ");
                        $stmt->execute([$account_number]);
                        while ($deadline = $stmt->fetch()) {
                            echo "<div class='deadline-item'>";
                            echo "<span class='deadline-project'>" . htmlspecialchars($deadline['project_name']) . "</span>";
                            echo "<span class='deadline-task'>" . htmlspecialchars($deadline['task_name']) . "</span>";
                            echo "<span class='deadline-date'>" . date('M d, Y', strtotime($deadline['due_date'])) . "</span>";
                            echo "</div>";
                        }
                    } catch (PDOException $e) {
                        echo "<p>Error loading upcoming deadlines.</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="dashboard-card">
                <h2>Resource Allocation</h2>
                <canvas id="resourceChart"></canvas>
            </div>
        </div>
    </div>

    <script src="../../js/sidebar.js"></script>
    <script src="js/dashboard-project.js"></script>
</body>
</html> 