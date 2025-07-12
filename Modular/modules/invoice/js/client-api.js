// --- Client/Company API Logic ---

// Fetch all clients or companies
export async function fetchClientData(section) {
    const endpoint = section === 'private' ? '../api/customers.php' : '../api/companies.php';
    try {
        const response = await fetch(`${endpoint}?action=get_all`);
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        return await response.json();
    } catch (error) {
        console.error('Fetch error:', error);
        return { success: false, error: error.message || 'Unknown error' };
    }
}

// Add or update company/customer (action: 'add' or 'update')
export async function submitEntityApi(type, action, formData) {
    const endpoint = type === 'company' ? '../api/companies.php' : '../api/customers.php';
    try {
        const response = await fetch(`${endpoint}?action=${action}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(formData)
        });
        return await response.json();
    } catch (error) {
        console.error(`Error ${action} ${type}:`, error);
        return { success: false, error: `An error occurred while ${action === 'add' ? 'adding' : 'updating'} the ${type}` };
    }
}

// Fetch company or customer by ID
export async function fetchEntityData(type, id) {
    const endpoint = type === 'company' ? '../api/companies.php' : '../api/customers.php';
    try {
        const response = await fetch(`${endpoint}?action=get&id=${id}`);
        return await response.json();
    } catch (error) {
        console.error(`Error fetching ${type} data:`, error);
        return { success: false, error: `An error occurred while fetching ${type} data` };
    }
}