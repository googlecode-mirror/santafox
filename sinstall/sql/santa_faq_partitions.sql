DROP TABLE IF EXISTS `%PREFIX%_faq_partitions`;
CREATE TABLE `%PREFIX%_faq_partitions` (
  `id` int(11) unsigned NOT NULL auto_increment COMMENT 'ID элемента',
  `name` varchar(255) character set utf8 collate utf8_unicode_ci NOT NULL COMMENT 'Название раздела',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8 COMMENT='Таблица разделов FAQ';
INSERT INTO `%PREFIX%_faq_partitions` VALUES ('2','Вопросы и ответы');
