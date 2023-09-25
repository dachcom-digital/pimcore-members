CREATE TABLE IF NOT EXISTS `members_restrictions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `targetId` int(11) unsigned NOT NULL DEFAULT '0',
  `ctype` varchar(255) DEFAULT NULL,
  `isInherited` tinyint(1) unsigned DEFAULT '0',
  `inherit` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_targetId_cType` (`targetId`,`ctype`),
  KEY `index_targetId_cType` (`targetId`,`ctype`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;

CREATE TABLE IF NOT EXISTS `members_group_relations` (
  `restrictionId` int(11) unsigned NOT NULL DEFAULT '0',
  `groupId` int(11) unsigned DEFAULT '1',
  UNIQUE KEY `groupId` (`groupId`,`restrictionId`),
  KEY `restrictionId` (`restrictionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;