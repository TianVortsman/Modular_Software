-- Migration script to add refund fields to invoice_settings table
-- Run this on existing databases that don't have the refund fields

-- Add refund numbering fields if they don't exist
DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'settings' AND table_name = 'invoice_settings' AND column_name = 'refund_prefix') THEN
        ALTER TABLE settings.invoice_settings ADD COLUMN refund_prefix VARCHAR(50);
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'settings' AND table_name = 'invoice_settings' AND column_name = 'refund_starting_number') THEN
        ALTER TABLE settings.invoice_settings ADD COLUMN refund_starting_number INTEGER DEFAULT 1;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns WHERE table_schema = 'settings' AND table_name = 'invoice_settings' AND column_name = 'refund_current_number') THEN
        ALTER TABLE settings.invoice_settings ADD COLUMN refund_current_number INTEGER;
    END IF;
END $$;

-- Update existing records to have default values for refund fields
UPDATE settings.invoice_settings 
SET refund_prefix = 'REF',
    refund_starting_number = 1,
    refund_current_number = 1
WHERE refund_prefix IS NULL; 