CREATE TABLE IF NOT EXISTS `Stunden` (
  `employee_key` int(10) unsigned NOT NULL,
  `Datum` date NOT NULL,
  `Stunden` float DEFAULT NULL,
  `Saldo` float NOT NULL,
  `Grund` varchar(64) DEFAULT NULL,
  `Aktualisierung` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`employee_key`,`Datum`),
  CONSTRAINT `Stunden_ibfk_1` FOREIGN KEY (`employee_key`) REFERENCES `employees`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
