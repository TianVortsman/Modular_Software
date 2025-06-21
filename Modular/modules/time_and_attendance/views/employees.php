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
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Employee Management - Time and Attendance</title>
  <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
  <link rel="stylesheet" href="../../../public/assets/css/root.css">
  <link rel="stylesheet" href="../../../public/assets/css/sidebar.css">
  <link rel="stylesheet" href="../css/modals.css">
  <link rel="stylesheet" href="../css/employees.css">
  <link rel="stylesheet" href="../css/employee-modal.css">
  <link rel="stylesheet" href="../../../public/assets/css/tutorial.css">
</head>
<body id="TA-employees">
  <!-- Sidebar -->
  <?php include('../../../src/ui/sidebar.php') ?>
  <div class="dashboard-container" id="dashboard-container">

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
      
      <!-- Main Tabs -->
      <div class="page-tabs-container">
        <div class="page-tabs-header-wrapper">
          <div class="page-tabs-header">
            <button class="page-tab-button active" data-tab="active" role="tab" aria-selected="true" aria-controls="active-tab">
              <i class="material-icons">check_circle</i> Active
            </button>
            <button class="page-tab-button" data-tab="terminated" role="tab" aria-selected="false" aria-controls="terminated-tab">
              <i class="material-icons">cancel</i> Terminated
            </button>
            <button class="page-tab-button" data-tab="incomplete" role="tab" aria-selected="false" aria-controls="incomplete-tab">
              <i class="material-icons">error_outline</i> Incomplete
            </button>
            <button class="page-tab-button" data-tab="all" role="tab" aria-selected="false" aria-controls="all-tab">
              <i class="material-icons">group</i> All Employees
            </button>
          </div>
          <div class="show-entries-container">
              Show
              <select id="perPageSelect">
                  <option value="20">20</option>
                  <option value="40">40</option>
                  <option value="60">60</option>
                  <option value="80">80</option>
                  <option value="100">100</option>
              </select>
              entries
          </div>
        </div>
        
        <!-- Tab Content -->
        <div class="page-tab-content">
          <!-- Active Tab -->
          <div class="page-tab-pane active" id="active-tab" role="tabpanel">
            <!-- Sub-tabs for Active Employees -->
            <div class="page-subtabs-header">
              <button class="page-subtab-button active" data-subtab="permanent" role="tab" aria-selected="true" aria-controls="permanent-tab">
                <i class="material-icons">business</i> Permanent
              </button>
              <button class="page-subtab-button" data-subtab="temporary" role="tab" aria-selected="false" aria-controls="temporary-tab">
                <i class="material-icons">timer</i> Temporary
              </button>
            </div>
            
            <!-- Permanent Employees Sub-tab -->
            <div class="page-subtab-pane active" id="permanent-tab" role="tabpanel">
              <div class="table-container">
                <table class="main-employee-table">
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
            <div class="page-subtab-pane" id="temporary-tab" role="tabpanel">
              <div class="table-container">
                <table class="main-employee-table">
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
          <div class="page-tab-pane" id="terminated-tab" role="tabpanel">
            <div class="table-container">
              <table class="main-employee-table">
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
          <div class="page-tab-pane" id="incomplete-tab" role="tabpanel">
            <div class="table-container">
              <table class="main-employee-table">
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
          <div class="page-tab-pane" id="all-tab" role="tabpanel">
            <div class="table-container">
              <table class="main-employee-table">
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
<?php 
    include '../../../src/ui/loading-modal.php';
    include '../../../src/ui/response-modal.php';
    include '../../../src/ui/error-table-modal.php';
    include '../modals/add-employee-modal.php';
    include '../modals/employee-modal.php';
?>
  <!-- Load scripts in correct order -->
  <script src="../../../public/assets/js/sidebar.js"></script>
  <script src="../../../public/assets/js/toggle-theme.js"></script>
  <script src="../js/employees.js"></script>
  <!-- Load tutorial scripts last -->
  <script src="../../../public/assets/js/tutorial/tutorial-data.js"></script>
  <script src="../../../public/assets/js/tutorial/tutorial-engine.js"></script>
  <!-- After all scripts are loaded -->
  <script>
  // Debug check for modal functions
  document.addEventListener('DOMContentLoaded', () => {
      console.log('Modal functions available:', {
          openAddEmployeeModal: typeof openAddEmployeeModal === 'function',
          openEmployeeModal: typeof openEmployeeModal === 'function'
      });
  });
  </script>
  </body>
  </html>