CREATE TABLE IF NOT EXISTS `maintenance` (
  `id` enum('1') COLLATE latin1_german1_ci NOT NULL DEFAULT '1' COMMENT 'The ENUM(''1'') construct as primary key is used to prevent that more than one row can be entered to the table',
  `last_execution` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci