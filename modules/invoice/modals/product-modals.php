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

<!-- Part Details Modal -->
<div class="modal-part-details" id="modalPartDetails">
  <div class="modal-part-details-content">
    <span class="modal-part-details-close">&times;</span>
    <h2 class="modal-part-details-title">Part Details</h2>

    <form class="modal-part-details-form">
      <!-- Hidden field (for internal reference, e.g. updating existing part) -->
      <input type="hidden" name="part_id" id="modalPartId" />

      <!-- Part Name -->
      <div class="modal-part-details-field">
        <label for="modalPartName">Part Name:</label>
        <input type="text" name="part_name" id="modalPartName" placeholder="Enter part name" required>
      </div>

      <!-- Part Description -->
      <div class="modal-part-details-field">
        <label for="modalPartDescr">Description:</label>
        <textarea name="part_descr" id="modalPartDescr" placeholder="Short description here..."></textarea>
      </div>

      <!-- Price -->
      <div class="modal-part-details-field">
        <label for="modalPartPrice">Price:</label>
        <input type="number" step="0.01" name="part_price" id="modalPartPrice" placeholder="0.00" required>
      </div>

      <!-- SKU -->
      <div class="modal-part-details-field">
        <label for="modalPartSKU">SKU:</label>
        <input type="text" name="sku" id="modalPartSKU" placeholder="Stock Keeping Unit">
      </div>

      <!-- Barcode -->
      <div class="modal-part-details-field">
        <label for="modalPartBarcode">Barcode:</label>
        <input type="text" name="barcode" id="modalPartBarcode" placeholder="Barcode">
      </div>

      <!-- Brand -->
      <div class="modal-part-details-field">
        <label for="modalPartBrand">Brand:</label>
        <input type="text" name="brand" id="modalPartBrand" placeholder="Brand name">
      </div>

      <!-- Manufacturer -->
      <div class="modal-part-details-field">
        <label for="modalPartManufacturer">Manufacturer:</label>
        <input type="text" name="manufacturer" id="modalPartManufacturer" placeholder="Manufacturer">
      </div>

      <!-- Weight -->
      <div class="modal-part-details-field">
        <label for="modalPartWeight">Weight:</label>
        <input type="number" step="0.01" name="weight" id="modalPartWeight" placeholder="Weight in kg/lbs">
      </div>

      <!-- Dimensions -->
      <div class="modal-part-details-field">
        <label for="modalPartDimensions">Dimensions:</label>
        <input type="text" name="dimensions" id="modalPartDimensions" placeholder="e.g., 10x5x3">
      </div>

      <!-- Warranty Period -->
      <div class="modal-part-details-field">
        <label for="modalPartWarranty">Warranty Period:</label>
        <input type="text" name="warranty_period" id="modalPartWarranty" placeholder="e.g., 1 year, 2 years...">
      </div>

      <!-- Tax Rate -->
      <div class="modal-part-details-field">
        <label for="modalPartTaxRate">Tax Rate:</label>
        <input type="number" step="0.01" name="tax_rate" id="modalPartTaxRate" placeholder="Tax %">
      </div>

      <!-- Discount -->
      <div class="modal-part-details-field">
        <label for="modalPartDiscount">Discount:</label>
        <input type="number" step="0.01" name="discount" id="modalPartDiscount" placeholder="Discount amount">
      </div>

      <!-- Status -->
      <div class="modal-part-details-field">
        <label for="modalPartStatus">Status:</label>
        <select name="status" id="modalPartStatus">
          <option value="active" selected>Active</option>
          <option value="inactive">Inactive</option>
          <option value="discontinued">Discontinued</option>
        </select>
      </div>

      <!-- Part Image -->
      <div class="modal-part-details-field">
        <label for="modalPartImage">Part Image:</label>
        <input type="file" name="part_image" id="modalPartImage" accept="image/*">
        <div class="image-preview-container">
          <img id="modalPartImagePreview" src="https://placehold.co/300x300?text=No+Image" alt="Image preview" class="image-preview">
        </div>
      </div>

      <!-- Submit Button -->
      <div class="modal-part-details-buttons">
        <button type="submit" class="btn-modal-part-save">Save</button>
        <button type="button" class="btn-modal-part-cancel modal-part-details-close">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Extra Details Modal -->
