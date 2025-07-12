<!-- Credit Reason Modal -->
<div id="creditReasonModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="creditReasonModalTitle">Add Credit Reason</h2>
            <span class="close" onclick="closeCreditReasonModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="creditReasonForm" class="modal-form">
                <input type="hidden" id="credit-reason-id" name="credit_reason_id">
                
                <div class="form-group">
                    <label for="credit-reason-text">Reason *</label>
                    <input type="text" id="credit-reason-text" name="reason" class="form-input" required>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeCreditReasonModal()">Cancel</button>
                    <button type="submit" class="btn-primary">
                        <span class="material-icons">save</span> Save Reason
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openCreditReasonModal(reasonId = null) {
    const modal = document.getElementById('creditReasonModal');
    const title = document.getElementById('creditReasonModalTitle');
    const form = document.getElementById('creditReasonForm');
    
    // Reset form
    form.reset();
    document.getElementById('credit-reason-id').value = '';
    
    if (reasonId) {
        title.textContent = 'Edit Credit Reason';
        // Load reason data for editing
        loadCreditReasonData(reasonId);
    } else {
        title.textContent = 'Add Credit Reason';
    }
    
    modal.style.display = 'block';
}

function closeCreditReasonModal() {
    const modal = document.getElementById('creditReasonModal');
    modal.style.display = 'none';
}

function loadCreditReasonData(reasonId) {
    // This would load existing credit reason data for editing
    // Implementation depends on your data loading approach
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('creditReasonModal');
    if (event.target === modal) {
        closeCreditReasonModal();
    }
}
</script> 