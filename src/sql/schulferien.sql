CREATE TABLE `schulferien` (
  `Name` varchar(64) NOT NULL,
  `Beginn` date NOT NULL,
  `Ende` date NOT NULL,
  PRIMARY KEY (`Beginn`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1