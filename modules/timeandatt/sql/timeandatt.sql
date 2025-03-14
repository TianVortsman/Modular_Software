-- Time and Attendance Module Database Structure
-- Version: 2.0

-- Enable necessary extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

-- Core Tables

-- Positions Table
CREATE TABLE IF NOT EXISTS positions (
    position_id SERIAL PRIMARY KEY,
    title VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE
);

-- Employees Table
CREATE TABLE IF NOT EXISTS employees (
    employee_id SERIAL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    phone_number VARCHAR(20),
    hire_date DATE NOT NULL,
    division VARCHAR(100),
    group_name VARCHAR(100),
    department VARCHAR(100),
    cost_center VARCHAR(100),
    position_id INTEGER REFERENCES positions(position_id) ON DELETE SET NULL,
    employee_number VARCHAR(50) UNIQUE NOT NULL,
    status VARCHAR(20) DEFAULT 'active' CHECK (status IN ('active', 'inactive', 'terminated')),
    employment_type VARCHAR(20) DEFAULT 'Permanent' CHECK (employment_type IN ('Permanent', 'Contract')),
    work_schedule_type VARCHAR(20) DEFAULT 'Open' CHECK (work_schedule_type IN ('Open', 'Fixed', 'Rotating')),
    biometric_id VARCHAR(100) UNIQUE,
    emergency_contact_name VARCHAR(100),
    emergency_contact_phone VARCHAR(20),
    address TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE
);

-- Attendance Management

-- Shifts Table
CREATE TABLE IF NOT EXISTS shifts (
    shift_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    break_time INTEGER DEFAULT 30,
    color VARCHAR(7) DEFAULT '#007bff',
    is_default BOOLEAN DEFAULT FALSE,
    tolerance_minutes INTEGER DEFAULT 15,
    description TEXT,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE
);

-- Attendance Records Table
CREATE TABLE IF NOT EXISTS attendance_records (
    record_id SERIAL PRIMARY KEY,
    employee_id INTEGER NOT NULL,
    date DATE NOT NULL,
    shift_id INTEGER NOT NULL,
    check_in TIMESTAMP WITH TIME ZONE,
    check_out TIMESTAMP WITH TIME ZONE,
    status VARCHAR(20) DEFAULT 'present',
    late_minutes INTEGER DEFAULT 0,
    early_departure_minutes INTEGER DEFAULT 0,
    overtime_minutes INTEGER DEFAULT 0,
    break_duration INTEGER DEFAULT 0,
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (shift_id) REFERENCES shifts(shift_id)
);

-- Access Control Records Table
CREATE TABLE IF NOT EXISTS access_control_records (
    record_id SERIAL PRIMARY KEY,
    employee_id INTEGER NOT NULL,
    access_time TIMESTAMP WITH TIME ZONE NOT NULL,
    access_type VARCHAR(20) NOT NULL,
    access_point VARCHAR(100),
    device_id VARCHAR(100),
    ip_address VARCHAR(45),
    location_data JSONB,
    notes TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id)
);

-- Leave Management

-- Leave Types Table
CREATE TABLE IF NOT EXISTS leave_types (
    leave_type_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    description TEXT,
    paid BOOLEAN DEFAULT true,
    color VARCHAR(7) DEFAULT '#007bff',
    max_days_per_year INTEGER,
    requires_approval BOOLEAN DEFAULT true,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE
);

-- Leave Requests Table
CREATE TABLE IF NOT EXISTS leave_requests (
    request_id SERIAL PRIMARY KEY,
    employee_id INTEGER NOT NULL,
    leave_type_id INTEGER NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'pending',
    reason TEXT,
    approved_by INTEGER,
    approval_date TIMESTAMP WITH TIME ZONE,
    rejection_reason TEXT,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE,
    escalated_to INTEGER REFERENCES employees(employee_id),
    escalation_date TIMESTAMP WITH TIME ZONE,
    approval_level INTEGER DEFAULT 1,
    approval_chain JSONB,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(leave_type_id),
    FOREIGN KEY (approved_by) REFERENCES employees(employee_id)
);

-- Leave Balances Table
CREATE TABLE IF NOT EXISTS leave_balances (
    balance_id SERIAL PRIMARY KEY,
    employee_id INTEGER NOT NULL,
    leave_type_id INTEGER NOT NULL,
    year INTEGER NOT NULL,
    total_days INTEGER NOT NULL,
    used_days INTEGER DEFAULT 0,
    pending_days INTEGER DEFAULT 0,
    carry_over_days INTEGER DEFAULT 0,
    carry_over_expiry_date DATE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (leave_type_id) REFERENCES leave_types(leave_type_id),
    UNIQUE(employee_id, leave_type_id, year)
);

