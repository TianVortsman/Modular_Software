<!-- Enhanced Setup Modal -->
<div class="modal" id="setupModal">
    <div class="modal-content setup-modal">
        <div class="modal-header">
            <h3><i class="material-icons">settings</i> System Setup</h3>
            <button class="close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <div class="setup-tabs">
                <div class="setup-tab-buttons">
                    <button class="setup-tab-btn active" data-tab="holidays">
                        <i class="material-icons">event</i>
                        Public Holidays
                    </button>
                    <button class="setup-tab-btn" data-tab="time-categories">
                        <i class="material-icons">category</i>
                        Time Categories
                    </button>
                    <button class="setup-tab-btn" data-tab="rounding-profiles">
                        <i class="material-icons">schedule</i>
                        Rounding Profiles
                    </button>
                    <button class="setup-tab-btn" data-tab="overtime-profiles">
                        <i class="material-icons">timer</i>
                        Overtime Profiles
                    </button>
                    <button class="setup-tab-btn" data-tab="pay-periods">
                        <i class="material-icons">payments</i>
                        Pay Periods
                    </button>
                    <button class="setup-tab-btn" data-tab="approval-workflow">
                        <i class="material-icons">verified</i>
                        Approval Workflow
                    </button>
                    <button class="setup-tab-btn" data-tab="shift-matching">
                        <i class="material-icons">compare_arrows</i>
                        Shift Matching
                    </button>
                </div>

                <!-- Public Holidays Tab -->
                <div class="setup-tab-content active" id="holidays-tab">
                    <div class="tab-header">
                        <h4>Manage Public Holidays</h4>
                        <div class="action-row">
                            <button class="action-btn" id="importHolidaysBtn">
                                <i class="material-icons">download</i>
                                Import Standard Holidays
                            </button>
                            <select id="holidayYear" class="year-select">
                                <option value="2023">2023</option>
                                <option value="2024" selected>2024</option>
                                <option value="2025">2025</option>
                            </select>
                        </div>
                    </div>
                    
                    <form id="holidaysForm">
                        <div class="form-group">
                            <label>Add New Holiday</label>
                            <div class="holiday-input-group">
                                <input type="text" id="holidayName" name="holidayName" placeholder="Holiday Name" required>
                                <input type="date" id="holidayDate" name="holidayDate" required>
                                <select id="holidayType" name="holidayType">
                                    <option value="standard">Standard</option>
                                    <option value="company">Company Specific</option>
                                </select>
                                <button type="button" class="add-btn" id="addHolidayBtn">
                                    <i class="material-icons">add</i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="options-section">
                            <div class="option-item">
                                <label class="toggle-switch">
                                    <input type="checkbox" id="enableRollover" checked>
                                    <span class="toggle-slider"></span>
                                </label>
                                <span class="option-label">Enable PH rollover to Monday if on Sunday</span>
                            </div>
                        </div>

                        <div class="holiday-list">
                            <div class="list-header">
                                <h4>Configured Holidays</h4>
                                <div class="list-filters">
                                    <button class="filter-btn active" data-filter="all">All</button>
                                    <button class="filter-btn" data-filter="standard">Standard</button>
                                    <button class="filter-btn" data-filter="company">Company</button>
                                </div>
                            </div>
                            <div class="list-container" id="holidaysList">
                                <!-- Holidays will be loaded here -->
                                <div class="holiday-item standard">
                                    <div class="holiday-info">
                                        <span class="holiday-date">Jan 1, 2024</span>
                                        <span class="holiday-name">New Year's Day</span>
                                        <span class="holiday-badge standard">Standard</span>
                                    </div>
                                    <div class="holiday-actions">
                                        <button class="action-icon edit-holiday">
                                            <i class="material-icons">edit</i>
                                        </button>
                                        <button class="action-icon delete-holiday">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </div>
                                </div>
                                <div class="holiday-item company">
                                    <div class="holiday-info">
                                        <span class="holiday-date">Mar 15, 2024</span>
                                        <span class="holiday-name">Company Anniversary</span>
                                        <span class="holiday-badge company">Company</span>
                                    </div>
                                    <div class="holiday-actions">
                                        <button class="action-icon edit-holiday">
                                            <i class="material-icons">edit</i>
                                        </button>
                                        <button class="action-icon delete-holiday">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Time Categories Tab -->
                <div class="setup-tab-content" id="time-categories-tab">
                    <div class="tab-header">
                        <h4>Configure Time Categories</h4>
                        <button class="action-btn" id="resetDefaultCategoriesBtn">
                            <i class="material-icons">restore</i>
                            Reset to Defaults
                        </button>
                    </div>
                    
                    <div class="category-tabs">
                        <button class="category-tab-btn active" data-category="normal">Normal Time</button>
                        <button class="category-tab-btn" data-category="overtime">Overtime</button>
                        <button class="category-tab-btn" data-category="holiday">Holiday</button>
                        <button class="category-tab-btn" data-category="special">Special Categories</button>
                    </div>

                    <!-- Normal Time Categories -->
                    <div class="category-content active" id="normal-categories">
                        <form id="normalCategoriesForm">
                            <div class="form-group">
                                <label>Add Normal Time Category</label>
                                <div class="category-input-group">
                                    <input type="text" id="normalCategoryName" name="name" placeholder="Category Name" required>
                                    <input type="number" id="normalCategoryRate" name="rate" step="0.01" placeholder="Rate" required>
                                    <input type="color" id="normalCategoryColor" name="color" value="#4CAF50">
                                    <button type="button" class="add-btn" id="addNormalCategoryBtn">
                                        <i class="material-icons">add</i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="category-list" id="normalCategoriesList">
                                <div class="category-item">
                                    <div class="category-info">
                                        <span class="category-color" style="background-color: #4CAF50"></span>
                                        <span class="category-name">Normal Time</span>
                                        <span class="category-rate">Rate: 1.0x</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-icon edit-category">
                                            <i class="material-icons">edit</i>
                                        </button>
                                        <button class="action-icon delete-category">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Overtime Categories -->
                    <div class="category-content" id="overtime-categories">
                        <form id="overtimeCategoriesForm">
                            <div class="form-group">
                                <label>Add Overtime Category</label>
                                <div class="category-input-group">
                                    <input type="text" id="overtimeCategoryName" name="name" placeholder="Category Name" required>
                                    <input type="number" id="overtimeCategoryRate" name="rate" step="0.01" placeholder="Rate" required>
                                    <input type="color" id="overtimeCategoryColor" name="color" value="#FF9800">
                                    <button type="button" class="add-btn" id="addOvertimeCategoryBtn">
                                        <i class="material-icons">add</i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="category-list" id="overtimeCategoriesList">
                                <div class="category-item">
                                    <div class="category-info">
                                        <span class="category-color" style="background-color: #FF9800"></span>
                                        <span class="category-name">Overtime 1.5</span>
                                        <span class="category-rate">Rate: 1.5x</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-icon edit-category">
                                            <i class="material-icons">edit</i>
                                        </button>
                                        <button class="action-icon delete-category">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </div>
                                </div>
                                <div class="category-item">
                                    <div class="category-info">
                                        <span class="category-color" style="background-color: #F44336"></span>
                                        <span class="category-name">Overtime 2.0</span>
                                        <span class="category-rate">Rate: 2.0x</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-icon edit-category">
                                            <i class="material-icons">edit</i>
                                        </button>
                                        <button class="action-icon delete-category">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                    
                    <!-- Holiday Categories -->
                    <div class="category-content" id="holiday-categories">
                        <form id="holidayCategoriesForm">
                            <div class="form-group">
                                <label>Add Holiday Category</label>
                                <div class="category-input-group">
                                    <input type="text" id="holidayCategoryName" name="name" placeholder="Category Name" required>
                                    <input type="number" id="holidayCategoryRate" name="rate" step="0.01" placeholder="Rate" required>
                                    <input type="color" id="holidayCategoryColor" name="color" value="#3F51B5">
                                    <button type="button" class="add-btn" id="addHolidayCategoryBtn">
                                        <i class="material-icons">add</i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="category-list" id="holidayCategoriesList">
                                <div class="category-item">
                                    <div class="category-info">
                                        <span class="category-color" style="background-color: #3F51B5"></span>
                                        <span class="category-name">PH Worked</span>
                                        <span class="category-rate">Rate: 1.0x</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-icon edit-category">
                                            <i class="material-icons">edit</i>
                                        </button>
                                        <button class="action-icon delete-category">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </div>
                                </div>
                                <div class="category-item">
                                    <div class="category-info">
                                        <span class="category-color" style="background-color: #9C27B0"></span>
                                        <span class="category-name">PH Not Worked</span>
                                        <span class="category-rate">Rate: 1.0x</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-icon edit-category">
                                            <i class="material-icons">edit</i>
                                        </button>
                                        <button class="action-icon delete-category">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </div>
                                </div>
                                <div class="category-item">
                                    <div class="category-info">
                                        <span class="category-color" style="background-color: #673AB7"></span>
                                        <span class="category-name">PH Overtime</span>
                                        <span class="category-rate">Rate: 2.0x</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-icon edit-category">
                                            <i class="material-icons">edit</i>
                                        </button>
                                        <button class="action-icon delete-category">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>

                    <!-- Special Categories -->
                    <div class="category-content" id="special-categories">
                        <form id="specialCategoriesForm">
                            <div class="form-group">
                                <label>Add Special Category</label>
                                <div class="category-input-group">
                                    <input type="text" id="specialCategoryName" name="name" placeholder="Category Name" required>
                                    <input type="number" id="specialCategoryRate" name="rate" step="0.01" placeholder="Rate" required>
                                    <input type="color" id="specialCategoryColor" name="color" value="#2196F3">
                                    <button type="button" class="add-btn" id="addSpecialCategoryBtn">
                                        <i class="material-icons">add</i>
                                    </button>
                                </div>
                            </div>
                            
                            <div class="category-list" id="specialCategoriesList">
                                <div class="category-item">
                                    <div class="category-info">
                                        <span class="category-color" style="background-color: #2196F3"></span>
                                        <span class="category-name">Sunday OT @ 2.5x</span>
                                        <span class="category-rate">Rate: 2.5x</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-icon edit-category">
                                            <i class="material-icons">edit</i>
                                        </button>
                                        <button class="action-icon delete-category">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </div>
                                </div>
                                <div class="category-item">
                                    <div class="category-info">
                                        <span class="category-color" style="background-color: #00BCD4"></span>
                                        <span class="category-name">Travel Time</span>
                                        <span class="category-rate">Rate: 1.0x</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-icon edit-category">
                                            <i class="material-icons">edit</i>
                                        </button>
                                        <button class="action-icon delete-category">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </div>
                                </div>
                                <div class="category-item">
                                    <div class="category-info">
                                        <span class="category-color" style="background-color: #009688"></span>
                                        <span class="category-name">Call-Out OT</span>
                                        <span class="category-rate">Rate: 2.0x</span>
                                    </div>
                                    <div class="category-actions">
                                        <button class="action-icon edit-category">
                                            <i class="material-icons">edit</i>
                                        </button>
                                        <button class="action-icon delete-category">
                                            <i class="material-icons">delete</i>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Rounding Profiles Tab -->
                <div class="setup-tab-content" id="rounding-profiles-tab">
                    <div class="tab-header">
                        <h4>Configure Rounding Profiles</h4>
                    </div>
                    
                    <form id="roundingProfilesForm">
                        <div class="form-group">
                            <label>Add Rounding Profile</label>
                            <div class="profile-input-group">
                                <input type="text" id="roundingProfileName" name="name" placeholder="Profile Name" required>
                                <div class="rounding-rules">
                                    <div class="rule-group">
                                        <label>Clock In Rounding</label>
                                        <select id="clockInRounding" name="clockInRounding" required>
                                            <option value="none">No Rounding</option>
                                            <option value="nearest_5">Nearest 5 Minutes</option>
                                            <option value="nearest_15">Nearest 15 Minutes</option>
                                            <option value="nearest_30">Nearest 30 Minutes</option>
                                            <option value="up_5">Round Up 5 Minutes</option>
                                            <option value="down_5">Round Down 5 Minutes</option>
                                            <option value="up_15">Round Up 15 Minutes</option>
                                            <option value="down_15">Round Down 15 Minutes</option>
                                        </select>
                                    </div>
                                    <div class="rule-group">
                                        <label>Clock Out Rounding</label>
                                        <select id="clockOutRounding" name="clockOutRounding" required>
                                            <option value="none">No Rounding</option>
                                            <option value="nearest_5">Nearest 5 Minutes</option>
                                            <option value="nearest_15">Nearest 15 Minutes</option>
                                            <option value="nearest_30">Nearest 30 Minutes</option>
                                            <option value="up_5">Round Up 5 Minutes</option>
                                            <option value="down_5">Round Down 5 Minutes</option>
                                            <option value="up_15">Round Up 15 Minutes</option>
                                            <option value="down_15">Round Down 15 Minutes</option>
                                        </select>
                                    </div>
                                    
                                    <div class="advanced-rounding-options">
                                        <h5>Advanced Options</h5>
                                        <div class="option-item">
                                            <label class="toggle-switch">
                                                <input type="checkbox" id="roundEarlyToStart">
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <span class="option-label">Round early punches to shift start time</span>
                                        </div>
                                        <div class="option-item">
                                            <label class="toggle-switch">
                                                <input type="checkbox" id="disableEarlyClockImpact">
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <span class="option-label">Disable early clock-in impact</span>
                                        </div>
                                        <div class="rule-group">
                                            <label>Grace Period (Minutes)</label>
                                            <div class="grace-period-group">
                                                <div class="grace-input">
                                                    <label>Before Shift</label>
                                                    <input type="number" id="graceBeforeShift" name="graceBeforeShift" min="0" value="15">
                                                </div>
                                                <div class="grace-input">
                                                    <label>After Shift</label>
                                                    <input type="number" id="graceAfterShift" name="graceAfterShift" min="0" value="15">
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <button type="button" class="add-btn" id="addRoundingProfileBtn">
                                    <i class="material-icons">add</i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="profile-list" id="roundingProfilesList">
                            <div class="profile-item">
                                <div class="profile-info">
                                    <span class="profile-name">Standard Rounding</span>
                                    <div class="profile-details">
                                        <span class="detail-item">In: Nearest 15 Minutes</span>
                                        <span class="detail-item">Out: Nearest 15 Minutes</span>
                                        <span class="detail-item">Grace: 15 min</span>
                                    </div>
                                </div>
                                <div class="profile-actions">
                                    <button class="action-icon edit-profile">
                                        <i class="material-icons">edit</i>
                                    </button>
                                    <button class="action-icon delete-profile">
                                        <i class="material-icons">delete</i>
                                    </button>
                                </div>
                            </div>
                            <div class="profile-item">
                                <div class="profile-info">
                                    <span class="profile-name">No Rounding</span>
                                    <div class="profile-details">
                                        <span class="detail-item">In: No Rounding</span>
                                        <span class="detail-item">Out: No Rounding</span>
                                        <span class="detail-item">Grace: 0 min</span>
                                    </div>
                                </div>
                                <div class="profile-actions">
                                    <button class="action-icon edit-profile">
                                        <i class="material-icons">edit</i>
                                    </button>
                                    <button class="action-icon delete-profile">
                                        <i class="material-icons">delete</i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Overtime Profiles Tab -->
                <div class="setup-tab-content" id="overtime-profiles-tab">
                    <div class="tab-header">
                        <h4>Configure Overtime Profiles</h4>
                    </div>
                    
                    <form id="overtimeProfilesForm">
                        <div class="form-group">
                            <label>Add Overtime Profile</label>
                            <div class="profile-input-group">
                                <input type="text" id="overtimeProfileName" name="name" placeholder="Profile Name" required>
                                <select id="overtimePayPeriod" name="payPeriod" required>
                                    <option value="">Select Pay Period</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="biweekly">Bi-Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="custom">Custom</option>
                                </select>
                                
                                <div class="ot-thresholds">
                                    <h5>Overtime Thresholds</h5>
                                    <div class="threshold-group">
                                        <div class="threshold-item">
                                            <label>Daily Threshold (Hours)</label>
                                            <input type="number" id="dailyThreshold" name="dailyThreshold" step="0.5" value="8">
                                            <select id="dailyOTCategory" name="dailyOTCategory">
                                                <option value="OT1.5">Overtime 1.5</option>
                                            </select>
                                        </div>
                                        <div class="threshold-item">
                                            <label>Weekly Threshold (Hours)</label>
                                            <input type="number" id="weeklyThreshold" name="weeklyThreshold" step="0.5" value="40">
                                            <select id="weeklyOTCategory" name="weeklyOTCategory">
                                                <option value="OT1.5">Overtime 1.5</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="multiplier-group">
                                        <h5>Special Multipliers</h5>
                                        <div class="multiplier-item">
                                            <label>
                                                <input type="checkbox" id="enableWeekendMultiplier" name="enableWeekendMultiplier">
                                                Weekend Multiplier
                                            </label>
                                            <select id="weekendOTCategory" name="weekendOTCategory" disabled>
                                                <option value="OT2.0">Overtime 2.0</option>
                                            </select>
                                        </div>
                                        <div class="multiplier-item">
                                            <label>
                                                <input type="checkbox" id="enableHolidayMultiplier" name="enableHolidayMultiplier">
                                                Holiday Multiplier
                                            </label>
                                            <select id="holidayOTCategory" name="holidayOTCategory" disabled>
                                                <option value="PHOT">PH Overtime</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="filling-sequence">
                                    <label>Filling Sequence</label>
                                    <div class="sequence-options">
                                        <label>
                                            <input type="radio" name="fillingSequence" value="daily" checked>
                                            Daily
                                        </label>
                                        <label>
                                            <input type="radio" name="fillingSequence" value="weekly">
                                            Weekly
                                        </label>
                                    </div>
                                </div>
                                
                                <div class="option-item">
                                    <label class="toggle-switch">
                                        <input type="checkbox" id="separateOTDateRange">
                                        <span class="toggle-slider"></span>
                                    </label>
                                    <span class="option-label">Use separate date range for OT tracking</span>
                                </div>
                                
                                <button type="button" class="add-btn" id="addOvertimeProfileBtn">
                                    <i class="material-icons">add</i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="profile-list" id="overtimeProfilesList">
                            <div class="profile-item">
                                <div class="profile-info">
                                    <span class="profile-name">Standard Weekly OT</span>
                                    <div class="profile-details">
                                        <span class="detail-item">Daily: > 8h = OT1.5</span>
                                        <span class="detail-item">Weekly: > 40h = OT1.5</span>
                                        <span class="detail-item">Weekends: OT2.0</span>
                                    </div>
                                </div>
                                <div class="profile-actions">
                                    <button class="action-icon edit-profile">
                                        <i class="material-icons">edit</i>
                                    </button>
                                    <button class="action-icon delete-profile">
                                        <i class="material-icons">delete</i>
                                    </button>
                                </div>
                            </div>
                            <div class="profile-item">
                                <div class="profile-info">
                                    <span class="profile-name">Monthly OT Profile</span>
                                    <div class="profile-details">
                                        <span class="detail-item">Daily: > 9h = OT1.5</span>
                                        <span class="detail-item">Monthly: > 176h = OT1.5</span>
                                        <span class="detail-item">Holidays: PHOT</span>
                                    </div>
                                </div>
                                <div class="profile-actions">
                                    <button class="action-icon edit-profile">
                                        <i class="material-icons">edit</i>
                                    </button>
                                    <button class="action-icon delete-profile">
                                        <i class="material-icons">delete</i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Pay Periods Tab -->
                <div class="setup-tab-content" id="pay-periods-tab">
                    <div class="tab-header">
                        <h4>Configure Pay Periods</h4>
                    </div>
                    
                    <form id="payPeriodsForm">
                        <div class="form-group">
                            <label>Add Pay Period</label>
                            <div class="period-input-group">
                                <input type="text" id="periodName" name="name" placeholder="Period Name" required>
                                <select id="periodType" name="type" required>
                                    <option value="">Select Type</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="biweekly">Bi-Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="custom">Custom</option>
                                </select>
                                
                                <div class="start-day-selection">
                                    <label>Start Day/Date</label>
                                    <select id="startDay" name="startDay">
                                        <option value="1">Monday</option>
                                        <option value="2">Tuesday</option>
                                        <option value="3">Wednesday</option>
                                        <option value="4">Thursday</option>
                                        <option value="5">Friday</option>
                                        <option value="6">Saturday</option>
                                        <option value="0">Sunday</option>
                                    </select>
                                </div>
                                
                                <div class="period-details" id="customPeriodDetails" style="display: none;">
                                    <div class="custom-period-inputs">
                                        <div class="input-group">
                                            <label>Number of Days</label>
                                            <input type="number" id="periodDays" name="days" placeholder="Days" min="1">
                                        </div>
                                        <div class="input-group">
                                            <label>Number of Weeks</label>
                                            <input type="number" id="periodWeeks" name="weeks" placeholder="Weeks" min="1">
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="link-profiles-section">
                                    <h5>Linked Profiles</h5>
                                    <div class="link-item">
                                        <label>Default OT Profile</label>
                                        <select id="defaultOTProfile" name="defaultOTProfile">
                                            <option value="">None</option>
                                            <option value="standard">Standard Weekly OT</option>
                                            <option value="monthly">Monthly OT Profile</option>
                                        </select>
                                    </div>
                                    <div class="link-item">
                                        <label>Default Rounding Profile</label>
                                        <select id="defaultRoundingProfile" name="defaultRoundingProfile">
                                            <option value="">None</option>
                                            <option value="standard">Standard Rounding</option>
                                            <option value="none">No Rounding</option>
                                        </select>
                                    </div>
                                </div>
                                
                                <button type="button" class="add-btn" id="addPayPeriodBtn">
                                    <i class="material-icons">add</i>
                                </button>
                            </div>
                        </div>
                        
                        <div class="period-list" id="payPeriodsList">
                            <div class="period-item">
                                <div class="period-info">
                                    <span class="period-name">Weekly Mon-Sun</span>
                                    <div class="period-details">
                                        <span class="detail-item">Type: Weekly</span>
                                        <span class="detail-item">Start: Monday</span>
                                        <span class="detail-item">OT: Standard Weekly OT</span>
                                    </div>
                                </div>
                                <div class="period-actions">
                                    <button class="action-icon edit-period">
                                        <i class="material-icons">edit</i>
                                    </button>
                                    <button class="action-icon delete-period">
                                        <i class="material-icons">delete</i>
                                    </button>
                                </div>
                            </div>
                            <div class="period-item">
                                <div class="period-info">
                                    <span class="period-name">Monthly Calendar</span>
                                    <div class="period-details">
                                        <span class="detail-item">Type: Monthly</span>
                                        <span class="detail-item">Start: 1st of Month</span>
                                        <span class="detail-item">OT: Monthly OT Profile</span>
                                    </div>
                                </div>
                                <div class="period-actions">
                                    <button class="action-icon edit-period">
                                        <i class="material-icons">edit</i>
                                    </button>
                                    <button class="action-icon delete-period">
                                        <i class="material-icons">delete</i>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Approval Workflow Tab -->
                <div class="setup-tab-content" id="approval-workflow-tab">
                    <div class="tab-header">
                        <h4>Configure Approval Workflow</h4>
                        <div class="info-badge">
                            <i class="material-icons">info</i>
                            <span class="tooltip">Configure multi-level approval requirements for timecards and overtime</span>
                        </div>
                    </div>

                    <form id="approvalWorkflowForm">
                        <div class="approval-sections">
                            <!-- Timecard Approval Section -->
                            <div class="approval-section">
                                <h5>Timecard Approval Settings</h5>
                                
                                <div class="approval-options">
                                    <div class="option-item">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="requireTimecardApproval" checked>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="option-label">Require timecard approval before export</span>
                                    </div>
                                    
                                    <div class="approval-level-selector">
                                        <label>Required Approval Levels</label>
                                        <select id="timecardApprovalLevels">
                                            <option value="0">Auto-Approved (0 levels)</option>
                                            <option value="1" selected>1 Level (Supervisor)</option>
                                            <option value="2">2 Levels (Supervisor → Manager)</option>
                                            <option value="3">3 Levels (Supervisor → Manager → Admin)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="default-status">
                                        <label>Default Timecard Status</label>
                                        <div class="status-options">
                                            <label>
                                                <input type="radio" name="defaultTimecardStatus" value="approved">
                                                Always Approved
                                            </label>
                                            <label>
                                                <input type="radio" name="defaultTimecardStatus" value="unapproved" checked>
                                                Always Unapproved (needs review)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Overtime Approval Section -->
                            <div class="approval-section">
                                <h5>Overtime Approval Settings</h5>
                                
                                <div class="approval-options">
                                    <div class="option-item">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="requireOTApproval" checked>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="option-label">Require separate overtime approval</span>
                                    </div>
                                    
                                    <div class="approval-level-selector">
                                        <label>Required OT Approval Levels</label>
                                        <select id="otApprovalLevels">
                                            <option value="0">Auto-Approved (0 levels)</option>
                                            <option value="1" selected>1 Level (Supervisor)</option>
                                            <option value="2">2 Levels (Supervisor → Manager)</option>
                                            <option value="3">3 Levels (Supervisor → Manager → Admin)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="default-status">
                                        <label>Default OT Status</label>
                                        <div class="status-options">
                                            <label>
                                                <input type="radio" name="defaultOTStatus" value="approved">
                                                Always Approved
                                            </label>
                                            <label>
                                                <input type="radio" name="defaultOTStatus" value="unapproved" checked>
                                                Always Unapproved (needs review)
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Approval Role Configuration -->
                            <div class="approval-section">
                                <h5>Approval Role Configuration</h5>
                                
                                <div class="role-table">
                                    <div class="role-header">
                                        <div class="role-cell">Role</div>
                                        <div class="role-cell">Can Approve</div>
                                        <div class="role-cell">Level</div>
                                    </div>
                                    
                                    <div class="role-row">
                                        <div class="role-cell">Supervisor</div>
                                        <div class="role-cell">
                                            <label class="toggle-switch small">
                                                <input type="checkbox" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                        <div class="role-cell">
                                            <select class="approval-level">
                                                <option value="1" selected>Level 1</option>
                                                <option value="2">Level 2</option>
                                                <option value="3">Level 3</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="role-row">
                                        <div class="role-cell">Manager</div>
                                        <div class="role-cell">
                                            <label class="toggle-switch small">
                                                <input type="checkbox" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                        <div class="role-cell">
                                            <select class="approval-level">
                                                <option value="1">Level 1</option>
                                                <option value="2" selected>Level 2</option>
                                                <option value="3">Level 3</option>
                                            </select>
                                        </div>
                                    </div>
                                    
                                    <div class="role-row">
                                        <div class="role-cell">Administrator</div>
                                        <div class="role-cell">
                                            <label class="toggle-switch small">
                                                <input type="checkbox" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                        </div>
                                        <div class="role-cell">
                                            <select class="approval-level">
                                                <option value="1">Level 1</option>
                                                <option value="2">Level 2</option>
                                                <option value="3" selected>Level 3</option>
                                            </select>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Advanced Approval Settings -->
                            <div class="approval-section">
                                <h5>Advanced Settings</h5>
                                
                                <div class="advanced-approval-options">
                                    <div class="option-item">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="enableEscalation">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="option-label">Enable approval escalation if no action taken</span>
                                    </div>
                                    
                                    <div class="input-group" id="escalationDaysGroup" style="display: none;">
                                        <label>Days before escalation</label>
                                        <input type="number" id="escalationDays" min="1" value="3">
                                    </div>
                                    
                                    <div class="option-item">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="enableNotifications" checked>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="option-label">Send notifications for pending approvals</span>
                                    </div>
                                    
                                    <div class="option-item">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="requireApprovalComments">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="option-label">Require comments for rejected timecards/OT</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="action-btn primary" id="saveApprovalSettingsBtn">
                                <i class="material-icons">save</i>
                                Save Approval Settings
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Shift Matching Tab -->
                <div class="setup-tab-content" id="shift-matching-tab">
                    <div class="tab-header">
                        <h4>Configure Shift Matching</h4>
                        <div class="info-badge">
                            <i class="material-icons">info</i>
                            <span class="tooltip">Configure how employee clockings match to assigned shifts</span>
                        </div>
                    </div>
                    
                    <form id="shiftMatchingForm">
                        <div class="matching-sections">
                            <!-- Default Behavior Section -->
                            <div class="matching-section">
                                <h5>Default Behavior</h5>
                                
                                <div class="section-description">
                                    <p>Configure how the system should behave when no specific shift is assigned or matched.</p>
                                </div>
                                
                                <div class="matching-options">
                                    <div class="option-group">
                                        <label>When no shift is assigned or matched</label>
                                        <div class="radio-options">
                                            <label>
                                                <input type="radio" name="noShiftBehavior" value="openShift" checked>
                                                Use "Open Shift" (flexible start/end time)
                                            </label>
                                            <label>
                                                <input type="radio" name="noShiftBehavior" value="defaultShift">
                                                Apply Default Shift
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="open-shift-options">
                                        <h6>Open Shift Settings</h6>
                                        
                                        <div class="option-item">
                                            <label class="toggle-switch">
                                                <input type="checkbox" id="crossDaySupport" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <span class="option-label">Support cross-day clocking (overnight shifts)</span>
                                        </div>
                                        
                                        <div class="input-group">
                                            <label>Maximum hours per shift</label>
                                            <input type="number" id="maxHoursPerShift" value="20" min="1" max="24">
                                        </div>
                                        
                                        <div class="option-item">
                                            <label class="toggle-switch">
                                                <input type="checkbox" id="autoNewShift" checked>
                                                <span class="toggle-slider"></span>
                                            </label>
                                            <span class="option-label">Auto-detect new shift if max hours exceeded</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Shift Matching Configuration -->
                            <div class="matching-section">
                                <h5>Shift Matching Configuration</h5>
                                
                                <div class="section-description">
                                    <p>Define how the system matches employee clock-ins to scheduled shifts.</p>
                                </div>
                                
                                <div class="matching-options">
                                    <div class="input-group">
                                        <label>Match clock-in to shift if within (minutes)</label>
                                        <div class="minutes-selector">
                                            <select id="matchWindowMinutes">
                                                <option value="15">15 minutes</option>
                                                <option value="30" selected>30 minutes</option>
                                                <option value="45">45 minutes</option>
                                                <option value="60">60 minutes</option>
                                                <option value="90">90 minutes</option>
                                                <option value="120">120 minutes</option>
                                                <option value="custom">Custom...</option>
                                            </select>
                                            <input type="number" id="customMatchWindow" placeholder="Minutes" min="1" style="display: none;">
                                        </div>
                                    </div>
                                    
                                    <div class="option-group">
                                        <label>Matching Method</label>
                                        <div class="radio-options">
                                            <label>
                                                <input type="radio" name="matchingMethod" value="timeProximity" checked>
                                                Time Proximity (closest shift)
                                            </label>
                                            <label>
                                                <input type="radio" name="matchingMethod" value="windowContainment">
                                                Window Containment (within shift window)
                                            </label>
                                            <label>
                                                <input type="radio" name="matchingMethod" value="combined">
                                                Combined (try both methods)
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="option-group">
                                        <label>When multiple shifts are scheduled on same day</label>
                                        <div class="radio-options">
                                            <label>
                                                <input type="radio" name="multipleShiftsMethod" value="priority" checked>
                                                Use shift priority order (top-down)
                                            </label>
                                            <label>
                                                <input type="radio" name="multipleShiftsMethod" value="timeProximity">
                                                Always use closest shift by time
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Advanced Matching Rules -->
                            <div class="matching-section">
                                <h5>Advanced Matching Rules</h5>
                                
                                <div class="matching-options">
                                    <div class="option-item">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="ignorePunchesOnLeave" checked>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="option-label">Ignore punches on leave days</span>
                                    </div>
                                    
                                    <div class="option-item">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="useDefaultOnFail" checked>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="option-label">Use default shift when matching fails</span>
                                    </div>
                                    
                                    <div class="option-item">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="enableShiftSwapping">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="option-label">Enable shift swapping between employees</span>
                                    </div>
                                    
                                    <div class="option-item">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="allowManualMatch" checked>
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="option-label">Allow manual match override by admin</span>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Time Banking Configuration -->
                            <div class="matching-section">
                                <h5>Time Banking Configuration</h5>
                                
                                <div class="section-description">
                                    <p>Configure how extra hours worked can be banked for future time-off or redemption.</p>
                                </div>
                                
                                <div class="matching-options">
                                    <div class="option-item">
                                        <label class="toggle-switch">
                                            <input type="checkbox" id="enableTimeBanking">
                                            <span class="toggle-slider"></span>
                                        </label>
                                        <span class="option-label">Enable Time Banking</span>
                                    </div>
                                    
                                    <div class="time-banking-options" style="display: none;">
                                        <div class="input-group">
                                            <label>Maximum bankable hours per week</label>
                                            <input type="number" id="maxBankableHours" value="10" min="0" step="0.5">
                                        </div>
                                        
                                        <div class="option-group">
                                            <label>Banking Method</label>
                                            <div class="radio-options">
                                                <label>
                                                    <input type="radio" name="bankingMethod" value="automatic">
                                                    Automatic (all eligible hours)
                                                </label>
                                                <label>
                                                    <input type="radio" name="bankingMethod" value="employeeChoice" checked>
                                                    Employee Choice (opt-in per pay period)
                                                </label>
                                                <label>
                                                    <input type="radio" name="bankingMethod" value="managerApproval">
                                                    Manager Approval Required
                                                </label>
                                            </div>
                                        </div>
                                        
                                        <div class="option-group">
                                            <label>Redemption Options</label>
                                            <div class="checkbox-options">
                                                <label>
                                                    <input type="checkbox" name="redemptionOptions" value="timeOff" checked>
                                                    Time-off redemption
                                                </label>
                                                <label>
                                                    <input type="checkbox" name="redemptionOptions" value="reducedHours" checked>
                                                    Reduced future working hours
                                                </label>
                                                <label>
                                                    <input type="checkbox" name="redemptionOptions" value="cashOut">
                                                    Cash out option
                                                </label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="form-actions">
                            <button type="button" class="action-btn primary" id="saveMatchingSettingsBtn">
                                <i class="material-icons">save</i>
                                Save Matching Settings
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="action-btn outline" id="cancelSetupBtn">Cancel</button>
            <button class="action-btn primary" id="saveAllSetupBtn">
                <i class="material-icons">save</i>
                Save All Settings
            </button>
        </div>
    </div>
</div>