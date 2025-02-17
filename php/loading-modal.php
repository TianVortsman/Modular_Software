<!-- Loading Modal -->
<div id="unique-loading-modal" class="hidden">
    <div class="unique-modal-content">
        <div class="unique-spinner"></div>
        <p class="modal-message">Loading...</p>
    </div>
</div>

<style>
    /* Unique modal styles */
    #unique-loading-modal {
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
        transition: opacity var(--transition-speed) ease-in-out;
    }

    /* Hide modal by default */
    .hidden {
        display: none;
    }

    /* Modal content box */
    .unique-modal-content {
        background-color: var(--modal-bg);  /* Dark background */
        padding: var(--modal-padding);
        border-radius: var(--radius-medium);
        box-shadow: 0px 4px 8px var(--modal-shadow);
        text-align: center;
        color: var(--color-text-light);
        max-width: 300px;
        width: 100%;
    }

    /* Spinner animation */
    .unique-spinner {
        border: 4px solid var(--color-secondary);  /* Light gray border */
        border-top: 4px solid var(--color-primary);  /* Primary color */
        border-radius: 50%;
        width: 50px;
        height: 50px;
        animation: spin 1s linear infinite;
    }

    /* Spinner animation */
    @keyframes spin {
        0% { transform: rotate(0deg); }
        100% { transform: rotate(360deg); }
    }

    /* Message styling */
    .modal-message {
        margin-top: var(--spacing-small);
        font-size: var(--font-size-base);
        color: var(--color-text-light);
    }
</style>

<script>
    // This function shows the loading modal
    function showLoadingModal(message = "Loading...") {
        const modal = document.getElementById('unique-loading-modal');
        const messageElement = modal.querySelector('.modal-message');
        messageElement.textContent = message;
        modal.classList.remove('hidden');
        modal.style.opacity = 1;
    }

    // This function hides the loading modal
    function hideLoadingModal() {
        const modal = document.getElementById('unique-loading-modal');
        modal.classList.add('hidden');
        modal.style.opacity = 0;
    }
</script>
