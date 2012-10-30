DROP TABLE IF EXISTS `%PREFIX%_catalog_catalog2_cats`;
CREATE TABLE `%PREFIX%_catalog_catalog2_cats` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) unsigned NOT NULL,
  `order` smallint(3) unsigned default '1',
  `is_default` tinyint(1) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `is_default` (`is_default`),
  KEY `parent_id_order` (`parent_id`,`order`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8;
INSERT INTO `%PREFIX%_catalog_catalog2_cats` VALUES ('2','0','3','0','ЖК телевизоры');
INSERT INTO `%PREFIX%_catalog_catalog2_cats` VALUES ('3','0','5','0','Плазменные телевизоры');
INSERT INTO `%PREFIX%_catalog_catalog2_cats` VALUES ('4','0','7','0','Пульты ДУ');
