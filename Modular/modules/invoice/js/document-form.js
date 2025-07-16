// Form function: handles UI and populates fields
import { searchClients, searchSalespeople, searchProducts, saveDocumentApi } from './document-api.js';
import { openDocumentModal } from './document-modal.js';

// Refactored for new modal structure and DB schema
// All field selectors, data extraction, and population logic now use new IDs/classes from document-modal.php
// All logic is mapped to the unified invoicing.clients and invoicing.documents schema

// --- Client Search ---
function searchClient(inputElement) {
    const query = inputElement.value.trim();
    const resultsContainer = document.getElementById('search-results-client');
    const dropdown = document.getElementById('search-results-client');
    if (query.length < 2) {
        dropdown.style.display = 'none';
        resultsContainer.innerHTML = '';
        return;
    }
    searchClients(query, function (results) {
        resultsContainer.innerHTML = '';
        if (results && results.length > 0) {
            dropdown.style.display = 'block';
            results.forEach((item, idx) => {
                const div = document.createElement('div');
                div.classList.add('search-result-client');
                div.textContent = item.client_name + (item.client_email ? ' (' + item.client_email + ')' : '');
                div.onclick = function (e) {
                    fillClientFields(item);
                    resultsContainer.innerHTML = '';
                    dropdown.style.display = 'none';
                };
                if (idx === 0) div.classList.add('highlight'); // highlight top result by default
                resultsContainer.appendChild(div);
            });
        } else {
            dropdown.style.display = 'block';
            const noResults = document.createElement('div');
            noResults.textContent = 'No clients found';
            resultsContainer.appendChild(noResults);
        }
    });
}

function fillClientFields(item) {
    document.getElementById('client-id').value = item.client_id || '';
    document.getElementById('client-name').value = item.client_name || '';
    document.getElementById('client-email').value = item.client_email || '';
    document.getElementById('client-phone').value = item.client_cell || item.client_tell || '';
    document.getElementById('client-vat-number').value = item.vat_number || '';
    document.getElementById('client-reg-number').value = item.registration_number || '';
    document.getElementById('client-address-1').value = item.address_line1 || '';
    document.getElementById('client-address-2').value = item.address_line2 || '';
    document.getElementById('client-city').value = item.city || '';
    document.getElementById('client-suburb').value = item.suburb || '';
    document.getElementById('client-province').value = item.province || '';
    document.getElementById('client-country').value = item.country || '';
    document.getElementById('client-postal-code').value = item.postal_code || '';
}

// --- Add Tab key and blur support for client search ---
document.addEventListener('DOMContentLoaded', function () {
    const clientInput = document.getElementById('client-name');
    const resultsContainer = document.getElementById('search-results-client');
    if (clientInput && resultsContainer) {
        // Tab key selects top result
        clientInput.addEventListener('keydown', function (e) {
            if (e.key === 'Tab' && resultsContainer.style.display === 'block') {
                const firstResult = resultsContainer.querySelector('.search-result-client');
                if (firstResult) {
                    firstResult.click();
                    e.preventDefault();
                    // Move focus to next logical input (client email or next field)
                    const next = document.getElementById('client-email') || clientInput.nextElementSibling;
                    if (next && typeof next.focus === 'function') {
                        next.focus();
                    }
                }
            }
        });
        // Blur closes dropdown (with small delay to allow click)
        clientInput.addEventListener('blur', function () {
            setTimeout(() => {
                resultsContainer.style.display = 'none';
            }, 150);
        });
        // Optional: visually highlight top result on Tab
        clientInput.addEventListener('keyup', function (e) {
            if (e.key === 'Tab' && resultsContainer.style.display === 'block') {
                const firstResult = resultsContainer.querySelector('.search-result-client');
                if (firstResult) {
                    resultsContainer.querySelectorAll('.search-result-client').forEach(el => el.classList.remove('highlight'));
                    firstResult.classList.add('highlight');
                }
            }
        });
    }

    // Salesperson Tab/blur support
    const salespersonInput = document.getElementById('salesperson');
    const salespersonDropdown = document.getElementById('search-results-salesperson');
    if (salespersonInput && salespersonDropdown) {
        salespersonInput.addEventListener('keydown', function (e) {
            if (e.key === 'Tab' && salespersonDropdown.style.display === 'block') {
                const firstResult = salespersonDropdown.querySelector('.search-result-salesperson');
                if (firstResult) {
                    firstResult.click();
                    e.preventDefault();
                    // Move focus to next logical input (e.g., document type or next field)
                    const next = document.getElementById('document-type') || salespersonInput.nextElementSibling;
                    if (next && typeof next.focus === 'function') {
                        next.focus();
                    }
                }
            }
        });
        salespersonInput.addEventListener('blur', function () {
            setTimeout(() => {
                salespersonDropdown.style.display = 'none';
            }, 150);
        });
        salespersonInput.addEventListener('keyup', function (e) {
            if (e.key === 'Tab' && salespersonDropdown.style.display === 'block') {
                const firstResult = salespersonDropdown.querySelector('.search-result-salesperson');
                if (firstResult) {
                    salespersonDropdown.querySelectorAll('.search-result-salesperson').forEach(el => el.classList.remove('highlight'));
                    firstResult.classList.add('highlight');
                }
            }
        });
    }
});

