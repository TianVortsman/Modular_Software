import { ProductAPI } from './product-api.js';
import './product-modals.js';
console.log('product-screen.js loaded');
// --- ProductScreenManager: Handles product screen (view, grid, filters, cards, etc.) ---
class ProductScreenManager {
    constructor() {
        console.log('ProductScreenManager instantiated');
        this.currentFilters = {
            search: '',
            category: '',
            subcategory: ''
        };
        this.allProducts = [];
    }
    initializeFilters() {
        const searchInput = document.getElementById('search-input');
        const categoryFilter = document.getElementById('category-filter');
        const subcategoryFilter = document.getElementById('subcategory-filter');
        const clearFiltersBtn = document.getElementById('clear-filters');
        if (searchInput) {
            searchInput.addEventListener('input', (e) => {
                this.currentFilters.search = e.target.value.toLowerCase();
                this.applyFilters();
            });
        }
        if (categoryFilter) {
            categoryFilter.addEventListener('change', (e) => {
                this.currentFilters.category = e.target.value;
                this.updateSubcategoryFilter();
                this.applyFilters();
            });
        }
        if (subcategoryFilter) {
            subcategoryFilter.addEventListener('change', (e) => {
                this.currentFilters.subcategory = e.target.value;
                this.applyFilters();
            });
        }
        if (clearFiltersBtn) {
            clearFiltersBtn.addEventListener('click', () => {
                this.clearAllFilters();
            });
        }
    }
    clearAllFilters() {
        const searchInput = document.getElementById('search-input');
        const categoryFilter = document.getElementById('category-filter');
        const subcategoryFilter = document.getElementById('subcategory-filter');
        this.currentFilters.search = '';
        this.currentFilters.category = '';
        this.currentFilters.subcategory = '';
        if (searchInput) searchInput.value = '';
        if (categoryFilter) categoryFilter.value = '';
        if (subcategoryFilter) {
            subcategoryFilter.innerHTML = '<option value="">All Subcategories</option>';
            subcategoryFilter.value = '';
        }
        this.applyFilters();
    }
    async updateSubcategoryFilter() {
        const subcategoryFilter = document.getElementById('subcategory-filter');
        const categoryFilter = document.getElementById('category-filter');
        if (!subcategoryFilter || !categoryFilter) return;
        subcategoryFilter.innerHTML = '<option value="">All Subcategories</option>';
        if (this.currentFilters.category) {
            // Use ProductModalAPI for fetching subcategories
            window.ProductModalAPI.fetchProductSubcategories(this.currentFilters.category).then(data => {
                if (data.success && Array.isArray(data.data)) {
                    data.data.forEach(sub => {
                        const option = document.createElement('option');
                        option.value = sub.subcategory_id;
                        option.textContent = sub.subcategory_name;
                        subcategoryFilter.appendChild(option);
                    });
                }
            });
        }
    }
    applyFilters() {
        const sectionType = this.getCurrentSectionType();
        let filteredProducts = this.allProducts.filter(product => {
            if (this.currentFilters.search) {
                const searchMatch = product.product_name.toLowerCase().includes(this.currentFilters.search) ||
                                   product.sku?.toLowerCase().includes(this.currentFilters.search) ||
                                   product.barcode?.toLowerCase().includes(this.currentFilters.search);
                if (!searchMatch) return false;
            }
            if (this.currentFilters.category && product.category_id != this.currentFilters.category) {
                return false;
            }
            if (this.currentFilters.subcategory && product.subcategory_id != this.currentFilters.subcategory) {
                return false;
            }
            if (sectionType === 'product') {
                if (product.product_status !== 'active') return false;
                if ((product.product_type_name || '').toLowerCase() !== 'product') return false;
            } else if (sectionType === 'part') {
                if ((product.product_type_name || '').toLowerCase() !== 'part') return false;
                if (product.product_status !== 'active') return false;
            } else if (sectionType === 'service') {
                if ((product.product_type_name || '').toLowerCase() !== 'service') return false;
                if (product.product_status !== 'active') return false;
            } else if (sectionType === 'extra') {
                if ((product.product_type_name || '').toLowerCase() !== 'extra') return false;
                if (product.product_status !== 'active') return false;
            } else if (sectionType === 'discontinued') {
                if (product.product_status !== 'discontinued') return false;
            } else if (sectionType === 'disabled') {
                if (product.product_status !== 'inactive' && product.product_status !== 'disabled') return false;
            }
            return true;
        });
        const gridIdMap = {
            'product': 'products-grid',
            'part': 'parts-grid',
            'service': 'services-grid',
            'extra': 'extras-grid',
            'discontinued': 'discontinued-grid',
            'disabled': 'disabled-grid',
        };
        const gridId = gridIdMap[sectionType];
        if (gridId) {
            const grid = document.getElementById(gridId);
            if (grid) {
                grid.innerHTML = '';
                filteredProducts.forEach(product => {
                    const card = this.createProductCard(product, sectionType);
                    grid.appendChild(card);
                });
            }
        }
    }
    async refreshProductList() {
        // Use ProductModalAPI for fetching products
        ProductAPI.fetchProducts().then(data => {
            if (data.success && Array.isArray(data.data)) {
                this.allProducts = data.data;
                this.applyFilters();
            }
        });
    }
    createProductCard(product, type) {
        const productCard = document.createElement('div');
        productCard.className = 'product-card';
        productCard.setAttribute('data-product-id', product.product_id);
        const accountNumber = document.querySelector('meta[name="account-number"]')?.content || 'ACC002';
        let imageUrl = product.image_url || `/Uploads/${accountNumber}/products/${type}/${product.product_id}.webp`;
        let typeBadge = `<span class="type-badge">${type || 'Product'}</span>`;
        let statusBadge = '';
        if (product.status === 'discontinued') statusBadge = '<span class="status-badge">Discontinued</span>';
        else if (product.status === 'disabled' || product.status === 'inactive') statusBadge = '<span class="status-badge">Disabled</span>';
        else if (product.stock_quantity !== undefined && product.stock_quantity <= 2) statusBadge = '<span class="status-badge">Low Stock</span>';
        // Use backend field names for badges and info
        let badges = '';
        if (product.category_name) badges += `<span class="category-badge">${product.category_name}</span>`;
        if (product.subcategory_name) badges += `<span class="subcategory-badge">${product.subcategory_name}</span>`;
        let supplier = product.supplier_name ? `<div class="supplier"><span class="material-icons" style="font-size:1rem;vertical-align:middle;">local_shipping</span> ${product.supplier_name}</div>` : '';
        productCard.innerHTML = `
            <div class="product-image">
                <img src="${imageUrl}" alt="${product.product_name}" onerror="this.src='https://placehold.co/300x300?text=No+Image'">
                <div class="image-overlay">${typeBadge}${statusBadge}</div>
            </div>
            <div class="product-info">
                <div class="badges">${badges}</div>
                <h3>${product.product_name}</h3>
                <div class="price">R${product.product_price}</div>
                <div class="sku"><span class="material-icons" style="font-size:1rem;vertical-align:middle;">qr_code</span> SKU: ${product.sku || 'N/A'}</div>
                <div class="stock"><span class="material-icons" style="font-size:1rem;vertical-align:middle;">inventory_2</span> Stock: ${product.stock_quantity ?? 0}</div>
                ${supplier}
            </div>
        `;
        // Drag-and-drop image upload
        productCard.addEventListener('dragover', e => {
            e.preventDefault();
            productCard.classList.add('drag-over');
        });
        productCard.addEventListener('dragleave', e => {
            productCard.classList.remove('drag-over');
        });
        productCard.addEventListener('drop', e => {
            e.preventDefault();
            productCard.classList.remove('drag-over');
            const files = e.dataTransfer.files;
            if (files && files.length > 0) {
                const productId = productCard.getAttribute('data-product-id') || productCard.dataset.productId || product.product_id;
                if (productId) {
                    // Show overlay/status
                    let overlay = productCard.querySelector('.image-upload-overlay');
                    if (!overlay) {
                        overlay = document.createElement('div');
                        overlay.className = 'image-upload-overlay';
                        overlay.style.position = 'absolute';
                        overlay.style.top = 0;
                        overlay.style.left = 0;
                        overlay.style.width = '100%';
                        overlay.style.height = '100%';
                        overlay.style.background = 'rgba(0,0,0,0.5)';
                        overlay.style.color = '#fff';
                        overlay.style.display = 'flex';
                        overlay.style.alignItems = 'center';
                        overlay.style.justifyContent = 'center';
                        overlay.style.fontSize = '1.2rem';
                        overlay.style.zIndex = 10;
                        productCard.style.position = 'relative';
                        productCard.appendChild(overlay);
                    }
                    overlay.textContent = 'Uploading...';
                    // Upload image
                    const file = files[0];
                    const ext = file.name.split('.').pop().toLowerCase();
                    // Determine type/category for upload
                    let uploadType = (product.product_type_name || type || 'product').toLowerCase();
                    ProductAPI.uploadImage(file, productId, uploadType).then(res => {
                        if (res.success && res.url) {
                            overlay.textContent = 'Image uploaded!';
                            const img = productCard.querySelector('img');
                            img.src = '/' + res.url.replace(/^\/*/, '') + '?t=' + Date.now();
                            if (window.productScreenManager && Array.isArray(window.productScreenManager.allProducts)) {
                                const idx = window.productScreenManager.allProducts.findIndex(p => String(p.product_id) === String(productId));
                                if (idx !== -1) {
                                    window.productScreenManager.allProducts[idx].image_url = '/' + res.url.replace(/^\/*/, '');
                                }
                            }
                            setTimeout(() => {
                                overlay.remove();
                                if (window.productScreenManager && typeof window.productScreenManager.refreshProductList === 'function') {
                                    window.productScreenManager.refreshProductList();
                                }
                            }, 1200);
                        } else {
                            overlay.textContent = 'Unsupported Image or failed to convert to WebP';
                            setTimeout(() => overlay.remove(), 3000);
                        }
                    }).catch(() => {
                        overlay.textContent = 'Unsupported Image or failed to convert to WebP';
                        setTimeout(() => overlay.remove(), 3000);
                    });
                }
            }
        });
        // Double-click to edit
        productCard.addEventListener('dblclick', () => {
            console.log('Double-clicked product card:', product.product_id);
            if (window.productModalUI) {
                window.productModalUI.openModal('edit', product.product_id);
            } else {
                console.error('window.productModalUI is not initialized!');
            }
        });
        // Selection logic
        productCard.addEventListener('click', e => {
            if (e.ctrlKey || e.metaKey) {
                // Multi-select
                if (selectedProductIds.has(product.product_id)) {
                    selectedProductIds.delete(product.product_id);
                    productCard.classList.remove('selected');
                } else {
                    selectedProductIds.add(product.product_id);
                    productCard.classList.add('selected');
                }
            } else {
                // Single select
                clearProductSelection();
                selectedProductIds.add(product.product_id);
                productCard.classList.add('selected');
            }
            updateProductDeleteButton();
            e.stopPropagation();
        });
        // Right-click context menu
        productCard.addEventListener('contextmenu', e => {
            e.preventDefault();
            showProductContextMenu(e.clientX, e.clientY, product.product_id);
        });
        // Deselect on double-click (edit still works)
        productCard.addEventListener('dblclick', e => {
            clearProductSelection();
            updateProductDeleteButton();
        });
        return productCard;
    }
    getCurrentSectionType() {
        const sectionTypeMap = {
            'products': 'product',
            'parts': 'part',
            'services': 'service',
            'extras': 'extra',
            'discontinued': 'discontinued',
            'disabled': 'disabled',
        };
        const activeSection = document.querySelector('.tab-content.active');
        if (!activeSection) return 'product';
        const sectionId = activeSection.id;
        return sectionTypeMap[sectionId] || 'product';
    }
    setupSectionTabSwitching() {
        const tabsContent = document.querySelector('.tabs-content');
        if (!tabsContent) return;
        let lastSectionId = this.getCurrentSectionType();
        const observer = new MutationObserver(() => {
            const sectionType = this.getCurrentSectionType();
            if (sectionType !== lastSectionId) {
                lastSectionId = sectionType;
                this.populateFilterDropdownsForSection(sectionType);
                this.applyFilters();
            }
        });
        document.querySelectorAll('.tab-content').forEach(tab => {
            observer.observe(tab, { attributes: true, attributeFilter: ['class'] });
        });
        document.addEventListener('DOMContentLoaded', () => {
            const sectionType = this.getCurrentSectionType();
            this.populateFilterDropdownsForSection(sectionType);
            this.applyFilters();
        });
    }
    async populateFilterDropdownsForSection(sectionType) {
        if (!sectionTypeToTypeId['product']) await updateSectionTypeToTypeIdMap();
        const typeId = sectionTypeToTypeId[sectionType] || sectionTypeToTypeId['product'];
        await this.populateCategoryDropdown(typeId);
        const subcategoryFilter = document.getElementById('subcategory-filter');
        if (subcategoryFilter) {
            subcategoryFilter.innerHTML = '<option value="">All Subcategories</option>';
        }
    }
    async populateFilterDropdowns() {
        // Use ProductModalAPI for fetching dropdowns if needed
    }
    async populateCategoryDropdown(typeId) {
        const categoryFilter = document.getElementById('category-filter');
        if (!categoryFilter) return;
        const res = await ProductAPI.fetchProductCategories(typeId);
        categoryFilter.innerHTML = '<option value="">All Categories</option>';
        if (res.success && Array.isArray(res.data)) {
            res.data.forEach(cat => {
                const option = document.createElement('option');
                option.value = cat.category_id;
                option.textContent = cat.category_name;
                categoryFilter.appendChild(option);
            });
        }
        const subcategoryFilter = document.getElementById('subcategory-filter');
        if (subcategoryFilter) {
            subcategoryFilter.innerHTML = '<option value="">All Subcategories</option>';
        }
    }
    async populateSubcategoryDropdown(categoryId) {
        const subcategoryFilter = document.getElementById('subcategory-filter');
        if (!subcategoryFilter) return;
        subcategoryFilter.innerHTML = '<option value="">All Subcategories</option>';
        if (!categoryId) return;
        const res = await ProductAPI.fetchProductSubcategories(categoryId);
        if (res.success && Array.isArray(res.data)) {
            res.data.forEach(sub => {
                const option = document.createElement('option');
                option.value = sub.subcategory_id;
                option.textContent = sub.subcategory_name;
                subcategoryFilter.appendChild(option);
            });
        }
    }
}

