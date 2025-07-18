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

// --- Credit Reason API ---
export async function getCreditReasons() {
    const res = await fetch('../api/setup-api.php?action=getCreditReasons', {
        credentials: 'include'
    });
    return await res.json();
}

export async function addCreditReason(formData) {
    const res = await fetch('../api/setup-api.php?action=addCreditReason', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    });
    return await res.json();
}

export async function updateCreditReason(formData) {
    const res = await fetch('../api/setup-api.php?action=updateCreditReason', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    });
    return await res.json();
}

export async function deleteCreditReason(formData) {
    const res = await fetch('../api/setup-api.php?action=deleteCreditReason', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    });
    return await res.json();
}

// --- Payment Term API ---
export async function getPaymentTerms() {
    const res = await fetch('../api/setup-api.php?action=getPaymentTerms');
    return await res.json();
}

export async function addPaymentTerm(formData) {
    const res = await fetch('../api/setup-api.php?action=addPaymentTerm', {
        method: 'POST',
        body: formData
    });
    return await res.json();
}

export async function updatePaymentTerm(formData) {
    const res = await fetch('../api/setup-api.php?action=updatePaymentTerm', {
        method: 'POST',
        body: formData
    });
    return await res.json();
}

export async function deletePaymentTerm(formData) {
    const res = await fetch('../api/setup-api.php?action=deletePaymentTerm', {
        method: 'POST',
        body: formData
    });
    return await res.json();
}

export async function getCreditPolicy() {
    const res = await fetch('../api/setup-api.php?action=getCreditPolicy', {
        credentials: 'include'
    });
    return await res.json();
}

export async function saveCreditPolicy(formData) {
    const res = await fetch('../api/setup-api.php?action=saveCreditPolicy', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    });
    return await res.json();
}
