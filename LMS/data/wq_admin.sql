# Host: localhost  (Version: 5.5.47)
# Date: 2016-12-27 11:24:35
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "wq_admin"
#

DROP TABLE IF EXISTS `wq_admin`;
CREATE TABLE `wq_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL COMMENT '管理员权限等级',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=4 DEFAULT CHARSET=utf8 COMMENT='管理员表';

#
# Data for table "wq_admin"
#

/*!40000 ALTER TABLE `wq_admin` DISABLE KEYS */;
INSERT INTO `wq_admin` VALUES (3,'2','9');
/*!40000 ALTER TABLE `wq_admin` ENABLE KEYS */;
