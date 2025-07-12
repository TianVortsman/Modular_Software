// --- Utility Functions ---

function removeR(input) {
    // Remove the 'R' symbol while typing
    if (input.value.startsWith('R')) {
        input.value = input.value.slice(1); // Remove 'R' symbol
    }
}

function formatPrice(input) {
    let value = parseFloat(input.value.replace(/[^0-9.-]+/g, "")); // Remove any non-numeric characters
    if (!isNaN(value)) {
        if (value < 0) {
            input.value = `-R${Math.abs(value).toFixed(2)}`; // Format the value with '-R' for negative values
        } else {
            input.value = `R${value.toFixed(2)}`; // Format the value with 'R' for positive values
        }
    } else {
        input.value = "R0.00"; // Default if the value is not a valid number
    }
}

function formatDiscount(input) {
    // Strip out all non-numeric characters except for the decimal point and minus sign
    let value = input.value.replace(/[^0-9.-]/g, "");
    // Handle the case where the input is empty or just "-"
    if (value === "" || value === "-") {
        input.value = "-R0.00";
        return;
    }
    // Parse the value as a float
    let parsedValue = parseFloat(value);
    // If it's a valid number, format as negative with "R"
    if (!isNaN(parsedValue)) {
        // Ensure the value is negative
        if (parsedValue > 0) {
            parsedValue = -parsedValue;
        }
        input.value = `-R${Math.abs(parsedValue).toFixed(2)}`;
    } else {
        // If not a valid number, reset to the default
        input.value = "-R0.00";
    }
}

function setModalMode(mode) {
    const modal = document.getElementById('invoice-modal');
    if (!modal) return;
    modal.setAttribute('data-mode', mode);
    // Enable/disable fields and show/hide buttons based on mode
    const isView = mode === 'view';
    const isEdit = mode === 'edit';
    const isCreate = mode === 'create';
    // All input/select/textarea fields
    const fields = modal.querySelectorAll('input, select, textarea');
    fields.forEach(field => {
        if (isView) {
            field.setAttribute('disabled', 'disabled');
        } else {
            field.removeAttribute('disabled');
        }
    });
    // Special handling for always-disabled fields
    document.getElementById('current-date')?.setAttribute('disabled', 'disabled');
    document.getElementById('invoice-number')?.setAttribute('disabled', 'disabled');
    // Buttons
    document.getElementById('create-invoice').style.display = isCreate ? '' : 'none';
    document.getElementById('preview-invoice').style.display = isView ? 'none' : '';
    document.getElementById('clear-invoice').style.display = isView ? 'none' : '';
    // Hide all remove-row buttons in view mode
    modal.querySelectorAll('.remove-row').forEach(btn => {
        btn.style.display = isView ? 'none' : '';
    });
}

function openInvoiceModal(mode = 'create') {
    console.log('[openInvoiceModal] called with mode:', mode);
    const modal = document.getElementById('invoice-modal');
    if (!modal) {
        console.error('[openInvoiceModal] Modal element with id invoice-modal not found!');
        return;
    }
    console.log('[openInvoiceModal] Modal element found. Current display:', modal.style.display);

    // Reset modal form and state
    modal.style.display = 'flex';
    console.log('[openInvoiceModal] Modal display set to flex.');
    setModalMode(mode);

    // Only clear all fields if mode is 'create'
    if (mode === 'create') {
        modal.querySelectorAll('input, textarea').forEach(input => {
            if (input.type === 'hidden') return; // Don't clear hidden fields (like IDs)
            input.value = '';
        });
        // Also clear all selects
        modal.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
        });
        // Clear table rows and add back the default fixed line item row
        const invoiceTableBody = document.querySelector('#invoice-table tbody');
        if (invoiceTableBody) {
            invoiceTableBody.innerHTML = `
                <tr>
                    <td><input type="number" value="1" class="quantity"></td>
                    <td style="display:none;"><input type="hidden" class="product-id"></td>
                    <td>
                        <div class="search-container" style="position: relative;">
                            <input type="text" placeholder="Search Item Code" class="item-code" id="item-code" autocomplete="off">
                            <div class="search-dropdown1" id="search-results-code"></div>
                        </div>
                    </td>
                    <td>
                        <div class="search-container" style="position: relative;">
                            <input type="text" placeholder="Search Description" class="description" id="description" autocomplete="off">
                            <div class="search-dropdown2" id="search-results-desc"></div>
                        </div>
                    </td>
                    <td><input type="text" value="R0.00" class="unit-price" id="unit-price" ></td>
                    <td>
                        <select class="tax">
                            <option value="0">[None]</option>
                            <option value="10">10%</option>
                            <option value="15">15%</option>
                            <option value="20">20%</option>
                            <option value="25">25%</option>
                        </select>
                    </td>
                    <td><span class="total">0.00</span></td>
                    <td class="stock" style="display:none;">0</td>
                </tr>
            `;
            // Attach event listeners for item-code and description
            const itemCodeInput = invoiceTableBody.querySelector('.item-code');
            if (itemCodeInput) {
                itemCodeInput.addEventListener('input', function() { searchItem(this, 'item-code'); });
            }
            const descriptionInput = invoiceTableBody.querySelector('.description');
            if (descriptionInput) {
                descriptionInput.addEventListener('input', function() { searchItem(this, 'description'); });
            }
        }
    }

    // Set invoice type to quotation by default on create
    const invoiceTypeDropdown = document.getElementById('invoice-type');
    if (invoiceTypeDropdown) {
        if (mode === 'create') {
            invoiceTypeDropdown.value = 'quotation';
            switchModal('quotation');
        } else if (window._lastInvoiceType) {
            // If editing/viewing, set to the last invoice type if available
            invoiceTypeDropdown.value = window._lastInvoiceType;
            switchModal(window._lastInvoiceType);
        }
    }

    // Set current date in the input field
    const dateInput = document.getElementById('current-date');
    if (dateInput) {
        const today = new Date();
        const formattedDate = today.toISOString().split('T')[0];
        dateInput.value = formattedDate;
    }

    // Set pay-in-days to 30 by default
    const payInDaysSelect = document.getElementById('pay-in-days');
    if (payInDaysSelect) {
        payInDaysSelect.value = '30';
    }

    // Always hide recurring section on modal open
    const recurringSection = document.getElementById('recurring-section');
    if (recurringSection) {
        recurringSection.style.display = 'none';
    }

    // Setup row listeners and update totals
    setupRowListeners();
    updateTotals();

    // Setup customer name input search
    const customerNameInput = document.getElementById('customer-name');
    if (customerNameInput) {
        customerNameInput.oninput = function () { searchClientOrCompany(customerNameInput); };
    }

    // Setup modal type dropdown event (remove previous to avoid stacking)
    if (invoiceTypeDropdown) {
        invoiceTypeDropdown.removeEventListener('_modular_invoice_type_change', invoiceTypeDropdown._modularHandler || (() => {}));
        invoiceTypeDropdown._modularHandler = async function () {
            const selectedType = this.value;
            switchModal(selectedType);
            // Show/hide recurring section
            if (recurringSection) {
                recurringSection.style.display = (selectedType === 'recurring-invoice') ? 'block' : 'none';
            }
            // Update preview number on type change
            let previewNumber = '';
            if (selectedType === 'quotation') {
                previewNumber = await fetchNextQuotationNumberPreview();
            } else {
                previewNumber = await fetchNextInvoiceNumberPreview();
            }
            const invoiceNumberField = document.getElementById('invoice-number');
            if (invoiceNumberField) {
                invoiceNumberField.value = previewNumber + ' (Preview)';
                invoiceNumberField.style.color = 'var(--color-invoice-preview, #8888cc)';
            }
        };
        invoiceTypeDropdown.addEventListener('change', invoiceTypeDropdown._modularHandler, false);
        // Custom event name to avoid duplicate listeners
        invoiceTypeDropdown.addEventListener('_modular_invoice_type_change', invoiceTypeDropdown._modularHandler, false);
    } else {
        console.error('Invoice type dropdown not found!');
    }

    // Async: fetch company info and preview number
    (async function() {
        try {
            const companyData = await fetchCompanyInfoApi();
            document.getElementById('company-name-display').textContent = companyData.company_name || '';
            document.getElementById('company-address-display').textContent = companyData.company_address || '';
            document.getElementById('company-email-display').textContent = companyData.company_email || '';
            document.getElementById('company-phone-display').textContent = companyData.company_phone || '';
            document.getElementById('company-vat-display').textContent = 'VAT: ' + (companyData.vat_number || '');
            document.getElementById('company-reg-display').textContent = 'Reg: ' + (companyData.registration_number || '');
            const customerName = document.getElementById('customer-name')?.value;
            if (customerName && customerName === companyData.company_name) {
                document.getElementById('customer-vat-number').value = companyData.vat_number || '';
                document.getElementById('customer-reg-number').value = companyData.registration_number || '';
            }
        } catch (err) {
            if (typeof ResponseModal !== 'undefined') {
                ResponseModal.error('Error loading company info: ' + (err.message || err));
            } else {
                alert('Error loading company info: ' + (err.message || err));
            }
        }
        try {
            // For preview, fetch the next quotation number but do not assign it
            const invoiceType = invoiceTypeDropdown ? invoiceTypeDropdown.value : 'quotation';
            let previewNumber = '';
            if (invoiceType === 'quotation') {
                previewNumber = await fetchNextQuotationNumberPreview();
            } else {
                previewNumber = await fetchNextInvoiceNumberPreview();
            }
            const invoiceNumberField = document.getElementById('invoice-number');
            if (invoiceNumberField) {
                invoiceNumberField.value = previewNumber + ' (Preview)';
                invoiceNumberField.style.color = 'var(--color-invoice-preview, #8888cc)';
            }
        } catch (err) {
            if (typeof ResponseModal !== 'undefined') {
                ResponseModal.error('Error loading preview number: ' + (err.message || err));
            } else {
                alert('Error loading preview number: ' + (err.message || err));
            }
        }
    })();
}

function closeInvoiceModal() {
    document.getElementById('invoice-modal').style.display = 'none';
    updateDashboard();
}

function addItem() {
    console.log('Adding item...');
    // Get the invoice type from the dropdown
    const invoiceType = document.getElementById('invoice-type').value;
    // Get the relevant tables
    const invoiceTable = document.getElementById('invoice-table');
    const vehicleTable = document.getElementById('dealership-vehicle-table');
    // Define a variable to hold the table to add rows to
    let table;
    // Determine the table based on the selected invoice type
    if (invoiceType === 'vehicle-quotation' || invoiceType === 'vehicle-invoice') {
        // Use the vehicle table for vehicle-related invoices
        table = vehicleTable;
    } else {
        // Default to the standard invoice table
        table = invoiceTable;
    }
    // Ensure the table exists and is visible before appending a new row
    if (table && table.style.display !== 'none') {
        const row = document.createElement('tr');
        // Add content for the row based on the table being used
        if (table === invoiceTable) {
            row.innerHTML = `
                <td><input type="number" value="1" class="quantity"></td>
                <td style="display:none;"><input type="hidden" class="product-id"></td>
                <td>
                    <div class="search-container" style="position: relative;">
                        <input type="text" placeholder="Search Item Code" class="item-code" id="item-code" oninput="searchItem(this, 'item-code')">
                        <div class="search-dropdown1" id="search-results-code"></div>
                    </div>
                </td>
                <td>
                    <div class="search-container" style="position: relative;">
                        <input type="text" placeholder="Search Description" class="description" id="description" oninput="searchItem(this, 'description')">
                        <div class="search-dropdown2" id="search-results-desc"></div>
                    </div>
                </td>
                <td><input type="text" value="R0.00" class="unit-price" oninput="removeR(this)" onblur="formatPrice(this)" onfocus="removeR(this)"></td>
                <td>
                    <select class="tax">
                        <option value="0">[None]</option>
                        <option value="10">10%</option>
                        <option value="15">15%</option>
                        <option value="20">20%</option>
                        <option value="25">25%</option>
                    </select>
                </td>
                <td class="total-container">
                    <span class="total">R0.00</span>
                    <button class="remove-row" onclick="removeItem(event)">✖</button>
                </td>
                <td class="stock" style="display:none;">0</td>
            `;
        } else if (table === vehicleTable) {
            row.innerHTML = `
                <td><input type="number" value="1" class="dealership-quantity"></td>
                <td>
                    <div class="search-container2" style="position: relative;">
                        <input type="text" placeholder="Search Part Name" class="dealership-part-name" id="dealership-part-name" oninput="searchParts(this)">
                        <div class="search-dropdown3" id="search-results-part-name"></div>
                    </div>
                </td>
                <td>
                    <div class="search-container2" style="position: relative;">
                        <input type="text" placeholder="Search Description" class="dealership-description" id="dealership-description" oninput="searchParts(this)">
                        <div class="search-dropdown4" id="search-results-description"></div>
                    </div>
                </td>
                <td><input type="text" value="R0.00" class="dealership-unit-price"></td>
                <td>
                    <select class="dealership-tax">
                        <option value="0">[None]</option>
                        <option value="15">15%</option>
                        <option value="20">20%</option>
                    </select>
                </td>
                <td class="dealership-total">R0.00</td>
                <td class="dealership-stock" style="display:none;">0</td>
            `;
        }
        // Append the newly created row to the respective table body
        table.querySelector('tbody').appendChild(row);
    } else {
        console.error('No visible table found to add the item.');
    }
}

function addDiscount() {
    const table = document.getElementById('invoice-table');
    if (!table) {
        console.error('Table element not found');
        return;
    }
    const row = document.createElement('tr');
    row.innerHTML = `
        <td><input type="number" value="1" class="quantity" disabled></td>
        <td style="display:none;"><input type="hidden" class="product-id"></td>
        <td><input type="text" placeholder="Discount" class="item-code" id="item-code" oninput="searchitem(this)" value="Discount" disabled></td>
        <td><input type="text" placeholder="Discount" class="description" oninput="searchitem(this)" value="Discount" disabled></td>
        <td><input type="text" value="-R0.00" class="unit-price" oninput="removeR(this)" onblur="formatDiscount(this)" onfocus="removeR(this)"></td>
        <td>
            <select class="tax" disabled>
                <option value="0">[None]</option>
            </select>
        </td>
        <td class="total-container">
            <span class="total">R0.00</span>
            <button class="remove-row" onclick="removeItem(event)">✖</button>
        </td>
        <td class="stock" style="display:none;">0</td>
    `;
    table.appendChild(row);
}

function removeItem(event) {
    const button = event.target;
    const row = button.closest('tr');
    if (row) {
        row.remove();
        updateTotals();
    } else {
        console.error('Row element not found');
    }
}

// Function to calculate the total price with tax
function calculateTotal(price, tax) {
    return parseFloat(price) * (1 + (parseFloat(tax) / 100));
}

function autofillRow(row, result) {
    console.log('DEBUG autofillRow result:', result);
    // Autofill the row with product details
    row.querySelector('.item-code').value = result.item_code || result.sku || '';
    row.querySelector('.description').value = result.description || result.product_description || '';
    if (result.unit_price || result.product_price) {
        row.querySelector('.unit-price').value = `R${result.unit_price || result.product_price}`;
    }
    row.querySelector('.product-id').value = result.product_id;
    row.dataset.productId = result.product_id || '';
    row.dataset.taxRateId = result.tax_rate_id || result.tax_rate || '';
    // Always set quantity to 1 if not set or empty
    const qtyInput = row.querySelector('.quantity');
    if (qtyInput && (!qtyInput.value || isNaN(parseInt(qtyInput.value)) || parseInt(qtyInput.value) < 1)) {
        qtyInput.value = 1;
    }
    // Set the tax dropdown using result.tax_rate (from PHP, e.g., 15 for 15%)
    const taxDropdown = row.querySelector('.tax');
    const taxRateFromPHP = typeof result.tax_rate !== 'undefined' ? Math.round(parseFloat(result.tax_rate)) : 0;
    if (taxDropdown) {
        let found = false;
        for (let option of taxDropdown.options) {
            let optVal = option.value.replace('%', '');
            if (Math.round(parseFloat(optVal)) === taxRateFromPHP) {
                taxDropdown.value = option.value;
                found = true;
                break;
            }
        }
        if (!found) taxDropdown.value = '0';
    }
    // Recalculate row total after autofill
    calculateRowTotal(row);
    updateTotals();
}

function calculateRowTotal(row) {
    // Retrieve input values
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0; // Default to 0 if no quantity
    const unitPrice = parseFloat(row.querySelector('.unit-price').value.replace(/[^\d.-]/g, '')) || 0; // Remove "R" and non-numeric characters
    const taxPercentage = parseFloat(row.querySelector('.tax').value) || 0; // Default to 0 if no tax value
    console.log('Calculating Row Total - Quantity:', quantity);
    console.log('Calculating Row Total - Unit Price:', unitPrice);
    console.log('Calculating Row Total - Tax Percentage:', taxPercentage);
    // Calculate row total before tax
    const rowTotalBeforeTax = quantity * unitPrice;
    // Calculate row tax
    const rowTax = (rowTotalBeforeTax * taxPercentage) / 100;
    // Calculate the final row total (including tax)
    const rowTotal = rowTotalBeforeTax + rowTax;
    // Update the row total field
    const totalElement = row.querySelector('.total');
    if (totalElement) {
        console.log('Row Total (including tax):', rowTotal.toFixed(2));
        // Format with "-R" for negatives
        const formattedTotal = rowTotal < 0
            ? `-R${Math.abs(rowTotal).toFixed(2)}`
            : `R${rowTotal.toFixed(2)}`;
        totalElement.textContent = formattedTotal; // Update the total element
    } else {
        console.log('Total element not found for this row.');
    }
    return {
        rowTotalBeforeTax: rowTotalBeforeTax,
        rowTax: rowTax,
        rowTotal: rowTotal
    };
}

function updateTotals() {
    let subtotal = 0;
    let totalTax = 0;
    let finalTotal = 0;
    const rows = document.querySelectorAll('tr'); // Select all rows
    rows.forEach(row => {
        if (row.querySelector('.quantity') && row.querySelector('.unit-price') && row.querySelector('.tax')) {
            const rowTotals = calculateRowTotal(row); // Calculate row totals
            // Accumulate totals for all rows
            subtotal += rowTotals.rowTotalBeforeTax;
            totalTax += rowTotals.rowTax;
            finalTotal += rowTotals.rowTotal;
        }
    });
    // Update the summary section
    console.log('Subtotal:', subtotal.toFixed(2));
    document.querySelector('.subtotal').textContent = subtotal.toFixed(2);
    console.log('Total Tax:', totalTax.toFixed(2));
    document.querySelector('.tax-total').textContent = totalTax.toFixed(2);
    console.log('Final Total:', finalTotal.toFixed(2));
    document.querySelector('.final-total').textContent = finalTotal.toFixed(2);
}

function setupRowListeners() {
    const table = document.querySelector('.modal-invoice-table'); // Select the parent table (or container)
    if (!table) return;
    // Attach a single event listener to the table (event delegation)
    table.addEventListener('input', function(event) {
        const row = event.target.closest('tr'); // Find the closest row that was clicked
        // Check if the input element is within the fields we want to listen for changes
        if (row && (event.target.classList.contains('quantity') ||
            event.target.classList.contains('unit-price') ||
            event.target.classList.contains('tax'))) {
            console.log('Input detected in row:', row);  // Log for debugging
            // Recalculate row total when quantity, unit price, or tax inputs change
            calculateRowTotal(row);
            // Update the grand totals (e.g., subtotal, tax, and final total)
            updateTotals();
        }
    });
    // Optionally, add a change event listener for non-input elements (e.g., for selecting from dropdowns)
    table.addEventListener('change', function(event) {
        const row = event.target.closest('tr');
        if (row && (event.target.classList.contains('quantity') ||
            event.target.classList.contains('unit-price') ||
            event.target.classList.contains('tax'))) {
            console.log('Change detected in row:', row);  // Log for debugging
            // Recalculate row total and update grand totals
            calculateRowTotal(row);
            updateTotals();
        }
    });
}


// Form/UI function: handle product search and display results
function searchItem(inputElement) {
    const searchTerm = inputElement.value.trim();
    const row = inputElement.closest('tr');
    const resultsContainer1 = row.querySelector('.search-dropdown1');
    const resultsContainer2 = row.querySelector('.search-dropdown2');
    const field = inputElement.id;
    const resultsContainer = (field === 'description') ? resultsContainer2 : resultsContainer1;

    if (searchTerm.length < 2) {
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';
        return;
    }

    resultsContainer.style.display = 'block';

    fetchProductSearchResults(field, searchTerm, function(results) {
        resultsContainer.innerHTML = '';
        if (results.length > 0) {
            const ul = document.createElement('ul');
            ul.classList.add('search-results-list');
            results.forEach((result) => {
                const li = document.createElement('li');
                li.classList.add('search-result-item');
                if (field === 'description') {
                    li.textContent = result.product_description;
                } else {
                    li.textContent = result.sku || result.product_name;
                }
                li.addEventListener('click', () => {
                    autofillRow(row, {
                        item_code: result.sku || '',
                        description: result.product_description || '',
                        unit_price: result.product_price || '',
                        product_id: result.product_id,
                        tax_rate_id: result.tax_rate_id || '',
                        tax_rate: result.tax_rate || 0,
                        tax_percentage: result.tax_rate || 0
                    });
                    resultsContainer.style.display = 'none';
                });
                ul.appendChild(li);
            });
            resultsContainer.appendChild(ul);

            // Only add one keydown event per input
            inputElement.onkeydown = function (event) {
                if (event.key === 'Tab' && results.length > 0) {
                    event.preventDefault();
                    const result = results[0];
                    autofillRow(row, {
                        item_code: result.sku || '',
                        description: result.product_description || '',
                        unit_price: result.product_price || '',
                        product_id: result.product_id,
                        tax_rate_id: result.tax_rate_id || '',
                        tax_rate: result.tax_rate || 0,
                        tax_percentage: result.tax_rate || 0
                    });
                    resultsContainer.style.display = 'none';
                }
            };
        } else {
            resultsContainer.innerHTML = "<p>No results found.</p>";
        }
    });
}

// --- Modal Switching ---

function switchModal(type) {
    console.log('Switching modal to type:', type); // Debug log
    // Get modal elements
    const invoiceHeader = document.querySelector('.modal-invoice-header h2');
    const dealershipDetails = document.querySelector('.modal-dealership-vehicle-details');
    const dealershipExtras = document.querySelector('.modal-dealership-extras');
    const invoiceTable = document.querySelector('.modal-invoice-table-container');
    const recurringDateInput = document.getElementById('current-date');
    // Reset visibility of elements
    if (dealershipDetails && dealershipExtras && invoiceTable) {
        dealershipDetails.style.display = 'none';
        dealershipExtras.style.display = 'none';
        invoiceTable.style.display = 'block'; // Default back to normal invoice
    }
    if (recurringDateInput) recurringDateInput.disabled = true;
    // Switch based on invoice type
    switch (type) {
        case 'quotation':
            if (invoiceHeader) invoiceHeader.textContent = 'New Quotation';
            break;
        case 'vehicle-quotation':
            if (invoiceHeader) invoiceHeader.textContent = 'New Vehicle Quotation';
            if (dealershipDetails) dealershipDetails.style.display = 'block';
            if (dealershipExtras) dealershipExtras.style.display = 'block';
            if (invoiceTable) invoiceTable.style.display = 'none';
            if (typeof setupPartRowListeners === "function") setupPartRowListeners();
            if (typeof updatePartTotals === "function") updatePartTotals();
            break;
        case 'standard-invoice':
            if (invoiceHeader) invoiceHeader.textContent = 'New Invoice';
            break;
        case 'vehicle-invoice':
            if (invoiceHeader) invoiceHeader.textContent = 'New Vehicle Invoice';
            if (dealershipDetails) dealershipDetails.style.display = 'block';
            if (dealershipExtras) dealershipExtras.style.display = 'block';
            if (invoiceTable) invoiceTable.style.display = 'none';
            break;
        case 'recurring-invoice':
            if (invoiceHeader) invoiceHeader.textContent = 'New Recurring Invoice';
            if (recurringDateInput) recurringDateInput.disabled = false;
            break;
        default:
            if (invoiceHeader) invoiceHeader.textContent = 'New Invoice';
            break;
    }
    console.log('Modal visibility updated.');
}

// --- DOMContentLoaded Master Event Handler ---

document.addEventListener('DOMContentLoaded', function () {
    // Customer name search
    const customerNameInput = document.getElementById('customer-name');
    if (customerNameInput) {
        customerNameInput.addEventListener('input', function() { searchClientOrCompany(customerNameInput); });
    }
    // Salesperson search
    const salespersonInput = document.getElementById('salesperson');
    if (salespersonInput) {
        salespersonInput.addEventListener('input', function() { searchSalesperson(salespersonInput); });
    }
    // Item code search
    const itemCodeInput = document.getElementById('item-code');
    if (itemCodeInput) {
        itemCodeInput.addEventListener('input', function() { searchItem(itemCodeInput, 'item-code'); });
    }
    // Description search
    const descriptionInput = document.getElementById('description');
    if (descriptionInput) {
        descriptionInput.addEventListener('input', function() { searchItem(descriptionInput, 'description'); });
    }
    // Unit price removeR and formatPrice
    const unitPriceInput = document.getElementById('unit-price');
    if (unitPriceInput) {
        unitPriceInput.addEventListener('input', function() { removeR(unitPriceInput); });
        unitPriceInput.addEventListener('focus', function() { removeR(unitPriceInput); });
        unitPriceInput.addEventListener('blur', function() { formatPrice(unitPriceInput); });
    }
    // Save and Preview button event listeners
    const saveBtn = document.getElementById('save-invoice');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveInvoiceDraft);
    }
    const previewBtn = document.getElementById('preview-invoice');
    if (previewBtn) {
        previewBtn.addEventListener('click', generateInvoicePDF);
    }
});

import { fetchCompanyInfoApi, fetchNextQuotationNumberPreview, fetchNextInvoiceNumberPreview, generateInvoicePDF, saveInvoiceApi,fetchProductSearchResults } from './invoice-api.js';
import { searchClientOrCompany, searchSalesperson, getInvoiceFormData } from './invoice-form.js';

async function saveInvoiceDraft() {
    try {
        showLoadingModal('Saving draft...');
        const formData = getInvoiceFormData();
        // Remove invoice_number for draft
        formData.invoice_number = '';
        // Set status to Draft (status_id should be set by backend, but can be sent as a hint)
        formData.status = 'Draft';
        // Set invoice_type based on dropdown
        const invoiceTypeDropdown = document.getElementById('invoice-type');
        formData.invoice_type = invoiceTypeDropdown ? invoiceTypeDropdown.value : 'quotation';
        // Call save API (no PDF generation)
        const result = await saveInvoiceApi(formData, window.selectedPartyType || 'customer', {});
        hideLoadingModal();
        if (result.success) {
            showResponseModal('Draft saved successfully', 'success');
        } else {
            showResponseModal(result.message || 'Failed to save draft', 'error');
        }
    } catch (err) {
        hideLoadingModal();
        showResponseModal('Error saving draft: ' + (err.message || err), 'error');
    }
}


async function Create() {
    try {
        // Show warning before finalizing
        if (!confirm('Finalize and create invoice will prevent further editing. Are you sure you want to continue?')) {
            return;
        }
        showLoadingModal('Finalizing and creating invoice...');
        const formData = getInvoiceFormData();
        // Assign/increment number
        const invoiceTypeDropdown = document.getElementById('invoice-type');
        const invoiceType = invoiceTypeDropdown ? invoiceTypeDropdown.value : 'quotation';
        let finalNumber = '';
        if (invoiceType === 'quotation') {
            finalNumber = await fetchNextQuotationNumberPreview();
        } else {
            finalNumber = await fetchNextInvoiceNumberPreview();
        }
        formData.invoice_number = finalNumber;
        // Set status to Approved (get status_id from backend)
        formData.status = 'Approved';
        formData.invoice_type = invoiceType;
        // Save to DB and generate PDF
        const result = await saveInvoiceApi(formData, window.selectedPartyType || 'customer', {});
        if (result.success) {
            // Generate PDF after successful save
            await generateInvoicePDF();
            showResponseModal('Invoice finalized and PDF generated!', 'success');
        } else {
            showResponseModal(result.message || 'Failed to finalize invoice', 'error');
        }
        hideLoadingModal();
    } catch (err) {
        hideLoadingModal();
        showResponseModal('Error finalizing invoice: ' + (err.message || err), 'error');
    }
}


export {
    getInvoiceFormData,
    openInvoiceModal,
    closeInvoiceModal,
    setModalMode
};

// Attach global functions for inline HTML handlers
if (typeof removeR === 'function') window.removeR = removeR;
if (typeof formatPrice === 'function') window.formatPrice = formatPrice;
if (typeof addItem === 'function') window.addItem = addItem;
if (typeof removeItem === 'function') window.removeItem = removeItem;
if (typeof addDiscount === 'function') window.addDiscount = addDiscount;
if (typeof searchItem === 'function') window.searchItem = searchItem;

