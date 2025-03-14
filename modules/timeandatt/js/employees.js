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

  document.addEventListener('DOMContentLoaded', function() {
    // Main tabs functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
      button.addEventListener('click', function() {
        // Remove active class from all buttons and panes
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabPanes.forEach(pane => pane.classList.remove('active'));
        
        // Add active class to clicked button
        this.classList.add('active');
        
        // Show corresponding tab pane
        const tabId = this.getAttribute('data-tab');
        document.getElementById(`${tabId}-tab`).classList.add('active');
      });
    });
    
    // Sub-tabs functionality
    const subTabButtons = document.querySelectorAll('.sub-tab-button');
    const subTabPanes = document.querySelectorAll('.sub-tab-pane');
    
    subTabButtons.forEach(button => {
      button.addEventListener('click', function() {
        // Remove active class from all sub-tab buttons and panes
        subTabButtons.forEach(btn => btn.classList.remove('active'));
        subTabPanes.forEach(pane => pane.classList.remove('active'));
        
        // Add active class to clicked button
        this.classList.add('active');
        
        // Show corresponding sub-tab pane
        const subTabId = this.getAttribute('data-subtab');
        document.getElementById(`${subTabId}-tab`).classList.add('active');
      });
    });
    
    // Double-click functionality for employee rows
    const employeeRows = document.querySelectorAll('.employee-row');
    
    employeeRows.forEach(row => {
      row.addEventListener('dblclick', function() {
        const employeeId = this.getAttribute('data-employee-id');
        openEmployeeModal(employeeId);
      });
    });
    initEmployeeOverviewModal();
  });

  document.addEventListener('DOMContentLoaded', function() {
    // Tab switching functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');

    // Function to switch tabs
    function switchTab(tabId) {
        // Remove active class from all tabs and panes
        tabButtons.forEach(button => button.classList.remove('active'));
        tabPanes.forEach(pane => pane.classList.remove('active'));
        
        // Add active class to selected tab and pane
        const selectedButton = document.querySelector(`.tab-button[data-tab="${tabId}"]`);
        const selectedPane = document.getElementById(`${tabId}-tab`);
        
        if (selectedButton && selectedPane) {
            selectedButton.classList.add('active');
            selectedPane.classList.add('active');
        }
    }

    // Add click event listeners to tab buttons
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            switchTab(tabId);
        });
    });

    // Modal open/close functionality
    const modal = document.getElementById('employee-details-modal');
    const closeButton = document.querySelector('.modal-close');
    const cancelButton = document.querySelector('.btn-cancel');
    
    // Function to open the modal
    window.openEmployeeModal = function(employeeId) {
        if (modal) {
            modal.style.display = 'flex';
            
            // If an employee ID is provided, fetch and populate employee data
            if (employeeId) {
                fetchEmployeeData(employeeId);
            }
        }
    };
    
    // Function to close the modal
    function closeEmployeeModal() {
        if (modal) {
            modal.style.display = 'none';
        }
    }
    
    // Close modal when clicking the close button
    if (closeButton) {
        closeButton.addEventListener('click', closeEmployeeModal);
    }
    
    // Close modal when clicking the cancel button
    if (cancelButton) {
        cancelButton.addEventListener('click', closeEmployeeModal);
    }
    
    // Close modal when clicking outside the modal content
    window.addEventListener('click', function(event) {
        if (event.target === modal) {
            closeEmployeeModal();
        }
    });

    // Toggle switches in Mobile Clocking Setup tab
    const toggleSwitches = document.querySelectorAll('.switch input[type="checkbox"]');
    toggleSwitches.forEach(toggle => {
        toggle.addEventListener('change', function() {
            // Handle toggle state changes
            const featureName = this.id;
            const isEnabled = this.checked;
            console.log(`${featureName} is now ${isEnabled ? 'enabled' : 'disabled'}`);
            
            // Show/hide conditional fields based on toggle state
            if (featureName === 'manual-entry') {
                // Example: If manual entry is disabled, hide related settings
                const manualEntrySettings = document.getElementById('manual-entry-settings');
                if (manualEntrySettings) {
                    manualEntrySettings.style.display = isEnabled ? 'block' : 'none';
                }
            }
        });
    });

    // Handle geofencing dropdown change
    const geofencingDropdown = document.getElementById('geofencing-type');
    if (geofencingDropdown) {
        geofencingDropdown.addEventListener('change', function() {
            const customLocationsField = document.getElementById('custom-locations');
            if (customLocationsField) {
                customLocationsField.style.display = this.value === 'custom' ? 'block' : 'none';
            }
        });
    }

    // Add device functionality
    const addDeviceBtn = document.querySelector('.add-device-btn');
    if (addDeviceBtn) {
        addDeviceBtn.addEventListener('click', function() {
            const deviceList = document.querySelector('.device-list');
            if (deviceList) {
                // Create a new device item
                const deviceItem = document.createElement('div');
                deviceItem.className = 'device-item';
                deviceItem.innerHTML = `
                    <span>New Device</span>
                    <button class="remove-device">Remove</button>
                `;
                
                // Add remove functionality to the new device
                const removeBtn = deviceItem.querySelector('.remove-device');
                removeBtn.addEventListener('click', function() {
                    deviceItem.remove();
                });
                
                // Add the new device to the list
                deviceList.appendChild(deviceItem);
            }
        });
    }

    // Add remove device functionality to existing devices
    const removeDeviceBtns = document.querySelectorAll('.remove-device');
    removeDeviceBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const deviceItem = this.closest('.device-item');
            if (deviceItem) {
                deviceItem.remove();
            }
        });
    });

    // Save changes functionality
    const saveButton = document.querySelector('.btn-save');
    if (saveButton) {
        saveButton.addEventListener('click', function() {
            // Collect form data
            const employeeData = {
                // Basic info
                fullName: document.getElementById('employee-full-name').textContent,
                payrollNumber: document.getElementById('employee-payroll-number').textContent,
                badgeNumber: document.getElementById('employee-badge-number').textContent,
                roster: document.getElementById('employee-roster').value,
                
                // Personal details
                dob: document.getElementById('employee-dob').value,
                email: document.getElementById('employee-email').value,
                phone: document.getElementById('employee-phone').value,
                address: {
                    line1: document.getElementById('employee-address1').value,
                    line2: document.getElementById('employee-address2').value,
                    city: document.getElementById('employee-city').value,
                    state: document.getElementById('employee-state').value,
                    postalCode: document.getElementById('employee-postal').value,
                    country: document.getElementById('employee-country').value
                },
                emergency: {
                    name: document.getElementById('emergency-name').value,
                    relation: document.getElementById('emergency-relation').value,
                    phone: document.getElementById('emergency-phone').value,
                    email: document.getElementById('emergency-email').value
                },
                
                // Employment details
                jobTitle: document.getElementById('employee-job-title').value,
                department: document.getElementById('employee-department').value,
                hireDate: document.getElementById('employee-hire-date').value,
                contractType: document.getElementById('employee-contract-type').value,
                manager: document.getElementById('employee-manager').value,
                location: document.getElementById('employee-location').value,
                status: document.getElementById('employee-status').value,
                
                // Mobile clocking setup
                mobileSettings: {
                    gpsTracking: document.getElementById('gps-tracking').checked,
                    biometricAuth: document.getElementById('biometric-auth').checked,
                    manualEntry: document.getElementById('manual-entry').checked,
                    offlineMode: document.getElementById('offline-mode').checked,
                    photoVerification: document.getElementById('photo-verification').checked,
                    geofencingType: document.getElementById('geofencing-type').value
                }
            };
            
            // If termination details are filled out, include them
            if (document.getElementById('termination-date').value) {
                employeeData.termination = {
                    date: document.getElementById('termination-date').value,
                    reason: document.getElementById('termination-reason').value,
                    rehireEligibility: document.getElementById('rehire-eligibility').value,
                    settlementDate: document.getElementById('settlement-date').value,
                    notes: document.getElementById('termination-notes').value
                };
            }
            
            console.log('Saving employee data:', employeeData);
            
            // Here you would typically send this data to your server
            // For now, we'll just simulate a successful save
            setTimeout(() => {
                alert('Employee details saved successfully!');
                closeEmployeeModal();
            }, 500);
        });
    }

    // Function to fetch employee data (simulated)
    function fetchEmployeeData(employeeId) {
        console.log(`Fetching data for employee ID: ${employeeId}`);
        
        // In a real application, you would make an AJAX request to your server
        // For this example, we'll simulate a successful data fetch with setTimeout
        setTimeout(() => {
            // Populate the form with the fetched data
            // This is just example data - in a real app, this would come from your server
            document.getElementById('employee-full-name').textContent = 'John Smith';
            document.getElementById('employee-payroll-number').textContent = 'EMP001';
            document.getElementById('employee-badge-number').textContent = 'B12345';
            document.getElementById('employee-roster').value = 'standard';
            
            // Set the first tab as active by default
            switchTab('personal');
        }, 300);
    }

    // Request leave button functionality
    const requestLeaveBtn = document.querySelector('.request-leave-btn');
    if (requestLeaveBtn) {
        requestLeaveBtn.addEventListener('click', function() {
            // In a real application, this would open a leave request form or modal
            alert('Leave request functionality would open here.');
        });
    }

    // Upload document button functionality
    const uploadDocumentBtn = document.querySelector('.upload-document-btn');
    if (uploadDocumentBtn) {
        uploadDocumentBtn.addEventListener('click', function() {
            // In a real application, this would open a file upload dialog
            alert('Document upload functionality would open here.');
        });
    }

    // Process termination button functionality
    const processTerminationBtn = document.getElementById('process-termination');
    if (processTerminationBtn) {
        processTerminationBtn.addEventListener('click', function() {
            const terminationDate = document.getElementById('termination-date').value;
            const terminationReason = document.getElementById('termination-reason').value;
            
            if (!terminationDate || !terminationReason) {
                alert('Please fill in all required termination fields.');
                return;
            }
            
            if (confirm('Are you sure you want to process this termination? This action cannot be undone.')) {
                // In a real application, this would send the termination data to your server
                alert('Termination processed successfully.');
                
                // Update the employee status
                const statusDropdown = document.getElementById('employee-status');
                if (statusDropdown) {
                    statusDropdown.value = 'terminated';
                }
                
                // Update the status badge
                const statusBadge = document.querySelector('.status-badge');
                if (statusBadge) {
                    statusBadge.className = 'status-badge status-terminated';
                    statusBadge.textContent = 'Terminated';
                }
            }
        });
    }
  });

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
                badgeNumber: document.getElementById('employee-badge-number').textContent,
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

    fetchEmployeeData(employeeId) {
        console.log(`Fetching data for employee ID: ${employeeId}`);
        // Simulate API call
        setTimeout(() => {
            // Populate form with mock data
            document.getElementById('employee-full-name').textContent = 'John Smith';
            document.getElementById('employee-payroll-number').textContent = 'EMP001';
            document.getElementById('employee-badge-number').textContent = 'B12345';
        }, 300);
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
    const employeeList = document.getElementById('employee-list');
    if (!employeeList) return;

    employeeList.innerHTML = '';
    employees.forEach(employee => {
        const employeeCard = createEmployeeCard(employee);
        employeeList.appendChild(employeeCard);
    });
}

function createEmployeeCard(employee) {
    const card = document.createElement('div');
    card.className = 'employee-card';
    card.innerHTML = `
        <div class="employee-info">
            <h3>${employee.first_name} ${employee.last_name}</h3>
            <p>Employee #: ${employee.employee_number}</p>
            <p>Badge #: ${employee.badge_number}</p>
            <p>Pay Period: ${employee.pay_period}</p>
        </div>
        <div class="employee-actions">
            <button class="action-btn edit-btn" data-id="${employee.id}">
                <i class="material-icons">edit</i>
            </button>
            <button class="action-btn delete-btn" data-id="${employee.id}">
                <i class="material-icons">delete</i>
            </button>
        </div>
    `;
    return card;
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

    // Show modal
    addEmployeeBtn.addEventListener('click', () => {
        addEmployeeModal.style.display = 'block';
        loadScheduleTemplates();
    });

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
            badge_number: formData.get('badgeNumber'),
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