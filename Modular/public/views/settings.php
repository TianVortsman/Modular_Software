<?php
session_start();

// Check if account number is in the query parameters
if (isset($_GET['account_number'])) {
    $account_number = $_GET['account_number'];

    // Store the account number in the session
    $_SESSION['account_number'] = $account_number;

    // Optionally, redirect to remove the query parameter from the URL
    header("Location: ../views/dashboard.php");
    exit;
}

// If the account number is already in the session, use it
if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
} else {
    // Redirect to login or show an error if no account number is found
    header("Location: ../admin/techlogin.php"); 
    exit;
}

$userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');

?>
<!DOCTYPE html>
<html lang="en" id="page">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Settings</title>
    <link rel="stylesheet" href="../assets/css/reset.css">
    <link rel="stylesheet" href="../assets/css/root.css">
    <link rel="stylesheet" href="../assets/css/sidebar.css">
    <link rel="stylesheet" href="../assets/css/settings.css">
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="../assets/js/toggle-theme.js" type="module"></script>
</head>
<body id="settings">
<?php include '../../src/UI/sidebar.php'; ?>
<div class="settings-container" id="settings-container">
        <div class="settings-content">
        <!-- Section for Preferences -->
        <div id="preferences-settings" class="setting-section">
            <h3>Preferences</h3>

            <!-- Theme Settings -->
            <div class="section">
                <h4>Theme Settings</h4>

                <label for="theme-selection">Select Theme:</label>
                <select id="theme-selection">
                    <option value="system">System</option>
                    <option value="light">Light</option>
                    <option value="light-mode-green">Light Green</option>
                    <option value="light-mode-brown">Light Brown</option>
                    <option value="dark">Dark</option>
                    <option value="dark-mode-blue">Dark Blue</option>
                    <option value="dark-mode-red">Dark Red</option>
                    <option value="dark-mode-brown">Dark Brown</option>
                </select>
            </div>

            <!-- Language Settings -->
            <div class="section">
                <h4>Language Settings</h4>

                <label for="language-selection">Select Language:</label>
                <select id="language-selection">
                    <option value="en">English</option>
                    <option value="es">Spanish</option>
                    <option value="fr">French</option>
                    <option value="de">German</option>
                </select>
            </div>

            <!-- Notification Settings -->
            <div class="section">
                <h4>Notification Settings</h4>

                <label for="email-notifications">Email Notifications:</label>
                <select id="email-notifications">
                    <option value="enabled">Enabled</option>
                    <option value="disabled">Disabled</option>
                </select>

                <label for="sms-notifications">SMS Notifications:</label>
                <select id="sms-notifications">
                    <option value="enabled">Enabled</option>
                    <option value="disabled">Disabled</option>
                </select>
            </div>

            <!-- Save Button -->
            <button type="button">Save Preferences</button>
        </div>


            <!-- Section for Module 1 -->
            <div id="time-attendance-settings" class="setting-section">
        <h3>Time and Attendance Settings</h3>

        <!-- Employee Attendance Settings -->
        <div class="section">
                <h4>Attendance Settings</h4>

                <label for="attendance-policy">Attendance Policy:</label>
                <select id="attendance-policy">
                    <option value="flexible">Flexible</option>
                    <option value="fixed">Fixed</option>
                </select>
                
                <label for="late-punch-policy">Late Punch Policy:</label>
                <select id="late-punch-policy">
                    <option value="allow-late">Allow Late Punch</option>
                    <option value="no-late">No Late Punch</option>
                </select>
                
                <label for="attendance-threshold">Attendance Threshold (in minutes):</label>
                <input type="number" id="attendance-threshold" min="0" max="60" placeholder="Enter threshold for attendance lateness">
            </div>

            <!-- Shift Settings -->
            <div class="section">
                <h4>Shift Settings</h4>

                <label for="shift-start-time">Shift Start Time:</label>
                <input type="time" id="shift-start-time" value="09:00">

                <label for="shift-end-time">Shift End Time:</label>
                <input type="time" id="shift-end-time" value="17:00">

                <label for="default-shift-duration">Default Shift Duration (hours):</label>
                <input type="number" id="default-shift-duration" min="1" max="24" value="8">
            </div>

            <!-- Overtime Settings -->
            <div class="section">
                <h4>Overtime Settings</h4>

                <label for="overtime-threshold">Overtime Threshold (in hours):</label>
                <input type="number" id="overtime-threshold" min="0" max="24" placeholder="Enter threshold for overtime">

                <label for="overtime-rate">Overtime Rate (as % of regular rate):</label>
                <input type="number" id="overtime-rate" min="1" max="100" value="150">
            </div>

            <!-- Attendance Notifications Settings -->
            <div class="section">
                <h4>Attendance Notifications</h4>

                <label for="attendance-notifications">Attendance Notifications:</label>
                <select id="attendance-notifications">
                    <option value="enabled">Enabled</option>
                    <option value="disabled">Disabled</option>
                </select>

                <label for="attendance-notification-time">Notification Time (in minutes before shift):</label>
                <input type="number" id="attendance-notification-time" min="1" max="60" placeholder="Enter notification time before shift start">
            </div>

            <!-- Save Button -->
            <button type="button">Save Time and Attendance Settings</button>
        </div>


        <!-- Section for Module 2 -->
        <div id="invoicing-settings" class="setting-section">
            <h3>Module 2 Settings</h3>
            <!-- Add your Module 2 specific settings here -->
            <label for="setting2">Setting 2:</label>
            <input type="text" id="setting2" placeholder="Enter setting for Module 2">
            <button type="button">Save Module 2 Settings</button>
        </div>
        
        <!-- Section for Module 3 -->
        <div id="payroll-settings" class="setting-section">
            <h3>Module 3 Settings</h3>
            <label for="setting3">Setting 3:</label>
            <input type="text" id="setting3" placeholder="Enter setting for Module 3">
            <button type="button">Save Module 3 Settings</button>
        </div>

        <!-- Section for Module 4 -->
        <div id="inventory-settings" class="setting-section">
            <h3>Module 4 Settings</h3>
            <label for="setting4">Setting 4:</label>
            <input type="text" id="setting4" placeholder="Enter setting for Module 4">
            <button type="button">Save Module 4 Settings</button>
        </div>

        <!-- Section for Module 5 -->
        <div id="crm-settings" class="setting-section">
            <h3>Module 5 Settings</h3>
            <label for="setting5">Setting 5:</label>
            <input type="text" id="setting5" placeholder="Enter setting for Module 5">
            <button type="button">Save Module 5 Settings</button>
        </div>

        <!-- Section for Module 6 -->
        <div id="project-management-settings" class="setting-section">
            <h3>Module 6 Settings</h3>
            <label for="setting6">Setting 6:</label>
            <input type="text" id="setting6" placeholder="Enter setting for Module 6">
            <button type="button">Save Module 6 Settings</button>
        </div>

        <!-- Section for Module 7 -->
        <div id="accounting-settings" class="setting-section">
            <h3>Module 7 Settings</h3>
            <label for="setting7">Setting 7:</label>
            <input type="text" id="setting7" placeholder="Enter setting for Module 7">
            <button type="button">Save Module 7 Settings</button>
        </div>

        <!-- Section for Module 8 -->
        <div id="hr-settings" class="setting-section">
            <h3>Module 8 Settings</h3>
            <label for="setting8">Setting 8:</label>
            <input type="text" id="setting8" placeholder="Enter setting for Module 8">
            <button type="button">Save Module 8 Settings</button>
        </div>

        <!-- Section for Module 9 -->
        <div id="support-settings" class="setting-section">
            <h3>Module 9 Settings</h3>
            <label for="setting9">Setting 9:</label>
            <input type="text" id="setting9" placeholder="Enter setting for Module 9">
            <button type="button">Save Module 9 Settings</button>
        </div>

        <!-- Section for Module 10 -->
        <div id="fleet-settings" class="setting-section">
            <h3>Module 10 Settings</h3>
            <label for="setting10">Setting 10:</label>
            <input type="text" id="setting10" placeholder="Enter setting for Module 10">
            <button type="button">Save Module 10 Settings</button>
        </div>

        <!-- Section for Module 11 -->
        <div id="asset-settings" class="setting-section">
            <h3>Module 11 Settings</h3>
            <label for="setting11">Setting 11:</label>
            <input type="text" id="setting11" placeholder="Enter setting for Module 11">
            <button type="button">Save Module 11 Settings</button>
        </div>

        <div id="access-control-settings" class="setting-section">
            <h3>Module 12 Settings</h3>
            <label for="setting12">Setting 12:</label>
            <input type="text" id="setting12" placeholder="Enter setting for Module 12">
            <button type="button">Save Module 12 Settings</button>
        </div>
    </div>
</div>
<script src="../assets/js/settings.js"></script>
<script src="../assets/js/sidebar.js"></script>
</body>
</html>
