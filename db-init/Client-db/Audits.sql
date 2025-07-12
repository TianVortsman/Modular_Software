CREATE SCHEMA IF NOT EXISTS audit;

CREATE TABLE audit.user_actions (
    action_id SERIAL PRIMARY KEY,

    user_id INT NOT NULL REFERENCES core.users(user_id) ON DELETE CASCADE,

    module VARCHAR(50) NOT NULL,        -- e.g. 'invoicing'
    action VARCHAR(100) NOT NULL,       -- e.g. 'update_invoice'
    related_type VARCHAR(50),           -- e.g. 'invoice'
    related_id INT,                     -- e.g. invoice_id

    old_data JSONB,                     -- before the change
    new_data JSONB,                     -- after the change

    details TEXT,                       -- optional string summary

    ip_address VARCHAR(45),
    user_agent TEXT,
    session_id VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