// --- Salesperson Search ---
function searchSalesperson(input) {
    const searchTerm = input.value.trim();
    const dropdown = document.getElementById('search-results-salesperson');
    if (searchTerm.length < 2) {
        dropdown.style.display = 'none';
        dropdown.innerHTML = '';
        return;
    }
    searchSalespeople(searchTerm, function (data) {
        console.log('searchSalesperson called, data:', data); // DEBUG
        dropdown.innerHTML = '';
        if (data.success && data.results && data.results.length > 0) {
            dropdown.style.display = 'block';
            data.results.forEach((person, idx) => {
                const div = document.createElement('div');
                div.classList.add('search-result-salesperson');
                div.textContent = person.salesperson_name;
                div.onclick = function () {
                    input.value = person.salesperson_name;
                    input.setAttribute('data-employee-id', person.salesperson_id);
                    dropdown.style.display = 'none';
                };
                if (idx === 0) div.classList.add('highlight');
                dropdown.appendChild(div);
            });
        } else if (data.success) {
            dropdown.style.display = 'block';
            dropdown.innerHTML = '<div>No salespeople found</div>';
        } else {
            dropdown.style.display = 'block';
            dropdown.innerHTML = `<div>${data.message || 'Error searching salespeople'}</div>`;
        }
    });
}

// --- Populate Modal with Document Data ---
function setDocumentFormData(documentData) {
    // IDs
    document.getElementById('document-id').value = documentData.document_id || '';
    document.getElementById('client-id').value = documentData.client_id || '';
    // Client fields
    document.getElementById('client-name').value = documentData.client_name || '';
    document.getElementById('client-email').value = documentData.client_email || '';
    document.getElementById('client-phone').value = documentData.client_phone || '';
    document.getElementById('client-vat-number').value = documentData.vat_number || '';
    document.getElementById('client-reg-number').value = documentData.registration_number || '';
    document.getElementById('client-address-1').value = documentData.address_line1 || '';
    document.getElementById('client-address-2').value = documentData.address_line2 || '';
    // Document fields
    document.getElementById('document-type').value = documentData.document_type || 'quotation';
    document.getElementById('document-number').value = documentData.document_number || '';
    document.getElementById('current-date').value = documentData.issue_date || '';
    document.getElementById('document-status').value = documentData.document_status || 'Unpaid';
    document.getElementById('pay-in-days').value = documentData.pay_in_days || '30';
    document.getElementById('purchase-order-number').value = documentData.client_purchase_order_number || '';
    // Salesperson
    document.getElementById('salesperson').value = documentData.salesperson_name || '';
    document.getElementById('salesperson').setAttribute('data-employee-id', documentData.salesperson_id || '');
    // Notes
    document.getElementById('public-note').value = documentData.public_note || '';
    document.getElementById('private-note').value = documentData.private_note || '';
    document.getElementById('foot-note').value = documentData.foot_note || '';
    // Line items
    const tableBody = document.getElementById('document-rows');
    tableBody.innerHTML = '';
    if (Array.isArray(documentData.items)) {
        documentData.items.forEach(item => {
            const row = document.createElement('tr');
            row.classList.add('document-item-row');
            row.innerHTML = `
                <td><input type="number" value="${item.quantity || 1}" class="quantity"></td>
                <td style="display:none;"><input type="hidden" class="product-id" value="${item.product_id || ''}"></td>
                <td>
                    <div class="search-container" style="position: relative;">
                        <input type="text" placeholder="Search Item Code" class="item-code" value="${item.item_code || ''}" autocomplete="off">
                        <div class="search-dropdown1"></div>
                    </div>
                </td>
                <td>
                    <div class="search-container" style="position: relative;">
                        <input type="text" placeholder="Search Description" class="description" value="${item.product_description || ''}" autocomplete="off">
                        <div class="search-dropdown2"></div>
                    </div>
                </td>
                <td><input type="text" value="${item.unit_price || 'R0.00'}" class="unit-price"></td>
                <td>
                    <select class="tax">
                        <option value="0" ${item.tax_percentage == 0 ? 'selected' : ''}>[None]</option>
                        <option value="10" ${item.tax_percentage == 10 ? 'selected' : ''}>10%</option>
                        <option value="15" ${item.tax_percentage == 15 ? 'selected' : ''}>15%</option>
                        <option value="20" ${item.tax_percentage == 20 ? 'selected' : ''}>20%</option>
                        <option value="25" ${item.tax_percentage == 25 ? 'selected' : ''}>25%</option>
                    </select>
                </td>
                <td><span class="total">${item.line_total || 'R0.00'}</span></td>
                <td class="stock" style="display:none;">${item.stock || 0}</td>
            `;
            tableBody.appendChild(row);
        });
    }
    // Totals
    document.getElementById('subtotal').textContent = documentData.subtotal || '0.00';
    document.getElementById('tax-total').textContent = documentData.tax_amount || '0.00';
    document.getElementById('final-total').textContent = documentData.total_amount || '0.00';
}

// --- Extract Data from Modal ---
function getDocumentFormData() {
    const client_id = document.getElementById('client-id').value;
    const client_name = document.getElementById('client-name').value;
    const client_email = document.getElementById('client-email').value;
    const client_phone = document.getElementById('client-phone').value;
    const vat_number = document.getElementById('client-vat-number').value;
    const registration_number = document.getElementById('client-reg-number').value;
    const address1 = document.getElementById('client-address-1').value;
    const address2 = document.getElementById('client-address-2').value;
    const document_id = document.getElementById('document-id').value;
    const document_type = document.getElementById('document-type').value;
    const document_number = document.getElementById('document-number').value;
    const issue_date = document.getElementById('current-date').value;
    const document_status = document.getElementById('document-status').value;
    const pay_in_days = document.getElementById('pay-in-days').value;
    const client_purchase_order_number = document.getElementById('purchase-order-number').value;
    const salesperson_name = document.getElementById('salesperson').value;
    const salesperson_id = document.getElementById('salesperson').getAttribute('data-employee-id') || '';
    const public_note = document.getElementById('public-note').value;
    const private_note = document.getElementById('private-note').value;
    const foot_note = document.getElementById('foot-note').value;
    // Line items
    const items = [];
    const rows = document.querySelectorAll('#document-rows tr');
    rows.forEach(row => {
        const quantity = row.querySelector('.quantity')?.value || 1;
        const product_id = row.querySelector('.product-id')?.value || '';
        const item_code = row.querySelector('.item-code')?.value || '';
        const product_description = row.querySelector('.description')?.value || '';
        const unit_price = row.querySelector('.unit-price')?.value || '';
        const taxDropdown = row.querySelector('.tax');
        const tax_percentage = taxDropdown ? taxDropdown.value : '';
        const line_total = row.querySelector('.total')?.textContent || '';
        if (item_code || product_description) {
            items.push({ quantity, product_id, item_code, product_description, unit_price, tax_percentage, line_total });
        }
    });
    // Totals
    const subtotal = document.getElementById('subtotal')?.textContent || '';
    const tax_amount = document.getElementById('tax-total')?.textContent || '';
    const total_amount = document.getElementById('final-total')?.textContent || '';
    return {
        document_id,
        client_id,
        client_name,
        client_email,
        client_phone,
        vat_number,
        registration_number,
        address1,
        address2,
        document_type,
        document_number,
        issue_date,
        document_status,
        pay_in_days,
        client_purchase_order_number,
        salesperson_name,
        salesperson_id,
        public_note,
        private_note,
        foot_note,
        items,
        subtotal,
        tax_amount,
        total_amount
    };
}

export { searchClient, searchSalesperson, setDocumentFormData, getDocumentFormData };
window.setDocumentFormData = setDocumentFormData;
window.getDocumentFormData = getDocumentFormData;
window.searchClient = searchClient;