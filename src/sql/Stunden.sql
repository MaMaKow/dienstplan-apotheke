CREATE TABLE IF NOT EXISTS `stunden` (
  `VK` int(11) NOT NULL,
  `Datum` date NOT NULL,
  `Stunden` float DEFAULT NULL,
  `Saldo` float NOT NULL,
  `Grund` varchar(64) DEFAULT NULL,
  `Aktualisierung` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`VK`,`Datum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1