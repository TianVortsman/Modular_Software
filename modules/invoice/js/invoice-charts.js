document.addEventListener('DOMContentLoaded', function () {
    function getChartColors() {
        return {
            barPaid: '#4CAF50',
            barOverdue: '#F44336',
            gridColor: getComputedStyle(document.documentElement).getPropertyValue('--chart-grid-color').trim(),
            textColor: getComputedStyle(document.documentElement).getPropertyValue('--chart-text-color').trim(),
        };
    }

    console.log('Grid Color:', getChartColors().gridColor);
    console.log('Text Color:', getChartColors().textColor);

const colors = getChartColors();

const ctx = document.getElementById('invoiceChart').getContext('2d');
const invoiceChart = new Chart(ctx, {
    type: 'bar',
    data: {
        labels: ['January', 'February', 'March', 'April', 'May', 'June', 'July'],
        datasets: [
            {
                label: 'Invoices Paid',
                data: [12, 19, 8, 15, 20, 10, 14],
                backgroundColor: colors.barPaid,
                borderColor: colors.barPaid,
                borderWidth: 1,
            },
            {
                label: 'Overdue Invoices',
                data: [5, 6, 3, 7, 4, 5, 2],
                backgroundColor: colors.barOverdue,
                borderColor: colors.barOverdue,
                borderWidth: 1,
            },
        ],
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    color: colors.textColor,
                },
                grid: {
                    color: colors.gridColor,
                },
            },
            x: {
                ticks: {
                    color: colors.textColor,
                },
                grid: {
                    color: colors.gridColor,
                },
            },
        },
        plugins: {
            legend: {
                labels: {
                    color: colors.textColor,
                },
            },
        },
    },
});

});