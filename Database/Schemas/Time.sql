-- Time Schema
CREATE SCHEMA IF NOT EXISTS time;

-- Base Tables (No Dependencies)
CREATE TABLE IF NOT EXISTS time.time_shift ();
ALTER TABLE time.time_shift ADD COLUMN IF NOT EXISTS shift_id SERIAL PRIMARY KEY;
ALTER TABLE time.time_shift ADD COLUMN IF NOT EXISTS shift_name VARCHAR(50) NOT NULL;
ALTER TABLE time.time_shift ADD COLUMN IF NOT EXISTS shift_start_time TIME NOT NULL;
ALTER TABLE time.time_shift ADD COLUMN IF NOT EXISTS shift_end_time TIME NOT NULL;
ALTER TABLE time.time_shift ADD COLUMN IF NOT EXISTS shift_break_start TIME;
ALTER TABLE time.time_shift ADD COLUMN IF NOT EXISTS shift_break_end TIME;
ALTER TABLE time.time_shift ADD COLUMN IF NOT EXISTS shift_status VARCHAR(20) DEFAULT 'Active';
ALTER TABLE time.time_shift ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.time_shift ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.time_shift ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

-- Holiday Management (No Dependencies)
CREATE TABLE IF NOT EXISTS time.holidays ();
ALTER TABLE time.holidays ADD COLUMN IF NOT EXISTS holiday_id SERIAL PRIMARY KEY;
ALTER TABLE time.holidays ADD COLUMN IF NOT EXISTS holiday_name VARCHAR(100) NOT NULL;
ALTER TABLE time.holidays ADD COLUMN IF NOT EXISTS date DATE NOT NULL;
ALTER TABLE time.holidays ADD COLUMN IF NOT EXISTS description TEXT;
ALTER TABLE time.holidays ADD COLUMN IF NOT EXISTS is_paid BOOLEAN NOT NULL DEFAULT true;
ALTER TABLE time.holidays ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.holidays ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.holidays ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

-- Employee Dependent Tables
CREATE TABLE IF NOT EXISTS time.time_clocking ();
ALTER TABLE time.time_clocking ADD COLUMN IF NOT EXISTS clocking_id SERIAL PRIMARY KEY;
ALTER TABLE time.time_clocking ADD COLUMN IF NOT EXISTS employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE;
ALTER TABLE time.time_clocking ADD COLUMN IF NOT EXISTS clocking_date DATE NOT NULL;
ALTER TABLE time.time_clocking ADD COLUMN IF NOT EXISTS clocking_time TIME NOT NULL;
ALTER TABLE time.time_clocking ADD COLUMN IF NOT EXISTS clocking_type VARCHAR(20) NOT NULL;
ALTER TABLE time.time_clocking ADD COLUMN IF NOT EXISTS clocking_method VARCHAR(20) NOT NULL;
ALTER TABLE time.time_clocking ADD COLUMN IF NOT EXISTS clocking_status VARCHAR(20) DEFAULT 'Valid';
ALTER TABLE time.time_clocking ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.time_clocking ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.time_clocking ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

CREATE TABLE IF NOT EXISTS time.time_roster ();
ALTER TABLE time.time_roster ADD COLUMN IF NOT EXISTS roster_id SERIAL PRIMARY KEY;
ALTER TABLE time.time_roster ADD COLUMN IF NOT EXISTS employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE;
ALTER TABLE time.time_roster ADD COLUMN IF NOT EXISTS shift_id INTEGER REFERENCES time.time_shift(shift_id);
ALTER TABLE time.time_roster ADD COLUMN IF NOT EXISTS roster_date DATE NOT NULL;
ALTER TABLE time.time_roster ADD COLUMN IF NOT EXISTS roster_status VARCHAR(20) DEFAULT 'Scheduled';
ALTER TABLE time.time_roster ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.time_roster ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.time_roster ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

CREATE TABLE IF NOT EXISTS time.time_overtime ();
ALTER TABLE time.time_overtime ADD COLUMN IF NOT EXISTS overtime_id SERIAL PRIMARY KEY;
ALTER TABLE time.time_overtime ADD COLUMN IF NOT EXISTS employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE;
ALTER TABLE time.time_overtime ADD COLUMN IF NOT EXISTS overtime_date DATE NOT NULL;
ALTER TABLE time.time_overtime ADD COLUMN IF NOT EXISTS overtime_hours NUMERIC(5,2) NOT NULL;
ALTER TABLE time.time_overtime ADD COLUMN IF NOT EXISTS overtime_type VARCHAR(20) NOT NULL;
ALTER TABLE time.time_overtime ADD COLUMN IF NOT EXISTS overtime_status VARCHAR(20) DEFAULT 'Pending';
ALTER TABLE time.time_overtime ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.time_overtime ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.time_overtime ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

