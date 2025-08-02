-- Add credit note specific fields to document_items table
ALTER TABLE invoicing.document_items 
ADD COLUMN IF NOT EXISTS credit_type VARCHAR(50) DEFAULT NULL,
ADD COLUMN IF NOT EXISTS credit_reason TEXT DEFAULT NULL;

-- Add index for better performance on credit note queries
CREATE INDEX IF NOT EXISTS idx_document_items_credit_type ON invoicing.document_items(credit_type);
CREATE INDEX IF NOT EXISTS idx_document_items_credit_reason ON invoicing.document_items(credit_reason);

-- Create credit_reasons table if it doesn't exist
CREATE TABLE IF NOT EXISTS settings.credit_reasons (
    credit_reason_id SERIAL PRIMARY KEY,
    reason VARCHAR(255) NOT NULL UNIQUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert some default credit reasons if the table is empty
INSERT INTO settings.credit_reasons (reason) VALUES 
('Return/Refund'),
('Overpayment'),
('Goodwill Credit'),
('Service Issue'),
('Product Defect'),
('Late Delivery'),
('Incorrect Billing'),
('Customer Complaint'),
('Price Adjustment'),
('Volume Discount')
ON CONFLICT DO NOTHING; 