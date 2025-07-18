/**
 * Invoice Setup JavaScript
 * Handles all setup functionality including tabs, forms, and API interactions
 */

import * as SetupAPI from './setup-api.js';
import { ProductAPI } from './product-api.js';
import { openCreditReasonModal } from './setup-modals.js';
import { loadCreditPolicyForm } from './setup-form.js';

class InvoiceSetup {
    constructor() {
        this.currentTab = 'products';
        this.init();
    }

    init() {
        this.setupTabNavigation();
        this.setupFormHandlers();
        this.loadInitialData();
    }

    setupTabNavigation() {
        // Support both sidebar tabs (class 'tab') and any '.tab-btn' buttons
        const tabButtons = document.querySelectorAll('.tab, .tab-btn');
        tabButtons.forEach(button => {
            button.addEventListener('click', (e) => {
                e.preventDefault();
                const targetTab = button.getAttribute('data-tab');
                if (targetTab) {
                    this.switchTab(targetTab);
                }
            });
        });
    }

    switchTab(tabName) {
        // --- HARD RESET: Remove any extra .tab-panel elements not matching original tab IDs ---
        const validTabIds = ['products','banking','sales','suppliers','credit','numbering','terms'];
        document.querySelectorAll('.tab-panel').forEach(panel => {
            if (!validTabIds.includes(panel.id)) {
                panel.parentNode.removeChild(panel);
            }
        });

        // Remove .active from all tab buttons (sidebar and in-page)
        document.querySelectorAll('.tab, .tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        // Add .active to the clicked tab button (sidebar or in-page)
        document.querySelectorAll(`.tab[data-tab="${tabName}"], .tab-btn[data-tab="${tabName}"]`).forEach(btn => {
            btn.classList.add('active');
        });

        // Forcibly remove .active from all tab panels
        const allPanels = document.querySelectorAll('.tab-panel');
        allPanels.forEach(panel => panel.classList.remove('active'));
        // Add .active to the selected tab panel only
        const panel = document.getElementById(tabName);
        if (panel) panel.classList.add('active');

        // Debug: log all active panels
        const activePanels = document.querySelectorAll('.tab-panel.active');
        if (activePanels.length > 1) {
            console.warn('More than one tab-panel is active! IDs:', Array.from(activePanels).map(p => p.id));
        }
        if (activePanels.length === 0) {
            console.warn('No tab-panel is active!');
        }

        this.currentTab = tabName;
        this.loadTabData(tabName);
    }

    loadTabData(tabName) {
        switch(tabName) {
            case 'banking':
                this.loadBankInfo();
                this.loadCompanyInfo();
                break;
            case 'sales':
                this.loadSalesTargets();
                break;
            case 'suppliers':
                this.loadSuppliers();
                break;
            case 'credit':
                this.loadCreditPolicy();
                this.loadCreditReasons();
                break;
            case 'numbering':
                this.loadNumberingSettings();
                break;
            case 'terms':
                this.loadTermsSettings();
                break;
            // No product logic here anymore
        }
    }

    setupFormHandlers() {
        const forms = ['bank-info-form', 'company-info-form', 'credit-policy-form', 'numbering-form', 'terms-form'];
        forms.forEach(formId => {
            const form = document.getElementById(formId);
            if (form) {
                form.addEventListener('submit', (e) => {
                    e.preventDefault();
                    this.handleFormSubmit(formId);
                });
            }
        });
    }

    handleFormSubmit(formId) {
        switch(formId) {
            case 'bank-info-form':
                this.saveBankInfo();
                break;
            case 'company-info-form':
                this.saveCompanyInfo();
                break;
            case 'credit-policy-form':
                this.saveCreditPolicy();
                break;
            case 'numbering-form':
                this.saveNumberingSettings();
                break;
            case 'terms-form':
                this.saveTermsSettings();
                break;
        }
    }

    loadInitialData() {
        this.loadTabData(this.currentTab);
    }

    // Banking & Company
    async loadBankInfo() {
        try {
            const data = await SetupAPI.getBankInfo();
            if (data.success && data.data) {
                this.populateBankForm(data.data);
            }
        } catch (error) {
            console.error('Error loading bank info:', error);
        }
    }

    populateBankForm(bankInfo) {
        const fields = ['bank-name', 'bank-branch', 'account-number', 'swift-code'];
        fields.forEach(field => {
            const element = document.getElementById(field);
            if (element) {
                element.value = bankInfo[field.replace('-', '_')] || '';
            }
        });
    }

    async saveBankInfo() {
        try {
            showLoadingModal('Saving bank information...');
            const formData = new FormData(document.getElementById('bank-info-form'));
            const data = await SetupAPI.saveBankInfo(formData);
            hideLoadingModal();
            if (data.success) {
                showResponseModal('Success', 'Bank information saved successfully', 'success');
            } else {
                showResponseModal('Error', data.message, 'error');
            }
        } catch (error) {
            hideLoadingModal();
            showResponseModal('Error', 'Failed to save bank information', 'error');
        }
    }

    async loadCompanyInfo() {
        try {
            const data = await SetupAPI.getCompanyInfo();
            if (data.success && data.data) {
                this.populateCompanyForm(data.data);
            }
        } catch (error) {
            console.error('Error loading company info:', error);
        }
    }

    populateCompanyForm(companyInfo) {
        const fields = ['company-name', 'company-address', 'company-phone', 'company-email', 'vat-number', 'registration-number'];
        fields.forEach(field => {
            const element = document.getElementById(field);
            if (element) {
                element.value = companyInfo[field.replace('-', '_')] || '';
            }
        });
    }

    async saveCompanyInfo() {
        try {
            showLoadingModal('Saving company information...');
            const formData = new FormData(document.getElementById('company-info-form'));
            const data = await SetupAPI.saveCompanyInfo(formData);
            hideLoadingModal();
            if (data.success) {
                showResponseModal('Success', 'Company information saved successfully', 'success');
            } else {
                showResponseModal('Error', data.message, 'error');
            }
        } catch (error) {
            hideLoadingModal();
            showResponseModal('Error', 'Failed to save company information', 'error');
        }
    }

    // Invoice Numbering
    async loadNumberingSettings() {
        try {
            const data = await SetupAPI.getNumberingSettings();
            if (data.success && data.data) {
                this.populateNumberingForm(data.data);
            }
        } catch (error) {
            console.error('Error loading numbering settings:', error);
        }
    }

    populateNumberingForm(settings) {
        const fields = ['invoice-prefix', 'starting-number', 'current-number', 'reset-frequency', 'date-format'];
        fields.forEach(field => {
            const element = document.getElementById(field);
            if (element) {
                element.value = settings[field.replace('-', '_')] || '';
            }
        });

        const autoReset = document.getElementById('auto-reset-count');
        if (autoReset) autoReset.checked = settings.auto_reset_count || false;
    }

    async saveNumberingSettings() {
        try {
            showLoadingModal('Saving numbering settings...');
            const formData = new FormData(document.getElementById('numbering-form'));
            const data = await SetupAPI.saveNumberingSettings(formData);
            hideLoadingModal();
            if (data.success) {
                showResponseModal('Success', 'Numbering settings saved successfully', 'success');
            } else {
                showResponseModal('Error', data.message, 'error');
            }
        } catch (error) {
            hideLoadingModal();
            showResponseModal('Error', 'Failed to save numbering settings', 'error');
        }
    }

    // --- Add stubs for missing methods to prevent runtime errors ---
    async loadSalesTargets() {
        console.log('loadSalesTargets called (stub)');
        // TODO: Implement using existing controller/API
    }
    async loadSuppliers() {
        console.log('loadSuppliers called (stub)');
        // TODO: Implement using existing controller/API
    }
    async loadCreditPolicy() {
        console.log('loadCreditPolicy called (stub)');
        // TODO: Implement using existing controller/API
    }
    async loadCreditReasons() {
        console.log('loadCreditReasons called (stub)');
        // TODO: Implement using existing controller/API
    }
    async loadTermsSettings() {
        console.log('loadTermsSettings called (stub)');
        // TODO: Implement using existing controller/API
    }
}

function openCategoryModal(categoryId = null) {
    const modal = document.getElementById('categoryModal');
    const title = document.getElementById('categoryModalTitle');
    const form = document.getElementById('categoryForm');
    // Reset form
    form.reset();
    document.getElementById('category-id').value = '';
    // Populate product types from DB
    populateProductTypeDropdown();
    if (categoryId) {
        title.textContent = 'Edit Category';
        // Load category data for editing
        loadCategoryData(categoryId);
    } else {
        title.textContent = 'Add Category';
    }
    modal.style.display = 'block';
}
window.openCategoryModal = openCategoryModal;

function closeCategoryModal() {
    const modal = document.getElementById('categoryModal');
    modal.style.display = 'none';
}
window.closeCategoryModal = closeCategoryModal;

function openSubcategoryModal(subcategoryId = null) {
    const modal = document.getElementById('subcategoryModal');
    const title = document.getElementById('subcategoryModalTitle');
    const form = document.getElementById('subcategoryForm');
    // Reset form
    form.reset();
    document.getElementById('subcategory-id').value = '';
    // Populate categories from DB
    populateSubcategoryCategoryDropdown();
    if (subcategoryId) {
        title.textContent = 'Edit Subcategory';
        // Load subcategory data for editing
        loadSubcategoryData(subcategoryId);
    } else {
        title.textContent = 'Add Subcategory';
    }
    modal.style.display = 'block';
}
window.openSubcategoryModal = openSubcategoryModal;

function closeSubcategoryModal() {
    const modal = document.getElementById('subcategoryModal');
    modal.style.display = 'none';
}
window.closeSubcategoryModal = closeSubcategoryModal;

// --- SUPPLIER MODAL LOGIC ---
function openSupplierModal(supplierId = null) {
    const modal = document.getElementById('supplierModal');
    const title = document.getElementById('supplierModalTitle');
    const form = document.getElementById('supplierForm');
    // Reset form
    form.reset();
    document.getElementById('supplier-id').value = '';
    document.getElementById('supplier-email').value = '';
    if (supplierId) {
        title.textContent = 'Edit Supplier';
        loadSupplierData(supplierId);
    } else {
        title.textContent = 'Add Supplier';
        showSupplierContactsSection(null);
    }
    modal.style.display = 'block';
}
window.openSupplierModal = openSupplierModal;

function closeSupplierModal() {
    const modal = document.getElementById('supplierModal');
    modal.style.display = 'none';
}
window.closeSupplierModal = closeSupplierModal;

async function loadSupplierData(supplierId) {
    const data = await SetupAPI.getSupplier(supplierId);
    if (data.success && data.data) {
        document.getElementById('supplier-id').value = data.data.supplier_id;
        document.getElementById('supplier-name').value = data.data.supplier_name || '';
        document.getElementById('supplier-email').value = data.data.supplier_email || '';
        document.getElementById('supplier-contact').value = data.data.supplier_contact || '';
        document.getElementById('supplier-address').value = data.data.supplier_address || '';
        document.getElementById('supplier-website').value = data.data.website_url || '';
        showSupplierContactsSection(data.data.supplier_id);
    } else {
        showSupplierContactsSection(null);
    }
}
window.loadSupplierData = loadSupplierData;

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
                loadSuppliersList();
            } else {
                showResponseModal('Error', data.message, 'error');
            }
        } catch (error) {
            hideLoadingModal();
            showResponseModal('Error', 'Failed to save supplier', 'error');
        }
    });
}

async function loadSuppliersList() {
    const container = document.getElementById('suppliers-list');
    if (!container) return;
    const data = await SetupAPI.getSuppliers();
    if (!data.success || !Array.isArray(data.data) || data.data.length === 0) {
        container.innerHTML = `<div class="empty-state"><span class="material-icons">local_shipping</span><h3>No Suppliers</h3><p>Add your first supplier to get started</p></div>`;
        return;
    }
    container.innerHTML = data.data.map(supplier => `
        <div class="data-item supplier-item" data-id="${supplier.supplier_id}">
            <div class="data-item-info">
                <div class="data-item-title">${supplier.supplier_name}</div>
                <div class="data-item-subtitle">Email: ${supplier.supplier_email || ''}</div>
                <div class="data-item-subtitle">Contact: ${supplier.supplier_contact || ''}</div>
                ${supplier.supplier_address ? `<div class="data-item-description">${supplier.supplier_address}</div>` : ''}
                ${supplier.website_url ? `<div class="data-item-description">Website: <a href="${supplier.website_url}" target="_blank">${supplier.website_url}</a></div>` : ''}
            </div>
        </div>
    `).join('');
    // Add double-click event listeners for edit
    container.querySelectorAll('.supplier-item').forEach(item => {
        item.addEventListener('dblclick', () => {
            const id = item.getAttribute('data-id');
            openSupplierModal(id);
        });
    });
}
window.loadSuppliersList = loadSuppliersList;

// Load suppliers list on tab switch
const suppliersTab = document.getElementById('suppliers');
if (suppliersTab) {
    suppliersTab.addEventListener('show', loadSuppliersList);
}

// --- CLEAN PRODUCT SETUP LOGIC ---

document.addEventListener('DOMContentLoaded', () => {
    // Populate filters on load
    Promise.all([
        populateCategoryTypeFilter(),
        populateSubcategoryTypeFilter(),
        populateSubcategoryCategoryFilter()
    ]).then(() => {
        loadProductSetupData(); // Ensure lists are initialized after filters
    });

    // Add event listeners
    document.getElementById('category-type-filter').addEventListener('change', applyCategoryFilter);
    document.getElementById('subcategory-type-filter').addEventListener('change', applySubcategoryFilter);
    document.getElementById('subcategory-category-filter').addEventListener('change', applySubcategoryFilter);

    // Load suppliers list on page load
    loadSuppliersList();
});

let allCategories = [];
let allSubcategories = [];
let allTypes = [];

async function populateCategoryTypeFilter() {
    const select = document.getElementById('category-type-filter');
    if (!select) return;
    select.innerHTML = '<option value="">All Types</option>';
    const data = await ProductAPI.fetchProductTypes();
    if (data.success && Array.isArray(data.data)) {
        data.data.forEach(type => {
            const option = document.createElement('option');
            option.value = type.product_type_id;
            option.textContent = type.product_type_name;
            select.appendChild(option);
        });
    }
}

async function populateSubcategoryTypeFilter() {
    const select = document.getElementById('subcategory-type-filter');
    if (!select) return;
    select.innerHTML = '<option value="">All Types</option>';
    const data = await ProductAPI.fetchProductTypes();
        if (data.success && Array.isArray(data.data)) {
        data.data.forEach(type => {
            const option = document.createElement('option');
            option.value = type.product_type_id;
            option.textContent = type.product_type_name;
            select.appendChild(option);
        });
    }
}

async function populateSubcategoryCategoryFilter() {
    const select = document.getElementById('subcategory-category-filter');
    if (!select) return;
    const typeId = document.getElementById('subcategory-type-filter').value;
    select.innerHTML = '<option value="">All Categories</option>';
    let categories = allCategories;
    if (typeId) {
        categories = categories.filter(cat => String(cat.product_type_id) === String(typeId));
    }
    categories.forEach(category => {
                const option = document.createElement('option');
                option.value = category.category_id;
                option.textContent = category.category_name;
                select.appendChild(option);
            });
        }

function applyCategoryFilter() {
    const typeId = document.getElementById('category-type-filter').value;
    let filtered = allCategories.slice();
    if (typeId) {
        filtered = filtered.filter(cat => String(cat.product_type_id) === String(typeId));
    }
    renderProductCategories(filtered);
}

function applySubcategoryFilter() {
    const typeId = document.getElementById('subcategory-type-filter').value;
    const categoryId = document.getElementById('subcategory-category-filter').value;
    let filtered = allSubcategories.slice();
    if (typeId) {
        // Filter by type via category
        const categoryIds = allCategories.filter(cat => String(cat.product_type_id) === String(typeId)).map(cat => cat.category_id);
        filtered = filtered.filter(sub => categoryIds.includes(sub.category_id));
    }
    if (categoryId) {
        filtered = filtered.filter(sub => String(sub.category_id) === String(categoryId));
    }
    renderProductSubcategories(filtered);
}

async function loadProductSetupData() {
    // Fetch all data in parallel
    const [typesRes, categoriesRes, subcategoriesRes] = await Promise.all([
        ProductAPI.fetchProductTypes(),
        SetupAPI.getCategories(),
        SetupAPI.getSubcategories()
    ]);
    const types = typesRes.success ? typesRes.data : [];
    const categories = categoriesRes.success ? categoriesRes.data : [];
    const subcategories = subcategoriesRes.success ? subcategoriesRes.data : [];

    allCategories = categories;
    allSubcategories = subcategories;
    allTypes = types;

    renderProductCategories(categories);
    renderProductSubcategories(subcategories);
}

function renderProductCategories(categories) {
    const container = document.getElementById('product-categories-list');
    if (!container) return;
    if (!categories || categories.length === 0) {
        container.innerHTML = `<div class="empty-state"><span class="material-icons">category</span><h3>No Categories</h3><p>Add your first category to get started</p></div>`;
        return;
    }
    container.innerHTML = categories.map(category => `
        <div class="data-item category-item" data-id="${category.category_id}">
            <div class="data-item-info">
                <div class="data-item-title">${category.category_name}</div>
                <div class="data-item-subtitle">Type: ${category.product_type_name || 'Unknown'}</div>
                ${category.category_description ? `<div class="data-item-description">${category.category_description}</div>` : ''}
            </div>
        </div>
    `).join('');
    // Add double-click event listeners for edit
    container.querySelectorAll('.category-item').forEach(item => {
        item.addEventListener('dblclick', () => {
            const id = item.getAttribute('data-id');
            openCategoryModal(id);
        });
    });
}

function renderProductSubcategories(subcategories) {
    console.log('DEBUG: subcategories for main view', subcategories);
    const container = document.getElementById('product-subcategories-list');
    if (!container) return;
    if (!subcategories || subcategories.length === 0) {
        container.innerHTML = `<div class="empty-state"><span class="material-icons">subdirectory_arrow_right</span><h3>No Subcategories</h3><p>Add your first subcategory to get started</p></div>`;
        return;
    }
    container.innerHTML = subcategories.map(subcategory => `
        <div class="data-item subcategory-item" data-id="${subcategory.subcategory_id}">
            <div class="data-item-info">
                <div class="data-item-title">${subcategory.subcategory_name}</div>
                <div class="data-item-subtitle">Category: ${subcategory.category_name || 'Unknown'}</div>
                ${subcategory.subcategory_description ? `<div class="data-item-description">${subcategory.subcategory_description}</div>` : ''}
            </div>
        </div>
    `).join('');
    // Add double-click event listeners for edit
    container.querySelectorAll('.subcategory-item').forEach(item => {
        item.addEventListener('dblclick', () => {
            const id = item.getAttribute('data-id');
            openSubcategoryModal(id);
        });
    });
}

// Initialize when DOM is loaded
// Ensure invoiceSetup is globally available for sidebar onclick
window.invoiceSetup = new InvoiceSetup();

// --- Invoice Template Preference Logic ---

document.addEventListener('DOMContentLoaded', function() {
    const templateForm = document.getElementById('template-form');
    if (templateForm) {
        // Fetch current preference
        fetch('../api/invoice_template_pref.php?action=get')
            .then(res => res.json())
            .then(data => {
                if (data.success && data.template) {
                    const radio = templateForm.querySelector(`input[name="template"][value="${data.template}"]`);
                    if (radio) radio.checked = true;
                }
            });

        // Save preference
        templateForm.onsubmit = async function(e) {
            e.preventDefault();
            const selected = templateForm.querySelector('input[name="template"]:checked');
            if (!selected) {
                showResponseModal('Please select a template.','error');
                return;
            }
            try {
                const res = await fetch('../api/invoice_template_pref.php?action=save', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ template: selected.value })
                });
                const result = await res.json();
                if (result.success) {
                    showResponseModal('Template preference saved!','success');
                } else {
                    showResponseModal(result.message || 'Failed to save template.','error');
                }
            } catch (err) {
                showResponseModal('Error saving template: ' + (err.message || err),'error');
            }
        };
    }
}); 

function populateSubcategoryCategoryDropdown() {
    const select = document.getElementById('subcategory-category');
    if (!select) return;
    select.innerHTML = '<option value="">Select Category</option>';
    SetupAPI.getCategories().then(data => {
        if (data.success && Array.isArray(data.data)) {
            data.data.forEach(category => {
                const option = document.createElement('option');
                option.value = category.category_id;
                option.textContent = category.category_name;
                select.appendChild(option);
            });
        }
    });
}
window.populateSubcategoryCategoryDropdown = populateSubcategoryCategoryDropdown;

async function loadCategoryData(categoryId) {
    await new Promise(resolve => {
        populateProductTypeDropdown();
        setTimeout(resolve, 100); // Wait for dropdown to populate
    });
    const data = await SetupAPI.getCategories();
    if (data.success && Array.isArray(data.data)) {
        const cat = data.data.find(c => c.category_id == categoryId);
        if (cat) {
            document.getElementById('category-id').value = cat.category_id;
            document.getElementById('category-name').value = cat.category_name;
            document.getElementById('category-description').value = cat.category_description || '';
            const typeSelect = document.getElementById('category-type');
            if (typeSelect) typeSelect.value = cat.product_type_id;
        }
    }
}
window.loadCategoryData = loadCategoryData;

async function loadSubcategoryData(subcategoryId) {
    await new Promise(resolve => {
        populateSubcategoryCategoryDropdown();
        setTimeout(resolve, 100); // Wait for dropdown to populate
    });
    const data = await SetupAPI.getSubcategories();
    if (data.success && Array.isArray(data.data)) {
        const sub = data.data.find(s => s.subcategory_id == subcategoryId);
        if (sub) {
            document.getElementById('subcategory-id').value = sub.subcategory_id;
            document.getElementById('subcategory-name').value = sub.subcategory_name;
            document.getElementById('subcategory-description').value = sub.subcategory_description || '';
            const catSelect = document.getElementById('subcategory-category');
            if (catSelect) catSelect.value = sub.category_id;
        }
    }
}
window.loadSubcategoryData = loadSubcategoryData;

function populateProductTypeDropdown() {
    const select = document.getElementById('category-type');
    if (!select) return;
    select.innerHTML = '<option value="">Select Product Type</option>';
    ProductAPI.fetchProductTypes().then(data => {
        if (data.success && Array.isArray(data.data)) {
            data.data.forEach(type => {
                const option = document.createElement('option');
                option.value = type.product_type_id;
                option.textContent = type.product_type_name;
                select.appendChild(option);
            });
        }
    });
}
window.populateProductTypeDropdown = populateProductTypeDropdown;

// Update event listener for subcategory-type-filter to repopulate category filter
const subcategoryTypeFilter = document.getElementById('subcategory-type-filter');
if (subcategoryTypeFilter) {
    subcategoryTypeFilter.addEventListener('change', () => {
        populateSubcategoryCategoryFilter().then(applySubcategoryFilter);
    });
}

const categoryForm = document.getElementById('categoryForm');
if (categoryForm) {
    categoryForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        try {
            showLoadingModal('Saving category...');
            const formData = new FormData(categoryForm);
            const data = await SetupAPI.saveCategory(formData);
            hideLoadingModal();
            if (data.success) {
                showResponseModal('Success', 'Category saved successfully', 'success');
                closeCategoryModal();
                loadProductSetupData();
            } else {
                showResponseModal('Error', data.message, 'error');
            }
        } catch (error) {
            hideLoadingModal();
            showResponseModal('Error', 'Failed to save category', 'error');
        }
    });
}

const subcategoryForm = document.getElementById('subcategoryForm');
if (subcategoryForm) {
    subcategoryForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        try {
            showLoadingModal('Saving subcategory...');
            const formData = new FormData(subcategoryForm);
            const data = await SetupAPI.saveSubcategory(formData);
            hideLoadingModal();
            if (data.success) {
                showResponseModal('Success', 'Subcategory saved successfully', 'success');
                closeSubcategoryModal();
                loadProductSetupData();
            } else {
                showResponseModal('Error', data.message, 'error');
            }
        } catch (error) {
            hideLoadingModal();
            showResponseModal('Error', 'Failed to save subcategory', 'error');
        }
    });
}

function previewInvoiceTemplate(template) {
    showLoadingModal('Generating preview...');
    fetch('../api/generate_invoice_pdf.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        credentials: 'include',
        body: JSON.stringify({ template, preview: 1 })
    })
    .then(res => res.json())
    .then(result => {
        hideLoadingModal();
        console.log('Preview result:', result);
        if (result.success && result.url) {
            window.open(result.url, '_blank');
        } else {
            showResponseModal(result.message || 'Failed to generate preview', 'error');
        }
    })
    .catch(err => {
        hideLoadingModal();
        showResponseModal('Error generating preview: ' + (err.message || err), 'error');
    });
}

// --- SUPPLIER CONTACT PERSON LOGIC ---
function showSupplierContactsSection(supplierId) {
    const section = document.getElementById('supplier-contacts-section');
    if (!section) return;
    if (supplierId) {
        section.style.display = '';
        document.getElementById('contact-supplier-id').value = supplierId;
        loadSupplierContactsList(supplierId);
    } else {
        section.style.display = 'none';
    }
}

async function loadSupplierContactsList(supplierId) {
    const container = document.getElementById('supplier-contacts-list');
    if (!container) return;
    container.innerHTML = '<div class="loading">Loading contacts...</div>';
    const data = await SetupAPI.getSupplierContacts(supplierId);
    if (!data.success || !Array.isArray(data.data) || data.data.length === 0) {
        container.innerHTML = `<div class="empty-state"><span class="material-icons">person_off</span><h4>No Contacts</h4><p>Add a contact person for this supplier</p></div>`;
        return;
    }
    container.innerHTML = data.data.map(contact => `
        <div class="data-item contact-item" data-id="${contact.contact_person_id}">
            <div class="data-item-info">
                <div class="data-item-title">${contact.full_name}</div>
                <div class="data-item-subtitle">${contact.position || ''}</div>
                <div class="data-item-description">${contact.email ? 'Email: ' + contact.email + '<br>' : ''}${contact.phone ? 'Phone: ' + contact.phone : ''}</div>
                ${contact.notes ? `<div class="data-item-description">${contact.notes}</div>` : ''}
            </div>
            <div class="data-item-actions">
                <button class="btn-secondary" type="button" onclick="editSupplierContact(${contact.contact_person_id}, ${contact.supplier_id})"><span class="material-icons">edit</span></button>
                <button class="btn-danger" type="button" onclick="deleteSupplierContact(${contact.contact_person_id}, ${contact.supplier_id})"><span class="material-icons">delete</span></button>
            </div>
        </div>
    `).join('');
}
window.loadSupplierContactsList = loadSupplierContactsList;

function openSupplierContactModal(supplierId = null) {
    const modal = document.getElementById('supplierContactModal');
    const title = document.getElementById('supplierContactModalTitle');
    const form = document.getElementById('supplierContactForm');
    form.reset();
    document.getElementById('contact-person-id').value = '';
    if (supplierId) {
        document.getElementById('contact-supplier-id').value = supplierId;
    }
    title.textContent = 'Add Contact';
    modal.style.display = 'block';
}
window.openSupplierContactModal = openSupplierContactModal;

function closeSupplierContactModal() {
    const modal = document.getElementById('supplierContactModal');
    modal.style.display = 'none';
}
window.closeSupplierContactModal = closeSupplierContactModal;

async function editSupplierContact(contactId, supplierId) {
    const data = await SetupAPI.getSupplierContacts(supplierId);
    if (data.success && Array.isArray(data.data)) {
        const contact = data.data.find(c => c.contact_person_id == contactId);
        if (contact) {
            const form = document.getElementById('supplierContactForm');
            form.reset();
            document.getElementById('contact-person-id').value = contact.contact_person_id;
            document.getElementById('contact-supplier-id').value = contact.supplier_id;
            document.getElementById('contact-full-name').value = contact.full_name || '';
            document.getElementById('contact-position').value = contact.position || '';
            document.getElementById('contact-email').value = contact.email || '';
            document.getElementById('contact-phone').value = contact.phone || '';
            document.getElementById('contact-notes').value = contact.notes || '';
            document.getElementById('supplierContactModalTitle').textContent = 'Edit Contact';
            document.getElementById('supplierContactModal').style.display = 'block';
        }
    }
}
window.editSupplierContact = editSupplierContact;

async function deleteSupplierContact(contactId, supplierId) {
    if (!confirm('Are you sure you want to delete this contact?')) return;
    showLoadingModal('Deleting contact...');
    const formData = new FormData();
    formData.append('contact_person_id', contactId);
    const data = await SetupAPI.deleteSupplierContact(formData);
    hideLoadingModal();
    if (data.success) {
        showResponseModal('Success', 'Contact deleted', 'success');
        loadSupplierContactsList(supplierId);
    } else {
        showResponseModal('Error', data.message, 'error');
    }
}
window.deleteSupplierContact = deleteSupplierContact;

const supplierContactForm = document.getElementById('supplierContactForm');
if (supplierContactForm) {
    supplierContactForm.addEventListener('submit', async function(e) {
        e.preventDefault();
        try {
            showLoadingModal('Saving contact...');
            const formData = new FormData(supplierContactForm);
            let data;
            if (formData.get('contact_person_id')) {
                data = await SetupAPI.updateSupplierContact(formData);
            } else {
                data = await SetupAPI.addSupplierContact(formData);
            }
            hideLoadingModal();
            if (data.success) {
                showResponseModal('Success', 'Contact saved', 'success');
                closeSupplierContactModal();
                loadSupplierContactsList(formData.get('supplier_id'));
            } else {
                showResponseModal('Error', data.message, 'error');
            }
        } catch (error) {
            hideLoadingModal();
            showResponseModal('Error', 'Failed to save contact', 'error');
        }
    });
}

// --- Credit Reasons List Logic ---
const creditReasonsList = document.getElementById('creditReasonsList');
const addCreditReasonBtn = document.getElementById('addCreditReasonBtn');

async function loadCreditReasons() {
    if (!creditReasonsList) return;
    showLoadingModal('Loading credit reasons...');
    const res = await SetupAPI.getCreditReasons();
    hideLoadingModal();
    creditReasonsList.innerHTML = '';
    if (!res.success || !Array.isArray(res.data)) {
        showResponseModal('Failed to load credit reasons', 'error');
        return;
    }
    res.data.forEach(reason => {
        const row = document.createElement('div');
        row.className = 'credit-reason-row';
        row.innerHTML = `
            <span class="credit-reason-text">${reason.reason}</span>
            <button class="edit-credit-reason-btn" data-id="${reason.credit_reason_id}">Edit</button>
            <button class="delete-credit-reason-btn" data-id="${reason.credit_reason_id}">Delete</button>
        `;
        creditReasonsList.appendChild(row);
    });
    // Attach handlers
    creditReasonsList.querySelectorAll('.edit-credit-reason-btn').forEach(btn => {
        btn.addEventListener('click', e => {
            const id = btn.getAttribute('data-id');
            const reason = res.data.find(r => r.credit_reason_id == id);
            openCreditReasonModal('edit', reason);
        });
    });
    creditReasonsList.querySelectorAll('.delete-credit-reason-btn').forEach(btn => {
        btn.addEventListener('click', async e => {
            const id = btn.getAttribute('data-id');
            if (!confirm('Delete this credit reason?')) return;
            showLoadingModal('Deleting...');
            const delRes = await SetupAPI.deleteCreditReason(id);
            hideLoadingModal();
            if (delRes.success) {
                showResponseModal('Credit reason deleted', 'success');
                loadCreditReasons();
            } else {
                showResponseModal(delRes.message || 'Delete failed', 'error');
            }
        });
    });
}

if (addCreditReasonBtn) {
    addCreditReasonBtn.addEventListener('click', () => openCreditReasonModal('add'));
}

// Expose for reload after add/edit
window.loadCreditReasons = loadCreditReasons;

// Initial load
if (creditReasonsList) loadCreditReasons();

// Tab switch logic (ensure this runs on tab change)
document.addEventListener('DOMContentLoaded', function() {
    const creditTab = document.getElementById('credit');
    if (creditTab) {
        creditTab.addEventListener('show', function() {
            loadCreditPolicyForm();
            if (window.loadCreditReasons) window.loadCreditReasons();
        });
    }
});