CREATE TABLE IF NOT EXISTS `principle_roster` (
  `primary_key` int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `alternating_week_id` tinyint(4) NOT NULL,
  `employee_id` tinyint(4) NOT NULL,
  `weekday` tinyint(4) NOT NULL,
  `duty_start` time DEFAULT NULL,
  `duty_end` time DEFAULT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `comment` text COLLATE latin1_german1_ci,
  `working_hours` float DEFAULT NULL,
  `branch_id` int(11) NOT NULL DEFAULT '1',
  `valid_from` date DEFAULT NULL,
  `valid_until` date DEFAULT NULL,
  PRIMARY KEY (`primary_key`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci;
