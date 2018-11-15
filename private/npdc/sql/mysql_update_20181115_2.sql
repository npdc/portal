CREATE TABLE `npp_theme` (
  `npp_theme_id` int(11) NOT NULL AUTO_INCREMENT,
  `theme_nl` varchar(100) DEFAULT NULL,
  `theme_en` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`npp_theme_id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4;

ALTER TABLE c2_npdc.program ADD sort SMALLINT NULL;
ALTER TABLE c2_npdc.project MODIFY COLUMN nwo_project_id varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NULL;
ALTER TABLE c2_npdc.project ADD npp_theme_id integer NULL;
ALTER TABLE c2_npdc.project ADD CONSTRAINT project_x_npp_theme_fk FOREIGN KEY (npp_theme_id) REFERENCES c2_npdc.npp_theme(npp_theme_id);

