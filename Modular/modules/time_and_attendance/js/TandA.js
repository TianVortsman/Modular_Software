// Time and Attendance Dashboard JavaScript

// Get account number from meta tag
const accountNumber = document.querySelector('meta[name="account-number"]').content;

// Variables for tracking last seen activity
let lastSeenActivityId = 0;
let lastSeenAccessId = 0;  // Add this variable for access events
let lastSeenUnknownId = 0; // For unknown users
let lastSeenErrorId = 0;   // For errors
let notificationSound;

document.addEventListener('DOMContentLoaded', function() {
    console.log('Time and Attendance dashboard initialized');
    
    // Initialize notification sound
    notificationSound = new Audio('../../../public/assets/sounds/notification.mp3');
    
    // Initialize dashboard data
    initializeDashboard();
    
    // Set up the KPI tabs
    setupKpiTabs();
});

/**
 * Initialize the dashboard data and start real-time updates
 */
function initializeDashboard() {
    // Load dashboard statistics
    updateDashboardStats();
    
    // Initialize recent activity with real-time updates
    initializeRecentActivity();
    
    // Initialize access control with real-time updates
    initializeAccessControl();
    
    // Setup activity tabs
    setupActivityTabs();
    
    // Setup expand/collapse functionality
    setupActivityExpansion();
    
    // Set up periodic refresh for dashboard stats (every 30 seconds)
    setInterval(updateDashboardStats, 30000);
}

/**
 * Initialize the recent activity table and set up real-time updates
 */
async function initializeRecentActivity() {
    // Initial load of recent activity
    await fetchRecentActivity();
    
    // Set up polling for real-time updates every 5 seconds
    setInterval(fetchRecentActivity, 5000);
}

/**
 * Fetch recent activity data from the API
 */
async function fetchRecentActivity() {
    try {
        const response = await fetch(`../../../src/api/time_attendance.php?action=get_recent_activity&account=${accountNumber}`);
        
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}`);
        }
        
        const data = await response.json();
        window.handleApiResponse(data);
        
        if (data.success) {
            // Process activities for different tabs
            const activities = data.activities || [];
            
            // Split activities into different categories
            const timeEntries = [];
            const unknownUsers = [];
            const errorEntries = [];
            
            const currentDate = new Date();
            // Strip time component for date comparison
            const currentDateOnly = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());
            const yesterday = new Date(currentDate);
            yesterday.setDate(yesterday.getDate() - 1);
            
            activities.forEach(activity => {
                const activityDate = new Date(activity.date_time);
                
                // Get current date once to ensure consistency
                const now = new Date();
                const currentYear = now.getFullYear();
                const currentMonth = now.getMonth();
                const currentDay = now.getDate();
                
                // Check if it's a future date by directly comparing years, months, and days
                const isFutureDate = (
                    activityDate.getFullYear() > currentYear ||
                    (activityDate.getFullYear() === currentYear && 
                     activityDate.getMonth() > currentMonth) ||
                    (activityDate.getFullYear() === currentYear && 
                     activityDate.getMonth() === currentMonth && 
                     activityDate.getDate() > currentDay)
                );
                
                console.log("Activity:", activity.employee_name || "Unknown", "Date:", activity.date_time, 
                           "Year:", activityDate.getFullYear(),
                           "Current Year:", currentYear,
                           "Is Future:", isFutureDate);
                
                // Check if it's a future date (any date beyond today) - these always go to Errors tab
                if (isFutureDate) {
                    // Mark as future date error
                    activity.errorType = 'Future Date';
                    activity.errorDetails = `Event date is in the future: ${activityDate.toLocaleDateString()}`;
                    console.log("Adding to errors: ", activity);
                    errorEntries.push(activity);
                }
                // Check if it's an unknown user (no employee name) - only for time entries
                else if (!activity.employee_name || activity.employee_name === 'Unknown') {
                    console.log("Adding to unknown users: ", activity);
                    unknownUsers.push(activity);
                }
                // Normal time entry
                else {
                    timeEntries.push(activity);
                }
            });
            
            // Update tables for each category
            updateRecentActivityTable(timeEntries);
            updateUnknownUsersTable(unknownUsers);
            updateErrorsTable(errorEntries);
        } else {
            console.error('Failed to fetch recent activity:', data.message);
        }
    } catch (error) {
        showResponseModal(error.message, 'error');
    }
}

/**
 * Format a date/time string for display
 */
function formatDateTime(dateTimeStr) {
    const date = new Date(dateTimeStr);
    const now = new Date();
    const yesterday = new Date(now);
    yesterday.setDate(yesterday.getDate() - 1);
    
    // Format the time portion
    const timeStr = date.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
    
    // Check if the date is today, yesterday, or another day
    if (date.toDateString() === now.toDateString()) {
        return `Today, ${timeStr}`;
    } else if (date.toDateString() === yesterday.toDateString()) {
        return `Yesterday, ${timeStr}`;
    } else {
        // Format as MM/DD/YYYY, HH:MM
        return `${date.toLocaleDateString()}, ${timeStr}`;
    }
}

/**
 * Determine the action text based on event types
 */
function determineAction(majorType, minorType, verifyStatus) {
    // First check verifyStatus if available
    if (verifyStatus === 'checkIn') return 'Clock In';
    if (verifyStatus === 'checkOut') return 'Clock Out';
    
    // Fallback to event types
    if (majorType === 5) return 'Clock In';
    if (majorType === 6) return 'Clock Out';
    
    // Default for other events
    return 'Activity';
}

/**
 * Update the recent activity table with new data
 */
function updateRecentActivityTable(activities) {
    if (!activities || activities.length === 0) {
        return;
    }
    
    const table = document.getElementById('recent-activity-table');
    let hasNewActivities = false;
    
    // Sort activities by date_time in descending order (newest first)
    activities.sort((a, b) => {
        // First try to sort by ID
        const idA = parseInt(a.attendance_id || a.id || 0);
        const idB = parseInt(b.attendance_id || b.id || 0);
        
        if (idA !== idB) {
            return idB - idA; // Descending order (newest first)
        }
        
        // If IDs are the same, use date_time
        return new Date(b.date_time) - new Date(a.date_time);
    });
    
    // Get the maximum ID to track for next update
    const maxId = Math.max(...activities.map(act => parseInt(act.attendance_id || act.id || 0)));
    
    // Check for new activities
    const newActivities = activities.filter(activity => {
        const activityId = parseInt(activity.attendance_id || activity.id || 0);
        return activityId > lastSeenActivityId;
    });
    
    if (newActivities.length > 0) {
        hasNewActivities = true;
    }
    
    // Build the entire table HTML at once, with newest at top
    let tableHTML = '';
    
    // Limit to 20 most recent activities (newest first)
    const limitedActivities = activities.slice(0, 20);
    
    // Generate HTML for each activity
    limitedActivities.forEach(activity => {
        const isNew = parseInt(activity.attendance_id || activity.id || 0) > lastSeenActivityId;
        const newClass = isNew ? 'class="new-activity"' : '';
        
        tableHTML += `<tr ${newClass}>
            <td>${activity.employee_name}</td>
            <td>${activity.clock_number || activity.employee_id}</td>
            <td>${formatDateTime(activity.date_time)}</td>
            <td>${determineAction(activity.major_event_type, activity.minor_event_type, activity.verify_status)}</td>
            <td>${activity.status || 'Recorded'}</td>
            <td>${activity.device_id || 'Unknown'}</td>
        </tr>`;
    });
    
    // Replace table content at once
    table.innerHTML = tableHTML;
    
    // Remove highlighting after animation completes
    if (hasNewActivities) {
        setTimeout(() => {
            const newRows = table.querySelectorAll('.new-activity');
            newRows.forEach(row => {
                row.classList.remove('new-activity');
            });
        }, 5000);
        
        // Play notification sound for new activities
        playNotificationSound();
    }
    
    // Update the last seen activity ID
    if (maxId > lastSeenActivityId) {
        lastSeenActivityId = maxId;
    }
}

/**
 * Play notification sound for new activities
 */
function playNotificationSound() {
    // Only play if the sound is loaded and document is visible
    if (notificationSound && !document.hidden) {
        notificationSound.play().catch(err => {
            console.log('Could not play notification sound:', err);
        });
    }
}

/**
 * Set up KPI tabs interaction
 */
function setupKpiTabs() {
    const tabs = document.querySelectorAll('.kpi-tab');
    const contents = document.querySelectorAll('.kpi-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs and contents
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab
            tab.classList.add('active');
            
            // Show corresponding content
            const tabName = tab.getAttribute('data-tab');
            document.getElementById(`${tabName}-tab`).classList.add('active');
        });
    });
}

/**
 * Fetch and update dashboard statistics
 */
async function updateDashboardStats() {
    try {
        const response = await fetch(`../../../src/api/time_attendance.php?action=get_dashboard_stats&account=${accountNumber}`);
        
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}`);
        }
        
        const data = await response.json();
        window.handleApiResponse(data);
        
        if (data.success) {
            // Update dashboard widgets with new stats
            document.getElementById('total-employees').textContent = data.stats.totalEmployees;
            document.getElementById('total-clocked-in').textContent = data.stats.clockedInToday;
            document.getElementById('late-arrivals').textContent = data.stats.lateArrivals;
            document.getElementById('total-overtime').textContent = data.stats.overtimeHours;
            document.getElementById('avg-checkin-time').textContent = data.stats.avgCheckinTime;
            document.getElementById('total-absent').textContent = data.stats.absentToday;
            document.getElementById('total-leave').textContent = data.stats.onLeave;
        } else {
            console.error('Failed to fetch dashboard stats:', data.message);
        }
    } catch (error) {
        showResponseModal(error.message, 'error');
    }
}

/**
 * Set up tabs for the activity section
 */
function setupActivityTabs() {
    const tabs = document.querySelectorAll('.activity-tab');
    const contents = document.querySelectorAll('.activity-content');
    
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            // Remove active class from all tabs and contents
            tabs.forEach(t => t.classList.remove('active'));
            contents.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked tab
            tab.classList.add('active');
            
            // Get corresponding content and activate it
            const contentId = `${tab.dataset.tab}-tab`;
            document.getElementById(contentId).classList.add('active');
        });
    });
}

/**
 * Set up expand/collapse functionality for the activity section
 */
function setupActivityExpansion() {
    const activityContainer = document.getElementById('recent-activity-container');
    const header = activityContainer.querySelector('.recent-activity-header');
    
    // Double-click on header to expand/collapse
    header.addEventListener('dblclick', toggleActivityExpansion);
    
    // Add escape key to collapse when expanded
    document.addEventListener('keydown', (e) => {
        if (e.key === 'Escape' && activityContainer.classList.contains('expanded')) {
            toggleActivityExpansion();
        }
    });
}

/**
 * Toggle the expansion state of the activity container
 */
function toggleActivityExpansion() {
    const activityContainer = document.getElementById('recent-activity-container');
    
    if (activityContainer.classList.contains('expanded')) {
        // Collapse
        activityContainer.classList.remove('expanded');
        activityContainer.classList.add('collapsing');
        
        setTimeout(() => {
            activityContainer.classList.remove('collapsing');
        }, 300);
    } else {
        // Expand
        activityContainer.classList.add('expanding');
        
        setTimeout(() => {
            activityContainer.classList.remove('expanding');
            activityContainer.classList.add('expanded');
        }, 300);
    }
}

/**
 * Initialize the access control activity table and set up real-time updates
 */
async function initializeAccessControl() {
    // Initial load of access control activity
    await fetchAccessActivity();
    
    // Set up polling for real-time updates every 5 seconds
    setInterval(fetchAccessActivity, 5000);
}

