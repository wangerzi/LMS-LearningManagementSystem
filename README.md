# LMS-LearningManagementSystem
Learning Management System 学习计划管理系统
#系统介绍：
本系统是由@wangerzi 独立开发的项目，系统的核心是通过监督用户完成制定的学习计划，达到辅助学习的效果。  
线上地址：http://wj2015.com.cn  
部分素材来自网络，若侵删。  
数据库文件在/LMS/data/think_lms.sql和/LMS/data/wq_level.sql 总数据结构和等级数据（正常运行系统需要）
#注意事项：
1. 在部署的时候，管理员的邮件监控只能通过localhost进入，或者将在邮箱配置里将管理IP改为管理者的IP才能进入。  
2. 邮箱发送需要开启extension=php_sockets.dll;extension=php_openssl.dll;然后重启否则发送失败！  
3. 如果遇到图片无法打开，可以将URL模式兼容模式，在/LMS/Conf/config.php配置项里边加上 'URL_MODEL'	=>	3。  
4. 后台配置的更改采用的是重写配置文件的方法，如果出现无法修改配置的情况，请尝试给 /LMS/Config 文件夹读写权限。  
5. 由于使用了部分PHP 5.5的语法，所以，PHP的版本需要在5.5以上。  
