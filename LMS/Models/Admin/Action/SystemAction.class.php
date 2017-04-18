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
        if(!IS_POST)
            _404('页面不存在！');
        //验证表单唯一标识码！
        $this->checkFormUniqid(I('post.uniqid'));

        $res = updateConf(CONF_PATH.'verify.php',$_POST);
        if($res['status'])
            $this->success('修改成功');
        else
            $this->error($res['info']);
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
        $this->checkFormUniqid(I('post.uniqid'));

        $res = updateConf(CONF_PATH.'register.php',$_POST);
        if($res['status'])
            $this->success('修改成功');
        else
            $this->error($res['info']);
    }
    //网站设置
    public function web(){
        $this->initUniqid(GROUP_NAME.'/System/updateWeb');
        $this->display();
    }
    public function updateWeb(){
        if(!IS_POST)
            _404('页面不存在！');
        $this->checkFormUniqid(I('post.uniqid'));

        $res = updateConf(CONF_PATH.'web.php',$_POST);
        if($res['status'])
            $this->success('修改成功');
        else
            $this->error($res['info']);
    }
    public function plan(){
        $this->initUniqid(GROUP_NAME.'/System/updatePlan');
        $this->display();
    }
    public function updatePlan(){
        if(!IS_POST)
            _404('页面不存在！');
        $this->checkFormUniqid(I('post.uniqid'));

        $res = updateConf(CONF_PATH.'plan.php',$_POST);
        if($res['status'])
            $this->success('修改成功');
        else
            $this->error($res['info']);
    }
}