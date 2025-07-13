// Client API: Handles all API calls for client CRUD and list operations
// Usage: import { fetchClients, fetchClientDetails, createClient, updateClient, deleteClient } from './client-api.js';

import { buildQueryParams } from '../../../public/assets/js/helpers.js';

/**
 * Fetch a paginated, filtered list of clients
 * @param {Object} options - {search, type, page, limit, sort_by, sort_dir}
 * @returns {Promise<Object>} Modal-compatible response
 */
export async function fetchClients({ page = 1, limit = 10, type = 'private' } = {}) {
    try {
        const params = new URLSearchParams({
            action: 'list_clients',
            page,
            limit,
            type
        });
        const res = await fetch(`/modules/invoice/api/client-api.php?${params.toString()}`);
        const json = await res.json();
        // Ensure total is present for pagination
        if (!('total' in json) && Array.isArray(json.data)) {
            json.total = json.data.length;
        }
        return json;
    } catch (e) {
        return { success: false, message: 'Failed to fetch clients', data: [], total: 0 };
    }
}

/**
 * Fetch details for a single client
 * @param {number} clientId
 * @returns {Promise<Object>} Modal-compatible response
 */
export async function fetchClientDetails(clientId) {
    // TODO: Implement API call to backend (get_client_details)
}

/**
 * Create a new client
 * @param {Object} data - Client data
 * @returns {Promise<Object>} Modal-compatible response
 */
export async function createClient(data) {
    // TODO: Implement API call to backend (create_client)
}

/**
 * Update an existing client
 * @param {number} clientId
 * @param {Object} data - Updated client data
 * @returns {Promise<Object>} Modal-compatible response
 */
export async function updateClient(clientId, data) {
    // TODO: Implement API call to backend (update_client)
}

/**
 * Delete a client
 * @param {number} clientId
 * @param {number} deletedBy - User ID
 * @returns {Promise<Object>} Modal-compatible response
 */
export async function deleteClient(clientId, deletedBy) {
    // TODO: Implement API call to backend (delete_client)
}
