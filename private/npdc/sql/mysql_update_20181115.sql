CREATE TABLE contact (
	contact_id INT NOT NULL AUTO_INCREMENT,
	receiver TEXT NOT NULL,
	sender_mail TEXT NOT NULL,
	sender_name TEXT NOT NULL,
	subject TEXT NULL,
	`text` TEXT NOT NULL,
	country TEXT NULL COMMENT 'this should be empty, is the anti-spam field',
	ip varchar(100) NULL,
  browser TEXT NULL,
	CONSTRAINT contact_PK PRIMARY KEY (contact_id)
)
ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_general_ci;
