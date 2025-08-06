const { Pool } = require('pg');
const EventEmitter = require('events');

class DatabaseManager extends EventEmitter {
  constructor() {
    super();
    this.pools = new Map(); // Cache database pools per customer
    this.mainPool = null;
    this.initMainPool();
  }
  
  initMainPool() {
    this.mainPool = new Pool({
      host: process.env.DB_HOST || 'postgres',
      port: process.env.DB_PORT || 5432,
      database: process.env.DB_NAME || 'modular_system',
      user: process.env.DB_USER || 'Tian',
      password: process.env.DB_PASSWORD || 'Modul@rdev@2024',
      max: 20,
      idleTimeoutMillis: 30000,
      connectionTimeoutMillis: 2000,
    });
  }
  
  async getCustomerPool(customerId) {
    if (this.pools.has(customerId)) {
      return this.pools.get(customerId);
    }
    
    // Get customer database config from main database
    const customerConfig = await this.getCustomerConfig(customerId);
    
    if (!customerConfig) {
      throw new Error(`Customer ${customerId} not found`);
    }
    
    const pool = new Pool({
      host: customerConfig.host || process.env.DB_HOST || 'postgres',
      port: customerConfig.port || process.env.DB_PORT || 5432,
      database: customerConfig.database,
      user: customerConfig.username,
      password: customerConfig.password,
      max: 10,
      idleTimeoutMillis: 30000,
      connectionTimeoutMillis: 2000,
    });
    
    this.pools.set(customerId, pool);
    return pool;
  }
  
  async getCustomerConfig(customerId) {
    const query = `
      SELECT 
        'postgres' as host,
        5432 as port,
        client_db as database,
        'Tian' as username,
        'Modul@rdev@2024' as password
      FROM customers 
      WHERE account_number = $1 AND status = 'active'
    `;
    
    const result = await this.mainPool.query(query, [customerId]);
    return result.rows[0] || null;
  }
  
  async storeEvent(customerId, eventType, eventData) {
    const pool = await this.getCustomerPool(customerId);
    
    const query = `
      INSERT INTO settings.websocket_events (customer_id, event_type, event_data, status)
      VALUES ($1, $2, $3, 'pending')
      RETURNING id
    `;
    
    const result = await pool.query(query, [customerId, eventType, JSON.stringify(eventData)]);
    return result.rows[0].id;
  }
  
  async updateEventStatus(eventId, customerId, status, errorMessage = null) {
    const pool = await this.getCustomerPool(customerId);
    
    const query = `
      UPDATE settings.websocket_events 
      SET status = $1, error_message = $2, processed_at = NOW()
      WHERE id = $3 AND customer_id = $4
    `;
    
    await pool.query(query, [status, errorMessage, eventId, customerId]);
  }
  
  async getPendingEvents(customerId, limit = 10) {
    const pool = await this.getCustomerPool(customerId);
    
    const query = `
      SELECT id, event_type, event_data, retry_count
      FROM settings.websocket_events 
      WHERE customer_id = $1 AND status = 'pending'
      ORDER BY created_at ASC
      LIMIT $2
    `;
    
    const result = await pool.query(query, [customerId, limit]);
    return result.rows;
  }
  
  async incrementRetryCount(eventId, customerId) {
    const pool = await this.getCustomerPool(customerId);
    
    const query = `
      UPDATE settings.websocket_events 
      SET retry_count = retry_count + 1
      WHERE id = $1 AND customer_id = $2
    `;
    
    await pool.query(query, [eventId, customerId]);
  }
  
  async getCustomerSettings(customerId) {
    const pool = await this.getCustomerPool(customerId);
    
    const query = `
      SELECT * FROM settings.email_whatsapp 
      WHERE customer_id = $1
    `;
    
    const result = await pool.query(query, [customerId]);
    return result.rows[0] || null;
  }
  
  async trackConnection(customerId, userId, sessionId, connectionId, ipAddress, userAgent) {
    const pool = await this.getCustomerPool(customerId);
    
    // Mark any existing connections as inactive
    await pool.query(`
      UPDATE settings.websocket_connections 
      SET is_active = false, disconnected_at = NOW()
      WHERE customer_id = $1 AND user_id = $2 AND is_active = true
    `, [customerId, userId]);
    
    // Insert new connection
    const query = `
      INSERT INTO settings.websocket_connections 
      (customer_id, user_id, session_id, connection_id, ip_address, user_agent, is_active)
      VALUES ($1, $2, $3, $4, $5, $6, true)
    `;
    
    await pool.query(query, [customerId, userId, sessionId, connectionId, ipAddress, userAgent]);
  }
  
  async trackDisconnection(connectionId) {
    // Find the connection across all customer databases
    for (const [customerId, pool] of this.pools) {
      try {
        const query = `
          UPDATE settings.websocket_connections 
          SET is_active = false, disconnected_at = NOW()
          WHERE connection_id = $1
        `;
        
        const result = await pool.query(query, [connectionId]);
        if (result.rowCount > 0) {
          break; // Found and updated
        }
      } catch (error) {
        // Continue to next pool if this one fails
        console.error(`Error tracking disconnection for customer ${customerId}:`, error);
      }
    }
  }
  
  async cleanupOldConnections() {
    const cutoffTime = new Date(Date.now() - 24 * 60 * 60 * 1000); // 24 hours ago
    
    for (const [customerId, pool] of this.pools) {
      try {
        const query = `
          DELETE FROM settings.websocket_connections 
          WHERE disconnected_at < $1 OR (is_active = false AND connected_at < $1)
        `;
        
        await pool.query(query, [cutoffTime]);
      } catch (error) {
        console.error(`Error cleaning up connections for customer ${customerId}:`, error);
      }
    }
  }
  
  async storeMessageDelivery(eventId, customerId, channel, recipient, messageContent) {
    const pool = await this.getCustomerPool(customerId);
    
    const query = `
      INSERT INTO settings.message_deliveries 
      (event_id, customer_id, channel, recipient, message_content, status)
      VALUES ($1, $2, $3, $4, $5, 'pending')
      RETURNING id
    `;
    
    const result = await pool.query(query, [eventId, customerId, channel, recipient, messageContent]);
    return result.rows[0].id;
  }
  
  async updateMessageDeliveryStatus(deliveryId, customerId, status, errorMessage = null) {
    const pool = await this.getCustomerPool(customerId);
    
    const query = `
      UPDATE settings.message_deliveries 
      SET status = $1, error_message = $2, sent_at = CASE WHEN $1 = 'sent' THEN NOW() ELSE sent_at END, delivered_at = CASE WHEN $1 = 'delivered' THEN NOW() ELSE delivered_at END
      WHERE id = $3 AND customer_id = $4
    `;
    
    await pool.query(query, [status, errorMessage, deliveryId, customerId]);
  }
  
  async getFailedDeliveries(customerId, maxRetries = 3) {
    const pool = await this.getCustomerPool(customerId);
    
    const query = `
      SELECT md.*, we.event_type, we.event_data
      FROM settings.message_deliveries md
      JOIN settings.websocket_events we ON md.event_id = we.id
      WHERE md.customer_id = $1 AND md.status = 'failed' AND md.retry_count < $2
      ORDER BY md.sent_at ASC
    `;
    
    const result = await pool.query(query, [customerId, maxRetries]);
    return result.rows;
  }
  
  async incrementDeliveryRetryCount(deliveryId, customerId) {
    const pool = await this.getCustomerPool(customerId);
    
    const query = `
      UPDATE settings.message_deliveries 
      SET retry_count = retry_count + 1
      WHERE id = $1 AND customer_id = $2
    `;
    
    await pool.query(query, [deliveryId, customerId]);
  }
  
  async verifySession(customerId, userId, sessionId) {
    const pool = await this.getCustomerPool(customerId);
    
    // This is a simplified session verification
    // In a real implementation, you'd check against your session table
    const query = `
      SELECT 1 FROM users 
      WHERE user_id = $1 AND is_active = true
      LIMIT 1
    `;
    
    const result = await pool.query(query, [userId]);
    return result.rows.length > 0;
  }
  
  async close() {
    // Close all customer pools
    for (const [customerId, pool] of this.pools) {
      await pool.end();
    }
    this.pools.clear();
    
    // Close main pool
    if (this.mainPool) {
      await this.mainPool.end();
    }
  }
}

module.exports = DatabaseManager; 