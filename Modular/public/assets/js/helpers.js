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

