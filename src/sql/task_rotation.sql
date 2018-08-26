CREATE TABLE IF NOT EXISTS `task_rotation` (
  `date` date NOT NULL,
  `task` varchar(64) NOT NULL,
  `VK` tinyint(4) NOT NULL,
  `branch_id` tinyint(4) NOT NULL,
  PRIMARY KEY (`date`,`task`,`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1