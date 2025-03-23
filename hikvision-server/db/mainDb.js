const { Pool } = require('pg');
require('dotenv').config();

// Configure PostgreSQL connection from environment variables
const pool = new Pool({
  host: process.env.DB_HOST,
  port: parseInt(process.env.DB_PORT, 10),
  user: process.env.DB_USER,
  password: process.env.DB_PASS,
  database: process.env.DB_NAME
});

// Log connection information for debugging 
console.log('📦 Database connection initialized with:', {
  host: process.env.DB_HOST,
  database: process.env.DB_NAME,
  port: parseInt(process.env.DB_PORT)
});

/**
 * Get all customers with assigned clock server ports
 * @returns {Promise<Array>} - List of customers with their clock server ports
 */
async function getCustomerPorts() {
  console.log('🔍 Getting all customer ports');
  
  try {
    // Get from database
    const query = `
      SELECT
        account_number,
        clock_server_port
      FROM
        customers
      WHERE
        clock_server_port IS NOT NULL
    `;
    const result = await pool.query(query);
    
    console.log(`✅ Found ${result.rows.length} customers with ports in database`);
    return result.rows;
  } catch (error) {
    console.error('❌ Database error getting customer ports:', error.message);
    throw error; // Don't use fallback, let the error propagate
  }
}

/**
 * Calculate port number based on account number
 * @param {string} accountNumber - The customer account number (e.g. "ACC005")
 * @returns {number} - Calculated port number (e.g. 10004 for ACC005)
 */
function calculatePortFromAccount(accountNumber) {
  // Extract the numeric part from account number (e.g. "005" from "ACC005")
  const match = accountNumber.match(/(\d+)$/);
  
  if (match && match[1]) {
    // Convert to number, subtract 1 to get the correct index (e.g. ACC005 -> port 10004)
    const accountIndex = parseInt(match[1], 10) - 1;
    return 10000 + accountIndex;
  }
  
  // Fallback to a default if no match
  console.error(`❌ Could not parse account number: ${accountNumber}`);
  throw new Error(`Invalid account number format: ${accountNumber}`);
}

/**
 * Get customer information by account number
 * @param {string} accountNumber - The customer account number
 * @returns {Promise<Object>} - Customer data
 */
async function getCustomerByAccount(accountNumber) {
  console.log(`🔍 Looking up customer: ${accountNumber}`);
  
  try {
    // Get from database
    const query = `
      SELECT
        *
      FROM
        customers
      WHERE
        account_number = $1
    `;
    const result = await pool.query(query, [accountNumber]);
    
    if (result.rows.length > 0) {
      const customer = result.rows[0];
      
      // If clock_server_port is not set, calculate it
      if (!customer.clock_server_port) {
        customer.clock_server_port = calculatePortFromAccount(accountNumber);
        console.log(`🔢 Calculated port ${customer.clock_server_port} for ${accountNumber}`);
      }
      
      console.log(`✅ Found customer in database: ${accountNumber} with port ${customer.clock_server_port}`);
      return customer;
    }
    
    console.log(`❌ Customer not found: ${accountNumber}`);
    throw new Error(`Customer not found: ${accountNumber}`);
  } catch (error) {
    console.error(`❌ Database error looking up customer ${accountNumber}:`, error.message);
    throw error; // Don't use fallback, let the error propagate
  }
}

// ✅ Export both
module.exports = { getCustomerPorts, getCustomerByAccount, calculatePortFromAccount, pool };
