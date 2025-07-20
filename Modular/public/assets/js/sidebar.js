window.callIfExists = function(functionName, ...args) {
    if (typeof window[functionName] === 'function') {
        window[functionName](...args);
    } else {
        // Optionally: do nothing or show a generic warning
        // alert(functionName + ' is not available on this page.');
    }
};

document.addEventListener('DOMContentLoaded', function () {
    const bodyId = document.body.id;

    // Get current page path
    const currentPath = window.location.pathname;

    // Check if user is logged in as technician
    const isTechnician = document.body.classList.contains('technician-mode') || 
                        (typeof isTechnicianUser !== 'undefined' && isTechnicianUser === true);

    // Sidebar configurations
    const sidebarConfig = {
        "dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/views/settings.php", icon: "settings", text: "Settings" },
            { href: "/public/views/export.php", icon: "upload", text: "Exporting" },
            { href: "/public/views/importing.php", icon: "download", text: "Importing" },
            { href: isTechnician ? "/public/admin/techlogin.php" : "/public/php/logout.php", icon: "exit_to_app", text: isTechnician ? "Back to Tech Portal" : "LogOut" },
            { href: "/public/views/devices.php", icon: "devices", text: "Devices" },
        ],
        "invoice-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/modules/invoice/views/invoices.php", icon: "description", text: "Invoices" },
            { href: "/modules/invoice/views/invoice-products.php", icon: "inventory_2", text: "Products" },
            { href: "/modules/invoice/views/invoice-clients.php", icon: "people", text: "Clients" },
            { href: "/modules/invoice/views/invoice-payments.php", icon: "payment", text: "Payments" },
            { href: "/modules/invoice/views/invoice-reports.php", icon: "bar_chart", text: "Reports" },
            { href: "/modules/invoice/views/invoice-setup.php", icon: "build", text: "Setup" },
            { href: "/modules/invoice/views/sales-reps.php", icon: "group", text: "Sales Reps" }
        ],
        "settings": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "#preferences-settings", icon: "settings", text: "Preferences", onclick: "activateSection('preferences-settings')" },
            { href: "#time-attendance-settings", icon: "schedule", text: "Time & Attendance", onclick: "activateSection('time-attendance-settings')" },
            { href: "#invoicing-settings", icon: "receipt", text: "Invoicing & Billing", onclick: "activateSection('invoicing-settings')" },
            { href: "#payroll-settings", icon: "payment", text: "Payroll", onclick: "activateSection('payroll-settings')" },
            { href: "#inventory-settings", icon: "inventory_2", text: "Inventory Management", onclick: "activateSection('inventory-settings')" },
            { href: "#crm-settings", icon: "people", text: "CRM", onclick: "activateSection('crm-settings')" },
            { href: "#project-management-settings", icon: "assignment", text: "Project Management", onclick: "activateSection('project-management-settings')" },
            { href: "#accounting-settings", icon: "account_balance", text: "Accounting", onclick: "activateSection('accounting-settings')" },
            { href: "#hr-settings", icon: "group", text: "HR Management", onclick: "activateSection('hr-settings')" },
            { href: "#support-settings", icon: "support_agent", text: "Support Module", onclick: "activateSection('support-settings')" },
            { href: "#fleet-settings", icon: "directions_car", text: "Fleet Management", onclick: "activateSection('fleet-settings')" },
            { href: "#asset-settings", icon: "business_center", text: "Asset Management", onclick: "activateSection('asset-settings')" },
            { href: "#access-control-settings", icon: "lock", text: "Access Control", onclick: "activateSection('access-control-settings')" }
        ],
        "invoice-clients": [
            { href: "/modules/invoice/views/invoice-dashboard.php", icon: "dashboard", text: "Dashboard" },
            { href: "#", icon: "person_add", text: "Add Private Client", onclick: "callIfExists('showAddCustomerModal')" },
            { href: "#", icon: "person_add", text: "Add Business Client", onclick: "callIfExists('showAddCompanyModal')" },
            { href: "/modules/invoice/views/invoice-payments.php", icon: "payment", text: "Payment Reminder" }
        ],
        "invoice-products": [
            { href: "/modules/invoice/views/invoice-dashboard.php", icon: "dashboard", text: "Dashboard"},
            { href: "#", icon: "category", text: "Products", tab: "products", class: "sidebar-button" },
            { href: "#", icon: "build", text: "Parts", tab: "parts", class: "sidebar-button" },
            { href: "#", icon: "directions_car", text: "Vehicles", tab: "vehicles", class: "sidebar-button" },
            { href: "#", icon: "add_circle_outline", text: "Extras", tab: "extras", class: "sidebar-button" },
            { href: "#", icon: "remove_circle_outline", text: "Services", tab: "services", class: "sidebar-button" },
            { href: "#", icon: "block", text: "Discontinued", tab: "discontinued", class: "sidebar-button" },
            { href: "#", icon: "do_not_disturb_on", text: "Disabled", tab: "disabled", class: "sidebar-button" }
        ],
        "payments":[
            { href: "/modules/invoice/views/invoice-dashboard.php", icon: "dashboard", text: "Dashboard" },
        ],
        "sales-reps":[
            { href: "/modules/invoice/views/invoice-dashboard.php", icon: "dashboard", text: "Dashboard" },
            { href: "#", icon: "person_add", text: "Add Sales Rep", onclick: "openAddSalesRepModal()" }
        ],
        "invoice-reports":[
            { href: "/modules/invoice/views/invoice-dashboard.php", icon: "dashboard", text: "Dashboard" },
            { href: "#sales-reports", icon: "bar_chart", text: "Sales Reports", onclick: "activateSection('sales-reports')" },
            { href: "#tax-reports", icon: "receipt", text: "Tax Reports", onclick: "activateSection('tax-reports')" },
            { href: "#income-reports", icon: "attach_money", text: "Income Reports", onclick: "activateSection('income-reports')" },
            { href: "#expenses-reports", icon: "money_off", text: "Expenses Reports", onclick: "activateSection('expenses-reports')" },
            { href: "#general-reports", icon: "assessment", text: "General Reports", onclick: "activateSection('general-reports')" }
        ],
        "accounting-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/accounting/pages/general-ledger.php", icon: "book", text: "General Ledger" },
            { href: "/public/modules/accounting/pages/chart-of-accounts.php", icon: "account_tree", text: "Chart of Accounts" },
            { href: "/public/modules/accounting/pages/trial-balance.php", icon: "balance", text: "Trial Balance" },
            { href: "/public/modules/accounting/pages/profit-loss-report.php", icon: "bar_chart", text: "Profit & Loss Report" },
            { href: "/public/modules/accounting/pages/balance-sheet.php", icon: "assessment", text: "Balance Sheet" },
            { href: "/public/modules/accounting/pages/cash-flow-statement.php", icon: "show_chart", text: "Cash Flow" },
            { href: "/public/modules/accounting/pages/reconciliation.php", icon: "sync", text: "Reconciliation" },
            { href: "/public/modules/accounting/pages/journal-entries.php", icon: "edit", text: "Journal Entries" }
        ],
        "invoices": [
            { href: "/modules/invoice/views/invoice-dashboard.php", icon: "dashboard", text: "Dashboard", },
        ],
        "invoice-setup": [
            { href: "/modules/invoice/views/invoice-dashboard.php", icon: "dashboard", text: "Dashboard" },
            { href: "#", icon: "inventory", text: "Product Setup", tab: "products", class: "tab", onclick: "invoiceSetup.switchTab('products')" },
            { href: "#", icon: "account_balance", text: "Bank & Company", tab: "banking", class: "tab", onclick: "invoiceSetup.switchTab('banking')" },
            { href: "#", icon: "trending_up", text: "Sales Configuration", tab: "sales", class: "tab", onclick: "invoiceSetup.switchTab('sales')" },
            { href: "#", icon: "business", text: "Suppliers", tab: "suppliers", class: "tab", onclick: "invoiceSetup.switchTab('suppliers')" },
            { href: "#", icon: "receipt_long", text: "Credit Notes", tab: "credit", class: "tab", onclick: "invoiceSetup.switchTab('credit')" },
            { href: "#", icon: "format_list_numbered", text: "Invoice Numbering", tab: "numbering", class: "tab", onclick: "invoiceSetup.switchTab('numbering')" },
            { href: "#", icon: "description", text: "Terms & Footer", tab: "terms", class: "tab", onclick: "invoiceSetup.switchTab('terms')" }
        ],
        "TandA": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/modules/time_and_attendance/views/employees.php", icon: "people", text: "Employees" },
            { href: "/modules/time_and_attendance/views/timecards.php", icon: "access_time", text: "Timecards" },
            { href: "/modules/time_and_attendance/views/mobile-clocking.php", icon: "phone_android", text: "Mobile Clocking" },
            { href: "/modules/time_and_attendance/views/reports.php", icon: "bar_chart", text: "Reports" },
            { href: "/modules/time_and_attendance/views/devices.php", icon: "devices", text: "Devices" },
            { href: "/modules/time_and_attendance/views/schedules.php", icon: "calendar_today", text: "Schedules" }
        ],
        "timecards": [
            { href: "/modules/time_and_attendance/views/dashboard-TA.php", icon: "dashboard", text: "Dashboard", },
            { href: "/modules/time_and_attendance/views/employees.php", icon: "people", text: "Employees" },
            { href: "/modules/time_and_attendance/views/timecards.php", icon: "access_time", text: "Timecards", active: true },
            { href: "/modules/time_and_attendance/views/mobile-clocking.php", icon: "phone_android", text: "Mobile Clocking" },
            { href: "/modules/time_and_attendance/views/reports.php", icon: "bar_chart", text: "Reports" },
            { href: "/modules/time_and_attendance/views/devices.php", icon: "devices", text: "Devices" },
            { href: "/modules/time_and_attendance/views/schedules.php", icon: "calendar_today", text: "Schedules" }
        ],
        "schedules": [
            { href: "/modules/time_and_attendance/views/dashboard-TA.php", icon: "dashboard", text: "Dashboard", },
            { href: "/modules/time_and_attendance/views/employees.php", icon: "people", text: "Employees" },
            { href: "/modules/time_and_attendance/views/timecards.php", icon: "access_time", text: "Timecards" },
            { href: "/modules/time_and_attendance/views/mobile-clocking.php", icon: "phone_android", text: "Mobile Clocking" },
            { href: "/modules/time_and_attendance/views/reports.php", icon: "bar_chart", text: "Reports" },
            { href: "/modules/time_and_attendance/views/devices.php", icon: "devices", text: "Devices" },
            { href: "/modules/time_and_attendance/views/schedules.php", icon: "calendar_today", text: "Schedules", active: true }
        ],
        "TA-employees": [
            { href: "/modules/time_and_attendance/views/dashboard-TA.php", icon: "dashboard", text: "Dashboard", },
            { href: "#", icon: "person_add", text: "Add Employee", onclick: "openAddEmployeeModal()" },
            { href: "/modules/time_and_attendance/views/import-employees.php", icon: "upload_file", text: "Import Employees" }
        ],
        "hr-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/hr/pages/employees.php", icon: "people", text: "Employees" },
            { href: "/public/modules/hr/pages/recruitment.php", icon: "person_add", text: "Recruitment" },
            { href: "/public/modules/hr/pages/performance.php", icon: "assessment", text: "Performance" },
            { href: "/public/modules/hr/pages/training.php", icon: "school", text: "Training" },
            { href: "/public/modules/hr/pages/documents.php", icon: "description", text: "Documents" },
            { href: "/public/modules/hr/pages/benefits.php", icon: "health_and_safety", text: "Benefits" },
            { href: "/public/modules/hr/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "project-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/project/pages/projects.php", icon: "assignment", text: "Projects" },
            { href: "/public/modules/project/pages/tasks.php", icon: "task", text: "Tasks" },
            { href: "/public/modules/project/pages/teams.php", icon: "groups", text: "Teams" },
            { href: "/public/modules/project/pages/timeline.php", icon: "timeline", text: "Timeline" },
            { href: "/public/modules/project/pages/resources.php", icon: "build", text: "Resources" },
            { href: "/public/modules/project/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "inventory-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/inventory/pages/items.php", icon: "inventory_2", text: "Items" },
            { href: "/public/modules/inventory/pages/stock.php", icon: "store", text: "Stock" },
            { href: "/public/modules/inventory/pages/suppliers.php", icon: "local_shipping", text: "Suppliers" },
            { href: "/public/modules/inventory/pages/orders.php", icon: "shopping_cart", text: "Orders" },
            { href: "/public/modules/inventory/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "crm-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/crm/pages/contacts.php", icon: "contacts", text: "Contacts" },
            { href: "/public/modules/crm/pages/leads.php", icon: "trending_up", text: "Leads" },
            { href: "/public/modules/crm/pages/opportunities.php", icon: "lightbulb", text: "Opportunities" },
            { href: "/public/modules/crm/pages/campaigns.php", icon: "campaign", text: "Campaigns" },
            { href: "/public/modules/crm/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "support-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/support/pages/tickets.php", icon: "confirmation_number", text: "Tickets" },
            { href: "/public/modules/support/pages/knowledge.php", icon: "menu_book", text: "Knowledge Base" },
            { href: "/public/modules/support/pages/faq.php", icon: "help", text: "FAQ" },
            { href: "/public/modules/support/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "fleet-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/fleet/pages/vehicles.php", icon: "directions_car", text: "Vehicles" },
            { href: "/public/modules/fleet/pages/maintenance.php", icon: "build", text: "Maintenance" },
            { href: "/public/modules/fleet/pages/drivers.php", icon: "person", text: "Drivers" },
            { href: "/public/modules/fleet/pages/trips.php", icon: "map", text: "Trips" },
            { href: "/public/modules/fleet/pages/fuel.php", icon: "local_gas_station", text: "Fuel Log" },
            { href: "/public/modules/fleet/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "asset-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/asset/pages/assets.php", icon: "business_center", text: "Assets" },
            { href: "/public/modules/asset/pages/maintenance.php", icon: "build", text: "Maintenance" },
            { href: "/public/modules/asset/pages/depreciation.php", icon: "trending_down", text: "Depreciation" },
            { href: "/public/modules/asset/pages/licenses.php", icon: "vpn_key", text: "Licenses" },
            { href: "/public/modules/asset/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "access-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/access/pages/users.php", icon: "people", text: "Users" },
            { href: "/public/modules/access/pages/roles.php", icon: "admin_panel_settings", text: "Roles" },
            { href: "/public/modules/access/pages/permissions.php", icon: "security", text: "Permissions" },
            { href: "/public/modules/access/pages/logs.php", icon: "history", text: "Access Logs" },
            { href: "/public/modules/access/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "payroll-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/payroll/pages/salaries.php", icon: "payments", text: "Salaries" },
            { href: "/public/modules/payroll/pages/deductions.php", icon: "remove_circle", text: "Deductions" },
            { href: "/public/modules/payroll/pages/benefits.php", icon: "add_circle", text: "Benefits" },
            { href: "/public/modules/payroll/pages/taxes.php", icon: "receipt", text: "Taxes" },
            { href: "/public/modules/payroll/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "mobile-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/mobile/pages/settings.php", icon: "settings", text: "Settings" },
            { href: "/public/modules/mobile/pages/users.php", icon: "people", text: "Users" },
            { href: "/public/modules/mobile/pages/notifications.php", icon: "notifications", text: "Notifications" },
            { href: "/public/modules/mobile/pages/sync.php", icon: "sync", text: "Sync" },
            { href: "/public/modules/mobile/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "importing": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "#time_and_attendance", icon: "schedule", text: "Time & Attendance", onclick: "activateSection('time_and_attendance')" },
            { href: "#accounting", icon: "account_balance", text: "Accounting", onclick: "activateSection('accounting')" },
            { href: "#payroll", icon: "payments", text: "Payroll Management", onclick: "activateSection('payroll')" },
            { href: "#access", icon: "security", text: "Access Control", onclick: "activateSection('access')" },
            { href: "#asset", icon: "inventory", text: "Asset Management", onclick: "activateSection('asset')" },
            { href: "#fleet", icon: "directions_car", text: "Fleet Management", onclick: "activateSection('fleet')" },
            { href: "#support", icon: "support_agent", text: "Support/Help Desk", onclick: "activateSection('support')" },
            { href: "#crm", icon: "people", text: "Customer Relationship", onclick: "activateSection('crm')" },
            { href: "#inventory", icon: "inventory_2", text: "Inventory Management", onclick: "activateSection('inventory')" },
            { href: "#project", icon: "assignment", text: "Project Management", onclick: "activateSection('project')" },
            { href: "#hr", icon: "person", text: "Human Resources", onclick: "activateSection('hr')" },
            { href: "#invoice", icon: "receipt", text: "Invoice Management", onclick: "activateSection('invoice')" }
        ]
    };
    

    /**
     * Initialize sidebar based on the current body ID
     */
    function initializeSidebar() {
        const sidebarItems = sidebarConfig[bodyId] || [];
        const sidebar = document.querySelector('.modular-nav-items');

        if (!sidebar) return;

        // Check if tutorial is completed
        const isTutorialCompleted = localStorage.getItem(`tutorial-done-${bodyId}`);
        if (isTutorialCompleted) {
            document.body.setAttribute('data-tutorial-completed', 'true');
        }

        // Clear existing items
        sidebar.innerHTML = '';

        // Add items from configuration
        sidebarItems.forEach(item => {
            const li = document.createElement('li');
            const onclick = item.onclick ? `onclick="${item.onclick}"` : '';

            li.innerHTML = `
                <a href="${item.href}" ${onclick} class="${item.tab ? 'tab' : ''}" ${item.tab ? `data-tab="${item.tab}"` : ''}>
                    <i class="material-icons">${item.icon}</i>
                    <span class="nav-text">${item.text}</span>
                </a>
            `;
            sidebar.appendChild(li);
        });
    }

    /**
     * Setup tab switching for pages with tabs (like invoice-products)
     */
    function setupTabs() {
        if (!sidebarConfig[bodyId]?.some(item => item.tab)) return;

        document.querySelectorAll('.tab').forEach(tabButton => {
            tabButton.addEventListener('click', function (event) {
                event.preventDefault();
                const tab = this.getAttribute('data-tab');

                // Remove active class from all tabs and tab contents
                document.querySelectorAll('.tab').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

                // Add active class to the clicked tab and corresponding tab content
                this.classList.add('active');
                const tabContent = document.getElementById(tab);
                if (tabContent) tabContent.classList.add('active');
            });
        });
    }
    /**
     * Sidebar toggle functionality
     */
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    // Configuration object for toggling classes
    const toggleConfig = {
        dashboard: {
            targetId: 'mainContent',
            toggleClasses: ['collapsed']
        },
        'invoice-dashboard': {
            targetId: 'main-content',
            toggleClasses: ['collapsed']
        },
        settings: {
            targetId: 'settings-container',
            toggleClasses: ['collapsed']
        },
        'invoice-clients': {
            targetId: 'clients-screen',
            toggleClasses: ['collapsed']
        },
        'invoice-products': {
            targetId: 'products-container',
            toggleClasses: ['collapsed']
        },
        'payments':{
            targetId: 'payments-container',
            toggleClasses: ['collapsed']
        },
        'sales-reps':{
            targetId: 'sales-reps-container',
            toggleClasses: ['collapsed']
        },
        'invoices':{
            targetId: 'screen-container',
            toggleClasses: ['collapsed']
        },
        'TandA':{
            targetId: '.dashboard-container',
            toggleClasses: ['collapsed']
        },
        'TA-employees':{
            targetId: '.dashboard-container',
            toggleClasses: ['collapsed']
        },
        'invoice-setup':{
            targetId: '.container',
            toggleClasses: ['collapsed']
        },
        'schedules': {
            targetId: 'schedule-container',
            toggleClasses: ['collapsed']
        },
        'importing': {
            targetId: 'import-container',
            toggleClasses: ['collapsed']
        }
    };

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            // Toggle the sidebar class
            sidebar?.classList.toggle('collapsed');

            // Check if a configuration exists for the current body ID
            if (toggleConfig[bodyId]) {
                const { targetId, toggleClasses } = toggleConfig[bodyId];
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    // Toggle all classes defined in the configuration
                    toggleClasses.forEach(className => {
                        targetElement.classList.toggle(className);
                    });
                }
            }
        });
    }

    /**
     * Session exit button logic
     */
    const exitButton = document.getElementById('exit-button');
    if (exitButton) {
        exitButton.addEventListener('click', function (event) {
            event.preventDefault();
            fetch('/src/api/session/status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.tech_logged_in) {
                        window.location.href = '/public/admin/techlogin.php';
                    } else if (data.user_logged_in) {
                        window.location.href = '/public/index.php';
                    }
                })
                .catch(error => console.error('Error fetching session status:', error));
        });
    }

    // Initialize sidebar and page-specific logic
    initializeSidebar();
    setupTabs();

    // Initialize notification system
    initializeNotifications();

});

