<!-- Product Details Modal -->
<div class="modal-product-details" id="modalProductDetails">
  <div class="modal-product-details-content">
    <span class="modal-product-details-close">&times;</span>
    <h2 class="modal-product-details-title">Product Details</h2>

    <form class="modal-product-details-form">
      <!-- Hidden field (for internal reference, e.g. updating existing product) -->
      <input type="hidden" name="prod_id" id="modalProductId" />
      <!-- Product Type -->
      <input type="hidden" name="prod_type" id="modalProductType" value="products">

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

            <!-- Product Image -->
      <div class="modal-product-details-field">
        <label for="modalProductImage">Product Image:</label>
        <input type="file" name="product_image" id="modalProductImage" accept="image/*">
        <div class="image-preview-container">
          <img id="modalProductImagePreview" src="https://placehold.co/300x300?text=No+Image" alt="Image preview" class="image-preview">
        </div>
      </div>

      <!-- Submit Button -->
      <div class="modal-product-details-buttons">
        <button type="submit" class="btn-modal-product-save">Save</button>
        <button type="button" class="btn-modal-product-cancel modal-product-details-close">Cancel</button>
      </div>
    </form>
  </div>
</div>



<!-- Add Product Modal -->
<div class="modal-add-product" id="modalAddProduct">
  <div class="modal-add-product-content">
    <span class="modal-add-product-close">&times;</span>
    <h2 class="modal-add-product-title">Add New Product</h2>

    <form class="modal-add-product-form" id="modal-add-product-form">
      <!-- Product Name -->
      <div class="modal-add-product-field">
        <label for="addProductName">Product Name:</label>
        <input type="text" name="prod_name" id="addProductName" placeholder="Enter product name" required>
      </div>

      <!-- Product Description -->
      <div class="modal-add-product-field">
        <label for="addProductDescr">Description:</label>
        <textarea name="prod_descr" id="addProductDescr" placeholder="Short description here..."></textarea>
      </div>

      <!-- Price -->
      <div class="modal-add-product-field">
        <label for="addProductPrice">Price:</label>
        <input type="number" step="0.01" name="prod_price" id="addProductPrice" placeholder="0.00" required>
      </div>

      <!-- SKU -->
      <div class="modal-add-product-field">
        <label for="addProductSKU">SKU:</label>
        <input type="text" name="sku" id="addProductSKU" placeholder="Stock Keeping Unit">
      </div>

      <!-- Barcode -->
      <div class="modal-add-product-field">
        <label for="addProductBarcode">Barcode:</label>
        <input type="text" name="barcode" id="addProductBarcode" placeholder="Barcode">
      </div>

      <!-- Brand -->
      <div class="modal-add-product-field">
        <label for="addProductBrand">Brand:</label>
        <input type="text" name="brand" id="addProductBrand" placeholder="Brand name">
      </div>

      <!-- Manufacturer -->
      <div class="modal-add-product-field">
        <label for="addProductManufacturer">Manufacturer:</label>
        <input type="text" name="manufacturer" id="addProductManufacturer" placeholder="Manufacturer">
      </div>

      <!-- Weight -->
      <div class="modal-add-product-field">
        <label for="addProductWeight">Weight:</label>
        <input type="number" step="0.01" name="weight" id="addProductWeight" placeholder="Weight in kg/lbs">
      </div>

      <!-- Dimensions -->
      <div class="modal-add-product-field">
        <label for="addProductDimensions">Dimensions:</label>
        <input type="text" name="dimensions" id="addProductDimensions" placeholder="e.g., 10x5x3">
      </div>

      <!-- Warranty Period -->
      <div class="modal-add-product-field">
        <label for="addProductWarranty">Warranty Period:</label>
        <input type="text" name="warranty_period" id="addProductWarranty" placeholder="e.g., 1 year, 2 years...">
      </div>

      <!-- Tax Rate -->
      <div class="modal-add-product-field">
        <label for="addProductTaxRate">Tax Rate:</label>
        <input type="number" step="0.01" name="tax_rate" id="addProductTaxRate" placeholder="Tax %">
      </div>

      <!-- Discount -->
      <div class="modal-add-product-field">
        <label for="addProductDiscount">Discount:</label>
        <input type="number" step="0.01" name="discount" id="addProductDiscount" placeholder="Discount amount">
      </div>

      <!-- Status -->
      <div class="modal-add-product-field">
        <label for="addProductStatus">Status:</label>
        <select name="status" id="addProductStatus">
          <option value="active" selected>Active</option>
          <option value="inactive">Inactive</option>
          <option value="discontinued">Discontinued</option>
        </select>
      </div>

      <!-- Product Image Upload -->
      <div class="modal-add-product-field">
        <label for="addProductImage">Product Image:</label>
        <input type="file" name="product_image" id="addProductImage" accept="image/*">
        <div class="image-preview-container">
          <img id="addProductImagePreview" src="https://placehold.co/300x300?text=No+Image" alt="Image preview" class="image-preview">
        </div>
      </div>

      <!-- Submit Button -->
      <div class="modal-add-product-buttons">
        <button type="submit" class="btn-modal-add-product-save">Add Product</button>
        <button type="button" class="btn-modal-add-product-cancel modal-add-product-close">Cancel</button>
      </div>
    </form>
  </div>
</div>