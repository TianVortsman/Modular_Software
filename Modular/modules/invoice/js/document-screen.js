// Tab switching and document fetching logic for document sections
import { buildQueryParams } from '../../../public/assets/js/helpers.js';
import { searchClients } from './document-api.js';
import { searchClient } from './document-form.js';

document.addEventListener('DOMContentLoaded', function() {
    const mainTabButtons = document.querySelectorAll('.tab-button');
    const sections = document.querySelectorAll('.document-section');
    // Show only the first section by default
    sections.forEach((sec, idx) => {
        sec.style.display = idx === 0 ? '' : 'none';
    });
    mainTabButtons.forEach((btn, idx) => {
        btn.classList.toggle('active', idx === 0);
        btn.addEventListener('click', function() {
            mainTabButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            sections.forEach(sec => {
                if (sec.id === this.dataset.section) {
                    sec.style.display = '';
                    // Re-apply filter values for this section
                    applySectionFilters(sec.id);
                } else {
                    sec.style.display = 'none';
                }
            });
            // Find and activate the first subtab in this section
            const section = document.getElementById(this.dataset.section);
            if (section) {
                const subtabs = section.querySelectorAll('.subtab-button');
                subtabs.forEach((st, i) => st.classList.toggle('active', i === 0));
                const status = subtabs.length > 0 ? subtabs[0].dataset.status : undefined;
                // Always restore filters before fetching
                applySectionFilters(this.dataset.section);
                fetchAndRenderDocuments(this.dataset.section, status, filterState[this.dataset.section]);
            }
        });
    });
    // Subtab logic for all sections
    document.querySelectorAll('.document-section').forEach(section => {
        const subtabs = section.querySelectorAll('.subtab-button');
        subtabs.forEach(subtab => {
            subtab.addEventListener('click', function() {
                subtabs.forEach(st => st.classList.remove('active'));
                this.classList.add('active');
                const sectionId = section.id;
                const status = this.dataset.status;
                fetchAndRenderDocuments(sectionId, status);
            });
        });
    });
    // Initial fetch for the first section's first subtab
    const firstSection = sections[0];
    if (firstSection) {
        const firstSubtab = firstSection.querySelector('.subtab-button');
        const status = firstSubtab ? firstSubtab.dataset.status : undefined;
        fetchAndRenderDocuments(firstSection.id, status);
    }
    setupFilters();
});

// Map section IDs to document types for API
const sectionTypeMap = {
    'invoices-section': 'invoice',
    'recurring-invoices-section': 'recurring-invoice',
    'quotations-section': 'quotation',
    'vehicle-quotations-section': 'vehicle-quotation',
    'vehicle-invoices-section': 'vehicle-invoice'
};

// --- Filter state ---
const filterState = {
    'invoices-section': {},
    'recurring-invoices-section': {},
    'quotations-section': {},
    'vehicle-quotations-section': {},
    'vehicle-invoices-section': {}
};

// Helper: section to client filter IDs
const sectionClientIds = {
    'invoices-section': {
        input: 'client-name-invoices',
        hidden: 'client-id-invoices',
        dropdown: 'search-results-client-invoices'
    },
    'recurring-invoices-section': {
        input: 'client-name-recurring',
        hidden: 'client-id-recurring',
        dropdown: 'search-results-client-recurring'
    },
    'quotations-section': {
        input: 'client-name-quotations',
        hidden: 'client-id-quotations',
        dropdown: 'search-results-client-quotations'
    },
    'vehicle-quotations-section': {
        input: 'client-name-vehicle-quotations',
        hidden: 'client-id-vehicle-quotations',
        dropdown: 'search-results-client-vehicle-quotations'
    },
    'vehicle-invoices-section': {
        input: 'client-name-vehicle-invoices',
        hidden: 'client-id-vehicle-invoices',
        dropdown: 'search-results-client-vehicle-invoices'
    }
};

function applySectionFilters(sectionId) {
    const ids = sectionClientIds[sectionId];
    const state = filterState[sectionId] || {};
    // Date filters
    const section = document.getElementById(sectionId);
    if (!section) return;
    const dateFrom = section.querySelector('input[type="date"][id$="date-from"]');
    const dateTo = section.querySelector('input[type="date"][id$="date-to"]');
    if (dateFrom && state.date_from !== undefined) dateFrom.value = state.date_from;
    if (dateTo && state.date_to !== undefined) dateTo.value = state.date_to;
    // Client filter
    if (ids) {
        const clientInput = document.getElementById(ids.input);
        const clientIdInput = document.getElementById(ids.hidden);
        if (clientInput && state.client_name !== undefined) clientInput.value = state.client_name;
        if (clientIdInput && state.client_id !== undefined) clientIdInput.value = state.client_id;
    }
}

function setupFilters() {
    document.querySelectorAll('.document-section').forEach(section => {
        const sectionId = section.id;
        // Date filters
        const dateFrom = section.querySelector('input[type="date"][id$="date-from"]');
        const dateTo = section.querySelector('input[type="date"][id$="date-to"]');
        if (dateFrom) {
            dateFrom.addEventListener('change', function() {
                filterState[sectionId].date_from = this.value;
                dateFrom.value = this.value;
                triggerSectionFetch(sectionId);
            });
        }
        if (dateTo) {
            dateTo.addEventListener('change', function() {
                filterState[sectionId].date_to = this.value;
                dateTo.value = this.value;
                triggerSectionFetch(sectionId);
            });
        }
        // Client filter
        const ids = sectionClientIds[sectionId];
        if (ids) {
            const clientInput = document.getElementById(ids.input);
            const clientIdInput = document.getElementById(ids.hidden);
            const dropdown = document.getElementById(ids.dropdown);
            if (clientInput && clientIdInput && dropdown) {
                clientInput.addEventListener('input', function() {
                    customSectionClientSearch(sectionId, clientInput, clientIdInput, dropdown);
                });
                clientIdInput.addEventListener('change', function() {
                    filterState[sectionId].client_id = this.value;
                    clientIdInput.value = this.value;
                    triggerSectionFetch(sectionId);
                });
            }
        }
        // Add Clear Filters button
        let clearBtn = section.querySelector('.clear-filters-btn');
        if (!clearBtn) {
            clearBtn = document.createElement('button');
            clearBtn.textContent = 'Clear Filters';
            clearBtn.className = 'clear-filters-btn';
            clearBtn.type = 'button';
            clearBtn.style.marginLeft = 'auto';
            clearBtn.onclick = function() {
                // Reset filter state and inputs
                filterState[sectionId] = {};
                applySectionFilters(sectionId);
                triggerSectionFetch(sectionId);
            };
            const filterBar = section.querySelector('.invoice-filter');
            if (filterBar) filterBar.appendChild(clearBtn);
        }
    });
}

function customSectionClientSearch(sectionId, input, hidden, dropdown) {
    const query = input.value.trim();
    if (query.length < 2) {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        hidden.value = '';
        filterState[sectionId].client_id = '';
        // Do NOT clear input.value here, so user can type
        // Do NOT clear client_name here, so it persists
        triggerSectionFetch(sectionId);
        return;
    }
    searchClients(query, function(results) {
        dropdown.innerHTML = '';
        if (results && results.length > 0) {
            dropdown.style.display = 'block';
            results.forEach((item, idx) => {
                const div = document.createElement('div');
                div.classList.add('search-result-client');
                div.textContent = item.client_name + (item.client_email ? ' (' + item.client_email + ')' : '');
                div.onclick = function () {
                    hidden.value = item.client_id;
                    input.value = item.client_name;
                    filterState[sectionId].client_id = item.client_id;
                    filterState[sectionId].client_name = item.client_name;
                    dropdown.style.display = 'none';
                    triggerSectionFetch(sectionId);
                };
                if (idx === 0) div.classList.add('highlight');
                dropdown.appendChild(div);
            });
        } else {
            dropdown.style.display = 'block';
            const noResults = document.createElement('div');
            noResults.textContent = 'No clients found';
            dropdown.appendChild(noResults);
        }
    });
}

function triggerSectionFetch(sectionId) {
    // Find active subtab
    const section = document.getElementById(sectionId);
    const activeSubtab = section ? section.querySelector('.subtab-button.active') : null;
    const status = activeSubtab ? activeSubtab.dataset.status : undefined;
    fetchAndRenderDocuments(sectionId, status, filterState[sectionId]);
}

// Patch fetchAndRenderDocuments to accept filters
function fetchAndRenderDocuments(sectionId, status, filters = {}) {
    const type = sectionTypeMap[sectionId];
    if (!type) return;
    const paramsObj = { action: 'list_documents', type };
    if (status && status !== 'all') paramsObj.status = status;
    if (filters.date_from) paramsObj.date_from = filters.date_from;
    if (filters.date_to) paramsObj.date_to = filters.date_to;
    if (filters.client_id) paramsObj.client_id = filters.client_id;
    const params = buildQueryParams(paramsObj);
    const url = `../api/document-api.php?${params.toString()}`;
    fetch(url)
        .then(res => res.json().catch(() => {
            if (window.showResponseModal) {
                window.showResponseModal('Server error: Invalid response format', 'error');
            }
            renderDocumentRows(sectionId, []);
            throw new Error('Invalid JSON');
        }))
        .then(data => {
            if (!data.success || !Array.isArray(data.data)) {
                if (typeof window.handleApiResponse === 'function') {
                    window.handleApiResponse(data);
                } else if (window.showResponseModal) {
                    window.showResponseModal(data.message || 'Failed to load documents', 'error');
                }
                renderDocumentRows(sectionId, []);
                return;
            }
            renderDocumentRows(sectionId, data.data);
        })
        .catch((err) => {
            if (window.showResponseModal) {
                window.showResponseModal(err.message || 'Failed to load documents', 'error');
            }
            renderDocumentRows(sectionId, []);
        });
}

function renderDocumentRows(sectionId, documents) {
    let tbodyId = '';
    switch (sectionId) {
        case 'invoices-section': tbodyId = 'invoice-body'; break;
        case 'recurring-invoices-section': tbodyId = 'recurring-invoice-body'; break;
        case 'quotations-section': tbodyId = 'quotation-body'; break;
        case 'vehicle-quotations-section': tbodyId = 'vehicle-quotation-body'; break;
        case 'vehicle-invoices-section': tbodyId = 'vehicle-invoice-body'; break;
        default: return;
    }
    const tbody = document.getElementById(tbodyId);
    if (!tbody) return;
    tbody.innerHTML = '';
    if (!documents.length) {
        const tr = document.createElement('tr');
        const td = document.createElement('td');
        td.colSpan = 10;
        td.textContent = 'No documents found.';
        tr.appendChild(td);
        tbody.appendChild(tr);
        return;
    }
    documents.forEach(doc => {
        const tr = document.createElement('tr');
        // Render columns based on section, no Actions column
        if (sectionId === 'invoices-section') {
            tr.innerHTML = `
                <td>${doc.document_number || ''}</td>
                <td>${doc.client_name || ''}</td>
                <td>${doc.issue_date || ''}</td>
                <td>${doc.updated_at || ''}</td>
                <td>${doc.document_status || ''}</td>
                <td>${doc.total_amount || ''}</td>
                <td>${doc.due_date || ''}</td>
            `;
        } else if (sectionId === 'recurring-invoices-section') {
            tr.innerHTML = `
                <td>${doc.document_number || ''}</td>
                <td>${doc.client_name || ''}</td>
                <td>${doc.start_date || ''}</td>
                <td>${doc.next_generation || ''}</td>
                <td>${doc.frequency || ''}</td>
                <td>${doc.document_status || ''}</td>
            `;
        } else if (sectionId === 'quotations-section') {
            tr.innerHTML = `
                <td>${doc.document_number || ''}</td>
                <td>${doc.client_name || ''}</td>
                <td>${doc.issue_date || ''}</td>
                <td>${doc.document_status || ''}</td>
                <td>${doc.total_amount || ''}</td>
            `;
        } else if (sectionId === 'vehicle-quotations-section') {
            tr.innerHTML = `
                <td>${doc.document_number || ''}</td>
                <td>${doc.client_name || ''}</td>
                <td>${doc.vehicle || ''}</td>
                <td>${doc.issue_date || ''}</td>
                <td>${doc.document_status || ''}</td>
                <td>${doc.total_amount || ''}</td>
            `;
        } else if (sectionId === 'vehicle-invoices-section') {
            tr.innerHTML = `
                <td>${doc.document_number || ''}</td>
                <td>${doc.client_name || ''}</td>
                <td>${doc.vehicle || ''}</td>
                <td>${doc.issue_date || ''}</td>
                <td>${doc.document_status || ''}</td>
                <td>${doc.total_amount || ''}</td>
            `;
        }
        // Add context menu event
        tr.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            showDocumentContextMenu(e, doc, sectionId);
        });
        tbody.appendChild(tr);
    });
}

function showDocumentContextMenu(e, doc, sectionId) {
    // Remove any existing menu
    const oldMenu = document.getElementById('document-context-menu');
    if (oldMenu) oldMenu.remove();

    // Build menu options based on type and status
    const isDraft = (doc.document_status && doc.document_status.toLowerCase() === 'draft');
    let options = [];
    if (sectionId === 'quotations-section') {
        options.push({ label: 'View', action: () => viewDocument(doc) });
        if (isDraft) options.push({ label: 'Edit', action: () => editDocument(doc) });
        options.push({ label: 'Convert to Invoice', action: () => convertToInvoice(doc) });
        options.push({ label: 'View Client', action: () => viewClient(doc) });
        if (isDraft) options.push({ label: 'Delete', action: () => deleteDocument(doc) });
        options.push({ label: 'Request Approval', action: () => requestApproval(doc) });
    } else if (sectionId === 'invoices-section' || sectionId === 'vehicle-invoices-section') {
        options.push({ label: 'View', action: () => viewDocument(doc) });
        if (isDraft) options.push({ label: 'Edit', action: () => editDocument(doc) });
        options.push({ label: 'Load Payment', action: () => loadPayment(doc) });
        options.push({ label: 'Create Credit Note', action: () => createCreditNote(doc) });
        options.push({ label: 'Refund Invoice', action: () => refundInvoice(doc) });
        options.push({ label: 'Send Invoice', action: () => sendInvoice(doc) });
        options.push({ label: 'Send Payment Reminder', action: () => sendPaymentReminder(doc) });
        options.push({ label: 'View Client', action: () => viewClient(doc) });
    } else {
        options.push({ label: 'View', action: () => viewDocument(doc) });
        if (isDraft) options.push({ label: 'Edit', action: () => editDocument(doc) });
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

// Placeholder action handlers
function viewDocument(doc) { window.showResponseModal('View: ' + doc.document_number, 'info'); }
function editDocument(doc) { window.showResponseModal('Edit: ' + doc.document_number, 'info'); }
function convertToInvoice(doc) { window.showResponseModal('Convert to Invoice: ' + doc.document_number, 'info'); }
function viewClient(doc) { window.showResponseModal('View Client: ' + doc.client_id, 'info'); }
function deleteDocument(doc) { window.showResponseModal('Delete: ' + doc.document_number, 'warning'); }
function requestApproval(doc) { window.showResponseModal('Request Approval: ' + doc.document_number, 'info'); }
function loadPayment(doc) { window.showResponseModal('Load Payment: ' + doc.document_number, 'info'); }
function createCreditNote(doc) { window.showResponseModal('Create Credit Note: ' + doc.document_number, 'info'); }
function refundInvoice(doc) { window.showResponseModal('Refund Invoice: ' + doc.document_number, 'info'); }
function sendInvoice(doc) { window.showResponseModal('Send Invoice: ' + doc.document_number, 'info'); }
function sendPaymentReminder(doc) { window.showResponseModal('Send Payment Reminder: ' + doc.document_number, 'info'); }
