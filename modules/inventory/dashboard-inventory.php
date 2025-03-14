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

//     // Fetch Inventory statistics
//     $stats = [
//         'total_items' => 0,
//         'low_stock' => 0,
//         'pending_orders' => 0,
//         'total_value' => 0
//     ];

//     // Total Items
//     $stmt = $pdo->prepare("SELECT COUNT(*) FROM inventory_items WHERE account_number = ?");
//     $stmt->execute([$account_number]);
//     $stats['total_items'] = $stmt->fetchColumn();

//     // Low Stock Items
//     $stmt = $pdo->prepare("SELECT COUNT(*) FROM inventory_items WHERE account_number = ? AND quantity <= reorder_point");
//     $stmt->execute([$account_number]);
//     $stats['low_stock'] = $stmt->fetchColumn();

//     // Pending Orders
//     $stmt = $pdo->prepare("SELECT COUNT(*) FROM purchase_orders WHERE account_number = ? AND status = 'pending'");
//     $stmt->execute([$account_number]);
//     $stats['pending_orders'] = $stmt->fetchColumn();

//     // Total Inventory Value
//     $stmt = $pdo->prepare("SELECT SUM(quantity * unit_price) FROM inventory_items WHERE account_number = ?");
//     $stmt->execute([$account_number]);
//     $stats['total_value'] = number_format($stmt->fetchColumn(), 2);

// } catch (PDOException $e) {
//     error_log("Error in Inventory Dashboard: " . $e->getMessage());
//     $error_message = "An error occurred while fetching dashboard data.";
// }
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory Dashboard</title>
    <link rel="stylesheet" href="../../css/root.css">
    <link rel="stylesheet" href="../../css/reset.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="css/dashboard-inventory.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../../js/toggle-theme.js"></script>
</head>
<body id="inventory-dashboard">
    <?php include('../../main/sidebar.php'); ?>
    
    <div class="dashboard-content">
        <header class="dashboard-header">
            <h1>Inventory Management Dashboard</h1>
            <div class="header-actions">
                <button onclick="window.location.href='pages/items.php'" class="btn-primary">
                    <i class="material-icons">add</i> Add Item
                </button>
                <button onclick="window.location.href='pages/reports.php'" class="btn-secondary">
                    <i class="material-icons">description</i> Generate Report
                </button>
            </div>
        </header>

        <div class="stats-container">
            <div class="stat-card">
                <i class="material-icons">inventory_2</i>
                <div class="stat-info">
                    <h3>Total Items</h3>
                    <p><?php echo $stats['total_items']; ?></p>
                </div>
            </div>
            <div class="stat-card warning">
                <i class="material-icons">warning</i>
                <div class="stat-info">
                    <h3>Low Stock Items</h3>
                    <p><?php echo $stats['low_stock']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">shopping_cart</i>
                <div class="stat-info">
                    <h3>Pending Orders</h3>
                    <p><?php echo $stats['pending_orders']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">attach_money</i>
                <div class="stat-info">
                    <h3>Total Value</h3>
                    <p>$<?php echo $stats['total_value']; ?></p>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Stock Level Overview</h2>
                <canvas id="stockLevelChart"></canvas>
            </div>
            <div class="dashboard-card">
                <h2>Recent Activities</h2>
                <div class="activity-list">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT activity_type, description, created_at 
                            FROM inventory_activities 
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
                <h2>Low Stock Alerts</h2>
                <div class="alert-list">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT name, quantity, reorder_point 
                            FROM inventory_items 
                            WHERE account_number = ? AND quantity <= reorder_point
                            ORDER BY quantity/reorder_point ASC
                            LIMIT 5
                        ");
                        $stmt->execute([$account_number]);
                        while ($item = $stmt->fetch()) {
                            $alertLevel = ($item['quantity'] / $item['reorder_point']) * 100;
                            $alertClass = $alertLevel <= 25 ? 'critical' : ($alertLevel <= 50 ? 'warning' : 'normal');
                            
                            echo "<div class='alert-item {$alertClass}'>";
                            echo "<span class='alert-name'>" . htmlspecialchars($item['name']) . "</span>";
                            echo "<span class='alert-quantity'>Qty: " . $item['quantity'] . "</span>";
                            echo "<span class='alert-reorder'>Reorder at: " . $item['reorder_point'] . "</span>";
                            echo "</div>";
                        }
                    } catch (PDOException $e) {
                        echo "<p>Error loading low stock alerts.</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="dashboard-card">
                <h2>Inventory Value by Category</h2>
                <canvas id="categoryValueChart"></canvas>
            </div>
        </div>
    </div>

    <script>
        // Stock Level Chart
        const stockCtx = document.getElementById('stockLevelChart').getContext('2d');
        new Chart(stockCtx, {
            type: 'bar',
            data: {
                labels: ['Optimal', 'Moderate', 'Low', 'Critical'],
                datasets: [{
                    label: 'Number of Items',
                    data: [45, 25, 15, 5],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(255, 99, 132, 0.8)'
                    ]
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        title: {
                            display: true,
                            text: 'Number of Items'
                        }
                    }
                }
            }
        });

        // Category Value Chart
        const categoryCtx = document.getElementById('categoryValueChart').getContext('2d');
        new Chart(categoryCtx, {
            type: 'pie',
            data: {
                labels: ['Electronics', 'Office Supplies', 'Furniture', 'Raw Materials', 'Tools'],
                datasets: [{
                    data: [35000, 15000, 25000, 18000, 12000],
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
    <script src="js/dashboard-inventory.js"></script>
</body>
</html> 