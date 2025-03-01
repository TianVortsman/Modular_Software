document.addEventListener('DOMContentLoaded', () => {
    // Update KPI Summary with simulated data
    updateKPISummary();
  
    // Initialize charts
    const attendanceChart = initAttendanceChart();
    const deptShiftChart = initDeptShiftChart();
    const historicalChart = initHistoricalChart();
  
    // Simulate real-time activity feed updates
    simulateRealTimeActivity();
  
    // Attach event listener for historical data filtering
    document.getElementById('filter-data').addEventListener('click', () => {
      filterHistoricalData(historicalChart);
    });

    // Apply root variable styles to the page dynamically
    applyRootVariables();

    // Initialize device status table
    initDeviceStatusTable();

    // Add double-click event listeners to all widgets
    initWidgetDoubleClickListeners();

    // Initialize mobile metrics
    updateMobileMetrics();
});
  
// Function to update KPI Summary values with simulated data
function updateKPISummary() {
    document.getElementById('total-clocked-in').innerText = Math.floor(Math.random() * 100);
    document.getElementById('avg-checkin-time').innerText = "09:00 AM";
    document.getElementById('total-overtime').innerText = Math.floor(Math.random() * 20);
    document.getElementById('late-arrivals').innerText = Math.floor(Math.random() * 10);
}
  
// Initialize the attendance trends chart using Chart.js
function initAttendanceChart() {
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    return new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri'],
            datasets: [{
                label: 'Attendance',
                data: [65, 59, 80, 81, 56],
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--color-primary'),
                backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--color-primary-fade'),
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return 'Count: ' + context.parsed.y;
                        }
                    }
                }
            }
        }
    });
}
  
// Initialize the department/shift breakdown chart (doughnut chart)
function initDeptShiftChart() {
    const ctx = document.getElementById('deptShiftChart').getContext('2d');
    return new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Dept A', 'Dept B', 'Dept C'],
            datasets: [{
                data: [30, 50, 20],
                backgroundColor: [
                    getComputedStyle(document.documentElement).getPropertyValue('--color-hover'),
                    getComputedStyle(document.documentElement).getPropertyValue('--color-primary'),
                    getComputedStyle(document.documentElement).getPropertyValue('--color-secondary')
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom'
                }
            }
        }
    });
}
  
// Initialize the historical data chart (bar chart)
function initHistoricalChart() {
    const ctx = document.getElementById('historicalChart').getContext('2d');
    return new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['January', 'February', 'March', 'April', 'May'],
            datasets: [{
                label: 'Attendance',
                data: [45, 67, 80, 90, 100],
                backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--color-primary')
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        }
    });
}
  
// Simulate the real-time activity feed updates every 5 seconds
function simulateRealTimeActivity() {
    const activityFeed = document.getElementById('activity-feed');
    const alertsList = document.getElementById('alerts-list');
    
    // Initial activity items
    const activities = [
        "Employee 42 clocked in at 08:45 AM",
        "Employee 17 clocked out at 05:15 PM",
        "Employee 23 started break at 12:30 PM"
    ];
    
    // Initial alerts
    const alerts = [
        "Late arrival: Employee 15 (30 minutes)",
        "Missing clock-out: Employee 28 (yesterday)",
        "Overtime alert: Employee 7 (2 hours)"
    ];
    
    // Populate initial activities
    activities.forEach(activity => {
        const li = document.createElement('li');
        li.innerText = activity;
        activityFeed.appendChild(li);
    });
    
    // Populate initial alerts
    alerts.forEach(alert => {
        const li = document.createElement('li');
        li.innerText = alert;
        li.classList.add('alert-item');
        alertsList.appendChild(li);
    });
    
    // Add new activities periodically
    setInterval(() => {
        const li = document.createElement('li');
        const now = new Date();
        li.innerText = `Employee ${Math.floor(Math.random() * 100)} clocked in at ${now.toLocaleTimeString()}`;
        activityFeed.prepend(li);
        
        // Remove oldest item if more than 10
        if (activityFeed.children.length > 10) {
            activityFeed.removeChild(activityFeed.lastChild);
        }
    }, 5000);
    
    // Add new alerts less frequently
    setInterval(() => {
        const alertTypes = [
            "Late arrival: Employee",
            "Missing clock-out: Employee",
            "Overtime alert: Employee",
            "Early departure: Employee"
        ];
        
        const randomAlert = alertTypes[Math.floor(Math.random() * alertTypes.length)];
        const li = document.createElement('li');
        li.innerText = `${randomAlert} ${Math.floor(Math.random() * 50)} (${Math.floor(Math.random() * 60)} minutes)`;
        li.classList.add('alert-item');
        alertsList.prepend(li);
        
        // Remove oldest item if more than 8
        if (alertsList.children.length > 8) {
            alertsList.removeChild(alertsList.lastChild);
        }
    }, 12000);
}
  
// Filter historical data by updating the chart with simulated data (demo purpose)
function filterHistoricalData(chart) {
    const startDate = document.getElementById('start-date').value;
    const endDate = document.getElementById('end-date').value;
    // For demonstration, simply update the chart with new random data
    if (startDate && endDate) {
        chart.data.datasets[0].data = chart.data.datasets[0].data.map(() => Math.floor(Math.random() * 100));
        chart.update();
    }
}

// Function to apply root variables dynamically
function applyRootVariables() {
    const rootStyles = getComputedStyle(document.documentElement);
    
    // Example: Changing background colors of elements based on root variables
    document.querySelector('.dashboard-container').style.backgroundColor = rootStyles.getPropertyValue('--color-background').trim();
}

// Initialize device status table with sample data
function initDeviceStatusTable() {
    const tableBody = document.querySelector('#devices-table tbody');
    
    // Clear existing rows
    tableBody.innerHTML = '';
    
    // Sample device data
    const devices = [
        { name: 'Main Entrance Terminal', status: 'online', lastCheck: '2 minutes ago' },
        { name: 'Warehouse Scanner', status: 'offline', lastCheck: '3 hours ago' },
        { name: 'Office Biometric', status: 'online', lastCheck: '5 minutes ago' },
        { name: 'Mobile App Server', status: 'warning', lastCheck: '15 minutes ago' }
    ];
    
    // Add rows to the table
    devices.forEach(device => {
        const row = document.createElement('tr');
        row.innerHTML = `
            <td>${device.name}</td>
            <td><span class="status-indicator status-${device.status}"></span>${device.status}</td>
            <td>${device.lastCheck}</td>
        `;
        tableBody.appendChild(row);
    });
}

// Update mobile metrics with sample data
function updateMobileMetrics() {
    document.getElementById('mobile-usage').innerText = '68%';
    document.getElementById('mobile-success').innerText = '94%';
}

// Initialize double-click event listeners for all widgets
function initWidgetDoubleClickListeners() {
    const widgets = document.querySelectorAll('.widget');
    
    widgets.forEach(widget => {
        widget.addEventListener('dblclick', function() {
            const widgetType = this.dataset.widgetType;
            openWidgetModal(widgetType, this);
        });
    });
}

// Function to open a modal with detailed information based on widget type
function openWidgetModal(widgetType, widgetElement) {
    console.log(`Opening modal for widget type: ${widgetType}`);
    
    // Create modal container if it doesn't exist
    let modalContainer = document.getElementById('widget-modal-container');
    if (!modalContainer) {
        modalContainer = document.createElement('div');
        modalContainer.id = 'widget-modal-container';
        modalContainer.className = 'widget-modal-container';
        document.body.appendChild(modalContainer);
    }
    
    // Get widget title
    const widgetTitle = widgetElement.querySelector('.widget-header h3').textContent;
    
    // Create modal content based on widget type
    let modalContent = '';
    
    switch(widgetType) {
        case 'employees':
            modalContent = createEmployeesModalContent();
            break;
        case 'checkin':
            modalContent = createCheckinModalContent();
            break;
        case 'overtime':
            modalContent = createOvertimeModalContent();
            break;
        case 'late':
            modalContent = createLateModalContent();
            break;
        case 'attendance-chart':
            modalContent = createAttendanceChartModalContent();
            break;
        case 'dept-chart':
            modalContent = createDeptChartModalContent();
            break;
        case 'activity':
            modalContent = createActivityModalContent();
            break;
        case 'alerts':
            modalContent = createAlertsModalContent();
            break;
        case 'mobile-metrics':
            modalContent = createMobileMetricsModalContent();
            break;
        case 'devices':
            modalContent = createDevicesModalContent();
            break;
        case 'historical':
            modalContent = createHistoricalModalContent();
            break;
        case 'filters':
            modalContent = createFiltersModalContent();
            break;
        default:
            modalContent = `<p>Detailed view for ${widgetTitle} is not available.</p>`;
    }
    
    // Set modal HTML
    modalContainer.innerHTML = `
        <div class="widget-modal">
            <div class="widget-modal-header">
                <h2>${widgetTitle}</h2>
                <button class="widget-modal-close">&times;</button>
            </div>
            <div class="widget-modal-content">
                ${modalContent}
            </div>
            <div class="widget-modal-footer">
                <button class="widget-modal-close-btn">Close</button>
            </div>
        </div>
    `;
    
    // Show modal
    modalContainer.style.display = 'flex';
    
    // Add event listeners to close buttons
    const closeButtons = modalContainer.querySelectorAll('.widget-modal-close, .widget-modal-close-btn');
    closeButtons.forEach(button => {
        button.addEventListener('click', () => {
            modalContainer.style.display = 'none';
        });
    });
    
    // Close modal when clicking outside
    modalContainer.addEventListener('click', (event) => {
        if (event.target === modalContainer) {
            modalContainer.style.display = 'none';
        }
    });
    
    // Initialize any charts or special elements in the modal
    initModalSpecialElements(widgetType);
}

// Helper functions to create modal content for each widget type
function createEmployeesModalContent() {
    return `
        <div class="modal-section">
            <h3>Employees Currently Clocked In</h3>
            <div class="modal-filters">
                <select id="dept-filter">
                    <option value="all">All Departments</option>
                    <option value="admin">Administration</option>
                    <option value="sales">Sales</option>
                    <option value="warehouse">Warehouse</option>
                </select>
                <input type="text" placeholder="Search employees..." id="employee-search">
            </div>
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>Employee ID</th>
                        <th>Name</th>
                        <th>Department</th>
                        <th>Clock In</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>EMP001</td>
                        <td>John Smith</td>
                        <td>Sales</td>
                        <td>08:45 AM</td>
                        <td><span class="status-active">Active</span></td>
                    </tr>
                    <tr>
                        <td>EMP015</td>
                        <td>Sarah Johnson</td>
                        <td>Administration</td>
                        <td>09:02 AM</td>
                        <td><span class="status-break">On Break</span></td>
                    </tr>
                    <tr>
                        <td>EMP042</td>
                        <td>Michael Brown</td>
                        <td>Warehouse</td>
                        <td>08:30 AM</td>
                        <td><span class="status-active">Active</span></td>
                    </tr>
                    <tr>
                        <td>EMP023</td>
                        <td>Emily Davis</td>
                        <td>Sales</td>
                        <td>08:55 AM</td>
                        <td><span class="status-active">Active</span></td>
                    </tr>
                    <tr>
                        <td>EMP037</td>
                        <td>Robert Wilson</td>
                        <td>Administration</td>
                        <td>09:15 AM</td>
                        <td><span class="status-active">Active</span></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="modal-section">
            <h3>Attendance Summary</h3>
            <div class="modal-stats">
                <div class="stat-item">
                    <span class="stat-label">Expected Today:</span>
                    <span class="stat-value">42</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Present:</span>
                    <span class="stat-value">38</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">Absent:</span>
                    <span class="stat-value">4</span>
                </div>
                <div class="stat-item">
                    <span class="stat-label">On Leave:</span>
                    <span class="stat-value">2</span>
                </div>
            </div>
        </div>
    `;
}

function createCheckinModalContent() {
    return `
        <div class="modal-section">
            <h3>Check-In Time Analysis</h3>
            <div class="modal-filters">
                <select id="time-period">
                    <option value="today">Today</option>
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>
            <div class="chart-container-large">
                <canvas id="checkinTimeChart"></canvas>
            </div>
        </div>
        <div class="modal-section">
            <h3>Department Averages</h3>
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Average Check-In</th>
                        <th>Earliest</th>
                        <th>Latest</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Administration</td>
                        <td>08:55 AM</td>
                        <td>08:30 AM</td>
                        <td>09:15 AM</td>
                    </tr>
                    <tr>
                        <td>Sales</td>
                        <td>08:48 AM</td>
                        <td>08:30 AM</td>
                        <td>09:05 AM</td>
                    </tr>
                    <tr>
                        <td>Warehouse</td>
                        <td>08:35 AM</td>
                        <td>08:15 AM</td>
                        <td>08:50 AM</td>
                    </tr>
                    <tr>
                        <td>IT</td>
                        <td>09:05 AM</td>
                        <td>08:45 AM</td>
                        <td>09:30 AM</td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

function createOvertimeModalContent() {
    return `
        <div class="modal-section">
            <h3>Overtime Hours Distribution</h3>
            <div class="modal-filters">
                <select id="overtime-period">
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                    <option value="quarter">This Quarter</option>
                </select>
            </div>
            <div class="chart-container-large">
                <canvas id="overtimeDistributionChart"></canvas>
            </div>
        </div>
        <div class="modal-section">
            <h3>Top Overtime Employees</h3>
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Department</th>
                        <th>Overtime Hours</th>
                        <th>Cost</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Michael Brown</td>
                        <td>Warehouse</td>
                        <td>12.5</td>
                        <td>$312.50</td>
                    </tr>
                    <tr>
                        <td>Sarah Johnson</td>
                        <td>Administration</td>
                        <td>8.2</td>
                        <td>$246.00</td>
                    </tr>
                    <tr>
                        <td>John Smith</td>
                        <td>Sales</td>
                        <td>6.5</td>
                        <td>$227.50</td>
                    </tr>
                    <tr>
                        <td>Emily Davis</td>
                        <td>Sales</td>
                        <td>5.0</td>
                        <td>$175.00</td>
                    </tr>
                    <tr>
                        <td>Robert Wilson</td>
                        <td>Administration</td>
                        <td>4.5</td>
                        <td>$157.50</td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

function createLateModalContent() {
    return `
        <div class="modal-section">
            <h3>Late Arrivals Analysis</h3>
            <div class="modal-filters">
                <select id="late-period">
                    <option value="week">This Week</option>
                    <option value="month">This Month</option>
                </select>
            </div>
            <div class="chart-container-large">
                <canvas id="lateArrivalsChart"></canvas>
            </div>
        </div>
        <div class="modal-section">
            <h3>Recent Late Arrivals</h3>
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>Employee</th>
                        <th>Date</th>
                        <th>Expected</th>
                        <th>Actual</th>
                        <th>Minutes Late</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Emily Davis</td>
                        <td>Today</td>
                        <td>09:00 AM</td>
                        <td>09:15 AM</td>
                        <td>15</td>
                    </tr>
                    <tr>
                        <td>John Smith</td>
                        <td>Today</td>
                        <td>09:00 AM</td>
                        <td>09:10 AM</td>
                        <td>10</td>
                    </tr>
                    <tr>
                        <td>Sarah Johnson</td>
                        <td>Yesterday</td>
                        <td>09:00 AM</td>
                        <td>09:30 AM</td>
                        <td>30</td>
                    </tr>
                    <tr>
                        <td>Robert Wilson</td>
                        <td>Yesterday</td>
                        <td>09:00 AM</td>
                        <td>09:05 AM</td>
                        <td>5</td>
                    </tr>
                    <tr>
                        <td>Michael Brown</td>
                        <td>2 days ago</td>
                        <td>08:30 AM</td>
                        <td>08:45 AM</td>
                        <td>15</td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

function createAttendanceChartModalContent() {
    return `
        <div class="modal-section">
            <h3>Detailed Attendance Trends</h3>
            <div class="modal-filters">
                <select id="attendance-view">
                    <option value="daily">Daily</option>
                    <option value="weekly">Weekly</option>
                    <option value="monthly">Monthly</option>
                </select>
                <select id="attendance-metric">
                    <option value="headcount">Headcount</option>
                    <option value="percentage">Percentage</option>
                    <option value="hours">Hours Worked</option>
                </select>
            </div>
            <div class="chart-container-large">
                <canvas id="detailedAttendanceChart"></canvas>
            </div>
        </div>
        <div class="modal-section">
            <h3>Attendance by Department</h3>
            <div class="chart-container-medium">
                <canvas id="deptAttendanceChart"></canvas>
            </div>
        </div>
    `;
}

function createDeptChartModalContent() {
    return `
        <div class="modal-section">
            <h3>Department & Shift Analysis</h3>
            <div class="modal-filters">
                <select id="dept-view">
                    <option value="departments">By Department</option>
                    <option value="shifts">By Shift</option>
                    <option value="combined">Combined View</option>
                </select>
            </div>
            <div class="chart-container-large">
                <canvas id="detailedDeptShiftChart"></canvas>
            </div>
        </div>
        <div class="modal-section">
            <h3>Department Statistics</h3>
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>Department</th>
                        <th>Headcount</th>
                        <th>Avg. Hours</th>
                        <th>Attendance Rate</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Administration</td>
                        <td>12</td>
                        <td>7.8</td>
                        <td>95%</td>
                    </tr>
                    <tr>
                        <td>Sales</td>
                        <td>18</td>
                        <td>8.2</td>
                        <td>92%</td>
                    </tr>
                    <tr>
                        <td>Warehouse</td>
                        <td>15</td>
                        <td>8.5</td>
                        <td>90%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

function createActivityModalContent() {
    return `
        <div class="modal-section">
            <h3>Real-Time Activity Log</h3>
            <div class="modal-filters">
                <select id="activity-filter">
                    <option value="all">All Activities</option>
                    <option value="clock-in">Clock In</option>
                    <option value="clock-out">Clock Out</option>
                    <option value="break">Breaks</option>
                </select>
                <input type="text" placeholder="Search activities..." id="activity-search">
            </div>
            <div class="activity-log-container">
                <ul class="activity-log">
                    <li class="activity-item clock-in">
                        <span class="activity-time">09:45 AM</span>
                        <span class="activity-icon">login</span>
                        <span class="activity-text">Employee 42 (John Smith) clocked in</span>
                    </li>
                    <li class="activity-item clock-out">
                        <span class="activity-time">09:30 AM</span>
                        <span class="activity-icon">logout</span>
                        <span class="activity-text">Employee 15 (Sarah Johnson) clocked out</span>
                    </li>
                    <li class="activity-item break-start">
                        <span class="activity-time">09:15 AM</span>
                        <span class="activity-icon">timer</span>
                        <span class="activity-text">Employee 23 (Emily Davis) started break</span>
                    </li>
                    <li class="activity-item break-end">
                        <span class="activity-time">09:00 AM</span>
                        <span class="activity-icon">timer_off</span>
                        <span class="activity-text">Employee 37 (Robert Wilson) ended break</span>
                    </li>
                    <li class="activity-item clock-in">
                        <span class="activity-time">08:55 AM</span>
                        <span class="activity-icon">login</span>
                        <span class="activity-text">Employee 28 (Michael Brown) clocked in</span>
                    </li>
                    <li class="activity-item clock-in">
                        <span class="activity-time">08:45 AM</span>
                        <span class="activity-icon">login</span>
                        <span class="activity-text">Employee 19 (Jennifer Lee) clocked in</span>
                    </li>
                    <li class="activity-item clock-in">
                        <span class="activity-time">08:30 AM</span>
                        <span class="activity-icon">login</span>
                        <span class="activity-text">Employee 7 (David Clark) clocked in</span>
                    </li>
                </ul>
            </div>
        </div>
        <div class="modal-section">
            <h3>Activity Summary</h3>
            <div class="chart-container-medium">
                <canvas id="activitySummaryChart"></canvas>
            </div>
        </div>
    `;
}

// Complete the createAlertsModalContent function that was truncated
function createAlertsModalContent() {
    return `
        <div class="modal-section">
            <h3>Alerts & Notifications</h3>
            <div class="modal-filters">
                <select id="alert-filter">
                    <option value="all">All Alerts</option>
                    <option value="late">Late Arrivals</option>
                    <option value="missing">Missing Clock-outs</option>
                    <option value="overtime">Overtime</option>
                </select>
                <select id="alert-priority">
                    <option value="all">All Priorities</option>
                    <option value="high">High</option>
                    <option value="medium">Medium</option>
                    <option value="low">Low</option>
                </select>
            </div>
            <div class="alerts-container">
                <div class="alert-item high-priority">
                    <div class="alert-header">
                        <span class="alert-type">Missing Clock-out</span>
                        <span class="alert-priority-indicator">High</span>
                    </div>
                    <div class="alert-content">
                        <p>Employee 28 (Michael Brown) did not clock out yesterday.</p>
                    </div>
                    <div class="alert-actions">
                        <button class="alert-action">Resolve</button>
                        <button class="alert-action">Ignore</button>
                    </div>
                </div>
                <div class="alert-item medium-priority">
                    <div class="alert-header">
                        <span class="alert-type">Late Arrival</span>
                        <span class="alert-priority-indicator">Medium</span>
                    </div>
                    <div class="alert-content">
                        <p>Employee 15 (Sarah Johnson) arrived 30 minutes late today.</p>
                    </div>
                    <div class="alert-actions">
                        <button class="alert-action">Resolve</button>
                        <button class="alert-action">Ignore</button>
                    </div>
                </div>
                <div class="alert-item low-priority">
                    <div class="alert-header">
                        <span class="alert-type">Overtime Alert</span>
                        <span class="alert-priority-indicator">Low</span>
                    </div>
                    <div class="alert-content">
                        <p>Employee 7 (David Clark) has accumulated 2 hours of overtime this week.</p>
                    </div>
                    <div class="alert-actions">
                        <button class="alert-action">Resolve</button>
                        <button class="alert-action">Ignore</button>
                    </div>
                </div>
            </div>
        </div>
    `;
}

function createMobileMetricsModalContent() {
    return `
        <div class="modal-section">
            <h3>Mobile Clocking Metrics</h3>
            <div class="chart-container-large">
                <canvas id="mobileUsageChart"></canvas>
            </div>
        </div>
        <div class="modal-section">
            <h3>Mobile App Usage Statistics</h3>
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Current</th>
                        <th>Previous Period</th>
                        <th>Change</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Mobile Usage</td>
                        <td>68%</td>
                        <td>62%</td>
                        <td class="positive-change">+6%</td>
                    </tr>
                    <tr>
                        <td>Success Rate</td>
                        <td>94%</td>
                        <td>91%</td>
                        <td class="positive-change">+3%</td>
                    </tr>
                    <tr>
                        <td>Average Response Time</td>
                        <td>1.2s</td>
                        <td>1.5s</td>
                        <td class="positive-change">-0.3s</td>
                    </tr>
                    <tr>
                        <td>Daily Active Users</td>
                        <td>35</td>
                        <td>30</td>
                        <td class="positive-change">+5</td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

function createDevicesModalContent() {
    return `
        <div class="modal-section">
            <h3>Device Status Overview</h3>
            <div class="chart-container-medium">
                <canvas id="deviceStatusChart"></canvas>
            </div>
        </div>
        <div class="modal-section">
            <h3>Device Details</h3>
            <div class="modal-filters">
                <select id="device-filter">
                    <option value="all">All Devices</option>
                    <option value="online">Online</option>
                    <option value="offline">Offline</option>
                    <option value="warning">Warning</option>
                </select>
            </div>
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>Device Name</th>
                        <th>Location</th>
                        <th>Status</th>
                        <th>Last Check</th>
                        <th>Uptime</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Main Entrance Terminal</td>
                        <td>Front Office</td>
                        <td><span class="status-indicator status-online"></span>Online</td>
                        <td>2 minutes ago</td>
                        <td>99.8%</td>
                    </tr>
                    <tr>
                        <td>Warehouse Scanner</td>
                        <td>Warehouse</td>
                        <td><span class="status-indicator status-offline"></span>Offline</td>
                        <td>3 hours ago</td>
                        <td>85.2%</td>
                    </tr>
                    <tr>
                        <td>Office Biometric</td>
                        <td>HR Department</td>
                        <td><span class="status-indicator status-online"></span>Online</td>
                        <td>5 minutes ago</td>
                        <td>98.5%</td>
                    </tr>
                    <tr>
                        <td>Mobile App Server</td>
                        <td>Server Room</td>
                        <td><span class="status-indicator status-warning"></span>Warning</td>
                        <td>15 minutes ago</td>
                        <td>97.1%</td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

function createHistoricalModalContent() {
    return `
        <div class="modal-section">
            <h3>Historical Data Analysis</h3>
            <div class="modal-filters">
                <div class="filter-group">
                    <label for="modal-start-date">Start Date:</label>
                    <input type="date" id="modal-start-date" class="filter-input">
                </div>
                <div class="filter-group">
                    <label for="modal-end-date">End Date:</label>
                    <input type="date" id="modal-end-date" class="filter-input">
                </div>
                <div class="filter-group">
                    <label for="modal-metric">Metric:</label>
                    <select id="modal-metric" class="filter-select">
                        <option value="attendance">Attendance</option>
                        <option value="overtime">Overtime</option>
                        <option value="late">Late Arrivals</option>
                    </select>
                </div>
                <button id="modal-filter-data" class="filter-button">Apply Filter</button>
            </div>
            <div class="chart-container-large">
                <canvas id="modalHistoricalChart"></canvas>
            </div>
        </div>
        <div class="modal-section">
            <h3>Comparative Analysis</h3>
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>Period</th>
                        <th>Attendance Rate</th>
                        <th>Avg. Hours</th>
                        <th>Overtime</th>
                        <th>Late Arrivals</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Current Month</td>
                        <td>95%</td>
                        <td>7.8</td>
                        <td>42</td>
                        <td>15</td>
                    </tr>
                    <tr>
                        <td>Previous Month</td>
                        <td>93%</td>
                        <td>7.6</td>
                        <td>38</td>
                        <td>18</td>
                    </tr>
                    <tr>
                        <td>3 Months Ago</td>
                        <td>91%</td>
                        <td>7.5</td>
                        <td>45</td>
                        <td>22</td>
                    </tr>
                    <tr>
                        <td>6 Months Ago</td>
                        <td>89%</td>
                        <td>7.4</td>
                        <td>50</td>
                        <td>25</td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

function createFiltersModalContent() {
    return `
        <div class="modal-section">
            <h3>Advanced Filtering Options</h3>
            <div class="advanced-filters">
                <div class="filter-column">
                    <h4>Time Period</h4>
                    <div class="filter-group">
                        <label for="adv-start-date">Start Date:</label>
                        <input type="date" id="adv-start-date" class="filter-input">
                    </div>
                    <div class="filter-group">
                        <label for="adv-end-date">End Date:</label>
                        <input type="date" id="adv-end-date" class="filter-input">
                    </div>
                    <div class="filter-group">
                        <label for="adv-time-period">Preset Periods:</label>
                        <select id="adv-time-period" class="filter-select">
                            <option value="today">Today</option>
                            <option value="yesterday">Yesterday</option>
                            <option value="this-week">This Week</option>
                            <option value="last-week">Last Week</option>
                            <option value="this-month">This Month</option>
                            <option value="last-month">Last Month</option>
                            <option value="custom">Custom</option>
                        </select>
                    </div>
                </div>
                <div class="filter-column">
                    <h4>Employee Filters</h4>
                    <div class="filter-group">
                        <label for="adv-department">Department:</label>
                        <select id="adv-department" class="filter-select">
                            <option value="all">All Departments</option>
                            <option value="admin">Administration</option>
                            <option value="sales">Sales</option>
                            <option value="warehouse">Warehouse</option>
                            <option value="it">IT</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="adv-shift">Shift:</label>
                        <select id="adv-shift" class="filter-select">
                            <option value="all">All Shifts</option>
                            <option value="morning">Morning</option>
                            <option value="afternoon">Afternoon</option>
                            <option value="night">Night</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="adv-employee-search">Employee Search:</label>
                        <input type="text" id="adv-employee-search" class="filter-input" placeholder="Search by name or ID...">
                    </div>
                </div>
                <div class="filter-column">
                    <h4>Data Filters</h4>
                    <div class="filter-group">
                        <label for="adv-metric">Metric:</label>
                        <select id="adv-metric" class="filter-select">
                            <option value="attendance">Attendance</option>
                            <option value="hours">Hours Worked</option>
                            <option value="overtime">Overtime</option>
                            <option value="late">Late Arrivals</option>
                            <option value="early-departure">Early Departures</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label for="adv-threshold">Threshold:</label>
                        <input type="number" id="adv-threshold" class="filter-input" placeholder="Enter threshold value">
                    </div>
                    <div class="filter-group">
                        <label for="adv-comparison">Comparison:</label>
                        <select id="adv-comparison" class="filter-select">
                            <option value="greater">Greater Than</option>
                            <option value="less">Less Than</option>
                            <option value="equal">Equal To</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="filter-actions">
                <button id="apply-advanced-filters" class="primary-button">Apply Filters</button>
                <button id="reset-advanced-filters" class="secondary-button">Reset</button>
                <button id="save-filter-preset" class="tertiary-button">Save as Preset</button>
            </div>
        </div>
        <div class="modal-section">
            <h3>Saved Filter Presets</h3>
            <table class="modal-table">
                <thead>
                    <tr>
                        <th>Preset Name</th>
                        <th>Description</th>
                        <th>Last Used</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Late Arrivals - Sales</td>
                        <td>Shows late arrivals in Sales department</td>
                        <td>Yesterday</td>
                        <td>
                            <button class="action-button">Load</button>
                            <button class="action-button">Delete</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Overtime Report</td>
                        <td>Shows overtime hours for all departments</td>
                        <td>3 days ago</td>
                        <td>
                            <button class="action-button">Load</button>
                            <button class="action-button">Delete</button>
                        </td>
                    </tr>
                    <tr>
                        <td>Monthly Attendance</td>
                        <td>Shows attendance rates for current month</td>
                        <td>1 week ago</td>
                        <td>
                            <button class="action-button">Load</button>
                            <button class="action-button">Delete</button>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    `;
}

// Function to initialize special elements in modals (charts, etc.)
function initModalSpecialElements(widgetType) {
    switch(widgetType) {
        case 'checkin':
            initCheckinTimeChart();
            break;
        case 'overtime':
            initOvertimeDistributionChart();
            break;
        case 'late':
            initLateArrivalsChart();
            break;
        case 'attendance-chart':
            initDetailedAttendanceChart();
            initDeptAttendanceChart();
            break;
        case 'dept-chart':
            initDetailedDeptShiftChart();
            break;
        case 'activity':
            initActivitySummaryChart();
            break;
        case 'mobile-metrics':
            initMobileUsageChart();
            break;
        case 'devices':
            initDeviceStatusChart();
            break;
        case 'historical':
            initModalHistoricalChart();
            // Add event listener for the modal filter button
            document.getElementById('modal-filter-data')?.addEventListener('click', function() {
                updateModalHistoricalChart();
            });
            break;
    }
}

// Chart initialization functions for modals
function initCheckinTimeChart() {
    const ctx = document.getElementById('checkinTimeChart')?.getContext('2d');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['8:00 AM', '8:15 AM', '8:30 AM', '8:45 AM', '9:00 AM', '9:15 AM', '9:30 AM'],
            datasets: [{
                label: 'Check-in Count',
                data: [2, 5, 10, 15, 8, 3, 1],
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--color-primary'),
                backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--color-primary-fade'),
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function initOvertimeDistributionChart() {
    const ctx = document.getElementById('overtimeDistributionChart')?.getContext('2d');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['0-1h', '1-2h', '2-3h', '3-4h', '4-5h', '5h+'],
            datasets: [{
                label: 'Number of Employees',
                data: [15, 10, 8, 5, 3, 2],
                backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--color-primary')
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function initLateArrivalsChart() {
    const ctx = document.getElementById('lateArrivalsChart')?.getContext('2d');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday'],
            datasets: [{
                label: 'Late Arrivals',
                data: [5, 3, 4, 2, 6],
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--color-secondary'),
                backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--color-secondary-fade'),
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function initDetailedAttendanceChart() {
    const ctx = document.getElementById('detailedAttendanceChart')?.getContext('2d');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4'],
            datasets: [{
                label: 'Present',
                data: [42, 40, 38, 41],
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--color-primary'),
                backgroundColor: 'transparent'
            }, {
                label: 'Absent',
                data: [3, 5, 7, 4],
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--color-secondary'),
                backgroundColor: 'transparent'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function initDeptAttendanceChart() {
    const ctx = document.getElementById('deptAttendanceChart')?.getContext('2d');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Administration', 'Sales', 'Warehouse', 'IT'],
            datasets: [{
                label: 'Attendance Rate (%)',
                data: [95, 92, 90, 94],
                backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--color-primary')
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

function initDetailedDeptShiftChart() {
    const ctx = document.getElementById('detailedDeptShiftChart')?.getContext('2d');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Administration', 'Sales', 'Warehouse', 'IT'],
            datasets: [{
                label: 'Morning Shift',
                data: [8, 10, 12, 5],
                backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--color-primary')
            }, {
                label: 'Afternoon Shift',
                data: [4, 8, 3, 7],
                backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--color-secondary')
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                x: {
                    stacked: true
                },
                y: {
                    stacked: true
                }
            }
        }
    });
}

function initActivitySummaryChart() {
    const ctx = document.getElementById('activitySummaryChart')?.getContext('2d');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['Clock In', 'Clock Out', 'Break Start', 'Break End'],
            datasets: [{
                data: [35, 30, 20, 15],
                backgroundColor: [
                    getComputedStyle(document.documentElement).getPropertyValue('--color-primary'),
                    getComputedStyle(document.documentElement).getPropertyValue('--color-secondary'),
                    getComputedStyle(document.documentElement).getPropertyValue('--color-hover'),
                    getComputedStyle(document.documentElement).getPropertyValue('--color-background-alt')
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function initMobileUsageChart() {
    const ctx = document.getElementById('mobileUsageChart')?.getContext('2d');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            datasets: [{
                label: 'Mobile Usage (%)',
                data: [45, 52, 58, 63, 65, 68],
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--color-primary'),
                backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--color-primary-fade'),
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    max: 100
                }
            }
        }
    });
}

function initDeviceStatusChart() {
    const ctx = document.getElementById('deviceStatusChart')?.getContext('2d');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'pie',
        data: {
            labels: ['Online', 'Offline', 'Warning'],
            datasets: [{
                data: [12, 3, 2],
                backgroundColor: [
                    '#4CAF50', // Green for online
                    '#F44336', // Red for offline
                    '#FFC107'  // Yellow for warning
                ]
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function initModalHistoricalChart() {
    const ctx = document.getElementById('modalHistoricalChart')?.getContext('2d');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
            datasets: [{
                label: 'Attendance Rate (%)',
                data: [88, 89, 90, 91, 92, 93, 94, 93, 92, 93, 94, 95],
                borderColor: getComputedStyle(document.documentElement).getPropertyValue('--color-primary'),
                backgroundColor: getComputedStyle(document.documentElement).getPropertyValue('--color-primary-fade'),
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false
        }
    });
}

function updateModalHistoricalChart() {
    const startDate = document.getElementById('modal-start-date')?.value;
    const endDate = document.getElementById('modal-end-date')?.value;
    const metric = document.getElementById('modal-metric')?.value;
    
    // For demonstration, simply log the filter values
    console.log(`Filtering modal historical chart: Start=${startDate}, End=${endDate}, Metric=${metric}`);
    
    // In a real implementation, you would fetch data based on these filters
    // and update the chart accordingly
}