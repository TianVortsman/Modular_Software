document.addEventListener("DOMContentLoaded", function () {
    let currentPage = 1;
    let rowsPerPage = parseInt(document.getElementById("rows-per-page").value) || 10;
    let totalCustomers = 0;
    let isLoading = false;
    
    // Make fetchCustomerData available globally
    window.fetchCustomerData = function(searchTerm = '') {
        if (isLoading) return; // Prevent multiple simultaneous requests
        isLoading = true;

        const statusFilter = document.getElementById("status-filter").value;
        const sortBy = document.getElementById("sort-by").value;
        const sortDirection = currentSort.direction;

        // Show loading state
        const tableBody = document.getElementById('customer-body');
        if (!tableBody) {
            console.error('Table body element not found');
            return;
        }

        // Add loading class to existing rows
        const rows = tableBody.getElementsByTagName('tr');
        Array.from(rows).forEach(row => {
            row.classList.add('loading');
            const cells = row.getElementsByTagName('td');
            Array.from(cells).forEach(cell => {
                cell.style.opacity = '0.5';
            });
        });

        // Build query parameters
        const params = new URLSearchParams({
            searchTerm: searchTerm,
            limit: rowsPerPage,
            page: currentPage,
            status: statusFilter,
            sortColumn: currentSort.column || sortBy,
            sortDirection: sortDirection
        });

        // Fetch customer data from the backend
        fetch(`../php/fetch-customers.php?${params.toString()}`)
            .then(response => {
                const contentType = response.headers.get("content-type");
                if (!contentType || !contentType.includes("application/json")) {
                    throw new Error("Server returned non-JSON response");
                }
                return response.json().then(data => {
                    if (!response.ok) {
                        throw new Error(data.error || `Server error: ${response.status}`);
                    }
                    return data;
                });
            })
            .then(data => {
                if (!data || !Array.isArray(data.customers)) {
                    throw new Error('Invalid data format received from server');
                }

                totalCustomers = data.totalCustomers || 0;

                // Update customer count
                const countElement = document.querySelector('.customer-count');
                if (countElement) {
                    countElement.textContent = `${totalCustomers} customers`;
                }

                // Prepare the new rows
                const fragment = document.createDocumentFragment();
                data.customers.forEach(customer => {
                    if (!customer || !customer.id) {
                        console.error('Invalid customer data:', customer);
                        return;
                    }

                    const row = document.createElement("tr");
                    row.classList.add('customer-row');
                    row.setAttribute('data-customer-id', customer.id);

                    // Safely get customer data with defaults
                    const customerData = {
                        id: parseInt(customer.id) || 0,
                        customer_name: customer.customer_name || customer.company_name || 'N/A',
                        company_name: customer.company_name || 'N/A',
                        email: customer.email || 'N/A',
                        account_number: customer.account_number || 'N/A',
                        total_devices: parseInt(customer.total_devices) || 0,
                        active_devices: parseInt(customer.active_devices) || 0,
                        status: (customer.status || 'unknown').toLowerCase(),
                        last_login: customer.last_login || null
                    };

                    // Verify customer ID before creating row
                    if (!customerData.id) {
                        console.error('Invalid customer ID:', customerData);
                        return;
                    }

                    const statusClass = getStatusClass(customerData.status);

                    row.innerHTML = `
                        <td>${escapeHtml(customerData.customer_name)}</td>
                        <td>${escapeHtml(customerData.company_name)}</td>
                        <td>${escapeHtml(customerData.email)}</td>
                        <td>${escapeHtml(customerData.account_number)}</td>
                        <td>
                            <div class="device-stats">
                                <span class="device-total">${customerData.total_devices}</span>
                                <span class="device-active">${customerData.active_devices} active</span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge ${statusClass}">${capitalizeFirst(customerData.status)}</span>
                        </td>
                        <td>${customerData.last_login ? formatDate(customerData.last_login) : 'Never'}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="icon-button" onclick="viewCustomer(${customerData.id})" title="View Details">
                                    <i class="material-icons">visibility</i>
                                </button>
                                <button class="icon-button" onclick="editCustomer(${customerData.id})" title="Edit">
                                    <i class="material-icons">edit</i>
                                </button>
                                <button class="icon-button" onclick="manageDevices(${customerData.id})" title="Manage Devices">
                                    <i class="material-icons">devices</i>
                                </button>
                                <button class="icon-button danger" onclick="deleteCustomer(${customerData.id})" title="Delete">
                                    <i class="material-icons">delete</i>
                                </button>
                            </div>
                        </td>
                    `;

                    fragment.appendChild(row);
                });

                // Update the table content
                requestAnimationFrame(() => {
                    tableBody.innerHTML = '';
                    tableBody.appendChild(fragment);
                    
                    // Attach double-click handlers to all rows
                    attachRowEventListeners();

                    // Fade in the new rows
                    const newRows = tableBody.getElementsByTagName('tr');
                    Array.from(newRows).forEach(row => {
                        row.style.opacity = '0';
                        requestAnimationFrame(() => {
                            row.style.opacity = '1';
                            row.style.transition = 'opacity 0.2s ease-in';
                        });
                    });
                });

                updatePagination();
                updateShowingInfo();
            })
            .catch(error => {
                console.error('Error fetching customer data:', error);
                tableBody.innerHTML = `
                    <tr>
                        <td colspan="8" class="error-message">
                            Failed to fetch customer data: ${error.message}
                            <button onclick="window.fetchCustomerData()" class="retry-button">
                                <i class="material-icons">refresh</i> Retry
                            </button>
                        </td>
                    </tr>
                `;
                showToast('Failed to fetch customer data. Please try again.', 'error');
            })
            .finally(() => {
                isLoading = false;
            });
    };

    // Function to attach event listeners to rows
    function attachRowEventListeners() {
        const rows = document.querySelectorAll('#customer-body tr.customer-row');
        rows.forEach(row => {
            // Remove existing event listeners if any
            row.removeEventListener('dblclick', handleRowDoubleClick);
            // Add new event listener
            row.addEventListener('dblclick', handleRowDoubleClick);
        });
    }

    // Function to handle row double-click
    function handleRowDoubleClick(event) {
        const row = event.currentTarget;
        const customerId = row.getAttribute('data-customer-id');
        if (customerId) {
            window.openManageCustomerModal(parseInt(customerId));
        } else {
            console.error('No customer ID found on row');
            showToast('Error: Unable to open customer details', 'error');
        }
    }

    // Helper function to get status class
    function getStatusClass(status) {
        const statusMap = {
            'active': 'active',
            'inactive': 'inactive',
            'pending': 'pending',
            'unknown': 'unknown'
        };
        return statusMap[status] || 'unknown';
    }

    // Helper function to capitalize first letter
    function capitalizeFirst(str) {
        return str.charAt(0).toUpperCase() + str.slice(1);
    }

    // Helper function to escape HTML
    function escapeHtml(unsafe) {
        return unsafe
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Helper function to format date
    function formatDate(dateString) {
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) {
                throw new Error('Invalid date');
            }
            return date.toLocaleString('en-US', {
                year: 'numeric',
                month: '2-digit',
                day: '2-digit',
                hour: '2-digit',
                minute: '2-digit'
            });
        } catch (error) {
            console.error('Date formatting error:', error);
            return 'Invalid date';
        }
    }

    // Function to update pagination
    function updatePagination() {
        const totalPages = Math.ceil(totalCustomers / rowsPerPage);
        const pageNumbers = document.getElementById('page-numbers');
        pageNumbers.innerHTML = '';

        // Calculate range of pages to show
        let startPage = Math.max(1, currentPage - 2);
        let endPage = Math.min(totalPages, startPage + 4);
        
        if (endPage - startPage < 4) {
            startPage = Math.max(1, endPage - 4);
        }

        // Create page number buttons
        for (let i = startPage; i <= endPage; i++) {
            const button = document.createElement('button');
            button.classList.add('pagination-button');
            if (i === currentPage) button.classList.add('active');
            button.textContent = i;
            button.onclick = () => goToPage(i);
            pageNumbers.appendChild(button);
        }

        // Update button states
        document.querySelector('button[onclick="goToFirstPage()"]').disabled = currentPage === 1;
        document.querySelector('button[onclick="goToPreviousPage()"]').disabled = currentPage === 1;
        document.querySelector('button[onclick="goToNextPage()"]').disabled = currentPage === totalPages;
        document.querySelector('button[onclick="goToLastPage()"]').disabled = currentPage === totalPages;
    }

    // Function to update showing info
    function updateShowingInfo() {
        const start = (currentPage - 1) * rowsPerPage + 1;
        const end = Math.min(start + rowsPerPage - 1, totalCustomers);
        
        document.getElementById('showing-start').textContent = totalCustomers === 0 ? 0 : start;
        document.getElementById('showing-end').textContent = end;
        document.getElementById('total-entries').textContent = totalCustomers;
    }

    // Pagination navigation functions
    window.goToFirstPage = () => {
        if (currentPage !== 1) {
            currentPage = 1;
            window.fetchCustomerData();
        }
    };

    window.goToPreviousPage = () => {
        if (currentPage > 1) {
            currentPage--;
            window.fetchCustomerData();
        }
    };

    window.goToNextPage = () => {
        const totalPages = Math.ceil(totalCustomers / rowsPerPage);
        if (currentPage < totalPages) {
            currentPage++;
            window.fetchCustomerData();
        }
    };

    window.goToLastPage = () => {
        const totalPages = Math.ceil(totalCustomers / rowsPerPage);
        if (currentPage !== totalPages) {
            currentPage = totalPages;
            window.fetchCustomerData();
        }
    };

    window.goToPage = (page) => {
        if (page !== currentPage) {
            currentPage = page;
            window.fetchCustomerData();
        }
    };

    // Event listeners
    document.getElementById("rows-per-page").addEventListener("change", function () {
        rowsPerPage = parseInt(this.value);
        currentPage = 1;
        window.fetchCustomerData();
    });

    document.getElementById("search-bar").addEventListener("keyup", function () {
        currentPage = 1; // Reset to first page when searching
        window.fetchCustomerData(this.value.trim());
    });

    document.getElementById("status-filter").addEventListener("change", function () {
        currentPage = 1;
        window.fetchCustomerData();
    });

    document.getElementById("sort-by").addEventListener("change", function () {
        currentPage = 1;
        window.fetchCustomerData();
    });

    // Error message helper
    function showToast(message, type) {
        // Implement your toast notification logic here
        console.log(message);
    }

    // Initial data fetch
    window.fetchCustomerData();
});
