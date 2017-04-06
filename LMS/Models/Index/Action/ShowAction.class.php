<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/21 0021
 * Time: 下午 12:58
 */
/*向外展示的控制器，就如外层首页，意见反馈等*/
class ShowAction extends CommonAction
{
    function index(){
        $this->display();
    }
    /*意见反馈*/
    function feedback(){
        $this->initUniqid(GROUP_NAME.'/Show/feedbackHandle');
        $this->display();
    }
    /*意见反馈的验证码*/
    function feedbackVerify(){
        import('imageCode',APP_PATH.C('APP_GROUP_PATH').'/'.GROUP_NAME.'/Class/');
        $img = new imageCode();
        $img->create('feedback_verify',C('VERIFY_LEN'),C('VERIFY_TYPE'),100,30);
    }
    function feedbackVerifyCheck(){
        if(!IS_POST||!IS_AJAX)
            _404('页面不存在');
        $verify=I('post.verify');
        import('imageCode',APP_PATH.C('APP_GROUP_PATH').'/'.GROUP_NAME.'/Class/');
        $valid = imageCode::check('feedback_verify',$verify);
        $this->ajaxReturn(array('valid'=>$valid));
    }
    function feedbackHandle(){
        if(!IS_POST||!IS_AJAX)
            _404('页面不存在');
        if(!checkFormUniqid(I('post.uniqid')))
            $this->error('表单标识码不正确');
        import('imageCode',APP_PATH.C('APP_GROUP_PATH').'/'.GROUP_NAME.'/Class/');
        if(intval(C('FEEDBACK_VERIFY')) && !imageCode::check('feedback_verify',I('post.verify')))
            $this->error('验证码错误！');
        imageCode::remove('feedback_verify');
        load('@/check');
        $name=I('post.name');
        if(!($str=mb_check_stringLen($name,C('FDBCK_NAME_MIN_LEN'),C('FDBCK_NAME_MAX_LEN'),'名字')))
            $this->error($str);
        $content=I('post.content');
        if(!($str=mb_check_stringLen($content,C('FDBCK_CONTENT_MIN_LEN'),C('FDBCK_CONTENT_MIN_LEN'),'内容')))
            $this->error($str);
        $connect=I('post.connect',0,'intval');
        if(!preg_match('/^1[3|5|8]{1}[0-9]{9}|[0-9]{3,12}$/',$connect))
            $this->error('联系方式填写不正确');

        load('@/account');
        $path = save_user_image('temp');
        if(!$path['status']) {
            $path = null;
        }
        else
            $path = $_SERVER['HTTP_HOST'].':'.$_SERVER['SERVER_PORT'].get_thumb_file($path['path']);
        //$this->ajaxReturn(array('status'=>false,'info'=>'test','path'=>$path));

        $uid=session('uid');
        $data=array(
            'name'  =>  $name,
            'content'=> $content,
            'connect'=> $connect,
            'uid'   =>  $uid,
            'pics'  =>  $path,
            'time'  =>  time(),
            'status'=>  false,
        );
        if(!($fid=M('feedback')->add($data)))
            $this->error('加入数据库失败！');
        load('@/email');
        addEmailTimeQueue(C('WEB_EMAIL'),C('WEB_NAME'),'您有一条新的反馈','来自'.$name.'('.$connect.')在'.date('Y.m.d H:i:s',time()).'给您反馈<br/>'.$content.'<br>图片：<img src="'.$path.'"/>',0);
        clearUniqid();
        $this->success('提交成功，感谢您的反馈！');
    }
}