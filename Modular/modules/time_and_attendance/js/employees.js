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
        this.contentSelector = options.contentSelector;
        this.activeClass = options.activeClass || 'active';
        this.dataAttribute = options.dataAttribute || 'data-tab';
        this.transitionDuration = 300; // ms - match with CSS animation duration
        this.init();
    }

    init() {
        const container = document.querySelector(this.containerSelector);
        if (!container) return;

        const buttons = container.querySelectorAll(this.buttonSelector);
        buttons.forEach(button => {
            button.addEventListener('click', () => {
                this.switchTab(button);
            });
        });
    }

    switchTab(selectedButton) {
        // Get the container elements
        const container = selectedButton.closest(this.containerSelector);
        const allButtons = container.querySelectorAll(this.buttonSelector);
        const allPanes = container.querySelectorAll(this.contentSelector);
        
        // Get the target tab pane
        const tabId = selectedButton.getAttribute(this.dataAttribute);
        const targetPane = document.getElementById(`${tabId}-tab`);
        
        if (!targetPane) return;
        
        // Store the currently active pane before making changes
        const activePane = Array.from(allPanes).find(pane => 
            pane.classList.contains(this.activeClass)
        );
        
        // If the selected tab is already active, do nothing
        if (activePane === targetPane) return;
        
        // Update ARIA attributes
        allButtons.forEach(btn => {
            btn.setAttribute('aria-selected', 'false');
            btn.classList.remove(this.activeClass);
        });
        
        selectedButton.classList.add(this.activeClass);
        selectedButton.setAttribute('aria-selected', 'true');
        
        // Elegant transition
        if (activePane) {
            // Start fade out of current pane
            activePane.style.animation = 'fadeOut 0.2s ease forwards';
            
            // After the fade out animation completes
            setTimeout(() => {
                activePane.classList.remove(this.activeClass);
                activePane.style.animation = '';
                
                // Show and animate the target pane
                targetPane.classList.add(this.activeClass);
                targetPane.style.animation = 'fadeIn 0.3s ease forwards';
            }, 200); // Match this with the fadeOut animation duration
        } else {
            // No active pane, just show the target
            targetPane.classList.add(this.activeClass);
        }
    }
}

// Initialize page level tabs
const pageTabManager = new TabManager({
    containerSelector: '.page-tabs-container',
    buttonSelector: '.page-tab-button',
    contentSelector: '.page-tab-pane',
    dataAttribute: 'data-tab'
});

// Initialize employee modal tabs
const modalTabManager = new TabManager({
    containerSelector: '.employee-details-tabs',
    buttonSelector: '.emp-details-tab-button',
    contentSelector: '.emp-details-tab-pane',
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
        const containers = document.querySelectorAll(this.containerSelector);
        if (containers.length === 0) return;

        containers.forEach(container => {
            container.addEventListener('click', (e) => {
                const button = e.target.closest(this.buttonSelector);
                if (!button) return;

                this.switchSubTab(button);
            });
        });
    }

    switchSubTab(selectedButton) {
        const container = selectedButton.closest(this.containerSelector);
        const tabId = selectedButton.getAttribute(this.dataAttribute);
        
        // Update buttons within this container
        const buttons = container.querySelectorAll(this.buttonSelector);
        buttons.forEach(button => {
            button.classList.remove(this.activeClass);
            button.setAttribute('aria-selected', 'false');
        });
        selectedButton.classList.add(this.activeClass);
        selectedButton.setAttribute('aria-selected', 'true');
        
        // Find the parent tab pane to scope our search for subtab panes
        const parentPane = container.closest('.page-tab-pane');
        if (!parentPane) return;
        
        // Update panes - only within the current active parent tab
        const allPanes = parentPane.querySelectorAll(this.paneSelector);
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
    containerSelector: '.page-subtabs-header',
    buttonSelector: '.page-subtab-button',
    paneSelector: '.page-subtab-pane',
    dataAttribute: 'data-subtab'
});

