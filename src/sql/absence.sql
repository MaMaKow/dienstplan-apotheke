CREATE TABLE IF NOT EXISTS `absence` (
  `employee_id` tinyint(4) NOT NULL,
  `reason` enum('maternity leave','paid leave of absence','parental leave','remaining holiday','sickness','sickness of child','unpaid leave of absence','vacation') COLLATE latin1_german1_ci NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `days` int(11) NOT NULL,
  `comment` varchar(64) COLLATE latin1_german1_ci DEFAULT NULL,
  `approval` set('approved','not_yet_approved','disapproved','changed_after_approval') CHARACTER SET latin1 NOT NULL DEFAULT 'not_yet_approved',
  `user` varchar(64) CHARACTER SET latin1 NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`employee_id`,`start`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_german1_ci