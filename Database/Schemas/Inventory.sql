-- Inventory Schema
CREATE SCHEMA IF NOT EXISTS inventory;
-- Product Related Tables
CREATE TABLE IF NOT EXISTS inventory.product_inventory (
    product_id INTEGER PRIMARY KEY REFERENCES core.product(product_id) ON DELETE CASCADE,
    stock_quantity VARCHAR(50),
    reorder_level INTEGER DEFAULT 0,
    lead_time INTEGER,
    weight NUMERIC(10,2),
    dimensions VARCHAR(100),
    brand VARCHAR(100),
    manufacturer VARCHAR(100),
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
    suppl_id SERIAL PRIMARY KEY,
    suppl_name VARCHAR(100) NOT NULL,
    suppl_address TEXT,
    suppl_contact VARCHAR(30),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Dependent Tables
CREATE TABLE IF NOT EXISTS inventory.product_supplier (
    prod_id INTEGER REFERENCES core.product(product_id) ON DELETE CASCADE,
    suppl_id INTEGER REFERENCES inventory.supplier(suppl_id) ON DELETE CASCADE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT product_supplier_pkey PRIMARY KEY (prod_id, suppl_id)
);

CREATE TABLE IF NOT EXISTS inventory.product_supplier_price_history (
    id SERIAL PRIMARY KEY,
    prod_id INTEGER REFERENCES core.product(product_id) ON DELETE CASCADE,
    suppl_id INTEGER REFERENCES inventory.supplier(suppl_id) ON DELETE CASCADE,
    purch_price NUMERIC(12,2) NOT NULL,
    start_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    end_date TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory.purchase_order (
    po_id SERIAL PRIMARY KEY,
    suppl_id INTEGER REFERENCES inventory.supplier(suppl_id) ON DELETE CASCADE,
    po_date DATE NOT NULL,
    po_status VARCHAR(30) NOT NULL,
    total_amount NUMERIC(12,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory.purchase_order_items (
    po_item_id SERIAL PRIMARY KEY,
    po_id INTEGER REFERENCES inventory.purchase_order(po_id) ON DELETE CASCADE,
    prod_id INTEGER REFERENCES core.product(product_id) ON DELETE CASCADE,
    qty INTEGER NOT NULL,
    unit_price NUMERIC(11,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory.purchase_payment (
    purchase_paym_id SERIAL PRIMARY KEY,
    po_id INTEGER REFERENCES inventory.purchase_order(po_id) ON DELETE CASCADE,
    paym_date DATE NOT NULL,
    paym_amount NUMERIC(12,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Supplier Invoice Management
CREATE TABLE IF NOT EXISTS inventory.supplier_invoice (
    suppl_inv_id SERIAL PRIMARY KEY,
    suppl_id INTEGER REFERENCES inventory.supplier(suppl_id) ON DELETE CASCADE,
    inv_number VARCHAR(50) NOT NULL,
    inv_date DATE NOT NULL,
    due_date DATE NOT NULL,
    total_amount NUMERIC(12,0) NOT NULL,
    status VARCHAR(30) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

CREATE TABLE IF NOT EXISTS inventory.supplier_invoice_items (
    suppl_inv_item_id SERIAL PRIMARY KEY,
    suppl_inv_id INTEGER REFERENCES inventory.supplier_invoice(suppl_inv_id) ON DELETE CASCADE,
    prod_id INTEGER REFERENCES core.product(product_id) ON DELETE CASCADE,
    qty INTEGER NOT NULL,
    unit_price NUMERIC(11,0) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
); 