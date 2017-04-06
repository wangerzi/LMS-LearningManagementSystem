# Host: localhost  (Version: 5.5.47)
# Date: 2017-01-24 14:28:58
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "wq_active"
#

DROP TABLE IF EXISTS `wq_active`;
CREATE TABLE `wq_active` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `active` varchar(42) DEFAULT NULL COMMENT '激活码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=35 DEFAULT CHARSET=utf8 COMMENT='用户激活表';

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
# Structure for table "wq_checkout"
#

DROP TABLE IF EXISTS `wq_checkout`;
CREATE TABLE `wq_checkout` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL COMMENT '签到时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=73 DEFAULT CHARSET=utf8 COMMENT='签到表';

#
# Structure for table "wq_code"
#

DROP TABLE IF EXISTS `wq_code`;
CREATE TABLE `wq_code` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '使用者UID',
  `code` varchar(40) DEFAULT NULL,
  `time` int(11) DEFAULT NULL COMMENT '生成时间',
  `continue` int(11) DEFAULT NULL COMMENT '持续有效时间',
  `for` varchar(40) DEFAULT NULL COMMENT '用途',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=31 DEFAULT CHARSET=utf8 COMMENT='存储生成的码';

#
# Structure for table "wq_email"
#

DROP TABLE IF EXISTS `wq_email`;
CREATE TABLE `wq_email` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(60) DEFAULT NULL COMMENT '邮件地址',
  `title` char(40) DEFAULT NULL COMMENT '标题',
  `name` varchar(40) DEFAULT NULL COMMENT '发信人名字',
  `content` text COMMENT '内容',
  `error_time` tinyint(3) NOT NULL DEFAULT '0' COMMENT '发送失败次数',
  `error_uid` int(11) DEFAULT NULL COMMENT '发送失败，发给uid一个消息',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=145 DEFAULT CHARSET=utf8 COMMENT='即时邮件发送表';

#
# Structure for table "wq_email_time"
#

DROP TABLE IF EXISTS `wq_email_time`;
CREATE TABLE `wq_email_time` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) NOT NULL DEFAULT '0' COMMENT '创建者的UID',
  `email` varchar(60) DEFAULT NULL COMMENT '邮箱地址',
  `title` varchar(60) DEFAULT NULL COMMENT '标题',
  `name` varchar(40) DEFAULT NULL COMMENT '姓名',
  `content` text,
  `send_time` int(11) DEFAULT NULL COMMENT '定时发送的时间戳，只看H:i:s，重复时会自动调整时间',
  `repeat` bit(1) DEFAULT NULL COMMENT '是否重复，重复则+1天之后继续发送',
  `for` varchar(40) DEFAULT NULL COMMENT '用作什么，便于查找',
  `is_function` bit(1) NOT NULL DEFAULT b'0' COMMENT '标识内容是否是function',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8 COMMENT='延时邮件发送表';

#
# Structure for table "wq_friend"
#

DROP TABLE IF EXISTS `wq_friend`;
CREATE TABLE `wq_friend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) DEFAULT NULL,
  `rid` int(11) DEFAULT NULL COMMENT '接受ID',
  `time` int(11) DEFAULT NULL COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8;

#
# Structure for table "wq_friend_request"
#

DROP TABLE IF EXISTS `wq_friend_request`;
CREATE TABLE `wq_friend_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) DEFAULT NULL COMMENT '申请者ID',
  `rid` int(11) DEFAULT NULL COMMENT '接受者ID',
  `time` int(11) DEFAULT NULL COMMENT '申请时间',
  `type` bit(1) NOT NULL DEFAULT b'0' COMMENT '类型，好友查找或者缘分系统',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=22 DEFAULT CHARSET=utf8;

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
# Structure for table "wq_lock_user"
#

DROP TABLE IF EXISTS `wq_lock_user`;
CREATE TABLE `wq_lock_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

#
# Structure for table "wq_message"
#

DROP TABLE IF EXISTS `wq_message`;
CREATE TABLE `wq_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) DEFAULT NULL,
  `rid` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL COMMENT '发信时间',
  `content` varchar(300) DEFAULT NULL COMMENT '信的内容',
  `title` varchar(60) DEFAULT NULL COMMENT '标题',
  `status` bit(1) DEFAULT NULL COMMENT '状态，1代表已读，0代表未读',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=77 DEFAULT CHARSET=utf8 COMMENT='站内信';

#
# Structure for table "wq_mission"
#

DROP TABLE IF EXISTS `wq_mission`;
CREATE TABLE `wq_mission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '名字',
  `info` varchar(255) DEFAULT NULL COMMENT '注释',
  `time` int(11) NOT NULL DEFAULT '6' COMMENT '花费时间(小时)',
  `sid` int(11) DEFAULT NULL COMMENT '阶段ID',
  `sort` tinyint(3) NOT NULL DEFAULT '9' COMMENT '用于排序的字段',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=273 DEFAULT CHARSET=utf8 COMMENT='任务表';

#
# Structure for table "wq_mission_complete"
#

DROP TABLE IF EXISTS `wq_mission_complete`;
CREATE TABLE `wq_mission_complete` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(20) DEFAULT NULL,
  `mid` int(11) DEFAULT NULL COMMENT '任务ID',
  `pcid` int(11) DEFAULT NULL COMMENT 'plan_clone的ID，便于查找',
  `time` int(11) DEFAULT NULL COMMENT '完成时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=83 DEFAULT CHARSET=utf8 COMMENT='用户完成表';

#
# Structure for table "wq_penalize"
#

DROP TABLE IF EXISTS `wq_penalize`;
CREATE TABLE `wq_penalize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pcid` int(11) DEFAULT NULL COMMENT 'plan_clone中的id',
  `time` int(11) DEFAULT NULL COMMENT '鞭笞时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=13 DEFAULT CHARSET=utf8 COMMENT='鞭笞专用表';

#
# Structure for table "wq_plan"
#

DROP TABLE IF EXISTS `wq_plan`;
CREATE TABLE `wq_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '建立表格用户ID',
  `open` bit(1) NOT NULL DEFAULT b'1' COMMENT '是否公开',
  `mode` bit(1) NOT NULL DEFAULT b'1' COMMENT '计划模式',
  `name` varchar(30) DEFAULT NULL COMMENT '计划名字',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `last_edit_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `total` int(11) DEFAULT NULL COMMENT '花费的总时间(h)',
  `face` char(60) DEFAULT NULL COMMENT '封面地址',
  `praised` int(11) NOT NULL DEFAULT '0' COMMENT '获赞数目',
  `saw` int(11) NOT NULL DEFAULT '0' COMMENT '被查看次数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=50 DEFAULT CHARSET=utf8 COMMENT='计划表';

#
# Structure for table "wq_plan_clone"
#

DROP TABLE IF EXISTS `wq_plan_clone`;
CREATE TABLE `wq_plan_clone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL COMMENT '计划ID',
  `uid` int(11) DEFAULT NULL COMMENT '使用用户ID',
  `svid` int(1) DEFAULT NULL COMMENT '监督者ID',
  `start_time` int(11) DEFAULT NULL COMMENT '开始时间',
  `complete_time` int(11) DEFAULT NULL COMMENT '完成时间，为空则未完成',
  `create_time` int(11) DEFAULT NULL COMMENT '创建此克隆计划的时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=43 DEFAULT CHARSET=utf8 COMMENT='用户使用的计划，支持分享';

#
# Structure for table "wq_plan_comment"
#

DROP TABLE IF EXISTS `wq_plan_comment`;
CREATE TABLE `wq_plan_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(1) DEFAULT NULL COMMENT '发布者ID',
  `pid` int(11) DEFAULT NULL COMMENT '计划对应的ID',
  `rid` int(11) DEFAULT NULL COMMENT '回复的评论ID，评论计划则为0',
  `content` varchar(255) DEFAULT NULL COMMENT '评价内容',
  `time` int(11) DEFAULT NULL COMMENT '发表时间',
  `star` tinyint(3) NOT NULL DEFAULT '0' COMMENT '评价几星，rid不为0的不能评分',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COMMENT='计划评论表';

#
# Structure for table "wq_plan_praise"
#

DROP TABLE IF EXISTS `wq_plan_praise`;
CREATE TABLE `wq_plan_praise` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL COMMENT '计划的ID',
  `uid` int(11) DEFAULT NULL COMMENT '点赞用户的ID',
  `time` int(11) DEFAULT NULL COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=24 DEFAULT CHARSET=utf8;

#
# Structure for table "wq_stage"
#

DROP TABLE IF EXISTS `wq_stage`;
CREATE TABLE `wq_stage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL COMMENT '计划的ID',
  `name` varchar(50) DEFAULT NULL COMMENT '阶段名称',
  `info` varchar(255) DEFAULT NULL COMMENT '阶段注释',
  `sort` tinyint(3) NOT NULL DEFAULT '9' COMMENT '用于排序的',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=110 DEFAULT CHARSET=utf8 COMMENT='阶段表';

