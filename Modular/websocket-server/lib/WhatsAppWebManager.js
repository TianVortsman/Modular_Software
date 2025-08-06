const { create, Whatsapp } = require('@wppconnect-team/wppconnect');
const qrcode = require('qrcode');
const EventEmitter = require('events');
const fs = require('fs');
const path = require('path');

class WhatsAppWebManager extends EventEmitter {
  constructor(dbManager) {
    super();
    this.dbManager = dbManager;
    this.clients = new Map(); // customerId -> WhatsApp client
    this.qrCodes = new Map(); // customerId -> QR code data
    this.sessionDir = path.join(__dirname, '../sessions');
    
    // Ensure sessions directory exists
    if (!fs.existsSync(this.sessionDir)) {
      fs.mkdirSync(this.sessionDir, { recursive: true });
    }
  }

  async initializeClient(customerId) {
    try {
      // Check if client already exists
      if (this.clients.has(customerId)) {
        const client = this.clients.get(customerId);
        if (client.isConnected) {
          return client;
        }
      }

      // Create session directory for this customer
      const customerSessionDir = path.join(this.sessionDir, `customer_${customerId}`);
      if (!fs.existsSync(customerSessionDir)) {
        fs.mkdirSync(customerSessionDir, { recursive: true });
      }

      // Check if session files exist (indicating previous login)
      const sessionFiles = fs.readdirSync(customerSessionDir);
      const hasExistingSession = sessionFiles.some(file => 
        file.includes('session') || file.includes('tokens') || file.includes('auth')
      );

      console.log(`Initializing wppconnect client for customer ${customerId} (existing session: ${hasExistingSession})`);

      // Create wppconnect client with session restoration
      const client = await create({
        session: `customer_${customerId}`,
        autoClose: false, // Disable auto-close
        createOptions: {
          // Enable session restoration
          sessionDataPath: customerSessionDir,
          // Keep session alive
          keepAliveIntervalMs: 30000,
          // Auto reconnect
          autoReconnect: true,
          // Session timeout
          sessionTimeoutMs: 60000
        },
        catchQR: async (base64Qr, asciiQR, attempts, urlCode) => {
          console.log(`QR Code received for customer ${customerId}, attempt ${attempts}`);
          
          try {
            // Generate QR code as data URL - base64Qr already contains the prefix
            const qrDataUrl = base64Qr.startsWith('data:') ? base64Qr : `data:image/png;base64,${base64Qr}`;
            this.qrCodes.set(customerId, {
              qr: qrDataUrl,
              timestamp: Date.now()
            });
            
            // Emit QR code event
            this.emit('qr:generated', {
              customer_id: customerId,
              qr: qrDataUrl,
              timestamp: Date.now()
            });
            
            // Update database with QR status
            await this.updateSessionStatus(customerId, 'qr_ready', qrDataUrl);
          } catch (error) {
            console.error(`Error generating QR code for customer ${customerId}:`, error);
          }
        },
        statusFind: (statusSession, session) => {
          console.log(`Status Session: ${statusSession} for customer ${customerId}`);
        },
        headless: true,
        devtools: false,
        useChrome: true,
        debug: false,
        logQR: false,
        browserWS: '',
        browserArgs: [
          '--no-sandbox',
          '--disable-setuid-sandbox',
          '--disable-dev-shm-usage',
          '--disable-accelerated-2d-canvas',
          '--no-first-run',
          '--no-zygote',
          '--disable-gpu',
          '--disable-web-security',
          '--disable-features=VizDisplayCompositor',
          '--disable-background-timer-throttling',
          '--disable-backgrounding-occluded-windows',
          '--disable-renderer-backgrounding',
          '--disable-features=TranslateUI',
          '--disable-ipc-flooding-protection',
          '--disable-extensions',
          '--disable-plugins',
          '--disable-images',
          '--disable-default-apps',
          '--disable-sync',
          '--disable-translate',
          '--hide-scrollbars',
          '--mute-audio',
          '--no-default-browser-check',
          '--disable-component-extensions-with-background-pages',
          '--disable-background-networking',
          '--disable-client-side-phishing-detection',
          '--disable-hang-monitor',
          '--disable-prompt-on-repost',
          '--disable-web-resources',
          '--metrics-recording-only',
          '--safebrowsing-disable-auto-update',
          '--enable-automation',
          '--password-store=basic',
          '--use-mock-keychain'
        ],
        puppeteerOptions: {
          executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || '/usr/bin/chromium'
        },
        createOptions: {
          folderNameToken: customerSessionDir,
          mkdirFolderToken: customerSessionDir,
          headless: true,
          devtools: false,
          useChrome: true,
          debug: false,
          logQR: false,
          browserWS: '',
          browserArgs: [
            '--no-sandbox',
            '--disable-setuid-sandbox',
            '--disable-dev-shm-usage',
            '--disable-accelerated-2d-canvas',
            '--no-first-run',
            '--no-zygote',
            '--disable-gpu',
            '--disable-web-security',
            '--disable-features=VizDisplayCompositor',
            '--disable-background-timer-throttling',
            '--disable-backgrounding-occluded-windows',
            '--disable-renderer-backgrounding',
            '--disable-features=TranslateUI',
            '--disable-ipc-flooding-protection',
            '--disable-extensions',
            '--disable-plugins',
            '--disable-images',
            '--disable-default-apps',
            '--disable-sync',
            '--disable-translate',
            '--hide-scrollbars',
            '--mute-audio',
            '--no-default-browser-check',
            '--disable-component-extensions-with-background-pages',
            '--disable-background-networking',
            '--disable-client-side-phishing-detection',
            '--disable-hang-monitor',
            '--disable-prompt-on-repost',
            '--disable-web-resources',
            '--metrics-recording-only',
            '--safebrowsing-disable-auto-update',
            '--enable-automation',
            '--password-store=basic',
            '--use-mock-keychain'
          ],
          puppeteerOptions: {
            executablePath: process.env.PUPPETEER_EXECUTABLE_PATH || '/usr/bin/chromium'
          }
        }
      });

      // Set up event handlers
      client.onStateChange((state) => {
        console.log(`State changed for customer ${customerId}:`, state);
        
        if (state === 'CONNECTED') {
          // Clear QR code
          this.qrCodes.delete(customerId);
          
          // Update database with ready status
          this.updateSessionStatus(customerId, 'ready', null);
          
          // Emit ready event
          this.emit('client:ready', {
            customer_id: customerId,
            timestamp: Date.now()
          });
        } else if (state === 'DISCONNECTED') {
          // Update database with disconnected status
          this.updateSessionStatus(customerId, 'disconnected', null);
          
          // Emit disconnected event
          this.emit('client:disconnected', {
            customer_id: customerId,
            reason: 'disconnected',
            timestamp: Date.now()
          });
          
          // Remove client from cache
          this.clients.delete(customerId);
        }
      });

      // Store client in cache
      this.clients.set(customerId, client);
      
      return client;
      
    } catch (error) {
      console.error(`Error initializing wppconnect client for customer ${customerId}:`, error);
      throw error;
    }
  }

  async getClient(customerId) {
    if (this.clients.has(customerId)) {
      const client = this.clients.get(customerId);
      if (client.isConnected) {
        return client;
      }
    }
    
    // Initialize new client
    return await this.initializeClient(customerId);
  }

  async sendMessage(customerId, recipient, message) {
    try {
      const client = await this.getClient(customerId);
      
      if (!client.isConnected) {
        throw new Error('WhatsApp client not connected');
      }

      // Format phone number
      const formattedPhone = this.formatPhoneNumber(recipient);
      
      // Send message
      const result = await client.sendText(`${formattedPhone}@c.us`, message);
      
      return {
        success: true,
        message_id: result.id,
        timestamp: Date.now()
      };
      
    } catch (error) {
      console.error(`Error sending WhatsApp message for customer ${customerId}:`, error);
      throw error;
    }
  }

  async sendDocument(customerId, recipient, documentPath, caption = null) {
    try {
      const client = await this.getClient(customerId);
      
      if (!client.isConnected) {
        throw new Error('WhatsApp client not connected');
      }

      // Format phone number
      const formattedPhone = this.formatPhoneNumber(recipient);
      
      // Send document
      const result = await client.sendFile(`${formattedPhone}@c.us`, documentPath, caption || '');
      
      return {
        success: true,
        message_id: result.id,
        timestamp: Date.now()
      };
      
    } catch (error) {
      console.error(`Error sending WhatsApp document for customer ${customerId}:`, error);
      throw error;
    }
  }

  async getQRCode(customerId) {
    const qrData = this.qrCodes.get(customerId);
    if (qrData) {
      // Check if QR code is still valid (less than 2 minutes old)
      if (Date.now() - qrData.timestamp < 120000) {
        return qrData.qr;
      } else {
        // QR code expired, remove it
        this.qrCodes.delete(customerId);
      }
    }
    
    // Try to initialize client to generate new QR code
    try {
      await this.initializeClient(customerId);
      
      // Wait a bit for QR code to be generated
      await new Promise(resolve => setTimeout(resolve, 3000));
      
      const newQrData = this.qrCodes.get(customerId);
      return newQrData ? newQrData.qr : null;
      
    } catch (error) {
      console.error(`Error generating QR code for customer ${customerId}:`, error);
      return null;
    }
  }

  async logout(customerId) {
    try {
      if (this.clients.has(customerId)) {
        const client = this.clients.get(customerId);
        await client.logout();
        this.clients.delete(customerId);
      }
      
      // Clear QR code
      this.qrCodes.delete(customerId);
      
      // Update database status
      await this.updateSessionStatus(customerId, 'logged_out', null);
      
      // Emit logout event
      this.emit('client:logged_out', {
        customer_id: customerId,
        timestamp: Date.now()
      });
      
    } catch (error) {
      console.error(`Error logging out WhatsApp client for customer ${customerId}:`, error);
      throw error;
    }
  }

  async getSessionStatus(customerId) {
    try {
      const pool = await this.dbManager.getCustomerPool(customerId);
      
      const query = `
        SELECT status, last_updated, qr_code
        FROM settings.whatsapp_sessions
        WHERE customer_id = $1
        ORDER BY last_updated DESC
        LIMIT 1
      `;
      
      const result = await pool.query(query, [customerId]);
      
      if (result.rows.length > 0) {
        return result.rows[0];
      }
      
      return {
        status: 'not_initialized',
        last_updated: null,
        qr_code: null
      };
      
    } catch (error) {
      console.error(`Error getting session status for customer ${customerId}:`, error);
      return {
        status: 'error',
        last_updated: null,
        qr_code: null
      };
    }
  }

  async updateSessionStatus(customerId, status, additionalData = null) {
    try {
      const pool = await this.dbManager.getCustomerPool(customerId);
      
      const query = `
        INSERT INTO settings.whatsapp_sessions (customer_id, status, qr_code, last_updated)
        VALUES ($1, $2, $3, NOW())
        ON CONFLICT (customer_id) 
        DO UPDATE SET 
          status = EXCLUDED.status,
          qr_code = EXCLUDED.qr_code,
          last_updated = NOW()
      `;
      
      await pool.query(query, [customerId, status, additionalData]);
      
    } catch (error) {
      console.error(`Error updating session status for customer ${customerId}:`, error);
    }
  }

  formatPhoneNumber(phone) {
    // Remove all non-digit characters
    let cleaned = phone.replace(/\D/g, '');
    
    // Add country code if not present (assuming South Africa +27)
    if (!cleaned.startsWith('27') && cleaned.length === 9) {
      cleaned = '27' + cleaned;
    }
    
    // Ensure it starts with country code
    if (!cleaned.startsWith('27')) {
      cleaned = '27' + cleaned;
    }
    
    return cleaned;
  }

  async close() {
    // Close all clients
    for (const [customerId, client] of this.clients) {
      try {
        await client.logout();
      } catch (error) {
        console.error(`Error closing WhatsApp client for customer ${customerId}:`, error);
      }
    }
    
    this.clients.clear();
    this.qrCodes.clear();
  }

  /**
   * Restore existing sessions on server startup
   */
  async restoreSessions() {
    console.log('Restoring existing WhatsApp sessions...');
    
    try {
      // Get all customer directories
      const customerDirs = fs.readdirSync(this.sessionDir, { withFileTypes: true })
        .filter(dirent => dirent.isDirectory() && dirent.name.startsWith('customer_'))
        .map(dirent => dirent.name.replace('customer_', ''));

      console.log(`Found ${customerDirs.length} customer session directories:`, customerDirs);

      for (const customerId of customerDirs) {
        try {
          const customerSessionDir = path.join(this.sessionDir, `customer_${customerId}`);
          const sessionFiles = fs.readdirSync(customerSessionDir);
          
          // Check if session files exist
          const hasSessionFiles = sessionFiles.some(file => 
            file.includes('session') || file.includes('tokens') || file.includes('auth')
          );

          if (hasSessionFiles) {
            console.log(`Attempting to restore session for customer ${customerId}`);
            
            // Try to initialize the client (this will attempt to restore the session)
            const client = await this.initializeClient(customerId);
            
            // Wait a bit to see if the session restores
            await new Promise(resolve => setTimeout(resolve, 5000));
            
            if (client && client.isConnected) {
              console.log(`✅ Session restored successfully for customer ${customerId}`);
              await this.updateSessionStatus(customerId, 'connected');
            } else {
              console.log(`❌ Session restoration failed for customer ${customerId} - will need QR code`);
              await this.updateSessionStatus(customerId, 'disconnected');
            }
          }
        } catch (error) {
          console.error(`Error restoring session for customer ${customerId}:`, error);
        }
      }
    } catch (error) {
      console.error('Error during session restoration:', error);
    }
  }
}

module.exports = WhatsAppWebManager; 