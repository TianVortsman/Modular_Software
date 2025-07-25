<!-- Universal Product Modal -->
<div class="universal-product-modal" id="universalProductModal">
  <div class="universal-product-modal-content">
    <button type="button" class="btn-universal-product-delete" id="universalProductDeleteBtn" style="position:absolute;top:18px;left:24px;z-index:11;">Delete</button>
    <span class="universal-product-modal-close">&times;</span>
    <h2 class="universal-product-modal-title" id="universalProductModalTitle">Product Details</h2>

    <form class="universal-product-modal-form" id="universalProductForm" enctype="multipart/form-data" novalidate>
      <!-- Hidden fields for internal reference -->
      <input type="hidden" name="product_id" id="universalItemId">
      <!-- <input type="hidden" name="product_type" id="universalItemType" value="products"> -->
      <input type="hidden" name="modal_mode" id="universalModalMode" value="add">

      <div class="upm-layout">
        <!-- Product Image Section (Fixed) -->
        <div class="upm-image-section">
          <div class="upm-image-dropzone" id="universalImageDropzone">
            <img id="universalItemImagePreview" src="https://placehold.co/300x300?text=No+Image" alt="Image preview" class="upm-image-preview">
            <div class="upm-dropzone-overlay">
              <div class="upm-dropzone-icon">
                <i class="fas fa-cloud-upload-alt"></i>
              </div>
              <div class="upm-dropzone-text">
                <span>Drag & drop image here</span>
                <span>or click to upload</span>
                <span class="upm-dropzone-hint">Supported formats: JPG, PNG, GIF, WEBP</span>
              </div>
            </div>
            <input type="file" name="item_image" id="universalItemImage" accept="image/jpeg,image/png,image/gif,image/webp" class="upm-image-input">
          </div>
          
          <!-- Essential Product Info -->
          <div class="upm-essential-info">
            <div class="upm-field">
              <label for="universalItemName">Product Name:</label>
              <input type="text" name="product_name" id="universalItemName" placeholder="Enter name" required>
            </div>
            <div class="upm-field">
              <label for="universalItemPrice">Price:</label>
              <input type="number" step="0.01" name="product_price" id="universalItemPrice" placeholder="0.00" required>
            </div>
            <div class="upm-field">
              <label for="universalItemStatus">Status:</label>
              <select name="product_status" id="universalItemStatus">
                <option value="active" selected>Active</option>
                <option value="inactive">Inactive</option>
                <option value="discontinued">Discontinued</option>
                <option value="out_of_stock">Out of Stock</option>
                <option value="on_order">On Order</option>
              </select>
            </div>
          </div>
        </div>

        <!-- Tabbed Content Section -->
        <div class="upm-tabs-section">
          <!-- Tab Navigation -->
          <div class="upm-tab-nav">
            <button type="button" class="upm-tab-btn upm-active" data-tab="basic">Basic Info</button>
            <button type="button" class="upm-tab-btn" data-tab="pricing">Pricing & Tax</button>
            <button type="button" class="upm-tab-btn" data-tab="inventory">Inventory</button>
            <button type="button" class="upm-tab-btn" data-tab="attributes">Attributes</button>
            <button type="button" class="upm-tab-btn" data-tab="specific">Type Specific</button>
            <button type="button" class="upm-tab-btn" data-tab="notes">Notes</button>
            <button type="button" class="upm-tab-btn" data-tab="suppliers">Suppliers</button>
            <button type="button" class="upm-tab-btn" data-tab="stock-history">Stock History</button>
          </div>

          <!-- Tab Content -->
          <div class="upm-tab-content">
            <!-- Basic Information Tab -->
            <div class="upm-tab-pane upm-active" id="upm-tab-basic">
              <div class="upm-field">
                <label for="universalItemDescr">Description:</label>
                <textarea name="product_descr" id="universalItemDescr" placeholder="Short description here..."></textarea>
              </div>
              <div class="upm-field">
                <label for="universalItemSKU">SKU:</label>
                <input type="text" name="sku" id="universalItemSKU" placeholder="Stock Keeping Unit">
              </div>
              <div class="upm-field">
                <label for="universalItemBarcode">Barcode:</label>
                <input type="text" name="barcode" id="universalItemBarcode" placeholder="Barcode">
              </div>
              <div class="upm-field">
                <label for="universalItemType">Type:</label>
                <select name="product_type_id" id="universalItemType" required></select>
              </div>
              <div class="upm-field">
                <label for="universalItemSupplier">Supplier:</label>
                <select name="supplier_id" id="universalItemSupplier"></select>
              </div>
              <div class="upm-field">
                <label for="universalItemCategory">Category:</label>
                <select name="category_id" id="universalItemCategory"></select>
              </div>
              <div class="upm-field">
                <label for="universalItemSubcategory">Subcategory:</label>
                <select name="subcategory_id" id="universalItemSubcategory"></select>
              </div>
            </div>

            <!-- Pricing and Tax Tab -->
            <div class="upm-tab-pane" id="upm-tab-pricing">
              <div class="upm-field">
                <label for="universalItemTaxRate">Tax Rate:</label>
                <select name="tax_rate_id" id="universalItemTaxRate">
                  <option value="">Select Tax Rate</option>
                </select>
              </div>
              <div class="upm-field">
                <label for="universalItemDiscount">Discount (%):</label>
                <input type="number" step="0.01" name="discount" id="universalItemDiscount" placeholder="Discount percentage">
              </div>
              <div class="upm-field">
                <label for="universalItemLaborCost">Labor Cost:</label>
                <input type="number" step="0.01" name="labor_cost" id="universalItemLaborCost" placeholder="0.00">
              </div>
            </div>

            <!-- Inventory Tab -->
            <div class="upm-tab-pane" id="upm-tab-inventory">
              <div class="upm-field">
                <label for="universalItemStock">Stock Quantity:</label>
                <input type="text" name="stock_quantity" id="universalItemStock" placeholder="0">
              </div>
              <div class="upm-field">
                <label for="universalItemReorderLevel">Reorder Level:</label>
                <input type="number" name="reorder_level" id="universalItemReorderLevel" placeholder="0">
              </div>
              <div class="upm-field">
                <label for="universalItemLeadTime">Lead Time (days):</label>
                <input type="number" name="lead_time" id="universalItemLeadTime" placeholder="0">
              </div>
            </div>

            <!-- Physical Attributes Tab -->
            <div class="upm-tab-pane" id="upm-tab-attributes">
              <div class="upm-field">
                <label for="universalItemBrand">Brand:</label>
                <input type="text" name="brand" id="universalItemBrand" placeholder="Brand name">
              </div>
              <div class="upm-field">
                <label for="universalItemManufacturer">Manufacturer:</label>
                <input type="text" name="manufacturer" id="universalItemManufacturer" placeholder="Manufacturer">
              </div>
              <div class="upm-field">
                <label for="universalItemWeight">Weight:</label>
                <input type="number" step="0.01" name="product_weight" id="universalItemWeight" placeholder="Weight in kg">
              </div>
              <div class="upm-field">
                <label for="universalItemDimensions">Dimensions:</label>
                <input type="text" name="dimensions" id="universalItemDimensions" placeholder="e.g., 10x5x3 cm">
              </div>
              <div class="upm-field">
                <label for="universalItemMaterial">Material:</label>
                <input type="text" name="product_material" id="universalItemMaterial" placeholder="e.g., Plastic, Metal, Wood">
              </div>
              <div class="upm-field">
                <label for="universalItemWarranty">Warranty Period:</label>
                <input type="text" name="warranty_period" id="universalItemWarranty" placeholder="e.g., 1 year, 2 years...">
              </div>
            </div>

            <!-- Type Specific Tab -->
            <div class="upm-tab-pane" id="upm-tab-specific">
              <!-- Parts-Specific Fields -->
              <div class="universal-product-fieldset-part">
                <h3>Part Details</h3>
                <div class="upm-field universal-product-field-part">
                  <label for="universalItemCompatibleVehicles">Compatible Vehicles:</label>
                  <textarea name="compatible_vehicles" id="universalItemCompatibleVehicles" placeholder="e.g., Toyota Corolla 2018-2022, Honda Civic 2019-2023"></textarea>
                </div>
                <div class="upm-field universal-product-field-part">
                  <label for="universalItemOEMNumber">OEM Part Number:</label>
                  <input type="text" name="oem_part_number" id="universalItemOEMNumber" placeholder="Original Equipment Manufacturer Number">
                </div>
              </div>

              <!-- Service-Specific Fields -->
              <div class="universal-product-fieldset-service">
                <h3>Service Details</h3>
                <div class="upm-field universal-product-field-service">
                  <label for="universalItemEstimatedTime">Estimated Time:</label>
                  <input type="text" name="estimated_time" id="universalItemEstimatedTime" placeholder="e.g., 2 hours, 3 days">
                </div>
                <div class="upm-field universal-product-field-service">
                  <label for="universalItemServiceFrequency">Service Frequency:</label>
                  <input type="text" name="service_frequency" id="universalItemServiceFrequency" placeholder="e.g., Monthly, Quarterly, Annually">
                </div>
              </div>

              <!-- Bundle Information -->
              <div class="upm-bundle-section">
                <h3>Bundle Information</h3>
                <div class="upm-field">
                  <label for="universalItemBundleItems">Bundle Items:</label>
                  <textarea name="bundle_items" id="universalItemBundleItems" placeholder="List of items included in this bundle..."></textarea>
                </div>
                <div class="upm-field">
                  <label for="universalItemInstallationRequired">Installation Required:</label>
                  <select name="installation_required" id="universalItemInstallationRequired">
                    <option value="true">Yes</option>
                    <option value="false" selected>No</option>
                  </select>
                </div>
              </div>
            </div>

            <!-- Notes Tab -->
            <div class="upm-tab-pane" id="upm-tab-notes">
              <div class="upm-field">
                <label for="universalItemNotes">Additional Notes:</label>
                <textarea name="notes" id="universalItemNotes" placeholder="Any additional information..." class="upm-notes-textarea"></textarea>
              </div>
            </div>

            <!-- Suppliers Tab -->
            <div class="upm-tab-pane" id="upm-tab-suppliers">
              <h3>Suppliers for this Product</h3>
              <div id="product-suppliers-list">
                <!-- JS will populate supplier cards here -->
              </div>
              <template id="product-supplier-card-template">
                <div class="product-supplier-card">
                  <div class="supplier-main-info">
                    <span class="supplier-name"></span>
                    <span class="supplier-contact"></span>
                    <span class="supplier-email"></span>
                    <span class="supplier-website"></span>
                  </div>
                  <div class="supplier-stock-info">
                    <span class="supplier-total-stock"></span>
                    <span class="supplier-last-restock-date"></span>
                    <span class="supplier-last-price"></span>
                  </div>
                  <div class="supplier-price-trend">
                    <!-- Placeholder for mini FIFO table or sparkline chart -->
                  </div>
                </div>
              </template>
              <div class="supplier-fifo-table-placeholder">
                <!-- Placeholder: JS will render FIFO table or chart per supplier here -->
              </div>
            </div>

            <!-- Stock History Tab -->
            <div class="upm-tab-pane" id="upm-tab-stock-history">
              <h3>Stock History</h3>
              <button type="button" class="btn-adjust-stock" id="openAdjustStockModalBtn">Adjust Stock</button>
              <div id="product-stock-history-list">
                <!-- JS will populate stock history rows here -->
              </div>
              <template id="product-stock-history-row-template">
                <div class="product-stock-history-row">
                  <span class="stock-supplier-name"></span>
                  <span class="stock-quantity"></span>
                  <span class="stock-remaining"></span>
                  <span class="stock-cost"></span>
                  <span class="stock-date"></span>
                  <span class="stock-notes"></span>
                </div>
              </template>
            </div>
          </div>
        </div>
      </div>

      <!-- Submit Buttons -->
      <div class="universal-product-buttons">
        <button type="button" class="btn-universal-product-cancel" id="universalProductCancelBtn">Cancel</button>
        <button type="submit" class="btn-universal-product-save" id="universalProductSaveBtn">Save</button>
      </div>
    </form>
  </div>
