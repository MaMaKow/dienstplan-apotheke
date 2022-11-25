CREATE TABLE IF NOT EXISTS `employees_backup` (
  `backup_id` int(11) NOT NULL AUTO_INCREMENT,
  `id` smallint(5) unsigned NOT NULL,
  `last_name` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profession` set('Apotheker','PI','PTA','PKA','Praktikant','Ernährungsberater','Kosmetiker','Zugehfrau') COLLATE utf8mb4_unicode_ci NOT NULL,
  `working_hours` float NOT NULL DEFAULT 40,
  `working_week_hours` float NOT NULL DEFAULT 38.5,
  `holidays` tinyint(11) NOT NULL DEFAULT 28,
  `lunch_break_minutes` tinyint(11) NOT NULL DEFAULT 30,
  `goods_receipt` tinyint(1) DEFAULT NULL,
  `compounding` tinyint(1) DEFAULT NULL,
  `branch` int(11) NOT NULL DEFAULT 1,
  `start_of_employment` date DEFAULT NULL,
  `end_of_employment` date DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`backup_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci