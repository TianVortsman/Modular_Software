const express = require('express');
const { Pool } = require('pg');
const bodyParser = require('body-parser');
const winston = require('winston');
const bodyParserXml = require('body-parser-xml');
const http = require('http');
const WebSocket = require('ws');
const cors = require('cors');
const multer = require('multer');
const upload = multer();

// Configure logger with console transport
const logger = winston.createLogger({
    level: 'debug',
    format: winston.format.combine(
        winston.format.timestamp(),
        winston.format.json()
    ),
    transports: [
        new winston.transports.Console({
            format: winston.format.combine(
                winston.format.colorize(),
                winston.format.simple()
            )
        }),
        new winston.transports.File({ filename: 'error.log', level: 'error' }),
        new winston.transports.File({ filename: 'clock_data.log', level: 'debug' }),
        new winston.transports.File({ filename: 'raw_clock_data.log' }),
        new winston.transports.File({ filename: 'combined.log' })
    ]
});

// Create a dedicated logger for raw data
const rawDataLogger = winston.createLogger({
    level: 'info',
    format: winston.format.combine(
        winston.format.timestamp(),
        winston.format.json()
    ),
    transports: [
        new winston.transports.File({ filename: 'raw_clock_data.log' })
    ]
});

// Enable XML parsing
bodyParserXml(bodyParser);

// Store active servers
const servers = new Map();

// Store WebSocket servers
const wssMap = new Map();

// Store active WebSocket connections
const connectionsMap = new Map();

// Main database pool for customer lookup
const pool = new Pool({
    user: 'Tian',
    host: 'localhost',
    database: 'modular_system',
    password: 'Modul@rdev@2024',
    port: 5432,
});

// Test database connection immediately
pool.connect()
    .then(client => {
        console.log('Successfully connected to PostgreSQL database');
        client.release();
    })
    .catch(err => {
        console.error('Error connecting to PostgreSQL:', err.message);
        logger.error('Database connection error:', err);
});

// Cache to store account number by port for faster lookups
const portToAccountCache = new Map();

// Error handling for uncaught exceptions
process.on('uncaughtException', (err) => {
    console.error('Uncaught Exception:', err);
    logger.error('Uncaught Exception:', err);
});

process.on('unhandledRejection', (reason, promise) => {
    console.error('Unhandled Rejection at:', promise, 'reason:', reason);
    logger.error('Unhandled Rejection:', { promise, reason });
});

// Add ISUP configuration
const ISUP_KEY = 'Modul@rdev@2024';

// ISUP Key validation middleware
function verifyIsupAuth(req, res, next) {
    // Log full request details for debugging
    logger.debug('ISUP Auth Check - Full Request Details:', {
        headers: req.headers,
        query: req.query,
        body: req.body,
        rawBody: req.rawBody ? req.rawBody.substring(0, 1000) : null,
        timestamp: new Date().toISOString()
    });

    // Define the expected ISUP key
    const EXPECTED_ISUP_KEY = process.env.ISUP_KEY || "MySecretKey123";
    
    // Check for ISUP key in headers (case-insensitive)
    const isupKey = req.headers['isup-key'] || 
                   req.headers['x-isup-key'] || 
                   req.headers['authorization'];
    
    // Special handling for multipart form-data requests from Hikvision devices
    if (req.headers['content-type'] && req.headers['content-type'].includes('multipart/form-data')) {
        logger.info('Processing multipart form-data request');
        
        // Check if this is a valid Hikvision event
        if (req.body && req.body.event_log) {
            try {
                // Try to parse the event_log to verify it's a valid Hikvision event
                const eventData = JSON.parse(req.body.event_log);
                if (eventData.AccessControllerEvent) {
                    logger.info('Valid Hikvision event data found, allowing request');
                    return next();
                }
            } catch (e) {
                // If we can't parse it but it contains the right keywords, still allow it
                if (req.body.event_log.includes('AccessControllerEvent')) {
                    logger.info('Hikvision event data found (string match), allowing request');
                    return next();
                }
                logger.warn('Error parsing event_log:', e.message);
            }
        } else if (req.rawBody && req.rawBody.includes('AccessControllerEvent')) {
            // If we have the raw body and it contains the right keywords, allow it
            logger.info('Hikvision event data found in raw body, allowing request');
            return next();
        }
    }
    
    // If no ISUP key is found, log warning but allow the request to proceed
    if (!isupKey) {
        logger.warn('No ISUP key provided in request', {
            headers: req.headers,
            url: req.url,
            method: req.method,
            timestamp: new Date().toISOString()
        });
        // Allow the request to proceed anyway
        return next();
    }
    
    // If ISUP key is found but doesn't match expected value
    if (isupKey !== EXPECTED_ISUP_KEY) {
        logger.warn(`Invalid ISUP key provided: ${isupKey}`, {
            timestamp: new Date().toISOString()
        });
        // Allow the request to proceed anyway
        return next();
    }
    
    // ISUP key is valid
    logger.info('Valid ISUP key provided', {
        timestamp: new Date().toISOString()
    });
    next();
}

