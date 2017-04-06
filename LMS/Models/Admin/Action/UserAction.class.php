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
        $pageSize=9;
        $this->initUniqid();

        import('ORG.Util.Page');
        $page=new Page($db->count(),$pageSize);
        $data = $db
            ->table(C('DB_PREFIX').'user as u')
            ->join(C('DB_PREFIX').'active as a ON a.user_id = u.id')
            ->field('u.id,u.username,u.lock,u.email,u.birth,u.reg_time,u.last_time,u.login_ip,u.face,u.exp,u.sex,u.info,a.active')
            ->order('u.reg_time DESC')
            ->limit($page->firstRow,$page->listRows)
            ->select();
        $this->data=$data;

        $page->setConfig('theme','%first% %upPage% %linkPage% %downPage% %end%');
        $this->page=$page->show();

        $this->display();
    }
    /**
     * 锁定一个权限比自己小的用户，扩展：做一个锁定时间
     */
    public function lock(){
        $this->checkUrlUniqid(I('get.uniqid'),GROUP_NAME.'/User/index');
        $uid = I('get.uid','intval');
        $muid = session('uid');
        if($uid == $muid)
            $this->error('不能锁定自己');

        $db = M('user');
        $db_ad = M('admin');
        $user = $db->find($uid);
        $map = array(
            'uid'   =>  array('eq',$uid),
        );
        //获取操作对象的admin信息。
        $admin = $db_ad->where($map)->find();
        //获取自己的admin信息。
        $map['uid'] = $muid;
        $mine = $db_ad->where($map)->find();

        if(empty($user))
            $this->error('用户不存在');
        if(empty($mine) || intval($admin['level']) >= $mine['level'])
            $this->error('权限不足');

        $map = array(
            'id'   =>  $uid,
            'lock' =>  I('get.lock','intval')%2,
        );
        clearUniqid();
        if($db->save($map))
            $this->success('操作成功');
        else
            $this->error('数据库操作失败');
    }
    public function mail(){
        $mail = I('get.mail');
        $this->mail = $mail;
        $this->initUniqid(GROUP_NAME.'/User/mailHandle');
        $this->display();
    }
    public function mailHandle(){
        if(!IS_POST)
            _404('页面不存在！');
        $this->checkFormUniqid(I('post.uniqid'));
        load('@/email');
        load('@/check');
        $uid = session('uid');

        $email = I('post.to');
        $name = cookie('username');
        $title = I('post.subject');
        $content = I('post.content');
        $time = I('post.time');
        if(!checkTime($time))
            $this->error('时间不符合规则');
        $time = strtotime($time);

        if(!checkEmail($email))
            $this->error('邮箱不符合规则');
        $str = mb_check_stringLen($title,1,20,'主题');
        if($str != true)
            $this->error($str);
        $str = mb_check_stringLen($content,1,300,'内容');
        if($str != true)
            $this->error($str);

        if(addEmailTimeQueue($email,$name,$title,$content,$time,'lms_admin_mail_'.$uid))
            $this->success('发送成功');
        else
            $this->error('发送失败');
    }

    /**
     * 删除一个用户，默认封印了这个功能，需要启用需删掉。
     * @return mixed
     */
    public function delete(){
        $this->checkUrlUniqid(I('get.uniqid'),GROUP_NAME.'/User/index');
        $uid = I('get.uid','intval');
        $muid = session('uid');
        if($uid == $muid)
            $this->error('不能删除自己');
        //数据库操作。
        $db = M('user');
        $db_ad = M('admin');
        $user = $db->find($uid);
        $map = array(
            'uid'   =>  array('eq',$uid),
        );
        //获取操作对象的admin信息。
        $admin = $db_ad->where($map)->find();
        //获取自己的admin信息。
        $map['uid'] = $muid;
        $mine = $db_ad->where($map)->find();

        if(empty($user))
            $this->error('用户不存在');
        if(empty($mine) || intval($admin['level']) >= $mine['level'])
            $this->error('权限不足');
        return error('此操作过于危险，如需使用请在源码中删除此句。');
        //删除用户。
        $db->delete($uid);
        //删除配置。
        M('user_conf')->where('uid=%d',$uid)->delete();
        //删除计划。
        M('plan_clone')->where('uid=%d',$uid)->delete();
        M('plan_clone')->where('pid=%d',$uid)->save(array('pid'=>0));
        //删除评论，回复等信息。
        M('commit')->where('uid=%d',$uid)->delete();
        //删除信息
        M('message')->where('fid=%d or uid=%d',$uid,$uid)->delete();
        M('mission_complete')->where('uid=%d',$uid)->delete();
        //等其他的。
        $this->success('删除成功');
    }
}