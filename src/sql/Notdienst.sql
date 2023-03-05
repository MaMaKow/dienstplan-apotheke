CREATE TABLE IF NOT EXISTS `Notdienst` (
  `employee_key` int(10) unsigned DEFAULT NULL,
  `Datum` date NOT NULL,
  `Mandant` tinyint(3) unsigned NOT NULL DEFAULT 1,
  PRIMARY KEY (`Datum`),
  CONSTRAINT `Notdienst_ibfk_1` FOREIGN KEY (`Mandant`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
