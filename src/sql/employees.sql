CREATE TABLE IF NOT EXISTS `employees` (
  `primary_key` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `last_name` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  `first_name` varchar(35) COLLATE utf8mb4_unicode_ci NOT NULL,
  `profession` set('Apotheker','PI','PTA','PKA','Praktikant','Ernährungsberater','Kosmetiker','Zugehfrau') COLLATE utf8mb4_unicode_ci NOT NULL,
  `working_week_hours` float NOT NULL DEFAULT 40,
  `holidays` tinyint(11) NOT NULL DEFAULT 28,
  `lunch_break_minutes` tinyint(11) NOT NULL DEFAULT 30,
  `goods_receipt` tinyint(1) DEFAULT NULL,
  `compounding` tinyint(1) DEFAULT NULL,
  `branch` tinyint(3) unsigned NULL DEFAULT NULL,
  `start_of_employment` date DEFAULT NULL,
  `end_of_employment` date DEFAULT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`primary_key`),
  CONSTRAINT `employees_ibfk_1` FOREIGN KEY (`branch`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB  DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
