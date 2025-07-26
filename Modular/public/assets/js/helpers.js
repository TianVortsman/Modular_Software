//HELPER FUNCTIONS FOR ALL APIS 
/**
 * Build query parameters for API requests.
 * Returns a URLSearchParams instance.
 * 
 * @param {Object} base - Base params (e.g. {action: 'get'})
 * @param {Object} filters - Filter params (e.g. {status: 'active'})
 * @param {string} search - Search string
 * @param {Object} pagination - Pagination (e.g. {page: 1, limit: 20})
 * @param {Object} sorting - Sorting (e.g. {sortBy: 'name', sortDir: 'asc'})
 * @returns {URLSearchParams}
 */
export function buildQueryParams(base = {}, filters = {}, search = '', pagination = {}, sorting = {}) {
    const params = new URLSearchParams();

    // Add base params (action, module, details, etc.)
    Object.entries(base).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            params.append(key, value);
        }
    });

    // Add filter params
    Object.entries(filters).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            params.append(key, value);
        }
    });

    // Add search
    if (search.trim()) {
        params.append('search', search.trim());
    }

    // Pagination
    const { page, limit } = pagination;
    if (page !== undefined && page !== null && page !== '') params.append('page', page);
    if (limit !== undefined && limit !== null && limit !== '') params.append('limit', limit);

    // Sorting
    const { sortBy, sortDir } = sorting;
    if (sortBy) params.append('sort_by', sortBy);
    if (sortDir) params.append('sort_dir', sortDir);

    return params;
}

/**
 * Fetch with timeout helper
 */
export async function fetchWithTimeout(resource, options = {}, timeout = 5000) {
    const controller = new AbortController();
    const id = setTimeout(() => controller.abort(), timeout);
    try {
        const response = await fetch(resource, { ...options, signal: controller.signal });
        clearTimeout(id);
        return response;
    } catch (error) {
        clearTimeout(id);
        throw error;
    }
}

/**
 * Auto-inject response modal if not present on the page
 */
function ensureResponseModalExists() {
    if (document.getElementById('modalResponse')) {
        return; // Modal already exists
    }

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
    const modalContainer = document.createElement('div');
    modalContainer.innerHTML = modalHTML;
    document.body.appendChild(modalContainer.firstElementChild);
}

/**
 * Enhanced response modal function
 * @param {string} message - Message to display
 * @param {string} type - Type: 'success', 'error', 'warning', 'info'
 * @param {boolean} persistent - If true, modal won't auto-close
 * @param {boolean} confirm - If true, shows Yes/No buttons
 * @param {object} details - Additional technical details (for development)
 * @returns {Promise} - For confirmation dialogs
 */
function showResponseModal(message, type = 'info', persistent = false, confirm = false, details = null) {
    // Ensure modal exists
    ensureResponseModalExists();
    
    console.log('showResponseModal called:', { message, type, persistent, confirm, details });
    
    const modal = document.getElementById('modalResponse');
    const title = document.getElementById('modalResponseTitle');
    const msg = document.getElementById('modalResponseMessage');
    const icon = document.getElementById('modalResponseIcon');
    const detailsDiv = document.getElementById('modalResponseDetails');
    const footer = modal.querySelector('.custom-modal-footer');
    
    if (!modal || !title || !msg || !icon || !footer) {
        console.error('Response modal elements not found');
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
    modal.classList.remove('hidden');

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

// Global functions
window.showResponseModal = showResponseModal;
window.closeResponseModal = closeResponseModal;

/**
 * Enhanced centralized API response handler for fetch/AJAX calls
 * Handles the new consistent error response format from the backend
 * Usage: .then(handleApiResponse)
 * Shows errors in ResponseModal if present, returns data on success
 */
function handleApiResponse(data) {
    window.lastApiRawResponse = data;
    
    // Handle null/undefined responses
    if (!data) {
        const errorMsg = 'No response from server. Please check your connection or try again later.';
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
        let errorMsg = data.message || data.error || 'An unknown error occurred.';
        
        // In development mode, show additional technical details if available
        if (data.technical_error && data.error_code) {
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
            showResponseModal(errorMsg, 'error', true, false, {
                technical_error: data.technical_error,
                error_code: data.error_code,
                form_data: data.form_data,
                context: data.context
            });
        } else {
            showResponseModal(errorMsg, 'error');
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

window.handleApiResponse = handleApiResponse;

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

export function makeModalDraggable(modalSelector, dragHandleSelector) {
    const modal = document.querySelector(modalSelector);
    if (!modal) return console.warn(`Modal not found: ${modalSelector}`);
  
    const dragHandle = modal.querySelector(dragHandleSelector);
    if (!dragHandle) return console.warn(`Drag handle not found: ${dragHandleSelector}`);
  
    let isDragging = false;
    let startX, startY;
    let initialLeft, initialTop;
  
    dragHandle.style.cursor = 'move';
  
    dragHandle.addEventListener('mousedown', (e) => {
      isDragging = true;
      startX = e.clientX;
      startY = e.clientY;
  
      const rect = modal.getBoundingClientRect();
      initialLeft = rect.left;
      initialTop = rect.top;
  
      document.body.style.userSelect = 'none';
    });
  
    document.addEventListener('mousemove', (e) => {
      if (!isDragging) return;
  
      const dx = e.clientX - startX;
      const dy = e.clientY - startY;
  
      modal.style.position = 'fixed';
      modal.style.left = initialLeft + dx + 'px';
      modal.style.top = initialTop + dy + 'px';
    });
  
    document.addEventListener('mouseup', () => {
      if (isDragging) {
        isDragging = false;
        document.body.style.userSelect = '';
      }
    });
}
  