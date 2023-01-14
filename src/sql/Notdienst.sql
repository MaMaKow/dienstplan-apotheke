CREATE TABLE IF NOT EXISTS `Notdienst` (
  `employee_key` int(10) DEFAULT NULL,
  `Datum` date NOT NULL,
  `Mandant` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Datum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
