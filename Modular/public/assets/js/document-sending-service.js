/**
 * Document Sending Service
 * Reusable JavaScript service for sending documents via email and WhatsApp
 * Can be used across the entire application
 */

class DocumentSendingService {
    constructor() {
        this.apiEndpoint = '/modules/invoice/api/send-document-email.php';
    }
    
    /**
     * Send document by email
     * @param {number} documentId - The document ID to send
     * @param {Object} options - Additional options (custom subject, body, etc.)
     * @returns {Promise<Object>} - API response
     */
    async sendByEmail(documentId, options = {}) {
        return this.sendDocument(documentId, 'email', options);
    }
    
    /**
     * Send document by WhatsApp
     * @param {number} documentId - The document ID to send
     * @param {Object} options - Additional options (custom message, etc.)
     * @returns {Promise<Object>} - API response
     */
    async sendByWhatsApp(documentId, options = {}) {
        return this.sendDocument(documentId, 'whatsapp', options);
    }
    
    /**
     * Send document with specified method
     * @param {number} documentId - The document ID to send
     * @param {string} method - 'email' or 'whatsapp'
     * @param {Object} options - Additional options
     * @returns {Promise<Object>} - API response
     */
    async sendDocument(documentId, method = 'email', options = {}) {
        try {
            // Show loading modal
            if (typeof window.showLoadingModal === 'function') {
                const methodText = method === 'whatsapp' ? 'WhatsApp' : 'Email';
                window.showLoadingModal(`Sending document by ${methodText}...`);
            }
            
            const response = await fetch(this.apiEndpoint, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    document_id: documentId,
                    action: method,
                    options: options
                })
            });

            const result = await response.json();
            
            // Hide loading modal
            if (typeof window.hideLoadingModal === 'function') {
                window.hideLoadingModal();
            }
            
            // Handle response
            if (result.success) {
                if (typeof window.handleApiResponse === 'function') {
                    window.handleApiResponse(result);
                } else if (typeof window.showResponseModal === 'function') {
                    const methodText = method === 'whatsapp' ? 'WhatsApp' : 'Email';
                    window.showResponseModal('success', 'Document Sent', `Document has been sent successfully by ${methodText}.`);
                } else {
                    alert(`Document has been sent successfully by ${method}.`);
                }
                
                // Refresh the current table to show updated status
                if (typeof window.refreshCurrentTable === 'function') {
                    window.refreshCurrentTable();
                }
            } else {
                if (typeof window.handleApiResponse === 'function') {
                    window.handleApiResponse(result);
                } else if (typeof window.showResponseModal === 'function') {
                    window.showResponseModal('error', 'Send Failed', result.message || `Failed to send document by ${method}.`);
                } else {
                    alert(`Failed to send document by ${method}: ` + (result.message || 'Unknown error'));
                }
            }
            
            return result;
            
        } catch (error) {
            // Hide loading modal
            if (typeof window.hideLoadingModal === 'function') {
                window.hideLoadingModal();
            }
            
            console.error(`Error sending document by ${method}:`, error);
            
            if (typeof window.handleApiResponse === 'function') {
                window.handleApiResponse({
                    success: false,
                    message: 'Send Failed',
                    details: `An error occurred while sending the document: ${error.message}`
                });
            } else if (typeof window.showResponseModal === 'function') {
                window.showResponseModal('error', 'Send Failed', `An error occurred while sending the document.`);
            } else {
                alert(`An error occurred while sending the document: ${error.message}`);
            }
            
            return {
                success: false,
                message: 'Network error occurred'
            };
        }
    }
    
    /**
     * Send document with confirmation dialog
     * @param {number} documentId - The document ID to send
     * @param {string} method - 'email' or 'whatsapp'
     * @param {string} documentNumber - Document number for display
     * @param {string} clientName - Client name for display
     * @param {Object} options - Additional options
     * @returns {Promise<Object>} - API response or null if cancelled
     */
    async sendWithConfirmation(documentId, method, documentNumber, clientName, options = {}) {
        const methodText = method === 'whatsapp' ? 'WhatsApp' : 'Email';
        const message = `Are you sure you want to send document ${documentNumber} to ${clientName} by ${methodText}?`;
        
        if (typeof window.showResponseModal === 'function') {
            // Use ResponseModal for confirmation
            const confirmed = await window.showResponseModal(message, 'warning', false, true);
            if (confirmed) {
                return await this.sendDocument(documentId, method, options);
            }
        } else {
            // Fallback to alert if ResponseModal is not available
            if (confirm(message)) {
                return await this.sendDocument(documentId, method, options);
            }
        }
        
        return null; // User cancelled
    }
    
    /**
     * Check if client has required contact information
     * @param {Object} clientData - Client data object
     * @param {string} method - 'email' or 'whatsapp'
     * @returns {Object} - { hasContact: boolean, contactInfo: string, errorMessage: string }
     */
    checkClientContact(clientData, method) {
        if (method === 'email') {
            const email = clientData.client_email || clientData.email;
            if (!email || email.trim() === '') {
                return {
                    hasContact: false,
                    contactInfo: '',
                    errorMessage: 'This client does not have an email address.'
                };
            }
            return {
                hasContact: true,
                contactInfo: email,
                errorMessage: ''
            };
        } else if (method === 'whatsapp') {
            const phone = clientData.client_cell || clientData.client_tell || clientData.phone;
            if (!phone || phone.trim() === '') {
                return {
                    hasContact: false,
                    contactInfo: '',
                    errorMessage: 'This client does not have a phone number.'
                };
            }
            return {
                hasContact: true,
                contactInfo: phone,
                errorMessage: ''
            };
        }
        
        return {
            hasContact: false,
            contactInfo: '',
            errorMessage: 'Invalid method specified.'
        };
    }
    
    /**
     * Generate action buttons for document sending
     * @param {number} documentId - The document ID
     * @param {string} documentNumber - Document number
     * @param {string} clientName - Client name
     * @param {Object} clientData - Client data object
     * @returns {string} - HTML for action buttons
     */
    generateActionButtons(documentId, documentNumber, clientName, clientData) {
        let buttons = '';
        
        // Check email availability
        const emailCheck = this.checkClientContact(clientData, 'email');
        if (emailCheck.hasContact) {
            buttons += `<button class="btn btn-sm btn-primary" onclick="documentSendingService.sendWithConfirmation(${documentId}, 'email', '${documentNumber}', '${clientName}')" title="Send by Email">
                <i class="fas fa-envelope"></i> Email
            </button>`;
        }
        
        // Check phone availability
        const phoneCheck = this.checkClientContact(clientData, 'whatsapp');
        if (phoneCheck.hasContact) {
            buttons += `<button class="btn btn-sm btn-success" onclick="documentSendingService.sendWithConfirmation(${documentId}, 'whatsapp', '${documentNumber}', '${clientName}')" title="Send by WhatsApp">
                <i class="fab fa-whatsapp"></i> WhatsApp
            </button>`;
        }
        
        return buttons;
    }
}

// Create global instance
const documentSendingService = new DocumentSendingService();

// Make it globally available
window.documentSendingService = documentSendingService;
window.DocumentSendingService = DocumentSendingService; 