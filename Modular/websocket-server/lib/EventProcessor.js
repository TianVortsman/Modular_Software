const EventEmitter = require('events');

class EventProcessor extends EventEmitter {
  constructor(dbManager) {
    super();
    this.dbManager = dbManager;
    this.eventHandlers = new Map();
    this.setupDefaultHandlers();
  }
  
  setupDefaultHandlers() {
    // Register default event handlers
    this.registerHandler('invoice:sent', this.handleInvoiceSent.bind(this));
    this.registerHandler('invoice:paid', this.handleInvoicePaid.bind(this));
    this.registerHandler('user:clockedIn', this.handleUserClockedIn.bind(this));
    this.registerHandler('user:clockedOut', this.handleUserClockedOut.bind(this));
    this.registerHandler('payslip:generated', this.handlePayslipGenerated.bind(this));
    this.registerHandler('document:sent', this.handleDocumentSent.bind(this));
    this.registerHandler('payment:received', this.handlePaymentReceived.bind(this));
    this.registerHandler('refund:created', this.handleRefundCreated.bind(this));
    this.registerHandler('timesheet:submitted', this.handleTimesheetSubmitted.bind(this));
    this.registerHandler('timesheet:approved', this.handleTimesheetApproved.bind(this));
    this.registerHandler('timesheet:rejected', this.handleTimesheetRejected.bind(this));
  }
  
  registerHandler(eventType, handler) {
    this.eventHandlers.set(eventType, handler);
  }
  
  async processEvent(eventId, customerId, eventType, eventData) {
    try {
      // Update event status to processing
      await this.dbManager.updateEventStatus(eventId, customerId, 'processing');
      
      // Emit progress event
      this.emit('event:progress', {
        event_id: eventId,
        customer_id: customerId,
        event_type: eventType,
        status: 'processing',
        progress: 0
      });
      
      // Get customer settings
      const settings = await this.dbManager.getCustomerSettings(customerId);
      if (!settings) {
        throw new Error('Customer settings not found');
      }
      
      // Find and execute handler
      const handler = this.eventHandlers.get(eventType);
      if (!handler) {
        throw new Error(`No handler registered for event type: ${eventType}`);
      }
      
      // Process the event
      const result = await handler(eventId, customerId, eventData, settings);
      
      // Update event status to completed
      await this.dbManager.updateEventStatus(eventId, customerId, 'completed');
      
      // Emit completion event
      this.emit('event:completed', {
        event_id: eventId,
        customer_id: customerId,
        event_type: eventType,
        status: 'completed',
        result: result
      });
      
      return result;
      
    } catch (error) {
      console.error(`Error processing event ${eventId}:`, error);
      
      // Update event status to failed
      await this.dbManager.updateEventStatus(eventId, customerId, 'failed', error.message);
      
      // Increment retry count
      await this.dbManager.incrementRetryCount(eventId, customerId);
      
      // Emit failure event
      this.emit('event:failed', {
        event_id: eventId,
        customer_id: customerId,
        event_type: eventType,
        status: 'failed',
        error: error.message
      });
      
      throw error;
    }
  }
  
  async processPendingEvents() {
    // Get all customer IDs from active pools
    for (const [customerId, pool] of this.dbManager.pools) {
      try {
        const pendingEvents = await this.dbManager.getPendingEvents(customerId, 5);
        
        for (const event of pendingEvents) {
          try {
            await this.processEvent(
              event.id,
              customerId,
              event.event_type,
              JSON.parse(event.event_data)
            );
          } catch (error) {
            console.error(`Failed to process event ${event.id}:`, error);
          }
        }
      } catch (error) {
        console.error(`Error processing pending events for customer ${customerId}:`, error);
      }
    }
  }
  
  // Default event handlers
  async handleInvoiceSent(eventId, customerId, eventData, settings) {
    const { invoice_id, client_email, client_phone, invoice_number, total_amount } = eventData;
    
    const deliveries = [];
    
    // Send email if enabled
    if (settings.email_enabled && client_email) {
      const emailContent = this.renderTemplate('invoice_sent_email', {
        invoice_number,
        total_amount,
        client_name: eventData.client_name || 'Valued Customer'
      });
      
      const deliveryId = await this.dbManager.storeMessageDelivery(
        eventId, customerId, 'email', client_email, emailContent
      );
      
      deliveries.push({ id: deliveryId, channel: 'email', recipient: client_email });
    }
    
    // Send WhatsApp if enabled
    if (settings.whatsapp_enabled && client_phone) {
      const whatsappContent = this.renderTemplate('invoice_sent_whatsapp', {
        invoice_number,
        total_amount,
        client_name: eventData.client_name || 'Valued Customer'
      });
      
      const deliveryId = await this.dbManager.storeMessageDelivery(
        eventId, customerId, 'whatsapp', client_phone, whatsappContent
      );
      
      deliveries.push({ id: deliveryId, channel: 'whatsapp', recipient: client_phone });
    }
    
    // Send webhook if enabled
    if (settings.webhook_enabled && settings.webhook_url) {
      const webhookData = {
        event_type: 'invoice:sent',
        invoice_id,
        invoice_number,
        total_amount,
        timestamp: new Date().toISOString()
      };
      
      const deliveryId = await this.dbManager.storeMessageDelivery(
        eventId, customerId, 'webhook', settings.webhook_url, JSON.stringify(webhookData)
      );
      
      deliveries.push({ id: deliveryId, channel: 'webhook', recipient: settings.webhook_url });
    }
    
    return { deliveries };
  }
  
  async handleInvoicePaid(eventId, customerId, eventData, settings) {
    const { invoice_id, client_email, client_phone, invoice_number, payment_amount } = eventData;
    
    const deliveries = [];
    
    // Send email confirmation
    if (settings.email_enabled && client_email) {
      const emailContent = this.renderTemplate('invoice_paid_email', {
        invoice_number,
        payment_amount,
        client_name: eventData.client_name || 'Valued Customer'
      });
      
      const deliveryId = await this.dbManager.storeMessageDelivery(
        eventId, customerId, 'email', client_email, emailContent
      );
      
      deliveries.push({ id: deliveryId, channel: 'email', recipient: client_email });
    }
    
    return { deliveries };
  }
  
  async handleUserClockedIn(eventId, customerId, eventData, settings) {
    const { user_id, user_name, clock_in_time, location } = eventData;
    
    // This might trigger notifications to managers or HR
    const deliveries = [];
    
    // Send notification to managers if configured
    if (settings.email_enabled && eventData.manager_email) {
      const emailContent = this.renderTemplate('user_clocked_in_email', {
        user_name,
        clock_in_time,
        location
      });
      
      const deliveryId = await this.dbManager.storeMessageDelivery(
        eventId, customerId, 'email', eventData.manager_email, emailContent
      );
      
      deliveries.push({ id: deliveryId, channel: 'email', recipient: eventData.manager_email });
    }
    
    return { deliveries };
  }
  
  async handleUserClockedOut(eventId, customerId, eventData, settings) {
    const { user_id, user_name, clock_out_time, total_hours } = eventData;
    
    const deliveries = [];
    
    // Send daily summary to user
    if (settings.email_enabled && eventData.user_email) {
      const emailContent = this.renderTemplate('user_clocked_out_email', {
        user_name,
        clock_out_time,
        total_hours
      });
      
      const deliveryId = await this.dbManager.storeMessageDelivery(
        eventId, customerId, 'email', eventData.user_email, emailContent
      );
      
      deliveries.push({ id: deliveryId, channel: 'email', recipient: eventData.user_email });
    }
    
    return { deliveries };
  }
  
