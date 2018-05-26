CREATE TABLE IF NOT EXISTS `pep_month_day` (
  `day` int(11) NOT NULL,
  `factor` float NOT NULL,
  `branch` int(11) NOT NULL,
  PRIMARY KEY (`day`,`branch`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1