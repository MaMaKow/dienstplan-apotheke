CREATE TABLE `users_lost_password_token` (
  `employee_id` tinyint(3) unsigned NOT NULL,
  `token` binary(20) NOT NULL,
  `time_created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci