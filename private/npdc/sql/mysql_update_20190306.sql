CREATE TABLE `publication_type` (
  `publication_type_id` int(11) NOT NULL AUTO_INCREMENT,
  `label` varchar(100) DEFAULT NULL,
  `bib` varchar(100) NOT NULL,
  `ris` varchar(100) DEFAULT NULL,
  `description` varchar(100) DEFAULT NULL,
  PRIMARY KEY (`publication_type_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO publication_type (`type`,description) VALUES 
('article','An article from a journal or magazine.')
,('book','A book with an explicit publisher.')
,('booklet','A work that is printed and bound, but without a named publisher or sponsoring institution.')
,('conference','The same as inproceedings, included for Scribe compatibility.')
,('inbook','A part of a book, usually untitled. May be a chapter (or section, etc.) and/or a range of pages.')
,('incollection','A part of a book having its own title.')
,('inproceedings','An article in a conference proceedings.')
,('manual','Technical documentation.')
,('mastersthesis','A Master''s thesis.')
,('misc','For use when nothing else fits.')
,('phdthesis','A Ph.D. thesis.')
,('proceedings','The proceedings of a conference.')
,('techreport','A report published by a school or other institution, usually numbered within a series.')
,('unpublished','A document having an author and title, but not formally published.')
;

ALTER TABLE `publication` ADD COLUMN `publication_type_id` int(11);
ALTER TABLE publication ADD CONSTRAINT publication_FK FOREIGN KEY (publication_type_id) REFERENCES c2_npdc.publication_type(publication_type_id) ON DELETE RESTRICT ON UPDATE RESTRICT;
