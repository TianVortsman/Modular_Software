

let category = 'products'; // Default category

function updateQueryParams(params) {
    const url = new URL(window.location);
    Object.keys(params).forEach(key => url.searchParams.set(key, params[key]));
    window.history.pushState({}, '', url);
}

async function fetchProducts(category) {
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
        const modal = document.getElementById('modalProductDetails');
        if (modal) {
          modal.classList.add('active');
        }
      }
    
      // Function to close the Product Details Modal
      function closeProductDetailsModal() {
        const modal = document.getElementById('modalProductDetails');
        if (modal) {
          modal.classList.remove('active');
        }
      }
    
      // Close button event listener
      const closeBtn = document.querySelector('.modal-close-product-details');
      if (closeBtn) {
        closeBtn.addEventListener('click', closeProductDetailsModal);
      }
    
      // Overlay click event listener to also close the modal when clicking outside the content
      const overlay = document.querySelector('.modal-overlay-product-details');
      if (overlay) {
        overlay.addEventListener('click', closeProductDetailsModal);
      }
    
      // Example: Open the modal when clicking a designated button
      const openBtn = document.getElementById('openProductModalBtn');
      if (openBtn) {
        openBtn.addEventListener('click', openProductDetailsModal);
      }

function createProductCard(product) {
    const card = document.createElement("div");
    card.classList.add("product-card");

    let imageUrl;
    let cardContent;

    switch (category) {
          case 'products':
                imageUrl = product.imageUrl || 'https://placehold.co/300x300?text=No+Product+Image';
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
                     <img src="${imageUrl}" alt="${product.part_name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.part_name || 'No name'}</h2>
                     <p class="product-description">${product.part_descr || 'No description available'}</p>
                     <p class="product-price">R${product.part_price || 'N/A'}</p>
                `;
                card.onclick = () => openPartDetailsModal(product);
                break;
          case 'extras':
                imageUrl = product.imageUrl || 'https://placehold.co/300x300?text=No+Extras+Image';
                cardContent = `
                     <img src="${imageUrl}" alt="${product.extra_name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.extra_name || 'No name'}</h2>
                     <p class="product-description">${product.extra_descr || 'No description available'}</p>
                     <p class="product-price">R${product.extra_price || 'N/A'}</p>
                `;
                card.onclick = () => openExtraDetailsModal(product);
                break;
          case 'services':
                imageUrl = product.imageUrl || 'https://placehold.co/300x300?text=No+Service+Image';
                cardContent = `
                     <img src="${imageUrl}" alt="${product.service_name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.service_name || 'No name'}</h2>
                     <p class="product-description">${product.service_descr || 'No description available'}</p>
                     <p class="product-price">R${product.service_price || 'N/A'}</p>
                `;
                card.onclick = () => openServiceDetailsModal(product);
                break;
          default:
                imageUrl = product.imageUrl || 'https://placehold.co/300x300?text=No+Image';
                cardContent = `
                     <img src="${imageUrl}" alt="${product.name || 'No name'}" class="product-image">
                     <h2 class="product-title">${product.name || 'No name'}</h2>
                     <p class="product-description">${product.descr || 'No description available'}</p>
                     <p class="product-price">R${product.price || 'N/A'}</p>
                `;
                card.onclick = () => openDefaultDetailsModal(product);
     }

     card.innerHTML = cardContent;
     return card;
}


    document.addEventListener("DOMContentLoaded", () => {
        // Fetch products when the page loads
        fetchProducts(category);
    });

