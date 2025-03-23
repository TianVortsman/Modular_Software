// Modal Elements
const clientDevicesModal = document.getElementById('client-devices-modal');
const addCustomerModal = document.getElementById('add-customer-modal');
const customerModal = document.getElementById('customerModal');

let currentCustomerId = null;
let currentCustomerData = null;
let currentPage = 1;
let itemsPerPage = 10;
let totalPages = 0;
let currentSortColumn = 'company_name';
let currentSortDirection = 'asc';
let searchTimeout = null; // Add debounce timeout variable

// Global variables at the top of the file
const SEARCH_DEBOUNCE_DELAY = 300; // 300ms debounce for search

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
    // Fetch and populate devices data will be implemented later
    showToast('Device management will be implemented later', 'info');
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
    if (customerModal) {
        closeCustomerModal();
    }
    currentCustomerId = null;
    currentCustomerData = null;
}

function loginAsTechnician() {
    if (!currentCustomerData || !currentCustomerData.account_number) {
        showToast('Error: Unable to login as technician - customer data missing', 'error');
        return;
    }
    
    const accountNumber = currentCustomerData.account_number;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    
    // Show loading state
    showLoadingModal('Logging in as technician...');
    
    // Prepare form data
    const formData = new FormData();
    formData.append('account_number', accountNumber);
    formData.append('csrf_token', csrfToken);
    
    // Make API request
    fetch('../../src/api/tech_login.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Redirect to the provided URL
            window.location.href = data.redirect;
        } else {
            showToast(`Error: ${data.error || 'Unknown error'}`, 'error');
        }
    })
    .catch(error => {
        console.error('Login failed:', error);
        showToast(`Login failed: ${error.message}`, 'error');
    })
    .finally(() => {
        hideLoadingModal();
    });
}

// Initialize modal event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Initialize customers data
    fetchCustomerData();

    // Add click handlers for all tab buttons
    const tabButtons = document.querySelectorAll('.tab-button');
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            switchTab(tabId);
        });
    });

    // Add event listener for the clock server toggle button
    document.getElementById('toggle-server')?.addEventListener('click', toggleServer);

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
    
    // Initialize pagination buttons
    setupPaginationEventListeners();

    // Add double-click event listener for customer table
    document.getElementById('customer-table').addEventListener('dblclick', function(event) {
        // Find the closest tr element (customer row)
        const row = event.target.closest('tr');
        if (row && row.dataset.customerId) {
            openManageCustomerModal(row.dataset.customerId);
        }
    });

    // Initialize search input with debounce
    const searchInput = document.getElementById('search-bar');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            // Clear any existing timeout
            if (searchTimeout) {
                clearTimeout(searchTimeout);
            }
            
            // Set a new timeout
            searchTimeout = setTimeout(() => {
                searchCustomers();
            }, SEARCH_DEBOUNCE_DELAY);
        });
    }
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

    fetch(`../../src/api/customer.php?action=details&id=${customerId}`)
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
    if (customerData.customer_id) {
        loadUsersData(customerData.customer_id);
        loadModulesData(customerData.customer_id);
    }
    
    loadAccountSettings(data);
}

// Users Management
function loadUsersData(customerId) {
    if (!customerId) {
        console.error('No customer ID provided to loadUsersData');
        return;
    }

    fetch(`../../src/api/customer.php?action=users&customer_id=${customerId}`)
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

    fetch(`../../src/api/customer.php?action=modules&customer_id=${customerId}`)
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
    
    // Check and update clock server status if account number exists
    if (data.customer?.account_number) {
        updateServerStatus(data.customer.account_number);
    }
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

// Clock Server Functions
function updateServerStatus(accountNumber) {
    const serverStatus = document.getElementById('server-status');
    const toggleButton = document.getElementById('toggle-server');
    const toggleText = document.getElementById('toggle-text');
    
    if (!serverStatus || !toggleButton || !toggleText) {
        console.error('Server status elements not found');
        return;
    }
    
    // Show loading state
    serverStatus.textContent = 'Checking...';
    serverStatus.className = 'status-badge pending';
    toggleText.textContent = 'Loading...';
    toggleButton.disabled = true;
    
    // Store account number on button for toggle action
    toggleButton.dataset.account = accountNumber;
    
    // Fetch current status
    const formData = new FormData();
    formData.append('action', 'status');
    formData.append('account', accountNumber);
    
    fetch('techlogin.php', {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.error) {
            throw new Error(data.error);
        }
        
        const isRunning = data && data.running;
        
        // Update status display
        serverStatus.textContent = isRunning ? '🟢 Running' : '🔴 Stopped';
        serverStatus.className = `status-badge ${isRunning ? 'active' : 'inactive'}`;
        
        // Update button text
        toggleText.textContent = isRunning ? 'Stop Server' : 'Start Server';
        toggleButton.disabled = false;
    })
    .catch(error => {
        console.error('Error fetching server status:', error);
        serverStatus.textContent = '⚠️ Connection Error';
        serverStatus.className = 'status-badge error';
        toggleText.textContent = 'Retry';
        toggleButton.disabled = false;
        
        // Show meaningful error message
        showToast(`Clock server connection error: ${error.message}. Make sure the Docker container is running.`, 'error');
    });
}

function toggleServer() {
    const toggleButton = document.getElementById('toggle-server');
    const accountNumber = toggleButton.dataset.account;
    
    if (!accountNumber) {
        showToast('Error: Account number not found', 'error');
        return;
    }
    
    // Determine action based on current button text
    const action = document.getElementById('toggle-text').textContent.includes('Start') ? 'start' : 'stop';
    
    // Disable button while processing
    toggleButton.disabled = true;
    
    // Show loading state
    const serverStatus = document.getElementById('server-status');
    serverStatus.textContent = action === 'start' ? 'Starting...' : 'Stopping...';
    serverStatus.className = 'status-badge pending';
    
    // Send request
    const formData = new FormData();
    formData.append('action', action);
    formData.append('account', accountNumber);
    
    fetch('techlogin.php', {
        method: 'POST',
        body: formData,
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        // Safely try to parse the JSON, with error handling
        return response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Error parsing JSON:', text);
                throw new Error('Invalid response format');
            }
        });
    })
    .then(data => {
        if (data && data.success) {
            showToast(`Server successfully ${action === 'start' ? 'started' : 'stopped'}`, 'success');
        } else {
            throw new Error(data?.error || `Failed to ${action} server`);
        }
        
        // Update status after action
        setTimeout(() => {
            updateServerStatus(accountNumber);
        }, 1000); // Short delay before checking status again
    })
    .catch(error => {
        console.error(`Error ${action}ing server:`, error);
        showToast(`Failed to ${action} server: ${error.message}`, 'error');
        
        // Update UI to show error state
        serverStatus.textContent = '⚠️ Connection Error';
        serverStatus.className = 'status-badge error';
        toggleText.textContent = action === 'start' ? 'Start Server' : 'Stop Server';
        toggleButton.disabled = false;
    });
}

// Storage Management
function loadStorageData(customerId) {
    console.log('Storage data will be implemented later');
    showToast('Storage management will be implemented later', 'info');
}

// Helper Functions
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
function sortByColumn(column) {
    const icons = document.querySelectorAll('.sort-icon');
    icons.forEach(icon => icon.textContent = 'unfold_more');

    if (currentSortColumn === column) {
        currentSortDirection = currentSortDirection === 'asc' ? 'desc' : 'asc';
    } else {
        currentSortColumn = column;
        currentSortDirection = 'asc';
    }

    const icon = document.querySelector(`th[onclick="sortByColumn('${column}')"] .sort-icon`);
    icon.textContent = currentSortDirection === 'asc' ? 'arrow_upward' : 'arrow_downward';

    fetchCustomerData(); // Re-fetch data with new sort parameters
}

// Device Discovery
function startDeviceDiscovery() {
    showToast('Device discovery will be implemented later', 'info');
}

