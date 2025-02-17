<div class="add-sales-rep-modal" id="add-sales-rep-modal">
    <div class="modal-content add-sales-rep-modal-content">
        <div class="modal-header">
            <h2 class="modal-title">Add Sales Representative</h2>
            <button class="modal-close-button" id="add-sales-rep-modal-close-btn" onclick="closeAddSalesRepModal()">✖</button>
        </div>
        <form class="add-sales-rep-form" id="add-sales-rep-form">
            <div class="form-group">
                <label for="sales-rep-name" class="form-label">Sales Rep Name:</label>
                <input type="text" id="sales-rep-name" class="form-input" placeholder="Enter Name" required>
            </div>
            <div class="form-group">
                <label for="sales-rep-email" class="form-label">Email Address:</label>
                <input type="email" id="sales-rep-email" class="form-input" placeholder="Enter Email" required>
            </div>
            <div class="form-group">
                <label for="sales-rep-phone" class="form-label">Phone Number:</label>
                <input type="tel" id="sales-rep-phone" class="form-input" placeholder="Enter Phone Number">
            </div>
            <div class="form-group">
                <label for="sales-rep-region" class="form-label">Region:</label>
                <select id="sales-rep-region" class="form-select">
                    <option value="north">North</option>
                    <option value="south">South</option>
                    <option value="east">East</option>
                    <option value="west">West</option>
                </select>
            </div>
            <div class="form-group">
                <label for="sales-rep-picture" class="form-label">Profile Picture:</label>
                <div class="image-upload-container">
                    <input type="file" id="sales-rep-picture" class="form-input" accept="image/*" />
                    <img id="preview-image" src="#" alt="Profile Image Preview" class="image-preview" />
                </div>
            </div>
            <div class="form-group">
                <label for="sales-rep-bio" class="form-label">Biography:</label>
                <textarea id="sales-rep-bio" class="form-textarea" placeholder="Enter a short bio of the sales rep" rows="4"></textarea>
            </div>
            <div class="form-actions">
                <button type="submit" class="btn-submit" id="add-sales-rep-submit">Add Sales Rep</button>
                <button type="button" class="btn-cancel" id="add-sales-rep-cancel">Cancel</button>
            </div>
        </form>
    </div>
</div>
