-- Active: 1741884749776@@127.0.0.1@5432@ACC002
-- Access Schema
CREATE SCHEMA IF NOT EXISTS access;

-- Devices Table
DROP TABLE IF EXISTS access.devices CASCADE;
CREATE TABLE access.devices (
    device_id VARCHAR(255) PRIMARY KEY,
    device_name VARCHAR(100) NOT NULL,
    device_type VARCHAR(50) NOT NULL,
    device_status VARCHAR(50) DEFAULT 'active',
    device_model VARCHAR(100),
    device_serial VARCHAR(100),
    device_firmware VARCHAR(50),
    device_ip VARCHAR(15),
    device_mac VARCHAR(17),
    device_location VARCHAR(255),
    device_notes TEXT,
    last_updated TIMESTAMP,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Door Config Table
DROP TABLE IF EXISTS access.door_config CASCADE;
CREATE TABLE access.door_config (
    door_id SERIAL PRIMARY KEY,
    device_id VARCHAR(255) NOT NULL REFERENCES access.devices(device_id),
    door_number INTEGER DEFAULT 1,
    door_name VARCHAR(100),
    unlock_duration INTEGER DEFAULT 5,
    door_status VARCHAR(50) DEFAULT 'closed',
    last_updated TIMESTAMP
);

-- Device Actions Table
DROP TABLE IF EXISTS access.device_actions CASCADE;
CREATE TABLE access.device_actions (
    id SERIAL PRIMARY KEY,
    device_id VARCHAR(255) NOT NULL REFERENCES access.devices(device_id),
    action_type VARCHAR(50) NOT NULL,
    status VARCHAR(50) NOT NULL,
    details JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Access Events Table
DROP TABLE IF EXISTS access.access_events CASCADE;
CREATE TABLE access.access_events (
    id SERIAL PRIMARY KEY,
    date_time TIMESTAMP NOT NULL,
    device_id VARCHAR(50),
    major_event_type INTEGER,
    minor_event_type INTEGER,
    raw_data JSONB,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);


