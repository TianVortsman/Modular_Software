-- This script was generated by the ERD tool in pgAdmin 4.
-- Please log an issue at https://github.com/pgadmin-org/pgadmin4/issues/new/choose if you find any bugs, including reproduction steps.
BEGIN;


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
    customer_address_id integer NOT NULL DEFAULT nextval('customer_address_addr_id_seq'::regclass),
    cust_id integer NOT NULL,
    updated_by integer,
    addr_id integer,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT customer_address_pkey PRIMARY KEY (customer_address_id)
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

CREATE TABLE IF NOT EXISTS public.quote_status
(
    status_id serial NOT NULL,
    status_name character varying(50) COLLATE pg_catalog."default" NOT NULL,
    created_at timestamp without time zone DEFAULT now(),
    updated_at timestamp without time zone DEFAULT now(),
    deleted_at timestamp without time zone,
    CONSTRAINT quote_status_pkey PRIMARY KEY (status_id)
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

ALTER TABLE IF EXISTS public.company_address
    ADD CONSTRAINT fk_company_addr FOREIGN KEY (company_id)
    REFERENCES public.company (company_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;
CREATE INDEX IF NOT EXISTS company_address_company_id_unique
    ON public.company_address(company_id);


ALTER TABLE IF EXISTS public.company_address
    ADD CONSTRAINT fk_company_addr_fk FOREIGN KEY (addr_id)
    REFERENCES public.address (addr_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;
CREATE INDEX IF NOT EXISTS idx_company_address_addr_id
    ON public.company_address(addr_id);


ALTER TABLE IF EXISTS public.company_contacts
    ADD CONSTRAINT company_contacts_company_id_fkey FOREIGN KEY (company_id)
    REFERENCES public.company (company_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;
CREATE INDEX IF NOT EXISTS idx_company_contacts_company
    ON public.company_contacts(company_id);


ALTER TABLE IF EXISTS public.customer_address
    ADD CONSTRAINT fk_cust_addr FOREIGN KEY (cust_id)
    REFERENCES public.customers (cust_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;
CREATE INDEX IF NOT EXISTS idx_customer_address_cust_id
    ON public.customer_address(cust_id);


ALTER TABLE IF EXISTS public.customer_address
    ADD CONSTRAINT fk_cust_addr_fk FOREIGN KEY (addr_id)
    REFERENCES public.address (addr_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;
CREATE INDEX IF NOT EXISTS idx_customer_address_addr_id
    ON public.customer_address(addr_id);


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
CREATE INDEX IF NOT EXISTS idx_customer_invoice_po
    ON public.customer_invoice(po_id);


ALTER TABLE IF EXISTS public.customer_invoice
    ADD CONSTRAINT fk_cust_sale_id FOREIGN KEY (sale_id)
    REFERENCES public.sale (sale_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION
    NOT VALID;


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
CREATE INDEX IF NOT EXISTS fki_p
    ON public.customer_invoice_items(prod_id);


ALTER TABLE IF EXISTS public.customers
    ADD CONSTRAINT fk_cust_company FOREIGN KEY (company_id)
    REFERENCES public.company (company_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE SET NULL;
CREATE INDEX IF NOT EXISTS idx_company_id
    ON public.customers(company_id);


ALTER TABLE IF EXISTS public.customers
    ADD CONSTRAINT fk_cust_type FOREIGN KEY (cust_type_id)
    REFERENCES public.customer_type (cust_type_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


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


ALTER TABLE IF EXISTS public.product_supplier_price_history
    ADD CONSTRAINT fk_prod FOREIGN KEY (prod_id)
    REFERENCES public.product (prod_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.product_supplier_price_history
    ADD CONSTRAINT fk_suppl FOREIGN KEY (suppl_id)
    REFERENCES public.supplier (suppl_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.purchase_order
    ADD CONSTRAINT fk_suppl_po FOREIGN KEY (suppl_id)
    REFERENCES public.supplier (suppl_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


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


ALTER TABLE IF EXISTS public.purchase_payment
    ADD CONSTRAINT fk_po_paym FOREIGN KEY (po_id)
    REFERENCES public.purchase_order (po_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.quote
    ADD CONSTRAINT fk_cust_quote FOREIGN KEY (cust_id)
    REFERENCES public.customers (cust_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.quote
    ADD CONSTRAINT fk_quote_status FOREIGN KEY (quote_status_id)
    REFERENCES public.quote_status (status_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION
    NOT VALID;


ALTER TABLE IF EXISTS public.quote_items
    ADD CONSTRAINT fk_prod_quote FOREIGN KEY (prod_id)
    REFERENCES public.product (prod_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.quote_items
    ADD CONSTRAINT fk_quote FOREIGN KEY (quote_id)
    REFERENCES public.quote (quote_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.sale
    ADD CONSTRAINT fk_sale_pers FOREIGN KEY (sale_pers_id)
    REFERENCES public.sale_person (sale_pers_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE NO ACTION;


ALTER TABLE IF EXISTS public.sales_order
    ADD CONSTRAINT fk_customer_so FOREIGN KEY (cust_id)
    REFERENCES public.customers (cust_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.sales_order_items
    ADD CONSTRAINT fk_prod_so FOREIGN KEY (prod_id)
    REFERENCES public.product (prod_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.sales_order_items
    ADD CONSTRAINT fk_so FOREIGN KEY (so_id)
    REFERENCES public.sales_order (so_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.sales_payment
    ADD CONSTRAINT fk_so_paym FOREIGN KEY (so_id)
    REFERENCES public.sales_order (so_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.supplier_invoice
    ADD CONSTRAINT fk_suppl_inv FOREIGN KEY (suppl_id)
    REFERENCES public.supplier (suppl_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.supplier_invoice_items
    ADD CONSTRAINT fk_prod_suppl_inv FOREIGN KEY (prod_id)
    REFERENCES public.product (prod_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.supplier_invoice_items
    ADD CONSTRAINT fk_suppl_inv FOREIGN KEY (suppl_inv_id)
    REFERENCES public.supplier_invoice (suppl_inv_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


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


ALTER TABLE IF EXISTS public.vehicle_invoice
    ADD CONSTRAINT fk_veh_inv_cust FOREIGN KEY (cust_id)
    REFERENCES public.customers (cust_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.vehicle_invoice
    ADD CONSTRAINT fk_veh_inv_veh FOREIGN KEY (veh_id)
    REFERENCES public.vehicle (veh_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.vehicle_invoice_items
    ADD CONSTRAINT fk_veh_inv_item FOREIGN KEY (inv_id)
    REFERENCES public.vehicle_invoice (inv_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.vehicle_maintenance
    ADD CONSTRAINT fk_veh_maintenance FOREIGN KEY (veh_id)
    REFERENCES public.vehicle (veh_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.vehicle_registration
    ADD CONSTRAINT fk_veh_regis FOREIGN KEY (veh_id)
    REFERENCES public.vehicle (veh_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;


ALTER TABLE IF EXISTS public.vehicle_service_provider
    ADD CONSTRAINT fk_service_provider FOREIGN KEY (provider_id)
    REFERENCES public.vehicle_maintenance (maint_id) MATCH SIMPLE
    ON UPDATE NO ACTION
    ON DELETE CASCADE;
CREATE INDEX IF NOT EXISTS vehicle_service_provider_pkey
    ON public.vehicle_service_provider(provider_id);

END;