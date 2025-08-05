// Document Screen Logic: Handles main screen rendering, event binding, and table updates
// Now using NOVA Table component for enhanced functionality while preserving existing features
// Document screen functionality - all functions are now available globally

// NOVA Table instances for each document type
let novaTableInstances = {
    'invoices-section': null,
    'recurring-invoices-section': null,
    'quotations-section': null,
    'vehicle-quotations-section': null,
    'vehicle-invoices-section': null
};

// NOVA Table configurations for each document type
const tableConfigs = {
    'invoices-section': {
        columns: [
            { key: 'document_number', label: 'Invoice #', sortable: true, filterable: true },
            { key: 'client_name', label: 'Client', sortable: true, filterable: true },
            { key: 'issue_date', label: 'Date Created', sortable: true, filterable: true },
            { key: 'updated_at', label: 'Last Modified', sortable: true, filterable: true },
            { key: 'document_status', label: 'Status', sortable: true, filterable: true },
            { key: 'total_amount', label: 'Total', sortable: true, filterable: true },
            { key: 'due_date', label: 'Due Date', sortable: true, filterable: true }
        ],
        rowsPerPage: 15,
        rowsPerPageOptions: [10, 15, 25, 50, 100],
        searchable: true,
        sortable: true,
        filterable: true,
        selectable: true,
        exportable: true,
        pagination: true,
        stickyHeader: true,
        maxHeight: '60vh',
        onDoubleClick: (row) => {
            console.log(`Double-click triggered for invoices:`, row);
            if (row.document_id) {
                console.log(`Opening invoice for edit: ${row.document_id}`);
                openDocumentForEdit(row.document_id);
            }
        },
        onSelectionChange: (selectedRows) => {
            console.log('Selected invoices:', selectedRows);
        },
        getContextMenuActions: (row) => {
            const status = row.document_status?.toLowerCase();
            const isFinalized = ['unpaid', 'paid', 'sent', 'approved'].includes(status);
            const isDraft = status === 'draft';
            
            const actions = [];
            
            if (isDraft) {
                actions.push(
                    { action: 'edit', label: 'Edit Invoice', icon: '✏️' },
                    { action: 'delete', label: 'Delete Invoice', icon: '🗑️' }
                );
            } else if (isFinalized) {
                actions.push(
                    { action: 'view', label: 'View Details', icon: '👁️' },
                    { action: 'send', label: 'Send Invoice', icon: '📧' },
                    { action: 'payment', label: 'Record Payment', icon: '💰' },
                    { action: 'credit_note', label: 'Create Credit Note', icon: '📝' },
                    { action: 'refund', label: 'Create Refund', icon: '↩️' },
                    { action: 'reminder', label: 'Send Reminder', icon: '⏰' },
                    { action: 'delete', label: 'Delete Invoice', icon: '🗑️' }
                );
            } else {
                actions.push(
                    { action: 'edit', label: 'Edit Invoice', icon: '✏️' },
                    { action: 'view', label: 'View Details', icon: '👁️' },
                    { action: 'delete', label: 'Delete Invoice', icon: '🗑️' }
                );
            }
            
            return actions;
        }
    },
    'recurring-invoices-section': {
        columns: [
            { key: 'document_number', label: 'Invoice #', sortable: true, filterable: true },
            { key: 'client_name', label: 'Client', sortable: true, filterable: true },
            { key: 'start_date', label: 'Start Date', sortable: true, filterable: true },
            { key: 'next_generation_date', label: 'Next Generation', sortable: true, filterable: true },
            { key: 'frequency', label: 'Frequency', sortable: true, filterable: true },
            { key: 'document_status', label: 'Status', sortable: true, filterable: true }
        ],
        rowsPerPage: 15,
        rowsPerPageOptions: [10, 15, 25, 50, 100],
        searchable: true,
        sortable: true,
        filterable: true,
        selectable: true,
        exportable: true,
        pagination: true,
        stickyHeader: true,
        maxHeight: '60vh',
        onDoubleClick: (row) => {
            console.log(`Double-click triggered for recurring invoices:`, row);
            if (row.document_id) {
                console.log(`Opening recurring invoice for edit: ${row.document_id}`);
                openDocumentForEdit(row.document_id);
            }
        },
        onSelectionChange: (selectedRows) => {
            console.log('Selected recurring invoices:', selectedRows);
        },
        contextMenuActions: [
            { action: 'edit', label: 'Edit Recurring Invoice', icon: '✏️' },
            { action: 'view', label: 'View Details', icon: '👁️' },
            { action: 'pause', label: 'Pause', icon: '⏸️' },
            { action: 'resume', label: 'Resume', icon: '▶️' },
            { action: 'cancel', label: 'Cancel', icon: '❌' },
            { action: 'delete', label: 'Delete', icon: '🗑️' }
        ]
    },
    'quotations-section': {
        columns: [
            { key: 'document_number', label: 'Quotation #', sortable: true, filterable: true },
            { key: 'client_name', label: 'Client', sortable: true, filterable: true },
            { key: 'issue_date', label: 'Date Created', sortable: true, filterable: true },
            { key: 'document_status', label: 'Status', sortable: true, filterable: true },
            { key: 'total_amount', label: 'Total', sortable: true, filterable: true }
        ],
        rowsPerPage: 15,
        rowsPerPageOptions: [10, 15, 25, 50, 100],
        searchable: true,
        sortable: true,
        filterable: true,
        selectable: true,
        exportable: true,
        pagination: true,
        stickyHeader: true,
        maxHeight: '60vh',
        onDoubleClick: (row) => {
            console.log(`Double-click triggered for quotations:`, row);
            if (row.document_id) {
                console.log(`Opening quotation for edit: ${row.document_id}`);
                openDocumentForEdit(row.document_id);
            }
        },
        onSelectionChange: (selectedRows) => {
            console.log('Selected quotations:', selectedRows);
        },
        getContextMenuActions: (row) => {
            const status = row.document_status?.toLowerCase();
            const isFinalized = ['approved', 'rejected'].includes(status);
            const isDraft = status === 'draft';
            
            const actions = [];
            
            if (isDraft) {
                actions.push(
                    { action: 'edit', label: 'Edit Quotation', icon: '✏️' },
                    { action: 'delete', label: 'Delete', icon: '🗑️' }
                );
            } else if (isFinalized) {
                actions.push(
                    { action: 'view', label: 'View Details', icon: '👁️' },
                    { action: 'convert', label: 'Convert to Invoice', icon: '📄' },
                    { action: 'delete', label: 'Delete', icon: '🗑️' }
                );
            } else {
                actions.push(
                    { action: 'edit', label: 'Edit Quotation', icon: '✏️' },
                    { action: 'view', label: 'View Details', icon: '👁️' },
                    { action: 'approve', label: 'Approve', icon: '✅' },
                    { action: 'reject', label: 'Reject', icon: '❌' },
                    { action: 'delete', label: 'Delete', icon: '🗑️' }
                );
            }
            
            return actions;
        }
    },
    'vehicle-quotations-section': {
        columns: [
            { key: 'document_number', label: 'Vehicle Quotation #', sortable: true, filterable: true },
            { key: 'client_name', label: 'Client', sortable: true, filterable: true },
            { key: 'vehicle_info', label: 'Vehicle', sortable: true, filterable: true },
            { key: 'issue_date', label: 'Date Created', sortable: true, filterable: true },
            { key: 'document_status', label: 'Status', sortable: true, filterable: true },
            { key: 'total_amount', label: 'Total', sortable: true, filterable: true }
        ],
        rowsPerPage: 15,
        rowsPerPageOptions: [10, 15, 25, 50, 100],
        searchable: true,
        sortable: true,
        filterable: true,
        selectable: true,
        exportable: true,
        pagination: true,
        stickyHeader: true,
        maxHeight: '60vh',
        onDoubleClick: (row) => {
            console.log(`Double-click triggered for vehicle quotations:`, row);
            if (row.document_id) {
                console.log(`Opening vehicle quotation for edit: ${row.document_id}`);
                openDocumentForEdit(row.document_id);
            }
        },
        onSelectionChange: (selectedRows) => {
            console.log('Selected vehicle quotations:', selectedRows);
        },
        getContextMenuActions: (row) => {
            const status = row.document_status?.toLowerCase();
            const isFinalized = ['approved', 'rejected'].includes(status);
            const isDraft = status === 'draft';
            
            const actions = [];
            
            if (isDraft) {
                actions.push(
                    { action: 'edit', label: 'Edit Vehicle Quotation', icon: '✏️' },
                    { action: 'delete', label: 'Delete', icon: '🗑️' }
                );
            } else if (isFinalized) {
                actions.push(
                    { action: 'view', label: 'View Details', icon: '👁️' },
                    { action: 'convert', label: 'Convert to Vehicle Invoice', icon: '📄' },
                    { action: 'delete', label: 'Delete', icon: '🗑️' }
                );
            } else {
                actions.push(
                    { action: 'edit', label: 'Edit Vehicle Quotation', icon: '✏️' },
                    { action: 'view', label: 'View Details', icon: '👁️' },
                    { action: 'approve', label: 'Approve', icon: '✅' },
                    { action: 'reject', label: 'Reject', icon: '❌' },
                    { action: 'delete', label: 'Delete', icon: '🗑️' }
                );
            }
            
            return actions;
        }
    },
    'vehicle-invoices-section': {
        columns: [
            { key: 'document_number', label: 'Vehicle Invoice #', sortable: true, filterable: true },
            { key: 'client_name', label: 'Client', sortable: true, filterable: true },
            { key: 'vehicle_info', label: 'Vehicle', sortable: true, filterable: true },
            { key: 'issue_date', label: 'Date Created', sortable: true, filterable: true },
            { key: 'document_status', label: 'Status', sortable: true, filterable: true },
            { key: 'total_amount', label: 'Total', sortable: true, filterable: true }
        ],
        rowsPerPage: 15,
        rowsPerPageOptions: [10, 15, 25, 50, 100],
        searchable: true,
        sortable: true,
        filterable: true,
        selectable: true,
        exportable: true,
        pagination: true,
        stickyHeader: true,
        maxHeight: '60vh',
        onDoubleClick: (row) => {
            console.log(`Double-click triggered for vehicle invoices:`, row);
            if (row.document_id) {
                console.log(`Opening vehicle invoice for edit: ${row.document_id}`);
                openDocumentForEdit(row.document_id);
            }
        },
        onSelectionChange: (selectedRows) => {
            console.log('Selected vehicle invoices:', selectedRows);
        },
        getContextMenuActions: (row) => {
            const status = row.document_status?.toLowerCase();
            const isFinalized = ['unpaid', 'paid', 'sent', 'approved'].includes(status);
            const isDraft = status === 'draft';
            
            const actions = [];
            
            if (isDraft) {
                actions.push(
                    { action: 'edit', label: 'Edit Vehicle Invoice', icon: '✏️' },
                    { action: 'delete', label: 'Delete', icon: '🗑️' }
                );
            } else if (isFinalized) {
                actions.push(
                    { action: 'view', label: 'View Details', icon: '👁️' },
                    { action: 'send', label: 'Send Invoice', icon: '📧' },
                    { action: 'payment', label: 'Record Payment', icon: '💰' },
                    { action: 'credit_note', label: 'Create Credit Note', icon: '📝' },
                    { action: 'refund', label: 'Create Refund', icon: '↩️' },
                    { action: 'reminder', label: 'Send Reminder', icon: '⏰' },
                    { action: 'delete', label: 'Delete', icon: '🗑️' }
                );
            } else {
                actions.push(
                    { action: 'edit', label: 'Edit Vehicle Invoice', icon: '✏️' },
                    { action: 'view', label: 'View Details', icon: '👁️' },
                    { action: 'delete', label: 'Delete', icon: '🗑️' }
                );
            }
            
            return actions;
        }
    }
};

document.addEventListener('DOMContentLoaded', function() {
    const mainTabButtons = document.querySelectorAll('.main-tabs .tab-button');
    const sections = document.querySelectorAll('.document-section');
    
    // Show only the first section by default
    sections.forEach((sec, idx) => {
        sec.style.display = idx === 0 ? '' : 'none';
    });
    
    // Show only the first subtab group by default
    const subtabGroups = document.querySelectorAll('.status-subtabs');
    subtabGroups.forEach((group, idx) => {
        group.style.display = idx === 0 ? 'flex' : 'none';
    });
    
    mainTabButtons.forEach((btn, idx) => {
        btn.classList.toggle('active', idx === 0);
                    btn.addEventListener('click', function() {
                console.log(`Tab clicked: ${this.dataset.section}`);
                mainTabButtons.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                
                // Show/hide sections
                sections.forEach(sec => {
                    if (sec.id === this.dataset.section) {
                        console.log(`Showing section: ${sec.id}`);
                        sec.style.display = '';
                        // Initialize Nova table for this section if not already done
                        initializeNovaTableForSection(this.dataset.section);
                    } else {
                        console.log(`Hiding section: ${sec.id}`);
                        sec.style.display = 'none';
                    }
                });
            
            // Show/hide corresponding subtab group
            const sectionName = this.dataset.section.replace('-section', '');
            console.log(`Looking for subtab group: ${sectionName}-subtabs`);
            subtabGroups.forEach(group => {
                if (group.id === `${sectionName}-subtabs`) {
                    console.log(`Found and showing subtab group: ${group.id}`);
                    group.style.display = 'flex';
                    // Activate first subtab
                    const firstSubtab = group.querySelector('.subtab-button');
                    if (firstSubtab) {
                        group.querySelectorAll('.subtab-button').forEach(st => st.classList.remove('active'));
                        firstSubtab.classList.add('active');
                        const status = firstSubtab.dataset.status;
                        console.log(`Activating first subtab with status: ${status}`);
                        fetchAndRenderDocuments(this.dataset.section, status);
                    } else {
                        console.log('No subtabs found in group');
                    }
                } else {
                    console.log(`Hiding subtab group: ${group.id}`);
                    group.style.display = 'none';
                }
            });
        });
    });
    
    // Subtab logic for all sections
    document.querySelectorAll('.status-subtabs').forEach(subtabGroup => {
        const subtabs = subtabGroup.querySelectorAll('.subtab-button');
        subtabs.forEach(subtab => {
            subtab.addEventListener('click', function() {
                subtabs.forEach(st => st.classList.remove('active'));
                this.classList.add('active');
                
                // Find the active main tab
                const activeMainTab = document.querySelector('.main-tabs .tab-button.active');
                if (activeMainTab) {
                    const sectionId = activeMainTab.dataset.section;
                    const status = this.dataset.status;
                    fetchAndRenderDocuments(sectionId, status);
                }
            });
        });
    });
    
    // Initialize Nova tables for all sections
    initializeAllNovaTables();
    
    // Initial fetch for the first section's first subtab
    const firstSection = sections[0];
    if (firstSection) {
        const firstSubtabGroup = document.querySelector('.status-subtabs');
        const firstSubtab = firstSubtabGroup ? firstSubtabGroup.querySelector('.subtab-button') : null;
        const status = firstSubtab ? firstSubtab.dataset.status : undefined;
        fetchAndRenderDocuments(firstSection.id, status);
    }
    
    // Filters are now handled by Nova table's built-in filtering system
});

