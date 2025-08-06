const express = require('express');
const http = require('http');
const socketIo = require('socket.io');
const cors = require('cors');
const helmet = require('helmet');
const jwt = require('jsonwebtoken');
const winston = require('winston');
require('dotenv').config();

// Import our modules
const DatabaseManager = require('./lib/DatabaseManager');
const EventProcessor = require('./lib/EventProcessor');
const EmailService = require('./lib/EmailService');
const WhatsAppService = require('./lib/WhatsAppService');
const WebhookService = require('./lib/WebhookService');
const AuthMiddleware = require('./lib/AuthMiddleware');
const RateLimiter = require('./lib/RateLimiter');

// Configure logging
const logger = winston.createLogger({
  level: 'info',
  format: winston.format.combine(
    winston.format.timestamp(),
    winston.format.errors({ stack: true }),
    winston.format.json()
  ),
  defaultMeta: { service: 'websocket-server' },
  transports: [
    new winston.transports.File({ filename: 'logs/error.log', level: 'error' }),
    new winston.transports.File({ filename: 'logs/combined.log' }),
    new winston.transports.Console({
      format: winston.format.simple()
    })
  ]
});

class WebSocketServer {
  constructor() {
    this.app = express();
    this.server = http.createServer(this.app);
    this.io = socketIo(this.server, {
      cors: {
        origin: process.env.ALLOWED_ORIGINS?.split(',') || ["http://localhost:3000"],
        methods: ["GET", "POST"],
        credentials: true
      }
    });
    
    this.dbManager = new DatabaseManager();
    this.eventProcessor = new EventProcessor(this.dbManager);
    this.emailService = new EmailService(this.dbManager);
    this.whatsappService = new WhatsAppService(this.dbManager);
    this.webhookService = new WebhookService(this.dbManager);
    this.authMiddleware = new AuthMiddleware();
    this.rateLimiter = new RateLimiter();
    
    this.setupMiddleware();
    this.setupSocketHandlers();
    this.setupEventListeners();
    this.startEventProcessing();
  }
  
  setupMiddleware() {
    // Security middleware
    this.app.use(helmet());
    this.app.use(cors({
      origin: process.env.ALLOWED_ORIGINS?.split(',') || ["http://localhost:3000"],
      credentials: true
    }));
    
    // Body parsing
    this.app.use(express.json({ limit: '10mb' }));
    this.app.use(express.urlencoded({ extended: true }));
    
    // Health check endpoint
    this.app.get('/health', (req, res) => {
      res.json({ status: 'ok', timestamp: new Date().toISOString() });
    });
    
    // Authentication endpoint for getting connection token
    this.app.post('/auth/token', async (req, res) => {
      try {
        const { customer_id, user_id, session_id } = req.body;
        
        if (!customer_id || !user_id || !session_id) {
          return res.status(400).json({ error: 'Missing required fields' });
        }
        
        // Verify session in database
        const isValidSession = await this.authMiddleware.verifySession(customer_id, user_id, session_id);
        
        if (!isValidSession) {
          return res.status(401).json({ error: 'Invalid session' });
        }
        
        // Generate connection token
        const token = jwt.sign(
          { customer_id, user_id, session_id },
          process.env.JWT_SECRET || 'your-secret-key',
          { expiresIn: '1h' }
        );
        
        res.json({ token });
      } catch (error) {
        logger.error('Token generation error:', error);
        res.status(500).json({ error: 'Internal server error' });
      }
    });

    // WhatsApp QR Code endpoints
    this.app.get('/whatsapp/qr/:customerId', async (req, res) => {
      try {
        const { customerId } = req.params;
        
        // Verify customer exists and user has access
        const isValidSession = await this.authMiddleware.verifySession(customerId, req.query.user_id, req.query.session_id);
        if (!isValidSession) {
          return res.status(401).json({ error: 'Unauthorized' });
        }
        
        const qrCode = await this.whatsappService.getQRCode(customerId);
        
        if (qrCode) {
          res.json({ qr_code: qrCode });
        } else {
          res.status(404).json({ error: 'QR code not available' });
        }
      } catch (error) {
        logger.error('QR code generation error:', error);
        res.status(500).json({ error: 'Internal server error' });
      }
    });

    this.app.get('/whatsapp/status/:customerId', async (req, res) => {
      try {
        const { customerId } = req.params;
        
        // Verify customer exists and user has access
        const isValidSession = await this.authMiddleware.verifySession(customerId, req.query.user_id, req.query.session_id);
        if (!isValidSession) {
          return res.status(401).json({ error: 'Unauthorized' });
        }
        
        const status = await this.whatsappService.getSessionStatus(customerId);
        res.json(status);
      } catch (error) {
        logger.error('Status check error:', error);
        res.status(500).json({ error: 'Internal server error' });
      }
    });

    this.app.post('/whatsapp/logout/:customerId', async (req, res) => {
      try {
        const { customerId } = req.params;
        
        // Verify customer exists and user has access
        const isValidSession = await this.authMiddleware.verifySession(customerId, req.body.user_id, req.body.session_id);
        if (!isValidSession) {
          return res.status(401).json({ error: 'Unauthorized' });
        }
        
        await this.whatsappService.logout(customerId);
        res.json({ success: true, message: 'Logged out successfully' });
      } catch (error) {
        logger.error('Logout error:', error);
        res.status(500).json({ error: 'Internal server error' });
      }
    });

    // WhatsApp document sending endpoint
    this.app.post('/whatsapp/send-document/:customerId', async (req, res) => {
      try {
        const { customerId } = req.params;
        const { recipient, document_path, caption, user_id, session_id } = req.body;
        
        console.log('Document sending request body:', req.body);
        console.log('Extracted parameters:', { customerId, recipient, document_path, caption, user_id, session_id });
        
        // Verify customer exists and user has access
        const isValidSession = await this.authMiddleware.verifySession(customerId, user_id, session_id);
        if (!isValidSession) {
          return res.status(401).json({ error: 'Unauthorized' });
        }
        
        // Validate required fields
        if (!recipient || !document_path) {
          return res.status(400).json({ error: 'Recipient and document_path are required' });
        }
        
        // Check if document file exists
        if (!require('fs').existsSync(document_path)) {
          return res.status(404).json({ error: 'Document file not found' });
        }
        
        // Send document via WhatsApp
        const result = await this.whatsappService.sendDocument(customerId, recipient, document_path, caption);
        
        if (result.success) {
          res.json({
            success: true,
            message: 'Document sent successfully',
            message_id: result.message_id,
            timestamp: result.timestamp
          });
        } else {
          res.status(500).json({
            success: false,
            message: result.message || 'Failed to send document',
            error: result.error
          });
        }
        
      } catch (error) {
        logger.error('Document sending error:', error);
        res.status(500).json({ error: 'Internal server error' });
      }
    });
  }
  
  setupSocketHandlers() {
    // Authentication middleware for socket connections
    this.io.use(async (socket, next) => {
      try {
        const token = socket.handshake.auth.token;
        
        if (!token) {
          return next(new Error('Authentication token required'));
        }
        
        const decoded = jwt.verify(token, process.env.JWT_SECRET || 'your-secret-key');
        socket.customer_id = decoded.customer_id;
        socket.user_id = decoded.user_id;
        socket.session_id = decoded.session_id;
        
        // Rate limiting check
        const rateLimitResult = await this.rateLimiter.checkLimit(socket.customer_id, socket.user_id);
        if (!rateLimitResult.allowed) {
          return next(new Error('Rate limit exceeded'));
        }
        
        next();
      } catch (error) {
        logger.error('Socket authentication error:', error);
        next(new Error('Authentication failed'));
      }
    });
    
    // Connection handler
    this.io.on('connection', async (socket) => {
      logger.info(`Client connected: ${socket.id} (Customer: ${socket.customer_id}, User: ${socket.user_id})`);
      
      // Join customer-specific room
      socket.join(`customer:${socket.customer_id}`);
      socket.join(`user:${socket.user_id}`);
      
      // Track connection in database
      await this.dbManager.trackConnection(socket.customer_id, socket.user_id, socket.session_id, socket.id, socket.handshake.address, socket.handshake.headers['user-agent']);
      
      // Handle internal events from client
      socket.on('internal:event', async (data) => {
        try {
          await this.handleInternalEvent(socket, data);
        } catch (error) {
          logger.error('Error handling internal event:', error);
          socket.emit('error', { message: 'Failed to process event' });
        }
      });
      
      // Handle disconnect
      socket.on('disconnect', async (reason) => {
        logger.info(`Client disconnected: ${socket.id} (Reason: ${reason})`);
        await this.dbManager.trackDisconnection(socket.id);
      });
    });
  }
  
  async handleInternalEvent(socket, data) {
    const { event_type, event_data } = data;
    
    logger.info(`Processing internal event: ${event_type} for customer ${socket.customer_id}`);
    
    // Store event in database
    const eventId = await this.dbManager.storeEvent(socket.customer_id, event_type, event_data);
    
    // Process event asynchronously
    this.eventProcessor.processEvent(eventId, socket.customer_id, event_type, event_data)
      .then(() => {
        logger.info(`Event ${eventId} processed successfully`);
      })
      .catch((error) => {
        logger.error(`Event ${eventId} processing failed:`, error);
      });
    
    // Send immediate acknowledgment
    socket.emit('event:acknowledged', { event_id: eventId, event_type });
  }
  
  setupEventListeners() {
    // Listen for event processing updates
    this.eventProcessor.on('event:progress', (data) => {
      this.io.to(`customer:${data.customer_id}`).emit('event:progress', data);
    });
    
    this.eventProcessor.on('event:completed', (data) => {
      this.io.to(`customer:${data.customer_id}`).emit('event:completed', data);
    });
    
    this.eventProcessor.on('event:failed', (data) => {
      this.io.to(`customer:${data.customer_id}`).emit('event:failed', data);
    });
    
    // Listen for message delivery updates
    this.emailService.on('message:sent', (data) => {
      this.io.to(`customer:${data.customer_id}`).emit('message:sent', data);
    });
    
    this.whatsappService.on('message:sent', (data) => {
      this.io.to(`customer:${data.customer_id}`).emit('message:sent', data);
    });

    // Listen for WhatsApp Web events
    this.whatsappService.on('qr:generated', (data) => {
      this.io.to(`customer:${data.customer_id}`).emit('whatsapp:qr_generated', data);
    });

    this.whatsappService.on('client:ready', (data) => {
      this.io.to(`customer:${data.customer_id}`).emit('whatsapp:client_ready', data);
    });

    this.whatsappService.on('client:authenticated', (data) => {
      this.io.to(`customer:${data.customer_id}`).emit('whatsapp:client_authenticated', data);
    });

    this.whatsappService.on('client:auth_failed', (data) => {
      this.io.to(`customer:${data.customer_id}`).emit('whatsapp:client_auth_failed', data);
    });

    this.whatsappService.on('client:disconnected', (data) => {
      this.io.to(`customer:${data.customer_id}`).emit('whatsapp:client_disconnected', data);
    });

    this.whatsappService.on('client:logged_out', (data) => {
      this.io.to(`customer:${data.customer_id}`).emit('whatsapp:client_logged_out', data);
    });
  }
  
  startEventProcessing() {
    // Start background processing of pending events
    setInterval(async () => {
      try {
        await this.eventProcessor.processPendingEvents();
      } catch (error) {
        logger.error('Error processing pending events:', error);
      }
    }, 5000); // Check every 5 seconds
    
    // Clean up old connections
    setInterval(async () => {
      try {
        await this.dbManager.cleanupOldConnections();
      } catch (error) {
        logger.error('Error cleaning up connections:', error);
      }
    }, 60000); // Every minute
  }
  
  async start(port = process.env.PORT || 3001) {
    try {
      // Initialize WhatsApp service
      this.whatsappService = new WhatsAppService(this.dbManager);
      
      // Setup middleware and routes
      this.setupMiddleware();
      
      // Setup WebSocket handlers
      this.setupSocketHandlers();
      
      // Start HTTP server
      this.server.listen(port, () => {
        logger.info(`WebSocket server running on port ${port}`);
      });
      
      // Start event processing
      this.startEventProcessing();
      
      // Restore existing WhatsApp sessions
      setTimeout(async () => {
        try {
          await this.whatsappService.webManager.restoreSessions();
        } catch (error) {
          logger.error('Error restoring WhatsApp sessions:', error);
        }
      }, 5000); // Wait 5 seconds after server starts
      
    } catch (error) {
      logger.error('Failed to start WebSocket server:', error);
      process.exit(1);
    }
  }
}

// Start the server
const server = new WebSocketServer();
server.start().catch(error => {
  logger.error('Failed to start server:', error);
  process.exit(1);
});

// Graceful shutdown
process.on('SIGTERM', () => {
  logger.info('SIGTERM received, shutting down gracefully');
  server.server.close(() => {
    logger.info('Process terminated');
    process.exit(0);
  });
});

process.on('SIGINT', () => {
  logger.info('SIGINT received, shutting down gracefully');
  server.server.close(() => {
    logger.info('Process terminated');
    process.exit(0);
  });
}); 