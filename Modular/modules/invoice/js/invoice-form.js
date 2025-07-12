// Form function: handles UI and populates fields
import { fetchClientsAndCompanies, fetchSalespeopleApi, fetchProductSearchResults } from './invoice-api.js';
import { openInvoiceModal } from './invoice-modal.js';

function searchClientOrCompany(inputElement) {
    const query = inputElement.value.trim();
    const resultsContainer = document.getElementById('search-results-customer');
    const dropdowns = document.querySelectorAll('.search-customer-dropdown');
    console.log('[searchClientOrCompany] Query:', query);
    // Hide dropdowns and clear results if query too short
    if (query.length < 2) {
        dropdowns.forEach(dropdown => { dropdown.style.display = 'none'; });
        resultsContainer.innerHTML = '';
        return;
    }
    // Remove previous keydown listeners to avoid stacking
    inputElement.onkeydown = null;
    fetchClientsAndCompanies(query, function (error, results) {
        console.log('[searchClientOrCompany] fetchClientsAndCompanies results:', results);
        resultsContainer.innerHTML = '';
        if (error) {
            dropdowns.forEach(dropdown => { dropdown.style.display = 'block'; });
            const errorDiv = document.createElement('div');
            errorDiv.textContent = error;
            resultsContainer.appendChild(errorDiv);
            return;
        }
        if (results && results.length > 0) {
            dropdowns.forEach(dropdown => { dropdown.style.display = 'block'; });
            results.forEach(item => {
                console.log('[searchClientOrCompany] Search result item:', item);
                const div = document.createElement('div');
                div.classList.add('search-result-customer');
                let displayName = '';
                if (item.company_name) {
                    displayName = item.company_name;
                } else if (item.customer_name) {
                    displayName = item.customer_name;
                } else if (item.name) {
                    displayName = item.name;
                } else if (item.email) {
                    displayName = item.email;
                } else {
                    displayName = 'Unknown';
                }
                div.textContent = displayName + (item.type === 'company' ? ' (Company)' : ' (Customer)');
                div.onclick = function () {
                    console.log('[searchClientOrCompany] Clicked item:', item);
                    window.selectedPartyType = item.type; // 'company' or 'customer'
                    window.selectedClientType = item.type; // for payload
                    if (item.type === 'company') {
                        var companyIdInput = document.getElementById('company-id');
                        if (companyIdInput) {
                            companyIdInput.value = item.company_id || '';
                            console.log('[searchClientOrCompany] Setting company-id to:', companyIdInput.value);
                        } else {
                            console.warn('[searchClientOrCompany] company-id input not found!');
                        }
                        var customerIdInput = document.getElementById('customer-id');
                        if (customerIdInput) {
                            customerIdInput.value = '';
                            console.log('[searchClientOrCompany] Clearing customer-id');
                        } else {
                            console.warn('[searchClientOrCompany] customer-id input not found!');
                        }
                    } else {
                        var customerIdInput = document.getElementById('customer-id');
                        if (customerIdInput) {
                            customerIdInput.value = item.customer_id || item.id || '';
                            console.log('[searchClientOrCompany] Setting customer-id to:', customerIdInput.value, 'from', item);
                        } else {
                            console.warn('[searchClientOrCompany] customer-id input not found!');
                        }
                        var companyIdInput = document.getElementById('company-id');
                        if (companyIdInput) {
                            companyIdInput.value = '';
                            console.log('[searchClientOrCompany] Clearing company-id');
                        } else {
                            console.warn('[searchClientOrCompany] company-id input not found!');
                        }
                    }
                    console.log('[searchClientOrCompany] After set: customer-id:', document.getElementById('customer-id') ? document.getElementById('customer-id').value : 'null', 'company-id:', document.getElementById('company-id') ? document.getElementById('company-id').value : 'null');
                    document.getElementById('customer-name').value = item.customer_name || item.company_name || item.name || '';
                    document.getElementById('customer-email').value = item.contact_email || item.email || '';
                    document.getElementById('customer-phone').value = item.contact_phone || item.phone || '';
                    document.getElementById('customer-vat-number').value = item.vat_number || '';
                    document.getElementById('customer-reg-number').value = item.registration_number || '';
                    document.getElementById('Customer-adress-1').value = item.address_line1 || '';
                    document.getElementById('Customer-adress-2').value = item.address_line2 || '';
                    resultsContainer.innerHTML = '';
                    dropdowns.forEach(dropdown => { dropdown.style.display = 'none'; });
                };
                resultsContainer.appendChild(div);
            });
            inputElement.onkeydown = function (e) {
                if (e.key === 'Tab' && results.length > 0) {
                    e.preventDefault();
                    const firstItem = results[0];
                    console.log('[searchClientOrCompany] Tab select search result item:', firstItem);
                    window.selectedPartyType = firstItem.type;
                    window.selectedClientType = firstItem.type;
                    if (firstItem.type === 'company') {
                        var companyIdInput = document.getElementById('company-id');
                        if (companyIdInput) {
                            companyIdInput.value = firstItem.company_id || '';
                            console.log('[searchClientOrCompany] Setting company-id to:', companyIdInput.value);
                        } else {
                            console.warn('[searchClientOrCompany] company-id input not found!');
                        }
                        var customerIdInput = document.getElementById('customer-id');
                        if (customerIdInput) {
                            customerIdInput.value = '';
                            console.log('[searchClientOrCompany] Clearing customer-id');
                        } else {
                            console.warn('[searchClientOrCompany] customer-id input not found!');
                        }
                    } else {
                        var customerIdInput = document.getElementById('customer-id');
                        if (customerIdInput) {
                            customerIdInput.value = firstItem.customer_id || firstItem.id || '';
                            console.log('[searchClientOrCompany] Setting customer-id to:', customerIdInput.value, 'from', firstItem);
                        } else {
                            console.warn('[searchClientOrCompany] customer-id input not found!');
                        }
                        var companyIdInput = document.getElementById('company-id');
                        if (companyIdInput) {
                            companyIdInput.value = '';
                            console.log('[searchClientOrCompany] Clearing company-id');
                        } else {
                            console.warn('[searchClientOrCompany] company-id input not found!');
                        }
                    }
                    console.log('[searchClientOrCompany] After Tab set: customer-id:', document.getElementById('customer-id') ? document.getElementById('customer-id').value : 'null', 'company-id:', document.getElementById('company-id') ? document.getElementById('company-id').value : 'null');
                    document.getElementById('customer-name').value = firstItem.customer_name || firstItem.company_name || firstItem.name || '';
                    document.getElementById('customer-email').value = firstItem.contact_email || firstItem.email || '';
                    document.getElementById('customer-phone').value = firstItem.contact_phone || firstItem.phone || '';
                    document.getElementById('customer-vat-number').value = firstItem.vat_number || '';
                    document.getElementById('customer-reg-number').value = firstItem.registration_number || '';
                    document.getElementById('Customer-adress-1').value = firstItem.address_line1 || '';
                    document.getElementById('Customer-adress-2').value = firstItem.address_line2 || '';
                    resultsContainer.innerHTML = '';
                    dropdowns.forEach(dropdown => { dropdown.style.display = 'none'; });
                }
            };
        } else {
            dropdowns.forEach(dropdown => { dropdown.style.display = 'block'; });
            const noResults = document.createElement('div');
            noResults.textContent = 'No customers or companies found';
            resultsContainer.appendChild(noResults);
        }
    });
}

// Form: Populate company info and invoice number in the modal
async function populateCompanyInfoAndInvoiceNumber() {
    try {
        // Fetch and fill company info
        const companyData = await fetchCompanyInfoApi();
        document.getElementById('company-name-display').textContent = companyData.company_name || '';
        document.getElementById('company-address-display').textContent = companyData.company_address || '';
        document.getElementById('company-email-display').textContent = companyData.company_email || '';
        document.getElementById('company-phone-display').textContent = companyData.company_phone || '';
        document.getElementById('company-vat-display').textContent = 'VAT: ' + (companyData.vat_number || '');
        document.getElementById('company-reg-display').textContent = 'Reg: ' + (companyData.registration_number || '');

        // Autofill VAT and registration number fields if company is selected as customer
        const customerName = document.getElementById('customer-name').value;
        if (customerName && customerName === companyData.company_name) {
            document.getElementById('customer-vat-number').value = companyData.vat_number || '';
            document.getElementById('customer-reg-number').value = companyData.registration_number || '';
        }

        // Fetch and fill invoice number
        const invoiceNumber = await fetchInvoiceNumberApi();
        document.getElementById('invoice-number').value = invoiceNumber;
    } catch (err) {
        showResponseModal('Error loading company info or invoice number: ' + (err.message || err), 'error');
    }
}

// Form: Handle salesperson search and dropdown UI
async function searchSalesperson(input) {
    const searchTerm = input.value.trim();
    const dropdown = document.getElementById('search-results-salesperson');
    if (searchTerm.length < 2) {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        return;
    }
    const data = await fetchSalespeopleApi(searchTerm);
    dropdown.innerHTML = '';
    if (data.success && data.results && data.results.length > 0) {
        dropdown.style.display = 'block';
        data.results.forEach(person => {
            const div = document.createElement('div');
            div.classList.add('search-result-salesperson');
            div.textContent = person.first_name + ' ' + person.last_name + (person.email ? ' (' + person.email + ')' : '');
            div.onclick = function () {
                input.value = person.first_name + ' ' + person.last_name;
                input.setAttribute('data-employee-id', person.employee_id);
                dropdown.style.display = 'none';
            };
            dropdown.appendChild(div);
        });
    } else if (data.success) {
        dropdown.style.display = 'block';
        dropdown.innerHTML = '<div>No salespeople found</div>';
    } else {
        dropdown.style.display = 'block';
        dropdown.innerHTML = `<div>${data.message || 'Error searching salespeople'}</div>`;
    }
}
/**
 * Yes, you can use getInvoiceFormData() to collect data for creating an invoice,
 * but to populate (prefill) the modal for viewing/editing an existing invoice,
 * you need a separate function that takes an invoice data object and fills the modal fields.
 * 
 * getInvoiceFormData() is for extracting data FROM the modal.
 * To populate the modal, you need a setInvoiceFormData(invoiceData) function.
 * 
 * Example: Use getInvoiceFormData() when submitting/saving.
 * Use setInvoiceFormData(invoiceData) when loading an existing invoice for view/edit.
 */

// Example function to populate the modal with invoice data
function setInvoiceFormData(invoiceData) {
    console.log('[setInvoiceFormData] invoiceData:', invoiceData);
    // Set invoice ID for edit mode
    if (document.getElementById('invoice-id')) {
        document.getElementById('invoice-id').value = invoiceData.invoice_id || '';
        console.log('[setInvoiceFormData] Set #invoice-id value to:', document.getElementById('invoice-id').value);
    } else {
        console.warn('[setInvoiceFormData] #invoice-id field not found in DOM');
    }
    // Always set both IDs
    if (document.getElementById('customer-id')) document.getElementById('customer-id').value = invoiceData.customer_id || '';
    if (document.getElementById('company-id')) document.getElementById('company-id').value = invoiceData.company_id || '';

    // Only fill client fields from the recipient (customer or company being invoiced)
    if (invoiceData.customer_id && invoiceData.customer) {
        if (document.getElementById('customer-name')) document.getElementById('customer-name').value = ((invoiceData.customer.customer_first_name || '') + ' ' + (invoiceData.customer.customer_last_name || '')).trim();
        if (document.getElementById('customer-email')) document.getElementById('customer-email').value = invoiceData.customer.customer_email || '';
        if (document.getElementById('customer-phone')) document.getElementById('customer-phone').value = invoiceData.customer.customer_phone || '';
        if (document.getElementById('customer-vat-number')) document.getElementById('customer-vat-number').value = invoiceData.customer.vat_number || '';
        if (document.getElementById('customer-reg-number')) document.getElementById('customer-reg-number').value = invoiceData.customer.registration_number || '';
        if (document.getElementById('customer-adress-1')) document.getElementById('customer-adress-1').value = invoiceData.customer.address_line1 || '';
        if (document.getElementById('customer-adress-2')) document.getElementById('customer-adress-2').value = invoiceData.customer.address_line2 || '';
    } else if (invoiceData.company_id && invoiceData.company) {
        if (document.getElementById('customer-name')) document.getElementById('customer-name').value = invoiceData.company.company_name || '';
        if (document.getElementById('customer-email')) document.getElementById('customer-email').value = invoiceData.company.email || '';
        if (document.getElementById('customer-phone')) document.getElementById('customer-phone').value = invoiceData.company.phone || '';
        if (document.getElementById('customer-vat-number')) document.getElementById('customer-vat-number').value = invoiceData.company.vat_number || '';
        if (document.getElementById('customer-reg-number')) document.getElementById('customer-reg-number').value = invoiceData.company.registration_number || '';
        if (document.getElementById('Customer-adress-1')) document.getElementById('Customer-adress-1').value = invoiceData.company.address_line1 || '';
        if (document.getElementById('Customer-adress-2')) document.getElementById('Customer-adress-2').value = invoiceData.company.address_line2 || '';
    }
    // Set salesperson info
    if (invoiceData.salesperson) {
        const name = (invoiceData.salesperson.employee_first_name || '') + ' ' + (invoiceData.salesperson.employee_last_name || '');
        document.getElementById('salesperson').value = name.trim();
        // If you have employee_id, set as data attribute
        if (invoiceData.employee_id) {
            document.getElementById('salesperson').setAttribute('data-employee-id', invoiceData.employee_id);
        }
    }
    // Set invoice number
    document.getElementById('invoice-number').value = invoiceData.invoice_number || '';
    // Set invoice date
    if (document.getElementById('current-date')) {
        document.getElementById('current-date').value = invoiceData.invoice_date || '';
    }
    // Set due date
    if (document.getElementById('due-date')) {
        document.getElementById('due-date').value = invoiceData.due_date || '';
    }
    // Set status
    if (document.getElementById('status')) {
        document.getElementById('status').value = invoiceData.status_name || '';
    }
    // Set items
    const invoiceTableBody = document.querySelector('#invoice-table tbody');
    if (invoiceTableBody) {
        invoiceTableBody.innerHTML = '';
        if (Array.isArray(invoiceData.items)) {
            invoiceData.items.forEach(item => {
                const row = document.createElement('tr');
                row.innerHTML = `
                    <td><input type="number" value="${item.qty || 1}" class="quantity"></td>
                    <td style="display:none;"><input type="hidden" class="product-id" value="${item.product_id || ''}"></td>
                    <td>
                        <div class="search-container" style="position: relative;">
                            <input type="text" placeholder="Search Item Code" class="item-code" value="${item.item_code || ''}" oninput="searchItem(this, 'item-code')">
                            <div class="search-dropdown1" id="search-results-code"></div>
                        </div>
                    </td>
                    <td>
                        <div class="search-container" style="position: relative;">
                            <input type="text" placeholder="Search Description" class="description" value="${item.description || ''}" oninput="searchItem(this, 'description')">
                            <div class="search-dropdown2" id="search-results-desc"></div>
                        </div>
                    </td>
                    <td><input type="text" value="${item.unit_price || 'R0.00'}" class="unit-price" oninput="removeR(this)" onblur="formatPrice(this)" onfocus="removeR(this)"></td>
                    <td>
                        <select class="tax">
                            <option value="0" ${item.tax_percentage == 0 ? 'selected' : ''}>[None]</option>
                            <option value="10" ${item.tax_percentage == 10 ? 'selected' : ''}>10%</option>
                            <option value="15" ${item.tax_percentage == 15 ? 'selected' : ''}>15%</option>
                            <option value="20" ${item.tax_percentage == 20 ? 'selected' : ''}>20%</option>
                            <option value="25" ${item.tax_percentage == 25 ? 'selected' : ''}>25%</option>
                        </select>
                    </td>
                    <td class="total-container">
                        <span class="total">${item.total || 'R0.00'}</span>
                        <button class="remove-row" onclick="removeItem(event)">✖</button>
                    </td>
                    <td class="stock" style="display:none;">${item.stock || 0}</td>
                `;
                invoiceTableBody.appendChild(row);
            });
        }
    }
    // Set totals if present
    if (invoiceData.subtotal && document.querySelector('.subtotal')) {
        document.querySelector('.subtotal').textContent = invoiceData.subtotal;
    }
    if (invoiceData.tax_amount && document.querySelector('.tax-total')) {
        document.querySelector('.tax-total').textContent = invoiceData.tax_amount;
    }
    if (invoiceData.total_amount && document.querySelector('.final-total')) {
        document.querySelector('.final-total').textContent = invoiceData.total_amount;
    }
    // Set notes
    if (invoiceData.notes !== undefined && document.getElementById('invoice-notes')) {
        document.getElementById('invoice-notes').value = invoiceData.notes;
    }
}

// Collects all invoice form data and returns a structured object
function getInvoiceFormData() {
    // Collect client info
    const customerIdInput = document.getElementById('customer-id');
    const companyIdInput = document.getElementById('company-id');
    console.log('[getInvoiceFormData] customer-id input value:', customerIdInput ? customerIdInput.value : 'null');
    console.log('[getInvoiceFormData] company-id input value:', companyIdInput ? companyIdInput.value : 'null');
    const clientType = window.selectedClientType || 'customer';
    let customer_id = '';
    let company_id = '';
    if (clientType === 'company') {
        company_id = companyIdInput ? companyIdInput.value : '';
    } else {
        customer_id = customerIdInput ? customerIdInput.value : '';
    }
    const customer_name = document.getElementById('customer-name').value;
    const email = document.getElementById('customer-email').value;
    const phone = document.getElementById('customer-phone').value;
    const vat_number = document.getElementById('customer-vat-number').value;
    const registration_number = document.getElementById('customer-reg-number').value;
    const address1 = document.getElementById('Customer-adress-1').value;
    const address2 = document.getElementById('Customer-adress-2').value;

    // Collect salesperson info
    const salesperson = {
        name: document.getElementById('salesperson').value,
        employee_id: document.getElementById('salesperson').getAttribute('data-employee-id') || ''
    };
    // Collect invoice number
    const invoice_number = document.getElementById('invoice-number').value;
    // Collect items from invoice table
    const items = [];
    const rows = document.querySelectorAll('#invoice-table tbody tr');
    rows.forEach(row => {
        const qty = row.querySelector('.quantity')?.value || 1;
        const item_code = row.querySelector('.item-code')?.value || '';
        const description = row.querySelector('.description')?.value || '';
        const unit_price = row.querySelector('.unit-price')?.value || '';
        const taxDropdown = row.querySelector('.tax');
        const tax_percentage = taxDropdown ? taxDropdown.value : '';
        let tax_rate_id = '';
        if (taxDropdown) {
            const selectedOption = taxDropdown.options[taxDropdown.selectedIndex];
            if (selectedOption && selectedOption.dataset.taxRateId) {
                tax_rate_id = selectedOption.dataset.taxRateId;
            }
        }
        const total = row.querySelector('.total')?.textContent || '';
        const item_id = row.dataset.itemId || '';
        if (item_code || description) {
            items.push({ qty, item_code, description, unit_price, tax: tax_percentage, total, tax_percentage, tax_rate_id, item_id });
        }
    });
    // Collect totals
    const totals = {
        subtotal: document.getElementById('subtotal')?.textContent || '',
        tax: document.getElementById('tax-total')?.textContent || '',
        total: document.getElementById('final-total')?.textContent || ''
    };
    // Collect notes
    const notes = Array.from(document.querySelectorAll('.modal-invoice-notes textarea')).map(t => t.value);

    const client = {
        customer_id,
        company_id,
        customer_name,
        email,
        phone,
        vat_number,
        registration_number,
        address1,
        address2
    };
    const invoice_id = document.getElementById('invoice-id') ? document.getElementById('invoice-id').value : '';
    console.log('[getInvoiceFormData] Read #invoice-id value:', invoice_id);
    const result = {
        invoice_id,
        client_type: clientType,
        customer_id: client.customer_id,
        company_id: client.company_id,
        customer_name: client.customer_name,
        email: client.email,
        phone: client.phone,
        vat_number: client.vat_number,
        registration_number: client.registration_number,
        address1: client.address1,
        address2: client.address2,
        salesperson,
        invoice_number,
        items,
        totals,
        notes,
        invoice_type: document.getElementById('invoice-type') ? document.getElementById('invoice-type').value : 'invoice'
    };
    console.log('[getInvoiceFormData] Returning:', result);
    return result;
}

// In invoice-form.js, you would call this API function and handle UI modals:
async function handleSaveInvoice() {
    const formData = getInvoiceFormData();
    const partyType = window.selectedPartyType;
    const invoiceType = document.getElementById('invoice-type') ? document.getElementById('invoice-type').value : 'invoice';
    let recurringDetails = {};
    if (invoiceType === 'recurring') {
        recurringDetails = {
            frequency: document.getElementById('recurring-frequency')?.value || '',
            start_date: document.getElementById('recurring-start-date')?.value || '',
            end_date: document.getElementById('recurring-end-date')?.value || ''
        };
    }

    showLoadingModal('Saving invoice...');
    const result = await saveInvoiceApi(formData, partyType, recurringDetails);
    hideLoadingModal();

    if (result.success) {
        showResponseModal('Invoice saved successfully', 'success');
        // Optionally refresh or update UI here
    } else {
        showResponseModal(result.message || 'Failed to save invoice', 'error');
    }
}

function autofillRow(row, result) {
    row.querySelector('.item-code').value = result.item_code;
    row.querySelector('.description').value = result.description;
    row.querySelector('.unit-price').value = result.unit_price;
    row.querySelector('.product-id').value = result.product_id;
    row.dataset.productId = result.product_id || '';
    row.dataset.taxRateId = result.tax_rate_id || result.tax_rate || '';
    // Set the tax dropdown using result.tax_percentage or result.tax_rate
    const taxDropdown = row.querySelector('.tax');
    if (taxDropdown) {
        let taxVal = '';
        if (typeof result.tax_percentage !== 'undefined' && result.tax_percentage !== null) {
            taxVal = String(parseFloat(result.tax_percentage));
        } else if (typeof result.tax_rate !== 'undefined' && result.tax_rate !== null) {
            taxVal = String(parseFloat(result.tax_rate));
        }
        if (taxVal) {
            for (let option of taxDropdown.options) {
                if (option.value === taxVal || option.value === taxVal + '%') {
                    taxDropdown.value = option.value;
                    break;
                }
            }
        }
    }
}

export { searchClientOrCompany, searchSalesperson, setInvoiceFormData, getInvoiceFormData};
window.setInvoiceFormData = setInvoiceFormData;
window.getInvoiceFormData = getInvoiceFormData;
window.handleSaveInvoice = handleSaveInvoice;
window.searchClientOrCompany = searchClientOrCompany;