<div class="modal-extra-details" id="modalExtraDetails">
  <div class="modal-extra-details-content">
    <span class="modal-extra-details-close">&times;</span>
    <h2 class="modal-extra-details-title">Extra Details</h2>

    <form class="modal-extra-details-form">
      <!-- Hidden field (for internal reference, e.g. updating existing extra) -->
      <input type="hidden" name="extra_id" id="modalExtraId" />

      <!-- Extra Name -->
      <div class="modal-extra-details-field">
        <label for="modalExtraName">Extra Name:</label>
        <input type="text" name="extra_name" id="modalExtraName" placeholder="Enter extra name" required>
      </div>

      <!-- Extra Description -->
      <div class="modal-extra-details-field">
        <label for="modalExtraDescr">Description:</label>
        <textarea name="extra_descr" id="modalExtraDescr" placeholder="Short description here..."></textarea>
      </div>

      <!-- Price -->
      <div class="modal-extra-details-field">
        <label for="modalExtraPrice">Price:</label>
        <input type="number" step="0.01" name="extra_price" id="modalExtraPrice" placeholder="0.00" required>
      </div>

      <!-- SKU -->
      <div class="modal-extra-details-field">
        <label for="modalExtraSKU">SKU:</label>
        <input type="text" name="sku" id="modalExtraSKU" placeholder="Stock Keeping Unit">
      </div>

      <!-- Barcode -->
      <div class="modal-extra-details-field">
        <label for="modalExtraBarcode">Barcode:</label>
        <input type="text" name="barcode" id="modalExtraBarcode" placeholder="Barcode">
      </div>

      <!-- Brand -->
      <div class="modal-extra-details-field">
        <label for="modalExtraBrand">Brand:</label>
        <input type="text" name="brand" id="modalExtraBrand" placeholder="Brand name">
      </div>

      <!-- Manufacturer -->
      <div class="modal-extra-details-field">
        <label for="modalExtraManufacturer">Manufacturer:</label>
        <input type="text" name="manufacturer" id="modalExtraManufacturer" placeholder="Manufacturer">
      </div>

      <!-- Weight -->
      <div class="modal-extra-details-field">
        <label for="modalExtraWeight">Weight:</label>
        <input type="number" step="0.01" name="weight" id="modalExtraWeight" placeholder="Weight in kg/lbs">
      </div>

      <!-- Dimensions -->
      <div class="modal-extra-details-field">
        <label for="modalExtraDimensions">Dimensions:</label>
        <input type="text" name="dimensions" id="modalExtraDimensions" placeholder="e.g., 10x5x3">
      </div>

      <!-- Warranty Period -->
      <div class="modal-extra-details-field">
        <label for="modalExtraWarranty">Warranty Period:</label>
        <input type="text" name="warranty_period" id="modalExtraWarranty" placeholder="e.g., 1 year, 2 years...">
      </div>

      <!-- Tax Rate -->
      <div class="modal-extra-details-field">
        <label for="modalExtraTaxRate">Tax Rate:</label>
        <input type="number" step="0.01" name="tax_rate" id="modalExtraTaxRate" placeholder="Tax %">
      </div>

      <!-- Discount -->
      <div class="modal-extra-details-field">
        <label for="modalExtraDiscount">Discount:</label>
        <input type="number" step="0.01" name="discount" id="modalExtraDiscount" placeholder="Discount amount">
      </div>

      <!-- Status -->
      <div class="modal-extra-details-field">
        <label for="modalExtraStatus">Status:</label>
        <select name="status" id="modalExtraStatus">
          <option value="active" selected>Active</option>
          <option value="inactive">Inactive</option>
          <option value="discontinued">Discontinued</option>
        </select>
      </div>

      <!-- Extra Image -->
      <div class="modal-extra-details-field">
        <label for="modalExtraImage">Extra Image:</label>
        <input type="file" name="extra_image" id="modalExtraImage" accept="image/*">
        <div class="image-preview-container">
          <img id="modalExtraImagePreview" src="https://placehold.co/300x300?text=No+Image" alt="Image preview" class="image-preview">
        </div>
      </div>

      <!-- Submit Button -->
      <div class="modal-extra-details-buttons">
        <button type="submit" class="btn-modal-extra-save">Save</button>
        <button type="button" class="btn-modal-extra-cancel modal-extra-details-close">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Service Details Modal -->
<div class="modal-service-details" id="modalServiceDetails">
  <div class="modal-service-details-content">
    <span class="modal-service-details-close">&times;</span>
    <h2 class="modal-service-details-title">Service Details</h2>

    <form class="modal-service-details-form">
      <!-- Hidden field (for internal reference, e.g. updating existing service) -->
      <input type="hidden" name="service_id" id="modalServiceId" />

      <!-- Service Name -->
      <div class="modal-service-details-field">
        <label for="modalServiceName">Service Name:</label>
        <input type="text" name="service_name" id="modalServiceName" placeholder="Enter service name" required>
      </div>

      <!-- Service Description -->
      <div class="modal-service-details-field">
        <label for="modalServiceDescr">Description:</label>
        <textarea name="service_descr" id="modalServiceDescr" placeholder="Short description here..."></textarea>
      </div>

      <!-- Price -->
      <div class="modal-service-details-field">
        <label for="modalServicePrice">Price:</label>
        <input type="number" step="0.01" name="service_price" id="modalServicePrice" placeholder="0.00" required>
      </div>

      <!-- SKU -->
      <div class="modal-service-details-field">
        <label for="modalServiceSKU">SKU:</label>
        <input type="text" name="sku" id="modalServiceSKU" placeholder="Stock Keeping Unit">
      </div>

      <!-- Barcode -->
      <div class="modal-service-details-field">
        <label for="modalServiceBarcode">Barcode:</label>
        <input type="text" name="barcode" id="modalServiceBarcode" placeholder="Barcode">
      </div>

      <!-- Brand -->
      <div class="modal-service-details-field">
        <label for="modalServiceBrand">Brand:</label>
        <input type="text" name="brand" id="modalServiceBrand" placeholder="Brand name">
      </div>

      <!-- Manufacturer -->
      <div class="modal-service-details-field">
        <label for="modalServiceManufacturer">Manufacturer:</label>
        <input type="text" name="manufacturer" id="modalServiceManufacturer" placeholder="Manufacturer">
      </div>

      <!-- Weight -->
      <div class="modal-service-details-field">
        <label for="modalServiceWeight">Weight:</label>
        <input type="number" step="0.01" name="weight" id="modalServiceWeight" placeholder="Weight in kg/lbs">
      </div>

      <!-- Dimensions -->
      <div class="modal-service-details-field">
        <label for="modalServiceDimensions">Dimensions:</label>
        <input type="text" name="dimensions" id="modalServiceDimensions" placeholder="e.g., 10x5x3">
      </div>

      <!-- Warranty Period -->
      <div class="modal-service-details-field">
        <label for="modalServiceWarranty">Warranty Period:</label>
        <input type="text" name="warranty_period" id="modalServiceWarranty" placeholder="e.g., 1 year, 2 years...">
      </div>

      <!-- Tax Rate -->
      <div class="modal-service-details-field">
        <label for="modalServiceTaxRate">Tax Rate:</label>
        <input type="number" step="0.01" name="tax_rate" id="modalServiceTaxRate" placeholder="Tax %">
      </div>

      <!-- Discount -->
      <div class="modal-service-details-field">
        <label for="modalServiceDiscount">Discount:</label>
        <input type="number" step="0.01" name="discount" id="modalServiceDiscount" placeholder="Discount amount">
      </div>

      <!-- Status -->
      <div class="modal-service-details-field">
        <label for="modalServiceStatus">Status:</label>
        <select name="status" id="modalServiceStatus">
          <option value="active" selected>Active</option>
          <option value="inactive">Inactive</option>
          <option value="discontinued">Discontinued</option>
        </select>
      </div>

      <!-- Service Image -->
      <div class="modal-service-details-field">
        <label for="modalServiceImage">Service Image:</label>
        <input type="file" name="service_image" id="modalServiceImage" accept="image/*">
        <div class="image-preview-container">
          <img id="modalServiceImagePreview" src="https://placehold.co/300x300?text=No+Image" alt="Image preview" class="image-preview">
        </div>
      </div>

      <!-- Submit Button -->
      <div class="modal-service-details-buttons">
        <button type="submit" class="btn-modal-service-save">Save</button>
        <button type="button" class="btn-modal-service-cancel modal-service-details-close">Cancel</button>
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