/**
 * Fetch access control activity data from the API
 */
async function fetchAccessActivity() {
    try {
        const response = await fetch(`../../../src/api/time_attendance.php?action=get_access_activity&account=${accountNumber}`);
        
        if (!response.ok) {
            throw new Error(`Server returned ${response.status}`);
        }
        
        const data = await response.json();
        window.handleApiResponse(data);
        
        if (data.success) {
            // Split activities into categories
            const activities = data.activities || [];
            const accessActivities = [];
            const errorEntries = [];
            
            const currentDate = new Date();
            // Strip time component for date comparison
            const currentDateOnly = new Date(currentDate.getFullYear(), currentDate.getMonth(), currentDate.getDate());
            
            activities.forEach(activity => {
                const activityDate = new Date(activity.date_time);
                
                // Get current date once to ensure consistency
                const now = new Date();
                const currentYear = now.getFullYear();
                const currentMonth = now.getMonth();
                const currentDay = now.getDate();
                
                // Check if it's a future date by directly comparing years, months, and days
                const isFutureDate = (
                    activityDate.getFullYear() > currentYear ||
                    (activityDate.getFullYear() === currentYear && 
                     activityDate.getMonth() > currentMonth) ||
                    (activityDate.getFullYear() === currentYear && 
                     activityDate.getMonth() === currentMonth && 
                     activityDate.getDate() > currentDay)
                );
                
                console.log("Access Activity:", activity.employee_name || "Unknown", "Date:", activity.date_time, 
                           "Year:", activityDate.getFullYear(),
                           "Current Year:", currentYear,
                           "Is Future:", isFutureDate);
                
                // Check if it's a future date (any date beyond today) - these always go to Errors tab
                if (isFutureDate) {
                    // Mark as future date error
                    activity.errorType = 'Future Date';
                    activity.errorDetails = `Event date is in the future: ${activityDate.toLocaleDateString()}`;
                    console.log("Adding access to errors: ", activity);
                    errorEntries.push(activity);
                }
                // All other access events go to access tab regardless of employee name
                else {
                    accessActivities.push(activity);
                }
            });
            
            // Update access events table
            updateAccessActivityTable(accessActivities);
            
            // Add to errors table if needed
            if (errorEntries.length > 0) {
                const errorsTable = document.getElementById('errors-table');
                if (errorsTable) {
                    updateErrorsTable(errorEntries);
                }
            }
        } else {
            console.error('Failed to fetch access activity:', data.message);
        }
    } catch (error) {
        showResponseModal(error.message, 'error');
    }
}

/**
 * Update the access control activity table with new data
 */
