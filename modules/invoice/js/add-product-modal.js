/* Add Product Modal JS */
document.addEventListener("DOMContentLoaded", () => {
    // Get the modal element
    const modalAddProduct = document.getElementById("modalAddProduct");
  
    // Get the close button elements (both the "X" and the "Cancel" button)
    const closeButtons = modalAddProduct.querySelectorAll(".modal-add-product-close");
  
    // Function to open the modal
    window.openAddProductModal = function() {
      // Reset form fields if needed
      document.getElementById("modal-add-product-form").reset();
      
      // Show the modal
      modalAddProduct.classList.add('active');
    };
  
    // Function to close the modal
    function closeAddProductModal() {
      modalAddProduct.classList.remove('active');
    }
  
    // Attach event listeners to close buttons
    closeButtons.forEach(btn => {
      btn.addEventListener("click", closeAddProductModal);
    });
  
    // Close modal when clicking outside of the modal content
    window.addEventListener("click", (event) => {
      if (event.target === modalAddProduct) {
        closeAddProductModal();
      }
    });
  
    // Handle form submission
const productForm = modalAddProduct.querySelector(".modal-add-product-form");
productForm.addEventListener("submit", async (event) => {
  event.preventDefault();

  // Create FormData object for handling file uploads
  const formData = new FormData(productForm);
  
  try {
    const response = await fetch("../handlers/product-handler.php", {
      method: "POST",
      body: formData, // Send FormData directly (don't set Content-Type header)
    });

    if (!response.ok) {
      throw new Error("Failed to add product");
    }

    const data = await response.json();
    
    // Close the modal on success
    closeAddProductModal();
    
    // Optionally refresh the product list
    if (typeof fetchProducts === 'function') {
      fetchProducts(category);
    }
    
    // Show success message if available
    if (data.message) {
      showResponseModal(data.success ? "success" : "error", data.message);
    }
    
  } catch (error) {
    console.error("Error adding product:", error);
    showResponseModal("error", "Failed to add product: " + error.message);
  }
});

// Add image preview functionality
const imageInput = document.getElementById("addProductImage");
const imagePreview = document.getElementById("addProductImagePreview");

if (imageInput && imagePreview) {
  imageInput.addEventListener("change", function() {
    if (this.files && this.files[0]) {
      const reader = new FileReader();
      
      reader.onload = function(e) {
        imagePreview.src = e.target.result;
      };
      
      reader.readAsDataURL(this.files[0]);
    } else {
      imagePreview.src = "https://placehold.co/300x300?text=No+Image";
    }
  });
}
  });