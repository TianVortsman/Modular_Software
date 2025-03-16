# Modular System Components Guide

## Required Components for Every Page

###VERY IMPORTANT
USE UNIQUE CLASSNAMES FOR EVERYTHING ESPECIALLY MODALS

##
Each account number has their own DB

## Sidebar config
create a sidebar config for each new page in (sidebar.js)

### 1. Session Management
```php
<?php
session_start();

// Account Number Handling
if (isset($_GET['account_number'])) {
    $account_number = $_GET['account_number'];
    $_SESSION['account_number'] = $account_number;
    header("Location: dashboard.php");
    exit;
}

if (isset($_SESSION['account_number'])) {
    $account_number = $_SESSION['account_number'];
} else {
    header("Location: techlogin.php");
    exit;
}

// User Name Handling
$userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');
```

### 2. Database Connection (db.php)
```php
// PostgreSQL Connection Parameters
$db_host = 'localhost';
$db_port = '5432';
$db_user = 'Tian';
$db_pass = 'Modul@rdev@2024';
$db_name = $account_number;

// Connection String
$dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";
```

### 3. Required UI Components

#### CSS Files
- `root.css` - Core styles and variables
- `sidebar.css` - Sidebar navigation styles
- Module-specific CSS files as needed

#### JavaScript Files
- `toggle-theme.js` - Theme switching functionality
- `sidebar.js` - Sidebar navigation behavior
- Module-specific JS files as needed

#### PHP Components
- `sidebar.php` - Navigation sidebar
- `loading-modal.php` - Loading state display
  - Modal ID: `unique-loading-modal`
  - Methods: `showLoadingModal()`, `hideLoadingModal()`
  - Usage: Show during async operations
- `response-modal.php` - Response messages
  - Modal ID: `modalResponse`
  - Methods: `showResponseModal(status, message)`
  - Status types: 'success', 'error', 'warning'
  - Usage: Display operation results
- `error-table-modal.php` - Detailed error reporting
  - Modal ID: `errorTableModal`
  - Methods: `showErrorTable(data)`
  - Data structure:
    ```javascript
    {
      totalRows: number,
      successCount: number,
      errors: Array<{
        row: string | number,
        data: string,
        message: string
      }>
    }
    ```
  - Features:
    - Error summary statistics
    - Detailed error table with row numbers
    - Downloadable error report (CSV)
    - Responsive design
  - Usage: Display detailed import/processing errors

## Database Guidelines
- Always use PostgreSQL for database operations
- Use prepared statements for queries
- Include error handling for database operations

## Best Practices
1. Always include session management at the start
2. Include database connection where needed
3. Add required CSS/JS files in the header
4. Include modals before closing body tag
5. Use consistent modal IDs across pages
6. Implement proper error handling
7. Follow PostgreSQL syntax for queries

## Modal Implementation Examples

### Loading Modal
```php
<!-- Loading Modal -->
<div id="unique-loading-modal" class="hidden">
    <div class="unique-modal-content">
        <div class="unique-spinner"></div>
        <p class="modal-message">Loading...</p>
    </div>
</div>
```

### Error Table Modal
```php
<!-- Error Table Modal -->
<div id="errorTableModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2>Import Errors</h2>
            <span class="close">&times;</span>
        </div>
        <div class="modal-body">
            <div class="error-summary">
                <p><strong>Total Rows Processed: </strong><span id="totalRows">0</span></p>
                <p><strong>Successfully Imported: </strong><span id="successRows">0</span></p>
                <p><strong>Failed Rows: </strong><span id="failedRows">0</span></p>
            </div>
            <div class="table-container">
                <table id="errorTable">
                    <thead>
                        <tr>
                            <th>Row #</th>
                            <th>Data</th>
                            <th>Error Message</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Error rows will be inserted here dynamically -->
                    </tbody>
                </table>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn btn-primary" onclick="closeErrorModal()">Close</button>
            <button class="btn btn-secondary" onclick="downloadErrorReport()">Download Report</button>
        </div>
    </div>
</div>
```

### Response Modal
```php
<!-- Response Modal -->
<div id="modalResponse" class="custom-modal hidden">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <span id="modalResponseIcon" class="custom-modal-icon">✔</span>
            <h2 id="modalResponseTitle">Success</h2>
        </div>
        <div class="custom-modal-body">
            <p id="modalResponseMessage">Your request was successful!</p>
        </div>
        <div class="custom-modal-footer">
            <button onclick="closeResponseModal()" class="custom-modal-close-btn">OK</button>
        </div>
    </div>
</div>
```

