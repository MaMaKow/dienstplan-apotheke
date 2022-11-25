CREATE TABLE IF NOT EXISTS `absence_reasons` (
  `id` tinyint(3) unsigned NOT NULL,
  `reason_string` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci