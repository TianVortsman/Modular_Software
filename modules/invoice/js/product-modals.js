// Universal Product Modal Controller
class UniversalProductModal {
  // Add this method to the UniversalProductModal class constructor
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
    
    // Type-specific fieldsets
    this.vehicleFields = document.querySelectorAll('.universal-product-fieldset-vehicle, .universal-product-field-vehicle');
    this.serviceFields = document.querySelectorAll('.universal-product-fieldset-service, .universal-product-field-service');
    this.partFields = document.querySelectorAll('.universal-product-fieldset-part, .universal-product-field-part');
    this.extraFields = document.querySelectorAll('.universal-product-fieldset-extra, .universal-product-field-extra');
    
    // Image preview
    this.imageInput = document.getElementById('universalItemImage');
    this.imagePreview = document.getElementById('universalItemImagePreview');
    this.imageUrlField = document.getElementById('universalItemImageUrl');
    
    // Check for missing elements
    this.checkRequiredElements();
    
    this.setupEventListeners();
  }

checkRequiredElements() {
  const requiredElements = [
    { id: 'universalProductModal', name: 'Modal Container' },
    { id: 'universalProductForm', name: 'Form Element' },
    { id: 'universalProductModalTitle', name: 'Modal Title' },
    { id: 'universalProductSaveBtn', name: 'Save Button' },
    { id: 'universalProductDeleteBtn', name: 'Delete Button' },
    { id: 'universalItemId', name: 'Item ID Field' },
    { id: 'universalItemType', name: 'Item Type Field' },
    { id: 'universalModalMode', name: 'Modal Mode Field' },
    { id: 'universalItemImage', name: 'Image Input' },
    { id: 'universalItemImagePreview', name: 'Image Preview' },
    { id: 'universalItemImageUrl', name: 'Image URL Field' }
  ];
  
  let missingElements = [];
  
  requiredElements.forEach(element => {
    if (!document.getElementById(element.id)) {
      missingElements.push(element.name);
      console.error(`Missing required element: ${element.name} (ID: ${element.id})`);
    }
  });
  
  if (missingElements.length > 0) {
    console.error('The following required elements are missing:', missingElements.join(', '));
  }
}
  
  setupEventListeners() {
    // Existing event listeners...
    
    // Setup drag and drop for image upload
    this.setupImageDragAndDrop();
    
    // Tab navigation
    document.querySelectorAll('.upm-tab-btn').forEach(tabBtn => {
      tabBtn.addEventListener('click', (e) => {
        // Remove active class from all tabs
        document.querySelectorAll('.upm-tab-btn').forEach(btn => {
          btn.classList.remove('upm-active');
        });
        
        // Add active class to clicked tab
        e.target.classList.add('upm-active');
        
        // Hide all tab panes
        document.querySelectorAll('.upm-tab-pane').forEach(pane => {
          pane.classList.remove('upm-active');
        });
        
        // Show the corresponding tab pane
        const tabId = e.target.getAttribute('data-tab');
        document.getElementById('upm-tab-' + tabId).classList.add('upm-active');
      });
    });
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
  }
  
  setupImageDragAndDrop() {
    const dropzone = document.getElementById('universalImageDropzone');
    const imageInput = document.getElementById('universalItemImage');
    const imagePreview = document.getElementById('universalItemImagePreview');
    
    // Prevent default drag behaviors
    ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, preventDefaults, false);
    });
    
    function preventDefaults(e) {
      e.preventDefault();
      e.stopPropagation();
    }
    
    // Highlight drop zone when item is dragged over it
    ['dragenter', 'dragover'].forEach(eventName => {
      dropzone.addEventListener(eventName, highlight, false);
    });
    
    ['dragleave', 'drop'].forEach(eventName => {
      dropzone.addEventListener(eventName, unhighlight, false);
    });
    
    function highlight() {
      dropzone.classList.add('drag-over');
    }
    
    function unhighlight() {
      dropzone.classList.remove('drag-over');
    }
    
    // Handle dropped files
    dropzone.addEventListener('drop', handleDrop, false);
    
    function handleDrop(e) {
      const dt = e.dataTransfer;
      const files = dt.files;
      
      if (files.length) {
        imageInput.files = files;
        handleFiles(files);
      }
    }
    
    // Handle selected files from input
    imageInput.addEventListener('change', () => {
      if (imageInput.files.length) {
        handleFiles(imageInput.files);
      }
    });
    
    function handleFiles(files) {
      if (files[0].type.match('image.*')) {
        const reader = new FileReader();
        
        reader.onload = (e) => {
          imagePreview.src = e.target.result;
          dropzone.classList.add('has-image');
        };
        
        reader.readAsDataURL(files[0]);
      }
    }
    
    // Make the entire dropzone clickable to trigger file input
    dropzone.addEventListener('click', (e) => {
      // Prevent click if the actual file input was clicked
      if (e.target !== imageInput) {
        imageInput.click();
      }
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
    this.itemIdField.value = itemData.prod_id;
    
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
    document.getElementById('universalImageDropzone').classList.remove('has-image');
  }
  
  // Update modal title and field labels based on item type
  updateTitleAndLabels(itemType, mode) {
    const action = mode === 'add' ? 'Add New' : 'Edit';
    const typeName = this.getItemTypeName(itemType);
    
    this.title.textContent = `${action} ${typeName}`;
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
      
      reader.onload = (e) => {
        imgElement.src = e.target.result;
        document.getElementById('universalImageDropzone').classList.add('has-image');
      };
      
      reader.readAsDataURL(input.files[0]);
    }
  }
  
  // Populate form with item data
  populateForm(itemType, data) {
    // Set basic fields
    document.getElementById('universalItemName').value = data.prod_name || '';
    document.getElementById('universalItemDescr').value = data.prod_descr || '';
    document.getElementById('universalItemPrice').value = data.prod_price || '';
    
    // Set common fields if they exist in the data
    const commonFields = [
      'sku', 'barcode', 'brand', 'manufacturer', 'weight', 
      'dimensions', 'warranty_period','product_type', 'tax_rate', 'discount', 'status',
      'category', 'sub_category', 'stock_quantity', 'reorder_level',
      'lead_time', 'material', 'labor_cost'
    ];
    
    commonFields.forEach(field => {
      const input = document.getElementById('universalItem' + this.capitalizeFirstLetter(field));
      if (input && data[field] !== undefined) {
        input.value = data[field];
      }
    });
    
    // Set image preview if available
    if (data.image_url) {
      this.imagePreview.src = '../../../' + data.image_url;
      this.imageUrlField.value = data.image_url;
      document.getElementById('universalImageDropzone').classList.add('has-image');
    } else {
      document.getElementById('universalImageDropzone').classList.remove('has-image');
    }
    
    // Set type-specific fields
    this.populateTypeSpecificFields(itemType, data);
  }
  
  // Helper to capitalize first letter
  capitalizeFirstLetter(string) {
    return string.charAt(0).toUpperCase() + string.slice(1);
  }
  
  // Populate type-specific fields
  populateTypeSpecificFields(itemType, data) {
    switch (itemType) {
      case 'parts':
        if (data.compatible_vehicles) document.getElementById('universalItemCompatibleVehicles').value = data.compatible_vehicles;
        if (data.oem_part_number) document.getElementById('universalItemOEMNumber').value = data.oem_part_number;
        break;
        
      case 'services':
        if (data.estimated_time) document.getElementById('universalItemEstimatedTime').value = data.estimated_time;
        if (data.service_frequency) document.getElementById('universalItemServiceFrequency').value = data.service_frequency;
        break;
    }
    
    // Handle bundle items and installation required
    if (data.bundle_items) document.getElementById('universalItemBundleItems').value = data.bundle_items;
    if (data.installation_required !== undefined) {
      document.getElementById('universalItemInstallationRequired').value = data.installation_required ? 'true' : 'false';
    }
  }

    handleSubmit() {
      // Show loading modal
      document.getElementById('unique-loading-modal').style.display = 'flex';
      
      // Create FormData object from the form
      const formData = new FormData(this.form);
      
      // Add the mode (add/edit)
      formData.append('mode', this.modalModeField.value);
      
      // Handle image file
      const imageFile = this.imageInput.files[0];
      if (imageFile) {
          formData.append('image', imageFile);
      }
      
      // Send the data to the server
      fetch('../php/save-product.php', {
          method: 'POST',
          body: formData
      })
      .then(response => response.json())
      .then(data => {
          // Hide loading modal
          document.getElementById('unique-loading-modal').style.display = 'none';
          
          // Show response modal
          showResponseModal(data.status, data.message);
          
          if (data.status === 'success') {
              this.close();
              // Refresh the product list
              if (typeof fetchProducts === 'function') {
                  fetchProducts(this.itemTypeField.value);
              }
          }
      })
      .catch(error => {
          console.error('Error:', error);
          document.getElementById('unique-loading-modal').style.display = 'none';
          showResponseModal('error', 'An error occurred while saving the product');
      });
    }

    handleDelete() {
      if (!confirm('Are you sure you want to delete this item?')) {
          return;
      }

      document.getElementById('unique-loading-modal').style.display = 'flex';
      
      const formData = new FormData();
      formData.append('prod_id', this.itemIdField.value);
      formData.append('mode', 'delete');
      
      fetch('../php/save-product.php', {
          method: 'POST',
          body: formData
      })
      .then(response => response.json())
      .then(data => {
          document.getElementById('unique-loading-modal').style.display = 'none';
          showResponseModal(data.status, data.message);
          
          if (data.status === 'success') {
              this.close();
              if (typeof fetchProducts === 'function') {
                  fetchProducts(this.itemTypeField.value);
              }
          }
      })
      .catch(error => {
          console.error('Error:', error);
          document.getElementById('unique-loading-modal').style.display = 'none';
          showResponseModal('error', 'An error occurred while deleting the product');
      });
    }

    populateForm(itemType, data) {
      // Reset form first
      this.resetForm();
      
      // Map all database fields to form fields
      const fieldMappings = {
          'prod_name': 'universalItemName',
          'prod_descr': 'universalItemDescr',
          'prod_price': 'universalItemPrice',
          'stock_quantity': 'universalItemStockQuantity',
          'barcode': 'universalItemBarcode',
          'product_type': 'universalItemProductType',
          'brand': 'universalItemBrand',
          'manufacturer': 'universalItemManufacturer',
          'weight': 'universalItemWeight',
          'dimensions': 'universalItemDimensions',
          'warranty_period': 'universalItemWarrantyPeriod',
          'tax_rate': 'universalItemTaxRate',
          'discount': 'universalItemDiscount',
          'status': 'universalItemStatus',
          'sku': 'universalItemSKU',
          'category': 'universalItemCategory',
          'sub_category': 'universalItemSubCategory',
          'reorder_level': 'universalItemReorderLevel',
          'lead_time': 'universalItemLeadTime',
          'oem_part_number': 'universalItemOEMPartNumber',
          'compatible_vehicles': 'universalItemCompatibleVehicles',
          'material': 'universalItemMaterial',
          'labor_cost': 'universalItemLaborCost',
          'estimated_time': 'universalItemEstimatedTime',
          'service_frequency': 'universalItemServiceFrequency',
          'bundle_items': 'universalItemBundleItems',
          'installation_required': 'universalItemInstallationRequired'
      };
  
      // Populate each field if data exists
      Object.entries(fieldMappings).forEach(([dbField, formId]) => {
          const input = document.getElementById(formId);
          if (input && data[dbField] !== null && data[dbField] !== undefined) {
              input.value = data[dbField];
          }
      });
      
      // Handle image preview
      if (data.image_url) {
          this.imagePreview.src = '../../../' + data.image_url;
          this.imageUrlField.value = data.image_url;
          document.getElementById('universalImageDropzone').classList.add('has-image');
      } else {
          this.imagePreview.src = 'https://placehold.co/300x300?text=No+Image';
          document.getElementById('universalImageDropzone').classList.remove('has-image');
      }
  
      // Set the item ID
      if (data.prod_id) {
          this.itemIdField.value = data.prod_id;
      }
  
      // Show type-specific fields
      this.toggleTypeSpecificFields(itemType);
  }
}

// Initialize the modal controller
document.addEventListener('DOMContentLoaded', () => {
  const universalProductModal = new UniversalProductModal();
  
  // Close response modal when clicking the X
  const responseModalClose = document.querySelector('.response-modal-close');
  if (responseModalClose) {
    responseModalClose.addEventListener('click', () => {
      document.getElementById('response-modal').style.display = 'none';
    });
  }
  
  // Close response modal when clicking outside
  const responseModal = document.getElementById('response-modal');
  if (responseModal) {
    responseModal.addEventListener('click', (e) => {
      if (e.target === responseModal) {
        responseModal.style.display = 'none';
      }
    });
  }
  
  // Expose the modal controller to the global scope for external access
  window.universalProductModal = universalProductModal;
});