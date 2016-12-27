<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/26 0026
 * Time: 下午 2:24
 */
/**
 * 处理提交上来的计划，返回计划的name,info，包括的任务信息
 * @param $arr
 */
function merge_stage_mission($arr){
    $stage=array();
    foreach($arr as $key => $value){
        $temp=explode('&',$key);
        if(count($temp)==2){
            if($temp[0]=='stage'){
                /*阶段的编号！*/
                $num=$temp[1];
                $stage[]=array(
                    'name'      => $value,
                    //通过编号获取stage_info&n的内容
                    'info'      => $arr['stage_info&'.$num],
                    //通过函数找到阶段编号对应任务的名字，描述，时间
                    'mission'   => get_source_mission($arr,$num),
                );
            }
        }
    }
    /*返回转义过的数组！*/
    return changeHtmlSpecialChars($stage);
}
/**
 * 根据stage_num从原数组中获取相应的任务（POST提交数据的时候用的上！）
 * @param $arr
 * @param $num
 */
function get_source_mission($arr,$num){
    $mission=array();
    foreach($arr['mission&'.$num] as $key => $value){
        $time=$arr['mission_time&'.$num][$key];
        if(empty($time)){
            $time=C('MISSION_DEFAULT_TIME');
        }else{
            //根据最大时间进行设置！
            $time=intval($time)%C('MISSION_MAX_TIME');
        }
        $mission[]=array(
            'name'  => $value,
            'info'  => $arr['mission_info&'.$num][$key],
            'time'  => $time,
        );
    }
    return $mission;
}

/**
 * 返回处理过的阶段数组中任务总时间。
 * @param $arr
 * @return int
 */
function get_mission_total_time($arr){
    $total=0;
    foreach($arr as $key => $value){
        foreach($value['mission'] as $k => $v)
            $total+=$v['time'];
    }
    return $total;
}

/**
 * 统计完成的任务数！
 * @param $pcid
 * @return mixed
 */
function count_complete_mission($pcid,$uid=null){
    $uid=empty($uid)?session('uid'):$uid;
    $complete = M('mission_complete')->where("uid='%d' AND pcid='%d'",$uid,$pcid)->count();
    //p($complete);
    //p(M('mission_complete')->getLastSql());
    return $complete;
}

/**
 * 统计任务总数
 * @param $pid
 * @return int
 */
function count_total_mission($pid){
    $total=0;
    $stage=M('stage')->where("pid='%d'",$pid)->select();
    foreach($stage as $k => $v){
        $total+=M('mission')->where("sid='%d'",$v['id'])->count();
    }
    return $total;
}

/**
 * 对一个用用户ID取出来的plan_clone数据进行重组，获取计划，能否编辑等信息，由于不在控制器里，所以，生成编辑地址的时候需要url_uniqid防止跨站脚本攻击
 * @param $arr
 * @param $config_open  "是否保存用户数组"
 * @param $thumb_prex   "封面的缩略图的大小"
 * @param $followPlan   "是否跟随plan_clone，其实暂时只影响是否查找监督人信息"
 * @return array
 */
function merge_plan($arr,$thumb_prex='s_',$uid=null,$followPlan=false){
    $uid=empty($uid)?session('uid'):$uid;
    $db_comment=M('plan_comment');
    $db_pc=M('plan_clone');
    $db_plan=M('plan');
    //存放重组后的数据
    $data=array();
    foreach($arr as $key => $value){
        //判断是否开始
        if($value['start_time']<time())
            $value['start']=true;
        else
            $value['start']=false;
        //从数据库中找出计划！
        $value['plan']=$db_plan->find($value['pid']);
        if(!$value['plan'])
            continue;
        //对计划的封面进行解析
        $value['plan']['face']=get_thumb_file($value['plan']['face'],$thumb_prex);
        //计算任务完成量和完成比例，这里直接用的plan_clone中的uid
        $complete=count_complete_mission($value['id'],$value['uid']);
        $total=count_total_mission($value['plan']['id']);
        $value['mission_complete']=$complete;
        $value['mission_total']=$total;
        //避免除数是0
        if(!$total)
            $total++;
        $value['mission_complete_rate']=number_format(($complete/$total)*100,2);
        //如果有监督人，则找出监督人，在跟随模式（监督者信息）中，直接找用自身信息
        if(empty($value['svid']))
            $value['supervision']=array('username'=>'无','id'=>0);
        else {
            if($followPlan){
                $value['supervision']=array(
                    'id'    =>  session('uid'),
                    'username'=>cookie('username'),
                );
            }else {
                $value['supervision'] = M('user')->field('id', 'username')->find($value['svid']);
            }
            if (empty($value['supervision'])){
                $value['supervision']=array(
                    'id'    =>  0,
                    'username'=>'无',
                );
            }
        }
        //这里是为了处理某些情况下，点击完成任务后没有自动完成的情况。
        if(empty($value['complete_time'])&&$complete>=$total){
            $plan_clone['id']=$value['id'];
            $plan_clone['complete_time']=$value['complete_time']=time();
            $db_plan->save($plan_clone);
        }
        //如果已经完成，则将完成时间转换出来！
        if(empty($value['complete_time'])){
            //处理某些奇葩情况，任务未完成，却显示完成
            if($total!=$complete){
                $plan_clone['id']=$value['id'];
                $plan_clone['complete_time']=$value['complete_time']=null;
                M('plan_clone')->save($plan_clone);
            }
            $value['complete_time']=false;
        }
        //统计评论数：
        $value['plan']['comment']=$db_comment->where("pid='%d'",$value['plan']['id'])->count();
        //统计星星数，由于rid>0的时候，不能评星，所以跳过
        $value['plan']['star']=intval($db_comment->where("pid='%d' AND rid = 0",$value['plan']['id'])->avg('star'));
        //统计评价星星的人数
        $value['plan']['comment_star']=intval($db_comment->where("pid='%d' AND rid = 0",$value['plan']['id'])->count());
        $data[]=$value;
    }
    return $data;
}

