CREATE TABLE IF NOT EXISTS `pdr_self` (
  `id` enum('1') COLLATE latin1_german1_ci NOT NULL DEFAULT '1',
  `pdr_version_number` int(6) unsigned zerofill DEFAULT NULL,
  `pdr_version_string` varchar(64) COLLATE latin1_german1_ci DEFAULT NULL,
  `pdr_database_version_hash` char(40) COLLATE latin1_german1_ci DEFAULT NULL,
  `last_execution_of_maintenance` timestamp NULL DEFAULT NULL,
  `principle_roster_start_date` date DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci