CREATE TABLE IF NOT EXISTS `users` (
  `primary_key`  int(10) UNSIGNED NOT NULL AUTO_INCREMENT,
  `employee_key` int(10) unsigned NULL,
  `user_name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` set('deleted','blocked','inactive','active') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'inactive',
  `failed_login_attempts` tinyint(3) unsigned NOT NULL DEFAULT 0,
  `failed_login_attempt_time` timestamp NULL DEFAULT NULL,
  `receive_emails_on_changed_roster` tinyint(1) NOT NULL DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`primary_key`),
  UNIQUE KEY `user_name` (`user_name`),
  UNIQUE KEY `email` (`email`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`employee_key`) REFERENCES `employees` (`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
