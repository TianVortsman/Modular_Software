<!-- Subcategory Modal -->
<div id="subcategoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="subcategoryModalTitle">Add Subcategory</h2>
            <span class="close" onclick="closeSubcategoryModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="subcategoryForm" class="modal-form">
                <input type="hidden" id="subcategory-id" name="subcategory_id">
                
                <div class="form-group">
                    <label for="subcategory-name">Subcategory Name *</label>
                    <input type="text" id="subcategory-name" name="subcategory_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="subcategory-category">Parent Category *</label>
                    <select id="subcategory-category" name="category_id" class="form-select" required>
                        <option value="">Select Category</option>
                        <!-- Categories will be loaded dynamically -->
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="subcategory-description">Description</label>
                    <textarea id="subcategory-description" name="description" class="form-textarea" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeSubcategoryModal()">Cancel</button>
                    <button type="submit" class="btn-primary">
                        <span class="material-icons">save</span> Save Subcategory
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function openSubcategoryModal(subcategoryId = null) {
    const modal = document.getElementById('subcategoryModal');
    const title = document.getElementById('subcategoryModalTitle');
    const form = document.getElementById('subcategoryForm');
    
    // Reset form
    form.reset();
    document.getElementById('subcategory-id').value = '';
    
    // Load categories for the dropdown
    loadCategoriesForSubcategory();
    
    if (subcategoryId) {
        title.textContent = 'Edit Subcategory';
        // Load subcategory data for editing
        loadSubcategoryData(subcategoryId);
    } else {
        title.textContent = 'Add Subcategory';
    }
    
    modal.style.display = 'block';
}

function closeSubcategoryModal() {
    const modal = document.getElementById('subcategoryModal');
    modal.style.display = 'none';
}

function loadCategoriesForSubcategory() {
    // This would load categories from the core schema
    // Implementation depends on your data loading approach
    const categorySelect = document.getElementById('subcategory-category');
    // Clear existing options except the first one
    categorySelect.innerHTML = '<option value="">Select Category</option>';
    
    // Load categories from API and populate dropdown
    // This is a placeholder - implement based on your API structure
}

function loadSubcategoryData(subcategoryId) {
    // This would load existing subcategory data for editing
    // Implementation depends on your data loading approach
}

// Close modal when clicking outside
window.onclick = function(event) {
    const modal = document.getElementById('subcategoryModal');
    if (event.target === modal) {
        closeSubcategoryModal();
    }
}
</script> 