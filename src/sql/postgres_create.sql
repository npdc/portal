--
-- npdcQL database dump
--

-- Dumped from database version 11.7 (Debian 11.7-0+deb10u1)
-- Dumped by pg_dump version 11.7 (Debian 11.7-0+deb10u1)

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
-- Name: npdc; Type: SCHEMA; Schema: -; Owner: npdc
--

CREATE SCHEMA npdc;


ALTER SCHEMA npdc OWNER TO npdc;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: access_request; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.access_request (
    access_request_id bigint NOT NULL,
    person_id bigint NOT NULL,
    reason text NOT NULL,
    request_timestamp timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    permitted smallint,
    response text,
    response_timestamp timestamp with time zone,
    dataset_id bigint,
    zip_id bigint,
    responder_id bigint
);


ALTER TABLE npdc.access_request OWNER TO npdc;

--
-- Name: access_request_access_request_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.access_request_access_request_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.access_request_access_request_id_seq OWNER TO npdc;

--
-- Name: access_request_access_request_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.access_request_access_request_id_seq OWNED BY npdc.access_request.access_request_id;


--
-- Name: access_request_file; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.access_request_file (
    access_request_file_id bigint NOT NULL,
    access_request_id bigint NOT NULL,
    file_id bigint NOT NULL,
    permitted smallint DEFAULT '0'::smallint NOT NULL
);


ALTER TABLE npdc.access_request_file OWNER TO npdc;

--
-- Name: access_request_file_access_request_file_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.access_request_file_access_request_file_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.access_request_file_access_request_file_id_seq OWNER TO npdc;

--
-- Name: access_request_file_access_request_file_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.access_request_file_access_request_file_id_seq OWNED BY npdc.access_request_file.access_request_file_id;


--
-- Name: account_new; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.account_new (
    account_new_id bigint NOT NULL,
    code text NOT NULL,
    request_time timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    used_time timestamp with time zone,
    expire_reason text,
    mail text
);


ALTER TABLE npdc.account_new OWNER TO npdc;

--
-- Name: account_new_account_new_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.account_new_account_new_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.account_new_account_new_id_seq OWNER TO npdc;

--
-- Name: account_new_account_new_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.account_new_account_new_id_seq OWNED BY npdc.account_new.account_new_id;


--
-- Name: account_reset; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.account_reset (
    account_reset_id bigint NOT NULL,
    person_id bigint NOT NULL,
    code text NOT NULL,
    request_time timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    used_time timestamp with time zone,
    expire_reason text
);


ALTER TABLE npdc.account_reset OWNER TO npdc;

--
-- Name: account_reset_account_reset_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.account_reset_account_reset_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.account_reset_account_reset_id_seq OWNER TO npdc;

--
-- Name: account_reset_account_reset_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.account_reset_account_reset_id_seq OWNED BY npdc.account_reset.account_reset_id;


--
-- Name: additional_attributes; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.additional_attributes (
    additional_attributes_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    name text NOT NULL,
    datatype text NOT NULL,
    description text NOT NULL,
    measurement_resolution text,
    parameter_range_begin text,
    parameter_range_end text,
    parameter_units_of_measure text,
    parameter_value_accuracy text,
    value_accuracy_explanation text,
    value text,
    dataset_version_min bigint NOT NULL
);


ALTER TABLE npdc.additional_attributes OWNER TO npdc;

--
-- Name: additional_attributes_additional_attributes_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.additional_attributes_additional_attributes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.additional_attributes_additional_attributes_id_seq OWNER TO npdc;

--
-- Name: additional_attributes_additional_attributes_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.additional_attributes_additional_attributes_id_seq OWNED BY npdc.additional_attributes.additional_attributes_id;


--
-- Name: characteristics; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.characteristics (
    characteristics_id bigint NOT NULL,
    name text NOT NULL,
    description text NOT NULL,
    unit text NOT NULL,
    value text NOT NULL,
    platform_id bigint,
    instrument_id bigint,
    sensor_id bigint,
    data_type text,
    dataset_version_min bigint,
    dataset_version_max bigint
);


ALTER TABLE npdc.characteristics OWNER TO npdc;

--
-- Name: characteristics_characteristics_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.characteristics_characteristics_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.characteristics_characteristics_id_seq OWNER TO npdc;

--
-- Name: characteristics_characteristics_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.characteristics_characteristics_id_seq OWNED BY npdc.characteristics.characteristics_id;


--
-- Name: contact; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.contact (
    contact_id bigint NOT NULL,
    receiver text NOT NULL,
    sender_mail text NOT NULL,
    sender_name text NOT NULL,
    subject text,
    text text NOT NULL,
    country text,
    ip character varying(100) DEFAULT NULL::character varying,
    browser text,
    "timestamp" timestamp with time zone DEFAULT CURRENT_TIMESTAMP
);


ALTER TABLE npdc.contact OWNER TO npdc;

--
-- Name: COLUMN contact.country; Type: COMMENT; Schema: npdc; Owner: npdc
--

COMMENT ON COLUMN npdc.contact.country IS 'this should be empty, is the anti-spam field';


--
-- Name: contact_contact_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.contact_contact_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.contact_contact_id_seq OWNER TO npdc;

--
-- Name: contact_contact_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.contact_contact_id_seq OWNED BY npdc.contact.contact_id;


--
-- Name: continent; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.continent (
    continent_id character(2) NOT NULL,
    continent_name text
);


ALTER TABLE npdc.continent OWNER TO npdc;

--
-- Name: country; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.country (
    country_id character(2) NOT NULL,
    country_name text,
    continent_id character(2) DEFAULT NULL::bpchar
);


ALTER TABLE npdc.country OWNER TO npdc;

--
-- Name: data_center_person_default; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.data_center_person_default (
    organization_id bigint NOT NULL,
    person_id bigint NOT NULL
);


ALTER TABLE npdc.data_center_person_default OWNER TO npdc;

--
-- Name: data_resolution; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.data_resolution (
    data_resolution_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    latitude_resolution text,
    longitude_resolution text,
    vocab_res_hor_id bigint,
    vertical_resolution text,
    vocab_res_vert_id bigint,
    temporal_resolution text,
    vocab_res_time_id bigint,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint
);


ALTER TABLE npdc.data_resolution OWNER TO npdc;

--
-- Name: data_resolution_data_resolution_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.data_resolution_data_resolution_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.data_resolution_data_resolution_id_seq OWNER TO npdc;

--
-- Name: data_resolution_data_resolution_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.data_resolution_data_resolution_id_seq OWNED BY npdc.data_resolution.data_resolution_id;


--
-- Name: dataset; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.dataset (
    dataset_id bigint NOT NULL,
    dataset_version bigint NOT NULL,
    dif_id text,
    published timestamp with time zone,
    title text NOT NULL,
    summary text NOT NULL,
    region character varying(10) NOT NULL,
    date_start date,
    date_end date,
    quality text,
    access_constraints text,
    use_constraints text,
    dataset_progress text,
    originating_center bigint,
    dif_revision_history text,
    version_description text,
    product_level_id text,
    collection_data_type text,
    extended_metadata text,
    record_status character varying(9) NOT NULL,
    purpose text,
    insert_timestamp timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    creator bigint NOT NULL,
    ipy boolean DEFAULT false NOT NULL,
    uuid character varying(36) DEFAULT NULL::character varying,
    created_from character varying(36) DEFAULT NULL::character varying,
    license character varying(10) DEFAULT NULL::character varying
);


ALTER TABLE npdc.dataset OWNER TO npdc;

--
-- Name: dataset_ancillary_keyword; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.dataset_ancillary_keyword (
    dataset_ancillary_keyword_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint,
    keyword text NOT NULL
);


ALTER TABLE npdc.dataset_ancillary_keyword OWNER TO npdc;

--
-- Name: dataset_ancillary_keyword_dataset_ancillary_keyword_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.dataset_ancillary_keyword_dataset_ancillary_keyword_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.dataset_ancillary_keyword_dataset_ancillary_keyword_id_seq OWNER TO npdc;

--
-- Name: dataset_ancillary_keyword_dataset_ancillary_keyword_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.dataset_ancillary_keyword_dataset_ancillary_keyword_id_seq OWNED BY npdc.dataset_ancillary_keyword.dataset_ancillary_keyword_id;


--
-- Name: dataset_citation; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.dataset_citation (
    dataset_citation_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    creator text,
    editor text,
    title text,
    series_name text,
    release_date date,
    release_place text,
    publisher text,
    version text,
    issue_identification text,
    presentation_form text,
    other text,
    persistent_identifier_type text,
    persistent_identifier_identifier text,
    online_resource text,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint,
    type text
);


ALTER TABLE npdc.dataset_citation OWNER TO npdc;

--
-- Name: dataset_citation_dataset_citation_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.dataset_citation_dataset_citation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.dataset_citation_dataset_citation_id_seq OWNER TO npdc;

--
-- Name: dataset_citation_dataset_citation_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.dataset_citation_dataset_citation_id_seq OWNED BY npdc.dataset_citation.dataset_citation_id;


--
-- Name: dataset_data_center; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.dataset_data_center (
    dataset_data_center_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint,
    organization_id bigint NOT NULL
);


ALTER TABLE npdc.dataset_data_center OWNER TO npdc;

--
-- Name: dataset_data_center_dataset_data_center_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.dataset_data_center_dataset_data_center_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.dataset_data_center_dataset_data_center_id_seq OWNER TO npdc;

--
-- Name: dataset_data_center_dataset_data_center_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.dataset_data_center_dataset_data_center_id_seq OWNED BY npdc.dataset_data_center.dataset_data_center_id;


--
-- Name: dataset_data_center_person; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.dataset_data_center_person (
    dataset_data_center_person_id bigint NOT NULL,
    dataset_data_center_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint,
    person_id bigint NOT NULL
);


ALTER TABLE npdc.dataset_data_center_person OWNER TO npdc;

--
-- Name: dataset_data_center_person_dataset_data_center_person_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.dataset_data_center_person_dataset_data_center_person_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.dataset_data_center_person_dataset_data_center_person_id_seq OWNER TO npdc;

--
-- Name: dataset_data_center_person_dataset_data_center_person_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.dataset_data_center_person_dataset_data_center_person_id_seq OWNED BY npdc.dataset_data_center_person.dataset_data_center_person_id;


--
-- Name: dataset_dataset_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.dataset_dataset_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.dataset_dataset_id_seq OWNER TO npdc;

--
-- Name: dataset_dataset_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.dataset_dataset_id_seq OWNED BY npdc.dataset.dataset_id;


--
-- Name: dataset_file; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.dataset_file (
    dataset_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint,
    file_id bigint NOT NULL
);


ALTER TABLE npdc.dataset_file OWNER TO npdc;

--
-- Name: dataset_keyword; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.dataset_keyword (
    dataset_keyword_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    vocab_science_keyword_id bigint NOT NULL,
    free_text text,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint
);


ALTER TABLE npdc.dataset_keyword OWNER TO npdc;

--
-- Name: dataset_keyword_dataset_keyword_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.dataset_keyword_dataset_keyword_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.dataset_keyword_dataset_keyword_id_seq OWNER TO npdc;

--
-- Name: dataset_keyword_dataset_keyword_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.dataset_keyword_dataset_keyword_id_seq OWNED BY npdc.dataset_keyword.dataset_keyword_id;


--
-- Name: dataset_link; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.dataset_link (
    dataset_link_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    title text NOT NULL,
    vocab_url_type_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    description text,
    mime_type_id bigint,
    protocol text,
    dataset_version_max bigint
);


ALTER TABLE npdc.dataset_link OWNER TO npdc;

--
-- Name: dataset_link_dataset_link_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.dataset_link_dataset_link_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.dataset_link_dataset_link_id_seq OWNER TO npdc;

--
-- Name: dataset_link_dataset_link_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.dataset_link_dataset_link_id_seq OWNED BY npdc.dataset_link.dataset_link_id;


--
-- Name: dataset_link_url; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.dataset_link_url (
    dataset_link_url_id bigint NOT NULL,
    dataset_link_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint,
    url text,
    old_dataset_link_url_id bigint
);


ALTER TABLE npdc.dataset_link_url OWNER TO npdc;

--
-- Name: dataset_link_url_dataset_link_url_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.dataset_link_url_dataset_link_url_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.dataset_link_url_dataset_link_url_id_seq OWNER TO npdc;

--
-- Name: dataset_link_url_dataset_link_url_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.dataset_link_url_dataset_link_url_id_seq OWNED BY npdc.dataset_link_url.dataset_link_url_id;


--
-- Name: dataset_person; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.dataset_person (
    dataset_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    person_id bigint NOT NULL,
    organization_id bigint,
    editor smallint DEFAULT '0'::smallint NOT NULL,
    sort bigint NOT NULL,
    dataset_version_max bigint,
    role text
);


ALTER TABLE npdc.dataset_person OWNER TO npdc;

--
-- Name: dataset_project; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.dataset_project (
    dataset_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    project_version_min bigint NOT NULL,
    dataset_version_max bigint,
    project_version_max bigint,
    project_id bigint NOT NULL
);


ALTER TABLE npdc.dataset_project OWNER TO npdc;

--
-- Name: dataset_publication; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.dataset_publication (
    publication_id bigint NOT NULL,
    publication_version_min bigint NOT NULL,
    dataset_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    publication_version_max bigint,
    dataset_version_max bigint
);


ALTER TABLE npdc.dataset_publication OWNER TO npdc;

--
-- Name: dataset_topic; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.dataset_topic (
    vocab_iso_topic_category_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint
);


ALTER TABLE npdc.dataset_topic OWNER TO npdc;

--
-- Name: distribution; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.distribution (
    distribution_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    media text NOT NULL,
    size text NOT NULL,
    format text NOT NULL,
    fees text NOT NULL,
    dataset_version_min bigint NOT NULL
);


ALTER TABLE npdc.distribution OWNER TO npdc;

--
-- Name: distribution_distribution_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.distribution_distribution_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.distribution_distribution_id_seq OWNER TO npdc;

--
-- Name: distribution_distribution_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.distribution_distribution_id_seq OWNED BY npdc.distribution.distribution_id;


--
-- Name: file; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.file (
    file_id bigint NOT NULL,
    name text,
    location text,
    type text,
    size bigint,
    default_access character varying(13) DEFAULT 'private'::character varying NOT NULL,
    description text,
    insert_timestamp timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    record_state character varying(9) DEFAULT 'draft'::character varying NOT NULL,
    title text,
    form_id text NOT NULL
);


ALTER TABLE npdc.file OWNER TO npdc;

--
-- Name: file_file_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.file_file_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.file_file_id_seq OWNER TO npdc;

--
-- Name: file_file_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.file_file_id_seq OWNED BY npdc.file.file_id;


--
-- Name: instrument; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.instrument (
    instrument_id bigint NOT NULL,
    platform_id bigint NOT NULL,
    vocab_instrument_id bigint NOT NULL,
    number_of_sensors bigint,
    operational_mode text,
    technique text,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint,
    old_instrument_id bigint
);


ALTER TABLE npdc.instrument OWNER TO npdc;

--
-- Name: instrument_instrument_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.instrument_instrument_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.instrument_instrument_id_seq OWNER TO npdc;

--
-- Name: instrument_instrument_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.instrument_instrument_id_seq OWNED BY npdc.instrument.instrument_id;


--
-- Name: location; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.location (
    location_id bigint NOT NULL,
    vocab_location_id bigint NOT NULL,
    detailed text,
    dataset_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint
);


ALTER TABLE npdc.location OWNER TO npdc;

--
-- Name: location_location_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.location_location_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.location_location_id_seq OWNER TO npdc;

--
-- Name: location_location_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.location_location_id_seq OWNED BY npdc.location.location_id;


--
-- Name: menu; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.menu (
    menu_id bigint NOT NULL,
    label text NOT NULL,
    url text,
    parent_menu_id bigint,
    sort bigint NOT NULL,
    min_user_level text NOT NULL
);


ALTER TABLE npdc.menu OWNER TO npdc;

--
-- Name: menu_menu_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.menu_menu_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.menu_menu_id_seq OWNER TO npdc;

--
-- Name: menu_menu_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.menu_menu_id_seq OWNED BY npdc.menu.menu_id;


--
-- Name: metadata_association; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.metadata_association (
    metadata_association_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    entry_id text NOT NULL,
    type text NOT NULL,
    description text,
    dataset_version_min bigint NOT NULL
);


ALTER TABLE npdc.metadata_association OWNER TO npdc;

--
-- Name: metadata_association_metadata_association_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.metadata_association_metadata_association_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.metadata_association_metadata_association_id_seq OWNER TO npdc;

--
-- Name: metadata_association_metadata_association_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.metadata_association_metadata_association_id_seq OWNED BY npdc.metadata_association.metadata_association_id;


--
-- Name: mime_type; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.mime_type (
    mime_type_id bigint NOT NULL,
    label text,
    type text,
    visible smallint DEFAULT '1'::smallint NOT NULL
);


ALTER TABLE npdc.mime_type OWNER TO npdc;

--
-- Name: mime_type_mime_type_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.mime_type_mime_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.mime_type_mime_type_id_seq OWNER TO npdc;

--
-- Name: mime_type_mime_type_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.mime_type_mime_type_id_seq OWNED BY npdc.mime_type.mime_type_id;


