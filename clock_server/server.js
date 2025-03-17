const express = require('express');
const { Pool } = require('pg');
const bodyParser = require('body-parser');
const winston = require('winston');
const bodyParserXml = require('body-parser-xml');
const http = require('http');
const cors = require('cors');
const multer = require('multer');

// Simplified logger configuration
const logger = winston.createLogger({
    level: 'info',
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
        new winston.transports.File({ filename: 'combined.log' })
    ]
});

// Enable XML parsing
bodyParserXml(bodyParser);

// Store active servers
const servers = new Map();

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

// ISUP Key validation middleware
function verifyIsupAuth(req, res, next) {
    // Define the expected ISUP key
    const EXPECTED_ISUP_KEY = process.env.ISUP_KEY || "MySecretKey123";
    
    // Check for ISUP key in headers (case-insensitive)
    const isupKey = req.headers['isup-key'] || 
                   req.headers['x-isup-key'] || 
                   req.headers['authorization'];
    
    // Special handling for Hikvision devices (simplified)
    if (req.headers['content-type'] && req.headers['content-type'].includes('multipart/form-data')) {
        // Allow Hikvision multipart requests to proceed
        logger.info('Allowing Hikvision multipart form-data request');
                    return next();
    }
    
    // If no ISUP key is found, log warning but allow the request to proceed
    if (!isupKey) {
        logger.warn('No ISUP key provided in request');
        // Allow the request to proceed anyway
        return next();
    }
    
    // If ISUP key is found but doesn't match expected value
    if (isupKey !== EXPECTED_ISUP_KEY) {
        logger.warn(`Invalid ISUP key provided: ${isupKey}`);
        // Allow the request to proceed anyway
        return next();
    }
    
    // ISUP key is valid
    logger.info('Valid ISUP key provided');
    next();
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
    const { Pool } = require('pg');
    const mainPool = new Pool({
        user: 'Tian',
        host: 'localhost',
        database: 'modular_system',
        password: 'Modul@rdev@2024',
        port: 5432,
    });
    
    try {
        const result = await mainPool.query(
            'SELECT account_number FROM customers WHERE clock_server_port = $1 AND status = $2',
            [port, 'active']
        );
        
        if (result.rows.length === 0) {
            return null;
        }
        
        return result.rows[0].account_number;
    } catch (error) {
        logger.error(`Error getting account number for port ${port}: ${error.message}`);
        return null;
    } finally {
        await mainPool.end();
    }
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
            logger.debug(`[PORT ${port}] RAW REQUEST BODY: ${data}`);
        next();
        });
    });
    
    // Configure body parsers
    app.use(bodyParser.json());
    app.use(bodyParser.urlencoded({ extended: true }));
    
    // Add ISUP verification middleware
    app.use(['/ISAPI/*', '/EventService', '/clock'], verifyIsupAuth);
    
    // Log all incoming requests
    app.use((req, res, next) => {
        logger.info(`[PORT ${port}] Received ${req.method} request to ${req.url}`);
        next();
    });

    // Handle clock data from Hikvision device
    app.post('/clock', async (req, res) => {
        try {
            logger.info('Received request to /clock');
            
            // Check if we have the raw body
            if (!req.rawBody) {
                logger.error('No raw body available');
                return res.status(400).json({
                    status: "ERROR",
                    message: "No raw body available",
                    timestamp: new Date().toISOString()
                });
            }
            
            // Log the raw request body for debugging
            logger.info(`RAW BODY: ${req.rawBody.substring(0, 2000)}`);

            // Get request content type for determining response format
            const isXmlRequest = req.headers['content-type'] && 
                (req.headers['content-type'].includes('application/xml') || 
                 req.headers['content-type'].includes('text/xml'));
            
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
                logger.info('Extracted event_log JSON:', eventLogJson);
            } else {
                // Try a simpler pattern as fallback
                const simpleMatch = req.rawBody.match(/name="event_log"[\s\S]*?\r\n\r\n([\s\S]*?)(?:\r\n--|$)/);
                if (simpleMatch && simpleMatch[1]) {
                    eventLogJson = simpleMatch[1].trim();
                    logger.info('Extracted event_log JSON (simple method):', eventLogJson);
                } else {
                    // Last resort - try to find any JSON object in the raw body
                    const jsonMatch = req.rawBody.match(/(\{[\s\S]*\})/);
                    if (jsonMatch && jsonMatch[1]) {
                        eventLogJson = jsonMatch[1].trim();
                        logger.info('Extracted JSON from raw body (last resort):', eventLogJson);
                    }
                }
            }
            
            if (!eventLogJson) {
                logger.error('Could not extract event_log from multipart form data');
                
                // Even when failing to extract, send a successful ACK to prevent retries
                if (isXmlRequest) {
                    res.set('Content-Type', 'application/xml');
                    res.status(200).send(`<?xml version="1.0" encoding="UTF-8"?>
<ResponseStatus version="2.0" xmlns="http://www.hikvision.com/ver20/XMLSchema">
    <statusCode>1</statusCode>
    <statusString>OK</statusString>
    <subStatusCode>ok</subStatusCode>
</ResponseStatus>`);
                } else {
                    res.status(200).json({
                        ResponseStatus: {
                            statusCode: 1,
                            statusString: "OK", 
                            subStatusCode: "ok"
                        }
                    });
                }
                return;
            }
            
            // Check for duplicates at the raw event level using a hash
            const crypto = require('crypto');
            const eventHash = crypto.createHash('md5').update(eventLogJson).digest('hex');
            
            // Use a simple in-memory cache for duplicates (lasts for server lifetime)
            if (!global.recentEventHashes) {
                global.recentEventHashes = new Map();
            }
            
            // Check if this exact event was processed recently (within 2 minutes)
            const now = Date.now();
            const twoMinutesAgo = now - (2 * 60 * 1000);
            
            // Clean up old entries first
            for (const [hash, timestamp] of global.recentEventHashes.entries()) {
                if (timestamp < twoMinutesAgo) {
                    global.recentEventHashes.delete(hash);
                }
            }
            
            // Check if event is a duplicate
            if (global.recentEventHashes.has(eventHash)) {
                logger.info(`Duplicate event detected by hash ${eventHash}. Acknowledging without processing.`);
                
                // Send ACK response even for duplicates
                if (isXmlRequest) {
                    res.set('Content-Type', 'application/xml');
                    res.status(200).send(`<?xml version="1.0" encoding="UTF-8"?>
<ResponseStatus version="2.0" xmlns="http://www.hikvision.com/ver20/XMLSchema">
    <statusCode>1</statusCode>
    <statusString>OK</statusString>
    <subStatusCode>ok</subStatusCode>
</ResponseStatus>`);
                } else {
                        res.status(200).json({
                        ResponseStatus: {
                            statusCode: 1,
                            statusString: "OK",
                            subStatusCode: "ok"
                        }
                    });
                }
                return;
            }
            
            // Store this event hash
            global.recentEventHashes.set(eventHash, now);
            
            // Parse the JSON
            try {
                const eventData = JSON.parse(eventLogJson);
                
                // Log the complete event data structure
                logger.info('FULL EVENT DATA:', JSON.stringify(eventData, null, 2));
                
                // Handle different types of events from the clock
                    if (eventData.AccessControllerEvent) {
                    // This is the main access control event
                    logger.info('AccessControllerEvent FIELDS:', Object.keys(eventData.AccessControllerEvent));
                    
                    // Look for all expected fields that should be in a valid clock event
                    const employeeNoString = eventData.AccessControllerEvent.employeeNoString;
                    const cardNo = eventData.AccessControllerEvent.cardNo;
                    const verifyNo = eventData.AccessControllerEvent.verifyNo;
                    const attendanceStatus = eventData.AccessControllerEvent.attendanceStatus;
                    
                    logger.info(`CLOCK EVENT - Card: ${cardNo}, Employee: ${employeeNoString}, VerifyNo: ${verifyNo}, Status: ${attendanceStatus}`);
                    
                    // Determine if this is a primary clock event or a secondary relay event
                    // Primary events usually have employeeNoString and verifyNo
                    const isPrimaryClockEvent = employeeNoString && (verifyNo || cardNo);
                    
                    if (isPrimaryClockEvent) {
                        // Process the primary event
                        try {
                            // Use employeeNoString as the employee number since that's what user is clocking with (1005)
                            const clockNumber = employeeNoString || verifyNo?.toString() || cardNo;
                            
                            if (!clockNumber) {
                                logger.error('No valid clock number found in event data');
                                
                                // Send ACK even though we didn't find a clock number
                                if (isXmlRequest) {
                                    res.set('Content-Type', 'application/xml');
                                    res.status(200).send(`<?xml version="1.0" encoding="UTF-8"?>
<ResponseStatus version="2.0" xmlns="http://www.hikvision.com/ver20/XMLSchema">
    <statusCode>1</statusCode>
    <statusString>OK</statusString>
    <subStatusCode>ok</subStatusCode>
</ResponseStatus>`);
                                } else {
                                    res.status(200).json({
                                        ResponseStatus: {
                                            statusCode: 1,
                                            statusString: "OK",
                                            subStatusCode: "ok"
                                        }
                                    });
                                }
                                return;
                            }
                            
                            logger.info(`Processing primary clock event for employee ${clockNumber}`);
                            const result = await processClockEvent(req, port, eventData, clockNumber);
                            
                            // Send ACK response in exact Hikvision-compatible format per documentation
                            if (isXmlRequest) {
                                // XML format for Hikvision devices that expect XML
                                res.set('Content-Type', 'application/xml');
                                res.status(200).send(`<?xml version="1.0" encoding="UTF-8"?>
<ResponseStatus version="2.0" xmlns="http://www.hikvision.com/ver20/XMLSchema">
    <statusCode>1</statusCode>
    <statusString>OK</statusString>
    <subStatusCode>ok</subStatusCode>
</ResponseStatus>`);
                            } else {
                                // JSON format for newer Hikvision devices
                            res.status(200).json({
                                    ResponseStatus: {
                                        statusCode: 1,
                                        statusString: "OK", 
                                        subStatusCode: "ok"
                                    }
                                });
                            }
                        } catch (error) {
                            logger.error(`Error processing clock event: ${error.message}`);
                            
                            // Still send ACK to the device to prevent retries - in exact Hikvision format
                            if (isXmlRequest) {
                                // XML format
                                res.set('Content-Type', 'application/xml');
                                res.status(200).send(`<?xml version="1.0" encoding="UTF-8"?>
<ResponseStatus version="2.0" xmlns="http://www.hikvision.com/ver20/XMLSchema">
    <statusCode>1</statusCode>
    <statusString>OK</statusString>
    <subStatusCode>ok</subStatusCode>
</ResponseStatus>`);
                            } else {
                                // JSON format
                                res.status(200).json({
                                    ResponseStatus: {
                                        statusCode: 1,
                                        statusString: "OK",
                                        subStatusCode: "ok"
                                    }
                                });
                            }
                        }
                    } else {
                        // This appears to be a secondary event (like relay activation)
                        logger.info('Received secondary event (likely relay activation). Acknowledging without processing.');
                        
                        // Just send ACK for secondary events without processing them
                        if (isXmlRequest) {
                            res.set('Content-Type', 'application/xml');
                            res.status(200).send(`<?xml version="1.0" encoding="UTF-8"?>
<ResponseStatus version="2.0" xmlns="http://www.hikvision.com/ver20/XMLSchema">
    <statusCode>1</statusCode>
    <statusString>OK</statusString>
    <subStatusCode>ok</subStatusCode>
</ResponseStatus>`);
                        } else {
        res.status(200).json({
                                ResponseStatus: {
                                    statusCode: 1,
                                    statusString: "OK",
                                    subStatusCode: "ok"
                                }
                            });
                        }
                    }
                } else if (eventData.eventType && eventData.eventType === "AccessControllerEvent") {
                    // This is likely a secondary event or notification 
                    logger.info('Received secondary or notification event. Acknowledging without processing.');
                    
                    // Send ACK response without processing further
                    if (isXmlRequest) {
                        res.set('Content-Type', 'application/xml');
                        res.status(200).send(`<?xml version="1.0" encoding="UTF-8"?>
<ResponseStatus version="2.0" xmlns="http://www.hikvision.com/ver20/XMLSchema">
    <statusCode>1</statusCode>
    <statusString>OK</statusString>
    <subStatusCode>ok</subStatusCode>
</ResponseStatus>`);
                    } else {
                        res.status(200).json({
                            ResponseStatus: {
                                statusCode: 1,
                                statusString: "OK",
                                subStatusCode: "ok"
                            }
                        });
                    }
                } else {
                    logger.error('Invalid event format: No AccessControllerEvent found');
                    // Send success response even for invalid format to stop retries
                    if (isXmlRequest) {
                        res.set('Content-Type', 'application/xml');
                        res.status(200).send(`<?xml version="1.0" encoding="UTF-8"?>
<ResponseStatus version="2.0" xmlns="http://www.hikvision.com/ver20/XMLSchema">
    <statusCode>1</statusCode>
    <statusString>OK</statusString>
    <subStatusCode>ok</subStatusCode>
</ResponseStatus>`);
                    } else {
                        res.status(200).json({
                            ResponseStatus: {
                                statusCode: 1,
                                statusString: "OK",
                                subStatusCode: "ok"
                            }
                        });
                    }
                }
            } catch (e) {
                logger.error(`Error parsing event_log JSON: ${e.message}`);
                // Send success response even for parse errors to stop retries
                if (isXmlRequest) {
                    res.set('Content-Type', 'application/xml');
                    res.status(200).send(`<?xml version="1.0" encoding="UTF-8"?>
<ResponseStatus version="2.0" xmlns="http://www.hikvision.com/ver20/XMLSchema">
    <statusCode>1</statusCode>
    <statusString>OK</statusString>
    <subStatusCode>ok</subStatusCode>
</ResponseStatus>`);
                } else {
                    res.status(200).json({
                        ResponseStatus: {
                            statusCode: 1,
                            statusString: "OK",
                            subStatusCode: "ok"
                        }
                    });
                }
            }
        } catch (error) {
            logger.error(`Error processing Hikvision event on port ${port}:`, error);
            // Send success response even for unexpected errors to stop retries
            if (req.headers['content-type'] && (req.headers['content-type'].includes('application/xml') || req.headers['content-type'].includes('text/xml'))) {
                res.set('Content-Type', 'application/xml');
                res.status(200).send(`<?xml version="1.0" encoding="UTF-8"?>
<ResponseStatus version="2.0" xmlns="http://www.hikvision.com/ver20/XMLSchema">
    <statusCode>1</statusCode>
    <statusString>OK</statusString>
    <subStatusCode>ok</subStatusCode>
</ResponseStatus>`);
            } else {
                res.status(200).json({
                    ResponseStatus: {
                        statusCode: 1,
                        statusString: "OK",
                        subStatusCode: "ok"
                    }
                });
            }
        }
    });
    
    // Additional endpoints for Hikvision - use the same handler
    app.post(['/EventService', '/EventService/*', '/ISAPI/Event/notification/alertStream', 
        '/ISAPI/AccessControl/AcsEvent', '/ISAPI/AccessControl/*', '/ISAPI/Event/*'], (req, res) => {
        // Forward to the /clock handler
        req.url = '/clock';
        app.handle(req, res);
    });

    // Required for Hikvision device compatibility
    app.get('/DeviceStatus', (req, res) => {
        const statusResponse = `<AcsWorkStatus>
            <cardReaderOnlineStatus>enable</cardReaderOnlineStatus>
            <masterChannelControllerStatus>enable</masterChannelControllerStatus>
            <slaveChannelControllerStatus>enable</slaveChannelControllerStatus>
        </AcsWorkStatus>`;
        
        res.set('Content-Type', 'application/xml');
        res.send(statusResponse);
    });

    // Add door control endpoint
    app.post('/door/control', async (req, res) => {
        try {
            // Extract device info and command
            const { deviceId, action, duration } = req.body;
            
            if (!deviceId || !action) {
                return res.status(400).json({
                    success: false,
                    message: 'Missing required parameters'
                });
            }
            
            // Get account number for this port
            const accountNumber = await getAccountNumberForPort(port);
            if (!accountNumber) {
                return res.status(404).json({
                    success: false,
                    message: 'Customer account not found'
                });
            }
            
            // Get device info from customer database
            const customerPool = new Pool({
                user: 'Tian',
                host: 'localhost',
                database: accountNumber,
                password: 'Modul@rdev@2024',
                port: 5432,
            });

                try {
                        const deviceResult = await customerPool.query(
                    'SELECT ip_address, username, password FROM devices WHERE device_id = $1 OR serial_number = $1',
                    [deviceId]
                );
                
                if (deviceResult.rows.length === 0) {
                    return res.status(404).json({
                        success: false,
                        message: 'Device not found'
                    });
                }
                
                const device = deviceResult.rows[0];
                const deviceIp = device.ip_address;
                const username = device.username || 'admin'; // Default to admin if not set
                const password = device.password || '12345'; // Default to 12345 if not set
                
                // Define XML commands for different actions
                const commandMap = {
                    'unlock': `<?xml version="1.0" encoding="UTF-8"?>
<RemoteControlDoor version="2.0" xmlns="http://www.isapi.org/ver20/XMLSchema">
    <doorNo>1</doorNo>
    <openDoor>true</openDoor>
</RemoteControlDoor>`,
                    'lock': `<?xml version="1.0" encoding="UTF-8"?>
<RemoteControlDoor version="2.0" xmlns="http://www.isapi.org/ver20/XMLSchema">
    <doorNo>1</doorNo>
    <openDoor>false</openDoor>
</RemoteControlDoor>`,
                    'hold': `<?xml version="1.0" encoding="UTF-8"?>
<RemoteControlDoor version="2.0" xmlns="http://www.isapi.org/ver20/XMLSchema">
    <doorNo>1</doorNo>
    <openDoor>true</openDoor>
    <holdTime>${duration || 60}</holdTime>
</RemoteControlDoor>`
                };
                
                const commandXml = commandMap[action];
                if (!commandXml) {
                    return res.status(400).json({
                        success: false,
                        message: 'Invalid action specified'
                    });
                }
                
                // Implement the API call to control the door
                // Using node-fetch for HTTP requests
                const fetch = require('node-fetch');
                const btoa = require('btoa');
                
                const apiUrl = `http://${deviceIp}/ISAPI/AccessControl/RemoteControl/door/1`;
                const authHeader = 'Basic ' + btoa(`${username}:${password}`);
                
                const response = await fetch(apiUrl, {
                    method: 'PUT',
                    headers: {
                        'Content-Type': 'application/xml',
                        'Authorization': authHeader
                    },
                    body: commandXml
                });
                
                const responseText = await response.text();
                
                if (response.ok) {
                    logger.info(`Door control successful: ${action} for device ${deviceId}`);
                    
                    // Log the action in the database
                    await customerPool.query(
                        `INSERT INTO device_actions (
                            device_id,
                            action_type,
                            status,
                            details,
                            created_at
                        ) VALUES ($1, $2, $3, $4, CURRENT_TIMESTAMP)`,
                        [deviceId, action, 'success', JSON.stringify({duration})]
                    );
                    
        res.status(200).json({
                        success: true,
                        message: `Door ${action} command sent successfully`,
                        response: responseText
                    });
                } else {
                    logger.error(`Door control failed: ${responseText}`);
                    
                    // Log the failed action
                    await customerPool.query(
                        `INSERT INTO device_actions (
                            device_id,
                            action_type,
                            status,
                            details,
                            created_at
                        ) VALUES ($1, $2, $3, $4, CURRENT_TIMESTAMP)`,
                        [deviceId, action, 'failed', JSON.stringify({
                            error: response.statusText,
                            response: responseText
                        })]
                    );
                    
                    res.status(response.status).json({
                        success: false,
                        message: 'Failed to execute door control command',
                        error: responseText
                    });
                }
            } finally {
                await customerPool.end();
            }
        } catch (error) {
            logger.error(`Error in door control: ${error.message}`);
            res.status(500).json({
                success: false,
                message: 'Internal server error processing door control command',
                error: error.message
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

// Separate the event processing logic
async function processClockEvent(req, port, eventData, verifyNo) {
    // Connect to main DB to get account number from port
    const { Pool } = require('pg');
    const mainPool = new Pool({
        user: 'Tian',
        host: 'localhost',
        database: 'modular_system',
        password: 'Modul@rdev@2024',
        port: 5432,
    });
    
    try {
        // Get account number from port in customers table
        const portResult = await mainPool.query(
            'SELECT account_number FROM customers WHERE clock_server_port = $1 AND status = $2',
            [port, 'active']
        );
        
        if (portResult.rows.length === 0) {
            throw new Error(`No active customer found for port ${port}`);
        }
        
        const accountNumber = portResult.rows[0].account_number;
        logger.info(`Found account number ${accountNumber} for port ${port}`);

        // Create a new pool for the customer's database
        const customerPool = new Pool({
            user: 'Tian',
            host: 'localhost',
            database: accountNumber,
            password: 'Modul@rdev@2024',
            port: 5432,
        });

        try {
            // Extract device information
            const deviceId = eventData.deviceID || '';
            const ipAddress = eventData.ipAddress || req.ip || '';
            const macAddress = eventData.macAddress || '';
            const serialNumber = deviceId; // Using deviceID as serialNumber
            const deviceName = eventData.deviceName || `Clock Device ${deviceId}`;

        // Use the provided event data
        const clockData = eventData;
        
        // Extract the important fields
            const majorEventType = clockData.AccessControllerEvent?.majorEventType;
            const subEventType = clockData.AccessControllerEvent?.subEventType;
        const eventDateTime = new Date(clockData.dateTime || new Date());
        
            // Extract verify mode
            const verifyMode = clockData.AccessControllerEvent?.currentVerifyMode || 
                            clockData.AccessControllerEvent?.verifyMode || 
                          'unknown';

        // Start a transaction
        const client = await customerPool.connect();
            
        try {
            await client.query('BEGIN');
                
                // Check if device exists, create or update it
                const deviceResult = await client.query(
                    `SELECT device_id FROM devices WHERE serial_number = $1 OR device_id = $2`,
                    [serialNumber, deviceId]
                );
                
                if (deviceResult.rows.length === 0) {
                    // Create new device
                    logger.info(`Creating new device record for ${deviceId} in account ${accountNumber}`);
                    await client.query(
                        `INSERT INTO devices (
                            serial_number, 
                            device_id, 
                            device_name, 
                            ip_address, 
                            mac_address, 
                            status, 
                            last_online,
                            created_at
                        ) VALUES ($1, $2, $3, $4, $5, $6, $7, CURRENT_TIMESTAMP)`,
                        [
                            serialNumber,
                            deviceId,
                            deviceName,
                            ipAddress,
                            macAddress,
                            'online',
                            new Date()
                        ]
                    );
                } else {
                    // Update existing device
                    logger.info(`Updating device ${deviceId} status to online`);
                    await client.query(
                        `UPDATE devices SET 
                            ip_address = $1, 
                            mac_address = $2,
                            status = $3, 
                            last_online = $4,
                            updated_at = CURRENT_TIMESTAMP
                        WHERE device_id = $5 OR serial_number = $5`,
                        [ipAddress, macAddress, 'online', new Date(), deviceId]
                    );
                }

            // Check for existing clocking within 1 minute
            const existingClocking = await client.query(
                `SELECT attendance_id FROM attendance_records 
                 WHERE device_id = $1 
                 AND date_time >= $2::timestamp - interval '1 minute'
                 AND date_time <= $2::timestamp`,
                [deviceId, eventDateTime]
            );

            if (existingClocking.rows.length > 0) {
                    logger.info('Skipping duplicate clocking');
                await client.query('COMMIT');
                return {
                    success: true,
                    message: 'Duplicate clocking ignored',
                    existing_id: existingClocking.rows[0].attendance_id
                };
            }

                // Try to get employee details by clock_number (verifyNo)
            let employee = null;
            
                // Look up employee by clock_number
                const employeeResult = await client.query(
                    'SELECT employee_id, first_name, last_name, department FROM employees WHERE clock_number = $1',
                    [verifyNo]
                );
                
                if (employeeResult.rows.length > 0) {
                    employee = employeeResult.rows[0];
                    logger.info(`Found employee by clock_number: ${verifyNo}`);
            }

                // Determine attendance status based on verify mode and event type
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
                    logger.warn(`No employee found for clock number ${verifyNo}`);
                
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
                            verifyNo.toString(), // Always use verifyNo
                        deviceId,
                        verifyMode,
                        status,
                        majorEventType,
                        subEventType,
                        JSON.stringify(clockData) // Store the full raw data
                    ]
                );
                
                await client.query('COMMIT');
                
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
                            verifyNo.toString(), // Always use verifyNo for clock_number
                        deviceId,
                        verifyMode,
                        status,
                        majorEventType,
                        subEventType
                    ]
                );
                
                await client.query('COMMIT');
                
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
            }
        } finally {
        await customerPool.end();
        }
    } catch (error) {
        throw error;
    } finally {
        await mainPool.end();
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