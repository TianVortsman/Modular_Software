<?php
session_start();

// Check if account number is in the query parameters
if (isset($_GET['account_number'])) {
    $account_number = $_GET['account_number'];

    // Store the account number in the session
    $_SESSION['account_number'] = $account_number;

    // Optionally, redirect to remove the query parameter from the URL
    header("Location: dashboard.php");
    exit;
}

// If the account number is already in the session, use it
if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
} else {
    // Redirect to login or show an error if no account number is found
    header("Location: techlogin.php");
    exit;
}

$userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');
$multiple_accounts = isset($_SESSION['multiple_accounts']) ? $_SESSION['multiple_accounts'] : false;

// Include the database connection
include('../../../php/db.php');
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Management - Time and Attendance</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="../../../css/root.css">
  <link rel="stylesheet" href="../../../css/sidebar.css">
  <link rel="stylesheet" href="../css/TandA.css">
  <link rel="stylesheet" href="../css/modals.css">
  <link rel="stylesheet" href="../css/employees.css">
  <script src="../../../js/sidebar.js"></script>
  <script src="../../../js/toggle-theme.js"></script>
</head>
<body id="TA-employees">
  <!-- Sidebar -->
  <?php include('../../../main/sidebar.php') ?>
  
  <div class="dashboard-container">
    <!-- Header Section with Employee Count and License Info -->
    <section id="employee-summary" class="widget-section">
      <div class="widget-container">
        <div class="widget" id="employee-count-widget">
          <div class="widget-header">
            <h3>Employee Overview</h3>
            <span class="material-icons widget-icon">people</span>
          </div>
          <div class="widget-content">
            <div class="employee-stats">
              <div class="stat-item">
                <span class="stat-label">Total Employees:</span>
                <span class="stat-value" id="total-employees">247</span>
              </div>
              <div class="stat-item">
                <span class="stat-label">License Limit:</span>
                <span class="stat-value" id="license-limit">300</span>
              </div>
              <div class="stat-item">
                <span class="stat-label">License Usage:</span>
                <span class="stat-value" id="license-usage">82%</span>
                <div class="progress-bar">
                  <div class="progress" style="width: 82%;"></div>
                </div>
              </div>
            </div>
            <div class="help-widget">
              <span class="material-icons help-icon">help_outline</span>
              <div class="help-tooltip">
                <h4>Quick Tips</h4>
                <ul>
                  <li>Double-click on any employee to view detailed information</li>
                  <li>Use the filter options to quickly find specific employees</li>
                  <li>Export employee data using the export button</li>
                </ul>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- Employee Management Section -->
    <section id="employee-management" class="widget-section">
      <h2>Employee Management</h2>
      
      <!-- Action Buttons -->
      <div class="action-buttons">
        <div class="search-container">
          <input type="text" id="employee-search" placeholder="Search employees...">
          <span class="material-icons search-icon">search</span>
        </div>
      </div>
      
      <!-- Main Tabs -->
      <div class="tabs-container">
        <div class="tabs-header">
          <button class="tab-button active" data-tab="active">Active</button>
          <button class="tab-button" data-tab="terminated">Terminated</button>
          <button class="tab-button" data-tab="incomplete">Incomplete</button>
          <button class="tab-button" data-tab="all">All Employees</button>
        </div>
        
        <!-- Tab Content -->
        <div class="tab-content">
          <!-- Active Tab -->
          <div class="tab-pane active" id="active-tab">
            <!-- Sub-tabs for Active Employees -->
            <div class="sub-tabs-header">
              <button class="sub-tab-button active" data-subtab="permanent">Permanent</button>
              <button class="sub-tab-button" data-subtab="temporary">Temporary</button>
            </div>
            
            <!-- Permanent Employees Sub-tab -->
            <div class="sub-tab-pane active" id="permanent-tab">
              <div class="table-container">
                <table class="employee-table">
                  <thead>
                    <tr>
                      <th>Employee ID</th>
                      <th>Name</th>
                      <th>Department</th>
                      <th>Position</th>
                      <th>Start Date</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="employee-row" data-employee-id="EMP001">
                      <td>EMP001</td>
                      <td>John Smith</td>
                      <td>Sales</td>
                      <td>Sales Manager</td>
                      <td>01/15/2020</td>
                      <td><span class="status-badge active">Active</span></td>
                    </tr>
                    <tr class="employee-row" data-employee-id="EMP002">
                      <td>EMP002</td>
                      <td>Sarah Johnson</td>
                      <td>Administration</td>
                      <td>Office Manager</td>
                      <td>03/22/2019</td>
                      <td><span class="status-badge active">Active</span></td>
                    </tr>
                    <tr class="employee-row" data-employee-id="EMP003">
                      <td>EMP003</td>
                      <td>Michael Brown</td>
                      <td>Warehouse</td>
                      <td>Warehouse Supervisor</td>
                      <td>06/10/2021</td>
                      <td><span class="status-badge active">Active</span></td>
                    </tr>
                    <tr class="employee-row" data-employee-id="EMP004">
                      <td>EMP004</td>
                      <td>Emily Davis</td>
                      <td>Sales</td>
                      <td>Sales Representative</td>
                      <td>11/05/2020</td>
                      <td><span class="status-badge active">Active</span></td>
                    </tr>
                    <tr class="employee-row" data-employee-id="EMP005">
                      <td>EMP005</td>
                      <td>Robert Wilson</td>
                      <td>IT</td>
                      <td>IT Specialist</td>
                      <td>02/15/2022</td>
                      <td><span class="status-badge active">Active</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="pagination">
                <button class="pagination-button"><span class="material-icons">first_page</span></button>
                <button class="pagination-button"><span class="material-icons">chevron_left</span></button>
                <span class="pagination-info">Page 1 of 5</span>
                <button class="pagination-button"><span class="material-icons">chevron_right</span></button>
                <button class="pagination-button"><span class="material-icons">last_page</span></button>
              </div>
            </div>
            
            <!-- Temporary Employees Sub-tab -->
            <div class="sub-tab-pane" id="temporary-tab">
              <div class="table-container">
                <table class="employee-table">
                  <thead>
                    <tr>
                      <th>Employee ID</th>
                      <th>Name</th>
                      <th>Department</th>
                      <th>Position</th>
                      <th>Start Date</th>
                      <th>End Date</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="employee-row" data-employee-id="TEMP001">
                      <td>TEMP001</td>
                      <td>Jennifer Lee</td>
                      <td>Sales</td>
                      <td>Seasonal Sales Associate</td>
                      <td>11/01/2023</td>
                      <td>01/15/2024</td>
                      <td><span class="status-badge temp">Temporary</span></td>
                    </tr>
                    <tr class="employee-row" data-employee-id="TEMP002">
                      <td>TEMP002</td>
                      <td>David Clark</td>
                      <td>Warehouse</td>
                      <td>Seasonal Warehouse Worker</td>
                      <td>10/15/2023</td>
                      <td>01/15/2024</td>
                      <td><span class="status-badge temp">Temporary</span></td>
                    </tr>
                    <tr class="employee-row" data-employee-id="TEMP003">
                      <td>TEMP003</td>
                      <td>Amanda Martinez</td>
                      <td>Administration</td>
                      <td>Temporary Assistant</td>
                      <td>09/01/2023</td>
                      <td>03/01/2024</td>
                      <td><span class="status-badge temp">Temporary</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              <div class="pagination">
                <button class="pagination-button"><span class="material-icons">first_page</span></button>
                <button class="pagination-button"><span class="material-icons">chevron_left</span></button>
                <span class="pagination-info">Page 1 of 1</span>
                <button class="pagination-button"><span class="material-icons">chevron_right</span></button>
                <button class="pagination-button"><span class="material-icons">last_page</span></button>
              </div>
            </div>
          </div>
          
          <!-- Terminated Tab -->
          <div class="tab-pane" id="terminated-tab">
            <div class="table-container">
              <table class="employee-table">
                <thead>
                  <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Reason</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="employee-row" data-employee-id="EMP006">
                    <td>EMP006</td>
                    <td>Thomas Anderson</td>
                    <td>IT</td>
                    <td>Developer</td>
                    <td>05/10/2019</td>
                    <td>08/15/2023</td>
                    <td>Resignation</td>
                  </tr>
                  <tr class="employee-row" data-employee-id="EMP007">
                    <td>EMP007</td>
                    <td>Lisa Johnson</td>
                    <td>Sales</td>
                    <td>Sales Representative</td>
                    <td>03/22/2020</td>
                    <td>07/01/2023</td>
                    <td>Better Opportunity</td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="pagination">
              <button class="pagination-button"><span class="material-icons">first_page</span></button>
              <button class="pagination-button"><span class="material-icons">chevron_left</span></button>
              <span class="pagination-info">Page 1 of 1</span>
              <button class="pagination-button"><span class="material-icons">chevron_right</span></button>
              <button class="pagination-button"><span class="material-icons">last_page</span></button>
            </div>
          </div>
          
          <!-- Incomplete Tab -->
          <div class="tab-pane" id="incomplete-tab">
            <div class="table-container">
              <table class="employee-table">
                <thead>
                  <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Missing Information</th>
                    <th>Created Date</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="employee-row" data-employee-id="INC001">
                    
                    <td>INC001</td>
                    <td>Kevin Williams</td>
                    <td>Tax Information, Emergency Contact</td>
                    <td>11/01/2023</td>
                    <td><span class="status-badge incomplete">Incomplete</span></td>
                  </tr>
                  <tr class="employee-row" data-employee-id="INC002">
                    
                    <td>INC002</td>
                    <td>Maria Garcia</td>
                    <td>Banking Details, ID Verification</td>
                    <td>11/05/2023</td>
                    <td><span class="status-badge incomplete">Incomplete</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="pagination">
              <button class="pagination-button"><span class="material-icons">first_page</span></button>
              <button class="pagination-button"><span class="material-icons">chevron_left</span></button>
              <span class="pagination-info">Page 1 of 1</span>
              <button class="pagination-button"><span class="material-icons">chevron_right</span></button>
              <button class="pagination-button"><span class="material-icons">last_page</span></button>
            </div>
          </div>
          
          <!-- All Employees Tab -->
          <div class="tab-pane" id="all-tab">
            <div class="table-container">
              <table class="employee-table">
                <thead>
                  <tr>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Start Date</th>
                    <th>Status</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- This would contain all employees from all categories -->
                  <tr class="employee-row" data-employee-id="EMP001">
                    
                    <td>EMP001</td>
                    <td>John Smith</td>
                    <td>Sales</td>
                    <td>Sales Manager</td>
                    <td>01/15/2020</td>
                    <td><span class="status-badge active">Active</span></td>
                  </tr>
                  <tr class="employee-row" data-employee-id="TEMP001">
                    
                    <td>TEMP001</td>
                    <td>Jennifer Lee</td>
                    <td>Sales</td>
                    <td>Seasonal Sales Associate</td>
                    <td>11/01/2023</td>
                    <td><span class="status-badge temp">Temporary</span></td>
                  </tr>
                  <tr class="employee-row" data-employee-id="EMP006">
                    
                    <td>EMP006</td>
                    <td>Thomas Anderson</td>
                    <td>IT</td>
                    <td>Developer</td>
                    <td>05/10/2019</td>
                    <td><span class="status-badge terminated">Terminated</span></td>
                  </tr>
                  <tr class="employee-row" data-employee-id="INC001">
                    
                    <td>INC001</td>
                    <td>Kevin Williams</td>
                    <td>Marketing</td>
                    <td>Marketing Specialist</td>
                    <td>11/01/2023</td>
                    <td><span class="status-badge incomplete">Incomplete</span></td>
                  </tr>
                </tbody>
              </table>
            </div>
            <div class="pagination">
              <button class="pagination-button"><span class="material-icons">first_page</span></button>
              <button class="pagination-button"><span class="material-icons">chevron_left</span></button>
              <span class="pagination-info">Page 1 of 10</span>
              <button class="pagination-button"><span class="material-icons">chevron_right</span></button>
              <button class="pagination-button"><span class="material-icons">last_page</span></button>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
<!-- Employee Overview Modal -->
<div id="employee-overview-modal" class="modal">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Employee Overview Details</h2>
      <span class="modal-close material-icons">close</span>
    </div>
    <div class="modal-body">
      <div class="overview-stats-container">
        <div class="overview-section">
          <h3>Employee Statistics</h3>
          <div class="employee-stats-grid">
            <div class="stat-item">
              <span class="stat-label">Total Employees:</span>
              <span class="stat-value" id="modal-total-employees">247</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">License Limit:</span>
              <span class="stat-value" id="modal-license-limit">300</span>
            </div>
            <div class="stat-item">
              <span class="stat-label">License Usage:</span>
              <span class="stat-value" id="modal-license-usage">82%</span>
              <div class="progress-bar">
                <div class="progress" style="width: 82%;"></div>
              </div>
            </div>
          </div>
        </div>
        
        <div class="overview-section">
          <h3>Employee Distribution</h3>
          <div class="distribution-grid">
            <div class="distribution-item">
              <span class="distribution-label">By Department</span>
              <div class="distribution-chart">
                <div class="chart-bar" style="width: 40%;">
                  <span class="chart-label">Sales (40%)</span>
                </div>
                <div class="chart-bar" style="width: 30%;">
                  <span class="chart-label">Admin (30%)</span>
                </div>
                <div class="chart-bar" style="width: 20%;">
                  <span class="chart-label">Warehouse (20%)</span>
                </div>
                <div class="chart-bar" style="width: 10%;">
                  <span class="chart-label">IT (10%)</span>
                </div>
              </div>
            </div>
            <div class="distribution-item">
              <span class="distribution-label">By Status</span>
              <div class="status-distribution">
                <div class="status-item">
                  <span class="status-badge active">Active</span>
                  <span class="status-count">210</span>
                </div>
                <div class="status-item">
                  <span class="status-badge temp">Temporary</span>
                  <span class="status-count">25</span>
                </div>
                <div class="status-item">
                  <span class="status-badge incomplete">Incomplete</span>
                  <span class="status-count">12</span>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
</div>

  <script src="../js/employees.js"></script>
  </body>
  </html>