document.addEventListener("DOMContentLoaded", () => {
    fetchProducts();
});

async function fetchProducts() {
    try {
        const response = await fetch("fetch-products.php");
        const data = await response.json();

        if (!Array.isArray(data)) {
            console.error("Invalid product data:", data);
            return;
        }

        // Clear previous products before inserting new ones
        document.getElementById("products-grid").innerHTML = "";
        document.getElementById("vehicles-grid").innerHTML = "";
        document.getElementById("parts-grid").innerHTML = "";
        document.getElementById("extras-grid").innerHTML = "";
        document.getElementById("tax-free-grid").innerHTML = "";

        data.forEach(product => {
            const productCard = createProductCard(product);

            switch (product.category) {
            case "Vehicle":
                document.getElementById("vehicles-grid").appendChild(productCard);
                break;
            case "Part":
                document.getElementById("parts-grid").appendChild(productCard);
                break;
            case "Extra":
                document.getElementById("extras-grid").appendChild(productCard);
                break;
            case "Service":
                document.getElementById("tax-free-grid").appendChild(productCard);
                break;
            case "Product":
                document.getElementById("products-grid").appendChild(productCard);
                break;
            default:
                console.warn("Unknown product category:", product.category);
            }
        });

    } catch (error) {
        console.error("Error fetching products:", error);
    }
}

function createProductCard(product) {
    const card = document.createElement("div");

    if (product.category === "Vehicle") {
        card.classList.add("product-card", "vehicle-card");
        card.innerHTML = `
            <img id="mainImage-${product.id}" class="image-large" src="${product.image}" alt="${product.name}" />
            <div class="small-image-thumbnails">
                ${product.thumbnails.map((thumb, index) => `
                    <img src="${thumb}" alt="Thumbnail ${index + 1}" onclick="changeImage('${thumb}', 'mainImage-${product.id}')" />
                `).join("")}
            </div>
            <h2 class="product-title">${product.name}</h2>
            <p class="product-description">${product.description}</p>
            <p class="product-price">R${product.price}</p>
            <button class="open-vehicle-modal" data-vehicle-id="${product.id}">View Details</button>
        `;
    } else if (product.category === "Part") {
        card.classList.add("product-card", "part-card");
        card.innerHTML = `
            <img src="${product.image}" alt="${product.name}" class="product-image-small">
            <h2 class="product-title">${product.name}</h2>
            <p class="product-price">R${product.price}</p>
            <button class="view-part-details">View Details</button>
        `;
    } else if (product.category === "Extra") {
        card.classList.add("product-card", "extra-card");
        card.innerHTML = `
            <img src="${product.image}" alt="${product.name}" class="product-image-medium">
            <h2 class="product-title">${product.name}</h2>
            <p class="product-price">R${product.price}</p>
            <button class="view-extra-details">View Details</button>
        `;
    } else if (product.category === "Service") {
        card.classList.add("product-card", "service-card");
        card.innerHTML = `
            <div class="service-icon"><img src="${product.image}" alt="${product.name}"></div>
            <h2 class="product-title">${product.name}</h2>
            <p class="product-price">R${product.price}</p>
            <button class="view-service-details">View Details</button>
        `;
    } else if (product.category === "Product") {
        card.classList.add("product-card", "product-card");
        card.innerHTML = `
            <img src="${product.image}" alt="${product.name}" class="product-image">
            <h2 class="product-title">${product.name}</h2>
            <p class="product-description">${product.description}</p>
            <p class="product-price">R${product.price}</p>
            <button class="view-product-details">View Details</button>
        `;
    }

    return card;
}

function changeImage(newImage, imageId) {
    document.getElementById(imageId).src = newImage;
}
