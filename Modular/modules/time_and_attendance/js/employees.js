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
        this.onTabChange = options.onTabChange; // New callback for tab change
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

        // Set initial active tab if none is set
        const activeButton = container.querySelector(`.${this.activeClass}`);
        if (!activeButton && buttons.length > 0) {
            this.switchTab(buttons[0]);
        }
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
        
        // Always proceed to update UI and call callback, even if the tab is already active
        // if (activePane === targetPane) return; // Removed this line
        
        // Update ARIA attributes
        allButtons.forEach(btn => {
            btn.setAttribute('aria-selected', 'false');
            btn.classList.remove(this.activeClass);
        });
        
        selectedButton.classList.add(this.activeClass);
        selectedButton.setAttribute('aria-selected', 'true');
        
        // Elegant transition
        if (activePane && activePane !== targetPane) { // Only animate if switching to a different tab
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
            // No active pane, or same active pane, just show the target
            targetPane.classList.add(this.activeClass);
            if (!activePane) { // If there was no active pane initially, also trigger fadeIn
                targetPane.style.animation = 'fadeIn 0.3s ease forwards';
            }
        }

        // Execute callback if provided (always call it)
        if (this.onTabChange) {
            this.onTabChange(tabId);
        }
    }
}

// Initialize page level tabs
const pageTabManager = new TabManager({
    containerSelector: '.page-tabs-container',
    buttonSelector: '.page-tab-button',
    contentSelector: '.page-tab-pane',
    dataAttribute: 'data-tab',
    onTabChange: (tabId) => {
        let filters = {};
        console.log(`Main tab changed to: ${tabId}`);
        if (tabId === 'active') {
            // Activate the first sub-tab when the active main tab is selected
            const permanentSubTabButton = document.querySelector('.page-subtab-button[data-subtab="permanent"]');
            if (permanentSubTabButton) {
                // Calling switchSubTab will trigger onSubTabChange, which then calls loadEmployees
                subTabManager.switchSubTab(permanentSubTabButton);
            } else {
                // Fallback if sub-tab button not found, load all active
                console.log('Fallback: Loading all active employees because permanent sub-tab button not found.');
                loadEmployees(1, { status: 'active' });
            }
        } else if (tabId === 'terminated') {
            filters.status = 'terminated';
            console.log('Loading employees with filters:', filters);
            loadEmployees(1, filters);
        } else if (tabId === 'incomplete') {
            filters.status = 'incomplete';
            console.log('Loading employees with filters:', filters);
            loadEmployees(1, filters);
        } else if (tabId === 'all') {
            // No status filter for 'all' tab
            console.log('Loading all employees.');
            loadEmployees(1, {});
        }
    }
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
        this.onSubTabChange = options.onSubTabChange; // New callback for sub-tab change
        
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

        // Set initial active sub-tab if none is set
        const activeButton = containers[0].querySelector(`.${this.activeClass}`);
        if (!activeButton && containers[0].querySelectorAll(this.buttonSelector).length > 0) {
            this.switchSubTab(containers[0].querySelectorAll(this.buttonSelector)[0]);
        }
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

        // Execute callback if provided (always call it)
        if (this.onSubTabChange) {
            this.onSubTabChange(tabId);
        }
    }
}

