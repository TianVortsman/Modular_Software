<!-- Mass Clockings Modal -->
<div class="modal-overlay" id="massClockingsModalOverlay"></div>
<div class="modal" id="massClockingsModal">
    <div class="modal-header">
        <div class="modal-title-group">
            <h3 class="modal-title">
                <span class="material-icons">schedule</span>
                Mass Clockings
            </h3>
            <div class="modal-subtitle" id="massClockingsEmployeeName">John Doe (EMP001)</div>
        </div>
        <div class="modal-actions">
            <button class="btn btn-icon" title="Close" onclick="closeMassClockingsModal()">
                <span class="material-icons">close</span>
            </button>
        </div>
    </div>
    
    <div class="modal-content">
        <div class="info-alert">
            <span class="material-icons">info</span>
            <div class="alert-content">
                Use mass clockings to apply the same clock in/out times to multiple days at once. This is useful for regular schedules or to correct multiple days with similar patterns.
            </div>
        </div>

        <form id="massClockingsForm" class="form">
            <div class="form-section">
                <h4 class="section-title">
                    <span class="material-icons">date_range</span>
                    Select Date Range
                </h4>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">
                            <span class="material-icons">event</span>
                            Start Date <span class="text-required">*</span>
                        </label>
                        <input type="date" class="form-control" name="massStartDate" id="massStartDate" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <span class="material-icons">event</span>
                            End Date <span class="text-required">*</span>
                        </label>
                        <input type="date" class="form-control" name="massEndDate" id="massEndDate" required>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label">
                        <span class="material-icons">today</span>
                        Apply To
                    </label>
                    <div class="weekday-selector">
                        <div class="weekday-option">
                            <input type="checkbox" id="mass-monday" name="massDays[]" value="monday" class="weekday-checkbox" checked>
                            <label for="mass-monday" class="weekday-label">Mon</label>
                        </div>
                        <div class="weekday-option">
                            <input type="checkbox" id="mass-tuesday" name="massDays[]" value="tuesday" class="weekday-checkbox" checked>
                            <label for="mass-tuesday" class="weekday-label">Tue</label>
                        </div>
                        <div class="weekday-option">
                            <input type="checkbox" id="mass-wednesday" name="massDays[]" value="wednesday" class="weekday-checkbox" checked>
                            <label for="mass-wednesday" class="weekday-label">Wed</label>
                        </div>
                        <div class="weekday-option">
                            <input type="checkbox" id="mass-thursday" name="massDays[]" value="thursday" class="weekday-checkbox" checked>
                            <label for="mass-thursday" class="weekday-label">Thu</label>
                        </div>
                        <div class="weekday-option">
                            <input type="checkbox" id="mass-friday" name="massDays[]" value="friday" class="weekday-checkbox" checked>
                            <label for="mass-friday" class="weekday-label">Fri</label>
                        </div>
                        <div class="weekday-option">
                            <input type="checkbox" id="mass-saturday" name="massDays[]" value="saturday" class="weekday-checkbox">
                            <label for="mass-saturday" class="weekday-label weekend-day">Sat</label>
                        </div>
                        <div class="weekday-option">
                            <input type="checkbox" id="mass-sunday" name="massDays[]" value="sunday" class="weekday-checkbox">
                            <label for="mass-sunday" class="weekday-label weekend-day">Sun</label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4 class="section-title">
                    <span class="material-icons">schedule</span>
                    Clock Times
                </h4>
                <div class="clock-times-container">
                    <div class="clock-time-row">
                        <div class="form-group">
                            <label class="form-label">
                                <span class="material-icons">login</span>
                                Clock In <span class="text-required">*</span>
                            </label>
                            <input type="time" class="form-control" name="massClockIn" id="massClockIn" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">
                                <span class="material-icons">logout</span>
                                Clock Out <span class="text-required">*</span>
                            </label>
                            <input type="time" class="form-control" name="massClockOut" id="massClockOut" required>
                        </div>
                    </div>
                    
                    <div class="form-row">
                        <div class="form-check">
                            <input type="checkbox" class="form-checkbox" id="includeBreak" name="includeBreak">
                            <label for="includeBreak" class="checkbox-label">
                                Include break time
                            </label>
                        </div>
                    </div>

                    <div id="breakTimeSection" class="break-time-section" style="display: none;">
                        <div class="clock-time-row">
                            <div class="form-group">
                                <label class="form-label">
                                    <span class="material-icons">free_breakfast</span>
                                    Break Start
                                </label>
                                <input type="time" class="form-control" name="massBreakStart" id="massBreakStart">
                            </div>
                            <div class="form-group">
                                <label class="form-label">
                                    <span class="material-icons">work</span>
                                    Break End
                                </label>
                                <input type="time" class="form-control" name="massBreakEnd" id="massBreakEnd">
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-section">
                <h4 class="section-title">
                    <span class="material-icons">settings</span>
                    Options
                </h4>
                <div class="options-container">
                    <div class="form-group">
                        <label class="form-label">
                            <span class="material-icons">work</span>
                            Shift Code
                        </label>
                        <select class="form-control" name="massShiftCode" id="massShiftCode">
                            <option value="DAY">DAY - Day Shift</option>
                            <option value="EVE">EVE - Evening Shift</option>
                            <option value="NGT">NGT - Night Shift</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">
                            <span class="material-icons">description</span>
                            Daily Description
                        </label>
                        <input type="text" class="form-control" name="massDescription" id="massDescription" placeholder="Regular Day">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">
                        <span class="material-icons">edit_note</span>
                        Reason for Mass Update <span class="text-required">*</span>
                    </label>
                    <textarea class="form-control" name="massReason" id="massReason" rows="3" required
                        placeholder="Please provide a reason for this mass update..."></textarea>
                </div>
            </div>

            <div class="form-group">
                <div class="mass-clocking-summary">
                    <h4 class="summary-title">
                        <span class="material-icons">summarize</span>
                        Summary
                    </h4>
                    <div class="summary-content">
                        <div class="summary-row">
                            <div class="summary-label">Date Range:</div>
                            <div class="summary-value" id="summaryDateRange">Not specified</div>
                        </div>
                        <div class="summary-row">
                            <div class="summary-label">Days Applied:</div>
                            <div class="summary-value" id="summaryDaysApplied">Weekdays (Mon-Fri)</div>
                        </div>
                        <div class="summary-row">
                            <div class="summary-label">Clock Times:</div>
                            <div class="summary-value" id="summaryClockTimes">Not specified</div>
                        </div>
                        <div class="summary-row">
                            <div class="summary-label">Total Days:</div>
                            <div class="summary-value" id="summaryTotalDays">0 days will be updated</div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="form-check">
                <input type="checkbox" class="form-checkbox" id="overwriteExisting" name="overwriteExisting">
                <label for="overwriteExisting" class="checkbox-label checkbox-warning">
                    Overwrite existing clock entries (if any)
                </label>
            </div>
        </form>
    </div>
    
    <div class="modal-footer">
        <div class="footer-info">
            <div class="warning-text" id="warningText">
                <span class="material-icons">warning</span>
                This will affect multiple days at once
            </div>
        </div>
        <div class="footer-actions">
            <button type="button" class="btn btn-secondary" onclick="closeMassClockingsModal()">
                <span class="material-icons">close</span>
                Cancel
            </button>
            <button type="submit" form="massClockingsForm" class="btn btn-primary">
                <span class="material-icons">save</span>
                Apply To All
            </button>
        </div>
    </div>
</div>

<!-- Mass Clockings Confirmation Modal -->
<div class="modal-overlay" id="massClockingConfirmModalOverlay"></div>
<div class="modal mini-modal" id="massClockingConfirmModal">
    <div class="modal-header">
        <h3 class="modal-title">
            <span class="material-icons">help</span>
            Confirm Mass Update
        </h3>
    </div>
    <div class="modal-content">
        <p class="confirm-message">
            You are about to update clocking data for <strong><span id="confirmTotalDays">10</span> days</strong>.
        </p>
        <div class="confirmation-details">
            <p>The following changes will be applied:</p>
            <ul class="confirmation-list">
                <li><strong>Clock In:</strong> <span id="confirmClockIn">08:00</span></li>
                <li><strong>Clock Out:</strong> <span id="confirmClockOut">17:00</span></li>
                <li><strong>Date Range:</strong> <span id="confirmDateRange">Feb 1, 2024 - Feb 14, 2024</span></li>
                <li><strong>Days Applied:</strong> <span id="confirmDaysApplied">Weekdays (Mon-Fri)</span></li>
            </ul>
        </div>
        <div class="warning-box" id="overwriteWarningBox" style="display: none;">
            <span class="material-icons">warning</span>
            <span>This will overwrite <strong><span id="overwriteCount">5</span> existing entries</strong>. This action cannot be undone.</span>
        </div>
    </div>
    <div class="modal-footer">
        <button class="btn btn-secondary" onclick="closeMassClockingConfirmModal()">
            <span class="material-icons">cancel</span>
            Cancel
        </button>
        <button class="btn btn-primary" id="confirmMassClockingBtn">
            <span class="material-icons">check_circle</span>
            Confirm Update
        </button>
    </div>
</div> 