CREATE TABLE IF NOT EXISTS `opening_times_special` (
  `date` date NOT NULL,
  `start` time NOT NULL,
  `end` time NOT NULL,
  `event_name` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4