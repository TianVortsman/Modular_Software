document.addEventListener('DOMContentLoaded', function () {
    const bodyId = document.body.id;

    // Sidebar configurations
    const sidebarConfig = {
        "dashboard": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
            { href: "/modular1/main/settings.php", icon: "settings", text: "Settings" },
            { href: "pages/export.html", icon: "upload", text: "Exporting" },
            { href: "pages/import.html", icon: "download", text: "Importing" },
            { href: "../php/logout.php", icon: "exit_to_app", text: "LogOut" }
        ],
        "invoice-dashboard": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
            { href: "/modular1/modules/invoice/pages/invoices.php", icon: "description", text: "Invoices" },
            { href: "/modular1/modules/invoice/pages/invoice-products.php", icon: "inventory_2", text: "Products" },
            { href: "/modular1/modules/invoice/pages/invoice-clients.php", icon: "people", text: "Clients" },
            { href: "/modular1/modules/invoice/pages/invoice-payments.php", icon: "payment", text: "Payments" },
            { href: "/modular1/modules/invoice/pages/invoice-reports.php", icon: "bar_chart", text: "Reports" },
            { href: "/modular1/modules/invoice/pages/invoice-setup.php", icon: "build", text: "Setup" },
            { href: "/modular1/modules/invoice/pages/sales-reps.php", icon: "group", text: "Sales Reps" }
        ],
        "settings": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
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
            { href: "/modular1/modules/invoice/invoice-dashboard.php", icon: "dashboard", text: "Dashboard" },
            { href: "#", icon: "person_add", text: "Add Client", onclick: "openAddClientModal()" },
            { href: "/modular1/modules/invoice/pages/invoice-payments.php", icon: "payment", text: "Payment Reminder" }
        ],
        "invoice-products": [
            { href: "/modular1/modules/invoice/invoice-dashboard.php", icon: "dashboard", text: "Dashboard"},
            { href: "#", icon: "category", text: "Products", tab: "products", class: "sidebar-button", onclick: "fetchProducts('products')" },
            { href: "#", icon: "build", text: "Parts", tab: "parts", class: "sidebar-button", onclick: "fetchProducts('parts')" },
            { href: "#", icon: "directions_car", text: "Vehicles", tab: "vehicles", class: "sidebar-button", onclick: "fetchProducts('vehicles')" },
            { href: "#", icon: "add_circle_outline", text: "Extras", tab: "extras", class: "sidebar-button", onclick: "fetchProducts('extras')" },
            { href: "#", icon: "remove_circle_outline", text: "Services", tab: "services", class: "sidebar-button", onclick: "fetchProducts('services')" }
        ],
        "payments":[
            { href: "/modular1/modules/invoice/invoice-dashboard.php", icon: "dashboard", text: "Dashboard" },
        ],
        "sales-reps":[
            { href: "/modular1/modules/invoice/invoice-dashboard.php", icon: "dashboard", text: "Dashboard" },
            { href: "#", icon: "person_add", text: "Add Sales Rep", onclick: "openAddSalesRepModal()" }
        ],
        "invoice-reports":[
            { href: "/modular1/modules/invoice/invoice-dashboard.php", icon: "dashboard", text: "Dashboard" },
            { href: "#sales-reports", icon: "bar_chart", text: "Sales Reports", onclick: "activateSection('sales-reports')" },
            { href: "#tax-reports", icon: "receipt", text: "Tax Reports", onclick: "activateSection('tax-reports')" },
            { href: "#income-reports", icon: "attach_money", text: "Income Reports", onclick: "activateSection('income-reports')" },
            { href: "#expenses-reports", icon: "money_off", text: "Expenses Reports", onclick: "activateSection('expenses-reports')" },
            { href: "#general-reports", icon: "assessment", text: "General Reports", onclick: "activateSection('general-reports')" }
        ],
        "accounting-dashboard": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
            { href: "/modular1/modules/accounting/pages/general-ledger.php", icon: "book", text: "General Ledger" },
            { href: "/modular1/modules/accounting/pages/chart-of-accounts.php", icon: "account_tree", text: "Chart of Accounts" },
            { href: "/modular1/modules/accounting/pages/trial-balance.php", icon: "balance", text: "Trial Balance" },
            { href: "/modular1/modules/accounting/pages/profit-loss-report.php", icon: "bar_chart", text: "Profit & Loss Report" },
            { href: "/modular1/modules/accounting/pages/balance-sheet.php", icon: "assessment", text: "Balance Sheet" },
            { href: "/modular1/modules/accounting/pages/cash-flow-statement.php", icon: "show_chart", text: "Cash Flow" },
            { href: "/modular1/modules/accounting/pages/reconciliation.php", icon: "sync", text: "Reconciliation" },
            { href: "/modular1/modules/accounting/pages/journal-entries.php", icon: "edit", text: "Journal Entries" }
        ],
        "invoices": [
            { href: "/modular1/modules/invoice/invoice-dashboard.php", icon: "dashboard", text: "Dashboard", },
        ],
        "invoice-setup": [
            { href: "/modular1/modules/invoice/invoice-dashboard.php", icon: "dashboard", text: "Dashboard", },
        ],
        "TandA": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
            { href: "/modular1/modules/timeandatt/pages/employees.php", icon: "people", text: "Employees" },
            { href: "/modular1/modules/timeandatt/pages/timecards.php", icon: "access_time", text: "Timecards" },
            { href: "/modular1/modules/timeandatt/pages/mobile-clocking.php", icon: "phone_android", text: "Mobile Clocking" },
            { href: "/modular1/modules/timeandatt/pages/reports.php", icon: "bar_chart", text: "Reports" },
            { href: "/modular1/modules/timeandatt/pages/devices.php", icon: "devices", text: "Devices" },
            { href: "/modular1/modules/timeandatt/pages/schedules.php", icon: "calendar_today", text: "Schedules" }
        ],
        "schedules": [
            { href: "/modular1/modules/timeandatt/dashboard-TA.php", icon: "dashboard", text: "Dashboard" },
            { href: "/modular1/modules/timeandatt/pages/employees.php", icon: "people", text: "Employees" },
            { href: "/modular1/modules/timeandatt/pages/timecards.php", icon: "access_time", text: "Timecards" },
            { href: "/modular1/modules/timeandatt/pages/mobile-clocking.php", icon: "phone_android", text: "Mobile Clocking" },
            { href: "/modular1/modules/timeandatt/pages/reports.php", icon: "bar_chart", text: "Reports" },
            { href: "/modular1/modules/timeandatt/pages/devices.php", icon: "devices", text: "Devices" },
            { href: "/modular1/modules/timeandatt/pages/schedules.php", icon: "calendar_today", text: "Schedules", active: true }
        ],
        "TA-employees": [
            { href: "/modular1/modules/timeandatt/dashboard-TA.php", icon: "dashboard", text: "Dashboard", },
            { href: "#", icon: "person_add", text: "Add Employee", onclick: "openAddEmployeeModal()" }
        ],
        "hr-dashboard": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
            { href: "/modular1/modules/hr/pages/employees.php", icon: "people", text: "Employees" },
            { href: "/modular1/modules/hr/pages/recruitment.php", icon: "person_add", text: "Recruitment" },
            { href: "/modular1/modules/hr/pages/performance.php", icon: "assessment", text: "Performance" },
            { href: "/modular1/modules/hr/pages/training.php", icon: "school", text: "Training" },
            { href: "/modular1/modules/hr/pages/documents.php", icon: "description", text: "Documents" },
            { href: "/modular1/modules/hr/pages/benefits.php", icon: "health_and_safety", text: "Benefits" },
            { href: "/modular1/modules/hr/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "project-dashboard": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
            { href: "/modular1/modules/project/pages/projects.php", icon: "assignment", text: "Projects" },
            { href: "/modular1/modules/project/pages/tasks.php", icon: "task", text: "Tasks" },
            { href: "/modular1/modules/project/pages/teams.php", icon: "groups", text: "Teams" },
            { href: "/modular1/modules/project/pages/timeline.php", icon: "timeline", text: "Timeline" },
            { href: "/modular1/modules/project/pages/resources.php", icon: "build", text: "Resources" },
            { href: "/modular1/modules/project/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "inventory-dashboard": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
            { href: "/modular1/modules/inventory/pages/items.php", icon: "inventory_2", text: "Items" },
            { href: "/modular1/modules/inventory/pages/stock.php", icon: "store", text: "Stock" },
            { href: "/modular1/modules/inventory/pages/suppliers.php", icon: "local_shipping", text: "Suppliers" },
            { href: "/modular1/modules/inventory/pages/orders.php", icon: "shopping_cart", text: "Orders" },
            { href: "/modular1/modules/inventory/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "crm-dashboard": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
            { href: "/modular1/modules/crm/pages/contacts.php", icon: "contacts", text: "Contacts" },
            { href: "/modular1/modules/crm/pages/leads.php", icon: "trending_up", text: "Leads" },
            { href: "/modular1/modules/crm/pages/opportunities.php", icon: "lightbulb", text: "Opportunities" },
            { href: "/modular1/modules/crm/pages/campaigns.php", icon: "campaign", text: "Campaigns" },
            { href: "/modular1/modules/crm/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "support-dashboard": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
            { href: "/modular1/modules/support/pages/tickets.php", icon: "confirmation_number", text: "Tickets" },
            { href: "/modular1/modules/support/pages/knowledge.php", icon: "menu_book", text: "Knowledge Base" },
            { href: "/modular1/modules/support/pages/faq.php", icon: "help", text: "FAQ" },
            { href: "/modular1/modules/support/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "fleet-dashboard": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
            { href: "/modular1/modules/fleet/pages/vehicles.php", icon: "directions_car", text: "Vehicles" },
            { href: "/modular1/modules/fleet/pages/maintenance.php", icon: "build", text: "Maintenance" },
            { href: "/modular1/modules/fleet/pages/drivers.php", icon: "person", text: "Drivers" },
            { href: "/modular1/modules/fleet/pages/trips.php", icon: "map", text: "Trips" },
            { href: "/modular1/modules/fleet/pages/fuel.php", icon: "local_gas_station", text: "Fuel Log" },
            { href: "/modular1/modules/fleet/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "asset-dashboard": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
            { href: "/modular1/modules/asset/pages/assets.php", icon: "business_center", text: "Assets" },
            { href: "/modular1/modules/asset/pages/maintenance.php", icon: "build", text: "Maintenance" },
            { href: "/modular1/modules/asset/pages/depreciation.php", icon: "trending_down", text: "Depreciation" },
            { href: "/modular1/modules/asset/pages/licenses.php", icon: "vpn_key", text: "Licenses" },
            { href: "/modular1/modules/asset/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "access-dashboard": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
            { href: "/modular1/modules/access/pages/users.php", icon: "people", text: "Users" },
            { href: "/modular1/modules/access/pages/roles.php", icon: "admin_panel_settings", text: "Roles" },
            { href: "/modular1/modules/access/pages/permissions.php", icon: "security", text: "Permissions" },
            { href: "/modular1/modules/access/pages/logs.php", icon: "history", text: "Access Logs" },
            { href: "/modular1/modules/access/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "payroll-dashboard": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
            { href: "/modular1/modules/payroll/pages/salaries.php", icon: "payments", text: "Salaries" },
            { href: "/modular1/modules/payroll/pages/deductions.php", icon: "remove_circle", text: "Deductions" },
            { href: "/modular1/modules/payroll/pages/benefits.php", icon: "add_circle", text: "Benefits" },
            { href: "/modular1/modules/payroll/pages/taxes.php", icon: "receipt", text: "Taxes" },
            { href: "/modular1/modules/payroll/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "mobile-dashboard": [
            { href: "/modular1/main/dashboard.php", icon: "home", text: "Home" },
            { href: "/modular1/modules/mobile/pages/settings.php", icon: "settings", text: "Settings" },
            { href: "/modular1/modules/mobile/pages/users.php", icon: "people", text: "Users" },
            { href: "/modular1/modules/mobile/pages/notifications.php", icon: "notifications", text: "Notifications" },
            { href: "/modular1/modules/mobile/pages/sync.php", icon: "sync", text: "Sync" },
            { href: "/modular1/modules/mobile/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ]
    };
    

    /**
     * Initialize sidebar based on the current body ID
     */
    function initializeSidebar() {
        const sidebarItems = sidebarConfig[bodyId] || [];
        const sidebar = document.querySelector('.modular-nav-items');

        if (!sidebar) return;

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
        'invoice-setup':{
            targetId: '.container',
            toggleClasses: ['collapsed']
        },
        'schedules': {
            targetId: 'schedule-container',
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
            fetch('/path/to/session/status/endpoint')
                .then(response => response.json())
                .then(data => {
                    if (data.tech_logged_in) {
                        window.location.href = 'techlogin.php';
                    } else if (data.user_logged_in) {
                        window.location.href = '../index.php';
                    }
                })
                .catch(error => console.error('Error fetching session status:', error));
        });
    }

    // Initialize sidebar and page-specific logic
    initializeSidebar();
    setupTabs();

});

function checkMultipleAccounts() {
    if (multipleAccounts) {
        window.location.href = "/modular1/main/choose-account.php"; // Redirect if session variable is set
    }
}



