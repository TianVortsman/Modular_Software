// Client Screen Logic: Handles main screen rendering, event binding, and table updates
// Usage: import { initClientScreen, renderClientTable, bindEvents, handleSearch, handleFilter, handlePagination, handleRowClick, refreshClientTable } from './client-screen.js';
import { openClientModal, closeModal, } from './client-modal.js';
import { fetchClients } from './client-api.js';

let isLoading = false;
let currentType = 'private';
let currentPage = 1;
let currentLimitPrivate = 10;
let currentLimitBusiness = 10;
let currentSearchPrivate = '';
let currentSearchBusiness = '';
let searchDebounceTimeout = null;

function getCurrentTableBody() {
    if (currentType === 'private') {
        return document.getElementById('client-body-private');
    } else {
        return document.getElementById('client-body-business');
    }
}

function getCurrentLimit() {
    return currentType === 'private' ? currentLimitPrivate : currentLimitBusiness;
}
function setCurrentLimit(val) {
    if (currentType === 'private') {
        currentLimitPrivate = val;
    } else {
        currentLimitBusiness = val;
    }
}

function getCurrentSearch() {
    return currentType === 'private' ? currentSearchPrivate : currentSearchBusiness;
}
function setCurrentSearch(val) {
    if (currentType === 'private') {
        currentSearchPrivate = val;
    } else {
        currentSearchBusiness = val;
    }
}

/**
 * Initialize the client screen (on page load)
 */
export async function initClientScreen() {
    const tableBody = getCurrentTableBody();
    if (!tableBody) return;
    bindEvents();
    await refreshClientTable();
}

// Set a min-height for the table container to prevent height jumps
function setTableMinHeight() {
    const containers = document.querySelectorAll('.client-table-container');
    containers.forEach(container => {
        container.style.minHeight = '1200px'; // Adjust as needed for your UI
    });
}

/**
 * Render the client table with data
 * @param {Array} clients
 */
export function renderClientTable(clients, total = 0) {
    setTableMinHeight();
    const tableBody = getCurrentTableBody();
    if (!tableBody) return;
    // Remove all existing rows (but keep tbody in DOM)
    while (tableBody.firstChild) {
        tableBody.removeChild(tableBody.firstChild);
    }
    if (!clients || clients.length === 0) {
        const row = document.createElement('tr');
        const cell = document.createElement('td');
        cell.colSpan = 7;
        cell.textContent = 'No clients found.';
        row.appendChild(cell);
        tableBody.appendChild(row);
        renderPagination(0, 0, 0);
        return;
    }
    clients.forEach(client => {
        const row = document.createElement('tr');
        let name = client.client_name || ((client.first_name || '') + ' ' + (client.last_name || ''));
        row.innerHTML = `
            <td>${client.client_id}</td>
            <td>${name.trim()}</td>
            <td>${client.client_email || ''}</td>
            <td>${client.client_cell || client.client_tell || ''}</td>
            <td>${client.last_invoice_date || '-'}</td>
            <td>${client.outstanding_amount !== undefined ? client.outstanding_amount : '-'}</td>
            <td>${client.total_invoices !== undefined ? client.total_invoices : '-'}</td>
        `;
        row.addEventListener('dblclick', () => {
            if (typeof openClientModal === 'function') {
                openClientModal(client.client_id);
            } else {
                console.warn('openClientModal(client_id) not implemented');
            }
        });
        tableBody.appendChild(row);
    });
    renderPagination(currentPage, getCurrentLimit(), total);
}

function renderLoading(tableBody) {
    // Remove all existing rows (but keep tbody in DOM)
    while (tableBody.firstChild) {
        tableBody.removeChild(tableBody.firstChild);
    }
    const row = document.createElement('tr');
    const cell = document.createElement('td');
    cell.colSpan = 7;
    cell.textContent = 'Loading...';
    row.appendChild(cell);
    tableBody.appendChild(row);
}

function renderError(tableBody, message) {
    tableBody.innerHTML = '';
    const row = document.createElement('tr');
    const cell = document.createElement('td');
    cell.colSpan = 7;
    cell.textContent = message || 'Failed to load clients.';
    row.appendChild(cell);
    tableBody.appendChild(row);
}

function renderPagination(page, limit, total) {
    // Find the correct container for the active tab
    let container = null;
    if (currentType === 'private') {
        container = document.getElementById('pagination-container1');
    } else {
        container = document.getElementById('pagination-container2');
    }
    if (!container) return;
    container.innerHTML = '';
    if (total <= limit) return; // No need for pagination
    const totalPages = Math.ceil(total / limit);
    const prevBtn = document.createElement('button');
    prevBtn.textContent = 'Previous';
    prevBtn.disabled = page <= 1;
    prevBtn.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            refreshClientTable();
        }
    });
    const nextBtn = document.createElement('button');
    nextBtn.textContent = 'Next';
    nextBtn.disabled = page >= totalPages;
    nextBtn.addEventListener('click', () => {
        if (currentPage < totalPages) {
            currentPage++;
            refreshClientTable();
        }
    });
    const pageInfo = document.createElement('span');
    pageInfo.textContent = `Page ${page} of ${totalPages}`;
    container.appendChild(prevBtn);
    container.appendChild(pageInfo);
    container.appendChild(nextBtn);
}

/**
 * Bind all event listeners (search, filter, pagination, add/edit/delete)
 */
export function bindEvents() {
    // Tab switching
    const privateBtn = document.getElementById('clientSectionButton1');
    const businessBtn = document.getElementById('clientSectionButton2');
    const section1 = document.getElementById('client-section1');
    const section2 = document.getElementById('client-section2');
    if (privateBtn && businessBtn && section1 && section2) {
        privateBtn.addEventListener('click', () => {
            privateBtn.classList.add('active');
            businessBtn.classList.remove('active');
            section1.classList.add('active');
            section2.classList.remove('active');
            currentType = 'private';
            currentPage = 1;
            // Set select value for private
            const select = section1.querySelector('.rows-per-page');
            if (select) select.value = currentLimitPrivate;
            // Update search input for private
            const searchInput = document.getElementById('client-search');
            if (searchInput) searchInput.value = getCurrentSearch();
            refreshClientTable();
        });
        businessBtn.addEventListener('click', () => {
            businessBtn.classList.add('active');
            privateBtn.classList.remove('active');
            section2.classList.add('active');
            section1.classList.remove('active');
            currentType = 'business';
            currentPage = 1;
            // Set select value for business
            const select = section2.querySelector('.rows-per-page');
            if (select) select.value = currentLimitBusiness;
            // Update search input for business
            const searchInput = document.getElementById('client-search');
            if (searchInput) searchInput.value = getCurrentSearch();
            refreshClientTable();
        });
    }
    // Rows per page select (per section)
    document.querySelectorAll('.rows-per-page').forEach(select => {
        select.addEventListener('change', (e) => {
            setCurrentLimit(parseInt(e.target.value, 10));
            currentPage = 1;
            refreshClientTable();
        });
    });
    // Search input
    const searchInput = document.getElementById('client-search');
    if (searchInput) {
        searchInput.addEventListener('input', (e) => {
            const val = searchInput.value.trim();
            setCurrentSearch(val);
            currentPage = 1;
            if (searchDebounceTimeout) clearTimeout(searchDebounceTimeout);
            searchDebounceTimeout = setTimeout(() => {
                if (val.length === 0 || val.length >= 2) {
                    refreshClientTable();
                }
            }, 300);
        });
        // Optional: search icon click
        const searchIcon = document.querySelector('.search-icon');
        if (searchIcon) {
            searchIcon.addEventListener('click', () => {
                const val = searchInput.value.trim();
                setCurrentSearch(val);
                currentPage = 1;
                if (val.length === 0 || val.length >= 2) {
                    refreshClientTable();
                }
            });
        }
    }
}

/**
 * Handle search input
 * @param {Event} event
 */
export function handleSearch(event) {
    // TODO: Handle search/filter logic
}

/**
 * Handle filter change (e.g., client type)
 * @param {Event} event
 */
export function handleFilter(event) {
    // TODO: Handle filter logic
}

/**
 * Handle pagination controls
 * @param {number} page
 */
export function handlePagination(page) {
    // TODO: Handle pagination logic
}

/**
 * Handle row click (edit/view client)
 * @param {Event} event
 */
export function handleRowClick(event) {
    // TODO: Handle row click logic (open modal, fetch details)
}

/**
 * Refresh the client table (after add/edit/delete)
 */
export async function refreshClientTable() {
    const tableBody = getCurrentTableBody();
    if (!tableBody) return;
    isLoading = true;
    renderLoading(tableBody);
    const response = await fetchClients({ page: currentPage, limit: getCurrentLimit(), type: currentType, search: getCurrentSearch() });
    isLoading = false;
    if (response.success) {
        // Pass total count for pagination
        renderClientTable(response.data, response.total || 0);
    } else {
        renderError(tableBody, response.message);
        renderPagination(0, 0, 0);
    }
}

// Auto-init if this is the main client screen
if (getCurrentTableBody()) {
    window.addEventListener('DOMContentLoaded', initClientScreen);
}
