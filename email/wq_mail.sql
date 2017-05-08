# Host: localhost  (Version: 5.5.47)
# Date: 2017-03-02 20:16:19
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "wq_email"
#

CREATE TABLE `wq_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(60) DEFAULT NULL COMMENT '邮件地址',
  `title` char(40) DEFAULT NULL COMMENT '标题',
  `name` varchar(40) DEFAULT NULL COMMENT '发信人名字',
  `content` text COMMENT '内容',
  `error_time` tinyint(3) NOT NULL DEFAULT '0' COMMENT '发送失败次数',
  `fail_callback` varchar(40) DEFAULT NULL COMMENT '发送失败的回调函数',
  `for` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='即时邮件发送表';

#
# Structure for table "wq_email_time"
#

CREATE TABLE `wq_email_time` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(60) DEFAULT NULL COMMENT '邮箱地址',
  `title` varchar(60) DEFAULT NULL COMMENT '标题',
  `name` varchar(40) DEFAULT NULL COMMENT '姓名',
  `content` text,
  `send_time` int(11) DEFAULT NULL COMMENT '定时发送的时间戳，只看H:i:s，重复时会自动调整时间',
  `repeat` bit(1) DEFAULT NULL COMMENT '是否重复，重复则+1天之后继续发送',
  `for` varchar(40) DEFAULT NULL COMMENT '用作什么，便于查找',
  `is_function` bit(1) NOT NULL DEFAULT b'0' COMMENT '标识内容是否是function',
  `fail_callback` varchar(40) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=33 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC COMMENT='延时邮件发送表';
