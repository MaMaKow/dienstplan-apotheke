CREATE TABLE IF NOT EXISTS `Grundplan` (
  `VK` tinyint(11) NOT NULL,
  `Wochentag` tinyint(4) NOT NULL,
  `Dienstbeginn` time NOT NULL DEFAULT '00:00:00',
  `Dienstende` time DEFAULT NULL,
  `Mittagsbeginn` time DEFAULT NULL,
  `Mittagsende` time DEFAULT NULL,
  `Kommentar` text COLLATE latin1_german1_ci,
  `Stunden` float DEFAULT NULL,
  `Mandant` int(11) NOT NULL DEFAULT '1',
  PRIMARY KEY (`VK`,`Wochentag`,`Dienstbeginn`,`Mandant`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci