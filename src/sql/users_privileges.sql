CREATE TABLE IF NOT EXISTS `users_privileges` (
  `user_key` int(10) UNSIGNED NOT NULL,
  `privilege` varchar(32) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`user_key`,`privilege`),
  CONSTRAINT `users_privileges_ibfk_1` FOREIGN KEY (`user_key`) REFERENCES `users`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
