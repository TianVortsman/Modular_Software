class ScheduleManager {
    constructor() {
        this.currentTemplate = null;
        this.currentDate = new Date();
        this.templates = [];
        this.shifts = [];
        this.draggedShift = null;
        this.employees = [];
        this.rosters = [];
        this.currentRoster = null;
        this.init();
        this.initDoubleClick();
    }

    async init() {
        try {
            await this.loadInitialData();
            this.initTabs();
            this.initTemplateDropdown();
            this.initDragAndDrop();
            this.initModals();
            this.initActions();
            this.updateMonthlyView();
        } catch (error) {
            this.showError('Failed to initialize schedule manager: ' + error.message);
        }
    }

    async loadInitialData() {
        try {
            await Promise.all([
                this.loadTemplates(),
                this.loadShifts(),
                this.loadEmployees()
            ]);
        } catch (error) {
            this.showError('Failed to load initial data: ' + error.message);
        }
    }

    async loadTemplates() {
        try {
            const response = await fetch('api/templates.php');
            if (!response.ok) throw new Error('Failed to load templates');
            this.templates = await response.json();
            this.renderTemplates();
        } catch (error) {
            this.showError('Failed to load templates: ' + error.message);
        }
    }

    async loadShifts() {
        try {
            const response = await fetch('api/shifts.php');
            if (!response.ok) throw new Error('Failed to load shifts');
            this.shifts = await response.json();
            this.renderShiftLibrary();
        } catch (error) {
            this.showError('Failed to load shifts: ' + error.message);
        }
    }

    async loadEmployees() {
        try {
            const response = await fetch('../api/employees.php');
            if (!response.ok) throw new Error('Failed to load employees');
            this.employees = await response.json();
            this.renderEmployeeList();
        } catch (error) {
            this.showError('Failed to load employees: ' + error.message);
        }
    }

    showError(message) {
        // Create and show error notification
        const notification = document.createElement('div');
        notification.className = 'error-notification';
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 5000);
    }

    showSuccess(message) {
        // Create and show success notification
        const notification = document.createElement('div');
        notification.className = 'success-notification';
        notification.textContent = message;
        document.body.appendChild(notification);

        setTimeout(() => {
            notification.remove();
        }, 3000);
    }

    initTabs() {
        const tabButtons = document.querySelectorAll('.tab-btn');
        const tabs = document.querySelectorAll('.schedule-tab');

        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const tabId = btn.dataset.tab;
                
                // Update active states
                tabButtons.forEach(b => b.classList.remove('active'));
                tabs.forEach(t => t.classList.remove('active'));
                
                btn.classList.add('active');
                document.getElementById(`${tabId}-tab`).classList.add('active');

                // Refresh the drag and drop functionality when switching tabs
                this.initDragAndDrop();
            });
        });
    }

    initTemplateDropdown() {
        const templateSelect = document.getElementById('templateSelect');
        if (!templateSelect) return;

        templateSelect.addEventListener('change', (e) => {
            const templateId = e.target.value;
            if (templateId) {
                this.loadTemplate(parseInt(templateId));
            } else {
                this.clearTemplateEditor();
            }
        });

        // Add template button click handler
        const addTemplateBtn = document.querySelector('#addTemplateBtn');
        if (addTemplateBtn) {
            addTemplateBtn.addEventListener('click', () => this.createNewTemplate());
        }
    }

    initDragAndDrop() {
        // Initialize drag events for all shift items
        const shiftItems = document.querySelectorAll('.shift-item');
        shiftItems.forEach(item => {
            item.addEventListener('dragstart', (e) => {
                this.draggedShift = item;
                item.classList.add('dragging');
                e.dataTransfer.setData('text/plain', item.dataset.shiftId);
            });

            item.addEventListener('dragend', () => {
                if (this.draggedShift) {
                    this.draggedShift.classList.remove('dragging');
                    this.draggedShift = null;
                }
            });
        });

        // Initialize drop zones for work week
        const workWeekDropZones = document.querySelectorAll('.work-week .shift-drop-zone');
        workWeekDropZones.forEach(zone => {
            this.initializeDropZone(zone, 'workweek');
        });

        // Initialize drop zones for monthly calendar
        const calendarDropZones = document.querySelectorAll('.calendar-day');
        calendarDropZones.forEach(zone => {
            this.initializeDropZone(zone, 'calendar');
        });
    }

    initializeDropZone(zone, type) {
        // First, remove all existing event listeners
        const newZone = zone.cloneNode(true);
        zone.parentNode.replaceChild(newZone, zone);
        zone = newZone;

        // Add dragover event
        zone.addEventListener('dragover', (e) => {
            e.preventDefault();
            e.stopPropagation();
            zone.classList.add('drag-over');
        });

        // Add dragleave event
        zone.addEventListener('dragleave', (e) => {
            e.preventDefault();
            e.stopPropagation();
            zone.classList.remove('drag-over');
        });

        // Add drop event with single handler
        zone.addEventListener('drop', (e) => {
            e.preventDefault();
            e.stopPropagation();
            zone.classList.remove('drag-over');

            const shiftId = e.dataTransfer.getData('text/plain');
            if (!shiftId || !this.draggedShift) return;

            // Check for duplicates
            const existingShift = zone.querySelector(`[data-shift-id="${shiftId}"]`);
            if (existingShift) return;

            // Create new shift element with compact display
            const newShift = this.createShiftElement({
                id: shiftId,
                name: this.draggedShift.querySelector('.shift-name')?.textContent || 'Shift',
                time: this.draggedShift.querySelector('.shift-time')?.textContent || '',
                color: this.draggedShift.querySelector('.shift-color')?.style.background || ''
            }, 'compact');

            // Add the new shift to the drop zone
            if (type === 'workweek') {
                zone.appendChild(newShift);
                this.initializeDraggableShift(newShift);
            } else if (type === 'calendar') {
                const shiftContainer = zone.querySelector('.shift-container');
                if (shiftContainer) {
                    // Remove empty message if it exists
                    const emptyMessage = shiftContainer.querySelector('.empty-day-message');
                    if (emptyMessage) {
                        emptyMessage.remove();
                    }

                    shiftContainer.appendChild(newShift);
                    this.initializeDraggableShift(newShift);
                }
            }
        });
    }

    renderTemplates() {
        const templateSelect = document.getElementById('templateSelect');
        if (!templateSelect) return;

        // Clear existing options except the first one
        while (templateSelect.options.length > 1) {
            templateSelect.remove(1);
        }

        // Add template options
        this.templates.forEach(template => {
            const option = document.createElement('option');
            option.value = template.id;
            option.textContent = template.name;
            templateSelect.appendChild(option);
        });
    }

    loadTemplate(templateId) {
        const template = this.templates.find(t => t.id === parseInt(templateId));
        if (!template) return;

        this.currentTemplate = template;
        this.updateTemplateEditor(template);
    }

    clearTemplateEditor() {
        this.currentTemplate = null;
        const nameInput = document.querySelector('.template-name-input');
        if (nameInput) {
            nameInput.value = '';
        }

        // Clear all drop zones
        document.querySelectorAll('.shift-drop-zone').forEach(zone => {
            zone.innerHTML = '';
        });
    }

    updateTemplateEditor(template) {
        // Update template name input
        const nameInput = document.querySelector('.template-name-input');
        if (nameInput) {
            nameInput.value = template.name;
        }

        // Update shifts in each day
        Object.entries(template.shifts).forEach(([day, shifts]) => {
            const dropZone = document.querySelector(`.shift-drop-zone[data-day="${day}"]`);
            if (!dropZone) return;

            dropZone.innerHTML = shifts.map(shift => this.createShiftElement(shift)).join('');
        });
    }

    createShiftElement(shiftData, type = 'library') {
        const shiftElement = document.createElement('div');
        shiftElement.className = 'shift-item';
        shiftElement.draggable = true;
        shiftElement.dataset.shiftId = shiftData.id;

        // Different HTML structure based on where the shift is being displayed
        if (type === 'library') {
            shiftElement.innerHTML = `
                <div class="shift-content">
                    <div class="shift-color" style="background: ${shiftData.color}"></div>
                    <div class="shift-details">
                        <div class="shift-name">${shiftData.name}</div>
                        <div class="shift-time">${shiftData.time}</div>
                        <div class="shift-rules">${shiftData.rules || ''}</div>
                    </div>
                </div>
            `;
        } else {
            // Compact version for work week and calendar
            shiftElement.innerHTML = `
                <div class="shift-content">
                    <div class="shift-color" style="background: ${shiftData.color}"></div>
                    <div class="shift-details">
                        <div class="shift-name">${shiftData.name}</div>
                        <div class="shift-time">${shiftData.time}</div>
                    </div>
                </div>
            `;
        }

        // Add remove button
        const removeBtn = document.createElement('button');
        removeBtn.className = 'remove-shift';
        removeBtn.innerHTML = '<i class="material-icons">close</i>';
        removeBtn.onclick = (e) => {
            e.stopPropagation();
            shiftElement.remove();
        };
        shiftElement.appendChild(removeBtn);

        return shiftElement;
    }

    createNewTemplate() {
        const newTemplate = {
            id: this.templates.length + 1,
            name: 'New Template',
            shifts: {
                monday: [],
                tuesday: [],
                wednesday: [],
                thursday: [],
                friday: [],
                saturday: [],
                sunday: []
            }
        };

        this.templates.push(newTemplate);
        this.renderTemplates();
        
        // Select the new template
        const templateSelect = document.getElementById('templateSelect');
        if (templateSelect) {
            templateSelect.value = newTemplate.id;
            this.loadTemplate(newTemplate.id);
        }
    }

    renderShiftLibrary() {
        const shiftList = document.querySelector('.shift-list');
        if (!shiftList) return;

        shiftList.innerHTML = this.shifts.map(shift => {
            const shiftElement = this.createShiftElement({
                id: shift.id,
                name: shift.name,
                time: `${shift.startTime} - ${shift.endTime}`,
                color: shift.color,
                rules: shift.rules || `${shift.breakDuration || '30'} min break @ ${shift.breakTime || 'midshift'}`
            }, 'library');
            return shiftElement.outerHTML;
        }).join('');

        // Reinitialize drag and drop
        this.initDragAndDrop();
    }

    findShift(shiftId) {
        return this.shifts.find(s => s.id === parseInt(shiftId));
    }

    removeShiftFromDay(shiftId) {
        if (!this.currentTemplate) return;

        Object.values(this.currentTemplate.shifts).forEach(shifts => {
            const index = shifts.findIndex(s => s.id === parseInt(shiftId));
            if (index !== -1) {
                shifts.splice(index, 1);
            }
        });
    }

    async handleAddShift(e) {
        e.preventDefault();
        try {
            const form = e.target;
            const formData = new FormData(form);
            
            // Add additional shift data
            const shift = {
                name: formData.get('shiftName'),
                startTime: formData.get('startTime'),
                endTime: formData.get('endTime'),
                breakTime: parseInt(formData.get('breakTime')),
                color: formData.get('shiftColor'),
                payPeriod: formData.get('payPeriod'),
                periodStartDate: formData.get('periodStartDate'),
                periodEndDate: formData.get('periodEndDate'),
                periodDays: parseInt(formData.get('periodDays')),
                shiftType: formData.get('shiftType'),
                shiftPattern: formData.get('shiftPattern'),
                breakType: formData.get('breakType'),
                breakDuration: parseInt(formData.get('breakDuration')),
                requiresApproval: formData.get('requiresApproval') === 'on',
                allowOvertime: formData.get('allowOvertime') === 'on',
                allowEarlyClockIn: formData.get('allowEarlyClockIn') === 'on',
                allowLateClockOut: formData.get('allowLateClockOut') === 'on',
                earlyClockInLimit: parseInt(formData.get('earlyClockInLimit')),
                lateClockOutLimit: parseInt(formData.get('lateClockOutLimit')),
                shiftRules: formData.get('shiftRules')
            };

            const response = await fetch('api/shifts.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(shift)
            });

            if (!response.ok) throw new Error('Failed to add shift');

            this.showSuccess('Shift added successfully');
            await this.loadShifts();
            this.closeModal(form.closest('.modal'));
            form.reset();
        } catch (error) {
            this.showError('Failed to add shift: ' + error.message);
        }
    }

    async handleAssignTemplate(e) {
        e.preventDefault();
        try {
            const form = e.target;
            const formData = new FormData(form);
            const employeeIds = Array.from(formData.getAll('employeeIds'));
            const templateId = formData.get('templateSelect');

            const response = await fetch('api/assign-template.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    templateId,
                    employeeIds
                })
            });

            if (!response.ok) throw new Error('Failed to assign template');

            this.showSuccess('Template assigned successfully');
            this.closeModal(form.closest('.modal'));
            form.reset();
        } catch (error) {
            this.showError('Failed to assign template: ' + error.message);
        }
    }

    async saveTemplate() {
        if (!this.currentTemplate) return;

        try {
            const nameInput = document.querySelector('.template-name-input');
            if (nameInput) {
                this.currentTemplate.name = nameInput.value;
            }

            const response = await fetch('api/templates.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(this.currentTemplate)
            });

            if (!response.ok) throw new Error('Failed to save template');
            
            this.showSuccess('Template saved successfully');
            await this.loadTemplates();
        } catch (error) {
            this.showError('Failed to save template: ' + error.message);
        }
    }

    // Monthly Roster Methods
    updateMonthlyView() {
        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 
                          'July', 'August', 'September', 'October', 'November', 'December'];
        
        const currentMonthElement = document.getElementById('currentMonth');
        if (currentMonthElement) {
            currentMonthElement.textContent = 
                `${monthNames[this.currentDate.getMonth()]} ${this.currentDate.getFullYear()}`;
        }

        this.renderCalendar();
    }

    navigateMonth(delta) {
        this.currentDate.setMonth(this.currentDate.getMonth() + delta);
        this.updateMonthlyView();
    }

    renderCalendar() {
        const firstDay = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth(), 1);
        const lastDay = new Date(this.currentDate.getFullYear(), this.currentDate.getMonth() + 1, 0);
        const calendarGrid = document.getElementById('calendarGrid');
        if (!calendarGrid) return;

        // Clear existing content
        calendarGrid.innerHTML = '';

        // Add empty cells for days before the first of the month
        for (let i = 0; i < firstDay.getDay(); i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day empty';
            calendarGrid.appendChild(emptyDay);
        }

        // Add cells for each day of the month
        for (let i = 1; i <= lastDay.getDate(); i++) {
            const dayCell = this.createDayCell(i, this.currentDate.getFullYear(), this.currentDate.getMonth());
            calendarGrid.appendChild(dayCell);
        }

        // Add empty cells for days after the last day of the month
        const lastDayOfWeek = lastDay.getDay();
        for (let i = lastDayOfWeek + 1; i < 7; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day empty';
            calendarGrid.appendChild(emptyDay);
        }

        // Reinitialize drag and drop for the new calendar days
        this.initDragAndDrop();
    }

    createDayCell(day, year, month) {
        const currentDate = new Date(year, month, day);
        const dateString = currentDate.toISOString().split('T')[0];
        const isWeekend = currentDate.getDay() === 0 || currentDate.getDay() === 6;
        const isToday = currentDate.toDateString() === new Date().toDateString();

        const dayCell = document.createElement('div');
        dayCell.className = `calendar-day${isWeekend ? ' weekend' : ''}${isToday ? ' today' : ''}`;
        
        // Add day number
        const dayNumber = document.createElement('div');
        dayNumber.className = 'day-number';
        dayNumber.textContent = day;
        dayCell.appendChild(dayNumber);

        // Add shift container
        const shiftContainer = document.createElement('div');
        shiftContainer.className = 'shift-container';
        
        // Add shifts if they exist for this date
        if (this.currentRoster && this.currentRoster.shifts[dateString]) {
            this.currentRoster.shifts[dateString].forEach(shift => {
                const shiftElement = this.createRosterShiftElement(shift);
                shiftContainer.appendChild(shiftElement);
            });
        } else {
            const emptyMessage = document.createElement('div');
            emptyMessage.className = 'empty-day-message';
            emptyMessage.textContent = 'No shifts';
            shiftContainer.appendChild(emptyMessage);
        }

        dayCell.appendChild(shiftContainer);

        // Make the cell a drop target for shifts
        this.makeDropTarget(dayCell, dateString);

        return dayCell;
    }

    createRosterShiftElement(shift) {
        const shiftElement = document.createElement('div');
        shiftElement.className = 'roster-shift';
        shiftElement.draggable = true;
        shiftElement.dataset.shiftId = shift.id;

        shiftElement.innerHTML = `
            <div class="shift-info">
                <div class="shift-time">${shift.startTime} - ${shift.endTime}</div>
                <div class="shift-employees">${shift.employees ? shift.employees.join(', ') : 'No employees assigned'}</div>
            </div>
        `;

        // Add drag event listeners
        shiftElement.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', shift.id);
            shiftElement.classList.add('dragging');
        });

        shiftElement.addEventListener('dragend', () => {
            shiftElement.classList.remove('dragging');
        });

        return shiftElement;
    }

    makeDropTarget(element, date) {
        element.addEventListener('dragover', (e) => {
            e.preventDefault();
            element.classList.add('drag-over');
        });

        element.addEventListener('dragleave', () => {
            element.classList.remove('drag-over');
        });

        element.addEventListener('drop', async (e) => {
            e.preventDefault();
            element.classList.remove('drag-over');

            const shiftId = e.dataTransfer.getData('text/plain');
            await this.assignShiftToDate(shiftId, date);
        });
    }

    async assignShiftToDate(shiftId, date) {
        if (!this.currentRoster) return;

        try {
            const response = await fetch('api/roster-assignments.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    rosterId: this.currentRoster.id,
                    shiftId,
                    date
                })
            });

            if (!response.ok) throw new Error('Failed to assign shift');

            // Update local data
            if (!this.currentRoster.shifts[date]) {
                this.currentRoster.shifts[date] = [];
            }
            
            const shift = this.findShift(shiftId);
            if (shift) {
                this.currentRoster.shifts[date].push(shift);
                this.updateCalendar();
                this.showSuccess('Shift assigned successfully');
            }
        } catch (error) {
            this.showError('Failed to assign shift: ' + error.message);
        }
    }

    // Add method to handle shift removal
    removeShift(shiftElement) {
        if (shiftElement && shiftElement.parentNode) {
            shiftElement.remove();
        }
    }

    initializeDraggableShift(shiftElement) {
        shiftElement.addEventListener('dragstart', (e) => {
            this.draggedShift = shiftElement;
            shiftElement.classList.add('dragging');
            e.dataTransfer.setData('text/plain', shiftElement.dataset.shiftId);
        });

        shiftElement.addEventListener('dragend', () => {
            if (this.draggedShift) {
                this.draggedShift.classList.remove('dragging');
                this.draggedShift = null;
            }
        });
    }

    openModal(modalId) {
        const modal = document.getElementById(modalId);
        if (modal) {
            modal.classList.add('active');
        }
    }

    closeModal(modal) {
        if (modal) {
            modal.classList.remove('active');
            if (modal.id === 'addShiftModal') {
                this.resetShiftModal();
            }
        }
    }

    renderEmployeeList() {
        const employeeList = document.getElementById('employeeList');
        if (!employeeList) return;

        employeeList.innerHTML = this.employees.map(employee => `
            <div class="employee-item">
                <input type="checkbox" name="employeeIds" value="${employee.id}" id="employee-${employee.id}">
                <label for="employee-${employee.id}">${employee.name}</label>
            </div>
        `).join('');
    }

    initDoubleClick() {
        document.addEventListener('dblclick', (e) => {
            const shiftItem = e.target.closest('.shift-item');
            if (shiftItem) {
                const shiftId = shiftItem.dataset.shiftId;
                const shift = this.findShift(shiftId);
                if (shift) {
                    this.openShiftDetails(shift);
                }
            }
        });
    }

    openShiftDetails(shift) {
        const modal = document.getElementById('addShiftModal');
        const modalTitle = modal.querySelector('.modal-header h3');
        modalTitle.textContent = 'Edit Shift';

        // Pre-fill Shift Details tab
        const shiftDetailsForm = document.getElementById('shiftDetailsForm');
        shiftDetailsForm.querySelector('#shiftName').value = shift.name;
        shiftDetailsForm.querySelector('#shiftTarget').value = shift.target_hours || '';
        shiftDetailsForm.querySelector('#normalTimeCategory').value = shift.normal_time_category || '';
        shiftDetailsForm.querySelector('#overtimeCategory').value = shift.overtime_category || '';
        shiftDetailsForm.querySelector('#shiftCounter').value = shift.shift_counter || '';
        shiftDetailsForm.querySelector(`input[name="punchHandling"][value="${shift.punch_handling || 'first_last'}"]`).checked = true;
        shiftDetailsForm.querySelector('#singleClosingShift').checked = shift.single_closing_shift || false;
        shiftDetailsForm.querySelector('#paidHolidays').checked = shift.paid_holidays || false;
        shiftDetailsForm.querySelector('#nightShiftAllowance').checked = shift.night_shift_allowance || false;
        shiftDetailsForm.querySelector('#splitNormalTime').checked = shift.split_normal_time || false;

        // Pre-fill Shift Times tab
        const shiftTimesForm = document.getElementById('shiftTimesForm');
        shiftTimesForm.querySelector('#startTime').value = shift.start_time || '';
        shiftTimesForm.querySelector('#endTime').value = shift.end_time || '';
        shiftTimesForm.querySelector('#earliestStart').value = shift.earliest_start || '';
        shiftTimesForm.querySelector('#latestEnd').value = shift.latest_end || '';
        shiftTimesForm.querySelector('#roundingProfile').value = shift.rounding_profile || '';

        // Pre-fill Holidays tab if enabled
        if (shift.paid_holidays) {
            const shiftHolidaysForm = document.getElementById('shiftHolidaysForm');
            shiftHolidaysForm.querySelector(`input[name="holidayPayment"][value="${shift.holiday_payment || 'work_only'}"]`).checked = true;
            shiftHolidaysForm.querySelector('#holidayTimeCategory').value = shift.holiday_time_category || '';
        }

        // Pre-fill Night Allowance tab if enabled
        if (shift.night_shift_allowance) {
            const shiftNightAllowanceForm = document.getElementById('shiftNightAllowanceForm');
            shiftNightAllowanceForm.querySelector('#nightAllowanceStart').value = shift.night_allowance_start || '';
            shiftNightAllowanceForm.querySelector('#nightAllowanceEnd').value = shift.night_allowance_end || '';
            shiftNightAllowanceForm.querySelector('#nightAllowanceRate').value = shift.night_allowance_rate || '';
        }

        // Pre-fill Split Time tab if enabled
        if (shift.split_normal_time) {
            const shiftSplitTimeForm = document.getElementById('shiftSplitTimeForm');
            shiftSplitTimeForm.querySelector('#splitTimeStart').value = shift.split_time_start || '';
            shiftSplitTimeForm.querySelector('#splitTimeEnd').value = shift.split_time_end || '';
            shiftSplitTimeForm.querySelector('#splitTimeRate').value = shift.split_time_rate || '';
        }

        // Update tab visibility based on checkbox states
        this.updateTabVisibility();

        // Add shift ID to the modal for reference
        modal.dataset.shiftId = shift.id;

        // Update submit button text
        const submitBtn = modal.querySelector('.submit-btn');
        submitBtn.textContent = 'Update Shift';

        // Show the modal
        modal.classList.add('active');

        // Update the submit handler
        submitBtn.onclick = async () => {
            if (this.validateCurrentTab()) {
                try {
                    const formData = new FormData();
                    
                    // Add shift ID to form data
                    formData.append('shiftId', shift.id);
                    
                    // Add data from all forms
                    document.querySelectorAll('.shift-tab-content form').forEach(form => {
                        const formDataObj = new FormData(form);
                        for (let [key, value] of formDataObj.entries()) {
                            formData.append(key, value);
                        }
                    });

                    const response = await fetch('../api/shifts.php', {
                        method: 'PUT',
                        body: formData
                    });

                    const data = await response.json();
                    if (data.success) {
                        this.showSuccess('Shift updated successfully');
                        modal.classList.remove('active');
                        await this.loadShifts(); // Refresh the shift list
                    } else {
                        this.showError('Error updating shift: ' + data.message);
                    }
                } catch (error) {
                    this.showError('An error occurred while updating the shift');
                    console.error('Error:', error);
                }
            }
        };
    }

    // Add a method to reset the modal to its original state
    resetShiftModal() {
        const modal = document.getElementById('addShiftModal');
        const modalTitle = modal.querySelector('.modal-header h3');
        modalTitle.textContent = 'Add New Shift';

        // Reset all forms
        modal.querySelectorAll('form').forEach(form => form.reset());

        // Reset tab visibility
        this.updateTabVisibility();

        // Remove shift ID from modal
        delete modal.dataset.shiftId;

        // Reset submit button text
        const submitBtn = modal.querySelector('.submit-btn');
        submitBtn.textContent = 'Save Shift';

        // Reset submit handler to original state
        submitBtn.onclick = null;
    }

    initModals() {
        // Initialize modal close buttons
        document.querySelectorAll('.close-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = btn.closest('.modal');
                this.closeModal(modal);
            });
        });

        // Initialize cancel buttons
        document.querySelectorAll('.cancel-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                const modal = btn.closest('.modal');
                this.closeModal(modal);
            });
        });

        // Initialize Setup modal
        const setupBtn = document.getElementById('setupBtn');
        const setupModal = document.getElementById('setupModal');
        if (setupBtn && setupModal) {
            setupBtn.addEventListener('click', () => {
                this.openModal('setupModal');
                this.initSetupTabs();
                this.loadSetupData();
            });
        }

        // Initialize Add Shift modal
        const addShiftBtn = document.getElementById('addShiftBtn');
        const addShiftModal = document.getElementById('addShiftModal');
        if (addShiftBtn && addShiftModal) {
            addShiftBtn.addEventListener('click', () => {
                this.openModal('addShiftModal');
                this.initializeShiftForm();
            });
        }

        // Initialize Add Shift form
        const addShiftForm = document.getElementById('addShiftForm');
        if (addShiftForm) {
            addShiftForm.addEventListener('submit', (e) => this.handleAddShift(e));
        }

        // Initialize Assign Template modal
        const assignTemplateBtn = document.getElementById('assignTemplateBtn');
        const assignTemplateModal = document.getElementById('assignTemplateModal');
        if (assignTemplateBtn && assignTemplateModal) {
            assignTemplateBtn.addEventListener('click', () => {
                this.openModal('assignTemplateModal');
            });
        }

        // Initialize Assign Template form
        const assignTemplateForm = document.getElementById('assignTemplateForm');
        if (assignTemplateForm) {
            assignTemplateForm.addEventListener('submit', (e) => this.handleAssignTemplate(e));
        }

        // Initialize Save Template button
        const saveTemplateBtn = document.getElementById('saveTemplateBtn');
        if (saveTemplateBtn) {
            saveTemplateBtn.addEventListener('click', () => this.saveTemplate());
        }

        // Initialize month navigation
        const prevMonthBtn = document.getElementById('prevMonth');
        const nextMonthBtn = document.getElementById('nextMonth');
        if (prevMonthBtn) {
            prevMonthBtn.addEventListener('click', () => this.navigateMonth(-1));
        }
        if (nextMonthBtn) {
            nextMonthBtn.addEventListener('click', () => this.navigateMonth(1));
        }

        // Initialize roster action buttons
        const saveRosterBtn = document.getElementById('saveRosterBtn');
        const exportRosterBtn = document.getElementById('exportRosterBtn');
        const printRosterBtn = document.getElementById('printRosterBtn');

        if (saveRosterBtn) {
            saveRosterBtn.addEventListener('click', () => this.saveRoster());
        }
        if (exportRosterBtn) {
            exportRosterBtn.addEventListener('click', () => this.exportRoster());
        }
        if (printRosterBtn) {
            printRosterBtn.addEventListener('click', () => this.printRoster());
        }
    }

    initializeShiftForm() {
        // Initialize pay period handling
        const payPeriodSelect = document.getElementById('payPeriod');
        const customPeriodDetails = document.querySelector('.custom-period-details');
        const periodStartDate = document.getElementById('periodStartDate');
        const periodEndDate = document.getElementById('periodEndDate');
        const periodDays = document.getElementById('periodDays');

        if (payPeriodSelect && customPeriodDetails) {
            payPeriodSelect.addEventListener('change', () => {
                customPeriodDetails.style.display = 
                    payPeriodSelect.value === 'custom' ? 'block' : 'none';
                
                // Set default values for different pay periods
                switch(payPeriodSelect.value) {
                    case 'weekly':
                        periodDays.value = 7;
                        break;
                    case 'biweekly':
                        periodDays.value = 14;
                        break;
                    case 'monthly':
                        periodDays.value = 30;
                        break;
                    case 'custom':
                        periodDays.value = '';
                        break;
                }
            });
        }

        // Initialize shift type handling
        const shiftTypeSelect = document.getElementById('shiftType');
        const shiftPatternInput = document.getElementById('shiftPattern');

        if (shiftTypeSelect && shiftPatternInput) {
            shiftTypeSelect.addEventListener('change', () => {
                shiftPatternInput.style.display = 
                    shiftTypeSelect.value === 'rotating' ? 'block' : 'none';
            });
        }

        // Initialize break type handling
        const breakTypeSelect = document.getElementById('breakType');
        const breakDurationInput = document.getElementById('breakDuration');

        if (breakTypeSelect && breakDurationInput) {
            breakTypeSelect.addEventListener('change', () => {
                breakDurationInput.style.display = 
                    breakTypeSelect.value === 'none' ? 'none' : 'block';
            });
        }

        // Initialize early/late clock in/out handling
        const earlyClockInCheckbox = document.getElementById('allowEarlyClockIn');
        const lateClockOutCheckbox = document.getElementById('allowLateClockOut');
        const earlyClockInLimit = document.getElementById('earlyClockInLimit');
        const lateClockOutLimit = document.getElementById('lateClockOutLimit');

        if (earlyClockInCheckbox && earlyClockInLimit) {
            earlyClockInCheckbox.addEventListener('change', () => {
                earlyClockInLimit.style.display = 
                    earlyClockInCheckbox.checked ? 'block' : 'none';
            });
        }

        if (lateClockOutCheckbox && lateClockOutLimit) {
            lateClockOutCheckbox.addEventListener('change', () => {
                lateClockOutLimit.style.display = 
                    lateClockOutCheckbox.checked ? 'block' : 'none';
            });
        }

        // Set default values
        if (periodStartDate) {
            periodStartDate.value = new Date().toISOString().split('T')[0];
        }
        if (periodEndDate) {
            const endDate = new Date();
            endDate.setDate(endDate.getDate() + 14); // Default to 2 weeks
            periodEndDate.value = endDate.toISOString().split('T')[0];
        }
    }

    initActions() {
        // Initialize mobile shift library toggle
        const shiftLibraryToggle = document.getElementById('shiftLibraryToggle');
        const shiftLibrary = document.querySelector('.shift-library');
        
        if (shiftLibraryToggle && shiftLibrary) {
            shiftLibraryToggle.addEventListener('click', () => {
                shiftLibrary.classList.toggle('active');
            });
        }
    }

    updateTabVisibility() {
        // Implementation of updateTabVisibility method
    }

    validateCurrentTab() {
        // Implementation of validateCurrentTab method
        return true; // Placeholder return, actual implementation needed
    }

    initSetupTabs() {
        const tabButtons = document.querySelectorAll('.setup-tab-btn');
        const tabContents = document.querySelectorAll('.setup-tab-content');
        const categoryButtons = document.querySelectorAll('.category-tab-btn');
        const categoryContents = document.querySelectorAll('.category-content');

        // Main tab switching
        tabButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const tabId = btn.dataset.tab;
                
                // Update active states for main tabs
                tabButtons.forEach(b => b.classList.remove('active'));
                tabContents.forEach(t => t.classList.remove('active'));
                
                btn.classList.add('active');
                document.getElementById(`${tabId}-tab`).classList.add('active');

                // Reset category tabs when switching main tabs
                if (categoryButtons.length > 0) {
                    categoryButtons[0].click(); // Activate first category tab
                }
            });
        });

        // Category tab switching (within Time Categories tab)
        categoryButtons.forEach(btn => {
            btn.addEventListener('click', () => {
                const categoryId = btn.dataset.category;
                
                // Update active states for category tabs
                categoryButtons.forEach(b => b.classList.remove('active'));
                categoryContents.forEach(c => c.classList.remove('active'));
                
                btn.classList.add('active');
                document.getElementById(`${categoryId}-categories`).classList.add('active');
            });
        });

        // Initialize custom period details visibility
        const periodTypeSelect = document.getElementById('periodType');
        const customPeriodDetails = document.getElementById('customPeriodDetails');
        
        if (periodTypeSelect && customPeriodDetails) {
            periodTypeSelect.addEventListener('change', () => {
                customPeriodDetails.style.display = 
                    periodTypeSelect.value === 'custom' ? 'grid' : 'none';
            });
        }

        // Initialize form submissions
        this.initializeSetupForms();
    }

    initializeSetupForms() {
        // Holidays form
        const holidaysForm = document.getElementById('holidaysForm');
        if (holidaysForm) {
            holidaysForm.addEventListener('submit', (e) => this.handleHolidaySubmit(e));
        }

        // Time Categories forms
        const normalCategoriesForm = document.getElementById('normalCategoriesForm');
        const overtimeCategoriesForm = document.getElementById('overtimeCategoriesForm');
        const specialCategoriesForm = document.getElementById('specialCategoriesForm');

        if (normalCategoriesForm) {
            normalCategoriesForm.addEventListener('submit', (e) => this.handleTimeCategorySubmit(e));
        }
        if (overtimeCategoriesForm) {
            overtimeCategoriesForm.addEventListener('submit', (e) => this.handleTimeCategorySubmit(e));
        }
        if (specialCategoriesForm) {
            specialCategoriesForm.addEventListener('submit', (e) => this.handleTimeCategorySubmit(e));
        }

        // Rounding Profiles form
        const roundingProfilesForm = document.getElementById('roundingProfilesForm');
        if (roundingProfilesForm) {
            roundingProfilesForm.addEventListener('submit', (e) => this.handleRoundingProfileSubmit(e));
        }

        // Overtime Profiles form
        const overtimeProfilesForm = document.getElementById('overtimeProfilesForm');
        if (overtimeProfilesForm) {
            overtimeProfilesForm.addEventListener('submit', (e) => this.handleOvertimeProfileSubmit(e));
        }

        // Pay Periods form
        const payPeriodsForm = document.getElementById('payPeriodsForm');
        if (payPeriodsForm) {
            payPeriodsForm.addEventListener('submit', (e) => this.handlePayPeriodSubmit(e));
        }
    }

    async loadRosters() {
        try {
            const response = await fetch('api/rosters.php');
            if (!response.ok) throw new Error('Failed to load rosters');
            this.rosters = await response.json();
            this.renderRosterDropdown();
        } catch (error) {
            this.showError('Failed to load rosters: ' + error.message);
        }
    }

    renderRosterDropdown() {
        const rosterSelect = document.getElementById('rosterSelect');
        if (!rosterSelect) return;

        // Clear existing options except the first one
        while (rosterSelect.options.length > 1) {
            rosterSelect.remove(1);
        }

        // Add roster options
        this.rosters.forEach(roster => {
            const option = document.createElement('option');
            option.value = roster.id;
            option.textContent = roster.name;
            rosterSelect.appendChild(option);
        });
    }

    async createNewRoster() {
        const newRoster = {
            id: this.rosters.length + 1,
            name: 'New Roster',
            shifts: {}
        };

        try {
            const response = await fetch('api/rosters.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(newRoster)
            });

            if (!response.ok) throw new Error('Failed to create roster');

            this.rosters.push(newRoster);
            this.renderRosterDropdown();
            
            // Select the new roster
            const rosterSelect = document.getElementById('rosterSelect');
            if (rosterSelect) {
                rosterSelect.value = newRoster.id;
                this.loadRoster(newRoster.id);
            }

            this.showSuccess('New roster created successfully');
        } catch (error) {
            this.showError('Failed to create roster: ' + error.message);
        }
    }

    async loadRoster(rosterId) {
        const roster = this.rosters.find(r => r.id === parseInt(rosterId));
        if (!roster) return;

        this.currentRoster = roster;
        this.updateCalendar();
    }

    updateCalendar() {
        const calendarGrid = document.getElementById('calendarGrid');
        if (!calendarGrid) return;

        const year = this.currentDate.getFullYear();
        const month = this.currentDate.getMonth();
        const firstDay = new Date(year, month, 1);
        const lastDay = new Date(year, month + 1, 0);

        calendarGrid.innerHTML = '';

        // Add empty cells for days before the first of the month
        for (let i = 0; i < firstDay.getDay(); i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day empty';
            calendarGrid.appendChild(emptyDay);
        }

        // Add cells for each day of the month
        for (let i = 1; i <= lastDay.getDate(); i++) {
            const dayCell = this.createDayCell(i, year, month);
            calendarGrid.appendChild(dayCell);
        }

        // Add empty cells for days after the last day of the month
        const remainingDays = 7 - lastDay.getDay() - 1;
        for (let i = 0; i < remainingDays; i++) {
            const emptyDay = document.createElement('div');
            emptyDay.className = 'calendar-day empty';
            calendarGrid.appendChild(emptyDay);
        }
    }

    createDayCell(day, year, month) {
        const currentDate = new Date(year, month, day);
        const dateString = currentDate.toISOString().split('T')[0];
        const isWeekend = currentDate.getDay() === 0 || currentDate.getDay() === 6;
        const isToday = currentDate.toDateString() === new Date().toDateString();

        const dayCell = document.createElement('div');
        dayCell.className = `calendar-day${isWeekend ? ' weekend' : ''}${isToday ? ' today' : ''}`;
        
        // Add day number
        const dayNumber = document.createElement('div');
        dayNumber.className = 'day-number';
        dayNumber.textContent = day;
        dayCell.appendChild(dayNumber);

        // Add shift container
        const shiftContainer = document.createElement('div');
        shiftContainer.className = 'shift-container';
        
        // Add shifts if they exist for this date
        if (this.currentRoster && this.currentRoster.shifts[dateString]) {
            this.currentRoster.shifts[dateString].forEach(shift => {
                const shiftElement = this.createRosterShiftElement(shift);
                shiftContainer.appendChild(shiftElement);
            });
        } else {
            const emptyMessage = document.createElement('div');
            emptyMessage.className = 'empty-day-message';
            emptyMessage.textContent = 'No shifts';
            shiftContainer.appendChild(emptyMessage);
        }

        dayCell.appendChild(shiftContainer);

        // Make the cell a drop target for shifts
        this.makeDropTarget(dayCell, dateString);

        return dayCell;
    }

    createRosterShiftElement(shift) {
        const shiftElement = document.createElement('div');
        shiftElement.className = 'roster-shift';
        shiftElement.draggable = true;
        shiftElement.dataset.shiftId = shift.id;

        shiftElement.innerHTML = `
            <div class="shift-info">
                <div class="shift-time">${shift.startTime} - ${shift.endTime}</div>
                <div class="shift-employees">${shift.employees ? shift.employees.join(', ') : 'No employees assigned'}</div>
            </div>
        `;

        // Add drag event listeners
        shiftElement.addEventListener('dragstart', (e) => {
            e.dataTransfer.setData('text/plain', shift.id);
            shiftElement.classList.add('dragging');
        });

        shiftElement.addEventListener('dragend', () => {
            shiftElement.classList.remove('dragging');
        });

        return shiftElement;
    }

    makeDropTarget(element, date) {
        element.addEventListener('dragover', (e) => {
            e.preventDefault();
            element.classList.add('drag-over');
        });

        element.addEventListener('dragleave', () => {
            element.classList.remove('drag-over');
        });

        element.addEventListener('drop', async (e) => {
            e.preventDefault();
            element.classList.remove('drag-over');

            const shiftId = e.dataTransfer.getData('text/plain');
            await this.assignShiftToDate(shiftId, date);
        });
    }

    async assignShiftToDate(shiftId, date) {
        if (!this.currentRoster) return;

        try {
            const response = await fetch('api/roster-assignments.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    rosterId: this.currentRoster.id,
                    shiftId,
                    date
                })
            });

            if (!response.ok) throw new Error('Failed to assign shift');

            // Update local data
            if (!this.currentRoster.shifts[date]) {
                this.currentRoster.shifts[date] = [];
            }
            
            const shift = this.findShift(shiftId);
            if (shift) {
                this.currentRoster.shifts[date].push(shift);
                this.updateCalendar();
                this.showSuccess('Shift assigned successfully');
            }
        } catch (error) {
            this.showError('Failed to assign shift: ' + error.message);
        }
    }

    // Add method to handle shift removal
    removeShift(shiftElement) {
        if (shiftElement && shiftElement.parentNode) {
            shiftElement.remove();
        }
    }
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    const scheduleManager = new ScheduleManager();
});

// Shift Modal Tab Management
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('addShiftModal');
    const tabButtons = document.querySelectorAll('.shift-tab-btn');
    const tabContents = document.querySelectorAll('.shift-tab-content');
    const prevBtn = document.querySelector('.prev-tab-btn');
    const nextBtn = document.querySelector('.next-tab-btn');
    const submitBtn = document.querySelector('.submit-btn');
    const cancelBtn = document.querySelector('.cancel-btn');
    
    let currentTab = 0;
    const totalTabs = tabButtons.length;

    // Function to show/hide tabs based on checkbox states
    function updateTabVisibility() {
        const paidHolidays = document.getElementById('paidHolidays').checked;
        const nightShiftAllowance = document.getElementById('nightShiftAllowance').checked;
        const splitNormalTime = document.getElementById('splitNormalTime').checked;

        // Show/hide holiday tab
        const holidayTab = document.querySelector('[data-tab="shift-holidays"]');
        holidayTab.style.display = paidHolidays ? 'flex' : 'none';

        // Show/hide night allowance tab
        const nightAllowanceTab = document.querySelector('[data-tab="shift-night-allowance"]');
        nightAllowanceTab.style.display = nightShiftAllowance ? 'flex' : 'none';

        // Show/hide split time tab
        const splitTimeTab = document.querySelector('[data-tab="shift-split-time"]');
        splitTimeTab.style.display = splitNormalTime ? 'flex' : 'none';
    }

    // Event listeners for checkboxes
    document.getElementById('paidHolidays').addEventListener('change', updateTabVisibility);
    document.getElementById('nightShiftAllowance').addEventListener('change', updateTabVisibility);
    document.getElementById('splitNormalTime').addEventListener('change', updateTabVisibility);

    // Function to switch tabs
    function switchTab(index) {
        // Remove active class from all tabs
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));

        // Add active class to current tab
        tabButtons[index].classList.add('active');
        tabContents[index].classList.add('active');

        // Update button visibility
        prevBtn.style.display = index === 0 ? 'none' : 'block';
        nextBtn.style.display = index === totalTabs - 1 ? 'none' : 'block';
        submitBtn.style.display = index === totalTabs - 1 ? 'block' : 'none';
    }

    // Next button click handler
    nextBtn.addEventListener('click', () => {
        if (validateCurrentTab()) {
            currentTab++;
            switchTab(currentTab);
        }
    });

    // Previous button click handler
    prevBtn.addEventListener('click', () => {
        currentTab--;
        switchTab(currentTab);
    });

    // Tab button click handler
    tabButtons.forEach((btn, index) => {
        btn.addEventListener('click', () => {
            if (index <= currentTab || validateCurrentTab()) {
                currentTab = index;
                switchTab(currentTab);
            }
        });
    });

    // Validate current tab
    function validateCurrentTab() {
        const currentForm = tabContents[currentTab].querySelector('form');
        if (!currentForm) return true;

        const requiredFields = currentForm.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                isValid = false;
                field.classList.add('error');
            } else {
                field.classList.remove('error');
            }
        });

        if (!isValid) {
            alert('Please fill in all required fields');
        }

        return isValid;
    }

    // Submit form handler
    submitBtn.addEventListener('click', () => {
        if (validateCurrentTab()) {
            // Collect all form data
            const formData = new FormData();
            
            // Add data from all forms
            tabContents.forEach(content => {
                const form = content.querySelector('form');
                if (form) {
                    const formDataObj = new FormData(form);
                    for (let [key, value] of formDataObj.entries()) {
                        formData.append(key, value);
                    }
                }
            });

            // Send data to server
            fetch('../api/shifts.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Shift created successfully');
                    modal.style.display = 'none';
                    // Refresh shift list or update UI as needed
                } else {
                    alert('Error creating shift: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while creating the shift');
            });
        }
    });

    // Cancel button handler
    cancelBtn.addEventListener('click', () => {
        modal.style.display = 'none';
        // Reset forms
        tabContents.forEach(content => {
            const form = content.querySelector('form');
            if (form) form.reset();
        });
        // Reset to first tab
        currentTab = 0;
        switchTab(currentTab);
    });

    // Close modal when clicking outside
    window.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.style.display = 'none';
        }
    });
});

// Time Input Formatting
document.addEventListener('DOMContentLoaded', function() {
    // Get all time inputs with data-format attribute
    const timeInputs = document.querySelectorAll('input[type="time"][data-format="true"]');
    
    timeInputs.forEach(input => {
        // Handle input event for real-time formatting
        input.addEventListener('input', function(e) {
            formatTimeInput(e.target);
        });

        // Handle blur event to ensure proper format when leaving the field
        input.addEventListener('blur', function(e) {
            formatTimeInput(e.target);
        });
    });
});

function formatTimeInput(input) {
    let value = input.value.replace(/[^0-9]/g, '');
    
    // If the value is empty, return
    if (!value) return;
    
    // Ensure the value is 4 digits (HHMM)
    if (value.length > 4) {
        value = value.slice(0, 4);
    }
    
    // Pad with leading zeros if needed
    while (value.length < 4) {
        value = '0' + value;
    }
    
    // Extract hours and minutes
    const hours = parseInt(value.slice(0, 2));
    const minutes = parseInt(value.slice(2, 4));
    
    // Validate hours and minutes
    if (hours > 23) {
        value = '23' + value.slice(2);
    }
    if (minutes > 59) {
        value = value.slice(0, 2) + '59';
    }
    
    // Format the time string
    const formattedTime = value.slice(0, 2) + ':' + value.slice(2, 4);
    
    // Update the input value
    input.value = formattedTime;
}

// Validate time inputs before form submission
function validateTimeInputs(form) {
    const timeInputs = form.querySelectorAll('input[type="time"][data-format="true"]');
    let isValid = true;
    
    timeInputs.forEach(input => {
        if (input.required && !input.value) {
            isValid = false;
            input.classList.add('error');
        } else {
            input.classList.remove('error');
        }
    });
    
    return isValid;
}

// Update the form validation function to include time validation
function validateCurrentTab() {
    const currentTab = document.querySelector('.shift-tab-content.active');
    const form = currentTab.querySelector('form');
    
    if (!form) return true;
    
    // Validate required fields
    const requiredFields = form.querySelectorAll('[required]');
    let isValid = true;
    
    requiredFields.forEach(field => {
        if (!field.value) {
            isValid = false;
            field.classList.add('error');
        } else {
            field.classList.remove('error');
        }
    });
    
    // Validate time inputs
    if (isValid) {
        isValid = validateTimeInputs(form);
    }
    
    return isValid;
}