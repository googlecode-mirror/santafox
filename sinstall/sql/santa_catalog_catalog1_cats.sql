DROP TABLE IF EXISTS `%PREFIX%_catalog_catalog1_cats`;
CREATE TABLE `%PREFIX%_catalog_catalog1_cats` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `parent_id` int(10) unsigned NOT NULL,
  `order` smallint(3) unsigned default '1',
  `is_default` tinyint(1) unsigned NOT NULL default '0',
  `name` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `is_default` (`is_default`),
  KEY `order` (`order`),
  KEY `parent_id` (`parent_id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;
INSERT INTO `%PREFIX%_catalog_catalog1_cats` VALUES ('5','0','3','0','ЖК телевизоры');
INSERT INTO `%PREFIX%_catalog_catalog1_cats` VALUES ('4','5','2','0','LG');
INSERT INTO `%PREFIX%_catalog_catalog1_cats` VALUES ('3','5','1','1','Hantarex');
INSERT INTO `%PREFIX%_catalog_catalog1_cats` VALUES ('6','5','4','0','Panasonic');
INSERT INTO `%PREFIX%_catalog_catalog1_cats` VALUES ('7','0','5','0','Плазменные телевизоры');
INSERT INTO `%PREFIX%_catalog_catalog1_cats` VALUES ('8','7','3','0','LG');
INSERT INTO `%PREFIX%_catalog_catalog1_cats` VALUES ('9','7','5','0','Panasonic');
INSERT INTO `%PREFIX%_catalog_catalog1_cats` VALUES ('10','7','7','0','Sony');
INSERT INTO `%PREFIX%_catalog_catalog1_cats` VALUES ('11','0','7','0','Пульты ДУ');
INSERT INTO `%PREFIX%_catalog_catalog1_cats` VALUES ('12','11','3','0','Logitech');
INSERT INTO `%PREFIX%_catalog_catalog1_cats` VALUES ('13','11','5','0','Marantz');
INSERT INTO `%PREFIX%_catalog_catalog1_cats` VALUES ('14','11','7','0','Philips');
INSERT INTO `%PREFIX%_catalog_catalog1_cats` VALUES ('15','11','9','0','Nevo');
