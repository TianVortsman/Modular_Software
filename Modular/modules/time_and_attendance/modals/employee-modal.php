<div id="employee-details-modal" class="modal-container">
  <div class="modal-content">
    <div class="modal-header">
      <h2>Employee Details</h2>
      <button class="modal-close">&times;</button>
    </div>
    
    <div class="modal-body">
      <!-- Two-column layout -->
      <div class="employee-details-layout">
        <!-- Left column - Enhanced Employee Profile -->
        <div class="employee-profile-section">
          <div class="profile-image-container">
            <img id="employee-profile-image" src="" alt="Employee Profile" 
              data-gender="male" 
              onerror="this.src='../img/placeholders/' + (this.dataset.gender === 'female' ? 'Female-placeholder.jpg' : 'Male-placeholder.jpg')">
          </div>
          
          <div class="employee-basic-info">
            <h3 id="employee-full-name"></h3>
            
            <div class="presence-indicator">
              <span class="presence-dot"></span>
              <span class="presence-status">Present</span>
            </div>
            
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
              <div class="gender-selector">
                <div class="gender-option">
                  <input type="radio" id="gender-male" name="gender" value="male" checked onchange="updateProfilePlaceholder('male')">
                  <label for="gender-male">
                    <i class="material-icons">male</i>
                    Male
                  </label>
                </div>
                <div class="gender-option">
                  <input type="radio" id="gender-female" name="gender" value="female" onchange="updateProfilePlaceholder('female')">
                  <label for="gender-female">
                    <i class="material-icons">female</i>
                    Female
                  </label>
                </div>
              </div>
            </div>
            
            <div class="info-field">
              <div class="status-indicator-container">
                <div class="status-card">
                  <div class="status-icon">
                    <i class="material-icons status-icon-symbol"></i>
                  </div>
                  <div class="status-details">
                    <span class="status-label">Status</span>
                    <span class="status-value"></span>
                  </div>
                  <div class="status-badge-pill"></div>
                </div>
              </div>
            </div>
            
            <div class="employee-quick-actions">
              <button class="quick-action" title="Send Message">
                <i class="material-icons">message</i>
              </button>
              <button class="quick-action" title="Call Employee">
                <i class="material-icons">call</i>
              </button>
              <button class="quick-action" title="Email Employee">
                <i class="material-icons">email</i>
              </button>
              <button class="quick-action" title="View History">
                <i class="material-icons">history</i>
              </button>
            </div>
          </div>
        </div>
        
        <!-- Right column - Tabbed content -->
        <div class="employee-details-tabs">
          <!-- Tab navigation -->
          <div class="emp-details-tab-nav" role="tablist">
            <button class="emp-details-tab-button active" data-modal-tab="personal" role="tab" aria-selected="true" aria-controls="personal-tab">
              <i class="material-icons">person</i> Personal Details
            </button>
            <button class="emp-details-tab-button" data-modal-tab="organization" role="tab" aria-selected="false" aria-controls="organization-tab">
              <i class="material-icons">business</i> Organization
            </button>
            <button class="emp-details-tab-button" data-modal-tab="employment" role="tab" aria-selected="false" aria-controls="employment-tab">
              <i class="material-icons">work</i> Employment
            </button>
            <button class="emp-details-tab-button" data-modal-tab="schedule" role="tab" aria-selected="false" aria-controls="schedule-tab">
              <i class="material-icons">schedule</i> Schedule
            </button>
            <button class="emp-details-tab-button" data-modal-tab="termination" role="tab" aria-selected="false" aria-controls="termination-tab">
              <i class="material-icons">exit_to_app</i> Termination
            </button>
            <button class="emp-details-tab-button" data-modal-tab="mobile" role="tab" aria-selected="false" aria-controls="mobile-tab">
              <i class="material-icons">smartphone</i> Mobile Setup
            </button>
            <button class="emp-details-tab-button" data-modal-tab="leave" role="tab" aria-selected="false" aria-controls="leave-tab">
              <i class="material-icons">event_available</i> Leave
            </button>
            <button class="emp-details-tab-button" data-modal-tab="hr" role="tab" aria-selected="false" aria-controls="hr-tab">
              <i class="material-icons">folder</i> HR Docs
            </button>
            <button class="emp-details-tab-button" data-modal-tab="access" role="tab" aria-selected="false" aria-controls="access-tab">
              <i class="material-icons">security</i> Access Control
            </button>
          </div>
          
          <!-- Tab content -->
          <div class="emp-details-tab-content">
            <!-- Personal Details Tab -->
            <div class="emp-details-tab-pane active" id="personal-tab" role="tabpanel" aria-labelledby="personal-tab-button">
              <div class="section-header">
                <h3>Personal Details</h3>
              </div>
              
              <div class="form-card">
                <h4>Contact Information</h4>
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
              </div>
              
              <div class="form-card">
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
            </div>
            
            <!-- Organization Tab -->
            <div class="emp-details-tab-pane" id="organization-tab" role="tabpanel" aria-labelledby="organization-tab-button">
              <div class="section-header">
                <h3>Organizational Structure</h3>
              </div>
              
              <div class="form-card">
                <h4>Organizational Hierarchy</h4>
                <div class="org-visual-hierarchy">
                  <div class="org-hierarchy-node company">
                    <div class="org-node-icon"><i class="material-icons">business</i></div>
                    <div class="org-node-content">
                      <span class="org-node-label">Company</span>
                      <select id="employee-company" class="org-node-select">
                        <option value="">Select Company</option>
                        <option value="acme">Acme Corporation</option>
                        <option value="globex">Globex Inc.</option>
                      </select>
                    </div>
                  </div>
                  <div class="org-hierarchy-connector"></div>
                  <div class="org-hierarchy-node division">
                    <div class="org-node-icon"><i class="material-icons">domain</i></div>
                    <div class="org-node-content">
                      <span class="org-node-label">Division</span>
                      <select id="employee-division" class="org-node-select">
                        <option value="">Select Division</option>
                      </select>
                    </div>
                  </div>
                  <div class="org-hierarchy-connector"></div>
                  <div class="org-hierarchy-node department">
                    <div class="org-node-icon"><i class="material-icons">groups</i></div>
                    <div class="org-node-content">
                      <span class="org-node-label">Department</span>
                      <select id="employee-department" class="org-node-select">
                        <option value="">Select Department</option>
                      </select>
                    </div>
                  </div>
                  <div class="org-hierarchy-connector"></div>
                  <div class="org-hierarchy-node group">
                    <div class="org-node-icon"><i class="material-icons">group_work</i></div>
                    <div class="org-node-content">
                      <span class="org-node-label">Group</span>
                      <select id="employee-group" class="org-node-select">
                        <option value="">Select Group</option>
                      </select>
                    </div>
                  </div>
                  <div class="org-hierarchy-connector"></div>
                  <div class="org-hierarchy-node cost-centre">
                    <div class="org-node-icon"><i class="material-icons">account_balance_wallet</i></div>
                    <div class="org-node-content">
                      <span class="org-node-label">Cost Centre</span>
                      <select id="employee-cost-centre" class="org-node-select">
                        <option value="">Select Cost Centre</option>
                      </select>
                    </div>
                  </div>
                  <div class="org-hierarchy-connector"></div>
                  <div class="org-hierarchy-node team">
                    <div class="org-node-icon"><i class="material-icons">people</i></div>
                    <div class="org-node-content">
                      <span class="org-node-label">Team</span>
                      <select id="employee-team" class="org-node-select">
                        <option value="">Select Team</option>
                      </select>
                    </div>
                  </div>
                  <div class="org-hierarchy-connector"></div>
                  <div class="org-hierarchy-node location">
                    <div class="org-node-icon"><i class="material-icons">location_on</i></div>
                    <div class="org-node-content">
                      <span class="org-node-label">Location</span>
                      <select id="employee-location" class="org-node-select">
                        <option value="">Select Location</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="form-row">
                <div class="form-column">
                  <div class="form-card">
                    <h4>Management & Reporting</h4>
                    <div class="reporting-manager-card">
                      <div class="manager-avatar">
                        <img src="../img/placeholders/Male-placeholder.jpg" alt="Manager" id="manager-avatar">
                      </div>
                      <div class="manager-details">
                        <label>Reporting Manager:</label>
                        <select id="employee-manager">
                          <option value="">Select Manager</option>
                        </select>
                        <div class="manager-quick-actions">
                          <button class="micro-action" title="View Manager Profile">
                            <i class="material-icons">visibility</i>
                          </button>
                          <button class="micro-action" title="Contact Manager">
                            <i class="material-icons">email</i>
                          </button>
                        </div>
                      </div>
                    </div>
                    
                    <div class="direct-reports">
                      <h5>Direct Reports <span class="count-badge">0</span></h5>
                      <div class="direct-reports-list">
                        <p class="no-data">No direct reports assigned.</p>
                        <!-- Direct reports will be added dynamically -->
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="form-card">
                <h4>Employee Tags</h4>
                <div class="tags-container">
                  <div class="tag-chips">
                    <div class="tag-chip" data-tag="shift-worker">
                      <input type="checkbox" id="tag-shift-worker" name="tags">
                      <label for="tag-shift-worker">Shift Worker</label>
                    </div>
                    <div class="tag-chip" data-tag="remote-worker">
                      <input type="checkbox" id="tag-remote-worker" name="tags">
                      <label for="tag-remote-worker">Remote Worker</label>
                    </div>
                    <div class="tag-chip" data-tag="overtime-eligible">
                      <input type="checkbox" id="tag-overtime-eligible" name="tags">
                      <label for="tag-overtime-eligible">Overtime Eligible</label>
                    </div>
                    <div class="tag-chip" data-tag="flexible-hours">
                      <input type="checkbox" id="tag-flexible-hours" name="tags">
                      <label for="tag-flexible-hours">Flexible Hours</label>
                    </div>
                    <div class="tag-chip" data-tag="temp-contractor">
                      <input type="checkbox" id="tag-temp-contractor" name="tags">
                      <label for="tag-temp-contractor">Temporary Contractor</label>
                    </div>
                    <div class="tag-chip" data-tag="part-time">
                      <input type="checkbox" id="tag-part-time" name="tags">
                      <label for="tag-part-time">Part-Time</label>
                    </div>
                  </div>
                  <div class="add-tag-control">
                    <input type="text" id="new-tag-input" placeholder="Add custom tag...">
                    <button id="add-tag-btn" class="micro-button">
                      <i class="material-icons">add</i> Add
                    </button>
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
                    <label>Work Week:</label>
                    <select id="work-pattern">
                      <option value="standard">Standard Week (Mon-Fri)</option>
                      <option value="rotating">Rotating Shift</option>
                      <option value="fixed">Fixed Shift</option>
                      <option value="flexible">Flexible Hours</option>
                      <option value="custom">Custom Schedule</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Monthly Template:</label>
                    <select id="roster-template">
                      <option value="">Select Roster Template</option>
                      <option value="day">Day Shift (8AM-4PM)</option>
                      <option value="evening">Evening Shift (4PM-12AM)</option>
                      <option value="night">Night Shift (12AM-8AM)</option>
                      <option value="custom">Custom Roster</option>
                    </select>
                  </div>

                  <div class="form-group">
                    <label>Pay Period:</label>
                    <select id="employee-pay-period">
                      <option value="weekly">Weekly</option>
                      <option value="biweekly">Bi-Weekly</option>
                      <option value="monthly">Monthly</option>
                      <option value="custom">Custom</option>
                    </select>
                  </div>
                </div>

                <div class="form-column">
                  
                </div>
              </div>

              <div class="inline-inputs-container">
                <div class="form-group small-input">
                  <label>Standard Hours:</label>
                  <input type="number" id="standard-hours" value="40" min="0" max="168" step="0.5">
                </div>
                <div class="form-group small-input">
                  <label>Break Duration (min):</label>
                  <input type="number" id="break-duration" value="60" min="0" max="480" step="15">
                </div>
                <div class="form-group small-input">
                  <label>Grace Period (min):</label>
                  <input type="number" id="grace-period" value="15" min="0" max="60" step="5">
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
                    <label>Employment Type:</label>
                    <select id="employee-employment-type">
                      <option value="Permanent">Permanent</option>
                      <option value="Temporary">Temporary</option>
                      <option value="Contract-based">Contract-based</option>
                      <option value="Probation">Probation</option>
                      <option value="Internship">Internship</option>
                      <option value="Other">Other</option>
                    </select>
                  </div>
                  <div class="form-group toggle-group">
                    <label>Is Sales:</label>
                    <label class="switch">
                      <input type="checkbox" id="employee-is-sales">
                      <span class="slider round"></span>
                    </label>
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
                  <div class="form-group">
                    <label>Mobile Device OS:</label>
                    <input type="text" id="mobile-device-os" placeholder="e.g., iOS, Android">
                  </div>
                  <div class="form-group">
                    <label>Device ID:</label>
                    <input type="text" id="device_id" placeholder="Unique device identifier">
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
              
              <div class="form-card leave-summary-card">
                <div class="leave-summary-header">
                  <h4>Leave Summary</h4>
                  <div class="year-selector">
                    <button class="year-nav-btn" id="prev-year"><i class="material-icons">chevron_left</i></button>
                    <span id="current-year">2023</span>
                    <button class="year-nav-btn" id="next-year"><i class="material-icons">chevron_right</i></button>
                  </div>
                </div>
                
                <div class="balance-cards">
                  <div class="balance-card annual">
                    <div class="balance-card-header">
                      <i class="material-icons">beach_access</i>
                      <span>Annual Leave</span>
                    </div>
                    <div class="balance-card-value">
                      <span class="balance-number" id="annual-leave-balance">15</span>
                      <span class="balance-unit">days</span>
                    </div>
                    <div class="balance-card-footer">
                      <span class="used-balance">Used: <strong id="annual-leave-used">5</strong> days</span>
                      <span class="balance-expiry">Expires: Dec 31</span>
                    </div>
                    <div class="balance-progress-bar">
                      <div class="balance-progress" style="width: 25%"></div>
                    </div>
                  </div>
                  
                  <div class="balance-card sick">
                    <div class="balance-card-header">
                      <i class="material-icons">healing</i>
                      <span>Sick Leave</span>
                    </div>
                    <div class="balance-card-value">
                      <span class="balance-number" id="sick-leave-balance">10</span>
                      <span class="balance-unit">days</span>
                    </div>
                    <div class="balance-card-footer">
                      <span class="used-balance">Used: <strong id="sick-leave-used">2</strong> days</span>
                      <span class="balance-expiry">No expiry</span>
                    </div>
                    <div class="balance-progress-bar">
                      <div class="balance-progress" style="width: 20%"></div>
                    </div>
                  </div>
                  
                  <div class="balance-card personal">
                    <div class="balance-card-header">
                      <i class="material-icons">person</i>
                      <span>Personal Leave</span>
                    </div>
                    <div class="balance-card-value">
                      <span class="balance-number" id="personal-leave-balance">5</span>
                      <span class="balance-unit">days</span>
                    </div>
                    <div class="balance-card-footer">
                      <span class="used-balance">Used: <strong id="personal-leave-used">1</strong> day</span>
                      <span class="balance-expiry">Expires: Dec 31</span>
                    </div>
                    <div class="balance-progress-bar">
                      <div class="balance-progress" style="width: 20%"></div>
                    </div>
                  </div>
                  
                  <div class="balance-card other">
                    <div class="balance-card-header">
                      <i class="material-icons">more_horiz</i>
                      <span>Other Leave</span>
                    </div>
                    <div class="balance-card-value">
                      <span class="balance-number" id="other-leave-balance">3</span>
                      <span class="balance-unit">days</span>
                    </div>
                    <div class="balance-card-footer">
                      <span class="used-balance">Used: <strong id="other-leave-used">0</strong> days</span>
                      <span class="balance-expiry">Varies by type</span>
                    </div>
                    <div class="balance-progress-bar">
                      <div class="balance-progress" style="width: 0%"></div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="form-card">
                <div class="leave-history-header">
                  <h4>Leave History</h4>
                  <div class="leave-filters">
                    <select id="leave-type-filter">
                      <option value="all">All Types</option>
                      <option value="annual">Annual Leave</option>
                      <option value="sick">Sick Leave</option>
                      <option value="personal">Personal Leave</option>
                      <option value="other">Other</option>
                    </select>
                    <select id="leave-status-filter">
                      <option value="all">All Status</option>
                      <option value="approved">Approved</option>
                      <option value="pending">Pending</option>
                      <option value="rejected">Rejected</option>
                      <option value="cancelled">Cancelled</option>
                    </select>
                  </div>
                </div>
                
                <div class="leave-timeline">
                  <!-- Entries will be populated dynamically based on leave history data -->
                  <div class="leave-entry approved">
                    <div class="leave-entry-icon">
                      <i class="material-icons">beach_access</i>
                    </div>
                    <div class="leave-entry-details">
                      <div class="leave-entry-header">
                        <span class="leave-type">Annual Leave</span>
                        <span class="leave-status">Approved</span>
                      </div>
                      <div class="leave-period">
                        <i class="material-icons">date_range</i>
                        <span>Jun 15, 2023 - Jun 20, 2023 (5 days)</span>
                      </div>
                      <div class="leave-notes">Summer vacation</div>
                    </div>
                  </div>
                  
                  <div class="leave-entry pending">
                    <div class="leave-entry-icon">
                      <i class="material-icons">healing</i>
                    </div>
                    <div class="leave-entry-details">
                      <div class="leave-entry-header">
                        <span class="leave-type">Sick Leave</span>
                        <span class="leave-status">Pending</span>
                      </div>
                      <div class="leave-period">
                        <i class="material-icons">date_range</i>
                        <span>Aug 10, 2023 (1 day)</span>
                      </div>
                      <div class="leave-notes">Doctor's appointment</div>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="form-card">
                <h4>Request Leave</h4>
                <div class="form-row">
                  <div class="form-column">
                    <div class="form-group">
                      <label>Leave Type:</label>
                      <select id="leave-type">
                        <option value="annual">Annual Leave</option>
                        <option value="sick">Sick Leave</option>
                        <option value="personal">Personal Leave</option>
                        <option value="other">Other</option>
                      </select>
                    </div>
                    <div class="form-group">
                      <label>Start Date:</label>
                      <input type="date" id="leave-start-date">
                    </div>
                    <div class="form-group">
                      <label>End Date:</label>
                      <input type="date" id="leave-end-date">
                    </div>
                  </div>
                  <div class="form-column">
                    <div class="form-group">
                      <label>Duration:</label>
                      <div class="duration-radio-group">
                        <label class="duration-option">
                          <input type="radio" name="leave-duration" value="full" checked>
                          <span>Full Day</span>
                        </label>
                        <label class="duration-option">
                          <input type="radio" name="leave-duration" value="half-morning">
                          <span>Half Day (AM)</span>
                        </label>
                        <label class="duration-option">
                          <input type="radio" name="leave-duration" value="half-afternoon">
                          <span>Half Day (PM)</span>
                        </label>
                      </div>
                    </div>
                    <div class="form-group">
                      <label>Notes:</label>
                      <textarea id="leave-notes" placeholder="Reason for leave request..."></textarea>
                    </div>
                  </div>
                </div>
                <div class="balance-info-banner">
                  <i class="material-icons">info</i>
                  <span>You will use <strong id="days-calculated">5</strong> days from your <strong id="leave-type-name">Annual</strong> Leave balance. <strong id="balance-after">10</strong> days will remain.</span>
                </div>
                <div class="form-actions">
                  <button id="submit-leave-request" class="btn-primary">Submit Request</button>
                </div>
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
                    <div class="form-group">
                      <label>Last Day Worked:</label>
                      <input type="date" id="last-day-worked">
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
                <div class="form-group">
                  <label>Exit Interview Notes:</label>
                  <textarea id="exit-interview-notes" placeholder="Enter any notes from the exit interview"></textarea>
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
              
              <div class="form-card">
                <div class="document-header">
                  <h4>Employment Documents</h4>
                  <div class="document-search">
                    <i class="material-icons">search</i>
                    <input type="text" id="document-search" placeholder="Search documents...">
                    <select id="document-category-filter">
                      <option value="all">All Categories</option>
                      <option value="contract">Contracts</option>
                      <option value="id">ID Documents</option>
                      <option value="certification">Certifications</option>
                      <option value="performance">Performance</option>
                      <option value="other">Other</option>
                    </select>
                  </div>
                </div>
                
                <div class="documents-tabs">
                  <button class="document-tab active" data-category="all">All</button>
                  <button class="document-tab" data-category="contract">Contracts</button>
                  <button class="document-tab" data-category="id">IDs</button>
                  <button class="document-tab" data-category="certification">Certifications</button>
                  <button class="document-tab" data-category="other">Other</button>
                </div>
                
                <div class="document-grid">
                  <div class="document-card" data-category="contract">
                    <div class="document-card-preview">
                      <i class="material-icons">description</i>
                      <span class="document-format">PDF</span>
                    </div>
                    <div class="document-card-info">
                      <h5>Employment Contract</h5>
                      <div class="document-meta">
                        <span class="document-date">Added: May 15, 2023</span>
                        <span class="document-size">245 KB</span>
                      </div>
                    </div>
                    <div class="document-card-actions">
                      <button class="document-action" title="View">
                        <i class="material-icons">visibility</i>
                      </button>
                      <button class="document-action" title="Download">
                        <i class="material-icons">download</i>
                      </button>
                      <button class="document-action" title="More Options">
                        <i class="material-icons">more_vert</i>
                      </button>
                    </div>
                  </div>
                  
                  <div class="document-card" data-category="id">
                    <div class="document-card-preview">
                      <i class="material-icons">badge</i>
                      <span class="document-format">PDF</span>
                    </div>
                    <div class="document-card-info">
                      <h5>ID Copy</h5>
                      <div class="document-meta">
                        <span class="document-date">Added: May 15, 2023</span>
                        <span class="document-size">1.2 MB</span>
                      </div>
                    </div>
                    <div class="document-card-actions">
                      <button class="document-action" title="View">
                        <i class="material-icons">visibility</i>
                      </button>
                      <button class="document-action" title="Download">
                        <i class="material-icons">download</i>
                      </button>
                      <button class="document-action" title="More Options">
                        <i class="material-icons">more_vert</i>
                      </button>
                    </div>
                  </div>
                  
                  <div class="document-card" data-category="certification">
                    <div class="document-card-preview">
                      <i class="material-icons">workspace_premium</i>
                      <span class="document-format">PDF</span>
                    </div>
                    <div class="document-card-info">
                      <h5>Sales Certification</h5>
                      <div class="document-meta">
                        <span class="document-date">Added: Jun 22, 2023</span>
                        <span class="document-size">3.5 MB</span>
                      </div>
                    </div>
                    <div class="document-card-actions">
                      <button class="document-action" title="View">
                        <i class="material-icons">visibility</i>
                      </button>
                      <button class="document-action" title="Download">
                        <i class="material-icons">download</i>
                      </button>
                      <button class="document-action" title="More Options">
                        <i class="material-icons">more_vert</i>
                      </button>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="form-card">
                <h4>Upload Document</h4>
                <div class="upload-zone">
                  <div class="upload-dropzone" id="document-dropzone">
                    <i class="material-icons">cloud_upload</i>
                    <p>Drag & drop files here or <span class="browse-link">browse</span></p>
                    <p class="upload-note">Supported formats: PDF, DOC, DOCX, JPG, PNG (Max 10MB)</p>
                    <input type="file" id="document-upload" hidden>
                  </div>
                  <div class="upload-preview" id="upload-preview">
                    <!-- File preview will appear here -->
                  </div>
                </div>
                
                <div class="form-row">
                  <div class="form-column">
                    <div class="form-group">
                      <label>Document Title:</label>
                      <input type="text" id="document-title" placeholder="Enter document title">
                    </div>
                    <div class="form-group">
                      <label>Document Type:</label>
                      <select id="document-type">
                        <option value="">Select document type</option>
                        <option value="contract">Employment Contract</option>
                        <option value="id">ID Document</option>
                        <option value="certification">Certification</option>
                        <option value="performance">Performance Review</option>
                        <option value="other">Other</option>
                      </select>
                    </div>
                  </div>
                  <div class="form-column">
                    <div class="form-group">
                      <label>Expiry Date (if applicable):</label>
                      <input type="date" id="document-expiry-date">
                    </div>
                    <div class="form-group">
                      <label>Document Description:</label>
                      <textarea id="document-description" placeholder="Enter document description"></textarea>
                    </div>
                  </div>
                </div>
                
                <div class="upload-actions">
                  <button id="cancel-upload" class="btn-outline">Cancel</button>
                  <button id="upload-document" class="btn-primary">Upload Document</button>
                </div>
              </div>
              
              <div class="form-card">
                <h4>Employee Records Timeline</h4>
                <div class="activity-timeline">
                  <div class="timeline-entry">
                    <div class="timeline-icon document-added">
                      <i class="material-icons">note_add</i>
                    </div>
                    <div class="timeline-content">
                      <div class="timeline-header">
                        <span class="timeline-title">Performance Review Added</span>
                        <span class="timeline-date">Jul 15, 2023</span>
                      </div>
                      <p class="timeline-description">Annual performance review document uploaded by HR Manager</p>
                    </div>
                  </div>
                  
                  <div class="timeline-entry">
                    <div class="timeline-icon contract-signed">
                      <i class="material-icons">edit</i>
                    </div>
                    <div class="timeline-content">
                      <div class="timeline-header">
                        <span class="timeline-title">Contract Amendment Signed</span>
                        <span class="timeline-date">Mar 10, 2023</span>
                      </div>
                      <p class="timeline-description">Salary adjustment contract amendment signed by employee</p>
                    </div>
                  </div>
                  
                  <div class="timeline-entry">
                    <div class="timeline-icon certification">
                      <i class="material-icons">workspace_premium</i>
                    </div>
                    <div class="timeline-content">
                      <div class="timeline-header">
                        <span class="timeline-title">New Certification Added</span>
                        <span class="timeline-date">Jan 22, 2023</span>
                      </div>
                      <p class="timeline-description">Sales Excellence certification uploaded</p>
                    </div>
                  </div>
                </div>
              </div>
            </div>
            
            <!-- Access Control Tab -->
            <div class="emp-details-tab-pane" id="access-tab" role="tabpanel" aria-labelledby="access-tab-button">
              <div class="section-header">
                <h3>Access Control & Security</h3>
              </div>
              
              <div class="form-card">
                <h4>Access Permissions</h4>
                <div class="form-row">
                  <div class="form-column">
                    <div class="form-group">
                      <label>Security Level:</label>
                      <select id="security-level">
                        <option value="1">Level 1 - Basic Access</option>
                        <option value="2">Level 2 - Standard Access</option>
                        <option value="3">Level 3 - Extended Access</option>
                        <option value="4">Level 4 - Administration</option>
                        <option value="5">Level 5 - Security</option>
                      </select>
                    </div>
                    
                    <div class="form-group">
                      <label>Role:</label>
                      <select id="role-select">
                        <option value="">Select Role</option>
                        <option value="admin">Administrator</option>
                        <option value="manager">Manager</option>
                        <option value="employee">Employee</option>
                      </select>
                    </div>

                    <div class="form-group">
                      <label>Time Restriction:</label>
                      <select id="time-restriction">
                        <option value="none">No Restrictions</option>
                        <option value="business">Business Hours Only</option>
                        <option value="shift">Shift Hours Only</option>
                        <option value="custom">Custom Hours</option>
                      </select>
                    </div>
                    
                    <div class="form-group toggle-group">
                      <label>Weekend Access:</label>
                      <label class="switch">
                        <input type="checkbox" id="weekend-access">
                        <span class="slider round"></span>
                      </label>
                    </div>
                  </div>
                  
                  <div class="form-column">
                    <div class="form-group">
                      <label>Badge ID:</label>
                      <input type="text" id="badge-id">
                    </div>

                    <div class="form-group">
                      <label>Fingerprint ID:</label>
                      <input type="text" id="fingerprint-id-input" placeholder="Enter Fingerprint ID">
                    </div>
                    <div class="form-group">
                      <label>RFID ID:</label>
                      <input type="text" id="rfid-id-input" placeholder="Enter RFID ID">
                    </div>
                    <div class="form-group">
                      <label>Facial Recognition ID:</label>
                      <input type="text" id="facial-recognition-id-input" placeholder="Enter Facial Recognition ID">
                    </div>
                    
                    <div class="form-group toggle-group">
                      <label>Biometric Access:</label>
                      <label class="switch">
                        <input type="checkbox" id="biometric-access">
                        <span class="slider round"></span>
                      </label>
                    </div>
                    
                    <div class="form-group toggle-group">
                      <label>Mobile Access:</label>
                      <label class="switch">
                        <input type="checkbox" id="mobile-access">
                        <span class="slider round"></span>
                      </label>
                    </div>
                  </div>
                </div>
              </div>
              
              <div class="form-card">
                <h4>Zone Access</h4>
                <div class="form-group">
                  <label>Access Zones:</label>
                  <select id="access-zones-select" multiple>
                    <option value="">Select Access Zones</option>
                    <option value="zone-1">Main Entrance</option>
                    <option value="zone-2">Office Area - General</option>
                    <option value="zone-3">Break Room</option>
                    <option value="zone-4">Server Room</option>
                    <option value="zone-5">Executive Suite</option>
                    <option value="zone-6">R&D Lab</option>
                    <option value="zone-7">Warehouse</option>
                    <option value="zone-8">Parking Garage</option>
                  </select>
                </div>
                <div class="zone-selection">
                  <div class="zone-list">
                    <h5>Available Zones</h5>
                    <div class="zone-list-items">
                      <div class="zone-item">
                        <input type="checkbox" id="zone-1" checked>
                        <label for="zone-1">Main Entrance</label>
                      </div>
                      <div class="zone-item">
                        <input type="checkbox" id="zone-2" checked>
                        <label for="zone-2">Office Area - General</label>
                      </div>
                      <div class="zone-item">
                        <input type="checkbox" id="zone-3" checked>
                        <label for="zone-3">Break Room</label>
                      </div>
                      <div class="zone-item">
                        <input type="checkbox" id="zone-4">
                        <label for="zone-4">Server Room</label>
                      </div>
                      <div class="zone-item">
                        <input type="checkbox" id="zone-5">
                        <label for="zone-5">Executive Suite</label>
                      </div>
                      <div class="zone-item">
                        <input type="checkbox" id="zone-6">
                        <label for="zone-6">R&D Lab</label>
                      </div>
                      <div class="zone-item">
                        <input type="checkbox" id="zone-7">
                        <label for="zone-7">Warehouse</label>
                      </div>
                      <div class="zone-item">
                        <input type="checkbox" id="zone-8">
                        <label for="zone-8">Parking Garage</label>
                      </div>
                    </div>
                  </div>
                  
                  <div class="zone-map-container">
                    <h5>Building Layout</h5>
                    <div class="zone-map">
                      <!-- Interactive zones will be added via JavaScript -->
                      <div class="zone-highlight" data-zone="zone-1" style="top: 45%; left: 50%; width: 10%; height: 5%;"></div>
                      <div class="zone-highlight" data-zone="zone-2" style="top: 30%; left: 30%; width: 40%; height: 30%;"></div>
                      <div class="zone-highlight" data-zone="zone-3" style="top: 20%; left: 75%; width: 15%; height: 15%;"></div>
                      <div class="zone-highlight" data-zone="zone-4" style="top: 70%; left: 20%; width: 10%; height: 10%;"></div>
                      <div class="zone-highlight" data-zone="zone-5" style="top: 15%; left: 10%; width: 15%; height: 20%;"></div>
                      <div class="zone-highlight" data-zone="zone-6" style="top: 60%; left: 60%; width: 25%; height: 20%;"></div>
                      <div class="zone-highlight" data-zone="zone-7" style="top: 80%; left: 40%; width: 30%; height: 15%;"></div>
                      <div class="zone-highlight" data-zone="zone-8" style="top: 85%; left: 10%; width: 20%; height: 10%;"></div>
                    </div>
                  </div>
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

<script>
// Function to update profile image placeholder based on gender
function updateProfilePlaceholder(gender) {
    const profileImage = document.getElementById('employee-profile-image');
    if (profileImage) {
        profileImage.dataset.gender = gender;
        if (!profileImage.src || profileImage.src.includes('placeholder')) {
            profileImage.src = `../img/placeholders/${gender === 'female' ? 'Female-placeholder.jpg' : 'Male-placeholder.jpg'}`;
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