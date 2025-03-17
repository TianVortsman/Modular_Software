// Add KPI functionality to existing dashboard scripts
document.addEventListener('DOMContentLoaded', function() {
    // ... existing code ...
    
    // Initialize KPI functionality
    initializeKPISection();
});

/**
 * Initialize the KPI section functionality
 */
function initializeKPISection() {
    // KPI Period selector
    const kpiPeriodSelect = document.getElementById('kpi-period');
    const customDateRange = document.getElementById('custom-date-range-kpi');
    const kpiStartDate = document.getElementById('kpi-start-date');
    const kpiEndDate = document.getElementById('kpi-end-date');
    const applyCustomDates = document.getElementById('apply-custom-dates');
    
    // KPI Tabs
    const kpiTabs = document.querySelectorAll('.kpi-tab');
    const kpiContents = document.querySelectorAll('.kpi-content');
    
    // Set default dates for the custom date range
    const today = new Date();
    const oneMonthAgo = new Date();
    oneMonthAgo.setMonth(today.getMonth() - 1);
    
    kpiStartDate.value = formatDateForInput(oneMonthAgo);
    kpiEndDate.value = formatDateForInput(today);
    
    // Add smooth tab indicator functionality
    initializeTabIndicator();
    
    // Period selector change event
    if (kpiPeriodSelect) {
        kpiPeriodSelect.addEventListener('change', function() {
            if (this.value === 'custom') {
                customDateRange.classList.remove('hidden');
                // Add animation class
                customDateRange.classList.add('fade-in');
                // Remove animation class after animation completes
                setTimeout(() => {
                    customDateRange.classList.remove('fade-in');
                }, 500);
            } else {
                // Add fade-out animation before hiding
                customDateRange.classList.add('fade-out');
                setTimeout(() => {
                    customDateRange.classList.add('hidden');
                    customDateRange.classList.remove('fade-out');
                }, 300);
                
                // Show loading state
                showLoadingState();
                
                // Load data after a short delay to allow UI to update
                setTimeout(() => {
                    loadKPIData(this.value);
                }, 100);
            }
        });
    }
    
    // Apply custom date range
    if (applyCustomDates) {
        applyCustomDates.addEventListener('click', function() {
            if (kpiStartDate.value && kpiEndDate.value) {
                // Show loading state
                showLoadingState();
                
                // Load data after a short delay
                setTimeout(() => {
                    loadKPIData('custom', kpiStartDate.value, kpiEndDate.value);
                }, 100);
            } else {
                // Show validation error with animation
                const inputs = [kpiStartDate, kpiEndDate];
                inputs.forEach(input => {
                    if (!input.value) {
                        input.classList.add('error-shake');
                        setTimeout(() => {
                            input.classList.remove('error-shake');
                        }, 500);
                    }
                });
                
                showNotification('Please select both start and end dates', 'error');
            }
        });
    }
    
    // Tab switching with improved animation
    kpiTabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Only proceed if this isn't already the active tab
            if (!tab.classList.contains('active')) {
                // Get the tab ID
                const tabId = tab.getAttribute('data-tab');
                const tabContent = document.getElementById(`${tabId}-tab`);
                
                // Get currently active tab for animation
                const activeTab = document.querySelector('.kpi-tab.active');
                const activeContent = document.querySelector('.kpi-content.active');
                
                // Update tab indicator position before switching tabs
                updateTabIndicatorPosition(tab);
                
                // Remove active class from all tabs
                kpiTabs.forEach(t => t.classList.remove('active'));
                
                // Add active class to clicked tab
                tab.classList.add('active');
                
                // Animate the tab content transition
                if (activeContent && tabContent) {
                    // Add exit animation to current active content
                    activeContent.classList.add('tab-exit');
                    
                    // After exit animation completes, switch tabs and play entrance animation
                    setTimeout(() => {
                        // Remove active and exit classes from all content
                        kpiContents.forEach(c => {
                            c.classList.remove('active', 'tab-exit', 'tab-enter');
                        });
                        
                        // Add active and enter classes to new content
                        tabContent.classList.add('active', 'tab-enter');
                        
                        // Refresh data for the selected tab
                        const period = kpiPeriodSelect.value;
                        if (period === 'custom') {
                            loadKPIData('custom', kpiStartDate.value, kpiEndDate.value, tabId);
                        } else {
                            loadKPIData(period, null, null, tabId);
                        }
                    }, 150);
                }
            }
        });
    });
    
    // Load initial KPI data with loading indicator
    showLoadingState();
    setTimeout(() => {
        loadKPIData('month');
    }, 300);
    
    // Add window resize handler for tab indicator
    window.addEventListener('resize', () => {
        updateTabIndicatorPosition(document.querySelector('.kpi-tab.active'));
    });
}

/**
 * Initialize the tab indicator
 */
function initializeTabIndicator() {
    const tabsContainer = document.querySelector('.kpi-tabs');
    
    // Only proceed if tabs container exists
    if (!tabsContainer) return;
    
    // Check if indicator already exists
    let indicator = tabsContainer.querySelector('.tab-indicator');
    
    // Create the indicator if it doesn't exist
    if (!indicator) {
        indicator = document.createElement('span');
        indicator.className = 'tab-indicator';
        tabsContainer.appendChild(indicator);
        
        // Add indicator styles if not already in CSS
        const style = document.createElement('style');
        style.textContent = `
            .kpi-tabs {
                position: relative;
            }
            .tab-indicator {
                position: absolute;
                bottom: 0;
                height: 2px;
                background-color: var(--primary-color);
                transition: all 0.3s ease;
                z-index: 2;
            }
            .tab-exit {
                animation: tabFadeOut 0.15s ease-in-out forwards;
            }
            .tab-enter {
                animation: tabFadeIn 0.3s ease-in-out forwards;
            }
            @keyframes tabFadeOut {
                from {
                    opacity: 1;
                    transform: translateY(0);
                }
                to {
                    opacity: 0;
                    transform: translateY(5px);
                }
            }
            .error-shake {
                animation: errorShake 0.4s ease-in-out;
                border-color: var(--error-color, #e53935) !important;
            }
            @keyframes errorShake {
                0%, 100% { transform: translateX(0); }
                25% { transform: translateX(-5px); }
                50% { transform: translateX(5px); }
                75% { transform: translateX(-5px); }
            }
            .fade-in {
                animation: fadeIn 0.3s ease-in-out forwards;
            }
            .fade-out {
                animation: fadeOut 0.3s ease-in-out forwards;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(-10px); }
                to { opacity: 1; transform: translateY(0); }
            }
            @keyframes fadeOut {
                from { opacity: 1; transform: translateY(0); }
                to { opacity: 0; transform: translateY(-10px); }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Set initial position of the indicator
    updateTabIndicatorPosition(document.querySelector('.kpi-tab.active'));
}

/**
 * Update the position of the tab indicator
 * @param {HTMLElement} activeTab - The active tab element
 */
function updateTabIndicatorPosition(activeTab) {
    const indicator = document.querySelector('.tab-indicator');
    
    if (indicator && activeTab) {
        const tabRect = activeTab.getBoundingClientRect();
        const tabsContainerRect = activeTab.parentElement.getBoundingClientRect();
        
        // Set indicator width and position
        indicator.style.width = `${tabRect.width}px`;
        indicator.style.left = `${tabRect.left - tabsContainerRect.left}px`;
    }
}

/**
 * Show loading state for the entire KPI section
 */
function showLoadingState() {
    const contents = document.querySelectorAll('.kpi-content');
    
    contents.forEach(content => {
        // Add loading class to content
        content.classList.add('loading-state');
        
        // Save the KPI summary
        const summary = content.querySelector('.kpi-summary');
        if (summary) {
            summary.querySelectorAll('.kpi-number').forEach(num => {
                num.dataset.originalValue = num.textContent;
                num.textContent = '...';
                num.classList.add('pulsing');
            });
        }
        
        // Add loading indicators to tables
        const tbody = content.querySelector('tbody');
        if (tbody) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="loading-row">Loading data...</td>
                </tr>
            `;
        }
    });
}

/**
 * Show a notification message
 * @param {string} message - The message to display
 * @param {string} type - The type of notification (success, error, info)
 */
function showNotification(message, type = 'info') {
    // Check if notification container exists
    let container = document.getElementById('notification-container');
    
    // Create container if it doesn't exist
    if (!container) {
        container = document.createElement('div');
        container.id = 'notification-container';
        container.style.cssText = `
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        `;
        document.body.appendChild(container);
    }
    
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.innerHTML = `
        <div class="notification-content">${message}</div>
        <button class="notification-close">&times;</button>
    `;
    notification.style.cssText = `
        background-color: ${type === 'error' ? 'rgba(229, 57, 53, 0.9)' : 
                           type === 'success' ? 'rgba(76, 175, 80, 0.9)' : 
                           'rgba(33, 150, 243, 0.9)'};
        color: white;
        padding: 12px 20px;
        border-radius: 4px;
        margin-top: 10px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.2);
        animation: slideIn 0.3s ease-out forwards;
        transform: translateX(100%);
    `;
    
    // Add animation styles if not already added
    if (!document.getElementById('notification-styles')) {
        const style = document.createElement('style');
        style.id = 'notification-styles';
        style.textContent = `
            @keyframes slideIn {
                to { transform: translateX(0); }
            }
            @keyframes slideOut {
                to { transform: translateX(120%); }
            }
            .notification-close {
                background: none;
                border: none;
                color: white;
                font-size: 20px;
                cursor: pointer;
                margin-left: 10px;
                opacity: 0.7;
                transition: opacity 0.2s;
            }
            .notification-close:hover {
                opacity: 1;
            }
            .pulsing {
                animation: pulse 1.5s infinite ease-in-out;
            }
            @keyframes pulse {
                0%, 100% { opacity: 0.6; }
                50% { opacity: 1; }
            }
        `;
        document.head.appendChild(style);
    }
    
    // Add to container
    container.appendChild(notification);
    
    // Set up close button
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.style.animation = 'slideOut 0.3s ease-in forwards';
        setTimeout(() => {
            notification.remove();
        }, 300);
    });
    
    // Auto remove after 5 seconds
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOut 0.3s ease-in forwards';
            setTimeout(() => {
                notification.remove();
            }, 300);
        }
    }, 5000);
}

/**
 * Load KPI data from the server
 * @param {string} period - The time period to load data for (week, month, quarter, year, custom)
 * @param {string} startDate - The start date for custom period (optional)
 * @param {string} endDate - The end date for custom period (optional)
 * @param {string} tabType - The specific tab to load data for (optional)
 */
