CREATE TABLE `users_privileges` (
  `user_id` smallint(6) unsigned NOT NULL,
  `privilege` varchar(16) COLLATE latin1_german1_ci NOT NULL,
  PRIMARY KEY (`user_id`,`privilege`),
  CONSTRAINT `id_constraint` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci