CREATE TABLE IF NOT EXISTS `pep_tag_im_monat` (
  `Tag` int(11) NOT NULL,
  `25-perzentile` int(11) NOT NULL,
  `Median` int(11) NOT NULL,
  `75-perzentile` int(11) NOT NULL,
  `Mandant` int(11) NOT NULL,
  PRIMARY KEY (`Tag`,`Mandant`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1