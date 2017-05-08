<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/1
 * Time: 21:19
 * Info:页面用于定时发送邮件用，为了不引起重复发邮件，端口占用等问题，本脚本仅能通过命令行执行 ;php time.php
 */
require 'MyEmail.class.php';
$email = new MyEmail();
if(!$argc)//仅能通过命令行刷邮件，不喜欢的，可改为$_SERVER['REMOTE_ADDR'] == $email->mailConf['MAIL_CLIENT_IP']，这样就能用HTTP刷定时邮件，并且仅执行IP才能执行次页面。
    die('仅主机访问');
$sleep = $email->mailConf['MAIL_DEAL_SPACE'];
while(1) {
    $arr = $email->dealEmailTimeQueue();
    //echo date('Y-m-d H:i:s') . " 成功添加{$arr['success']}条邮件至 待发送 队列,定时队列总邮件{$arr['count']}条\r\n";
	if($arr['success'] > 0)
		echo date('Y-m-d H:i:s') . " successfully added {$arr['success']} emails to EmailQueue,total:{$arr['count']}\r\n";//中文在DOS和Linux下都可能出现显示问题，所以暂用英语。

    $arr = $email->dealEmailQueue();
    //echo date('Y-m-d H:i:s') . " 成功发送{$arr['success']}条邮件,失败{$arr['error']}条,舍弃{$arr['remove']}条\r\n";
	if($arr['success'] > 0 || $arr['error'] > 0 || $arr['remove'] > 0)
		echo date('Y-m-d H:i:s') . " successfully sent  {$arr['success']} emails,failed: {$arr['error']},remove:{$arr['remove']}\r\n\r\n";
    sleep($sleep);
}