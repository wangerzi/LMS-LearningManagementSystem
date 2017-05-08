# Multi - project timed mail delivery management

**多项目定时邮件发送管理**  
## Overview:

本系统可用于处理 多项目定时邮件 冲突问题，邮件发送使用[PHPMailer](https://github.com/PHPMailer/PHPMailer)插件。
  
系统可实现定时发送固定邮件，定时发送动态邮件（调用自定义函数返回内容），通过 表查询 管理定时队列中的邮件。

通过PDO操作MySQL数据库实现邮件定时队列和待发送队列。通过`for`字段可有效区分 项目、发送用户、发送目的，并通过MyEmail类中的delEmailTimeQueue方法进行控制。

线上案例：[学习计划管理系统](http://wj2015.com.cn)。

源码内置`demo/demo-test.php`用来熟悉使用本系统。

详见`MyEmail.class.php`,`Conf/mail.php`,`Conf/db.php`

## Structure

    目录结构
    ----------
    |---PHPMailer/-PHPMailerAutoload.php      #PHPMailer插件
    |
    |         |--db.php                       #数据库配置
    |---Conf/-|
    |         |--mail.php                     #邮件发送配置。
    |
    |--demo/--demo-test.php                   #邮件发送案例
    |
    |--MyEmail.class.php                      #核心类文件
    |
    |--time.php                               #定时邮件发送脚本
    |
    |--windows.bat                            #批处理刷脚本以达到定时发送效果
    |
    |--Linux.sh                               #直接使用Linux的shell指令
    |
    |--wq_mail.sql                            #数据库文件，内含表结构。
## Usage

**Windows:使用 windows.bat 即可**  

**Linux:使用 Linux.sh 即可**，若使用 `nohup php time.php &`可实现登出后依旧后台运行
  
    文件实质： php time.php

##### 配置（Conf/mail.php和Conf/db.php）：

1. 需要打开`php.ini`中的 `php_openssl`扩展，否则`SMTP Connect failed`
1. 创建数据库 `wq_mail`，执行`wq_mail.sql` 初始化表结构。
1. 在 `Conf/db.php` 中配置数据库连接信息。
1. 在 `Conf/mail.php`中配置&&用户名、密码、邮件引用，自动调用函数加载 等信息。

##### 调用（更多请看demo/demo-test.php）：


    <?php
        require "./MyEmail.class.php";//这里填写MyEmail类的路径。
        //首先，引入MyEmail.class.php
        require '../MyEmail.class.php';
        
        //然后，实例化对象
        $email = new MyEmail();
        
        //最后，发邮件或者管理邮件即可。
        $email->addEmailTimeQueue('admin@wj2015.com.cn','对方称呼','邮件名字','内容',time()+20);//延迟20s发送
    ?>

## Notice

1. 本系统为原生PHP开发，PHP版本最好在5.3.8以上，以减少MySQL注入等安全问题。
1. 系统发送邮件的功能由PHPMailer实现，用户可自己制定PHPMailer的路径。
1. 使用时，需要在`php.ini`中开启`php_openssl` 扩展，否则出现SMTP connect failed.错误。
1. 数据库操作使用PDO对象，所以需要在`php.ini`中开启`php_pdo`相关扩展。
1. 如果使用QQ邮箱，在配置`Conf/mail.php`中的密码时，需要用QQ邮箱独立密码。
1. 为了避免重复发送邮件 和 端口冲突等问题，time.php只能用命令行执行，无法用HTTP访问。
1. 如果您的发送内容是某函数返回值，并且该函数基于 Thinkphp等框架实现，请在`Conf/mail.php`中配置`MAIL_CON_EXTRA`中配置入口文件（`index.php`）以及函数所在路径（`Common/function.php`）。
1. 如果您的发送内容是某函数返回值，并且该函数基于 Thinkphp等框架实现，请在`Conf/mail.php`中配置`MAIL_CON_EXTRA`中配置入口文件（`index.php`）或者核心文件(`../ThinkPHP/ThinkPHP.class.php`)+函数所在路径（`Common/function.php`）。
    1. 对于使用框架的用户，个人建议引入核心文件，因为引入入口文件可能会因为静态缓存等原因导致脚本停止运行。

            配置样例('Conf/mail.php'):
            ...
            ...
            'MAIL_CON_EXTRA'    =>  '../ThinkPHP/ThinkPHP.class.php,../LMS/Common/functions.php',
            ...
            ...
    1. 注意：如果引入入口文件（`index.php`），则需要在`index.php`中加入`chdir(dirname(__FILE__))`改变include相对定位点，否则加载出错。

            配置样例('Conf/mail.php'):
            ...
            ...
            //加载框架的入口函数，记得在`index.php`中调用chdir(dirnane(__FILE__))
            'MAIL_CON_EXTRA'    =>  '../index.php,../LMS/Common/functions.php',
            ...
            ...

## Version

##### 1.0.2			2017年05月8日
1. BUG:修复普通定时邮件重复发送的BUG（需要更改wq_email_time中repeat,is_function的数据类型）。
1. OPT:针对定时邮件积累的优化，当定时过早，只会发一次邮件，而不是重复发n次。
1. OPT:由于该进程一直开启，Linux重定向输出会占用很大的空间，所以修改了`time.php`中处理逻辑，只输出有价值的信息。

##### 1.0.1			2017年03月24日

1. BUG:更改重复发送部分的逻辑错误。
1. OPT:优化include相对定位点的问题。

##### 1.0.0			2017年5月2日

第一个版本

**author:Jeffrey Wang**  - *2017年3月2日21:19:49*
