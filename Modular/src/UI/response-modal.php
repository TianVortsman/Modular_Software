<!-- Response Modal - Auto-injected by helpers.js, this is a fallback for manual includes -->
<div id="modalResponse" class="custom-modal hidden">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <span id="modalResponseIcon" class="custom-modal-icon">✔</span>
            <h2 id="modalResponseTitle">Success</h2>
            <button class="custom-modal-close-x" onclick="closeResponseModal()" aria-label="Close">&times;</button>
        </div>
        <div class="custom-modal-body">
            <p id="modalResponseMessage">Your request was successful!</p>
            <div id="modalResponseDetails" class="custom-modal-details hidden"></div>
        </div>
        <div class="custom-modal-footer">
            <button onclick="closeResponseModal()" class="custom-modal-close-btn">OK</button>
        </div>
    </div>
</div>

<style>
    /* Enhanced Modal Container */
    .custom-modal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100vw;
        height: 100vh;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 2147483647;
        pointer-events: auto;
        backdrop-filter: blur(2px);
        transition: opacity 0.3s ease-in-out;
    }
    
    .custom-modal.hidden {
        display: none !important;
    }
    
    .custom-modal-content {
        background: var(--color-secondary, #ffffff);
        color: var(--color-text-light, #333333);
        padding: var(--spacing-large, 24px);
        border-radius: var(--radius-medium, 8px);
        box-shadow: var(--shadow-medium, 0 4px 12px rgba(0, 0, 0, 0.15));
        text-align: center;
        width: 90%;
        max-width: 450px;
        min-width: 300px;
        animation: slide-down 0.3s ease-in-out;
        position: relative;
        z-index: 2147483648;
        border: 1px solid var(--color-border, #e0e0e0);
    }
    
    .custom-modal-header {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        margin-bottom: var(--spacing-medium, 16px);
        position: relative;
        padding-bottom: var(--spacing-small, 8px);
        border-bottom: 1px solid var(--color-border, #e0e0e0);
    }
    
    .custom-modal-close-x {
        position: absolute;
        top: -8px;
        right: -8px;
        background: none;
        border: none;
        font-size: 24px;
        color: var(--color-text-light, #666);
        cursor: pointer;
        width: 32px;
        height: 32px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    
    .custom-modal-close-x:hover {
        background: var(--color-hover, #f0f0f0);
        color: var(--color-text-dark, #000);
        transform: scale(1.1);
    }
    
    .custom-modal-icon {
        font-size: 3rem;
        font-weight: bold;
        margin-bottom: var(--spacing-small, 8px);
        display: block;
    }
    
    .custom-modal-icon.success {
        color: var(--color-success, #28a745);
    }
    
    .custom-modal-icon.error {
        color: var(--color-error, #dc3545);
    }
    
    .custom-modal-icon.warning {
        color: var(--color-warning, #ffc107);
    }
    
    .custom-modal-icon.info {
        color: var(--color-info, #17a2b8);
    }
    
    .custom-modal-header h2 {
        margin: 0;
        font-size: var(--font-size-large, 1.25rem);
        font-weight: 600;
    }
    
    .custom-modal-body p {
        font-size: var(--font-size-base, 1rem);
        line-height: var(--line-height, 1.5);
        margin: var(--spacing-small, 8px) 0;
        text-align: left;
        word-wrap: break-word;
    }
    
    .custom-modal-details {
        margin-top: var(--spacing-medium, 16px);
        padding: var(--spacing-small, 8px);
        background: var(--color-background, #f8f9fa);
        border-radius: var(--radius-small, 4px);
        border-left: 3px solid var(--color-info, #17a2b8);
        text-align: left;
        font-size: var(--font-size-small, 0.875rem);
        font-family: monospace;
        white-space: pre-wrap;
        word-wrap: break-word;
        max-height: 200px;
        overflow-y: auto;
    }
    
    .custom-modal-details.hidden {
        display: none;
    }
    
    .custom-modal-footer {
        margin-top: var(--spacing-medium, 16px);
        display: flex;
        justify-content: center;
        gap: 12px;
        padding-top: var(--spacing-small, 8px);
        border-top: 1px solid var(--color-border, #e0e0e0);
    }
    
    .custom-modal-close-btn {
        background: var(--color-primary, #007bff);
        color: var(--color-text-dark, #ffffff);
        border: none;
        padding: var(--spacing-small, 8px) var(--spacing-medium, 16px);
        font-size: var(--font-size-base, 1rem);
        font-family: var(--font-primary, inherit);
        border-radius: var(--radius-small, 4px);
        cursor: pointer;
        transition: all 0.2s ease;
        min-width: 80px;
        font-weight: 500;
    }
    
    .custom-modal-close-btn:hover {
        background: var(--color-hover, #0056b3);
        transform: translateY(-1px);
        box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
    }
    
    .custom-modal-close-btn:active {
        transform: translateY(0);
    }
    
    /* Animation */
    @keyframes slide-down {
        from {
            transform: translateY(-30px) scale(0.95);
            opacity: 0;
        }
        to {
            transform: translateY(0) scale(1);
            opacity: 1;
        }
    }
    
    /* Dark mode support */
    @media (prefers-color-scheme: dark) {
        .custom-modal-content {
            background: var(--color-secondary, #2d3748);
            color: var(--color-text-light, #ffffff);
            border-color: var(--color-border, #4a5568);
        }
        
        .custom-modal-details {
            background: var(--color-background, #1a202c);
            color: var(--color-text-light, #e2e8f0);
        }
    }
    
    /* Mobile responsiveness */
    @media (max-width: 480px) {
        .custom-modal-content {
            width: 95%;
            padding: var(--spacing-medium, 16px);
        }
        
        .custom-modal-icon {
            font-size: 2.5rem;
        }
        
        .custom-modal-header h2 {
            font-size: var(--font-size-base, 1rem);
        }
    }
</style>

<script>
// Check if modal functions are already loaded from helpers.js
if (typeof window.showResponseModal === 'undefined') {
    console.warn('Response modal auto-injection failed, using fallback');
    
    // Fallback implementation
    window.showResponseModal = function(message, type = 'info', persistent = false, confirm = false) {
        const modal = document.getElementById('modalResponse');
        const title = document.getElementById('modalResponseTitle');
        const msg = document.getElementById('modalResponseMessage');
        const icon = document.getElementById('modalResponseIcon');
        
        if (!modal || !title || !msg || !icon) {
            alert(message);
            return Promise.resolve(false);
        }
        
        const typeConfig = {
            success: { title: 'Success', icon: '✔' },
            error: { title: 'Error', icon: '✖' },
            warning: { title: 'Warning', icon: '⚠' },
            info: { title: 'Information', icon: 'ℹ' }
        };
        
        const config = typeConfig[type] || typeConfig.info;
        title.textContent = config.title;
        msg.textContent = message;
        icon.textContent = config.icon;
        
        modal.classList.remove('hidden');
        
        if (!persistent) {
            setTimeout(() => modal.classList.add('hidden'), 3000);
        }
        
        return Promise.resolve(true);
    };
    
    window.closeResponseModal = function() {
        const modal = document.getElementById('modalResponse');
        if (modal) modal.classList.add('hidden');
    };
}
</script>
