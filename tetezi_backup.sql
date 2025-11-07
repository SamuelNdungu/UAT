--
-- PostgreSQL database dump
--

\restrict KE4mqKdaPUbtQfRAcCI4TnwiboaSsuAoR9k6riYdhbDPww0xhSGfVMDf3cvK8wO

-- Dumped from database version 16.10 (Ubuntu 16.10-0ubuntu0.24.04.1)
-- Dumped by pg_dump version 16.10 (Ubuntu 16.10-0ubuntu0.24.04.1)

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: update_updated_at_column(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.update_updated_at_column() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
BEGIN
    NEW.updated_at = NOW();
    RETURN NEW;
END;
$$;


ALTER FUNCTION public.update_updated_at_column() OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- Name: allocations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.allocations (
    id bigint NOT NULL,
    payment_id bigint NOT NULL,
    policy_id bigint NOT NULL,
    allocation_amount numeric(15,2) NOT NULL,
    allocation_date date NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.allocations OWNER TO postgres;

--
-- Name: allocations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.allocations_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.allocations_id_seq OWNER TO postgres;

--
-- Name: allocations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.allocations_id_seq OWNED BY public.allocations.id;


--
-- Name: cache; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache (
    key character varying(255) NOT NULL,
    value text NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache OWNER TO postgres;

--
-- Name: cache_locks; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.cache_locks (
    key character varying(255) NOT NULL,
    owner character varying(255) NOT NULL,
    expiration integer NOT NULL
);


ALTER TABLE public.cache_locks OWNER TO postgres;

--
-- Name: claim_events; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.claim_events (
    id bigint NOT NULL,
    claim_id bigint NOT NULL,
    event_date date NOT NULL,
    event_type character varying(255) NOT NULL,
    description text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.claim_events OWNER TO postgres;

--
-- Name: claim_events_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.claim_events_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.claim_events_id_seq OWNER TO postgres;

--
-- Name: claim_events_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.claim_events_id_seq OWNED BY public.claim_events.id;


--
-- Name: claims; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.claims (
    id bigint NOT NULL,
    claim_number character varying(255) NOT NULL,
    fileno character varying(255),
    policy_id bigint NOT NULL,
    reported_date date NOT NULL,
    type_of_loss character varying(255) NOT NULL,
    loss_details text,
    loss_date date NOT NULL,
    followup_date date,
    claimant_name character varying(255) NOT NULL,
    amount_claimed numeric(15,2) NOT NULL,
    amount_paid numeric(15,2),
    status character varying(255) DEFAULT 'Open'::character varying NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    customer_code character varying(10),
    upload_file character varying(255),
    user_id bigint,
    attachments jsonb
);


ALTER TABLE public.claims OWNER TO postgres;

--
-- Name: claims_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.claims_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.claims_id_seq OWNER TO postgres;

--
-- Name: claims_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.claims_id_seq OWNED BY public.claims.id;


--
-- Name: company_data; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.company_data (
    id bigint NOT NULL,
    company_name character varying(255) NOT NULL,
    email character varying(255),
    phone character varying(50),
    website character varying(255),
    address text,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    logo_path character varying(255)
);


ALTER TABLE public.company_data OWNER TO postgres;

--
-- Name: company_data_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.company_data_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.company_data_id_seq OWNER TO postgres;

--
-- Name: company_data_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.company_data_id_seq OWNED BY public.company_data.id;


--
-- Name: customers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.customers (
    id bigint NOT NULL,
    customer_type character varying(255),
    title text,
    first_name character varying(255),
    last_name character varying(255),
    surname character varying(255),
    dob date,
    occupation character varying(255),
    corporate_name character varying(255),
    business_no character varying(255),
    contact_person character varying(255),
    designation character varying(255),
    industry_class character varying(255),
    industry_segment character varying(255),
    email character varying(255),
    phone character varying(255),
    address text,
    city character varying(255),
    county character varying(255),
    postal_code character varying(255),
    country character varying(255),
    id_number character varying(255),
    kra_pin character varying(255),
    documents character varying,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    customer_code character varying(10),
    user_id bigint,
    status character varying(255) DEFAULT 'Active'::character varying NOT NULL
);


ALTER TABLE public.customers OWNER TO postgres;

--
-- Name: customers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.customers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.customers_id_seq OWNER TO postgres;

--
-- Name: customers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.customers_id_seq OWNED BY public.customers.id;


--
-- Name: documents; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.documents (
    id bigint NOT NULL,
    claim_id bigint NOT NULL,
    path character varying(255) NOT NULL,
    original_name character varying(255) NOT NULL,
    mime character varying(255),
    size bigint,
    uploaded_by bigint,
    tag character varying(255),
    notes text,
    created_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    updated_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.documents OWNER TO postgres;

--
-- Name: documents_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.documents_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.documents_id_seq OWNER TO postgres;

--
-- Name: documents_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.documents_id_seq OWNED BY public.documents.id;


--
-- Name: endorsements; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.endorsements (
    id bigint NOT NULL,
    policy_id bigint NOT NULL,
    endorsement_type character varying(255) NOT NULL,
    effective_date timestamp without time zone NOT NULL,
    premium_impact numeric(15,2) NOT NULL,
    description text,
    document_path character varying(255),
    created_at timestamp without time zone,
    updated_at timestamp without time zone,
    sum_insured numeric(18,2),
    rate numeric(8,2),
    premium numeric(18,2),
    c_rate numeric(8,2),
    commission numeric(18,2),
    wht numeric(18,2),
    s_duty numeric(18,2),
    t_levy numeric(18,2),
    pcf_levy numeric(18,2),
    policy_charge numeric(18,2),
    aa_charges numeric(18,2),
    other_charges numeric(18,2),
    gross_premium numeric(18,2),
    net_premium numeric(18,2),
    excess numeric(18,2),
    courtesy_car numeric(18,2),
    ppl numeric(18,2),
    road_rescue numeric(18,2),
    created_by bigint,
    user_id bigint,
    type character varying(255),
    reason text,
    delta_sum_insured numeric(10,2),
    delta_premium numeric(10,2),
    delta_commission numeric(10,2),
    delta_wht numeric(10,2),
    delta_s_duty numeric(10,2),
    delta_t_levy numeric(10,2),
    delta_pcf_levy numeric(10,2),
    delta_policy_charge numeric(10,2),
    delta_aa_charges numeric(10,2),
    delta_other_charges numeric(10,2),
    delta_gross_premium numeric(10,2),
    delta_net_premium numeric(10,2),
    delta_excess numeric(10,2),
    delta_courtesy_car numeric(10,2),
    delta_ppl numeric(10,2),
    delta_road_rescue numeric(10,2)
);


ALTER TABLE public.endorsements OWNER TO postgres;

--
-- Name: endorsements_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.endorsements_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.endorsements_id_seq OWNER TO postgres;

--
-- Name: endorsements_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.endorsements_id_seq OWNED BY public.endorsements.id;


--
-- Name: failed_jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.failed_jobs (
    id bigint NOT NULL,
    uuid character varying(255) NOT NULL,
    connection text NOT NULL,
    queue text NOT NULL,
    payload text NOT NULL,
    exception text NOT NULL,
    failed_at timestamp(0) without time zone DEFAULT CURRENT_TIMESTAMP NOT NULL
);


ALTER TABLE public.failed_jobs OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.failed_jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.failed_jobs_id_seq OWNER TO postgres;

--
-- Name: failed_jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.failed_jobs_id_seq OWNED BY public.failed_jobs.id;


--
-- Name: fees; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.fees (
    id bigint NOT NULL,
    customer_code character varying(10) NOT NULL,
    invoice_number character varying(50) NOT NULL,
    amount numeric(12,2) NOT NULL,
    description text NOT NULL,
    due_date date NOT NULL,
    status character varying(20) DEFAULT 'Pending'::character varying,
    payment_status character varying(20) DEFAULT 'Unpaid'::character varying,
    created_by bigint NOT NULL,
    updated_by bigint,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE public.fees OWNER TO postgres;

--
-- Name: fees_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fees_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fees_id_seq OWNER TO postgres;

--
-- Name: fees_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.fees_id_seq OWNED BY public.fees.id;


--
-- Name: fileno_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.fileno_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.fileno_seq OWNER TO postgres;

--
-- Name: insurers; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.insurers (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    address text,
    phone character varying(20),
    email character varying(255),
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    city character varying(255),
    country character varying(255),
    physical_address character varying(255),
    street character varying(255)
);


ALTER TABLE public.insurers OWNER TO postgres;

--
-- Name: insurers_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.insurers_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.insurers_id_seq OWNER TO postgres;

--
-- Name: insurers_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.insurers_id_seq OWNED BY public.insurers.id;


--
-- Name: job_batches; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.job_batches (
    id character varying(255) NOT NULL,
    name character varying(255) NOT NULL,
    total_jobs integer NOT NULL,
    pending_jobs integer NOT NULL,
    failed_jobs integer NOT NULL,
    failed_job_ids text NOT NULL,
    options text,
    cancelled_at integer,
    created_at integer NOT NULL,
    finished_at integer
);


ALTER TABLE public.job_batches OWNER TO postgres;

--
-- Name: jobs; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.jobs (
    id bigint NOT NULL,
    queue character varying(255) NOT NULL,
    payload text NOT NULL,
    attempts smallint NOT NULL,
    reserved_at integer,
    available_at integer NOT NULL,
    created_at integer NOT NULL
);


ALTER TABLE public.jobs OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.jobs_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.jobs_id_seq OWNER TO postgres;

--
-- Name: jobs_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.jobs_id_seq OWNED BY public.jobs.id;


--
-- Name: leads; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.leads (
    id bigint NOT NULL,
    company_name character varying(255),
    deal_size numeric(15,2),
    probability numeric(5,2),
    weighted_revenue_forecast numeric(15,2),
    deal_stage character varying(255),
    deal_status character varying(255),
    date_initiated date,
    closing_date date,
    next_action text,
    contact_name character varying(255),
    email_address character varying(255),
    phone character varying(255),
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.leads OWNER TO postgres;

--
-- Name: COLUMN leads.probability; Type: COMMENT; Schema: public; Owner: postgres
--

COMMENT ON COLUMN public.leads.probability IS 'Probability of deal in percentage';


--
-- Name: leads_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.leads_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.leads_id_seq OWNER TO postgres;

--
-- Name: leads_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.leads_id_seq OWNED BY public.leads.id;


--
-- Name: migrations; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.migrations (
    id integer NOT NULL,
    migration character varying(255) NOT NULL,
    batch integer NOT NULL
);


ALTER TABLE public.migrations OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.migrations_id_seq
    AS integer
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.migrations_id_seq OWNER TO postgres;

--
-- Name: migrations_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.migrations_id_seq OWNED BY public.migrations.id;


--
-- Name: password_reset_tokens; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.password_reset_tokens (
    email character varying(255) NOT NULL,
    token character varying(255) NOT NULL,
    created_at timestamp(0) without time zone
);


ALTER TABLE public.password_reset_tokens OWNER TO postgres;

--
-- Name: payments; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.payments (
    id bigint NOT NULL,
    payment_date date NOT NULL,
    payment_amount numeric(15,2) NOT NULL,
    payment_method character varying(255),
    payment_reference character varying(255),
    payment_status character varying(255) DEFAULT 'pending'::character varying NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    customer_code character varying(255),
    user_id bigint,
    phone_number character varying(255),
    merchant_request_id character varying(255),
    checkout_request_id character varying(255),
    mpesa_receipt_number character varying(255),
    failure_reason character varying(255),
    transaction_date timestamp(0) without time zone
);


ALTER TABLE public.payments OWNER TO postgres;

--
-- Name: payments_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.payments_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.payments_id_seq OWNER TO postgres;

--
-- Name: payments_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.payments_id_seq OWNED BY public.payments.id;


--
-- Name: policies; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.policies (
    id bigint NOT NULL,
    lead_id bigint,
    customer_name character varying(255),
    kra_pin character varying(255),
    phone character varying(255),
    email character varying(255),
    policy_type_id bigint NOT NULL,
    coverage character varying(255),
    start_date date NOT NULL,
    days integer,
    end_date date,
    insurer_id bigint NOT NULL,
    policy_no character varying(255),
    insured character varying(255),
    risk_details json,
    sum_insured numeric(15,2),
    rate numeric(8,2),
    premium numeric(15,2),
    commission_rate numeric(8,2),
    commission numeric(15,2),
    s_duty numeric(15,2),
    wht numeric(15,2),
    t_levy numeric(15,2),
    pcf_levy numeric(15,2),
    policy_charge numeric(15,2),
    card_charges numeric(15,2),
    aa_charges numeric(15,2),
    other_charges numeric(15,2),
    cover_details text,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    vehicle_type_id bigint,
    reg_no character varying(50),
    make character varying(50),
    model character varying(50),
    yom integer,
    cc integer,
    chassisno character varying(50),
    engine_no character varying(50),
    body_type character varying(50),
    description text,
    fileno character varying(15),
    gross_premium numeric(10,2) DEFAULT 0,
    net_premium numeric(10,2) DEFAULT 0,
    buss_date date,
    c_rate numeric(8,2),
    customer_code character varying,
    documents jsonb,
    document_description character varying(255),
    bus_type character varying(255),
    status character varying(255),
    notifications character varying(255),
    paid_amount numeric(15,2) DEFAULT 0,
    outstanding_amount numeric(15,2) DEFAULT 0,
    balance numeric(15,2),
    user_id bigint,
    pvt numeric(15,2),
    excess numeric(15,2),
    courtesy_car numeric(15,2),
    ppl numeric(15,2),
    road_rescue numeric(15,2),
    cancellation_reason character varying(255),
    cancellation_date date,
    is_canceled boolean DEFAULT false NOT NULL,
    renewal_notices_sent json
);


ALTER TABLE public.policies OWNER TO postgres;

--
-- Name: policies_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.policies_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.policies_id_seq OWNER TO postgres;

--
-- Name: policies_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.policies_id_seq OWNED BY public.policies.id;


--
-- Name: policy_types; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.policy_types (
    id bigint NOT NULL,
    type_name character varying(255) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    user_id bigint
);


ALTER TABLE public.policy_types OWNER TO postgres;

--
-- Name: policy_types_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.policy_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.policy_types_id_seq OWNER TO postgres;

--
-- Name: policy_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.policy_types_id_seq OWNED BY public.policy_types.id;


--
-- Name: receipts; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.receipts (
    id bigint NOT NULL,
    payment_id bigint NOT NULL,
    receipt_date date NOT NULL,
    receipt_number character varying(255) NOT NULL,
    allocated_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    remaining_amount numeric(15,2) DEFAULT '0'::numeric NOT NULL,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    user_id bigint
);


ALTER TABLE public.receipts OWNER TO postgres;

--
-- Name: receipts_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.receipts_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.receipts_id_seq OWNER TO postgres;

--
-- Name: receipts_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.receipts_id_seq OWNED BY public.receipts.id;


--
-- Name: renewal_notices; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.renewal_notices (
    id bigint NOT NULL,
    policy_id bigint NOT NULL,
    notice_date date NOT NULL,
    due_date date NOT NULL,
    premium numeric(10,2) NOT NULL,
    status character varying(50) NOT NULL,
    sent_at timestamp without time zone,
    created_at timestamp without time zone,
    updated_at timestamp without time zone
);


ALTER TABLE public.renewal_notices OWNER TO postgres;

--
-- Name: renewal_notices_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.renewal_notices_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.renewal_notices_id_seq OWNER TO postgres;

--
-- Name: renewal_notices_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.renewal_notices_id_seq OWNED BY public.renewal_notices.id;


--
-- Name: renewals; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.renewals (
    id bigint NOT NULL,
    fileno character varying(15) NOT NULL,
    original_policy_id bigint NOT NULL,
    renewed_policy_id bigint NOT NULL,
    renewal_date timestamp(0) without time zone NOT NULL,
    renewal_sequence integer DEFAULT 1,
    renewal_type character varying(50) DEFAULT 'standard'::character varying,
    notes text,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone,
    created_by bigint
);


ALTER TABLE public.renewals OWNER TO postgres;

--
-- Name: renewals_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.renewals_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.renewals_id_seq OWNER TO postgres;

--
-- Name: renewals_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.renewals_id_seq OWNED BY public.renewals.id;


--
-- Name: reports; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.reports (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    file_path character varying(255) NOT NULL,
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.reports OWNER TO postgres;

--
-- Name: reports_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.reports_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.reports_id_seq OWNER TO postgres;

--
-- Name: reports_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.reports_id_seq OWNED BY public.reports.id;


--
-- Name: sessions; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sessions (
    id character varying(255) NOT NULL,
    user_id bigint,
    ip_address character varying(45),
    user_agent text,
    payload text NOT NULL,
    last_activity integer NOT NULL
);


ALTER TABLE public.sessions OWNER TO postgres;

--
-- Name: users; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.users (
    id bigint NOT NULL,
    name character varying(255) NOT NULL,
    email character varying(255) NOT NULL,
    email_verified_at timestamp(0) without time zone,
    password character varying(255) NOT NULL,
    remember_token character varying(100),
    created_at timestamp(0) without time zone,
    updated_at timestamp(0) without time zone
);


ALTER TABLE public.users OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.users_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.users_id_seq OWNER TO postgres;

--
-- Name: users_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.users_id_seq OWNED BY public.users.id;


--
-- Name: vehicle_types; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.vehicle_types (
    id bigint NOT NULL,
    make character varying(255) NOT NULL,
    model character varying(255) NOT NULL,
    created_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    updated_at timestamp without time zone DEFAULT CURRENT_TIMESTAMP,
    user_id bigint
);


ALTER TABLE public.vehicle_types OWNER TO postgres;

--
-- Name: vehicle_types_id_seq; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.vehicle_types_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.vehicle_types_id_seq OWNER TO postgres;

--
-- Name: vehicle_types_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: postgres
--

ALTER SEQUENCE public.vehicle_types_id_seq OWNED BY public.vehicle_types.id;


--
-- Name: allocations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.allocations ALTER COLUMN id SET DEFAULT nextval('public.allocations_id_seq'::regclass);


--
-- Name: claim_events id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.claim_events ALTER COLUMN id SET DEFAULT nextval('public.claim_events_id_seq'::regclass);


--
-- Name: claims id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.claims ALTER COLUMN id SET DEFAULT nextval('public.claims_id_seq'::regclass);


--
-- Name: company_data id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_data ALTER COLUMN id SET DEFAULT nextval('public.company_data_id_seq'::regclass);


--
-- Name: customers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers ALTER COLUMN id SET DEFAULT nextval('public.customers_id_seq'::regclass);


--
-- Name: documents id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documents ALTER COLUMN id SET DEFAULT nextval('public.documents_id_seq'::regclass);


--
-- Name: endorsements id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.endorsements ALTER COLUMN id SET DEFAULT nextval('public.endorsements_id_seq'::regclass);


--
-- Name: failed_jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs ALTER COLUMN id SET DEFAULT nextval('public.failed_jobs_id_seq'::regclass);


--
-- Name: fees id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fees ALTER COLUMN id SET DEFAULT nextval('public.fees_id_seq'::regclass);


--
-- Name: insurers id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insurers ALTER COLUMN id SET DEFAULT nextval('public.insurers_id_seq'::regclass);


--
-- Name: jobs id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs ALTER COLUMN id SET DEFAULT nextval('public.jobs_id_seq'::regclass);


--
-- Name: leads id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leads ALTER COLUMN id SET DEFAULT nextval('public.leads_id_seq'::regclass);


--
-- Name: migrations id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations ALTER COLUMN id SET DEFAULT nextval('public.migrations_id_seq'::regclass);


--
-- Name: payments id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments ALTER COLUMN id SET DEFAULT nextval('public.payments_id_seq'::regclass);


--
-- Name: policies id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.policies ALTER COLUMN id SET DEFAULT nextval('public.policies_id_seq'::regclass);


--
-- Name: policy_types id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.policy_types ALTER COLUMN id SET DEFAULT nextval('public.policy_types_id_seq'::regclass);


--
-- Name: receipts id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipts ALTER COLUMN id SET DEFAULT nextval('public.receipts_id_seq'::regclass);


--
-- Name: renewal_notices id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.renewal_notices ALTER COLUMN id SET DEFAULT nextval('public.renewal_notices_id_seq'::regclass);


--
-- Name: renewals id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.renewals ALTER COLUMN id SET DEFAULT nextval('public.renewals_id_seq'::regclass);


--
-- Name: reports id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reports ALTER COLUMN id SET DEFAULT nextval('public.reports_id_seq'::regclass);


--
-- Name: users id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users ALTER COLUMN id SET DEFAULT nextval('public.users_id_seq'::regclass);


--
-- Name: vehicle_types id; Type: DEFAULT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_types ALTER COLUMN id SET DEFAULT nextval('public.vehicle_types_id_seq'::regclass);


--
-- Data for Name: allocations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.allocations (id, payment_id, policy_id, allocation_amount, allocation_date, created_at, updated_at) FROM stdin;
5	6	37	270.00	2025-10-13	2025-10-13 23:13:34	2025-10-13 23:13:34
\.


--
-- Data for Name: cache; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache (key, value, expiration) FROM stdin;
s2ndungu@gmail.com|102.222.234.11:timer	i:1759243745;	1759243745
s2ndungu@gmail.com|102.222.234.11	i:1;	1759243745
info@tetezi.co.k|41.191.231.238:timer	i:1761565264;	1761565264
info@tetezi.co.k|41.191.231.238	i:1;	1761565264
\.


--
-- Data for Name: cache_locks; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.cache_locks (key, owner, expiration) FROM stdin;
\.


--
-- Data for Name: claim_events; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.claim_events (id, claim_id, event_date, event_type, description, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: claims; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.claims (id, claim_number, fileno, policy_id, reported_date, type_of_loss, loss_details, loss_date, followup_date, claimant_name, amount_claimed, amount_paid, status, created_at, updated_at, customer_code, upload_file, user_id, attachments) FROM stdin;
\.


--
-- Data for Name: company_data; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.company_data (id, company_name, email, phone, website, address, created_at, updated_at, logo_path) FROM stdin;
1	Tetezi Insurance Agency	info@tetezi.co.ke	0720 044957	\N	P.O BOX N/A	2025-10-23 00:27:30	2025-10-23 00:28:13	company_logos/VvuTsgbf1ewe0ZiE93cjkYmvpIS7dC8llbVz9mvz.png
\.


--
-- Data for Name: customers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.customers (id, customer_type, title, first_name, last_name, surname, dob, occupation, corporate_name, business_no, contact_person, designation, industry_class, industry_segment, email, phone, address, city, county, postal_code, country, id_number, kra_pin, documents, notes, created_at, updated_at, customer_code, user_id, status) FROM stdin;
34	Individual	\N	Sila	Kibet	\N	\N	\N	\N	\N	\N	\N	\N	\N	s2ndungu@gmail.com	0748410076	00100 Nairobi	Nairobi	Nairobi	00100	Kenya	123456	P051365947X	\N	\N	2025-04-22 14:38:44	2025-10-13 22:41:16	CUS-00100	5	Active
35	Corporate	\N	\N	\N	\N	\N	\N	Safaricom	\N	Samuel	\N	IT	\N	s2ndungu@gmail.com	0729502099	\N	Nairobi	\N	\N	Kenya	\N	P051365997X	\N	\N	2025-10-27 21:39:50	2025-10-27 21:39:50	CUS-00101	5	1
\.


--
-- Data for Name: documents; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.documents (id, claim_id, path, original_name, mime, size, uploaded_by, tag, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: endorsements; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.endorsements (id, policy_id, endorsement_type, effective_date, premium_impact, description, document_path, created_at, updated_at, sum_insured, rate, premium, c_rate, commission, wht, s_duty, t_levy, pcf_levy, policy_charge, aa_charges, other_charges, gross_premium, net_premium, excess, courtesy_car, ppl, road_rescue, created_by, user_id, type, reason, delta_sum_insured, delta_premium, delta_commission, delta_wht, delta_s_duty, delta_t_levy, delta_pcf_levy, delta_policy_charge, delta_aa_charges, delta_other_charges, delta_gross_premium, delta_net_premium, delta_excess, delta_courtesy_car, delta_ppl, delta_road_rescue) FROM stdin;
1	38	cancellation	2025-10-14 00:00:00	-8804.87	client-request	\N	2025-10-27 17:20:19	2025-10-27 17:20:19	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	5	cancellation	client-request	-876543.00	-8765.43	-876.54	-87.65	0.00	-17.53	-21.91	0.00	0.00	0.00	\N	-7928.33	0.00	0.00	0.00	0.00
3	37	deletion	2025-10-01 00:00:00	0.00	\N	\N	2025-10-29 23:08:53	2025-10-29 23:08:53	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	5	deletion	\N	\N	-6000.00	-600.00	-60.00	\N	-12.00	-15.00	\N	\N	\N	\N	-5427.00	\N	\N	\N	\N
\.


--
-- Data for Name: failed_jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.failed_jobs (id, uuid, connection, queue, payload, exception, failed_at) FROM stdin;
\.


--
-- Data for Name: fees; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.fees (id, customer_code, invoice_number, amount, description, due_date, status, payment_status, created_by, updated_by, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: insurers; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.insurers (id, name, address, phone, email, created_at, updated_at, city, country, physical_address, street) FROM stdin;
1	AAR Insurance Kenya Ltd	Box 41766	2895000	info@aar.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	George Williamson House 2nd Floor	4th Ngong Avenue
2	Africa Merchant Assurance Ltd	Box 61599-00200	2204000	info@amaco.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	4th Nextgen Mall	Mombasa Rd
3	AIG Kenya Insurance Company Ltd	Box 49460-00100	3676000	aigkenya@aig.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Eden Square Complex	Chiromo Road
4	APA Insurance Company Ltd	Box 30389-0100	2862000	info@apainsurance.org	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Apollo Centre	Ring Road
5	APA Life Assurance Ltd	Box 30065-0100	3641000	Info@apalife.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Apollo Canter	Ring Road
6	Britam General Insurance Kenya Ltd	Box 30375-00100	4904000	info@britam.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Renaissance Corporate Park	Elgon Rd
7	Britam Life Assurance Kenya Ltd	Box 30375-00100	2833000	info@britam.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Britam Centre	Mara/Ragati Road
8	Capex Life Assurance Company Ltd	Box 12043-00400	2712384/5	capex@swiNkenya.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Galana Plaza	Kilimani
9	CIC General Insurance Company Ltd	Box 59485-00200	2823000	cic@cic.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	CIC Plaza	Mara Road
10	Directline Assurance Company Ltd	Box 40863-00100	3250000	info@directline.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Hazina Towers 17th Floor	Monrovia Street
11	Fidelity Shield Insurance Company Ltd	Box 47435-00100	4225000	info@fidelityshield.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Equatorial Fidelity Centre	Waridi Lane
12	First Assurance Company Ltd	Box 30064-00100	2692250	hoinfo@firstassurance.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	First Assurance House	Gitanga Road
13	GA Insurance Company Ltd	Box 42166-00100	2711633	insure@gakeya.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Ga Insurance House	Ralph Bunche Road
14	Geminia Insurance Company Ltd	Box 61316-00200	2782000	info@geminia.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Le’mac 5th Floor	Church Road
15	Heritage Insurance (K) Company Ltd	Box 30390-00100	2783000	info@heritage.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Liberty House	Mamlaka Road
16	ICEA Lion General Insurance Co Ltd	Box 30190-00100	2750000	info@icealion.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	ICEA Lion Centre Riverside Park	Chiromo Road
17	ICEA Lion Life Assurance Co Ltd	Box 46143-00100	2750000	info@icealion.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	ICEA Lion Centre Riverside Park	Chiromo Road
18	Intra Africa Assurance Company Ltd - Hq	Box 43241-00100	2712610	intra@swiNkenya.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Williamson House	4th Ngong Avenue
19	Invesco Assurance Company Ltd	Box 52964-00200	2605220	invesco@invescoassurance.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Bishop Magua Center 3rd Floor	Ngong Road
20	Jubilee Allianz General Insurance (K) Limited	Box 30376-00100	3281000	jic@jubileekenya.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Jubilee Insurance House	Mama Ngina Street
21	Kenindia Assurance Company Ltd	Box 44372-00100	2214439	kenindia@kenindia.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Kenindia House	Loita Street
22	Kenya Orient Insurance Company Ltd	Box 34530-00100	2728603/4	info@kenyaorient.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Capitol Hill Towers	Cathedral Road
23	Kenya Orient Life Assurance Ltd	Box 34530-00100	2728603/4	info@kenyaorient.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Capitol Hill Towers	Cathedral Road
24	Kenya Reinsurance Corporation	P.O. Box 30271	240188	\N	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Kenya Re Plaza	Taifa Rd
25	Kenyan Alliance Insurance Company Ltd	Box 30170-00100	2216450	kai@kenyanalliance.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Chester House	Koinange Street
26	Madison General Insurance Company Ltd	Box 47382-00100	2864000	madison@madison.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Madison Insurance House	Upper Hill Road
27	Madison Life Assurance Company Ltd	Box 47382-00100	2864000	madison@madison.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Madison Insurance House	Upper Hill Road
28	Mayfair Insurance Company Ltd	Box 45161-00100	2999000	info@mayfair.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Mayfair Centre	Ralph Bunche Road
29	Metropolitan Cannon General Insurance Ltd	Box 30216-00100	3966000	info@cannonassurance.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Gateway Business Park	Mombasa Road
30	MUA Insurance (K) Ltd	Box 30129-00100	732178000	info@mua.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	The Mirage Tower 1-7th Floor	Chiromo Lane
31	Occidental Insurance Company Ltd	Box 39459-00623	8024149	enquiries@occidental-ins.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Crescent Business Centre 7th Floor	Parklands Road
32	PACIS Insurance Company Ltd	Box 1870-00200	4247000	info@paciskenya.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Centenary House 2nd Floor	Off Ring Road
33	Pioneer General Insurance Company Ltd	Box 20333-00200	222081	general@pioneerinsurance.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Pioneer House	Moi Avenue
34	Prudential Assurance Company Ltd	Box 25093-00100	2712591	info@prudenntiallife.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Vienna Court	Statehouse Road
35	Sanlam Life Insurance Ltd	Box 44041-00100	2781000	customerservice@pan-africa.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Sanlam Tower	Waiyak Way
36	Sanlam Insurance Company Ltd	Box 60656-00200	\N	info@sanlam.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Sanlam Tower	Waiyak Way
37	Takaful Insurance Of Africa Ltd	Box 1811-00100	2725134/5	info@takafulafrica.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	CIC Plaza	Mara Road
38	Tausi Assurance Company Ltd	Box 28889-00100	3746602	clients@tausiassurance.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Tausi Court	Tausi Road
39	The Monarch Insurance Company Ltd	Box 44003-00100	4292000	info@monarchinsurance.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Monarch House	664 Olenguruone Avenue
40	Trident Insurance Company Ltd	Box 55651-00200	2721710	info@trident.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Capitol Hill Towers	Cathedral Road
41	UAP Insurance Company Limited	Box 43013-00100	2850000	uapinsurance@uap-group.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Bishops Garden Towers	Bishops Road
42	UAP Life Assurance Company Ltd	Box 43013-00100	2850000	uapinsurance@uap-group.com	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Bishops Garden Towers	Bishops Road
43	Xplico Insurance Company Ltd	Box 38106-00623	3642000	info@explicoinsurance.co.ke	2024-08-22 14:26:32.951173	2024-08-22 14:26:32.951173	Nairobi	Kenya	Park Place 5th Floor	Limuru Road
44	Definite Assurance Company limited	NA	+25470824724	info@definiteassurance.com	2025-06-03 00:00:00	2025-06-03 00:00:00	Nairobi	Kenya	ABSA TOWERS 1st Floor	Loita Street
\.


--
-- Data for Name: job_batches; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.job_batches (id, name, total_jobs, pending_jobs, failed_jobs, failed_job_ids, options, cancelled_at, created_at, finished_at) FROM stdin;
\.


--
-- Data for Name: jobs; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.jobs (id, queue, payload, attempts, reserved_at, available_at, created_at) FROM stdin;
\.


--
-- Data for Name: leads; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.leads (id, company_name, deal_size, probability, weighted_revenue_forecast, deal_stage, deal_status, date_initiated, closing_date, next_action, contact_name, email_address, phone, notes, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: migrations; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.migrations (id, migration, batch) FROM stdin;
1	0001_01_01_000000_create_users_table	1
2	0001_01_01_000001_create_cache_table	1
3	0001_01_01_000002_create_jobs_table	1
4	2024_08_15_123751_create_customers_table	2
7	2024_08_15_124923_create_customers_table	3
8	2024_08_17_100337_create_documents_table	4
9	2024_08_17_164517_create_documents_table	5
10	2024_08_17_193424_create_leads_table	6
11	2024_08_18_174901_update_documents_column_type_in_customers_table	7
12	2024_08_19_181926_add_status_to_customers_table	8
13	2024_08_20_185759_create_policies_table	9
14	2024_08_28_215245_change_documents_column_type_in_policies_table	10
15	2024_08_30_081935_create_payments_table	10
16	2024_08_30_082119_create_receipts_table	10
17	2024_08_30_082202_create_allocations_table	10
18	2024_08_31_181024_create_claims_table	11
19	2024_08_31_181030_create_claims_table	12
20	2024_08_31_182037_create_events_table	13
21	2024_08_31_182459_create_events_table	14
22	2024_09_03_212025_create_reports_table	15
23	2024_01_11_add_mpesa_columns_to_payments	16
24	2024_02_07_create_leads_table	16
25	2024_02_24_modify_status_column_in_customers_table	16
26	2025_01_12_203007_update_documents_column_to_json_format	17
27	2025_01_12_210032_alter_policies_table_change_documents_to_json	18
28	2025_10_16_000000_create_endorsements_table	18
29	2025_10_16_120000_add_cancellation_fields_to_policies_table	18
30	2025_10_17_000000_create_company_data_table	19
31	2025_10_17_000000_create_endorsements_table	19
32	2025_10_17_000001_add_is_canceled_to_policies_table	19
33	2025_10_17_000002_create_endorsements_table	19
34	2025_10_18_000000_add_logo_to_company_data	19
35	2025_10_19_120000_create_renewal_notices_table	20
36	2025_10_21_000000_create_documents_table	21
37	2025_10_21_214325_add_attachments_to_claims_table	21
\.


--
-- Data for Name: password_reset_tokens; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.password_reset_tokens (email, token, created_at) FROM stdin;
\.


--
-- Data for Name: payments; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.payments (id, payment_date, payment_amount, payment_method, payment_reference, payment_status, notes, created_at, updated_at, customer_code, user_id, phone_number, merchant_request_id, checkout_request_id, mpesa_receipt_number, failure_reason, transaction_date) FROM stdin;
5	2025-04-22	60000.00	Cash	test	pending	test	2025-04-22 14:42:58	2025-04-22 14:42:58	CUS-00100	\N	\N	\N	\N	\N	\N	\N
6	2025-10-13	270.00	Cash	ref	pending	\N	2025-10-13 23:13:11	2025-10-13 23:13:11	CUS-00100	\N	\N	\N	\N	\N	\N	\N
7	2025-10-27	48900.00	Cash	ref	pending	\N	2025-10-27 17:27:23	2025-10-27 17:27:23	CUS-00100	\N	\N	\N	\N	\N	\N	\N
8	2025-10-29	45000.00	Cash	ref	pending	\N	2025-10-29 16:08:28	2025-10-29 16:08:28	CUS-00100	\N	\N	\N	\N	\N	\N	\N
\.


--
-- Data for Name: policies; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.policies (id, lead_id, customer_name, kra_pin, phone, email, policy_type_id, coverage, start_date, days, end_date, insurer_id, policy_no, insured, risk_details, sum_insured, rate, premium, commission_rate, commission, s_duty, wht, t_levy, pcf_levy, policy_charge, card_charges, aa_charges, other_charges, cover_details, notes, created_at, updated_at, vehicle_type_id, reg_no, make, model, yom, cc, chassisno, engine_no, body_type, description, fileno, gross_premium, net_premium, buss_date, c_rate, customer_code, documents, document_description, bus_type, status, notifications, paid_amount, outstanding_amount, balance, user_id, pvt, excess, courtesy_car, ppl, road_rescue, cancellation_reason, cancellation_date, is_canceled, renewal_notices_sent) FROM stdin;
37	\N	Sila Kibet	\N	\N	\N	35	Comprehensive	2025-04-22	365	2026-04-22	4	\N	\N	\N	0.00	0.00	60000.00	\N	6000.00	0.00	600.00	120.00	150.00	0.00	\N	\N	0.00	MOTOR VEHICLE INSURANCE – PRIVATE (COMPREHENSIVE)\r\n\r\nScope of Cover\t\r\nAny loss or damage by accidental means, theft, fire, malicious damage to the vehicle and Third Party Liabilities (death, injuries and property damage)\r\n\r\n\r\nLimits of Liability\r\n       | - Third Party Persons- Unlimited\r\n       | - Third Party Property damage Kshs 20,000,000\r\n       | - Passenger liability –   Kshs 5 million per person & Kshs 50 million per event\r\n       | - Towing Charges – Kshs 50,000\r\n       | - Authorized Repairs – Kshs 50,000\r\n       | - Windscreen cover –  Kshs 50,000\r\n       | - Radio Cassette –\tKshs 50,000\r\n       | - Medical Expenses – Kshs 50,000\r\n       | - Geographical Area – EAST AFRIC\r\n\r\nEXCESS\r\n       | - Accidental Damage – 2.5% of value Minimum Kshs 15,000 & Maximum Kshs 100,000\r\n       | - Third Party Property damage – Kshs 7,500\r\n       | - Theft (with anti-theft device) – 10% of value minimum Kshs 20,000\r\n       | - Theft (without anti-theft device) – 20% of value minimum Kshs 20,000\r\n       | - Theft (with tracking devise or fleet management system) – 2.5% of value minimum Kshs 20,000\r\n       | - Young/Inexperienced Driver – Additional Kshs 5,000 (under 21yrs/under 1yrs)\r\n\r\n\r\nVehicle Use\t\r\n       | - Vehicle must be declared for private and business use\r\n \r\nDrivers\t\r\n       | - The Insured or any person authorized by the insured and Holding a valid Driving License\r\n\r\nSPECIAL CLAUSES  \r\n       | - Legal Liability of passengers for acts of negligence\r\n       | - 30 days’ notice of cancellation\r\n       | - Motor theft cover subject to Anti-theft device\r\n       | - Including the unobtainable parts clause\r\n       | - Pre-insurance inspection reports required\r\n       | - Agreed value subject to valuation within 12 months\r\n       | - No blame no excess – subject to the police abstract blaming a named third party	\N	2025-04-22 14:41:16	2025-10-13 23:13:34	\N	test	Mazda	CX-3	\N	\N	\N	\N	Hatchback	\N	FN-00001	60270.00	54270.00	\N	10.00	CUS-00100	[{"name": "\\"[]\\"", "description": null}]	[]	New	\N	\N	270.00	-270.00	60000.00	5	0.00	0.00	0.00	0.00	0.00	\N	\N	f	\N
38	\N	Sila Kibet	\N	\N	\N	14	Comprehensive	2025-10-27	364	2026-10-26	8	\N	\N	\N	876543.00	1.00	8765.43	\N	876.54	0.00	87.65	17.53	21.91	0.00	\N	\N	0.00	DOMESTIC PACKAGE\r\n\r\nSummary of Cover\t\r\nLoss or damage to buildings and/or contents by fire lighting, explosion, earthquake, (Fire shock and volcanic eruption) bush fire, spontaneous combustion subterranean fire, all types of impact (aerial, land etc.) riot strike civil commotion, malicious damage, theft larceny, burglary, all types of water damage and special perils A to H as per policy.\r\n\r\n------------------------------------------------------------------------------\r\n| No.\t  |\tDescription\t\t\t\t   Sums Insured (Kshs.)\r\n------------------------------------------------------------------------------\r\n| 1.\t  |\tBuildings..................................Kshs. \r\n| 2.\t  |\tContents...................................Kshs. \t\r\n| 3.\t  |\tAll Risks..................................Kshs. \t\r\n| 4.\t  |\tWork Injury Benefits Act...................Kshs. \t\r\n| 5.\t  |\tLiability (Owners /Occupiers & Personal)...Kshs. \t\r\n------------------------------------------------------------------------------\t\r\n Total                                                     Kshs. \t\r\n------------------------------------------------------------------------------\r\n\r\nSection B – Contents\r\n\tThis section covers Household items of every description that are confined to the premises e.g. furniture, TV set , All household goods and personal effects of every description.\t\t\t\t\t                                                        \r\n\tNB: provide a separate declaration for items whose value is more than 5% of total sum insured under this section except furniture, household appliances, pianos and organs\r\n\r\n\r\nSection C – All Risks\t\t\r\n\tThis section covers items that are portable in nature and are naturally not confined in the premises e.g. Mobile Phones, Cameras, Laptops, Watches, Jewellery, & Personal effects etc.\r\n\tNB: Provide a schedule of items covered under this section & valuation for jewellery and related items over Kshs. 200,000    \t\r\n\r\nSection D – Work Injury Benefits Act\r\n\tThis section extends cover to domestic employees as stipulated under “WIBA”. Please state the number of servants employed and their Estimated Annual Salaries.\t\r\n\r\nSection E – Liability (Owners /Occupiers & Personal)\r\n\tThis Section covers your liabilities as a Householder and/or House owner & Personal liabilities in respect of accidental death, bodily injury/illness or loss or damage to property of third parties.  \r\n\r\nPerils Insured Under Sections A & B\t\r\n       | - Fire, lightning, thunderbolt, earthquake, volcanic eruption or subterranean fire\r\n       | - Explosion\r\n       | - Riot & Strike\r\n       | - Aircraft & other aerial devices or articles dropped therefrom\r\n       | - Bursting or overflowing of water tanks apparatus or pipes\r\n       | - Theft (as qualified)\r\n       | - Impact by road vehicles or animals\r\n       | - Storm tempest or flood \r\n       | - Malicious damage\r\n\r\nSection C – All risks of loss or damage other than by perils specifically excluded \r\n\r\nBasis of Valuation  \r\n       | - Buildings section.....Reinstatement cost or sum insured whichever is less \r\n       | - Contents Section......Replacement except clothing indemnity\r\n       | - All Risks Section.....Replacement of a similar type of item \r\n       | - Domestic Servants.....WIBA Benefits \r\n       | - Liabilities...........Indemnity\r\n\r\nSpecial Conditions/Clauses\r\n       | -85 % average clause             \r\n       | -Accidental error or omission\r\n       | -Adjoining building\r\n       | -Pairs and Sets\r\n       | -Automatic reinstatement of loss clause\r\n       | -Breach of condition clause\r\n       | -Bush fire\r\n       | -Cancellation (30 days) clause\r\n       | -Capital additional clause - 10%\r\n       | -Cross liability clause\r\n       | -Landlord's fixtures and fittings\r\n       | -Loss reduction clause\r\n       | -Malicious damage\r\n       | -Reinstatement clause / replacement\r\n       | -Riot, strike and civil commotion\r\n       | -Spontaneous combustion\r\n       | -Temporary removal clause\r\n       | -Automatic additions / deletions\r\n       | -Debris removal costs clause - Ksh.500,000/- within overall sum insured\r\n       | -Fire brigade charges - Ksh.200,000/- within overall sum insured\r\n       | -Tenants clause either with the client as a tenant or the client as the owner\r\n       | -Un-occupancy 30 days buildings and contents\r\n       | -Locked car clause when vehicle left unattended maximum limit Ksh.20,000/- otherwise locked boot\r\n       | -Total value of platinum, gold and silver articles, jewellery and furs under contents section limited to one third of the sum insured unless specially agreed on\r\n\r\nImportant Note\t\r\nCompletion of detailed questionnaire required before terms can be confirmed.	\N	2025-10-27 17:19:14	2025-10-27 17:20:19	\N	\N	\N	\N	\N	\N	\N	\N	\N	\N	FN-00002	8804.87	7928.33	\N	10.00	CUS-00100	"[]"	\N	New	canceled	\N	0.00	0.00	0.00	5	0.00	0.00	0.00	0.00	0.00	\N	\N	f	\N
39	\N	Sila Kibet	\N	\N	\N	70	Comprehensive	2025-10-28	364	2026-10-27	7	\N	\N	\N	20000.00	0.00	0.00	\N	0.00	0.00	0.00	0.00	0.00	0.00	\N	\N	0.00	Cyber Insurance Cover Summary\r\nInsured: MEZIZ BET LIMITEDBroker: Midrash Insurance Brokers Ltd.Type of Cover: Cyber Risk Insurance\r\n\r\nScope of Cover\r\nThe Cyber Insurance policy provides financial protection and technical response in the event of cyber incidents affecting you\r\nbusiness operations, systems, or data. It is designed to safeguard against:\r\na. First-Party Losses (Direct Impact):\r\n* Data breach and data restoration costs\r\n* Business interruption and loss of income due to system downtime\r\n* Cyber extortion or ransomware demands\r\n* Incident response and digital forensic investigation costs\r\n* Crisis management and public relations expenses\r\nb. Third-Party Liability (Legal & Regulatory Impact):\r\n* Legal defense costs and settlements arising from data breach claims\r\n* Liability for loss of customer or employee data\r\n* Regulatory fines and penalties (where insurable by law)\r\n* Media and privacy liability\r\n\r\n2\r\n* 24/7 incident response hotline\r\n* Access to expert forensic and legal advisors\r\n* Business continuity support during a breach\r\n* Regulatory and compliance guidance\r\n* Restores customer trust and protects brand reputation\r\n\r\nPolicy Limit & Deductible\r\n* Limit of Liability: 1,000,000 KES\r\n* Deductible/Excess: 10% LIMIT OF LIABILITY\r\n\r\n1\r\n\r\nKey Benefits\r\n\r\n3\r\n\r\nIPSOS KENYA ASSET CO	\N	2025-10-28 14:27:39	2025-10-28 14:27:39	\N	\N	\N	\N	\N	\N	\N	\N	\N	Cyber Insurance Cover Summary\r\nInsured: MEZIZ BET LIMITEDBroker: Midrash Insurance Brokers Ltd.Type of Cover: Cyber Risk Insurance\r\n\r\nScope of Cover\r\nThe Cyber Insurance policy provides financial protection and technical response in the event of cyber incidents affecting you\r\nbusiness operations, systems, or data. It is designed to safeguard against:\r\na. First-Party Losses (Direct Impact):\r\n* Data breach and data restoration costs\r\n* Business interruption and loss of income due to system downtime\r\n* Cyber extortion or ransomware demands\r\n* Incident response and digital forensic investigation costs\r\n* Crisis management and public relations expenses\r\nb. Third-Party Liability (Legal & Regulatory Impact):\r\n* Legal defense costs and settlements arising from data breach claims\r\n* Liability for loss of customer or employee data\r\n* Regulatory fines and penalties (where insurable by law)\r\n* Media and privacy liability\r\n\r\n2\r\n* 24/7 incident response hotline\r\n* Access to expert forensic and legal advisors\r\n* Business continuity support during a breach\r\n* Regulatory and compliance guidance\r\n* Restores customer trust and protects brand reputation\r\n\r\nPolicy Limit & Deductible\r\n* Limit of Liability: 1,000,000 KES\r\n* Deductible/Excess: 10% LIMIT OF LIABILITY\r\n\r\n1\r\n\r\nKey Benefits\r\n\r\n3\r\n\r\nIPSOS KENYA ASSET CO	FN-00003	0.00	0.00	\N	10.00	CUS-00100	"[]"	\N	New	\N	\N	0.00	0.00	\N	5	0.00	0.00	0.00	0.00	0.00	\N	\N	f	\N
\.


--
-- Data for Name: policy_types; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.policy_types (id, type_name, created_at, updated_at, user_id) FROM stdin;
1	Agribusiness	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
2	All Risks	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
3	Asset All Risk	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
4	Burglary	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
5	Business Combined	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
6	Carriers Legal Liability	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
7	Combined General Liability	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
8	Contaminated Products	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
9	Contractor Plant Machinery	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
10	Contractors All Risks	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
11	Contractual Liability	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
12	Custom Bond	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
13	Directors & Officers Liability	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
14	Domestic Package	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
15	Electronic Equipment	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
16	Employers Liability	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
17	Engineering	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
18	Erection All Risks	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
19	Evacuation & Repatriation	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
20	Fidelity Guarantee	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
21	Fire & Perils	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
22	Golfers	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
23	Goods In Transit	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
24	Group Life	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
26	Immigration Bond	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
27	Individual Life	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
28	Industrial All Risks	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
29	Kidnap & Ransom	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
30	Last Expense	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
31	Machinery Breakdown	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
32	Marine Cargo	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
33	Marine Hull	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
34	Medical	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
35	Motor Private	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
36	Motor Commercial	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
37	MotorCycle	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
38	Office Combined	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
39	Performance Bond	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
40	Personal Accident	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
41	Political Violence & Terrorism	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
42	Products Liability	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
43	Professional Indemnity	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
44	Public Liability	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
45	Surety Bond	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
46	Tender Bond	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
47	Term Assurance	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
48	Travel	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
49	Warehousing	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
50	Warehousing Legal Liability	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
51	WIBA	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
52	Motor PSV	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
25	Group Personal Accident & WIBA	2024-08-22 13:44:09.973064	2024-08-22 13:44:09.973064	\N
55	All Risks Insurance	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
56	Group Personal Accident (GPA)	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
57	Aviation Hull	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
58	Aviation Premises	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
59	Business Interruption Insurance	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
60	Group Personal Accident Aviation	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
61	Group Personal Accident Fixed Benefits	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
62	Machinery Breakdown Consequential Loss	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
63	Money Insurance	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
64	Motor Contingent Legal Liability	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
65	Motor Trade Road Risks	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
66	Plant All Risks	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
67	Plate Glass Insurance	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
68	Stock Floater Insurance	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
69	Trustees Liability	2025-02-07 08:44:37.817921	2025-02-07 08:44:37.817921	1
70	Cyber Liability Insurance	2025-05-13 09:38:26.870463	2025-05-13 09:38:26.870463	\N
\.


--
-- Data for Name: receipts; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.receipts (id, payment_id, receipt_date, receipt_number, allocated_amount, remaining_amount, notes, created_at, updated_at, user_id) FROM stdin;
5	5	2025-04-22	RCPT0001	0.00	60000.00	\N	2025-04-22 14:42:58	2025-04-22 14:42:58	\N
6	6	2025-10-13	RCPT0002	270.00	0.00	\N	2025-10-13 23:13:11	2025-10-13 23:13:34	\N
7	7	2025-10-27	RCPT0003	0.00	48900.00	\N	2025-10-27 17:27:23	2025-10-27 17:27:23	\N
8	8	2025-10-29	RCPT0004	0.00	45000.00	\N	2025-10-29 16:08:28	2025-10-29 16:08:28	\N
\.


--
-- Data for Name: renewal_notices; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.renewal_notices (id, policy_id, notice_date, due_date, premium, status, sent_at, created_at, updated_at) FROM stdin;
\.


--
-- Data for Name: renewals; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.renewals (id, fileno, original_policy_id, renewed_policy_id, renewal_date, renewal_sequence, renewal_type, notes, created_at, updated_at, created_by) FROM stdin;
\.


--
-- Data for Name: reports; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.reports (id, name, file_path, created_at, updated_at) FROM stdin;
1	Test Report	reports/test_report.pdf	2024-09-03 21:24:57	2024-09-03 21:24:57
\.


--
-- Data for Name: sessions; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.sessions (id, user_id, ip_address, user_agent, payload, last_activity) FROM stdin;
JNQwdIJujm7krviSrhdKpbSTxn0hthjh48ZdcVKK	\N	147.185.133.164	Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity	YTozOntzOjY6Il90b2tlbiI7czo0MDoia3NQeFBRc0p1T1VxbWVZOHR6c1V3RElEMks5Y3NKWHlJaDZxM2JkUSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjQ6Imh0dHA6Ly8xMDIuNjguODcuMjU6MzAwMyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1761665793
shXd1BcVQEVarFTZb3MYi3dW6rYIIexj0QdYF623	5	102.68.87.25	Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:144.0) Gecko/20100101 Firefox/144.0	YTo1OntzOjY6Il90b2tlbiI7czo0MDoiUFJUaVp4N3pDSTdTZmszNFhPNXhYVmpOa25QcFBxMlZEZ0o3V3N1eCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzM6Imh0dHA6Ly8xMDIuNjguODcuMjU6MzAwMy9wb2xpY2llcyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjU7czo0OiJhdXRoIjthOjE6e3M6MjE6InBhc3N3b3JkX2NvbmZpcm1lZF9hdCI7aToxNzYxNjkxMDQzO319	1761691048
TQ7OiXWGMKAWhFmCMzLeNpBGGDm0RUiZoat8B0Q0	5	102.68.87.25	Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:144.0) Gecko/20100101 Firefox/144.0	YTo2OntzOjY6Il90b2tlbiI7czo0MDoiTG5YTk1jUUxHUmxqY2VkeE1zUjJoM0NpbmFYeHdhSnB5cWQ2UXZrYiI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjMzOiJodHRwOi8vMTAyLjY4Ljg3LjI1OjMwMDMvcmVuZXdhbHMiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo1O3M6NDoiYXV0aCI7YToxOntzOjIxOiJwYXNzd29yZF9jb25maXJtZWRfYXQiO2k6MTc2MTc0MzAwMTt9fQ==	1761743894
VvnFqTNQr795LpB0kpbfj4pon0Vn6gX6UfxhVT5N	\N	54.38.193.48	Mozilla/5.0 (compatible; ModatScanner/1.1; +https://modat.io/)	YTozOntzOjY6Il90b2tlbiI7czo0MDoiWG00RXQzSUpjZDB5SUxjZTNHdmRJVzIwdHZWc0lmTHQ0Z1NnQ1BYdSI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjQ6Imh0dHA6Ly8xMDIuNjguODcuMjU6MzAwMyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1761760374
DLoWBgexRu5aeEUejrPWrK64i65wju3dyQPSnNOc	\N	62.8.70.61	Mozilla/5.0 (Macintosh; Intel Mac OS X 10_15_7) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0	YTozOntzOjY6Il90b2tlbiI7czo0MDoia1Y0ek54UnBub2hqZnNmMjFGdG5BQ3BqSENQaVI1V2JEQTJGb0NjSyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MzA6Imh0dHA6Ly8xMDIuNjguODcuMjU6MzAwMy9sb2dpbiI7fX0=	1761762188
CzlUpecob82aT6aE5sW38YLpRwXWWADj8hfu2G8L	\N	162.216.150.190	Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity	YTozOntzOjY6Il90b2tlbiI7czo0MDoiU1Izd0FMTXNtQjZ0aTduN0FVQ2tHVlpEZmpTSVlnZW5pTkNCaU5RMCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjQ6Imh0dHA6Ly8xMDIuNjguODcuMjU6MzAwMyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1761799181
zLzEF32nm8JvRfP85iEIEpODHrHypWwN2wICuZeP	\N	104.152.52.60	curl/7.61.1	YTozOntzOjY6Il90b2tlbiI7czo0MDoiU3VYWkwyUThUZjdhZ1dKbk15WWxRd3hVMHVjcVJDbzBLR0ltSFlDZCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MTk6Imh0dHA6Ly8xMDIuNjguODcuMjUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX19	1761689883
IdJLbpoTkNevz3IOXKWy9HUEdkcm65U8QZYZK8yA	\N	147.185.133.98	Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity	YTozOntzOjY6Il90b2tlbiI7czo0MDoiRkhnTkNwd0QzYkJNODhDM2NJYWxrUFBSWXkxWlJMOXVHVHREcHlWdiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjQ6Imh0dHA6Ly8xMDIuNjguODcuMjU6MzAwMyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1761716088
ap5HuFyQsPv0B40uxPqXwM4jvT1X4aBvWA7xo3OD	\N	41.191.231.238	Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/138.0.0.0 Safari/537.36 OPR/122.0.0.0	YTo0OntzOjY6Il90b2tlbiI7czo0MDoieU9FcXFnVmc2TXJLZEVIdzFvaVlCbjVYNFM4T1BpbTFYTTVtU3Z4ciI7czozOiJ1cmwiO2E6MTp7czo4OiJpbnRlbmRlZCI7czo3MToiaHR0cDovLzEwMi42OC44Ny4yNTozMDAzL2hvbWU/ZW5kX2RhdGU9MjAyNS0xMC0yOCZzdGFydF9kYXRlPTIwMjUtMDEtMDEiO31zOjk6Il9wcmV2aW91cyI7YToxOntzOjM6InVybCI7czozMDoiaHR0cDovLzEwMi42OC44Ny4yNTozMDAzL2xvZ2luIjt9czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319fQ==	1761744141
KWNL6iT9ZzyyRghxz7aSgQqeKR6qSLukXwY4u71k	\N	147.185.133.81	Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity	YTozOntzOjY6Il90b2tlbiI7czo0MDoidGpNWDlweG5DZFI4VTZJZzRMRXFRMk5LaUdabk12b2w1MUNLZU1ySyI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjQ6Imh0dHA6Ly8xMDIuNjguODcuMjU6MzAwMyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1761801247
Mm5DhiKk995GVcCbsn1fjtWcGyXYpj6PsK4crbjY	5	102.68.87.25	Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:144.0) Gecko/20100101 Firefox/144.0	YTo2OntzOjY6Il90b2tlbiI7czo0MDoiTG9XTUZnbnVCVHJOcDcxWFZFaG5IdHVQb3hQcHoySlZNSnUyRHJ3MyI7czo2OiJfZmxhc2giO2E6Mjp7czozOiJvbGQiO2E6MDp7fXM6MzoibmV3IjthOjA6e319czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjUzOiJodHRwOi8vMTAyLjY4Ljg3LjI1OjMwMDMvcG9saWNpZXMvMzkvcHJpbnQtZGViaXQtbm90ZSI7fXM6NTA6ImxvZ2luX3dlYl81OWJhMzZhZGRjMmIyZjk0MDE1ODBmMDE0YzdmNThlYTRlMzA5ODlkIjtpOjU7czo0OiJhdXRoIjthOjE6e3M6MjE6InBhc3N3b3JkX2NvbmZpcm1lZF9hdCI7aToxNzYxNjUwNjA3O319	1761650891
P6Frq4zPtnkenOXxSNz9o3ajavBj6kpIhMaibdBd	\N	162.216.150.15	Hello from Palo Alto Networks, find out more about our scans in https://docs-cortex.paloaltonetworks.com/r/1/Cortex-Xpanse/Scanning-activity	YTozOntzOjY6Il90b2tlbiI7czo0MDoieUJwbTFvQW90bTZxNHdOc3NEMEE1VnFlbWZCOXBIRllKOFhEcEgwOCI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6MjQ6Imh0dHA6Ly8xMDIuNjguODcuMjU6MzAwMyI7fXM6NjoiX2ZsYXNoIjthOjI6e3M6Mzoib2xkIjthOjA6e31zOjM6Im5ldyI7YTowOnt9fX0=	1761746939
PJb6ygy4bXy3itXa7GwqrqyDMStbzi89fYIzGlya	5	102.68.87.25	Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:144.0) Gecko/20100101 Firefox/144.0	YTo2OntzOjY6Il90b2tlbiI7czo0MDoiSnNoWVdycTl6YVhERkFsamk4VzllUmgzeDBRdUplZnZaN0VFbWRTVyI7czozOiJ1cmwiO2E6MDp7fXM6OToiX3ByZXZpb3VzIjthOjE6e3M6MzoidXJsIjtzOjM2OiJodHRwOi8vMTAyLjY4Ljg3LjI1OjMwMDMvcG9saWNpZXMvMzciO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo1O3M6NDoiYXV0aCI7YToxOntzOjIxOiJwYXNzd29yZF9jb25maXJtZWRfYXQiO2k6MTc2MTc2ODMwMzt9fQ==	1761768533
bJ6qIc4WHiWFxPzV3TubJeO92UkwVePEyEjZmttb	5	102.68.87.25	Mozilla/5.0 (X11; Ubuntu; Linux x86_64; rv:144.0) Gecko/20100101 Firefox/144.0	YTo1OntzOjY6Il90b2tlbiI7czo0MDoiRFZtcHdEbU91aDFORHdncFptQjRaQWt1dFY0cXJiOFhjczJjcnBkTiI7czo5OiJfcHJldmlvdXMiO2E6MTp7czozOiJ1cmwiO3M6NDA6Imh0dHA6Ly8xMDIuNjguODcuMjU6MzAwMy9wb2xpY2llcy9jcmVhdGUiO31zOjY6Il9mbGFzaCI7YToyOntzOjM6Im9sZCI7YTowOnt9czozOiJuZXciO2E6MDp7fX1zOjUwOiJsb2dpbl93ZWJfNTliYTM2YWRkYzJiMmY5NDAxNTgwZjAxNGM3ZjU4ZWE0ZTMwOTg5ZCI7aTo1O3M6NDoiYXV0aCI7YToxOntzOjIxOiJwYXNzd29yZF9jb25maXJtZWRfYXQiO2k6MTc2MTY1MDE4Mzt9fQ==	1761650214
\.


--
-- Data for Name: users; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.users (id, name, email, email_verified_at, password, remember_token, created_at, updated_at) FROM stdin;
6	samuel Ndungu	s2ndungu@gmail.com	\N	$2y$12$2t2CMeZmb8UXGzs6XcAAeePjUwFGeqRaClN3vq3x76MUunJ5SFtUe	\N	2025-10-25 01:14:56	2025-10-25 01:14:56
5	Dennis Makori	info@tetezi.co.ke	\N	$2y$12$Gz8FucAIediMifi6Tm0SweXX6f6kOni5HTsA4y2NkImGWHtpptG.u	CVZqftvJxAa80k0mErYrKM95v0S5RX06aiEW79GatH4MYyonyWzbg1STP0AC	2025-02-04 17:14:25	2025-02-04 17:14:25
\.


--
-- Data for Name: vehicle_types; Type: TABLE DATA; Schema: public; Owner: postgres
--

COPY public.vehicle_types (id, make, model, created_at, updated_at, user_id) FROM stdin;
22	Alfa Romeo	164	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
23	Alfa Romeo	4C	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
24	Alfa Romeo	8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
25	Alfa Romeo	Giulia	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
26	Alfa Romeo	GT	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
27	Alfa Romeo	GTV	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
28	Alfa Romeo	Milano	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
29	Alfa Romeo	Spider	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
30	Alfa Romeo	Stelvio	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
31	Alfa Romeo	Tonale	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
32	AM General	DJ	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
33	AM General	FJ8c	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
34	AM General	Post	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
35	Aston Martin	DB	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
36	Aston Martin	DB11	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
37	Aston Martin	DB12	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
38	Aston Martin	DB7	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
39	Aston Martin	DB-7	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
40	Aston Martin	DB9	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
41	Aston Martin	DBS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
42	Aston Martin	DBX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
43	Aston Martin	Lagonda	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
44	Aston Martin	Rapide	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
45	Aston Martin	Saloon/Vantage/Volante	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
46	Aston Martin	V12	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
47	Aston Martin	V8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
48	Aston Martin	Valour	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
49	Aston Martin	Vanquish	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
50	Aston Martin	Vantage	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
51	Aston Martin	Virage	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
52	Aston Martin	Virage/Volante	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
53	Audi	100	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
54	Audi	200	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
55	Audi	4000	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
56	Audi	4000s	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
57	Audi	5000	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
58	Audi	5000S	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
59	Audi	80	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
60	Audi	80/90	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
61	Audi	90	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
62	Audi	A3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
63	Audi	A4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
64	Audi	A5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
65	Audi	A6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
66	Audi	A7	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
67	Audi	A8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
68	Audi	Allroad	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
69	Audi	Cabriolet	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
70	Audi	Coupe	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
71	Audi	e-tron	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
72	Audi	Q3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
73	Audi	Q4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
74	Audi	Q5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
75	Audi	Q7	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
76	Audi	Q8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
77	Audi	Quattro	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
78	Audi	R8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
79	Audi	RS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
80	Audi	RS3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
81	Audi	RS4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
82	Audi	RS6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
83	Audi	S3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
84	Audi	S4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
85	Audi	S5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
86	Audi	S6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
87	Audi	S7	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
88	Audi	S8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
89	Audi	SQ5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
90	Audi	SQ7	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
91	Audi	SQ8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
92	Audi	TT	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
93	Audi	TTS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
94	Audi	V8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
95	Bentley	Arnage	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
96	Bentley	Azure	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
97	Bentley	Bentayga	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
98	Bentley	Brooklands	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
99	Bentley	Continental	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
100	Bentley	Flying	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
101	Bentley	Mulsanne	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
102	Bentley	Turbo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
103	Bertone	X1/9	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
104	BMW	128ci	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
105	BMW	128i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
106	BMW	135i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
107	BMW	228i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
108	BMW	230i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
109	BMW	3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
110	BMW	318i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
111	BMW	318i/318iS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
112	BMW	318is	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
113	BMW	318ti	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
114	BMW	320i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
115	BMW	323ci	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
116	BMW	323i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
117	BMW	323is	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
118	BMW	325ci	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
119	BMW	325i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
120	BMW	325i/325iS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
121	BMW	325ic	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
122	BMW	325is	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
123	BMW	325ix	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
124	BMW	325xi	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
125	BMW	328ci	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
126	BMW	328cxi	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
127	BMW	328d	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
128	BMW	328i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
129	BMW	328i/328is	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
130	BMW	328ic	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
131	BMW	328is	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
132	BMW	328xi	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
133	BMW	330ci	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
134	BMW	330e	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
135	BMW	330i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
136	BMW	330xi	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
137	BMW	335ci	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
138	BMW	335cxi	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
139	BMW	335d	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
140	BMW	335i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
141	BMW	335is	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
142	BMW	335xi	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
143	BMW	340i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
144	BMW	428i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
145	BMW	430i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
146	BMW	435i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
147	BMW	440i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
148	BMW	5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
149	BMW	525i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
150	BMW	525xi	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
151	BMW	528i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
152	BMW	528xi	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
153	BMW	530e	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
154	BMW	530i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
155	BMW	530xi	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
156	BMW	535d	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
157	BMW	535i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
158	BMW	535xi	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
159	BMW	540d	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
160	BMW	540i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
161	BMW	545i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
162	BMW	550	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
163	BMW	550i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
164	BMW	6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
165	BMW	635csi	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
166	BMW	640i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
167	BMW	645ci	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
168	BMW	650ci	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
169	BMW	650i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
170	BMW	7	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
171	BMW	735i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
172	BMW	735il	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
173	BMW	740e	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
174	BMW	740i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
175	BMW	740i/740i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
176	BMW	740il	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
177	BMW	740il/740il	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
178	BMW	740Ld	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
179	BMW	740Li	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
180	BMW	745e	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
181	BMW	745i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
182	BMW	745li	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
183	BMW	750	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
184	BMW	750i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
185	BMW	750il	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
186	BMW	750il/750il	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
187	BMW	750Li	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
188	BMW	760i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
189	BMW	760Li	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
190	BMW	840ci	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
191	BMW	840i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
192	BMW	850ci	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
193	BMW	850csi	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
194	BMW	850i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
195	BMW	Active	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
196	BMW	ActiveHybrid	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
197	BMW	Alpina	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
198	BMW	i3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
199	BMW	i3s	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
200	BMW	i4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
201	BMW	i5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
202	BMW	i7	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
203	BMW	i8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
204	BMW	iX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
205	BMW	M	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
206	BMW	M1	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
207	BMW	M2	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
208	BMW	M235i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
209	BMW	M240i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
210	BMW	M3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
211	BMW	M340i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
212	BMW	M4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
213	BMW	M440i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
214	BMW	M5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
215	BMW	M550i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
216	BMW	M6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
217	BMW	M760i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
218	BMW	M8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
219	BMW	M850i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
220	BMW	X1	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
221	BMW	X2	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
222	BMW	X3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
223	BMW	X4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
224	BMW	X5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
225	BMW	X6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
226	BMW	X7	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
227	BMW	XM	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
228	BMW	Z3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
229	BMW	Z4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
230	BMW	Z8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
231	Bugatti	Chiron	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
232	Bugatti	Divo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
233	Bugatti	Veyron	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
234	Buick	Cascada	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
235	Buick	Century	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
236	Buick	Coachbuilder	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
237	Buick	Electra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
238	Buick	Electra/Park	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
239	Buick	Enclave	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
240	Buick	Encore	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
241	Buick	Envision	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
242	Buick	Envista	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
243	Buick	Estate	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
244	Buick	Funeral	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
245	Buick	LaCrosse	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
246	Buick	Lacrosse/Allure	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
247	Buick	LeSabre	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
248	Buick	LeSabre/Electra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
249	Buick	Lucerne	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
250	Buick	Park	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
251	Buick	Rainier	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
252	Buick	Reatta	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
253	Buick	Regal	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
254	Buick	Regal/Century	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
255	Buick	Rendezvous	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
256	Buick	Riviera	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
257	Buick	Roadmaster	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
258	Buick	Skyhawk	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
259	Buick	Skylark	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
260	Buick	Somerset	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
261	Buick	Somerset/Skylark	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
262	Buick	Terraza	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
263	Buick	Verano	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
264	BYD	e6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
265	Cadillac	Allante	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
266	Cadillac	Armored	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
267	Cadillac	ATS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
268	Cadillac	ATS-V	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
269	Cadillac	Brougham	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
270	Cadillac	Brougham/DeVille	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
271	Cadillac	Catera	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
272	Cadillac	Cimarron	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
273	Cadillac	Commercial	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
274	Cadillac	CT4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
275	Cadillac	CT5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
276	Cadillac	CT6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
277	Cadillac	CTS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
278	Cadillac	CTS-V	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
279	Cadillac	DeVille	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
280	Cadillac	DeVille/60	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
281	Cadillac	DeVille/Concourse	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
282	Cadillac	DTS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
283	Cadillac	Eldorado	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
284	Cadillac	ELR	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
285	Cadillac	Escalade	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
286	Cadillac	Fleetwood	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
287	Cadillac	Fleetwood/DeVille	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
288	Cadillac	Funeral	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
289	Cadillac	Limousine	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
290	Cadillac	LYRIQ	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
291	Cadillac	Seville	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
292	Cadillac	SRX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
293	Cadillac	STS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
294	Cadillac	STS-V	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
295	Cadillac	XLR	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
296	Cadillac	XLR-V	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
297	Cadillac	XT4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
298	Cadillac	XT5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
299	Cadillac	XT6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
300	Cadillac	XTS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
301	Chevrolet	Astro	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
302	Chevrolet	Avalanche	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
303	Chevrolet	Aveo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
304	Chevrolet	Beretta	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
305	Chevrolet	Blazer	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
306	Chevrolet	Bolt	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
307	Chevrolet	C10	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
308	Chevrolet	C1500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
309	Chevrolet	C20	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
310	Chevrolet	C2500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
311	Chevrolet	Camaro	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
312	Chevrolet	Caprice	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
313	Chevrolet	Caprice/Impala	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
314	Chevrolet	Captiva	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
315	Chevrolet	Cavalier	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
316	Chevrolet	Celebrity	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
317	Chevrolet	Chevette	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
318	Chevrolet	Citation	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
319	Chevrolet	City	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
320	Chevrolet	Classic	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
321	Chevrolet	Coachbuilder	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
322	Chevrolet	Cobalt	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
323	Chevrolet	Colorado	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
324	Chevrolet	Corsica	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
325	Chevrolet	Corvette	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
326	Chevrolet	Cruze	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
327	Chevrolet	El	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
328	Chevrolet	Epica	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
329	Chevrolet	Equinox	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
330	Chevrolet	Express	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
331	Chevrolet	G10/20	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
332	Chevrolet	G30	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
333	Chevrolet	HHR	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
334	Chevrolet	Impala	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
335	Chevrolet	Impala/Caprice	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
336	Chevrolet	K10	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
337	Chevrolet	K1500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
338	Chevrolet	K20	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
339	Chevrolet	K2500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
340	Chevrolet	K5/K10	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
341	Chevrolet	Lumina	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
342	Chevrolet	Lumina/APV	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
343	Chevrolet	Lumina/Monte	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
344	Chevrolet	Malibu	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
345	Chevrolet	Metro	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
346	Chevrolet	Monte	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
347	Chevrolet	Nova	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
348	Chevrolet	Optra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
349	Chevrolet	Pickup	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
350	Chevrolet	Postal	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
351	Chevrolet	Prizm	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
352	Chevrolet	R10	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
353	Chevrolet	R1500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
354	Chevrolet	R20	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
355	Chevrolet	S10	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
356	Chevrolet	Silverado	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
357	Chevrolet	Sonic	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
358	Chevrolet	Spark	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
359	Chevrolet	Spectrum	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
360	Chevrolet	Sport	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
361	Chevrolet	Sprint	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
362	Chevrolet	SS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
363	Chevrolet	SSR	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
364	Chevrolet	Suburban	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
365	Chevrolet	T10	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
366	Chevrolet	Tahoe	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
367	Chevrolet	Tracker	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
368	Chevrolet	TrailBlazer	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
369	Chevrolet	Traverse	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
370	Chevrolet	Trax	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
371	Chevrolet	Turbo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
372	Chevrolet	Twin-Turbo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
373	Chevrolet	Uplander	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
374	Chevrolet	V10	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
375	Chevrolet	V20	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
376	Chevrolet	Van	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
377	Chevrolet	Venture	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
378	Chevrolet	Volt	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
379	Chrysler	200	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
380	Chrysler	300	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
381	Chrysler	300/SRT-8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
382	Chrysler	300C	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
383	Chrysler	300C/SRT-8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
384	Chrysler	Aspen	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
385	Chrysler	Cirrus	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
386	Chrysler	Concorde	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
387	Chrysler	Concorde/LHS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
388	Chrysler	Conquest	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
389	Chrysler	Crossfire	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
390	Chrysler	E	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
391	Chrysler	Executive	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
392	Chrysler	Fifth	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
393	Chrysler	Imperial/New	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
394	Chrysler	JX/JXI/Limited	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
395	Chrysler	Laser	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
396	Chrysler	Laser/Daytona	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
397	Chrysler	LeBaron	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
398	Chrysler	LHS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
399	Chrysler	Limousine	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
400	Chrysler	New	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
401	Chrysler	Newport/Fifth	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
402	Chrysler	Pacifica	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
403	Chrysler	Prowler	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
404	Chrysler	PT	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
405	Chrysler	QC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
406	Chrysler	Sebring	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
407	Chrysler	TC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
408	Chrysler	Town	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
409	Chrysler	Voyager	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
410	Chrysler	Voyager/Town	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
411	CODA Automotive	CODA	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
412	Consulier Industries Inc	Consulier	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
413	CX Automotive	CX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
414	CX Automotive	Cxestate	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
415	CX Automotive	XM	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
416	Dacia	Coupe	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
417	Dacia	Sedan	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
418	Dacia	Station	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
419	Daewoo	Kalos	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
420	Daewoo	Lacetti	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
421	Daewoo	Lanos	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
422	Daewoo	Leganza	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
423	Daewoo	Magnus	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
424	Daewoo	Nubira	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
425	Daihatsu	Charade	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
426	Daihatsu	Rocky	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
427	Dodge	600	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
428	Dodge	AD100/AD150	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
429	Dodge	Aries	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
430	Dodge	Avenger	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
431	Dodge	AW100/AW150	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
432	Dodge	B150/B250	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
433	Dodge	B1500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
434	Dodge	B1500/B2500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
435	Dodge	B2500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
436	Dodge	B350	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
437	Dodge	B3500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
438	Dodge	Caliber	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
439	Dodge	Caravan	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
440	Dodge	Caravan/Grand	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
441	Dodge	Caravan/Ram	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
442	Dodge	Challenger	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
443	Dodge	Charger	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
444	Dodge	Colt	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
445	Dodge	Conquest	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
446	Dodge	CSX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
447	Dodge	D100/D150	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
448	Dodge	D250	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
449	Dodge	Dakota	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
450	Dodge	Dart	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
451	Dodge	Daytona	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
452	Dodge	Diplomat	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
453	Dodge	Durango	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
454	Dodge	Dynasty	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
455	Dodge	GLH-S	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
456	Dodge	Grand	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
457	Dodge	Hornet	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
458	Dodge	Intrepid	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
459	Dodge	Journey	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
460	Dodge	Lancer	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
461	Dodge	Magnum	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
462	Dodge	Monaco	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
463	Dodge	Neon	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
464	Dodge	Neon/SRT-4/SX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
465	Dodge	Nitro	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
466	Dodge	Omni	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
467	Dodge	Power	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
468	Dodge	Raider	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
469	Dodge	Ram	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
470	Dodge	Ramcharger	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
471	Dodge	Rampage	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
472	Dodge	Shadow	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
473	Dodge	Spirit	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
474	Dodge	Stealth	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
475	Dodge	Stratus	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
476	Dodge	Viper	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
477	Dodge	W100/W150	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
478	Dodge	W250	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
479	E. P. Dutton, Inc.	Funeral	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
480	Eagle	Medallion	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
481	Eagle	Premier	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
482	Eagle	Renault	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
483	Eagle	Summit	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
484	Eagle	Talon	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
485	Eagle	Vision	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
486	Eagle	Wagon	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
487	Ferrari	296	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
488	Ferrari	3.2	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
489	Ferrari	308	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
490	Ferrari	328	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
491	Ferrari	348	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
492	Ferrari	360	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
493	Ferrari	456	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
494	Ferrari	458	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
495	Ferrari	488	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
496	Ferrari	550	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
497	Ferrari	575	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
498	Ferrari	599	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
499	Ferrari	612	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
500	Ferrari	812	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
501	Ferrari	California	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
502	Ferrari	Daytona	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
503	Ferrari	Enzo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
504	Ferrari	F	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
505	Ferrari	F12	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
506	Ferrari	F141	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
507	Ferrari	F175	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
508	Ferrari	F355/355	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
509	Ferrari	F40	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
510	Ferrari	F430	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
511	Ferrari	F60	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
512	Ferrari	F8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
513	Ferrari	Ferrari	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
514	Ferrari	FF	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
515	Ferrari	GTC4Lusso	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
516	Ferrari	LaFerrari	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
517	Ferrari	Mondial	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
518	Ferrari	Mondial/Cabriolet	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
519	Ferrari	Monza	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
520	Ferrari	Portofino	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
521	Ferrari	Purosangue	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
522	Ferrari	Roma	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
523	Ferrari	SF90	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
524	Ferrari	Testarossa	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
525	Fiat	124	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
526	Fiat	500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
527	Fiat	500e	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
528	Fiat	500X	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
529	Fisker	Karma	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
530	Fisker	Ocean	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
531	Ford	Aerostar	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
532	Ford	Aspire	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
533	Ford	Bronco	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
534	Ford	C-MAX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
535	Ford	Contour	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
536	Ford	Courier	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
537	Ford	Crown	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
538	Ford	E150	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
539	Ford	E250	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
540	Ford	E350	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
541	Ford	EcoSport	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
542	Ford	Edge	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
543	Ford	Escape	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
544	Ford	Escort	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
545	Ford	EXP	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
546	Ford	Expedition	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
547	Ford	Explorer	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
548	Ford	F150	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
549	Ford	F-150	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
550	Ford	F250	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
551	Ford	Festiva	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
552	Ford	Fiesta	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
553	Ford	Five	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
554	Ford	Flex	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
555	Ford	Focus	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
556	Ford	Freestar	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
557	Ford	Freestyle	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
558	Ford	Fusion	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
559	Ford	GT	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
560	Ford	Laser	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
561	Ford	Lightning	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
562	Ford	LTD	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
563	Ford	Maverick	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
564	Ford	Mustang	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
565	Ford	Postal	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
566	Ford	Probe	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
567	Ford	Ranger	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
568	Ford	Shelby	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
569	Ford	Taurus	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
570	Ford	Tempo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
571	Ford	Th!nk	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
572	Ford	Thunderbird	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
573	Ford	Transit	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
574	Ford	Windstar	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
575	Genesis	Electrified	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
576	Genesis	G70	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
577	Genesis	G80	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
578	Genesis	G90	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
579	Genesis	GV60	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
580	Genesis	GV70	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
581	Genesis	GV80	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
582	Geo	Metro	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
583	Geo	Prizm	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
584	Geo	Spectrum	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
585	Geo	Storm	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
586	Geo	Tracker	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
587	GMC	Acadia	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
588	GMC	C15	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
589	GMC	C25	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
590	GMC	C2500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
591	GMC	Caballero	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
592	GMC	Canyon	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
593	GMC	Envoy	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
594	GMC	EV1	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
595	GMC	G15/25	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
596	GMC	G35	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
597	GMC	Hummer	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
598	GMC	Jimmy	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
599	GMC	K15	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
600	GMC	K25	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
601	GMC	K2500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
602	GMC	R15	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
603	GMC	R1500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
604	GMC	R25	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
605	GMC	Rally	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
606	GMC	S15	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
607	GMC	Safari	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
608	GMC	Savana	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
609	GMC	Sierra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
610	GMC	Sonoma	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
611	GMC	Suburban	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
612	GMC	T15	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
613	GMC	Terrain	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
614	GMC	V15	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
615	GMC	V25	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
616	GMC	Vandura	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
617	GMC	Yukon	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
618	Honda	Accord	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
619	Honda	Civic	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
620	Honda	Clarity	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
621	Honda	Crosstour	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
622	Honda	CR-V	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
623	Honda	CR-Z	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
624	Honda	Del	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
625	Honda	Element	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
626	Honda	EV	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
627	Honda	FCX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
628	Honda	Fit	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
629	Honda	HR-V	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
630	Honda	Insight	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
631	Honda	Odyssey	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
632	Honda	Passport	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
633	Honda	Pilot	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
634	Honda	Prelude	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
635	Honda	Prologue	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
636	Honda	Ridgeline	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
637	Honda	S2000	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
638	Hummer	H3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
639	Hummer	H3T	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
640	Hyundai	Accent	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
641	Hyundai	Accent/Brio	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
642	Hyundai	Azera	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
643	Hyundai	Elantra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
644	Hyundai	Entourage	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
645	Hyundai	Equus	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
646	Hyundai	Excel	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
647	Hyundai	Genesis	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
648	Hyundai	Ioniq	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
649	Hyundai	J-Car/Elantra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
650	Hyundai	Kona	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
651	Hyundai	Nexo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
652	Hyundai	Palisade	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
653	Hyundai	Pony	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
654	Hyundai	Precis	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
655	Hyundai	Santa	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
656	Hyundai	Scoupe	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
657	Hyundai	Sonata	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
658	Hyundai	Tiburon	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
659	Hyundai	Tucson	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
660	Hyundai	Veloster	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
661	Hyundai	Venue	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
662	Hyundai	Veracruz	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
663	Hyundai	XG300	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
664	Hyundai	XG350	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
665	INEOS Automotive	Grenadier	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
666	Infiniti	EX35	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
667	Infiniti	EX37	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
668	Infiniti	FX35	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
669	Infiniti	FX37	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
670	Infiniti	FX45	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
671	Infiniti	FX50	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
672	Infiniti	G20	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
673	Infiniti	G25	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
674	Infiniti	G25x	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
675	Infiniti	G35	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
676	Infiniti	G35x	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
677	Infiniti	G37	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
678	Infiniti	G37x	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
679	Infiniti	I30	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
680	Infiniti	I35	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
681	Infiniti	J30	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
682	Infiniti	JX35	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
683	Infiniti	M30	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
684	Infiniti	M35	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
685	Infiniti	M35h	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
686	Infiniti	M35x	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
687	Infiniti	M37	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
688	Infiniti	M37x	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
689	Infiniti	M45	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
690	Infiniti	M45x	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
691	Infiniti	M56	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
692	Infiniti	M56x	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
693	Infiniti	Q40	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
694	Infiniti	Q45	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
695	Infiniti	Q50	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
696	Infiniti	Q50a	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
697	Infiniti	Q50S	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
698	Infiniti	Q60	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
699	Infiniti	Q60S	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
700	Infiniti	Q70	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
701	Infiniti	QX30	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
702	Infiniti	QX4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
703	Infiniti	QX50	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
704	Infiniti	QX55	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
705	Infiniti	QX56	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
706	Infiniti	QX60	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
707	Infiniti	QX70	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
708	Infiniti	QX80	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
709	Isuzu	750C/I-Mark	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
710	Isuzu	Amigo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
711	Isuzu	Ascender	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
712	Isuzu	Axiom	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
713	Isuzu	Hombre	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
714	Isuzu	i-280	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
715	Isuzu	i-290	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
716	Isuzu	i-350	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
717	Isuzu	i-370	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
718	Isuzu	I-Mark	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
719	Isuzu	Impulse	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
720	Isuzu	Oasis	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
721	Isuzu	Pickup	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
722	Isuzu	Rodeo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
723	Isuzu	Stylus	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
724	Isuzu	Trooper	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
725	Isuzu	Vehicross	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
726	Jaguar	E-Pace	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
727	Jaguar	F-Pace	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
728	Jaguar	F-Type	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
729	Jaguar	I-Pace	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
730	Jaguar	S-Type	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
731	Jaguar	Super	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
732	Jaguar	Vanden	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
733	Jaguar	Vdp	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
734	Jaguar	XE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
735	Jaguar	XF	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
736	Jaguar	XJ	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
737	Jaguar	XJ12	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
738	Jaguar	XJ6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
739	Jaguar	XJ6L	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
740	Jaguar	XJ8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
741	Jaguar	XJ8L	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
742	Jaguar	XJL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
743	Jaguar	XJR	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
744	Jaguar	XJRS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
745	Jaguar	XJS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
746	Jaguar	XJ-S	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
747	Jaguar	XJ-SC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
748	Jaguar	XK	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
749	Jaguar	XK8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
750	Jaguar	XKR	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
751	Jaguar	X-Type	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
752	Jeep	Cherokee	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
753	Jeep	Cherokee/Wagoneer	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
754	Jeep	CJ7	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
755	Jeep	CJ8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
756	Jeep	Comanche	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
757	Jeep	Commander	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
758	Jeep	Compass	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
759	Jeep	Gladiator	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
760	Jeep	Grand	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
761	Jeep	J-10	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
762	Jeep	J-20	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
763	Jeep	Liberty	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
764	Jeep	Liberty/Cherokee	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
765	Jeep	New	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
766	Jeep	Patriot	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
767	Jeep	Renegade	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
768	Jeep	Scrambler	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
769	Jeep	Wagoneer	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
770	Jeep	Wrangler	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
771	Jeep	Wrangler/TJ	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
772	Karma	GS-6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
773	Karma	Revero	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
774	Kia	Amanti	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
775	Kia	Borrego	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
776	Kia	Cadenza	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
777	Kia	Carnival	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
778	Kia	EV6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
779	Kia	EV9	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
780	Kia	Forte	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
781	Kia	K5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
782	Kia	K900	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
783	Kia	Niro	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
784	Kia	Optima	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
785	Kia	Rio	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
786	Kia	Rondo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
787	Kia	Sedona	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
788	Kia	Seltos	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
789	Kia	Sephia	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
790	Kia	Sephia/Spectra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
791	Kia	Sorento	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
792	Kia	Soul	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
793	Kia	Spectra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
794	Kia	Sportage	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
795	Kia	Stinger	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
796	Kia	Telluride	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
797	Koenigsegg	Agera	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
798	Koenigsegg	Regera	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
799	Lambda Control Systems	300E	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
800	Lamborghini	Aventador	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
801	Lamborghini	Countach	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
802	Lamborghini	DB132/144	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
803	Lamborghini	DB132/Diablo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
804	Lamborghini	Gallardo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
805	Lamborghini	Huracan	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
806	Lamborghini	L-140/141	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
807	Lamborghini	L-140/715	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
808	Lamborghini	L-147	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
809	Lamborghini	L-147/148	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
810	Lamborghini	Murcielago	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
811	Lamborghini	Urus	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
812	Lamborghini	Veneno	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
813	Land Rover	Defender	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
814	Land Rover	Discovery	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
815	Land Rover	Evoque	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
816	Land Rover	Freelander	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
817	Land Rover	LR2	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
818	Land Rover	LR3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
819	Land Rover	LR4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
820	Land Rover	New	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
821	Land Rover	Range	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
822	Lexus	CT	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
823	Lexus	ES	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
824	Lexus	GS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
825	Lexus	GS300	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
826	Lexus	GX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
827	Lexus	HS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
828	Lexus	IS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
829	Lexus	LC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
830	Lexus	LFA	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
831	Lexus	LS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
832	Lexus	LX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
833	Lexus	NX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
834	Lexus	RC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
835	Lexus	RX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
836	Lexus	RZ	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
837	Lexus	SC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
838	Lexus	TX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
839	Lexus	UX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
840	Lincoln	Aviator	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
841	Lincoln	Blackwood	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
842	Lincoln	Continental	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
843	Lincoln	Corsair	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
844	Lincoln	LS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
845	Lincoln	Mark	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
846	Lincoln	MKC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
847	Lincoln	MKS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
848	Lincoln	MKT	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
849	Lincoln	MKX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
850	Lincoln	MKZ	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
851	Lincoln	Nautilus	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
852	Lincoln	Navigator	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
853	Lincoln	Town	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
854	Lincoln	Zephyr	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
855	London Taxi	London	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
856	Lordstown	Endurance	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
857	Lotus	98	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
858	Lotus	Elan	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
859	Lotus	Elise/Exige	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
860	Lotus	Emira	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
861	Lotus	Esprit	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
862	Lotus	Evora	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
863	Lucid	Air	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
864	Mahindra	TR40	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
865	Maserati	222E	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
866	Maserati	225	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
867	Maserati	228	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
868	Maserati	430	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
869	Maserati	Biturbo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
870	Maserati	Coupe	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
871	Maserati	Ghibli	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
872	Maserati	Grancabrio	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
873	Maserati	GranTurismo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
874	Maserati	Grecale	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
875	Maserati	Karif	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
876	Maserati	Levante	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
877	Maserati	MC20	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
878	Maserati	Q	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
879	Maserati	Quattroporte	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
880	Maserati	Quattroporte/QP	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
881	Maserati	Spider	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
882	Maserati	Spyder	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
883	Maybach	57	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
884	Maybach	57S	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
885	Maybach	62	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
886	Maybach	62S	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
887	Maybach	Landaulet	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
888	Mazda	2	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
889	Mazda	2500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
891	Mazda	323	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
892	Mazda	323/323	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
893	Mazda	5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
894	Mazda	6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
895	Mazda	626	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
896	Mazda	626/MX-6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
897	Mazda	929	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
900	Mazda	B2000/B2200/B2600	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
901	Mazda	B2200/B2600	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
902	Mazda	B2200/B2600i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
903	Mazda	B2300	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
904	Mazda	B2300/B3000/B4000	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
905	Mazda	B2500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
906	Mazda	B2500/B3000/B4000	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
907	Mazda	B2600	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
908	Mazda	B2600i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
909	Mazda	B3000	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
910	Mazda	B4000	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
911	Mazda	CX-3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
912	Mazda	CX-30	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
913	Mazda	CX-5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
914	Mazda	CX-50	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
915	Mazda	CX-7	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
916	Mazda	CX-9	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
917	Mazda	CX-90	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
918	Mazda	GLC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
919	Mazda	Millenia	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
920	Mazda	MPV	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
921	Mazda	MX-3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
922	Mazda	MX-30	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
923	Mazda	MX-5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
924	Mazda	MX-6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
925	Mazda	Navajo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
926	Mazda	Protege	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
928	Mazda	RX-7	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
929	Mazda	RX-8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
930	Mazda	Speed	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
931	Mazda	Tribute	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
932	McLaren Automotive	540C	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
933	McLaren Automotive	570GT	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
934	McLaren Automotive	570S	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
935	McLaren Automotive	600LT	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
936	McLaren Automotive	620R	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
937	McLaren Automotive	650S	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
938	McLaren Automotive	675LT	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
939	McLaren Automotive	720S	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
940	McLaren Automotive	765LT	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
941	McLaren Automotive	Artura	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
942	McLaren Automotive	Elva	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
943	McLaren Automotive	GT	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
944	McLaren Automotive	MP4-12C	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
945	McLaren Automotive	MSO	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
946	McLaren Automotive	P1	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
947	McLaren Automotive	Sabre	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
948	McLaren Automotive	Senna	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
949	McLaren Automotive	Speedtail	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
950	Mercedes-Benz	190	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
951	Mercedes-Benz	190D	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
952	Mercedes-Benz	190E	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
953	Mercedes-Benz	200E	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
954	Mercedes-Benz	230CE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
955	Mercedes-Benz	230E	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
956	Mercedes-Benz	230TE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
957	Mercedes-Benz	260E	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
958	Mercedes-Benz	300CE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
959	Mercedes-Benz	300D	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
960	Mercedes-Benz	300D/300CD	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
961	Mercedes-Benz	300E	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
962	Mercedes-Benz	300SD	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
963	Mercedes-Benz	300SD/380SE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
964	Mercedes-Benz	300SDL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
965	Mercedes-Benz	300SE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
966	Mercedes-Benz	300SEL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
967	Mercedes-Benz	300SL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
968	Mercedes-Benz	300TD	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
969	Mercedes-Benz	300TE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
970	Mercedes-Benz	350SD	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
971	Mercedes-Benz	350SDL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
972	Mercedes-Benz	380SE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
973	Mercedes-Benz	380SL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
974	Mercedes-Benz	400E	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
975	Mercedes-Benz	400SE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
976	Mercedes-Benz	400SEL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
977	Mercedes-Benz	420	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
978	Mercedes-Benz	420SE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
979	Mercedes-Benz	420SEL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
980	Mercedes-Benz	500E	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
981	Mercedes-Benz	500SE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
982	Mercedes-Benz	500SEC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
983	Mercedes-Benz	500SEL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
984	Mercedes-Benz	500SL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
985	Mercedes-Benz	560SE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
986	Mercedes-Benz	560SEC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
987	Mercedes-Benz	560SEL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
988	Mercedes-Benz	560SL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
989	Mercedes-Benz	600SEL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
990	Mercedes-Benz	600SEL/SEC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
991	Mercedes-Benz	600SL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
992	Mercedes-Benz	A220	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
993	Mercedes-Benz	AMG	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
994	Mercedes-Benz	B250e	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
995	Mercedes-Benz	B-Class	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
996	Mercedes-Benz	C220	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
997	Mercedes-Benz	C230	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
998	Mercedes-Benz	C240	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
999	Mercedes-Benz	C250	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1000	Mercedes-Benz	C280	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1001	Mercedes-Benz	C300	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1002	Mercedes-Benz	C32	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1003	Mercedes-Benz	C320	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1004	Mercedes-Benz	C350	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1005	Mercedes-Benz	C350e	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1006	Mercedes-Benz	C36	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1007	Mercedes-Benz	C400	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1008	Mercedes-Benz	C43	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1009	Mercedes-Benz	C450	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1010	Mercedes-Benz	C55	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1011	Mercedes-Benz	C63	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1012	Mercedes-Benz	CL500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1013	Mercedes-Benz	CL55	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1014	Mercedes-Benz	CL550	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1015	Mercedes-Benz	CL600	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1016	Mercedes-Benz	CL63	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1017	Mercedes-Benz	CL65	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1018	Mercedes-Benz	CLA250	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1019	Mercedes-Benz	CLA45	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1020	Mercedes-Benz	CLE300	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1021	Mercedes-Benz	CLE450	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1022	Mercedes-Benz	CLK320	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1023	Mercedes-Benz	CLK350	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1024	Mercedes-Benz	CLK430	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1025	Mercedes-Benz	CLK500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1026	Mercedes-Benz	CLK55	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1027	Mercedes-Benz	CLK550	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1028	Mercedes-Benz	CLK63	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1029	Mercedes-Benz	CLS400	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1030	Mercedes-Benz	CLS450	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1031	Mercedes-Benz	CLS500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1032	Mercedes-Benz	CLS55	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1033	Mercedes-Benz	CLS550	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1034	Mercedes-Benz	CLS63	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1035	Mercedes-Benz	E300	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1036	Mercedes-Benz	E320	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1037	Mercedes-Benz	E350	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1038	Mercedes-Benz	E400	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1039	Mercedes-Benz	E420	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1040	Mercedes-Benz	E430	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1041	Mercedes-Benz	E450	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1042	Mercedes-Benz	E500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1043	Mercedes-Benz	E55	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1044	Mercedes-Benz	E550	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1045	Mercedes-Benz	E63	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1046	Mercedes-Benz	EQB	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1047	Mercedes-Benz	EQE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1048	Mercedes-Benz	EQS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1049	Mercedes-Benz	G500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1050	Mercedes-Benz	G55	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1051	Mercedes-Benz	G550	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1052	Mercedes-Benz	G63	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1053	Mercedes-Benz	GL320	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1054	Mercedes-Benz	GL450	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1055	Mercedes-Benz	GL550	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1056	Mercedes-Benz	GL63	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1057	Mercedes-Benz	GLA250	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1058	Mercedes-Benz	GLA45	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1059	Mercedes-Benz	GLB250	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1060	Mercedes-Benz	GLC300	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1061	Mercedes-Benz	GLC350e	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1062	Mercedes-Benz	GLE300	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1063	Mercedes-Benz	GLE350	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1064	Mercedes-Benz	GLE400	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1065	Mercedes-Benz	GLE450	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1066	Mercedes-Benz	GLE550e	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1067	Mercedes-Benz	GLE580	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1068	Mercedes-Benz	GLK250	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1069	Mercedes-Benz	GLK350	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1070	Mercedes-Benz	GLS450	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1071	Mercedes-Benz	GLS550	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1072	Mercedes-Benz	GLS580	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1073	Mercedes-Benz	GLS600	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1074	Mercedes-Benz	Maybach	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1075	Mercedes-Benz	Metris	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1076	Mercedes-Benz	ML250	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1077	Mercedes-Benz	ML320	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1078	Mercedes-Benz	ML350	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1079	Mercedes-Benz	ML400	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1080	Mercedes-Benz	ML430	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1081	Mercedes-Benz	ML450	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1082	Mercedes-Benz	ML500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1083	Mercedes-Benz	ML55	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1084	Mercedes-Benz	ML550	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1085	Mercedes-Benz	ML63	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1086	Mercedes-Benz	R320	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1087	Mercedes-Benz	R350	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1088	Mercedes-Benz	R500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1089	Mercedes-Benz	R63	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1090	Mercedes-Benz	S320	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1091	Mercedes-Benz	S350	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1092	Mercedes-Benz	S350D	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1093	Mercedes-Benz	S400	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1094	Mercedes-Benz	S420	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1095	Mercedes-Benz	S430	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1096	Mercedes-Benz	S450	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1097	Mercedes-Benz	S500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1098	Mercedes-Benz	S55	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1099	Mercedes-Benz	S550	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1100	Mercedes-Benz	S550e	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1101	Mercedes-Benz	S560	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1102	Mercedes-Benz	S560e	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1103	Mercedes-Benz	S580	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1104	Mercedes-Benz	S580e	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1105	Mercedes-Benz	S600	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1106	Mercedes-Benz	S63	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1107	Mercedes-Benz	S65	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1108	Mercedes-Benz	SL320	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1109	Mercedes-Benz	SL400	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1110	Mercedes-Benz	SL450	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1111	Mercedes-Benz	SL500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1112	Mercedes-Benz	SL55	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1113	Mercedes-Benz	SL550	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1114	Mercedes-Benz	SL600	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1115	Mercedes-Benz	SL63	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1116	Mercedes-Benz	SL65	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1117	Mercedes-Benz	SLC300	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1118	Mercedes-Benz	SLK230	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1119	Mercedes-Benz	SLK250	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1120	Mercedes-Benz	SLK280	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1121	Mercedes-Benz	SLK300	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1122	Mercedes-Benz	SLK32	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1123	Mercedes-Benz	SLK320	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1124	Mercedes-Benz	SLK350	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1125	Mercedes-Benz	SLK55	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1126	Mercedes-Benz	SLR	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1127	Mercedes-Benz	SLS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1128	Mercury	Capri	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1129	Mercury	Cougar	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1130	Mercury	Grand	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1131	Mercury	Lynx	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1132	Mercury	Marauder	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1133	Mercury	Mariner	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1134	Mercury	Marquis	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1135	Mercury	Milan	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1136	Mercury	Montego	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1137	Mercury	Monterey	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1138	Mercury	Mountaineer	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1139	Mercury	Mystique	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1140	Mercury	Sable	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1141	Mercury	Topaz	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1142	Mercury	Tracer	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1143	Mercury	Villager	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1144	Merkur	Scorpio	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1145	Merkur	XR4Ti	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1146	MINI	Clubman	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1147	MINI	Cooper	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1148	MINI	Countryman	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1149	MINI	JCW	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1150	MINI	John	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1151	MINI	MiniE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1152	Mitsubishi	3000	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1153	Mitsubishi	Cordia	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1154	Mitsubishi	Diamante	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1155	Mitsubishi	Eclipse	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1156	Mitsubishi	Endeavor	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1157	Mitsubishi	Expo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1158	Mitsubishi	Expo.LRV	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1159	Mitsubishi	Galant	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1160	Mitsubishi	i-MiEV	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1161	Mitsubishi	Lancer	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1162	Mitsubishi	Mirage	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1163	Mitsubishi	Montero	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1164	Mitsubishi	Nativa	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1165	Mitsubishi	Outlander	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1166	Mitsubishi	Precis	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1167	Mitsubishi	Raider	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1168	Mitsubishi	Sigma	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1169	Mitsubishi	Space	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1170	Mitsubishi	Starion	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1171	Mitsubishi	Tredia	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1172	Mitsubishi	Truck	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1173	Mitsubishi	Van	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1174	Mitsubishi	Wagon	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1175	Morgan	Plus	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1176	Nissan	200SX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1177	Nissan	240SX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1178	Nissan	300ZX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1179	Nissan	350z	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1180	Nissan	370z	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1181	Nissan	Altima	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1182	Nissan	Altra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1183	Nissan	ARIYA	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1184	Nissan	Armada	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1185	Nissan	Axxess	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1186	Nissan	Cube	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1187	Nissan	Frontier	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1188	Nissan	GT-R	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1189	Nissan	Hardbody	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1190	Nissan	Hyper-Mini	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1191	Nissan	Juke	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1192	Nissan	Kicks	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1193	Nissan	Leaf	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1194	Nissan	Maxima	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1195	Nissan	Murano	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1196	Nissan	NV200	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1197	Nissan	NX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1198	Nissan	Pathfinder	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1199	Nissan	Pickup	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1200	Nissan	Pulsar	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1201	Nissan	Pulsar/Pulsar-NX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1202	Nissan	Quest	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1203	Nissan	Rogue	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1204	Nissan	Sentra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1205	Nissan	Sentra/200SX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1206	Nissan	Stanza	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1207	Nissan	Titan	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1208	Nissan	Truck	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1209	Nissan	Van	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1210	Nissan	Van(cargo)	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1211	Nissan	Versa	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1212	Nissan	Xterra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1213	Nissan	Z	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1214	Pagani	Huayra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1215	Peugeot	405	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1216	Peugeot	505	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1217	Peugeot	604	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1218	Pininfarina	Spider	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1219	Plymouth	Acclaim	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1220	Plymouth	Breeze	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1221	Plymouth	Caravelle	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1222	Plymouth	Colt	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1223	Plymouth	Conquest	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1224	Plymouth	Gran	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1225	Plymouth	Horizon	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1226	Plymouth	Laser	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1227	Plymouth	Neon	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1228	Plymouth	Prowler	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1229	Plymouth	Reliant	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1230	Plymouth	Sundance	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1231	Plymouth	Sundance/Duster	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1232	Plymouth	Turismo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1233	Plymouth	Voyager	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1234	Plymouth	Voyager/Grand	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1235	Polestar	1	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1236	Polestar	2	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1237	Pontiac	1000	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1238	Pontiac	2000	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1239	Pontiac	20th	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1240	Pontiac	6000	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1241	Pontiac	Aztek	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1242	Pontiac	Bonneville	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1243	Pontiac	Fiero	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1244	Pontiac	Firebird	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1245	Pontiac	Firebird/Formula	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1246	Pontiac	Firebird/Trans	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1247	Pontiac	Firefly	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1248	Pontiac	G3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1249	Pontiac	G5	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1250	Pontiac	G5/Pursuit	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1251	Pontiac	G6	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1252	Pontiac	G8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1253	Pontiac	Grand	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1254	Pontiac	GTO	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1255	Pontiac	Lemans	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1256	Pontiac	Montana	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1257	Pontiac	Monterey	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1258	Pontiac	Parisienne	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1259	Pontiac	Phoenix	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1260	Pontiac	Safari	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1261	Pontiac	Solstice	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1262	Pontiac	Sunbird	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1263	Pontiac	Sunburst	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1264	Pontiac	Sunfire	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1265	Pontiac	Torrent	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1266	Pontiac	Trans	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1267	Pontiac	Turbo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1268	Pontiac	Vibe	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1269	Pontiac	Wave	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1270	Porsche	718	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1271	Porsche	911	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1272	Porsche	918	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1273	Porsche	924	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1274	Porsche	928	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1275	Porsche	944	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1276	Porsche	968	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1277	Porsche	Boxster	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1278	Porsche	Carrera	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1279	Porsche	Cayenne	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1280	Porsche	Cayenne/Coupe	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1281	Porsche	Cayman	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1282	Porsche	Macan	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1283	Porsche	New	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1284	Porsche	Panamera	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1285	Porsche	Targa	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1286	Porsche	Taycan	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1287	Porsche	Turbo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1288	Ram	1500	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1289	Ram	C/V	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1290	Ram	Promaster	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1291	Red Shift Ltd.	Delta	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1292	Renault	18i	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1293	Renault	Alliance	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1294	Renault	Alliance/Encore	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1295	Renault	Fuego	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1296	Renault	GTA	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1297	Renault	Sportwagon	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1298	Rivian	R1S	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1299	Rivian	R1T	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1300	Rolls-Royce	Azure	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1301	Rolls-Royce	Brooklands	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1302	Rolls-Royce	Brooklands/Brklnds	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1303	Rolls-Royce	Camargue	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1304	Rolls-Royce	Continental	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1305	Rolls-Royce	Corniche	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1306	Rolls-Royce	Corniche/Continental	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1307	Rolls-Royce	Cullinan	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1308	Rolls-Royce	Dawn	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1309	Rolls-Royce	Eight/Mulsan	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1310	Rolls-Royce	Eight/Mulsanne	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1311	Rolls-Royce	Flying	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1312	Rolls-Royce	Ghost	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1313	Rolls-Royce	Limousine	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1314	Rolls-Royce	Park	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1315	Rolls-Royce	Phantom	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1316	Rolls-Royce	Silver	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1317	Rolls-Royce	Spectre	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1318	Rolls-Royce	Spirit	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1319	Rolls-Royce	Turbo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1320	Rolls-Royce	Wraith	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1321	Saab	900	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1322	Saab	9000	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1323	Saab	900S	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1324	Saab	900SE	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1325	Saab	9-2x	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1326	Saab	3-Sep	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1327	Saab	9-3X	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1328	Saab	9-4X	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1329	Saab	5-Sep	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1330	Saab	9-7X	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1331	Saab	Convertible	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1332	Saleen	Mustang	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1333	Saleen	SSC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1334	Saturn	Astra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1335	Saturn	Aura	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1336	Saturn	Ion	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1337	Saturn	L100/200	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1338	Saturn	L200	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1339	Saturn	L300	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1340	Saturn	LS	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1341	Saturn	LW	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1342	Saturn	LW200	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1343	Saturn	LW300	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1344	Saturn	Outlook	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1345	Saturn	Relay	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1346	Saturn	SC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1347	Saturn	SKY	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1348	Saturn	SL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1349	Saturn	SW	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1350	Saturn	Vue	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1351	Scion	FR-S	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1352	Scion	iA	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1353	Scion	iM	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1354	Scion	iQ	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1355	Scion	tC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1356	Scion	xA	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1357	Scion	xB	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1358	Scion	xD	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1359	smart	EQ	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1360	smart	fortwo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1361	Spyker	C8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1362	Subaru	3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1363	Subaru	Ascent	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1364	Subaru	B9	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1365	Subaru	Baja	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1366	Subaru	Brat	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1367	Subaru	BRZ	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1368	Subaru	Crosstrek	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1369	Subaru	Forester	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1370	Subaru	Hatchback	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1371	Subaru	Impreza	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1372	Subaru	Justy	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1373	Subaru	Legacy	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1374	Subaru	Legacy/Outback	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1375	Subaru	Loyale	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1376	Subaru	Outback	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1377	Subaru	RX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1378	Subaru	Sedan	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1379	Subaru	Sedan/3	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1380	Subaru	Sedan/3Door	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1381	Subaru	Solterra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1382	Subaru	SVX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1383	Subaru	Tribeca	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1384	Subaru	Wagon	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1385	Subaru	WRX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1386	Subaru	XT	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1387	Subaru	XT-DL	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1388	Subaru	XV	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1389	Suzuki	Aerio	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1390	Suzuki	Equator	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1391	Suzuki	Esteem	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1392	Suzuki	Forenza	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1393	Suzuki	Forsa	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1394	Suzuki	Grand	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1395	Suzuki	Kizashi	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1396	Suzuki	Reno	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1397	Suzuki	SA310	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1398	Suzuki	Samurai	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1399	Suzuki	Sidekick	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1400	Suzuki	SJ	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1401	Suzuki	SJ410K	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1402	Suzuki	SW	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1403	Suzuki	Swift	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1404	Suzuki	SX4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1405	Suzuki	Verona	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1406	Suzuki	Vitara	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1407	Suzuki	X-90	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1408	Suzuki	XL7	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1409	Tesla	Model	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1410	Toyota	1-Ton	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1411	Toyota	4Runner	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1412	Toyota	86	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1413	Toyota	Avalon	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1414	Toyota	bZ4X	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1415	Toyota	Camry	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1416	Toyota	Cargo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1417	Toyota	Celica	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1418	Toyota	C-HR	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1419	Toyota	Corolla	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1420	Toyota	Cressida	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1421	Toyota	Crown	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1422	Toyota	Echo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1423	Toyota	FJ	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1424	Toyota	GR	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1425	Toyota	Grand	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1426	Toyota	Highlander	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1427	Toyota	Land Cruiser	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1428	Toyota	Land Cruiser Prado	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1429	Toyota	Land Cruiser 79 Series	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1430	Toyota	Fortuner	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1431	Toyota	Matrix	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1432	Toyota	Mirai	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1433	Toyota	MR2	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1434	Toyota	Paseo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1435	Toyota	Previa	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1436	Toyota	Prius	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1437	Toyota	RAV4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1438	Toyota	Sequoia	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1439	Toyota	Sienna	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1440	Toyota	Starlet	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1441	Toyota	Supra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1442	Toyota	T100	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1443	Toyota	Tacoma	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1444	Toyota	Tercel	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1445	Toyota	Truck	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1446	Toyota	Tundra	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1447	Toyota	Van	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1448	Toyota	Venza	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1449	Toyota	Yaris	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1450	TVR Engineering Ltd	TVR	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1451	Vector	Avtech	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1452	Vector	W8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1453	Vinfast	VF	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1454	Vixen Motor Company	21	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1455	Volkswagen	Arteon	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1456	Volkswagen	Atlas	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1457	Volkswagen	Beetle	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1458	Volkswagen	Cabrio	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1459	Volkswagen	Cabriolet	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1460	Volkswagen	CC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1461	Volkswagen	Corrado	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1462	Volkswagen	e-Golf	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1463	Volkswagen	Eos	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1464	Volkswagen	Eurovan	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1465	Volkswagen	Fox	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1466	Volkswagen	GLI	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1467	Volkswagen	Golf	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1468	Volkswagen	Golf/GTI	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1469	Volkswagen	Golf-R	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1470	Volkswagen	GTI	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1471	Volkswagen	ID.4	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1472	Volkswagen	Jetta	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1473	Volkswagen	New	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1474	Volkswagen	Passat	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1475	Volkswagen	Phaeton	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1476	Volkswagen	Quantum	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1477	Volkswagen	R32	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1478	Volkswagen	Rabbit	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1479	Volkswagen	Routan	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1480	Volkswagen	Scirocco	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1481	Volkswagen	Taos	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1482	Volkswagen	Tiguan	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1483	Volkswagen	Touareg	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1484	Volkswagen	Vanagon	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1485	Volkswagen	Vanagon/Camper	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1486	Volvo	240	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1487	Volvo	740	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1488	Volvo	740/760	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1489	Volvo	760	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1490	Volvo	780	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1491	Volvo	850	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1492	Volvo	940	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1493	Volvo	960	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1494	Volvo	960/S90	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1495	Volvo	C30	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1496	Volvo	C40	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1497	Volvo	C70	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1498	Volvo	Coupe	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1499	Volvo	New	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1500	Volvo	S40	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1501	Volvo	S60	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1502	Volvo	S70	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1503	Volvo	S80	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1504	Volvo	S80/S80	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1505	Volvo	S90	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1506	Volvo	V40	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1507	Volvo	V50	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1508	Volvo	V60	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1509	Volvo	V60CC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1510	Volvo	V70	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1511	Volvo	V90	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1512	Volvo	V90CC	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1513	Volvo	XC40	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1514	Volvo	XC60	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1515	Volvo	XC70	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1516	Volvo	XC90	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1517	VPG	MV-1	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1518	Yugo	GV	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1519	Yugo	GV/GVX	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1520	Yugo	Gy/yugo	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
890	Mazda	Axela	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
898	Mazda	BT-50	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
899	Mazda	CX-8	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
927	Mazda	Tribute	2025-01-11 16:46:52.000029	2025-01-11 16:46:52.000029	1
1521	Toyota	ISIS	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1522	Toyota	Chaser	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1523	Toyota	Sienta	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1524	Toyota	Roomy	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1525	Toyota	Axio	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1526	Toyota	Coaster	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1527	Toyota	Ipsum	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1528	Toyota	Succeed	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1529	Toyota	Auris	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1530	Toyota	Aurion	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1531	Toyota	Vanguard	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1532	Toyota	Aqua	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1533	Toyota	Vitz	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1534	Toyota	Estima	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1535	Toyota	Vista	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1536	Toyota	Harrier	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1537	Toyota	Noah	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1538	Toyota	Mark II	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1539	Toyota	Cresta	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1540	Toyota	Spacio	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1541	Toyota	Verso	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1542	Toyota	Pixis Epoch	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1543	Toyota	RunX	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1544	Toyota	Caldina	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1545	Toyota	Sai	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1546	Toyota	Premio	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1547	Toyota	Sprinter	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1548	Toyota	Porte	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1549	Toyota	Wish	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1550	Toyota	Esquire	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1551	Toyota	Ractis	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1552	Toyota	Passo Sette	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1553	Toyota	Hilux	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1554	Toyota	Opa	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1555	Toyota	Tank	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1556	Toyota	GT86	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1557	Toyota	Dyna	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1558	Toyota	Pixis Joy	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1559	Toyota	Pixis Space	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1560	Toyota	Raize	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1561	Toyota	Cami	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1562	Toyota	Allion	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1563	Toyota	Gaia	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1564	Toyota	Pixis Mega	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1565	Toyota	Corsa	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1566	Toyota	Kluger	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1567	Toyota	Sera	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1568	Toyota	Lite-Ace	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1569	Toyota	Granvia	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1570	Toyota	Allex	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1571	Toyota	IST	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1572	Toyota	Blade	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1573	Toyota	Hilux Surf	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1574	Toyota	WiLL VS	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1575	Toyota	Carib	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1576	Toyota	Avensis	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1577	Toyota	TownAce	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1578	Toyota	Fielder	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1579	Toyota	Probox	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1580	Toyota	Rush	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1581	Toyota	Avanza	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1582	Toyota	Mark X	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1583	Toyota	Belta	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1584	Toyota	Alphard	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1585	Toyota	Corona	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1586	Toyota	Voxy	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1587	Toyota	Fun Cargo	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1588	Toyota	Toyoace	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1589	Toyota	Passo	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1590	Toyota	Raum	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1591	Toyota	Altis	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1592	Toyota	Yaris Cross	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1593	Toyota	Corona Premio	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1594	Toyota	Urban Cruiser	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1595	Toyota	Platz	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1596	Toyota	Spade	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1597	Toyota	Altezza	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1598	Toyota	iQ	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1599	Toyota	Carina	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1600	Toyota	Duet	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1601	Toyota	Vellfire	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1602	Toyota	Rumion	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
1603	Toyota	Cross	2025-02-17 15:55:36.25227	2025-02-17 15:55:36.25227	1
\.


--
-- Name: allocations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.allocations_id_seq', 5, true);


--
-- Name: claim_events_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.claim_events_id_seq', 1, false);


--
-- Name: claims_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.claims_id_seq', 3, true);


--
-- Name: company_data_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.company_data_id_seq', 1, true);


--
-- Name: customers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.customers_id_seq', 35, true);


--
-- Name: documents_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.documents_id_seq', 1, false);


--
-- Name: endorsements_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.endorsements_id_seq', 3, true);


--
-- Name: failed_jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.failed_jobs_id_seq', 1, false);


--
-- Name: fees_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fees_id_seq', 1, false);


--
-- Name: fileno_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.fileno_seq', 80, true);


--
-- Name: insurers_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.insurers_id_seq', 44, true);


--
-- Name: jobs_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.jobs_id_seq', 1, false);


--
-- Name: leads_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.leads_id_seq', 1, false);


--
-- Name: migrations_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.migrations_id_seq', 37, true);


--
-- Name: payments_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.payments_id_seq', 8, true);


--
-- Name: policies_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.policies_id_seq', 39, true);


--
-- Name: policy_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.policy_types_id_seq', 70, true);


--
-- Name: receipts_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.receipts_id_seq', 8, true);


--
-- Name: renewal_notices_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.renewal_notices_id_seq', 1, false);


--
-- Name: renewals_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.renewals_id_seq', 1, false);


--
-- Name: reports_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.reports_id_seq', 1, true);


--
-- Name: users_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.users_id_seq', 6, true);


--
-- Name: vehicle_types_id_seq; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.vehicle_types_id_seq', 101, true);


--
-- Name: allocations allocations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.allocations
    ADD CONSTRAINT allocations_pkey PRIMARY KEY (id);


--
-- Name: cache_locks cache_locks_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache_locks
    ADD CONSTRAINT cache_locks_pkey PRIMARY KEY (key);


--
-- Name: cache cache_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.cache
    ADD CONSTRAINT cache_pkey PRIMARY KEY (key);


--
-- Name: claim_events claim_events_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.claim_events
    ADD CONSTRAINT claim_events_pkey PRIMARY KEY (id);


--
-- Name: claims claims_claim_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.claims
    ADD CONSTRAINT claims_claim_number_unique UNIQUE (claim_number);


--
-- Name: claims claims_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.claims
    ADD CONSTRAINT claims_pkey PRIMARY KEY (id);


--
-- Name: company_data company_data_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.company_data
    ADD CONSTRAINT company_data_pkey PRIMARY KEY (id);


--
-- Name: customers customers_customer_code_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_customer_code_key UNIQUE (customer_code);


--
-- Name: customers customers_kra_pin_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_kra_pin_unique UNIQUE (kra_pin);


--
-- Name: customers customers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.customers
    ADD CONSTRAINT customers_pkey PRIMARY KEY (id);


--
-- Name: documents documents_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT documents_pkey PRIMARY KEY (id);


--
-- Name: endorsements endorsements_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.endorsements
    ADD CONSTRAINT endorsements_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_pkey PRIMARY KEY (id);


--
-- Name: failed_jobs failed_jobs_uuid_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.failed_jobs
    ADD CONSTRAINT failed_jobs_uuid_unique UNIQUE (uuid);


--
-- Name: fees fees_invoice_number_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fees
    ADD CONSTRAINT fees_invoice_number_key UNIQUE (invoice_number);


--
-- Name: fees fees_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fees
    ADD CONSTRAINT fees_pkey PRIMARY KEY (id);


--
-- Name: insurers insurers_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.insurers
    ADD CONSTRAINT insurers_pkey PRIMARY KEY (id);


--
-- Name: job_batches job_batches_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.job_batches
    ADD CONSTRAINT job_batches_pkey PRIMARY KEY (id);


--
-- Name: jobs jobs_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.jobs
    ADD CONSTRAINT jobs_pkey PRIMARY KEY (id);


--
-- Name: leads leads_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.leads
    ADD CONSTRAINT leads_pkey PRIMARY KEY (id);


--
-- Name: migrations migrations_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.migrations
    ADD CONSTRAINT migrations_pkey PRIMARY KEY (id);


--
-- Name: password_reset_tokens password_reset_tokens_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.password_reset_tokens
    ADD CONSTRAINT password_reset_tokens_pkey PRIMARY KEY (email);


--
-- Name: payments payments_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.payments
    ADD CONSTRAINT payments_pkey PRIMARY KEY (id);


--
-- Name: policies policies_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.policies
    ADD CONSTRAINT policies_pkey PRIMARY KEY (id);


--
-- Name: policy_types policy_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.policy_types
    ADD CONSTRAINT policy_types_pkey PRIMARY KEY (id);


--
-- Name: receipts receipts_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipts
    ADD CONSTRAINT receipts_pkey PRIMARY KEY (id);


--
-- Name: receipts receipts_receipt_number_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipts
    ADD CONSTRAINT receipts_receipt_number_unique UNIQUE (receipt_number);


--
-- Name: renewal_notices renewal_notices_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.renewal_notices
    ADD CONSTRAINT renewal_notices_pkey PRIMARY KEY (id);


--
-- Name: renewals renewals_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.renewals
    ADD CONSTRAINT renewals_pkey PRIMARY KEY (id);


--
-- Name: reports reports_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.reports
    ADD CONSTRAINT reports_pkey PRIMARY KEY (id);


--
-- Name: sessions sessions_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sessions
    ADD CONSTRAINT sessions_pkey PRIMARY KEY (id);


--
-- Name: users users_email_unique; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_email_unique UNIQUE (email);


--
-- Name: users users_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.users
    ADD CONSTRAINT users_pkey PRIMARY KEY (id);


--
-- Name: vehicle_types vehicle_types_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.vehicle_types
    ADD CONSTRAINT vehicle_types_pkey PRIMARY KEY (id);


--
-- Name: company_data_company_name_unique; Type: INDEX; Schema: public; Owner: postgres
--

CREATE UNIQUE INDEX company_data_company_name_unique ON public.company_data USING btree (company_name);


--
-- Name: company_data_email_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX company_data_email_index ON public.company_data USING btree (email);


--
-- Name: idx_documents_claim_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_documents_claim_id ON public.documents USING btree (claim_id);


--
-- Name: idx_documents_tag; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_documents_tag ON public.documents USING btree (tag);


--
-- Name: idx_documents_uploaded_by; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_documents_uploaded_by ON public.documents USING btree (uploaded_by);


--
-- Name: jobs_queue_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX jobs_queue_index ON public.jobs USING btree (queue);


--
-- Name: leads_closing_date_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_closing_date_index ON public.leads USING btree (closing_date);


--
-- Name: leads_company_name_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_company_name_index ON public.leads USING btree (company_name);


--
-- Name: leads_date_initiated_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_date_initiated_index ON public.leads USING btree (date_initiated);


--
-- Name: leads_deal_stage_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_deal_stage_index ON public.leads USING btree (deal_stage);


--
-- Name: leads_deal_status_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX leads_deal_status_index ON public.leads USING btree (deal_status);


--
-- Name: renewal_notices_policy_id_idx; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX renewal_notices_policy_id_idx ON public.renewal_notices USING btree (policy_id);


--
-- Name: sessions_last_activity_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_last_activity_index ON public.sessions USING btree (last_activity);


--
-- Name: sessions_user_id_index; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX sessions_user_id_index ON public.sessions USING btree (user_id);


--
-- Name: fees trigger_update_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER trigger_update_updated_at BEFORE UPDATE ON public.fees FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: documents update_documents_updated_at; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER update_documents_updated_at BEFORE UPDATE ON public.documents FOR EACH ROW EXECUTE FUNCTION public.update_updated_at_column();


--
-- Name: allocations allocations_payment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.allocations
    ADD CONSTRAINT allocations_payment_id_foreign FOREIGN KEY (payment_id) REFERENCES public.payments(id) ON DELETE CASCADE;


--
-- Name: allocations allocations_policy_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.allocations
    ADD CONSTRAINT allocations_policy_id_foreign FOREIGN KEY (policy_id) REFERENCES public.policies(id) ON DELETE CASCADE;


--
-- Name: claim_events claim_events_claim_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.claim_events
    ADD CONSTRAINT claim_events_claim_id_foreign FOREIGN KEY (claim_id) REFERENCES public.claims(id) ON DELETE CASCADE;


--
-- Name: claims claims_policy_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.claims
    ADD CONSTRAINT claims_policy_id_foreign FOREIGN KEY (policy_id) REFERENCES public.policies(id) ON DELETE CASCADE;


--
-- Name: documents fk_documents_claim_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT fk_documents_claim_id FOREIGN KEY (claim_id) REFERENCES public.claims(id) ON DELETE CASCADE;


--
-- Name: documents fk_documents_uploaded_by; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.documents
    ADD CONSTRAINT fk_documents_uploaded_by FOREIGN KEY (uploaded_by) REFERENCES public.users(id) ON DELETE SET NULL;


--
-- Name: fees fk_fees_customer; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.fees
    ADD CONSTRAINT fk_fees_customer FOREIGN KEY (customer_code) REFERENCES public.customers(customer_code) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: endorsements fk_policy; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.endorsements
    ADD CONSTRAINT fk_policy FOREIGN KEY (policy_id) REFERENCES public.policies(id) ON DELETE CASCADE;


--
-- Name: renewal_notices fk_renewal_notices_policy_id; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.renewal_notices
    ADD CONSTRAINT fk_renewal_notices_policy_id FOREIGN KEY (policy_id) REFERENCES public.policies(id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: policies policies_insurer_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.policies
    ADD CONSTRAINT policies_insurer_id_foreign FOREIGN KEY (insurer_id) REFERENCES public.insurers(id);


--
-- Name: policies policies_policy_type_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.policies
    ADD CONSTRAINT policies_policy_type_id_foreign FOREIGN KEY (policy_type_id) REFERENCES public.policy_types(id);


--
-- Name: policies policies_vehicle_type_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.policies
    ADD CONSTRAINT policies_vehicle_type_id_foreign FOREIGN KEY (vehicle_type_id) REFERENCES public.vehicle_types(id);


--
-- Name: receipts receipts_payment_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.receipts
    ADD CONSTRAINT receipts_payment_id_foreign FOREIGN KEY (payment_id) REFERENCES public.payments(id) ON DELETE CASCADE;


--
-- Name: renewals renewals_original_policy_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.renewals
    ADD CONSTRAINT renewals_original_policy_id_foreign FOREIGN KEY (original_policy_id) REFERENCES public.policies(id);


--
-- Name: renewals renewals_renewed_policy_id_foreign; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.renewals
    ADD CONSTRAINT renewals_renewed_policy_id_foreign FOREIGN KEY (renewed_policy_id) REFERENCES public.policies(id);


--
-- Name: TABLE allocations; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.allocations TO tetezi_user;


--
-- Name: SEQUENCE allocations_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.allocations_id_seq TO tetezi_user;


--
-- Name: TABLE cache; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.cache TO tetezi_user;


--
-- Name: TABLE cache_locks; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.cache_locks TO tetezi_user;


--
-- Name: TABLE claim_events; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.claim_events TO tetezi_user;


--
-- Name: SEQUENCE claim_events_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.claim_events_id_seq TO tetezi_user;


--
-- Name: TABLE claims; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.claims TO tetezi_user;


--
-- Name: SEQUENCE claims_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.claims_id_seq TO tetezi_user;


--
-- Name: TABLE company_data; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.company_data TO tetezi_user;


--
-- Name: SEQUENCE company_data_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.company_data_id_seq TO tetezi_user;


--
-- Name: TABLE customers; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.customers TO tetezi_user;


--
-- Name: SEQUENCE customers_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.customers_id_seq TO tetezi_user;


--
-- Name: TABLE documents; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.documents TO tetezi_user;


--
-- Name: SEQUENCE documents_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.documents_id_seq TO tetezi_user;


--
-- Name: TABLE endorsements; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.endorsements TO tetezi_user;


--
-- Name: SEQUENCE endorsements_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.endorsements_id_seq TO tetezi_user;


--
-- Name: TABLE failed_jobs; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.failed_jobs TO tetezi_user;


--
-- Name: SEQUENCE failed_jobs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.failed_jobs_id_seq TO tetezi_user;


--
-- Name: TABLE fees; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.fees TO tetezi_user;


--
-- Name: SEQUENCE fees_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.fees_id_seq TO tetezi_user;


--
-- Name: SEQUENCE fileno_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.fileno_seq TO tetezi_user;


--
-- Name: TABLE insurers; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.insurers TO tetezi_user;


--
-- Name: SEQUENCE insurers_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.insurers_id_seq TO tetezi_user;


--
-- Name: TABLE job_batches; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.job_batches TO tetezi_user;


--
-- Name: TABLE jobs; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.jobs TO tetezi_user;


--
-- Name: SEQUENCE jobs_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.jobs_id_seq TO tetezi_user;


--
-- Name: TABLE leads; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.leads TO tetezi_user;


--
-- Name: SEQUENCE leads_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.leads_id_seq TO tetezi_user;


--
-- Name: TABLE migrations; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.migrations TO tetezi_user;


--
-- Name: SEQUENCE migrations_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.migrations_id_seq TO tetezi_user;


--
-- Name: TABLE password_reset_tokens; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.password_reset_tokens TO tetezi_user;


--
-- Name: TABLE payments; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.payments TO tetezi_user;


--
-- Name: SEQUENCE payments_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.payments_id_seq TO tetezi_user;


--
-- Name: TABLE policies; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.policies TO tetezi_user;


--
-- Name: SEQUENCE policies_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.policies_id_seq TO tetezi_user;


--
-- Name: TABLE policy_types; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.policy_types TO tetezi_user;


--
-- Name: SEQUENCE policy_types_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.policy_types_id_seq TO tetezi_user;


--
-- Name: TABLE receipts; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.receipts TO tetezi_user;


--
-- Name: SEQUENCE receipts_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.receipts_id_seq TO tetezi_user;


--
-- Name: TABLE renewal_notices; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.renewal_notices TO tetezi_user;


--
-- Name: SEQUENCE renewal_notices_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.renewal_notices_id_seq TO tetezi_user;


--
-- Name: TABLE renewals; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.renewals TO tetezi_user;


--
-- Name: SEQUENCE renewals_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.renewals_id_seq TO tetezi_user;


--
-- Name: TABLE reports; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.reports TO tetezi_user;


--
-- Name: SEQUENCE reports_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.reports_id_seq TO tetezi_user;


--
-- Name: TABLE sessions; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.sessions TO tetezi_user;


--
-- Name: TABLE users; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.users TO tetezi_user;


--
-- Name: SEQUENCE users_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.users_id_seq TO tetezi_user;


--
-- Name: TABLE vehicle_types; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,INSERT,DELETE,UPDATE ON TABLE public.vehicle_types TO tetezi_user;


--
-- Name: SEQUENCE vehicle_types_id_seq; Type: ACL; Schema: public; Owner: postgres
--

GRANT SELECT,USAGE ON SEQUENCE public.vehicle_types_id_seq TO tetezi_user;


--
-- Name: DEFAULT PRIVILEGES FOR TABLES; Type: DEFAULT ACL; Schema: public; Owner: postgres
--

ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT SELECT,INSERT,DELETE,UPDATE ON TABLES TO postgres;
ALTER DEFAULT PRIVILEGES FOR ROLE postgres IN SCHEMA public GRANT SELECT,INSERT,DELETE,UPDATE ON TABLES TO tetezi_user;


--
-- PostgreSQL database dump complete
--

\unrestrict KE4mqKdaPUbtQfRAcCI4TnwiboaSsuAoR9k6riYdhbDPww0xhSGfVMDf3cvK8wO

