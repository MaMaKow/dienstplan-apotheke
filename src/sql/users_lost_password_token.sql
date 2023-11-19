CREATE TABLE IF NOT EXISTS `users_lost_password_token` (
  `user_key` int(10) unsigned NOT NULL,
  `token` binary(20) NOT NULL,
  `time_created` timestamp NOT NULL DEFAULT current_timestamp(),
  CONSTRAINT `users_lost_password_token_ibfk_1` FOREIGN KEY (`user_key`) REFERENCES `users`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
