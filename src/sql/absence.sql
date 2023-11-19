CREATE TABLE IF NOT EXISTS `absence` (
  `employee_key` int(10) unsigned NOT NULL,
  `reason_id` tinyint(3) unsigned NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `days` int(11) NOT NULL,
  `comment` varchar(64) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `approval` set('approved','not_yet_approved','disapproved','changed_after_approval') CHARACTER SET utf8mb4 NOT NULL DEFAULT 'not_yet_approved',
  `user` varchar(64) CHARACTER SET utf8mb4 NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`employee_key`,`start`),
  KEY `reason_id` (`reason_id`),
  CONSTRAINT `absence_ibfk_1` FOREIGN KEY (`reason_id`) REFERENCES `absence_reasons` (`id`) ON UPDATE CASCADE,
  CONSTRAINT `absence_ibfk_2` FOREIGN KEY (`employee_key`) REFERENCES `employees`(`primary_key`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
