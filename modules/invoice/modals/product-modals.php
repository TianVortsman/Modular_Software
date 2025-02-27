<!-- Product Details Modal -->
<div class="modal-product-details" id="modalProductDetails">
  <div class="modal-product-details-content">
    <span class="modal-product-details-close">&times;</span>
    <h2 class="modal-product-details-title">Product Details</h2>

    <form class="modal-product-details-form">
      <!-- Hidden field (for internal reference, e.g. updating existing product) -->
      <input type="hidden" name="prod_id" id="modalProductId" />

      <!-- Product Name -->
      <div class="modal-product-details-field">
        <label for="modalProductName">Product Name:</label>
        <input type="text" name="prod_name" id="modalProductName" placeholder="Enter product name" required>
      </div>

      <!-- Product Description -->
      <div class="modal-product-details-field">
        <label for="modalProductDescr">Description:</label>
        <textarea name="prod_descr" id="modalProductDescr" placeholder="Short description here..."></textarea>
      </div>

      <!-- Price -->
      <div class="modal-product-details-field">
        <label for="modalProductPrice">Price:</label>
        <input type="number" step="0.01" name="prod_price" id="modalProductPrice" placeholder="0.00" required>
      </div>

      <!-- SKU -->
      <div class="modal-product-details-field">
        <label for="modalProductSKU">SKU:</label>
        <input type="text" name="sku" id="modalProductSKU" placeholder="Stock Keeping Unit">
      </div>

      <!-- Barcode -->
      <div class="modal-product-details-field">
        <label for="modalProductBarcode">Barcode:</label>
        <input type="text" name="barcode" id="modalProductBarcode" placeholder="Barcode">
      </div>

      <!-- Brand -->
      <div class="modal-product-details-field">
        <label for="modalProductBrand">Brand:</label>
        <input type="text" name="brand" id="modalProductBrand" placeholder="Brand name">
      </div>

      <!-- Manufacturer -->
      <div class="modal-product-details-field">
        <label for="modalProductManufacturer">Manufacturer:</label>
        <input type="text" name="manufacturer" id="modalProductManufacturer" placeholder="Manufacturer">
      </div>

      <!-- Weight -->
      <div class="modal-product-details-field">
        <label for="modalProductWeight">Weight:</label>
        <input type="number" step="0.01" name="weight" id="modalProductWeight" placeholder="Weight in kg/lbs">
      </div>

      <!-- Dimensions -->
      <div class="modal-product-details-field">
        <label for="modalProductDimensions">Dimensions:</label>
        <input type="text" name="dimensions" id="modalProductDimensions" placeholder="e.g., 10x5x3">
      </div>

      <!-- Warranty Period -->
      <div class="modal-product-details-field">
        <label for="modalProductWarranty">Warranty Period:</label>
        <input type="text" name="warranty_period" id="modalProductWarranty" placeholder="e.g., 1 year, 2 years...">
      </div>

      <!-- Tax Rate -->
      <div class="modal-product-details-field">
        <label for="modalProductTaxRate">Tax Rate:</label>
        <input type="number" step="0.01" name="tax_rate" id="modalProductTaxRate" placeholder="Tax %">
      </div>

      <!-- Discount -->
      <div class="modal-product-details-field">
        <label for="modalProductDiscount">Discount:</label>
        <input type="number" step="0.01" name="discount" id="modalProductDiscount" placeholder="Discount amount">
      </div>

      <!-- Status -->
      <div class="modal-product-details-field">
        <label for="modalProductStatus">Status:</label>
        <select name="status" id="modalProductStatus">
          <option value="active" selected>Active</option>
          <option value="inactive">Inactive</option>
          <option value="discontinued">Discontinued</option>
        </select>
      </div>

      <!-- Submit Button -->
      <div class="modal-product-details-buttons">
        <button type="submit" class="btn-modal-product-save">Save</button>
        <button type="button" class="btn-modal-product-cancel modal-product-details-close">Cancel</button>
      </div>
    </form>
  </div>
</div>
