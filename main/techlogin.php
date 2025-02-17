<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="../css/root.css">
    <link rel="stylesheet" href="../css/techlogin.css">
    <script src="../js/toggle-theme.js" type="module"></script>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
</head>
<body>
    <div class="admin-header">
        <h1>Customer Management Dashboard</h1>
        <div class="header-actions">
            <button onclick="openAddCustomerModal()" class="button">Add Customer</button>
        </div>
    </div>

    <div class="search-container">
        <div class="search-icon">
            <i class="material-icons">search</i>
        </div>
        <input type="text" id="search-bar" placeholder="Search customers..." onkeyup="searchCustomers()">
            <!-- Pagination Controls -->

        <div id="pagination-container" class="pagination-container"></div>
    </div>

    <table id="customer-table">
        <thead>
        <label for="rows-per-page">Show:</label>
            <select id="rows-per-page" class="rows-per-page">
            <option value="5">5</option>
            <option value="10" selected>10</option>
            <option value="25">25</option>
            <option value="50">50</option>
        </select>
            <tr>
                <th>Company Name</th>
                <th>Email</th>
                <th>Account Number</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody id="customer-body">
            <!-- Rows will be populated dynamically via JavaScript -->
        </tbody>
    </table>

    <footer class="admin-footer">
        <p>&copy; <?= date('Y') ?> Modular Software. All rights reserved.</p>
    </footer>

    <!-- Add User Modal -->
    <div id="add-user-modal" class="add-user-modal">
        <div class="add-user-modal-content">
            <span class="close" onclick="closeAddUserModal()">&times;</span>
            <h2>Add New User</h2>
            <form id="add-user-form" action="../php/add-user.php" method="POST">
                <label for="name">Name:</label>
                <input type="text" id="name" name="name" required>
                
                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required>
                
                <label for="role">Role:</label>
                <select class="user-role" name="role" id="role">
                    <option value="1">Superuser</option>
                    <option value="2">Finance Officer</option>
                    <option value="3">Administrator</option>
                    <option value="4">Manager</option>
                    <option value="5">User</option>
                    <option value="6">Viewer</option>
                </select>
                
                <!-- Hidden input for account number -->
                <input type="hidden" id="account_number" name="account_number" required>

                <button type="submit" class="button">Add User</button>
            </form>
        </div>
    </div>

    <!-- Add Customer Modal -->
    <div id="add-customer-modal" class="modal">
        <div class="modal-content">
            <span class="close" onclick="closeAddCustomerModal()">&times;</span>
            <h2>Add New Customer</h2>
            <div class="tabs">
                <button class="tab-button active" onclick="openTab(event, 'general-info')">General Info</button>
                <button class="tab-button" onclick="openTab(event, 'modules-access')">Modules Access</button>
                <button class="tab-button" onclick="openTab(event, 'contact-details')">Contact Details</button>
                <button class="tab-button" onclick="openTab(event, 'additional-info')">Additional Info</button>
            </div>
            <form id="add-customer-form" action="php/add_customer.php" method="POST">
                <!-- General Info Tab -->
                <div id="general-info" class="tab-content active">
                    <label for="company_name">Company Name:</label>
                    <input type="text" id="company_name" name="company_name" required>

                    <label for="customer_email">Email:</label>
                    <input type="email" id="customer_email" name="email" required>

                    <label for="account_number">Account Number:</label>
                    <input type="text" id="account_number" name="account_number" required>

                    <label for="start_date">Start Date:</label>
                    <input type="date" id="start_date" name="start_date" required value="currentDate">
                </div>

                <!-- Modules Access Tab -->
                <div id="modules-access" class="tab-content">
                    <label>Select Modules:</label>
                    <div class="checkbox-group">
                        <label><input type="checkbox" name="modules[]" value="Time & Attendance"> Time & Attendance</label>
                        <label><input type="checkbox" name="modules[]" value="Payroll Management"> Payroll Management</label>
                        <label><input type="checkbox" name="modules[]" value="CRM"> CRM</label>
                        <label><input type="checkbox" name="modules[]" value="Inventory"> Inventory</label>
                        <label><input type="checkbox" name="modules[]" value="Invoicing"> Invoicing</label>
                        <label><input type="checkbox" name="modules[]" value="HR Management"> HR Management</label>
                        <label><input type="checkbox" name="modules[]" value="Sales"> Sales</label>
                        <label><input type="checkbox" name="modules[]" value="Marketing"> Marketing</label>
                        <label><input type="checkbox" name="modules[]" value="Support"> Support</label>
                        <label><input type="checkbox" name="modules[]" value="Analytics"> Analytics</label>
                        <label><input type="checkbox" name="modules[]" value="E-commerce"> E-commerce</label>
                        <label><input type="checkbox" name="modules[]" value="Project Management"> Project Management</label>
                    </div>
                </div>

                <!-- Contact Details Tab -->
                <div id="contact-details" class="tab-content">
                    <label for="address">Address:</label>
                    <input type="text" id="address" name="address" required>

                    <label for="city">City:</label>
                    <input type="text" id="city" name="city" required>

                    <label for="site_contact">Site Contact:</label>
                    <input type="text" id="site_contact" name="site_contact" required>

                    <label for="phone">Phone Number:</label>
                    <input type="text" id="phone" name="phone" required>

                    <label for="secondary_contact">Secondary Contact:</label>
                    <input type="text" id="secondary_contact" name="secondary_contact">

                    <label for="contact_notes">Contact Notes:</label>
                    <textarea id="contact_notes" name="contact_notes" class="contact-notes"></textarea>
                </div>

                <!-- Additional Info Tab -->
                <div id="additional-info" class="tab-content">
                    <label for="notes">Additional Notes:</label>
                    <textarea id="notes" name="notes" class="customer-notes"></textarea>
                </div>

                <button type="submit" class="button">Add Customer</button>
            </form>
        </div>
    </div>
    <script src="../js/techlogin.js"></script>
    <script src="../js/add-customer.js"></script>
    <script src="/modular1/js/page-transitions.js"></script>
    <script src="../js/fetch-customerdata.js"></script>
</body>
</html>