/**
 * 传入经过merge_plan()处理过的用户计划数组，或者直接从plan中取得的数据
 * @param $arr
 * @param $uid
 * @param $followPlan   “用户配置是否跟着plan_clone中的uid走--用于监督者页面，需要传入经过merge_plan处理过的从plan_clone取出的数据”
 * @return array
 */
function merge_plan_mission($arr,$uid=null,$followPlan=false){
    $uid=empty($uid)?session('uid'):$uid;
    $data=array();

    $db_uc=M('user_config');
    $db_user=M('user');
    $db_mc=M('mission_complete');
    $db_pena=M('penalize');
    if(!$followPlan) {
        $user_config = $db_uc->where("uid='%d'", $uid)->limit(1)->select();
        $user_config = $user_config[0];
        $data['config'] = $user_config;
    }


    //许多的plan_clone，里边都有一个plan
    foreach($arr as $key => $value){

        //用于监督者那里对被监督者的计划进行处理的情况，所以，用户配置和uid均跟随plan_clone
        if($followPlan){
            $user_config=$db_uc->where("uid='%d'",$value['uid'])->find();
            $uid=$value['uid'];
            //是否已经鞭笞过！
            $map=array(
                'pcid'  =>  array('eq',$value['id']),
                'time'  =>  array('gt',get_time(0)),
            );
            $penalize=$db_pena->where($map)->find();
            if(empty($penalize)){
                $value['penalized']=false;
            }else{
                $value['penalized']=true;
            }
        }

        /*-----这里是为了在计划详情里边用的，在列表里边用不到--------*/
        //学习时间！
        if($value['start']){
            $start=$value['start_time'];
            //这里之所以要分一下类，是因为新创建的计划会出现空跑时间的状况！
            if($value['start_time']<$value['create_time'])
                $start=$value['create_time'];
            if($value['complete_time']){
                $value['study_time']=number_format(($value['complete_time']-$start)/86400.0,2);
            }else{
                $value['study_time']=number_format((time()-$start)/86400.0,2);
            }
        }
        else
            $value['study_time']='0';
        /*-----/这里是为了在计划详情里边用的，在列表里边用不到--------*/

        //取出完成情况数据.
        $temp=$db_mc->where("uid='%d' AND pcid='%d'",$uid,$value['id'])->field('mid,time')->select();
        $stage=get_stage($value['plan']['id']);

        //p($temp);

        //将stage进行重组
        foreach($stage as $k => $val){
            //将stage里边的mission进行重组
            foreach($val['mission'] as $name => $v){
                $v['complete']=false;
                //将mission与取出来的信息进行比较！
                foreach($temp as $item){
                    if($item['mid']==$v['id']){
                        $v['complete']=$item['time'];
                        break;
                    }
                }
                $val['mission'][$name]=$v;
            }
            $stage[$k]=$val;
        }

        $value['plan']['stage']=$stage;
        //已完成的总时间(s)
        $complete_time=0;
        $today_complete_time=0;
        $today=get_time(0);
        foreach($value['plan']['stage'] as $val)
            foreach($val['mission'] as $k => $v) {
                $complete_time += $v['complete'] ? $v['time'] * 3600 : 0;
                //今天完成的任务时间
                if($v['complete']>$today)
                    $today_complete_time += $v['time']*3600;
            }
        $day=ceil((time()-$value['start_time'])/86400);
        $need_time=$user_config['stu_time']*3600*$day;
        //已经完成的总时间
        $value['already_complete_time']=$complete_time;
        //今日已完成时间
        $value['today_complete_time']=$today_complete_time;
        //需要完成的时间
        $value['need_time']=$need_time;

        $value['delay']=($need_time-$complete_time)/3600;
        //预计结束时间
        //(总时间(h)+延迟的时间(h))/每天学习的时间=需要学习的天数 秒数=天数*86400
        $value['end_time']=$value['start_time']+(($value['plan']['total']+$value['delay'])/$user_config['study_time'])*86400;

        //今日是否完成
        if($today_complete_time >= $user_config['stu_time']*3600)
            $value['today_complete']=true;
        else
            $value['today_complete']=false;


        //创建者信息，只在需要的时候出现就好了
        if(!$followPlan)
            $value['creator']=$db_user->field(array('id','username'))->find($value['plan']['uid']);

        $data[]=$value;
    }
    return $data;
}
function get_stage($pid){
    $data=array();
    $arr=M('stage')->where("pid='%d'",$pid)->order('sort ASC,id ASC')->select();
    //p(M('stage')->getLastSql());
    foreach($arr as $key => $value){
        $value['mission']=get_mission($value['id']);
        $data[]=$value;
    }
    return $data;
}
function get_mission($sid){
    $data=array();
    $arr=M('mission')->where("sid='%d'",$sid)->order('sort ASC,id ASC')->select();
    /*foreach($arr as $key => $value){
        $tmp=M('mission_complete')->field('time')->where("pcid='%d' AND mid='%d'",$pcid,$value['id'])->limit(1)->select();
        if(empty($tmp))
            $value['complete']=false;
        else{
            $value['complete']=$tmp[0]['time'];
        }
        $data[]=$value;
    }*/
    return $arr;
}
/**
 * 这个方法用于将所有(或未完成)的计划和任务结合到一起
 * @param $uid
 * @param $all
 * @return array
 */
