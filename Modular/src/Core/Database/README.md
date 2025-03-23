# Database Classes

This directory contains the database connection and query classes for the Modular application.

## Recent Improvements (March 2025)

### Error Handling Enhancements

- Added graceful connection failure handling in Database classes
- Suppressed PHP warnings in pg_connect to prevent fatal errors
- Implemented fallback host connection attempts
- Added connection timeout and SSL preferences
- Added comprehensive connection testing

### Authentication Improvements

- Fixed prepared statement naming conflicts by using unique identifiers with timestamps
- Added proper session handling to prevent errors when a session is already started
- Fixed password verification for both technician and user logins
- Improved session variable management to prevent conflicts between user and technician logins

### Diagnostic Tools

- Added testConnection() method to all Database classes
- Created db_test.php script for diagnosing connection issues
- Added login_test.php script for testing authentication

## Connection Troubleshooting

If database connections are failing:

1. Check PostgreSQL server status:
   ```
   pg_isready -h localhost -p 5432
   ```

2. Verify credentials:
   ```
   psql -h localhost -p 5432 -U Tian -d modular_system
   ```

3. Check network access:
   - Ensure PostgreSQL is listening on proper interfaces
   - Check firewall settings
   - Try both 'localhost' and '127.0.0.1' as host

4. Connection Configuration:
   - See Config/Database.php for current settings
   - Try adjusting connect_timeout if server is slow to respond

## Authentication Troubleshooting

If authentication is failing:

1. Verify password hashing:
   ```php
   // Check if hash is valid bcrypt format (should start with $2y$)
   $isValid = str_starts_with($hash, '$2y$');
   
   // Test password verification
   $verified = password_verify('actual_password', $hash);
   ```

2. Check prepared statement conflicts:
   - Use unique identifiers for prepared statement names 
   - Add timestamp or random values to statement names

3. Session issues:
   - Check if session is already started before calling session_start()
   - Clear conflicting session variables
   - Verify session cookie settings

## Best Practices

1. Always check query results:
   ```php
   $result = $db->executeQuery(...);
   if ($result === false) {
       // Handle error
   }
   ```

2. Test connections before critical operations:
   ```php
   if (!$db->testConnection()) {
       // Handle connection issue
   }
   ```

3. Log database errors:
   ```php
   error_log("DB Error: " . $db->getLastError());
   ```

4. Use unique prepared statement names:
   ```php
   $stmtName = "query_" . time() . rand(1000, 9999);
   $result = $db->executeQuery($stmtName, $sql, $params);
   ``` 