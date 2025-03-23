const http = require('http');
const { Pool } = require('pg');
const mainDbConnection = require('../db/mainDb');

// Main pool for getting connection details
const mainPool = new Pool({
  user: process.env.DB_USER || 'postgres',
  host: process.env.DB_HOST || 'host.docker.internal',
  password: process.env.DB_PASS || 'postgres',
  port: process.env.DB_PORT || 5432,
  database: process.env.DB_NAME || 'postgres'
});

// Customer connection pools cache
const customerPools = {};

// Get or create a database connection for the customer
function getCustomerPool(accountNumber) {
  if (customerPools[accountNumber]) {
    return customerPools[accountNumber];
  }
  
  // Format account number as database name (e.g., ACC005)
  const dbName = accountNumber;
  
  // Create a new pool for this customer
  const pool = new Pool({
    user: process.env.DB_USER || 'postgres',
    host: process.env.DB_HOST || 'host.docker.internal',
    password: process.env.DB_PASS || 'postgres',
    port: process.env.DB_PORT || 5432,
    database: dbName
  });
  
  // Cache the pool for future use
  customerPools[accountNumber] = pool;
  console.log(`📊 Created database connection for customer: ${accountNumber} (${dbName})`);
  
  return pool;
}

/**
 * Extract the event_log data from multipart form data
 * @param {string} body - The raw request body
 * @return {object|null} - Parsed JSON object or null if not found/valid
 */
function extractEventLogFromMultipart(body) {
  console.log('🔍 Extracting event_log from multipart data');
  
  // Handle different possible multipart formats
  const match = body.match(/name="event_log"\s*\r?\n\r?\n([\s\S]+?)\r?\n--/);
  if (!match) {
    console.error('❌ Failed to find event_log part in multipart data');
    return null;
  }

  try {
    const jsonData = JSON.parse(match[1]);
    console.log('✅ Successfully parsed event_log JSON');
    return jsonData;
  } catch (e) {
    console.error("❌ Failed to parse event_log JSON:", e.message);
    return null;
  }
}

/**
 * Process clocking data and write to the appropriate database tables
 * @param {string} accountNumber - Customer account number
 * @param {string} rawBody - Raw HTTP request body
 * @return {Promise<boolean>} - Success or failure
 */
async function processClocking(accountNumber, rawBody) {
  try {
    console.log(`\n🔍 Processing clocking data for ${accountNumber}`);
    
    // Extract event data from multipart form data
    const data = extractEventLogFromMultipart(rawBody);
    
    // If extracting from multipart fails, try plain JSON
    if (!data) {
      try {
        console.log('⚠️ Trying to parse as plain JSON');
        const jsonData = JSON.parse(rawBody);
        console.log('✅ Successfully parsed as plain JSON');
        return await processEventData(accountNumber, jsonData);
      } catch (e) {
        console.error('❌ Failed to parse as plain JSON:', e.message);
        console.log('📄 Raw data received:');
        console.log(rawBody.substring(0, 500) + (rawBody.length > 500 ? '...' : ''));
        throw new Error("Missing or invalid payload data");
      }
    } else {
      return await processEventData(accountNumber, data);
    }
  } catch (error) {
    console.error(`❌ Error processing clocking data: ${error.message}`);
    console.error(error.stack);
    return false;
  }
}

/**
 * Process event data and write to database
 * @param {string} accountNumber - Customer account number
 * @param {object} data - Parsed event data
 * @return {Promise<boolean>} - Success or failure
 */
async function processEventData(accountNumber, data) {
  try {
    console.log('📝 Processing event data');
    console.log('📄 Full data object:', JSON.stringify(data, null, 2));
    
    // Extract event information
    const event = data.AccessControllerEvent || {};
    
    // Try multiple potential locations for the clock number
    let clockNumber = '';
    
    // Look for the clock number in different possible locations
    if (event.employeeNoString) {
      clockNumber = event.employeeNoString;
      console.log('📌 Found clock number in event.employeeNoString');
    } else if (event.employeeNo) {
      clockNumber = event.employeeNo.toString();
      console.log('📌 Found clock number in event.employeeNo');
    } else if (data.employeeNoString) {
      clockNumber = data.employeeNoString;
      console.log('📌 Found clock number in data.employeeNoString');
    } else if (data.employeeNo) {
      clockNumber = data.employeeNo.toString();
      console.log('📌 Found clock number in data.employeeNo');
    } else if (event.cardNo) {
      clockNumber = event.cardNo;
      console.log('📌 Found clock number in event.cardNo');
    } else if (data.cardNo) {
      clockNumber = data.cardNo;
      console.log('📌 Found clock number in data.cardNo');
    }
    
    // If we have an access event with user info
    if (!clockNumber && event.userInfo && event.userInfo.employeeNoString) {
      clockNumber = event.userInfo.employeeNoString;
      console.log('📌 Found clock number in event.userInfo.employeeNoString');
    }
    
    const deviceId = data.deviceID || event.deviceID || '';
    const dateTime = data.dateTime || new Date().toISOString();
    const verifyMode = event.currentVerifyMode || '';
    const verifyStatus = event.attendanceStatus || '';
    const majorEventType = event.majorEventType || data.majorEventType || null;
    const minorEventType = event.subEventType || data.minorEventType || null;
    
    console.log('📊 Event details:');
    console.log(`   Clock Number: ${clockNumber || 'NONE'}`);
    console.log(`   Device ID: ${deviceId}`);
    console.log(`   Date/Time: ${dateTime}`);
    console.log(`   Event Type: ${majorEventType}/${minorEventType}`);
    
    // Get customer database connection
    console.log(`🔌 Getting database connection for ${accountNumber}`);
    const pool = getCustomerPool(accountNumber);
    
    // Test the database connection
    try {
      const testResult = await pool.query('SELECT NOW()');
      console.log(`✅ Database connection successful: ${testResult.rows[0].now}`);
    } catch (dbErr) {
      console.error(`❌ Database connection failed: ${dbErr.message}`);
      throw new Error(`Database connection failed: ${dbErr.message}`);
    }
    
    // If clockNumber exists, treat as attendance punch
    if (clockNumber) {
      console.log(`🔍 Looking up employee with clock number: ${clockNumber}`);
      
      // Check if there's any numeric part in the clock number
      // In case it comes in a format like "123;#Admin" from certain devices
      if (clockNumber.includes(';')) {
        clockNumber = clockNumber.split(';')[0];
        console.log(`⚠️ Found semicolon in clock number, using part before semicolon: ${clockNumber}`);
      }
      
      // First check what columns actually exist in the employees table
      const employeeColumnsResult = await pool.query(`
        SELECT column_name 
        FROM information_schema.columns 
        WHERE table_name = 'employees'
      `);
      
      const employeeColumns = employeeColumnsResult.rows.map(row => row.column_name);
      console.log(`📋 Available columns in employees table: ${employeeColumns.join(', ')}`);
      
      // Build the SELECT query based on available columns
      let selectColumns = ['employee_id'];
      
      // Add employee name (constructed from first_name and last_name)
      selectColumns.push(`CONCAT(first_name, ' ', last_name) as employee_name`);
      
      // Check employees table for a matching clock number (trying both string and numeric formats)
      const employeeResult = await pool.query(`
        SELECT 
          ${selectColumns.join(', ')}
        FROM 
          employees 
        WHERE 
          clock_number = $1 
          OR clock_number = $2
      `, [clockNumber, parseInt(clockNumber)]);
      
      console.log(`📊 Employee lookup result: ${employeeResult.rows.length} rows found`);
      
      if (employeeResult.rows.length > 0) {
        // Employee found - write to attendance_records
        const employee = employeeResult.rows[0];
        
        console.log(`✅ Employee found for clock number ${clockNumber}, writing to attendance_records`);
        console.log(`   Employee ID: ${employee.employee_id}`);
        console.log(`   Employee Name: ${employee.employee_name}`);
        
        try {
          // Get all available columns in attendance_records
          const columnsResult = await pool.query(`
            SELECT column_name 
            FROM information_schema.columns 
            WHERE table_name = 'attendance_records'
            ORDER BY ordinal_position
          `);
          
          console.log('📋 Available columns in attendance_records:');
          const columns = columnsResult.rows.map(row => row.column_name);
          console.log(`   ${columns.join(', ')}`);
          
          // Get current date/time
          const now = new Date();
          const dateStr = now.toISOString().split('T')[0]; // YYYY-MM-DD
          
          // Determine if this is a clock-in or clock-out based on attendanceStatus
          // This should take precedence over the majorEventType
          let isClockIn = false;
          let isClockOut = false;
          
          // First check attendanceStatus (from the event)
          if (verifyStatus === 'checkIn') {
            isClockIn = true;
            console.log('📌 Identified as CHECK IN based on attendanceStatus: checkIn');
          } else if (verifyStatus === 'checkOut') {
            isClockOut = true;
            console.log('📌 Identified as CHECK OUT based on attendanceStatus: checkOut');
          } 
          // If attendanceStatus is not set, fall back to majorEventType
          else if (majorEventType === 5) {
            isClockIn = true;
            console.log('📌 Falling back to majorEventType 5 for CHECK IN');
          } else if (majorEventType === 6) {
            isClockOut = true;
            console.log('📌 Falling back to majorEventType 6 for CHECK OUT');
          } else {
            console.log(`📌 Neither checkIn nor checkOut identified (attendanceStatus: ${verifyStatus}, majorEventType: ${majorEventType})`);
            console.log('📌 Will store as generic clock_time');
          }
          
          // Build column list and values array based on columns present in the table
          let columnsList = ['employee_id', 'date', 'date_time', 'clock_number', 'device_id', 
                             'verify_mode', 'verify_status', 'major_event_type', 'minor_event_type',
                             'status', 'notes'];
          let valuesPlaceholders = ['$1', '$2', '$3', '$4', '$5', '$6', '$7', '$8', '$9', '$10', '$11'];
          let values = [
            employee.employee_id,
            dateStr,
            dateTime,
            clockNumber,
            deviceId,
            verifyMode,
            verifyStatus,
            majorEventType,
            minorEventType,
            'Present',
            `Recorded from device ${deviceId} at ${new Date().toLocaleString()}`
          ];
          
          // Add time_in, time_out, or clock_time as applicable
          if (isClockIn && columns.includes('time_in')) {
            columnsList.push('time_in');
            valuesPlaceholders.push(`$${values.length + 1}`);
            values.push(dateTime);
          } else if (isClockOut && columns.includes('time_out')) {
            columnsList.push('time_out');
            valuesPlaceholders.push(`$${values.length + 1}`);
            values.push(dateTime);
          } else if (columns.includes('clock_time')) {
            // If it's neither a valid clock-in nor clock-out, store as generic clock_time
            columnsList.push('clock_time');
            valuesPlaceholders.push(`$${values.length + 1}`);
            values.push(dateTime);
            console.log('📌 Storing as generic clock_time');
          }
          
          // Build and execute the insert query
          const insertQuery = `
            INSERT INTO attendance_records 
            (${columnsList.join(', ')})
            VALUES 
            (${valuesPlaceholders.join(', ')})
            RETURNING attendance_id
          `;
          
          console.log(`📝 Executing insert query with ${values.length} parameters`);
          
          const insertResult = await pool.query(insertQuery, values);
          
          console.log(`✅ Successfully recorded attendance with ID: ${insertResult.rows[0].attendance_id}`);
        } catch (insertErr) {
          console.error(`❌ Error inserting record: ${insertErr.message}`);
          
          try {
            // Simpler fallback query with only the essential fields
            console.log('⚠️ Trying simplified insert with just required fields');
            
            const basicResult = await pool.query(`
              INSERT INTO attendance_records 
              (employee_id, date, status, clock_number, date_time)
              VALUES ($1, $2, $3, $4, $5)
              RETURNING attendance_id
            `, [
              employee.employee_id,
              new Date(dateTime).toISOString().split('T')[0],
              'Present',
              clockNumber,
              dateTime
            ]);
            
            console.log(`✅ Successfully recorded simplified attendance with ID: ${basicResult.rows[0].attendance_id}`);
          } catch (fallbackErr) {
            console.error(`❌ Even simplified insert failed: ${fallbackErr.message}`);
            throw fallbackErr; // Let the outer catch handle this
          }
        }
      } else {
        // IMPORTANT: Check the structure of clock_number
        console.log(`⚠️ No employee found for clock number ${clockNumber}, checking database schema`);
        
        try {
          // Check the data type and values in the clock_number column
          const columnInfoResult = await pool.query(`
            SELECT 
              column_name, 
              data_type 
            FROM 
              information_schema.columns 
            WHERE 
              table_name = 'employees' AND 
              column_name = 'clock_number'
          `);
          
          if (columnInfoResult.rows.length > 0) {
            const columnInfo = columnInfoResult.rows[0];
            console.log(`📊 Column info for clock_number: ${columnInfo.data_type}`);
            
            // Check some sample values
            const sampleResult = await pool.query(`
              SELECT 
                employee_id, 
                clock_number, 
                CONCAT(first_name, ' ', last_name) as employee_name
              FROM 
                employees 
              LIMIT 5
            `);
            
            console.log('📋 Sample employee records:');
            sampleResult.rows.forEach((row, i) => {
              console.log(`   Row ${i+1}: ID=${row.employee_id}, Clock=${row.clock_number}, Name=${row.employee_name}`);
            });
          }
        } catch (schemaErr) {
          console.error(`❌ Error checking schema: ${schemaErr.message}`);
        }
        
        // Employee not found - write to unknown_clockings
        console.log(`⚠️ Writing to unknown_clockings for clock number: ${clockNumber}`);
        
        const insertResult = await pool.query(`
          INSERT INTO unknown_clockings 
          (date, date_time, clock_number, device_id, 
           verify_mode, verify_status, major_event_type, minor_event_type, 
           raw_data, processed)
          VALUES 
          ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)
          RETURNING id
        `, [
          new Date(dateTime).toISOString().split('T')[0], // Just the date part
          dateTime,
          clockNumber,
          deviceId,
          verifyMode,
          verifyStatus,
          majorEventType,
          minorEventType,
          JSON.stringify(data),
          false // Not processed
        ]);
        
        console.log(`✅ Successfully recorded unknown clocking with ID: ${insertResult.rows[0].id}`);
      }
    } else {
      // This is an access-only event (e.g., door open/close)
      console.log(`🔐 Access Event Logged (no clock number): [${majorEventType}/${minorEventType}]`);
      
      // Try to create access_events table if it doesn't exist
      try {
        await pool.query(`
          CREATE TABLE IF NOT EXISTS access_events (
            id SERIAL PRIMARY KEY,
            date_time TIMESTAMP NOT NULL,
            device_id VARCHAR(50),
            major_event_type INTEGER,
            minor_event_type INTEGER,
            raw_data TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
          )
        `);
        
        await pool.query(`
          INSERT INTO access_events
          (date_time, device_id, major_event_type, minor_event_type, raw_data)
          VALUES ($1, $2, $3, $4, $5)
          RETURNING id
        `, [
          dateTime,
          deviceId,
          majorEventType,
          minorEventType,
          JSON.stringify(data)
        ]);
        
        console.log(`✅ Successfully recorded access event`);
      } catch (accessErr) {
        console.error(`❌ Error recording access event: ${accessErr.message}`);
        console.log(`⚠️ Falling back to unknown_clockings table`);
        
        // Fallback to unknown_clockings if access_events table doesn't exist
        await pool.query(`
          INSERT INTO unknown_clockings 
          (date, date_time, clock_number, device_id, 
           verify_mode, verify_status, major_event_type, minor_event_type, 
           raw_data, processed)
          VALUES 
          ($1, $2, $3, $4, $5, $6, $7, $8, $9, $10)
        `, [
          new Date(dateTime).toISOString().split('T')[0],
          dateTime,
          'ACCESS_EVENT', // Use a special value to indicate this is an access event
          deviceId,
          verifyMode,
          verifyStatus,
          majorEventType,
          minorEventType,
          JSON.stringify(data),
          false
        ]);
        
        console.log(`✅ Recorded access event to unknown_clockings table instead`);
      }
    }
    
    return true;
  } catch (error) {
    console.error(`❌ Error processing event data: ${error.message}`);
    console.error(error.stack);
    return false;
  }
}

