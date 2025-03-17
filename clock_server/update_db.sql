-- Update attendance_records table
IF EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('attendance_records') AND name = 'device_serial_no')
BEGIN
    ALTER TABLE attendance_records DROP COLUMN device_serial_no;
END

-- Update unknown_clockings table
IF EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('unknown_clockings') AND name = 'device_serial_no')
BEGIN
    ALTER TABLE unknown_clockings DROP COLUMN device_serial_no;
END

-- Create index on device_id for faster lookups
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'idx_attendance_device_id' AND object_id = OBJECT_ID('attendance_records'))
BEGIN
    CREATE INDEX idx_attendance_device_id ON attendance_records(device_id);
END

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'idx_unknown_device_id' AND object_id = OBJECT_ID('unknown_clockings'))
BEGIN
    CREATE INDEX idx_unknown_device_id ON unknown_clockings(device_id);
END

-- Create index on clock_number for faster lookups
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'idx_attendance_clock_number' AND object_id = OBJECT_ID('attendance_records'))
BEGIN
    CREATE INDEX idx_attendance_clock_number ON attendance_records(clock_number);
END

IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'idx_unknown_clock_number' AND object_id = OBJECT_ID('unknown_clockings'))
BEGIN
    CREATE INDEX idx_unknown_clock_number ON unknown_clockings(clock_number);
END

-- Create index on employee_number for faster lookups
IF NOT EXISTS (SELECT 1 FROM sys.indexes WHERE name = 'idx_employees_employee_number' AND object_id = OBJECT_ID('employees'))
BEGIN
    CREATE INDEX idx_employees_employee_number ON employees(employee_number);
END

-- Update the employees table to ensure clock_number is a string
IF EXISTS (SELECT 1 FROM sys.columns WHERE object_id = OBJECT_ID('employees') AND name = 'clock_number')
BEGIN
    ALTER TABLE employees ALTER COLUMN clock_number VARCHAR(50);
END

-- Add a note about the changes
COMMENT ON TABLE unknown_clockings IS 'Stores clock events from unknown employees';
COMMENT ON TABLE attendance_records IS 'Stores clock events from known employees';

-- Create a default shift if it doesn't exist
IF NOT EXISTS (SELECT 1 FROM shifts WHERE shift_id = 1)
BEGIN
    INSERT INTO shifts (shift_id, shift_name, start_time, end_time)
    VALUES (1, 'Default Shift', '08:00:00', '17:00:00');
END 