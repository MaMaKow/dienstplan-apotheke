CREATE TABLE IF NOT EXISTS `saturday_rotation_teams` (
  `team_id` tinyint(3) unsigned NOT NULL,
  `employee_key` int(10) unsigned NOT NULL,
  `branch_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`team_id`,`employee_key`,`branch_id`),
  CONSTRAINT `saturday_rotation_teams_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `saturday_rotation_teams_ibfk_2` FOREIGN KEY (`employee_key`) REFERENCES `employees`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
