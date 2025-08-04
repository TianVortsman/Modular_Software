-- Add Vehicle Document Numbering Fields to invoice_settings
-- This migration adds the missing vehicle-quotation and vehicle-invoice numbering fields

-- Add Vehicle Quotation Numbering Settings
DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'settings' AND table_name = 'invoice_settings' AND column_name = 'vehicle_quotation_prefix') THEN
        ALTER TABLE settings.invoice_settings ADD COLUMN vehicle_quotation_prefix VARCHAR(50);
    END IF;
END $$;

DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'settings' AND table_name = 'invoice_settings' AND column_name = 'vehicle_quotation_starting_number') THEN
        ALTER TABLE settings.invoice_settings ADD COLUMN vehicle_quotation_starting_number INTEGER DEFAULT 1;
    END IF;
END $$;

DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'settings' AND table_name = 'invoice_settings' AND column_name = 'vehicle_quotation_current_number') THEN
        ALTER TABLE settings.invoice_settings ADD COLUMN vehicle_quotation_current_number INTEGER;
    END IF;
END $$;

-- Add Vehicle Invoice Numbering Settings
DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'settings' AND table_name = 'invoice_settings' AND column_name = 'vehicle_invoice_prefix') THEN
        ALTER TABLE settings.invoice_settings ADD COLUMN vehicle_invoice_prefix VARCHAR(50);
    END IF;
END $$;

DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'settings' AND table_name = 'invoice_settings' AND column_name = 'vehicle_invoice_starting_number') THEN
        ALTER TABLE settings.invoice_settings ADD COLUMN vehicle_invoice_starting_number INTEGER DEFAULT 1;
    END IF;
END $$;

DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'settings' AND table_name = 'invoice_settings' AND column_name = 'vehicle_invoice_current_number') THEN
        ALTER TABLE settings.invoice_settings ADD COLUMN vehicle_invoice_current_number INTEGER;
    END IF;
END $$;

-- Set default values for existing records
UPDATE settings.invoice_settings 
SET 
    vehicle_quotation_prefix = 'VQUO',
    vehicle_quotation_starting_number = 1,
    vehicle_quotation_current_number = 1,
    vehicle_invoice_prefix = 'VINV',
    vehicle_invoice_starting_number = 1,
    vehicle_invoice_current_number = 1
WHERE id = 1; 