// Initialize ProductScreenManager and wire up add product button

document.addEventListener('DOMContentLoaded', async () => {
    window.productScreenManager = new ProductScreenManager();
    window.productScreenManager.initializeFilters();
    window.productScreenManager.refreshProductList();
    window.productScreenManager.setupSectionTabSwitching();
    // Ensure category dropdown is populated on initial load
    const sectionType = window.productScreenManager.getCurrentSectionType();
    window.productScreenManager.populateFilterDropdownsForSection(sectionType);

    // --- Dynamic typeId map from DB ---
    const typeNameToBtn = {
        'Product': 'add-products-open-btn',
        'Part': 'add-parts-open-btn',
        'Service': 'add-services-open-btn',
        'Extra': 'add-extras-open-btn',
        'Discontinued': 'add-discontinued-open-btn',
        'Disabled': 'add-disabled-open-btn',
    };
    const typeRes = await ProductAPI.fetchProductTypes();
    if (typeRes.success && Array.isArray(typeRes.data)) {
        // Map type name (capitalized) to id
        const typeMap = {};
        typeRes.data.forEach(type => {
            typeMap[type.product_type_name] = type.product_type_id;
        });
        // Wire up add buttons using DB IDs
        Object.entries(typeNameToBtn).forEach(([typeName, btnClass]) => {
            const typeId = typeMap[typeName];
            if (!typeId) return;
            document.querySelectorAll('.' + btnClass).forEach(btn => {
                btn.addEventListener('click', () => {
                    if (window.productModalUI) {
                        window.productModalUI.openModal('add', null, typeId);
                    }
                });
            });
        });
    }
    // Wire up category filter to populate subcategories
    const categoryFilter = document.getElementById('category-filter');
    if (categoryFilter) {
        categoryFilter.addEventListener('change', (e) => {
            window.productScreenManager.populateSubcategoryDropdown(e.target.value);
        });
    }
    // Wait for product cards to be rendered
    setTimeout(() => {
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('dragover', e => {
                e.preventDefault();
                card.classList.add('drag-over');
            });
            card.addEventListener('dragleave', e => {
                card.classList.remove('drag-over');
            });
            card.addEventListener('drop', e => {
                e.preventDefault();
                card.classList.remove('drag-over');
                const files = e.dataTransfer.files;
                if (files && files.length > 0) {
                    const productId = card.getAttribute('data-product-id') || card.dataset.productId;
                    if (productId) {
                        // Show overlay/status
                        let overlay = card.querySelector('.image-upload-overlay');
                        if (!overlay) {
                            overlay = document.createElement('div');
                            overlay.className = 'image-upload-overlay';
                            overlay.style.position = 'absolute';
                            overlay.style.top = 0;
                            overlay.style.left = 0;
                            overlay.style.width = '100%';
                            overlay.style.height = '100%';
                            overlay.style.background = 'rgba(0,0,0,0.5)';
                            overlay.style.color = '#fff';
                            overlay.style.display = 'flex';
                            overlay.style.alignItems = 'center';
                            overlay.style.justifyContent = 'center';
                            overlay.style.fontSize = '1.2rem';
                            overlay.style.zIndex = 10;
                            card.style.position = 'relative';
                            card.appendChild(overlay);
                        }
                        overlay.textContent = 'Uploading...';
                        // Upload image
                        const file = files[0];
                        const ext = file.name.split('.').pop().toLowerCase();
                        // Determine type/category for upload
                        let uploadType = (product.product_type_name || type || 'product').toLowerCase();
                        ProductAPI.uploadImage(file, productId, uploadType).then(res => {
                            if (res.success && res.url) {
                                overlay.textContent = 'Image uploaded!';
                                const img = card.querySelector('img');
                                img.src = '/' + res.url.replace(/^\/*/, '') + '?t=' + Date.now();
                                if (window.productScreenManager && Array.isArray(window.productScreenManager.allProducts)) {
                                    const idx = window.productScreenManager.allProducts.findIndex(p => String(p.product_id) === String(productId));
                                    if (idx !== -1) {
                                        window.productScreenManager.allProducts[idx].image_url = '/' + res.url.replace(/^\/*/, '');
                                    }
                                }
                                setTimeout(() => {
                                    overlay.remove();
                                    if (window.productScreenManager && typeof window.productScreenManager.refreshProductList === 'function') {
                                        window.productScreenManager.refreshProductList();
                                    }
                                }, 1200);
                            } else {
                                overlay.textContent = 'Unsupported Image or failed to convert to WebP';
                                setTimeout(() => overlay.remove(), 3000);
                            }
                        }).catch(() => {
                            overlay.textContent = 'Unsupported Image or failed to convert to WebP';
                            setTimeout(() => overlay.remove(), 3000);
                        });
                    }
                }
            });
        });
    }, 1000); // Wait for cards to render

    // Ensure Delete Selected button is always present
    let btn = document.getElementById('productDeleteSelectedBtn');
    if (!btn) {
        btn = document.createElement('button');
        btn.id = 'productDeleteSelectedBtn';
        btn.textContent = 'Delete Selected';
        btn.className = 'delete-selected-btn';
        btn.style.display = 'none';
        btn.onclick = async () => {
            const idsToDelete = Array.from(selectedProductIds);
            console.log('Delete Selected clicked. selectedProductIds:', idsToDelete);
            if (idsToDelete.length === 0) return;
            if (!window.productModalUI) return;
            const confirmed = await window.productModalUI.confirmWithResponseModal('Delete selected products?');
            if (!confirmed) return;
            // Log before deletion
            console.log('Deleting product IDs:', idsToDelete);
            await Promise.all(idsToDelete.map(pid => window.ProductAPI.deleteProduct(pid)));
            clearProductSelection();
            if (window.productScreenManager) window.productScreenManager.refreshProductList();
        };
        const header = document.querySelector('.header');
        if (header) header.appendChild(btn);
    }
});

// --- Product Card Selection, Multi-Select, and Context Menu ---
let selectedProductIds = new Set();
let contextMenuEl = null;

function clearProductSelection() {
    document.querySelectorAll('.product-card.selected').forEach(card => card.classList.remove('selected'));
    selectedProductIds.clear();
    updateProductDeleteButton();
}

function updateProductDeleteButton() {
    let btn = document.getElementById('productDeleteSelectedBtn');
    if (!btn) {
        btn = document.createElement('button');
        btn.id = 'productDeleteSelectedBtn';
        btn.textContent = 'Delete Selected';
        btn.className = 'delete-selected-btn';
        btn.style.display = 'none';
        btn.onclick = async () => {
            const idsToDelete = Array.from(selectedProductIds);
            console.log('Delete Selected clicked. selectedProductIds:', idsToDelete);
            if (idsToDelete.length === 0) return;
            if (!window.productModalUI) return;
            const confirmed = await window.productModalUI.confirmWithResponseModal('Delete selected products?');
            if (!confirmed) return;
            // Log before deletion
            console.log('Deleting product IDs:', idsToDelete);
            await Promise.all(idsToDelete.map(pid => window.ProductAPI.deleteProduct(pid)));
            clearProductSelection();
            if (window.productScreenManager) window.productScreenManager.refreshProductList();
        };
        const header = document.querySelector('.header');
        if (header) header.appendChild(btn);
    }
    btn.style.display = selectedProductIds.size > 0 ? '' : 'none';
}

