CREATE TABLE `members_restrictions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `targetId` int(11) unsigned NOT NULL DEFAULT '0',
  `ctype` varchar(255) DEFAULT NULL,
  `inheritable` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_targetId_cType` (`targetId`,`ctype`),
  KEY `index_targetId_cType` (`targetId`,`ctype`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `members_group_relations`;
CREATE TABLE `members_group_relations` (
  `restrictionId` int(11) unsigned NOT NULL DEFAULT '0',
  `groupId` tinyint(1) unsigned DEFAULT '1',
  UNIQUE KEY `groupId` (`groupId`,`restrictionId`),
  KEY `restrictionId` (`restrictionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;