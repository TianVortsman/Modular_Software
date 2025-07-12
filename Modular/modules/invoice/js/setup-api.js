// Centralized API class for Invoice Setup
// All API calls from invoice-setup.js should be routed through here

export default class SetupAPI {
    // Product Categories
    static async getCategories() {
        return fetch('../api/setup.php?action=get_categories', {
            method: 'GET', credentials: 'include'
        }).then(res => res.json());
    }
    static async saveCategory(formData) {
        return fetch('../api/setup.php?action=save_category', {
            method: 'POST', body: formData, credentials: 'include'
        }).then(res => res.json());
    }
    static async deleteCategory(id) {
        return fetch(`../api/setup.php?action=delete_category&id=${id}`, {
            method: 'DELETE', credentials: 'include'
        }).then(res => res.json());
    }

    // Product Subcategories
    static async getSubcategories() {
        return fetch('../api/setup.php?action=list_subcategories', {
            method: 'GET', credentials: 'include'
        }).then(res => res.json());
    }
    static async saveSubcategory(formData) {
        return fetch('../api/setup.php?action=save_subcategory', {
            method: 'POST', body: formData, credentials: 'include'
        }).then(res => res.json());
    }
    static async deleteSubcategory(id) {
        return fetch(`../api/setup.php?action=delete_subcategory&id=${id}`, {
            method: 'DELETE', credentials: 'include'
        }).then(res => res.json());
    }

    // Product Types
    static async getProductTypes() {
        return fetch('../api/setup.php?action=get_product_types', {
            method: 'GET', credentials: 'include'
        }).then(res => res.json());
    }
    static async deleteProductType(id) {
        return fetch(`../api/setup.php?action=delete_product_type&id=${id}`, {
            method: 'DELETE', credentials: 'include'
        }).then(res => res.json());
    }

    // Bank Info
    static async getBankInfo() {
        return fetch('../api/setup.php?action=get_bank_info', {
            method: 'GET', credentials: 'include'
        }).then(res => res.json());
    }
    static async saveBankInfo(formData) {
        return fetch('../api/setup.php?action=save_bank_info', {
            method: 'POST', body: formData, credentials: 'include'
        }).then(res => res.json());
    }

    // Company Info
    static async getCompanyInfo() {
        return fetch('../api/setup.php?action=get_company_info', {
            method: 'GET', credentials: 'include'
        }).then(res => res.json());
    }
    static async saveCompanyInfo(formData) {
        return fetch('../api/setup.php?action=save_company_info', {
            method: 'POST', body: formData, credentials: 'include'
        }).then(res => res.json());
    }

    // Sales Targets
    static async getSalesTargets() {
        return fetch('../api/setup.php?action=get_sales_targets', {
            method: 'GET', credentials: 'include'
        }).then(res => res.json());
    }
    static async deleteSalesTarget(id) {
        return fetch(`../api/setup.php?action=delete_sales_target&id=${id}`, {
            method: 'DELETE', credentials: 'include'
        }).then(res => res.json());
    }

    // Suppliers
    static async getSuppliers() {
        return fetch('../api/setup.php?action=get_suppliers', {
            method: 'GET', credentials: 'include'
        }).then(res => res.json());
    }
    static async deleteSupplier(id) {
        return fetch(`../api/setup.php?action=delete_supplier&id=${id}`, {
            method: 'DELETE', credentials: 'include'
        }).then(res => res.json());
    }

    // Credit Policy
    static async getCreditPolicy() {
        return fetch('../api/setup.php?action=get_credit_policy', {
            method: 'GET', credentials: 'include'
        }).then(res => res.json());
    }
    static async saveCreditPolicy(formData) {
        return fetch('../api/setup.php?action=save_credit_policy', {
            method: 'POST', body: formData, credentials: 'include'
        }).then(res => res.json());
    }

    // Credit Reasons
    static async getCreditReasons() {
        return fetch('../api/setup.php?action=get_credit_reasons', {
            method: 'GET', credentials: 'include'
        }).then(res => res.json());
    }
    static async deleteCreditReason(id) {
        return fetch(`../api/setup.php?action=delete_credit_reason&id=${id}`, {
            method: 'DELETE', credentials: 'include'
        }).then(res => res.json());
    }

    // Numbering
    static async getNumberingSettings() {
        return fetch('../api/setup.php?action=get_numbering_settings', {
            method: 'GET', credentials: 'include'
        }).then(res => res.json());
    }
    static async saveNumberingSettings(formData) {
        return fetch('../api/setup.php?action=save_numbering_settings', {
            method: 'POST', body: formData, credentials: 'include'
        }).then(res => res.json());
    }

    // Terms
    static async getTermsSettings() {
        return fetch('../api/setup.php?action=get_terms_settings', {
            method: 'GET', credentials: 'include'
        }).then(res => res.json());
    }
    static async saveTermsSettings(formData) {
        return fetch('../api/setup.php?action=save_terms_settings', {
            method: 'POST', body: formData, credentials: 'include'
        }).then(res => res.json());
    }
} 