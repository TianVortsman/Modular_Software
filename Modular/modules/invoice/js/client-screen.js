// Client Screen Logic: Handles main screen rendering, event binding, and table updates
// Now using NOVA Table component for enhanced functionality
import { openClientModal, closeModal } from './client-modal.js';
import { fetchClients } from './client-api.js';

let isLoading = false;
let currentType = 'private';
let novaTablePrivate = null;
let novaTableBusiness = null;

// NOVA Table configuration for private clients
const privateTableConfig = {
    columns: [
        { key: 'client_id', label: 'Client ID', sortable: true },
        { key: 'client_name', label: 'Client Name', sortable: true, filterable: true },
        { key: 'client_email', label: 'Email', sortable: true, filterable: true },
        { key: 'client_phone', label: 'Phone', sortable: true, filterable: true },
        { key: 'last_invoice_date', label: 'Last Invoice Date', sortable: true },
        { key: 'outstanding_amount', label: 'Outstanding Balance', sortable: true },
        { key: 'total_invoices', label: 'Total Invoices', sortable: true }
    ],
    rowsPerPage: 10,
    rowsPerPageOptions: [5, 10, 25, 50, 100],
    searchable: true,
    sortable: true,
    filterable: true,
    selectable: true,
    exportable: true,
    pagination: true,
    stickyHeader: true,
    maxHeight: null, // Let CSS handle the height
    onDoubleClick: (row) => {
        if (row.client_id) {
            openClientModal(row.client_id);
        }
    },
    onSelectionChange: (selectedRows) => {
        console.log('Selected private clients:', selectedRows);
    },
    onDataChange: (data) => {
        console.log('Private clients data changed:', data);
    },
    // Custom context menu actions
    contextMenuActions: [
        { action: 'edit', label: 'Edit Client', icon: '✏️' },
        { action: 'view', label: 'View Details', icon: '👁️' },
        { action: 'invoices', label: 'View Invoices', icon: '📄' },
        { action: 'export', label: 'Export Client', icon: '📤' },
        { action: 'delete', label: 'Delete Client', icon: '🗑️' }
    ]
};

// NOVA Table configuration for business clients
const businessTableConfig = {
    columns: [
        { key: 'client_id', label: 'Company ID', sortable: true },
        { key: 'client_name', label: 'Company Name', sortable: true, filterable: true },
        { key: 'client_email', label: 'Email', sortable: true, filterable: true },
        { key: 'client_phone', label: 'Phone', sortable: true, filterable: true },
        { key: 'last_invoice_date', label: 'Last Invoice Date', sortable: true },
        { key: 'outstanding_amount', label: 'Outstanding Balance', sortable: true },
        { key: 'total_invoices', label: 'Total Invoices', sortable: true }
    ],
    rowsPerPage: 10,
    rowsPerPageOptions: [5, 10, 25, 50, 100],
    searchable: true,
    sortable: true,
    filterable: true,
    selectable: true,
    exportable: true,
    pagination: true,
    stickyHeader: true,
    maxHeight: null, // Let CSS handle the height
    onDoubleClick: (row) => {
        if (row.client_id) {
            openClientModal(row.client_id);
        }
    },
    onSelectionChange: (selectedRows) => {
        console.log('Selected business clients:', selectedRows);
    },
    onDataChange: (data) => {
        console.log('Business clients data changed:', data);
    },
    // Custom context menu actions
    contextMenuActions: [
        { action: 'edit', label: 'Edit Company', icon: '✏️' },
        { action: 'view', label: 'View Details', icon: '👁️' },
        { action: 'invoices', label: 'View Invoices', icon: '📄' },
        { action: 'export', label: 'Export Company', icon: '📤' },
        { action: 'delete', label: 'Delete Company', icon: '🗑️' }
    ]
};

/**
 * Initialize the client screen (on page load)
 */
export async function initClientScreen() {
    console.log('Initializing client screen with NOVA tables...');
    
    // Initialize NOVA tables
    initializeNovaTables();
    
    // Wait a bit for tables to initialize
    await new Promise(resolve => setTimeout(resolve, 200));
    
    // Bind events
    bindEvents();
    
    // Load initial data
    await refreshClientTable();
}

/**
 * Initialize NOVA table instances
 */
function initializeNovaTables() {
    try {
        // Wait for NovaTable to be available
        if (!window.NovaTable) {
            console.log('Waiting for NovaTable to be available...');
            setTimeout(initializeNovaTables, 100);
            return;
        }

        // Initialize private clients table
        const privateContainer = document.getElementById('nova-table-private');
        if (privateContainer && window.NovaTable) {
            novaTablePrivate = new window.NovaTable('nova-table-private', privateTableConfig);
            console.log('NOVA table for private clients initialized');
        }

        // Initialize business clients table
        const businessContainer = document.getElementById('nova-table-business');
        if (businessContainer && window.NovaTable) {
            novaTableBusiness = new window.NovaTable('nova-table-business', businessTableConfig);
            console.log('NOVA table for business clients initialized');
        }
    } catch (error) {
        console.error('Error initializing NOVA tables:', error);
    }
}

/**
 * Convert API data to NOVA table format
 * @param {Array} clients - Raw client data from API
 * @returns {Array} - Formatted data for NOVA table
 */
