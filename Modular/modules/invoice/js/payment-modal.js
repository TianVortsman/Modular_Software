/**
 * Payment Modal JavaScript
 * Handles all payment-related functionality including recording payments,
 * payment history, validation, and UI interactions
 */

class PaymentModal {
    constructor() {
        this.currentDocument = null;
        this.currentPayment = null;
        this.paymentHistory = [];
        this.isLoading = false;
        this.autoSaveTimer = null;
        
        this.initializeEventListeners();
        this.initializeFormatters();
    }

    initializeEventListeners() {
        // Modal open/close
        document.getElementById('modal-payment-close-btn')?.addEventListener('click', () => this.closeModal());
        document.getElementById('modal-payment-history-close-btn')?.addEventListener('click', () => this.closeHistoryModal());
        document.getElementById('close-confirmation-btn')?.addEventListener('click', () => this.closeConfirmationModal());

        // Payment form inputs
        document.getElementById('payment-amount')?.addEventListener('input', (e) => this.handleAmountChange(e));
        document.getElementById('payment-date')?.addEventListener('change', () => this.updateSummary());
        document.getElementById('payment-method')?.addEventListener('change', () => this.updateSummary());
        document.getElementById('payment-allocation-type')?.addEventListener('change', () => this.handleAllocationChange());
        document.getElementById('payment-reference')?.addEventListener('input', () => this.updatePreview());
        document.getElementById('payment-notes')?.addEventListener('input', () => this.updatePreview());
        
        // Client search input
        document.getElementById('payment-client-search')?.addEventListener('input', (e) => this.searchClient(e.target));

        // Action buttons
        document.getElementById('clear-payment-btn')?.addEventListener('click', () => this.clearForm());
        document.getElementById('save-payment-draft-btn')?.addEventListener('click', () => this.saveDraft());
        document.getElementById('record-payment-btn')?.addEventListener('click', () => this.recordPayment());

        // Payment history
        document.getElementById('refresh-payment-history-btn')?.addEventListener('click', () => this.loadPaymentHistory());
        document.querySelector('[title="Payment History"]')?.addEventListener('click', () => this.openHistoryModal());

        // History modal filters
        document.getElementById('apply-history-filters-btn')?.addEventListener('click', () => this.applyHistoryFilters());
        document.getElementById('clear-history-filters-btn')?.addEventListener('click', () => this.clearHistoryFilters());

        // Confirmation modal actions
        document.getElementById('print-payment-receipt-btn')?.addEventListener('click', () => this.printReceipt());
        document.getElementById('email-payment-receipt-btn')?.addEventListener('click', () => this.emailReceipt());

        // Preview toggle
        document.getElementById('toggle-payment-preview')?.addEventListener('click', () => this.togglePreview());

        // Keyboard shortcuts
        document.addEventListener('keydown', (e) => this.handleKeyboardShortcuts(e));

        // Auto-save on form changes
        this.setupAutoSave();
    }

    initializeFormatters() {
        // Currency formatter
        this.currencyFormatter = new Intl.NumberFormat('en-ZA', {
            style: 'currency',
            currency: 'ZAR',
            minimumFractionDigits: 2
        });

        // Date formatter
        this.dateFormatter = new Intl.DateTimeFormat('en-ZA', {
            year: 'numeric',
            month: '2-digit',
            day: '2-digit'
        });
    }

    setupAutoSave() {
        const formInputs = [
            'payment-amount',
            'payment-date',
            'payment-method',
            'payment-reference',
            'payment-notes',
            'payment-allocation-type'
            // Note: payment-client-search is not included to avoid auto-save during search
        ];

        formInputs.forEach(inputId => {
            const input = document.getElementById(inputId);
            if (input) {
                input.addEventListener('input', () => this.scheduleAutoSave());
                input.addEventListener('change', () => this.scheduleAutoSave());
            }
        });
    }

    scheduleAutoSave() {
        if (this.autoSaveTimer) {
            clearTimeout(this.autoSaveTimer);
        }
        
        this.autoSaveTimer = setTimeout(() => {
            this.saveDraft(true); // Silent auto-save
        }, 2000);
    }

