<!-- Modal Document -->
<div class="modal-document-overlay" id="document-modal" data-mode="create" style="display:none;">
    <!-- Client Details Panel (Left Side) -->
    <div class="client-panel" id="client-panel">
        <div class="client-panel-header">
            <h3>Client Details</h3>
        </div>
        <div class="client-panel-content">
            <div class="form-group">
                <label for="client-name">Client Name</label>
                <div class="search-container search-client-container">
                    <input type="text" placeholder="Search Client Name" id="client-name" autocomplete="off">
                    <div class="search-client-dropdown" id="search-results-client"></div>
                </div>
            </div>
            <div class="form-group">
                <label for="client-email">Email Address</label>
                <input type="email" id="client-email" placeholder="client@example.com">
            </div>
            <div class="form-group">
                <label for="client-phone">Phone Number</label>
                <input type="text" placeholder="Client Phone" id="client-phone" autocomplete="off">
            </div>
            <div class="form-group">
                <label for="client-vat-number">VAT Number</label>
                <input type="text" placeholder="Client VAT Number" id="client-vat-number" autocomplete="off">
            </div>
            <div class="form-group">
                <label for="client-reg-number">Registration Number</label>
                <input type="text" placeholder="Client Registration Number" id="client-reg-number" autocomplete="off">
            </div>
            <div class="form-group">
                <label for="client-address-1">Address Line 1</label>
                <input type="text" placeholder="Address Line 1" id="client-address-1">
            </div>
            <div class="form-group">
                <label for="client-address-2">Address Line 2</label>
                <input type="text" placeholder="Address Line 2" id="client-address-2">
            </div>
        </div>
    </div>

    <!-- Main Modal Container -->
    <div class="modal-container">
        <header class="modal-header">
            <div class="header-left">
                <div class="document-type-indicator" data-doc-type="invoice">
                    <i data-lucide="file-text"></i>
                </div>
                <div class="document-title">
                    <h2 id="documentTitle">New Document</h2>
                    <span id="documentStatus" class="status-badge status-draft">Draft</span>
                </div>
                <div id="autosaveIndicator" class="autosave-indicator" style="display: none;">
                    <i data-lucide="loader-circle" class="spin"></i>
                    <span>Saving...</span>
                </div>
            </div>
            <div class="header-right">
                <button class="btn btn-secondary btn-icon" title="View History">
                    <i data-lucide="history"></i>
                </button>
                <button class="btn-icon-only" id="modal-document-close-btn" title="Close Modal">
                    <i data-lucide="x"></i>
                </button>
            </div>
        </header>

        <main class="modal-body">
            <!-- Hidden fields for JavaScript -->
            <input type="hidden" id="client-id">
            <input type="hidden" id="issuer-id">
            <input type="hidden" id="document-id">
            <input type="hidden" id="related-document-id">
            <input type="hidden" id="document-status" value="Unpaid">

            <!-- Main Content Area -->
            <div class="modal-content-wrapper">
                <!-- Company Logo Section (Row 1, Col 1) -->
                <div class="logo-section" id="logo-section">
                    <div class="logo-card">
                        <div class="logo-content">
                            <div class="logo-preview" id="logo-preview">
                                <img id="company-logo" src="img/default-logo.png" alt="Company Logo" style="display: none;">
                                <div class="logo-placeholder" id="logo-placeholder">
                                    <i data-lucide="image"></i>
                                    <span>No logo uploaded</span>
                                </div>
                            </div>
                            <input type="file" id="logo-file-input" accept="image/*" style="display: none;">
                        </div>
                    </div>
                </div>

                <!-- Document Info Section (Row 1, Col 2) -->
                <div class="modal-main-content">
                    <section class="card">
                        <div class="form-grid-4">
                            <div class="form-group">
                                <label for="document-type">Document Type</label>
                                <select id="document-type">
                                    <option value="quotation">Quotation</option>
                                    <option value="vehicle-quotation">Vehicle Quotation</option>
                                    <option value="standard-invoice">Invoice</option>
                                    <option value="vehicle-invoice">Vehicle Invoice</option>
                                    <option value="recurring-invoice">Recurring Invoice</option>
                                    <option value="credit-note">Credit Note</option>
                                    <option value="refund">Refund</option>
                                    <option value="pro-forma">Pro Forma</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="document-number">Document Number</label>
                                <input type="text" id="document-number" placeholder="INV-2025-001" disabled>
                            </div>
                            <div class="form-group">
                                <label for="current-date">Issue Date</label>
                                <input type="date" id="current-date" value="2025-08-02" disabled>
                            </div>
                            <div class="form-group" data-show-on="standard-invoice,recurring-invoice">
                                <label for="pay-in-days">Payment Terms (Days)</label>
                                <select id="pay-in-days">
                                    <option value="15">15</option>
                                    <option value="30" selected>30</option>
                                    <option value="45">45</option>
                                </select>
                            </div>
                            <div class="form-group">
                                 <label for="purchase-order-number">Purchase Order #</label>
                                 <input type="text" placeholder="PO Number" id="purchase-order-number">
                            </div>
                            <div class="form-group">
                                <label for="salesperson">Salesperson</label>
                                <div class="search-container search-salesperson-container">
                                    <input type="text" placeholder="Search Salesperson" id="salesperson" autocomplete="off">
                                    <div class="search-salesperson-dropdown" id="search-results-salesperson"></div>
                                </div>
                            </div>
                             <div class="form-group" id="related-document-info" data-show-on="credit-note,refund" style="display: none;">
                                <label for="related-document-number-display">Related Invoice</label>
                                <input type="text" id="related-document-number-display" placeholder="Click to select invoice" readonly>
                            </div>
                        </div>
                    </section>

                    <section class="card recurring-section" data-show-on="recurring-invoice" style="display: none;">
                        <div class="card-header"><h4>Recurring Details</h4></div>
                        <div class="form-grid-3">
                            <div class="form-group">
                                <label for="recurring-frequency">Frequency</label>
                                <select id="recurring-frequency">
                                    <option value="monthly" selected>Monthly</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="bi-weekly">Bi-Weekly</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="recurring-start-date">Start Date</label>
                                <input type="date" id="recurring-start-date">
                            </div>
                            <div class="form-group">
                                <label for="recurring-end-date">End Date (Optional)</label>
                                <input type="date" id="recurring-end-date">
                            </div>
                        </div>
                    </section>
                </div>

                <!-- Issuer Info Section (Row 1, Col 3) -->
                <div class="issuer-info-section" id="issuer-info-section">
                    <h4>Issuer Details</h4>
                    <div class="issuer-info-content">
                        <strong><span id="issuer-name-display">Your Company</span></strong><br>
                        <span id="issuer-address-display">123 Main Street, Alberton</span><br>
                        <span id="issuer-email-display">contact@yourcompany.com</span><br>
                        <span id="issuer-phone-display">011 555 1234</span><br>
                        <span id="issuer-vat-display">VAT: 4001234567</span>
                    </div>
                </div>

                <!-- Summary Section (Row 2, Col 3) -->
                <div class="line-items-summary">
                    <aside class="summary-card">
                        <h4>Summary</h4>
                        <div class="summary-row"><span>Subtotal</span><span id="subtotal">R0.00</span></div>
                        <div class="summary-row"><span>Tax</span><span id="tax-total">R0.00</span></div>
                        <div class="summary-row grand-total"><span>Grand Total</span><span id="final-total">R0.00</span></div>
                    </aside>
                </div>

                <!-- Line Items Section (Row 2, Col 1-2) -->
                <section class="line-items-section" id="document-table" data-show-on="quotation,invoice,standard-invoice,recurring-invoice,pro-forma">
                    <div class="line-items-content">
                        <div class="line-items-toolbar">
                            <h4>Line Items</h4>
                            <div class="toolbar-actions">
                                <button class="btn btn-secondary" id="add-item-btn"><i data-lucide="plus"></i> Add Item</button>
                                <button class="btn btn-secondary" id="add-discount-btn"><i data-lucide="plus"></i> Add Discount</button>
                            </div>
                        </div>
                        <table class="line-items-table">
                            <thead>
                                <tr>
                                    <th>Qty</th>
                                    <th>Item Code</th>
                                    <th>Description</th>
                                    <th>Unit Price</th>
                                    <th>VAT</th>
                                    <th>Discount</th>
                                    <th>Line Total</th>
                                    <th style="display: none;">Product ID</th>
                                </tr>
                            </thead>
                            <tbody id="document-rows">
                                <!-- Dynamic content will be inserted here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </section>

                <section class="line-items-section" id="credit-note-table" data-show-on="credit-note" style="display: none;">
                    <div class="line-items-content">
                        <div class="line-items-toolbar"><h4>Credit Items</h4></div>
                        <table class="line-items-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Reason / Product</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="credit-note-rows">
                                <!-- Dynamic content will be inserted here by JavaScript -->
                            </tbody>
                        </table>
                        <div class="line-items-footer">
                            <button class="btn btn-secondary" id="add-credit-note-item-btn"><i data-lucide="plus"></i> Add Credit Item</button>
                        </div>
                    </div>
                </section>

                <!-- Refund Items Section -->
                <section class="line-items-section" id="refund-table" data-show-on="refund" style="display: none;">
                    <div class="line-items-content">
                        <div class="line-items-toolbar"><h4>Refund Items</h4></div>
                        <table class="line-items-table">
                            <thead>
                                <tr>
                                    <th>Type</th>
                                    <th>Original Product</th>
                                    <th>Amount</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="refund-rows">
                                <!-- Dynamic content will be inserted here by JavaScript -->
                            </tbody>
                        </table>
                        <div class="line-items-footer">
                            <button class="btn btn-secondary" id="add-refund-item-btn"><i data-lucide="plus"></i> Add Refund Item</button>
                        </div>
                    </div>
                </section>

                <div class="vehicle-section-container" data-show-on="vehicle-invoice,vehicle-quotation" style="display: none;">
                    <section class="card vehicle-section">
                        <div class="card-header"><h4>Vehicle Details</h4></div>
                        <table class="line-items-table">
                            <thead>
                                <tr>
                                    <th>Vehicle</th>
                                    <th>VIN</th>
                                    <th>Price</th>
                                    <th>VAT</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr class="vehicle-row">
                                    <td><input type="text" placeholder="Vehicle Model" id="vehicle-model"></td>
                                    <td><input type="text" placeholder="VIN" id="vehicle-vin"></td>
                                    <td><input type="text" placeholder="R0.00" id="vehicle-price"></td>
                                    <td><select id="vehicle-tax"><option value="0">[None]</option><option value="15">15%</option></select></td>
                                    <td id="vehicle-total">R0.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </section>
                    <section class="line-items-section vehicle-extras-section" id="vehicle-parts-table" style="margin-top: 1.5rem;">
                         <div class="line-items-content">
                            <div class="line-items-toolbar">
                                <h4>Extras / Parts</h4>
                                <div class="toolbar-actions">
                                    <button class="btn btn-secondary" id="add-vehicle-part-btn"><i data-lucide="plus"></i> Add Part</button>
                                </div>
                            </div>
                            <table class="line-items-table">
                                 <thead>
                                     <tr>
                                         <th>Qty</th>
                                         <th>Item Code</th>
                                         <th>Description</th>
                                         <th>Unit Price</th>
                                         <th>VAT</th>
                                         <th>Discount</th>
                                         <th>Line Total</th>
                                         <th>Actions</th>
                                         <th style="display: none;">Product ID</th>
                                     </tr>
                                 </thead>
                                 <tbody id="vehicle-parts-rows">
                                     <!-- Dynamic content will be inserted here by JavaScript -->
                                 </tbody>
                             </table>
                        </div>
                    </section>
                </div>
            </div>
        </main>

        <footer class="modal-footer">
            <div class="footer-left">
                <section class="card notes-footer">
                    <div class="card-header"><h4>Notes</h4></div>
                    <div class="form-grid-3">
                        <div class="form-group"><textarea placeholder="Public Note (visible to client)" id="public-note" rows="3"></textarea></div>
                        <div class="form-group"><textarea placeholder="Private Note (internal use only)" id="private-note" rows="3"></textarea></div>
                        <div class="form-group"><textarea placeholder="Foot Note (e.g., banking details)" id="foot-note" rows="3"></textarea></div>
                    </div>
                </section>
            </div>
            
            <div class="footer-right">
                <aside class="actions-card">
                    <h4>Actions</h4>
                    <button class="btn btn-full" id="clear-document-btn"><i data-lucide="trash-2"></i> Clear</button>
                    <button class="btn btn-full" id="preview-pdf-btn"><i data-lucide="eye"></i> Preview</button>
                    <button class="btn btn-full" id="save-document-btn"><i data-lucide="save"></i> Save Draft</button>
                    <button class="btn btn-full btn-primary" id="create-document-btn"><i data-lucide="send"></i> Create Document</button>
                </aside>
            </div>
        </footer>
    </div>

    <!-- Live Preview Section (Right Side) -->
    <div id="live-preview-modal" class="preview-container">
        <div class="preview-header">
            <h3>Live Preview</h3>
            <button class="btn-icon-only" id="toggle-preview" title="Toggle Preview">
                <i data-lucide="eye-off"></i>
            </button>
        </div>
        <div class="preview-body">
            <div class="a4-preview">
                <div class="preview-content">
                    <div class="preview-header-section">
                        <div class="preview-company">
                            <strong>Your Company</strong><br>
                            123 Main Street, Alberton<br>
                            contact@yourcompany.com
                        </div>
                        <div class="preview-document-info">
                            <h2 id="preview-title">INVOICE</h2>
                            <div id="preview-number">INV-2025-001</div>
                            <div id="preview-date">2025-08-02</div>
                        </div>
                    </div>
                    <div class="preview-client-section">
                        <strong>Bill To:</strong><br>
                        <div id="preview-client-name">Client Name</div>
                        <div id="preview-client-address">Client Address</div>
                    </div>
                    <div class="preview-items">
                        <table class="preview-table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Qty</th>
                                    <th>Price</th>
                                    <th>Total</th>
                                </tr>
                            </thead>
                            <tbody id="preview-items-body" class="preview-items-body">
                                <tr>
                                    <td>Sample Item</td>
                                    <td>1</td>
                                    <td>R100.00</td>
                                    <td>R100.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="preview-totals">
                        <div class="total-row"><span>Subtotal:</span><span id="preview-subtotal">R0.00</span></div>
                        <div class="total-row"><span>Tax:</span><span id="preview-tax">R0.00</span></div>
                        <div class="total-row grand"><span>Total:</span><span id="preview-total">R0.00</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
