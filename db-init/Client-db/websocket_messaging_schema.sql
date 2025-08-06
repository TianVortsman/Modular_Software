-- WebSocket Messaging System Schema
-- This schema will be added to each client database

-- Settings table for email and WhatsApp configuration
CREATE TABLE IF NOT EXISTS settings.email_whatsapp (
    id SERIAL PRIMARY KEY,
    customer_id VARCHAR(50) NOT NULL UNIQUE,
    
    -- Email Settings
    email_enabled BOOLEAN DEFAULT TRUE,
    use_customer_smtp BOOLEAN DEFAULT FALSE,
    smtp_host VARCHAR(255),
    smtp_port INTEGER DEFAULT 587,
    smtp_username VARCHAR(255),
    smtp_password VARCHAR(255),
    smtp_from_email VARCHAR(255),
    smtp_from_name VARCHAR(255),
    smtp_secure BOOLEAN DEFAULT TRUE,
    
    -- WhatsApp Settings
    whatsapp_enabled BOOLEAN DEFAULT TRUE,
    use_customer_whatsapp BOOLEAN DEFAULT FALSE,
    whatsapp_api_key VARCHAR(255),
    whatsapp_phone_number_id VARCHAR(255),
    whatsapp_business_account_id VARCHAR(255),
    
    -- Webhook Settings
    webhook_enabled BOOLEAN DEFAULT FALSE,
    webhook_url VARCHAR(500),
    webhook_secret VARCHAR(255),
    
    -- Rate Limiting
    rate_limit_per_minute INTEGER DEFAULT 60,
    rate_limit_per_hour INTEGER DEFAULT 1000,
    
    -- Retry Settings
    max_retries INTEGER DEFAULT 3,
    retry_delay_seconds INTEGER DEFAULT 300,
    
    created_at TIMESTAMP DEFAULT NOW(),
    updated_at TIMESTAMP DEFAULT NOW()
);

-- WhatsApp Web sessions table
CREATE TABLE IF NOT EXISTS settings.whatsapp_sessions (
    id SERIAL PRIMARY KEY,
    customer_id VARCHAR(50) NOT NULL UNIQUE,
    status VARCHAR(50) NOT NULL, -- 'qr_ready', 'authenticated', 'ready', 'disconnected', 'auth_failed', 'logged_out'
    qr_code TEXT, -- Base64 QR code data URL
    error_message TEXT,
    last_updated TIMESTAMP DEFAULT NOW()
);

-- Event tracking table
CREATE TABLE IF NOT EXISTS settings.websocket_events (
    id SERIAL PRIMARY KEY,
    customer_id VARCHAR(50) NOT NULL,
    event_type VARCHAR(100) NOT NULL, -- e.g., 'invoice:sent', 'user:clockedIn'
    event_data JSONB NOT NULL,
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'processing', 'completed', 'failed'
    error_message TEXT,
    retry_count INTEGER DEFAULT 0,
    created_at TIMESTAMP DEFAULT NOW(),
    processed_at TIMESTAMP
);

-- Message delivery tracking
CREATE TABLE IF NOT EXISTS settings.message_deliveries (
    id SERIAL PRIMARY KEY,
    event_id INTEGER REFERENCES settings.websocket_events(id) ON DELETE CASCADE,
    customer_id VARCHAR(50) NOT NULL,
    channel VARCHAR(20) NOT NULL, -- 'email', 'whatsapp', 'webhook'
    recipient VARCHAR(255) NOT NULL,
    message_content TEXT,
    status VARCHAR(20) DEFAULT 'pending', -- 'pending', 'sent', 'delivered', 'failed'
    error_message TEXT,
    sent_at TIMESTAMP,
    delivered_at TIMESTAMP,
    retry_count INTEGER DEFAULT 0
);

-- WebSocket connection tracking
CREATE TABLE IF NOT EXISTS settings.websocket_connections (
    id SERIAL PRIMARY KEY,
    customer_id VARCHAR(50) NOT NULL,
    user_id INTEGER,
    session_id VARCHAR(255) NOT NULL,
    connection_id VARCHAR(255) NOT NULL,
    user_agent TEXT,
    ip_address INET,
    connected_at TIMESTAMP DEFAULT NOW(),
    disconnected_at TIMESTAMP,
    is_active BOOLEAN DEFAULT TRUE
);

-- Create indexes for performance
CREATE INDEX IF NOT EXISTS idx_whatsapp_sessions_customer_status ON settings.whatsapp_sessions(customer_id, status);
CREATE INDEX IF NOT EXISTS idx_whatsapp_sessions_last_updated ON settings.whatsapp_sessions(last_updated);

CREATE INDEX IF NOT EXISTS idx_events_customer_event ON settings.websocket_events(customer_id, event_type);
CREATE INDEX IF NOT EXISTS idx_events_status ON settings.websocket_events(status);
CREATE INDEX IF NOT EXISTS idx_events_created_at ON settings.websocket_events(created_at);
CREATE INDEX IF NOT EXISTS idx_events_customer_status ON settings.websocket_events(customer_id, status);

CREATE INDEX IF NOT EXISTS idx_deliveries_event_id ON settings.message_deliveries(event_id);
CREATE INDEX IF NOT EXISTS idx_deliveries_customer_channel ON settings.message_deliveries(customer_id, channel);
CREATE INDEX IF NOT EXISTS idx_deliveries_status ON settings.message_deliveries(status);
CREATE INDEX IF NOT EXISTS idx_deliveries_customer_status ON settings.message_deliveries(customer_id, status);

CREATE INDEX IF NOT EXISTS idx_connections_customer_user ON settings.websocket_connections(customer_id, user_id);
CREATE INDEX IF NOT EXISTS idx_connections_session ON settings.websocket_connections(session_id);
CREATE INDEX IF NOT EXISTS idx_connections_connection ON settings.websocket_connections(connection_id);
CREATE INDEX IF NOT EXISTS idx_connections_active ON settings.websocket_connections(is_active);
CREATE INDEX IF NOT EXISTS idx_connections_customer_active ON settings.websocket_connections(customer_id, is_active);

-- Insert default settings for existing customers (if needed)
-- This can be run after the table is created
-- INSERT INTO settings.email_whatsapp (customer_id) VALUES ('ACC002') ON CONFLICT (customer_id) DO NOTHING; 