function formatClientDataForNovaTable(clients) {
    if (!Array.isArray(clients)) return [];
    
    return clients.map(client => {
        // Combine first_name and last_name for private clients, or use client_name
        let displayName = client.client_name || 
                         ((client.first_name || '') + ' ' + (client.last_name || '')).trim();
        
        // Combine phone fields
        let phone = client.client_cell || client.client_tell || '';
        
        return {
            id: client.client_id, // Unique identifier for NOVA table
            client_id: client.client_id,
            client_name: displayName,
            client_email: client.client_email || '',
            client_phone: phone,
            last_invoice_date: client.last_invoice_date || '-',
            outstanding_amount: client.outstanding_amount !== undefined ? client.outstanding_amount : '-',
            total_invoices: client.total_invoices !== undefined ? client.total_invoices : '-',
            // Keep original data for reference
            _originalData: client
        };
    });
}

/**
 * Get the current active NOVA table instance
 */
function getCurrentNovaTable() {
    return currentType === 'private' ? novaTablePrivate : novaTableBusiness;
}

/**
 * Render the client table with data using NOVA table
 * @param {Array} clients
 * @param {number} total
 */
export function renderClientTable(clients, total = 0) {
    const novaTable = getCurrentNovaTable();
    if (!novaTable) {
        console.error('NOVA table not initialized for type:', currentType);
        return;
    }

    try {
        const formattedData = formatClientDataForNovaTable(clients);
        novaTable.loadData(formattedData);
        console.log(`Rendered ${formattedData.length} ${currentType} clients in NOVA table`);
    } catch (error) {
        console.error('Error rendering client table:', error);
    }
}

/**
 * Bind all event listeners (tab switching, etc.)
 */
export function bindEvents() {
    // Tab switching
    const privateBtn = document.getElementById('clientSectionButton1');
    const businessBtn = document.getElementById('clientSectionButton2');
    const section1 = document.getElementById('client-section1');
    const section2 = document.getElementById('client-section2');
    
    if (privateBtn && businessBtn && section1 && section2) {
        privateBtn.addEventListener('click', () => {
            currentType = 'private';
            privateBtn.classList.add('active');
            businessBtn.classList.remove('active');
            section1.classList.add('active');
            section2.classList.remove('active');
            refreshClientTable();
        });

        businessBtn.addEventListener('click', () => {
            currentType = 'business';
            businessBtn.classList.add('active');
            privateBtn.classList.remove('active');
            section2.classList.add('active');
            section1.classList.remove('active');
            refreshClientTable();
        });
    }

    // Add any additional event listeners here
    console.log('Client screen events bound');
}

/**
 * Handle search functionality (now handled by NOVA table)
 */
export function handleSearch(event) {
    // Search is now handled internally by NOVA table
    console.log('Search handled by NOVA table');
}

/**
 * Handle filter functionality (now handled by NOVA table)
 */
export function handleFilter(event) {
    // Filtering is now handled internally by NOVA table
    console.log('Filter handled by NOVA table');
}

/**
 * Handle pagination (now handled by NOVA table)
 */
export function handlePagination(page) {
    // Pagination is now handled internally by NOVA table
    console.log('Pagination handled by NOVA table');
}

/**
 * Handle row click (now handled by NOVA table)
 */
export function handleRowClick(event) {
    // Row clicks are now handled internally by NOVA table
    console.log('Row click handled by NOVA table');
}

/**
 * Refresh the client table with current data
 */
export async function refreshClientTable() {
    if (isLoading) return;
    
    isLoading = true;
    console.log(`Refreshing ${currentType} clients table...`);

    try {
        // Show loading state
        const novaTable = getCurrentNovaTable();
        if (novaTable) {
            novaTable.loadData([]); // Clear table while loading
        } else {
            console.warn(`NOVA table not available for type: ${currentType}`);
        }

        // Fetch data from API
        const response = await fetchClients({
            type: currentType,
            page: 1,
            limit: 1000 // Get all data, let NOVA table handle pagination
        });

        if (response.success && response.data) {
            console.log(`Fetched ${response.data.length} ${currentType} clients from API`);
            renderClientTable(response.data, response.total || response.data.length);
        } else {
            console.error('Failed to fetch clients:', response.message);
            renderClientTable([], 0);
        }
    } catch (error) {
        console.error('Error refreshing client table:', error);
        renderClientTable([], 0);
    } finally {
        isLoading = false;
    }
}

/**
 * Get selected clients from the current table
 */
export function getSelectedClients() {
    const novaTable = getCurrentNovaTable();
    if (novaTable) {
        return novaTable.getSelectedRows();
    }
    return [];
}

/**
 * Export selected clients
 */
export function exportSelectedClients() {
    const novaTable = getCurrentNovaTable();
    if (novaTable) {
        novaTable.exportSelected();
    }
}

/**
 * Clear selection in current table
 */
export function clearSelection() {
    const novaTable = getCurrentNovaTable();
    if (novaTable) {
        novaTable.clearSelection();
    }
}

/**
 * Refresh both tables (useful after data changes)
 */
export async function refreshAllTables() {
    await refreshClientTable();
}

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
    
    console.log('DOM ready and NovaTable available, initializing client screen...');
    initClientScreen();
}

// Start the initialization process
waitForNovaTableAndInit();