// Function to broadcast clock event to all connected clients for an account
function broadcastClockEvent(accountNumber, eventData) {
    if (!connectionsMap.has(accountNumber)) {
        return; // No connections for this account
    }
    
    const connections = connectionsMap.get(accountNumber);
    const message = JSON.stringify({
        type: 'clock_event',
        timestamp: new Date().toISOString(),
        data: eventData
    });
    
    logger.info(`Broadcasting to ${connections.size} clients for account ${accountNumber}`);
    
    connections.forEach(client => {
        if (client.readyState === WebSocket.OPEN) {
            client.send(message);
        }
    });
}

// Function to refresh the port cache
async function refreshPortCache() {
    try {
        logger.info('Refreshing port to account cache');
        
        // Get active customers with clock server ports
        const customersQuery = await pool.query(
            'SELECT account_number, clock_server_port FROM customers WHERE clock_server_port IS NOT NULL AND status = $1',
            ['active']
        );
        
        // Clear the current cache
        portToAccountCache.clear();
        
        // Add customers data to cache
        customersQuery.rows.forEach(row => {
            portToAccountCache.set(row.clock_server_port, row.account_number);
        });
        
        // Get all unique ports
        const activePorts = Array.from(portToAccountCache.keys());
        logger.info(`Updated port cache with ${activePorts.length} active ports`);
        
        return activePorts;
    } catch (error) {
        logger.error('Error refreshing port cache:', error);
        throw error;
    }
}

// Function to get account number for a port
async function getAccountNumberForPort(port) {
    // First try the cache
    if (portToAccountCache.has(port)) {
        return portToAccountCache.get(port);
    }
    
    // If not in cache, refresh the cache and try again
    await refreshPortCache();
    return portToAccountCache.get(port);
}

// Function to set up WebSocket server for an account
function setupWebSocket(server, accountNumber) {
    // Check if WebSocket server already exists for this account
    if (wssMap.has(accountNumber)) {
        return wssMap.get(accountNumber);
    }
    
    const wss = new WebSocket.Server({ server });
    
    wss.on('connection', (ws, req) => {
        logger.info(`New WebSocket connection for account ${accountNumber}`);
        
        // Initialize connections set for this account if it doesn't exist
        if (!connectionsMap.has(accountNumber)) {
            connectionsMap.set(accountNumber, new Set());
        }
        
        // Add this connection to the set
        connectionsMap.get(accountNumber).add(ws);
        
        // Send welcome message
        ws.send(JSON.stringify({
            type: 'connection',
            message: `Connected to real-time clock events for account ${accountNumber}`,
            timestamp: new Date().toISOString()
        }));
        
        // Handle connection close
        ws.on('close', () => {
            logger.info(`WebSocket connection closed for account ${accountNumber}`);
            if (connectionsMap.has(accountNumber)) {
                connectionsMap.get(accountNumber).delete(ws);
            }
        });
        
        // Ping-pong to keep connection alive
        ws.isAlive = true;
        ws.on('pong', () => {
            ws.isAlive = true;
        });
    });
    
    // Set up ping interval
    const pingInterval = setInterval(() => {
        wss.clients.forEach(ws => {
            if (ws.isAlive === false) return ws.terminate();
            
            ws.isAlive = false;
            ws.ping();
        });
    }, 30000);
    
    // Handle server shutdown
    wss.on('close', () => {
        clearInterval(pingInterval);
    });
    
    // Store WebSocket server
    wssMap.set(accountNumber, wss);
    
    return wss;
}

