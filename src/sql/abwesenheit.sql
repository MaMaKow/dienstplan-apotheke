CREATE TABLE `abwesenheit` (
  `VK` int(11) NOT NULL,
  `Grund` varchar(64) NOT NULL,
  `Beginn` date NOT NULL,
  `Ende` date NOT NULL,
  `Tage` int(11) NOT NULL,
  PRIMARY KEY (`VK`,`Beginn`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1