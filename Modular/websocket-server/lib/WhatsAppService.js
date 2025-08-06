const EventEmitter = require('events');
const WhatsAppWebManager = require('./WhatsAppWebManager');

class WhatsAppService extends EventEmitter {
  constructor(dbManager) {
    super();
    this.dbManager = dbManager;
    this.webManager = new WhatsAppWebManager(dbManager);
    
    // Forward events from web manager
    this.webManager.on('qr:generated', (data) => {
      this.emit('qr:generated', data);
    });
    
    this.webManager.on('client:ready', (data) => {
      this.emit('client:ready', data);
    });
    
    this.webManager.on('client:authenticated', (data) => {
      this.emit('client:authenticated', data);
    });
    
    this.webManager.on('client:auth_failed', (data) => {
      this.emit('client:auth_failed', data);
    });
    
    this.webManager.on('client:disconnected', (data) => {
      this.emit('client:disconnected', data);
    });
    
    this.webManager.on('client:logged_out', (data) => {
      this.emit('client:logged_out', data);
    });
  }
  
  async sendWhatsAppMessage(deliveryId, customerId, recipient, message) {
    try {
      const settings = await this.dbManager.getCustomerSettings(customerId);
      if (!settings || !settings.whatsapp_enabled) {
        throw new Error('WhatsApp is disabled for this customer');
      }
      
      // Send message using WhatsApp Web
      const result = await this.webManager.sendMessage(customerId, recipient, message);
      
      // Update delivery status
      await this.dbManager.updateMessageDeliveryStatus(deliveryId, customerId, 'sent');
      
      // Emit success event
      this.emit('message:sent', {
        delivery_id: deliveryId,
        customer_id: customerId,
        channel: 'whatsapp',
        recipient: recipient,
        message_id: result.message_id,
        status: 'sent'
      });
      
      return {
        success: true,
        message_id: result.message_id,
        timestamp: result.timestamp
      };
      
    } catch (error) {
      console.error(`WhatsApp sending failed for delivery ${deliveryId}:`, error);
      
      // Update delivery status
      await this.dbManager.updateMessageDeliveryStatus(deliveryId, customerId, 'failed', error.message);
      
      // Increment retry count
      await this.dbManager.incrementDeliveryRetryCount(deliveryId, customerId);
      
      // Emit failure event
      this.emit('message:failed', {
        delivery_id: deliveryId,
        customer_id: customerId,
        channel: 'whatsapp',
        recipient: recipient,
        error: error.message
      });
      
      throw error;
    }
  }
  
  async sendDocument(customerId, recipient, documentPath, caption = null) {
    try {
      // Send document using WhatsApp Web
      const result = await this.webManager.sendDocument(customerId, recipient, documentPath, caption);
      
      return {
        success: true,
        message_id: result.message_id,
        timestamp: result.timestamp
      };
      
    } catch (error) {
      console.error(`WhatsApp document sending failed for customer ${customerId}:`, error);
      
      return {
        success: false,
        message: error.message,
        error: error.message
      };
    }
  }
  
  async sendWhatsAppDocument(deliveryId, customerId, recipient, documentPath, caption = null) {
    try {
      const settings = await this.dbManager.getCustomerSettings(customerId);
      if (!settings || !settings.whatsapp_enabled) {
        throw new Error('WhatsApp is disabled for this customer');
      }
      
      // Send document using WhatsApp Web
      const result = await this.webManager.sendDocument(customerId, recipient, documentPath, caption);
      
      // Update delivery status
      await this.dbManager.updateMessageDeliveryStatus(deliveryId, customerId, 'sent');
      
      // Emit success event
      this.emit('message:sent', {
        delivery_id: deliveryId,
        customer_id: customerId,
        channel: 'whatsapp',
        recipient: recipient,
        message_id: result.message_id,
        status: 'sent'
      });
      
      return {
        success: true,
        message_id: result.message_id,
        timestamp: result.timestamp
      };
      
    } catch (error) {
      console.error(`WhatsApp document sending failed for delivery ${deliveryId}:`, error);
      
      // Update delivery status
      await this.dbManager.updateMessageDeliveryStatus(deliveryId, customerId, 'failed', error.message);
      
      // Increment retry count
      await this.dbManager.incrementDeliveryRetryCount(deliveryId, customerId);
      
      // Emit failure event
      this.emit('message:failed', {
        delivery_id: deliveryId,
        customer_id: customerId,
        channel: 'whatsapp',
        recipient: recipient,
        error: error.message
      });
      
      throw error;
    }
  }
  
  async getQRCode(customerId) {
    return await this.webManager.getQRCode(customerId);
  }
  
  async getSessionStatus(customerId) {
    return await this.webManager.getSessionStatus(customerId);
  }
  
  async logout(customerId) {
    return await this.webManager.logout(customerId);
  }
  
  async processPendingWhatsAppMessages() {
    // Get all customer IDs from active pools
    for (const [customerId, pool] of this.dbManager.pools) {
      try {
        const settings = await this.dbManager.getCustomerSettings(customerId);
        if (!settings || !settings.whatsapp_enabled) {
          continue; // Skip if WhatsApp is disabled
        }
        
        // Get pending WhatsApp deliveries
        const pendingDeliveries = await this.getPendingWhatsAppDeliveries(customerId);
        
        for (const delivery of pendingDeliveries) {
          try {
            await this.sendWhatsAppMessage(
              delivery.id,
              customerId,
              delivery.recipient,
              delivery.message_content
            );
            
            // Add delay to avoid rate limiting
            await this.delay(2000);
            
          } catch (error) {
            console.error(`Failed to send WhatsApp delivery ${delivery.id}:`, error);
          }
        }
      } catch (error) {
        console.error(`Error processing pending WhatsApp messages for customer ${customerId}:`, error);
      }
    }
  }
  
  async getPendingWhatsAppDeliveries(customerId) {
    const pool = await this.dbManager.getCustomerPool(customerId);
    
    const query = `
      SELECT md.*, we.event_type, we.event_data
      FROM settings.message_deliveries md
      JOIN settings.websocket_events we ON md.event_id = we.id
      WHERE md.customer_id = $1 AND md.channel = 'whatsapp' AND md.status = 'pending'
      ORDER BY md.id ASC
      LIMIT 10
    `;
    
    const result = await pool.query(query, [customerId]);
    return result.rows;
  }
  
  async retryFailedWhatsAppMessages() {
    for (const [customerId, pool] of this.dbManager.pools) {
      try {
        const settings = await this.dbManager.getCustomerSettings(customerId);
        if (!settings || !settings.whatsapp_enabled) {
          continue;
        }
        
        const failedDeliveries = await this.dbManager.getFailedDeliveries(customerId, settings.max_retries);
        
        for (const delivery of failedDeliveries) {
          if (delivery.channel === 'whatsapp' && delivery.retry_count < settings.max_retries) {
            try {
              await this.sendWhatsAppMessage(
                delivery.id,
                customerId,
                delivery.recipient,
                delivery.message_content
              );
              
              await this.delay(3000); // Longer delay for retries
              
            } catch (error) {
              console.error(`Retry failed for WhatsApp delivery ${delivery.id}:`, error);
            }
          }
        }
      } catch (error) {
        console.error(`Error retrying failed WhatsApp messages for customer ${customerId}:`, error);
      }
    }
  }
  
  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
  
  async close() {
    await this.webManager.close();
  }
}

module.exports = WhatsAppService; 