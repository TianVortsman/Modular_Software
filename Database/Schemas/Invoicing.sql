-- Invoicing Schema
CREATE SCHEMA IF NOT EXISTS invoicing;

-- Status Management Tables (Create these first)
DROP TABLE IF EXISTS invoicing.invoice_status CASCADE;
CREATE TABLE invoicing.invoice_status (
    status_id SERIAL PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Insert default invoice statuses
INSERT INTO invoicing.invoice_status (status_name, description) VALUES
    ('Draft', 'Initial draft invoice'),
    ('Pending', 'Invoice pending approval'),
    ('Approved', 'Invoice approved'),
    ('Sent', 'Invoice sent to customer'),
    ('Paid', 'Invoice paid'),
    ('Overdue', 'Invoice payment overdue'),
    ('Cancelled', 'Invoice cancelled'),
    ('Void', 'Invoice voided');

DROP TABLE IF EXISTS invoicing.quote_status CASCADE;
CREATE TABLE invoicing.quote_status (
    status_id SERIAL PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Insert default quote statuses
INSERT INTO invoicing.quote_status (status_name, description) VALUES
    ('Draft', 'Initial draft quote'),
    ('Pending', 'Quote pending approval'),
    ('Approved', 'Quote approved'),
    ('Sent', 'Quote sent to customer'),
    ('Accepted', 'Quote accepted by customer'),
    ('Rejected', 'Quote rejected by customer'),
    ('Expired', 'Quote expired'),
    ('Converted', 'Quote converted to order'),
    ('Cancelled', 'Quote cancelled');

-- Address Management
CREATE TABLE IF NOT EXISTS invoicing.address_type (
    address_type_id SERIAL PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS invoicing.address (
    address_id SERIAL PRIMARY KEY,
    address_type_id INTEGER REFERENCES invoicing.address_type(address_type_id),
    address_line1 VARCHAR(255) NOT NULL,
    address_line2 VARCHAR(255),
    city VARCHAR(100) NOT NULL,
    suburb VARCHAR(100),
    province VARCHAR(100),
    postal_code VARCHAR(20) NOT NULL,
    country VARCHAR(100) NOT NULL,
    is_primary BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Contact Person Management
CREATE TABLE IF NOT EXISTS invoicing.contact_type (
    contact_type_id SERIAL PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS invoicing.contact_person (
    contact_id SERIAL PRIMARY KEY,
    contact_type_id INTEGER REFERENCES invoicing.contact_type(contact_type_id),
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    position VARCHAR(100),
    email VARCHAR(255),
    phone VARCHAR(50),
    cell VARCHAR(50),
    is_primary BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Company Management
DROP TABLE IF EXISTS invoicing.company CASCADE;
CREATE TABLE invoicing.company (
    company_id SERIAL PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    registration_number VARCHAR(100),
    vat_number VARCHAR(100),
    industry VARCHAR(100),
    website VARCHAR(255),
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Company Contact Link Table
CREATE TABLE IF NOT EXISTS invoicing.company_contact (
    company_id INTEGER REFERENCES invoicing.company(company_id) ON DELETE CASCADE,
    contact_id INTEGER REFERENCES invoicing.contact_person(contact_id) ON DELETE CASCADE,
    PRIMARY KEY (company_id, contact_id)
);

-- Company Address Link Table
CREATE TABLE IF NOT EXISTS invoicing.company_address (
    company_id INTEGER REFERENCES invoicing.company(company_id) ON DELETE CASCADE,
    address_id INTEGER REFERENCES invoicing.address(address_id) ON DELETE CASCADE,
    PRIMARY KEY (company_id, address_id)
);

-- Customer Management
DROP TABLE IF EXISTS invoicing.customers CASCADE;
CREATE TABLE invoicing.customers (
    customer_id SERIAL PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    email VARCHAR(255) NOT NULL,
    phone VARCHAR(50),
    cell VARCHAR(50),
    dob DATE,
    gender VARCHAR(20),
    loyalty_level VARCHAR(20),
    notes TEXT,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Customer Address Link Table
CREATE TABLE IF NOT EXISTS invoicing.customer_address (
    customer_id INTEGER REFERENCES invoicing.customers(customer_id) ON DELETE CASCADE,
    address_id INTEGER REFERENCES invoicing.address(address_id) ON DELETE CASCADE,
    PRIMARY KEY (customer_id, address_id)
);

-- Customer Contact Link Table
CREATE TABLE IF NOT EXISTS invoicing.customer_contact (
    customer_id INTEGER REFERENCES invoicing.customers(customer_id) ON DELETE CASCADE,
    contact_id INTEGER REFERENCES invoicing.contact_person(contact_id) ON DELETE CASCADE,
    PRIMARY KEY (customer_id, contact_id)
);

-- Insert default address types
INSERT INTO invoicing.address_type (type_name, description) VALUES
    ('Physical', 'Physical location address'),
    ('Postal', 'Postal/mailing address'),
    ('Billing', 'Billing address'),
    ('Shipping', 'Shipping/delivery address');

-- Insert default contact types
INSERT INTO invoicing.contact_type (type_name, description) VALUES
    ('Primary', 'Primary contact person'),
    ('Billing', 'Billing contact person'),
    ('Technical', 'Technical contact person'),
    ('Emergency', 'Emergency contact person');

-- Core Schema Dependent Tables
DROP TABLE IF EXISTS invoicing.invoice CASCADE;
CREATE TABLE invoicing.invoice (
    inv_id SERIAL PRIMARY KEY,
    customer_id INTEGER REFERENCES invoicing.customers(customer_id) ON DELETE CASCADE,
    company_id INTEGER REFERENCES invoicing.company(company_id) ON DELETE CASCADE,
    emp_id INTEGER REFERENCES core.employees(employee_id) ON DELETE CASCADE,
    inv_date DATE NOT NULL,
    due_date DATE NOT NULL,
    status_id INTEGER REFERENCES invoicing.invoice_status(status_id),
    total_amount NUMERIC(12,0) NOT NULL,
    tax_amount NUMERIC(12,0) DEFAULT 0,
    discount_amount NUMERIC(12,0) DEFAULT 0,
    notes TEXT,
    terms_conditions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Invoice Management
DROP TABLE IF EXISTS invoicing.customer_invoice CASCADE;
CREATE TABLE invoicing.customer_invoice (
    cust_inv_id SERIAL PRIMARY KEY,
    po_id INTEGER REFERENCES inventory.purchase_order(po_id) ON DELETE CASCADE,
    customer_id INTEGER REFERENCES invoicing.customers(customer_id),
    company_id INTEGER REFERENCES invoicing.company(company_id) ON DELETE CASCADE,
    inv_no VARCHAR(50) NOT NULL,
    inv_date DATE NOT NULL,
    total_amount NUMERIC(12,0) NOT NULL,
    status VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Invoice Items
DROP TABLE IF EXISTS invoicing.invoice_items CASCADE;
CREATE TABLE invoicing.invoice_items (
    inv_item_id SERIAL PRIMARY KEY,
    inv_id INTEGER REFERENCES invoicing.invoice(inv_id) ON DELETE CASCADE,
    prod_id INTEGER REFERENCES inventory.product(prod_id) ON DELETE CASCADE,
    qty INTEGER NOT NULL,
    unit_price NUMERIC(11,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Invoice Payment
DROP TABLE IF EXISTS invoicing.invoice_payment CASCADE;
CREATE TABLE invoicing.invoice_payment (
    inv_paym_id SERIAL PRIMARY KEY,
    inv_id INTEGER REFERENCES invoicing.invoice(inv_id) ON DELETE CASCADE,
    paym_date DATE NOT NULL,
    paym_amount NUMERIC(12,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Company Branch
DROP TABLE IF EXISTS invoicing.company_branch CASCADE;
CREATE TABLE invoicing.company_branch (
    branch_id SERIAL PRIMARY KEY,
    company_id INTEGER REFERENCES invoicing.company(company_id) ON DELETE CASCADE,
    branch_name VARCHAR(100) NOT NULL,
    branch_address TEXT,
    branch_tel VARCHAR(20),
    branch_email VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Vehicle Type
DROP TABLE IF EXISTS invoicing.vehicle_type CASCADE;
CREATE TABLE invoicing.vehicle_type (
    veh_type_id SERIAL PRIMARY KEY,
    veh_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Vehicle
DROP TABLE IF EXISTS invoicing.vehicle CASCADE;
CREATE TABLE invoicing.vehicle (
    veh_id SERIAL PRIMARY KEY,
    veh_type_id INTEGER REFERENCES invoicing.vehicle_type(veh_type_id),
    veh_reg_no VARCHAR(20) NOT NULL,
    veh_make VARCHAR(50) NOT NULL,
    veh_model VARCHAR(50) NOT NULL,
    veh_year INTEGER NOT NULL,
    veh_color VARCHAR(30),
    veh_vin VARCHAR(50),
    veh_engine_no VARCHAR(50),
    veh_status VARCHAR(30) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Vehicle Maintenance
DROP TABLE IF EXISTS invoicing.vehicle_maintenance CASCADE;
CREATE TABLE invoicing.vehicle_maintenance (
    maint_id SERIAL PRIMARY KEY,
    veh_id INTEGER REFERENCES invoicing.vehicle(veh_id) ON DELETE CASCADE,
    maint_date DATE NOT NULL,
    maint_type VARCHAR(50) NOT NULL,
    maint_desc TEXT,
    maint_cost NUMERIC(12,2) NOT NULL,
    maint_status VARCHAR(30) DEFAULT 'Completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Vehicle Fuel
DROP TABLE IF EXISTS invoicing.vehicle_fuel CASCADE;
CREATE TABLE invoicing.vehicle_fuel (
    fuel_id SERIAL PRIMARY KEY,
    veh_id INTEGER REFERENCES invoicing.vehicle(veh_id) ON DELETE CASCADE,
    fuel_date DATE NOT NULL,
    fuel_qty NUMERIC(10,2) NOT NULL,
    fuel_cost NUMERIC(12,2) NOT NULL,
    fuel_station VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Vehicle Insurance
DROP TABLE IF EXISTS invoicing.vehicle_insurance CASCADE;
CREATE TABLE invoicing.vehicle_insurance (
    ins_id SERIAL PRIMARY KEY,
    veh_id INTEGER REFERENCES invoicing.vehicle(veh_id) ON DELETE CASCADE,
    ins_company VARCHAR(100) NOT NULL,
    ins_policy_no VARCHAR(50) NOT NULL,
    ins_start_date DATE NOT NULL,
    ins_end_date DATE NOT NULL,
    ins_premium NUMERIC(12,2) NOT NULL,
    ins_status VARCHAR(30) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Vehicle License
DROP TABLE IF EXISTS invoicing.vehicle_license CASCADE;
CREATE TABLE invoicing.vehicle_license (
    lic_id SERIAL PRIMARY KEY,
    veh_id INTEGER REFERENCES invoicing.vehicle(veh_id) ON DELETE CASCADE,
    lic_number VARCHAR(50) NOT NULL,
    lic_issue_date DATE NOT NULL,
    lic_expiry_date DATE NOT NULL,
    lic_fee NUMERIC(12,2) NOT NULL,
    lic_status VARCHAR(30) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Sales Management
DROP TABLE IF EXISTS invoicing.sales_order CASCADE;
CREATE TABLE invoicing.sales_order (
    so_id SERIAL PRIMARY KEY,
    customer_id INTEGER REFERENCES invoicing.customers(customer_id) ON DELETE CASCADE,
    order_date DATE NOT NULL,
    order_status VARCHAR(30) NOT NULL,
    total_amount NUMERIC(12,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Sales Order Items
DROP TABLE IF EXISTS invoicing.sales_order_items CASCADE;
CREATE TABLE invoicing.sales_order_items (
    so_item_id SERIAL PRIMARY KEY,
    so_id INTEGER REFERENCES invoicing.sales_order(so_id) ON DELETE CASCADE,
    prod_id INTEGER REFERENCES inventory.product(prod_id) ON DELETE CASCADE,
    qty INTEGER NOT NULL,
    unit_price NUMERIC(11,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Sales Payment
DROP TABLE IF EXISTS invoicing.sales_payment CASCADE;
CREATE TABLE invoicing.sales_payment (
    sales_paym_id SERIAL PRIMARY KEY,
    so_id INTEGER REFERENCES invoicing.sales_order(so_id) ON DELETE CASCADE,
    paym_date DATE NOT NULL,
    paym_amount NUMERIC(12,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Quote Management
DROP TABLE IF EXISTS invoicing.quote CASCADE;
CREATE TABLE invoicing.quote (
    quote_id SERIAL PRIMARY KEY,
    customer_id INTEGER REFERENCES invoicing.customers(customer_id) ON DELETE CASCADE,
    company_id INTEGER REFERENCES invoicing.company(company_id) ON DELETE CASCADE,
    quote_date DATE NOT NULL,
    expiration_date DATE NOT NULL,
    total_amount NUMERIC(12,0) NOT NULL,
    status_id INTEGER REFERENCES invoicing.quote_status(status_id),
    notes TEXT,
    terms_conditions TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Quote Items
DROP TABLE IF EXISTS invoicing.quote_items CASCADE;
CREATE TABLE invoicing.quote_items (
    quote_item_id SERIAL PRIMARY KEY,
    quote_id INTEGER REFERENCES invoicing.quote(quote_id) ON DELETE CASCADE,
    prod_id INTEGER REFERENCES inventory.product(prod_id) ON DELETE CASCADE,
    description TEXT,
    qty INTEGER NOT NULL,
    unit_price NUMERIC(11,0) NOT NULL,
    discount_percent NUMERIC(5,2) DEFAULT 0,
    tax_rate NUMERIC(5,2) DEFAULT 0,
    line_total NUMERIC(12,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Quote History
DROP TABLE IF EXISTS invoicing.quote_history CASCADE;
CREATE TABLE invoicing.quote_history (
    history_id SERIAL PRIMARY KEY,
    quote_id INTEGER REFERENCES invoicing.quote(quote_id) ON DELETE CASCADE,
    status_id INTEGER REFERENCES invoicing.quote_status(status_id),
    action VARCHAR(50) NOT NULL,
    notes TEXT,
    created_by INTEGER REFERENCES core.employees(employee_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Quote Attachments
DROP TABLE IF EXISTS invoicing.quote_attachments CASCADE;
CREATE TABLE invoicing.quote_attachments (
    attachment_id SERIAL PRIMARY KEY,
    quote_id INTEGER REFERENCES invoicing.quote(quote_id) ON DELETE CASCADE,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INTEGER,
    uploaded_by INTEGER REFERENCES core.employees(employee_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Invoice History
DROP TABLE IF EXISTS invoicing.invoice_history CASCADE;
CREATE TABLE invoicing.invoice_history (
    history_id SERIAL PRIMARY KEY,
    inv_id INTEGER REFERENCES invoicing.invoice(inv_id) ON DELETE CASCADE,
    status_id INTEGER REFERENCES invoicing.invoice_status(status_id),
    action VARCHAR(50) NOT NULL,
    notes TEXT,
    created_by INTEGER REFERENCES core.employees(employee_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Invoice Attachments
DROP TABLE IF EXISTS invoicing.invoice_attachments CASCADE;
CREATE TABLE invoicing.invoice_attachments (
    attachment_id SERIAL PRIMARY KEY,
    inv_id INTEGER REFERENCES invoicing.invoice(inv_id) ON DELETE CASCADE,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INTEGER,
    uploaded_by INTEGER REFERENCES core.employees(employee_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Customer Invoice Items
DROP TABLE IF EXISTS invoicing.customer_invoice_items CASCADE;
CREATE TABLE invoicing.customer_invoice_items (
    cust_inv_item_id SERIAL PRIMARY KEY,
    cust_inv_id INTEGER REFERENCES invoicing.customer_invoice(cust_inv_id) ON DELETE CASCADE,
    prod_id INTEGER REFERENCES inventory.product(prod_id) ON DELETE CASCADE,
    line_no NUMERIC(5,0) NOT NULL,
    line_qty NUMERIC(7,0) NOT NULL,
    unit_price NUMERIC NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Vehicle Invoice
DROP TABLE IF EXISTS invoicing.vehicle_invoice CASCADE;
CREATE TABLE invoicing.vehicle_invoice (
    veh_inv_id SERIAL PRIMARY KEY,
    veh_id INTEGER REFERENCES invoicing.vehicle(veh_id) ON DELETE CASCADE,
    company_id INTEGER REFERENCES invoicing.company(company_id) ON DELETE CASCADE,
    inv_no VARCHAR(50) NOT NULL,
    inv_date DATE NOT NULL,
    total_amount NUMERIC(12,0) NOT NULL,
    status VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Vehicle Invoice Items
DROP TABLE IF EXISTS invoicing.vehicle_invoice_items CASCADE;
CREATE TABLE invoicing.vehicle_invoice_items (
    veh_inv_item_id SERIAL PRIMARY KEY,
    veh_inv_id INTEGER REFERENCES invoicing.vehicle_invoice(veh_inv_id) ON DELETE CASCADE,
    line_no NUMERIC(5,0) NOT NULL,
    line_qty NUMERIC(7,0) NOT NULL,
    unit_price NUMERIC NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Tax Rates
CREATE TABLE invoicing.tax_rates (
    tax_id SERIAL PRIMARY KEY,
    tax_name VARCHAR(50) NOT NULL,
    rate NUMERIC(5,2) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Discount Types
CREATE TABLE invoicing.discount_types (
    discount_id SERIAL PRIMARY KEY,
    discount_name VARCHAR(50) NOT NULL,
    discount_type VARCHAR(20) NOT NULL, -- percentage or fixed
    value NUMERIC(10,2) NOT NULL,
    description TEXT,
    is_active BOOLEAN DEFAULT true,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Credit Notes
CREATE TABLE invoicing.credit_notes (
    credit_note_id SERIAL PRIMARY KEY,
    invoice_id INTEGER REFERENCES invoicing.invoice(inv_id),
    credit_note_number VARCHAR(50) NOT NULL,
    issue_date DATE NOT NULL,
    total_amount NUMERIC(12,0) NOT NULL,
    reason TEXT,
    status_id INTEGER REFERENCES core.payment_status(status_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Invoice Templates (Create before recurring_invoices)
DROP TABLE IF EXISTS invoicing.invoice_templates CASCADE;
CREATE TABLE invoicing.invoice_templates (
    template_id SERIAL PRIMARY KEY,
    template_name VARCHAR(100) NOT NULL,
    description TEXT,
    template_content TEXT NOT NULL,
    is_default BOOLEAN DEFAULT false,
    created_by INTEGER REFERENCES core.employees(employee_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Recurring Invoices
DROP TABLE IF EXISTS invoicing.recurring_invoices CASCADE;
CREATE TABLE invoicing.recurring_invoices (
    recurring_id SERIAL PRIMARY KEY,
    customer_id INTEGER REFERENCES invoicing.customers(customer_id),
    frequency VARCHAR(20) NOT NULL, -- daily, weekly, monthly, yearly
    start_date DATE NOT NULL,
    end_date DATE,
    last_generated DATE,
    next_generation DATE,
    template_id INTEGER REFERENCES invoicing.invoice_templates(template_id),
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
); 