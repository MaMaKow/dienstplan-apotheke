CREATE TABLE IF NOT EXISTS `pep_weekday_time` (
  `Uhrzeit` time NOT NULL,
  `Wochentag` int(11) NOT NULL COMMENT '0=Monday',
  `Mittelwert` float DEFAULT NULL,
  `Mandant` int(11) NOT NULL,
  PRIMARY KEY (`Uhrzeit`,`Wochentag`,`Mandant`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1