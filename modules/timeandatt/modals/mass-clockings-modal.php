<!-- Mass Clockings Modal -->
<div class="modal-overlay" id="massClockingsModalOverlay"></div>
<div class="timecard-modal" id="massClockingsModal">
    <div class="modal-header">
        <h3 class="modal-title">
            <span class="material-icons">schedule</span>
            Mass Add Clockings
        </h3>
        <button class="btn" onclick="closeMassClockingsModal()">
            <span class="material-icons">close</span>
        </button>
    </div>
    <div class="modal-content">
        <form id="massClockingsForm">
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
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-icons">login</span>
                        Clock In Time
                    </label>
                    <input type="time" class="form-control" name="clockInTime" required>
                </div>
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-icons">logout</span>
                        Clock Out Time
                    </label>
                    <input type="time" class="form-control" name="clockOutTime" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">date_range</span>
                    Apply To Days
                </label>
                <div class="checkbox-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="days[]" value="monday"> Monday
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="days[]" value="tuesday"> Tuesday
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="days[]" value="wednesday"> Wednesday
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="days[]" value="thursday"> Thursday
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="days[]" value="friday"> Friday
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="days[]" value="saturday"> Saturday
                    </label>
                    <label class="checkbox-label">
                        <input type="checkbox" name="days[]" value="sunday"> Sunday
                    </label>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">description</span>
                    Reason
                </label>
                <textarea class="form-control" name="reason" rows="3" required></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeMassClockingsModal()">
                    <span class="material-icons">close</span>
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons">save</span>
                    Apply Clockings
                </button>
            </div>
        </form>
    </div>
</div> 