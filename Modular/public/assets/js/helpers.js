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
 * Centralized API response handler for fetch/AJAX calls
 * Usage: .then(handleApiResponse)
 * Shows errors in ResponseModal if present, returns data on success
 */
function handleApiResponse(data) {
    window.lastApiRawResponse = data;
    if (!data) {
        showResponseModal('No response from server. Please check your connection or try again later.', 'error');
        throw new Error('No response from server.');
    }
    if (data && (data.success === false || data.status >= 400)) {
        // Always prefer AI-friendly message
        const msg = data.message || data.error || 'An unknown error occurred.';
        showResponseModal(msg, 'error');
        throw new Error(msg);
    }
    return data;
}

window.handleApiResponse = handleApiResponse;

window.onerror = async function(message, source, lineno, colno, error) {
    const errorCode = Math.random().toString(36).substring(2, 8).toUpperCase(); // e.g. 7H2G3T
    try {
        const res = await fetch('/public/php/log-js-error.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                message, source, lineno, colno, stack: error?.stack, code: errorCode
            })
        });
        const data = await res.json();
        if (data && data.error) {
            showResponseModal(data.error, 'error');
        } else {
            showResponseModal('Oops! Something went wrong. Please contact Modular Software Support. Error Code: ' + errorCode, 'error');
        }
    } catch (e) {
        showResponseModal('Oops! Something went wrong. Please contact Modular Software Support. Error Code: ' + errorCode, 'error');
    }
};

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
  