function createCustomerServer(port, accountNumber) {
  const server = http.createServer((req, res) => {
    let rawData = '';

    req.on('data', chunk => {
      console.log(`📥 Received data chunk on port ${port} for ${accountNumber}`);
      rawData += chunk;
    });

    req.on('end', async () => {
      console.log(`\n📥 Raw data received on port ${port} for ${accountNumber}`);
      console.log(`   URL: ${req.url}`);
      console.log(`   Method: ${req.method}`);
      console.log(`   Content-Type: ${req.headers['content-type'] || 'none'}`);
      
      // Always respond with success to the device to avoid it retrying
      // We'll process the data asynchronously after sending response
      res.writeHead(200, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ success: true }));
      
      // Process the data after responding to avoid timeouts
      try {
        const success = await processClocking(accountNumber, rawData);
        
        if (success) {
          console.log(`✅ Successfully processed clocking data for ${accountNumber}`);
        } else {
          console.error(`❌ Failed to process clocking data for ${accountNumber}`);
        }
      } catch (error) {
        console.error(`❌ Error in clocking processing: ${error.message}`);
      }
    });

    req.on('error', err => {
      console.error(`❌ Error receiving data on port ${port}:`, err);
      
      // Respond with OK anyway to avoid device retries
      res.writeHead(200, { 'Content-Type': 'application/json' });
      res.end(JSON.stringify({ success: false, error: err.message }));
    });
  });

  // Listen on all interfaces (0.0.0.0) to ensure the container can receive external connections
  server.listen(port, '0.0.0.0', () => {
    console.log(`✅ Server for ${accountNumber} listening on port ${port} (all interfaces)`);
  });
  
  // Return the server instance so it can be closed later
  return server;
}

module.exports = createCustomerServer;
