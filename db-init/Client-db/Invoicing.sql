-- Active: 1752321420346@@127.0.0.1@5432@ACC002

CREATE SCHEMA IF NOT EXISTS invoicing;

-- 1. Unified Clients table replacing customers and company
CREATE TABLE invoicing.clients (
    client_id SERIAL PRIMARY KEY,
    client_type VARCHAR(10) CHECK (client_type IN ('private', 'business')) NOT NULL,
    
    -- Shared fields
    client_name VARCHAR(255) NOT NULL,
    client_email VARCHAR(255),
    client_cell VARCHAR(50),
    client_tell VARCHAR(50),
    client_status VARCHAR(20) DEFAULT 'active',

    -- Private client fields
    first_name VARCHAR(100),
    last_name VARCHAR(100),
    dob DATE,
    gender VARCHAR(20),
    loyalty_level VARCHAR(20),
    title VARCHAR(20),
    initials VARCHAR(10),

    -- Business client fields
    registration_number VARCHAR(100),
    vat_number VARCHAR(100),
    website VARCHAR(255),
    industry VARCHAR(100),

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- 2. Address Type Table (unchanged)
CREATE TABLE IF NOT EXISTS invoicing.address_type (
    address_type_id SERIAL PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

INSERT INTO invoicing.address_type (type_name, description) VALUES
    ('Physical', 'Physical location address'),
    ('Postal', 'Postal/mailing address'),
    ('Billing', 'Billing address'),
    ('Shipping', 'Shipping/delivery address');

-- 3. Contact Type Table (unchanged)
CREATE TABLE IF NOT EXISTS invoicing.contact_type (
    contact_type_id SERIAL PRIMARY KEY,
    type_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

INSERT INTO invoicing.contact_type (type_name, description) VALUES
    ('Primary', 'Primary contact person'),
    ('Billing', 'Billing contact person'),
    ('Technical', 'Technical contact person'),
    ('Emergency', 'Emergency contact person');

-- 4. Address Table (unchanged)
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

-- 5. Contact Person Table (unchanged)
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

-- 6. Client Addresses (replace customer_address and company_address)
CREATE TABLE invoicing.client_addresses (
    client_id INTEGER REFERENCES invoicing.clients(client_id) ON DELETE CASCADE,
    address_id INTEGER REFERENCES invoicing.address(address_id) ON DELETE CASCADE,
    PRIMARY KEY (client_id, address_id)
);

-- 7. Client Contacts (replace customer_contact and company_contact)
CREATE TABLE invoicing.client_contacts (
    client_id INTEGER REFERENCES invoicing.clients(client_id) ON DELETE CASCADE,
    contact_id INTEGER REFERENCES invoicing.contact_person(contact_id) ON DELETE CASCADE,
    PRIMARY KEY (client_id, contact_id)
);

-- 8. Invoice Status (unchanged)
CREATE TABLE IF NOT EXISTS invoicing.invoice_status (
    status_id SERIAL PRIMARY KEY,
    status_name VARCHAR(50) NOT NULL,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Insert default invoice status values
INSERT INTO invoicing.invoice_status (status_name, description) VALUES
    ('Draft', 'Initial draft invoice'),
    ('Pending', 'Invoice pending approval'),
    ('Approved', 'Invoice approved'),
    ('Sent', 'Invoice sent to customer'),
    ('Paid', 'Invoice paid'),
    ('Overdue', 'Invoice payment overdue'),
    ('Cancelled', 'Invoice cancelled'),
    ('Void', 'Invoice voided')
ON CONFLICT DO NOTHING;

-- 9. Discount Types (unchanged)
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

-- 10. Recurring documents
CREATE TABLE invoicing.recurring_invoices (
    recurring_id SERIAL PRIMARY KEY,
    client_id INTEGER REFERENCES invoicing.clients(client_id),
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

-- 11. Unified Documents Table
CREATE TABLE invoicing.documents (
    document_id SERIAL PRIMARY KEY,
    client_id INTEGER REFERENCES invoicing.clients(client_id) ON DELETE CASCADE,
    
    document_type VARCHAR(30) NOT NULL CHECK (
        document_type IN (
            'quotation', 'proforma', 'invoice', 'vehicle_invoice', 
            'vehicle_quotation', 'credit_note', 'refund', 'recurring_invoice'
        )
    ),
    
    document_number VARCHAR(50) NOT NULL UNIQUE,    
    issue_date DATE NOT NULL,
    due_date DATE,
    
    document_status VARCHAR(20) NOT NULL DEFAULT 'draft', -- draft, pending_approval, approved, sent, paid, overdue, cancelled, void
    
    salesperson_id INTEGER REFERENCES core.employees(employee_id), -- link to sales rep
    
    subtotal NUMERIC(12,2) NOT NULL DEFAULT 0,
    discount_amount NUMERIC(12,2) NOT NULL DEFAULT 0,
    tax_amount NUMERIC(12,2) NOT NULL DEFAULT 0,
    total_amount NUMERIC(12,2) NOT NULL DEFAULT 0,
    balance_due NUMERIC(12,2) NOT NULL DEFAULT 0,
    
    client_purchase_order_number VARCHAR(50),
    
    notes TEXT,
    terms_conditions TEXT,
    
    is_recurring BOOLEAN DEFAULT FALSE,
    recurring_template_id INTEGER REFERENCES invoicing.recurring_invoices(recurring_id),
    
    requires_approval BOOLEAN DEFAULT FALSE,
    approved_by INTEGER REFERENCES core.employees(employee_id),
    approved_at TIMESTAMP,
    
    created_by INTEGER REFERENCES core.employees(employee_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- 12. Document Items
CREATE TABLE invoicing.document_items (
    item_id SERIAL PRIMARY KEY,
    document_id INTEGER REFERENCES invoicing.documents(document_id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES core.products(product_id),
    product_description TEXT,
    quantity NUMERIC(10,2) NOT NULL,
    unit_price NUMERIC(12,2) NOT NULL,
    discount_percentage NUMERIC(5,2) DEFAULT 0,
    tax_rate_id INTEGER REFERENCES core.tax_rates(tax_rate_id),
    line_total NUMERIC(12,2) NOT NULL
);

-- 13. Vehicle Details
CREATE TABLE invoicing.vehicle_details (
    vehicle_id SERIAL PRIMARY KEY,
    document_id INTEGER REFERENCES invoicing.documents(document_id) ON DELETE CASCADE,
    make TEXT,
    model TEXT,
    vin TEXT,
    engine_number TEXT,
    license_plate TEXT,
    color TEXT,
    year INTEGER,
    mileage INTEGER
);

-- 14. Document Approvals
CREATE TABLE invoicing.document_approvals (
    approval_id SERIAL PRIMARY KEY,
    document_id INTEGER REFERENCES invoicing.documents(document_id) ON DELETE CASCADE,
    requested_by INTEGER REFERENCES core.employees(employee_id),
    approved_by INTEGER REFERENCES core.employees(employee_id),
    status VARCHAR(20) CHECK (status IN ('pending', 'approved', 'rejected')) NOT NULL DEFAULT 'pending',
    comment TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP
);

-- 15. Document Audit Log
CREATE TABLE invoicing.document_audit_log (
    audit_id SERIAL PRIMARY KEY,
    document_id INTEGER REFERENCES invoicing.documents(document_id),
    user_id INTEGER REFERENCES core.employees(employee_id),
    action TEXT NOT NULL,
    previous_data JSONB,
    new_data JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 16. Document Attachments
CREATE TABLE invoicing.document_attachments (
    attachment_id SERIAL PRIMARY KEY,
    document_id INTEGER REFERENCES invoicing.documents(document_id),
    file_path TEXT NOT NULL,
    uploaded_by INTEGER REFERENCES core.employees(employee_id),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 17. Invoice Payment (references documents)
CREATE TABLE invoicing.documents_payment (
    document_payment_id SERIAL PRIMARY KEY,
    document_id INTEGER REFERENCES invoicing.documents(document_id) ON DELETE CASCADE,
    payment_date DATE NOT NULL,
    payment_amount NUMERIC(12,2) NOT NULL,
    payment_type VARCHAR(20) DEFAULT 'payment' CHECK (payment_type IN ('payment', 'refund','credit note')),
    related_document_type VARCHAR(20) CHECK (related_document_type IN ('credit_note', 'invoice')),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- 18. Credit Notes (references documents, core.payment_status, core.employees)
CREATE TABLE invoicing.customer_credit_notes (
    credit_note_id SERIAL PRIMARY KEY,
    document_id INTEGER REFERENCES invoicing.documents(document_id),
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

-- 19. Credit Note Applications (track application of credit notes to documents)
CREATE TABLE IF NOT EXISTS invoicing.credit_note_applications (
    application_id SERIAL PRIMARY KEY,
    credit_note_id INTEGER NOT NULL REFERENCES invoicing.customer_credit_notes(credit_note_id) ON DELETE CASCADE,
    document_id INTEGER NOT NULL REFERENCES invoicing.documents(document_id) ON DELETE CASCADE,
    amount_applied NUMERIC(12,2) NOT NULL CHECK (amount_applied > 0),
    applied_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    applied_by INTEGER REFERENCES core.employees(employee_id)
);

