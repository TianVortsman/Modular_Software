console.log('Loading product-modals.js');
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
        this.lastSuppliersForStock = [];
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
        // Stock Adjustment Modal logic
        const openAdjustBtn = document.getElementById('openAdjustStockModalBtn');
        const adjustModal = document.getElementById('adjustStockModal');
        const closeAdjustBtn = document.getElementById('closeAdjustStockModalBtn');
        const cancelAdjustBtn = document.getElementById('cancelAdjustStockBtn');
        const adjustForm = document.getElementById('adjustStockForm');
        if (openAdjustBtn && adjustModal) {
            openAdjustBtn.addEventListener('click', () => {
                this.openAdjustStockModal();
            });
        }
        if (closeAdjustBtn && adjustModal) {
            closeAdjustBtn.addEventListener('click', () => {
                adjustModal.style.display = 'none';
            });
        }
        if (cancelAdjustBtn && adjustModal) {
            cancelAdjustBtn.addEventListener('click', () => {
                adjustModal.style.display = 'none';
            });
        }
        if (adjustForm) {
            adjustForm.addEventListener('submit', (e) => this.handleAdjustStockSubmit(e));
        }
    }
    async openModal(mode = 'add', productId = null, typeId = null) {
        console.log ('Opening product modal');
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
                    // --- Populate Suppliers tab ---
                    this.populateSuppliersTab(productId);
                    // --- Populate Stock History tab ---
                    this.populateStockHistoryTab(productId);
                } else {
                    showResponseModal(result.message || 'Failed to fetch product details', 'error');
                    console.error('Failed to fetch product details:', result);
                }
            }).catch(error => {
                showResponseModal('Error fetching product details: ' + error.message, 'error');
                console.error('Error fetching product details:', error);
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
        // Explicitly build FormData with all mapped fields
        const fieldMappings = {
            'product_name': 'product_name',
            'product_description': 'product_descr',
            'product_price': 'product_price',
            'product_status': 'product_status',
            'sku': 'sku',
            'barcode': 'barcode',
            'product_type_id': 'product_type_id',
            'category_id': 'category_id',
            'subcategory_id': 'subcategory_id',
            'tax_rate_id': 'tax_rate_id',
            'discount': 'discount',
            'notes': 'notes',
            'product_stock_quantity': 'stock_quantity',
            'product_reorder_level': 'reorder_level',
            'product_lead_time': 'lead_time',
            'product_weight': 'product_weight',
            'product_dimensions': 'dimensions',
            'product_brand': 'brand',
            'product_manufacturer': 'manufacturer',
            'warranty_period': 'warranty_period',
            'product_material': 'product_material',
            'supplier_id': 'supplier_id',
            'compatible_vehicles': 'compatible_vehicles',
            'oem_part_number': 'oem_part_number',
            'estimated_time': 'estimated_time',
            'service_frequency': 'service_frequency',
            'bundle_items': 'bundle_items',
            'installation_required': 'installation_required',
            'labor_cost': 'labor_cost',
        };
        const formData = new FormData();
        Object.entries(fieldMappings).forEach(([apiField, formField]) => {
            const element = this.formManager.form.querySelector(`[name="${formField}"]`);
            if (element) {
                let value = element.value;
                // For checkboxes and selects
                if (element.type === 'checkbox') {
                    value = element.checked ? 'true' : 'false';
                }
                // For number fields, default to 0 if blank
                if ((element.type === 'number' || element.type === 'text') && value === '') {
                    if ([
                        'product_price', 'discount', 'labor_cost', 'product_stock_quantity',
                        'product_reorder_level', 'product_lead_time', 'product_weight'
                    ].includes(apiField)) {
                        value = '0';
                    }
                }
                formData.set(apiField, value);
            } else {
                // If field is missing in DOM, send empty string
                formData.set(apiField, '');
            }
        });
        // Add product_id if in edit mode
        if (this.mode === 'edit' && this.productId) {
            formData.set('product_id', this.productId);
        }
        // Add image if present
        const imageInput = this.formManager.imageInput;
        if (imageInput && imageInput.files && imageInput.files.length > 0) {
            formData.set('item_image', imageInput.files[0]);
        }
        const isEditMode = this.mode === 'edit';
        showLoadingModal(isEditMode ? 'Saving changes...' : 'Adding product...');
        let apiCall = isEditMode
            ? ProductAPI.editProduct(this.productId, formData)
            : ProductAPI.addProduct(formData);
        apiCall.then(async data => {
            hideLoadingModal();
            if (data.success) {
                // If image is selected, upload it (already handled above)
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
    async populateSuppliersTab(productId) {
        const container = this.modal.querySelector('#product-suppliers-list');
        if (!container) return;
        container.innerHTML = '<div class="loading">Loading suppliers...</div>';
        try {
            const data = await ProductAPI.fetchProductSuppliersAndStock(productId);
            if (!data.success || !Array.isArray(data.data) || data.data.length === 0) {
                container.innerHTML = '<div class="empty-state">No suppliers linked to this product.</div>';
                return;
            }
            container.innerHTML = '';
            const template = document.getElementById('product-supplier-card-template');
            data.data.forEach(supplier => {
                const card = template.content.cloneNode(true);
                // Modern card layout
                const nameSpan = card.querySelector('.supplier-name');
                nameSpan.textContent = supplier.supplier_name;
                // Contact
                const contactSpan = card.querySelector('.supplier-contact');
                contactSpan.innerHTML = supplier.supplier_contact ? `<span class="material-icons" style="font-size:1em;vertical-align:middle;">call</span> ${supplier.supplier_contact}` : '';
                // Email
                const emailSpan = card.querySelector('.supplier-email');
                emailSpan.innerHTML = supplier.supplier_email ? `<span class="material-icons" style="font-size:1em;vertical-align:middle;">email</span> <a href="mailto:${supplier.supplier_email}">${supplier.supplier_email}</a>` : '';
                // Website
                const websiteSpan = card.querySelector('.supplier-website');
                websiteSpan.innerHTML = supplier.website_url ? `<span class="material-icons" style="font-size:1em;vertical-align:middle;">public</span> <a href="${supplier.website_url}" target="_blank">${supplier.website_url}</a>` : '';
                // Stock info
                const totalStockSpan = card.querySelector('.supplier-total-stock');
                totalStockSpan.textContent = `Total Stock: ${supplier.total_stock}`;
                if (supplier.total_stock <= 2) totalStockSpan.classList.add('low-stock');
                card.querySelector('.supplier-last-restock-date').textContent = supplier.last_restock ? `Last Restock: ${supplier.last_restock}` : '';
                card.querySelector('.supplier-last-price').textContent = supplier.last_price ? `Last Price: R${supplier.last_price}` : '';
                // Mini FIFO table
                const trendDiv = card.querySelector('.supplier-price-trend');
                if (Array.isArray(supplier.fifo_entries) && supplier.fifo_entries.length > 0) {
                    const table = document.createElement('table');
                    table.className = 'fifo-table';
                    table.innerHTML = '<thead><tr><th>Qty</th><th>Rem</th><th>Price</th><th>Date</th></tr></thead>';
                    const tbody = document.createElement('tbody');
                    supplier.fifo_entries.forEach(entry => {
                        const tr = document.createElement('tr');
                        tr.innerHTML = `<td>${entry.quantity}</td><td>${entry.remaining_quantity}</td><td>R${entry.cost_per_unit}</td><td>${entry.received_at}</td>`;
                        tbody.appendChild(tr);
                    });
                    table.appendChild(tbody);
                    trendDiv.appendChild(table);
                } else {
                    trendDiv.textContent = 'No stock entries.';
                }
                container.appendChild(card);
            });
        } catch (e) {
            container.innerHTML = '<div class="error">Failed to load suppliers.</div>';
            console.error('Error loading suppliers for product:', e);
        }
    }
    async populateStockHistoryTab(productId) {
        const container = this.modal.querySelector('#product-stock-history-list');
        if (!container) return;
        container.innerHTML = '<div class="loading">Loading stock history...</div>';
        try {
            const data = await ProductAPI.fetchProductSuppliersAndStock(productId);
            if (!data.success || !Array.isArray(data.data)) {
                container.innerHTML = '<div class="error">Failed to load stock history.</div>';
                return;
            }
            // Flatten all fifo_entries with supplier name
            let allEntries = [];
            data.data.forEach(supplier => {
                if (Array.isArray(supplier.fifo_entries)) {
                    supplier.fifo_entries.forEach(entry => {
                        allEntries.push({
                            supplier_name: supplier.supplier_name,
                            quantity: entry.quantity,
                            remaining_quantity: entry.remaining_quantity,
                            cost_per_unit: entry.cost_per_unit,
                            received_at: entry.received_at,
                            notes: entry.notes || ''
                        });
                    });
                }
            });
            // Sort by received_at descending
            allEntries.sort((a, b) => new Date(b.received_at) - new Date(a.received_at));
            container.innerHTML = '';
            if (allEntries.length === 0) {
                container.innerHTML = '<div class="empty-state">No stock history for this product.</div>';
                return;
            }
            const template = document.getElementById('product-stock-history-row-template');
            // Header row
            const header = document.createElement('div');
            header.className = 'product-stock-history-row product-stock-history-header';
            header.innerHTML = '<span>Supplier</span><span>Qty</span><span>Rem</span><span>Cost</span><span>Date</span><span>Notes</span>';
            container.appendChild(header);
            allEntries.forEach(entry => {
                const row = template.content.cloneNode(true);
                row.querySelector('.stock-supplier-name').textContent = entry.supplier_name;
                row.querySelector('.stock-quantity').textContent = entry.quantity;
                row.querySelector('.stock-remaining').textContent = entry.remaining_quantity;
                row.querySelector('.stock-cost').textContent = `R${entry.cost_per_unit}`;
                row.querySelector('.stock-date').textContent = entry.received_at;
                row.querySelector('.stock-notes').textContent = entry.notes;
                container.appendChild(row);
            });
        } catch (e) {
            container.innerHTML = '<div class="error">Failed to load stock history.</div>';
            console.error('Error loading stock history for product:', e);
        }
    }
    async openAdjustStockModal() {
        const adjustModal = document.getElementById('adjustStockModal');
        const supplierSelect = document.getElementById('adjustStockSupplier');
        if (!adjustModal || !supplierSelect) return;
        // Fetch suppliers for this product
        if (!this.productId) return;
        supplierSelect.innerHTML = '<option value="">Loading...</option>';
        try {
            const data = await ProductAPI.fetchProductSuppliersAndStock(this.productId);
            if (data.success && Array.isArray(data.data) && data.data.length > 0) {
                supplierSelect.innerHTML = '';
                this.lastSuppliersForStock = data.data;
                data.data.forEach(supplier => {
                    const opt = document.createElement('option');
                    opt.value = supplier.supplier_id;
                    opt.textContent = supplier.supplier_name;
                    supplierSelect.appendChild(opt);
                });
            } else {
                supplierSelect.innerHTML = '<option value="">No suppliers</option>';
            }
        } catch (e) {
            supplierSelect.innerHTML = '<option value="">Error loading suppliers</option>';
        }
        adjustModal.style.display = 'block';
    }
    async handleAdjustStockSubmit(e) {
        e.preventDefault();
        const adjustModal = document.getElementById('adjustStockModal');
        const form = document.getElementById('adjustStockForm');
        if (!form || !this.productId) return;
        const supplier_id = form.supplier_id.value;
        const quantity = parseInt(form.quantity.value, 10);
        const cost_per_unit = form.cost_per_unit.value ? parseFloat(form.cost_per_unit.value) : null;
        const notes = form.notes.value;
        if (!supplier_id || isNaN(quantity) || quantity === 0) {
            showResponseModal('Please select a supplier and enter a non-zero quantity.', 'error');
            return;
        }
        // Find product_supplier_id
        let product_supplier_id = null;
        if (this.lastSuppliersForStock && Array.isArray(this.lastSuppliersForStock)) {
            const found = this.lastSuppliersForStock.find(s => String(s.supplier_id) === String(supplier_id));
            if (found && found.fifo_entries && found.fifo_entries[0] && found.fifo_entries[0].product_supplier_id) {
                product_supplier_id = found.fifo_entries[0].product_supplier_id;
            }
        }
        // If not found, fallback to API (to be implemented)
        if (!product_supplier_id) {
            showResponseModal('Could not resolve product-supplier link.', 'error');
            return;
        }
        // Send adjustment to API (endpoint to be implemented)
        try {
            const res = await fetch('/modules/invoice/api/products.php?action=adjust_stock', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                credentials: 'include',
                body: JSON.stringify({
                    product_supplier_id,
                    quantity,
                    cost_per_unit,
                    notes
                })
            });
            const result = await res.json();
            if (result.success) {
                showResponseModal('Stock adjustment successful.', 'success');
                adjustModal.style.display = 'none';
                this.populateStockHistoryTab(this.productId);
                this.populateSuppliersTab(this.productId);
            } else {
                showResponseModal(result.message || 'Failed to adjust stock.', 'error');
            }
        } catch (err) {
            showResponseModal('Error adjusting stock: ' + (err.message || err), 'error');
        }
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