function loadKPIData(period, startDate, endDate, tabType) {
    // Build the API URL based on parameters
    let apiUrl = `/modular1/modules/timeandatt/api/kpi-data.php?period=${period}`;
    
    if (period === 'custom' && startDate && endDate) {
        apiUrl += `&start_date=${startDate}&end_date=${endDate}`;
    }
    
    if (tabType) {
        apiUrl += `&tab=${tabType}`;
    }
    
    // Track loading start time for minimal display time
    const loadStart = Date.now();
    const minLoadingTime = 700; // Minimum time to show loading state in ms
    
    // Fetch KPI data from the server
    fetch(apiUrl)
        .then(response => response.json())
        .then(data => {
            // Calculate how long we've been loading
            const loadTime = Date.now() - loadStart;
            const remainingTime = Math.max(0, minLoadingTime - loadTime);
            
            // Wait at least minLoadingTime before updating UI
            setTimeout(() => {
                if (data.success) {
                    // Update the KPI data in the UI
                    updateKPIData(data, tabType);
                    
                    // Show success message if any data was updated
                    if (data.summary) {
                        showNotification('KPI data updated successfully', 'success');
                    }
                } else {
                    console.error('Failed to load KPI data:', data.message);
                    showKPIError(tabType);
                    showNotification(`Error: ${data.message || 'Failed to load KPI data'}`, 'error');
                }
                
                // Remove loading state from all contents
                document.querySelectorAll('.kpi-content').forEach(content => {
                    content.classList.remove('loading-state');
                });
            }, remainingTime);
        })
        .catch(error => {
            console.error('Error fetching KPI data:', error);
            
            // Calculate how long we've been loading
            const loadTime = Date.now() - loadStart;
            const remainingTime = Math.max(0, minLoadingTime - loadTime);
            
            // Wait at least minLoadingTime before showing error
            setTimeout(() => {
                showKPIError(tabType);
                showNotification('Error connecting to server. Please try again.', 'error');
                
                // Remove loading state from all contents
                document.querySelectorAll('.kpi-content').forEach(content => {
                    content.classList.remove('loading-state');
                });
            }, remainingTime);
        });
}

/**
 * Show error state for KPI content
 * @param {string} tabType - The specific tab to show error for (optional)
 */
function showKPIError(tabType) {
    const tabs = tabType ? [document.getElementById(`${tabType}-tab`)] : document.querySelectorAll('.kpi-content');
    
    tabs.forEach(tab => {
        if (tab) {
            // Restore original values in the KPI summary if available
            const summary = tab.querySelector('.kpi-summary');
            if (summary) {
                summary.querySelectorAll('.kpi-number').forEach(num => {
                    if (num.dataset.originalValue) {
                        num.textContent = num.dataset.originalValue;
                    }
                    num.classList.remove('pulsing');
                });
            }
            
            // Show error in the table
            const tbody = tab.querySelector('tbody');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="error-row">
                            <i class="material-icons">error_outline</i>
                            Error loading data. Please try again.
                            <button class="retry-button">Retry</button>
                        </td>
                    </tr>
                `;
                
                // Add retry button functionality
                const retryButton = tbody.querySelector('.retry-button');
                if (retryButton) {
                    retryButton.addEventListener('click', () => {
                        // Show loading again
                        showLoadingState();
                        
                        // Get current period value
                        const period = document.getElementById('kpi-period').value;
                        
                        // Reload data
                        if (period === 'custom') {
                            const startDate = document.getElementById('kpi-start-date').value;
                            const endDate = document.getElementById('kpi-end-date').value;
                            setTimeout(() => {
                                loadKPIData('custom', startDate, endDate, tabType);
                            }, 100);
                        } else {
                            setTimeout(() => {
                                loadKPIData(period, null, null, tabType);
                            }, 100);
                        }
                    });
                }
            }
        }
    });
    
    // Add CSS for retry button if not already added
    if (!document.getElementById('retry-button-style')) {
        const style = document.createElement('style');
        style.id = 'retry-button-style';
        style.textContent = `
            .error-row {
                display: flex;
                align-items: center;
                justify-content: center;
                gap: 10px;
            }
            .error-row i {
                color: var(--error-color, #e53935);
                margin-right: 5px;
            }
            .retry-button {
                background-color: var(--primary-color);
                color: white;
                border: none;
                border-radius: 4px;
                padding: 5px 10px;
                margin-left: 10px;
                cursor: pointer;
                font-size: 0.8rem;
                transition: background-color 0.2s;
            }
            .retry-button:hover {
                background-color: var(--primary-color-dark);
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Update KPI data in the UI
 * @param {Object} data - The KPI data from the server
 * @param {string} tabType - The specific tab to update (optional)
 */
function updateKPIData(data, tabType) {
    // If specific tab is provided, only update that tab
    if (tabType && data[tabType]) {
        updateKPITab(tabType, data[tabType]);
    } else {
        // Update all tabs with their respective data
        if (data.late) updateKPITab('late', data.late);
        if (data.absent) updateKPITab('absent', data.absent);
        if (data.perfect) updateKPITab('perfect', data.perfect);
        if (data.overtime) updateKPITab('overtime', data.overtime);
    }
    
    // Update widgets with summary data if available
    if (data.summary) {
        updateDashboardWidgets(data.summary);
    }
    
    // Restore any pulsing numbers to their new values
    document.querySelectorAll('.kpi-number.pulsing').forEach(num => {
        num.classList.remove('pulsing');
    });
    
    // Add animation to the updated numbers
    document.querySelectorAll('.kpi-number').forEach(num => {
        // Check if the number has changed
        if (num.dataset.previousValue && num.dataset.previousValue !== num.textContent) {
            num.classList.add('value-changed');
            setTimeout(() => {
                num.classList.remove('value-changed');
            }, 1500);
        }
        
        // Store current value for next comparison
        num.dataset.previousValue = num.textContent;
    });
    
    // Add CSS for value changed animation if not already added
    if (!document.getElementById('value-change-style')) {
        const style = document.createElement('style');
        style.id = 'value-change-style';
        style.textContent = `
            .value-changed {
                animation: valueChange 1.5s ease-out;
            }
            @keyframes valueChange {
                0% { background-color: rgba(var(--primary-rgb), 0.1); }
                50% { background-color: rgba(var(--primary-rgb), 0.15); }
                100% { background-color: transparent; }
            }
        `;
        document.head.appendChild(style);
    }
}

/**
 * Update a specific KPI tab with data
 * @param {string} tabType - The tab to update
 * @param {Object} tabData - The data for the tab
 */
function updateKPITab(tabType, tabData) {
    const tabContent = document.getElementById(`${tabType}-tab`);
    if (!tabContent) return;
    
    // Update summary statistics
    if (tabData.stats) {
        const lateEmployeeCount = tabContent.querySelector(`#${tabType}-employee-count`);
        if (lateEmployeeCount) lateEmployeeCount.textContent = tabData.stats.employeeCount || 0;
        
        // Update specific metrics for each tab type
        switch (tabType) {
            case 'late':
                const lateOccurrenceCount = tabContent.querySelector('#late-occurrence-count');
                const lateMinutesAvg = tabContent.querySelector('#late-minutes-avg');
                
                if (lateOccurrenceCount) lateOccurrenceCount.textContent = tabData.stats.occurrenceCount || 0;
                if (lateMinutesAvg) lateMinutesAvg.textContent = tabData.stats.minutesAvg || 0;
                break;
                
            case 'absent':
                const absentDaysCount = tabContent.querySelector('#absent-days-count');
                const absentRate = tabContent.querySelector('#absent-rate');
                
                if (absentDaysCount) absentDaysCount.textContent = tabData.stats.daysCount || 0;
                if (absentRate) absentRate.textContent = tabData.stats.rate || '0%';
                break;
                
            case 'perfect':
                const perfectPercentage = tabContent.querySelector('#perfect-percentage');
                const perfectAvgCheckin = tabContent.querySelector('#perfect-avg-checkin');
                
                if (perfectPercentage) perfectPercentage.textContent = tabData.stats.percentage || '0%';
                if (perfectAvgCheckin) perfectAvgCheckin.textContent = tabData.stats.avgCheckin || '--:--';
                break;
                
            case 'overtime':
                const overtimeHoursTotal = tabContent.querySelector('#overtime-hours-total');
                const overtimeCost = tabContent.querySelector('#overtime-cost');
                
                if (overtimeHoursTotal) overtimeHoursTotal.textContent = tabData.stats.hoursTotal || 0;
                if (overtimeCost) overtimeCost.textContent = tabData.stats.cost || '$0';
                break;
        }
    }
    
    // Update employee list table
    if (tabData.employees && tabData.employees.length > 0) {
        const tableId = `${tabType}-employees-table`;
        const table = document.getElementById(tableId);
        if (table) {
            const tbody = table.querySelector('tbody');
            if (tbody) {
                tbody.innerHTML = '';
                
                tabData.employees.forEach(employee => {
                    const row = document.createElement('tr');
                    
                    // Common columns for all tables
                    row.innerHTML = `
                        <td>${employee.name}</td>
                        <td>${employee.department}</td>
                    `;
                    
                    // Add specific columns based on tab type
                    switch (tabType) {
                        case 'late':
                            row.innerHTML += `
                                <td>${employee.lateCount}</td>
                                <td>${employee.avgMinutesLate}</td>
                                <td>${employee.lastLate}</td>
                                <td>
                                    <button class="action-button small view-history" data-employee-id="${employee.id}">History</button>
                                    <button class="action-button small contact" data-employee-id="${employee.id}">Contact</button>
                                </td>
                            `;
                            break;
                            
                        case 'absent':
                            row.innerHTML += `
                                <td>${employee.absentDays}</td>
                                <td>${employee.absentRate}</td>
                                <td>${employee.lastAbsent}</td>
                                <td>
                                    <button class="action-button small view-history" data-employee-id="${employee.id}">History</button>
                                    <button class="action-button small contact" data-employee-id="${employee.id}">Contact</button>
                                </td>
                            `;
                            break;
                            
                        case 'perfect':
                            row.innerHTML += `
                                <td>${employee.daysPresent}</td>
                                <td>${employee.avgCheckin}</td>
                                <td>${employee.lastCheckin}</td>
                                <td>
                                    <button class="action-button small view-history" data-employee-id="${employee.id}">History</button>
                                    <button class="action-button small reward" data-employee-id="${employee.id}">Reward</button>
                                </td>
                            `;
                            break;
                            
                        case 'overtime':
                            row.innerHTML += `
                                <td>${employee.overtimeHours}</td>
                                <td>${employee.overtimeRate}</td>
                                <td>${employee.lastOvertime}</td>
                                <td>
                                    <button class="action-button small view-history" data-employee-id="${employee.id}">History</button>
                                    <button class="action-button small approve" data-employee-id="${employee.id}">Approve</button>
                                </td>
                            `;
                            break;
                    }
                    
                    tbody.appendChild(row);
                });
                
                // Add event listeners to the action buttons
                addKPIActionButtonListeners(tbody, tabType);
            }
        }
    } else {
        // No employees data
        const tableId = `${tabType}-employees-table`;
        const table = document.getElementById(tableId);
        if (table) {
            const tbody = table.querySelector('tbody');
            if (tbody) {
                tbody.innerHTML = `
                    <tr>
                        <td colspan="6" class="empty-row">No employees found for this criteria</td>
                    </tr>
                `;
            }
        }
    }
}

/**
 * Add event listeners to KPI action buttons
 * @param {HTMLElement} container - The container with action buttons
 * @param {string} tabType - The type of tab (late, absent, perfect, overtime)
 */
function addKPIActionButtonListeners(container, tabType) {
    // View history buttons
    const historyButtons = container.querySelectorAll('.view-history');
    historyButtons.forEach(button => {
        button.addEventListener('click', function() {
            const employeeId = this.getAttribute('data-employee-id');
            showEmployeeHistory(employeeId, tabType);
        });
    });
    
    // Contact buttons
    const contactButtons = container.querySelectorAll('.contact');
    contactButtons.forEach(button => {
        button.addEventListener('click', function() {
            const employeeId = this.getAttribute('data-employee-id');
            contactEmployee(employeeId);
        });
    });
    
    // Reward buttons
    const rewardButtons = container.querySelectorAll('.reward');
    rewardButtons.forEach(button => {
        button.addEventListener('click', function() {
            const employeeId = this.getAttribute('data-employee-id');
            rewardEmployee(employeeId);
        });
    });
    
    // Approve buttons
    const approveButtons = container.querySelectorAll('.approve');
    approveButtons.forEach(button => {
        button.addEventListener('click', function() {
            const employeeId = this.getAttribute('data-employee-id');
            approveOvertime(employeeId);
        });
    });
}

/**
 * Show employee attendance history
 * @param {string} employeeId - The employee ID
 * @param {string} historyType - The type of history to display
 */
function showEmployeeHistory(employeeId, historyType) {
    // This function would be implemented to show a modal with detailed history
    console.log(`Showing ${historyType} history for employee ${employeeId}`);
    // Ideally, this would open a modal with detailed attendance history
}

/**
 * Contact an employee
 * @param {string} employeeId - The employee ID
 */
function contactEmployee(employeeId) {
    // This function would be implemented to show a contact modal
    console.log(`Contacting employee ${employeeId}`);
    // Ideally, this would open a modal with contact options
}

/**
 * Reward an employee
 * @param {string} employeeId - The employee ID
 */
function rewardEmployee(employeeId) {
    // This function would be implemented to show a reward modal
    console.log(`Rewarding employee ${employeeId}`);
    // Ideally, this would open a modal with reward options
}

/**
 * Approve overtime for an employee
 * @param {string} employeeId - The employee ID
 */
function approveOvertime(employeeId) {
    // This function would be implemented to show an overtime approval modal
    console.log(`Approving overtime for employee ${employeeId}`);
    // Ideally, this would open a modal with overtime details and approval options
}

/**
 * Update the dashboard widgets with summary data
 * @param {Object} summaryData - Summary data for dashboard widgets
 */
function updateDashboardWidgets(summaryData) {
    // Update total employees widget
    if (summaryData.totalEmployees !== undefined) {
        const totalEmployeesElement = document.getElementById('total-employees');
        if (totalEmployeesElement) {
            totalEmployeesElement.textContent = summaryData.totalEmployees;
        }
    }
    
    // Update clocked in today widget
    if (summaryData.clockedInToday !== undefined) {
        const totalClockedInElement = document.getElementById('total-clocked-in');
        if (totalClockedInElement) {
            totalClockedInElement.textContent = summaryData.clockedInToday;
        }
    }
    
    // Update late arrivals widget
    if (summaryData.lateArrivalsToday !== undefined) {
        const lateArrivalsElement = document.getElementById('late-arrivals');
        if (lateArrivalsElement) {
            lateArrivalsElement.textContent = summaryData.lateArrivalsToday;
        }
    }
    
    // Update overtime hours widget
    if (summaryData.overtimeHours !== undefined) {
        const totalOvertimeElement = document.getElementById('total-overtime');
        if (totalOvertimeElement) {
            totalOvertimeElement.textContent = summaryData.overtimeHours;
        }
    }
    
    // Update average check-in time widget
    if (summaryData.avgCheckinTime) {
        const avgCheckinTimeElement = document.getElementById('avg-checkin-time');
        if (avgCheckinTimeElement) {
            avgCheckinTimeElement.textContent = summaryData.avgCheckinTime;
        }
    }
    
    // Update absent today widget
    if (summaryData.absentToday !== undefined) {
        const totalAbsentElement = document.getElementById('total-absent');
        if (totalAbsentElement) {
            totalAbsentElement.textContent = summaryData.absentToday;
        }
    }
    
    // Update on leave widget
    if (summaryData.onLeave !== undefined) {
        const totalLeaveElement = document.getElementById('total-leave');
        if (totalLeaveElement) {
            totalLeaveElement.textContent = summaryData.onLeave;
        }
    }
}

/**
 * Format a date for input fields
 * @param {Date} date - The date to format
 * @returns {string} - Formatted date string (YYYY-MM-DD)
 */
function formatDateForInput(date) {
    const year = date.getFullYear();
    const month = String(date.getMonth() + 1).padStart(2, '0');
    const day = String(date.getDate()).padStart(2, '0');
    return `${year}-${month}-${day}`;
} 