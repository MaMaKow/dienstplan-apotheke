CREATE TABLE IF NOT EXISTS `roster` (
  `roster_element_id` int(11) NOT NULL,
  `employee_id` tinyint(3) unsigned NOT NULL,
  `date` date NOT NULL,
  `dtstart` datetime DEFAULT NULL,
  `dtend` datetime DEFAULT NULL,
  `Mittagsbeginn` time DEFAULT NULL,
  `Mittagsende` time DEFAULT NULL,
  `comment` text CHARACTER SET utf8 COLLATE utf8_german2_ci DEFAULT NULL,
  `status` set('confirmed','cancelled','tentative','') CHARACTER SET utf8 COLLATE utf8_german2_ci DEFAULT NULL,
  `working_hours` float DEFAULT NULL,
  `branch_id` tinyint(4) NOT NULL DEFAULT 1,
  `user` varchar(64) CHARACTER SET utf8 COLLATE utf8_german2_ci NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`roster_element_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci