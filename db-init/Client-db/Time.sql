-- Time Schema
CREATE SCHEMA IF NOT EXISTS time;

-- Time Shift Table
DROP TABLE IF EXISTS time.time_shift CASCADE;
CREATE TABLE time.time_shift (
    shift_id SERIAL PRIMARY KEY,
    shift_name VARCHAR(50) NOT NULL,
    shift_start_time TIME NOT NULL,
    shift_end_time TIME NOT NULL,
    shift_break_start TIME,
    shift_break_end TIME,
    shift_status VARCHAR(20) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Holidays Table
DROP TABLE IF EXISTS time.holidays CASCADE;
CREATE TABLE time.holidays (
    holiday_id SERIAL PRIMARY KEY,
    holiday_name VARCHAR(100) NOT NULL,
    date DATE NOT NULL,
    description TEXT,
    is_paid BOOLEAN NOT NULL DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Time Clocking Table
DROP TABLE IF EXISTS time.time_clocking CASCADE;
CREATE TABLE time.time_clocking (
    clocking_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    clocking_date DATE NOT NULL,
    clocking_time TIME NOT NULL,
    clocking_type VARCHAR(20) NOT NULL,
    clocking_method VARCHAR(20) NOT NULL,
    clocking_status VARCHAR(20) DEFAULT 'Valid',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Time Roster Table
DROP TABLE IF EXISTS time.time_roster CASCADE;
CREATE TABLE time.time_roster (
    roster_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    shift_id INTEGER REFERENCES time.time_shift(shift_id),
    roster_date DATE NOT NULL,
    roster_status VARCHAR(20) DEFAULT 'Scheduled',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Time Overtime Table
DROP TABLE IF EXISTS time.time_overtime CASCADE;
CREATE TABLE time.time_overtime (
    overtime_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    overtime_date DATE NOT NULL,
    overtime_hours NUMERIC(5,2) NOT NULL,
    overtime_type VARCHAR(20) NOT NULL,
    overtime_status VARCHAR(20) DEFAULT 'Pending',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Time Attendance Table
DROP TABLE IF EXISTS time.time_attendance CASCADE;
CREATE TABLE time.time_attendance (
    attendance_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    attendance_date DATE NOT NULL,
    time_in TIME,
    time_out TIME,
    total_hours NUMERIC(5,2),
    attendance_status VARCHAR(20) DEFAULT 'Present',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Time Break Table
DROP TABLE IF EXISTS time.time_break CASCADE;
CREATE TABLE time.time_break (
    break_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    break_date DATE NOT NULL,
    break_start TIME NOT NULL,
    break_end TIME,
    break_type VARCHAR(20) NOT NULL,
    break_status VARCHAR(20) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Weekly Hours Table
DROP TABLE IF EXISTS time.weekly_hours CASCADE;
CREATE TABLE time.weekly_hours (
    hours_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    week_start_date DATE NOT NULL,
    regular_hours NUMERIC(5,2) NOT NULL DEFAULT 0,
    overtime_hours NUMERIC(5,2) NOT NULL DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Monthly Rosters Table
DROP TABLE IF EXISTS time.monthly_rosters CASCADE;
CREATE TABLE time.monthly_rosters (
    roster_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    month INTEGER NOT NULL,
    year INTEGER NOT NULL,
    created_by INTEGER REFERENCES core.employees(employee_id),
    status VARCHAR(20) DEFAULT 'Draft',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Unknown Clockings Table
DROP TABLE IF EXISTS time.unknown_clockings CASCADE;
CREATE TABLE time.unknown_clockings (
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
    processed BOOLEAN DEFAULT false,
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_weekly_hours_employee ON time.weekly_hours(employee_id);
CREATE INDEX IF NOT EXISTS idx_roster_employee ON time.monthly_rosters(employee_id);
CREATE INDEX IF NOT EXISTS idx_roster_creator ON time.monthly_rosters(created_by);