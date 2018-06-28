CREATE TABLE IF NOT EXISTS `pep_bak` (
  `hash` binary(32) NOT NULL,
  `Datum` date NOT NULL,
  `Zeit` time NOT NULL,
  `Anzahl` int(11) NOT NULL,
  `Mandant` int(11) DEFAULT '1',
  PRIMARY KEY (`Datum`,`Zeit`,`Anzahl`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1