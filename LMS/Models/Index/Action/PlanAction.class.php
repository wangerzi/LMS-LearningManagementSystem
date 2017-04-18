<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/23 0023
 * Time: 下午 8:13
 */
class PlanAction extends CommonAction
{
    /*学习计划首页，计划列表！*/
    public function index(){
        import('ORG.Util.Page');

        $uid=session('uid');
        $db=D('plan_clone');
        $type=I('get.type');


        $map=array(
            'uid'   =>  array('eq',$uid),
        );
        $continue=array(
            'complete_time' => array('exp', 'IS null'),
        );
        $end=array(
            'complete_time' => array('exp', 'IS NOT null'),
        );

        //各个类型的学习计划数据统计
        $plan_num=array(
            'all'   =>  $db->where($map)->count(),
            'continue'  =>$db->where(array_merge($map,$continue))->count(),
            'end'   =>  $db->where(array_merge($map,$end))->count(),
        );
        //这代表正常顺序，从pci里边取
        $mode=1;

        //加载函数库！
        load('@/plan');


        //正在进行的
        if($type=='continue') {
            $message='正在进行计划';
            $info='正在进行计划';
            $color = 'info';
            $count=$plan_num['continue'];
            $map=array_merge($map,$continue);
        }//结束的
        elseif($type=='end') {
            $message='已完成计划';
            $info='已完成计划';
            $color = 'warning';
            $count=$plan_num['end'];
            $map=array_merge($map,$end);
        }elseif($type=='today_complete'){//今日完成,今日未完成，之所以放在一起，是因为今日已完成完成计划的PID有利于效率优化。
            $message='今日已完成';
            $info='今日已完成计划';
            $color = 'success';

            //这标记代表已经取好了，直接用
            $mode=0;


            //由于今日已完成计划中可能含有已经完成的计划，所以将所有可能的计划都取出来
            $temp = array(
                'uid'   =>  array('eq',$uid),
                'time'  =>  array(array('gt',get_time(0)),array('lt',time()),'and'),
            );
            $plan=M('mission_complete')->field('pcid')->where($temp)->group('pcid')->select();
            foreach($plan as $key=>$value){
                $plan[$key] = $value['pcid'];
            }
            $plan = $db->detail($plan);
            foreach($plan as $key => $value){
                if($value['complete_time'] or !$value['active_status']['today_complete'])//这里效率有点低了，但由于不知道从哪里开始，到哪里结束，效率优化也没有办法。
                    unset($plan[$key]);
            }
            //p(M('mission_complete')->getLastSql());
            //p($plan);
            $count=count($plan);
        }
        elseif($type=='today_not_complete'){
            $message='今日未完成';
            $info='今日未完成计划';
            $color = 'danger';

            //这标记代表已经取好了，直接用
            $mode=0;

            //由于进行中计划中包含未完成计划，所以将所有可能的计划都取出来
            $temp = array(
                'uid'           =>  array('eq',$uid),
                'complete_time' =>  array('exp','IS NULL')
            );
            $plan=$db->field('id')->where($temp)->select();
            foreach($plan as $key=>$value){
                $plan[$key] = $value['id'];
            }
            $plan = $db->detail($plan);
            foreach($plan as $key => $value){
                if($value['complete_time'] or $value['active_status']['today_complete'])//这里效率有点低了。。
                    unset($plan[$key]);
            }
            $count=count($plan);
        }
        else{
            $message='全部学习计划';
            $info='计划';
            $color = 'primary';
            $count=$plan_num['all'];
        }
        //小标题
        $this->title=$message;
        $this->color = $color;
        //当没有内容的时候，描述不一样。
        $this->title_info=$info;

        $listRows=6;
        $page=new Page($count,$listRows);
        $page->setConfig('theme','%first% %upPage% %linkPage% %downPage% %end%');
        $this->page=$page->show();

        if($mode) {
            $arr = $db->field('id')->where($map)->order('create_time desc,complete_time desc')->limit($page->firstRow, $page->listRows)->select();
            if(!empty($arr)){
                foreach($arr as $key=>$value)
                    $arr[$key] = $arr[$key]['id'];
                $this->data=D('plan_clone')->detail($arr);
            }else{
                $this->data=null;
            }
        }
        else {
            $arr = array_slice($plan, $page->firstRow, $page->listRows);
            $this->data = $arr;
        }

        $this->total=count($arr);

        //p($this->data);
        $this->display();
    }
    public function detail(){
        if(!isset($_GET['pcid'])||IS_POST)
            _404('页面不存在！');
        /*if(!checkUrlUniqid(I('get.uniqid'),GROUP_NAME.'/Plan/index'))
            $this->error('您的URL验证已失效，为了您的安全，请刷新原页面后重新打开此页面！');*/
        $uid=session('uid');
        $db=D('plan_clone');
        $pcid = I('get.pcid',0,'intval');

        //这里让监督者也能看到计划详情
        $data=$db->where("id='%d' AND (uid='%d' OR svid='%d')",$pcid,$uid,$uid)->find();
        if(empty($data))
            $this->error('计划不存在！');
        //更新计划信息
        M('plan')->where("id='%d'",$data['pid'])->setInc('saw',1);
        //p(M('plan')->getLastSql());

        //为点赞初始化验证码。
        $this->initUniqid(GROUP_NAME.'/Plan/praise');

        load('@/plan');
        plan_complete($pcid);//需要更新下是否已完成。

        $plan = $db->detail($pcid,1,true);
        //p($plan);
        $this->data=$plan;
        //是否是监督者
        $this->supervision=$data['svid']==$uid;
        //p($this->data);

        $pid=$data['pid'];
        $data=M('plan_praise')->where("uid='%d' AND pid='%d'",$uid,$pid)->find();
        if(empty($data))
            $this->praised=0;
        else
            $this->praised=1;
        $map=array(
            'pid'   =>  array('eq',$pid)
        );
        $this->delay = round($plan['active_status']['delay_time']*1.0/86400,2);
        $this->studyPeople=$db->where($map)->count();//学习人数
        $this->display();
    }

    /**
     * 学习记录，计算计划状态比较耗费服务器资源，为了减少负担，log页面做的简单点。
     */
    public function log(){
        $uid=session('uid');
        $pcid=I('get.pcid',0,'intval');
        $db_pc=M('plan_clone');
        $db_cop=M('mission_complete');

        $plan_clone=$db_pc->find($pcid);
        if(empty($plan_clone))
            $this->error('没有此计划');
        //如果不是所有者和监督者，则无法查看
        if($plan_clone['svid']!=$uid && $plan_clone['uid']!=$uid)
            $this->error('无权查看');
        load('@/plan');
        //由于需要监督者名字，计划名称等，里边包含数据库操作，效率着想，违规情况无需进行此操作，所以放在后边
        $plan_clone=D('plan_clone')->detail($pcid,1,true);
        //如果是监督者看的，那么重新读取表
        if($plan_clone['svid']==$uid){
            $plan_clone['user']=M('user')->field('id,username')->find($plan_clone['uid']);
        }else {
            $plan_clone['user'] = $this->user;
        }

        //获取记录，这里可能要做ajax翻页，但挺麻烦的
        $complete_mission=$db_cop->table(C('DB_PREFIX').'mission_complete as mc')->join(C('DB_PREFIX')."supervision_log as sl ON sl.mcid=mc.id")->where("mc.pcid='%d'",$pcid)->order('mc.time DESC')->limit(20)->select();
        //p($complete_mission);
        //p($db_cop->getLastSql());

        //是否是监督者
        $this->supervision=$plan_clone['svid']==$uid;

        $this->plan_clone=$plan_clone;
        //p($this->plan_clone);
        $this->data=$complete_mission;
        $this->display();
    }
    public function comment(){
        $uid=session('uid');
        $pid=I('get.pid',0,'intval');
        $db_plan=M('plan');
        $db_comment=M('plan_comment');
        $map=array(
            'pid'   =>  array('eq',$pid),
            'rid'   =>  array('eq',0)
        );

        $plan=$db_plan->find($pid);
        if(empty($plan))
            $this->error('计划不存在');

        //统计星星数，由于rid>0的时候，不能评星，所以跳过
        $plan['star']=intval($db_comment->where("pid='%d' AND rid = 0",$plan['id'])->avg('star'));
        //统计评价星星的人数
        $plan['comment_star']=intval($db_comment->where("pid='%d' AND rid = 0",$plan['id'])->count());
        $plan['creator']=M('user')->field('id,username')->find($plan['uid']);

        //为评价提交附上验证码
        $this->initUniqid(GROUP_NAME.'/Plan/commentHandle');

        //分页类
        import('ORG.Util.Page');
        $listRows=5;
        $count=$db_comment->where($map)->count();
        $page=new Page($count,$listRows);

        load('@/comment');
        //提取数据
        $comment=$db_comment->where($map)->order('time DESC')->limit($page->firstRow,$page->listRows)->select();
        $comment=merge_comment($comment);

        //分配数据
        $this->plan=$plan;
        $this->comment=$comment;

        $page->setConfig('theme','%first% %upPage% %linkPage% %downPage% %end%');
        $this->page=$page->show();

        $this->display();
    }
    public function commentHandle(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在');
        $data=array(
            'status'=>false,
            'text'=>'',
        );
        if(!checkUrlUniqid(I('post.uniqid'))){
            $data['text']='表单验证码不匹配，请刷新后重试!';
            $this->ajaxReturn($data);
        }
        //获取相关数据。
        $content=I('post.content');
        $rid=I('post.rid',0,'intval');
        $pid=I('post.pid',0,'intval');
        $star=I('post.star',0,'intval')%6;//星级只能在0-5
        $uid=session('uid');
        $db=M('plan_comment');

        load('@/check');
        load('@/message');
        //长度检测
        $len=mb_strlen($content,'utf-8');
        if($len<0||$len>230){
            $data['text']='评论长度不得为空或多于'.$len.'字';
            $this->ajaxReturn($data);
        }
        //存在检测，以免加入数据库后，相关计划不存在，但新建完成后莫名出现评论
		$plan=M('plan')->field('id,uid,name')->find($pid);
        if(empty($plan)){
            $data['text']='不存在的计划';
            $this->ajaxReturn($data);
        }
        //评论合格性检测
        if($rid!=0){
            $r_comment=$db->field('uid,rid')->find($rid);
            if(empty($r_comment)){
                $data['text']='回复的评论不存在!';
                $this->ajaxReturn($data);
            }
            //如果是回复的话，把被回复的信息放下来
            $ruser=M('user')->field('username')->find($r_comment['uid']);
            $content='回复'.$ruser['username'].':<br>'.$content;

            //强制作为一层评论
            if($r_comment['rid']!=0) {
                $rid = $r_comment['rid'];
            }
            $data['rid']=$r_comment;
        }
        //每小时最多评论10次
        $map=array(
            'uid'   =>  $uid,
            'pid'   =>  $pid,
            'time'  =>  array(array('lt',time()),array('gt',time()-3600))
        );
        $num=$db->where($map)->count();
        if($num>10){
            $data['text']='一小时内，同一计划只能评论10次!';
            $this->ajaxReturn($data);
        }
        $arr=array(
            'rid'   =>  $rid,
            'uid'   =>  $uid,
            'star'  =>  $star,
            'pid'   =>  $pid,
            'content'=> $content,
            'time'  =>  time(),
        );
        if($db->add($arr)){
            //发送消息
            load('@/Message');
            $rid=$plan['uid'];
            $content=$this->user['username'].'在北京时间 '.date('Y年m月d日 H:i:s').' 对您的计划《'.$plan['name'].'》做出评论：<a href="'.U(GROUP_NAME.'/Plan/comment',array('pid'=>$plan['id']),false,false,true).'">回复</a><br>'.$content;
            sendMessage($uid,$rid,C('WEB_NAME'),$content,get_email($rid));
            $data['status']=true;
        }else{
            $data['text']='由于未知原因，添加失败！';
        }
        $this->ajaxReturn($data);
    }
    /*用于完成任务！*/
    public function mission_complete(){
        if(!IS_AJAX||!IS_POST)
            __404('页面不存在！');
        $data=array(
            'status'    => false,
            'text'      => '',
        );

        $uid=session('uid');
        $pcid=I('get.pcid',0,'intval');
        $mid=I('post.id',0,'intval');
        $db=M('mission_complete');
        $db_sl=M('supervision_log');

        //查看是否完成
        if($db->where("uid='%d' AND mid='%d' AND pcid='%d'",$uid,$mid,$pcid)->limit(1)->select()){
            $data['text']='该任务已完成过！';
            $this->ajaxReturn($data);
        }

        $plan_clone=M('plan_clone')->field('id,pid,uid,start,svid')->where("uid='%d' AND id='%d'",$uid,$pcid)->find();
        $mission=M('mission')->find($mid);
        $stage=M('stage')->find($mission['sid']);
        $plan=M('plan')->find($stage['pid']);//反向查询计划，然后验证plan和plan_clone是否相契合。
        //检查是否存在任务和计划！
        if(empty($plan_clone)||empty($mission)||empty($stage)||empty($plan)) {
            $data['text']='计划不存在！';
            $this->ajaxReturn($data);
        }
        //检查计划ID和当前操作的plan_clone里的pid是否相同。
        if($plan['id']!=$plan_clone['pid']){
            $data['text']='操作失败，任务与计划不对应！';
            $this->ajaxReturn($data);
        }
        if($plan_clone['start']>time()){
            $data['text']='操作失败，任务未开始！';
            $this->ajaxReturn($data);
        }
        $num_hour=$db->where("uid='%d' AND pcid='%d' AND time>'%d' AND time<'%d'",$uid,$pcid,time()-3600,time())->count();
        if($num_hour>=C('PLAN_MISSION_COMPLETE_TIME_HOUR')){
            $data['text']='操作失败，同一计划每小时只能完成'.C('PLAN_MISSION_COMPLETE_TIME_HOUR').'次任务！';
            $this->ajaxReturn($data);
        }
        //传递上来的完成日志
        $info=I('post.info');
        if(empty($info)){
            $data['text']='完成日志不得为空';
            $this->ajaxReturn($data);
        }
        //任务完成表和都需要时间。
        $title='进度：'.$stage['name'].' --- '.$mission['name'];
        $time=time();
        $arr=array(
            'uid' => $uid,
            'mid' => $mid,
            'pcid'=> $pcid,
            'time'=> $time,
        );
        //加入任务完成表
        if(!$mcid=$db->add($arr)){
            $data['text']='操作失败，请重试！';
            $this->ajaxReturn($data);
        }else{
            load('@/plan');

            $arr=array(
                'pcid'  =>  $pcid,
                'rid'   =>  $plan_clone['svid'],
                'mcid'  =>  $mcid,
                'info'  =>  $info,
                'status'=>  0,
                'title' =>  $title,
                'complete_time'=> $time,
            );
            if(!$db_sl->add($arr)){
                $data['text']='任务汇报失败，请重试！';
                $this->ajaxReturn($data);
            }
            //如果完成整个计划，则更新完成时间。
            plan_complete($pcid);
            $exp=$this->level['complete_exp'];
            M('user')->where("id='%d'",$uid)->setInc('exp',$exp);

            //给监督者发信。
            if($plan_clone['svid']){
                load('@/email');
                load('@/message');
                addEmailTimeQueue(get_email($plan_clone['svid']),'监督者','您监督的学习计划有了新进展','您监督的学习计划《'.$plan['name'].'》在北京时间'.date('Y年m月d日 H:i:s',time()).'提交了学习总结，进入系统<a href="'.U(GROUP_NAME.'/Supervision/waiting','',true,false,true).'">检阅进度</a>吧！',time());
            }
            $data['status']=true;
            $data['time']=date('Y年m月d日 H:i:s',$time);
            $data['exp']=$exp;
            $this->ajaxReturn($data);
        }
    }

    /**
     * 分享计划
     */
    public function share(){
        $pid=I('get.pid',0,'intval');
        $uid = session('uid');

        $db=D('plan');
        $plan=$db->field('id,uid,face,open,mode,name,create_time,last_edit_time,praised,saw')->find($pid);
        if(empty($plan)){
            $this->error('该计划不存在');
        }
        //$plan['mission_total']=$db->table(C('DB_PREFIX').'plan AS p')->join(C('DB_PREFIX').'stage AS s ON s.pid=p.id')->join(C('DB_PREFIX').'mission AS m ON m.sid=s.id')->where("p.id='%d'",$pid)->count();
        //p($db->getLastSql());

        //在计划公开的情况下查询数据
        if($plan['open']){
            $plan['stage'] = $db->get_stage_mission_by_pid($pid);

            $mission_num=0;
            $total_power=0;
            foreach($plan['stage'] as $key=>$value){
                $plan['stage'][$key]['power'] = $value['power']?$value['power']:10;
                $total_power += $plan['stage'][$key]['power'];
            }
            foreach($plan['stage'] as $key=>$value){
                $num = count($value['mission']);
                $mission_num += $num;

                $plan['stage'][$key]['avg_rate'] = $value['power']*1.0/$total_power;
                $plan['stage'][$key]['m_avg_rate'] = $value['power']*1.0/$total_power/$num;
            }
            $plan['mission_total']=$mission_num;
        }
        //创造者
        $plan['creator']=M('user')->field('id,username')->find($plan['uid']);
        $plan['joined'] = M('plan_clone')->field('id,create_time')->where('uid=%d AND pid=%d',$uid,$pid)->find();

        $plan['active_status'] = array(
            //评论区
            'comment_num'           =>  M('plan_comment')->where('pid=%d',$pid)->count(),
            'comment_star'          =>  M('plan_comment')->where('pid=%d AND rid=0',$pid)->count(),
            'star'                  =>  floor(M('plan_comment')->where('pid=%d AND rid=0',$pid)->avg('star')),//floor函数在这里比较科学。
        );
        $this->data=$plan;
        //p($this->data);
        //更新计划信息，查看人数+1
        M('plan')->where("id='%d'",$plan['id'])->setInc('saw',1);


        //为点赞初始化验证码。
        $this->initUniqid(GROUP_NAME.'/Plan/praise');
        $data=M('plan_praise')->where("uid='%d' AND pid='%d'",session('uid'),$pid)->find();
        if(empty($data))
            $this->praised=0;
        else
            $this->praised=1;

        $map=array(
            'pid'   =>  array('eq',$pid)
        );
        $this->studyPeople=M('plan_clone')->where($map)->count();

        //为加入计划初始化验证码
        $this->initUniqid(GROUP_NAME.'/Plan/join','plan_join_');
        $this->display();
    }
    public function join(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');
        $this->checkFormUniqid(I('post.uniqid'));
        import('imageCode',APP_PATH.C('APP_GROUP_PATH').'/'.GROUP_NAME.'/Class/');
        if(!imageCode::check('plan_verify',I('post.verify')))
            $this->error('验证码不正确');


        $uid = session('uid');
        $pid = I('post.pid');
        $start = I('post.start',get_time(0),'strtotime');
        $end = I('post.end',get_time(0),'strtotime');

        if($start>$end)
            $this->error('开始时间大于结束时间');

        $db_pc = M('plan_clone');
        $db_p = M('plan');

        //检测是否开放和计划是否存在。
        $plan = $db_p->field('open')->find($pid);
        if(empty($plan)){
            $this->error('没有此计划');
        }
        if(!$plan['open']){
            $this->error('添加失败，该计划未公开');
        }

        //检测是否已经加入
        $plan_clone = $db_pc->where('pid=%d AND uid=%d',$pid,$uid)->field('create_time')->find();

        if(!empty($plan_clone)){
            $this->error('您在'.date('Y-m-d H:i:s',$plan_clone['create_time']).'已经加入过此计划了，不可重复加入！');
        }
        //清除表单验证。
        clearUniqid();
        imageCode::remove('plan_verify');

        $arr = array(
            'pid'           =>  $pid,
            'uid'           =>  $uid,
            'start'         =>  $start,
            'end'           =>  $end,
            'create_time'   =>  time(),
        );
        $plan_clone=$db_pc->add($arr);

        if(empty($plan_clone)) {
            $this->error('加入计划失败');
        }
        $this->success('加入成功');
    }

    /**
     * 赞一个计划
     */
    public function praise(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在！');
        $data=array(
            'status'    =>  false,
            'text'      =>  '',
        );
        if(!checkUrlUniqid(I('post.uniqid'))){
            $data['text']='页面标识码不匹配，请刷新重试！';
            $this->ajaxReturn($data);
        }
        $pid=I('post.pid',0,'intval');
        $uid=session('uid');

        //用find查找计划，直接返回信息！
        $plan=M('plan')->field('id,name,uid')->find($pid);
        //检查计划是否存在！
        if(empty($plan)){
            $data['text']='计划不存在！';
            $this->ajaxReturn($data);
        }

        $pid=$plan['id'];
        //检查是否赞过！
        if(count(M('plan_praise')->where("pid='%d' AND uid='%d'",$pid,$uid)->limit(1)->select())>0){
            $data['text']='一个计划只能点一个赞，您已经赞过了！';
            $this->ajaxReturn($data);
        }
        clearUniqid();
        //加入数据库！
        $arr=array(
            'pid'   =>  $pid,
            'uid'   =>  $uid,
            'time'  =>  time(),
        );
        if(!M('plan_praise')->add($arr)||!M('plan')->where("id='%d'",$pid)->setInc('praised',1)){
            $data['text']='由于未知原因，点赞失败，请重试！';
            $this->ajaxReturn($data);
        }

        if($uid!=$plan['uid']){
            //发送消息
            load('@/message');
            $rid=$plan['uid'];
            $content=$this->user['username'].'在北京时间 '.date('Y年m月d日 H:i:s').' 对您的计划《'.$plan['name'].'》点了一个赞！';
            sendMessage($uid,$rid,C('WEB_NAME'),$content,get_email($rid));
        }

        $data['status']=true;
        $data['text']='点赞成功！';
        $this->ajaxReturn($data);
    }
    /*删除计划！*/
    public function delete(){
        if(!IS_POST||!IS_AJAX)
            _404('页面不存在！');
        if(!checkFormUniqid(I('post.uniqid'),GROUP_NAME.'/Plan/index'))
            $this->error('页面标识码不匹配，请刷新后重试！');
        $data=array(
            'status'=>  false,
            'info'  =>  '',
            'uniqid'=>  '',
        );

        //获取pcid
        $pcid=I('post.pcid',0,'intval');
        //获取uid
        $uid=session('uid');

        //找到计划
        $plan_clone=M('plan_clone')->field('svid,uid,pid')->find($pcid);
        //错误处理
        if(empty($plan_clone))
            $this->error('没有该计划');
        if($plan_clone['uid']!=$uid)
            $this->error('您没有权限删除该计划！');

        //关于删除源计划，即使是私有的计划，也可能被分享过，应该判断除了自己有多少人在用这个计划，还是留着？。
		$map = array(
			'pid'	=>	array('eq',$plan_clone['pid'])
		);
        $num = M('plan_clone')->where($map)->count();
        $plan = M('plan')->field('open,saw')->find($plan_clone['pid']);
		if($num <= 1 && !$plan['open']){//删除只有一个人的，并且是私有的任务。
            M('plan')->delete($plan_clone['pid']);
			M('mission')->table(C('DB_PREFIX').'mission AS m')->join(C('DB_PREFIX').'mission_complete AS mc ON mc.mid = m.id')->join('INNER JOIN '.C('DB_PREFIX').'stage AS s on m.sid=s.id s.pid='.$plan_clone['pid'])->delete();
            M('stage')->where('pid=%d',$plan_clone['pid'])->delete();
		}


        //删除计划
        if(M('plan_clone')->delete($pcid)) {
            //给监督者发条短信
            if(!empty($plan_clone['svid'])){
                load('@/message');
                sendMessage($uid,$plan_clone['svid'],'系统通知','您监督的计划被使用者删除！',get_email($plan_clone['svid']));
            }


            //把相应的mission_complete删掉.
            $map=array(
                'pcid' => array('eq',$pcid),
            );
            M('mission_complete')->where($map)->delete();

            //把相应的监督申请关系也删掉
            M('supervision_request')->where($map)->delete();
            //$this->redirect(GROUP_NAME . '/Plan/index');
            $data['status']=true;
            $this->initUniqid(GROUP_NAME.'/Plan/index');
            $data['uniqid']=$this->uniqid;
            $this->ajaxReturn($data);
        }
        else
            $this->error('由于未知原因,删除失败，请重试！');
    }
    //编辑计划
    public function edit(){
        if(!isset($_GET['pcid']))
            _404('页面不存在！');
        /*if(!checkUrlUniqid(I('get.uniqid'),GROUP_NAME.'/Plan/index'))
            $this->error('页面标识码不匹配，请刷新后重试！',U(GROUP_NAME.'/Plan/index'));*/

        $uid=session('uid');
        $pcid=I('get.pcid',0,'intval');

        $this->initUniqid(GROUP_NAME.'/Plan/editHandle');

        //操作的uniqid，准备试一下动态更新标识码
        $uniqid=get_uniqid();
        session('operate_uniqid',$uniqid);
        $this->operate_uniqid=$uniqid;

        $plan_clone=M('plan_clone')
            ->table(C('DB_PREFIX').'plan_clone AS pc')
            ->join(C('DB_PREFIX').'plan AS plan ON pc.pid=plan.id')
            ->field('pc.id,pc.start,pc.end,pc.svid,pc.pid,plan.uid,plan.name,plan.open,plan.mode,plan.face')
            ->where("pc.id='%d' AND pc.uid='%d'",$pcid,$uid)
            ->find();

        //如果是所有者才加载信息
        if($plan_clone['uid']==$uid){
            load('@/plan');
            $plan_clone['stage']=get_stage($plan_clone['pid']);
            $this->owner=true;//所有者修改标记
        }
        $this->data=$plan_clone;
        //p($plan_clone);

        if($plan_clone['svid']){
            $this->svuser=M('user')->field('id,username,face')->find($plan_clone['svid']);
        }else{
            //加载朋友信息
            load('@/friend');
            $this->friends=merge_friend(get_friends_all($uid),$uid);
        }
        if(empty($plan_clone)){
            $this->error('计划不存在！');
        }

        $this->display();
    }
    public function editHandle(){
        if(!IS_POST||!IS_AJAX){
            _404('页面不存在');
        }

        $uid=session('uid');
        $pcid=I('post.pcid',0,'intval');
        $type=I('post.type');
        $db=M('plan');
        $db_pc=M('plan_clone');

        $plan_clone=$db_pc
            ->table(C('DB_PREFIX').'plan_clone AS pc')
            ->join(C('DB_PREFIX').'plan AS p ON p.id=pc.pid')
            ->where("pc.id='%d'",$pcid)
            ->field('p.uid,p.name,p.face,pc.pid,pc.svid,pc.uid AS pcuid')
            ->find();

        if(empty($plan_clone)){
            $this->error('没有此计划');
        }

        $pid=$plan_clone['pid'];

        $data=array(
            'status'    =>  false,
            'info'      =>  '',
        );
        if(!checkUrlUniqid(I('post.uniqid'))){
            $this->error('表单标识码不匹配，可能是您操作过快，请稍后重试或刷新页面');
        }

        //分类处理
        switch($type){
            case 'basic':
                if($plan_clone['uid']!=$uid)
                    $this->error('无权操作');

                $arr=array(
                    'id'    =>  $pid,
                    'name'  =>  I('post.name'),
                    'open'  =>  I('post.open',0,'intval')%2,
                    'mode'  =>  I('post.mode',0,'intval')%2
                );

                load('@/check');
                if($str=mb_check_stringLen($arr['name'],C('PLAN_MIN_NAME'),C('PLAN_MAX_NAME'))!=true)
                    $this->error($str);

                if($db->save($arr)){
                    $data['status']=true;
                    break;//跳出，然后执行后边的分配随机码
                }else{
                    $this->error('没有数据被修改');
                }
                break;
            case 'face':
                if($plan_clone['uid']!=$uid)
                    $this->error('无权操作');

                //保存封面
                load('@/account');
                $faces=save_user_image($uid);
                if($faces['status'])
                    $_POST['face']=$faces['path'];
                else
                    $this->error($faces['info']);

                $arr=array(
                    'id'    =>  $pid,
                    'face'  =>  $_POST['face'],
                );
                if($db->save($arr)){
                    unlink(get_thumb_file($plan_clone['face'],'m_'));//删除之前的图片。
                    unlink(get_thumb_file($plan_clone['face'],'s_'));//删除之前的图片。
                    $data['status']=true;
                    $data['face']=get_thumb_file($_POST['face'],'m_');
                    break;//跳出，然后执行后边的分配随机码
                }else{
                    $this->error('没有数据被修改');
                }
                break;
            case 'sv':
                if($plan_clone['pcuid']!=$uid)
                    $this->error('无权操作');
                if($plan_clone['svid'])
                    $this->error('您已经有监督人了');


                $data['status']=true;

                $supervision=I('post.supervision',0,'intval');

                //发送监督申请
                load('@/supervision');
                $num=send_supervision_requests($pcid,$supervision,$plan_clone,$uid,$this->user['username'],true);
                $data['num']=$num;
                break;
            case 'restart':
                if($plan_clone['pcuid']!=$uid)
                    $this->error('无权操作');

                $db_mc = M('mission_complete');
                $start = I('post.start',get_time(0),'strtotime');
                $end = I('post.end',get_time(0),'strtotime');

                if($start>$end)
                    $this->error('开始时间大于结束时间');
                //清理验证码。
                imageCode::remove('plan_verify');
                //清理mission_complete表。
                $map = array(
                    'pcid'  =>  $pcid,
                );
                $db_mc->where($map)->delete();
                //清理sv_request表
                M('supervision_request')->where($map)->delete();
                M('supervision_log')->where($map)->delete();

                if($plan_clone['svid']) {
                    //给监督者发送消息
                    load('@/message');
                    sendMessage($uid, $plan_clone['svid'], '监督关系解除', $this->user['username'] . '在北京时间' . date('Y年m月d日 H:i:s', time()) . '重新开始学习计划《' . $plan_clone['name'] . '》，监督关系就此解除！', get_email($plan_clone['svid']));
                }
                $arr = array(
                    'id'    =>  $pcid,
                    'svid'  =>  null,
                    'start' =>  $start,
                    'complete_time' =>  null,
                    'end'   =>  $end,
                );
                if(!$db_pc->save($arr))
                    $this->error('没有数据被修改！');
                $data['status']=true;
                break;
            default:
                $this->error('不存在的类型');
                break;
        }
        //重新为自己初始化随机码并分配到数据中
        $this->initUniqid();
        $data['uniqid']=$this->uniqid;
        $this->ajaxReturn($data);
    }
    //ajax添加mission。
    public function add_mission(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在');

        $uid=session('uid');
        $pid=I('post.pid',0,'intval');

        $sid=I('post.sid',0,'intval');

        $data=array(
            'status'    =>  false,
            'info'      =>  '',
        );
        //检查标识码
        if(I('post.uniqid')!=session('operate_uniqid')){
            $this->error('表单标识码不匹配，可能是您操作过快导致，请稍后重试或者刷新页面');
        }

        //比对阶段和计划是否匹配
        $stage=M('stage')->find($sid);
        if(empty($stage)){
            $this->error('没有该阶段');
        }
        if($stage['pid']!=$pid){
            $this->error('阶段和计划不匹配');
        }

        //比对计划所有者
        $plan=M('plan')->find($pid);
        if(empty($plan)){
            $this->error('没有此计划');
        }
        if($plan['uid']!=$uid){
            $this->error('无权操作');
        }

        //任务信息
        $name=pillStr(I('post.name'),C('MISSION_MIN_NAME'),C('MISSION_MAX_NAME'));
        $info=pillStr(I('post.info'),0,C('MISSION_MAX_INFO'));
        $sort=I('post.sort',0,'intval');

        $arr=array(
            'sid'   =>  $sid,
            'name'  =>  $name,
            'info'  =>  $info,
            'sort'  =>  $sort,
        );
        //添加数据
        if($mid=M('mission')->add($arr)){
            $data['mid']=$mid;
            $data['status']=true;
            //更新标识码
            $uniqid=get_uniqid();
            session('operate_uniqid',$uniqid);
            $data['uniqid']=$uniqid;

            $this->ajaxReturn($data);
        }else{
            $this->error('由于未知原因，新建出错，请稍后重试！');
        }
    }
    //ajax保存mission。
    public function save_mission(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在');

        $uid=session('uid');
        $pid=I('post.pid',0,'intval');

        $mid=I('post.mid',0,'intval');

        $data=array(
            'status'    =>  false,
            'info'      =>  '',
        );
        //检查标识码
        if(I('post.uniqid')!=session('operate_uniqid')){
            $this->error('表单标识码不匹配，可能是您操作过快导致，请稍后重试或者刷新页面');
        }

        //比对计划所有者
        $plan=M('mission')
            ->table(C('DB_PREFIX').'mission AS m')
            ->join(C('DB_PREFIX').'stage AS s ON m.sid=s.id')
            ->join(C('DB_PREFIX').'plan AS p ON p.id=s.pid')
            ->where("m.id='%d'",$mid)
            ->field("p.uid")
            ->find();
        if(empty($plan)){
            $this->error('没有找到此任务');
        }
        if($plan['uid']!=$uid){
            $this->error('无权操作');
        }

        //任务信息
        $name=pillStr(I('post.name'),C('MISSION_MIN_NAME'),C('MISSION_MAX_NAME'));
        $info=pillStr(I('post.info'),0,C('MISSION_MAX_INFO'));
        $sort=abs(I('post.sort',0,'intval'));

        $arr=array(
            'id'    =>  $mid,
            'name'  =>  $name,
            'info'  =>  $info,
            'sort'  =>  $sort,
        );
        //添加数据
        if($mid=M('mission')->save($arr)){
            $data['status']=true;
            //更新标识码
            $uniqid=get_uniqid();
            session('operate_uniqid',$uniqid);
            $data['uniqid']=$uniqid;

            $this->ajaxReturn($data);
        }else{
            $this->error('没有数据被改变');
        }
    }
    //ajax保存mission。
    public function save_stage(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在');

        $uid=session('uid');
        $pid=I('post.pid',0,'intval');

        $sid=I('post.sid',0,'intval');

        $data=array(
            'status'    =>  false,
            'info'      =>  '',
        );
        //检查标识码
        if(I('post.uniqid')!=session('operate_uniqid')){
            $this->error('表单标识码不匹配，可能是您操作过快导致，请稍后重试或者刷新页面');
        }

        //比对计划所有者
        $plan=M('stage')
            ->table(C('DB_PREFIX').'stage AS s')
            ->join(C('DB_PREFIX').'plan AS p ON p.id=s.pid')
            ->where("s.id='%d'",$sid)
            ->field("p.uid")
            ->find();
        if(empty($plan)){
            $this->error('没有找到此任务');
        }
        if($plan['uid']!=$uid){
            $this->error('无权操作');
        }

        //任务信息
        $name=pillStr(I('post.name'),C('STAGE_MIN_NAME'),C('STAGE_MAX_NAME'));
        $info=pillStr(I('post.info'),0,C('STAGE_MAX_INFO'));
        $power=abs(I('post.power',0,'intval')%10000);
        $sort=abs(I('post.sort',0,'intval'));

        $arr=array(
            'id'    =>  $sid,
            'name'  =>  $name,
            'info'  =>  $info,
            'power' =>  $power,
            'sort'  =>  $sort,
        );
        //保存数据
        if($mid=M('stage')->save($arr)){
            $data['status']=true;
            //更新标识码
            $uniqid=get_uniqid();
            session('operate_uniqid',$uniqid);
            $data['uniqid']=$uniqid;

            $this->ajaxReturn($data);
        }else{
            //$this->error(M('stage')->getLastSql());
            $this->error('没有数据被改变');
        }
    }
    public function add_stage(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在');

        $uid=session('uid');
        $pid=I('post.pid',0,'intval');

        $data=array(
            'status'    =>  false,
            'info'      =>  '',
        );
        //检查标识码
        if(I('post.uniqid')!=session('operate_uniqid')){
            $this->error('表单标识码不匹配，可能是您操作过快导致，请稍后重试或者刷新页面');
        }
        
        //比对计划所有者
        $plan=M('plan')->find($pid);
        if(empty($plan)){
            $this->error('没有此计划');
        }
        if($plan['uid']!=$uid){
            $this->error('无权操作');
        }

        //任务信息
        $name=pillStr(I('post.name'),C('STAGE_MIN_NAME'),C('STAGE_MAX_NAME'));
        $info=pillStr(I('post.info'),0,C('STAGE_MAX_INFO'));
        $power=abs(I('post.power',0,'intval')%10000);
        $sort=abs(I('post.sort',0,'intval'));

        $mission=$_POST['mission'];

        $arr=array(
            'pid'   =>  $pid,
            'name'  =>  $name,
            'info'  =>  $info,
            'power' =>  $power,
            'sort'  =>  $sort,
        );
        //添加数据
        if($sid=M('stage')->add($arr)){
            load('@/plan');
            $mission=filterMissions($mission,$sid);
            if(count($mission)<1){
                $this->error('一个阶段最少一个任务');
            }

            $db=M('mission');

            //循环获取新增的mission
            $mid=array();
            foreach($mission as $key=>$value){
                $mid[]=$db->add($value);
            }
            //这里要ID的原因是，需要修改啊。
            $data['mid']=$mid;
            $data['sid']=$sid;

            /*不能获取到所有的ID
             * if(!M('mission')->addAll($mission)){
                $this->error('由于未知原因，新建任务出错，请刷新查看！');
            }*/
            $data['status']=true;
            //更新标识码
            $uniqid=get_uniqid();
            session('operate_uniqid',$uniqid);
            $data['uniqid']=$uniqid;

            $this->ajaxReturn($data);
        }else{
            $this->error('由于未知原因，新建出错，请稍后重试！');
        }
    }
    //移除后需要重新计算
    public function remove_mission(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在');

        $uid=session('uid');
        $pid=I('post.pid',0,'intval');

        $mid=I('post.mid',0,'intval');
        $db=M('mission');

        $data=array(
            'status'    =>  false,
            'info'      =>  '',
        );
        //检查标识码
        if(I('post.uniqid')!=session('operate_uniqid')){
            $this->error('表单标识码不匹配，可能是您操作过快导致，请稍后重试或者刷新页面');
        }

        $mission=$db
            ->table(C('DB_PREFIX').'mission AS m')
            ->join(C('DB_PREFIX').'stage AS s ON s.id=m.sid')
            ->join(C('DB_PREFIX').'plan AS p ON p.id=s.pid')
            ->where("m.id='%d' AND p.id='%d'",$mid,$pid)
            ->field('p.uid,s.id AS sid')
            ->find();
        if(empty($mission)){
            /*$data['sql']=$db->getLastSql();
            $this->ajaxReturn($data);*/
            $this->error('任务不存在');
        }
        if($mission['uid']!=$uid){
            $this->error('无权操作');
        }
        $count=$db->where("sid='%d'",$mission['sid'])->count();
        //只有一个任务了。
        if($count<2){
            $this->error('每个阶段至少一个任务');
        }

        //移除数据！
        if($db->delete($mid)){
            $data['status']=true;
            //更新标识码
            $uniqid=get_uniqid();
            session('operate_uniqid',$uniqid);
            $data['uniqid']=$uniqid;

            $this->ajaxReturn($data);
        }else{
            $this->error('由于未知原因，删除出错，请稍后重试！');
        }
    }
    public function remove_stage(){
        if(!IS_AJAX||!IS_POST)
            _404('页面不存在');

        $uid=session('uid');
        $pid=I('post.pid',0,'intval');

        $sid=I('post.sid',0,'intval');
        $db=M('stage');

        $data=array(
            'status'    =>  false,
            'info'      =>  '',
        );
        //检查标识码
        if(I('post.uniqid')!=session('operate_uniqid')){
            $this->error('表单标识码不匹配，可能是您操作过快导致，请稍后重试或者刷新页面');
        }

        $stage=$db
            ->table(C('DB_PREFIX').'stage AS s')
            ->join(C('DB_PREFIX').'plan AS p ON p.id=s.pid')
            ->where("s.id='%d'",$sid)
            ->field('p.uid,s.pid')
            ->find();
        if(empty($stage)){
            /*$data['sql']=$db->getLastSql();
            $this->ajaxReturn($data);*/
            $this->error('阶段不存在');
        }
        if($stage['uid']!=$uid){
            $this->error('无权操作');
        }
        $count=$db->where("pid='%d'",$stage['pid'])->count();
        //只有一个阶段了。
        if($count<2){
            $this->error('每个计划至少一个阶段');
        }

        //移除数据！
        if($db->delete($sid) && M('mission')->where("sid='%d'",$sid)->delete()){
            $data['status']=true;
            //更新标识码
            $uniqid=get_uniqid();
            session('operate_uniqid',$uniqid);
            $data['uniqid']=$uniqid;

            $this->ajaxReturn($data);
        }else{
            $this->error('由于未知原因，删除出错，请稍后重试！');
        }
    }
    /*添加学习计划*/
    public function addPlan(){
        $this->initUniqid(GROUP_NAME.'/Plan/addPlanHandle');

        $uid=session('uid');

        $num=M('plan_clone')->where("uid='%d'",$uid)->count();

        $this->haveMore=$this->level['plan_num']>$num;

        //导入函数库！
        load('@/friend');
        $this->friends=merge_friend(get_friends_all($uid),$uid);
        $this->display();
    }
    /*添加学习计划的表单处理！*/
    public function addPlanHandle(){
        if(!IS_POST||!IS_AJAX)
            _404('页面不存在！');
        if(!checkFormUniqid(I('post.uniqid')))
            $this->error('表单验证码不正确，请刷新重试！');

        $uid=session('uid');
        $data=array(
            'status'    =>  false,
            'info'      =>  '',
        );

        /*校验设置开始时间*/
        $start=strtotime(I('post.start'));
        $end = strtotime(I('post.end'));
        //如果开始时间在今天之前，则改为今天
        if($start<time())
            $start=get_time(0);
        //如果结束时间在开始时间之前，则改为明天。
        if($end < $start)
            $end = $start + 86400;
        //echo $start_time;
        $name=pillStr(I('post.name',''),C('PLAN_MIN_NAME'),C('PLAN_MAX_NAME'));
        load('@/plan');
        $arr=merge_stage_mission($_POST);
        if(empty($arr)){
            $data['info']='至少一个阶段！';
            $data['stage']=1;
            $this->ajaxReturn($data);
        }
        $i=0;
        foreach($arr as $key=>$value){
            $i++;
            if(empty($value['mission'])){
                $data['info']='每个阶段至少一个任务';
                $data['index']=$i;
                $this->ajaxReturn($data);
            }
        }

        //上传封面文件或使用默认封面
        if(!empty($_FILES['face']['size'])) {
            load('@/account');
            $faces=save_user_image($uid);
            if($faces['status'])
                $_POST['face']=$faces['path'];
            else
                $this->error($faces['info']);
        }else{
            $_POST['face'] = C('PLAN_DEFAULT_FACE');
        }
        //清空验证码，防批量添加！
        clearUniqid();

        $plan=array(
            'uid'   => $uid,
            'open'  => I('post.open',1,'intval')%2,
            'mode'  => I('post.mode',1,'intval')%2,
            'name'  => $name,
            'create_time' => time(),
            'face'  => $_POST['face'],
        );
        if(!$pid=M('plan')->add($plan)){
            $data['info']='添加计划信息失败，可能服务器忙！';
            $this->ajaxReturn($data);
        }
        $i=9;
        foreach($arr as $key => $value){
            $data=array(
                'pid'   => $pid,
                'name'  => pillStr($value['name'],C('STAGE_MIN_NAME'),C('STAGE_MAX_NAME')),
                'info'  => pillStr($value['info'],0,C('STAGE_MAX_INFO')),
                'sort'  => $i++,
                'power' => abs(intval($value['power'])%10000),
            );
            if(!$sid=M('stage')->add($data)){
                $data['info']='添加阶段时出现未知错误！添加计划失败！';
                $this->ajaxReturn($data);
            }
            $j=9;
            foreach($value['mission'] as $k => $v) {
                $data = array(
                    'sid' => $sid,
                    'name' => pillStr($v['name'],C('MISSION_MIN_NAME'),C('MISSION_MAX_NAME')),
                    'info' => pillStr($v['info'],0,C('MISSION_MAX_INFO')),
                    'sort' => $j++,
                );
                if(!M('mission')->add($data)){
                    $data['info']='添加任务时出现未知错误！添加计划失败！';
                    $this->ajaxReturn($data);
                }
            }
        }
        $clone=array(
            'pid'   => $pid,
            'uid'   => session('uid'),
            'sid'   => 0,
            'start' => $start,
            'end'   => $end,
            'create_time' => time()
        );
        if(!$pcid=M('plan_clone')->add($clone)) {
            $data['info']='创建计划成功，但添加至我的计划失败！<a href="'.U(GROUP_NAME.'/Plan/share',array('pid'=>$pid)).'">点击跳转</a>至计划分享目录，您可以自行通过加入计划自行添加！';
            $this->ajaxReturn($data);
        }
        //发送监督请求
        load('@/supervision');
        send_supervision_requests($pcid,I('post.supervision'),$plan,$uid,$this->user['username'],false);
        $data['status']=true;
        $this->ajaxReturn($data);
    }
    public function verify(){
        import('imageCode',APP_PATH.C('APP_GROUP_PATH').'/'.GROUP_NAME.'/Class/');
        $img = new imageCode();
        $img->create('plan_verify',C('VERIFY_LEN'),C('VERIFY_TYPE'),100,30);
    }
    public function verifyCheck(){
        import('imageCode',APP_PATH.C('APP_GROUP_PATH').'/'.GROUP_NAME.'/Class/');
        $this->ajaxReturn(array('valid'=>imageCode::check('plan_verify',I('post.verify'))));
    }
}