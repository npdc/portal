CREATE TABLE `contact` (
  `contact_id` int(11) NOT NULL AUTO_INCREMENT,
  `receiver` text NOT NULL,
  `sender_mail` text NOT NULL,
  `sender_name` text NOT NULL,
  `subject` text,
  `text` text NOT NULL,
  `country` text COMMENT 'this should be empty, is the anti-spam field',
  `ip` varchar(100) DEFAULT NULL,
  `browser` text,
  `timestamp` datetime DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`contact_id`)
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4