<!-- Time Edit Modal -->
<div class="timecard-modal" id="timeEditModal">
    <div class="modal-header">
        <h3 class="modal-title">
            <span class="material-icons">edit</span>
            Edit Time Entry
        </h3>
        <button class="btn" onclick="closeTimeEditModal()">
            <span class="material-icons">close</span>
        </button>
    </div>
    <div class="modal-content">
        <form id="timeEditForm">
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">schedule</span>
                    Time
                </label>
                <input type="time" class="form-control" required>
            </div>
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">description</span>
                    Reason (Required)
                </label>
                <textarea class="form-control" required rows="3"></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn" onclick="closeTimeEditModal()">
                    <span class="material-icons">close</span>
                    Cancel
                </button>
                <button type="submit" class="btn btn-primary">
                    <span class="material-icons">save</span>
                    Save Changes
                </button>
            </div>
        </form>
    </div>
</div> 