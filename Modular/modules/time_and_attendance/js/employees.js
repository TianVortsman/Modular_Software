// Function to initialize employee overview modal functionality
function initEmployeeOverviewModal() {
    // Get the employee overview widget
    const employeeOverviewWidget = document.getElementById('employee-count-widget');
    
    // Add double-click event listener to the widget
    if (employeeOverviewWidget) {
        employeeOverviewWidget.addEventListener('dblclick', function() {
            // Show the employee overview modal
            openEmployeeOverviewModal();
        });
    }
    
    // Close modal when clicking the close button
    const closeButtons = document.querySelectorAll('.modal-close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            document.getElementById('employee-overview-modal').classList.remove('show');
        });
    });
    
    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('employee-overview-modal');
        if (event.target === modal) {
            modal.classList.remove('show');
        }
    });
}

// Function to open the employee overview modal with detailed information
function openEmployeeOverviewModal() {
    const modal = document.getElementById('employee-overview-modal');
    modal.classList.add('show');
    
    // You can add code here to fetch and display real data
    updateEmployeeOverviewStats();
}

// Function to update the employee overview statistics in the modal
function updateEmployeeOverviewStats() {
    // This would typically fetch data from an API
    // For now, we'll use the same data as displayed in the widget
    const totalEmployees = document.getElementById('total-employees').textContent;
    const licenseLimit = document.getElementById('license-limit').textContent;
    const licenseUsage = document.getElementById('license-usage').textContent;
    
    // Update the modal with this information
    document.getElementById('modal-total-employees').textContent = totalEmployees;
    document.getElementById('modal-license-limit').textContent = licenseLimit;
    document.getElementById('modal-license-usage').textContent = licenseUsage;
    
    // Set the progress bar width
    const progressBar = document.querySelector('#employee-overview-modal .progress');
    if (progressBar) {
        progressBar.style.width = licenseUsage;
    }
}

class TabManager {
    constructor(options) {
        this.containerSelector = options.containerSelector;
        this.buttonSelector = options.buttonSelector;
        this.paneSelector = options.paneSelector;
        this.activeClass = options.activeClass || 'active';
        this.dataAttribute = options.dataAttribute;
        
        this.init();
    }

    init() {
        const container = document.querySelector(this.containerSelector);
        if (!container) return;

        container.addEventListener('click', (e) => {
            const button = e.target.closest(this.buttonSelector);
            if (!button) return;

            this.switchTab(button);
        });
    }

    switchTab(selectedButton) {
        const container = selectedButton.closest(this.containerSelector);
        const tabId = selectedButton.getAttribute(this.dataAttribute);
        
        // Update buttons
        container.querySelectorAll(this.buttonSelector).forEach(button => {
            button.classList.remove(this.activeClass);
            button.setAttribute('aria-selected', 'false');
        });
        selectedButton.classList.add(this.activeClass);
        selectedButton.setAttribute('aria-selected', 'true');
        
        // Update panes
        container.querySelectorAll(this.paneSelector).forEach(pane => {
            pane.classList.remove(this.activeClass);
        });
        const targetPane = document.getElementById(`${tabId}-tab`);
        if (targetPane) {
            targetPane.classList.add(this.activeClass);
        }
    }
}

// Initialize page level tabs
const pageTabManager = new TabManager({
    containerSelector: '.page-tabs-container',
    buttonSelector: '.page-tab-button',
    paneSelector: '.page-tab-pane',
    dataAttribute: 'data-tab'
});

// Initialize employee modal tabs
const modalTabManager = new TabManager({
    containerSelector: '.employee-details-tabs',
    buttonSelector: '.emp-details-tab-button',
    paneSelector: '.emp-details-tab-pane',
    dataAttribute: 'data-modal-tab'
});

// SubTabManager class for handling nested tabs
class SubTabManager {
    constructor(options) {
        this.containerSelector = options.containerSelector;
        this.buttonSelector = options.buttonSelector;
        this.paneSelector = options.paneSelector;
        this.activeClass = options.activeClass || 'active';
        this.dataAttribute = options.dataAttribute;
        
        this.init();
    }

    init() {
        const container = document.querySelector(this.containerSelector);
        if (!container) return;

        container.addEventListener('click', (e) => {
            const button = e.target.closest(this.buttonSelector);
            if (!button) return;

            this.switchSubTab(button);
        });
    }

    switchSubTab(selectedButton) {
        const container = selectedButton.closest(this.containerSelector);
        const tabId = selectedButton.getAttribute(this.dataAttribute);
        
        // Update buttons
        container.querySelectorAll(this.buttonSelector).forEach(button => {
            button.classList.remove(this.activeClass);
            button.setAttribute('aria-selected', 'false');
        });
        selectedButton.classList.add(this.activeClass);
        selectedButton.setAttribute('aria-selected', 'true');
        
        // Update panes
        const allPanes = document.querySelectorAll(this.paneSelector);
        allPanes.forEach(pane => {
            pane.classList.remove(this.activeClass);
        });
        const targetPane = document.getElementById(`${tabId}-tab`);
        if (targetPane) {
            targetPane.classList.add(this.activeClass);
        }
    }
}

// Initialize sub-tabs for permanent/temporary employees
const employeeSubTabManager = new SubTabManager({
    containerSelector: '#active-tab',
    buttonSelector: '.page-subtab-button',
    paneSelector: '.page-subtab-pane',
    dataAttribute: 'data-subtab'
});

// Employee Modal Manager
class EmployeeModalManager {
    constructor() {
        this.modal = document.getElementById('employee-details-modal');
        this.init();
    }

    init() {
        if (!this.modal) return;
        
        // Modal open/close handlers
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-action="open-employee-modal"]')) {
                this.openModal(e.target.dataset.employeeId);
            }
            if (e.target.matches('.modal-close, .btn-cancel')) {
                this.closeModal();
            }
        });

        // Close modal when clicking outside
        this.modal.addEventListener('click', (e) => {
            if (e.target === this.modal) {
                this.closeModal();
            }
        });

        // Handle form submission
        const saveButton = this.modal.querySelector('.btn-save');
        if (saveButton) {
            saveButton.addEventListener('click', () => this.saveEmployeeData());
        }

        // Initialize device management
        this.initDeviceManagement();
    }

    openModal(employeeId) {
        const firstTab = this.modal.querySelector('.emp-details-tab-button');
        if (firstTab) {
            modalTabManager.switchTab(firstTab);
        }
        
        this.modal.style.display = 'flex';
        if (employeeId) {
            this.fetchEmployeeData(employeeId);
        }
    }

    closeModal() {
        if (this.modal) {
            this.modal.style.display = 'none';
        }
    }

    initDeviceManagement() {
        const deviceList = this.modal.querySelector('.device-list');
        const addDeviceBtn = this.modal.querySelector('.add-device-btn');

        if (deviceList && addDeviceBtn) {
            // Add device functionality
            addDeviceBtn.addEventListener('click', () => {
                const deviceItem = document.createElement('div');
                deviceItem.className = 'device-item';
                deviceItem.innerHTML = `
                    <span>New Device</span>
                    <button class="remove-device">Remove</button>
                `;
                deviceList.appendChild(deviceItem);
            });

            // Remove device functionality
            deviceList.addEventListener('click', (e) => {
                if (e.target.matches('.remove-device')) {
                    e.target.closest('.device-item').remove();
                }
            });
        }
    }

    saveEmployeeData() {
        const formData = this.collectFormData();
        console.log('Saving employee data:', formData);
        
        // Simulate API call
        setTimeout(() => {
            alert('Employee details saved successfully!');
            this.closeModal();
        }, 500);
    }

    collectFormData() {
        return {
            personalDetails: {
                fullName: document.getElementById('employee-full-name').textContent,
                dob: document.getElementById('employee-dob').value,
                email: document.getElementById('employee-email').value,
                phone: document.getElementById('employee-phone').value,
                address: {
                    line1: document.getElementById('employee-address1').value,
                    line2: document.getElementById('employee-address2').value,
                    city: document.getElementById('employee-city').value,
                    state: document.getElementById('employee-state').value,
                    postal: document.getElementById('employee-postal').value,
                    country: document.getElementById('employee-country').value
                }
            },
            employmentDetails: {
                payrollNumber: document.getElementById('employee-payroll-number').textContent,
                clockNumber: document.getElementById('employee-clock-number').textContent,
                jobTitle: document.getElementById('employee-job-title').value,
                department: document.getElementById('employee-department').value,
                location: document.getElementById('employee-location').value
            },
            mobileSettings: {
                gpsTracking: document.getElementById('gps-tracking').checked,
                biometricAuth: document.getElementById('biometric-auth').checked,
                manualEntry: document.getElementById('manual-entry').checked,
                offlineMode: document.getElementById('offline-mode').checked,
                photoVerification: document.getElementById('photo-verification').checked,
                geofencingType: document.getElementById('geofencing-type').value
            }
        };
    }

    async fetchEmployeeData(employeeId) {
        console.log(`Fetching data for employee ID: ${employeeId}`);
        try {
            const response = await fetch(`../api/employee-details.php?id=${employeeId}`);
            if (!response.ok) throw new Error('Failed to fetch employee data');
            
            const result = await response.json();
            if (!result.success) throw new Error(result.message || 'Failed to fetch employee data');
            
            const employee = result.data;
            
            // Basic info
            document.getElementById('employee-full-name').textContent = `${employee.first_name} ${employee.last_name}`;
            document.getElementById('employee-payroll-number').textContent = employee.employee_number;
            document.getElementById('employee-badge-number').textContent = employee.badge_number || 'Not Set';
            document.getElementById('employee-gender').value = employee.gender || 'male';
            
            // Personal Details
            document.getElementById('employee-dob').value = employee.date_of_birth || '';
            document.getElementById('employee-email').value = employee.email || '';
            document.getElementById('employee-phone').value = employee.phone_number || '';
            document.getElementById('employee-address1').value = employee.address_line1 || '';
            document.getElementById('employee-address2').value = employee.address_line2 || '';
            document.getElementById('employee-city').value = employee.city || '';
            document.getElementById('employee-state').value = employee.state || '';
            document.getElementById('employee-postal').value = employee.postal_code || '';
            document.getElementById('employee-country').value = employee.country || '';
            
            // Emergency Contact
            document.getElementById('emergency-name').value = employee.emergency_contact_name || '';
            document.getElementById('emergency-relation').value = employee.emergency_contact_relation || '';
            document.getElementById('emergency-phone').value = employee.emergency_contact_phone || '';
            document.getElementById('emergency-email').value = employee.emergency_contact_email || '';
            
            // Organization
            document.getElementById('employee-division').value = employee.division_id || '';
            document.getElementById('employee-department').value = employee.department_id || '';
            document.getElementById('employee-group').value = employee.employee_group || '';
            document.getElementById('employee-cost-centre').value = employee.cost_centre || '';
            document.getElementById('employee-location').value = employee.location_id || '';
            document.getElementById('employee-team').value = employee.team_id || '';
            document.getElementById('employee-manager').value = employee.manager_id || '';
            
            // Employment Details
            document.getElementById('employee-job-title').value = employee.position_name || '';
            document.getElementById('employee-hire-date').value = employee.hire_date || '';
            document.getElementById('employee-contract-type').value = employee.contract_type || 'permanent';
            document.getElementById('employee-status').value = employee.status || 'active';
            
            // Update status badge
            const statusBadge = document.querySelector('.status-badge');
            if (statusBadge) {
                statusBadge.className = `status-badge ${employee.status || 'active'}`;
                statusBadge.textContent = employee.status || 'Active';
            }
            
            // Leave Balances
            if (employee.leave_balances) {
                employee.leave_balances.forEach(balance => {
                    const input = document.getElementById(`${balance.leave_type.toLowerCase()}-leave`);
                    if (input) input.value = balance.balance;
                });
            }
            
            // Leave History
            const leaveHistoryBody = document.querySelector('#leave-tab .employee-table tbody');
            if (leaveHistoryBody && employee.leave_history) {
                leaveHistoryBody.innerHTML = employee.leave_history.map(leave => `
                    <tr>
                        <td>${leave.leave_type}</td>
                        <td>${new Date(leave.start_date).toLocaleDateString()}</td>
                        <td>${new Date(leave.end_date).toLocaleDateString()}</td>
                        <td>${Math.ceil((new Date(leave.end_date) - new Date(leave.start_date)) / (1000 * 60 * 60 * 24))} days</td>
                        <td><span class="status-badge status-${leave.status.toLowerCase()}">${leave.status}</span></td>
                    </tr>
                `).join('');
            }
            
            // Devices
            const deviceList = document.querySelector('.device-list');
            if (deviceList && employee.devices) {
                deviceList.innerHTML = employee.devices.map(device => `
                    <div class="device-item">
                        <span>${device.device_name} (${device.device_type})</span>
                        <button class="remove-device">Remove</button>
                    </div>
                `).join('');
            }
            
            // Mobile Settings
            if (employee.mobile_settings) {
                document.getElementById('gps-tracking').checked = employee.mobile_settings.gps_tracking;
                document.getElementById('biometric-auth').checked = employee.mobile_settings.biometric_auth;
                document.getElementById('manual-entry').checked = employee.mobile_settings.manual_entry;
                document.getElementById('offline-mode').checked = employee.mobile_settings.offline_mode;
                document.getElementById('photo-verification').checked = employee.mobile_settings.photo_verification;
                document.getElementById('geofencing-type').value = employee.mobile_settings.geofencing_type || 'none';
            }
            
            // Set the first tab as active
            const firstTab = document.querySelector('.emp-details-tab-button');
            if (firstTab) {
                modalTabManager.switchTab(firstTab);
            }
            
        } catch (error) {
            console.error('Error fetching employee data:', error);
            this.showNotification('Failed to load employee details', 'error');
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    initEmployeeOverviewModal();
    new EmployeeModalManager();
});

// Employee Management Functions
async function loadEmployees() {
    try {
        const response = await fetch('../api/employees.php');
        if (!response.ok) {
            throw new Error('Failed to load employees');
        }
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Failed to load employees');
        }
        displayEmployees(result.data);
    } catch (error) {
        console.error('Error loading employees:', error);
        showNotification('Failed to load employees', 'error');
    }
}

function displayEmployees(employees) {
    const tables = {
        active: document.querySelector('#permanent-tab .main-employee-table tbody'),
        temporary: document.querySelector('#temporary-tab .main-employee-table tbody'),
        terminated: document.querySelector('#terminated-tab .main-employee-table tbody'),
        incomplete: document.querySelector('#incomplete-tab .main-employee-table tbody'),
        all: document.querySelector('#all-tab .main-employee-table tbody')
    };

    // Clear existing rows
    Object.values(tables).forEach(table => {
        if (table) table.innerHTML = '';
    });

    // Populate tables
    employees.forEach(employee => {
                const row = createEmployeeRow(employee);
        
        // Add to appropriate tables based on status and employment type
        if (employee.status === 'active') {
            if (employee.employment_type === 'Temporary' && tables.temporary) {
                const clonedRow = row.cloneNode(true);
                addRowEventListeners(clonedRow, employee);
                tables.temporary.appendChild(clonedRow);
            } else if (tables.active) {
                const clonedRow = row.cloneNode(true);
                addRowEventListeners(clonedRow, employee);
                tables.active.appendChild(clonedRow);
            }
        } else if (employee.status === 'terminated' && tables.terminated) {
            const clonedRow = row.cloneNode(true);
            addRowEventListeners(clonedRow, employee);
            tables.terminated.appendChild(clonedRow);
        } else if (employee.status === 'incomplete' && tables.incomplete) {
            const clonedRow = row.cloneNode(true);
            addRowEventListeners(clonedRow, employee);
            tables.incomplete.appendChild(clonedRow);
        }
        
        // Always add to the "all" table
        if (tables.all) {
            const clonedRow = row.cloneNode(true);
            addRowEventListeners(clonedRow, employee);
            tables.all.appendChild(clonedRow);
        }
    });

    // Update employee statistics
    updateEmployeeStats(employees);
}

