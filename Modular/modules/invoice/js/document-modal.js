// Refactored for new modal structure and DB schema
// All modal logic, event handlers, and line item management now use new IDs/classes from document-modal.php
// All logic is mapped to the unified invoicing.clients and invoicing.documents schema

import { searchClients, searchSalespeople, searchProducts, saveDocumentApi, generateInvoicePDF } from './document-api.js';
import { searchClient, searchSalesperson, getDocumentFormData, setDocumentFormData } from './document-form.js';

// --- Utility Functions ---
function removeR(input) {
    if (input.value.startsWith('R')) {
        input.value = input.value.slice(1);
    }
}
function formatPrice(input) {
    let value = parseFloat(input.value.replace(/[^0-9.-]+/g, ""));
    if (!isNaN(value)) {
        if (value < 0) {
            input.value = `-R${Math.abs(value).toFixed(2)}`;
        } else {
            input.value = `R${value.toFixed(2)}`;
        }
    } else {
        input.value = "R0.00";
    }
}
function formatDiscount(input) {
    let value = input.value.replace(/[^0-9.-]/g, "");
    if (value === "" || value === "-") {
        input.value = "-R0.00";
        return;
    }
    let parsedValue = parseFloat(value);
    if (!isNaN(parsedValue)) {
        if (parsedValue > 0) {
            parsedValue = -parsedValue;
        }
        input.value = `-R${Math.abs(parsedValue).toFixed(2)}`;
    } else {
        input.value = "-R0.00";
    }
}

// --- Modal Mode ---
function setModalMode(mode) {
    const modal = document.getElementById('document-modal');
    if (!modal) return;
    modal.setAttribute('data-mode', mode);
    const isView = mode === 'view';
    const isEdit = mode === 'edit';
    const isCreate = mode === 'create';
    const fields = modal.querySelectorAll('input, select, textarea');
    fields.forEach(field => {
        if (isView) {
            field.setAttribute('disabled', 'disabled');
        } else {
            field.removeAttribute('disabled');
        }
    });
    document.getElementById('current-date')?.setAttribute('disabled', 'disabled');
    document.getElementById('document-number')?.setAttribute('disabled', 'disabled');
    // Buttons (update as needed for your modal)
    // Hide remove-row buttons in view mode
    modal.querySelectorAll('.remove-row').forEach(btn => {
        btn.style.display = isView ? 'none' : '';
    });
}

// --- Section Show/Hide Logic ---
function updateSectionVisibility() {
    const type = document.getElementById('document-type').value;
    document.querySelectorAll('[data-show-on]').forEach(section => {
        const showOn = section.getAttribute('data-show-on').split(',');
        section.style.display = showOn.includes(type) ? '' : 'none';
    });
    // Vehicle sections: hide unless vehicle-invoice or vehicle-quotation
    const vehicleSections = document.querySelectorAll('.vehicle-section, .vehicle-extras-section');
    if (type === 'vehicle-invoice' || type === 'vehicle-quotation') {
        vehicleSections.forEach(sec => sec.classList.remove('hidden'));
    } else {
        vehicleSections.forEach(sec => sec.classList.add('hidden'));
    }
}

// --- Open/Close Modal ---
function openDocumentModal(mode = 'create') {
    const modal = document.getElementById('document-modal');
    if (!modal) return;
    modal.style.display = 'flex';
    setModalMode(mode);
    if (mode === 'create') {
        modal.querySelectorAll('input, textarea').forEach(input => {
            if (input.type === 'hidden') return;
            input.value = '';
        });
        modal.querySelectorAll('select').forEach(select => {
            select.selectedIndex = 0;
        });
        // Clear table rows and add back the default fixed line item row
        const tableBody = document.getElementById('document-rows');
        if (tableBody) {
            tableBody.innerHTML = `
                <tr class="document-item-row">
                    <td><input type="number" value="1" class="quantity"></td>
                    <td style="display:none;"><input type="hidden" class="product-id"></td>
                    <td>
                        <div class="search-container" style="position: relative;">
                            <input type="text" placeholder="Search Item Code" class="item-code" autocomplete="off">
                            <div class="search-dropdown1"></div>
                        </div>
                    </td>
                    <td>
                        <div class="search-container" style="position: relative;">
                            <input type="text" placeholder="Search Description" class="description" autocomplete="off">
                            <div class="search-dropdown2"></div>
                        </div>
                    </td>
                    <td><input type="text" value="R0.00" class="unit-price"></td>
                    <td>
                        <select class="tax">
                            <option value="0">[None]</option>
                            <option value="10">10%</option>
                            <option value="15">15%</option>
                            <option value="20">20%</option>
                            <option value="25">25%</option>
                        </select>
                    </td>
                    <td>
                        <button type="button" class="toggle-line-discount-btn" title="Add Discount">+ Discount</button>
                        <div class="line-discount-input" style="display:none;margin-top:4px;">
                            <input type="text" class="line-discount" placeholder="10% or R50">
                        </div>
                    </td>
                    <td class="total-cell">
                        <span class="total">0.00</span>
                        <button type="button" class="remove-row-btn" title="Remove Line">&#10006;</button>
                    </td>
                    <td class="stock" style="display:none;">0</td>
                </tr>
            `;
        }
    }
    // Set current date
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
    // Setup row listeners and update totals
    setupRowListeners();
    updateTotals();
    // Setup client name input search
    const clientNameInput = document.getElementById('client-name');
    if (clientNameInput) {
        clientNameInput.oninput = function () { searchClient(clientNameInput); };
    }
    // Setup salesperson search
    const salespersonInput = document.getElementById('salesperson');
    if (salespersonInput) {
        salespersonInput.oninput = function () { searchSalesperson(salespersonInput); };
    }
    // Update section visibility
    updateSectionVisibility();
    // Add event listener for document-type changes
    const docType = document.getElementById('document-type');
    if (docType) {
        docType.removeEventListener('change', updateSectionVisibility);
        docType.addEventListener('change', updateSectionVisibility);
    }
}
function closeDocumentModal() {
    document.getElementById('document-modal').style.display = 'none';
    // Optionally refresh dashboard or list
}

// --- Add/Remove Item Rows ---
function addDocumentItem() {
    const tableBody = document.getElementById('document-rows');
    if (!tableBody) return;
    const row = document.createElement('tr');
    row.classList.add('document-item-row');
    row.innerHTML = `
        <td><input type="number" value="1" class="quantity"></td>
        <td style="display:none;"><input type="hidden" class="product-id"></td>
        <td>
            <div class="search-container" style="position: relative;">
                <input type="text" placeholder="Search Item Code" class="item-code" autocomplete="off">
                <div class="search-dropdown1"></div>
            </div>
        </td>
        <td>
            <div class="search-container" style="position: relative;">
                <input type="text" placeholder="Search Description" class="description" autocomplete="off">
                <div class="search-dropdown2"></div>
            </div>
        </td>
        <td><input type="text" value="R0.00" class="unit-price"></td>
        <td>
            <select class="tax">
                <option value="0">[None]</option>
                <option value="10">10%</option>
                <option value="15">15%</option>
                <option value="20">20%</option>
                <option value="25">25%</option>
            </select>
        </td>
        <td>
            <button type="button" class="toggle-line-discount-btn" title="Add Discount">+ Discount</button>
            <div class="line-discount-input" style="display:none;margin-top:4px;">
                <input type="text" class="line-discount" placeholder="10% or R50">
            </div>
        </td>
        <td class="total-cell">
            <span class="total">0.00</span>
            <button type="button" class="remove-row-btn" title="Remove Line">&#10006;</button>
        </td>
        <td class="stock" style="display:none;">0</td>
    `;
    tableBody.appendChild(row);
    setupRowListeners(); // Ensure all events are bound for new row
}

function addDocumentDiscount(row = null) {
    const tableBody = document.getElementById('document-rows');
    if (!tableBody) return;
    const discountRow = document.createElement('tr');
    discountRow.classList.add('document-item-row', 'discount-row');
    discountRow.innerHTML = `
        <td><input type="number" value="1" class="quantity" disabled></td>
        <td style="display:none;"><input type="hidden" class="product-id"></td>
        <td><input type="text" placeholder="Discount" class="item-code" value="Discount" disabled></td>
        <td><input type="text" placeholder="Discount" class="description" value="Discount" disabled></td>
        <td><input type="text" value="-R0.00" class="unit-price" oninput="removeR(this)" onblur="formatDiscount(this)" onfocus="removeR(this)"></td>
        <td>
            <select class="tax" disabled>
                <option value="0">[None]</option>
            </select>
        </td>
        <td></td>
        <td class="total-cell">
            <span class="total">R0.00</span>
            <button type="button" class="remove-row-btn" title="Remove Line">&#10006;</button>
        </td>
        <td class="stock" style="display:none;">0</td>
    `;
    if (row && row.parentNode) {
        row.parentNode.insertBefore(discountRow, row.nextSibling);
    } else {
        tableBody.appendChild(discountRow);
    }
    setupRowListeners();
}

function removeItem(event) {
    const button = event.target;
    const row = button.closest('tr');
    if (row) {
        row.remove();
        updateTotals();
    }
}

// --- Row Calculation ---
function calculateRowTotal(row) {
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const unitPrice = parseFloat(row.querySelector('.unit-price').value.replace(/[^\d.-]/g, '')) || 0;
    const taxPercentage = parseFloat(row.querySelector('.tax').value) || 0;
    // Per-line discount
    let discount = 0;
    const discountInput = row.querySelector('.line-discount');
    if (discountInput && discountInput.value.trim() !== '') {
        const val = discountInput.value.trim();
        if (val.endsWith('%')) {
            discount = (parseFloat(val) / 100) * (quantity * unitPrice);
        } else if (val.startsWith('R')) {
            discount = parseFloat(val.replace(/[^\d.-]/g, ''));
        } else {
            discount = parseFloat(val);
        }
        if (isNaN(discount)) discount = 0;
    }
    let rowTotalBeforeTax = quantity * unitPrice - discount;
    if (rowTotalBeforeTax < 0) rowTotalBeforeTax = 0;
    const rowTax = (rowTotalBeforeTax * taxPercentage) / 100;
    const rowTotal = rowTotalBeforeTax + rowTax;
    const totalElement = row.querySelector('.total');
    if (totalElement) {
        const formattedTotal = rowTotal < 0
            ? `-R${Math.abs(rowTotal).toFixed(2)}`
            : `R${rowTotal.toFixed(2)}`;
        totalElement.textContent = formattedTotal;
    }
    return {
        rowTotalBeforeTax,
        rowTax,
        rowTotal
    };
}
function updateTotals() {
    let subtotal = 0;
    let totalTax = 0;
    let finalTotal = 0;
    const rows = document.querySelectorAll('#document-rows tr');
    rows.forEach(row => {
        if (row.querySelector('.quantity') && row.querySelector('.unit-price') && row.querySelector('.tax')) {
            const rowTotals = calculateRowTotal(row);
            subtotal += rowTotals.rowTotalBeforeTax;
            totalTax += rowTotals.rowTax;
            finalTotal += rowTotals.rowTotal;
        }
    });
    document.getElementById('subtotal').textContent = subtotal.toFixed(2);
    document.getElementById('tax-total').textContent = totalTax.toFixed(2);
    document.getElementById('final-total').textContent = finalTotal.toFixed(2);
}
function setupRowListeners() {
    const tableBody = document.getElementById('document-rows');
    if (!tableBody) return;
    // Remove row button logic (event delegation)
    tableBody.removeEventListener('click', handleRemoveRowBtnClick);
    tableBody.addEventListener('click', handleRemoveRowBtnClick);
    // Toggle per-line discount input
    tableBody.querySelectorAll('.toggle-line-discount-btn').forEach(btn => {
        btn.onclick = function(e) {
            const discountDiv = btn.nextElementSibling;
            if (discountDiv) {
                discountDiv.style.display = discountDiv.style.display === 'none' ? 'block' : 'none';
            }
        };
    });
    // Hide remove button by default, show on hover
    tableBody.querySelectorAll('.total-cell').forEach(cell => {
        if (cell.querySelector('.remove-row-btn')) {
            cell.querySelector('.remove-row-btn').style.display = 'none';
        }
    });
    // --- PRODUCT SEARCH, AUTOFILL, AND CALCULATION EVENTS ---
    tableBody.querySelectorAll('tr').forEach(row => {
        // Product search for item code
        const itemCodeInput = row.querySelector('.item-code');
        if (itemCodeInput) {
            itemCodeInput.oninput = function() { searchItem(itemCodeInput); };
            itemCodeInput.onkeydown = null;
        }
        // Product search for description
        const descInput = row.querySelector('.description');
        if (descInput) {
            descInput.oninput = function() { searchItem(descInput); };
            descInput.onkeydown = null;
        }
        // Quantity, price, tax, discount: recalc on change
        const qtyInput = row.querySelector('.quantity');
        if (qtyInput) {
            qtyInput.oninput = function() { calculateRowTotal(row); updateTotals(); };
        }
        const priceInput = row.querySelector('.unit-price');
        if (priceInput) {
            priceInput.oninput = function() { removeR(priceInput); calculateRowTotal(row); updateTotals(); };
            priceInput.onfocus = function() { removeR(priceInput); };
            priceInput.onblur = function() { formatPrice(priceInput); calculateRowTotal(row); updateTotals(); };
        }
        const taxInput = row.querySelector('.tax');
        if (taxInput) {
            taxInput.onchange = function() { calculateRowTotal(row); updateTotals(); };
        }
        const discountInput = row.querySelector('.line-discount');
        if (discountInput) {
            discountInput.oninput = function() { calculateRowTotal(row); updateTotals(); };
        }
        // --- PRODUCT SEARCH DROPDOWN/KEYBOARD UX ---
        // Item code
        if (itemCodeInput) {
            itemCodeInput.addEventListener('keydown', function(event) {
                const row = itemCodeInput.closest('tr');
                const resultsContainer = row.querySelector('.search-dropdown1');
                if (event.key === 'Tab' && resultsContainer && resultsContainer.style.display === 'block') {
                    const firstResult = resultsContainer.querySelector('.search-result-item');
                    if (firstResult) {
                        firstResult.click();
                        event.preventDefault();
                        // Move focus to next logical field (quantity)
                        const next = row.querySelector('.quantity');
                        if (next && typeof next.focus === 'function') next.focus();
                    }
                }
            });
        }
        // Description
        if (descInput) {
            descInput.addEventListener('keydown', function(event) {
                const row = descInput.closest('tr');
                const resultsContainer = row.querySelector('.search-dropdown2');
                if (event.key === 'Tab' && resultsContainer && resultsContainer.style.display === 'block') {
                    const firstResult = resultsContainer.querySelector('.search-result-item');
                    if (firstResult) {
                        firstResult.click();
                        event.preventDefault();
                        // Move focus to next logical field (quantity)
                        const next = row.querySelector('.quantity');
                        if (next && typeof next.focus === 'function') next.focus();
                    }
                }
            });
        }
        // Dropdown closes on blur
        if (itemCodeInput) {
            const resultsContainer = row.querySelector('.search-dropdown1');
            itemCodeInput.addEventListener('blur', function() {
                setTimeout(() => { if (resultsContainer) resultsContainer.style.display = 'none'; }, 150);
            });
        }
        if (descInput) {
            const resultsContainer = row.querySelector('.search-dropdown2');
            descInput.addEventListener('blur', function() {
                setTimeout(() => { if (resultsContainer) resultsContainer.style.display = 'none'; }, 150);
            });
        }
    });
}
function handleRemoveRowBtnClick(e) {
    if (e.target.classList.contains('remove-row-btn')) {
        const row = e.target.closest('tr');
        if (row) {
            row.remove();
            updateTotals();
        }
    }
}

// --- Product Search for Line Items ---
function searchItem(inputElement) {
    const searchTerm = inputElement.value.trim();
    const row = inputElement.closest('tr');
    const resultsContainer1 = row.querySelector('.search-dropdown1');
    const resultsContainer2 = row.querySelector('.search-dropdown2');
    const isDescription = inputElement.classList.contains('description');
    const resultsContainer = isDescription ? resultsContainer2 : resultsContainer1;
    if (searchTerm.length < 2) {
        resultsContainer.innerHTML = '';
        resultsContainer.style.display = 'none';
        return;
    }
    resultsContainer.style.display = 'block';
    searchProducts(searchTerm, function(results) {
        resultsContainer.innerHTML = '';
        if (results.length > 0) {
            const ul = document.createElement('ul');
            ul.classList.add('search-results-list');
            results.forEach((result, idx) => {
                const li = document.createElement('li');
                li.classList.add('search-result-item');
                li.textContent = isDescription ? result.product_description : (result.sku || result.product_name);
                li.addEventListener('click', () => {
                    autofillRow(row, {
                        item_code: result.sku || '',
                        product_description: result.product_description || '',
                        unit_price: result.product_price || '',
                        product_id: result.product_id,
                        tax_percentage: result.tax_rate || 0
                    });
                    resultsContainer.style.display = 'none';
                    // Move focus to next logical field (quantity)
                    const next = row.querySelector('.quantity');
                    if (next && typeof next.focus === 'function') next.focus();
                });
                if (idx === 0) li.classList.add('highlight'); // highlight top result
                ul.appendChild(li);
            });
            resultsContainer.appendChild(ul);
            inputElement.onkeydown = function (event) {
                if (event.key === 'Tab' && results.length > 0) {
                    event.preventDefault();
                    const result = results[0];
                    autofillRow(row, {
                        item_code: result.sku || '',
                        product_description: result.product_description || '',
                        unit_price: result.product_price || '',
                        product_id: result.product_id,
                        tax_percentage: result.tax_rate || 0
                    });
                    resultsContainer.style.display = 'none';
                    // Move focus to next logical field (quantity)
                    const next = row.querySelector('.quantity');
                    if (next && typeof next.focus === 'function') next.focus();
                }
            };
        } else {
            resultsContainer.innerHTML = "<p>No results found.</p>";
        }
    });
}
function autofillRow(row, result) {
    row.querySelector('.item-code').value = result.item_code;
    row.querySelector('.description').value = result.product_description;
    row.querySelector('.unit-price').value = result.unit_price;
    row.querySelector('.product-id').value = result.product_id;
    const taxDropdown = row.querySelector('.tax');
    if (taxDropdown) {
        let taxVal = '';
        if (typeof result.tax_percentage !== 'undefined' && result.tax_percentage !== null) {
            taxVal = String(parseFloat(result.tax_percentage));
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
    calculateRowTotal(row);
    updateTotals();
}

// --- DOMContentLoaded Master Event Handler ---
document.addEventListener('DOMContentLoaded', function () {
    // Client name search
    const clientNameInput = document.getElementById('client-name');
    if (clientNameInput) {
        clientNameInput.addEventListener('input', function() { searchClient(clientNameInput); });
    }
    // Salesperson search
    const salespersonInput = document.getElementById('salesperson');
    if (salespersonInput) {
        salespersonInput.addEventListener('input', function() { searchSalesperson(salespersonInput); });
    }
    // Save and Preview button event listeners
    const saveBtn = document.getElementById('save-document');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveDocumentDraft);
    }
    const previewBtn = document.querySelector('button[onclick="previewDocument()"]');
    if (previewBtn) {
        previewBtn.addEventListener('click', generateInvoicePDF);
    }
    // Add item/discount buttons
    const addItemBtn = document.getElementById('add-item-btn');
    if (addItemBtn && !addItemBtn.hasListener) {
        addItemBtn.addEventListener('click', addDocumentItem);
        addItemBtn.hasListener = true;
    }
    const addDiscountBtn = document.getElementById('add-discount-btn');
    if (addDiscountBtn && !addDiscountBtn.hasListener) {
        addDiscountBtn.addEventListener('click', addDocumentDiscount);
        addDiscountBtn.hasListener = true;
    }
});

// --- Save/Preview Logic ---
async function saveDocumentDraft() {
    try {
        showLoadingModal('Saving draft...');
        const formData = getDocumentFormData();
        formData.document_status = 'Draft';
        formData.document_type = document.getElementById('document-type') ? document.getElementById('document-type').value : 'quotation';
        const result = await saveDocumentApi(formData, {});
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

// --- Modal Close on Escape and Close Button ---
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeDocumentModal();
    }
});
const closeBtn = document.getElementById('modal-document-close-btn');
if (closeBtn) {
    closeBtn.onclick = closeDocumentModal;
}

export {
    getDocumentFormData,
    openDocumentModal,
    closeDocumentModal,
    setModalMode,
    addDocumentItem,
    addDocumentDiscount,
    removeItem,
    searchItem,
    autofillRow
};

// Attach global functions for inline HTML handlers
if (typeof removeR === 'function') window.removeR = removeR;
if (typeof formatPrice === 'function') window.formatPrice = formatPrice;
if (typeof addDocumentItem === 'function') window.addDocumentItem = addDocumentItem;
if (typeof removeItem === 'function') window.removeItem = removeItem;
if (typeof addDocumentDiscount === 'function') window.addDocumentDiscount = addDocumentDiscount;
if (typeof searchItem === 'function') window.searchItem = searchItem;

