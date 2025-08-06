window.callIfExists = function(functionName, ...args) {
    if (typeof window[functionName] === 'function') {
        window[functionName](...args);
    } else {
        // Optionally: do nothing or show a generic warning
        // alert(functionName + ' is not available on this page.');
    }
};

/**
 * Auto-inject response modal if not present on the page
 * This ensures the error handling modal is available on ALL pages
 */
function ensureResponseModalExists() {
    console.log('🔧 ensureResponseModalExists called');
    const existingModal = document.getElementById('modalResponse');
    if (existingModal) {
        console.log('✅ Modal already exists:', existingModal);
        return; // Modal already exists
    }
    console.log('⚠️ Modal does not exist, creating new one...');

    const modalHTML = `
        <div id="modalResponse" class="custom-modal hidden">
            <div class="custom-modal-content">
                <div class="custom-modal-header">
                    <span id="modalResponseIcon" class="custom-modal-icon">✔</span>
                    <h2 id="modalResponseTitle">Success</h2>
                    <button class="custom-modal-close-x" onclick="closeResponseModal()" aria-label="Close">&times;</button>
                </div>
                <div class="custom-modal-body">
                    <p id="modalResponseMessage">Your request was successful!</p>
                    <div id="modalResponseDetails" class="custom-modal-details hidden"></div>
                </div>
                <div class="custom-modal-footer">
                    <button onclick="closeResponseModal()" class="custom-modal-close-btn">OK</button>
                </div>
            </div>
        </div>
    `;

    // Inject CSS if not present
    if (!document.getElementById('response-modal-styles')) {
        const styles = document.createElement('style');
        styles.id = 'response-modal-styles';
        styles.textContent = `
            .custom-modal {
                position: fixed;
                top: 0;
                left: 0;
                width: 100vw;
                height: 100vh;
                background: rgba(0, 0, 0, 0.6);
                display: flex;
                align-items: center;
                justify-content: center;
                z-index: 2147483647;
                pointer-events: auto;
                backdrop-filter: blur(2px);
                transition: opacity 0.3s ease-in-out;
            }
            
            .custom-modal.hidden {
                display: none !important;
            }
            
            .custom-modal-content {
                background: #ffffff;
                color: #333333;
                padding: 24px;
                border-radius: 8px;
                box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
                text-align: center;
                width: 90%;
                max-width: 450px;
                min-width: 300px;
                animation: slide-down 0.3s ease-in-out;
                position: relative;
                z-index: 2147483648;
                border: 1px solid #e0e0e0;
            }
            
            .custom-modal-header {
                display: flex;
                align-items: center;
                justify-content: center;
                flex-direction: column;
                margin-bottom: 16px;
                position: relative;
                padding-bottom: 8px;
                border-bottom: 1px solid #e0e0e0;
            }
            
            .custom-modal-close-x {
                position: absolute;
                top: -8px;
                right: -8px;
                background: none;
                border: none;
                font-size: 24px;
                color: #666;
                cursor: pointer;
                width: 32px;
                height: 32px;
                border-radius: 50%;
                display: flex;
                align-items: center;
                justify-content: center;
                transition: all 0.2s ease;
            }
            
            .custom-modal-close-x:hover {
                background: #f0f0f0;
                color: #000;
                transform: scale(1.1);
            }
            
            .custom-modal-icon {
                font-size: 3rem;
                font-weight: bold;
                margin-bottom: 8px;
                display: block;
            }
            
            .custom-modal-icon.success { color: #28a745; }
            .custom-modal-icon.error { color: #dc3545; }
            .custom-modal-icon.warning { color: #ffc107; }
            .custom-modal-icon.info { color: #17a2b8; }
            
            .custom-modal-header h2 {
                margin: 0;
                font-size: 1.25rem;
                font-weight: 600;
            }
            
            .custom-modal-body p {
                font-size: 1rem;
                line-height: 1.5;
                margin: 8px 0;
                text-align: left;
                word-wrap: break-word;
            }
            
            .custom-modal-details {
                margin-top: 16px;
                padding: 8px;
                background: #f8f9fa;
                border-radius: 4px;
                border-left: 3px solid #17a2b8;
                text-align: left;
                font-size: 0.875rem;
                font-family: monospace;
                white-space: pre-wrap;
                word-wrap: break-word;
                max-height: 200px;
                overflow-y: auto;
            }
            
            .custom-modal-details.hidden {
                display: none;
            }
            
            .custom-modal-footer {
                margin-top: 16px;
                display: flex;
                justify-content: center;
                gap: 12px;
                padding-top: 8px;
                border-top: 1px solid #e0e0e0;
            }
            
            .custom-modal-close-btn {
                background: #007bff;
                color: #ffffff;
                border: none;
                padding: 8px 16px;
                font-size: 1rem;
                border-radius: 4px;
                cursor: pointer;
                transition: all 0.2s ease;
                min-width: 80px;
                font-weight: 500;
            }
            
            .custom-modal-close-btn:hover {
                background: #0056b3;
                transform: translateY(-1px);
                box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            }
            
            .custom-modal-close-btn:active {
                transform: translateY(0);
            }
            
            @keyframes slide-down {
                from {
                    transform: translateY(-30px) scale(0.95);
                    opacity: 0;
                }
                to {
                    transform: translateY(0) scale(1);
                    opacity: 1;
                }
            }
            
            @media (prefers-color-scheme: dark) {
                .custom-modal-content {
                    background: #2d3748;
                    color: #ffffff;
                    border-color: #4a5568;
                }
                
                .custom-modal-details {
                    background: #1a202c;
                    color: #e2e8f0;
                }
            }
            
            @media (max-width: 480px) {
                .custom-modal-content {
                    width: 95%;
                    padding: 16px;
                }
                
                .custom-modal-icon {
                    font-size: 2.5rem;
                }
                
                .custom-modal-header h2 {
                    font-size: 1rem;
                }
            }
        `;
        document.head.appendChild(styles);
    }

    // Inject HTML
    console.log('📦 Injecting modal HTML into DOM...');
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    const modalElement = modalContainer.firstElementChild;
    console.log('📦 Created modal element:', modalElement);
    document.body.appendChild(modalElement);
    console.log('✅ Modal injected into DOM');
    
    // Verify injection
    const verifyModal = document.getElementById('modalResponse');
    console.log('🔍 Verification - modal found after injection:', verifyModal);
    console.log('🔍 Verification - modal parent:', verifyModal ? verifyModal.parentNode : 'null');
}

/**
 * Enhanced response modal function - Available globally
 * @param {string} message - Message to display
 * @param {string} type - Type: 'success', 'error', 'warning', 'info'
 * @param {boolean} persistent - If true, modal won't auto-close
 * @param {boolean} confirm - If true, shows Yes/No buttons
 * @param {object} details - Additional technical details (for development)
 * @returns {Promise} - For confirmation dialogs
 */
