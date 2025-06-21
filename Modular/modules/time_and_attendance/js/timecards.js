/**
 * Timecards Management Script
 * Main file for the time and attendance module
 */

// Global variables
let currentPage = 1;
let totalPages = 1;
let perPage = 20;
let currentEmployee = null;
let employeeCache = {};

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
    
    currentEmployee = null;
    
    // Get employee name
    const employeeRow = document.querySelector(`.employee-row td:first-child`);
    if (employeeRow) {
        const nameCell = employeeRow.closest('tr').querySelector('td:nth-child(2)');
        if (nameCell) {
            currentEmployee = { employee: { id: employeeId, name: nameCell.textContent.trim() } };
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
        nameElement.textContent = currentEmployee?.employee.name || employeeId;
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
    setElementText('timeEditEmployeeName', currentEmployee?.employee.name);
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
    setElementText('punchesEmployeeName', currentEmployee?.employee.name);
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
    setElementText('leaveEmployeeName', currentEmployee?.employee.name);
    
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
    setElementText('massClockingsEmployeeName', currentEmployee?.employee.name);
    
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
    setElementText('shiftChangeEmployeeName', currentEmployee?.employee.name);
    
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
    if (!currentEmployee) return;
    
    const rows = document.querySelectorAll('.employee-row');
    const employeeIds = Array.from(rows).map(row => row.getAttribute('data-employee-id'));
    const currentIndex = employeeIds.indexOf(currentEmployee.employee.id.toString());
    
    if (currentIndex === -1) return;
    
    let newIndex;
    if (direction === 'next') {
        newIndex = currentIndex + 1 >= employeeIds.length ? 0 : currentIndex + 1;
    } else {
        newIndex = currentIndex - 1 < 0 ? employeeIds.length - 1 : currentIndex - 1;
    }
    
    const newEmployeeId = employeeIds[newIndex];
    loadEmployeeTimecardDetails(newEmployeeId);
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

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Set initial pay period dates (weekly by default)
    initializePayPeriodDates();
    
    // Add event listeners
    initializeEventListeners();
    
    // Load timecard data
    loadEmployeeTimecards();
    
    // Load exception summary for the sidebar
    loadExceptionSummary();
});

// Initialize pay period dates based on the current date
function initializePayPeriodDates() {
    const payPeriodType = document.getElementById('pay-period-type')?.value || 'weekly';
    updateDateRangeForPayPeriod(payPeriodType);
}

// Update date range based on selected pay period type
function updateDateRangeForPayPeriod(periodType) {
    const today = new Date();
    let startDate = new Date(today);
    let endDate = new Date(today);
    
    // Reset to start of day
    startDate.setHours(0, 0, 0, 0);
    
    // Calculate the start of the current pay period
    switch (periodType) {
        case 'weekly':
            // Start from the beginning of current week (Monday)
            const dayOfWeek = today.getDay() || 7; // Convert Sunday (0) to 7
            startDate.setDate(today.getDate() - dayOfWeek + 1); // Monday
            endDate.setDate(startDate.getDate() + 6); // Sunday
            break;
            
        case 'biweekly':
            // Start from beginning of 2-week period (assume starts on 1st and 15th)
            if (today.getDate() <= 15) {
                startDate.setDate(1);
                endDate.setDate(15);
            } else {
                startDate.setDate(16);
                endDate.setMonth(endDate.getMonth() + 1, 0); // Last day of current month
            }
            break;
            
        case 'monthly':
            // Start from beginning of current month
            startDate.setDate(1);
            endDate.setMonth(endDate.getMonth() + 1, 0); // Last day of current month
            break;
    }
    
    // Set the date inputs
    document.getElementById('start-date').valueAsDate = startDate;
    document.getElementById('end-date').valueAsDate = endDate;
}

// Initialize all event listeners
function initializeEventListeners() {
    // Search functionality
    const searchInput = document.querySelector('.search-input');
    if (searchInput) {
        searchInput.addEventListener('keyup', function(e) {
            if (e.key === 'Enter') {
                loadEmployeeTimecards();
            }
        });
    }
    
    // Pay Period Type
    const payPeriodSelect = document.getElementById('pay-period-type');
    if (payPeriodSelect) {
        payPeriodSelect.addEventListener('change', function() {
            updateDateRangeForPayPeriod(this.value);
            loadEmployeeTimecards();
        });
    }
    
    // Date range filters
    const startDateInput = document.getElementById('start-date');
    const endDateInput = document.getElementById('end-date');
    
    if (startDateInput && endDateInput) {
        startDateInput.addEventListener('change', function() {
            if (endDateInput.value && startDateInput.value > endDateInput.value) {
                endDateInput.value = startDateInput.value;
            }
            loadEmployeeTimecards();
        });
        
        endDateInput.addEventListener('change', function() {
            if (startDateInput.value && endDateInput.value < startDateInput.value) {
                startDateInput.value = endDateInput.value;
            }
            loadEmployeeTimecards();
        });
    }
    
    // Filter button
    const filterButton = document.querySelector('.page-actions .btn-secondary');
    if (filterButton) {
        filterButton.addEventListener('click', loadEmployeeTimecards);
    }
    
    // Status filter
    const statusSelect = document.querySelector('.status-filter select');
    if (statusSelect) {
        statusSelect.addEventListener('change', loadEmployeeTimecards);
    }
    
    // Pagination
    document.querySelectorAll('.pagination-btn').forEach(button => {
        button.addEventListener('click', function() {
            const action = this.querySelector('.material-icons').textContent;
            handlePagination(action);
        });
    });
    
    // Tab navigation in the timecard modal
    document.querySelectorAll('.tab-btn').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            activateTab(tabId);
        });
    });
}

// Load employee timecard data from API
function loadEmployeeTimecards() {
    // Show loading state
    const tableBody = document.querySelector('.timecard-table tbody');
    if (tableBody) {
        tableBody.innerHTML = '<tr><td colspan="8" class="loading-message">Loading employee data...</td></tr>';
    }
    
    // Get filter values
    const searchTerm = document.querySelector('.search-input').value;
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    const statusFilter = document.querySelector('.status-filter select').value;
    const payPeriodType = document.getElementById('pay-period-type').value;
    
    // Build API URL
    let apiUrl = `../api/timecard-api.php?action=employee_timecards&page=${currentPage}&per_page=${perPage}`;
    
    if (searchTerm) {
        apiUrl += `&search=${encodeURIComponent(searchTerm)}`;
    }
    
    if (startDate) {
        apiUrl += `&start_date=${encodeURIComponent(startDate)}`;
    }
    
    if (endDate) {
        apiUrl += `&end_date=${encodeURIComponent(endDate)}`;
    }
    
    if (payPeriodType) {
        apiUrl += `&pay_period=${encodeURIComponent(payPeriodType)}`;
    }
    
    if (statusFilter && statusFilter !== 'all') {
        apiUrl += `&status=${encodeURIComponent(statusFilter)}`;
    }
    
    // Fetch data from API
    fetch(apiUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayEmployeeTimecards(data.data);
                updatePagination(data.pagination);
            } else {
                throw new Error(data.message || 'Failed to load employee data');
            }
        })
        .catch(error => {
            console.error('Error fetching employee timecard data:', error);
            if (tableBody) {
                tableBody.innerHTML = `<tr><td colspan="8" class="error-message">Error loading data: ${error.message}</td></tr>`;
            }
        });
}

// Display employee timecard data in the table
function displayEmployeeTimecards(employees) {
    const tableBody = document.querySelector('.timecard-table tbody');
    if (!tableBody) return;
    
    // Clear existing rows
    tableBody.innerHTML = '';
    
    if (employees.length === 0) {
        tableBody.innerHTML = '<tr><td colspan="8" class="empty-message">No employee records found</td></tr>';
        return;
    }
    
    // Add rows for each employee
    employees.forEach((employee, index) => {
        const row = document.createElement('tr');
        row.className = 'employee-row interactive-row';
        row.setAttribute('data-employee-id', employee.employee_id);
        
        // Set double-click event
        row.ondblclick = function() {
            openTimecardModal(employee.employee_id);
        };
        
        // Also add a regular click event for mobile compatibility
        row.addEventListener('click', function() {
            // Use a timer to distinguish between single and double clicks
            if (!this.clickTimer) {
                this.clickTimer = setTimeout(() => {
                    this.clickTimer = null;
                    // Single click action here if needed
                }, 300);
            }
        });
        
        // Add all columns
        row.innerHTML = `
            <td>${employee.employee_number}</td>
            <td>${employee.name}</td>
            <td>${employee.regular_hours.toFixed(1)}</td>
            <td>${employee.ot_hours_15x.toFixed(1)}</td>
            <td>${employee.ot_hours_20x.toFixed(1)}</td>
            <td>${employee.total_hours.toFixed(1)}</td>
            <td><span class="badge badge-${getBadgeClass(employee.exception_count)}">${employee.exception_count}</span></td>
            <td><span class="status-indicator status-${employee.status}">${capitalizeFirstLetter(employee.status)}</span></td>
        `;
        
        tableBody.appendChild(row);
    });
}

// Update pagination display
function updatePagination(pagination) {
    if (!pagination) return;
    
    totalPages = pagination.total_pages;
    currentPage = pagination.current_page;
    
    const paginationInfo = document.querySelector('.pagination-info');
    if (paginationInfo) {
        paginationInfo.textContent = `Page ${currentPage} of ${totalPages}`;
    }
}

// Handle pagination actions
function handlePagination(action) {
    switch (action) {
        case 'first_page':
            currentPage = 1;
            break;
        case 'chevron_left':
            currentPage = Math.max(1, currentPage - 1);
            break;
        case 'chevron_right':
            currentPage = Math.min(totalPages, currentPage + 1);
            break;
        case 'last_page':
            currentPage = totalPages;
            break;
    }
    
    loadEmployeeTimecards();
}

// Load exception summary for the sidebar
function loadExceptionSummary() {
    const exceptionsContainer = document.querySelector('.exceptions-list');
    const summaryContainer = document.querySelector('.summary-content');
    
    if (!exceptionsContainer || !summaryContainer) return;
    
    fetch('../api/timecard-api.php?action=exception_summary')
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                displayExceptions(data.data.exceptions, exceptionsContainer);
                displaySummary(data.data.summary, summaryContainer);
            } else {
                throw new Error(data.message || 'Failed to load exception data');
            }
        })
        .catch(error => {
            console.error('Error fetching exception data:', error);
            exceptionsContainer.innerHTML = `<div class="error-message">Error loading exceptions: ${error.message}</div>`;
        });
}

// Display exception data in the sidebar
function displayExceptions(exceptions, container) {
    if (!exceptions || !container) return;
    
    container.innerHTML = '';
    
    if (exceptions.length === 0) {
        container.innerHTML = '<div class="empty-message">No exceptions found</div>';
        return;
    }
    
    exceptions.forEach(exception => {
        const formattedDate = exception.date ? new Date(exception.date).toLocaleDateString('en-US', { year: 'numeric', month: 'short', day: 'numeric' }) : '';
        const datePeriod = exception.date_period || '';
        
        const exceptionElement = document.createElement('div');
        exceptionElement.className = `exception-item exception-${exception.type}`;
        exceptionElement.setAttribute('data-employee-id', exception.employee_id);
        exceptionElement.ondblclick = function() {
            openTimecardModal(exception.employee_id);
        };
        
        exceptionElement.innerHTML = `
            <div class="exception-icon">
                <span class="material-icons">${getExceptionIcon(exception.title)}</span>
            </div>
            <div class="exception-details">
                <div class="exception-title">${exception.title}</div>
                <div class="exception-info">${exception.name} - ${formattedDate} ${datePeriod}</div>
            </div>
        `;
        
        container.appendChild(exceptionElement);
    });
}

// Display summary data in the sidebar
function displaySummary(summary, container) {
    if (!summary || !container) return;
    
    const summaryItems = [
        { label: 'Total Employees', value: summary.total_employees, class: '' },
        { label: 'Pending Approval', value: summary.pending_approval, class: 'highlight-warning' },
        { label: 'With Exceptions', value: summary.with_exceptions, class: 'highlight-danger' },
        { label: 'Total Hours', value: summary.total_hours.toFixed(1), class: '' },
        { label: 'Overtime Hours', value: summary.overtime_hours.toFixed(1), class: 'highlight-info' }
    ];
    
    container.innerHTML = '';
    
    summaryItems.forEach(item => {
        const summaryElement = document.createElement('div');
        summaryElement.className = 'summary-item';
        
        summaryElement.innerHTML = `
            <div class="summary-label">${item.label}</div>
            <div class="summary-value ${item.class}">${item.value}</div>
        `;
        
        container.appendChild(summaryElement);
    });
}

// Load employee timecard details for the modal
function loadEmployeeTimecardDetails(employeeId) {
    // Show loading state in the modal
    const modalContent = document.querySelector('#timecardModal .modal-content');
    modalContent.innerHTML = '<div class="loading-spinner">Loading employee timecard data...</div>';
    
    // Get date range from filters
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    
    // Check if we have cached data for this employee
    const cacheKey = `${employeeId}-${startDate}-${endDate}`;
    if (employeeCache[cacheKey]) {
        displayEmployeeTimecardDetails(employeeCache[cacheKey]);
        return;
    }
    
    // Fetch data from API
    fetch(`../api/timecard-api.php?action=employee_timecard_details&id=${employeeId}&start_date=${startDate}&end_date=${endDate}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            if (data.success) {
                // Cache the data
                employeeCache[cacheKey] = data.data;
                currentEmployee = data.data;
                
                // Display the data
                displayEmployeeTimecardDetails(data.data);
            } else {
                throw new Error(data.message || 'Failed to load employee timecard details');
            }
        })
        .catch(error => {
            console.error('Error fetching employee timecard details:', error);
            modalContent.innerHTML = `<div class="error-message">Error loading employee timecard details: ${error.message}</div>`;
        });
}

// Display employee timecard details in the modal
function displayEmployeeTimecardDetails(data) {
    if (!data) return;
    
    // Update employee name and ID
    document.getElementById('modal-employee-name').textContent = data.employee.name;
    document.querySelector('.employee-id').textContent = data.employee.employee_number;
    
    // Update employee details
    const employeeDetails = document.createElement('div');
    employeeDetails.className = 'employee-details-card';
    
    employeeDetails.innerHTML = `
        <div class="employee-info">
            <div class="employee-photo">
                <span class="material-icons photo-placeholder">account_circle</span>
            </div>
            <div class="employee-data">
                <div class="detail-item">
                    <span class="material-icons">business</span>
                    <span class="detail-label">Division:</span>
                    <span class="detail-value">${data.employee.division}</span>
                </div>
                <div class="detail-item">
                    <span class="material-icons">account_tree</span>
                    <span class="detail-label">Department:</span>
                    <span class="detail-value">${data.employee.department}</span>
                </div>
                <div class="detail-item">
                    <span class="material-icons">group</span>
                    <span class="detail-label">Group:</span>
                    <span class="detail-value">${data.employee.group}</span>
                </div>
                <div class="detail-item">
                    <span class="material-icons">attach_money</span>
                    <span class="detail-label">Cost Centre:</span>
                    <span class="detail-value">${data.employee.cost_center}</span>
                </div>
            </div>
        </div>
        <div class="timecard-summary">
            <div class="summary-tile">
                <div class="tile-label">Regular Hours</div>
                <div class="tile-value">${data.timecard_summary.regular_hours.toFixed(1)}</div>
            </div>
            <div class="summary-tile">
                <div class="tile-label">OT (1.5x)</div>
                <div class="tile-value">${data.timecard_summary.ot_hours_15x.toFixed(1)}</div>
            </div>
            <div class="summary-tile">
                <div class="tile-label">OT (2.0x)</div>
                <div class="tile-value">${data.timecard_summary.ot_hours_20x.toFixed(1)}</div>
            </div>
            <div class="summary-tile">
                <div class="tile-label">Total Hours</div>
                <div class="tile-value">${data.timecard_summary.total_hours.toFixed(1)}</div>
            </div>
        </div>
    `;
    
    // Get pay period type and date range info
    const payPeriodType = document.getElementById('pay-period-type').value || 'weekly';
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    
    // Create a period info section
    const periodInfo = document.createElement('div');
    periodInfo.className = 'period-info-card';
    periodInfo.innerHTML = `
        <div class="period-info-header">
            <h3>
                <span class="material-icons">date_range</span>
                Pay Period: ${capitalizeFirstLetter(payPeriodType)}
            </h3>
            <div class="period-dates">
                ${formatDate(startDate)} - ${formatDate(endDate)}
            </div>
        </div>
    `;
    
    // Create tabs and content
    const tabsContainer = document.createElement('div');
    tabsContainer.className = 'timecard-tabs';
    
    tabsContainer.innerHTML = `
        <div class="tab-header">
            <button class="tab-btn active" data-tab="daily">Daily View</button>
            <button class="tab-btn" data-tab="weekly">Weekly View</button>
            <button class="tab-btn" data-tab="exceptions">Exceptions</button>
        </div>
        
        <div class="tab-content active" id="daily-tab">
            <div class="table-container">
                <table class="table">
                    <thead>
                        <tr>
                            <th><span class="material-icons">event</span> Date</th>
                            <th><span class="material-icons">today</span> Day</th>
                            <th><span class="material-icons">schedule</span> Start Time</th>
                            <th><span class="material-icons">schedule</span> End Time</th>
                            <th><span class="material-icons">work</span> Shift</th>
                            <th><span class="material-icons">description</span> Description</th>
                            <th><span class="material-icons">schedule</span> Regular</th>
                            <th><span class="material-icons">schedule</span> OT (1.5x)</th>
                            <th><span class="material-icons">schedule</span> OT (2.0x)</th>
                            <th><span class="material-icons">restaurant</span> Breaks</th>
                            <th><span class="material-icons">calculate</span> Total</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody id="daily-timecard-rows">
                        ${data.timecard_days.map((day, index) => `
                            <tr class="day-row" data-date="${day.date}">
                                <td>${formatDateShort(day.date)}</td>
                                <td>${day.day_of_week}</td>
                                <td ondblclick="openTimeEditModal(event, 'start', '${day.start_time || ''}')">${day.start_time || '-'}</td>
                                <td ondblclick="openTimeEditModal(event, 'end', '${day.end_time || ''}')">${day.end_time || '-'}</td>
                                <td>${day.shift}</td>
                                <td>${day.description}</td>
                                <td>${day.regular_hours.toFixed(1)}</td>
                                <td>${day.ot_hours_15x.toFixed(1)}</td>
                                <td>${day.ot_hours_20x.toFixed(1)}</td>
                                <td ondblclick="openBreaksModal(event)">${day.breaks.toFixed(1)}</td>
                                <td>${day.total_hours.toFixed(1)}</td>
                                <td>
                                    <button class="btn btn-icon toggle-clockings" data-index="${index}">
                                        <span class="material-icons">expand_more</span>
                                    </button>
                                </td>
                            </tr>
                            <tr class="clockings-row" id="clockings-${index}" style="display: none;">
                                <td colspan="12">
                                    <div class="clockings-container">
                                        <h4>Clockings for ${formatDateShort(day.date)}</h4>
                                        ${day.clocking_records && day.clocking_records.length > 0 ? `
                                            <table class="clockings-table">
                                                <thead>
                                                    <tr>
                                                        <th>Time In</th>
                                                        <th>Time Out</th>
                                                        <th>Status</th>
                                                        <th>Notes</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    ${day.clocking_records.map(record => `
                                                        <tr>
                                                            <td>${record.time_in || '-'}</td>
                                                            <td>${record.time_out || '-'}</td>
                                                            <td>${record.status || '-'}</td>
                                                            <td>${record.notes || '-'}</td>
                                                        </tr>
                                                    `).join('')}
                                                </tbody>
                                            </table>
                                        ` : `
                                            <div class="empty-message">No clock records found for this day</div>
                                        `}
                                    </div>
                                </td>
                            </tr>
                        `).join('')}
                    </tbody>
                </table>
            </div>
        </div>
        
        <div class="tab-content" id="weekly-tab">
            <div class="weekly-view-placeholder">Weekly view will be displayed here</div>
        </div>
        
        <div class="tab-content" id="exceptions-tab">
            <div class="exception-list">
                ${data.exceptions.length > 0 ? 
                    data.exceptions.map(exception => `
                        <div class="exception-card">
                            <div class="exception-header ${exception.type}">
                                <span class="material-icons">${getExceptionIcon(exception.title)}</span>
                                <span>${exception.title}</span>
                                <span class="exception-date">${new Date(exception.date).toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' })}</span>
                            </div>
                            <div class="exception-body">
                                <p>${exception.description}</p>
                                <div class="exception-actions">
                                    <button class="btn btn-small" ondblclick="openTimeEditModal(event, 'end', '')">
                                        <span class="material-icons">edit</span>
                                        Fix
                                    </button>
                                </div>
                            </div>
                        </div>
                    `).join('') : 
                    '<div class="empty-message">No exceptions found for this employee</div>'
                }
            </div>
        </div>
    `;
    
    // Update footer status
    const footerInfo = document.querySelector('.modal-footer .footer-info');
    if (footerInfo) {
        footerInfo.innerHTML = `
            <span class="status-label">Status:</span>
            <span class="status-value status-${data.timecard_status}">${capitalizeFirstLetter(data.timecard_status)}</span>
        `;
    }
    
    // Set the modal content
    const modalContent = document.querySelector('#timecardModal .modal-content');
    modalContent.innerHTML = '';
    modalContent.appendChild(employeeDetails);
    modalContent.appendChild(periodInfo);
    modalContent.appendChild(tabsContainer);
    
    // Add tab event listeners
    document.querySelectorAll('.tab-btn').forEach(tab => {
        tab.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            activateTab(tabId);
        });
    });
    
    // Add toggle listeners for clocking details
    document.querySelectorAll('.toggle-clockings').forEach(button => {
        button.addEventListener('click', function() {
            const index = this.getAttribute('data-index');
            const clockingsRow = document.getElementById(`clockings-${index}`);
            
            if (clockingsRow) {
                const isVisible = clockingsRow.style.display !== 'none';
                clockingsRow.style.display = isVisible ? 'none' : 'table-row';
                
                // Update icon
                const icon = this.querySelector('.material-icons');
                icon.textContent = isVisible ? 'expand_more' : 'expand_less';
            }
        });
    });
}

// Format date for display (short format)
function formatDateShort(dateString) {
    if (!dateString) return '';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('en-US', { 
        month: 'short', 
        day: 'numeric'
    });
}

// Utility functions
function getBadgeClass(count) {
    if (count === 0) return 'success';
    if (count === 1) return 'warning';
    return 'danger';
}

function getExceptionIcon(title) {
    switch (title) {
        case 'Missed Punch':
            return 'error_outline';
        case 'Absenteeism':
            return 'cancel';
        case 'Late Arrival':
            return 'watch_later';
        case 'Early Departure':
            return 'schedule';
        default:
            return 'warning';
    }
}

function capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
} 