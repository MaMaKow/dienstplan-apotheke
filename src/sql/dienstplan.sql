CREATE TABLE `dienstplan` (
  `VK` int(11) NOT NULL,
  `Datum` date NOT NULL,
  `Dienstbeginn` time NOT NULL DEFAULT '00:00:00',
  `Dienstende` time DEFAULT NULL,
  `Mittagsbeginn` time DEFAULT NULL,
  `Mittagsende` time DEFAULT NULL,
  `Kommentar` text,
  `Stunden` float DEFAULT NULL,
  `Mandant` int(11) NOT NULL DEFAULT '1',
  `user` varchar(64) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`VK`,`Datum`,`Dienstbeginn`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1