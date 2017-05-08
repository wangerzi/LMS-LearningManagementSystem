<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/1 0001
 * Time: 下午 8:56
 */
class MessageAction extends CommonAction
{
    public function index(){
        $uid=session('uid');
        $db=M('message');

        $this->initUniqid();

        $map=array(
            'rid'=>array('eq',$uid),
        );
        //仅显示未读的。
        if(I('get.onlyRead')){
            $map=array_merge($map,array(
                'status' => array('eq',0))
            );
        }

        $count=$db->where($map)->count();
        $listRows=4;

        import('ORG.Util.Page');
        $page=new Page($count,$listRows);
        $page->setConfig('theme','%first% %upPage% %linkPage% %downPage% %end%');
        $this->page=$page->show();

        $data=$db->where($map)->order('time DESC')->limit($page->firstRow,$page->listRows)->select();

        load('@/message');
        $data=merge_message($data);
        //p($data);
        $this->data=$data;
        $this->display();
    }
    public function send(){
        $this->initUniqid(GROUP_NAME.'/Message/sendHandle');
        //p($_SESSION);
        //p(get_cache_file_name());
        //获取朋友信息
        load('@/friend');

        $rid=I('get.uid',0,'intval');
        $uid=session('uid');

        if($rid!=0&&$rid!=$uid){
            $this->ruser=M('user')->field(array('id','username'))->find($rid);
            //p($this->ruser);
        }
        //无论是否有收信人ID，都放入朋友信息
        $friend = get_friends_all($uid);
        $friend = merge_friend($friend, $uid);
        $this->friend=$friend;
        $this->display();
    }
    public function sendHandle(){
        if(!IS_POST)
            _404('页面不存在！');

        if(!checkFormUniqid(I('post.uniqid'))){
            $this->error('表单验证已失败，为了你的安全，请刷新重试！');
        }

        $rid=I('uid',0,'intval');
        $uid=session('uid');
        $title=I('post.title');
        $content=I('post.content');

        load('@/check');
        if($rid==$uid){
            $this->error('不能给自己发私信！');
        }
        if($str=mb_check_stringLen(I('post.title'),C('MESS_MIN_NAME'),C('MESS_MAX_NAME'),'私信标题')!=true){
            $this->error($str);
        }
        if($str=mb_check_stringLen(I('post.content'),C('MESS_MIN_CONTENT'),C('MESS_MAX_CONTENT'),'私信内容')!=true){
            $this->error($str);
        }
        $db=M('user');

        if(!$user=$db->find($rid)){
            $this->error('用户不存在！');
        }
        load('@/message');
        if(sendMessage($uid,$rid,$title,$content,get_email($rid))) {
            clearUniqid();
            $this->success('发送成功！');
        }
        else
            $this->error('发送失败！');
    }

    /**
     * 全部已读
     */
    public function readAll(){
        //验证验证码的控制器。
        $control=GROUP_NAME.'/Message/index';
        if(!checkUrlUniqid(I('get.uniqid'),$control)){
            $this->error('URL地址唯一标识码不匹配，为了您的安全，请刷新重试！');
        }
        $uid=session('uid');
        $db=M('message');
        $db->where("rid='%d'",$uid)->setField('status',1);

        clearUniqid($control);
        $this->redirect(GROUP_NAME.'/Message/index');
    }

    /**
     * 清空私信
     */
    public function deleteAll(){
        //验证验证码的控制器。
        $control=GROUP_NAME.'/Message/index';
        if(!checkUrlUniqid(I('get.uniqid'),$control)){
            $this->error('URL地址唯一标识码不匹配，为了您的安全，请刷新重试！');
        }
        $uid=session('uid');
        $db=M('message');
        $db->where("rid='%d'",$uid)->delete();

        clearUniqid($control);
        $this->redirect(GROUP_NAME.'/Message/index');
    }
    public function read(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');

        $data=array(
            'status'=>true,
            'text'  => '',
        );
        //验证验证码的控制器。
        $control=GROUP_NAME.'/Message/index';
        if(!checkFormUniqid(I('post.uniqid'),$control)){
            $this->error('表单验证失败，为了你的安全，请刷新重试！');
        }
        $uid=session('uid');
        $mid=I('post.id',0,'intval');
        $db=M('message');
        $message=$db->find($mid);
        if(empty($message)){
            $data['text']='不存在此邮件，可能已被删除！';
            $this->ajaxReturn($data);
        }
        if($message['rid']!=$uid){
            $data['text']='无权操作！';
            $this->ajaxReturn($data);
        }

        $arr=array(
            'id' => $mid,
            'status'=> 1,
        );
        if($db->save($arr))
            $data['status']=true;
        else
            $data['text']='操作失败，邮件可能已阅读！';
        $this->ajaxReturn($data);
    }
    public function delete(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');

        $data=array(
            'status'=>true,
            'text'  => '',
        );
        //验证验证码的控制器。
        $control=GROUP_NAME.'/Message/index';
        if(!checkFormUniqid(I('post.uniqid'),$control)){
            $this->error('表单验证失败，为了你的安全，请刷新重试！');
        }
        $uid=session('uid');
        $mid=I('post.id',0,'intval');
        $db=M('message');
        $message=$db->find($mid);
        if(empty($message)){
            $data['text']='不存在此邮件，可能已被删除！';
            $this->ajaxReturn($data);
        }
        if($message['rid']!=$uid){
            $data['text']='无权操作！';
            $this->ajaxReturn($data);
        }
        if($db->delete($mid))
            $data['status']=true;
        else
            $data['text']='由于未知原因，删除失败！';
        $this->ajaxReturn($data);
    }
}