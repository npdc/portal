--
-- PostgreSQL database dump
--

-- Dumped from database version 9.6.2
-- Dumped by pg_dump version 9.6.2

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SET check_function_bodies = false;
SET client_min_messages = warning;
SET row_security = off;

--
-- Name: plpgsql; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS plpgsql WITH SCHEMA pg_catalog;


--
-- Name: EXTENSION plpgsql; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION plpgsql IS 'PL/pgSQL procedural language';


--
-- Name: postgis; Type: EXTENSION; Schema: -; Owner: 
--

CREATE EXTENSION IF NOT EXISTS postgis WITH SCHEMA public;


--
-- Name: EXTENSION postgis; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION postgis IS 'PostGIS geometry, geography, and raster spatial types and functions';


SET search_path = public, pg_catalog;

--
-- Name: spatial_coverage_wkt_update(); Type: FUNCTION; Schema: public; Owner: db_admin
--

CREATE FUNCTION spatial_coverage_wkt_update() RETURNS trigger
    LANGUAGE plpgsql
    AS $$

BEGIN

IF (TG_OP = 'UPDATE' AND NOT ST_EQUALS(OLD.geom,NEW.geom)) THEN
	RAISE EXCEPTION 'Altering geom by hand is not permitted, please change using the wkt field';
END IF;
IF (TG_OP = 'INSERT' OR OLD.wkt<>NEW.wkt) THEN
	NEW.geom := st_geomfromtext(NEW.wkt, 4326);
END IF;

RETURN NEW;
END;

$$;


ALTER FUNCTION public.spatial_coverage_wkt_update() OWNER TO db_admin;

SET default_tablespace = '';

SET default_with_oids = false;

--
-- Name: access_request; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE access_request (
    access_request_id integer NOT NULL,
    person_id integer NOT NULL,
    reason text NOT NULL,
    request_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    permitted boolean,
    response text,
    response_timestamp timestamp without time zone,
    dataset_id integer,
    zip_id integer,
    responder_id integer
);


ALTER TABLE access_request OWNER TO npdc;

--
-- Name: access_request_access_request_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE access_request_access_request_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE access_request_access_request_id_seq OWNER TO npdc;

--
-- Name: access_request_access_request_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE access_request_access_request_id_seq OWNED BY access_request.access_request_id;


--
-- Name: access_request_file; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE access_request_file (
    access_request_file_id integer NOT NULL,
    access_request_id integer NOT NULL,
    file_id integer NOT NULL,
    permitted boolean DEFAULT false NOT NULL
);


ALTER TABLE access_request_file OWNER TO npdc;

--
-- Name: access_request_file_access_request_file_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE access_request_file_access_request_file_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE access_request_file_access_request_file_id_seq OWNER TO npdc;

--
-- Name: access_request_file_access_request_file_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE access_request_file_access_request_file_id_seq OWNED BY access_request_file.access_request_file_id;


--
-- Name: account_new; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE account_new (
    account_new_id integer NOT NULL,
    code character varying NOT NULL,
    request_time timestamp without time zone DEFAULT now() NOT NULL,
    used_time timestamp without time zone,
    expire_reason character varying,
    mail text
);


ALTER TABLE account_new OWNER TO npdc;

--
-- Name: account_new_account_new_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE account_new_account_new_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE account_new_account_new_id_seq OWNER TO npdc;

--
-- Name: account_new_account_new_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE account_new_account_new_id_seq OWNED BY account_new.account_new_id;


--
-- Name: account_reset; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE account_reset (
    account_reset_id integer NOT NULL,
    person_id integer NOT NULL,
    code character varying NOT NULL,
    request_time timestamp without time zone DEFAULT now() NOT NULL,
    used_time timestamp without time zone,
    expire_reason character varying
);


ALTER TABLE account_reset OWNER TO npdc;

--
-- Name: account_reset_account_reset_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE account_reset_account_reset_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE account_reset_account_reset_id_seq OWNER TO npdc;

--
-- Name: account_reset_account_reset_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE account_reset_account_reset_id_seq OWNED BY account_reset.account_reset_id;


--
-- Name: additional_attributes; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE additional_attributes (
    additional_attributes_id integer NOT NULL,
    dataset_id integer NOT NULL,
    name character varying NOT NULL,
    datatype character varying NOT NULL,
    description character varying NOT NULL,
    measurement_resolution character varying,
    parameter_range_begin character varying,
    parameter_range_end character varying,
    parameter_units_of_measure character varying,
    parameter_value_accuracy character varying,
    value_accuracy_explanation character varying,
    value character varying,
    dataset_version_min integer NOT NULL
);


ALTER TABLE additional_attributes OWNER TO npdc;

--
-- Name: TABLE additional_attributes; Type: COMMENT; Schema: public; Owner: npdc
--

COMMENT ON TABLE additional_attributes IS '= parameters';


--
-- Name: additional_attributes_additional_attributes_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE additional_attributes_additional_attributes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE additional_attributes_additional_attributes_id_seq OWNER TO npdc;

--
-- Name: additional_attributes_additional_attributes_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE additional_attributes_additional_attributes_id_seq OWNED BY additional_attributes.additional_attributes_id;


--
-- Name: characteristics; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE characteristics (
    characteristics_id integer NOT NULL,
    name character varying NOT NULL,
    description character varying NOT NULL,
    unit character varying NOT NULL,
    value character varying NOT NULL,
    platform_id integer,
    instrument_id integer,
    sensor_id integer,
    data_type character varying,
    dataset_version_min integer,
    dataset_version_max integer
);


ALTER TABLE characteristics OWNER TO npdc;

--
-- Name: characteristics_characteristics_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE characteristics_characteristics_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE characteristics_characteristics_id_seq OWNER TO npdc;

--
-- Name: characteristics_characteristics_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE characteristics_characteristics_id_seq OWNED BY characteristics.characteristics_id;


--
-- Name: continent; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE continent (
    continent_id character(2) NOT NULL,
    continent_name character varying
);


ALTER TABLE continent OWNER TO npdc;

--
-- Name: country; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE country (
    country_id character(2) NOT NULL,
    country_name character varying,
    continent_id character(2)
);


ALTER TABLE country OWNER TO npdc;

--
-- Name: data_resolution; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE data_resolution (
    data_resolution_id integer NOT NULL,
    dataset_id integer NOT NULL,
    latitude_resolution character varying,
    longitude_resolution character varying,
    vocab_res_hor_id integer,
    vertical_resolution character varying,
    vocab_res_vert_id integer,
    temporal_resolution character varying,
    vocab_res_time_id integer,
    dataset_version_min integer NOT NULL,
    dataset_version_max integer
);


ALTER TABLE data_resolution OWNER TO npdc;

--
-- Name: data_resolution_data_resolution_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE data_resolution_data_resolution_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE data_resolution_data_resolution_id_seq OWNER TO npdc;

--
-- Name: data_resolution_data_resolution_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE data_resolution_data_resolution_id_seq OWNED BY data_resolution.data_resolution_id;


--
-- Name: dataset; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE dataset (
    dataset_id integer NOT NULL,
    dataset_version integer NOT NULL,
    dif_id character varying,
    title character varying NOT NULL,
    summary character varying NOT NULL,
    date_start date,
    date_end date,
    quality character varying,
    access_constraints character varying,
    use_constraints character varying,
    dataset_progress character varying,
    originating_center integer,
    dif_revision_history character varying,
    version_description character varying,
    product_level_id character varying,
    collection_data_type character varying,
    extended_metadata character varying,
    record_status character varying NOT NULL,
    purpose character varying,
    insert_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    published timestamp without time zone
);


ALTER TABLE dataset OWNER TO npdc;

--
-- Name: dataset_citation; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE dataset_citation (
    dataset_citation_id integer NOT NULL,
    dataset_id integer NOT NULL,
    creator character varying,
    editor character varying,
    title character varying,
    series_name character varying,
    release_date date,
    release_place character varying,
    publisher character varying,
    version character varying,
    issue_identification character varying,
    presentation_form character varying,
    other character varying,
    persistent_identifier_type character varying,
    persistent_identifier_identifier character varying,
    online_resource character varying,
    dataset_version_min integer NOT NULL,
    dataset_version_max integer,
    type character varying
);


ALTER TABLE dataset_citation OWNER TO npdc;

--
-- Name: dataset_citation_dataset_citation_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE dataset_citation_dataset_citation_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dataset_citation_dataset_citation_id_seq OWNER TO npdc;

--
-- Name: dataset_citation_dataset_citation_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE dataset_citation_dataset_citation_id_seq OWNED BY dataset_citation.dataset_citation_id;


--
-- Name: dataset_dataset_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE dataset_dataset_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dataset_dataset_id_seq OWNER TO npdc;

--
-- Name: dataset_dataset_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE dataset_dataset_id_seq OWNED BY dataset.dataset_id;


--
-- Name: dataset_file; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE dataset_file (
    dataset_id integer NOT NULL,
    dataset_version_min integer NOT NULL,
    dataset_version_max integer,
    file_id integer NOT NULL
);


ALTER TABLE dataset_file OWNER TO npdc;

--
-- Name: dataset_keyword; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE dataset_keyword (
    dataset_keyword_id integer NOT NULL,
    dataset_id integer NOT NULL,
    vocab_science_keyword_id integer NOT NULL,
    detailed_variable character varying,
    dataset_version_min integer NOT NULL,
    dataset_version_max integer
);


ALTER TABLE dataset_keyword OWNER TO npdc;

--
-- Name: TABLE dataset_keyword; Type: COMMENT; Schema: public; Owner: npdc
--

COMMENT ON TABLE dataset_keyword IS 'met link naar vocab gaat de waarde in science_keywords, anders in keywords. Op database niveau lijkt me dit onderscheid niet relevant.';


--
-- Name: dataset_keyword_dataset_keyword_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE dataset_keyword_dataset_keyword_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dataset_keyword_dataset_keyword_id_seq OWNER TO npdc;

--
-- Name: dataset_keyword_dataset_keyword_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE dataset_keyword_dataset_keyword_id_seq OWNED BY dataset_keyword.dataset_keyword_id;


--
-- Name: dataset_link; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE dataset_link (
    dataset_link_id integer NOT NULL,
    dataset_id integer NOT NULL,
    title character varying NOT NULL,
    vocab_url_type_id integer NOT NULL,
    dataset_version_min integer NOT NULL,
    description character varying,
    mime_type_id integer,
    protocol character varying,
    dataset_version_max integer
);


ALTER TABLE dataset_link OWNER TO npdc;

--
-- Name: dataset_link_dataset_link_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE dataset_link_dataset_link_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dataset_link_dataset_link_id_seq OWNER TO npdc;

--
-- Name: dataset_link_dataset_link_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE dataset_link_dataset_link_id_seq OWNED BY dataset_link.dataset_link_id;


--
-- Name: dataset_link_url; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE dataset_link_url (
    dataset_link_url_id integer NOT NULL,
    dataset_link_id integer,
    dataset_version_min integer,
    dataset_version_max integer,
    url character varying
);


ALTER TABLE dataset_link_url OWNER TO npdc;

--
-- Name: dataset_link_url_dataset_link_url_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE dataset_link_url_dataset_link_url_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE dataset_link_url_dataset_link_url_id_seq OWNER TO npdc;

--
-- Name: dataset_link_url_dataset_link_url_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE dataset_link_url_dataset_link_url_id_seq OWNED BY dataset_link_url.dataset_link_url_id;


--
-- Name: dataset_person; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE dataset_person (
    dataset_id integer NOT NULL,
    dataset_version_min integer NOT NULL,
    person_id integer NOT NULL,
    editor boolean DEFAULT false NOT NULL,
    sort integer NOT NULL,
    dataset_version_max integer,
    role character varying[]
);


ALTER TABLE dataset_person OWNER TO npdc;

--
-- Name: TABLE dataset_person; Type: COMMENT; Schema: public; Owner: npdc
--

COMMENT ON TABLE dataset_person IS 'for use in Organization and Personnel';


--
-- Name: dataset_project; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE dataset_project (
    dataset_id integer NOT NULL,
    dataset_version_min integer NOT NULL,
    project_version_min integer NOT NULL,
    dataset_version_max integer,
    project_version_max integer,
    project_id integer
);


ALTER TABLE dataset_project OWNER TO npdc;

--
-- Name: dataset_publication; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE dataset_publication (
    publication_id integer NOT NULL,
    publication_version_min integer NOT NULL,
    dataset_id integer NOT NULL,
    dataset_version_min integer NOT NULL,
    publication_version_max integer,
    dataset_version_max integer
);


ALTER TABLE dataset_publication OWNER TO npdc;

--
-- Name: dataset_topic; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE dataset_topic (
    vocab_iso_topic_category_id integer NOT NULL,
    dataset_id integer NOT NULL,
    dataset_version_min integer NOT NULL,
    dataset_version_max integer
);


ALTER TABLE dataset_topic OWNER TO npdc;

--
-- Name: distribution; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE distribution (
    distribution_id integer NOT NULL,
    dataset_id integer NOT NULL,
    media character varying NOT NULL,
    size character varying NOT NULL,
    format character varying NOT NULL,
    fees character varying NOT NULL,
    dataset_version_min integer NOT NULL
);


ALTER TABLE distribution OWNER TO npdc;

--
-- Name: distribution_distribution_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE distribution_distribution_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE distribution_distribution_id_seq OWNER TO npdc;

--
-- Name: distribution_distribution_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE distribution_distribution_id_seq OWNED BY distribution.distribution_id;


--
-- Name: file; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE file (
    file_id integer NOT NULL,
    name character varying,
    location character varying,
    type character varying,
    size integer,
    default_access character varying DEFAULT 'private'::character varying NOT NULL,
    description character varying,
    insert_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    record_state character varying DEFAULT 'draft'::character varying NOT NULL,
    title character varying,
    form_id character varying NOT NULL
);


ALTER TABLE file OWNER TO npdc;

--
-- Name: file_file_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE file_file_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE file_file_id_seq OWNER TO npdc;

--
-- Name: file_file_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE file_file_id_seq OWNED BY file.file_id;


--
-- Name: idn_node; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE idn_node (
    short_name character varying NOT NULL
);


ALTER TABLE idn_node OWNER TO npdc;

--
-- Name: instrument; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE instrument (
    instrument_id integer NOT NULL,
    platform_id integer NOT NULL,
    vocab_instrument_id integer NOT NULL,
    number_of_sensors integer,
    operational_mode character varying,
    technique character varying,
    dataset_version_min integer,
    dataset_version_max integer
);


ALTER TABLE instrument OWNER TO npdc;

--
-- Name: instrument_instrument_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE instrument_instrument_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE instrument_instrument_id_seq OWNER TO npdc;

--
-- Name: instrument_instrument_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE instrument_instrument_id_seq OWNED BY instrument.instrument_id;


--
-- Name: location; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE location (
    location_id integer NOT NULL,
    vocab_location_id integer NOT NULL,
    detailed text,
    dataset_id integer NOT NULL,
    dataset_version_min integer NOT NULL,
    dataset_version_max integer
);


ALTER TABLE location OWNER TO npdc;

--
-- Name: location_location_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE location_location_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE location_location_id_seq OWNER TO npdc;

--
-- Name: location_location_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE location_location_id_seq OWNED BY location.location_id;


--
-- Name: location_node; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE location_node (
    vocab_location_id integer NOT NULL,
    short_name character varying NOT NULL
);


ALTER TABLE location_node OWNER TO npdc;

--
-- Name: menu; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE menu (
    menu_id integer NOT NULL,
    label character varying NOT NULL,
    url character varying,
    parent_menu_id integer,
    sort integer NOT NULL,
    min_user_level character varying NOT NULL
);


ALTER TABLE menu OWNER TO npdc;

--
-- Name: menu_menu_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE menu_menu_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE menu_menu_id_seq OWNER TO npdc;

--
-- Name: menu_menu_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE menu_menu_id_seq OWNED BY menu.menu_id;


--
-- Name: metadata_association; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE metadata_association (
    metadata_association_id integer NOT NULL,
    dataset_id integer NOT NULL,
    entry_id character varying NOT NULL,
    type character varying NOT NULL,
    description character varying,
    dataset_version_min integer NOT NULL
);


ALTER TABLE metadata_association OWNER TO npdc;

--
-- Name: metadata_association_metadata_association_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE metadata_association_metadata_association_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE metadata_association_metadata_association_id_seq OWNER TO npdc;

--
-- Name: metadata_association_metadata_association_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE metadata_association_metadata_association_id_seq OWNED BY metadata_association.metadata_association_id;


--
-- Name: mime_type; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE mime_type (
    mime_type_id integer NOT NULL,
    label character varying,
    type character varying,
    visible boolean DEFAULT true NOT NULL
);


ALTER TABLE mime_type OWNER TO npdc;

--
-- Name: mime_type_mime_type_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE mime_type_mime_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE mime_type_mime_type_id_seq OWNER TO npdc;

--
-- Name: mime_type_mime_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE mime_type_mime_type_id_seq OWNED BY mime_type.mime_type_id;


--
-- Name: multimedia_sample; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE multimedia_sample (
    multimedia_sample_id integer NOT NULL,
    dataset_id integer NOT NULL,
    file character varying,
    url character varying NOT NULL,
    format character varying,
    caption character varying,
    description character varying,
    dataset_version_min integer NOT NULL
);


ALTER TABLE multimedia_sample OWNER TO npdc;

--
-- Name: multimedia_sample_multimedia_sample_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE multimedia_sample_multimedia_sample_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE multimedia_sample_multimedia_sample_id_seq OWNER TO npdc;

--
-- Name: multimedia_sample_multimedia_sample_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE multimedia_sample_multimedia_sample_id_seq OWNED BY multimedia_sample.multimedia_sample_id;


--
-- Name: news; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE news (
    news_id integer NOT NULL,
    title character varying,
    content character varying,
    published timestamp without time zone DEFAULT now() NOT NULL,
    show_till timestamp without time zone,
    link character varying
);


ALTER TABLE news OWNER TO npdc;

--
-- Name: news_news_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE news_news_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE news_news_id_seq OWNER TO npdc;

--
-- Name: news_news_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE news_news_id_seq OWNED BY news.news_id;


--
-- Name: organization; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE organization (
    organization_id integer NOT NULL,
    organization_name character varying NOT NULL,
    organization_address character varying NOT NULL,
    organization_zip character varying NOT NULL,
    organization_city character varying NOT NULL,
    visiting_address character varying,
    edmo integer,
    dif_code character varying,
    dif_name character varying,
    website character varying,
    country_id character(2) DEFAULT 'NL'::bpchar NOT NULL
);


ALTER TABLE organization OWNER TO npdc;

--
-- Name: organization_organization_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE organization_organization_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE organization_organization_id_seq OWNER TO npdc;

--
-- Name: organization_organization_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE organization_organization_id_seq OWNED BY organization.organization_id;


--
-- Name: page; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE page (
    page_id integer NOT NULL,
    title character varying NOT NULL,
    content character varying NOT NULL,
    url character varying NOT NULL
);


ALTER TABLE page OWNER TO npdc;

--
-- Name: page_link; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE page_link (
    page_link_id integer NOT NULL,
    page_id integer NOT NULL,
    url character varying NOT NULL,
    text character varying NOT NULL,
    sort integer NOT NULL
);


ALTER TABLE page_link OWNER TO npdc;

--
-- Name: page_link_page_link_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE page_link_page_link_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE page_link_page_link_id_seq OWNER TO npdc;

--
-- Name: page_link_page_link_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE page_link_page_link_id_seq OWNED BY page_link.page_link_id;


--
-- Name: page_page_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE page_page_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE page_page_id_seq OWNER TO npdc;

--
-- Name: page_page_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE page_page_id_seq OWNED BY page.page_id;


--
-- Name: page_person; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE page_person (
    page_id integer NOT NULL,
    person_id integer NOT NULL,
    role character varying NOT NULL,
    editor boolean DEFAULT false NOT NULL,
    sort integer NOT NULL
);


ALTER TABLE page_person OWNER TO npdc;

--
-- Name: person; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE person (
    person_id integer NOT NULL,
    organization_id integer,
    name character varying NOT NULL,
    titles character varying,
    initials character varying,
    given_name character varying,
    surname character varying,
    mail character varying,
    phone_personal character varying,
    phone_secretariat character varying,
    phone_mobile character varying,
    address character varying,
    zip character varying,
    city character varying,
    sees_participant character varying,
    language character varying,
    password character varying,
    user_level character varying DEFAULT 'user'::character varying NOT NULL,
    orcid character(16),
    phone_personal_public character varying DEFAULT 'yes'::character varying,
    phone_secretariat_public character varying,
    phone_mobile_public character varying
);


ALTER TABLE person OWNER TO npdc;

--
-- Name: person_person_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE person_person_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE person_person_id_seq OWNER TO npdc;

--
-- Name: person_person_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE person_person_id_seq OWNED BY person.person_id;


--
-- Name: platform; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE platform (
    platform_id integer NOT NULL,
    dataset_id integer NOT NULL,
    vocab_platform_id integer NOT NULL,
    dataset_version_min integer NOT NULL,
    dataset_version_max integer
);


ALTER TABLE platform OWNER TO npdc;

--
-- Name: platform_platform_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE platform_platform_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE platform_platform_id_seq OWNER TO npdc;

--
-- Name: platform_platform_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE platform_platform_id_seq OWNED BY platform.platform_id;


--
-- Name: program; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE program (
    program_id integer NOT NULL,
    name character varying NOT NULL,
    program_start date NOT NULL,
    program_end date
);


ALTER TABLE program OWNER TO npdc;

--
-- Name: program_program_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE program_program_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE program_program_id_seq OWNER TO npdc;

--
-- Name: program_program_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE program_program_id_seq OWNED BY program.program_id;


--
-- Name: project; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE project (
    nwo_project_id character varying,
    project_version integer NOT NULL,
    title character varying NOT NULL,
    acronym character varying,
    region character varying NOT NULL,
    summary character varying NOT NULL,
    program_id integer NOT NULL,
    date_start date,
    date_end date,
    ris_id integer,
    proposal_status character varying,
    data_status character varying,
    research_type character varying,
    science_field character varying,
    data_type character varying,
    comments character varying,
    record_status character varying NOT NULL,
    insert_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    published timestamp without time zone,
    project_id integer NOT NULL
);


ALTER TABLE project OWNER TO npdc;

--
-- Name: project_keyword; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE project_keyword (
    project_keyword_id integer NOT NULL,
    keyword character varying NOT NULL,
    project_version_min integer NOT NULL,
    project_version_max integer,
    project_id integer
);


ALTER TABLE project_keyword OWNER TO npdc;

--
-- Name: project_keyword_project_keyword_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE project_keyword_project_keyword_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE project_keyword_project_keyword_id_seq OWNER TO npdc;

--
-- Name: project_keyword_project_keyword_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE project_keyword_project_keyword_id_seq OWNED BY project_keyword.project_keyword_id;


--
-- Name: project_link; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE project_link (
    project_link_id integer NOT NULL,
    url character varying NOT NULL,
    text character varying NOT NULL,
    project_version_min integer NOT NULL,
    project_version_max integer,
    project_id integer
);


ALTER TABLE project_link OWNER TO npdc;

--
-- Name: project_link_project_link_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE project_link_project_link_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE project_link_project_link_id_seq OWNER TO npdc;

--
-- Name: project_link_project_link_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE project_link_project_link_id_seq OWNED BY project_link.project_link_id;


--
-- Name: project_person; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE project_person (
    person_id integer NOT NULL,
    project_version_min integer NOT NULL,
    project_version_max integer,
    role character varying NOT NULL,
    sort integer NOT NULL,
    contact boolean DEFAULT true NOT NULL,
    editor boolean DEFAULT false NOT NULL,
    project_id integer
);


ALTER TABLE project_person OWNER TO npdc;

--
-- Name: project_project_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE project_project_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE project_project_id_seq OWNER TO npdc;

--
-- Name: project_project_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE project_project_id_seq OWNED BY project.project_id;


--
-- Name: project_publication; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE project_publication (
    publication_id integer NOT NULL,
    publication_version_min integer NOT NULL,
    project_version_min integer NOT NULL,
    publication_version_max integer,
    project_version_max integer,
    project_id integer
);


ALTER TABLE project_publication OWNER TO npdc;

--
-- Name: publication; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE publication (
    publication_id integer NOT NULL,
    publication_version integer NOT NULL,
    title character varying NOT NULL,
    abstract character varying NOT NULL,
    journal character varying NOT NULL,
    volume character varying NOT NULL,
    pages character varying NOT NULL,
    isbn character varying,
    doi character varying,
    record_status character varying NOT NULL,
    date date,
    url character varying,
    file_id integer,
    insert_timestamp timestamp without time zone DEFAULT now() NOT NULL,
    published timestamp without time zone
);


ALTER TABLE publication OWNER TO npdc;

--
-- Name: publication_keyword; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE publication_keyword (
    publication_keyword_id integer NOT NULL,
    publication_id integer NOT NULL,
    keyword character varying NOT NULL,
    keyword_id integer NOT NULL,
    publication_version_min integer NOT NULL,
    publication_version_max integer
);


ALTER TABLE publication_keyword OWNER TO npdc;

--
-- Name: publication_keyword_keyword_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE publication_keyword_keyword_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE publication_keyword_keyword_id_seq OWNER TO npdc;

--
-- Name: publication_keyword_keyword_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE publication_keyword_keyword_id_seq OWNED BY publication_keyword.keyword_id;


--
-- Name: publication_keyword_publication_keyword_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE publication_keyword_publication_keyword_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE publication_keyword_publication_keyword_id_seq OWNER TO npdc;

--
-- Name: publication_keyword_publication_keyword_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE publication_keyword_publication_keyword_id_seq OWNED BY publication_keyword.publication_keyword_id;


--
-- Name: publication_person; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE publication_person (
    publication_id integer NOT NULL,
    publication_version_min integer NOT NULL,
    person_id integer NOT NULL,
    sort integer NOT NULL,
    contact boolean DEFAULT false NOT NULL,
    publication_version_max integer,
    editor boolean DEFAULT false NOT NULL
);


ALTER TABLE publication_person OWNER TO npdc;

--
-- Name: publication_publication_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE publication_publication_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE publication_publication_id_seq OWNER TO npdc;

--
-- Name: publication_publication_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE publication_publication_id_seq OWNED BY publication.publication_id;


--
-- Name: record_status; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE record_status (
    record_status character varying NOT NULL,
    editable boolean NOT NULL,
    visible boolean NOT NULL
);


ALTER TABLE record_status OWNER TO npdc;

--
-- Name: record_status_change; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE record_status_change (
    project_id integer,
    dataset_id integer,
    publication_id integer,
    old_state character varying NOT NULL,
    new_state character varying NOT NULL,
    person_id integer NOT NULL,
    datetime timestamp without time zone DEFAULT now() NOT NULL,
    comment character varying,
    version integer,
    record_status_change_id integer NOT NULL
);


ALTER TABLE record_status_change OWNER TO npdc;

--
-- Name: record_status_change_record_status_change_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE record_status_change_record_status_change_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE record_status_change_record_status_change_id_seq OWNER TO npdc;

--
-- Name: record_status_change_record_status_change_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE record_status_change_record_status_change_id_seq OWNED BY record_status_change.record_status_change_id;


--
-- Name: sensor; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE sensor (
    sensor_id integer NOT NULL,
    instrument_id integer NOT NULL,
    dataset_version_min integer,
    dataset_version_max integer,
    vocab_instrument_id integer,
    technique character varying
);


ALTER TABLE sensor OWNER TO npdc;

--
-- Name: sensor_sensor_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE sensor_sensor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE sensor_sensor_id_seq OWNER TO npdc;

--
-- Name: sensor_sensor_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE sensor_sensor_id_seq OWNED BY sensor.sensor_id;


--
-- Name: spatial_coverage; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE spatial_coverage (
    spatial_coverage_id integer NOT NULL,
    dataset_id integer NOT NULL,
    dataset_version_min integer NOT NULL,
    dataset_version_max integer,
    wkt character varying,
    depth_min double precision,
    depth_max double precision,
    depth_unit character varying,
    altitude_min double precision,
    altitude_max double precision,
    altitude_unit character varying,
    type character varying,
    geom geometry
);


ALTER TABLE spatial_coverage OWNER TO npdc;

--
-- Name: COLUMN spatial_coverage.depth_min; Type: COMMENT; Schema: public; Owner: npdc
--

COMMENT ON COLUMN spatial_coverage.depth_min IS '
';


--
-- Name: spatial_coverage_spatial_coverage_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE spatial_coverage_spatial_coverage_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE spatial_coverage_spatial_coverage_id_seq OWNER TO npdc;

--
-- Name: spatial_coverage_spatial_coverage_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE spatial_coverage_spatial_coverage_id_seq OWNED BY spatial_coverage.spatial_coverage_id;


--
-- Name: temporal_coverage; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE temporal_coverage (
    temporal_coverage_id integer NOT NULL,
    dataset_id integer NOT NULL,
    dataset_version_min integer NOT NULL,
    dataset_version_max integer
);


ALTER TABLE temporal_coverage OWNER TO npdc;

--
-- Name: temporal_coverage_ancillary; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE temporal_coverage_ancillary (
    temporal_coverage_ancillary_id integer NOT NULL,
    temporal_coverage_id integer,
    dataset_version_min integer,
    dataset_version_max integer,
    keyword character varying
);


ALTER TABLE temporal_coverage_ancillary OWNER TO npdc;

--
-- Name: temporal_coverage_ancillary_temporal_coverage_ancillary_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE temporal_coverage_ancillary_temporal_coverage_ancillary_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE temporal_coverage_ancillary_temporal_coverage_ancillary_id_seq OWNER TO npdc;

--
-- Name: temporal_coverage_ancillary_temporal_coverage_ancillary_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE temporal_coverage_ancillary_temporal_coverage_ancillary_id_seq OWNED BY temporal_coverage_ancillary.temporal_coverage_ancillary_id;


--
-- Name: temporal_coverage_cycle; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE temporal_coverage_cycle (
    temporal_coverage_cycle_id integer NOT NULL,
    temporal_coverage_id integer,
    dataset_version_min integer,
    dataset_version_max integer,
    name character varying,
    date_start date,
    date_end date,
    sampling_frequency double precision,
    sampling_frequency_unit character varying
);


ALTER TABLE temporal_coverage_cycle OWNER TO npdc;

--
-- Name: temporal_coverage_cycle_temporal_coverage_cycle_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE temporal_coverage_cycle_temporal_coverage_cycle_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE temporal_coverage_cycle_temporal_coverage_cycle_id_seq OWNER TO npdc;

--
-- Name: temporal_coverage_cycle_temporal_coverage_cycle_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE temporal_coverage_cycle_temporal_coverage_cycle_id_seq OWNED BY temporal_coverage_cycle.temporal_coverage_cycle_id;


--
-- Name: temporal_coverage_paleo; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE temporal_coverage_paleo (
    temporal_coverage_paleo_id integer NOT NULL,
    temporal_coverage_id integer,
    dataset_version_min integer,
    dataset_version_max integer,
    start_value double precision,
    start_unit character varying,
    end_value double precision,
    end_unit character varying,
    vocab_chronounit_id integer
);


ALTER TABLE temporal_coverage_paleo OWNER TO npdc;

--
-- Name: temporal_coverage_paleo_temporal_coverage_paleo_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE temporal_coverage_paleo_temporal_coverage_paleo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE temporal_coverage_paleo_temporal_coverage_paleo_id_seq OWNER TO npdc;

--
-- Name: temporal_coverage_paleo_temporal_coverage_paleo_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE temporal_coverage_paleo_temporal_coverage_paleo_id_seq OWNED BY temporal_coverage_paleo.temporal_coverage_paleo_id;


--
-- Name: temporal_coverage_period; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE temporal_coverage_period (
    temporal_coverage_period_id integer NOT NULL,
    temporal_coverage_id integer,
    dataset_version_min integer,
    dataset_version_max integer,
    date_start date,
    date_end date
);


ALTER TABLE temporal_coverage_period OWNER TO npdc;

--
-- Name: temporal_coverage_period_temporal_coverage_period_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE temporal_coverage_period_temporal_coverage_period_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE temporal_coverage_period_temporal_coverage_period_id_seq OWNER TO npdc;

--
-- Name: temporal_coverage_period_temporal_coverage_period_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE temporal_coverage_period_temporal_coverage_period_id_seq OWNED BY temporal_coverage_period.temporal_coverage_period_id;


--
-- Name: temporal_coverage_temporal_coverage_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE temporal_coverage_temporal_coverage_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE temporal_coverage_temporal_coverage_id_seq OWNER TO npdc;

--
-- Name: temporal_coverage_temporal_coverage_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE temporal_coverage_temporal_coverage_id_seq OWNED BY temporal_coverage.temporal_coverage_id;


--
-- Name: user_level; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE user_level (
    user_level_id integer NOT NULL,
    label character varying NOT NULL,
    description character varying,
    name character varying
);


ALTER TABLE user_level OWNER TO npdc;

--
-- Name: user_level_user_level_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE user_level_user_level_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE user_level_user_level_id_seq OWNER TO npdc;

--
-- Name: user_level_user_level_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE user_level_user_level_id_seq OWNED BY user_level.user_level_id;


--
-- Name: vocab; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE vocab (
    vocab_id integer NOT NULL,
    vocab_name character varying,
    last_update_date date,
    last_update_local date,
    sync boolean DEFAULT false NOT NULL
);


ALTER TABLE vocab OWNER TO npdc;

--
-- Name: vocab_chronounit; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE vocab_chronounit (
    vocab_chronounit_id integer NOT NULL,
    eon character varying,
    era character varying,
    period character varying,
    epoch character varying,
    stage character varying,
    uuid uuid,
    visible boolean DEFAULT true NOT NULL
);


ALTER TABLE vocab_chronounit OWNER TO npdc;

--
-- Name: vocab_chronounit_vocab_chronounit_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE vocab_chronounit_vocab_chronounit_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vocab_chronounit_vocab_chronounit_id_seq OWNER TO npdc;

--
-- Name: vocab_chronounit_vocab_chronounit_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE vocab_chronounit_vocab_chronounit_id_seq OWNED BY vocab_chronounit.vocab_chronounit_id;


--
-- Name: vocab_instrument; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE vocab_instrument (
    vocab_instrument_id integer NOT NULL,
    category character varying NOT NULL,
    class character varying,
    type character varying,
    subtype character varying,
    short_name character varying,
    long_name character varying,
    uuid uuid,
    visible boolean DEFAULT true NOT NULL
);


ALTER TABLE vocab_instrument OWNER TO npdc;

--
-- Name: vocab_instrument_vocab_instrument_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE vocab_instrument_vocab_instrument_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vocab_instrument_vocab_instrument_id_seq OWNER TO npdc;

--
-- Name: vocab_instrument_vocab_instrument_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE vocab_instrument_vocab_instrument_id_seq OWNED BY vocab_instrument.vocab_instrument_id;


--
-- Name: vocab_iso_topic_category; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE vocab_iso_topic_category (
    vocab_iso_topic_category_id integer NOT NULL,
    topic character varying NOT NULL,
    description character varying,
    visible boolean DEFAULT true NOT NULL
);


ALTER TABLE vocab_iso_topic_category OWNER TO npdc;

--
-- Name: vocab_iso_topic_category_vocab_iso_topic_category_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE vocab_iso_topic_category_vocab_iso_topic_category_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vocab_iso_topic_category_vocab_iso_topic_category_id_seq OWNER TO npdc;

--
-- Name: vocab_iso_topic_category_vocab_iso_topic_category_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE vocab_iso_topic_category_vocab_iso_topic_category_id_seq OWNED BY vocab_iso_topic_category.vocab_iso_topic_category_id;


--
-- Name: vocab_location; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE vocab_location (
    vocab_location_id integer NOT NULL,
    location_category character varying NOT NULL,
    location_type character varying,
    location_subregion1 character varying,
    location_subregion2 character varying,
    location_subregion3 character varying,
    uuid character varying NOT NULL,
    visible boolean DEFAULT true NOT NULL
);


ALTER TABLE vocab_location OWNER TO npdc;

--
-- Name: vocab_location_vocab_location_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE vocab_location_vocab_location_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vocab_location_vocab_location_id_seq OWNER TO npdc;

--
-- Name: vocab_location_vocab_location_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE vocab_location_vocab_location_id_seq OWNED BY vocab_location.vocab_location_id;


--
-- Name: vocab_platform; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE vocab_platform (
    vocab_platform_id integer NOT NULL,
    category character varying NOT NULL,
    series_entity character varying,
    short_name character varying,
    long_name character varying,
    uuid character varying NOT NULL,
    visible boolean DEFAULT true NOT NULL
);


ALTER TABLE vocab_platform OWNER TO npdc;

--
-- Name: vocab_platform_vocab_platform_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE vocab_platform_vocab_platform_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vocab_platform_vocab_platform_id_seq OWNER TO npdc;

--
-- Name: vocab_platform_vocab_platform_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE vocab_platform_vocab_platform_id_seq OWNED BY vocab_platform.vocab_platform_id;


--
-- Name: vocab_res_hor; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE vocab_res_hor (
    vocab_res_hor_id integer NOT NULL,
    range character varying NOT NULL,
    uuid uuid NOT NULL,
    sort integer,
    visible boolean DEFAULT true NOT NULL
);


ALTER TABLE vocab_res_hor OWNER TO npdc;

--
-- Name: TABLE vocab_res_hor; Type: COMMENT; Schema: public; Owner: npdc
--

COMMENT ON TABLE vocab_res_hor IS 'horizontal data resolution';


--
-- Name: vocab_res_hor_vocab_res_hor_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE vocab_res_hor_vocab_res_hor_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vocab_res_hor_vocab_res_hor_id_seq OWNER TO npdc;

--
-- Name: vocab_res_hor_vocab_res_hor_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE vocab_res_hor_vocab_res_hor_id_seq OWNED BY vocab_res_hor.vocab_res_hor_id;


--
-- Name: vocab_res_time; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE vocab_res_time (
    vocab_res_time_id integer NOT NULL,
    range character varying NOT NULL,
    uuid character varying NOT NULL,
    sort integer,
    visible boolean DEFAULT true NOT NULL
);


ALTER TABLE vocab_res_time OWNER TO npdc;

--
-- Name: TABLE vocab_res_time; Type: COMMENT; Schema: public; Owner: npdc
--

COMMENT ON TABLE vocab_res_time IS 'temporal data resolution';


--
-- Name: vocab_res_time_vocab_res_time_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE vocab_res_time_vocab_res_time_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vocab_res_time_vocab_res_time_id_seq OWNER TO npdc;

--
-- Name: vocab_res_time_vocab_res_time_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE vocab_res_time_vocab_res_time_id_seq OWNED BY vocab_res_time.vocab_res_time_id;


--
-- Name: vocab_res_vert; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE vocab_res_vert (
    vocab_res_vert_id integer NOT NULL,
    range character varying NOT NULL,
    uuid character varying NOT NULL,
    sort integer,
    visible boolean DEFAULT true NOT NULL
);


ALTER TABLE vocab_res_vert OWNER TO npdc;

--
-- Name: TABLE vocab_res_vert; Type: COMMENT; Schema: public; Owner: npdc
--

COMMENT ON TABLE vocab_res_vert IS 'vertical data resolution';


--
-- Name: vocab_res_vert_vocab_res_vert_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE vocab_res_vert_vocab_res_vert_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vocab_res_vert_vocab_res_vert_id_seq OWNER TO npdc;

--
-- Name: vocab_res_vert_vocab_res_vert_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE vocab_res_vert_vocab_res_vert_id_seq OWNED BY vocab_res_vert.vocab_res_vert_id;


--
-- Name: vocab_science_keyword; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE vocab_science_keyword (
    vocab_science_keyword_id integer NOT NULL,
    category character varying NOT NULL,
    topic character varying,
    term character varying,
    var_lvl_1 character varying,
    var_lvl_2 character varying,
    var_lvl_3 character varying,
    uuid character varying NOT NULL,
    detailed_variable character varying,
    visible boolean DEFAULT true NOT NULL
);


ALTER TABLE vocab_science_keyword OWNER TO npdc;

--
-- Name: vocab_science_keyword_vocab_science_keyword_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE vocab_science_keyword_vocab_science_keyword_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vocab_science_keyword_vocab_science_keyword_id_seq OWNER TO npdc;

--
-- Name: vocab_science_keyword_vocab_science_keyword_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE vocab_science_keyword_vocab_science_keyword_id_seq OWNED BY vocab_science_keyword.vocab_science_keyword_id;


--
-- Name: vocab_url_type; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE vocab_url_type (
    vocab_url_type_id integer NOT NULL,
    type character varying NOT NULL,
    subtype character varying,
    uuid character varying NOT NULL,
    visible boolean DEFAULT true NOT NULL
);


ALTER TABLE vocab_url_type OWNER TO npdc;

--
-- Name: vocab_url_type_vocab_url_type_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE vocab_url_type_vocab_url_type_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE vocab_url_type_vocab_url_type_id_seq OWNER TO npdc;

--
-- Name: vocab_url_type_vocab_url_type_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE vocab_url_type_vocab_url_type_id_seq OWNED BY vocab_url_type.vocab_url_type_id;


--
-- Name: zip; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE zip (
    zip_id integer NOT NULL,
    filename text NOT NULL,
    person_id integer,
    guest_user text,
    "timestamp" timestamp without time zone DEFAULT now() NOT NULL,
    dataset_id integer
);


ALTER TABLE zip OWNER TO npdc;

--
-- Name: zip_files; Type: TABLE; Schema: public; Owner: npdc
--

CREATE TABLE zip_files (
    zip_files_id integer NOT NULL,
    zip_id integer NOT NULL,
    file_id integer NOT NULL
);


ALTER TABLE zip_files OWNER TO npdc;

--
-- Name: zip_files_zip_files_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE zip_files_zip_files_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE zip_files_zip_files_id_seq OWNER TO npdc;

--
-- Name: zip_files_zip_files_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE zip_files_zip_files_id_seq OWNED BY zip_files.zip_files_id;


--
-- Name: zip_zip_id_seq; Type: SEQUENCE; Schema: public; Owner: npdc
--

CREATE SEQUENCE zip_zip_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER TABLE zip_zip_id_seq OWNER TO npdc;

--
-- Name: zip_zip_id_seq; Type: SEQUENCE OWNED BY; Schema: public; Owner: npdc
--

ALTER SEQUENCE zip_zip_id_seq OWNED BY zip.zip_id;


--
-- Name: access_request access_request_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY access_request ALTER COLUMN access_request_id SET DEFAULT nextval('access_request_access_request_id_seq'::regclass);


--
-- Name: access_request_file access_request_file_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY access_request_file ALTER COLUMN access_request_file_id SET DEFAULT nextval('access_request_file_access_request_file_id_seq'::regclass);


--
-- Name: account_new account_new_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY account_new ALTER COLUMN account_new_id SET DEFAULT nextval('account_new_account_new_id_seq'::regclass);


--
-- Name: account_reset account_reset_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY account_reset ALTER COLUMN account_reset_id SET DEFAULT nextval('account_reset_account_reset_id_seq'::regclass);


--
-- Name: additional_attributes additional_attributes_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY additional_attributes ALTER COLUMN additional_attributes_id SET DEFAULT nextval('additional_attributes_additional_attributes_id_seq'::regclass);


--
-- Name: characteristics characteristics_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY characteristics ALTER COLUMN characteristics_id SET DEFAULT nextval('characteristics_characteristics_id_seq'::regclass);


--
-- Name: data_resolution data_resolution_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY data_resolution ALTER COLUMN data_resolution_id SET DEFAULT nextval('data_resolution_data_resolution_id_seq'::regclass);


--
-- Name: dataset dataset_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset ALTER COLUMN dataset_id SET DEFAULT nextval('dataset_dataset_id_seq'::regclass);


--
-- Name: dataset_citation dataset_citation_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_citation ALTER COLUMN dataset_citation_id SET DEFAULT nextval('dataset_citation_dataset_citation_id_seq'::regclass);


--
-- Name: dataset_keyword dataset_keyword_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_keyword ALTER COLUMN dataset_keyword_id SET DEFAULT nextval('dataset_keyword_dataset_keyword_id_seq'::regclass);


--
-- Name: dataset_link dataset_link_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_link ALTER COLUMN dataset_link_id SET DEFAULT nextval('dataset_link_dataset_link_id_seq'::regclass);


--
-- Name: dataset_link_url dataset_link_url_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_link_url ALTER COLUMN dataset_link_url_id SET DEFAULT nextval('dataset_link_url_dataset_link_url_id_seq'::regclass);


--
-- Name: distribution distribution_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY distribution ALTER COLUMN distribution_id SET DEFAULT nextval('distribution_distribution_id_seq'::regclass);


--
-- Name: file file_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY file ALTER COLUMN file_id SET DEFAULT nextval('file_file_id_seq'::regclass);


--
-- Name: instrument instrument_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY instrument ALTER COLUMN instrument_id SET DEFAULT nextval('instrument_instrument_id_seq'::regclass);


--
-- Name: location location_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY location ALTER COLUMN location_id SET DEFAULT nextval('location_location_id_seq'::regclass);


--
-- Name: menu menu_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY menu ALTER COLUMN menu_id SET DEFAULT nextval('menu_menu_id_seq'::regclass);


--
-- Name: metadata_association metadata_association_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY metadata_association ALTER COLUMN metadata_association_id SET DEFAULT nextval('metadata_association_metadata_association_id_seq'::regclass);


--
-- Name: mime_type mime_type_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY mime_type ALTER COLUMN mime_type_id SET DEFAULT nextval('mime_type_mime_type_id_seq'::regclass);


--
-- Name: multimedia_sample multimedia_sample_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY multimedia_sample ALTER COLUMN multimedia_sample_id SET DEFAULT nextval('multimedia_sample_multimedia_sample_id_seq'::regclass);


--
-- Name: news news_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY news ALTER COLUMN news_id SET DEFAULT nextval('news_news_id_seq'::regclass);


--
-- Name: organization organization_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY organization ALTER COLUMN organization_id SET DEFAULT nextval('organization_organization_id_seq'::regclass);


--
-- Name: page page_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY page ALTER COLUMN page_id SET DEFAULT nextval('page_page_id_seq'::regclass);


--
-- Name: page_link page_link_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY page_link ALTER COLUMN page_link_id SET DEFAULT nextval('page_link_page_link_id_seq'::regclass);


--
-- Name: person person_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY person ALTER COLUMN person_id SET DEFAULT nextval('person_person_id_seq'::regclass);


--
-- Name: platform platform_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY platform ALTER COLUMN platform_id SET DEFAULT nextval('platform_platform_id_seq'::regclass);


--
-- Name: program program_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY program ALTER COLUMN program_id SET DEFAULT nextval('program_program_id_seq'::regclass);


--
-- Name: project project_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY project ALTER COLUMN project_id SET DEFAULT nextval('project_project_id_seq'::regclass);


--
-- Name: project_keyword project_keyword_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY project_keyword ALTER COLUMN project_keyword_id SET DEFAULT nextval('project_keyword_project_keyword_id_seq'::regclass);


--
-- Name: project_link project_link_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY project_link ALTER COLUMN project_link_id SET DEFAULT nextval('project_link_project_link_id_seq'::regclass);


--
-- Name: publication publication_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY publication ALTER COLUMN publication_id SET DEFAULT nextval('publication_publication_id_seq'::regclass);


--
-- Name: publication_keyword publication_keyword_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY publication_keyword ALTER COLUMN publication_keyword_id SET DEFAULT nextval('publication_keyword_publication_keyword_id_seq'::regclass);


--
-- Name: publication_keyword keyword_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY publication_keyword ALTER COLUMN keyword_id SET DEFAULT nextval('publication_keyword_keyword_id_seq'::regclass);


--
-- Name: record_status_change record_status_change_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY record_status_change ALTER COLUMN record_status_change_id SET DEFAULT nextval('record_status_change_record_status_change_id_seq'::regclass);


--
-- Name: sensor sensor_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY sensor ALTER COLUMN sensor_id SET DEFAULT nextval('sensor_sensor_id_seq'::regclass);


--
-- Name: spatial_coverage spatial_coverage_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY spatial_coverage ALTER COLUMN spatial_coverage_id SET DEFAULT nextval('spatial_coverage_spatial_coverage_id_seq'::regclass);


--
-- Name: temporal_coverage temporal_coverage_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage ALTER COLUMN temporal_coverage_id SET DEFAULT nextval('temporal_coverage_temporal_coverage_id_seq'::regclass);


--
-- Name: temporal_coverage_ancillary temporal_coverage_ancillary_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage_ancillary ALTER COLUMN temporal_coverage_ancillary_id SET DEFAULT nextval('temporal_coverage_ancillary_temporal_coverage_ancillary_id_seq'::regclass);


--
-- Name: temporal_coverage_cycle temporal_coverage_cycle_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage_cycle ALTER COLUMN temporal_coverage_cycle_id SET DEFAULT nextval('temporal_coverage_cycle_temporal_coverage_cycle_id_seq'::regclass);


--
-- Name: temporal_coverage_paleo temporal_coverage_paleo_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage_paleo ALTER COLUMN temporal_coverage_paleo_id SET DEFAULT nextval('temporal_coverage_paleo_temporal_coverage_paleo_id_seq'::regclass);


--
-- Name: temporal_coverage_period temporal_coverage_period_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage_period ALTER COLUMN temporal_coverage_period_id SET DEFAULT nextval('temporal_coverage_period_temporal_coverage_period_id_seq'::regclass);


--
-- Name: user_level user_level_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY user_level ALTER COLUMN user_level_id SET DEFAULT nextval('user_level_user_level_id_seq'::regclass);


--
-- Name: vocab_chronounit vocab_chronounit_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_chronounit ALTER COLUMN vocab_chronounit_id SET DEFAULT nextval('vocab_chronounit_vocab_chronounit_id_seq'::regclass);


--
-- Name: vocab_instrument vocab_instrument_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_instrument ALTER COLUMN vocab_instrument_id SET DEFAULT nextval('vocab_instrument_vocab_instrument_id_seq'::regclass);


--
-- Name: vocab_iso_topic_category vocab_iso_topic_category_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_iso_topic_category ALTER COLUMN vocab_iso_topic_category_id SET DEFAULT nextval('vocab_iso_topic_category_vocab_iso_topic_category_id_seq'::regclass);


--
-- Name: vocab_location vocab_location_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_location ALTER COLUMN vocab_location_id SET DEFAULT nextval('vocab_location_vocab_location_id_seq'::regclass);


--
-- Name: vocab_platform vocab_platform_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_platform ALTER COLUMN vocab_platform_id SET DEFAULT nextval('vocab_platform_vocab_platform_id_seq'::regclass);


--
-- Name: vocab_res_hor vocab_res_hor_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_res_hor ALTER COLUMN vocab_res_hor_id SET DEFAULT nextval('vocab_res_hor_vocab_res_hor_id_seq'::regclass);


--
-- Name: vocab_res_time vocab_res_time_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_res_time ALTER COLUMN vocab_res_time_id SET DEFAULT nextval('vocab_res_time_vocab_res_time_id_seq'::regclass);


--
-- Name: vocab_res_vert vocab_res_vert_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_res_vert ALTER COLUMN vocab_res_vert_id SET DEFAULT nextval('vocab_res_vert_vocab_res_vert_id_seq'::regclass);


--
-- Name: vocab_science_keyword vocab_science_keyword_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_science_keyword ALTER COLUMN vocab_science_keyword_id SET DEFAULT nextval('vocab_science_keyword_vocab_science_keyword_id_seq'::regclass);


--
-- Name: vocab_url_type vocab_url_type_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_url_type ALTER COLUMN vocab_url_type_id SET DEFAULT nextval('vocab_url_type_vocab_url_type_id_seq'::regclass);


--
-- Name: zip zip_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY zip ALTER COLUMN zip_id SET DEFAULT nextval('zip_zip_id_seq'::regclass);


--
-- Name: zip_files zip_files_id; Type: DEFAULT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY zip_files ALTER COLUMN zip_files_id SET DEFAULT nextval('zip_files_zip_files_id_seq'::regclass);


--
-- Name: access_request_file access_request_file_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY access_request_file
    ADD CONSTRAINT access_request_file_pk PRIMARY KEY (access_request_file_id);


--
-- Name: access_request access_request_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY access_request
    ADD CONSTRAINT access_request_pk PRIMARY KEY (access_request_id);


--
-- Name: account_new account_new_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY account_new
    ADD CONSTRAINT account_new_pk PRIMARY KEY (account_new_id);


--
-- Name: account_reset account_reset_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY account_reset
    ADD CONSTRAINT account_reset_pk PRIMARY KEY (account_reset_id);


--
-- Name: additional_attributes additional_attributes_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY additional_attributes
    ADD CONSTRAINT additional_attributes_pk PRIMARY KEY (additional_attributes_id);


--
-- Name: characteristics characteristics_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY characteristics
    ADD CONSTRAINT characteristics_pk PRIMARY KEY (characteristics_id);


--
-- Name: continent continent_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY continent
    ADD CONSTRAINT continent_pk PRIMARY KEY (continent_id);


--
-- Name: country country_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY country
    ADD CONSTRAINT country_pk PRIMARY KEY (country_id);


--
-- Name: data_resolution data_resolution_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY data_resolution
    ADD CONSTRAINT data_resolution_pk PRIMARY KEY (data_resolution_id);


--
-- Name: dataset_citation dataset_citation_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_citation
    ADD CONSTRAINT dataset_citation_pk PRIMARY KEY (dataset_citation_id);


--
-- Name: dataset_file dataset_file_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_file
    ADD CONSTRAINT dataset_file_pk PRIMARY KEY (dataset_id, dataset_version_min, file_id);


--
-- Name: dataset_keyword dataset_keyword_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_keyword
    ADD CONSTRAINT dataset_keyword_pk PRIMARY KEY (dataset_keyword_id);


--
-- Name: dataset_link dataset_link_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_link
    ADD CONSTRAINT dataset_link_pk PRIMARY KEY (dataset_link_id);


--
-- Name: dataset_link_url dataset_link_url_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_link_url
    ADD CONSTRAINT dataset_link_url_pk PRIMARY KEY (dataset_link_url_id);


--
-- Name: dataset_person dataset_person_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_person
    ADD CONSTRAINT dataset_person_pk PRIMARY KEY (dataset_id, dataset_version_min, person_id);


--
-- Name: dataset dataset_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset
    ADD CONSTRAINT dataset_pk PRIMARY KEY (dataset_id, dataset_version);


--
-- Name: dataset_publication dataset_publication_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_publication
    ADD CONSTRAINT dataset_publication_pk PRIMARY KEY (publication_id, publication_version_min, dataset_id, dataset_version_min);


--
-- Name: dataset_topic dataset_topic_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_topic
    ADD CONSTRAINT dataset_topic_pk PRIMARY KEY (vocab_iso_topic_category_id, dataset_id, dataset_version_min);


--
-- Name: distribution distribution_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY distribution
    ADD CONSTRAINT distribution_pk PRIMARY KEY (distribution_id);


--
-- Name: file file_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY file
    ADD CONSTRAINT file_pk PRIMARY KEY (file_id);


--
-- Name: idn_node idn_node_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY idn_node
    ADD CONSTRAINT idn_node_pk PRIMARY KEY (short_name);


--
-- Name: instrument instrument_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY instrument
    ADD CONSTRAINT instrument_pk PRIMARY KEY (instrument_id);


--
-- Name: location_node location_node_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY location_node
    ADD CONSTRAINT location_node_pk PRIMARY KEY (vocab_location_id, short_name);


--
-- Name: location location_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY location
    ADD CONSTRAINT location_pk PRIMARY KEY (location_id);


--
-- Name: menu menu_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY menu
    ADD CONSTRAINT menu_pk PRIMARY KEY (menu_id);


--
-- Name: metadata_association metadata_association_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY metadata_association
    ADD CONSTRAINT metadata_association_pk PRIMARY KEY (metadata_association_id);


--
-- Name: mime_type mime_type_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY mime_type
    ADD CONSTRAINT mime_type_pk PRIMARY KEY (mime_type_id);


--
-- Name: multimedia_sample multimedia_sample_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY multimedia_sample
    ADD CONSTRAINT multimedia_sample_pk PRIMARY KEY (multimedia_sample_id);


--
-- Name: news news_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY news
    ADD CONSTRAINT news_pk PRIMARY KEY (news_id);


--
-- Name: organization organization_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY organization
    ADD CONSTRAINT organization_pk PRIMARY KEY (organization_id);


--
-- Name: page_link page_link_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY page_link
    ADD CONSTRAINT page_link_pk PRIMARY KEY (page_link_id);


--
-- Name: page_person page_person_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY page_person
    ADD CONSTRAINT page_person_pk PRIMARY KEY (page_id, person_id);


--
-- Name: page page_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY page
    ADD CONSTRAINT page_pk PRIMARY KEY (page_id);


--
-- Name: person person_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY person
    ADD CONSTRAINT person_pk PRIMARY KEY (person_id);


--
-- Name: platform platform_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY platform
    ADD CONSTRAINT platform_pk PRIMARY KEY (platform_id);


--
-- Name: program program_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY program
    ADD CONSTRAINT program_pk PRIMARY KEY (program_id);


--
-- Name: project_keyword project_keyword_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY project_keyword
    ADD CONSTRAINT project_keyword_pk PRIMARY KEY (project_keyword_id);


--
-- Name: project_link project_link_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY project_link
    ADD CONSTRAINT project_link_pk PRIMARY KEY (project_link_id);


--
-- Name: project project_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY project
    ADD CONSTRAINT project_pk PRIMARY KEY (project_id, project_version);


--
-- Name: publication_keyword publication_keyword_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY publication_keyword
    ADD CONSTRAINT publication_keyword_pk PRIMARY KEY (publication_keyword_id);


--
-- Name: publication_person publication_person_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY publication_person
    ADD CONSTRAINT publication_person_pk PRIMARY KEY (publication_id, publication_version_min, person_id);


--
-- Name: publication publication_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY publication
    ADD CONSTRAINT publication_pk PRIMARY KEY (publication_id, publication_version);


--
-- Name: record_status_change record_status_change_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY record_status_change
    ADD CONSTRAINT record_status_change_pk PRIMARY KEY (record_status_change_id);


--
-- Name: record_status record_status_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY record_status
    ADD CONSTRAINT record_status_pk PRIMARY KEY (record_status);


--
-- Name: sensor sensor_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY sensor
    ADD CONSTRAINT sensor_pk PRIMARY KEY (sensor_id);


--
-- Name: spatial_coverage spatial_coverage_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY spatial_coverage
    ADD CONSTRAINT spatial_coverage_pk PRIMARY KEY (spatial_coverage_id);


--
-- Name: temporal_coverage_ancillary temporal_coverage_ancillary_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage_ancillary
    ADD CONSTRAINT temporal_coverage_ancillary_pk PRIMARY KEY (temporal_coverage_ancillary_id);


--
-- Name: temporal_coverage_cycle temporal_coverage_cycle_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage_cycle
    ADD CONSTRAINT temporal_coverage_cycle_pk PRIMARY KEY (temporal_coverage_cycle_id);


--
-- Name: temporal_coverage_paleo temporal_coverage_paleo_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage_paleo
    ADD CONSTRAINT temporal_coverage_paleo_pk PRIMARY KEY (temporal_coverage_paleo_id);


--
-- Name: temporal_coverage_period temporal_coverage_period_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage_period
    ADD CONSTRAINT temporal_coverage_period_pk PRIMARY KEY (temporal_coverage_period_id);


--
-- Name: temporal_coverage temporal_coverage_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage
    ADD CONSTRAINT temporal_coverage_pk PRIMARY KEY (temporal_coverage_id);


--
-- Name: user_level user_level_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY user_level
    ADD CONSTRAINT user_level_pk PRIMARY KEY (user_level_id);


--
-- Name: vocab_chronounit vocab_chronounit_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_chronounit
    ADD CONSTRAINT vocab_chronounit_pk PRIMARY KEY (vocab_chronounit_id);


--
-- Name: vocab_instrument vocab_instrument_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_instrument
    ADD CONSTRAINT vocab_instrument_pk PRIMARY KEY (vocab_instrument_id);


--
-- Name: vocab_iso_topic_category vocab_iso_topic_category_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_iso_topic_category
    ADD CONSTRAINT vocab_iso_topic_category_pk PRIMARY KEY (vocab_iso_topic_category_id);


--
-- Name: vocab_location vocab_location_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_location
    ADD CONSTRAINT vocab_location_pk PRIMARY KEY (vocab_location_id);


--
-- Name: vocab vocab_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab
    ADD CONSTRAINT vocab_pk PRIMARY KEY (vocab_id);


--
-- Name: vocab_platform vocab_platform_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_platform
    ADD CONSTRAINT vocab_platform_pk PRIMARY KEY (vocab_platform_id);


--
-- Name: vocab_res_hor vocab_res_hor_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_res_hor
    ADD CONSTRAINT vocab_res_hor_pk PRIMARY KEY (vocab_res_hor_id);


--
-- Name: vocab_res_time vocab_res_time_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_res_time
    ADD CONSTRAINT vocab_res_time_pk PRIMARY KEY (vocab_res_time_id);


--
-- Name: vocab_res_vert vocab_res_vert_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_res_vert
    ADD CONSTRAINT vocab_res_vert_pk PRIMARY KEY (vocab_res_vert_id);


--
-- Name: vocab_science_keyword vocab_science_keyword_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_science_keyword
    ADD CONSTRAINT vocab_science_keyword_pk PRIMARY KEY (vocab_science_keyword_id);


--
-- Name: vocab_url_type vocab_url_type_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY vocab_url_type
    ADD CONSTRAINT vocab_url_type_pk PRIMARY KEY (vocab_url_type_id);


--
-- Name: zip_files zip_files_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY zip_files
    ADD CONSTRAINT zip_files_pk PRIMARY KEY (zip_files_id);


--
-- Name: zip zip_pk; Type: CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY zip
    ADD CONSTRAINT zip_pk PRIMARY KEY (zip_id);


--
-- Name: fki_access_zip; Type: INDEX; Schema: public; Owner: npdc
--

CREATE INDEX fki_access_zip ON access_request USING btree (zip_id);


--
-- Name: fki_file_id; Type: INDEX; Schema: public; Owner: npdc
--

CREATE INDEX fki_file_id ON publication USING btree (file_id);


--
-- Name: fki_instrument; Type: INDEX; Schema: public; Owner: npdc
--

CREATE INDEX fki_instrument ON sensor USING btree (vocab_instrument_id);


--
-- Name: fki_link; Type: INDEX; Schema: public; Owner: npdc
--

CREATE INDEX fki_link ON dataset_link_url USING btree (dataset_link_id);


--
-- Name: fki_location_dataset; Type: INDEX; Schema: public; Owner: npdc
--

CREATE INDEX fki_location_dataset ON location USING btree (dataset_id, dataset_version_min);


--
-- Name: fki_location_vocab; Type: INDEX; Schema: public; Owner: npdc
--

CREATE INDEX fki_location_vocab ON location USING btree (vocab_location_id);


--
-- Name: fki_mime; Type: INDEX; Schema: public; Owner: npdc
--

CREATE INDEX fki_mime ON dataset_link USING btree (mime_type_id);


--
-- Name: fki_organization_countryu; Type: INDEX; Schema: public; Owner: npdc
--

CREATE INDEX fki_organization_countryu ON organization USING btree (country_id);


--
-- Name: fki_parent_menu_id; Type: INDEX; Schema: public; Owner: npdc
--

CREATE INDEX fki_parent_menu_id ON menu USING btree (parent_menu_id);


--
-- Name: fki_responder; Type: INDEX; Schema: public; Owner: npdc
--

CREATE INDEX fki_responder ON access_request USING btree (responder_id);


--
-- Name: spatial_coverage spatial_coverage_wkt_update; Type: TRIGGER; Schema: public; Owner: npdc
--

CREATE TRIGGER spatial_coverage_wkt_update BEFORE INSERT OR UPDATE ON spatial_coverage FOR EACH ROW EXECUTE PROCEDURE spatial_coverage_wkt_update();


--
-- Name: access_request_file access_request_file_x_access_request_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY access_request_file
    ADD CONSTRAINT access_request_file_x_access_request_fk FOREIGN KEY (access_request_id) REFERENCES access_request(access_request_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: access_request_file access_request_file_x_file_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY access_request_file
    ADD CONSTRAINT access_request_file_x_file_fk FOREIGN KEY (file_id) REFERENCES file(file_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: access_request access_request_x_person_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY access_request
    ADD CONSTRAINT access_request_x_person_fk FOREIGN KEY (person_id) REFERENCES person(person_id) ON UPDATE CASCADE;


--
-- Name: access_request access_request_x_person_responder_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY access_request
    ADD CONSTRAINT access_request_x_person_responder_fk FOREIGN KEY (responder_id) REFERENCES person(person_id) ON UPDATE CASCADE;


--
-- Name: access_request access_request_x_zip_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY access_request
    ADD CONSTRAINT access_request_x_zip_fk FOREIGN KEY (zip_id) REFERENCES zip(zip_id) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- Name: account_reset account_reset_x_person_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY account_reset
    ADD CONSTRAINT account_reset_x_person_fk FOREIGN KEY (person_id) REFERENCES person(person_id) ON UPDATE CASCADE;


--
-- Name: additional_attributes additional_attributes_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY additional_attributes
    ADD CONSTRAINT additional_attributes_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: characteristics characteristics_x_instrument_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY characteristics
    ADD CONSTRAINT characteristics_x_instrument_fk FOREIGN KEY (instrument_id) REFERENCES instrument(instrument_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: characteristics characteristics_x_platform_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY characteristics
    ADD CONSTRAINT characteristics_x_platform_fk FOREIGN KEY (platform_id) REFERENCES platform(platform_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: characteristics characteristics_x_sensor_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY characteristics
    ADD CONSTRAINT characteristics_x_sensor_fk FOREIGN KEY (sensor_id) REFERENCES sensor(sensor_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: country country_x_continent_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY country
    ADD CONSTRAINT country_x_continent_fk FOREIGN KEY (continent_id) REFERENCES continent(continent_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: data_resolution data_resolution_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY data_resolution
    ADD CONSTRAINT data_resolution_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: data_resolution data_resolution_x_vocab_res_hor_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY data_resolution
    ADD CONSTRAINT data_resolution_x_vocab_res_hor_fk FOREIGN KEY (vocab_res_hor_id) REFERENCES vocab_res_hor(vocab_res_hor_id) ON UPDATE CASCADE;


--
-- Name: data_resolution data_resolution_x_vocab_res_time_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY data_resolution
    ADD CONSTRAINT data_resolution_x_vocab_res_time_fk FOREIGN KEY (vocab_res_time_id) REFERENCES vocab_res_time(vocab_res_time_id) ON UPDATE CASCADE;


--
-- Name: data_resolution data_resolution_x_vocab_res_vert_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY data_resolution
    ADD CONSTRAINT data_resolution_x_vocab_res_vert_fk FOREIGN KEY (vocab_res_vert_id) REFERENCES vocab_res_vert(vocab_res_vert_id) ON UPDATE CASCADE;


--
-- Name: dataset_citation dataset_citation_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_citation
    ADD CONSTRAINT dataset_citation_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_file dataset_file_x_file_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_file
    ADD CONSTRAINT dataset_file_x_file_fk FOREIGN KEY (file_id) REFERENCES file(file_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_keyword dataset_keyword_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_keyword
    ADD CONSTRAINT dataset_keyword_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_keyword dataset_keyword_x_vocab_science_keyword_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_keyword
    ADD CONSTRAINT dataset_keyword_x_vocab_science_keyword_fk FOREIGN KEY (vocab_science_keyword_id) REFERENCES vocab_science_keyword(vocab_science_keyword_id) ON UPDATE CASCADE;


--
-- Name: dataset_link_url dataset_link_url_x_dataset_link_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_link_url
    ADD CONSTRAINT dataset_link_url_x_dataset_link_fk FOREIGN KEY (dataset_link_id) REFERENCES dataset_link(dataset_link_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_link dataset_link_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_link
    ADD CONSTRAINT dataset_link_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_link dataset_link_x_mime_type_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_link
    ADD CONSTRAINT dataset_link_x_mime_type_fk FOREIGN KEY (mime_type_id) REFERENCES mime_type(mime_type_id) ON UPDATE CASCADE;


--
-- Name: dataset_link dataset_link_x_vocab_url_type_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_link
    ADD CONSTRAINT dataset_link_x_vocab_url_type_fk FOREIGN KEY (vocab_url_type_id) REFERENCES vocab_url_type(vocab_url_type_id) ON UPDATE CASCADE;


--
-- Name: dataset_person dataset_person_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_person
    ADD CONSTRAINT dataset_person_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_person dataset_person_x_person_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_person
    ADD CONSTRAINT dataset_person_x_person_fk FOREIGN KEY (person_id) REFERENCES person(person_id) ON UPDATE CASCADE;


--
-- Name: dataset_project dataset_project_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_project
    ADD CONSTRAINT dataset_project_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_project dataset_project_x_project_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_project
    ADD CONSTRAINT dataset_project_x_project_fk FOREIGN KEY (project_id, project_version_min) REFERENCES project(project_id, project_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_publication dataset_publication_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_publication
    ADD CONSTRAINT dataset_publication_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_publication dataset_publication_x_publication_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_publication
    ADD CONSTRAINT dataset_publication_x_publication_fk FOREIGN KEY (publication_id, publication_version_min) REFERENCES publication(publication_id, publication_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_topic dataset_topic_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_topic
    ADD CONSTRAINT dataset_topic_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: dataset_topic dataset_topic_x_vocab_iso_topic_category_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset_topic
    ADD CONSTRAINT dataset_topic_x_vocab_iso_topic_category_fk FOREIGN KEY (vocab_iso_topic_category_id) REFERENCES vocab_iso_topic_category(vocab_iso_topic_category_id) ON UPDATE CASCADE;


--
-- Name: dataset dataset_x_organization_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset
    ADD CONSTRAINT dataset_x_organization_fk FOREIGN KEY (originating_center) REFERENCES organization(organization_id) ON UPDATE CASCADE;


--
-- Name: dataset dataset_x_record_status_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY dataset
    ADD CONSTRAINT dataset_x_record_status_fk FOREIGN KEY (record_status) REFERENCES record_status(record_status) ON UPDATE CASCADE;


--
-- Name: distribution distribution_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY distribution
    ADD CONSTRAINT distribution_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: instrument instrument_x_platform_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY instrument
    ADD CONSTRAINT instrument_x_platform_fk FOREIGN KEY (platform_id) REFERENCES platform(platform_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: instrument instrument_x_vocab_instrument_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY instrument
    ADD CONSTRAINT instrument_x_vocab_instrument_fk FOREIGN KEY (vocab_instrument_id) REFERENCES vocab_instrument(vocab_instrument_id) ON UPDATE CASCADE;


--
-- Name: location_node location_node_x_idn_node_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY location_node
    ADD CONSTRAINT location_node_x_idn_node_fk FOREIGN KEY (short_name) REFERENCES idn_node(short_name) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: location_node location_node_x_vocab_location_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY location_node
    ADD CONSTRAINT location_node_x_vocab_location_fk FOREIGN KEY (vocab_location_id) REFERENCES vocab_location(vocab_location_id) ON UPDATE CASCADE;


--
-- Name: location location_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY location
    ADD CONSTRAINT location_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: location location_x_vocab_location_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY location
    ADD CONSTRAINT location_x_vocab_location_fk FOREIGN KEY (vocab_location_id) REFERENCES vocab_location(vocab_location_id) ON UPDATE CASCADE;


--
-- Name: menu menu_x_menu_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY menu
    ADD CONSTRAINT menu_x_menu_fk FOREIGN KEY (parent_menu_id) REFERENCES menu(menu_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: metadata_association metadata_association_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY metadata_association
    ADD CONSTRAINT metadata_association_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: multimedia_sample multimedia_sample_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY multimedia_sample
    ADD CONSTRAINT multimedia_sample_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: organization organization_x_country_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY organization
    ADD CONSTRAINT organization_x_country_fk FOREIGN KEY (country_id) REFERENCES country(country_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: page_link page_link_x_page_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY page_link
    ADD CONSTRAINT page_link_x_page_fk FOREIGN KEY (page_id) REFERENCES page(page_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: page_person page_person_x_page_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY page_person
    ADD CONSTRAINT page_person_x_page_fk FOREIGN KEY (page_id) REFERENCES page(page_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: page_person page_person_x_person_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY page_person
    ADD CONSTRAINT page_person_x_person_fk FOREIGN KEY (person_id) REFERENCES person(person_id) ON UPDATE CASCADE;


--
-- Name: person person_x_organization_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY person
    ADD CONSTRAINT person_x_organization_fk FOREIGN KEY (organization_id) REFERENCES organization(organization_id) ON UPDATE CASCADE;


--
-- Name: platform platform_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY platform
    ADD CONSTRAINT platform_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: platform platform_x_vocab_platform_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY platform
    ADD CONSTRAINT platform_x_vocab_platform_fk FOREIGN KEY (vocab_platform_id) REFERENCES vocab_platform(vocab_platform_id) ON UPDATE CASCADE;


--
-- Name: project_keyword project_keyword_x_project_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY project_keyword
    ADD CONSTRAINT project_keyword_x_project_fk FOREIGN KEY (project_id, project_version_min) REFERENCES project(project_id, project_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: project_link project_link_x_project_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY project_link
    ADD CONSTRAINT project_link_x_project_fk FOREIGN KEY (project_id, project_version_min) REFERENCES project(project_id, project_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: project_person project_person_x_person_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY project_person
    ADD CONSTRAINT project_person_x_person_fk FOREIGN KEY (person_id) REFERENCES person(person_id) ON UPDATE CASCADE;


--
-- Name: project_person project_person_x_project_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY project_person
    ADD CONSTRAINT project_person_x_project_fk FOREIGN KEY (project_id, project_version_min) REFERENCES project(project_id, project_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: project_publication project_publication_x_project_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY project_publication
    ADD CONSTRAINT project_publication_x_project_fk FOREIGN KEY (project_id, project_version_min) REFERENCES project(project_id, project_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: project_publication project_publication_x_publication_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY project_publication
    ADD CONSTRAINT project_publication_x_publication_fk FOREIGN KEY (publication_id, publication_version_min) REFERENCES publication(publication_id, publication_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: project project_x_program_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY project
    ADD CONSTRAINT project_x_program_fk FOREIGN KEY (program_id) REFERENCES program(program_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: project project_x_record_status_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY project
    ADD CONSTRAINT project_x_record_status_fk FOREIGN KEY (record_status) REFERENCES record_status(record_status) ON UPDATE CASCADE;


--
-- Name: publication_keyword publication_keyword_x_publication_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY publication_keyword
    ADD CONSTRAINT publication_keyword_x_publication_fk FOREIGN KEY (publication_id, publication_version_min) REFERENCES publication(publication_id, publication_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: publication_person publication_person_x_person_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY publication_person
    ADD CONSTRAINT publication_person_x_person_fk FOREIGN KEY (person_id) REFERENCES person(person_id) ON UPDATE CASCADE;


--
-- Name: publication_person publication_person_x_publication_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY publication_person
    ADD CONSTRAINT publication_person_x_publication_fk FOREIGN KEY (publication_id, publication_version_min) REFERENCES publication(publication_id, publication_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: publication publication_x_file_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY publication
    ADD CONSTRAINT publication_x_file_fk FOREIGN KEY (file_id) REFERENCES file(file_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: publication publication_x_record_status_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY publication
    ADD CONSTRAINT publication_x_record_status_fk FOREIGN KEY (record_status) REFERENCES record_status(record_status) ON UPDATE CASCADE;


--
-- Name: sensor sensor_x_instrument_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY sensor
    ADD CONSTRAINT sensor_x_instrument_fk FOREIGN KEY (instrument_id) REFERENCES instrument(instrument_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: sensor sensor_x_vocab_instrument_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY sensor
    ADD CONSTRAINT sensor_x_vocab_instrument_fk FOREIGN KEY (vocab_instrument_id) REFERENCES vocab_instrument(vocab_instrument_id) ON UPDATE CASCADE;


--
-- Name: spatial_coverage spatial_coverage_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY spatial_coverage
    ADD CONSTRAINT spatial_coverage_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: temporal_coverage_ancillary temporal_coverage_ancillary_x_temporal_coverage_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage_ancillary
    ADD CONSTRAINT temporal_coverage_ancillary_x_temporal_coverage_fk FOREIGN KEY (temporal_coverage_id) REFERENCES temporal_coverage(temporal_coverage_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: temporal_coverage_cycle temporal_coverage_cycle_x_temporal_coverage_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage_cycle
    ADD CONSTRAINT temporal_coverage_cycle_x_temporal_coverage_fk FOREIGN KEY (temporal_coverage_id) REFERENCES temporal_coverage(temporal_coverage_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: temporal_coverage_paleo temporal_coverage_paleo_x_temporal_coverage_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage_paleo
    ADD CONSTRAINT temporal_coverage_paleo_x_temporal_coverage_fk FOREIGN KEY (temporal_coverage_id) REFERENCES temporal_coverage(temporal_coverage_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: temporal_coverage_paleo temporal_coverage_paleo_x_vocab_chronounit_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage_paleo
    ADD CONSTRAINT temporal_coverage_paleo_x_vocab_chronounit_fk FOREIGN KEY (vocab_chronounit_id) REFERENCES vocab_chronounit(vocab_chronounit_id) ON UPDATE CASCADE;


--
-- Name: temporal_coverage_period temporal_coverage_period_x_temporal_coverage_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage_period
    ADD CONSTRAINT temporal_coverage_period_x_temporal_coverage_fk FOREIGN KEY (temporal_coverage_id) REFERENCES temporal_coverage(temporal_coverage_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: temporal_coverage temporal_coverage_x_dataset_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY temporal_coverage
    ADD CONSTRAINT temporal_coverage_x_dataset_fk FOREIGN KEY (dataset_id, dataset_version_min) REFERENCES dataset(dataset_id, dataset_version) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: zip_files zip_files_x_file_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY zip_files
    ADD CONSTRAINT zip_files_x_file_fk FOREIGN KEY (file_id) REFERENCES file(file_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: zip_files zip_files_x_zip_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY zip_files
    ADD CONSTRAINT zip_files_x_zip_fk FOREIGN KEY (zip_id) REFERENCES zip(zip_id) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- Name: zip zip_x_person_fk; Type: FK CONSTRAINT; Schema: public; Owner: npdc
--

ALTER TABLE ONLY zip
    ADD CONSTRAINT zip_x_person_fk FOREIGN KEY (person_id) REFERENCES person(person_id) ON UPDATE CASCADE;


--
-- Name: public; Type: ACL; Schema: -; Owner: npdc
--

REVOKE ALL ON SCHEMA public FROM postgres;
REVOKE ALL ON SCHEMA public FROM PUBLIC;
GRANT ALL ON SCHEMA public TO npdc;


--
-- PostgreSQL database dump complete
--

