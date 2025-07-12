const express = require('express');
const cors = require('cors');
const { startServer, getStatus, startPeriodicSync } = require('./serverManager');
const { getCustomerPorts, pool } = require('./db/mainDb');
const clockRoutes = require('./api/clockControl');
const createCustomerServer = require('./handlers/customerServer');
const { openDoor } = require('./lib/sendCommand');

// Create Express app
const app = express();
const PORT = process.env.PORT || 3000;

// Middleware
app.use(cors());
app.use(express.json());

// Health check endpoint
app.get('/', (req, res) => {
  res.status(200).json({ status: 'ok', message: 'Clock control API running' });
});

// API routes
app.use('/clock', clockRoutes);

/**
 * Auto-start clock servers with retry mechanism
 */
async function autoStartClockServers(maxRetries = 5, retryDelay = 5000) {
  let retries = 0;
  let success = false;

  while (!success && retries < maxRetries) {
    try {
      console.log(`🔄 Attempting to auto-start clock servers (attempt ${retries + 1}/${maxRetries})...`);
      
      // Try to get customer data from database
      const customers = await getCustomerPorts();
      
      if (!customers || customers.length === 0) {
        console.log('⚠️ No customers found to auto-start');
        return;
      }
      
      console.log(`📋 Found ${customers.length} customers to initialize`);
      
      // Start servers for each customer
      for (const customer of customers) {
        const account = customer.account_number;
        try {
          await startServer(account);
          console.log(`✅ Auto-started server for ${account} on port ${customer.clock_server_port}`);
        } catch (e) {
          console.error(`❌ Failed to auto-start ${account}:`, e.message);
        }
      }
      
      console.log('🚀 Auto-start process completed successfully');
      success = true;
    } catch (error) {
      retries++;
      console.error(`❌ Error during auto-start process (attempt ${retries}/${maxRetries}):`, error.message);
      
      if (retries < maxRetries) {
        console.log(`⏱️ Retrying in ${retryDelay/1000} seconds...`);
        await new Promise(resolve => setTimeout(resolve, retryDelay));
      } else {
        console.error('❌ Maximum retries reached. Failed to auto-start clock servers.');
      }
    }
  }
}

// Test database connection and handle errors
pool.query('SELECT NOW()')
  .then(() => {
    console.log('✅ Database connection test successful');
  })
  .catch(err => {
    console.error('❌ Database connection test failed:', err.message);
    console.error('⚠️ Make sure host.docker.internal is properly configured and PostgreSQL is running on the host machine.');
  });

// Start the server
app.listen(PORT, async () => {
  try {
    console.log(`🧠 Clock control API running at http://localhost:${PORT}`);
    
    // Auto-start clock servers with retry
    await autoStartClockServers();

    // Log before starting periodic sync
    console.log('⏳ About to start periodic server sync...');
    // Start periodic sync
    startPeriodicSync();

    // Replace with your clock's IP
    openDoor('192.168.1.55', 'admin', 'Modul@rdev@2024');
  } catch (err) {
    console.error('❌ Error in app.listen startup callback:', err);
  }
});
