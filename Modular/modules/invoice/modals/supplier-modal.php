<!-- Supplier Modal -->
<div id="supplierModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="supplierModalTitle">Add Supplier</h2>
            <span class="close" onclick="closeSupplierModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="supplierForm" class="modal-form">
                <input type="hidden" id="supplier-id" name="supplier_id">
                
                <div class="form-group">
                    <label for="supplier-name">Supplier Name *</label>
                    <input type="text" id="supplier-name" name="name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="supplier-email">Email</label>
                    <input type="email" id="supplier-email" name="email" class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="supplier-phone">Phone</label>
                    <input type="tel" id="supplier-phone" name="phone" class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="supplier-address">Address</label>
                    <textarea id="supplier-address" name="address" class="form-textarea" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="supplier-contact-person">Contact Person</label>
                    <input type="text" id="supplier-contact-person" name="contact_person" class="form-input">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeSupplierModal()">Cancel</button>
                    <button type="submit" class="btn-primary">
                        <span class="material-icons">save</span> Save Supplier
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openSupplierModal(supplierId = null) {
    const modal = document.getElementById('supplierModal');
    const title = document.getElementById('supplierModalTitle');
    const form = document.getElementById('supplierForm');
    
    // Reset form
    form.reset();
    document.getElementById('supplier-id').value = '';
    
    if (supplierId) {
        title.textContent = 'Edit Supplier';
        // Load supplier data for editing
        loadSupplierData(supplierId);
    } else {
        title.textContent = 'Add Supplier';
    }
    
    modal.style.display = 'block';
}

function closeSupplierModal() {
    const modal = document.getElementById('supplierModal');
    modal.style.display = 'none';
}

function loadSupplierData(supplierId) {
    // This would load existing supplier data for editing
    // Implementation depends on your data loading approach
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('supplierModal');
    if (event.target === modal) {
        closeSupplierModal();
    }
}
</script> 