/**
 * Payments Screen - Enhanced with Nova Tables
 * Handles payments, credit notes, and refunds with real data integration
 */

// Nova Table instances for each tab
let novaTablePayments = null;
let novaTableCreditNotes = null;
let novaTableRefunds = null;

// Current active tab
let currentTab = 'incoming-payments';

// Nova Table configurations
const paymentsTableConfig = {
    columns: [
        { key: 'payment_id', label: 'Payment ID', sortable: true, width: '120px' },
        { key: 'payment_date', label: 'Date', sortable: true, width: '120px' },
        { key: 'customer_name', label: 'Customer Name', sortable: true, width: '200px' },
        { key: 'document_number', label: 'Invoice', sortable: true, width: '120px' },
        { key: 'payment_method', label: 'Payment Method', sortable: true, width: '150px' },
        { key: 'payment_amount', label: 'Amount', sortable: true, width: '120px', type: 'currency' },
        { key: 'payment_status', label: 'Status', sortable: true, width: '100px', type: 'badge' },
        { key: 'payment_notes', label: 'Notes', sortable: false, width: '200px' },
        { key: 'actions', label: 'Actions', sortable: false, width: '150px', type: 'actions' }
    ],
    rowsPerPage: 15,
    searchable: true,
    sortable: true,
    filterable: true,
    selectable: true,
    exportable: true,
    pagination: true,
    stickyHeader: true,
    maxHeight: '60vh',
    onDoubleClick: (row) => {
        // Open payment details or edit modal
        if (row.payment_id) {
            window.openPaymentModal('edit', row.payment_id);
        }
    },
    onSelectionChange: (selectedRows) => {
        console.log('Selected payments:', selectedRows);
    }
};

const creditNotesTableConfig = {
    columns: [
        { key: 'document_number', label: 'Credit Note ID', sortable: true, width: '150px' },
        { key: 'issue_date', label: 'Date Issued', sortable: true, width: '120px' },
        { key: 'customer_name', label: 'Customer', sortable: true, width: '200px' },
        { key: 'total_amount', label: 'Amount', sortable: true, width: '120px', type: 'currency' },
        { key: 'document_status', label: 'Status', sortable: true, width: '120px', type: 'badge' },
        { key: 'remaining_time', label: 'Remaining Time', sortable: true, width: '120px' },
        { key: 'notes', label: 'Notes', sortable: false, width: '200px' },
        { key: 'actions', label: 'Actions', sortable: false, width: '150px', type: 'actions' }
    ],
    rowsPerPage: 15,
    searchable: true,
    sortable: true,
    filterable: true,
    selectable: true,
    exportable: true,
    pagination: true,
    stickyHeader: true,
    maxHeight: '60vh',
    onDoubleClick: (row) => {
        // Open credit note details or edit modal
        if (row.document_id) {
            window.openDocumentModal('edit', row.document_id);
        }
    },
    onSelectionChange: (selectedRows) => {
        console.log('Selected credit notes:', selectedRows);
    }
};

const refundsTableConfig = {
    columns: [
        { key: 'document_number', label: 'Refund ID', sortable: true, width: '120px' },
        { key: 'issue_date', label: 'Date', sortable: true, width: '120px' },
        { key: 'customer_name', label: 'Customer Name', sortable: true, width: '200px' },
        { key: 'refund_method', label: 'Refund Method', sortable: true, width: '150px' },
        { key: 'total_amount', label: 'Amount', sortable: true, width: '120px', type: 'currency' },
        { key: 'related_document_number', label: 'Linked Invoice', sortable: true, width: '150px' },
        { key: 'document_status', label: 'Status', sortable: true, width: '120px', type: 'badge' },
        { key: 'actions', label: 'Actions', sortable: false, width: '150px', type: 'actions' }
    ],
    rowsPerPage: 15,
    searchable: true,
    sortable: true,
    filterable: true,
    selectable: true,
    exportable: true,
    pagination: true,
    stickyHeader: true,
    maxHeight: '60vh',
    onDoubleClick: (row) => {
        // Open refund details or edit modal
        if (row.document_id) {
            window.openDocumentModal('edit', row.document_id);
        }
    },
    onSelectionChange: (selectedRows) => {
        console.log('Selected refunds:', selectedRows);
    }
};

/**
 * Initialize the payments screen
 */
function initializePaymentsScreen() {
    console.log('Initializing payments screen...');
    
    // Initialize Nova tables
    initializeNovaTables();
    
    // Set up tab switching
    setupTabSwitching();
    
    // Load initial data
    loadPaymentsData();
    
    // Set up event listeners
    setupEventListeners();
    
    console.log('Payments screen initialized successfully');
}

/**
 * Initialize Nova tables for all tabs
 */
function initializeNovaTables() {
    console.log('Initializing Nova tables...');
    
    // Wait for NovaTable to be available
    if (!window.NovaTable) {
        console.log('Waiting for NovaTable to be available...');
        setTimeout(initializeNovaTables, 100);
        return;
    }
    
    try {
        // Initialize payments table
        const paymentsContainer = document.getElementById('nova-table-payments');
        if (paymentsContainer) {
            novaTablePayments = new window.NovaTable('nova-table-payments', paymentsTableConfig);
            console.log('Nova table for payments initialized');
        }
        
        // Initialize credit notes table
        const creditNotesContainer = document.getElementById('nova-table-credit-notes');
        if (creditNotesContainer) {
            novaTableCreditNotes = new window.NovaTable('nova-table-credit-notes', creditNotesTableConfig);
            console.log('Nova table for credit notes initialized');
        }
        
        // Initialize refunds table
        const refundsContainer = document.getElementById('nova-table-refunds');
        if (refundsContainer) {
            novaTableRefunds = new window.NovaTable('nova-table-refunds', refundsTableConfig);
            console.log('Nova table for refunds initialized');
        }
        
    } catch (error) {
        console.error('Error initializing Nova tables:', error);
    }
}

/**
 * Set up tab switching functionality
 */
function setupTabSwitching() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    
    tabButtons.forEach(button => {
        button.addEventListener('click', () => {
            const targetTab = button.getAttribute('data-tab');
            
            // Update active tab
            currentTab = targetTab;
            
            // Update button states
            tabButtons.forEach(btn => btn.classList.remove('active'));
            button.classList.add('active');
            
            // Update content visibility
            tabContents.forEach(content => content.classList.remove('active'));
            const targetContent = document.getElementById(targetTab);
            if (targetContent) {
                targetContent.classList.add('active');
            }
            
            // Load data for the active tab
            loadDataForTab(targetTab);
        });
    });
}

/**
 * Load data for the specified tab
 */
function loadDataForTab(tabName) {
    console.log(`Loading data for tab: ${tabName}`);
    
    switch (tabName) {
        case 'incoming-payments':
            loadPaymentsData();
            break;
        case 'credit-notes':
            loadCreditNotesData();
            break;
        case 'refunds':
            loadRefundsData();
            break;
        default:
            console.warn(`Unknown tab: ${tabName}`);
    }
}

/**
 * Load payments data
 */
async function loadPaymentsData() {
    console.log('Loading payments data...');
    
    try {
        window.showLoadingModal('Loading payments...');
        
        const response = await fetch('../api/payment-api.php?action=get_payments', {
            method: 'GET',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            const formattedData = formatPaymentsDataForNovaTable(data.data || []);
            
            if (novaTablePayments) {
                novaTablePayments.loadData(formattedData);
                console.log(`Loaded ${formattedData.length} payments`);
            } else {
                console.error('Nova table for payments not initialized');
            }
        } else {
            console.error('Failed to load payments:', data.message);
            window.showResponseModal('Error loading payments: ' + data.message, 'error');
        }
        
            } catch (error) {
            console.error('Error loading payments:', error);
            window.showResponseModal('Error loading payments. Please try again.', 'error');
        } finally {
            window.hideLoadingModal();
        }
}

/**
 * Load credit notes data
 */
async function loadCreditNotesData() {
    console.log('Loading credit notes data...');
    
    try {
        window.showLoadingModal('Loading credit notes...');
        
        // Use buildQueryParams for consistent parameter handling
        const paramsObj = { 
            action: 'get_documents', 
            type: 'credit-note' 
        };
        const params = window.buildQueryParams(paramsObj);
        const url = `../api/document-api.php?${params.toString()}`;
        
        console.log('Loading credit notes with URL:', url);
        
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            const formattedData = formatCreditNotesDataForNovaTable(data.data || []);
            
            if (novaTableCreditNotes) {
                novaTableCreditNotes.loadData(formattedData);
                console.log(`Loaded ${formattedData.length} credit notes`);
            } else {
                console.error('Nova table for credit notes not initialized');
            }
        } else {
            console.error('Failed to load credit notes:', data.message);
            window.showResponseModal('Error loading credit notes: ' + data.message, 'error');
        }
        
    } catch (error) {
        console.error('Error loading credit notes:', error);
        window.showResponseModal('Error loading credit notes. Please try again.', 'error');
    } finally {
        window.hideLoadingModal();
    }
}

/**
 * Load refunds data
 */
async function loadRefundsData() {
    console.log('Loading refunds data...');
    
    try {
        window.showLoadingModal('Loading refunds...');
        
        // Use buildQueryParams for consistent parameter handling
        const paramsObj = { 
            action: 'get_documents', 
            type: 'refund' 
        };
        const params = window.buildQueryParams(paramsObj);
        const url = `../api/document-api.php?${params.toString()}`;
        
        console.log('Loading refunds with URL:', url);
        
        const response = await fetch(url, {
            method: 'GET',
            credentials: 'include'
        });
        
        const data = await response.json();
        
        if (data.success) {
            const formattedData = formatRefundsDataForNovaTable(data.data || []);
            
            if (novaTableRefunds) {
                novaTableRefunds.loadData(formattedData);
                console.log(`Loaded ${formattedData.length} refunds`);
            } else {
                console.error('Nova table for refunds not initialized');
            }
        } else {
            console.error('Failed to load refunds:', data.message);
            window.showResponseModal('Error loading refunds: ' + data.message, 'error');
        }
        
    } catch (error) {
        console.error('Error loading refunds:', error);
        window.showResponseModal('Error loading refunds. Please try again.', 'error');
    } finally {
        window.hideLoadingModal();
    }
}

/**
 * Format payments data for Nova table
 */
function formatPaymentsDataForNovaTable(payments) {
    return payments.map(payment => ({
        id: payment.payment_id,
        payment_id: payment.payment_id,
        payment_date: formatDate(payment.payment_date),
        customer_name: payment.customer_name || 'N/A',
        document_number: payment.document_number || 'N/A',
        payment_method: payment.payment_method_name || 'N/A',
        payment_amount: parseFloat(payment.payment_amount || 0).toFixed(2),
        payment_status: getPaymentStatusBadge(payment.payment_status),
        payment_notes: payment.payment_notes || '',
        actions: generatePaymentActions(payment)
    }));
}

/**
 * Format credit notes data for Nova table
 */
function formatCreditNotesDataForNovaTable(creditNotes) {
    return creditNotes.map(creditNote => ({
        id: creditNote.document_id,
        document_id: creditNote.document_id,
        document_number: creditNote.document_number,
        issue_date: formatDate(creditNote.issue_date),
        customer_name: creditNote.customer_name || 'N/A',
        total_amount: parseFloat(creditNote.total_amount || 0).toFixed(2),
        document_status: getDocumentStatusBadge(creditNote.document_status),
        remaining_time: calculateRemainingTime(creditNote.issue_date),
        notes: creditNote.notes || '',
        actions: generateCreditNoteActions(creditNote)
    }));
}

/**
 * Format refunds data for Nova table
 */
function formatRefundsDataForNovaTable(refunds) {
    return refunds.map(refund => ({
        id: refund.document_id,
        document_id: refund.document_id,
        document_number: refund.document_number,
        issue_date: formatDate(refund.issue_date),
        customer_name: refund.customer_name || 'N/A',
        refund_method: refund.refund_method || 'N/A',
        total_amount: parseFloat(refund.total_amount || 0).toFixed(2),
        related_document_number: refund.related_document_number || 'N/A',
        document_status: getDocumentStatusBadge(refund.document_status),
        actions: generateRefundActions(refund)
    }));
}

/**
 * Generate payment actions HTML
 */
function generatePaymentActions(payment) {
    const actions = `
        <div class="action-buttons">
            <button class="btn btn-sm btn-secondary" onclick="viewPaymentDetails(${payment.payment_id})" title="View Details">
                <i class="material-icons">visibility</i>
            </button>
            <button class="btn btn-sm btn-primary" onclick="editPayment(${payment.payment_id})" title="Edit Payment">
                <i class="material-icons">edit</i>
            </button>
            <button class="btn btn-sm btn-danger" onclick="deletePayment(${payment.payment_id})" title="Delete Payment">
                <i class="material-icons">delete</i>
            </button>
        </div>
    `;
    
    // Add link button only for unlinked payments
    if (!payment.document_id || payment.document_id === '') {
        return actions.replace('</div>', `
            <button class="btn btn-sm btn-info" onclick="linkPaymentToInvoice(${payment.payment_id})" title="Link to Invoice">
                <i class="material-icons">link</i>
            </button>
        </div>`);
    }
    
    return actions;
}

/**
 * Generate credit note actions HTML
 */
function generateCreditNoteActions(creditNote) {
    return `
        <div class="action-buttons">
            <button class="btn btn-sm btn-secondary" onclick="viewCreditNoteDetails(${creditNote.document_id})" title="View Details">
                <i class="material-icons">visibility</i>
            </button>
            <button class="btn btn-sm btn-primary" onclick="editCreditNote(${creditNote.document_id})" title="Edit Credit Note">
                <i class="material-icons">edit</i>
            </button>
            <button class="btn btn-sm btn-success" onclick="applyCreditNote(${creditNote.document_id})" title="Apply to Invoice">
                <i class="material-icons">attach_money</i>
            </button>
        </div>
    `;
}

/**
 * Generate refund actions HTML
 */
function generateRefundActions(refund) {
    return `
        <div class="action-buttons">
            <button class="btn btn-sm btn-secondary" onclick="viewRefundDetails(${refund.document_id})" title="View Details">
                <i class="material-icons">visibility</i>
            </button>
            <button class="btn btn-sm btn-primary" onclick="editRefund(${refund.document_id})" title="Edit Refund">
                <i class="material-icons">edit</i>
            </button>
            <button class="btn btn-sm btn-success" onclick="approveRefund(${refund.document_id})" title="Approve Refund">
                <i class="material-icons">check_circle</i>
            </button>
        </div>
    `;
}

/**
 * Get payment status badge HTML
 */
function getPaymentStatusBadge(status) {
    const statusMap = {
        'paid': { class: 'badge-success', text: 'Paid' },
        'pending': { class: 'badge-warning', text: 'Pending' },
        'failed': { class: 'badge-danger', text: 'Failed' },
        'cancelled': { class: 'badge-secondary', text: 'Cancelled' }
    };
    
    const statusInfo = statusMap[status] || { class: 'badge-secondary', text: status || 'Unknown' };
    
    return `<span class="badge ${statusInfo.class}">${statusInfo.text}</span>`;
}

/**
 * Get document status badge HTML
 */
function getDocumentStatusBadge(status) {
    const statusMap = {
        'draft': { class: 'badge-secondary', text: 'Draft' },
        'sent': { class: 'badge-info', text: 'Sent' },
        'paid': { class: 'badge-success', text: 'Paid' },
        'overdue': { class: 'badge-warning', text: 'Overdue' },
        'cancelled': { class: 'badge-danger', text: 'Cancelled' },
        'refunded': { class: 'badge-warning', text: 'Refunded' }
    };
    
    const statusInfo = statusMap[status] || { class: 'badge-secondary', text: status || 'Unknown' };
    
    return `<span class="badge ${statusInfo.class}">${statusInfo.text}</span>`;
}

/**
 * Format date for display
 */
function formatDate(dateString) {
    if (!dateString) return 'N/A';
    
    const date = new Date(dateString);
    return date.toLocaleDateString('en-GB', {
        year: 'numeric',
        month: '2-digit',
        day: '2-digit'
    });
}

/**
 * Calculate remaining time for credit notes
 */
function calculateRemainingTime(issueDate) {
    if (!issueDate) return 'N/A';
    
    const issue = new Date(issueDate);
    const now = new Date();
    const expiry = new Date(issue.getTime() + (30 * 24 * 60 * 60 * 1000)); // 30 days from issue
    
    const diffTime = expiry - now;
    const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
    
    if (diffDays < 0) {
        return 'Expired';
    } else if (diffDays === 0) {
        return 'Expires today';
    } else {
        return `${diffDays} days`;
    }
}

/**
 * Set up event listeners
 */
function setupEventListeners() {
    // Refresh button functionality
    const refreshButtons = document.querySelectorAll('.btn-refresh');
    refreshButtons.forEach(button => {
        button.addEventListener('click', () => {
            loadDataForTab(currentTab);
        });
    });
    
    // Export functionality
    const exportButtons = document.querySelectorAll('.btn-export');
    exportButtons.forEach(button => {
        button.addEventListener('click', () => {
            exportCurrentTabData();
        });
    });
}

/**
 * Export current tab data
 */
function exportCurrentTabData() {
    let novaTable = null;
    
    switch (currentTab) {
        case 'incoming-payments':
            novaTable = novaTablePayments;
            break;
        case 'credit-notes':
            novaTable = novaTableCreditNotes;
            break;
        case 'refunds':
            novaTable = novaTableRefunds;
            break;
    }
    
    if (novaTable) {
        novaTable.exportSelected();
    } else {
        window.showResponseModal('No data available to export', 'warning');
    }
}

// Action functions for buttons
function viewPaymentDetails(paymentId) {
    console.log('Viewing payment details:', paymentId);
    // Implement payment details view
    window.showResponseModal('Payment details view not yet implemented', 'info');
}

function editPayment(paymentId) {
    console.log('Editing payment:', paymentId);
    window.openPaymentModal('edit', paymentId);
}

function deletePayment(paymentId) {
    console.log('Deleting payment:', paymentId);
    if (confirm('Are you sure you want to delete this payment?')) {
        // Implement payment deletion
        window.showResponseModal('Payment deletion not yet implemented', 'info');
    }
}

function linkPaymentToInvoice(paymentId) {
    console.log('Linking payment to invoice:', paymentId);
    // Show invoice selection modal
    showInvoiceSelectionModal(paymentId);
}

