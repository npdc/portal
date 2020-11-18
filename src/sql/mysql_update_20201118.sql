CREATE TABLE `license` (
  `license_id` int(11) NOT NULL AUTO_INCREMENT,
  `license` varchar(100) NOT NULL,
  `description` varchar(100) DEFAULT NULL,
  `spdx_url` varchar(100) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  `sort` int(11) NOT NULL DEFAULT 0,
  PRIMARY KEY (`license_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO license (license,description,spdx_url,url,sort) VALUES
('CC-BY', 'Creative Commons Attribution','https://spdx.org/licenses/CC-BY-4.0','https://creativecommons.org/licenses/by/4.0/',1),
('CC0', 'No rights reserved','https://spdx.org/licenses/CC0-1.0','https://creativecommons.org/publicdomain/zero/1.0/',2),
('MIT', 'Open license for software','https://spdx.org/licenses/MIT.html','https://opensource.org/licenses/MIT',3),
('Other', 'please specifiy under access and use constraints',NULL,NULL,999);

ALTER TABLE c2_npdc.dataset ADD license_id int NULL;
ALTER TABLE c2_npdc.dataset ADD CONSTRAINT dataset_x_license_fk FOREIGN KEY (license_id) REFERENCES c2_npdc.license(license_id) ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE c2_npdc.dataset DROP COLUMN license;
