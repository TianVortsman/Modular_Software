-- Payroll Schema
CREATE SCHEMA IF NOT EXISTS payroll;

-- Leave Type Table
DROP TABLE IF EXISTS payroll.leave_type CASCADE;
CREATE TABLE payroll.leave_type (
    leave_type_id SERIAL PRIMARY KEY,
    leave_type VARCHAR(50) NOT NULL,
    leave_desc TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Leave Balance Table
DROP TABLE IF EXISTS payroll.leave_balance CASCADE;
CREATE TABLE payroll.leave_balance (
    balance_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    leave_type_id INTEGER REFERENCES payroll.leave_type(leave_type_id),
    balance_year INTEGER NOT NULL,
    total_days NUMERIC(5,2) NOT NULL,
    used_days NUMERIC(5,2) DEFAULT 0,
    remaining_days NUMERIC(5,2) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Leave Request Table
DROP TABLE IF EXISTS payroll.leave_request CASCADE;
CREATE TABLE payroll.leave_request (
    request_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    leave_type_id INTEGER REFERENCES payroll.leave_type(leave_type_id),
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    total_days NUMERIC(5,2) NOT NULL,
    request_status VARCHAR(20) DEFAULT 'Pending',
    request_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Leave Approval Table
DROP TABLE IF EXISTS payroll.leave_approval CASCADE;
CREATE TABLE payroll.leave_approval (
    approval_id SERIAL PRIMARY KEY,
    request_id INTEGER REFERENCES payroll.leave_request(request_id) ON DELETE CASCADE,
    approver_id INTEGER REFERENCES core.employees(employee_id),
    approval_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    approval_status VARCHAR(20) NOT NULL,
    approval_comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);
