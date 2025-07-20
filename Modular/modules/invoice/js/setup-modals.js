// --- Supplier Modal Logic ---

export function openSupplierModal(supplierId = null) {
    const modal = document.getElementById('supplierModal');
    const title = document.getElementById('supplierModalTitle');
    const form = document.getElementById('supplierForm');
    // Reset form
    form.reset();
    document.getElementById('supplier-id').value = '';
    if (supplierId) {
        title.textContent = 'Edit Supplier';
        loadSupplierData(supplierId);
    } else {
        title.textContent = 'Add Supplier';
    }
    modal.style.display = 'block';
}

export function closeSupplierModal() {
    const modal = document.getElementById('supplierModal');
    modal.style.display = 'none';
}

export async function loadSupplierData(supplierId) {
    // Use SetupAPI to fetch supplier data and autofill form
    const { getSupplier } = await import('./setup-api.js');
    const res = await getSupplier(supplierId);
    if (res.success && res.data) {
        document.getElementById('supplier-id').value = res.data.supplier_id;
        document.getElementById('supplier-name').value = res.data.name || '';
        document.getElementById('supplier-email').value = res.data.email || '';
        document.getElementById('supplier-phone').value = res.data.phone || '';
        document.getElementById('supplier-address').value = res.data.address || '';
        document.getElementById('supplier-contact-person').value = res.data.contact_person || '';
    }
}

// --- Sales Target Modal Logic ---

export function openSalesTargetModal(targetId = null) {
    const modal = document.getElementById('salesTargetModal');
    const title = document.getElementById('salesTargetModalTitle');
    const form = document.getElementById('salesTargetForm');
    // Reset form
    form.reset();
    document.getElementById('sales-target-id').value = '';
    loadUsersForSalesTarget();
    if (targetId) {
        title.textContent = 'Edit Sales Target';
        loadSalesTargetData(targetId);
    } else {
        title.textContent = 'Add Sales Target';
    }
    modal.style.display = 'block';
}

export function closeSalesTargetModal() {
    const modal = document.getElementById('salesTargetModal');
    modal.style.display = 'none';
}

export async function loadUsersForSalesTarget() {
    // Use SetupAPI to fetch users and populate dropdown
    const { getSalesUsers } = await import('./setup-api.js');
    const res = await getSalesUsers();
    const userSelect = document.getElementById('sales-target-user');
    if (!userSelect) return;
    userSelect.innerHTML = '<option value="">All Representatives</option>';
    if (res.success && Array.isArray(res.data)) {
        res.data.forEach(user => {
            const option = document.createElement('option');
            option.value = user.user_id;
            option.textContent = user.name;
            userSelect.appendChild(option);
        });
    }
}

export async function loadSalesTargetData(targetId) {
    // Use SetupAPI to fetch sales target data and autofill form
    const { getSalesTarget } = await import('./setup-api.js');
    const res = await getSalesTarget(targetId);
    if (res.success && res.data) {
        document.getElementById('sales-target-id').value = res.data.sales_target_id;
        document.getElementById('sales-target-user').value = res.data.user_id || '';
        document.getElementById('sales-target-amount').value = res.data.target_amount || '';
        document.getElementById('sales-target-period').value = res.data.period || '';
        document.getElementById('sales-target-start-date').value = res.data.start_date || '';
        document.getElementById('sales-target-end-date').value = res.data.end_date || '';
    }
}

// --- Credit Reason Modal Logic ---
const creditReasonModal = document.getElementById('creditReasonModal');
const creditReasonForm = document.getElementById('creditReasonForm');

export function openCreditReasonModal(mode = 'add', data = null) {
    if (!creditReasonModal || !creditReasonForm) return;
    creditReasonModal.setAttribute('data-mode', mode);
    creditReasonForm.reset();
    creditReasonForm.querySelector('[name="credit_reason_id"]').value = '';
    creditReasonForm.querySelector('[name="reason"]').value = '';
    if (mode === 'edit' && data) {
        creditReasonForm.querySelector('[name="credit_reason_id"]').value = data.credit_reason_id;
        creditReasonForm.querySelector('[name="reason"]').value = data.reason;
    }
    creditReasonModal.style.display = 'block';
}

export function closeCreditReasonModal() {
    if (!creditReasonModal) return;
    creditReasonForm.reset();
    creditReasonModal.removeAttribute('data-mode');
    creditReasonModal.style.display = 'none';
    // No event object here; nothing to prevent. Remove this line.
}

// Attach close event if modal has a close button
const closeBtn = creditReasonModal?.querySelector('.modal-close-btn');
if (closeBtn) {
    closeBtn.addEventListener('click', closeCreditReasonModal);
}

// --- Modal Close on Outside Click ---
document.addEventListener('click', function(event) {
    const supplierModal = document.getElementById('supplierModal');
    if (supplierModal && event.target === supplierModal) {
        closeSupplierModal();
    }
    const salesTargetModal = document.getElementById('salesTargetModal');
    if (salesTargetModal && event.target === salesTargetModal) {
        closeSalesTargetModal();
    }
});
