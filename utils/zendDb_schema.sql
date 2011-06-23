CREATE TABLE `shibalike_attributes` (
   `key` varchar(255) NOT NULL,
   `value` text,
   `username` varchar(255) NOT NULL,
   PRIMARY KEY (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;