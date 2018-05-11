CREATE TABLE IF NOT EXISTS `branch` (
  `branch_id` tinyint(3) unsigned NOT NULL,
  `name` varchar(64) NOT NULL,
  `short_name` varchar(16) NOT NULL,
  `address` varchar(64) NOT NULL,
  `manager` varchar(64) NOT NULL,
  `PEP` int(11) DEFAULT NULL COMMENT 'Dieser Wert wird vom ASYS PEP-Modul vorgegeben.',
  PRIMARY KEY (`branch_id`)
) ENGINE=InnoDB DEFAULT CHARSET=latin1