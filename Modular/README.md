markdown
Copy code
# Modular Software System

## Overview
A modern PHP-based modular software system implementing PSR-4 standards, Docker support, and component-based architecture for managing customer data, user accounts, and business operations.

## Table of Contents
- [Core Features](#core-features)
- [System Architecture](#system-architecture)
- [UI Components](#ui-components)
- [Asset Structure](#asset-structure)
- [Database Structure](#database-structure)
- [Setup and Configuration](#setup-and-configuration)
- [Best Practices](#best-practices)
- [Recent Updates](#recent-updates)
- [Troubleshooting](#troubleshooting)
- [Getting Started](#getting-started)
- [Testing](#testing)
- [Contributing](#contributing)
- [Support](#support)
- [License](#license)
- [Module Development](#module-development)

## Core Features
- PSR-4 compliant architecture
- Docker containerization support
- Standardized modal system
- Component-based UI
- PostgreSQL database integration
- Secure authentication flow
- Scalable multi-tenant architecture
- Responsive design principles

## System Architecture

### PSR-4 Directory Structure
src/ ├── Core/ │ ├── Auth/ │ │ ├── Authentication.php │ │ └── TwoFactorAuth.php │ ├── Database/ │ │ ├── Database.php │ │ ├── MainDatabase.php │ │ └── ClientDatabase.php │ └── Exception/ │ └── DatabaseException.php ├── Controllers/ │ ├── AuthController.php │ └── UserController.php ├── Models/ │ ├── User.php │ └── Customer.php ├── Services/ │ ├── DatabaseService.php │ └── AuthService.php └── Config/ └── Database.php

Copy code

### Namespace Structure
The system uses PSR-4 autoloading with the following namespace structure:
```php


NB!!// All classes use App\ as the root namespace
use App\Core\Auth\Authentication;
use App\Core\Database\MainDatabase;
use App\Controllers\UserController;
use App\Services\DatabaseService;
```

### Composer Autoloading Configuration
```json
"autoload": {
    "psr-4": {
        "App\\": "src/",
        "App\\Controllers\\": "src/Controllers/",
        "App\\Core\\": "src/Core/",
        "App\\Services\\": "src/Services/",
        "App\\Config\\": "src/Config/"
    }
}
```

### Database Service Implementation
The system uses a centralized DatabaseService class for managing connections:

```php
// Example usage of DatabaseService
use App\Services\DatabaseService;

// Get main database connection
$mainDb = DatabaseService::getMainDatabase();

// Get client-specific database
$clientDb = DatabaseService::getClientDatabase($accountNumber, $userName);

// Get current database based on session
$currentDb = DatabaseService::getCurrentDatabase();
```

### Authentication System
Implements secure user authentication with:
- Multi-factor authentication support
- Session management with configurable timeouts
- Password reset and recovery workflow
- Account management across multiple tenants
- Role-based access control (RBAC)
- Technician/admin separate login paths and permissions

## UI Components
### Required Components - to be included and used in all pages - if incorrect or duplicated to be removed and restructured to use these below
- Loading Modal (`unique-loading-modal`) - Displays during async operations
- Response Modal (`modalResponse`) - Shows operation results to users
- Error Table Modal (`errorTableModal`) - Presents detailed error information
- Navigation Sidebar - Context-aware navigation system
- Sidebar also includes the notifications
- Sibar is located in src/ui.sidebar.php

## Asset Structure
Copy code
public/
├── assets/
│   ├── css/
│   │   ├── root.css - always to be include and read to use the varibles
│   │   ├── sidebar.css
│   │   └── module-specific.css
│   ├── js/
│   │   ├── toggle-theme.js
│   │   ├── sidebar.js
│   │   └── module-specific.js
│   └── img/
└── views/
Database Structure
### Main Tables
- Customers: Company information and account management
- Users: User accounts and authentication data
- Account Numbers: Account number mapping and clock server configuration
- Technicians: Admin/technician account management

### Key Relationships
- Users -> Customers (Many-to-One)
- Account Numbers -> Users (Many-to-One)

## Setup and Configuration
### Docker Environment
```yaml
# Example docker-compose.yml configuration
services:
  app:
    build: .
    ports:
      - "8080:80"
    volumes:
      - ./src:/var/www/html/src
    environment:
      - DB_HOST=host.docker.internal
      - DB_PORT=5432
      - DB_USER=Tian
      - DB_PASSWORD=Modul@rdev@2024
      - APP_ENV=development
  
  postgres:
    image: postgres:13
    ports:
      - "5432:5432"
    environment:
      - POSTGRES_USER=Tian
      - POSTGRES_PASSWORD=Modul@rdev@2024
      - POSTGRES_DB=modular
    volumes:
      - postgres_data:/var/lib/postgresql/data

volumes:
  postgres_data:
```

### Composer Configuration
```json
{
    "name": "organization/modular-system",
    "description": "Modern PHP-based modular software system",
    "type": "project",
    "require": {
        "php": ">=8.1",
        "ext-pdo": "*",
        "monolog/monolog": "^2.8",
        "vlucas/phpdotenv": "^5.5"
    },
    "require-dev": {
        "phpunit/phpunit": "^9.5",
        "squizlabs/php_codesniffer": "^3.7"
    },
    "autoload": {
        "psr-4": {
            "App\\": "src/",
            "App\\Controllers\\": "src/Controllers/",
            "App\\Core\\": "src/Core/",
            "App\\Services\\": "src/Services/",
            "App\\Config\\": "src/Config/"
        }
    },
    "autoload-dev": {
        "psr-4": {
            "Tests\\": "tests/"
        }
    },
    "scripts": {
        "test": "phpunit",
        "cs": "phpcs src"
    }
}
```

## Best Practices
### Code Organization
- Follow PSR-4 autoloading standards with App\ namespace
- Use namespaced classes
- Implement proper error handling with custom exception classes
- Maintain consistent directory structure

### Database Operations
- Use DatabaseService for all database connections
- Implement prepared statements to prevent SQL injection
- Handle connection errors gracefully with appropriate fallbacks
- Use appropriate PostgreSQL syntax and features
- Implement database transaction handling for related operations

### UI Development
- Use unique class names for components to prevent style collisions
- Include required CSS/JS in correct order to ensure proper functioning
- Follow modal implementation standards across the application
- Maintain consistent naming conventions for all UI elements
- Implement responsive design patterns

### Security
- Implement proper session management with appropriate timeouts
- Use prepared statements for all database queries
- Validate and sanitize all user input
- Handle authentication securely with modern password hashing
- Implement CSRF protection for all forms
- Use HTTPS for all connections

## Recent Updates
### March 2025
- Enhanced error handling in Database classes
- Improved authentication flow
- Added connection testing capabilities
- Updated Docker configuration
- Implemented PSR-4 autoloading
- Added PostgreSQL 13 support

### Authentication Improvements
- Fixed prepared statement naming
- Enhanced session handling with improved security
- Improved password verification using argon2id
- Updated login flow with anti-brute force measures
- Added account lockout functionality

### Docker Integration
- Added proper configuration support with environment variables
- Updated database connection handling for container networking
- Improved environment variable usage
- Enhanced container networking
- Added volume persistence for development

## Troubleshooting
### Database Connections
- Check PostgreSQL server status and availability
- Verify credentials match environment configuration
- Test network access between application and database
- Review connection configuration for proper parameters
- Confirm database schema is properly initialized

### Authentication Issues
- Verify password hashing algorithm is consistent
- Check prepared statement parameter binding
- Review session management configuration
- Test connection parameters for authentication database
- Validate user input handling for login forms
- Validate all paths to css/js/php-includes and href links

## Getting Started
### Prerequisites
- PHP 8.1 or higher
- Composer
- Docker and Docker Compose
- PostgreSQL 13+
- Git

### Installation
1. Clone the repository
   ```bash
   git clone https://github.com/yourorganization/modular-system.git
   cd modular-system
   ```

2. Install dependencies
   ```bash
   composer install
   ```

3. Set up environment
   ```bash
   cp .env.example .env
   # Edit .env with your configuration
   ```

4. Start Docker environment
   ```bash
   docker-compose up -d
   ```

5. Initialize database
   ```bash
   php bin/console db:migrate
   php bin/console db:seed
   ```

### First Login
Access the system at http://localhost:8080 and use the default admin credentials:
- Username: `admin`
- Password: `ModularAdmin2025`
- Change this password immediately after first login

## Testing
The system uses PHPUnit for testing. Run tests with:

```bash
composer test
```

For individual test suites:
```bash
composer test:unit     # Unit tests only
composer test:feature  # Feature tests only
composer test:integration # Integration tests only
```

## Contributing
1. Follow the established coding standards (PSR-12)
   ```bash
   composer cs
   ```
2. Create feature branches from `develop`
3. Add appropriate tests for new functionality
4. Submit pull requests with comprehensive descriptions
5. Ensure CI checks pass before requesting review

## Documentation
Additional documentation is available in the `/docs` directory:
- [API Documentation](docs/api.md)
- [Database Schema](docs/database.md)
- [Development Guide](docs/development.md)
- [Deployment Guide](docs/deployment.md)


This README now accurately reflects:
1. The current DatabaseService implementation
2. PSR-4 compliant architecture
3. Updated directory structure
4. Modern authentication flow
5. Docker integration
6. Recent improvements and best practices

## Module Development

### Autoloading Module Classes
Currently, the Composer autoloader is only configured for the `App\` namespace. When developing module-specific controllers and classes, there are two options:

1. **Manual Inclusion** (Quick Solution):
   ```php
   // Manually include the controller files in your module API
   require_once __DIR__ . '/../Controllers/YourController.php';
   ```

2. **Update Composer Configuration** (Recommended):
   Add module namespaces to the composer.json file:
   ```json
   "autoload": {
       "psr-4": {
           "App\\": "src/",
           "Modules\\": "modules/"
       }
   }
   ```
   Then run `composer dump-autoload` to regenerate the autoloader.

### Module File Structure
```

### Database Access in Modules

When working with the database in module controllers:

1. **Use the Correct Database Connection**:
   - **MainDatabase**: For system-level operations (user management, configuration)
   - **ClientDatabase**: For customer/business data (employees, departments, etc.)
   
   ```php
   // For system operations (admin/configuration)
   $mainDb = DatabaseService::getMainDatabase();
   
   // For client/customer data operations
   $clientDb = DatabaseService::getClientDatabase($accountNumber, $userName);
   ```

2. **Session-based Database Selection**:
   When constructing controllers that access customer data:
   ```php
   public function __construct() {
       if (isset($_SESSION['account_number'])) {
           $accountNumber = $_SESSION['account_number'];
           $userName = $_SESSION['user_name'] ?? 'Guest';
           $this->db = DatabaseService::getClientDatabase($accountNumber, $userName);
       } else {
           throw new \Exception("No account number in session");
       }
   }
   ```

3. **Use Database Wrapper Methods**: 
   Instead of using PDO methods directly, use the wrapper methods provided by the Database class:

   ```php
   // INCORRECT - direct PDO methods
   $stmt = $this->db->prepare($query);
   $stmt->bindParam(':param', $value);
   $stmt->execute();
   $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
   
   // CORRECT - database wrapper methods
   $stmt = $this->db->executeQuery($query, [':param' => $value]);
   $results = $this->db->fetchAll($stmt);
   ```

### Session Management for Modules

Modules often need access to the current user's account information. To ensure proper database access:

1. **Always start a session** at the beginning of your API endpoints:
   ```php
   // Start a session if not already started
   if (session_status() === PHP_SESSION_NONE) {
       session_start();
   }
   ```

2. **Check for required session variables**:
   ```php
   if (!isset($_SESSION['account_number'])) {
       // Return an appropriate error or redirect to login
       http_response_code(401);
       echo json_encode([
           'success' => false,
           'message' => 'User not authenticated or missing account information'
       ]);
       exit;
   }
   ```

3. **Store important information in session upon login**:
   - `$_SESSION['account_number']` - Customer's account identifier
   - `$_SESSION['user_name']` - Logged in user's name
   - `$_SESSION['user_id']` - Unique user ID
   - `$_SESSION['tech_logged_in']` - Boolean for technician access

### Database Schema Compatibility

When working with database tables in modules:

1. **Verify actual database schema**: Always check the actual database schema before writing queries. 
   The schema may differ between development and production environments.

2. **Handle missing tables gracefully**: Add try/catch blocks around queries that might reference 
   tables that don't exist yet.

   ```php
   try {
       $result = $this->db->executeQuery("SELECT * FROM some_table");
       // Process result
   } catch (\Exception $e) {
       // Handle gracefully - provide empty data or default values
       $result = [];
       error_log("Table might not exist: " . $e->getMessage());
   }
   ```

3. **Database schema reference**:
   - The main database schema is defined in `Database/database_schema.sql`
   - Key employee tables:
     - `employees`: Contains basic employee information
     - `attendance_records`: Tracks time and attendance
     - `leave_balances`, `leave_requests`: Manage employee leave
   
   Be sure to check column names and table existence before writing queries.