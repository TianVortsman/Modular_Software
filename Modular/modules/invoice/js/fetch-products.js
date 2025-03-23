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
        const limit = parseInt(params.get('limit')) || 100;
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
                card.ondblclick = () => openProductDetailsModal(product);
                break;
          case 'vehicles':
                imageUrl = product.imageUrl || 'https://placehold.co/300x300?text=No+Vehicle+Image';
                cardContent = `
                     <img src="${imageUrl}" alt="${product.vehicle_name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.vehicle_name || 'No name'}</h2>
                     <p class="product-description">${product.vehicle_descr || 'No description available'}</p>
                     <p class="product-price">R${product.vehicle_price || 'N/A'}</p>
                `;
                card.ondblclick = () => openVehicleDetailsModal(product);
                break;
          case 'parts':
                imageUrl = product.imageUrl || 'https://placehold.co/300x300?text=No+Part+Image';
                cardContent = `
                     <img src="${imageUrl}" alt="${product.prod_name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.prod_name || 'No name'}</h2>
                     <p class="product-description">${product.prod_descr || 'No description available'}</p>
                     <p class="product-price">R${product.prod_price || 'N/A'}</p>
                `;
                card.ondblclick = () => openPartDetailsModal(product);
                break;
          case 'extras':
                imageUrl = product.imageUrl || 'https://placehold.co/300x300?text=No+Extras+Image';
                cardContent = `
                     <img src="${imageUrl}" alt="${product.prod_name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.prod_name || 'No name'}</h2>
                     <p class="product-description">${product.prod_descr || 'No description available'}</p>
                     <p class="product-price">R${product.prod_price || 'N/A'}</p>
                `;
                card.ondblclick = () => openExtraDetailsModal(product);
                break;
          case 'services':
                imageUrl = product.imageUrl || 'https://placehold.co/300x300?text=No+Service+Image';
                cardContent = `
                     <img src="${imageUrl}" alt="${product.prod_name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.prod_name || 'No name'}</h2>
                     <p class="product-description">${product.prod_descr || 'No description available'}</p>
                     <p class="product-price">R${product.prod_price || 'N/A'}</p>
                `;
                card.ondblclick = () => openServiceDetailsModal(product);
                break;
          default:
                imageUrl = product.imageUrl || 'https://placehold.co/300x300?text=No+Image';
                cardContent = `
                     <img src="${imageUrl}" alt="${product.prod_name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.prod_name || 'No name'}</h2>
                     <p class="product-description">${product.prod_descr || 'No description available'}</p>
                     <p class="product-price">R${product.prod_price || 'N/A'}</p>
                `;
                card.ondblclick = () => openDefaultDetailsModal(product);
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
            fetch('../handlers/save-product.php', {
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
// Override the existing modal open functions to use the universal modal
function openProductDetailsModal(product) {
    console.log("Opening product modal with data:", product);
    // Use the universal product modal instead of the old modal
    if (window.universalProductModal) {
        window.universalProductModal.openForEdit('products', product);
    } else {
        console.error("Universal product modal not initialized");
    }
}

function openVehicleDetailsModal(product) {
    console.log("Opening vehicle modal with data:", product);
    if (window.universalProductModal) {
        window.universalProductModal.openForEdit('vehicles', product);
    } else {
        console.error("Universal product modal not initialized");
    }
}

function openPartDetailsModal(product) {
    console.log("Opening part modal with data:", product);
    if (window.universalProductModal) {
        window.universalProductModal.openForEdit('parts', product);
    } else {
        console.error("Universal product modal not initialized");
    }
}

function openExtraDetailsModal(product) {
    console.log("Opening extra modal with data:", product);
    if (window.universalProductModal) {
        window.universalProductModal.openForEdit('extras', product);
    } else {
        console.error("Universal product modal not initialized");
    }
}

function openServiceDetailsModal(product) {
    console.log("Opening service modal with data:", product);
    if (window.universalProductModal) {
        window.universalProductModal.openForEdit('services', product);
    } else {
        console.error("Universal product modal not initialized");
    }
}

// Also override the "Add" button functions
function openAddProductModal() {
    if (window.universalProductModal) {
        window.universalProductModal.openForAdd('products');
    } else {
        console.error("Universal product modal not initialized");
    }
}

function openAddVehicleModal() {
    if (window.universalProductModal) {
        window.universalProductModal.openForAdd('vehicles');
    } else {
        console.error("Universal product modal not initialized");
    }
}

function openAddPartModal() {
    if (window.universalProductModal) {
        window.universalProductModal.openForAdd('parts');
    } else {
        console.error("Universal product modal not initialized");
    }
}

function openAddExtraModal() {
    if (window.universalProductModal) {
        window.universalProductModal.openForAdd('extras');
    } else {
        console.error("Universal product modal not initialized");
    }
}

function openAddServiceModal() {
    if (window.universalProductModal) {
        window.universalProductModal.openForAdd('services');
    } else {
        console.error("Universal product modal not initialized");
    }
}