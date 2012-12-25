DROP TABLE IF EXISTS `%PREFIX%_faq1_content`;
CREATE TABLE `%PREFIX%_faq1_content` (
  `id` int(11) unsigned NOT NULL auto_increment,
  `pid` int(11) unsigned NOT NULL default '0',
  `question` varchar(255) NOT NULL default '',
  `description` mediumtext NOT NULL,
  `answer` text default NULL,
  `user` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `added` datetime NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `pid` (`pid`,`question`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
INSERT INTO `%PREFIX%_faq1_content` VALUES ('2','2','Каким образом производится обмен товара в случае брака?','Я собираюсь у вас приобрести телевизор и хочу заранее уточнить, каким образом можно будет обменять товар в случае брака? Возможно ли это?','<p>Здраствуйте! Обмен товара в случае брака производится по гарантии в рамках законодательства.</p>','natali','','2010-05-20 10:15:30');
INSERT INTO `%PREFIX%_faq1_content` VALUES ('3','2','Вчера заказал пульт по одной цене, а сегодня она выросла на 500 руб. На мой заказ цена тоже возрастёт?','Вчера заказал у вас пульт ДУ. Посмотрел сегодня, а на него цена возросла на 500 руб. Повлияет ли это на стоимость моего заказа? Если да, то почему? Я же заказывал вчера, по другой стоимости.','<p>Здрасвтвуйте! Цена фиксируется на момент заказа и изменению не подлежит. Покупатель получит товар по той цене по которой был осуществлён заказ.</p>','natali','','2010-05-20 10:15:30');
INSERT INTO `%PREFIX%_faq1_content` VALUES ('4','2','Где будет, в случае поломки телевизора производиться ремонт?','Скажите пожалуйста, где будет производится ремонт телевизора в случае его поломки, если я его покупал у Вас','<div>Добрый день! На сайте фирмы производителей есть список авторизированных сервисных центров. Вам нужно зайти на сайт и найти ближайший к вам.<br /></div>','natali','','2010-05-20 10:15:30');
INSERT INTO `%PREFIX%_faq1_content` VALUES ('5','2','Чья сборка телевизора LG 32LG7000?','Хочу приобрести у вас телевизор марки LG 32LG7000 и мне интересно где был собран этот телевизор.','<p>Добрый день! Сборка этого телевизора производится в России.</p>','natali','','2010-05-20 10:15:30');
INSERT INTO `%PREFIX%_faq1_content` VALUES ('6','2','Что означает товар с уценкой?','Что означает товар с уценкой? Это брак частично или совсем?','<div>Здравствуйте! ТОвар с уценкой - это абсолютно работоспособный товар, с официальной гарантией, но имеет внешние дефекты (сколы, царапины, вмятины).</div>','natali','','2010-05-20 10:15:30');
