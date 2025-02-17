document.addEventListener('DOMContentLoaded', function () {

// Revenue Chart (Bar Chart Example)
var revenueOptions = {
    chart: {
        type: 'bar',  // Bar chart type
        height: 300,  // Adjust the height of the chart
        toolbar: {
            show: false  // Hides the toolbar (we don't need it here)
        }
    },
    series: [
        {
            name: 'Revenue',
            data: [5000, 7000, 8000, 9000, 11000]  // Sample revenue data per quarter
        }
    ],
    xaxis: {
        categories: ['Q1', 'Q2', 'Q3', 'Q4', 'Q1'],  // Quarters or months
        labels: {
            style: {
                colors: '#ffffff',  // Text color for x-axis labels
                fontSize: '14px',
                fontWeight: 'bold'
            }
        }
    },
    yaxis: {
        labels: {
            style: {
                colors: '#ffffff',  // Text color for y-axis labels
                fontSize: '14px'
            }
        }
    },
    fill: {
        type: 'gradient',  // Gradient fill for the bars
        gradient: {
            shade: 'dark',
            type: 'vertical',
            gradientToColors: ['#FFD700'],  // Gold color for the gradient effect
            stops: [0, 100]
        }
    },
    colors: ['#FFD700'],  // Bar color
    tooltip: {
        theme: 'dark',
        y: {
            formatter: function (value) {
                return '$' + value + 'K';  // Format the tooltip with a dollar sign
            }
        }
    },
    dataLabels: {
        enabled: true,  // Show data labels
        style: {
            colors: ['#ffffff'],
            fontSize: '14px',
            fontWeight: 'bold'
        }
    },
    plotOptions: {
        bar: {
            horizontal: false,  // Vertical bars
            columnWidth: '50%',  // Adjust the width of the bars
            borderRadius: 8  // Rounded edges for the bars
        }
    },
    stroke: {
        show: true,
        width: 2,  // Border width for the bars
        colors: ['#333']  // Border color
    }
};

// Expenses Chart (Pie Chart Example)
var expensesOptions = {
    chart: {
        type: 'pie',  // Pie chart type
        height: 300  // Set height for the chart
    },
    series: [40, 30, 20, 10],  // Expense distribution (Example data)
    labels: ['Marketing', 'Salaries', 'Rent', 'Miscellaneous'],  // Categories
    colors: ['#FF5733', '#33FF57', '#5733FF', '#FFC300'],  // Colors for each segment
    legend: {
        position: 'bottom',
        labels: {
            colors: '#ffffff'  // Text color for legend
        }
    },
    tooltip: {
        theme: 'dark',
        y: {
            formatter: function (value) {
                return value + '%';  // Format the tooltip for percentages
            }
        }
    }
};

// Profit & Loss Chart (Area Chart Example)
var profitLossOptions = {
    chart: {
        type: 'area',  // Area chart type
        height: 300
    },
    series: [
        {
            name: 'Profit',
            data: [3000, 5000, 7000, 4000, 6000]  // Example profit data
        },
        {
            name: 'Loss',
            data: [1000, 1500, 1200, 1800, 1000]  // Example loss data
        }
    ],
    colors: ['#4CAF50', '#F44336'],  // Green for profit, red for loss
    xaxis: {
        categories: ['Jan', 'Feb', 'Mar', 'Apr', 'May'],
        labels: {
            style: {
                colors: '#ffffff',
                fontSize: '14px'
            }
        }
    },
    yaxis: {
        labels: {
            style: {
                colors: '#ffffff',
                fontSize: '14px'
            }
        }
    },
    fill: {
        type: 'gradient',
        gradient: {
            shade: 'light',
            gradientToColors: ['#4CAF50', '#F44336'],
            stops: [0, 100]
        }
    },
    tooltip: {
        theme: 'dark',
        y: {
            formatter: function (value) {
                return '$' + value;  // Format the tooltip to show the currency
            }
        }
    },
    dataLabels: {
        enabled: true,
        style: {
            colors: '#ffffff',
            fontSize: '14px',
            fontWeight: 'bold'
        }
    }
};

// Initialize the charts
var revenueChart = new ApexCharts(document.getElementById('revenue-chart'), revenueOptions);
var expensesChart = new ApexCharts(document.getElementById('expenses-chart'), expensesOptions);
var profitLossChart = new ApexCharts(document.getElementById('profit-loss-chart'), profitLossOptions);

// Render the charts
revenueChart.render();
expensesChart.render();
profitLossChart.render();
});