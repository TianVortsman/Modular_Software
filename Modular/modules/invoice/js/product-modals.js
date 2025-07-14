import { ProductAPI } from './product-api.js';
window.ProductAPI = ProductAPI;

// --- ProductModalUI: Handles modal open/close, tab switching, and modal-specific UI logic ---
class ProductModalUI {
    constructor(modalElement) {
        this.modal = modalElement;
        this.formManager = new window.ProductModalForm(modalElement);
        this.mode = 'add';
        this.productId = null;
        // Map typeId to typeName for modal title
        this.typeIdNameMap = {
            1: 'Part',
            2: 'Service',
            3: 'Extra',
            4: 'Product'
        };
        this.initEventListeners();
    }
    initEventListeners() {
        const closeButton = this.modal?.querySelector('.universal-product-modal-close');
        if (closeButton) closeButton.addEventListener('click', () => this.closeModal());
        const cancelBtn = document.getElementById('universalProductCancelBtn');
        if (cancelBtn) cancelBtn.addEventListener('click', () => this.closeModal());
            this.modal.addEventListener('mousedown', (e) => {
            if (e.target === this.modal) this.closeModal();
            });
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && this.modal.classList.contains('active')) this.closeModal();
        });
        // Tab navigation
        const tabButtons = this.modal?.querySelectorAll('.upm-tab-btn');
        if (tabButtons) {
            tabButtons.forEach(button => {
                button.addEventListener('click', () => {
                    const tabId = button.dataset.tab;
                    this.showRelevantTabs(tabId);
                });
            });
        }
        // Delete button
        const deleteBtn = document.getElementById('universalProductDeleteBtn');
        if (deleteBtn) {
            deleteBtn.onclick = () => {
                if (this.productId) {
                    this.deleteProduct(this.productId);
                } else {
                    showResponseModal('No product selected for deletion.', 'error');
                }
            };
        }
        // Form submit
        if (this.formManager.form) {
            this.formManager.form.addEventListener('submit', (e) => this.handleSubmit(e));
        }
    }
    async openModal(mode = 'add', productId = null, typeId = null) {
        this.mode = mode;
        this.productId = productId;
        this.modal.dataset.mode = mode;
        this.modal.dataset.productId = productId;
        // Set modal title based on typeId
        const modalTitle = document.getElementById('universalProductModalTitle');
        if (!typeIdToNameMap[typeId]) await updateTypeIdToNameMap();
        let typeName = typeIdToNameMap[typeId] || 'Product';
        if (modalTitle) modalTitle.textContent = mode === 'add' ? `Add New ${typeName}` : `Edit ${typeName}`;
        this.formManager.resetForm();
        // Remove lingering product_id field in add mode
        if (mode === 'add' && this.formManager.form) {
            const pidFields = this.formManager.form.querySelectorAll('input[name="product_id"]');
            pidFields.forEach(f => f.remove());
        }
        // Always populate type, suppliers, and categories before showing modal
        await this.formManager.populateTypeDropdown(typeId);
        if (mode === 'add' && typeId) {
            // Set the type dropdown to the correct value and trigger change
            const typeDropdown = this.formManager.typeDropdown;
            if (typeDropdown) {
                typeDropdown.value = typeId;
                typeDropdown.dispatchEvent(new Event('change'));
            }
        }
        await this.formManager.populateSupplierDropdown();
        await this.formManager.populateCategoryDropdown(typeId);
        await this.formManager.populateSubcategoryDropdown();
        // Always populate tax rate dropdown before showing modal
        await this.formManager.populateTaxRateDropdown();
        if (mode === 'edit' && productId) {
            ProductAPI.fetchProductDetails(productId).then(async result => {
                if (result.success) {
                    // Populate dropdowns with correct values from product data
                    await this.formManager.populateTypeDropdown(result.data.product_type_id);
                    await this.formManager.populateSupplierDropdown(result.data.supplier_id);
                    await this.formManager.populateCategoryDropdown(result.data.product_type_id, result.data.category_id);
                    await this.formManager.populateSubcategoryDropdown(result.data.category_id, result.data.subcategory_id);
                    await this.formManager.populateTaxRateDropdown(result.data.tax_rate_id);
                    this.formManager.populateForm(result.data);
                } else {
                    showResponseModal(result.message || 'Failed to fetch product details', 'error');
                }
            });
        }
        this.modal.classList.add('active');
        this.showRelevantTabs();
    }
    closeModal() {
            this.modal.classList.remove('active');
        this.formManager.resetForm();
    }
    showRelevantTabs(tabId = 'basic') {
        const tabButtons = this.modal?.querySelectorAll('.upm-tab-btn');
        const tabPanes = this.modal?.querySelectorAll('.upm-tab-pane');
        if (tabButtons) tabButtons.forEach(btn => btn.classList.remove('upm-active'));
        if (tabPanes) tabPanes.forEach(pane => pane.classList.remove('upm-active'));
        const selectedTab = this.modal?.querySelector(`.upm-tab-btn[data-tab="${tabId}"]`);
        const selectedPane = this.modal?.querySelector(`#upm-tab-${tabId}`);
        if (selectedTab) selectedTab.classList.add('upm-active');
        if (selectedPane) selectedPane.classList.add('upm-active');
    }
    async handleSubmit(e) {
        e.preventDefault();
        e.stopPropagation();
        if (!this.formManager.validateForm()) {
            showResponseModal('Please fill in all required fields.', 'error');
            return;
        }
        // Remove lingering product_id field in add mode
        if (this.mode === 'add' && this.formManager.form) {
            const pidFields = this.formManager.form.querySelectorAll('input[name="product_id"]');
            pidFields.forEach(f => f.remove());
        }
        // Collect all required fields for backend
        const formData = new FormData(this.formManager.form);
        // Ensure supplier_id is included if present
        const supplierDropdown = this.formManager.supplierDropdown;
        if (supplierDropdown && supplierDropdown.value) {
            formData.set('supplier_id', supplierDropdown.value);
        }
        const isEditMode = this.mode === 'edit';
        showLoadingModal(isEditMode ? 'Saving changes...' : 'Adding product...');
        let apiCall = isEditMode
            ? ProductAPI.editProduct(this.productId, formData)
            : ProductAPI.addProduct(formData);
        apiCall.then(async data => {
            hideLoadingModal();
            if (data.success) {
                // If image is selected, upload it
                const imageInput = this.formManager.imageInput;
                if (imageInput && imageInput.files && imageInput.files.length > 0) {
                    const file = imageInput.files[0];
                    let uploadType = null;
                    if (this.formManager && this.formManager.typeDropdown) {
                        uploadType = this.formManager.typeDropdown.options[this.formManager.typeDropdown.selectedIndex]?.textContent?.toLowerCase() || 'product';
                    } else if (data.product_type_name) {
                        uploadType = data.product_type_name.toLowerCase();
                    } else {
                        uploadType = 'product';
                    }
                    const uploadProductId = data.data && data.data.product_id ? data.data.product_id : (data.product_id || this.productId);
                    await ProductAPI.uploadImage(file, uploadProductId, uploadType);
                    const preview = document.getElementById('universalItemImagePreview');
                    if (preview) {
                        const url = preview.src.split('?')[0];
                        preview.src = url + '?t=' + Date.now();
                    }
                }
                this.closeModal();
                showResponseModal('Product saved successfully', 'success', true);
                if (window.productScreenManager && typeof window.productScreenManager.refreshProductList === 'function') {
                    window.productScreenManager.refreshProductList();
                }
            } else {
                showResponseModal(data.message || 'Failed to save product', 'error', true);
            }
        }).catch(error => {
            hideLoadingModal();
            showResponseModal(error.message || 'Failed to save product', 'error', true);
            console.error('Error saving product:', error);
        });
    }
    async deleteProduct(productId) {
        const confirmed = await this.confirmWithResponseModal('Do you really want to delete this product?');
        if (!confirmed) {
            showResponseModal('Product deletion cancelled.', 'info');
            return;
        }
        showLoadingModal('Deleting product...');
        ProductAPI.deleteProduct(productId).then(result => {
            hideLoadingModal();
            if (!result.success) {
                this.closeModal();
                showResponseModal(result.message || 'Failed to delete product', 'error');
            } else {
                this.closeModal();
                showResponseModal('Product deleted successfully', 'success');
                if (window.productScreenManager && typeof window.productScreenManager.refreshProductList === 'function') {
                    window.productScreenManager.refreshProductList();
                }
            }
        }).catch(error => {
            hideLoadingModal();
            this.closeModal();
            showResponseModal('Failed to delete product: ' + error.message, 'error');
            console.error('Error deleting product:', error);
        });
    }
    confirmWithResponseModal(message) {
    return new Promise((resolve) => {
        const modal = document.getElementById('modalResponse');
        const title = document.getElementById('modalResponseTitle');
        const msg = document.getElementById('modalResponseMessage');
        const icon = document.getElementById('modalResponseIcon');
        const closeBtn = document.querySelector('.custom-modal-close-btn');
        if (modal && title && msg && icon && closeBtn) {
            title.textContent = 'Are you sure?';
            msg.textContent = message;
            icon.textContent = '⚠';
            modal.classList.remove('hidden');
            closeBtn.onclick = null;
            let confirmBtn = document.getElementById('modalResponseConfirmBtn');
            let cancelBtn = document.getElementById('modalResponseCancelBtn');
            if (confirmBtn) confirmBtn.remove();
            if (cancelBtn) cancelBtn.remove();
            closeBtn.style.display = 'none';
            confirmBtn = document.createElement('button');
            confirmBtn.id = 'modalResponseConfirmBtn';
            confirmBtn.textContent = 'Yes';
            confirmBtn.className = 'custom-modal-close-btn';
            confirmBtn.style.marginRight = '16px';
            cancelBtn = document.createElement('button');
            cancelBtn.id = 'modalResponseCancelBtn';
            cancelBtn.textContent = 'No';
            cancelBtn.className = 'custom-modal-close-btn';
            const footer = modal.querySelector('.custom-modal-footer');
            footer.appendChild(confirmBtn);
            footer.appendChild(cancelBtn);
            confirmBtn.onclick = () => {
                modal.classList.add('hidden');
                confirmBtn.remove();
                cancelBtn.remove();
                closeBtn.style.display = '';
                resolve(true);
            };
            cancelBtn.onclick = () => {
                modal.classList.add('hidden');
                confirmBtn.remove();
                cancelBtn.remove();
                closeBtn.style.display = '';
                showResponseModal('Product deletion cancelled.', 'info');
                resolve(false);
            };
        } else {
            resolve(window.confirm(message));
            }
        });
    }
}

// Initialize modal on DOMContentLoaded

document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('universalProductModal');
    if (modal) {
        window.productModalUI = new ProductModalUI(modal);
    }
});

// Export for use in other files
window.ProductModalUI = ProductModalUI;

// --- Drag-and-drop image upload for product cards ---
document.addEventListener('DOMContentLoaded', () => {
    // Drag-and-drop on product cards
    document.querySelectorAll('.product-card').forEach(card => {
        card.addEventListener('dragover', e => {
            e.preventDefault();
            card.classList.add('drag-over');
        });
        card.addEventListener('dragleave', e => {
            card.classList.remove('drag-over');
        });
        card.addEventListener('drop', e => {
            e.preventDefault();
            card.classList.remove('drag-over');
            const files = e.dataTransfer.files;
            if (files && files.length > 0) {
                const productId = card.getAttribute('data-product-id') || card.dataset.productId;
                if (window.productModalUI && productId) {
                    window.productModalUI.openModal('edit', productId);
                    setTimeout(() => {
                        const imageInput = document.getElementById('universalItemImage');
                        if (imageInput) {
                            const file = files[0];
                            const dt = new DataTransfer();
                            dt.items.add(file);
                            imageInput.files = dt.files;
                            // Optionally, trigger preview update
                            const preview = document.getElementById('universalItemImagePreview');
                            if (preview) {
                                const reader = new FileReader();
                                reader.onload = ev => { preview.src = ev.target.result; };
                                reader.readAsDataURL(file);
                            }
                        }
                    }, 500);
                }
            }
        });
    });
    // Drag-and-drop and click-to-upload for modal image
    const preview = document.getElementById('universalItemImagePreview');
    const imageInput = document.getElementById('universalItemImage');
    if (preview && imageInput) {
        // Click to open file dialog
        preview.style.cursor = 'pointer';
        preview.addEventListener('click', () => {
            imageInput.click();
        });
        // Drag-and-drop
        preview.addEventListener('dragover', e => {
            e.preventDefault();
            preview.classList.add('drag-over');
        });
        preview.addEventListener('dragleave', e => {
            preview.classList.remove('drag-over');
        });
        preview.addEventListener('drop', e => {
            e.preventDefault();
            preview.classList.remove('drag-over');
            const files = e.dataTransfer.files;
            if (files && files.length > 0) {
                const file = files[0];
                const dt = new DataTransfer();
                dt.items.add(file);
                imageInput.files = dt.files;
                // Update preview
                const reader = new FileReader();
                reader.onload = ev => { preview.src = ev.target.result; };
                reader.readAsDataURL(file);
            }
        });
        // File input change updates preview
        imageInput.addEventListener('change', e => {
            if (imageInput.files && imageInput.files.length > 0) {
                const file = imageInput.files[0];
                const reader = new FileReader();
                reader.onload = ev => { preview.src = ev.target.result; };
                reader.readAsDataURL(file);
            }
        });
    }
});

// --- Dynamic typeId to typeName mapping for modal titles ---
let typeIdToNameMap = {};
async function updateTypeIdToNameMap() {
    const typeRes = await ProductAPI.fetchProductTypes();
    if (typeRes.success && Array.isArray(typeRes.data)) {
        typeRes.data.forEach(type => {
            typeIdToNameMap[type.product_type_id] = type.product_type_name;
        });
    }
}