function showResponseModal(message, type = 'info', persistent = false, confirm = false, details = null) {
    console.log('🚀 showResponseModal called:', { message, type, persistent, confirm, details });
    
    // Ensure modal exists
    ensureResponseModalExists();
    
    const modal = document.getElementById('modalResponse');
    console.log('🔍 Modal element found:', modal);
    console.log('🔍 Modal current classes:', modal ? modal.className : 'null');
    console.log('🔍 Modal current style.display:', modal ? modal.style.display : 'null');
    
    const title = document.getElementById('modalResponseTitle');
    const msg = document.getElementById('modalResponseMessage');
    const icon = document.getElementById('modalResponseIcon');
    const detailsDiv = document.getElementById('modalResponseDetails');
    const footer = modal ? modal.querySelector('.custom-modal-footer') : null;
    
    console.log('🔍 Modal elements found:', {
        modal: !!modal,
        title: !!title,
        msg: !!msg,
        icon: !!icon,
        footer: !!footer
    });
    
    if (!modal || !title || !msg || !icon || !footer) {
        console.error('❌ Response modal elements not found');
        console.log('🔍 Available modal elements in DOM:', {
            allModals: document.querySelectorAll('[id*="modal"], [id*="Modal"]'),
            allResponseElements: document.querySelectorAll('[id*="response"], [id*="Response"]')
        });
        // Fallback to alert if modal not available
        alert(message);
        return Promise.resolve(false);
    }

    // Clear previous state
    while (footer.firstChild) footer.removeChild(footer.firstChild);
    detailsDiv.classList.add('hidden');
    icon.classList.remove('success', 'error', 'warning', 'info');

    // Set content based on type
    const typeConfig = {
        success: { title: 'Success', icon: '✔', class: 'success' },
        error: { title: 'Error', icon: '✖', class: 'error' },
        warning: { title: 'Warning', icon: '⚠', class: 'warning' },
        info: { title: 'Information', icon: 'ℹ', class: 'info' }
    };

    const config = typeConfig[type] || typeConfig.info;
    title.textContent = config.title;
    msg.textContent = message;
    icon.textContent = config.icon;
    icon.classList.add(config.class);

    // Show technical details if provided (development mode)
    if (details && typeof details === 'object') {
        detailsDiv.textContent = JSON.stringify(details, null, 2);
        detailsDiv.classList.remove('hidden');
    }

    // Show modal
    console.log('🎯 About to show modal, removing hidden class...');
    modal.classList.remove('hidden');
    console.log('✅ Modal classes after show:', modal.className);
    console.log('✅ Modal style.display after show:', modal.style.display);
    console.log('✅ Modal offsetHeight after show:', modal.offsetHeight);
    console.log('✅ Modal visibility check:', {
        offsetWidth: modal.offsetWidth,
        offsetHeight: modal.offsetHeight,
        clientHeight: modal.clientHeight,
        getBoundingClientRect: modal.getBoundingClientRect()
    });

    // Handle confirmation dialog
    if (confirm) {
        return new Promise((resolve) => {
            const yesBtn = document.createElement('button');
            yesBtn.textContent = 'Yes';
            yesBtn.className = 'custom-modal-close-btn';
            yesBtn.style.marginRight = '12px';
            yesBtn.style.background = '#28a745';
            
            const noBtn = document.createElement('button');
            noBtn.textContent = 'No';
            noBtn.className = 'custom-modal-close-btn';
            noBtn.style.background = '#dc3545';
            
            footer.appendChild(yesBtn);
            footer.appendChild(noBtn);
            
            yesBtn.onclick = () => {
                modal.classList.add('hidden');
                resolve(true);
            };
            
            noBtn.onclick = () => {
                modal.classList.add('hidden');
                resolve(false);
            };
            
            // Store resolve function for potential external access
            window._modalConfirmResolve = resolve;
        });
    } else {
        // Regular OK button
        const okBtn = document.createElement('button');
        okBtn.textContent = 'OK';
        okBtn.className = 'custom-modal-close-btn';
        okBtn.onclick = () => closeResponseModal();
        footer.appendChild(okBtn);
        
        // Auto-close after delay (except for errors in development)
        if (!persistent && !(type === 'error' && details)) {
            setTimeout(() => {
                if (!modal.classList.contains('hidden')) {
                    modal.classList.add('hidden');
                }
            }, type === 'success' ? 2000 : 3000);
        }
    }

    // Click outside to close (except for confirmations)
    modal.onclick = function(e) {
        if (e.target === modal && !confirm) {
            closeResponseModal();
        }
    };

    // ESC key to close
    const escHandler = function(e) {
        if (e.key === 'Escape' && !confirm) {
            closeResponseModal();
        }
    };
    
    document.addEventListener('keydown', escHandler);
    modal._escHandler = escHandler; // Store reference for cleanup
    
    return Promise.resolve(true);
}

function closeResponseModal() {
    const modal = document.getElementById('modalResponse');
    if (modal) {
        modal.classList.add('hidden');
        
        // Clean up ESC handler
        if (modal._escHandler) {
            document.removeEventListener('keydown', modal._escHandler);
            modal._escHandler = null;
        }
    }
}

/**
 * Enhanced centralized API response handler for fetch/AJAX calls
 * Handles the new consistent error response format from the backend
 * Usage: .then(handleApiResponse)
 * Shows errors in ResponseModal if present, returns data on success
 */
function handleApiResponse(data) {
    console.log('🔄 handleApiResponse called with:', data);
    window.lastApiRawResponse = data;
    
    // Handle null/undefined responses
    if (!data) {
        const errorMsg = 'No response from server. Please check your connection or try again later.';
        console.log('❌ No data received, showing error modal');
        showResponseModal(errorMsg, 'error');
        throw new Error(errorMsg);
    }

    // Handle network/HTTP errors (status codes)
    if (data.status && data.status >= 400) {
        const errorMsg = data.message || data.error || `Server error (${data.status})`;
        showResponseModal(errorMsg, 'error');
        throw new Error(errorMsg);
    }

    // Handle application-level errors (success: false)
    if (data.success === false) {
        console.log('❌ API Error detected (success: false)');
        let errorMsg = data.message || data.error || 'An unknown error occurred.';
        console.log('❌ Error message to show:', errorMsg);
        
        // In development mode, show additional technical details if available
        if (data.technical_error && data.error_code) {
            console.log('🔧 Development mode - showing technical details');
            console.log('🔍 Checking if showResponseModal is available:', typeof showResponseModal);
            console.log('🔍 Checking if window.showResponseModal is available:', typeof window.showResponseModal);
            console.group('🔧 Development Error Details');
            console.error('User Message:', errorMsg);
            console.error('Technical Error:', data.technical_error);
            console.error('Error Code:', data.error_code);
            if (data.form_data) {
                console.error('Form Data:', data.form_data);
            }
            if (data.context) {
                console.error('Context:', data.context);
            }
            console.groupEnd();
            
            // In dev mode, append error code to user message for reference
            errorMsg += ` (${data.error_code})`;
            
            // Show technical details in modal for development
            console.log('🎯 About to call showResponseModal with technical details');
            console.log('🔍 Available functions in window:', Object.keys(window).filter(key => key.includes('Modal') || key.includes('modal')));
            try {
                const modalFunc = window.showResponseModal || showResponseModal;
                console.log('🎯 Using modal function:', modalFunc);
                modalFunc(errorMsg, 'error', true, false, {
                    technical_error: data.technical_error,
                    error_code: data.error_code,
                    form_data: data.form_data,
                    context: data.context
                });
            } catch (e) {
                console.error('❌ Error calling showResponseModal with details:', e);
                alert(errorMsg); // Fallback
            }
        } else {
            console.log('🎯 About to call showResponseModal without technical details');
            try {
                const modalFunc = window.showResponseModal || showResponseModal;
                console.log('🎯 Using modal function:', modalFunc);
                modalFunc(errorMsg, 'error');
            } catch (e) {
                console.error('❌ Error calling showResponseModal:', e);
                alert(errorMsg); // Fallback
            }
        }
        
        throw new Error(errorMsg);
    }

    // Handle success responses
    if (data.success === true) {
        // Show success message if explicitly provided
        if (data.message && data.message.toLowerCase() !== 'success') {
            showResponseModal(data.message, 'success');
        }
        return data;
    }

    // For responses without explicit success field, assume success if no error indicators
    if (!data.error && !data.message) {
        return data;
    }

    // Fallback: treat as success if we reach here
    return data;
}

