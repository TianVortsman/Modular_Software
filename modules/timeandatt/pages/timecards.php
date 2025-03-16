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

include('../../../php/db.php');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employee Timecards</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link rel="stylesheet" href="../../../css/root.css">
    <link rel="stylesheet" href="../../../css/sidebar.css">
    <link rel="stylesheet" href="../css/timecards.css">
    <script src="../../../js/toggle-theme.js"></script>
    <script src="../../../js/sidebar.js"></script>
</head>
<body id="timecards" class="theme-enabled">
    <?php include('../../../main/sidebar.php') ?>

    <div class="timecard-container">
        <div class="main-content">
            <!-- Timecard Table -->
            <div class="timecard-table">
                <div class="timecard-header">
                    <h3 class="timecard-title">
                        <span class="material-icons">schedule</span>
                        Employee Timecards
                    </h3>
                    <div class="header-actions">
                        <button class="btn">
                            <span class="material-icons">filter_list</span>
                            Filter
                        </button>
                        <button class="btn">
                            <span class="material-icons">download</span>
                            Export
                        </button>
                    </div>
                </div>
                <div class="table-container">
                    <table class="table">
                        <thead>
                            <tr>
                                <th><span class="material-icons">badge</span> Employee Number</th>
                                <th><span class="material-icons">person</span> Employee Name</th>
                                <th><span class="material-icons">schedule</span> OT 1.5</th>
                                <th><span class="material-icons">schedule</span> OT 2.0</th>
                                <th><span class="material-icons">event_available</span> Total Hours</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr class="employee-row" onclick="openTimecardModal(1)">
                                <td>EMP001</td>
                                <td>John Doe</td>
                                <td>2.5</td>
                                <td>1.0</td>
                                <td>43.5</td>
                            </tr>
                            <tr class="employee-row" onclick="openTimecardModal(2)">
                                <td>EMP002</td>
                                <td>Jane Smith</td>
                                <td>1.5</td>
                                <td>0.0</td>
                                <td>41.5</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Exceptions Widget -->
            <div class="exceptions-widget">
                <div class="exceptions-header">
                    <h4 class="exceptions-title">
                        <span class="material-icons">warning</span>
                        Clocking Exceptions
                    </h4>
                    <div class="toggle-container">
                        <label class="toggle-switch">
                            <input type="checkbox" id="exceptionToggle">
                            <span class="slider"></span>
                        </label>
                        <span>Show All</span>
                    </div>
                </div>
                <div class="exceptions-list">
                    <div class="alert alert-warning">
                        <span class="material-icons">error_outline</span>
                        <strong>Missed Punch:</strong> John Doe - 2024-02-20 PM
                    </div>
                    <div class="alert alert-danger">
                        <span class="material-icons">cancel</span>
                        <strong>Absenteeism:</strong> Jane Smith - 2024-02-19
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Timecard Detail Modal -->
    <div class="modal-overlay" id="timecardModalOverlay"></div>
    <div class="timecard-modal" id="timecardModal">
        <div class="modal-header">
            <div class="modal-nav">
                <button class="btn">
                    <span class="material-icons">arrow_back</span>
                    Previous
                </button>
                <button class="btn">
                    Next
                    <span class="material-icons">arrow_forward</span>
                </button>
            </div>
            <h3 class="modal-title">
                <span class="material-icons">person</span>
                Employee Name
            </h3>
            <div class="modal-actions">
                <button class="btn" onclick="openLeaveModal()">
                    <span class="material-icons">event_available</span>
                    Leave
                </button>
                <button class="btn" onclick="openMassClockingsModal()">
                    <span class="material-icons">schedule</span>
                    Mass Clockings
                </button>
                <button class="btn" onclick="openShiftChangesModal()">
                    <span class="material-icons">sync_alt</span>
                    Shifts
                </button>
            </div>
        </div>
        
        <div class="modal-content">
            <div class="employee-details">
                <div class="details-grid">
                    <div class="detail-item">
                        <span class="material-icons">business</span>
                        Division: HR
                    </div>
                    <div class="detail-item">
                        <span class="material-icons">account_tree</span>
                        Department: Administration
                    </div>
                    <div class="detail-item">
                        <span class="material-icons">group</span>
                        Group: Office Staff
                    </div>
                    <div class="detail-item">
                        <span class="material-icons">attach_money</span>
                        Cost Centre: CC001
                    </div>
                </div>
            </div>

            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th><span class="material-icons">event</span> Date & Day</th>
                            <th><span class="material-icons">schedule</span> Start Time</th>
                            <th><span class="material-icons">schedule</span> Stop Time</th>
                            <th><span class="material-icons">work</span> Shift Code</th>
                            <th><span class="material-icons">description</span> Daily Description</th>
                            <th><span class="material-icons">schedule</span> Paid Hours</th>
                            <th><span class="material-icons">restaurant</span> Lunch Breaks</th>
                            <th><span class="material-icons">local_cafe</span> Tea Breaks</th>
                            <th><span class="material-icons">pause</span> Add. Breaks</th>
                            <th><span class="material-icons">track_changes</span> Shift Target</th>
                            <th><span class="material-icons">calculate</span> Running Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td>2024-02-20 Mon</td>
                            <td onclick="openTimeEditModal(event)">08:00</td>
                            <td onclick="openTimeEditModal(event)">17:00</td>
                            <td>DAY</td>
                            <td>Regular Day</td>
                            <td>8.0</td>
                            <td>1.0</td>
                            <td>0.5</td>
                            <td>0.0</td>
                            <td>8.0</td>
                            <td>8.0</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="modal-footer">
            <button class="btn" onclick="closeTimecardModal()">
                <span class="material-icons">close</span>
                Close
            </button>
            <button class="btn btn-primary">
                <span class="material-icons">auto_fix_high</span>
                Auto Resolve
            </button>
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