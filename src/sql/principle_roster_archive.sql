CREATE TABLE IF NOT EXISTS `principle_roster_archive` (
  `primary_key` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alternating_week_id` tinyint(4) NOT NULL,
  `employee_id` tinyint(4) NOT NULL,
  `weekday` tinyint(4) NOT NULL,
  `duty_start` time NOT NULL,
  `duty_end` time NOT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `comment` text COLLATE latin1_german1_ci DEFAULT NULL,
  `working_hours` float DEFAULT NULL,
  `branch_id` int(11) NOT NULL DEFAULT 1,
  `was_valid_until` date DEFAULT current_timestamp(),
  PRIMARY KEY (`primary_key`)
) ENGINE=InnoDB  DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci