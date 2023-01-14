CREATE TABLE IF NOT EXISTS `user_email_notification_cache` (
  `notification_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `employee_key` int(10) unsigned NOT NULL,
  `date` date NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `notification_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `notification_ics_file` blob NOT NULL,
  PRIMARY KEY (`notification_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
