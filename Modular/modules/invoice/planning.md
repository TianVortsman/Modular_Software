# Product-Supplier Linking & Management Plan

## 1. Import Logic: Linking Products and Suppliers

- **Goal:** Robustly link products to one or more suppliers during import, supporting many-to-many relationships.
- **Steps:**
  1. For each row in import:
     - Extract: `product_name`, `sku`, `supplier_name`, `supplier_email`, `website_url`, `purchase_price`, etc.
  2. **Find or create product:**
     - Prefer lookup by SKU (unique). If not found, fallback to product_name (+ type/category if needed).
     - If not found, create the product.
  3. **Find or create supplier:**
     - Lookup by supplier_name + supplier_email + website_url (all, if present).
     - If not found, fallback to supplier_name only.
     - If not found, create the supplier.
  4. **Link product to supplier:**
     - Insert `(product_id, supplier_id)` into `product_supplier`.
     - If duplicate, log as already linked.
     - If not duplicate, link is created.
     - If `purchase_price` or other price info is present, insert into `product_supplier_price_history`.
  5. **Multiple suppliers per product:**
     - Each row can create a new link; all links are preserved.
     - No links are deleted or overwritten unless explicitly requested.

- **Edge Cases:**
  - Duplicate product_name but different SKU: Use SKU as primary key.
  - Duplicate supplier_name but different email/website: Use all fields for lookup.
  - Blank/missing SKU: Fallback to product_name (warn if ambiguous).
  - Blank/missing supplier_email/website: Fallback to supplier_name.

## 2. UI/UX: Displaying & Managing Multiple Suppliers per Product

- **Product Detail View:**
  - Show a "Suppliers" tab or section listing all linked suppliers for the product.
  - For each supplier, display:
    - Supplier name, contact, email, website
    - Current purchase price (from latest `product_supplier_price_history`)
    - Option to view price history (modal or expandable row)
    - Option to set as "preferred supplier" (toggle/flag)
    - Option to unlink supplier from product
    - Option to add/edit purchase price

- **Supplier Management:**
  - In supplier detail, show all products supplied by this supplier.
  - Allow linking/unlinking products from supplier view as well.

- **Product List/Grid:**
  - Optionally show main/preferred supplier in product card/list.
  - Filter products by supplier (already implemented).

## 3. Admin & Edge Scenarios

- **Relinking:**
  - If a product is already linked to a supplier, skip or update as needed (never delete unless admin requests).
- **Unlinking:**
  - Allow admin to unlink a supplier from a product (with confirmation).
- **Preferred Supplier:**
  - Allow marking one supplier as "preferred" per product (optional, via a boolean flag in `product_supplier`).
- **Price Management:**
  - Allow adding new purchase prices (with date) for a product-supplier link.
  - Show price history and allow reverting to previous prices.
- **Bulk Actions:**
  - Allow bulk import/export of product-supplier links and prices.

## 4. Data Model Considerations

- `core.products` (product_id, sku, ...)
- `inventory.supplier` (supplier_id, supplier_name, ...)
- `inventory.product_supplier` (product_supplier_id, product_id, supplier_id, preferred, supplier_product_code, ...)
- `inventory.product_supplier_price_history` (product_supplier_price_history_id, product_supplier_id, purchase_price, start_date, end_date, ...)

## 5. Future Extensibility

- Support for supplier-specific lead times, MOQs, or discounts per product.
- Support for supplier approval workflows.
- API endpoints for managing links and prices.
- Audit log for all changes to product-supplier links and prices.

## 6. Summary Table

| Scenario                                 | What Happens?                |
|-------------------------------------------|------------------------------|
| Product & supplier both new               | Both created, link created   |
| Product exists, supplier new              | Supplier created, link made  |
| Product new, supplier exists              | Product created, link made   |
| Both exist, link missing                  | Link created                 |
| Both exist, link exists                   | Skip, log as already linked  |
| Product has multiple suppliers            | All links preserved          |
| Supplier has multiple products            | All links preserved          |
| Product-supplier link exists, new price   | Add to price history         |
| Product-supplier link exists, unlink      | Remove link (admin only)     |

---

**This plan ensures robust, flexible, and user-friendly management of product-supplier relationships and pricing.**



1. Import Logic: Linking Products and Suppliers
(Same as before — still valid)

Now adds:

When importing and restocking, a new entry is added to product_stock_entries:

Includes quantity, cost per unit, and supplier reference.

Updates inventory per-supplier without changing the global products.stock column (for now).

2. UI/UX: Displaying & Managing Multiple Suppliers per Product
(Same as before with additions)

On the Product Detail View, in the “Suppliers” tab:

Show total stock per supplier from product_stock_entries.

Show the last restock date and price from product_stock_entries.

Optional: Add mini FIFO table or sparkline chart per supplier for price trend.

Add a new Stock History tab or expandable row:

List of all stock additions (product_stock_entries)

Columns: quantity, cost per unit, remaining quantity, received_at

3. Admin & Edge Scenarios
(Expanded)

Restocking a product from a supplier:

Instead of updating a products.stock, a new row is added to product_stock_entries

Each stock row tracks how much was received and how much is left

Allows future FIFO/LIFO costing logic and accurate profit

Selling:

Stock should later be subtracted from the oldest product_stock_entries first (FIFO)

Manual adjustments:

Add entries with negative quantity for corrections, returns, shrinkage

4. Data Model Considerations (UPDATED)
✅ Core Tables (Still in Use)
core.products

inventory.supplier

inventory.product_supplier

inventory.product_supplier_price_history

✅ 🔥 NEW TABLE: inventory.product_stock_entries
sql
Copy
Edit
CREATE TABLE IF NOT EXISTS inventory.product_stock_entries (
    stock_entry_id SERIAL PRIMARY KEY,
    product_supplier_id INT NOT NULL REFERENCES inventory.product_supplier(product_supplier_id) ON DELETE CASCADE,
    quantity INT NOT NULL CHECK (quantity <> 0),
    remaining_quantity INT NOT NULL CHECK (remaining_quantity >= 0),
    cost_per_unit NUMERIC(10,2) NOT NULL CHECK (cost_per_unit >= 0),
    received_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    notes TEXT
);
🔍 Explanation:

quantity: Amount added to stock (or negative if adjustment)

remaining_quantity: How much is still available from this batch

cost_per_unit: The price per unit paid

received_at: When it was received or entered

notes: For admin/logging/returns

5. Future Extensibility (Same + More)
Additions:

Ability to track stock expiry: add expiry_date column to product_stock_entries

Returns: Just insert negative quantities referencing original entry

Stock usage log: Add a table later for tracking which sale used which stock row

Support for FIFO, average cost, margin rules

6. Summary Table (Expanded)
Scenario	What Happens?
Stock added for product & supplier	New row in product_stock_entries
Stock added again later	New row, separate batch
Stock sold	Subtract from oldest batch (FIFO)
Stock adjusted manually	Add negative row with note
Stock per supplier needed	SUM of remaining_quantity per supplier
Profit/loss calculated	Based on actual cost from stock entries

✅ Migration Strategy (Safe Additions)
You only need to add this table — no changes to your current ones.

If you're ready to support basic reporting, you can also run:

CREATE INDEX idx_stock_entries_product_id
ON inventory.product_stock_entries(product_supplier_id);