CREATE TABLE IF NOT EXISTS time.time_attendance ();
ALTER TABLE time.time_attendance ADD COLUMN IF NOT EXISTS attendance_id SERIAL PRIMARY KEY;
ALTER TABLE time.time_attendance ADD COLUMN IF NOT EXISTS employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE;
ALTER TABLE time.time_attendance ADD COLUMN IF NOT EXISTS attendance_date DATE NOT NULL;
ALTER TABLE time.time_attendance ADD COLUMN IF NOT EXISTS time_in TIME;
ALTER TABLE time.time_attendance ADD COLUMN IF NOT EXISTS time_out TIME;
ALTER TABLE time.time_attendance ADD COLUMN IF NOT EXISTS total_hours NUMERIC(5,2);
ALTER TABLE time.time_attendance ADD COLUMN IF NOT EXISTS attendance_status VARCHAR(20) DEFAULT 'Present';
ALTER TABLE time.time_attendance ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.time_attendance ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.time_attendance ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

CREATE TABLE IF NOT EXISTS time.time_break ();
ALTER TABLE time.time_break ADD COLUMN IF NOT EXISTS break_id SERIAL PRIMARY KEY;
ALTER TABLE time.time_break ADD COLUMN IF NOT EXISTS employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE;
ALTER TABLE time.time_break ADD COLUMN IF NOT EXISTS break_date DATE NOT NULL;
ALTER TABLE time.time_break ADD COLUMN IF NOT EXISTS break_start TIME NOT NULL;
ALTER TABLE time.time_break ADD COLUMN IF NOT EXISTS break_end TIME;
ALTER TABLE time.time_break ADD COLUMN IF NOT EXISTS break_type VARCHAR(20) NOT NULL;
ALTER TABLE time.time_break ADD COLUMN IF NOT EXISTS break_status VARCHAR(20) DEFAULT 'Active';
ALTER TABLE time.time_break ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.time_break ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.time_break ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

CREATE TABLE IF NOT EXISTS time.weekly_hours ();
ALTER TABLE time.weekly_hours ADD COLUMN IF NOT EXISTS hours_id SERIAL PRIMARY KEY;
ALTER TABLE time.weekly_hours ADD COLUMN IF NOT EXISTS employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE;
ALTER TABLE time.weekly_hours ADD COLUMN IF NOT EXISTS week_start_date DATE NOT NULL;
ALTER TABLE time.weekly_hours ADD COLUMN IF NOT EXISTS regular_hours NUMERIC(5,2) NOT NULL DEFAULT 0;
ALTER TABLE time.weekly_hours ADD COLUMN IF NOT EXISTS overtime_hours NUMERIC(5,2) NOT NULL DEFAULT 0;
ALTER TABLE time.weekly_hours ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.weekly_hours ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.weekly_hours ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

CREATE TABLE IF NOT EXISTS time.monthly_rosters ();
ALTER TABLE time.monthly_rosters ADD COLUMN IF NOT EXISTS roster_id SERIAL PRIMARY KEY;
ALTER TABLE time.monthly_rosters ADD COLUMN IF NOT EXISTS employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE;
ALTER TABLE time.monthly_rosters ADD COLUMN IF NOT EXISTS month INTEGER NOT NULL;
ALTER TABLE time.monthly_rosters ADD COLUMN IF NOT EXISTS year INTEGER NOT NULL;
ALTER TABLE time.monthly_rosters ADD COLUMN IF NOT EXISTS created_by INTEGER REFERENCES core.employees(employee_id);
ALTER TABLE time.monthly_rosters ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'Draft';
ALTER TABLE time.monthly_rosters ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.monthly_rosters ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE time.monthly_rosters ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

-- System Tables
CREATE TABLE IF NOT EXISTS time.unknown_clockings ();
ALTER TABLE time.unknown_clockings ADD COLUMN IF NOT EXISTS id SERIAL PRIMARY KEY;
ALTER TABLE time.unknown_clockings ADD COLUMN IF NOT EXISTS date DATE NOT NULL;
ALTER TABLE time.unknown_clockings ADD COLUMN IF NOT EXISTS date_time TIMESTAMP NOT NULL;
ALTER TABLE time.unknown_clockings ADD COLUMN IF NOT EXISTS clock_number VARCHAR(50) NOT NULL;
ALTER TABLE time.unknown_clockings ADD COLUMN IF NOT EXISTS device_id VARCHAR(50);
ALTER TABLE time.unknown_clockings ADD COLUMN IF NOT EXISTS verify_mode VARCHAR(50);
ALTER TABLE time.unknown_clockings ADD COLUMN IF NOT EXISTS verify_status VARCHAR(50);
ALTER TABLE time.unknown_clockings ADD COLUMN IF NOT EXISTS major_event_type INTEGER;
ALTER TABLE time.unknown_clockings ADD COLUMN IF NOT EXISTS minor_event_type INTEGER;
ALTER TABLE time.unknown_clockings ADD COLUMN IF NOT EXISTS raw_data TEXT;
ALTER TABLE time.unknown_clockings ADD COLUMN IF NOT EXISTS processed BOOLEAN DEFAULT false;
ALTER TABLE time.unknown_clockings ADD COLUMN IF NOT EXISTS notes TEXT;
ALTER TABLE time.unknown_clockings ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;

-- Create indexes
CREATE INDEX IF NOT EXISTS idx_weekly_hours_employee ON time.weekly_hours(employee_id);
CREATE INDEX IF NOT EXISTS idx_roster_employee ON time.monthly_rosters(employee_id);
CREATE INDEX IF NOT EXISTS idx_roster_creator ON time.monthly_rosters(created_by);