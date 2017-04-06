# Host: localhost  (Version: 5.5.47)
# Date: 2016-12-27 11:24:25
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "wq_level"
#

DROP TABLE IF EXISTS `wq_level`;
CREATE TABLE `wq_level` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `level` tinyint(3) NOT NULL DEFAULT '1' COMMENT '等级',
  `plan_num` tinyint(3) DEFAULT NULL COMMENT '拥有计划数',
  `complete_exp` smallint(6) DEFAULT NULL COMMENT '完成任务获得的经验',
  `need` smallint(6) NOT NULL DEFAULT '0' COMMENT '需要的经验',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=304 DEFAULT CHARSET=utf8 COMMENT='等级对应表';

#
# Data for table "wq_level"
#

/*!40000 ALTER TABLE `wq_level` DISABLE KEYS */;
INSERT INTO `wq_level` VALUES (274,0,4,2,0),(275,1,6,4,30),(276,2,8,6,90),(277,3,10,8,180),(278,4,12,10,300),(279,5,14,12,450),(280,6,16,14,630),(281,7,18,16,840),(282,8,20,18,1080),(283,9,22,20,1350),(284,10,24,22,1650),(285,11,26,24,1980),(286,12,28,26,2340),(287,13,30,28,2730),(288,14,32,30,3150),(289,15,34,32,3600),(290,16,36,34,4080),(291,17,38,36,4590),(292,18,40,38,5130),(293,19,42,40,5700),(294,20,44,42,6300),(295,21,46,44,6930),(296,22,48,46,7590),(297,23,50,48,8280),(298,24,52,50,9000),(299,25,54,52,9750),(300,26,56,54,10530),(301,27,58,56,11340),(302,28,60,58,12180),(303,29,62,60,13050);
/*!40000 ALTER TABLE `wq_level` ENABLE KEYS */;
