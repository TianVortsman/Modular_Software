-- Fix missing updated_at column in product_categories table
-- This script adds the updated_at column if it doesn't exist

DO $$ 
BEGIN
    -- Add updated_at column to core.product_categories if it doesn't exist
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_schema = 'core' 
        AND table_name = 'product_categories' 
        AND column_name = 'updated_at'
    ) THEN
        ALTER TABLE core.product_categories 
        ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
        
        -- Update existing records to have current timestamp
        UPDATE core.product_categories 
        SET updated_at = CURRENT_TIMESTAMP 
        WHERE updated_at IS NULL;
        
        RAISE NOTICE 'Added updated_at column to core.product_categories';
    ELSE
        RAISE NOTICE 'updated_at column already exists in core.product_categories';
    END IF;
    
    -- Add updated_at column to core.product_subcategories if it doesn't exist
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_schema = 'core' 
        AND table_name = 'product_subcategories' 
        AND column_name = 'updated_at'
    ) THEN
        ALTER TABLE core.product_subcategories 
        ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
        
        -- Update existing records to have current timestamp
        UPDATE core.product_subcategories 
        SET updated_at = CURRENT_TIMESTAMP 
        WHERE updated_at IS NULL;
        
        RAISE NOTICE 'Added updated_at column to core.product_subcategories';
    ELSE
        RAISE NOTICE 'updated_at column already exists in core.product_subcategories';
    END IF;
    
    -- Add updated_at column to core.product_types if it doesn't exist
    IF NOT EXISTS (
        SELECT 1 FROM information_schema.columns 
        WHERE table_schema = 'core' 
        AND table_name = 'product_types' 
        AND column_name = 'updated_at'
    ) THEN
        ALTER TABLE core.product_types 
        ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP;
        
        -- Update existing records to have current timestamp
        UPDATE core.product_types 
        SET updated_at = CURRENT_TIMESTAMP 
        WHERE updated_at IS NULL;
        
        RAISE NOTICE 'Added updated_at column to core.product_types';
    ELSE
        RAISE NOTICE 'updated_at column already exists in core.product_types';
    END IF;
    
END $$; 