// Map section IDs to document types for API
const sectionTypeMap = {
    'invoices-section': 'invoice',
    'recurring-invoices-section': 'recurring-invoice',
    'quotations-section': 'quotation',
    'vehicle-quotations-section': 'vehicle-quotation',
    'vehicle-invoices-section': 'vehicle-invoice'
};

// Filter functionality is now handled by Nova table's built-in filtering system

/**
 * Initialize all Nova tables
 */
function initializeAllNovaTables() {
    Object.keys(tableConfigs).forEach(sectionId => {
        initializeNovaTableForSection(sectionId);
    });
}

/**
 * Initialize Nova table for a specific section
 */
function initializeNovaTableForSection(sectionId) {
    if (novaTableInstances[sectionId]) {
        console.log(`Nova table already initialized for ${sectionId}`);
        return; // Already initialized
    }
    
    const containerId = `nova-table-${sectionId.replace('-section', '')}`;
    const container = document.getElementById(containerId);
    
    if (!container) {
        console.warn(`Container ${containerId} not found for section ${sectionId}`);
        return;
    }
    
    console.log(`Initializing Nova table for ${sectionId} with container ${containerId}`);
    console.log(`Table config:`, tableConfigs[sectionId]);
    
    const config = {
        ...tableConfigs[sectionId],
        onContextMenuAction: (action, row) => {
            console.log(`Context menu action triggered: ${action} for row:`, row);
            handleContextMenuAction(action, row, sectionId);
        }
    };
    
    try {
        novaTableInstances[sectionId] = new NovaTable(containerId, config);
        console.log(`Nova table successfully initialized for ${sectionId}`);
    } catch (error) {
        console.error(`Failed to initialize Nova table for ${sectionId}:`, error);
    }
}

/**
 * Format document data for Nova table
 */
function formatDocumentDataForNovaTable(documents, sectionId) {
    return documents.map(doc => {
        const formattedDoc = {
            document_id: doc.document_id,
            document_number: doc.document_number || '',
            client_name: doc.client_name || '',
            issue_date: doc.issue_date || '',
            updated_at: doc.updated_at || '',
            document_status: doc.document_status || '',
            total_amount: doc.total_amount ? `R${parseFloat(doc.total_amount).toFixed(2)}` : 'R0.00',
            due_date: doc.due_date || '',
            start_date: doc.start_date || '',
            next_generation_date: doc.next_generation || '',
            frequency: doc.frequency || '',
            vehicle_info: doc.vehicle || ''
        };
        
        // Add any additional fields based on section
        if (sectionId === 'invoices-section') {
            formattedDoc.balance_due = doc.balance_due ? `R${parseFloat(doc.balance_due).toFixed(2)}` : 'R0.00';
        }
        
        return formattedDoc;
    });
}

/**
 * Refresh Nova table for a specific section
 */
function refreshNovaTable(sectionId) {
    const novaTable = novaTableInstances[sectionId];
    if (novaTable) {
        novaTable.refresh();
    }
}

/**
 * Get current Nova table instance for a section
 */
function getCurrentNovaTable(sectionId) {
    return novaTableInstances[sectionId];
}

/**
 * Handle context menu actions
 */
function handleContextMenuAction(action, row, sectionId) {
    console.log(`handleContextMenuAction called: action=${action}, sectionId=${sectionId}, row:`, row);
    
    switch (action) {
        case 'edit':
            console.log(`Edit action triggered for document_id: ${row.document_id}`);
            if (row.document_id) {
                openDocumentForEdit(row.document_id);
            }
            break;
        case 'view':
            if (row.document_id) {
                openDocumentForEdit(row.document_id, 'view');
            }
            break;
        case 'send':
            sendInvoice(row);
            break;
        case 'payment':
            loadPayment(row);
            break;
        case 'credit_note':
            createCreditNote(row);
            break;
        case 'refund':
            refundInvoice(row);
            break;
        case 'reminder':
            sendPaymentReminder(row);
            break;
        case 'convert':
            convertToInvoice(row);
            break;
        case 'approve':
            requestApproval(row);
            break;
        case 'reject':
            // Handle reject action
            break;
        case 'pause':
            // Handle pause action for recurring invoices
            break;
        case 'resume':
            // Handle resume action for recurring invoices
            break;
        case 'cancel':
            // Handle cancel action for recurring invoices
            break;
        case 'delete':
            deleteDocument(row);
            break;
        default:
            console.log(`Unknown action: ${action}`);
    }
}

// Filter functionality is now handled by Nova table's built-in filtering system

// Fetch and render documents for Nova table
function fetchAndRenderDocuments(sectionId, status) {
    const type = sectionTypeMap[sectionId];
    if (!type) {
        console.error(`No type mapping found for section: ${sectionId}`);
        return;
    }
    
    console.log(`Fetching documents for section: ${sectionId}, type: ${type}, status: ${status}`);
    
    // Get the Nova table instance for this section
    const novaTable = novaTableInstances[sectionId];
    if (!novaTable) {
        console.warn(`Nova table not initialized for section: ${sectionId}`);
        return;
    }
    
    const paramsObj = { action: 'list_documents', type };
    if (status && status !== 'all') paramsObj.status = status;
            const params = window.buildQueryParams(paramsObj);
    const url = `../api/document-api.php?${params.toString()}`;
    
    console.log(`API URL: ${url}`);
    
    // Show loading state in Nova table
    novaTable.loadData([]);
    
    fetch(url)
        .then(res => {
            console.log(`API Response status: ${res.status}`);
            return res.json().catch(() => {
                if (window.showResponseModal) {
                    window.showResponseModal('Server error: Invalid response format', 'error');
                }
                novaTable.loadData([]);
                throw new Error('Invalid JSON');
            });
        })
        .then(data => {
            console.log(`API Response data:`, data);
            
            if (!data.success || !Array.isArray(data.data)) {
                console.error(`API Error:`, data);
                if (typeof window.handleApiResponse === 'function') {
                    window.handleApiResponse(data);
                } else if (window.showResponseModal) {
                    window.showResponseModal(data.message || 'Failed to load documents', 'error');
                }
                novaTable.loadData([]);
                return;
            }
            
            console.log(`Raw documents data:`, data.data);
            
            // Format data for Nova table and load it
            const formattedData = formatDocumentDataForNovaTable(data.data, sectionId);
            console.log(`Formatted data for Nova table:`, formattedData);
            
            novaTable.loadData(formattedData);
        })
        .catch((err) => {
            console.error(`Fetch error:`, err);
            if (window.showResponseModal) {
                window.showResponseModal(err.message || 'Failed to load documents', 'error');
            }
            novaTable.loadData([]);
        });
}

function renderDocumentRows(sectionId, documents) {
    // This function is kept for backward compatibility
    // Nova tables handle the rendering automatically
    const novaTable = novaTableInstances[sectionId];
    if (novaTable) {
        const formattedData = formatDocumentDataForNovaTable(documents, sectionId);
        novaTable.loadData(formattedData);
    } else {
        console.warn(`Nova table not available for section: ${sectionId}`);
    }
}

