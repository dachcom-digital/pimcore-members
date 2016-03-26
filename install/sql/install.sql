DROP TABLE IF EXISTS `members_restrictions`;
CREATE TABLE `members_restrictions` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `targetId` int(11) unsigned NOT NULL DEFAULT '0',
  `inheritable` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `targetId` (`targetId`,`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `members_group_relations`;
CREATE TABLE `members_group_relations` (
  `restrictionId` int(11) unsigned NOT NULL DEFAULT '0',
  `groupId` tinyint(1) unsigned DEFAULT '1',
  UNIQUE KEY `groupId` (`groupId`,`restrictionId`),
  KEY `restrictionId` (`restrictionId`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;