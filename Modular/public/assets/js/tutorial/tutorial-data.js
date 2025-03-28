const tutorials = {
    "dashboard": [
        {
            element: ".modular-logo",
            title: "Welcome!",
            text: "Click this logo to toggle the sidebar menu.",
            placement: "right"
        },
        {
            element: ".notification-bell",
            title: "Notifications",
            text: "Check your notifications here.",
            placement: "left"
        }
    ],
    "invoice-products": [
        {
            element: "[data-tab='products']",
            title: "Product Categories",
            text: "Switch between products, parts, and services here.",
            placement: "bottom"
        },
        {
            element: "#add-product-btn",
            title: "Add Product",
            text: "Click here to create a new product.",
            placement: "right"
        }
    ],
    "TA-employees": [
        {
            element: "#employee-count-widget",
            title: "Employee Overview",
            text: "Here you can see your total employee count and license usage at a glance.",
            placement: "bottom"
        },
        {
            element: ".help-widget",
            title: "Quick Help",
            text: "Hover over this icon anytime to see helpful tips and shortcuts.",
            placement: "right"
        },
        {
            element: ".page-tabs-header",
            title: "Employee Categories",
            text: "Navigate between Active, Terminated, Incomplete, and All Employees using these tabs.",
            placement: "bottom"
        },
        {
            element: ".page-subtabs-header",
            title: "Employment Type",
            text: "For active employees, you can filter between Permanent and Temporary staff.",
            placement: "bottom"
        },
        {
            element: ".main-employee-table",
            title: "Employee List",
            text: "This table shows all your employees. Double-click any row to view detailed information.",
            placement: "top"
        },
        {
            element: ".pagination",
            title: "Navigation",
            text: "Use these controls to browse through multiple pages of employee records.",
            placement: "top"
        },
        {
            element: ".modular-nav-items a[onclick*='openAddEmployeeModal']",
            title: "Adding New Employees",
            text: "Click Next to open the Add Employee form where you can create new employee records.",
            placement: "right",
            beforeNext: async () => {
                return new Promise(resolve => {
                    // Wait for function to be available
                    const checkFunction = setInterval(() => {
                        if (typeof window.openAddEmployeeModal === 'function') {
                            clearInterval(checkFunction);
                            window.openAddEmployeeModal();
                            setTimeout(resolve, 800);
                        }
                    }, 100);

                    // Timeout after 5 seconds
                    setTimeout(() => {
                        clearInterval(checkFunction);
                        resolve();
                    }, 5000);
                });
            }
        },
        {
            element: ".add-emp-modal-content",
            title: "New Employee Form",
            text: "This form allows you to enter all necessary information for a new employee, including personal details, employment information, and contact details.",
            placement: "right",
            modal: "add-employee-modal",
            waitForModal: true
        },
        {
            element: ".add-emp-modal-content .modal-header .modal-close",
            title: "Closing the Form",
            text: "Click Next to close this form. In practice, you would fill in the required information and click Save.",
            placement: "left",
            modal: "add-employee-modal",
            beforeNext: async () => {
                return new Promise(resolve => {
                    const modal = document.getElementById('add-employee-modal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                    setTimeout(resolve, 1000);
                });
            }
        },
        {
            element: ".main-employee-table tbody tr:first-child",
            title: "Viewing Employee Details",
            text: "Double-click any employee row to view their detailed information. Click Next to see an example.",
            placement: "top",
            beforeNext: async () => {
                return new Promise(resolve => {
                    // Wait for function to be available
                    const checkFunction = setInterval(() => {
                        if (typeof window.openEmployeeModal === 'function') {
                            clearInterval(checkFunction);
                            const firstRow = document.querySelector('.main-employee-table tbody tr:first-child');
                            if (firstRow && firstRow.dataset.employeeId) {
                                window.openEmployeeModal(firstRow.dataset.employeeId);
                            }
                            setTimeout(resolve, 800);
                        }
                    }, 100);

                    // Timeout after 5 seconds
                    setTimeout(() => {
                        clearInterval(checkFunction);
                        resolve();
                    }, 5000);
                });
            }
        },
        {
            element: ".emp-details-modal-content",
            title: "Employee Details",
            text: "This screen shows you all information about the selected employee.",
            placement: "right",
            modal: "employee-details-modal",
            waitForModal: true
        },
        {
            element: ".emp-details-modal-content .overview-stats-container",
            title: "Employee Profile",
            text: "Review and manage all aspects of the employee's information here.",
            placement: "right",
            modal: "employee-details-modal"
        },
        {
            element: ".emp-details-modal-content .employee-stats-grid",
            title: "Employee Statistics",
            text: "View key metrics and statistics for this employee.",
            placement: "bottom",
            modal: "employee-details-modal"
        },
        {
            element: ".emp-details-modal-content .modal-header .modal-close",
            title: "Finishing Up",
            text: "Click Next to close the employee details and complete the tutorial.",
            placement: "left",
            modal: "employee-details-modal",
            beforeNext: async () => {
                return new Promise(resolve => {
                    const modal = document.getElementById('employee-details-modal');
                    if (modal) {
                        modal.style.display = 'none';
                    }
                    setTimeout(resolve, 800);
                });
            }
        }
    ],
    
    // Multi-page tutorial for adding new employee
    "add-employee": [
        {
            element: "#personal-info",
            title: "Personal Information",
            text: "Start by filling in the employee's personal details.",
            placement: "right"
        },
        {
            element: "#employment-details",
            title: "Employment Details",
            text: "Enter job-related information like position and department.",
            placement: "right"
        },
        {
            element: "#contact-info",
            title: "Contact Information",
            text: "Add contact details and emergency contacts.",
            placement: "right"
        },
        {
            element: "#documents-upload",
            title: "Documents",
            text: "Upload required documents like ID and certifications.",
            placement: "right"
        },
        {
            nextPage: "/modules/time_and_attendance/views/employees.php",
            element: "#submit-employee",
            title: "Save Employee",
            text: "Click here to save the new employee record.",
            placement: "bottom"
        }
    ],
    
    // Add tutorial for the employee modal when it opens
    "employee-modal": [
        {
            element: ".modal-header",
            title: "Employee Details",
            text: "View and edit comprehensive employee information here.",
            placement: "bottom"
        },
        {
            element: ".overview-stats-container",
            title: "Employee Distribution",
            text: "See how your workforce is distributed across departments and employment status.",
            placement: "right"
        }
    ]
}; 