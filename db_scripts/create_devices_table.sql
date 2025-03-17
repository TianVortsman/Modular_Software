-- Create devices table if it doesn't exist
CREATE TABLE IF NOT EXISTS devices (
    id SERIAL PRIMARY KEY,
    device_id VARCHAR(255) NOT NULL,
    serial_number VARCHAR(255) NOT NULL,
    device_name VARCHAR(255) NOT NULL,
    ip_address VARCHAR(45) NOT NULL,
    mac_address VARCHAR(255),
    username VARCHAR(255) DEFAULT 'admin',
    password VARCHAR(255) DEFAULT '12345',
    firmware_version VARCHAR(100),
    model VARCHAR(100),
    status VARCHAR(50) DEFAULT 'offline',
    last_online TIMESTAMP,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Add indexes
CREATE INDEX IF NOT EXISTS idx_devices_device_id ON devices(device_id);
CREATE INDEX IF NOT EXISTS idx_devices_serial_number ON devices(serial_number);
CREATE INDEX IF NOT EXISTS idx_devices_status ON devices(status);

-- Create device actions table to track door control and other actions
CREATE TABLE IF NOT EXISTS device_actions (
    id SERIAL PRIMARY KEY,
    device_id VARCHAR(255) NOT NULL,
    action_type VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    details JSONB,
    created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- Add index for device actions
CREATE INDEX IF NOT EXISTS idx_device_actions_device_id ON device_actions(device_id);
CREATE INDEX IF NOT EXISTS idx_device_actions_created_at ON device_actions(created_at);

-- Instructions:
-- This script should be run on each customer database (identified by account number)
-- to create the necessary tables for device management.
-- 
-- Example execution:
-- psql -U Tian -d [account_number] -f create_devices_table.sql 