<div id="modalResponse" class="custom-modal hidden">
    <div class="custom-modal-content">
        <div class="custom-modal-header">
            <span id="modalResponseIcon" class="custom-modal-icon">✔</span>
            <h2 id="modalResponseTitle">Success</h2>
        </div>
        <div class="custom-modal-body">
            <p id="modalResponseMessage">Your request was successful!</p>
        </div>
        <div class="custom-modal-footer">
            <button onclick="closeResponseModal()" class="custom-modal-close-btn">OK</button>
        </div>
    </div>
</div>

<style>
    /* Modal Container */
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
    }
    .custom-modal.hidden {
        display: none !important;
    }
    .custom-modal-content {
        background: var(--color-secondary);
        color: var(--color-text-light);
        padding: var(--spacing-large);
        border-radius: var(--radius-medium);
        box-shadow: var(--shadow-medium);
        text-align: center;
        width: 90%;
        max-width: 400px;
        animation: slide-down 0.3s ease-in-out;
        position: relative;
        z-index: 2147483648;
    }
    .custom-modal-header {
        display: flex;
        align-items: center;
        justify-content: center;
        flex-direction: column;
        margin-bottom: var(--spacing-medium);
    }
    .custom-modal-icon {
        font-size: 3rem;
        font-weight: bold;
    }
    .custom-modal-body p {
        font-size: var(--font-size-large);
        line-height: var(--line-height);
        margin: var(--spacing-small) 0;
    }
    .custom-modal-footer {
        margin-top: var(--spacing-medium);
        display: flex;
        justify-content: center;
        gap: 16px;
    }
    .custom-modal-close-btn {
        background: var(--color-primary);
        color: var(--color-text-dark);
        border: none;
        padding: var(--spacing-small) var(--spacing-medium);
        font-size: var(--font-size-base);
        font-family: var(--font-primary);
        border-radius: var(--radius-small);
        cursor: pointer;
        transition: background var(--transition-speed);
    }
    .custom-modal-close-btn:hover {
        background: var(--color-hover);
    }
    @keyframes slide-down {
        from {
            transform: translateY(-50px);
            opacity: 0;
        }
        to {
            transform: translateY(0);
            opacity: 1;
        }
    }
</style>

<script>
function showResponseModal(message, type = 'info', persistent = false, confirm = false) {
    const modal = document.getElementById('modalResponse');
    const title = document.getElementById('modalResponseTitle');
    const msg = document.getElementById('modalResponseMessage');
    const icon = document.getElementById('modalResponseIcon');
    const footer = modal.querySelector('.custom-modal-footer');
    if (modal && title && msg && icon && footer) {
        // Remove all buttons
        while (footer.firstChild) footer.removeChild(footer.firstChild);
        // Set content
        title.textContent = type === 'success' ? 'Success' : type === 'error' ? 'Error' : type === 'warning' ? 'Warning' : 'Info';
        msg.textContent = message;
        icon.textContent = type === 'success' ? '✔' : type === 'error' ? '✖' : type === 'warning' ? '⚠' : 'ℹ';
        modal.classList.remove('hidden');
        // Confirmation dialog (Yes/No)
        if (confirm) {
            const yesBtn = document.createElement('button');
            yesBtn.textContent = 'Yes';
            yesBtn.className = 'custom-modal-close-btn';
            yesBtn.style.marginRight = '16px';
            const noBtn = document.createElement('button');
            noBtn.textContent = 'No';
            noBtn.className = 'custom-modal-close-btn';
            footer.appendChild(yesBtn);
            footer.appendChild(noBtn);
            yesBtn.onclick = () => {
                modal.classList.add('hidden');
                if (typeof window._modalConfirmResolve === 'function') window._modalConfirmResolve(true);
            };
            noBtn.onclick = () => {
                modal.classList.add('hidden');
                if (typeof window._modalConfirmResolve === 'function') window._modalConfirmResolve(false);
            };
        } else {
            // Auto-close after 1.5s, or close on click outside
            setTimeout(() => { modal.classList.add('hidden'); }, persistent ? 2000 : 1500);
        }
    }
    // Click outside to close
    modal.onclick = function(e) {
        if (e.target === modal && !confirm) {
            modal.classList.add('hidden');
        }
    };
}
function closeResponseModal() {
    document.getElementById('modalResponse').classList.add('hidden');
}
</script>
