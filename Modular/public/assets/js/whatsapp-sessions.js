/**
 * WhatsApp Sessions Manager
 * Handles QR code login, session status, and logout for WhatsApp Web
 */
class WhatsAppSessionsManager {
    constructor(customerId, userId = 1) {
        this.customerId = customerId;
        this.userId = userId;
        this.sessionId = this.getSessionId();
        this.baseUrl = '/api/whatsapp-sessions.php';
        this.statusCheckInterval = null;
        this.qrRefreshInterval = null;
        this.isInitialized = false;
        
        // Bind methods
        this.handleStatusUpdate = this.handleStatusUpdate.bind(this);
        this.handleQRUpdate = this.handleQRUpdate.bind(this);
        this.handleClientReady = this.handleClientReady.bind(this);
        this.handleClientDisconnected = this.handleClientDisconnected.bind(this);
    }
    
    /**
     * Initialize the WhatsApp session manager
     */
    async initialize() {
        if (this.isInitialized) return;
        
        // Set up WebSocket event listeners
        this.setupWebSocketListeners();
        
        // Check initial status
        await this.checkStatus();
        
        this.isInitialized = true;
    }
    
    /**
     * Set up WebSocket event listeners for real-time updates
     */
    setupWebSocketListeners() {
        // Listen for WhatsApp Web events from the WebSocket server
        if (window.socket) {
            window.socket.on('whatsapp:qr_generated', this.handleQRUpdate);
            window.socket.on('whatsapp:client_ready', this.handleClientReady);
            window.socket.on('whatsapp:client_authenticated', this.handleStatusUpdate);
            window.socket.on('whatsapp:client_auth_failed', this.handleStatusUpdate);
            window.socket.on('whatsapp:client_disconnected', this.handleClientDisconnected);
            window.socket.on('whatsapp:client_logged_out', this.handleStatusUpdate);
        }
    }
    
    /**
     * Get or generate session ID
     */
    getSessionId() {
        let sessionId = sessionStorage.getItem('whatsapp_session_id');
        if (!sessionId) {
            sessionId = 'session_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            sessionStorage.setItem('whatsapp_session_id', sessionId);
        }
        return sessionId;
    }
    
    /**
     * Check current session status
     */
    async checkStatus() {
        try {
            const response = await fetch(`${this.baseUrl}/${this.customerId}?action=status&user_id=${this.userId}&session_id=${this.sessionId}`);
            const data = await response.json();
            
            if (data.success) {
                this.handleStatusUpdate(data.data);
            } else {
                console.error('Failed to check status:', data.message);
            }
        } catch (error) {
            console.error('Error checking WhatsApp status:', error);
        }
    }
    
    /**
     * Get QR code for login
     */
    async getQRCode() {
        try {
            const response = await fetch(`${this.baseUrl}/${this.customerId}?action=qr&user_id=${this.userId}&session_id=${this.sessionId}`);
            const data = await response.json();
            
            if (data.success && data.data.qr_code) {
                this.handleQRUpdate({
                    customer_id: this.customerId,
                    qr: data.data.qr_code,
                    timestamp: Date.now()
                });
                
                // Start QR refresh interval
                this.startQRRefresh();
                
                return data.data.qr_code;
            } else {
                console.error('Failed to get QR code:', data.message);
                return null;
            }
        } catch (error) {
            console.error('Error getting QR code:', error);
            return null;
        }
    }
    
