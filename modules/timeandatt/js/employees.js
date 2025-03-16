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
        
        this.modal.classList.add('show');
        if (employeeId) {
            this.fetchEmployeeData(employeeId);
        }
    }

    closeModal() {
        this.modal.classList.remove('show');
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
        const employees = await response.json();
        displayEmployees(employees);
    } catch (error) {
        console.error('Error loading employees:', error);
        showNotification('Failed to load employees', 'error');
    }
}

function displayEmployees(employees) {
    const tables = {
        active: document.getElementById('active-employees-table').getElementsByTagName('tbody')[0],
        inactive: document.getElementById('inactive-employees-table').getElementsByTagName('tbody')[0],
        onLeave: document.getElementById('on-leave-employees-table').getElementsByTagName('tbody')[0]
    };

    // Clear existing rows
    Object.values(tables).forEach(table => {
        if (table) table.innerHTML = '';
    });

    // Group employees by status
    const groupedEmployees = groupEmployeesByStatus(employees);

    // Populate tables
    Object.entries(groupedEmployees).forEach(([status, statusEmployees]) => {
        const table = tables[status];
        if (table) {
            statusEmployees.forEach(employee => {
                const row = createEmployeeRow(employee);
                // Bind double-click handler with proper context
                row.addEventListener('dblclick', () => {
                    const employeeId = row.dataset.employeeId;
                    console.log(`Double clicked employee row for employee ID: ${employeeId}`);
                    openEmployeeModal(employeeId);
                });
                table.appendChild(row);
            });
        }
    });

    // Update employee statistics
    updateEmployeeStats(employees);
}

