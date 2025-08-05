// Client Modal Logic: Handles modal open/close, tab switching, autofill, and state reset
// Usage: import { openModal, closeModal, switchTab, autofillModal, resetModalState } from './client-modal.js';

/**
 * Open the client modal for editing (company or customer)
 * @param {number} clientId
 * @param {string} [type] - 'company' or 'customer' (optional, can be inferred from data)
 */
async function openClientModal(clientId, type = null) {
    // Determine modal type by fetching details
    const res = await window.fetchClientDetails(clientId);
    if (!res.success || !res.data) {
        const errorMsg = res.error || res.message || 'Failed to load client details';
        window.ResponseModal && window.ResponseModal.error(errorMsg);
        return;
    }
    // Infer type if not provided
    const clientType = type || (res.data.client_type === 'company' || res.data.client_type === 'business' ? 'company' : 'customer');
    const modalId = clientType === 'company' ? 'companyModal' : 'customerModal';
    openModal(modalId, res.data);
}

/**
 * Open a modal (add/edit)
 * @param {string} modalId
 * @param {Object} [data] - Optional data to autofill
 */
// All functions are now globally available via window object

function openModal(modalId, data = null) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.style.display = 'block';
    resetModalState(modalId);
    if (data) autofillModal(modalId, data);
    // Set mode for later (add/edit)
    modal.setAttribute('data-mode', data ? 'edit' : 'add');

    // Enable tab switching (only bind once)
    if (!modal._tabEventsBound) {
        const tabs = modal.querySelectorAll('.modal-tab');
        tabs.forEach(tab => {
            tab.addEventListener('click', function () {
                if (typeof switchTab === 'function') switchTab(modalId, tab.dataset.tab);
            });
        });
        modal._tabEventsBound = true;
    }

    // Close modal on close button, cancel button, overlay click
    if (!modal._closeEventsBound) {
        // Close button (X)
        const closeBtn = modal.querySelector('.close');
        if (closeBtn) closeBtn.addEventListener('click', () => closeModal(modalId));
        // Cancel button (if present)
        const cancelBtn = modal.querySelector('button#cancelCustomer, button#cancelCompany, button[type="button"].cancel');
        if (cancelBtn) cancelBtn.addEventListener('click', () => closeModal(modalId));
        // Overlay click (outside modal-dialog)
        modal.addEventListener('mousedown', function (e) {
            if (e.target === modal) closeModal(modalId);
        });
        modal._closeEventsBound = true;
    }
    // Always re-assign Escape key handler
    if (modal._escHandler) {
        document.removeEventListener('keydown', modal._escHandler);
    }
    modal._escHandler = function (e) {
        if (e.key === 'Escape') closeModal(modalId);
    };
    document.addEventListener('keydown', modal._escHandler);

    // Wire up form submit
    let form = modal.querySelector('form');
    if (form) {
        form.onsubmit = null;
        const mode = modal.getAttribute('data-mode') === 'edit' ? 'edit' : 'add';
        form.onsubmit = (e) => window.handleFormSubmit(e, mode);
    }
}

/**
 * Close a modal
 * @param {string} modalId
 */
function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    modal.style.display = 'none';
    resetModalState(modalId);
    // Remove Escape key handler
    if (modal._escHandler) {
        document.removeEventListener('keydown', modal._escHandler);
        modal._escHandler = null;
    }
}

/**
 * Autofill modal fields with data (for edit)
 * @param {string} modalId
 * @param {Object} data
 */
function autofillModal(modalId, data) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    if (modalId === 'companyModal') {
        // Company fields
        let el;
        el = modal.querySelector('#companyId'); if (el) el.value = data.client_id || '';
        el = modal.querySelector('#companyName'); if (el) el.value = data.client_name || '';
        el = modal.querySelector('#companyVatNo'); if (el) el.value = data.vat_number || '';
        el = modal.querySelector('#companyRegisNo'); if (el) el.value = data.registration_number || '';
        el = modal.querySelector('#companyIndustry'); if (el) el.value = data.industry || '';
        el = modal.querySelector('#companyWebsite'); if (el) el.value = data.website || '';
        el = modal.querySelector('#companyPhone'); if (el) el.value = data.client_tell || data.client_cell || '';
        el = modal.querySelector('#companyEmail'); if (el) el.value = data.client_email || '';
        // Address (first address only)
        if (data.addresses && data.addresses[0]) {
            const addr = data.addresses[0];
            el = modal.querySelector('#addrLine1'); if (el) el.value = addr.address_line1 || '';
            el = modal.querySelector('#addrLine2'); if (el) el.value = addr.address_line2 || '';
            el = modal.querySelector('#suburb'); if (el) el.value = addr.suburb || '';
            el = modal.querySelector('#city'); if (el) el.value = addr.city || '';
            el = modal.querySelector('#province'); if (el) el.value = addr.province || '';
            el = modal.querySelector('#country'); if (el) el.value = addr.country || '';
            el = modal.querySelector('#postcode'); if (el) el.value = addr.postal_code || '';
        }
        // Contact (first contact only)
        if (data.contacts && data.contacts[0]) {
            const contact = data.contacts[0];
            el = modal.querySelector('#contactFirstName'); if (el) el.value = contact.first_name || '';
            el = modal.querySelector('#contactLastName'); if (el) el.value = contact.last_name || '';
            el = modal.querySelector('#contactEmail'); if (el) el.value = contact.email || '';
            el = modal.querySelector('#contactPhone'); if (el) el.value = contact.phone || contact.cell || '';
            el = modal.querySelector('#contactPosition'); if (el) el.value = contact.position || '';
        }
        // Autofill module toggles (if present)
        if (data.modules && typeof data.modules === 'object') {
            Object.entries(data.modules).forEach(([moduleKey, enabled]) => {
                const toggle = modal.querySelector(`#module-toggle-${moduleKey}`);
                if (toggle) toggle.checked = !!enabled;
            });
        }
    } else if (modalId === 'customerModal') {
        // Customer fields
        let el;
        el = modal.querySelector('#customerId'); if (el) el.value = data.client_id || '';
        el = modal.querySelector('#customerInitials'); if (el) el.value = data.initials || '';
        el = modal.querySelector('#customerTitle'); if (el) el.value = data.title || '';
        el = modal.querySelector('#customerName'); if (el) el.value = data.first_name || '';
        el = modal.querySelector('#customerSurname'); if (el) el.value = data.last_name || '';
        el = modal.querySelector('#customerEmail'); if (el) el.value = data.client_email || '';
        el = modal.querySelector('#customerCell'); if (el) el.value = data.client_cell || '';
        el = modal.querySelector('#customerTel'); if (el) el.value = data.client_tell || '';
        // Address (first address only)
        if (data.addresses && data.addresses[0]) {
            const addr = data.addresses[0];
            el = modal.querySelector('#custAddrLine1'); if (el) el.value = addr.address_line1 || '';
            el = modal.querySelector('#custAddrLine2'); if (el) el.value = addr.address_line2 || '';
            el = modal.querySelector('#custCity'); if (el) el.value = addr.city || '';
            el = modal.querySelector('#custSuburb'); if (el) el.value = addr.suburb || '';
            el = modal.querySelector('#custProvince'); if (el) el.value = addr.province || '';
            el = modal.querySelector('#custPostalCode'); if (el) el.value = addr.postal_code || '';
            el = modal.querySelector('#custCountry'); if (el) el.value = addr.country || '';
        }
        // Additional
        el = modal.querySelector('#customerDOB'); if (el) el.value = data.dob || '';
        el = modal.querySelector('#customerGender'); if (el) el.value = data.gender || '';
        el = modal.querySelector('#customerLoyalty'); if (el) el.value = data.loyalty_level || '';
        el = modal.querySelector('#customerNotes'); if (el) el.value = data.notes || '';
    }
}

/**
 * Reset modal state (clear fields, errors, etc.)
 * @param {string} modalId
 */
function resetModalState(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    // Reset all input fields
    modal.querySelectorAll('input, select, textarea').forEach(input => {
        if (input.type === 'hidden') return;
        if (input.tagName === 'SELECT') input.selectedIndex = 0;
        else if (input.type === 'checkbox' || input.type === 'radio') input.checked = false;
        else input.value = '';
    });
    // Reset tabs to first
    const tabs = modal.querySelectorAll('.modal-tab');
    const contents = modal.querySelectorAll('.tab-content');
    tabs.forEach((tab, i) => tab.classList.toggle('active', i === 0));
    contents.forEach((content, i) => content.classList.toggle('active', i === 0));
    // Remove error classes/messages if any
    modal.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
}

// Restore switchTab for tab switching in modals
function switchTab(modalId, tabName) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    const tabs = modal.querySelectorAll('.modal-tab');
    const contents = modal.querySelectorAll('.tab-content');
    tabs.forEach(tab => {
        tab.classList.toggle('active', tab.dataset.tab === tabName);
    });
    contents.forEach(content => {
        content.classList.toggle('active', content.dataset.tabContent === tabName);
    });
}

// Expose all functions globally for use in client-screen.js and inline HTML
if (typeof window !== 'undefined') {
    window.openClientModal = openClientModal;
    window.openModal = openModal;
    window.closeModal = closeModal;
    window.autofillModal = autofillModal;
    window.resetModalState = resetModalState;
    window.switchTab = switchTab;
}

// Add global functions for sidebar add buttons
function showAddCompanyModal() {
    openModal('companyModal');
}
function showAddCustomerModal() {
    openModal('customerModal');
}
if (typeof window !== 'undefined') {
    window.showAddCompanyModal = showAddCompanyModal;
    window.showAddCustomerModal = showAddCustomerModal;
}
