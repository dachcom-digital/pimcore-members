DROP TABLE IF EXISTS `members_restrictions`;
CREATE TABLE `members_restrictions` (
  `id` int(11) unsigned NOT NULL DEFAULT '0',
  `targetId` int(11) unsigned NOT NULL DEFAULT '0',
  `inheritable` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id` (`id`,`targetId`)
) DEFAULT CHARSET=utf8;

DROP TABLE IF EXISTS `members_group_relations`;
CREATE TABLE `members_group_relations` (
  `id` int(11) unsigned NOT NULL DEFAULT '0',
  `restrictionId` int(11) unsigned NOT NULL DEFAULT '0',
  `groupId` tinyint(1) unsigned DEFAULT '1',
  PRIMARY KEY (`id`),
  KEY `id` (`restrictionId`,`groupId`)
) DEFAULT CHARSET=utf8;