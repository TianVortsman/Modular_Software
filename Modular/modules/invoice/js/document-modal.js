// Refactored for new modal structure and DB schema
// All modal logic, event handlers, and line item management now use new IDs/classes from document-modal.php
// All logic is mapped to the unified invoicing.clients and invoicing.documents schema

// Initialize Lucide icons when available
function initializeLucideIcons() {
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    } else {
        console.warn('Lucide icons not loaded');
    }
}

// Initialize live preview functionality
function initializeLivePreview() {
    const togglePreview = document.getElementById('toggle-preview');
    if (togglePreview) {
        togglePreview.addEventListener('click', () => {
            const preview = document.getElementById('live-preview-modal');
            const icon = togglePreview.querySelector('i');
            
            if (preview && preview.classList.contains('hidden')) {
                preview.classList.remove('hidden');
                if (icon) icon.setAttribute('data-lucide', 'eye');
            } else if (preview) {
                preview.classList.add('hidden');
                if (icon) icon.setAttribute('data-lucide', 'eye-off');
            }
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    }
    
    // Update preview when inputs change
    document.addEventListener('input', updateLivePreview);
    document.addEventListener('change', updateLivePreview);
    
    // Initial preview update
    updateLivePreview();
}

// Initialize client panel functionality
function initializeClientPanel() {
    // Client panel is now always visible, no collapse functionality needed
    console.log('Client panel initialized - always visible');
}

// Update live preview with current form data
function updateLivePreview() {
    const docType = document.getElementById('document-type')?.value || 'invoice';
    const docNumber = document.getElementById('document-number')?.value || 'INV-2025-001';
    const issueDate = document.getElementById('current-date')?.value || new Date().toISOString().split('T')[0];
    const clientName = document.getElementById('client-name')?.value || 'Client Name';
    const clientAddress = document.getElementById('client-address-1')?.value || 'Client Address';
    const subtotal = document.getElementById('subtotal')?.textContent || 'R0.00';
    const tax = document.getElementById('tax-total')?.textContent || 'R0.00';
    const total = document.getElementById('final-total')?.textContent || 'R0.00';
    
    // Update preview elements
    const previewTitle = document.getElementById('preview-title');
    const previewNumber = document.getElementById('preview-number');
    const previewDate = document.getElementById('preview-date');
    const previewClientName = document.getElementById('preview-client-name');
    const previewClientAddress = document.getElementById('preview-client-address');
    const previewSubtotal = document.getElementById('preview-subtotal');
    const previewTax = document.getElementById('preview-tax');
    const previewTotal = document.getElementById('preview-total');
    
    if (previewTitle) previewTitle.textContent = docType.toUpperCase().replace('-', ' ');
    if (previewNumber) previewNumber.textContent = docNumber;
    if (previewDate) previewDate.textContent = issueDate;
    if (previewClientName) previewClientName.textContent = clientName;
    if (previewClientAddress) previewClientAddress.textContent = clientAddress;
    if (previewSubtotal) previewSubtotal.textContent = subtotal;
    if (previewTax) previewTax.textContent = tax;
    if (previewTotal) previewTotal.textContent = total;
    
    // Update table headers based on document type
    updatePreviewTableHeaders(docType);
    
    // Update line items in preview
    updatePreviewLineItems(docType);
}

function updatePreviewTableHeaders(docType) {
    const previewTable = document.querySelector('.preview-table thead tr');
    if (!previewTable) return;
    
    let headers = [];
    if (docType === 'vehicle-invoice' || docType === 'vehicle-quotation') {
        headers = ['Vehicle', 'VIN', 'Price', 'VAT', 'Total'];
    } else if (docType === 'credit-note') {
        headers = ['Type', 'Reason', 'Amount', 'Actions'];
    } else {
        headers = ['Description', 'Qty', 'Price', 'Total'];
    }
    
    previewTable.innerHTML = headers.map(header => `<th>${header}</th>`).join('');
}

function updatePreviewLineItems(docType) {
    const previewTableBody = document.getElementById('preview-items-body');
    if (!previewTableBody) return;
    
    let items = [];
    
    if (docType === 'vehicle-invoice' || docType === 'vehicle-quotation') {
        // Get vehicle details
        const vehicleMake = document.getElementById('vehicle-make')?.value || '';
        const vehicleModel = document.getElementById('vehicle-model')?.value || '';
        const vehicleVIN = document.getElementById('vehicle-vin')?.value || '';
        const vehiclePrice = document.getElementById('vehicle-price')?.value || 'R0.00';
        const vehicleTax = document.getElementById('vehicle-tax')?.value || '0';
        const vehicleTotal = document.getElementById('vehicle-total')?.textContent || 'R0.00';
        
        if (vehicleMake || vehicleModel) {
            items.push({
                description: `${vehicleMake} ${vehicleModel}`.trim(),
                vin: vehicleVIN,
                price: vehiclePrice,
                tax: `${vehicleTax}%`,
                total: vehicleTotal
            });
        }
        
        // Get vehicle parts
        const vehiclePartsRows = document.querySelectorAll('#vehicle-parts-rows tr');
        vehiclePartsRows.forEach(row => {
            const desc = row.querySelector('.description')?.value || '';
            const qty = row.querySelector('.quantity')?.value || '1';
            const price = row.querySelector('.unit-price')?.value || 'R0.00';
            const tax = row.querySelector('.tax')?.value || '0';
            const total = row.querySelector('.total')?.textContent || 'R0.00';
            
            if (desc) {
                items.push({
                    description: desc,
                    vin: '',
                    price: price,
                    tax: `${tax}%`,
                    total: total
                });
            }
        });
    } else if (docType === 'credit-note') {
        // Get credit note items
        const creditNoteRows = document.querySelectorAll('.credit-note-item-row');
        creditNoteRows.forEach(row => {
            const type = row.querySelector('.credit-type')?.value || '';
            const reason = row.querySelector('.credit-reason')?.value || '';
            const amount = row.querySelector('.credit-amount')?.value || 'R0.00';
            
            if (reason) {
                items.push({
                    type: type,
                    reason: reason,
                    amount: amount
                });
            }
        });
    } else {
        // Get regular line items
        const lineItemRows = document.querySelectorAll('.document-item-row:not(.discount-row)');
        lineItemRows.forEach(row => {
            const desc = row.querySelector('.description')?.value || '';
            const qty = row.querySelector('.quantity')?.value || '1';
            const price = row.querySelector('.unit-price')?.value || 'R0.00';
            const total = row.querySelector('.total')?.textContent || 'R0.00';
            
            if (desc) {
                items.push({
                    description: desc,
                    qty: qty,
                    price: price,
                    total: total
                });
            }
        });
        
        // Add discount row if exists
        const discountRow = document.querySelector('.discount-row');
        if (discountRow) {
            const discountAmount = discountRow.querySelector('.total')?.textContent || 'R0.00';
            if (discountAmount !== 'R0.00') {
                items.push({
                    description: 'Discount',
                    qty: '1',
                    price: discountAmount,
                    total: discountAmount
                });
            }
        }
    }
    
    // Generate preview rows
    if (items.length === 0) {
        let colspan = 4;
        if (docType === 'vehicle-invoice' || docType === 'vehicle-quotation') {
            colspan = 5;
        } else if (docType === 'credit-note') {
            colspan = 4;
        }
        previewTableBody.innerHTML = `<tr><td colspan="${colspan}" style="text-align: center; color: #666;">No items added</td></tr>`;
    } else {
        previewTableBody.innerHTML = items.map(item => {
            if (docType === 'vehicle-invoice' || docType === 'vehicle-quotation') {
                return `<tr>
                    <td>${item.description}</td>
                    <td>${item.vin}</td>
                    <td>${item.price}</td>
                    <td>${item.tax}</td>
                    <td>${item.total}</td>
                </tr>`;
            } else if (docType === 'credit-note') {
                return `<tr>
                    <td>${item.type}</td>
                    <td>${item.reason}</td>
                    <td>${item.amount}</td>
                    <td></td>
                </tr>`;
            } else {
                return `<tr>
                    <td>${item.description}</td>
                    <td>${item.qty}</td>
                    <td>${item.price}</td>
                    <td>${item.total}</td>
                </tr>`;
            }
        }).join('');
    }
}

// Initialize icons when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize Lucide icons
    initializeLucideIcons();
    
    // Initialize live preview functionality
    initializeLivePreview();
    
    // Initialize client panel
    initializeClientPanel();
    
    // Update live preview
    updateLivePreview();
});

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
    
    // Handle all sections with data-show-on attribute
    document.querySelectorAll('[data-show-on]').forEach(section => {
        const showOn = section.getAttribute('data-show-on').split(',');
        section.style.display = showOn.includes(type) ? '' : 'none';
    });
    
    // Handle vehicle sections specifically
    const vehicleContainer = document.querySelector('.vehicle-section-container');
    const documentTable = document.getElementById('document-table');
    
    if (type === 'vehicle-invoice' || type === 'vehicle-quotation') {
        // Show vehicle sections, hide normal line items
        if (vehicleContainer) vehicleContainer.style.display = '';
        if (documentTable) documentTable.style.display = 'none';
    } else {
        // Hide vehicle sections, show normal line items (unless credit note)
        if (vehicleContainer) vehicleContainer.style.display = 'none';
        if (documentTable && type !== 'credit-note' && type !== 'refund') {
            documentTable.style.display = '';
        }
    }
    
    // Handle credit note specific logic
    if (type === 'credit-note') {
        // Show credit note table, hide regular document table
        if (documentTable) documentTable.style.display = 'none';
        const creditNoteTable = document.getElementById('credit-note-table');
        if (creditNoteTable) creditNoteTable.style.display = '';
        
        // Hide refund table
        const refundTable = document.getElementById('refund-table');
        if (refundTable) refundTable.style.display = 'none';
        
        // Show related document number field
        const relatedDocDisplay = document.getElementById('related-document-number-display');
        const relatedDocInfo = document.getElementById('related-document-info');
        if (relatedDocDisplay) relatedDocDisplay.style.display = 'block';
        if (relatedDocInfo) relatedDocInfo.style.display = 'block';
        
        // Set up credit note row listeners
        setupInitialCreditNoteListeners();
        
        // Load original invoice products if we have a related document
        const relatedDocId = document.getElementById('related-document-id').value;
        if (relatedDocId) {
            loadOriginalInvoiceProducts(relatedDocId);
        }
    } else if (type === 'refund') {
        // Show refund table, hide regular document table and credit note table
        if (documentTable) documentTable.style.display = 'none';
        const creditNoteTable = document.getElementById('credit-note-table');
        if (creditNoteTable) creditNoteTable.style.display = 'none';
        
        const refundTable = document.getElementById('refund-table');
        if (refundTable) refundTable.style.display = '';
        
        // Show related document number field
        const relatedDocDisplay = document.getElementById('related-document-number-display');
        const relatedDocInfo = document.getElementById('related-document-info');
        if (relatedDocDisplay) relatedDocDisplay.style.display = 'block';
        if (relatedDocInfo) relatedDocInfo.style.display = 'block';
        
        // Set up refund row listeners
        setupInitialRefundListeners();
        
        // Load original invoice products if we have a related document
        const relatedDocId = document.getElementById('related-document-id').value;
        if (relatedDocId) {
            loadOriginalInvoiceProducts(relatedDocId);
        }
    } else {
        // Hide credit note table, refund table and related document fields
        const creditNoteTable = document.getElementById('credit-note-table');
        if (creditNoteTable) creditNoteTable.style.display = 'none';
        
        const refundTable = document.getElementById('refund-table');
        if (refundTable) refundTable.style.display = 'none';
        
        const relatedDocDisplay = document.getElementById('related-document-number-display');
        const relatedDocInfo = document.getElementById('related-document-info');
        if (relatedDocDisplay) relatedDocDisplay.style.display = 'none';
        if (relatedDocInfo) relatedDocInfo.style.display = 'none';
    }
    
    // Update live preview after section visibility changes
    updateLivePreview();
}

// --- Open/Close Modal ---
function openDocumentModal(mode = 'create', documentId = null) {
    const modal = document.getElementById('document-modal');
    if (!modal) return;

    // Set modal mode
    modal.setAttribute('data-mode', mode);
    
    // Show modal
    modal.style.display = 'flex';
    
    // Initialize components
    initializeLucideIcons();
    initializeLivePreview();
    initializeClientPanel();
    
    // Load issuer details from database
    loadIssuerDetails();
    
    // Reset form if creating new document
    if (mode === 'create') {
        resetDocumentForm();
    }
    
    // Load document data if editing
    if (mode === 'edit' && documentId) {
        fetchAndSetDocument(documentId);
    }
    
    // Update live preview
    updateLivePreview();
}

// --- Preview Next Document Number Logic ---
async function previewNextDocumentNumber() {
    const docType = document.getElementById('document-type')?.value || 'invoice';
    let endpoint = '';
    switch (docType) {
        case 'quotation':
            endpoint = '../api/document-api.php?action=preview_quotation_number';
            break;
        case 'vehicle-quotation':
            endpoint = '../api/document-api.php?action=preview_vehicle_quotation_number';
            break;
        case 'invoice':
        case 'standard-invoice':
            endpoint = '../api/document-api.php?action=preview_invoice_number';
            break;
        case 'vehicle-invoice':
            endpoint = '../api/document-api.php?action=preview_vehicle_invoice_number';
            break;
        case 'recurring-invoice':
            endpoint = '../api/document-api.php?action=preview_invoice_number';
            break;
        case 'credit-note':
            endpoint = '../api/document-api.php?action=preview_credit_note_number';
            break;
        case 'refund':
            endpoint = '../api/document-api.php?action=preview_refund_number';
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
    
    // Show/hide related document info for credit notes and refunds
    updateRelatedDocumentInfo();
}

function updateRelatedDocumentInfo() {
    const docType = document.getElementById('document-type')?.value;
    const relatedDocInfo = document.getElementById('related-document-info');
    const relatedDocNumber = document.getElementById('related-document-number');
    const relatedDocId = document.getElementById('related-document-id')?.value;
    
    if (relatedDocInfo && relatedDocNumber) {
        if ((docType === 'credit-note' || docType === 'refund') && relatedDocId) {
            // Show the info panel
            relatedDocInfo.style.display = 'block';
            
            // Try to get the document number from the related document
            fetch(`../api/document-api.php?action=get_document&document_id=${relatedDocId}`, {
                credentials: 'include'
            })
            .then(res => res.json())
            .then(data => {
                if (data.success && data.data) {
                    relatedDocNumber.textContent = data.data.document_number || `Document #${relatedDocId}`;
                } else {
                    relatedDocNumber.textContent = `Document #${relatedDocId}`;
                }
            })
            .catch(err => {
                relatedDocNumber.textContent = `Document #${relatedDocId}`;
            });
        } else {
            // Hide the info panel
            relatedDocInfo.style.display = 'none';
        }
    }
}

// --- Wrapper for global usage ---
function openDocumentModalWithPreview(mode = 'create') {
    openDocumentModal(mode);
    previewNextDocumentNumber();
    
    // Set up document type change listener
    const docType = document.getElementById('document-type');
    if (docType) {
        // Remove existing listener to prevent duplicates
        docType.removeEventListener('change', handleDocumentTypeChange);
        docType.addEventListener('change', handleDocumentTypeChange);
    }
}

// Handle document type changes
function handleDocumentTypeChange() {
    previewNextDocumentNumber();
    updateLivePreview();
    updateSectionVisibility();
}

window.openDocumentModal = openDocumentModal;
function closeDocumentModal() {
    // Clear related document ID when closing modal
    const relatedDocInput = document.getElementById('related-document-id');
    if (relatedDocInput) {
        relatedDocInput.value = '';
    }
    
    // Hide related document info
    const relatedDocInfo = document.getElementById('related-document-info');
    if (relatedDocInfo) {
        relatedDocInfo.style.display = 'none';
    }
    
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
            <div class="line-discount-input">
                <input type="text" class="line-discount" placeholder="10% or R50">
            </div>
        </td>
        <td class="total-cell">
            <span class="total">R0.00</span>
            <button type="button" class="remove-row-btn" title="Remove Line">&#10006;</button>
        </td>
        <td style="display: none;"><input type="hidden" class="product-id"></td>
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
        <td style="display: none;"><input type="hidden" class="product-id"></td>
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
        // Check if this is the first line item
        const tableBody = row.parentNode;
        const allRows = tableBody.querySelectorAll('.document-item-row, .vehicle-part-row, .credit-note-item-row');
        
        if (allRows.length <= 1) {
            // Don't remove the last remaining row
            showResponseModal('Cannot remove the last line item. At least one item must remain.', 'warning');
            return;
        }
        
        // Check if this is the first row (index 0)
        const rowIndex = Array.from(allRows).indexOf(row);
        if (rowIndex === 0) {
            showResponseModal('Cannot remove the first line item.', 'warning');
            return;
        }
        
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
    updateLivePreview();
}
function setupRowListeners() {
    const rows = document.querySelectorAll('.document-item-row');
    rows.forEach(row => {
        // Remove existing listeners to prevent duplicates
        const inputs = row.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.removeEventListener('input', updateTotals);
            input.removeEventListener('change', updateTotals);
            input.removeEventListener('input', updateLivePreview);
            input.removeEventListener('change', updateLivePreview);
        });
        
        // Add new listeners
        inputs.forEach(input => {
            input.addEventListener('input', updateTotals);
            input.addEventListener('change', updateTotals);
            input.addEventListener('input', updateLivePreview);
            input.addEventListener('change', updateLivePreview);
        });
        
        // Setup remove button
        const removeBtn = row.querySelector('.remove-row-btn');
        if (removeBtn) {
            removeBtn.removeEventListener('click', handleRemoveRowBtnClick);
            removeBtn.addEventListener('click', handleRemoveRowBtnClick);
        }
        
        // Setup search functionality
        const itemCodeInput = row.querySelector('.item-code');
        const descriptionInput = row.querySelector('.description');
        
        if (itemCodeInput) {
            itemCodeInput.removeEventListener('input', () => searchItem(itemCodeInput));
            itemCodeInput.addEventListener('input', () => searchItem(itemCodeInput));
        }
        
        if (descriptionInput) {
            descriptionInput.removeEventListener('input', () => searchItem(descriptionInput));
            descriptionInput.addEventListener('input', () => searchItem(descriptionInput));
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
            results.forEach((result, idx) => {
                const div = document.createElement('div');
                div.classList.add('search-result-product');
                // For item code column, show SKU if available, otherwise show product name
                if (isDescription) {
                    div.textContent = result.product_description || result.product_name;
                } else {
                    // Item code column - prioritize SKU, fallback to product name
                    if (result.sku && result.sku.trim()) {
                        div.textContent = `${result.sku} - ${result.product_name}`;
                    } else {
                        div.textContent = result.product_name;
                    }
                }
                div.tabIndex = -1; // allow focus for accessibility
                div.dataset.idx = idx;
                div.addEventListener('click', () => {
                    autofillRow(row, {
                        item_code: result.sku || result.product_name || '',
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
                resultsContainer.appendChild(div);
            });

            // Highlight the first result by default
            let currentIdx = 0;
            const items = resultsContainer.querySelectorAll('.search-result-product');
            if (items.length > 0) items[0].classList.add('highlight');

            // Keyboard navigation handler
            const keydownHandler = function(event) {
                if (!resultsContainer || resultsContainer.style.display !== 'block') return;
                const items = resultsContainer.querySelectorAll('.search-result-product');
                if (!items.length) return;

                // Find the currently highlighted index
                let highlightIdx = Array.from(items).findIndex(div => div.classList.contains('highlight'));
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
                            item_code: result.sku || result.product_name || '',
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
                        item_code: result.sku || result.product_name || '',
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
    const itemCode = row.querySelector('.item-code');
    const description = row.querySelector('.description');
    const unitPrice = row.querySelector('.unit-price');
    const productId = row.querySelector('.product-id');
    
    if (itemCode) itemCode.value = result.item_code || '';
    if (description) description.value = result.product_description || '';
    if (unitPrice) unitPrice.value = result.unit_price || '';
    if (productId) productId.value = result.product_id || '';
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

    // Save and Preview button event listeners
    const saveBtn = document.getElementById('save-document');
    if (saveBtn) {
        saveBtn.addEventListener('click', saveDocument);
    }
    
    // New button event listeners
    const clearBtn = document.getElementById('clear-document-btn');
    if (clearBtn) {
        clearBtn.addEventListener('click', clearDocument);
    }
    
    const saveDocumentBtn = document.getElementById('save-document-btn');
    if (saveDocumentBtn) {
        saveDocumentBtn.addEventListener('click', saveDocument);
    }
    
    const createDocumentBtn = document.getElementById('create-document-btn');
    if (createDocumentBtn) {
        createDocumentBtn.addEventListener('click', createDocument);
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
    
    // Add credit note item button
    const addCreditNoteItemBtn = document.getElementById('add-credit-note-item-btn');
    if (addCreditNoteItemBtn && !addCreditNoteItemBtn.hasListener) {
        addCreditNoteItemBtn.addEventListener('click', addCreditNoteItem);
        addCreditNoteItemBtn.hasListener = true;
    }
    const addDiscountBtn = document.getElementById('add-discount-btn');
    if (addDiscountBtn && !addDiscountBtn.hasListener) {
        addDiscountBtn.addEventListener('click', addDocumentDiscount);
        addDiscountBtn.hasListener = true;
    }
    
    // Add vehicle part button
    const addVehiclePartBtn = document.getElementById('add-vehicle-part-btn');
    if (addVehiclePartBtn && !addVehiclePartBtn.hasListener) {
        addVehiclePartBtn.addEventListener('click', addVehiclePart);
        addVehiclePartBtn.hasListener = true;
    }
    
    const previewPdfBtn = document.getElementById('preview-pdf-btn');
    if (previewPdfBtn) {
        previewPdfBtn.addEventListener('click', previewDocument);
    }
    
    // Add click outside handler to close dropdowns
    document.addEventListener('click', function(e) {
        const clientDropdown = document.getElementById('search-results-client');
        const salespersonDropdown = document.getElementById('search-results-salesperson');
        
        // Close client dropdown if clicking outside
        if (clientDropdown && !e.target.closest('.search-client-container')) {
            clientDropdown.style.display = 'none';
            clientDropdown.classList.remove('active');
        }
        
        // Close salesperson dropdown if clicking outside
        if (salespersonDropdown && !e.target.closest('.search-salesperson-container')) {
            salespersonDropdown.style.display = 'none';
            salespersonDropdown.classList.remove('active');
        }
    });
});

// --- Modal Initialization ---
document.addEventListener('DOMContentLoaded', function() {
    // Set up event listeners for modal elements
    const modal = document.getElementById('document-modal');
    if (!modal) return;
    
    // Document type change handler
    const typeSelect = document.getElementById('document-type');
    if (typeSelect) {
        typeSelect.addEventListener('change', handleDocumentTypeChange);
    }
    
    // Document date and other fields
    const documentFields = ['current-date', 'document-number', 'salesperson'];
    documentFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', updateLivePreview);
            field.addEventListener('change', updateLivePreview);
        }
    });
    
    // Vehicle input listeners
    const vehiclePriceInput = document.getElementById('vehicle-price');
    const vehicleTaxSelect = document.getElementById('vehicle-tax');
    
    if (vehiclePriceInput) {
        vehiclePriceInput.addEventListener('input', () => {
            formatPrice(vehiclePriceInput);
            updateVehicleTotals();
            updateLivePreview();
        });
    }
    
    if (vehicleTaxSelect) {
        vehicleTaxSelect.addEventListener('change', () => {
            updateVehicleTotals();
            updateLivePreview();
        });
    }
    
    // Add event listeners for other vehicle fields
    const vehicleFields = ['vehicle-model', 'vehicle-vin'];
    vehicleFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', updateLivePreview);
            field.addEventListener('change', updateLivePreview);
        }
    });
    
    // Client search functionality - using document-form.js functions
    const clientNameInput = document.getElementById('client-name');
    if (clientNameInput) {
        clientNameInput.addEventListener('input', function() {
            window.searchClient(this);
            updateLivePreview();
        });
    }
    
    // Add event listeners for other client fields
    const clientFields = ['client-email', 'client-phone', 'client-vat-number', 'client-reg-number', 'client-address-1', 'client-address-2'];
    clientFields.forEach(fieldId => {
        const field = document.getElementById(fieldId);
        if (field) {
            field.addEventListener('input', updateLivePreview);
            field.addEventListener('change', updateLivePreview);
        }
    });
    
    // Salesperson search functionality - using document-form.js functions
    const salespersonInput = document.getElementById('salesperson');
    if (salespersonInput) {
        salespersonInput.addEventListener('input', function() {
            window.searchSalesperson(this);
            updateLivePreview();
        });
    }
    
    // Add item/discount buttons
    const addItemBtn = document.getElementById('add-item-btn');
    if (addItemBtn && !addItemBtn.hasListener) {
        addItemBtn.addEventListener('click', addDocumentItem);
        addItemBtn.hasListener = true;
    }
    
    // Add credit note item button
    const addCreditNoteItemBtn = document.getElementById('add-credit-note-item-btn');
    if (addCreditNoteItemBtn && !addCreditNoteItemBtn.hasListener) {
        addCreditNoteItemBtn.addEventListener('click', addCreditNoteItem);
        addCreditNoteItemBtn.hasListener = true;
    }
    
    const addDiscountBtn = document.getElementById('add-discount-btn');
    if (addDiscountBtn && !addDiscountBtn.hasListener) {
        addDiscountBtn.addEventListener('click', addDocumentDiscount);
        addDiscountBtn.hasListener = true;
    }
    
    const previewPdfBtn = document.getElementById('preview-pdf-btn');
    if (previewPdfBtn) {
        previewPdfBtn.addEventListener('click', previewDocument);
    }
    
    // Set up initial credit note row listeners
    setupInitialCreditNoteListeners();
});

// Function to set up initial credit note listeners
function setupInitialCreditNoteListeners() {
    console.log('[setupInitialCreditNoteListeners] Setting up initial credit note listeners');
    const creditNoteRows = document.querySelectorAll('.credit-note-item-row');
    console.log('[setupInitialCreditNoteListeners] Found credit note rows:', creditNoteRows.length);
    
    creditNoteRows.forEach((row, index) => {
        console.log(`[setupInitialCreditNoteListeners] Setting up row ${index}:`, row);
        setupCreditNoteRowListeners(row);
    });
}

// --- Document Workflow Functions ---

/**
 * Preview Document - Generate PDF preview with draft number
 */
async function previewDocument() {
    try {
        window.showLoadingModal('Generating PDF preview...');
        const formData = window.getDocumentFormData();
        
                            // For preview, use a simple draft number
                    if (!formData.document_number || formData.document_number.includes('(Preview)')) {
                        formData.document_number = 'DRAFT-' + Date.now() + ' (Preview)';
                    }
        
        formData.preview = true;
        
        // For preview, we need to handle the direct PDF output differently
        try {
            const response = await fetch('../api/generate-document-pdf.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(formData)
            });
            
            if (response.ok) {
                // Get the PDF blob
                const blob = await response.blob();
                // Create a URL for the blob
                const url = window.URL.createObjectURL(blob);
                // Open in new tab
                window.open(url, '_blank');
                // Clean up the URL after a delay
                setTimeout(() => window.URL.revokeObjectURL(url), 1000);
            } else {
                const errorData = await response.json();
                showResponseModal(errorData.message || 'Failed to generate PDF preview', 'error');
            }
        } catch (err) {
            showResponseModal('Error generating PDF preview: ' + (err.message || err), 'error');
        }
        
        window.hideLoadingModal();
    } catch (err) {
        window.hideLoadingModal();
        showResponseModal('Error generating PDF preview: ' + (err.message || err), 'error');
    }
}

/**
 * Save Document - Save as draft to database (no PDF generation)
 */
async function saveDocument() {
    try {
        window.showLoadingModal('Saving draft...');
        const formData = window.getDocumentFormData();
        formData.document_status = 'Draft';
        
        // Pass mode parameter for draft
        const result = await saveDocumentApi(formData, { mode: 'draft' });
        window.hideLoadingModal();
        
        console.log('[saveDocument] API result:', result);
        
        if (result.success === false) {
            window.handleApiResponse(result);
        } else if (result.success === true) {
            let msg = 'Draft saved successfully';
            if (result.data && result.data.document_number) {
                msg += ` (Number: ${result.data.document_number})`;
            }
            showResponseModal(msg, 'success');
            
            // Refresh the document list to show updated status
            if (typeof window.fetchAndRenderDocuments === 'function') {
                // Get the current active section
                const activeSection = document.querySelector('.document-section.active');
                if (activeSection) {
                    const sectionId = activeSection.id;
                    const status = document.querySelector(`#${sectionId} .status-filter select`)?.value || 'all';
                    window.fetchAndRenderDocuments(sectionId, status);
                }
            }
        }
        
    } catch (err) {
        window.hideLoadingModal();
        console.error('[saveDocument] Error:', err);
        showResponseModal('Error saving draft: ' + (err.message || err), 'error');
    }
}

