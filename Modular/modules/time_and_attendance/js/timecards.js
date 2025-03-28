/**
 * Timecards Management Script
 * Main file for the time and attendance module
 */

// Global Variables
let currentEmployeeId = null;
let currentEmployeeName = null;

// =======================================================
// INITIALIZATION - Run immediately
// =======================================================
(function() {
    console.log("Initializing timecards.js");
    
    // Add critical CSS
    const criticalStyle = document.createElement('style');
    criticalStyle.textContent = `
        /* Fix employee row hover */
        tr.employee-row {
            cursor: pointer !important;
            transition: background-color 0.2s ease-in-out !important;
        }
        tr.employee-row:hover {
            background-color: rgba(0, 0, 0, 0.05) !important;
        }
        
        /* Fix modal z-index issues */
        #timecardModal, #timecardModalOverlay {
            z-index: 1001 !important;
        }
        .subsidiary-modal, .modal:not(#timecardModal) {
            z-index: 1003 !important; 
        }
        .subsidiary-overlay, .modal-overlay:not(#timecardModalOverlay) {
            z-index: 1002 !important;
        }
        
        /* Time cell styling */
        .editable-cell {
            position: relative;
            cursor: pointer !important;
            background-color: rgba(var(--color-primary-rgb, 0, 120, 210), 0.05);
        }
        .editable-cell:hover {
            background-color: rgba(var(--color-primary-rgb, 0, 120, 210), 0.1) !important;
        }
        .editable-cell::after {
            content: "edit";
            font-family: "Material Icons";
            position: absolute;
            right: 5px;
            opacity: 0.5;
            font-size: 14px;
            color: var(--color-primary, #0078d2);
        }
        
        /* Fix modal visibility issues */
        .modal {
            display: none !important; /* Force hide all modals by default */
        }
        
        /* Only show active modals */
        .modal.active {
            display: flex !important;
        }
        
        /* Fix modal overlay visibility */
        .modal-overlay {
            display: none !important;
        }
    `;
    document.head.appendChild(criticalStyle);
    
    // Setup employee rows at different times to ensure it works
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', setupEmployeeRows);
    } else {
        setupEmployeeRows();
    }
    
    window.addEventListener('load', function() {
        // Force hide all modals on page load
        hideAllModals();
        
        // Make sure employee rows are clickable
        setupEmployeeRows();
    });
})();

// Event delegation fallback for employee rows
document.addEventListener('DOMContentLoaded', function() {
    // Add a delegated event listener to the document body
    // This ensures the double-click works even if direct binding fails
    document.body.addEventListener('dblclick', function(event) {
        // Find if the click is on or within an employee row
        const row = event.target.closest('tr.employee-row');
        if (row) {
            // Get the employee ID from the first cell
            const employeeId = row.querySelector('td:first-child')?.textContent.trim();
            if (employeeId) {
                console.log(`Delegated dblclick captured for employee: ${employeeId}`);
                openTimecardModal(employeeId);
                event.stopPropagation();
            }
        }
    });
    
    // Also fix any styling issues with a global rule
    const employeeRowsStyle = document.createElement('style');
    employeeRowsStyle.textContent = `
        /* Stronger selector to override any conflicting styles */
        table tbody tr.employee-row,
        tr[class*="employee-row"],
        tr.employee-row {
            cursor: pointer !important;
            transition: background-color 0.2s ease-in-out !important;
        }
        
        table tbody tr.employee-row:hover,
        tr[class*="employee-row"]:hover,
        tr.employee-row:hover {
            background-color: rgba(0, 0, 0, 0.1) !important;
        }
    `;
    document.head.appendChild(employeeRowsStyle);
    
    // Create a MutationObserver to watch for dynamically added rows
    const observer = new MutationObserver(function(mutations) {
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length) {
                const hasNewRows = Array.from(mutation.addedNodes).some(node => 
                    node.nodeType === 1 && (
                        node.classList?.contains('employee-row') ||
                        node.querySelector?.('.employee-row')
                    )
                );
                
                if (hasNewRows) {
                    console.log("New employee rows detected, re-initializing...");
                    setupEmployeeRows();
                }
            }
        });
    });
    
    // Start observing the document body for changes
    observer.observe(document.body, { 
        childList: true, 
        subtree: true 
    });
});