-- Schedule Management

-- Schedule Templates Table
CREATE TABLE IF NOT EXISTS schedule_templates (
    template_id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    shifts JSONB NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE
);

-- Employee Schedules Table
CREATE TABLE IF NOT EXISTS employee_schedules (
    schedule_id SERIAL PRIMARY KEY,
    employee_id INTEGER NOT NULL,
    template_id INTEGER NOT NULL,
    start_date DATE NOT NULL,
    end_date DATE,
    shifts JSONB NOT NULL,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (template_id) REFERENCES schedule_templates(template_id)
);

-- Monthly Rosters Table
CREATE TABLE IF NOT EXISTS monthly_rosters (
    roster_id SERIAL PRIMARY KEY,
    month DATE NOT NULL,
    shifts JSONB NOT NULL,
    status VARCHAR(20) DEFAULT 'draft',
    published_by INTEGER,
    published_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE,
    FOREIGN KEY (published_by) REFERENCES employees(employee_id)
);

-- Overtime Management

-- Overtime Categories Table
CREATE TABLE IF NOT EXISTS overtime_categories (
    category_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    multiplier DECIMAL(4,2) NOT NULL,
    description TEXT,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE
);

-- Overtime Requests Table
CREATE TABLE IF NOT EXISTS overtime_requests (
    request_id SERIAL PRIMARY KEY,
    employee_id INTEGER NOT NULL,
    category_id INTEGER NOT NULL,
    date DATE NOT NULL,
    start_time TIME NOT NULL,
    end_time TIME NOT NULL,
    reason TEXT,
    status VARCHAR(20) DEFAULT 'pending',
    approved_by INTEGER,
    approval_date TIMESTAMP WITH TIME ZONE,
    approved_overtime_minutes INTEGER,
    approval_level INTEGER DEFAULT 1,
    approval_chain JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    FOREIGN KEY (category_id) REFERENCES overtime_categories(category_id),
    FOREIGN KEY (approved_by) REFERENCES employees(employee_id)
);

-- Break Management

-- Break Types Table
CREATE TABLE IF NOT EXISTS break_types (
    break_type_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    code VARCHAR(20) UNIQUE NOT NULL,
    duration INTEGER NOT NULL,
    paid BOOLEAN DEFAULT true,
    description TEXT,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE
);

-- Break Records Table
CREATE TABLE IF NOT EXISTS break_records (
    record_id SERIAL PRIMARY KEY,
    attendance_id INTEGER NOT NULL,
    break_type_id INTEGER NOT NULL,
    start_time TIMESTAMP WITH TIME ZONE NOT NULL,
    end_time TIMESTAMP WITH TIME ZONE,
    duration INTEGER,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE,
    FOREIGN KEY (attendance_id) REFERENCES attendance_records(record_id),
    FOREIGN KEY (break_type_id) REFERENCES break_types(break_type_id)
);

-- Holiday Management

-- Holidays Table
CREATE TABLE IF NOT EXISTS holidays (
    holiday_id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    date DATE NOT NULL,
    type VARCHAR(50) DEFAULT 'regular',
    description TEXT,
    is_paid BOOLEAN DEFAULT true,
    status VARCHAR(20) DEFAULT 'active',
    holiday_pay_multiplier DECIMAL(4,2) DEFAULT 1.0,
    is_recurring BOOLEAN DEFAULT false,
    recurrence_rule JSONB,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE
);

-- Pay Periods Table
CREATE TABLE IF NOT EXISTS pay_periods (
    period_id SERIAL PRIMARY KEY,
    start_date DATE NOT NULL,
    end_date DATE NOT NULL,
    pay_date DATE NOT NULL,
    status VARCHAR(20) DEFAULT 'open',
    processed_by INTEGER,
    processed_at TIMESTAMP WITH TIME ZONE,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE,
    FOREIGN KEY (processed_by) REFERENCES employees(employee_id)
);

-- Rounding Rules Table
CREATE TABLE IF NOT EXISTS rounding_rules (
    rule_id SERIAL PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    rounding_type VARCHAR(20) NOT NULL,
    before_rounding_minutes INTEGER NOT NULL,
    after_rounding_minutes INTEGER NOT NULL,
    grace_period_minutes INTEGER DEFAULT 0,
    description TEXT,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE
);

-- Audit Table for Tracking Changes
CREATE TABLE IF NOT EXISTS audit_logs (
    audit_id SERIAL PRIMARY KEY,
    table_name VARCHAR(100) NOT NULL,
    record_id INTEGER NOT NULL,
    operation VARCHAR(20) NOT NULL, -- INSERT, UPDATE, DELETE
    old_data JSONB,
    new_data JSONB,
    changed_by VARCHAR(100) NOT NULL,
    changed_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45),
    user_agent TEXT
);

-- Create index for audit logs
CREATE INDEX IF NOT EXISTS idx_audit_table_record ON audit_logs(table_name, record_id);
CREATE INDEX IF NOT EXISTS idx_audit_changed_at ON audit_logs(changed_at);
CREATE INDEX IF NOT EXISTS idx_audit_changed_by ON audit_logs(changed_by);

-- Audit Trigger Function
CREATE OR REPLACE FUNCTION audit_trigger_function()
RETURNS TRIGGER AS $$
DECLARE
    v_old_data JSONB;
    v_new_data JSONB;
    v_operation VARCHAR(20);
BEGIN
    -- Determine operation type
    IF TG_OP = 'INSERT' THEN
        v_operation := 'INSERT';
        v_old_data := NULL;
        v_new_data := row_to_json(NEW);
    ELSIF TG_OP = 'UPDATE' THEN
        v_operation := 'UPDATE';
        v_old_data := row_to_json(OLD);
        v_new_data := row_to_json(NEW);
    ELSIF TG_OP = 'DELETE' THEN
        v_operation := 'DELETE';
        v_old_data := row_to_json(OLD);
        v_new_data := NULL;
    END IF;

    -- Insert audit record
    INSERT INTO audit_logs (
        table_name,
        record_id,
        operation,
        old_data,
        new_data,
        changed_by,
        ip_address,
        user_agent
    ) VALUES (
        TG_TABLE_NAME,
        CASE 
            WHEN TG_OP = 'INSERT' THEN NEW.id
            WHEN TG_OP = 'UPDATE' THEN NEW.id
            WHEN TG_OP = 'DELETE' THEN OLD.id
        END,
        v_operation,
        v_old_data,
        v_new_data,
        current_user,
        current_setting('app.current_ip', true),
        current_setting('app.user_agent', true)
    );

    RETURN NULL;
END;
$$ LANGUAGE plpgsql;

-- Create audit triggers for all tables
CREATE TRIGGER audit_positions
    AFTER INSERT OR UPDATE OR DELETE ON positions
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_employees
    AFTER INSERT OR UPDATE OR DELETE ON employees
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_shifts
    AFTER INSERT OR UPDATE OR DELETE ON shifts
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_attendance_records
    AFTER INSERT OR UPDATE OR DELETE ON attendance_records
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_access_control_records
    AFTER INSERT OR UPDATE OR DELETE ON access_control_records
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_leave_types
    AFTER INSERT OR UPDATE OR DELETE ON leave_types
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_leave_requests
    AFTER INSERT OR UPDATE OR DELETE ON leave_requests
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_leave_balances
    AFTER INSERT OR UPDATE OR DELETE ON leave_balances
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_schedule_templates
    AFTER INSERT OR UPDATE OR DELETE ON schedule_templates
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_employee_schedules
    AFTER INSERT OR UPDATE OR DELETE ON employee_schedules
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_monthly_rosters
    AFTER INSERT OR UPDATE OR DELETE ON monthly_rosters
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_overtime_categories
    AFTER INSERT OR UPDATE OR DELETE ON overtime_categories
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_overtime_requests
    AFTER INSERT OR UPDATE OR DELETE ON overtime_requests
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_break_types
    AFTER INSERT OR UPDATE OR DELETE ON break_types
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_break_records
    AFTER INSERT OR UPDATE OR DELETE ON break_records
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_holidays
    AFTER INSERT OR UPDATE OR DELETE ON holidays
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_pay_periods
    AFTER INSERT OR UPDATE OR DELETE ON pay_periods
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

CREATE TRIGGER audit_rounding_rules
    AFTER INSERT OR UPDATE OR DELETE ON rounding_rules
    FOR EACH ROW
    EXECUTE FUNCTION audit_trigger_function();

-- Indexes for Better Performance

-- Employee Indexes
CREATE INDEX IF NOT EXISTS idx_employees_division ON employees(division);
CREATE INDEX IF NOT EXISTS idx_employees_group ON employees(group_name);
CREATE INDEX IF NOT EXISTS idx_employees_department ON employees(department);
CREATE INDEX IF NOT EXISTS idx_employees_cost_center ON employees(cost_center);
CREATE INDEX IF NOT EXISTS idx_employees_position ON employees(position_id);
CREATE INDEX IF NOT EXISTS idx_employees_status ON employees(status);

