CREATE TABLE `pep_wareneingang` (
  `Datum` date NOT NULL,
  `Zeit` time NOT NULL,
  `Anzahl` int(11) NOT NULL,
  `Mandant` int(11) NOT NULL,
  PRIMARY KEY (`Datum`,`Zeit`,`Mandant`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1