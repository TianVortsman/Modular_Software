-- Active: 1751521549530@@127.0.0.1@5432@ACC001
-- Invoicing Schema
CREATE SCHEMA IF NOT EXISTS invoicing;

-- 1. Base Tables (no dependencies)
CREATE TABLE invoicing.customers(
    customer_id SERIAL NOT NULL,
    first_name varchar(100) NOT NULL,
    last_name varchar(100) NOT NULL,
    email varchar(255) NOT NULL,
    phone varchar(50),
    cell varchar(50),
    dob date,
    gender varchar(20),
    loyalty_level varchar(20),
    notes text,
    status varchar(20) DEFAULT 'active'::character varying,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    deleted_at timestamp without time zone,
    customer_initials varchar(255),
    customer_title varchar(255),
    tel varchar(50),
    PRIMARY KEY(customer_id)
);

DROP TABLE IF EXISTS invoicing.invoice_status CASCADE;
CREATE TABLE invoicing.invoice_status (
    status_id SERIAL PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS invoicing.address_type (
    address_type_id SERIAL PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS invoicing.contact_type (
    contact_type_id SERIAL PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

DROP TABLE IF EXISTS invoicing.company CASCADE;
CREATE TABLE invoicing.company (
    company_id SERIAL PRIMARY KEY,
    company_name VARCHAR(255) NOT NULL,
    registration_number VARCHAR(100),
    vat_number VARCHAR(100),
    industry VARCHAR(100),
    website VARCHAR(255),
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- 2. Insert default data for types/statuses
INSERT INTO invoicing.invoice_status (status_name, description) VALUES
    ('Draft', 'Initial draft invoice'),
    ('Pending', 'Invoice pending approval'),
    ('Approved', 'Invoice approved'),
    ('Sent', 'Invoice sent to customer'),
    ('Paid', 'Invoice paid'),
    ('Overdue', 'Invoice payment overdue'),
    ('Cancelled', 'Invoice cancelled'),
    ('Void', 'Invoice voided');

INSERT INTO invoicing.address_type (type_name, description) VALUES
    ('Physical', 'Physical location address'),
    ('Postal', 'Postal/mailing address'),
    ('Billing', 'Billing address'),
    ('Shipping', 'Shipping/delivery address');

INSERT INTO invoicing.contact_type (type_name, description) VALUES
    ('Primary', 'Primary contact person'),
    ('Billing', 'Billing contact person'),
    ('Technical', 'Technical contact person'),
    ('Emergency', 'Emergency contact person');

-- 3. Tables that reference only the above
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

-- 4. Link/junction tables (only reference above)
CREATE TABLE IF NOT EXISTS invoicing.company_contact (
    company_id INTEGER REFERENCES invoicing.company(company_id) ON DELETE CASCADE,
    contact_id INTEGER REFERENCES invoicing.contact_person(contact_id) ON DELETE CASCADE,
    PRIMARY KEY (company_id, contact_id)
);

CREATE TABLE IF NOT EXISTS invoicing.company_address (
    company_id INTEGER REFERENCES invoicing.company(company_id) ON DELETE CASCADE,
    address_id INTEGER REFERENCES invoicing.address(address_id) ON DELETE CASCADE,
    PRIMARY KEY (company_id, address_id)
);

CREATE TABLE IF NOT EXISTS invoicing.customer_address (
    customer_id INTEGER REFERENCES invoicing.customers(customer_id) ON DELETE CASCADE,
    address_id INTEGER REFERENCES invoicing.address(address_id) ON DELETE CASCADE,
    PRIMARY KEY (customer_id, address_id)
);

CREATE TABLE IF NOT EXISTS invoicing.customer_contact (
    customer_id INTEGER REFERENCES invoicing.customers(customer_id) ON DELETE CASCADE,
    contact_id INTEGER REFERENCES invoicing.contact_person(contact_id) ON DELETE CASCADE,
    PRIMARY KEY (customer_id, contact_id)
);

-- 5. Discount types (no dependencies)
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

-- 6. Recurring invoices (references customers, company)
DROP TABLE IF EXISTS invoicing.recurring_invoices CASCADE;
CREATE TABLE invoicing.recurring_invoices (
    recurring_id SERIAL PRIMARY KEY,
    customer_id INTEGER REFERENCES invoicing.customers(customer_id),
    company_id  INTEGER REFERENCES invoicing.company(company_id),
    frequency VARCHAR(20) NOT NULL, -- daily, weekly, monthly, yearly
    start_date DATE NOT NULL,
    end_date DATE,
    last_generated DATE,
    next_generation DATE,
    status VARCHAR(20) DEFAULT 'active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- 7. Invoices (references customers, company, recurring_invoices, invoice_status)
-- NEEDS TO BE REMOVED FROM INVOICING SCHEMA
DROP TABLE IF EXISTS invoicing.invoices CASCADE;
CREATE TABLE invoicing.invoices (
    invoice_id SERIAL PRIMARY KEY,
    customer_id INTEGER REFERENCES invoicing.customers(customer_id) ON DELETE CASCADE,
    company_id INTEGER REFERENCES invoicing.company(company_id) ON DELETE CASCADE,
    employee_id INTEGER REFERENCES core.employees(employee_id),
    invoice_type VARCHAR(30) NOT NULL DEFAULT 'standard', -- standard, vehicle, customer_po, recurring
    invoice_number VARCHAR(50) NOT NULL UNIQUE,
    invoice_date DATE NOT NULL,
    client_purchase_order_number VARCHAR(20),
    due_date DATE,
    status_id INTEGER REFERENCES invoicing.invoice_status(status_id),
    subtotal NUMERIC(12,2),
    discount_amount NUMERIC(12,2) DEFAULT 0,
    tax_amount NUMERIC(12,2) DEFAULT 0,
    total_amount NUMERIC(12,2) NOT NULL,
    pay_in_days NUMERIC,
    notes TEXT,
    terms_conditions TEXT,
    is_recurring BOOLEAN,
    recurring_id INTEGER REFERENCES invoicing.recurring_invoices(recurring_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- 8. Invoice Items (references invoices, core.product, core.tax_rates)
DROP TABLE IF EXISTS invoicing.invoice_items CASCADE;
CREATE TABLE invoicing.invoice_items (
    item_id SERIAL PRIMARY KEY,
    invoice_id INTEGER REFERENCES invoicing.invoices(invoice_id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES core.product(product_id) ON DELETE CASCADE,
    description TEXT,
    quantity INTEGER NOT NULL,
    unit_price NUMERIC(12,2) NOT NULL,
    discount_percent NUMERIC(5,2) DEFAULT 0,
    tax_rate_id INTEGER REFERENCES core.tax_rates(tax_rate_id),
    line_total NUMERIC(12,2),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- 9. Invoice Payment (references invoices)
DROP TABLE IF EXISTS invoicing.invoice_payment CASCADE;
CREATE TABLE invoicing.invoice_payment (
    invoice_payment_id SERIAL PRIMARY KEY,
    invoice_id INTEGER REFERENCES invoicing.invoices(invoice_id) ON DELETE CASCADE,
    payment_date DATE NOT NULL,
    payment_amount NUMERIC(12,2) NOT NULL,
    payment_type VARCHAR(20) DEFAULT 'payment' CHECK (payment_type IN ('payment', 'refund')),
    related_document_id INTEGER REFERENCES invoicing.customer_credit_notes(credit_note_id),
    related_document_type VARCHAR(20) CHECK (related_document_type IN ('credit_note', 'invoice')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);


-- 10. Document History (references core.employees)
DROP TABLE IF EXISTS invoicing.document_history CASCADE;
CREATE TABLE invoicing.document_history (
    history_id SERIAL PRIMARY KEY,
    document_type VARCHAR(20) NOT NULL, -- 'invoice', 'quote'
    document_id INTEGER NOT NULL,
    status_id INTEGER NOT NULL,
    action VARCHAR(50) NOT NULL,
    notes TEXT,
    created_by INTEGER REFERENCES core.employees(employee_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 11. Invoice Attachments (references invoices, core.employees)
DROP TABLE IF EXISTS invoicing.invoice_attachments CASCADE;
CREATE TABLE invoicing.invoice_attachments (
    attachment_id SERIAL PRIMARY KEY,
    invoice_id INTEGER REFERENCES invoicing.invoices(invoice_id) ON DELETE CASCADE,
    file_name VARCHAR(255) NOT NULL,
    file_path VARCHAR(255) NOT NULL,
    file_type VARCHAR(50),
    file_size INTEGER,
    uploaded_by INTEGER REFERENCES core.employees(employee_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- 12. Credit Notes (references invoices, core.payment_status, core.employees)
DROP TABLE IF EXISTS invoicing.customer_credit_notes CASCADE;
CREATE TABLE invoicing.customer_credit_notes (
    credit_note_id SERIAL PRIMARY KEY,
    invoice_id INTEGER REFERENCES invoicing.invoices(invoice_id),
    credit_note_number VARCHAR(50) NOT NULL UNIQUE,
    issue_date DATE NOT NULL,
    total_amount NUMERIC(12,2) NOT NULL,
    reason TEXT,
    status VARCHAR(30) DEFAULT 'pending',
    status_id INTEGER REFERENCES core.payment_status(status_id),
    created_by INTEGER REFERENCES core.employees(employee_id),
    approved_by INTEGER REFERENCES core.employees(employee_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);
-- 13. Credit Note Applications (track application of credit notes to invoices)
CREATE TABLE IF NOT EXISTS invoicing.credit_note_applications (
    application_id SERIAL PRIMARY KEY,
    credit_note_id INTEGER NOT NULL REFERENCES invoicing.customer_credit_notes(credit_note_id) ON DELETE CASCADE,
    invoice_id INTEGER NOT NULL REFERENCES invoicing.invoices(invoice_id) ON DELETE CASCADE,
    amount_applied NUMERIC(12,2) NOT NULL CHECK (amount_applied > 0),
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    applied_by INTEGER REFERENCES core.employees(employee_id)
);