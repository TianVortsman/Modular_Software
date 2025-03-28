<!-- Time Edit Modal -->
<div class="modal-overlay" id="timeEditModalOverlay"></div>
<div class="modal" id="timeEditModal">
    <div class="modal-header">
        <div class="modal-title-group">
            <h3 class="modal-title">
                <span class="material-icons">edit_calendar</span>
                Edit Time Entry
            </h3>
            <div class="modal-subtitle" id="timeEditDateInfo">February 20, 2024</div>
        </div>
        <div class="modal-actions">
            <button class="btn btn-icon" title="Close" onclick="closeTimeEditModal()">
                <span class="material-icons">close</span>
            </button>
        </div>
    </div>
    
    <div class="modal-content">
        <form id="timeEditForm" class="form">
            <div class="form-info-card">
                <div class="info-card-content">
                    <div class="info-item">
                        <span class="material-icons">person</span>
                        <span id="timeEditEmployeeName">John Doe</span>
                    </div>
                    <div class="info-item">
                        <span class="material-icons">event</span>
                        <span id="timeEditDay">Monday</span>
                    </div>
                    <div class="info-item">
                        <span class="material-icons">work</span>
                        <span id="timeEditShift">Day Shift</span>
                    </div>
                </div>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-icons">schedule</span>
                        Current Time
                    </label>
                    <div class="form-value" id="timeEditCurrentValue">08:00</div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-icons">update</span>
                        New Time
                    </label>
                    <input type="time" class="form-control" id="timeEditNewValue" name="newTime" required>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">edit_note</span>
                    Reason for Edit <span class="text-required">*</span>
                </label>
                <textarea class="form-control" name="editReason" id="timeEditReason" rows="3" required 
                    placeholder="Please provide a detailed explanation for this time change..."></textarea>
            </div>
            
            <div class="form-group">
                <div class="checkbox-group">
                    <input type="checkbox" id="sendNotification" name="sendNotification" class="form-checkbox">
                    <label for="sendNotification" class="checkbox-label">
                        Notify employee of this change
                    </label>
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">visibility</span>
                    Audit Trail
                </label>
                <div class="audit-trail">
                    <div class="audit-item">
                        <div class="audit-info">
                            <span class="audit-action">Original Entry</span>
                            <span class="audit-timestamp">Feb 20, 2024 08:00 AM</span>
                        </div>
                        <div class="audit-user">System</div>
                    </div>
                </div>
            </div>
        </form>
    </div>
    
    <div class="modal-footer">
        <div class="footer-info">
            <div class="edit-type-indicator" id="editTypeIndicator">Start Time Edit</div>
        </div>
        <div class="footer-actions">
            <button type="button" class="btn btn-secondary" onclick="closeTimeEditModal()">
                <span class="material-icons">cancel</span>
                Cancel
            </button>
            <button type="submit" form="timeEditForm" class="btn btn-primary">
                <span class="material-icons">save</span>
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Time Edit Confirmation Modal -->
<div class="modal-overlay" id="timeEditConfirmOverlay"></div>
<div class="modal mini-modal" id="timeEditConfirmModal">
    <div class="modal-header">
        <h3 class="modal-title">
            <span class="material-icons">help</span>
            Confirm Time Change
        </h3>
    </div>
    <div class="modal-content">
        <p class="confirm-message">
            You're about to change the <span id="confirmEditType">start</span> time from 
            <span id="confirmOldTime">08:00</span> to <span id="confirmNewTime">08:30</span>.
        </p>
        <p>This change will affect calculated hours and may impact payroll. Are you sure you want to proceed?</p>
    </div>
    <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeConfirmModal()">
            <span class="material-icons">cancel</span>
            Cancel
        </button>
        <button class="btn btn-primary" id="confirmTimeEditBtn">
            <span class="material-icons">check_circle</span>
            Confirm Change
        </button>
    </div>
</div> 