function showProductContextMenu(x, y, productId) {
    if (contextMenuEl) contextMenuEl.remove();
    // Find the product object by ID
    const product = (window.productScreenManager && Array.isArray(window.productScreenManager.allProducts))
        ? window.productScreenManager.allProducts.find(p => String(p.product_id) === String(productId))
        : null;
    let menuHtml = `
        <button class="edit">Edit</button>
        <button class="delete">Delete</button>
        <button class="upload-image">Upload Image</button>
    `;
    if (product && (product.product_status === 'inactive' || product.product_status === 'disabled')) {
        menuHtml += `<button class="reactivate">Reactivate</button>`;
    } else if (product && product.product_status === 'discontinued') {
        menuHtml += `<button class="restore">Restore</button>`;
    } else {
        menuHtml += `<button class="deactivate">Deactivate</button>`;
        menuHtml += `<button class="discontinue">Discontinue</button>`;
    }
    contextMenuEl = document.createElement('div');
    contextMenuEl.className = 'product-context-menu';
    contextMenuEl.innerHTML = menuHtml;
    contextMenuEl.style.position = 'fixed';
    contextMenuEl.style.left = x + 'px';
    contextMenuEl.style.top = y + 'px';
    contextMenuEl.style.zIndex = 10000;
    document.body.appendChild(contextMenuEl);
    contextMenuEl.querySelector('.edit').onclick = () => {
        if (window.productModalUI) window.productModalUI.openModal('edit', productId);
        contextMenuEl.remove();
    };
    contextMenuEl.querySelector('.delete').onclick = async () => {
        if (window.productModalUI) {
            const confirmed = await window.productModalUI.confirmWithResponseModal('Delete this product?');
            if (confirmed) {
                await window.ProductAPI.deleteProduct(productId);
                if (window.productScreenManager) window.productScreenManager.refreshProductList();
            }
        }
        contextMenuEl.remove();
    };
    contextMenuEl.querySelector('.upload-image').onclick = async () => {
        if (window.productModalUI) {
            await window.productModalUI.openModal('edit', productId);
            setTimeout(() => {
                const imageInput = document.getElementById('universalItemImage');
                if (imageInput) imageInput.click();
            }, 400);
        }
        contextMenuEl.remove();
    };
    if (contextMenuEl.querySelector('.reactivate')) {
        contextMenuEl.querySelector('.reactivate').onclick = async () => {
            await ProductAPI.updateStatus(productId, 'active');
            if (window.productScreenManager) window.productScreenManager.refreshProductList();
            contextMenuEl.remove();
        };
    }
    if (contextMenuEl.querySelector('.restore')) {
        contextMenuEl.querySelector('.restore').onclick = async () => {
            await ProductAPI.updateStatus(productId, 'active');
            if (window.productScreenManager) window.productScreenManager.refreshProductList();
            contextMenuEl.remove();
        };
    }
    if (contextMenuEl.querySelector('.deactivate')) {
        contextMenuEl.querySelector('.deactivate').onclick = async () => {
            await ProductAPI.updateStatus(productId, 'inactive');
            if (window.productScreenManager) window.productScreenManager.refreshProductList();
            contextMenuEl.remove();
        };
    }
    if (contextMenuEl.querySelector('.discontinue')) {
        contextMenuEl.querySelector('.discontinue').onclick = async () => {
            await ProductAPI.updateStatus(productId, 'discontinued');
            if (window.productScreenManager) window.productScreenManager.refreshProductList();
            contextMenuEl.remove();
        };
    }
    document.addEventListener('click', () => contextMenuEl && contextMenuEl.remove(), { once: true });
}

// Deselect all on background click
window.addEventListener('click', e => {
    if (!e.target.closest('.product-card')) {
        clearProductSelection();
    }
});

// --- Dynamic sectionType to product_type_id mapping ---
let sectionTypeToTypeId = {};

async function updateSectionTypeToTypeIdMap() {
    const typeRes = await ProductAPI.fetchProductTypes();
    if (typeRes.success && Array.isArray(typeRes.data)) {
        // Lowercase type names for mapping
        typeRes.data.forEach(type => {
            sectionTypeToTypeId[type.product_type_name.toLowerCase()] = type.product_type_id;
        });
    }
}