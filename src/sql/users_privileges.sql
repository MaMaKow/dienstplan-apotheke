CREATE TABLE IF NOT EXISTS `users_privileges` (
  `employee_id` tinyint(3) unsigned NOT NULL,
  `privilege` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`employee_id`,`privilege`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci