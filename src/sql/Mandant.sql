CREATE TABLE IF NOT EXISTS `Mandant` (
  `Mandant` int(11) NOT NULL,
  `Name` varchar(64) NOT NULL,
  `Kurzname` varchar(16) NOT NULL,
  `Adresse` varchar(64) NOT NULL,
  `Leiter` varchar(64) NOT NULL,
  `PEP` int(11) DEFAULT NULL COMMENT 'Dieser Wert wird vom ASYS PEP-Modul vorgegeben.',
  PRIMARY KEY (`Mandant`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1