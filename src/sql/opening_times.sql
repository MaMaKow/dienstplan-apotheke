CREATE TABLE IF NOT EXISTS `opening_times` (
  `weekday` tinyint(4) NOT NULL,
  `start` time NOT NULL,
  `end` time NOT NULL,
  `branch_id` tinyint(4) NOT NULL,
  PRIMARY KEY (`weekday`,`start`,`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci