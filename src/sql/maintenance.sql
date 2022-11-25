CREATE TABLE IF NOT EXISTS `maintenance` (
  `id` enum('1') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT '1' COMMENT 'The ENUM(''1'') construct as primary key is used to prevent that more than one row can be entered to the table',
  `last_execution` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci