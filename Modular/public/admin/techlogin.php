<?php
session_start();
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Debug mode - set to false in production
$debug = true;

if ($debug) {
    // Output server path information
    error_log("Current file: " . __FILE__);
    error_log("Current directory: " . __DIR__);
    error_log("Document root: " . $_SERVER['DOCUMENT_ROOT']);
}

// Direct include of the controller file instead of using an autoloader
$controllerPath = __DIR__ . '/../../../src/Controllers/ClockServerController.php';
$altPath = __DIR__ . '/../../src/Controllers/ClockServerController.php';
$relativePath = 'src/Controllers/ClockServerController.php';

if ($debug) {
    error_log("Trying controller path: " . $controllerPath . " - " . (file_exists($controllerPath) ? "EXISTS" : "NOT FOUND"));
    error_log("Trying alt path: " . $altPath . " - " . (file_exists($altPath) ? "EXISTS" : "NOT FOUND"));
    error_log("Trying relative path: " . $relativePath . " - " . (file_exists($relativePath) ? "EXISTS" : "NOT FOUND"));
}

$controllerLoaded = false;

if (file_exists($controllerPath)) {
    require_once $controllerPath;
    $controllerLoaded = true;
} elseif (file_exists($altPath)) {
    require_once $altPath;
    $controllerLoaded = true;
} elseif (file_exists($relativePath)) {
    require_once $relativePath;
    $controllerLoaded = true;
} 

// If we still couldn't find the controller, implement it directly here as a fallback
if (!$controllerLoaded) {
    if ($debug) {
        error_log("Using inline controller implementation as fallback");
    }
    
    // Define a fallback controller class without namespace
    class FallbackClockServerController {
        // Maximum number of retry attempts
        private static $maxRetries = 3;
        // Delay between retries in microseconds (0.5 seconds)
        private static $retryDelay = 500000;
        
        public static function getStatus($accountNumber) {
            // Try multiple possible URLs for the clock server with retry logic
            $urls = [
                "http://hikvision-server:3000/clock/status/$accountNumber", // Docker internal name
                "http://localhost:3000/clock/status/$accountNumber",           // Local fallback
                "http://127.0.0.1:3000/clock/status/$accountNumber"            // Explicit IP fallback
            ];
            return self::callWithRetries($urls);
        }
    
        public static function startServer($accountNumber) {
            $urls = [
                "http://hikvision-server:3000/clock/start/$accountNumber",
                "http://localhost:3000/clock/start/$accountNumber",
                "http://127.0.0.1:3000/clock/start/$accountNumber"
            ];
            return self::callWithRetries($urls, 'POST');
        }
    
        public static function stopServer($accountNumber) {
            $urls = [
                "http://hikvision-server:3000/clock/stop/$accountNumber",
                "http://localhost:3000/clock/stop/$accountNumber",
                "http://127.0.0.1:3000/clock/stop/$accountNumber"
            ];
            return self::callWithRetries($urls, 'POST');
        }
        
        private static function callWithRetries($urls, $method = 'GET') {
            $lastError = null;
            
            // Try each URL until one works
            foreach ($urls as $url) {
                // Implement retry logic for each URL
                for ($attempt = 1; $attempt <= self::$maxRetries; $attempt++) {
                    if ($debug) {
                        error_log("Attempt $attempt for URL: $url");
                    }
                    
                    $result = self::call($url, $method);
                    
                    // If successful, return immediately
                    if (!isset($result['error'])) {
                        if ($debug) {
                            error_log("Success for URL: $url on attempt $attempt");
                        }
                        return $result;
                    }
                    
                    // If this wasn't the last attempt, wait before retrying
                    if ($attempt < self::$maxRetries) {
                        if ($debug) {
                            error_log("Retrying $url in " . (self::$retryDelay / 1000000) . " seconds...");
                        }
                        usleep(self::$retryDelay);
                    }
                }
                
                // Store the last error for this URL
                $lastError = $result;
            }
            
            // If all URLs and retries failed, return the last error
            return $lastError;
        }
    
        private static function call($url, $method = 'GET') {
            global $debug;
            
            $opts = [
                "http" => [
                    "method" => $method,
                    "header" => "Content-Type: application/json",
                    "ignore_errors" => true,  // Continue even if server returns an error
                    "timeout" => 3            // Short timeout to avoid long waits
                ]
            ];
            
            // Set error reporting level to suppress warnings during this call
            $errorLevel = error_reporting(0);
            
            try {
                $context = stream_context_create($opts);
                $response = @file_get_contents($url, false, $context);
                
                // Get response status code
                $status_line = $http_response_header[0] ?? '';
                preg_match('{HTTP\/\S*\s(\d{3})}', $status_line, $match);
                $status = $match[1] ?? 500;
                
                // Handle response based on status code
                if ($status >= 200 && $status < 300) {
                    // Success response
                    $decoded = json_decode($response, true);
                    if ($decoded) {
                        return $decoded;
                    } else {
                        return ['success' => true, 'running' => $status === 200];
                    }
                } else {
                    // Error response
                    if ($debug) {
                        error_log("HTTP error $status for URL: $url");
                    }
                    return [
                        'success' => false,
                        'error' => "Clock server returned error (HTTP $status)",
                        'details' => "The clock server is either not available or returned an error. Please ensure the modular_clockserver container is running."
                    ];
                }
            } catch (Exception $e) {
                if ($debug) {
                    error_log("Exception for URL $url: " . $e->getMessage());
                }
                return ['success' => false, 'error' => $e->getMessage()];
            } finally {
                // Restore error reporting level
                error_reporting($errorLevel);
            }
        }
    }
    
    if ($debug) {
        // Show warning for debugging
        echo "<div style='background: #ffd; border: 1px solid #db0; padding: 10px; margin: 10px;'>";
        echo "<h3>Using Fallback Controller</h3>";
        echo "<p>The ClockServerController file could not be found at expected locations. Using inline implementation.</p>";
        echo "</div>";
    }
} else if ($debug) {
    error_log("Controller successfully loaded from file");
}

