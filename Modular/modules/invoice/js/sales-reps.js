function openAddSalesRepModal() {
    // Display the modal
    document.querySelector(".add-sales-rep-modal").style.display = "flex";
}

function closeAddSalesRepModal() {
    // Hide the modal

    document.querySelector(".add-sales-rep-modal").style.display = "none";
}
// Close modal when clicking outside of it
window.addEventListener("click", function(event) {
    const modal = document.querySelector(".add-sales-rep-modal");
    if (event.target === modal) {
        closeAddSalesRepModal();
    }
});

// Wait for the DOM to load
document.addEventListener("DOMContentLoaded", function () {
  // Radial Gauge: Deals Won Value vs Target
let percentage = 30; // Replace with dynamic value

// Determine the color gradient based on percentage
let gaugeColor;
if (percentage >= 75) {
    gaugeColor = ["#4CAF50", "#81C784"]; // Gradient for success
} else if (percentage >= 50) {
    gaugeColor = ["#FF9800", "#FFC107"]; // Gradient for warning
} else {
    gaugeColor = ["#FF0000", "#FF5252"]; // Gradient for danger
}

// Radial gauge options
var radialOptions = {
    chart: {
        height: 250, // Slightly larger for modern aesthetics
        type: "radialBar",
        animations: {
            enabled: true,
            easing: "easeinout",
            speed: 1500, // Smooth animation
        },
    },
    series: [percentage], // Percentage value
    plotOptions: {
        radialBar: {
            startAngle: -135, // Modern arc design
            endAngle: 135,
            hollow: {
                size: "60%",
                background: "transparent", // Transparent center
                dropShadow: {
                    enabled: true,
                    blur: 5,
                    color: "rgba(0, 0, 0, 0.2)", // Subtle shadow
                    opacity: 0.5,
                },
            },
            track: {
                background: "var(--color-secondary)", // Neutral track color
                strokeWidth: "100%",
            },
            dataLabels: {
                name: {
                    offsetY: -10, // Center the label
                    color: "var(--color-text-light)",
                    fontSize: "16px",
                    fontWeight: "bold",
                    show: true,
                    text: "Progress", // Description label
                },
                value: {
                    offsetY: 10,
                    fontSize: "28px",
                    fontWeight: "bold",
                    color: "var(--color-text-light)", // Match text color
                    formatter: function (val) {
                        return val + "%"; // Add percentage sign
                    },
                },
            },
        },
    },
    fill: {
        type: "gradient", // Use a gradient fill
        gradient: {
            shade: "dark",
            type: "horizontal",
            gradientToColors: [gaugeColor[1]], // End color of the gradient
            stops: [0, 100], // Smooth transition
        },
    },
    stroke: {
        lineCap: "round", // Rounded edges for modern aesthetics
    },
    colors: [gaugeColor[0]], // Start color of the gradient
};

// Initialize the chart
var radialChart = new ApexCharts(
    document.querySelector(".radial-gauge"),
    radialOptions
);
radialChart.render();


// Bar Chart: Upcoming Demos
var barOptions = {
    chart: {
        type: "bar", // Change chart type to bar
        height: 400, // Adjust height for the chart
        width: "100%", // Adjust width for the chart
        toolbar: {
            show: true, // Show toolbar for interactions
        },
        animations: {
            enabled: true,
            easing: "easeinout",
            speed: 800, // Smooth animation
        },
    },
    series: [
        {
            name: "Demos", // Name of the dataset
            data: [12, 8, 5, 6, 9, 19, 11], // Values for John, Jane, and Alan
        },
    ],
    xaxis: {
        categories: ["John", "Jane", "Alan"], // Names displayed on the x-axis
        labels: {
            style: {
                colors: ["var(--color-text-light)", "var(--color-text-light)", "var(--color-text-light)"],
                fontSize: "14px",
                fontWeight: "bold",
            },
        },
        axisBorder: {
            show: true,
            color: "var(--color-secondary)", // Border color for x-axis
        },
        axisTicks: {
            show: true,
            color: "var(--color-secondary)", // Tick color for x-axis
        },
    },
    yaxis: {
        labels: {
            style: {
                colors: "var(--color-text-light)",
                fontSize: "14px",
            },
        },
    },
    colors: ["var(--color-primary)", "var(--color-hover)", "var(--color-secondary)"], // Colors for the bars
    fill: {
        type: "gradient", // Gradient fill for bars
        gradient: {
            shade: "dark",
            type: "vertical",
            gradientToColors: ["var(--color-primary)", "var(--color-hover)"], // Gradient effect for modern look
            stops: [0, 100],
        },
    },
    legend: {
        show: false, // Disable the legend
    },
    dataLabels: {
        enabled: true,
        style: {
            colors: ["var(--color-text-light)"],
            fontSize: "16px", // Increase font size for better visibility
            fontWeight: "bold",
        },
        dropShadow: {
            enabled: true,
            top: 2,
            left: 2,
            opacity: 0.3,
            blur: 3,
            color: "rgba(0, 0, 0, 0.2)", // Subtle shadow for text
        },
    },
    tooltip: {
        enabled: true,
        theme: "dark",
        style: {
            fontSize: "14px",
            fontWeight: "bold",
        },
        y: {
            formatter: function (val) {
                return val + " Demos"; // Add custom label for tooltip
            },
        },
    },
    plotOptions: {
        bar: {
            horizontal: false, // Vertical bars (default)
            columnWidth: "50%", // Adjust column width
            borderRadius: 8, // Rounded bar edges for a modern look
        },
    },
    stroke: {
        show: true,
        width: 2, // Border around bars
        colors: ["var(--color-background)"], // Match background for clean separation
    },
};

// Initialize the bar chart
var barChart = new ApexCharts(
    document.querySelector(".bar-chart-placeholder-1"), // Target container
    barOptions
);
barChart.render();


// Bar Chart: Deals by Team
var barOptions = {
  chart: {
      type: "bar",
      height: 350, // Increased height for a larger chart
      animations: {
          enabled: true,
          easing: "easeinout",
          speed: 800, // Smooth animation
      },
  },
  series: [
      {
          name: "Deals",
          data: [45, 38, 50], // Values for US, UK, IT
      },
  ],
  xaxis: {
      categories: ["US", "UK", "IT"],
      labels: {
          style: {
              colors: "var(--color-text-light)", // Use text color for labels
              fontSize: "14px",
              fontWeight: "bold", // Bold category labels
          },
      },
  },
  yaxis: {
      labels: {
          style: {
              colors: "var(--color-text-light)", // Use text color for labels
              fontSize: "14px",
          },
      },
  },
  colors: ["var(--color-primary)"], // Standard bar color
  fill: {
      type: "gradient", // Apply gradient to bars
      gradient: {
          shade: "dark",
          type: "horizontal", // Horizontal gradient for a sleek effect
          gradientToColors: ["var(--color-primary)"], // Gold gradient for the bars
          stops: [0, 100],
      },
  },
  grid: {
      borderColor: "var(--color-secondary)", // Use the secondary color for grid lines
  },
  plotOptions: {
      bar: {
          borderRadius: 8, // Rounded corners for bars
          horizontal: false, // Vertical bars
          columnWidth: "50%", // Adjust bar width
      },
  },
  tooltip: {
      enabled: true,
      theme: "dark",
      style: {
          fontSize: "14px",
          fontWeight: "bold",
      },
      y: {
          formatter: function (val) {
              return val + " Deals"; // Add custom label for tooltip
          },
      },
  },
  dataLabels: {
      enabled: true,
      style: {
          colors: ["var(--color-text-light)"],
          fontSize: "14px",
          fontWeight: "bold",
      },
      dropShadow: {
          enabled: true,
          top: 2,
          left: 2,
          opacity: 0.3,
          blur: 3,
          color: "rgba(0, 0, 0, 0.2)", // Subtle shadow for data labels
      },
  },
};

// Initialize the bar chart
var barChart = new ApexCharts(
  document.querySelector(".bar-chart-placeholder"),
  barOptions
);
barChart.render();


  // Smooth Hover Effects for Table Rows
  const tableRows = document.querySelectorAll(".employee-table table tbody tr");
  tableRows.forEach((row) => {
      row.addEventListener("mouseover", () => {
          row.style.backgroundColor = "var(--color-hover)";
      });
      row.addEventListener("mouseout", () => {
          row.style.backgroundColor = "transparent";
      });
  });
});
