// Global variables
let currentPage = 1;
let rowsPerPage = 10;
let currentSection = 'private'; // or 'business'
let customers = [];
let companies = [];

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {

    // Tab switching functionality
    const modalTabs = document.querySelectorAll('.modal-tab');
    const tabContents = document.querySelectorAll('.tab-content');

    modalTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs
            modalTabs.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            tab.classList.add('active');

            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.remove('active');
                content.style.display = 'none';
            });

            // Show the selected tab content
            const tabId = tab.getAttribute('data-tab');
            const activeContent = document.querySelector(`.tab-content[data-tab-content="${tabId}"]`);
            if (activeContent) {
                activeContent.classList.add('active');
                activeContent.style.display = 'block';
            }
        });
    });

    // Function to initialize tabs for a specific modal
    function initializeTabs(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const modalTabs = modal.querySelectorAll('.modal-tab');
        const tabContents = modal.querySelectorAll('.tab-content');

        modalTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs in this modal
                modalTabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                tab.classList.add('active');

                // Hide all tab contents in this modal
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    content.style.display = 'none';
                });

                // Show the selected tab content
                const tabId = tab.getAttribute('data-tab');
                const activeContent = modal.querySelector(`.tab-content[data-tab-content="${tabId}"]`);
                if (activeContent) {
                    activeContent.classList.add('active');
                    activeContent.style.display = 'block';
                }
            });
        });

        // Activate first tab by default
        if (modalTabs.length > 0) {
            modalTabs[0].click();
        }
    }

    // Initialize tabs for both modals
    initializeTabs('customerModal');
    initializeTabs('companyModal');

    // Initialize tabs
    initializeTabs();

    // Add Customer button
    const addCustomerBtn = document.getElementById('addCustomerBtn');
    if (addCustomerBtn) {
        addCustomerBtn.addEventListener('click', function() {
            console.log('Add Customer button clicked');
            showAddCustomerModal();
        });
    }

    // Add Company button
    const addCompanyBtn = document.getElementById('addCompanyBtn');
    if (addCompanyBtn) {
        addCompanyBtn.addEventListener('click', function() {
            console.log('Add Company button clicked');
            showAddCompanyModal();
        });
    }

    // Add form submission handler
    const addClientForm = document.getElementById('addClientForm');
    const clientTypeSelect = document.getElementById('clientType');
    const companyFields = document.getElementById('companyFields');
    const customerFields = document.getElementById('customerFields');

    // Handle client type change
    if (clientTypeSelect) {
        clientTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            // Hide both sections first
            if (companyFields) companyFields.style.display = 'none';
            if (customerFields) customerFields.style.display = 'none';
            
            // Remove required from all fields
            const allFields = addClientForm.querySelectorAll('input, select');
            allFields.forEach(field => field.removeAttribute('required'));
            
            // Show and set required fields based on selection
            if (selectedType === 'company') {
                if (companyFields) {
                    companyFields.style.display = 'block';
                    // Set required fields for company
                    const requiredFields = [
                        'add-company-name',
                        'add-address-line1',
                        'add-city',
                        'add-postal-code',
                        'add-country',
                        'add-contact-first-name',
                        'add-contact-last-name',
                        'add-contact-email',
                        'add-contact-phone'
                    ];
                    requiredFields.forEach(id => {
                        const field = document.getElementById(id);
                        if (field) field.setAttribute('required', '');
                    });
                }
            } else if (selectedType === 'customer') {
                if (customerFields) {
                    customerFields.style.display = 'block';
                    // Set required fields for customer
                    const requiredFields = [
                        'add-first-name',
                        'add-last-name',
                        'add-email',
                        'add-phone',
                        'add-address-line1',
                        'add-city',
                        'add-postal-code',
                        'add-country'
                    ];
                    requiredFields.forEach(id => {
                        const field = document.getElementById(id);
                        if (field) field.setAttribute('required', '');
                    });
                }
            }
        });
    }

    // Attach close event for add client modal close button ONCE
    const closeAdd = document.getElementById('closeAddClientModal');
    if (closeAdd) {
        closeAdd.addEventListener('click', () => closeModal('add-modal'));
    }

    // Attach close event for clicking outside the modal content
    const addModal = document.getElementById('add-modal');
    if (addModal) {
        addModal.addEventListener('click', function(event) {
            if (event.target === addModal) {
                closeModal('add-modal');
            }
        });
    }

    // Attach close event for Escape key
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape' && addModal && addModal.style.display === 'flex') {
            closeModal('add-modal');
        }
    });
});

function closeEditModal() {
    document.getElementById('edit-modal').style.display = 'none';
}

export function closeAddModal() {
    document.getElementById('add-modal').style.display = 'none';
}

function setRequiredFieldsForClientType(type) {
    // Company required fields
    const companyRequired = [
        'add-company-name',
        'add-company-address-line1',
        'add-company-city',
        'add-company-postal-code',
        'add-company-country',
        'add-contact-first-name',
        'add-contact-last-name',
        'add-contact-email',
        'add-contact-phone'
    ];
    // Customer required fields
    const customerRequired = [
        'add-first-name',
        'add-last-name',
        'add-email',
        'add-phone',
        'add-customer-address-line1',
        'add-customer-city',
        'add-customer-postal-code',
        'add-customer-country'
    ];
    // Remove required from all
    [...companyRequired, ...customerRequired].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.removeAttribute('required');
    });
    // Set required only for the selected type
    const toSet = type === 'company' ? companyRequired : customerRequired;
    toSet.forEach(id => {
        const el = document.getElementById(id);
        if (el) el.setAttribute('required', '');
    });
}

// Update modal forms
export function updateModalForms() {
    const type = document.getElementById('clientType').value;
    document.getElementById('companyFields').style.display = type === 'company' ? 'block' : 'none';
    document.getElementById('customerFields').style.display = type === 'customer' ? 'block' : 'none';
    setRequiredFieldsForClientType(type);
}

// Initialize tabs for both modals
function initializeTabs() {
    // Customer modal tabs
    const customerModal = document.getElementById('add-modal');
    if (customerModal) {
        const customerTabs = customerModal.querySelectorAll('.modal-tab');
        const customerTabContents = customerModal.querySelectorAll('.tab-content');
        const customerForm = document.getElementById('addCustomerForm');

        customerTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                customerTabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                tab.classList.add('active');

                // Hide all tab contents and remove required attributes
                customerTabContents.forEach(content => {
                    content.classList.remove('active');
                    content.style.display = 'none';
                    const requiredFields = content.querySelectorAll('[required]');
                    requiredFields.forEach(field => field.removeAttribute('required'));
                });

                // Show the selected tab content and add required attributes
                const tabId = tab.getAttribute('data-tab');
                const activeContent = customerModal.querySelector(`.tab-content[data-tab-content="${tabId}"]`);
                if (activeContent) {
                    activeContent.classList.add('active');
                    activeContent.style.display = 'block';
                    const requiredFields = activeContent.querySelectorAll('[required]');
                    requiredFields.forEach(field => field.setAttribute('required', ''));
                }
            });
        });

        // Activate first tab by default
        if (customerTabs.length > 0) {
            customerTabs[0].click();
        }
    }

    // Company modal tabs (similar structure)
    const companyModal = document.getElementById('add-modal');
    if (companyModal) {
        const companyTabs = companyModal.querySelectorAll('.modal-tab');
        const companyTabContents = companyModal.querySelectorAll('.tab-content');
        const companyForm = document.getElementById('addCompanyForm');

        companyTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                companyTabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                tab.classList.add('active');

                // Hide all tab contents and remove required attributes
                companyTabContents.forEach(content => {
                    content.classList.remove('active');
                    content.style.display = 'none';
                    const requiredFields = content.querySelectorAll('[required]');
                    requiredFields.forEach(field => field.removeAttribute('required'));
                });

                // Show the selected tab content and add required attributes
                const tabId = tab.getAttribute('data-tab');
                const activeContent = companyModal.querySelector(`.tab-content[data-tab-content="${tabId}"]`);
                if (activeContent) {
                    activeContent.classList.add('active');
                    activeContent.style.display = 'block';
                    const requiredFields = activeContent.querySelectorAll('[required]');
                    requiredFields.forEach(field => field.setAttribute('required', ''));
                }
            });
        });

        // Activate first tab by default
        if (companyTabs.length > 0) {
            companyTabs[0].click();
        }
    }
}

