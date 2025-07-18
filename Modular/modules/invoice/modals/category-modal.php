<!-- Category Modal -->
<div id="categoryModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="categoryModalTitle">Add Category</h2>
            <span class="close" onclick="closeCategoryModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="categoryForm" class="modal-form">
                <input type="hidden" id="category-id" name="category_id">
                
                <div class="form-group">
                    <label for="category-name">Category Name *</label>
                    <input type="text" id="category-name" name="category_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="category-type">Product Type *</label>
                    <select id="category-type" name="product_type_id" class="form-select" required>
                    </select>
                </div>
                
                <div class="form-group">
                    <label for="category-description">Description</label>
                    <textarea id="category-description" name="category_description" class="form-textarea" rows="3"></textarea>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeCategoryModal()">Cancel</button>
                    <button type="submit" class="btn-primary">
                        <span class="material-icons">save</span> Save Category
                    </button>
                </div>
            </form>
        </div>
    </div>
</div> 