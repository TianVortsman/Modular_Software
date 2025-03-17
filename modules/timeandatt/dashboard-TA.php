<?php
session_start();

// Check if account number is in the query parameters
if (isset($_GET['account_number'])) {
    $account_number = $_GET['account_number'];

    // Store the account number in the session
    $_SESSION['account_number'] = $account_number;

    // Redirect to remove the query parameter from the URL
    header("Location: dashboard-TA.php");
    exit;
}

// If the account number is already in the session, use it
if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
} else {
    // Redirect to login or show an error if no account number is found
    header("Location: ../../index.php");
    exit;
}

$userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');
$multiple_accounts = isset($_SESSION['multiple_accounts']) ? $_SESSION['multiple_accounts'] : false;

// Include the database connection
include('../../php/db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="account-number" content="<?php echo htmlspecialchars($account_number); ?>">
  <title>Time and Attendance Dashboard</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="../../css/reset.css">
  <link rel="stylesheet" href="../../css/root.css">
  <link rel="stylesheet" href="../../css/sidebar.css">
  <link rel="stylesheet" href="css/TandA.css">
  <link rel="stylesheet" href="css/modals.css">
  <script src="../../js/sidebar.js"></script>
  <script src="../../js/toggle-theme.js" type="module"></script>
</head>
<body id="TandA">
  <div class="dashboard-container">
    <!-- Sidebar -->
    <?php include('../../main/sidebar.php'); ?>
    
    <!-- Main Content -->
    <div class="main-content" id="main-content">
      <div class="header">
        <h1>Time and Attendance Dashboard</h1>
      </div>

      <div class="dashboard-widgets">
        <!-- Top Widgets -->
        <div class="widgets">
          <div class="widget" id="total-employees-widget">
            <h3>Total Employees</h3>
            <p id="total-employees">0</p>
          </div>
          <div class="widget" id="clocked-in-widget">
            <h3>Clocked In Today</h3>
            <p id="total-clocked-in">0</p>
          </div>
          <div class="widget" id="late-arrivals-widget">
            <h3>Late Arrivals</h3>
            <p id="late-arrivals">0</p>
          </div>
          <div class="widget" id="overtime-widget">
            <h3>Overtime Hours</h3>
            <p id="total-overtime">0</p>
          </div>
          <div class="widget" id="avg-checkin-widget">
            <h3>Avg. Check-In Time</h3>
            <p id="avg-checkin-time">--:--</p>
          </div>
          <div class="widget" id="absent-widget">
            <h3>Absent Today</h3>
            <p id="total-absent">0</p>
          </div>
          <div class="widget" id="leave-widget">
            <h3>On Leave</h3>
            <p id="total-leave">0</p>
          </div>
        </div>

        <!-- KPI Performance Section -->
        <div class="kpi-performance-section">
          <div class="kpi-header">
            <h2>KPI Performance</h2>
            <div class="kpi-period-selector">
              <label for="kpi-period">Time Period:</label>
              <select id="kpi-period" name="kpi-period">
                <option value="week">Last 7 Days</option>
                <option value="month" selected>Last 30 Days</option>
                <option value="quarter">Last 90 Days</option>
                <option value="year">Last 365 Days</option>
                <option value="custom">Custom Range</option>
              </select>
              <div id="custom-date-range-kpi" class="custom-date-range hidden">
                <input type="date" id="kpi-start-date" name="kpi-start-date">
                <span>to</span>
                <input type="date" id="kpi-end-date" name="kpi-end-date">
                <button id="apply-custom-dates" class="action-button small">Apply</button>
              </div>
            </div>
          </div>
          
          <div class="kpi-tabs">
            <button class="kpi-tab active" data-tab="late">Late Arrivals</button>
            <button class="kpi-tab" data-tab="absent">Absenteeism</button>
            <button class="kpi-tab" data-tab="perfect">Perfect Attendance</button>
            <button class="kpi-tab" data-tab="overtime">Overtime Utilization</button>
          </div>
          
          <div class="kpi-content-container">
            <!-- Late Arrivals Tab -->
            <div id="late-tab" class="kpi-content active">
              <div class="kpi-summary">
                <div class="kpi-stat">
                  <div class="kpi-number" id="late-employee-count">0</div>
                  <div class="kpi-label">Employees with Late Arrivals</div>
                </div>
                <div class="kpi-stat">
                  <div class="kpi-number" id="late-occurrence-count">0</div>
                  <div class="kpi-label">Total Late Occurrences</div>
                </div>
                <div class="kpi-stat">
                  <div class="kpi-number" id="late-minutes-avg">0</div>
                  <div class="kpi-label">Average Minutes Late</div>
                </div>
              </div>
              
              <table id="late-employees-table" class="data-table">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Late Count</th>
                    <th>Avg. Minutes Late</th>
                    <th>Last Late</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="6" class="loading-row">Loading data...</td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <!-- Absenteeism Tab -->
            <div id="absent-tab" class="kpi-content">
              <div class="kpi-summary">
                <div class="kpi-stat">
                  <div class="kpi-number" id="absent-employee-count">0</div>
                  <div class="kpi-label">Employees with Absences</div>
                </div>
                <div class="kpi-stat">
                  <div class="kpi-number" id="absent-days-count">0</div>
                  <div class="kpi-label">Total Absent Days</div>
                </div>
                <div class="kpi-stat">
                  <div class="kpi-number" id="absent-rate">0%</div>
                  <div class="kpi-label">Absenteeism Rate</div>
                </div>
              </div>
              
              <table id="absent-employees-table" class="data-table">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Absent Days</th>
                    <th>Absent Rate</th>
                    <th>Last Absent</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="6" class="loading-row">Loading data...</td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <!-- Perfect Attendance Tab -->
            <div id="perfect-tab" class="kpi-content">
              <div class="kpi-summary">
                <div class="kpi-stat">
                  <div class="kpi-number" id="perfect-employee-count">0</div>
                  <div class="kpi-label">Employees with Perfect Attendance</div>
                </div>
                <div class="kpi-stat">
                  <div class="kpi-number" id="perfect-percentage">0%</div>
                  <div class="kpi-label">Percentage of Workforce</div>
                </div>
                <div class="kpi-stat">
                  <div class="kpi-number" id="perfect-avg-checkin">--:--</div>
                  <div class="kpi-label">Average Check-in Time</div>
                </div>
              </div>
              
              <table id="perfect-employees-table" class="data-table">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Days Present</th>
                    <th>Avg. Check-in</th>
                    <th>Last Check-in</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="6" class="loading-row">Loading data...</td>
                  </tr>
                </tbody>
              </table>
            </div>
            
            <!-- Overtime Utilization Tab -->
            <div id="overtime-tab" class="kpi-content">
              <div class="kpi-summary">
                <div class="kpi-stat">
                  <div class="kpi-number" id="overtime-employee-count">0</div>
                  <div class="kpi-label">Employees with Overtime</div>
                </div>
                <div class="kpi-stat">
                  <div class="kpi-number" id="overtime-hours-total">0</div>
                  <div class="kpi-label">Total Overtime Hours</div>
                </div>
                <div class="kpi-stat">
                  <div class="kpi-number" id="overtime-cost">$0</div>
                  <div class="kpi-label">Overtime Cost</div>
                </div>
              </div>
              
              <table id="overtime-employees-table" class="data-table">
                <thead>
                  <tr>
                    <th>Employee</th>
                    <th>Department</th>
                    <th>Overtime Hours</th>
                    <th>Overtime Rate</th>
                    <th>Last Overtime</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td colspan="6" class="loading-row">Loading data...</td>
                  </tr>
                </tbody>
              </table>
            </div>
          </div>
        </div>

        <!-- Recent Activity -->
        <div class="recent-activity">
          <h2>Recent Activity</h2>
          <table class="ta-dashboard-table">
            <thead>
              <tr>
                <th>Employee</th>
                <th>ID/Clock</th>
                <th>Time</th>
                <th>Action</th>
                <th>Status</th>
                <th>Device</th>
              </tr>
            </thead>
            <tbody id="recent-activity-table">
              <!-- Will be populated by JavaScript -->
            </tbody>
          </table>
        </div>

        <!-- Quick Actions -->
        <div class="quick-actions">
          <h2>Quick Actions</h2>
          <button class="action-button" id="add-employee-btn">Add Employee</button>
          <button class="action-button" id="manual-clock-btn">Manual Clock Entry</button>
          <button class="action-button" id="generate-report-btn">Generate Report</button>
        </div>

        <!-- Device Status -->
        <div class="device-status-section">
          <h2>Device Status</h2>
          <table class="ta-dashboard-table" id="devices-table">
            <thead>
              <tr>
                <th>Device</th>
                <th>Status</th>
                <th>Last Check</th>
                <th>Actions</th>
              </tr>
            </thead>
            <tbody>
              <!-- Will be populated by JavaScript -->
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>

  <footer>
    <p>&copy; 2024 Modular Software. All rights reserved.</p>
  </footer>

  <!-- Add Employee Modal -->
  <div id="add-employee-modal" class="modal hidden">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Add New Employee</h2>
        <span class="close">&times;</span>
      </div>
      <div class="modal-body">
        <form id="add-employee-form">
          <div class="form-group">
            <label for="employee-name">Full Name</label>
            <input type="text" id="employee-name" name="employee-name" required>
          </div>
          <div class="form-group">
            <label for="employee-id">Employee ID</label>
            <input type="text" id="employee-id" name="employee-id" required>
          </div>
          <div class="form-group">
            <label for="department">Department</label>
            <select id="department" name="department" required>
              <option value="">Select Department</option>
              <!-- Will be populated by JavaScript -->
            </select>
          </div>
          <div class="form-group">
            <label for="position">Position</label>
            <input type="text" id="position" name="position" required>
          </div>
          <div class="form-group">
            <label for="shift">Shift</label>
            <select id="shift" name="shift" required>
              <option value="">Select Shift</option>
              <!-- Will be populated by JavaScript -->
            </select>
          </div>
          <div class="form-group">
            <label for="start-date">Start Date</label>
            <input type="date" id="start-date" name="start-date" required>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="cancel-btn close">Cancel</button>
        <button type="button" class="save-btn" id="save-employee-btn">Save Employee</button>
      </div>
    </div>
  </div>

  <!-- Manual Clock Entry Modal -->
  <div id="manual-clock-modal" class="modal hidden">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Manual Clock Entry</h2>
        <span class="close">&times;</span>
      </div>
      <div class="modal-body">
        <form id="manual-clock-form">
          <div class="form-group">
            <label for="clock-employee">Employee</label>
            <select id="clock-employee" name="clock-employee" required>
              <option value="">Select Employee</option>
              <!-- Will be populated by JavaScript -->
            </select>
          </div>
          <div class="form-group">
            <label for="clock-date">Date</label>
            <input type="date" id="clock-date" name="clock-date" required>
          </div>
          <div class="form-group">
            <label for="clock-time">Time</label>
            <input type="time" id="clock-time" name="clock-time" required>
          </div>
          <div class="form-group">
            <label for="clock-type">Clock Type</label>
            <select id="clock-type" name="clock-type" required>
              <option value="in">Clock In</option>
              <option value="out">Clock Out</option>
              <option value="break-start">Break Start</option>
              <option value="break-end">Break End</option>
            </select>
          </div>
          <div class="form-group">
            <label for="clock-notes">Notes</label>
            <textarea id="clock-notes" name="clock-notes" rows="3"></textarea>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="cancel-btn close">Cancel</button>
        <button type="button" class="save-btn" id="save-clock-btn">Save Entry</button>
      </div>
    </div>
  </div>

  <!-- Response Modal -->
  <div id="response-modal" class="modal hidden">
    <div class="modal-content">
      <div class="modal-header">
        <h2 id="response-title">Success</h2>
        <span class="close">&times;</span>
      </div>
      <div class="modal-body">
        <p id="response-message"></p>
      </div>
      <div class="modal-footer">
        <button type="button" class="ok-btn close">OK</button>
      </div>
    </div>
  </div>

  <!-- Generate Report Modal -->
  <div id="report-modal" class="modal hidden">
    <div class="modal-content">
      <div class="modal-header">
        <h2>Generate Attendance Report</h2>
        <span class="close">&times;</span>
      </div>
      <div class="modal-body">
        <form id="report-form">
          <div class="form-group">
            <label for="report-type">Report Type</label>
            <select id="report-type" name="report-type" required>
              <option value="daily">Daily Attendance</option>
              <option value="weekly">Weekly Summary</option>
              <option value="monthly">Monthly Summary</option>
              <option value="custom">Custom Date Range</option>
            </select>
          </div>
          <div class="form-group date-range hidden" id="custom-date-range">
            <div class="date-input">
              <label for="report-start-date">Start Date</label>
              <input type="date" id="report-start-date" name="report-start-date">
            </div>
            <div class="date-input">
              <label for="report-end-date">End Date</label>
              <input type="date" id="report-end-date" name="report-end-date">
            </div>
          </div>
          <div class="form-group">
            <label for="report-department">Department</label>
            <select id="report-department" name="report-department">
              <option value="all">All Departments</option>
              <!-- Will be populated by JavaScript -->
            </select>
          </div>
          <div class="form-group">
            <label for="report-format">Format</label>
            <select id="report-format" name="report-format" required>
              <option value="pdf">PDF</option>
              <option value="excel">Excel</option>
              <option value="csv">CSV</option>
            </select>
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="cancel-btn close">Cancel</button>
        <button type="button" class="save-btn" id="generate-report-btn-submit">Generate</button>
      </div>
    </div>
  </div>

  <script src="js/TandA.js"></script>
  <script src="js/dashboard-charts.js"></script>
  <script src="js/dashboard-modals.js"></script>
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      const accountNumber = document.querySelector('meta[name="account-number"]').content;
      
      // Initialize dashboard
      initializeDashboard(accountNumber);
      
      // Initialize device status table
      updateDeviceStatus();
      setInterval(updateDeviceStatus, 60000); // Update every minute
      
      // Initialize event listeners for quick action buttons
      document.getElementById('add-employee-btn').addEventListener('click', function() {
        openModal('add-employee-modal');
        loadDepartments();
        loadShifts();
      });
      
      document.getElementById('manual-clock-btn').addEventListener('click', function() {
        openModal('manual-clock-modal');
        loadEmployees();
        // Set default date to today
        document.getElementById('clock-date').valueAsDate = new Date();
      });
      
      document.getElementById('generate-report-btn').addEventListener('click', function() {
        openModal('report-modal');
        loadDepartments();
        // Set default dates
        const today = new Date();
        document.getElementById('report-start-date').valueAsDate = today;
        document.getElementById('report-end-date').valueAsDate = today;
      });
      
      // Show/hide custom date range based on report type selection
      document.getElementById('report-type').addEventListener('change', function() {
        const dateRange = document.getElementById('custom-date-range');
        if (this.value === 'custom') {
          dateRange.classList.remove('hidden');
        } else {
          dateRange.classList.add('hidden');
        }
      });
      
      // Real-time updates connection
      connectToRealTimeUpdates(accountNumber);
    });
    
    // Function to initialize dashboard data
    function initializeDashboard(accountNumber) {
      // Fetch dashboard statistics
      fetch(`../../api/timeandatt/get-dashboard-stats.php?account=${accountNumber}`)
        .then(response => response.json())
        .then(data => {
          if (data.success) {
            // Update top widgets
            document.getElementById('total-employees').textContent = data.stats.totalEmployees || 0;
            document.getElementById('total-clocked-in').textContent = data.stats.clockedInToday || 0;
            document.getElementById('late-arrivals').textContent = data.stats.lateArrivals || 0;
            document.getElementById('total-overtime').textContent = data.stats.overtimeHours || 0;
            document.getElementById('avg-checkin-time').textContent = data.stats.avgCheckinTime || '--:--';
            document.getElementById('total-absent').textContent = data.stats.absentToday || 0;
            document.getElementById('total-leave').textContent = data.stats.onLeave || 0;
            
            // Update analytics summary
            document.getElementById('avg-attendance-rate').textContent = data.stats.attendanceRate || '0%';
            document.getElementById('top-department').textContent = data.stats.topDepartment || '--';
            
            // Update recent activity table
            updateRecentActivity(data.recentActivity || []);
            
            // Update alerts list
            updateAlerts(data.alerts || []);
            
            // Initialize charts
            if (data.chartData) {
              initializeCharts(data.chartData);
            }
          } else {
            showResponse('error', 'Failed to load dashboard data: ' + data.message);
          }
        })
        .catch(error => {
          console.error('Error fetching dashboard data:', error);
          showResponse('error', 'An error occurred while loading dashboard data.');
        });
    }
    
    // Function to update device status table
    function updateDeviceStatus() {
      const accountNumber = document.querySelector('meta[name="account-number"]').content;
      const devicesTable = document.getElementById('devices-table').querySelector('tbody');
      
      // Clear existing rows
      devicesTable.innerHTML = '';
      
      // Fetch device status from server
      fetch(`../api/get_device_status.php?account=${accountNumber}`)
        .then(response => response.json())
        .then(data => {
          if (data.devices && data.devices.length > 0) {
            data.devices.forEach(device => {
              const row = document.createElement('tr');
              
              // Determine status class
              let statusClass = 'status-unknown';
              if (device.status === 'online') {
                statusClass = 'status-online';
              } else if (device.status === 'offline') {
                statusClass = 'status-offline';
              }
              
              // Format last check time
              let lastCheck = 'Never';
              if (device.last_check) {
                const lastCheckDate = new Date(device.last_check);
                lastCheck = lastCheckDate.toLocaleString();
              }
              
              row.innerHTML = `
                <td>${device.name || device.device_id}</td>
                <td><span class="device-status ${statusClass}">${device.status}</span></td>
                <td>${lastCheck}</td>
                <td>
                  <button class="action-button small" onclick="pingDevice('${device.device_id}')">Ping</button>
                  <button class="action-button small" onclick="configureDevice('${device.device_id}')">Configure</button>
                </td>
              `;
              
              devicesTable.appendChild(row);
            });
          } else {
            // No devices found
            const row = document.createElement('tr');
            row.innerHTML = `
              <td colspan="4" class="no-data">No devices configured</td>
            `;
            devicesTable.appendChild(row);
          }
        })
        .catch(error => {
          console.error('Error fetching device status:', error);
          const row = document.createElement('tr');
          row.innerHTML = `
            <td colspan="4" class="error-data">Error loading device status</td>
          `;
          devicesTable.appendChild(row);
        });
    }
    
    // Function to update recent activity table
    function updateRecentActivity(activities) {
      const activityTable = document.getElementById('recent-activity-table');
      activityTable.innerHTML = '';
      
      if (activities.length > 0) {
        activities.forEach(activity => {
          const row = document.createElement('tr');
          
          // Determine status class
          let statusClass = 'status-normal';
          if (activity.status === 'late') {
            statusClass = 'status-warning';
          } else if (activity.status === 'error' || activity.status === 'failed') {
            statusClass = 'status-error';
          }
          
          row.innerHTML = `
            <td>${activity.employee_name}</td>
            <td>${activity.employee_id}</td>
            <td>${new Date(activity.timestamp).toLocaleTimeString()}</td>
            <td>${activity.action}</td>
            <td><span class="status ${statusClass}">${activity.status}</span></td>
            <td>${activity.device || 'N/A'}</td>
          `;
          
          activityTable.appendChild(row);
        });
      } else {
        const row = document.createElement('tr');
        row.innerHTML = `
          <td colspan="6" class="no-data">No recent activity</td>
        `;
        activityTable.appendChild(row);
      }
    }
    
    // Function to update alerts list
    function updateAlerts(alerts) {
      const alertsList = document.getElementById('alerts-list');
      alertsList.innerHTML = '';
      
      if (alerts.length > 0) {
        alerts.forEach(alert => {
          const alertItem = document.createElement('li');
          alertItem.className = `alert-item alert-${alert.type}`;
          
          alertItem.innerHTML = `
            <span class="alert-time">${new Date(alert.timestamp).toLocaleTimeString()}</span>
            <span class="alert-message">${alert.message}</span>
          `;
          
          alertsList.appendChild(alertItem);
        });
      } else {
        const alertItem = document.createElement('li');
        alertItem.className = 'alert-item';
        alertItem.innerHTML = 'No alerts or notifications';
        alertsList.appendChild(alertItem);
      }
    }
    
    // Helper function to add an alert
    function addAlert(source, message, type) {
      const alertsList = document.getElementById('alerts-list');
      const alertItem = document.createElement('li');
      alertItem.className = `alert-item alert-${type}`;
      
      alertItem.innerHTML = `
        <span class="alert-time">${new Date().toLocaleTimeString()}</span>
        <span class="alert-message">${message}</span>
      `;
      
      // Add to the beginning of the list
      alertsList.insertBefore(alertItem, alertsList.firstChild);
      
      // Limit to 10 items
      if (alertsList.children.length > 10) {
        alertsList.removeChild(alertsList.lastChild);
      }
    }
    
    // Function to update dashboard stats without refreshing the page
    function updateDashboardStats() {
      const accountNumber = document.querySelector('meta[name="account-number"]').content;
      
      fetch(`../../api/timeandatt/get-dashboard-stats.php?account=${accountNumber}&stats_only=1`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.stats) {
            // Update top widgets
            document.getElementById('total-employees').textContent = data.stats.totalEmployees || 0;
            document.getElementById('total-clocked-in').textContent = data.stats.clockedInToday || 0;
            document.getElementById('late-arrivals').textContent = data.stats.lateArrivals || 0;
            document.getElementById('total-overtime').textContent = data.stats.overtimeHours || 0;
            document.getElementById('avg-checkin-time').textContent = data.stats.avgCheckinTime || '--:--';
            document.getElementById('total-absent').textContent = data.stats.absentToday || 0;
            document.getElementById('total-leave').textContent = data.stats.onLeave || 0;
            
            // Update analytics summary
            document.getElementById('avg-attendance-rate').textContent = data.stats.attendanceRate || '0%';
            document.getElementById('top-department').textContent = data.stats.topDepartment || '--';
          }
        })
        .catch(error => {
          console.error('Error updating dashboard stats:', error);
        });
    }
    
    // Modal functions
    function openModal(modalId) {
      document.getElementById(modalId).classList.remove('hidden');
    }
    
    function closeModal(modalId) {
      document.getElementById(modalId).classList.add('hidden');
    }
    
    // Set up event listeners for all modal close buttons
    document.addEventListener('DOMContentLoaded', function() {
      const closeButtons = document.querySelectorAll('.close');
      closeButtons.forEach(button => {
        button.addEventListener('click', function() {
          const modal = this.closest('.modal');
          if (modal) {
            modal.classList.add('hidden');
          }
        });
      });
    });
    
    // Helper function to show response modal
    function showResponse(type, message) {
      const modal = document.getElementById('response-modal');
      const title = document.getElementById('response-title');
      const messageEl = document.getElementById('response-message');
      
      // Set title and style based on type
      if (type === 'success') {
        title.textContent = 'Success';
        title.style.color = 'var(--color-success)';
      } else if (type === 'error') {
        title.textContent = 'Error';
        title.style.color = 'var(--color-error)';
      } else if (type === 'warning') {
        title.textContent = 'Warning';
        title.style.color = 'var(--color-warning)';
      }
      
      messageEl.textContent = message;
      openModal('response-modal');
    }
    
    // Device actions
    function pingDevice(deviceId) {
      console.log('Pinging device:', deviceId);
      // Implement ping functionality
      showResponse('success', `Ping request sent to device ${deviceId}`);
    }
    
    function configureDevice(deviceId) {
      console.log('Configure device:', deviceId);
      // Implement device configuration
      // This would typically open a configuration modal specific to the device
      showResponse('info', `Configure device functionality for ${deviceId} is not implemented yet.`);
    }
    
    // Helper functions for form loading
    function loadDepartments() {
      const accountNumber = document.querySelector('meta[name="account-number"]').content;
      
      fetch(`../../api/timeandatt/get-departments.php?account=${accountNumber}`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.departments) {
            const deptSelects = document.querySelectorAll('#department, #report-department');
            
            deptSelects.forEach(select => {
              // Clear existing options except the first one
              while (select.options.length > 1) {
                select.remove(1);
              }
              
              // Add new options
              data.departments.forEach(dept => {
                const option = document.createElement('option');
                option.value = dept.id;
                option.textContent = dept.name;
                select.appendChild(option);
              });
            });
          }
        })
        .catch(error => {
          console.error('Error loading departments:', error);
        });
    }
    
    function loadShifts() {
      const accountNumber = document.querySelector('meta[name="account-number"]').content;
      
      fetch(`../../api/timeandatt/get-shifts.php?account=${accountNumber}`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.shifts) {
            const shiftSelect = document.getElementById('shift');
            
            // Clear existing options except the first one
            while (shiftSelect.options.length > 1) {
              shiftSelect.remove(1);
            }
            
            // Add new options
            data.shifts.forEach(shift => {
              const option = document.createElement('option');
              option.value = shift.id;
              option.textContent = shift.name;
              shiftSelect.appendChild(option);
            });
          }
        })
        .catch(error => {
          console.error('Error loading shifts:', error);
        });
    }
    
    function loadEmployees() {
      const accountNumber = document.querySelector('meta[name="account-number"]').content;
      
      fetch(`../../api/timeandatt/get-employees.php?account=${accountNumber}`)
        .then(response => response.json())
        .then(data => {
          if (data.success && data.employees) {
            const empSelect = document.getElementById('clock-employee');
            
            // Clear existing options except the first one
            while (empSelect.options.length > 1) {
              empSelect.remove(1);
            }
            
            // Add new options
            data.employees.forEach(emp => {
              const option = document.createElement('option');
              option.value = emp.id;
              option.textContent = `${emp.name} (${emp.employee_id})`;
              empSelect.appendChild(option);
            });
          }
        })
        .catch(error => {
          console.error('Error loading employees:', error);
        });
    }
  </script>
</body>
</html>
