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
    <meta name="csrf-token" content="<?php echo $csrf_token; ?>">
    <link rel="stylesheet" href="../../../public/assets/css/root.css">
    <link rel="stylesheet" href="../../../public/assets/css/sidebar.css">
    <link rel="stylesheet" href="../css/schedules.css">
    <link rel="stylesheet" href="../css/setup-modal.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="../../../public/assets/js/sidebar.js"></script>
    <script src="../../../public/assets/js/toggle-theme.js"></script>
    <title>Schedule Management - <?php echo htmlspecialchars($account['name']); ?></title>
</head>
<body id="schedules">
    <?php include_once '../../../src/ui/sidebar.php'; ?>
    
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
                <div class="template-controls">
                    <div class="template-actions">
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
                    <!-- Shift Library - Now fixed position -->
                    <div class="shift-library">
                        <div class="panel-header">
                            <h3>Shift Library</h3>
                        </div>
                        <div class="shift-list" id="shiftList">
                            <!-- Morning Shift -->
                            <div class="shift-item" draggable="true" data-shift-id="1">
                                <div class="shift-content">
                                    <div class="shift-color" style="background: #4CAF50;"></div>
                                    <div class="shift-details">
                                        <div class="shift-name">Morning Shift</div>
                                        <div class="shift-time">06:00 - 14:00</div>
                                        <div class="shift-rules">30 min break @ 10:00</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Afternoon Shift -->
                            <div class="shift-item" draggable="true" data-shift-id="2">
                                <div class="shift-content">
                                    <div class="shift-color" style="background: #2196F3;"></div>
                                    <div class="shift-details">
                                        <div class="shift-name">Afternoon Shift</div>
                                        <div class="shift-time">14:00 - 22:00</div>
                                        <div class="shift-rules">30 min break @ 18:00</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Night Shift -->
                            <div class="shift-item" draggable="true" data-shift-id="3">
                                <div class="shift-content">
                                    <div class="shift-color" style="background: #9C27B0;"></div>
                                    <div class="shift-details">
                                        <div class="shift-name">Night Shift</div>
                                        <div class="shift-time">22:00 - 06:00</div>
                                        <div class="shift-rules">45 min break @ 02:00</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Split Shift -->
                            <div class="shift-item" draggable="true" data-shift-id="4">
                                <div class="shift-content">
                                    <div class="shift-color" style="background: #FF9800;"></div>
                                    <div class="shift-details">
                                        <div class="shift-name">Split Shift</div>
                                        <div class="shift-time">10:00-14:00, 18:00-22:00</div>
                                        <div class="shift-rules">4hr break between shifts</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Weekend Shift -->
                            <div class="shift-item" draggable="true" data-shift-id="5">
                                <div class="shift-content">
                                    <div class="shift-color" style="background: #F44336;"></div>
                                    <div class="shift-details">
                                        <div class="shift-name">Weekend Shift</div>
                                        <div class="shift-time">12:00 - 20:00</div>
                                        <div class="shift-rules">Weekend rate applies</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Template Editor - Now scrollable with full width work weeks -->
                    <div class="template-editor">
                        <div class="template-list" id="templateList">
                            <!-- Each template will be rendered here -->
                            <div class="template-item">
                                <div class="editor-header">
                                    <input type="text" id="templateName" placeholder="Template Name" class="template-name-input">
                                    <div class="editor-actions">
                                        <button class="action-btn" id="copyTemplateBtn">
                                            <i class="material-icons">content_copy</i>
                                            Copy
                                        </button>
                                        <button class="action-btn" id="deleteTemplateBtn">
                                            <i class="material-icons">delete</i>
                                            Delete
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
                            <!-- Additional templates will be added here -->
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Rosters Tab -->
            <div class="schedule-tab" id="rosters-tab">
                <div class="roster-controls">
                    <div class="roster-template-controls">
                        <select id="rosterSelect" class="roster-dropdown">
                            <option value="">Select Roster</option>
                        </select>
                        <button class="action-btn" id="addRosterBtn">
                            <i class="material-icons">add</i>
                            New Roster
                        </button>
                        <button class="action-btn" id="saveRosterBtn">
                            <i class="material-icons">save</i>
                            Save Roster
                        </button>
                    </div>
                    
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
                        <button class="action-btn" id="assignEmployeesBtn">
                            <i class="material-icons">person_add</i>
                            Assign Employees
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

                <div class="tab-content">
                    <!-- Shift Library -->
                    <div class="shift-library">
                        <div class="panel-header">
                            <h3>Shift Library</h3>
                        </div>
                        <div class="shift-list" id="rosterShiftList">
                            <!-- Morning Shift -->
                            <div class="shift-item" draggable="true" data-shift-id="1">
                                <div class="shift-content">
                                    <div class="shift-color" style="background: #4CAF50;"></div>
                                    <div class="shift-details">
                                        <div class="shift-name">Morning Shift</div>
                                        <div class="shift-time">06:00 - 14:00</div>
                                        <div class="shift-rules">30 min break @ 10:00</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Afternoon Shift -->
                            <div class="shift-item" draggable="true" data-shift-id="2">
                                <div class="shift-content">
                                    <div class="shift-color" style="background: #2196F3;"></div>
                                    <div class="shift-details">
                                        <div class="shift-name">Afternoon Shift</div>
                                        <div class="shift-time">14:00 - 22:00</div>
                                        <div class="shift-rules">30 min break @ 18:00</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Night Shift -->
                            <div class="shift-item" draggable="true" data-shift-id="3">
                                <div class="shift-content">
                                    <div class="shift-color" style="background: #9C27B0;"></div>
                                    <div class="shift-details">
                                        <div class="shift-name">Night Shift</div>
                                        <div class="shift-time">22:00 - 06:00</div>
                                        <div class="shift-rules">45 min break @ 02:00</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Split Shift -->
                            <div class="shift-item" draggable="true" data-shift-id="4">
                                <div class="shift-content">
                                    <div class="shift-color" style="background: #FF9800;"></div>
                                    <div class="shift-details">
                                        <div class="shift-name">Split Shift</div>
                                        <div class="shift-time">10:00-14:00, 18:00-22:00</div>
                                        <div class="shift-rules">4hr break between shifts</div>
                                    </div>
                                </div>
                            </div>

                            <!-- Weekend Shift -->
                            <div class="shift-item" draggable="true" data-shift-id="5">
                                <div class="shift-content">
                                    <div class="shift-color" style="background: #F44336;"></div>
                                    <div class="shift-details">
                                        <div class="shift-name">Weekend Shift</div>
                                        <div class="shift-time">12:00 - 20:00</div>
                                        <div class="shift-rules">Weekend rate applies</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Monthly Calendar -->
                    <div class="monthly-calendar">
                        <div class="calendar-header">
                            <div>Sunday</div>
                            <div>Monday</div>
                            <div>Tuesday</div>
                            <div>Wednesday</div>
                            <div>Thursday</div>
                            <div>Friday</div>
                            <div>Saturday</div>
                        </div>
                        <div class="calendar-body" id="calendarGrid">
                            <!-- Calendar days will be loaded here -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php include_once '../modals/add-shift-modal.php'; ?>
    <?php include_once '../modals/setup-modal.php'; ?>
    <script src="../js/schedules.js"></script>
</body>
</html>
