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

    // Fetch Asset statistics
    $stats = [
        'total_assets' => 0,
        'total_value' => 0,
        'maintenance_due' => 0,
        'expiring_licenses' => 0
    ];

    // Total Assets
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM assets WHERE account_number = ?");
    $stmt->execute([$account_number]);
    $stats['total_assets'] = $stmt->fetchColumn();

    // Total Asset Value
    $stmt = $pdo->prepare("SELECT SUM(current_value) FROM assets WHERE account_number = ?");
    $stmt->execute([$account_number]);
    $stats['total_value'] = number_format($stmt->fetchColumn(), 2);

    // Assets Due for Maintenance
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM assets 
        WHERE account_number = ? 
        AND next_maintenance_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
    ");
    $stmt->execute([$account_number]);
    $stats['maintenance_due'] = $stmt->fetchColumn();

    // Expiring Licenses
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM asset_licenses 
        WHERE account_number = ? 
        AND expiry_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
        AND status = 'active'
    ");
    $stmt->execute([$account_number]);
    $stats['expiring_licenses'] = $stmt->fetchColumn();

} catch (PDOException $e) {
    error_log("Error in Asset Dashboard: " . $e->getMessage());
    $error_message = "An error occurred while fetching dashboard data.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Asset Management Dashboard</title>
    <link rel="stylesheet" href="../../css/root.css">
    <link rel="stylesheet" href="../../css/reset.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="css/dashboard-asset.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/modular1/js/toggle-theme.js"></script>
</head>
<body id="asset-dashboard">
    <?php include('../../main/sidebar.php'); ?>
    
    <div class="dashboard-content">
        <header class="dashboard-header">
            <h1>Asset Management Dashboard</h1>
            <div class="header-actions">
                <button onclick="window.location.href='pages/assets.php'" class="btn-primary">
                    <i class="material-icons">add</i> Add Asset
                </button>
                <button onclick="window.location.href='pages/reports.php'" class="btn-secondary">
                    <i class="material-icons">description</i> Generate Report
                </button>
            </div>
        </header>

        <div class="stats-container">
            <div class="stat-card">
                <i class="material-icons">business_center</i>
                <div class="stat-info">
                    <h3>Total Assets</h3>
                    <p><?php echo $stats['total_assets']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">attach_money</i>
                <div class="stat-info">
                    <h3>Total Value</h3>
                    <p>$<?php echo $stats['total_value']; ?></p>
                </div>
            </div>
            <div class="stat-card warning">
                <i class="material-icons">build</i>
                <div class="stat-info">
                    <h3>Maintenance Due</h3>
                    <p><?php echo $stats['maintenance_due']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">vpn_key</i>
                <div class="stat-info">
                    <h3>Expiring Licenses</h3>
                    <p><?php echo $stats['expiring_licenses']; ?></p>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Asset Distribution by Category</h2>
                <canvas id="categoryChart"></canvas>
            </div>
            <div class="dashboard-card">
                <h2>Upcoming Maintenance</h2>
                <div class="maintenance-list">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT name, asset_tag, next_maintenance_date, maintenance_type
                            FROM assets 
                            WHERE account_number = ? 
                            AND next_maintenance_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                            ORDER BY next_maintenance_date ASC
                            LIMIT 5
                        ");
                        $stmt->execute([$account_number]);
                        while ($maintenance = $stmt->fetch()) {
                            echo "<div class='maintenance-item'>";
                            echo "<span class='asset-info'>" . htmlspecialchars($maintenance['name']) . " (" . htmlspecialchars($maintenance['asset_tag']) . ")</span>";
                            echo "<span class='maintenance-type'>" . htmlspecialchars($maintenance['maintenance_type']) . "</span>";
                            echo "<span class='maintenance-date'>Due: " . date('M d, Y', strtotime($maintenance['next_maintenance_date'])) . "</span>";
                            echo "</div>";
                        }
                    } catch (PDOException $e) {
                        echo "<p>Error loading maintenance schedule.</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="dashboard-card">
                <h2>Depreciation Overview</h2>
                <canvas id="depreciationChart"></canvas>
            </div>
            <div class="dashboard-card">
                <h2>License Expiry Timeline</h2>
                <div class="license-list">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT l.name, l.license_key, l.expiry_date, a.name as asset_name
                            FROM asset_licenses l
                            LEFT JOIN assets a ON l.asset_id = a.id
                            WHERE l.account_number = ? 
                            AND l.expiry_date <= DATE_ADD(CURDATE(), INTERVAL 90 DAY)
                            AND l.status = 'active'
                            ORDER BY l.expiry_date ASC
                            LIMIT 5
                        ");
                        $stmt->execute([$account_number]);
                        while ($license = $stmt->fetch()) {
                            $daysUntilExpiry = floor((strtotime($license['expiry_date']) - time()) / (60 * 60 * 24));
                            $urgencyClass = $daysUntilExpiry <= 7 ? 'critical' : ($daysUntilExpiry <= 30 ? 'warning' : 'normal');
                            
                            echo "<div class='license-item {$urgencyClass}'>";
                            echo "<span class='license-name'>" . htmlspecialchars($license['name']) . "</span>";
                            echo "<span class='license-asset'>" . htmlspecialchars($license['asset_name']) . "</span>";
                            echo "<span class='license-expiry'>Expires: " . date('M d, Y', strtotime($license['expiry_date'])) . "</span>";
                            echo "</div>";
                        }
                    } catch (PDOException $e) {
                        echo "<p>Error loading license information.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Asset Category Distribution Chart
        const categoryCtx = document.getElementById('categoryChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'doughnut',
            data: {
                labels: ['IT Equipment', 'Furniture', 'Vehicles', 'Machinery', 'Office Equipment'],
                datasets: [{
                    data: [35, 20, 15, 20, 10],
                    backgroundColor: [
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
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

        // Depreciation Chart
        const depreciationCtx = document.getElementById('depreciationChart').getContext('2d');
        new Chart(depreciationCtx, {
            type: 'line',
            data: {
                labels: ['2023', '2024', '2025', '2026', '2027'],
                datasets: [{
                    label: 'Projected Asset Value',
                    data: [500000, 425000, 361250, 307062, 260000],
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
                            text: 'Value ($)'
                        }
                    }
                }
            }
        });
    </script>
    <script src="../../js/sidebar.js"></script>
    <script src="js/dashboard-asset.js"></script>
</body>
</html> 