-- Payroll Schema
CREATE SCHEMA IF NOT EXISTS payroll;

-- Base Tables (No Dependencies)
CREATE TABLE IF NOT EXISTS payroll.leave_type ();
ALTER TABLE payroll.leave_type ADD COLUMN IF NOT EXISTS leave_type_id SERIAL PRIMARY KEY;
ALTER TABLE payroll.leave_type ADD COLUMN IF NOT EXISTS leave_type VARCHAR(50) NOT NULL;
ALTER TABLE payroll.leave_type ADD COLUMN IF NOT EXISTS leave_desc TEXT;
ALTER TABLE payroll.leave_type ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE payroll.leave_type ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE payroll.leave_type ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

-- Employee Dependent Tables
CREATE TABLE IF NOT EXISTS payroll.leave_balance ();
ALTER TABLE payroll.leave_balance ADD COLUMN IF NOT EXISTS balance_id SERIAL PRIMARY KEY;
ALTER TABLE payroll.leave_balance ADD COLUMN IF NOT EXISTS employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE;
ALTER TABLE payroll.leave_balance ADD COLUMN IF NOT EXISTS leave_type_id INTEGER REFERENCES payroll.leave_type(leave_type_id);
ALTER TABLE payroll.leave_balance ADD COLUMN IF NOT EXISTS balance_year INTEGER NOT NULL;
ALTER TABLE payroll.leave_balance ADD COLUMN IF NOT EXISTS total_days NUMERIC(5,2) NOT NULL;
ALTER TABLE payroll.leave_balance ADD COLUMN IF NOT EXISTS used_days NUMERIC(5,2) DEFAULT 0;
ALTER TABLE payroll.leave_balance ADD COLUMN IF NOT EXISTS remaining_days NUMERIC(5,2) NOT NULL;
ALTER TABLE payroll.leave_balance ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE payroll.leave_balance ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE payroll.leave_balance ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

CREATE TABLE IF NOT EXISTS payroll.leave_request ();
ALTER TABLE payroll.leave_request ADD COLUMN IF NOT EXISTS request_id SERIAL PRIMARY KEY;
ALTER TABLE payroll.leave_request ADD COLUMN IF NOT EXISTS employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE;
ALTER TABLE payroll.leave_request ADD COLUMN IF NOT EXISTS leave_type_id INTEGER REFERENCES payroll.leave_type(leave_type_id);
ALTER TABLE payroll.leave_request ADD COLUMN IF NOT EXISTS start_date DATE NOT NULL;
ALTER TABLE payroll.leave_request ADD COLUMN IF NOT EXISTS end_date DATE NOT NULL;
ALTER TABLE payroll.leave_request ADD COLUMN IF NOT EXISTS total_days NUMERIC(5,2) NOT NULL;
ALTER TABLE payroll.leave_request ADD COLUMN IF NOT EXISTS request_status VARCHAR(20) DEFAULT 'Pending';
ALTER TABLE payroll.leave_request ADD COLUMN IF NOT EXISTS request_reason TEXT;
ALTER TABLE payroll.leave_request ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE payroll.leave_request ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE payroll.leave_request ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

CREATE TABLE IF NOT EXISTS payroll.leave_approval ();
ALTER TABLE payroll.leave_approval ADD COLUMN IF NOT EXISTS approval_id SERIAL PRIMARY KEY;
ALTER TABLE payroll.leave_approval ADD COLUMN IF NOT EXISTS request_id INTEGER REFERENCES payroll.leave_request(request_id) ON DELETE CASCADE;
ALTER TABLE payroll.leave_approval ADD COLUMN IF NOT EXISTS approver_id INTEGER REFERENCES core.employees(employee_id);
ALTER TABLE payroll.leave_approval ADD COLUMN IF NOT EXISTS approval_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE payroll.leave_approval ADD COLUMN IF NOT EXISTS approval_status VARCHAR(20) NOT NULL;
ALTER TABLE payroll.leave_approval ADD COLUMN IF NOT EXISTS approval_comment TEXT;
ALTER TABLE payroll.leave_approval ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE payroll.leave_approval ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE payroll.leave_approval ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;
