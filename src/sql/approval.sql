CREATE TABLE IF NOT EXISTS `approval` (
  `date` date NOT NULL,
  `state` set('approved','not_yet_approved','disapproved','changed_after_approval') NOT NULL,
  `branch` tinyint(3) unsigned NOT NULL,
  `user` varchar(64) NOT NULL,
  `timestamp` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`date`,`branch`),
  CONSTRAINT `approval_ibfk_1` FOREIGN KEY (`branch`) REFERENCES `branch`(`branch_id`) ON DELETE RESTRICT ON UPDATE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4
