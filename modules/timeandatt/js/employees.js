// Function to initialize employee overview modal functionality
function initEmployeeOverviewModal() {
    // Get the employee overview widget
    const employeeOverviewWidget = document.getElementById('employee-count-widget');
    
    // Add double-click event listener to the widget
    if (employeeOverviewWidget) {
      employeeOverviewWidget.addEventListener('dblclick', function() {
        // Show the employee overview modal
        openEmployeeOverviewModal();
      });
    }
    
    // Close modal when clicking the close button
    const closeButtons = document.querySelectorAll('.modal-close');
    closeButtons.forEach(button => {
      button.addEventListener('click', function() {
        document.getElementById('employee-overview-modal').classList.remove('show');
      });
    });
    
    // Close modal when clicking outside of it
    window.addEventListener('click', function(event) {
      const modal = document.getElementById('employee-overview-modal');
      if (event.target === modal) {
        modal.classList.remove('show');
      }
    });
  }
  
  // Function to open the employee overview modal with detailed information
  function openEmployeeOverviewModal() {
    const modal = document.getElementById('employee-overview-modal');
    modal.classList.add('show');
    
    // You can add code here to fetch and display real data
    updateEmployeeOverviewStats();
  }
  
  // Function to update the employee overview statistics in the modal
  function updateEmployeeOverviewStats() {
    // This would typically fetch data from an API
    // For now, we'll use the same data as displayed in the widget
    const totalEmployees = document.getElementById('total-employees').textContent;
    const licenseLimit = document.getElementById('license-limit').textContent;
    const licenseUsage = document.getElementById('license-usage').textContent;
    
    // Update the modal with this information
    document.getElementById('modal-total-employees').textContent = totalEmployees;
    document.getElementById('modal-license-limit').textContent = licenseLimit;
    document.getElementById('modal-license-usage').textContent = licenseUsage;
    
    // Set the progress bar width
    const progressBar = document.querySelector('#employee-overview-modal .progress');
    if (progressBar) {
      progressBar.style.width = licenseUsage;
    }
  }

  document.addEventListener('DOMContentLoaded', function() {
    // Main tabs functionality
    const tabButtons = document.querySelectorAll('.tab-button');
    const tabPanes = document.querySelectorAll('.tab-pane');
    
    tabButtons.forEach(button => {
      button.addEventListener('click', function() {
        // Remove active class from all buttons and panes
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabPanes.forEach(pane => pane.classList.remove('active'));
        
        // Add active class to clicked button
        this.classList.add('active');
        
        // Show corresponding tab pane
        const tabId = this.getAttribute('data-tab');
        document.getElementById(`${tabId}-tab`).classList.add('active');
      });
    });
    
    // Sub-tabs functionality
    const subTabButtons = document.querySelectorAll('.sub-tab-button');
    const subTabPanes = document.querySelectorAll('.sub-tab-pane');
    
    subTabButtons.forEach(button => {
      button.addEventListener('click', function() {
        // Remove active class from all sub-tab buttons and panes
        subTabButtons.forEach(btn => btn.classList.remove('active'));
        subTabPanes.forEach(pane => pane.classList.remove('active'));
        
        // Add active class to clicked button
        this.classList.add('active');
        
        // Show corresponding sub-tab pane
        const subTabId = this.getAttribute('data-subtab');
        document.getElementById(`${subTabId}-tab`).classList.add('active');
      });
    });
    
    // Double-click functionality for employee rows
    const employeeRows = document.querySelectorAll('.employee-row');
    
    employeeRows.forEach(row => {
      row.addEventListener('dblclick', function() {
        const employeeId = this.getAttribute('data-employee-id');
        alert(`Employee details modal for ${employeeId} will be implemented later.`);
      });
    });
    initEmployeeOverviewModal();
  });