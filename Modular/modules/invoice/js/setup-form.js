import { closeSupplierModal, closeSalesTargetModal, closeCreditReasonModal } from './setup-modals.js';
import * as SetupAPI from './setup-api.js';

// --- Supplier Form Submit ---
const supplierForm = document.getElementById('supplierForm');
if (supplierForm) {
    supplierForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        try {
            showLoadingModal('Saving supplier...');
            const formData = new FormData(supplierForm);
            const data = await SetupAPI.saveSupplier(formData); // This already calls handleApiResponse
            hideLoadingModal();
            if (data && data.success) {
                showResponseModal('Success', 'Supplier saved successfully', 'success');
                closeSupplierModal();
                // Optionally reload supplier list
                if (window.invoiceSetup && window.invoiceSetup.loadSuppliers) {
                    window.invoiceSetup.loadSuppliers();
                }
            }
            // No else: errors are handled by handleApiResponse
        } catch (error) {
            hideLoadingModal();
            // No showResponseModal here: errors are handled by handleApiResponse
        }
    });
}

// --- Sales Target Form Submit ---
const salesTargetForm = document.getElementById('salesTargetForm');
if (salesTargetForm) {
    salesTargetForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        try {
            showLoadingModal('Saving sales target...');
            const formData = new FormData(salesTargetForm);
            const data = await SetupAPI.saveSalesTarget(formData);
            hideLoadingModal();
            if (data.success) {
                showResponseModal('Success', 'Sales target saved successfully', 'success');
                closeSalesTargetModal();
                // Optionally reload sales targets list
                if (window.invoiceSetup && window.invoiceSetup.loadSalesTargets) {
                    window.invoiceSetup.loadSalesTargets();
                }
            } else {
                const errorMsg = data.error || data.message || 'Failed to save sales target';
                showResponseModal('Error', errorMsg, 'error');
            }
        } catch (error) {
            hideLoadingModal();
            showResponseModal('Error', 'Failed to save sales target', 'error');
        }
    });
}

// --- Credit Policy Form Submit ---
const creditPolicyForm = document.getElementById('credit-policy-form');
if (creditPolicyForm) {
    creditPolicyForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        try {
            showLoadingModal('Saving credit policy...');
            const formData = new FormData(creditPolicyForm);
            const data = await SetupAPI.saveCreditPolicy(formData);
            hideLoadingModal();
            if (data.success) {
                showResponseModal('Success', 'Credit policy saved', 'success');
                loadCreditPolicyForm();
            } else {
                const errorMsg = data.error || data.message || 'Failed to save credit policy';
                showResponseModal('Error', errorMsg, 'error');
            }
        } catch (error) {
            hideLoadingModal();
            showResponseModal('Error', 'Failed to save credit policy', 'error');
        }
    });
}

// --- Load and Autofill Credit Policy Form ---
export async function loadCreditPolicyForm() {
    const creditPolicyForm = document.getElementById('credit-policy-form');
    if (!creditPolicyForm) return;
    showLoadingModal('Loading credit policy...');
    const res = await SetupAPI.getCreditPolicy();
    hideLoadingModal();
    if (res.success && res.data) {
        creditPolicyForm.querySelector('[name="allow_credit_notes"]').checked = !!res.data.allow_credit_notes;
        creditPolicyForm.querySelector('[name="require_approval"]').checked = !!res.data.require_approval;
    }
}

// --- Document Numbering Form Submit ---
const documentNumberingForm = document.getElementById('document-numbering-form');
if (documentNumberingForm) {
    documentNumberingForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        try {
            showLoadingModal('Saving document numbering...');
            const formData = new FormData(documentNumberingForm);
            const data = await SetupAPI.saveDocumentNumbering(formData);
            if (data.success) {
                await loadDocumentNumberingForm(true); // reload and show modal after
            } else {
                hideLoadingModal();
                const errorMsg = data.error || data.message || 'Failed to save document numbering';
                showResponseModal('Error', errorMsg, 'error');
            }
        } catch (error) {
            hideLoadingModal();
            showResponseModal('Error', 'Failed to save document numbering', 'error');
        }
    });
}

// --- Load and Autofill Document Numbering Form ---
export async function loadDocumentNumberingForm(showSuccess = false) {
    const form = document.getElementById('document-numbering-form');
    if (!form) return;
    showLoadingModal('Loading document numbering...');
    const res = await SetupAPI.getDocumentNumbering();
    hideLoadingModal();
    if (res.success && res.data) {
        for (const [key, value] of Object.entries(res.data)) {
            const input = form.querySelector(`[name="${key}"]`);
            if (input) {
                if (input.tagName === 'SELECT') {
                    input.value = value ?? '';
                    // If value is not found, select the first option
                    if (![...input.options].some(opt => opt.value == value)) {
                        input.selectedIndex = 0;
                    }
                } else if (input.type === 'number') {
                    // Accept 0 as valid value
                    input.value = (value !== null && value !== undefined) ? value : '';
                } else {
                    input.value = value ?? '';
                }
            }
        }
        if (showSuccess) showResponseModal('Success', 'Document numbering saved', 'success');
        console.log('Loaded document numbering values:', res.data);
    } else if (showSuccess) {
        showResponseModal('Error', res.message || 'Failed to load document numbering', 'error');
    }
}

function attachCreditReasonFormHandler() {
    const creditReasonForm = document.getElementById('creditReasonForm');
    if (creditReasonForm && !creditReasonForm._handlerAttached) {
        creditReasonForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                showLoadingModal('Saving credit reason...');
                const formData = new FormData(creditReasonForm);
                const id = formData.get('credit_reason_id');
                let data;
                if (id) {
                    data = await SetupAPI.updateCreditReason(formData);
                } else {
                    data = await SetupAPI.addCreditReason(formData);
                }
                hideLoadingModal();
                if (data.success) {
                    showResponseModal('Success', 'Credit reason saved successfully', 'success');
                    closeCreditReasonModal();
                    if (window.invoiceSetup && window.invoiceSetup.loadCreditReasons) {
                        window.invoiceSetup.loadCreditReasons();
                    }
                } else {
                    const errorMsg = data.error || data.message || 'Failed to save credit reason';
                    showResponseModal('Error', errorMsg, 'error');
                }
                console.log('Credit reason API response:', data);
            } catch (error) {
                hideLoadingModal();
                showResponseModal('Error', 'Failed to save credit reason', 'error');
            }
        });
        creditReasonForm._handlerAttached = true;
        console.log('CreditReasonForm submit handler attached');
    }
}

// Attach immediately (for module load)
attachCreditReasonFormHandler();
// Attach on DOMContentLoaded (for safety)
document.addEventListener('DOMContentLoaded', attachCreditReasonFormHandler);
