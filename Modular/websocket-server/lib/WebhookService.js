const axios = require('axios');
const EventEmitter = require('events');

class WebhookService extends EventEmitter {
  constructor(dbManager) {
    super();
    this.dbManager = dbManager;
  }
  
  async sendWebhook(deliveryId, customerId, webhookUrl, data, secret = null) {
    try {
      const settings = await this.dbManager.getCustomerSettings(customerId);
      if (!settings || !settings.webhook_enabled) {
        throw new Error('Webhook is disabled for this customer');
      }
      
      // Prepare webhook payload
      const payload = {
        event_type: data.event_type,
        customer_id: customerId,
        timestamp: new Date().toISOString(),
        data: data
      };
      
      // Add signature if secret is provided
      const headers = {
        'Content-Type': 'application/json',
        'User-Agent': 'Modular-System-Webhook/1.0'
      };
      
      if (secret) {
        const signature = this.generateSignature(payload, secret);
        headers['X-Webhook-Signature'] = signature;
      }
      
      // Send webhook
      const response = await axios.post(webhookUrl, payload, {
        headers: headers,
        timeout: 10000 // 10 second timeout
      });
      
      // Update delivery status
      await this.dbManager.updateMessageDeliveryStatus(deliveryId, customerId, 'sent');
      
      // Emit success event
      this.emit('webhook:sent', {
        delivery_id: deliveryId,
        customer_id: customerId,
        webhook_url: webhookUrl,
        status: 'sent',
        response_status: response.status
      });
      
      return {
        success: true,
        response_status: response.status,
        response_data: response.data
      };
      
    } catch (error) {
      console.error(`Webhook sending failed for delivery ${deliveryId}:`, error);
      
      // Update delivery status
      await this.dbManager.updateMessageDeliveryStatus(deliveryId, customerId, 'failed', error.message);
      
      // Increment retry count
      await this.dbManager.incrementDeliveryRetryCount(deliveryId, customerId);
      
      // Emit failure event
      this.emit('webhook:failed', {
        delivery_id: deliveryId,
        customer_id: customerId,
        webhook_url: webhookUrl,
        error: error.message
      });
      
      throw error;
    }
  }
  
  async processPendingWebhooks() {
    // Get all customer IDs from active pools
    for (const [customerId, pool] of this.dbManager.pools) {
      try {
        const settings = await this.dbManager.getCustomerSettings(customerId);
        if (!settings || !settings.webhook_enabled || !settings.webhook_url) {
          continue; // Skip if webhook is disabled
        }
        
        // Get pending webhook deliveries
        const pendingDeliveries = await this.getPendingWebhookDeliveries(customerId);
        
        for (const delivery of pendingDeliveries) {
          try {
            const webhookData = JSON.parse(delivery.message_content);
            
            await this.sendWebhook(
              delivery.id,
              customerId,
              settings.webhook_url,
              webhookData,
              settings.webhook_secret
            );
            
            // Add delay to avoid overwhelming the webhook endpoint
            await this.delay(1000);
            
          } catch (error) {
            console.error(`Failed to send webhook delivery ${delivery.id}:`, error);
          }
        }
      } catch (error) {
        console.error(`Error processing pending webhooks for customer ${customerId}:`, error);
      }
    }
  }
  
  async getPendingWebhookDeliveries(customerId) {
    const pool = await this.dbManager.getCustomerPool(customerId);
    
    const query = `
      SELECT md.*, we.event_type, we.event_data
      FROM message_deliveries md
      JOIN websocket_events we ON md.event_id = we.id
      WHERE md.customer_id = $1 AND md.channel = 'webhook' AND md.status = 'pending'
      ORDER BY md.id ASC
      LIMIT 10
    `;
    
    const result = await pool.query(query, [customerId]);
    return result.rows;
  }
  
  async retryFailedWebhooks() {
    for (const [customerId, pool] of this.dbManager.pools) {
      try {
        const settings = await this.dbManager.getCustomerSettings(customerId);
        if (!settings || !settings.webhook_enabled || !settings.webhook_url) {
          continue;
        }
        
        const failedDeliveries = await this.dbManager.getFailedDeliveries(customerId, settings.max_retries);
        
        for (const delivery of failedDeliveries) {
          if (delivery.channel === 'webhook' && delivery.retry_count < settings.max_retries) {
            try {
              const webhookData = JSON.parse(delivery.message_content);
              
              await this.sendWebhook(
                delivery.id,
                customerId,
                settings.webhook_url,
                webhookData,
                settings.webhook_secret
              );
              
              await this.delay(2000); // Longer delay for retries
              
            } catch (error) {
              console.error(`Retry failed for webhook delivery ${delivery.id}:`, error);
            }
          }
        }
      } catch (error) {
        console.error(`Error retrying failed webhooks for customer ${customerId}:`, error);
      }
    }
  }
  
  generateSignature(payload, secret) {
    const crypto = require('crypto');
    const hmac = crypto.createHmac('sha256', secret);
    hmac.update(JSON.stringify(payload));
    return hmac.digest('hex');
  }
  
  delay(ms) {
    return new Promise(resolve => setTimeout(resolve, ms));
  }
}

module.exports = WebhookService; 