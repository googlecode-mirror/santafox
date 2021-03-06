DROP TABLE IF EXISTS `%PREFIX%_stat_robot`;
CREATE TABLE `%PREFIX%_stat_robot` (
  `IDRobot` int(10) unsigned NOT NULL auto_increment,
  `robot` varchar(30) NOT NULL default '',
  `agent` varchar(255) NOT NULL default '',
  PRIMARY KEY  (`IDRobot`)
) ENGINE=MyISAM AUTO_INCREMENT=331 DEFAULT CHARSET=utf8 COMMENT='Статистика - поисковые роботы';
INSERT INTO `%PREFIX%_stat_robot` VALUES ('1','Aport','aport');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('2','AWBot','awbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('3','BaiDuSpider','baiduspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('4','Bobby','bobby');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('5','Boris','boris');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('6','Bumblebee (relevare.com)','bumblebee');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('7','CsCrawler','cscrawler');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('8','DaviesBot','daviesbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('9','ExactSeek Crawler','exactseek');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('10','Ezresult','ezresult');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('11','GigaBot','gigabot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('12','GNOD Spider','gnodspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('13','Grub.org','grub');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('14','Mirago','henrythemiragorobot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('15','Holmes','holmes');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('16','InternetSeer','internetseer');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('17','JustView','justview');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('18','LinkBot','linkbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('19','MetaGer LinkChecker','metager\\-linkchecker');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('20','LinkChecker','linkchecker');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('21','Microsoft URL Control','microsoft_url_control');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('22','MSIECrawler','msiecrawler');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('23','Nagios','nagios');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('24','Perman surfer','perman');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('25','Pompos','pompos');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('26','StackRambler','rambler');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('27','Red Alert','redalert');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('28','Shoutcast Directory Service','shoutcast');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('29','SlySearch','slysearch');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('30','SurveyBot','surveybot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('31','Turn It In','turnitinbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('32','Turtle','turtlescanner');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('33','Turtle','turtle');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('34','Ultraseek','ultraseek');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('35','WebClipping.com','webclipping\\.com');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('36','webcompass','webcompass');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('37','Web Wombat Redback Spider','wonderer');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('38','Yahoo Vertical Crawler','yahoo\\-verticalcrawler');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('39','Yandex bot','yandex');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('40','ZealBot','zealbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('41','Zyborg','zyborg');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('42','Walhello appie','appie');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('43','ArchitextSpider','architext');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('44','AskJeeves','jeeves');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('45','Bjaaland','bjaaland');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('46','Wild Ferret Web Hopper #1, #2,','ferret');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('47','Googlebot','googlebot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('48','Northern Light Gulliver','gulliver');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('49','Harvest','harvest');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('50','ht://Dig','htdig');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('51','LinkWalker','linkwalker');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('52','Lycos','lycos_');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('53','moget','moget');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('54','Muscat Ferret','muscatferret');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('55','Internet Shinchakubin','myweb');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('56','Nomad','nomad');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('57','Scooter','scooter');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('58','Inktomi Slurp','slurp');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('59','Voyager','^voyager\\/');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('60','weblayers','weblayers');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('61','Antibot','antibot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('62','Digout4u','digout4u');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('63','EchO!','echo');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('64','Fast-Webcrawler','fast\\-webcrawler');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('65','Alexa (IA Archiver)','ia_archiver');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('66','JennyBot','jennybot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('67','Mercator','mercator');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('68','Netcraft','netcraft');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('69','Petersnews','petersnews');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('70','Unlost Web Crawler','unlost_web_crawler');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('71','Voila','voila');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('72','WebBase','webbase');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('73','WISENutbot','wisenutbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('74','Fish search','[^a]fish');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('75','ABCdatos BotLink','abcdatos');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('76','Acme.Spider','acme\\.spider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('77','Ahoy! The Homepage Finder','ahoythehomepagefinder');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('78','Alkaline','alkaline');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('79','Anthill','anthill');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('80','Arachnophilia','arachnophilia');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('81','Arale','arale');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('82','Araneo','araneo');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('83','Aretha','aretha');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('84','ARIADNE','ariadne');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('85','arks','arks');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('86','ASpider (Associative Spider)','aspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('87','ATN Worldwide','atn\\.txt');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('88','Atomz.com Search Robot','atomz');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('89','AURESYS','auresys');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('90','BackRub','backrub');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('91','BBot','bbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('92','Big Brother','bigbrother');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('93','BlackWidow','blackwidow');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('94','Die Blinde Kuh','blindekuh');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('95','Bloodhound','bloodhound');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('96','Borg-Bot','borg\\-bot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('97','bright.net caching robot','brightnet');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('98','BSpider','bspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('99','CACTVS Chemistry Spider','cactvschemistryspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('100','Calif','calif[^r]');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('101','Cassandra','cassandra');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('102','Digimarc Marcspider/CGI','cgireader');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('103','Checkbot','checkbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('104','ChristCrawler.com','christcrawler');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('105','churl','churl');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('106','cIeNcIaFiCcIoN.nEt','cienciaficcion');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('107','Collective','collective');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('108','Combine System','combine');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('109','Conceptbot','conceptbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('110','CoolBot','coolbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('111','Web Core / Roots','core');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('112','XYLEME Robot','cosmos');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('113','Internet Cruiser Robot','cruiser');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('114','Cusco','cusco');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('115','CyberSpyder Link Test','cyberspyder');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('116','Desert Realm Spider','desertrealm');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('117','DeWeb(c) Katalog/Index','deweb');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('118','DienstSpider','dienstspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('119','Digger','digger');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('120','Digital Integrity Robot','diibot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('121','Direct Hit Grabber','direct_hit');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('122','DNAbot','dnabot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('123','DownLoad Express','download_express');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('124','DragonBot','dragonbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('125','DWCP (Dridus\' Web Cataloging P','dwcp');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('126','e-collector','e\\-collector');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('127','EbiNess','ebiness');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('128','ELFINBOT','elfinbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('129','Emacs-w3 Search Engine','emacs');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('130','ananzi','emcspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('131','Esther','esther');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('132','Evliya Celebi','evliyacelebi');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('133','FastCrawler','fastcrawler');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('134','Fluid Dynamics Search Engine r','fdse');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('135','Felix IDE','felix');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('136','FetchRover','fetchrover');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('137','fido','fido');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('138','Hдmдhдkki','finnish');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('139','KIT-Fireball','fireball');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('140','Fouineur','fouineur');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('141','Robot Francoroute','francoroute');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('142','Freecrawl','freecrawl');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('143','FunnelWeb','funnelweb');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('144','gammaSpider, FocusedCrawler','gama');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('145','gazz','gazz');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('146','GCreep','gcreep');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('147','GetBot','getbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('148','GetURL','geturl');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('149','Golem','golem');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('150','Grapnel/0.01 Experiment','grapnel');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('151','Griffon','griffon');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('152','Gromit','gromit');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('153','Gulper Bot','gulperbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('154','HamBot','hambot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('155','havIndex','havindex');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('156','Hometown Spider Pro','hometown');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('157','HTMLgobble','htmlgobble');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('158','Hyper-Decontextualizer','hyperdecontextualizer');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('159','iajaBot','iajabot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('160','Popular Iconoclast','iconoclast');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('161','Ingrid','ilse');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('162','Imagelock','imagelock');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('163','IncyWincy','incywincy');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('164','Informant','informant');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('165','InfoSeek Robot 1.0','infoseek');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('166','Infoseek Sidewinder','infoseeksidewinder');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('167','InfoSpiders','infospider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('168','Inspector Web','inspectorwww');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('169','IntelliAgent','intelliagent');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('170','I, Robot','irobot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('171','Iron33','iron33');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('172','Israeli-search','israelisearch');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('173','JavaBee','javabee');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('174','JBot Java Web Robot','jbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('175','JCrawler','jcrawler');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('176','JoBo Java Web Robot','jobo');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('177','Jobot','jobot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('178','JoeBot','joebot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('179','The Jubii Indexing Robot','jubii');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('180','JumpStation','jumpstation');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('181','image.kapsi.net','kapsi');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('182','Katipo','katipo');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('183','Kilroy','kilroy');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('184','KO_Yappo_Robot','ko_yappo_robot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('185','LabelGrabber','labelgrabber\\.txt');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('186','larbin','larbin');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('187','legs','legs');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('188','Link Validator','linkidator');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('189','LinkScan','linkscan');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('190','Lockon','lockon');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('191','logo.gif Crawler','logo_gif');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('192','Mac WWWWorm','macworm');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('193','Magpie','magpie');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('194','marvin/infoseek','marvin');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('195','Mattie','mattie');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('196','MediaFox','mediafox');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('197','MerzScope','merzscope');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('198','NEC-MeshExplorer','meshexplorer');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('199','MindCrawler','mindcrawler');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('200','mnoGoSearch search engine soft','mnogosearch');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('201','MOMspider','momspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('202','Monster','monster');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('203','Motor','motor');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('204','MSNBot','msnbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('205','Muncher','muncher');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('206','Mwd.Search','mwdsearch');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('207','NDSpider','ndspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('208','Nederland.zoek','nederland\\.zoek');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('209','NetCarta WebMap Engine','netcarta');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('210','NetMechanic','netmechanic');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('211','NetScoop','netscoop');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('212','newscan-online','newscan\\-online');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('213','NHSE Web Forager','nhse');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('214','The NorthStar Robot','northstar');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('215','nzexplorer','nzexplorer');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('216','ObjectsSearch','objectssearch');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('217','Occam','occam');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('218','HKU WWW Octopus','octopus');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('219','Openfind data gatherer','openfind');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('220','Orb Search','orb_search');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('221','Pack Rat','packrat');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('222','PageBoy','pageboy');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('223','ParaSite','parasite');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('224','Patric','patric');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('225','pegasus','pegasus');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('226','The Peregrinator','perignator');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('227','PerlCrawler 1.0','perlcrawler');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('228','Phantom','phantom');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('229','PhpDig','phpdig');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('230','PiltdownMan','piltdownman');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('231','Pimptrain.com\'s robot','pimptrain');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('232','Pioneer','pioneer');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('233','html_analyzer','pitkow');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('234','Portal Juice Spider','pjspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('235','PlumtreeWebAccessor','plumtreewebaccessor');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('236','Poppi','poppi');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('237','PortalB Spider','portalb');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('238','psbot','psbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('239','The Python Robot','python');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('240','Raven Search','raven');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('241','RBSE Spider','rbse');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('242','Resume Robot','resumerobot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('243','RoadHouse Crawling System','rhcs');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('244','Road Runner: The ImageScape Ro','road_runner');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('245','Robbie the Robot','robbie');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('246','ComputingSite Robi/1.0','robi');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('247','RoboCrawl Spider','robocrawl');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('248','RoboFox','robofox');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('249','Robozilla','robozilla');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('250','Roverbot','roverbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('251','RuLeS','rules');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('252','SafetyNet Robot','safetynetrobot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('253','Sleek','search\\-info');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('254','Search.Aus-AU.COM','search_au');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('255','SearchProcess','searchprocess');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('256','Senrigan','senrigan');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('257','SG-Scout','sgscout');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('258','ShagSeeker','shaggy');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('259','Shai\'Hulud','shaihulud');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('260','Sift','sift');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('261','Simmany Robot Ver1.0','simbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('262','Site Valet','site\\-valet');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('263','SiteTech-Rover','sitetech');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('264','Skymob.com','skymob');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('265','SLCrawler','slcrawler');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('266','Smart Spider','smartspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('267','Snooper','snooper');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('268','Solbot','solbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('269','Speedy Spider','speedy');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('270','spider_monkey','spider_monkey');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('271','SpiderBot','spiderbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('272','Spiderline Crawler','spiderline');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('273','SpiderMan','spiderman');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('274','SpiderView(tm)','spiderview');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('275','Spry Wizard Robot','spry');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('276','Site Searcher','ssearcher');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('277','Suke','suke');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('278','suntek search engine','suntek');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('279','Sven','sven');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('280','TACH Black Widow','tach_bw');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('281','Tarantula','tarantula');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('282','tarspider','tarspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('283','TechBOT','techbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('284','Templeton','templeton');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('285','TITAN','titan');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('286','TitIn','titin');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('287','The TkWWW Robot','tkwww');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('288','TLSpider','tlspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('289','UCSD Crawl','ucsd');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('290','UdmSearch','udmsearch');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('291','URL Check','urlck');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('292','Valkyrie','valkyrie');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('293','Verticrawl','verticrawl');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('294','Victoria','victoria');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('295','vision-search','visionsearch');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('296','void-bot','voidbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('297','VWbot','vwbot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('298','The NWI Robot','w3index');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('299','W3M2','w3m2');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('300','WallPaper (alias crawlpaper)','wallpaper');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('301','the World Wide Web Wanderer','wanderer');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('302','w@pSpider by wap4.com','wapspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('303','WebBandit Web Spider','webbandit');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('304','WebCatcher','webcatcher');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('305','WebCopy','webcopy');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('306','webfetcher','webfetcher');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('307','The Webfoot Robot','webfoot');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('308','Webinator','webinator');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('309','WebLinker','weblinker');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('310','WebMirror','webmirror');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('311','The Web Moose','webmoose');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('312','WebQuest','webquest');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('313','Digimarc MarcSpider','webreader');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('314','WebReaper','webreaper');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('315','Websnarf','websnarf');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('316','WebSpider','webspider');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('317','WebVac','webvac');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('318','webwalk','webwalk');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('319','WebWalker','webwalker');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('320','WebWatch','webwatch');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('321','whatUseek Winona','whatuseek');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('322','WhoWhere Robot','whowhere');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('323','Wired Digital','wired\\-digital');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('324','w3mir','wmir');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('325','WebStolperer','wolp');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('326','The Web Wombat','wombat');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('327','The World Wide Web Worm','worm');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('328','WWWC Ver 0.2.5','wwwc');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('329','WebZinger','wz101');
INSERT INTO `%PREFIX%_stat_robot` VALUES ('330','XGET','xget');