function groupEmployeesByStatus(employees) {
    return employees.reduce((acc, employee) => {
        const status = employee.status.toLowerCase();
        if (!acc[status]) acc[status] = [];
        acc[status].push(employee);
        return acc;
    }, { active: [], inactive: [], onLeave: [] });
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
        <td><span class="status-badge ${employee.status || 'active'}">${employee.status || 'active'}</span></td>
    `;
    
    return row;
}

// Add Employee Modal Functionality
document.addEventListener('DOMContentLoaded', function() {
    const addEmployeeBtn = document.getElementById('addEmployeeBtn');
    const addEmployeeModal = document.getElementById('add-employee-modal');
    const addEmployeeForm = document.getElementById('addEmployeeForm');
    const payPeriodSelect = document.getElementById('payPeriod');
    const customPeriodDetails = document.querySelector('.custom-period-details');
    const cancelBtn = addEmployeeModal.querySelector('.cancel-btn');
    const closeBtn = addEmployeeModal.querySelector('.modal-close');

    // Hide modal
    function hideModal() {
        addEmployeeModal.style.display = 'none';
        addEmployeeForm.reset();
        customPeriodDetails.style.display = 'none';
    }

    closeBtn.addEventListener('click', hideModal);
    cancelBtn.addEventListener('click', hideModal);

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === addEmployeeModal) {
            hideModal();
        }
    });

    // Handle pay period selection
    payPeriodSelect.addEventListener('change', () => {
        if (payPeriodSelect.value === 'custom') {
            customPeriodDetails.style.display = 'block';
        } else {
            customPeriodDetails.style.display = 'none';
        }
    });

    // Load schedule templates
    async function loadScheduleTemplates() {
        try {
            const response = await fetch('../api/templates.php');
            if (!response.ok) {
                throw new Error('Failed to load templates');
            }
            const templates = await response.json();
            const templateSelect = document.getElementById('scheduleTemplate');
            
            // Clear existing options except the first one
            while (templateSelect.options.length > 1) {
                templateSelect.remove(1);
            }

            // Add template options
            templates.forEach(template => {
                const option = document.createElement('option');
                option.value = template.id;
                option.textContent = template.name;
                templateSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Error loading templates:', error);
        }
    }

    // Handle form submission
    addEmployeeForm.addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(addEmployeeForm);
        const employeeData = {
            employee_number: formData.get('employeeNumber'),
            clock_number: formData.get('clockNumber'),
            biometric_id: formData.get('biometricId') || null,
            first_name: formData.get('firstName'),
            last_name: formData.get('lastName'),
            pay_period: formData.get('payPeriod'),
            period_start_date: formData.get('periodStartDate'),
            period_end_date: formData.get('periodEndDate'),
            period_days: formData.get('periodDays'),
            schedule_template_id: formData.get('scheduleTemplate') || null,
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
                throw new Error('Failed to add employee');
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
});

// Utility Functions
function showNotification(message, type = 'info') {
    // Implementation depends on your notification system
    console.log(`${type}: ${message}`);
}

// Initialize the page
document.addEventListener('DOMContentLoaded', () => {
    loadEmployees();
});

// Function to open the Add Employee modal
function openAddEmployeeModal() {
    const modal = document.getElementById('add-employee-modal');
    if (modal) {
        modal.style.display = 'block';
        loadScheduleTemplates();
    }
}

// Employee Management Class
class EmployeeManager {
    constructor() {
        this.tables = {
            active: '#active-tab .main-employee-table tbody',
            terminated: '#terminated-tab .main-employee-table tbody',
            incomplete: '#incomplete-tab .main-employee-table tbody',
            all: '#all-tab .main-employee-table tbody'
        };
        this.initializeEventListeners();
        this.loadEmployees();
    }

    displayEmployees(employees) {
        // Get table references using querySelector
        const tableRefs = {
            active: document.querySelector(this.tables.active),
            terminated: document.querySelector(this.tables.terminated),
            incomplete: document.querySelector(this.tables.incomplete),
            all: document.querySelector(this.tables.all)
        };

        // Clear existing rows
        Object.values(tableRefs).forEach(table => {
            if (table) table.innerHTML = '';
        });

        // Populate tables
        employees.forEach(employee => {
            const row = this.createEmployeeRow(employee);
            
            // Add to appropriate tables
            if (tableRefs[employee.status]) {
                const clonedRow = row.cloneNode(true);
                // Add double-click handler to cloned row
                clonedRow.addEventListener('dblclick', () => {
                    console.log('Double-clicked employee row:', employee.employee_id);
                    this.openEmployeeModal(employee.employee_id);
                });
                tableRefs[employee.status].appendChild(clonedRow);
            }
            if (tableRefs.all) {
                const clonedRow = row.cloneNode(true);
                // Add double-click handler to cloned row
                clonedRow.addEventListener('dblclick', () => {
                    console.log('Double-clicked employee row:', employee.employee_id);
                    this.openEmployeeModal(employee.employee_id);
                });
                tableRefs.all.appendChild(clonedRow);
            }
        });

        // Update employee statistics
        this.updateEmployeeStats(employees);
    }

    initializeEventListeners() {
        // Add Employee button
        const addEmployeeBtn = document.getElementById('addEmployeeBtn');
        if (addEmployeeBtn) {
            addEmployeeBtn.addEventListener('click', () => this.openAddEmployeeModal());
        }

        // Tab switching
        const tabButtons = document.querySelectorAll('.page-tab-button');
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const status = button.dataset.tab;
                this.loadEmployees(status);
            });
        });

        // Modal close handlers
        const closeButtons = document.querySelectorAll('.modal-close, .btn-cancel');
        closeButtons.forEach(button => {
            button.addEventListener('click', () => this.closeModal(button.closest('.modal-container')));
        });

        // Close modal when clicking outside
        window.addEventListener('click', (e) => {
            if (e.target.matches('.modal-container')) {
                this.closeModal(e.target);
            }
        });

        // Form submission handlers
        const addEmployeeForm = document.getElementById('addEmployeeForm');
        if (addEmployeeForm) {
            addEmployeeForm.addEventListener('submit', (e) => this.handleAddEmployee(e));
        }

        // Save changes button
        const saveButton = document.querySelector('.btn-save');
        if (saveButton) {
            saveButton.addEventListener('click', () => this.saveEmployeeData());
        }

        // Initialize device management
        this.initDeviceManagement();
    }

    initializeEmployeeOverviewModal() {
        const employeeOverviewWidget = document.getElementById('employee-count-widget');
        if (employeeOverviewWidget) {
            employeeOverviewWidget.addEventListener('dblclick', () => this.openEmployeeOverviewModal());
        }
    }

    openEmployeeOverviewModal() {
        const modal = document.getElementById('employee-overview-modal');
        if (modal) {
            modal.classList.add('show');
            this.updateEmployeeOverviewStats();
        }
    }

    updateEmployeeOverviewStats() {
        const totalEmployees = document.getElementById('total-employees').textContent;
        const licenseLimit = document.getElementById('license-limit').textContent;
        const licenseUsage = document.getElementById('license-usage').textContent;
        
        document.getElementById('modal-total-employees').textContent = totalEmployees;
        document.getElementById('modal-license-limit').textContent = licenseLimit;
        document.getElementById('modal-license-usage').textContent = licenseUsage;
        
        const progressBar = document.querySelector('#employee-overview-modal .progress');
        if (progressBar) {
            progressBar.style.width = licenseUsage;
        }
    }

    async loadEmployees(status = 'active') {
        try {
            const response = await fetch(`../api/employees.php?status=${status}`);
            if (!response.ok) throw new Error('Failed to load employees');
            
            const employees = await response.json();
            this.displayEmployees(employees);
            this.updateEmployeeStats(employees);
        } catch (error) {
            console.error('Error loading employees:', error);
            this.showNotification('Failed to load employees', 'error');
        }
    }

    createEmployeeRow(employee) {
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

    async openEmployeeModal(employeeId) {
        console.log('Opening modal for employee:', employeeId);
        const modal = document.getElementById('employee-details-modal');
        if (modal) {
            modal.style.display = 'flex';
            modal.classList.add('show');
            if (employeeId) {
                await this.fetchEmployeeData(employeeId);
            }
        }
    }

    closeModal(modal) {
        if (modal) {
            modal.style.display = 'none';
            modal.classList.remove('show');
        }
    }

    initDeviceManagement() {
        const deviceList = document.querySelector('.device-list');
        const addDeviceBtn = document.querySelector('.add-device-btn');

        if (deviceList && addDeviceBtn) {
            addDeviceBtn.addEventListener('click', () => {
                const deviceItem = document.createElement('div');
                deviceItem.className = 'device-item';
                deviceItem.innerHTML = `
                    <span>New Device</span>
                    <button class="remove-device">Remove</button>
                `;
                deviceList.appendChild(deviceItem);
            });

            deviceList.addEventListener('click', (e) => {
                if (e.target.matches('.remove-device')) {
                    e.target.closest('.device-item').remove();
                }
            });
        }
    }

    async fetchEmployeeData(employeeId) {
        console.log('Fetching data for employee:', employeeId);
        try {
            const response = await fetch(`../api/employee-details.php?id=${employeeId}`);
            if (!response.ok) throw new Error('Failed to fetch employee data');
            
            const result = await response.json();
            if (!result.success) throw new Error(result.message || 'Failed to fetch employee data');
            
            console.log('Received employee data:', result.data);
            this.populateEmployeeModal(result.data);
        } catch (error) {
            console.error('Error fetching employee data:', error);
            this.showNotification('Failed to load employee details', 'error');
        }
    }

    populateEmployeeModal(employee) {
        console.log('Populating modal with employee data:', employee);
        
        // Basic info
        this.setElementValue('employee-full-name', `${employee.first_name} ${employee.last_name}`, 'textContent');
        this.setElementValue('employee-payroll-number', employee.employee_number, 'textContent');
        this.setElementValue('employee-clock-number', employee.clock_number || 'Not Set', 'textContent');
        this.setElementValue('employee-gender', employee.gender || 'male');
        
        // Personal Details
        this.setElementValue('employee-dob', employee.date_of_birth);
        this.setElementValue('employee-email', employee.email);
        this.setElementValue('employee-phone', employee.phone_number);
        this.setElementValue('employee-address1', employee.address_line1);
        this.setElementValue('employee-address2', employee.address_line2);
        this.setElementValue('employee-city', employee.city);
        this.setElementValue('employee-state', employee.state);
        this.setElementValue('employee-postal', employee.postal_code);
        this.setElementValue('employee-country', employee.country);
        
        // Emergency Contact
        this.setElementValue('emergency-name', employee.emergency_contact_name);
        this.setElementValue('emergency-relation', employee.emergency_contact_relation);
        this.setElementValue('emergency-phone', employee.emergency_contact_phone);
        this.setElementValue('emergency-email', employee.emergency_contact_email);
        
        // Organization
        this.setElementValue('employee-division', employee.division_id);
        this.setElementValue('employee-department', employee.department_id);
        this.setElementValue('employee-group', employee.employee_group);
        this.setElementValue('employee-cost-centre', employee.cost_centre);
        this.setElementValue('employee-location', employee.location_id);
        this.setElementValue('employee-team', employee.team_id);
        this.setElementValue('employee-manager', employee.manager_id);
        
        // Employment Details
        this.setElementValue('employee-job-title', employee.position_name);
        this.setElementValue('employee-hire-date', employee.hire_date);
        this.setElementValue('employee-contract-type', employee.contract_type || 'permanent');
        this.setElementValue('employee-status', employee.status || 'active');
        
        // Update status badge
        const statusBadge = document.querySelector('.status-badge');
        if (statusBadge) {
            statusBadge.className = `status-badge ${employee.status || 'active'}`;
            statusBadge.textContent = employee.status || 'Active';
        }
        
        // Leave Balances
        if (employee.leave_balances) {
            employee.leave_balances.forEach(balance => {
                this.setElementValue(`${balance.leave_type.toLowerCase()}-leave`, balance.balance);
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
            this.setElementValue('gps-tracking', employee.mobile_settings.gps_tracking, 'checked');
            this.setElementValue('biometric-auth', employee.mobile_settings.biometric_auth, 'checked');
            this.setElementValue('manual-entry', employee.mobile_settings.manual_entry, 'checked');
            this.setElementValue('offline-mode', employee.mobile_settings.offline_mode, 'checked');
            this.setElementValue('photo-verification', employee.mobile_settings.photo_verification, 'checked');
            this.setElementValue('geofencing-type', employee.mobile_settings.geofencing_type || 'none');
        }

        // Set the first tab as active
        const firstTab = document.querySelector('.emp-details-tab-button');
        if (firstTab) {
            modalTabManager.switchTab(firstTab);
        }
    }

    setElementValue(elementId, value, property = 'value') {
        const element = document.getElementById(elementId);
        if (element) {
            element[property] = value || '';
        } else {
            console.warn(`Element with id '${elementId}' not found`);
        }
    }

    collectFormData() {
        return {
            personalDetails: {
                fullName: document.getElementById('employee-full-name').textContent,
                gender: document.getElementById('employee-gender').value,
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
                jobTitle: document.getElementById('employee-job-title').value,
                hireDate: document.getElementById('employee-hire-date').value,
                contractType: document.getElementById('employee-contract-type').value,
                status: document.getElementById('employee-status').value
            },
            organization: {
                division: document.getElementById('employee-division').value,
                department: document.getElementById('employee-department').value,
                group: document.getElementById('employee-group').value,
                costCentre: document.getElementById('employee-cost-centre').value,
                location: document.getElementById('employee-location').value,
                team: document.getElementById('employee-team').value,
                manager: document.getElementById('employee-manager').value
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

    updateEmployeeStats(employees) {
        const totalEmployees = employees.length;
        const activeEmployees = employees.filter(e => e.status === 'active').length;
        const licenseLimit = 300; // This should come from your configuration
        const usagePercentage = Math.round((activeEmployees / licenseLimit) * 100);

        document.getElementById('total-employees').textContent = totalEmployees;
        document.getElementById('license-limit').textContent = licenseLimit;
        document.getElementById('license-usage').textContent = `${usagePercentage}%`;

        const progressBar = document.querySelector('.progress');
        if (progressBar) {
            progressBar.style.width = `${usagePercentage}%`;
        }
    }

    showNotification(message, type = 'info') {
        console.log(`${type}: ${message}`);
        // Implement your notification system here
    }
}

// Initialize the employee manager when the DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const employeeManager = new EmployeeManager();
    employeeManager.initializeEventListeners();
    employeeManager.loadEmployees();
});