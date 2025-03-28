-- Reorganized Database Schema Script
-- This script creates tables first, then indexes, then relationships

BEGIN;

-- =============================================
-- PART 1: CREATE ALL TABLES
-- =============================================

CREATE TABLE IF NOT EXISTS public.address
(
    addr_id integer NOT NULL GENERATED ALWAYS AS IDENTITY ( INCREMENT 1 START 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1 ),
    addr_line_1 character varying(50) COLLATE pg_catalog."default" NOT NULL,
    addr_line_2 character varying(50) COLLATE pg_catalog."default",
    suburb character varying(35) COLLATE pg_catalog."default" NOT NULL,
    city character varying(35) COLLATE pg_catalog."default" NOT NULL,
    province character varying(50) COLLATE pg_catalog."default" NOT NULL,
    country character varying(50) COLLATE pg_catalog."default" NOT NULL,
    postcode character varying(10) COLLATE pg_catalog."default" NOT NULL,
    updated_by integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT address_pkey PRIMARY KEY (addr_id),
    CONSTRAINT address_addr_id_unique UNIQUE (addr_id)
);

CREATE TABLE IF NOT EXISTS public.company
(
    company_id serial NOT NULL,
    company_name character varying(255) COLLATE pg_catalog."default" NOT NULL,
    company_tax_no character varying(50) COLLATE pg_catalog."default" NOT NULL,
    company_regis_no character varying(50) COLLATE pg_catalog."default" NOT NULL,
    company_type character varying(100) COLLATE pg_catalog."default",
    industry character varying(100) COLLATE pg_catalog."default",
    website character varying(255) COLLATE pg_catalog."default",
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    company_tell character varying(20) COLLATE pg_catalog."default",
    company_email character varying(100) COLLATE pg_catalog."default",
    CONSTRAINT company_pkey PRIMARY KEY (company_id),
    CONSTRAINT company_company_regis_no_key UNIQUE (company_regis_no),
    CONSTRAINT company_company_tax_no_key UNIQUE (company_tax_no)
);

CREATE TABLE IF NOT EXISTS public.company_address
(
    company_address_id serial NOT NULL,
    company_id integer NOT NULL,
    addr_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT company_address_pkey PRIMARY KEY (company_address_id),
    CONSTRAINT company_address_company_id_unique UNIQUE (company_id)
);

CREATE TABLE IF NOT EXISTS public.company_contacts
(
    contact_id serial NOT NULL,
    company_id integer NOT NULL,
    contact_name character varying(255) COLLATE pg_catalog."default" NOT NULL,
    contact_email character varying(255) COLLATE pg_catalog."default",
    contact_phone character varying(50) COLLATE pg_catalog."default",
    "position" character varying(100) COLLATE pg_catalog."default",
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    deleted_at timestamp without time zone,
    CONSTRAINT company_contacts_pkey PRIMARY KEY (contact_id)
);

CREATE TABLE IF NOT EXISTS public.customer_address
(
    customer_address_id serial NOT NULL,
    cust_id integer NOT NULL,
    updated_by integer,
    addr_id integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT customer_address_pkey PRIMARY KEY (customer_address_id)
);

CREATE TABLE IF NOT EXISTS public.customer_type
(
    cust_type_id serial NOT NULL,
    cust_type character varying(50) COLLATE pg_catalog."default" NOT NULL DEFAULT 'Client'::character varying,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT customer_type_pkey PRIMARY KEY (cust_type_id)
);

CREATE TABLE IF NOT EXISTS public.customers
(
    cust_id serial NOT NULL,
    cust_fname character varying(30) COLLATE pg_catalog."default" NOT NULL,
    cust_lname character varying(30) COLLATE pg_catalog."default" NOT NULL,
    cust_init character varying(5) COLLATE pg_catalog."default" NOT NULL,
    cust_title character varying(15) COLLATE pg_catalog."default" NOT NULL,
    cust_type_id integer,
    cust_email character varying(255) COLLATE pg_catalog."default" NOT NULL,
    cust_tel character varying(20) COLLATE pg_catalog."default",
    cust_cell character varying(20) COLLATE pg_catalog."default",
    date_created timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_by integer,
    cust_status character varying(30) COLLATE pg_catalog."default" DEFAULT 'All Paid'::character varying,
    company_id integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT customers_pkey PRIMARY KEY (cust_id)
);

CREATE TABLE IF NOT EXISTS public.product
(
    prod_id serial NOT NULL,
    prod_name character varying(100) COLLATE pg_catalog."default" NOT NULL,
    prod_descr text COLLATE pg_catalog."default",
    prod_price numeric(12, 2) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    stock_quantity character varying(50) COLLATE pg_catalog."default",
    barcode character varying(50) COLLATE pg_catalog."default",
    product_type character varying(50) COLLATE pg_catalog."default",
    brand character varying(100) COLLATE pg_catalog."default",
    manufacturer character varying(100) COLLATE pg_catalog."default",
    weight numeric(10, 2),
    dimensions character varying(100) COLLATE pg_catalog."default",
    warranty_period character varying(50) COLLATE pg_catalog."default",
    tax_rate numeric(5, 2),
    discount numeric(10, 2),
    image_url character varying(255) COLLATE pg_catalog."default",
    status character varying(50) COLLATE pg_catalog."default" DEFAULT 'active'::character varying,
    sku character varying(50) COLLATE pg_catalog."default",
    category character varying(50) COLLATE pg_catalog."default",
    sub_category character varying(50) COLLATE pg_catalog."default",
    reorder_level integer DEFAULT 0,
    lead_time integer,
    oem_part_number character varying(100) COLLATE pg_catalog."default",
    compatible_vehicles text COLLATE pg_catalog."default",
    material character varying(100) COLLATE pg_catalog."default",
    labor_cost numeric(12, 2),
    estimated_time interval,
    service_frequency character varying(50) COLLATE pg_catalog."default",
    bundle_items text COLLATE pg_catalog."default",
    installation_required boolean DEFAULT false,
    CONSTRAINT product_pkey PRIMARY KEY (prod_id),
    CONSTRAINT product_barcode_key UNIQUE (barcode),
    CONSTRAINT product_sku_key UNIQUE (sku),
    CONSTRAINT unique_barcode UNIQUE (barcode)
);

