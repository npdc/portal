ALTER TABLE temporal_coverage_paleo ADD CONSTRAINT temporal_coverage_paleo_temporal_coverage_FK FOREIGN KEY (temporal_coverage_id) REFERENCES temporal_coverage(temporal_coverage_id) ON DELETE CASCADE ON UPDATE CASCADE ;

ALTER TABLE user_level MODIFY COLUMN label VARCHAR(9) CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci NOT NULL,
ADD CONSTRAINT user_level_label UNIQUE KEY (label(9));
ALTER TABLE person ADD CONSTRAINT person_x_user_level_fk FOREIGN KEY (user_level) REFERENCES user_level(label) ON DELETE RESTRICT ON UPDATE RESTRICT;