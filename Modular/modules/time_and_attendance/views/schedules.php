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
    <meta name="csrf-token" content="<?php echo $csrf_token; ?>">
    <link rel="stylesheet" href="../../../css/root.css">
    <link rel="stylesheet" href="../../../css/sidebar.css">
    <link rel="stylesheet" href="../css/schedules.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="../../../js/sidebar.js"></script>
    <script src="../../../js/toggle-theme.js"></script>
    <title>Schedule Management - <?php echo htmlspecialchars($account['name']); ?></title>
</head>
<body id="schedules">
    <?php include_once '../../../main/sidebar.php'; ?>
    
    <div class="schedule-container" id="schedule-container">
        <!-- Header Section -->
        <div class="schedule-header">
            <div class="header-left">
                <h1>Schedule Management</h1>
                <div class="schedule-tabs">
                    <button class="tab-btn active" data-tab="workweeks">
                        <i class="material-icons">calendar_today</i>
                        Work Weeks
                    </button>
                    <button class="tab-btn" data-tab="rosters">
                        <i class="material-icons">calendar_month</i>
                        Monthly Rosters
                    </button>
                </div>
            </div>
            <div class="header-right">
                <button class="action-btn" id="setupBtn">
                    <i class="material-icons">settings</i>
                    Setup
                </button>
                <button class="action-btn" id="addShiftBtn">
                    <i class="material-icons">schedule</i>
                    Add Shift
                </button>
            </div>
        </div>

        <!-- Main Content Area -->
        <div class="schedule-content">
            <!-- Work Weeks Tab -->
            <div class="schedule-tab active" id="workweeks-tab">
                <div class="tab-header">
                    <div class="template-controls">
                        <select id="templateSelect" class="template-dropdown">
                            <option value="">Select Template</option>
                        </select>
                        <button class="action-btn" id="addTemplateBtn">
                            <i class="material-icons">add</i>
                            New Template
                        </button>
                        <button class="action-btn" id="saveTemplateBtn">
                            <i class="material-icons">save</i>
                            Save Template
                        </button>
                    </div>
                    <button class="action-btn" id="assignTemplateBtn">
                        <i class="material-icons">person_add</i>
                        Assign to Employees
                    </button>
                </div>

                <div class="tab-content">
                    <!-- Shift Library -->
                    <div class="shift-library">
                        <div class="panel-header">
                            <h3>Shift Library</h3>
                        </div>
                        <div class="shift-list" id="shiftList">
                            <!-- Shifts will be loaded here -->
                        </div>
                    </div>

                    <!-- Template Editor -->
                    <div class="template-editor">
                        <div class="editor-header">
                            <input type="text" id="templateName" placeholder="Template Name" class="template-name-input">
                            <div class="editor-actions">
                                <button class="action-btn" id="deleteTemplateBtn">
                                    <i class="material-icons">delete</i>
                                    Delete Template
                                </button>
                            </div>
                        </div>

                        <div class="work-week">
                            <?php
                            $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
                            foreach ($days as $day) {
                                echo "<div class='day-column' data-day='$day'>
                                    <div class='day-header'>
                                        <h4>" . ucfirst($day) . "</h4>
                                    </div>
                                    <div class='shift-drop-zone'></div>
                                </div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Rosters Tab -->
            <div class="schedule-tab" id="rosters-tab">
                <div class="tab-header">
                    <div class="roster-controls">
                        <div class="month-navigation">
                            <button class="nav-btn" id="prevMonth">
                                <i class="material-icons">chevron_left</i>
                            </button>
                            <h2 id="currentMonth">September 2023</h2>
                            <button class="nav-btn" id="nextMonth">
                                <i class="material-icons">chevron_right</i>
                            </button>
                        </div>
                        <div class="roster-actions">
                            <button class="action-btn" id="saveRosterBtn">
                                <i class="material-icons">save</i>
                                Save Roster
                            </button>
                            <button class="action-btn" id="exportRosterBtn">
                                <i class="material-icons">download</i>
                                Export
                            </button>
                            <button class="action-btn" id="printRosterBtn">
                                <i class="material-icons">print</i>
                                Print
                            </button>
                        </div>
                    </div>
                </div>

                <div class="tab-content">
                    <!-- Shift Library -->
                    <div class="shift-library">
                        <div class="panel-header">
                            <h3>Shift Library</h3>
                        </div>
                        <div class="shift-list" id="shiftList">
                            <!-- Shifts will be loaded here -->
                        </div>
                    </div>

                    <!-- Monthly Calendar -->
                    <div class="monthly-calendar">
                        <div class="calendar-header">
                            <div>Sun</div>
                            <div>Mon</div>
                            <div>Tue</div>
                            <div>Wed</div>
                            <div>Thu</div>
                            <div>Fri</div>
                            <div>Sat</div>
                        </div>
                        <div class="calendar-body" id="calendarGrid">
                            <!-- Calendar days will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Add Shift Modal -->
    <div class="modal" id="addShiftModal">
        <div class="modal-content shift-modal">
            <div class="modal-header">
                <h3>Add New Shift</h3>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="shift-tabs">
                    <div class="shift-tab-buttons">
                        <button class="shift-tab-btn active" data-tab="shift-details">
                            <i class="material-icons">info</i>
                            Shift Details
                        </button>
                        <button class="shift-tab-btn" data-tab="shift-times">
                            <i class="material-icons">schedule</i>
                            Shift Times
                        </button>
                        <button class="shift-tab-btn" data-tab="shift-holidays" style="display: none;">
                            <i class="material-icons">event</i>
                            Holidays
                        </button>
                        <button class="shift-tab-btn" data-tab="shift-night-allowance" style="display: none;">
                            <i class="material-icons">nightlight</i>
                            Night Allowance
                        </button>
                        <button class="shift-tab-btn" data-tab="shift-split-time" style="display: none;">
                            <i class="material-icons">call_split</i>
                            Split Time
                        </button>
                    </div>

                    <!-- Shift Details Tab -->
                    <div class="shift-tab-content active" id="shift-details-tab">
                        <form id="shiftDetailsForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <div class="form-group">
                                <label for="shiftName">Shift Name</label>
                                <input type="text" id="shiftName" name="shiftName" required>
                            </div>
                            <div class="form-group">
                                <label for="shiftTarget">Shift Target Hours</label>
                                <input type="number" id="shiftTarget" name="shiftTarget" step="0.5" required>
                            </div>
                            <div class="form-group">
                                <label for="normalTimeCategory">Normal Time Category</label>
                                <select id="normalTimeCategory" name="normalTimeCategory" required>
                                    <!-- Time categories will be loaded here -->
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="overtimeCategory">Overtime Category</label>
                                <select id="overtimeCategory" name="overtimeCategory" required>
                                    <!-- Time categories will be loaded here -->
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="shiftCounter">Hours for Shift Counter</label>
                                <input type="number" id="shiftCounter" name="shiftCounter" step="0.5" required>
                            </div>
                            <div class="form-group">
                                <label for="payPeriod">Pay Period</label>
                                <select id="payPeriod" name="payPeriod" required>
                                    <option value="">Select Pay Period</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="biweekly">Bi-Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                            <div class="form-group custom-period-details" style="display: none;">
                                <label for="periodStartDate">Period Start Date</label>
                                <input type="date" id="periodStartDate" name="periodStartDate">
                                <label for="periodEndDate">Period End Date</label>
                                <input type="date" id="periodEndDate" name="periodEndDate">
                                <label for="periodDays">Number of Days</label>
                                <input type="number" id="periodDays" name="periodDays" min="1">
                            </div>
                            <div class="form-group">
                                <label for="shiftType">Shift Type</label>
                                <select id="shiftType" name="shiftType" required>
                                    <option value="regular">Regular</option>
                                    <option value="rotating">Rotating</option>
                                    <option value="split">Split Shift</option>
                                    <option value="on_call">On-Call</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="shiftPattern">Shift Pattern (for rotating shifts)</label>
                                <input type="text" id="shiftPattern" name="shiftPattern" placeholder="e.g., 4 days on, 4 days off">
                            </div>
                            <div class="form-group">
                                <label for="breakType">Break Type</label>
                                <select id="breakType" name="breakType" required>
                                    <option value="paid">Paid Break</option>
                                    <option value="unpaid">Unpaid Break</option>
                                    <option value="none">No Break</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="breakDuration">Break Duration (minutes)</label>
                                <input type="number" id="breakDuration" name="breakDuration" min="0" value="30">
                            </div>
                            <div class="form-group">
                                <label for="shiftColor">Shift Color</label>
                                <input type="color" id="shiftColor" name="shiftColor" value="#007bff">
                            </div>
                            <div class="form-group">
                                <label>Punch Handling</label>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="punchHandling" value="first_last" checked>
                                        Use First and Last Punch Only
                                    </label>
                                    <label>
                                        <input type="radio" name="punchHandling" value="ignore">
                                        Ignore First and Last Punch
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="singleClosingShift" name="singleClosingShift">
                                    Single Closing Shift
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="paidHolidays" name="paidHolidays">
                                    Paid Holidays
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="nightShiftAllowance" name="nightShiftAllowance">
                                    Night Shift Allowance
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="splitNormalTime" name="splitNormalTime">
                                    Split Normal Time
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="requiresApproval" name="requiresApproval">
                                    Requires Approval
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="allowOvertime" name="allowOvertime">
                                    Allow Overtime
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="allowEarlyClockIn" name="allowEarlyClockIn">
                                    Allow Early Clock In
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="allowLateClockOut" name="allowLateClockOut">
                                    Allow Late Clock Out
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="earlyClockInLimit">Early Clock In Limit (minutes)</label>
                                <input type="number" id="earlyClockInLimit" name="earlyClockInLimit" min="0" value="15">
                            </div>
                            <div class="form-group">
                                <label for="lateClockOutLimit">Late Clock Out Limit (minutes)</label>
                                <input type="number" id="lateClockOutLimit" name="lateClockOutLimit" min="0" value="15">
                            </div>
                            <div class="form-group">
                                <label for="shiftRules">Shift Rules</label>
                                <textarea id="shiftRules" name="shiftRules" rows="4" placeholder="Enter any specific rules or requirements for this shift"></textarea>
                            </div>
                        </form>
                    </div>

                    <!-- Shift Times Tab -->
                    <div class="shift-tab-content" id="shift-times-tab">
                        <form id="shiftTimesForm">
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="startTime">Start Time</label>
                                    <input type="time" id="startTime" name="startTime" required data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="endTime">End Time</label>
                                    <input type="time" id="endTime" name="endTime" required data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="earliestStart">Earliest Start (Optional)</label>
                                    <input type="time" id="earliestStart" name="earliestStart" data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="latestEnd">Latest End (Optional)</label>
                                    <input type="time" id="latestEnd" name="latestEnd" data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="roundingProfile">Rounding Profile</label>
                                <select id="roundingProfile" name="roundingProfile" required>
                                    <!-- Rounding profiles will be loaded here -->
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- Holidays Tab -->
                    <div class="shift-tab-content" id="shift-holidays-tab">
                        <form id="shiftHolidaysForm">
                            <div class="form-group">
                                <label>Holiday Payment Rules</label>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="holidayPayment" value="work_only" checked>
                                        Pay Only if Worked
                                    </label>
                                    <label>
                                        <input type="radio" name="holidayPayment" value="always">
                                        Always Pay
                                    </label>
                                    <label>
                                        <input type="radio" name="holidayPayment" value="both">
                                        Pay if Worked or Not Worked
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="holidayTimeCategory">Holiday Time Category</label>
                                <select id="holidayTimeCategory" name="holidayTimeCategory" required>
                                    <!-- Time categories will be loaded here -->
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- Night Allowance Tab -->
                    <div class="shift-tab-content" id="shift-night-allowance-tab">
                        <form id="shiftNightAllowanceForm">
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="nightAllowanceStart">Night Allowance Start Time</label>
                                    <input type="time" id="nightAllowanceStart" name="nightAllowanceStart" required data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="nightAllowanceEnd">Night Allowance End Time</label>
                                    <input type="time" id="nightAllowanceEnd" name="nightAllowanceEnd" required data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nightAllowanceRate">Night Allowance Rate</label>
                                <input type="number" id="nightAllowanceRate" name="nightAllowanceRate" step="0.01" required>
                            </div>
                        </form>
                    </div>

                    <!-- Split Time Tab -->
                    <div class="shift-tab-content" id="shift-split-time-tab">
                        <form id="shiftSplitTimeForm">
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="splitTimeStart">Split Time Start</label>
                                    <input type="time" id="splitTimeStart" name="splitTimeStart" required data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="splitTimeEnd">Split Time End</label>
                                    <input type="time" id="splitTimeEnd" name="splitTimeEnd" required data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="splitTimeRate">Split Time Rate</label>
                                <input type="number" id="splitTimeRate" name="splitTimeRate" step="0.01" required>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-btn">Cancel</button>
                <button type="button" class="prev-tab-btn" style="display: none;">Previous</button>
                <button type="button" class="next-tab-btn">Next</button>
                <button type="submit" class="submit-btn" style="display: none;">Save Shift</button>
            </div>
        </div>
    </div>

    <!-- Assign Template Modal -->
    <div class="modal" id="assignTemplateModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Assign Template to Employees</h3>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <form id="assignTemplateForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-group">
                        <label for="templateSelect">Select Template</label>
                        <select id="templateSelect" required>
                            <!-- Templates will be loaded here -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Select Employees</label>
                        <div class="employee-list" id="employeeList">
                            <!-- Employees will be loaded here -->
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn">Cancel</button>
                        <button type="submit" class="submit-btn">Assign Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Shift Details Modal -->
    <div class="modal" id="shiftDetailsModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Shift Details</h3>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <form id="shiftDetailsForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <input type="hidden" id="shiftId" name="shiftId">
                    <div class="form-group">
                        <label for="editShiftName">Shift Name</label>
                        <input type="text" id="editShiftName" name="shiftName" required>
                    </div>
                    <div class="form-group">
                        <label for="editStartTime">Start Time</label>
                        <input type="time" id="editStartTime" name="startTime" required>
                    </div>
                    <div class="form-group">
                        <label for="editEndTime">End Time</label>
                        <input type="time" id="editEndTime" name="endTime" required>
                    </div>
                    <div class="form-group">
                        <label for="editBreakTime">Break Time (minutes)</label>
                        <input type="number" id="editBreakTime" name="breakTime" min="0" value="30">
                    </div>
                    <div class="form-group">
                        <label for="editShiftColor">Shift Color</label>
                        <input type="color" id="editShiftColor" name="shiftColor" value="#007bff">
                    </div>
                    <div class="form-group">
                        <label for="shiftRules">Shift Rules</label>
                        <textarea id="shiftRules" name="shiftRules" rows="4" placeholder="Enter any specific rules or requirements for this shift"></textarea>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn">Cancel</button>
                        <button type="submit" class="submit-btn">Save Changes</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Mobile Shift Library Toggle -->
    <button class="shift-library-toggle" id="shiftLibraryToggle">
        <i class="material-icons">menu</i>
    </button>

    <!-- Setup Modal -->
    <div class="modal" id="setupModal">
        <div class="modal-content setup-modal">
            <div class="modal-header">
                <h3>System Setup</h3>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="setup-tabs">
                    <div class="setup-tab-buttons">
                        <button class="setup-tab-btn active" data-tab="holidays">
                            <i class="material-icons">event</i>
                            Public Holidays
                        </button>
                        <button class="setup-tab-btn" data-tab="time-categories">
                            <i class="material-icons">category</i>
                            Time Categories
                        </button>
                        <button class="setup-tab-btn" data-tab="rounding-profiles">
                            <i class="material-icons">schedule</i>
                            Rounding Profiles
                        </button>
                        <button class="setup-tab-btn" data-tab="overtime-profiles">
                            <i class="material-icons">timer</i>
                            Overtime Profiles
                        </button>
                        <button class="setup-tab-btn" data-tab="pay-periods">
                            <i class="material-icons">payments</i>
                            Pay Periods
                        </button>
                    </div>

                    <!-- Public Holidays Tab -->
                    <div class="setup-tab-content active" id="holidays-tab">
                        <form id="holidaysForm">
                            <div class="form-group">
                                <label>Add New Holiday</label>
                                <div class="holiday-input-group">
                                    <input type="text" id="holidayName" name="holidayName" placeholder="Holiday Name" required>
                                    <input type="date" id="holidayDate" name="holidayDate" required>
                                    <button type="submit" class="add-btn">
                                        <i class="material-icons">add</i>
                                    </button>
                                </div>
                            </div>
                            <div class="holiday-list">
                                <h4>Existing Holidays</h4>
                                <div class="list-container" id="holidaysList">
                                    <!-- Holidays will be loaded here -->
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Time Categories Tab -->
                    <div class="setup-tab-content" id="time-categories-tab">
                        <div class="category-tabs">
                            <button class="category-tab-btn active" data-category="normal">Normal Time</button>
                            <button class="category-tab-btn" data-category="overtime">Overtime</button>
                            <button class="category-tab-btn" data-category="special">Special Categories</button>
                        </div>

                        <!-- Normal Time Categories -->
                        <div class="category-content active" id="normal-categories">
                            <form id="normalCategoriesForm">
                                <div class="form-group">
                                    <label>Add Normal Time Category</label>
                                    <div class="category-input-group">
                                        <input type="text" id="normalCategoryName" name="name" placeholder="Category Name" required>
                                        <input type="number" id="normalCategoryRate" name="rate" step="0.01" placeholder="Rate" required>
                                        <button type="submit" class="add-btn">
                                            <i class="material-icons">add</i>
                                        </button>
                                    </div>
                                </div>
                                <div class="category-list" id="normalCategoriesList">
                                    <!-- Normal categories will be loaded here -->
                                </div>
                            </form>
                        </div>

                        <!-- Overtime Categories -->
                        <div class="category-content" id="overtime-categories">
                            <form id="overtimeCategoriesForm">
                                <div class="form-group">
                                    <label>Add Overtime Category</label>
                                    <div class="category-input-group">
                                        <input type="text" id="overtimeCategoryName" name="name" placeholder="Category Name" required>
                                        <input type="number" id="overtimeCategoryRate" name="rate" step="0.01" placeholder="Rate" required>
                                        <button type="submit" class="add-btn">
                                            <i class="material-icons">add</i>
                                        </button>
                                    </div>
                                </div>
                                <div class="category-list" id="overtimeCategoriesList">
                                    <!-- Overtime categories will be loaded here -->
                                </div>
                            </form>
                        </div>

                        <!-- Special Categories -->
                        <div class="category-content" id="special-categories">
                            <form id="specialCategoriesForm">
                                <div class="form-group">
                                    <label>Add Special Category</label>
                                    <div class="category-input-group">
                                        <input type="text" id="specialCategoryName" name="name" placeholder="Category Name" required>
                                        <input type="number" id="specialCategoryRate" name="rate" step="0.01" placeholder="Rate" required>
                                        <button type="submit" class="add-btn">
                                            <i class="material-icons">add</i>
                                        </button>
                                    </div>
                                </div>
                                <div class="category-list" id="specialCategoriesList">
                                    <!-- Special categories will be loaded here -->
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Rounding Profiles Tab -->
                    <div class="setup-tab-content" id="rounding-profiles-tab">
                        <form id="roundingProfilesForm">
                            <div class="form-group">
                                <label>Add Rounding Profile</label>
                                <div class="profile-input-group">
                                    <input type="text" id="roundingProfileName" name="name" placeholder="Profile Name" required>
                                    <div class="rounding-rules">
                                        <div class="rule-group">
                                            <label>Clock In Rounding</label>
                                            <select id="clockInRounding" name="clockInRounding" required>
                                                <option value="none">No Rounding</option>
                                                <option value="nearest_5">Nearest 5 Minutes</option>
                                                <option value="nearest_15">Nearest 15 Minutes</option>
                                                <option value="nearest_30">Nearest 30 Minutes</option>
                                            </select>
                                        </div>
                                        <div class="rule-group">
                                            <label>Clock Out Rounding</label>
                                            <select id="clockOutRounding" name="clockOutRounding" required>
                                                <option value="none">No Rounding</option>
                                                <option value="nearest_5">Nearest 5 Minutes</option>
                                                <option value="nearest_15">Nearest 15 Minutes</option>
                                                <option value="nearest_30">Nearest 30 Minutes</option>
                                            </select>
                                        </div>
                                    </div>
                                    <button type="submit" class="add-btn">
                                        <i class="material-icons">add</i>
                                    </button>
                                </div>
                            </div>
                            <div class="profile-list" id="roundingProfilesList">
                                <!-- Rounding profiles will be loaded here -->
                            </div>
                        </form>
                    </div>

                    <!-- Overtime Profiles Tab -->
                    <div class="setup-tab-content" id="overtime-profiles-tab">
                        <form id="overtimeProfilesForm">
                            <div class="form-group">
                                <label>Add Overtime Profile</label>
                                <div class="profile-input-group">
                                    <input type="text" id="overtimeProfileName" name="name" placeholder="Profile Name" required>
                                    <select id="payPeriod" name="payPeriod" required>
                                        <option value="">Select Pay Period</option>
                                        <!-- Pay periods will be loaded here -->
                                    </select>
                                    <input type="number" id="targetHours" name="targetHours" step="0.5" placeholder="Target Hours" required>
                                    <div class="filling-sequence">
                                        <label>Filling Sequence</label>
                                        <div class="sequence-options">
                                            <label>
                                                <input type="radio" name="fillingSequence" value="daily" checked>
                                                Daily
                                            </label>
                                            <label>
                                                <input type="radio" name="fillingSequence" value="weekly">
                                                Weekly
                                            </label>
                                        </div>
                                    </div>
                                    <button type="submit" class="add-btn">
                                        <i class="material-icons">add</i>
                                    </button>
                                </div>
                            </div>
                            <div class="profile-list" id="overtimeProfilesList">
                                <!-- Overtime profiles will be loaded here -->
                            </div>
                        </form>
                    </div>

                    <!-- Pay Periods Tab -->
                    <div class="setup-tab-content" id="pay-periods-tab">
                        <form id="payPeriodsForm">
                            <div class="form-group">
                                <label>Add Pay Period</label>
                                <div class="period-input-group">
                                    <input type="text" id="periodName" name="name" placeholder="Period Name" required>
                                    <select id="periodType" name="type" required>
                                        <option value="">Select Type</option>
                                        <option value="weekly">Weekly</option>
                                        <option value="biweekly">Bi-Weekly</option>
                                        <option value="monthly">Monthly</option>
                                        <option value="custom">Custom</option>
                                    </select>
                                    <div class="period-details" id="customPeriodDetails" style="display: none;">
                                        <input type="number" id="periodDays" name="days" placeholder="Number of Days" min="1">
                                        <input type="number" id="periodWeeks" name="weeks" placeholder="Number of Weeks" min="1">
                                    </div>
                                    <button type="submit" class="add-btn">
                                        <i class="material-icons">add</i>
                                    </button>
                                </div>
                            </div>
                            <div class="period-list" id="payPeriodsList">
                                <!-- Pay periods will be loaded here -->
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-btn">Close</button>
            </div>
        </div>
    </div>

    <script src="../js/schedules.js"></script>
</body>
</html>
