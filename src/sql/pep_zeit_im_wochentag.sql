CREATE TABLE IF NOT EXISTS `pep_zeit_im_wochentag` (
  `Uhrzeit` time NOT NULL,
  `Wochentag` int(11) NOT NULL COMMENT '1=Sonntag',
  `Mittelwert` float DEFAULT NULL,
  `Mandant` int(11) NOT NULL,
  PRIMARY KEY (`Uhrzeit`,`Wochentag`,`Mandant`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1