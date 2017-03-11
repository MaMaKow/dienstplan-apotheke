CREATE TABLE `pep_monat_im_jahr` (
  `Monat` int(11) NOT NULL,
  `25-perzentile` int(11) NOT NULL,
  `Median` int(11) NOT NULL,
  `75-perzentile` int(11) NOT NULL,
  `Mandant` int(11) NOT NULL,
  PRIMARY KEY (`Monat`,`Mandant`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1