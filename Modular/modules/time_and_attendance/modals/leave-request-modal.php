<!-- Leave Request Modal -->
<div class="modal-overlay" id="leaveRequestModalOverlay"></div>
<div class="modal" id="leaveRequestModal">
    <div class="modal-header">
        <div class="modal-title-group">
            <h3 class="modal-title">
                <span class="material-icons">event_busy</span>
                Request Leave
            </h3>
            <div class="modal-subtitle" id="leaveEmployeeName">John Doe (EMP001)</div>
        </div>
        <div class="modal-actions">
            <button class="btn btn-icon" title="Close" onclick="closeLeaveRequestModal()">
                <span class="material-icons">close</span>
            </button>
        </div>
    </div>
    
    <div class="modal-content">
        <div class="leave-balance-container">
            <h4 class="section-title">
                <span class="material-icons">account_balance</span>
                Leave Balance
            </h4>
            <div class="leave-balance-cards">
                <div class="balance-card">
                    <div class="balance-icon vacation-icon">
                        <span class="material-icons">beach_access</span>
                    </div>
                    <div class="balance-info">
                        <div class="balance-type">Vacation</div>
                        <div class="balance-value">15 days</div>
                    </div>
                </div>
                <div class="balance-card">
                    <div class="balance-icon sick-icon">
                        <span class="material-icons">healing</span>
                    </div>
                    <div class="balance-info">
                        <div class="balance-type">Sick Leave</div>
                        <div class="balance-value">8 days</div>
                    </div>
                </div>
                <div class="balance-card">
                    <div class="balance-icon personal-icon">
                        <span class="material-icons">person</span>
                    </div>
                    <div class="balance-info">
                        <div class="balance-type">Personal</div>
                        <div class="balance-value">3 days</div>
                    </div>
                </div>
                <div class="balance-card">
                    <div class="balance-icon other-icon">
                        <span class="material-icons">more_horiz</span>
                    </div>
                    <div class="balance-info">
                        <div class="balance-type">Other</div>
                        <div class="balance-value">5 days</div>
                    </div>
                </div>
            </div>
        </div>

        <form id="leaveRequestForm" class="form">
            <h4 class="section-title">
                <span class="material-icons">event_note</span>
                Leave Details
            </h4>
            
            <div class="leave-types-selector">
                <div class="leave-type-option" onclick="selectLeaveType('vacation')">
                    <input type="radio" name="leaveType" id="vacation" value="vacation" class="leave-type-radio">
                    <label for="vacation" class="leave-type-label">
                        <div class="leave-type-icon vacation-icon">
                            <span class="material-icons">beach_access</span>
                        </div>
                        <div class="leave-type-text">Vacation</div>
                    </label>
                </div>
                <div class="leave-type-option" onclick="selectLeaveType('sick')">
                    <input type="radio" name="leaveType" id="sick" value="sick" class="leave-type-radio">
                    <label for="sick" class="leave-type-label">
                        <div class="leave-type-icon sick-icon">
                            <span class="material-icons">healing</span>
                        </div>
                        <div class="leave-type-text">Sick Leave</div>
                    </label>
                </div>
                <div class="leave-type-option" onclick="selectLeaveType('personal')">
                    <input type="radio" name="leaveType" id="personal" value="personal" class="leave-type-radio">
                    <label for="personal" class="leave-type-label">
                        <div class="leave-type-icon personal-icon">
                            <span class="material-icons">person</span>
                        </div>
                        <div class="leave-type-text">Personal</div>
                    </label>
                </div>
                <div class="leave-type-option" onclick="selectLeaveType('unpaid')">
                    <input type="radio" name="leaveType" id="unpaid" value="unpaid" class="leave-type-radio">
                    <label for="unpaid" class="leave-type-label">
                        <div class="leave-type-icon unpaid-icon">
                            <span class="material-icons">money_off</span>
                        </div>
                        <div class="leave-type-text">Unpaid</div>
                    </label>
                </div>
                <div class="leave-type-option" onclick="selectLeaveType('other')">
                    <input type="radio" name="leaveType" id="other" value="other" class="leave-type-radio">
                    <label for="other" class="leave-type-label">
                        <div class="leave-type-icon other-icon">
                            <span class="material-icons">more_horiz</span>
                        </div>
                        <div class="leave-type-text">Other</div>
                    </label>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-icons">event</span>
                        Start Date <span class="text-required">*</span>
                    </label>
                    <div class="date-input-container">
                        <input type="date" class="form-control" name="startDate" id="startDate" required>
                        <select class="form-control time-select" name="startTime" id="startTime">
                            <option value="full">Full Day</option>
                            <option value="morning">Morning Only</option>
                            <option value="afternoon">Afternoon Only</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-icons">event</span>
                        End Date <span class="text-required">*</span>
                    </label>
                    <div class="date-input-container">
                        <input type="date" class="form-control" name="endDate" id="endDate" required>
                        <select class="form-control time-select" name="endTime" id="endTime">
                            <option value="full">Full Day</option>
                            <option value="morning">Morning Only</option>
                            <option value="afternoon">Afternoon Only</option>
                        </select>
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">calculate</span>
                    Total Days
                </label>
                <div class="days-calculation">
                    <div class="calculation-result" id="totalDays">1 day</div>
                    <div class="calculation-breakdown">
                        <span id="workDays">1</span> working days (<span id="leaveHours">8</span> hours)
                    </div>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">description</span>
                    Reason <span class="text-required">*</span>
                </label>
                <textarea class="form-control" name="reason" id="leaveReason" rows="3" required
                    placeholder="Please provide details about your leave request..."></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">contact_mail</span>
                    Contact While Away
                </label>
                <input type="text" class="form-control" name="contactInfo" id="contactInfo" 
                    placeholder="Phone number or email where you can be reached if needed">
            </div>
            
            <div class="form-check">
                <input type="checkbox" class="form-checkbox" id="notifyManager" name="notifyManager" checked>
                <label for="notifyManager" class="checkbox-label">
                    Send notification to my manager
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
            <button type="button" class="btn btn-secondary" onclick="closeLeaveRequestModal()">
                <span class="material-icons">close</span>
                Cancel
            </button>
            <button type="submit" form="leaveRequestForm" class="btn btn-primary">
                <span class="material-icons">send</span>
                Submit Request
            </button>
        </div>
    </div>
</div>

<!-- Leave Request Confirmation Modal -->
<div class="modal-overlay" id="leaveConfirmModalOverlay"></div>
<div class="modal mini-modal" id="leaveConfirmModal">
    <div class="modal-header">
        <h3 class="modal-title">
            <span class="material-icons">check_circle</span>
            Leave Request Submitted
        </h3>
    </div>
    <div class="modal-content">
        <div class="confirmation-icon">
            <span class="material-icons">task_alt</span>
        </div>
        <h4 class="confirmation-title">Your leave request has been submitted successfully!</h4>
        <p class="confirmation-details">
            <strong>Type:</strong> <span id="confirmLeaveType">Vacation</span><br>
            <strong>Dates:</strong> <span id="confirmLeaveDates">Feb 10, 2024 - Feb 15, 2024</span><br>
            <strong>Total:</strong> <span id="confirmLeaveDays">5 days</span>
        </p>
        <p class="confirmation-message">
            Your request will be reviewed by your manager. You will be notified once it has been approved or rejected.
        </p>
    </div>
    <div class="modal-footer">
        <button class="btn btn-primary" onclick="closeAllLeaveModals()">
            <span class="material-icons">check</span>
            Done
        </button>
    </div>
</div> 