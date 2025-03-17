# Hikvision Clock Server

A Node.js server for handling clock events from Hikvision access control devices.

## Features

- Processes clock events from Hikvision access control devices
- Handles multiple devices on different ports
- Stores clock events in PostgreSQL database
- Supports real-time event broadcasting via WebSockets
- Handles unknown employees by storing their clock events in a separate table
- Provides device status and heartbeat endpoints
- Supports ISUP key authentication
- Logs all raw data for debugging

## Setup

### Prerequisites

- Node.js 14+
- PostgreSQL 12+
- Hikvision access control devices

### Installation

1. Clone the repository
2. Install dependencies:
   ```
   npm install
   ```
3. Configure your PostgreSQL database using the provided SQL script
4. Start the server:
   ```
   node server.js
   ```

### Database Setup

The server requires two tables in your PostgreSQL database:

1. `attendance_records` - For storing clock events from known employees
2. `unknown_clockings` - For storing clock events from unknown employees (employees not found in the system)

Run the provided `combined_database_setup.sql` script to create these tables.

### Attendance Records Table Structure

The `attendance_records` table has the following structure:

```sql
CREATE TABLE IF NOT EXISTS public.attendance_records (
    attendance_id serial NOT NULL,
    employee_id integer NOT NULL,
    shift_id integer NOT NULL,
    date date NOT NULL,
    date_time timestamp without time zone NOT NULL,
    time_in timestamp without time zone,
    time_out timestamp without time zone,
    status character varying(20) NOT NULL DEFAULT 'Present',
    clock_number VARCHAR(50),
    device_id VARCHAR(50),
    verify_mode VARCHAR(50),
    verify_status VARCHAR(50),
    major_event_type INTEGER,
    minor_event_type INTEGER,
    notes text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT attendance_records_pkey PRIMARY KEY (attendance_id)
);
```

This table stores all clock events from known employees. The system automatically populates this table when a valid employee is found for a clock event.

### Unknown Clockings Table Structure

The `unknown_clockings` table has the following structure:

```sql
CREATE TABLE IF NOT EXISTS public.unknown_clockings (
    id SERIAL PRIMARY KEY,
    date DATE NOT NULL,
    date_time TIMESTAMP NOT NULL,
    clock_number VARCHAR(50) NOT NULL,
    device_id VARCHAR(50),
    verify_mode VARCHAR(50),
    verify_status VARCHAR(50),
    major_event_type INTEGER,
    minor_event_type INTEGER,
    raw_data TEXT,
    processed BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);
```

This table stores all clock events from employees who are not found in the system. Administrators can review these events and assign them to the correct employees using the admin interface.

## Configuration

The server uses the following environment variables:

- `ISUP_KEY` - The ISUP key for authentication (default: "MySecretKey123")

## API Endpoints

### Clock Event Endpoints

- `POST /clock` - Main endpoint for receiving clock events
- `POST /EventService` - Alternative endpoint for Hikvision devices
- `POST /ISAPI/Event/notification/alertStream` - Alternative endpoint for Hikvision devices
- `POST /ISAPI/AccessControl/AcsEvent` - Alternative endpoint for Hikvision devices

### Device Status Endpoints

- `GET /DeviceStatus` - Returns device status in XML format
- `POST /KeepAlive` - Heartbeat endpoint for devices

### Debugging Endpoints

- `GET /test` - Test endpoint to verify server is running
- `GET /raw-logs` - View the last 100 raw log entries

### Admin Endpoints

- `GET /unknown-clockings` - View the last 100 unknown clockings
- `POST /assign-clocking` - Assign an unknown clocking to an employee
  - Required parameters: `clockingId` and `employeeId`
  - Example request:
    ```json
    {
      "clockingId": 123,
      "employeeId": 456
    }
    ```

## Event Processing

When a clock event is received:

1. The server extracts the employee ID from the event data, checking these fields in order:
   - `employeeNoString` - The employee number from the device (preferred)
   - `verifyNo` - The verification number from the device
   - `cardNo` - The card number from the device
   - Other ID fields if available
2. It checks if the employee exists in the database by matching against:
   - `employee_number` field in the employees table (using employeeNoString)
   - `clock_number` field in the employees table (using verifyNo as fallback)
3. If the employee exists, the event is stored in the `attendance_records` table
4. If the employee does not exist, the event is stored in the `unknown_clockings` table
5. The event is broadcast to all connected WebSocket clients

## Handling Unknown Employees

When an employee is not found in the system:

1. The clock event is stored in the `unknown_clockings` table
2. The event includes all available information from the device
3. The `processed` flag is set to `false` by default
4. Administrators can review these events and manually assign them to employees

## Logging

The server logs all events to the following files:

- `error.log` - Error messages
- `clock_data.log` - Processed clock events
- `raw_clock_data.log` - Raw data received from devices
- `combined.log` - All logs combined

## WebSocket API

Connect to `/ws` to receive real-time clock events.

## Troubleshooting

If you encounter issues:

1. Check the log files for error messages
2. Verify the device is sending the correct data format
3. Ensure the database connection is working
4. Check that the employee's clock number exists in the database

## License

This project is licensed under the MIT License.

## Shift Management

The system uses a flexible shift management approach:

1. **Open Shift**: By default, all employees are assigned to an "Open Shift" (shift_id = 1) which has no fixed start or end times. This allows employees to clock in and out at any time without restrictions.

2. **Custom Shifts**: Administrators can create custom shifts with specific start and end times for more structured scheduling.

When a clock event is received, the system:
1. Checks if the employee exists
2. If the employee exists, assigns the clock event to the Open Shift (if no specific shift is assigned)
3. Records the actual clock time regardless of shift settings 