function updateAccessActivityTable(activities) {
    if (!activities || activities.length === 0) {
        return;
    }
    
    const table = document.getElementById('recent-access-table');
    let hasNewActivities = false;
    
    // Sort activities by date_time in descending order (newest first)
    activities.sort((a, b) => {
        // First try to sort by ID
        const idA = parseInt(a.access_id || a.id || 0);
        const idB = parseInt(b.access_id || b.id || 0);
        
        if (idA !== idB) {
            return idB - idA; // Descending order (newest first)
        }
        
        // If IDs are the same, use date_time
        return new Date(b.date_time) - new Date(a.date_time);
    });
    
    // Get the maximum ID to track for next update
    const maxId = activities.length > 0 ? 
        Math.max(...activities.map(act => parseInt(act.access_id || act.id || 0))) : 0;
    
    // Check for new activities
    const newActivities = activities.filter(activity => {
        const activityId = parseInt(activity.access_id || activity.id || 0);
        return activityId > lastSeenAccessId;
    });
    
    if (newActivities.length > 0) {
        hasNewActivities = true;
    }
    
    // Build the entire table HTML at once, with newest at top
    let tableHTML = '';
    
    // Limit to 15 most recent activities
    const limitedActivities = activities.slice(0, 15);
    
    // Generate HTML for each activity
    limitedActivities.forEach(activity => {
        const isNew = parseInt(activity.access_id || activity.id || 0) > lastSeenAccessId;
        const newClass = isNew ? 'class="new-activity"' : '';
        const accessType = determineAccessType(activity.major_event_type, activity.minor_event_type);
        
        tableHTML += `<tr ${newClass} data-access-id="${activity.access_id || activity.id || 0}">
            <td>${activity.employee_name || 'Unknown'}</td>
            <td>${activity.card_number || activity.clock_number || 'N/A'}</td>
            <td>${formatDateTime(activity.date_time)}</td>
            <td>${accessType}</td>
            <td>${activity.location || activity.device_name || 'Unknown'}</td>
            <td>${activity.device_id || 'Unknown'}</td>
        </tr>`;
    });
    
    // Replace table content at once
    table.innerHTML = tableHTML;
    
    // Remove highlighting after animation completes
    if (hasNewActivities) {
        setTimeout(() => {
            const newRows = table.querySelectorAll('.new-activity');
            newRows.forEach(row => {
                row.classList.remove('new-activity');
            });
        }, 3000);
        
        // Play notification sound for new activities
        playNotificationSound();
    }
    
    // Update the last seen access ID if we have a higher ID
    if (maxId > lastSeenAccessId) {
        lastSeenAccessId = maxId;
    }
}

/**
 * Determine the access type based on event types
 */
function determineAccessType(majorType, minorType) {
    // Basic mapping for access events
    if (majorType === 1) {
        if (minorType === 1) return 'Card Swipe';
        if (minorType === 2) return 'Door Open';
        if (minorType === 3) return 'Door Closed';
        if (minorType === 4) return 'Access Granted';
        if (minorType === 5) return 'Access Denied';
        return 'Access Event';
    } else {
        return 'Other Event';
    }
}

/**
 * Update the unknown users table with new data
 */
function updateUnknownUsersTable(activities) {
    if (!activities || activities.length === 0) {
        return;
    }
    
    const table = document.getElementById('unknown-users-table');
    let hasNewActivities = false;
    
    // Sort activities by date_time in descending order (newest first)
    activities.sort((a, b) => {
        // First try to sort by ID
        const idA = parseInt(a.attendance_id || a.access_id || a.id || 0);
        const idB = parseInt(b.attendance_id || b.access_id || b.id || 0);
        
        if (idA !== idB) {
            return idB - idA; // Descending order (newest first)
        }
        
        // If IDs are the same, use date_time
        return new Date(b.date_time) - new Date(a.date_time);
    });
    
    // Get the maximum ID to track for next update
    const maxId = activities.length > 0 ? 
        Math.max(...activities.map(act => parseInt(act.attendance_id || act.access_id || act.id || 0))) : 0;
    
    // Check for new activities
    const newActivities = activities.filter(activity => {
        const activityId = parseInt(activity.attendance_id || activity.access_id || activity.id || 0);
        return activityId > lastSeenUnknownId;
    });
    
    if (newActivities.length > 0) {
        hasNewActivities = true;
    }
    
    // Build the entire table HTML at once, with newest at top
    let tableHTML = '';
    
    // Limit to 15 most recent activities
    const limitedActivities = activities.slice(0, 15);
    
    // Generate HTML for each activity
    limitedActivities.forEach(activity => {
        const isNew = parseInt(activity.attendance_id || activity.access_id || activity.id || 0) > lastSeenUnknownId;
        const newClass = isNew ? 'class="new-activity"' : '';
        
        // Determine action type based on available data
        let actionType = 'Unknown';
        if (activity.major_event_type) {
            if (activity.major_event_type === 5 || activity.major_event_type === 6) {
                actionType = determineAction(activity.major_event_type, activity.minor_event_type, activity.verify_status);
            } else {
                actionType = determineAccessType(activity.major_event_type, activity.minor_event_type);
            }
        }
        
        tableHTML += `<tr ${newClass}>
            <td>${activity.clock_number || activity.card_number || 'N/A'}</td>
            <td>${formatDateTime(activity.date_time)}</td>
            <td>${actionType}</td>
            <td>${activity.status || 'Unknown'}</td>
            <td>${activity.location || 'Unknown Location'}</td>
            <td>${activity.device_id || 'Unknown'}</td>
        </tr>`;
    });
    
    // Replace table content at once
    table.innerHTML = tableHTML;
    
    // Remove highlighting after animation completes
    if (hasNewActivities) {
        setTimeout(() => {
            const newRows = table.querySelectorAll('.new-activity');
            newRows.forEach(row => {
                row.classList.remove('new-activity');
            });
        }, 3000);
        
        // Play notification sound for new activities
        playNotificationSound();
    }
    
    // Update the last seen ID
    if (maxId > lastSeenUnknownId) {
        lastSeenUnknownId = maxId;
    }
}