--
-- Name: multimedia_sample; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.multimedia_sample (
    multimedia_sample_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    file text,
    url text NOT NULL,
    format text,
    caption text,
    description text,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint
);


ALTER TABLE npdc.multimedia_sample OWNER TO npdc;

--
-- Name: multimedia_sample_multimedia_sample_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.multimedia_sample_multimedia_sample_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.multimedia_sample_multimedia_sample_id_seq OWNER TO npdc;

--
-- Name: multimedia_sample_multimedia_sample_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.multimedia_sample_multimedia_sample_id_seq OWNED BY npdc.multimedia_sample.multimedia_sample_id;


--
-- Name: news; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.news (
    news_id bigint NOT NULL,
    title text NOT NULL,
    content text NOT NULL,
    published timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    show_till timestamp with time zone,
    link text
);


ALTER TABLE npdc.news OWNER TO npdc;

--
-- Name: news_news_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.news_news_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.news_news_id_seq OWNER TO npdc;

--
-- Name: news_news_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.news_news_id_seq OWNED BY npdc.news.news_id;


--
-- Name: npp_theme; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.npp_theme (
    npp_theme_id bigint NOT NULL,
    theme_nl character varying(100) DEFAULT NULL::character varying,
    theme_en character varying(100) DEFAULT NULL::character varying
);


ALTER TABLE npdc.npp_theme OWNER TO npdc;

--
-- Name: npp_theme_npp_theme_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.npp_theme_npp_theme_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.npp_theme_npp_theme_id_seq OWNER TO npdc;

--
-- Name: npp_theme_npp_theme_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.npp_theme_npp_theme_id_seq OWNED BY npdc.npp_theme.npp_theme_id;


--
-- Name: organization; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.organization (
    organization_id bigint NOT NULL,
    organization_name text NOT NULL,
    organization_address text,
    organization_zip text,
    organization_city text,
    visiting_address text,
    edmo bigint,
    dif_code text,
    dif_name text,
    website text,
    country_id character(2) DEFAULT 'NL'::bpchar,
    uuid text,
    historic_name text
);


ALTER TABLE npdc.organization OWNER TO npdc;

--
-- Name: COLUMN organization.historic_name; Type: COMMENT; Schema: npdc; Owner: npdc
--

COMMENT ON COLUMN npdc.organization.historic_name IS 'multiple values allowed, comma separated';


--
-- Name: organization_organization_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.organization_organization_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.organization_organization_id_seq OWNER TO npdc;

--
-- Name: organization_organization_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.organization_organization_id_seq OWNED BY npdc.organization.organization_id;


--
-- Name: page; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.page (
    page_id bigint NOT NULL,
    title text NOT NULL,
    content text NOT NULL,
    url text NOT NULL,
    last_update timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    show_last_revision boolean DEFAULT false
);


ALTER TABLE npdc.page OWNER TO npdc;

--
-- Name: page_link; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.page_link (
    page_link_id bigint NOT NULL,
    page_id bigint NOT NULL,
    url text NOT NULL,
    text text NOT NULL,
    sort bigint NOT NULL
);


ALTER TABLE npdc.page_link OWNER TO npdc;

--
-- Name: page_link_page_link_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.page_link_page_link_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.page_link_page_link_id_seq OWNER TO npdc;

--
-- Name: page_link_page_link_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.page_link_page_link_id_seq OWNED BY npdc.page_link.page_link_id;


--
-- Name: page_page_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.page_page_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.page_page_id_seq OWNER TO npdc;

--
-- Name: page_page_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.page_page_id_seq OWNED BY npdc.page.page_id;


--
-- Name: page_person; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.page_person (
    page_id bigint NOT NULL,
    person_id bigint NOT NULL,
    role text NOT NULL,
    editor smallint DEFAULT '0'::smallint NOT NULL,
    sort bigint NOT NULL
);


ALTER TABLE npdc.page_person OWNER TO npdc;

--
-- Name: person; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.person (
    person_id bigint NOT NULL,
    organization_id bigint,
    name text NOT NULL,
    titles text,
    initials text,
    given_name text,
    surname text,
    mail text,
    phone_personal text,
    phone_secretariat text,
    phone_mobile text,
    address text,
    zip text,
    city text,
    sees_participant text,
    language text,
    password text,
    user_level character varying(9) DEFAULT 'user'::character varying NOT NULL,
    orcid character(16) DEFAULT NULL::bpchar,
    phone_personal_public boolean DEFAULT true NOT NULL,
    phone_secretariat_public boolean DEFAULT true NOT NULL,
    phone_mobile_public boolean DEFAULT false NOT NULL,
    sex character(1) DEFAULT NULL::bpchar
);


ALTER TABLE npdc.person OWNER TO npdc;

--
-- Name: person_person_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.person_person_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.person_person_id_seq OWNER TO npdc;

--
-- Name: person_person_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.person_person_id_seq OWNED BY npdc.person.person_id;


--
-- Name: platform; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.platform (
    platform_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    vocab_platform_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint
);


ALTER TABLE npdc.platform OWNER TO npdc;

--
-- Name: platform_platform_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.platform_platform_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.platform_platform_id_seq OWNER TO npdc;

--
-- Name: platform_platform_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.platform_platform_id_seq OWNED BY npdc.platform.platform_id;


--
-- Name: program; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.program (
    program_id bigint NOT NULL,
    name text NOT NULL,
    program_start date NOT NULL,
    program_end date,
    sort smallint
);


ALTER TABLE npdc.program OWNER TO npdc;

--
-- Name: program_program_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.program_program_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.program_program_id_seq OWNER TO npdc;

--
-- Name: program_program_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.program_program_id_seq OWNED BY npdc.program.program_id;


--
-- Name: project; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.project (
    project_id bigint NOT NULL,
    project_version bigint NOT NULL,
    nwo_project_id character varying(20) DEFAULT NULL::character varying,
    title text NOT NULL,
    acronym text,
    region text NOT NULL,
    summary text,
    program_id bigint,
    date_start date,
    date_end date,
    ris_id bigint,
    proposal_status text,
    data_status text,
    research_type text,
    science_field text,
    data_type text,
    comments text,
    record_status character varying(9) NOT NULL,
    insert_timestamp timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    creator bigint NOT NULL,
    published timestamp with time zone,
    uuid character varying(36) DEFAULT NULL::character varying,
    npp_theme_id bigint
);


ALTER TABLE npdc.project OWNER TO npdc;

--
-- Name: project_keyword; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.project_keyword (
    project_keyword_id bigint NOT NULL,
    keyword text NOT NULL,
    project_version_min bigint NOT NULL,
    project_version_max bigint,
    project_id bigint NOT NULL
);


ALTER TABLE npdc.project_keyword OWNER TO npdc;

--
-- Name: project_keyword_project_keyword_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.project_keyword_project_keyword_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.project_keyword_project_keyword_id_seq OWNER TO npdc;

--
-- Name: project_keyword_project_keyword_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.project_keyword_project_keyword_id_seq OWNED BY npdc.project_keyword.project_keyword_id;


--
-- Name: project_link; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.project_link (
    project_link_id bigint NOT NULL,
    url text NOT NULL,
    text text NOT NULL,
    project_version_min bigint NOT NULL,
    project_version_max bigint,
    project_id bigint NOT NULL
);


ALTER TABLE npdc.project_link OWNER TO npdc;

--
-- Name: project_link_project_link_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.project_link_project_link_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.project_link_project_link_id_seq OWNER TO npdc;

--
-- Name: project_link_project_link_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.project_link_project_link_id_seq OWNED BY npdc.project_link.project_link_id;


--
-- Name: project_person; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.project_person (
    person_id bigint NOT NULL,
    organization_id bigint,
    project_version_min bigint NOT NULL,
    project_version_max bigint,
    role text NOT NULL,
    sort bigint NOT NULL,
    contact smallint DEFAULT '1'::smallint NOT NULL,
    editor smallint DEFAULT '0'::smallint NOT NULL,
    project_id bigint NOT NULL
);


ALTER TABLE npdc.project_person OWNER TO npdc;

--
-- Name: project_project; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.project_project (
    parent_project_id bigint NOT NULL,
    child_project_id bigint NOT NULL
);


ALTER TABLE npdc.project_project OWNER TO npdc;

--
-- Name: project_project_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.project_project_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.project_project_id_seq OWNER TO npdc;

--
-- Name: project_project_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.project_project_id_seq OWNED BY npdc.project.project_id;


--
-- Name: project_publication; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.project_publication (
    publication_id bigint NOT NULL,
    publication_version_min bigint NOT NULL,
    project_version_min bigint NOT NULL,
    publication_version_max bigint,
    project_version_max bigint,
    project_id bigint NOT NULL
);


ALTER TABLE npdc.project_publication OWNER TO npdc;

--
-- Name: publication; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.publication (
    publication_id bigint NOT NULL,
    publication_version bigint NOT NULL,
    title text NOT NULL,
    abstract text,
    journal text,
    volume text,
    issue text,
    pages text,
    isbn text,
    doi text,
    record_status character varying(9) NOT NULL,
    date date,
    url text,
    insert_timestamp timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    creator bigint NOT NULL,
    published timestamp with time zone,
    uuid character varying(36) DEFAULT NULL::character varying,
    publication_type_id bigint
);


ALTER TABLE npdc.publication OWNER TO npdc;

--
-- Name: publication_keyword; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.publication_keyword (
    publication_keyword_id bigint NOT NULL,
    publication_id bigint NOT NULL,
    keyword text NOT NULL,
    publication_version_min bigint NOT NULL,
    publication_version_max bigint
);


ALTER TABLE npdc.publication_keyword OWNER TO npdc;

--
-- Name: publication_keyword_publication_keyword_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.publication_keyword_publication_keyword_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.publication_keyword_publication_keyword_id_seq OWNER TO npdc;

--
-- Name: publication_keyword_publication_keyword_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.publication_keyword_publication_keyword_id_seq OWNED BY npdc.publication_keyword.publication_keyword_id;