function showInvoiceSelectionModal(paymentId) {
    // Create a simple modal for invoice selection
    const modal = document.createElement('div');
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    `;
    
    const modalContent = document.createElement('div');
    modalContent.className = 'modal-content';
    modalContent.style.cssText = `
        background: white;
        padding: 20px;
        border-radius: 8px;
        min-width: 400px;
        max-width: 600px;
        max-height: 80vh;
        overflow-y: auto;
    `;
    
    modalContent.innerHTML = `
        <h3>Link Payment to Invoice</h3>
        <p>Select an invoice to link this payment to:</p>
        <select id="invoice-select-modal" style="width: 100%; margin: 10px 0; padding: 8px;">
            <option value="">Loading invoices...</option>
        </select>
        <div style="margin-top: 20px; text-align: right;">
            <button onclick="closeInvoiceSelectionModal()" style="margin-right: 10px;">Cancel</button>
            <button onclick="confirmLinkPayment(${paymentId})" class="btn btn-primary">Link Payment</button>
        </div>
    `;
    
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    // Load available invoices
    loadInvoicesForLinking();
}

function closeInvoiceSelectionModal() {
    const modal = document.querySelector('.modal-overlay');
    if (modal) {
        modal.remove();
    }
}

async function loadInvoicesForLinking() {
    try {
        // Use buildQueryParams for consistent parameter handling
        const paramsObj = { 
            action: 'list_documents', 
            type: 'invoice', 
            status: 'sent' 
        };
        const params = window.buildQueryParams(paramsObj);
        const url = `../api/document-api.php?${params.toString()}`;
        
        console.log('Loading invoices for linking with URL:', url);
        
        const response = await fetch(url);
        const result = await response.json();
        
        const select = document.getElementById('invoice-select-modal');
        if (result.success && result.data) {
            select.innerHTML = '<option value="">Select an invoice...</option>';
            result.data.forEach(invoice => {
                const option = document.createElement('option');
                option.value = invoice.document_id;
                option.textContent = `${invoice.document_number} - ${invoice.client_name} (R${parseFloat(invoice.balance_due || 0).toFixed(2)} due)`;
                select.appendChild(option);
            });
        } else {
            select.innerHTML = '<option value="">No unpaid invoices available</option>';
        }
    } catch (error) {
        console.error('Error loading invoices:', error);
        const select = document.getElementById('invoice-select-modal');
        select.innerHTML = '<option value="">Error loading invoices</option>';
    }
}

async function confirmLinkPayment(paymentId) {
    const invoiceSelect = document.getElementById('invoice-select-modal');
    const documentId = invoiceSelect.value;
    
    if (!documentId) {
        window.showResponseModal('Please select an invoice to link the payment to', 'error');
        return;
    }
    
    try {
        const response = await fetch('../api/payment-api.php?action=link_payment_to_invoice', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                payment_id: paymentId,
                document_id: documentId
            })
        });
        
        const result = await response.json();
        
        if (result.success) {
            window.showResponseModal('Payment linked to invoice successfully', 'success');
            closeInvoiceSelectionModal();
            // Refresh the payments table
            loadPaymentsData();
        } else {
            window.showResponseModal(result.message || 'Failed to link payment', 'error');
        }
    } catch (error) {
        console.error('Error linking payment:', error);
        window.showResponseModal('Error linking payment to invoice', 'error');
    }
}

function viewCreditNoteDetails(documentId) {
    console.log('Viewing credit note details:', documentId);
    window.openDocumentModal('view', documentId);
}

function editCreditNote(documentId) {
    console.log('Editing credit note:', documentId);
    window.openDocumentModal('edit', documentId);
}

function applyCreditNote(documentId) {
    console.log('Applying credit note:', documentId);
    window.showResponseModal('Credit note application not yet implemented', 'info');
}

function viewRefundDetails(documentId) {
    console.log('Viewing refund details:', documentId);
    window.openDocumentModal('view', documentId);
}

function editRefund(documentId) {
    console.log('Editing refund:', documentId);
    window.openDocumentModal('edit', documentId);
}

function approveRefund(documentId) {
    console.log('Approving refund:', documentId);
    window.showResponseModal('Refund approval not yet implemented', 'info');
}

// Global functions for sidebar integration
// Note: window.openPaymentModal is already defined in payment-modal.js
// This function is for sidebar integration and should call the actual modal
window.openPaymentModalFromSidebar = function(mode, paymentId = null) {
    console.log('Opening payment modal from sidebar:', mode, paymentId);
    try {
        // Call the actual payment modal function
        if (typeof window.paymentModal !== 'undefined' && window.paymentModal.openModal) {
            window.paymentModal.openModal(mode, paymentId);
        } else {
            window.showResponseModal('Payment modal not available', 'error');
        }
    } catch (error) {
        console.error('Error opening payment modal:', error);
        window.showResponseModal('Payment modal not available', 'error');
    }
};

window.showPaymentHistory = function() {
    console.log('Showing payment history');
    window.showResponseModal('Payment history not yet implemented', 'info');
};

// Initialize when DOM is ready and NovaTable is available
function waitForNovaTableAndInit() {
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', waitForNovaTableAndInit);
        return;
    }
    
    if (!window.NovaTable) {
        console.log('Waiting for NovaTable to be available...');
        setTimeout(waitForNovaTableAndInit, 100);
        return;
    }
    
    console.log('DOM ready and NovaTable available, initializing payments screen...');
    initializePaymentsScreen();
}

// Invoice selection modal for credit notes and refunds
let selectedInvoiceForDocument = null;
let currentDocumentType = null;

function showInvoiceSelectionForDocument(documentType) {
    console.log('Showing invoice selection for document type:', documentType);
    currentDocumentType = documentType;
    selectedInvoiceForDocument = null;
    
    // Create modal
    const modal = document.createElement('div');
    modal.id = 'invoice-selection-modal';
    modal.className = 'modal-overlay';
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.5);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 1000;
    `;
    
    const modalContent = document.createElement('div');
    modalContent.className = 'modal-content';
    modalContent.style.cssText = `
        background: white;
        padding: 20px;
        border-radius: 8px;
        min-width: 500px;
        max-width: 700px;
        max-height: 80vh;
        overflow-y: auto;
    `;
    
    const documentTypeLabel = documentType === 'credit-note' ? 'Credit Note' : 'Refund';
    
    modalContent.innerHTML = `
        <h3>Select Invoice for ${documentTypeLabel}</h3>
        <p>Choose an invoice to create a ${documentTypeLabel.toLowerCase()} for:</p>
        
        <div style="margin: 20px 0;">
            <label for="invoice-search-input" style="display: block; margin-bottom: 8px; font-weight: bold;">Search Invoice:</label>
            <input type="text" id="invoice-search-input" placeholder="Search by invoice number or client name..." 
                   style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
        </div>
        
        <div style="margin: 20px 0;">
            <label for="invoice-select-modal" style="display: block; margin-bottom: 8px; font-weight: bold;">Select Invoice:</label>
            <select id="invoice-select-modal" style="width: 100%; padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                <option value="">Loading invoices...</option>
            </select>
        </div>
        
        <div style="margin-top: 20px; text-align: right;">
            <button onclick="closeInvoiceSelectionForDocument()" style="margin-right: 10px; padding: 8px 16px; border: 1px solid #ddd; border-radius: 4px; background: #f5f5f5;">Cancel</button>
            <button onclick="confirmInvoiceSelectionForDocument()" class="btn btn-primary" style="padding: 8px 16px;">Next</button>
        </div>
    `;
    
    modal.appendChild(modalContent);
    document.body.appendChild(modal);
    
    // Load invoices and setup search
    loadInvoicesForDocumentSelection();
    setupInvoiceSearch();
}

