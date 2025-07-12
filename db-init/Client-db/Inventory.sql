-- Inventory Schema
CREATE SCHEMA IF NOT EXISTS inventory;
-- Product Related Tables
CREATE TABLE IF NOT EXISTS inventory.product_inventory (
    product_id INTEGER PRIMARY KEY REFERENCES core.product(product_id) ON DELETE CASCADE,
    product_stock_quantity VARCHAR(50),
    product_reorder_level INTEGER DEFAULT 0,
    product_lead_time INTEGER,
    product_weight NUMERIC(10,2),
    product_dimensions VARCHAR(100),
    product_brand VARCHAR(100),
    product_manufacturer VARCHAR(100),
    warranty_period VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory.product_parts (
    product_id INTEGER PRIMARY KEY REFERENCES core.product(product_id) ON DELETE CASCADE,
    oem_part_number VARCHAR(100),
    compatible_vehicles TEXT,
    material VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory.product_service (
    product_id INTEGER PRIMARY KEY REFERENCES core.product(product_id) ON DELETE CASCADE,
    labor_cost NUMERIC(12,2),
    estimated_time INTERVAL,
    service_frequency VARCHAR(50),
    installation_required BOOLEAN DEFAULT false,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory.product_bundle (
    product_id INTEGER PRIMARY KEY REFERENCES core.product(product_id) ON DELETE CASCADE,
    bundle_items TEXT, -- Consider a normalized mapping table later
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Base Tables (No Dependencies)
CREATE TABLE IF NOT EXISTS inventory.supplier (
    supplier_id SERIAL PRIMARY KEY,
    supplier_name VARCHAR(100) NOT NULL,
    supplier_address TEXT,
    supplier_contact VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Dependent Tables
CREATE TABLE IF NOT EXISTS inventory.product_supplier (
    product_id INTEGER REFERENCES core.product(product_id) ON DELETE CASCADE,
    supplier_id INTEGER REFERENCES inventory.supplier(supplier_id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT product_supplier_pkey PRIMARY KEY (product_id, supplier_id)
);

CREATE TABLE IF NOT EXISTS inventory.product_supplier_price_history (
    product_supplier_price_history_id SERIAL PRIMARY KEY,
    product_id INTEGER REFERENCES core.product(product_id) ON DELETE CASCADE,
    supplier_id INTEGER REFERENCES inventory.supplier(supplier_id) ON DELETE CASCADE,
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
    product_id INTEGER REFERENCES core.product(product_id) ON DELETE CASCADE,
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
    product_id INTEGER REFERENCES core.product(product_id) ON DELETE CASCADE,
    quantity INTEGER NOT NULL,
    unit_price NUMERIC(11,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
); 