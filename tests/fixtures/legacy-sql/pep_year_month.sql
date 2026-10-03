CREATE TABLE `pep_year_month` (
  `month` int(11) NOT NULL,
  `factor` float NOT NULL,
  `branch` int(11) NOT NULL,
  PRIMARY KEY (`month`,`branch`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1