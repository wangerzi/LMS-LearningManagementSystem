<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/20 0020
 * Time: 下午 12:06
 */
class SupervisionAction extends CommonAction {
    public function index(){
        import('ORG.Util.Page');
        //为自己初始化标识码，验证时，需传入此控制器的信息！
        $this->initUniqid(GROUP_NAME.'/Supervision/penalize');

        $uid=session('uid');
        $db=M('plan_clone');
        $type=I('get.type');

        //气泡提醒
        load('@/supervision');
        $page_num=count_supervision($uid);
        $this->page_num=$page_num;

        //加载函数库！
        load('@/plan');

        $count=$page_num['all'];

        $listRows=6;
        $page=new Page($count,$listRows);
        $page->setConfig('theme','%first% %upPage% %linkPage% %downPage% %end%');
        $this->page=$page->show();

        //分页
        $arr = merge_plan($db->where("svid='%d'",$uid)->order('start_time DESC')->limit($page->firstRow,$page->listRows)->select(),'s_',null,true);
        $arr = merge_plan_mission($arr,null,true);//这里之所以merge_plan_mission，是因为需要用到里边的今日是否完成

        //p($arr);
        unset($arr['config']);//继承下来的用户配置。
        $this->data=$arr;
        $this->display();
    }
    //边吃的意思
    public function penalize(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');
        $uid=session('uid');
        $pcid=I('post.pcid',0,'intval');
        $db=M('penalize');
        $data=array(
            'status'    =>  false,
            'text'      =>  ''
        );
        if(!checkUrlUniqid(I('post.uniqid'))){
            $data['text']='表单验证码不匹配，请刷新重试！';
            $this->ajaxReturn($data);
        }
        $plan_clone=M('plan_clone')->field('svid,pid')->find($pcid);
        if($plan_clone['svid']!=$uid){
            $data['text']='无权操作';
            $this->ajaxReturn($data);
        }
        if($plan_clone['complete_time']){
            $data['text']='不满足鞭笞条件';
            $this->ajaxReturn($data);
        }
		$map=array(
			"pcid"	=>	array("eq",$pcid),
			"time"	=>	array("between",get_time(0).','.time()),
		);
        if(!empty(M('mission_complete')->where($map)->find())){
            $data['text']='好友今天完成过任务，就不要再鞭笞TA了！';
            $this->ajaxReturn($data);
        }
        $map=array(
            'pcid'  =>  array('eq',$pcid),
            'time'  =>  array('gt',get_time(0)),
        );
        $penalize=$db->where($map)->find();
        if($penalize){
            $data['text']='每天只能同一任务鞭笞一次';
            $this->ajaxReturn($data);
        }
        $user=M('user')->field('email')->find($plan_clone['uid']);
        $plan=M('plan')->field('name')->find($plan_clone['pid']);
        load('@/email');
        $content=$this->user['username'].'在北京时间'.date('Y年m月d日 H:i:s',time()).'对您使用了鞭笞，提醒您应该执行您的学习计划《'.$plan['name'].'》了！';
        $arr=array(
            'pcid'  =>  $pcid,
            'time'  =>  time(),
        );
        if(M('penalize')->add($arr)&&addEmailTimeQueue($user['email'],C('WEB_NAME'),'学习计划鞭笞邮件',$content,0)){
            $data['status']=true;
        }else{
            $data['text']='由于未知原因，操作失败！';
        }
        $this->ajaxReturn($data);
    }

    /**
     * 申请列表
     */
    public function request(){
        //初始化验证码
        $this->initUniqid();

        $uid=session('uid');
        $db=M('supervision_request');
        $count=$db->where("rid='%d'",$uid)->count();
        //气泡提醒
        load('@/supervision');
        $page_num=count_supervision($uid);
        $this->page_num=$page_num;


        //分页
        import('ORG.Util.Page');
        $listRows=5;
        $page=new Page($count,$listRows);
        $page->setConfig('theme','%first% %upPage% %linkPage% %downPage% %end%');
        $this->page=$page->show();

        //获取数据，未处理的排在前边
        $data=$db->where("rid='%d'",$uid)->limit($page->firstRow,$page->listRows)->order('status ASC,time DESC')->select();
        $data=merge_sv_request_plan($data);
        $this->data=$data;
        //p($this->data);

        $this->display();
    }
    public function waiting(){
        //初始化验证码
        $this->initUniqid(GROUP_NAME.'/Supervision/checkSubmit');

        $uid=session('uid');
        $db=M('supervision_log');
        $count=$db->table(C('DB_PREFIX').'supervision_log AS sv')->join(C('DB_PREFIX').'plan_clone AS pc ON sv.pcid=pc.id')->where("pc.svid='%d'",$uid)->count();
        //气泡提醒
        load('@/supervision');
        $page_num=count_supervision($uid);
        $this->page_num=$page_num;
        //p($page_num);


        //分页
        import('ORG.Util.Page');
        $listRows=5;
        $page=new Page($count,$listRows);
        $page->setConfig('theme','%first% %upPage% %linkPage% %downPage% %end%');
        $this->page=$page->show();

        //获取数据，sv.id 倒序相当于时间倒序
        $data=$db
            ->table(C('DB_PREFIX').'supervision_log AS sv')
            ->join(C('DB_PREFIX').'plan_clone AS pc ON sv.pcid=pc.id')
            ->join(C('DB_PREFIX').'plan AS plan ON plan.id=pc.pid')
            ->where("pc.svid='%d'",$uid)->field('sv.id,sv.pcid,sv.status,sv.info,sv.reply,sv.star,sv.title,sv.complete_time,pc.uid,plan.name,plan.face')
            ->order('sv.status ASC,sv.id DESC')->select();
        //p($db->getLastSql());
        //$data=merge_sv_log_plan($data);
        $this->data=$data;
        //p($this->data);
        $this->display();
    }

    /**
     * 完成监督
     */
    public function checkSubmit(){
        if(!IS_POST||!IS_AJAX){
            _404('页面不存在');
        }
        $data=array(
            'status'    =>  false,
            'text'      =>  ''
        );

        $uid=session('uid');
        $sid=I('post.sid',0,'intval');
        $star=I('post.star',0,'intval')%6;//星级1-5
        $reply=I('post.content');
        $len=mb_strlen($reply,'utf-8');

        $db_sl=M('supervision_log');

        if($len<1||$len>230){
            $data['text']='评论不得为空或大于230字！';
            $this->ajaxReturn($data);
        }
        //从数据库中获取数据。
        $arr_sl=$db_sl
            ->table(C('DB_PREFIX').'supervision_log AS sl')
            ->join(C('DB_PREFIX').'plan_clone AS pc ON pc.id=sl.pcid')
            ->join(C('DB_PREFIX').'plan AS plan ON plan.id=pc.pid')
            ->field('sl.status,sl.pcid,sl.title,pc.svid,pc.uid,plan.name')
            ->where("sl.id='%d'",$sid)->find();
        //$data['sql']=$db_sl->getLastSql();
        //$data['arr']=$arr_sl;

        //错误处理
        if($arr_sl['svid']!=$uid){
            $data['text']='无权操作!';
            $this->ajaxReturn($data);
        }
        if($arr_sl['status']){
            $data['text']='已经检阅过啦！';
            $this->ajaxReturn($data);
        }

        //保存数据
        $arr=array(
            'id'        =>  $sid,
            'status'    =>  1,
            'reply'     =>  $reply,
            'star'      =>  $star,
            'reply_time'=>  time(),
        );
        if($db_sl->save($arr)){

            //plan_clone中的uid
            $rid=$arr_sl['uid'];
            //发送信息，不用站内信，直接通知
            load('@/message');
            $content=$this->user['username'].'在'.date('Y年m月d H:i:s').'检阅了您的学习进度报告《'.$arr_sl['title'].'》，并给出'.$star.'星评价：'.$reply;
            sendMessage($uid,$rid,'学习报告通知',$content,get_email($rid));
            $data['status']=true;
        }else{
            $data['text']='由于未知原因，操作失败！';
        }
        $this->ajaxReturn($data);
    }

    /**
     * 同意申请
     */
    public function agree(){
        if(!IS_AJAX||!IS_POST)
            $this->error('页面不存在！');
        $uid=session('uid');
        $req_id=I('post.req_id',0,'intval');
        $db=M('supervision_request');
        $db_pc=M('plan_clone');

        $data=array(
            'status'    =>  false,
            'text'      =>  '',
        );
        if(!checkUrlUniqid(I('post.uniqid'),GROUP_NAME.'/Supervision/request')){
            $data['text']='表单验证码错误，请刷新重试！';
            $this->ajaxReturn($data);
        }
        $req=$db->find($req_id);
        if(empty($req)){
            $data['rid']=$req;
            $data['text']='该申请不存在！';
            $this->ajaxReturn($data);
        }
        if($req['rid']!=$uid){
            $data['text']='无权操作！';
            $this->ajaxReturn($data);
        }
        $arr=array(
            'id'        =>  $req_id,
            'status'    =>  1,
        );
        $db->save($arr);
        $pcid=$req['pcid'];
        $pc=$db_pc->field('svid')->find($pcid);
        if(!empty($pc['svid'])){
            $data['text']='该计划已经有人监督了，下次早点来吧！';
            $this->ajaxReturn($data);
        }
        $arr=array(
            'id'    =>  $pcid,
            'svid'  =>  $uid,
        );
        if(M('plan_clone')->save($arr)){
            $plan=M('plan')->field('name')->find($pc['pid']);
            //短信通知
            load('@/message');
            $content=$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'同意了您的计划 <a href="'.U(GROUP_NAME.'/Plan/detail',array('pcid'=>$pc['id']),false,false,true).'" target="_blanks">'.$plan['name'].'</a>的监督申请！';
            sendMessage($uid,$req['fid'],'系统通知',$content,get_email($req['fid']));
            $data['status']=true;
        }else{
            $data['text']='监督状态没有任何修改！';
        }
        $this->ajaxReturn($data);
    }
    public function refuse(){
        if(!IS_AJAX||!IS_POST)
            $this->error('页面不存在！');
        $uid=session('uid');
        $req_id=I('post.req_id',0,'intval');
        $db=M('supervision_request');
        $db_pc=M('plan_clone');

        $data=array(
            'status'    =>  false,
            'text'      =>  '',
        );
        if(!checkUrlUniqid(I('post.uniqid'),GROUP_NAME.'/Supervision/request')){
            $data['text']='表单验证码错误，请刷新重试！';
            $this->ajaxReturn($data);
        }
        $req=$db->find($req_id);
        if(empty($req)){
            $data['rid']=$req;
            $data['text']='该申请不存在！';
            $this->ajaxReturn($data);
        }
        if($req['rid']!=$uid){
            $data['text']='无权操作！';
            $this->ajaxReturn($data);
        }
        if($req['status']){
            $data['text']='您已经同意申请，不要再拒绝了！';
            $this->ajaxReturn($data);
        }

        //取出plan
        $pcid=$req['pcid'];
        $plan_clone=$db_pc
            ->table(C('DB_PREFIX').'plan_clone AS pc')
            ->join(C('DB_PREFIX').'plan AS plan ON plan.id=pc.pid')
            ->where("pc.id='%d'",$pcid)
            ->field('pc.id,plan.name')->find();


        //拒绝，则发送站内信提醒
        load('@/message');
        $content=$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'拒绝您的计划 <a href="'.U(GROUP_NAME.'/Plan/detail',array('pcid'=>$plan_clone['id']),false,false,true).'" target="_blanks">'.$plan_clone['name'].'</a>的监督申请！';
        sendMessage($uid,$req['fid'],'系统通知',$content,get_email($req['fid']));

        if($db->delete($req_id)){
            $data['status']=true;
        }else{
            $data['text']='由于未知原因，操作失败！';
        }
        $this->ajaxReturn($data);
    }
    /**
     * 在抢先之后，才出现的[删除]选项和[解除监督]选项只是在是否清空plan_clone中的svid这里的不同，所以可以合并在一起。
     */
    function del(){
        if(!IS_AJAX||!IS_POST)
            $this->error('页面不存在！');
        $uid=session('uid');
        $req_id=I('post.req_id',0,'intval');
        $db=M('supervision_request');
        $db_pc=M('plan_clone');

        $data=array(
            'status'    =>  false,
            'text'      =>  '',
        );
        if(!checkUrlUniqid(I('post.uniqid'),GROUP_NAME.'/Supervision/request')){
            $data['text']='表单验证码错误，请刷新重试！';
            $this->ajaxReturn($data);
        }
        $req=$db->find($req_id);
        if(empty($req)){
            $data['rid']=$req;
            $data['text']='该申请不存在！';
            $this->ajaxReturn($data);
        }
        if($req['rid']!=$uid){
            $data['text']='无权操作！';
            $this->ajaxReturn($data);
        }
        $pcid=$req['pcid'];
        $plan_clone=$db_pc->field('id,svid,pid')->find($pcid);
        //如果plan_clone中的svid与自己的相同，就说明是解除监督
        if($plan_clone['svid']==$uid){
            $arr=array(
                'id'    =>  $pcid,
                'svid'  =>  0,
            );
            if(!$db_pc->save($arr)){
                $data['sql']=$db_pc->getLastSql();
                $data['text']='由于未知原因，解除关系失败！';
                $this->ajaxReturn($data);
                add_log('解除关系失败！pcid='.$pcid);
            }
            $plan=M('plan')->field('name')->find($plan_clone['pid']);
            //在解除监督这里，需要站内信提醒，而删除那里则不用。
            load('@/message');
            $content=$this->user['username'].'在'.date('Y-m-d H:i:s',time()).'解除了和您的计划 <a href="'.U(GROUP_NAME.'/Plan/detail',array('pcid'=>$plan_clone['id']),false,false,true).'" target="_blanks">'.$plan['name'].'</a>解除监督关系！';
            sendMessage($uid,$req['fid'],'系统通知',$content,get_email($req['fid']));
        }
        if($db->delete($req_id)){
            $data['status']=true;
        }else{
            $data['text']='由于未知原因，操作失败！';
        }
        $this->ajaxReturn($data);
    }
}