<!-- Add Part Modal -->
<div class="modal-add-part" id="modalAddPart">
  <div class="modal-add-part-content">
    <span class="modal-add-part-close">&times;</span>
    <h2 class="modal-add-part-title">Add New Part</h2>

    <form class="modal-add-part-form" id="modal-add-part-form">
      <!-- Part Name -->
      <div class="modal-add-part-field">
        <label for="addPartName">Part Name:</label>
        <input type="text" name="part_name" id="addPartName" placeholder="Enter part name" required>
      </div>

      <!-- Part Description -->
      <div class="modal-add-part-field">
        <label for="addPartDescr">Description:</label>
        <textarea name="part_descr" id="addPartDescr" placeholder="Short description here..."></textarea>
      </div>

      <!-- Price -->
      <div class="modal-add-part-field">
        <label for="addPartPrice">Price:</label>
        <input type="number" step="0.01" name="part_price" id="addPartPrice" placeholder="0.00" required>
      </div>

      <!-- SKU -->
      <div class="modal-add-part-field">
        <label for="addPartSKU">SKU:</label>
        <input type="text" name="sku" id="addPartSKU" placeholder="Stock Keeping Unit">
      </div>

      <!-- Barcode -->
      <div class="modal-add-part-field">
        <label for="addPartBarcode">Barcode:</label>
        <input type="text" name="barcode" id="addPartBarcode" placeholder="Barcode">
      </div>

      <!-- Manufacturer -->
      <div class="modal-add-part-field">
        <label for="addPartManufacturer">Manufacturer:</label>
        <input type="text" name="manufacturer" id="addPartManufacturer" placeholder="Manufacturer">
      </div>

      <!-- Weight -->
      <div class="modal-add-part-field">
        <label for="addPartWeight">Weight:</label>
        <input type="number" step="0.01" name="weight" id="addPartWeight" placeholder="Weight in kg/lbs">
      </div>

      <!-- Dimensions -->
      <div class="modal-add-part-field">
        <label for="addPartDimensions">Dimensions:</label>
        <input type="text" name="dimensions" id="addPartDimensions" placeholder="e.g., 10x5x3">
      </div>

      <!-- Warranty Period -->
      <div class="modal-add-part-field">
        <label for="addPartWarranty">Warranty Period:</label>
        <input type="text" name="warranty_period" id="addPartWarranty" placeholder="e.g., 1 year, 2 years...">
      </div>

      <!-- Tax Rate -->
      <div class="modal-add-part-field">
        <label for="addPartTaxRate">Tax Rate:</label>
        <input type="number" step="0.01" name="tax_rate" id="addPartTaxRate" placeholder="Tax %">
      </div>

      <!-- Discount -->
      <div class="modal-add-part-field">
        <label for="addPartDiscount">Discount:</label>
        <input type="number" step="0.01" name="discount" id="addPartDiscount" placeholder="Discount amount">
      </div>

      <!-- Status -->
      <div class="modal-add-part-field">
        <label for="addPartStatus">Status:</label>
        <select name="status" id="addPartStatus">
          <option value="active" selected>Active</option>
          <option value="inactive">Inactive</option>
          <option value="discontinued">Discontinued</option>
        </select>
      </div>

      <!-- Part Image Upload -->
      <div class="modal-add-part-field">
        <label for="addPartImage">Part Image:</label>
        <input type="file" name="part_image" id="addPartImage" accept="image/*">
        <div class="image-preview-container">
          <img id="addPartImagePreview" src="https://placehold.co/300x300?text=No+Image" alt="Image preview" class="image-preview">
        </div>
      </div>

      <!-- Submit Button -->
      <div class="modal-add-part-buttons">
        <button type="submit" class="btn-modal-add-part-save">Add Part</button>
        <button type="button" class="btn-modal-add-part-cancel modal-add-part-close">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Add Extra Modal -->
