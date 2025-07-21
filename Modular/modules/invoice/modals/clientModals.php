<!-- Expanded Company Modal -->
<div id="companyModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h2>Edit Company</h2>
                <span class="close" id="closeCompanyModal">&times;</span>
            </div>

            <!-- Modal Tabs -->
            <div class="modal-tabs">
                <button class="modal-tab active" data-tab="basic">Basic Info</button>
                <button class="modal-tab" data-tab="address">Address</button>
                <button class="modal-tab" data-tab="contact">Contact</button>
                <button class="modal-tab" data-tab="invoices">Invoices</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="companyForm" action="../php/save-company-details.php" method="POST">
                    <input type="hidden" id="companyId" name="companyId">
                    
                    <!-- Basic Info Tab -->
                    <div class="tab-content active" data-tab-content="basic">
                        <fieldset class="basic-info">
                            <legend>Basic Information</legend>
                            <div class="form-group">
                                <label for="companyName">Company Name:</label>
                                <input type="text" id="companyName" name="companyName" required>
                            </div>
                            <div class="form-group">
                                <label for="companyVatNo">Vat Number:</label>
                                <input type="text" id="companyVatNo" name="companyVatNo">
                            </div>
                            <div class="form-group">
                                <label for="companyRegisNo">Registration Number:</label>
                                <input type="text" id="companyRegisNo" name="companyRegisNo">
                            </div>
                            <div class="form-group">
                                <label for="companyIndustry">Industry:</label>
                                <input type="text" id="companyIndustry" name="companyIndustry">
                            </div>
                            <div class="form-group">
                                <label for="companyWebsite">Website:</label>
                                <input type="url" id="companyWebsite" name="companyWebsite" placeholder="https://">
                            </div>
                            <div class="form-group">
                                <label for="companyPhone">Phone:</label>
                                <input type="text" id="companyPhone" name="companyPhone">
                            </div>
                            <div class="form-group">
                                <label for="companyEmail">Email:</label>
                                <input type="email" id="companyEmail" name="companyEmail">
                            </div>
                        </fieldset>
                    </div>

                    <!-- Address Tab -->
                    <div class="tab-content" data-tab-content="address">
                        <fieldset class="address-info">
                            <legend>Company Address</legend>
                            <div class="form-group">
                                <label for="addrLine1">Address Line 1:</label>
                                <input type="text" id="addrLine1" name="addrLine1">
                            </div>
                            <div class="form-group">
                                <label for="addrLine2">Address Line 2:</label>
                                <input type="text" id="addrLine2" name="addrLine2">
                            </div>
                            <div class="form-group">
                                <label for="suburb">Suburb:</label>
                                <input type="text" id="suburb" name="suburb">
                            </div>
                            <div class="form-group">
                                <label for="city">City:</label>
                                <input type="text" id="city" name="city">
                            </div>
                            <div class="form-group">
                                <label for="province">Province:</label>
                                <input type="text" id="province" name="province">
                            </div>
                            <div class="form-group">
                                <label for="country">Country:</label>
                                <input type="text" id="country" name="country">
                            </div>
                            <div class="form-group">
                                <label for="postcode">Postcode:</label>
                                <input type="text" id="postcode" name="postcode">
                            </div>
                        </fieldset>
                    </div>

                    <!-- Contact Tab -->
                    <div class="tab-content" data-tab-content="contact">
                        <fieldset class="contact-info">
                            <legend>Company Contact</legend>
                            <div class="form-group">
                                <label for="contactFirstName">Contact First Name:</label>
                                <input type="text" id="contactFirstName" name="contactFirstName" required>
                            </div>
                            <div class="form-group">
                                <label for="contactLastName">Contact Last Name:</label>
                                <input type="text" id="contactLastName" name="contactLastName" required>
                            </div>
                            <div class="form-group">
                                <label for="contactEmail">Contact Email:</label>
                                <input type="email" id="contactEmail" name="contactEmail">
                            </div>
                            <div class="form-group">
                                <label for="contactPhone">Contact Phone:</label>
                                <input type="text" id="contactPhone" name="contactPhone">
                            </div>
                            <div class="form-group">
                                <label for="contactPosition">Position:</label>
                                <input type="text" id="contactPosition" name="contactPosition">
                            </div>
                        </fieldset>
                    </div>

                    <!-- Invoices Tab -->
                    <div class="tab-content" data-tab-content="invoices">
                        <div class="invoices-section">
                            <h3>Company Invoices</h3>
                            <div class="invoice-filters">
                                <label for="companyInvoiceSearch">Search Invoices:</label>
                                <input type="text" id="companyInvoiceSearch" name="companyInvoiceSearch" placeholder="Search by ID or status">
                            </div>
                            <table class="company-invoices-table">
                                <thead>
                                    <tr>
                                        <th>Invoice ID</th>
                                        <th>Date</th>
                                        <th>Amount</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                                <tbody id="invoiceTableBody">
                                    <!-- Invoice data will be dynamically inserted here -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Form Action -->
                    <div class="form-actions">
                        <button type="submit" id="saveCompany">Save Company</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<!-- Expanded Customer Modal -->
