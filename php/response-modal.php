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
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.6);
        display: flex;
        align-items: center;
        justify-content: center;
        z-index: 100011;
    }

    /* Modal Content */
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
    }

    /* Header */
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

    /* Body */
    .custom-modal-body p {
        font-size: var(--font-size-large);
        line-height: var(--line-height);
        margin: var(--spacing-small) 0;
    }

    /* Footer */
    .custom-modal-footer {
        margin-top: var(--spacing-medium);
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

    /* Hidden Class */
    .hidden {
        display: none;
    }

    /* Slide Down Animation */
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
function showResponseModal(status, message) {
    const modal = document.getElementById('modalResponse');
    const title = document.getElementById('modalResponseTitle');
    const icon = document.getElementById('modalResponseIcon');
    const msg = document.getElementById('modalResponseMessage');

    msg.innerText = message;

    const statusConfig = {
        success: { title: "Success!", icon: "✔", color: "var(--color-primary)" },
        error: { title: "Error!", icon: "✖", color: "#F44336" },
        warning: { title: "Warning!", icon: "⚠", color: "#FFC107" }
    };

    if (statusConfig[status]) {
        title.innerText = statusConfig[status].title;
        icon.innerHTML = statusConfig[status].icon;
        icon.style.color = statusConfig[status].color;
    } else {
        title.innerText = "Notification";
        icon.innerHTML = "ℹ";
        icon.style.color = "#4A90E2";
    }

    modal.classList.remove('hidden');
}


    function closeResponseModal() {
        document.getElementById('modalResponse').classList.add('hidden');
    }
</script>
