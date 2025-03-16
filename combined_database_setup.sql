-- Combined Database Setup Script
-- This script combines both the ERP and Time & Attendance systems
-- Please log an issue if you find any bugs
ROLLBACK;
BEGIN;

-- Enable extensions
CREATE EXTENSION IF NOT EXISTS "uuid-ossp";
CREATE EXTENSION IF NOT EXISTS "pgcrypto";

----------------------------------------
-- PART 1: CREATE ALL TABLES
----------------------------------------

-- Core Business Tables
CREATE TABLE IF NOT EXISTS public.address (
    addr_id integer NOT NULL GENERATED ALWAYS AS IDENTITY,
    addr_line_1 character varying(50) NOT NULL,
    addr_line_2 character varying(50),
    suburb character varying(35) NOT NULL,
    city character varying(35) NOT NULL,
    province character varying(50) NOT NULL,
    country character varying(50) NOT NULL,
    postcode character varying(10) NOT NULL,
    updated_by integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT address_pkey PRIMARY KEY (addr_id)
);

CREATE TABLE IF NOT EXISTS public.company (
    company_id serial NOT NULL,
    company_name character varying(255) NOT NULL,
    company_tax_no character varying(50) NOT NULL,
    company_regis_no character varying(50) NOT NULL,
    company_type character varying(100),
    industry character varying(100),
    website character varying(255),
    company_tell character varying(20),
    company_email character varying(100),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT company_pkey PRIMARY KEY (company_id)
);

CREATE TABLE IF NOT EXISTS public.company_address (
    company_address_id serial NOT NULL,
    company_id integer NOT NULL,
    addr_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT company_address_pkey PRIMARY KEY (company_address_id)
);

CREATE TABLE IF NOT EXISTS public.company_contacts (
    contact_id serial NOT NULL,
    company_id integer NOT NULL,
    contact_name character varying(255) NOT NULL,
    contact_email character varying(255),
    contact_phone character varying(50),
    "position" character varying(100),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT company_contacts_pkey PRIMARY KEY (contact_id)
);

CREATE TABLE IF NOT EXISTS public.customer_type (
    cust_type_id serial NOT NULL,
    cust_type character varying(50) NOT NULL DEFAULT 'Client',
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT customer_type_pkey PRIMARY KEY (cust_type_id)
);

CREATE TABLE IF NOT EXISTS public.customers (
    cust_id serial NOT NULL,
    cust_fname character varying(30) NOT NULL,
    cust_lname character varying(30) NOT NULL,
    cust_init character varying(5) NOT NULL,
    cust_title character varying(15) NOT NULL,
    cust_type_id integer,
    cust_email character varying(255) NOT NULL,
    cust_tel character varying(20),
    cust_cell character varying(20),
    date_created timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by integer,
    cust_status character varying(30) DEFAULT 'All Paid',
    company_id integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT customers_pkey PRIMARY KEY (cust_id)
);

CREATE TABLE IF NOT EXISTS public.customer_address (
    customer_address_id serial NOT NULL,
    cust_id integer NOT NULL,
    addr_id integer,
    updated_by integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT customer_address_pkey PRIMARY KEY (customer_address_id)
);

-- Product and Supplier Tables
CREATE TABLE IF NOT EXISTS public.product_category (
    prod_cat_id serial NOT NULL,
    prod_cat_name character varying(50) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT product_category_pkey PRIMARY KEY (prod_cat_id)
);

CREATE TABLE IF NOT EXISTS public.product_type (
    prod_type_id serial NOT NULL,
    prod_type_name character varying(50) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT product_type_pkey PRIMARY KEY (prod_type_id)
);

CREATE TABLE IF NOT EXISTS public.products (
    prod_id serial NOT NULL,
    prod_name character varying(100) NOT NULL,
    prod_desc text,
    prod_cat_id integer,
    prod_type_id integer,
    prod_price numeric(10,2) NOT NULL,
    prod_quantity integer NOT NULL DEFAULT 0,
    prod_status character varying(30) DEFAULT 'Available',
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT products_pkey PRIMARY KEY (prod_id)
);

CREATE TABLE IF NOT EXISTS public.supplier_type (
    supp_type_id serial NOT NULL,
    supp_type character varying(50) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT supplier_type_pkey PRIMARY KEY (supp_type_id)
);

CREATE TABLE IF NOT EXISTS public.suppliers (
    supp_id serial NOT NULL,
    company_id integer NOT NULL,
    supp_type_id integer,
    supp_status character varying(30) DEFAULT 'Active',
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT suppliers_pkey PRIMARY KEY (supp_id)
);

CREATE TABLE IF NOT EXISTS public.supplier_products (
    supp_prod_id serial NOT NULL,
    supp_id integer NOT NULL,
    prod_id integer NOT NULL,
    supp_prod_price numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT supplier_products_pkey PRIMARY KEY (supp_prod_id)
);

-- Sales and Order Tables
CREATE TABLE IF NOT EXISTS public.order_status (
    order_status_id serial NOT NULL,
    status_name character varying(50) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT order_status_pkey PRIMARY KEY (order_status_id)
);

CREATE TABLE IF NOT EXISTS public.payment_method (
    payment_method_id serial NOT NULL,
    method_name character varying(50) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT payment_method_pkey PRIMARY KEY (payment_method_id)
);

CREATE TABLE IF NOT EXISTS public.payment_status (
    payment_status_id serial NOT NULL,
    status_name character varying(50) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT payment_status_pkey PRIMARY KEY (payment_status_id)
);
-- Part 3: Sales and Order Tables

CREATE TABLE IF NOT EXISTS public.customer_orders (
    order_id serial NOT NULL,
    cust_id integer NOT NULL,
    order_date timestamp without time zone DEFAULT now(),
    order_status_id integer NOT NULL,
    payment_method_id integer,
    payment_status_id integer,
    order_total numeric(10,2) NOT NULL DEFAULT 0.00,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT customer_orders_pkey PRIMARY KEY (order_id)
);

CREATE TABLE IF NOT EXISTS public.order_items (
    order_item_id serial NOT NULL,
    order_id integer NOT NULL,
    prod_id integer NOT NULL,
    quantity integer NOT NULL DEFAULT 1,
    unit_price numeric(10,2) NOT NULL,
    line_total numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT order_items_pkey PRIMARY KEY (order_item_id)
);

CREATE TABLE IF NOT EXISTS public.sales_order (
    sales_order_id serial NOT NULL,
    order_id integer NOT NULL,
    sales_date timestamp without time zone DEFAULT now(),
    sales_total numeric(10,2) NOT NULL DEFAULT 0.00,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT sales_order_pkey PRIMARY KEY (sales_order_id)
);

CREATE TABLE IF NOT EXISTS public.sales_items (
    sales_item_id serial NOT NULL,
    sales_order_id integer NOT NULL,
    prod_id integer NOT NULL,
    quantity integer NOT NULL DEFAULT 1,
    unit_price numeric(10,2) NOT NULL,
    line_total numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT sales_items_pkey PRIMARY KEY (sales_item_id)
);
-- Part 4: Time and Attendance Tables

CREATE TABLE IF NOT EXISTS public.employees (
    employee_id serial NOT NULL,
    first_name varchar(100) NOT NULL,
    last_name varchar(100) NOT NULL,
    email varchar(255),
    phone_number varchar(20),
    hire_date date,
    division varchar(100),
    group_name varchar(100),
    department varchar(100),
    cost_center varchar(100),
    position varchar(100),
    employee_number varchar(50) NOT NULL,
    status varchar(20) DEFAULT 'active',
    employment_type varchar(20) DEFAULT 'Permanent',
    work_schedule_type varchar(20) DEFAULT 'Open',
    biometric_id varchar(100),
    emergency_contact_name varchar(100),
    emergency_contact_phone varchar(20),
    address text,
    clock_number integer NOT NULL,
    created_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp with time zone DEFAULT CURRENT_TIMESTAMP,
    deleted_at timestamp with time zone,
    CONSTRAINT employees_pkey PRIMARY KEY (employee_id),
    CONSTRAINT employees_email_key UNIQUE (email),
    CONSTRAINT employees_employee_number_key UNIQUE (employee_number),
    CONSTRAINT employees_biometric_id_key UNIQUE (biometric_id),
    CONSTRAINT employees_status_check CHECK (status IN ('active', 'inactive', 'terminated')),
    CONSTRAINT employees_employment_type_check CHECK (employment_type IN ('Permanent', 'Contract')),
    CONSTRAINT employees_work_schedule_type_check CHECK (work_schedule_type IN ('Open', 'Fixed', 'Rotating'))
);

CREATE TABLE IF NOT EXISTS public.shifts (
    shift_id serial NOT NULL,
    shift_name character varying(50) NOT NULL,
    start_time time NOT NULL,
    end_time time NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT shifts_pkey PRIMARY KEY (shift_id)
);

CREATE TABLE IF NOT EXISTS public.attendance_records (
    attendance_id serial NOT NULL,
    employee_id integer NOT NULL,
    shift_id integer NOT NULL,
    date date NOT NULL,
    time_in timestamp without time zone,
    time_out timestamp without time zone,
    status character varying(20) NOT NULL DEFAULT 'Present',
    notes text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT attendance_records_pkey PRIMARY KEY (attendance_id)
);

CREATE TABLE IF NOT EXISTS public.break_types (
    break_type_id serial NOT NULL,
    break_name character varying(50) NOT NULL,
    duration_minutes integer NOT NULL,
    is_paid boolean NOT NULL DEFAULT true,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT break_types_pkey PRIMARY KEY (break_type_id)
);

CREATE TABLE IF NOT EXISTS public.break_records (
    break_id serial NOT NULL,
    attendance_id integer NOT NULL,
    break_type_id integer NOT NULL,
    start_time timestamp without time zone NOT NULL,
    end_time timestamp without time zone,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT break_records_pkey PRIMARY KEY (break_id)
);

CREATE TABLE IF NOT EXISTS public.leave_types (
    leave_type_id serial NOT NULL,
    leave_name character varying(50) NOT NULL,
    description text,
    is_paid boolean NOT NULL DEFAULT true,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT leave_types_pkey PRIMARY KEY (leave_type_id)
);

CREATE TABLE IF NOT EXISTS public.leave_balances (
    balance_id serial NOT NULL,
    employee_id integer NOT NULL,
    leave_type_id integer NOT NULL,
    year integer NOT NULL,
    total_days numeric(5,2) NOT NULL,
    used_days numeric(5,2) NOT NULL DEFAULT 0,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT leave_balances_pkey PRIMARY KEY (balance_id)
);

CREATE TABLE IF NOT EXISTS public.leave_requests (
    request_id serial NOT NULL,
    employee_id integer NOT NULL,
    leave_type_id integer NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    total_days numeric(5,2) NOT NULL,
    status character varying(20) NOT NULL DEFAULT 'Pending',
    approved_by integer,
    notes text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT leave_requests_pkey PRIMARY KEY (request_id)
);

CREATE TABLE IF NOT EXISTS public.overtime_categories (
    category_id serial NOT NULL,
    category_name character varying(50) NOT NULL,
    rate_multiplier numeric(3,2) NOT NULL,
    description text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT overtime_categories_pkey PRIMARY KEY (category_id)
);

CREATE TABLE IF NOT EXISTS public.overtime_requests (
    overtime_id serial NOT NULL,
    employee_id integer NOT NULL,
    category_id integer NOT NULL,
    date date NOT NULL,
    hours numeric(4,2) NOT NULL,
    reason text NOT NULL,
    status character varying(20) NOT NULL DEFAULT 'Pending',
    approved_by integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT overtime_requests_pkey PRIMARY KEY (overtime_id)
);

CREATE TABLE IF NOT EXISTS public.holidays (
    holiday_id serial NOT NULL,
    holiday_name character varying(100) NOT NULL,
    date date NOT NULL,
    description text,
    is_paid boolean NOT NULL DEFAULT true,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT holidays_pkey PRIMARY KEY (holiday_id)
);

CREATE TABLE IF NOT EXISTS public.schedule_templates (
    template_id serial NOT NULL,
    template_name character varying(100) NOT NULL,
    position_id integer NOT NULL,
    shift_id integer NOT NULL,
    day_of_week integer NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT schedule_templates_pkey PRIMARY KEY (template_id)
);

CREATE TABLE IF NOT EXISTS public.monthly_rosters (
    roster_id serial NOT NULL,
    employee_id integer NOT NULL,
    month integer NOT NULL,
    year integer NOT NULL,
    created_by integer NOT NULL,
    status character varying(20) NOT NULL DEFAULT 'Draft',
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT monthly_rosters_pkey PRIMARY KEY (roster_id)
);

CREATE TABLE IF NOT EXISTS public.weekly_hours (
    hours_id serial NOT NULL,
    employee_id integer NOT NULL,
    week_start_date date NOT NULL,
    regular_hours numeric(5,2) NOT NULL DEFAULT 0,
    overtime_hours numeric(5,2) NOT NULL DEFAULT 0,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT weekly_hours_pkey PRIMARY KEY (hours_id)
);
-- Part 5: Additional ERP/Invoice Tables

CREATE TABLE IF NOT EXISTS public.sale_person (
    sale_pers_id serial NOT NULL,
    sale_pers_fname character varying(30) NOT NULL,
    sale_pers_lname character varying(30) NOT NULL,
    sales_pers_no text NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT sale_person_pkey PRIMARY KEY (sale_pers_id)
);

CREATE TABLE IF NOT EXISTS public.sale (
    sale_id serial NOT NULL,
    sale_pers_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT sale_pkey PRIMARY KEY (sale_id)
);

CREATE TABLE IF NOT EXISTS public.quote_status (
    status_id serial NOT NULL,
    status_name character varying(50) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT quote_status_pkey PRIMARY KEY (status_id)
);

CREATE TABLE IF NOT EXISTS public.quote (
    quote_id serial NOT NULL,
    cust_id integer NOT NULL,
    quote_date date NOT NULL,
    expiration_date date NOT NULL,
    total_amount numeric(12,0) NOT NULL,
    status character varying(30) NOT NULL,
    quote_status_id integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT quote_pkey PRIMARY KEY (quote_id)
);

CREATE TABLE IF NOT EXISTS public.quote_items (
    quote_item_id serial NOT NULL,
    quote_id integer NOT NULL,
    prod_id integer NOT NULL,
    qty integer NOT NULL,
    unit_price numeric(11,0) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT quote_items_pkey PRIMARY KEY (quote_item_id)
);

CREATE TABLE IF NOT EXISTS public.purchase_payment (
    purchase_paym_id serial NOT NULL,
    po_id integer NOT NULL,
    paym_date date NOT NULL,
    paym_amount numeric(12,0) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT purchase_payment_pkey PRIMARY KEY (purchase_paym_id)
);

CREATE TABLE IF NOT EXISTS public.sales_payment (
    sales_paym_id serial NOT NULL,
    sales_order_id integer NOT NULL,
    paym_date date NOT NULL,
    paym_amount numeric(12,0) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT sales_payment_pkey PRIMARY KEY (sales_paym_id)
);

CREATE TABLE IF NOT EXISTS public.product_supplier_price_history (
    id serial NOT NULL,
    prod_id integer NOT NULL,
    suppl_id integer NOT NULL,
    purch_price numeric(12,2) NOT NULL,
    start_date timestamp without time zone DEFAULT now(),
    end_date timestamp without time zone,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    CONSTRAINT product_supplier_price_history_pkey PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS public.vehicle (
    veh_id serial NOT NULL,
    make character varying(100) NOT NULL,
    model character varying(100) NOT NULL,
    year integer NOT NULL,
    vin character varying(50) NOT NULL,
    regis_number character varying(50),
    mileage numeric(12,0),
    status character varying(50),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_pkey PRIMARY KEY (veh_id)
);

CREATE TABLE IF NOT EXISTS public.vehicle_documents (
    doc_id serial NOT NULL,
    veh_id integer NOT NULL,
    doc_type character varying(50) NOT NULL,
    doc_name character varying(100) NOT NULL,
    doc_url character varying(255),
    upload_date date NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_documents_pkey PRIMARY KEY (doc_id)
);

CREATE TABLE IF NOT EXISTS public.vehicle_history (
    hist_id serial NOT NULL,
    veh_id integer NOT NULL,
    change_date timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    descr text NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_history_pkey PRIMARY KEY (hist_id)
);

CREATE TABLE IF NOT EXISTS public.vehicle_maintenance (
    maint_id serial NOT NULL,
    veh_id integer NOT NULL,
    maintenance_date date NOT NULL,
    descr text,
    cost numeric(12,2),
    next_maintenance_date date,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_maintenance_pkey PRIMARY KEY (maint_id)
);

CREATE TABLE IF NOT EXISTS public.vehicle_registration (
    regis_id serial NOT NULL,
    veh_id integer NOT NULL,
    regis_no character varying(50) NOT NULL,
    regis_date date NOT NULL,
    exp_date date,
    issued_by character varying(100),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_registration_pkey PRIMARY KEY (regis_id)
);

CREATE TABLE IF NOT EXISTS public.purchase_orders (
    po_id serial NOT NULL,
    supp_id integer NOT NULL,
    po_number character varying(50) NOT NULL,
    po_date timestamp without time zone DEFAULT now(),
    order_status_id integer NOT NULL,
    total_amount numeric(10,2) NOT NULL DEFAULT 0.00,
    notes text,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT purchase_orders_pkey PRIMARY KEY (po_id),
    CONSTRAINT purchase_orders_po_number_key UNIQUE (po_number)
);

CREATE TABLE IF NOT EXISTS public.purchase_order_items (
    po_item_id serial NOT NULL,
    po_id integer NOT NULL,
    prod_id integer NOT NULL,
    quantity integer NOT NULL,
    unit_price numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT purchase_order_items_pkey PRIMARY KEY (po_item_id)
);
CREATE TABLE IF NOT EXISTS public.invoices (
    invoice_id serial NOT NULL,
    order_id integer NOT NULL,
    invoice_number character varying(50) NOT NULL,
    invoice_date timestamp without time zone DEFAULT now(),
    payment_status_id integer NOT NULL,
    payment_method_id integer,
    payment_date timestamp without time zone,
    total_amount numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT invoices_pkey PRIMARY KEY (invoice_id),
    CONSTRAINT invoices_invoice_number_key UNIQUE (invoice_number)
);

CREATE TABLE IF NOT EXISTS public.invoice_items (
    invoice_item_id serial NOT NULL,
    invoice_id integer NOT NULL,
    prod_id integer NOT NULL,
    quantity integer NOT NULL,
    unit_price numeric(10,2) NOT NULL,
    subtotal numeric(10,2) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT invoice_items_pkey PRIMARY KEY (invoice_item_id)
);

-- Invoice Foreign Keys
ALTER TABLE IF EXISTS public.invoices
    ADD CONSTRAINT fk_invoice_order FOREIGN KEY (order_id)
    REFERENCES public.customer_orders (order_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.invoices
    ADD CONSTRAINT fk_invoice_payment_status FOREIGN KEY (payment_status_id)
    REFERENCES public.payment_status (payment_status_id) ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.invoices
    ADD CONSTRAINT fk_invoice_payment_method FOREIGN KEY (payment_method_id)
    REFERENCES public.payment_method (payment_method_id) ON DELETE SET NULL;

ALTER TABLE IF EXISTS public.invoice_items
    ADD CONSTRAINT fk_invoice_item_invoice FOREIGN KEY (invoice_id)
    REFERENCES public.invoices (invoice_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.invoice_items
    ADD CONSTRAINT fk_invoice_item_product FOREIGN KEY (prod_id)
    REFERENCES public.products (prod_id) ON DELETE RESTRICT;

-- Invoice Indexes
CREATE INDEX IF NOT EXISTS idx_invoices_order ON public.invoices(order_id);
CREATE INDEX IF NOT EXISTS idx_invoices_payment_status ON public.invoices(payment_status_id);
CREATE INDEX IF NOT EXISTS idx_invoices_payment_method ON public.invoices(payment_method_id);
CREATE INDEX IF NOT EXISTS idx_invoice_items_invoice ON public.invoice_items(invoice_id);
CREATE INDEX IF NOT EXISTS idx_invoice_items_product ON public.invoice_items(prod_id);


-- Core Business Foreign Keys
ALTER TABLE IF EXISTS public.company_address
    ADD CONSTRAINT fk_company_addr FOREIGN KEY (company_id)
    REFERENCES public.company (company_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.company_address
    ADD CONSTRAINT fk_company_addr_fk FOREIGN KEY (addr_id)
    REFERENCES public.address (addr_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.company_contacts
    ADD CONSTRAINT company_contacts_company_id_fkey FOREIGN KEY (company_id)
    REFERENCES public.company (company_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.customer_address
    ADD CONSTRAINT fk_cust_addr FOREIGN KEY (cust_id)
    REFERENCES public.customers (cust_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.customer_address
    ADD CONSTRAINT fk_cust_addr_fk FOREIGN KEY (addr_id)
    REFERENCES public.address (addr_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.customers
    ADD CONSTRAINT fk_cust_company FOREIGN KEY (company_id)
    REFERENCES public.company (company_id) ON DELETE SET NULL;

ALTER TABLE IF EXISTS public.customers
    ADD CONSTRAINT fk_cust_type FOREIGN KEY (cust_type_id)
    REFERENCES public.customer_type (cust_type_id) ON DELETE CASCADE;

-- Core Business Indexes
CREATE INDEX IF NOT EXISTS idx_company_address_company ON public.company_address(company_id);
CREATE INDEX IF NOT EXISTS idx_company_address_addr ON public.company_address(addr_id);
CREATE INDEX IF NOT EXISTS idx_company_contacts_company ON public.company_contacts(company_id);
CREATE INDEX IF NOT EXISTS idx_customer_address_cust ON public.customer_address(cust_id);
CREATE INDEX IF NOT EXISTS idx_customer_address_addr ON public.customer_address(addr_id);
CREATE INDEX IF NOT EXISTS idx_customers_company ON public.customers(company_id);

-- Part 2: Product and Supplier Tables

-- Product and Supplier Foreign Keys
ALTER TABLE IF EXISTS public.products
    ADD CONSTRAINT fk_prod_category FOREIGN KEY (prod_cat_id)
    REFERENCES public.product_category (prod_cat_id) ON DELETE SET NULL;

ALTER TABLE IF EXISTS public.products
    ADD CONSTRAINT fk_prod_type FOREIGN KEY (prod_type_id)
    REFERENCES public.product_type (prod_type_id) ON DELETE SET NULL;

ALTER TABLE IF EXISTS public.suppliers
    ADD CONSTRAINT fk_supp_company FOREIGN KEY (company_id)
    REFERENCES public.company (company_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.suppliers
    ADD CONSTRAINT fk_supp_type FOREIGN KEY (supp_type_id)
    REFERENCES public.supplier_type (supp_type_id) ON DELETE SET NULL;

ALTER TABLE IF EXISTS public.supplier_products
    ADD CONSTRAINT fk_supp_prod_supplier FOREIGN KEY (supp_id)
    REFERENCES public.suppliers (supp_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.supplier_products
    ADD CONSTRAINT fk_supp_prod_product FOREIGN KEY (prod_id)
    REFERENCES public.products (prod_id) ON DELETE CASCADE;

-- Product and Supplier Indexes
CREATE INDEX IF NOT EXISTS idx_products_category ON public.products(prod_cat_id);
CREATE INDEX IF NOT EXISTS idx_products_type ON public.products(prod_type_id);
CREATE INDEX IF NOT EXISTS idx_suppliers_company ON public.suppliers(company_id);
CREATE INDEX IF NOT EXISTS idx_suppliers_type ON public.suppliers(supp_type_id);
CREATE INDEX IF NOT EXISTS idx_supplier_products_supplier ON public.supplier_products(supp_id);
CREATE INDEX IF NOT EXISTS idx_supplier_products_product ON public.supplier_products(prod_id);


-- Sales and Order Foreign Keys
ALTER TABLE IF EXISTS public.customer_orders
    ADD CONSTRAINT fk_order_customer FOREIGN KEY (cust_id)
    REFERENCES public.customers (cust_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.customer_orders
    ADD CONSTRAINT fk_order_status FOREIGN KEY (order_status_id)
    REFERENCES public.order_status (order_status_id) ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.customer_orders
    ADD CONSTRAINT fk_payment_method FOREIGN KEY (payment_method_id)
    REFERENCES public.payment_method (payment_method_id) ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.customer_orders
    ADD CONSTRAINT fk_payment_status FOREIGN KEY (payment_status_id)
    REFERENCES public.payment_status (payment_status_id) ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.order_items
    ADD CONSTRAINT fk_order_item_order FOREIGN KEY (order_id)
    REFERENCES public.customer_orders (order_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.order_items
    ADD CONSTRAINT fk_order_item_product FOREIGN KEY (prod_id)
    REFERENCES public.products (prod_id) ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.sales_order
    ADD CONSTRAINT fk_sales_order FOREIGN KEY (order_id)
    REFERENCES public.customer_orders (order_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.sales_items
    ADD CONSTRAINT fk_sales_item_order FOREIGN KEY (sales_order_id)
    REFERENCES public.sales_order (sales_order_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.sales_items
    ADD CONSTRAINT fk_sales_item_product FOREIGN KEY (prod_id)
    REFERENCES public.products (prod_id) ON DELETE RESTRICT;

-- Sales and Order Indexes
CREATE INDEX IF NOT EXISTS idx_customer_orders_customer ON public.customer_orders(cust_id);
CREATE INDEX IF NOT EXISTS idx_customer_orders_status ON public.customer_orders(order_status_id);
CREATE INDEX IF NOT EXISTS idx_customer_orders_payment_method ON public.customer_orders(payment_method_id);
CREATE INDEX IF NOT EXISTS idx_customer_orders_payment_status ON public.customer_orders(payment_status_id);
CREATE INDEX IF NOT EXISTS idx_order_items_order ON public.order_items(order_id);
CREATE INDEX IF NOT EXISTS idx_order_items_product ON public.order_items(prod_id);
CREATE INDEX IF NOT EXISTS idx_sales_order_order ON public.sales_order(order_id);
CREATE INDEX IF NOT EXISTS idx_sales_items_order ON public.sales_items(sales_order_id);
CREATE INDEX IF NOT EXISTS idx_sales_items_product ON public.sales_items(prod_id);


-- Time and Attendance Foreign Keys
ALTER TABLE IF EXISTS public.employees
    ADD CONSTRAINT fk_employee_address FOREIGN KEY (addr_id)
    REFERENCES public.address (addr_id) ON DELETE SET NULL;

ALTER TABLE IF EXISTS public.attendance_records
    ADD CONSTRAINT fk_attendance_employee FOREIGN KEY (employee_id)
    REFERENCES public.employees (employee_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.attendance_records
    ADD CONSTRAINT fk_attendance_shift FOREIGN KEY (shift_id)
    REFERENCES public.shifts (shift_id) ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.break_records
    ADD CONSTRAINT fk_break_attendance FOREIGN KEY (attendance_id)
    REFERENCES public.attendance_records (attendance_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.break_records
    ADD CONSTRAINT fk_break_type FOREIGN KEY (break_type_id)
    REFERENCES public.break_types (break_type_id) ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.leave_balances
    ADD CONSTRAINT fk_balance_employee FOREIGN KEY (employee_id)
    REFERENCES public.employees (employee_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.leave_balances
    ADD CONSTRAINT fk_balance_leave_type FOREIGN KEY (leave_type_id)
    REFERENCES public.leave_types (leave_type_id) ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.leave_requests
    ADD CONSTRAINT fk_request_employee FOREIGN KEY (employee_id)
    REFERENCES public.employees (employee_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.leave_requests
    ADD CONSTRAINT fk_request_leave_type FOREIGN KEY (leave_type_id)
    REFERENCES public.leave_types (leave_type_id) ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.leave_requests
    ADD CONSTRAINT fk_request_approver FOREIGN KEY (approved_by)
    REFERENCES public.employees (employee_id) ON DELETE SET NULL;

ALTER TABLE IF EXISTS public.overtime_requests
    ADD CONSTRAINT fk_overtime_employee FOREIGN KEY (employee_id)
    REFERENCES public.employees (employee_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.overtime_requests
    ADD CONSTRAINT fk_overtime_category FOREIGN KEY (category_id)
    REFERENCES public.overtime_categories (category_id) ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.overtime_requests
    ADD CONSTRAINT fk_overtime_approver FOREIGN KEY (approved_by)
    REFERENCES public.employees (employee_id) ON DELETE SET NULL;

ALTER TABLE IF EXISTS public.schedule_templates
    ADD CONSTRAINT fk_template_position FOREIGN KEY (position_id)
    REFERENCES public.positions (position_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.schedule_templates
    ADD CONSTRAINT fk_template_shift FOREIGN KEY (shift_id)
    REFERENCES public.shifts (shift_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.monthly_rosters
    ADD CONSTRAINT fk_roster_employee FOREIGN KEY (employee_id)
    REFERENCES public.employees (employee_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.monthly_rosters
    ADD CONSTRAINT fk_roster_creator FOREIGN KEY (created_by)
    REFERENCES public.employees (employee_id) ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.weekly_hours
    ADD CONSTRAINT fk_hours_employee FOREIGN KEY (employee_id)
    REFERENCES public.employees (employee_id) ON DELETE CASCADE;

-- Time and Attendance Indexes
CREATE INDEX IF NOT EXISTS idx_employees_address ON public.employees(addr_id);
CREATE INDEX IF NOT EXISTS idx_employees_division ON public.employees(division);
CREATE INDEX IF NOT EXISTS idx_employees_group ON public.employees(group_name);
CREATE INDEX IF NOT EXISTS idx_employees_department ON public.employees(department);
CREATE INDEX IF NOT EXISTS idx_employees_cost_center ON public.employees(cost_center);
CREATE INDEX IF NOT EXISTS idx_employees_status ON public.employees(status);
CREATE INDEX IF NOT EXISTS idx_attendance_employee ON public.attendance_records(employee_id);
CREATE INDEX IF NOT EXISTS idx_attendance_shift ON public.attendance_records(shift_id);
CREATE INDEX IF NOT EXISTS idx_break_attendance ON public.break_records(attendance_id);
CREATE INDEX IF NOT EXISTS idx_break_type ON public.break_records(break_type_id);
CREATE INDEX IF NOT EXISTS idx_leave_balance_employee ON public.leave_balances(employee_id);
CREATE INDEX IF NOT EXISTS idx_leave_balance_type ON public.leave_balances(leave_type_id);
CREATE INDEX IF NOT EXISTS idx_leave_request_employee ON public.leave_requests(employee_id);
CREATE INDEX IF NOT EXISTS idx_leave_request_type ON public.leave_requests(leave_type_id);
CREATE INDEX IF NOT EXISTS idx_leave_request_approver ON public.leave_requests(approved_by);
CREATE INDEX IF NOT EXISTS idx_overtime_employee ON public.overtime_requests(employee_id);
CREATE INDEX IF NOT EXISTS idx_overtime_category ON public.overtime_requests(category_id);
CREATE INDEX IF NOT EXISTS idx_overtime_approver ON public.overtime_requests(approved_by);
CREATE INDEX IF NOT EXISTS idx_template_position ON public.schedule_templates(position_id);
CREATE INDEX IF NOT EXISTS idx_template_shift ON public.schedule_templates(shift_id);
CREATE INDEX IF NOT EXISTS idx_roster_employee ON public.monthly_rosters(employee_id);
CREATE INDEX IF NOT EXISTS idx_roster_creator ON public.monthly_rosters(created_by);
CREATE INDEX IF NOT EXISTS idx_weekly_hours_employee ON public.weekly_hours(employee_id);

-- Additional Foreign Keys
ALTER TABLE IF EXISTS public.sale
    ADD CONSTRAINT fk_sale_person FOREIGN KEY (sale_pers_id)
    REFERENCES public.sale_person (sale_pers_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.quote
    ADD CONSTRAINT fk_quote_customer FOREIGN KEY (cust_id)
    REFERENCES public.customers (cust_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.quote
    ADD CONSTRAINT fk_quote_status FOREIGN KEY (quote_status_id)
    REFERENCES public.quote_status (status_id) ON DELETE SET NULL;

ALTER TABLE IF EXISTS public.quote_items
    ADD CONSTRAINT fk_quote_items_quote FOREIGN KEY (quote_id)
    REFERENCES public.quote (quote_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.quote_items
    ADD CONSTRAINT fk_quote_items_product FOREIGN KEY (prod_id)
    REFERENCES public.products (prod_id) ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.purchase_payment
    ADD CONSTRAINT fk_purchase_payment_po FOREIGN KEY (po_id)
    REFERENCES public.purchase_orders (po_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.sales_payment
    ADD CONSTRAINT fk_sales_payment_so FOREIGN KEY (sales_order_id)
    REFERENCES public.sales_order (sales_order_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.product_supplier_price_history
    ADD CONSTRAINT fk_price_history_product FOREIGN KEY (prod_id)
    REFERENCES public.products (prod_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.product_supplier_price_history
    ADD CONSTRAINT fk_price_history_supplier FOREIGN KEY (suppl_id)
    REFERENCES public.suppliers (supp_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.vehicle_documents
    ADD CONSTRAINT fk_vehicle_documents_vehicle FOREIGN KEY (veh_id)
    REFERENCES public.vehicle (veh_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.vehicle_history
    ADD CONSTRAINT fk_vehicle_history_vehicle FOREIGN KEY (veh_id)
    REFERENCES public.vehicle (veh_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.vehicle_maintenance
    ADD CONSTRAINT fk_vehicle_maintenance_vehicle FOREIGN KEY (veh_id)
    REFERENCES public.vehicle (veh_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.vehicle_registration
    ADD CONSTRAINT fk_vehicle_registration_vehicle FOREIGN KEY (veh_id)
    REFERENCES public.vehicle (veh_id) ON DELETE CASCADE;

-- Additional Indexes
CREATE INDEX IF NOT EXISTS idx_sale_person ON public.sale(sale_pers_id);
CREATE INDEX IF NOT EXISTS idx_quote_customer ON public.quote(cust_id);
CREATE INDEX IF NOT EXISTS idx_quote_status ON public.quote(quote_status_id);
CREATE INDEX IF NOT EXISTS idx_quote_items_quote ON public.quote_items(quote_id);
CREATE INDEX IF NOT EXISTS idx_quote_items_product ON public.quote_items(prod_id);
CREATE INDEX IF NOT EXISTS idx_purchase_payment_po ON public.purchase_payment(po_id);
CREATE INDEX IF NOT EXISTS idx_sales_payment_so ON public.sales_payment(sales_order_id);
CREATE INDEX IF NOT EXISTS idx_price_history_product ON public.product_supplier_price_history(prod_id);
CREATE INDEX IF NOT EXISTS idx_price_history_supplier ON public.product_supplier_price_history(suppl_id);
CREATE INDEX IF NOT EXISTS idx_vehicle_documents_vehicle ON public.vehicle_documents(veh_id);
CREATE INDEX IF NOT EXISTS idx_vehicle_history_vehicle ON public.vehicle_history(veh_id);
CREATE INDEX IF NOT EXISTS idx_vehicle_maintenance_vehicle ON public.vehicle_maintenance(veh_id);
CREATE INDEX IF NOT EXISTS idx_vehicle_registration_vehicle ON public.vehicle_registration(veh_id);

-- Purchase Order Foreign Keys
ALTER TABLE IF EXISTS public.purchase_orders
    ADD CONSTRAINT fk_po_supplier FOREIGN KEY (supp_id)
    REFERENCES public.suppliers (supp_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.purchase_orders
    ADD CONSTRAINT fk_po_status FOREIGN KEY (order_status_id)
    REFERENCES public.order_status (order_status_id) ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.purchase_order_items
    ADD CONSTRAINT fk_po_item_po FOREIGN KEY (po_id)
    REFERENCES public.purchase_orders (po_id) ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.purchase_order_items
    ADD CONSTRAINT fk_po_item_product FOREIGN KEY (prod_id)
    REFERENCES public.products (prod_id) ON DELETE RESTRICT;

-- Purchase Order Indexes
CREATE INDEX IF NOT EXISTS idx_purchase_orders_supplier ON public.purchase_orders(supp_id);
CREATE INDEX IF NOT EXISTS idx_purchase_orders_status ON public.purchase_orders(order_status_id);
CREATE INDEX IF NOT EXISTS idx_po_items_po ON public.purchase_order_items(po_id);
CREATE INDEX IF NOT EXISTS idx_po_items_product ON public.purchase_order_items(prod_id);

-- Remove the positions table and its related foreign keys
ALTER TABLE IF EXISTS public.employees DROP CONSTRAINT IF EXISTS fk_employee_position;
DROP TABLE IF EXISTS public.positions;

-- Remove positions-related indexes
DROP INDEX IF EXISTS idx_positions_company;
DROP INDEX IF EXISTS idx_employees_position;

COMMIT; 