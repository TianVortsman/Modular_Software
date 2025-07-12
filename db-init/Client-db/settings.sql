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

    -- Credit Note Numbering Settings
    credit_note_prefix VARCHAR(50),
    credit_note_starting_number INTEGER DEFAULT 1,
    credit_note_current_number INTEGER,

    date_format VARCHAR(20) DEFAULT 'Y-m-d',

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