<div class="modal-add-extra" id="modalAddExtra">
  <div class="modal-add-extra-content">
    <span class="modal-add-extra-close">&times;</span>
    <h2 class="modal-add-extra-title">Add New Extra</h2>

    <form class="modal-add-extra-form" id="modal-add-extra-form">
      <!-- Extra Name -->
      <div class="modal-add-extra-field">
        <label for="addExtraName">Extra Name:</label>
        <input type="text" name="extra_name" id="addExtraName" placeholder="Enter extra name" required>
      </div>

      <!-- Extra Description -->
      <div class="modal-add-extra-field">
        <label for="addExtraDescr">Description:</label>
        <textarea name="extra_descr" id="addExtraDescr" placeholder="Short description here..."></textarea>
      </div>

      <!-- Price -->
      <div class="modal-add-extra-field">
        <label for="addExtraPrice">Price:</label>
        <input type="number" step="0.01" name="extra_price" id="addExtraPrice" placeholder="0.00" required>
      </div>

      <!-- SKU -->
      <div class="modal-add-extra-field">
        <label for="addExtraSKU">SKU:</label>
        <input type="text" name="sku" id="addExtraSKU" placeholder="Stock Keeping Unit">
      </div>

      <!-- Barcode -->
      <div class="modal-add-extra-field">
        <label for="addExtraBarcode">Barcode:</label>
        <input type="text" name="barcode" id="addExtraBarcode" placeholder="Barcode">
      </div>

      <!-- Manufacturer -->
      <div class="modal-add-extra-field">
        <label for="addExtraManufacturer">Manufacturer:</label>
        <input type="text" name="manufacturer" id="addExtraManufacturer" placeholder="Manufacturer">
      </div>

      <!-- Weight -->
      <div class="modal-add-extra-field">
        <label for="addExtraWeight">Weight:</label>
        <input type="number" step="0.01" name="weight" id="addExtraWeight" placeholder="Weight in kg/lbs">
      </div>

      <!-- Dimensions -->
      <div class="modal-add-extra-field">
        <label for="addExtraDimensions">Dimensions:</label>
        <input type="text" name="dimensions" id="addExtraDimensions" placeholder="e.g., 10x5x3">
      </div>

      <!-- Warranty Period -->
      <div class="modal-add-extra-field">
        <label for="addExtraWarranty">Warranty Period:</label>
        <input type="text" name="warranty_period" id="addExtraWarranty" placeholder="e.g., 1 year, 2 years...">
      </div>

      <!-- Tax Rate -->
      <div class="modal-add-extra-field">
        <label for="addExtraTaxRate">Tax Rate:</label>
        <input type="number" step="0.01" name="tax_rate" id="addExtraTaxRate" placeholder="Tax %">
      </div>

      <!-- Discount -->
      <div class="modal-add-extra-field">
        <label for="addExtraDiscount">Discount:</label>
        <input type="number" step="0.01" name="discount" id="addExtraDiscount" placeholder="Discount amount">
      </div>

      <!-- Status -->
      <div class="modal-add-extra-field">
        <label for="addExtraStatus">Status:</label>
        <select name="status" id="addExtraStatus">
          <option value="active" selected>Active</option>
          <option value="inactive">Inactive</option>
          <option value="discontinued">Discontinued</option>
        </select>
      </div>

      <!-- Extra Image Upload -->
      <div class="modal-add-extra-field">
        <label for="addExtraImage">Extra Image:</label>
        <input type="file" name="extra_image" id="addExtraImage" accept="image/*">
        <div class="image-preview-container">
          <img id="addExtraImagePreview" src="https://placehold.co/300x300?text=No+Image" alt="Image preview" class="image-preview">
        </div>
      </div>

      <!-- Submit Button -->
      <div class="modal-add-extra-buttons">
        <button type="submit" class="btn-modal-add-extra-save">Add Extra</button>
        <button type="button" class="btn-modal-add-extra-cancel modal-add-extra-close">Cancel</button>
      </div>
    </form>
  </div>
