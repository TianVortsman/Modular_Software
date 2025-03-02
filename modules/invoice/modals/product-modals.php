<!-- Universal Product Modal -->
<div class="universal-product-modal" id="universalProductModal">
  <div class="universal-product-modal-content">
    <span class="universal-product-modal-close">&times;</span>
    <h2 class="universal-product-modal-title" id="universalProductModalTitle">Product Details</h2>

    <form class="universal-product-modal-form" id="universalProductForm" enctype="multipart/form-data">
      <!-- Hidden fields for internal reference -->
      <input type="hidden" name="item_id" id="universalItemId">
      <input type="hidden" name="item_type" id="universalItemType" value="products">
      <input type="hidden" name="modal_mode" id="universalModalMode" value="add">

      <!-- Basic Information Section -->
      <fieldset class="universal-product-fieldset">
        <legend>Basic Information</legend>
        
        <!-- Item Name (dynamically labeled) -->
        <div class="universal-product-field">
          <label for="universalItemName" id="universalItemNameLabel">Product Name:</label>
          <input type="text" name="item_name" id="universalItemName" placeholder="Enter name" required>
        </div>

        <!-- Item Description (dynamically labeled) -->
        <div class="universal-product-field">
          <label for="universalItemDescr" id="universalItemDescrLabel">Description:</label>
          <textarea name="item_descr" id="universalItemDescr" placeholder="Short description here..."></textarea>
        </div>

        <!-- Price (dynamically labeled) -->
        <div class="universal-product-field">
          <label for="universalItemPrice" id="universalItemPriceLabel">Price:</label>
          <input type="number" step="0.01" name="item_price" id="universalItemPrice" placeholder="0.00" required>
        </div>

        <!-- SKU -->
        <div class="universal-product-field">
          <label for="universalItemSKU">SKU:</label>
          <input type="text" name="sku" id="universalItemSKU" placeholder="Stock Keeping Unit">
        </div>

        <!-- Barcode -->
        <div class="universal-product-field">
          <label for="universalItemBarcode">Barcode:</label>
          <input type="text" name="barcode" id="universalItemBarcode" placeholder="Barcode">
        </div>

        <!-- Status -->
        <div class="universal-product-field">
          <label for="universalItemStatus">Status:</label>
          <select name="status" id="universalItemStatus">
            <option value="active" selected>Active</option>
            <option value="inactive">Inactive</option>
            <option value="discontinued">Discontinued</option>
            <option value="out_of_stock">Out of Stock</option>
            <option value="on_order">On Order</option>
          </select>
        </div>
      </fieldset>

      <!-- Pricing and Tax Section -->
      <fieldset class="universal-product-fieldset">
        <legend>Pricing and Tax</legend>
        
        <!-- Cost Price -->
        <div class="universal-product-field">
          <label for="universalItemCostPrice">Cost Price:</label>
          <input type="number" step="0.01" name="cost_price" id="universalItemCostPrice" placeholder="0.00">
        </div>
        
        <!-- Retail Price -->
        <div class="universal-product-field">
          <label for="universalItemRetailPrice">Retail Price:</label>
          <input type="number" step="0.01" name="retail_price" id="universalItemRetailPrice" placeholder="0.00">
        </div>
        
        <!-- Wholesale Price -->
        <div class="universal-product-field">
          <label for="universalItemWholesalePrice">Wholesale Price:</label>
          <input type="number" step="0.01" name="wholesale_price" id="universalItemWholesalePrice" placeholder="0.00">
        </div>

        <!-- Tax Rate -->
        <div class="universal-product-field">
          <label for="universalItemTaxRate">Tax Rate (%):</label>
          <input type="number" step="0.01" name="tax_rate" id="universalItemTaxRate" placeholder="Tax %">
        </div>

        <!-- Discount -->
        <div class="universal-product-field">
          <label for="universalItemDiscount">Discount (%):</label>
          <input type="number" step="0.01" name="discount" id="universalItemDiscount" placeholder="Discount percentage">
        </div>
      </fieldset>

      <!-- Inventory Section -->
      <fieldset class="universal-product-fieldset">
        <legend>Inventory</legend>
        
        <!-- Stock Quantity -->
        <div class="universal-product-field">
          <label for="universalItemStock">Stock Quantity:</label>
          <input type="number" name="stock_quantity" id="universalItemStock" placeholder="0">
        </div>
        
        <!-- Reorder Level -->
        <div class="universal-product-field">
          <label for="universalItemReorderLevel">Reorder Level:</label>
          <input type="number" name="reorder_level" id="universalItemReorderLevel" placeholder="0">
        </div>
        
        <!-- Location -->
        <div class="universal-product-field">
          <label for="universalItemLocation">Storage Location:</label>
          <input type="text" name="location" id="universalItemLocation" placeholder="e.g., Warehouse A, Shelf B3">
        </div>
      </fieldset>

      <!-- Physical Attributes Section -->
      <fieldset class="universal-product-fieldset">
        <legend>Physical Attributes</legend>
        
        <!-- Brand -->
        <div class="universal-product-field">
          <label for="universalItemBrand">Brand:</label>
          <input type="text" name="brand" id="universalItemBrand" placeholder="Brand name">
        </div>

        <!-- Manufacturer -->
        <div class="universal-product-field">
          <label for="universalItemManufacturer">Manufacturer:</label>
          <input type="text" name="manufacturer" id="universalItemManufacturer" placeholder="Manufacturer">
        </div>

        <!-- Weight -->
        <div class="universal-product-field">
          <label for="universalItemWeight">Weight:</label>
          <input type="number" step="0.01" name="weight" id="universalItemWeight" placeholder="Weight in kg">
        </div>

        <!-- Dimensions -->
        <div class="universal-product-field">
          <label for="universalItemDimensions">Dimensions:</label>
          <input type="text" name="dimensions" id="universalItemDimensions" placeholder="e.g., 10x5x3 cm">
        </div>

        <!-- Color -->
        <div class="universal-product-field universal-product-field-vehicle">
          <label for="universalItemColor">Color:</label>
          <input type="text" name="color" id="universalItemColor" placeholder="e.g., Red, Blue, Black">
        </div>

        <!-- Material -->
        <div class="universal-product-field">
          <label for="universalItemMaterial">Material:</label>
          <input type="text" name="material" id="universalItemMaterial" placeholder="e.g., Plastic, Metal, Wood">
        </div>
      </fieldset>

      <!-- Warranty and Support Section -->
      <fieldset class="universal-product-fieldset">
        <legend>Warranty and Support</legend>
        
        <!-- Warranty Period -->
        <div class="universal-product-field">
          <label for="universalItemWarranty">Warranty Period:</label>
          <input type="text" name="warranty_period" id="universalItemWarranty" placeholder="e.g., 1 year, 2 years...">
        </div>
        
        <!-- Support Contact -->
        <div class="universal-product-field">
          <label for="universalItemSupport">Support Contact:</label>
          <input type="text" name="support_contact" id="universalItemSupport" placeholder="e.g., support@example.com">
        </div>
        
        <!-- Return Policy -->
        <div class="universal-product-field">
          <label for="universalItemReturnPolicy">Return Policy:</label>
          <textarea name="return_policy" id="universalItemReturnPolicy" placeholder="Return policy details..."></textarea>
        </div>
      </fieldset>

      <!-- Vehicle-Specific Fields (hidden by default) -->
      <fieldset class="universal-product-fieldset universal-product-fieldset-vehicle">
        <legend>Vehicle Details</legend>
        
        <!-- Engine Type -->
        <div class="universal-product-field universal-product-field-vehicle">
          <label for="universalItemEngineType">Engine Type:</label>
          <input type="text" name="engine_type" id="universalItemEngineType" placeholder="e.g., V8, Inline-4">
        </div>
        
        <!-- License Plate -->
        <div class="universal-product-field universal-product-field-vehicle">
          <label for="universalItemLicensePlate">License Plate:</label>
          <input type="text" name="license_plate" id="universalItemLicensePlate" placeholder="e.g., ABC-1234">
        </div>
        
        <!-- Registration Number -->
        <div class="universal-product-field universal-product-field-vehicle">
          <label for="universalItemRegistrationNumber">Registration Number:</label>
          <input type="text" name="registration_number" id="universalItemRegistrationNumber" placeholder="e.g., 1HGBH41JXMN109186">
        </div>
        
        <!-- Seat Type -->
        <div class="universal-product-field universal-product-field-vehicle">
          <label for="universalItemSeatType">Seat Type:</label>
          <select name="seat_type" id="universalItemSeatType">
            <option value="" disabled selected>Select Seat Type</option>
            <option value="Cloth">Cloth</option>
            <option value="Leather">Leather</option>
            <option value="Synthetic">Synthetic</option>
          </select>
        </div>
        
        <!-- Previous Owners -->
        <div class="universal-product-field universal-product-field-vehicle">
          <label for="universalItemPreviousOwners">Previous Owners:</label>
          <input type="number" name="previous_owners" id="universalItemPreviousOwners" placeholder="e.g., 2">
        </div>
        
        <!-- Extra Features -->
        <div class="universal-product-field universal-product-field-vehicle">
          <label for="universalItemExtraFeatures">Extra Features:</label>
          <textarea name="extra_features" id="universalItemExtraFeatures" placeholder="e.g., Cruise Control, Sunroof, Heated Seats"></textarea>
        </div>
      </fieldset>

      <!-- Service-Specific Fields (hidden by default) -->
      <fieldset class="universal-product-fieldset universal-product-fieldset-service">
        <legend>Service Details</legend>
        
        <!-- Duration -->
        <div class="universal-product-field universal-product-field-service">
          <label for="universalItemDuration">Duration:</label>
          <input type="text" name="duration" id="universalItemDuration" placeholder="e.g., 2 hours, 3 days">
        </div>
        
        <!-- Service Type -->
        <div class="universal-product-field universal-product-field-service">
          <label for="universalItemServiceType">Service Type:</label>
          <select name="service_type" id="universalItemServiceType">
            <option value="" disabled selected>Select Service Type</option>
            <option value="maintenance">Maintenance</option>
            <option value="repair">Repair</option>
            <option value="installation">Installation</option>
            <option value="consultation">Consultation</option>
            <option value="other">Other</option>
          </select>
        </div>
        
        <!-- Staff Required -->
        <div class="universal-product-field universal-product-field-service">
          <label for="universalItemStaffRequired">Staff Required:</label>
          <input type="number" name="staff_required" id="universalItemStaffRequired" placeholder="e.g., 2">
        </div>
        
        <!-- Certification Required -->
        <div class="universal-product-field universal-product-field-service">
          <label for="universalItemCertification">Certification Required:</label>
          <input type="text" name="certification" id="universalItemCertification" placeholder="e.g., ASE Certified, Licensed Plumber">
        </div>
      </fieldset>

      <!-- Parts-Specific Fields (hidden by default) -->
      <fieldset class="universal-product-fieldset universal-product-fieldset-part">
        <legend>Part Details</legend>
        
        <!-- Compatible Models -->
        <div class="universal-product-field universal-product-field-part">
          <label for="universalItemCompatibleModels">Compatible Models:</label>
          <textarea name="compatible_models" id="universalItemCompatibleModels" placeholder="e.g., Toyota Corolla 2018-2022, Honda Civic 2019-2023"></textarea>
        </div>
        
        <!-- OEM Number -->
        <div class="universal-product-field universal-product-field-part">
          <label for="universalItemOEMNumber">OEM Number:</label>
          <input type="text" name="oem_number" id="universalItemOEMNumber" placeholder="Original Equipment Manufacturer Number">
        </div>
        
        <!-- Condition -->
        <div class="universal-product-field universal-product-field-part">
          <label for="universalItemCondition">Condition:</label>
          <select name="condition" id="universalItemCondition">
            <option value="" disabled selected>Select Condition</option>
            <option value="new">New</option>
            <option value="used">Used</option>
            <option value="refurbished">Refurbished</option>
          </select>
        </div>
      </fieldset>

      <!-- Extras-Specific Fields (hidden by default) -->
      <fieldset class="universal-product-fieldset universal-product-fieldset-extra">
        <legend>Extra Details</legend>
        
        <!-- Category -->
        <div class="universal-product-field universal-product-field-extra">
          <label for="universalItemCategory">Category:</label>
          <select name="category" id="universalItemCategory">
            <option value="" disabled selected>Select Category</option>
            <option value="accessory">Accessory</option>
            <option value="add_on">Add-on</option>
            <option value="upgrade">Upgrade</option>
            <option value="other">Other</option>
          </select>
        </div>
        
        <!-- Installation Required -->
        <div class="universal-product-field universal-product-field-extra">
          <label for="universalItemInstallationRequired">Installation Required:</label>
          <select name="installation_required" id="universalItemInstallationRequired">
            <option value="yes">Yes</option>
            <option value="no">No</option>
          </select>
        </div>
        
        <!-- Installation Cost -->
        <div class="universal-product-field universal-product-field-extra">
          <label for="universalItemInstallationCost">Installation Cost:</label>
          <input type="number" step="0.01" name="installation_cost" id="universalItemInstallationCost" placeholder="0.00">
        </div>
      </fieldset>

      <!-- Image Upload Section -->
      <fieldset class="universal-product-fieldset">
        <legend>Images</legend>
        <div class="universal-product-field">
          <label for="universalItemImage" id="universalItemImageLabel">Product Image:</label>
          <input type="file" name="item_image" id="universalItemImage" accept="image/*">
          <div class="image-preview-container">
            <img id="universalItemImagePreview" src="https://placehold.co/300x300?text=No+Image" alt="Image preview" class="image-preview">
          </div>
        </div>
        
        <!-- Additional Images (for vehicles) -->
        <div class="universal-product-field universal-product-field-vehicle">
          <label for="universalItemAdditionalImages">Additional Images:</label>
          <input type="file" name="additional_images[]" id="universalItemAdditionalImages" accept="image/*" multiple>
          <div class="additional-images-preview" id="additionalImagesPreview">
            <!-- Additional image previews will be added here dynamically -->
          </div>
        </div>
      </fieldset>

      <!-- Additional Notes Section -->
      <fieldset class="universal-product-fieldset">
        <legend>Additional Notes</legend>
        <div class="universal-product-field">
          <label for="universalItemNotes">Notes:</label>
          <textarea name="notes" id="universalItemNotes" placeholder="Any additional information..."></textarea>
        </div>
      </fieldset>

      <!-- Submit Buttons -->
      <div class="universal-product-buttons">
        <button type="submit" class="btn-universal-product-save" id="universalProductSaveBtn">Save</button>
        <button type="button" class="btn-universal-product-cancel universal-product-modal-close" id="universalProductCancelBtn">Cancel</button>
        <button type="button" class="btn-universal-product-delete" id="universalProductDeleteBtn" style="display: none;">Delete</button>
      </div>
    </form>
  </div>
</div>

<!-- Loading Modal -->
<div id="loading-modal" class="loading-modal">
  <div class="loading-spinner"></div>
  <p>Processing your request...</p>
</div>

<!-- Response Modal -->
<div id="response-modal" class="response-modal">
  <div class="response-modal-content">
    <span class="response-modal-close">&times;</span>
    <h3 class="response-status">SUCCESS</h3>
    <p class="response-message">Operation completed successfully.</p>
  </div>
</div>