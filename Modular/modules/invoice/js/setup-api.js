// --- Supplier API ---
export async function getSuppliers() {
    const res = await fetch('../api/setup-api.php?action=getSuppliers');
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

export async function getSupplier(supplierId) {
    const res = await fetch(`../api/setup-api.php?action=getSupplier&supplier_id=${encodeURIComponent(supplierId)}`);
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

export async function saveSupplier(formData) {
    const res = await fetch('../api/setup-api.php?action=saveSupplier', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

// --- Category & Subcategory API ---
export async function getCategories() {
    const res = await fetch('../api/setup-api.php?action=getCategories');
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

export async function getSubcategories() {
    const res = await fetch('../api/setup-api.php?action=getSubcategories');
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

export async function saveCategory(formData) {
    const res = await fetch('../api/setup-api.php?action=saveCategory', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

export async function saveSubcategory(formData) {
    const res = await fetch('../api/setup-api.php?action=saveSubcategory', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

// --- Sales Target API ---
export async function getSalesTargets() {
    const res = await fetch('../api/setup-api.php?action=getSalesTargets');
    const data = await res.json();
    window.handleApiResponse(data);
}

export async function getSalesTarget(targetId) {
    const res = await fetch(`../api/setup-api.php?action=getSalesTarget&sales_target_id=${encodeURIComponent(targetId)}`);
    const data = await res.json();
    window.handleApiResponse(data);
}

export async function saveSalesTarget(formData) {
    const res = await fetch('../api/setup-api.php?action=saveSalesTarget', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();
    window.handleApiResponse(data);
}

export async function getSalesUsers() {
    const res = await fetch('../api/setup-api.php?action=getSalesUsers');
    const data = await res.json();
    window.handleApiResponse(data);
}

export async function getSupplierContacts(supplierId) {
    const res = await fetch(`../api/setup-api.php?action=getSupplierContacts&supplier_id=${encodeURIComponent(supplierId)}`);
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

export async function addSupplierContact(formData) {
    const res = await fetch('../api/setup-api.php?action=addSupplierContact', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

export async function updateSupplierContact(formData) {
    const res = await fetch('../api/setup-api.php?action=updateSupplierContact', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

export async function deleteSupplierContact(formData) {
    const res = await fetch('../api/setup-api.php?action=deleteSupplierContact', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

// --- Credit Reason API ---
export async function getCreditReasons() {
    const res = await fetch('../api/setup-api.php?action=getCreditReasons', {
        credentials: 'include'
    });
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

export async function addCreditReason(formData) {
    const res = await fetch('../api/setup-api.php?action=addCreditReason', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    });
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

export async function updateCreditReason(formData) {
    const res = await fetch('../api/setup-api.php?action=updateCreditReason', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    });
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

export async function deleteCreditReason(id) {
    const res = await fetch(`../api/setup-api.php?action=deleteCreditReason&id=${encodeURIComponent(id)}`, {
        method: 'POST',
        credentials: 'include'
    });
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

// --- Payment Term API ---
export async function getPaymentTerms() {
    const res = await fetch('../api/setup-api.php?action=getPaymentTerms');
    const data = await res.json();
    window.handleApiResponse(data);
}

export async function addPaymentTerm(formData) {
    const res = await fetch('../api/setup-api.php?action=addPaymentTerm', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();
    window.handleApiResponse(data);
}

export async function updatePaymentTerm(formData) {
    const res = await fetch('../api/setup-api.php?action=updatePaymentTerm', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();
    window.handleApiResponse(data);
}

export async function deletePaymentTerm(formData) {
    const res = await fetch('../api/setup-api.php?action=deletePaymentTerm', {
        method: 'POST',
        body: formData
    });
    const data = await res.json();
    window.handleApiResponse(data);
}

export async function getCreditPolicy() {
    const res = await fetch('../api/setup-api.php?action=getCreditPolicy', {
        credentials: 'include'
    });
    const data = await res.json();
    window.handleApiResponse(data);
}

export async function saveCreditPolicy(formData) {
    const res = await fetch('../api/setup-api.php?action=saveCreditPolicy', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    });
    const data = await res.json();
    window.handleApiResponse(data);
}

export async function getDocumentNumbering() {
    const res = await fetch('../api/setup-api.php?action=getDocumentNumbering', {
        credentials: 'include'
    });
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}

export async function saveDocumentNumbering(formData) {
    const res = await fetch('../api/setup-api.php?action=saveDocumentNumbering', {
        method: 'POST',
        body: formData,
        credentials: 'include'
    });
    const data = await res.json();
    window.handleApiResponse(data);
    return data;
}
