CREATE TABLE `wunschplan` (
  `VK` tinyint(11) NOT NULL,
  `Wochentag` tinyint(4) NOT NULL,
  `Dienstbeginn` varchar(64) DEFAULT NULL,
  `Dienstende` varchar(64) DEFAULT NULL,
  `Mittagsbeginn` varchar(64) DEFAULT NULL,
  `Mittagsende` varchar(64) DEFAULT NULL,
  `Kommentar` text,
  `Stunden` varchar(64) DEFAULT NULL,
  `Mandant` varchar(64) NOT NULL DEFAULT '1',
  PRIMARY KEY (`VK`,`Wochentag`,`Mandant`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1