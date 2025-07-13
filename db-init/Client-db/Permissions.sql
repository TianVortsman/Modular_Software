CREATE SCHEMA IF NOT EXISTS permissions;

CREATE TABLE Permissions.invoicing (
    user_id INT REFERENCES core.users(user_id) ON DELETE CASCADE,
    can_view BOOLEAN DEFAULT FALSE,
    can_create BOOLEAN DEFAULT FALSE,
    can_edit BOOLEAN DEFAULT FALSE,
    can_delete BOOLEAN DEFAULT FALSE,
    can_finalize BOOLEAN DEFAULT FALSE,
    can_approve BOOLEAN DEFAULT FALSE,
    PRIMARY KEY (user_id)
);