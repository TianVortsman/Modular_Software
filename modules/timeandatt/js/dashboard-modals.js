/**
 * Time and Attendance Dashboard Modals
 * This file contains functions to handle the modals on the dashboard
 */

// Modal open and close functions
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('hidden');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('hidden');
    }
}

// Initialize modal event listeners
document.addEventListener('DOMContentLoaded', function() {
    // Set up close events for all modals
    const closeButtons = document.querySelectorAll('.close');
    closeButtons.forEach(button => {
        button.addEventListener('click', function() {
            const modal = this.closest('.modal');
            if (modal) {
                modal.classList.add('hidden');
            }
        });
    });

    // Close modals when clicking outside the modal content
    document.querySelectorAll('.modal').forEach(modal => {
        modal.addEventListener('click', function(event) {
            if (event.target === this) {
                this.classList.add('hidden');
            }
        });
    });

    // Add Employee Modal
    const saveEmployeeBtn = document.getElementById('save-employee-btn');
    if (saveEmployeeBtn) {
        saveEmployeeBtn.addEventListener('click', saveEmployee);
    }

    // Manual Clock Entry Modal
    const saveClockBtn = document.getElementById('save-clock-btn');
    if (saveClockBtn) {
        saveClockBtn.addEventListener('click', saveClockEntry);
    }

    // Generate Report Modal
    const generateReportBtn = document.getElementById('generate-report-btn-submit');
    if (generateReportBtn) {
        generateReportBtn.addEventListener('click', generateReport);
    }

    // Set up report type change event to show/hide custom date range
    const reportType = document.getElementById('report-type');
    if (reportType) {
        reportType.addEventListener('change', function() {
            const dateRange = document.getElementById('custom-date-range');
            if (this.value === 'custom') {
                dateRange.classList.remove('hidden');
            } else {
                dateRange.classList.add('hidden');
            }
        });
    }
});

