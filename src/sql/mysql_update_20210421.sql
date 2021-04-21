ALTER TABLE c2_npdc.npp_theme ADD `year` int NOT NULL;
ALTER TABLE c2_npdc.npp_theme ADD npp_id int NOT NULL;
UPDATE npp_theme SET `year`=2011, npp_id=npp_theme_id 

--  Auto-generated SQL script. Actual values for binary/complex data types may differ - what you see is the default string representation of values.
INSERT INTO c2_npdc.npp_theme (theme_en,`year`,npp_id)
	VALUES ('Climate Change',2021,1);
INSERT INTO c2_npdc.npp_theme (theme_en,`year`,npp_id)
	VALUES ('Ecosystem Dynamics',2021,2);
INSERT INTO c2_npdc.npp_theme (theme_en,`year`,npp_id)
	VALUES ('Social Sciences and Humanities',2021,3);
INSERT INTO c2_npdc.npp_theme (theme_en,`year`,npp_id)
	VALUES ('Sustainable Development',2021,4);
    
CREATE TABLE c2_npdc.project_theme (
	project_theme_id int auto_increment NOT NULL,
	npp_theme_id int NOT NULL,
	project_id int NOT NULL,
	project_version_min int NOT NULL,
	project_version_max int NULL,
	CONSTRAINT project_theme_PK PRIMARY KEY (project_theme_id),
	CONSTRAINT project_theme_vs_project FOREIGN KEY (project_id,project_version_min) REFERENCES c2_npdc.project(project_id,project_version) ON DELETE RESTRICT ON UPDATE CASCADE,
	CONSTRAINT project_theme_vs_npp_theme FOREIGN KEY (npp_theme_id) REFERENCES c2_npdc.npp_theme(npp_theme_id) ON DELETE RESTRICT ON UPDATE CASCADE
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;
