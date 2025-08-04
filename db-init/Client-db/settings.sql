CREATE SCHEMA IF NOT EXISTS settings;

-- Unified Invoice Settings Table 
DROP TABLE IF EXISTS settings.invoice_settings CASCADE;
CREATE TABLE IF NOT EXISTS settings.invoice_settings (
    id SERIAL PRIMARY KEY,
    -- Template Preferences
    template_name VARCHAR(100),

    -- Invoice Numbering Settings
    invoice_prefix VARCHAR(50),
    invoice_starting_number INTEGER DEFAULT 1,
    invoice_current_number INTEGER,

    -- Quotation Numbering Settings
    quotation_prefix VARCHAR(50),
    quotation_starting_number INTEGER DEFAULT 1,
    quotation_current_number INTEGER,

    -- Vehicle Quotation Numbering Settings
    vehicle_quotation_prefix VARCHAR(50),
    vehicle_quotation_starting_number INTEGER DEFAULT 1,
    vehicle_quotation_current_number INTEGER,

    -- Vehicle Invoice Numbering Settings
    vehicle_invoice_prefix VARCHAR(50),
    vehicle_invoice_starting_number INTEGER DEFAULT 1,
    vehicle_invoice_current_number INTEGER,

    -- Credit Note Numbering Settings
    credit_note_prefix VARCHAR(50),
    credit_note_starting_number INTEGER DEFAULT 1,
    credit_note_current_number INTEGER,

    -- Pro Forma Invoice Numbering Settings
    proforma_prefix VARCHAR(50),
    proforma_starting_number INTEGER DEFAULT 1,
    proforma_current_number INTEGER,

    -- Refund Numbering Settings
    refund_prefix VARCHAR(50),
    refund_starting_number INTEGER DEFAULT 1,
    refund_current_number INTEGER,

    -- Delivery Note Numbering Settings
    delivery_note_prefix VARCHAR(50),
    delivery_note_starting_number INTEGER DEFAULT 1,
    delivery_note_current_number INTEGER,

    date_format VARCHAR(20) DEFAULT 'Y-m-d',

    -- Credit Policy
    allow_credit_notes BOOLEAN DEFAULT FALSE,
    require_approval BOOLEAN DEFAULT FALSE,

    -- Company Info
    company_name VARCHAR(255),
    company_address TEXT,
    company_phone VARCHAR(50),
    company_email VARCHAR(255),
    vat_number VARCHAR(100),
    registration_number VARCHAR(100),

    -- Bank Info
    bank_name VARCHAR(255),
    bank_branch VARCHAR(255),
    account_number VARCHAR(100),
    swift_code VARCHAR(50),

    public_note TEXT,
    private_note TEXT,
    foot_note TEXT,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Ensure at least one row exists for settings.invoice_settings
INSERT INTO settings.invoice_settings DEFAULT VALUES ON CONFLICT DO NOTHING;

-- Add Credit Reasons Table
CREATE TABLE IF NOT EXISTS settings.credit_reasons (
    credit_reason_id SERIAL PRIMARY KEY,
    reason TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Add Default Payment Terms Table
CREATE TABLE IF NOT EXISTS settings.payment_terms (
    payment_term_id SERIAL PRIMARY KEY,
    term_name VARCHAR(100) NOT NULL,
    days_due INTEGER NOT NULL,
    is_default BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Optionally, add default_payment_term_id to invoice_settings for reference
ALTER TABLE settings.invoice_settings
ADD COLUMN IF NOT EXISTS default_payment_term_id INTEGER REFERENCES settings.payment_terms(payment_term_id);