## JavaScript Modal Functions
```javascript
// Loading Modal
function showLoadingModal(message = "Loading...") {
    const modal = document.getElementById('unique-loading-modal');
    const messageElement = modal.querySelector('.modal-message');
    messageElement.textContent = message;
    modal.classList.remove('hidden');
    modal.style.opacity = 1;
}

function hideLoadingModal() {
    const modal = document.getElementById('unique-loading-modal');
    modal.classList.add('hidden');
    modal.style.opacity = 0;
}

// Error Table Modal
function showErrorTable(data) {
    const modal = document.getElementById('errorTableModal');
    const tbody = document.querySelector('#errorTable tbody');
    const totalRows = document.getElementById('totalRows');
    const successRows = document.getElementById('successRows');
    const failedRows = document.getElementById('failedRows');

    // Update summary statistics
    totalRows.textContent = data.totalRows || 0;
    successRows.textContent = data.successCount || 0;
    failedRows.textContent = data.errors?.length || 0;

    // Clear and populate error table
    tbody.innerHTML = '';
    if (data.errors && data.errors.length > 0) {
        data.errors.forEach(error => {
            tbody.innerHTML += `
                <tr>
                    <td>${error.row || 'N/A'}</td>
                    <td>${error.data || 'N/A'}</td>
                    <td>${error.message || 'Unknown error'}</td>
                </tr>
            `;
        });
    }

    modal.style.display = 'block';
}

function downloadErrorReport() {
    const table = document.getElementById('errorTable');
    const rows = Array.from(table.querySelectorAll('tr'));
    let csv = 'Row #,Data,Error Message\n';
    rows.slice(1).forEach(row => {
        const cells = Array.from(row.cells);
        csv += cells.map(cell => `"${cell.textContent}"`).join(',') + '\n';
    });
    
    const blob = new Blob([csv], { type: 'text/csv' });
    const url = window.URL.createObjectURL(blob);
    const a = document.createElement('a');
    a.href = url;
    a.download = 'import_errors.csv';
    a.click();
    window.URL.revokeObjectURL(url);
}

// Response Modal
function showResponseModal(status, message) {
    const modal = document.getElementById('modalResponse');
    const title = document.getElementById('modalResponseTitle');
    const icon = document.getElementById('modalResponseIcon');
    const msg = document.getElementById('modalResponseMessage');

    msg.innerText = message;

    const statusConfig = {
        success: { title: "Success!", icon: "✔", color: "var(--color-primary)" },
        error: { title: "Error!", icon: "✖", color: "#F44336" },
        warning: { title: "Warning!", icon: "⚠", color: "#FFC107" }
    };

    if (statusConfig[status]) {
        title.innerText = statusConfig[status].title;
        icon.innerHTML = statusConfig[status].icon;
        icon.style.color = statusConfig[status].color;
    }

    modal.classList.remove('hidden');
}

function closeResponseModal() {
    document.getElementById('modalResponse').classList.add('hidden');
}
```

## Error Handling Template
```php
try {
    // Database operations
    $stmt = $conn->prepare("SELECT * FROM table WHERE id = ?");
    $stmt->execute([$id]);
} catch (PDOException $e) {
    error_log("Database Error: " . $e->getMessage());
    showResponseModal('error', 'A database error occurred');
}
```

## Database Schema Overview

### Core Business Tables
- `address` - Stores address information
- `company` - Company details and information
- `company_address` - Links companies to addresses
- `company_contacts` - Company contact persons
- `customer_type` - Types of customers
- `customers` - Customer information
- `customer_address` - Links customers to addresses

### Product and Supplier Tables
- `product_category` - Product categories
- `product_type` - Product types
- `products` - Product information
- `supplier_type` - Types of suppliers
- `suppliers` - Supplier information
- `supplier_products` - Links products to suppliers

### Sales and Order Tables
- `order_status` - Order status types
- `payment_method` - Payment methods
- `payment_status` - Payment status types
- `customer_orders` - Customer order information
- `order_items` - Items in customer orders
- `sales_order` - Sales order information
- `sales_items` - Items in sales orders

### Time and Attendance Tables
- `positions` - Job positions
- `employees` - Employee information
- `shifts` - Shift definitions
- `attendance_records` - Employee attendance
- `break_types` - Types of breaks
- `break_records` - Break records
- `leave_types` - Types of leave
- `leave_balances` - Employee leave balances
- `leave_requests` - Leave requests
- `overtime_categories` - Overtime types
- `overtime_requests` - Overtime requests
- `holidays` - Holiday definitions
- `schedule_templates` - Work schedule templates
- `monthly_rosters` - Monthly work rosters
- `weekly_hours` - Weekly work hours

### Invoice and Purchase Tables
- `invoices` - Invoice information
- `invoice_items` - Items in invoices
- `purchase_orders` - Purchase order information
- `purchase_order_items` - Items in purchase orders
- `purchase_payment` - Purchase payments
- `sales_payment` - Sales payments

### Vehicle Management Tables
- `vehicle` - Vehicle information
- `vehicle_documents` - Vehicle documentation
- `vehicle_history` - Vehicle history records
- `vehicle_maintenance` - Maintenance records
- `vehicle_registration` - Registration information

### Additional Features
- Soft delete support via `deleted_at` timestamp
- Audit trails via `created_at` and `updated_at` timestamps
- Foreign key constraints for data integrity
- Indexes for optimized queries
- UUID and pgcrypto extensions enabled

### Common Fields
Most tables include:
- Primary key (usually `id` or table-specific name)
- Creation timestamp (`created_at`)
- Update timestamp (`updated_at`)
- Soft delete timestamp (`deleted_at`)

### Database Guidelines
1. Always use prepared statements
2. Include proper error handling
3. Use transactions for multiple operations
4. Follow PostgreSQL naming conventions
5. Implement soft deletes when removing data
6. Maintain referential integrity
7. Use appropriate data types
8. Index frequently queried columns

For detailed schema information, refer to `combined_database_setup.sql`.

