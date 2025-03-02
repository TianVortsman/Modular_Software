<div id="employee-details-modal" class="modal-container">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Employee Details</h2>
      <button class="modal-close">&times;</button>
    </div>
    
    <div class="modal-body">
      <!-- Two-column layout -->
      <div class="employee-details-layout">
        <!-- Left column - Always visible -->
        <div class="employee-profile-section">
          <div class="profile-image-container">
            <img id="employee-profile-image" src="../../../img/default-profile.jpg" alt="Employee Profile">
          </div>
          
          <div class="employee-basic-info">
            <h3 id="employee-full-name">John Smith</h3>
            
            <div class="info-field">
              <label>Payroll Number:</label>
              <span id="employee-payroll-number">EMP001</span>
            </div>
            
            <div class="info-field">
              <label>Badge Number:</label>
              <span id="employee-badge-number">B12345</span>
            </div>
            
            <div class="info-field">
              <label>Roster Assignment:</label>
              <select id="employee-roster">
                <option value="standard">Standard Week (Mon-Fri)</option>
                <option value="rotating">Rotating Shift</option>
                <option value="weekend">Weekend Shift</option>
                <option value="night">Night Shift</option>
                <option value="custom">Custom Schedule</option>
              </select>
            </div>
            
            <div class="status-indicator">
              <span class="status-badge status-active">Active</span>
            </div>
          </div>
        </div>
        
        <!-- Right column - Tabbed content -->
        <div class="employee-details-tabs">
          <!-- Tab navigation -->
          <div class="tabs-header">
            <button class="tab-button active" data-tab="personal">Personal Details</button>
            <button class="tab-button" data-tab="employment">Employment Details</button>
            <button class="tab-button" data-tab="mobile">Mobile Clocking Setup</button>
            <button class="tab-button" data-tab="leave">Leave & Balances</button>
            <button class="tab-button" data-tab="termination">Termination</button>
            <button class="tab-button" data-tab="hr">HR & Documentation</button>
          </div>
          
          <!-- Tab content -->
          <div class="tab-content">
            <!-- Personal Details Tab -->
            <div class="tab-pane active" id="personal-tab">
              <h3>Personal Details</h3>
              <div class="form-row">
                <div class="form-column">
                  <div class="form-group">
                    <label>Date of Birth:</label>
                    <input type="date" id="employee-dob" value="1985-06-15">
                  </div>
                  <div class="form-group">
                    <label>Email:</label>
                    <input type="email" id="employee-email" value="john.smith@example.com">
                  </div>
                  <div class="form-group">
                    <label>Phone:</label>
                    <input type="tel" id="employee-phone" value="(555) 123-4567">
                  </div>
                </div>
                <div class="form-column">
                  <div class="form-group">
                    <label>Address Line 1:</label>
                    <input type="text" id="employee-address1" value="123 Main Street">
                  </div>
                  <div class="form-group">
                    <label>Address Line 2:</label>
                    <input type="text" id="employee-address2" value="Apt 4B">
                  </div>
                  <div class="form-group">
                    <label>City:</label>
                    <input type="text" id="employee-city" value="Springfield">
                  </div>
                  <div class="form-group">
                    <label>State/Province:</label>
                    <input type="text" id="employee-state" value="IL">
                  </div>
                  <div class="form-group">
                    <label>Postal Code:</label>
                    <input type="text" id="employee-postal" value="62701">
                  </div>
                  <div class="form-group">
                    <label>Country:</label>
                    <input type="text" id="employee-country" value="United States">
                  </div>
                </div>
              </div>
              
              <h4>Emergency Contact</h4>
              <div class="form-row">
                <div class="form-column">
                  <div class="form-group">
                    <label>Name:</label>
                    <input type="text" id="emergency-name" value="Jane Smith">
                  </div>
                  <div class="form-group">
                    <label>Relationship:</label>
                    <input type="text" id="emergency-relation" value="Spouse">
                  </div>
                </div>
                <div class="form-column">
                  <div class="form-group">
                    <label>Phone:</label>
                    <input type="tel" id="emergency-phone" value="(555) 987-6543">
                  </div>
                  <div class="form-group">
                    <label>Email:</label>
                    <input type="email" id="emergency-email" value="jane.smith@example.com">
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Employment Details Tab -->
            <div class="tab-pane" id="employment-tab">
              <h3>Employment Details</h3>
              <div class="form-row">
                <div class="form-column">
                  <div class="form-group">
                    <label>Job Title:</label>
                    <input type="text" id="employee-job-title" value="Sales Manager">
                  </div>
                  <div class="form-group">
                    <label>Department:</label>
                    <select id="employee-department">
                      <option value="sales" selected>Sales</option>
                      <option value="marketing">Marketing</option>
                      <option value="it">IT</option>
                      <option value="hr">Human Resources</option>
                      <option value="finance">Finance</option>
                      <option value="operations">Operations</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Date of Employment:</label>
                    <input type="date" id="employee-hire-date" value="2018-03-15">
                  </div>
                  <div class="form-group">
                    <label>Contract Type:</label>
                    <select id="employee-contract-type">
                      <option value="permanent" selected>Permanent</option>
                      <option value="temporary">Temporary</option>
                      <option value="contract">Contract-based</option>
                      <option value="probation">Probation</option>
                    </select>
                  </div>
                </div>
                <div class="form-column">
                  <div class="form-group">
                    <label>Reporting Manager:</label>
                    <select id="employee-manager">
                      <option value="1">Sarah Johnson (VP Sales)</option>
                      <option value="2">Michael Brown (Director)</option>
                      <option value="3">Emily Davis (Regional Manager)</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Work Location:</label>
                    <select id="employee-location">
                      <option value="hq">Headquarters</option>
                      <option value="branch1" selected>Branch Office 1</option>
                      <option value="branch2">Branch Office 2</option>
                      <option value="remote">Remote</option>
                    </select>
                  </div>
                  <div class="form-group">
                    <label>Employment Status:</label>
                    <select id="employee-status">
                      <option value="active" selected>Active</option>
                      <option value="suspended">Suspended</option>
                      <option value="terminated">Terminated</option>
                      <option value="leave">On Leave</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Mobile Clocking Setup Tab -->
            <div class="tab-pane" id="mobile-tab">
              <h3>Mobile Clocking Setup</h3>
              <div class="form-row">
                <div class="form-column">
                  <div class="form-group">
                    <label>Associated Devices:</label>
                    <div class="device-list">
                      <div class="device-item">
                        <span>iPhone 13 (John's Phone)</span>
                        <button class="remove-device">Remove</button>
                      </div>
                      <div class="device-item">
                        <span>Samsung Galaxy S21 (Work Phone)</span>
                        <button class="remove-device">Remove</button>
                      </div>
                    </div>
                    <button class="add-device-btn">+ Add Device</button>
                  </div>
                </div>
                <div class="form-column">
                  <div class="form-group toggle-group">
                    <label>GPS Location Tracking:</label>
                    <label class="switch">
                      <input type="checkbox" id="gps-tracking" checked>
                      <span class="slider round"></span>
                    </label>
                  </div>
                  <div class="form-group toggle-group">
                    <label>Biometric Authentication:</label>
                    <label class="switch">
                      <input type="checkbox" id="biometric-auth" checked>
                      <span class="slider round"></span>
                    </label>
                  </div>
                  <div class="form-group toggle-group">
                    <label>Manual Entry Allowed:</label>
                    <label class="switch">
                      <input type="checkbox" id="manual-entry">
                      <span class="slider round"></span>
                    </label>
                  </div>
                  <div class="form-group toggle-group">
                    <label>Offline Mode Allowed:</label>
                    <label class="switch">
                      <input type="checkbox" id="offline-mode" checked>
                      <span class="slider round"></span>
                    </label>
                  </div>
                  <div class="form-group toggle-group">
                    <label>Photo Verification:</label>
                    <label class="switch">
                      <input type="checkbox" id="photo-verification">
                      <span class="slider round"></span>
                    </label>
                  </div>
                </div>
              </div>
              <div class="form-group">
                <label>Geofencing Restrictions:</label>
                <select id="geofencing-type">
                  <option value="none">None</option>
                  <option value="office" selected>Office Only</option>
                  <option value="custom">Custom Locations</option>
                </select>
              </div>
              <div id="custom-locations" class="form-group" style="display: none;">
                <label>Custom Locations:</label>
                <textarea id="custom-locations-list" placeholder="Enter custom locations"></textarea>
              </div>
            </div>
            
            <!-- Leave & Balances Tab -->
            <div class="tab-pane" id="leave-tab">
              <h3>Leave & Balances</h3>
              <div class="leave-balances">
                <div class="balance-item">
                  <span class="balance-label">Annual Leave:</span>
                  <span class="balance-value">15 days</span>
                </div>
                <div class="balance-item">
                  <span class="balance-label">Sick Leave:</span>
                  <span class="balance-value">8 days</span>
                </div>
                <div class="balance-item">
                  <span class="balance-label">Personal Leave:</span>
                  <span class="balance-value">3 days</span>
                </div>
                <div class="balance-item">
                  <span class="balance-label">Unpaid Leave:</span>
                  <span class="balance-value">Unlimited</span>
                </div>
              </div>
              
              <h4>Leave History</h4>
              <div class="table-container">
                <table class="employee-table">
                  <thead>
                    <tr>
                      <th>Type</th>
                      <th>Start Date</th>
                      <th>End Date</th>
                      <th>Duration</th>
                      <th>Status</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Annual Leave</td>
                      <td>2023-12-20</td>
                      <td>2023-12-31</td>
                      <td>8 days</td>
                      <td><span class="status-badge status-approved">Approved</span></td>
                    </tr>
                    <tr>
                      <td>Sick Leave</td>
                      <td>2023-10-05</td>
                      <td>2023-10-06</td>
                      <td>2 days</td>
                      <td><span class="status-badge status-approved">Approved</span></td>
                    </tr>
                    <tr>
                      <td>Annual Leave</td>
                      <td>2024-07-15</td>
                      <td>2024-07-26</td>
                      <td>10 days</td>
                      <td><span class="status-badge status-pending">Pending</span></td>
                    </tr>
                  </tbody>
                </table>
              </div>
              
              <button class="request-leave-btn">+ Request Leave</button>
            </div>
            
            <!-- Termination Tab -->
            <div class="tab-pane" id="termination-tab">
              <h3>Termination Details</h3>
              <div class="termination-status">
                <p>This employee is currently <strong>Active</strong>.</p>
              </div>
              
              <div class="termination-form">
                <div class="form-row">
                  <div class="form-column">
                    <div class="form-group">
                      <label>Termination Date:</label>
                      <input type="date" id="termination-date">
                    </div>
                    <div class="form-group">
                      <label>Reason for Termination:</label>
                      <select id="termination-reason">
                        <option value="">Select a reason</option>
                        <option value="resignation">Resignation</option>
                        <option value="layoff">Layoff</option>
                        <option value="performance">Performance</option>
                        <option value="misconduct">Misconduct</option>
                        <option value="contract-end">End of Contract</option>
                        <option value="other">Other</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-column">
                    <div class="form-group">
                      <label>Rehire Eligibility:</label>
                      <select id="rehire-eligibility">
                        <option value="">Select eligibility</option>
                        <option value="eligible">Eligible</option>
                        <option value="not-eligible">Not Eligible</option>
                        <option value="conditional">Conditional</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Final Settlement Date:</label>
                      <input type="date" id="settlement-date">
                    </div>
                  </div>
                </div>
                <div class="form-group">
                  <label>Additional Notes:</label>
                  <textarea id="termination-notes" placeholder="Enter any additional notes regarding termination"></textarea>
                </div>
                <div class="form-actions">
                  <button id="process-termination" class="btn-danger">Process Termination</button>
                </div>
              </div>
            </div>
            
            <!-- HR & Documentation Tab -->
            <div class="tab-pane" id="hr-tab">
              <h3>HR & Documentation</h3>
              <div class="documents-section">
                <h4>Employment Documents</h4>
                <div class="document-list">
                  <div class="document-item">
                    <span class="document-icon">📄</span>
                    <span class="document-name">Employment Contract.pdf</span>
                    <div class="document-actions">
                      <button class="document-action">View</button>
                      <button class="document-action">Download</button>
                    </div>
                  </div>
                  <div class="document-item">
                    <span class="document-icon">📄</span>
                    <span class="document-name">ID Copy.pdf</span>
                    <div class="document-actions">
                      <button class="document-action">View</button>
                      <button class="document-action">Download</button>
                    </div>
                  </div>
                  <div class="document-item">
                    <span class="document-icon">📄</span>
                    <span class="document-name">Sales Certification.pdf</span>
                    <div class="document-actions">
                      <button class="document-action">View</button>
                      <button class="document-action">Download</button>
                    </div>
                  </div>
                </div>
                
                <button class="upload-document-btn">+ Upload Document</button>
                
                <h4>Disciplinary Records</h4>
                <div class="table-container">
                  <table class="employee-table">
                    <thead>
                      <tr>
                        <th>Date</th>
                        <th>Type</th>
                        <th>Description</th>
                        <th>Status</th>
                        <th>Actions</th>
                      </tr>
                    </thead>
                    <tbody>
                      <tr>
                        <td>2022-05-10</td>
                        <td>Verbal Warning</td>
                        <td>Late arrival (3 instances)</td>
                        <td><span class="status-badge status-resolved">Resolved</span></td>
                        <td><button class="icon-button">📋</button></td>
                      </tr>
                      <tr>
                        <td>2023-02-15</td>
                        <td>Written Warning</td>
                        <td>Missed sales targets for Q4 2022</td>
                        <td><span class="status-badge status-active">Active</span></td>
                        <td><button class="icon-button">📋</button></td>
                      </tr>
                    </tbody>
                  </table>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
    
    <div class="modal-footer">
      <button class="btn-cancel">Cancel</button>
      <button class="btn-save">Save Changes</button>
    </div>
  </div>
</div>