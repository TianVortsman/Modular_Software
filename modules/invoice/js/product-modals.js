// Universal Product Modal Controller
class UniversalProductModal {
  constructor() {
    // Modal elements
    this.modal = document.getElementById('universalProductModal');
    this.form = document.getElementById('universalProductForm');
    this.title = document.getElementById('universalProductModalTitle');
    this.saveBtn = document.getElementById('universalProductSaveBtn');
    this.deleteBtn = document.getElementById('universalProductDeleteBtn');
    
    // Hidden fields
    this.itemIdField = document.getElementById('universalItemId');
    this.itemTypeField = document.getElementById('universalItemType');
    this.modalModeField = document.getElementById('universalModalMode');
    
    // Dynamic labels
    this.itemNameLabel = document.getElementById('universalItemNameLabel');
    this.itemDescrLabel = document.getElementById('universalItemDescrLabel');
    this.itemPriceLabel = document.getElementById('universalItemPriceLabel');
    this.itemImageLabel = document.getElementById('universalItemImageLabel');
    
    // Type-specific fieldsets
    this.vehicleFields = document.querySelectorAll('.universal-product-fieldset-vehicle, .universal-product-field-vehicle');
    this.serviceFields = document.querySelectorAll('.universal-product-fieldset-service, .universal-product-field-service');
    this.partFields = document.querySelectorAll('.universal-product-fieldset-part, .universal-product-field-part');
    this.extraFields = document.querySelectorAll('.universal-product-fieldset-extra, .universal-product-field-extra');
    
    // Image preview
    this.imageInput = document.getElementById('universalItemImage');
    this.imagePreview = document.getElementById('universalItemImagePreview');
    
    // Additional images (for vehicles)
    this.additionalImagesInput = document.getElementById('universalItemAdditionalImages');
    this.additionalImagesPreview = document.getElementById('additionalImagesPreview');
    
    this.setupEventListeners();
  }
  
  setupEventListeners() {
    // Close modal when clicking the X or Cancel button
    document.querySelectorAll('.universal-product-modal-close').forEach(closeBtn => {
      closeBtn.addEventListener('click', () => this.close());
    });
    
    // Close modal when clicking outside the content
    this.modal.addEventListener('click', (e) => {
      if (e.target === this.modal) {
        this.close();
      }
    });
    
    // Handle form submission
    this.form.addEventListener('submit', (e) => {
      e.preventDefault();
      this.handleSubmit();
    });
    
    // Handle delete button
    this.deleteBtn.addEventListener('click', () => {
      if (confirm('Are you sure you want to delete this item?')) {
        this.handleDelete();
      }
    });
    
    // Handle image preview
    this.imageInput.addEventListener('change', () => {
      this.previewImage(this.imageInput, this.imagePreview);
    });
    
    // Handle additional images preview
    this.additionalImagesInput.addEventListener('change', () => {
      this.previewMultipleImages();
    });
  }
  
  // Open modal for adding a new item
  openForAdd(itemType) {
    this.resetForm();
    
    // Set modal mode and item type
    this.modalModeField.value = 'add';
    this.itemTypeField.value = itemType;
    
    // Update title and button text
    this.updateTitleAndLabels(itemType, 'add');
    this.saveBtn.textContent = 'Add ' + this.getItemTypeName(itemType);
    
    // Hide delete button for add mode
    this.deleteBtn.style.display = 'none';
    
    // Show/hide type-specific fields
    this.toggleTypeSpecificFields(itemType);
    
    // Show the modal
    this.modal.style.display = 'block';
  }
  
  // Open modal for viewing/editing an existing item
  openForEdit(itemType, itemData) {
    this.resetForm();
    
    // Set modal mode and item type
    this.modalModeField.value = 'edit';
    this.itemTypeField.value = itemType;
    this.itemIdField.value = itemData.id;
    
    // Update title and button text
    this.updateTitleAndLabels(itemType, 'edit');
    this.saveBtn.textContent = 'Update ' + this.getItemTypeName(itemType);
    
    // Show delete button for edit mode
    this.deleteBtn.style.display = 'block';
    
    // Show/hide type-specific fields
    this.toggleTypeSpecificFields(itemType);
    
    // Populate form with item data
    this.populateForm(itemType, itemData);
    
    // Show the modal
    this.modal.style.display = 'block';
  }
  