--
-- Name: publication_person; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.publication_person (
    publication_person_id bigint NOT NULL,
    publication_id bigint NOT NULL,
    publication_version_min bigint NOT NULL,
    person_id bigint,
    organization_id bigint,
    free_person character varying(255) DEFAULT NULL::character varying,
    sort bigint NOT NULL,
    contact smallint DEFAULT '0'::smallint NOT NULL,
    publication_version_max bigint,
    editor smallint DEFAULT '0'::smallint NOT NULL,
    free_organization character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE npdc.publication_person OWNER TO npdc;

--
-- Name: publication_person_publication_person_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.publication_person_publication_person_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.publication_person_publication_person_id_seq OWNER TO npdc;

--
-- Name: publication_person_publication_person_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.publication_person_publication_person_id_seq OWNED BY npdc.publication_person.publication_person_id;


--
-- Name: publication_publication_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.publication_publication_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.publication_publication_id_seq OWNER TO npdc;

--
-- Name: publication_publication_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.publication_publication_id_seq OWNED BY npdc.publication.publication_id;


--
-- Name: publication_type; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.publication_type (
    publication_type_id bigint NOT NULL,
    label character varying(100) DEFAULT NULL::character varying,
    bib character varying(100) NOT NULL,
    ris character varying(100) DEFAULT NULL::character varying,
    description character varying(100) DEFAULT NULL::character varying
);


ALTER TABLE npdc.publication_type OWNER TO npdc;

--
-- Name: publication_type_publication_type_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.publication_type_publication_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.publication_type_publication_type_id_seq OWNER TO npdc;

--
-- Name: publication_type_publication_type_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.publication_type_publication_type_id_seq OWNED BY npdc.publication_type.publication_type_id;


--
-- Name: record_status; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.record_status (
    record_status character varying(9) NOT NULL,
    editable smallint NOT NULL,
    visible smallint NOT NULL
);


ALTER TABLE npdc.record_status OWNER TO npdc;

--
-- Name: record_status_change; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.record_status_change (
    project_id bigint,
    dataset_id bigint,
    publication_id bigint,
    old_state text NOT NULL,
    new_state text NOT NULL,
    person_id bigint NOT NULL,
    datetime timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    comment text,
    version bigint,
    record_status_change_id bigint NOT NULL
);


ALTER TABLE npdc.record_status_change OWNER TO npdc;

--
-- Name: record_status_change_record_status_change_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.record_status_change_record_status_change_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.record_status_change_record_status_change_id_seq OWNER TO npdc;

--
-- Name: record_status_change_record_status_change_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.record_status_change_record_status_change_id_seq OWNED BY npdc.record_status_change.record_status_change_id;


--
-- Name: related_dataset; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.related_dataset (
    related_dataset_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint,
    url character varying(100) DEFAULT NULL::character varying,
    doi character varying(100) DEFAULT NULL::character varying,
    internal_related_dataset_id bigint,
    relation character varying(255) DEFAULT NULL::character varying,
    same boolean NOT NULL
);


ALTER TABLE npdc.related_dataset OWNER TO npdc;

--
-- Name: related_dataset_related_dataset_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.related_dataset_related_dataset_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.related_dataset_related_dataset_id_seq OWNER TO npdc;

--
-- Name: related_dataset_related_dataset_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.related_dataset_related_dataset_id_seq OWNED BY npdc.related_dataset.related_dataset_id;


--
-- Name: sensor; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.sensor (
    sensor_id bigint NOT NULL,
    instrument_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint,
    vocab_instrument_id bigint,
    technique text,
    old_sensor_id bigint
);


ALTER TABLE npdc.sensor OWNER TO npdc;

--
-- Name: sensor_sensor_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.sensor_sensor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.sensor_sensor_id_seq OWNER TO npdc;

--
-- Name: sensor_sensor_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.sensor_sensor_id_seq OWNED BY npdc.sensor.sensor_id;


--
-- Name: spatial_coverage; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.spatial_coverage (
    spatial_coverage_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint,
    wkt text,
    depth_min double precision,
    depth_max double precision,
    depth_unit text,
    altitude_min double precision,
    altitude_max double precision,
    altitude_unit text,
    type text,
    label character varying(255) DEFAULT NULL::character varying
);


ALTER TABLE npdc.spatial_coverage OWNER TO npdc;

--
-- Name: spatial_coverage_spatial_coverage_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.spatial_coverage_spatial_coverage_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.spatial_coverage_spatial_coverage_id_seq OWNER TO npdc;

--
-- Name: spatial_coverage_spatial_coverage_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.spatial_coverage_spatial_coverage_id_seq OWNED BY npdc.spatial_coverage.spatial_coverage_id;


--
-- Name: suggestion; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.suggestion (
    suggestion_id bigint NOT NULL,
    field character varying(45) NOT NULL,
    suggestion text NOT NULL
);


ALTER TABLE npdc.suggestion OWNER TO npdc;

--
-- Name: suggestion_suggestion_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.suggestion_suggestion_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.suggestion_suggestion_id_seq OWNER TO npdc;

--
-- Name: suggestion_suggestion_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.suggestion_suggestion_id_seq OWNED BY npdc.suggestion.suggestion_id;


--
-- Name: temporal_coverage; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.temporal_coverage (
    temporal_coverage_id bigint NOT NULL,
    dataset_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint
);


ALTER TABLE npdc.temporal_coverage OWNER TO npdc;

--
-- Name: temporal_coverage_ancillary; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.temporal_coverage_ancillary (
    temporal_coverage_ancillary_id bigint NOT NULL,
    temporal_coverage_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint,
    keyword text
);


ALTER TABLE npdc.temporal_coverage_ancillary OWNER TO npdc;

--
-- Name: temporal_coverage_ancillary_temporal_coverage_ancillary_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.temporal_coverage_ancillary_temporal_coverage_ancillary_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.temporal_coverage_ancillary_temporal_coverage_ancillary_id_seq OWNER TO npdc;

--
-- Name: temporal_coverage_ancillary_temporal_coverage_ancillary_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.temporal_coverage_ancillary_temporal_coverage_ancillary_id_seq OWNED BY npdc.temporal_coverage_ancillary.temporal_coverage_ancillary_id;


--
-- Name: temporal_coverage_cycle; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.temporal_coverage_cycle (
    temporal_coverage_cycle_id bigint NOT NULL,
    temporal_coverage_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint,
    name text NOT NULL,
    date_start date NOT NULL,
    date_end date NOT NULL,
    sampling_frequency double precision NOT NULL,
    sampling_frequency_unit text NOT NULL
);


ALTER TABLE npdc.temporal_coverage_cycle OWNER TO npdc;

--
-- Name: temporal_coverage_cycle_temporal_coverage_cycle_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.temporal_coverage_cycle_temporal_coverage_cycle_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.temporal_coverage_cycle_temporal_coverage_cycle_id_seq OWNER TO npdc;

--
-- Name: temporal_coverage_cycle_temporal_coverage_cycle_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.temporal_coverage_cycle_temporal_coverage_cycle_id_seq OWNED BY npdc.temporal_coverage_cycle.temporal_coverage_cycle_id;


--
-- Name: temporal_coverage_paleo; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.temporal_coverage_paleo (
    temporal_coverage_paleo_id bigint NOT NULL,
    temporal_coverage_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint,
    start_value double precision,
    start_unit text,
    end_value double precision,
    end_unit text
);


ALTER TABLE npdc.temporal_coverage_paleo OWNER TO npdc;

--
-- Name: temporal_coverage_paleo_chronounit; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.temporal_coverage_paleo_chronounit (
    temporal_coverage_paleo_chronounit_id bigint NOT NULL,
    temporal_coverage_paleo_id bigint DEFAULT '0'::bigint NOT NULL,
    dataset_version_min bigint DEFAULT '0'::bigint NOT NULL,
    dataset_version_max bigint,
    vocab_chronounit_id bigint DEFAULT '0'::bigint NOT NULL
);


ALTER TABLE npdc.temporal_coverage_paleo_chronounit OWNER TO npdc;

--
-- Name: temporal_coverage_paleo_chron_temporal_coverage_paleo_chron_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.temporal_coverage_paleo_chron_temporal_coverage_paleo_chron_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.temporal_coverage_paleo_chron_temporal_coverage_paleo_chron_seq OWNER TO npdc;

--
-- Name: temporal_coverage_paleo_chron_temporal_coverage_paleo_chron_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.temporal_coverage_paleo_chron_temporal_coverage_paleo_chron_seq OWNED BY npdc.temporal_coverage_paleo_chronounit.temporal_coverage_paleo_chronounit_id;


--
-- Name: temporal_coverage_paleo_temporal_coverage_paleo_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.temporal_coverage_paleo_temporal_coverage_paleo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.temporal_coverage_paleo_temporal_coverage_paleo_id_seq OWNER TO npdc;

--
-- Name: temporal_coverage_paleo_temporal_coverage_paleo_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.temporal_coverage_paleo_temporal_coverage_paleo_id_seq OWNED BY npdc.temporal_coverage_paleo.temporal_coverage_paleo_id;


--
-- Name: temporal_coverage_period; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.temporal_coverage_period (
    temporal_coverage_period_id bigint NOT NULL,
    temporal_coverage_id bigint NOT NULL,
    dataset_version_min bigint NOT NULL,
    dataset_version_max bigint,
    date_start date NOT NULL,
    date_end date NOT NULL
);


ALTER TABLE npdc.temporal_coverage_period OWNER TO npdc;

--
-- Name: temporal_coverage_period_temporal_coverage_period_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.temporal_coverage_period_temporal_coverage_period_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.temporal_coverage_period_temporal_coverage_period_id_seq OWNER TO npdc;

--
-- Name: temporal_coverage_period_temporal_coverage_period_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.temporal_coverage_period_temporal_coverage_period_id_seq OWNED BY npdc.temporal_coverage_period.temporal_coverage_period_id;


--
-- Name: temporal_coverage_temporal_coverage_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.temporal_coverage_temporal_coverage_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.temporal_coverage_temporal_coverage_id_seq OWNER TO npdc;

--
-- Name: temporal_coverage_temporal_coverage_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.temporal_coverage_temporal_coverage_id_seq OWNED BY npdc.temporal_coverage.temporal_coverage_id;


--
-- Name: user_level; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.user_level (
    user_level_id bigint NOT NULL,
    label character varying(9) NOT NULL,
    description text,
    name text NOT NULL
);


ALTER TABLE npdc.user_level OWNER TO npdc;

--
-- Name: user_level_user_level_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.user_level_user_level_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.user_level_user_level_id_seq OWNER TO npdc;

--
-- Name: user_level_user_level_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.user_level_user_level_id_seq OWNED BY npdc.user_level.user_level_id;


--
-- Name: vocab; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.vocab (
    vocab_id bigint NOT NULL,
    vocab_name text NOT NULL,
    last_update_date date,
    last_update_local date,
    sync smallint DEFAULT '0'::smallint NOT NULL
);


ALTER TABLE npdc.vocab OWNER TO npdc;

--
-- Name: vocab_chronounit; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.vocab_chronounit (
    vocab_chronounit_id bigint NOT NULL,
    eon text,
    era text,
    period text,
    epoch text,
    stage text,
    uuid character varying(36) DEFAULT NULL::character varying,
    visible smallint DEFAULT '1'::smallint NOT NULL,
    sort bigint
);


ALTER TABLE npdc.vocab_chronounit OWNER TO npdc;

--
-- Name: vocab_chronounit_vocab_chronounit_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.vocab_chronounit_vocab_chronounit_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.vocab_chronounit_vocab_chronounit_id_seq OWNER TO npdc;

--
-- Name: vocab_chronounit_vocab_chronounit_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.vocab_chronounit_vocab_chronounit_id_seq OWNED BY npdc.vocab_chronounit.vocab_chronounit_id;


--
-- Name: vocab_idn_node; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.vocab_idn_node (
    vocab_idn_node_id bigint NOT NULL,
    short_name text NOT NULL,
    long_name text,
    uuid character varying(36) NOT NULL,
    visible smallint DEFAULT '1'::smallint NOT NULL
);


ALTER TABLE npdc.vocab_idn_node OWNER TO npdc;

--
-- Name: vocab_idn_node_vocab_idn_node_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.vocab_idn_node_vocab_idn_node_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.vocab_idn_node_vocab_idn_node_id_seq OWNER TO npdc;

--
-- Name: vocab_idn_node_vocab_idn_node_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.vocab_idn_node_vocab_idn_node_id_seq OWNED BY npdc.vocab_idn_node.vocab_idn_node_id;


--
-- Name: vocab_instrument; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.vocab_instrument (
    vocab_instrument_id bigint NOT NULL,
    category text NOT NULL,
    class text,
    type text,
    subtype text,
    short_name text,
    long_name text,
    uuid character varying(36) DEFAULT NULL::character varying,
    visible smallint DEFAULT '1'::smallint NOT NULL
);


ALTER TABLE npdc.vocab_instrument OWNER TO npdc;

--
-- Name: vocab_instrument_vocab_instrument_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.vocab_instrument_vocab_instrument_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.vocab_instrument_vocab_instrument_id_seq OWNER TO npdc;

--
-- Name: vocab_instrument_vocab_instrument_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.vocab_instrument_vocab_instrument_id_seq OWNED BY npdc.vocab_instrument.vocab_instrument_id;


--
-- Name: vocab_iso_topic_category; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.vocab_iso_topic_category (
    vocab_iso_topic_category_id bigint NOT NULL,
    topic text NOT NULL,
    description text,
    visible smallint DEFAULT '1'::smallint NOT NULL
);


ALTER TABLE npdc.vocab_iso_topic_category OWNER TO npdc;

--
-- Name: vocab_iso_topic_category_vocab_iso_topic_category_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.vocab_iso_topic_category_vocab_iso_topic_category_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.vocab_iso_topic_category_vocab_iso_topic_category_id_seq OWNER TO npdc;

--
-- Name: vocab_iso_topic_category_vocab_iso_topic_category_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.vocab_iso_topic_category_vocab_iso_topic_category_id_seq OWNED BY npdc.vocab_iso_topic_category.vocab_iso_topic_category_id;


--
-- Name: vocab_location; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.vocab_location (
    vocab_location_id bigint NOT NULL,
    location_category text NOT NULL,
    location_type text,
    location_subregion1 text,
    location_subregion2 text,
    location_subregion3 text,
    uuid text NOT NULL,
    visible smallint DEFAULT '1'::smallint NOT NULL
);


ALTER TABLE npdc.vocab_location OWNER TO npdc;

--
-- Name: vocab_location_vocab_idn_node; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.vocab_location_vocab_idn_node (
    vocab_location_id bigint NOT NULL,
    vocab_idn_node_id bigint NOT NULL
);


ALTER TABLE npdc.vocab_location_vocab_idn_node OWNER TO npdc;

--
-- Name: vocab_location_vocab_location_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.vocab_location_vocab_location_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.vocab_location_vocab_location_id_seq OWNER TO npdc;

--
-- Name: vocab_location_vocab_location_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.vocab_location_vocab_location_id_seq OWNED BY npdc.vocab_location.vocab_location_id;


--
-- Name: vocab_organization; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.vocab_organization (
    lvl0 text,
    lvl1 text,
    lvl2 text,
    lvl3 text,
    short_name text,
    long_name text,
    url text,
    uuid text
);


ALTER TABLE npdc.vocab_organization OWNER TO npdc;

--
-- Name: vocab_platform; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.vocab_platform (
    vocab_platform_id bigint NOT NULL,
    category text NOT NULL,
    series_entity text,
    short_name text,
    long_name text,
    uuid text NOT NULL,
    visible smallint DEFAULT '1'::smallint NOT NULL
);


ALTER TABLE npdc.vocab_platform OWNER TO npdc;

--
-- Name: vocab_platform_vocab_platform_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.vocab_platform_vocab_platform_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.vocab_platform_vocab_platform_id_seq OWNER TO npdc;

--
-- Name: vocab_platform_vocab_platform_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.vocab_platform_vocab_platform_id_seq OWNED BY npdc.vocab_platform.vocab_platform_id;


--
-- Name: vocab_res_hor; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.vocab_res_hor (
    vocab_res_hor_id bigint NOT NULL,
    range text NOT NULL,
    uuid character varying(36) NOT NULL,
    sort bigint NOT NULL,
    visible smallint DEFAULT '1'::smallint NOT NULL
);


ALTER TABLE npdc.vocab_res_hor OWNER TO npdc;

--
-- Name: vocab_res_hor_vocab_res_hor_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.vocab_res_hor_vocab_res_hor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.vocab_res_hor_vocab_res_hor_id_seq OWNER TO npdc;

--
-- Name: vocab_res_hor_vocab_res_hor_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.vocab_res_hor_vocab_res_hor_id_seq OWNED BY npdc.vocab_res_hor.vocab_res_hor_id;


--
-- Name: vocab_res_time; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.vocab_res_time (
    vocab_res_time_id bigint NOT NULL,
    range text NOT NULL,
    uuid text NOT NULL,
    sort bigint NOT NULL,
    visible smallint DEFAULT '1'::smallint NOT NULL
);


ALTER TABLE npdc.vocab_res_time OWNER TO npdc;

--
-- Name: vocab_res_time_vocab_res_time_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.vocab_res_time_vocab_res_time_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.vocab_res_time_vocab_res_time_id_seq OWNER TO npdc;

--
-- Name: vocab_res_time_vocab_res_time_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.vocab_res_time_vocab_res_time_id_seq OWNED BY npdc.vocab_res_time.vocab_res_time_id;


--
-- Name: vocab_res_vert; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.vocab_res_vert (
    vocab_res_vert_id bigint NOT NULL,
    range text NOT NULL,
    uuid text NOT NULL,
    sort bigint NOT NULL,
    visible smallint DEFAULT '1'::smallint NOT NULL
);


ALTER TABLE npdc.vocab_res_vert OWNER TO npdc;

--
-- Name: vocab_res_vert_vocab_res_vert_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.vocab_res_vert_vocab_res_vert_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.vocab_res_vert_vocab_res_vert_id_seq OWNER TO npdc;

--
-- Name: vocab_res_vert_vocab_res_vert_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.vocab_res_vert_vocab_res_vert_id_seq OWNED BY npdc.vocab_res_vert.vocab_res_vert_id;


--
-- Name: vocab_science_keyword; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.vocab_science_keyword (
    vocab_science_keyword_id bigint NOT NULL,
    category text NOT NULL,
    topic text,
    term text,
    var_lvl_1 text,
    var_lvl_2 text,
    var_lvl_3 text,
    uuid text NOT NULL,
    detailed_variable text,
    visible smallint DEFAULT '1'::smallint NOT NULL
);


ALTER TABLE npdc.vocab_science_keyword OWNER TO npdc;

--
-- Name: vocab_science_keyword_vocab_science_keyword_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.vocab_science_keyword_vocab_science_keyword_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.vocab_science_keyword_vocab_science_keyword_id_seq OWNER TO npdc;

--
-- Name: vocab_science_keyword_vocab_science_keyword_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.vocab_science_keyword_vocab_science_keyword_id_seq OWNED BY npdc.vocab_science_keyword.vocab_science_keyword_id;


--
-- Name: vocab_url_type; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.vocab_url_type (
    vocab_url_type_id bigint NOT NULL,
    type text NOT NULL,
    subtype text,
    uuid text NOT NULL,
    visible smallint DEFAULT '1'::smallint NOT NULL
);


ALTER TABLE npdc.vocab_url_type OWNER TO npdc;

--
-- Name: vocab_url_type_vocab_url_type_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.vocab_url_type_vocab_url_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.vocab_url_type_vocab_url_type_id_seq OWNER TO npdc;

--
-- Name: vocab_url_type_vocab_url_type_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.vocab_url_type_vocab_url_type_id_seq OWNED BY npdc.vocab_url_type.vocab_url_type_id;


--
-- Name: zip; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.zip (
    zip_id bigint NOT NULL,
    filename text NOT NULL,
    person_id bigint,
    guest_user text,
    "timestamp" timestamp with time zone DEFAULT CURRENT_TIMESTAMP NOT NULL,
    dataset_id bigint
);


ALTER TABLE npdc.zip OWNER TO npdc;

--
-- Name: zip_files; Type: TABLE; Schema: npdc; Owner: npdc
--

CREATE TABLE npdc.zip_files (
    zip_files_id bigint NOT NULL,
    zip_id bigint NOT NULL,
    file_id bigint NOT NULL
);


ALTER TABLE npdc.zip_files OWNER TO npdc;

--
-- Name: zip_files_zip_files_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.zip_files_zip_files_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.zip_files_zip_files_id_seq OWNER TO npdc;

--
-- Name: zip_files_zip_files_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.zip_files_zip_files_id_seq OWNED BY npdc.zip_files.zip_files_id;


--
-- Name: zip_zip_id_seq; Type: SEQUENCE; Schema: npdc; Owner: npdc
--

CREATE SEQUENCE npdc.zip_zip_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE npdc.zip_zip_id_seq OWNER TO npdc;

--
-- Name: zip_zip_id_seq; Type: SEQUENCE OWNED BY; Schema: npdc; Owner: npdc
--

ALTER SEQUENCE npdc.zip_zip_id_seq OWNED BY npdc.zip.zip_id;


--
-- Name: access_request access_request_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.access_request ALTER COLUMN access_request_id SET DEFAULT nextval('npdc.access_request_access_request_id_seq'::regclass);


--
-- Name: access_request_file access_request_file_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.access_request_file ALTER COLUMN access_request_file_id SET DEFAULT nextval('npdc.access_request_file_access_request_file_id_seq'::regclass);


--
-- Name: account_new account_new_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.account_new ALTER COLUMN account_new_id SET DEFAULT nextval('npdc.account_new_account_new_id_seq'::regclass);


--
-- Name: account_reset account_reset_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.account_reset ALTER COLUMN account_reset_id SET DEFAULT nextval('npdc.account_reset_account_reset_id_seq'::regclass);


--
-- Name: additional_attributes additional_attributes_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.additional_attributes ALTER COLUMN additional_attributes_id SET DEFAULT nextval('npdc.additional_attributes_additional_attributes_id_seq'::regclass);


--
-- Name: characteristics characteristics_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.characteristics ALTER COLUMN characteristics_id SET DEFAULT nextval('npdc.characteristics_characteristics_id_seq'::regclass);


--
-- Name: contact contact_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.contact ALTER COLUMN contact_id SET DEFAULT nextval('npdc.contact_contact_id_seq'::regclass);


--
-- Name: data_resolution data_resolution_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.data_resolution ALTER COLUMN data_resolution_id SET DEFAULT nextval('npdc.data_resolution_data_resolution_id_seq'::regclass);


--
-- Name: dataset dataset_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset ALTER COLUMN dataset_id SET DEFAULT nextval('npdc.dataset_dataset_id_seq'::regclass);


--
-- Name: dataset_ancillary_keyword dataset_ancillary_keyword_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_ancillary_keyword ALTER COLUMN dataset_ancillary_keyword_id SET DEFAULT nextval('npdc.dataset_ancillary_keyword_dataset_ancillary_keyword_id_seq'::regclass);


--
-- Name: dataset_citation dataset_citation_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_citation ALTER COLUMN dataset_citation_id SET DEFAULT nextval('npdc.dataset_citation_dataset_citation_id_seq'::regclass);


--
-- Name: dataset_data_center dataset_data_center_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_data_center ALTER COLUMN dataset_data_center_id SET DEFAULT nextval('npdc.dataset_data_center_dataset_data_center_id_seq'::regclass);


--
-- Name: dataset_data_center_person dataset_data_center_person_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_data_center_person ALTER COLUMN dataset_data_center_person_id SET DEFAULT nextval('npdc.dataset_data_center_person_dataset_data_center_person_id_seq'::regclass);


--
-- Name: dataset_keyword dataset_keyword_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_keyword ALTER COLUMN dataset_keyword_id SET DEFAULT nextval('npdc.dataset_keyword_dataset_keyword_id_seq'::regclass);


--
-- Name: dataset_link dataset_link_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_link ALTER COLUMN dataset_link_id SET DEFAULT nextval('npdc.dataset_link_dataset_link_id_seq'::regclass);


--
-- Name: dataset_link_url dataset_link_url_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_link_url ALTER COLUMN dataset_link_url_id SET DEFAULT nextval('npdc.dataset_link_url_dataset_link_url_id_seq'::regclass);


--
-- Name: distribution distribution_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.distribution ALTER COLUMN distribution_id SET DEFAULT nextval('npdc.distribution_distribution_id_seq'::regclass);


--
-- Name: file file_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.file ALTER COLUMN file_id SET DEFAULT nextval('npdc.file_file_id_seq'::regclass);


--
-- Name: instrument instrument_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.instrument ALTER COLUMN instrument_id SET DEFAULT nextval('npdc.instrument_instrument_id_seq'::regclass);


--
-- Name: location location_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.location ALTER COLUMN location_id SET DEFAULT nextval('npdc.location_location_id_seq'::regclass);


--
-- Name: menu menu_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.menu ALTER COLUMN menu_id SET DEFAULT nextval('npdc.menu_menu_id_seq'::regclass);


--
-- Name: metadata_association metadata_association_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.metadata_association ALTER COLUMN metadata_association_id SET DEFAULT nextval('npdc.metadata_association_metadata_association_id_seq'::regclass);


--
-- Name: mime_type mime_type_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.mime_type ALTER COLUMN mime_type_id SET DEFAULT nextval('npdc.mime_type_mime_type_id_seq'::regclass);


--
-- Name: multimedia_sample multimedia_sample_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.multimedia_sample ALTER COLUMN multimedia_sample_id SET DEFAULT nextval('npdc.multimedia_sample_multimedia_sample_id_seq'::regclass);


--
-- Name: news news_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.news ALTER COLUMN news_id SET DEFAULT nextval('npdc.news_news_id_seq'::regclass);


--
-- Name: npp_theme npp_theme_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.npp_theme ALTER COLUMN npp_theme_id SET DEFAULT nextval('npdc.npp_theme_npp_theme_id_seq'::regclass);


--
-- Name: organization organization_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.organization ALTER COLUMN organization_id SET DEFAULT nextval('npdc.organization_organization_id_seq'::regclass);


--
-- Name: page page_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.page ALTER COLUMN page_id SET DEFAULT nextval('npdc.page_page_id_seq'::regclass);


--
-- Name: page_link page_link_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.page_link ALTER COLUMN page_link_id SET DEFAULT nextval('npdc.page_link_page_link_id_seq'::regclass);


--
-- Name: person person_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.person ALTER COLUMN person_id SET DEFAULT nextval('npdc.person_person_id_seq'::regclass);


--
-- Name: platform platform_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.platform ALTER COLUMN platform_id SET DEFAULT nextval('npdc.platform_platform_id_seq'::regclass);


--
-- Name: program program_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.program ALTER COLUMN program_id SET DEFAULT nextval('npdc.program_program_id_seq'::regclass);


--
-- Name: project project_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project ALTER COLUMN project_id SET DEFAULT nextval('npdc.project_project_id_seq'::regclass);


--
-- Name: project_keyword project_keyword_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project_keyword ALTER COLUMN project_keyword_id SET DEFAULT nextval('npdc.project_keyword_project_keyword_id_seq'::regclass);


--
-- Name: project_link project_link_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project_link ALTER COLUMN project_link_id SET DEFAULT nextval('npdc.project_link_project_link_id_seq'::regclass);


--
-- Name: publication publication_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication ALTER COLUMN publication_id SET DEFAULT nextval('npdc.publication_publication_id_seq'::regclass);


--
-- Name: publication_keyword publication_keyword_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication_keyword ALTER COLUMN publication_keyword_id SET DEFAULT nextval('npdc.publication_keyword_publication_keyword_id_seq'::regclass);


--
-- Name: publication_person publication_person_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication_person ALTER COLUMN publication_person_id SET DEFAULT nextval('npdc.publication_person_publication_person_id_seq'::regclass);


--
-- Name: publication_type publication_type_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication_type ALTER COLUMN publication_type_id SET DEFAULT nextval('npdc.publication_type_publication_type_id_seq'::regclass);


--
-- Name: record_status_change record_status_change_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.record_status_change ALTER COLUMN record_status_change_id SET DEFAULT nextval('npdc.record_status_change_record_status_change_id_seq'::regclass);


--
-- Name: related_dataset related_dataset_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.related_dataset ALTER COLUMN related_dataset_id SET DEFAULT nextval('npdc.related_dataset_related_dataset_id_seq'::regclass);


--
-- Name: sensor sensor_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.sensor ALTER COLUMN sensor_id SET DEFAULT nextval('npdc.sensor_sensor_id_seq'::regclass);


--
-- Name: spatial_coverage spatial_coverage_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.spatial_coverage ALTER COLUMN spatial_coverage_id SET DEFAULT nextval('npdc.spatial_coverage_spatial_coverage_id_seq'::regclass);


--
-- Name: suggestion suggestion_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.suggestion ALTER COLUMN suggestion_id SET DEFAULT nextval('npdc.suggestion_suggestion_id_seq'::regclass);


--
-- Name: temporal_coverage temporal_coverage_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage ALTER COLUMN temporal_coverage_id SET DEFAULT nextval('npdc.temporal_coverage_temporal_coverage_id_seq'::regclass);


--
-- Name: temporal_coverage_ancillary temporal_coverage_ancillary_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_ancillary ALTER COLUMN temporal_coverage_ancillary_id SET DEFAULT nextval('npdc.temporal_coverage_ancillary_temporal_coverage_ancillary_id_seq'::regclass);


--
-- Name: temporal_coverage_cycle temporal_coverage_cycle_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_cycle ALTER COLUMN temporal_coverage_cycle_id SET DEFAULT nextval('npdc.temporal_coverage_cycle_temporal_coverage_cycle_id_seq'::regclass);


--
-- Name: temporal_coverage_paleo temporal_coverage_paleo_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_paleo ALTER COLUMN temporal_coverage_paleo_id SET DEFAULT nextval('npdc.temporal_coverage_paleo_temporal_coverage_paleo_id_seq'::regclass);


--
-- Name: temporal_coverage_paleo_chronounit temporal_coverage_paleo_chronounit_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_paleo_chronounit ALTER COLUMN temporal_coverage_paleo_chronounit_id SET DEFAULT nextval('npdc.temporal_coverage_paleo_chron_temporal_coverage_paleo_chron_seq'::regclass);


--
-- Name: temporal_coverage_period temporal_coverage_period_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_period ALTER COLUMN temporal_coverage_period_id SET DEFAULT nextval('npdc.temporal_coverage_period_temporal_coverage_period_id_seq'::regclass);


--
-- Name: user_level user_level_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.user_level ALTER COLUMN user_level_id SET DEFAULT nextval('npdc.user_level_user_level_id_seq'::regclass);


--
-- Name: vocab_chronounit vocab_chronounit_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_chronounit ALTER COLUMN vocab_chronounit_id SET DEFAULT nextval('npdc.vocab_chronounit_vocab_chronounit_id_seq'::regclass);


--
-- Name: vocab_idn_node vocab_idn_node_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_idn_node ALTER COLUMN vocab_idn_node_id SET DEFAULT nextval('npdc.vocab_idn_node_vocab_idn_node_id_seq'::regclass);


--
-- Name: vocab_instrument vocab_instrument_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_instrument ALTER COLUMN vocab_instrument_id SET DEFAULT nextval('npdc.vocab_instrument_vocab_instrument_id_seq'::regclass);


--
-- Name: vocab_iso_topic_category vocab_iso_topic_category_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_iso_topic_category ALTER COLUMN vocab_iso_topic_category_id SET DEFAULT nextval('npdc.vocab_iso_topic_category_vocab_iso_topic_category_id_seq'::regclass);


--
-- Name: vocab_location vocab_location_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_location ALTER COLUMN vocab_location_id SET DEFAULT nextval('npdc.vocab_location_vocab_location_id_seq'::regclass);


--
-- Name: vocab_platform vocab_platform_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_platform ALTER COLUMN vocab_platform_id SET DEFAULT nextval('npdc.vocab_platform_vocab_platform_id_seq'::regclass);


--
-- Name: vocab_res_hor vocab_res_hor_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_res_hor ALTER COLUMN vocab_res_hor_id SET DEFAULT nextval('npdc.vocab_res_hor_vocab_res_hor_id_seq'::regclass);


--
-- Name: vocab_res_time vocab_res_time_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_res_time ALTER COLUMN vocab_res_time_id SET DEFAULT nextval('npdc.vocab_res_time_vocab_res_time_id_seq'::regclass);


--
-- Name: vocab_res_vert vocab_res_vert_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_res_vert ALTER COLUMN vocab_res_vert_id SET DEFAULT nextval('npdc.vocab_res_vert_vocab_res_vert_id_seq'::regclass);


--
-- Name: vocab_science_keyword vocab_science_keyword_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_science_keyword ALTER COLUMN vocab_science_keyword_id SET DEFAULT nextval('npdc.vocab_science_keyword_vocab_science_keyword_id_seq'::regclass);


--
-- Name: vocab_url_type vocab_url_type_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_url_type ALTER COLUMN vocab_url_type_id SET DEFAULT nextval('npdc.vocab_url_type_vocab_url_type_id_seq'::regclass);


--
-- Name: zip zip_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.zip ALTER COLUMN zip_id SET DEFAULT nextval('npdc.zip_zip_id_seq'::regclass);


--
-- Name: zip_files zip_files_id; Type: DEFAULT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.zip_files ALTER COLUMN zip_files_id SET DEFAULT nextval('npdc.zip_files_zip_files_id_seq'::regclass);


--
-- Data for Name: access_request; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.access_request (access_request_id, person_id, reason, request_timestamp, permitted, response, response_timestamp, dataset_id, zip_id, responder_id) FROM stdin;
\.


--
-- Data for Name: access_request_file; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.access_request_file (access_request_file_id, access_request_id, file_id, permitted) FROM stdin;
\.


--
-- Data for Name: account_new; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.account_new (account_new_id, code, request_time, used_time, expire_reason, mail) FROM stdin;
\.


--
-- Data for Name: account_reset; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.account_reset (account_reset_id, person_id, code, request_time, used_time, expire_reason) FROM stdin;
\.


--
-- Data for Name: additional_attributes; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.additional_attributes (additional_attributes_id, dataset_id, name, datatype, description, measurement_resolution, parameter_range_begin, parameter_range_end, parameter_units_of_measure, parameter_value_accuracy, value_accuracy_explanation, value, dataset_version_min) FROM stdin;
\.


--
-- Data for Name: characteristics; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.characteristics (characteristics_id, name, description, unit, value, platform_id, instrument_id, sensor_id, data_type, dataset_version_min, dataset_version_max) FROM stdin;
\.


--
-- Data for Name: contact; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.contact (contact_id, receiver, sender_mail, sender_name, subject, text, country, ip, browser, "timestamp") FROM stdin;
\.


--
-- Data for Name: continent; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.continent (continent_id, continent_name) FROM stdin;
AF	Africa
AN	Antarctica
AS	Asia
EU	Europe
NA	North America
OC	Oceania
SA	South America
\.


--
-- Data for Name: country; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.country (country_id, country_name, continent_id) FROM stdin;
AD	Andorra	EU
AE	United Arab Emirates	AS
AF	Afghanistan	AS
AG	Antigua and Barbuda	NA
AI	Anguilla	NA
AL	Albania	EU
AM	Armenia	AS
AN	Netherlands Antilles	NA
AO	Angola	AF
AQ	Antarctica	AN
AR	Argentina	SA
AS	American Samoa	OC
AT	Austria	EU
AU	Australia	OC
AW	Aruba	NA
AX	Aland Islands	EU
AZ	Azerbaijan	AS
BA	Bosnia and Herzegovina	EU
BB	Barbados	NA
BD	Bangladesh	AS
BE	Belgium	EU
BF	Burkina Faso	AF
BG	Bulgaria	EU
BH	Bahrain	AS
BI	Burundi	AF
BJ	Benin	AF
BL	Saint Barthelemy	NA
BM	Bermuda	NA
BN	Brunei	AS
BO	Bolivia	SA
BQ	Bonaire, Saint Eustatius and Saba 	NA
BR	Brazil	SA
BS	Bahamas	NA
BT	Bhutan	AS
BV	Bouvet Island	AN
BW	Botswana	AF
BY	Belarus	EU
BZ	Belize	NA
CA	Canada	NA
CC	Cocos Islands	AS
CD	Democratic Republic of the Congo	AF
CF	Central African Republic	AF
CG	Republic of the Congo	AF
CH	Switzerland	EU
CI	Ivory Coast	AF
CK	Cook Islands	OC
CL	Chile	SA
CM	Cameroon	AF
CN	China	AS
CO	Colombia	SA
CR	Costa Rica	NA
CS	Serbia and Montenegro	EU
CU	Cuba	NA
CV	Cape Verde	AF
CW	Curacao	NA
CX	Christmas Island	AS
CY	Cyprus	EU
CZ	Czech Republic	EU
DE	Germany	EU
DJ	Djibouti	AF
DK	Denmark	EU
DM	Dominica	NA
DO	Dominican Republic	NA
DZ	Algeria	AF
EC	Ecuador	SA
EE	Estonia	EU
EG	Egypt	AF
EH	Western Sahara	AF
ER	Eritrea	AF
ES	Spain	EU
ET	Ethiopia	AF
FI	Finland	EU
FJ	Fiji	OC
FK	Falkland Islands	SA
FM	Micronesia	OC
FO	Faroe Islands	EU
FR	France	EU
GA	Gabon	AF
GB	United Kingdom	EU
GD	Grenada	NA
GE	Georgia	AS
GF	French Guiana	SA
GG	Guernsey	EU
GH	Ghana	AF
GI	Gibraltar	EU
GL	Greenland	NA
GM	Gambia	AF
GN	Guinea	AF
GP	Guadeloupe	NA
GQ	Equatorial Guinea	AF
GR	Greece	EU
GS	South Georgia and the South Sandwich Islands	AN
GT	Guatemala	NA
GU	Guam	OC
GW	Guinea-Bissau	AF
GY	Guyana	SA
HK	Hong Kong	AS
HM	Heard Island and McDonald Islands	AN
HN	Honduras	NA
HR	Croatia	EU
HT	Haiti	NA
HU	Hungary	EU
ID	Indonesia	AS
IE	Ireland	EU
IL	Israel	AS
IM	Isle of Man	EU
IN	India	AS
IO	British Indian Ocean Territory	AS
IQ	Iraq	AS
IR	Iran	AS
IS	Iceland	EU
IT	Italy	EU
JE	Jersey	EU
JM	Jamaica	NA
JO	Jordan	AS
JP	Japan	AS
KE	Kenya	AF
KG	Kyrgyzstan	AS
KH	Cambodia	AS
KI	Kiribati	OC
KM	Comoros	AF
KN	Saint Kitts and Nevis	NA
KP	North Korea	AS
KR	South Korea	AS
KW	Kuwait	AS
KY	Cayman Islands	NA
KZ	Kazakhstan	AS
LA	Laos	AS
LB	Lebanon	AS
LC	Saint Lucia	NA
LI	Liechtenstein	EU
LK	Sri Lanka	AS
LR	Liberia	AF
LS	Lesotho	AF
LT	Lithuania	EU
LU	Luxembourg	EU
LV	Latvia	EU
LY	Libya	AF
MA	Morocco	AF
MC	Monaco	EU
MD	Moldova	EU
ME	Montenegro	EU
MF	Saint Martin	NA
MG	Madagascar	AF
MH	Marshall Islands	OC
MK	Macedonia	EU
ML	Mali	AF
MM	Myanmar	AS
MN	Mongolia	AS
MO	Macao	AS
MP	Northern Mariana Islands	OC
MQ	Martinique	NA
MR	Mauritania	AF
MS	Montserrat	NA
MT	Malta	EU
MU	Mauritius	AF
MV	Maldives	AS
MW	Malawi	AF
MX	Mexico	NA
MY	Malaysia	AS
MZ	Mozambique	AF
NA	Namibia	AF
NC	New Caledonia	OC
NE	Niger	AF
NF	Norfolk Island	OC
NG	Nigeria	AF
NI	Nicaragua	NA
NL	Netherlands	EU
NO	Norway	EU
NP	Nepal	AS
NR	Nauru	OC
NU	Niue	OC
NZ	New Zealand	OC
OM	Oman	AS
PA	Panama	NA
PE	Peru	SA
PF	French Polynesia	OC
PG	Papua New Guinea	OC
PH	Philippines	AS
PK	Pakistan	AS
PL	Poland	EU
PM	Saint Pierre and Miquelon	NA
PN	Pitcairn	OC
PR	Puerto Rico	NA
PS	Palestinian Territory	AS
PT	Portugal	EU
PW	Palau	OC
PY	Paraguay	SA
QA	Qatar	AS
RE	Reunion	AF
RO	Romania	EU
RS	Serbia	EU
RU	Russia	EU
RW	Rwanda	AF
SA	Saudi Arabia	AS
SB	Solomon Islands	OC
SC	Seychelles	AF
SD	Sudan	AF
SE	Sweden	EU
SG	Singapore	AS
SH	Saint Helena	AF
SI	Slovenia	EU
SJ	Svalbard and Jan Mayen	EU
SK	Slovakia	EU
SL	Sierra Leone	AF
SM	San Marino	EU
SN	Senegal	AF
SO	Somalia	AF
SR	Suriname	SA
SS	South Sudan	AF
ST	Sao Tome and Principe	AF
SV	El Salvador	NA
SX	Sint Maarten	NA
SY	Syria	AS
SZ	Swaziland	AF
TC	Turks and Caicos Islands	NA
TD	Chad	AF
TF	French Southern Territories	AN
TG	Togo	AF
TH	Thailand	AS
TJ	Tajikistan	AS
TK	Tokelau	OC
TL	East Timor	OC
TM	Turkmenistan	AS
TN	Tunisia	AF
TO	Tonga	OC
TR	Turkey	AS
TT	Trinidad and Tobago	NA
TV	Tuvalu	OC
TW	Taiwan	AS
TZ	Tanzania	AF
UA	Ukraine	EU
UG	Uganda	AF
UM	United States Minor Outlying Islands	OC
US	United States	NA
UY	Uruguay	SA
UZ	Uzbekistan	AS
VA	Vatican	EU
VC	Saint Vincent and the Grenadines	NA
VE	Venezuela	SA
VG	British Virgin Islands	NA
VI	U.S. Virgin Islands	NA
VN	Vietnam	AS
VU	Vanuatu	OC
WF	Wallis and Futuna	OC
WS	Samoa	OC
XK	Kosovo	EU
YE	Yemen	AS
YT	Mayotte	AF
ZA	South Africa	AF
ZM	Zambia	AF
ZW	Zimbabwe	AF
\.


--
-- Data for Name: data_center_person_default; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.data_center_person_default (organization_id, person_id) FROM stdin;
\.


--
-- Data for Name: data_resolution; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.data_resolution (data_resolution_id, dataset_id, latitude_resolution, longitude_resolution, vocab_res_hor_id, vertical_resolution, vocab_res_vert_id, temporal_resolution, vocab_res_time_id, dataset_version_min, dataset_version_max) FROM stdin;
\.


--
-- Data for Name: dataset; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.dataset (dataset_id, dataset_version, dif_id, published, title, summary, region, date_start, date_end, quality, access_constraints, use_constraints, dataset_progress, originating_center, dif_revision_history, version_description, product_level_id, collection_data_type, extended_metadata, record_status, purpose, insert_timestamp, creator, ipy, uuid, created_from, license) FROM stdin;
\.


--
-- Data for Name: dataset_ancillary_keyword; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.dataset_ancillary_keyword (dataset_ancillary_keyword_id, dataset_id, dataset_version_min, dataset_version_max, keyword) FROM stdin;
\.


--
-- Data for Name: dataset_citation; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.dataset_citation (dataset_citation_id, dataset_id, creator, editor, title, series_name, release_date, release_place, publisher, version, issue_identification, presentation_form, other, persistent_identifier_type, persistent_identifier_identifier, online_resource, dataset_version_min, dataset_version_max, type) FROM stdin;
\.


--
-- Data for Name: dataset_data_center; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.dataset_data_center (dataset_data_center_id, dataset_id, dataset_version_min, dataset_version_max, organization_id) FROM stdin;
\.


--
-- Data for Name: dataset_data_center_person; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.dataset_data_center_person (dataset_data_center_person_id, dataset_data_center_id, dataset_version_min, dataset_version_max, person_id) FROM stdin;
\.


--
-- Data for Name: dataset_file; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.dataset_file (dataset_id, dataset_version_min, dataset_version_max, file_id) FROM stdin;
\.


--
-- Data for Name: dataset_keyword; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.dataset_keyword (dataset_keyword_id, dataset_id, vocab_science_keyword_id, free_text, dataset_version_min, dataset_version_max) FROM stdin;
\.


--
-- Data for Name: dataset_link; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.dataset_link (dataset_link_id, dataset_id, title, vocab_url_type_id, dataset_version_min, description, mime_type_id, protocol, dataset_version_max) FROM stdin;
\.


--
-- Data for Name: dataset_link_url; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.dataset_link_url (dataset_link_url_id, dataset_link_id, dataset_version_min, dataset_version_max, url, old_dataset_link_url_id) FROM stdin;
\.


--
-- Data for Name: dataset_person; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.dataset_person (dataset_id, dataset_version_min, person_id, organization_id, editor, sort, dataset_version_max, role) FROM stdin;
\.


--
-- Data for Name: dataset_project; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.dataset_project (dataset_id, dataset_version_min, project_version_min, dataset_version_max, project_version_max, project_id) FROM stdin;
\.


--
-- Data for Name: dataset_publication; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.dataset_publication (publication_id, publication_version_min, dataset_id, dataset_version_min, publication_version_max, dataset_version_max) FROM stdin;
\.


--
-- Data for Name: dataset_topic; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.dataset_topic (vocab_iso_topic_category_id, dataset_id, dataset_version_min, dataset_version_max) FROM stdin;
\.


--
-- Data for Name: distribution; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.distribution (distribution_id, dataset_id, media, size, format, fees, dataset_version_min) FROM stdin;
\.


--
-- Data for Name: file; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.file (file_id, name, location, type, size, default_access, description, insert_timestamp, record_state, title, form_id) FROM stdin;
\.


--
-- Data for Name: instrument; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.instrument (instrument_id, platform_id, vocab_instrument_id, number_of_sensors, operational_mode, technique, dataset_version_min, dataset_version_max, old_instrument_id) FROM stdin;
\.


--
-- Data for Name: location; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.location (location_id, vocab_location_id, detailed, dataset_id, dataset_version_min, dataset_version_max) FROM stdin;
\.


--
-- Data for Name: menu; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.menu (menu_id, label, url, parent_menu_id, sort, min_user_level) FROM stdin;
3	Home		\N	1	public
4	Data	\N	\N	2	public
5	Info	\N	\N	3	public
6	Tips	tips	\N	4	public
7	Contact	contact	\N	5	public
8	Datasets	dataset	4	1	public
9	Publications	publication	4	2	public
10	Data portals	portals	4	3	public
11	Projects	project	4	4	public
12	NPP	npp	5	1	public
13	NPDC	npdc	5	2	public
14	Admin	\N	\N	7	admin
15	Organizations	organization	14	1	admin
16	People	person	14	2	admin
17	Users	users	14	3	nobody
18	User	\N	\N	6	user
19	Data requests	request	18	2	user
20	Account settings	account	18	1	user
21	Editor tools	editor	18	3	editor
22	Organizations	organization	4	5	public
\.


--
-- Data for Name: metadata_association; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.metadata_association (metadata_association_id, dataset_id, entry_id, type, description, dataset_version_min) FROM stdin;
\.


--
-- Data for Name: mime_type; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.mime_type (mime_type_id, label, type, visible) FROM stdin;
\.


--
-- Data for Name: multimedia_sample; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.multimedia_sample (multimedia_sample_id, dataset_id, file, url, format, caption, description, dataset_version_min, dataset_version_max) FROM stdin;
\.


--
-- Data for Name: news; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.news (news_id, title, content, published, show_till, link) FROM stdin;
\.


--
-- Data for Name: npp_theme; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.npp_theme (npp_theme_id, theme_nl, theme_en) FROM stdin;
\.


--
-- Data for Name: organization; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.organization (organization_id, organization_name, organization_address, organization_zip, organization_city, visiting_address, edmo, dif_code, dif_name, website, country_id, uuid, historic_name) FROM stdin;
\.


--
-- Data for Name: page; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.page (page_id, title, content, url, last_update, show_last_revision) FROM stdin;
\.


--
-- Data for Name: page_link; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.page_link (page_link_id, page_id, url, text, sort) FROM stdin;
\.


--
-- Data for Name: page_person; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.page_person (page_id, person_id, role, editor, sort) FROM stdin;
\.


--
-- Data for Name: person; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.person (person_id, organization_id, name, titles, initials, given_name, surname, mail, phone_personal, phone_secretariat, phone_mobile, address, zip, city, sees_participant, language, password, user_level, orcid, phone_personal_public, phone_secretariat_public, phone_mobile_public, sex) FROM stdin;
\.


--
-- Data for Name: platform; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.platform (platform_id, dataset_id, vocab_platform_id, dataset_version_min, dataset_version_max) FROM stdin;
\.


--
-- Data for Name: program; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.program (program_id, name, program_start, program_end, sort) FROM stdin;
\.


--
-- Data for Name: project; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.project (project_id, project_version, nwo_project_id, title, acronym, region, summary, program_id, date_start, date_end, ris_id, proposal_status, data_status, research_type, science_field, data_type, comments, record_status, insert_timestamp, creator, published, uuid, npp_theme_id) FROM stdin;
\.


--
-- Data for Name: project_keyword; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.project_keyword (project_keyword_id, keyword, project_version_min, project_version_max, project_id) FROM stdin;
\.


--
-- Data for Name: project_link; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.project_link (project_link_id, url, text, project_version_min, project_version_max, project_id) FROM stdin;
\.


--
-- Data for Name: project_person; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.project_person (person_id, organization_id, project_version_min, project_version_max, role, sort, contact, editor, project_id) FROM stdin;
\.


--
-- Data for Name: project_project; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.project_project (parent_project_id, child_project_id) FROM stdin;
\.


--
-- Data for Name: project_publication; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.project_publication (publication_id, publication_version_min, project_version_min, publication_version_max, project_version_max, project_id) FROM stdin;
\.


--
-- Data for Name: publication; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.publication (publication_id, publication_version, title, abstract, journal, volume, issue, pages, isbn, doi, record_status, date, url, insert_timestamp, creator, published, uuid, publication_type_id) FROM stdin;
\.


--
-- Data for Name: publication_keyword; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.publication_keyword (publication_keyword_id, publication_id, keyword, publication_version_min, publication_version_max) FROM stdin;
\.


--
-- Data for Name: publication_person; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.publication_person (publication_person_id, publication_id, publication_version_min, person_id, organization_id, free_person, sort, contact, publication_version_max, editor, free_organization) FROM stdin;
\.


--
-- Data for Name: publication_type; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.publication_type (publication_type_id, label, bib, ris, description) FROM stdin;
\.


--
-- Data for Name: record_status; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.record_status (record_status, editable, visible) FROM stdin;
\.


--
-- Data for Name: record_status_change; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.record_status_change (project_id, dataset_id, publication_id, old_state, new_state, person_id, datetime, comment, version, record_status_change_id) FROM stdin;
\.


--
-- Data for Name: related_dataset; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.related_dataset (related_dataset_id, dataset_id, dataset_version_min, dataset_version_max, url, doi, internal_related_dataset_id, relation, same) FROM stdin;
\.


--
-- Data for Name: sensor; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.sensor (sensor_id, instrument_id, dataset_version_min, dataset_version_max, vocab_instrument_id, technique, old_sensor_id) FROM stdin;
\.


--
-- Data for Name: spatial_coverage; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.spatial_coverage (spatial_coverage_id, dataset_id, dataset_version_min, dataset_version_max, wkt, depth_min, depth_max, depth_unit, altitude_min, altitude_max, altitude_unit, type, label) FROM stdin;
\.


--
-- Data for Name: suggestion; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.suggestion (suggestion_id, field, suggestion) FROM stdin;
\.


--
-- Data for Name: temporal_coverage; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.temporal_coverage (temporal_coverage_id, dataset_id, dataset_version_min, dataset_version_max) FROM stdin;
\.


--
-- Data for Name: temporal_coverage_ancillary; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.temporal_coverage_ancillary (temporal_coverage_ancillary_id, temporal_coverage_id, dataset_version_min, dataset_version_max, keyword) FROM stdin;
\.


--
-- Data for Name: temporal_coverage_cycle; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.temporal_coverage_cycle (temporal_coverage_cycle_id, temporal_coverage_id, dataset_version_min, dataset_version_max, name, date_start, date_end, sampling_frequency, sampling_frequency_unit) FROM stdin;
\.


--
-- Data for Name: temporal_coverage_paleo; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.temporal_coverage_paleo (temporal_coverage_paleo_id, temporal_coverage_id, dataset_version_min, dataset_version_max, start_value, start_unit, end_value, end_unit) FROM stdin;
\.


--
-- Data for Name: temporal_coverage_paleo_chronounit; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.temporal_coverage_paleo_chronounit (temporal_coverage_paleo_chronounit_id, temporal_coverage_paleo_id, dataset_version_min, dataset_version_max, vocab_chronounit_id) FROM stdin;
\.


--
-- Data for Name: temporal_coverage_period; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.temporal_coverage_period (temporal_coverage_period_id, temporal_coverage_id, dataset_version_min, dataset_version_max, date_start, date_end) FROM stdin;
\.


--
-- Data for Name: user_level; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.user_level (user_level_id, label, description, name) FROM stdin;
0	public	\N	Guest
1	user	- You can download files which are available to logged in users\r\n- You can request access to restricted files	Logged in user
2	editor	- You can add new projects, publications and datasets\r\n- You can edit projects, publications and datasets for which you have been given edit rights (either by creating them or when someone else granted you those rights)	Editor
3	admin	- You can edit all content	Administrator
4	nobody	\N	Unrestricted access
\.


--
-- Data for Name: vocab; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.vocab (vocab_id, vocab_name, last_update_date, last_update_local, sync) FROM stdin;
\.


--
-- Data for Name: vocab_chronounit; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.vocab_chronounit (vocab_chronounit_id, eon, era, period, epoch, stage, uuid, visible, sort) FROM stdin;
\.


--
-- Data for Name: vocab_idn_node; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.vocab_idn_node (vocab_idn_node_id, short_name, long_name, uuid, visible) FROM stdin;
\.


--
-- Data for Name: vocab_instrument; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.vocab_instrument (vocab_instrument_id, category, class, type, subtype, short_name, long_name, uuid, visible) FROM stdin;
\.


--
-- Data for Name: vocab_iso_topic_category; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.vocab_iso_topic_category (vocab_iso_topic_category_id, topic, description, visible) FROM stdin;
\.


--
-- Data for Name: vocab_location; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.vocab_location (vocab_location_id, location_category, location_type, location_subregion1, location_subregion2, location_subregion3, uuid, visible) FROM stdin;
\.


--
-- Data for Name: vocab_location_vocab_idn_node; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.vocab_location_vocab_idn_node (vocab_location_id, vocab_idn_node_id) FROM stdin;
\.


--
-- Data for Name: vocab_organization; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.vocab_organization (lvl0, lvl1, lvl2, lvl3, short_name, long_name, url, uuid) FROM stdin;
\.


--
-- Data for Name: vocab_platform; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.vocab_platform (vocab_platform_id, category, series_entity, short_name, long_name, uuid, visible) FROM stdin;
\.


--
-- Data for Name: vocab_res_hor; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.vocab_res_hor (vocab_res_hor_id, range, uuid, sort, visible) FROM stdin;
\.


--
-- Data for Name: vocab_res_time; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.vocab_res_time (vocab_res_time_id, range, uuid, sort, visible) FROM stdin;
\.


--
-- Data for Name: vocab_res_vert; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.vocab_res_vert (vocab_res_vert_id, range, uuid, sort, visible) FROM stdin;
\.


--
-- Data for Name: vocab_science_keyword; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.vocab_science_keyword (vocab_science_keyword_id, category, topic, term, var_lvl_1, var_lvl_2, var_lvl_3, uuid, detailed_variable, visible) FROM stdin;
\.


--
-- Data for Name: vocab_url_type; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.vocab_url_type (vocab_url_type_id, type, subtype, uuid, visible) FROM stdin;
\.


--
-- Data for Name: zip; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.zip (zip_id, filename, person_id, guest_user, "timestamp", dataset_id) FROM stdin;
\.


--
-- Data for Name: zip_files; Type: TABLE DATA; Schema: npdc; Owner: npdc
--

COPY npdc.zip_files (zip_files_id, zip_id, file_id) FROM stdin;
\.


--
-- Name: access_request_access_request_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.access_request_access_request_id_seq', 1, true);


--
-- Name: access_request_file_access_request_file_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.access_request_file_access_request_file_id_seq', 1, true);


--
-- Name: account_new_account_new_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.account_new_account_new_id_seq', 1, true);


--
-- Name: account_reset_account_reset_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.account_reset_account_reset_id_seq', 1, true);


--
-- Name: additional_attributes_additional_attributes_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.additional_attributes_additional_attributes_id_seq', 1, true);


--
-- Name: characteristics_characteristics_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.characteristics_characteristics_id_seq', 1, true);


--
-- Name: contact_contact_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.contact_contact_id_seq', 1, true);


--
-- Name: data_resolution_data_resolution_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.data_resolution_data_resolution_id_seq', 1, true);


--
-- Name: dataset_ancillary_keyword_dataset_ancillary_keyword_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.dataset_ancillary_keyword_dataset_ancillary_keyword_id_seq', 1, true);


--
-- Name: dataset_citation_dataset_citation_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.dataset_citation_dataset_citation_id_seq', 1, true);


--
-- Name: dataset_data_center_dataset_data_center_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.dataset_data_center_dataset_data_center_id_seq', 1, true);


--
-- Name: dataset_data_center_person_dataset_data_center_person_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.dataset_data_center_person_dataset_data_center_person_id_seq', 1, true);


--
-- Name: dataset_dataset_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.dataset_dataset_id_seq', 1, true);


--
-- Name: dataset_keyword_dataset_keyword_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.dataset_keyword_dataset_keyword_id_seq', 1, true);


--
-- Name: dataset_link_dataset_link_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.dataset_link_dataset_link_id_seq', 1, true);


--
-- Name: dataset_link_url_dataset_link_url_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.dataset_link_url_dataset_link_url_id_seq', 1, true);


--
-- Name: distribution_distribution_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.distribution_distribution_id_seq', 1, true);


--
-- Name: file_file_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.file_file_id_seq', 1, true);


--
-- Name: instrument_instrument_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.instrument_instrument_id_seq', 1, true);


--
-- Name: location_location_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.location_location_id_seq', 1, true);


--
-- Name: menu_menu_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.menu_menu_id_seq', 22, true);


--
-- Name: metadata_association_metadata_association_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.metadata_association_metadata_association_id_seq', 1, true);


--
-- Name: mime_type_mime_type_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.mime_type_mime_type_id_seq', 1, true);


--
-- Name: multimedia_sample_multimedia_sample_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.multimedia_sample_multimedia_sample_id_seq', 1, true);


--
-- Name: news_news_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.news_news_id_seq', 1, true);


--
-- Name: npp_theme_npp_theme_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.npp_theme_npp_theme_id_seq', 1, true);


--
-- Name: organization_organization_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.organization_organization_id_seq', 1, true);


--
-- Name: page_link_page_link_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.page_link_page_link_id_seq', 1, true);


--
-- Name: page_page_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.page_page_id_seq', 1, true);


--
-- Name: person_person_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.person_person_id_seq', 1, true);


--
-- Name: platform_platform_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.platform_platform_id_seq', 1, true);


--
-- Name: program_program_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.program_program_id_seq', 1, true);


--
-- Name: project_keyword_project_keyword_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.project_keyword_project_keyword_id_seq', 1, true);


--
-- Name: project_link_project_link_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.project_link_project_link_id_seq', 1, true);


--
-- Name: project_project_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.project_project_id_seq', 1, true);


--
-- Name: publication_keyword_publication_keyword_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.publication_keyword_publication_keyword_id_seq', 1, true);


--
-- Name: publication_person_publication_person_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.publication_person_publication_person_id_seq', 1, true);


--
-- Name: publication_publication_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.publication_publication_id_seq', 1, true);


--
-- Name: publication_type_publication_type_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.publication_type_publication_type_id_seq', 1, true);


--
-- Name: record_status_change_record_status_change_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.record_status_change_record_status_change_id_seq', 1, true);


--
-- Name: related_dataset_related_dataset_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.related_dataset_related_dataset_id_seq', 1, true);


--
-- Name: sensor_sensor_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.sensor_sensor_id_seq', 1, true);


--
-- Name: spatial_coverage_spatial_coverage_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.spatial_coverage_spatial_coverage_id_seq', 1, true);


--
-- Name: suggestion_suggestion_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.suggestion_suggestion_id_seq', 1, true);


--
-- Name: temporal_coverage_ancillary_temporal_coverage_ancillary_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.temporal_coverage_ancillary_temporal_coverage_ancillary_id_seq', 1, true);


--
-- Name: temporal_coverage_cycle_temporal_coverage_cycle_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.temporal_coverage_cycle_temporal_coverage_cycle_id_seq', 1, true);


--
-- Name: temporal_coverage_paleo_chron_temporal_coverage_paleo_chron_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.temporal_coverage_paleo_chron_temporal_coverage_paleo_chron_seq', 1, true);


--
-- Name: temporal_coverage_paleo_temporal_coverage_paleo_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.temporal_coverage_paleo_temporal_coverage_paleo_id_seq', 1, true);


--
-- Name: temporal_coverage_period_temporal_coverage_period_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.temporal_coverage_period_temporal_coverage_period_id_seq', 1, true);


--
-- Name: temporal_coverage_temporal_coverage_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.temporal_coverage_temporal_coverage_id_seq', 1, true);


--
-- Name: user_level_user_level_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.user_level_user_level_id_seq', 4, true);


--
-- Name: vocab_chronounit_vocab_chronounit_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.vocab_chronounit_vocab_chronounit_id_seq', 1, true);


--
-- Name: vocab_idn_node_vocab_idn_node_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.vocab_idn_node_vocab_idn_node_id_seq', 1, true);


--
-- Name: vocab_instrument_vocab_instrument_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.vocab_instrument_vocab_instrument_id_seq', 1, true);


--
-- Name: vocab_iso_topic_category_vocab_iso_topic_category_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.vocab_iso_topic_category_vocab_iso_topic_category_id_seq', 1, true);


--
-- Name: vocab_location_vocab_location_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.vocab_location_vocab_location_id_seq', 1, true);


--
-- Name: vocab_platform_vocab_platform_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.vocab_platform_vocab_platform_id_seq', 1, true);


--
-- Name: vocab_res_hor_vocab_res_hor_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.vocab_res_hor_vocab_res_hor_id_seq', 1, true);


--
-- Name: vocab_res_time_vocab_res_time_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.vocab_res_time_vocab_res_time_id_seq', 1, true);


--
-- Name: vocab_res_vert_vocab_res_vert_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.vocab_res_vert_vocab_res_vert_id_seq', 1, true);


--
-- Name: vocab_science_keyword_vocab_science_keyword_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.vocab_science_keyword_vocab_science_keyword_id_seq', 1, true);


--
-- Name: vocab_url_type_vocab_url_type_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.vocab_url_type_vocab_url_type_id_seq', 1, true);


--
-- Name: zip_files_zip_files_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.zip_files_zip_files_id_seq', 1, true);


--
-- Name: zip_zip_id_seq; Type: SEQUENCE SET; Schema: npdc; Owner: npdc
--

SELECT pg_catalog.setval('npdc.zip_zip_id_seq', 1, true);


--
-- Name: access_request idx_16434_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.access_request
    ADD CONSTRAINT idx_16434_primary PRIMARY KEY (access_request_id);


--
-- Name: access_request_file idx_16444_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.access_request_file
    ADD CONSTRAINT idx_16444_primary PRIMARY KEY (access_request_file_id);


--
-- Name: account_new idx_16451_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.account_new
    ADD CONSTRAINT idx_16451_primary PRIMARY KEY (account_new_id);


--
-- Name: account_reset idx_16461_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.account_reset
    ADD CONSTRAINT idx_16461_primary PRIMARY KEY (account_reset_id);


--
-- Name: additional_attributes idx_16471_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.additional_attributes
    ADD CONSTRAINT idx_16471_primary PRIMARY KEY (additional_attributes_id);


--
-- Name: characteristics idx_16480_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.characteristics
    ADD CONSTRAINT idx_16480_primary PRIMARY KEY (characteristics_id);


--
-- Name: contact idx_16489_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.contact
    ADD CONSTRAINT idx_16489_primary PRIMARY KEY (contact_id);


--
-- Name: continent idx_16498_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.continent
    ADD CONSTRAINT idx_16498_primary PRIMARY KEY (continent_id);


--
-- Name: country idx_16504_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.country
    ADD CONSTRAINT idx_16504_primary PRIMARY KEY (country_id);


--
-- Name: dataset idx_16513_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset
    ADD CONSTRAINT idx_16513_primary PRIMARY KEY (dataset_id, dataset_version);


--
-- Name: dataset_ancillary_keyword idx_16527_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_ancillary_keyword
    ADD CONSTRAINT idx_16527_primary PRIMARY KEY (dataset_ancillary_keyword_id);


--
-- Name: dataset_citation idx_16536_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_citation
    ADD CONSTRAINT idx_16536_primary PRIMARY KEY (dataset_citation_id);


--
-- Name: dataset_data_center idx_16545_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_data_center
    ADD CONSTRAINT idx_16545_primary PRIMARY KEY (dataset_data_center_id);


--
-- Name: dataset_data_center_person idx_16551_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_data_center_person
    ADD CONSTRAINT idx_16551_primary PRIMARY KEY (dataset_data_center_person_id);


--
-- Name: dataset_file idx_16555_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_file
    ADD CONSTRAINT idx_16555_primary PRIMARY KEY (dataset_id, dataset_version_min, file_id);


--
-- Name: dataset_keyword idx_16560_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_keyword
    ADD CONSTRAINT idx_16560_primary PRIMARY KEY (dataset_keyword_id);


--
-- Name: dataset_link idx_16569_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_link
    ADD CONSTRAINT idx_16569_primary PRIMARY KEY (dataset_link_id);


--
-- Name: dataset_link_url idx_16578_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_link_url
    ADD CONSTRAINT idx_16578_primary PRIMARY KEY (dataset_link_url_id);


--
-- Name: dataset_person idx_16585_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_person
    ADD CONSTRAINT idx_16585_primary PRIMARY KEY (dataset_id, dataset_version_min, person_id);


--
-- Name: dataset_project idx_16592_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_project
    ADD CONSTRAINT idx_16592_primary PRIMARY KEY (dataset_id, dataset_version_min, project_version_min, project_id);


--
-- Name: dataset_publication idx_16595_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_publication
    ADD CONSTRAINT idx_16595_primary PRIMARY KEY (publication_id, publication_version_min, dataset_id, dataset_version_min);


--
-- Name: dataset_topic idx_16598_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_topic
    ADD CONSTRAINT idx_16598_primary PRIMARY KEY (vocab_iso_topic_category_id, dataset_id, dataset_version_min);


--
-- Name: data_center_person_default idx_16601_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.data_center_person_default
    ADD CONSTRAINT idx_16601_primary PRIMARY KEY (organization_id, person_id);


--
-- Name: data_resolution idx_16606_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.data_resolution
    ADD CONSTRAINT idx_16606_primary PRIMARY KEY (data_resolution_id);


--
-- Name: distribution idx_16615_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.distribution
    ADD CONSTRAINT idx_16615_primary PRIMARY KEY (distribution_id);


--
-- Name: file idx_16624_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.file
    ADD CONSTRAINT idx_16624_primary PRIMARY KEY (file_id);


--
-- Name: instrument idx_16636_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.instrument
    ADD CONSTRAINT idx_16636_primary PRIMARY KEY (instrument_id);


--
-- Name: location idx_16645_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.location
    ADD CONSTRAINT idx_16645_primary PRIMARY KEY (location_id);


--
-- Name: menu idx_16654_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.menu
    ADD CONSTRAINT idx_16654_primary PRIMARY KEY (menu_id);


--
-- Name: metadata_association idx_16663_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.metadata_association
    ADD CONSTRAINT idx_16663_primary PRIMARY KEY (metadata_association_id);


--
-- Name: mime_type idx_16672_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.mime_type
    ADD CONSTRAINT idx_16672_primary PRIMARY KEY (mime_type_id);


--
-- Name: multimedia_sample idx_16682_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.multimedia_sample
    ADD CONSTRAINT idx_16682_primary PRIMARY KEY (multimedia_sample_id);


--
-- Name: news idx_16691_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.news
    ADD CONSTRAINT idx_16691_primary PRIMARY KEY (news_id);


--
-- Name: npp_theme idx_16701_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.npp_theme
    ADD CONSTRAINT idx_16701_primary PRIMARY KEY (npp_theme_id);


--
-- Name: organization idx_16709_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.organization
    ADD CONSTRAINT idx_16709_primary PRIMARY KEY (organization_id);


--
-- Name: page idx_16719_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.page
    ADD CONSTRAINT idx_16719_primary PRIMARY KEY (page_id);


--
-- Name: page_link idx_16730_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.page_link
    ADD CONSTRAINT idx_16730_primary PRIMARY KEY (page_link_id);


--
-- Name: page_person idx_16737_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.page_person
    ADD CONSTRAINT idx_16737_primary PRIMARY KEY (page_id, person_id);


--
-- Name: person idx_16746_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.person
    ADD CONSTRAINT idx_16746_primary PRIMARY KEY (person_id);


--
-- Name: platform idx_16761_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.platform
    ADD CONSTRAINT idx_16761_primary PRIMARY KEY (platform_id);


--
-- Name: program idx_16767_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.program
    ADD CONSTRAINT idx_16767_primary PRIMARY KEY (program_id);


--
-- Name: project idx_16776_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project
    ADD CONSTRAINT idx_16776_primary PRIMARY KEY (project_id, project_version);


--
-- Name: project_keyword idx_16788_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project_keyword
    ADD CONSTRAINT idx_16788_primary PRIMARY KEY (project_keyword_id);


--
-- Name: project_link idx_16797_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project_link
    ADD CONSTRAINT idx_16797_primary PRIMARY KEY (project_link_id);


--
-- Name: project_person idx_16804_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project_person
    ADD CONSTRAINT idx_16804_primary PRIMARY KEY (person_id, project_version_min, project_id);


--
-- Name: project_project idx_16812_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project_project
    ADD CONSTRAINT idx_16812_primary PRIMARY KEY (parent_project_id, child_project_id);


--
-- Name: project_publication idx_16815_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project_publication
    ADD CONSTRAINT idx_16815_primary PRIMARY KEY (project_id, project_version_min, publication_version_min, publication_id);


--
-- Name: publication idx_16820_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication
    ADD CONSTRAINT idx_16820_primary PRIMARY KEY (publication_id, publication_version);


--
-- Name: publication_keyword idx_16831_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication_keyword
    ADD CONSTRAINT idx_16831_primary PRIMARY KEY (publication_keyword_id);


--
-- Name: publication_person idx_16840_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication_person
    ADD CONSTRAINT idx_16840_primary PRIMARY KEY (publication_person_id);


--
-- Name: publication_type idx_16853_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication_type
    ADD CONSTRAINT idx_16853_primary PRIMARY KEY (publication_type_id);


--
-- Name: record_status idx_16860_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.record_status
    ADD CONSTRAINT idx_16860_primary PRIMARY KEY (record_status);


--
-- Name: record_status_change idx_16865_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.record_status_change
    ADD CONSTRAINT idx_16865_primary PRIMARY KEY (record_status_change_id);


--
-- Name: related_dataset idx_16875_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.related_dataset
    ADD CONSTRAINT idx_16875_primary PRIMARY KEY (related_dataset_id);


--
-- Name: sensor idx_16884_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.sensor
    ADD CONSTRAINT idx_16884_primary PRIMARY KEY (sensor_id);


--
-- Name: spatial_coverage idx_16893_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.spatial_coverage
    ADD CONSTRAINT idx_16893_primary PRIMARY KEY (spatial_coverage_id);


--
-- Name: suggestion idx_16903_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.suggestion
    ADD CONSTRAINT idx_16903_primary PRIMARY KEY (suggestion_id);


--
-- Name: temporal_coverage idx_16912_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage
    ADD CONSTRAINT idx_16912_primary PRIMARY KEY (temporal_coverage_id);


--
-- Name: temporal_coverage_ancillary idx_16918_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_ancillary
    ADD CONSTRAINT idx_16918_primary PRIMARY KEY (temporal_coverage_ancillary_id);


--
-- Name: temporal_coverage_cycle idx_16927_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_cycle
    ADD CONSTRAINT idx_16927_primary PRIMARY KEY (temporal_coverage_cycle_id);


--
-- Name: temporal_coverage_paleo idx_16936_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_paleo
    ADD CONSTRAINT idx_16936_primary PRIMARY KEY (temporal_coverage_paleo_id);


--
-- Name: temporal_coverage_paleo_chronounit idx_16945_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_paleo_chronounit
    ADD CONSTRAINT idx_16945_primary PRIMARY KEY (temporal_coverage_paleo_chronounit_id);


--
-- Name: temporal_coverage_period idx_16954_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_period
    ADD CONSTRAINT idx_16954_primary PRIMARY KEY (temporal_coverage_period_id);


--
-- Name: user_level idx_16960_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.user_level
    ADD CONSTRAINT idx_16960_primary PRIMARY KEY (user_level_id);


--
-- Name: vocab idx_16967_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab
    ADD CONSTRAINT idx_16967_primary PRIMARY KEY (vocab_id);


--
-- Name: vocab_chronounit idx_16976_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_chronounit
    ADD CONSTRAINT idx_16976_primary PRIMARY KEY (vocab_chronounit_id);


--
-- Name: vocab_idn_node idx_16987_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_idn_node
    ADD CONSTRAINT idx_16987_primary PRIMARY KEY (vocab_idn_node_id);


--
-- Name: vocab_instrument idx_16997_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_instrument
    ADD CONSTRAINT idx_16997_primary PRIMARY KEY (vocab_instrument_id);


--
-- Name: vocab_iso_topic_category idx_17008_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_iso_topic_category
    ADD CONSTRAINT idx_17008_primary PRIMARY KEY (vocab_iso_topic_category_id);


--
-- Name: vocab_location idx_17018_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_location
    ADD CONSTRAINT idx_17018_primary PRIMARY KEY (vocab_location_id);


--
-- Name: vocab_location_vocab_idn_node idx_17026_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_location_vocab_idn_node
    ADD CONSTRAINT idx_17026_primary PRIMARY KEY (vocab_location_id, vocab_idn_node_id);


--
-- Name: vocab_platform idx_17037_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_platform
    ADD CONSTRAINT idx_17037_primary PRIMARY KEY (vocab_platform_id);


--
-- Name: vocab_res_hor idx_17047_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_res_hor
    ADD CONSTRAINT idx_17047_primary PRIMARY KEY (vocab_res_hor_id);


--
-- Name: vocab_res_time idx_17057_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_res_time
    ADD CONSTRAINT idx_17057_primary PRIMARY KEY (vocab_res_time_id);


--
-- Name: vocab_res_vert idx_17067_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_res_vert
    ADD CONSTRAINT idx_17067_primary PRIMARY KEY (vocab_res_vert_id);


--
-- Name: vocab_science_keyword idx_17077_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_science_keyword
    ADD CONSTRAINT idx_17077_primary PRIMARY KEY (vocab_science_keyword_id);


--
-- Name: vocab_url_type idx_17087_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_url_type
    ADD CONSTRAINT idx_17087_primary PRIMARY KEY (vocab_url_type_id);


--
-- Name: zip idx_17097_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.zip
    ADD CONSTRAINT idx_17097_primary PRIMARY KEY (zip_id);


--
-- Name: zip_files idx_17107_primary; Type: CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.zip_files
    ADD CONSTRAINT idx_17107_primary PRIMARY KEY (zip_files_id);


--
-- Name: idx_16434_access_request_x_person_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16434_access_request_x_person_fk ON npdc.access_request USING btree (person_id);


--
-- Name: idx_16434_fki_access_zip; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16434_fki_access_zip ON npdc.access_request USING btree (zip_id);


--
-- Name: idx_16434_fki_responder; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16434_fki_responder ON npdc.access_request USING btree (responder_id);


--
-- Name: idx_16444_access_request_file_x_access_request_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16444_access_request_file_x_access_request_fk ON npdc.access_request_file USING btree (access_request_id);


--
-- Name: idx_16444_access_request_file_x_file_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16444_access_request_file_x_file_fk ON npdc.access_request_file USING btree (file_id);


--
-- Name: idx_16461_account_reset_x_person_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16461_account_reset_x_person_fk ON npdc.account_reset USING btree (person_id);


--
-- Name: idx_16471_additional_attributes_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16471_additional_attributes_x_dataset_fk ON npdc.additional_attributes USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16480_characteristics_x_instrument_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16480_characteristics_x_instrument_fk ON npdc.characteristics USING btree (instrument_id);


--
-- Name: idx_16480_characteristics_x_platform_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16480_characteristics_x_platform_fk ON npdc.characteristics USING btree (platform_id);


--
-- Name: idx_16480_characteristics_x_sensor_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16480_characteristics_x_sensor_fk ON npdc.characteristics USING btree (sensor_id);


--
-- Name: idx_16504_country_x_continent_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16504_country_x_continent_fk ON npdc.country USING btree (continent_id);


--
-- Name: idx_16513_dataset_record_status; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16513_dataset_record_status ON npdc.dataset USING btree (record_status);


--
-- Name: idx_16513_dataset_x_organization_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16513_dataset_x_organization_fk ON npdc.dataset USING btree (originating_center);


--
-- Name: idx_16513_dataset_x_person_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16513_dataset_x_person_fk ON npdc.dataset USING btree (creator);


--
-- Name: idx_16527_dataset_id; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16527_dataset_id ON npdc.dataset_ancillary_keyword USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16536_dataset_citation_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16536_dataset_citation_x_dataset_fk ON npdc.dataset_citation USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16545_dataset_data_center_id; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16545_dataset_data_center_id ON npdc.dataset_data_center USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16545_organization_id; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16545_organization_id ON npdc.dataset_data_center USING btree (organization_id);


--
-- Name: idx_16551_dataset_data_center_id; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16551_dataset_data_center_id ON npdc.dataset_data_center_person USING btree (dataset_data_center_id);


--
-- Name: idx_16551_person_id; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16551_person_id ON npdc.dataset_data_center_person USING btree (person_id);


--
-- Name: idx_16555_dataset_file_x_file_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16555_dataset_file_x_file_fk ON npdc.dataset_file USING btree (file_id);


--
-- Name: idx_16560_dataset_keyword_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16560_dataset_keyword_x_dataset_fk ON npdc.dataset_keyword USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16560_dataset_keyword_x_vocab_science_keyword_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16560_dataset_keyword_x_vocab_science_keyword_fk ON npdc.dataset_keyword USING btree (vocab_science_keyword_id);


--
-- Name: idx_16569_dataset_link_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16569_dataset_link_x_dataset_fk ON npdc.dataset_link USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16569_dataset_link_x_vocab_url_type_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16569_dataset_link_x_vocab_url_type_fk ON npdc.dataset_link USING btree (vocab_url_type_id);


--
-- Name: idx_16569_fki_mime; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16569_fki_mime ON npdc.dataset_link USING btree (mime_type_id);


--
-- Name: idx_16578_fki_link; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16578_fki_link ON npdc.dataset_link_url USING btree (dataset_link_id);


--
-- Name: idx_16578_old_dataset_link_url_id; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16578_old_dataset_link_url_id ON npdc.dataset_link_url USING btree (old_dataset_link_url_id);


--
-- Name: idx_16585_dataset_person_x_person_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16585_dataset_person_x_person_fk ON npdc.dataset_person USING btree (person_id);


--
-- Name: idx_16585_dataset_x_org_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16585_dataset_x_org_fk ON npdc.dataset_person USING btree (organization_id);


--
-- Name: idx_16592_dataset_project_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16592_dataset_project_x_dataset_fk ON npdc.dataset_project USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16592_dataset_project_x_project_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16592_dataset_project_x_project_fk ON npdc.dataset_project USING btree (project_id, project_version_min);


--
-- Name: idx_16595_dataset_publication_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16595_dataset_publication_x_dataset_fk ON npdc.dataset_publication USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16598_dataset_topic_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16598_dataset_topic_x_dataset_fk ON npdc.dataset_topic USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16601_data_center_org_id; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16601_data_center_org_id ON npdc.data_center_person_default USING btree (organization_id);


--
-- Name: idx_16601_data_center_person_id; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16601_data_center_person_id ON npdc.data_center_person_default USING btree (person_id);


--
-- Name: idx_16606_data_resolution_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16606_data_resolution_x_dataset_fk ON npdc.data_resolution USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16606_data_resolution_x_vocab_res_hor_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16606_data_resolution_x_vocab_res_hor_fk ON npdc.data_resolution USING btree (vocab_res_hor_id);


--
-- Name: idx_16606_data_resolution_x_vocab_res_time_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16606_data_resolution_x_vocab_res_time_fk ON npdc.data_resolution USING btree (vocab_res_time_id);


--
-- Name: idx_16606_data_resolution_x_vocab_res_vert_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16606_data_resolution_x_vocab_res_vert_fk ON npdc.data_resolution USING btree (vocab_res_vert_id);


--
-- Name: idx_16615_distribution_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16615_distribution_x_dataset_fk ON npdc.distribution USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16636_instrument_x_platform_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16636_instrument_x_platform_fk ON npdc.instrument USING btree (platform_id);


--
-- Name: idx_16636_instrument_x_vocab_instrument_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16636_instrument_x_vocab_instrument_fk ON npdc.instrument USING btree (vocab_instrument_id);


--
-- Name: idx_16636_old_instrument_id; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16636_old_instrument_id ON npdc.instrument USING btree (old_instrument_id);


--
-- Name: idx_16645_fki_location_dataset; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16645_fki_location_dataset ON npdc.location USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16645_fki_location_vocab; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16645_fki_location_vocab ON npdc.location USING btree (vocab_location_id);


--
-- Name: idx_16654_fki_parent_menu_id; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16654_fki_parent_menu_id ON npdc.menu USING btree (parent_menu_id);


--
-- Name: idx_16663_metadata_association_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16663_metadata_association_x_dataset_fk ON npdc.metadata_association USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16682_multimedia_sample_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16682_multimedia_sample_x_dataset_fk ON npdc.multimedia_sample USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16709_fki_organization_country; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16709_fki_organization_country ON npdc.organization USING btree (country_id);


--
-- Name: idx_16730_page_link_x_page_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16730_page_link_x_page_fk ON npdc.page_link USING btree (page_id);


--
-- Name: idx_16737_page_person_x_person_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16737_page_person_x_person_fk ON npdc.page_person USING btree (person_id);


--
-- Name: idx_16746_person_x_organization_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16746_person_x_organization_fk ON npdc.person USING btree (organization_id);


--
-- Name: idx_16746_person_x_user_level_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16746_person_x_user_level_fk ON npdc.person USING btree (user_level);


--
-- Name: idx_16761_platform_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16761_platform_x_dataset_fk ON npdc.platform USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16761_platform_x_vocab_platform_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16761_platform_x_vocab_platform_fk ON npdc.platform USING btree (vocab_platform_id);


--
-- Name: idx_16776_project_record_status; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16776_project_record_status ON npdc.project USING btree (record_status);


--
-- Name: idx_16776_project_x_npp_theme_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16776_project_x_npp_theme_fk ON npdc.project USING btree (npp_theme_id);


--
-- Name: idx_16776_project_x_person_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16776_project_x_person_fk ON npdc.project USING btree (creator);


--
-- Name: idx_16776_project_x_program_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16776_project_x_program_fk ON npdc.project USING btree (program_id);


--
-- Name: idx_16788_project_keyword_x_project_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16788_project_keyword_x_project_fk ON npdc.project_keyword USING btree (project_id, project_version_min);


--
-- Name: idx_16797_project_link_x_project_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16797_project_link_x_project_fk ON npdc.project_link USING btree (project_id, project_version_min);


--
-- Name: idx_16804_project_person_x_organization_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16804_project_person_x_organization_fk ON npdc.project_person USING btree (organization_id);


--
-- Name: idx_16804_project_person_x_person_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16804_project_person_x_person_fk ON npdc.project_person USING btree (person_id);


--
-- Name: idx_16804_project_person_x_project_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16804_project_person_x_project_fk ON npdc.project_person USING btree (project_id, project_version_min);


--
-- Name: idx_16812_child_id; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16812_child_id ON npdc.project_project USING btree (child_project_id);


--
-- Name: idx_16815_project_publication_x_project_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16815_project_publication_x_project_fk ON npdc.project_publication USING btree (project_id, project_version_min);


--
-- Name: idx_16815_project_publication_x_publication_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16815_project_publication_x_publication_fk ON npdc.project_publication USING btree (publication_id, publication_version_min);


--
-- Name: idx_16820_publication_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16820_publication_fk ON npdc.publication USING btree (publication_type_id);


--
-- Name: idx_16820_publication_record_status; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16820_publication_record_status ON npdc.publication USING btree (record_status);


--
-- Name: idx_16820_publication_x_person_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16820_publication_x_person_fk ON npdc.publication USING btree (creator);


--
-- Name: idx_16831_publication_keyword_x_publication_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16831_publication_keyword_x_publication_fk ON npdc.publication_keyword USING btree (publication_id, publication_version_min);


--
-- Name: idx_16840_publication_person_x_person_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16840_publication_person_x_person_fk ON npdc.publication_person USING btree (person_id);


--
-- Name: idx_16840_publication_person_x_publication_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16840_publication_person_x_publication_fk ON npdc.publication_person USING btree (publication_id, publication_version_min, person_id);


--
-- Name: idx_16840_publication_x_organization_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16840_publication_x_organization_fk ON npdc.publication_person USING btree (organization_id);


--
-- Name: idx_16860_record_status_index; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE UNIQUE INDEX idx_16860_record_status_index ON npdc.record_status USING btree (record_status);


--
-- Name: idx_16875_related_dataset_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16875_related_dataset_x_dataset_fk ON npdc.related_dataset USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16884_fki_instrument; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16884_fki_instrument ON npdc.sensor USING btree (vocab_instrument_id);


--
-- Name: idx_16884_old_sensor_id; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16884_old_sensor_id ON npdc.sensor USING btree (old_sensor_id);


--
-- Name: idx_16884_sensor_x_instrument_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16884_sensor_x_instrument_fk ON npdc.sensor USING btree (instrument_id);


--
-- Name: idx_16893_spatial_coverage_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16893_spatial_coverage_x_dataset_fk ON npdc.spatial_coverage USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16903_field; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16903_field ON npdc.suggestion USING btree (field);


--
-- Name: idx_16912_temporal_coverage_x_dataset_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16912_temporal_coverage_x_dataset_fk ON npdc.temporal_coverage USING btree (dataset_id, dataset_version_min);


--
-- Name: idx_16918_temporal_coverage_ancillary_x_temporal_coverage_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16918_temporal_coverage_ancillary_x_temporal_coverage_fk ON npdc.temporal_coverage_ancillary USING btree (temporal_coverage_id);


--
-- Name: idx_16927_temporal_coverage_cycle_x_temporal_coverage_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16927_temporal_coverage_cycle_x_temporal_coverage_fk ON npdc.temporal_coverage_cycle USING btree (temporal_coverage_id);


--
-- Name: idx_16936_temporal_coverage_paleo_x_temporal_coverage_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16936_temporal_coverage_paleo_x_temporal_coverage_fk ON npdc.temporal_coverage_paleo USING btree (temporal_coverage_id);


--
-- Name: idx_16945_fk_temporal_coverage_paleo_chronounit_temporal_covera; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16945_fk_temporal_coverage_paleo_chronounit_temporal_covera ON npdc.temporal_coverage_paleo_chronounit USING btree (temporal_coverage_paleo_id);


--
-- Name: idx_16945_fk_temporal_coverage_paleo_chronounit_vocab_chronouni; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16945_fk_temporal_coverage_paleo_chronounit_vocab_chronouni ON npdc.temporal_coverage_paleo_chronounit USING btree (vocab_chronounit_id);


--
-- Name: idx_16954_temporal_coverage_period_x_temporal_coverage_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_16954_temporal_coverage_period_x_temporal_coverage_fk ON npdc.temporal_coverage_period USING btree (temporal_coverage_id);


--
-- Name: idx_16960_user_level_label; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE UNIQUE INDEX idx_16960_user_level_label ON npdc.user_level USING btree (label);


--
-- Name: idx_17026_vocab_idn_node; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_17026_vocab_idn_node ON npdc.vocab_location_vocab_idn_node USING btree (vocab_idn_node_id);


--
-- Name: idx_17026_vocab_location; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_17026_vocab_location ON npdc.vocab_location_vocab_idn_node USING btree (vocab_location_id);


--
-- Name: idx_17097_dataset_id; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_17097_dataset_id ON npdc.zip USING btree (dataset_id);


--
-- Name: idx_17097_zip_x_person_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_17097_zip_x_person_fk ON npdc.zip USING btree (person_id);


--
-- Name: idx_17107_zip_files_x_file_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_17107_zip_files_x_file_fk ON npdc.zip_files USING btree (file_id);


--
-- Name: idx_17107_zip_files_x_zip_fk; Type: INDEX; Schema: npdc; Owner: npdc
--

CREATE INDEX idx_17107_zip_files_x_zip_fk ON npdc.zip_files USING btree (zip_id);


--
-- Name: access_request_file access_request_file_x_access_request_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.access_request_file
    ADD CONSTRAINT access_request_file_x_access_request_fk FOREIGN KEY (access_request_id) REFERENCES npdc.access_request(access_request_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: access_request_file access_request_file_x_file_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.access_request_file
    ADD CONSTRAINT access_request_file_x_file_fk FOREIGN KEY (file_id) REFERENCES npdc.file(file_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: access_request access_request_x_person_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.access_request
    ADD CONSTRAINT access_request_x_person_fk FOREIGN KEY (person_id) REFERENCES npdc.person(person_id) ON UPDATE CASCADE;


--
-- Name: access_request access_request_x_person_responder_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.access_request
    ADD CONSTRAINT access_request_x_person_responder_fk FOREIGN KEY (responder_id) REFERENCES npdc.person(person_id) ON UPDATE CASCADE;


--
-- Name: access_request access_request_x_zip_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.access_request
    ADD CONSTRAINT access_request_x_zip_fk FOREIGN KEY (zip_id) REFERENCES npdc.zip(zip_id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: account_reset account_reset_x_person_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.account_reset
    ADD CONSTRAINT account_reset_x_person_fk FOREIGN KEY (person_id) REFERENCES npdc.person(person_id) ON UPDATE CASCADE;


--
-- Name: additional_attributes additional_attributes_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.additional_attributes
    ADD CONSTRAINT additional_attributes_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: characteristics characteristics_x_instrument_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.characteristics
    ADD CONSTRAINT characteristics_x_instrument_fk FOREIGN KEY (instrument_id) REFERENCES npdc.instrument(instrument_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: characteristics characteristics_x_platform_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.characteristics
    ADD CONSTRAINT characteristics_x_platform_fk FOREIGN KEY (platform_id) REFERENCES npdc.platform(platform_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: characteristics characteristics_x_sensor_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.characteristics
    ADD CONSTRAINT characteristics_x_sensor_fk FOREIGN KEY (sensor_id) REFERENCES npdc.sensor(sensor_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: country country_x_continent_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.country
    ADD CONSTRAINT country_x_continent_fk FOREIGN KEY (continent_id) REFERENCES npdc.continent(continent_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: data_center_person_default data_center_person_default_x_organization_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.data_center_person_default
    ADD CONSTRAINT data_center_person_default_x_organization_fk FOREIGN KEY (organization_id) REFERENCES npdc.organization(organization_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: data_center_person_default data_center_person_default_x_person_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.data_center_person_default
    ADD CONSTRAINT data_center_person_default_x_person_fk FOREIGN KEY (person_id) REFERENCES npdc.person(person_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: data_resolution data_resolution_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.data_resolution
    ADD CONSTRAINT data_resolution_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: data_resolution data_resolution_x_vocab_res_hor_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.data_resolution
    ADD CONSTRAINT data_resolution_x_vocab_res_hor_fk FOREIGN KEY (vocab_res_hor_id) REFERENCES npdc.vocab_res_hor(vocab_res_hor_id) ON UPDATE CASCADE;


--
-- Name: data_resolution data_resolution_x_vocab_res_time_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.data_resolution
    ADD CONSTRAINT data_resolution_x_vocab_res_time_fk FOREIGN KEY (vocab_res_time_id) REFERENCES npdc.vocab_res_time(vocab_res_time_id) ON UPDATE CASCADE;


--
-- Name: data_resolution data_resolution_x_vocab_res_vert_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.data_resolution
    ADD CONSTRAINT data_resolution_x_vocab_res_vert_fk FOREIGN KEY (vocab_res_vert_id) REFERENCES npdc.vocab_res_vert(vocab_res_vert_id) ON UPDATE CASCADE;


--
-- Name: dataset_ancillary_keyword dataset_ancillary_keyword_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_ancillary_keyword
    ADD CONSTRAINT dataset_ancillary_keyword_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_citation dataset_citation_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_citation
    ADD CONSTRAINT dataset_citation_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_data_center_person dataset_data_center_person_x_dataset_data_center_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_data_center_person
    ADD CONSTRAINT dataset_data_center_person_x_dataset_data_center_fk FOREIGN KEY (dataset_data_center_id) REFERENCES npdc.dataset_data_center(dataset_data_center_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_data_center_person dataset_data_center_person_x_person_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_data_center_person
    ADD CONSTRAINT dataset_data_center_person_x_person_fk FOREIGN KEY (person_id) REFERENCES npdc.person(person_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_data_center dataset_data_center_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_data_center
    ADD CONSTRAINT dataset_data_center_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_data_center dataset_data_center_x_organization_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_data_center
    ADD CONSTRAINT dataset_data_center_x_organization_fk FOREIGN KEY (organization_id) REFERENCES npdc.organization(organization_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_file dataset_file_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_file
    ADD CONSTRAINT dataset_file_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_file dataset_file_x_file_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_file
    ADD CONSTRAINT dataset_file_x_file_fk FOREIGN KEY (file_id) REFERENCES npdc.file(file_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_keyword dataset_keyword_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_keyword
    ADD CONSTRAINT dataset_keyword_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_keyword dataset_keyword_x_vocab_science_keyword_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_keyword
    ADD CONSTRAINT dataset_keyword_x_vocab_science_keyword_fk FOREIGN KEY (vocab_science_keyword_id) REFERENCES npdc.vocab_science_keyword(vocab_science_keyword_id) ON UPDATE CASCADE;


--
-- Name: dataset_link_url dataset_link_url_x_dataset_link_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_link_url
    ADD CONSTRAINT dataset_link_url_x_dataset_link_fk FOREIGN KEY (dataset_link_id) REFERENCES npdc.dataset_link(dataset_link_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_link dataset_link_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_link
    ADD CONSTRAINT dataset_link_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_link dataset_link_x_mime_type_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_link
    ADD CONSTRAINT dataset_link_x_mime_type_fk FOREIGN KEY (mime_type_id) REFERENCES npdc.mime_type(mime_type_id) ON UPDATE CASCADE;


--
-- Name: dataset_link dataset_link_x_vocab_url_type_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_link
    ADD CONSTRAINT dataset_link_x_vocab_url_type_fk FOREIGN KEY (vocab_url_type_id) REFERENCES npdc.vocab_url_type(vocab_url_type_id) ON UPDATE CASCADE;


--
-- Name: dataset_person dataset_person_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_person
    ADD CONSTRAINT dataset_person_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_person dataset_person_x_person_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_person
    ADD CONSTRAINT dataset_person_x_person_fk FOREIGN KEY (person_id) REFERENCES npdc.person(person_id) ON UPDATE CASCADE;


--
-- Name: dataset_project dataset_project_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_project
    ADD CONSTRAINT dataset_project_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_project dataset_project_x_project_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_project
    ADD CONSTRAINT dataset_project_x_project_fk FOREIGN KEY (project_id, project_version_min) REFERENCES npdc.project(project_id, project_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_publication dataset_publication_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_publication
    ADD CONSTRAINT dataset_publication_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_publication dataset_publication_x_publication_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_publication
    ADD CONSTRAINT dataset_publication_x_publication_fk FOREIGN KEY (publication_id, publication_version_min) REFERENCES npdc.publication(publication_id, publication_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_topic dataset_topic_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_topic
    ADD CONSTRAINT dataset_topic_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_topic dataset_topic_x_vocab_iso_topic_category_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_topic
    ADD CONSTRAINT dataset_topic_x_vocab_iso_topic_category_fk FOREIGN KEY (vocab_iso_topic_category_id) REFERENCES npdc.vocab_iso_topic_category(vocab_iso_topic_category_id) ON UPDATE CASCADE;


--
-- Name: dataset_person dataset_x_org_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_person
    ADD CONSTRAINT dataset_x_org_fk FOREIGN KEY (organization_id) REFERENCES npdc.organization(organization_id) ON UPDATE CASCADE;


--
-- Name: dataset dataset_x_organization_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset
    ADD CONSTRAINT dataset_x_organization_fk FOREIGN KEY (originating_center) REFERENCES npdc.organization(organization_id) ON UPDATE CASCADE;


--
-- Name: dataset dataset_x_person_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset
    ADD CONSTRAINT dataset_x_person_fk FOREIGN KEY (creator) REFERENCES npdc.person(person_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: dataset dataset_x_record_status_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset
    ADD CONSTRAINT dataset_x_record_status_fk FOREIGN KEY (record_status) REFERENCES npdc.record_status(record_status) ON UPDATE CASCADE;


--
-- Name: distribution distribution_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.distribution
    ADD CONSTRAINT distribution_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: temporal_coverage_paleo_chronounit fk_temporal_coverage_paleo_chronounit_temporal_coverage_paleo; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_paleo_chronounit
    ADD CONSTRAINT fk_temporal_coverage_paleo_chronounit_temporal_coverage_paleo FOREIGN KEY (temporal_coverage_paleo_id) REFERENCES npdc.temporal_coverage_paleo(temporal_coverage_paleo_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: temporal_coverage_paleo_chronounit fk_temporal_coverage_paleo_chronounit_vocab_chronounit; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_paleo_chronounit
    ADD CONSTRAINT fk_temporal_coverage_paleo_chronounit_vocab_chronounit FOREIGN KEY (vocab_chronounit_id) REFERENCES npdc.vocab_chronounit(vocab_chronounit_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: instrument instrument_x_platform_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.instrument
    ADD CONSTRAINT instrument_x_platform_fk FOREIGN KEY (platform_id) REFERENCES npdc.platform(platform_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: instrument instrument_x_vocab_instrument_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.instrument
    ADD CONSTRAINT instrument_x_vocab_instrument_fk FOREIGN KEY (vocab_instrument_id) REFERENCES npdc.vocab_instrument(vocab_instrument_id) ON UPDATE CASCADE;


--
-- Name: location location_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.location
    ADD CONSTRAINT location_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: location location_x_vocab_location_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.location
    ADD CONSTRAINT location_x_vocab_location_fk FOREIGN KEY (vocab_location_id) REFERENCES npdc.vocab_location(vocab_location_id) ON UPDATE CASCADE;


--
-- Name: menu menu_x_menu_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.menu
    ADD CONSTRAINT menu_x_menu_fk FOREIGN KEY (parent_menu_id) REFERENCES npdc.menu(menu_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: metadata_association metadata_association_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.metadata_association
    ADD CONSTRAINT metadata_association_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: multimedia_sample multimedia_sample_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.multimedia_sample
    ADD CONSTRAINT multimedia_sample_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_link_url old_dataset_link_url_id; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.dataset_link_url
    ADD CONSTRAINT old_dataset_link_url_id FOREIGN KEY (old_dataset_link_url_id) REFERENCES npdc.dataset_link_url(dataset_link_url_id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: instrument old_instrument_id; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.instrument
    ADD CONSTRAINT old_instrument_id FOREIGN KEY (old_instrument_id) REFERENCES npdc.instrument(instrument_id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: sensor old_sensor_id; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.sensor
    ADD CONSTRAINT old_sensor_id FOREIGN KEY (old_sensor_id) REFERENCES npdc.sensor(sensor_id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: organization organization_x_country_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.organization
    ADD CONSTRAINT organization_x_country_fk FOREIGN KEY (country_id) REFERENCES npdc.country(country_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: page_link page_link_x_page_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.page_link
    ADD CONSTRAINT page_link_x_page_fk FOREIGN KEY (page_id) REFERENCES npdc.page(page_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: page_person page_person_x_page_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.page_person
    ADD CONSTRAINT page_person_x_page_fk FOREIGN KEY (page_id) REFERENCES npdc.page(page_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: page_person page_person_x_person_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.page_person
    ADD CONSTRAINT page_person_x_person_fk FOREIGN KEY (person_id) REFERENCES npdc.person(person_id) ON UPDATE CASCADE;


--
-- Name: person person_x_organization_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.person
    ADD CONSTRAINT person_x_organization_fk FOREIGN KEY (organization_id) REFERENCES npdc.organization(organization_id) ON UPDATE CASCADE;


--
-- Name: person person_x_user_level_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.person
    ADD CONSTRAINT person_x_user_level_fk FOREIGN KEY (user_level) REFERENCES npdc.user_level(label) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: platform platform_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.platform
    ADD CONSTRAINT platform_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: platform platform_x_vocab_platform_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.platform
    ADD CONSTRAINT platform_x_vocab_platform_fk FOREIGN KEY (vocab_platform_id) REFERENCES npdc.vocab_platform(vocab_platform_id) ON UPDATE CASCADE;


--
-- Name: project_keyword project_keyword_x_project_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project_keyword
    ADD CONSTRAINT project_keyword_x_project_fk FOREIGN KEY (project_id, project_version_min) REFERENCES npdc.project(project_id, project_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: project_link project_link_project_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project_link
    ADD CONSTRAINT project_link_project_fk FOREIGN KEY (project_id, project_version_min) REFERENCES npdc.project(project_id, project_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: project_person project_person_x_organization_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project_person
    ADD CONSTRAINT project_person_x_organization_fk FOREIGN KEY (organization_id) REFERENCES npdc.organization(organization_id) ON UPDATE CASCADE;


--
-- Name: project_person project_person_x_person_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project_person
    ADD CONSTRAINT project_person_x_person_fk FOREIGN KEY (person_id) REFERENCES npdc.person(person_id) ON UPDATE CASCADE;


--
-- Name: project_person project_person_x_project_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project_person
    ADD CONSTRAINT project_person_x_project_fk FOREIGN KEY (project_id, project_version_min) REFERENCES npdc.project(project_id, project_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: project_publication project_publication_x_project_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project_publication
    ADD CONSTRAINT project_publication_x_project_fk FOREIGN KEY (project_id, project_version_min) REFERENCES npdc.project(project_id, project_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: project_publication project_publication_x_publication_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project_publication
    ADD CONSTRAINT project_publication_x_publication_fk FOREIGN KEY (publication_id, publication_version_min) REFERENCES npdc.publication(publication_id, publication_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: project project_x_npp_theme_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project
    ADD CONSTRAINT project_x_npp_theme_fk FOREIGN KEY (npp_theme_id) REFERENCES npdc.npp_theme(npp_theme_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: project project_x_person_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project
    ADD CONSTRAINT project_x_person_fk FOREIGN KEY (creator) REFERENCES npdc.person(person_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: project project_x_program_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project
    ADD CONSTRAINT project_x_program_fk FOREIGN KEY (program_id) REFERENCES npdc.program(program_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: project project_x_record_status_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.project
    ADD CONSTRAINT project_x_record_status_fk FOREIGN KEY (record_status) REFERENCES npdc.record_status(record_status) ON UPDATE CASCADE;


--
-- Name: publication publication_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication
    ADD CONSTRAINT publication_fk FOREIGN KEY (publication_type_id) REFERENCES npdc.publication_type(publication_type_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: publication_keyword publication_keyword_x_publication_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication_keyword
    ADD CONSTRAINT publication_keyword_x_publication_fk FOREIGN KEY (publication_id, publication_version_min) REFERENCES npdc.publication(publication_id, publication_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: publication_person publication_person_x_organization_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication_person
    ADD CONSTRAINT publication_person_x_organization_fk FOREIGN KEY (organization_id) REFERENCES npdc.organization(organization_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: publication_person publication_person_x_person_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication_person
    ADD CONSTRAINT publication_person_x_person_fk FOREIGN KEY (person_id) REFERENCES npdc.person(person_id) ON UPDATE CASCADE;


--
-- Name: publication_person publication_person_x_publication_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication_person
    ADD CONSTRAINT publication_person_x_publication_fk FOREIGN KEY (publication_id, publication_version_min) REFERENCES npdc.publication(publication_id, publication_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: publication publication_x_person_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication
    ADD CONSTRAINT publication_x_person_fk FOREIGN KEY (creator) REFERENCES npdc.person(person_id) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: publication publication_x_record_status_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.publication
    ADD CONSTRAINT publication_x_record_status_fk FOREIGN KEY (record_status) REFERENCES npdc.record_status(record_status) ON UPDATE CASCADE;


--
-- Name: related_dataset related_dataset_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.related_dataset
    ADD CONSTRAINT related_dataset_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE RESTRICT;


--
-- Name: sensor sensor_x_instrument_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.sensor
    ADD CONSTRAINT sensor_x_instrument_fk FOREIGN KEY (instrument_id) REFERENCES npdc.instrument(instrument_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: sensor sensor_x_vocab_instrument_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.sensor
    ADD CONSTRAINT sensor_x_vocab_instrument_fk FOREIGN KEY (vocab_instrument_id) REFERENCES npdc.vocab_instrument(vocab_instrument_id) ON UPDATE CASCADE;


--
-- Name: spatial_coverage spatial_coverage_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.spatial_coverage
    ADD CONSTRAINT spatial_coverage_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: temporal_coverage_ancillary temporal_coverage_ancillary_x_temporal_coverage_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_ancillary
    ADD CONSTRAINT temporal_coverage_ancillary_x_temporal_coverage_fk FOREIGN KEY (temporal_coverage_id) REFERENCES npdc.temporal_coverage(temporal_coverage_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: temporal_coverage_cycle temporal_coverage_cycle_x_temporal_coverage_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_cycle
    ADD CONSTRAINT temporal_coverage_cycle_x_temporal_coverage_fk FOREIGN KEY (temporal_coverage_id) REFERENCES npdc.temporal_coverage(temporal_coverage_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: temporal_coverage_paleo temporal_coverage_paleo_temporal_coverage_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_paleo
    ADD CONSTRAINT temporal_coverage_paleo_temporal_coverage_fk FOREIGN KEY (temporal_coverage_id) REFERENCES npdc.temporal_coverage(temporal_coverage_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: temporal_coverage_period temporal_coverage_period_x_temporal_coverage_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage_period
    ADD CONSTRAINT temporal_coverage_period_x_temporal_coverage_fk FOREIGN KEY (temporal_coverage_id) REFERENCES npdc.temporal_coverage(temporal_coverage_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: temporal_coverage temporal_coverage_x_dataset_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.temporal_coverage
    ADD CONSTRAINT temporal_coverage_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES npdc.dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: vocab_location_vocab_idn_node vocab_location_vocab_idn_node_idn_node; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_location_vocab_idn_node
    ADD CONSTRAINT vocab_location_vocab_idn_node_idn_node FOREIGN KEY (vocab_idn_node_id) REFERENCES npdc.vocab_idn_node(vocab_idn_node_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: vocab_location_vocab_idn_node vocab_location_vocab_idn_node_location; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.vocab_location_vocab_idn_node
    ADD CONSTRAINT vocab_location_vocab_idn_node_location FOREIGN KEY (vocab_location_id) REFERENCES npdc.vocab_location(vocab_location_id) ON UPDATE RESTRICT ON DELETE RESTRICT;


--
-- Name: zip_files zip_files_x_file_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.zip_files
    ADD CONSTRAINT zip_files_x_file_fk FOREIGN KEY (file_id) REFERENCES npdc.file(file_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: zip_files zip_files_x_zip_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.zip_files
    ADD CONSTRAINT zip_files_x_zip_fk FOREIGN KEY (zip_id) REFERENCES npdc.zip(zip_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: zip zip_x_person_fk; Type: FK CONSTRAINT; Schema: npdc; Owner: npdc
--

ALTER TABLE ONLY npdc.zip
    ADD CONSTRAINT zip_x_person_fk FOREIGN KEY (person_id) REFERENCES npdc.person(person_id) ON UPDATE CASCADE;


--


CREATE EXTENSION fuzzystrmatch;
--
-- npdcQL database dump complete
--


CREATE FUNCTION levenshtein_ratio( s1 VARCHAR(255), s2 VARCHAR(255) ) 
  RETURNS integer AS 'select ROUND((1 - levenshtein(s1, s2)::float / GREATEST(CHAR_LENGTH(s1), CHAR_LENGTH(s2))) * 100)::int'
  language sql;