CREATE TABLE IF NOT EXISTS `Stunden` (
  `VK` int(11) NOT NULL,
  `Datum` date NOT NULL,
  `Stunden` float DEFAULT NULL,
  `Saldo` float NOT NULL,
  `Grund` varchar(64) DEFAULT NULL,
  `Aktualisierung` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`VK`,`Datum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1