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
  <link rel="stylesheet" href="../../../public/assets/css/reset.css">
  <link rel="stylesheet" href="../../../public/assets/css/root.css">
  <link rel="stylesheet" href="../../../public/assets/css/sidebar.css">
  <link rel="stylesheet" href="../css/TandA.css">
  <link rel="stylesheet" href="../css/modals.css">
  <script src="../../../public/assets/js/sidebar.js"></script>
  <script src="../../../public/assets/js/toggle-theme.js" type="module"></script>
  <script>
    // Custom Right-Click Menu Functionality
    document.addEventListener('DOMContentLoaded', function() {
        const contextMenu = document.querySelector('.context-menu');
        const mainContent = document.getElementById('main-content');
        
        // Hide context menu when clicking outside
        document.addEventListener('click', function(e) {
            if (!contextMenu.contains(e.target)) {
                contextMenu.classList.remove('active');
            }
        });

        // Prevent default context menu
        mainContent.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            
            // Get click coordinates
            const x = e.clientX;
            const y = e.clientY;
            
            // Position the menu
            contextMenu.style.left = `${x}px`;
            contextMenu.style.top = `${y}px`;
            
            // Show the menu
            contextMenu.classList.add('active');
        });

        // Handle menu item clicks
        const menuItems = contextMenu.querySelectorAll('.context-menu-item:not(.disabled)');
        menuItems.forEach(item => {
            item.addEventListener('click', function(e) {
                e.stopPropagation();
                
                // Get the action type from the menu item
                const action = this.textContent.trim().toLowerCase();
                
                // Handle different actions
                switch(action) {
                    case 'edit':
                        // Handle edit action
                        console.log('Edit clicked');
                        break;
                    case 'delete':
                        // Handle delete action
                        console.log('Delete clicked');
                        break;
                    // Add more cases as needed
                }
                
                // Hide the menu after action
                contextMenu.classList.remove('active');
            });
        });

        // Handle submenu hover
        const submenuItems = contextMenu.querySelectorAll('.context-menu-item.has-submenu');
        submenuItems.forEach(item => {
            item.addEventListener('mouseenter', function() {
                const submenu = this.querySelector('.submenu');
                if (submenu) {
                    // Position submenu
                    const rect = this.getBoundingClientRect();
                    submenu.style.top = '0';
                    submenu.style.left = '100%';
                }
            });
        });
    });
  </script>
</head>
<body id="TandA">
<div class="context-menu">
    <div class="context-menu-item">
        <span class="material-icons">edit</span>
        Edit
    </div>
    <div class="context-menu-item">
        <span class="material-icons">delete</span>
        Delete
    </div>
    <div class="context-menu-divider"></div>
    <div class="context-menu-item has-submenu">
        <span class="material-icons">more_vert</span>
        More Options
        <div class="submenu">
            <div class="context-menu-item">Option 1</div>
            <div class="context-menu-item">Option 2</div>
        </div>
    </div>
</div>
  <div class="dashboard-container">
    <!-- Sidebar -->
    <?php include('../../../src/UI/sidebar.php'); ?>
    
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
        <div class="recent-activity" id="recent-activity-container">
          <div class="recent-activity-header">
            <h2>Recent Activity</h2>
            <div class="activity-tabs">
              <button class="activity-tab active" data-tab="attendance">Time & Attendance</button>
              <button class="activity-tab" data-tab="access">Access Control</button>
              <button class="activity-tab" data-tab="unknown">Unknown Users</button>
              <button class="activity-tab" data-tab="errors">Errors</button>
            </div>
          </div>
          
          <div class="activity-content-container">
            <!-- Time & Attendance Tab -->
            <div id="attendance-tab" class="activity-content active">
              <div class="scrollable-table-container">
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
            </div>
            
            <!-- Access Control Tab -->
            <div id="access-tab" class="activity-content">
              <div class="scrollable-table-container">
                <table class="ta-dashboard-table">
                  <thead>
                    <tr>
                      <th>Employee</th>
                      <th>ID/Card</th>
                      <th>Time</th>
                      <th>Access Type</th>
                      <th>Location</th>
                      <th>Device</th>
                    </tr>
                  </thead>
                  <tbody id="recent-access-table">
                    <!-- Will be populated by JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>
            
            <!-- Unknown Users Tab -->
            <div id="unknown-tab" class="activity-content">
              <div class="scrollable-table-container">
                <table class="ta-dashboard-table">
                  <thead>
                    <tr>
                      <th>ID/Card</th>
                      <th>Time</th>
                      <th>Action/Type</th>
                      <th>Status</th>
                      <th>Location</th>
                      <th>Device</th>
                    </tr>
                  </thead>
                  <tbody id="unknown-users-table">
                    <!-- Will be populated by JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>
            
            <!-- Errors Tab -->
            <div id="errors-tab" class="activity-content">
              <div class="scrollable-table-container">
                <table class="ta-dashboard-table">
                  <thead>
                    <tr>
                      <th>Employee</th>
                      <th>ID/Card</th>
                      <th>Time</th>
                      <th>Error Type</th>
                      <th>Details</th>
                      <th>Device</th>
                    </tr>
                  </thead>
                  <tbody id="errors-table">
                    <!-- Will be populated by JavaScript -->
                  </tbody>
                </table>
              </div>
            </div>
          </div>
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

  <script src="../js/TandA.js"></script>
</body>
</html>
