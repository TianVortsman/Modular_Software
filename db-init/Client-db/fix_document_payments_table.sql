-- Fix Document Payments Table Migration
-- Adds missing columns to the existing document_payments table

-- 1. Add missing columns to document_payments table
DO $$ 
BEGIN
    -- Add payment_type field if it doesn't exist
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'document_payments' 
                   AND column_name = 'payment_type') THEN
        ALTER TABLE invoicing.document_payments ADD COLUMN payment_type VARCHAR(20) DEFAULT 'payment' CHECK (payment_type IN ('payment', 'refund', 'credit_note'));
    END IF;
    
    -- Add payment_status field if it doesn't exist
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'document_payments' 
                   AND column_name = 'payment_status') THEN
        ALTER TABLE invoicing.document_payments ADD COLUMN payment_status VARCHAR(20) DEFAULT 'completed' CHECK (payment_status IN ('pending', 'completed', 'failed', 'cancelled'));
    END IF;
    
    -- Add payment_method_id field if it doesn't exist
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'document_payments' 
                   AND column_name = 'payment_method_id') THEN
        ALTER TABLE invoicing.document_payments ADD COLUMN payment_method_id INTEGER REFERENCES invoicing.payment_methods(payment_method_id);
    END IF;
    
    -- Add payment_reference field if it doesn't exist
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'document_payments' 
                   AND column_name = 'payment_reference') THEN
        ALTER TABLE invoicing.document_payments ADD COLUMN payment_reference VARCHAR(100);
    END IF;
    
    -- Add payment_notes field if it doesn't exist
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'document_payments' 
                   AND column_name = 'payment_notes') THEN
        ALTER TABLE invoicing.document_payments ADD COLUMN payment_notes TEXT;
    END IF;
    
    -- Add created_by field if it doesn't exist
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'document_payments' 
                   AND column_name = 'created_by') THEN
        ALTER TABLE invoicing.document_payments ADD COLUMN created_by INTEGER REFERENCES core.employees(employee_id);
    END IF;
    
    -- Add updated_at field if it doesn't exist
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'document_payments' 
                   AND column_name = 'updated_at') THEN
        ALTER TABLE invoicing.document_payments ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
    END IF;
    
    -- Add deleted_at field if it doesn't exist
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'document_payments' 
                   AND column_name = 'deleted_at') THEN
        ALTER TABLE invoicing.document_payments ADD COLUMN deleted_at TIMESTAMP;
    END IF;
END $$;

-- 2. Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_document_payments_payment_type ON invoicing.document_payments(payment_type);
CREATE INDEX IF NOT EXISTS idx_document_payments_payment_status ON invoicing.document_payments(payment_status);
CREATE INDEX IF NOT EXISTS idx_document_payments_payment_method_id ON invoicing.document_payments(payment_method_id);
CREATE INDEX IF NOT EXISTS idx_document_payments_deleted_at ON invoicing.document_payments(deleted_at);

-- 3. Update existing records to have proper payment_type
UPDATE invoicing.document_payments SET payment_type = 'payment' WHERE payment_type IS NULL;

-- Migration completed successfully
SELECT 'Document Payments Table Migration completed successfully. All required columns have been added.' as migration_status; 