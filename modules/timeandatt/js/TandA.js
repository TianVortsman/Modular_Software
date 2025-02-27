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
    setInterval(() => {
        const li = document.createElement('li');
        const now = new Date();
        li.innerText = `Employee ${Math.floor(Math.random() * 100)} clocked in at ${now.toLocaleTimeString()}`;
        activityFeed.prepend(li);
    }, 5000);
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
    document.querySelector('.kpi-cards').style.gap = rootStyles.getPropertyValue('--spacing-medium').trim();
}
