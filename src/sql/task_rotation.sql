CREATE TABLE IF NOT EXISTS `task_rotation` (
  `date` date NOT NULL,
  `task` varchar(64) NOT NULL,
  `employee_key` int(10) NOT NULL,
  `branch_id` tinyint(4) NOT NULL,
  PRIMARY KEY (`date`,`task`,`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