/**
 * Enhanced global error handler for JavaScript errors
 * Sends errors to the backend AI error handler for consistent processing
 */
window.onerror = async function(message, source, lineno, colno, error) {
    const errorCode = Math.random().toString(36).substring(2, 8).toUpperCase();
    
    console.group('🚨 JavaScript Error Caught');
    console.error('Message:', message);
    console.error('Source:', source);
    console.error('Line:Column:', `${lineno}:${colno}`);
    if (error && error.stack) {
        console.error('Stack:', error.stack);
    }
    console.error('Error Code:', errorCode);
    console.groupEnd();
    
    try {
        const response = await fetch('/public/php/log-js-error.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                message, 
                source, 
                lineno, 
                colno, 
                stack: error?.stack, 
                code: errorCode,
                url: window.location.href,
                userAgent: navigator.userAgent,
                timestamp: new Date().toISOString()
            })
        });
        
        const data = await response.json();
        
        if (data && data.error) {
            showResponseModal(data.error, 'error');
        } else if (data && data.message) {
            showResponseModal(data.message, 'error');
        } else {
            showResponseModal(`Something went wrong. Please contact support. Error Code: ${errorCode}`, 'error');
        }
    } catch (fetchError) {
        console.error('Failed to log error:', fetchError);
        showResponseModal(`Something went wrong. Please contact support. Error Code: ${errorCode}`, 'error');
    }
};

/**
 * Handle unhandled promise rejections
 */
window.addEventListener('unhandledrejection', function(event) {
    console.error('Unhandled promise rejection:', event.reason);
    
    // Try to extract a meaningful error message
    let errorMsg = 'An unexpected error occurred.';
    if (event.reason && typeof event.reason === 'string') {
        errorMsg = event.reason;
    } else if (event.reason && event.reason.message) {
        errorMsg = event.reason.message;
    }
    
    showResponseModal(errorMsg, 'error');
});

// Make functions globally available
console.log('🌐 Setting global functions...');
window.showResponseModal = showResponseModal;
window.closeResponseModal = closeResponseModal;
window.handleApiResponse = handleApiResponse;

// Add loading modal functions to global scope if they exist
if (typeof showLoadingModal !== 'undefined') {
    window.showLoadingModal = showLoadingModal;
}
if (typeof hideLoadingModal !== 'undefined') {
    window.hideLoadingModal = hideLoadingModal;
}
console.log('✅ Global functions set:', {
    'window.showResponseModal': typeof window.showResponseModal,
    'window.closeResponseModal': typeof window.closeResponseModal,
    'window.handleApiResponse': typeof window.handleApiResponse
});

document.addEventListener('DOMContentLoaded', function () {
    const bodyId = document.body.id;

    // Get current page path
    const currentPath = window.location.pathname;

    // Check if user is logged in as technician
    const isTechnician = document.body.classList.contains('technician-mode') || 
                        (typeof isTechnicianUser !== 'undefined' && isTechnicianUser === true);

    // Sidebar configurations
    const sidebarConfig = {
        "dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/views/settings.php", icon: "settings", text: "Settings" },
            { href: "/public/views/export.php", icon: "upload", text: "Exporting" },
            { href: "/public/views/importing.php", icon: "download", text: "Importing" },
            { href: isTechnician ? "/public/admin/techlogin.php" : "/public/php/logout.php", icon: "exit_to_app", text: isTechnician ? "Back to Tech Portal" : "LogOut" },
            { href: "/public/views/devices.php", icon: "devices", text: "Devices" },
        ],
        "invoice-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/modules/invoice/views/invoice-documents.php", icon: "description", text: "Documents" },
            { href: "/modules/invoice/views/invoice-products.php", icon: "inventory_2", text: "Products" },
            { href: "/modules/invoice/views/invoice-clients.php", icon: "people", text: "Clients" },
            { href: "/modules/invoice/views/invoice-payments.php", icon: "payment", text: "Payments" },
            { href: "/modules/invoice/views/invoice-reports.php", icon: "bar_chart", text: "Reports" },
            { href: "/modules/invoice/views/invoice-setup.php", icon: "build", text: "Setup" },
            { href: "/modules/invoice/views/sales-reps.php", icon: "group", text: "Sales Reps" }
        ],
        "settings": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "#preferences-settings", icon: "settings", text: "Preferences", onclick: "activateSection('preferences-settings')" },
            { href: "#time-attendance-settings", icon: "schedule", text: "Time & Attendance", onclick: "activateSection('time-attendance-settings')" },
            { href: "#invoicing-settings", icon: "receipt", text: "Invoicing & Billing", onclick: "activateSection('invoicing-settings')" },
            { href: "#payroll-settings", icon: "payment", text: "Payroll", onclick: "activateSection('payroll-settings')" },
            { href: "#inventory-settings", icon: "inventory_2", text: "Inventory Management", onclick: "activateSection('inventory-settings')" },
            { href: "#crm-settings", icon: "people", text: "CRM", onclick: "activateSection('crm-settings')" },
            { href: "#project-management-settings", icon: "assignment", text: "Project Management", onclick: "activateSection('project-management-settings')" },
            { href: "#accounting-settings", icon: "account_balance", text: "Accounting", onclick: "activateSection('accounting-settings')" },
            { href: "#hr-settings", icon: "group", text: "HR Management", onclick: "activateSection('hr-settings')" },
            { href: "#support-settings", icon: "support_agent", text: "Support Module", onclick: "activateSection('support-settings')" },
            { href: "#fleet-settings", icon: "directions_car", text: "Fleet Management", onclick: "activateSection('fleet-settings')" },
            { href: "#asset-settings", icon: "business_center", text: "Asset Management", onclick: "activateSection('asset-settings')" },
            { href: "#access-control-settings", icon: "lock", text: "Access Control", onclick: "activateSection('access-control-settings')" }
        ],
        "invoice-clients": [
            { href: "/modules/invoice/views/invoice-dashboard.php", icon: "dashboard", text: "Dashboard" },
            { href: "#", icon: "person_add", text: "Add Private Client", onclick: "callIfExists('showAddCustomerModal')" },
            { href: "#", icon: "person_add", text: "Add Business Client", onclick: "callIfExists('showAddCompanyModal')" },
            { href: "/modules/invoice/views/invoice-payments.php", icon: "payment", text: "Payment Reminder" }
        ],
        "invoice-products": [
            { href: "/modules/invoice/views/invoice-dashboard.php", icon: "dashboard", text: "Dashboard"},
            { href: "#", icon: "category", text: "Products", tab: "products", class: "sidebar-button" },
            { href: "#", icon: "build", text: "Parts", tab: "parts", class: "sidebar-button" },
            { href: "#", icon: "directions_car", text: "Vehicles", tab: "vehicles", class: "sidebar-button" },
            { href: "#", icon: "add_circle_outline", text: "Extras", tab: "extras", class: "sidebar-button" },
            { href: "#", icon: "remove_circle_outline", text: "Services", tab: "services", class: "sidebar-button" },
            { href: "#", icon: "block", text: "Discontinued", tab: "discontinued", class: "sidebar-button" },
            { href: "#", icon: "do_not_disturb_on", text: "Disabled", tab: "disabled", class: "sidebar-button" }
        ],
        "payments":[
            { href: "/modules/invoice/views/invoice-dashboard.php", icon: "dashboard", text: "Dashboard" },
            { href: "#", icon: "add_circle", text: "Add Payment", onclick: "callIfExists('openPaymentModal', 'create', null)" },
            { href: "#", icon: "receipt_long", text: "Add Credit Note", onclick: "callIfExists('openDocumentModal', 'create', null, 'credit-note')" },
            { href: "#", icon: "money_off", text: "Add Refund", onclick: "callIfExists('openDocumentModal', 'create', null, 'refund')" },
            { href: "#", icon: "history", text: "Payment History", onclick: "callIfExists('showPaymentHistory')" },
        ],
        "sales-reps":[
            { href: "/modules/invoice/views/invoice-dashboard.php", icon: "dashboard", text: "Dashboard" },
            { href: "#", icon: "person_add", text: "Add Sales Rep", onclick: "openAddSalesRepModal()" }
        ],
        "invoice-reports":[
            { href: "/modules/invoice/views/invoice-dashboard.php", icon: "dashboard", text: "Dashboard" },
            { href: "#sales-reports", icon: "bar_chart", text: "Sales Reports", onclick: "activateSection('sales-reports')" },
            { href: "#tax-reports", icon: "receipt", text: "Tax Reports", onclick: "activateSection('tax-reports')" },
            { href: "#income-reports", icon: "attach_money", text: "Income Reports", onclick: "activateSection('income-reports')" },
            { href: "#expenses-reports", icon: "money_off", text: "Expenses Reports", onclick: "activateSection('expenses-reports')" },
            { href: "#general-reports", icon: "assessment", text: "General Reports", onclick: "activateSection('general-reports')" }
        ],
        "accounting-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/accounting/pages/general-ledger.php", icon: "book", text: "General Ledger" },
            { href: "/public/modules/accounting/pages/chart-of-accounts.php", icon: "account_tree", text: "Chart of Accounts" },
            { href: "/public/modules/accounting/pages/trial-balance.php", icon: "balance", text: "Trial Balance" },
            { href: "/public/modules/accounting/pages/profit-loss-report.php", icon: "bar_chart", text: "Profit & Loss Report" },
            { href: "/public/modules/accounting/pages/balance-sheet.php", icon: "assessment", text: "Balance Sheet" },
            { href: "/public/modules/accounting/pages/cash-flow-statement.php", icon: "show_chart", text: "Cash Flow" },
            { href: "/public/modules/accounting/pages/reconciliation.php", icon: "sync", text: "Reconciliation" },
            { href: "/public/modules/accounting/pages/journal-entries.php", icon: "edit", text: "Journal Entries" }
        ],
        "documents": [
            { href: "/modules/invoice/views/invoice-dashboard.php", icon: "dashboard", text: "Dashboard", },
            { href: "#", icon: "add_circle", text: "Create Document", onclick: "callIfExists('openDocumentModal', 'create')" },
        ],
        "invoice-setup": [
            { href: "/modules/invoice/views/invoice-dashboard.php", icon: "dashboard", text: "Dashboard" },
            { href: "#", icon: "inventory", text: "Product Setup", tab: "products", class: "tab", onclick: "callIfExists('switchTab', 'products')" },
            { href: "#", icon: "account_balance", text: "Bank & Company", tab: "banking", class: "tab", onclick: "callIfExists('switchTab', 'banking')" },
            { href: "#", icon: "trending_up", text: "Sales Configuration", tab: "sales", class: "tab", onclick: "callIfExists('switchTab', 'sales')" },
            { href: "#", icon: "business", text: "Suppliers", tab: "suppliers", class: "tab", onclick: "callIfExists('switchTab', 'suppliers')" },
            { href: "#", icon: "receipt_long", text: "Credit Notes", tab: "credit", class: "tab", onclick: "callIfExists('switchTab', 'credit')" },
            { href: "#", icon: "format_list_numbered", text: "Invoice Numbering", tab: "numbering", class: "tab", onclick: "callIfExists('switchTab', 'numbering')" },
            { href: "#", icon: "description", text: "Terms & Footer", tab: "terms", class: "tab", onclick: "callIfExists('switchTab', 'terms')" }
        ],
        "TandA": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/modules/time_and_attendance/views/employees.php", icon: "people", text: "Employees" },
            { href: "/modules/time_and_attendance/views/timecards.php", icon: "access_time", text: "Timecards" },
            { href: "/modules/time_and_attendance/views/mobile-clocking.php", icon: "phone_android", text: "Mobile Clocking" },
            { href: "/modules/time_and_attendance/views/reports.php", icon: "bar_chart", text: "Reports" },
            { href: "/modules/time_and_attendance/views/devices.php", icon: "devices", text: "Devices" },
            { href: "/modules/time_and_attendance/views/schedules.php", icon: "calendar_today", text: "Schedules" }
        ],
        "timecards": [
            { href: "/modules/time_and_attendance/views/dashboard-TA.php", icon: "dashboard", text: "Dashboard", },
            { href: "/modules/time_and_attendance/views/employees.php", icon: "people", text: "Employees" },
            { href: "/modules/time_and_attendance/views/timecards.php", icon: "access_time", text: "Timecards", active: true },
            { href: "/modules/time_and_attendance/views/mobile-clocking.php", icon: "phone_android", text: "Mobile Clocking" },
            { href: "/modules/time_and_attendance/views/reports.php", icon: "bar_chart", text: "Reports" },
            { href: "/modules/time_and_attendance/views/devices.php", icon: "devices", text: "Devices" },
            { href: "/modules/time_and_attendance/views/schedules.php", icon: "calendar_today", text: "Schedules" }
        ],
        "schedules": [
            { href: "/modules/time_and_attendance/views/dashboard-TA.php", icon: "dashboard", text: "Dashboard", },
            { href: "/modules/time_and_attendance/views/employees.php", icon: "people", text: "Employees" },
            { href: "/modules/time_and_attendance/views/timecards.php", icon: "access_time", text: "Timecards" },
            { href: "/modules/time_and_attendance/views/mobile-clocking.php", icon: "phone_android", text: "Mobile Clocking" },
            { href: "/modules/time_and_attendance/views/reports.php", icon: "bar_chart", text: "Reports" },
            { href: "/modules/time_and_attendance/views/devices.php", icon: "devices", text: "Devices" },
            { href: "/modules/time_and_attendance/views/schedules.php", icon: "calendar_today", text: "Schedules", active: true }
        ],
        "TA-employees": [
            { href: "/modules/time_and_attendance/views/dashboard-TA.php", icon: "dashboard", text: "Dashboard", },
            { href: "#", icon: "person_add", text: "Add Employee", onclick: "openAddEmployeeModal()" },
            { href: "/modules/time_and_attendance/views/import-employees.php", icon: "upload_file", text: "Import Employees" }
        ],
        "hr-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/hr/pages/employees.php", icon: "people", text: "Employees" },
            { href: "/public/modules/hr/pages/recruitment.php", icon: "person_add", text: "Recruitment" },
            { href: "/public/modules/hr/pages/performance.php", icon: "assessment", text: "Performance" },
            { href: "/public/modules/hr/pages/training.php", icon: "school", text: "Training" },
            { href: "/public/modules/hr/pages/documents.php", icon: "description", text: "Documents" },
            { href: "/public/modules/hr/pages/benefits.php", icon: "health_and_safety", text: "Benefits" },
            { href: "/public/modules/hr/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "project-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/project/pages/projects.php", icon: "assignment", text: "Projects" },
            { href: "/public/modules/project/pages/tasks.php", icon: "task", text: "Tasks" },
            { href: "/public/modules/project/pages/teams.php", icon: "groups", text: "Teams" },
            { href: "/public/modules/project/pages/timeline.php", icon: "timeline", text: "Timeline" },
            { href: "/public/modules/project/pages/resources.php", icon: "build", text: "Resources" },
            { href: "/public/modules/project/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "inventory-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/inventory/pages/items.php", icon: "inventory_2", text: "Items" },
            { href: "/public/modules/inventory/pages/stock.php", icon: "store", text: "Stock" },
            { href: "/public/modules/inventory/pages/suppliers.php", icon: "local_shipping", text: "Suppliers" },
            { href: "/public/modules/inventory/pages/orders.php", icon: "shopping_cart", text: "Orders" },
            { href: "/public/modules/inventory/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "crm-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/crm/pages/contacts.php", icon: "contacts", text: "Contacts" },
            { href: "/public/modules/crm/pages/leads.php", icon: "trending_up", text: "Leads" },
            { href: "/public/modules/crm/pages/opportunities.php", icon: "lightbulb", text: "Opportunities" },
            { href: "/public/modules/crm/pages/campaigns.php", icon: "campaign", text: "Campaigns" },
            { href: "/public/modules/crm/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "support-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/support/pages/tickets.php", icon: "confirmation_number", text: "Tickets" },
            { href: "/public/modules/support/pages/knowledge.php", icon: "menu_book", text: "Knowledge Base" },
            { href: "/public/modules/support/pages/faq.php", icon: "help", text: "FAQ" },
            { href: "/public/modules/support/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "fleet-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/fleet/pages/vehicles.php", icon: "directions_car", text: "Vehicles" },
            { href: "/public/modules/fleet/pages/maintenance.php", icon: "build", text: "Maintenance" },
            { href: "/public/modules/fleet/pages/drivers.php", icon: "person", text: "Drivers" },
            { href: "/public/modules/fleet/pages/trips.php", icon: "map", text: "Trips" },
            { href: "/public/modules/fleet/pages/fuel.php", icon: "local_gas_station", text: "Fuel Log" },
            { href: "/public/modules/fleet/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "asset-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/asset/pages/assets.php", icon: "business_center", text: "Assets" },
            { href: "/public/modules/asset/pages/maintenance.php", icon: "build", text: "Maintenance" },
            { href: "/public/modules/asset/pages/depreciation.php", icon: "trending_down", text: "Depreciation" },
            { href: "/public/modules/asset/pages/licenses.php", icon: "vpn_key", text: "Licenses" },
            { href: "/public/modules/asset/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "access-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/access/pages/users.php", icon: "people", text: "Users" },
            { href: "/public/modules/access/pages/roles.php", icon: "admin_panel_settings", text: "Roles" },
            { href: "/public/modules/access/pages/permissions.php", icon: "security", text: "Permissions" },
            { href: "/public/modules/access/pages/logs.php", icon: "history", text: "Access Logs" },
            { href: "/public/modules/access/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "payroll-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/payroll/pages/salaries.php", icon: "payments", text: "Salaries" },
            { href: "/public/modules/payroll/pages/deductions.php", icon: "remove_circle", text: "Deductions" },
            { href: "/public/modules/payroll/pages/benefits.php", icon: "add_circle", text: "Benefits" },
            { href: "/public/modules/payroll/pages/taxes.php", icon: "receipt", text: "Taxes" },
            { href: "/public/modules/payroll/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "mobile-dashboard": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "/public/modules/mobile/pages/settings.php", icon: "settings", text: "Settings" },
            { href: "/public/modules/mobile/pages/users.php", icon: "people", text: "Users" },
            { href: "/public/modules/mobile/pages/notifications.php", icon: "notifications", text: "Notifications" },
            { href: "/public/modules/mobile/pages/sync.php", icon: "sync", text: "Sync" },
            { href: "/public/modules/mobile/pages/reports.php", icon: "bar_chart", text: "Reports" }
        ],
        "importing": [
            { href: "/public/views/dashboard.php", icon: "home", text: "Home" },
            { href: "#time_and_attendance", icon: "schedule", text: "Time & Attendance", onclick: "activateSection('time_and_attendance')" },
            { href: "#accounting", icon: "account_balance", text: "Accounting", onclick: "activateSection('accounting')" },
            { href: "#payroll", icon: "payments", text: "Payroll Management", onclick: "activateSection('payroll')" },
            { href: "#access", icon: "security", text: "Access Control", onclick: "activateSection('access')" },
            { href: "#asset", icon: "inventory", text: "Asset Management", onclick: "activateSection('asset')" },
            { href: "#fleet", icon: "directions_car", text: "Fleet Management", onclick: "activateSection('fleet')" },
            { href: "#support", icon: "support_agent", text: "Support/Help Desk", onclick: "activateSection('support')" },
            { href: "#crm", icon: "people", text: "Customer Relationship", onclick: "activateSection('crm')" },
            { href: "#inventory", icon: "inventory_2", text: "Inventory Management", onclick: "activateSection('inventory')" },
            { href: "#project", icon: "assignment", text: "Project Management", onclick: "activateSection('project')" },
            { href: "#hr", icon: "person", text: "Human Resources", onclick: "activateSection('hr')" },
            { href: "#invoice", icon: "receipt", text: "Invoice Management", onclick: "activateSection('invoice')" }
        ]
    };
    

    /**
     * Initialize sidebar based on the current body ID
     */
    function initializeSidebar() {
        const sidebarItems = sidebarConfig[bodyId] || [];
        const sidebar = document.querySelector('.modular-nav-items');

        if (!sidebar) return;

        // Check if tutorial is completed
        const isTutorialCompleted = localStorage.getItem(`tutorial-done-${bodyId}`);
        if (isTutorialCompleted) {
            document.body.setAttribute('data-tutorial-completed', 'true');
        }

        // Clear existing items
        sidebar.innerHTML = '';

        // Add items from configuration
        sidebarItems.forEach(item => {
            const li = document.createElement('li');
            const onclick = item.onclick ? `onclick="${item.onclick}"` : '';

            li.innerHTML = `
                <a href="${item.href}" ${onclick} class="${item.tab ? 'tab' : ''}" ${item.tab ? `data-tab="${item.tab}"` : ''}>
                    <i class="material-icons">${item.icon}</i>
                    <span class="nav-text">${item.text}</span>
                </a>
            `;
            sidebar.appendChild(li);
        });
    }

    /**
     * Setup tab switching for pages with tabs (like invoice-products)
     */
    function setupTabs() {
        if (!sidebarConfig[bodyId]?.some(item => item.tab)) return;

        document.querySelectorAll('.tab').forEach(tabButton => {
            tabButton.addEventListener('click', function (event) {
                event.preventDefault();
                const tab = this.getAttribute('data-tab');

                // Remove active class from all tabs and tab contents
                document.querySelectorAll('.tab').forEach(btn => btn.classList.remove('active'));
                document.querySelectorAll('.tab-content').forEach(content => content.classList.remove('active'));

                // Add active class to the clicked tab and corresponding tab content
                this.classList.add('active');
                const tabContent = document.getElementById(tab);
                if (tabContent) tabContent.classList.add('active');
            });
        });
    }
    /**
     * Sidebar toggle functionality
     */
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebar = document.getElementById('sidebar');
    // Configuration object for toggling classes
    const toggleConfig = {
        dashboard: {
            targetId: 'mainContent',
            toggleClasses: ['collapsed']
        },
        'invoice-dashboard': {
            targetId: 'main-content',
            toggleClasses: ['collapsed']
        },
        settings: {
            targetId: 'settings-container',
            toggleClasses: ['collapsed']
        },
        'invoice-clients': {
            targetId: 'clients-screen',
            toggleClasses: ['collapsed']
        },
        'invoice-products': {
            targetId: 'products-container',
            toggleClasses: ['collapsed']
        },
        'payments':{
            targetId: 'payments-container',
            toggleClasses: ['collapsed']
        },
        'sales-reps':{
            targetId: 'sales-reps-container',
            toggleClasses: ['collapsed']
        },
        'documents':{
            targetId: 'screen-container',
            toggleClasses: ['collapsed']
        },
        'TandA':{
            targetId: '.dashboard-container',
            toggleClasses: ['collapsed']
        },
        'TA-employees':{
            targetId: '.dashboard-container',
            toggleClasses: ['collapsed']
        },
        'invoice-setup':{
            targetId: '.container',
            toggleClasses: ['collapsed']
        },
        'schedules': {
            targetId: 'schedule-container',
            toggleClasses: ['collapsed']
        },
        'importing': {
            targetId: 'import-container',
            toggleClasses: ['collapsed']
        }
    };

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            // Toggle the sidebar class
            sidebar?.classList.toggle('collapsed');

            // Check if a configuration exists for the current body ID
            if (toggleConfig[bodyId]) {
                const { targetId, toggleClasses } = toggleConfig[bodyId];
                const targetElement = document.getElementById(targetId);

                if (targetElement) {
                    // Toggle all classes defined in the configuration
                    toggleClasses.forEach(className => {
                        targetElement.classList.toggle(className);
                    });
                }
            }
        });
    }

    /**
     * Session exit button logic
     */
    const exitButton = document.getElementById('exit-button');
    if (exitButton) {
        exitButton.addEventListener('click', function (event) {
            event.preventDefault();
            fetch('/src/api/session/status.php')
                .then(response => response.json())
                .then(data => {
                    if (data.tech_logged_in) {
                        window.location.href = '/public/admin/techlogin.php';
                    } else if (data.user_logged_in) {
                        window.location.href = '/public/index.php';
                    }
                })
                .catch(error => console.error('Error fetching session status:', error));
        });
    }

    // Initialize sidebar and page-specific logic
    initializeSidebar();
    setupTabs();

    // Initialize notification system
    initializeNotifications();

});

function checkMultipleAccounts() {
    if (multipleAccounts) {
        window.location.href = "/public/account/choose-account.php"; // Redirect if session variable is set
    }
}

/**
 * Notification System
 */
function initializeNotifications() {
    try {
        // Wrap the initialization in try-catch
        updateNotificationCount().catch(error => {
            console.warn('Failed to initialize notifications:', error);
        });
        const notificationBell = document.getElementById('notification-bell');
        const notificationsModal = document.getElementById('notifications-modal');
        const closeNotificationsBtn = document.querySelector('.close-notifications');
        const markAllReadBtn = document.getElementById('mark-all-read');
        const loadMoreBtn = document.getElementById('load-more-notifications');
        const tabButtons = document.querySelectorAll('.tab-button');
        
        // Current state variables
        let currentTab = 'all';
        let currentPage = 1;
        const notificationsPerPage = 10;
        
        // Toggle notifications modal when bell is clicked
        if (notificationBell) {
            notificationBell.addEventListener('click', () => {
                notificationsModal.classList.remove('hidden');
                notificationsModal.classList.add('visible');
                
                // Load notifications if this is the first open
                if (document.querySelector('.no-notifications')) {
                    loadNotifications(currentTab, currentPage, true);
                }
            });
        }
        
        // Close notifications modal when close button is clicked
        if (closeNotificationsBtn) {
            closeNotificationsBtn.addEventListener('click', () => {
                notificationsModal.classList.remove('visible');
                notificationsModal.classList.add('hidden');
            });
        }
        
        // Handle tab switching
        tabButtons.forEach(button => {
            button.addEventListener('click', () => {
                // Remove active class from all tabs
                tabButtons.forEach(btn => btn.classList.remove('active'));
                
                // Add active class to clicked tab
                button.classList.add('active');
                
                // Update current tab and reload notifications
                currentTab = button.getAttribute('data-tab');
                currentPage = 1;
                loadNotifications(currentTab, currentPage, true);
            });
        });
        
        // Handle mark all as read
        if (markAllReadBtn) {
            markAllReadBtn.addEventListener('click', () => {
                markAllNotificationsAsRead();
            });
        }
        
        // Handle load more
        if (loadMoreBtn) {
            loadMoreBtn.addEventListener('click', () => {
                currentPage++;
                loadNotifications(currentTab, currentPage, false);
            });
        }
    } catch (error) {
        console.warn('Error in notification initialization:', error);
    }
}

/**
 * Load notifications based on tab and page
 * @param {string} tab - The notification tab to load
 * @param {number} page - The page number to load
 * @param {boolean} reset - Whether to reset the list or append
 */
function loadNotifications(tab, page, reset) {
    const notificationsList = document.getElementById('notifications-list');
    
    // Show loading state
    if (reset) {
        notificationsList.innerHTML = '<div class="notification-loading">Loading...</div>';
    } else {
        // Add loading indicator at the end
        const loadingDiv = document.createElement('div');
        loadingDiv.className = 'notification-loading';
        loadingDiv.textContent = 'Loading...';
        notificationsList.appendChild(loadingDiv);
    }
    
    // Fetch notifications from the server
    fetch(`/src/api/notifications.php?tab=${tab}&page=${page}`)
        .then(response => response.json())
        .then(data => {
            // Remove loading indicators
            const loadingElements = notificationsList.querySelectorAll('.notification-loading');
            loadingElements.forEach(el => el.remove());
            
            if (reset) {
                notificationsList.innerHTML = '';
            }
            
            if (data.notifications && data.notifications.length > 0) {
                // Create and append notification items
                data.notifications.forEach(notification => {
                    const notificationElement = createNotificationItem(notification);
                    notificationsList.appendChild(notificationElement);
                });
                
                // Hide load more button if no more notifications
                const loadMoreBtn = document.getElementById('load-more-notifications');
                if (data.has_more) {
                    loadMoreBtn.style.display = 'block';
                } else {
                    loadMoreBtn.style.display = 'none';
                }
            } else if (reset) {
                // Show no notifications message
                notificationsList.innerHTML = '<div class="no-notifications">No notifications to display</div>';
            }
        })
        .catch(error => {
            console.error('Error fetching notifications:', error);
            const loadingElements = notificationsList.querySelectorAll('.notification-loading');
            loadingElements.forEach(el => el.remove());
            
            if (reset) {
                notificationsList.innerHTML = '<div class="no-notifications">Error loading notifications</div>';
            }
        });
}

/**
 * Create a notification item element
 * @param {Object} notification - The notification data
 * @returns {HTMLElement} - The notification item element
 */
function createNotificationItem(notification) {
    const item = document.createElement('div');
    item.className = `notification-item ${notification.is_read ? '' : 'unread'}`;
    item.setAttribute('data-id', notification.id);
    
    const header = document.createElement('div');
    header.className = 'notification-header';
    
    const title = document.createElement('div');
    title.className = 'notification-title';
    title.textContent = notification.title;
    
    const time = document.createElement('div');
    time.className = 'notification-time';
    time.textContent = formatNotificationTime(notification.created_at);
    
    header.appendChild(title);
    header.appendChild(time);
    
    const message = document.createElement('div');
    message.className = 'notification-message';
    message.textContent = notification.message;
    
    const footer = document.createElement('div');
    footer.className = 'notification-footer';
    
    const source = document.createElement('div');
    source.className = 'notification-source';
    source.textContent = notification.source;
    
    const actions = document.createElement('div');
    actions.className = 'notification-actions';
    
    if (!notification.is_read) {
        const markReadAction = document.createElement('span');
        markReadAction.className = 'notification-action';
        markReadAction.textContent = 'Mark as read';
        markReadAction.addEventListener('click', (e) => {
            e.stopPropagation();
            markNotificationAsRead(notification.id);
        });
        actions.appendChild(markReadAction);
    }
    
    footer.appendChild(source);
    footer.appendChild(actions);
    
    item.appendChild(header);
    item.appendChild(message);
    item.appendChild(footer);
    
    // Mark notification as read when clicked
    item.addEventListener('click', () => {
        if (!notification.is_read) {
            markNotificationAsRead(notification.id);
        }
        
        // Handle notification action if specified
        if (notification.action_url) {
            window.location.href = notification.action_url;
        }
    });
    
    return item;
}

/**
 * Format notification timestamp
 * @param {string} timestamp - The notification timestamp
 * @returns {string} - Formatted time string
 */
function formatNotificationTime(timestamp) {
    const date = new Date(timestamp);
    const now = new Date();
    const diffMs = now - date;
    const diffSec = Math.floor(diffMs / 1000);
    const diffMin = Math.floor(diffSec / 60);
    const diffHr = Math.floor(diffMin / 60);
    const diffDays = Math.floor(diffHr / 24);
    
    if (diffSec < 60) {
        return 'Just now';
    } else if (diffMin < 60) {
        return `${diffMin} minute${diffMin !== 1 ? 's' : ''} ago`;
    } else if (diffHr < 24) {
        return `${diffHr} hour${diffHr !== 1 ? 's' : ''} ago`;
    } else if (diffDays < 7) {
        return `${diffDays} day${diffDays !== 1 ? 's' : ''} ago`;
    } else {
        // Format as MM/DD/YYYY
        const month = date.getMonth() + 1;
        const day = date.getDate();
        const year = date.getFullYear();
        return `${month}/${day}/${year}`;
    }
}

/**
 * Mark a notification as read
 * @param {number} id - The notification ID
 */
function markNotificationAsRead(id) {
    fetch(`/src/api/notifications.php?action=mark_read&id=${id}`, {
        method: 'POST'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const notificationItem = document.querySelector(`.notification-item[data-id="${id}"]`);
                if (notificationItem) {
                    notificationItem.classList.remove('unread');
                    
                    // Remove 'Mark as read' action
                    const markReadAction = notificationItem.querySelector('.notification-action');
                    if (markReadAction) {
                        markReadAction.remove();
                    }
                }
                
                // Update notification count
                updateNotificationCount();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
}

/**
 * Mark all notifications as read
 */
function markAllNotificationsAsRead() {
    fetch('/src/api/notifications.php?action=mark_all_read', {
        method: 'POST'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Update UI
                const unreadItems = document.querySelectorAll('.notification-item.unread');
                unreadItems.forEach(item => {
                    item.classList.remove('unread');
                    
                    // Remove 'Mark as read' action
                    const markReadAction = item.querySelector('.notification-action');
                    if (markReadAction) {
                        markReadAction.remove();
                    }
                });
                
                // Update notification count
                updateNotificationCount(0);
            }
        })
        .catch(error => {
            console.error('Error marking all notifications as read:', error);
        });
}

/**
 * Update the notification count badge
 */
async function updateNotificationCount() {
    try {
        const response = await fetch('/src/api/notifications.php?action=count');
        if (!response.ok) {
            // Silently fail for 404s and other errors
            console.warn('Notification service unavailable');
            return;
        }
        const data = await response.json();
        const countElement = document.getElementById('notification-count');
        if (countElement) {
            countElement.textContent = data.count;
            
            // Show/hide the badge based on count
            if (data.count > 0) {
                countElement.style.display = 'flex';
            } else {
                countElement.style.display = 'none';
            }
        }
    } catch (error) {
        // Silently fail and log warning
        console.warn('Error updating notifications:', error);
    }
}

/**
 * Initialize WhatsApp session management
 */
function initializeWhatsAppSessions() {
    console.log('📱 Initializing WhatsApp sessions...');
    
    // Load WhatsApp sessions CSS
    if (!document.getElementById('whatsapp-sessions-styles')) {
        const link = document.createElement('link');
        link.id = 'whatsapp-sessions-styles';
        link.rel = 'stylesheet';
        link.href = '/assets/css/whatsapp-sessions.css';
        document.head.appendChild(link);
    }
    
    // Load WhatsApp sessions JS
    if (!window.WhatsAppSessionsManager) {
        const script = document.createElement('script');
        script.src = '/assets/js/whatsapp-sessions.js';
        script.onload = function() {
            if (window.WhatsAppSessionsManager) {
                window.whatsappSessionsManager = new window.WhatsAppSessionsManager();
                window.whatsappSessionsManager.initialize();
            }
        };
        document.head.appendChild(script);
    } else {
        window.whatsappSessionsManager = new window.WhatsAppSessionsManager();
        window.whatsappSessionsManager.initialize();
    }
}

// Initialize WhatsApp functionality on all pages (since sidebar is on every page)
document.addEventListener('DOMContentLoaded', function() {
    initializeWhatsAppFunctionality();
});

/**
 * Initialize WhatsApp functionality in the sidebar
 */
function initializeWhatsAppFunctionality() {
    console.log('📱 Initializing WhatsApp functionality in sidebar...');
    
    const whatsappButton = document.getElementById('whatsapp-button');
    const whatsappModal = document.getElementById('whatsapp-modal');
    const closeWhatsappBtn = document.querySelector('.close-whatsapp');
    const initializeBtn = document.getElementById('initialize-whatsapp');
    const logoutBtn = document.getElementById('logout-whatsapp');
    const refreshQrBtn = document.getElementById('refresh-qr');
    
    console.log('🔍 WhatsApp elements found:', {
        whatsappButton: !!whatsappButton,
        whatsappModal: !!whatsappModal,
        closeWhatsappBtn: !!closeWhatsappBtn,
        initializeBtn: !!initializeBtn,
        logoutBtn: !!logoutBtn,
        refreshQrBtn: !!refreshQrBtn
    });
    
    if (!whatsappButton || !whatsappModal) {
        console.warn('WhatsApp elements not found');
        return;
    }
    
    // Open WhatsApp modal when button is clicked
    whatsappButton.addEventListener('click', () => {
        whatsappModal.classList.remove('hidden');
        checkWhatsAppStatus();
    });
    
    // Close WhatsApp modal
    closeWhatsappBtn.addEventListener('click', () => {
        whatsappModal.classList.add('hidden');
    });
    
    // Close modal when clicking outside
    whatsappModal.addEventListener('click', (e) => {
        if (e.target === whatsappModal) {
            whatsappModal.classList.add('hidden');
        }
    });
    
    // Initialize WhatsApp session
    initializeBtn.addEventListener('click', async () => {
        console.log('🔘 Initialize button clicked!');
        try {
            initializeBtn.disabled = true;
            initializeBtn.textContent = 'Initializing...';
            
            console.log('📞 Calling initializeWhatsAppSession()...');
            await initializeWhatsAppSession();
            console.log('✅ initializeWhatsAppSession() completed successfully');
        } catch (error) {
            console.error('❌ Failed to initialize WhatsApp:', error);
            showResponseModal('Failed to initialize WhatsApp: ' + error.message, 'error');
        } finally {
            initializeBtn.disabled = false;
            initializeBtn.textContent = 'Initialize WhatsApp';
        }
    });
    
    // Logout WhatsApp session
    logoutBtn.addEventListener('click', async () => {
        try {
            logoutBtn.disabled = true;
            logoutBtn.textContent = 'Logging out...';
            
            await logoutWhatsAppSession();
        } catch (error) {
            console.error('Failed to logout WhatsApp:', error);
            showResponseModal('Failed to logout WhatsApp: ' + error.message, 'error');
        } finally {
            logoutBtn.disabled = false;
            logoutBtn.textContent = 'Logout WhatsApp';
        }
    });
    
    // Refresh QR code
    refreshQrBtn.addEventListener('click', async () => {
        try {
            refreshQrBtn.disabled = true;
            refreshQrBtn.textContent = 'Refreshing...';
            
            await refreshQRCode();
        } catch (error) {
            console.error('Failed to refresh QR code:', error);
            showResponseModal('Failed to refresh QR code: ' + error.message, 'error');
        } finally {
            refreshQrBtn.disabled = false;
            refreshQrBtn.textContent = 'Refresh QR Code';
        }
    });
    
    // Initial status check
    checkWhatsAppStatus();
    
    // Set up periodic status checks
    setInterval(checkWhatsAppStatus, 30000); // Check every 30 seconds
}

/**
 * Check WhatsApp session status
 */
async function checkWhatsAppStatus() {
    try {
        const response = await fetch('/src/api/whatsapp-sessions.php?action=status');
        const data = await response.json();
        
        if (data.success) {
            updateWhatsAppStatus(data.status);
        } else {
            updateWhatsAppStatus('disconnected');
        }
    } catch (error) {
        console.error('Failed to check WhatsApp status:', error);
        updateWhatsAppStatus('disconnected');
    }
}

/**
 * Update WhatsApp status display
 */
function updateWhatsAppStatus(status) {
    const statusIndicator = document.getElementById('whatsapp-status-indicator');
    const statusDot = document.getElementById('whatsapp-status-dot');
    const statusText = document.getElementById('whatsapp-status-text');
    const initializeBtn = document.getElementById('initialize-whatsapp');
    const logoutBtn = document.getElementById('logout-whatsapp');
    const refreshQrBtn = document.getElementById('refresh-qr');
    
    if (!statusIndicator || !statusDot || !statusText) return;
    
    // Update status indicator in sidebar
    statusIndicator.className = `whatsapp-status-indicator ${status}`;
    
    // Update status in modal
    statusDot.className = `status-dot ${status}`;
    
    // Update status text and button visibility
    switch (status) {
        case 'ready':
            statusText.textContent = 'Connected and ready to send messages';
            initializeBtn.classList.add('hidden');
            logoutBtn.classList.remove('hidden');
            refreshQrBtn.classList.add('hidden');
            break;
        case 'qr_ready':
            statusText.textContent = 'QR code ready - scan with your phone';
            initializeBtn.classList.add('hidden');
            logoutBtn.classList.remove('hidden');
            refreshQrBtn.classList.remove('hidden');
            break;
        case 'authenticated':
            statusText.textContent = 'Authenticating...';
            initializeBtn.classList.add('hidden');
            logoutBtn.classList.remove('hidden');
            refreshQrBtn.classList.add('hidden');
            break;
        case 'disconnected':
        case 'auth_failed':
        case 'logged_out':
        default:
            statusText.textContent = 'Not connected';
            initializeBtn.classList.remove('hidden');
            logoutBtn.classList.add('hidden');
            refreshQrBtn.classList.add('hidden');
            break;
    }
}

/**
 * Initialize WhatsApp session
 */
async function initializeWhatsAppSession() {
    console.log('🚀 initializeWhatsAppSession() called');
    
    console.log('📡 Making API call to /src/api/whatsapp-sessions.php...');
    const response = await fetch('/src/api/whatsapp-sessions.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'action=initialize'
    });
    
    console.log('📥 API response received:', response.status, response.statusText);
    const data = await response.json();
    console.log('📋 API response data:', data);
    
    if (data.success) {
        console.log('✅ API call successful');
        console.log('📋 Full response data:', data);
        
        // Check for QR code in the correct location
        const qrCode = data.qr_code;
        const status = data.status;
        
        if (qrCode) {
            console.log('🖼️ QR code received, displaying...');
            // Clean up QR code data - remove duplicate prefixes
            let cleanQrCode = qrCode;
            if (qrCode.includes('data:image/png;base64,data:image/png;base64,')) {
                cleanQrCode = qrCode.replace('data:image/png;base64,data:image/png;base64,', 'data:image/png;base64,');
            }
            displayQRCode(cleanQrCode);
            console.log('QR Code displayed:', cleanQrCode.substring(0, 100) + '...');
        } else {
            console.log('❌ No QR code found in response');
        }
        
        updateWhatsAppStatus(status);
        
        // Don't show success modal if QR code is displayed - let user see the QR code
        if (!qrCode) {
            showResponseModal('WhatsApp session initialized successfully', 'success');
        }
    } else {
        console.error('❌ API call failed:', data.message);
        throw new Error(data.message || 'Failed to initialize WhatsApp session');
    }
}

/**
 * Logout WhatsApp session
 */
async function logoutWhatsAppSession() {
    const response = await fetch('/src/api/whatsapp-sessions.php?action=logout', {
        method: 'DELETE'
    });
    
    const data = await response.json();
    
    if (data.success) {
        updateWhatsAppStatus('logged_out');
        hideQRCode();
        showResponseModal('WhatsApp session logged out successfully', 'success');
    } else {
        throw new Error(data.message || 'Failed to logout WhatsApp session');
    }
}

/**
 * Refresh QR code
 */
async function refreshQRCode() {
    const response = await fetch('/src/api/whatsapp-sessions.php?action=qr');
    
    const data = await response.json();
    
    if (data.success && data.qr_code) {
        displayQRCode(data.qr_code);
        showResponseModal('QR code refreshed', 'success');
    } else {
        throw new Error(data.message || 'Failed to refresh QR code');
    }
}

/**
 * Display QR code in modal
 */
function displayQRCode(qrCodeDataUrl) {
    console.log('Displaying QR code...');
    const qrImage = document.getElementById('qr-code-image');
    const qrPlaceholder = document.querySelector('.qr-placeholder');
    
    console.log('QR Image element:', qrImage);
    console.log('QR Placeholder element:', qrPlaceholder);
    
    if (qrImage && qrPlaceholder) {
        qrImage.src = qrCodeDataUrl;
        qrImage.classList.remove('hidden');
        qrPlaceholder.classList.add('hidden');
        console.log('QR Code displayed successfully');
    } else {
        console.error('QR code elements not found:', { qrImage, qrPlaceholder });
    }
}

/**
 * Hide QR code in modal
 */
function hideQRCode() {
    const qrImage = document.getElementById('qr-code-image');
    const qrPlaceholder = document.querySelector('.qr-placeholder');
    
    if (qrImage && qrPlaceholder) {
        qrImage.classList.add('hidden');
        qrPlaceholder.classList.remove('hidden');
    }
}