    /**
     * Initialize WhatsApp session (generate QR code)
     */
    async initializeSession() {
        try {
            const response = await fetch(`${this.baseUrl}/${this.customerId}`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=initialize&user_id=${this.userId}&session_id=${this.sessionId}`
            });
            
            const data = await response.json();
            
            if (data.success) {
                return data.data;
            } else {
                console.error('Failed to initialize session:', data.message);
                return null;
            }
        } catch (error) {
            console.error('Error initializing session:', error);
            return null;
        }
    }
    
    /**
     * Logout from WhatsApp session
     */
    async logout() {
        try {
            const response = await fetch(`${this.baseUrl}/${this.customerId}?action=logout&user_id=${this.userId}&session_id=${this.sessionId}`, {
                method: 'DELETE'
            });
            
            const data = await response.json();
            
            if (data.success) {
                this.handleStatusUpdate({
                    customer_id: this.customerId,
                    status: 'logged_out',
                    timestamp: Date.now()
                });
                
                // Stop status checking
                this.stopStatusCheck();
                this.stopQRRefresh();
                
                return true;
            } else {
                console.error('Failed to logout:', data.message);
                return false;
            }
        } catch (error) {
            console.error('Error logging out:', error);
            return false;
        }
    }
    
    /**
     * Start periodic status checking
     */
    startStatusCheck(interval = 5000) {
        this.stopStatusCheck();
        this.statusCheckInterval = setInterval(() => {
            this.checkStatus();
        }, interval);
    }
    
    /**
     * Stop periodic status checking
     */
    stopStatusCheck() {
        if (this.statusCheckInterval) {
            clearInterval(this.statusCheckInterval);
            this.statusCheckInterval = null;
        }
    }
    
    /**
     * Start QR code refresh interval
     */
    startQRRefresh(interval = 60000) { // Refresh QR every minute
        this.stopQRRefresh();
        this.qrRefreshInterval = setInterval(() => {
            this.getQRCode();
        }, interval);
    }
    
    /**
     * Stop QR code refresh interval
     */
    stopQRRefresh() {
        if (this.qrRefreshInterval) {
            clearInterval(this.qrRefreshInterval);
            this.qrRefreshInterval = null;
        }
    }
    
    /**
     * Handle status updates
     */
    handleStatusUpdate(data) {
        const status = data.status || 'unknown';
        
        // Emit custom event for status change
        const event = new CustomEvent('whatsapp:status_changed', {
            detail: {
                customer_id: this.customerId,
                status: status,
                data: data
            }
        });
        document.dispatchEvent(event);
        
        // Update UI based on status
        this.updateStatusUI(status, data);
        
        // Handle specific statuses
        switch (status) {
            case 'ready':
                this.stopQRRefresh();
                this.startStatusCheck(10000); // Check every 10 seconds when ready
                break;
            case 'qr_ready':
                this.startStatusCheck(3000); // Check every 3 seconds when QR is ready
                break;
            case 'disconnected':
            case 'auth_failed':
            case 'logged_out':
                this.stopStatusCheck();
                this.stopQRRefresh();
                break;
        }
    }
    
    /**
     * Handle QR code updates
     */
    handleQRUpdate(data) {
        // Emit custom event for QR update
        const event = new CustomEvent('whatsapp:qr_updated', {
            detail: {
                customer_id: this.customerId,
                qr_code: data.qr,
                timestamp: data.timestamp
            }
        });
        document.dispatchEvent(event);
        
        // Update QR code in UI
        this.updateQRCodeUI(data.qr);
    }
    
    /**
     * Handle client ready event
     */
    handleClientReady(data) {
        this.handleStatusUpdate({
            customer_id: this.customerId,
            status: 'ready',
            timestamp: data.timestamp
        });
    }
    
    /**
     * Handle client disconnected event
     */
    handleClientDisconnected(data) {
        this.handleStatusUpdate({
            customer_id: this.customerId,
            status: 'disconnected',
            reason: data.reason,
            timestamp: data.timestamp
        });
    }
    
    /**
     * Update status UI
     */
    updateStatusUI(status, data) {
        const statusElement = document.getElementById('whatsapp-status');
        if (!statusElement) return;
        
        const statusText = {
            'not_initialized': 'Not Initialized',
            'qr_ready': 'QR Code Ready',
            'authenticated': 'Authenticated',
            'ready': 'Ready',
            'disconnected': 'Disconnected',
            'auth_failed': 'Authentication Failed',
            'logged_out': 'Logged Out'
        };
        
        statusElement.textContent = statusText[status] || status;
        statusElement.className = `whatsapp-status whatsapp-status-${status}`;
        
        // Update last updated time
        const lastUpdatedElement = document.getElementById('whatsapp-last-updated');
        if (lastUpdatedElement && data.last_updated) {
            lastUpdatedElement.textContent = new Date(data.last_updated).toLocaleString();
        }
    }
    
    /**
     * Update QR code UI
     */
    updateQRCodeUI(qrCodeDataUrl) {
        const qrElement = document.getElementById('whatsapp-qr-code');
        if (!qrElement) return;
        
        qrElement.src = qrCodeDataUrl;
        qrElement.style.display = 'block';
        
        // Show QR container
        const qrContainer = document.getElementById('whatsapp-qr-container');
        if (qrContainer) {
            qrContainer.style.display = 'block';
        }
    }
    
    /**
     * Show QR code container
     */
    showQRCode() {
        const qrContainer = document.getElementById('whatsapp-qr-container');
        if (qrContainer) {
            qrContainer.style.display = 'block';
        }
    }
    
    /**
     * Hide QR code container
     */
    hideQRCode() {
        const qrContainer = document.getElementById('whatsapp-qr-container');
        if (qrContainer) {
            qrContainer.style.display = 'none';
        }
        
        const qrElement = document.getElementById('whatsapp-qr-code');
        if (qrElement) {
            qrElement.style.display = 'none';
        }
    }
    
    /**
     * Cleanup resources
     */
    destroy() {
        this.stopStatusCheck();
        this.stopQRRefresh();
        this.isInitialized = false;
    }
}

// Global instance
window.whatsappSessionsManager = null;

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    // Get customer ID from page or global variable
    const customerId = window.CUSTOMER_ID || document.body.dataset.customerId;
    
    if (customerId) {
        window.whatsappSessionsManager = new WhatsAppSessionsManager(customerId);
        window.whatsappSessionsManager.initialize();
    }
});

// Export for module usage
if (typeof module !== 'undefined' && module.exports) {
    module.exports = WhatsAppSessionsManager;
} 