    async openModal(mode = 'create', documentId = null) {
        try {
            this.showLoadingModal();
            
            // Set modal mode
            this.currentMode = mode;
            
            if (documentId) {
                await this.loadDocumentForPayment(documentId);
            } else {
                this.resetForm();
                // Load available invoices for linking
                await this.loadAvailableInvoices();
            }
            
            document.getElementById('payment-modal').style.display = 'flex';
            this.hideLoadingModal();
            
            // Focus on first input
            setTimeout(() => {
                if (documentId) {
                    document.getElementById('payment-amount')?.focus();
                } else {
                    document.getElementById('payment-client-search')?.focus();
                }
            }, 100);
            
        } catch (error) {
            this.hideLoadingModal();
            this.showError('Failed to open payment modal: ' + error.message);
        }
    }

    closeModal() {
        document.getElementById('payment-modal').style.display = 'none';
        this.resetForm();
        this.clearAutoSave();
    }

    async loadDocumentForPayment(documentId) {
        try {
            const response = await fetch(`../api/payment-api.php?action=get_document_for_payment&document_id=${documentId}`);
            const result = await handleApiResponse(response);
            
            if (result.success) {
                this.currentDocument = result.data;
                this.populateDocumentInfo();
                this.populateClientInfo();
                await this.loadPaymentHistory();
                this.updateSummary();
                this.updatePreview();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            throw new Error('Failed to load document: ' + error.message);
        }
    }

    async loadAvailableInvoices() {
        try {
            // Use buildQueryParams for consistent parameter handling
            const paramsObj = { 
                action: 'list_documents', 
                type: 'invoice', 
                status: 'sent' 
            };
            const params = window.buildQueryParams(paramsObj);
            const url = `../api/document-api.php?${params.toString()}`;
            
            console.log('Loading available invoices with URL:', url);
            
            const response = await fetch(url);
            const result = await handleApiResponse(response);
            
            if (result.success) {
                this.populateInvoiceSelect(result.data);
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Failed to load available invoices:', error);
            // Don't throw error, just show empty select
            this.populateInvoiceSelect([]);
        }
    }

    populateInvoiceSelect(invoices) {
        const select = document.getElementById('invoice-select');
        if (!select) return;

        // Clear existing options
        select.innerHTML = '<option value="">Select an invoice to link payment to (optional)</option>';
        
        if (invoices.length === 0) {
            select.innerHTML += '<option value="" disabled>No unpaid invoices available</option>';
            return;
        }

        // Add invoice options
        invoices.forEach(invoice => {
            const option = document.createElement('option');
            option.value = invoice.document_id;
            option.textContent = `${invoice.document_number} - ${invoice.client_name} (R${parseFloat(invoice.balance_due || 0).toFixed(2)} due)`;
            select.appendChild(option);
        });

        // Add event listener for invoice selection
        select.addEventListener('change', (e) => {
            const selectedInvoiceId = e.target.value;
            if (selectedInvoiceId) {
                this.loadDocumentForPayment(selectedInvoiceId);
            } else {
                this.resetForm();
            }
        });
    }

    populateDocumentInfo() {
        if (!this.currentDocument) return;

        const doc = this.currentDocument;
        
        // Set hidden fields
        document.getElementById('payment-document-id').value = doc.document_id;
        document.getElementById('payment-client-id').value = doc.client_id;
        
        // Populate document info
        document.getElementById('payment-document-number').value = doc.document_number;
        document.getElementById('payment-document-type').value = this.formatDocumentType(doc.document_type);
        document.getElementById('payment-document-date').value = doc.issue_date;
        
        // Update summary
        document.getElementById('document-total').textContent = this.currencyFormatter.format(doc.total_amount);
        document.getElementById('previously-paid').textContent = this.currencyFormatter.format(doc.total_paid || 0);
        document.getElementById('balance-due').textContent = this.currencyFormatter.format(doc.balance_due);
        
        // Set default payment date to today
        document.getElementById('payment-date').value = new Date().toISOString().split('T')[0];
        
        // Show document info section and hide client search and invoice selection
        document.getElementById('document-info-section').style.display = 'block';
        document.getElementById('client-search-section').style.display = 'none';
        document.getElementById('invoice-selection-section').style.display = 'none';
    }

    populateClientInfo() {
        if (!this.currentDocument) return;

        const client = this.currentDocument;
        
        document.getElementById('payment-client-name').value = client.client_name;
        document.getElementById('payment-client-email').value = client.client_email;
        document.getElementById('payment-client-phone').value = client.client_cell || client.client_tell;
        document.getElementById('payment-client-vat-number').value = client.vat_number;
    }

    formatDocumentType(type) {
        const typeMap = {
            'invoice': 'Invoice',
            'vehicle_invoice': 'Vehicle Invoice',
            'recurring_invoice': 'Recurring Invoice',
            'quotation': 'Quotation',
            'pro_forma': 'Pro Forma',
            'credit_note': 'Credit Note',
            'refund': 'Refund'
        };
        
        return typeMap[type] || type;
    }

    handleAmountChange(event) {
        const input = event.target;
        let value = input.value.replace(/[^\d.]/g, '');
        
        // Ensure only one decimal point
        const parts = value.split('.');
        if (parts.length > 2) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }
        
        // Limit to 2 decimal places
        if (parts.length === 2 && parts[1].length > 2) {
            value = parts[0] + '.' + parts[1].substring(0, 2);
        }
        
        input.value = value;
        this.updateSummary();
        this.updatePreview();
        this.validatePayment();
    }

    handleAllocationChange() {
        const allocationType = document.getElementById('payment-allocation-type').value;
        const amountInput = document.getElementById('payment-amount');
        
        if (allocationType === 'full' && this.currentDocument) {
            amountInput.value = this.currentDocument.balance_due.toFixed(2);
            this.handleAmountChange({ target: amountInput });
        }
        
        this.updateSummary();
        this.validatePayment();
    }

    updateSummary() {
        if (!this.currentDocument) return;

        const paymentAmount = parseFloat(document.getElementById('payment-amount').value) || 0;
        const balanceDue = this.currentDocument.balance_due;
        const remainingBalance = Math.max(0, balanceDue - paymentAmount);
        
        document.getElementById('payment-total').textContent = this.currencyFormatter.format(paymentAmount);
        document.getElementById('remaining-balance').textContent = this.currencyFormatter.format(remainingBalance);
    }

    async validatePayment() {
        if (!this.currentDocument) return;

        const paymentAmount = parseFloat(document.getElementById('payment-amount').value) || 0;
        const allocationType = document.getElementById('payment-allocation-type').value;
        
        if (paymentAmount <= 0) return;

        try {
            const response = await fetch('../api/payment-api.php?action=validate_payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    document_id: this.currentDocument.document_id,
                    payment_amount: paymentAmount,
                    allocation_type: allocationType
                })
            });

            const result = await handleApiResponse(response);
            
            if (result.success) {
                this.displayValidation(result.data);
            }
        } catch (error) {
            console.error('Payment validation error:', error);
        }
    }

    displayValidation(validation) {
        const container = document.getElementById('payment-validation-messages');
        container.innerHTML = '';

        // Display errors
        validation.errors.forEach(error => {
            const errorDiv = document.createElement('div');
            errorDiv.className = 'validation-error';
            errorDiv.innerHTML = `<i data-lucide="alert-circle"></i> ${error}`;
            container.appendChild(errorDiv);
        });

        // Display warnings
        validation.warnings.forEach(warning => {
            const warningDiv = document.createElement('div');
            warningDiv.className = 'validation-warning';
            warningDiv.innerHTML = `<i data-lucide="alert-triangle"></i> ${warning}`;
            container.appendChild(warningDiv);
        });

        // Update record button state
        const recordBtn = document.getElementById('record-payment-btn');
        if (recordBtn) {
            recordBtn.disabled = !validation.is_valid;
        }
    }

    async loadPaymentHistory() {
        if (!this.currentDocument) return;

        try {
            const response = await fetch(`../api/payment-api.php?action=get_payment_history&document_id=${this.currentDocument.document_id}`);
            const result = await handleApiResponse(response);
            
            if (result.success) {
                this.paymentHistory = result.data;
                this.renderPaymentHistory();
            }
        } catch (error) {
            console.error('Failed to load payment history:', error);
        }
    }

    renderPaymentHistory() {
        const tbody = document.getElementById('payment-history-rows');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (this.paymentHistory.length === 0) {
            tbody.innerHTML = '<tr><td colspan="6" class="no-data">No payment history found</td></tr>';
            return;
        }

        this.paymentHistory.forEach(payment => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.dateFormatter.format(new Date(payment.payment_date))}</td>
                <td>${this.currencyFormatter.format(payment.payment_amount)}</td>
                <td>${payment.method_name || 'N/A'}</td>
                <td>${payment.payment_reference || 'N/A'}</td>
                <td><span class="status-badge status-${payment.payment_type}">${payment.payment_type}</span></td>
                <td>
                    <button class="btn-icon-only" onclick="paymentModal.viewPayment(${payment.document_payment_id})" title="View Details">
                        <i data-lucide="eye"></i>
                    </button>
                    <button class="btn-icon-only" onclick="paymentModal.deletePayment(${payment.document_payment_id})" title="Delete Payment">
                        <i data-lucide="trash-2"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });

        // Initialize Lucide icons
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    async recordPayment() {
        const formData = this.getFormData();
        
        // Validate required fields
        const requiredFields = ['payment_amount', 'payment_date', 'payment_method'];
        for (const field of requiredFields) {
            if (!formData[field]) {
                this.showError(`Please fill in the ${field.replace('_', ' ')} field`);
                return;
            }
        }

        if (parseFloat(formData.payment_amount) <= 0) {
            this.showError('Payment amount must be greater than zero');
            return;
        }

        // If no document is selected, this is a standalone payment
        if (!this.currentDocument) {
            if (!confirm('No invoice is linked to this payment. This will create a standalone payment record. Continue?')) {
                return;
            }
            // Remove document_id from form data for standalone payment
            delete formData.document_id;
        }

        try {
            this.showLoadingModal();
            
            const response = await fetch('../api/payment-api.php?action=record_payment', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            });

            const result = await handleApiResponse(response);
            
            if (result.success) {
                this.currentPayment = result.data;
                this.showConfirmationModal();
                if (this.currentDocument) {
                    await this.loadPaymentHistory();
                    this.updateSummary();
                }
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            this.showError('Failed to record payment: ' + error.message);
        } finally {
            this.hideLoadingModal();
        }
    }

    getFormData() {
        return {
            document_id: this.currentDocument?.document_id,
            payment_amount: document.getElementById('payment-amount').value,
            payment_date: document.getElementById('payment-date').value,
            payment_method: document.getElementById('payment-method').value,
            payment_reference: document.getElementById('payment-reference').value,
            payment_notes: document.getElementById('payment-notes').value,
            allocation_type: document.getElementById('payment-allocation-type').value
        };
    }

    showConfirmationModal() {
        if (!this.currentPayment) return;

        const payment = this.currentPayment;
        const document = payment.document;
        
        document.getElementById('confirmation-details').innerHTML = `
            <strong>Payment Amount:</strong> ${this.currencyFormatter.format(payment.payment_amount)}<br>
            <strong>Document:</strong> ${document.document_number}<br>
            <strong>New Balance:</strong> ${this.currencyFormatter.format(payment.new_balance)}<br>
            <strong>Status:</strong> ${document.document_status}
        `;
        
        document.getElementById('payment-confirmation-modal').style.display = 'flex';
    }

    closeConfirmationModal() {
        document.getElementById('payment-confirmation-modal').style.display = 'none';
        this.closeModal();
    }

    async saveDraft(silent = false) {
        // For now, we'll just save the form data to localStorage
        // In a full implementation, this would save to the database
        const formData = this.getFormData();
        localStorage.setItem('payment_draft', JSON.stringify(formData));
        
        if (!silent) {
            this.showSuccess('Payment draft saved');
        }
    }

    clearForm() {
        if (confirm('Are you sure you want to clear the payment form?')) {
            this.resetForm();
        }
    }

    resetForm() {
        // Reset form fields
        document.getElementById('payment-amount').value = '';
        document.getElementById('payment-date').value = new Date().toISOString().split('T')[0];
        document.getElementById('payment-method').value = '';
        document.getElementById('payment-reference').value = '';
        document.getElementById('payment-notes').value = '';
        document.getElementById('payment-allocation-type').value = 'full';
        
        // Reset client search
        document.getElementById('payment-client-search').value = '';
        document.getElementById('search-results-client').innerHTML = '';
        document.getElementById('search-results-client').style.display = 'none';
        
        // Reset summary
        document.getElementById('payment-total').textContent = this.currencyFormatter.format(0);
        document.getElementById('remaining-balance').textContent = this.currencyFormatter.format(0);
        
        // Clear validation
        document.getElementById('payment-validation-messages').innerHTML = '';
        
        // Reset current document
        this.currentDocument = null;
        this.currentPayment = null;
        
        // Clear payment history
        document.getElementById('payment-history-rows').innerHTML = '';
        
        // Show client search and invoice selection sections, hide document info
        document.getElementById('client-search-section').style.display = 'block';
        document.getElementById('invoice-selection-section').style.display = 'block';
        document.getElementById('document-info-section').style.display = 'none';
        
        // Update preview
        this.updatePreview();
    }

    clearAutoSave() {
        if (this.autoSaveTimer) {
            clearTimeout(this.autoSaveTimer);
            this.autoSaveTimer = null;
        }
    }

    updatePreview() {
        if (!this.currentDocument) return;

        const doc = this.currentDocument;
        const paymentAmount = document.getElementById('payment-amount').value;
        const paymentMethod = document.getElementById('payment-method');
        const paymentReference = document.getElementById('payment-reference').value;
        
        // Update preview content
        document.getElementById('preview-payment-document-number').textContent = doc.document_number;
        document.getElementById('preview-payment-document-date').textContent = doc.issue_date;
        document.getElementById('preview-payment-client-name').textContent = doc.client_name;
        document.getElementById('preview-payment-amount').textContent = this.currencyFormatter.format(paymentAmount || 0);
        document.getElementById('preview-payment-method').textContent = paymentMethod.options[paymentMethod.selectedIndex]?.text || 'N/A';
        document.getElementById('preview-payment-reference').textContent = paymentReference || 'N/A';
    }

    togglePreview() {
        const preview = document.getElementById('payment-preview-modal');
        const toggleBtn = document.getElementById('toggle-payment-preview');
        const icon = toggleBtn.querySelector('i');
        
        if (preview.style.display === 'none' || !preview.style.display) {
            preview.style.display = 'block';
            icon.setAttribute('data-lucide', 'eye');
            toggleBtn.title = 'Hide Preview';
        } else {
            preview.style.display = 'none';
            icon.setAttribute('data-lucide', 'eye-off');
            toggleBtn.title = 'Show Preview';
        }
        
        if (window.lucide) {
            lucide.createIcons();
        }
    }

    openHistoryModal() {
        document.getElementById('payment-history-modal').style.display = 'flex';
        this.loadFullPaymentHistory();
    }

    closeHistoryModal() {
        document.getElementById('payment-history-modal').style.display = 'none';
    }

    async loadFullPaymentHistory() {
        try {
            const response = await fetch('../api/payment-api.php?action=get_payment_history');
            const result = await handleApiResponse(response);
            
            if (result.success) {
                this.renderFullPaymentHistory(result.data);
            }
        } catch (error) {
            this.showError('Failed to load payment history: ' + error.message);
        }
    }

    renderFullPaymentHistory(payments) {
        const tbody = document.getElementById('full-payment-history-rows');
        if (!tbody) return;

        tbody.innerHTML = '';

        if (payments.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="no-data">No payment history found</td></tr>';
            return;
        }

        payments.forEach(payment => {
            const row = document.createElement('tr');
            row.innerHTML = `
                <td>${this.dateFormatter.format(new Date(payment.payment_date))}</td>
                <td>${payment.document_number}</td>
                <td>${payment.client_name}</td>
                <td>${this.currencyFormatter.format(payment.payment_amount)}</td>
                <td>${payment.method_name || 'N/A'}</td>
                <td>${payment.payment_reference || 'N/A'}</td>
                <td><span class="status-badge status-${payment.payment_type}">${payment.payment_type}</span></td>
                <td>
                    <button class="btn-icon-only" onclick="paymentModal.viewPayment(${payment.document_payment_id})" title="View Details">
                        <i data-lucide="eye"></i>
                    </button>
                </td>
            `;
            tbody.appendChild(row);
        });

        if (window.lucide) {
            lucide.createIcons();
        }
    }

    applyHistoryFilters() {
        const dateFrom = document.getElementById('history-date-from').value;
        const dateTo = document.getElementById('history-date-to').value;
        const paymentMethod = document.getElementById('history-payment-method').value;
        const status = document.getElementById('history-status').value;
        
        const params = new URLSearchParams();
        if (dateFrom) params.append('date_from', dateFrom);
        if (dateTo) params.append('date_to', dateTo);
        if (paymentMethod) params.append('payment_method', paymentMethod);
        if (status) params.append('status', status);
        
        this.loadFullPaymentHistory(params.toString());
    }

    clearHistoryFilters() {
        document.getElementById('history-date-from').value = '';
        document.getElementById('history-date-to').value = '';
        document.getElementById('history-payment-method').value = '';
        document.getElementById('history-status').value = '';
        
        this.loadFullPaymentHistory();
    }

    async deletePayment(paymentId) {
        if (!confirm('Are you sure you want to delete this payment?')) {
            return;
        }

        try {
            const response = await fetch(`../api/payment-api.php?action=delete_payment&payment_id=${paymentId}`, {
                method: 'DELETE'
            });

            const result = await handleApiResponse(response);
            
            if (result.success) {
                this.showSuccess('Payment deleted successfully');
                await this.loadPaymentHistory();
                this.updateSummary();
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            this.showError('Failed to delete payment: ' + error.message);
        }
    }

    viewPayment(paymentId) {
        // Implementation for viewing payment details
        console.log('View payment:', paymentId);
    }

    printReceipt() {
        // Implementation for printing receipt
        console.log('Print receipt');
        window.print();
    }

    emailReceipt() {
        // Implementation for emailing receipt
        console.log('Email receipt');
    }

    handleKeyboardShortcuts(event) {
        // Ctrl/Cmd + S to save draft
        if ((event.ctrlKey || event.metaKey) && event.key === 's') {
            event.preventDefault();
            this.saveDraft();
        }
        
        // Ctrl/Cmd + Enter to record payment
        if ((event.ctrlKey || event.metaKey) && event.key === 'Enter') {
            event.preventDefault();
            this.recordPayment();
        }
        
        // Escape to close modal
        if (event.key === 'Escape') {
            this.closeModal();
        }
    }

    showLoadingModal() {
        this.isLoading = true;
        // Implementation depends on your loading modal
        if (window.showLoadingModal) {
            window.showLoadingModal();
        }
    }

    hideLoadingModal() {
        this.isLoading = false;
        // Implementation depends on your loading modal
        if (window.hideLoadingModal) {
            window.hideLoadingModal();
        }
    }

    showError(message) {
        // Implementation depends on your error handling
        if (window.showResponseModal) {
            window.showResponseModal('error', 'Error', message);
        } else {
            alert('Error: ' + message);
        }
    }

    showSuccess(message) {
        // Implementation depends on your success handling
        if (window.showResponseModal) {
            window.showResponseModal('success', 'Success', message);
        } else {
            alert('Success: ' + message);
        }
    }

    // Client search functionality (reused from document modal)
    searchClient(inputElement) {
        console.log('PaymentModal.searchClient called with:', inputElement);
        console.log('Input value:', inputElement.value);
        
        const query = inputElement.value.trim();
        const resultsContainer = document.getElementById('search-results-client');
        const dropdown = document.getElementById('search-results-client');
        
        console.log('Query:', query);
        console.log('Results container:', resultsContainer);
        console.log('Dropdown:', dropdown);
        
        // Close dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!inputElement.contains(e.target) && !dropdown.contains(e.target)) {
                dropdown.style.display = 'none';
                dropdown.classList.remove('active');
            }
        });
        
        if (query.length < 2) {
            console.log('Query too short, hiding dropdown');
            dropdown.style.display = 'none';
            dropdown.classList.remove('active');
            resultsContainer.innerHTML = '';
            return;
        }
        
        console.log('Calling window.searchClients with query:', query);
        
        // Use the existing searchClients function from document-api.js
        if (window.searchClients) {
            console.log('window.searchClients is available');
            window.searchClients(query, (results) => {
                console.log('searchClients callback received results:', results);
                resultsContainer.innerHTML = '';
                if (results && results.length > 0) {
                    console.log('Showing results, count:', results.length);
                    dropdown.style.display = 'block';
                    dropdown.classList.add('active');
                    console.log('Dropdown display style:', dropdown.style.display);
                    console.log('Dropdown classes:', dropdown.className);
                    console.log('Dropdown position:', dropdown.getBoundingClientRect());
                    results.forEach((item, idx) => {
                        console.log('Processing result item:', item);
                        const div = document.createElement('div');
                        div.classList.add('search-result-client');
                        
                        // Create better display text based on client type
                        let displayText = '';
                        let subtitleText = '';
                        
                        if (item.client_type === 'business') {
                            displayText = item.client_name;
                            subtitleText = 'Business Client';
                        } else {
                            // Private client
                            displayText = item.client_name;
                            if (item.first_name && item.last_name) {
                                subtitleText = `${item.first_name} ${item.last_name}`;
                            }
                        }
                        
                        // Add email if available
                        if (item.client_email) {
                            subtitleText += subtitleText ? ` • ${item.client_email}` : item.client_email;
                        }
                        
                        // Create HTML structure
                        div.innerHTML = `
                            <div class="client-result-name">${displayText}</div>
                            ${subtitleText ? `<div class="client-result-details">${subtitleText}</div>` : ''}
                        `;
                        
                        div.onclick = function (e) {
                            console.log('Client result clicked:', item);
                            this.fillClientFields(item);
                            resultsContainer.innerHTML = '';
                            dropdown.style.display = 'none';
                            dropdown.classList.remove('active');
                        }.bind(this);
                        
                        if (idx === 0) div.classList.add('highlight');
                        resultsContainer.appendChild(div);
                    });
                } else {
                    console.log('No results found, showing no results message');
                    dropdown.style.display = 'block';
                    dropdown.classList.add('active');
                    const noResults = document.createElement('div');
                    noResults.classList.add('search-no-results');
                    noResults.textContent = 'No clients found';
                    resultsContainer.appendChild(noResults);
                }
            });
        } else {
            console.error('window.searchClients is not available');
        }
    }

    fillClientFields(item) {
        const set = (id, value) => {
            const el = document.getElementById(id);
            if (el) el.value = value || '';
        };
        
        // Set client fields in the client panel
        set('payment-client-name', item.client_name);
        set('payment-client-email', item.client_email);
        set('payment-client-phone', item.client_cell || item.client_tell);
        set('payment-client-vat-number', item.vat_number);
        
        // Set hidden client ID
        set('payment-client-id', item.client_id);
        
        // Load available invoices for this client
        this.loadAvailableInvoicesForClient(item.client_id);
    }

    async loadAvailableInvoicesForClient(clientId) {
        try {
            // Use buildQueryParams for consistent parameter handling
            const paramsObj = { 
                action: 'list_documents', 
                type: 'invoice', 
                status: 'sent',
                client_id: clientId
            };
            const params = window.buildQueryParams(paramsObj);
            const url = `../api/document-api.php?${params.toString()}`;
            
            console.log('Loading available invoices for client with URL:', url);
            
            const response = await fetch(url);
            const result = await handleApiResponse(response);
            
            if (result.success) {
                this.populateInvoiceSelect(result.data);
            } else {
                throw new Error(result.message);
            }
        } catch (error) {
            console.error('Failed to load available invoices for client:', error);
            this.populateInvoiceSelect([]);
        }
    }
}

// Initialize payment modal
const paymentModal = new PaymentModal();

// Make payment modal instance globally available
window.paymentModal = paymentModal;

// Global function to open payment modal
window.openPaymentModal = (mode, documentId) => {
    paymentModal.openModal(mode, documentId);
};

// Global function for client search (reused from document modal)
window.searchClient = function(inputElement) {
    paymentModal.searchClient(inputElement);
};

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = PaymentModal;
} 