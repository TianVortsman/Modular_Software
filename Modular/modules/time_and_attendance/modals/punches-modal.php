<!-- Punches Modal -->
<div class="modal-overlay" id="punchesModalOverlay"></div>
<div class="modal" id="punchesModal">
    <div class="modal-header">
        <div class="modal-title-group">
            <h3 class="modal-title">
                <span class="material-icons">fingerprint</span>
                Daily Punches
            </h3>
            <div class="modal-subtitle" id="punchesDateInfo">February 20, 2024</div>
        </div>
        <div class="modal-actions">
            <button class="btn btn-secondary" id="addPunchBtn">
                <span class="material-icons">add_circle</span>
                Add Punch
            </button>
            <button class="btn btn-icon" title="Close" onclick="closePunchesModal()">
                <span class="material-icons">close</span>
            </button>
        </div>
    </div>

    <div class="modal-content">
        <div class="punches-info-card">
            <div class="info-card-content">
                <div class="info-item">
                    <span class="material-icons">person</span>
                    <span id="punchesEmployeeName">John Doe</span>
                </div>
                <div class="info-item">
                    <span class="material-icons">badge</span>
                    <span id="punchesEmployeeId">EMP001</span>
                </div>
                <div class="info-item">
                    <span class="material-icons">work</span>
                    <span id="punchesShift">Day Shift (08:00 - 17:00)</span>
                </div>
            </div>
        </div>

        <div class="punches-timeline">
            <div class="timeline-header">
                <h4>Punch Timeline</h4>
                <div class="timeline-legend">
                    <div class="legend-item">
                        <span class="legend-color legend-in"></span>
                        <span>Clock In</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color legend-out"></span>
                        <span>Clock Out</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color legend-break"></span>
                        <span>Break</span>
                    </div>
                    <div class="legend-item">
                        <span class="legend-color legend-manual"></span>
                        <span>Manual Entry</span>
                    </div>
                </div>
            </div>
            <div class="timeline-container">
                <div class="timeline-scale">
                    <div class="time-marker">6:00</div>
                    <div class="time-marker">8:00</div>
                    <div class="time-marker">10:00</div>
                    <div class="time-marker">12:00</div>
                    <div class="time-marker">14:00</div>
                    <div class="time-marker">16:00</div>
                    <div class="time-marker">18:00</div>
                    <div class="time-marker">20:00</div>
                </div>
                <div class="timeline-graph">
                    <div class="shift-period" style="left: 25%; width: 56.25%;">
                        <span class="shift-label">Regular Shift</span>
                    </div>
                    <div class="punch-point punch-in" style="left: 25%;" title="Clock In: 08:00" ondblclick="editPunch(1)">
                        <span class="punch-time">08:00</span>
                    </div>
                    <div class="punch-point punch-break-start" style="left: 50%;" title="Break Start: 12:00" ondblclick="editPunch(2)">
                        <span class="punch-time">12:00</span>
                    </div>
                    <div class="punch-point punch-break-end" style="left: 56.25%;" title="Break End: 13:00" ondblclick="editPunch(3)">
                        <span class="punch-time">13:00</span>
                    </div>
                    <div class="punch-point punch-out" style="left: 81.25%;" title="Clock Out: 17:00" ondblclick="editPunch(4)">
                        <span class="punch-time">17:00</span>
                    </div>
                </div>
            </div>
        </div>

        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th width="5%"><span class="material-icons">tag</span> ID</th>
                        <th width="15%"><span class="material-icons">schedule</span> Time</th>
                        <th width="15%"><span class="material-icons">sync_alt</span> Type</th>
                        <th width="20%"><span class="material-icons">location_on</span> Location</th>
                        <th width="15%"><span class="material-icons">devices</span> Device</th>
                        <th width="15%"><span class="material-icons">verified</span> Status</th>
                        <th width="15%"><span class="material-icons">settings</span> Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <tr ondblclick="editPunch(1)">
                        <td>1</td>
                        <td>08:00</td>
                        <td><span class="punch-type punch-in-type">Clock In</span></td>
                        <td>Main Entrance</td>
                        <td>Biometric Terminal</td>
                        <td><span class="status-indicator status-approved">Verified</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-icon btn-small" title="Edit" ondblclick="editPunch(1)">
                                    <span class="material-icons">edit</span>
                                </button>
                                <button class="btn btn-icon btn-small" title="Delete" ondblclick="deletePunch(1)">
                                    <span class="material-icons">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr ondblclick="editPunch(2)">
                        <td>2</td>
                        <td>12:00</td>
                        <td><span class="punch-type punch-break-type">Break Start</span></td>
                        <td>Cafeteria</td>
                        <td>Mobile App</td>
                        <td><span class="status-indicator status-approved">Verified</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-icon btn-small" title="Edit" ondblclick="editPunch(2)">
                                    <span class="material-icons">edit</span>
                                </button>
                                <button class="btn btn-icon btn-small" title="Delete" ondblclick="deletePunch(2)">
                                    <span class="material-icons">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr ondblclick="editPunch(3)">
                        <td>3</td>
                        <td>13:00</td>
                        <td><span class="punch-type punch-break-type">Break End</span></td>
                        <td>Cafeteria</td>
                        <td>Mobile App</td>
                        <td><span class="status-indicator status-approved">Verified</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-icon btn-small" title="Edit" ondblclick="editPunch(3)">
                                    <span class="material-icons">edit</span>
                                </button>
                                <button class="btn btn-icon btn-small" title="Delete" ondblclick="deletePunch(3)">
                                    <span class="material-icons">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                    <tr ondblclick="editPunch(4)">
                        <td>4</td>
                        <td>17:00</td>
                        <td><span class="punch-type punch-out-type">Clock Out</span></td>
                        <td>Main Entrance</td>
                        <td>Biometric Terminal</td>
                        <td><span class="status-indicator status-approved">Verified</span></td>
                        <td>
                            <div class="action-buttons">
                                <button class="btn btn-icon btn-small" title="Edit" ondblclick="editPunch(4)">
                                    <span class="material-icons">edit</span>
                                </button>
                                <button class="btn btn-icon btn-small" title="Delete" ondblclick="deletePunch(4)">
                                    <span class="material-icons">delete</span>
                                </button>
                            </div>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal-footer">
        <div class="footer-info">
            <div class="punch-stats">
                <span class="stats-label">Total Work:</span>
                <span class="stats-value">8.0 hrs</span>
                <span class="stats-divider">|</span>
                <span class="stats-label">Break:</span>
                <span class="stats-value">1.0 hr</span>
            </div>
        </div>
        <div class="footer-actions">
            <button class="btn btn-secondary" onclick="closePunchesModal()">
                <span class="material-icons">close</span>
                Close
            </button>
            <button class="btn btn-primary" id="savePunchesBtn">
                <span class="material-icons">save</span>
                Save Changes
            </button>
        </div>
    </div>
</div>

<!-- Add/Edit Punch Modal -->
<div class="modal-overlay" id="editPunchModalOverlay"></div>
<div class="modal mini-modal" id="editPunchModal">
    <div class="modal-header">
        <h3 class="modal-title" id="punchEditTitle">
            <span class="material-icons">add_circle</span>
            Add New Punch
        </h3>
        <button class="btn btn-icon" title="Close" onclick="closeEditPunchModal()">
            <span class="material-icons">close</span>
        </button>
    </div>
    <div class="modal-content">
        <form id="editPunchForm" class="form">
            <input type="hidden" id="punchId" name="punchId" value="">
            
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">schedule</span>
                    Time
                </label>
                <input type="time" class="form-control" id="punchTime" name="punchTime" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">sync_alt</span>
                    Punch Type
                </label>
                <select class="form-control" id="punchType" name="punchType" required>
                    <option value="in">Clock In</option>
                    <option value="out">Clock Out</option>
                    <option value="break_start">Break Start</option>
                    <option value="break_end">Break End</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">location_on</span>
                    Location
                </label>
                <select class="form-control" id="punchLocation" name="punchLocation">
                    <option value="main_entrance">Main Entrance</option>
                    <option value="back_entrance">Back Entrance</option>
                    <option value="cafeteria">Cafeteria</option>
                    <option value="remote">Remote</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">
                    <span class="material-icons">devices</span>
                    Device
                </label>
                <select class="form-control" id="punchDevice" name="punchDevice">
                    <option value="biometric">Biometric Terminal</option>
                    <option value="card_reader">Card Reader</option>
                    <option value="mobile">Mobile App</option>
                    <option value="web">Web Portal</option>
                    <option value="manual">Manual Entry</option>
                </select>
            </div>
            
            <div class="form-group" id="reasonGroup">
                <label class="form-label">
                    <span class="material-icons">description</span>
                    Reason <span class="text-required">*</span>
                </label>
                <textarea class="form-control" id="punchReason" name="punchReason" rows="3" 
                    placeholder="Please provide a reason for this manual entry or edit..."></textarea>
                <div class="form-help">A reason is required for manual entries or edits to existing punches.</div>
            </div>
        </form>
    </div>
    <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeEditPunchModal()">
            <span class="material-icons">cancel</span>
            Cancel
        </button>
        <button type="submit" form="editPunchForm" class="btn btn-primary">
            <span class="material-icons">save</span>
            <span id="savePunchBtnText">Save Punch</span>
        </button>
    </div>
</div>

<!-- Delete Punch Confirmation Modal -->
<div class="modal-overlay" id="deletePunchModalOverlay"></div>
<div class="modal mini-modal" id="deletePunchModal">
    <div class="modal-header">
        <h3 class="modal-title">
            <span class="material-icons">delete</span>
            Delete Punch
        </h3>
    </div>
    <div class="modal-content">
        <p>Are you sure you want to delete this punch?</p>
        <div class="delete-punch-details">
            <div class="delete-punch-info">
                <span class="info-label">Time:</span>
                <span id="deletePunchTime" class="info-value">08:00</span>
            </div>
            <div class="delete-punch-info">
                <span class="info-label">Type:</span>
                <span id="deletePunchType" class="info-value">Clock In</span>
            </div>
        </div>
        <div class="form-group">
            <label class="form-label">
                <span class="material-icons">description</span>
                Reason for Deletion <span class="text-required">*</span>
            </label>
            <textarea class="form-control" id="deletePunchReason" rows="3" required 
                placeholder="Please provide a reason for deleting this punch..."></textarea>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeDeletePunchModal()">
            <span class="material-icons">cancel</span>
            Cancel
        </button>
        <button class="btn btn-danger" id="confirmDeletePunchBtn">
            <span class="material-icons">delete_forever</span>
            Delete Punch
        </button>
    </div>
</div> 