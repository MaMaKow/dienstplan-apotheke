CREATE TABLE `Dienstplan` (
  `VK` tinyint(3) unsigned NOT NULL,
  `Datum` date NOT NULL,
  `Dienstbeginn` time NOT NULL DEFAULT '00:00:00',
  `Dienstende` time DEFAULT NULL,
  `Mittagsbeginn` time DEFAULT NULL,
  `Mittagsende` time DEFAULT NULL,
  `Kommentar` text CHARACTER SET latin1,
  `Stunden` float DEFAULT NULL,
  `Mandant` int(11) NOT NULL DEFAULT '1',
  `user` varchar(64) CHARACTER SET latin1 NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`VK`,`Datum`,`Dienstbeginn`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci