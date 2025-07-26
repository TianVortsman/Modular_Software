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
 * Make a modal draggable
 * @param {string} modalSelector - CSS selector for the modal
 * @param {string} dragHandleSelector - CSS selector for the drag handle within the modal
 */
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

// Note: Error handling and modal functions are now handled by sidebar.js
// This file focuses on pure utility functions for API operations
console.log('📦 Helper utilities loaded - Error handling managed by sidebar.js');
  