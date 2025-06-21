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
                <div class="period-selector-container">
                    <label for="pay-period-type">Pay Period:</label>
                    <select class="form-select" id="pay-period-type">
                        <option value="weekly">Weekly</option>
                        <option value="biweekly">Bi-Weekly</option>
                        <option value="monthly">Monthly</option>
                    </select>
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
                                <!-- Table will be populated by JavaScript -->
                                <tr>
                                    <td colspan="8" class="loading-message">Loading employee data...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="card-footer">
                        <div class="pagination">
                            <button class="pagination-btn"><span class="material-icons">first_page</span></button>
                            <button class="pagination-btn"><span class="material-icons">chevron_left</span></button>
                            <span class="pagination-info">Page 1 of 1</span>
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
                        <!-- Exceptions will be populated by JavaScript -->
                        <div class="loading-message">Loading exceptions...</div>
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
                        <!-- Summary will be populated by JavaScript -->
                        <div class="loading-message">Loading summary data...</div>
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
                    <span id="modal-employee-name">Loading...</span>
                </h3>
                <div class="employee-id">Loading...</div>
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
            <!-- Modal content will be populated by JavaScript -->
            <div class="loading-spinner">Loading employee timecard data...</div>
        </div>

        <div class="modal-footer">
            <div class="footer-info">
                <span class="status-label">Status:</span>
                <span class="status-value status-pending">Loading...</span>
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