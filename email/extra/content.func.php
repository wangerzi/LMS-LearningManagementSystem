<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/2
 * Time: 18:38
 */
/**
 * 发送内容的函数样例，可扩展为定时提醒功能，每次发邮件都从数据库获取动态内容，如果没有内容，返回空即可，邮件会自动取消。
 * @param $arr
 * @return string
 */
function content_test($arr){
    return "这封邮件的归属是：{$arr['for']}，发送时间是服务器时间".date('Y-m-d H:i:s',$arr['send_time'])."接收方名字为：{$arr['name']}";
}