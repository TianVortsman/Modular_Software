// --- ProductModalAPI: Handles all API calls for products ---
import { buildQueryParams } from '../../../public/assets/js/helpers.js';

export class ProductAPI {
    // Fetch all products, optionally with filters
    static fetchProducts(filters = {}) {
        // Use the shared buildQueryParams helper for consistency
        const params = buildQueryParams({ action: 'list' }, filters);
        const url = '/modules/invoice/api/products.php?' + params.toString();
        return fetch(url, {
            credentials: 'include'
        }).then(res => res.json()).then(window.handleApiResponse)
        .catch(error => {
            throw error;
        });
    }

    // Fetch product details by ID
    static fetchProductDetails(productId) {
        console.log('Calling fetchProductDetails for productId:', productId);
        return fetch(`/modules/invoice/api/products.php?action=get&id=${productId}`, {
            credentials: 'include'
        }).then(res => res.json()).then(data => {
            console.log('fetchProductDetails result:', data);
            return data;
        });
    }

    // Add a new product
    static addProduct(formData) {
        // Ensure all required fields are present in formData
        // (Handled by form logic)
        return fetch('/modules/invoice/api/products.php?action=add', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        }).then(res => res.json());
    }

    // Edit an existing product
    static editProduct(productId, formData) {
        formData.set('product_id', productId);
        return fetch('/modules/invoice/api/products.php?action=edit', {
            method: 'PUT',
            body: formData,
            credentials: 'include'
        }).then(res => res.json());
    }

    // Delete a product
    static deleteProduct(productId) {
        return fetch(`/modules/invoice/api/products.php?action=delete&id=${productId}`, {
            method: 'DELETE',
            credentials: 'include'
        }).then(res => res.json());
    }

    // Upload product image
    static uploadImage(file, productId = null, type = 'product') {
        const formData = new FormData();
        formData.append('image', file);
        formData.append('category', type);
        if (productId) formData.append('product_id', productId);
        return fetch('/modules/invoice/api/products.php?action=upload_image', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        }).then(res => res.json());
    }

    // Fetch product types
    static fetchProductTypes() {
        return fetch('/modules/invoice/api/products.php?action=list_types', {
            credentials: 'include'
        }).then(res => res.json());
    }

    // Fetch product categories, optionally filtered by typeId
    static fetchProductCategories(typeId) {
        let url = '/modules/invoice/api/products.php?action=list_categories';
        if (typeId) {
            url += `&product_type_id=${typeId}`;
        }
        console.log('fetchProductCategories: fetching', url);
        return fetch(url, {
            credentials: 'include'
        }).then(res => res.json());
    }

    // Fetch product subcategories by categoryId
    static fetchProductSubcategories(categoryId) {
        return fetch(`/modules/invoice/api/products.php?action=list_subcategories&category_id=${categoryId}`, {
            credentials: 'include'
        }).then(res => res.json());
    }

    // Fetch suppliers (if needed)
    static fetchSuppliers() {
        return fetch('/modules/invoice/api/suppliers.php?action=list', {
            credentials: 'include'
        }).then(res => res.json());
    }

    // Fetch all tax rates for dropdown
    static fetchTaxRates() {
        return fetch('/modules/invoice/api/products.php?action=list_tax_rates', {
            credentials: 'include'
        }).then(res => res.json());
    }

    // Update product status (active/inactive/discontinued)
    static updateStatus(productId, status) {
        const formData = new FormData();
        formData.append('product_id', productId);
        formData.append('product_status', status);
        return fetch('/modules/invoice/api/products.php?action=update_status', {
            method: 'POST',
            body: formData,
            credentials: 'include'
        }).then(res => res.json());
    }

    // Fetch suppliers and stock info for a product (for modal Suppliers tab)
    static fetchProductSuppliersAndStock(productId) {
        return fetch(`/modules/invoice/api/products.php?action=get_product_suppliers_and_stock&product_id=${productId}`, {
            credentials: 'include'
        }).then(res => res.json());
    }
}
