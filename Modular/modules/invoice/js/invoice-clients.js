// Global variables
let currentPage = 1;
let rowsPerPage = 10;
let currentSection = 'private'; // or 'business'
let customers = [];
let companies = [];

// Initialize the page
document.addEventListener('DOMContentLoaded', function() {
    // Set up event listeners
    setupEventListeners();
    
    // Load initial data
    loadData();

    // Tab switching functionality
    const modalTabs = document.querySelectorAll('.modal-tab');
    const tabContents = document.querySelectorAll('.tab-content');

    modalTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs
            modalTabs.forEach(t => t.classList.remove('active'));
            // Add active class to clicked tab
            tab.classList.add('active');

            // Hide all tab contents
            tabContents.forEach(content => {
                content.classList.remove('active');
                content.style.display = 'none';
            });

            // Show the selected tab content
            const tabId = tab.getAttribute('data-tab');
            const activeContent = document.querySelector(`.tab-content[data-tab-content="${tabId}"]`);
            if (activeContent) {
                activeContent.classList.add('active');
                activeContent.style.display = 'block';
            }
        });
    });

    // Function to initialize tabs for a specific modal
    function initializeTabs(modalId) {
        const modal = document.getElementById(modalId);
        if (!modal) return;

        const modalTabs = modal.querySelectorAll('.modal-tab');
        const tabContents = modal.querySelectorAll('.tab-content');

        modalTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs in this modal
                modalTabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                tab.classList.add('active');

                // Hide all tab contents in this modal
                tabContents.forEach(content => {
                    content.classList.remove('active');
                    content.style.display = 'none';
                });

                // Show the selected tab content
                const tabId = tab.getAttribute('data-tab');
                const activeContent = modal.querySelector(`.tab-content[data-tab-content="${tabId}"]`);
                if (activeContent) {
                    activeContent.classList.add('active');
                    activeContent.style.display = 'block';
                }
            });
        });

        // Activate first tab by default
        if (modalTabs.length > 0) {
            modalTabs[0].click();
        }
    }

    // Initialize tabs for both modals
    initializeTabs('customerModal');
    initializeTabs('companyModal');

    // Initialize tabs
    initializeTabs();

    // Add Customer button
    const addCustomerBtn = document.getElementById('addCustomerBtn');
    if (addCustomerBtn) {
        addCustomerBtn.addEventListener('click', function() {
            console.log('Add Customer button clicked');
            showAddCustomerModal();
        });
    }

    // Add Company button
    const addCompanyBtn = document.getElementById('addCompanyBtn');
    if (addCompanyBtn) {
        addCompanyBtn.addEventListener('click', function() {
            console.log('Add Company button clicked');
            showAddCompanyModal();
        });
    }

    // Add form submission handler
    const addClientForm = document.getElementById('addClientForm');
    const clientTypeSelect = document.getElementById('clientType');
    const companyFields = document.getElementById('companyFields');
    const customerFields = document.getElementById('customerFields');

    // Handle client type change
    if (clientTypeSelect) {
        clientTypeSelect.addEventListener('change', function() {
            const selectedType = this.value;
            
            // Hide both sections first
            if (companyFields) companyFields.style.display = 'none';
            if (customerFields) customerFields.style.display = 'none';
            
            // Remove required from all fields
            const allFields = addClientForm.querySelectorAll('input, select');
            allFields.forEach(field => field.removeAttribute('required'));
            
            // Show and set required fields based on selection
            if (selectedType === 'company') {
                if (companyFields) {
                    companyFields.style.display = 'block';
                    // Set required fields for company
                    const requiredFields = [
                        'add-company-name',
                        'add-address-line1',
                        'add-city',
                        'add-postal-code',
                        'add-country',
                        'add-contact-first-name',
                        'add-contact-last-name',
                        'add-contact-email',
                        'add-contact-phone'
                    ];
                    requiredFields.forEach(id => {
                        const field = document.getElementById(id);
                        if (field) field.setAttribute('required', '');
                    });
                }
            } else if (selectedType === 'customer') {
                if (customerFields) {
                    customerFields.style.display = 'block';
                    // Set required fields for customer
                    const requiredFields = [
                        'add-first-name',
                        'add-last-name',
                        'add-email',
                        'add-phone',
                        'add-address-line1',
                        'add-city',
                        'add-postal-code',
                        'add-country'
                    ];
                    requiredFields.forEach(id => {
                        const field = document.getElementById(id);
                        if (field) field.setAttribute('required', '');
                    });
                }
            }
        });
    }

    // Handle form submission
    if (addClientForm) {
        addClientForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const clientType = document.getElementById('clientType').value;
            const formElements = this.elements;
            
            if (clientType === 'customer') {
                // Collect customer form data
                const formData = {
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

                console.log('Sending customer data:', formData);
                
                try {
                    const response = await fetch('../api/customers.php?action=add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });
                    
                    const result = await response.json();
                    console.log('API Response:', result);
                    
                    if (result.success) {
                        showResponseModal('Success', result.message);
                        document.getElementById('add-modal').style.display = 'none';
                        loadData(); // Refresh the list
                    } else {
                        showResponseModal('Error', result.error);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showResponseModal('Error', 'An error occurred while adding the customer');
                }
            } else if (clientType === 'company') {
                // Collect company form data
                const formData = {
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

                console.log('Sending company data:', formData);
                
                try {
                    const response = await fetch('../api/companies.php?action=add', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify(formData)
                    });
                    
                    const result = await response.json();
                    console.log('API Response:', result);
                    
                    if (result.success) {
                        showResponseModal('Success', result.message);
                        document.getElementById('add-modal').style.display = 'none';
                        loadData(); // Refresh the list
                    } else {
                        showResponseModal('Error', result.error);
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showResponseModal('Error', 'An error occurred while adding the company');
                }
            }
        });
    }
});

function setupEventListeners() {
    // Section buttons
    document.getElementById('clientSectionButton1').addEventListener('click', () => switchSection('private'));
    document.getElementById('clientSectionButton2').addEventListener('click', () => switchSection('business'));
    
    // Search input
    document.getElementById('client-search').addEventListener('input', handleSearch);
    
    // Rows per page select
    document.querySelectorAll('.rows-per-page').forEach(select => {
        select.addEventListener('change', function() {
            rowsPerPage = parseInt(this.value);
            currentPage = 1;
            renderTable();
        });
    });

    // Modal event listeners
    document.getElementById('closeAddClientModal').addEventListener('click', closeAddModal);
    document.getElementById('clientType').addEventListener('change', updateModalForms);
    
    // Form submissions
    document.getElementById('customerForm').addEventListener('submit', saveEdit);
    document.getElementById('companyForm').addEventListener('submit', saveEdit);
}

function switchSection(section) {
    currentSection = section;
    currentPage = 1;
    
    // Update button states
    document.getElementById('clientSectionButton1').classList.toggle('active', section === 'private');
    document.getElementById('clientSectionButton2').classList.toggle('active', section === 'business');
    
    // Update section visibility
    document.getElementById('client-section1').classList.toggle('active', section === 'private');
    document.getElementById('client-section2').classList.toggle('active', section === 'business');
    
    // Update modal forms
    updateModalForms();
    
    // Load and render data
    loadData();
}

async function loadData() {
    showLoadingModal("Loading...");    
    
    try {
        const endpoint = currentSection === 'private' ? '../api/customers.php' : '../api/companies.php';
        console.log('Fetching data from:', endpoint);
        
        const response = await fetch(`${endpoint}?action=get_all`);
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        
        const result = await response.json();
        console.log('Response data:', result);
        
        if (result.success) {
            if (currentSection === 'private') {
                customers = result.data;
                console.log('Loaded customers:', customers.length);
                console.log('First customer:', customers[0]);
            } else {
                companies = result.data;
                console.log('Loaded companies:', companies.length);
                console.log('First company:', companies[0]);
            }
            renderTable();
        } else {
            console.error('API error:', result.error);
            showResponseModal('Failed to load data: ' + result.error);
        }
    } catch (error) {
        console.error('Fetch error:', error);
        showResponseModal('Failed to load data: ' + error.message);
    } finally {
        hideLoadingModal();
    }
}

function renderTable() {
    const data = currentSection === 'private' ? customers : companies;
    const filteredData = filterData(data);
    const paginatedData = paginateData(filteredData);
    
    const tbody = document.querySelector(`#client-section${currentSection === 'private' ? '1' : '2'} tbody`);
    tbody.innerHTML = '';
    
    paginatedData.forEach(item => {
        const row = document.createElement('tr');
        
        // Add data attributes to identify the type and ID
        row.setAttribute('data-type', currentSection === 'private' ? 'customer' : 'company');
        row.setAttribute('data-id', currentSection === 'private' ? item.customer_id : item.company_id);

        // Create cells based on type
        if (currentSection === 'private') {
            row.innerHTML = `
                <td>${item.customer_id}</td>
                <td>${item.first_name} ${item.last_name}</td>
                <td>${item.email || '-'}</td>
                <td>${item.phone || '-'}</td>
                <td>${item.last_invoice_date || '-'}</td>
                <td>R${parseFloat(item.outstanding_balance || 0).toFixed(2)}</td>
                <td>${item.total_invoices || 0}</td>
            `;
        } else {
            row.innerHTML = `
                <td>${item.company_id}</td>
                <td>${item.company_name}</td>
                <td>${item.contact_email || '-'}</td>
                <td>${item.contact_phone || '-'}</td>
                <td>${item.last_invoice_date || '-'}</td>
                <td>R${parseFloat(item.outstanding_balance || 0).toFixed(2)}</td>
                <td>${item.total_invoices || 0}</td>
            `;
        }
        
        // Add hover effect class
        row.classList.add('clickable-row');
        
        // Add double-click event listener
        row.addEventListener('dblclick', function() {
            console.log('Double-click event triggered');
            const type = this.getAttribute('data-type');
            const id = this.getAttribute('data-id');
            console.log(`Type: ${type}, ID: ${id}`);
            
            if (type === 'customer') {
                openCustomerModal(id);
            } else if (type === 'company') {
                openCompanyModal(id);
            }
        });
        
        tbody.appendChild(row);
    });
    
    renderPagination(filteredData.length);
}

function filterData(data) {
    const searchTerm = document.getElementById('client-search').value.toLowerCase();
    if (!searchTerm) return data;
    
    return data.filter(item => {
        if (currentSection === 'private') {
            return `${item.first_name} ${item.last_name}`.toLowerCase().includes(searchTerm) ||
                   item.customer_id.toString().includes(searchTerm);
        } else {
            return item.name.toLowerCase().includes(searchTerm) ||
                   item.company_id.toString().includes(searchTerm);
        }
    });
}

function paginateData(data) {
    const start = (currentPage - 1) * rowsPerPage;
    const end = start + rowsPerPage;
    return data.slice(start, end);
}

function renderPagination(totalItems) {
    const totalPages = Math.ceil(totalItems / rowsPerPage);
    const container = document.getElementById(`pagination-container${currentSection === 'private' ? '1' : '2'}`);
    
    let html = '';
    
    // Previous button
    html += `<button onclick="changePage(${currentPage - 1})" ${currentPage === 1 ? 'disabled' : ''}>Previous</button>`;
    
    // Page numbers
    for (let i = 1; i <= totalPages; i++) {
        html += `<button onclick="changePage(${i})" class="${i === currentPage ? 'active' : ''}">${i}</button>`;
    }
    
    // Next button
    html += `<button onclick="changePage(${currentPage + 1})" ${currentPage === totalPages ? 'disabled' : ''}>Next</button>`;
    
    container.innerHTML = html;
}

function changePage(page) {
    const totalPages = Math.ceil((currentSection === 'private' ? customers : companies).length / rowsPerPage);
    if (page >= 1 && page <= totalPages) {
        currentPage = page;
        renderTable();
    }
}

function handleSearch() {
    currentPage = 1;
    renderTable();
}

async function editItem(id) {
    const item = currentSection === 'private' 
        ? customers.find(c => c.customer_id === id)
        : companies.find(c => c.company_id === id);
    
    if (!item) return;
    
    // Show the appropriate form section
    document.getElementById('editCompanyFields').style.display = currentSection === 'business' ? 'block' : 'none';
    document.getElementById('editCustomerFields').style.display = currentSection === 'private' ? 'block' : 'none';
    
    // Populate modal fields
    if (currentSection === 'private') {
        document.getElementById('edit-id').value = item.customer_id;
        document.getElementById('edit-first-name').value = item.first_name;
        document.getElementById('edit-last-name').value = item.last_name;
        document.getElementById('edit-email').value = item.email || '';
        document.getElementById('edit-phone').value = item.phone || '';
        document.getElementById('edit-address-line1').value = item.address_line1 || '';
        document.getElementById('edit-city').value = item.city || '';
        document.getElementById('edit-postal-code').value = item.postal_code || '';
        document.getElementById('edit-country').value = item.country || '';
    } else {
        document.getElementById('edit-id').value = item.company_id;
        document.getElementById('edit-company-name').value = item.company_name;
        document.getElementById('edit-registration-number').value = item.registration_number || '';
        document.getElementById('edit-vat-number').value = item.vat_number || '';
        document.getElementById('edit-industry').value = item.industry || '';
        document.getElementById('edit-website').value = item.website || '';
        document.getElementById('edit-address-line1').value = item.address_line1 || '';
        document.getElementById('edit-city').value = item.city || '';
        document.getElementById('edit-postal-code').value = item.postal_code || '';
        document.getElementById('edit-country').value = item.country || '';
        document.getElementById('edit-contact-first-name').value = item.contact_first_name || '';
        document.getElementById('edit-contact-last-name').value = item.contact_last_name || '';
        document.getElementById('edit-contact-position').value = item.contact_position || '';
        document.getElementById('edit-contact-email').value = item.contact_email || '';
        document.getElementById('edit-contact-phone').value = item.contact_phone || '';
    }
}

async function saveEdit(event) {
    event.preventDefault();
    showLoadingModal(message = "Saving...");   
    
    try {
        const endpoint = currentSection === 'private' ? 'api/customers.php' : 'api/companies.php';
        const formData = currentSection === 'private'
            ? {
                id: document.getElementById('edit-id').value,
                first_name: document.getElementById('edit-first-name').value,
                last_name: document.getElementById('edit-last-name').value,
                email: document.getElementById('edit-email').value,
                phone: document.getElementById('edit-phone').value,
                address_line1: document.getElementById('edit-address-line1').value,
                city: document.getElementById('edit-city').value,
                postal_code: document.getElementById('edit-postal-code').value,
                country: document.getElementById('edit-country').value
            }
            : {
                id: document.getElementById('edit-company-id').value,
                company_name: document.getElementById('edit-company-name').value,
                registration_number: document.getElementById('edit-registration-number').value,
                vat_number: document.getElementById('edit-vat-number').value,
                industry: document.getElementById('edit-industry').value,
                website: document.getElementById('edit-website').value,
                address_line1: document.getElementById('edit-address-line1').value,
                city: document.getElementById('edit-city').value,
                postal_code: document.getElementById('edit-postal-code').value,
                country: document.getElementById('edit-country').value,
                contact_first_name: document.getElementById('edit-contact-first-name').value,
                contact_last_name: document.getElementById('edit-contact-last-name').value,
                contact_position: document.getElementById('edit-contact-position').value,
                contact_email: document.getElementById('edit-contact-email').value,
                contact_phone: document.getElementById('edit-contact-phone').value
            };
        
        const response = await fetch(`${endpoint}?action=update`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showResponseModal('Changes saved successfully');
            closeCompanyModal();
            loadData();
        } else {
            showResponseModal('Failed to save changes: ' + result.error);
        }
    } catch (error) {
        showResponseModal('Failed to save changes: ' + error.message);
    } finally {
        hideLoadingModal();
    }
}

async function deleteItem(id) {
    if (!confirm('Are you sure you want to delete this item?')) return;
    
    showLoadingModal(message = "Deleting...");
    
    try {
        const endpoint = currentSection === 'private' ? 'api/customers.php' : 'api/companies.php';
        const response = await fetch(`${endpoint}?action=delete&id=${id}`);
        const result = await response.json();
        
        if (result.success) {
            showResponseModal('Item deleted successfully');
            loadData();
        } else {
            showResponseModal('Failed to delete item: ' + result.error);
        }
    } catch (error) {
        showResponseModal('Failed to delete item: ' + error.message);
    } finally {
        hideLoadingModal();
    }
}

function closeEditModal() {
    document.getElementById('edit-modal').style.display = 'none';
}

// Add new item functions
function openAddClientModal() {
    document.getElementById('add-modal').style.display = 'flex';
}

async function saveAdd(event) {
    event.preventDefault();
    showLoadingModal(message = "Saving...");    
    try {
        const endpoint = currentSection === 'private' ? 'api/customers.php' : 'api/companies.php';
        const formData = currentSection === 'private'
            ? {
                first_name: document.getElementById('add-first-name').value,
                last_name: document.getElementById('add-last-name').value,
                email: document.getElementById('add-email').value,
                phone: document.getElementById('add-phone').value,
                address_line1: document.getElementById('add-address-line1').value,
                city: document.getElementById('add-city').value,
                postal_code: document.getElementById('add-postal-code').value,
                country: document.getElementById('add-country').value
            }
            : {
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
        
        const response = await fetch(`${endpoint}?action=add`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(formData)
        });
        
        const result = await response.json();
        
        if (result.success) {
            showResponseModal('Item added successfully');
            closeAddModal();
            loadData();
        } else {
            showResponseModal('Failed to add item: ' + result.error);
        }
    } catch (error) {
        showResponseModal('Failed to add item: ' + error.message);
    } finally {
        hideLoadingModal();
    }
}

function closeAddModal() {
    document.getElementById('add-modal').style.display = 'none';
}

// Update modal forms
function updateModalForms() {
    const type = document.getElementById('clientType').value;
    document.getElementById('companyFields').style.display = type === 'company' ? 'block' : 'none';
    document.getElementById('customerFields').style.display = type === 'customer' ? 'block' : 'none';
}

