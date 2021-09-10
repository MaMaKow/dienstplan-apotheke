CREATE TABLE IF NOT EXISTS `Feiertage` (
  `Name` varchar(64) NOT NULL,
  `Datum` date NOT NULL,
  PRIMARY KEY (`Datum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1