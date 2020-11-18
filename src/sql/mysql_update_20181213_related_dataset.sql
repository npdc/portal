CREATE TABLE `related_dataset` (
  `related_dataset_id` int(11) NOT NULL AUTO_INCREMENT,
  `dataset_id` int(11) NOT NULL,
  `dataset_version_min` int(11) NOT NULL,
  `dataset_version_max` int(11) DEFAULT NULL,
  `url` varchar(100) DEFAULT NULL,
  `doi` varchar(100) DEFAULT NULL,
  `internal_related_dataset_id` int(11) DEFAULT NULL,
  `relation` varchar(255) DEFAULT NULL,
  `same` tinyint(1) NOT NULL,
  PRIMARY KEY (`related_dataset_id`),
  KEY `related_dataset_x_dataset_fk` (`dataset_id`,`dataset_version_min`),
  CONSTRAINT `related_dataset_x_dataset_fk` FOREIGN KEY (`dataset_id`, `dataset_version_min`) REFERENCES `dataset` (`dataset_id`, `dataset_version`) ON UPDATE CASCADE
)