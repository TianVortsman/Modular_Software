// Modal Management
let currentEmployeeId = null;

function openTimecardModal(employeeId) {
    currentEmployeeId = employeeId;
    document.getElementById('timecardModalOverlay').style.display = 'block';
    document.getElementById('timecardModal').style.display = 'block';
    document.body.style.overflow = 'hidden';
    
    // Add click handlers to timecard rows after modal is opened
    setupTimecardRowHandlers();
}

function setupTimecardRowHandlers() {
    const timecardRows = document.querySelectorAll('#timecardModal .table tbody tr');
    timecardRows.forEach(row => {
        row.addEventListener('click', function(e) {
            const date = this.querySelector('td:first-child').textContent;
            const isTimeCell = e.target.hasAttribute('ondblclick');
            
            if (!isTimeCell) {
                openPunchesModal(currentEmployeeId, date);
            }
        });
    });
}

function closeTimecardModal() {
    document.getElementById('timecardModalOverlay').style.display = 'none';
    document.getElementById('timecardModal').style.display = 'none';
    document.body.style.overflow = 'auto';
}

function openTimeEditModal(event) {
    event.stopPropagation();
    document.getElementById('timeEditModal').style.display = 'block';
}

function closeTimeEditModal() {
    document.getElementById('timeEditModal').style.display = 'none';
}

function openPunchesModal(employeeId, date) {
    document.getElementById('punchesModal').style.display = 'block';
    // Update modal title with the date
    const modalTitle = document.querySelector('#punchesModal .modal-title');
    modalTitle.innerHTML = `
        <i class="bi bi-fingerprint me-2"></i>
        Daily Punches - ${date}
    `;
    loadPunchesData(employeeId, date);
}

function closePunchesModal() {
    document.getElementById('punchesModal').style.display = 'none';
}

function openLeaveModal() {
    document.getElementById('leaveModal').style.display = 'block';
}

function closeLeaveModal() {
    document.getElementById('leaveModal').style.display = 'none';
}

function openMassClockingsModal() {
    document.getElementById('massClockingsModal').style.display = 'block';
}

function closeMassClockingsModal() {
    document.getElementById('massClockingsModal').style.display = 'none';
}

function openShiftChangesModal() {
    document.getElementById('shiftChangesModal').style.display = 'block';
}

function closeShiftChangesModal() {
    document.getElementById('shiftChangesModal').style.display = 'none';
}

// Data Loading Functions
function loadPunchesData(employeeId, date) {
    // Simulate loading punches data
    const punchesTable = document.querySelector('#punchesModal .punches-table tbody');
    // Clear existing rows
    punchesTable.innerHTML = '';
    
    // Example data - replace with actual API call
    const punches = [
        { time: '08:00', type: 'IN', device: 'Main Entrance', status: 'Valid' },
        { time: '12:00', type: 'OUT', device: 'Side Door', status: 'Valid' },
        { time: '13:00', type: 'IN', device: 'Main Entrance', status: 'Valid' },
        { time: '17:00', type: 'OUT', device: 'Main Entrance', status: 'Valid' }
    ];

    punches.forEach(punch => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${punch.time}</td>
            <td>${punch.type}</td>
            <td>${punch.device}</td>
            <td><span class="badge bg-success">${punch.status}</span></td>
            <td>
                <button class="btn btn-sm btn-outline-primary btn-icon">
                    <i class="bi bi-pencil"></i>
                    Edit
                </button>
                <button class="btn btn-sm btn-outline-danger btn-icon">
                    <i class="bi bi-trash"></i>
                    Delete
                </button>
            </td>
        `;
        punchesTable.appendChild(row);
    });
}

// Event Listeners
document.addEventListener('DOMContentLoaded', function() {
    // Close modals when clicking overlay
    document.querySelectorAll('.modal-overlay').forEach(overlay => {
        overlay.addEventListener('click', function(e) {
            if (e.target === this) {
                closeTimecardModal();
                closePunchesModal();
                closeLeaveModal();
                closeMassClockingsModal();
                closeShiftChangesModal();
                closeTimeEditModal();
            }
        });
    });

    // Handle main table row clicks for timecard modal
    document.querySelectorAll('.employee-row').forEach(row => {
        row.addEventListener('click', function() {
            const employeeId = this.querySelector('td:first-child').textContent;
            openTimecardModal(employeeId);
        });
    });

    // Exception toggle functionality
    document.getElementById('exceptionToggle').addEventListener('change', function(e) {
        const showAll = e.target.checked;
        if (showAll) {
            document.querySelectorAll('.alert').forEach(alert => {
                alert.style.display = 'block';
            });
        } else {
            document.querySelectorAll('.alert-danger').forEach(alert => {
                alert.style.display = 'none';
            });
        }
    });

    // Handle escape key to close modals
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            closeTimecardModal();
            closeTimeEditModal();
            closePunchesModal();
            closeLeaveModal();
            closeMassClockingsModal();
            closeShiftChangesModal();
        }
    });

    // Handle custom shift selection
    const shiftPatternSelect = document.querySelector('#shiftChangesModal select');
    const customShiftTimes = document.querySelector('#customShiftTimes');
    
    if (shiftPatternSelect) {
        shiftPatternSelect.addEventListener('change', function() {
            customShiftTimes.style.display = this.value === 'custom' ? 'flex' : 'none';
            
            // Make custom time fields required only when custom shift is selected
            const timeInputs = customShiftTimes.querySelectorAll('input[type="time"]');
            timeInputs.forEach(input => {
                input.required = this.value === 'custom';
            });
        });
    }

    // Initialize datepicker defaults
    const dateInputs = document.querySelectorAll('input[type="date"]');
    dateInputs.forEach(input => {
        // Set min date to today
        const today = new Date().toISOString().split('T')[0];
        input.min = today;
    });

    // Form submissions
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            e.preventDefault();
            // Handle form submission
            console.log('Form submitted:', this.id);
            
            // Collect form data
            const formData = new FormData(this);
            const data = {};
            for (let [key, value] of formData.entries()) {
                data[key] = value;
            }
            
            // Log the collected data
            console.log('Form data:', data);
            
            // Close the respective modal after submission
            switch(this.id) {
                case 'leaveForm':
                    closeLeaveModal();
                    break;
                case 'massClockingsForm':
                    closeMassClockingsModal();
                    break;
                case 'shiftChangesForm':
                    closeShiftChangesModal();
                    break;
                case 'timeEditForm':
                    closeTimeEditModal();
                    break;
            }
        });
    });
}); 