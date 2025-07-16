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

-- Modules table
CREATE TABLE IF NOT EXISTS public.modules (
    module_id     SERIAL PRIMARY KEY,
    module_name   VARCHAR(100) UNIQUE NOT NULL, -- e.g. 'invoicing', 'payroll'
    display_name  VARCHAR(100) NOT NULL,        -- e.g. 'Invoicing System'
    base_price    NUMERIC(10, 2) DEFAULT 0.00,
    is_active     BOOLEAN DEFAULT TRUE
);

-- Customer modules link table
CREATE TABLE IF NOT EXISTS public.customer_modules (
    id                SERIAL PRIMARY KEY,
    customer_id       INT NOT NULL REFERENCES public.customers(customer_id) ON DELETE CASCADE,
    module_id         INT NOT NULL REFERENCES public.modules(module_id) ON DELETE CASCADE,
    active            BOOLEAN DEFAULT TRUE,
    trial_mode        BOOLEAN DEFAULT FALSE,
    date_enabled      TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    expiry_date       TIMESTAMP,
    use_custom_price  BOOLEAN DEFAULT FALSE,
    custom_price      NUMERIC(10, 2),
    UNIQUE (customer_id, module_id)
);

INSERT INTO public.modules (module_name, display_name, base_price, is_active) VALUES
    ('access_control',        'Access Control',         0.00, TRUE),
    ('accounting',            'Accounting',             0.00, TRUE),
    ('crm',                   'CRM',                    0.00, TRUE),
    ('fleet_management',      'Fleet Management',       0.00, TRUE),
    ('hr',                    'HR',                     0.00, TRUE),
    ('inventory_management',  'Inventory Management',   0.00, TRUE),
    ('invoicing',             'Invoicing',              0.00, TRUE),
    ('payroll',               'Payroll',                0.00, TRUE),
    ('support',               'Support',                0.00, TRUE),
    ('time_and_attendance',   'Time and Attendance',    0.00, TRUE),
    ('projects',              'Projects',               0.00, TRUE),
    ('documents',             'Documents',              0.00, TRUE)
ON CONFLICT (module_name) DO NOTHING;
