<!-- Credit Reason Modal -->
<div id="creditReasonModal" class="modal" data-mode="add" style="display:none;">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="creditReasonModalTitle">Credit Reason</h2>
            <button type="button" class="modal-close-btn">&times;</button>
        </div>
        <div class="modal-body">
            <form id="creditReasonForm">
                <input type="hidden" name="credit_reason_id" value="">
                <div class="form-group">
                    <label for="credit_reason_text">Reason</label>
                    <input type="text" name="reason" id="credit_reason_text" required>
                </div>
                <div class="modal-actions">
                    <button type="submit" class="btn btn-primary">Save</button>
                    <button type="button" class="btn btn-secondary modal-close-btn">Cancel</button>
                </div>
            </form>
        </div>
    </div>
</div>
<!-- Use CSS classes for consistent modal appearance --> 