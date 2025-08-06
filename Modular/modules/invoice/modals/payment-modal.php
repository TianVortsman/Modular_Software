<!-- Payment Modal -->
<div class="modal-payment-overlay" id="payment-modal" data-mode="create" style="display:none;">
    <!-- Client Details Panel (Left Side) -->
    <div class="client-panel" id="payment-client-panel">
        <div class="client-panel-header">
            <h3>Client Details</h3>
        </div>
        <div class="client-panel-content">
            <div class="form-group">
                <label for="payment-client-name">Client Name</label>
                <input type="text" id="payment-client-name" readonly>
            </div>
            <div class="form-group">
                <label for="payment-client-email">Email Address</label>
                <input type="email" id="payment-client-email" readonly>
            </div>
            <div class="form-group">
                <label for="payment-client-phone">Phone Number</label>
                <input type="text" id="payment-client-phone" readonly>
            </div>
            <div class="form-group">
                <label for="payment-client-vat-number">VAT Number</label>
                <input type="text" id="payment-client-vat-number" readonly>
            </div>
        </div>
    </div>

    <!-- Main Modal Container -->
    <div class="modal-container">
        <header class="modal-header">
            <div class="header-left">
                <div class="document-type-indicator" data-doc-type="payment">
                    <i data-lucide="credit-card"></i>
                </div>
                <div class="document-title">
                    <h2 id="paymentTitle">Record Payment</h2>
                    <span id="paymentStatus" class="status-badge status-pending">Pending</span>
                </div>
                <div id="paymentAutosaveIndicator" class="autosave-indicator" style="display: none;">
                    <i data-lucide="loader-circle" class="spin"></i>
                    <span>Saving...</span>
                </div>
            </div>
            <div class="header-right">
                <button class="btn btn-secondary btn-icon" title="Payment History">
                    <i data-lucide="history"></i>
                </button>
                <button class="btn-icon-only" id="modal-payment-close-btn" title="Close Modal">
                    <i data-lucide="x"></i>
                </button>
            </div>
        </header>

        <main class="modal-body">
            <!-- Hidden fields for JavaScript -->
            <input type="hidden" id="payment-id">
            <input type="hidden" id="payment-document-id">
            <input type="hidden" id="payment-client-id">
            <input type="hidden" id="payment-issuer-id">

            <!-- Main Content Area -->
            <div class="modal-content-wrapper">
                <!-- Document Info Section (Row 1, Col 1) -->
                <div class="modal-main-content">
                    <!-- Client Search Section (shown when no document is pre-selected) -->
                    <section class="card" id="client-search-section" style="display: none;">
                        <div class="card-header">
                            <h4>Client Information</h4>
                        </div>
                        <div class="form-group">
                            <label for="payment-client-search">Search Client</label>
                            <input type="text" id="payment-client-search" placeholder="Start typing client name...">
                            <div id="search-results-client" class="search-results-client"></div>
                            <small class="form-help">Search for a client to record a payment for.</small>
                        </div>
                    </section>

                    <!-- Invoice Selection Section (shown when no document is pre-selected) -->
                    <section class="card" id="invoice-selection-section" style="display: none;">
                        <div class="card-header">
                            <h4>Link Payment to Invoice</h4>
                        </div>
                        <div class="form-group">
                            <label for="invoice-select">Select Invoice (Optional)</label>
                            <select id="invoice-select">
                                <option value="">Select an invoice to link payment to (optional)</option>
                            </select>
                            <small class="form-help">You can record a payment without linking it to an invoice, or select an invoice to automatically link the payment.</small>
                        </div>
                    </section>

                    <section class="card" id="document-info-section">
                        <div class="card-header">
                            <h4>Document Information</h4>
                        </div>
                        <div class="form-grid-3">
                            <div class="form-group">
                                <label for="payment-document-number">Document Number</label>
                                <input type="text" id="payment-document-number" readonly>
                            </div>
                            <div class="form-group">
                                <label for="payment-document-type">Document Type</label>
                                <input type="text" id="payment-document-type" readonly>
                            </div>
                            <div class="form-group">
                                <label for="payment-document-date">Document Date</label>
                                <input type="date" id="payment-document-date" readonly>
                            </div>
                        </div>
                    </section>

                    <section class="card">
                        <div class="card-header">
                            <h4>Payment Details</h4>
                        </div>
                        <div class="form-grid-4">
                            <div class="form-group">
                                <label for="payment-amount">Payment Amount</label>
                                <input type="text" id="payment-amount" placeholder="R0.00" required>
                            </div>
                            <div class="form-group">
                                <label for="payment-date">Payment Date</label>
                                <input type="date" id="payment-date" required>
                            </div>
                            <div class="form-group">
                                <label for="payment-method">Payment Method</label>
                                <select id="payment-method" required>
                                    <option value="">Select Payment Method</option>
                                    <option value="CASH">Cash</option>
                                    <option value="BANK_TRANSFER">Bank Transfer</option>
                                    <option value="CREDIT_CARD">Credit Card</option>
                                    <option value="DEBIT_CARD">Debit Card</option>
                                    <option value="PAYPAL">PayPal</option>
                                    <option value="CHECK">Check</option>
                                    <option value="MONEY_ORDER">Money Order</option>
                                    <option value="EFT">Electronic Funds Transfer</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="payment-reference">Payment Reference</label>
                                <input type="text" id="payment-reference" placeholder="Reference Number">
                            </div>
                        </div>
                    </section>

                    <section class="card">
                        <div class="card-header">
                            <h4>Payment Allocation</h4>
                        </div>
                        <div class="form-group">
                            <label for="payment-allocation-type">Allocation Type</label>
                            <select id="payment-allocation-type" required>
                                <option value="full">Full Payment</option>
                                <option value="partial">Partial Payment</option>
                                <option value="overpayment">Overpayment</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="payment-notes">Payment Notes</label>
                            <textarea id="payment-notes" rows="3" placeholder="Additional notes about this payment"></textarea>
                        </div>
                    </section>
                </div>

                <!-- Document Summary Section (Row 1, Col 2) -->
                <div class="document-summary-section">
                    <aside class="summary-card">
                        <h4>Document Summary</h4>
                        <div class="summary-row">
                            <span>Document Total</span>
                            <span id="document-total">R0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Previously Paid</span>
                            <span id="previously-paid">R0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Balance Due</span>
                            <span id="balance-due">R0.00</span>
                        </div>
                        <div class="summary-row grand-total">
                            <span>Payment Amount</span>
                            <span id="payment-total">R0.00</span>
                        </div>
                        <div class="summary-row">
                            <span>Remaining Balance</span>
                            <span id="remaining-balance">R0.00</span>
                        </div>
                    </aside>
                </div>

                <!-- Payment History Section (Row 2, Col 1-2) -->
                <section class="payment-history-section">
                    <div class="payment-history-content">
                        <div class="payment-history-toolbar">
                            <h4>Payment History</h4>
                            <div class="toolbar-actions">
                                <button class="btn btn-secondary" id="refresh-payment-history-btn">
                                    <i data-lucide="refresh-cw"></i> Refresh
                                </button>
                            </div>
                        </div>
                        <table class="payment-history-table">
                            <thead>
                                <tr>
                                    <th>Date</th>
                                    <th>Amount</th>
                                    <th>Method</th>
                                    <th>Reference</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody id="payment-history-rows">
                                <!-- Dynamic content will be inserted here by JavaScript -->
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>
        </main>

        <footer class="modal-footer">
            <div class="footer-left">
                <section class="card payment-validation-footer">
                    <div class="card-header">
                        <h4>Payment Validation</h4>
                    </div>
                    <div class="validation-messages" id="payment-validation-messages">
                        <!-- Validation messages will be displayed here -->
                    </div>
                </section>
            </div>
            
            <div class="footer-right">
                <aside class="actions-card">
                    <h4>Actions</h4>
                    <button class="btn btn-full" id="clear-payment-btn">
                        <i data-lucide="trash-2"></i> Clear
                    </button>
                    <button class="btn btn-full" id="save-payment-draft-btn">
                        <i data-lucide="save"></i> Save Draft
                    </button>
                    <button class="btn btn-full btn-primary" id="record-payment-btn">
                        <i data-lucide="credit-card"></i> Record Payment
                    </button>
                </aside>
            </div>
        </footer>
    </div>

    <!-- Payment Preview Section (Right Side) -->
    <div id="payment-preview-modal" class="preview-container">
        <div class="preview-header">
            <h3>Payment Preview</h3>
            <button class="btn-icon-only" id="toggle-payment-preview" title="Toggle Preview">
                <i data-lucide="eye-off"></i>
            </button>
        </div>
        <div class="preview-body">
            <div class="a4-preview">
                <div class="preview-content">
                    <div class="preview-header-section">
                        <div class="preview-company">
                            <strong id="preview-company-name">Your Company</strong><br>
                            <span id="preview-company-address">123 Main Street, Alberton</span><br>
                            <span id="preview-company-email">contact@yourcompany.com</span>
                        </div>
                        <div class="preview-document-info">
                            <h2 id="preview-payment-title">PAYMENT RECEIPT</h2>
                            <div id="preview-payment-number">PAY-2025-001</div>
                            <div id="preview-payment-date">2025-08-02</div>
                        </div>
                    </div>
                    <div class="preview-client-section">
                        <strong>Received From:</strong><br>
                        <div id="preview-payment-client-name">Client Name</div>
                        <div id="preview-payment-client-address">Client Address</div>
                    </div>
                    <div class="preview-document-section">
                        <strong>For Document:</strong><br>
                        <div id="preview-payment-document-number">INV-2025-001</div>
                        <div id="preview-payment-document-date">2025-08-02</div>
                    </div>
                    <div class="preview-payment-details">
                        <table class="preview-table">
                            <thead>
                                <tr>
                                    <th>Description</th>
                                    <th>Amount</th>
                                </tr>
                            </thead>
                            <tbody id="preview-payment-details-body">
                                <tr>
                                    <td>Payment for Invoice INV-2025-001</td>
                                    <td>R100.00</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="preview-payment-summary">
                        <div class="total-row"><span>Payment Amount:</span><span id="preview-payment-amount">R100.00</span></div>
                        <div class="total-row"><span>Payment Method:</span><span id="preview-payment-method">Bank Transfer</span></div>
                        <div class="total-row"><span>Reference:</span><span id="preview-payment-reference">REF123456</span></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Payment History Modal -->
<div class="modal-payment-history-overlay" id="payment-history-modal" style="display:none;">
    <div class="modal-container">
        <header class="modal-header">
            <div class="header-left">
                <div class="document-type-indicator" data-doc-type="history">
                    <i data-lucide="history"></i>
                </div>
                <div class="document-title">
                    <h2>Payment History</h2>
                </div>
            </div>
            <div class="header-right">
                <button class="btn-icon-only" id="modal-payment-history-close-btn" title="Close Modal">
                    <i data-lucide="x"></i>
                </button>
            </div>
        </header>

        <main class="modal-body">
            <div class="payment-history-filters">
                <div class="form-grid-4">
                    <div class="form-group">
                        <label for="history-date-from">Date From</label>
                        <input type="date" id="history-date-from">
                    </div>
                    <div class="form-group">
                        <label for="history-date-to">Date To</label>
                        <input type="date" id="history-date-to">
                    </div>
                    <div class="form-group">
                        <label for="history-payment-method">Payment Method</label>
                        <select id="history-payment-method">
                            <option value="">All Methods</option>
                            <option value="CASH">Cash</option>
                            <option value="BANK_TRANSFER">Bank Transfer</option>
                            <option value="CREDIT_CARD">Credit Card</option>
                            <option value="DEBIT_CARD">Debit Card</option>
                            <option value="PAYPAL">PayPal</option>
                            <option value="CHECK">Check</option>
                            <option value="MONEY_ORDER">Money Order</option>
                            <option value="EFT">Electronic Funds Transfer</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="history-status">Status</label>
                        <select id="history-status">
                            <option value="">All Statuses</option>
                            <option value="completed">Completed</option>
                            <option value="pending">Pending</option>
                            <option value="failed">Failed</option>
                            <option value="cancelled">Cancelled</option>
                        </select>
                    </div>
                </div>
                <div class="filter-actions">
                    <button class="btn btn-secondary" id="apply-history-filters-btn">
                        <i data-lucide="filter"></i> Apply Filters
                    </button>
                    <button class="btn btn-secondary" id="clear-history-filters-btn">
                        <i data-lucide="x"></i> Clear Filters
                    </button>
                </div>
            </div>

            <div class="payment-history-table-container">
                <table class="payment-history-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Document</th>
                            <th>Client</th>
                            <th>Amount</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody id="full-payment-history-rows">
                        <!-- Dynamic content will be inserted here by JavaScript -->
                    </tbody>
                </table>
            </div>
        </main>
    </div>
</div>

<!-- Payment Confirmation Modal -->
<div class="modal-payment-confirmation-overlay" id="payment-confirmation-modal" style="display:none;">
    <div class="modal-container">
        <header class="modal-header">
            <div class="header-left">
                <div class="document-type-indicator" data-doc-type="confirmation">
                    <i data-lucide="check-circle"></i>
                </div>
                <div class="document-title">
                    <h2>Payment Confirmation</h2>
                </div>
            </div>
        </header>

        <main class="modal-body">
            <div class="confirmation-content">
                <div class="confirmation-icon">
                    <i data-lucide="check-circle" class="success-icon"></i>
                </div>
                <div class="confirmation-message">
                    <h3>Payment Recorded Successfully</h3>
                    <p id="confirmation-details">Payment details will be displayed here</p>
                </div>
                <div class="confirmation-actions">
                    <button class="btn btn-primary" id="print-payment-receipt-btn">
                        <i data-lucide="printer"></i> Print Receipt
                    </button>
                    <button class="btn btn-secondary" id="email-payment-receipt-btn">
                        <i data-lucide="mail"></i> Email Receipt
                    </button>
                    <button class="btn btn-secondary" id="close-confirmation-btn">
                        <i data-lucide="x"></i> Close
                    </button>
                </div>
            </div>
        </main>
    </div>
</div> 