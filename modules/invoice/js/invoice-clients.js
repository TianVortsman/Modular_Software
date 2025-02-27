// Toggle add client modal fields based on client type
document.getElementById('clientType').addEventListener('change', function() {
    const type = this.value;
    const companyFields = document.getElementById('companyFields');
    const customerFields = document.getElementById('customerFields');

    if (type === 'company') {
        companyFields.style.display = 'flex';
        customerFields.style.display = 'none';
        // Enable company fields and disable customer fields
        companyFields.querySelectorAll('input, select, textarea').forEach(el => el.disabled = false);
        customerFields.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);
    } else if (type === 'customer') {
        customerFields.style.display = 'flex';
        companyFields.style.display = 'none';
        // Enable customer fields and disable company fields
        customerFields.querySelectorAll('input, select, textarea').forEach(el => el.disabled = false);
        companyFields.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);
    } else {
        // If no client type is selected, hide and disable both sections
        companyFields.style.display = 'none';
        customerFields.style.display = 'none';
        companyFields.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);
        customerFields.querySelectorAll('input, select, textarea').forEach(el => el.disabled = true);
    }
});


document.getElementById('closeAddClientModal').onclick = function() {
    document.getElementById('addClientModal').style.display = "none";
}

function openAddClientModal() {
    // Reset client type and hide both fields initially
    document.getElementById('clientType').value = "";
    document.getElementById('companyFields').style.display = "none";
    document.getElementById('customerFields').style.display = "none";
    document.getElementById('addClientModal').style.display = "flex";
}

document.getElementById('addClientForm').addEventListener('submit', function(event) {
    event.preventDefault();
    const clientType = document.getElementById('clientType').value;
    
    if (!clientType) {
        showResponseModal("Please select a client type.", false);
        return;
    }
    
    // Since we've disabled the hidden fields, all data in the formData is relevant
    const formData = new FormData(this);
    
    showLoadingModal();
    fetch('../php/save-client-details.php', {
        method: 'POST',
        body: formData,
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`Server returned ${response.status} ${response.statusText}`);
        }
        return response.json();
    })
    .then(data => {
        hideLoadingModal();
        if (data.status === 'success') {
            showResponseModal(data.status, data.message); // Passing status and message
            document.getElementById('addClientModal').style.display = "none"; // Close the modal
        } else {
            showResponseModal(data.status, "Error: " + data.message); // Display error
        }
    })
    .catch(error => {
        hideLoadingModal();
        console.error("Fetch error:", error);
        showResponseModal("error", "A server error occurred: " + error.message); // Server error
    });
});


document.addEventListener('DOMContentLoaded', function () {
    const clientSection1 = document.getElementById('client-section1');
    const clientSection2 = document.getElementById('client-section2');
    const clientSectionButton1 = document.getElementById('clientSectionButton1');
    const clientSectionButton2 = document.getElementById('clientSectionButton2');
    document.getElementById('addClientModal').style.display = "none";
    document.getElementById('companyModal').style.display = "none";
    document.getElementById('customerModal').style.display = "none";
    let currentPage = 1;
    let rowsPerPage = 10; // Default rows per page
    const companyForm = document.getElementById('companyForm');
    const customerForm = document.getElementById('customerForm');

    companyForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(companyForm);

        showLoadingModal();
        fetch('../php/save-company-details.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Server returned ${response.status} ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            hideLoadingModal();
            showResponseModal(data.status, data.message);
        })
        .catch(error => {
            hideLoadingModal();
            console.error("Fetch error:", error);
            showResponseModal("error", "A server error occurred: " + error.message);
        });
    });

    customerForm.addEventListener('submit', function(event) {
        event.preventDefault();
        const formData = new FormData(customerForm);
        
        showLoadingModal();
        fetch('../php/save-customer-details.php', {
            method: 'POST',
            body: formData,
        })
        .then(response => {
            if (!response.ok) {
                throw new Error(`Server returned ${response.status} ${response.statusText}`);
            }
            return response.json();
        })
        .then(data => {
            hideLoadingModal();
            showResponseModal(data.status, data.message);
        })
        .catch(error => {
            hideLoadingModal();
            console.error("Fetch error:", error);
            showResponseModal("error", "A server error occurred: " + error.message);
        });
    });
    
    
    // Get both "rows per page" dropdowns
    const rowsPerPageDropdowns = document.querySelectorAll(".rows-per-page");

    // Switch between sections
    clientSectionButton1.addEventListener('click', function () {
        switchSection(1);
    });

    clientSectionButton2.addEventListener('click', function () {
        switchSection(2);
    });

    function switchSection(sectionNumber) {
        clientSection1.classList.toggle('active', sectionNumber === 1);
        clientSection2.classList.toggle('active', sectionNumber === 2);
        clientSectionButton1.classList.toggle('active', sectionNumber === 1);
        clientSectionButton2.classList.toggle('active', sectionNumber === 2);

        currentPage = 1;
        rowsPerPage = getRowsPerPage(sectionNumber);
        fetchClientData(sectionNumber);
    }

    function getRowsPerPage(sectionNumber) {
        const activeDropdown = sectionNumber === 1
            ? document.querySelector("#client-section1 .rows-per-page")
            : document.querySelector("#client-section2 .rows-per-page");
        return parseInt(activeDropdown.value);
    }

    // Attach event listeners to both dropdowns
    rowsPerPageDropdowns.forEach(dropdown => {
        dropdown.addEventListener("change", function () {
            const customerType = clientSection1.classList.contains('active') ? 1 : 2;
            rowsPerPage = parseInt(this.value);
            currentPage = 1;
            fetchClientData(customerType);
        });
    });

    // Function to fetch client data
    async function fetchClientData(customerType, searchTerm = '') {
        try {
            const response = await fetch(`../php/get-invoice-customers.php?customerType=${customerType}&searchTerm=${searchTerm}&limit=${rowsPerPage}&page=${currentPage}`);
            const data = await response.json();

            if (data.error) {
                console.error('Error:', data.error);
                return;
            }

            totalCustomers = data.totalCustomers;

            const tableBody = customerType === 1 
                ? document.querySelector('#client-section1 table tbody') 
                : document.querySelector('#client-section2 table tbody');
            tableBody.innerHTML = '';

            data.customers.forEach(customer => {
                const row = document.createElement('tr');
                row.dataset.customerId = customerType === 1 ? customer.cust_id : ''; // Set data attribute
                row.dataset.customerType = customerType; // Set customer type data attribute
                row.dataset.companyId = customerType === 2 ? customer.company_id : ''; // Set company data attribute for customer type 2    
                if (customerType === 1) {
                    row.innerHTML = `
                        <td>${customer.cust_id}</td>
                        <td>${customer.cust_fname}</td>
                        <td>${customer.cust_email}</td>
                        <td>${customer.cust_cell}</td>
                        <td>${customer.last_invoice_date || 'N/A'}</td>
                        <td>${customer.total_invoice_amount}</td>
                        <td>${customer.customer_status}</td>
                    `;
                    
                } else {
                    row.innerHTML = `
                        <td>${customer.company_id}</td>
                        <td>${customer.company_name}</td>
                        <td>${customer.company_email}</td>
                        <td>${customer.company_tell}</td>
                        <td>${customer.last_invoice_date || 'N/A'}</td>
                        <td>${customer.total_invoice_amount}</td>
                        <td>${customer.comp_status}</td>
                    `;
                }
                
                tableBody.appendChild(row);
            });

            setupPagination(customerType);
            addRowDoubleClickListeners(); // Add event listeners after updating the table content
        } catch (error) {
            hideLoadingModal();
            console.error('Error fetching customer data:', error);
        }
    }

    function addRowDoubleClickListeners() {
        const rows = document.querySelectorAll('tr[data-customer-id]');
        rows.forEach(row => {
            row.addEventListener('dblclick', function() {
                const companyId = row.dataset.companyId;
                const customerId = row.dataset.customerId;
                const customerType = row.dataset.customerType;
                if (customerType == 1) {
                    openCustomerModal(customerId);
                } else {
                    openCompanyModal(companyId);
                }
            });
        });
    }

    function setupPagination(customerType) {
        const totalPages = Math.ceil(totalCustomers / rowsPerPage);
        const paginationContainer = customerType === 1 
            ? document.getElementById('pagination-container1') 
            : document.getElementById('pagination-container2');

        paginationContainer.innerHTML = '';

        if (totalPages === 0) return;

        for (let page = 1; page <= totalPages; page++) {
            const button = document.createElement('button');
            button.textContent = page;
            button.classList.add('pagination-button');
            if (page === currentPage) {
                button.classList.add('active');
            }

            button.addEventListener('click', function () {
                currentPage = page;
                fetchClientData(customerType);
            });

            paginationContainer.appendChild(button);
        }
    }

    // Attach the search functionality dynamically using event listener
    const searchInput = document.getElementById("client-search");
    searchInput.addEventListener("keyup", function () {
        const searchTerm = searchInput.value.toLowerCase(); // Get the search term
        const customerType = clientSection1.classList.contains('active') ? 1 : 2; // Determine the customer type
        fetchClientData(customerType, searchTerm); // Fetch customer data with the search term
    });

    // Initial data fetch
    fetchClientData(1);
    // Initial load
    switchSection(1);

    document.getElementById('closeCompanyModal').onclick = function() {
        document.getElementById('companyModal').style.display = "none";
    }

    document.getElementById('closeCustomerModal').onclick = function() {
        document.getElementById('customerModal').style.display = "none";
    }

    function openCustomerModal(customerId) {
        showLoadingModal();
        fetch(`../php/get-customer-details.php?cust_id=${customerId}`)
            .then(response => response.json())
            .then(data => {
                hideLoadingModal();
                const cust = data.find(cust => cust.cust_id == customerId);
                if (cust) {
                    document.getElementById('customerId').value = cust.cust_id;
                    document.getElementById('customerInitials').value = cust.cust_init;
                    document.getElementById('customerTitle').value = cust.cust_title;
                    document.getElementById('customerName').value = cust.cust_fname;
                    document.getElementById('customerSurname').value = cust.cust_lname;
                    document.getElementById('customerEmail').value = cust.cust_email;
                    document.getElementById('customerCell').value = cust.cust_cell;
                    document.getElementById('customerTel').value = cust.cust_tel;
                    document.getElementById('custAddrLine1').value = cust.addr_line_1;
                    document.getElementById('custAddrLine2').value = cust.addr_line_2;
                    document.getElementById('custCity').value = cust.city;
                    document.getElementById('custSuburb').value = cust.suburb;
                    document.getElementById('custProvince').value = cust.province;
                    document.getElementById('custPostalCode').value = cust.postcode;
                    document.getElementById('custCountry').value = cust.country;
                    document.getElementById('customerDOB').value = cust.cust_dob;
                    document.getElementById('customerGender').value = cust.cust_gender;
                    document.getElementById('customerLoyalty').value = cust.cust_loyalty;
                    document.getElementById('customerNotes').value = cust.cust_notes;
                    document.getElementById('customerModal').style.display = "flex";
                }
            })
            .catch(error => {
                hideLoadingModal();
                console.error('Error fetching customer details:', error);
            });
    }
    

    function openCompanyModal(companyId) {
        showLoadingModal();
        fetch(`../php/get-company-details.php?company_id=${companyId}`)
            .then(response => response.json())
            .then(data => {
                hideLoadingModal();
                const company = data.find(comp => comp.company_id == companyId);
                if (company) {
                    document.getElementById('companyId').value = company.company_id;
                    document.getElementById('companyName').value = company.company_name;
                    document.getElementById('companyTaxNo').value = company.company_tax_no;
                    document.getElementById('companyRegisNo').value = company.company_regis_no;
                    document.getElementById('companyType').value = company.company_type;
                    document.getElementById('companyIndustry').value = company.industry;
                    document.getElementById('companyWebsite').value = company.website;
                    document.getElementById('companyPhone').value = company.company_tell;
                    document.getElementById('companyEmail').value = company.company_email;
                    document.getElementById('addrLine1').value = company.addr_line_1;
                    document.getElementById('addrLine2').value = company.addr_line_2;
                    document.getElementById('suburb').value = company.suburb;
                    document.getElementById('city').value = company.city;
                    document.getElementById('province').value = company.province;
                    document.getElementById('country').value = company.country;
                    document.getElementById('postcode').value = company.postcode;
                    document.getElementById('contactName').value = company.contact_name;
                    document.getElementById('contactEmail').value = company.contact_email;
                    document.getElementById('contactPhone').value = company.contact_phone;
                    document.getElementById('contactPosition').value = company.position;
                    document.getElementById('companyModal').style.display = "flex";
                } else {
                    console.error('Company not found');
                }
            })
            .catch(error => {
                hideLoadingModal();
                console.error('Error fetching company details:', error);
            });
    }

    window.onclick = function(event) {
        if (event.target == document.getElementById('companyModal')) {
            document.getElementById('companyModal').style.display = "none";
        }
        if (event.target == document.getElementById('customerModal')) {
            document.getElementById('customerModal').style.display = "none";
        }
        if (event.target == document.getElementById('addClientModal')) {
            document.getElementById('addClientModal').style.display = "none";
        }
    }

    // Add event delegation for double-click to open modals
    document.addEventListener('dblclick', function(event) {
        const row = event.target.closest('tr');
        if (!row) return; // If clicked outside a row, do nothing

        const customerId = row.dataset.customerId; // Get the customer ID from the data attribute
        const customerType = row.dataset.customerType; // Get the customer type from the data attribute
        if (customerId) {
            if (customerType == 1) {
                openCustomerModal(customerId);
            } else {
                openCompanyModal(companyId);
            }
        }
    });

});