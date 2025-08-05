// All functions are now globally available via window object

// --- Bank Info Form Submit ---
function attachBankInfoFormHandler() {
    const bankInfoForm = document.getElementById('bank-info-form');
    if (bankInfoForm && !bankInfoForm._handlerAttached) {
        bankInfoForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                window.showLoadingModal('Saving bank information...');
                const formData = new FormData(bankInfoForm);
                const data = await window.SetupAPI.saveBankInfo(formData);
                window.hideLoadingModal();
                if (data.success) {
                    window.showResponseModal('Success', 'Bank information saved successfully', 'success');
                } else {
                    const errorMsg = data.error || data.message || 'Failed to save bank information';
                    window.showResponseModal('Error', errorMsg, 'error');
                }
            } catch (error) {
                window.hideLoadingModal();
                window.showResponseModal('Error', 'Failed to save bank information', 'error');
            }
        });
        bankInfoForm._handlerAttached = true;
    }
}

// --- Company Info Form Submit ---
function attachCompanyInfoFormHandler() {
    const companyInfoForm = document.getElementById('company-info-form');
    if (companyInfoForm && !companyInfoForm._handlerAttached) {
        companyInfoForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                window.showLoadingModal('Saving company information...');
                const formData = new FormData(companyInfoForm);
                const data = await window.SetupAPI.saveCompanyInfo(formData);
                window.hideLoadingModal();
                if (data.success) {
                    window.showResponseModal('Success', 'Company information saved successfully', 'success');
                } else {
                    const errorMsg = data.error || data.message || 'Failed to save company information';
                    window.showResponseModal('Error', errorMsg, 'error');
                }
            } catch (error) {
                window.hideLoadingModal();
                window.showResponseModal('Error', 'Failed to save company information', 'error');
            }
        });
        companyInfoForm._handlerAttached = true;
    }
}

// --- Supplier Form Submit ---
function attachSupplierFormHandler() {
    const supplierForm = document.getElementById('supplierForm');
    if (supplierForm && !supplierForm._handlerAttached) {
        supplierForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                window.showLoadingModal('Saving supplier...');
                const formData = new FormData(supplierForm);
                const data = await window.SetupAPI.saveSupplier(formData); // This already calls handleApiResponse
                window.hideLoadingModal();
                if (data && data.success) {
                    window.showResponseModal('Success', 'Supplier saved successfully', 'success');
                    window.closeSupplierModal();
                    // Optionally reload supplier list
                    if (window.invoiceSetup && window.invoiceSetup.loadSuppliers) {
                        window.invoiceSetup.loadSuppliers();
                    }
                }
                // No else: errors are handled by handleApiResponse
            } catch (error) {
                window.hideLoadingModal();
                // No showResponseModal here: errors are handled by handleApiResponse
            }
        });
        supplierForm._handlerAttached = true;
    }
}

// --- Sales Target Form Submit ---
function attachSalesTargetFormHandler() {
    const salesTargetForm = document.getElementById('salesTargetForm');
    if (salesTargetForm && !salesTargetForm._handlerAttached) {
        salesTargetForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                window.showLoadingModal('Saving sales target...');
                const formData = new FormData(salesTargetForm);
                const data = await window.SetupAPI.saveSalesTarget(formData);
                window.hideLoadingModal();
                if (data.success) {
                    window.showResponseModal('Success', 'Sales target saved successfully', 'success');
                    window.closeSalesTargetModal();
                    // Optionally reload sales targets list
                    if (window.invoiceSetup && window.invoiceSetup.loadSalesTargets) {
                        window.invoiceSetup.loadSalesTargets();
                    }
                } else {
                    const errorMsg = data.error || data.message || 'Failed to save sales target';
                    window.showResponseModal('Error', errorMsg, 'error');
                }
            } catch (error) {
                window.hideLoadingModal();
                window.showResponseModal('Error', 'Failed to save sales target', 'error');
            }
        });
        salesTargetForm._handlerAttached = true;
    }
}

// --- Credit Policy Form Submit ---
function attachCreditPolicyFormHandler() {
    const creditPolicyForm = document.getElementById('credit-policy-form');
    if (creditPolicyForm && !creditPolicyForm._handlerAttached) {
        creditPolicyForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                window.showLoadingModal('Saving credit policy...');
                const formData = new FormData(creditPolicyForm);
                const data = await window.SetupAPI.saveCreditPolicy(formData);
                window.hideLoadingModal();
                if (data.success) {
                    window.showResponseModal('Success', 'Credit policy saved', 'success');
                    loadCreditPolicyForm();
                } else {
                    const errorMsg = data.error || data.message || 'Failed to save credit policy';
                    window.showResponseModal('Error', errorMsg, 'error');
                }
            } catch (error) {
                window.hideLoadingModal();
                window.showResponseModal('Error', 'Failed to save credit policy', 'error');
            }
        });
        creditPolicyForm._handlerAttached = true;
    }
}

// --- Load and Autofill Credit Policy Form ---
async function loadCreditPolicyForm() {
    const creditPolicyForm = document.getElementById('credit-policy-form');
    if (!creditPolicyForm) return;
    window.showLoadingModal('Loading credit policy...');
    const res = await window.SetupAPI.getCreditPolicy();
    window.hideLoadingModal();
    if (res.success && res.data) {
        creditPolicyForm.querySelector('[name="allow_credit_notes"]').checked = !!res.data.allow_credit_notes;
        creditPolicyForm.querySelector('[name="require_approval"]').checked = !!res.data.require_approval;
    }
}

// --- Document Numbering Form Submit ---
function attachDocumentNumberingFormHandler() {
    const documentNumberingForm = document.getElementById('document-numbering-form');
    if (documentNumberingForm && !documentNumberingForm._handlerAttached) {
        documentNumberingForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                window.showLoadingModal('Saving document numbering...');
                const formData = new FormData(documentNumberingForm);
                const data = await window.SetupAPI.saveDocumentNumbering(formData);
                if (data.success) {
                    await loadDocumentNumberingForm(true); // reload and show modal after
                } else {
                    window.hideLoadingModal();
                    const errorMsg = data.error || data.message || 'Failed to save document numbering';
                    window.showResponseModal('Error', errorMsg, 'error');
                }
            } catch (error) {
                window.hideLoadingModal();
                window.showResponseModal('Error', 'Failed to save document numbering', 'error');
            }
        });
        documentNumberingForm._handlerAttached = true;
    }
}

// --- Load and Autofill Document Numbering Form ---
async function loadDocumentNumberingForm(showSuccess = false) {
    const form = document.getElementById('document-numbering-form');
    if (!form) return;
    window.showLoadingModal('Loading document numbering...');
    const res = await window.SetupAPI.getDocumentNumbering();
    window.hideLoadingModal();
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
        if (showSuccess) window.showResponseModal('Success', 'Document numbering saved', 'success');
        console.log('Loaded document numbering values:', res.data);
    } else if (showSuccess) {
        window.showResponseModal('Error', res.message || 'Failed to load document numbering', 'error');
    }
}

function attachCreditReasonFormHandler() {
    const creditReasonForm = document.getElementById('creditReasonForm');
    if (creditReasonForm && !creditReasonForm._handlerAttached) {
        creditReasonForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            try {
                window.showLoadingModal('Saving credit reason...');
                const formData = new FormData(creditReasonForm);
                const id = formData.get('credit_reason_id');
                let data;
                if (id) {
                    data = await window.SetupAPI.updateCreditReason(formData);
                } else {
                    data = await window.SetupAPI.addCreditReason(formData);
                }
                window.hideLoadingModal();
                if (data.success) {
                    window.showResponseModal('Success', 'Credit reason saved successfully', 'success');
                    closeCreditReasonModal();
                    if (window.invoiceSetup && window.invoiceSetup.loadCreditReasons) {
                        window.invoiceSetup.loadCreditReasons();
                    }
                } else {
                    const errorMsg = data.error || data.message || 'Failed to save credit reason';
                    window.showResponseModal('Error', errorMsg, 'error');
                }
                console.log('Credit reason API response:', data);
            } catch (error) {
                window.hideLoadingModal();
                window.showResponseModal('Error', 'Failed to save credit reason', 'error');
            }
        });
        creditReasonForm._handlerAttached = true;
        console.log('CreditReasonForm submit handler attached');
    }
}

// Attach immediately (for module load)
attachBankInfoFormHandler();
attachCompanyInfoFormHandler();
attachSupplierFormHandler();
attachSalesTargetFormHandler();
attachCreditPolicyFormHandler();
attachDocumentNumberingFormHandler();
attachCreditReasonFormHandler();

// Attach on DOMContentLoaded (for safety)
document.addEventListener('DOMContentLoaded', function() {
    attachBankInfoFormHandler();
    attachCompanyInfoFormHandler();
    attachSupplierFormHandler();
    attachSalesTargetFormHandler();
    attachCreditPolicyFormHandler();
    attachDocumentNumberingFormHandler();
    attachCreditReasonFormHandler();
});

// Make functions available globally
window.loadCreditPolicyForm = loadCreditPolicyForm;
window.loadDocumentNumberingForm = loadDocumentNumberingForm;
window.attachBankInfoFormHandler = attachBankInfoFormHandler;
window.attachCompanyInfoFormHandler = attachCompanyInfoFormHandler;
window.attachSupplierFormHandler = attachSupplierFormHandler;
window.attachSalesTargetFormHandler = attachSalesTargetFormHandler;
window.attachCreditPolicyFormHandler = attachCreditPolicyFormHandler;
window.attachDocumentNumberingFormHandler = attachDocumentNumberingFormHandler;
window.attachCreditReasonFormHandler = attachCreditReasonFormHandler;