// --- Modal Logic for Client/Company ---
import { getCompanyFormData, getCustomerFormData, populateCompanyModalFields, populateCustomerFormFields, setupCompanyModalSubmission, setupCustomerModalSubmission } from './client-form.js';
import { fetchEntityData, submitEntityApi } from './client-api.js';

function activateFirstTab(modalId) {
    const modal = document.getElementById(modalId);
    if (!modal) return;
    const tabs = modal.querySelectorAll('.modal-tab');
    const tabContents = modal.querySelectorAll('.tab-content');
    tabs.forEach(t => t.classList.remove('active'));
    tabContents.forEach(c => {
        c.classList.remove('active');
        c.style.display = 'none';
    });
    if (tabs.length > 0 && tabContents.length > 0) {
        tabs[0].classList.add('active');
        tabContents[0].classList.add('active');
        tabContents[0].style.display = 'block';
    }
}

// Open Add Company Modal
export function showAddCompanyModal() {
    const modal = document.getElementById('add-modal');
    const form = document.getElementById('addClientForm');
    if (!modal || !form) return;
    form.reset();
    document.getElementById('companyFields').style.display = 'block';
    document.getElementById('customerFields').style.display = 'none';
    document.getElementById('clientType').value = 'company';
    setRequiredFieldsForClientType('company');
    // Add event listener for clientType change
    const clientType = document.getElementById('clientType');
    if (clientType) clientType.addEventListener('change', updateModalForms, { once: true });
    form.onsubmit = async function(e) {
        e.preventDefault();
        const formData = getCompanyFormData(form);
        const result = await submitEntityApi('company', 'add', formData);
        if (result.success) {
            showResponseModal('Company added successfully', 'success');
            modal.style.display = 'none';
            currentSection = 'business'; // Ensure company section is active
            await loadData();
        } else {
            showResponseModal(result.error || 'Failed to add Company', 'error');
        }
    };
    modal.style.display = 'block';
}

// Open Add Customer Modal
export function showAddCustomerModal() {
    const modal = document.getElementById('add-modal');
    const form = document.getElementById('addClientForm');
    if (!modal || !form) return;
    form.reset();
    document.getElementById('companyFields').style.display = 'none';
    document.getElementById('customerFields').style.display = 'block';
    document.getElementById('clientType').value = 'customer';
    setRequiredFieldsForClientType('customer');
    // Add event listener for clientType change
    const clientType = document.getElementById('clientType');
    if (clientType) clientType.addEventListener('change', updateModalForms, { once: true });
    form.onsubmit = async function(e) {
        e.preventDefault();
        const formData = getCustomerFormData(form);
        const result = await submitEntityApi('customer', 'add', formData);
        if (result.success) {
            showResponseModal('Customer added successfully', 'success');
            modal.style.display = 'none';
            currentSection = 'private'; // Ensure customer section is active
            await loadData();
        } else {
            showResponseModal(result.error || 'Failed to add Customer', 'error');
        }
    };
    modal.style.display = 'block';
}

// Open Edit Company Modal
export async function openCompanyModal(companyId) {
    const modal = document.getElementById('companyModal');
    if (!modal) return;
    activateFirstTab('companyModal');
    const result = await fetchEntityData('company', companyId);
    if (result.success) {
        populateCompanyModalFields(result.data);
        setupCompanyModalSubmission(companyId, (formData) => submitEntityApi('company', 'update', formData));
        modal.style.display = 'block';
    } else {
        showResponseModal('Error', result.error);
    }
}

// Open Edit Customer Modal
export async function openCustomerModal(customerId) {
    const modal = document.getElementById('customerModal');
    if (!modal) return;
    activateFirstTab('customerModal');
    const result = await fetchEntityData('customer', customerId);
    if (result.success) {
        populateCustomerFormFields(result.data);
        setupCustomerModalSubmission(customerId, (formData) => submitEntityApi('customer', 'update', formData));
        modal.style.display = 'block';
    } else {
        showResponseModal('Error', result.error);
    }
}

// Utility: Close Modal
export function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) modal.style.display = 'none';
}