// Initialize sub-tabs for active employees
const subTabManager = new SubTabManager({
    containerSelector: '.page-subtabs-header',
    buttonSelector: '.page-subtab-button',
    paneSelector: '.page-subtab-pane',
    dataAttribute: 'data-subtab',
    onSubTabChange: (subTabId) => {
        let filters = { status: 'active' }; // Always active for these sub-tabs
        if (subTabId === 'permanent') {
            filters.employment_type = 'Permanent';
        } else if (subTabId === 'temporary') {
            filters.employment_type = 'Temporary';
        }
        console.log(`Sub-tab changed to: ${subTabId}. Loading employees with filters:`, filters);
        loadEmployees(1, filters);
    }
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
        // Prevent double submission
        const saveButton = this.modal.querySelector('.btn-save');
        if (saveButton) {
            saveButton.disabled = true;
        }

        // Show loading modal
        const loadingModal = document.getElementById('loadingModal');
        if (loadingModal) {
            loadingModal.classList.remove('hidden');
        }
        
        const formData = this.collectFormData();
        const employeeId = this.currentEmployeeId;
        
        if (!employeeId) {
            showNotification('Invalid employee ID', 'error');
            if (saveButton) saveButton.disabled = false;
            if (loadingModal) loadingModal.classList.add('hidden');
            return;
        }

        console.log('Sending update request for employee:', employeeId, formData);
        
        // Make API call to save data
        fetch(`../api/employee-api.php?action=update&id=${employeeId}`, {
            method: 'PUT',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        })
        .then(async response => {
            const text = await response.text();
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Failed to parse response:', text);
                throw new Error('Invalid server response');
            }
        })
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
            showNotification(error.message || 'Failed to save employee details', 'error');
        })
        .finally(() => {
            // Re-enable save button
            if (saveButton) {
                saveButton.disabled = false;
            }
        });
    }

    collectFormData() {
        // Helper to safely get value from an element
        const getVal = (id, property = 'value', defaultValue = null) => {
            const el = document.getElementById(id);
            return el ? (property === 'checked' ? el.checked : el[property]) : defaultValue;
        };

        // Helper to get checked radio button value by name
        const getRadioVal = (name, defaultValue = null) => {
            const selected = document.querySelector(`input[name="${name}"]:checked`);
            return selected ? selected.value : defaultValue;
        };

        const formData = {
            // core.employees fields
            first_name: getVal('employee-full-name', 'textContent').split(' ')[0],
            last_name: getVal('employee-full-name', 'textContent').split(' ').slice(1).join(' '),
            employee_number: getVal('employee-payroll-number', 'textContent'),
            clock_number: getVal('employee-clock-number', 'textContent'),
            is_sales: getVal('is-sales', 'checked', false),

            // core.employee_contact fields
            email: getVal('employee-email'),
            phone_number: getVal('employee-phone'),
            
            // core.employee_personal fields
            date_of_birth: getVal('employee-dob'),
            gender: getRadioVal('gender'),
            id_number: getVal('employee-id-number'),
            
            // core.employee_employment fields
            hire_date: getVal('employee-hire-date'),
            termination_date: getVal('termination-date'),
            status: getVal('employee-status'),
            
            // core.address fields
            address: {
                line1: getVal('employee-address1'),
                line2: getVal('employee-address2'),
                suburb: getVal('employee-city'),
                city: getVal('employee-city'),
                province: getVal('employee-state'),
                postcode: getVal('employee-postal'),
                country: getVal('employee-country')
            },
            
            // core.employee_emergency_contact fields
            emergency_contact: {
                name: getVal('emergency-name'),
                relation: getVal('emergency-relation'),
                phone: getVal('emergency-phone'),
                email: getVal('emergency-email')
            }
        };
        
        return formData;
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
            
            // Use the same path structure as the working list endpoint
            const url = `../api/employee-api.php?action=details&id=${employeeId}`;
            console.log('Fetching employee details from:', url);
            
            const response = await fetch(url, {
                method: 'GET',
                credentials: 'same-origin',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });
            if (!response.ok) throw new Error('Failed to fetch employee data');
            
            const result = await response.json();
            if (!result.success) throw new Error(result.message || 'Failed to fetch employee data');
            
            // Hide loading indicator
            if (loadingModal) {
                loadingModal.classList.add('hidden');
            }
            
            const employee = result.data;
            
            // --- Employee Profile Section --- 
            this.setFieldValue('employee-full-name', `${employee.first_name || ''} ${employee.last_name || ''}`);
            this.setFieldValue('employee-payroll-number', employee.employee_number);
            this.setFieldValue('employee-clock-number', employee.clock_number);
            this.setFieldValue('employee-badge-number', employee.badge_number, 'Not Set');
            
            // Gender radio buttons
            if (employee.gender) {
                const genderMale = document.getElementById('gender-male');
                const genderFemale = document.getElementById('gender-female');
                if (genderMale) genderMale.checked = employee.gender.toLowerCase() === 'male';
                if (genderFemale) genderFemale.checked = employee.gender.toLowerCase() === 'female';
                updateProfilePlaceholder(employee.gender.toLowerCase()); // Update profile image based on gender
            } else {
                 updateProfilePlaceholder('male'); // Default to male if gender is null
            }

            // Status handling (existing)
            this.updateEmployeeStatus(employee.status || 'active');

            // --- Personal Details Tab ---
            this.setFieldValue('employee-dob', employee.date_of_birth);
            this.setFieldValue('employee-email', employee.email);
            this.setFieldValue('employee-phone', employee.phone_number);

            // Address fields
            if (employee.address) {
                try {
                    const addressObj = JSON.parse(employee.address);
                    this.setFieldValue('employee-address1', addressObj.line1);
                    this.setFieldValue('employee-address2', addressObj.line2);
                    this.setFieldValue('employee-city', addressObj.city);
                    this.setFieldValue('employee-state', addressObj.province);
                    this.setFieldValue('employee-postal', addressObj.postcode);
                    this.setFieldValue('employee-country', addressObj.country);
                } catch (e) {
                    console.warn('Error parsing address JSON:', e, employee.address);
                    // Fallback to plain text address or clear fields
                    this.setFieldValue('employee-address1', employee.address);
                    this.setFieldValue('employee-address2', '');
                    this.setFieldValue('employee-city', '');
                    this.setFieldValue('employee-state', '');
                    this.setFieldValue('employee-postal', '');
                    this.setFieldValue('employee-country', '');
                }
            } else {
                // Clear address fields if no address data
                this.setFieldValue('employee-address1', '');
                this.setFieldValue('employee-address2', '');
                this.setFieldValue('employee-city', '');
                this.setFieldValue('employee-state', '');
                this.setFieldValue('employee-postal', '');
                this.setFieldValue('employee-country', '');
            }
            
            // Emergency Contact fields
            if (employee.emergency_contact) {
                this.setFieldValue('emergency-name', employee.emergency_contact.name);
                this.setFieldValue('emergency-relation', employee.emergency_contact.relation);
                this.setFieldValue('emergency-phone', employee.emergency_contact.phone);
                this.setFieldValue('emergency-email', employee.emergency_contact.email);
            } else {
                // Clear emergency contact fields if no data
                this.setFieldValue('emergency-name', '');
                this.setFieldValue('emergency-relation', '');
                this.setFieldValue('emergency-phone', '');
                this.setFieldValue('emergency-email', '');
            }
            
            // --- Organization Tab ---
            this.setFieldValue('employee-company', employee.company);
            this.setFieldValue('employee-division', employee.division);
            this.setFieldValue('employee-department', employee.department);
            this.setFieldValue('employee-group', employee.group);
            this.setFieldValue('employee-cost-centre', employee.cost_center);
            this.setFieldValue('employee-location', employee.location);
            this.setFieldValue('employee-manager', employee.manager_id);
            
            // --- Employment Tab ---
            this.setFieldValue('employee-job-title', employee.position);
            this.setFieldValue('employee-hire-date', employee.hire_date);
            this.setFieldValue('employee-contract-type', employee.employment_type);
            this.setFieldValue('employee-title', employee.title);
            this.setFieldValue('employee-id-number', employee.id_number);
            this.setFieldValue('employee-rate-type', employee.rate_type);
            this.setFieldValue('employee-rate', employee.rate);
            
            // Overtime checkbox
            const allowOvertimeCheckbox = document.getElementById('allow-overtime');
            if (allowOvertimeCheckbox) {
                allowOvertimeCheckbox.checked = employee.overtime === 'Yes';
            }

            this.setFieldValue('employee-pay-period', employee.pay_period);

            // --- Schedule Tab ---
            this.setFieldValue('work-pattern', employee.work_week);
            this.setFieldValue('roster-template', employee.monthly_roster);
            this.setFieldValue('standard-hours', employee.standard_hours);
            this.setFieldValue('break-duration', employee.break_duration);
            this.setFieldValue('grace-period', employee.grace_period);
            
            // Flexible Hours checkbox
            const flexibleHoursCheckbox = document.getElementById('flexible-hours');
            if (flexibleHoursCheckbox) {
                flexibleHoursCheckbox.checked = employee.flexible_hours === 'Yes';
            }

            // --- Termination Tab (mostly not in DDL)
            this.setFieldValue('termination-date', employee.termination_date);
            this.setFieldValue('termination-reason', employee.termination_reason);
            this.setFieldValue('last-day-worked', employee.last_day_worked);
            const eligibleForRehireCheckbox = document.getElementById('eligible-for-rehire');
            if (eligibleForRehireCheckbox) {
                eligibleForRehireCheckbox.checked = employee.eligible_for_rehire === 'Yes';
            }
            this.setFieldValue('settlement-date', employee.final_settlement_date);
            this.setFieldValue('termination-notes', employee.termination_notes);
            this.setFieldValue('exit-interview-notes', employee.exit_interview_notes);

            // --- Mobile Setup Tab ---
            this.setFieldValue('mobile-device-os', employee.mobile_device_os);
            this.setFieldValue('device_id', employee.device_id);
            const gpsTrackingCheckbox = document.getElementById('gps-tracking');
            if (gpsTrackingCheckbox) {
                gpsTrackingCheckbox.checked = employee.gps_tracking === 'Yes';
            }
            const biometricAuthCheckbox = document.getElementById('biometric-auth');
            if (biometricAuthCheckbox) {
                biometricAuthCheckbox.checked = employee.biometric_auth === 'Yes';
            }
            const manualEntryCheckbox = document.getElementById('manual-entry');
            if (manualEntryCheckbox) {
                manualEntryCheckbox.checked = employee.manual_entry === 'Yes';
            }
            const offlineModeCheckbox = document.getElementById('offline-mode');
            if (offlineModeCheckbox) {
                offlineModeCheckbox.checked = employee.offline_mode === 'Yes';
            }
            const photoVerificationCheckbox = document.getElementById('photo-verification');
            if (photoVerificationCheckbox) {
                photoVerificationCheckbox.checked = employee.photo_verification === 'Yes';
            }
            this.setFieldValue('geofencing-type', employee.geofencing_type);

            // --- Access Control Tab ---
            this.setFieldValue('security-level', employee.security_level);
            this.setFieldValue('role-select', employee.role);
            this.setFieldValue('time-restriction', employee.time_restriction);
            const weekendAccessCheckbox = document.getElementById('weekend-access');
            if (weekendAccessCheckbox) {
                weekendAccessCheckbox.checked = employee.weekend_access === 'Yes';
            }
            this.setFieldValue('badge-id', employee.badge_number);
            this.setFieldValue('fingerprint-id-input', employee.fingerprint_id);
            this.setFieldValue('rfid-id-input', employee.rfid_id);
            this.setFieldValue('facial-recognition-id-input', employee.facial_recognition_id);
            const biometricAccessCheckbox = document.getElementById('biometric-access');
            if (biometricAccessCheckbox) {
                biometricAccessCheckbox.checked = employee.biometric_access === 'Yes';
            }
            const mobileAccessCheckbox = document.getElementById('mobile-access');
            if (mobileAccessCheckbox) {
                mobileAccessCheckbox.checked = employee.mobile_access === 'Yes';
            }
            
            // Set selected access zones
            const accessZonesSelect = document.getElementById('access-zones-select');
            if (accessZonesSelect && Array.isArray(employee.access_zones)) {
                Array.from(accessZonesSelect.options).forEach(option => {
                    option.selected = employee.access_zones.includes(option.value);
                });
            }
            this.syncZoneHighlights(); // Sync visual highlights with selected zones

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
        console.log(`Attempting to set field: ${elementId} with value: ${value}`);
        const element = document.getElementById(elementId);
        if (element) {
            if (element.tagName === 'INPUT' || element.tagName === 'SELECT' || element.tagName === 'TEXTAREA') {
                if (element.type === 'radio') {
                    // For radio buttons, the value should be set by checking the correct one
                    // This is handled in fetchEmployeeData, but good to keep in mind for generic setFieldValue
                    // For now, let's assume individual radio inputs are handled directly in fetchEmployeeData.
                } else if (element.type === 'checkbox') {
                    element.checked = (value === 'Yes' || value === true);
                } else if (element.type === 'date') {
                    // Format date to YYYY-MM-DD for date inputs
                    element.value = value ? new Date(value).toISOString().split('T')[0] : '';
                } else {
                    element.value = value !== null ? value : defaultValue;
                }
            } else if (element.tagName === 'SPAN' || element.tagName === 'H3') {
                element.textContent = value !== null ? value : defaultValue;
            }
            console.log(`Successfully set field: ${elementId}`);
        } else {
            console.warn(`Element with ID: ${elementId} not found.`);
        }
    }

    // Helper methods for updating specific sections
    updateLeaveBalances(leaveBalances) {
        // Iterate over the balances and update the corresponding HTML elements
        if (leaveBalances && Array.isArray(leaveBalances)) {
            leaveBalances.forEach(balance => {
                const type = balance.leave_type.toLowerCase().replace(/ /g, '-'); // e.g., 'Annual Leave' -> 'annual-leave'
                this.setFieldValue(`${type}-balance`, balance.balance);
                this.setFieldValue(`${type}-used`, balance.used);
                
                // Update progress bar (assuming total leave is balance + used for percentage)
                const total = balance.balance + balance.used;
                const progressBar = document.querySelector(`.balance-card.${type} .balance-progress`);
                if (progressBar) {
                    const percentage = total > 0 ? (balance.used / total) * 100 : 0;
                    progressBar.style.width = `${percentage}%`;
                }
            });
        }
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

    // Explicitly load employees for the initial active tab
    // This part should be simple and direct to ensure data loads on page refresh.
    const initialMainTabId = document.querySelector('.page-tab-button.active')?.dataset.tab || 'active'; // Default to 'active'
    let initialFilters = {};

    if (initialMainTabId === 'active') {
        const initialSubTabId = document.querySelector('.page-subtab-button.active')?.dataset.subtab || 'permanent'; // Default to 'permanent'
        initialFilters.status = 'active';
        if (initialSubTabId === 'permanent') {
            initialFilters.employment_type = 'Permanent';
        } else if (initialSubTabId === 'temporary') {
            initialFilters.employment_type = 'Temporary';
        }
    } else if (initialMainTabId === 'terminated') {
        initialFilters.status = 'terminated';
    } else if (initialMainTabId === 'incomplete') {
        initialFilters.status = 'incomplete';
    } else if (initialMainTabId === 'all') {
        // No status filter for 'all' tab
    }
    
    console.log('Initial page load. Loading employees with filters:', initialFilters);
    loadEmployees(1, initialFilters);
});

