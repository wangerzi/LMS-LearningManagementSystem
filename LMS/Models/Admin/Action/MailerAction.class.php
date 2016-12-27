<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/13 0013
 * Time: 下午 3:44
 */
class MailerAction extends CommonAction
{
    //用系统给某个邮箱发邮件。
    function index(){
        /*初始化唯一碼*/
        $this->initUniqid(GROUP_NAME.'/Mailer/indexHandle');
        if(isset($_GET['uid'])){
            $user=M('user')->where('id=%d',I('get.uid',0,'intval'))->limit(1)->select();
            if(!user)
                $this->error('用户不存在！');
            $this->data=array(
                'email' => $user['email'],
                'username' => '尊敬的'.$user['name'],
                'title'    => '系统邮件',
                'content'  => '来自'.C('WEB_NAME').'的通知...',
                'delay'    => 0,
            );
        }
        $this->display();
    }
    function spy(){
        if(C('EMAIL_UID') != session('uid'))
            $this->error('没有权限');
        $this->display();
    }
    //发送邮件处理。
    function indexHandle(){
        if(!IS_POST)
            _404('页面不存在！');
        //加载函数库！
        load('email',APP_PATH.'Common/');

        if(!checkFormUniqid(I('post.uniqid')))
            $this->error('唯一标识码不匹配！',U(GROUP_NAME.'/Mailer/index'));
        if(addEmailTimeQueue(I('post.email'),I('post.name'),I('post.title'),I('post.content'),time()+I('post.time',0,'intval')))
            $this->success('邮件发送成功！');
        else {
            $this->error('邮件发送失败！');
        }
    }
    //监控需要马上发送的Emails的变化。
    function sendEmails(){
        if(C('EMAIL_UID') != session('uid'))
            $this->error('没有权限');
        //加载函数库！
        load('email',APP_PATH.'Common/');

        if($arr=dealEmailQueue())
            echo "成功发送{$arr['send']}条邮件，失败：{$arr['fail']}条，舍弃{$arr['del']}条！<br/>";
        else
            echo '文件被占用！';
    }
    //监控定时任务。
    function dealEmailTime(){
        if(C('EMAIL_UID') != session('uid'))
            $this->error('没有权限');
        //加载函数库！
        load('email',APP_PATH.'Common/');

        if($arr=dealEmailTimeQueue())
            echo "共有{$arr['count']}条邮件在定时队列，成功将{$arr['add']}条定时邮件加入Email队列！";
        else
            echo '文件被占用';
    }
    //编辑邮件配置。
    function email(){
        /*为即将提交的表单创建一个UNIQID作为表单验证使用!*/
        $this->initUniqid(GROUP_NAME.'/Mailer/webHandel');
        $this->data=array(
            'MAIL_HOST' => '发送的SMTP服务器',
            'MAIL_SMTPAUTH' => '是否开启SMTP验证（最好开启）',
            'MAIL_USERNAME' => '用户名',
            'MAIL_PASSWORD' => '密码',
            'MAIL_FROM' => '来自',
            'MAIL_FROM_NAME' => '发出者',
            //'MAIL_IS_HTML' => array(true,'是否是HTML'),
            'MAIL_CHARSET' => '字符集',
            'ALT_BODY' => '邮件备注摘要',
            //能打开邮件监控页面的用户ID。
            'EMAIL_UID' => '能打开邮件监控页面的用户ID',
            //请求IP，存在$_SERVER['REQUEST_IP']
            'MAIL_CLIENT_IP' => '请求IP，默认服务器本地请求',
            //邮件发送间隔
            'MAIL_SEND_SPACE' => '邮件发送间隔，不要小于0.1',
            //监控刷新间隔
            'MAIL_REFRESH_SPACE' => '监控刷新间隔',
        );
        $this->display();
    }
    function webHandle(){
        if(!IS_POST)
            _404('页面不存在！');
        if(checkFormUniqid(I('post.uniqid')))
            $this->error('表单唯一标识码不匹配！请刷新重试！');
        $data=array(
            'MAIL_HOST' => I('post.MAIL_HOST','smtp.qq.com'),
            'MAIL_SMTPAUTH' => I('post.MAIL_SMTPAUTH',true),
            'MAIL_USERNAME' => I('post.MAIL_USERNAME',''),
            'MAIL_PASSWORD' =>  I('post.MAIL_PASSWORD',''),
            'MAIL_FROM' =>  I('post.MAIL_FROM','944688482@qq.com'),
            'MAIL_FROM_NAME' =>  I('post.MAIL_FROM_NAME','Wang'),
            'MAIL_IS_HTML' =>  I('post.MAIL_IS_HTML',true),
            'MAIL_CHARSET' =>  I('post.MAIL_CHARSET','utf-8'),
            'ALT_BODY' =>  I('post.ALT_BODY','这是来自wj2015.com.cn的邮件'),
            //能打开邮件监控页面的用户ID。
            'EMAIL_UID' =>  I('post.EMAIL_UID',2),
            //请求IP，存在$_SERVER['REQUEST_IP']
            'MAIL_CLIENT_IP' =>  I('post.MAIL_CLIENT_IP','::1'),
            //邮件发送间隔
            'MAIL_SEND_SPACE' =>  I('post.MAIL_SEND_SPACE','0.1'),
            //监控刷新间隔
            'MAIL_REFRESH_SPACE' => array('1','监控刷新间隔'),
        );
        if($data['MAIL_SEND_SPACE']<0.1)
            $this->error('发送间隔不能过短');
    }
    //显示邮件发送记录
    function emailLog(){

    }
    //邮件舍弃记录
    function emailErrorLog(){

    }
}