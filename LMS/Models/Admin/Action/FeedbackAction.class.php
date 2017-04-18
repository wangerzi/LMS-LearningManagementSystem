<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/1 0001
 * Time: 下午 8:56
 */
class FeedbackAction extends CommonAction
{
    public function index(){
        $db=M('feedback');

        $this->initUniqid();

        $count=$db->count();
        $listRows=6;

        import('ORG.Util.Page');
        $page=new Page($count,$listRows);
        $page->setConfig('theme','%first% %upPage% %linkPage% %downPage% %end%');
        $this->page=$page->show();

        $data=$db->order('status ASC,time DESC')->limit($page->firstRow,$page->listRows)->select();

        //p($data);
        $this->data=$data;
        $this->display();
    }
    public function statusHandle(){
        $id = I('get.id',0,'intval');
        $status = I('get.status',0,'intval');
        $status = $status%2;

        $control=GROUP_NAME.'/Feedback/index';
        $this->checkUrlUniqid(I('get.uniqid'),$control);
        clearUniqid($control);

        $db=M('feedback');
        $db->where('id=%d',$id)->setField('status',$status);
        $this->redirect('index');
    }

    /**
     * 全部已读
     */
    public function readAll(){
        //验证验证码的控制器。
        $control=GROUP_NAME.'/Feedback/index';
        $this->checkUrlUniqid(I('get.uniqid'),$control);

        $db=M('feedback');
        $db->where('1')->setField('status',1);

        clearUniqid($control);
        $this->redirect('index');
    }
}