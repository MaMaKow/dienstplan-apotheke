CREATE TABLE `absence` (
  `employee_id` tinyint(4) NOT NULL,
  `reason` varchar(64) NOT NULL,
  `start` date NOT NULL,
  `end` date NOT NULL,
  `days` int(11) NOT NULL,
  `approval` set('approved','not_yet_approved','disapproved','changed_after_approval') NOT NULL DEFAULT 'not_yet_approved',
  `user` varchar(64) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`employee_id`,`start`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1