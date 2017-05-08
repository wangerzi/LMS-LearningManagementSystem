<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/21 0021
 * Time: 下午 12:58
 */
class IndexAction extends CommonAction
{
    public function index(){
        $uid=session('uid');

        $plan_num=M('plan_clone')->where('uid=%d',$uid)->field('COUNT(IF(start<'.time().',id,NULL)) as started,COUNT(IF(complete_time IS NOT NULL,id,NULL)) as completed')->find();

        load('@/supervision');
        $arr = count_supervision($uid);
        $plan_num['sv_not_deal'] = $arr['waiting'];
        $this->plan_num=$plan_num;

        $this->days=7;//数据的个数。

        $this->day_active_path=U(GROUP_NAME.'/Index/day_active');
        $this->display();
    }
    public function day_mission_plan(){
        if(!IS_AJAX)
            _404('页面不存在');
        $uid=session('uid');

        //天数。
        $days = 7;

        //今天00:00的时间戳
        $db_pc=D('plan_clone');
        $db_p = D('plan');

        $map = array(
            'uid'           =>  array('eq',$uid),
            'complete_time' =>  array(array('gt',get_time(-$days+1)),array('exp','IS NULL'),'or'),
        );
        //$plan = D('plan_clone')->table(C('DB_PREFIX').'plan_clone AS pc')->join(C('DB_PREFIX').'plan AS p ON p.id=pc.pid')->where($map)->field('pc.id,pc.pid')->select();
        //获取即将出场的plan_clone信息，并进行合并。
        $plan = $db_pc->where($map)->field('id,pid,complete_time,start,end')->select();
        foreach($plan as $key => $value){
            $plan[$key]['stage'] = $db_p->get_stage_mission_by_pid($value['pid'],$value['id']);
            $total_power = 0;
            foreach($plan[$key]['stage'] as $k => $val){
                $plan[$key]['stage'][$k]['power'] = $val['power']?$val['power']:10;
                $total_power += $val['power']?$val['power']:10;
            }
            //分成两个循环，也是无奈
            foreach($plan[$key]['stage'] as $k => $val){
                //计算分配的时间。
                $avg_time = ($plan[$key]['end']-$plan[$key]['start'])*$val['power']/$total_power;
                $plan[$key]['stage'][$k]['avg_time'] = $avg_time;
                $avg_time_m = $avg_time/count($val['mission']);//任务数。
                $plan[$key]['stage'][$k]['avg_time_m'] = $avg_time_m;//效率优化。
                /*foreach($val['mission'] as $k2=>$v){
                    $plan[$key]['stage'][$k]['mission'][$k2]['avg_time'] = $avg_time_m;
                }*/
            }
            $plan[$key]['total_power'] = $total_power;
            $plan[$key]['active_schedule'] = $plan[$key]['start'];//实时进度初始化。
        }
        //$this->ajaxReturn($plan);

        //初始化数组。
        $arr=array(
            'status'    =>  false,
        );
        //任务的数量统计
        $arr['mission'][0]['data']=array();
        $arr['mission'][1]['data']=array();
        //$arr['mission'][2]['data']=array();

        $arr['mission'][0]['name']='未完成任务';
        $arr['mission'][1]['name']='已完成任务';
        //$arr['mission'][2]['name']='总任务';

        //计划的数量统计
        $arr['plan'][0]['data']=array();
        $arr['plan'][1]['data']=array();
        $arr['plan'][2]['data']=array();

        $arr['plan'][0]['name']='未完成计划';
        $arr['plan'][1]['name']='已完成计划';
        $arr['plan'][2]['name']='已延期计划';
        for($i=$days-1;$i>=0;$i--){
            //获取时间
            $time_i = get_time(-$i);
            $time_i_1 = get_time(-$i+1);

            $complete_num = 0;
            $not_complete_num = 0;
            $total_num = 0;

            $complete_plan = 0;
            $not_complete_plan = 0;
            $delay_plan = 0;

            //统计当天完成时间和任务数。
            foreach($plan as $key=>$value){
                //注意大于等于，否则数据不对。
                if((!empty($value['complete_time']) && $value['complete_time'] < $time_i) || $value['start']>=$time_i_1)//如果任务在第i天之前已结束，或者在第i天时还没开始，忽略。
                    continue;
                //这里的complete_time需要在里边。
                $complete_time = 0;
                foreach($value['stage'] as $k=>$val){
                    foreach($val['mission'] as $k2=>$v){
                        $total_num++;
                        if($v['time']>$time_i && $v['time']<$time_i_1){
                            $complete_num++;
                            $complete_time += $val['avg_time_m'];
                        }
                    }
                }
                $plan[$key]['active_schedule'] += $complete_time;//实时进度。
                if($plan[$key]['active_schedule'] < $time_i_1)
                    $delay_plan++;
                //如果已完成时间没有一天，并且计划延期，没有提前完成，则在数组里找未完成任务，直到达到一天为止。
                if($plan[$key]['active_schedule'] < $time_i_1 && $complete_time < 86400){
                    $not_complete_plan++;//当天该计划任务未完成
                    foreach($value['stage'] as $k=>$val){
                        foreach($val['mission'] as $k2=>$v){
                            if(!($v['time']>$time_i && $v['time']<$time_i_1)){
                                $complete_time += $val['avg_time_m'];
                                $not_complete_num++;
                                if($complete_time > 86400)
                                    break(2);
                            }
                        }
                    }
                }else{
                    $complete_plan++;
                }
            }
            $arr['mission'][0]['data'][]=array($time_i*1000,$not_complete_num);
            $arr['mission'][1]['data'][]=array($time_i*1000,$complete_num);
            //$arr['mission'][2]['data'][]=array($time_i*1000,$total_num);

            $arr['plan'][0]['data'][]=array($time_i*1000,$not_complete_plan);
            $arr['plan'][1]['data'][]=array($time_i*1000,$complete_plan);
            $arr['plan'][2]['data'][]=array($time_i*1000,$delay_plan);
        }

        $arr['status']=true;

        $this->ajaxReturn($arr);
    }
    public function day_supervision(){
        if(!IS_AJAX)
            _404('页面不存在');
        $uid=intval(session('uid'));

        //天数。
        $days = 7;

        $db=M('supervision_log');

        //初始化数组。
        $arr=array(
            'status'    =>  false,
        );
        //任务的数量统计
        $arr['supervision'][0]['data']=array();
        $arr['supervision'][1]['data']=array();

        $arr['supervision'][0]['name']='已处理监督';
        $arr['supervision'][1]['name']='未处理监督';
        for($i=$days-1;$i>=0;$i--){
            //获取时间
            $time_i = get_time(-$i);
            $time_i_1 = get_time(-$i+1);

            //通过complete_time和resquest_time获取supervision_log统计数据。
            $count=$db
                ->table(C('DB_PREFIX').'supervision_log AS sv')
                ->join('INNER JOIN '.C('DB_PREFIX').'plan_clone AS pc ON sv.pcid=pc.id AND pc.svid='.$uid)
                //由于if的条件中sv.id能得出正确的统计结果。
                ->field('COUNT(IF(sv.reply_time > '.$time_i.' AND sv.reply_time < '.$time_i_1.',sv.id,NULL)) AS complete, COUNT(IF(sv.complete_time < '.$time_i_1.' AND (sv.reply_time IS NULL OR sv.reply_time>'.$time_i_1.'),sv.id,NULL)) AS not_complete')
                ->find();
            $arr['supervision'][0]['data'][]=array($time_i*1000,intval($count['complete']));
            $arr['supervision'][1]['data'][]=array($time_i*1000,intval($count['not_complete']));
        }
        $arr['status'] = true;
        $this->ajaxReturn($arr);
    }
    /*签到*/
    public function checkout(){
        if(!IS_AJAX)
            _404('页面不存在！');
        $uid = session('uid');
        $data=array(
            'status'    => false,
            'text'      => '',
        );
        if(M('checkout')->where("uid='%d' AND time > '%d'",session('uid'),get_time(0))->count()>0){
            $data['text']='您已经签过到了，不能再签了哦！';
            $this->ajaxReturn($data);
        }
        $arr=array(
            'uid'   => session('uid'),
            'time'  => time(),
        );
        //是否是连续签到，筛选有没有昨天之后的签到记录！
        $is_continue=count(M('checkout')->where("uid='%d' AND time > '%d'",session('uid'),get_time(-1))->limit(1)->select())>0;
        if(M('checkout')->add($arr)){
            $check=$this->user['checkout'];
            $user=M('user');
            if($is_continue){
                $exp=checkoutExp(++$check);
            }else{
                $exp=checkoutExp(1);
                $check=1;
            }
            $user->save(array('id' => session('uid'),'checkout' => $check,'exp' => $this->user['exp']+$exp));

            clear_cache('user/'.$uid.'/');//清除所有缓存，否则由于缓存容易出现错误。

            $data['text']="签到成功，您已连续签到 <b>{$check}</b> 天，获得 <b>{$exp}</b> 点经验。<br/>明天再来可获得<b class='text-warning'>".checkoutExp($check+1).'</b>经验哦！';
            $data['status']=true;
            $this->ajaxReturn($data);
        }else{
            $data['text']='签到失败，请刷新重试！';
            $this->ajaxReturn($data);
        }
    }

    /**
     * 用户的签到信息
     */
    function checkoutInit(){
        $uid = session('uid');
        $checkout=array(
            'total' => M('checkout')->where("uid='%d' AND time > '%d'",$uid,get_time(0))->count(),
            'serialize' => $this->user['checkout'],
        );
        //查找匹配的等级！
        $level=M('level')->where("need < '%d'",$this->user['exp'])->order('level DESC')->limit(1)->select();
        if(empty($level)){//如果没找到，那就默认一个。
            $level=array(
                'level' => 0,
                'need'  => 0,
                'plan_num'=> 4,
                'exp'   => 2,
            );
        }else{
            $level=$level[0];
        }
        //找出离用户经验最近的下一等级
        $tmp=M('level')->where('need > %d',$this->user['exp'])->order('need ASC')->limit(1)->select();
        if(empty($tmp)){
            //这种情况在满级的时候能看到！
            $level['next']=0;
            $level['next_need']=$this->user['exp'];
        }else{
            $level['next']=$tmp[0]['need']-$this->user['exp'];
            $level['next_need']=$tmp[0]['need'];
        }
        if($checkout['total'] < 1){
            $str =  '<p class="pull-left" style="line-height:2.42857143;margin-right:10px;">今天还没签到哦！戳我签到 ( ﹁ ﹁ ) ~→</p>
                    <button type="submit" id="checkout" class="btn btn-success">一键签到</button>';
        }else{
            $str = '<p>
                        当前等级：<b class="text-danger">Lv. '.$level['level'].'</b>，距离 <b class="text-primary">Lv. '.($level['level']+1).'</b>还差 <b>'.$level['next'].'</b>点经验
                    </p>
                    <div class="progress" style="height:10px;margin-bottom:5px;">
                        <div alt="'.$this->user['exp']/$level['next_need'].'" id="exp-bar" class="progress-bar progress-bar-success progress-bar-striped active">
                            <span style="line-height:10px;">'.$this->user['exp'].'/'.$level['next_need'].'</span>
                        </div>
                    </div>';
        }
        $this->success($str);
    }

    /**
     * 统计
     */
    function popNum(){
        $uid = session('uid');
        //对需要提示的数字进行统计
        $arr_num=array(
            'message'   =>  M('message')->where("rid='%d' AND status=0",$uid)->count(),
            'friend'    =>  M('friend_request')->where("rid='%d'",$uid)->count(),
            //监控这里只需要统计未处理的申请就好了！
            'supervision'=> M('supervision_request')->where("rid='%d' AND status=0",$uid)->count()+M('supervision_log')->table(C('DB_PREFIX').'supervision_log AS sv')->join(C('DB_PREFIX').'plan_clone AS pc ON sv.pcid=pc.id AND sv.status<>1')->where("pc.svid='%d'",$uid)->count(),
            //'plan_all'  =>  M('plan_clone')->where("uid='%d'",$uid)->count(),加上个徽标之后空间不够了。。。
        );
        $data = array(
            'status'    =>  true,
            'info'      =>  '',
            'num'       =>  $arr_num,
        );
        $this->ajaxReturn($data);
    }
    /*生成等级需求的代码！*/
    /*public function test(){
        $exp=2;
        $need=0;
        $plan_num=4;
        for($i=0;$i<30;$i++) {
            $data=array(
                'level' => $i,
                'plan_num'=> $plan_num,
                'need'  => $need,
                'complete_exp'   =>$exp
            );
            M('level')->add($data);
            $plan_num=6+$i*2;
            $exp+=2;
            $need+=15*($i*2+2);
        }
    }*/
}