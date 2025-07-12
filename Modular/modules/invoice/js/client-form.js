import { submitEntityApi } from './client-api.js';

// Form submit handler for adding a company
function handleAddCompanyFormSubmit(e) {
    e.preventDefault();
    const modal = document.getElementById('add-modal');
    const form = document.getElementById('addClientForm');
    if (!form) {
        showResponseModal('Error', 'Form not found');
        return;
    }

    // Get form data
    const formData = {
        name: document.getElementById('add-company-name').value,
        registration_number: document.getElementById('add-registration-number').value,
        vat_number: document.getElementById('add-vat-number').value,
        industry: document.getElementById('add-industry').value,
        website: document.getElementById('add-website').value,
        address_line1: document.getElementById('add-company-address-line1').value,
        city: document.getElementById('add-company-city').value,
        postal_code: document.getElementById('add-company-postal-code').value,
        country: document.getElementById('add-company-country').value,
        contact_name: document.getElementById('add-contact-first-name').value + ' ' + document.getElementById('add-contact-last-name').value,
        contact_email: document.getElementById('add-contact-email').value,
        contact_phone: document.getElementById('add-contact-phone').value,
        contact_position: document.getElementById('add-contact-position').value
    };

    submitEntityApi('company', 'add', formData).then(data => {
        console.log('Add company response:', data);
        if (data.success) {
            modal.style.display = 'none';
            loadData();
            showResponseModal('Company added successfully', 'success');
        } else {
            showResponseModal(data.error || 'Failed to add company', 'error');
        }
    });
}


// Collects customer form data from the add customer form
function getAddCustomerFormData(form) {
    const formElements = form.elements;
    return {
        customerInitials: formElements['add-first-name']?.value?.charAt(0) || '',
        customerTitle: formElements['add-title']?.value || '',
        customerName: formElements['add-first-name']?.value || '',
        customerSurname: formElements['add-last-name']?.value || '',
        dob: formElements['add-dob']?.value || null,
        gender: formElements['add-gender']?.value || null,
        loyaltyLevel: formElements['add-loyalty']?.value || null,
        customerEmail: formElements['add-email']?.value || '',
        customerCell: formElements['add-phone']?.value || '',
        customerTel: formElements['add-tel']?.value || null,
        custAddrLine1: formElements['add-customer-address-line1']?.value || '',
        custAddrLine2: formElements['add-customer-address-line2']?.value || null,
        custSuburb: formElements['add-customer-suburb']?.value || null,
        custProvince: formElements['add-customer-province']?.value || null,
        custCity: formElements['add-customer-city']?.value || '',
        custPostalCode: formElements['add-customer-postal-code']?.value || '',
        custCountry: formElements['add-customer-country']?.value || ''
    };
}

// Helper: Set up the company modal form submission handler
export function setupCompanyModalSubmission(companyId) {
    const modal = document.getElementById('companyModal');
    const form = document.getElementById('companyForm');
    if (!form) {
        showResponseModal('Error', 'Company form not found');
        return;
    }
    form.onsubmit = async function(e) {
        e.preventDefault();
        const formData = {
            id: companyId,
            company_name: document.getElementById('companyName').value,
            vat_number: document.getElementById('companyVatNo').value,
            registration_number: document.getElementById('companyRegisNo').value,
            industry: document.getElementById('companyIndustry').value,
            website: document.getElementById('companyWebsite').value,
            address_line1: document.getElementById('addrLine1').value,
            address_line2: document.getElementById('addrLine2').value,
            suburb: document.getElementById('suburb').value,
            city: document.getElementById('city').value,
            province: document.getElementById('province').value,
            country: document.getElementById('country').value,
            postal_code: document.getElementById('postcode').value,
            contact_first_name: document.getElementById('contactName').value.split(' ')[0] || '',
            contact_last_name: document.getElementById('contactName').value.split(' ').slice(1).join(' ') || '',
            contact_email: document.getElementById('contactEmail').value,
            contact_phone: document.getElementById('contactPhone').value,
            contact_position: document.getElementById('contactPosition').value
        };

        const updateResult = await submitEntityApi('company', 'update', formData);

        if (updateResult.success) {
            showResponseModal('Success', updateResult.message);
            modal.style.display = 'none';
            loadData(); // Refresh the list
        } else {
            showResponseModal('Error', updateResult.error);
        }
    };
}

