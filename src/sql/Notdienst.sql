CREATE TABLE IF NOT EXISTS `Notdienst` (
  `VK` int(11) DEFAULT NULL,
  `Datum` date NOT NULL,
  `Mandant` int(11) NOT NULL DEFAULT 1,
  PRIMARY KEY (`Datum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1