</div>

              <!-- Manual Stock Adjustment Modal (hidden by default) -->
              <div class="adjust-stock-modal" id="adjustStockModal" style="display:none;">
                <div class="adjust-stock-modal-content">
                  <span class="adjust-stock-modal-close" id="closeAdjustStockModalBtn">&times;</span>
                  <h4>Adjust Stock</h4>
                  <form id="adjustStockForm">
                    <div class="adjust-stock-field">
                      <label for="adjustStockSupplier">Supplier:</label>
                      <select id="adjustStockSupplier" name="supplier_id" ></select>
                    </div>
                    <div class="adjust-stock-field">
                      <label for="adjustStockQuantity">Quantity (use negative for removal):</label>
                      <input type="number" id="adjustStockQuantity" name="quantity" >
                    </div>
                    <div class="adjust-stock-field">
                      <label for="adjustStockCost">Cost per Unit (optional):</label>
                      <input type="number" step="0.01" id="adjustStockCost" name="cost_per_unit">
                    </div>
                    <div class="adjust-stock-field">
                      <label for="adjustStockNotes">Notes (optional):</label>
                      <input type="text" id="adjustStockNotes" name="notes">
                    </div>
                    <div class="adjust-stock-actions">
                      <button type="submit" class="btn-primary">Submit Adjustment</button>
                      <button type="button" class="btn-secondary" id="cancelAdjustStockBtn">Cancel</button>
                    </div>
                  </form>
                </div>
              </div>
              <!-- JS will handle opening, submitting, and closing the adjustment modal -->