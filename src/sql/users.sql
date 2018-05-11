CREATE TABLE IF NOT EXISTS `users` (
  `employee_id` tinyint(3) unsigned NOT NULL,
  `user_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` set('deleted','blocked','inactive','active') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'inactive',
  `failed_login_attempts` tinyint(3) unsigned NOT NULL DEFAULT '0',
  `failed_login_attempt_time` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`employee_id`),
  UNIQUE KEY `user_name` (`user_name`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci