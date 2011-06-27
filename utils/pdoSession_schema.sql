CREATE TABLE `shibalike_sessions` (
   `id` varchar(255) NOT NULL,
   `name` varchar(255) NOT NULL,
   `data` TEXT,
   `time` INT NOT NULL,
   PRIMARY KEY (`id`, `name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;