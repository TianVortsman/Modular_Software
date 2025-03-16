const express = require('express');
const { Pool } = require('pg');
const bodyParser = require('body-parser');
const winston = require('winston');
const bodyParserXml = require('body-parser-xml');

// Configure logger
const logger = winston.createLogger({
    level: 'info',
    format: winston.format.combine(
        winston.format.timestamp(),
        winston.format.json()
    ),
    transports: [
        new winston.transports.File({ filename: 'error.log', level: 'error' }),
        new winston.transports.File({ filename: 'combined.log' })
    ]
});

if (process.env.NODE_ENV !== 'production') {
    logger.add(new winston.transports.Console({
        format: winston.format.simple()
    }));
}

// Enable XML parsing
bodyParserXml(bodyParser);

// Store active servers
const servers = new Map();

// Main database pool for customer lookup
const pool = new Pool({
    user: 'Tian',
    host: 'localhost',
    database: 'modular1', // Main database
    password: 'Modul@rdev@2024',
    port: 5432,
});

// Function to create a server for a specific port
async function createServer(port) {
    if (servers.has(port)) {
        return servers.get(port);
    }

    const app = express();
    app.use(bodyParser.json());
    app.use(bodyParser.xml());

    // Handle clock data from Hikvision device
    app.post('/clock', async (req, res) => {
        try {
            // Get customer based on port
            const customerQuery = await pool.query(
                'SELECT account_number FROM customers WHERE clock_port = $1',
                [port]
            );

            if (customerQuery.rows.length === 0) {
                logger.error(`No customer found for port ${port}`);
                return res.status(404).send('Customer not found');
            }

            const customer = customerQuery.rows[0];
            const accountNumber = customer.account_number;

            // Create a new pool for the customer's database
            const customerPool = new Pool({
                user: 'Tian',
                host: 'localhost',
                database: accountNumber, // Use account number as database name
                password: 'Modul@rdev@2024',
                port: 5432,
            });

            // Parse clock data from request
            const clockData = req.body;
            logger.info('Received clock data:', { port, clockData });

            // Insert into attendance_records in customer's database
            await customerPool.query(
                `INSERT INTO attendance_records (
                    employee_id,
                    date,
                    shift_id,
                    check_in,
                    status,
                    created_at
                ) VALUES ($1, $2, $3, $4, $5, CURRENT_TIMESTAMP)`,
                [
                    clockData.employeeId,
                    new Date(), // current date
                    1, // default shift_id, adjust as needed
                    new Date(), // current timestamp for check_in
                    'present'
                ]
            );

            await customerPool.end();
            res.status(200).send('Clock data recorded successfully');

        } catch (error) {
            logger.error('Error processing clock data:', error);
            res.status(500).send('Error processing clock data');
        }
    });

    // Health check endpoint
    app.get('/health', (req, res) => {
        res.status(200).send('OK');
    });

    // Error handling middleware
    app.use((err, req, res, next) => {
        logger.error('Server error:', err);
        res.status(500).send('Internal server error');
    });

    // Start the server
    const server = app.listen(port, () => {
        logger.info(`Clock server listening on port ${port}`);
    });

    servers.set(port, server);
    return server;
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
        // Get all customer ports
        const { rows } = await pool.query(
            'SELECT clock_port FROM customers WHERE clock_port IS NOT NULL AND status = $1',
            ['active']
        );
        
        const activePorts = rows.map(row => row.clock_port);
        const currentPorts = Array.from(servers.keys());

        // Start servers for new ports
        for (const port of activePorts) {
            if (!servers.has(port)) {
                await createServer(port);
            }
        }

        // Stop servers for removed ports
        for (const port of currentPorts) {
            if (!activePorts.includes(port)) {
                await stopServer(port);
            }
        }
    } catch (error) {
        logger.error('Error managing servers:', error);
    }
}

// Check for port changes every minute
setInterval(manageServers, 60000);

// Initial server setup
manageServers().catch(error => {
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