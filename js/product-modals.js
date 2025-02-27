/* Product Details Modal JS */
document.addEventListener("DOMContentLoaded", () => {
  // Get the modal element
  const modalProductDetails = document.getElementById("modalProductDetails");

  // Get the close button elements (both the "X" and the "Cancel" button)
  const closeButtons = modalProductDetails.querySelectorAll(".modal-product-details-close");

  // Function to open the modal and (optionally) populate fields
  // 'productData' could be an object with existing product info if you're editing
  // For a new product, you can pass an empty object or omit fields
  window.openProductDetailsModal = function(productData = {}) {
    // Populate the form fields if productData is provided
    document.getElementById("modalProductId").value = productData.prod_id || "";
    document.getElementById("modalProductName").value = productData.prod_name || "";
    document.getElementById("modalProductDescr").value = productData.prod_descr || "";
    document.getElementById("modalProductPrice").value = productData.prod_price || "";
    document.getElementById("modalProductSKU").value = productData.sku || "";
    document.getElementById("modalProductBarcode").value = productData.barcode || "";
    document.getElementById("modalProductBrand").value = productData.brand || "";
    document.getElementById("modalProductManufacturer").value = productData.manufacturer || "";
    document.getElementById("modalProductWeight").value = productData.weight || "";
    document.getElementById("modalProductDimensions").value = productData.dimensions || "";
    document.getElementById("modalProductWarranty").value = productData.warranty_period || "";
    document.getElementById("modalProductTaxRate").value = productData.tax_rate || "";
    document.getElementById("modalProductDiscount").value = productData.discount || "";
    document.getElementById("modalProductStatus").value = productData.status || "active";

    // Show the modal
    modalProductDetails.style.display = "block";
  };

  // Function to close the modal
  function closeProductDetailsModal() {
    modalProductDetails.style.display = "none";
  }

  // Attach event listeners to close buttons
  closeButtons.forEach(btn => {
    btn.addEventListener("click", closeProductDetailsModal);
  });

  // (Optional) Close modal when clicking outside of the modal content
  window.addEventListener("click", (event) => {
    if (event.target === modalProductDetails) {
      closeProductDetailsModal();
    }
  });

  // Handle form submission (example with fetch or AJAX)
  const productForm = modalProductDetails.querySelector(".modal-product-details-form");
  productForm.addEventListener("submit", async (event) => {
    event.preventDefault();

    // Gather data from the form
    const formData = new FormData(productForm);
    const productData = Object.fromEntries(formData.entries());

    // Example: A POST request to save or update product details.
    // Adjust the URL and method as needed for your backend.
    try {
      const response = await fetch("/api/products/save", {
        method: "POST",
        headers: {
          "Content-Type": "application/json",
        },
        body: JSON.stringify(productData),
      });

      if (!response.ok) {
        throw new Error("Failed to save product data");
      }

      // Close the modal on success
      closeProductDetailsModal();
      // Optionally refresh or update the UI with the new/updated product data
      // ...
    } catch (error) {
      console.error("Error saving product data:", error);
      alert("There was an error saving the product details. Please try again.");
    }
  });
});
