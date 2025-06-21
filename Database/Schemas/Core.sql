-- Core Schema
CREATE SCHEMA IF NOT EXISTS core;

-- Enum Tables (No Dependencies)
CREATE TABLE IF NOT EXISTS core.employment_types ();
ALTER TABLE core.employment_types ADD COLUMN IF NOT EXISTS type_id SERIAL PRIMARY KEY;
ALTER TABLE core.employment_types ADD COLUMN IF NOT EXISTS type_name VARCHAR(50) NOT NULL UNIQUE;

CREATE TABLE IF NOT EXISTS core.contract_types ();
ALTER TABLE core.contract_types ADD COLUMN IF NOT EXISTS type_id SERIAL PRIMARY KEY;
ALTER TABLE core.contract_types ADD COLUMN IF NOT EXISTS type_name VARCHAR(50) NOT NULL UNIQUE;

CREATE TABLE IF NOT EXISTS core.pay_periods ();
ALTER TABLE core.pay_periods ADD COLUMN IF NOT EXISTS period_id SERIAL PRIMARY KEY;
ALTER TABLE core.pay_periods ADD COLUMN IF NOT EXISTS period_name VARCHAR(50) NOT NULL UNIQUE;

CREATE TABLE IF NOT EXISTS core.access_levels ();
ALTER TABLE core.access_levels ADD COLUMN IF NOT EXISTS level_id SERIAL PRIMARY KEY;
ALTER TABLE core.access_levels ADD COLUMN IF NOT EXISTS level_name VARCHAR(50) NOT NULL UNIQUE;

CREATE TABLE IF NOT EXISTS core.time_categories ();
ALTER TABLE core.time_categories ADD COLUMN IF NOT EXISTS category_id SERIAL PRIMARY KEY;
ALTER TABLE core.time_categories ADD COLUMN IF NOT EXISTS category_name VARCHAR(50) NOT NULL UNIQUE;

CREATE TABLE IF NOT EXISTS core.shift_types ();
ALTER TABLE core.shift_types ADD COLUMN IF NOT EXISTS type_id SERIAL PRIMARY KEY;
ALTER TABLE core.shift_types ADD COLUMN IF NOT EXISTS type_name VARCHAR(50) NOT NULL UNIQUE;

-- Grouping Tables (No Dependencies)
CREATE TABLE IF NOT EXISTS core.divisions ();
ALTER TABLE core.divisions ADD COLUMN IF NOT EXISTS division_id SERIAL PRIMARY KEY;
ALTER TABLE core.divisions ADD COLUMN IF NOT EXISTS division_name VARCHAR(100) NOT NULL;

CREATE TABLE IF NOT EXISTS core.departments ();
ALTER TABLE core.departments ADD COLUMN IF NOT EXISTS department_id SERIAL PRIMARY KEY;
ALTER TABLE core.departments ADD COLUMN IF NOT EXISTS department_name VARCHAR(100) NOT NULL;

CREATE TABLE IF NOT EXISTS core.groups ();
ALTER TABLE core.groups ADD COLUMN IF NOT EXISTS group_id SERIAL PRIMARY KEY;
ALTER TABLE core.groups ADD COLUMN IF NOT EXISTS group_name VARCHAR(100) NOT NULL;

CREATE TABLE IF NOT EXISTS core.cost_centers ();
ALTER TABLE core.cost_centers ADD COLUMN IF NOT EXISTS cost_center_id SERIAL PRIMARY KEY;
ALTER TABLE core.cost_centers ADD COLUMN IF NOT EXISTS cost_center_name VARCHAR(100) NOT NULL;

CREATE TABLE IF NOT EXISTS core.sites ();
ALTER TABLE core.sites ADD COLUMN IF NOT EXISTS site_id SERIAL PRIMARY KEY;
ALTER TABLE core.sites ADD COLUMN IF NOT EXISTS site_name VARCHAR(100) NOT NULL;

CREATE TABLE IF NOT EXISTS core.teams ();
ALTER TABLE core.teams ADD COLUMN IF NOT EXISTS team_id SERIAL PRIMARY KEY;
ALTER TABLE core.teams ADD COLUMN IF NOT EXISTS team_name VARCHAR(100) NOT NULL;

CREATE TABLE IF NOT EXISTS core.positions ();
ALTER TABLE core.positions ADD COLUMN IF NOT EXISTS position_id SERIAL PRIMARY KEY;
ALTER TABLE core.positions ADD COLUMN IF NOT EXISTS position_name VARCHAR(255) NOT NULL;

-- Address Table (No Dependencies)
CREATE TABLE IF NOT EXISTS core.address ();
ALTER TABLE core.address ADD COLUMN IF NOT EXISTS addr_id SERIAL PRIMARY KEY;
ALTER TABLE core.address ADD COLUMN IF NOT EXISTS addr_line_1 VARCHAR(50) NOT NULL;
ALTER TABLE core.address ADD COLUMN IF NOT EXISTS addr_line_2 VARCHAR(50);
ALTER TABLE core.address ADD COLUMN IF NOT EXISTS suburb VARCHAR(35) NOT NULL;
ALTER TABLE core.address ADD COLUMN IF NOT EXISTS city VARCHAR(35) NOT NULL;
ALTER TABLE core.address ADD COLUMN IF NOT EXISTS province VARCHAR(50) NOT NULL;
ALTER TABLE core.address ADD COLUMN IF NOT EXISTS country VARCHAR(50) NOT NULL;
ALTER TABLE core.address ADD COLUMN IF NOT EXISTS postcode VARCHAR(10) NOT NULL;
ALTER TABLE core.address ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE core.address ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE core.address ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

-- Employees Table (Base Table)
CREATE TABLE IF NOT EXISTS core.employees ();
ALTER TABLE core.employees ADD COLUMN IF NOT EXISTS employee_id SERIAL PRIMARY KEY;
ALTER TABLE core.employees ADD COLUMN IF NOT EXISTS first_name VARCHAR(50) NOT NULL;
ALTER TABLE core.employees ADD COLUMN IF NOT EXISTS last_name VARCHAR(50) NOT NULL;
ALTER TABLE core.employees ADD COLUMN IF NOT EXISTS employee_number VARCHAR(50) NOT NULL UNIQUE;
ALTER TABLE core.employees ADD COLUMN IF NOT EXISTS clock_number VARCHAR(50) UNIQUE;
ALTER TABLE core.employees ADD COLUMN IF NOT EXISTS is_sales BOOLEAN DEFAULT FALSE;

-- Employee Related Tables (Dependent on employees)
CREATE TABLE IF NOT EXISTS core.employee_contact ();
ALTER TABLE core.employee_contact ADD COLUMN IF NOT EXISTS contact_id SERIAL PRIMARY KEY;
ALTER TABLE core.employee_contact ADD COLUMN IF NOT EXISTS employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE;
ALTER TABLE core.employee_contact ADD COLUMN IF NOT EXISTS email VARCHAR(100) UNIQUE;
ALTER TABLE core.employee_contact ADD COLUMN IF NOT EXISTS phone VARCHAR(20);
ALTER TABLE core.employee_contact ADD COLUMN IF NOT EXISTS address_id INTEGER REFERENCES core.address(addr_id);
ALTER TABLE core.employee_contact ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE core.employee_contact ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE core.employee_contact ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

CREATE TABLE IF NOT EXISTS core.employee_emergency_contact ();
ALTER TABLE core.employee_emergency_contact ADD COLUMN IF NOT EXISTS emergency_contact_id SERIAL PRIMARY KEY;
ALTER TABLE core.employee_emergency_contact ADD COLUMN IF NOT EXISTS employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE;
ALTER TABLE core.employee_emergency_contact ADD COLUMN IF NOT EXISTS contact_name VARCHAR(100);
ALTER TABLE core.employee_emergency_contact ADD COLUMN IF NOT EXISTS contact_phone VARCHAR(20);
ALTER TABLE core.employee_emergency_contact ADD COLUMN IF NOT EXISTS contact_relation VARCHAR(50);
ALTER TABLE core.employee_emergency_contact ADD COLUMN IF NOT EXISTS contact_email VARCHAR(100);
ALTER TABLE core.employee_emergency_contact ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE core.employee_emergency_contact ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE core.employee_emergency_contact ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

CREATE TABLE IF NOT EXISTS core.employee_personal ();
ALTER TABLE core.employee_personal ADD COLUMN IF NOT EXISTS personal_id SERIAL PRIMARY KEY;
ALTER TABLE core.employee_personal ADD COLUMN IF NOT EXISTS employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE;
ALTER TABLE core.employee_personal ADD COLUMN IF NOT EXISTS title VARCHAR(20);
ALTER TABLE core.employee_personal ADD COLUMN IF NOT EXISTS id_number VARCHAR(20);
ALTER TABLE core.employee_personal ADD COLUMN IF NOT EXISTS date_of_birth DATE;
ALTER TABLE core.employee_personal ADD COLUMN IF NOT EXISTS gender VARCHAR(10) DEFAULT 'male';
ALTER TABLE core.employee_personal ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE core.employee_personal ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE core.employee_personal ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

CREATE TABLE IF NOT EXISTS core.employee_employment ();
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS employment_id SERIAL PRIMARY KEY;
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE;
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS hire_date DATE;
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS termination_date DATE;
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS position_id INTEGER REFERENCES core.positions(position_id);
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS division_id INTEGER REFERENCES core.divisions(division_id);
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS department_id INTEGER REFERENCES core.departments(department_id);
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS group_id INTEGER REFERENCES core.groups(group_id);
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS cost_center_id INTEGER REFERENCES core.cost_centers(cost_center_id);
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS site_id INTEGER REFERENCES core.sites(site_id);
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS team_id INTEGER REFERENCES core.teams(team_id);
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS manager_id INTEGER REFERENCES core.employees(employee_id);
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS status VARCHAR(20) DEFAULT 'active';
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS employment_type_id INTEGER REFERENCES core.employment_types(type_id);
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS contract_type_id INTEGER REFERENCES core.contract_types(type_id);
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS pay_period_id INTEGER REFERENCES core.pay_periods(period_id);
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS access_level_id INTEGER REFERENCES core.access_levels(level_id);
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS time_category_id INTEGER REFERENCES core.time_categories(category_id);
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS shift_type_id INTEGER REFERENCES core.shift_types(type_id);
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE core.employee_employment ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

CREATE TABLE IF NOT EXISTS core.employee_roles ();
ALTER TABLE core.employee_roles ADD COLUMN IF NOT EXISTS role_id SERIAL PRIMARY KEY;
ALTER TABLE core.employee_roles ADD COLUMN IF NOT EXISTS employee_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE;
ALTER TABLE core.employee_roles ADD COLUMN IF NOT EXISTS role_name VARCHAR(50) NOT NULL;
ALTER TABLE core.employee_roles ADD COLUMN IF NOT EXISTS module VARCHAR(50) NOT NULL;
ALTER TABLE core.employee_roles ADD COLUMN IF NOT EXISTS active BOOLEAN DEFAULT TRUE;
ALTER TABLE core.employee_roles ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE core.employee_roles ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
ALTER TABLE core.employee_roles ADD COLUMN IF NOT EXISTS deleted_at TIMESTAMP;

-- Update address table with employee reference after employees table is created
ALTER TABLE core.address ADD COLUMN IF NOT EXISTS updated_by INTEGER REFERENCES core.employees(employee_id);


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

-- Payment Status (Core since it's used across modules)
CREATE TABLE core.payment_status (
    status_id SERIAL PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Currency (Core since it's used across modules)
CREATE TABLE core.currency (
    currency_id SERIAL PRIMARY KEY,
    code VARCHAR(3) NOT NULL UNIQUE,
    name VARCHAR(50) NOT NULL,
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
    rate NUMERIC(12,6) NOT NULL,
    effective_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Product Tables (Core since products are used across modules)
CREATE TABLE core.product (
    product_id SERIAL PRIMARY KEY,
    product_name VARCHAR(100) NOT NULL,
    product_description TEXT,
    product_price NUMERIC(12,2) NOT NULL,
    status VARCHAR(50) DEFAULT 'active',
    sku VARCHAR(50) UNIQUE,
    barcode VARCHAR(50) UNIQUE,
    type_id INTEGER REFERENCES core.product_types(type_id),
    category_id INTEGER REFERENCES core.product_categories(category_id),
    subcategory_id INTEGER REFERENCES core.product_subcategories(subcategory_id),
    image_url VARCHAR(255),
    tax_rate NUMERIC(5,2),
    discount NUMERIC(10,2),
    product_type VARCHAR(50), -- 'part', 'service', 'bundle'
    notes TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE core.product_types (
    type_id SERIAL PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE core.product_categories (
    category_id SERIAL PRIMARY KEY,
    category_name VARCHAR(50) NOT NULL UNIQUE,
    type_id INTEGER REFERENCES core.product_types(type_id),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE core.product_subcategories (
    subcategory_id SERIAL PRIMARY KEY,
    subcategory_name VARCHAR(50) NOT NULL UNIQUE,
    category_id INTEGER REFERENCES core.product_categories(category_id),
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

