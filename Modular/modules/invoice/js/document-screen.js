// Tab switching and document fetching logic for document sections
import { buildQueryParams } from '../../../public/assets/js/helpers.js';
import { searchClients } from './document-api.js';
import { fetchAndSetDocument } from './document-api.js';

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
        dropdown.classList.remove('active');
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
            dropdown.classList.add('active');
            results.forEach((item, idx) => {
                const div = document.createElement('div');
                div.classList.add('search-result-client');
                
                // Create better display text based on client type
                let displayText = '';
                let subtitleText = '';
                
                if (item.client_type === 'business') {
                    displayText = item.client_name;
                    subtitleText = 'Business Client';
                } else {
                    // Private client
                    displayText = item.client_name;
                    if (item.first_name && item.last_name) {
                        subtitleText = `${item.first_name} ${item.last_name}`;
                    }
                }
                
                // Add email if available
                if (item.client_email) {
                    subtitleText += subtitleText ? ` • ${item.client_email}` : item.client_email;
                }
                
                // Create HTML structure
                div.innerHTML = `
                    <div class="client-result-name">${displayText}</div>
                    ${subtitleText ? `<div class="client-result-details">${subtitleText}</div>` : ''}
                `;
                
                div.onclick = function () {
                    hidden.value = item.client_id;
                    input.value = item.client_name;
                    filterState[sectionId].client_id = item.client_id;
                    filterState[sectionId].client_name = item.client_name;
                    dropdown.style.display = 'none';
                    dropdown.classList.remove('active');
                    triggerSectionFetch(sectionId);
                };
                if (idx === 0) div.classList.add('highlight');
                dropdown.appendChild(div);
            });
        } else {
            dropdown.style.display = 'block';
            dropdown.classList.add('active');
            const noResults = document.createElement('div');
            noResults.classList.add('search-no-results');
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
        // Add double-click event to open modal and autofill
        tr.addEventListener('dblclick', function() {
            openDocumentForEdit(doc.document_id);
        });
        // Add context menu event
        tr.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            showDocumentContextMenu(e, doc, sectionId);
        });
        tbody.appendChild(tr);
    });
}

// Helper to fetch and open document in modal
async function openDocumentForEdit(documentId) {
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
