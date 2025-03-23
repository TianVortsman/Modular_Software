<!-- Punches Modal -->
<div class="modal-overlay" id="punchesModalOverlay"></div>
<div class="timecard-modal" id="punchesModal">
    <div class="modal-header">
        <h3 class="modal-title">
            <span class="material-icons">fingerprint</span>
            Daily Punches
        </h3>
        <div class="modal-actions">
            <button class="btn">
                <span class="material-icons">add</span>
                Add Punch
            </button>
            <button class="btn" onclick="closePunchesModal()">
                <span class="material-icons">close</span>
                Close
            </button>
        </div>
    </div>
    <div class="modal-content">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th><span class="material-icons">schedule</span> Time</th>
                        <th><span class="material-icons">sync_alt</span> Type</th>
                        <th><span class="material-icons">devices</span> Device</th>
                        <th><span class="material-icons">verified</span> Status</th>
                        <th><span class="material-icons">settings</span> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Punches will be loaded dynamically -->
                </tbody>
            </table>
        </div>
    </div>
</div> 