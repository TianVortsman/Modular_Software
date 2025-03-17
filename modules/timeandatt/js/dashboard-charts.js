/**
 * Time and Attendance Dashboard Charts
 * This file contains functions to initialize and update charts on the dashboard
 */

// Initialize charts when data is available
function initializeCharts(chartData) {
    // Initialize attendance chart
    initializeAttendanceChart(chartData.attendance || getDefaultAttendanceData());
    
    // Initialize department/shift breakdown chart
    initializeDepartmentChart(chartData.departments || getDefaultDepartmentData());
}

// Initialize the main attendance trends chart
function initializeAttendanceChart(data) {
    const ctx = document.getElementById('attendanceChart').getContext('2d');
    
    // Create the chart
    const attendanceChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: data.labels,
            datasets: [
                {
                    label: 'Present',
                    data: data.present,
                    borderColor: '#4CAF50',
                    backgroundColor: 'rgba(76, 175, 80, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Late',
                    data: data.late,
                    borderColor: '#FF9800',
                    backgroundColor: 'rgba(255, 152, 0, 0.1)',
                    tension: 0.4,
                    fill: true
                },
                {
                    label: 'Absent',
                    data: data.absent,
                    borderColor: '#F44336',
                    backgroundColor: 'rgba(244, 67, 54, 0.1)',
                    tension: 0.4,
                    fill: true
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'top',
                    labels: {
                        color: getComputedStyle(document.documentElement).getPropertyValue('--color-text-light')
                    }
                },
                tooltip: {
                    mode: 'index',
                    intersect: false
                }
            },
            scales: {
                x: {
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: getComputedStyle(document.documentElement).getPropertyValue('--color-text-light')
                    }
                },
                y: {
                    beginAtZero: true,
                    grid: {
                        color: 'rgba(255, 255, 255, 0.1)'
                    },
                    ticks: {
                        color: getComputedStyle(document.documentElement).getPropertyValue('--color-text-light')
                    }
                }
            }
        }
    });
    
    // Store the chart instance for potential updates
    window.attendanceChart = attendanceChart;
}

// Initialize the department breakdown chart
function initializeDepartmentChart(data) {
    const ctx = document.getElementById('deptShiftChart').getContext('2d');
    
    // Create the chart
    const deptChart = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: data.labels,
            datasets: [
                {
                    data: data.values,
                    backgroundColor: [
                        '#4CAF50', '#2196F3', '#FF9800', '#9C27B0', 
                        '#E91E63', '#00BCD4', '#FFEB3B', '#795548'
                    ],
                    borderWidth: 1
                }
            ]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'right',
                    labels: {
                        color: getComputedStyle(document.documentElement).getPropertyValue('--color-text-light')
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            let label = context.label || '';
                            let value = context.raw || 0;
                            let total = context.dataset.data.reduce((a, b) => a + b, 0);
                            let percentage = Math.round((value * 100) / total) + '%';
                            return `${label}: ${value} (${percentage})`;
                        }
                    }
                }
            }
        }
    });
    
    // Store the chart instance for potential updates
    window.deptChart = deptChart;
}

// Update the attendance chart with new data
function updateAttendanceChart(data) {
    if (window.attendanceChart) {
        window.attendanceChart.data.labels = data.labels;
        window.attendanceChart.data.datasets[0].data = data.present;
        window.attendanceChart.data.datasets[1].data = data.late;
        window.attendanceChart.data.datasets[2].data = data.absent;
        window.attendanceChart.update();
    } else {
        // Initialize if not yet created
        initializeAttendanceChart(data);
    }
}

// Update the department chart with new data
function updateDepartmentChart(data) {
    if (window.deptChart) {
        window.deptChart.data.labels = data.labels;
        window.deptChart.data.datasets[0].data = data.values;
        window.deptChart.update();
    } else {
        // Initialize if not yet created
        initializeDepartmentChart(data);
    }
}

// Default data for attendance chart if no data is provided
function getDefaultAttendanceData() {
    const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
    
    return {
        labels: days,
        present: [23, 25, 24, 22, 20, 5, 2],
        late: [3, 2, 4, 5, 3, 1, 0],
        absent: [2, 1, 0, 1, 5, 0, 0]
    };
}

// Default data for department chart if no data is provided
function getDefaultDepartmentData() {
    return {
        labels: ['Administration', 'Production', 'Sales', 'IT', 'HR', 'Finance'],
        values: [8, 12, 10, 6, 4, 5]
    };
}

// Handle theme changes to update chart colors
document.addEventListener('themeChanged', function(e) {
    const textColor = getComputedStyle(document.documentElement).getPropertyValue('--color-text-light');
    const gridColor = 'rgba(255, 255, 255, 0.1)';
    
    // Update attendance chart colors
    if (window.attendanceChart) {
        window.attendanceChart.options.scales.x.ticks.color = textColor;
        window.attendanceChart.options.scales.y.ticks.color = textColor;
        window.attendanceChart.options.scales.x.grid.color = gridColor;
        window.attendanceChart.options.scales.y.grid.color = gridColor;
        window.attendanceChart.options.plugins.legend.labels.color = textColor;
        window.attendanceChart.update();
    }
    
    // Update department chart colors
    if (window.deptChart) {
        window.deptChart.options.plugins.legend.labels.color = textColor;
        window.deptChart.update();
    }
}); 