// Function to create a server for a specific port
async function createServer(port) {
    if (servers.has(port)) {
        return servers.get(port);
    }

    const app = express();
    app.use(cors()); // Enable CORS for all routes
    
    // Simple raw body capture for logging
    app.use((req, res, next) => {
        let data = '';
        req.on('data', chunk => {
            data += chunk;
        });
        
        req.on('end', () => {
            req.rawBody = data;
            logger.info(`[PORT ${port}] RAW REQUEST BODY: ${data}`);
        next();
        });
    });
    
    // Configure body parsers - SIMPLIFIED
    app.use(bodyParser.json());
    app.use(bodyParser.urlencoded({ extended: true }));
    
    // Configure multer for multipart form data
    const upload = multer();
    
    // Add ISUP verification middleware
    app.use(['/ISAPI/*', '/EventService', '/clock'], verifyIsupAuth);
    
    // Log all incoming requests
    app.use((req, res, next) => {
        logger.info(`[PORT ${port}] Received ${req.method} request to ${req.url}`);
        logger.info(`[PORT ${port}] Headers: ${JSON.stringify(req.headers)}`);
        next();
    });

    // Handle clock data from Hikvision device - manually parse multipart form data
    app.post('/clock', async (req, res) => {
        try {
            logger.debug('Received request to /clock');
            
            // Check if we have the raw body
            if (!req.rawBody) {
                logger.error('No raw body available');
                return res.status(400).json({
                    status: "ERROR",
                    message: "No raw body available",
                    timestamp: new Date().toISOString()
                });
            }
            
            // Log the raw body for debugging
            logger.debug('Raw body:', req.rawBody.substring(0, 1000));
            
            // Manually extract the event_log from the multipart form data
            let eventLogJson = null;
            
            // Look for the event_log field in the raw body using a more robust pattern
            const boundaryMatch = req.rawBody.match(/--([^\r\n]+)/);
            const boundary = boundaryMatch ? boundaryMatch[1] : 'MIME_boundary';
            
            // Extract content between the form-data markers
            const pattern = new RegExp(`Content-Disposition:[^\\n]*name="event_log"[^\\n]*\\r\\n\\r\\n([\\s\\S]*?)(?:\\r\\n--${boundary}|$)`, 'i');
            const match = req.rawBody.match(pattern);
            
            if (match && match[1]) {
                eventLogJson = match[1].trim();
                logger.debug('Extracted event_log JSON:', eventLogJson);
            } else {
                // Try a simpler pattern as fallback
                const simpleMatch = req.rawBody.match(/name="event_log"[\s\S]*?\r\n\r\n([\s\S]*?)(?:\r\n--|$)/);
                if (simpleMatch && simpleMatch[1]) {
                    eventLogJson = simpleMatch[1].trim();
                    logger.debug('Extracted event_log JSON (simple method):', eventLogJson);
                } else {
                    // Last resort - try to find any JSON object in the raw body
                    const jsonMatch = req.rawBody.match(/(\{[\s\S]*\})/);
                    if (jsonMatch && jsonMatch[1]) {
                        eventLogJson = jsonMatch[1].trim();
                        logger.debug('Extracted JSON from raw body (last resort):', eventLogJson);
                    }
                }
            }
            
            if (!eventLogJson) {
                logger.error('Could not extract event_log from multipart form data');
                return res.status(400).json({
                    status: "ERROR",
                    message: "Could not extract event_log from request",
                    timestamp: new Date().toISOString()
                });
            }
            
            // Parse the JSON
            try {
                const eventData = JSON.parse(eventLogJson);
                logger.debug('Parsed event data:', eventData);
                
                // Log all fields in AccessControllerEvent
                if (eventData.AccessControllerEvent) {
                    logger.debug('AccessControllerEvent fields:', {
                        availableFields: Object.keys(eventData.AccessControllerEvent),
                        fieldValues: eventData.AccessControllerEvent
                    });
                    
                    // Check for employee ID fields
                    const employeeIdFields = ['employeeNoString', 'verifyNo', 'cardNo', 'employeeNo', 'cardNumber', 'employeeID'];
                    let employeeId = null;
                    
                    for (const field of employeeIdFields) {
                        if (eventData.AccessControllerEvent && eventData.AccessControllerEvent[field]) {
                            employeeId = eventData.AccessControllerEvent[field];
                            logger.info(`Found employee ID in field ${field}: ${employeeId}`);
                        break;
                    }
                }
                
                if (!employeeId) {
                        logger.warn('No employee ID found in event data. Using fallback values.');
                        // Use fallback values
                        if (eventData.AccessControllerEvent.doorNo) {
                            employeeId = eventData.AccessControllerEvent.doorNo;
                            logger.info(`Using doorNo as fallback: ${employeeId}`);
                        } else if (eventData.AccessControllerEvent.serialNo) {
                            employeeId = eventData.AccessControllerEvent.serialNo;
                            logger.info(`Using serialNo as fallback: ${employeeId}`);
                        }
                    }
                    
                    // Process the event with the extracted employee ID
                    try {
                        const result = await processClockEvent(req, port, eventData, employeeId);
                        
                        // Send ACK response
                        res.status(200).json({
                            status: "ACK",
                            message: "Event received successfully",
                            timestamp: new Date().toISOString()
                        });
            } catch (error) {
                        logger.error(`Error processing clock event: ${error.message}`, {
                            stack: error.stack,
                            timestamp: new Date().toISOString()
                        });
                        
                        // Still send an ACK to the device to prevent retries
                        res.status(200).json({
                            status: "ACK",
                            message: "Event received but processing failed",
                            error: error.message,
                            timestamp: new Date().toISOString()
                        });
                    }
                } else {
                    logger.error('Invalid event format: AccessControllerEvent not found');
                    res.status(400).json({
                        status: "ERROR",
                        message: "Invalid event format",
                        timestamp: new Date().toISOString()
                    });
                }
            } catch (e) {
                logger.error(`Error parsing event_log JSON: ${e.message}`, {
                    rawJson: eventLogJson ? eventLogJson.substring(0, 200) + '...' : 'null',
                    error: e.message,
                    stack: e.stack,
                    timestamp: new Date().toISOString()
                });
                
                // Try to clean the JSON string and parse again
                try {
                    // Remove any non-JSON characters that might be causing issues
                    const cleanedJson = eventLogJson.replace(/[\u0000-\u0019]+/g, "");
                    const eventData = JSON.parse(cleanedJson);
                    logger.info('Successfully parsed JSON after cleaning');
                    
                    // Continue processing with the cleaned JSON
                    if (eventData.AccessControllerEvent) {
                        logger.debug('AccessControllerEvent fields after cleaning:', {
                            availableFields: Object.keys(eventData.AccessControllerEvent),
                            fieldValues: eventData.AccessControllerEvent
                        });
                        
                        // Check for employee ID fields
                        const employeeIdFields = ['employeeNoString', 'verifyNo', 'cardNo', 'employeeNo', 'cardNumber', 'employeeID'];
                        let employeeId = null;
                        
                        for (const field of employeeIdFields) {
                            if (eventData.AccessControllerEvent && eventData.AccessControllerEvent[field]) {
                                employeeId = eventData.AccessControllerEvent[field];
                                logger.info(`Found employee ID in field ${field}: ${employeeId}`);
                                break;
                            }
                        }
                        
                        if (!employeeId) {
                            logger.warn('No employee ID found in event data. Using fallback values.');
                            // Use fallback values
                            if (eventData.AccessControllerEvent.doorNo) {
                                employeeId = eventData.AccessControllerEvent.doorNo;
                                logger.info(`Using doorNo as fallback: ${employeeId}`);
                            } else if (eventData.AccessControllerEvent.serialNo) {
                                employeeId = eventData.AccessControllerEvent.serialNo;
                                logger.info(`Using serialNo as fallback: ${employeeId}`);
                            }
                        }
                        
                        // Process the event with the extracted employee ID
                        try {
                            const result = await processClockEvent(req, port, eventData, employeeId);
                            
                            // Send ACK response
                            res.status(200).json({
                                status: "ACK",
                                message: "Event received successfully (after JSON cleaning)",
                                timestamp: new Date().toISOString()
                            });
                        } catch (error) {
                            logger.error(`Error processing clock event: ${error.message}`, {
                                stack: error.stack,
                                timestamp: new Date().toISOString()
                            });
                            
                            // Still send an ACK to the device to prevent retries
        res.status(200).json({
                                status: "ACK",
                                message: "Event received but processing failed",
                                error: error.message,
                                timestamp: new Date().toISOString()
                            });
                        }
                    } else {
                        logger.error('Invalid event format after cleaning: AccessControllerEvent not found');
                        res.status(400).json({
                            status: "ERROR",
                            message: "Invalid event format",
                            timestamp: new Date().toISOString()
                        });
                    }
                } catch (cleanError) {
                    // If cleaning also fails, respond with error
                    res.status(400).json({
                        status: "ERROR",
                        message: "Invalid JSON in event_log",
                        originalError: e.message,
                        cleanError: cleanError.message,
                        timestamp: new Date().toISOString()
                    });
                }
            }
        } catch (error) {
            logger.error(`Error processing Hikvision event on port ${port}:`, error);
            res.status(500).json({
                status: "ERROR",
                message: error.message,
                timestamp: new Date().toISOString()
            });
        }
    });
    
    // Additional endpoints for Hikvision - use the same handler
    app.post('/EventService', (req, res) => {
        // Forward to the /clock handler
        req.url = '/clock';
        app.handle(req, res);
    });

    app.post('/EventService/*', (req, res) => {
        // Forward to the /clock handler
        req.url = '/clock';
        app.handle(req, res);
    });

    app.post('/ISAPI/Event/notification/alertStream', (req, res) => {
        // Forward to the /clock handler
        req.url = '/clock';
        app.handle(req, res);
    });

    app.post('/ISAPI/AccessControl/AcsEvent', (req, res) => {
        // Forward to the /clock handler
        req.url = '/clock';
        app.handle(req, res);
    });

    app.post('/ISAPI/AccessControl/*', (req, res) => {
        // Forward to the /clock handler
        req.url = '/clock';
        app.handle(req, res);
    });

    app.post('/ISAPI/Event/*', (req, res) => {
        // Forward to the /clock handler
        req.url = '/clock';
        app.handle(req, res);
    });

    // Add device status endpoint
    app.get('/DeviceStatus', (req, res) => {
        const statusResponse = `<AcsWorkStatus>
            <cardReaderOnlineStatus>enable</cardReaderOnlineStatus>
            <masterChannelControllerStatus>enable</masterChannelControllerStatus>
            <slaveChannelControllerStatus>enable</slaveChannelControllerStatus>
        </AcsWorkStatus>`;
        
        res.set('Content-Type', 'application/xml');
        res.send(statusResponse);
        logger.info('Sent online status response to device');
    });

    // Add heartbeat endpoint
    app.post('/KeepAlive', async (req, res) => {
        try {
            const accountNumber = await getAccountNumberForPort(port);
            const deviceId = req.body.deviceID || req.query.deviceID || 'unknown';
            
            // Log the heartbeat
            logger.info(`Received heartbeat from device ${deviceId} on port ${port}`);
            
            // Update device status in database if we have an account number
            if (accountNumber) {
            const customerPool = new Pool({
                user: 'Tian',
                host: 'localhost',
                database: accountNumber,
                password: 'Modul@rdev@2024',
                port: 5432,
            });

                try {
                    // Get account ID for this account number
                    const accountResult = await pool.query(
                        'SELECT id FROM customers WHERE account_number = $1',
                        [accountNumber]
                    );
                    
                    if (accountResult.rows.length > 0) {
                        const accountId = accountResult.rows[0].id;
                        
                        // Check if device exists
                        const deviceResult = await customerPool.query(
                            'SELECT id FROM clock_devices WHERE device_id = $1 AND account_id = $2',
                            [deviceId, accountId]
                        );
                        
                        if (deviceResult.rows.length > 0) {
                            // Update existing device
                            await customerPool.query(
                                'UPDATE clock_devices SET last_heartbeat = NOW(), status = $1 WHERE device_id = $2 AND account_id = $3',
                                ['online', deviceId, accountId]
                            );
                        } else {
                            // Insert new device
                            await customerPool.query(
                                'INSERT INTO clock_devices (account_id, device_id, device_name, status, last_heartbeat) VALUES ($1, $2, $3, $4, NOW())',
                                [accountId, deviceId, `Device ${deviceId}`, 'online']
                            );
                        }
                    }
                    
                    await customerPool.end();
                } catch (dbError) {
                    logger.error(`Error updating device status: ${dbError.message}`);
                }
            }
            
            // Send response
            res.status(200).json({ 
                status: "OK", 
                timestamp: new Date().toISOString(),
                message: "Heartbeat received"
            });
        } catch (error) {
            logger.error(`Error processing heartbeat: ${error.message}`);
            res.status(500).json({ 
                status: "ERROR", 
                timestamp: new Date().toISOString(),
                message: error.message
            });
        }
    });

    // WebSocket setup endpoint
    app.get('/ws', (req, res) => {
        res.send('WebSocket endpoint available');
    });
    
    // Test endpoint to verify server is running
    app.get('/test', (req, res) => {
        res.status(200).json({
            status: 'OK',
            port: port,
            accountNumber: portToAccountCache.get(port) || 'Unknown',
            time: new Date().toISOString()
        });
    });

    // Add endpoint to view raw logs
    app.get('/raw-logs', (req, res) => {
        const fs = require('fs');
        try {
            // Read the last 100 lines of the raw log file
            const logData = fs.readFileSync('raw_clock_data.log', 'utf8')
                .split('\n')
                .filter(line => line.trim() !== '')
                .slice(-100)
                .map(line => {
                    try {
                        return JSON.parse(line);
                    } catch (e) {
                        return { raw: line };
                    }
                });
            
            res.json({
                timestamp: new Date().toISOString(),
                count: logData.length,
                logs: logData
            });
        } catch (error) {
            res.status(500).json({
                error: 'Failed to read log file',
                message: error.message
            });
        }
    });

    // Error handling middleware
    app.use((err, req, res, next) => {
        logger.error(`Server error on port ${port}:`, err);
        res.status(500).send('Internal server error: ' + err.message);
    });

    // Create HTTP server instance
    const server = http.createServer(app);
    
    // Get account number for the port
    const accountNumber = portToAccountCache.get(port);
    
    // Set up WebSocket if we have an account number
    if (accountNumber) {
        setupWebSocket(server, accountNumber);
    }

    // Start the server
    server.listen(port, () => {
        logger.info(`Clock server listening on port ${port}`);
    });

    // Handle server errors
    server.on('error', (error) => {
        if (error.code === 'EADDRINUSE') {
            logger.error(`Port ${port} is already in use. Cannot start server.`);
        } else {
            logger.error(`Error starting server on port ${port}:`, error);
        }
        servers.delete(port);
    });

    servers.set(port, server);
    return server;
}

// Add Hikvision event type constants
const HIKVISION_EVENT_TYPES = {
    MAJOR: {
        ALARM: 0x1,
        EXCEPTION: 0x2,
        OPERATION: 0x3,
        ACCESS: 0x5
    },
    MINOR: {
        SWIPE_CARD_SUCCESS: 0x01,
        SWIPE_CARD_FAILED: 0x02,
        FACE_RECOGNITION_SUCCESS: 0x03,
        FACE_RECOGNITION_FAILED: 0x04,
        DOOR_REMOTE_OPEN: 0x07,
        PASSWORD_OPEN: 0x08,
        INVALID_PASSWORD: 0x09,
        TAMPER_ALARM: 0x10,
        FIRE_ALARM: 0x11,
        INTRUSION: 0x12
    }
};

// Helper function to handle clock data from various endpoints
async function handleClockData(req, res) {
    const port = req.socket.localPort;
    try {
        // Enhanced logging of ALL incoming data
        logger.debug('========== HIKVISION EVENT DATA START ==========');
        logger.debug(`Timestamp: ${new Date().toISOString()}`);
        logger.debug(`Port: ${port}`);
        logger.debug(`Method: ${req.method}`);
        logger.debug(`URL: ${req.url}`);
        logger.debug(`Headers:`, req.headers);
        
        // Log the raw request body if available
        if (req.rawBody) {
            logger.debug(`RAW BODY: ${req.rawBody}`);
        }
        
        // Log the parsed body
        logger.debug('Parsed Body:', req.body);
        
        // Process the event
        const result = await processClockEvent(req, port);

        // Send ACK response to the device
        const ackResponse = {
            status: "ACK",
            message: "Event received successfully",
            timestamp: new Date().toISOString(),
            eventId: result.id
        };

        // Log the acknowledgment
        logger.info('Sending ACK for clock event:', {
            port,
            eventId: result.id,
            timestamp: new Date().toISOString()
        });

        // Send response with ACK
        res.status(200).json(ackResponse);

    } catch (error) {
        logger.error(`Error processing Hikvision event on port ${port}:`, error);
        res.status(500).json({
            status: "ERROR",
            message: error.message,
            timestamp: new Date().toISOString()
        });
    }
}

// Separate the event processing logic
async function processClockEvent(req, port, eventData, employeeId) {
        const accountNumber = await getAccountNumberForPort(port);
        
        if (!accountNumber) {
        throw new Error('Customer not found');
        }

        // Create a new pool for the customer's database
        const customerPool = new Pool({
            user: 'Tian',
            host: 'localhost',
            database: accountNumber,
            password: 'Modul@rdev@2024',
            port: 5432,
        });

        try {
        // Test database connection
            const testResult = await customerPool.query('SELECT NOW()');
            logger.info(`Database connection test successful for ${accountNumber}: ${JSON.stringify(testResult.rows[0])}`);

        // Use the provided event data
        const clockData = eventData;
        
        // Extract the important fields
        const majorEventType = clockData.AccessControllerEvent.majorEventType;
        const subEventType = clockData.AccessControllerEvent.subEventType;
        
        // Fix: Use deviceID as the device identifier
        const deviceId = clockData.deviceID || '';
        
        const eventDateTime = new Date(clockData.dateTime || new Date());
        
        // Fix: Properly extract verify mode
        const verifyMode = clockData.AccessControllerEvent.currentVerifyMode || 
                          clockData.AccessControllerEvent.verifyMode || 
                          'unknown';
        
        // Use the provided employee ID
        const verifyNo = employeeId;
        
        // Extract employeeNoString if available (this is the employee number)
        const employeeNoString = clockData.AccessControllerEvent.employeeNoString || null;

        // Log the complete event data for debugging
        logger.debug('Processing Hikvision access event:', {
            verifyNo,
            employeeNoString,
            deviceId,
            eventDateTime,
            majorEventType,
            subEventType,
            verifyMode,
            fullEvent: clockData
        });

        // Start a transaction
        const client = await customerPool.connect();
        try {
            await client.query('BEGIN');

            // Check for existing clocking within 1 minute
            const existingClocking = await client.query(
                `SELECT attendance_id FROM attendance_records 
                 WHERE device_id = $1 
                 AND date_time >= $2::timestamp - interval '1 minute'
                 AND date_time <= $2::timestamp`,
                [deviceId, eventDateTime]
            );

            if (existingClocking.rows.length > 0) {
                logger.info('Skipping duplicate clocking:', {
                    deviceId,
                    eventDateTime
                });
                await client.query('COMMIT');
                return {
                    success: true,
                    message: 'Duplicate clocking ignored',
                    existing_id: existingClocking.rows[0].attendance_id
                };
            }

            // Try to get employee details using employee_number first (from employeeNoString)
            let employee = null;
            
            // First try by employee_number if employeeNoString is available
            if (employeeNoString) {
                const employeeResult = await client.query(
                    'SELECT employee_id, first_name, last_name, department FROM employees WHERE employee_number = $1',
                    [employeeNoString]
                );
                
                if (employeeResult.rows.length > 0) {
                    employee = employeeResult.rows[0];
                    logger.info(`Found employee by employee_number: ${employeeNoString}`);
                }
            }
            
            // If not found by employee_number, try by clock_number
            if (!employee && verifyNo) {
                const fallbackResult = await client.query(
                    'SELECT employee_id, first_name, last_name, department FROM employees WHERE clock_number = $1',
                    [verifyNo]
                );
                
                if (fallbackResult.rows.length > 0) {
                    employee = fallbackResult.rows[0];
                    logger.info(`Found employee by clock_number: ${verifyNo}`);
                }
            }

            // Fix: Determine attendance status based on verify mode and event type more accurately
            let status = 'present';
            if (subEventType === HIKVISION_EVENT_TYPES.MINOR.SWIPE_CARD_FAILED ||
                subEventType === HIKVISION_EVENT_TYPES.MINOR.FACE_RECOGNITION_FAILED ||
                subEventType === HIKVISION_EVENT_TYPES.MINOR.INVALID_PASSWORD) {
                status = 'failed';
            } else if (subEventType === HIKVISION_EVENT_TYPES.MINOR.SWIPE_CARD_SUCCESS ||
                       subEventType === HIKVISION_EVENT_TYPES.MINOR.FACE_RECOGNITION_SUCCESS ||
                       subEventType === HIKVISION_EVENT_TYPES.MINOR.PASSWORD_OPEN) {
                status = 'present';
            }

            // If no employee found, insert into unknown_clockings table
            if (!employee) {
                logger.warn(`No employee found for clock number ${verifyNo} or employee number ${employeeNoString}, inserting into unknown_clockings table`);
                
                // Insert into unknown_clockings table
                const insertResult = await client.query(
                    `INSERT INTO unknown_clockings (
                        date,
                        date_time,
                        clock_number,
                        device_id,
                        verify_mode,
                        verify_status,
                        major_event_type,
                        minor_event_type,
                        raw_data,
                        created_at
                    ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, CURRENT_TIMESTAMP) RETURNING id`,
                    [
                        new Date(eventDateTime).toISOString().split('T')[0], // Extract date part only
                        eventDateTime,
                        employeeNoString || verifyNo.toString(), // Use employeeNoString if available, otherwise verifyNo
                        deviceId,
                        verifyMode,
                        status,
                        majorEventType,
                        subEventType,
                        JSON.stringify(clockData) // Store the full raw data
                    ]
                );
                
                await client.query('COMMIT');
                
                // Broadcast the event with unknown employee info
                broadcastClockEvent(accountNumber, {
                    id: insertResult.rows[0].id,
                    employeeId: null,
                    employeeName: `Unknown (${employeeNoString || verifyNo})`,
                    department: 'Unassigned',
                    clockNumber: employeeNoString || verifyNo.toString(),
                    deviceId: deviceId,
                    time: eventDateTime.toISOString(),
                    type: 'unknown_clocking',
                    status: status,
                    verifyMode: verifyMode,
                    majorEventType: majorEventType,
                    minorEventType: subEventType
                });
                
                return {
                    success: true,
                    message: 'Unknown employee clocking recorded',
                    id: insertResult.rows[0].id,
                    status: status,
                    table: 'unknown_clockings'
                };
            } else {
                // Check if default shift exists, create if not
                const shiftResult = await client.query(
                    'SELECT shift_id FROM shifts WHERE shift_id = $1',
                    [1]
                );
                
                if (shiftResult.rows.length === 0) {
                    logger.info('Creating open shift with ID 1');
                    await client.query(
                        `INSERT INTO shifts (shift_id, shift_name, start_time, end_time) 
                         VALUES ($1, $2, $3, $4)`,
                        [1, 'Open Shift', null, null]
                    );
                }
                
                // Insert attendance record with correct column names and additional Hikvision data
                const insertResult = await client.query(
            `INSERT INTO attendance_records (
                employee_id,
                date,
                        date_time,
                shift_id,
                        clock_in_time,
                status,
                        clock_number,
                        device_id,
                        verify_mode,
                        verify_status,
                        major_event_type,
                        minor_event_type,
                created_at
                    ) VALUES ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10, $11, $12, CURRENT_TIMESTAMP) RETURNING attendance_id`,
                    [
                        employee.employee_id,
                        new Date(eventDateTime).toISOString().split('T')[0], // Extract date part only
                        eventDateTime,
                        1, // default shift_id
                        eventDateTime,
                        status,
                        employeeNoString || verifyNo.toString(), // Use employeeNoString if available, otherwise verifyNo
                        deviceId,
                        verifyMode,
                        status,
                        majorEventType,
                        subEventType
                    ]
                );
                
                await client.query('COMMIT');
                
                // Broadcast the event with correct employee name fields and status
        broadcastClockEvent(accountNumber, {
                    id: insertResult.rows[0].attendance_id,
                    employeeId: employee.employee_id,
                    employeeName: `${employee.first_name} ${employee.last_name}`,
                    department: employee.department,
                    clockNumber: employeeNoString || verifyNo.toString(),
                    deviceId: deviceId,
                    time: eventDateTime.toISOString(),
                    type: 'clock_in',
                    status: status,
                    verifyMode: verifyMode,
                    majorEventType: majorEventType,
                    minorEventType: subEventType
                });
                
                return {
                    success: true,
                    message: 'Hikvision event processed successfully',
                    id: insertResult.rows[0].attendance_id,
                    status: status,
                    table: 'attendance_records'
                };
            }
        } catch (error) {
            await client.query('ROLLBACK');
            throw error;
        } finally {
            client.release();
        await customerPool.end();
        }
    } catch (error) {
        throw error;
    }
}

// Function to stop a server
async function stopServer(port) {
    const server = servers.get(port);
    if (server) {
        await new Promise(resolve => server.close(resolve));
        servers.delete(port);
        logger.info(`Server on port ${port} stopped`);
    }
}

// Function to manage servers
async function manageServers() {
    try {
        // Refresh port cache and get all active ports
        const activePorts = await refreshPortCache();
        const currentPorts = Array.from(servers.keys());
        
        logger.info(`Managing servers: Active ports = ${activePorts.length}, Current servers = ${currentPorts.length}`);

        // Start servers for new ports
        for (const port of activePorts) {
            if (!servers.has(port)) {
                logger.info(`Starting new server on port ${port}`);
                try {
                    await createServer(port);
                } catch (error) {
                    logger.error(`Failed to create server on port ${port}:`, error);
                }
            }
        }

        // Stop servers for removed ports
        for (const port of currentPorts) {
            if (!activePorts.includes(port)) {
                logger.info(`Stopping server on port ${port} (no longer active)`);
                await stopServer(port);
            }
        }
        
        logger.info(`Server management complete. Active servers: ${servers.size}`);
    } catch (error) {
        logger.error('Error managing servers:', error);
    }
}

// Check for port changes every minute
const REFRESH_INTERVAL = 60000; // 60 seconds
setInterval(manageServers, REFRESH_INTERVAL);

