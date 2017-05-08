<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/2
 * Time: 18:47
 */
/**
 * 发送失败的演示函数，可扩展为向用户发私信等功能。
 * @param $arr
 */
function send_fail($arr){
    echo "一封归属为{$arr['for']}的邮件在 ".date('Y-m-d H:i:s',time())." 发送失败";
}