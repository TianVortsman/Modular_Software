// Client Form Logic: Handles form serialization, validation, reset, and submit for add/edit modals
// Usage: import { serializeForm, validateForm, resetForm, handleFormSubmit } from './client-form.js';

/**
 * Serialize form data from a given form element
 * @param {HTMLFormElement} form
 * @returns {Object} Serialized data
 */
export function serializeForm(form) {
    const data = {};
    const formData = new FormData(form);
    for (const [key, value] of formData.entries()) {
        data[key] = value.trim();
    }
    return data;
}

/**
 * Validate form data before submit
 * @param {Object} data
 * @returns {Object} { valid: boolean, errors: Object }
 */
export function validateForm(data) {
    const errors = {};
    // Company modal (edit)
    if (data.companyName !== undefined) {
        if (!data.companyName) errors.companyName = 'Company Name is required';
        if (!data.addrLine1) errors.addrLine1 = 'Address Line 1 is required';
        if (!data.city) errors.city = 'City is required';
        if (!data.postcode) errors.postcode = 'Postal Code is required';
        if (!data.country) errors.country = 'Country is required';
        if (!data.contactFirstName) errors.contactFirstName = 'Contact First Name is required';
        if (!data.contactLastName) errors.contactLastName = 'Contact Last Name is required';
        if (!data.contactEmail) errors.contactEmail = 'Contact Email is required';
        if (!data.contactPhone) errors.contactPhone = 'Contact Phone is required';
    }
    // Customer modal (edit)
    else if (data.customerName !== undefined) {
        if (!data.customerInitials) errors.customerInitials = 'Initials are required';
        if (!data.customerTitle) errors.customerTitle = 'Title is required';
        if (!data.customerName) errors.customerName = 'Customer Name is required';
        if (!data.customerSurname) errors.customerSurname = 'Customer Surname is required';
        if (!data.customerEmail) errors.customerEmail = 'Email is required';
        if (!data.customerCell) errors.customerCell = 'Cell is required';
        if (!data.custAddrLine1) errors.custAddrLine1 = 'Address Line 1 is required';
        if (!data.custCity) errors.custCity = 'City is required';
        if (!data.custPostalCode) errors.custPostalCode = 'Postal Code is required';
        if (!data.custCountry) errors.custCountry = 'Country is required';
    }
    // Add modal (legacy)
    else {
        if (!data['add-first-name']) errors['add-first-name'] = 'First Name is required';
        if (!data['add-last-name']) errors['add-last-name'] = 'Last Name is required';
        if (!data['add-email']) errors['add-email'] = 'Email is required';
        if (!data['add-phone']) errors['add-phone'] = 'Cell is required';
        if (!data['add-customer-address-line1']) errors['add-customer-address-line1'] = 'Address Line 1 is required';
        if (!data['add-customer-city']) errors['add-customer-city'] = 'City is required';
        if (!data['add-customer-postal-code']) errors['add-customer-postal-code'] = 'Postal Code is required';
        if (!data['add-customer-country']) errors['add-customer-country'] = 'Country is required';
    }
    return { valid: Object.keys(errors).length === 0, errors };
}

/**
 * Reset form fields to default/empty values
 * @param {HTMLFormElement} form
 */
export function resetForm(form) {
    form.reset();
    // Remove error classes/messages
    form.querySelectorAll('.error').forEach(el => el.classList.remove('error'));
}

/**
 * Handle form submit for add/edit client
 * @param {Event} event
 * @param {string} mode - 'add' or 'edit'
 */
export async function handleFormSubmit(event, mode) {
    event.preventDefault();
    const form = event.target;
    const data = serializeForm(form);
    const { valid, errors } = validateForm(data);
    if (!valid) {
        // Show errors (add .error class to fields)
        Object.keys(errors).forEach(key => {
            const field = form.querySelector(`[name="${key}"]`);
            if (field) field.classList.add('error');
        });
        if (typeof showResponseModal === 'function') {
            showResponseModal('Please fix the highlighted errors.', 'error');
        } else if (window.ResponseModal && window.ResponseModal.error) {
            window.ResponseModal.error('Please fix the highlighted errors.');
        }
        return;
    }
    // Map form data to API structure
    let payload = {};
    if (data.companyName !== undefined) {
        // Company modal (edit)
        payload = {
            client_type: 'business',
            client_name: data.companyName,
            registration_number: data.companyRegisNo,
            vat_number: data.companyVatNo,
            industry: data.companyIndustry,
            website: data.companyWebsite,
            client_email: data.companyEmail,
            client_tell: data.companyPhone,
            address: {
                address_line1: data.addrLine1,
                address_line2: data.addrLine2,
                suburb: data.suburb,
                city: data.city,
                province: data.province,
                postal_code: data.postcode,
                country: data.country
            },
            contact: {
                first_name: data.contactFirstName,
                last_name: data.contactLastName,
                email: data.contactEmail,
                phone: data.contactPhone,
                position: data.contactPosition
            }
        };
    } else if (data.customerName !== undefined) {
        // Customer modal (edit)
        payload = {
            client_type: 'private',
            initials: data.customerInitials,
            title: data.customerTitle,
            first_name: data.customerName,
            last_name: data.customerSurname,
            client_email: data.customerEmail,
            client_cell: data.customerCell,
            client_tell: data.customerTel,
            address: {
                address_line1: data.custAddrLine1,
                address_line2: data.custAddrLine2,
                suburb: data.custSuburb,
                city: data.custCity,
                province: data.custProvince,
                postal_code: data.custPostalCode,
                country: data.custCountry
            },
            dob: data.customerDOB,
            gender: data.customerGender,
            loyalty_level: data.customerLoyalty,
            notes: data.customerNotes
        };
    } else {
        // Add modal (legacy)
        payload = {
            client_type: 'private',
            first_name: data['add-first-name'],
            last_name: data['add-last-name'],
            title: data['add-title'],
            dob: data['add-dob'],
            gender: data['add-gender'],
            loyalty_level: data['add-loyalty'],
            initials: data['add-initials'],
            client_email: data['add-email'],
            client_cell: data['add-phone'],
            client_tell: data['add-tel'],
            address: {
                address_line1: data['add-customer-address-line1'],
                address_line2: data['add-customer-address-line2'],
                suburb: data['add-customer-suburb'],
                city: data['add-customer-city'],
                province: data['add-customer-province'],
                postal_code: data['add-customer-postal-code'],
                country: data['add-customer-country']
            }
        };
    }
    // Add created_by/updated_by if available
    if (window.currentUserId) {
        payload[mode === 'edit' ? 'updated_by' : 'created_by'] = window.currentUserId;
    }
    // Ensure client_type is set for add (empty modal):
    if (mode === 'add' && !payload.client_type) {
        const modal = form.closest('.modal');
        if (modal && modal.id) {
            if (modal.id.toLowerCase().includes('company')) payload.client_type = 'business';
            else if (modal.id.toLowerCase().includes('customer')) payload.client_type = 'private';
        }
    }
    // Remove mapping: expect client_type to be 'private' or 'business' from the form
    // Do NOT delete payload.client_type
    // Call API
    const { createClient, updateClient } = await import('./client-api.js');
    let res;
    try {
        if (mode === 'edit') {
            const clientId = data.companyId || data.customerId;
            res = await updateClient(clientId, payload);
        } else {
            res = await createClient(payload);
        }
    } catch (err) {
        if (typeof showResponseModal === 'function') {
            showResponseModal('Network or server error. Please try again.', 'error');
        } else if (window.ResponseModal && window.ResponseModal.error) {
            window.ResponseModal.error('Network or server error. Please try again.');
        }
        return;
    }
    if (res && res.success) {
        if (typeof showResponseModal === 'function') {
            showResponseModal(res.message || 'Client saved successfully', 'success');
        } else if (window.ResponseModal && window.ResponseModal.success) {
            window.ResponseModal.success(res.message || 'Client saved successfully');
        }
        // Optionally close modal and refresh table
        form.closest('.modal').style.display = 'none';
        if (window.refreshClientTable) window.refreshClientTable();
    } else {
        if (typeof showResponseModal === 'function') {
            showResponseModal((res && res.message) || 'Failed to save client', 'error');
        } else if (window.ResponseModal && window.ResponseModal.error) {
            window.ResponseModal.error((res && res.message) || 'Failed to save client');
        }
    }
}
