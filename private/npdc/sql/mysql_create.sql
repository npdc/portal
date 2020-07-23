-- phpMyAdmin SQL Dump
-- version 4.5.4.1deb2ubuntu2.1
-- http://www.phpmyadmin.net
--
-- Host: localhost
-- Generation Time: 23 jul 2020, 13:57
-- Serverversie: 10.0.38-MariaDB-0ubuntu0.16.04.1
-- PHP-versie: 7.0.33-0ubuntu0.16.04.15

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `c2_npdc`
--

-- --------------------------------------------------------

--
-- Table structure for table `access_request`
--

CREATE TABLE `access_request` (
  `access_request_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `reason` longtext NOT NULL,
  `request_timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `permitted` tinyint(4) DEFAULT NULL,
  `response` longtext,
  `response_timestamp` datetime DEFAULT NULL,
  `dataset_id` int(11) DEFAULT NULL,
  `zip_id` int(11) DEFAULT NULL,
  `responder_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `access_request_file`
--

CREATE TABLE `access_request_file` (
  `access_request_file_id` int(11) NOT NULL,
  `access_request_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL,
  `permitted` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `account_new`
--

CREATE TABLE `account_new` (
  `account_new_id` int(11) NOT NULL,
  `code` longtext NOT NULL,
  `request_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used_time` datetime DEFAULT NULL,
  `expire_reason` longtext,
  `mail` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `account_reset`
--

CREATE TABLE `account_reset` (
  `account_reset_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `code` longtext NOT NULL,
  `request_time` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `used_time` datetime DEFAULT NULL,
  `expire_reason` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `additional_attributes`
--

CREATE TABLE `additional_attributes` (
  `additional_attributes_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `name` longtext NOT NULL,
  `datatype` longtext NOT NULL,
  `description` longtext NOT NULL,
  `measurement_resolution` longtext,
  `parameter_range_begin` longtext,
  `parameter_range_end` longtext,
  `parameter_units_of_measure` longtext,
  `parameter_value_accuracy` longtext,
  `value_accuracy_explanation` longtext,
  `value` longtext,
  `dataset_version_min` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `characteristics`
--

CREATE TABLE `characteristics` (
  `characteristics_id` int(11) NOT NULL,
  `name` longtext NOT NULL,
  `description` longtext NOT NULL,
  `unit` longtext NOT NULL,
  `value` longtext NOT NULL,
  `platform_id` int(11) DEFAULT NULL,
  `instrument_id` int(11) DEFAULT NULL,
  `sensor_id` int(11) DEFAULT NULL,
  `data_type` longtext,
  `dataset_version_min` int(11) DEFAULT NULL,
  `dataset_version_max` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `contact`
--

CREATE TABLE `contact` (
  `contact_id` int(11) NOT NULL,
  `receiver` text NOT NULL,
  `sender_mail` text NOT NULL,
  `sender_name` text NOT NULL,
  `subject` text,
  `text` text NOT NULL,
  `country` text COMMENT 'this should be empty, is the anti-spam field',
  `ip` varchar(100) DEFAULT NULL,
  `browser` text,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `continent`
--

CREATE TABLE `continent` (
  `continent_id` char(2) NOT NULL,
  `continent_name` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `continent`
--

INSERT INTO `continent` (`continent_id`, `continent_name`) VALUES
('AF', 'Africa'),
('AN', 'Antarctica'),
('AS', 'Asia'),
('EU', 'Europe'),
('NA', 'North America'),
('OC', 'Oceania'),
('SA', 'South America');

-- --------------------------------------------------------

--
-- Table structure for table `country`
--

CREATE TABLE `country` (
  `country_id` char(2) NOT NULL,
  `country_name` longtext,
  `continent_id` char(2) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `country`
--

INSERT INTO `country` (`country_id`, `country_name`, `continent_id`) VALUES
('AD', 'Andorra', 'EU'),
('AE', 'United Arab Emirates', 'AS'),
('AF', 'Afghanistan', 'AS'),
('AG', 'Antigua and Barbuda', 'NA'),
('AI', 'Anguilla', 'NA'),
('AL', 'Albania', 'EU'),
('AM', 'Armenia', 'AS'),
('AN', 'Netherlands Antilles', 'NA'),
('AO', 'Angola', 'AF'),
('AQ', 'Antarctica', 'AN'),
('AR', 'Argentina', 'SA'),
('AS', 'American Samoa', 'OC'),
('AT', 'Austria', 'EU'),
('AU', 'Australia', 'OC'),
('AW', 'Aruba', 'NA'),
('AX', 'Aland Islands', 'EU'),
('AZ', 'Azerbaijan', 'AS'),
('BA', 'Bosnia and Herzegovina', 'EU'),
('BB', 'Barbados', 'NA'),
('BD', 'Bangladesh', 'AS'),
('BE', 'Belgium', 'EU'),
('BF', 'Burkina Faso', 'AF'),
('BG', 'Bulgaria', 'EU'),
('BH', 'Bahrain', 'AS'),
('BI', 'Burundi', 'AF'),
('BJ', 'Benin', 'AF'),
('BL', 'Saint Barthelemy', 'NA'),
('BM', 'Bermuda', 'NA'),
('BN', 'Brunei', 'AS'),
('BO', 'Bolivia', 'SA'),
('BQ', 'Bonaire, Saint Eustatius and Saba ', 'NA'),
('BR', 'Brazil', 'SA'),
('BS', 'Bahamas', 'NA'),
('BT', 'Bhutan', 'AS'),
('BV', 'Bouvet Island', 'AN'),
('BW', 'Botswana', 'AF'),
('BY', 'Belarus', 'EU'),
('BZ', 'Belize', 'NA'),
('CA', 'Canada', 'NA'),
('CC', 'Cocos Islands', 'AS'),
('CD', 'Democratic Republic of the Congo', 'AF'),
('CF', 'Central African Republic', 'AF'),
('CG', 'Republic of the Congo', 'AF'),
('CH', 'Switzerland', 'EU'),
('CI', 'Ivory Coast', 'AF'),
('CK', 'Cook Islands', 'OC'),
('CL', 'Chile', 'SA'),
('CM', 'Cameroon', 'AF'),
('CN', 'China', 'AS'),
('CO', 'Colombia', 'SA'),
('CR', 'Costa Rica', 'NA'),
('CS', 'Serbia and Montenegro', 'EU'),
('CU', 'Cuba', 'NA'),
('CV', 'Cape Verde', 'AF'),
('CW', 'Curacao', 'NA'),
('CX', 'Christmas Island', 'AS'),
('CY', 'Cyprus', 'EU'),
('CZ', 'Czech Republic', 'EU'),
('DE', 'Germany', 'EU'),
('DJ', 'Djibouti', 'AF'),
('DK', 'Denmark', 'EU'),
('DM', 'Dominica', 'NA'),
('DO', 'Dominican Republic', 'NA'),
('DZ', 'Algeria', 'AF'),
('EC', 'Ecuador', 'SA'),
('EE', 'Estonia', 'EU'),
('EG', 'Egypt', 'AF'),
('EH', 'Western Sahara', 'AF'),
('ER', 'Eritrea', 'AF'),
('ES', 'Spain', 'EU'),
('ET', 'Ethiopia', 'AF'),
('FI', 'Finland', 'EU'),
('FJ', 'Fiji', 'OC'),
('FK', 'Falkland Islands', 'SA'),
('FM', 'Micronesia', 'OC'),
('FO', 'Faroe Islands', 'EU'),
('FR', 'France', 'EU'),
('GA', 'Gabon', 'AF'),
('GB', 'United Kingdom', 'EU'),
('GD', 'Grenada', 'NA'),
('GE', 'Georgia', 'AS'),
('GF', 'French Guiana', 'SA'),
('GG', 'Guernsey', 'EU'),
('GH', 'Ghana', 'AF'),
('GI', 'Gibraltar', 'EU'),
('GL', 'Greenland', 'NA'),
('GM', 'Gambia', 'AF'),
('GN', 'Guinea', 'AF'),
('GP', 'Guadeloupe', 'NA'),
('GQ', 'Equatorial Guinea', 'AF'),
('GR', 'Greece', 'EU'),
('GS', 'South Georgia and the South Sandwich Islands', 'AN'),
('GT', 'Guatemala', 'NA'),
('GU', 'Guam', 'OC'),
('GW', 'Guinea-Bissau', 'AF'),
('GY', 'Guyana', 'SA'),
('HK', 'Hong Kong', 'AS'),
('HM', 'Heard Island and McDonald Islands', 'AN'),
('HN', 'Honduras', 'NA'),
('HR', 'Croatia', 'EU'),
('HT', 'Haiti', 'NA'),
('HU', 'Hungary', 'EU'),
('ID', 'Indonesia', 'AS'),
('IE', 'Ireland', 'EU'),
('IL', 'Israel', 'AS'),
('IM', 'Isle of Man', 'EU'),
('IN', 'India', 'AS'),
('IO', 'British Indian Ocean Territory', 'AS'),
('IQ', 'Iraq', 'AS'),
('IR', 'Iran', 'AS'),
('IS', 'Iceland', 'EU'),
('IT', 'Italy', 'EU'),
('JE', 'Jersey', 'EU'),
('JM', 'Jamaica', 'NA'),
('JO', 'Jordan', 'AS'),
('JP', 'Japan', 'AS'),
('KE', 'Kenya', 'AF'),
('KG', 'Kyrgyzstan', 'AS'),
('KH', 'Cambodia', 'AS'),
('KI', 'Kiribati', 'OC'),
('KM', 'Comoros', 'AF'),
('KN', 'Saint Kitts and Nevis', 'NA'),
('KP', 'North Korea', 'AS'),
('KR', 'South Korea', 'AS'),
('KW', 'Kuwait', 'AS'),
('KY', 'Cayman Islands', 'NA'),
('KZ', 'Kazakhstan', 'AS'),
('LA', 'Laos', 'AS'),
('LB', 'Lebanon', 'AS'),
('LC', 'Saint Lucia', 'NA'),
('LI', 'Liechtenstein', 'EU'),
('LK', 'Sri Lanka', 'AS'),
('LR', 'Liberia', 'AF'),
('LS', 'Lesotho', 'AF'),
('LT', 'Lithuania', 'EU'),
('LU', 'Luxembourg', 'EU'),
('LV', 'Latvia', 'EU'),
('LY', 'Libya', 'AF'),
('MA', 'Morocco', 'AF'),
('MC', 'Monaco', 'EU'),
('MD', 'Moldova', 'EU'),
('ME', 'Montenegro', 'EU'),
('MF', 'Saint Martin', 'NA'),
('MG', 'Madagascar', 'AF'),
('MH', 'Marshall Islands', 'OC'),
('MK', 'Macedonia', 'EU'),
('ML', 'Mali', 'AF'),
('MM', 'Myanmar', 'AS'),
('MN', 'Mongolia', 'AS'),
('MO', 'Macao', 'AS'),
('MP', 'Northern Mariana Islands', 'OC'),
('MQ', 'Martinique', 'NA'),
('MR', 'Mauritania', 'AF'),
('MS', 'Montserrat', 'NA'),
('MT', 'Malta', 'EU'),
('MU', 'Mauritius', 'AF'),
('MV', 'Maldives', 'AS'),
('MW', 'Malawi', 'AF'),
('MX', 'Mexico', 'NA'),
('MY', 'Malaysia', 'AS'),
('MZ', 'Mozambique', 'AF'),
('NA', 'Namibia', 'AF'),
('NC', 'New Caledonia', 'OC'),
('NE', 'Niger', 'AF'),
('NF', 'Norfolk Island', 'OC'),
('NG', 'Nigeria', 'AF'),
('NI', 'Nicaragua', 'NA'),
('NL', 'Netherlands', 'EU'),
('NO', 'Norway', 'EU'),
('NP', 'Nepal', 'AS'),
('NR', 'Nauru', 'OC'),
('NU', 'Niue', 'OC'),
('NZ', 'New Zealand', 'OC'),
('OM', 'Oman', 'AS'),
('PA', 'Panama', 'NA'),
('PE', 'Peru', 'SA'),
('PF', 'French Polynesia', 'OC'),
('PG', 'Papua New Guinea', 'OC'),
('PH', 'Philippines', 'AS'),
('PK', 'Pakistan', 'AS'),
('PL', 'Poland', 'EU'),
('PM', 'Saint Pierre and Miquelon', 'NA'),
('PN', 'Pitcairn', 'OC'),
('PR', 'Puerto Rico', 'NA'),
('PS', 'Palestinian Territory', 'AS'),
('PT', 'Portugal', 'EU'),
('PW', 'Palau', 'OC'),
('PY', 'Paraguay', 'SA'),
('QA', 'Qatar', 'AS'),
('RE', 'Reunion', 'AF'),
('RO', 'Romania', 'EU'),
('RS', 'Serbia', 'EU'),
('RU', 'Russia', 'EU'),
('RW', 'Rwanda', 'AF'),
('SA', 'Saudi Arabia', 'AS'),
('SB', 'Solomon Islands', 'OC'),
('SC', 'Seychelles', 'AF'),
('SD', 'Sudan', 'AF'),
('SE', 'Sweden', 'EU'),
('SG', 'Singapore', 'AS'),
('SH', 'Saint Helena', 'AF'),
('SI', 'Slovenia', 'EU'),
('SJ', 'Svalbard and Jan Mayen', 'EU'),
('SK', 'Slovakia', 'EU'),
('SL', 'Sierra Leone', 'AF'),
('SM', 'San Marino', 'EU'),
('SN', 'Senegal', 'AF'),
('SO', 'Somalia', 'AF'),
('SR', 'Suriname', 'SA'),
('SS', 'South Sudan', 'AF'),
('ST', 'Sao Tome and Principe', 'AF'),
('SV', 'El Salvador', 'NA'),
('SX', 'Sint Maarten', 'NA'),
('SY', 'Syria', 'AS'),
('SZ', 'Swaziland', 'AF'),
('TC', 'Turks and Caicos Islands', 'NA'),
('TD', 'Chad', 'AF'),
('TF', 'French Southern Territories', 'AN'),
('TG', 'Togo', 'AF'),
('TH', 'Thailand', 'AS'),
('TJ', 'Tajikistan', 'AS'),
('TK', 'Tokelau', 'OC'),
('TL', 'East Timor', 'OC'),
('TM', 'Turkmenistan', 'AS'),
('TN', 'Tunisia', 'AF'),
('TO', 'Tonga', 'OC'),
('TR', 'Turkey', 'AS'),
('TT', 'Trinidad and Tobago', 'NA'),
('TV', 'Tuvalu', 'OC'),
('TW', 'Taiwan', 'AS'),
('TZ', 'Tanzania', 'AF'),
('UA', 'Ukraine', 'EU'),
('UG', 'Uganda', 'AF'),
('UM', 'United States Minor Outlying Islands', 'OC'),
('US', 'United States', 'NA'),
('UY', 'Uruguay', 'SA'),
('UZ', 'Uzbekistan', 'AS'),
('VA', 'Vatican', 'EU'),
('VC', 'Saint Vincent and the Grenadines', 'NA'),
('VE', 'Venezuela', 'SA'),
('VG', 'British Virgin Islands', 'NA'),
('VI', 'U.S. Virgin Islands', 'NA'),
('VN', 'Vietnam', 'AS'),
('VU', 'Vanuatu', 'OC'),
('WF', 'Wallis and Futuna', 'OC'),
('WS', 'Samoa', 'OC'),
('XK', 'Kosovo', 'EU'),
('YE', 'Yemen', 'AS'),
('YT', 'Mayotte', 'AF'),
('ZA', 'South Africa', 'AF'),
('ZM', 'Zambia', 'AF'),
('ZW', 'Zimbabwe', 'AF');

-- --------------------------------------------------------

--
-- Table structure for table `dataset`
--

CREATE TABLE `dataset` (
  `dataset_id` int(11) NOT NULL,
  `dataset_version` int(11) NOT NULL,
  `dif_id` longtext,
  `published` datetime DEFAULT NULL,
  `title` longtext NOT NULL,
  `summary` longtext NOT NULL,
  `region` varchar(10) NOT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `quality` longtext,
  `access_constraints` longtext,
  `use_constraints` longtext,
  `dataset_progress` longtext,
  `originating_center` int(11) DEFAULT NULL,
  `dif_revision_history` longtext,
  `version_description` longtext,
  `product_level_id` longtext,
  `collection_data_type` longtext,
  `extended_metadata` longtext,
  `record_status` varchar(9) NOT NULL,
  `purpose` longtext,
  `insert_timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creator` int(11) NOT NULL,
  `ipy` tinyint(1) NOT NULL DEFAULT '0',
  `uuid` varchar(36) DEFAULT NULL,
  `created_from` varchar(36) DEFAULT NULL,
  `license` varchar(10) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `dataset_ancillary_keyword`
--

CREATE TABLE `dataset_ancillary_keyword` (
  `dataset_ancillary_keyword_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `keyword` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `dataset_citation`
--

CREATE TABLE `dataset_citation` (
  `dataset_citation_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `creator` longtext,
  `editor` longtext,
  `title` longtext,
  `series_name` longtext,
  `release_date` date DEFAULT NULL,
  `release_place` longtext,
  `publisher` longtext,
  `version` longtext,
  `issue_identification` longtext,
  `presentation_form` longtext,
  `other` longtext,
  `persistent_identifier_type` longtext,
  `persistent_identifier_identifier` longtext,
  `online_resource` longtext,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `type` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `dataset_data_center`
--

CREATE TABLE `dataset_data_center` (
  `dataset_data_center_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `organization_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `dataset_data_center_person`
--

CREATE TABLE `dataset_data_center_person` (
  `dataset_data_center_person_id` int(11) NOT NULL,
  `dataset_data_center_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `person_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `dataset_file`
--

CREATE TABLE `dataset_file` (
  `dataset_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `file_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `dataset_keyword`
--

CREATE TABLE `dataset_keyword` (
  `dataset_keyword_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `vocab_science_keyword_id` int(11) NOT NULL,
  `free_text` longtext,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `dataset_link`
--

CREATE TABLE `dataset_link` (
  `dataset_link_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `title` longtext NOT NULL,
  `vocab_url_type_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `description` longtext,
  `mime_type_id` int(11) DEFAULT NULL,
  `protocol` longtext,
  `dataset_version_max` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `dataset_link_url`
--

CREATE TABLE `dataset_link_url` (
  `dataset_link_url_id` int(11) NOT NULL,
  `dataset_link_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `url` longtext,
  `old_dataset_link_url_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `dataset_person`
--

CREATE TABLE `dataset_person` (
  `dataset_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `editor` tinyint(4) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `role` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `dataset_project`
--

CREATE TABLE `dataset_project` (
  `dataset_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `project_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `project_version_max` int(11) DEFAULT NULL,
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `dataset_publication`
--

CREATE TABLE `dataset_publication` (
  `publication_id` int(11) NOT NULL,
  `publication_version_min` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `publication_version_max` int(11) DEFAULT NULL,
  `dataset_version_max` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `dataset_topic`
--

CREATE TABLE `dataset_topic` (
  `vocab_iso_topic_category_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `data_center_person_default`
--

CREATE TABLE `data_center_person_default` (
  `organization_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `data_resolution`
--

CREATE TABLE `data_resolution` (
  `data_resolution_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `latitude_resolution` longtext,
  `longitude_resolution` longtext,
  `vocab_res_hor_id` int(11) DEFAULT NULL,
  `vertical_resolution` longtext,
  `vocab_res_vert_id` int(11) DEFAULT NULL,
  `temporal_resolution` longtext,
  `vocab_res_time_id` int(11) DEFAULT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `distribution`
--

CREATE TABLE `distribution` (
  `distribution_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `media` longtext NOT NULL,
  `size` longtext NOT NULL,
  `format` longtext NOT NULL,
  `fees` longtext NOT NULL,
  `dataset_version_min` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `file`
--

CREATE TABLE `file` (
  `file_id` int(11) NOT NULL,
  `name` longtext,
  `location` longtext,
  `type` longtext,
  `size` int(11) DEFAULT NULL,
  `default_access` varchar(13) NOT NULL DEFAULT 'private',
  `description` longtext,
  `insert_timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `record_state` varchar(9) NOT NULL DEFAULT 'draft',
  `title` longtext,
  `form_id` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `instrument`
--

CREATE TABLE `instrument` (
  `instrument_id` int(11) NOT NULL,
  `platform_id` int(11) NOT NULL,
  `vocab_instrument_id` int(11) NOT NULL,
  `number_of_sensors` int(11) DEFAULT NULL,
  `operational_mode` longtext,
  `technique` longtext,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `old_instrument_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `location`
--

CREATE TABLE `location` (
  `location_id` int(11) NOT NULL,
  `vocab_location_id` int(11) NOT NULL,
  `detailed` longtext,
  `dataset_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `menu`
--

CREATE TABLE `menu` (
  `menu_id` int(11) NOT NULL,
  `label` longtext NOT NULL,
  `url` longtext,
  `parent_menu_id` int(11) DEFAULT NULL,
  `sort` int(11) NOT NULL,
  `min_user_level` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `menu`
--

INSERT INTO `menu` (`menu_id`, `label`, `url`, `parent_menu_id`, `sort`, `min_user_level`) VALUES
(3, 'Home', '', NULL, 1, 'public'),
(4, 'Data', NULL, NULL, 2, 'public'),
(5, 'Info', NULL, NULL, 3, 'public'),
(6, 'Tips', 'tips', NULL, 4, 'public'),
(7, 'Contact', 'contact', NULL, 5, 'public'),
(8, 'Datasets', 'dataset', 4, 1, 'public'),
(9, 'Publications', 'publication', 4, 2, 'public'),
(10, 'Data portals', 'portals', 4, 3, 'public'),
(11, 'Projects', 'project', 4, 4, 'public'),
(12, 'NPP', 'npp', 5, 1, 'public'),
(13, 'NPDC', 'npdc', 5, 2, 'public'),
(14, 'Admin', NULL, NULL, 7, 'admin'),
(15, 'Organizations', 'organization', 14, 1, 'admin'),
(16, 'People', 'person', 14, 2, 'admin'),
(17, 'Users', 'users', 14, 3, 'nobody'),
(18, 'User', NULL, NULL, 6, 'user'),
(19, 'Data requests', 'request', 18, 2, 'user'),
(20, 'Account settings', 'account', 18, 1, 'user'),
(21, 'Editor tools', 'editor', 18, 3, 'editor'),
(22, 'Organizations', 'organization', 4, 5, 'public');

-- --------------------------------------------------------

--
-- Table structure for table `metadata_association`
--

CREATE TABLE `metadata_association` (
  `metadata_association_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `entry_id` longtext NOT NULL,
  `type` longtext NOT NULL,
  `description` longtext,
  `dataset_version_min` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `mime_type`
--

CREATE TABLE `mime_type` (
  `mime_type_id` int(11) NOT NULL,
  `label` longtext,
  `type` longtext,
  `visible` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `multimedia_sample`
--

CREATE TABLE `multimedia_sample` (
  `multimedia_sample_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `file` longtext,
  `url` longtext NOT NULL,
  `format` longtext,
  `caption` longtext,
  `description` longtext,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `news_id` int(11) NOT NULL,
  `title` longtext NOT NULL,
  `content` longtext NOT NULL,
  `published` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `show_till` datetime DEFAULT NULL,
  `link` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `npp_theme`
--

CREATE TABLE `npp_theme` (
  `npp_theme_id` int(11) NOT NULL,
  `theme_nl` varchar(100) DEFAULT NULL,
  `theme_en` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `organization`
--

CREATE TABLE `organization` (
  `organization_id` int(11) NOT NULL,
  `organization_name` longtext NOT NULL,
  `organization_address` longtext,
  `organization_zip` longtext,
  `organization_city` longtext,
  `visiting_address` longtext,
  `edmo` int(11) DEFAULT NULL,
  `dif_code` longtext,
  `dif_name` longtext,
  `website` longtext,
  `country_id` char(2) DEFAULT 'NL',
  `uuid` text,
  `historic_name` text COMMENT 'multiple values allowed, comma separated'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `page`
--

CREATE TABLE `page` (
  `page_id` int(11) NOT NULL,
  `title` longtext NOT NULL,
  `content` longtext NOT NULL,
  `url` longtext NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `show_last_revision` tinyint(1) DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `page_link`
--

CREATE TABLE `page_link` (
  `page_link_id` int(11) NOT NULL,
  `page_id` int(11) NOT NULL,
  `url` longtext NOT NULL,
  `text` longtext NOT NULL,
  `sort` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `page_person`
--

CREATE TABLE `page_person` (
  `page_id` int(11) NOT NULL,
  `person_id` int(11) NOT NULL,
  `role` longtext NOT NULL,
  `editor` tinyint(4) NOT NULL DEFAULT '0',
  `sort` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `person`
--

CREATE TABLE `person` (
  `person_id` int(11) NOT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `name` longtext NOT NULL,
  `titles` longtext,
  `initials` longtext,
  `given_name` longtext,
  `surname` longtext,
  `mail` longtext,
  `phone_personal` longtext,
  `phone_secretariat` longtext,
  `phone_mobile` longtext,
  `address` longtext,
  `zip` longtext,
  `city` longtext,
  `sees_participant` longtext,
  `language` longtext,
  `password` longtext,
  `user_level` varchar(9) NOT NULL DEFAULT 'user',
  `orcid` char(16) DEFAULT NULL,
  `phone_personal_public` tinyint(1) NOT NULL DEFAULT '1',
  `phone_secretariat_public` tinyint(1) NOT NULL DEFAULT '1',
  `phone_mobile_public` tinyint(1) NOT NULL DEFAULT '0',
  `sex` char(1) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `platform`
--

CREATE TABLE `platform` (
  `platform_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `vocab_platform_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `program`
--

CREATE TABLE `program` (
  `program_id` int(11) NOT NULL,
  `name` longtext NOT NULL,
  `program_start` date NOT NULL,
  `program_end` date DEFAULT NULL,
  `sort` smallint(6) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `project`
--

CREATE TABLE `project` (
  `project_id` int(11) NOT NULL,
  `project_version` int(11) NOT NULL,
  `nwo_project_id` varchar(20) DEFAULT NULL,
  `title` longtext NOT NULL,
  `acronym` longtext,
  `region` longtext NOT NULL,
  `summary` longtext,
  `program_id` int(11) DEFAULT NULL,
  `date_start` date DEFAULT NULL,
  `date_end` date DEFAULT NULL,
  `ris_id` int(11) DEFAULT NULL,
  `proposal_status` longtext,
  `data_status` longtext,
  `research_type` longtext,
  `science_field` longtext,
  `data_type` longtext,
  `comments` longtext,
  `record_status` varchar(9) NOT NULL,
  `insert_timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creator` int(11) NOT NULL,
  `published` datetime DEFAULT NULL,
  `uuid` varchar(36) DEFAULT NULL,
  `npp_theme_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `project_keyword`
--

CREATE TABLE `project_keyword` (
  `project_keyword_id` int(11) NOT NULL,
  `keyword` longtext NOT NULL,
  `project_version_min` int(11) NOT NULL,
  `project_version_max` int(11) DEFAULT NULL,
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `project_link`
--

CREATE TABLE `project_link` (
  `project_link_id` int(11) NOT NULL,
  `url` longtext NOT NULL,
  `text` longtext NOT NULL,
  `project_version_min` int(11) NOT NULL,
  `project_version_max` int(11) DEFAULT NULL,
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `project_person`
--

CREATE TABLE `project_person` (
  `person_id` int(11) NOT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `project_version_min` int(11) NOT NULL,
  `project_version_max` int(11) DEFAULT NULL,
  `role` longtext NOT NULL,
  `sort` int(11) NOT NULL,
  `contact` tinyint(4) NOT NULL DEFAULT '1',
  `editor` tinyint(4) NOT NULL DEFAULT '0',
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `project_project`
--

CREATE TABLE `project_project` (
  `parent_project_id` int(11) NOT NULL,
  `child_project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `project_publication`
--

CREATE TABLE `project_publication` (
  `publication_id` int(11) NOT NULL,
  `publication_version_min` int(11) NOT NULL,
  `project_version_min` int(11) NOT NULL,
  `publication_version_max` int(11) DEFAULT NULL,
  `project_version_max` int(11) DEFAULT NULL,
  `project_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `publication`
--

CREATE TABLE `publication` (
  `publication_id` int(11) NOT NULL,
  `publication_version` int(11) NOT NULL,
  `title` longtext NOT NULL,
  `abstract` longtext,
  `journal` longtext,
  `volume` longtext,
  `issue` text,
  `pages` longtext,
  `isbn` longtext,
  `doi` longtext,
  `record_status` varchar(9) NOT NULL,
  `date` date DEFAULT NULL,
  `url` longtext,
  `insert_timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `creator` int(11) NOT NULL,
  `published` datetime DEFAULT NULL,
  `uuid` varchar(36) DEFAULT NULL,
  `publication_type_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `publication_keyword`
--

CREATE TABLE `publication_keyword` (
  `publication_keyword_id` int(11) NOT NULL,
  `publication_id` int(11) NOT NULL,
  `keyword` longtext NOT NULL,
  `publication_version_min` int(11) NOT NULL,
  `publication_version_max` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `publication_person`
--

CREATE TABLE `publication_person` (
  `publication_person_id` int(11) NOT NULL,
  `publication_id` int(11) NOT NULL,
  `publication_version_min` int(11) NOT NULL,
  `person_id` int(11) DEFAULT NULL,
  `organization_id` int(11) DEFAULT NULL,
  `free_person` varchar(255) DEFAULT NULL,
  `sort` int(11) NOT NULL,
  `contact` tinyint(4) NOT NULL DEFAULT '0',
  `publication_version_max` int(11) DEFAULT NULL,
  `editor` tinyint(4) NOT NULL DEFAULT '0',
  `free_organization` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `publication_type`
--

CREATE TABLE `publication_type` (
  `publication_type_id` int(11) NOT NULL,
  `label` varchar(100) DEFAULT NULL,
  `bib` varchar(100) NOT NULL,
  `ris` varchar(100) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `record_status`
--

CREATE TABLE `record_status` (
  `record_status` varchar(9) NOT NULL,
  `editable` tinyint(4) NOT NULL,
  `visible` tinyint(4) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `record_status_change`
--

CREATE TABLE `record_status_change` (
  `project_id` int(11) DEFAULT NULL,
  `dataset_id` int(11) DEFAULT NULL,
  `publication_id` int(11) DEFAULT NULL,
  `old_state` longtext NOT NULL,
  `new_state` longtext NOT NULL,
  `person_id` int(11) NOT NULL,
  `datetime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `comment` longtext,
  `version` int(11) DEFAULT NULL,
  `record_status_change_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `related_dataset`
--

CREATE TABLE `related_dataset` (
  `related_dataset_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  `doi` varchar(100) DEFAULT NULL,
  `internal_related_dataset_id` int(11) DEFAULT NULL,
  `relation` varchar(255) DEFAULT NULL,
  `same` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `sensor`
--

CREATE TABLE `sensor` (
  `sensor_id` int(11) NOT NULL,
  `instrument_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `vocab_instrument_id` int(11) DEFAULT NULL,
  `technique` longtext,
  `old_sensor_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `spatial_coverage`
--

CREATE TABLE `spatial_coverage` (
  `spatial_coverage_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `wkt` longtext,
  `depth_min` double DEFAULT NULL,
  `depth_max` double DEFAULT NULL,
  `depth_unit` longtext,
  `altitude_min` double DEFAULT NULL,
  `altitude_max` double DEFAULT NULL,
  `altitude_unit` longtext,
  `type` longtext,
  `label` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `suggestion`
--

CREATE TABLE `suggestion` (
  `suggestion_id` int(11) NOT NULL,
  `field` varchar(45) NOT NULL,
  `suggestion` mediumtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `temporal_coverage`
--

CREATE TABLE `temporal_coverage` (
  `temporal_coverage_id` int(11) NOT NULL,
  `dataset_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `temporal_coverage_ancillary`
--

CREATE TABLE `temporal_coverage_ancillary` (
  `temporal_coverage_ancillary_id` int(11) NOT NULL,
  `temporal_coverage_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `keyword` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `temporal_coverage_cycle`
--

CREATE TABLE `temporal_coverage_cycle` (
  `temporal_coverage_cycle_id` int(11) NOT NULL,
  `temporal_coverage_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `name` longtext NOT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL,
  `sampling_frequency` double NOT NULL,
  `sampling_frequency_unit` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `temporal_coverage_paleo`
--

CREATE TABLE `temporal_coverage_paleo` (
  `temporal_coverage_paleo_id` int(11) NOT NULL,
  `temporal_coverage_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `start_value` double DEFAULT NULL,
  `start_unit` longtext,
  `end_value` double DEFAULT NULL,
  `end_unit` longtext
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `temporal_coverage_paleo_chronounit`
--

CREATE TABLE `temporal_coverage_paleo_chronounit` (
  `temporal_coverage_paleo_chronounit_id` int(11) NOT NULL,
  `temporal_coverage_paleo_id` int(11) NOT NULL DEFAULT '0',
  `dataset_version_min` int(11) NOT NULL DEFAULT '0',
  `dataset_version_max` int(11) DEFAULT NULL,
  `vocab_chronounit_id` int(11) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `temporal_coverage_period`
--

CREATE TABLE `temporal_coverage_period` (
  `temporal_coverage_period_id` int(11) NOT NULL,
  `temporal_coverage_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `date_start` date NOT NULL,
  `date_end` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `user_level`
--

CREATE TABLE `user_level` (
  `user_level_id` int(11) NOT NULL,
  `label` varchar(9) NOT NULL,
  `description` longtext,
  `name` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_level`
--

INSERT INTO `user_level` (`user_level_id`, `label`, `description`, `name`) VALUES
(0, 'public', NULL, 'Guest'),
(1, 'user', '- You can download files which are available to logged in users\r\n- You can request access to restricted files', 'Logged in user'),
(2, 'editor', '- You can add new projects, publications and datasets\r\n- You can edit projects, publications and datasets for which you have been given edit rights (either by creating them or when someone else granted you those rights)', 'Editor'),
(3, 'admin', '- You can edit all content', 'Administrator'),
(4, 'nobody', NULL, 'Unrestricted access');

-- --------------------------------------------------------

--
-- Table structure for table `vocab`
--

CREATE TABLE `vocab` (
  `vocab_id` int(11) NOT NULL,
  `vocab_name` longtext NOT NULL,
  `last_update_date` date DEFAULT NULL,
  `last_update_local` date DEFAULT NULL,
  `sync` tinyint(4) NOT NULL DEFAULT '0'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vocab_chronounit`
--

CREATE TABLE `vocab_chronounit` (
  `vocab_chronounit_id` int(11) NOT NULL,
  `eon` longtext,
  `era` longtext,
  `period` longtext,
  `epoch` longtext,
  `stage` longtext,
  `uuid` varchar(36) DEFAULT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1',
  `sort` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vocab_idn_node`
--

CREATE TABLE `vocab_idn_node` (
  `vocab_idn_node_id` int(11) NOT NULL,
  `short_name` text NOT NULL,
  `long_name` text,
  `uuid` varchar(36) NOT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vocab_instrument`
--

CREATE TABLE `vocab_instrument` (
  `vocab_instrument_id` int(11) NOT NULL,
  `category` longtext NOT NULL,
  `class` longtext,
  `type` longtext,
  `subtype` longtext,
  `short_name` longtext,
  `long_name` longtext,
  `uuid` varchar(36) DEFAULT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vocab_iso_topic_category`
--

CREATE TABLE `vocab_iso_topic_category` (
  `vocab_iso_topic_category_id` int(11) NOT NULL,
  `topic` longtext NOT NULL,
  `description` longtext,
  `visible` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vocab_location`
--

CREATE TABLE `vocab_location` (
  `vocab_location_id` int(11) NOT NULL,
  `location_category` longtext NOT NULL,
  `location_type` longtext,
  `location_subregion1` longtext,
  `location_subregion2` longtext,
  `location_subregion3` longtext,
  `uuid` longtext NOT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vocab_location_vocab_idn_node`
--

CREATE TABLE `vocab_location_vocab_idn_node` (
  `vocab_location_id` int(11) NOT NULL,
  `vocab_idn_node_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vocab_organization`
--

CREATE TABLE `vocab_organization` (
  `lvl0` text,
  `lvl1` text,
  `lvl2` text,
  `lvl3` text,
  `short_name` text,
  `long_name` text,
  `url` text,
  `uuid` text
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vocab_platform`
--

CREATE TABLE `vocab_platform` (
  `vocab_platform_id` int(11) NOT NULL,
  `category` longtext NOT NULL,
  `series_entity` longtext,
  `short_name` longtext,
  `long_name` longtext,
  `uuid` longtext NOT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vocab_res_hor`
--

CREATE TABLE `vocab_res_hor` (
  `vocab_res_hor_id` int(11) NOT NULL,
  `range` longtext NOT NULL,
  `uuid` varchar(36) NOT NULL,
  `sort` int(11) NOT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vocab_res_time`
--

CREATE TABLE `vocab_res_time` (
  `vocab_res_time_id` int(11) NOT NULL,
  `range` longtext NOT NULL,
  `uuid` longtext NOT NULL,
  `sort` int(11) NOT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vocab_res_vert`
--

CREATE TABLE `vocab_res_vert` (
  `vocab_res_vert_id` int(11) NOT NULL,
  `range` longtext NOT NULL,
  `uuid` longtext NOT NULL,
  `sort` int(11) NOT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vocab_science_keyword`
--

CREATE TABLE `vocab_science_keyword` (
  `vocab_science_keyword_id` int(11) NOT NULL,
  `category` longtext NOT NULL,
  `topic` longtext,
  `term` longtext,
  `var_lvl_1` longtext,
  `var_lvl_2` longtext,
  `var_lvl_3` longtext,
  `uuid` longtext NOT NULL,
  `detailed_variable` longtext,
  `visible` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `vocab_url_type`
--

CREATE TABLE `vocab_url_type` (
  `vocab_url_type_id` int(11) NOT NULL,
  `type` longtext NOT NULL,
  `subtype` longtext,
  `uuid` longtext NOT NULL,
  `visible` tinyint(4) NOT NULL DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `zip`
--

CREATE TABLE `zip` (
  `zip_id` int(11) NOT NULL,
  `filename` longtext NOT NULL,
  `person_id` int(11) DEFAULT NULL,
  `guest_user` longtext,
  `timestamp` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `dataset_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `zip_files`
--

CREATE TABLE `zip_files` (
  `zip_files_id` int(11) NOT NULL,
  `zip_id` int(11) NOT NULL,
  `file_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `access_request`
--
ALTER TABLE `access_request`
  ADD PRIMARY KEY (`access_request_id`),
  ADD KEY `fki_access_zip` (`zip_id`),
  ADD KEY `fki_responder` (`responder_id`),
  ADD KEY `access_request_x_person_fk` (`person_id`);

--
-- Indexes for table `access_request_file`
--
ALTER TABLE `access_request_file`
  ADD PRIMARY KEY (`access_request_file_id`),
  ADD KEY `access_request_file_x_access_request_fk` (`access_request_id`),
  ADD KEY `access_request_file_x_file_fk` (`file_id`);

--
-- Indexes for table `account_new`
--
ALTER TABLE `account_new`
  ADD PRIMARY KEY (`account_new_id`);

--
-- Indexes for table `account_reset`
--
ALTER TABLE `account_reset`
  ADD PRIMARY KEY (`account_reset_id`),
  ADD KEY `account_reset_x_person_fk` (`person_id`);

--
-- Indexes for table `additional_attributes`
--
ALTER TABLE `additional_attributes`
  ADD PRIMARY KEY (`additional_attributes_id`),
  ADD KEY `additional_attributes_x_dataset_fk` (`dataset_id`,`dataset_version_min`);

--
-- Indexes for table `characteristics`
--
ALTER TABLE `characteristics`
  ADD PRIMARY KEY (`characteristics_id`),
  ADD KEY `characteristics_x_instrument_fk` (`instrument_id`),
  ADD KEY `characteristics_x_platform_fk` (`platform_id`),
  ADD KEY `characteristics_x_sensor_fk` (`sensor_id`);

--
-- Indexes for table `contact`
--
ALTER TABLE `contact`
  ADD PRIMARY KEY (`contact_id`);

--
-- Indexes for table `continent`
--
ALTER TABLE `continent`
  ADD PRIMARY KEY (`continent_id`);

--
-- Indexes for table `country`
--
ALTER TABLE `country`
  ADD PRIMARY KEY (`country_id`),
  ADD KEY `country_x_continent_fk` (`continent_id`);

--
-- Indexes for table `dataset`
--
ALTER TABLE `dataset`
  ADD PRIMARY KEY (`dataset_id`,`dataset_version`),
  ADD KEY `dataset_record_status` (`record_status`),
  ADD KEY `dataset_x_organization_fk` (`originating_center`),
  ADD KEY `dataset_x_person_fk` (`creator`);

--
-- Indexes for table `dataset_ancillary_keyword`
--
ALTER TABLE `dataset_ancillary_keyword`
  ADD PRIMARY KEY (`dataset_ancillary_keyword_id`),
  ADD KEY `dataset_id` (`dataset_id`,`dataset_version_min`);

--
-- Indexes for table `dataset_citation`
--
ALTER TABLE `dataset_citation`
  ADD PRIMARY KEY (`dataset_citation_id`),
  ADD KEY `dataset_citation_x_dataset_fk` (`dataset_id`,`dataset_version_min`);

--
-- Indexes for table `dataset_data_center`
--
ALTER TABLE `dataset_data_center`
  ADD PRIMARY KEY (`dataset_data_center_id`),
  ADD KEY `organization_id` (`organization_id`),
  ADD KEY `dataset_data_center_id` (`dataset_id`,`dataset_version_min`) USING BTREE;

--
-- Indexes for table `dataset_data_center_person`
--
ALTER TABLE `dataset_data_center_person`
  ADD PRIMARY KEY (`dataset_data_center_person_id`),
  ADD KEY `dataset_data_center_id` (`dataset_data_center_id`),
  ADD KEY `person_id` (`person_id`);

--
-- Indexes for table `dataset_file`
--
ALTER TABLE `dataset_file`
  ADD PRIMARY KEY (`dataset_id`,`dataset_version_min`,`file_id`),
  ADD KEY `dataset_file_x_file_fk` (`file_id`);

--
-- Indexes for table `dataset_keyword`
--
ALTER TABLE `dataset_keyword`
  ADD PRIMARY KEY (`dataset_keyword_id`),
  ADD KEY `dataset_keyword_x_dataset_fk` (`dataset_id`,`dataset_version_min`),
  ADD KEY `dataset_keyword_x_vocab_science_keyword_fk` (`vocab_science_keyword_id`);

--
-- Indexes for table `dataset_link`
--
ALTER TABLE `dataset_link`
  ADD PRIMARY KEY (`dataset_link_id`),
  ADD KEY `fki_mime` (`mime_type_id`),
  ADD KEY `dataset_link_x_dataset_fk` (`dataset_id`,`dataset_version_min`),
  ADD KEY `dataset_link_x_vocab_url_type_fk` (`vocab_url_type_id`);

--
-- Indexes for table `dataset_link_url`
--
ALTER TABLE `dataset_link_url`
  ADD PRIMARY KEY (`dataset_link_url_id`),
  ADD KEY `fki_link` (`dataset_link_id`),
  ADD KEY `old_dataset_link_url_id` (`old_dataset_link_url_id`);

--
-- Indexes for table `dataset_person`
--
ALTER TABLE `dataset_person`
  ADD PRIMARY KEY (`dataset_id`,`dataset_version_min`,`person_id`),
  ADD KEY `dataset_person_x_person_fk` (`person_id`),
  ADD KEY `dataset_x_org_fk` (`organization_id`);

--
-- Indexes for table `dataset_project`
--
ALTER TABLE `dataset_project`
  ADD PRIMARY KEY (`dataset_id`,`dataset_version_min`,`project_version_min`,`project_id`),
  ADD KEY `dataset_project_x_dataset_fk` (`dataset_id`,`dataset_version_min`),
  ADD KEY `dataset_project_x_project_fk` (`project_id`,`project_version_min`);

--
-- Indexes for table `dataset_publication`
--
ALTER TABLE `dataset_publication`
  ADD PRIMARY KEY (`publication_id`,`publication_version_min`,`dataset_id`,`dataset_version_min`),
  ADD KEY `dataset_publication_x_dataset_fk` (`dataset_id`,`dataset_version_min`);

--
-- Indexes for table `dataset_topic`
--
ALTER TABLE `dataset_topic`
  ADD PRIMARY KEY (`vocab_iso_topic_category_id`,`dataset_id`,`dataset_version_min`),
  ADD KEY `dataset_topic_x_dataset_fk` (`dataset_id`,`dataset_version_min`);

--
-- Indexes for table `data_center_person_default`
--
ALTER TABLE `data_center_person_default`
  ADD PRIMARY KEY (`organization_id`,`person_id`),
  ADD KEY `data_center_org_id` (`organization_id`),
  ADD KEY `data_center_person_id` (`person_id`);

--
-- Indexes for table `data_resolution`
--
ALTER TABLE `data_resolution`
  ADD PRIMARY KEY (`data_resolution_id`),
  ADD KEY `data_resolution_x_dataset_fk` (`dataset_id`,`dataset_version_min`),
  ADD KEY `data_resolution_x_vocab_res_hor_fk` (`vocab_res_hor_id`),
  ADD KEY `data_resolution_x_vocab_res_time_fk` (`vocab_res_time_id`),
  ADD KEY `data_resolution_x_vocab_res_vert_fk` (`vocab_res_vert_id`);

--
-- Indexes for table `distribution`
--
ALTER TABLE `distribution`
  ADD PRIMARY KEY (`distribution_id`),
  ADD KEY `distribution_x_dataset_fk` (`dataset_id`,`dataset_version_min`);

--
-- Indexes for table `file`
--
ALTER TABLE `file`
  ADD PRIMARY KEY (`file_id`);

--
-- Indexes for table `instrument`
--
ALTER TABLE `instrument`
  ADD PRIMARY KEY (`instrument_id`),
  ADD KEY `instrument_x_platform_fk` (`platform_id`),
  ADD KEY `instrument_x_vocab_instrument_fk` (`vocab_instrument_id`),
  ADD KEY `old_instrument_id` (`old_instrument_id`);

--
-- Indexes for table `location`
--
ALTER TABLE `location`
  ADD PRIMARY KEY (`location_id`),
  ADD KEY `fki_location_dataset` (`dataset_id`,`dataset_version_min`),
  ADD KEY `fki_location_vocab` (`vocab_location_id`);

--
-- Indexes for table `menu`
--
ALTER TABLE `menu`
  ADD PRIMARY KEY (`menu_id`),
  ADD KEY `fki_parent_menu_id` (`parent_menu_id`);

--
-- Indexes for table `metadata_association`
--
ALTER TABLE `metadata_association`
  ADD PRIMARY KEY (`metadata_association_id`),
  ADD KEY `metadata_association_x_dataset_fk` (`dataset_id`,`dataset_version_min`);

--
-- Indexes for table `mime_type`
--
ALTER TABLE `mime_type`
  ADD PRIMARY KEY (`mime_type_id`);

--
-- Indexes for table `multimedia_sample`
--
ALTER TABLE `multimedia_sample`
  ADD PRIMARY KEY (`multimedia_sample_id`),
  ADD KEY `multimedia_sample_x_dataset_fk` (`dataset_id`,`dataset_version_min`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`news_id`);

--
-- Indexes for table `npp_theme`
--
ALTER TABLE `npp_theme`
  ADD PRIMARY KEY (`npp_theme_id`);

--
-- Indexes for table `organization`
--
ALTER TABLE `organization`
  ADD PRIMARY KEY (`organization_id`),
  ADD KEY `fki_organization_country` (`country_id`) USING BTREE;

--
-- Indexes for table `page`
--
ALTER TABLE `page`
  ADD PRIMARY KEY (`page_id`);

--
-- Indexes for table `page_link`
--
ALTER TABLE `page_link`
  ADD PRIMARY KEY (`page_link_id`),
  ADD KEY `page_link_x_page_fk` (`page_id`);

--
-- Indexes for table `page_person`
--
ALTER TABLE `page_person`
  ADD PRIMARY KEY (`page_id`,`person_id`),
  ADD KEY `page_person_x_person_fk` (`person_id`);

--
-- Indexes for table `person`
--
ALTER TABLE `person`
  ADD PRIMARY KEY (`person_id`),
  ADD KEY `person_x_organization_fk` (`organization_id`),
  ADD KEY `person_x_user_level_fk` (`user_level`);

--
-- Indexes for table `platform`
--
ALTER TABLE `platform`
  ADD PRIMARY KEY (`platform_id`),
  ADD KEY `platform_x_dataset_fk` (`dataset_id`,`dataset_version_min`),
  ADD KEY `platform_x_vocab_platform_fk` (`vocab_platform_id`);

--
-- Indexes for table `program`
--
ALTER TABLE `program`
  ADD PRIMARY KEY (`program_id`);

--
-- Indexes for table `project`
--
ALTER TABLE `project`
  ADD PRIMARY KEY (`project_id`,`project_version`),
  ADD KEY `project_record_status` (`record_status`),
  ADD KEY `project_x_program_fk` (`program_id`),
  ADD KEY `project_x_person_fk` (`creator`),
  ADD KEY `project_x_npp_theme_fk` (`npp_theme_id`);

--
-- Indexes for table `project_keyword`
--
ALTER TABLE `project_keyword`
  ADD PRIMARY KEY (`project_keyword_id`),
  ADD KEY `project_keyword_x_project_fk` (`project_id`,`project_version_min`);

--
-- Indexes for table `project_link`
--
ALTER TABLE `project_link`
  ADD PRIMARY KEY (`project_link_id`),
  ADD KEY `project_link_x_project_fk` (`project_id`,`project_version_min`);

--
-- Indexes for table `project_person`
--
ALTER TABLE `project_person`
  ADD PRIMARY KEY (`person_id`,`project_version_min`,`project_id`),
  ADD KEY `project_person_x_person_fk` (`person_id`),
  ADD KEY `project_person_x_project_fk` (`project_id`,`project_version_min`),
  ADD KEY `project_person_x_organization_fk` (`organization_id`);

--
-- Indexes for table `project_project`
--
ALTER TABLE `project_project`
  ADD PRIMARY KEY (`parent_project_id`,`child_project_id`),
  ADD KEY `child_id` (`child_project_id`);

--
-- Indexes for table `project_publication`
--
ALTER TABLE `project_publication`
  ADD PRIMARY KEY (`project_id`,`project_version_min`,`publication_version_min`,`publication_id`),
  ADD KEY `project_publication_x_project_fk` (`project_id`,`project_version_min`),
  ADD KEY `project_publication_x_publication_fk` (`publication_id`,`publication_version_min`);

--
-- Indexes for table `publication`
--
ALTER TABLE `publication`
  ADD PRIMARY KEY (`publication_id`,`publication_version`),
  ADD KEY `publication_record_status` (`record_status`),
  ADD KEY `publication_x_person_fk` (`creator`),
  ADD KEY `publication_FK` (`publication_type_id`);

--
-- Indexes for table `publication_keyword`
--
ALTER TABLE `publication_keyword`
  ADD PRIMARY KEY (`publication_keyword_id`),
  ADD KEY `publication_keyword_x_publication_fk` (`publication_id`,`publication_version_min`);

--
-- Indexes for table `publication_person`
--
ALTER TABLE `publication_person`
  ADD PRIMARY KEY (`publication_person_id`),
  ADD KEY `publication_person_x_person_fk` (`person_id`),
  ADD KEY `publication_x_organization_fk` (`organization_id`),
  ADD KEY `publication_person_x_publication_fk` (`publication_id`,`publication_version_min`,`person_id`) USING BTREE;

--
-- Indexes for table `publication_type`
--
ALTER TABLE `publication_type`
  ADD PRIMARY KEY (`publication_type_id`);

--
-- Indexes for table `record_status`
--
ALTER TABLE `record_status`
  ADD PRIMARY KEY (`record_status`),
  ADD UNIQUE KEY `record_status_index` (`record_status`);

--
-- Indexes for table `record_status_change`
--
ALTER TABLE `record_status_change`
  ADD PRIMARY KEY (`record_status_change_id`);

--
-- Indexes for table `related_dataset`
--
ALTER TABLE `related_dataset`
  ADD PRIMARY KEY (`related_dataset_id`),
  ADD KEY `related_dataset_x_dataset_fk` (`dataset_id`,`dataset_version_min`);

--
-- Indexes for table `sensor`
--
ALTER TABLE `sensor`
  ADD PRIMARY KEY (`sensor_id`),
  ADD KEY `fki_instrument` (`vocab_instrument_id`),
  ADD KEY `sensor_x_instrument_fk` (`instrument_id`),
  ADD KEY `old_sensor_id` (`old_sensor_id`);

--
-- Indexes for table `spatial_coverage`
--
ALTER TABLE `spatial_coverage`
  ADD PRIMARY KEY (`spatial_coverage_id`),
  ADD KEY `spatial_coverage_x_dataset_fk` (`dataset_id`,`dataset_version_min`);

--
-- Indexes for table `suggestion`
--
ALTER TABLE `suggestion`
  ADD PRIMARY KEY (`suggestion_id`),
  ADD KEY `field` (`field`);

--
-- Indexes for table `temporal_coverage`
--
ALTER TABLE `temporal_coverage`
  ADD PRIMARY KEY (`temporal_coverage_id`),
  ADD KEY `temporal_coverage_x_dataset_fk` (`dataset_id`,`dataset_version_min`);

--
-- Indexes for table `temporal_coverage_ancillary`
--
ALTER TABLE `temporal_coverage_ancillary`
  ADD PRIMARY KEY (`temporal_coverage_ancillary_id`),
  ADD KEY `temporal_coverage_ancillary_x_temporal_coverage_fk` (`temporal_coverage_id`);

--
-- Indexes for table `temporal_coverage_cycle`
--
ALTER TABLE `temporal_coverage_cycle`
  ADD PRIMARY KEY (`temporal_coverage_cycle_id`),
  ADD KEY `temporal_coverage_cycle_x_temporal_coverage_fk` (`temporal_coverage_id`);

--
-- Indexes for table `temporal_coverage_paleo`
--
ALTER TABLE `temporal_coverage_paleo`
  ADD PRIMARY KEY (`temporal_coverage_paleo_id`),
  ADD KEY `temporal_coverage_paleo_x_temporal_coverage_fk` (`temporal_coverage_id`);

--
-- Indexes for table `temporal_coverage_paleo_chronounit`
--
ALTER TABLE `temporal_coverage_paleo_chronounit`
  ADD PRIMARY KEY (`temporal_coverage_paleo_chronounit_id`),
  ADD KEY `FK_temporal_coverage_paleo_chronounit_temporal_coverage_paleo` (`temporal_coverage_paleo_id`),
  ADD KEY `FK_temporal_coverage_paleo_chronounit_vocab_chronounit` (`vocab_chronounit_id`);

--
-- Indexes for table `temporal_coverage_period`
--
ALTER TABLE `temporal_coverage_period`
  ADD PRIMARY KEY (`temporal_coverage_period_id`),
  ADD KEY `temporal_coverage_period_x_temporal_coverage_fk` (`temporal_coverage_id`);

--
-- Indexes for table `user_level`
--
ALTER TABLE `user_level`
  ADD PRIMARY KEY (`user_level_id`),
  ADD UNIQUE KEY `user_level_label` (`label`);

--
-- Indexes for table `vocab`
--
ALTER TABLE `vocab`
  ADD PRIMARY KEY (`vocab_id`);

--
-- Indexes for table `vocab_chronounit`
--
ALTER TABLE `vocab_chronounit`
  ADD PRIMARY KEY (`vocab_chronounit_id`);

--
-- Indexes for table `vocab_idn_node`
--
ALTER TABLE `vocab_idn_node`
  ADD PRIMARY KEY (`vocab_idn_node_id`);

--
-- Indexes for table `vocab_instrument`
--
ALTER TABLE `vocab_instrument`
  ADD PRIMARY KEY (`vocab_instrument_id`);

--
-- Indexes for table `vocab_iso_topic_category`
--
ALTER TABLE `vocab_iso_topic_category`
  ADD PRIMARY KEY (`vocab_iso_topic_category_id`);

--
-- Indexes for table `vocab_location`
--
ALTER TABLE `vocab_location`
  ADD PRIMARY KEY (`vocab_location_id`);

--
-- Indexes for table `vocab_location_vocab_idn_node`
--
ALTER TABLE `vocab_location_vocab_idn_node`
  ADD PRIMARY KEY (`vocab_location_id`,`vocab_idn_node_id`),
  ADD KEY `vocab_location` (`vocab_location_id`),
  ADD KEY `vocab_idn_node` (`vocab_idn_node_id`);

--
-- Indexes for table `vocab_platform`
--
ALTER TABLE `vocab_platform`
  ADD PRIMARY KEY (`vocab_platform_id`);

--
-- Indexes for table `vocab_res_hor`
--
ALTER TABLE `vocab_res_hor`
  ADD PRIMARY KEY (`vocab_res_hor_id`);

--
-- Indexes for table `vocab_res_time`
--
ALTER TABLE `vocab_res_time`
  ADD PRIMARY KEY (`vocab_res_time_id`);

--
-- Indexes for table `vocab_res_vert`
--
ALTER TABLE `vocab_res_vert`
  ADD PRIMARY KEY (`vocab_res_vert_id`);

--
-- Indexes for table `vocab_science_keyword`
--
ALTER TABLE `vocab_science_keyword`
  ADD PRIMARY KEY (`vocab_science_keyword_id`);

--
-- Indexes for table `vocab_url_type`
--
ALTER TABLE `vocab_url_type`
  ADD PRIMARY KEY (`vocab_url_type_id`);

--
-- Indexes for table `zip`
--
ALTER TABLE `zip`
  ADD PRIMARY KEY (`zip_id`),
  ADD KEY `zip_x_person_fk` (`person_id`),
  ADD KEY `dataset_id` (`dataset_id`);

--
-- Indexes for table `zip_files`
--
ALTER TABLE `zip_files`
  ADD PRIMARY KEY (`zip_files_id`),
  ADD KEY `zip_files_x_file_fk` (`file_id`),
  ADD KEY `zip_files_x_zip_fk` (`zip_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `access_request`
--
ALTER TABLE `access_request`
  MODIFY `access_request_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `access_request_file`
--
ALTER TABLE `access_request_file`
  MODIFY `access_request_file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `account_new`
--
ALTER TABLE `account_new`
  MODIFY `account_new_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `account_reset`
--
ALTER TABLE `account_reset`
  MODIFY `account_reset_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `additional_attributes`
--
ALTER TABLE `additional_attributes`
  MODIFY `additional_attributes_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `characteristics`
--
ALTER TABLE `characteristics`
  MODIFY `characteristics_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `contact`
--
ALTER TABLE `contact`
  MODIFY `contact_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dataset`
--
ALTER TABLE `dataset`
  MODIFY `dataset_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dataset_ancillary_keyword`
--
ALTER TABLE `dataset_ancillary_keyword`
  MODIFY `dataset_ancillary_keyword_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dataset_citation`
--
ALTER TABLE `dataset_citation`
  MODIFY `dataset_citation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dataset_data_center`
--
ALTER TABLE `dataset_data_center`
  MODIFY `dataset_data_center_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dataset_data_center_person`
--
ALTER TABLE `dataset_data_center_person`
  MODIFY `dataset_data_center_person_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dataset_keyword`
--
ALTER TABLE `dataset_keyword`
  MODIFY `dataset_keyword_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dataset_link`
--
ALTER TABLE `dataset_link`
  MODIFY `dataset_link_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dataset_link_url`
--
ALTER TABLE `dataset_link_url`
  MODIFY `dataset_link_url_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `data_resolution`
--
ALTER TABLE `data_resolution`
  MODIFY `data_resolution_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `distribution`
--
ALTER TABLE `distribution`
  MODIFY `distribution_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `file`
--
ALTER TABLE `file`
  MODIFY `file_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `instrument`
--
ALTER TABLE `instrument`
  MODIFY `instrument_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `location`
--
ALTER TABLE `location`
  MODIFY `location_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `menu`
--
ALTER TABLE `menu`
  MODIFY `menu_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `metadata_association`
--
ALTER TABLE `metadata_association`
  MODIFY `metadata_association_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `mime_type`
--
ALTER TABLE `mime_type`
  MODIFY `mime_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `multimedia_sample`
--
ALTER TABLE `multimedia_sample`
  MODIFY `multimedia_sample_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `news_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `npp_theme`
--
ALTER TABLE `npp_theme`
  MODIFY `npp_theme_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `organization`
--
ALTER TABLE `organization`
  MODIFY `organization_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `page`
--
ALTER TABLE `page`
  MODIFY `page_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `page_link`
--
ALTER TABLE `page_link`
  MODIFY `page_link_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `person`
--
ALTER TABLE `person`
  MODIFY `person_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `platform`
--
ALTER TABLE `platform`
  MODIFY `platform_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `program`
--
ALTER TABLE `program`
  MODIFY `program_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project`
--
ALTER TABLE `project`
  MODIFY `project_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_keyword`
--
ALTER TABLE `project_keyword`
  MODIFY `project_keyword_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `project_link`
--
ALTER TABLE `project_link`
  MODIFY `project_link_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `publication`
--
ALTER TABLE `publication`
  MODIFY `publication_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `publication_keyword`
--
ALTER TABLE `publication_keyword`
  MODIFY `publication_keyword_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `publication_person`
--
ALTER TABLE `publication_person`
  MODIFY `publication_person_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `publication_type`
--
ALTER TABLE `publication_type`
  MODIFY `publication_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `record_status_change`
--
ALTER TABLE `record_status_change`
  MODIFY `record_status_change_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `related_dataset`
--
ALTER TABLE `related_dataset`
  MODIFY `related_dataset_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sensor`
--
ALTER TABLE `sensor`
  MODIFY `sensor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `spatial_coverage`
--
ALTER TABLE `spatial_coverage`
  MODIFY `spatial_coverage_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `suggestion`
--
ALTER TABLE `suggestion`
  MODIFY `suggestion_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temporal_coverage`
--
ALTER TABLE `temporal_coverage`
  MODIFY `temporal_coverage_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temporal_coverage_ancillary`
--
ALTER TABLE `temporal_coverage_ancillary`
  MODIFY `temporal_coverage_ancillary_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temporal_coverage_cycle`
--
ALTER TABLE `temporal_coverage_cycle`
  MODIFY `temporal_coverage_cycle_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temporal_coverage_paleo`
--
ALTER TABLE `temporal_coverage_paleo`
  MODIFY `temporal_coverage_paleo_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temporal_coverage_paleo_chronounit`
--
ALTER TABLE `temporal_coverage_paleo_chronounit`
  MODIFY `temporal_coverage_paleo_chronounit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `temporal_coverage_period`
--
ALTER TABLE `temporal_coverage_period`
  MODIFY `temporal_coverage_period_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user_level`
--
ALTER TABLE `user_level`
  MODIFY `user_level_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `vocab_chronounit`
--
ALTER TABLE `vocab_chronounit`
  MODIFY `vocab_chronounit_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vocab_idn_node`
--
ALTER TABLE `vocab_idn_node`
  MODIFY `vocab_idn_node_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vocab_instrument`
--
ALTER TABLE `vocab_instrument`
  MODIFY `vocab_instrument_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vocab_iso_topic_category`
--
ALTER TABLE `vocab_iso_topic_category`
  MODIFY `vocab_iso_topic_category_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vocab_location`
--
ALTER TABLE `vocab_location`
  MODIFY `vocab_location_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vocab_platform`
--
ALTER TABLE `vocab_platform`
  MODIFY `vocab_platform_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vocab_res_hor`
--
ALTER TABLE `vocab_res_hor`
  MODIFY `vocab_res_hor_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vocab_res_time`
--
ALTER TABLE `vocab_res_time`
  MODIFY `vocab_res_time_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vocab_res_vert`
--
ALTER TABLE `vocab_res_vert`
  MODIFY `vocab_res_vert_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vocab_science_keyword`
--
ALTER TABLE `vocab_science_keyword`
  MODIFY `vocab_science_keyword_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `vocab_url_type`
--
ALTER TABLE `vocab_url_type`
  MODIFY `vocab_url_type_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zip`
--
ALTER TABLE `zip`
  MODIFY `zip_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `zip_files`
--
ALTER TABLE `zip_files`
  MODIFY `zip_files_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `access_request`
--
ALTER TABLE `access_request`
  ADD CONSTRAINT `access_request_x_person_fk` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `access_request_x_person_responder_fk` FOREIGN KEY (`responder_id`) REFERENCES `person` (`person_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `access_request_x_zip_fk` FOREIGN KEY (`zip_id`) REFERENCES `zip` (`zip_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `access_request_file`
--
ALTER TABLE `access_request_file`
  ADD CONSTRAINT `access_request_file_x_access_request_fk` FOREIGN KEY (`access_request_id`) REFERENCES `access_request` (`access_request_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `access_request_file_x_file_fk` FOREIGN KEY (`file_id`) REFERENCES `file` (`file_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `account_reset`
--
ALTER TABLE `account_reset`
  ADD CONSTRAINT `account_reset_x_person_fk` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `additional_attributes`
--
ALTER TABLE `additional_attributes`
  ADD CONSTRAINT `additional_attributes_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `characteristics`
--
ALTER TABLE `characteristics`
  ADD CONSTRAINT `characteristics_x_instrument_fk` FOREIGN KEY (`instrument_id`) REFERENCES `instrument` (`instrument_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `characteristics_x_platform_fk` FOREIGN KEY (`platform_id`) REFERENCES `platform` (`platform_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `characteristics_x_sensor_fk` FOREIGN KEY (`sensor_id`) REFERENCES `sensor` (`sensor_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `country`
--
ALTER TABLE `country`
  ADD CONSTRAINT `country_x_continent_fk` FOREIGN KEY (`continent_id`) REFERENCES `continent` (`continent_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dataset`
--
ALTER TABLE `dataset`
  ADD CONSTRAINT `dataset_x_organization_fk` FOREIGN KEY (`originating_center`) REFERENCES `organization` (`organization_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `dataset_x_person_fk` FOREIGN KEY (`creator`) REFERENCES `person` (`person_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `dataset_x_record_status_fk` FOREIGN KEY (`record_status`) REFERENCES `record_status` (`record_status`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `dataset_ancillary_keyword`
--
ALTER TABLE `dataset_ancillary_keyword`
  ADD CONSTRAINT `dataset_ancillary_keyword_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dataset_citation`
--
ALTER TABLE `dataset_citation`
  ADD CONSTRAINT `dataset_citation_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dataset_data_center`
--
ALTER TABLE `dataset_data_center`
  ADD CONSTRAINT `dataset_data_center_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dataset_data_center_x_organization_fk` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`organization_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dataset_data_center_person`
--
ALTER TABLE `dataset_data_center_person`
  ADD CONSTRAINT `dataset_data_center_person_x_dataset_data_center_fk` FOREIGN KEY (`dataset_data_center_id`) REFERENCES `dataset_data_center` (`dataset_data_center_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dataset_data_center_person_x_person_fk` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dataset_file`
--
ALTER TABLE `dataset_file`
  ADD CONSTRAINT `dataset_file_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dataset_file_x_file_fk` FOREIGN KEY (`file_id`) REFERENCES `file` (`file_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dataset_keyword`
--
ALTER TABLE `dataset_keyword`
  ADD CONSTRAINT `dataset_keyword_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dataset_keyword_x_vocab_science_keyword_fk` FOREIGN KEY (`vocab_science_keyword_id`) REFERENCES `vocab_science_keyword` (`vocab_science_keyword_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `dataset_link`
--
ALTER TABLE `dataset_link`
  ADD CONSTRAINT `dataset_link_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dataset_link_x_mime_type_fk` FOREIGN KEY (`mime_type_id`) REFERENCES `mime_type` (`mime_type_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `dataset_link_x_vocab_url_type_fk` FOREIGN KEY (`vocab_url_type_id`) REFERENCES `vocab_url_type` (`vocab_url_type_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `dataset_link_url`
--
ALTER TABLE `dataset_link_url`
  ADD CONSTRAINT `dataset_link_url_x_dataset_link_fk` FOREIGN KEY (`dataset_link_id`) REFERENCES `dataset_link` (`dataset_link_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `old_dataset_link_url_id` FOREIGN KEY (`old_dataset_link_url_id`) REFERENCES `dataset_link_url` (`dataset_link_url_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `dataset_person`
--
ALTER TABLE `dataset_person`
  ADD CONSTRAINT `dataset_person_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dataset_person_x_person_fk` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `dataset_x_org_fk` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`organization_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `dataset_project`
--
ALTER TABLE `dataset_project`
  ADD CONSTRAINT `dataset_project_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dataset_project_x_project_fk` FOREIGN KEY (`project_id`,`project_version_min`) REFERENCES `project` (`project_id`, `project_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dataset_publication`
--
ALTER TABLE `dataset_publication`
  ADD CONSTRAINT `dataset_publication_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dataset_publication_x_publication_fk` FOREIGN KEY (`publication_id`,`publication_version_min`) REFERENCES `publication` (`publication_id`, `publication_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `dataset_topic`
--
ALTER TABLE `dataset_topic`
  ADD CONSTRAINT `dataset_topic_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `dataset_topic_x_vocab_iso_topic_category_fk` FOREIGN KEY (`vocab_iso_topic_category_id`) REFERENCES `vocab_iso_topic_category` (`vocab_iso_topic_category_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `data_center_person_default`
--
ALTER TABLE `data_center_person_default`
  ADD CONSTRAINT `data_center_person_default_x_organization_fk` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`organization_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `data_center_person_default_x_person_fk` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`) ON UPDATE CASCADE;

--
-- Constraints for table `data_resolution`
--
ALTER TABLE `data_resolution`
  ADD CONSTRAINT `data_resolution_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `data_resolution_x_vocab_res_hor_fk` FOREIGN KEY (`vocab_res_hor_id`) REFERENCES `vocab_res_hor` (`vocab_res_hor_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `data_resolution_x_vocab_res_time_fk` FOREIGN KEY (`vocab_res_time_id`) REFERENCES `vocab_res_time` (`vocab_res_time_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `data_resolution_x_vocab_res_vert_fk` FOREIGN KEY (`vocab_res_vert_id`) REFERENCES `vocab_res_vert` (`vocab_res_vert_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `distribution`
--
ALTER TABLE `distribution`
  ADD CONSTRAINT `distribution_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `instrument`
--
ALTER TABLE `instrument`
  ADD CONSTRAINT `instrument_x_platform_fk` FOREIGN KEY (`platform_id`) REFERENCES `platform` (`platform_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `instrument_x_vocab_instrument_fk` FOREIGN KEY (`vocab_instrument_id`) REFERENCES `vocab_instrument` (`vocab_instrument_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `old_instrument_id` FOREIGN KEY (`old_instrument_id`) REFERENCES `instrument` (`instrument_id`) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table `location`
--
ALTER TABLE `location`
  ADD CONSTRAINT `location_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `location_x_vocab_location_fk` FOREIGN KEY (`vocab_location_id`) REFERENCES `vocab_location` (`vocab_location_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `menu`
--
ALTER TABLE `menu`
  ADD CONSTRAINT `menu_x_menu_fk` FOREIGN KEY (`parent_menu_id`) REFERENCES `menu` (`menu_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `metadata_association`
--
ALTER TABLE `metadata_association`
  ADD CONSTRAINT `metadata_association_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `multimedia_sample`
--
ALTER TABLE `multimedia_sample`
  ADD CONSTRAINT `multimedia_sample_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `organization`
--
ALTER TABLE `organization`
  ADD CONSTRAINT `organization_x_country_fk` FOREIGN KEY (`country_id`) REFERENCES `country` (`country_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `page_link`
--
ALTER TABLE `page_link`
  ADD CONSTRAINT `page_link_x_page_fk` FOREIGN KEY (`page_id`) REFERENCES `page` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `page_person`
--
ALTER TABLE `page_person`
  ADD CONSTRAINT `page_person_x_page_fk` FOREIGN KEY (`page_id`) REFERENCES `page` (`page_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `page_person_x_person_fk` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `person`
--
ALTER TABLE `person`
  ADD CONSTRAINT `person_x_organization_fk` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`organization_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `person_x_user_level_fk` FOREIGN KEY (`user_level`) REFERENCES `user_level` (`label`);

--
-- Constraints for table `platform`
--
ALTER TABLE `platform`
  ADD CONSTRAINT `platform_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `platform_x_vocab_platform_fk` FOREIGN KEY (`vocab_platform_id`) REFERENCES `vocab_platform` (`vocab_platform_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `project`
--
ALTER TABLE `project`
  ADD CONSTRAINT `project_x_npp_theme_fk` FOREIGN KEY (`npp_theme_id`) REFERENCES `npp_theme` (`npp_theme_id`),
  ADD CONSTRAINT `project_x_person_fk` FOREIGN KEY (`creator`) REFERENCES `person` (`person_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `project_x_program_fk` FOREIGN KEY (`program_id`) REFERENCES `program` (`program_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `project_x_record_status_fk` FOREIGN KEY (`record_status`) REFERENCES `record_status` (`record_status`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `project_keyword`
--
ALTER TABLE `project_keyword`
  ADD CONSTRAINT `project_keyword_x_project_fk` FOREIGN KEY (`project_id`,`project_version_min`) REFERENCES `project` (`project_id`, `project_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project_link`
--
ALTER TABLE `project_link`
  ADD CONSTRAINT `project_link_project_fk` FOREIGN KEY (`project_id`,`project_version_min`) REFERENCES `project` (`project_id`, `project_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project_person`
--
ALTER TABLE `project_person`
  ADD CONSTRAINT `project_person_x_organization_fk` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`organization_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_person_x_person_fk` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `project_person_x_project_fk` FOREIGN KEY (`project_id`,`project_version_min`) REFERENCES `project` (`project_id`, `project_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project_project`
--
ALTER TABLE `project_project`
  ADD CONSTRAINT `child_id` FOREIGN KEY (`child_project_id`) REFERENCES `project` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `parent_id` FOREIGN KEY (`parent_project_id`) REFERENCES `project` (`project_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `project_publication`
--
ALTER TABLE `project_publication`
  ADD CONSTRAINT `project_publication_x_project_fk` FOREIGN KEY (`project_id`,`project_version_min`) REFERENCES `project` (`project_id`, `project_version`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `project_publication_x_publication_fk` FOREIGN KEY (`publication_id`,`publication_version_min`) REFERENCES `publication` (`publication_id`, `publication_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `publication`
--
ALTER TABLE `publication`
  ADD CONSTRAINT `publication_FK` FOREIGN KEY (`publication_type_id`) REFERENCES `publication_type` (`publication_type_id`),
  ADD CONSTRAINT `publication_x_person_fk` FOREIGN KEY (`creator`) REFERENCES `person` (`person_id`) ON UPDATE CASCADE,
  ADD CONSTRAINT `publication_x_record_status_fk` FOREIGN KEY (`record_status`) REFERENCES `record_status` (`record_status`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `publication_keyword`
--
ALTER TABLE `publication_keyword`
  ADD CONSTRAINT `publication_keyword_x_publication_fk` FOREIGN KEY (`publication_id`,`publication_version_min`) REFERENCES `publication` (`publication_id`, `publication_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `publication_person`
--
ALTER TABLE `publication_person`
  ADD CONSTRAINT `publication_person_x_organization_fk` FOREIGN KEY (`organization_id`) REFERENCES `organization` (`organization_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `publication_person_x_person_fk` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`) ON DELETE NO ACTION ON UPDATE CASCADE,
  ADD CONSTRAINT `publication_person_x_publication_fk` FOREIGN KEY (`publication_id`,`publication_version_min`) REFERENCES `publication` (`publication_id`, `publication_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `related_dataset`
--
ALTER TABLE `related_dataset`
  ADD CONSTRAINT `related_dataset_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON UPDATE CASCADE;

--
-- Constraints for table `sensor`
--
ALTER TABLE `sensor`
  ADD CONSTRAINT `old_sensor_id` FOREIGN KEY (`old_sensor_id`) REFERENCES `sensor` (`sensor_id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `sensor_x_instrument_fk` FOREIGN KEY (`instrument_id`) REFERENCES `instrument` (`instrument_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `sensor_x_vocab_instrument_fk` FOREIGN KEY (`vocab_instrument_id`) REFERENCES `vocab_instrument` (`vocab_instrument_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `spatial_coverage`
--
ALTER TABLE `spatial_coverage`
  ADD CONSTRAINT `spatial_coverage_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `temporal_coverage`
--
ALTER TABLE `temporal_coverage`
  ADD CONSTRAINT `temporal_coverage_x_dataset_fk` FOREIGN KEY (`dataset_id`,`dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `temporal_coverage_ancillary`
--
ALTER TABLE `temporal_coverage_ancillary`
  ADD CONSTRAINT `temporal_coverage_ancillary_x_temporal_coverage_fk` FOREIGN KEY (`temporal_coverage_id`) REFERENCES `temporal_coverage` (`temporal_coverage_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `temporal_coverage_cycle`
--
ALTER TABLE `temporal_coverage_cycle`
  ADD CONSTRAINT `temporal_coverage_cycle_x_temporal_coverage_fk` FOREIGN KEY (`temporal_coverage_id`) REFERENCES `temporal_coverage` (`temporal_coverage_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `temporal_coverage_paleo`
--
ALTER TABLE `temporal_coverage_paleo`
  ADD CONSTRAINT `temporal_coverage_paleo_temporal_coverage_FK` FOREIGN KEY (`temporal_coverage_id`) REFERENCES `temporal_coverage` (`temporal_coverage_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `temporal_coverage_paleo_chronounit`
--
ALTER TABLE `temporal_coverage_paleo_chronounit`
  ADD CONSTRAINT `FK_temporal_coverage_paleo_chronounit_temporal_coverage_paleo` FOREIGN KEY (`temporal_coverage_paleo_id`) REFERENCES `temporal_coverage_paleo` (`temporal_coverage_paleo_id`),
  ADD CONSTRAINT `FK_temporal_coverage_paleo_chronounit_vocab_chronounit` FOREIGN KEY (`vocab_chronounit_id`) REFERENCES `vocab_chronounit` (`vocab_chronounit_id`);

--
-- Constraints for table `temporal_coverage_period`
--
ALTER TABLE `temporal_coverage_period`
  ADD CONSTRAINT `temporal_coverage_period_x_temporal_coverage_fk` FOREIGN KEY (`temporal_coverage_id`) REFERENCES `temporal_coverage` (`temporal_coverage_id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table `vocab_location_vocab_idn_node`
--
ALTER TABLE `vocab_location_vocab_idn_node`
  ADD CONSTRAINT `vocab_location_vocab_idn_node_idn_node` FOREIGN KEY (`vocab_idn_node_id`) REFERENCES `vocab_idn_node` (`vocab_idn_node_id`),
  ADD CONSTRAINT `vocab_location_vocab_idn_node_location` FOREIGN KEY (`vocab_location_id`) REFERENCES `vocab_location` (`vocab_location_id`);

--
-- Constraints for table `zip`
--
ALTER TABLE `zip`
  ADD CONSTRAINT `zip_x_person_fk` FOREIGN KEY (`person_id`) REFERENCES `person` (`person_id`) ON DELETE NO ACTION ON UPDATE CASCADE;

--
-- Constraints for table `zip_files`
--
ALTER TABLE `zip_files`
  ADD CONSTRAINT `zip_files_x_file_fk` FOREIGN KEY (`file_id`) REFERENCES `file` (`file_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `zip_files_x_zip_fk` FOREIGN KEY (`zip_id`) REFERENCES `zip` (`zip_id`) ON DELETE CASCADE ON UPDATE CASCADE;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;

/*Function taken from https://snippets.aktagon.com/snippets/610-levenshtein-distance-for-mysql */
DELIMITER $$
CREATE FUNCTION levenshtein( s1 VARCHAR(255), s2 VARCHAR(255) ) 
  RETURNS INT 
  DETERMINISTIC 
  BEGIN 
    DECLARE s1_len, s2_len, i, j, c, c_temp, cost INT; 
    DECLARE s1_char CHAR; 
    -- max strlen=255 
    DECLARE cv0, cv1 VARBINARY(256); 
    SET s1_len = CHAR_LENGTH(s1), s2_len = CHAR_LENGTH(s2), cv1 = 0x00, j = 1, i = 1, c = 0; 
    IF s1 = s2 THEN 
      RETURN 0; 
    ELSEIF s1_len = 0 THEN 
      RETURN s2_len; 
    ELSEIF s2_len = 0 THEN 
      RETURN s1_len; 
    ELSE 
      WHILE j <= s2_len DO 
        SET cv1 = CONCAT(cv1, UNHEX(HEX(j))), j = j + 1; 
      END WHILE; 
      WHILE i <= s1_len DO 
        SET s1_char = SUBSTRING(s1, i, 1), c = i, cv0 = UNHEX(HEX(i)), j = 1; 
        WHILE j <= s2_len DO 
          SET c = c + 1; 
          IF s1_char = SUBSTRING(s2, j, 1) THEN  
            SET cost = 0; ELSE SET cost = 1; 
          END IF; 
          SET c_temp = CONV(HEX(SUBSTRING(cv1, j, 1)), 16, 10) + cost; 
          IF c > c_temp THEN SET c = c_temp; END IF; 
            SET c_temp = CONV(HEX(SUBSTRING(cv1, j+1, 1)), 16, 10) + 1; 
            IF c > c_temp THEN  
              SET c = c_temp;  
            END IF; 
            SET cv0 = CONCAT(cv0, UNHEX(HEX(c))), j = j + 1; 
        END WHILE; 
        SET cv1 = cv0, i = i + 1; 
      END WHILE; 
    END IF; 
    RETURN c; 
  END$$


CREATE FUNCTION levenshtein_ratio( s1 VARCHAR(255), s2 VARCHAR(255) ) 
  RETURNS INT 
  DETERMINISTIC 
  BEGIN 
    DECLARE s1_len, s2_len, max_len INT; 
    SET s1_len = LENGTH(s1), s2_len = LENGTH(s2); 
    IF s1_len > s2_len THEN  
      SET max_len = s1_len;  
    ELSE  
      SET max_len = s2_len;  
    END IF; 
    RETURN ROUND((1 - LEVENSHTEIN(s1, s2) / max_len) * 100); 
  END$$

DELIMITER ;