</div>

<!-- Add Service Modal -->
<div class="modal-add-service" id="modalAddService">
  <div class="modal-add-service-content">
    <span class="modal-add-service-close">&times;</span>
    <h2 class="modal-add-service-title">Add New Service</h2>

    <form class="modal-add-service-form" id="modal-add-service-form">
      <!-- Service Name -->
      <div class="modal-add-service-field">
        <label for="addServiceName">Service Name:</label>
        <input type="text" name="service_name" id="addServiceName" placeholder="Enter service name" required>
      </div>

      <!-- Service Description -->
      <div class="modal-add-service-field">
        <label for="addServiceDescr">Description:</label>
        <textarea name="service_descr" id="addServiceDescr" placeholder="Short description here..."></textarea>
      </div>

      <!-- Price -->
      <div class="modal-add-service-field">
        <label for="addServicePrice">Price:</label>
        <input type="number" step="0.01" name="service_price" id="addServicePrice" placeholder="0.00" required>
      </div>

      <!-- SKU -->
      <div class="modal-add-service-field">
        <label for="addServiceSKU">SKU:</label>
        <input type="text" name="sku" id="addServiceSKU" placeholder="Stock Keeping Unit">
      </div>

      <!-- Barcode -->
      <div class="modal-add-service-field">
        <label for="addServiceBarcode">Barcode:</label>
        <input type="text" name="barcode" id="addServiceBarcode" placeholder="Barcode">
      </div>

      <!-- Manufacturer -->
      <div class="modal-add-service-field">
        <label for="addServiceManufacturer">Manufacturer:</label>
        <input type="text" name="manufacturer" id="addServiceManufacturer" placeholder="Manufacturer">
      </div>

      <!-- Weight -->
      <div class="modal-add-service-field">
        <label for="addServiceWeight">Weight:</label>
        <input type="number" step="0.01" name="weight" id="addServiceWeight" placeholder="Weight in kg/lbs">
      </div>

      <!-- Dimensions -->
      <div class="modal-add-service-field">
        <label for="addServiceDimensions">Dimensions:</label>
        <input type="text" name="dimensions" id="addServiceDimensions" placeholder="e.g., 10x5x3">
      </div>

      <!-- Warranty Period -->
      <div class="modal-add-service-field">
        <label for="addServiceWarranty">Warranty Period:</label>
        <input type="text" name="warranty_period" id="addServiceWarranty" placeholder="e.g., 1 year, 2 years...">
      </div>

      <!-- Tax Rate -->
      <div class="modal-add-service-field">
        <label for="addServiceTaxRate">Tax Rate:</label>
        <input type="number" step="0.01" name="tax_rate" id="addServiceTaxRate" placeholder="Tax %">
      </div>

      <!-- Discount -->
      <div class="modal-add-service-field">
        <label for="addServiceDiscount">Discount:</label>
        <input type="number" step="0.01" name="discount" id="addServiceDiscount" placeholder="Discount amount">
      </div>

      <!-- Status -->
      <div class="modal-add-service-field">
        <label for="addServiceStatus">Status:</label>
        <select name="status" id="addServiceStatus">
          <option value="active" selected>Active</option>
          <option value="inactive">Inactive</option>
          <option value="discontinued">Discontinued</option>
        </select>
      </div>

      <!-- Service Image Upload -->
      <div class="modal-add-service-field">
        <label for="addServiceImage">Service Image:</label>
        <input type="file" name="service_image" id="addServiceImage" accept="image/*">
        <div class="image-preview-container">
          <img id="addServiceImagePreview" src="https://placehold.co/300x300?text=No+Image" alt="Image preview" class="image-preview">
        </div>
      </div>

      <!-- Submit Button -->
      <div class="modal-add-service-buttons">
        <button type="submit" class="btn-modal-add-service-save">Add Service</button>
        <button type="button" class="btn-modal-add-service-cancel modal-add-service-close">Cancel</button>
      </div>
    </form>
  </div>
</div>