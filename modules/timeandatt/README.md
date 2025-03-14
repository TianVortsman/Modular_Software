# Schedule Management System

A comprehensive schedule management system that allows organizations to create and manage work schedules, templates, and monthly rosters.

## Features

- Create and manage shift templates
- Drag-and-drop interface for schedule creation
- Monthly roster view
- Employee schedule assignment
- Shift library management
- Mobile-responsive design
- Real-time updates
- Export and print capabilities

## Database Schema

The system uses the following tables:

- `schedule_templates`: Stores reusable schedule templates
- `shifts`: Stores individual shift definitions
- `employee_schedules`: Links employees to their assigned schedules
- `monthly_rosters`: Stores monthly roster data

## API Endpoints

### Templates API (`/api/templates.php`)
- GET: Retrieve all templates for an account
- POST: Create or update a template
- DELETE: Delete a template

### Shifts API (`/api/shifts.php`)
- GET: Retrieve all shifts for an account
- POST: Create or update a shift
- DELETE: Delete a shift

### Employees API (`/api/employees.php`)
- GET: Retrieve all active employees for an account

### Assign Template API (`/api/assign-template.php`)
- POST: Assign a template to selected employees

## Security Features

- CSRF protection
- Input validation
- SQL injection prevention
- Session-based authentication
- Account-based access control

## Dependencies

- PHP 7.4 or higher
- MySQL 5.7 or higher
- Modern web browser with JavaScript enabled

## Installation

1. Import the database schema:
   ```sql
   mysql -u your_username -p your_database < sql/schedules.sql
   ```

2. Configure your database connection in `php/db.php`

3. Ensure proper file permissions are set:
   ```bash
   chmod 755 -R modules/timeandatt/
   chmod 644 modules/timeandatt/*.php
   ```

## Usage

1. Access the schedule management system through your dashboard
2. Create shifts in the shift library
3. Create templates using the drag-and-drop interface
4. Assign templates to employees
5. View and manage monthly rosters

## Contributing

1. Fork the repository
2. Create a feature branch
3. Commit your changes
4. Push to the branch
5. Create a Pull Request

## License

This project is licensed under the MIT License - see the LICENSE file for details. 