-- Attendance Indexes
CREATE INDEX IF NOT EXISTS idx_attendance_employee_date ON attendance_records(employee_id, date);
CREATE INDEX IF NOT EXISTS idx_attendance_shift ON attendance_records(shift_id);
CREATE INDEX IF NOT EXISTS idx_attendance_status ON attendance_records(status);

-- Access Control Indexes
CREATE INDEX IF NOT EXISTS idx_access_employee_time ON access_control_records(employee_id, access_time);
CREATE INDEX IF NOT EXISTS idx_access_type ON access_control_records(access_type);

-- Leave Indexes
CREATE INDEX IF NOT EXISTS idx_leave_requests_employee ON leave_requests(employee_id);
CREATE INDEX IF NOT EXISTS idx_leave_requests_status ON leave_requests(status);
CREATE INDEX IF NOT EXISTS idx_leave_requests_dates ON leave_requests(start_date, end_date);
CREATE INDEX IF NOT EXISTS idx_leave_balances_employee ON leave_balances(employee_id);
CREATE INDEX IF NOT EXISTS idx_leave_balances_year ON leave_balances(year);

-- Schedule Indexes
CREATE INDEX IF NOT EXISTS idx_employee_schedules_employee ON employee_schedules(employee_id);
CREATE INDEX IF NOT EXISTS idx_employee_schedules_dates ON employee_schedules(start_date, end_date);
CREATE INDEX IF NOT EXISTS idx_monthly_rosters_month ON monthly_rosters(month);

-- Overtime Indexes
CREATE INDEX IF NOT EXISTS idx_overtime_requests_employee ON overtime_requests(employee_id);
CREATE INDEX IF NOT EXISTS idx_overtime_requests_status ON overtime_requests(status);
CREATE INDEX IF NOT EXISTS idx_overtime_requests_date ON overtime_requests(date);

-- Break Indexes
CREATE INDEX IF NOT EXISTS idx_break_records_attendance ON break_records(attendance_id);
CREATE INDEX IF NOT EXISTS idx_break_records_times ON break_records(start_time, end_time);

-- Holiday Indexes
CREATE INDEX IF NOT EXISTS idx_holidays_date ON holidays(date);
CREATE INDEX IF NOT EXISTS idx_holidays_type ON holidays(type);

-- Pay Period Indexes
CREATE INDEX IF NOT EXISTS idx_pay_periods_dates ON pay_periods(start_date, end_date);
CREATE INDEX IF NOT EXISTS idx_pay_periods_status ON pay_periods(status);

-- Trigger Function for Updated At
CREATE OR REPLACE FUNCTION update_updated_at_column()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ language 'plpgsql';

-- Create Triggers for All Tables
CREATE TRIGGER update_positions_updated_at
    BEFORE UPDATE ON positions
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_employees_updated_at
    BEFORE UPDATE ON employees
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_shifts_updated_at
    BEFORE UPDATE ON shifts
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_attendance_records_updated_at
    BEFORE UPDATE ON attendance_records
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_access_control_records_updated_at
    BEFORE UPDATE ON access_control_records
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_leave_types_updated_at
    BEFORE UPDATE ON leave_types
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_leave_requests_updated_at
    BEFORE UPDATE ON leave_requests
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_leave_balances_updated_at
    BEFORE UPDATE ON leave_balances
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_schedule_templates_updated_at
    BEFORE UPDATE ON schedule_templates
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_employee_schedules_updated_at
    BEFORE UPDATE ON employee_schedules
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_monthly_rosters_updated_at
    BEFORE UPDATE ON monthly_rosters
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_overtime_categories_updated_at
    BEFORE UPDATE ON overtime_categories
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_overtime_requests_updated_at
    BEFORE UPDATE ON overtime_requests
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_break_types_updated_at
    BEFORE UPDATE ON break_types
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_break_records_updated_at
    BEFORE UPDATE ON break_records
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_holidays_updated_at
    BEFORE UPDATE ON holidays
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_pay_periods_updated_ata
    BEFORE UPDATE ON pay_periods
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

CREATE TRIGGER update_rounding_rules_updated_at
    BEFORE UPDATE ON rounding_rules
    FOR EACH ROW
    EXECUTE FUNCTION update_updated_at_column();

-- Create Weekly Hours Tracking Table
CREATE TABLE IF NOT EXISTS weekly_hours (
    record_id SERIAL PRIMARY KEY,
    employee_id INTEGER NOT NULL,
    week_start_date DATE NOT NULL,
    week_end_date DATE NOT NULL,
    regular_hours DECIMAL(5,2) DEFAULT 0,
    overtime_hours DECIMAL(5,2) DEFAULT 0,
    holiday_hours DECIMAL(5,2) DEFAULT 0,
    leave_hours DECIMAL(5,2) DEFAULT 0,
    created_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP WITH TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP WITH TIME ZONE,
    FOREIGN KEY (employee_id) REFERENCES employees(employee_id),
    UNIQUE(employee_id, week_start_date)
);

-- Create function for calculating late minutes
CREATE OR REPLACE FUNCTION calculate_late_minutes()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.check_in IS NOT NULL AND NEW.shift_id IS NOT NULL THEN
        SELECT EXTRACT(EPOCH FROM (NEW.check_in - (NEW.date + s.start_time)))::INTEGER / 60
        INTO NEW.late_minutes
        FROM shifts s
        WHERE s.shift_id = NEW.shift_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create function for calculating early departure minutes
CREATE OR REPLACE FUNCTION calculate_early_departure_minutes()
RETURNS TRIGGER AS $$
BEGIN
    IF NEW.check_out IS NOT NULL AND NEW.shift_id IS NOT NULL THEN
        SELECT EXTRACT(EPOCH FROM ((NEW.date + s.end_time) - NEW.check_out))::INTEGER / 60
        INTO NEW.early_departure_minutes
        FROM shifts s
        WHERE s.shift_id = NEW.shift_id;
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create triggers for late and early departure calculations
CREATE TRIGGER calculate_late_minutes_trigger
    BEFORE INSERT OR UPDATE OF check_in ON attendance_records
    FOR EACH ROW
    EXECUTE FUNCTION calculate_late_minutes();

CREATE TRIGGER calculate_early_departure_minutes_trigger
    BEFORE INSERT OR UPDATE OF check_out ON attendance_records
    FOR EACH ROW
    EXECUTE FUNCTION calculate_early_departure_minutes();

-- Create function for updating weekly hours
CREATE OR REPLACE FUNCTION update_weekly_hours()
RETURNS TRIGGER AS $$
DECLARE
    v_week_start DATE;
    v_week_end DATE;
    v_regular_hours DECIMAL(5,2);
    v_overtime_hours DECIMAL(5,2);
BEGIN
    -- Calculate week start and end dates
    v_week_start := NEW.date - (EXTRACT(DOW FROM NEW.date) * INTERVAL '1 day');
    v_week_end := v_week_start + INTERVAL '6 days';

    -- Calculate hours worked
    IF NEW.check_in IS NOT NULL AND NEW.check_out IS NOT NULL THEN
        v_regular_hours := EXTRACT(EPOCH FROM (NEW.check_out - NEW.check_in))::DECIMAL / 3600;
        v_overtime_hours := CASE 
            WHEN v_regular_hours > 8 THEN v_regular_hours - 8
            ELSE 0
        END;
    END IF;

    -- Insert or update weekly hours
    INSERT INTO weekly_hours (
        employee_id,
        week_start_date,
        week_end_date,
        regular_hours,
        overtime_hours
    ) VALUES (
        NEW.employee_id,
        v_week_start,
        v_week_end,
        v_regular_hours,
        v_overtime_hours
    )
    ON CONFLICT (employee_id, week_start_date) 
    DO UPDATE SET
        regular_hours = weekly_hours.regular_hours + v_regular_hours,
        overtime_hours = weekly_hours.overtime_hours + v_overtime_hours,
        updated_at = CURRENT_TIMESTAMP;

    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create trigger for updating weekly hours
CREATE TRIGGER update_weekly_hours_trigger
    AFTER INSERT OR UPDATE OF check_out ON attendance_records
    FOR EACH ROW
    EXECUTE FUNCTION update_weekly_hours();

-- Create indexes for new columns
CREATE INDEX IF NOT EXISTS idx_weekly_hours_employee_week ON weekly_hours(employee_id, week_start_date);
CREATE INDEX IF NOT EXISTS idx_leave_requests_escalated ON leave_requests(escalated_to);
CREATE INDEX IF NOT EXISTS idx_leave_requests_approval ON leave_requests(approval_level);
CREATE INDEX IF NOT EXISTS idx_overtime_requests_approval ON overtime_requests(approval_level);
CREATE INDEX IF NOT EXISTS idx_holidays_recurring ON holidays(is_recurring); 