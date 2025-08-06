const nodemailer = require('nodemailer');
const EventEmitter = require('events');

class EmailService extends EventEmitter {
  constructor(dbManager) {
    super();
    this.dbManager = dbManager;
    this.transporters = new Map(); // Cache transporters per customer
  }
  
  async getTransporter(customerId, settings) {
    if (this.transporters.has(customerId)) {
      return this.transporters.get(customerId);
    }
    
    let transporter;
    
    if (settings.use_customer_smtp) {
      // Use customer's SMTP settings
      transporter = nodemailer.createTransporter({
        host: settings.smtp_host,
        port: settings.smtp_port,
        secure: settings.smtp_secure,
        auth: {
          user: settings.smtp_username,
          pass: settings.smtp_password
        }
      });
    } else {
      // Use system default SMTP
      transporter = nodemailer.createTransporter({
        host: process.env.SYSTEM_SMTP_HOST || 'smtp.gmail.com',
        port: process.env.SYSTEM_SMTP_PORT || 587,
        secure: false,
        auth: {
          user: process.env.SYSTEM_SMTP_USER || 'tianryno01@gmail.com',
          pass: process.env.SYSTEM_SMTP_PASS || 'axms oobi witf ytqa'
        }
      });
    }
    
    // Verify connection
    try {
      await transporter.verify();
      console.log(`Email transporter verified for customer ${customerId}`);
    } catch (error) {
      console.error(`Email transporter verification failed for customer ${customerId}:`, error);
      throw error;
    }
    
    this.transporters.set(customerId, transporter);
    return transporter;
  }
  
  async sendEmail(deliveryId, customerId, recipient, subject, content, attachments = []) {
    try {
      const settings = await this.dbManager.getCustomerSettings(customerId);
      if (!settings || !settings.email_enabled) {
        throw new Error('Email is disabled for this customer');
      }
      
      const transporter = await this.getTransporter(customerId, settings);
      
      // Prepare email options
      const mailOptions = {
        from: settings.use_customer_smtp 
          ? `"${settings.smtp_from_name}" <${settings.smtp_from_email}>`
          : `"${process.env.SYSTEM_SMTP_FROM_NAME || 'Modular System'}" <${process.env.SYSTEM_SMTP_USER || 'tianryno01@gmail.com'}>`,
        to: recipient,
        subject: subject,
        html: content,
        attachments: attachments
      };
      
      // Send email
      const result = await transporter.sendMail(mailOptions);
      
      // Update delivery status
      await this.dbManager.updateMessageDeliveryStatus(deliveryId, customerId, 'sent');
      
      // Emit success event
      this.emit('message:sent', {
        delivery_id: deliveryId,
        customer_id: customerId,
        channel: 'email',
        recipient: recipient,
        message_id: result.messageId,
        status: 'sent'
      });
      
      return {
        success: true,
        message_id: result.messageId,
        response: result.response
      };
      
    } catch (error) {
      console.error(`Email sending failed for delivery ${deliveryId}:`, error);
      
      // Update delivery status
      await this.dbManager.updateMessageDeliveryStatus(deliveryId, customerId, 'failed', error.message);
      
      // Increment retry count
      await this.dbManager.incrementDeliveryRetryCount(deliveryId, customerId);
      
      // Emit failure event
      this.emit('message:failed', {
        delivery_id: deliveryId,
        customer_id: customerId,
        channel: 'email',
        recipient: recipient,
        error: error.message
      });
      
      throw error;
    }
  }
  
  async processPendingEmails() {
    // Get all customer IDs from active pools
    for (const [customerId, pool] of this.dbManager.pools) {
      try {
        const settings = await this.dbManager.getCustomerSettings(customerId);
        if (!settings || !settings.email_enabled) {
          continue; // Skip if email is disabled
        }
        
        // Get pending email deliveries
        const pendingDeliveries = await this.getPendingEmailDeliveries(customerId);
        
        for (const delivery of pendingDeliveries) {
          try {
            // Parse message content to extract subject and body
            const { subject, body } = this.parseEmailContent(delivery.message_content);
            
            await this.sendEmail(
              delivery.id,
              customerId,
              delivery.recipient,
              subject,
              body
            );
            
            // Add delay to avoid rate limiting
            await this.delay(1000);
            
          } catch (error) {
            console.error(`Failed to send email delivery ${delivery.id}:`, error);
          }
        }
      } catch (error) {
        console.error(`Error processing pending emails for customer ${customerId}:`, error);
      }
    }
  }
  
  async getPendingEmailDeliveries(customerId) {
    const pool = await this.dbManager.getCustomerPool(customerId);
    
    const query = `
      SELECT md.*, we.event_type, we.event_data
      FROM message_deliveries md
      JOIN websocket_events we ON md.event_id = we.id
      WHERE md.customer_id = $1 AND md.channel = 'email' AND md.status = 'pending'
      ORDER BY md.id ASC
      LIMIT 10
    `;
    
    const result = await pool.query(query, [customerId]);
    return result.rows;
  }
  
  parseEmailContent(content) {
    // Simple parsing - assumes first line is subject, rest is body
    const lines = content.split('\n');
    const subject = lines[0] || 'Notification from Modular System';
    const body = lines.slice(1).join('\n') || content;
    
    return { subject, body };
  }
  
  async retryFailedEmails() {
    for (const [customerId, pool] of this.dbManager.pools) {
      try {
        const settings = await this.dbManager.getCustomerSettings(customerId);
        if (!settings || !settings.email_enabled) {
          continue;
        }
        
        const failedDeliveries = await this.dbManager.getFailedDeliveries(customerId, settings.max_retries);
        
        for (const delivery of failedDeliveries) {
          if (delivery.channel === 'email' && delivery.retry_count < settings.max_retries) {
            try {
              const { subject, body } = this.parseEmailContent(delivery.message_content);
              
              await this.sendEmail(
                delivery.id,
                customerId,
                delivery.recipient,
                subject,
                body
              );
              
              await this.delay(2000); // Longer delay for retries
              
            } catch (error) {
              console.error(`Retry failed for email delivery ${delivery.id}:`, error);
            }
          }
        }
      } catch (error) {
        console.error(`Error retrying failed emails for customer ${customerId}:`, error);
      }
    }
  }
  
  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
  
  async close() {
    // Close all transporters
    for (const [customerId, transporter] of this.transporters) {
      try {
        await transporter.close();
      } catch (error) {
        console.error(`Error closing transporter for customer ${customerId}:`, error);
      }
    }
    this.transporters.clear();
  }
}

module.exports = EmailService; 