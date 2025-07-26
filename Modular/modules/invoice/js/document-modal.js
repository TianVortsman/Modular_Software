// Refactored for new modal structure and DB schema
// All modal logic, event handlers, and line item management now use new IDs/classes from document-modal.php
// All logic is mapped to the unified invoicing.clients and invoicing.documents schema

import { searchClients, searchSalespeople, searchProducts, saveDocumentApi, previewDocumentPDF } from './document-api.js';
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
    // Only treat as finalized if NOT in create mode and status is not draft (case-insensitive)
    const statusInput = document.getElementById('document-status');
    const statusVal = statusInput && statusInput.value ? statusInput.value.toLowerCase() : '';
    const isFinalized = !isCreate && statusInput && statusVal && statusVal !== 'draft';
    fields.forEach(field => {
        if (isView || isFinalized) {
            field.setAttribute('disabled', 'disabled');
        } else {
            field.removeAttribute('disabled');
        }
    });
    document.getElementById('current-date')?.setAttribute('disabled', 'disabled');
    document.getElementById('document-number')?.setAttribute('disabled', 'disabled');
    // Hide remove-row buttons in view/finalized mode
    modal.querySelectorAll('.remove-row').forEach(btn => {
        btn.style.display = (isView || isFinalized) ? 'none' : '';
    });
    // Hide save/create buttons if finalized
    const saveBtn = document.getElementById('save-document');
    const createBtn = modal.querySelector('button[onclick="createOrUpdateDocument()"]');
    if (isFinalized) {
        if (saveBtn) saveBtn.style.display = 'none';
        if (createBtn) createBtn.style.display = 'none';
        // Show finalized message
        let msg = document.getElementById('finalized-msg');
        if (!msg) {
            msg = document.createElement('div');
            msg.id = 'finalized-msg';
            msg.textContent = 'This document is finalized and cannot be edited.';
            msg.style.color = 'red';
            msg.style.margin = '12px 0';
            modal.querySelector('.modal-document-footer')?.prepend(msg);
        }
    } else {
        if (saveBtn) saveBtn.style.display = '';
        if (createBtn) createBtn.style.display = '';
        const msg = document.getElementById('finalized-msg');
        if (msg) msg.remove();
    }
    // Update modal header
    const header = modal.querySelector('.modal-document-title');
    if (header) {
        if (isCreate) header.textContent = 'New Document';
        else if (isEdit) header.textContent = 'Edit Document';
        else if (isView) header.textContent = 'View Document';
    }
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
    
    // Only clear data if creating a new document
    if (mode === 'create') {
        // Clear all inputs for new document
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
                    <td style="display:none;">
                        <input type="hidden" class="product-id">
                        <input type="hidden" class="item-id">
                    </td>
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
        
        // Set current date for new documents
        const dateInput = document.getElementById('current-date');
        if (dateInput) {
            const today = new Date();
            const formattedDate = today.toISOString().split('T')[0];
            dateInput.value = formattedDate;
        }
        
        // Set pay-in-days to 30 by default for new documents
        const payInDaysSelect = document.getElementById('pay-in-days');
        if (payInDaysSelect) {
            payInDaysSelect.value = '30';
        }
    }
    
    // Set modal mode (this will handle UI state, disable fields if finalized, etc.)
    setModalMode(mode);
    
    // Always setup row listeners and update totals after data is loaded
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

// --- Preview Next Document Number Logic ---
async function previewNextDocumentNumber() {
    const docType = document.getElementById('document-type')?.value || 'invoice';
    let endpoint = '';
    switch (docType) {
        case 'quotation':
        case 'vehicle-quotation':
            endpoint = '../api/document-api.php?action=preview_quotation_number';
            break;
        case 'invoice':
        case 'standard-invoice':
        case 'vehicle-invoice':
        case 'recurring-invoice':
            endpoint = '../api/document-api.php?action=preview_invoice_number';
            break;
        case 'credit-note':
            endpoint = '../api/document-api.php?action=preview_credit_note_number';
            break;
        case 'pro-forma':
            endpoint = '../api/document-api.php?action=preview_proforma_number';
            break;
        default:
            endpoint = '../api/document-api.php?action=preview_invoice_number';
    }
    try {
        const res = await fetch(endpoint, { credentials: 'include' });
        const data = await res.json();
        if (data.success && data.number) {
            const docNumInput = document.getElementById('document-number');
            if (docNumInput) {
                docNumInput.value = data.number + ' (Preview)';
                docNumInput.setAttribute('data-preview', data.number);
            }
        }
    } catch (err) {
        // Optionally show error or fallback
    }
}

// --- Wrapper for global usage ---
function openDocumentModalWithPreview(mode = 'create') {
    openDocumentModal(mode);
    previewNextDocumentNumber();
    const docType = document.getElementById('document-type');
    if (docType) {
        docType.removeEventListener('change', previewNextDocumentNumber);
        docType.addEventListener('change', previewNextDocumentNumber);
    }
}
window.openDocumentModal = openDocumentModalWithPreview;
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
        <td style="display:none;">
            <input type="hidden" class="product-id">
            <input type="hidden" class="item-id">
        </td>
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
                <option value="0" data-tax-id="1">[None]</option>
                <option value="10" data-tax-id="3">10%</option>
                <option value="15" data-tax-id="2">15%</option>
                <option value="20" data-tax-id="4">20%</option>
                <option value="25" data-tax-id="5">25%</option>
            </select>
            <input type="hidden" class="tax-rate-id" value="">
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
            taxInput.onchange = function() { 
                // Update hidden tax_rate_id field when tax dropdown changes
                const selectedOption = taxInput.options[taxInput.selectedIndex];
                const taxRateId = selectedOption.getAttribute('data-tax-id') || '';
                const hiddenTaxRateIdField = row.querySelector('.tax-rate-id');
                if (hiddenTaxRateIdField) {
                    hiddenTaxRateIdField.value = taxRateId;
                }
                calculateRowTotal(row); 
                updateTotals(); 
            };
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