// Helper to fetch and open document in modal
async function openDocumentForEdit(documentId) {
    console.log(`openDocumentForEdit called with documentId: ${documentId}`);
    
    try {
        // Show loading modal while fetching data
        if (typeof window.showLoadingModal === 'function') {
            window.showLoadingModal('Loading document...');
        }
        
        // Fetch document details from backend
        const response = await fetch(`../api/document_modal.php?action=fetch_document&document_id=${encodeURIComponent(documentId)}`);
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const result = await response.json();
        
        if (typeof window.hideLoadingModal === 'function') {
            window.hideLoadingModal();
        }
        
        if (result.success && result.data) {
            // First open the modal in edit mode
            if (typeof window.openDocumentModal === 'function') {
                window.openDocumentModal('edit');
            } else {
                throw new Error('openDocumentModal function not available');
            }
            
            // Wait a moment for modal to open
            await new Promise(resolve => setTimeout(resolve, 100));
            
            // Then populate it with the document data
            if (typeof window.setDocumentFormData === 'function') {
                window.setDocumentFormData(result.data);
            } else if (typeof setDocumentFormData === 'function') {
                setDocumentFormData(result.data);
            } else {
                throw new Error('setDocumentFormData function not available');
            }
            
            // Check if document is finalized and adjust the mode accordingly
            const status = result.data.document_status?.toLowerCase();
            const finalizedStatuses = ['unpaid', 'approved', 'paid', 'sent'];
            const actualMode = finalizedStatuses.includes(status) ? 'view' : 'edit';
            
            // Set the appropriate modal mode
            if (typeof window.setModalMode === 'function') {
                window.setModalMode(actualMode);
            }
            
            // Re-setup row listeners after data population
            if (typeof window.setupRowListeners === 'function') {
                window.setupRowListeners();
            }
            
            // Update totals after data is loaded
            if (typeof window.updateTotals === 'function') {
                window.updateTotals();
            }
            
        } else {
            if (typeof window.showResponseModal === 'function') {
                window.showResponseModal(result.message || 'Failed to load document', 'error');
            } else {
                alert(result.message || 'Failed to load document');
            }
        }
    } catch (err) {
        if (typeof window.hideLoadingModal === 'function') {
            window.hideLoadingModal();
        }
        
        const errorMsg = 'Error loading document: ' + (err.message || err);
        if (typeof window.showResponseModal === 'function') {
            window.showResponseModal(errorMsg, 'error');
        } else {
            alert(errorMsg);
        }
        console.error('Error loading document:', err);
    }
}

function showDocumentContextMenu(e, doc, sectionId) {
    // Remove any existing menu
    const oldMenu = document.getElementById('document-context-menu');
    if (oldMenu) oldMenu.remove();

    // Build menu options based on type and status
    const isDraft = (doc.document_status && doc.document_status.toLowerCase() === 'draft');
    const status = doc.document_status?.toLowerCase() || '';
    const finalizedStatuses = ['finalized', 'approved', 'paid', 'sent'];
    const isEditable = !finalizedStatuses.includes(status);
    
    let options = [];
    if (sectionId === 'quotations-section') {
        options.push({ label: 'View', action: () => openDocumentForEdit(doc.document_id) });
        if (isEditable) options.push({ label: 'Edit', action: () => openDocumentForEdit(doc.document_id) });
        options.push({ label: 'Convert to Invoice', action: () => convertToInvoice(doc) });
        options.push({ label: 'View Client', action: () => viewClient(doc) });
        if (isDraft) options.push({ label: 'Delete', action: () => deleteDocument(doc) });
        options.push({ label: 'Request Approval', action: () => requestApproval(doc) });
    } else if (sectionId === 'invoices-section' || sectionId === 'vehicle-invoices-section') {
        options.push({ label: 'View', action: () => openDocumentForEdit(doc.document_id) });
        if (isEditable) options.push({ label: 'Edit', action: () => openDocumentForEdit(doc.document_id) });
        options.push({ label: 'Load Payment', action: () => loadPayment(doc) });
        options.push({ label: 'Create Credit Note', action: () => createCreditNote(doc) });
        options.push({ label: 'Refund Invoice', action: () => refundInvoice(doc) });
        options.push({ label: 'View Related Documents', action: () => viewRelatedDocuments(doc) });
        options.push({ label: 'Send Invoice', action: () => sendInvoice(doc) });
        options.push({ label: 'Send Payment Reminder', action: () => sendPaymentReminder(doc) });
        options.push({ label: 'View Client', action: () => viewClient(doc) });
    } else {
        options.push({ label: 'View', action: () => openDocumentForEdit(doc.document_id) });
        if (isEditable) options.push({ label: 'Edit', action: () => openDocumentForEdit(doc.document_id) });
        options.push({ label: 'View Client', action: () => viewClient(doc) });
    }

    // Create menu
    const menu = document.createElement('div');
    menu.id = 'document-context-menu';
    menu.style.position = 'fixed';
    menu.style.top = e.clientY + 'px';
    menu.style.left = e.clientX + 'px';
    menu.style.background = 'var(--color-background, #fff)';
    menu.style.border = '1px solid var(--border-color, #ccc)';
    menu.style.borderRadius = 'var(--radius-small, 4px)';
    menu.style.boxShadow = '0 2px 8px var(--shadow-light, rgba(0,0,0,0.08))';
    menu.style.zIndex = '3000';
    menu.style.minWidth = '180px';
    menu.style.padding = '4px 0';
    menu.style.fontSize = 'var(--font-size-base, 14px)';

    options.forEach(opt => {
        const item = document.createElement('div');
        item.textContent = opt.label;
        item.style.padding = '8px 18px';
        item.style.cursor = 'pointer';
        item.style.userSelect = 'none';
        item.onmouseenter = () => { item.style.background = 'var(--color-primary-fade, #f5faff)'; };
        item.onmouseleave = () => { item.style.background = 'var(--color-background, #fff)'; };
        item.onclick = () => {
            menu.remove();
            opt.action();
        };
        menu.appendChild(item);
    });

    document.body.appendChild(menu);

    // Remove menu on click elsewhere
    setTimeout(() => {
        document.addEventListener('click', removeMenu, { once: true });
        document.addEventListener('contextmenu', removeMenu, { once: true });
    }, 0);
    function removeMenu() {
        if (menu.parentNode) menu.parentNode.removeChild(menu);
    }
}

// Action handlers for context menu
function convertToInvoice(doc) { 
    if (confirm(`Convert quotation ${doc.document_number} to invoice?`)) {
        // Implementation would create new invoice based on quotation
        window.showResponseModal('Convert to Invoice functionality coming soon', 'info'); 
    }
}

function viewClient(doc) { 
    // Navigate to client screen with this client selected
    window.location.href = `invoice-clients.php?client_id=${doc.client_id}`;
}

function deleteDocument(doc) { 
    if (doc.document_status?.toLowerCase() !== 'draft') {
        window.showResponseModal('Only draft documents can be deleted', 'error');
        return;
    }
    
    if (confirm(`Are you sure you want to delete ${doc.document_number}? This action cannot be undone.`)) {
        // Call delete API
        fetch(`../api/document-api.php?action=delete_document&document_id=${doc.document_id}`, {
            method: 'DELETE',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include'
        })
        .then(res => res.json())
        .then(result => {
            if (result.success) {
                window.showResponseModal('Document deleted successfully', 'success');
                // Refresh current section
                const activeSection = document.querySelector('.document-section:not([style*="display: none"])');
                if (activeSection) {
                    triggerSectionFetch(activeSection.id);
                }
            } else {
                window.showResponseModal(result.message || 'Failed to delete document', 'error');
            }
        })
        .catch(err => {
            window.showResponseModal('Error deleting document: ' + err.message, 'error');
        });
    }
}

function requestApproval(doc) { 
    window.showResponseModal('Approval workflow functionality coming soon', 'info'); 
}

function loadPayment(doc) { 
    window.showResponseModal('Payment loading functionality coming soon', 'info'); 
}

function createCreditNote(doc) { 
    // Open modal in credit note mode with invoice data
    window.openDocumentModal('create');
    
    // Wait for modal to load, then set up credit note
    setTimeout(() => {
        // Set document type to credit note
        const typeSelect = document.getElementById('document-type');
        if (typeSelect) {
            typeSelect.value = 'credit-note';
            // Trigger change event to update form
            typeSelect.dispatchEvent(new Event('change'));
        }
        
        // Use existing client search to populate full client details
        if (doc.client_name) {
            const clientNameInput = document.getElementById('client-name');
            if (clientNameInput) {
                // Set the client name first
                clientNameInput.value = doc.client_name;
                
                // Use the existing client search to populate all details
                searchClients(doc.client_name, function(results) {
                    if (results && results.length > 0) {
                        // Find the exact match or first result
                        const client = results.find(c => c.client_name === doc.client_name) || results[0];
                        
                        // Populate all client fields using the existing autofill logic
                        const clientIdInput = document.getElementById('client-id');
                        const clientEmailInput = document.getElementById('client-email');
                        const clientPhoneInput = document.getElementById('client-phone');
                        const clientVatInput = document.getElementById('client-vat-number');
                        const clientRegInput = document.getElementById('client-reg-number');
                        const clientAddress1Input = document.getElementById('client-address-1');
                        const clientAddress2Input = document.getElementById('client-address-2');
                        
                        if (clientIdInput) clientIdInput.value = client.client_id || doc.client_id;
                        if (clientEmailInput) clientEmailInput.value = client.client_email || '';
                        if (clientPhoneInput) clientPhoneInput.value = client.client_cell || client.client_tell || '';
                        if (clientVatInput) clientVatInput.value = client.vat_number || '';
                        if (clientRegInput) clientRegInput.value = client.registration_number || '';
                        if (clientAddress1Input) clientAddress1Input.value = client.address_line1 || '';
                        if (clientAddress2Input) clientAddress2Input.value = client.address_line2 || '';
                    }
                });
            }
        }
        
        // Set related document ID and display number
        const relatedDocInput = document.getElementById('related-document-id');
        const relatedDocDisplay = document.getElementById('related-document-number-display');
        const relatedDocNumber = document.getElementById('related-document-number');
        
        if (relatedDocInput) {
            relatedDocInput.value = doc.document_id;
        }
        if (relatedDocDisplay) {
            relatedDocDisplay.value = doc.document_number;
        }
        if (relatedDocNumber) {
            relatedDocNumber.textContent = doc.document_number;
        }
        
        // Update related document info display
        if (typeof updateRelatedDocumentInfo === 'function') {
            updateRelatedDocumentInfo();
        }
        
        // Show success message
        window.showResponseModal(`Credit note creation mode activated for invoice ${doc.document_number}`, 'success');
    }, 500);
}

function refundInvoice(doc) { 
    // Open modal in refund mode
    window.openDocumentModal('create');
    
    // Wait for modal to load, then set up refund
    setTimeout(() => {
        // Set document type to refund
        const typeSelect = document.getElementById('document-type');
        if (typeSelect) {
            typeSelect.value = 'refund';
            // Trigger change event to update form
            typeSelect.dispatchEvent(new Event('change'));
        }
        
        // Pre-populate with invoice client data
        if (doc.client_id) {
            const clientIdInput = document.getElementById('client-id');
            const clientNameInput = document.getElementById('client-name');
            const clientEmailInput = document.getElementById('client-email');
            const clientPhoneInput = document.getElementById('client-phone');
            
            if (clientIdInput) clientIdInput.value = doc.client_id;
            if (clientNameInput) clientNameInput.value = doc.client_name || '';
            if (clientEmailInput) clientEmailInput.value = doc.client_email || '';
            if (clientPhoneInput) clientPhoneInput.value = doc.client_phone || '';
        }
        
        // Set related document ID
        const relatedDocInput = document.getElementById('related-document-id');
        if (relatedDocInput) {
            relatedDocInput.value = doc.document_id;
        }
        
        // Update related document info display
        if (typeof updateRelatedDocumentInfo === 'function') {
            updateRelatedDocumentInfo();
        }
        
        // Show success message
        window.showResponseModal(`Refund creation mode activated for invoice ${doc.document_number}`, 'success');
    }, 500);
}

function sendInvoice(doc) { 
    window.showResponseModal('Email sending functionality coming soon', 'info'); 
}

function sendPaymentReminder(doc) { 
    window.showResponseModal('Payment reminder functionality coming soon', 'info'); 
}

function viewRelatedDocuments(doc) {
    // Fetch related documents
    fetch(`../api/document-api.php?action=get_related_documents&document_id=${doc.document_id}`, {
        credentials: 'include'
    })
    .then(res => res.json())
    .then(result => {
        if (result.success) {
            let message = `Related Documents for ${doc.document_number}:\n\n`;
            
            if (result.data.parent_document) {
                message += `📄 Parent Document:\n`;
                message += `   ${result.data.parent_document.document_type.toUpperCase()}: ${result.data.parent_document.document_number}\n`;
                message += `   Amount: R${result.data.parent_document.total_amount}\n`;
                message += `   Status: ${result.data.parent_document.document_status}\n\n`;
            }
            
            if (result.data.related_documents && result.data.related_documents.length > 0) {
                message += `📋 Related Documents:\n`;
                result.data.related_documents.forEach(related => {
                    message += `   ${related.document_type.toUpperCase()}: ${related.document_number}\n`;
                    message += `   Amount: R${related.total_amount}\n`;
                    message += `   Status: ${related.document_status}\n\n`;
                });
            } else {
                message += `📋 No related documents found.`;
            }
            
            window.showResponseModal(message, 'info');
        } else {
            window.showResponseModal(result.message || 'Failed to fetch related documents', 'error');
        }
    })
    .catch(err => {
        window.showResponseModal('Error fetching related documents: ' + err.message, 'error');
    });
}

/**
 * Refresh the current active table after document operations
 */
function refreshCurrentTable() {
    const activeMainTab = document.querySelector('.main-tabs .tab-button.active');
    if (activeMainTab) {
        const sectionId = activeMainTab.dataset.section;
        const activeSubtab = document.querySelector('.status-subtabs[style*="display: flex"] .subtab-button.active');
        const status = activeSubtab ? activeSubtab.dataset.status : undefined;
        
        fetchAndRenderDocuments(sectionId, status);
    }
}

// Export functions for use in other modules
window.refreshCurrentTable = refreshCurrentTable;
window.getCurrentNovaTable = getCurrentNovaTable;
window.refreshNovaTable = refreshNovaTable;
