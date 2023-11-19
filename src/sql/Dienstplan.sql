CREATE TABLE IF NOT EXISTS `Dienstplan` (
  `employee_key` int(10) unsigned NOT NULL,
  `Datum` date NOT NULL,
  `Dienstbeginn` time NOT NULL DEFAULT '00:00:00',
  `Dienstende` time DEFAULT NULL,
  `Mittagsbeginn` time DEFAULT NULL,
  `Mittagsende` time DEFAULT NULL,
  `Kommentar` text CHARACTER SET utf8mb4 DEFAULT NULL,
  `Stunden` float DEFAULT NULL,
  `Mandant` int(11) NOT NULL DEFAULT 1,
  `user` varchar(64) CHARACTER SET utf8mb4 NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`employee_key`,`Datum`,`Dienstbeginn`),
  CONSTRAINT `Dienstplan_ibfk_1` FOREIGN KEY (`employee_key`) REFERENCES `employees` (`primary_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
