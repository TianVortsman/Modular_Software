    <!-- Add Shift Modal -->
    <div class="modal" id="addShiftModal">
        <div class="modal-content shift-modal">
            <div class="modal-header">
                <h3>Add New Shift</h3>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="shift-tabs">
                    <div class="shift-tab-buttons">
                        <button class="shift-tab-btn active" data-tab="shift-details">
                            <i class="material-icons">info</i>
                            Shift Details
                        </button>
                        <button class="shift-tab-btn" data-tab="shift-times">
                            <i class="material-icons">schedule</i>
                            Shift Times
                        </button>
                        <button class="shift-tab-btn" data-tab="shift-holidays" style="display: none;">
                            <i class="material-icons">event</i>
                            Holidays
                        </button>
                        <button class="shift-tab-btn" data-tab="shift-night-allowance" style="display: none;">
                            <i class="material-icons">nightlight</i>
                            Night Allowance
                        </button>
                        <button class="shift-tab-btn" data-tab="shift-split-time" style="display: none;">
                            <i class="material-icons">call_split</i>
                            Split Time
                        </button>
                    </div>

                    <!-- Shift Details Tab -->
                    <div class="shift-tab-content active" id="shift-details-tab">
                        <form id="shiftDetailsForm">
                            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                            <div class="form-group">
                                <label for="shiftName">Shift Name</label>
                                <input type="text" id="shiftName" name="shiftName" required>
                            </div>
                            <div class="form-group">
                                <label for="shiftTarget">Shift Target Hours</label>
                                <input type="number" id="shiftTarget" name="shiftTarget" step="0.5" required>
                            </div>
                            <div class="form-group">
                                <label for="normalTimeCategory">Normal Time Category</label>
                                <select id="normalTimeCategory" name="normalTimeCategory" required>
                                    <!-- Time categories will be loaded here -->
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="overtimeCategory">Overtime Category</label>
                                <select id="overtimeCategory" name="overtimeCategory" required>
                                    <!-- Time categories will be loaded here -->
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="shiftCounter">Hours for Shift Counter</label>
                                <input type="number" id="shiftCounter" name="shiftCounter" step="0.5" required>
                            </div>
                            <div class="form-group">
                                <label for="payPeriod">Pay Period</label>
                                <select id="payPeriod" name="payPeriod" required>
                                    <option value="">Select Pay Period</option>
                                    <option value="weekly">Weekly</option>
                                    <option value="biweekly">Bi-Weekly</option>
                                    <option value="monthly">Monthly</option>
                                    <option value="custom">Custom</option>
                                </select>
                            </div>
                            <div class="form-group custom-period-details" style="display: none;">
                                <label for="periodStartDate">Period Start Date</label>
                                <input type="date" id="periodStartDate" name="periodStartDate">
                                <label for="periodEndDate">Period End Date</label>
                                <input type="date" id="periodEndDate" name="periodEndDate">
                                <label for="periodDays">Number of Days</label>
                                <input type="number" id="periodDays" name="periodDays" min="1">
                            </div>
                            <div class="form-group">
                                <label for="shiftType">Shift Type</label>
                                <select id="shiftType" name="shiftType" required>
                                    <option value="regular">Regular</option>
                                    <option value="rotating">Rotating</option>
                                    <option value="split">Split Shift</option>
                                    <option value="on_call">On-Call</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="shiftPattern">Shift Pattern (for rotating shifts)</label>
                                <input type="text" id="shiftPattern" name="shiftPattern" placeholder="e.g., 4 days on, 4 days off">
                            </div>
                            <div class="form-group">
                                <label for="breakType">Break Type</label>
                                <select id="breakType" name="breakType" required>
                                    <option value="paid">Paid Break</option>
                                    <option value="unpaid">Unpaid Break</option>
                                    <option value="none">No Break</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="breakDuration">Break Duration (minutes)</label>
                                <input type="number" id="breakDuration" name="breakDuration" min="0" value="30">
                            </div>
                            <div class="form-group">
                                <label for="shiftColor">Shift Color</label>
                                <input type="color" id="shiftColor" name="shiftColor" value="#007bff">
                            </div>
                            <div class="form-group">
                                <label>Punch Handling</label>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="punchHandling" value="first_last" checked>
                                        Use First and Last Punch Only
                                    </label>
                                    <label>
                                        <input type="radio" name="punchHandling" value="ignore">
                                        Ignore First and Last Punch
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="singleClosingShift" name="singleClosingShift">
                                    Single Closing Shift
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="paidHolidays" name="paidHolidays">
                                    Paid Holidays
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="nightShiftAllowance" name="nightShiftAllowance">
                                    Night Shift Allowance
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="splitNormalTime" name="splitNormalTime">
                                    Split Normal Time
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="requiresApproval" name="requiresApproval">
                                    Requires Approval
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="allowOvertime" name="allowOvertime">
                                    Allow Overtime
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="allowEarlyClockIn" name="allowEarlyClockIn">
                                    Allow Early Clock In
                                </label>
                            </div>
                            <div class="form-group">
                                <label>
                                    <input type="checkbox" id="allowLateClockOut" name="allowLateClockOut">
                                    Allow Late Clock Out
                                </label>
                            </div>
                            <div class="form-group">
                                <label for="earlyClockInLimit">Early Clock In Limit (minutes)</label>
                                <input type="number" id="earlyClockInLimit" name="earlyClockInLimit" min="0" value="15">
                            </div>
                            <div class="form-group">
                                <label for="lateClockOutLimit">Late Clock Out Limit (minutes)</label>
                                <input type="number" id="lateClockOutLimit" name="lateClockOutLimit" min="0" value="15">
                            </div>
                            <div class="form-group">
                                <label for="shiftRules">Shift Rules</label>
                                <textarea id="shiftRules" name="shiftRules" rows="4" placeholder="Enter any specific rules or requirements for this shift"></textarea>
                            </div>
                        </form>
                    </div>

                    <!-- Shift Times Tab -->
                    <div class="shift-tab-content" id="shift-times-tab">
                        <form id="shiftTimesForm">
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="startTime">Start Time</label>
                                    <input type="time" id="startTime" name="startTime" required data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="endTime">End Time</label>
                                    <input type="time" id="endTime" name="endTime" required data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="earliestStart">Earliest Start (Optional)</label>
                                    <input type="time" id="earliestStart" name="earliestStart" data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="latestEnd">Latest End (Optional)</label>
                                    <input type="time" id="latestEnd" name="latestEnd" data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="roundingProfile">Rounding Profile</label>
                                <select id="roundingProfile" name="roundingProfile" required>
                                    <!-- Rounding profiles will be loaded here -->
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- Holidays Tab -->
                    <div class="shift-tab-content" id="shift-holidays-tab">
                        <form id="shiftHolidaysForm">
                            <div class="form-group">
                                <label>Holiday Payment Rules</label>
                                <div class="radio-group">
                                    <label>
                                        <input type="radio" name="holidayPayment" value="work_only" checked>
                                        Pay Only if Worked
                                    </label>
                                    <label>
                                        <input type="radio" name="holidayPayment" value="always">
                                        Always Pay
                                    </label>
                                    <label>
                                        <input type="radio" name="holidayPayment" value="both">
                                        Pay if Worked or Not Worked
                                    </label>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="holidayTimeCategory">Holiday Time Category</label>
                                <select id="holidayTimeCategory" name="holidayTimeCategory" required>
                                    <!-- Time categories will be loaded here -->
                                </select>
                            </div>
                        </form>
                    </div>

                    <!-- Night Allowance Tab -->
                    <div class="shift-tab-content" id="shift-night-allowance-tab">
                        <form id="shiftNightAllowanceForm">
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="nightAllowanceStart">Night Allowance Start Time</label>
                                    <input type="time" id="nightAllowanceStart" name="nightAllowanceStart" required data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="nightAllowanceEnd">Night Allowance End Time</label>
                                    <input type="time" id="nightAllowanceEnd" name="nightAllowanceEnd" required data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="nightAllowanceRate">Night Allowance Rate</label>
                                <input type="number" id="nightAllowanceRate" name="nightAllowanceRate" step="0.01" required>
                            </div>
                        </form>
                    </div>

                    <!-- Split Time Tab -->
                    <div class="shift-tab-content" id="shift-split-time-tab">
                        <form id="shiftSplitTimeForm">
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="splitTimeStart">Split Time Start</label>
                                    <input type="time" id="splitTimeStart" name="splitTimeStart" required data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <div class="time-input-container">
                                    <label for="splitTimeEnd">Split Time End</label>
                                    <input type="time" id="splitTimeEnd" name="splitTimeEnd" required data-format="true">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="splitTimeRate">Split Time Rate</label>
                                <input type="number" id="splitTimeRate" name="splitTimeRate" step="0.01" required>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="cancel-btn">Cancel</button>
                <button type="button" class="prev-tab-btn" style="display: none;">Previous</button>
                <button type="button" class="next-tab-btn">Next</button>
                <button type="submit" class="submit-btn" style="display: none;">Save Shift</button>
            </div>
        </div>
    </div>

    <!-- Assign Template Modal -->
    <div class="modal" id="assignTemplateModal">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Assign Template to Employees</h3>
                <button class="close-btn">&times;</button>
            </div>
            <div class="modal-body">
                <form id="assignTemplateForm">
                    <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                    <div class="form-group">
                        <label for="templateSelect">Select Template</label>
                        <select id="templateSelect" required>
                            <!-- Templates will be loaded here -->
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Select Employees</label>
                        <div class="employee-list" id="employeeList">
                            <!-- Employees will be loaded here -->
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="button" class="cancel-btn">Cancel</button>
                        <button type="submit" class="submit-btn">Assign Template</button>
                    </div>
                </form>
            </div>
        </div>
    </div>