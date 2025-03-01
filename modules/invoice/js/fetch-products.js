let category = 'products'; // Default category

function updateQueryParams(params) {
    const url = new URL(window.location);
    Object.keys(params).forEach(key => url.searchParams.set(key, params[key]));
    window.history.pushState({}, '', url);
}

async function fetchProducts(categoryParam) {
    // Update the global category variable
    category = categoryParam;
    console.log("Fetching products for category:", category);
    try {
        const params = new URLSearchParams(window.location.search);
        const searchTerm = params.get('searchTerm') || '';
        const limit = parseInt(params.get('limit')) || 10;
        const page = parseInt(params.get('page')) || 1;
        
        // Construct the query string for the request
        const queryString = new URLSearchParams({
            category: category,
            searchTerm: searchTerm,
            limit: limit,
            page: page
        }).toString();

        // Fetch products from the server with the correct category
        const response = await fetch(`../php/fetch-products.php?${queryString}`);
        const data = await response.json();

        if (!data.results || !Array.isArray(data.results)) {
            console.error("Invalid product data:", data);
            return;
        }

        // Clear existing product grid
        document.getElementById("products-grid").innerHTML = "";
        document.getElementById("vehicles-grid").innerHTML = "";
        document.getElementById("parts-grid").innerHTML = "";
        document.getElementById("extras-grid").innerHTML = "";
        document.getElementById("services-grid").innerHTML = "";

        // Populate the appropriate grid based on category
        data.results.forEach(item => {
            const productCard = createProductCard(item);

            // Add the product to the correct grid based on category
            if (category === 'products') {
                console.log("Appending product card to products grid");
                document.getElementById("products-grid").appendChild(productCard);
            } else if (category === 'vehicles') {
                document.getElementById("vehicles-grid").appendChild(productCard);
            } else if (category === 'parts') {
                document.getElementById("parts-grid").appendChild(productCard);
            } else if (category === 'extras') {
                document.getElementById("extras-grid").appendChild(productCard);
            } else if (category === 'services') {
                document.getElementById("services-grid").appendChild(productCard);
            }
        });
    } catch (error) {
        console.error("Error fetching products:", error);
    }
}

// Function to open the Product Details Modal
function openProductDetailsModal(product) {
    console.log("Opening product modal with data:", product);
    const modal = document.getElementById('modalProductDetails');
    
    if (modal) {
        // Populate form fields with product data
        document.getElementById('modalProductId').value = product.prod_id || '';
        document.getElementById('modalProductName').value = product.prod_name || '';
        document.getElementById('modalProductDescr').value = product.prod_descr || '';
        document.getElementById('modalProductPrice').value = product.prod_price || '';
        
        // Check if other fields exist before trying to set their values
        if (document.getElementById('modalProductSKU')) {
            document.getElementById('modalProductSKU').value = product.sku || '';
        }
        if (document.getElementById('modalProductBarcode')) {
            document.getElementById('modalProductBarcode').value = product.barcode || '';
        }
        if (document.getElementById('modalProductBrand')) {
            document.getElementById('modalProductBrand').value = product.brand || '';
        }
        if (document.getElementById('modalProductManufacturer')) {
            document.getElementById('modalProductManufacturer').value = product.manufacturer || '';
        }
        if (document.getElementById('modalProductWeight')) {
            document.getElementById('modalProductWeight').value = product.weight || '';
        }
        if (document.getElementById('modalProductDimensions')) {
            document.getElementById('modalProductDimensions').value = product.dimensions || '';
        }
        if (document.getElementById('modalProductWarranty')) {
            document.getElementById('modalProductWarranty').value = product.warranty_period || '';
        }
        if (document.getElementById('modalProductTaxRate')) {
            document.getElementById('modalProductTaxRate').value = product.tax_rate || '';
        }
        if (document.getElementById('modalProductDiscount')) {
            document.getElementById('modalProductDiscount').value = product.discount || '';
        }
        if (document.getElementById('modalProductStatus')) {
            document.getElementById('modalProductStatus').value = product.status || 'active';
        }

                // Set image preview if available
                const imagePreview = document.getElementById('modalProductImagePreview');
                if (imagePreview) {
                    if (product.image_url) {
                        imagePreview.src = '../../../' + product.image_url;
                    } else {
                        imagePreview.src = 'https://placehold.co/300x300?text=No+Image';
                    }
                }
        
        // Show the modal - use classList for consistency
        modal.classList.add('active');
    } else {
        console.error("Product modal element not found");
    }
}

// Function to close the Product Details Modal
function closeProductDetailsModal() {
    const modal = document.getElementById('modalProductDetails');
    if (modal) {
        modal.classList.remove('active');
    }
}

// Placeholder functions for other modal types
function openVehicleDetailsModal(product) {
    console.log("Vehicle modal not implemented yet", product);
    // Implement similar to product modal
}

function openPartDetailsModal(product) {
    console.log("Part modal not implemented yet", product);
    // Implement similar to product modal
}

function openExtraDetailsModal(product) {
    console.log("Extra modal not implemented yet", product);
    // Implement similar to product modal
}

function openServiceDetailsModal(product) {
    console.log("Service modal not implemented yet", product);
    // Implement similar to product modal
}

function openDefaultDetailsModal(product) {
    console.log("Default modal not implemented yet", product);
    // Implement similar to product modal
}

function createProductCard(product) {
    const card = document.createElement("div");
    card.classList.add("product-card");

    let imageUrl;
    let cardContent;

    switch (category) {
          case 'products':
            imageUrl = product.image_url ? '../../../' + product.image_url : 'https://placehold.co/300x300?text=No+Product+Image';
            cardContent = `
                     <img src="${imageUrl}" alt="${product.prod_name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.prod_name || 'No name'}</h2>
                     <p class="product-description">${product.prod_descr || 'No description available'}</p>
                     <p class="product-price">R${product.prod_price || 'N/A'}</p>
                `;
                card.onclick = () => openProductDetailsModal(product);
                break;
          case 'vehicles':
                imageUrl = product.imageUrl || 'https://placehold.co/300x300?text=No+Vehicle+Image';
                cardContent = `
                     <img src="${imageUrl}" alt="${product.vehicle_name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.vehicle_name || 'No name'}</h2>
                     <p class="product-description">${product.vehicle_descr || 'No description available'}</p>
                     <p class="product-price">R${product.vehicle_price || 'N/A'}</p>
                `;
                card.onclick = () => openVehicleDetailsModal(product);
                break;
          case 'parts':
                imageUrl = product.imageUrl || 'https://placehold.co/300x300?text=No+Part+Image';
                cardContent = `
                     <img src="${imageUrl}" alt="${product.prod_name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.prod_name || 'No name'}</h2>
                     <p class="product-description">${product.prod_descr || 'No description available'}</p>
                     <p class="product-price">R${product.prod_price || 'N/A'}</p>
                `;
                card.onclick = () => openPartDetailsModal(product);
                break;
          case 'extras':
                imageUrl = product.imageUrl || 'https://placehold.co/300x300?text=No+Extras+Image';
                cardContent = `
                     <img src="${imageUrl}" alt="${product.prod_name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.prod_name || 'No name'}</h2>
                     <p class="product-description">${product.prod_descr || 'No description available'}</p>
                     <p class="product-price">R${product.prod_price || 'N/A'}</p>
                `;
                card.onclick = () => openExtraDetailsModal(product);
                break;
          case 'services':
                imageUrl = product.imageUrl || 'https://placehold.co/300x300?text=No+Service+Image';
                cardContent = `
                     <img src="${imageUrl}" alt="${product.prod_name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.prod_name || 'No name'}</h2>
                     <p class="product-description">${product.prod_descr || 'No description available'}</p>
                     <p class="product-price">R${product.prod_price || 'N/A'}</p>
                `;
                card.onclick = () => openServiceDetailsModal(product);
                break;
          default:
                imageUrl = product.imageUrl || 'https://placehold.co/300x300?text=No+Image';
                cardContent = `
                     <img src="${imageUrl}" alt="${product.prod_name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.prod_name || 'No name'}</h2>
                     <p class="product-description">${product.prod_descr || 'No description available'}</p>
                     <p class="product-price">R${product.prod_price || 'N/A'}</p>
                `;
                card.onclick = () => openDefaultDetailsModal(product);
     }

     card.innerHTML = cardContent;
     return card;
}

// Initialize everything when the DOM is fully loaded
document.addEventListener("DOMContentLoaded", () => {
    // Fetch products when the page loads
    fetchProducts(category);
    
    // Set up modal close buttons
    const modalCloseButtons = document.querySelectorAll('.modal-product-details-close');
    if (modalCloseButtons) {
        modalCloseButtons.forEach(button => {
            button.addEventListener('click', closeProductDetailsModal);
        });
    }
    
    // Set up form submission
    const productForm = document.querySelector('.modal-product-details-form');
    if (productForm) {
        productForm.addEventListener('submit', function(event) {
            event.preventDefault();
            
            // Use FormData for file uploads
            const formData = new FormData(productForm);
            
            // AJAX request to save product
            fetch('../handlers/product-handler.php', {
                method: 'POST',
                body: formData // Don't set Content-Type header when using FormData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Show success message
                    showResponseModal(data.status, data.message);                  
                    // Close modal
                    closeProductDetailsModal();
                    
                    // Refresh product list
                    fetchProducts(category);
                } else {
                    // Show error message
                    showResponseModal(data.status, data.message);                  
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred while saving the product');
            });
        });
    }
    
    // Close modal when clicking outside
    window.addEventListener('click', function(event) {
        const modal = document.getElementById('modalProductDetails');
        if (event.target === modal) {
            closeProductDetailsModal();
        }
    });

    // Add image preview functionality for Product Details Modal
const modalProductImage = document.getElementById("modalProductImage");
const modalProductImagePreview = document.getElementById("modalProductImagePreview");

if (modalProductImage && modalProductImagePreview) {
    modalProductImage.addEventListener("change", function() {
        if (this.files && this.files[0]) {
            const reader = new FileReader();
            
            reader.onload = function(e) {
                modalProductImagePreview.src = e.target.result;
            };
            
            reader.readAsDataURL(this.files[0]);
        } else {
            modalProductImagePreview.src = "https://placehold.co/300x300?text=No+Image";
        }
    });
}
});

