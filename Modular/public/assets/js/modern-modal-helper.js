/**
 * Modern Modal Helper
 * Utilities to easily modernize existing modals
 */

// Ensure the modern modal CSS is loaded
function ensureModernModalCSS() {
    if (!document.getElementById('modern-modal-css')) {
        const link = document.createElement('link');
        link.id = 'modern-modal-css';
        link.rel = 'stylesheet';
        link.href = '/public/assets/css/modern-modals.css';
        document.head.appendChild(link);
    }
}

/**
 * Convert an existing modal to use modern classes
 * @param {string} modalSelector - CSS selector for the modal
 * @param {Object} options - Configuration options
 */
function modernizeModal(modalSelector, options = {}) {
    ensureModernModalCSS();
    
    const modal = document.querySelector(modalSelector);
    if (!modal) return;

    const config = {
        size: 'lg', // sm, md, lg, xl
        hasHeader: true,
        hasTabs: false,
        hasFooter: false,
        ...options
    };

    // Add modern overlay classes if it's a modal overlay
    if (modal.classList.contains('modal') || modal.classList.contains('modal-overlay')) {
        modal.classList.add('modern-modal-overlay');
    }

    // Find modal content/dialog
    const modalContent = modal.querySelector('.modal-content, .modal-dialog, .universal-product-modal-content, .modal-document');
    if (modalContent) {
        modalContent.classList.add('modern-modal', `size-${config.size}`);
    }

    // Modernize header
    if (config.hasHeader) {
        const header = modal.querySelector('.modal-header, .universal-product-modal-header, .modal-document-header');
        if (header) {
            header.classList.add('modern-modal-header');
            
            const title = header.querySelector('h1, h2, h3, .modal-title, .universal-product-modal-title, .modal-document-title');
            if (title) {
                title.classList.add('modern-modal-title');
            }

            const closeBtn = header.querySelector('.close, .modal-close, .universal-product-modal-close, .modal-document-close');
            if (closeBtn) {
                closeBtn.classList.add('modern-modal-close');
            }
        }
    }

    // Modernize tabs if present
    if (config.hasTabs) {
        const tabNav = modal.querySelector('.modal-tabs, .upm-tab-nav');
        if (tabNav) {
            tabNav.classList.add('modern-modal-tabs');
            
            const tabs = tabNav.querySelectorAll('.modal-tab, .upm-tab-btn');
            tabs.forEach(tab => {
                tab.classList.add('modern-modal-tab');
            });
        }

        const tabContents = modal.querySelectorAll('.tab-content, .upm-tab-pane');
        tabContents.forEach(content => {
            content.classList.add('modern-tab-content');
        });
    }

    // Modernize body
    const body = modal.querySelector('.modal-body, .upm-tab-content, .modal-document-body');
    if (body) {
        body.classList.add('modern-modal-body');
    }

    // Modernize footer if present
    if (config.hasFooter) {
        const footer = modal.querySelector('.modal-footer');
        if (footer) {
            footer.classList.add('modern-modal-footer');
            
            const buttons = footer.querySelectorAll('button');
            buttons.forEach(btn => {
                if (!btn.classList.contains('modern-btn')) {
                    btn.classList.add('modern-btn');
                    
                    // Add appropriate button type class based on existing classes or text
                    if (btn.classList.contains('btn-primary') || btn.textContent.toLowerCase().includes('save') || btn.textContent.toLowerCase().includes('submit')) {
                        btn.classList.add('modern-btn-primary');
                    } else if (btn.classList.contains('btn-danger') || btn.textContent.toLowerCase().includes('delete')) {
                        btn.classList.add('modern-btn-danger');
                    } else {
                        btn.classList.add('modern-btn-secondary');
                    }
                }
            });
        }
    }

    // Modernize form elements
    const formElements = modal.querySelectorAll('input, select, textarea');
    formElements.forEach(element => {
        if (element.type !== 'button' && element.type !== 'submit') {
            element.classList.add('form-input');
        }
    });

    const labels = modal.querySelectorAll('label');
    labels.forEach(label => {
        label.classList.add('form-label');
    });

    const formGroups = modal.querySelectorAll('.form-group, .upm-field');
    formGroups.forEach(group => {
        group.classList.add('form-group');
    });
}

/**
 * Show a modal with modern animations
 * @param {string} modalSelector - CSS selector for the modal
 */
function showModernModal(modalSelector) {
    const modal = document.querySelector(modalSelector);
    if (!modal) return;

    modal.style.display = 'flex';
    modal.classList.add('active');
    
    // Add backdrop click to close
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            hideModernModal(modalSelector);
        }
    });

    // Add escape key to close
    const escapeHandler = (e) => {
        if (e.key === 'Escape') {
            hideModernModal(modalSelector);
            document.removeEventListener('keydown', escapeHandler);
        }
    };
    document.addEventListener('keydown', escapeHandler);
}

/**
 * Hide a modal with modern animations
 * @param {string} modalSelector - CSS selector for the modal
 */
function hideModernModal(modalSelector) {
    const modal = document.querySelector(modalSelector);
    if (!modal) return;

    modal.classList.remove('active');
    
    // Wait for animation to complete before hiding
    setTimeout(() => {
        modal.style.display = 'none';
    }, 300);
}

/**
 * Auto-modernize all modals on page load
 */
function autoModernizeModals() {
    ensureModernModalCSS();
    
    // Auto-detect and modernize common modal patterns
    const modalConfigs = [
        {
            selector: '.universal-product-modal',
            options: { size: 'xl', hasHeader: true, hasTabs: true }
        },
        {
            selector: '.modal-document-overlay',
            options: { size: 'xl', hasHeader: true }
        },
        {
            selector: '.modal',
            options: { size: 'lg', hasHeader: true, hasTabs: true }
        }
    ];

    modalConfigs.forEach(config => {
        const modals = document.querySelectorAll(config.selector);
        modals.forEach(modal => {
            modernizeModal(config.selector, config.options);
        });
    });
}

// Export functions for global use
window.modernizeModal = modernizeModal;
window.showModernModal = showModernModal;
window.hideModernModal = hideModernModal;
window.autoModernizeModals = autoModernizeModals;

// Auto-run when DOM is loaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', autoModernizeModals);
} else {
    autoModernizeModals();
}

console.log('🎨 Modern Modal Helper loaded - All modals have been modernized!'); 