  // Close the modal
  close() {
    this.modal.style.display = 'none';
  }
  
  // Reset form to default state
  resetForm() {
    this.form.reset();
    this.imagePreview.src = 'https://placehold.co/300x300?text=No+Image';
    this.additionalImagesPreview.innerHTML = '';
  }
  
  // Update modal title and field labels based on item type
  updateTitleAndLabels(itemType, mode) {
    const action = mode === 'add' ? 'Add New' : 'Edit';
    const typeName = this.getItemTypeName(itemType);
    
    this.title.textContent = `${action} ${typeName}`;
    
    // Update field labels
    switch (itemType) {
      case 'products':
        this.itemNameLabel.textContent = 'Product Name:';
        this.itemDescrLabel.textContent = 'Product Description:';
        this.itemPriceLabel.textContent = 'Product Price:';
        this.itemImageLabel.textContent = 'Product Image:';
        break;
      case 'parts':
        this.itemNameLabel.textContent = 'Part Name:';
        this.itemDescrLabel.textContent = 'Part Description:';
        this.itemPriceLabel.textContent = 'Part Price:';
        this.itemImageLabel.textContent = 'Part Image:';
        break;
      case 'services':
        this.itemNameLabel.textContent = 'Service Name:';
        this.itemDescrLabel.textContent = 'Service Description:';
        this.itemPriceLabel.textContent = 'Service Price:';
        this.itemImageLabel.textContent = 'Service Image:';
        break;
      case 'extras':
        this.itemNameLabel.textContent = 'Extra Name:';
        this.itemDescrLabel.textContent = 'Extra Description:';
        this.itemPriceLabel.textContent = 'Extra Price:';
        this.itemImageLabel.textContent = 'Extra Image:';
        break;
      case 'vehicles':
        this.itemNameLabel.textContent = 'Vehicle Model:';
        this.itemDescrLabel.textContent = 'Vehicle Description:';
        this.itemPriceLabel.textContent = 'Vehicle Price:';
        this.itemImageLabel.textContent = 'Vehicle Image:';
        break;
    }
  }
  
  // Get human-readable name for item type
  getItemTypeName(itemType) {
    switch (itemType) {
      case 'products': return 'Product';
      case 'parts': return 'Part';
      case 'services': return 'Service';
      case 'extras': return 'Extra';
      case 'vehicles': return 'Vehicle';
      default: return 'Item';
    }
  }
  
  // Show/hide fields specific to item type
  toggleTypeSpecificFields(itemType) {
    // Hide all type-specific fields first
    this.hideAllTypeSpecificFields();
    
    // Show fields for the selected type
    switch (itemType) {
      case 'vehicles':
        this.showElements(this.vehicleFields);
        break;
      case 'services':
        this.showElements(this.serviceFields);
        break;
      case 'parts':
        this.showElements(this.partFields);
        break;
      case 'extras':
        this.showElements(this.extraFields);
        break;
    }
  }
  
  // Hide all type-specific fields
  hideAllTypeSpecificFields() {
    this.hideElements(this.vehicleFields);
    this.hideElements(this.serviceFields);
    this.hideElements(this.partFields);
    this.hideElements(this.extraFields);
  }
  
  // Helper to hide elements
  hideElements(elements) {
    elements.forEach(el => {
      el.style.display = 'none';
    });
  }
  
  // Helper to show elements
  showElements(elements) {
    elements.forEach(el => {
      el.style.display = '';
    });
  }
  
  // Preview a single image
  previewImage(input, imgElement) {
    if (input.files && input.files[0]) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        imgElement.src = e.target.result;
      };
      
      reader.readAsDataURL(input.files[0]);
    }
  }
  
  // Preview multiple images
  previewMultipleImages() {
    const files = this.additionalImagesInput.files;
    this.additionalImagesPreview.innerHTML = '';
    
    if (files) {
      for (let i = 0; i < files.length; i++) {
        const reader = new FileReader();
        const imgContainer = document.createElement('div');
        imgContainer.className = 'additional-image-container';
        
        const img = document.createElement('img');
        img.className = 'additional-image-preview';
        
        reader.onload = function(e) {
          img.src = e.target.result;
        };
        
        reader.readAsDataURL(files[i]);
        imgContainer.appendChild(img);
        this.additionalImagesPreview.appendChild(imgContainer);
      }
    }
  }
  
  // Populate form with item data
  populateForm(itemType, data) {
    // Map field names based on item type
    const nameField = this.getFieldNameMapping(itemType, 'name');
    const descrField = this.getFieldNameMapping(itemType, 'descr');
    const priceField = this.getFieldNameMapping(itemType, 'price');
    
    // Set basic fields
    document.getElementById('universalItemName').value = data[nameField] || '';
    document.getElementById('universalItemDescr').value = data[descrField] || '';
    document.getElementById('universalItemPrice').value = data[priceField] || '';
    
    // Set common fields if they exist in the data
    const commonFields = [
      'sku', 'barcode', 'brand', 'manufacturer', 'weight', 
      'dimensions', 'warranty_period', 'tax_rate', 'discount', 'status'
    ];
    
    commonFields.forEach(field => {
      const input = document.getElementById('universalItem' + this.capitalizeFirstLetter(field));
      if (input && data[field]) {
        input.value = data[field];
      }
    });
    
    // Set image preview if available
    if (data.image_url) {
      this.imagePreview.src = '../../../' + data.image_url;
    }
    
    // Set type-specific fields
    this.populateTypeSpecificFields(itemType, data);
  }
  
  // Get field name mapping based on item type
  getFieldNameMapping(itemType, fieldType) {
    const prefix = itemType.slice(0, -1); // Remove 's' from the end
    
    switch (fieldType) {
      case 'name':
        return `${prefix}_name`;
      case 'descr':
        return `${prefix}_descr`;
      case 'price':
        return `${prefix}_price`;
      default:
        return fieldType;
    }
  }
  
  // Populate type-specific fields
  populateTypeSpecificFields(itemType, data) {
    switch (itemType) {
      case 'vehicles':
        if (data.engine_type) document.getElementById('universalItemEngineType').value = data.engine_type;
        if (data.license_plate) document.getElementById('universalItemLicensePlate').value = data.license_plate;
        if (data.registration_number) document.getElementById('universalItemRegistrationNumber').value = data.registration_number;
        if (data.seat_type) document.getElementById('universalItemSeatType').value = data.seat_type;
        if (data.previous_owners) document.getElementById('universalItemPreviousOwners').value = data.previous_owners;
        if (data.extra_features) document.getElementById('universalItemExtraFeatures').value = data.extra_features;
        if (data.color) document.getElementById('universalItemColor').value = data.color;
        break;
        
      case 'services':
        if (data.duration) document.getElementById('universalItemDuration').value = data.duration;
        if (data.service_type) document.getElementById('universalItemServiceType').value = data.service_type;
        if (data.staff) document.getElementById('universalItemStaff').value = data.staff;
        break;
        
            case 'parts':
        if (data.part_number) document.getElementById('universalItemPartNumber').value = data.part_number;
        if (data.compatible_vehicles) document.getElementById('universalItemCompatibleVehicles').value = data.compatible_vehicles;
        break;
        
            case 'extras':
        if (data.extra_type) document.getElementById('universalItemExtraType').value = data.extra_type;
        if (data.availability) document.getElementById('universalItemAvailability').value = data.availability;
        break;
          }
        }
        
        // Handle form submission
        handleSubmit() {
          // Implement form submission logic here
          console.log('Form submitted');
        }
        
        // Handle item deletion
        handleDelete() {
          // Implement item deletion logic here
          console.log('Item deleted');
        }
      }

      // Initialize the modal controller
      document.addEventListener('DOMContentLoaded', () => {
        const universalProductModal = new UniversalProductModal();
      });