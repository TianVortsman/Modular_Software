// Universal Product Modal Handler
class ProductModal {
    constructor() {
        // Initialize modal elements
        this.modal = document.getElementById('universalProductModal');
        this.modalTitle = document.getElementById('universalProductModalTitle');
        this.form = document.getElementById('universalProductForm');
        this.closeButton = this.modal?.querySelector('.universal-product-modal-close');
        this.imagePreview = document.getElementById('universalItemImagePreview');
        this.imageInput = document.getElementById('universalItemImage');
        this.dropzone = document.getElementById('universalImageDropzone');
        this.typeDropdown = document.getElementById('universalItemType');
        this.categoryDropdown = document.getElementById('universalItemCategory');
        this.subcategoryDropdown = document.getElementById('universalItemSubcategory');
        this.lastTypeName = null; // Store for autofill

        // Detailed error logging for missing elements
        if (!this.modal) {
            console.error('ProductModal init error: #universalProductModal not found in DOM');
            throw new Error('Required modal elements not found');
        }
        if (!this.modalTitle) {
            console.error('ProductModal init error: #universalProductModalTitle not found in DOM');
            throw new Error('Required modal elements not found');
        }
        if (!this.form) {
            console.error('ProductModal init error: #universalProductForm not found in DOM');
            throw new Error('Required modal elements not found');
        }

        // Bind methods to preserve 'this' context
        this.handleSubmit = this.handleSubmit.bind(this);
        this.closeModal = this.closeModal.bind(this);
        this.showRelevantTabs = this.showRelevantTabs.bind(this);
        this.handleAddImageUpload = this.handleAddImageUpload.bind(this);
        this.handleEditImageUpload = this.handleEditImageUpload.bind(this);

        // Initialize event listeners
        this.initializeEventListeners();
    }

    handleAddImageUpload(file) {
        this.uploadImage(file, 'add');
    }
    
    handleEditImageUpload(file) {
        this.uploadImage(file, 'edit');
    }

    initializeEventListeners() {
        // Form submission
        if (this.form) {
            this.form.addEventListener('submit', this.handleSubmit);
        }

        // Close button
        if (this.closeButton) {
            this.closeButton.addEventListener('click', this.closeModal);
        }

        // Image upload and dropzone
        if (this.dropzone && this.imageInput) {
            // Handle click to upload
            this.dropzone.addEventListener('click', () => {
                this.imageInput.click();
            });

            // Handle drag and drop
            this.dropzone.addEventListener('dragover', (e) => {
                e.preventDefault();
                this.dropzone.classList.add('dragover');
            });

            this.dropzone.addEventListener('dragleave', () => {
                this.dropzone.classList.remove('dragover');
            });
            this.dropzone.addEventListener('drop', (e) => {
                e.preventDefault();
                this.dropzone.classList.remove('dragover');
                if (e.dataTransfer.files.length) {
                    const file = e.dataTransfer.files[0];
                    if (file.type.startsWith('image/')) {
                        const mode = this.modal?.dataset.mode || 'add'; // ← Check mode
                        if (mode === 'edit') {
                            this.handleEditImageUpload(file);
                        } else {
                            this.handleAddImageUpload(file);
                        }
                    }
                }
            });
            
            this.imageInput.addEventListener('change', (e) => {
                if (e.target.files.length) {
                    const file = e.target.files[0];
                    if (file.type.startsWith('image/')) {
                        const mode = this.modal?.dataset.mode || 'add'; // ← Check the mode
                        if (mode === 'edit') {
                            this.handleEditImageUpload(file); // Call edit handler
                        } else {
                            this.handleAddImageUpload(file);  // Call add handler
                        }
                    }
                }
            });
        }

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
    }

    uploadImage(file, mode = 'add') {
        if (!file || !file.type.startsWith('image/')) {
            console.error('Invalid file type');
            return;
        }
    
        const formData = new FormData();
        formData.append('image', file);
    
        // Always include the correct type/category from the form
        let type = 'product';
        const typeField = this.form.querySelector('[name="product_type"]') ||
                          this.form.querySelector('[name="part_type"]') ||
                          this.form.querySelector('[name="service_type"]') ||
                          this.form.querySelector('[name="extra_type"]') ||
                          this.form.querySelector('[name="bundle_type"]');
        if (typeField && typeField.value) {
            type = typeField.value;
        }
        formData.append('category', type);
    
        const accountNumber = document.querySelector('meta[name="account-number"]')?.content || 'ACC002';
    
        // Always use POST for image upload
        let action = 'upload_image';
        if (mode === 'edit') {
            const productId = this.modal?.dataset.productId;
            if (!productId) {
                alert('Product ID is missing for image upload in edit mode.');
                return;
            }
            formData.append('product_id', productId);
        }
    
        if (this.imagePreview) {
            this.imagePreview.src = 'https://placehold.co/300x300?text=Uploading...';
        }
    
        fetch(`/modules/invoice/api/products.php?action=${action}`, {
            method: 'POST',
            body: formData,
            credentials: 'include'
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                if (this.imagePreview) {
                    let imgUrl = data.url;
                    if (imgUrl && !imgUrl.startsWith('http') && !imgUrl.startsWith('/')) {
                        imgUrl = '/' + imgUrl;
                }
                    this.imagePreview.src = imgUrl;
                }
            } else {
                throw new Error(data.message || 'Image upload failed');
            }
        })
        .catch(error => {
            console.error('Image upload error:', error);
            alert('Failed to upload image: ' + error.message);
            if (this.imagePreview) {
                this.imagePreview.src = 'https://placehold.co/300x300?text=No+Image';
            }
        });
    }
    

    switchTab(e) {
        const tabName = e.target.dataset.tab;
        document.querySelectorAll('.upm-tab-btn').forEach(btn => btn.classList.remove('upm-active'));
        document.querySelectorAll('.upm-tab-pane').forEach(pane => pane.classList.remove('upm-active'));
        
        e.target.classList.add('upm-active');
        document.getElementById(`upm-tab-${tabName}`).classList.add('upm-active');
    }

    openModal(mode = 'add', productId = null, type = null) {
        if (!this.modal || !this.modalTitle || !this.form) {
            console.error('Required modal elements not found');
            return;
        }

        // Set modal mode and product ID
        this.modal.dataset.mode = mode;
        this.modal.dataset.productId = productId;
        
        // Update modal title
        this.modalTitle.textContent = mode === 'add' ? 'Add New Product' : 'Edit Product';
        
        // Reset form
        this.form.reset();
        
        // Clear any existing product_id fields
        const existingFields = this.form.querySelectorAll('input[name="product_id"]');
        existingFields.forEach(field => field.remove());
        
        // Add product ID field for edit mode
        if (mode === 'edit' && productId) {
            const productIdField = document.createElement('input');
            productIdField.type = 'hidden';
            productIdField.name = 'product_id';
            productIdField.value = productId;
            this.form.appendChild(productIdField);
            console.log('Added product ID to form:', productId);
        }

        // Set the correct type/category for add mode and autofill type dropdown
        if (mode === 'add') {
            this.initializeDropdowns(null, null, null, type);
        }

        // Reset image preview
        if (this.imagePreview) {
            this.imagePreview.src = 'https://placehold.co/300x300?text=No+Image';
        }

        // Show modal using .active class
        this.modal.classList.add('active');
        
        // Show/hide tabs based on product type
        this.showRelevantTabs();
    }

    closeModal() {
        if (this.modal) {
            this.modal.classList.remove('active');
            // Reset form
            if (this.form) {
                this.form.reset();
                // Clear any existing product_id fields
                const existingFields = this.form.querySelectorAll('input[name="product_id"]');
                existingFields.forEach(field => field.remove());
            }
            // Reset image preview
            if (this.imagePreview) {
                this.imagePreview.src = 'https://placehold.co/300x300?text=No+Image';
            }
        }
    }

    async populateForm(data) {
        if (!this.form) {
            console.error('Form not found');
            return;
        }

        // Log the data being used to populate the form
        console.log('Populating form with data:', data);

        // Set product ID first
        if (data.product_id) {
            const productIdField = document.createElement('input');
            productIdField.type = 'hidden';
            productIdField.name = 'product_id';
            productIdField.value = data.product_id;
            this.form.appendChild(productIdField);
            console.log('Set product ID in form:', data.product_id);
        }

        // Map API field names to form field names
        const fieldMappings = {
            'product_name': 'prod_name',
            'product_description': 'prod_descr',
            'product_price': 'prod_price',
            'status': 'status',
            'sku': 'sku',
            'barcode': 'barcode',
            'tax_rate': 'tax_rate',
            'discount': 'discount',
            'notes': 'notes',
            'brand': 'brand',
            'manufacturer': 'manufacturer',
            'weight': 'weight',
            'dimensions': 'dimensions',
            'warranty_period': 'warranty_period',
            'stock_quantity': 'stock_quantity',
            'reorder_level': 'reorder_level',
            'lead_time': 'lead_time'
        };

        // Get account number from meta tag
        const accountNumber = document.querySelector('meta[name="account-number"]')?.content || 'ACC002';

        // Set values for each field
        Object.entries(fieldMappings).forEach(([apiField, formField]) => {
            const element = this.form.querySelector(`[name="${formField}"]`);
            if (element) {
                // Handle different input types
                if (element.type === 'checkbox') {
                    element.checked = data[apiField] === true || data[apiField] === 'true';
                } else if (element.type === 'number') {
                    element.value = data[apiField] || '0';
                } else {
                    element.value = data[apiField] || '';
                }
            }
        });

        // Set image preview based on product_id and type_name
        if (this.imagePreview && data.product_id && data.type_name) {
            const type = (data.type_name || 'product').toLowerCase();
            this.imagePreview.src = `/Uploads/${accountNumber}/products/${type}/${data.product_id}.jpg`;
        } else if (this.imagePreview) {
            this.imagePreview.src = 'https://placehold.co/300x300?text=No+Image';
        }

        // Set dropdowns by ID and type
        await this.initializeDropdowns(data.type_id, data.category_id, data.subcategory_id, data.type_name);

        // Show relevant tabs based on product type
        this.showRelevantTabs();
    }

    async handleSubmit(e) {
        e.preventDefault();
        e.stopPropagation();
        const formData = new FormData(this.form);
        const isEditMode = this.modal.dataset.mode === 'edit';
        if (isEditMode) {
            const productId = this.modal.dataset.productId;
            if (!productId) {
                throw new Error('Product ID is required for edit mode');
            }
            formData.set('product_id', productId);
        }
        formData.delete('prod_id');
        // Remove any old product_type/category/sub_category fields
        formData.delete('product_type');
        formData.delete('category');
        formData.delete('sub_category');
        fetch(`/modules/invoice/api/products.php?action=${isEditMode ? 'edit' : 'add'}`, {
            method: isEditMode ? 'PUT' : 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                this.closeModal();
                if (typeof refreshProductList === 'function') {
                    refreshProductList();
                    refreshPartList();
                    refreshServiceList();
                    refreshExtraList();
                }
            } else {
                throw new Error(data.message || 'Failed to save product');
            }
        })
        .catch(error => {
            console.error('Error saving product:', error);
            alert(error.message || 'Failed to save product');
        });
    }

    // Add fetchProducts function
    async fetchProducts() {
        try {
            const response = await fetch('/modules/invoice/api/products.php?action=list');
            const result = await response.json();
            
            if (!result.success) {
                throw new Error(result.message || 'Failed to fetch products');
            }
            
            return result.data;
        } catch (error) {
            console.error('Error fetching products:', error);
            throw error;
        }
    }

    showRelevantTabs(tabId = 'basic') {
        // Remove active class from all tabs and panes
        const tabButtons = this.modal?.querySelectorAll('.upm-tab-btn');
        const tabPanes = this.modal?.querySelectorAll('.upm-tab-pane');
        
        if (tabButtons) {
            tabButtons.forEach(btn => btn.classList.remove('upm-active'));
        }
        
        if (tabPanes) {
            tabPanes.forEach(pane => pane.classList.remove('upm-active'));
        }
        
        // Add active class to selected tab and pane
        const selectedTab = this.modal?.querySelector(`.upm-tab-btn[data-tab="${tabId}"]`);
        const selectedPane = this.modal?.querySelector(`#upm-tab-${tabId}`);
        
        if (selectedTab) {
            selectedTab.classList.add('upm-active');
        }
        
        if (selectedPane) {
            selectedPane.classList.add('upm-active');
        }
    }

    async initializeDropdowns(selectedTypeId = null, selectedCategoryId = null, selectedSubcategoryId = null, typeName = null) {
        console.log('initializeDropdowns called with:', {selectedTypeId, selectedCategoryId, selectedSubcategoryId, typeName});
        // Populate type dropdown
        const types = await fetchProductTypes();
        this.typeDropdown.innerHTML = '<option value="">Select type</option>' + types.map(t => `<option value="${t.type_id}">${t.type_name}</option>`).join('');
        if (this.typeDropdown && this.typeDropdown.options) {
            console.log('Dropdown after innerHTML:', this.typeDropdown, this.typeDropdown.options, Array.from(this.typeDropdown.options).map(o => ({value: o.value, text: o.text})));
        } else {
            console.log('Dropdown after innerHTML: options not available', this.typeDropdown);
        }
        let resolvedTypeId = selectedTypeId;
        if (typeName) {
            const match = types.find(t => t.type_name.toLowerCase() === typeName.toLowerCase());
            if (match) {
                console.log('Setting typeDropdown.value to', match.type_id);
                this.typeDropdown.value = match.type_id;
                console.log('typeDropdown.value after set:', this.typeDropdown.value);
                resolvedTypeId = match.type_id;
                console.log('Autofilled type dropdown to:', match.type_id, match.type_name);
            } else {
                console.warn('Type not found for autofill:', typeName, types);
            }
        } else if (selectedTypeId) {
            console.log('Setting typeDropdown.value to', selectedTypeId);
            this.typeDropdown.value = selectedTypeId;
            console.log('typeDropdown.value after set:', this.typeDropdown.value);
            resolvedTypeId = selectedTypeId;
        }
        // Populate categories for selected type
        if (resolvedTypeId) {
            const categories = await fetchProductCategories(resolvedTypeId);
            this.categoryDropdown.innerHTML = '<option value="">Select category</option>' + categories.map(c => `<option value="${c.category_id}">${c.category_name}</option>`).join('');
            if (selectedCategoryId) this.categoryDropdown.value = selectedCategoryId;
            // Populate subcategories for selected category
            if (selectedCategoryId) {
                const subcategories = await fetchProductSubcategories(selectedCategoryId);
                this.subcategoryDropdown.innerHTML = '<option value="">Select subcategory</option>' + subcategories.map(s => `<option value="${s.subcategory_id}">${s.subcategory_name}</option>`).join('');
                if (selectedSubcategoryId) this.subcategoryDropdown.value = selectedSubcategoryId;
            } else {
                this.subcategoryDropdown.innerHTML = '<option value="">Select subcategory</option>';
            }
        } else {
            this.categoryDropdown.innerHTML = '<option value="">Select category</option>';
            this.subcategoryDropdown.innerHTML = '<option value="">Select subcategory</option>';
        }
        // Event listeners for cascading
        this.typeDropdown.onchange = async () => {
            const typeId = this.typeDropdown.value;
            if (typeId) {
                const categories = await fetchProductCategories(typeId);
                this.categoryDropdown.innerHTML = '<option value="">Select category</option>' + categories.map(c => `<option value="${c.category_id}">${c.category_name}</option>`).join('');
                this.subcategoryDropdown.innerHTML = '<option value="">Select subcategory</option>';
            } else {
                this.categoryDropdown.innerHTML = '<option value="">Select category</option>';
                this.subcategoryDropdown.innerHTML = '<option value="">Select subcategory</option>';
            }
        };
        this.categoryDropdown.onchange = async () => {
            const categoryId = this.categoryDropdown.value;
            if (categoryId) {
                const subcategories = await fetchProductSubcategories(categoryId);
                this.subcategoryDropdown.innerHTML = '<option value="">Select subcategory</option>' + subcategories.map(s => `<option value="${s.subcategory_id}">${s.subcategory_name}</option>`).join('');
            } else {
                this.subcategoryDropdown.innerHTML = '<option value="">Select subcategory</option>';
            }
        };
        if (this.typeDropdown && this.typeDropdown.options) {
            console.log('Type dropdown value after init:', this.typeDropdown.value, 'Options:', Array.from(this.typeDropdown.options).map(o => ({value: o.value, text: o.text, selected: o.selected})));
        } else {
            console.error('Type dropdown or its options are not available:', this.typeDropdown);
        }
    }
}

// Wait for modal DOM before initializing ProductModal
function waitForModalAndInitProductModal() {
    const modal = document.getElementById('universalProductModal');
    const typeDropdown = document.getElementById('universalItemType');
    if (modal && typeDropdown) {
        window.productModal = new ProductModal();
        refreshProductList();
        refreshPartList();
        refreshServiceList();
        refreshExtraList();
    } else {
        setTimeout(waitForModalAndInitProductModal, 100); // Retry every 100ms
    }
}

document.addEventListener('DOMContentLoaded', () => {
    waitForModalAndInitProductModal();
});

// Global functions for opening modal
function openAddProductModal(type = 'product') {
    window.productModal.openModal('add', null, type);
}

function openEditProductModal(productData) {
    window.productModal.openModal('edit', productData.product_id);
}

// Export for use in other files
window.openAddProductModal = openAddProductModal;
window.openEditProductModal = openEditProductModal;

// Add refreshProductList function (global, for use outside the class)
async function refreshProductList() {
    try {
        const response = await fetch('/modules/invoice/api/products.php?action=list');
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to fetch products');
        }
        
        const productsGrid = document.getElementById('products-grid');
        if (!productsGrid) {
            console.error('Products grid element not found');
            return;
        }

        productsGrid.innerHTML = ''; // Clear existing products
        
        result.data.forEach(product => {
            if ((product.type_name || '').toLowerCase() !== 'product') return;
            const productCard = document.createElement('div');
            productCard.className = 'product-card';
            
            // Get account number from meta tag
            const accountNumber = document.querySelector('meta[name="account-number"]')?.content || 'ACC002';
            
            // Build image path
            let imageUrl = `/Uploads/${accountNumber}/products/product/${product.product_id}.jpg`;
            
            // Fallback if file doesn't exist (handled by onerror)
            productCard.innerHTML = `
                <div class="product-image">
                    <img src="${imageUrl}" alt="${product.product_name}" onerror="this.src='https://placehold.co/300x300?text=No+Image'">
                </div>
                <div class="product-info">
                    <h3>${product.product_name}</h3>
                    <p class="price">R${product.product_price}</p>
                    <p class="sku">SKU: ${product.sku || 'N/A'}</p>
                    <p class="stock">Stock: ${product.inventory?.stock_quantity || 0}</p>
                </div>
            `;
            
            // Add double-click event listener
            productCard.addEventListener('dblclick', async () => {
                try {
                    // First open the modal in edit mode
                    window.productModal.openModal('edit', product.product_id);
                    
                    // Then fetch product details
                    const detailsResponse = await fetch(`/modules/invoice/api/products.php?action=get&product_id=${product.product_id}`);
                    const detailsResult = await detailsResponse.json();
                    
                    if (!detailsResult.success) {
                        throw new Error(detailsResult.message || 'Failed to fetch product details');
                    }
                    
                    // Populate the form with the fetched details
                    window.productModal.populateForm(detailsResult.data);
                } catch (error) {
                    console.error('Error fetching product details:', error);
                    alert('Failed to load product details: ' + error.message);
                    window.productModal.closeModal(); // Close modal if there's an error
                }
            });
            
            productsGrid.appendChild(productCard);
        });
    } catch (error) {
        console.error('Error refreshing product list:', error);
        alert('Failed to refresh product list: ' + error.message);
    }
}