CREATE TABLE IF NOT EXISTS public.supplier
(
    suppl_id serial NOT NULL,
    suppl_name character varying(100) COLLATE pg_catalog."default" NOT NULL,
    suppl_address text COLLATE pg_catalog."default",
    suppl_contact character varying(30) COLLATE pg_catalog."default",
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT supplier_pkey PRIMARY KEY (suppl_id)
);

CREATE TABLE IF NOT EXISTS public.product_supplier
(
    prod_id integer NOT NULL,
    suppl_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    CONSTRAINT product_supplier_pkey PRIMARY KEY (prod_id, suppl_id)
);

CREATE TABLE IF NOT EXISTS public.product_supplier_price_history
(
    id serial NOT NULL,
    prod_id integer NOT NULL,
    suppl_id integer NOT NULL,
    purch_price numeric(12, 2) NOT NULL,
    start_date timestamp without time zone DEFAULT now(),
    end_date timestamp without time zone,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    CONSTRAINT product_supplier_price_history_pkey PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS public.purchase_order
(
    po_id serial NOT NULL,
    suppl_id integer NOT NULL,
    po_date date NOT NULL,
    po_status character varying(30) COLLATE pg_catalog."default" NOT NULL,
    total_amount numeric(12, 0) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT purchase_order_pkey PRIMARY KEY (po_id)
);

CREATE TABLE IF NOT EXISTS public.purchase_order_items
(
    po_item_id serial NOT NULL,
    po_id integer NOT NULL,
    prod_id integer NOT NULL,
    qty integer NOT NULL,
    unit_price numeric(11, 0) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT purchase_order_items_pkey PRIMARY KEY (po_item_id)
);

CREATE TABLE IF NOT EXISTS public.purchase_payment
(
    purchase_paym_id serial NOT NULL,
    po_id integer NOT NULL,
    paym_date date NOT NULL,
    paym_amount numeric(12, 0) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT purchase_payment_pkey PRIMARY KEY (purchase_paym_id)
);

CREATE TABLE IF NOT EXISTS public.customer_invoice
(
    cust_inv_id serial NOT NULL,
    po_id integer NOT NULL,
    inv_no character varying(50) COLLATE pg_catalog."default" NOT NULL,
    inv_date date NOT NULL,
    total_amount numeric(12, 0) NOT NULL,
    status character varying(30) COLLATE pg_catalog."default" NOT NULL,
    sale_id integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    cust_id integer,
    company_id integer,
    CONSTRAINT customer_invoice_pkey PRIMARY KEY (cust_inv_id)
);

CREATE TABLE IF NOT EXISTS public.customer_invoice_items
(
    cust_inv_item_id serial NOT NULL,
    cust_inv_id integer NOT NULL,
    prod_id integer NOT NULL,
    line_no numeric(5, 0) NOT NULL,
    line_qty numeric(7, 0) NOT NULL,
    unit_price numeric NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT cust_inv_item_pkey PRIMARY KEY (cust_inv_item_id)
);

CREATE TABLE IF NOT EXISTS public.quote_status
(
    status_id serial NOT NULL,
    status_name character varying(50) COLLATE pg_catalog."default" NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT quote_status_pkey PRIMARY KEY (status_id)
);

CREATE TABLE IF NOT EXISTS public.quote
(
    quote_id serial NOT NULL,
    cust_id integer NOT NULL,
    quote_date date NOT NULL,
    expiration_date date NOT NULL,
    total_amount numeric(12, 0) NOT NULL,
    status character varying(30) COLLATE pg_catalog."default" NOT NULL,
    quote_status_id integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT quote_pkey PRIMARY KEY (quote_id)
);

CREATE TABLE IF NOT EXISTS public.quote_items
(
    quote_item_id serial NOT NULL,
    quote_id integer NOT NULL,
    prod_id integer NOT NULL,
    qty integer NOT NULL,
    unit_price numeric(11, 0) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT quote_items_pkey PRIMARY KEY (quote_item_id)
);

CREATE TABLE IF NOT EXISTS public.sale_person
(
    sale_pers_id serial NOT NULL,
    sale_pers_fname character varying(30) COLLATE pg_catalog."default" NOT NULL,
    sale_pers_lname character varying(30) COLLATE pg_catalog."default" NOT NULL,
    sales_pers_no text COLLATE pg_catalog."default" NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT sale_person_pkey PRIMARY KEY (sale_pers_id)
);

CREATE TABLE IF NOT EXISTS public.sale
(
    sale_id serial NOT NULL,
    sale_pers_id integer NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT sale_pkey PRIMARY KEY (sale_id)
);

CREATE TABLE IF NOT EXISTS public.sales_order
(
    so_id serial NOT NULL,
    cust_id integer NOT NULL,
    order_date date NOT NULL,
    order_status character varying(30) COLLATE pg_catalog."default" NOT NULL,
    total_amount numeric(12, 0) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT sales_order_pkey PRIMARY KEY (so_id)
);

CREATE TABLE IF NOT EXISTS public.sales_order_items
(
    so_item_id serial NOT NULL,
    so_id integer NOT NULL,
    prod_id integer NOT NULL,
    qty integer NOT NULL,
    unit_price numeric(11, 0) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT sales_order_items_pkey PRIMARY KEY (so_item_id)
);

CREATE TABLE IF NOT EXISTS public.sales_payment
(
    sales_paym_id serial NOT NULL,
    so_id integer NOT NULL,
    paym_date date NOT NULL,
    paym_amount numeric(12, 0) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT sales_payment_pkey PRIMARY KEY (sales_paym_id)
);

CREATE TABLE IF NOT EXISTS public.supplier_invoice
(
    suppl_inv_id serial NOT NULL,
    suppl_id integer NOT NULL,
    inv_number character varying(50) COLLATE pg_catalog."default" NOT NULL,
    inv_date date NOT NULL,
    due_date date NOT NULL,
    total_amount numeric(12, 0) NOT NULL,
    status character varying(30) COLLATE pg_catalog."default" NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT supplier_invoice_pkey PRIMARY KEY (suppl_inv_id)
);

CREATE TABLE IF NOT EXISTS public.supplier_invoice_items
(
    suppl_inv_item_id serial NOT NULL,
    suppl_inv_id integer NOT NULL,
    prod_id integer NOT NULL,
    qty integer NOT NULL,
    unit_price numeric(11, 0) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT supplier_invoice_items_pkey PRIMARY KEY (suppl_inv_item_id)
);

-- Vehicle tables
CREATE TABLE IF NOT EXISTS public.vehicle
(
    veh_id serial NOT NULL,
    make character varying(100) COLLATE pg_catalog."default" NOT NULL,
    model character varying(100) COLLATE pg_catalog."default" NOT NULL,
    year integer NOT NULL,
    vin character varying(50) COLLATE pg_catalog."default" NOT NULL,
    regis_number character varying(50) COLLATE pg_catalog."default",
    mileage numeric(12, 0),
    status character varying(50) COLLATE pg_catalog."default",
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_pkey PRIMARY KEY (veh_id)
);

CREATE TABLE IF NOT EXISTS public.vehicle_documents
(
    doc_id serial NOT NULL,
    veh_id integer NOT NULL,
    doc_type character varying(50) COLLATE pg_catalog."default" NOT NULL,
    doc_name character varying(100) COLLATE pg_catalog."default" NOT NULL,
    doc_url character varying(255) COLLATE pg_catalog."default",
    upload_date date NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_documents_pkey PRIMARY KEY (doc_id)
);

CREATE TABLE IF NOT EXISTS public.vehicle_history
(
    hist_id serial NOT NULL,
    veh_id integer NOT NULL,
    change_date timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    descr text COLLATE pg_catalog."default" NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_history_pkey PRIMARY KEY (hist_id)
);

CREATE TABLE IF NOT EXISTS public.vehicle_images
(
    image_id serial NOT NULL,
    veh_id integer NOT NULL,
    image_path character varying(255) COLLATE pg_catalog."default" NOT NULL,
    image_type character varying(50) COLLATE pg_catalog."default",
    upload_date timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_images_pkey PRIMARY KEY (image_id)
);

CREATE TABLE IF NOT EXISTS public.vehicle_insurance
(
    insurance_id serial NOT NULL,
    veh_id integer NOT NULL,
    insurance_provider character varying(100) COLLATE pg_catalog."default" NOT NULL,
    policy_number character varying(50) COLLATE pg_catalog."default",
    coverage_type character varying(50) COLLATE pg_catalog."default",
    start_date date,
    end_date date,
    amount numeric(12, 2),
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_insurance_pkey PRIMARY KEY (insurance_id)
);

CREATE TABLE IF NOT EXISTS public.vehicle_invoice
(
    inv_id serial NOT NULL,
    veh_id integer NOT NULL,
    cust_id integer NOT NULL,
    inv_date date NOT NULL,
    due_date date,
    total_amount numeric(12, 2) NOT NULL,
    status character varying(50) COLLATE pg_catalog."default",
    paym_method character varying(50) COLLATE pg_catalog."default",
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_invoice_pkey PRIMARY KEY (inv_id)
);

CREATE TABLE IF NOT EXISTS public.vehicle_invoice_items
(
    item_id serial NOT NULL,
    inv_id integer NOT NULL,
    descr text COLLATE pg_catalog."default" NOT NULL,
    qty integer NOT NULL,
    unit_price numeric(12, 2) NOT NULL,
    total_price numeric(12, 2) NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_invoice_items_pkey PRIMARY KEY (item_id)
);

CREATE TABLE IF NOT EXISTS public.vehicle_maintenance
(
    maint_id serial NOT NULL,
    veh_id integer NOT NULL,
    maintenance_date date NOT NULL,
    descr text COLLATE pg_catalog."default",
    cost numeric(12, 2),
    next_maintenance_date date,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_maintenance_pkey PRIMARY KEY (maint_id)
);

CREATE TABLE IF NOT EXISTS public.vehicle_registration
(
    regis_id serial NOT NULL,
    veh_id integer NOT NULL,
    regis_no character varying(50) COLLATE pg_catalog."default" NOT NULL,
    regis_date date NOT NULL,
    exp_date date,
    issued_by character varying(100) COLLATE pg_catalog."default",
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_registration_pkey PRIMARY KEY (regis_id)
);

CREATE TABLE IF NOT EXISTS public.vehicle_service_provider
(
    provider_id serial NOT NULL,
    name character varying(100) COLLATE pg_catalog."default" NOT NULL,
    contact_number character varying(50) COLLATE pg_catalog."default",
    address text COLLATE pg_catalog."default",
    service_type character varying(100) COLLATE pg_catalog."default",
    email character varying(100) COLLATE pg_catalog."default",
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT vehicle_service_provider_pkey PRIMARY KEY (provider_id)
);

-- Employee and HR tables
CREATE TABLE IF NOT EXISTS public.employees
(
    employee_id serial NOT NULL,
    first_name character varying(50) COLLATE pg_catalog."default" NOT NULL,
    last_name character varying(50) COLLATE pg_catalog."default" NOT NULL,
    email character varying(100) COLLATE pg_catalog."default",
    phone character varying(20) COLLATE pg_catalog."default",
    hire_date date,
    "position" character varying(255) COLLATE pg_catalog."default" DEFAULT 'NONE'::character varying,
    department character varying(100) COLLATE pg_catalog."default",
    manager_id integer,
    addr_id integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    phone_number character varying(20) COLLATE pg_catalog."default",
    division character varying(100) COLLATE pg_catalog."default",
    group_name character varying(100) COLLATE pg_catalog."default",
    cost_center character varying(100) COLLATE pg_catalog."default",
    employee_number character varying(50) COLLATE pg_catalog."default",
    status character varying(20) COLLATE pg_catalog."default" DEFAULT 'active'::character varying,
    employment_type character varying(20) COLLATE pg_catalog."default" DEFAULT 'Permanent'::character varying,
    work_schedule_type character varying(20) COLLATE pg_catalog."default" DEFAULT 'Open'::character varying,
    biometric_id character varying(100) COLLATE pg_catalog."default",
    emergency_contact_name character varying(100) COLLATE pg_catalog."default",
    emergency_contact_phone character varying(20) COLLATE pg_catalog."default",
    emergency_contact_relation character varying(50) COLLATE pg_catalog."default",
    emergency_contact_email character varying(100) COLLATE pg_catalog."default",
    address text COLLATE pg_catalog."default",
    clock_number character varying(50) COLLATE pg_catalog."default" NOT NULL,
    date_of_birth date,
    gender character varying(10) DEFAULT 'male',
    badge_number character varying(50),
    CONSTRAINT employees_pkey PRIMARY KEY (employee_id),
    CONSTRAINT employees_email_key UNIQUE (email)
);

CREATE TABLE IF NOT EXISTS public.attendance_records
(
    attendance_id serial NOT NULL,
    employee_id integer NOT NULL,
    date date NOT NULL,
    time_in timestamp without time zone,
    time_out timestamp without time zone,
    status character varying(20) COLLATE pg_catalog."default" NOT NULL DEFAULT 'Present'::character varying,
    notes text COLLATE pg_catalog."default",
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    clock_number character varying(50) COLLATE pg_catalog."default",
    device_id character varying(50) COLLATE pg_catalog."default",
    verify_mode character varying(50) COLLATE pg_catalog."default",
    verify_status character varying(50) COLLATE pg_catalog."default",
    major_event_type integer,
    minor_event_type integer,
    date_time timestamp without time zone,
    clock_in_time timestamp without time zone,
    clock_time timestamp without time zone,
    CONSTRAINT attendance_records_pkey PRIMARY KEY (attendance_id)
);

CREATE TABLE IF NOT EXISTS public.break_types
(
    break_type_id serial NOT NULL,
    break_name character varying(50) COLLATE pg_catalog."default" NOT NULL,
    duration_minutes integer NOT NULL,
    is_paid boolean NOT NULL DEFAULT true,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT break_types_pkey PRIMARY KEY (break_type_id)
);

CREATE TABLE IF NOT EXISTS public.break_records
(
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

CREATE TABLE IF NOT EXISTS public.holidays
(
    holiday_id serial NOT NULL,
    holiday_name character varying(100) COLLATE pg_catalog."default" NOT NULL,
    date date NOT NULL,
    description text COLLATE pg_catalog."default",
    is_paid boolean NOT NULL DEFAULT true,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT holidays_pkey PRIMARY KEY (holiday_id)
);

CREATE TABLE IF NOT EXISTS public.leave_types
(
    leave_type_id serial NOT NULL,
    leave_name character varying(50) COLLATE pg_catalog."default" NOT NULL,
    description text COLLATE pg_catalog."default",
    is_paid boolean NOT NULL DEFAULT true,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT leave_types_pkey PRIMARY KEY (leave_type_id)
);

CREATE TABLE IF NOT EXISTS public.leave_balances
(
    balance_id serial NOT NULL,
    employee_id integer NOT NULL,
    leave_type_id integer NOT NULL,
    year integer NOT NULL,
    total_days numeric(5, 2) NOT NULL,
    used_days numeric(5, 2) NOT NULL DEFAULT 0,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT leave_balances_pkey PRIMARY KEY (balance_id)
);

CREATE TABLE IF NOT EXISTS public.leave_requests
(
    request_id serial NOT NULL,
    employee_id integer NOT NULL,
    leave_type_id integer NOT NULL,
    start_date date NOT NULL,
    end_date date NOT NULL,
    total_days numeric(5, 2) NOT NULL,
    status character varying(20) COLLATE pg_catalog."default" NOT NULL DEFAULT 'Pending'::character varying,
    approved_by integer,
    notes text COLLATE pg_catalog."default",
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT leave_requests_pkey PRIMARY KEY (request_id)
);

CREATE TABLE IF NOT EXISTS public.shifts
(
    shift_id serial NOT NULL,
    shift_name character varying(50) COLLATE pg_catalog."default" NOT NULL,
    start_time time without time zone,
    end_time time without time zone,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT shifts_pkey PRIMARY KEY (shift_id)
);

CREATE TABLE IF NOT EXISTS public.schedule_templates
(
    template_id serial NOT NULL,
    template_name character varying(100) COLLATE pg_catalog."default" NOT NULL,
    position_id integer NOT NULL,
    shift_id integer NOT NULL,
    day_of_week integer NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT schedule_templates_pkey PRIMARY KEY (template_id)
);

CREATE TABLE IF NOT EXISTS public.monthly_rosters
(
    roster_id serial NOT NULL,
    employee_id integer NOT NULL,
    month integer NOT NULL,
    year integer NOT NULL,
    created_by integer NOT NULL,
    status character varying(20) COLLATE pg_catalog."default" NOT NULL DEFAULT 'Draft'::character varying,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT monthly_rosters_pkey PRIMARY KEY (roster_id)
);

CREATE TABLE IF NOT EXISTS public.overtime_categories
(
    category_id serial NOT NULL,
    category_name character varying(50) COLLATE pg_catalog."default" NOT NULL,
    rate_multiplier numeric(3, 2) NOT NULL,
    description text COLLATE pg_catalog."default",
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT overtime_categories_pkey PRIMARY KEY (category_id)
);

CREATE TABLE IF NOT EXISTS public.overtime_requests
(
    overtime_id serial NOT NULL,
    employee_id integer NOT NULL,
    category_id integer NOT NULL,
    date date NOT NULL,
    hours numeric(4, 2) NOT NULL,
    reason text COLLATE pg_catalog."default" NOT NULL,
    status character varying(20) COLLATE pg_catalog."default" NOT NULL DEFAULT 'Pending'::character varying,
    approved_by integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT overtime_requests_pkey PRIMARY KEY (overtime_id)
);

CREATE TABLE IF NOT EXISTS public.weekly_hours
(
    hours_id serial NOT NULL,
    employee_id integer NOT NULL,
    week_start_date date NOT NULL,
    regular_hours numeric(5, 2) NOT NULL DEFAULT 0,
    overtime_hours numeric(5, 2) NOT NULL DEFAULT 0,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT weekly_hours_pkey PRIMARY KEY (hours_id)
);

-- Access control tables
CREATE TABLE IF NOT EXISTS public.devices
(
    id serial NOT NULL,
    device_id character varying(255) COLLATE pg_catalog."default" NOT NULL,
    serial_number character varying(255) COLLATE pg_catalog."default" NOT NULL,
    device_name character varying(255) COLLATE pg_catalog."default" NOT NULL,
    ip_address character varying(45) COLLATE pg_catalog."default" NOT NULL,
    mac_address character varying(255) COLLATE pg_catalog."default",
    username character varying(255) COLLATE pg_catalog."default" DEFAULT 'admin'::character varying,
    password character varying(255) COLLATE pg_catalog."default" DEFAULT '12345'::character varying,
    firmware_version character varying(100) COLLATE pg_catalog."default",
    model character varying(100) COLLATE pg_catalog."default",
    status character varying(50) COLLATE pg_catalog."default" DEFAULT 'offline'::character varying,
    last_online timestamp without time zone,
    created_at timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone,
    deleted_at timestamp without time zone,
    device_type character varying(50) COLLATE pg_catalog."default" DEFAULT 'hikvision'::character varying,
    port integer DEFAULT 80,
    capabilities jsonb DEFAULT '{"camera": true, "door_control": true}'::jsonb,
    CONSTRAINT devices_pkey PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS public.door_config
(
    id serial NOT NULL,
    device_id character varying(255) COLLATE pg_catalog."default" NOT NULL,
    door_number integer DEFAULT 1,
    door_name character varying(100) COLLATE pg_catalog."default",
    unlock_duration integer DEFAULT 5,
    door_status character varying(50) COLLATE pg_catalog."default" DEFAULT 'closed'::character varying,
    last_updated timestamp without time zone,
    CONSTRAINT door_config_pkey PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS public.device_actions
(
    id serial NOT NULL,
    device_id character varying(255) COLLATE pg_catalog."default" NOT NULL,
    action_type character varying(50) COLLATE pg_catalog."default" NOT NULL,
    status character varying(50) COLLATE pg_catalog."default" NOT NULL,
    details jsonb,
    created_at timestamp without time zone NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT device_actions_pkey PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS public.access_events
(
    id serial NOT NULL,
    date_time timestamp without time zone NOT NULL,
    device_id character varying(50) COLLATE pg_catalog."default",
    major_event_type integer,
    minor_event_type integer,
    raw_data jsonb,
    CONSTRAINT access_events_pkey PRIMARY KEY (id)
);

CREATE TABLE IF NOT EXISTS public.unknown_clockings
(
    id serial NOT NULL,
    date date NOT NULL,
    date_time timestamp without time zone NOT NULL,
    clock_number character varying(50) COLLATE pg_catalog."default" NOT NULL,
    device_id character varying(50) COLLATE pg_catalog."default",
    verify_mode character varying(50) COLLATE pg_catalog."default",
    verify_status character varying(50) COLLATE pg_catalog."default",
    major_event_type integer,
    minor_event_type integer,
    raw_data text COLLATE pg_catalog."default",
    processed boolean DEFAULT false,
    notes text COLLATE pg_catalog."default",
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT unknown_clockings_pkey PRIMARY KEY (id)
);

-- Employee address table
CREATE TABLE IF NOT EXISTS public.employee_address
(
    employee_address_id serial NOT NULL,
    employee_id integer NOT NULL,
    addr_id integer NOT NULL,
    updated_by integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT employee_address_pkey PRIMARY KEY (employee_address_id),
    CONSTRAINT fk_employee_addr FOREIGN KEY (employee_id)
        REFERENCES public.employees (employee_id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE CASCADE,
    CONSTRAINT fk_employee_addr_fk FOREIGN KEY (addr_id)
        REFERENCES public.address (addr_id) MATCH SIMPLE
        ON UPDATE NO ACTION
        ON DELETE CASCADE
);

-- Employee address indexes
CREATE INDEX IF NOT EXISTS idx_employee_address_employee_id
    ON public.employee_address(employee_id);
CREATE INDEX IF NOT EXISTS idx_employee_address_addr_id
    ON public.employee_address(addr_id);

-- =============================================
-- PART 2: CREATE ALL INDEXES
-- =============================================

-- Company address indexes
CREATE INDEX IF NOT EXISTS idx_company_address_company_id
    ON public.company_address(company_id);
CREATE INDEX IF NOT EXISTS idx_company_address_addr_id
    ON public.company_address(addr_id);

-- Company contacts index
CREATE INDEX IF NOT EXISTS idx_company_contacts_company
    ON public.company_contacts(company_id);

-- Customer address indexes
CREATE INDEX IF NOT EXISTS idx_customer_address_cust_id
    ON public.customer_address(cust_id);
CREATE INDEX IF NOT EXISTS idx_customer_address_addr_id
    ON public.customer_address(addr_id);

-- Customer invoice index
CREATE INDEX IF NOT EXISTS idx_customer_invoice_po
    ON public.customer_invoice(po_id);

-- Customer invoice items index
CREATE INDEX IF NOT EXISTS fki_p
    ON public.customer_invoice_items(prod_id);

-- Customers indexes
CREATE INDEX IF NOT EXISTS idx_company_id
    ON public.customers(company_id);

-- Employees index
CREATE INDEX IF NOT EXISTS idx_employees_manager
    ON public.employees(manager_id);

-- Attendance records index
CREATE INDEX IF NOT EXISTS idx_attendance_employee
    ON public.attendance_records(employee_id);

-- Break records indexes
CREATE INDEX IF NOT EXISTS idx_break_attendance
    ON public.break_records(attendance_id);
CREATE INDEX IF NOT EXISTS idx_break_type
    ON public.break_records(break_type_id);

-- Leave balances indexes
CREATE INDEX IF NOT EXISTS idx_leave_balance_employee
    ON public.leave_balances(employee_id);
CREATE INDEX IF NOT EXISTS idx_leave_balance_type
    ON public.leave_balances(leave_type_id);

-- Leave requests indexes
CREATE INDEX IF NOT EXISTS idx_leave_request_employee
    ON public.leave_requests(employee_id);
CREATE INDEX IF NOT EXISTS idx_leave_request_type
    ON public.leave_requests(leave_type_id);
CREATE INDEX IF NOT EXISTS idx_leave_request_approver
    ON public.leave_requests(approved_by);

-- Monthly rosters indexes
CREATE INDEX IF NOT EXISTS idx_roster_employee
    ON public.monthly_rosters(employee_id);
CREATE INDEX IF NOT EXISTS idx_roster_creator
    ON public.monthly_rosters(created_by);

-- Overtime requests indexes
CREATE INDEX IF NOT EXISTS idx_overtime_employee
    ON public.overtime_requests(employee_id);
CREATE INDEX IF NOT EXISTS idx_overtime_category
    ON public.overtime_requests(category_id);
CREATE INDEX IF NOT EXISTS idx_overtime_approver
    ON public.overtime_requests(approved_by);

-- Schedule templates index
CREATE INDEX IF NOT EXISTS idx_template_shift
    ON public.schedule_templates(shift_id);

-- Weekly hours index
CREATE INDEX IF NOT EXISTS idx_weekly_hours_employee
    ON public.weekly_hours(employee_id);

-- Door config index
CREATE INDEX IF NOT EXISTS idx_door_config_id
    ON public.door_config(id);

-- Vehicle service provider index
CREATE INDEX IF NOT EXISTS idx_vehicle_service_provider_id
    ON public.vehicle_service_provider(provider_id);

-- =============================================
-- PART 3: ADD FOREIGN KEY RELATIONSHIPS
-- =============================================

-- Company address relationships
ALTER TABLE IF EXISTS public.company_address
    ADD CONSTRAINT fk_company_addr FOREIGN KEY (company_id)
    REFERENCES public.company (company_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.company_address
    ADD CONSTRAINT fk_company_addr_fk FOREIGN KEY (addr_id)
    REFERENCES public.address (addr_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Company contacts relationships
ALTER TABLE IF EXISTS public.company_contacts
    ADD CONSTRAINT company_contacts_company_id_fkey FOREIGN KEY (company_id)
    REFERENCES public.company (company_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Customer address relationships
ALTER TABLE IF EXISTS public.customer_address
    ADD CONSTRAINT fk_cust_addr FOREIGN KEY (cust_id)
    REFERENCES public.customers (cust_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.customer_address
    ADD CONSTRAINT fk_cust_addr_fk FOREIGN KEY (addr_id)
    REFERENCES public.address (addr_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Customer invoice relationships
ALTER TABLE IF EXISTS public.customer_invoice
    ADD CONSTRAINT fk_company_id FOREIGN KEY (company_id)
    REFERENCES public.company (company_id) MATCH SIMPLE
    ON UPDATE CASCADE
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.customer_invoice
    ADD CONSTRAINT fk_cust_id FOREIGN KEY (cust_id)
    REFERENCES public.customers (cust_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE IF EXISTS public.customer_invoice
    ADD CONSTRAINT fk_cust_inv FOREIGN KEY (po_id)
    REFERENCES public.purchase_order (po_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.customer_invoice
    ADD CONSTRAINT fk_cust_sale_id FOREIGN KEY (sale_id)
    REFERENCES public.sale (sale_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Customer invoice items relationships
ALTER TABLE IF EXISTS public.customer_invoice_items
    ADD CONSTRAINT fk_cust_inv_id FOREIGN KEY (cust_inv_id)
    REFERENCES public.customer_invoice (cust_inv_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

ALTER TABLE IF EXISTS public.customer_invoice_items
    ADD CONSTRAINT fk_prod_id FOREIGN KEY (prod_id)
    REFERENCES public.product (prod_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Customers relationships
ALTER TABLE IF EXISTS public.customers
    ADD CONSTRAINT fk_cust_company FOREIGN KEY (company_id)
    REFERENCES public.company (company_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE SET NULL;

ALTER TABLE IF EXISTS public.customers
    ADD CONSTRAINT fk_cust_type FOREIGN KEY (cust_type_id)
    REFERENCES public.customer_type (cust_type_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Product supplier relationships
ALTER TABLE IF EXISTS public.product_supplier
    ADD CONSTRAINT fk_prod FOREIGN KEY (prod_id)
    REFERENCES public.product (prod_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.product_supplier
    ADD CONSTRAINT fk_suppl FOREIGN KEY (suppl_id)
    REFERENCES public.supplier (suppl_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Product supplier price history relationships (CORRECTED)
ALTER TABLE IF EXISTS public.product_supplier_price_history
    ADD CONSTRAINT fk_prod_price_history FOREIGN KEY (prod_id)
    REFERENCES public.product (prod_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.product_supplier_price_history
    ADD CONSTRAINT fk_suppl_price_history FOREIGN KEY (suppl_id)
    REFERENCES public.supplier (suppl_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Purchase order relationships
ALTER TABLE IF EXISTS public.purchase_order
    ADD CONSTRAINT fk_suppl_po FOREIGN KEY (suppl_id)
    REFERENCES public.supplier (suppl_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Purchase order items relationships
ALTER TABLE IF EXISTS public.purchase_order_items
    ADD CONSTRAINT fk_po FOREIGN KEY (po_id)
    REFERENCES public.purchase_order (po_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.purchase_order_items
    ADD CONSTRAINT fk_prod_po FOREIGN KEY (prod_id)
    REFERENCES public.product (prod_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Purchase payment relationships
ALTER TABLE IF EXISTS public.purchase_payment
    ADD CONSTRAINT fk_po_paym FOREIGN KEY (po_id)
    REFERENCES public.purchase_order (po_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Quote relationships
ALTER TABLE IF EXISTS public.quote
    ADD CONSTRAINT fk_cust_quote FOREIGN KEY (cust_id)
    REFERENCES public.customers (cust_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.quote
    ADD CONSTRAINT fk_quote_status FOREIGN KEY (quote_status_id)
    REFERENCES public.quote_status (status_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Quote items relationships
ALTER TABLE IF EXISTS public.quote_items
    ADD CONSTRAINT fk_quote FOREIGN KEY (quote_id)
    REFERENCES public.quote (quote_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.quote_items
    ADD CONSTRAINT fk_prod_quote FOREIGN KEY (prod_id)
    REFERENCES public.product (prod_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Sale relationships
ALTER TABLE IF EXISTS public.sale
    ADD CONSTRAINT fk_sale_pers FOREIGN KEY (sale_pers_id)
    REFERENCES public.sale_person (sale_pers_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

-- Sales order relationships
ALTER TABLE IF EXISTS public.sales_order
    ADD CONSTRAINT fk_customer_so FOREIGN KEY (cust_id)
    REFERENCES public.customers (cust_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Sales order items relationships
ALTER TABLE IF EXISTS public.sales_order_items
    ADD CONSTRAINT fk_so FOREIGN KEY (so_id)
    REFERENCES public.sales_order (so_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.sales_order_items
    ADD CONSTRAINT fk_prod_so FOREIGN KEY (prod_id)
    REFERENCES public.product (prod_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Sales payment relationships
ALTER TABLE IF EXISTS public.sales_payment
    ADD CONSTRAINT fk_so_paym FOREIGN KEY (so_id)
    REFERENCES public.sales_order (so_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Supplier invoice relationships
ALTER TABLE IF EXISTS public.supplier_invoice
    ADD CONSTRAINT fk_suppl_inv FOREIGN KEY (suppl_id)
    REFERENCES public.supplier (suppl_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Supplier invoice items relationships
ALTER TABLE IF EXISTS public.supplier_invoice_items
    ADD CONSTRAINT fk_suppl_inv FOREIGN KEY (suppl_inv_id)
    REFERENCES public.supplier_invoice (suppl_inv_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.supplier_invoice_items
    ADD CONSTRAINT fk_prod_suppl_inv FOREIGN KEY (prod_id)
    REFERENCES public.product (prod_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Vehicle relationships
ALTER TABLE IF EXISTS public.vehicle_documents
    ADD CONSTRAINT fk_veh_docs FOREIGN KEY (veh_id)
    REFERENCES public.vehicle (veh_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.vehicle_history
    ADD CONSTRAINT fk_veh_hist FOREIGN KEY (veh_id)
    REFERENCES public.vehicle (veh_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.vehicle_images
    ADD CONSTRAINT fk_veh_image FOREIGN KEY (veh_id)
    REFERENCES public.vehicle (veh_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.vehicle_insurance
    ADD CONSTRAINT fk_veh_insurance FOREIGN KEY (veh_id)
    REFERENCES public.vehicle (veh_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Vehicle invoice relationships
ALTER TABLE IF EXISTS public.vehicle_invoice
    ADD CONSTRAINT fk_veh_inv_veh FOREIGN KEY (veh_id)
    REFERENCES public.vehicle (veh_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.vehicle_invoice
    ADD CONSTRAINT fk_veh_inv_cust FOREIGN KEY (cust_id)
    REFERENCES public.customers (cust_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Vehicle invoice items relationships
ALTER TABLE IF EXISTS public.vehicle_invoice_items
    ADD CONSTRAINT fk_veh_inv_item FOREIGN KEY (inv_id)
    REFERENCES public.vehicle_invoice (inv_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Vehicle maintenance relationship
ALTER TABLE IF EXISTS public.vehicle_maintenance
    ADD CONSTRAINT fk_veh_maintenance FOREIGN KEY (veh_id)
    REFERENCES public.vehicle (veh_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Vehicle registration relationship
ALTER TABLE IF EXISTS public.vehicle_registration
    ADD CONSTRAINT fk_veh_regis FOREIGN KEY (veh_id)
    REFERENCES public.vehicle (veh_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Vehicle service provider relationship
ALTER TABLE IF EXISTS public.vehicle_service_provider
    ADD CONSTRAINT fk_service_provider FOREIGN KEY (provider_id)
    REFERENCES public.vehicle_maintenance (maint_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Employee self-reference (manager) relationship
ALTER TABLE IF EXISTS public.employees
    ADD CONSTRAINT fk_employee_manager FOREIGN KEY (manager_id)
    REFERENCES public.employees (employee_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE SET NULL;

-- Attendance records relationship
ALTER TABLE IF EXISTS public.attendance_records
    ADD CONSTRAINT fk_attendance_employee FOREIGN KEY (employee_id)
    REFERENCES public.employees (employee_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Break records relationships
ALTER TABLE IF EXISTS public.break_records
    ADD CONSTRAINT fk_break_attendance FOREIGN KEY (attendance_id)
    REFERENCES public.attendance_records (attendance_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.break_records
    ADD CONSTRAINT fk_break_type FOREIGN KEY (break_type_id)
    REFERENCES public.break_types (break_type_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE RESTRICT;

-- Leave balance relationships
ALTER TABLE IF EXISTS public.leave_balances
    ADD CONSTRAINT fk_balance_employee FOREIGN KEY (employee_id)
    REFERENCES public.employees (employee_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.leave_balances
    ADD CONSTRAINT fk_balance_leave_type FOREIGN KEY (leave_type_id)
    REFERENCES public.leave_types (leave_type_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE RESTRICT;

-- Leave request relationships
ALTER TABLE IF EXISTS public.leave_requests
    ADD CONSTRAINT fk_request_employee FOREIGN KEY (employee_id)
    REFERENCES public.employees (employee_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.leave_requests
    ADD CONSTRAINT fk_request_leave_type FOREIGN KEY (leave_type_id)
    REFERENCES public.leave_types (leave_type_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.leave_requests
    ADD CONSTRAINT fk_request_approver FOREIGN KEY (approved_by)
    REFERENCES public.employees (employee_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE SET NULL;

-- Monthly roster relationships
ALTER TABLE IF EXISTS public.monthly_rosters
    ADD CONSTRAINT fk_roster_employee FOREIGN KEY (employee_id)
    REFERENCES public.employees (employee_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.monthly_rosters
    ADD CONSTRAINT fk_roster_creator FOREIGN KEY (created_by)
    REFERENCES public.employees (employee_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE RESTRICT;

-- Overtime request relationships
ALTER TABLE IF EXISTS public.overtime_requests
    ADD CONSTRAINT fk_overtime_employee FOREIGN KEY (employee_id)
    REFERENCES public.employees (employee_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

ALTER TABLE IF EXISTS public.overtime_requests
    ADD CONSTRAINT fk_overtime_category FOREIGN KEY (category_id)
    REFERENCES public.overtime_categories (category_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE RESTRICT;

ALTER TABLE IF EXISTS public.overtime_requests
    ADD CONSTRAINT fk_overtime_approver FOREIGN KEY (approved_by)
    REFERENCES public.employees (employee_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE SET NULL;

-- Schedule template relationship
ALTER TABLE IF EXISTS public.schedule_templates
    ADD CONSTRAINT fk_template_shift FOREIGN KEY (shift_id)
    REFERENCES public.shifts (shift_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Weekly hours relationship
ALTER TABLE IF EXISTS public.weekly_hours
    ADD CONSTRAINT fk_hours_employee FOREIGN KEY (employee_id)
    REFERENCES public.employees (employee_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;

-- Door config relationship
ALTER TABLE IF EXISTS public.door_config
    ADD CONSTRAINT door_config_id_fkey FOREIGN KEY (id)
    REFERENCES public.devices (id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;

COMMIT; 