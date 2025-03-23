<!-- Add Vehicle Modal -->
<div id="add-vehicle-modal" class="add-vehicle-modal hidden">
    <div class="add-vehicle-modal-content">
        <!-- Modal Header -->
        <div class="add-vehicle-modal-header">
            <h3 class="add-vehicle-modal-title">Add a New Vehicle</h3>
            <button class="add-vehicle-close-btn" onclick="toggleAddVehicleModal()">✖</button>
        </div>

        <!-- Modal Form -->
        <form id="add-vehicle-form" class="add-vehicle-form">
            <!-- Vehicle Details Section -->
            <fieldset class="add-vehicle-fieldset">
                <legend>Basic Vehicle Details</legend>
                <div class="add-vehicle-form-group">
                    <label for="add-vehicle-model" class="add-vehicle-label">Vehicle Model</label>
                    <input type="text" id="add-vehicle-model" name="vehicle_model" class="add-vehicle-input" placeholder="e.g., Toyota Corolla" required>
                </div>
                <div class="add-vehicle-form-group">
                    <label for="add-engine-type" class="add-vehicle-label">Engine Type</label>
                    <input type="text" id="add-engine-type" name="engine_type" class="add-vehicle-input" placeholder="e.g., V8" required>
                </div>
                <div class="add-vehicle-form-group">
                    <label for="add-color" class="add-vehicle-label">Color</label>
                    <input type="text" id="add-color" name="color" class="add-vehicle-input" placeholder="e.g., Red" required>
                </div>
                <div class="add-vehicle-form-group">
                    <label for="add-license-plate" class="add-vehicle-label">License Plate</label>
                    <input type="text" id="add-license-plate" name="license_plate" class="add-vehicle-input" placeholder="e.g., ABC-1234" required>
                </div>
                <div class="add-vehicle-form-group">
                    <label for="add-registration-number" class="add-vehicle-label">Registration Number</label>
                    <input type="text" id="add-registration-number" name="registration_number" class="add-vehicle-input" placeholder="e.g., 1HGBH41JXMN109186" required>
                </div>
                <div class="add-vehicle-form-group">
                    <label for="add-seat-type" class="add-vehicle-label">Seat Type</label>
                    <select id="add-seat-type" name="seat_type" class="add-vehicle-select" required>
                        <option value="" disabled selected>Select Seat Type</option>
                        <option value="Cloth">Cloth</option>
                        <option value="Leather">Leather</option>
                        <option value="Synthetic">Synthetic</option>
                    </select>
                </div>
            </fieldset>

            <!-- Additional Features Section -->
            <fieldset class="add-vehicle-fieldset">
                <legend>Additional Features</legend>
                <div class="add-vehicle-form-group">
                    <label for="add-extra-features" class="add-vehicle-label">Extra Features</label>
                    <textarea id="add-extra-features" name="extra_features" class="add-vehicle-textarea" placeholder="e.g., Cruise Control, Sunroof, Heated Seats"></textarea>
                </div>
                <div class="add-vehicle-form-group">
                    <label for="add-previous-owners" class="add-vehicle-label">Previous Owners (if any)</label>
                    <input type="number" id="add-previous-owners" name="previous_owners" class="add-vehicle-input" placeholder="e.g., 2">
                </div>
            </fieldset>

            <!-- Service History Section -->
            <fieldset class="add-vehicle-fieldset">
                <legend>Service History</legend>
                <div id="service-history-container" class="add-vehicle-form-group">
                    <label class="add-vehicle-label">Add Service History</label>
                    <div class="service-history-entry">
                        <input type="date" name="service_date[]" class="add-vehicle-input" placeholder="Service Date" required>
                        <input type="text" name="service_description[]" class="add-vehicle-input" placeholder="Service Description" required>
                        <button type="button" class="add-vehicle-add-service-btn" onclick="addServiceHistory()">+ Add Another</button>
                    </div>
                </div>
            </fieldset>

            <!-- Maintenance Plan Section -->
            <fieldset class="add-vehicle-fieldset">
                <legend>Maintenance Plan</legend>
                <div class="add-vehicle-form-group">
                    <label for="add-maintenance-plan" class="add-vehicle-label">Maintenance Plan Details</label>
                    <textarea id="add-maintenance-plan" name="maintenance_plan" class="add-vehicle-textarea" placeholder="e.g., Covers oil changes, tire rotations, etc."></textarea>
                </div>
                <div class="add-vehicle-form-group">
                    <label for="add-maintenance-expiry" class="add-vehicle-label">Maintenance Plan Expiry</label>
                    <input type="date" id="add-maintenance-expiry" name="maintenance_expiry" class="add-vehicle-input">
                </div>
            </fieldset>

            <!-- Image Upload Section -->
            <fieldset class="add-vehicle-fieldset">
                <legend>Upload Images</legend>
                <div class="add-vehicle-form-group">
                    <label for="add-vehicle-images" class="add-vehicle-label">Upload Vehicle Images</label>
                    <input type="file" id="add-vehicle-images" name="vehicle_images[]" class="add-vehicle-input-file" accept="image/*" multiple>
                    <span class="add-vehicle-tooltip">You can upload multiple images (JPG, PNG).</span>
                </div>
            </fieldset>

            <!-- Buttons -->
            <div class="add-vehicle-form-actions">
                <button type="button" class="add-vehicle-reset-btn" onclick="resetAddVehicleForm()">Reset Form</button>
                <button type="submit" class="add-vehicle-submit-btn">Add Vehicle</button>
            </div>
        </form>
    </div>
</div>