// Add deleteProduct function
async function deleteProduct(productId) {
    if (!confirm('Are you sure you want to delete this product?')) {
        return;
    }
    
    try {
        const response = await fetch(`/modules/invoice/api/products.php?action=delete&id=${productId}`, {
            method: 'DELETE',
            credentials: 'include'
        });
        
        const result = await response.json();
        
        if (!result.success) {
            throw new Error(result.message || 'Failed to delete product');
        }
        
        refreshProductList();
    } catch (error) {
        console.error('Error deleting product:', error);
        alert('Failed to delete product: ' + error.message);
    }
}

// Add refreshPartList function
async function refreshPartList() {
    try {
        const response = await fetch('/modules/invoice/api/products.php?action=list');
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Failed to fetch parts');
        }
        const partsGrid = document.getElementById('parts-grid');
        if (!partsGrid) {
            console.error('Parts grid element not found');
            return;
        }
        partsGrid.innerHTML = '';
        result.data.forEach(product => {
            if ((product.type_name || '').toLowerCase() !== 'part') return;
            const partCard = document.createElement('div');
            partCard.className = 'product-card';
            const accountNumber = document.querySelector('meta[name="account-number"]')?.content || 'ACC002';
            let imageUrl = `/Uploads/${accountNumber}/products/part/${product.product_id}.jpg`;
            partCard.innerHTML = `
                <div class="product-image">
                    <img src="${imageUrl}" alt="${product.product_name}" onerror="this.src='https://placehold.co/300x300?text=No+Image'">
                </div>
                <div class="product-info">
                    <h3>${product.product_name}</h3>
                    <p class="price">R${product.product_price}</p>
                    <p class="sku">SKU: ${product.sku || 'N/A'}</p>
                    <p class="stock">Stock: ${product.inventory?.stock_quantity || 0}</p>
                </div>
            `;
            partCard.addEventListener('dblclick', async () => {
                try {
                    window.productModal.openModal('edit', product.product_id);
                    const detailsResponse = await fetch(`/modules/invoice/api/products.php?action=get&product_id=${product.product_id}`);
                    const detailsResult = await detailsResponse.json();
                    if (!detailsResult.success) {
                        throw new Error(detailsResult.message || 'Failed to fetch part details');
                    }
                    window.productModal.populateForm(detailsResult.data);
                } catch (error) {
                    console.error('Error fetching part details:', error);
                    alert('Failed to load part details: ' + error.message);
                    window.productModal.closeModal();
                }
            });
            partsGrid.appendChild(partCard);
        });
    } catch (error) {
        console.error('Error refreshing part list:', error);
        alert('Failed to refresh part list: ' + error.message);
    }
}

// Add refreshServiceList function
async function refreshServiceList() {
    try {
        const response = await fetch('/modules/invoice/api/products.php?action=list');
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Failed to fetch services');
        }
        const servicesGrid = document.getElementById('services-grid');
        if (!servicesGrid) {
            console.error('Services grid element not found');
            return;
        }
        servicesGrid.innerHTML = '';
        result.data.forEach(product => {
            if ((product.type_name || '').toLowerCase() !== 'service') return;
            const serviceCard = document.createElement('div');
            serviceCard.className = 'product-card';
            const accountNumber = document.querySelector('meta[name="account-number"]')?.content || 'ACC002';
            let imageUrl = `/Uploads/${accountNumber}/products/service/${product.product_id}.jpg`;
            serviceCard.innerHTML = `
                <div class="product-image">
                    <img src="${imageUrl}" alt="${product.product_name}" onerror="this.src='https://placehold.co/300x300?text=No+Image'">
                </div>
                <div class="product-info">
                    <h3>${product.product_name}</h3>
                    <p class="price">R${product.product_price}</p>
                    <p class="sku">SKU: ${product.sku || 'N/A'}</p>
                    <p class="stock">Stock: ${product.inventory?.stock_quantity || 0}</p>
                </div>
            `;
            serviceCard.addEventListener('dblclick', async () => {
                try {
                    window.productModal.openModal('edit', product.product_id);
                    const detailsResponse = await fetch(`/modules/invoice/api/products.php?action=get&product_id=${product.product_id}`);
                    const detailsResult = await detailsResponse.json();
                    if (!detailsResult.success) {
                        throw new Error(detailsResult.message || 'Failed to fetch service details');
                    }
                    window.productModal.populateForm(detailsResult.data);
                } catch (error) {
                    console.error('Error fetching service details:', error);
                    alert('Failed to load service details: ' + error.message);
                    window.productModal.closeModal();
                }
            });
            servicesGrid.appendChild(serviceCard);
        });
    } catch (error) {
        console.error('Error refreshing service list:', error);
        alert('Failed to refresh service list: ' + error.message);
    }
}

// Add refreshExtraList function
async function refreshExtraList() {
    try {
        const response = await fetch('/modules/invoice/api/products.php?action=list');
        const result = await response.json();
        if (!result.success) {
            throw new Error(result.message || 'Failed to fetch extras');
        }
        const extrasGrid = document.getElementById('extras-grid');
        if (!extrasGrid) {
            console.error('Extras grid element not found');
            return;
        }
        extrasGrid.innerHTML = '';
        result.data.forEach(product => {
            if ((product.type_name || '').toLowerCase() !== 'extra') return;
            const extraCard = document.createElement('div');
            extraCard.className = 'product-card';
            const accountNumber = document.querySelector('meta[name="account-number"]')?.content || 'ACC002';
            let imageUrl = `/Uploads/${accountNumber}/products/extra/${product.product_id}.jpg`;
            extraCard.innerHTML = `
                <div class="product-image">
                    <img src="${imageUrl}" alt="${product.product_name}" onerror="this.src='https://placehold.co/300x300?text=No+Image'">
                </div>
                <div class="product-info">
                    <h3>${product.product_name}</h3>
                    <p class="price">R${product.product_price}</p>
                    <p class="sku">SKU: ${product.sku || 'N/A'}</p>
                    <p class="stock">Stock: ${product.inventory?.stock_quantity || 0}</p>
                </div>
            `;
            extraCard.addEventListener('dblclick', async () => {
                try {
                    window.productModal.openModal('edit', product.product_id);
                    const detailsResponse = await fetch(`/modules/invoice/api/products.php?action=get&product_id=${product.product_id}`);
                    const detailsResult = await detailsResponse.json();
                    if (!detailsResult.success) {
                        throw new Error(detailsResult.message || 'Failed to fetch extra details');
                    }
                    window.productModal.populateForm(detailsResult.data);
                } catch (error) {
                    console.error('Error fetching extra details:', error);
                    alert('Failed to load extra details: ' + error.message);
                    window.productModal.closeModal();
                }
            });
            extrasGrid.appendChild(extraCard);
        });
    } catch (error) {
        console.error('Error refreshing extra list:', error);
        alert('Failed to refresh extra list: ' + error.message);
    }
}

// --- Dropdown population helpers ---
async function fetchProductTypes() {
    const res = await fetch('/modules/invoice/api/products.php?action=list_types');
    const data = await res.json();
    if (!data.success) throw new Error('Failed to fetch product types');
    return data.data;
}

async function fetchProductCategories(typeId) {
    const res = await fetch(`/modules/invoice/api/products.php?action=list_categories&type_id=${typeId}`);
    const data = await res.json();
    if (!data.success) throw new Error('Failed to fetch categories');
    return data.data;
}

async function fetchProductSubcategories(categoryId) {
    const res = await fetch(`/modules/invoice/api/products.php?action=list_subcategories&category_id=${categoryId}`);
    const data = await res.json();
    if (!data.success) throw new Error('Failed to fetch subcategories');
    return data.data;
}

// Add a global function to initialize dropdowns from outside the class
window.initializeDropdowns = function(selectedTypeId = null, selectedCategoryId = null, selectedSubcategoryId = null, typeName = null) {
    if (window.productModal && typeof window.productModal.initializeDropdowns === 'function') {
        return window.productModal.initializeDropdowns(selectedTypeId, selectedCategoryId, selectedSubcategoryId, typeName);
    } else {
        console.error('ProductModal or initializeDropdowns not available');
    }
};