// Employee Modal Manager
class EmployeeModalManager {
    constructor() {
        this.modal = document.getElementById('employee-details-modal');
        this.statusStates = {
            active: { icon: 'check_circle', color: '#2ed573' },
            suspended: { icon: 'pause_circle', color: '#ffa000' },
            terminated: { icon: 'cancel', color: '#ff4757' },
            leave: { icon: 'event_busy', color: '#1e90ff' }
        };
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
        this.initFormTracking();
        this.initAccessControl();
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
        // Show loading modal
        const loadingModal = document.getElementById('loadingModal');
        if (loadingModal) {
            loadingModal.classList.remove('hidden');
        }
        
        const formData = this.collectFormData();
        const employeeId = this.currentEmployeeId;
        
        // Make API call to save data
        fetch(`../api/employee-api.php?action=update&id=${employeeId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(response => response.json())
        .then(result => {
            // Hide loading modal
            if (loadingModal) {
                loadingModal.classList.add('hidden');
            }
            
            if (result.success) {
                showNotification('Employee details saved successfully!', 'success');
                this.closeModal();
                
                // Refresh the employee list to show updated data
                loadEmployees();
            } else {
                throw new Error(result.message || 'Failed to save employee details');
            }
        })
        .catch(error => {
            // Hide loading modal
            if (loadingModal) {
                loadingModal.classList.add('hidden');
            }
            
            console.error('Error saving employee data:', error);
            showNotification(error.message, 'error');
        });
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
                },
                emergencyContact: {
                    name: document.getElementById('emergency-name').value,
                    relationship: document.getElementById('emergency-relation').value,
                    phone: document.getElementById('emergency-phone').value,
                    email: document.getElementById('emergency-email').value
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
            // Store the current employee ID for later use (e.g., when saving)
            this.currentEmployeeId = employeeId;
            
            // Show loading indicator
            const loadingModal = document.getElementById('loadingModal');
            if (loadingModal) {
                loadingModal.classList.remove('hidden');
            }
            
            const response = await fetch(`../api/employee-api.php?action=details&id=${employeeId}`);
            if (!response.ok) throw new Error('Failed to fetch employee data');
            
            const result = await response.json();
            if (!result.success) throw new Error(result.message || 'Failed to fetch employee data');
            
            // Hide loading indicator
            if (loadingModal) {
                loadingModal.classList.add('hidden');
            }
            
            const employee = result.data;
            
            // Safely set field values - only if the element and data property exist
            this.setFieldValue('employee-full-name', `${employee.first_name} ${employee.last_name}`);
            this.setFieldValue('employee-payroll-number', employee.employee_number);
            this.setFieldValue('employee-clock-number', employee.clock_number);
            this.setFieldValue('employee-badge-number', employee.badge_number, 'Not Set');
            this.setFieldValue('employee-gender', employee.gender, 'male');
            
            // Personal Details
            this.setFieldValue('employee-dob', employee.date_of_birth);
            this.setFieldValue('employee-email', employee.email);
            this.setFieldValue('employee-phone', employee.phone_number);
            this.setFieldValue('employee-address1', employee.address_line1);
            this.setFieldValue('employee-address2', employee.address_line2);
            this.setFieldValue('employee-city', employee.city);
            this.setFieldValue('employee-state', employee.state);
            this.setFieldValue('employee-postal', employee.postal_code);
            this.setFieldValue('employee-country', employee.country);
            
            // Only set these fields if they exist in the API response
            if (employee.emergency_contact_name) {
                this.setFieldValue('emergency-name', employee.emergency_contact_name);
                this.setFieldValue('emergency-relation', employee.emergency_contact_relation);
                this.setFieldValue('emergency-phone', employee.emergency_contact_phone);
                this.setFieldValue('emergency-email', employee.emergency_contact_email);
            }
            
            // Organization fields
            this.setFieldValue('employee-division', employee.division_id);
            this.setFieldValue('employee-department', employee.department_id);
            this.setFieldValue('employee-group', employee.employee_group);
            this.setFieldValue('employee-cost-centre', employee.cost_centre);
            this.setFieldValue('employee-location', employee.location_id);
            this.setFieldValue('employee-team', employee.team_id);
            this.setFieldValue('employee-manager', employee.manager_id);
            
            // Employment Details
            this.setFieldValue('employee-job-title', employee.position_name);
            this.setFieldValue('employee-hire-date', employee.hire_date);
            this.setFieldValue('employee-contract-type', employee.contract_type, 'permanent');
            this.setFieldValue('employee-status', employee.status, 'active');
            
            // Update modern status card
            this.updateEmployeeStatus(employee.status || 'active');
            
            // Only process these sections if they exist in the API response
            if (employee.leave_balances) {
                this.updateLeaveBalances(employee.leave_balances);
            }
            
            if (employee.leave_history) {
                this.updateLeaveHistory(employee.leave_history);
            }
            
            if (employee.devices) {
                this.updateDevices(employee.devices);
            }
            
            if (employee.mobile_settings) {
                this.updateMobileSettings(employee.mobile_settings);
            }
            
            // Set the first tab as active
            const firstTab = document.querySelector('.emp-details-tab-button');
            if (firstTab) {
                const modalTabManager = new TabManager({
                    containerSelector: '.employee-details-tabs',
                    buttonSelector: '.emp-details-tab-button',
                    contentSelector: '.emp-details-tab-pane',
                    dataAttribute: 'data-modal-tab'
                });
                modalTabManager.switchTab(firstTab);
            }
            
            // Set gender in the new radio buttons
            this.updateGenderSelection(employee.gender || 'male');
            
        } catch (error) {
            console.error('Error fetching employee data:', error);
            showNotification('Failed to load employee details: ' + error.message, 'error');
            
            // Hide loading modal
            const loadingModal = document.getElementById('loadingModal');
            if (loadingModal) {
                loadingModal.classList.add('hidden');
            }
        }
    }

    // Helper method to safely set field values
    setFieldValue(elementId, value, defaultValue = '') {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        if (element.tagName === 'INPUT') {
            if (element.type === 'checkbox') {
                element.checked = !!value;
            } else {
                element.value = value || defaultValue;
            }
        } else if (element.tagName === 'SELECT') {
            element.value = value || defaultValue;
        } else {
            element.textContent = value || defaultValue;
        }
    }

    // Helper methods for updating specific sections
    updateLeaveBalances(leaveBalances) {
        leaveBalances.forEach(balance => {
            const input = document.getElementById(`${balance.leave_type.toLowerCase()}-leave`);
            if (input) input.value = balance.balance;
        });
    }

    updateLeaveHistory(leaveHistory) {
        const leaveHistoryBody = document.querySelector('#leave-tab .employee-table tbody');
        if (!leaveHistoryBody) return;
        
        leaveHistoryBody.innerHTML = leaveHistory.map(leave => `
            <tr>
                <td>${leave.leave_type}</td>
                <td>${new Date(leave.start_date).toLocaleDateString()}</td>
                <td>${new Date(leave.end_date).toLocaleDateString()}</td>
                <td>${Math.ceil((new Date(leave.end_date) - new Date(leave.start_date)) / (1000 * 60 * 60 * 24))} days</td>
                <td><span class="status-badge status-${leave.status.toLowerCase()}">${leave.status}</span></td>
            </tr>
        `).join('');
    }

    updateDevices(devices) {
        const deviceList = document.querySelector('.device-list');
        if (!deviceList) return;
        
        deviceList.innerHTML = devices.map(device => `
            <div class="device-item">
                <span>${device.device_name} (${device.device_type})</span>
                <button class="remove-device">Remove</button>
            </div>
        `).join('');
    }

    updateMobileSettings(mobileSettings) {
        this.setFieldValue('gps-tracking', mobileSettings.gps_tracking);
        this.setFieldValue('biometric-auth', mobileSettings.biometric_auth);
        this.setFieldValue('manual-entry', mobileSettings.manual_entry);
        this.setFieldValue('offline-mode', mobileSettings.offline_mode);
        this.setFieldValue('photo-verification', mobileSettings.photo_verification);
        this.setFieldValue('geofencing-type', mobileSettings.geofencing_type, 'none');
    }

    initFormTracking() {
        // Get all form fields in the modal
        const formInputs = document.querySelectorAll('#employee-details-modal input, #employee-details-modal select, #employee-details-modal textarea');
        const formCards = document.querySelectorAll('#employee-details-modal .form-card');
        
        // Track changes to mark sections as modified
        formInputs.forEach(input => {
            input.addEventListener('change', () => {
                // Find the parent form-card
                const parentCard = input.closest('.form-card');
                if (parentCard) {
                    parentCard.classList.add('changed');
                    
                    // Find the associated tab and mark it
                    const tabPane = parentCard.closest('.emp-details-tab-pane');
                    if (tabPane) {
                        const tabId = tabPane.id.replace('-tab', '');
                        const tabButton = document.querySelector(`.emp-details-tab-button[data-modal-tab="${tabId}"]`);
                        if (tabButton) {
                            tabButton.classList.add('incomplete');
                        }
                    }
                }
            });
        });
        
        // Initialize completion indicators for tabs
        document.querySelectorAll('.emp-details-tab-button').forEach(button => {
            // Add a completion indicator element
            const indicator = document.createElement('span');
            indicator.className = 'completion-indicator';
            button.appendChild(indicator);
        });
    }
    
    markSectionCompleted(sectionId) {
        const section = document.getElementById(sectionId);
        if (section) {
            section.classList.add('completed');
            section.classList.remove('changed');
            
            // Update tab indicator
            const tabId = section.closest('.emp-details-tab-pane').id.replace('-tab', '');
            const tabButton = document.querySelector(`.emp-details-tab-button[data-modal-tab="${tabId}"]`);
            if (tabButton) {
                tabButton.classList.add('completed');
                tabButton.classList.remove('incomplete');
            }
        }
    }

    initAccessControl() {
        // Connect checkboxes to zone highlights
        const zoneItems = document.querySelectorAll('.zone-item input[type="checkbox"]');
        const zoneHighlights = document.querySelectorAll('.zone-highlight');
        
        // Initial sync of zones
        this.syncZoneHighlights();
        
        // Add event listeners to checkboxes
        zoneItems.forEach(checkbox => {
            checkbox.addEventListener('change', () => {
                this.syncZoneHighlights();
            });
        });
        
        // Add event listeners to zone highlights
        zoneHighlights.forEach(zone => {
            zone.addEventListener('click', () => {
                const zoneId = zone.dataset.zone;
                const checkbox = document.getElementById(zoneId);
                if (checkbox) {
                    checkbox.checked = !checkbox.checked;
                    this.syncZoneHighlights();
                }
            });
        });
    }
    
    syncZoneHighlights() {
        const zoneItems = document.querySelectorAll('.zone-item input[type="checkbox"]');
        const zoneHighlights = document.querySelectorAll('.zone-highlight');
        
        // Create a map of zone IDs to checked status
        const zoneStatus = {};
        zoneItems.forEach(checkbox => {
            zoneStatus[checkbox.id] = checkbox.checked;
        });
        
        // Update zone highlights based on checkbox status
        zoneHighlights.forEach(zone => {
            const zoneId = zone.dataset.zone;
            if (zoneStatus[zoneId]) {
                zone.classList.remove('disabled');
                zone.classList.add('active');
            } else {
                zone.classList.add('disabled');
                zone.classList.remove('active');
            }
        });
    }
    
    // Update the updateGenderSelection function to work with the new radio buttons
    updateGenderSelection(gender) {
        const maleRadio = document.getElementById('gender-male');
        const femaleRadio = document.getElementById('gender-female');
        
        if (gender === 'female') {
            femaleRadio.checked = true;
        } else {
            maleRadio.checked = true;
        }
        
        updateProfilePlaceholder(gender);
    }

    updateEmployeeStatus(status) {
        const statusCard = document.querySelector('.status-card');
        const statusValue = document.querySelector('.status-value');
        const statusIcon = document.querySelector('.status-icon-symbol');
        
        if (!statusCard || !statusValue || !statusIcon) return;
        
        // Remove all existing status classes
        statusCard.classList.remove('active', 'suspended', 'terminated', 'leave');
        
        // Add appropriate class and set text/icon based on status
        statusCard.classList.add(status);
        
        // Set the display text with first letter capitalized
        statusValue.textContent = status.charAt(0).toUpperCase() + status.slice(1);
        
        // Set appropriate icon
        switch(status) {
            case 'active':
                statusIcon.textContent = 'check_circle';
                break;
            case 'suspended':
                statusIcon.textContent = 'pause_circle';
                break;
            case 'terminated':
                statusIcon.textContent = 'cancel';
                break;
            case 'leave':
                statusIcon.textContent = 'event_busy';
                break;
            default:
                statusIcon.textContent = 'help';
        }
    }
}

// Initialize search functionality
function initSearch() {
    const searchInput = document.getElementById('employee-search');
    if (searchInput) {
        let searchTimeout;
        
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                loadEmployees(1, { search: this.value });
            }, 500); // Debounce search input
        });
    }
}

// Function to ensure the openAddEmployeeModal function works properly for the sidebar button
function openAddEmployeeModal() {
    const addEmployeeModal = document.getElementById('add-employee-modal');
    if (!addEmployeeModal) {
        console.error('Add Employee modal not found!');
        return;
    }
    
    // Show the modal with flex display
    addEmployeeModal.style.display = 'flex';
    
    // Force browser reflow before adding the show class for animation
    void addEmployeeModal.offsetWidth;
    
    // Add show class for animation if it exists
    addEmployeeModal.classList.add('show');
    
    // Reset form
    const addEmployeeForm = document.getElementById('addEmployeeForm');
    if (addEmployeeForm) {
        addEmployeeForm.reset();
        
        // Clear any errors
        document.querySelectorAll('.add-emp-error').forEach(error => {
            error.style.display = 'none';
            error.textContent = '';
        });
        document.querySelectorAll('.add-emp-form-group').forEach(group => {
            group.classList.remove('has-error');
        });
    }
}

// Initialize when DOM is loaded - Remove the event handler for the deleted button
document.addEventListener('DOMContentLoaded', () => {
    initEmployeeOverviewModal();
    new EmployeeModalManager();
    initSearch();
    loadEmployees(); // Initial load
});

// Employee Management Functions
async function loadEmployees(page = 1, filters = {}) {
    try {
        // Show loading indicator
        const loadingModal = document.getElementById('loadingModal');
        if (loadingModal) {
            loadingModal.classList.remove('hidden');
        }
        
        // Build query string for filters
        const queryParams = new URLSearchParams({
            action: 'list',
            page: page,
            per_page: 20,
            ...filters
        });
        
        const response = await fetch(`../api/employee-api.php?${queryParams}`);
        
        // Check for non-JSON responses
        const contentType = response.headers.get('content-type');
        if (!contentType || !contentType.includes('application/json')) {
            const text = await response.text();
            throw new Error(`Server returned non-JSON response: ${text}`);
        }
        
        if (!response.ok) {
            throw new Error(`Server returned status ${response.status}`);
        }
        
        const result = await response.json();
        
        // Hide loading indicator
        if (loadingModal) {
            loadingModal.classList.add('hidden');
        }
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to load employees');
        }
        
        displayEmployees(result.data);
        updatePagination(result.pagination);
    } catch (error) {
        // Hide loading indicator
        const loadingModal = document.getElementById('loadingModal');
        if (loadingModal) {
            loadingModal.classList.add('hidden');
        }
        
        console.error('Error loading employees:', error);
        showNotification('Failed to load employees: ' + error.message, 'error');
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

    // If no employees, show message
    if (!employees || employees.length === 0) {
        Object.values(tables).forEach(table => {
            if (table) {
                table.innerHTML = `
                    <tr>
                        <td colspan="6" class="no-data">No employees found</td>
                    </tr>
                `;
            }
        });
        
        // Update stats with zeros
        updateEmployeeStats([]);
        return;
    }

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
            
            // Show loading modal
            const loadingModal = document.getElementById('loadingModal');
            if (loadingModal) {
                loadingModal.classList.remove('hidden');
            }
            
            const formData = new FormData(addEmployeeForm);
            const employeeData = {
                employee_number: formData.get('employeeNumber'),
                clock_number: formData.get('clockNumber'),
                first_name: formData.get('firstName'),
                last_name: formData.get('lastName'),
                csrf_token: formData.get('csrf_token')
            };

            try {
                const response = await fetch('../api/employee-api.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(employeeData)
                });

                const result = await response.json();
                
                // Hide loading modal
                if (loadingModal) {
                    loadingModal.classList.add('hidden');
                }
                
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
                // Hide loading modal
                if (loadingModal) {
                    loadingModal.classList.add('hidden');
                }
                
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

// Update the showNotification function to use the standardized responseModal
function showNotification(message, type = 'info') {
    const modalResponse = document.getElementById('modalResponse');
    if (modalResponse) {
        const title = document.getElementById('modalResponseTitle');
        const icon = document.getElementById('modalResponseIcon');
        const msg = document.getElementById('modalResponseMessage');
        const closeBtn = document.getElementById('modalResponseClose');

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
        
        // Auto-hide success messages after 3 seconds
        if (type === 'success') {
            setTimeout(() => {
                modalResponse.classList.add('hidden');
            }, 3000);
        }
        
        // Ensure close button works
        if (closeBtn) {
            closeBtn.onclick = function() {
                modalResponse.classList.add('hidden');
            };
        }
    } else {
        console.log(`${type}: ${message}`);
    }
}

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

    // Fetch and display employee details using the new API endpoint
    fetch(`../api/employee-api.php?action=details&id=${employeeId}`)
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

// Function to update pagination controls based on API response
function updatePagination(paginationData) {
    if (!paginationData) return;
    
    const paginationElements = document.querySelectorAll('.pagination');
    
    paginationElements.forEach(pagination => {
        // Find the pagination info span
        const paginationInfo = pagination.querySelector('.pagination-info');
        if (paginationInfo) {
            paginationInfo.textContent = `Page ${paginationData.current_page} of ${paginationData.total_pages}`;
        }
        
        // Get the pagination buttons
        const firstPageBtn = pagination.querySelector('.pagination-button:first-child');
        const prevPageBtn = pagination.querySelector('.pagination-button:nth-child(2)');
        const nextPageBtn = pagination.querySelector('.pagination-button:nth-child(4)');
        const lastPageBtn = pagination.querySelector('.pagination-button:last-child');
        
        // Update button states based on current page
        if (firstPageBtn) {
            firstPageBtn.disabled = paginationData.current_page <= 1;
        }
        
        if (prevPageBtn) {
            prevPageBtn.disabled = paginationData.current_page <= 1;
        }
        
        if (nextPageBtn) {
            nextPageBtn.disabled = paginationData.current_page >= paginationData.total_pages;
        }
        
        if (lastPageBtn) {
            lastPageBtn.disabled = paginationData.current_page >= paginationData.total_pages;
        }
        
        // Add click handlers to pagination buttons
        if (firstPageBtn && !firstPageBtn.hasAttribute('data-handler-attached')) {
            firstPageBtn.setAttribute('data-handler-attached', 'true');
            firstPageBtn.addEventListener('click', () => {
                if (!firstPageBtn.disabled) {
                    loadEmployees(1);
                }
            });
        }
        
        if (prevPageBtn && !prevPageBtn.hasAttribute('data-handler-attached')) {
            prevPageBtn.setAttribute('data-handler-attached', 'true');
            prevPageBtn.addEventListener('click', () => {
                if (!prevPageBtn.disabled) {
                    loadEmployees(paginationData.current_page - 1);
                }
            });
        }
        
        if (nextPageBtn && !nextPageBtn.hasAttribute('data-handler-attached')) {
            nextPageBtn.setAttribute('data-handler-attached', 'true');
            nextPageBtn.addEventListener('click', () => {
                if (!nextPageBtn.disabled) {
                    loadEmployees(paginationData.current_page + 1);
                }
            });
        }
        
        if (lastPageBtn && !lastPageBtn.hasAttribute('data-handler-attached')) {
            lastPageBtn.setAttribute('data-handler-attached', 'true');
            lastPageBtn.addEventListener('click', () => {
                if (!lastPageBtn.disabled) {
                    loadEmployees(paginationData.total_pages);
                }
            });
        }
    });
}

// Update the updateProfilePlaceholder function to work with the new radio buttons
function updateProfilePlaceholder(gender) {
    const profileImage = document.getElementById('employee-profile-image');
    if (profileImage) {
        profileImage.dataset.gender = gender;
        if (!profileImage.src || profileImage.src.includes('placeholder')) {
            profileImage.src = `../img/placeholders/${gender === 'female' ? 'Female-placeholder.jpg' : 'Male-placeholder.jpg'}`;
        }
    }
}

// Make functions globally accessible
window.openAddEmployeeModal = openAddEmployeeModal;
window.openEmployeeModal = openEmployeeModal;