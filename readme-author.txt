注意事项：
1. 在部署的时候，管理员的邮件监控只能通过localhost进入，或者改为某IP的才能进入，修改IP在邮箱配置里边。
2. 邮箱发送需要开启extension=php_sockets.dll;extension=php_openssl.dll;然后重启否则发送失败！
3. 如果遇到图片无法打开，可以将URL模式兼容模式，在/LMS/Conf/config.php配置项里边加上 'URL_MODEL'	=>	3。