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
        
        const response = await fetch('../api/document-api.php?action=get_documents&type=credit-note', {
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
        
        const response = await fetch('../api/document-api.php?action=get_documents&type=refund', {
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
    return `
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
window.openPaymentModal = function(mode, paymentId = null) {
    console.log('Opening payment modal:', mode, paymentId);
    if (typeof window.PaymentModal !== 'undefined') {
        window.PaymentModal.open(mode, paymentId);
    } else {
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

// Start initialization
waitForNovaTableAndInit(); 