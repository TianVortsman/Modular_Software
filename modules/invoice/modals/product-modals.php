<!-- Universal Product Modal -->
<div class="universal-product-modal" id="universalProductModal">
  <div class="universal-product-modal-content">
    <span class="universal-product-modal-close">&times;</span>
    <h2 class="universal-product-modal-title" id="universalProductModalTitle">Product Details</h2>

    <form class="universal-product-modal-form" id="universalProductForm" enctype="multipart/form-data">
      <!-- Hidden fields for internal reference -->
      <input type="hidden" name="prod_id" id="universalItemId">
      <input type="hidden" name="product_type" id="universalItemType" value="products">
      <input type="hidden" name="modal_mode" id="universalModalMode" value="add">

      <!-- Basic Information Section -->
      <fieldset class="universal-product-fieldset">
        <legend>Basic Information</legend>
        
        <!-- Product Name -->
        <div class="universal-product-field">
          <label for="universalItemName">Product Name:</label>
          <input type="text" name="prod_name" id="universalItemName" placeholder="Enter name" required>
        </div>

        <!-- Product Description -->
        <div class="universal-product-field">
          <label for="universalItemDescr">Description:</label>
          <textarea name="prod_descr" id="universalItemDescr" placeholder="Short description here..."></textarea>
        </div>

        <!-- Price -->
        <div class="universal-product-field">
          <label for="universalItemPrice">Price:</label>
          <input type="number" step="0.01" name="prod_price" id="universalItemPrice" placeholder="0.00" required>
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
        
        <!-- Category -->
        <div class="universal-product-field">
          <label for="universalItemCategory">Category:</label>
          <input type="text" name="category" id="universalItemCategory" placeholder="Product Category">
        </div>
        
        <!-- Sub-Category -->
        <div class="universal-product-field">
          <label for="universalItemSubCategory">Sub-Category:</label>
          <input type="text" name="sub_category" id="universalItemSubCategory" placeholder="Product Sub-Category">
        </div>
      </fieldset>

      <!-- Pricing and Tax Section -->
      <fieldset class="universal-product-fieldset">
        <legend>Pricing and Tax</legend>
        
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
        
        <!-- Labor Cost -->
        <div class="universal-product-field">
          <label for="universalItemLaborCost">Labor Cost:</label>
          <input type="number" step="0.01" name="labor_cost" id="universalItemLaborCost" placeholder="0.00">
        </div>
      </fieldset>

      <!-- Inventory Section -->
      <fieldset class="universal-product-fieldset">
        <legend>Inventory</legend>
        
        <!-- Stock Quantity -->
        <div class="universal-product-field">
          <label for="universalItemStock">Stock Quantity:</label>
          <input type="text" name="stock_quantity" id="universalItemStock" placeholder="0">
        </div>
        
        <!-- Reorder Level -->
        <div class="universal-product-field">
          <label for="universalItemReorderLevel">Reorder Level:</label>
          <input type="number" name="reorder_level" id="universalItemReorderLevel" placeholder="0">
        </div>
        
        <!-- Lead Time -->
        <div class="universal-product-field">
          <label for="universalItemLeadTime">Lead Time (days):</label>
          <input type="number" name="lead_time" id="universalItemLeadTime" placeholder="0">
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
      </fieldset>

      <!-- Parts-Specific Fields -->
      <fieldset class="universal-product-fieldset universal-product-fieldset-part">
        <legend>Part Details</legend>
        
        <!-- Compatible Vehicles -->
        <div class="universal-product-field universal-product-field-part">
          <label for="universalItemCompatibleVehicles">Compatible Vehicles:</label>
          <textarea name="compatible_vehicles" id="universalItemCompatibleVehicles" placeholder="e.g., Toyota Corolla 2018-2022, Honda Civic 2019-2023"></textarea>
        </div>
        
        <!-- OEM Part Number -->
        <div class="universal-product-field universal-product-field-part">
          <label for="universalItemOEMNumber">OEM Part Number:</label>
          <input type="text" name="oem_part_number" id="universalItemOEMNumber" placeholder="Original Equipment Manufacturer Number">
        </div>
      </fieldset>

      <!-- Service-Specific Fields -->
      <fieldset class="universal-product-fieldset universal-product-fieldset-service">
        <legend>Service Details</legend>
        
        <!-- Estimated Time -->
        <div class="universal-product-field universal-product-field-service">
          <label for="universalItemEstimatedTime">Estimated Time:</label>
          <input type="text" name="estimated_time" id="universalItemEstimatedTime" placeholder="e.g., 2 hours, 3 days">
        </div>
        
        <!-- Service Frequency -->
        <div class="universal-product-field universal-product-field-service">
          <label for="universalItemServiceFrequency">Service Frequency:</label>
          <input type="text" name="service_frequency" id="universalItemServiceFrequency" placeholder="e.g., Monthly, Quarterly, Annually">
        </div>
      </fieldset>

      <!-- Bundle Items -->
      <fieldset class="universal-product-fieldset">
        <legend>Bundle Information</legend>
        <div class="universal-product-field">
          <label for="universalItemBundleItems">Bundle Items:</label>
          <textarea name="bundle_items" id="universalItemBundleItems" placeholder="List of items included in this bundle..."></textarea>
        </div>
        
        <!-- Installation Required -->
        <div class="universal-product-field">
          <label for="universalItemInstallationRequired">Installation Required:</label>
          <select name="installation_required" id="universalItemInstallationRequired">
            <option value="true">Yes</option>
            <option value="false" selected>No</option>
          </select>
        </div>
      </fieldset>

      <!-- Image Upload Section -->
      <fieldset class="universal-product-fieldset">
        <legend>Images</legend>
        <div class="universal-product-field">
          <label for="universalItemImage">Product Image:</label>
          <input type="file" name="item_image" id="universalItemImage" accept="image/*">
          <div class="image-preview-container">
            <img id="universalItemImagePreview" src="https://placehold.co/300x300?text=No+Image" alt="Image preview" class="image-preview">
          </div>
          <input type="hidden" name="image_url" id="universalItemImageUrl">
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