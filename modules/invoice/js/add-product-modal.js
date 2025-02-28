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
  
      // Gather data from the form
      const formData = new FormData(productForm);
      const productData = Object.fromEntries(formData.entries());
  
      try {
        const response = await fetch("../handlers/product-handler.php", {
          method: "POST",
          headers: {
            "Content-Type": "application/json",
          },
          body: JSON.stringify(productData),
        });
  
        if (!response.ok) {
          throw new Error("Failed to add product");
        }
  
        // Close the modal on success
        closeAddProductModal();
        
        // Optionally refresh the product list
        if (typeof fetchProducts === 'function') {
          fetchProducts(category);
        }
        
      } catch (error) {
        console.error("Error adding product:", error);
        showResponseModal(data.status, data.message);
          }
    });
  });