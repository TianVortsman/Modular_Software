/**
 * Invoice Setup JavaScript
 * Handles all setup functionality including tabs, forms, and API interactions
 */

import SetupAPI from './setup-api.js';

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
        const tabButtons = document.querySelectorAll('.tab-btn');
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                const targetTab = button.getAttribute('data-tab');
                this.switchTab(targetTab);
            });
        });
    }

    switchTab(tabName) {
        document.querySelectorAll('.tab-btn').forEach(btn => {
            btn.classList.remove('active');
        });
        document.querySelector(`[data-tab="${tabName}"]`).classList.add('active');

        document.querySelectorAll('.tab-panel').forEach(panel => {
            panel.classList.remove('active');
        });
        document.getElementById(tabName).classList.add('active');

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
});

let allCategories = [];
let allSubcategories = [];
let allTypes = [];

async function populateCategoryTypeFilter() {
    const select = document.getElementById('category-type-filter');
    if (!select) return;
    select.innerHTML = '<option value="">All Types</option>';
    const data = await SetupAPI.getProductTypes();
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
    const data = await SetupAPI.getProductTypes();
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
        SetupAPI.getProductTypes(),
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
document.addEventListener('DOMContentLoaded', function() {
    window.invoiceSetup = new InvoiceSetup();
});

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
    SetupAPI.getProductTypes().then(data => {
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