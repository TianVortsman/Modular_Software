# Modular System Components Guide

## Recent Updates (System-Wide)

### Authentication Flow Improvements
- Fixed authentication redirect paths throughout the system
- Corrected database connection error handling in login process
- Updated password reset form paths and styling
- Improved account selection workflow for users with multiple accounts
- Enhanced technician login experience with clear UI feedback

### Docker Integration
- Added proper Docker configuration support
- Updated database connection to use environment variables
- Fixed host resolution for containerized environments
- Improved configuration fallbacks for development and production

### PSR-4 Autoloading Implementation
- Restructured application to follow PSR-4 autoloading standards
- Namespace organization matches directory structure
- Simplified class imports and dependency management
- Eliminated manual includes and requires where possible
- Improved code organization and maintainability

## Required Components for Every Page

### Authentication and Session Management
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
    header("Location: ../index.php");
    exit;
}

// User Name Handling
$userName = $_SESSION['user_name'] ?? ($_SESSION['tech_logged_in'] ? $_SESSION['tech_name'] : 'Guest');
```

### Core Database Connection
```php
// Using environment variables with fallbacks
$db_host = getenv('DB_HOST') ?: 'host.docker.internal';
$db_port = getenv('DB_PORT') ?: '5432';
$db_user = getenv('DB_USER') ?: 'Tian';
$db_pass = getenv('DB_PASSWORD') ?: 'Modul@rdev@2024';
$db_name = $account_number;

// Connection String
$dsn = "pgsql:host=$db_host;port=$db_port;dbname=$db_name";
```

## Main Database Structure

The main database contains the following key tables:

### 1. Customers Table
- Primary table for customer records
- Stores company info, account numbers, and client DB references
- Key fields: customer_id, company_name, email, account_number, client_db, status

### 2. Users Table 
- Stores all user accounts
- Includes 2FA configuration
- Links to customers via customer_id foreign key
- Key fields: id, email, name, role, status, customer_id

### 3. Account Numbers Table
- Maps users to account numbers and clock server ports
- Links to users via user_id foreign key
- Key fields: id, account_number, user_id, clock_server_port

### 4. Technicians Table
- Stores technician/admin user accounts
- Separate from regular users
- Key fields: id, email, name, role

### Key Relationships:
- Users -> Customers (Many-to-One)
- Account Numbers -> Users (Many-to-One)

### Important Notes:
- Each customer gets their own database named after their account number
- Clock server ports must be unique
- User roles and statuses are enforced via string fields
- Timestamps are tracked for creation and last login

## UI Components and Asset Organization

### Asset Directory Structure
```
public/
├── assets/
│   ├── css/
│   │   ├── root.css
│   │   ├── sidebar.css
│   │   └── module-specific.css
│   ├── js/
│   │   ├── toggle-theme.js
│   │   ├── sidebar.js
│   │   └── module-specific.js
│   └── img/
│       └── Logo.webp
├── views/
│   ├── dashboard.php
│   └── other-views.php
├── admin/
│   └── techlogin.php
├── account/
│   └── choose-account.php
└── php/
    ├── loading-modal.php
    ├── response-modal.php
    └── error-table-modal.php
```

### Required UI Components

#### CSS Files
- `assets/css/root.css` - Core styles and variables
- `assets/css/sidebar.css` - Sidebar navigation styles
- Module-specific CSS files as needed

#### JavaScript Files
- `assets/js/toggle-theme.js` - Theme switching functionality
- `assets/js/sidebar.js` - Sidebar navigation behavior
- Module-specific JS files as needed

#### PHP Components
- `php/sidebar.php` - Navigation sidebar
- `php/loading-modal.php` - Loading state display
  - Modal ID: `unique-loading-modal`
  - Methods: `showLoadingModal()`, `hideLoadingModal()`
  - Usage: Show during async operations
- `php/response-modal.php` - Response messages
  - Modal ID: `modalResponse`
  - Methods: `showResponseModal(status, message)`
  - Status types: 'success', 'error', 'warning'
  - Usage: Display operation results
- `php/error-table-modal.php` - Detailed error reporting

## PSR-4 Directory Structure

```
src/
├── Core/
│   ├── Auth/
│   │   ├── Authentication.php
│   │   └── TwoFactorAuth.php
│   ├── Database/
│   │   ├── Database.php
│   │   ├── MainDatabase.php
│   │   └── ClientDatabase.php
│   └── Exception/
│       └── DatabaseException.php
├── Controllers/
│   ├── AuthController.php
│   └── UserController.php
├── Models/
│   ├── User.php
│   └── Customer.php
├── Services/
│   ├── DatabaseService.php
│   └── AuthService.php
└── Config/
    └── Database.php
```

### Autoloading with Composer
PSR-4 autoloading is configured in composer.json:

```json
{
    "autoload": {
        "psr-4": {
            "Core\\": "src/Core/",
            "Controllers\\": "src/Controllers/",
            "Models\\": "src/Models/",
            "Services\\": "src/Services/",
            "Config\\": "src/Config/"
        }
    }
}
```

### Class Usage Example
```php
<?php
// No need for require/include statements
namespace Controllers;

use Core\Auth\Authentication;
use Services\DatabaseService;
use Models\User;

class UserController
{
    private $authService;
    private $dbService;
    
    public function __construct()
    {
        $this->authService = new Authentication();
        $this->dbService = DatabaseService::getMainDatabase();
    }
    
    // Controller methods
}
```

## IMPORTANT UI Guidelines

### Sidebar Configuration
Create a sidebar config for each new page in sidebar.js:

```javascript
const sidebarConfig = [
    { href: "dashboard.php", icon: "dashboard", text: "Dashboard" },
    { href: "importing.php", icon: "cloud_upload", text: "Import Data" },
    // ... more items
    { href: "../php/logout.php", icon: "exit_to_app", text: "LogOut" }
];
```

### Modals and UI Components

#### IMPORTANT:
- USE UNIQUE CLASSNAMES FOR EVERYTHING ESPECIALLY MODALS
- Always include required CSS/JS files in the correct order
- Follow the established naming conventions
- Use consistent modal IDs across pages

## Best Practices

1. Always include session management at the start of PHP files
2. Use environment variables with fallbacks for configuration
3. Follow PSR-4 autoloading for class imports
4. Include proper error handling for all operations
5. Use prepared statements for all database queries
6. Implement proper access control checks
7. Use Docker environment variables when available
8. Add required CSS/JS files in the header
9. Include modals before closing body tag

## Database Guidelines
- Always use PostgreSQL for database operations
- Use prepared statements for queries
- Include error handling for database operations
- Follow PostgreSQL syntax for queries

## For additional details about database schema, modals, JavaScript functions, and error handling, refer to detailed documentation.

For any questions or issues, please contact the development team.