// Employee Management Functions
async function loadEmployees(page = 1, filters = {}) {
    try {
        // Show loading indicator
        const loadingModal = document.getElementById('loadingModal');
        if (loadingModal) {
            loadingModal.classList.remove('hidden');
        }
        console.log('loadEmployees called with filters:', filters);

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
        
        // Display employees or show "No employees found" message
        if (result.message === 'No employees found' || !result.data || result.data.length === 0) {
            displayEmployees([]);
            showNotification('No employees found', 'info');
        } else {
            displayEmployees(result.data);
        }
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
    // Get all table bodies
    const tableBodies = {
        permanent: document.querySelector('#permanent-tab .main-employee-table tbody'),
        temporary: document.querySelector('#temporary-tab .main-employee-table tbody'),
        terminated: document.querySelector('#terminated-tab .main-employee-table tbody'),
        incomplete: document.querySelector('#incomplete-tab .main-employee-table tbody'),
        all: document.querySelector('#all-tab .main-employee-table tbody')
    };

    // Clear existing rows
    Object.values(tableBodies).forEach(tbody => {
        if (tbody) tbody.innerHTML = '';
    });

    // If no employees, show message
    if (!employees || employees.length === 0) {
        Object.values(tableBodies).forEach(tbody => {
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="7" class="no-data">No employees found</td>
                    </tr>
                `;
            }
        });
        
        // Update stats with zeros
        updateEmployeeStats();
        return;
    }

    // Populate tables
    employees.forEach(employee => {
        const row = createEmployeeRow(employee);
        console.log(`Processing employee: ${employee.first_name} ${employee.last_name}, Status: ${employee.display_status}, Employment Type: ${employee.employment_type}`);
        
        // Add to appropriate tables based on status and employment type
        if (employee.display_status?.toLowerCase() === 'active') {
            if (employee.employment_type?.toLowerCase() === 'temporary' && tableBodies.temporary) {
                const clonedRow = row.cloneNode(true);
                addRowEventListeners(clonedRow, employee);
                tableBodies.temporary.appendChild(clonedRow);
            } else if (tableBodies.permanent) {
                const clonedRow = row.cloneNode(true);
                addRowEventListeners(clonedRow, employee);
                tableBodies.permanent.appendChild(clonedRow);
            }
        } else if (employee.display_status?.toLowerCase() === 'terminated' && tableBodies.terminated) {
            const clonedRow = row.cloneNode(true);
            addRowEventListeners(clonedRow, employee);
            tableBodies.terminated.appendChild(clonedRow);
        } else if (employee.display_status?.toLowerCase() === 'incomplete' && tableBodies.incomplete) {
            const clonedRow = row.cloneNode(true);
            addRowEventListeners(clonedRow, employee);
            tableBodies.incomplete.appendChild(clonedRow);
        }
        
        // Always add to the "all" table
        if (tableBodies.all) {
            const clonedRow = row.cloneNode(true);
            addRowEventListeners(clonedRow, employee);
            tableBodies.all.appendChild(clonedRow);
        }
    });

    // Update employee statistics
    updateEmployeeStats();
}

function createEmployeeRow(employee) {
    const row = document.createElement('tr');
    row.className = 'employee-row';
    row.dataset.employeeId = employee.employee_id;
    
    const hireDate = employee.hire_date ? new Date(employee.hire_date).toLocaleDateString() : '';
    const endDate = employee.end_date ? new Date(employee.end_date).toLocaleDateString() : '';
    
    // Base columns that are common to all tables
    const baseColumns = `
        <td>${employee.employee_number || ''}</td>
        <td>${employee.first_name} ${employee.last_name}</td>
        <td>${employee.department || ''}</td>
        <td>${employee.position || ''}</td>
        <td>${hireDate}</td>
    `;
    
    // Additional columns based on status
    if (employee.display_status?.toLowerCase() === 'terminated') {
        row.innerHTML = baseColumns + `
            <td>${endDate}</td>
            <td>${employee.termination_reason || ''}</td>
        `;
    } else if (employee.employment_type?.toLowerCase() === 'temporary') {
        row.innerHTML = baseColumns + `
            <td>${endDate}</td>
            <td><span class="status-badge ${employee.display_status || 'active'}">${employee.display_status || 'Active'}</span></td>
        `;
    } else {
        row.innerHTML = baseColumns + `
            <td><span class="status-badge ${employee.display_status || 'active'}">${employee.display_status || 'Active'}</span></td>
        `;
    }
    
    return row;
}

function addRowEventListeners(row, employee) {
    row.addEventListener('dblclick', () => {
        console.log('Double-clicked employee row:', employee.employee_id);
        openEmployeeModal(employee.employee_id);
    });
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
                first_name: formData.get('firstName'),
                last_name: formData.get('lastName'),
                is_sales: formData.get('isSales') === 'on' ? true : false
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
                // Show error message
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

// Async function to fetch and update employee statistics from the backend
async function updateEmployeeStats() {
    try {
        const response = await fetch('../api/employee-api.php?action=stats');
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        const result = await response.json();

        if (!result.success) {
            throw new Error(result.message || 'Failed to fetch employee stats');
        }

        const stats = result.data;
        const totalActiveEmployees = stats.total;
        const statusStats = stats.by_status;

        // Update total employees count
        const totalEmployeesElement = document.getElementById('total-employees');
        if (totalEmployeesElement) {
            totalEmployeesElement.textContent = totalActiveEmployees;
        }

        // Calculate and update license usage
        const licenseLimitElement = document.getElementById('license-limit');
        // Default to 300 if not found or invalid
        const licenseLimit = licenseLimitElement ? parseInt(licenseLimitElement.textContent) || 300 : 300;
        const usagePercentage = Math.round((totalActiveEmployees / licenseLimit) * 100);
        
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
            const statusCountsElements = modalStats.querySelectorAll('.status-count');
            if (statusCountsElements.length >= 3) {
                // Ensure these elements exist and update based on fetched data
                // Assuming order: Active (Total), Temporary, Incomplete
                statusCountsElements[0].textContent = statusStats.active || 0; // Total active employees
                statusCountsElements[1].textContent = statusStats.temporary || 0; // Temporary active employees
                statusCountsElements[2].textContent = statusStats.incomplete || 0; // Incomplete employees
            }
        }

        // Update modal statistics (if the modal is open)
        const modalTotalEmployees = document.getElementById('modal-total-employees');
        if (modalTotalEmployees) {
            modalTotalEmployees.textContent = totalActiveEmployees;
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

    } catch (error) {
        console.error('Error fetching employee stats:', error);
        showNotification('Failed to load employee statistics.', 'error');
    }
}

function openEmployeeModal(employeeId) {
    console.log('Opening modal for employee ID:', employeeId);
    
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

    // Use the same path structure as the working list endpoint
    const url = `../api/employee-api.php?action=details&id=${employeeId}`;
    console.log('Fetching employee details from:', url);

    // Fetch and display employee details using the details action
    fetch(url, {
        method: 'GET',
        credentials: 'same-origin',
        headers: {
            'Accept': 'application/json',
            'Content-Type': 'application/json'
        }
    })
    .then(response => {
        console.log('Response status:', response.status);
        console.log('Response headers:', Object.fromEntries(response.headers.entries()));
        
        if (!response.ok) {
            throw new Error(`Failed to fetch employee details: ${response.status} ${response.statusText}`);
        }
        return response.json();
    })
    .then(result => {
        console.log('Response data:', result);
        
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
    console.log('Updating pagination with data:', paginationData);
    console.log(`Pagination Data - Current Page: ${paginationData.current_page}, Total Pages: ${paginationData.total_pages}`);
    if (!paginationData) return;
    
    // Set the value of the perPageSelect dropdown
    const perPageSelect = document.getElementById('perPageSelect');
    if (perPageSelect && paginationData.per_page) {
        perPageSelect.value = paginationData.per_page;
    }

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
            // Remove existing listener before adding a new one
            if (firstPageBtn.listener) {
                firstPageBtn.removeEventListener('click', firstPageBtn.listener);
            }
            firstPageBtn.listener = () => {
                if (!firstPageBtn.disabled) {
                    loadEmployees(1, getCurrentFilters());
                }
            };
            firstPageBtn.addEventListener('click', firstPageBtn.listener);
        }
        
        if (prevPageBtn) {
            prevPageBtn.disabled = paginationData.current_page <= 1;
            if (prevPageBtn.listener) {
                prevPageBtn.removeEventListener('click', prevPageBtn.listener);
            }
            prevPageBtn.listener = () => {
                if (!prevPageBtn.disabled) {
                    loadEmployees(paginationData.current_page - 1, getCurrentFilters());
                }
            };
            prevPageBtn.addEventListener('click', prevPageBtn.listener);
        }
        
        if (nextPageBtn) {
            nextPageBtn.disabled = paginationData.current_page >= paginationData.total_pages;
            if (nextPageBtn.listener) {
                nextPageBtn.removeEventListener('click', nextPageBtn.listener);
            }
            nextPageBtn.listener = () => {
                if (!nextPageBtn.disabled) {
                    loadEmployees(paginationData.current_page + 1, getCurrentFilters());
                }
            };
            nextPageBtn.addEventListener('click', nextPageBtn.listener);
        }
        
        if (lastPageBtn) {
            lastPageBtn.disabled = paginationData.current_page >= paginationData.total_pages;
            if (lastPageBtn.listener) {
                lastPageBtn.removeEventListener('click', lastPageBtn.listener);
            }
            lastPageBtn.listener = () => {
                if (!lastPageBtn.disabled) {
                    loadEmployees(paginationData.total_pages, getCurrentFilters());
                }
            };
            lastPageBtn.addEventListener('click', lastPageBtn.listener);
        }
    });
}

// Helper function to get current filters from the active tab/sub-tab
function getCurrentFilters() {
    let filters = {};
    const activeMainTabButton = document.querySelector('.page-tab-button.active');
    const activeMainTabId = activeMainTabButton ? activeMainTabButton.dataset.tab : 'active';

    // Get per_page value from the select element
    const perPageSelect = document.getElementById('perPageSelect');
    if (perPageSelect) {
        filters.per_page = parseInt(perPageSelect.value, 10);
    }

    if (activeMainTabId === 'active') {
        const activeSubTabButton = document.querySelector('.page-subtab-button.active');
        const activeSubTabId = activeSubTabButton ? activeSubTabButton.dataset.subtab : 'permanent';
        filters.status = 'active';
        if (activeSubTabId === 'permanent') {
            filters.employment_type = 'Permanent';
        } else if (activeSubTabId === 'temporary') {
            filters.employment_type = 'Temporary';
        }
    } else if (activeMainTabId === 'terminated') {
        filters.status = 'terminated';
    } else if (activeMainTabId === 'incomplete') {
        filters.status = 'incomplete';
    } else if (activeMainTabId === 'all') {
        // No status filter for 'all' tab
    }

    // Add search term if present
    const searchInput = document.getElementById('employeeSearchInput');
    if (searchInput && searchInput.value.trim() !== '') {
        filters.search = searchInput.value.trim();
    }

    return filters;
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

document.addEventListener('DOMContentLoaded', function() {
    // Initialize search and pagination related elements here
    initSearch();

    // Handle per page select change
    const perPageSelect = document.getElementById('perPageSelect');
    if (perPageSelect) {
        perPageSelect.addEventListener('change', (event) => {
            // When per page changes, reset to the first page
            loadEmployees(1, getCurrentFilters());
        });
    }
});