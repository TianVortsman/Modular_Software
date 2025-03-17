// Function to save the clock server port
async function saveClockServerPort() {
    const portInput = document.getElementById('clock_server_port');
    const port = parseInt(portInput.value);
    const accountNumber = document.getElementById('customer-account-number').textContent;
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;

    // Validate port number
    if (isNaN(port) || port < 1024 || port > 65535) {
        showNotification('Please enter a valid port number between 1024 and 65535', 'error');
        return;
    }

    try {
        const response = await fetch('../api/update-clock-server-port.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({
                account_number: accountNumber,
                port: port,
                csrf_token: csrfToken
            })
        });

        const data = await response.json();

        if (data.success) {
            showNotification('Clock server port updated successfully', 'success');
            updateServerStatus(port);
        } else {
            showNotification(data.message || 'Failed to update clock server port', 'error');
        }
    } catch (error) {
        console.error('Error updating clock server port:', error);
        showNotification('Failed to update clock server port', 'error');
    }
}

// Function to load the current port number
async function loadClockServerPort() {
    try {
        const accountNumber = document.getElementById('customer-account-number').textContent;
        const response = await fetch(`../api/get-clock-server-port.php?account_number=${accountNumber}`);
        const data = await response.json();

        if (data.success) {
            const portInput = document.getElementById('clock_server_port');
            portInput.value = data.port || '';
            updateServerStatus(data.port);
        }
    } catch (error) {
        console.error('Error loading clock server port:', error);
    }
}

// Function to update the server status indicator
async function updateServerStatus(port) {
    const indicator = document.getElementById('server-status-indicator');
    const statusText = document.getElementById('server-status-text');

    if (!port) {
        indicator.className = 'status-indicator inactive';
        statusText.textContent = 'No port configured';
        return;
    }

    try {
        const response = await fetch(`../api/check-clock-server-status.php?port=${port}`);
        const data = await response.json();

        if (data.running) {
            indicator.className = 'status-indicator active';
            statusText.textContent = `Clock server running on port ${port}`;
        } else {
            indicator.className = 'status-indicator inactive';
            statusText.textContent = `Clock server not running on port ${port}`;
        }
    } catch (error) {
        indicator.className = 'status-indicator error';
        statusText.textContent = 'Unable to check server status';
    }
}

// Add event listener to load port when customer modal opens
document.addEventListener('DOMContentLoaded', () => {
    const customerModal = document.getElementById('manage-customer-modal');
    if (customerModal) {
        const observer = new MutationObserver((mutations) => {
            mutations.forEach((mutation) => {
                if (mutation.type === 'attributes' && mutation.attributeName === 'style') {
                    if (customerModal.style.display === 'block') {
                        loadClockServerPort();
                    }
                }
            });
        });

        observer.observe(customerModal, { attributes: true });
    }
});

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    const portInput = document.getElementById('clock_server_port');
    if (portInput) {
        portInput.addEventListener('change', saveClockServerPort);
    }
}); 