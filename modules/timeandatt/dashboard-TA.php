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
  <title>Dashboard</title>
<link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="../../css/root.css">
  <link rel="stylesheet" href="../../css/sidebar.css">
  <link rel="stylesheet" href="css/TandA.css">
  <script src="../../js/sidebar.js"></script>
  <script src="../../js/toggle-theme.js"></script>
</head>
<body id="TandA">
    <?php include('../../main/sidebar.php') ?>
  <div class="dashboard-container">
    <!-- KPI Widgets -->
    <section id="kpi-summary">
      <div class="widget">
        <h2>Total Employees Clocked In Today</h2>
        <p class="value" id="total-clocked-in">0</p>
      </div>
      <div class="widget">
        <h2>Average Check-In Time</h2>
        <p class="value" id="avg-checkin-time">--:--</p>
      </div>
      <div class="widget">
        <h2>Total Overtime Hours</h2>
        <p class="value" id="total-overtime">0</p>
      </div>
      <div class="widget">
        <h2>Late Arrivals</h2>
        <p class="value" id="late-arrivals">0</p>
      </div>
    </section>

    <!-- Attendance and Department Charts -->
    <section id="charts">
      <div class="chart-widget">
        <h2>Attendance Trends</h2>
        <div class="chart-container">
          <canvas id="attendanceChart"></canvas>
        </div>
      </div>
      <div class="chart-widget">
        <h2>Department/Shift Breakdown</h2>
        <div class="chart-container">
          <canvas id="deptShiftChart"></canvas>
        </div>
      </div>
    </section>

    <!-- Real-Time Feed and Alerts -->
    <section id="real-time">
      <div class="real-time-widget">
        <h2>Real-Time Activity Feed</h2>
        <ul id="activity-feed">
          <!-- Real-time events here -->
        </ul>
      </div>
      <div class="real-time-widget">
        <h2>Alerts & Notifications</h2>
        <ul id="alerts-list">
          <!-- Alerts here -->
        </ul>
      </div>
    </section>

    <!-- Mobile Metrics and Device Status -->
    <section id="mobile-devices">
      <div class="mobile-widget">
        <h2>Mobile Clocking Metrics</h2>
        <div class="metric">
          <h3>Mobile Usage</h3>
          <p class="value" id="mobile-usage">0%</p>
        </div>
        <div class="metric">
          <h3>Success Rate</h3>
          <p class="value" id="mobile-success">0%</p>
        </div>
      </div>
      <div class="device-widget">
        <h2>Devices Status Overview</h2>
        <table id="devices-table">
          <thead>
            <tr>
              <th>Device</th>
              <th>Status</th>
              <th>Last Check</th>
            </tr>
          </thead>
          <tbody>
            <!-- Device statuses here -->
          </tbody>
        </table>
      </div>
    </section>

    <!-- Historical Data and Filters -->
    <section id="historical">
      <div class="historical-widget">
        <h2>Historical Data & Comparisons</h2>
        <div class="historical-chart">
          <canvas id="historicalChart"></canvas>
        </div>
        <div class="data-filters">
          <label for="start-date">Start Date:</label>
          <input type="date" id="start-date">
          <label for="end-date">End Date:</label>
          <input type="date" id="end-date">
          <button id="filter-data">Filter</button>
        </div>
      </div>
    </section>

    <!-- Interactive Filters -->
    <section id="interactive-filters">
      <div class="filters-widget">
        <h2>Interactive Filters</h2>
        <div class="filters">
          <label for="department-filter">Department:</label>
          <select id="department-filter">
            <option value="all">All</option>
            <!-- Departments -->
          </select>
          <label for="shift-filter">Shift:</label>
          <select id="shift-filter">
            <option value="all">All</option>
            <!-- Shifts -->
          </select>
        </div>
      </div>
    </section>
  </div>

  <script src="js/TandA.js"></script>
</body>

</html>
