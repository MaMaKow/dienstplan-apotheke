CREATE TABLE `approval` (
  `date` date NOT NULL,
  `state` set('approved','not_yet_approved','disapproved','changed_after_approval') NOT NULL,
  `branch` int(11) NOT NULL,
  `user` varchar(64) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`date`,`branch`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1