// Function to open customer modal
function openCustomerModal(customerId) {
    const modal = document.getElementById('customerModal');
    const form = document.getElementById('customerForm');
    
    // Fetch customer data
    fetch(`../api/customers.php?action=get&id=${customerId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                const customer = data.data;
                
                // Populate form fields
                document.getElementById('customerId').value = customer.customer_id;
                document.getElementById('customerInitials').value = customer.customer_initials;
                document.getElementById('customerTitle').value = customer.customer_title;
                document.getElementById('customerName').value = customer.first_name;
                document.getElementById('customerSurname').value = customer.last_name;
                document.getElementById('customerDOB').value = customer.dob;
                document.getElementById('customerGender').value = customer.gender;
                document.getElementById('customerLoyalty').value = customer.loyalty_level;
                document.getElementById('customerEmail').value = customer.email;
                document.getElementById('customerCell').value = customer.phone;
                document.getElementById('custAddrLine1').value = customer.address_line1;
                document.getElementById('custAddrLine2').value = customer.address_line2;
                document.getElementById('custSuburb').value = customer.suburb;
                document.getElementById('custProvince').value = customer.province;
                document.getElementById('custCity').value = customer.city;
                document.getElementById('custPostalCode').value = customer.postal_code;
                document.getElementById('custCountry').value = customer.country;
                
                // Show modal
                modal.style.display = 'block';
                
                // Set up form submission handler
                form.onsubmit = async function(e) {
                    e.preventDefault();
                    
                    // Collect form data
                    const formData = {
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
                        customerTel: document.getElementById('customerTel').value,
                        custAddrLine1: document.getElementById('custAddrLine1').value,
                        custAddrLine2: document.getElementById('custAddrLine2').value,
                        custSuburb: document.getElementById('custSuburb').value,
                        custProvince: document.getElementById('custProvince').value,
                        custCity: document.getElementById('custCity').value,
                        custPostalCode: document.getElementById('custPostalCode').value,
                        custCountry: document.getElementById('custCountry').value
                    };
                    
                    try {
                        const response = await fetch('../api/customers.php?action=update', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(formData)
                        });
                        
                        const result = await response.json();
                        
                        if (result.success) {
                            showResponseModal('Success', result.message);
                            modal.style.display = 'none';
                            loadData(); // Refresh the list
                        } else {
                            showResponseModal('Error', result.error);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showResponseModal('Error', 'An error occurred while updating the customer');
                    }
                };
            } else {
                showResponseModal('Error', data.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showResponseModal('Error', 'An error occurred while fetching customer data');
        });
}

// Function to open company modal
function openCompanyModal(companyId) {
    console.log(`Opening company modal for ID: ${companyId}`);
    const modal = document.getElementById('companyModal');
    if (!modal) {
        console.error('Company modal element not found');
        return;
    }

    // Fetch company data
    fetch(`../api/companies.php?action=get&id=${companyId}`)
        .then(response => response.json())
        .then(result => {
            if (result.success) {
                const company = result.data;
                console.log('Company data:', company);

                // Populate form fields
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

                // Show modal
                modal.style.display = 'block';

                // Initialize tabs
                initializeTabs('companyModal');

                // Set up form submission handler
                const form = document.getElementById('companyForm');
                form.onsubmit = async function(e) {
                    e.preventDefault();
                    
                    const formData = {
                        id: company.company_id,
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

                    try {
                        const response = await fetch('../api/companies.php?action=update', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify(formData)
                        });

                        const result = await response.json();
                        
                        if (result.success) {
                            showResponseModal('Success', result.message);
                            modal.style.display = 'none';
                            loadData(); // Refresh the list
                        } else {
                            showResponseModal('Error', result.error);
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        showResponseModal('Error', 'An error occurred while updating the company');
                    }
                };
            } else {
                showResponseModal('Error', result.error);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showResponseModal('Error', 'An error occurred while fetching company data');
        });
}

// Add event listeners for modal close buttons
document.addEventListener('DOMContentLoaded', function() {
    console.log('Setting up modal close handlers');
    
    // Close buttons for modals
    const closeCustomerModal = document.getElementById('closeCustomerModal');
    const closeCompanyModal = document.getElementById('closeCompanyModal');
    const cancelCustomer = document.getElementById('cancelCustomer');
    const cancelCompany = document.getElementById('cancelCompany');

    if (closeCustomerModal) {
        closeCustomerModal.addEventListener('click', function() {
            console.log('Closing customer modal');
            document.getElementById('customerModal').style.display = 'none';
        });
    } else {
        console.error('Customer modal close button not found');
    }

    if (closeCompanyModal) {
        closeCompanyModal.addEventListener('click', function() {
            console.log('Closing company modal');
            document.getElementById('companyModal').style.display = 'none';
        });
    } else {
        console.error('Company modal close button not found');
    }

    if (cancelCustomer) {
        cancelCustomer.addEventListener('click', function() {
            console.log('Canceling customer modal');
            document.getElementById('customerModal').style.display = 'none';
        });
    } else {
        console.error('Customer modal cancel button not found');
    }

    if (cancelCompany) {
        cancelCompany.addEventListener('click', function() {
            console.log('Canceling company modal');
            document.getElementById('companyModal').style.display = 'none';
        });
    } else {
        console.error('Company modal cancel button not found');
    }

    // Close modals when clicking outside
    window.addEventListener('click', function(event) {
        const customerModal = document.getElementById('customerModal');
        const companyModal = document.getElementById('companyModal');
        
        if (event.target === customerModal) {
            console.log('Closing customer modal (click outside)');
            customerModal.style.display = 'none';
        }
        if (event.target === companyModal) {
            console.log('Closing company modal (click outside)');
            companyModal.style.display = 'none';
        }
    });
});

// Initialize tabs for both modals
function initializeTabs() {
    // Customer modal tabs
    const customerModal = document.getElementById('add-modal');
    if (customerModal) {
        const customerTabs = customerModal.querySelectorAll('.modal-tab');
        const customerTabContents = customerModal.querySelectorAll('.tab-content');
        const customerForm = document.getElementById('addCustomerForm');

        customerTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                customerTabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                tab.classList.add('active');

                // Hide all tab contents and remove required attributes
                customerTabContents.forEach(content => {
                    content.classList.remove('active');
                    content.style.display = 'none';
                    const requiredFields = content.querySelectorAll('[required]');
                    requiredFields.forEach(field => field.removeAttribute('required'));
                });

                // Show the selected tab content and add required attributes
                const tabId = tab.getAttribute('data-tab');
                const activeContent = customerModal.querySelector(`.tab-content[data-tab-content="${tabId}"]`);
                if (activeContent) {
                    activeContent.classList.add('active');
                    activeContent.style.display = 'block';
                    const requiredFields = activeContent.querySelectorAll('[required]');
                    requiredFields.forEach(field => field.setAttribute('required', ''));
                }
            });
        });

        // Activate first tab by default
        if (customerTabs.length > 0) {
            customerTabs[0].click();
        }
    }

    // Company modal tabs (similar structure)
    const companyModal = document.getElementById('add-modal');
    if (companyModal) {
        const companyTabs = companyModal.querySelectorAll('.modal-tab');
        const companyTabContents = companyModal.querySelectorAll('.tab-content');
        const companyForm = document.getElementById('addCompanyForm');

        companyTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                // Remove active class from all tabs
                companyTabs.forEach(t => t.classList.remove('active'));
                // Add active class to clicked tab
                tab.classList.add('active');

                // Hide all tab contents and remove required attributes
                companyTabContents.forEach(content => {
                    content.classList.remove('active');
                    content.style.display = 'none';
                    const requiredFields = content.querySelectorAll('[required]');
                    requiredFields.forEach(field => field.removeAttribute('required'));
                });

                // Show the selected tab content and add required attributes
                const tabId = tab.getAttribute('data-tab');
                const activeContent = companyModal.querySelector(`.tab-content[data-tab-content="${tabId}"]`);
                if (activeContent) {
                    activeContent.classList.add('active');
                    activeContent.style.display = 'block';
                    const requiredFields = activeContent.querySelectorAll('[required]');
                    requiredFields.forEach(field => field.setAttribute('required', ''));
                }
            });
        });

        // Activate first tab by default
        if (companyTabs.length > 0) {
            companyTabs[0].click();
        }
    }
}

// Function to show add customer modal
function showAddCustomerModal() {
    const modal = document.getElementById('customerModal');
    const form = document.getElementById('customerForm');
    
    // Reset form
    form.reset();
    
    // Show modal
    modal.style.display = 'block';
    
    // Set up form submission handler
    form.onsubmit = async function(e) {
        e.preventDefault();
        
        // Get all form elements
        const formElements = form.elements;
        console.log('All form elements:', formElements);
        
        // Collect form data
        const formData = {
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

        console.log('Form data being sent:', formData);
        
        try {
            const response = await fetch('../api/customers.php?action=add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });
            
            const result = await response.json();
            console.log('API Response:', result);
            
            if (result.success) {
                showResponseModal('Success', result.message);
                modal.style.display = 'none';
                loadData(); // Refresh the list
            } else {
                showResponseModal('Error', result.error);
            }
        } catch (error) {
            console.error('Error:', error);
            showResponseModal('Error', 'An error occurred while adding the customer');
        }
    };
}

// Function to show add company modal
function showAddCompanyModal() {
    const modal = document.getElementById('add-modal');
    if (!modal) {
        console.error('Add company modal not found');
        return;
    }

    // Reset form
    const form = document.getElementById('addClientForm');
    if (form) {
        form.reset();
        
        // Show company fields
        document.getElementById('companyFields').style.display = 'block';
        document.getElementById('customerFields').style.display = 'none';
        
        // Set client type
        document.getElementById('clientType').value = 'company';

        // Add form submit handler
        form.onsubmit = async function(e) {
            e.preventDefault();
            console.log('Company form submitted');

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

            try {
                const response = await fetch('../api/companies.php?action=add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify(formData)
                });

                const data = await response.json();
                console.log('Add company response:', data);

                if (data.success) {
                    // Close modal
                    modal.style.display = 'none';
                    // Refresh company list
                    loadData();
                    // Show success message
                    showResponseModal('Company added successfully', 'success');
                } else {
                    showResponseModal(data.error || 'Failed to add company', 'error');
                }
            } catch (error) {
                console.error('Error adding company:', error);
                showResponseModal('An error occurred while adding the company', 'error');
            }
        };
    }

    // Show modal
    modal.style.display = 'block';

    // Setup close button
    const closeBtn = document.getElementById('closeAddClientModal');
    if (closeBtn) {
        closeBtn.onclick = function() {
            modal.style.display = 'none';
        };
    }

    // Close when clicking outside
    window.onclick = function(event) {
        if (event.target === modal) {
            modal.style.display = 'none';
        }
    };

    // Initialize tabs
    initializeTabs();
}

// Function to show response modal
function showResponseModal(message, type = 'info') {
    const modal = document.getElementById('responseModal');
    const messageElement = document.getElementById('responseMessage');
    
    if (modal && messageElement) {
        messageElement.textContent = message;
        messageElement.className = type; // Add class for styling (success, error, info)
        modal.style.display = 'block';
        
        // Auto close after 3 seconds
        setTimeout(() => {
            modal.style.display = 'none';
        }, 3000);
    }
} 