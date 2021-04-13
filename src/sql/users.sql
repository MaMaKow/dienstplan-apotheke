CREATE TABLE IF NOT EXISTS `users` (
  `employee_id` tinyint(3) unsigned NOT NULL,
  `user_name` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
  `status` set('deleted','blocked','inactive','active') COLLATE utf8_unicode_ci NOT NULL DEFAULT 'inactive',
  `failed_login_attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `failed_login_attempt_time` timestamp NULL DEFAULT NULL,
  `receive_emails_on_changed_roster` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`employee_id`),
  UNIQUE KEY `user_name` (`user_name`),
  UNIQUE KEY `email` (`email`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci