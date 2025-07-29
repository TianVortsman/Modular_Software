// Refactored for new modal structure and DB schema
// All API payloads, response handling, and data mapping now use new client/document structure and field names

// API function: search clients from the backend (unified)
function searchClients(query, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '../api/client-api.php?action=search&search=' + encodeURIComponent(query), true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            let results = [];
            if (xhr.status === 200) {
                try { 
                    const response = JSON.parse(xhr.responseText);
                    // Handle standardized API response format
                    if (response && response.success && response.data) {
                        results = response.data;
                    } else if (Array.isArray(response)) {
                        // Fallback for direct array response
                        results = response;
                    }
                } catch (e) {
                    console.error('Client search API response parse error:', e);
                }
            } else {
                console.error('Client search API failed with status:', xhr.status);
            }
            callback(results);
        }
    };
    xhr.send();
}

// API function: search salespeople from the backend
function searchSalespeople(query, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '../api/document-api.php?action=search_salesperson&query=' + encodeURIComponent(query), true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            let results = [];
            if (xhr.status === 200) {
                try { results = JSON.parse(xhr.responseText); } catch (e) {}
            }
            callback(results);
        }
    };
    xhr.send();
}

// API function: search products from the backend
function searchProducts(query, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '../api/document-api.php?action=search_product&query=' + encodeURIComponent(query), true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            let results = [];
            if (xhr.status === 200) {
                try { results = JSON.parse(xhr.responseText); } catch (e) {}
            }
            callback(results);
        }
    };
    xhr.send();
}

// API: Save document (create/update)
async function saveDocumentApi(formData, recurringDetails = {}) {
    const isDraft = (formData.document_status && formData.document_status.toLowerCase() === 'draft');
    const data = {
        ...formData,
        ...(isDraft ? { document_number: formData.document_number } : {}),
        ...recurringDetails
    };
    try {
        let response;
        const hasDocumentId = formData.document_id && formData.document_id !== '' && formData.document_id !== '0';
        
        console.log('[saveDocumentApi] Document ID check:', {
            document_id: formData.document_id,
            hasDocumentId: hasDocumentId,
            action: hasDocumentId ? 'UPDATE' : 'CREATE'
        });
        
        if (hasDocumentId) {
            // Update existing document
            response = await fetch(`../api/document_modal.php?action=update_document&document_id=${encodeURIComponent(formData.document_id)}`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data)
            });
        } else {
            // Create new document
            response = await fetch('../api/document_modal.php?action=save_document', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify(data)
            });
        }
        const result = await response.json();
        if (result.success && result.data && result.data.document_number) {
            const docNumInput = document.getElementById('document-number');
            if (docNumInput) docNumInput.value = result.data.document_number;
        }
        return result;
    } catch (err) {
        return { success: false, message: 'Error saving document: ' + (err.message || err) };
    }
}

// API: Fetch and set document data
async function fetchAndSetDocument(documentId) {
    try {
        if (typeof LoadingModal !== 'undefined') LoadingModal.show('Loading document...');
        const response = await fetch(`../api/document_modal.php?action=fetch_document&document_id=${encodeURIComponent(documentId)}`, {
            method: 'GET',
            credentials: 'include'
        });
        const result = await response.json();
        window.handleApiResponse(result);
    } catch (err) {
        if (typeof LoadingModal !== 'undefined') LoadingModal.hide();
        if (typeof ResponseModal !== 'undefined') {
            ResponseModal.error('Error fetching document: ' + (err.message || err));
        } else {
            alert('Error fetching document: ' + (err.message || err));
        }
    }
}

async function fetchNextQuotationNumberPreview() {
    const res = await fetch('../api/invoice_modal.php?action=preview_quotation_number', { credentials: 'include' });
    const data = await res.json();
    if (!data.success || !data.number) throw new Error('No preview quotation number returned');
    return data.number;
}

async function fetchNextInvoiceNumberPreview() {
    const res = await fetch('../api/invoice_modal.php?action=preview_invoice_number', { credentials: 'include' });
    const data = await res.json();
    if (!data.success || !data.number) throw new Error('No preview invoice number returned');
    return data.number;
}

// Add dashboard-related API functions for dashboard.js

async function fetchDashboardCards(range) {
    try {
        const res = await fetch(`../api/dashboard-api.php?action=get_dashboard_cards&range=${encodeURIComponent(range)}`);
        const data = await res.json();
        window.handleApiResponse(data);
        return data;
    } catch (err) {
        if (typeof ResponseModal !== 'undefined') {
            ResponseModal.error('Failed to load dashboard cards: ' + (err.message || err));
        } else {
            alert('Failed to load dashboard cards: ' + (err.message || err));
        }
        return { success: false, message: err.message };
    }
}

async function fetchInvoices(range, filters = {}, search = '', pagination = {}) {
    try {
        const params = new URLSearchParams();
        params.append('action', 'get_recent_invoices');
        params.append('range', range);
        Object.entries(filters).forEach(([key, value]) => {
            if (value !== undefined && value !== null && value !== '') {
                params.append(key, value);
            }
        });
        if (search.trim()) {
            params.append('search', search.trim());
        }
        const { page, limit } = pagination;
        if (page) params.append('page', page);
        if (limit) params.append('limit', limit);
        const res = await fetch(`../api/dashboard-api.php?${params.toString()}`);
        const data = await res.json();
        window.handleApiResponse(data);
        return data;
    } catch (err) {
        if (typeof ResponseModal !== 'undefined') {
            ResponseModal.error('Failed to load invoices: ' + (err.message || err));
        } else {
            alert('Failed to load invoices: ' + (err.message || err));
        }
        return { success: false, message: err.message };
    }
}

async function fetchRecurringInvoices(range) {
    const res = await fetch(`../api/dashboard-api.php?action=get_recurring_invoices&range=${encodeURIComponent(range)}`);
    return await res.json();
}

async function fetchInvoiceChartData(range) {
    const res = await fetch(`../api/dashboard-api.php?action=get_invoice_chart_data&range=${encodeURIComponent(range)}`);
    return await res.json();
}

// --- PDF Preview ---
export async function previewDocumentPDF(formData) {
    const payload = {
        ...formData,
        preview: true
    };
    const response = await fetch('../api/generate-document-pdf.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(payload)
    });
    return await response.json();
}

export {
    searchClients,
    searchSalespeople,
    searchProducts,
    saveDocumentApi,
    fetchAndSetDocument,
    fetchDashboardCards,
    fetchInvoices,
    fetchRecurringInvoices,
    fetchInvoiceChartData,
};



