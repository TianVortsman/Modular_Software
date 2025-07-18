// --- Supplier API ---
export async function getSuppliers() {
    const res = await fetch('../api/setup-api.php?action=getSuppliers');
    return await res.json();
}

export async function getSupplier(supplierId) {
    const res = await fetch(`../api/setup-api.php?action=getSupplier&supplier_id=${encodeURIComponent(supplierId)}`);
    return await res.json();
}

export async function saveSupplier(formData) {
    const res = await fetch('../api/setup-api.php?action=saveSupplier', {
        method: 'POST',
        body: formData
    });
    return await res.json();
}

// --- Category & Subcategory API ---
export async function getCategories() {
    const res = await fetch('../api/setup-api.php?action=getCategories');
    return await res.json();
}

export async function getSubcategories() {
    const res = await fetch('../api/setup-api.php?action=getSubcategories');
    return await res.json();
}

export async function saveCategory(formData) {
    const res = await fetch('../api/setup-api.php?action=saveCategory', {
        method: 'POST',
        body: formData
    });
    return await res.json();
}

export async function saveSubcategory(formData) {
    const res = await fetch('../api/setup-api.php?action=saveSubcategory', {
        method: 'POST',
        body: formData
    });
    return await res.json();
}

// --- Sales Target API ---
export async function getSalesTargets() {
    const res = await fetch('../api/setup-api.php?action=getSalesTargets');
    return await res.json();
}

export async function getSalesTarget(targetId) {
    const res = await fetch(`../api/setup-api.php?action=getSalesTarget&sales_target_id=${encodeURIComponent(targetId)}`);
    return await res.json();
}

export async function saveSalesTarget(formData) {
    const res = await fetch('../api/setup-api.php?action=saveSalesTarget', {
        method: 'POST',
        body: formData
    });
    return await res.json();
}

export async function getSalesUsers() {
    const res = await fetch('../api/setup-api.php?action=getSalesUsers');
    return await res.json();
}

export async function getSupplierContacts(supplierId) {
    const res = await fetch(`../api/setup-api.php?action=getSupplierContacts&supplier_id=${encodeURIComponent(supplierId)}`);
    return await res.json();
}

export async function addSupplierContact(formData) {
    const res = await fetch('../api/setup-api.php?action=addSupplierContact', {
        method: 'POST',
        body: formData
    });
    return await res.json();
}

export async function updateSupplierContact(formData) {
    const res = await fetch('../api/setup-api.php?action=updateSupplierContact', {
        method: 'POST',
        body: formData
    });
    return await res.json();
}

export async function deleteSupplierContact(formData) {
    const res = await fetch('../api/setup-api.php?action=deleteSupplierContact', {
        method: 'POST',
        body: formData
    });
    return await res.json();
}
