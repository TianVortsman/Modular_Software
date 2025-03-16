// Modal Elements
const clientDevicesModal = document.getElementById('client-devices-modal');
const addCustomerModal = document.getElementById('add-customer-modal');
const manageCustomerModal = document.getElementById('manage-customer-modal');

let currentCustomerId = null;
let currentCustomerData = null;

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
    manageCustomerModal.classList.add('active');
    fetchCustomerDetails(customerId);
}

function closeManageCustomerModal() {
    manageCustomerModal.classList.remove('active');
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
    // Close button listeners
    const closeButtons = document.querySelectorAll('.close-button');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.classList.remove('active');
                if (modal === manageCustomerModal) {
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
            if (event.target === manageCustomerModal) {
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

// Customer Data Management
function fetchCustomerDetails(customerId) {
    if (!customerId) {
        console.error('No customer ID provided to fetchCustomerDetails');
        return;
    }

    // Show loading state
    const modalContent = manageCustomerModal.querySelector('.modal-content');
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
    if (!data || !data.customer) {
        console.error('Invalid customer data received');
        showToast('Error loading customer data', 'error');
        return;
    }

    const customer = data.customer;
    
    // Update modal title and account number
    document.getElementById('customer-name-title').textContent = customer.company_name;
    document.getElementById('customer-account-number').textContent = `Account: ${customer.account_number}`;

    // Store customer data for other functions
    currentCustomerData = customer;

    // Load data for each tab
    loadUsersData(customer.id);
    loadModulesData(customer.id);
    loadClockMachines(customer.id);
    loadAccountSettings(data);
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
function loadClockMachines(customerId) {
    if (!customerId) {
        console.error('No customer ID provided to loadClockMachines');
        return;
    }

    fetch(`../php/get-customer-machines.php?customer_id=${customerId}`)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            if (!data.success) throw new Error(data.error || 'Failed to load machines');
            
            // Update machines list and statistics
            updateMachinesList(data.machines);
            updateMachinesStatistics(data.statistics);
        })
        .catch(error => {
            console.error('Error loading machines:', error);
            showToast('Failed to load machines data', 'error');
        });
}

function updateMachinesList(machines) {
    const container = document.getElementById('machines-list');
    if (!container) return;

    container.innerHTML = '';
    machines.forEach(machine => {
        const machineDiv = document.createElement('div');
        machineDiv.className = `machine-item ${machine.status}`;
        machineDiv.innerHTML = `
            <div class="machine-info">
                <span class="machine-name">${escapeHtml(machine.name)}</span>
                <span class="machine-type">${machine.type}</span>
                <span class="machine-location">${machine.location}</span>
            </div>
            <div class="machine-status">
                <span class="status-badge ${machine.status}">${machine.status}</span>
                <span class="last-sync">Last sync: ${formatDate(machine.last_sync)}</span>
            </div>
        `;
        container.appendChild(machineDiv);
    });
}

function updateMachinesStatistics(statistics) {
    const statsContainer = document.getElementById('machines-statistics');
    if (!statsContainer) return;

    statsContainer.innerHTML = `
        <div class="stat-item">
            <span class="stat-label">Total Machines</span>
            <span class="stat-value">${statistics.total_machines}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Active</span>
            <span class="stat-value">${statistics.active_machines}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Inactive</span>
            <span class="stat-value">${statistics.inactive_machines}</span>
        </div>
        <div class="stat-item">
            <span class="stat-label">Last Sync</span>
            <span class="stat-value">${formatDate(statistics.last_sync_time)}</span>
        </div>
    `;
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

// Toast notification function
function showToast(message, type = 'info') {
    // Create toast container if it doesn't exist
    let toastContainer = document.getElementById('toast-container');
    if (!toastContainer) {
        toastContainer = document.createElement('div');
        toastContainer.id = 'toast-container';
        document.body.appendChild(toastContainer);
    }

    // Create toast element
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;

    // Add toast to container
    toastContainer.appendChild(toast);

    // Remove toast after animation
    setTimeout(() => {
        toast.classList.add('fade-out');
        setTimeout(() => {
            toast.remove();
            if (toastContainer.children.length === 0) {
                toastContainer.remove();
            }
        }, 300);
    }, 3000);
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