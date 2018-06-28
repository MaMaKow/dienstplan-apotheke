CREATE TABLE IF NOT EXISTS `users_privileges` (
  `employee_id` tinyint(3) unsigned NOT NULL,
  `privilege` varchar(32) COLLATE latin1_german1_ci NOT NULL,
  PRIMARY KEY (`employee_id`,`privilege`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci