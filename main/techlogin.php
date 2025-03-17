<?php
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title>Customer Management Dashboard</title>
    <link rel="stylesheet" href="../css/root.css">
    <link rel="stylesheet" href="../css/techlogin.css">
    <script src="../js/toggle-theme.js" type="module"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <div class="dashboard-container">
        <!-- Header Section -->
        <header class="admin-header">
            <div class="header-left">
                <h1>Customer Management</h1>
                <p class="subtitle">Manage your customer accounts and devices</p>
            </div>
            <div class="header-actions">
                <button onclick="startDeviceDiscovery()" class="button discovery-btn">
                    <i class="material-icons">radar</i>
                    Device Discovery
                </button>
                <button onclick="openAddCustomerModal()" class="button primary">
                    <i class="material-icons">person_add</i>
                    Add Customer
                </button>
            </div>
        </header>

        <!-- Main Content Section -->
        <main class="dashboard-content">
            <!-- Search and Filter Section -->
            <div class="search-container">
                <div class="search-wrapper">
                    <i class="material-icons search-icon">search</i>
                    <input type="text" id="search-bar" placeholder="Search customers by name, email, or account number..." onkeyup="searchCustomers()">
                </div>
                <div class="filter-wrapper">
                    <select id="status-filter" class="filter-select" onchange="filterCustomers()">
                        <option value="all">All Status</option>
                        <option value="active">Active</option>
                        <option value="inactive">Inactive</option>
                        <option value="pending">Pending</option>
                    </select>
                    <select id="sort-by" class="filter-select" onchange="sortCustomers()">
                        <option value="name">Sort by Name</option>
                        <option value="date">Sort by Date</option>
                        <option value="devices">Sort by Devices</option>
                        <option value="activity">Sort by Activity</option>
                    </select>
                </div>
            </div>

            <!-- Table Section -->
            <div class="table-container">
                <div class="table-header">
                    <div class="table-title">
                        <h2>Customer List</h2>
                        <span class="customer-count">0 customers</span>
                    </div>
                    <div class="table-actions">
                        <select id="rows-per-page" class="rows-per-page" onchange="updateRowsPerPage()">
                            <option value="10">10 per page</option>
                            <option value="25">25 per page</option>
                            <option value="50">50 per page</option>
                            <option value="100">100 per page</option>
                        </select>
                        <button class="button secondary" onclick="exportCustomerData()">
                            <i class="material-icons">download</i>
                            Export
                        </button>
                    </div>
                </div>

                <div class="table-responsive">
                    <table id="customer-table">
                        <thead>
                            <tr>
                                <th class="sortable" onclick="sortByColumn('name')">
                                    Customer Name
                                    <i class="material-icons sort-icon">unfold_more</i>
                                </th>
                                <th class="sortable" onclick="sortByColumn('company')">
                                    Company
                                    <i class="material-icons sort-icon">unfold_more</i>
                                </th>
                                <th class="sortable" onclick="sortByColumn('email')">
                                    Email
                                    <i class="material-icons sort-icon">unfold_more</i>
                                </th>
                                <th>Account Number</th>
                                <th class="sortable" onclick="sortByColumn('devices')">
                                    Devices
                                    <i class="material-icons sort-icon">unfold_more</i>
                                </th>
                                <th class="sortable" onclick="sortByColumn('status')">
                                    Status
                                    <i class="material-icons sort-icon">unfold_more</i>
                                </th>
                                <th class="sortable" onclick="sortByColumn('lastLogin')">
                                    Last Login
                                    <i class="material-icons sort-icon">unfold_more</i>
                                </th>
                                <th class="actions-column">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="customer-body">
                            <!-- Example row structure -->
                            <tr class="customer-row">
                                <td>John Doe</td>
                                <td>Tech Corp Ltd</td>
                                <td>john@techcorp.com</td>
                                <td>ACC-001234</td>
                                <td>
                                    <div class="device-stats">
                                        <span class="device-total">12</span>
                                        <span class="device-active">8 active</span>
                                    </div>
                                </td>
                                <td>
                                    <span class="status-badge active">Active</span>
                                </td>
                                <td>2024-03-16 14:30</td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="icon-button" onclick="viewCustomer(1)" title="View Details">
                                            <i class="material-icons">visibility</i>
                                        </button>
                                        <button class="icon-button" onclick="editCustomer(1)" title="Edit">
                                            <i class="material-icons">edit</i>
                                        </button>
                                        <button class="icon-button" onclick="manageDevices(1)" title="Manage Devices">
                                            <i class="material-icons">devices</i>
                                        </button>
                                        <button class="icon-button danger" onclick="deleteCustomer(1)" title="Delete">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Enhanced Pagination -->
                <div class="pagination-wrapper">
                    <div class="pagination-info">
                        Showing <span id="showing-start">1</span> to <span id="showing-end">10</span> of <span id="total-entries">100</span> entries
                    </div>
                    <div class="pagination-container">
                        <button class="pagination-button" onclick="goToFirstPage()" title="First Page">
                            <i class="material-icons">first_page</i>
                        </button>
                        <button class="pagination-button" onclick="goToPreviousPage()" title="Previous Page">
                            <i class="material-icons">chevron_left</i>
                        </button>
                        <div id="page-numbers" class="page-numbers">
                            <button class="pagination-button active">1</button>
                            <button class="pagination-button">2</button>
                            <button class="pagination-button">3</button>
                            <button class="pagination-button">4</button>
                            <button class="pagination-button">5</button>
                        </div>
                        <button class="pagination-button" onclick="goToNextPage()" title="Next Page">
                            <i class="material-icons">chevron_right</i>
                        </button>
                        <button class="pagination-button" onclick="goToLastPage()" title="Last Page">
                            <i class="material-icons">last_page</i>
                        </button>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer Section -->
        <footer class="admin-footer">
            <div class="footer-content">
                <p>&copy; <?= date('Y') ?> Modular Software. All rights reserved.</p>
                <div class="footer-links">
                    <a href="#">Privacy Policy</a>
                    <a href="#">Terms of Service</a>
                    <a href="#">Support</a>
                </div>
            </div>
        </footer>
    </div>

    <!-- Modals -->
    <!-- Client Devices Modal -->
    <div id="client-devices-modal" class="modal">
        <div class="modal-content large">
            <div class="modal-header">
                <h2>
                    <i class="material-icons">devices</i>
                    Client Devices - <span id="client-name"></span>
                </h2>
                <button class="close-button">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <div class="device-filters">
                    <div class="filter-group">
                        <select id="status-filter" class="filter-select">
                            <option value="all">All Status</option>
                            <option value="online">Online</option>
                            <option value="offline">Offline</option>
                        </select>
                        <select id="type-filter" class="filter-select">
                            <option value="all">All Types</option>
                            <option value="camera">Cameras</option>
                            <option value="nvr">NVRs</option>
                            <option value="dvr">DVRs</option>
                        </select>
                    </div>
                    <div class="search-wrapper">
                        <i class="material-icons">search</i>
                        <input type="text" id="device-search" placeholder="Search devices...">
                    </div>
                </div>
                
                <div class="devices-grid">
                    <!-- Devices populated dynamically -->
                </div>
            </div>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div id="add-customer-modal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>
                    <i class="material-icons">person_add</i>
                    Add New Customer
                </h2>
                <button class="close-button" onclick="closeAddCustomerModal()">
                    <i class="material-icons">close</i>
                </button>
            </div>
            <div class="modal-body">
                <div class="tabs">
                    <button class="tab-button active" onclick="openTab(event, 'general-info')">
                        <i class="material-icons">info</i>
                        General Info
                    </button>
                    <button class="tab-button" onclick="openTab(event, 'modules-access')">
                        <i class="material-icons">apps</i>
                        Modules
                    </button>
                    <button class="tab-button" onclick="openTab(event, 'contact-details')">
                        <i class="material-icons">contact_phone</i>
                        Contact
                    </button>
                    <button class="tab-button" onclick="openTab(event, 'additional-info')">
                        <i class="material-icons">note_add</i>
                        Additional
                    </button>
                </div>

                <form id="add-customer-form" action="../php/add_customer.php" method="POST">
                    <!-- General Info Tab -->
                    <div id="general-info" class="tab-content active">
                        <div class="form-group">
                            <label for="company_name">Company Name</label>
                            <input type="text" id="company_name" name="company_name" required>
                        </div>

                        <div class="form-group">
                            <label for="customer_email">Email</label>
                            <input type="email" id="customer_email" name="email" required>
                        </div>

                        <div class="form-group">
                            <label for="account_number">Account Number</label>
                            <input type="text" id="account_number" name="account_number" required>
                        </div>

                        <div class="form-group">
                            <label for="start_date">Start Date</label>
                            <input type="date" id="start_date" name="start_date" required>
                        </div>
                    </div>

                    <!-- Modules Access Tab -->
                    <div id="modules-access" class="tab-content">
                        <div class="checkbox-group">
                            <label class="checkbox-label">
                                <input type="checkbox" name="modules[]" value="Time & Attendance">
                                <span class="checkbox-text">Time & Attendance</span>
                            </label>
                            <label class="checkbox-label">
                                <input type="checkbox" name="modules[]" value="Payroll Management">
                                <span class="checkbox-text">Payroll Management</span>
                            </label>
                            <!-- Add other modules similarly -->
                        </div>
                    </div>

                    <!-- Contact Details Tab -->
                    <div id="contact-details" class="tab-content">
                        <div class="form-group">
                            <label for="address">Address</label>
                            <input type="text" id="address" name="address" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City</label>
                                <input type="text" id="city" name="city" required>
                            </div>
                            <div class="form-group">
                                <label for="phone">Phone Number</label>
                                <input type="text" id="phone" name="phone" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="contact_notes">Contact Notes</label>
                            <textarea id="contact_notes" name="contact_notes" rows="4"></textarea>
                        </div>
                    </div>

                    <!-- Additional Info Tab -->
                    <div id="additional-info" class="tab-content">
                        <div class="form-group">
                            <label for="notes">Additional Notes</label>
                            <textarea id="notes" name="notes" rows="6"></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <button type="button" class="button secondary" onclick="closeAddCustomerModal()">Cancel</button>
                        <button type="submit" class="button primary">Add Customer</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Customer Management Modal -->
    <div id="customerModal" class="modal">
        <div class="modal-content extra-large">
            <div class="modal-header">
                <div class="modal-title">
                    <h2>
                        <i class="material-icons">business</i>
                        Manage Customer: <span id="customer-name-title"></span>
                    </h2>
                    <span id="customer-account-number"></span>
                </div>
                <div class="modal-actions">
                    <button class="button primary" onclick="loginAsCustomer()">
                        <i class="material-icons">login</i>
                        Login as Technician
                    </button>
                    <button class="close-button" onclick="closeCustomerModal()">
                        <i class="material-icons">close</i>
                    </button>
                </div>
            </div>

            <div class="modal-body">
                <div class="tabs">
                    <button class="tab-button active" data-tab="users-management">
                        <i class="material-icons">group</i>
                        Users
                    </button>
                    <button class="tab-button" data-tab="modules-permissions">
                        <i class="material-icons">apps</i>
                        Modules & Permissions
                    </button>
                    <button class="tab-button" data-tab="clock-machines">
                        <i class="material-icons">timer</i>
                        Clock Machines
                    </button>
                    <button class="tab-button" data-tab="account-settings">
                        <i class="material-icons">settings</i>
                        Account
                    </button>
                    <button class="tab-button" data-tab="data-storage">
                        <i class="material-icons">storage</i>
                        Data Storage
                    </button>
                </div>

                <!-- Users Management Tab -->
                <div id="users-management" class="tab-content active">
                    <div class="tab-header">
                        <h3>User Management</h3>
                        <button class="button primary" onclick="addNewUser()">
                            <i class="material-icons">person_add</i>
                            Add New User
                        </button>
                    </div>
                    <div class="users-list">
                        <table class="management-table">
                            <thead>
                                <tr>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>Role</th>
                                    <th>Last Login</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="users-table-body"></tbody>
                        </table>
                    </div>
                </div>

                <!-- Modules & Permissions Tab -->
                <div id="modules-permissions" class="tab-content">
                    <div class="tab-header">
                        <h3>Modules & Permissions</h3>
                        <div class="module-actions">
                            <button class="button secondary" onclick="saveModuleSettings()">
                                <i class="material-icons">save</i>
                                Save Changes
                            </button>
                        </div>
                    </div>
                    <div class="modules-grid">
                        <div class="module-section">
                            <h4>Core Modules</h4>
                            <div class="module-list" id="core-modules"></div>
                        </div>
                        <div class="module-section">
                            <h4>Additional Features</h4>
                            <div class="module-list" id="additional-features"></div>
                        </div>
                        <div class="module-section">
                            <h4>Mobile Access</h4>
                            <div class="module-list" id="mobile-features"></div>
                        </div>
                    </div>
                </div>

                <!-- Clock Machines Tab -->
                <div id="clock-machines" class="tab-content">
                    <div class="tab-header">
                        <h3>Clock Machines</h3>
                        <div class="machine-actions">
                            <button class="button secondary" onclick="scanForDevices()">
                                <i class="material-icons">search</i>
                                Scan Network
                            </button>
                            <button class="button primary" onclick="addNewMachine()">
                                <i class="material-icons">add_circle</i>
                                Add Machine
                            </button>
                        </div>
                    </div>
                    
                    <!-- Clock Server Port Section -->
                    <div class="settings-section">
                        <h4>Clock Server Configuration</h4>
                        <div class="form-group">
                            <label for="clockServerPort">Clock Server Port</label>
                            <div class="display-field">
                                <span id="clockServerPort" class="port-display">Not configured</span>
                            </div>
                            <small class="help-text">This port is used by the clock server to listen for this customer's clock machines.</small>
                        </div>
                        <div class="server-status">
                            <div id="server-status-indicator" class="status-indicator"></div>
                            <span id="server-status-text">Checking server status...</span>
                        </div>
                    </div>

                    <!-- Devices Table Section -->
                    <div class="devices-section">
                        <h4>Connected Devices</h4>
                        <div class="table-responsive">
                            <table class="management-table" id="devicesTable">
                                <thead>
                                    <tr>
                                        <th>Device ID</th>
                                        <th>Name</th>
                                        <th>IP Address</th>
                                        <th>Status</th>
                                        <th>Last Online</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody id="devicesTableBody">
                                    <!-- Devices will be loaded here -->
                                </tbody>
                            </table>
                        </div>
                        <div id="devicesLoading" class="loading-indicator">
                            <div class="spinner"></div>
                            <p>Loading devices...</p>
                        </div>
                        <div id="noDevicesMessage" class="no-data-message hidden">
                            <p>No devices found for this customer.</p>
                            <p>Click "Add Machine" to add a device manually, or devices will be automatically added when they connect to the clock server.</p>
                        </div>
                    </div>
                </div>

                <!-- Account Settings Tab -->
                <div id="account-settings" class="tab-content">
                    <div class="tab-header">
                        <h3>Account Settings</h3>
                    </div>
                    <form id="account-settings-form" class="settings-form">
                        <div class="settings-section">
                            <h4>Company Information</h4>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Company Name</label>
                                    <input type="text" name="company_name" id="company_name">
                                </div>
                                <div class="form-group">
                                    <label>Tax ID</label>
                                    <input type="text" name="tax_id" id="tax_id">
                                </div>
                                <div class="form-group">
                                    <label>Industry</label>
                                    <select name="industry" id="industry"></select>
                                </div>
                                <div class="form-group">
                                    <label>Time Zone</label>
                                    <select name="timezone" id="timezone"></select>
                                </div>
                            </div>
                        </div>
                        <div class="settings-section">
                            <h4>Billing Information</h4>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Billing Cycle</label>
                                    <select name="billing_cycle" id="billing_cycle"></select>
                                </div>
                                <div class="form-group">
                                    <label>Payment Method</label>
                                    <select name="payment_method" id="payment_method"></select>
                                </div>
                            </div>
                        </div>
                        <div class="settings-section">
                            <h4>Support & Maintenance</h4>
                            <div class="form-grid">
                                <div class="form-group">
                                    <label>Support Level</label>
                                    <select name="support_level" id="support_level"></select>
                                </div>
                                <div class="form-group">
                                    <label>Maintenance Window</label>
                                    <select name="maintenance_window" id="maintenance_window"></select>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Data Storage Tab -->
                <div id="data-storage" class="tab-content">
                    <div class="tab-header">
                        <h3>Data Storage & Archiving</h3>
                    </div>
                    <div class="storage-overview">
                        <div class="storage-stats">
                            <div class="stat-card">
                                <h4>Storage Usage</h4>
                                <div class="progress-bar">
                                    <div class="progress" id="storage-usage"></div>
                                </div>
                                <span id="storage-details"></span>
                            </div>
                            <div class="stat-card">
                                <h4>Database Size</h4>
                                <div class="progress-bar">
                                    <div class="progress" id="database-usage"></div>
                                </div>
                                <span id="database-details"></span>
                            </div>
                        </div>
                        <div class="archive-settings">
                            <h4>Archiving Settings</h4>
                            <div class="form-group">
                                <label>Auto-Archive Data Older Than</label>
                                <select name="archive_age" id="archive_age">
                                    <option value="3">3 months</option>
                                    <option value="6">6 months</option>
                                    <option value="12">1 year</option>
                                    <option value="24">2 years</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label>Archive Location</label>
                                <select name="archive_location" id="archive_location">
                                    <option value="local">Local Storage</option>
                                    <option value="cloud">Cloud Storage</option>
                                </select>
                            </div>
                            <div class="archive-actions">
                                <button class="button secondary" onclick="startArchiving()">
                                    <i class="material-icons">archive</i>
                                    Archive Now
                                </button>
                                <button class="button primary" onclick="downloadArchive()">
                                    <i class="material-icons">download</i>
                                    Download Archive
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../js/techlogin.js"></script>
    <script src="../js/add-customer.js"></script>
    <script src="../js/fetch-customerdata.js"></script>
    <script src="../js/clock-server.js"></script>

    <?php include '../php/loading-modal.php'; ?>
    <?php include '../php/response-modal.php'; ?>
</body>
</html>
