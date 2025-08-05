-- Active: 1751521549530@@127.0.0.1@5432@ACC002
-- Document System Migration Script
-- Adds missing fields for payment tracking and credit note functionality
-- Run this script to enable full document workflow functionality

-- 1. Add payment tracking fields to documents table
DO $$ 
BEGIN
    -- Add total_paid field if it doesn't exist
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'documents' 
                   AND column_name = 'total_paid') THEN
        ALTER TABLE invoicing.documents ADD COLUMN total_paid NUMERIC(12,2) DEFAULT 0;
    END IF;
    
    -- Add last_payment_date field if it doesn't exist
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'documents' 
                   AND column_name = 'last_payment_date') THEN
        ALTER TABLE invoicing.documents ADD COLUMN last_payment_date DATE;
    END IF;
END $$;

-- 2. Add credit note fields to document_items table
DO $$ 
BEGIN
    -- Add credit_type field if it doesn't exist
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'document_items' 
                   AND column_name = 'credit_type') THEN
        ALTER TABLE invoicing.document_items ADD COLUMN credit_type VARCHAR(50) DEFAULT NULL;
    END IF;
    
    -- Add credit_reason field if it doesn't exist
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'document_items' 
                   AND column_name = 'credit_reason') THEN
        ALTER TABLE invoicing.document_items ADD COLUMN credit_reason TEXT DEFAULT NULL;
    END IF;
END $$;

-- 3. Add indexes for better performance
CREATE INDEX IF NOT EXISTS idx_documents_total_paid ON invoicing.documents(total_paid);
CREATE INDEX IF NOT EXISTS idx_documents_last_payment_date ON invoicing.documents(last_payment_date);
CREATE INDEX IF NOT EXISTS idx_document_items_credit_type ON invoicing.document_items(credit_type);
CREATE INDEX IF NOT EXISTS idx_document_items_credit_reason ON invoicing.document_items(credit_reason);

-- 4. Ensure credit_reasons table exists and has default data
CREATE TABLE IF NOT EXISTS settings.credit_reasons (
    credit_reason_id SERIAL PRIMARY KEY,
    reason VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 5. Insert default credit reasons if table is empty
INSERT INTO settings.credit_reasons (reason) 
SELECT * FROM (VALUES 
    ('Return/Refund'),
    ('Overpayment'),
    ('Goodwill Credit'),
    ('Service Issue'),
    ('Product Defect'),
    ('Late Delivery'),
    ('Incorrect Billing'),
    ('Customer Complaint'),
    ('Price Adjustment'),
    ('Volume Discount'),
    ('Damaged Goods'),
    ('Wrong Item Shipped'),
    ('Quality Issue'),
    ('Delivery Problem'),
    ('Billing Error')
) AS v(reason)
WHERE NOT EXISTS (SELECT 1 FROM settings.credit_reasons WHERE reason = v.reason);

-- 6. Add payment method table for payment tracking
CREATE TABLE IF NOT EXISTS invoicing.payment_methods (
    payment_method_id SERIAL PRIMARY KEY,
    method_name VARCHAR(100) NOT NULL UNIQUE,
    method_code VARCHAR(50) NOT NULL UNIQUE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 7. Insert default payment methods
INSERT INTO invoicing.payment_methods (method_name, method_code) 
SELECT * FROM (VALUES 
    ('Cash', 'CASH'),
    ('Bank Transfer', 'BANK_TRANSFER'),
    ('Credit Card', 'CREDIT_CARD'),
    ('Debit Card', 'DEBIT_CARD'),
    ('PayPal', 'PAYPAL'),
    ('Check', 'CHECK'),
    ('Money Order', 'MONEY_ORDER'),
    ('Electronic Funds Transfer', 'EFT')
) AS v(method_name, method_code)
WHERE NOT EXISTS (SELECT 1 FROM invoicing.payment_methods WHERE method_code = v.method_code);

-- 8. Add payment reference field to documents_payment table
DO $$ 
BEGIN
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'documents_payment' 
                   AND column_name = 'payment_method_id') THEN
        ALTER TABLE invoicing.documents_payment ADD COLUMN payment_method_id INTEGER REFERENCES invoicing.payment_methods(payment_method_id);
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'documents_payment' 
                   AND column_name = 'payment_reference') THEN
        ALTER TABLE invoicing.documents_payment ADD COLUMN payment_reference VARCHAR(100);
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'documents_payment' 
                   AND column_name = 'payment_notes') THEN
        ALTER TABLE invoicing.documents_payment ADD COLUMN payment_notes TEXT;
    END IF;
    
    IF NOT EXISTS (SELECT 1 FROM information_schema.columns 
                   WHERE table_schema = 'invoicing' 
                   AND table_name = 'documents_payment' 
                   AND column_name = 'created_by') THEN
        ALTER TABLE invoicing.documents_payment ADD COLUMN created_by INTEGER REFERENCES core.employees(employee_id);
    END IF;
