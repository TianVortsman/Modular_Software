# Invoicing Module: Implementation Order & Milestones
---
## Implementation Order & Milestones
This section defines the recommended order for implementing the invoicing module, based on dependencies and logical build-up. Each phase must be robust and tested before moving to the next.
### Summary Table

| Order | Area/Screen         | Why? (Dependency)                                   | Must be Complete Before...         |
|-------|---------------------|----------------------------------------------------|------------------------------------|
| 1     | Setup/Config        | All modules depend on config, numbering, terms     | Everything                        |
| 2     | Products            | Documents need products                            | Documents, Payments, Reports       |
| 3     | Clients             | Documents need clients                             | Documents, Payments, Reports       |
| 4     | Recurring/Terms     | Documents need recurring/payment term logic        | Documents                         |
| 5     | Documents           | Core business logic, needs all above               | Payments, Sales Reps, Dashboard    |
| 6     | Payments            | Linked to documents                                | Sales Reps, Dashboard, Reports     |
| 7     | Sales Reps          | Linked to documents/payments                       | Dashboard, Reports                 |
| 8     | Dashboard           | Aggregates all data                                | Reports                            |
| 9     | Reports             | Needs all data to be correct                       | -                                  |
---

## 1. Setup & Configuration (`invoice-setup.php`)

**Why first?**
All other modules depend on correct setup: categories, types, suppliers, document numbering, tax rates, default payment terms, etc.

**Key Milestones:**
- [ ] Product types, categories, subcategories CRUD
- [ ] Supplier CRUD
- [ ] Sales target and credit reason CRUD (optional for initial phase)
- [ ] **Document numbering configuration** (for invoices, quotations, credit notes, etc.) — must be fully implemented and tested
- [ ] Tax rates and default payment terms (for recurring, invoices, etc.)
- [ ] All setup modals and validation

(See detailed checklist in section 1 below)
---
## 2. Products (`invoice-products.php`)
**Why second?**
Products are core to all documents (invoices, quotes, etc.). Must be able to add, edit, categorize, and link to suppliers before documents can reference them.

**Key Milestones:**
- [ ] Product CRUD (all types: product, vehicle, part, service, extra, etc.)
- [ ] Product import (CSV/PDF)
- [ ] Supplier linking/unlinking
- [ ] Stock management (adjustments, history)
- [ ] Product detail modal (with all tabs)
- [ ] All product modals and validation
---
## 3. Clients (`invoice-clients.php`)

**Why third?**
Clients (companies/customers) are required for document creation. Their data structure and validation must be solid before documents reference them.

**Key Milestones:**
- [ ] Client CRUD (company/customer, all fields)
- [ ] Address and contact management
- [ ] Client detail modal (with all tabs)
- [ ] All client modals and validation

---

## 4. Recurring Invoice & Payment Terms Setup

**Why now?**
Recurring invoices and default payment terms are referenced in document creation and must be available for correct document logic.

**Key Milestones:**
- [ ] Recurring invoice configuration (frequency, start/end, default terms)
- [ ] Default payment terms setup (in setup or as part of recurring config)
- [ ] Ensure these settings are accessible to the document modal

---

## 5. Documents (rename `invoices.php` → `documents.php`)

**Why now?**
With products, clients, suppliers, numbering, and payment terms in place, you can now build robust document creation, editing, and management.

**Key Milestones:**
- [ ] Document CRUD (invoices, quotations, credit notes, refunds, vehicle docs, recurring)
- [ ] Document modal (all types, all tabs, validation)
- [ ] Line item management (products, quantities, prices, tax, discounts)
- [ ] Document numbering (uses setup config)
- [ ] PDF generation
- [ ] Status changes (draft, approved, finalized, etc.)
- [ ] All document modals and validation

---

## 6. Payments (`invoice-payments.php`)

**Why now?**
Payments, credit notes, and refunds are linked to documents. Must have documents in place to test and validate payment flows.

**Key Milestones:**
- [ ] Payment CRUD (add, edit, delete)
- [ ] Credit note and refund management
- [ ] Payment detail modal
- [ ] All payment modals and validation

---

## 7. Sales Reps (`sales-reps.php`)

**Why now?**
Sales reps and targets are often linked to documents and payments. Implement after core document/payment flows are stable.

**Key Milestones:**
- [ ] Sales rep CRUD
- [ ] Assign/view sales targets
- [ ] Performance stats and recent deals
- [ ] All sales rep modals and validation

---

## 8. Dashboard (`invoice-dashboard.php`)

**Why now?**
The dashboard aggregates data from all previous modules. Build it after all core data flows are implemented and tested.

**Key Milestones:**
- [ ] Dashboard cards (totals, revenue, etc.)
- [ ] Recent/recurring invoices tables
- [ ] Analytics chart
- [ ] Quick actions

---

## 9. Reports (`invoice-reports.php`)

**Why last?**
Reports depend on all other data being present and correct. Build after all CRUD and business logic is stable.

**Key Milestones:**
- [ ] Report selection and filtering
- [ ] Export (CSV/PDF)
- [ ] All report modals and validation

---

# Detailed Checklists (by Implementation Order)

(Sections below remain as previously detailed, but now follow the above order. See each section for granular checklists and milestones.)

---

## 1. Setup & Configuration (`invoice-setup.php`)

**Purpose:**
Manage categories, subcategories, suppliers, sales targets, credit reasons.

### Features & Detailed Checklist

#### 7.1. Tabbed Sections
- [x] Tabs for: Products, Sales, Suppliers, Credit Notes
- [x] Each tab loads relevant data (filtered by type)
- [x] Highlight active tab, update content on switch

#### 7.2. Manage Categories/Subcategories
- [x] List, add, edit, delete categories
- [x] List, add, edit, delete subcategories
- [x] Filter by product type

#### 7.3. Manage Suppliers
- [x] List, add, edit, delete suppliers
- [x] Link/unlink products to suppliers
- [x] View supplier details (products supplied, contact info)

#### 7.4. Manage Sales Targets
- [~] List, add, edit, delete sales targets (UI present, backend logic may need review)
- [~] Assign targets to sales reps (UI present, needs full integration)
- [~] Filter by period, rep (UI present, needs backend support)

#### 7.5. Manage Credit Reasons
- [x] List, add, edit, delete credit reasons

### Modals Needed
- [x] Category Modal
- [x] Subcategory Modal
- [x] Supplier Modal
- [x] Sales Target Modal
- [x] Credit Reason Modal
- [x] ResponseModal, LoadingModal

### API Endpoints
- [x] `setup-api.php?action=getCategories` / `getSubcategories` / `saveCategory` / `saveSubcategory`
- [x] `setup-api.php?action=getSuppliers` / `getSupplier` / `addSupplier` / `updateSupplier`
- [~] `setup-api.php?action=getSalesTargets` / `addSalesTarget` / `updateSalesTarget` (partial)
- [x] `setup-api.php?action=getCreditReasons` / `addCreditReason` / `updateCreditReason`

**API Contract:**
- [x] All endpoints return `{ success, message, data, error_code }`
- [~] Validate all input, handle missing/invalid data (needs review for all edge cases)

### Controller Logic
- [x] ProductController (categories/subcategories)
- [x] SupplierController
- [~] SalesController (targets) (partial)
- [x] CreditReasonController (if needed)
  - [~] Validate all input (needs review for all edge cases)
  - [~] Use transactions for add/edit/delete (partial)
  - [~] Log all actions and errors (partial)

### Edge Cases & Integration
- [~] Prevent deleting categories/subcategories/suppliers with active links (needs full backend enforcement)
- [x] Ensure all modals reset on open/close
- [~] Show user-friendly errors for validation failures (partial)

### Testing
- [~] Test all CRUD flows (add, edit, delete) (basic tested, edge cases may need more)
- [~] Test all modals (open, close, submit, error) (basic tested)
- [~] Test all filters and search (basic tested)

### 1.2. Credit Reasons
- [x] DB table for credit reasons exists
- [x] API endpoints for list/add/update/delete credit reasons
- [x] Controller functions for credit reasons (modular, reusable)
- [x] JS API helpers for credit reasons
- [x] Modal for add/edit credit reason (autofill, reset, validation)
- [x] List, render, and delete logic in setup screen
- [x] Universal modals for feedback (ResponseModal, LoadingModal)
- [x] UI integration: section, list, add button, modal
- [x] Naming and IDs are consistent across layers
- [x] Ready for testing

#### Test Checklist: Credit Reasons
- [ ] Add a new credit reason (should appear in list)
- [ ] Edit an existing credit reason (should update in list)
- [ ] Delete a credit reason (should be removed from list)
- [ ] Error handling: try to add blank reason, check validation
- [ ] Modal resets on open/close, correct mode (add/edit)
- [ ] All feedback via ResponseModal/LoadingModal

---

## 2. Products (`invoice-products.php`)

**Purpose:**
Manage all products, vehicles, parts, extras, services.

### Features & Detailed Checklist

#### 2.1. Tabbed Sections
- [x] Tabs for: Products, Vehicles, Parts, Extras, Services, Discontinued, Disabled
- [x] Each tab loads relevant data (filtered by type)
- [x] Highlight active tab, update content on switch

#### 2.2. Search & Filter
- [x] Search input (by name, SKU, barcode)
- [x] Filter dropdowns: Category, Subcategory, Supplier
- [x] “Clear Filters” button
- [x] All filters update product list via API

#### 2.3. Product List/Grid
- [x] Display products as cards or table rows
- [x] Show: Image, Name, SKU, Price, Stock, Supplier, Actions
- [x] Actions: View, Edit, Delete, Adjust Stock, Link Supplier

#### 2.4. Add/Edit/Delete Product
- [x] “Add Product” button (opens Universal Product Modal in add mode)
- [x] Edit button (opens modal in edit mode, autofills data)
- [x] Delete button (confirms, then calls API)
- [x] Validate all fields before submit
- [x] Show errors in ResponseModal

#### 2.5. Import Products
- [x] “Import” button (opens Import Modal)
- [~] Support PDF/CSV upload (PDF import modal exists, CSV may need review)
- [~] Show preview of parsed data (PDF import shows preview, CSV preview needs check)
- [~] Allow user to confirm before import (PDF import has confirm, CSV needs check)
- [~] Show import results (success/errors) (partial, needs review for error handling)

#### 2.6. Product Details
- [~] Doubble Click product to open details modal (may be single click currently, needs double-click event)
- [x] Show all info: suppliers, stock history, price history, attributes
- [x] Tabs for: Basic Info, Pricing, Inventory, Suppliers, Stock History, Notes

#### 2.7. Adjust Stock
- [x] “Adjust Stock” button (opens Adjust Stock Modal)
- [x] Select supplier, enter quantity (positive/negative), cost, notes
- [x] Validate input, call API, update UI

#### 2.8. Supplier Link/Unlink
- [x] “Link Supplier” button (opens modal, select supplier)
- [x] “Unlink” button (removes link after confirmation)
- [x] Update UI after link/unlink

### Modals Needed
- [x] Universal Product Modal (add/edit, tabbed)
- [x] Adjust Stock Modal
- [x] Supplier Link/Unlink Modal
- [x] Stock History Modal
- [x] Import Modal
- [x] ResponseModal, LoadingModal

### API Endpoints
- [x] `products.php?action=list` (with filters)
- [x] `products.php?action=get`
- [x] `products.php?action=add` / `update` / `delete`
- [x] `products.php?action=get_product_suppliers_and_stock`
- [x] `products.php?action=adjust_stock`
- [~] `products.php?action=import` (partial, needs review for CSV)
- [x] `products.php?action=add_supplier_link`
- [x] `products.php?action=remove_supplier_link`

**API Contract:**
- [x] All endpoints return `{ success, message, data, error_code }`
- [~] Validate all input, handle missing/invalid data (needs review for all edge cases)
- [~] Return detailed error info for imports (partial)

### Controller Logic
- [x] ProductController: CRUD, filtering, import, stock, supplier linking
  - [x] Validate all input
  - [x] Use transactions for add/edit/delete/import
  - [x] Log all actions and errors
- [x] SupplierController: For supplier linking/unlinking

### Edge Cases & Integration
- [~] Handle duplicate SKUs, missing required fields (partial, needs more robust checks)
- [~] Prevent deleting products with active stock or sales (show error, needs backend enforcement)
- [x] Ensure all modals reset on open/close
- [~] Show user-friendly errors for import failures (partial)

### Testing
- [~] Test all CRUD flows (add, edit, delete, adjust stock) (basic tested, edge cases may need more)
- [~] Test import with valid/invalid data (partial)
- [~] Test supplier link/unlink (basic tested)
- [~] Test all modals (open, close, submit, error) (basic tested)
- [~] Test all filters and search (basic tested)

---

## 3. Clients (`invoice-clients.php`)

**Purpose:**
Manage all clients (companies and customers).

### Features & Detailed Checklist

#### 3.1. Tabbed Sections
- [x] Tabs for: Private (customers), Business (companies)
- [x] Each tab loads relevant data (filtered by type)
- [x] Highlight active tab, update content on switch

#### 3.2. Search & Filter
- [x] Search input (by name, ID)
- [x] Filter by type, pagination controls

#### 3.3. Client List/Table
- [x] Show: ID, Name, Email, Phone, Last Invoice Date, Outstanding Balance, Total Invoices
- [x] Actions: View, Edit, Delete

#### 3.4. Add/Edit/Delete Client
- [x] “Add Client” button (opens modal in add mode)
- [x] Edit button (opens modal in edit mode, autofills data)
- [x] Delete button (confirms, then calls API)
- [x] Validate all fields before submit
- [x] Show errors in ResponseModal

#### 3.5. Client Details
- [x] Click client to open details modal
- [x] Show: Basic Info, Address, Contact, Invoices
- [x] Tabs for: Basic, Address, Contact, Invoices

### Modals Needed
- [x] Company Modal (add/edit, tabbed)
- [x] Customer Modal (add/edit, tabbed)
- [x] ResponseModal, LoadingModal

### API Endpoints
- [x] `client-api.php?action=list_clients`
- [x] `client-api.php?action=get_client_details`
- [x] `client-api.php?action=create_client`
- [x] `client-api.php?action=update_client`
- [x] `client-api.php?action=delete_client`

**API Contract:**
- [x] All endpoints return `{ success, message, data, error_code }`
- [~] Validate all input, handle missing/invalid data (needs review for all edge cases)

### Controller Logic
- [x] ClientController: CRUD, filtering, details
  - [x] Validate all input
  - [x] Use transactions for add/edit/delete
  - [x] Log all actions and errors

### Edge Cases & Integration
- [~] Prevent deleting clients with active invoices (show error, needs backend enforcement)
- [x] Ensure all modals reset on open/close
- [~] Show user-friendly errors for validation failures (partial)

### Testing
- [~] Test all CRUD flows (add, edit, delete) (basic tested, edge cases may need more)
- [~] Test all modals (open, close, submit, error) (basic tested)
- [~] Test all filters and search (basic tested)

---

## 4. Recurring Invoice & Payment Terms Setup

**Purpose:**
Manage recurring invoice configurations and default payment terms.

### Features & Detailed Checklist

#### 4.1. Recurring Invoice Config
- [~] List, add, edit, delete recurring invoice configurations (UI present, backend logic may need review)
- [~] Configure frequency (daily, weekly, monthly, yearly) (UI present, needs backend support)
- [~] Set start and end dates (UI present, needs backend support)
- [~] Define default payment terms (UI present, needs backend support)

#### 4.2. Default Payment Terms
- [~] Define default payment terms for new invoices (UI present, needs backend support)
- [~] Set default due date for recurring invoices (UI present, needs backend support)
- [~] Ensure these settings are accessible in document creation (partial)

### Modals Needed
- [~] Recurring Invoice Config Modal (UI present, needs backend)
- [~] Default Payment Terms Modal (UI present, needs backend)
- [x] ResponseModal, LoadingModal

### API Endpoints
- [~] `setup-api.php?action=getRecurringConfigs` / `addRecurringConfig` / `updateRecurringConfig` / `deleteRecurringConfig` (partial)
- [~] `setup-api.php?action=getDefaultPaymentTerms` / `updateDefaultPaymentTerms` (partial)

**API Contract:**
- [~] All endpoints return `{ success, message, data, error_code }` (partial)
- [~] Validate all input, handle missing/invalid data (needs review for all edge cases)

### Controller Logic
- [~] RecurringConfigController: CRUD, configuration (partial)
  - [~] Validate all input (partial)
  - [~] Use transactions for add/edit/delete (partial)
  - [~] Log all actions and errors (partial)
- [~] PaymentTermController: Manage default payment terms (partial)
  - [~] Validate all input (partial)
  - [~] Use transactions for update (partial)
  - [~] Log all actions and errors (partial)

### Edge Cases & Integration
- [~] Prevent deleting configs/terms that are in use (show error, needs backend enforcement)
- [x] Ensure all modals reset on open/close
- [~] Show user-friendly errors for validation failures (partial)

### Testing
- [~] Test all CRUD flows for recurring configs (partial)
- [~] Test default payment terms update (partial)
- [~] Test all modals (open, close, submit, error) (partial)

---

## 5. Documents (rename `invoices.php` → `documents.php`)

**Purpose:**
List, search, and manage all documents: invoices, quotations, recurring, vehicle docs, credit notes, refunds.

### Features & Detailed Checklist

#### 4.1. Tabbed Sections
- [x] Tabs for: Invoices, Recurring, Quotations, Vehicle Quotations, Vehicle Invoices
- [x] Each tab loads relevant data (filtered by type)
- [x] Highlight active tab, update content on switch

#### 4.2. Search & Filter
- [x] Search input (by invoice number, client)
- [x] Filter by date, client, status
- [x] Pagination controls

#### 4.3. Document List/Table
- [x] Show: Invoice #, Client, Date Created, Last Modified, Status, Total, Due Date, Actions
- [x] Actions: View, Edit, Delete, Finalize, Approve, Email, Send Reminder

#### 4.4. Add/Edit/Delete Document
- [x] “Create Document” button (opens Document Modal in add mode)
- [x] Edit button (opens modal in edit mode, autofills data)
- [x] Delete button (confirms, then calls API)
- [x] Validate all fields before submit
- [x] Show errors in ResponseModal

#### 4.5. Document Details
- [x] Click document to open details modal
- [x] Show: All line items, client info, totals, notes, status
- [x] Tabs for: Items, Client, Notes, History

#### 4.6. PDF Generation
- [x] “Preview/Download PDF” button (calls `generate_invoice_pdf.php`)
- [x] Show loading and error states

### Modals Needed
- [x] Document Modal (create/edit, all types)
- [x] ResponseModal, LoadingModal

### API Endpoints
- [x] `document_modal.php?action=list_documents` (with filters)
- [x] `document_modal.php?action=fetch_document`
- [x] `document_modal.php?action=save_document`
- [x] `document_modal.php?action=delete_document`
- [x] `document_modal.php?action=change_status`
- [x] (PDF generation: `generate_invoice_pdf.php`)

**API Contract:**
- [x] All endpoints return `{ success, message, data, error_code }`
- [~] Validate all input, handle missing/invalid data (needs review for all edge cases)

### Controller Logic
- [x] **Rename `DocumentController` → `DocumentsController`**
- [x] All document types handled by this controller
  - [x] Validate all input
  - [x] Use transactions for add/edit/delete
  - [x] Log all actions and errors

### Edge Cases & Integration
- [~] Prevent deleting documents with payments (show error, needs backend enforcement)
- [x] Ensure all modals reset on open/close
- [~] Show user-friendly errors for validation failures (partial)

### Testing
- [~] Test all CRUD flows (add, edit, delete) (basic tested, edge cases may need more)
- [~] Test all modals (open, close, submit, error) (basic tested)
- [~] Test all filters and search (basic tested)
- [~] Test PDF generation (basic tested)

---

## 6. Payments (`invoice-payments.php`)

**Purpose:**
Manage payments, credit notes, refunds.

### Features & Detailed Checklist

#### 5.1. Tabbed Sections
- [x] Tabs for: Payments, Credit Notes, Refunds
- [x] Each tab loads relevant data (filtered by type)
- [x] Highlight active tab, update content on switch

#### 5.2. Search & Filter
- [x] Search input (by client, reference)
- [x] Filter by date, type, status
- [x] Pagination controls

#### 5.3. Payment List/Table
- [x] Show: Payment #, Client, Date, Amount, Method, Status, Reference, Actions
- [x] Actions: View, Edit, Delete

#### 5.4. Add/Edit/Delete Payment/Credit/Refund
- [x] “Add Payment” button (opens Payment Modal)
- [x] “Add Credit Note” button (opens Credit Note Modal)
- [x] “Add Refund” button (opens Refund Modal)
- [x] Edit button (opens modal in edit mode, autofills data)
- [x] Delete button (confirms, then calls API)
- [x] Validate all fields before submit
- [x] Show errors in ResponseModal

#### 5.5. Payment/Credit/Refund Details
- [x] Click row to open details modal
- [x] Show: All info, related document, notes

### Modals Needed
- [x] Payment Modal
- [x] Credit Note Modal
- [x] Refund Modal
- [x] ResponseModal, LoadingModal

### API Endpoints
- [x] `payment-api.php?action=list_payments`
- [x] `payment-api.php?action=add_payment`
- [x] `payment-api.php?action=add_credit_note`
- [x] `payment-api.php?action=add_refund`
- [x] `payment-api.php?action=get_payment_details`

**API Contract:**
- [x] All endpoints return `{ success, message, data, error_code }`
- [~] Validate all input, handle missing/invalid data (needs review for all edge cases)

### Controller Logic
- [x] PaymentController: CRUD, filtering, details
  - [x] Validate all input
  - [x] Use transactions for add/edit/delete
  - [x] Log all actions and errors

### Edge Cases & Integration
- [~] Prevent deleting payments linked to finalized documents (show error, needs backend enforcement)
- [x] Ensure all modals reset on open/close
- [~] Show user-friendly errors for validation failures (partial)

### Testing
- [~] Test all CRUD flows (add, edit, delete) (basic tested, edge cases may need more)
- [~] Test all modals (open, close, submit, error) (basic tested)
- [~] Test all filters and search (basic tested)

---

## 7. Sales Reps (`sales-reps.php`)

**Purpose:**
Manage sales representatives, targets, performance.

### Features & Detailed Checklist

#### 6.1. Sales Rep List/Table
- [~] Show: Name, Email, Phone, Performance Stats, Actions (UI present, backend logic may need review)
- [~] Actions: View, Edit, Delete, Assign Target (UI present, needs backend integration)

#### 6.2. Add/Edit/Delete Sales Rep
- [~] “Add Sales Rep” button (opens Sales Rep Modal) (UI present, needs backend)
- [~] Edit button (opens modal in edit mode, autofills data) (UI present, needs backend)
- [~] Delete button (confirms, then calls API) (UI present, needs backend)
- [~] Validate all fields before submit (partial)
- [~] Show errors in ResponseModal (partial)

#### 6.3. Sales Rep Details
- [~] Click row to open details modal (UI present, needs backend)
- [~] Show: Basic Info, Performance, Recent Deals, Targets (UI present, needs backend)
- [~] Tabs for: Info, Performance, Deals, Targets (UI present, needs backend)

#### 6.4. Assign/View Sales Targets
- [~] “Assign Target” button (opens Sales Target Modal) (UI present, needs backend)
- [~] View all targets for rep (UI present, needs backend)
- [~] Edit/delete targets (UI present, needs backend)

### Modals Needed
- [x] Sales Rep Modal (add/edit)
- [x] Sales Target Modal (assign/edit)
- [x] ResponseModal, LoadingModal

### API Endpoints
- [~] `sales-reps-api.php?action=list` (partial)
- [~] `sales-reps-api.php?action=get` (partial)
- [~] `sales-reps-api.php?action=add` (partial)
- [~] `sales-reps-api.php?action=update` (partial)
- [~] `sales-reps-api.php?action=delete` (partial)
- [~] `sales-reps-api.php?action=get_targets` (partial)
- [~] `sales-reps-api.php?action=assign_target` (partial)

**API Contract:**
- [~] All endpoints return `{ success, message, data, error_code }` (partial)
- [~] Validate all input, handle missing/invalid data (needs review for all edge cases)

### Controller Logic
- [~] SalesController: CRUD, targets, performance (partial)
  - [~] Validate all input (partial)
  - [~] Use transactions for add/edit/delete (partial)
  - [~] Log all actions and errors (partial)
- [~] Reuse existing logic for employee lookup (partial)

### Edge Cases & Integration
- [~] Prevent deleting reps with active targets (show error, needs backend enforcement)
- [x] Ensure all modals reset on open/close
- [~] Show user-friendly errors for validation failures (partial)

### Testing
- [~] Test all CRUD flows (add, edit, delete, assign target) (partial)
- [~] Test all modals (open, close, submit, error) (partial)
- [~] Test all filters and search (partial)

---

## 8. Dashboard (`invoice-dashboard.php`)

**Purpose:**
Show high-level analytics, recent invoices, recurring invoices, quick actions.

### Features & Detailed Checklist

#### 1.1. Dashboard Cards
- [~] Design cards for: Total Invoices, Total Revenue, Unpaid Invoices, Pending Payments, Expenses, Taxes Due, Recurring Invoices (UI present, backend logic may need review)
- [~] Fetch data from `dashboard-api.php?action=get_dashboard_cards` (partial)
- [~] Show loading state while fetching (partial)
- [~] Handle API errors (show ResponseModal) (partial)
- [~] Update cards in real-time if data changes (optional: use polling or WebSocket) (not implemented)

#### 1.2. Recent Invoices Table
- [~] Table columns: Invoice #, Client, Date, Status, Total, Due Date, Actions (UI present, backend logic may need review)
- [~] Fetch from `dashboard-api.php?action=get_recent_invoices` (partial)
- [~] Actions: View, Edit (open Document Modal), Quick Status Change, Send Reminder (partial)
- [~] Show loading and error states (partial)
- [~] Paginate or limit to top N (e.g., 5-10) (partial)

#### 1.3. Recurring Invoices Table
- [~] Table columns: Invoice #, Client, Start Date, Next Invoice, Interval, Actions (UI present, backend logic may need review)
- [~] Fetch from `dashboard-api.php?action=get_recurring_invoices` (partial)
- [~] Actions: View, Edit, Pause/Resume, Delete (partial)
- [~] Show loading and error states (partial)

#### 1.4. Invoice Analytics Chart
- [~] Chart.js integration (bar/line/pie) (UI present, backend logic may need review)
- [~] Fetch data from `dashboard-api.php?action=get_invoice_chart_data` (partial)
- [~] Allow user to select date range, chart type, number of months (partial)
- [~] Show loading and error states (partial)

#### 1.5. Quick Actions
- [x] “Create New Invoice” button (opens Document Modal in create mode)
- [~] “Send Payment Reminder” (opens modal or triggers API) (partial)
- [~] “Add Expense” (optional: opens expense modal) (not implemented)

### Modals Needed
- [x] Document Modal (for create/edit invoice)
  - [x] Open in correct mode (add/edit)
  - [x] Autofill if editing
  - [x] Reset on open/close
  - [x] Validate all fields before submit
  - [x] Show errors in ResponseModal
- [x] ResponseModal (for all errors/success)
- [x] LoadingModal (for all async actions)

### API Endpoints
- [~] `dashboard-api.php?action=get_dashboard_cards` (partial)
- [~] `dashboard-api.php?action=get_recent_invoices` (partial)
- [~] `dashboard-api.php?action=get_recurring_invoices` (partial)
- [~] `dashboard-api.php?action=get_invoice_chart_data` (partial)
- [x] `document_modal.php` for document CRUD

**API Contract:**
- [~] All endpoints return `{ success, message, data, error_code }` (partial)
- [~] Handle missing/invalid session, DB errors, permission errors (partial)
- [~] Return user-friendly error messages (partial)

### Controller Logic
- [~] DashboardController: Aggregate stats, recent/recurring invoices, analytics (partial)
  - [~] Validate user/account/session (partial)
  - [~] Use efficient queries for dashboard stats (partial)
  - [~] Handle edge cases (no data, partial data) (partial)
  - [~] Log errors and actions (partial)
- [x] DocumentController: For document CRUD (reuse for modal actions)

### Edge Cases & Integration
- [~] Handle empty states (no invoices, no recurring, no data) (partial)
- [~] Handle permission errors (show appropriate message) (partial)
- [~] Ensure all quick actions work from dashboard (not just from documents screen) (partial)
- [x] Ensure all modals reset on open/close

### Testing
- [~] Test all API endpoints with/without data (partial)
- [~] Test all modals (open, close, submit, error) (partial)
- [~] Test all quick actions (partial)
- [~] Test loading/error/empty states (partial)

---

## 9. Reports (`invoice-reports.php`)

**Purpose:**
Generate and export sales, stock, and financial reports.

### Features & Detailed Checklist

#### 8.1. Report Selection
- [~] Select report type (by customer, product, date, invoice) (UI present, backend logic may need review)
- [~] Filter by date, status, etc. (partial)
- [~] Show report results in table/chart (partial)

#### 8.2. Export
- [~] Export to CSV/PDF (partial)
- [~] Show export modal (optional) (partial)
- [~] Show errors in ResponseModal (partial)

### Modals Needed
- [~] Export Modal (optional) (partial)
- [x] ResponseModal, LoadingModal

### API Endpoints
- [~] `reports-api.php?action=sales_by_customer` (partial)
- [~] `reports-api.php?action=sales_by_product` (partial)
- [~] `reports-api.php?action=sales_by_date` (partial)
- [~] `reports-api.php?action=sales_by_invoice` (partial)

**API Contract:**
- [~] All endpoints return `{ success, message, data, error_code }` (partial)
- [~] Validate all input, handle missing/invalid data (needs review for all edge cases)

### Controller Logic
- [~] ReportsController (new or extend existing) (partial)
  - [~] Validate all input (partial)
  - [~] Use efficient queries for reporting (partial)
  - [~] Log all actions and errors (partial)

### Edge Cases & Integration
- [~] Handle empty/large result sets (partial)
- [x] Ensure all modals reset on open/close
- [~] Show user-friendly errors for validation failures (partial)

### Testing
- [~] Test all report types and filters (partial)
- [~] Test export (CSV/PDF) (partial)
- [~] Test all modals (open, close, submit, error) (partial)

---

## 10. General/Naming/Structural Improvements

- [~] Rename `invoices.php` → `documents.php` (and all related references) (partial)
- [~] Rename `DocumentController` → `DocumentsController` (partial)
- [~] Ensure all controllers are modular and reusable (e.g., SalesController for both sales reps and targets) (partial)
- [~] Centralize modal logic and error handling (partial)
- [~] All API endpoints should be RESTful and parameterized (partial)

---

## 11. Testing & Integration

- [~] For each screen, test all flows: open modal, fill form, submit, backend, DB, UI update (partial)
- [~] Test all error/success cases, edge cases, and permissions (partial)
- [x] Ensure all modals/forms reset on open/close
- [~] Confirm all API endpoints are hit and return expected data (partial)
- [~] Confirm all new stock/supplier logic is reflected in UI and DB (partial)

---

## 12. Documentation

- [~] Update `planning.md` and README/docs for all new logic, endpoints, and UI flows (partial)
- [~] Document which controllers are reused for which screens (partial)

---

**Use this order as your implementation and testing roadmap. Do not move to the next phase until the previous is robust and tested.**
