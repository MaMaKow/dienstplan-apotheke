CREATE TABLE IF NOT EXISTS `task_rotation` (
  `date` date NOT NULL,
  `task` varchar(64) NOT NULL,
  `employee_key` int(10) unsigned NOT NULL,
  `branch_id` tinyint(3) unsigned NOT NULL,
  PRIMARY KEY (`date`,`task`,`branch_id`),
  CONSTRAINT `task_rotation_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT,
  CONSTRAINT `task_rotation_ibfk_2` FOREIGN KEY (`employee_key`) REFERENCES `employees`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
