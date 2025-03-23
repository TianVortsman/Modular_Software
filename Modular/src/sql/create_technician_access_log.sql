-- Create technician access log table
CREATE TABLE IF NOT EXISTS technician_access_log (
    log_id SERIAL PRIMARY KEY,
    technician_id INTEGER NOT NULL,
    account_number VARCHAR(50) NOT NULL,
    access_token VARCHAR(255) NOT NULL,
    access_time TIMESTAMP WITHOUT TIME ZONE DEFAULT CURRENT_TIMESTAMP,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    expiration_time TIMESTAMP WITHOUT TIME ZONE
);

-- Create indexes for faster lookups
CREATE INDEX IF NOT EXISTS idx_technician_access_log_tech_id ON technician_access_log (technician_id);
CREATE INDEX IF NOT EXISTS idx_technician_access_log_account ON technician_access_log (account_number);
CREATE INDEX IF NOT EXISTS idx_technician_access_log_token ON technician_access_log (access_token);
CREATE INDEX IF NOT EXISTS idx_technician_access_log_time ON technician_access_log (access_time); 