/**
 * Create Document - Finalize document, get proper number, save to DB, and generate PDF
 */
async function createDocument() {
    try {
        window.showLoadingModal('Finalizing document...');
        const formData = window.getDocumentFormData();
        
        // Set document status to Unpaid (which means finalized)
        formData.document_status = 'Unpaid';
        
        // Get proper document number based on type
        const docType = document.getElementById('document-type')?.value || 'quotation';
        let documentNumber = '';
        
        try {
            console.log('[createDocument] Getting document number for type:', docType);
            let endpoint = '';
            switch (docType) {
                case 'quotation':
                    endpoint = '../api/document-api.php?action=get_next_quotation_number';
                    break;
                case 'vehicle-quotation':
                    endpoint = '../api/document-api.php?action=get_next_vehicle_quotation_number';
                    break;
                case 'invoice':
                case 'standard-invoice':
                    endpoint = '../api/document-api.php?action=get_next_invoice_number';
                    break;
                case 'vehicle-invoice':
                    endpoint = '../api/document-api.php?action=get_next_vehicle_invoice_number';
                    break;
                case 'recurring-invoice':
                    endpoint = '../api/document-api.php?action=get_next_invoice_number';
                    break;
                case 'credit-note':
                    endpoint = '../api/document-api.php?action=get_next_credit_note_number';
                    break;
                case 'refund':
                    endpoint = '../api/document-api.php?action=get_next_refund_number';
                    break;
                case 'pro-forma':
                    endpoint = '../api/document-api.php?action=get_next_proforma_number';
                    break;
                default:
                    endpoint = '../api/document-api.php?action=get_next_invoice_number';
            }
            
            const response = await fetch(endpoint, {
                credentials: 'include'
            });
            
            console.log('[createDocument] Response status:', response.status);
            console.log('[createDocument] Response ok:', response.ok);
            
            if (!response.ok) {
                throw new Error(`HTTP ${response.status}: ${response.statusText}`);
            }
            
            const responseText = await response.text();
            console.log('[createDocument] Response text:', responseText);
            
            if (!responseText) {
                throw new Error('Empty response from server');
            }
            
            const data = JSON.parse(responseText);
            console.log('[createDocument] Parsed data:', data);
            
            if (data.success && data.data && data.data.number) {
                documentNumber = data.data.number;
            } else {
                throw new Error('Invalid response format or missing document number');
            }
        } catch (err) {
            console.error('[createDocument] Error getting document number:', err);
            // Use a fallback document number
            documentNumber = 'DOC-' + Date.now();
            console.log('[createDocument] Using fallback document number:', documentNumber);
        }
        
        formData.document_number = documentNumber;
        
        // Ensure document_number is included in the form data for PDF generation
        const formDataForSave = { ...formData };
        const formDataForPDF = { ...formData };
        
        // Pass mode parameter for finalization
        const result = await saveDocumentApi(formDataForSave, { mode: 'finalize' });
        
        if (result.success === false) {
            window.hideLoadingModal();
            window.handleApiResponse(result);
            return;
        }
        
        // Generate final PDF
        window.showLoadingModal('Generating final PDF...');
        const pdfResult = await generateFinalPDF(formDataForPDF);
        window.hideLoadingModal();
        
        if (pdfResult.success && pdfResult.url) {
            let msg = 'Document finalized successfully';
            if (result.data && result.data.document_number) {
                msg += ` (Number: ${result.data.document_number})`;
            }
            msg += '\n\nPDF has been generated successfully.';
            showResponseModal(msg, 'success');
            
            // Refresh the document list to show updated status
            if (typeof window.fetchAndRenderDocuments === 'function') {
                // Get the current active section
                const activeSection = document.querySelector('.document-section.active');
                if (activeSection) {
                    const sectionId = activeSection.id;
                    const status = document.querySelector(`#${sectionId} .status-filter select`)?.value || 'all';
                    window.fetchAndRenderDocuments(sectionId, status);
                }
            }
            
            // Open PDF in new tab
            setTimeout(() => {
                if (pdfResult.url.startsWith('blob:')) {
                    // Blob URL (preview)
                    window.open(pdfResult.url, '_blank');
                } else {
                    // File URL (final PDF)
                    window.open(pdfResult.url, '_blank');
                }
            }, 1000);
        } else {
            showResponseModal('Document saved but PDF generation failed: ' + (pdfResult.message || 'Unknown error'), 'warning');
        }
        
    } catch (err) {
        window.hideLoadingModal();
        console.error('[createDocument] Error:', err);
        showResponseModal('Error finalizing document: ' + (err.message || err), 'error');
    }
}

/**
 * Clear Document - Reset form
 */
function clearDocument() {
    if (confirm('Are you sure you want to clear all data? This action cannot be undone.')) {
        // Reset form fields
        document.getElementById('client-name').value = '';
        document.getElementById('client-id').value = '';
        document.getElementById('client-email').value = '';
        document.getElementById('client-phone').value = '';
        document.getElementById('client-vat-number').value = '';
        document.getElementById('client-reg-number').value = '';
        document.getElementById('client-address-1').value = '';
        document.getElementById('client-address-2').value = '';
        document.getElementById('related-document-id').value = '';
        document.getElementById('document-id').value = '';
        document.getElementById('document-number').value = '';
        document.getElementById('purchase-order-number').value = '';
        document.getElementById('salesperson').value = '';
        document.getElementById('public-note').value = '';
        document.getElementById('private-note').value = '';
        document.getElementById('foot-note').value = '';
        
        // Clear items table
        const tableBody = document.getElementById('document-rows');
        if (tableBody) {
            tableBody.innerHTML = '';
            addDocumentItem(); // Add one empty row
        }
        
        // Reset totals
        document.getElementById('subtotal').textContent = '0.00';
        document.getElementById('tax-total').textContent = '0.00';
        document.getElementById('final-total').textContent = '0.00';
        
        // Hide related document info
        const relatedDocInfo = document.getElementById('related-document-info');
        if (relatedDocInfo) {
            relatedDocInfo.style.display = 'none';
        }
        
        showResponseModal('Document form cleared', 'success');
    }
}

function resetDocumentForm() {
    // Reset form fields
    document.getElementById('client-name').value = '';
    document.getElementById('client-id').value = '';
    document.getElementById('client-email').value = '';
    document.getElementById('client-phone').value = '';
    document.getElementById('client-vat-number').value = '';
    document.getElementById('client-reg-number').value = '';
    document.getElementById('client-address-1').value = '';
    document.getElementById('client-address-2').value = '';
    document.getElementById('related-document-id').value = '';
    document.getElementById('document-id').value = '';
    document.getElementById('document-number').value = '';
    document.getElementById('purchase-order-number').value = '';
    document.getElementById('salesperson').value = '';
    document.getElementById('public-note').value = '';
    document.getElementById('private-note').value = '';
    document.getElementById('foot-note').value = '';
    
    // Set default document type
    const docTypeSelect = document.getElementById('document-type');
    if (docTypeSelect) {
        docTypeSelect.value = 'quotation';
    }
    
    // Set default date to today
    const dateInput = document.getElementById('document-date');
    if (dateInput) {
        const today = new Date().toISOString().split('T')[0];
        dateInput.value = today;
    }
    
    // Clear items table
    const tableBody = document.getElementById('document-rows');
    if (tableBody) {
        tableBody.innerHTML = '';
        addDocumentItem(); // Add one empty row
    }
    
    // Clear credit note table
    const creditNoteTableBody = document.getElementById('credit-note-rows');
    if (creditNoteTableBody) {
        creditNoteTableBody.innerHTML = '';
        addCreditNoteItem(); // Add one empty row
    }
    
    // Clear refund table
    const refundTableBody = document.getElementById('refund-rows');
    if (refundTableBody) {
        refundTableBody.innerHTML = '';
        addRefundItem(); // Add one empty row
    }
    
    // Clear vehicle parts table
    const vehiclePartsTableBody = document.getElementById('vehicle-parts-rows');
    if (vehiclePartsTableBody) {
        vehiclePartsTableBody.innerHTML = '';
        addVehiclePart(); // Add one empty row
    }
    
    // Reset vehicle fields
    const vehicleModel = document.getElementById('vehicle-model');
    const vehicleVin = document.getElementById('vehicle-vin');
    const vehiclePrice = document.getElementById('vehicle-price');
    const vehicleTax = document.getElementById('vehicle-tax');
    const vehicleTotal = document.getElementById('vehicle-total');
    
    if (vehicleModel) vehicleModel.value = '';
    if (vehicleVin) vehicleVin.value = '';
    if (vehiclePrice) vehiclePrice.value = 'R0.00';
    if (vehicleTax) vehicleTax.value = '0';
    if (vehicleTotal) vehicleTotal.textContent = 'R0.00';
    
    // Reset totals
    document.getElementById('subtotal').textContent = 'R0.00';
    document.getElementById('tax-total').textContent = 'R0.00';
    document.getElementById('final-total').textContent = 'R0.00';
    
    // Hide related document info
    const relatedDocInfo = document.getElementById('related-document-info');
    if (relatedDocInfo) {
        relatedDocInfo.style.display = 'none';
    }
    
    // Update section visibility
    updateSectionVisibility();
    
    // Preview next document number
    previewNextDocumentNumber();
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

// Vehicle Parts Functions
function addVehiclePart() {
    console.log('[addVehiclePart] Adding new vehicle part');
    const tbody = document.getElementById('vehicle-parts-rows');
    if (!tbody) {
        console.error('[addVehiclePart] Could not find vehicle-parts-rows tbody');
        return;
    }
    
    const newRow = document.createElement('tr');
    newRow.className = 'vehicle-part-row';
    newRow.innerHTML = `
        <td><input type="number" value="1" class="quantity"></td>
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
        </td>
        <td>
            <button type="button" class="remove-row-btn" title="Remove Line">&#10006;</button>
        </td>
        <td style="display: none;"><input type="hidden" class="product-id"></td>
    `;
    tbody.appendChild(newRow);
    setupVehiclePartRowListeners(newRow);
}

function setupVehiclePartRowListeners(row) {
    const quantityInput = row.querySelector('.quantity');
    const priceInput = row.querySelector('.unit-price');
    const taxSelect = row.querySelector('.tax');
    const discountInput = row.querySelector('.line-discount');
    const removeBtn = row.querySelector('.remove-row-btn');
    const itemCodeInput = row.querySelector('.item-code');
    const descriptionInput = row.querySelector('.description');
    
    // Remove existing listeners
    [quantityInput, priceInput, taxSelect, discountInput].forEach(input => {
        if (input) {
            input.removeEventListener('input', () => calculateVehiclePartRowTotal(row));
            input.removeEventListener('change', () => calculateVehiclePartRowTotal(row));
            input.removeEventListener('input', updateLivePreview);
            input.removeEventListener('change', updateLivePreview);
        }
    });
    
    // Add new listeners
    if (quantityInput) {
        quantityInput.addEventListener('input', () => calculateVehiclePartRowTotal(row));
        quantityInput.addEventListener('change', () => calculateVehiclePartRowTotal(row));
        quantityInput.addEventListener('input', updateLivePreview);
        quantityInput.addEventListener('change', updateLivePreview);
    }
    
    if (priceInput) {
        priceInput.addEventListener('input', () => {
            removeR(priceInput);
            calculateVehiclePartRowTotal(row);
        });
        priceInput.addEventListener('change', () => {
            formatPrice(priceInput);
            calculateVehiclePartRowTotal(row);
        });
        priceInput.addEventListener('input', updateLivePreview);
        priceInput.addEventListener('change', updateLivePreview);
        priceInput.addEventListener('focus', () => removeR(priceInput));
        priceInput.addEventListener('blur', () => formatPrice(priceInput));
    }
    
    if (taxSelect) {
        taxSelect.addEventListener('change', () => calculateVehiclePartRowTotal(row));
        taxSelect.addEventListener('change', updateLivePreview);
    }
    
    if (discountInput) {
        discountInput.addEventListener('input', () => calculateVehiclePartRowTotal(row));
        discountInput.addEventListener('change', () => calculateVehiclePartRowTotal(row));
        discountInput.addEventListener('input', updateLivePreview);
        discountInput.addEventListener('change', updateLivePreview);
    }
    
    if (removeBtn) {
        removeBtn.addEventListener('click', () => removeVehiclePart(row));
    }
    
    // Setup search functionality
    if (itemCodeInput) {
        itemCodeInput.addEventListener('input', () => searchItem(itemCodeInput));
    }
    
    if (descriptionInput) {
        descriptionInput.addEventListener('input', () => searchItem(descriptionInput));
    }
}

function calculateVehiclePartRowTotal(row) {
    const quantity = parseFloat(row.querySelector('.quantity').value) || 0;
    const unitPrice = parseFloat(removeR(row.querySelector('.unit-price').value)) || 0;
    const taxRate = parseFloat(row.querySelector('.tax').value) || 0;
    const discountText = row.querySelector('.line-discount').value || '';
    
    let discount = 0;
    if (discountText.includes('%')) {
        const discountPercent = parseFloat(discountText) || 0;
        discount = (quantity * unitPrice * discountPercent) / 100;
    } else {
        discount = parseFloat(removeR(discountText)) || 0;
    }
    
    const subtotal = (quantity * unitPrice) - discount;
    const tax = (subtotal * taxRate) / 100;
    const total = subtotal + tax;
    
    row.querySelector('.total').textContent = total.toFixed(2);
    updateVehicleTotals();
}

function removeVehiclePart(row) {
    // Check if this is the first vehicle part
    const tableBody = row.parentNode;
    const allRows = tableBody.querySelectorAll('.vehicle-part-row');
    
    if (allRows.length <= 1) {
        // Don't remove the last remaining row
        showResponseModal('Cannot remove the last vehicle part. At least one part must remain.', 'warning');
        return;
    }
    
    // Check if this is the first row (index 0)
    const rowIndex = Array.from(allRows).indexOf(row);
    if (rowIndex === 0) {
        showResponseModal('Cannot remove the first vehicle part.', 'warning');
        return;
    }
    
    row.remove();
    updateVehicleTotals();
}

function updateVehicleTotals() {
    const vehiclePrice = parseFloat(document.getElementById('vehicle-price')?.value.replace(/[^\d.-]/g, '') || 0);
    const vehicleTax = parseFloat(document.getElementById('vehicle-tax')?.value || 0);
    const vehicleTaxAmount = (vehiclePrice * vehicleTax) / 100;
    const vehicleTotal = vehiclePrice + vehicleTaxAmount;
    
    // Update vehicle total display
    const vehicleTotalElement = document.getElementById('vehicle-total');
    if (vehicleTotalElement) {
        vehicleTotalElement.textContent = `R${vehicleTotal.toFixed(2)}`;
    }
    
    // Calculate parts total
    let partsTotal = 0;
    const vehiclePartsRows = document.querySelectorAll('#vehicle-parts-rows tr');
    vehiclePartsRows.forEach(row => {
        const totalElement = row.querySelector('.total');
        if (totalElement) {
            const total = parseFloat(totalElement.textContent.replace(/[^\d.-]/g, '') || 0);
            partsTotal += total;
        }
    });
    
    // Update parts total display
    const partsTotalElement = document.getElementById('parts-total');
    if (partsTotalElement) {
        partsTotalElement.textContent = `R${partsTotal.toFixed(2)}`;
    }
    
    // Calculate grand total
    const grandTotal = vehicleTotal + partsTotal;
    const grandTotalElement = document.getElementById('vehicle-grand-total');
    if (grandTotalElement) {
        grandTotalElement.textContent = `R${grandTotal.toFixed(2)}`;
    }
    
    // Update live preview
    updateLivePreview();
}

// Credit Note Functions
function addCreditNoteItem() {
    console.log('[addCreditNoteItem] Adding new credit note item');
    const tbody = document.getElementById('credit-note-rows');
    if (!tbody) {
        console.error('[addCreditNoteItem] Could not find credit-note-rows tbody');
        return;
    }
    
    const newRow = document.createElement('tr');
    newRow.className = 'credit-note-item-row';
    newRow.innerHTML = `
        <td>
            <select class="credit-type">
                <option value="reason">Credit Reason</option>
                <option value="product">Original Product</option>
            </select>
        </td>
        <td>
            <div class="search-container" style="position: relative;">
                <input type="text" placeholder="Search credit reason or select product" class="credit-reason" autocomplete="off">
                <div class="credit-search-dropdown"></div>
            </div>
        </td>
        <td><input type="text" value="R0.00" class="credit-amount"></td>
        <td>
            <button type="button" class="remove-credit-row-btn" title="Remove Line">&#10006;</button>
        </td>
    `;
    tbody.appendChild(newRow);
    console.log('[addCreditNoteItem] New row added, setting up listeners');
    setupCreditNoteRowListeners(newRow);
}

function removeCreditNoteItem(event) {
    const row = event.target.closest('tr');
    if (row && row.parentNode.children.length > 1) {
        // Check if this is the first credit note item
        const tableBody = row.parentNode;
        const allRows = tableBody.querySelectorAll('.credit-note-item-row');
        
        if (allRows.length <= 1) {
            // Don't remove the last remaining row
            showResponseModal('Cannot remove the last credit note item. At least one item must remain.', 'warning');
            return;
        }
        
        // Check if this is the first row (index 0)
        const rowIndex = Array.from(allRows).indexOf(row);
        if (rowIndex === 0) {
            showResponseModal('Cannot remove the first credit note item.', 'warning');
            return;
        }
        
        row.remove();
        updateCreditNoteTotals();
    }
}

function setupCreditNoteRowListeners(row) {
    const creditTypeSelect = row.querySelector('.credit-type');
    const creditReasonInput = row.querySelector('.credit-reason');
    const creditAmountInput = row.querySelector('.credit-amount');
    const removeBtn = row.querySelector('.remove-credit-row-btn');
    
    // Remove existing listeners
    [creditTypeSelect, creditReasonInput, creditAmountInput].forEach(input => {
        if (input) {
            input.removeEventListener('change', updateCreditNoteTotals);
            input.removeEventListener('input', updateCreditNoteTotals);
            input.removeEventListener('change', updateLivePreview);
            input.removeEventListener('input', updateLivePreview);
            input.removeEventListener('input', handleCreditNoteSearch);
            input.removeEventListener('change', handleCreditNoteSearch);
        }
    });
    
    // Add new listeners
    if (creditTypeSelect) {
        creditTypeSelect.addEventListener('change', updateCreditNoteTotals);
        creditTypeSelect.addEventListener('change', updateLivePreview);
        creditTypeSelect.addEventListener('change', handleCreditTypeChange);
    }
    
    if (creditReasonInput) {
        creditReasonInput.addEventListener('input', updateCreditNoteTotals);
        creditReasonInput.addEventListener('change', updateCreditNoteTotals);
        creditReasonInput.addEventListener('input', updateLivePreview);
        creditReasonInput.addEventListener('change', updateLivePreview);
        creditReasonInput.addEventListener('input', handleCreditNoteSearch);
        creditReasonInput.addEventListener('change', handleCreditNoteSearch);
        
        // Add keyboard navigation for search dropdown
        creditReasonInput.addEventListener('keydown', handleCreditNoteSearchKeydown);
    }
    
    if (creditAmountInput) {
        creditAmountInput.addEventListener('input', updateCreditNoteTotals);
        creditAmountInput.addEventListener('change', updateCreditNoteTotals);
        creditAmountInput.addEventListener('input', updateLivePreview);
        creditAmountInput.addEventListener('change', updateLivePreview);
        creditAmountInput.addEventListener('input', validateCreditAmount);
    }
    
    if (removeBtn) {
        removeBtn.addEventListener('click', removeCreditNoteItem);
    }
}

// Handle credit type change (reason vs product)
function handleCreditTypeChange(event) {
    const row = event.target.closest('tr');
    const creditTypeSelect = event.target;
    const creditReasonInput = row.querySelector('.credit-reason');
    const creditAmountInput = row.querySelector('.credit-amount');
    
    // Clear previous values
    creditReasonInput.value = '';
    creditAmountInput.value = 'R0.00';
    
    // Update placeholder based on type
    if (creditTypeSelect.value === 'reason') {
        creditReasonInput.placeholder = 'Search credit reason...';
    } else if (creditTypeSelect.value === 'product') {
        creditReasonInput.placeholder = 'Search original product...';
    }
    
    // Clear dropdown
    const dropdown = creditReasonInput.nextElementSibling;
    if (dropdown) {
        dropdown.innerHTML = '';
    }
    
    updateCreditNoteTotals();
    updateLivePreview();
}

// Handle credit note search (reasons or products)
function handleCreditNoteSearch(event) {
    const input = event.target;
    const query = input.value.trim();
    const row = input.closest('tr');
    const creditTypeSelect = row.querySelector('.credit-type');
    
    // Clear dropdown if query is too short
    if (query.length < 2) {
        const dropdown = input.nextElementSibling;
        if (dropdown) {
            dropdown.innerHTML = '';
        }
        return;
    }
    
    // Search based on credit type
    if (creditTypeSelect.value === 'reason') {
        searchCreditReasons(input, query);
    } else if (creditTypeSelect.value === 'product') {
        searchOriginalProducts(input, query);
    }
}

// Handle keyboard navigation for credit note search
function handleCreditNoteSearchKeydown(event) {
    const input = event.target;
    const dropdown = input.nextElementSibling;
    const results = dropdown.querySelectorAll('.search-result');
    const currentIndex = Array.from(results).findIndex(result => result.classList.contains('selected'));
    
    switch (event.key) {
        case 'ArrowDown':
            event.preventDefault();
            if (currentIndex < results.length - 1) {
                if (currentIndex >= 0) results[currentIndex].classList.remove('selected');
                results[currentIndex + 1].classList.add('selected');
            } else if (results.length > 0) {
                if (currentIndex >= 0) results[currentIndex].classList.remove('selected');
                results[0].classList.add('selected');
            }
            break;
        case 'ArrowUp':
            event.preventDefault();
            if (currentIndex > 0) {
                results[currentIndex].classList.remove('selected');
                results[currentIndex - 1].classList.add('selected');
            } else if (results.length > 0) {
                results[currentIndex].classList.remove('selected');
                results[results.length - 1].classList.add('selected');
            }
            break;
        case 'Enter':
            event.preventDefault();
            const selectedResult = dropdown.querySelector('.search-result.selected');
            if (selectedResult) {
                selectedResult.click();
            }
            break;
        case 'Escape':
            dropdown.innerHTML = '';
            input.blur();
            break;
    }
}

// Validate credit amount against original invoice
function validateCreditAmount(event) {
    const input = event.target;
    const row = input.closest('tr');
    const creditTypeSelect = row.querySelector('.credit-type');
    const creditReasonInput = row.querySelector('.credit-reason');
    const amount = parseFloat(input.value.replace(/[^\d.-]/g, '') || 0);
    
    // If this is a product credit, validate against original line total
    if (creditTypeSelect.value === 'product') {
        const originalLineTotal = parseFloat(creditReasonInput.getAttribute('data-original-line-total') || 0);
        if (amount > originalLineTotal) {
            showResponseModal('warning', 'Warning', `Credit amount (R${amount.toFixed(2)}) exceeds original line total (R${originalLineTotal.toFixed(2)})`);
        }
    }
    
    // Format the amount
    formatPrice(input);
}

function searchCreditReasons(inputElement, query) {
    console.log('[searchCreditReasons] Searching for:', query);
    
    fetch(`../api/document-api.php?action=search_credit_reasons&query=${encodeURIComponent(query)}`, {
        credentials: 'include'
    })
    .then(response => {
        console.log('[searchCreditReasons] Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('[searchCreditReasons] Response data:', data);
        const dropdown = inputElement.nextElementSibling;
        dropdown.innerHTML = '';
        
        if (data.success && data.results && data.results.length > 0) {
            data.results.forEach((reason, index) => {
                const div = document.createElement('div');
                div.className = 'search-result';
                div.textContent = reason.reason;
                div.setAttribute('data-reason-id', reason.credit_reason_id);
                
                // Highlight first result
                if (index === 0) {
                    div.classList.add('selected');
                }
                
                div.addEventListener('click', () => {
                    inputElement.value = reason.reason;
                    inputElement.setAttribute('data-reason-id', reason.credit_reason_id);
                    dropdown.innerHTML = '';
                    
                    // Update totals and preview
                    updateCreditNoteTotals();
                    updateLivePreview();
                });
                
                div.addEventListener('mouseenter', () => {
                    dropdown.querySelectorAll('.search-result').forEach(r => r.classList.remove('selected'));
                    div.classList.add('selected');
                });
                
                dropdown.appendChild(div);
            });
        } else {
            // Show no results message
            const div = document.createElement('div');
            div.className = 'search-result no-results';
            div.textContent = 'No credit reasons found';
            dropdown.appendChild(div);
        }
    })
    .catch(error => {
        console.error('Error searching credit reasons:', error);
        const dropdown = inputElement.nextElementSibling;
        dropdown.innerHTML = '<div class="search-result error">Error searching credit reasons</div>';
    });
}

function searchOriginalProducts(inputElement, query) {
    console.log('[searchOriginalProducts] Searching for:', query);
    
    const relatedDocId = document.getElementById('related-document-id').value;
    if (!relatedDocId) {
        console.error('No related document ID found');
        const dropdown = inputElement.nextElementSibling;
        dropdown.innerHTML = '<div class="search-result error">Please select a related invoice first</div>';
        return;
    }
    
    console.log('[searchOriginalProducts] Related document ID:', relatedDocId);
    
    fetch(`../api/document-api.php?action=get_original_invoice_products&invoice_id=${encodeURIComponent(relatedDocId)}`, {
        credentials: 'include'
    })
    .then(response => {
        console.log('[searchOriginalProducts] Response status:', response.status);
        return response.json();
    })
    .then(data => {
        console.log('[searchOriginalProducts] Response data:', data);
        const dropdown = inputElement.nextElementSibling;
        dropdown.innerHTML = '';
        
        if (data.success && data.results && data.results.length > 0) {
            // Filter results based on query
            const filteredResults = data.results.filter(product => {
                const productName = (product.product_description || product.product_name || '').toLowerCase();
                const sku = (product.sku || '').toLowerCase();
                const searchQuery = query.toLowerCase();
                return productName.includes(searchQuery) || sku.includes(searchQuery);
            });
            
            if (filteredResults.length > 0) {
                filteredResults.forEach((product, index) => {
                    const div = document.createElement('div');
                    div.className = 'search-result';
                    div.innerHTML = `
                        <div class="product-name">${product.product_description || product.product_name}</div>
                        <div class="product-details">
                            <span class="sku">SKU: ${product.sku || 'No SKU'}</span>
                            <span class="price">R${parseFloat(product.unit_price).toFixed(2)}</span>
                            <span class="line-total">Total: R${parseFloat(product.line_total).toFixed(2)}</span>
                        </div>
                    `;
                    div.setAttribute('data-product-id', product.product_id);
                    div.setAttribute('data-original-price', product.unit_price);
                    div.setAttribute('data-original-line-total', product.line_total);
                    
                    // Highlight first result
                    if (index === 0) {
                        div.classList.add('selected');
                    }
                    
                    div.addEventListener('click', () => {
                        inputElement.value = product.product_description || product.product_name;
                        inputElement.setAttribute('data-product-id', product.product_id);
                        inputElement.setAttribute('data-original-price', product.unit_price);
                        inputElement.setAttribute('data-original-line-total', product.line_total);
                        
                        // Auto-fill amount with original line total
                        const amountInput = inputElement.closest('tr').querySelector('.credit-amount');
                        amountInput.value = `R${parseFloat(product.line_total).toFixed(2)}`;
                        formatPrice(amountInput);
                        
                        dropdown.innerHTML = '';
                        updateCreditNoteTotals();
                        updateLivePreview();
                    });
                    
                    div.addEventListener('mouseenter', () => {
                        dropdown.querySelectorAll('.search-result').forEach(r => r.classList.remove('selected'));
                        div.classList.add('selected');
                    });
                    
                    dropdown.appendChild(div);
                });
            } else {
                // Show no results message
                const div = document.createElement('div');
                div.className = 'search-result no-results';
                div.textContent = 'No products found matching your search';
                dropdown.appendChild(div);
            }
        } else {
            // Show no results message
            const div = document.createElement('div');
            div.className = 'search-result no-results';
            div.textContent = 'No products found in original invoice';
            dropdown.appendChild(div);
        }
    })
    .catch(error => {
        console.error('Error searching original products:', error);
        const dropdown = inputElement.nextElementSibling;
        dropdown.innerHTML = '<div class="search-result error">Error searching original products</div>';
    });
}

function loadOriginalInvoiceProducts(invoiceId) {
    // This function can be used to pre-load products when credit note is created
    console.log('Loading original invoice products for:', invoiceId);
}

function updateCreditNoteTotals() {
    let total = 0;
    
    // Calculate total from credit note items
    document.querySelectorAll('.credit-note-item-row').forEach(row => {
        const amountElement = row.querySelector('.credit-amount');
        if (amountElement) {
            const amount = parseFloat(amountElement.value.replace(/[^\d.-]/g, '') || 0);
            total += amount;
        }
    });
    
    // Update credit note total display
    const creditNoteTotalElement = document.getElementById('credit-note-total');
    if (creditNoteTotalElement) {
        creditNoteTotalElement.textContent = `R${total.toFixed(2)}`;
    }
    
    // Update main summary totals for credit notes
    const documentType = document.getElementById('document-type').value;
    if (documentType === 'credit-note') {
        // Update subtotal (same as total for credit notes)
        const subtotalElement = document.getElementById('subtotal');
        if (subtotalElement) {
            subtotalElement.textContent = `R${total.toFixed(2)}`;
        }
        
        // Update tax total (0 for credit notes)
        const taxTotalElement = document.getElementById('tax-total');
        if (taxTotalElement) {
            taxTotalElement.textContent = 'R0.00';
        }
        
        // Update final total (negative for credit notes)
        const finalTotalElement = document.getElementById('final-total');
        if (finalTotalElement) {
            finalTotalElement.textContent = `-R${total.toFixed(2)}`;
        }
    }
    
    // Update live preview
    updateLivePreview();
}

function getCreditNoteFormData() {
    const rows = document.querySelectorAll('.credit-note-item-row');
    const items = [];
    
    rows.forEach(row => {
        const type = row.querySelector('.credit-type').value;
        const reason = row.querySelector('.credit-reason').value;
        const amount = parseFloat(row.querySelector('.credit-amount').value.replace(/[^\d.-]/g, '')) || 0;
        const productId = row.querySelector('.credit-reason').getAttribute('data-product-id');
        
        if (reason && amount > 0) {
            items.push({
                type: type,
                reason: reason,
                amount: amount,
                product_id: productId || null
            });
        }
    });
    
    return items;
}

// --- Issuer Details Functions ---
async function loadIssuerDetails() {
    try {
        const response = await fetch('../api/document-api.php?action=get_company_info', {
            credentials: 'include'
        });
        
        if (!response.ok) {
            throw new Error(`HTTP ${response.status}: ${response.statusText}`);
        }
        
        const data = await response.json();
        
        if (data.success && data.data) {
            const companyInfo = data.data;
            
            // Update issuer info display
            const issuerNameDisplay = document.getElementById('issuer-name-display');
            const issuerAddressDisplay = document.getElementById('issuer-address-display');
            const issuerEmailDisplay = document.getElementById('issuer-email-display');
            const issuerPhoneDisplay = document.getElementById('issuer-phone-display');
            const issuerVatDisplay = document.getElementById('issuer-vat-display');
            
            if (issuerNameDisplay) issuerNameDisplay.textContent = companyInfo.company_name || 'Your Company';
            if (issuerAddressDisplay) issuerAddressDisplay.textContent = companyInfo.company_address || '123 Main Street, Alberton';
            if (issuerEmailDisplay) issuerEmailDisplay.textContent = companyInfo.company_email || 'contact@yourcompany.com';
            if (issuerPhoneDisplay) issuerPhoneDisplay.textContent = companyInfo.company_phone || '011 555 1234';
            if (issuerVatDisplay) issuerVatDisplay.textContent = `VAT: ${companyInfo.vat_number || '4001234567'}`;
            
            // Update preview company info
            const previewCompany = document.querySelector('.preview-company');
            if (previewCompany) {
                previewCompany.innerHTML = `
                    <strong>${companyInfo.company_name || 'Your Company'}</strong><br>
                    ${companyInfo.company_address || '123 Main Street, Alberton'}<br>
                    ${companyInfo.company_email || 'contact@yourcompany.com'}
                `;
            }
        }
    } catch (error) {
        console.error('Error loading issuer details:', error);
        // Keep default values if loading fails
    }
}

// --- Refund Functions ---
function addRefundItem() {
    const tableBody = document.getElementById('refund-rows');
    if (!tableBody) return;
    
    const row = document.createElement('tr');
    row.classList.add('refund-item-row');
    row.innerHTML = `
        <td>
            <select class="refund-type">
                <option value="full">Full Refund</option>
                <option value="partial">Partial Refund</option>
            </select>
        </td>
        <td>
            <div class="search-container" style="position: relative;">
                <input type="text" placeholder="Search original product" class="original-product" autocomplete="off">
                <div class="refund-search-dropdown"></div>
            </div>
        </td>
        <td><input type="text" value="R0.00" class="refund-amount" oninput="removeR(this)" onblur="formatPrice(this)" onfocus="removeR(this)"></td>
        <td class="total-cell">
            <button type="button" class="remove-row-btn" title="Remove Line">&#10006;</button>
        </td>
    `;
    
    tableBody.appendChild(row);
    setupRefundRowListeners(row);
}

function removeRefundItem(event) {
    const button = event.target;
    const row = button.closest('tr');
    if (row) {
        const tableBody = row.parentNode;
        const allRows = tableBody.querySelectorAll('.refund-item-row');
        
        if (allRows.length <= 1) {
            showResponseModal('Cannot remove the last refund item. At least one item must remain.', 'warning');
            return;
        }
        
        const rowIndex = Array.from(allRows).indexOf(row);
        if (rowIndex === 0) {
            showResponseModal('Cannot remove the first refund item.', 'warning');
            return;
        }
        
        row.remove();
        updateRefundTotals();
    }
}

function setupRefundRowListeners(row) {
    const refundAmountInput = row.querySelector('.refund-amount');
    const originalProductInput = row.querySelector('.original-product');
    const refundTypeSelect = row.querySelector('.refund-type');
    const removeBtn = row.querySelector('.remove-row-btn');
    
    if (refundAmountInput) {
        refundAmountInput.addEventListener('input', () => {
            removeR(refundAmountInput);
        });
        refundAmountInput.addEventListener('blur', () => {
            formatPrice(refundAmountInput);
        });
        refundAmountInput.addEventListener('focus', () => {
            removeR(refundAmountInput);
        });
    }
    
    if (originalProductInput) {
        originalProductInput.addEventListener('input', () => {
            searchOriginalProducts(originalProductInput, originalProductInput.value);
        });
    }
    
    if (refundTypeSelect) {
        refundTypeSelect.addEventListener('change', () => {
            updateRefundTotals();
        });
    }
    
    if (removeBtn) {
        removeBtn.addEventListener('click', removeRefundItem);
    }
}

function updateRefundTotals() {
    const refundRows = document.querySelectorAll('#refund-rows .refund-item-row');
    let totalRefund = 0;
    
    refundRows.forEach(row => {
        const amountInput = row.querySelector('.refund-amount');
        if (amountInput) {
            const amount = parseFloat(amountInput.value.replace(/[^\d.-]/g, '')) || 0;
            totalRefund += amount;
        }
    });
    
    // Update any refund total display
    const refundTotalElement = document.getElementById('refund-total');
    if (refundTotalElement) {
        refundTotalElement.textContent = `R${totalRefund.toFixed(2)}`;
    }
}

function getRefundFormData() {
    const refundRows = document.querySelectorAll('#refund-rows .refund-item-row');
    const refundItems = [];
    
    refundRows.forEach(row => {
        const refundType = row.querySelector('.refund-type')?.value || 'full';
        const originalProduct = row.querySelector('.original-product')?.value || '';
        const refundAmount = row.querySelector('.refund-amount')?.value || 'R0.00';
        
        if (originalProduct && refundAmount !== 'R0.00') {
            refundItems.push({
                refund_type: refundType,
                original_product: originalProduct,
                refund_amount: refundAmount
            });
        }
    });
    
    return refundItems;
}

function setupInitialRefundListeners() {
    console.log('[setupInitialRefundListeners] Setting up initial refund listeners');
    
    const refundRows = document.querySelectorAll('#refund-rows .refund-item-row');
    console.log('[setupInitialRefundListeners] Found refund rows:', refundRows.length);
    
    refundRows.forEach(row => {
        setupRefundRowListeners(row);
    });
    
    // Add button listener
    const addRefundItemBtn = document.getElementById('add-refund-item-btn');
    if (addRefundItemBtn) {
        addRefundItemBtn.addEventListener('click', addRefundItem);
    }
}

// Related Invoice Selection Functions
function openRelatedInvoiceSelector() {
    const clientId = document.getElementById('client-id').value;
    if (!clientId) {
        showResponseModal('error', 'Error', 'Please select a client first before selecting a related invoice.');
        return;
    }
    
    const dropdown = document.getElementById('related-invoice-dropdown');
    dropdown.style.display = 'block';
    dropdown.innerHTML = '<div class="loading">Loading invoices...</div>';
    
    // Fetch available invoices for this client
    fetch(`../api/document-api.php?action=get_available_invoices_for_credit_refund&client_id=${clientId}`, {
        credentials: 'include'
    })
    .then(response => response.json())
    .then(data => {
        dropdown.innerHTML = '';
        
        if (data.success && data.data && data.data.length > 0) {
            data.data.forEach(invoice => {
                const div = document.createElement('div');
                div.className = 'invoice-option';
                div.innerHTML = `
                    <div class="invoice-number">${invoice.document_number}</div>
                    <div class="invoice-details">
                        <span class="invoice-date">${invoice.issue_date}</span>
                        <span class="invoice-amount">R${parseFloat(invoice.total_amount).toFixed(2)}</span>
                        <span class="invoice-balance">Balance: R${parseFloat(invoice.balance_due).toFixed(2)}</span>
                    </div>
                `;
                
                div.addEventListener('click', () => {
                    selectRelatedInvoice(invoice);
                });
                
                dropdown.appendChild(div);
            });
        } else {
            dropdown.innerHTML = '<div class="no-results">No invoices found for this client</div>';
        }
    })
    .catch(error => {
        console.error('Error fetching invoices:', error);
        dropdown.innerHTML = '<div class="error">Error loading invoices</div>';
    });
}

function selectRelatedInvoice(invoice) {
    // Set the related document ID and display
    document.getElementById('related-document-id').value = invoice.document_id;
    document.getElementById('related-document-number-display').value = invoice.document_number;
    
    // Hide the dropdown
    document.getElementById('related-invoice-dropdown').style.display = 'none';
    
    // Update credit note totals and preview
    updateCreditNoteTotals();
    updateLivePreview();
    
    // Show success message
    showResponseModal('success', 'Success', `Selected invoice ${invoice.document_number} as related document.`);
}

// Add click outside handler for related invoice dropdown
document.addEventListener('click', function(e) {
    const dropdown = document.getElementById('related-invoice-dropdown');
    const selectBtn = document.getElementById('select-related-invoice-btn');
    
    if (dropdown && !e.target.closest('.search-invoice-container') && !e.target.closest('.related-invoice-dropdown')) {
        dropdown.style.display = 'none';
    }
});

// Ensure global functions are available immediately and after DOM loads
function attachGlobalFunctions() {
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

    // Replace the old createOrUpdateDocument global with createDocument
    window.createOrUpdateDocument = createDocument;

    // Add new document workflow functions to global scope
    window.previewDocument = previewDocument;
    window.saveDocument = saveDocument;
    window.createDocument = createDocument;
    window.clearDocument = clearDocument;

    // Add credit note functions to global scope
    window.addCreditNoteItem = addCreditNoteItem;
    window.removeCreditNoteItem = removeCreditNoteItem;
    window.setupCreditNoteRowListeners = setupCreditNoteRowListeners;
    window.searchCreditReasons = searchCreditReasons;
    window.searchOriginalProducts = searchOriginalProducts;
    window.updateCreditNoteTotals = updateCreditNoteTotals;
    window.getCreditNoteFormData = getCreditNoteFormData;
    window.loadIssuerDetails = loadIssuerDetails;

    // Add refund functions to global scope
    window.addRefundItem = addRefundItem;
    window.removeRefundItem = removeRefundItem;
    window.setupRefundRowListeners = setupRefundRowListeners;
    window.updateRefundTotals = updateRefundTotals;
    window.getRefundFormData = getRefundFormData;
    window.setupInitialRefundListeners = setupInitialRefundListeners;
    
    // Add related invoice functions to global scope
    window.openRelatedInvoiceSelector = openRelatedInvoiceSelector;
    window.selectRelatedInvoice = selectRelatedInvoice;

    // Add live preview functions to global scope
    window.initializeLivePreview = initializeLivePreview;
    window.updateLivePreview = updateLivePreview;

    // Add client panel functions to global scope
    window.initializeClientPanel = initializeClientPanel;
    
    // Add missing functions that were causing errors
    window.resetDocumentForm = resetDocumentForm;
    window.initializeLogoUpload = function() {
        // Placeholder function to prevent errors
        console.log('Logo upload initialization - placeholder');
    };
}

// Attach functions immediately
attachGlobalFunctions();

// Also attach when DOM is ready to ensure they're available
document.addEventListener('DOMContentLoaded', attachGlobalFunctions);


