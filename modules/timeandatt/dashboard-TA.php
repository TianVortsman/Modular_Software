<?php
session_start();

// Check if account number is in the query parameters
if (isset($_GET['account_number'])) {
    $account_number = $_GET['account_number'];

    // Store the account number in the session
    $_SESSION['account_number'] = $account_number;

    // Optionally, redirect to remove the query parameter from the URL
    exit;
}

// If the account number is already in the session, use it
if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
} else {
    // Redirect to login or show an error if no account number is found
    header("Location: index.php");
    exit;
}

$userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');

// Include the database connection
include('../../php/db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Time and Attendance Dashboard</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="../../css/root.css">
  <link rel="stylesheet" href="../../css/sidebar.css">
  <link rel="stylesheet" href="css/TandA.css">
  <link rel="stylesheet" href="css/modals.css">
  <script src="../../js/sidebar.js"></script>
  <script src="../../js/toggle-theme.js"></script>
</head>
<body id="TandA">
  <?php include('../../main/sidebar.php') ?>
  <div class="dashboard-container">
    <!-- KPI Widgets Section -->
    <section id="kpi-summary" class="widget-section">
      <h2>Key Performance Indicators</h2>
      <div class="widget-container">
        <div class="widget" id="employees-widget" data-widget-type="employees">
          <div class="widget-header">
            <h3>Total Employees Clocked In Today</h3>
            <span class="material-icons widget-icon">people</span>
          </div>
          <div class="widget-content">
            <p class="value" id="total-clocked-in">0</p>
          </div>
        </div>
        
        <div class="widget" id="checkin-widget" data-widget-type="checkin">
          <div class="widget-header">
            <h3>Average Check-In Time</h3>
            <span class="material-icons widget-icon">schedule</span>
          </div>
          <div class="widget-content">
            <p class="value" id="avg-checkin-time">--:--</p>
          </div>
        </div>
        
        <div class="widget" id="overtime-widget" data-widget-type="overtime">
          <div class="widget-header">
            <h3>Total Overtime Hours</h3>
            <span class="material-icons widget-icon">timer</span>
          </div>
          <div class="widget-content">
            <p class="value" id="total-overtime">0</p>
          </div>
        </div>
        
        <div class="widget" id="late-widget" data-widget-type="late">
          <div class="widget-header">
            <h3>Late Arrivals</h3>
            <span class="material-icons widget-icon">warning</span>
          </div>
          <div class="widget-content">
            <p class="value" id="late-arrivals">0</p>
          </div>
        </div>
      </div>
    </section>

    <!-- Charts Widgets Section -->
    <section id="charts" class="widget-section">
      <h2>Attendance Analytics</h2>
      <div class="widget-container">
        <div class="widget chart-widget" id="attendance-chart-widget" data-widget-type="attendance-chart">
          <div class="widget-header">
            <h3>Attendance Trends</h3>
            <span class="material-icons widget-icon">trending_up</span>
          </div>
          <div class="widget-content">
            <div class="chart-container">
              <canvas id="attendanceChart"></canvas>
            </div>
          </div>
        </div>
        
        <div class="widget chart-widget" id="dept-chart-widget" data-widget-type="dept-chart">
          <div class="widget-header">
            <h3>Department/Shift Breakdown</h3>
            <span class="material-icons widget-icon">pie_chart</span>
          </div>
          <div class="widget-content">
            <div class="chart-container">
              <canvas id="deptShiftChart"></canvas>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Real-Time Widgets Section -->
    <section id="real-time" class="widget-section">
      <h2>Real-Time Monitoring</h2>
      <div class="widget-container">
        <div class="widget" id="activity-widget" data-widget-type="activity">
          <div class="widget-header">
            <h3>Real-Time Activity Feed</h3>
            <span class="material-icons widget-icon">notifications_active</span>
          </div>
          <div class="widget-content">
            <ul id="activity-feed" class="scrollable-content">
              <!-- Real-time events will be populated here -->
            </ul>
          </div>
        </div>
        
        <div class="widget" id="alerts-widget" data-widget-type="alerts">
          <div class="widget-header">
            <h3>Alerts & Notifications</h3>
            <span class="material-icons widget-icon">error_outline</span>
          </div>
          <div class="widget-content">
            <ul id="alerts-list" class="scrollable-content">
              <!-- Alerts will be populated here -->
            </ul>
          </div>
        </div>
      </div>
    </section>

    <!-- Mobile Metrics Widgets Section -->
    <section id="mobile-devices" class="widget-section">
      <h2>Mobile & Device Metrics</h2>
      <div class="widget-container">
        <div class="widget" id="mobile-metrics-widget" data-widget-type="mobile-metrics">
          <div class="widget-header">
            <h3>Mobile Clocking Metrics</h3>
            <span class="material-icons widget-icon">smartphone</span>
          </div>
          <div class="widget-content">
            <div class="metrics-grid">
              <div class="metric">
                <h4>Mobile Usage</h4>
                <p class="value" id="mobile-usage">0%</p>
              </div>
              <div class="metric">
                <h4>Success Rate</h4>
                <p class="value" id="mobile-success">0%</p>
              </div>
            </div>
          </div>
        </div>
        
        <div class="widget" id="devices-widget" data-widget-type="devices">
          <div class="widget-header">
            <h3>Devices Status Overview</h3>
            <span class="material-icons widget-icon">devices</span>
          </div>
          <div class="widget-content">
            <table id="devices-table" class="data-table">
              <thead>
                <tr>
                  <th>Device</th>
                  <th>Status</th>
                  <th>Last Check</th>
                </tr>
              </thead>
              <tbody>
                <!-- Device statuses will be populated here -->
              </tbody>
            </table>
          </div>
        </div>
      </div>
    </section>

    <!-- Historical Data Widget Section -->
    <section id="historical" class="widget-section">
      <h2>Historical Analysis</h2>
      <div class="widget-container">
        <div class="widget full-width" id="historical-widget" data-widget-type="historical">
          <div class="widget-header">
            <h3>Historical Data & Comparisons</h3>
            <span class="material-icons widget-icon">history</span>
          </div>
          <div class="widget-content">
            <div class="historical-chart">
              <canvas id="historicalChart"></canvas>
            </div>
            <div class="data-filters">
              <div class="filter-group">
                <label for="start-date">Start Date:</label>
                <input type="date" id="start-date" class="filter-input">
              </div>
              <div class="filter-group">
                <label for="end-date">End Date:</label>
                <input type="date" id="end-date" class="filter-input">
              </div>
              <button id="filter-data" class="filter-button">Apply Filter</button>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Interactive Filters Widget Section -->
    <section id="interactive-filters" class="widget-section">
      <h2>Dashboard Filters</h2>
      <div class="widget-container">
        <div class="widget full-width" id="filters-widget" data-widget-type="filters">
          <div class="widget-header">
            <h3>Interactive Filters</h3>
            <span class="material-icons widget-icon">filter_list</span>
          </div>
          <div class="widget-content">
            <div class="filters">
              <div class="filter-group">
                <label for="department-filter">Department:</label>
                <select id="department-filter" class="filter-select">
                  <option value="all">All Departments</option>
                  <!-- Departments will be populated here -->
                </select>
              </div>
              <div class="filter-group">
                <label for="shift-filter">Shift:</label>
                <select id="shift-filter" class="filter-select">
                  <option value="all">All Shifts</option>
                  <!-- Shifts will be populated here -->
                </select>
              </div>
              <div class="filter-group">
                <label for="status-filter">Status:</label>
                <select id="status-filter" class="filter-select">
                  <option value="all">All Statuses</option>
                  <option value="active">Active</option>
                  <option value="inactive">Inactive</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <script src="js/TandA.js"></script>
</body>
</html>
