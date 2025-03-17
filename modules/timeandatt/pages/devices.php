<?php
session_start();

// Account Number Handling
if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
} else {
    header("Location: ../techlogin.php");
    exit;
}

// User Name Handling
$userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Time & Attendance - Devices</title>
    <link rel="stylesheet" href="../../../css/root.css">
    <link rel="stylesheet" href="../../../css/sidebar.css">
    <link rel="stylesheet" href="../css/devices.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body id="devices">
    <?php include '../../../main/sidebar.php'; ?>
    
    <main class="main-content">
        <div class="content-header">
            <h1>Devices Manager</h1>
            <div class="action-buttons">
                <button id="refreshDevicesBtn" class="btn btn-primary">
                    <i class="fas fa-sync-alt"></i> Refresh
                </button>
                <button id="addDeviceBtn" class="btn btn-secondary">
                    <i class="fas fa-plus"></i> Add Device
                </button>
            </div>
        </div>
        
        <div class="devices-grid" id="devicesGrid">
            <!-- Devices will be loaded here -->
            <div class="device-loading">
                <div class="spinner"></div>
                <p>Loading devices...</p>
            </div>
        </div>
        
        <div id="noDevicesMessage" class="no-data-message hidden">
            <i class="fas fa-video-slash"></i>
            <h2>No Devices Found</h2>
            <p>No clock devices are currently registered for this account.</p>
            <p>Devices will be automatically added when they connect to the clock server.</p>
            <button class="btn btn-primary" id="addFirstDeviceBtn">Add First Device</button>
        </div>
    </main>
    
    <!-- Device Control Modal -->
    <div id="deviceControlModal" class="custom-modal hidden">
        <div class="custom-modal-content large-modal">
            <div class="custom-modal-header">
                <h2 id="deviceModalTitle">Device Control</h2>
                <span class="custom-modal-close" onclick="closeDeviceModal()">&times;</span>
            </div>
            <div class="custom-modal-body">
                <div class="modal-tabs">
                    <button class="modal-tab-btn active" onclick="openDeviceTab(event, 'deviceLive')">Live View</button>
                    <button class="modal-tab-btn" onclick="openDeviceTab(event, 'deviceIntercom')">Intercom</button>
                    <button class="modal-tab-btn" onclick="openDeviceTab(event, 'deviceDoor')">Door Control</button>
                    <button class="modal-tab-btn" onclick="openDeviceTab(event, 'deviceCommands')">Commands</button>
                    <button class="modal-tab-btn" onclick="openDeviceTab(event, 'deviceInfo')">Info</button>
                </div>
                
                <!-- Live View Tab -->
                <div id="deviceLive" class="modal-tab-content active">
                    <div class="camera-container">
                        <div class="camera-loading" id="cameraLoading">
                            <div class="spinner"></div>
                            <p>Loading camera feed...</p>
                        </div>
                        <div class="camera-error hidden" id="cameraError">
                            <i class="fas fa-exclamation-triangle"></i>
                            <p>Could not connect to camera</p>
                            <button class="btn btn-primary" onclick="refreshCameraFeed()">Retry</button>
                        </div>
                        <img id="cameraFeed" class="camera-feed hidden" alt="Live Camera Feed">
                    </div>
                </div>
                
                <!-- Intercom Tab -->
                <div id="deviceIntercom" class="modal-tab-content">
                    <div class="intercom-container">
                        <div class="intercom-status">
                            <span id="intercomStatus">Ready</span>
                        </div>
                        <div class="intercom-controls">
                            <button id="callBtn" class="btn btn-primary">
                                <i class="fas fa-phone-alt"></i> Call Device
                            </button>
                            <button id="hangupBtn" class="btn btn-danger" disabled>
                                <i class="fas fa-phone-slash"></i> Hang Up
                            </button>
                        </div>
                        <div class="audio-controls">
                            <div class="audio-control">
                                <label for="speakerVolume">Speaker</label>
                                <input type="range" id="speakerVolume" min="0" max="100" value="50">
                            </div>
                            <div class="audio-control">
                                <label for="micVolume">Microphone</label>
                                <input type="range" id="micVolume" min="0" max="100" value="50">
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Door Control Tab -->
                <div id="deviceDoor" class="modal-tab-content">
                    <div class="door-container">
                        <div class="door-status">
                            <div class="status-indicator">
                                <span id="doorStatus">Unknown</span>
                            </div>
                        </div>
                        <div class="door-controls">
                            <button id="unlockBtn" class="btn btn-success door-btn">
                                <i class="fas fa-lock-open"></i> Unlock Door
                            </button>
                            <button id="lockBtn" class="btn btn-danger door-btn">
                                <i class="fas fa-lock"></i> Lock Door
                            </button>
                            <button id="holdBtn" class="btn btn-warning door-btn">
                                <i class="fas fa-hourglass-half"></i> Hold Open
                                <select id="holdDuration">
                                    <option value="30">30 sec</option>
                                    <option value="60" selected>1 min</option>
                                    <option value="300">5 min</option>
                                    <option value="600">10 min</option>
                                </select>
                            </button>
                        </div>
                        <div class="door-activity">
                            <h3>Recent Activity</h3>
                            <div class="activity-list" id="doorActivityList">
                                <!-- Activity will be loaded here -->
                                <div class="activity-loading">
                                    <div class="spinner"></div>
                                    <p>Loading activity...</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Commands Tab -->
                <div id="deviceCommands" class="modal-tab-content">
                    <div class="commands-container">
                        <div class="command-group">
                            <h3>Device Management</h3>
                            <button class="btn btn-primary cmd-btn" onclick="executeCommand('restart')">
                                <i class="fas fa-redo"></i> Restart Device
                            </button>
                            <button class="btn btn-secondary cmd-btn" onclick="executeCommand('check_status')">
                                <i class="fas fa-info-circle"></i> Check Status
                            </button>
                            <button class="btn btn-secondary cmd-btn" onclick="executeCommand('sync_time')">
                                <i class="fas fa-clock"></i> Sync Time
                            </button>
                        </div>
                        <div class="command-group">
                            <h3>Templates Management</h3>
                            <button class="btn btn-primary cmd-btn" onclick="executeCommand('get_templates')">
                                <i class="fas fa-download"></i> Get Templates
                            </button>
                            <button class="btn btn-primary cmd-btn" onclick="executeCommand('push_templates')">
                                <i class="fas fa-upload"></i> Push Templates
                            </button>
                        </div>
                        <div class="command-group">
                            <h3>Access Control</h3>
                            <button class="btn btn-warning cmd-btn" onclick="executeCommand('clear_alarms')">
                                <i class="fas fa-bell-slash"></i> Clear Alarms
                            </button>
                        </div>
                    </div>
                </div>
                
                <!-- Info Tab -->
                <div id="deviceInfo" class="modal-tab-content">
                    <div class="device-info-container">
                        <div class="info-group">
                            <h3>Device Details</h3>
                            <table class="info-table">
                                <tr>
                                    <th>Device ID</th>
                                    <td id="deviceInfoId"></td>
                                </tr>
                                <tr>
                                    <th>Serial Number</th>
                                    <td id="deviceInfoSerial"></td>
                                </tr>
                                <tr>
                                    <th>Device Name</th>
                                    <td id="deviceInfoName"></td>
                                </tr>
                                <tr>
                                    <th>IP Address</th>
                                    <td id="deviceInfoIp"></td>
                                </tr>
                                <tr>
                                    <th>MAC Address</th>
                                    <td id="deviceInfoMac"></td>
                                </tr>
                                <tr>
                                    <th>Firmware</th>
                                    <td id="deviceInfoFirmware"></td>
                                </tr>
                                <tr>
                                    <th>Model</th>
                                    <td id="deviceInfoModel"></td>
                                </tr>
                                <tr>
                                    <th>Status</th>
                                    <td id="deviceInfoStatus"></td>
                                </tr>
                                <tr>
                                    <th>Last Online</th>
                                    <td id="deviceInfoLastOnline"></td>
                                </tr>
                            </table>
                        </div>
                        <div class="actions-group">
                            <h3>Device Actions</h3>
                            <button class="btn btn-secondary" onclick="editCurrentDevice()">
                                <i class="fas fa-edit"></i> Edit Device
                            </button>
                            <button class="btn btn-danger" onclick="deleteCurrentDevice()">
                                <i class="fas fa-trash"></i> Delete Device
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Add Device Modal -->
    <div id="addDeviceModal" class="custom-modal hidden">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h2>Add Device</h2>
                <span class="custom-modal-close" onclick="closeAddDeviceModal()">&times;</span>
            </div>
            <div class="custom-modal-body">
                <div class="form-row">
                    <div class="form-group">
                        <label for="deviceId">Device ID/Serial</label>
                        <input type="text" id="deviceId" required>
                    </div>
                    <div class="form-group">
                        <label for="deviceName">Device Name</label>
                        <input type="text" id="deviceName" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="deviceIp">IP Address</label>
                        <input type="text" id="deviceIp" required>
                    </div>
                    <div class="form-group">
                        <label for="deviceMac">MAC Address</label>
                        <input type="text" id="deviceMac">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="deviceUsername">Username</label>
                        <input type="text" id="deviceUsername" value="admin">
                    </div>
                    <div class="form-group">
                        <label for="devicePassword">Password</label>
                        <input type="password" id="devicePassword" value="12345">
                    </div>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button class="btn btn-secondary" onclick="closeAddDeviceModal()">Cancel</button>
                <button class="btn btn-primary" onclick="saveDevice()">Save Device</button>
            </div>
        </div>
    </div>
    
    <!-- Edit Device Modal - Simplified version of Add Device Modal -->
    <div id="editDeviceModal" class="custom-modal hidden">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h2>Edit Device</h2>
                <span class="custom-modal-close" onclick="closeEditDeviceModal()">&times;</span>
            </div>
            <div class="custom-modal-body">
                <input type="hidden" id="editDeviceId">
                <div class="form-row">
                    <div class="form-group">
                        <label for="editDeviceDeviceId">Device ID/Serial</label>
                        <input type="text" id="editDeviceDeviceId" readonly>
                    </div>
                    <div class="form-group">
                        <label for="editDeviceName">Device Name</label>
                        <input type="text" id="editDeviceName" required>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editDeviceIp">IP Address</label>
                        <input type="text" id="editDeviceIp" required>
                    </div>
                    <div class="form-group">
                        <label for="editDeviceMac">MAC Address</label>
                        <input type="text" id="editDeviceMac">
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label for="editDeviceUsername">Username</label>
                        <input type="text" id="editDeviceUsername">
                    </div>
                    <div class="form-group">
                        <label for="editDevicePassword">Password</label>
                        <input type="password" id="editDevicePassword" placeholder="Leave blank to keep current">
                    </div>
                </div>
            </div>
            <div class="custom-modal-footer">
                <button class="btn btn-secondary" onclick="closeEditDeviceModal()">Cancel</button>
                <button class="btn btn-primary" onclick="updateDevice()">Update Device</button>
            </div>
        </div>
    </div>
    
    <!-- Include Modals -->
    <?php include '../../../php/loading-modal.php'; ?>
    <?php include '../../../php/response-modal.php'; ?>
    
    <!-- Scripts -->
    <script src="../../../js/toggle-theme.js"></script>
    <script src="../../../js/sidebar.js"></script>
    <script src="../js/devices.js"></script>
</body>
</html> 