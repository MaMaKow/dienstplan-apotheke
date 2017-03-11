CREATE TABLE `mitarbeiter` (
  `VK` int(11) NOT NULL,
  `Nachname` varchar(64) NOT NULL,
  `Vorname` varchar(64) NOT NULL,
  `Ausbildung` set('Apotheker','PI','PTA','PKA','Praktikant','Ernährungsberater','Kosmetiker','Zugehfrau') NOT NULL,
  `Stunden` float NOT NULL DEFAULT '40',
  `Arbeitswochenstunden` float NOT NULL DEFAULT '38.5',
  `Urlaubstage` int(11) NOT NULL DEFAULT '28',
  `Mittag` int(11) NOT NULL DEFAULT '30',
  `Wareneingang` tinyint(1) DEFAULT NULL,
  `Rezeptur` tinyint(1) DEFAULT NULL,
  `Mandant` int(11) NOT NULL DEFAULT '1',
  `Beschäftigungsbeginn` date NOT NULL,
  `Beschäftigungsende` date DEFAULT NULL,
  PRIMARY KEY (`VK`,`Beschäftigungsbeginn`)
) ENGINE=MyISAM DEFAULT CHARSET=latin1