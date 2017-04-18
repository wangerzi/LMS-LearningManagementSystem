# Host: localhost  (Version: 5.5.47)
# Date: 2017-04-18 10:31:15
# Generator: MySQL-Front 5.3  (Build 4.234)

/*!40101 SET NAMES utf8 */;

#
# Structure for table "wq_active"
#

CREATE TABLE `wq_active` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `active` varchar(42) DEFAULT NULL COMMENT '激活码',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=42 DEFAULT CHARSET=utf8 COMMENT='用户激活表';

#
# Structure for table "wq_admin"
#

CREATE TABLE `wq_admin` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` varchar(255) DEFAULT NULL,
  `level` varchar(255) DEFAULT NULL COMMENT '管理员权限等级',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=5 DEFAULT CHARSET=utf8 COMMENT='管理员表';

#
# Data for table "wq_admin"
#

INSERT INTO `wq_admin` VALUES (3,'2','9');

#
# Structure for table "wq_checkout"
#

CREATE TABLE `wq_checkout` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL COMMENT '签到时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=100 DEFAULT CHARSET=utf8 COMMENT='签到表';

#
# Structure for table "wq_code"
#

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
# Structure for table "wq_feedback"
#

CREATE TABLE `wq_feedback` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(60) DEFAULT NULL COMMENT '名字',
  `content` text COMMENT '反馈内容',
  `uid` int(11) DEFAULT NULL COMMENT '用户ID（如果有）',
  `connect` varchar(255) DEFAULT NULL COMMENT '联系方式（选填）',
  `time` int(11) DEFAULT NULL COMMENT '时间',
  `status` tinyint(1) DEFAULT NULL COMMENT '是否阅读',
  `ip` char(15) DEFAULT NULL COMMENT '反馈者的IP',
  `image` varchar(80) DEFAULT NULL COMMENT '上传的图片路径',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=9 DEFAULT CHARSET=utf8 COMMENT='反馈表';

#
# Structure for table "wq_friend"
#

CREATE TABLE `wq_friend` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) DEFAULT NULL,
  `rid` int(11) DEFAULT NULL COMMENT '接受ID',
  `time` int(11) DEFAULT NULL COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

#
# Structure for table "wq_friend_request"
#

CREATE TABLE `wq_friend_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) DEFAULT NULL COMMENT '申请者ID',
  `rid` int(11) DEFAULT NULL COMMENT '接受者ID',
  `time` int(11) DEFAULT NULL COMMENT '申请时间',
  `type` tinyint(1) NOT NULL DEFAULT '0' COMMENT '类型，好友查找或者缘分系统',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=25 DEFAULT CHARSET=utf8;

#
# Structure for table "wq_level"
#

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

INSERT INTO `wq_level` VALUES (274,0,4,2,0),(275,1,6,4,30),(276,2,8,6,90),(277,3,10,8,180),(278,4,12,10,300),(279,5,14,12,450),(280,6,16,14,630),(281,7,18,16,840),(282,8,20,18,1080),(283,9,22,20,1350),(284,10,24,22,1650),(285,11,26,24,1980),(286,12,28,26,2340),(287,13,30,28,2730),(288,14,32,30,3150),(289,15,34,32,3600),(290,16,36,34,4080),(291,17,38,36,4590),(292,18,40,38,5130),(293,19,42,40,5700),(294,20,44,42,6300),(295,21,46,44,6930),(296,22,48,46,7590),(297,23,50,48,8280),(298,24,52,50,9000),(299,25,54,52,9750),(300,26,56,54,10530),(301,27,58,56,11340),(302,28,60,58,12180),(303,29,62,60,13050);

#
# Structure for table "wq_message"
#

CREATE TABLE `wq_message` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) DEFAULT NULL,
  `rid` int(11) DEFAULT NULL,
  `time` int(11) DEFAULT NULL COMMENT '发信时间',
  `content` varchar(300) DEFAULT NULL COMMENT '信的内容',
  `title` varchar(60) DEFAULT NULL COMMENT '标题',
  `status` tinyint(1) DEFAULT NULL COMMENT '状态，1代表已读，0代表未读',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=112 DEFAULT CHARSET=utf8 COMMENT='站内信';

#
# Structure for table "wq_mission"
#

CREATE TABLE `wq_mission` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) DEFAULT NULL COMMENT '名字',
  `info` varchar(255) DEFAULT NULL COMMENT '注释',
  `sid` int(11) DEFAULT NULL COMMENT '阶段ID',
  `sort` tinyint(3) NOT NULL DEFAULT '9' COMMENT '用于排序的字段',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=285 DEFAULT CHARSET=utf8 COMMENT='任务表';

#
# Structure for table "wq_mission_complete"
#

CREATE TABLE `wq_mission_complete` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(20) DEFAULT NULL,
  `mid` int(11) DEFAULT NULL COMMENT '任务ID',
  `pcid` int(11) DEFAULT NULL COMMENT 'plan_clone的ID，便于查找',
  `time` int(11) DEFAULT NULL COMMENT '完成时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=95 DEFAULT CHARSET=utf8 COMMENT='用户完成表';

#
# Structure for table "wq_penalize"
#

CREATE TABLE `wq_penalize` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pcid` int(11) DEFAULT NULL COMMENT 'plan_clone中的id',
  `time` int(11) DEFAULT NULL COMMENT '鞭笞时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=15 DEFAULT CHARSET=utf8 COMMENT='鞭笞专用表';

#
# Structure for table "wq_plan"
#

CREATE TABLE `wq_plan` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '建立表格用户ID',
  `open` tinyint(1) NOT NULL DEFAULT '1' COMMENT '是否公开',
  `mode` tinyint(1) NOT NULL DEFAULT '1' COMMENT '计划模式',
  `name` varchar(30) DEFAULT NULL COMMENT '计划名字',
  `create_time` int(11) DEFAULT NULL COMMENT '创建时间',
  `last_edit_time` int(11) NOT NULL DEFAULT '0' COMMENT '最后编辑时间',
  `face` char(60) DEFAULT NULL COMMENT '封面地址',
  `praised` int(11) NOT NULL DEFAULT '0' COMMENT '获赞数目',
  `saw` int(11) NOT NULL DEFAULT '0' COMMENT '被查看次数',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=52 DEFAULT CHARSET=utf8 COMMENT='计划表';

#
# Structure for table "wq_plan_clone"
#

CREATE TABLE `wq_plan_clone` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL COMMENT '计划ID',
  `uid` int(11) DEFAULT NULL COMMENT '使用用户ID',
  `svid` int(1) DEFAULT NULL COMMENT '监督者ID',
  `start` int(11) DEFAULT NULL COMMENT '开始时间',
  `complete_time` int(11) DEFAULT NULL COMMENT '完成时间，为空则未完成',
  `create_time` int(11) DEFAULT NULL COMMENT '创建此克隆计划的时间',
  `end` int(11) DEFAULT NULL COMMENT '结束时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=48 DEFAULT CHARSET=utf8 COMMENT='用户使用的计划，支持分享';

#
# Structure for table "wq_plan_comment"
#

CREATE TABLE `wq_plan_comment` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(1) DEFAULT NULL COMMENT '发布者ID',
  `pid` int(11) DEFAULT NULL COMMENT '计划对应的ID',
  `rid` int(11) DEFAULT NULL COMMENT '回复的评论ID，评论计划则为0',
  `content` varchar(255) DEFAULT NULL COMMENT '评价内容',
  `time` int(11) DEFAULT NULL COMMENT '发表时间',
  `star` tinyint(3) NOT NULL DEFAULT '0' COMMENT '评价几星，rid不为0的不能评分',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=47 DEFAULT CHARSET=utf8 COMMENT='计划评论表';

#
# Structure for table "wq_plan_praise"
#

CREATE TABLE `wq_plan_praise` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) DEFAULT NULL COMMENT '计划的ID',
  `uid` int(11) DEFAULT NULL COMMENT '点赞用户的ID',
  `time` int(11) DEFAULT NULL COMMENT '时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8;

#
# Structure for table "wq_stage"
#

CREATE TABLE `wq_stage` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pid` int(11) NOT NULL DEFAULT '0' COMMENT '计划的ID',
  `name` varchar(50) NOT NULL DEFAULT '未命名' COMMENT '阶段名称',
  `info` varchar(255) DEFAULT NULL COMMENT '阶段注释',
  `sort` tinyint(3) NOT NULL DEFAULT '9' COMMENT '用于排序的',
  `power` int(4) NOT NULL DEFAULT '10' COMMENT '权值，用来分配时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=117 DEFAULT CHARSET=utf8 COMMENT='阶段表';

#
# Structure for table "wq_supervision_log"
#

CREATE TABLE `wq_supervision_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `pcid` int(11) DEFAULT NULL COMMENT 'plan_clone的ID，便于监督者的查找',
  `mcid` int(1) DEFAULT NULL COMMENT '任务完成ID，用于完成任务的查找',
  `info` varchar(255) DEFAULT NULL COMMENT '计划执行者报告信息',
  `status` tinyint(1) DEFAULT NULL COMMENT '监督者检阅状态',
  `reply` varchar(255) DEFAULT NULL COMMENT '监督者回复',
  `star` tinyint(3) DEFAULT NULL COMMENT '检阅的星级',
  `title` varchar(50) DEFAULT NULL COMMENT '报告标题',
  `reply_time` int(11) DEFAULT NULL COMMENT '回复时间',
  `complete_time` int(11) DEFAULT NULL COMMENT '任务完成时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=28 DEFAULT CHARSET=utf8 COMMENT='监督记录';

#
# Structure for table "wq_supervision_request"
#

CREATE TABLE `wq_supervision_request` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fid` int(11) DEFAULT NULL COMMENT '请求者UID',
  `rid` int(11) DEFAULT NULL COMMENT '被请求者UID',
  `pcid` int(11) DEFAULT NULL COMMENT '请求的计划PCID',
  `time` int(11) DEFAULT NULL COMMENT '请求时间',
  `status` tinyint(1) DEFAULT NULL COMMENT '同意状态，不同意好像就直接删了。。。未处理的status=0',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=27 DEFAULT CHARSET=utf8 COMMENT='监督';

#
# Structure for table "wq_user"
#

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
  `sex` tinyint(1) NOT NULL DEFAULT '1' COMMENT '默认男性',
  `lock` tinyint(1) NOT NULL DEFAULT '0' COMMENT '是否锁定',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=46 DEFAULT CHARSET=utf8 ROW_FORMAT=DYNAMIC;

#
# Data for table "wq_user"
#

INSERT INTO `wq_user` VALUES (2,'admin','50cb91dc439a601134e69f310b91e27d43c18e04','944688482@qq.com',1492480204,'0.0.0.0',865526400,1480245629,'/ThinkPHP./LMS/data/user/2/images/m_1480991076.jpg',9,2616,'业精于勤荒于嬉，行成于思毁于随。',1,0);

#
# Structure for table "wq_user_config"
#

CREATE TABLE `wq_user_config` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `uid` int(11) DEFAULT NULL COMMENT '用户ID',
  `fri_rej_all` tinyint(1) NOT NULL DEFAULT '0' COMMENT '拒绝所有朋友',
  `rem_message` tinyint(1) NOT NULL DEFAULT '1' COMMENT '消息提示开关',
  `rem_evd` tinyint(1) NOT NULL DEFAULT '0' COMMENT '每日提醒开关',
  `rem_warn` tinyint(1) NOT NULL DEFAULT '1' COMMENT '每日警告开关',
  `rem_evd_time` int(11) DEFAULT NULL COMMENT '每日提醒的时间，存储的是修改当天的提醒时间戳',
  `rem_warn_time` int(11) DEFAULT NULL COMMENT '警告时间',
  PRIMARY KEY (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=11 DEFAULT CHARSET=utf8 COMMENT='用户的个性化配置';


#
# Data for table "wq_user_config"
#

INSERT INTO `wq_user_config` VALUES (1,2,0,0,1,1,1492219500,1492174800);