// =======================================================
// EMPLOYEE ROW SETUP
// =======================================================
function setupEmployeeRows() {
    console.log("Setting up employee rows");
    
    // Use a more inclusive selector to find employee rows
    const employeeRows = document.querySelectorAll('tr.employee-row, table tbody tr[class*="employee-row"]');
    console.log(`Found ${employeeRows.length} employee rows`);
    
    if (employeeRows.length === 0) {
        console.warn("No employee rows found - will rely on event delegation");
        return;
    }
    
    employeeRows.forEach(row => {
        // Remove any existing ondblclick attribute that might interfere
        row.removeAttribute('ondblclick');
        
        // Get employee ID
        const employeeId = row.querySelector('td:first-child')?.textContent.trim();
        if (!employeeId) return;
        
        // Add visual indicator for debug purposes
        row.style.position = 'relative';
        
        // Use both direct property assignment AND addEventListener for maximum compatibility
        row.ondblclick = function(event) {
            console.log(`Direct ondblclick for employee: ${employeeId}`);
            openTimecardModal(employeeId);
            event.stopPropagation();
        };
        
        row.addEventListener('dblclick', function(event) {
            console.log(`addEventListener dblclick for employee: ${employeeId}`);
            openTimecardModal(employeeId);
            event.stopPropagation();
        });
        
        // Mark as initialized
        row.classList.add('interactive-row');
        row.setAttribute('data-has-dblclick', 'true');
    });
}

// =======================================================
// MODAL MANAGEMENT
// =======================================================
function hideAllModals() {
    console.log("Force hiding all modals");
    
    // Get all modals and overlays
    const modals = document.querySelectorAll('.modal');
    const overlays = document.querySelectorAll('.modal-overlay');
    
    // Set display: none on all modals
    modals.forEach(modal => {
        modal.style.display = 'none';
        modal.classList.remove('active');
    });
    
    // Hide all overlays
    overlays.forEach(overlay => {
        overlay.style.display = 'none';
    });
    
    // Ensure body scrolling is enabled
    document.body.style.overflow = 'auto';
}

function closeAllModals(keepTimecardOpen = false) {
    if (keepTimecardOpen) {
        // Only close subsidiary modals
        const modals = document.querySelectorAll('.modal:not(#timecardModal)');
        const overlays = document.querySelectorAll('.modal-overlay:not(#timecardModalOverlay)');
        
        modals.forEach(modal => {
            modal.classList.remove('active');
            modal.style.display = 'none';
        });
        
        overlays.forEach(overlay => {
            overlay.style.display = 'none';
        });
    } else {
        // Close all modals
        hideAllModals();
    }
}

// Timecard Modal
function openTimecardModal(employeeId) {
    console.log(`Opening timecard for employee: ${employeeId}`);
    
    closeAllModals();
    
    currentEmployeeId = employeeId;
    
    // Get employee name
    const employeeRow = document.querySelector(`.employee-row td:first-child`);
    if (employeeRow) {
        const nameCell = employeeRow.closest('tr').querySelector('td:nth-child(2)');
        if (nameCell) {
            currentEmployeeName = nameCell.textContent.trim();
        }
    }
    
    const modal = document.getElementById('timecardModal');
    const overlay = document.getElementById('timecardModalOverlay');
    
    if (!modal || !overlay) {
        console.error("Timecard modal or overlay not found");
        return;
    }
    
    // Update employee name in modal
    const nameElement = document.getElementById('modal-employee-name');
    if (nameElement) {
        nameElement.textContent = currentEmployeeName || employeeId;
    }
    
    // Display modal
    overlay.style.display = 'block';
    modal.style.display = 'flex';
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Activate default tab
    activateTab('daily');
    
    // Set up cell handlers
    setTimeout(setupModalCells, 100);
}

function closeTimecardModal() {
    const modal = document.getElementById('timecardModal');
    const overlay = document.getElementById('timecardModalOverlay');
    
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
    
    if (overlay) {
        overlay.style.display = 'none';
    }
    
    document.body.style.overflow = 'auto';
}

// Time Edit Modal
function openTimeEditModal(event, timeType, currentValue) {
    // Keep timecard modal open
    closeAllModals(true);
    
    event.stopPropagation();
    
    const modal = document.getElementById('timeEditModal');
    const overlay = document.getElementById('timeEditModalOverlay');
    
    if (!modal || !overlay) return;
    
    // Set modal information
    setElementText('timeEditCurrentValue', currentValue);
    setElementValue('timeEditNewValue', currentValue);
    setElementText('timeEditEmployeeName', currentEmployeeName);
    setElementText('editTypeIndicator', `${timeType.charAt(0).toUpperCase() + timeType.slice(1)} Time Edit`);
    
    // Get date from row
    const dateCell = event.target.closest('tr').querySelector('td:first-child');
    const dateText = dateCell?.textContent.trim() || '';
    setElementText('timeEditDateInfo', dateText);
    
    // Get day of week
    if (dateText) {
        const date = new Date(dateText);
        const days = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
        setElementText('timeEditDay', days[date.getDay()]);
    }
    
    // Show modal
    overlay.style.display = 'block';
    modal.style.display = 'flex';
    modal.classList.add('active');
}

function closeTimeEditModal() {
    const modal = document.getElementById('timeEditModal');
    const overlay = document.getElementById('timeEditModalOverlay');
    
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
    
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Punches Modal
function openPunchesModal(event) {
    // Keep timecard modal open
    closeAllModals(true);
    
    const modal = document.getElementById('punchesModal');
    const overlay = document.getElementById('punchesModalOverlay');
    
    if (!modal || !overlay) return;
    
    // Get date from row
    const row = event.target.closest('tr');
    const dateCell = row?.querySelector('td:first-child');
    const dateText = dateCell?.textContent.trim() || '';
    
    // Set modal information
    setElementText('punchesEmployeeName', currentEmployeeName);
    setElementText('punchesDateInfo', dateText);
    
    // Show modal
    overlay.style.display = 'block';
    modal.style.display = 'flex';
    modal.classList.add('active');
}

function closePunchesModal() {
    const modal = document.getElementById('punchesModal');
    const overlay = document.getElementById('punchesModalOverlay');
    
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
    
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Leave Request Modal
function openLeaveModal() {
    // Keep timecard modal open
    closeAllModals(true);
    
    const modal = document.getElementById('leaveRequestModal');
    const overlay = document.getElementById('leaveRequestModalOverlay');
    
    if (!modal || !overlay) return;
    
    // Set employee name
    setElementText('leaveEmployeeName', currentEmployeeName);
    
    // Show modal
    overlay.style.display = 'block';
    modal.style.display = 'flex';
    modal.classList.add('active');
}

function closeLeaveRequestModal() {
    const modal = document.getElementById('leaveRequestModal');
    const overlay = document.getElementById('leaveRequestModalOverlay');
    
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
    
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Mass Clockings Modal
function openMassClockingsModal() {
    // Keep timecard modal open
    closeAllModals(true);
    
    const modal = document.getElementById('massClockingsModal');
    const overlay = document.getElementById('massClockingsModalOverlay');
    
    if (!modal || !overlay) return;
    
    // Set employee name
    setElementText('massClockingsEmployeeName', currentEmployeeName);
    
    // Set default dates
    const today = new Date();
    const nextWeek = new Date();
    nextWeek.setDate(today.getDate() + 7);
    
    setElementValue('massStartDate', formatDateForInput(today));
    setElementValue('massEndDate', formatDateForInput(nextWeek));
    
    // Update summary if needed
    if (typeof updateMassClockingSummary === 'function') {
        updateMassClockingSummary();
    }
    
    // Show modal
    overlay.style.display = 'block';
    modal.style.display = 'flex';
    modal.classList.add('active');
}

function closeMassClockingsModal() {
    const modal = document.getElementById('massClockingsModal');
    const overlay = document.getElementById('massClockingsModalOverlay');
    
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
    
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// Shift Changes Modal
function openShiftChangesModal() {
    // Keep timecard modal open
    closeAllModals(true);
    
    const modal = document.getElementById('shiftChangesModal');
    const overlay = document.getElementById('shiftChangesModalOverlay');
    
    if (!modal || !overlay) return;
    
    // Set employee name
    setElementText('shiftChangeEmployeeName', currentEmployeeName);
    
    // Set default dates
    const today = new Date();
    const nextWeek = new Date();
    nextWeek.setDate(today.getDate() + 7);
    
    setElementValue('shiftStartDate', formatDateForInput(today));
    setElementValue('shiftEndDate', formatDateForInput(nextWeek));
    
    // Update summary if needed
    if (typeof updateShiftChangeSummary === 'function') {
        updateShiftChangeSummary();
    }
    
    // Show modal
    overlay.style.display = 'block';
    modal.style.display = 'flex';
    modal.classList.add('active');
}

function closeShiftChangesModal() {
    const modal = document.getElementById('shiftChangesModal');
    const overlay = document.getElementById('shiftChangesModalOverlay');
    
    if (modal) {
        modal.classList.remove('active');
        modal.style.display = 'none';
    }
    
    if (overlay) {
        overlay.style.display = 'none';
    }
}

// =======================================================
// TIMECARD CELL SETUP
// =======================================================
function setupModalCells() {
    console.log("Setting up modal cell handlers");
    
    const timecardTable = document.querySelector('#timecardModal .table tbody');
    if (!timecardTable) {
        console.log("Timecard table not found");
        return;
    }
    
    // Find and set up time cells
    const timeCells = document.querySelectorAll('#timecardModal .table tbody td:nth-child(2), #timecardModal .table tbody td:nth-child(3)');
    console.log(`Found ${timeCells.length} time cells to set up`);
    
    timeCells.forEach(cell => {
        // Remove any existing handlers
        cell.removeAttribute('ondblclick');
        
        // Mark as editable
        cell.classList.add('editable-cell');
        
        // Add double-click handler
        cell.ondblclick = function(event) {
            event.stopPropagation();
            
            // Determine time type based on column
            const isStartTime = cell.cellIndex === 1;
            const timeType = isStartTime ? 'start' : 'end';
            const currentValue = cell.textContent.trim();
            
            openTimeEditModal(event, timeType, currentValue);
        };
    });
    
    // Set up row double-click for punches
    const rows = timecardTable.querySelectorAll('tr');
    rows.forEach(row => {
        row.ondblclick = function(event) {
            // Only trigger if not clicking a time cell
            if (!event.target.classList.contains('editable-cell')) {
                event.stopPropagation();
                openPunchesModal(event);
            }
        };
    });
}

// =======================================================
// TAB MANAGEMENT
// =======================================================
function activateTab(tabName) {
    // Hide all tabs
    document.querySelectorAll('.tab-content').forEach(tab => {
        tab.classList.remove('active');
    });
    
    // Remove active class from buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        btn.classList.remove('active');
    });
    
    // Activate the selected tab
    const tabContent = document.getElementById(`${tabName}-tab`);
    if (tabContent) {
        tabContent.classList.add('active');
    }
    
    // Activate the corresponding button
    const tabButton = document.querySelector(`.tab-btn[data-tab="${tabName}"]`);
    if (tabButton) {
        tabButton.classList.add('active');
    }
}

// =======================================================
// NAVIGATION
// =======================================================
function navigateEmployee(direction) {
    // Get all employee rows
    const employees = Array.from(document.querySelectorAll('.employee-row'));
    if (employees.length === 0) return;
    
    // Find the index of the current employee
    const currentIndex = employees.findIndex(row => {
        return row.querySelector('td:first-child').textContent.trim() === currentEmployeeId;
    });
    
    if (currentIndex === -1) return;
    
    // Calculate the new index
    let newIndex;
    if (direction === 'next') {
        newIndex = (currentIndex + 1) % employees.length;
    } else {
        newIndex = (currentIndex - 1 + employees.length) % employees.length;
    }
    
    // Get the ID of the new employee and open their modal
    const newEmployeeId = employees[newIndex].querySelector('td:first-child').textContent.trim();
    
    closeTimecardModal();
    setTimeout(() => {
        openTimecardModal(newEmployeeId);
    }, 100);
}

// =======================================================
// UTILITY FUNCTIONS
// =======================================================
function setElementText(elementId, text) {
    const element = document.getElementById(elementId);
    if (element) {
        element.textContent = text;
    }
}

function setElementValue(elementId, value) {
    const element = document.getElementById(elementId);
    if (element) {
        element.value = value;
    }
}

function formatDateForInput(date) {
    return date.toISOString().split('T')[0];
}

function calculateDays(startDate, endDate) {
    if (!startDate || !endDate) return 0;
    
    const start = new Date(startDate);
    const end = new Date(endDate);
    
    const diffTime = Math.abs(end - start);
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24)) + 1;
    
    return diffDays;
}