function get_plan_all($uid,$all=true){
    $map=array(
        'uid'   =>  array('eq',$uid),
    );
    //是否是全部计划
    if(!$all)
        $map['complete']=array('exp','IS NOT null');
    //先创建的，先开始的，先完成的
    $plan=merge_plan(M('plan_clone')->where($map)->order('create_time desc,start_time desc,complete_time desc')->select());
    return merge_plan_mission($plan);
}

/**
 * 筛选出今日完成/未完成的计划
 * @param $arr
 * @param bool $opposite
 * @return array
 */
function filterTodayCompletePlan($arr,$opposite=false){
    $config=$arr['config'];
    unset($arr['config']);//这是为了防止config出现在列表中遍历。
    $data=array();
    foreach($arr as $key => $value){
        //未开始的直接返回
        if(time()-$arr['start_time']<0)
            continue;
        //未完成的，如果任务已经完成，则不算。。
        if($opposite&&$value['complete_time'])
            continue;

        //将完成的总时间与理应完成的时间作比较，并判断是否反向
        if($opposite){
            if(!$value['today_complete'])
                $data[]=$value;
        }else{
            if($value['today_complete'])
                $data[]=$value;
        }
        //p('complete_time:'.$complete_time.'day:'.$day.'need_time:'.$need_time.'stu_time:'.$config['stu_time']);
    }
    return $data;
}
function countTodayPlan($arr){
    $config=$arr['config'];
    unset($arr['config']);//这是为了防止config出现在列表中遍历。
    $data=array(
        'complete'=>0,
        'not_complete'  =>  0,
        'continue'  =>  0,
        'end'   =>  0,
        'all'   =>  count($arr),
    );
    foreach($arr as $key => $value){
        if($value['complete_time'])//完成的
            $data['end']++;
        elseif(time()-$value['start_time']>0) {//开始的
            $data['continue']++;
        }
        //今日已完成，今日未完成
        if($value['today_complete'])
            $data['complete']++;
        else
            if(!$value['complete_time'])//已完成的不计入
                $data['not_complete']++;
    }
    return $data;
}

/**
 * 筛选继续中的还是已经结束了的。
 * @param $arr
 * @param bool $opposite
 * @return array $data
 */
function filterContinuePlan($arr,$opposite=false){
    $data=array();
    unset($arr['config']);//这是为了防止config出现在列表中遍历。
    if(!$opposite){//开始。。
        foreach($arr as $key => $value) {
            if (!$value['complete_time'] && $value['start_time'] < time()) {
                $data[] = $value;
            }
        }
    }else{//结束
        foreach($arr as $key => $value) {
            if ($value['complete_time']) {
                $data[] = $value;
            }
        }
    }
    return $data;
}

/**
 * 过滤任务信息，顺便加上sid
 * @param $arr
 * @return array
 */
function filterMissions($arr,$sid){
    $data=array();
    foreach($arr as $key=>$value){
        $value['sid']=$sid;
        $value['name']=pillStr(htmlspecialchars($value['name']),C('MISSION_MIN_NAME'),C('MISSION_MAX_NAME'));
        $value['info']=pillStr(htmlspecialchars($value['info']),0,C('MISSION_MAX_INFO'));
        $value['sort']=abs(intval($value['sort']));
        $value['time']=intval($value['time'])%49;
        $data[]=$value;
    }
    return $data;
}