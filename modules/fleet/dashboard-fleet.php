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
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch Fleet statistics
    $stats = [
        'total_vehicles' => 0,
        'active_trips' => 0,
        'maintenance_due' => 0,
        'fuel_efficiency' => 0
    ];

    // Total Vehicles
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM fleet_vehicles WHERE account_number = ?");
    $stmt->execute([$account_number]);
    $stats['total_vehicles'] = $stmt->fetchColumn();

    // Active Trips
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM fleet_trips WHERE account_number = ? AND status = 'active'");
    $stmt->execute([$account_number]);
    $stats['active_trips'] = $stmt->fetchColumn();

    // Vehicles Due for Maintenance
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM fleet_vehicles 
        WHERE account_number = ? 
        AND (
            next_service_date <= CURDATE() 
            OR current_mileage >= next_service_mileage
        )
    ");
    $stmt->execute([$account_number]);
    $stats['maintenance_due'] = $stmt->fetchColumn();

    // Average Fleet Fuel Efficiency (km/L)
    $stmt = $pdo->prepare("
        SELECT AVG(fuel_efficiency) 
        FROM fleet_fuel_logs 
        WHERE account_number = ?
    ");
    $stmt->execute([$account_number]);
    $stats['fuel_efficiency'] = number_format($stmt->fetchColumn(), 1);

} catch (PDOException $e) {
    error_log("Error in Fleet Dashboard: " . $e->getMessage());
    $error_message = "An error occurred while fetching dashboard data.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fleet Management Dashboard</title>
    <link rel="stylesheet" href="../../css/root.css">
    <link rel="stylesheet" href="../../css/reset.css">
    <link rel="stylesheet" href="../../css/dashboard.css">
    <link rel="stylesheet" href="../../css/sidebar.css">
    <link rel="stylesheet" href="css/dashboard-fleet.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body id="fleet-dashboard">
    <?php include('../../main/sidebar.php'); ?>
    
    <div class="dashboard-content">
        <header class="dashboard-header">
            <h1>Fleet Management Dashboard</h1>
        </header>

        <div class="stats-container">
            <div class="stat-card">
                <i class="material-icons">directions_car</i>
                <div class="stat-info">
                    <h3>Total Vehicles</h3>
                    <p><?php echo $stats['total_vehicles']; ?></p>
                </div>
            </div>
            <div class="stat-card">
                <i class="material-icons">route</i>
                <div class="stat-info">
                    <h3>Active Trips</h3>
                    <p><?php echo $stats['active_trips']; ?></p>
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
                <i class="material-icons">local_gas_station</i>
                <div class="stat-info">
                    <h3>Avg Fuel Efficiency</h3>
                    <p><?php echo $stats['fuel_efficiency']; ?> km/L</p>
                </div>
            </div>
        </div>

        <div class="dashboard-grid">
            <div class="dashboard-card">
                <h2>Vehicle Status Overview</h2>
                <canvas id="vehicleStatusChart"></canvas>
            </div>
            <div class="dashboard-card">
                <h2>Upcoming Maintenance</h2>
                <div class="maintenance-list">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT v.vehicle_number, v.model, v.next_service_date, v.next_service_mileage 
                            FROM fleet_vehicles v
                            WHERE v.account_number = ? 
                            AND (
                                v.next_service_date <= DATE_ADD(CURDATE(), INTERVAL 30 DAY)
                                OR v.current_mileage >= (v.next_service_mileage - 1000)
                            )
                            ORDER BY v.next_service_date ASC
                            LIMIT 5
                        ");
                        $stmt->execute([$account_number]);
                        while ($maintenance = $stmt->fetch()) {
                            echo "<div class='maintenance-item'>";
                            echo "<span class='vehicle-info'>" . htmlspecialchars($maintenance['vehicle_number']) . " - " . htmlspecialchars($maintenance['model']) . "</span>";
                            echo "<span class='service-date'>Service Due: " . date('M d, Y', strtotime($maintenance['next_service_date'])) . "</span>";
                            echo "<span class='service-mileage'>At: " . number_format($maintenance['next_service_mileage']) . " km</span>";
                            echo "</div>";
                        }
                    } catch (PDOException $e) {
                        echo "<p>Error loading maintenance schedule.</p>";
                    }
                    ?>
                </div>
            </div>
            <div class="dashboard-card">
                <h2>Fuel Consumption Trend</h2>
                <canvas id="fuelTrendChart"></canvas>
            </div>
            <div class="dashboard-card">
                <h2>Active Trip Map</h2>
                <div class="trip-list">
                    <?php
                    try {
                        $stmt = $pdo->prepare("
                            SELECT t.id, v.vehicle_number, d.name as driver_name, t.start_location, t.destination, t.start_time
                            FROM fleet_trips t
                            JOIN fleet_vehicles v ON t.vehicle_id = v.id
                            JOIN fleet_drivers d ON t.driver_id = d.id
                            WHERE t.account_number = ? AND t.status = 'active'
                            ORDER BY t.start_time DESC
                            LIMIT 5
                        ");
                        $stmt->execute([$account_number]);
                        while ($trip = $stmt->fetch()) {
                            echo "<div class='trip-item'>";
                            echo "<span class='trip-vehicle'>" . htmlspecialchars($trip['vehicle_number']) . "</span>";
                            echo "<span class='trip-driver'>" . htmlspecialchars($trip['driver_name']) . "</span>";
                            echo "<span class='trip-route'>" . htmlspecialchars($trip['start_location']) . " → " . htmlspecialchars($trip['destination']) . "</span>";
                            echo "<span class='trip-time'>Started: " . date('M d, H:i', strtotime($trip['start_time'])) . "</span>";
                            echo "</div>";
                        }
                    } catch (PDOException $e) {
                        echo "<p>Error loading active trips.</p>";
                    }
                    ?>
                </div>
            </div>
        </div>
    </div>

    <script src="../../js/sidebar.js"></script>
    <script src="js/dashboard-fleet.js"></script>
</body>
</html> 