CREATE TABLE IF NOT EXISTS `saturday_rotation_teams` (
  `team_id` tinyint(3) unsigned NOT NULL,
  `employee_id` tinyint(4) NOT NULL,
  `branch_id` tinyint(4) NOT NULL,
  PRIMARY KEY (`team_id`,`employee_id`,`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci