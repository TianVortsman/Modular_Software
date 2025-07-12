const { Pool } = require('pg');
require('dotenv').config({ path: __dirname + '/.env' });
console.log('DB ENV:', {
  host: process.env.DB_HOST,
  port: process.env.DB_PORT,
  user: process.env.DB_USER,
  password: process.env.DB_PASS,
  database: process.env.DB_NAME
});

const pool = new Pool({
  host: process.env.DB_HOST || 'postgres',
  port: process.env.DB_PORT || 5432,
  user: process.env.DB_USER,
  password: process.env.DB_PASS,
  database: process.env.DB_NAME
});

pool.query('SELECT 1', (err, res) => {
  if (err) {
    console.error('❌ DB Connection Error:', err.message);
    console.log('👉 Details:', err);
    process.exit(1);
  } else {
    console.log('✅ DB Connection Successful!');
    pool.end();
  }
});
