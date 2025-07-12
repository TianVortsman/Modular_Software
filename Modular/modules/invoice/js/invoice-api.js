

// API function: fetch customers and companies from the backend
function fetchClientsAndCompanies(query, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '../api/customers.php?action=search_all&query=' + encodeURIComponent(query), true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            let results = [];
            if (xhr.status === 200) {
                try {
                    results = JSON.parse(xhr.responseText);
                } catch (e) {
                    results = [];
                }
                callback(null, results);
            } else {
                callback('Error searching for customers or companies', []);
            }
        }
    };
    xhr.send();
}

// API: Fetch company info from backend
async function fetchCompanyInfoApi() {
    try {
        const companyRes = await fetch('../api/invoice_modal.php?action=get_company_info', { credentials: 'include' });
        const company = await companyRes.json();
        if (!company.success || !company.data) throw new Error('No company data returned');
        return company.data;
    } catch (err) {
        throw new Error('Error fetching company info: ' + (err.message || err));
    }
}

// API: Fetch invoice number from backend
async function fetchInvoiceNumberApi() {
    try {
        const invNumRes = await fetch('../api/invoice_modal.php?action=get_invoice_number', { credentials: 'include' });
        const invNum = await invNumRes.json();
        if (!invNum.success || !invNum.data) throw new Error('No invoice number returned');
        return invNum.data.invoice_number;
    } catch (err) {
        throw new Error('Error fetching invoice number: ' + (err.message || err));
    }
}

// API: Search salespeople by query
async function fetchSalespeopleApi(query) {
    try {
        const res = await fetch(`../api/invoice_modal.php?action=search_salesperson&query=${encodeURIComponent(query)}`, { credentials: 'include' });
        const data = await res.json();
        return data;
    } catch (err) {
        return { success: false, message: 'Error searching salespeople', results: [] };
    }
}

// Calls the API to generate the invoice PDF using the form data
async function generateInvoicePDF() {
    try {
        showLoadingModal('Generating PDF...');
        const data = getInvoiceFormData();
        const response = await fetch('../api/generate_invoice_pdf.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            credentials: 'include',
            body: JSON.stringify(data)
        });
        const result = await response.json();
        hideLoadingModal();
        if (result.success && result.url) {
            showResponseModal('PDF generated! Opening...', 'success');
            window.open(result.url, '_blank');
        } else {
            showResponseModal(result.message || 'Failed to generate PDF', 'error');
        }
    } catch (err) {
        hideLoadingModal();
        showResponseModal('Error generating PDF: ' + (err.message || err), 'error');
    }
}

// This function should be moved to invoice-api.js, as it is responsible for making an API call (not UI logic).
// Only keep a thin wrapper in invoice-form.js if you need to trigger it from a button or form event.
// Here is the modular API function for invoice-api.js:
/*
* Save invoice to backend via API.
* @param {Object} formData - The invoice data object (from getInvoiceFormData).
* @param {string} partyType - 'company' or 'customer'
* @param {Object} [recurringDetails] - Optional recurring invoice details.
* @returns {Promise<Object>} - API response.
*/
async function saveInvoiceApi(formData, partyType, recurringDetails = {}) {
   // Determine invoice type and recurring status
   const invoiceType = document.getElementById('invoice-type') ? document.getElementById('invoice-type').value : 'invoice';
   const isRecurring = invoiceType === 'recurring';

   let data = {};
   if (partyType === 'company' || partyType === 'customer') {
       data = {
           invoice_id: formData.invoice_id, // Always include invoice_id if present
           client_type: formData.client_type,
           customer_id: formData.customer_id,
           company_id: formData.company_id,
           customer_name: formData.customer_name,
           email: formData.email,
           phone: formData.phone,
           vat_number: formData.vat_number,
           registration_number: formData.registration_number,
           address1: formData.address1,
           address2: formData.address2,
           salesperson: formData.salesperson,
           invoice_number: formData.invoice_number,
           items: formData.items,
           totals: formData.totals,
           notes: formData.notes,
           current_number: formData.invoice_number ? formData.invoice_number.replace(/\D/g, '') : '',
           invoice_type: invoiceType,
           is_recurring: isRecurring,
           pay_in_days: formData.pay_in_days,
           recurring_frequency: formData.recurring_frequency,
           recurring_start_date: formData.recurring_start_date,
           recurring_end_date: formData.recurring_end_date,
           next_generation: formData.next_generation,
           last_generated: formData.last_generated,
           ...recurringDetails
       };
   } else {
       return { success: false, message: 'Please select a customer or company before creating an invoice.' };
   }

   console.log('[saveInvoiceApi] Sending data:', data);
   try {
       const response = await fetch('../api/invoice_modal.php?action=save_invoice', {
           method: 'POST',
           headers: { 'Content-Type': 'application/json' },
           credentials: 'include',
           body: JSON.stringify(data)
       });
       const result = await response.json();
       console.log('[saveInvoiceApi] Received response:', result);
       return result;
   } catch (err) {
       return { success: false, message: 'Error saving invoice: ' + (err.message || err) };
   }
}

// API function: fetch product search results
function fetchProductSearchResults(field, searchTerm, callback) {
    const xhr = new XMLHttpRequest();
    xhr.open('GET', '../api/products.php?action=search&field=' + field + '&query=' + encodeURIComponent(searchTerm), true);
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4) {
            let results = [];
            try { results = JSON.parse(xhr.responseText); } catch (e) { results = []; }
            callback(results);
        }
    };
    xhr.send();
}


// API function: fetch invoice data and set form
async function fetchAndSetInvoice(invoiceId) {
    try {
        // Show loading modal if available
        if (typeof LoadingModal !== 'undefined') LoadingModal.show('Loading invoice...');
        const response = await fetch(`../api/invoice_modal.php?action=fetch_invoice&invoice_id=${encodeURIComponent(invoiceId)}`, {
            method: 'GET',
            credentials: 'include'
        });
        const result = await response.json();

        // Hide loading modal
        if (typeof LoadingModal !== 'undefined') LoadingModal.hide();

        if (result.success && result.data) {
            // setInvoiceFormData should be defined elsewhere and handle the data structure
            setInvoiceFormData(result.data);
            // Optionally show a success modal
            if (typeof ResponseModal !== 'undefined') ResponseModal.success('Invoice loaded successfully.');
        } else {
            // Show error modal
            if (typeof ResponseModal !== 'undefined') {
                ResponseModal.error(result.message || 'Failed to fetch invoice.');
            } else {
                alert(result.message || 'Failed to fetch invoice.');
            }
        }
    } catch (err) {
        if (typeof LoadingModal !== 'undefined') LoadingModal.hide();
        if (typeof ResponseModal !== 'undefined') {
            ResponseModal.error('Error fetching invoice: ' + (err.message || err));
        } else {
            alert('Error fetching invoice: ' + (err.message || err));
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

export {
  fetchDashboardCards,
  fetchInvoices,
  fetchRecurringInvoices,
  fetchInvoiceChartData,
  fetchCompanyInfoApi,
  fetchNextQuotationNumberPreview,
  fetchNextInvoiceNumberPreview,
  fetchClientsAndCompanies,
  fetchSalespeopleApi,
  fetchProductSearchResults,
  generateInvoicePDF,
  saveInvoiceApi,
  fetchAndSetInvoice
};



