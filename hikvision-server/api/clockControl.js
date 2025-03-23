const express = require('express');
const { startServer, stopServer, getStatus } = require('../serverManager');

const router = express.Router();

/**
 * Start a clock server for an account
 */
router.post('/start/:account', async (req, res) => {
  const account = req.params.account;
  console.log(`➡️ Incoming request: START server for ${account}`);
  
  try {
    const result = await startServer(account);
    console.log(`✅ Server ${result ? 'started' : 'already running'} for ${account}`);
    
    // Small delay to ensure the server is fully started before returning
    await new Promise(resolve => setTimeout(resolve, 500));
    
    // Get the current status after starting
    const status = await getStatus(account);
    
    // Always return a consistent response format
    res.json({
      success: true,
      started: result,
      running: status.running, // Include the current running state
      account: account
    });
  } catch (err) {
    console.error(`❌ Failed to start clock server for ${account}:`, err.message);
    
    // Even on error, use a consistent format
    res.status(500).json({
      success: false,
      error: err.message,
      running: false,
      account: account
    });
  }
});

/**
 * Stop a clock server for an account
 */
router.post('/stop/:account', async (req, res) => {
  const account = req.params.account;
  console.log(`➡️ Incoming request: STOP server for ${account}`);
  
  try {
    const result = await stopServer(account);
    console.log(`✅ Server ${result ? 'stopped' : 'was not running'} for ${account}`);
    
    // Small delay to ensure the server is fully stopped before returning
    await new Promise(resolve => setTimeout(resolve, 500));
    
    // Get the current status after stopping
    const status = await getStatus(account);
    
    // Always return a consistent response format
    res.json({
      success: true,
      stopped: result,
      running: status.running, // Include the current running state
      account: account
    });
  } catch (err) {
    console.error(`❌ Failed to stop clock server for ${account}:`, err.message);
    
    // Even on error, use a consistent format
    res.status(500).json({
      success: false,
      error: err.message,
      account: account
    });
  }
});

/**
 * Get the status of a clock server
 */
router.get('/status/:account', async (req, res) => {
  const account = req.params.account;
  console.log(`➡️ Incoming request: GET STATUS for ${account}`);
  
  try {
    const status = await getStatus(account);
    console.log(`✅ Status for ${account}: ${status.running ? 'RUNNING' : 'STOPPED'}`);
    
    // Always return a consistent response format
    res.json({
      success: true,
      running: status.running,
      account: account
    });
  } catch (err) {
    console.error(`❌ Failed to get status for ${account}:`, err.message);
    
    // Even on error, use a consistent format that won't break JSON parsing
    res.status(500).json({
      success: false,
      running: false,
      error: err.message,
      account: account
    });
  }
});

module.exports = router;
