// Devices Page JavaScript

// Global variables
let devices = [];
let currentDevice = null;
let cameraRefreshInterval = null;
let cameraRefreshRate = 1000; // 1 second

// DOM Elements
const devicesGrid = document.getElementById('devicesGrid');
const noDevicesMessage = document.getElementById('noDevicesMessage');
const refreshDevicesBtn = document.getElementById('refreshDevicesBtn');
const addDeviceBtn = document.getElementById('addDeviceBtn');
const addFirstDeviceBtn = document.getElementById('addFirstDeviceBtn');

// Init on page load
document.addEventListener('DOMContentLoaded', () => {
    // Fetch devices on page load
    loadDevices();
    
    // Set up event listeners
    refreshDevicesBtn.addEventListener('click', loadDevices);
    addDeviceBtn.addEventListener('click', openAddDeviceModal);
    addFirstDeviceBtn.addEventListener('click', openAddDeviceModal);
    
    // Door control event listeners
    document.getElementById('unlockBtn').addEventListener('click', () => controlDoor('unlock'));
    document.getElementById('lockBtn').addEventListener('click', () => controlDoor('lock'));
    document.getElementById('holdBtn').addEventListener('click', () => {
        const duration = document.getElementById('holdDuration').value;
        controlDoor('hold', duration);
    });
    
    // Intercom event listeners
    document.getElementById('callBtn').addEventListener('click', startCall);
    document.getElementById('hangupBtn').addEventListener('click', endCall);
});

// Load devices from API
function loadDevices() {
    // Show loading, hide no devices message
    devicesGrid.innerHTML = `
        <div class="device-loading">
            <div class="spinner"></div>
            <p>Loading devices...</p>
        </div>
    `;
    noDevicesMessage.classList.add('hidden');
    
    // Fetch devices from API
    fetch('api/get_devices.php')
        .then(response => response.json())
        .then(data => {
            if (data.success && data.devices && data.devices.length > 0) {
                devices = data.devices;
                renderDevices(devices);
            } else {
                showNoDevices();
            }
        })
        .catch(error => {
            console.error('Error loading devices:', error);
            showNoDevices(error.message);
        });
}

// Render devices to grid
function renderDevices(devicesList) {
    devicesGrid.innerHTML = '';
    
    devicesList.forEach(device => {
        const card = createDeviceCard(device);
        devicesGrid.appendChild(card);
    });
}

// Create a device card element
function createDeviceCard(device) {
    const card = document.createElement('div');
    card.className = `device-card ${device.status}`;
    card.setAttribute('data-device-id', device.id);
    
    // Format last online date for display
    let lastOnlineDisplay = 'Never';
    if (device.last_online) {
        const lastOnline = new Date(device.last_online);
        const now = new Date();
        const diffMs = now - lastOnline;
        const diffMins = Math.floor(diffMs / 60000);
        const diffHours = Math.floor(diffMins / 60);
        const diffDays = Math.floor(diffHours / 24);
        
        if (diffMins < 1) {
            lastOnlineDisplay = 'Just now';
        } else if (diffMins < 60) {
            lastOnlineDisplay = `${diffMins} min${diffMins !== 1 ? 's' : ''} ago`;
        } else if (diffHours < 24) {
            lastOnlineDisplay = `${diffHours} hour${diffHours !== 1 ? 's' : ''} ago`;
        } else if (diffDays < 30) {
            lastOnlineDisplay = `${diffDays} day${diffDays !== 1 ? 's' : ''} ago`;
        } else {
            lastOnlineDisplay = lastOnline.toLocaleDateString();
        }
    }
    
    card.innerHTML = `
        <div class="device-icon">
            <i class="fas fa-video"></i>
        </div>
        <div class="device-body">
            <div class="device-name">${device.device_name}</div>
            <div class="device-id">ID: ${device.device_id}</div>
            <div class="device-ip">IP: ${device.ip_address}</div>
        </div>
        <div class="device-footer">
            <div class="device-status">
                <div class="status-indicator status-${device.status}"></div>
                ${device.status.charAt(0).toUpperCase() + device.status.slice(1)}
            </div>
            <div class="device-last-seen">Last seen: ${lastOnlineDisplay}</div>
        </div>
    `;
    
    // Add double-click event listener to open device control modal
    card.addEventListener('dblclick', () => {
        openDeviceControlModal(device);
    });
    
    return card;
}

