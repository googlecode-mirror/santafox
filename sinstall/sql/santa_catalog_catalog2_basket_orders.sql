DROP TABLE IF EXISTS `%PREFIX%_catalog_catalog2_basket_orders`;
CREATE TABLE `%PREFIX%_catalog_catalog2_basket_orders` (
  `sessionid` varchar(32) NOT NULL,
  `lastaccess` datetime default NULL,
  `isprocessed` tinyint(1) unsigned NOT NULL default '0',
  `name` varchar(255) default NULL,
  `email` varchar(255) default NULL,
  PRIMARY KEY  (`sessionid`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