<div id="customerModal" class="modal" style="display: none;">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <h2>Edit Client</h2>
                <span class="close" id="closeCustomerModal">&times;</span>
            </div>
            
            <!-- Modal Tabs -->
            <div class="modal-tabs">
                <button class="modal-tab active" data-tab="basic">Basic Info</button>
                <button class="modal-tab" data-tab="contact">Contact</button>
                <button class="modal-tab" data-tab="address">Address</button>
                <button class="modal-tab" data-tab="additional">Additional</button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body">
                <form id="customerForm">
                    <input type="hidden" id="customerId" name="customerId">
                    
                    <!-- Basic Info Tab -->
                    <div class="tab-content active" data-tab-content="basic">
                        <fieldset class="basic-info">
                            <legend>Basic Information</legend>
                            <div class="form-group">
                                <label for="customerInitials">Initials:</label>
                                <input type="text" id="customerInitials" name="customerInitials" required>
                            </div>
                            <div class="form-group">
                                <label for="customerTitle">Title:</label>
                                <select id="customerTitle" name="customerTitle" required>
                                    <option value="">Select</option>
                                    <option value="Mr">Mr</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Ms">Ms</option>
                                    <option value="Dr">Dr</option>
                                    <option value="Prof">Prof</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="customerName">Client Name:</label>
                                <input type="text" id="customerName" name="customerName" required>
                            </div>
                            <div class="form-group">
                                <label for="customerSurname">Client Surname:</label>
                                <input type="text" id="customerSurname" name="customerSurname" required>
                            </div>
                        </fieldset>
                    </div>

                    <!-- Contact Tab -->
                    <div class="tab-content" data-tab-content="contact">
                        <fieldset class="contact-info">
                            <legend>Contact Information</legend>
                            <div class="form-group">
                                <label for="customerEmail">Email:</label>
                                <input type="email" id="customerEmail" name="customerEmail" required>
                            </div>
                            <div class="form-group">
                                <label for="customerCell">Cell:</label>
                                <input type="text" id="customerCell" name="customerCell" required>
                            </div>
                            <div class="form-group">
                                <label for="customerTel">Telephone:</label>
                                <input type="text" id="customerTel" name="customerTel">
                            </div>
                        </fieldset>
                    </div>

                    <!-- Address Tab -->
                    <div class="tab-content" data-tab-content="address">
                        <fieldset class="address-info">
                            <legend>Address Details</legend>
                            <div class="form-group">
                                <label for="custAddrLine1">Address Line 1:</label>
                                <input type="text" id="custAddrLine1" name="custAddrLine1" required>
                            </div>
                            <div class="form-group">
                                <label for="custAddrLine2">Address Line 2:</label>
                                <input type="text" id="custAddrLine2" name="custAddrLine2">
                            </div>
                            <div class="form-group">
                                <label for="custCity">City:</label>
                                <input type="text" id="custCity" name="custCity" required>
                            </div>
                            <div class="form-group">
                                <label for="custSuburb">Suburb:</label>
                                <input type="text" id="custSuburb" name="custSuburb">
                            </div>
                            <div class="form-group">
                                <label for="custProvince">State/Province:</label>
                                <input type="text" id="custProvince" name="custProvince">
                            </div>
                            <div class="form-group">
                                <label for="custPostalCode">Postal Code:</label>
                                <input type="text" id="custPostalCode" name="custPostalCode" required>
                            </div>
                            <div class="form-group">
                                <label for="custCountry">Country:</label>
                                <input type="text" id="custCountry" name="custCountry" required>
                            </div>
                        </fieldset>
                    </div>

                    <!-- Additional Tab -->
                    <div class="tab-content" data-tab-content="additional">
                        <fieldset class="additional-info">
                            <legend>Additional Details</legend>
                            <div class="form-group">
                                <label for="customerDOB">Date of Birth:</label>
                                <input type="date" id="customerDOB" name="customerDOB">
                            </div>
                            <div class="form-group">
                                <label for="customerGender">Gender:</label>
                                <select id="customerGender" name="customerGender">
                                    <option value="">Select</option>
                                    <option value="female">Female</option>
                                    <option value="male">Male</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="customerLoyalty">Loyalty Level:</label>
                                <select id="customerLoyalty" name="customerLoyalty">
                                    <option value="">Select Loyalty Level</option>
                                    <option value="bronze">Bronze</option>
                                    <option value="silver">Silver</option>
                                    <option value="gold">Gold</option>
                                    <option value="platinum">Platinum</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="customerNotes">Notes:</label>
                                <textarea id="customerNotes" name="customerNotes" placeholder="Additional information about the customer"></textarea>
                            </div>
                        </fieldset>
                    </div>

                    <!-- Form Action -->
                    <div class="form-actions">
                        <button type="button" id="cancelCustomer">
                            <i class="material-icons">close</i>Cancel
                        </button>
                        <button type="submit">
                            <i class="material-icons">save</i>Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>