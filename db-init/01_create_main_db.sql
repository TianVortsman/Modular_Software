-- Create the main system database
CREATE DATABASE modular_system;
\connect modular_system

-- Import the schema from the Main-db folder
\i /docker-entrypoint-initdb.d/Main-db/modular_system.sql
