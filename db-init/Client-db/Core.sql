
-- Core Schema
CREATE SCHEMA IF NOT EXISTS core;

CREATE TABLE core.users (
    user_id SERIAL PRIMARY KEY,
    user_name VARCHAR(100) NOT NULL,
    role VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE core.user_modules (
    user_id INT REFERENCES core.users(user_id) ON DELETE CASCADE,
    module_name VARCHAR(50) NOT NULL, -- e.g. 'invoicing', 'time', 'payroll'
    enabled BOOLEAN DEFAULT TRUE,
    PRIMARY KEY (user_id, module_name)
);

CREATE TABLE core.notifications (
    notification_id SERIAL PRIMARY KEY,
    user_id INT NOT NULL REFERENCES core.users(user_id) ON DELETE CASCADE,

    module VARCHAR(50), -- optional: e.g. 'leave', 'invoicing'
    type VARCHAR(100),  -- e.g. 'new_invoice', 'leave_approval', 'clocking_alert'

    title TEXT NOT NULL,      -- Short display heading
    message TEXT,             -- Full notification content
    url TEXT,                 -- Optional: link to frontend route (e.g. '/invoice/123')

    related_id INT,           -- Optional: ID of related entity (invoice, leave_id etc)
    related_type VARCHAR(50), -- Optional: 'invoice', 'leave', 'employee'...

    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Enum Tables (No Dependencies)
CREATE TABLE IF NOT EXISTS core.employment_types (
    employment_type_id SERIAL PRIMARY KEY,
    employment_type_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS core.contract_types (
    contract_type_id SERIAL PRIMARY KEY,
    contract_type_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS core.pay_periods (
    period_id SERIAL PRIMARY KEY,
    period_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS core.access_levels (
    level_id SERIAL PRIMARY KEY,
    level_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS core.time_categories (
    category_id SERIAL PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS core.shift_types (
    shift_type_id SERIAL PRIMARY KEY,
    shift_type_name VARCHAR(50) NOT NULL UNIQUE
);

-- Grouping Tables (No Dependencies)
CREATE TABLE IF NOT EXISTS core.divisions (
    division_id SERIAL PRIMARY KEY,
    division_name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS core.departments (
    department_id SERIAL PRIMARY KEY,
    department_name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS core.groups (
    group_id SERIAL PRIMARY KEY,
    group_name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS core.cost_centers (
    cost_center_id SERIAL PRIMARY KEY,
    cost_center_name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS core.sites (
    site_id SERIAL PRIMARY KEY,
    site_name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS core.teams (
    team_id SERIAL PRIMARY KEY,
    team_name VARCHAR(100) NOT NULL
);

CREATE TABLE IF NOT EXISTS core.positions (
    position_id SERIAL PRIMARY KEY,
    position_name VARCHAR(255) NOT NULL
);

-- Address Table (No Dependencies)
CREATE TABLE IF NOT EXISTS core.employee_address (
    employee_address_id SERIAL PRIMARY KEY,
    address_line_1 VARCHAR(50) NOT NULL,
    address_line_2 VARCHAR(50),
    suburb VARCHAR(35) NOT NULL,
    city VARCHAR(35) NOT NULL,
    province VARCHAR(50) NOT NULL,
    country VARCHAR(50) NOT NULL,
    postcode VARCHAR(10) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Employees Table (Base Table)
CREATE TABLE IF NOT EXISTS core.employees (
    employee_id SERIAL PRIMARY KEY,
    employee_first_name VARCHAR(50) NOT NULL,
    employee_last_name VARCHAR(50) NOT NULL,
    employee_number VARCHAR(50) NOT NULL UNIQUE,
    clock_number VARCHAR(50) UNIQUE,
    is_sales BOOLEAN DEFAULT FALSE
);

-- Employee Related Tables (Dependent on employees)
CREATE TABLE IF NOT EXISTS core.employee_contact (
    contact_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    email VARCHAR(100) UNIQUE,
    phone VARCHAR(20),
    address_id INTEGER REFERENCES core.employee_address(employee_address_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS core.employee_emergency_contact (
    emergency_contact_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    contact_name VARCHAR(100),
    contact_phone VARCHAR(20),
    contact_relation VARCHAR(50),
    contact_email VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS core.employee_personal (
    personal_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    title VARCHAR(20),
    id_number VARCHAR(20),
    date_of_birth DATE,
    gender VARCHAR(10) DEFAULT 'male',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS core.employee_employment (
    employment_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    hire_date DATE,
    termination_date DATE,
    position_id INTEGER REFERENCES core.positions(position_id),
    division_id INTEGER REFERENCES core.divisions(division_id),
    department_id INTEGER REFERENCES core.departments(department_id),
    group_id INTEGER REFERENCES core.groups(group_id),
    cost_center_id INTEGER REFERENCES core.cost_centers(cost_center_id),
    site_id INTEGER REFERENCES core.sites(site_id),
    team_id INTEGER REFERENCES core.teams(team_id),
    manager_id INTEGER REFERENCES core.employees(employee_id),
    employment_status VARCHAR(20) DEFAULT 'active',
    employment_type_id INTEGER REFERENCES core.employment_types(employment_type_id),
    contract_type_id INTEGER REFERENCES core.contract_types(contract_type_id),
    pay_period_id INTEGER REFERENCES core.pay_periods(period_id),
    access_level_id INTEGER REFERENCES core.access_levels(level_id),
    time_category_id INTEGER REFERENCES core.time_categories(category_id),
    shift_type_id INTEGER REFERENCES core.shift_types(shift_type_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS core.employee_roles (
    role_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    role_name VARCHAR(50) NOT NULL,
    module VARCHAR(50) NOT NULL,
    active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Tax Rates
CREATE TABLE core.tax_rates (
    tax_rate_id SERIAL PRIMARY KEY,
    tax_name VARCHAR(50) NOT NULL,
    rate NUMERIC(5,2) NOT NULL,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

INSERT INTO core.tax_rates (tax_name, rate, is_active, created_at)
VALUES
  ('No Tax', 0.00, true, NOW()),
  ('VAT 15%', 15.00, true, NOW());

-- Payment Status (Core since it's used across modules)
CREATE TABLE core.payment_status (
    status_id SERIAL PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL,
    payment_status_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Currency (Core since it's used across modules)
CREATE TABLE core.currency (
    currency_id SERIAL PRIMARY KEY,
    code VARCHAR(3) NOT NULL UNIQUE,
    currency_name VARCHAR(50) NOT NULL,
    symbol VARCHAR(5),
    decimal_places INTEGER DEFAULT 2,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Exchange Rates (Core since it's used across modules)
CREATE TABLE core.exchange_rates (
    rate_id SERIAL PRIMARY KEY,
    from_currency_id INTEGER REFERENCES core.currency(currency_id),
    to_currency_id INTEGER REFERENCES core.currency(currency_id),
    exchange_rate NUMERIC(12,6) NOT NULL,
    effective_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Product Types
CREATE TABLE IF NOT EXISTS core.product_types (
    product_type_id SERIAL PRIMARY KEY,
    product_type_name VARCHAR(50) NOT NULL UNIQUE,
    product_type_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

INSERT INTO core.product_types (product_type_name, product_type_description, created_at)
VALUES
    ('Product', 'Standard product for sale', CURRENT_TIMESTAMP),
    ('Part', 'Component or part of a product', CURRENT_TIMESTAMP),
    ('Service', 'Service offering', CURRENT_TIMESTAMP),
    ('Extra', 'Additional item or add-on', CURRENT_TIMESTAMP)
ON CONFLICT (product_type_name) DO NOTHING;


-- Product Categories
CREATE TABLE IF NOT EXISTS core.product_categories (
    category_id SERIAL PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE,
    product_type_id INTEGER REFERENCES core.product_types(product_type_id),
    category_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Product Subcategories
CREATE TABLE IF NOT EXISTS core.product_subcategories (
    subcategory_id SERIAL PRIMARY KEY,
    subcategory_name VARCHAR(50) NOT NULL UNIQUE,
    category_id INTEGER REFERENCES core.product_categories(category_id),
    subcategory_description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Product Table (references above)
CREATE TABLE IF NOT EXISTS core.products (
    product_id SERIAL PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    product_description TEXT,
    product_price NUMERIC(12,2) NOT NULL,
    product_status VARCHAR(50) DEFAULT 'active',
    sku VARCHAR(50) UNIQUE,
    barcode VARCHAR(50) UNIQUE,
    product_type_id INTEGER REFERENCES core.product_types(product_type_id),
    category_id INTEGER REFERENCES core.product_categories(category_id),
    subcategory_id INTEGER REFERENCES core.product_subcategories(subcategory_id),
    tax_rate_id INTEGER REFERENCES core.tax_rates(tax_rate_id),
    discount NUMERIC(10,2),
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Payment Methods (Core since it's used across modules)
CREATE TABLE core.payment_methods (
    method_id SERIAL PRIMARY KEY,
    method_name VARCHAR(50) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

INSERT INTO core.employment_types (employment_type_name) VALUES ('Temporary');
INSERT INTO core.employment_types (employment_type_name) VALUES ('Permanent');
INSERT INTO core.employment_types (employment_type_name) VALUES ('Contract-based');
INSERT INTO core.employment_types (employment_type_name) VALUES ('Probation');
INSERT INTO core.employment_types (employment_type_name) VALUES ('Internship');
INSERT INTO core.employment_types (employment_type_name) VALUES ('Other');

CREATE TABLE IF NOT EXISTS core.sales_targets (
    target_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    target_amount NUMERIC(12,2) NOT NULL,
    achieved_amount NUMERIC(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS core.sales_performance_snapshots (
    snapshot_id SERIAL PRIMARY KEY,
    employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    period_start DATE NOT NULL,
    period_end DATE NOT NULL,
    quotes_count INTEGER DEFAULT 0,
    invoices_count INTEGER DEFAULT 0,
    paid_invoices_count INTEGER DEFAULT 0,
    total_sales NUMERIC(12,2) DEFAULT 0,
    total_paid NUMERIC(12,2) DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);