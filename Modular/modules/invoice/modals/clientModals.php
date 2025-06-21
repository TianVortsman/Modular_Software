<!-- Expanded Company Modal -->
<div id="companyModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <span class="close" id="closeCompanyModal">&times;</span>
                <h2>Edit Company</h2>
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
                                <label for="companyType">Company Type:</label>
                                <input type="text" id="companyType" name="companyType">
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
                                <label for="contactName">Contact Name:</label>
                                <input type="text" id="contactName" name="contactName">
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
                <span class="close" id="closeCustomerModal">&times;</span>
                <h2>Edit Customer</h2>
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
                                <label for="customerName">Customer Name:</label>
                                <input type="text" id="customerName" name="customerName" required>
                            </div>
                            <div class="form-group">
                                <label for="customerSurname">Customer Surname:</label>
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

<!-- Add Client Modal -->
<div id="add-modal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <span class="close" id="closeAddClientModal">&times;</span>
                <h2>Add Client</h2>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <form id="addClientForm">
                    <!-- Client Type Selection -->
                    <fieldset class="client-type">
                        <legend>Client Type</legend>
                        <div class="form-group">
                            <label for="clientType">Select Client Type:</label>
                            <select id="clientType" name="clientType" required>
                                <option value="">Select</option>
                                <option value="company">Company</option>
                                <option value="customer">Customer</option>
                            </select>
                        </div>
                    </fieldset>

                    <!-- Company Fields -->
                    <div id="companyFields" class="client-fields" style="display: none;">
                        <fieldset class="company-info">
                            <legend>Company Information</legend>
                            <div class="form-group">
                                <label for="add-company-name">Company Name:</label>
                                <input type="text" id="add-company-name" name="add-company-name" required>
                            </div>
                            <div class="form-group">
                                <label for="add-registration-number">Registration Number:</label>
                                <input type="text" id="add-registration-number" name="add-registration-number">
                            </div>
                            <div class="form-group">
                                <label for="add-vat-number">VAT Number:</label>
                                <input type="text" id="add-vat-number" name="add-vat-number">
                            </div>
                            <div class="form-group">
                                <label for="add-industry">Industry:</label>
                                <input type="text" id="add-industry" name="add-industry">
                            </div>
                            <div class="form-group">
                                <label for="add-website">Website:</label>
                                <input type="url" id="add-website" name="add-website" placeholder="https://">
                            </div>
                        </fieldset>

                        <fieldset class="company-address">
                            <legend>Company Address</legend>
                            <div class="form-group">
                                <label for="add-company-address-line1">Address Line 1:</label>
                                <input type="text" id="add-company-address-line1" name="add-company-address-line1" required>
                            </div>
                            <div class="form-group">
                                <label for="add-company-address-line2">Address Line 2:</label>
                                <input type="text" id="add-company-address-line2" name="add-company-address-line2">
                            </div>
                            <div class="form-group">
                                <label for="add-company-suburb">Suburb:</label>
                                <input type="text" id="add-company-suburb" name="add-company-suburb">
                            </div>
                            <div class="form-group">
                                <label for="add-company-city">City:</label>
                                <input type="text" id="add-company-city" name="add-company-city" required>
                            </div>
                            <div class="form-group">
                                <label for="add-company-province">Province:</label>
                                <input type="text" id="add-company-province" name="add-company-province">
                            </div>
                            <div class="form-group">
                                <label for="add-company-postal-code">Postal Code:</label>
                                <input type="text" id="add-company-postal-code" name="add-company-postal-code" required>
                            </div>
                            <div class="form-group">
                                <label for="add-company-country">Country:</label>
                                <input type="text" id="add-company-country" name="add-company-country" required>
                            </div>
                        </fieldset>

                        <fieldset class="company-contact">
                            <legend>Company Contact</legend>
                            <div class="form-group">
                                <label for="add-contact-first-name">First Name:</label>
                                <input type="text" id="add-contact-first-name" name="add-contact-first-name" required>
                            </div>
                            <div class="form-group">
                                <label for="add-contact-last-name">Last Name:</label>
                                <input type="text" id="add-contact-last-name" name="add-contact-last-name" required>
                            </div>
                            <div class="form-group">
                                <label for="add-contact-position">Position:</label>
                                <input type="text" id="add-contact-position" name="add-contact-position">
                            </div>
                            <div class="form-group">
                                <label for="add-contact-email">Email:</label>
                                <input type="email" id="add-contact-email" name="add-contact-email" required>
                            </div>
                            <div class="form-group">
                                <label for="add-contact-phone">Phone:</label>
                                <input type="text" id="add-contact-phone" name="add-contact-phone" required>
                            </div>
                        </fieldset>
                    </div>

                    <!-- Customer Fields -->
                    <div id="customerFields" class="client-fields" style="display: none;">
                        <fieldset class="customer-info">
                            <legend>Personal Information</legend>
                            <div class="form-group">
                                <label for="add-title">Title:</label>
                                <select id="add-title" name="add-title">
                                    <option value="">Select</option>
                                    <option value="Mr">Mr</option>
                                    <option value="Mrs">Mrs</option>
                                    <option value="Ms">Ms</option>
                                    <option value="Dr">Dr</option>
                                    <option value="Prof">Prof</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="add-first-name">First Name:</label>
                                <input type="text" id="add-first-name" name="add-first-name" required>
                            </div>
                            <div class="form-group">
                                <label for="add-last-name">Last Name:</label>
                                <input type="text" id="add-last-name" name="add-last-name" required>
                            </div>
                            <div class="form-group">
                                <label for="add-dob">Date of Birth:</label>
                                <input type="date" id="add-dob" name="add-dob">
                            </div>
                            <div class="form-group">
                                <label for="add-gender">Gender:</label>
                                <select id="add-gender" name="add-gender">
                                    <option value="">Select</option>
                                    <option value="female">Female</option>
                                    <option value="male">Male</option>
                                    <option value="other">Other</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="add-loyalty">Loyalty Level:</label>
                                <select id="add-loyalty" name="add-loyalty">
                                    <option value="">Select</option>
                                    <option value="bronze">Bronze</option>
                                    <option value="silver">Silver</option>
                                    <option value="gold">Gold</option>
                                    <option value="platinum">Platinum</option>
                                </select>
                            </div>
                        </fieldset>

                        <fieldset class="customer-contact">
                            <legend>Contact Information</legend>
                            <div class="form-group">
                                <label for="add-email">Email:</label>
                                <input type="email" id="add-email" name="add-email" required>
                            </div>
                            <div class="form-group">
                                <label for="add-phone">Cell:</label>
                                <input type="text" id="add-phone" name="add-phone" required>
                            </div>
                            <div class="form-group">
                                <label for="add-tel">Telephone:</label>
                                <input type="text" id="add-tel" name="add-tel">
                            </div>
                        </fieldset>

                        <fieldset class="customer-address">
                            <legend>Address Details</legend>
                            <div class="form-group">
                                <label for="add-customer-address-line1">Address Line 1:</label>
                                <input type="text" id="add-customer-address-line1" name="add-customer-address-line1" required>
                            </div>
                            <div class="form-group">
                                <label for="add-customer-address-line2">Address Line 2:</label>
                                <input type="text" id="add-customer-address-line2" name="add-customer-address-line2">
                            </div>
                            <div class="form-group">
                                <label for="add-customer-suburb">Suburb:</label>
                                <input type="text" id="add-customer-suburb" name="add-customer-suburb">
                            </div>
                            <div class="form-group">
                                <label for="add-customer-city">City:</label>
                                <input type="text" id="add-customer-city" name="add-customer-city" required>
                            </div>
                            <div class="form-group">
                                <label for="add-customer-province">Province:</label>
                                <input type="text" id="add-customer-province" name="add-customer-province">
                            </div>
                            <div class="form-group">
                                <label for="add-customer-postal-code">Postal Code:</label>
                                <input type="text" id="add-customer-postal-code" name="add-customer-postal-code" required>
                            </div>
                            <div class="form-group">
                                <label for="add-customer-country">Country:</label>
                                <input type="text" id="add-customer-country" name="add-customer-country" required>
                            </div>
                        </fieldset>
                    </div>

                    <!-- Form Action -->
                    <div class="form-actions">
                        <button type="submit" id="saveClient">Save Client</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>