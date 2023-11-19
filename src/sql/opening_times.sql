CREATE TABLE IF NOT EXISTS `opening_times` (
  `weekday` tinyint(4) NOT NULL,
  `start` time NOT NULL,
  `end` time NOT NULL,
  `branch_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`weekday`,`start`,`branch_id`),
  CONSTRAINT `opening_times_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
