<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/7 0007
 * Time: 下午 9:06
 */
class UserAction extends CommonAction{
    public function index(){
        $db=M('user');
        $pageSize=20;

        import('ORG.Util.Page');
        $page=new Page($db->count(),$pageSize);
        $this->data=$db->order('reg_time DESC')->limit($page->firstRow,$page->listRows)->select();

        $this->page=$page->show();

        $this->display();
    }
}