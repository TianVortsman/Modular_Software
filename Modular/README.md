# Modular Software System

## Overview
A modern PHP-based modular software system with PSR-4 autoloading, Docker support, and component-based architecture for managing multi-tenant business operations. Features comprehensive error handling, standardized UI components, and a scalable module-based design.

## Table of Contents
- [System Architecture](#system-architecture)
- [Database Architecture](#database-architecture)
- [Module Development Standards](#module-development-standards)
- [JavaScript Modular Structure](#javascript-modular-structure)
- [UI Component Standards](#ui-component-standards)
- [Error Handling System](#error-handling-system)
- [Authentication & Session Management](#authentication--session-management)
- [Development Workflow](#development-workflow)
- [Setup and Configuration](#setup-and-configuration)
- [Best Practices](#best-practices)
- [Contributing](#contributing)

## System Architecture

### PSR-4 Autoloading Structure
The system uses PSR-4 compliant autoloading with two main namespaces:

```json
{
    "autoload": {
        "psr-4": {
            "App\\": "src/",
            "Modules\\": "modules/"
        }
    }
}
```

### Core Directory Structure
```
src/
├── Core/
│   ├── Auth/           # Authentication system
│   ├── Database/       # Database connection classes
│   └── Exception/      # Custom exceptions
├── Controllers/        # System-level controllers
├── Services/          # Business logic services
├── Utils/             # Utility functions
├── UI/                # Global UI components
└── Config/            # System configuration

modules/
├── invoice/           # Example module
│   ├── api/          # Module API endpoints
│   ├── controllers/  # Module controllers
│   ├── css/         # Module-specific styles
│   ├── js/          # Module JavaScript files
│   ├── views/       # Module views
│   └── modals/      # Module modal components
└── [other-modules]/
```

### Namespace Usage Examples
```php
// Core system classes
use App\Core\Auth\Authentication;
use App\Core\Database\MainDatabase;
use App\Services\DatabaseService;
use App\Controllers\AuthController;

// Module classes (manual inclusion currently required)
require_once __DIR__ . '/../controllers/InvoiceController.php';
```

## Database Architecture

### DatabaseService - Centralized Connection Management
The `DatabaseService` class provides three main connection methods:

```php
use App\Services\DatabaseService;

// 1. Main Database - System operations (users, accounts, configuration)
$mainDb = DatabaseService::getMainDatabase();

// 2. Client Database - Business data specific to a customer account
$clientDb = DatabaseService::getClientDatabase($accountNumber, $userName);

// 3. Current Database - Automatically determines based on session
$currentDb = DatabaseService::getCurrentDatabase();
```

### Usage Patterns

**System Operations (Main Database):**
```php
// User authentication, account management, system configuration
$mainDb = DatabaseService::getMainDatabase();
$stmt = $mainDb->executeQuery("SELECT * FROM users WHERE username = ?", [$username]);
```

**Business Operations (Client Database):**
```php
// Customer-specific data (employees, invoices, inventory, etc.)
if (isset($_SESSION['account_number'])) {
    $clientDb = DatabaseService::getClientDatabase($_SESSION['account_number'], $_SESSION['user_name']);
    $stmt = $clientDb->executeQuery("SELECT * FROM employees", []);
}
```

### Session-Based Database Selection
```php
// In module controllers, use session information for automatic database selection
public function __construct() {
    if (isset($_SESSION['account_number'])) {
        $this->db = DatabaseService::getClientDatabase($_SESSION['account_number'], $_SESSION['user_name']);
    } else {
        throw new \Exception("No account number in session");
    }
}
```

## Module Development Standards

### Standard Module Structure
Each module follows this consistent directory layout:

```
modules/your-module/
├── api/              # API endpoints (your-module-api.php)
├── controllers/      # Business logic controllers
├── css/             # Module-specific stylesheets
├── js/              # JavaScript files (see modular structure below)
├── views/           # HTML view files
├── modals/          # Modal component files
└── sql/             # Database schema files (optional)
```

### Module Controller Template
```php
<?php
// Manual inclusion required for module controllers
require_once __DIR__ . '/../../src/Services/DatabaseService.php';

use App\Services\DatabaseService;

class YourModuleController {
    private $db;
    
    public function __construct() {
        // Start session if not already started
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Use client database for business data
        if (isset($_SESSION['account_number'])) {
            $this->db = DatabaseService::getClientDatabase($_SESSION['account_number'], $_SESSION['user_name']);
        } else {
            throw new \Exception("No account number in session");
        }
    }
    
    public function handleRequest() {
        // Your controller logic here
    }
}
```

### Module API Endpoint Template
```php
<?php
require_once __DIR__ . '/../controllers/YourModuleController.php';
require_once __DIR__ . '/../../src/Utils/response.php';

// Start session
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

try {
    $controller = new YourModuleController();
    $result = $controller->handleRequest();
    
    successResponse('Operation successful', ['data' => $result]);
} catch (Exception $e) {
    errorResponse($e->getMessage());
}
```

## JavaScript Modular Structure

### File Organization Pattern
Each module's JavaScript is split into specialized files:

```
js/
├── module-screen.js    # Main screen logic, tab switching, UI state
├── module-form.js      # Form handling, validation, data preparation
├── module-api.js       # API calls, data fetching, server communication
└── module-modal.js     # Modal management, popup interactions
```

### Example: Invoice Module JS Structure
```
js/
├── document-screen.js  # Document listing, filtering, pagination
├── document-form.js    # Invoice form handling and validation
├── document-api.js     # API calls for documents
├── document-modal.js   # Document-related modals
├── client-screen.js    # Client management interface
├── client-form.js      # Client form handling
├── client-api.js       # Client API calls
└── client-modal.js     # Client modals
```

### JavaScript Import Pattern
```javascript
// Import shared utilities
import { buildQueryParams, handleApiResponse } from '../../../public/assets/js/helpers.js';

// Import from related module files
import { searchClients } from './document-api.js';
import { setDocumentFormData } from './document-form.js';
```

## UI Component Standards

### Required Global Components
Every page must include these standardized components:

#### 1. Sidebar Navigation
```php
<?php include '/src/UI/sidebar.php'; ?>
```
- Context-aware navigation
- User information display
- Notification system
- Tutorial system integration

#### 2. Response Modal (Auto-injected)
Automatically available via `helpers.js`:
```javascript
// Success message
showResponseModal('Operation completed successfully!', 'success');

// Error message  
showResponseModal('Something went wrong', 'error');

// Confirmation dialog
const confirmed = await showResponseModal('Are you sure?', 'warning', false, true);
```

#### 3. Loading Modal
```php
<?php include '/src/UI/loading-modal.php'; ?>
```
```javascript
// Show loading
window.showLoadingModal('Processing...');

// Hide loading
hideLoadingModal();
```

### CSS Variables System
All styling uses centralized CSS variables from `root.css`:

```css
/* Always include root.css first */
@import url('/public/assets/css/root.css');

/* Use variables instead of hardcoded values */
.my-component {
    background-color: var(--color-background);
    color: var(--color-text-light);
    padding: var(--spacing-medium);
    border-radius: var(--radius-small);
    border: 1px solid var(--color-border);
}
```

## Error Handling System

### Frontend Error Handling
The system uses a centralized `handleApiResponse` function:

```javascript
// Standard API call pattern
fetch('/api/your-endpoint.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/json' },
    body: JSON.stringify(data)
})
.then(response => response.json())
.then(handleApiResponse)  // Automatically handles errors and shows modals
.then(data => {
    // Handle successful response
})
.catch(error => {
    // handleApiResponse already showed error modal
    console.error('API Error:', error);
});
```

### Backend Error Response Format
```php
// Use standardized response functions
require_once __DIR__ . '/../../src/Utils/response.php';

// Success response
successResponse('Data saved successfully', ['id' => $newId]);

// Error response  
errorResponse('Validation failed', 400, ['errors' => $validationErrors]);
```

### JavaScript Error Logging
Global error handler automatically logs JavaScript errors:
```javascript
// Errors are automatically caught and logged
window.onerror = function(message, source, lineno, colno, error) {
    // Automatically sends error to backend logging
    // Shows user-friendly error modal
};
```

## Authentication & Session Management

### Session Variables
The system maintains these key session variables:
```php
$_SESSION['account_number']  // Customer account identifier
$_SESSION['user_name']       // Logged-in user name
$_SESSION['user_id']         // Unique user ID
$_SESSION['tech_logged_in']  // Boolean for technician access
```

### Authentication Flow
```php
use App\Core\Auth\Authentication;

// Initialize authentication
$auth = new Authentication();

// Handle login
$loginResult = $auth->login($username, $password);

// Check if user is authenticated
if (!$auth->isAuthenticated()) {
    header('Location: /auth/login.php');
    exit;
}
```

### Database Context Based on Authentication
```php
// The system automatically selects the appropriate database
// based on authentication state and session data

// For system operations (login, user management)
$mainDb = DatabaseService::getMainDatabase();

// For business operations (after user login)
$clientDb = DatabaseService::getCurrentDatabase(); // Auto-selects based on session
```

## Development Workflow

### 1. Creating a New Module
```bash
# Create module directory structure
mkdir -p modules/your-module/{api,controllers,css,js,views,modals}

# Create basic files
touch modules/your-module/api/your-module-api.php
touch modules/your-module/controllers/YourModuleController.php
touch modules/your-module/js/your-module-{screen,form,api,modal}.js
touch modules/your-module/css/your-module.css
touch modules/your-module/views/your-module.php
```

### 2. Standard Development Process
Following the user's workflow rules:

1. **HTML Setup** - Build the UI structure first
2. **JavaScript Logic** - Add interactivity and form handling
3. **PHP Controller** - Create business logic controller
4. **API Endpoint** - Create endpoint that uses controller
5. **Database Integration** - Ensure schema matches operations

### 3. Testing Integration
```bash
# Test database connections
php src/Core/Database/test-connection.php

# Run through full workflow
# 1. Create/update HTML forms
# 2. Test JavaScript form handling
# 3. Verify API endpoints
# 4. Confirm database operations
```

## Setup and Configuration

### Prerequisites
- PHP 8.1 or higher
- Composer
- Docker and Docker Compose
- PostgreSQL 13+

### Installation
```bash
# Clone and setup
git clone [repository]
cd Modular

# Install dependencies
composer install
composer dump-autoload

# Start Docker environment
docker-compose up -d

# Initialize database
# Main database setup
psql -h localhost -U Tian -d modular_system < db-init/Main-db/modular_system.sql

# Client database setup (per customer)
# Databases are created automatically when customers are added
```

### Environment Configuration
```bash
# Docker configuration
DB_HOST=host.docker.internal
DB_PORT=5432
DB_USER=Tian
DB_PASSWORD=Modul@rdev@2024
APP_ENV=development
```

## Best Practices

### Database Operations
```php
// ✅ DO: Use DatabaseService for connections
$db = DatabaseService::getCurrentDatabase();

// ✅ DO: Use wrapper methods for queries
$stmt = $db->executeQuery($query, $params);
$results = $db->fetchAll($stmt);

// ❌ DON'T: Use PDO methods directly
$stmt = $db->prepare($query); // Avoid direct PDO access
```

### Error Handling
```php
// ✅ DO: Use standardized response functions
successResponse('Operation completed', ['data' => $result]);
errorResponse('Validation failed', 400);

// ✅ DO: Use handleApiResponse on frontend
.then(handleApiResponse)
.then(data => { /* handle success */ })
```

### CSS Styling
```css
/* ✅ DO: Use CSS variables */
.component {
    color: var(--color-text-light);
    background: var(--color-background);
}

/* ❌ DON'T: Use hardcoded colors */
.component {
    color: #ffffff;
    background: #333333;
}
```

### JavaScript Structure
```javascript
// ✅ DO: Import from helpers.js
import { buildQueryParams, handleApiResponse } from '../../../public/assets/js/helpers.js';

// ✅ DO: Split functionality across files
// screen.js - UI state management
// form.js - Form handling
// api.js - Server communication  
// modal.js - Modal interactions
```

## Contributing

### Code Standards
- Follow PSR-4 autoloading conventions
- Use standardized error handling patterns
- Implement responsive design with CSS variables
- Split JavaScript into modular files
- Include comprehensive error handling

### Module Development Checklist
- [ ] Module follows standard directory structure
- [ ] Controllers use appropriate database connections
- [ ] JavaScript split into screen/form/api/modal files
- [ ] CSS uses root variables instead of hardcoded values
- [ ] Error handling uses `handleApiResponse` pattern
- [ ] All forms include proper validation
- [ ] API endpoints use standardized response functions
- [ ] Modals use global response/loading modal system

### Testing Requirements
- [ ] Frontend form validation works
- [ ] API endpoints return proper response format
- [ ] Database operations use correct connection
- [ ] Error states display user-friendly messages
- [ ] Loading states show appropriate modals
- [ ] Responsive design works across devices

---

## System Evolution Notes

This README reflects the current state of the codebase as of 2024. The system has evolved from basic PHP to a sophisticated modular architecture with:

- **PSR-4 Autoloading**: Professional namespace organization
- **Centralized Database Management**: `DatabaseService` with main/client separation
- **Modular JavaScript**: File-per-responsibility organization
- **Comprehensive Error Handling**: Frontend/backend integration
- **Standardized UI Components**: Reusable modal and sidebar systems
- **CSS Variable System**: Maintainable theming approach

The architecture supports multi-tenant operations with secure database isolation, modern JavaScript patterns, and professional error handling throughout the stack.