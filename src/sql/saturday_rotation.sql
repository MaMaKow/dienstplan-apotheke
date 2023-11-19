CREATE TABLE IF NOT EXISTS `saturday_rotation` (
  `date` date NOT NULL,
  `team_id` tinyint(4) NOT NULL,
  `branch_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`date`,`branch_id`),
  CONSTRAINT `saturday_rotation_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