function closeInvoiceSelectionForDocument() {
    const modal = document.getElementById('invoice-selection-modal');
    if (modal) {
        modal.remove();
    }
    selectedInvoiceForDocument = null;
    currentDocumentType = null;
}

async function loadInvoicesForDocumentSelection() {
    try {
        // Use buildQueryParams for consistent parameter handling
        const paramsObj = { 
            action: 'list_documents', 
            type: 'invoice', 
            status: 'sent' 
        };
        const params = window.buildQueryParams(paramsObj);
        const url = `../api/document-api.php?${params.toString()}`;
        
        console.log('Loading invoices with URL:', url);
        
        const response = await fetch(url);
        const result = await response.json();
        
        if (result.success) {
            populateInvoiceSelectForDocument(result.data);
        } else {
            throw new Error(result.message || 'Failed to load invoices');
        }
    } catch (error) {
        console.error('Error loading invoices for document selection:', error);
        const select = document.getElementById('invoice-select-modal');
        if (select) {
            select.innerHTML = '<option value="">Error loading invoices</option>';
        }
    }
}

function populateInvoiceSelectForDocument(invoices) {
    const select = document.getElementById('invoice-select-modal');
    if (!select) return;
    
    select.innerHTML = '<option value="">Select an invoice...</option>';
    
    invoices.forEach(invoice => {
        const option = document.createElement('option');
        option.value = invoice.document_id;
        option.textContent = `${invoice.document_number} - ${invoice.client_name} (R${parseFloat(invoice.total_amount).toFixed(2)})`;
        option.dataset.invoice = JSON.stringify(invoice);
        select.appendChild(option);
    });
}

function setupInvoiceSearch() {
    const searchInput = document.getElementById('invoice-search-input');
    const select = document.getElementById('invoice-select-modal');
    
    if (!searchInput || !select) return;
    
    searchInput.addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const options = select.querySelectorAll('option');
        
        options.forEach(option => {
            if (option.value === '') return; // Skip placeholder options
            
            const text = option.textContent.toLowerCase();
            if (text.includes(searchTerm)) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        });
    });
    
    // Handle selection change
    select.addEventListener('change', function() {
        if (this.value) {
            const selectedOption = this.options[this.selectedIndex];
            selectedInvoiceForDocument = JSON.parse(selectedOption.dataset.invoice);
        } else {
            selectedInvoiceForDocument = null;
        }
    });
}

function confirmInvoiceSelectionForDocument() {
    if (!selectedInvoiceForDocument) {
        window.showResponseModal('Please select an invoice first', 'warning');
        return;
    }
    
    console.log('Selected invoice for document:', selectedInvoiceForDocument);
    console.log('Document type:', currentDocumentType);
    
    // Close the invoice selection modal
    closeInvoiceSelectionForDocument();
    
    // Open the document modal with the selected invoice and document type
    if (typeof window.openDocumentModal === 'function') {
        window.openDocumentModal('create', selectedInvoiceForDocument.document_id, currentDocumentType);
    } else {
        window.showResponseModal('Document modal not available', 'error');
    }
}

// Start initialization
waitForNovaTableAndInit(); 