function addRowEventListeners(row, employee) {
    row.addEventListener('dblclick', () => {
        console.log('Double-clicked employee row:', employee.employee_id);
        openEmployeeModal(employee.employee_id);
    });
}

function createEmployeeRow(employee) {
    const row = document.createElement('tr');
    row.className = 'employee-row';
    row.dataset.employeeId = employee.employee_id;
    
    const hireDate = employee.hire_date ? new Date(employee.hire_date).toLocaleDateString() : '';
    
    row.innerHTML = `
        <td>${employee.employee_number || ''}</td>
        <td>${employee.first_name} ${employee.last_name}</td>
        <td>${employee.department || ''}</td>
        <td>${employee.position || ''}</td>
        <td>${hireDate}</td>
        <td><span class="status-badge ${employee.status || 'active'}">${employee.status || 'Active'}</span></td>
    `;
    
    return row;
}

// Add Employee Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const addEmployeeBtn = document.getElementById('addEmployeeBtn');
    const addEmployeeModal = document.getElementById('add-employee-modal');
    const addEmployeeForm = document.getElementById('addEmployeeForm');
    const cancelBtn = addEmployeeModal.querySelector('.cancel-btn');
    const closeBtn = addEmployeeModal.querySelector('.modal-close');

    // Hide modal
    function hideModal() {
        addEmployeeModal.style.display = 'none';
        addEmployeeForm.reset();
    }

    if (closeBtn) closeBtn.addEventListener('click', hideModal);
    if (cancelBtn) cancelBtn.addEventListener('click', hideModal);

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === addEmployeeModal) {
            hideModal();
        }
    });

    // Handle form submission
    if (addEmployeeForm) {
    addEmployeeForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(addEmployeeForm);
        const employeeData = {
            employee_number: formData.get('employeeNumber'),
            clock_number: formData.get('clockNumber'),
            first_name: formData.get('firstName'),
            last_name: formData.get('lastName'),
            csrf_token: formData.get('csrf_token')
        };

        try {
            const response = await fetch('../api/employees.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(employeeData)
            });

            if (!response.ok) {
                    const errorData = await response.json();
                    throw new Error(errorData.message || 'Failed to add employee');
            }

            const result = await response.json();
            if (result.success) {
                // Refresh employee list
                loadEmployees();
                // Hide modal
                hideModal();
                // Show success message
                showNotification('Employee added successfully', 'success');
            } else {
                throw new Error(result.message || 'Failed to add employee');
            }
        } catch (error) {
            console.error('Error adding employee:', error);
            showNotification(error.message, 'error');
        }
    });
    }
});

// Function to open the Add Employee modal
function openAddEmployeeModal() {
    const modal = document.getElementById('add-employee-modal');
    if (modal) {
        modal.style.display = 'flex';
    }
}

// Utility Functions
function showNotification(message, type = 'info') {
    const modalResponse = document.getElementById('modalResponse');
    if (modalResponse) {
        const title = document.getElementById('modalResponseTitle');
        const icon = document.getElementById('modalResponseIcon');
        const msg = document.getElementById('modalResponseMessage');

        msg.innerText = message;

        const statusConfig = {
            success: { title: "Success!", icon: "✔", color: "var(--color-primary)" },
            error: { title: "Error!", icon: "✖", color: "#F44336" },
            warning: { title: "Warning!", icon: "⚠", color: "#FFC107" },
            info: { title: "Info", icon: "ℹ", color: "#2196F3" }
        };

        if (statusConfig[type]) {
            title.innerText = statusConfig[type].title;
            icon.innerHTML = statusConfig[type].icon;
            icon.style.color = statusConfig[type].color;
        }

        modalResponse.classList.remove('hidden');
        } else {
        console.log(`${type}: ${message}`);
    }
}

// Initialize the page
document.addEventListener('DOMContentLoaded', () => {
    loadEmployees();
    initEmployeeOverviewModal();
});

function updateEmployeeStats(employees) {
    // Count employees by status
    const stats = employees.reduce((acc, emp) => {
        if (emp.status === 'active') {
            if (emp.employment_type === 'Temporary') {
                acc.temporary++;
            } else {
                acc.permanent++;
            }
        } else if (emp.status === 'terminated') {
            acc.terminated++;
        } else if (emp.status === 'incomplete') {
            acc.incomplete++;
        }
        return acc;
    }, { permanent: 0, temporary: 0, terminated: 0, incomplete: 0 });

    // Update total employees count
    const totalEmployees = employees.length;
    const totalEmployeesElement = document.getElementById('total-employees');
    if (totalEmployeesElement) {
        totalEmployeesElement.textContent = totalEmployees;
    }

    // Calculate and update license usage
    const licenseLimitElement = document.getElementById('license-limit');
    const licenseLimit = licenseLimitElement ? parseInt(licenseLimitElement.textContent) || 300 : 300;
    const usagePercentage = Math.round((totalEmployees / licenseLimit) * 100);
    
    const licenseUsageElement = document.getElementById('license-usage');
    if (licenseUsageElement) {
        licenseUsageElement.textContent = `${usagePercentage}%`;
    }

    // Update progress bar
    const progressBar = document.querySelector('.progress');
        if (progressBar) {
        progressBar.style.width = `${usagePercentage}%`;
        // Add color classes based on usage
        progressBar.className = 'progress ' + 
            (usagePercentage > 90 ? 'critical' : 
             usagePercentage > 75 ? 'warning' : 
             'normal');
    }

    // Update status distribution in overview modal if it exists
    const modalStats = document.querySelector('.status-distribution');
    if (modalStats) {
        const statusCounts = modalStats.querySelectorAll('.status-count');
        if (statusCounts.length >= 3) {
            statusCounts[0].textContent = stats.permanent + stats.temporary; // Active (Permanent + Temporary)
            statusCounts[1].textContent = stats.temporary; // Temporary
            statusCounts[2].textContent = stats.incomplete; // Incomplete
        }
    }

    // Update modal statistics
    const modalTotalEmployees = document.getElementById('modal-total-employees');
    if (modalTotalEmployees) {
        modalTotalEmployees.textContent = totalEmployees;
    }

    const modalLicenseLimit = document.getElementById('modal-license-limit');
    if (modalLicenseLimit) {
        modalLicenseLimit.textContent = licenseLimit;
    }

    const modalLicenseUsage = document.getElementById('modal-license-usage');
    if (modalLicenseUsage) {
        modalLicenseUsage.textContent = `${usagePercentage}%`;
    }

    const modalProgressBar = document.querySelector('#employee-overview-modal .progress');
    if (modalProgressBar) {
        modalProgressBar.style.width = `${usagePercentage}%`;
    }
}

function openEmployeeModal(employeeId) {
    const modal = document.getElementById('employee-details-modal');
    if (!modal) {
        console.error('Employee details modal not found');
        return;
    }

    // Show loading modal while fetching data
    const loadingModal = document.getElementById('loadingModal');
    if (loadingModal) {
        loadingModal.classList.remove('hidden');
    }

    // Fetch and display employee details
    fetch(`../api/employees.php?id=${employeeId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Failed to fetch employee details');
            }
            return response.json();
        })
        .then(result => {
            if (!result.success) {
                throw new Error(result.message || 'Failed to fetch employee details');
            }

            // Hide loading modal
            if (loadingModal) {
                loadingModal.classList.add('hidden');
            }

            // Show employee modal
            modal.style.display = 'flex';

            // Initialize employee modal manager if not already initialized
            if (!window.employeeModalManager) {
                window.employeeModalManager = new EmployeeModalManager();
            }

            // Let the modal manager handle populating the data
            window.employeeModalManager.openModal(employeeId);
        })
        .catch(error => {
            console.error('Error fetching employee details:', error);
            showNotification(error.message, 'error');
            
            // Hide loading modal
            if (loadingModal) {
                loadingModal.classList.add('hidden');
            }
        });
}