// Handle AJAX requests for clock server
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'], $_POST['account'])) {
    $account = $_POST['account'];
    
    // Use the appropriate controller class based on what's available
    if ($controllerLoaded && class_exists('\\App\\Controllers\\ClockServerController')) {
        $controller = '\\App\\Controllers\\ClockServerController';
    } else {
        $controller = 'FallbackClockServerController';
    }
    
    switch ($_POST['action']) {
        case 'status':
            echo json_encode($controller::getStatus($account));
            break;
        case 'start':
            echo json_encode($controller::startServer($account));
            break;
        case 'stop':
            echo json_encode($controller::stopServer($account));
            break;
    }
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?php echo $_SESSION['csrf_token']; ?>">
    <title>Customer Management Dashboard</title>
    <link rel="stylesheet" href="../assets/css/root.css">
    <link rel="stylesheet" href="../assets/css/techlogin.css">
    <script src="../assets/js/toggle-theme.js" type="module"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <script>
        // Flag to indicate we're in technician mode for sidebar.js
        const isTechnicianUser = true;
    </script>
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
                    <input type="text" id="search-bar" placeholder="Search customers by name, email, or account number...">
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
                    <button class="button primary" onclick="loginAsTechnician()">
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

                <!-- Account Settings Tab -->
                <div id="account-settings" class="tab-content">
                    <div class="tab-header">
                        <h3>Account Settings</h3>
                    </div>
                    <form id="account-settings-form" class="settings-form">
                        <!-- Clock Server Status Section -->
                        <div class="settings-section">
                            <h4>Clock Server Status</h4>
                            <div class="server-status-container">
                                <div class="status-indicator">
                                    <span>Status: </span>
                                    <span id="server-status" class="status-badge">Checking...</span>
                                </div>
                                <button type="button" id="toggle-server" class="button primary">
                                    <i class="material-icons">power_settings_new</i>
                                    <span id="toggle-text">Loading...</span>
                                </button>
                            </div>
                        </div>
                        
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

    <!-- Debug Link -->
    <div style="position: fixed; bottom: 10px; right: 10px; font-size: 12px; opacity: 0.5;">
        <a href="../tech_debug.php" target="_blank">Session Debug</a>
    </div>

    <!-- Scripts -->
    <script src="../assets/js/techlogin.js"></script>
    <?php include '../../src/UI/loading-modal.php'; ?>
    <?php include '../../src/UI/response-modal.php'; ?>
    <?php include '../../src/UI/error-table-modal.php'; ?>
</body>
</html>
