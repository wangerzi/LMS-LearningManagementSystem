<?php

/**
 * Created by PhpStorm.
 * User: wang
 * Date: 17-3-29
 * Time: 下午3:07
 */
class BaseAction extends CommonAction
{
    public function _initialize(){
        parent::_initialize();
        if(!is_admin())
            _404('页面不存在！');
        //管理员的处理方法
        $this->admin_message_num = admin_message_num();
        $this->feedbackTip = M('feedback')->field('name,time')->where('status IS NULL')->order('time DESC')->limit(4)->select();//只显示一次都没处理过的，即：status IS NULL。
    }
}