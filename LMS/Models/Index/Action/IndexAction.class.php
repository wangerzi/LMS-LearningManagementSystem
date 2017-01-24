<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/21 0021
 * Time: 下午 12:58
 */
class IndexAction extends CommonAction
{
    /*展示向外的主页*/
    public function show_index(){
        $this->display();
    }
    public function index(){
        $uid=session('uid');
        //加载函数库！
        load('@/plan');

        $plan_all=get_plan_all($uid);
        $plan_num=countTodayPlan($plan_all);
        $this->plan_num=$plan_num;

        $this->day_active_path=U(GROUP_NAME.'/Index/day_active');
        $this->display();
    }
    public function day_active(){
        $uid=session('uid');
        //日活跃图的表
        load('@/account');
        $file=get_user_file('charts/day-active.php');
        if(!is_file($file)) {
            $arr=array(
                'refresh_time'  =>  0,
            );
            F('/charts/day-active', $arr, C('USER_BASE_PATH') . $uid);
        }

        //引入$file，不用缓存是因为缓存有时候会更新不及时造成重复向数据库请求
        $arr=include $file;

        //今天00:00的时间戳
        $today=get_time(0);
        $db=M('mission_complete');
        $db_pc=M('plan_clone');
        $config=M('user_config')->where("uid='%d'",$uid)->find();

        if($arr['fresh_time']<$today){
            $arr=array(
                'status'    =>  false,
            );//先置空
            //$arr['config']=$config;
            //完成任务的计算
            $arr['info'][0]['data']=array();
            $data=array();
            for($i=0;$i<7;$i++){
                //前i天的时间戳
                $time_i=get_time(-$i);
                $map=array(
                    'uid'   =>  array('eq',$uid),
                    //这里要注意between的用法，中间用,隔开
                    'time' =>  array('between',$time_i.','.get_time(-$i+1)),
                );
                $data[$i]=array(
                    $time_i*1000,
                    intval($db->where($map)->count()),

                );
            }
            $arr['info'][0]['data']=$data;
            //某些初次请求的，可能没有这个
            $arr['info'][0]['name']='已完成任务';


            //未完成任务的计算
            $arr['info'][1]['data']=array();
            $data=array();
            for($i=0;$i<7;$i++){
                //前i天的时间戳
                $time_i=get_time(-$i);
                //需要完成的任务数量大概是complete_time在前几天之前的，已经开始的任务数.
                $map=array(
                    'uid'   =>  array('eq',$uid),
                    //已经开始的
                    'start_time' =>  array('lt',$time_i),
                    //当时未完成或现在未完成的
                    'complete_time'  =>  array(array('exp','IS null'),array('gt',$time_i),'or'),
                );
                $need_num=$db_pc->where($map)->count();
                $need_num*=ceil($config['stu_time']/6);

                //$need_num=0;
                //需要完成减去已完成
                $not_complete=$need_num-intval($arr['info'][0]['data'][$i][1]);
                $not_complete=intval($not_complete>0?$not_complete:0);
                //$complete=0;
                //完成的任务数是mission_complete中
                $data[$i]=array(
                    $time_i*1000,
                    $not_complete,
                );
            }
            //p($db_pc->getLastSql());
            $arr['info'][1]['data']=$data;
            $arr['info'][1]['name']='未完成任务';
        }
        $arr['refresh_time']=time();
        $arr['status']=true;

        F('/charts/day-active',$arr,C('USER_BASE_PATH').$uid);

        //隐藏某些信息
        unset($arr['refresh_time']);
        $this->ajaxReturn($arr);
    }
    /*签到*/
    public function checkout(){
        if(!IS_AJAX)
            _404('页面不存在！');
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
            $data['text']="签到成功，您已连续签到 <b>{$check}</b> 天，获得 <b>{$exp}</b> 点经验。<br/>明天再来可获得<b class='text-warning'>".checkoutExp($check+1).'</b>经验哦！';
            $data['status']=true;
            $this->ajaxReturn($data);
        }else{
            $data['text']='签到失败，请刷新重试！';
            $this->ajaxReturn($data);
        }
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