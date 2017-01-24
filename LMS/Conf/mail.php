<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/13 0013
 * Time: 下午 4:01
 */
return array(
    'MAIL_HOST' => 'smtp.qq.com',
    'MAIL_SMTPAUTH' => true,
    'MAIL_USERNAME' => 'admin@wj2015.com.cn',
    'MAIL_PASSWORD' => '',
    'MAIL_FROM' => 'admin@wj2015.com.cn',
    'MAIL_FROM_NAME' => 'Wang',
    'MAIL_IS_HTML' => true,
    'MAIL_CHARSET' => 'utf-8',
    'ALT_BODY' => '这是来自wj2015.com.cn的邮件',
    //能打开邮件监控页面的用户ID。
    'EMAIL_UID' => 2,
    //请求IP，存在$_SERVER['REQUEST_IP']
    'MAIL_CLIENT_IP' => '::1',
    //邮件发送间隔
    'MAIL_SEND_SPACE' => '0.1',
    'MAIL_REFRESH_SPACE' => '1',
    //每次最多处理
    'EMAIL_SEND_MAX'    =>  5,
    //定时邮件最多处理个数，指筛选出来的，需要马上发送的邮件数目。
    'EMAIL_TIME_MAX'    =>  400,
    //邮件被退回的时候，通过站内信通知的发件人UID
    'EMAIL_BACK_FID'    =>  2,
);
?>