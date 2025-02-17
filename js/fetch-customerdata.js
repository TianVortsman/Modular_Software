document.addEventListener("DOMContentLoaded", function () {
    let currentPage = 1; // Current page
    let rowsPerPage = parseInt(document.getElementById("rows-per-page").value) || 10; // Default rows per page
    let totalCustomers = 0; // Total number of customers (fetched from the server)
    
    // Function to fetch customer data from the PHP file
    function fetchCustomerData(searchTerm = '') {
        const limit = document.getElementById("rows-per-page").value;
        const page = currentPage; // Use the current page

        // Fetch customer data with the search term from the PHP backend
        fetch(`../php/fetch-customers.php?searchTerm=${searchTerm}&limit=${limit}&page=${page}`)
            .then(response => response.json())
            .then(data => {
                const tableBody = document.getElementById('customer-body');
                tableBody.innerHTML = ''; // Clear the existing rows

                // Set the total customer count from the server response
                totalCustomers = data.totalCustomers;

                // Loop through the customer data and create table rows
                data.customers.forEach(customer => {
                    const row = document.createElement("tr");

                    // Add data to the row including the buttons in the last column
                    row.innerHTML = `
                        <td>${customer.company_name}</td>
                        <td>${customer.email}</td>
                        <td>${customer.account_number}</td>
                        <td>
                            <button class="button" onclick="openAddUserModal('${customer.account_number}')">Add User</button>
                            <button class="button" disabled>Placeholder</button>
                        </td>
                    `;
                    
                    row.addEventListener('dblclick', function () {
                        window.location.href = `dashboard.php?account_number=${customer.account_number}`;
                    });
                    // Append the row to the table body
                    tableBody.appendChild(row);
                });

                setupPagination(); // Reinitialize pagination
            })
            .catch(error => {
                console.error('Error fetching customer data:', error);
            });
    }

    // Function to handle dynamic page number buttons
    function setupPagination() {
        const totalPages = Math.ceil(totalCustomers / rowsPerPage); // Calculate total pages
        const paginationContainer = document.getElementById('pagination-container'); // Assuming this is where you want the buttons

        paginationContainer.innerHTML = ''; // Clear existing pagination buttons

        // If there are no pages, return
        if (totalPages === 0) return;

        // Create pagination buttons
        for (let page = 1; page <= totalPages; page++) {
            const button = document.createElement('button');
            button.textContent = page;
            button.classList.add('pagination-button');
            if (page === currentPage) {
                button.classList.add('active'); // Highlight the current page
            }

            button.addEventListener('click', function () {
                currentPage = page; // Set current page
                fetchCustomerData(); // Fetch the data for the new page
            });

            paginationContainer.appendChild(button);
        }
    }

    // Handle the change in rows per page
    document.getElementById("rows-per-page").addEventListener("change", function () {
        rowsPerPage = parseInt(this.value);
        currentPage = 1; // Reset to first page when changing rows per page
        fetchCustomerData(); // Re-fetch the data
    });

    // Attach the search functionality dynamically using event listener
    const searchInput = document.getElementById("search-bar");
    searchInput.addEventListener("keyup", function () {
        const searchTerm = searchInput.value.toLowerCase(); // Get the search term
        fetchCustomerData(searchTerm); // Fetch customer data with the search term
    });

    // Initial data fetch
    fetchCustomerData();
});
