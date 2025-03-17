// Function to save the clock server port
async function saveClockServerPort() {
    const portInput = document.getElementById('clockServerPort');
    const port = parseInt(portInput.value);
    const accountNumber = document.getElementById('customer-account-number').textContent;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Validate port number
    if (isNaN(port) || port < 1024 || port > 65535) {
        showNotification('Please enter a valid port number between 1024 and 65535', 'error');
        return;
    }

    try {
        const response = await fetch('../api/update-clock-server-port.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                account_number: accountNumber,
                port: port,
                csrf_token: csrfToken
            })
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Clock server port updated successfully', 'success');
            updateServerStatus(port);
        } else {
            showNotification(data.message || 'Failed to update clock server port', 'error');
        }
    } catch (error) {
        console.error('Error updating clock server port:', error);
        showNotification('Failed to update clock server port', 'error');
    }
}

// Function to load the current port number
async function loadClockServerPort() {
    try {
        const accountNumber = document.getElementById('customer-account-number').textContent;
        const response = await fetch(`../api/get-clock-server-port.php?account_number=${accountNumber}`);
        const data = await response.json();

        if (data.success) {
            const portInput = document.getElementById('clockServerPort');
            portInput.value = data.port || '';
            updateServerStatus(data.port);
        }
    } catch (error) {
        console.error('Error loading clock server port:', error);
    }
}

// Function to update the server status indicator
async function updateServerStatus(port) {
    const statusIndicator = document.getElementById('server-status-indicator');
    const statusText = document.getElementById('server-status-text');
    
    if (!statusIndicator || !statusText) return;
    
    statusIndicator.className = 'status-indicator pending';
    statusText.textContent = 'Checking server status...';
    
    try {
        const response = await fetch(`../api/check-clock-server-status.php?port=${port}`);
        const data = await response.json();
        
        if (data.success && data.is_running) {
            statusIndicator.className = 'status-indicator online';
            statusText.textContent = `Server is running on port ${port}`;
        } else {
            statusIndicator.className = 'status-indicator offline';
            statusText.textContent = 'Server is not running';
        }
    } catch (error) {
        console.error('Error checking server status:', error);
        statusIndicator.className = 'status-indicator error';
        statusText.textContent = 'Error checking server status';
    }
}

// Add event listener to load port when customer modal opens
document.addEventListener('DOMContentLoaded', () => {
    const customerModal = document.getElementById('manage-customer-modal');
    if (customerModal) {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    if (customerModal.style.display === 'block') {
                        loadClockServerPort();
                    }
                }
            });
        });

        observer.observe(customerModal, { attributes: true });
    }
});

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const portInput = document.getElementById('clockServerPort');
    if (portInput) {
        portInput.addEventListener('change', saveClockServerPort);
    }
});

// Function to add a new clock machine
function addNewMachine() {
    // Show the add machine dialog
    showAddMachineModal();
}

// Function to show the add machine modal
function showAddMachineModal() {
    // Create modal content
    const modalContent = `
    <div id="add-machine-modal" class="custom-modal">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h3>Add New Device</h3>
                <button class="close-button" onclick="closeAddMachineModal()">×</button>
            </div>
            <div class="custom-modal-body">
                <form id="add-machine-form">
                    <div class="form-group">
                        <label for="deviceId">Device ID/Serial Number</label>
                        <input type="text" id="deviceId" name="deviceId" required>
                    </div>
                    <div class="form-group">
                        <label for="deviceName">Device Name</label>
                        <input type="text" id="deviceName" name="deviceName" required>
                    </div>
                    <div class="form-group">
                        <label for="deviceIp">IP Address</label>
                        <input type="text" id="deviceIp" name="deviceIp" required>
                    </div>
                    <div class="form-group">
                        <label for="deviceUsername">Username</label>
                        <input type="text" id="deviceUsername" name="deviceUsername" value="admin">
                    </div>
                    <div class="form-group">
                        <label for="devicePassword">Password</label>
                        <input type="password" id="devicePassword" name="devicePassword" value="12345">
                    </div>
                </form>
            </div>
            <div class="custom-modal-footer">
                <button class="button secondary" onclick="closeAddMachineModal()">Cancel</button>
                <button class="button primary" onclick="saveMachine()">Add Device</button>
            </div>
        </div>
    </div>`;
    
    // Add modal to the page
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalContent;
    document.body.appendChild(modalContainer.firstChild);
    
    // Show the modal
    const modal = document.getElementById('add-machine-modal');
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('show'), 10);
}

// Function to close the add machine modal
function closeAddMachineModal() {
    const modal = document.getElementById('add-machine-modal');
    if (!modal) return;
    
    modal.classList.remove('show');
    setTimeout(() => {
        modal.remove();
    }, 300);
}

// Function to save a new machine
async function saveMachine() {
    const accountNumber = document.getElementById('customer-account-number').textContent;
    
    // Get form values
    const deviceData = {
        device_id: document.getElementById('deviceId').value,
        device_name: document.getElementById('deviceName').value,
        ip_address: document.getElementById('deviceIp').value,
        username: document.getElementById('deviceUsername').value,
        password: document.getElementById('devicePassword').value
    };
    
    // Validate required fields
    if (!deviceData.device_id || !deviceData.device_name || !deviceData.ip_address) {
        showNotification('Device ID, Name, and IP Address are required', 'error');
        return;
    }
    
    try {
        // Show loading
        showLoadingModal('Adding device...');
        
        // Call the API
        const response = await fetch(`../modules/timeandatt/api/add_device.php?account=${accountNumber}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(deviceData)
        });
        
        const data = await response.json();
        
        // Hide loading
        hideLoadingModal();
        
        if (data.success) {
            // Show success message
            showNotification('Device added successfully', 'success');
            
            // Close modal
            closeAddMachineModal();
            
            // Refresh devices list
            const currentAccount = document.getElementById('customer-account-number').textContent;
            loadClockMachines(currentAccount);
        } else {
            showNotification(data.message || 'Failed to add device', 'error');
        }
    } catch (error) {
        hideLoadingModal();
        console.error('Error adding device:', error);
        showNotification('Failed to add device', 'error');
    }
}

// Enhanced function to display devices in the machines list
function updateMachinesList(machines) {
    const container = document.getElementById('machines-list');
    if (!container) return;

    if (machines.length === 0) {
        container.innerHTML = `
            <div class="no-machines">
                <p>No devices found for this customer.</p>
                <button class="button primary" onclick="addNewMachine()">
                    <i class="material-icons">add_circle</i>
                    Add First Device
                </button>
            </div>
        `;
        return;
    }

    container.innerHTML = '';
    machines.forEach(device => {
        const deviceDiv = document.createElement('div');
        deviceDiv.className = `machine-item ${device.status || 'offline'}`;
        
        // Format last online date
        const lastOnline = device.last_online ? new Date(device.last_online).toLocaleString() : 'Never';
        
        deviceDiv.innerHTML = `
            <div class="machine-header">
                <span class="machine-name">${escapeHtml(device.device_name || device.name || 'Unnamed Device')}</span>
                <span class="status-badge ${device.status || 'offline'}">${device.status || 'offline'}</span>
            </div>
            <div class="machine-info">
                <div class="info-item">
                    <span class="info-label">ID:</span>
                    <span class="info-value">${escapeHtml(device.device_id || device.serial_number || 'Unknown')}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">IP:</span>
                    <span class="info-value">${escapeHtml(device.ip_address || 'Unknown')}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Last Online:</span>
                    <span class="info-value">${lastOnline}</span>
                </div>
            </div>
            <div class="machine-actions">
                <button class="icon-button" onclick="viewMachineDetails('${device.device_id || device.id}')" title="View Details">
                    <i class="material-icons">visibility</i>
                </button>
                <button class="icon-button" onclick="editMachine('${device.device_id || device.id}')" title="Edit Device">
                    <i class="material-icons">edit</i>
                </button>
                <button class="icon-button" onclick="controlMachineDoor('${device.device_id || device.id}')" title="Control Door">
                    <i class="material-icons">meeting_room</i>
                </button>
                <button class="icon-button danger" onclick="confirmDeleteMachine('${device.device_id || device.id}')" title="Delete Device">
                    <i class="material-icons">delete</i>
                </button>
            </div>
        `;
        container.appendChild(deviceDiv);
    });
}

// Function to control a door
function controlMachineDoor(deviceId) {
    // Show control door modal
    showDoorControlModal(deviceId);
}

// Function to show door control modal
function showDoorControlModal(deviceId) {
    // Create modal content
    const modalContent = `
    <div id="door-control-modal" class="custom-modal">
        <div class="custom-modal-content">
            <div class="custom-modal-header">
                <h3>Door Control</h3>
                <button class="close-button" onclick="closeDoorControlModal()">×</button>
            </div>
            <div class="custom-modal-body">
                <div class="door-control-buttons">
                    <button class="button primary" onclick="executeDoorAction('${deviceId}', 'unlock')">
                        <i class="material-icons">lock_open</i>
                        Unlock Door
                    </button>
                    <button class="button secondary" onclick="executeDoorAction('${deviceId}', 'lock')">
                        <i class="material-icons">lock</i>
                        Lock Door
                    </button>
                    <div class="hold-control">
                        <button class="button primary" onclick="executeDoorAction('${deviceId}', 'hold', document.getElementById('holdDuration').value)">
                            <i class="material-icons">schedule</i>
                            Hold Open
                        </button>
                        <select id="holdDuration" class="select-duration">
                            <option value="10">10 seconds</option>
                            <option value="30">30 seconds</option>
                            <option value="60" selected>1 minute</option>
                            <option value="300">5 minutes</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>`;
    
    // Add modal to the page
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalContent;
    document.body.appendChild(modalContainer.firstChild);
    
    // Show the modal
    const modal = document.getElementById('door-control-modal');
    modal.style.display = 'block';
    setTimeout(() => modal.classList.add('show'), 10);
}

// Function to close door control modal
function closeDoorControlModal() {
    const modal = document.getElementById('door-control-modal');
    if (!modal) return;
    
    modal.classList.remove('show');
    setTimeout(() => {
        modal.remove();
    }, 300);
}

// Function to execute a door action
async function executeDoorAction(deviceId, action, duration = null) {
    const accountNumber = document.getElementById('customer-account-number').textContent;
    
    try {
        // Show loading
        showLoadingModal(`${action.charAt(0).toUpperCase() + action.slice(1)}ing door...`);
        
        // Prepare request data
        const data = {
            deviceId: deviceId,
            action: action
        };
        
        if (duration) {
            data.duration = duration;
        }
        
        // Call the API
        const response = await fetch(`../modules/timeandatt/api/control_door.php?account=${accountNumber}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(data)
        });
        
        const responseData = await response.json();
        
        // Hide loading
        hideLoadingModal();
        
        if (responseData.success) {
            // Show success message
            showNotification(`Door ${action} successful`, 'success');
            
            // Close modal
            closeDoorControlModal();
        } else {
            showNotification(responseData.message || `Failed to ${action} door`, 'error');
        }
    } catch (error) {
        hideLoadingModal();
        console.error(`Error ${action}ing door:`, error);
        showNotification(`Failed to ${action} door`, 'error');
    }
}

// Function to view machine details
function viewMachineDetails(deviceId) {
    // Implementation for viewing machine details
    console.log('View machine details for device:', deviceId);
    // This would be expanded in a full implementation
}

// Function to edit a machine
function editMachine(deviceId) {
    // Implementation for editing a machine
    console.log('Edit machine:', deviceId);
    // This would be expanded in a full implementation
}

// Function to confirm delete a machine
function confirmDeleteMachine(deviceId) {
    if (confirm(`Are you sure you want to delete this device? This action cannot be undone.`)) {
        deleteMachine(deviceId);
    }
}

// Function to delete a machine
async function deleteMachine(deviceId) {
    const accountNumber = document.getElementById('customer-account-number').textContent;
    
    try {
        // Show loading
        showLoadingModal('Deleting device...');
        
        // Call the API
        const response = await fetch(`../modules/timeandatt/api/delete_device.php?account=${accountNumber}&device_id=${deviceId}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            }
        });
        
        const data = await response.json();
        
        // Hide loading
        hideLoadingModal();
        
        if (data.success) {
            // Show success message
            showNotification('Device deleted successfully', 'success');
            
            // Refresh devices list
            loadClockMachines(accountNumber);
        } else {
            showNotification(data.message || 'Failed to delete device', 'error');
        }
    } catch (error) {
        hideLoadingModal();
        console.error('Error deleting device:', error);
        showNotification('Failed to delete device', 'error');
    }
}

// Helper function to safely escape HTML
function escapeHtml(str) {
    if (!str) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

// Scan for devices on network
function scanForDevices() {
    const accountNumber = document.getElementById('customer-account-number').textContent;
    showLoadingModal('Scanning network for devices...');
    
    // This would be connected to a real network scanner in production
    setTimeout(() => {
        hideLoadingModal();
        showNotification('Network scan complete', 'success');
        // In a real implementation, this would find devices and add them to the list
    }, 2000);
} 