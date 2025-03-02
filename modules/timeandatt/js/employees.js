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
        alert(`Employee details modal for ${employeeId} will be implemented later.`);
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