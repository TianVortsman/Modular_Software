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
                    <input type="text" id="supplier-name" name="supplier_name" class="form-input" required>
                </div>
                
                <div class="form-group">
                    <label for="supplier-email">Email</label>
                    <input type="email" id="supplier-email" name="supplier_email" class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="supplier-contact">Contact</label>
                    <input type="tel" id="supplier-contact" name="supplier_contact" class="form-input">
                </div>
                
                <div class="form-group">
                    <label for="supplier-address">Address</label>
                    <textarea id="supplier-address" name="supplier_address" class="form-textarea" rows="3"></textarea>
                </div>
                
                <div class="form-group">
                    <label for="supplier-website">Website</label>
                    <input type="url" id="supplier-website" name="website_url" class="form-input">
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeSupplierModal()">Cancel</button>
                    <button type="submit" class="btn-primary">
                        <span class="material-icons">save</span> Save Supplier
                    </button>
                </div>
            </form>
            <div id="supplier-contacts-section" style="display:none; margin-top:32px;">
                <h3>Contact Persons</h3>
                <button class="btn-primary" type="button" onclick="openSupplierContactModal()">
                    <span class="material-icons">add</span> Add Contact
                </button>
                <div id="supplier-contacts-list" class="data-list" style="margin-top:16px;"></div>
            </div>
        </div>
    </div>
</div>

<!-- Supplier Contact Modal -->
<div id="supplierContactModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 id="supplierContactModalTitle">Add Contact</h2>
            <span class="close" onclick="closeSupplierContactModal()">&times;</span>
        </div>
        <div class="modal-body">
            <form id="supplierContactForm" class="modal-form">
                <input type="hidden" id="contact-person-id" name="contact_person_id">
                <input type="hidden" id="contact-supplier-id" name="supplier_id">
                <div class="form-group">
                    <label for="contact-full-name">Full Name *</label>
                    <input type="text" id="contact-full-name" name="full_name" class="form-input" required>
                </div>
                <div class="form-group">
                    <label for="contact-position">Position</label>
                    <input type="text" id="contact-position" name="position" class="form-input">
                </div>
                <div class="form-group">
                    <label for="contact-email">Email</label>
                    <input type="email" id="contact-email" name="email" class="form-input">
                </div>
                <div class="form-group">
                    <label for="contact-phone">Phone</label>
                    <input type="tel" id="contact-phone" name="phone" class="form-input">
                </div>
                <div class="form-group">
                    <label for="contact-notes">Notes</label>
                    <textarea id="contact-notes" name="notes" class="form-textarea" rows="2"></textarea>
                </div>
                <div class="form-actions">
                    <button type="button" class="btn-secondary" onclick="closeSupplierContactModal()">Cancel</button>
                    <button type="submit" class="btn-primary">
                        <span class="material-icons">save</span> Save Contact
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>