// --- Product Search for Line Items with Keyboard Navigation ---
function searchItem(inputElement) {
    const searchTerm = inputElement.value.trim();
    const row = inputElement.closest('tr');
    const resultsContainer1 = row.querySelector('.search-dropdown1');
    const resultsContainer2 = row.querySelector('.search-dropdown2');
    const isDescription = inputElement.classList.contains('description');
    const resultsContainer = isDescription ? resultsContainer2 : resultsContainer1;

    // Remove any previous keyboard event handler to avoid stacking
    if (inputElement._keydownHandler) {
        inputElement.removeEventListener('keydown', inputElement._keydownHandler);
        inputElement._keydownHandler = null;
    }

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
                li.tabIndex = -1; // allow focus for accessibility
                li.dataset.idx = idx;
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
                ul.appendChild(li);
            });
            resultsContainer.appendChild(ul);

            // Highlight the first result by default
            let currentIdx = 0;
            const items = ul.querySelectorAll('.search-result-item');
            if (items.length > 0) items[0].classList.add('highlight');

            // Keyboard navigation handler
            const keydownHandler = function(event) {
                if (!resultsContainer || resultsContainer.style.display !== 'block') return;
                const items = ul.querySelectorAll('.search-result-item');
                if (!items.length) return;

                // Find the currently highlighted index
                let highlightIdx = Array.from(items).findIndex(li => li.classList.contains('highlight'));
                if (highlightIdx === -1) highlightIdx = 0;

                if (event.key === 'ArrowDown') {
                    event.preventDefault();
                    items[highlightIdx].classList.remove('highlight');
                    highlightIdx = (highlightIdx + 1) % items.length;
                    items[highlightIdx].classList.add('highlight');
                    items[highlightIdx].scrollIntoView({ block: 'nearest' });
                } else if (event.key === 'ArrowUp') {
                    event.preventDefault();
                    items[highlightIdx].classList.remove('highlight');
                    highlightIdx = (highlightIdx - 1 + items.length) % items.length;
                    items[highlightIdx].classList.add('highlight');
                    items[highlightIdx].scrollIntoView({ block: 'nearest' });
                } else if (event.key === 'Enter') {
                    event.preventDefault();
                    const selected = items[highlightIdx];
                    if (selected) {
                        const idx = parseInt(selected.dataset.idx, 10);
                        const result = results[idx];
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
                } else if (event.key === 'Tab' && results.length > 0) {
                    // Tab selects the first result
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

            inputElement.addEventListener('keydown', keydownHandler);
            inputElement._keydownHandler = keydownHandler;
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
    const taxRateIdField = row.querySelector('.tax-rate-id');
    if (taxDropdown) {
        let taxVal = '';
        if (typeof result.tax_percentage !== 'undefined' && result.tax_percentage !== null) {
            taxVal = String(parseFloat(result.tax_percentage));
        }
        if (taxVal) {
            for (let option of taxDropdown.options) {
                if (option.value === taxVal || option.value === taxVal + '%') {
                    taxDropdown.value = option.value;
                    // Also set the tax_rate_id in the hidden field
                    if (taxRateIdField && option.getAttribute('data-tax-id')) {
                        taxRateIdField.value = option.getAttribute('data-tax-id');
                    }
                    break;
                }
            }
        }
    }
    // Set tax_rate_id directly if available from result
    if (taxRateIdField && result.tax_rate_id) {
        taxRateIdField.value = result.tax_rate_id;
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
    const previewPdfBtn = document.getElementById('preview-pdf-btn');
    if (previewPdfBtn) {
        previewPdfBtn.addEventListener('click', async function() {
            try {
                showLoadingModal('Generating PDF preview...');
                const formData = getDocumentFormData();
                const result = await previewDocumentPDF(formData);
                hideLoadingModal();
                if (result.success && result.url) {
                    window.open(result.url, '_blank');
                } else {
                    showResponseModal(result.message || 'Failed to generate PDF preview', 'error');
                }
            } catch (err) {
                hideLoadingModal();
                showResponseModal('Error generating PDF preview: ' + (err.message || err), 'error');
            }
        });
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
        
        console.log('[saveDocumentDraft] API result:', result);
        
        // Use the centralized error handling from sidebar.js
        if (result.success === false) {
            // This will be handled by handleApiResponse which shows the error modal
            window.handleApiResponse(result);
        } else if (result.success === true) {
            // Show success message
            let msg = 'Draft saved successfully';
            if (result.data && result.data.document_number) {
                msg += ` (Number: ${result.data.document_number})`;
            }
            showResponseModal(msg, 'success');
        }
        
    } catch (err) {
        hideLoadingModal();
        console.error('[saveDocumentDraft] Error:', err);
        showResponseModal('Error saving draft: ' + (err.message || err), 'error');
    }
}

async function saveDocumentFinal() {
    try {
        showLoadingModal('Saving document...');
        const formData = getDocumentFormData();
        formData.document_status = 'Finalized';
        formData.document_type = document.getElementById('document-type') ? document.getElementById('document-type').value : 'invoice';
        const result = await saveDocumentApi(formData, {});
        hideLoadingModal();
        
        console.log('[saveDocumentFinal] API result:', result);
        
        // Use the centralized error handling from sidebar.js
        if (result.success === false) {
            // This will be handled by handleApiResponse which shows the error modal
            window.handleApiResponse(result);
        } else if (result.success === true) {
            // Show success message
            let msg = 'Document saved successfully';
            if (result.data && result.data.document_number) {
                msg += ` (Number: ${result.data.document_number})`;
            }
            showResponseModal(msg, 'success');
        }
        
    } catch (err) {
        hideLoadingModal();
        console.error('[saveDocumentFinal] Error:', err);
        showResponseModal('Error saving document: ' + (err.message || err), 'error');
    }
}

// Add similar logic for finalized document save if needed

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

// Export functions needed for document editing
window.setModalMode = setModalMode;
window.setupRowListeners = setupRowListeners;
window.updateTotals = updateTotals;
window.openDocumentModal = openDocumentModal;
window.closeDocumentModal = closeDocumentModal;

// Replace the old createOrUpdateDocument global with saveDocumentFinal
window.createOrUpdateDocument = saveDocumentFinal;

