<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/10
 * Time: 21:17
 */
class plan_cloneModel extends Model
{
    /**得出某一时间段的任务完成与未完成信息。          --  未使用。
     * @param $uid
     * @param array $time
     * @return mixed
     */
    public function get_active_mission_status($uid,$time=null){
        if(is_array($time))
            $time = $time?$time:array(time()-86400,time());
        else
            $time = array($time,time());//不是数组的话，默认传入的是开始时间。
        //SELECT SUM(IF(s.power>0,s.power,10)) FROM wq_plan_clone AS pc INNER JOIN wq_plan AS p ON pc.pid=p.id LEFT JOIN wq_stage AS s ON s.pid=p.id WHERE pc.id=43;
        $map = array(
            'pc.uid'           =>  array('eq',$uid),
            'pc.complete_time' =>  array(array('gt',$time[0]),array('exp','IS NULL'),'or'),
        );
        //获取当天所有需要的plan_clone;
        $total = $this->table($this->trueTableName.' AS pc')->join('INNER JOIN '.C('DB_PREFIX').'plan AS p ON pc.pid=p.id LEFT JOIN wq_stage AS s ON s.pid = pc.pid')->where($map)->field('pc.id,SUM(IF(s.power>0,s.power,10)) AS sum')->group('pc.id')->select();
        return $total;
    }
    public function get_plan_clone_by_id($pcid){
        //如果为空，直接返回。
        if(empty($pcid))
            return null;
        $data = array();
        if(is_array($pcid)){
            foreach($pcid as $k => $v)
                $data[] = $this->get_plan_clone_by_id($v);
            return $data;
        }
        $plan = $this
            ->table(C('DB_PREFIX').'plan_clone AS pc')//使用$this->trueName在某些非调试模式下会出错。
            ->join(C('DB_PREFIX').'plan AS p ON pc.pid = p.id')
            ->field('pc.id,pc.pid,pc.uid,pc.svid,pc.start,pc.complete_time,pc.create_time,pc.end,p.uid as puid,p.open,p.mode,p.name,p.create_time as pcreate_time,p.last_edit_time,p.face,p.praised,p.saw')
            ->where('pc.id=%d',$pcid)
            ->find();
        //没找到直接返回。
        if(empty($plan))
            return null;
        $plan['id'] = $pcid;//因为被覆盖了。
        //合并stage和mission
        $plandb = new planModel();
        $plan['stage'] = $plandb->get_stage_mission_by_pid($plan['pid'],$pcid);
        return $plan;
    }

    /**
     * 将所有详细信息得出来。
     * @param int $pcid
     * @param int $mode     //mode=1代表拖延,mode=0代表从剩余时间压缩。
     * @return mixed
     */
    public function detail($pcid,$mode=1,$more=false){
        //如果为空，直接返回。
        if(empty($pcid))
            return null;
        $data = array();
        //递归解析所有详情。
        if(is_array($pcid)){
            foreach($pcid as $key => $value){
                $data[] = $this->detail($value);
            }
            return $data;
        }
        //获得详细信息
        $plan_clone = $this->get_plan_clone_by_id($pcid);
        if(empty($plan_clone))
            return null;

        $complete_mission = 0;//已完成的任务数。
        $complete_total_time = 0;//完成的总时间。
        $complete_time_today = 0;//今天完成的时间。
        $total_mission = 0;
        $total_time = $plan_clone['end'] - $plan_clone['start'];

        $today_time_start = get_time(0);//今天凌晨的时间戳

        $total_power = 0;//总权值
        foreach($plan_clone['stage'] as $key=>$value){
            $plan_clone['stage'][$key]['power'] = $value['power']?$value['power']:10;
            $total_power += $plan_clone['stage'][$key]['power'];//权值累加。
        }

        //获取各种数据。
        foreach($plan_clone['stage'] as $key => $value){
            $plan_clone['stage'][$key]['avg_time'] = $total_time * $value['power']/$total_power;//计算这个总时间
            $mission_num = $plan_clone['stage'][$key]['mission_num'] = count($plan_clone['stage'][$key]['mission']);//本阶段任务数。
            foreach($value['mission'] as $k => $v){
                $total_mission++;
                $plan_clone['stage'][$key]['mission'][$k]['avg_time'] = $plan_clone['stage'][$key]['avg_time']/$mission_num;//用权值划分这个任务应该有的时间。
                if($v['time']) {
                    if($v['time'] > $today_time_start)
                        $complete_time_today += $plan_clone['stage'][$key]['mission'][$k]['avg_time'];//今天完成的时间
                    $complete_total_time += $plan_clone['stage'][$key]['mission'][$k]['avg_time'];//累加已完成时间。
                    $complete_mission++;//统计完成的总任务数。
                }
                //将换行换为回车。
                $plan_clone['stage'][$key]['mission'][$k]['info'] = str_replace("\n",'<br/>',$v['info']);
            }
        }

        $plan_clone['creator']=users_cache($plan_clone['puid']);
        $plan_clone['supervision']=users_cache($plan_clone['svid']);

        //拖延的时间，为负表示超前完成。
        $delay_time = $plan_clone['start']<time()?($plan_clone['complete_time']?$plan_clone['complete_time']:get_time(1))-$plan_clone['start']-$complete_total_time:0;
        //实时状态。
        $plan_clone['active_status'] = array(
            //任务区
            'complete'              =>  $plan_clone['complete_time']?true:false,//是否已完成
            'mission_complete'      =>  $complete_mission,//已完成任务。
            'complete_total_time'   =>  $complete_total_time,//完成的总时间
            'mission_total'         =>  $total_mission,//总任务
            'total_time'            =>  $total_time,//总时间
            'complete_rate'         =>  round($complete_mission*1.0/$total_mission,2),
            'today_complete'        =>  $plan_clone['complete_time'] or ($delay_time<0 && $delay_time<-86400) or  $complete_time_today > 86000,//今日是否完成任务，完全看今天的表现或者超时完成的表现。
            'delay_time'            =>  $delay_time,
        );
        if($more){//在需要更多信息的时候，才执行。
            $temp = array(
                'today_complete_num'    =>  M('mission_complete')->where('pcid=%d AND time>%d AND time<%d',$pcid,$today_time_start,time())->count(),//找出今天内完成了多少次该计划。
                //评论区
                'comment_num'           =>  M('plan_comment')->where('pid=%d',$plan_clone['pid'])->count(),
                'comment_star'          =>  M('plan_comment')->where('pid=%d AND rid=0',$plan_clone['pid'])->count(),
                'star'                  =>  floor(M('plan_comment')->where('pid=%d AND rid=0',$plan_clone['pid'])->avg('star')),//floor函数在这里比较科学。
            );
            $plan_clone['active_status'] = array_merge($plan_clone['active_status'],$temp);
        }
        return $plan_clone;
    }
}