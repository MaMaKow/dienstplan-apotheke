CREATE TABLE IF NOT EXISTS `principle_roster` (
  `primary_key` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `alternating_week_id` tinyint(4) NOT NULL,
  `employee_key` int(10) unsigned NOT NULL,
  `weekday` tinyint(4) NOT NULL,
  `duty_start` time DEFAULT NULL,
  `duty_end` time DEFAULT NULL,
  `break_start` time DEFAULT NULL,
  `break_end` time DEFAULT NULL,
  `comment` text COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `working_hours` float DEFAULT NULL,
  `branch_id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`primary_key`),
  CONSTRAINT `principle_roster_ibfk_1` FOREIGN KEY (`employee_key`) REFERENCES `employees`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `principle_roster_ibfk_2` FOREIGN KEY (`branch_id`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
