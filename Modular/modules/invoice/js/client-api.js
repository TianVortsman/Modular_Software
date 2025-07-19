// Client API: Handles all API calls for client CRUD and list operations
// Usage: import { fetchClients, fetchClientDetails, createClient, updateClient, deleteClient } from './client-api.js';

import { buildQueryParams } from '../../../public/assets/js/helpers.js';

/**
 * Fetch a paginated, filtered list of clients
 * @param {Object} options - {search, type, page, limit, sort_by, sort_dir}
 * @returns {Promise<Object>} Modal-compatible response
 */
export async function fetchClients({ page = 1, limit = 10, type = 'private', search = '' } = {}) {
    try {
        const params = buildQueryParams(
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
        showResponseModal(e.message, 'error');
        return { success: false, message: 'Failed to fetch clients', data: [], total: 0 };
    }
}

/**
 * Fetch details for a single client
 * @param {number} clientId
 * @returns {Promise<Object>} Modal-compatible response
 */
export async function fetchClientDetails(clientId) {
    try {
        const params = buildQueryParams(
            { action: 'get_client_details' },
            { client_id: clientId }
        );
        const res = await fetch(`/modules/invoice/api/client-api.php?${params.toString()}`);
        const json = await res.json();
        window.handleApiResponse(json);
        return json;
    } catch (e) {
        showResponseModal(e.message, 'error');
        return { success: false, message: 'Failed to fetch client details', data: null };
    }
}

/**
 * Create a new client
 * @param {Object} data - Client data
 * @returns {Promise<Object>} Modal-compatible response
 */
export async function createClient(data) {
    try {
        const params = buildQueryParams({ action: 'create_client' });
        const res = await fetch(`/modules/invoice/api/client-api.php?${params.toString()}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const json = await res.json();
        window.handleApiResponse(json);
        return json;
    } catch (e) {
        showResponseModal(e.message, 'error');
        return { success: false, message: 'Failed to create client', data: null };
    }
}

/**
 * Update an existing client
 * @param {number} clientId
 * @param {Object} data - Updated client data
 * @returns {Promise<Object>} Modal-compatible response
 */
export async function updateClient(clientId, data) {
    try {
        const params = buildQueryParams({ action: 'update_client', client_id: clientId });
        const res = await fetch(`/modules/invoice/api/client-api.php?${params.toString()}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(data)
        });
        const json = await res.json();
        window.handleApiResponse(json);
        return json;
    } catch (e) {
        showResponseModal(e.message, 'error');
        return { success: false, message: 'Failed to update client', data: null };
    }
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
