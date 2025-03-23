const createCustomerServer = require('./handlers/customerServer');
const { getCustomerByAccount } = require('./db/mainDb');
const fs = require('fs').promises;
const path = require('path');

// Store active servers in memory
const activeServers = {};

// Path to persist server status
const STATE_FILE = path.join(__dirname, 'server-state.json');

// Load persisted server state on startup
async function loadServerState() {
  try {
    const data = await fs.readFile(STATE_FILE, 'utf8');
    const state = JSON.parse(data);
    console.log(`📂 Loaded saved state for ${Object.keys(state).length} servers`);
    return state;
  } catch (error) {
    console.log('⚠️ No saved state found or error reading state file');
    return {};
  }
}

// Save server state to disk
async function saveServerState() {
  try {
    // Create a state object with just account numbers and running status
    const state = {};
    for (const [account, server] of Object.entries(activeServers)) {
      state[account] = { running: true };
    }
    
    await fs.writeFile(STATE_FILE, JSON.stringify(state, null, 2));
    console.log(`💾 Saved state for ${Object.keys(state).length} servers`);
  } catch (error) {
    console.error('❌ Failed to save server state:', error.message);
  }
}

// Initialize server state from disk
(async () => {
  const savedState = await loadServerState();
  
  // Auto-restart any previously running servers
  for (const account of Object.keys(savedState)) {
    try {
      console.log(`🔄 Auto-restarting previously active server: ${account}`);
      await startServer(account);
    } catch (error) {
      console.error(`❌ Failed to auto-restart server for ${account}:`, error.message);
    }
  }
})();

async function startServer(accountNumber) {
  console.log(`🔍 Starting server for ${accountNumber}`);
  
  // If server is already running, return success without restarting
  if (activeServers[accountNumber]) {
    console.log(`⚠️ Server already running for ${accountNumber}`);
    return true;
  }

  try {
    const customer = await getCustomerByAccount(accountNumber);
    
    if (!customer) {
      throw new Error(`Customer not found for account: ${accountNumber}`);
    }

    if (!customer.clock_server_port) {
      throw new Error(`No clock_server_port defined for account: ${accountNumber}`);
    }

    console.log(`🔌 Creating server on port: ${customer.clock_server_port} for ${accountNumber}`);
    const server = createCustomerServer(customer.clock_server_port, accountNumber);
    
    // Store the server instance
    activeServers[accountNumber] = server;
    
    // Persist the updated state
    await saveServerState();
    
    console.log(`✅ Server started successfully for ${accountNumber}`);
    return true;
  } catch (error) {
    console.error(`❌ Error starting server for ${accountNumber}:`, error.message);
    throw error;
  }
}

async function stopServer(accountNumber) {
  console.log(`🔍 Stopping server for ${accountNumber}`);
  
  const server = activeServers[accountNumber];
  if (!server) {
    console.log(`⚠️ No active server found for ${accountNumber}`);
    return false;
  }

  try {
    console.log(`🛑 Closing server for ${accountNumber}`);
    server.close();
    delete activeServers[accountNumber];
    
    // Persist the updated state
    await saveServerState();
    
    console.log(`✅ Server stopped successfully for ${accountNumber}`);
    return true;
  } catch (error) {
    console.error(`❌ Error stopping server for ${accountNumber}:`, error.message);
    throw error;
  }
}

async function getStatus(accountNumber) {
  const isRunning = !!activeServers[accountNumber];
  console.log(`📊 Status check for ${accountNumber}: ${isRunning ? 'RUNNING' : 'STOPPED'}`);
  
  return {
    running: isRunning
  };
}

module.exports = { startServer, stopServer, getStatus };
