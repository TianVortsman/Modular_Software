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
    $dsn = "pgsql:host=$host;port=5432;dbname=$db";
    $pdo = new PDO($dsn, $user, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Fetch Payroll statistics
    $stats = [
        'total_employees' => 0,
        'monthly_payroll' => 0,
        'pending_approvals' => 0,
        'next_payday' => ''
    ];

    // Total Employees on Payroll
    $stmt = $pdo->prepare("SELECT COUNT(*) FROM employees WHERE account_number = ? AND payroll_status = 'active'");
    $stmt->execute([$account_number]);
    $stats['total_employees'] = $stmt->fetchColumn();

    // Monthly Payroll Amount
    $stmt = $pdo->prepare("
        SELECT SUM(base_salary + allowances) 
        FROM employee_compensation 
        WHERE account_number = ? AND status = 'active'
    ");
    $stmt->execute([$account_number]);
    $stats['monthly_payroll'] = number_format($stmt->fetchColumn(), 2);

    // Pending Payroll Approvals
    $stmt = $pdo->prepare("
        SELECT COUNT(*) 
        FROM payroll_adjustments 
        WHERE account_number = ? 
        AND status = 'pending'
    ");
    $stmt->execute([$account_number]);
    $stats['pending_approvals'] = $stmt->fetchColumn();

    // Next Payday
    $stmt = $pdo->prepare("
        SELECT next_payday 
        FROM payroll_schedule 
        WHERE account_number = ? 
        AND next_payday > CURDATE()
        ORDER BY next_payday ASC 
        LIMIT 1
    ");
    $stmt->execute([$account_number]);
    $stats['next_payday'] = $stmt->fetchColumn() ?: date('Y-m-d', strtotime('last day of this month'));

} catch (PDOException $e) {
    error_log("Error in Payroll Dashboard: " . $e->getMessage());
    $error_message = "An error occurred while fetching dashboard data.";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payroll Dashboard</title>
    <link rel="stylesheet" href="/modular1/css/root.css">
    <link rel="stylesheet" href="/modular1/css/sidebar.css">
    <link rel="stylesheet" href="https://fonts.googleapis.com/icon?family=Material+Icons">
    <link rel="stylesheet" href="/modular1/css/dashboard.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="/modular1/js/toggle-theme.js"></script>
</head>
<body id="payroll-dashboard">
    <div class="dashboard-container">
        <?php 
        include '../../includes/sidebar.php';
        include '../../includes/response-modal.php';
        include '../../includes/loading-modal.php';
        ?>
        
        <main class="main-content">
            <div class="dashboard-header">
                <h1>Payroll Dashboard</h1>
                <div class="header-actions">
                    <button onclick="window.location.href='pages/process-payroll.php'" class="btn-primary">
                        <i class="material-icons">payments</i> Process Payroll
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
                    <i class="material-icons">account_balance_wallet</i>
                    <div class="stat-info">
                        <h3>Monthly Payroll</h3>
                        <p>$<?php echo $stats['monthly_payroll']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="material-icons">pending_actions</i>
                    <div class="stat-info">
                        <h3>Pending Approvals</h3>
                        <p><?php echo $stats['pending_approvals']; ?></p>
                    </div>
                </div>
                <div class="stat-card">
                    <i class="material-icons">event</i>
                    <div class="stat-info">
                        <h3>Next Payday</h3>
                        <p><?php echo date('M d, Y', strtotime($stats['next_payday'])); ?></p>
                    </div>
                </div>
            </div>

            <div class="dashboard-grid">
                <div class="dashboard-card">
                    <h2>Payroll Distribution</h2>
                    <canvas id="payrollDistributionChart"></canvas>
                </div>
                <div class="dashboard-card">
                    <h2>Recent Transactions</h2>
                    <div class="transaction-list">
                        <?php
                        try {
                            $stmt = $pdo->prepare("
                                SELECT pt.transaction_type, pt.amount, e.name as employee_name, pt.created_at
                                FROM payroll_transactions pt
                                JOIN employees e ON pt.employee_id = e.id
                                WHERE pt.account_number = ? 
                                ORDER BY pt.created_at DESC
                                LIMIT 5
                            ");
                            $stmt->execute([$account_number]);
                            while ($transaction = $stmt->fetch()) {
                                echo "<div class='transaction-item'>";
                                echo "<span class='transaction-employee'>" . htmlspecialchars($transaction['employee_name']) . "</span>";
                                echo "<span class='transaction-type'>" . htmlspecialchars($transaction['transaction_type']) . "</span>";
                                echo "<span class='transaction-amount'>$" . number_format($transaction['amount'], 2) . "</span>";
                                echo "<span class='transaction-date'>" . date('M d, H:i', strtotime($transaction['created_at'])) . "</span>";
                                echo "</div>";
                            }
                        } catch (PDOException $e) {
                            echo "<p>Error loading recent transactions.</p>";
                        }
                        ?>
                    </div>
                </div>
                <div class="dashboard-card">
                    <h2>Deductions Overview</h2>
                    <canvas id="deductionsChart"></canvas>
                </div>
                <div class="dashboard-card">
                    <h2>Pending Adjustments</h2>
                    <div class="adjustment-list">
                        <?php
                        try {
                            $stmt = $pdo->prepare("
                                SELECT pa.adjustment_type, pa.amount, e.name as employee_name, pa.reason
                                FROM payroll_adjustments pa
                                JOIN employees e ON pa.employee_id = e.id
                                WHERE pa.account_number = ? AND pa.status = 'pending'
                                ORDER BY pa.created_at DESC
                                LIMIT 5
                            ");
                            $stmt->execute([$account_number]);
                            while ($adjustment = $stmt->fetch()) {
                                echo "<div class='adjustment-item'>";
                                echo "<span class='adjustment-employee'>" . htmlspecialchars($adjustment['employee_name']) . "</span>";
                                echo "<span class='adjustment-type'>" . htmlspecialchars($adjustment['adjustment_type']) . "</span>";
                                echo "<span class='adjustment-amount'>$" . number_format($adjustment['amount'], 2) . "</span>";
                                echo "<span class='adjustment-reason'>" . htmlspecialchars($adjustment['reason']) . "</span>";
                                echo "</div>";
                            }
                        } catch (PDOException $e) {
                            echo "<p>Error loading pending adjustments.</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Payroll Distribution Chart
        const distributionCtx = document.getElementById('payrollDistributionChart').getContext('2d');
        new Chart(distributionCtx, {
            type: 'pie',
            data: {
                labels: ['Base Salary', 'Overtime', 'Bonuses', 'Allowances', 'Commissions'],
                datasets: [{
                    data: [65, 10, 12, 8, 5],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
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

        // Deductions Chart
        const deductionsCtx = document.getElementById('deductionsChart').getContext('2d');
        new Chart(deductionsCtx, {
            type: 'bar',
            data: {
                labels: ['Tax', 'Insurance', 'Pension', 'Loans', 'Other'],
                datasets: [{
                    label: 'Amount ($)',
                    data: [25000, 15000, 20000, 8000, 5000],
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
                            text: 'Amount ($)'
                        }
                    }
                }
            }
        });
    </script>
</body>
</html> 