function formatDate(dateString) {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    const options = { year: 'numeric', month: 'short', day: 'numeric' };
    return date.toLocaleDateString('en-US', options);
}

function formatTime(timeString) {
    if (!timeString) return '';
    
    const [hours, minutes] = timeString.split(':');
    const hour = parseInt(hours, 10);
    const ampm = hour >= 12 ? 'PM' : 'AM';
    const hour12 = hour % 12 || 12;
    
    return `${hour12}:${minutes} ${ampm}`;
}

// Leave type selection
function selectLeaveType(type) {
    const element = document.getElementById(type);
    if (element) {
        element.checked = true;
    }
}

// =======================================================
// EVENT LISTENERS
// =======================================================
document.addEventListener('DOMContentLoaded', function() {
    console.log("DOM loaded, setting up event listeners");
    
    // Tab buttons
    document.querySelectorAll('.tab-btn').forEach(btn => {
        if (btn) {
            btn.addEventListener('click', function() {
                const tabName = this.getAttribute('data-tab');
                if (tabName) {
                    activateTab(tabName);
                }
            });
        }
    });
    
    // Exception item clicks
    document.querySelectorAll('.exception-item').forEach(item => {
        if (item) {
            const employeeId = item.getAttribute('data-employee-id');
            if (employeeId) {
                item.ondblclick = function() {
                    openTimecardModal(employeeId);
                };
            }
        }
    });
    
    // Modal overlay clicks
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                // Close the appropriate modal
                const modalId = this.id.replace('Overlay', '');
                const closeFunction = window[`close${modalId.charAt(0).toUpperCase() + modalId.slice(1)}`];
                
                if (typeof closeFunction === 'function') {
                    closeFunction();
                } else {
                    // Fallback to closing all modals
                    closeAllModals();
                }
            }
        });
    });
    
    // ESC key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeAllModals();
        }
    });
    
    // =======================================================
    // FORM SETUP
    // =======================================================
    
    // Leave request form
    const leaveRequestForm = document.getElementById('leaveRequestForm');
    if (leaveRequestForm) {
        // Leave type options
        document.querySelectorAll('.leave-type-option').forEach(option => {
            option.addEventListener('click', function() {
                const radio = this.querySelector('input[type="radio"]');
                if (radio) radio.checked = true;
            });
        });
        
        // Date fields
        ['startDate', 'endDate', 'startTime', 'endTime'].forEach(id => {
            const element = document.getElementById(id);
            if (element) {
                element.addEventListener('change', updateLeaveDays);
            }
        });
        
        // Form submission
        leaveRequestForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (typeof openLeaveConfirmation === 'function') {
                openLeaveConfirmation();
            }
        });
    }
    
    // Mass clockings form
    const massClockingsForm = document.getElementById('massClockingsForm');
    if (massClockingsForm) {
        // Break time toggle
        const includeBreakEl = document.getElementById('includeBreak');
        const breakTimeSectionEl = document.getElementById('breakTimeSection');
        
        if (includeBreakEl && breakTimeSectionEl) {
            includeBreakEl.addEventListener('change', function() {
                breakTimeSectionEl.style.display = this.checked ? 'block' : 'none';
            });
        }
        
        // Update summary when inputs change
        ['massStartDate', 'massEndDate', 'massClockIn', 'massClockOut'].forEach(id => {
            const element = document.getElementById(id);
            if (element && typeof updateMassClockingSummary === 'function') {
                element.addEventListener('change', updateMassClockingSummary);
            }
        });
        
        // Checkboxes
        document.querySelectorAll('input[name="massDays[]"]').forEach(checkbox => {
            if (checkbox && typeof updateMassClockingSummary === 'function') {
                checkbox.addEventListener('change', updateMassClockingSummary);
            }
        });
        
        // Form submission
        massClockingsForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (typeof openMassClockingConfirm === 'function') {
                openMassClockingConfirm();
            }
        });
    }
    
    // Shift changes form
    const shiftChangesForm = document.getElementById('shiftChangesForm');
    if (shiftChangesForm) {
        // Change type toggle
        const endDateContainer = document.getElementById('endDateContainer');
        
        document.querySelectorAll('input[name="changeType"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (endDateContainer) {
                    endDateContainer.style.display = this.value === 'permanent' ? 'none' : 'block';
                }
                if (typeof updateShiftChangeSummary === 'function') {
                    updateShiftChangeSummary();
                }
            });
        });
        
        // Shift pattern toggle
        const customShiftFields = document.getElementById('customShiftFields');
        
        document.querySelectorAll('input[name="shiftPattern"]').forEach(radio => {
            radio.addEventListener('change', function() {
                if (customShiftFields) {
                    customShiftFields.style.display = this.value === 'custom' ? 'block' : 'none';
                }
                if (typeof updateShiftChangeSummary === 'function') {
                    updateShiftChangeSummary();
                }
            });
        });
        
        // Date fields
        ['shiftStartDate', 'shiftEndDate'].forEach(id => {
            const element = document.getElementById(id);
            if (element && typeof updateShiftChangeSummary === 'function') {
                element.addEventListener('change', updateShiftChangeSummary);
            }
        });
        
        // Form submission
        shiftChangesForm.addEventListener('submit', function(e) {
            e.preventDefault();
            if (typeof openShiftChangeConfirm === 'function') {
                openShiftChangeConfirm();
            }
        });
    }
    
    // Helper function for leave days calculation
    function updateLeaveDays() {
        const startDateEl = document.getElementById('startDate');
        const endDateEl = document.getElementById('endDate');
        
        if (!startDateEl || !endDateEl) return;
        
        const startDate = startDateEl.value;
        const endDate = endDateEl.value;
        
        if (startDate && endDate) {
            const days = calculateDays(startDate, endDate);
            
            setElementText('totalDays', `${days} day${days !== 1 ? 's' : ''}`);
            setElementText('workDays', days);
            setElementText('leaveHours', days * 8);
        } else {
            setElementText('totalDays', '0 days');
            setElementText('workDays', '0');
            setElementText('leaveHours', '0');
        }
    }
}); 