function checkMultipleAccounts() {
    if (multipleAccounts) {
        window.location.href = "/public/account/choose-account.php"; // Redirect if session variable is set
    }
}

/**
 * Notification System
 */
function initializeNotifications() {
    try {
        // Wrap the initialization in try-catch
        updateNotificationCount().catch(error => {
            console.warn('Failed to initialize notifications:', error);
        });
        const notificationBell = document.getElementById('notification-bell');
        const notificationsModal = document.getElementById('notifications-modal');
        const closeNotificationsBtn = document.querySelector('.close-notifications');
        const markAllReadBtn = document.getElementById('mark-all-read');
        const loadMoreBtn = document.getElementById('load-more-notifications');
        const tabButtons = document.querySelectorAll('.tab-button');
        
        // Current state variables
        let currentTab = 'all';
        let currentPage = 1;
        const notificationsPerPage = 10;
        
        // Toggle notifications modal when bell is clicked
        if (notificationBell) {
            notificationBell.addEventListener('click', () => {
                notificationsModal.classList.remove('hidden');
                notificationsModal.classList.add('visible');
                
                // Load notifications if this is the first open
                if (document.querySelector('.no-notifications')) {
                    loadNotifications(currentTab, currentPage, true);
                }
            });
        }
        
        // Close notifications modal when close button is clicked
        if (closeNotificationsBtn) {
            closeNotificationsBtn.addEventListener('click', () => {
                notificationsModal.classList.remove('visible');
                notificationsModal.classList.add('hidden');
            });
        }
        
        // Handle tab switching
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all tabs
                tabButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked tab
                button.classList.add('active');
                
                // Update current tab and reload notifications
                currentTab = button.getAttribute('data-tab');
                currentPage = 1;
                loadNotifications(currentTab, currentPage, true);
            });
        });
        
        // Handle mark all as read
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', () => {
                markAllNotificationsAsRead();
            });
        }
        
        // Handle load more
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                currentPage++;
                loadNotifications(currentTab, currentPage, false);
            });
        }
    } catch (error) {
        console.warn('Error in notification initialization:', error);
    }
}

/**
 * Load notifications based on tab and page
 * @param {string} tab - The notification tab to load
 * @param {number} page - The page number to load
 * @param {boolean} reset - Whether to reset the list or append
 */
function loadNotifications(tab, page, reset) {
    const notificationsList = document.getElementById('notifications-list');
    
    // Show loading state
    if (reset) {
        notificationsList.innerHTML = '<div class="notification-loading">Loading...</div>';
    } else {
        // Add loading indicator at the end
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'notification-loading';
        loadingDiv.textContent = 'Loading...';
        notificationsList.appendChild(loadingDiv);
    }
    
    // Fetch notifications from the server
    fetch(`/src/api/notifications.php?tab=${tab}&page=${page}`)
        .then(response => response.json())
        .then(data => {
            // Remove loading indicators
            const loadingElements = notificationsList.querySelectorAll('.notification-loading');
            loadingElements.forEach(el => el.remove());
            
            if (reset) {
                notificationsList.innerHTML = '';
            }
            
            if (data.notifications && data.notifications.length > 0) {
                // Create and append notification items
                data.notifications.forEach(notification => {
                    const notificationElement = createNotificationItem(notification);
                    notificationsList.appendChild(notificationElement);
                });
                
                // Hide load more button if no more notifications
                const loadMoreBtn = document.getElementById('load-more-notifications');
                if (data.has_more) {
                    loadMoreBtn.style.display = 'block';
                } else {
                    loadMoreBtn.style.display = 'none';
                }
            } else if (reset) {
                // Show no notifications message
                notificationsList.innerHTML = '<div class="no-notifications">No notifications to display</div>';
            }
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
            const loadingElements = notificationsList.querySelectorAll('.notification-loading');
            loadingElements.forEach(el => el.remove());
            
            if (reset) {
                notificationsList.innerHTML = '<div class="no-notifications">Error loading notifications</div>';
            }
        });
}

/**
 * Create a notification item element
 * @param {Object} notification - The notification data
 * @returns {HTMLElement} - The notification item element
 */
function createNotificationItem(notification) {
    const item = document.createElement('div');
    item.className = `notification-item ${notification.is_read ? '' : 'unread'}`;
    item.setAttribute('data-id', notification.id);
    
    const header = document.createElement('div');
    header.className = 'notification-header';
    
    const title = document.createElement('div');
    title.className = 'notification-title';
    title.textContent = notification.title;
    
    const time = document.createElement('div');
    time.className = 'notification-time';
    time.textContent = formatNotificationTime(notification.created_at);
    
    header.appendChild(title);
    header.appendChild(time);
    
    const message = document.createElement('div');
    message.className = 'notification-message';
    message.textContent = notification.message;
    
    const footer = document.createElement('div');
    footer.className = 'notification-footer';
    
    const source = document.createElement('div');
    source.className = 'notification-source';
    source.textContent = notification.source;
    
    const actions = document.createElement('div');
    actions.className = 'notification-actions';
    
    if (!notification.is_read) {
        const markReadAction = document.createElement('span');
        markReadAction.className = 'notification-action';
        markReadAction.textContent = 'Mark as read';
        markReadAction.addEventListener('click', (e) => {
            e.stopPropagation();
            markNotificationAsRead(notification.id);
        });
        actions.appendChild(markReadAction);
    }
    
    footer.appendChild(source);
    footer.appendChild(actions);
    
    item.appendChild(header);
    item.appendChild(message);
    item.appendChild(footer);
    
    // Mark notification as read when clicked
    item.addEventListener('click', () => {
        if (!notification.is_read) {
            markNotificationAsRead(notification.id);
        }
        
        // Handle notification action if specified
        if (notification.action_url) {
            window.location.href = notification.action_url;
        }
    });
    
    return item;
}

/**
 * Format notification timestamp
 * @param {string} timestamp - The notification timestamp
 * @returns {string} - Formatted time string
 */
function formatNotificationTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffSec = Math.floor(diffMs / 1000);
    const diffMin = Math.floor(diffSec / 60);
    const diffHr = Math.floor(diffMin / 60);
    const diffDays = Math.floor(diffHr / 24);
    
    if (diffSec < 60) {
        return 'Just now';
    } else if (diffMin < 60) {
        return `${diffMin} minute${diffMin !== 1 ? 's' : ''} ago`;
    } else if (diffHr < 24) {
        return `${diffHr} hour${diffHr !== 1 ? 's' : ''} ago`;
    } else if (diffDays < 7) {
        return `${diffDays} day${diffDays !== 1 ? 's' : ''} ago`;
    } else {
        // Format as MM/DD/YYYY
        const month = date.getMonth() + 1;
        const day = date.getDate();
        const year = date.getFullYear();
        return `${month}/${day}/${year}`;
    }
}

/**
 * Mark a notification as read
 * @param {number} id - The notification ID
 */
function markNotificationAsRead(id) {
    fetch(`/src/api/notifications.php?action=mark_read&id=${id}`, {
        method: 'POST'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const notificationItem = document.querySelector(`.notification-item[data-id="${id}"]`);
                if (notificationItem) {
                    notificationItem.classList.remove('unread');
                    
                    // Remove 'Mark as read' action
                    const markReadAction = notificationItem.querySelector('.notification-action');
                    if (markReadAction) {
                        markReadAction.remove();
                    }
                }
                
                // Update notification count
                updateNotificationCount();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
}

/**
 * Mark all notifications as read
 */
function markAllNotificationsAsRead() {
    fetch('/src/api/notifications.php?action=mark_all_read', {
        method: 'POST'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const unreadItems = document.querySelectorAll('.notification-item.unread');
                unreadItems.forEach(item => {
                    item.classList.remove('unread');
                    
                    // Remove 'Mark as read' action
                    const markReadAction = item.querySelector('.notification-action');
                    if (markReadAction) {
                        markReadAction.remove();
                    }
                });
                
                // Update notification count
                updateNotificationCount(0);
            }
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
        });
}

/**
 * Update the notification count badge
 */
async function updateNotificationCount() {
    try {
        const response = await fetch('/src/api/notifications.php?action=count');
        if (!response.ok) {
            // Silently fail for 404s and other errors
            console.warn('Notification service unavailable');
            return;
        }
        const data = await response.json();
        const countElement = document.getElementById('notification-count');
        if (countElement) {
            countElement.textContent = data.count;
            
            // Show/hide the badge based on count
            if (data.count > 0) {
                countElement.style.display = 'flex';
            } else {
                countElement.style.display = 'none';
            }
        }
    } catch (error) {
        // Silently fail and log warning
        console.warn('Error updating notifications:', error);
    }
}