// Save employee function
function saveEmployee() {
    const accountNumber = document.querySelector('meta[name="account-number"]').content;
    const form = document.getElementById('add-employee-form');
    
    // Form validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Get form data
    const formData = {
        name: document.getElementById('employee-name').value,
        employee_id: document.getElementById('employee-id').value,
        department_id: document.getElementById('department').value,
        position: document.getElementById('position').value,
        shift_id: document.getElementById('shift').value,
        start_date: document.getElementById('start-date').value,
        account: accountNumber
    };

    // Show loading state
    showLoadingState('Saving employee...');

    // Send data to server
    fetch('../../api/timeandatt/add-employee.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingState();
        
        if (data.success) {
            // Show success message
            showResponse('success', 'Employee added successfully!');
            
            // Close modal and reset form
            closeModal('add-employee-modal');
            form.reset();
            
            // Refresh dashboard data
            initializeDashboard(accountNumber);
        } else {
            // Show error message
            showResponse('error', 'Failed to add employee: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        hideLoadingState();
        console.error('Error adding employee:', error);
        showResponse('error', 'An error occurred while adding the employee.');
    });
}

// Save clock entry function
function saveClockEntry() {
    const accountNumber = document.querySelector('meta[name="account-number"]').content;
    const form = document.getElementById('manual-clock-form');
    
    // Form validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Get form data
    const formData = {
        employee_id: document.getElementById('clock-employee').value,
        clock_date: document.getElementById('clock-date').value,
        clock_time: document.getElementById('clock-time').value,
        clock_type: document.getElementById('clock-type').value,
        notes: document.getElementById('clock-notes').value,
        account: accountNumber
    };

    // Show loading state
    showLoadingState('Saving clock entry...');

    // Send data to server
    fetch('../../api/timeandatt/add-clock-entry.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => response.json())
    .then(data => {
        hideLoadingState();
        
        if (data.success) {
            // Show success message
            showResponse('success', 'Clock entry added successfully!');
            
            // Close modal and reset form
            closeModal('manual-clock-modal');
            form.reset();
            
            // Refresh dashboard data
            initializeDashboard(accountNumber);
        } else {
            // Show error message
            showResponse('error', 'Failed to add clock entry: ' + (data.message || 'Unknown error'));
        }
    })
    .catch(error => {
        hideLoadingState();
        console.error('Error adding clock entry:', error);
        showResponse('error', 'An error occurred while adding the clock entry.');
    });
}

// Generate report function
function generateReport() {
    const accountNumber = document.querySelector('meta[name="account-number"]').content;
    const form = document.getElementById('report-form');
    
    // Form validation
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }

    // Get form data
    const reportType = document.getElementById('report-type').value;
    const formData = {
        report_type: reportType,
        department: document.getElementById('report-department').value,
        format: document.getElementById('report-format').value,
        account: accountNumber
    };

    // Add date range if custom report type
    if (reportType === 'custom') {
        formData.start_date = document.getElementById('report-start-date').value;
        formData.end_date = document.getElementById('report-end-date').value;
        
        // Validate date range
        if (!formData.start_date || !formData.end_date) {
            showResponse('error', 'Please select both start and end dates for custom reports.');
            return;
        }
        
        // Validate that end date is not before start date
        if (new Date(formData.end_date) < new Date(formData.start_date)) {
            showResponse('error', 'End date cannot be before start date.');
            return;
        }
    }

    // Show loading state
    showLoadingState('Generating report...');

    // Send data to server
    fetch('../../api/timeandatt/generate-report.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(formData)
    })
    .then(response => {
        hideLoadingState();
        
        if (response.ok) {
            // Check if it's returning JSON or a file
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json().then(data => {
                    if (data.success) {
                        showResponse('success', 'Report generated successfully!');
                    } else {
                        showResponse('error', 'Failed to generate report: ' + (data.message || 'Unknown error'));
                    }
                });
            } else {
                // It's a file download
                return response.blob().then(blob => {
                    // Create a temporary URL for the blob
                    const url = window.URL.createObjectURL(blob);
                    
                    // Create a link and click it to trigger download
                    const a = document.createElement('a');
                    a.href = url;
                    a.download = `attendance_report_${new Date().toISOString().split('T')[0]}.${formData.format}`;
                    document.body.appendChild(a);
                    a.click();
                    
                    // Clean up
                    window.URL.revokeObjectURL(url);
                    a.remove();
                    
                    // Close modal and show success message
                    closeModal('report-modal');
                    showResponse('success', 'Report downloaded successfully!');
                });
            }
        } else {
            throw new Error('Network response was not ok');
        }
    })
    .catch(error => {
        hideLoadingState();
        console.error('Error generating report:', error);
        showResponse('error', 'An error occurred while generating the report.');
    });
}

// Show response modal
function showResponse(type, message) {
    const modal = document.getElementById('response-modal');
    const title = document.getElementById('response-title');
    const messageEl = document.getElementById('response-message');
    
    // Set title and style based on type
    if (type === 'success') {
        title.textContent = 'Success';
        title.style.color = 'var(--color-success, #4CAF50)';
    } else if (type === 'error') {
        title.textContent = 'Error';
        title.style.color = 'var(--color-error, #F44336)';
    } else if (type === 'warning') {
        title.textContent = 'Warning';
        title.style.color = 'var(--color-warning, #FF9800)';
    } else if (type === 'info') {
        title.textContent = 'Information';
        title.style.color = 'var(--color-primary)';
    }
    
    messageEl.textContent = message;
    openModal('response-modal');
}

// Show loading state (placeholder - replace with actual implementation)
function showLoadingState(message = 'Loading...') {
    // This would typically show a loading spinner or modal
    console.log('Loading: ' + message);
    // Implement this based on your loading UI component
}

// Hide loading state (placeholder - replace with actual implementation)
function hideLoadingState() {
    // This would typically hide the loading spinner or modal
    console.log('Loading complete');
    // Implement this based on your loading UI component
} 