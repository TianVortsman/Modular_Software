<!-- Leave Request Modal -->
<div class="modal-overlay" id="leaveRequestModalOverlay"></div>
<div class="timecard-modal" id="leaveRequestModal">
    <div class="modal-header">
        <h3 class="modal-title">
            <span class="material-icons">event_busy</span>
            Request Leave
        </h3>
        <button class="btn" onclick="closeLeaveRequestModal()">
            <span class="material-icons">close</span>
        </button>
    </div>
    <div class="modal-content">
        <form id="leaveRequestForm">
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-icons">event</span>
                        Start Date
                    </label>
                    <input type="date" class="form-control" name="startDate" required>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-icons">event</span>
                        End Date
                    </label>
                    <input type="date" class="form-control" name="endDate" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">category</span>
                    Leave Type
                </label>
                <select class="form-control" name="leaveType" required>
                    <option value="">Select Leave Type</option>
                    <option value="vacation">Vacation</option>
                    <option value="sick">Sick Leave</option>
                    <option value="personal">Personal Leave</option>
                    <option value="unpaid">Unpaid Leave</option>
                </select>
            </div>
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">description</span>
                    Reason
                </label>
                <textarea class="form-control" name="reason" rows="3" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeLeaveRequestModal()">
                    <span class="material-icons">close</span>
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons">send</span>
                    Submit Request
                </button>
            </div>
        </form>
    </div>
</div> 