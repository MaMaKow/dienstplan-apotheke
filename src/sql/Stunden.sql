CREATE TABLE IF NOT EXISTS `Stunden` (
  `employee_key` int(10) NOT NULL,
  `Datum` date NOT NULL,
  `Stunden` float DEFAULT NULL,
  `Saldo` float NOT NULL,
  `Grund` varchar(64) DEFAULT NULL,
  `Aktualisierung` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`employee_key`,`Datum`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
