// Modal Elements
const clientDevicesModal = document.getElementById('client-devices-modal');
const addCustomerModal = document.getElementById('add-customer-modal');
const customerModal = document.getElementById('customerModal');

let currentCustomerId = null;
let currentCustomerData = null;

// Global variables at the top of the file
let deviceRefreshInterval = null;
const DEVICE_REFRESH_INTERVAL = 10000; // 10 seconds

// Modal Functions
function openAddCustomerModal() {
    addCustomerModal.classList.add('active');
}

function closeAddCustomerModal() {
    addCustomerModal.classList.remove('active');
}

function manageDevices(customerId) {
    if (!customerId) return;
    clientDevicesModal.classList.add('active');
    // Fetch and populate devices data
    fetchCustomerDevices(customerId);
}

function closeClientDevicesModal() {
    clientDevicesModal.classList.remove('active');
}

function openManageCustomerModal(customerId) {
    if (!customerId) {
        console.error('No customer ID provided');
        showToast('Error: Unable to open customer details', 'error');
        return;
    }
    currentCustomerId = customerId;
    const modal = document.getElementById('customerModal');
    if (modal) {
        modal.style.display = 'flex';
        fetchCustomerDetails(customerId);
    } else {
        console.error('Customer modal not found');
        showToast('Error: Unable to open customer details', 'error');
    }
}

function closeManageCustomerModal() {
    const modal = document.getElementById('customerModal');
    if (modal) {
        closeCustomerModal();
    }
    currentCustomerId = null;
    currentCustomerData = null;
}

function loginAsCustomer() {
    if (currentCustomerData && currentCustomerData.account_number) {
        window.location.href = `dashboard.php?account_number=${currentCustomerData.account_number}`;
    } else {
        showToast('Error: Unable to login as customer', 'error');
    }
}

// Initialize modal event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            switchTab(tabId);
        });
    });

    // Close button listeners
    const closeButtons = document.querySelectorAll('.close-button');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
                if (modal === customerModal) {
                    currentCustomerId = null;
                    currentCustomerData = null;
                }
            }
        });
    });

    // Close on outside click
    window.addEventListener('click', function(event) {
        if (event.target.classList.contains('modal')) {
            event.target.classList.remove('active');
            if (event.target === customerModal) {
                currentCustomerId = null;
                currentCustomerData = null;
            }
        }
    });

    // Prevent modal content clicks from closing the modal
    const modalContents = document.querySelectorAll('.modal-content');
    modalContents.forEach(content => {
        content.addEventListener('click', function(event) {
            event.stopPropagation();
        });
    });
});

// Function to switch tabs
function switchTab(tabId) {
    // Remove active class from all tabs and buttons
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });

    // Add active class to selected tab and button
    const selectedTab = document.getElementById(tabId);
    const selectedButton = document.querySelector(`[data-tab="${tabId}"]`);
    
    if (selectedTab) selectedTab.classList.add('active');
    if (selectedButton) selectedButton.classList.add('active');

    // If switching to clock machines tab, load the machines
    if (tabId === 'clock-machines') {
        const modal = document.getElementById('customerModal');
        if (modal && modal.dataset.accountNumber) {
            loadClockMachines(modal.dataset.accountNumber);
        }
    }
}

// Customer Data Management
function fetchCustomerDetails(customerId) {
    if (!customerId) {
        console.error('No customer ID provided to fetchCustomerDetails');
        return;
    }

    // Show loading state
    const modalContent = customerModal.querySelector('.modal-content');
    if (modalContent) modalContent.classList.add('loading');

    fetch(`../php/get-customer-details.php?id=${customerId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (!data) {
                throw new Error('No data received from server');
            }
            currentCustomerData = data;
            updateCustomerModalContent(data);
        })
        .catch(error => {
            console.error('Error fetching customer details:', error);
            showToast('Failed to fetch customer details. Please try again.', 'error');
            closeManageCustomerModal();
        })
        .finally(() => {
            if (modalContent) modalContent.classList.remove('loading');
        });
}

function updateCustomerModalContent(data) {
    if (!data || !data.success || !data.customer || !data.customer.account_number) {
        console.error('Invalid customer data received:', data);
        showToast('Error: Missing or invalid customer data', 'error');
        return;
    }

    const customerData = data.customer;
    const modal = document.getElementById('customerModal');
    if (!modal) {
        console.error('Customer modal not found');
        return;
    }

    // Store the account number in the modal's dataset
    modal.dataset.accountNumber = customerData.account_number;

    // Update modal title and account number display
    document.getElementById('customer-name-title').textContent = customerData.company_name || 'Customer Details';
    document.getElementById('customer-account-number').textContent = `Account: ${customerData.account_number}`;

    // Update form fields with available data
    const fields = {
        'customerName': customerData.name || customerData.company_name || '',
        'customerEmail': customerData.email || '',
        'customerPhone': customerData.phone || ''
    };

    // Update each field if the element exists
    Object.entries(fields).forEach(([elementId, value]) => {
        const element = document.getElementById(elementId);
        if (element) {
            element.value = value;
        }
    });

    // Store customer data for other functions
    currentCustomerData = customerData;

    // Load data for each tab
    if (customerData.id) {
        loadUsersData(customerData.id);
        loadModulesData(customerData.id);
    }
    
    loadAccountSettings(data);

    // Load clock machines using the account number
    loadClockMachines(customerData.account_number);
}

// Users Management
function loadUsersData(customerId) {
    if (!customerId) {
        console.error('No customer ID provided to loadUsersData');
        return;
    }

    fetch(`../php/get-customer-users.php?customer_id=${customerId}`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (!data.success) throw new Error(data.error || 'Failed to load users');
            
            const tbody = document.getElementById('users-table-body');
            tbody.innerHTML = '';

            data.users.forEach(user => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td>${escapeHtml(user.name)}</td>
                    <td>${escapeHtml(user.email)}</td>
                    <td>${escapeHtml(user.role)}</td>
                    <td>${formatDate(user.last_login)}</td>
                    <td><span class="status-badge ${user.status.toLowerCase()}">${user.status}</span></td>
                    <td>
                        <div class="action-buttons">
                            <button class="icon-button" onclick="editUser(${user.id})" title="Edit User">
                                <i class="material-icons">edit</i>
                            </button>
                            <button class="icon-button" onclick="resetPassword(${user.id})" title="Reset Password">
                                <i class="material-icons">lock_reset</i>
                            </button>
                            <button class="icon-button danger" onclick="deleteUser(${user.id})" title="Delete User">
                                <i class="material-icons">delete</i>
                            </button>
                        </div>
                    </td>
                `;
                tbody.appendChild(row);
            });
        })
        .catch(error => {
            console.error('Error loading users:', error);
            showToast('Failed to load users data', 'error');
        });
}

// Modules Management
function loadModulesData(customerId) {
    if (!customerId) {
        console.error('No customer ID provided to loadModulesData');
        return;
    }

    fetch(`../php/get-customer-modules.php?customer_id=${customerId}`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (!data.success) throw new Error(data.error || 'Failed to load modules');
            
            updateModulesList('core-modules', data.core_modules);
            updateModulesList('additional-features', data.additional_features);
            updateModulesList('mobile-features', data.mobile_features);
        })
        .catch(error => {
            console.error('Error loading modules:', error);
            showToast('Failed to load modules data', 'error');
        });
}

function updateModulesList(containerId, modules) {
    const container = document.getElementById(containerId);
    if (!container) return;

    container.innerHTML = '';
    modules.forEach(module => {
        const moduleDiv = document.createElement('div');
        moduleDiv.className = `module-item ${module.status}`;
        moduleDiv.innerHTML = `
            <span class="module-name">${escapeHtml(module.name)}</span>
            <span class="module-status">${module.status}</span>
            <div class="module-actions">
                <button class="icon-button" onclick="toggleModule('${module.name}', '${module.status}')">
                    <i class="material-icons">${module.status === 'active' ? 'toggle_on' : 'toggle_off'}</i>
                </button>
            </div>
        `;
        container.appendChild(moduleDiv);
    });
}

// Clock Machines Management
function loadClockMachines(accountNumber) {
    if (!accountNumber) {
        console.error('No account number provided to loadClockMachines');
        showResponseModal('error', 'Missing account number');
        return;
    }

    // Make sure we're using the functions defined in loading-modal.php
    if (typeof showLoadingModal !== 'function') {
        console.error('showLoadingModal function not found');
        return;
    }

    showLoadingModal('Loading clock machines...');
    
    // Track both fetch operations
    let portFetchComplete = false;
    let devicesComplete = false;
    
    function checkAndHideLoading() {
        if (portFetchComplete && devicesComplete) {
            // Use the global hideLoadingModal function
            if (typeof hideLoadingModal === 'function') {
                hideLoadingModal();
            } else {
                console.error('hideLoadingModal function not found');
                // Fallback to direct manipulation
                const modal = document.getElementById('unique-loading-modal');
                if (modal) {
                    modal.style.opacity = 0;
                    setTimeout(() => {
                        modal.classList.add('hidden');
                        modal.style.display = 'none';
                    }, 300);
                }
            }
        }
    }

    // Load the clock server port
    fetch(`../api/get-clock-server-port.php?account_number=${accountNumber}`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (data.success) {
                const portDisplay = document.getElementById('clockServerPort');
                if (portDisplay) {
                    portDisplay.textContent = data.port || 'Not configured';
                    updateServerStatus(data.port);
                } else {
                    console.error('Port display element not found');
                }
            } else {
                throw new Error(data.error || 'Failed to load port');
            }
        })
        .catch(error => {
            console.error('Error loading clock server port:', error);
            showResponseModal('error', 'Failed to load clock server port');
        })
        .finally(() => {
            portFetchComplete = true;
            checkAndHideLoading();
        });
        
    // Load devices for devices table
    const devicesTable = document.getElementById('devicesTableBody');
    const loadingIndicator = document.getElementById('devicesLoading');
    const noDevicesMessage = document.getElementById('noDevicesMessage');
    
    if (devicesTable && loadingIndicator && noDevicesMessage) {
        // Show loading, hide table and no devices message
        loadingIndicator.classList.remove('hidden');
        noDevicesMessage.classList.add('hidden');
        
        // Call the get_customer_devices API to get devices for this account
        fetch(`../techlogin/api/get_customer_devices.php?account_number=${accountNumber}`)
            .then(response => response.json())
            .then(data => {
                loadingIndicator.classList.add('hidden');
                
                if (data.success && data.devices && data.devices.length > 0) {
                    // Clear table body
                    devicesTable.innerHTML = '';
                    
                    // Populate table with devices
                    data.devices.forEach(device => {
                        const row = document.createElement('tr');
                        
                        // Format last online date
                        const lastOnline = device.last_online 
                            ? new Date(device.last_online).toLocaleString() 
                            : 'Never';
                        
                        // Determine status class
                        const statusClass = device.status === 'online' 
                            ? 'status-online' 
                            : 'status-offline';
                        
                        row.innerHTML = `
                            <td>${escapeHtml(device.device_id || device.serial_number || '')}</td>
                            <td>${escapeHtml(device.device_name || '')}</td>
                            <td>${escapeHtml(device.ip_address || '')}</td>
                            <td><span class="status-badge ${statusClass}">${device.status || 'offline'}</span></td>
                            <td>${lastOnline}</td>
                            <td>
                                <div class="action-buttons">
                                    <button class="icon-button" onclick="viewMachineDetails('${device.device_id}')" title="View Details">
                                        <i class="material-icons">visibility</i>
                                    </button>
                                    <button class="icon-button" onclick="editMachine('${device.device_id}')" title="Edit Device">
                                        <i class="material-icons">edit</i>
                                    </button>
                                    <button class="icon-button" onclick="controlMachineDoor('${device.device_id}')" title="Control Door">
                                        <i class="material-icons">meeting_room</i>
                                    </button>
                                    <button class="icon-button danger" onclick="confirmDeleteMachine('${device.device_id}')" title="Delete Device">
                                        <i class="material-icons">delete</i>
                                    </button>
                                </div>
                            </td>
                        `;
                        
                        devicesTable.appendChild(row);
                    });
                    
                    // Show table
                    document.getElementById('devicesTable').classList.remove('hidden');
                    
                    // Setup auto-refresh
                    setupDeviceRefresh(accountNumber);
                } else {
                    // Show no devices message
                    noDevicesMessage.classList.remove('hidden');
                }
            })
            .catch(error => {
                console.error('Error loading devices:', error);
                loadingIndicator.classList.add('hidden');
                noDevicesMessage.classList.remove('hidden');
                noDevicesMessage.innerHTML = `
                    <p>Error loading devices: ${error.message}</p>
                    <button class="btn btn-primary" onclick="loadClockMachines('${accountNumber}')">
                        <i class="material-icons">refresh</i> Retry
                    </button>
                `;
            })
            .finally(() => {
                devicesComplete = true;
                checkAndHideLoading();
            });
    } else {
        devicesComplete = true;
        checkAndHideLoading();
    }

    // Clear any existing refresh interval
    clearDeviceRefresh();
}

// Account Settings Management
function loadAccountSettings(data) {
    if (!data) {
        console.error('No data provided to loadAccountSettings');
        return;
    }

    // Populate form fields with existing data or empty strings
    document.getElementById('company_name').value = data.customer?.company_name || '';
    
    // Define dropdown options
    const dropdownOptions = {
        industry: ['Manufacturing', 'Technology', 'Healthcare', 'Retail', 'Education', 'Other'],
        timezone: ['UTC', 'UTC+1', 'UTC+2', 'UTC+3', 'UTC-1', 'UTC-2'],
        billing_cycle: ['Monthly', 'Quarterly', 'Annually'],
        payment_method: ['Credit Card', 'Bank Transfer', 'Direct Debit'],
        support_level: ['Basic', 'Standard', 'Premium'],
        maintenance_window: ['00:00-04:00', '04:00-08:00', '20:00-24:00']
    };

    // Load dropdowns with predefined options
    Object.keys(dropdownOptions).forEach(key => {
        loadDropdownOptions(key, data.customer?.[key], dropdownOptions[key]);
    });
}

function loadDropdownOptions(selectId, selectedValue, options) {
    const select = document.getElementById(selectId);
    if (!select) {
        console.warn(`Select element with id '${selectId}' not found`);
        return;
    }

    // Clear existing options
    select.innerHTML = '';

    // Add default empty option
    const defaultOption = document.createElement('option');
    defaultOption.value = '';
    defaultOption.textContent = `Select ${selectId.replace('_', ' ')}...`;
    select.appendChild(defaultOption);

    // Add provided options
    options.forEach(option => {
        const optionElement = document.createElement('option');
        optionElement.value = option.toLowerCase().replace(/\s+/g, '_');
        optionElement.textContent = option;
        if (selectedValue && optionElement.value === selectedValue.toLowerCase()) {
            optionElement.selected = true;
        }
        select.appendChild(optionElement);
    });
}

// Storage Management
function loadStorageData(customerId) {
    fetch(`../php/get-storage-stats.php?customer_id=${customerId}`)
        .then(response => response.json())
        .then(data => {
            // Update storage progress bars
            updateStorageProgress('storage-usage', data.storage_used, data.storage_limit);
            updateStorageProgress('database-usage', data.database_size, data.database_limit);
            
            // Update details text
            document.getElementById('storage-details').textContent = 
                `${formatSize(data.storage_used)} of ${formatSize(data.storage_limit)} used`;
            document.getElementById('database-details').textContent = 
                `${formatSize(data.database_size)} of ${formatSize(data.database_limit)} used`;

            // Set archive settings
            document.getElementById('archive_age').value = data.archive_age || '3';
            document.getElementById('archive_location').value = data.archive_location || 'local';
        });
}

// Helper Functions
function updateStorageProgress(elementId, used, total) {
    const percentage = (used / total) * 100;
    const progressElement = document.getElementById(elementId);
    progressElement.style.width = `${percentage}%`;
    progressElement.className = `progress ${percentage > 90 ? 'danger' : percentage > 70 ? 'warning' : ''}`;
}

function formatSize(bytes) {
    const sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
    if (bytes === 0) return '0 Byte';
    const i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
}

// Tab Functions
function openCustomerTab(event, tabName) {
    const tabContents = document.getElementsByClassName('tab-content');
    const tabButtons = document.getElementsByClassName('tab-button');

    Array.from(tabContents).forEach(tab => tab.classList.remove('active'));
    Array.from(tabButtons).forEach(button => button.classList.remove('active'));

    document.getElementById(tabName).classList.add('active');
    event.currentTarget.classList.add('active');
}

// Sort Functions
let currentSort = { column: '', direction: 'asc' };

function sortByColumn(column) {
    const icons = document.querySelectorAll('.sort-icon');
    icons.forEach(icon => icon.textContent = 'unfold_more');

    if (currentSort.column === column) {
        currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
    } else {
        currentSort.column = column;
        currentSort.direction = 'asc';
    }

    const icon = document.querySelector(`th[onclick="sortByColumn('${column}')"] .sort-icon`);
    icon.textContent = currentSort.direction === 'asc' ? 'arrow_upward' : 'arrow_downward';

    fetchCustomerData(); // This will re-fetch data with new sort parameters
}

// Device Discovery
function startDeviceDiscovery() {
    // Implement device discovery logic
    console.log('Starting device discovery...');
    // Add your device discovery implementation here
}

// Export Function
function exportCustomerData() {
    // Implement export functionality
    console.log('Exporting customer data...');
    // Add your export implementation here
}

// Initialize tooltips if you're using them
document.addEventListener('DOMContentLoaded', function() {
    // Add any initialization code here
});

// Function to show toast notifications
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.querySelector('.toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.className = 'toast-container';
        document.body.appendChild(toastContainer);
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast toast-${type}`;
    toast.innerHTML = `
        <div class="toast-content">
            <i class="material-icons toast-icon">${getIconForType(type)}</i>
            <span class="toast-message">${message}</span>
        </div>
        <button class="toast-close">
            <i class="material-icons">close</i>
        </button>
    `;
    
    // Add close functionality
    toast.querySelector('.toast-close').addEventListener('click', () => {
        toast.classList.add('toast-hiding');
        setTimeout(() => {
            toast.remove();
        }, 300);
    });
    
    // Add to container
    toastContainer.appendChild(toast);
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (toast.parentNode) {
            toast.classList.add('toast-hiding');
            setTimeout(() => {
                if (toast.parentNode) {
                    toast.remove();
                }
            }, 300);
        }
    }, 5000);
}

// Helper function to get icon for toast type
function getIconForType(type) {
    switch (type) {
        case 'success':
            return 'check_circle';
        case 'error':
            return 'error';
        case 'warning':
            return 'warning';
        case 'info':
        default:
            return 'info';
    }
}

function escapeHtml(str) {
    if (!str) return '';
    const div = document.createElement('div');
    div.textContent = str;
    return div.innerHTML;
}

function formatDate(dateStr) {
    if (!dateStr) return 'Never';
    try {
        const date = new Date(dateStr);
        return date.toLocaleString();
    } catch (e) {
        return dateStr;
    }
}

// Function to handle tab switching
function initializeTabSwitching() {
    document.querySelectorAll('.tab-button').forEach(button => {
        button.addEventListener('click', (e) => {
            const tabId = e.target.closest('.tab-button').dataset.tab;
            
            // Remove active class from all tabs and content
            document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));
            
            // Add active class to clicked tab and corresponding content
            e.target.closest('.tab-button').classList.add('active');
            document.getElementById(tabId).classList.add('active');
        });
    });
}

// Function to close the customer modal with animation
function closeCustomerModal() {
    const modal = document.getElementById('customerModal');
    if (modal) {
        modal.classList.add('closing');
        modal.classList.remove('active');
        
        // Restore body scrolling
        document.body.style.overflow = '';
        
        setTimeout(() => {
            modal.style.display = 'none';
            modal.classList.remove('closing');
            
            // Clear device refresh interval
            clearDeviceRefresh();
        }, 300); // Match the animation duration
    }
}

// Initialize when document is ready
document.addEventListener('DOMContentLoaded', () => {
    initializeTabSwitching();
    
    // Add click event listener to close modal when clicking outside
    const modal = document.getElementById('customerModal');
    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                closeCustomerModal();
            }
        });
    }
});

// Function to save clock server port
async function saveClockServerPort() {
    const modal = document.getElementById('customerModal');
    const portInput = document.getElementById('clockServerPort');
    const accountNumber = modal?.dataset?.accountNumber;
    
    if (!portInput || !accountNumber) {
        showResponseModal('error', 'Missing required information');
        return;
    }
    
    const port = parseInt(portInput.value);
    if (isNaN(port) || port < 1024 || port > 65535) {
        showResponseModal('error', 'Invalid port number. Must be between 1024 and 65535');
        return;
    }
    
    // Make sure we're using the functions defined in loading-modal.php
    if (typeof showLoadingModal !== 'function') {
        console.error('showLoadingModal function not found');
        showResponseModal('error', 'Internal error: loading modal function not found');
        return;
    }
    
    showLoadingModal('Saving port...');
    
    try {
        const response = await fetch('../api/update-clock-server-port.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                account_number: accountNumber,
                port: port
            })
        });
        
        if (!response.ok) throw new Error('Failed to update port');
        
        const data = await response.json();
        
        if (data.success) {
            showResponseModal('success', 'Port updated successfully');
            updateServerStatus(port);
        } else {
            throw new Error(data.error || 'Failed to update port');
        }
    } catch (error) {
        console.error('Error updating port:', error);
        showResponseModal('error', error.message || 'Failed to update port');
    } finally {
        // Use the global hideLoadingModal function
        if (typeof hideLoadingModal === 'function') {
            hideLoadingModal();
        } else {
            console.error('hideLoadingModal function not found');
            // Fallback to direct manipulation
            const loadingModal = document.getElementById('unique-loading-modal');
            if (loadingModal) {
                loadingModal.style.opacity = 0;
                setTimeout(() => {
                    loadingModal.classList.add('hidden');
                    loadingModal.style.display = 'none';
                    loadingModal.style.visibility = 'hidden';
                }, 300);
            }
        }
    }
}

// Function to update server status
async function updateServerStatus(port) {
    if (!port) return;
    
    const statusIndicator = document.getElementById('server-status-indicator');
    const statusText = document.getElementById('server-status-text');
    
    if (!statusIndicator || !statusText) return;
    
    // Set to checking state
    statusIndicator.className = 'status-indicator checking';
    statusText.textContent = 'Checking server status...';
    
    try {
        const response = await fetch(`../api/check-clock-server-status.php?port=${port}`);
        if (!response.ok) throw new Error('Failed to check server status');
        
        const data = await response.json();
        
        if (data.success) {
            if (data.is_running) {
                statusIndicator.className = 'status-indicator running';
                statusText.textContent = 'Server is running';
            } else {
                statusIndicator.className = 'status-indicator stopped';
                statusText.textContent = 'Server is not running';
            }
        } else {
            throw new Error(data.error || 'Error checking server status');
        }
    } catch (error) {
        console.error('Error checking server status:', error);
        statusIndicator.className = 'status-indicator error';
        statusText.textContent = 'Error checking server status';
    }
}

// Tab switching functionality
document.addEventListener('DOMContentLoaded', function() {
    // Add click handlers for all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            switchTab(tabId);
        });
    });
});

function switchTab(tabId) {
    // Remove active class from all tabs and buttons
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    document.querySelectorAll('.tab-button').forEach(button => {
        button.classList.remove('active');
    });

    // Add active class to selected tab and button
    const selectedTab = document.getElementById(tabId);
    const selectedButton = document.querySelector(`[data-tab="${tabId}"]`);
    
    if (selectedTab) selectedTab.classList.add('active');
    if (selectedButton) selectedButton.classList.add('active');

    // If switching to clock machines tab, load the machines
    if (tabId === 'clock-machines') {
        const modal = document.getElementById('customerModal');
        if (modal && modal.dataset.accountNumber) {
            loadClockMachines(modal.dataset.accountNumber);
        }
    }
}

// Modal management
function openCustomerModal(customerId) {
    const modal = document.getElementById('customerModal');
    if (modal) {
        modal.style.display = 'flex';
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeCustomerModal() {
    const modal = document.getElementById('customerModal');
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
        document.body.style.overflow = '';
    }
}

// Search function for customer search bar
function searchCustomers() {
    const searchTerm = document.getElementById('search-bar').value.trim();
    if (window.fetchCustomerData) {
        currentPage = 1; // Reset to first page when searching
        window.fetchCustomerData(searchTerm);
    } else {
        console.error('fetchCustomerData function not available');
        showToast('Search functionality not available', 'error');
    }
}

// Add these new functions to handle the periodic refresh
function setupDeviceRefresh(accountNumber) {
    // Clear any existing interval first
    clearDeviceRefresh();
    
    // Setup new refresh interval
    deviceRefreshInterval = setInterval(() => {
        refreshDevicesData(accountNumber);
    }, DEVICE_REFRESH_INTERVAL);
    
    // Add event listener to clear interval when tab is closed
    document.addEventListener('visibilitychange', handleVisibilityChange);
}

function clearDeviceRefresh() {
    if (deviceRefreshInterval) {
        clearInterval(deviceRefreshInterval);
        deviceRefreshInterval = null;
    }
}

function handleVisibilityChange() {
    if (document.hidden) {
        // Tab is hidden, clear refresh to save resources
        clearDeviceRefresh();
    } else {
        // Tab is visible again, setup refresh if modal is still open
        const modal = document.getElementById('customerModal');
        if (modal && modal.style.display !== 'none' && modal.classList.contains('active')) {
            const accountNumber = modal.dataset.accountNumber;
            if (accountNumber) {
                setupDeviceRefresh(accountNumber);
                // Immediately refresh data
                refreshDevicesData(accountNumber);
            }
        }
    }
}

function refreshDevicesData(accountNumber) {
    const devicesTable = document.getElementById('devicesTableBody');
    if (!devicesTable || !accountNumber) return;
    
    // Quiet refresh - no loading indicators
    fetch(`../techlogin/api/get_customer_devices.php?account_number=${accountNumber}`)
        .then(response => response.json())
        .then(data => {
            if (data.success && data.devices && data.devices.length > 0) {
                // Clear table body
                devicesTable.innerHTML = '';
                
                // Populate table with devices
                data.devices.forEach(device => {
                    const row = document.createElement('tr');
                    
                    // Format last online date
                    const lastOnline = device.last_online 
                        ? new Date(device.last_online).toLocaleString() 
                        : 'Never';
                    
                    // Determine status class
                    const statusClass = device.status === 'online' 
                        ? 'status-online' 
                        : 'status-offline';
                    
                    row.innerHTML = `
                        <td>${escapeHtml(device.device_id || device.serial_number || '')}</td>
                        <td>${escapeHtml(device.device_name || '')}</td>
                        <td>${escapeHtml(device.ip_address || '')}</td>
                        <td><span class="status-badge ${statusClass}">${device.status || 'offline'}</span></td>
                        <td>${lastOnline}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="icon-button" onclick="viewMachineDetails('${device.device_id}')" title="View Details">
                                    <i class="material-icons">visibility</i>
                                </button>
                                <button class="icon-button" onclick="editMachine('${device.device_id}')" title="Edit Device">
                                    <i class="material-icons">edit</i>
                                </button>
                                <button class="icon-button" onclick="controlMachineDoor('${device.device_id}')" title="Control Door">
                                    <i class="material-icons">meeting_room</i>
                                </button>
                                <button class="icon-button danger" onclick="confirmDeleteMachine('${device.device_id}')" title="Delete Device">
                                    <i class="material-icons">delete</i>
                                </button>
                            </div>
                        </td>
                    `;
                    
                    devicesTable.appendChild(row);
                });
                
                // Show table
                document.getElementById('devicesTable').classList.remove('hidden');
            }
        })
        .catch(error => {
            console.error('Error refreshing devices:', error);
        });
}