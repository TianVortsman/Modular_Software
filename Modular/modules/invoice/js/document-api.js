// Refactored for new modal structure and DB schema
// All API payloads, response handling, and data mapping now use new client/document structure and field names

// API function: search clients from the backend (unified)
function searchClients(query, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '../api/client-api.php?action=search&query=' + encodeURIComponent(query), true);
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

// API function: search salespeople from the backend
function searchSalespeople(query, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '../api/invoice_modal.php?action=search_salesperson&query=' + encodeURIComponent(query), true);
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
    xhr.open('GET', '../api/products.php?action=search&query=' + encodeURIComponent(query), true);
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
    const data = {
        document_id: formData.document_id,
        client_id: formData.client_id,
        client_name: formData.client_name,
        client_email: formData.client_email,
        client_phone: formData.client_phone,
        vat_number: formData.vat_number,
        registration_number: formData.registration_number,
        address1: formData.address1,
        address2: formData.address2,
        document_type: formData.document_type,
        document_number: formData.document_number,
        issue_date: formData.issue_date,
        document_status: formData.document_status,
        pay_in_days: formData.pay_in_days,
        client_purchase_order_number: formData.client_purchase_order_number,
        salesperson_name: formData.salesperson_name,
        salesperson_id: formData.salesperson_id,
        public_note: formData.public_note,
        private_note: formData.private_note,
        foot_note: formData.foot_note,
        items: formData.items,
        subtotal: formData.subtotal,
        tax_amount: formData.tax_amount,
        total_amount: formData.total_amount,
        ...recurringDetails
    };
    try {
        const response = await fetch('../api/document_modal.php?action=save_document', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(data)
        });
        const result = await response.json();
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
        if (typeof LoadingModal !== 'undefined') LoadingModal.hide();
        if (result.success && result.data) {
            setDocumentFormData(result.data);
            if (typeof ResponseModal !== 'undefined') ResponseModal.success('Document loaded successfully.');
        } else {
            if (typeof ResponseModal !== 'undefined') {
                ResponseModal.error(result.message || 'Failed to fetch document.');
            } else {
                alert(result.message || 'Failed to fetch document.');
            }
        }
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
    const res = await fetch(`../api/dashboard-api.php?action=get_dashboard_cards&range=${encodeURIComponent(range)}`);
    return await res.json();
}

async function fetchInvoices(range, filters = {}, search = '', pagination = {}) {
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
    return await res.json();
}

async function fetchRecurringInvoices(range) {
    const res = await fetch(`../api/dashboard-api.php?action=get_recurring_invoices&range=${encodeURIComponent(range)}`);
    return await res.json();
}

async function fetchInvoiceChartData(range) {
    const res = await fetch(`../api/dashboard-api.php?action=get_invoice_chart_data&range=${encodeURIComponent(range)}`);
    return await res.json();
}

function generateInvoicePDF() {
    if (typeof showResponseModal === 'function') {
        showResponseModal('PDF generation not yet implemented.', 'error');
    } else {
        alert('PDF generation not yet implemented.');
    }
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
    generateInvoicePDF
};



