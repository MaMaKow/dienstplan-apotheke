CREATE TABLE IF NOT EXISTS `principle_roster_archive` (
  `primary_key` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alternating_week_id` tinyint(4) NOT NULL,
  `employee_key` int(10) unsigned NOT NULL,
  `weekday` tinyint(4) NOT NULL,
  `duty_start` time NOT NULL,
  `duty_end` time NOT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `working_hours` float DEFAULT NULL,
  `branch_id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  `was_valid_until` date DEFAULT (CURRENT_DATE),
  PRIMARY KEY (`primary_key`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