  async handlePayslipGenerated(eventId, customerId, eventData, settings) {
    const { employee_id, employee_email, employee_phone, payslip_number, net_pay } = eventData;
    
    const deliveries = [];
    
    // Send payslip via email
    if (settings.email_enabled && employee_email) {
      const emailContent = this.renderTemplate('payslip_generated_email', {
        payslip_number,
        net_pay,
        employee_name: eventData.employee_name || 'Employee'
      });
      
      const deliveryId = await this.dbManager.storeMessageDelivery(
        eventId, customerId, 'email', employee_email, emailContent
      );
      
      deliveries.push({ id: deliveryId, channel: 'email', recipient: employee_email });
    }
    
    // Send WhatsApp notification
    if (settings.whatsapp_enabled && employee_phone) {
      const whatsappContent = this.renderTemplate('payslip_generated_whatsapp', {
        payslip_number,
        net_pay
      });
      
      const deliveryId = await this.dbManager.storeMessageDelivery(
        eventId, customerId, 'whatsapp', employee_phone, whatsappContent
      );
      
      deliveries.push({ id: deliveryId, channel: 'whatsapp', recipient: employee_phone });
    }
    
    return { deliveries };
  }
  
  async handleDocumentSent(eventId, customerId, eventData, settings) {
    const { document_id, document_type, recipient_email, recipient_phone, document_number } = eventData;
    
    const deliveries = [];
    
    // Send email
    if (settings.email_enabled && recipient_email) {
      const emailContent = this.renderTemplate('document_sent_email', {
        document_type,
        document_number,
        recipient_name: eventData.recipient_name || 'Recipient'
      });
      
      const deliveryId = await this.dbManager.storeMessageDelivery(
        eventId, customerId, 'email', recipient_email, emailContent
      );
      
      deliveries.push({ id: deliveryId, channel: 'email', recipient: recipient_email });
    }
    
    return { deliveries };
  }
  
  async handlePaymentReceived(eventId, customerId, eventData, settings) {
    const { payment_id, client_email, client_phone, payment_amount, payment_method } = eventData;
    
    const deliveries = [];
    
    // Send payment confirmation
    if (settings.email_enabled && client_email) {
      const emailContent = this.renderTemplate('payment_received_email', {
        payment_amount,
        payment_method,
        client_name: eventData.client_name || 'Valued Customer'
      });
      
      const deliveryId = await this.dbManager.storeMessageDelivery(
        eventId, customerId, 'email', client_email, emailContent
      );
      
      deliveries.push({ id: deliveryId, channel: 'email', recipient: client_email });
    }
    
    return { deliveries };
  }
  
  async handleRefundCreated(eventId, customerId, eventData, settings) {
    const { refund_id, client_email, client_phone, refund_amount, reason } = eventData;
    
    const deliveries = [];
    
    // Send refund notification
    if (settings.email_enabled && client_email) {
      const emailContent = this.renderTemplate('refund_created_email', {
        refund_amount,
        reason,
        client_name: eventData.client_name || 'Valued Customer'
      });
      
      const deliveryId = await this.dbManager.storeMessageDelivery(
        eventId, customerId, 'email', client_email, emailContent
      );
      
      deliveries.push({ id: deliveryId, channel: 'email', recipient: client_email });
    }
    
    return { deliveries };
  }
  
  async handleTimesheetSubmitted(eventId, customerId, eventData, settings) {
    const { timesheet_id, employee_name, manager_email, period } = eventData;
    
    const deliveries = [];
    
    // Notify manager of timesheet submission
    if (settings.email_enabled && manager_email) {
      const emailContent = this.renderTemplate('timesheet_submitted_email', {
        employee_name,
        period
      });
      
      const deliveryId = await this.dbManager.storeMessageDelivery(
        eventId, customerId, 'email', manager_email, emailContent
      );
      
      deliveries.push({ id: deliveryId, channel: 'email', recipient: manager_email });
    }
    
    return { deliveries };
  }
  
  async handleTimesheetApproved(eventId, customerId, eventData, settings) {
    const { timesheet_id, employee_email, employee_phone, period } = eventData;
    
    const deliveries = [];
    
    // Notify employee of approval
    if (settings.email_enabled && employee_email) {
      const emailContent = this.renderTemplate('timesheet_approved_email', {
        period
      });
      
      const deliveryId = await this.dbManager.storeMessageDelivery(
        eventId, customerId, 'email', employee_email, emailContent
      );
      
      deliveries.push({ id: deliveryId, channel: 'email', recipient: employee_email });
    }
    
    return { deliveries };
  }
  
  async handleTimesheetRejected(eventId, customerId, eventData, settings) {
    const { timesheet_id, employee_email, employee_phone, period, reason } = eventData;
    
    const deliveries = [];
    
    // Notify employee of rejection
    if (settings.email_enabled && employee_email) {
      const emailContent = this.renderTemplate('timesheet_rejected_email', {
        period,
        reason
      });
      
      const deliveryId = await this.dbManager.storeMessageDelivery(
        eventId, customerId, 'email', employee_email, emailContent
      );
      
      deliveries.push({ id: deliveryId, channel: 'email', recipient: employee_email });
    }
    
    return { deliveries };
  }
  
  renderTemplate(templateName, data) {
    // Simple template rendering with {{variable}} placeholders
    const templates = {
      'invoice_sent_email': `Dear {{client_name}},

Your invoice {{invoice_number}} has been sent.
Total Amount: R{{total_amount}}

Thank you for your business.

Best regards,
Your Company`,
      
      'invoice_sent_whatsapp': `Hi {{client_name}}! 

Your invoice {{invoice_number}} has been sent.
Amount: R{{total_amount}}

Thank you!`,
      
      'invoice_paid_email': `Dear {{client_name}},

Thank you for your payment of R{{payment_amount}} for invoice {{invoice_number}}.

Your payment has been received and processed.

Best regards,
Your Company`,
      
      'user_clocked_in_email': `{{user_name}} has clocked in at {{clock_in_time}}.

Location: {{location}}`,
      
      'user_clocked_out_email': `Hi {{user_name}},

You have clocked out at {{clock_out_time}}.
Total hours worked today: {{total_hours}}

Have a great day!`,
      
      'payslip_generated_email': `Dear {{employee_name}},

Your payslip {{payslip_number}} has been generated.
Net Pay: R{{net_pay}}

Please check your employee portal for details.

Best regards,
HR Department`,
      
      'payslip_generated_whatsapp': `Hi! Your payslip {{payslip_number}} is ready.
Net Pay: R{{net_pay}}

Check your email for details.`,
      
      'document_sent_email': `Dear {{recipient_name}},

Your {{document_type}} {{document_number}} has been sent.

Please check your email for the document.

Best regards,
Your Company`,
      
      'payment_received_email': `Dear {{client_name}},

Thank you for your payment of R{{payment_amount}} via {{payment_method}}.

Your payment has been received and processed.

Best regards,
Your Company`,
      
      'refund_created_email': `Dear {{client_name}},

A refund of R{{refund_amount}} has been processed.

Reason: {{reason}}

The refund will be processed within 3-5 business days.

Best regards,
Your Company`,
      
      'timesheet_submitted_email': `{{employee_name}} has submitted their timesheet for {{period}}.

Please review and approve/reject.`,
      
      'timesheet_approved_email': `Your timesheet for {{period}} has been approved.

Thank you!`,
      
      'timesheet_rejected_email': `Your timesheet for {{period}} has been rejected.

Reason: {{reason}}

Please resubmit with corrections.`
    };
    
    let template = templates[templateName] || 'Template not found';
    
    // Replace placeholders
    for (const [key, value] of Object.entries(data)) {
      template = template.replace(new RegExp(`{{${key}}}`, 'g'), value);
    }
    
    return template;
  }
}

module.exports = EventProcessor; 