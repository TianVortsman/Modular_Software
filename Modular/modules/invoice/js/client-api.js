// Client API: Handles all API calls for client CRUD and list operations
// All functions are now globally available via window object

/**
 * Fetch a paginated, filtered list of clients
 * @param {Object} options - {search, type, page, limit, sort_by, sort_dir}
 * @returns {Promise<Object>} Modal-compatible response
 */
async function fetchClients({ page = 1, limit = 10, type = 'private', search = '' } = {}) {
    try {
        const params = window.buildQueryParams(
            { action: 'list_clients' },
            { type },
            search,
            { page, limit }
        );
        const res = await fetch(`/modules/invoice/api/client-api.php?${params.toString()}`);
        const json = await res.json();
        window.handleApiResponse(json);
        // Ensure total is present for pagination
        if (!('total' in json) && Array.isArray(json.data)) {
            json.total = json.data.length;
        }
        return json;
    } catch (e) {
        window.showResponseModal(e.message, 'error');
        return { success: false, message: 'Failed to fetch clients', data: [], total: 0 };
    }
}

/**
 * Fetch details for a single client
 * @param {number} clientId
 * @returns {Promise<Object>} Modal-compatible response
 */
async function fetchClientDetails(clientId) {
    try {
        const params = window.buildQueryParams(
            { action: 'get_client_details' },
            { client_id: clientId }
        );
        const res = await fetch(`/modules/invoice/api/client-api.php?${params.toString()}`);
        const json = await res.json();
        window.handleApiResponse(json);
        return json;
    } catch (e) {
        window.showResponseModal(e.message, 'error');
        return { success: false, message: 'Failed to fetch client details', data: null };
    }
}

/**
 * Create a new client
 * @param {Object} data - Client data
 * @returns {Promise<Object>} Modal-compatible response
 */
async function createClient(data) {
    try {
        const params = window.buildQueryParams({ action: 'create_client' });
        const res = await fetch(`/modules/invoice/api/client-api.php?${params.toString()}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const json = await res.json();
        window.handleApiResponse(json);
        return json;
    } catch (e) {
        window.showResponseModal(e.message, 'error');
        return { success: false, message: 'Failed to create client', data: null };
    }
}

/**
 * Update an existing client
 * @param {number} clientId
 * @param {Object} data - Updated client data
 * @returns {Promise<Object>} Modal-compatible response
 */
async function updateClient(clientId, data) {
    try {
        const params = window.buildQueryParams({ action: 'update_client', client_id: clientId });
        const res = await fetch(`/modules/invoice/api/client-api.php?${params.toString()}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const json = await res.json();
        window.handleApiResponse(json);
        return json;
    } catch (e) {
        window.showResponseModal(e.message, 'error');
        return { success: false, message: 'Failed to update client', data: null };
    }
}

/**
 * Delete a client
 * @param {number} clientId
 * @param {number} deletedBy - User ID
 * @returns {Promise<Object>} Modal-compatible response
 */
async function deleteClient(clientId, deletedBy) {
    // TODO: Implement API call to backend (delete_client)
}

// Make functions available globally
window.fetchClients = fetchClients;
window.fetchClientDetails = fetchClientDetails;
window.createClient = createClient;
window.updateClient = updateClient;
window.deleteClient = deleteClient;
