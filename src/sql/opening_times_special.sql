CREATE TABLE IF NOT EXISTS `opening_times_special` (
  `date` date NOT NULL,
  `start` time NOT NULL,
  `end` time NOT NULL,
  `event_name` varchar(64) CHARACTER SET latin1 DEFAULT NULL,
  PRIMARY KEY (`date`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci