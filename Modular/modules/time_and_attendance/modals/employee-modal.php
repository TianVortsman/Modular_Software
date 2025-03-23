<div id="employee-details-modal" class="modal-container">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Employee Details</h2>
      <button class="modal-close">&times;</button>
    </div>
    
    <div class="modal-body">
      <!-- Two-column layout -->
      <div class="employee-details-layout">
        <!-- Left column - Employee Profile -->
        <div class="employee-profile-section">
          <div class="profile-image-container">
            <img id="employee-profile-image" src="" alt="Employee Profile" 
              data-gender="male" 
              onerror="this.src='../img/' + (this.dataset.gender === 'female' ? 'Female-placeholder.jpg' : 'Male-placeholder.jpg')">
          </div>
          
          <div class="employee-basic-info">
            <h3 id="employee-full-name"></h3>
            
            <div class="info-field">
              <label>Employee ID:</label>
              <span id="employee-payroll-number"></span>
            </div>
            
            <div class="info-field">
              <label>Clocking Number:</label>
              <span id="employee-clock-number"></span>
            </div>
            
            <div class="info-field">
              <label>Gender:</label>
              <select id="employee-gender" onchange="updateProfilePlaceholder(this.value)">
                <option value="male">Male</option>
                <option value="female">Female</option>
              </select>
            </div>
            
            <div class="status-indicator">
              <span class="status-badge"></span>
            </div>
          </div>
        </div>
        
        <!-- Right column - Tabbed content -->
        <div class="employee-details-tabs">
          <!-- Tab navigation -->
          <div class="emp-details-tab-nav" role="tablist">
            <button class="emp-details-tab-button active" data-modal-tab="personal" role="tab" aria-selected="true" aria-controls="personal-tab">Personal Details</button>
            <button class="emp-details-tab-button" data-modal-tab="organization" role="tab" aria-selected="false" aria-controls="organization-tab">Organization</button>
            <button class="emp-details-tab-button" data-modal-tab="employment" role="tab" aria-selected="false" aria-controls="employment-tab">Employment Details</button>
            <button class="emp-details-tab-button" data-modal-tab="schedule" role="tab" aria-selected="false" aria-controls="schedule-tab">Schedule & Roster</button>
            <button class="emp-details-tab-button" data-modal-tab="mobile" role="tab" aria-selected="false" aria-controls="mobile-tab">Mobile Clocking Setup</button>
            <button class="emp-details-tab-button" data-modal-tab="leave" role="tab" aria-selected="false" aria-controls="leave-tab">Leave & Balances</button>
            <button class="emp-details-tab-button" data-modal-tab="termination" role="tab" aria-selected="false" aria-controls="termination-tab">Termination</button>
            <button class="emp-details-tab-button" data-modal-tab="hr" role="tab" aria-selected="false" aria-controls="hr-tab">HR & Documentation</button>
          </div>
          
          <!-- Tab content -->
          <div class="emp-details-tab-content">
            <!-- Personal Details Tab -->
            <div class="emp-details-tab-pane active" id="personal-tab" role="tabpanel" aria-labelledby="personal-tab-button">
              <div class="section-header">
                <h3>Personal Details</h3>
              </div>
              <div class="form-row">
                <div class="form-column">
                  <div class="form-group">
                    <label>Date of Birth:</label>
                    <input type="date" id="employee-dob">
                  </div>
                  <div class="form-group">
                    <label>Email:</label>
                    <input type="email" id="employee-email">
                  </div>
                  <div class="form-group">
                    <label>Phone:</label>
                    <input type="tel" id="employee-phone">
                  </div>
                </div>
                <div class="form-column">
                  <div class="form-group">
                    <label>Address Line 1:</label>
                    <input type="text" id="employee-address1">
                  </div>
                  <div class="form-group">
                    <label>Address Line 2:</label>
                    <input type="text" id="employee-address2">
                  </div>
                  <div class="form-group">
                    <label>City:</label>
                    <input type="text" id="employee-city">
                  </div>
                  <div class="form-group">
                    <label>State/Province:</label>
                    <input type="text" id="employee-state">
                  </div>
                  <div class="form-group">
                    <label>Postal Code:</label>
                    <input type="text" id="employee-postal">
                  </div>
                  <div class="form-group">
                    <label>Country:</label>
                    <input type="text" id="employee-country">
                  </div>
                </div>
              </div>
              
              <h4>Emergency Contact</h4>
              <div class="form-row">
                <div class="form-column">
                  <div class="form-group">
                    <label>Name:</label>
                    <input type="text" id="emergency-name">
                  </div>
                  <div class="form-group">
                    <label>Relationship:</label>
                    <input type="text" id="emergency-relation">
                  </div>
                </div>
                <div class="form-column">
                  <div class="form-group">
                    <label>Phone:</label>
                    <input type="tel" id="emergency-phone">
                  </div>
                  <div class="form-group">
                    <label>Email:</label>
                    <input type="email" id="emergency-email">
                  </div>
                </div>
              </div>
            </div>
            
            <!-- New Organization Tab -->
            <div class="emp-details-tab-pane" id="organization-tab" role="tabpanel" aria-labelledby="organization-tab-button">
              <div class="section-header">
                <h3>Organizational Structure</h3>
              </div>
              
              <div class="form-row">
                <div class="form-column">
                  <div class="form-group">
                    <label>Division:</label>
                    <select id="employee-division">
                      <option value="">Select Division</option>
                    </select>
                  </div>
                  
                  <div class="form-group">
                    <label>Department:</label>
                    <select id="employee-department">
                      <option value="">Select Department</option>
                    </select>
                  </div>
                  
                  <div class="form-group">
                    <label>Group:</label>
                    <select id="employee-group">
                      <option value="">Select Group</option>
                    </select>
                  </div>
                </div>
                
                <div class="form-column">
                  <div class="form-group">
                    <label>Cost Centre:</label>
                    <select id="employee-cost-centre">
                      <option value="">Select Cost Centre</option>
                    </select>
                  </div>
                  
                  <div class="form-group">
                    <label>Location:</label>
                    <select id="employee-location">
                      <option value="">Select Location</option>
                    </select>
                  </div>
                  
                  <div class="form-group">
                    <label>Team:</label>
                    <select id="employee-team">
                      <option value="">Select Team</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Reporting Manager:</label>
                  <select id="employee-manager">
                    <option value="">Select Manager</option>
                  </select>
                </div>
              </div>

              <div class="form-row">
                <div class="form-group">
                  <label>Additional Filters:</label>
                  <div class="filter-tags">
                    <div class="filter-tag">
                      <label>
                        <input type="checkbox" name="filters" value="shift-worker">
                        Shift Worker
                      </label>
                    </div>
                    <div class="filter-tag">
                      <label>
                        <input type="checkbox" name="filters" value="remote-worker">
                        Remote Worker
                      </label>
                    </div>
                    <div class="filter-tag">
                      <label>
                        <input type="checkbox" name="filters" value="overtime-eligible">
                        Overtime Eligible
                      </label>
                    </div>
                    <div class="filter-tag">
                      <label>
                        <input type="checkbox" name="filters" value="flexible-hours">
                        Flexible Hours
                      </label>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <!-- New Schedule & Roster Tab -->
            <div class="emp-details-tab-pane" id="schedule-tab" role="tabpanel" aria-labelledby="schedule-tab-button">
              <div class="section-header">
                <h3>Work Schedule & Roster</h3>
              </div>

              <div class="form-row">
                <div class="form-column">
                  <div class="form-group">
                    <label>Work Pattern:</label>
                    <select id="work-pattern">
                      <option value="standard">Standard Week (Mon-Fri)</option>
                      <option value="rotating">Rotating Shift</option>
                      <option value="fixed">Fixed Shift</option>
                      <option value="flexible">Flexible Hours</option>
                      <option value="custom">Custom Schedule</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Roster Template:</label>
                    <select id="roster-template">
                      <option value="">Select Roster Template</option>
                      <option value="day">Day Shift (8AM-4PM)</option>
                      <option value="evening">Evening Shift (4PM-12AM)</option>
                      <option value="night">Night Shift (12AM-8AM)</option>
                      <option value="custom">Custom Roster</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Rotation Pattern:</label>
                    <select id="rotation-pattern">
                      <option value="none">No Rotation</option>
                      <option value="weekly">Weekly Rotation</option>
                      <option value="biweekly">Bi-weekly Rotation</option>
                      <option value="monthly">Monthly Rotation</option>
                    </select>
                  </div>
                </div>

                <div class="form-column">
                  <div class="form-group">
                    <label>Standard Hours:</label>
                    <input type="number" id="standard-hours" value="40" min="0" max="168" step="0.5">
                  </div>

                  <div class="form-group">
                    <label>Break Duration (minutes):</label>
                    <input type="number" id="break-duration" value="60" min="0" max="480" step="15">
                  </div>

                  <div class="form-group">
                    <label>Grace Period (minutes):</label>
                    <input type="number" id="grace-period" value="15" min="0" max="60" step="5">
                  </div>
                </div>
              </div>

              <div class="weekly-schedule">
                <h4>Weekly Schedule</h4>
                <table class="schedule-table">
                  <thead>
                    <tr>
                      <th>Day</th>
                      <th>Start Time</th>
                      <th>End Time</th>
                      <th>Break Start</th>
                      <th>Break End</th>
                      <th>Working</th>
                    </tr>
                  </thead>
                  <tbody>
                    <tr>
                      <td>Monday</td>
                      <td><input type="time" value="09:00"></td>
                      <td><input type="time" value="17:00"></td>
                      <td><input type="time" value="13:00"></td>
                      <td><input type="time" value="14:00"></td>
                      <td><input type="checkbox" checked></td>
                    </tr>
                    <tr>
                      <td>Tuesday</td>
                      <td><input type="time" value="09:00"></td>
                      <td><input type="time" value="17:00"></td>
                      <td><input type="time" value="13:00"></td>
                      <td><input type="time" value="14:00"></td>
                      <td><input type="checkbox" checked></td>
                    </tr>
                    <tr>
                      <td>Wednesday</td>
                      <td><input type="time" value="09:00"></td>
                      <td><input type="time" value="17:00"></td>
                      <td><input type="time" value="13:00"></td>
                      <td><input type="time" value="14:00"></td>
                      <td><input type="checkbox" checked></td>
                    </tr>
                    <tr>
                      <td>Thursday</td>
                      <td><input type="time" value="09:00"></td>
                      <td><input type="time" value="17:00"></td>
                      <td><input type="time" value="13:00"></td>
                      <td><input type="time" value="14:00"></td>
                      <td><input type="checkbox" checked></td>
                    </tr>
                    <tr>
                      <td>Friday</td>
                      <td><input type="time" value="09:00"></td>
                      <td><input type="time" value="17:00"></td>
                      <td><input type="time" value="13:00"></td>
                      <td><input type="time" value="14:00"></td>
                      <td><input type="checkbox" checked></td>
                    </tr>
                    <tr>
                      <td>Saturday</td>
                      <td><input type="time"></td>
                      <td><input type="time"></td>
                      <td><input type="time"></td>
                      <td><input type="time"></td>
                      <td><input type="checkbox"></td>
                    </tr>
                    <tr>
                      <td>Sunday</td>
                      <td><input type="time"></td>
                      <td><input type="time"></td>
                      <td><input type="time"></td>
                      <td><input type="time"></td>
                      <td><input type="checkbox"></td>
                    </tr>
                  </tbody>
                </table>
              </div>

              <div class="schedule-options">
                <h4>Additional Options</h4>
                <div class="form-row">
                  <div class="form-group toggle-group">
                    <label>Allow Overtime:</label>
                    <label class="switch">
                      <input type="checkbox" id="allow-overtime">
                      <span class="slider round"></span>
                    </label>
                  </div>
                  
                  <div class="form-group toggle-group">
                    <label>Flexible Hours:</label>
                    <label class="switch">
                      <input type="checkbox" id="flexible-hours">
                      <span class="slider round"></span>
                    </label>
                  </div>
                  
                  <div class="form-group toggle-group">
                    <label>Weekend Work:</label>
                    <label class="switch">
                      <input type="checkbox" id="weekend-work">
                      <span class="slider round"></span>
                    </label>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Employment Details Tab -->
            <div class="emp-details-tab-pane" id="employment-tab" role="tabpanel" aria-labelledby="employment-tab-button">
              <div class="section-header">
                <h3>Employment Details</h3>
              </div>
              <div class="form-row">
                <div class="form-column">
                  <div class="form-group">
                    <label>Job Title:</label>
                    <input type="text" id="employee-job-title">
                  </div>
                  <div class="form-group">
                    <label>Date of Employment:</label>
                    <input type="date" id="employee-hire-date">
                  </div>
                  <div class="form-group">
                    <label>Contract Type:</label>
                    <select id="employee-contract-type">
                      <option value="permanent">Permanent</option>
                      <option value="temporary">Temporary</option>
                      <option value="contract">Contract-based</option>
                      <option value="probation">Probation</option>
                    </select>
                  </div>
                </div>
                <div class="form-column">
                  <div class="form-group">
                    <label>Employment Status:</label>
                    <select id="employee-status">
                      <option value="active">Active</option>
                      <option value="suspended">Suspended</option>
                      <option value="terminated">Terminated</option>
                      <option value="leave">On Leave</option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Mobile Clocking Setup Tab -->
            <div class="emp-details-tab-pane" id="mobile-tab" role="tabpanel" aria-labelledby="mobile-tab-button">
              <div class="section-header">
                <h3>Mobile Clocking Setup</h3>
              </div>
              <div class="form-row">
                <div class="form-column">
                  <div class="form-group">
                    <label>Associated Devices:</label>
                    <div class="device-list">
                      <!-- Devices will be populated dynamically -->
                    </div>
                    <button class="add-device-btn">+ Add Device</button>
                  </div>
                </div>
                <div class="form-column">
                  <div class="form-group toggle-group">
                    <label>GPS Location Tracking:</label>
                    <label class="switch">
                      <input type="checkbox" id="gps-tracking">
                      <span class="slider round"></span>
                    </label>
                  </div>
                  <div class="form-group toggle-group">
                    <label>Biometric Authentication:</label>
                    <label class="switch">
                      <input type="checkbox" id="biometric-auth">
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
                      <input type="checkbox" id="offline-mode">
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
                  <option value="office">Office Only</option>
                  <option value="custom">Custom Locations</option>
                </select>
              </div>
            </div>
            
            <!-- Leave & Balances Tab -->
            <div class="emp-details-tab-pane" id="leave-tab" role="tabpanel" aria-labelledby="leave-tab-button">
              <div class="section-header">
                <h3>Leave & Balances</h3>
              </div>
              <div class="leave-balances">
                <div class="balance-item">
                  <span class="balance-label">Annual Leave:</span>
                  <input type="number" id="annual-leave" min="0" step="0.5"> days
                </div>
                <div class="balance-item">
                  <span class="balance-label">Sick Leave:</span>
                  <input type="number" id="sick-leave" min="0" step="0.5"> days
                </div>
                <div class="balance-item">
                  <span class="balance-label">Personal Leave:</span>
                  <input type="number" id="personal-leave" min="0" step="0.5"> days
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
                    <!-- Leave history will be populated dynamically -->
                  </tbody>
                </table>
              </div>
            </div>
            
            <!-- Termination Tab -->
            <div class="emp-details-tab-pane" id="termination-tab" role="tabpanel" aria-labelledby="termination-tab-button">
              <div class="section-header">
                <h3>Termination Details</h3>
              </div>
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
            <div class="emp-details-tab-pane" id="hr-tab" role="tabpanel" aria-labelledby="hr-tab-button">
              <div class="section-header">
                <h3>HR & Documentation</h3>
              </div>
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
                
                <div class="form-group">
                  <label>Upload New Document:</label>
                  <input type="file" id="document-upload">
                </div>
                <div class="form-group">
                  <label>Document Type:</label>
                  <select id="document-type">
                    <option value="">Select document type</option>
                    <option value="contract">Employment Contract</option>
                    <option value="id">ID Document</option>
                    <option value="certification">Certification</option>
                    <option value="other">Other</option>
                  </select>
                </div>
                <div class="form-group">
                  <label>Document Description:</label>
                  <textarea id="document-description" placeholder="Enter document description"></textarea>
                </div>
                <button class="upload-document-btn">Upload Document</button>
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

<script>
// Function to update profile image placeholder based on gender
function updateProfilePlaceholder(gender) {
    const profileImage = document.getElementById('employee-profile-image');
    if (profileImage) {
        profileImage.dataset.gender = gender;
        if (!profileImage.src || profileImage.src.includes('placeholder')) {
            profileImage.src = `../img/${gender === 'female' ? 'Female-placeholder.jpg' : 'Male-placeholder.jpg'}`;
        }
    }
}

// Initialize the modal when the document is ready
document.addEventListener('DOMContentLoaded', function() {
    // Initialize gender-based profile image
    const genderSelect = document.getElementById('employee-gender');
    if (genderSelect) {
        updateProfilePlaceholder(genderSelect.value);
    }
});
</script>