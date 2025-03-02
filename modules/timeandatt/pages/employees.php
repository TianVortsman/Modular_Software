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
        <button class="action-button" id="add-employee">
          <span class="material-icons">person_add</span> Add Employee
        </button>
        <button class="action-button" id="import-employees">
          <span class="material-icons">upload_file</span> Import
        </button>
        <button class="action-button" id="export-employees">
          <span class="material-icons">download</span> Export
        </button>
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
                      <th><input type="checkbox" id="select-all-permanent"></th>
                      <th>Employee ID</th>
                      <th>Name</th>
                      <th>Department</th>
                      <th>Position</th>
                      <th>Start Date</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="employee-row" data-employee-id="EMP001">
                      <td><input type="checkbox" class="employee-select"></td>
                      <td>EMP001</td>
                      <td>John Smith</td>
                      <td>Sales</td>
                      <td>Sales Manager</td>
                      <td>01/15/2020</td>
                      <td><span class="status-badge active">Active</span></td>
                      <td>
                        <button class="icon-button"><span class="material-icons">edit</span></button>
                        <button class="icon-button"><span class="material-icons">more_vert</span></button>
                      </td>
                    </tr>
                    <tr class="employee-row" data-employee-id="EMP002">
                      <td><input type="checkbox" class="employee-select"></td>
                      <td>EMP002</td>
                      <td>Sarah Johnson</td>
                      <td>Administration</td>
                      <td>Office Manager</td>
                      <td>03/22/2019</td>
                      <td><span class="status-badge active">Active</span></td>
                      <td>
                        <button class="icon-button"><span class="material-icons">edit</span></button>
                        <button class="icon-button"><span class="material-icons">more_vert</span></button>
                      </td>
                    </tr>
                    <tr class="employee-row" data-employee-id="EMP003">
                      <td><input type="checkbox" class="employee-select"></td>
                      <td>EMP003</td>
                      <td>Michael Brown</td>
                      <td>Warehouse</td>
                      <td>Warehouse Supervisor</td>
                      <td>06/10/2021</td>
                      <td><span class="status-badge active">Active</span></td>
                      <td>
                        <button class="icon-button"><span class="material-icons">edit</span></button>
                        <button class="icon-button"><span class="material-icons">more_vert</span></button>
                      </td>
                    </tr>
                    <tr class="employee-row" data-employee-id="EMP004">
                      <td><input type="checkbox" class="employee-select"></td>
                      <td>EMP004</td>
                      <td>Emily Davis</td>
                      <td>Sales</td>
                      <td>Sales Representative</td>
                      <td>11/05/2020</td>
                      <td><span class="status-badge active">Active</span></td>
                      <td>
                        <button class="icon-button"><span class="material-icons">edit</span></button>
                        <button class="icon-button"><span class="material-icons">more_vert</span></button>
                      </td>
                    </tr>
                    <tr class="employee-row" data-employee-id="EMP005">
                      <td><input type="checkbox" class="employee-select"></td>
                      <td>EMP005</td>
                      <td>Robert Wilson</td>
                      <td>IT</td>
                      <td>IT Specialist</td>
                      <td>02/15/2022</td>
                      <td><span class="status-badge active">Active</span></td>
                      <td>
                        <button class="icon-button"><span class="material-icons">edit</span></button>
                        <button class="icon-button"><span class="material-icons">more_vert</span></button>
                      </td>
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
                      <th><input type="checkbox" id="select-all-temporary"></th>
                      <th>Employee ID</th>
                      <th>Name</th>
                      <th>Department</th>
                      <th>Position</th>
                      <th>Start Date</th>
                      <th>End Date</th>
                      <th>Status</th>
                      <th>Actions</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr class="employee-row" data-employee-id="TEMP001">
                      <td><input type="checkbox" class="employee-select"></td>
                      <td>TEMP001</td>
                      <td>Jennifer Lee</td>
                      <td>Sales</td>
                      <td>Seasonal Sales Associate</td>
                      <td>11/01/2023</td>
                      <td>01/15/2024</td>
                      <td><span class="status-badge temp">Temporary</span></td>
                      <td>
                        <button class="icon-button"><span class="material-icons">edit</span></button>
                        <button class="icon-button"><span class="material-icons">more_vert</span></button>
                      </td>
                    </tr>
                    <tr class="employee-row" data-employee-id="TEMP002">
                      <td><input type="checkbox" class="employee-select"></td>
                      <td>TEMP002</td>
                      <td>David Clark</td>
                      <td>Warehouse</td>
                      <td>Seasonal Warehouse Worker</td>
                      <td>10/15/2023</td>
                      <td>01/15/2024</td>
                      <td><span class="status-badge temp">Temporary</span></td>
                      <td>
                        <button class="icon-button"><span class="material-icons">edit</span></button>
                        <button class="icon-button"><span class="material-icons">more_vert</span></button>
                      </td>
                    </tr>
                    <tr class="employee-row" data-employee-id="TEMP003">
                      <td><input type="checkbox" class="employee-select"></td>
                      <td>TEMP003</td>
                      <td>Amanda Martinez</td>
                      <td>Administration</td>
                      <td>Temporary Assistant</td>
                      <td>09/01/2023</td>
                      <td>03/01/2024</td>
                      <td><span class="status-badge temp">Temporary</span></td>
                      <td>
                        <button class="icon-button"><span class="material-icons">edit</span></button>
                        <button class="icon-button"><span class="material-icons">more_vert</span></button>
                      </td>
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
                    <th><input type="checkbox" id="select-all-terminated"></th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Reason</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="employee-row" data-employee-id="EMP006">
                    <td><input type="checkbox" class="employee-select"></td>
                    <td>EMP006</td>
                    <td>Thomas Anderson</td>
                    <td>IT</td>
                    <td>Developer</td>
                    <td>05/10/2019</td>
                    <td>08/15/2023</td>
                    <td>Resignation</td>
                    <td>
                      <button class="icon-button"><span class="material-icons">visibility</span></button>
                      <button class="icon-button"><span class="material-icons">more_vert</span></button>
                    </td>
                  </tr>
                  <tr class="employee-row" data-employee-id="EMP007">
                    <td><input type="checkbox" class="employee-select"></td>
                    <td>EMP007</td>
                    <td>Lisa Johnson</td>
                    <td>Sales</td>
                    <td>Sales Representative</td>
                    <td>03/22/2020</td>
                    <td>07/01/2023</td>
                    <td>Better Opportunity</td>
                    <td>
                      <button class="icon-button"><span class="material-icons">visibility</span></button>
                      <button class="icon-button"><span class="material-icons">more_vert</span></button>
                    </td>
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
                    <th><input type="checkbox" id="select-all-incomplete"></th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Missing Information</th>
                    <th>Created Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <tr class="employee-row" data-employee-id="INC001">
                    <td><input type="checkbox" class="employee-select"></td>
                    <td>INC001</td>
                    <td>Kevin Williams</td>
                    <td>Tax Information, Emergency Contact</td>
                    <td>11/01/2023</td>
                    <td><span class="status-badge incomplete">Incomplete</span></td>
                    <td>
                      <button class="icon-button"><span class="material-icons">edit</span></button>
                      <button class="icon-button"><span class="material-icons">more_vert</span></button>
                    </td>
                  </tr>
                  <tr class="employee-row" data-employee-id="INC002">
                    <td><input type="checkbox" class="employee-select"></td>
                    <td>INC002</td>
                    <td>Maria Garcia</td>
                    <td>Banking Details, ID Verification</td>
                    <td>11/05/2023</td>
                    <td><span class="status-badge incomplete">Incomplete</span></td>
                    <td>
                      <button class="icon-button"><span class="material-icons">edit</span></button>
                      <button class="icon-button"><span class="material-icons">more_vert</span></button>
                    </td>
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
                    <th><input type="checkbox" id="select-all-employees"></th>
                    <th>Employee ID</th>
                    <th>Name</th>
                    <th>Department</th>
                    <th>Position</th>
                    <th>Start Date</th>
                    <th>Status</th>
                    <th>Actions</th>
                  </tr>
                </thead>
                <tbody>
                  <!-- This would contain all employees from all categories -->
                  <tr class="employee-row" data-employee-id="EMP001">
                    <td><input type="checkbox" class="employee-select"></td>
                    <td>EMP001</td>
                    <td>John Smith</td>
                    <td>Sales</td>
                    <td>Sales Manager</td>
                    <td>01/15/2020</td>
                    <td><span class="status-badge active">Active</span></td>
                    <td>
                      <button class="icon-button"><span class="material-icons">edit</span></button>
                      <button class="icon-button"><span class="material-icons">more_vert</span></button>
                    </td>
                  </tr>
                  <tr class="employee-row" data-employee-id="TEMP001">
                    <td><input type="checkbox" class="employee-select"></td>
                    <td>TEMP001</td>
                    <td>Jennifer Lee</td>
                    <td>Sales</td>
                    <td>Seasonal Sales Associate</td>
                    <td>11/01/2023</td>
                    <td><span class="status-badge temp">Temporary</span></td>
                    <td>
                      <button class="icon-button"><span class="material-icons">edit</span></button>
                      <button class="icon-button"><span class="material-icons">more_vert</span></button>
                    </td>
                  </tr>
                  <tr class="employee-row" data-employee-id="EMP006">
                    <td><input type="checkbox" class="employee-select"></td>
                    <td>EMP006</td>
                    <td>Thomas Anderson</td>
                    <td>IT</td>
                    <td>Developer</td>
                    <td>05/10/2019</td>
                    <td><span class="status-badge terminated">Terminated</span></td>
                    <td>
                      <button class="icon-button"><span class="material-icons">visibility</span></button>
                      <button class="icon-button"><span class="material-icons">more_vert</span></button>
                    </td>
                  </tr>
                  <tr class="employee-row" data-employee-id="INC001">
                    <td><input type="checkbox" class="employee-select"></td>
                    <td>INC001</td>
                    <td>Kevin Williams</td>
                    <td>Marketing</td>
                    <td>Marketing Specialist</td>
                    <td>11/01/2023</td>
                    <td><span class="status-badge incomplete">Incomplete</span></td>
                    <td>
                      <button class="icon-button"><span class="material-icons">edit</span></button>
                      <button class="icon-button"><span class="material-icons">more_vert</span></button>
                    </td>
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

  <!-- JavaScript for tab functionality -->
  <script>
    document.addEventListener('DOMContentLoaded', function() {
      // Main tabs functionality
      const tabButtons = document.querySelectorAll('.tab-button');
      const tabPanes = document.querySelectorAll('.tab-pane');
      
      tabButtons.forEach(button => {
        button.addEventListener('click', function() {
          // Remove active class from all buttons and panes
          tabButtons.forEach(btn => btn.classList.remove('active'));
          tabPanes.forEach(pane => pane.classList.remove('active'));
          
          // Add active class to clicked button
          this.classList.add('active');
          
          // Show corresponding tab pane
          const tabId = this.getAttribute('data-tab');
          document.getElementById(`${tabId}-tab`).classList.add('active');
        });
      });
      
      // Sub-tabs functionality
      const subTabButtons = document.querySelectorAll('.sub-tab-button');
      const subTabPanes = document.querySelectorAll('.sub-tab-pane');
      
      subTabButtons.forEach(button => {
        button.addEventListener('click', function() {
          // Remove active class from all sub-tab buttons and panes
          subTabButtons.forEach(btn => btn.classList.remove('active'));
          subTabPanes.forEach(pane => pane.classList.remove('active'));
          
          // Add active class to clicked button
          this.classList.add('active');
          
          // Show corresponding sub-tab pane
          const subTabId = this.getAttribute('data-subtab');
          document.getElementById(`${subTabId}-tab`).classList.add('active');
        });
      });
      
      // Double-click functionality for employee rows
      const employeeRows = document.querySelectorAll('.employee-row');
      
      employeeRows.forEach(row => {
        row.addEventListener('dblclick', function() {
          const employeeId = this.getAttribute('data-employee-id');
          alert(`Employee details modal for ${employeeId} will be implemented later.`);
        });
      });
    });
  </script>
  
  <!-- Additional CSS for this specific page -->
  <style>
    /* Employee Stats Styling */
    .employee-stats {
      display: flex;
      flex-wrap: wrap;
      gap: 20px;
      margin-bottom: 15px;
    }
    
    .stat-item {
      flex: 1;
      min-width: 150px;
      background-color: var(--color-background-light);
      padding: 15px;
      border-radius: var(--radius-small);
      box-shadow: 0 2px 4px rgba(0,0,0,0.05);
    }
    
    .stat-label {
      display: block;
      font-size: 14px;
      color: var(--color-text-secondary);
      margin-bottom: 5px;
    }
    
    .stat-value {
      display: block;
      font-size: 24px;
      font-weight: bold;
      color: var(--color-text-primary);
    }
    
    .progress-bar {
      height: 6px;
      background-color: var(--color-background);
      border-radius: 3px;
      margin-top: 8px;
      overflow: hidden;
    }
    
    .progress {
      height: 100%;
      background-color: var(--color-primary);
      border-radius: 3px;
    }
    
    /* Help Widget Styling */
    .help-widget {
      position: relative;
      display: inline-block;
      margin-top: 10px;
    }
    
    .help-icon {
      color: var(--color-primary);
      cursor: pointer;
      font-size: 24px;
    }
    
    .help-tooltip {
      position: absolute;
      bottom: 100%;
      left: 0;
      width: 250px;
      background-color: var(--color-background-light);
      border: 1px solid var(--color-border);
      border-radius: var(--radius-small);
      padding: 15px;
      box-shadow: 0 4px 8px rgba(0,0,0,0.1);
      opacity: 0;
      visibility: hidden;
      transition: opacity 0.3s, visibility 0.3s;
      z-index: 100;
    }
    
    .help-widget:hover .help-tooltip {
      opacity: 1;
      visibility: visible;
    }
    
    .help-tooltip h4 {
      margin-top: 0;
      margin-bottom: 10px;
      color: var(--color-primary);
    }
    
    .help-tooltip ul {
      margin: 0;
      padding-left: 20px;
    }
    
    .help-tooltip li {
      margin-bottom: 5px;
      font-size: 14px;
    }
    
    /* Action Buttons Styling */
    .action-buttons {
      display: flex;
      gap: 10px;
      margin-bottom: 20px;
      flex-wrap: wrap;
      align-items: center;
    }
    
    .action-button {
      display: flex;
      align-items: center;
      gap: 5px;
      padding: 8px 15px;
      background-color: var(--color-primary);
      color: white;
      border: none;
      border-radius: var(--radius-small);
      cursor: pointer;
      font-size: 14px;
      transition: background-color 0.3s;
    }
    
    .action-button:hover {
      background-color: var(--color-hover);
    }
    
    .search-container {
      margin-left: auto;
      position: relative;
    }
    
    #employee-search {
      padding: 8px 15px 8px 35px;
      border: 1px solid var(--color-border);
      border-radius: var(--radius-small);
      width: 250px;
      font-size: 14px;
    }
    
    .search-icon {
      position: absolute;
      left: 10px;
      top: 50%;
      transform: translateY(-50%);
      color: var(--color-text-secondary);
    }
    
    /* Tabs Styling */
    .tabs-container {
      border: 1px solid var(--color-border);
      border-radius: var(--radius-small);
      overflow: hidden;
      background-color: var(--color-background-light);
    }
    
    .tabs-header, .sub-tabs-header {
      display: flex;
      background-color: var(--color-background);
      border-bottom: 1px solid var(--color-border);
    }
    
    .tab-button, .sub-tab-button {
      padding: 12px 20px;
      background: none;
      border: none;
      cursor: pointer;
      font-size: 14px;
      font-weight: 500;
      color: var(--color-text-secondary);
      transition: all 0.3s;
    }
    
    .tab-button.active, .sub-tab-button.active {
      color: var(--color-primary);
      border-bottom: 2px solid var(--color-primary);
      background-color: var(--color-background-light);
    }
    
    .tab-button:hover, .sub-tab-button:hover {
      background-color: rgba(0,0,0,0.05);
    }
    
    .tab-content {
      padding: 0;
    }
    
    .tab-pane, .sub-tab-pane {
      display: none;
    }
    
    .tab-pane.active, .sub-tab-pane.active {
      display: block;
    }
    
    .sub-tabs-header {
      background-color: var(--color-background-light);
      border-bottom: 1px solid var(--color-border);
    }
    
    /* Table Styling */
    .table-container {
      overflow-x: auto;
    }
    
    .employee-table {
      width: 100%;
      border-collapse: collapse;
    }
    
    .employee-table th {
      background-color: var(--color-background);
      padding: 12px 15px;
      text-align: left;
      font-weight: 500;
      color: var(--color-text-secondary);
      border-bottom: 1px solid var(--color-border);
    }
    
    .employee-table td {
      padding: 12px 15px;
      border-bottom: 1px solid var(--color-border);
      color: var(--color-text-primary);
    }
    
    .employee-table tr:hover {
      background-color: rgba(0,0,0,0.05);
    }
    
    .status-badge {
      display: inline-block;
      padding: 5px 10px;
      border-radius: var(--radius-small);
      font-size: 12px;
      font-weight: 500;
      text-transform: uppercase;
    }
    
    .status-badge.active {
      background-color: var(--color-success-light);
      color: var(--color-success);
    }
    
    .status-badge.temp {
      background-color: var(--color-warning-light);
      color: var(--color-warning);
    }
    
    .status-badge.terminated {
      background-color: var(--color-danger-light);
      color: var(--color-danger);
    }
    
    .status-badge.incomplete {
      background-color: var(--color-info-light);
      color: var(--color-info);
    }
    
    .icon-button {
      background: none;
      border: none;
      cursor: pointer;
      color: var(--color-primary);
      padding: 5px;
      transition: color 0.3s;
    }
    
    .icon-button:hover {
      color: var(--color-hover);
    }
    
    .pagination {
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 15px 0;
    }
    
    .pagination-button {
      background: none;
      border: none;
      cursor: pointer;
      color: var(--color-primary);
      padding: 5px;
      transition: color 0.3s;
    }
    
    .pagination-button:hover {
      color: var(--color-hover);
    }
    
    .pagination-info {
      margin: 0 10px;
      color: var(--color-text-secondary);
    }