<!-- Shift Changes Modal -->
<div class="modal-overlay" id="shiftChangesModalOverlay"></div>
<div class="modal" id="shiftChangesModal">
    <div class="modal-header">
        <div class="modal-title-group">
            <h3 class="modal-title">
                <span class="material-icons">update</span>
                Apply Shift Change
            </h3>
            <div class="modal-subtitle" id="shiftChangeEmployeeName">John Doe (EMP001)</div>
        </div>
        <div class="modal-actions">
            <button class="btn btn-icon" title="Close" onclick="closeShiftChangesModal()">
                <span class="material-icons">close</span>
            </button>
        </div>
    </div>
    
    <div class="modal-content">
        <div class="shift-info-card">
            <h4 class="section-title">
                <span class="material-icons">work</span>
                Current Schedule
            </h4>
            <div class="current-shifts">
                <div class="current-shift-item">
                    <div class="shift-badge day-shift">DAY</div>
                    <div class="shift-details">
                        <div class="shift-name">Day Shift</div>
                        <div class="shift-time">8:00 AM - 5:00 PM</div>
                        <div class="shift-days">Monday to Friday</div>
                    </div>
                </div>
            </div>
        </div>

        <form id="shiftChangesForm" class="form">
            <h4 class="section-title">
                <span class="material-icons">edit_calendar</span>
                Schedule Change
            </h4>
            
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">sync_alt</span>
                    Change Type
                </label>
                <div class="change-type-selector">
                    <div class="change-type-option">
                        <input type="radio" name="changeType" id="temporaryChange" value="temporary" class="change-type-radio" checked>
                        <label for="temporaryChange" class="radio-label">
                            <span class="material-icons">date_range</span>
                            Temporary Change
                        </label>
                    </div>
                    <div class="change-type-option">
                        <input type="radio" name="changeType" id="permanentChange" value="permanent" class="change-type-radio">
                        <label for="permanentChange" class="radio-label">
                            <span class="material-icons">update</span>
                            Permanent Change
                        </label>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-icons">event</span>
                        Start Date <span class="text-required">*</span>
                    </label>
                    <input type="date" class="form-control" name="startDate" id="shiftStartDate" required>
                </div>
                <div class="form-group" id="endDateContainer">
                    <label class="form-label">
                        <span class="material-icons">event</span>
                        End Date <span class="text-required">*</span>
                    </label>
                    <input type="date" class="form-control" name="endDate" id="shiftEndDate" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">schedule</span>
                    Shift Pattern <span class="text-required">*</span>
                </label>
                <div class="shift-pattern-selector">
                    <div class="shift-pattern-option">
                        <input type="radio" name="shiftPattern" id="morningShift" value="morning" class="shift-pattern-radio">
                        <label for="morningShift" class="pattern-label">
                            <div class="pattern-header">
                                <div class="shift-badge morning-shift">AM</div>
                                <div class="pattern-title">Morning Shift</div>
                            </div>
                            <div class="pattern-time">6:00 AM - 2:00 PM</div>
                        </label>
                    </div>
                    <div class="shift-pattern-option">
                        <input type="radio" name="shiftPattern" id="afternoonShift" value="afternoon" class="shift-pattern-radio">
                        <label for="afternoonShift" class="pattern-label">
                            <div class="pattern-header">
                                <div class="shift-badge afternoon-shift">PM</div>
                                <div class="pattern-title">Afternoon Shift</div>
                            </div>
                            <div class="pattern-time">2:00 PM - 10:00 PM</div>
                        </label>
                    </div>
                    <div class="shift-pattern-option">
                        <input type="radio" name="shiftPattern" id="nightShift" value="night" class="shift-pattern-radio">
                        <label for="nightShift" class="pattern-label">
                            <div class="pattern-header">
                                <div class="shift-badge night-shift">NT</div>
                                <div class="pattern-title">Night Shift</div>
                            </div>
                            <div class="pattern-time">10:00 PM - 6:00 AM</div>
                        </label>
                    </div>
                    <div class="shift-pattern-option">
                        <input type="radio" name="shiftPattern" id="customShift" value="custom" class="shift-pattern-radio">
                        <label for="customShift" class="pattern-label">
                            <div class="pattern-header">
                                <div class="shift-badge custom-shift">CS</div>
                                <div class="pattern-title">Custom Shift</div>
                            </div>
                            <div class="pattern-time">Define custom hours</div>
                        </label>
                    </div>
                </div>
            </div>
            
            <div id="customShiftFields" class="form-group" style="display: none;">
                <div class="custom-shift-container">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">
                                <span class="material-icons">login</span>
                                Start Time
                            </label>
                            <input type="time" class="form-control" name="customStartTime">
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <span class="material-icons">logout</span>
                                End Time
                            </label>
                            <input type="time" class="form-control" name="customEndTime">
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">
                            <span class="material-icons">today</span>
                            Working Days
                        </label>
                        <div class="weekday-selector">
                            <div class="weekday-option">
                                <input type="checkbox" id="monday" name="workingDays[]" value="monday" class="weekday-checkbox">
                                <label for="monday" class="weekday-label">Mon</label>
                            </div>
                            <div class="weekday-option">
                                <input type="checkbox" id="tuesday" name="workingDays[]" value="tuesday" class="weekday-checkbox">
                                <label for="tuesday" class="weekday-label">Tue</label>
                            </div>
                            <div class="weekday-option">
                                <input type="checkbox" id="wednesday" name="workingDays[]" value="wednesday" class="weekday-checkbox">
                                <label for="wednesday" class="weekday-label">Wed</label>
                            </div>
                            <div class="weekday-option">
                                <input type="checkbox" id="thursday" name="workingDays[]" value="thursday" class="weekday-checkbox">
                                <label for="thursday" class="weekday-label">Thu</label>
                            </div>
                            <div class="weekday-option">
                                <input type="checkbox" id="friday" name="workingDays[]" value="friday" class="weekday-checkbox">
                                <label for="friday" class="weekday-label">Fri</label>
                            </div>
                            <div class="weekday-option">
                                <input type="checkbox" id="saturday" name="workingDays[]" value="saturday" class="weekday-checkbox">
                                <label for="saturday" class="weekday-label weekend-day">Sat</label>
                            </div>
                            <div class="weekday-option">
                                <input type="checkbox" id="sunday" name="workingDays[]" value="sunday" class="weekday-checkbox">
                                <label for="sunday" class="weekday-label weekend-day">Sun</label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">description</span>
                    Reason for Change <span class="text-required">*</span>
                </label>
                <textarea class="form-control" name="reason" id="shiftChangeReason" rows="3" required
                    placeholder="Please provide details for this shift change request..."></textarea>
            </div>
            
            <div class="form-group">
                <div class="shift-change-summary">
                    <h4 class="summary-title">
                        <span class="material-icons">summarize</span>
                        Change Summary
                    </h4>
                    <div class="summary-content">
                        <div class="summary-row">
                            <div class="summary-label">Original Shift:</div>
                            <div class="summary-value" id="originalShiftSummary">Day Shift (8:00 AM - 5:00 PM)</div>
                        </div>
                        <div class="summary-row">
                            <div class="summary-label">New Shift:</div>
                            <div class="summary-value" id="newShiftSummary">Not selected</div>
                        </div>
                        <div class="summary-row">
                            <div class="summary-label">Duration:</div>
                            <div class="summary-value" id="durationSummary">Not specified</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-check">
                <input type="checkbox" class="form-checkbox" id="notifyEmployee" name="notifyEmployee" checked>
                <label for="notifyEmployee" class="checkbox-label">
                    Notify employee of this change
                </label>
            </div>
        </form>
    </div>
    
    <div class="modal-footer">
        <div class="footer-info">
            <div class="approval-flow">
                <span class="material-icons">approval</span>
                Requires manager approval
            </div>
        </div>
        <div class="footer-actions">
            <button type="button" class="btn btn-secondary" onclick="closeShiftChangesModal()">
                <span class="material-icons">close</span>
                Cancel
            </button>
            <button type="submit" form="shiftChangesForm" class="btn btn-primary">
                <span class="material-icons">save</span>
                Apply Changes
            </button>
        </div>
    </div>
</div>

<!-- Shift Change Confirmation Modal -->
<div class="modal-overlay" id="shiftChangeConfirmModalOverlay"></div>
<div class="modal mini-modal" id="shiftChangeConfirmModal">
    <div class="modal-header">
        <h3 class="modal-title">
            <span class="material-icons">check_circle</span>
            Shift Change Successful
        </h3>
    </div>
    <div class="modal-content">
        <div class="confirmation-icon">
            <span class="material-icons">task_alt</span>
        </div>
        <h4 class="confirmation-title">Shift change has been applied successfully!</h4>
        <p class="confirmation-details">
            <strong>Employee:</strong> <span id="confirmShiftEmployee">John Doe</span><br>
            <strong>New Shift:</strong> <span id="confirmNewShift">Morning Shift (6:00 AM - 2:00 PM)</span><br>
            <strong>Period:</strong> <span id="confirmShiftPeriod">Feb 22, 2024 - Feb 28, 2024</span>
        </p>
        <p class="confirmation-message">
            The timecard has been updated to reflect these changes.
        </p>
    </div>
    <div class="modal-footer">
        <button class="btn btn-primary" onclick="closeAllShiftModals()">
            <span class="material-icons">check</span>
            Done
        </button>
    </div>
</div> 