<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/2
 * Time: 13:00
 */
//邮件配置。
return array(
    'MAIL_HOST' => 'smtp.qq.com',           //SMTP服务器。
    'MAIL_SMTPAUTH' => true,                //SMTP验证。
    'MAIL_USERNAME' => '944688482@qq.com',  //用户名
    'MAIL_PASSWORD' => 'kwgdfsbctngdbfcj',  //密码 -- QQ邮箱使用独立密码。
    'MAIL_FROM' => 'admin@wj2015.com.cn',   //邮箱来自 ~~~
    'MAIL_FROM_NAME' => 'Wang',             //来自的名字
    'MAIL_IS_HTML' => true,                 //是否是HTML
    'MAIL_CHARSET' => 'utf-8',              //字符集
    'ALT_BODY' => '这是来自wj2015.com.cn的邮件',//简介

    //请求IP，对比$_SERVER['REQUEST_IP']，仅有此IP访问服务器才能刷邮件。
    'MAIL_CLIENT_IP' => '127.0.0.1',

    //处理邮件队列时，邮件发送间隔(s)。
    'MAIL_SEND_SPACE' => '0.1',

    //处理队列的时间间隔(s)
    'MAIL_DEAL_SPACE' => '1',

    //每次处理邮件队列最多发送邮件数，可适当设置小一点避免响应时间过长。
    'EMAIL_SEND_MAX'    =>  5,

    //定时邮件最多处理个数，最多多少条邮件添加到待发送队列中。
    'EMAIL_TIME_MAX'    =>  400,

    //执行错误处理函数的时候，加载的额外文件，多个文件中间用逗号隔开，注意先后顺序。
    'MAIL_ERR_EXTRA'    =>  'extra/error.func.php',

    //邮件内容是函数的时候，加载的额外文件，多个文件中间用逗号隔开。
    'MAIL_CON_EXTRA'    =>  '../ThinkPHP/ThinkPHP.class.php',

    //phpMailer自动加载路径。
    'MAIL_PHPMailer'    =>  './PHPMailer/PHPMailerAutoload.php',
);