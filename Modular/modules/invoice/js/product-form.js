    // --- ProductModalForm: Handles form logic (form state, validation, field population, etc.) ---
    class ProductModalForm {
        constructor(modalElement) {
            this.modal = modalElement;
            this.form = modalElement.querySelector('form') || document.getElementById('universalProductForm');
            this.imagePreview = document.getElementById('universalItemImagePreview');
            this.imageInput = document.getElementById('universalItemImage');
            this.typeDropdown = document.getElementById('universalItemType');
            this.categoryDropdown = document.getElementById('universalItemCategory');
            this.subcategoryDropdown = document.getElementById('universalItemSubcategory');
            this.supplierDropdown = document.getElementById('universalItemSupplier');
        }
        populateForm(data) {
            if (!this.form) return;
            if (data.product_id) {
                let productIdField = this.form.querySelector('input[name="product_id"]');
                if (!productIdField) {
                    productIdField = document.createElement('input');
                    productIdField.type = 'hidden';
                    productIdField.name = 'product_id';
                    this.form.appendChild(productIdField);
                }
                productIdField.value = data.product_id;
            }
            // Refactored fieldMappings to match backend controller fields and modal field names
            const fieldMappings = {
                'product_name': 'product_name',
                'product_description': 'product_descr', // textarea in modal
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
                // Inventory fields (modal uses different names)
                'product_stock_quantity': 'stock_quantity',
                'product_reorder_level': 'reorder_level',
                'product_lead_time': 'lead_time',
                'product_weight': 'product_weight',
                'product_dimensions': 'dimensions',
                'product_brand': 'brand',
                'product_manufacturer': 'manufacturer',
                'warranty_period': 'warranty_period',
                'product_material': 'product_material',
                // Supplier
                'supplier_id': 'supplier_id',
                // Type-specific fields
                'compatible_vehicles': 'compatible_vehicles',
                'oem_part_number': 'oem_part_number',
                'estimated_time': 'estimated_time',
                'service_frequency': 'service_frequency',
                'bundle_items': 'bundle_items',
                'installation_required': 'installation_required',
                'labor_cost': 'labor_cost',
            };
            Object.entries(fieldMappings).forEach(([apiField, formField]) => {
                const element = this.form.querySelector(`[name="${formField}"]`);
                if (element) {
                    if (element.type === 'checkbox') {
                        element.checked = data[apiField] === true || data[apiField] === 'true';
                    } else {
                        element.value = data[apiField] != null ? data[apiField] : '';
                    }
                }
            });
            if (this.imagePreview && data.product_id) {
                let imgUrl = data.image_url;
                if (!imgUrl) {
                    const type = (data.type_name || 'product').toLowerCase();
                    const accountNumber = document.querySelector('meta[name="account-number"]')?.content || 'ACC002';
                    imgUrl = `/Uploads/${accountNumber}/products/${type}/${data.product_id}.jpg`;
                }
                this.imagePreview.src = imgUrl;
            } else if (this.imagePreview) {
                this.imagePreview.src = 'https://placehold.co/300x300?text=No+Image';
                this.imagePreview.onerror = null;
            }
            const statusDropdown = document.getElementById('universalItemStatus');
            if (statusDropdown && data.status) {
                statusDropdown.value = data.status;
            }
            // Set tax rate dropdown if present
            const taxRateDropdown = this.form.querySelector('[name="tax_rate_id"]');
            if (taxRateDropdown && data.tax_rate_id) {
                taxRateDropdown.value = data.tax_rate_id;
            }
        }
        resetForm() {
            if (this.form) {
                this.form.reset();
                const existingFields = this.form.querySelectorAll('input[name="product_id"]');
                existingFields.forEach(field => field.remove());
            }
            if (this.imagePreview) {
                this.imagePreview.src = 'https://placehold.co/300x300?text=No+Image';
            }
        }
        validateForm() {
            // Add any custom validation logic here if needed
            // Return true if valid, false otherwise
            if (!this.form) {
                console.log('[ProductModalForm] No form found for validation');
                return false;
            }
            const valid = this.form.checkValidity();
            if (!valid) {
                // Log which fields are invalid
                Array.from(this.form.elements).forEach(el => {
                    if (!el.checkValidity()) {
                        console.log('[ProductModalForm] Invalid field:', el.name, el.value, el, el.validationMessage);
                    }
                });
            }
            return valid;
        }
        async populateSupplierDropdown(selectedId = null) {
            const supplierDropdown = this.supplierDropdown;
            if (!supplierDropdown) return;
            const res = await window.ProductAPI.fetchSuppliers();
            supplierDropdown.innerHTML = '<option value="">Select Supplier</option>';
            if (res.success && Array.isArray(res.data)) {
                res.data.forEach(supplier => {
                    const option = document.createElement('option');
                    option.value = supplier.supplier_id;
                    option.textContent = supplier.supplier_name;
                    supplierDropdown.appendChild(option);
                });
            }
            if (selectedId) supplierDropdown.value = selectedId;
        }
        async populateTypeDropdown(selectedId = null) {
            const typeDropdown = this.typeDropdown;
            if (!typeDropdown) return;
            const res = await window.ProductAPI.fetchProductTypes();
            typeDropdown.innerHTML = '<option value="">Select Type</option>';
            if (res.success && Array.isArray(res.data)) {
                res.data.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.product_type_id;
                    option.textContent = type.product_type_name;
                    typeDropdown.appendChild(option);
                });
            }
            if (selectedId) typeDropdown.value = selectedId;
            // Always trigger category filtering when type changes
            typeDropdown.onchange = () => {
                // Reset category and subcategory dropdowns
                if (this.categoryDropdown) {
                    this.categoryDropdown.value = '';
                    this.categoryDropdown.innerHTML = '<option value="">Select Category</option>';
                }
                if (this.subcategoryDropdown) {
                    this.subcategoryDropdown.value = '';
                    this.subcategoryDropdown.innerHTML = '<option value="">Select Subcategory</option>';
                }
                this.populateCategoryDropdown(typeDropdown.value);
                this.populateSubcategoryDropdown();
            };
        }
        async populateCategoryDropdown(typeId = null, selectedId = null) {
            const categoryDropdown = this.categoryDropdown;
            if (!categoryDropdown) return;
            const res = await window.ProductAPI.fetchProductCategories(typeId);
            categoryDropdown.innerHTML = '<option value="">Select Category</option>';
            if (res.success && Array.isArray(res.data)) {
                res.data.forEach(cat => {
                    const option = document.createElement('option');
                    option.value = cat.category_id;
                    option.textContent = cat.category_name;
                    categoryDropdown.appendChild(option);
                });
            }
            if (selectedId) categoryDropdown.value = selectedId;
        }
        async populateSubcategoryDropdown(categoryId = null, selectedId = null) {
            const subcategoryDropdown = this.subcategoryDropdown;
            if (!subcategoryDropdown) return;
            // If no categoryId provided, use currently selected category
            if (!categoryId) {
                categoryId = this.categoryDropdown ? this.categoryDropdown.value : null;
            }
            if (!categoryId) {
                subcategoryDropdown.innerHTML = '<option value="">Select Subcategory</option>';
                return;
            }
            const res = await window.ProductAPI.fetchProductSubcategories(categoryId);
            subcategoryDropdown.innerHTML = '<option value="">Select Subcategory</option>';
            if (res.success && Array.isArray(res.data)) {
                res.data.forEach(sub => {
                    const option = document.createElement('option');
                    option.value = sub.subcategory_id;
                    option.textContent = sub.subcategory_name;
                    subcategoryDropdown.appendChild(option);
                });
            }
            if (selectedId) subcategoryDropdown.value = selectedId;
        }
        async populateTaxRateDropdown(selectedId = null) {
            const taxRateDropdown = this.form.querySelector('[name="tax_rate_id"]');
            if (!taxRateDropdown) return;
            const res = await window.ProductAPI.fetchTaxRates();
            taxRateDropdown.innerHTML = '<option value="">Select Tax Rate</option>';
            if (res.success && Array.isArray(res.data)) {
                res.data.forEach(rate => {
                    const option = document.createElement('option');
                    option.value = rate.tax_rate_id;
                    option.textContent = `${rate.tax_name} (${rate.rate}%)`;
                    taxRateDropdown.appendChild(option);
                });
            }
            if (selectedId) taxRateDropdown.value = selectedId;
        }
    }

    // Export for use in other files
    window.ProductModalForm = ProductModalForm;