ALTER TABLE user_level ADD COLUMN `selectable` tinyint(1) NOT NULL DEFAULT 1;

UPDATE user_level SET user_level_id=5 WHERE label='nobody';
UPDATE user_level SET user_level_id=4, description='- You can edit all content
- You can take over accounts' WHERE label='admin';
INSERT INTO user_level (user_level_id,label,description,name,selectable) VALUES
	 (3, 'officer','- You can edit all projects, publications and datasets','Program officer',1)