<!-- Loading Modal -->
<div id="loadingModal" class="loading-modal" style="display:none;">
    <div class="loading-modal-content">
        <span class="loader"></span>
        <span id="loadingMessage">Loading...</span>
    </div>
</div>

<style>
    /* Unique modal styles */
    #loadingModal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.7); /* Dark overlay */
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000011; /* Ensure it's on top */
        opacity: 0;
        visibility: visible;
        transition: opacity 0.3s ease-in-out, visibility 0.3s ease-in-out;
    }

    /* Hide modal by default */
    #loadingModal.hidden {
        display: none !important;
        visibility: hidden !important;
        pointer-events: none !important;
    }

    /* Modal content box */
    .loading-modal-content {
        background-color: var(--modal-bg, #fff);  /* Default to white if var not defined */
        padding: 2rem;
        border-radius: 8px;
        box-shadow: 0px 4px 8px rgba(0, 0, 0, 0.2);
        text-align: center;
        color: var(--color-text-light, #333);  /* Default to dark text if var not defined */
        max-width: 300px;
        width: 100%;
        opacity: 1;
    }

    /* Spinner animation */
    .loader {
        border: 4px solid rgba(0, 0, 0, 0.1);
        border-top: 4px solid #3498db;  /* Use hardcoded blue if var not defined */
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
        margin: 0 auto 1rem auto;
    }

    /* Spinner animation */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Message styling */
    .modal-message {
        margin-top: 1rem;
        font-size: 1rem;
        color: var(--color-text-light, #333);
        font-weight: 500;
    }
</style>

<script>
    // This function shows the loading modal
    function showLoadingModal(message = "Loading...") {
        const modal = document.getElementById('loadingModal');
        if (!modal) {
            console.error('Loading modal element not found');
            return;
        }
        
        const messageElement = modal.querySelector('#loadingMessage');
        if (messageElement) {
            messageElement.textContent = message;
        }
        
        // First remove hidden class and set display to flex
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        
        // Force a reflow before setting opacity to ensure display change is applied
        modal.offsetHeight;
        
        // Now set opacity to make it visible
        modal.style.opacity = 1;
        modal.style.visibility = 'visible';
        
        // Safety timeout - hide after 30 seconds if not hidden
        window._loadingModalTimeout = setTimeout(() => {
            if (modal && modal.style.opacity == 1) {
                console.warn('Loading modal timed out after 30 seconds');
                hideLoadingModal();
            }
        }, 30000);
    }

    // This function hides the loading modal
    function hideLoadingModal() {
        const modal = document.getElementById('loadingModal');
        if (!modal) {
            console.error('Loading modal element not found when hiding');
            return;
        }
        
        // Clear any existing timeout
        if (window._loadingModalTimeout) {
            clearTimeout(window._loadingModalTimeout);
        }
        
        // First set opacity to 0 for fade-out animation
        modal.style.opacity = 0;
        
        // Use setTimeout to ensure the transition completes before hiding
        setTimeout(() => {
            // Then hide the modal completely
            modal.classList.add('hidden');
            modal.style.display = 'none';
            modal.style.visibility = 'hidden';
        }, 300); // Match transition duration
    }
</script>