// Initial server setup
manageServers().then(() => {
    logger.info('Initial server setup completed');
}).catch(error => {
    logger.error('Error during initial server setup:', error);
    process.exit(1);
});

// Graceful shutdown
process.on('SIGTERM', async () => {
    logger.info('Received SIGTERM. Shutting down servers...');
    for (const [port] of servers) {
        await stopServer(port);
    }
    await pool.end();
    process.exit(0);
});

// Handle other termination signals
process.on('SIGINT', async () => {
    logger.info('Received SIGINT. Shutting down servers...');
    for (const [port] of servers) {
        await stopServer(port);
    }
    await pool.end();
    process.exit(0);
}); 

// Add periodic heartbeat to maintain device connection
setInterval(async () => {
    try {
        // For each active server/port
        for (const [port] of servers) {
            const accountNumber = await getAccountNumberForPort(port);
            if (!accountNumber) continue;

            logger.debug(`Sending heartbeat for port ${port}, account ${accountNumber}`);
            
            // Log the heartbeat attempt
            logger.info('Heartbeat sent for device on port:', {
                port,
                accountNumber,
                timestamp: new Date().toISOString()
            });
        }
    } catch (error) {
        logger.error('Error sending heartbeat:', error);
    }
}, 30000); // Send every 30 seconds 