#
# Structure for table "wq_supervision_log"
#

DROP TABLE IF EXISTS `wq_supervision_log`;
CREATE TABLE `wq_supervision_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pcid` int(11) DEFAULT NULL COMMENT 'plan_clone的ID，便于监督者的查找',
  `mcid` int(1) DEFAULT NULL COMMENT '任务完成ID，用于完成任务的查找',
  `info` varchar(255) DEFAULT NULL COMMENT '计划执行者报告信息',
  `status` bit(1) DEFAULT NULL COMMENT '监督者检阅状态',
  `reply` varchar(255) DEFAULT NULL COMMENT '监督者回复',
  `star` tinyint(3) DEFAULT NULL COMMENT '检阅的星级',
  `title` varchar(50) DEFAULT NULL COMMENT '报告标题',
  `reply_time` int(11) DEFAULT NULL COMMENT '回复时间',
  `complete_time` int(11) DEFAULT NULL COMMENT '任务完成时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=16 DEFAULT CHARSET=utf8 COMMENT='监督记录';

#
# Structure for table "wq_supervision_request"
#

DROP TABLE IF EXISTS `wq_supervision_request`;
CREATE TABLE `wq_supervision_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) DEFAULT NULL COMMENT '请求者UID',
  `rid` int(11) DEFAULT NULL COMMENT '被请求者UID',
  `pcid` int(11) DEFAULT NULL COMMENT '请求的计划PCID',
  `time` int(11) DEFAULT NULL COMMENT '请求时间',
  `status` bit(1) DEFAULT NULL COMMENT '同意状态，不同意好像就直接删了。。。未处理的status=0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='监督';

#
# Structure for table "wq_user"
#

DROP TABLE IF EXISTS `wq_user`;
CREATE TABLE `wq_user` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `password` varchar(60) DEFAULT NULL,
  `email` char(30) DEFAULT NULL,
  `last_time` int(11) DEFAULT '0' COMMENT '最后登录时间',
  `login_ip` char(20) DEFAULT NULL,
  `birth` int(11) DEFAULT NULL COMMENT '生日',
  `reg_time` int(11) DEFAULT NULL COMMENT '注册时间',
  `face` varchar(80) NOT NULL DEFAULT './ThinkPHP/LMS/Models/Index/Public/images/faces/default.png' COMMENT '用户头像地址',
  `checkout` smallint(6) NOT NULL DEFAULT '0' COMMENT '连续签到天数',
  `exp` int(11) NOT NULL DEFAULT '0' COMMENT '已获得经验',
  `info` varchar(255) NOT NULL DEFAULT '他（她）没有留下自我介绍哦！' COMMENT '自我介绍',
  `sex` bit(1) NOT NULL DEFAULT b'1' COMMENT '默认男性',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=40 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Structure for table "wq_user_config"
#

DROP TABLE IF EXISTS `wq_user_config`;
CREATE TABLE `wq_user_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `fri_rej_all` bit(1) NOT NULL DEFAULT b'0' COMMENT '拒绝所有朋友',
  `stu_time` tinyint(3) DEFAULT '12' COMMENT '每日学习时间',
  `rem_message` bit(1) NOT NULL DEFAULT b'1' COMMENT '消息提示开关',
  `rem_evd` bit(1) NOT NULL DEFAULT b'0' COMMENT '每日提醒开关',
  `rem_warn` bit(1) NOT NULL DEFAULT b'1' COMMENT '每日警告开关',
  `rem_evd_time` int(11) DEFAULT NULL COMMENT '每日提醒的时间，存储的是修改当天的提醒时间戳',
  `rem_warn_time` int(11) DEFAULT NULL COMMENT '警告时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=8 DEFAULT CHARSET=utf8 COMMENT='用户的个性化配置';