// Export Function
function exportCustomerData() {
    showToast('Export functionality will be implemented later', 'info');
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

// Search function for customer search bar
function searchCustomers() {
    const searchTerm = document.getElementById('search-bar').value.trim();
    fetchCustomerData(searchTerm);
}

// Customer List Management
function fetchCustomerData(searchTerm = '') {
    // Only show the loading modal on initial page load, not during search
    const initialLoad = !searchTerm && currentPage === 1;
    
    if (initialLoad && typeof showLoadingModal === 'function') {
        showLoadingModal('Loading customers...');
    } else {
        // For search or pagination, only show loading indicator in the table
        const tableBody = document.getElementById('customer-body');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr>
                    <td colspan="8" class="loading-data">
                        <div class="spinner-container">
                            <div class="spinner"></div>
                            <span>Loading...</span>
                        </div>
                    </td>
                </tr>
            `;
        }
    }
    
    const url = new URL('../../src/api/customer.php', window.location.href);
    url.searchParams.append('action', 'list');
    url.searchParams.append('page', currentPage);
    url.searchParams.append('per_page', itemsPerPage);
    url.searchParams.append('sort_by', currentSortColumn);
    url.searchParams.append('sort_direction', currentSortDirection);
    
    if (searchTerm) {
        url.searchParams.append('search', searchTerm);
    }
    
    console.log('Fetching customers from URL:', url.toString());
    
    fetch(url)
        .then(response => {
            if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
            return response.json();
        })
        .then(data => {
            console.log('Customer data received:', data);
            
            if (!data.success) throw new Error(data.error || 'Failed to load customers');
            
            displayCustomers(data.customers);
            updatePagination(data.pagination);
        })
        .catch(error => {
            console.error('Error loading customers:', error);
            showToast('Failed to load customer data: ' + error.message, 'error');
            
            // Display empty state
            const tbody = document.getElementById('customer-body');
            if (tbody) {
                tbody.innerHTML = `<tr><td colspan="8" class="no-data">Error loading customers: ${error.message}</td></tr>`;
            }
            
            // Reset pagination
            document.getElementById('showing-start').textContent = '0';
            document.getElementById('showing-end').textContent = '0';
            document.getElementById('total-entries').textContent = '0';
            document.getElementById('page-numbers').innerHTML = '';
        })
        .finally(() => {
            if (initialLoad && typeof hideLoadingModal === 'function') {
                hideLoadingModal();
            }
        });
}

function displayCustomers(customers) {
    const tableBody = document.getElementById('customer-body');
    if (!tableBody) {
        console.error('Customer table body not found');
        return;
    }

    // Update customer count
    const customerCount = document.querySelector('.customer-count');
    if (customerCount) {
        customerCount.textContent = customers.length === 1 
            ? '1 customer' 
            : `${customers.length} customers`;
    }

    // Clear existing rows
    tableBody.innerHTML = '';

    if (!customers || customers.length === 0) {
        const noDataRow = document.createElement('tr');
        noDataRow.innerHTML = `
            <td colspan="8" class="no-data">No customers found. Try a different search term.</td>
        `;
        tableBody.appendChild(noDataRow);
        return;
    }

    // Populate table with customer data
    customers.forEach(customer => {
        const row = document.createElement('tr');
        row.className = 'customer-row';
        row.dataset.customerId = customer.customer_id; // Add customer_id to the row for double-click

        // Format last login date if it exists
        const lastLogin = customer.last_login ? formatDate(customer.last_login) : 'Never';

        // Determine status class
        const statusClass = (customer.status || 'inactive').toLowerCase();

        row.innerHTML = `
            <td>${customer.company_name || 'Unknown'}</td>
            <td>${customer.company_name || 'Unknown'}</td>
            <td>${customer.email || 'No email'}</td>
            <td>${customer.account_number || 'No account'}</td>
            <td>
                <div class="device-stats">
                    <span class="device-total">${customer.user_count || 0}</span>
                    <span class="device-active">${customer.active_users || 0} active</span>
                </div>
            </td>
            <td>
                <span class="status-badge ${statusClass}">${customer.status || 'Inactive'}</span>
            </td>
            <td>${lastLogin}</td>
            <td>
                <div class="action-buttons">
                    <button class="icon-button" onclick="openManageCustomerModal(${customer.customer_id})" title="View Details">
                        <i class="material-icons">visibility</i>
                    </button>
                    <button class="icon-button" onclick="editCustomer(${customer.customer_id})" title="Edit">
                        <i class="material-icons">edit</i>
                    </button>
                    <button class="icon-button" onclick="manageDevices(${customer.customer_id})" title="Manage Devices">
                        <i class="material-icons">devices</i>
                    </button>
                    <button class="icon-button danger" onclick="deleteCustomer(${customer.customer_id})" title="Delete">
                        <i class="material-icons">delete</i>
                    </button>
                </div>
            </td>
        `;

        tableBody.appendChild(row);
    });
}

// Pagination Functions
function updatePagination(pagination) {
    if (!pagination) {
        console.error('No pagination data provided');
        return;
    }
    
    console.log('Updating pagination with:', pagination);
    
    // Update global variables
    currentPage = pagination.current_page;
    totalPages = pagination.total_pages;
    
    // Update showing info
    document.getElementById('showing-start').textContent = pagination.showing.start;
    document.getElementById('showing-end').textContent = pagination.showing.end;
    document.getElementById('total-entries').textContent = pagination.showing.total;
    
    // Generate page numbers
    const pageNumbers = document.getElementById('page-numbers');
    pageNumbers.innerHTML = '';
    
    if (totalPages === 0) {
        return; // No pages to display
    }
    
    let startPage = Math.max(1, currentPage - 2);
    let endPage = Math.min(totalPages, startPage + 4);
    
    if (endPage - startPage < 4) {
        startPage = Math.max(1, endPage - 4);
    }
    
    for (let i = startPage; i <= endPage; i++) {
        const button = document.createElement('button');
        button.className = `pagination-button${i === currentPage ? ' active' : ''}`;
        button.textContent = i;
        button.onclick = () => goToPage(i);
        pageNumbers.appendChild(button);
    }
}

function setupPaginationEventListeners() {
    document.getElementById('rows-per-page').addEventListener('change', function() {
        itemsPerPage = parseInt(this.value);
        currentPage = 1;
        fetchCustomerData();
    });
}

function goToPage(page) {
    if (page < 1 || page > totalPages) return;
    currentPage = page;
    fetchCustomerData();
}

function goToFirstPage() {
    goToPage(1);
}

function goToPreviousPage() {
    goToPage(currentPage - 1);
}

function goToNextPage() {
    goToPage(currentPage + 1);
}

function goToLastPage() {
    goToPage(totalPages);
}

// User Management Functions - Stubs for future implementation
function editUser(userId) {
    console.log(`Editing user with ID: ${userId}`);
    showToast('User edit functionality not yet implemented', 'info');
}

function resetPassword(userId) {
    console.log(`Resetting password for user with ID: ${userId}`);
    showToast('Password reset functionality not yet implemented', 'info');
}

function deleteUser(userId) {
    console.log(`Deleting user with ID: ${userId}`);
    showToast('User deletion functionality not yet implemented', 'warning');
}

// Customer Management Functions - Stubs for future implementation
function editCustomer(customerId) {
    console.log(`Editing customer with ID: ${customerId}`);
    showToast('Customer edit functionality not yet implemented', 'info');
}

function deleteCustomer(customerId) {
    console.log(`Deleting customer with ID: ${customerId}`);
    showToast('Customer deletion functionality not yet implemented', 'warning');
}

// Module Management Functions - Stubs for future implementation
function toggleModule(moduleName, currentStatus) {
    const newStatus = currentStatus === 'active' ? 'inactive' : 'active';
    console.log(`Toggling module ${moduleName} from ${currentStatus} to ${newStatus}`);
    showToast(`${moduleName} module is now ${newStatus}`, 'info');
}

function saveModuleSettings() {
    console.log('Saving module settings');
    showToast('Module settings saved', 'success');
}