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

    // Fetch CRM statistics
    $stats = [
        'total_contacts' => 0,
        'active_leads' => 0,
        'opportunities' => 0,
        'conversion_rate' => 0
    ];

    // Total Contacts
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM crm_contacts WHERE account_number = ?");
    $stmt->execute([$account_number]);
    $stats['total_contacts'] = $stmt->fetchColumn();

    // Active Leads
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM crm_leads WHERE account_number = ? AND status = 'active'");
    $stmt->execute([$account_number]);
    $stats['active_leads'] = $stmt->fetchColumn();

    // Open Opportunities
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM crm_opportunities WHERE account_number = ? AND status = 'open'");
    $stmt->execute([$account_number]);
    $stats['opportunities'] = $stmt->fetchColumn();

    // Calculate Conversion Rate
    $stmt = $pdo->prepare("
        SELECT 
            (SELECT COUNT(*) FROM crm_leads WHERE account_number = ? AND status = 'converted') * 100.0 / 
            NULLIF((SELECT COUNT(*) FROM crm_leads WHERE account_number = ?), 0)
    ");
    $stmt->execute([$account_number, $account_number]);
    $stats['conversion_rate'] = number_format($stmt->fetchColumn(), 1);

} catch (PDOException $e) {
    error_log("Error in CRM Dashboard: " . $e->getMessage());
    $error_message = "An error occurred while fetching dashboard data.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CRM Dashboard</title>
    <link rel="stylesheet" href="../../css/root.css">
    <link rel="stylesheet" href="../../css/reset.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="css/dashboard-crm.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../js/toggle-theme.js"></script>
</head>
<body id="crm-dashboard">
    <?php include('../../main/sidebar.php'); ?>
    
    <div class="dashboard-content">
        <header class="dashboard-header">
            <h1>Customer Relationship Management Dashboard</h1>
        </header>

        <div class="stats-container">
            <div class="stat-card">
                <i class="material-icons">people</i>
                <div class="stat-info">
                    <h3>Total Contacts</h3>
                    <p><?php echo $stats['total_contacts']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">trending_up</i>
                <div class="stat-info">
                    <h3>Active Leads</h3>
                    <p><?php echo $stats['active_leads']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">lightbulb</i>
                <div class="stat-info">
                    <h3>Opportunities</h3>
                    <p><?php echo $stats['opportunities']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">show_chart</i>
                <div class="stat-info">
                    <h3>Conversion Rate</h3>
                    <p><?php echo $stats['conversion_rate']; ?>%</p>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Lead Status Distribution</h2>
                <canvas id="leadStatusChart"></canvas>
            </div>
            <div class="dashboard-card">
                <h2>Recent Activities</h2>
                <div class="activity-list">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT activity_type, description, created_at 
                            FROM crm_activities 
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
                <h2>Top Opportunities</h2>
                <div class="opportunity-list">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT name, value, probability, expected_close_date 
                            FROM crm_opportunities 
                            WHERE account_number = ? AND status = 'open'
                            ORDER BY value * probability DESC
                            LIMIT 5
                        ");
                        $stmt->execute([$account_number]);
                        while ($opportunity = $stmt->fetch()) {
                            echo "<div class='opportunity-item'>";
                            echo "<span class='opportunity-name'>" . htmlspecialchars($opportunity['name']) . "</span>";
                            echo "<span class='opportunity-value'>$" . number_format($opportunity['value'], 2) . "</span>";
                            echo "<span class='opportunity-probability'>" . $opportunity['probability'] . "%</span>";
                            echo "<span class='opportunity-date'>" . date('M d, Y', strtotime($opportunity['expected_close_date'])) . "</span>";
                            echo "</div>";
                        }
                    } catch (PDOException $e) {
                        echo "<p>Error loading opportunities.</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="dashboard-card">
                <h2>Sales Pipeline</h2>
                <canvas id="pipelineChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Lead Status Chart
        const leadCtx = document.getElementById('leadStatusChart').getContext('2d');
        new Chart(leadCtx, {
            type: 'doughnut',
            data: {
                labels: ['New', 'Contacted', 'Qualified', 'Proposal', 'Negotiation'],
                datasets: [{
                    data: [30, 25, 20, 15, 10],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(153, 102, 255, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
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

        // Sales Pipeline Chart
        const pipelineCtx = document.getElementById('pipelineChart').getContext('2d');
        new Chart(pipelineCtx, {
            type: 'bar',
            data: {
                labels: ['Prospecting', 'Qualification', 'Needs Analysis', 'Proposal', 'Negotiation', 'Closed Won'],
                datasets: [{
                    label: 'Value ($)',
                    data: [50000, 75000, 100000, 80000, 60000, 40000],
                    backgroundColor: 'rgba(54, 162, 235, 0.8)'
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Value ($)'
                        }
                    }
                }
            }
        });
    </script>
    <script src="../../js/sidebar.js"></script>
    <script src="js/dashboard-crm.js"></script>
</body>
</html> 