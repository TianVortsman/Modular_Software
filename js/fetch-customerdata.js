document.addEventListener("DOMContentLoaded", function () {
    let currentPage = 1;
    let rowsPerPage = parseInt(document.getElementById("rows-per-page").value) || 10;
    let totalCustomers = 0;
    let isLoading = false;
    
    // Make fetchCustomerData available globally
    window.fetchCustomerData = function(searchTerm = '') {
        if (isLoading) return;
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

                // Clear the table first
                tableBody.innerHTML = '';

                // Add each customer row
                data.customers.forEach(customer => {
                    const row = document.createElement('tr');
                    row.className = 'customer-row';
                    row.setAttribute('data-account', customer.account_number);
                    row.setAttribute('data-customerid', customer.id);
                    
                    row.innerHTML = `
                        <td>${escapeHtml(customer.customer_name || customer.company_name || 'N/A')}</td>
                        <td>${escapeHtml(customer.company_name || 'N/A')}</td>
                        <td>${escapeHtml(customer.email || 'N/A')}</td>
                        <td>${escapeHtml(customer.account_number || 'N/A')}</td>
                        <td>
                            <div class="device-stats">
                                <span class="device-total">${customer.total_devices || 0}</span>
                                <span class="device-active">${customer.active_devices || 0} active</span>
                            </div>
                        </td>
                        <td>
                            <span class="status-badge ${(customer.status || '').toLowerCase()}">${customer.status || 'Unknown'}</span>
                        </td>
                        <td>${formatDate(customer.last_login)}</td>
                        <td>
                            <div class="action-buttons">
                                <button class="icon-button" onclick="viewCustomer(${customer.id})" title="View Details">
                                    <i class="material-icons">visibility</i>
                                </button>
                                <button class="icon-button" onclick="editCustomer(${customer.id})" title="Edit">
                                    <i class="material-icons">edit</i>
                                </button>
                                <button class="icon-button" onclick="manageDevices(${customer.id})" title="Manage Devices">
                                    <i class="material-icons">devices</i>
                                </button>
                                <button class="icon-button danger" onclick="deleteCustomer(${customer.id})" title="Delete">
                                    <i class="material-icons">delete</i>
                                </button>
                            </div>
                        </td>
                    `;
                    
                    // Add double-click event listener
                    row.addEventListener('dblclick', handleRowDoubleClick);
                    
                    tableBody.appendChild(row);
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
                showResponseModal('error', 'Failed to fetch customer data. Please try again.');
            })
            .finally(() => {
                isLoading = false;
            });
    };

    // Function to handle row double-click
    async function handleRowDoubleClick(event) {
        const row = event.currentTarget;
        const accountNumber = row.getAttribute('data-account');
        const customerId = row.getAttribute('data-customerid');
        
        if (!accountNumber) {
            console.error('No account number found on row');
            showResponseModal('error', 'Unable to open customer details');
            return;
        }

        // Show loading modal first with proper error handling
        try {
            const loadingModal = document.getElementById('unique-loading-modal');
            if (loadingModal) {
                const messageElement = loadingModal.querySelector('.modal-message');
                if (messageElement) {
                    messageElement.textContent = 'Loading customer data...';
                }
                
                // First make it visible with display:flex
                loadingModal.classList.remove('hidden');
                loadingModal.style.display = 'flex';
                
                // Force a reflow to ensure display change is applied
                loadingModal.offsetHeight;
                
                // Now make it visible with opacity
                loadingModal.style.opacity = 1;
                loadingModal.style.visibility = 'visible';
            } else {
                console.error('Loading modal element not found');
            }

            // Fetch port data
            const response = await fetch(`../api/get-clock-server-port.php?account_number=${accountNumber}`);
            if (!response.ok) {
                console.error('Failed to fetch port with status:', response.status);
                throw new Error('Failed to fetch port');
            }
            
            const data = await response.json();
            
            if (data.success) {
                // Open the modal and switch to Clock Machines tab
                const modal = document.getElementById('customerModal');
                if (modal) {
                    // Store account number for later use
                    modal.dataset.accountNumber = accountNumber;
                    
                    // If we have a customer ID, store it and fetch customer details
                    if (customerId) {
                        currentCustomerId = customerId;
                        fetchCustomerDetails(customerId);
                    }
                    
                    // Show modal
                    modal.style.display = 'flex';
                    modal.classList.add('active');
                    
                    // Switch to Clock Machines tab and load data
                    const clockTab = document.querySelector('[data-tab="clock-machines"]');
                    if (clockTab) {
                        clockTab.click();
                    }
                    
                    // Update port input if it exists
                    const portInput = document.getElementById('clockServerPort');
                    if (portInput) {
                        portInput.value = data.port || '';
                    }
                    
                    // Prevent body scrolling
                    document.body.style.overflow = 'hidden';
                } else {
                    throw new Error('Modal not found');
                }
            } else {
                throw new Error(data.error || 'Failed to fetch port');
            }
        } catch (error) {
            console.error('Error:', error);
            showResponseModal('error', error.message || 'An error occurred');
        } finally {
            // Hide loading modal properly
            const loadingModal = document.getElementById('unique-loading-modal');
            if (loadingModal) {
                // First set opacity to 0 for fade out
                loadingModal.style.opacity = 0;
                
                // After animation completes, hide completely
                setTimeout(() => {
                    loadingModal.classList.add('hidden');
                    loadingModal.style.display = 'none';
                    loadingModal.style.visibility = 'hidden';
                }, 300);
            }
            
            // Extra fallback in case the loading modal doesn't hide properly
            setTimeout(() => {
                const modal = document.getElementById('unique-loading-modal');
                if (modal && modal.style.opacity !== "0") {
                    console.warn('Loading modal still visible - forcing hide');
                    modal.style.opacity = 0;
                    modal.style.display = 'none';
                    modal.style.visibility = 'hidden';
                    modal.classList.add('hidden');
                }
            }, 1000);
        }
    }
    
    // Expose handleRowDoubleClick globally
    window.handleRowDoubleClick = handleRowDoubleClick;

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
        if (!unsafe) return '';
        return unsafe
            .toString()
            .replace(/&/g, "&amp;")
            .replace(/</g, "&lt;")
            .replace(/>/g, "&gt;")
            .replace(/"/g, "&quot;")
            .replace(/'/g, "&#039;");
    }

    // Helper function to format date
    function formatDate(dateString) {
        if (!dateString) return 'Never';
        try {
            const date = new Date(dateString);
            if (isNaN(date.getTime())) {
                return 'Invalid date';
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

    // Initial data fetch
    window.fetchCustomerData();
});

// Function to create a customer row
function createCustomerRow(customer) {
    const row = document.createElement('tr');
    row.className = 'customer-row';
    row.setAttribute('data-account', customer.account_number);
    
    // Add double-click event listener
    row.addEventListener('dblclick', function() {
        const modal = document.getElementById('customerModal');
        if (modal) {
            // Store account number for later use
            modal.dataset.accountNumber = customer.account_number;
            
            // Show modal
            modal.style.display = 'flex';
            modal.classList.add('active');
            
            // Switch to Clock Machines tab
            const clockTab = document.querySelector('[data-tab="clock-machines"]');
            if (clockTab) {
                clockTab.click();
            }
            
            // Prevent body scrolling
            document.body.style.overflow = 'hidden';
        }
    });
    
    row.innerHTML = `
        <td>${escapeHtml(customer.name || customer.customer_name)}</td>
        <td>${escapeHtml(customer.company || customer.company_name)}</td>
        <td>${escapeHtml(customer.email)}</td>
        <td>${escapeHtml(customer.account_number)}</td>
        <td>
            <div class="device-stats">
                <span class="device-total">${customer.total_devices || 0}</span>
                <span class="device-active">${customer.active_devices || 0} active</span>
            </div>
        </td>
        <td>
            <span class="status-badge ${(customer.status || '').toLowerCase()}">${customer.status || 'Unknown'}</span>
        </td>
        <td>${formatDate(customer.last_login)}</td>
        <td>
            <div class="action-buttons">
                <button class="icon-button" onclick="viewCustomer(${customer.id})" title="View Details">
                    <i class="material-icons">visibility</i>
                </button>
                <button class="icon-button" onclick="editCustomer(${customer.id})" title="Edit">
                    <i class="material-icons">edit</i>
                </button>
                <button class="icon-button" onclick="manageDevices(${customer.id})" title="Manage Devices">
                    <i class="material-icons">devices</i>
                </button>
                <button class="icon-button danger" onclick="deleteCustomer(${customer.id})" title="Delete">
                    <i class="material-icons">delete</i>
                </button>
            </div>
        </td>
    `;
    return row;
}

// Function to handle double-click on customer row
async function handleCustomerRowDoubleClick(accountNumber) {
    try {
        // Get CSRF token from meta tag
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        
        const response = await fetch(`../api/get-clock-server-port.php?account_number=${accountNumber}`, {
            headers: {
                'X-CSRF-TOKEN': csrfToken
            }
        });

        if (!response.ok) throw new Error('Failed to fetch port');
        
        const data = await response.json();
        
        if (data.success) {
            // Open the modal and switch to Clock Machines tab
            const modal = document.getElementById('customerModal');
            if (modal) {
                // Store account number for later use
                modal.dataset.accountNumber = accountNumber;
                
                // Switch to Clock Machines tab
                const clockTab = document.querySelector('[data-tab="clock-machines"]');
                if (clockTab) {
                    clockTab.click();
                }
                
                // Update port input if it exists
                const portInput = document.getElementById('clockServerPort');
                if (portInput) {
                    portInput.value = data.port || '';
                }
                
                // Show modal
                modal.style.display = 'flex';
                modal.classList.add('active');
                
                // Add class to body to prevent scrolling
                document.body.style.overflow = 'hidden';
            } else {
                showToast('Error: Modal not found', 'error');
            }
        } else {
            showToast(data.error || 'Failed to fetch port', 'error');
        }
    } catch (error) {
        console.error('Error fetching port:', error);
        showToast('Failed to fetch port', 'error');
    }
}

// Function to fetch and display customer data
async function fetchCustomerData() {
    const tbody = document.getElementById('customer-body');
    if (!tbody) return;

    try {
        tbody.innerHTML = '<tr><td colspan="8" class="loading-state">Loading customers...</td></tr>';

        const response = await fetch('../php/get-customers.php');
        if (!response.ok) throw new Error('Failed to fetch customers');
        
        const data = await response.json();
        tbody.innerHTML = '';

        if (data.customers && data.customers.length > 0) {
            data.customers.forEach(customer => {
                const row = createCustomerRow(customer);
                tbody.appendChild(row);
            });

            // Update customer count
            const customerCount = document.querySelector('.customer-count');
            if (customerCount) {
                customerCount.textContent = `${data.customers.length} customers`;
            }
        } else {
            tbody.innerHTML = '<tr><td colspan="8" class="empty-state">No customers found</td></tr>';
        }
    } catch (error) {
        console.error('Error fetching customers:', error);
        tbody.innerHTML = '<tr><td colspan="8" class="error-state">Failed to load customers</td></tr>';
        showToast('Failed to load customers', 'error');
    }
}

// Initialize customer data on page load
document.addEventListener('DOMContentLoaded', () => {
    fetchCustomerData();
});