// Show "No devices" message
function showNoDevices(errorMessage = null) {
    devicesGrid.innerHTML = '';
    noDevicesMessage.classList.remove('hidden');
    
    if (errorMessage) {
        noDevicesMessage.innerHTML = `
            <i class="fas fa-exclamation-triangle"></i>
            <h2>Error Loading Devices</h2>
            <p>${errorMessage}</p>
            <button class="btn btn-primary" onclick="loadDevices()">Retry</button>
        `;
    }
}

// Add device modal functions
function openAddDeviceModal() {
    document.getElementById('addDeviceModal').classList.remove('hidden');
}

function closeAddDeviceModal() {
    document.getElementById('addDeviceModal').classList.add('hidden');
    // Reset form fields
    document.getElementById('deviceId').value = '';
    document.getElementById('deviceName').value = '';
    document.getElementById('deviceIp').value = '';
    document.getElementById('deviceMac').value = '';
    document.getElementById('deviceUsername').value = 'admin';
    document.getElementById('devicePassword').value = '12345';
}

function saveDevice() {
    const deviceData = {
        device_id: document.getElementById('deviceId').value,
        device_name: document.getElementById('deviceName').value,
        ip_address: document.getElementById('deviceIp').value,
        mac_address: document.getElementById('deviceMac').value,
        username: document.getElementById('deviceUsername').value,
        password: document.getElementById('devicePassword').value
    };
    
    // Validate required fields
    if (!deviceData.device_id || !deviceData.device_name || !deviceData.ip_address) {
        showResponseModal('error', 'Device ID, Name and IP Address are required');
        return;
    }
    
    // Show loading modal
    showLoadingModal('Saving device...');
    
    // Send data to server
    fetch('api/add_device.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(deviceData)
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingModal();
        
        if (data.success) {
            showResponseModal('success', 'Device added successfully');
            closeAddDeviceModal();
            loadDevices(); // Refresh the devices list
        } else {
            showResponseModal('error', data.message || 'Failed to add device');
        }
    })
    .catch(error => {
        hideLoadingModal();
        showResponseModal('error', 'Error saving device: ' + error.message);
    });
}

// Device control modal functions
function openDeviceControlModal(device) {
    currentDevice = device;
    
    // Set modal title
    document.getElementById('deviceModalTitle').textContent = `Device: ${device.device_name}`;
    
    // Reset tabs to default
    document.querySelector('.modal-tab-btn.active').classList.remove('active');
    document.querySelector('.modal-tab-btn').classList.add('active');
    
    document.querySelector('.modal-tab-content.active').classList.remove('active');
    document.getElementById('deviceLive').classList.add('active');
    
    // Fill device info tab
    document.getElementById('deviceInfoId').textContent = device.device_id;
    document.getElementById('deviceInfoSerial').textContent = device.serial_number || device.device_id;
    document.getElementById('deviceInfoName').textContent = device.device_name;
    document.getElementById('deviceInfoIp').textContent = device.ip_address;
    document.getElementById('deviceInfoMac').textContent = device.mac_address || 'N/A';
    document.getElementById('deviceInfoFirmware').textContent = device.firmware_version || 'Unknown';
    document.getElementById('deviceInfoModel').textContent = device.model || 'Unknown';
    document.getElementById('deviceInfoStatus').textContent = device.status;
    document.getElementById('deviceInfoLastOnline').textContent = device.last_online ? new Date(device.last_online).toLocaleString() : 'Never';
    
    // Start camera feed
    startCameraFeed(device);
    
    // Show the modal
    document.getElementById('deviceControlModal').classList.remove('hidden');
    
    // Load door activity if we switch to that tab
    document.querySelectorAll('.modal-tab-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            if (this.textContent === 'Door Control') {
                loadDoorActivity(device.device_id);
            }
        });
    });
}

function closeDeviceModal() {
    document.getElementById('deviceControlModal').classList.add('hidden');
    currentDevice = null;
    
    // Stop camera refresh interval
    if (cameraRefreshInterval) {
        clearInterval(cameraRefreshInterval);
        cameraRefreshInterval = null;
    }
}

