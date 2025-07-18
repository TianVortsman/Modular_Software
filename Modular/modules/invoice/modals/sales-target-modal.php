<!-- Sales Target Modal -->
<div id="salesTargetModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="salesTargetModalTitle">Add Sales Target</h2>
            <span class="close" onclick="closeSalesTargetModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="salesTargetForm" class="modal-form">
                <input type="hidden" id="sales-target-id" name="sales_target_id">
                
                <div class="form-group">
                    <label for="sales-target-user">Sales Representative</label>
                    <select id="sales-target-user" name="user_id" class="form-select">
                        <option value="">All Representatives</option>
                        <!-- Users will be loaded dynamically -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="sales-target-amount">Target Amount *</label>
                    <input type="number" id="sales-target-amount" name="target_amount" class="form-input" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label for="sales-target-period">Period *</label>
                    <select id="sales-target-period" name="period" class="form-select" required>
                        <option value="monthly">Monthly</option>
                        <option value="weekly">Weekly</option>
                        <option value="quarterly">Quarterly</option>
                        <option value="yearly">Yearly</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="sales-target-start-date">Start Date</label>
                    <input type="date" id="sales-target-start-date" name="start_date" class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="sales-target-end-date">End Date</label>
                    <input type="date" id="sales-target-end-date" name="end_date" class="form-input">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeSalesTargetModal()">Cancel</button>
                    <button type="submit" class="btn-primary">
                        <span class="material-icons">save</span> Save Target
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>