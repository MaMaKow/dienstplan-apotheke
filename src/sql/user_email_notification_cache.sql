CREATE TABLE IF NOT EXISTS `user_email_notification_cache` (
  `notification_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_id` tinyint(3) unsigned NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `notification_text` text COLLATE latin1_german1_ci NOT NULL,
  `notification_ics_file` blob NOT NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci