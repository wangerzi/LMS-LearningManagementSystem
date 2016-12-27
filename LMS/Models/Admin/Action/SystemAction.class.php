<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/10 0010
 * Time: 下午 4:26
 */
class SystemAction extends CommonAction
{
    /**
     * 验证码设置
     */
    public function verify(){
        $this->initUniqid(GROUP_NAME.'/System/updateVerify');
        $this->display();
    }
    //更新验证码设置。
    public function updateVerify(){
        if(!is_login()||!IS_POST)
            _404('页面不存在！');
        //验证表单唯一标识码！
        if(checkFormUniqid(I('post.uniqid')))
            $this->error('唯一标识码不正确，请刷新重试！');

        $arr=include CONF_PATH.'verify.php';

        foreach($arr as $key => $val){
            if(!isset($_POST[$key]))
                $this->error('表单元素缺失！');
            $arr[$key]=I("post.{$key}",'');
        }

        //更新文件。
        if(F('verify',$arr,CONF_PATH))
            $this->success('更新成功',U(GROUP_NAME.'/System/verify'));
        else
            $this->error('更新失败，请尝试给予'.CONF_PATH.'权限');
    }
    //注册设置。
    public function register(){
        $this->initUniqid(GROUP_NAME.'/System/updateRegister');
        $this->display();
    }
    //更新注册设置。
    public function updateRegister(){
        if(!IS_POST)
            _404('页面不存在！');
        if(!checkFormUniqid(I('post.uniqid')))
            $this->error('唯一标识码不正确，请刷新重试！');

        $arr=include CONF_PATH.'register.php';

        foreach($arr as $key => $val){
            if(!isset($_POST[$key]))
                $this->error('表单元素缺失！');
            $arr[$key]=I("post.{$key}",'');
        }

        //更新文件。
        if(F('register',$arr,CONF_PATH))
            $this->success('更新成功',U(GROUP_NAME.'/System/register'));
        else
            $this->error('更新失败，请尝试给予'.CONF_PATH.'权限');
    }
    //网站设置
    public function web(){
        $this->initUniqid(GROUP_NAME.'/System/updateWeb');
        $this->display();
    }
    public function updateWeb(){
        if(!IS_POST)
            _404('页面不存在！');
        if(!checkFormUniqid(I('post.uniqid')))
            $this->error('唯一标识码不正确，请刷新重试！');

        $arr=include CONF_PATH.'web.php';
        foreach($arr as $key => $val){
            if(!isset($_POST[$key]))
                $this->error('表单元素缺失！');
            $arr[$key]=I("post.{$key}",'');
        }

        //更新文件。
        if(F('web',$arr,CONF_PATH))
            $this->success('更新成功',U(GROUP_NAME.'/System/web'));
        else
            $this->error('更新失败，请尝试给予'.CONF_PATH.'权限');
    }
}