<!-- Shift Changes Modal -->
<div class="modal-overlay" id="shiftChangesModalOverlay"></div>
<div class="timecard-modal" id="shiftChangesModal">
    <div class="modal-header">
        <h3 class="modal-title">
            <span class="material-icons">update</span>
            Apply Shift Change
        </h3>
        <button class="btn" onclick="closeShiftChangesModal()">
            <span class="material-icons">close</span>
        </button>
    </div>
    <div class="modal-content">
        <form id="shiftChangesForm">
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
                    <span class="material-icons">schedule</span>
                    Shift Pattern
                </label>
                <select class="form-control" name="shiftPattern" id="shiftPattern" required>
                    <option value="">Select Shift Pattern</option>
                    <option value="morning">Morning Shift (6:00 AM - 2:00 PM)</option>
                    <option value="afternoon">Afternoon Shift (2:00 PM - 10:00 PM)</option>
                    <option value="night">Night Shift (10:00 PM - 6:00 AM)</option>
                    <option value="custom">Custom Shift</option>
                </select>
            </div>
            <div id="customShiftFields" style="display: none;">
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
            </div>
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">description</span>
                    Reason for Change
                </label>
                <textarea class="form-control" name="reason" rows="3" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeShiftChangesModal()">
                    <span class="material-icons">close</span>
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons">save</span>
                    Apply Changes
                </button>
            </div>
        </form>
    </div>
</div> 