END $$;

-- 9. Add indexes for payment queries
CREATE INDEX IF NOT EXISTS idx_documents_payment_document_id ON invoicing.documents_payment(document_id);
CREATE INDEX IF NOT EXISTS idx_documents_payment_payment_date ON invoicing.documents_payment(payment_date);
CREATE INDEX IF NOT EXISTS idx_documents_payment_payment_type ON invoicing.documents_payment(payment_type);

-- 10. Create function to update document balance after payment
CREATE OR REPLACE FUNCTION invoicing.update_document_balance_after_payment()
RETURNS TRIGGER AS $$
BEGIN
    -- Update total_paid and last_payment_date in documents table
    UPDATE invoicing.documents 
    SET total_paid = (
        SELECT COALESCE(SUM(payment_amount), 0) 
        FROM invoicing.documents_payment 
        WHERE document_id = NEW.document_id 
        AND payment_type = 'payment'
        AND deleted_at IS NULL
    ),
    last_payment_date = (
        SELECT MAX(payment_date) 
        FROM invoicing.documents_payment 
        WHERE document_id = NEW.document_id 
        AND payment_type = 'payment'
        AND deleted_at IS NULL
    ),
    balance_due = total_amount - (
        SELECT COALESCE(SUM(payment_amount), 0) 
        FROM invoicing.documents_payment 
        WHERE document_id = NEW.document_id 
        AND payment_type = 'payment'
        AND deleted_at IS NULL
    )
    WHERE document_id = NEW.document_id;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 11. Create trigger to automatically update document balance
DROP TRIGGER IF EXISTS trigger_update_document_balance ON invoicing.documents_payment;
CREATE TRIGGER trigger_update_document_balance
    AFTER INSERT OR UPDATE OR DELETE ON invoicing.documents_payment
    FOR EACH ROW
    EXECUTE FUNCTION invoicing.update_document_balance_after_payment();

-- 12. Add document status workflow table
CREATE TABLE IF NOT EXISTS invoicing.document_status_workflow (
    workflow_id SERIAL PRIMARY KEY,
    from_status VARCHAR(20) NOT NULL,
    to_status VARCHAR(20) NOT NULL,
    allowed_roles TEXT[],
    requires_approval BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE(from_status, to_status)
);

-- 13. Insert default status workflow rules
INSERT INTO invoicing.document_status_workflow (from_status, to_status, allowed_roles, requires_approval) 
SELECT * FROM (VALUES 
    ('draft', 'sent', ARRAY['admin', 'manager', 'sales'], FALSE),
    ('draft', 'approved', ARRAY['admin', 'manager'], TRUE),
    ('sent', 'paid', ARRAY['admin', 'manager', 'accountant'], FALSE),
    ('sent', 'overdue', ARRAY['system'], FALSE),
    ('overdue', 'paid', ARRAY['admin', 'manager', 'accountant'], FALSE),
    ('approved', 'sent', ARRAY['admin', 'manager', 'sales'], FALSE),
    ('paid', 'refunded', ARRAY['admin', 'manager'], TRUE),
    ('paid', 'credited', ARRAY['admin', 'manager'], TRUE)
) AS v(from_status, to_status, allowed_roles, requires_approval)
WHERE NOT EXISTS (
    SELECT 1 FROM invoicing.document_status_workflow 
    WHERE from_status = v.from_status AND to_status = v.to_status
);

-- 14. Add audit trail for status changes
CREATE TABLE IF NOT EXISTS invoicing.document_status_history (
    history_id SERIAL PRIMARY KEY,
    document_id INTEGER REFERENCES invoicing.documents(document_id),
    from_status VARCHAR(20),
    to_status VARCHAR(20),
    changed_by INTEGER REFERENCES core.employees(employee_id),
    change_reason TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 15. Create function to log status changes
CREATE OR REPLACE FUNCTION invoicing.log_document_status_change()
RETURNS TRIGGER AS $$
BEGIN
    IF OLD.document_status IS DISTINCT FROM NEW.document_status THEN
        INSERT INTO invoicing.document_status_history (
            document_id, 
            from_status, 
            to_status, 
            changed_by
        ) VALUES (
            NEW.document_id,
            OLD.document_status,
            NEW.document_status,
            COALESCE(NEW.updated_by, 1)
        );
    END IF;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- 16. Create trigger for status change logging
DROP TRIGGER IF EXISTS trigger_log_status_change ON invoicing.documents;
CREATE TRIGGER trigger_log_status_change
    AFTER UPDATE ON invoicing.documents
    FOR EACH ROW
    EXECUTE FUNCTION invoicing.log_document_status_change();

-- Migration completed successfully
SELECT 'Document System Migration completed successfully. All required fields and tables have been added.' as migration_status; 