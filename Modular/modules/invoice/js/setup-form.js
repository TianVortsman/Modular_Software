import { closeSupplierModal, closeSalesTargetModal } from './setup-modals.js';
import * as SetupAPI from './setup-api.js';

// --- Supplier Form Submit ---
const supplierForm = document.getElementById('supplierForm');
if (supplierForm) {
    supplierForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        try {
            showLoadingModal('Saving supplier...');
            const formData = new FormData(supplierForm);
            const data = await SetupAPI.saveSupplier(formData);
            hideLoadingModal();
            if (data.success) {
                showResponseModal('Success', 'Supplier saved successfully', 'success');
                closeSupplierModal();
                // Optionally reload supplier list
                if (window.invoiceSetup && window.invoiceSetup.loadSuppliers) {
                    window.invoiceSetup.loadSuppliers();
                }
            } else {
                showResponseModal('Error', data.message, 'error');
            }
        } catch (error) {
            hideLoadingModal();
            showResponseModal('Error', 'Failed to save supplier', 'error');
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
                showResponseModal('Error', data.message, 'error');
            }
        } catch (error) {
            hideLoadingModal();
            showResponseModal('Error', 'Failed to save sales target', 'error');
        }
    });
}
