-- Inventory Schema
CREATE SCHEMA IF NOT EXISTS inventory;
-- Product Related Tables
CREATE TABLE IF NOT EXISTS inventory.product_inventory (
    product_id INTEGER PRIMARY KEY REFERENCES core.products(product_id) ON DELETE CASCADE,
    product_stock_quantity VARCHAR(50),
    product_reorder_level INTEGER DEFAULT 0,
    product_lead_time INTEGER,
    product_weight NUMERIC(10,2),
    product_dimensions VARCHAR(100),
    product_brand VARCHAR(100),
    product_material VARCHAR(100),
    product_manufacturer VARCHAR(100),
    warranty_period VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory.product_stock_entries (
    stock_entry_id SERIAL PRIMARY KEY,
    product_supplier_id INT NOT NULL REFERENCES inventory.product_supplier(product_supplier_id) ON DELETE CASCADE,
    quantity INT NOT NULL CHECK (quantity <> 0),
    remaining_quantity INT NOT NULL CHECK (remaining_quantity >= 0),
    cost_per_unit NUMERIC(10,2) NOT NULL CHECK (cost_per_unit >= 0),
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT
);

CREATE INDEX idx_stock_entries_product_id
ON inventory.product_stock_entries(product_supplier_id);

CREATE TABLE IF NOT EXISTS inventory.product_parts (
    product_id INTEGER PRIMARY KEY REFERENCES core.products(product_id) ON DELETE CASCADE,
    oem_part_number VARCHAR(100),
    compatible_vehicles TEXT,
    material VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory.product_service (
    product_id INTEGER PRIMARY KEY REFERENCES core.products(product_id) ON DELETE CASCADE,
    labor_cost NUMERIC(12,2),
    estimated_time INTERVAL,
    service_frequency VARCHAR(50),
    installation_required BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory.product_bundle (
    product_id INTEGER PRIMARY KEY REFERENCES core.products(product_id) ON DELETE CASCADE,
    bundle_items TEXT, -- Consider a normalized mapping table later
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Base Tables (No Dependencies)
CREATE TABLE IF NOT EXISTS inventory.supplier (
    supplier_id SERIAL PRIMARY KEY,
    -- Basic Info
    supplier_name VARCHAR(100) NOT NULL CHECK (TRIM(supplier_name) <> ''),
    supplier_address TEXT,
    supplier_contact VARCHAR(30) CHECK (supplier_contact ~ '^[\d\+\-\s\(\)]*$'),
    supplier_email VARCHAR(100),
    -- Metadata
    website_url VARCHAR(200) CHECK (website_url ~* '^https?://'),
    -- Audit Columns
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    deleted_at TIMESTAMP,
    -- Constraints
    CHECK (deleted_at IS NULL OR deleted_at >= created_at)
);

-- Dependent Tables
CREATE TABLE IF NOT EXISTS inventory.product_supplier (
    product_supplier_id SERIAL PRIMARY KEY,
    product_id INTEGER NOT NULL REFERENCES core.products(product_id) ON DELETE CASCADE,
    supplier_id INTEGER NOT NULL REFERENCES inventory.supplier(supplier_id) ON DELETE CASCADE,

    supplier_product_code VARCHAR(100),
    preferred BOOLEAN DEFAULT FALSE,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,

    UNIQUE (product_id, supplier_id) -- optional constraint
);

CREATE TABLE IF NOT EXISTS inventory.supplier_contact_person (
    contact_person_id SERIAL PRIMARY KEY,
    supplier_id INTEGER NOT NULL REFERENCES inventory.supplier(supplier_id) ON DELETE CASCADE,
    full_name VARCHAR(100) NOT NULL,
    position VARCHAR(100),
    email VARCHAR(150) CHECK (email ~* '^[A-Z0-9._%+-]+@[A-Z0-9.-]+\.[A-Z]{2,}$'),
    phone VARCHAR(30) CHECK (phone ~ '^[\d\+\-\s\(\)]*$'),
    notes TEXT,
    created_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP NOT NULL
);

CREATE TABLE IF NOT EXISTS inventory.product_supplier_price_history (
    product_supplier_price_history_id SERIAL PRIMARY KEY,

    product_supplier_id INTEGER NOT NULL
        REFERENCES inventory.product_supplier(product_supplier_id)
        ON DELETE CASCADE,

    purchase_price NUMERIC(12,2) NOT NULL,
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP,

    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


CREATE TABLE IF NOT EXISTS inventory.purchase_order (
    purchase_order_id SERIAL PRIMARY KEY,
    supplier_id INTEGER REFERENCES inventory.supplier(supplier_id) ON DELETE CASCADE,
    purchase_order_date DATE NOT NULL,
    purchase_order_status VARCHAR(30) NOT NULL,
    total_amount NUMERIC(12,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory.purchase_order_items (
    purchase_order_item_id SERIAL PRIMARY KEY,
    purchase_order_id INTEGER REFERENCES inventory.purchase_order(purchase_order_id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES core.products(product_id) ON DELETE CASCADE,
    quantity INTEGER NOT NULL,
    unit_price NUMERIC(11,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory.purchase_payment (
    purchase_paym_id SERIAL PRIMARY KEY,
    purchase_order_id INTEGER REFERENCES inventory.purchase_order(purchase_order_id) ON DELETE CASCADE,
    payment_date DATE NOT NULL,
    payment_amount NUMERIC(12,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Supplier Invoice Management
CREATE TABLE IF NOT EXISTS inventory.supplier_invoice (
    supplier_invoice_id SERIAL PRIMARY KEY,
    supplier_id INTEGER REFERENCES inventory.supplier(supplier_id) ON DELETE CASCADE,
    invoice_number VARCHAR(50) NOT NULL,
    invoice_date DATE NOT NULL,
    due_date DATE NOT NULL,
    total_amount NUMERIC(12,0) NOT NULL,
    status VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory.supplier_invoice_items (
    supplier_invoice_item_id SERIAL PRIMARY KEY,
    supplier_invoice_id INTEGER REFERENCES inventory.supplier_invoice(supplier_invoice_id) ON DELETE CASCADE,
    product_id INTEGER REFERENCES core.products(product_id) ON DELETE CASCADE,
    quantity INTEGER NOT NULL,
    unit_price NUMERIC(11,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
); 