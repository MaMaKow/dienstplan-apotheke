CREATE TABLE `grundplan` (
  `VK` tinyint(11) NOT NULL,
  `Wochentag` tinyint(4) NOT NULL,
  `Dienstbeginn` time DEFAULT NULL,
  `Dienstende` time DEFAULT NULL,
  `Mittagsbeginn` time DEFAULT NULL,
  `Mittagsende` time DEFAULT NULL,
  `Kommentar` text,
  `Stunden` float DEFAULT NULL,
  `Mandant` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`VK`,`Wochentag`,`Mandant`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1