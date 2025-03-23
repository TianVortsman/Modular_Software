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

### Database Service Implementation
The system uses a centralized DatabaseService class for managing connections:

```php
// Example usage of DatabaseService
use Services\DatabaseService;

// Get main database connectio
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
Main Tables
Customers: Company information and account management
Users: User accounts and authentication data
Account Numbers: Account number mapping and clock server configuration
Technicians: Admin/technician account management
Key Relationships
Users -> Customers (Many-to-One)
Account Numbers -> Users (Many-to-One)
Setup and Configuration
Docker Environment
yaml
Copy code
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
json
Copy code
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
            "Core\\": "src/Core/",
            "Controllers\\": "src/Controllers/",
            "Models\\": "src/Models/",
            "Services\\": "src/Services/",
            "Config\\": "src/Config/"
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
Best Practices
Code Organization
Follow PSR-4 autoloading standards
Use namespaced classes
Implement proper error handling with custom exception classes
Maintain consistent directory structure
Database Operations
Use DatabaseService for all database connections
Implement prepared statements to prevent SQL injection
Handle connection errors gracefully with appropriate fallbacks
Use appropriate PostgreSQL syntax and features
Implement database transaction handling for related operations
UI Development
Use unique class names for components to prevent style collisions
Include required CSS/JS in correct order to ensure proper functioning
Follow modal implementation standards across the application
Maintain consistent naming conventions for all UI elements
Implement responsive design patterns
Security
Implement proper session management with appropriate timeouts
Use prepared statements for all database queries
Validate and sanitize all user input
Handle authentication securely with modern password hashing
Implement CSRF protection for all forms
Use HTTPS for all connections
Recent Updates
March 2025
Enhanced error handling in Database classes
Improved authentication flow
Added connection testing capabilities
Updated Docker configuration
Implemented PSR-4 autoloading
Added PostgreSQL 13 support
Authentication Improvements
Fixed prepared statement naming
Enhanced session handling with improved security
Improved password verification using argon2id
Updated login flow with anti-brute force measures
Added account lockout functionality
Docker Integration
Added proper configuration support with environment variables
Updated database connection handling for container networking
Improved environment variable usage
Enhanced container networking
Added volume persistence for development
Troubleshooting
Database Connections
Check PostgreSQL server status and availability
Verify credentials match environment configuration
Test network access between application and database
Review connection configuration for proper parameters
Confirm database schema is properly initialized
Authentication Issues
Verify password hashing algorithm is consistent
Check prepared statement parameter binding
Review session management configuration
Test connection parameters for authentication database
Validate user input handling for login forms
Validate all paths to css/js/php-includes and href links
Getting Started
Prerequisites
PHP 8.1 or higher
Composer
Docker and Docker Compose
PostgreSQL 13+


Git


Installation
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

First Login
Access the system at http://localhost:8080 and use the default admin credentials:
- Username: `admin`
- Password: `ModularAdmin2025`
- Change this password immediately after first login

Testing
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

Contributing
1. Follow the established coding standards (PSR-12)
   ```bash
   composer cs
   ```
2. Create feature branches from `develop`
3. Add appropriate tests for new functionality
4. Submit pull requests with comprehensive descriptions
5. Ensure CI checks pass before requesting review

Documentation
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