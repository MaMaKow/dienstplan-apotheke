CREATE TABLE `öffnungszeiten` (
  `Wochentag` int(11) NOT NULL,
  `Beginn` time NOT NULL,
  `Ende` time NOT NULL,
  `Mandant` int(11) NOT NULL,
  PRIMARY KEY (`Wochentag`,`Beginn`,`Mandant`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1