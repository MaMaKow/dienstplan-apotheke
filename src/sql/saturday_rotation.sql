CREATE TABLE IF NOT EXISTS `saturday_rotation` (
  `date` date NOT NULL,
  `team_id` tinyint(3) unsigned NOT NULL,
  `branch_id` tinyint(4) NOT NULL,
  PRIMARY KEY (`date`,`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci