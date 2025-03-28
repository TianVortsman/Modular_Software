<?php
session_start();

// Check if account number is in the query parameters
if (isset($_GET['account_number'])) {
    $account_number = $_GET['account_number'];
    $_SESSION['account_number'] = $account_number;
    header("Location: dashboard.php");
    exit;
}

if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
} else {
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
    <title>Employee Timecards | Time & Attendance</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../../../public/assets/css/root.css">
    <link rel="stylesheet" href="../../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="../css/timecards.css">
    <script src="../../../public/assets/js/toggle-theme.js"></script>
    <script src="../../../public/assets/js/sidebar.js"></script>
</head>
<body id="timecards" class="theme-enabled">
    <?php include('../../../src/ui/sidebar.php') ?>

    <main class="timecard-container">
        <div class="page-header">
            <h1 class="page-title">
                <span class="material-icons">schedule</span>
                Employee Timecards
            </h1>
            <div class="page-actions">
                <div class="search-container">
                    <input type="text" class="search-input" placeholder="Search employees...">
                    <span class="material-icons search-icon">search</span>
                </div>
                <div class="date-range-container">
                    <input type="date" class="date-input" id="start-date">
                    <span class="date-separator">to</span>
                    <input type="date" class="date-input" id="end-date">
                </div>
                <button class="btn btn-secondary">
                    <span class="material-icons">filter_list</span>
                    Filter
                </button>
                <button class="btn btn-primary">
                    <span class="material-icons">download</span>
                    Export
                </button>
            </div>
        </div>

        <div class="main-content">
            <!-- Timecard Table -->
            <section class="timecard-section">
                <div class="card timecard-table">
                    <div class="card-header">
                        <h2 class="card-title">
                            <span class="material-icons">people</span>
                            Employee Timecards
                        </h2>
                        <div class="card-actions">
                            <div class="status-filter">
                                <select class="form-select">
                                    <option value="all">All Employees</option>
                                    <option value="pending">Pending Approval</option>
                                    <option value="approved">Approved</option>
                                    <option value="exception">With Exceptions</option>
                                </select>
                            </div>
                        </div>
                    </div>
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><span class="material-icons">badge</span> Employee ID</th>
                                    <th><span class="material-icons">person</span> Employee Name</th>
                                    <th><span class="material-icons">schedule</span> Regular Hours</th>
                                    <th><span class="material-icons">timer</span> OT (1.5x)</th>
                                    <th><span class="material-icons">timer</span> OT (2.0x)</th>
                                    <th><span class="material-icons">event_available</span> Total Hours</th>
                                    <th><span class="material-icons">warning</span> Exceptions</th>
                                    <th><span class="material-icons">info</span> Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="employee-row" ondblclick="openTimecardModal(1)">
                                    <td>EMP001</td>
                                    <td>John Doe</td>
                                    <td>40.0</td>
                                    <td>2.5</td>
                                    <td>1.0</td>
                                    <td>43.5</td>
                                    <td><span class="badge badge-warning">1</span></td>
                                    <td><span class="status-indicator status-pending">Pending</span></td>
                                </tr>
                                <tr class="employee-row" ondblclick="openTimecardModal(2)">
                                    <td>EMP002</td>
                                    <td>Jane Smith</td>
                                    <td>40.0</td>
                                    <td>1.5</td>
                                    <td>0.0</td>
                                    <td>41.5</td>
                                    <td><span class="badge badge-danger">2</span></td>
                                    <td><span class="status-indicator status-pending">Pending</span></td>
                                </tr>
                                <tr class="employee-row" ondblclick="openTimecardModal(3)">
                                    <td>EMP003</td>
                                    <td>Michael Johnson</td>
                                    <td>40.0</td>
                                    <td>0.0</td>
                                    <td>0.0</td>
                                    <td>40.0</td>
                                    <td><span class="badge badge-success">0</span></td>
                                    <td><span class="status-indicator status-approved">Approved</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <div class="pagination">
                            <button class="pagination-btn"><span class="material-icons">first_page</span></button>
                            <button class="pagination-btn"><span class="material-icons">chevron_left</span></button>
                            <span class="pagination-info">Page 1 of 5</span>
                            <button class="pagination-btn"><span class="material-icons">chevron_right</span></button>
                            <button class="pagination-btn"><span class="material-icons">last_page</span></button>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Sidebar with Exceptions and Summary -->
            <aside class="timecard-sidebar">
                <!-- Exceptions Widget -->
                <div class="card exceptions-widget">
                    <div class="card-header">
                        <h2 class="card-title">
                            <span class="material-icons">warning</span>
                            Exceptions
                        </h2>
                        <div class="card-actions">
                            <button class="btn btn-small">
                                <span class="material-icons">refresh</span>
                            </button>
                        </div>
                    </div>
                    <div class="exceptions-list">
                        <div class="exception-item exception-warning" ondblclick="openTimecardModal(1)">
                            <div class="exception-icon">
                                <span class="material-icons">error_outline</span>
                            </div>
                            <div class="exception-details">
                                <div class="exception-title">Missed Punch</div>
                                <div class="exception-info">John Doe - 2024-02-20 PM</div>
                            </div>
                        </div>
                        <div class="exception-item exception-danger" ondblclick="openTimecardModal(2)">
                            <div class="exception-icon">
                                <span class="material-icons">cancel</span>
                            </div>
                            <div class="exception-details">
                                <div class="exception-title">Absenteeism</div>
                                <div class="exception-info">Jane Smith - 2024-02-19</div>
                            </div>
                        </div>
                        <div class="exception-item exception-warning" ondblclick="openTimecardModal(2)">
                            <div class="exception-icon">
                                <span class="material-icons">schedule</span>
                            </div>
                            <div class="exception-details">
                                <div class="exception-title">Early Departure</div>
                                <div class="exception-info">Jane Smith - 2024-02-21</div>
                            </div>
                        </div>
                    </div>
                    <div class="card-footer">
                        <button class="btn btn-text">View All Exceptions</button>
                    </div>
                </div>

                <!-- Summary Widget -->
                <div class="card summary-widget">
                    <div class="card-header">
                        <h2 class="card-title">
                            <span class="material-icons">summarize</span>
                            Summary
                        </h2>
                    </div>
                    <div class="summary-content">
                        <div class="summary-item">
                            <div class="summary-label">Total Employees</div>
                            <div class="summary-value">18</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Pending Approval</div>
                            <div class="summary-value highlight-warning">12</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">With Exceptions</div>
                            <div class="summary-value highlight-danger">5</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Total Hours</div>
                            <div class="summary-value">720.5</div>
                        </div>
                        <div class="summary-item">
                            <div class="summary-label">Overtime Hours</div>
                            <div class="summary-value highlight-info">24.5</div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </main>

    <!-- Timecard Detail Modal -->
    <div class="modal-overlay" id="timecardModalOverlay"></div>
    <div class="modal timecard-modal" id="timecardModal">
        <div class="modal-header">
            <div class="modal-title-group">
                <h3 class="modal-title">
                    <span class="material-icons">person</span>
                    <span id="modal-employee-name">John Doe</span>
                </h3>
                <div class="employee-id">EMP001</div>
            </div>
            <div class="modal-actions">
                <button class="btn btn-icon" title="Previous Employee" onclick="navigateEmployee('prev')">
                    <span class="material-icons">arrow_back</span>
                </button>
                <button class="btn btn-icon" title="Next Employee" onclick="navigateEmployee('next')">
                    <span class="material-icons">arrow_forward</span>
                </button>
                <button class="btn btn-secondary" onclick="openLeaveModal()">
                    <span class="material-icons">event_available</span>
                    Leave
                </button>
                <button class="btn btn-secondary" onclick="openMassClockingsModal()">
                    <span class="material-icons">schedule</span>
                    Mass Clockings
                </button>
                <button class="btn btn-secondary" onclick="openShiftChangesModal()">
                    <span class="material-icons">sync_alt</span>
                    Shifts
                </button>
                <button class="btn btn-icon" title="Close" onclick="closeTimecardModal()">
                    <span class="material-icons">close</span>
                </button>
            </div>
        </div>
        
        <div class="modal-content">
            <div class="employee-details-card">
                <div class="employee-info">
                    <div class="employee-photo">
                        <span class="material-icons photo-placeholder">account_circle</span>
                    </div>
                    <div class="employee-data">
                        <div class="detail-item">
                            <span class="material-icons">business</span>
                            <span class="detail-label">Division:</span>
                            <span class="detail-value">HR</span>
                        </div>
                        <div class="detail-item">
                            <span class="material-icons">account_tree</span>
                            <span class="detail-label">Department:</span>
                            <span class="detail-value">Administration</span>
                        </div>
                        <div class="detail-item">
                            <span class="material-icons">group</span>
                            <span class="detail-label">Group:</span>
                            <span class="detail-value">Office Staff</span>
                        </div>
                        <div class="detail-item">
                            <span class="material-icons">attach_money</span>
                            <span class="detail-label">Cost Centre:</span>
                            <span class="detail-value">CC001</span>
                        </div>
                    </div>
                </div>
                <div class="timecard-summary">
                    <div class="summary-tile">
                        <div class="tile-label">Regular Hours</div>
                        <div class="tile-value">40.0</div>
                    </div>
                    <div class="summary-tile">
                        <div class="tile-label">OT (1.5x)</div>
                        <div class="tile-value">2.5</div>
                    </div>
                    <div class="summary-tile">
                        <div class="tile-label">OT (2.0x)</div>
                        <div class="tile-value">1.0</div>
                    </div>
                    <div class="summary-tile">
                        <div class="tile-label">Total Hours</div>
                        <div class="tile-value">43.5</div>
                    </div>
                </div>
            </div>

            <div class="timecard-tabs">
                <div class="tab-header">
                    <button class="tab-btn active" data-tab="daily">Daily View</button>
                    <button class="tab-btn" data-tab="weekly">Weekly View</button>
                    <button class="tab-btn" data-tab="exceptions">Exceptions</button>
                </div>
                
                <div class="tab-content active" id="daily-tab">
                    <div class="table-container">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th><span class="material-icons">event</span> Date</th>
                                    <th><span class="material-icons">schedule</span> Start Time</th>
                                    <th><span class="material-icons">schedule</span> End Time</th>
                                    <th><span class="material-icons">work</span> Shift</th>
                                    <th><span class="material-icons">description</span> Description</th>
                                    <th><span class="material-icons">schedule</span> Regular</th>
                                    <th><span class="material-icons">schedule</span> OT (1.5x)</th>
                                    <th><span class="material-icons">schedule</span> OT (2.0x)</th>
                                    <th><span class="material-icons">restaurant</span> Breaks</th>
                                    <th><span class="material-icons">track_changes</span> Target</th>
                                    <th><span class="material-icons">calculate</span> Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>Mon, Feb 20, 2024</td>
                                    <td ondblclick="openTimeEditModal(event, 'start', '08:00')">08:00</td>
                                    <td ondblclick="openTimeEditModal(event, 'end', '17:00')">17:00</td>
                                    <td>DAY</td>
                                    <td>Regular Day</td>
                                    <td>8.0</td>
                                    <td>0.0</td>
                                    <td>0.0</td>
                                    <td ondblclick="openBreaksModal(event)">1.5</td>
                                    <td>8.0</td>
                                    <td>8.0</td>
                                </tr>
                                <tr>
                                    <td>Tue, Feb 21, 2024</td>
                                    <td ondblclick="openTimeEditModal(event, 'start', '08:00')">08:00</td>
                                    <td ondblclick="openTimeEditModal(event, 'end', '18:30')">18:30</td>
                                    <td>DAY</td>
                                    <td>Regular Day</td>
                                    <td>8.0</td>
                                    <td>1.5</td>
                                    <td>0.0</td>
                                    <td ondblclick="openBreaksModal(event)">1.5</td>
                                    <td>8.0</td>
                                    <td>9.5</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="tab-content" id="weekly-tab">
                    <!-- Weekly view content -->
                    <div class="weekly-view-placeholder">Weekly view will be displayed here</div>
                </div>
                
                <div class="tab-content" id="exceptions-tab">
                    <!-- Exceptions content -->
                    <div class="exception-list">
                        <div class="exception-card">
                            <div class="exception-header warning">
                                <span class="material-icons">error_outline</span>
                                <span>Missed Punch</span>
                                <span class="exception-date">Feb 20, 2024</span>
                            </div>
                            <div class="exception-body">
                                <p>Clock out missing for afternoon shift</p>
                                <div class="exception-actions">
                                    <button class="btn btn-small" ondblclick="openTimeEditModal(event, 'end', '')">
                                        <span class="material-icons">edit</span>
                                        Fix
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal-footer">
            <div class="footer-info">
                <span class="status-label">Status:</span>
                <span class="status-value status-pending">Pending Approval</span>
            </div>
            <div class="footer-actions">
                <button class="btn" onclick="closeTimecardModal()">
                    <span class="material-icons">close</span>
                    Cancel
                </button>
                <button class="btn btn-warning">
                    <span class="material-icons">auto_fix_high</span>
                    Auto Resolve
                </button>
                <button class="btn btn-primary">
                    <span class="material-icons">check_circle</span>
                    Approve
                </button>
            </div>
        </div>
    </div>

    <!-- Include Modal Files -->
    <?php
    include '../modals/time-edit-modal.php';
    include '../modals/punches-modal.php';
    include '../modals/leave-request-modal.php';
    include '../modals/mass-clockings-modal.php';
    include '../modals/shift-changes-modal.php';
    ?>

    <!-- Scripts -->
    <script src="../js/timecards.js"></script>
</body>
</html> 