function openDeviceTab(evt, tabName) {
    // Hide all tab content
    document.querySelectorAll('.modal-tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from all tab buttons
    document.querySelectorAll('.modal-tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Show the selected tab and mark its button as active
    document.getElementById(tabName).classList.add('active');
    evt.currentTarget.classList.add('active');
    
    // Special handling for different tabs
    if (tabName === 'deviceLive') {
        startCameraFeed(currentDevice);
    } else if (tabName === 'deviceDoor') {
        loadDoorActivity(currentDevice.device_id);
    }
}

// Camera feed functions
function startCameraFeed(device) {
    // Show loading, hide error and feed
    document.getElementById('cameraLoading').classList.remove('hidden');
    document.getElementById('cameraError').classList.add('hidden');
    document.getElementById('cameraFeed').classList.add('hidden');
    
    // Clear any existing interval
    if (cameraRefreshInterval) {
        clearInterval(cameraRefreshInterval);
    }
    
    // Function to fetch and update camera image
    const updateCameraImage = () => {
        const cameraFeed = document.getElementById('cameraFeed');
        const timestamp = new Date().getTime(); // Prevent caching
        
        // Set camera feed source
        cameraFeed.src = `api/get_camera_snapshot.php?device_id=${device.id}&t=${timestamp}`;
        
        // Handle image load success
        cameraFeed.onload = () => {
            document.getElementById('cameraLoading').classList.add('hidden');
            cameraFeed.classList.remove('hidden');
        };
        
        // Handle image load error
        cameraFeed.onerror = () => {
            document.getElementById('cameraLoading').classList.add('hidden');
            document.getElementById('cameraError').classList.remove('hidden');
            cameraFeed.classList.add('hidden');
            
            // Stop the refresh interval on error
            if (cameraRefreshInterval) {
                clearInterval(cameraRefreshInterval);
                cameraRefreshInterval = null;
            }
        };
    };
    
    // Initial update
    updateCameraImage();
    
    // Set interval for refresh
    cameraRefreshInterval = setInterval(updateCameraImage, cameraRefreshRate);
}

function refreshCameraFeed() {
    if (currentDevice) {
        startCameraFeed(currentDevice);
    }
}

// Door control functions
function loadDoorActivity(deviceId) {
    const activityList = document.getElementById('doorActivityList');
    
    activityList.innerHTML = `
        <div class="activity-loading">
            <div class="spinner"></div>
            <p>Loading activity...</p>
        </div>
    `;
    
    fetch(`api/get_door_activity.php?device_id=${deviceId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.activities && data.activities.length > 0) {
                activityList.innerHTML = '';
                
                data.activities.forEach(activity => {
                    const activityItem = document.createElement('div');
                    activityItem.className = 'activity-item';
                    
                    const activityTime = new Date(activity.created_at).toLocaleString();
                    
                    activityItem.innerHTML = `
                        <div class="activity-time">${activityTime}</div>
                        <div class="activity-action">
                            <span class="activity-status ${activity.status === 'success' ? 'status-online' : 'status-offline'}">${activity.status}</span>
                            ${activity.action_type}
                        </div>
                    `;
                    
                    activityList.appendChild(activityItem);
                });
            } else {
                activityList.innerHTML = '<p>No recent door activity found.</p>';
            }
        })
        .catch(error => {
            console.error('Error loading door activity:', error);
            activityList.innerHTML = `<p>Error loading activity: ${error.message}</p>`;
        });
}

function controlDoor(action, duration = null) {
    if (!currentDevice) return;
    
    const data = {
        deviceId: currentDevice.device_id,
        action: action
    };
    
    if (duration) {
        data.duration = duration;
    }
    
    // Show loading modal
    showLoadingModal(`${action.charAt(0).toUpperCase() + action.slice(1)}ing door...`);
    
    // Send command to server
    fetch('api/control_door.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(data)
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingModal();
        
        if (data.success) {
            showResponseModal('success', `Door ${action} command sent successfully`);
            // Refresh door activity
            loadDoorActivity(currentDevice.device_id);
        } else {
            showResponseModal('error', data.message || `Failed to ${action} door`);
        }
    })
    .catch(error => {
        hideLoadingModal();
        showResponseModal('error', `Error controlling door: ${error.message}`);
    });
}

// Intercom functions
function startCall() {
    if (!currentDevice) return;
    
    // Update UI
    document.getElementById('intercomStatus').textContent = 'Calling...';
    document.getElementById('callBtn').disabled = true;
    document.getElementById('hangupBtn').disabled = false;
    
    // Send call command to server
    fetch('api/start_call.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            deviceId: currentDevice.device_id
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('intercomStatus').textContent = 'Connected';
            // Here you would initialize WebRTC or other audio streaming
        } else {
            document.getElementById('intercomStatus').textContent = 'Failed to connect';
            document.getElementById('callBtn').disabled = false;
            document.getElementById('hangupBtn').disabled = true;
            showResponseModal('error', data.message || 'Failed to start call');
        }
    })
    .catch(error => {
        document.getElementById('intercomStatus').textContent = 'Error';
        document.getElementById('callBtn').disabled = false;
        document.getElementById('hangupBtn').disabled = true;
        showResponseModal('error', `Error starting call: ${error.message}`);
    });
}

function endCall() {
    if (!currentDevice) return;
    
    // Update UI
    document.getElementById('intercomStatus').textContent = 'Disconnecting...';
    
    // Send end call command to server
    fetch('api/end_call.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            deviceId: currentDevice.device_id
        })
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('intercomStatus').textContent = 'Ready';
        document.getElementById('callBtn').disabled = false;
        document.getElementById('hangupBtn').disabled = true;
        
        if (!data.success) {
            showResponseModal('warning', data.message || 'Warning: Call may not have ended properly');
        }
    })
    .catch(error => {
        document.getElementById('intercomStatus').textContent = 'Ready';
        document.getElementById('callBtn').disabled = false;
        document.getElementById('hangupBtn').disabled = true;
        showResponseModal('error', `Error ending call: ${error.message}`);
    });
}

// Device commands
function executeCommand(command) {
    if (!currentDevice) return;
    
    // Show loading modal
    showLoadingModal(`Executing command: ${command}...`);
    
    // Send command to server
    fetch('api/execute_command.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            deviceId: currentDevice.device_id,
            command: command
        })
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingModal();
        
        if (data.success) {
            showResponseModal('success', data.message || `Command ${command} executed successfully`);
        } else {
            showResponseModal('error', data.message || `Failed to execute command ${command}`);
        }
    })
    .catch(error => {
        hideLoadingModal();
        showResponseModal('error', `Error executing command: ${error.message}`);
    });
}

// Edit device functions
function editCurrentDevice() {
    if (!currentDevice) return;
    
    // Populate edit form
    document.getElementById('editDeviceId').value = currentDevice.id;
    document.getElementById('editDeviceDeviceId').value = currentDevice.device_id;
    document.getElementById('editDeviceName').value = currentDevice.device_name;
    document.getElementById('editDeviceIp').value = currentDevice.ip_address;
    document.getElementById('editDeviceMac').value = currentDevice.mac_address || '';
    document.getElementById('editDeviceUsername').value = currentDevice.username || 'admin';
    document.getElementById('editDevicePassword').value = '';
    
    // Show edit modal
    document.getElementById('editDeviceModal').classList.remove('hidden');
}

function closeEditDeviceModal() {
    document.getElementById('editDeviceModal').classList.add('hidden');
}

function updateDevice() {
    const deviceData = {
        id: document.getElementById('editDeviceId').value,
        device_id: document.getElementById('editDeviceDeviceId').value,
        device_name: document.getElementById('editDeviceName').value,
        ip_address: document.getElementById('editDeviceIp').value,
        mac_address: document.getElementById('editDeviceMac').value,
        username: document.getElementById('editDeviceUsername').value
    };
    
    // Only include password if it was changed
    const password = document.getElementById('editDevicePassword').value;
    if (password) {
        deviceData.password = password;
    }
    
    // Validate required fields
    if (!deviceData.device_name || !deviceData.ip_address) {
        showResponseModal('error', 'Device Name and IP Address are required');
        return;
    }
    
    // Show loading modal
    showLoadingModal('Updating device...');
    
    // Send data to server
    fetch('api/update_device.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(deviceData)
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingModal();
        
        if (data.success) {
            showResponseModal('success', 'Device updated successfully');
            closeEditDeviceModal();
            closeDeviceModal();
            loadDevices(); // Refresh the devices list
        } else {
            showResponseModal('error', data.message || 'Failed to update device');
        }
    })
    .catch(error => {
        hideLoadingModal();
        showResponseModal('error', 'Error updating device: ' + error.message);
    });
}

function deleteCurrentDevice() {
    if (!currentDevice) return;
    
    if (confirm(`Are you sure you want to delete this device? This action cannot be undone.`)) {
        // Show loading modal
        showLoadingModal('Deleting device...');
        
        // Send delete request to server
        fetch(`api/delete_device.php?id=${currentDevice.id}`, {
            method: 'DELETE'
        })
        .then(response => response.json())
        .then(data => {
            hideLoadingModal();
            
            if (data.success) {
                showResponseModal('success', 'Device deleted successfully');
                closeDeviceModal();
                loadDevices(); // Refresh the devices list
            } else {
                showResponseModal('error', data.message || 'Failed to delete device');
            }
        })
        .catch(error => {
            hideLoadingModal();
            showResponseModal('error', 'Error deleting device: ' + error.message);
        });
    }
} 