/**
 * Update the errors table with new data
 */
function updateErrorsTable(activities) {
    if (!activities || activities.length === 0) {
        return;
    }
    
    const table = document.getElementById('errors-table');
    let hasNewActivities = false;
    
    // Sort activities by date_time in descending order (newest first)
    activities.sort((a, b) => {
        // First try to sort by ID
        const idA = parseInt(a.attendance_id || a.access_id || a.id || 0);
        const idB = parseInt(b.attendance_id || b.access_id || b.id || 0);
        
        if (idA !== idB) {
            return idB - idA; // Descending order (newest first)
        }
        
        // If IDs are the same, use date_time
        return new Date(b.date_time) - new Date(a.date_time);
    });
    
    // Get the maximum ID to track for next update
    const maxId = activities.length > 0 ? 
        Math.max(...activities.map(act => parseInt(act.attendance_id || act.access_id || act.id || 0))) : 0;
    
    // Check for new activities
    const newActivities = activities.filter(activity => {
        const activityId = parseInt(activity.attendance_id || activity.access_id || activity.id || 0);
        return activityId > lastSeenErrorId;
    });
    
    if (newActivities.length > 0) {
        hasNewActivities = true;
    }
    
    // Build the entire table HTML at once, with newest at top
    let tableHTML = '';
    
    // Limit to 15 most recent activities
    const limitedActivities = activities.slice(0, 15);
    
    // Generate HTML for each activity
    limitedActivities.forEach(activity => {
        const isNew = parseInt(activity.attendance_id || activity.access_id || activity.id || 0) > lastSeenErrorId;
        const newClass = isNew ? 'class="new-activity"' : '';
        
        tableHTML += `<tr ${newClass}>
            <td>${activity.employee_name || 'Unknown'}</td>
            <td>${activity.clock_number || activity.card_number || 'N/A'}</td>
            <td>${formatDateTime(activity.date_time)}</td>
            <td>${activity.errorType || 'Unknown Error'}</td>
            <td>${activity.errorDetails || 'No details available'}</td>
            <td>${activity.device_id || 'Unknown'}</td>
        </tr>`;
    });
    
    // Replace table content at once
    table.innerHTML = tableHTML;
    
    // Remove highlighting after animation completes
    if (hasNewActivities) {
        setTimeout(() => {
            const newRows = table.querySelectorAll('.new-activity');
            newRows.forEach(row => {
                row.classList.remove('new-activity');
            });
        }, 3000);
        
        // Play notification sound for new activities
        playNotificationSound();
    }
    
    // Update the last seen ID
    if (maxId > lastSeenErrorId) {
        lastSeenErrorId = maxId;
    }
}

// Add CSS for highlighting new activities
const style = document.createElement('style');
style.textContent = `
    @keyframes highlight-new {
        0% { background-color: rgba(76, 175, 80, 0.3); }
        100% { background-color: transparent; }
    }
    
    .new-activity {
        animation: highlight-new 5s ease-out;
    }
`;
document.head.appendChild(style);