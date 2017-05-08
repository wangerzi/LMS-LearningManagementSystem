<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/28 0028
 * Time: 下午 7:53
 */
class FriendAction extends CommonAction
{
    public function index(){
        $this->display();
    }
    public function find(){
        $this->initUniqid(GROUP_NAME.'/Friend/findHandle');
        $this->display();
    }
    /*find的表单处理！*/
    public function findHandle(){
        //因为设置了翻页，所以。。。
        /*if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');*/
        if(!checkFormUniqid(I('post.uniqid'))){
            echo '<h1>表单标识码已过期，请点击 “找朋友” 重试！</h1>';
            return 0;
        }
        //初始化一个唯一标识码，与寻找有缘人的的唯一标识码分开，增加破解有缘人系统的难度！
        $this->initUniqid();
        import('ORG.Util.Page');
        load('@/friend');

        $content=I('post.content');
        $len=mb_strlen($content,'utf-8');
        if($len<C('FRIEND_SEARCH_MIN')||$len>C('FRIEND_SEARCH_MAX')){
            echo '<h1>搜索内容过短或过长，请点击“找朋友” 重试！</h1>';
            return 0;
        }

        $uid=session('uid');
        $db=M('user');
        //每页4个！
        $listRows=4;
        $page=new Page($db->where("username LIKE '%%%s%%' OR email LIKE '%%%s%%'",$content,$content)->count(),$listRows);
        $this->page=$page->show();


        /*$users=$db
            ->join("LEFT JOIN wq_friend fri ON (fri.fid={$uid} AND fri.rid=user.id) OR (fri.fid=user.id AND fri.rid=2) LEFT JOIN wq_friend_request fri_req ON (fri_req.fid={$uid} AND fri_req.rid=user.id) OR (fri_req.fid=user.id AND fri_req.rid={$uid})")
            ->where("user.id!={$uid} AND fri.id is null AND fri_req.id is null")
            ->select();*/
        $users=$db->where("username LIKE '%%%s%%' OR email LIKE '%%%s%%'",$content,$content)->limit($page->firstRow,$page->listRows)->select();

        $data=merge_friend_status($uid,$users);
       /* p($db->getLastSql());*/
        //p($data);
        //$users=$db->field(array('id','face','username','info','email'))->where("id!='%d' AND (username LIKE '%%%s%%' OR email LIKE '%%%s%%')",session('uid'),$content,$content)->limit($page->firstRow,$page->listRows)->select();
        //p($db->getLastSql());

        $this->type=1;//代表搜索找到的！
        $this->data=$data;
        $this->display();
    }
    /*好友列表*/
    public function friendList(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');

        $uid=session('uid');
        $count=M('friend')->where("fid='%d' OR rid='%d'",$uid,$uid)->count();
        $listRows=4;
        import('ORG.Util.Page');
        $page=new Page($count,$listRows);
        $page->setConfig('theme','%first% %upPage% %linkPage% %downPage% %end%');
        $this->page=$page->show();

        load('@.friend');
        $data=M('friend')->where("fid='%d' OR rid='%d'",$uid,$uid)->limit($page->firstRow,$listRows)->select();
        $data=merge_friend($data,$uid);
        $this->data=$data;
        $this->display();
    }
    /*好友申请列表*/
    public function friendRequest(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');

        //为dealRequest初始化验证码！
        $this->initUniqid(GROUP_NAME.'/Friend/passRequest');
        $uid=session('uid');
        $count=M('friend_request')->where("rid='%d'",$uid)->count();
        $listRows=4;
        import('ORG.Util.Page');
        $page=new Page($count,$listRows);
        $page->setConfig('theme','%first% %upPage% %linkPage% %downPage% %end%');
        $this->page=$page->show();

        load('@.friend');
        $data=M('friend_request')->where("rid='%d'",$uid)->limit($page->firstRow,$listRows)->select();
        $data=merge_friend($data,$uid);
        $this->data=$data;
        $this->display();
    }
    public function dealRequest(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');
        $data=array(
            'status' => false,
            'text'   => '',
        );
        //获取到friend_request的ID！
        $rid=I('post.rid',0,'intval');
        $uid=session('uid');
        $db=M('friend_request');
        $request=$db->find($rid);
        if(empty($request)){
            $data['text']='该申请不存在！';
            $this->ajaxReturn($data);
        }
        if($request['rid']!=$uid){
            $data['text']='无权处理该申请';
            $this->ajaxReturn($data);
        }
        $friend=$request;
        $friend['time']=time();
        if(I('get.pass')){
            if(!M('friend')->add($friend)){
                $data['text']='添加好友失败！';
                $this->ajaxReturn($data);
            }
        }else{//下边处理拒绝的！

        }
        if($db->delete($rid)){
            //清理缓存
            $data['status']=true;
            $data['text']='申请处理成功！';
        }
        else{
            $data['text']='申请处理失败！';
        }
        $this->ajaxReturn($data);
    }
    public function fate(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');

        //初始化一个唯一标识码，与查找好友的的唯一标识码分开，增加破解有缘人系统的难度！
        $this->initUniqid();
        $uid=session('uid');
        $db=M('user');
        $user=$db->field(array('id','birth','reg_time','last_time'))->find($uid);
        $birth=$user['birth'];//出生日期都是固定在某一天的，所以不必转化
        $reg_time=strtotime(date('Y-m-d',$user['reg_time']));//转换成当天00:00的时间戳！
        $last_time=strtotime(date('Y-m-d',$user['reg_time']));//转换成当天00:00的时间戳！

        $data=array();

        //首先，查看生日相同的
        $map=array(
            'user.id'    =>  array(array('neq',$uid),array('exp','IS NOT NULL')),
        );
        //$birth=$db->field(array('id','username','info','email'))->where($map)->limit(4)->select();
        /*$birth=$db->query("SELECT `id`,`username`,`face`,`info`,`email`
                          FROM `wq_user` 
                          WHERE 
                            id <> '{$uid}'
                            AND
                            FROM_UNIXTIME(birth,'%m-%d')='{$birth}' 
                          LIMIT 4;");*/

        //生日相同的，注册时间相近的，最后登录时间相近的。
        $birth=$db
            ->table(C('DB_PREFIX').'user AS user')
            //->join('LEFT JOIN '.C('DB_PREFIX')."friend AS fri ON (fri.fid={$uid} AND fri.rid=user.id) OR (fri.fid=user.id AND fri.rid={$uid})",$uid,$uid)//已经加好友的不算。
            ->where($map)
            ->where("
            user.birth='%s' 
            OR user.reg_time BETWEEN '%s' AND '%s' 
            OR user.last_time BETWEEN '%s' AND '%s'
            ",$birth,$reg_time,$reg_time+86400,$last_time,$last_time+86400)
            ->field('user.id,user.username,user.face,user.info,user.email')
            ->limit(4)
            ->select();
        //p($birth);
        //p($db->getLastSql());
        $data=array_merge($data,$birth);

        load('@/friend');
        $data=merge_friend_status($uid,$data);
        $this->data=$data;

        $this->display();
    }

    /**
     * 添加好友
     */
    public function add(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');
        $data=array(
            'status' => false,
            'text'   => '',
        );
        $uid=session('uid');

        if(I('post.fate',0,'intval')){
            $control=GROUP_NAME.'/Friend/fate';
        }else{
            $control=GROUP_NAME.'/Friend/findHandle';
        }
        $data['control']=$control;
        if(!checkUrlUniqid(I('post.uniqid'),$control)){
            $data['text']='表单标识码不匹配，为了您的安全，请刷新重试！';
            $this->ajaxReturn($data);
        }

        load('@/friend');

        $rid=I('post.uid',0,'intval');
        //进行合格性验证！
        if($rid==$uid){
            $data['text']='不能添加自己为好友';
            $this->ajaxReturn($data);
        }
        $tmp=get_friend_request($uid,$rid);
        if(!empty($tmp)){
            $data['text']='他（她）或您已经在 '.date('Y-m-d H:i:s',$tmp['time']).' 发过好友申请了！';
            $this->ajaxReturn($data);
        }
        $tmp=get_friend($uid,$rid);
        if(!empty($tmp)){
            $data['text']='您已经在 '.date('Y-m-d H:i:s',$tmp['time']).' 跟他是好友了！';
            $this->ajaxReturn($data);
        }
        //检查对方的info
        $info=get_user_info($rid);
        if($info['fri_rej_all']){
            $data['text']='对方已设置拒绝所有好友请求，您可以通过私信与他交流';
            $this->ajaxReturn($data);
        }
        /*$data['info']=$info;
        $data['sql']=M('user_config')->getLastSql();
        $this->ajaxReturn($data);*/

        $temp=array(
            'fid'   =>  $uid,
            'rid'   =>  $rid,
            'time'  =>  time(),
            'type'  =>  I('post.fate'),
        );
        if(M('friend_request')->add($temp)){
            load('@/message');
            sendMessage($uid,$rid,'好友申请','用户'.cookie('username').'向您发起'.'<a href="'.U(GROUP_NAME.'/Friend/index','',true,false,true).'">好友申请</a>');
            $data['status']=true;
            $data['text']='申请成功！';
            //清理对应用户的缓存
            clear_cache('user/'.$rid.'/Index/Friend/friendRequest/',true);
            clear_cache('user/'.$uid.'/Index/Friend/findHandle/',true);
            $this->ajaxReturn($data);
        }else{
            $data['text']='；由于未知原因，添加失败，请重试！';
            $this->ajaxReturn($data);
        }
    }
    function delete(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');
        $data=array(
            'status' => false,
            'text'   => '',
        );
        if(!checkUrlUniqid(I('post.uniqid'))){
            $data['text']='表单标识码不匹配，为了您的安全，请刷新重试！';
            $this->ajaxReturn($data);
        }
        $uid=session('uid');
        $fid=I('post.fid',0,'intval');
        $db=M('friend');
        $friend=$db->find($fid);
        if(empty($friend)){
            $data['text']='不存在的好友信息！';
            $this->ajaxReturn($data);
        }
        if($friend['fid']!=$uid&&$friend['rid']!=$uid){
            $data['text']='无权操作！';
            $this->ajaxReturn($data);
        }
        if($db->delete($fid)){
            //给被解除关系的一方发站内信。
            load('@/message');
            $rid=$uid==$friend['fid']?$friend['rid']:$friend['fid'];
            sendMessage($uid,$rid,'系统邮件',cookie('username').'在'.date('Y-m-d H:i:s',time()).'已跟你解除好友关系！',get_email($rid));

            //清理对应用户的缓存
            clear_cache('user/'.$rid.'/Index/Friend/friendList/',true);
            $data['status']=true;
            $data['text']='删除成功！';
            $this->ajaxReturn($data);
        }else{
            $data['text']='由于未知原因，删除失败，请重试！';
            $this->ajaxReturn($data);
        }
    }
}