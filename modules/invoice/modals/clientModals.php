<!-- Expanded Company Modal -->
<div id="companyModal" class="modal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!-- Modal Header -->
            <div class="modal-header">
                <span class="close" id="closeCompanyModal">&times;</span>
                <h2>Edit Company</h2>
            </div>
            <!-- Modal Body -->
            <div class="modal-body">
                <form id="companyForm" action="../php/save-company-details.php" method="POST" >
                    <div class="form-row">
                        <!-- Company Details Section -->
                        <div class="form-column">
                            <fieldset class="basic-info">
                                <legend>Basic Information</legend>
                                <input type="hidden" id="companyId" name="companyId">
                                <div class="form-group">
                                    <label for="companyName">Company Name:</label>
                                    <input type="text" id="companyName" name="companyName" required>
                                </div>
                                <div class="form-group">
                                    <label for="companyTaxNo">Tax Number:</label>
                                    <input type="text" id="companyTaxNo" name="companyTaxNo">
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
                        
                        <!-- Address Details Section -->
                        <div class="form-column">
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
                    </div>

                    <!-- Company Contact Section -->
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

                    <!-- Form Action -->
                    <div class="form-actions">
                        <button type="submit" id="saveCompany">Save Company</button>
                    </div>
                </form>

                <!-- Invoices Section -->
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
            <!-- Response Modal -->
        <div id="responseModal" class="modal" style="display: none;">
            <div class="modal-content">
                <span id="closeModal" class="close">&times;</span>
                <p id="responseMessage"></p>
            </div>
        </div>
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
        <!-- Modal Body -->
        <div class="modal-body">
            <form id="customerForm">
            <div class="form-row">
                <!-- Basic Customer Information -->
                <div class="form-column">
                <fieldset class="basic-info">
                    <legend>Basic Information</legend>
                    <input type="hidden" id="customerId" name="customerId">
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
                <!-- Address Details Fieldset -->
                <div class="form-column">
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
            </div>
            <!-- Additional Customer Details -->
            <div class="form-row">
                <div class="form-column">
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
            </div>
            <!-- Form Action -->
            <div class="form-actions">
                <button type="submit" id="saveCustomer">Save Customer</button>
            </div>
            </form>
            <!-- Invoices Section for Customer -->
            <div class="invoices-section">
            <h3>Customer Invoices</h3>
            <div class="invoice-filters">
                <label for="customerInvoiceSearch">Search Invoices:</label>
                <input type="text" id="customerInvoiceSearch" name="customerInvoiceSearch" placeholder="Search by ID or status">
            </div>
            <table class="customer-invoices-table">
                <thead>
                <tr>
                    <th>Invoice ID</th>
                    <th>Date</th>
                    <th>Amount</th>
                    <th>Status</th>
                </tr>
                </thead>
                <tbody>
                <!-- Invoice data will be dynamically inserted here -->
                </tbody>
            </table>
            </div>
        </div>
        <!-- Modal Footer (for customerModal, can include response message area if needed) -->
        <div class="modal-footer">
        <div id="responseModal" class="modal" style="display: none;">
            <div class="modal-content">
            <span id="closeModal" class="close">&times;</span>
            <p id="responseMessage"></p>
            </div>
        </div>
        </div>
    </div>
  </div>
</div>


<!-- Add Client Modal -->
<div id="addClientModal" class="modal" style="display: none;">
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
                <label for="addCompanyName">Company Name:</label>
                <input type="text" id="addCompanyName" name="addCompanyName" required>
              </div>
              <div class="form-group">
                <label for="addCompanyTaxNo">Tax Number:</label>
                <input type="text" id="addCompanyTaxNo" name="addCompanyTaxNo">
              </div>
              <div class="form-group">
                <label for="addCompanyRegisNo">Registration Number:</label>
                <input type="text" id="addCompanyRegisNo" name="addCompanyRegisNo">
              </div>
              <div class="form-group">
                <label for="addCompanyType">Company Type:</label>
                <input type="text" id="addCompanyType" name="addCompanyType">
              </div>
              <div class="form-group">
                <label for="addCompanyIndustry">Industry:</label>
                <input type="text" id="addCompanyIndustry" name="addCompanyIndustry">
              </div>
              <div class="form-group">
                <label for="addCompanyWebsite">Website:</label>
                <input type="url" id="addCompanyWebsite" name="addCompanyWebsite" placeholder="https://">
              </div>
              <div class="form-group">
                <label for="addCompanyPhone">Phone:</label>
                <input type="text" id="addCompanyPhone" name="addCompanyPhone" required>
              </div>
              <div class="form-group">
                <label for="addCompanyEmail">Email:</label>
                <input type="email" id="addCompanyEmail" name="addCompanyEmail" required>
              </div>
            </fieldset>
            <fieldset class="company-address">
              <legend>Company Address</legend>
              <div class="form-group">
                <label for="addCompanyAddr1">Address Line 1:</label>
                <input type="text" id="addCompanyAddr1" name="addCompanyAddr1" required>
              </div>
              <div class="form-group">
                <label for="addCompanyAddr2">Address Line 2:</label>
                <input type="text" id="addCompanyAddr2" name="addCompanyAddr2">
              </div>
              <div class="form-group">
                <label for="addCompanySuburb">Suburb:</label>
                <input type="text" id="addCompanySuburb" name="addCompanySuburb">
              </div>
              <div class="form-group">
                <label for="addCompanyCity">City:</label>
                <input type="text" id="addCompanyCity" name="addCompanyCity" required>
              </div>
              <div class="form-group">
                <label for="addCompanyProvince">Province:</label>
                <input type="text" id="addCompanyProvince" name="addCompanyProvince">
              </div>
              <div class="form-group">
                <label for="addCompanyCountry">Country:</label>
                <input type="text" id="addCompanyCountry" name="addCompanyCountry" required>
              </div>
              <div class="form-group">
                <label for="addCompanyPostcode">Postcode:</label>
                <input type="text" id="addCompanyPostcode" name="addCompanyPostcode">
              </div>
            </fieldset>
            <fieldset class="company-contact">
              <legend>Company Contact</legend>
              <div class="form-group">
                <label for="addCompanyContactName">Contact Name:</label>
                <input type="text" id="addCompanyContactName" name="addCompanyContactName">
              </div>
              <div class="form-group">
                <label for="addCompanyContactEmail">Contact Email:</label>
                <input type="email" id="addCompanyContactEmail" name="addCompanyContactEmail">
              </div>
              <div class="form-group">
                <label for="addCompanyContactPhone">Contact Phone:</label>
                <input type="text" id="addCompanyContactPhone" name="addCompanyContactPhone">
              </div>
              <div class="form-group">
                <label for="addCompanyContactPosition">Contact Position:</label>
                <input type="text" id="addCompanyContactPosition" name="addCompanyContactPosition">
              </div>
            </fieldset>
          </div>
          <!-- Customer Fields -->
          <div id="customerFields" class="client-fields" style="display: none;">
            <!-- Personal Details Fieldset -->
            <fieldset class="customer-info">
              <legend>Customer Personal Details</legend>
              <div class="form-group">
                <label for="addCustomerFirstName">First Name:</label>
                <input type="text" id="addCustomerFirstName" name="addCustomerFirstName" required>
              </div>
              <div class="form-group">
                <label for="addCustomerLastName">Last Name:</label>
                <input type="text" id="addCustomerLastName" name="addCustomerLastName" required>
              </div>
              <div class="form-group">
                <label for="addCustomerInitials">Initials:</label>
                <input type="text" id="addCustomerInitials" name="addCustomerInitials">
              </div>
              <div class="form-group">
                <label for="addCustomerTitle">Title:</label>
                <input type="text" id="addCustomerTitle" name="addCustomerTitle">
              </div>
              <div class="form-group">
                <label for="addCustomerEmail">Email:</label>
                <input type="email" id="addCustomerEmail" name="addCustomerEmail" required>
              </div>
              <div class="form-group">
                <label for="addCustomerTel">Telephone:</label>
                <input type="text" id="addCustomerTel" name="addCustomerTel">
              </div>
              <div class="form-group">
                <label for="addCustomerCell">Cell:</label>
                <input type="text" id="addCustomerCell" name="addCustomerCell">
              </div>
            </fieldset>

            <!-- Address Details Fieldset -->
            <fieldset class="customer-address">
              <legend>Customer Address</legend>
              <div class="form-group">
                <label for="addCustomerAddr1">Address Line 1:</label>
                <input type="text" id="addCustomerAddr1" name="addCustomerAddr1" required>
              </div>
              <div class="form-group">
                <label for="addCustomerAddr2">Address Line 2:</label>
                <input type="text" id="addCustomerAddr2" name="addCustomerAddr2">
              </div>
              <div class="form-group">
                <label for="addCustomerCity">City:</label>
                <input type="text" id="addCustomerCity" name="addCustomerCity" required>
              </div>
              <div class="form-group">
                <label for="addCustomerProvince">Province:</label>
                <input type="text" id="addCustomerProvince" name="addCustomerProvince" required>
              </div>
              <div class="form-group">
                <label for="addCustomerPostalCode">Postal Code:</label>
                <input type="text" id="addCustomerPostalCode" name="addCustomerPostalCode" required>
              </div>
              <div class="form-group">
                <label for="addCustomerSuburb">Suburb:</label>
                <input type="text" id="addCustomerSuburb" name="addCustomerSuburb">
              </div>
              <div class="form-group">
                <label for="addCustomerCountry">Country:</label>
                <input type="text" id="addCustomerCountry" name="addCustomerCountry" required>
              </div>
            </fieldset>

            <!-- Additional Details Fieldset -->
            <fieldset class="customer-additional">
              <legend>Additional Customer Details</legend>
              <div class="form-group">
                <label for="addCustomerDOB">Date of Birth:</label>
                <input type="date" id="addCustomerDOB" name="addCustomerDOB">
              </div>
              <div class="form-group">
                <label for="addCustomerGender">Gender:</label>
                <select id="addCustomerGender" name="addCustomerGender">
                  <option value="">Select</option>
                  <option value="female">Female</option>
                  <option value="male">Male</option>
                  <option value="other">Other</option>
                </select>
              </div>
              <div class="form-group">
                <label for="addCustomerLoyalty">Loyalty Level:</label>
                <select id="addCustomerLoyalty" name="addCustomerLoyalty">
                  <option value="">Select Loyalty Level</option>
                  <option value="bronze">Bronze</option>
                  <option value="silver">Silver</option>
                  <option value="gold">Gold</option>
                  <option value="platinum">Platinum</option>
                </select>
              </div>
              <div class="form-group">
                <label for="addCustomerNotes">Notes:</label>
                <textarea id="addCustomerNotes" name="addCustomerNotes" placeholder="Additional information about the customer"></textarea>
              </div>
            </fieldset>
          </div>

          <!-- Form Action -->
          <div class="form-actions">
            <button type="submit" id="saveClient">Save Client</button>
          </div>
        </form>
      </div>
      <!-- Modal Footer -->
      <div class="modal-footer">
        <div id="addClientResponseMessage"></div>
      </div>
    </div>
  </div>
</div>