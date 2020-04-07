CREATE TABLE IF NOT EXISTS `absence_reasons` (
  `id` tinyint(3) unsigned NOT NULL,
  `reason_string` varchar(32) COLLATE latin1_german1_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci