CREATE TABLE IF NOT EXISTS `opening_times_special` (
  `Datum` date NOT NULL,
  `Beginn` time NOT NULL,
  `Ende` time NOT NULL,
  `Bezeichnung` varchar(64) DEFAULT NULL,
  PRIMARY KEY (`Datum`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1