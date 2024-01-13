CREATE TABLE IF NOT EXISTS `emergency_services` (
  `primary_key` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_key` int(10) unsigned DEFAULT NULL,
  `date` date NOT NULL,
  `branch_id` tinyint(3) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`primary_key`),
  CONSTRAINT `emergency_services_ibfk_1` FOREIGN KEY (`branch_id`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