function collectCustomerFormData(customerId) {
    return {
        id: customerId,
        customerInitials: document.getElementById('customerInitials').value,
        customerTitle: document.getElementById('customerTitle').value,
        customerName: document.getElementById('customerName').value,
        customerSurname: document.getElementById('customerSurname').value,
        dob: document.getElementById('customerDOB').value,
        gender: document.getElementById('customerGender').value,
        loyaltyLevel: document.getElementById('customerLoyalty').value,
        customerEmail: document.getElementById('customerEmail').value,
        customerCell: document.getElementById('customerCell').value,
        customerTel: document.getElementById('customerTel') ? document.getElementById('customerTel').value : '',
        custAddrLine1: document.getElementById('custAddrLine1').value,
        custAddrLine2: document.getElementById('custAddrLine2').value,
        custSuburb: document.getElementById('custSuburb').value,
        custProvince: document.getElementById('custProvince').value,
        custCity: document.getElementById('custCity').value,
        custPostalCode: document.getElementById('custPostalCode').value,
        custCountry: document.getElementById('custCountry').value
    };
}


// --- Form Logic ---
function getAddFormData(section) {
    if (section === 'private') {
        return {
            first_name: document.getElementById('add-first-name').value,
            last_name: document.getElementById('add-last-name').value,
            email: document.getElementById('add-email').value,
            phone: document.getElementById('add-phone').value,
            address_line1: document.getElementById('add-address-line1').value,
            city: document.getElementById('add-city').value,
            postal_code: document.getElementById('add-postal-code').value,
            country: document.getElementById('add-country').value
        };
    } else {
        return {
            company_name: document.getElementById('add-company-name').value,
            registration_number: document.getElementById('add-registration-number').value,
            vat_number: document.getElementById('add-vat-number').value,
            industry: document.getElementById('add-industry').value,
            website: document.getElementById('add-website').value,
            address_line1: document.getElementById('add-address-line1').value,
            city: document.getElementById('add-city').value,
            postal_code: document.getElementById('add-postal-code').value,
            country: document.getElementById('add-country').value,
            contact_first_name: document.getElementById('add-contact-first-name').value,
            contact_last_name: document.getElementById('add-contact-last-name').value,
            contact_position: document.getElementById('add-contact-position').value,
            contact_email: document.getElementById('add-contact-email').value,
            contact_phone: document.getElementById('add-contact-phone').value
        };
    }
}

// --- Form Data Logic ---
export function getCustomerFormData(formElements) {
    return {
        // Personal Details
        customerInitials: formElements['add-first-name']?.value?.charAt(0) || '',
        customerTitle: formElements['add-title']?.value || '',
        customerName: formElements['add-first-name']?.value || '',
        customerSurname: formElements['add-last-name']?.value || '',
        dob: formElements['add-dob']?.value || null,
        gender: formElements['add-gender']?.value || null,
        loyaltyLevel: formElements['add-loyalty']?.value || null,

        // Contact Details
        customerEmail: formElements['add-email']?.value || '',
        customerCell: formElements['add-phone']?.value || '',
        customerTel: formElements['add-tel']?.value || null,

        // Address Details
        custAddrLine1: formElements['add-customer-address-line1']?.value || '',
        custAddrLine2: formElements['add-customer-address-line2']?.value || null,
        custSuburb: formElements['add-customer-suburb']?.value || null,
        custProvince: formElements['add-customer-province']?.value || null,
        custCity: formElements['add-customer-city']?.value || '',
        custPostalCode: formElements['add-customer-postal-code']?.value || '',
        custCountry: formElements['add-customer-country']?.value || ''
    };
}

export function getCompanyFormData(formElements) {
    return {
        // Company Information
        companyName: formElements['add-company-name']?.value || '',
        registrationNumber: formElements['add-registration-number']?.value || null,
        vatNumber: formElements['add-vat-number']?.value || null,
        industry: formElements['add-industry']?.value || null,
        website: formElements['add-website']?.value || null,

        // Company Address
        addressLine1: formElements['add-company-address-line1']?.value || '',
        addressLine2: formElements['add-company-address-line2']?.value || null,
        suburb: formElements['add-company-suburb']?.value || null,
        province: formElements['add-company-province']?.value || null,
        city: formElements['add-company-city']?.value || '',
        postalCode: formElements['add-company-postal-code']?.value || '',
        country: formElements['add-company-country']?.value || '',

        // Company Contact
        contactFirstName: formElements['add-contact-first-name']?.value || '',
        contactLastName: formElements['add-contact-last-name']?.value || '',
        contactPosition: formElements['add-contact-position']?.value || null,
        contactEmail: formElements['add-contact-email']?.value || '',
        contactPhone: formElements['add-contact-phone']?.value || ''
    };
}



// Helper: Populate all company modal fields
export function populateCompanyModalFields(company) {
    document.getElementById('companyId').value = company.company_id;
    document.getElementById('companyName').value = company.company_name;
    document.getElementById('companyVatNo').value = company.vat_number || '';
    document.getElementById('companyRegisNo').value = company.registration_number || '';
    document.getElementById('companyIndustry').value = company.industry || '';
    document.getElementById('companyWebsite').value = company.website || '';
    document.getElementById('companyPhone').value = company.contact_phone || '';
    document.getElementById('companyEmail').value = company.contact_email || '';

    // Address fields
    document.getElementById('addrLine1').value = company.address_line1 || '';
    document.getElementById('addrLine2').value = company.address_line2 || '';
    document.getElementById('suburb').value = company.suburb || '';
    document.getElementById('city').value = company.city || '';
    document.getElementById('province').value = company.province || '';
    document.getElementById('country').value = company.country || '';
    document.getElementById('postcode').value = company.postal_code || '';

    // Contact fields
    document.getElementById('contactName').value = `${company.contact_first_name || ''} ${company.contact_last_name || ''}`.trim();
    document.getElementById('contactPosition').value = company.contact_position || '';
    document.getElementById('contactEmail').value = company.contact_email || '';
    document.getElementById('contactPhone').value = company.contact_phone || '';
}

export function populateCustomerFormFields(customer) {
    document.getElementById('customerId').value = customer.customer_id || '';
    document.getElementById('customerInitials').value = customer.customer_initials || '';
    document.getElementById('customerTitle').value = customer.customer_title || '';
    document.getElementById('customerName').value = customer.first_name || '';
    document.getElementById('customerSurname').value = customer.last_name || '';
    document.getElementById('customerDOB').value = customer.dob || '';
    document.getElementById('customerGender').value = customer.gender || '';
    document.getElementById('customerLoyalty').value = customer.loyalty_level || '';
    document.getElementById('customerEmail').value = customer.email || '';
    document.getElementById('customerCell').value = customer.phone || '';
    document.getElementById('custAddrLine1').value = customer.address_line1 || '';
    document.getElementById('custAddrLine2').value = customer.address_line2 || '';
    document.getElementById('custSuburb').value = customer.suburb || '';
    document.getElementById('custProvince').value = customer.province || '';
    document.getElementById('custCity').value = customer.city || '';
    document.getElementById('custPostalCode').value = customer.postal_code || '';
    document.getElementById('custCountry').value = customer.country || '';
}

export function setupCustomerModalSubmission(customerId, onUpdate) {
    const form = document.getElementById('customerForm');
    if (!form) return;
    form.onsubmit = async function(e) {
        e.preventDefault();
        const formData = collectCustomerFormData(customerId);
        const result = await submitEntityApi('customer', 'update', formData);
        if (result.success) {
            showResponseModal('Success', result.message);
            document.getElementById('customerModal').style.display = 'none';
            loadData();
        } else {
            showResponseModal('Error', result.error);
        }
    };
}

