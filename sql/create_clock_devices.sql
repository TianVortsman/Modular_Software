-- Create clock_devices table to track Hikvision devices
CREATE TABLE IF NOT EXISTS clock_devices (
    id SERIAL PRIMARY KEY,
    account_id INTEGER NOT NULL,
    device_id VARCHAR(50) NOT NULL,
    device_name VARCHAR(100),
    ip_address VARCHAR(50),
    port INTEGER,
    model VARCHAR(100),
    firmware_version VARCHAR(50),
    serial_number VARCHAR(100),
    status VARCHAR(20) CHECK (status IN ('online', 'offline', 'unknown')) DEFAULT 'unknown',
    last_heartbeat TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (account_id) REFERENCES customers(id) ON DELETE CASCADE,
    UNIQUE (account_id, device_id)
);

-- Create index for faster lookups
CREATE INDEX IF NOT EXISTS idx_clock_devices_device_id ON clock_devices(device_id);
CREATE INDEX IF NOT EXISTS idx_clock_devices_account_id ON clock_devices(account_id);

-- Create a function to update the updated_at timestamp
CREATE OR REPLACE FUNCTION update_timestamp()
RETURNS TRIGGER AS $$
BEGIN
    NEW.updated_at = CURRENT_TIMESTAMP;
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create a trigger to update the timestamp
CREATE TRIGGER update_clock_devices_timestamp
BEFORE UPDATE ON clock_devices
FOR EACH ROW
EXECUTE FUNCTION update_timestamp();

-- Create a function to update device heartbeat
CREATE OR REPLACE FUNCTION update_device_heartbeat()
RETURNS TRIGGER AS $$
DECLARE
    account_id INTEGER;
BEGIN
    -- Get the account_id for the device
    SELECT c.id INTO account_id
    FROM customers c
    WHERE c.account_number = current_database();
    
    -- Check if the device exists
    IF EXISTS (SELECT 1 FROM clock_devices WHERE device_id = NEW.device_id AND account_id = account_id) THEN
        -- Update the last heartbeat
        UPDATE clock_devices 
        SET last_heartbeat = CURRENT_TIMESTAMP, status = 'online'
        WHERE device_id = NEW.device_id AND account_id = account_id;
    ELSE
        -- Insert a new device record
        INSERT INTO clock_devices (account_id, device_id, device_name, status, last_heartbeat)
        VALUES (account_id, NEW.device_id, CONCAT('Device ', NEW.device_id), 'online', CURRENT_TIMESTAMP);
    END IF;
    
    RETURN NEW;
END;
$$ LANGUAGE plpgsql;

-- Create a trigger to update the device heartbeat
CREATE TRIGGER update_device_heartbeat_trigger
AFTER INSERT ON attendance_records
FOR EACH ROW
EXECUTE FUNCTION update_device_heartbeat(); 