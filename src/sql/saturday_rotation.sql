CREATE TABLE IF NOT EXISTS `saturday_rotation` (
  `date` date NOT NULL,
  `team_id` tinyint(4) NOT NULL,
  `branch_id` tinyint(4) NOT NULL,
  PRIMARY KEY (`date`,`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci