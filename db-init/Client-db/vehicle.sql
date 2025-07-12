DROP TABLE IF EXISTS vehicle.vehicle_type CASCADE;
CREATE TABLE vehicle.vehicle_type (
    veh_type_id SERIAL PRIMARY KEY,
    veh_type VARCHAR(50) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Vehicle
DROP TABLE IF EXISTS vehicle.vehicle CASCADE;
CREATE TABLE vehicle.vehicle (
    veh_id SERIAL PRIMARY KEY,
    veh_type_id INTEGER REFERENCES vehicle.vehicle_type(veh_type_id),
    veh_reg_no VARCHAR(20) NOT NULL,
    veh_make VARCHAR(50) NOT NULL,
    veh_model VARCHAR(50) NOT NULL,
    veh_year INTEGER NOT NULL,
    veh_color VARCHAR(30),
    veh_vin VARCHAR(50),
    veh_engine_no VARCHAR(50),
    veh_status VARCHAR(30) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Vehicle Maintenance
DROP TABLE IF EXISTS vehicle.vehicle_maintenance CASCADE;
CREATE TABLE vehicle.vehicle_maintenance (
    maint_id SERIAL PRIMARY KEY,
    veh_id INTEGER REFERENCES vehicle.vehicle(veh_id) ON DELETE CASCADE,
    maint_date DATE NOT NULL,
    maint_type VARCHAR(50) NOT NULL,
    maint_desc TEXT,
    maint_cost NUMERIC(12,2) NOT NULL,
    maint_status VARCHAR(30) DEFAULT 'Completed',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Vehicle Fuel
DROP TABLE IF EXISTS vehicle.vehicle_fuel CASCADE;
CREATE TABLE vehicle.vehicle_fuel (
    fuel_id SERIAL PRIMARY KEY,
    veh_id INTEGER REFERENCES vehicle.vehicle(veh_id) ON DELETE CASCADE,
    fuel_date DATE NOT NULL,
    fuel_qty NUMERIC(10,2) NOT NULL,
    fuel_cost NUMERIC(12,2) NOT NULL,
    fuel_station VARCHAR(100),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Vehicle Insurance
DROP TABLE IF EXISTS vehicle.vehicle_insurance CASCADE;
CREATE TABLE vehicle.vehicle_insurance (
    ins_id SERIAL PRIMARY KEY,
    veh_id INTEGER REFERENCES vehicle.vehicle(veh_id) ON DELETE CASCADE,
    ins_company VARCHAR(100) NOT NULL,
    ins_policy_no VARCHAR(50) NOT NULL,
    ins_start_date DATE NOT NULL,
    ins_end_date DATE NOT NULL,
    ins_premium NUMERIC(12,2) NOT NULL,
    ins_status VARCHAR(30) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);

-- Vehicle License
DROP TABLE IF EXISTS vehicle.vehicle_license CASCADE;
CREATE TABLE vehicle.vehicle_license (
    lic_id SERIAL PRIMARY KEY,
    veh_id INTEGER REFERENCES vehicle.vehicle(veh_id) ON DELETE CASCADE,
    lic_number VARCHAR(50) NOT NULL,
    lic_issue_date DATE NOT NULL,
    lic_expiry_date DATE NOT NULL,
    lic_fee NUMERIC(12,2) NOT NULL,
    lic_status VARCHAR(30) DEFAULT 'Active',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    deleted_at TIMESTAMP
);