<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/24 0024
 * Time: 下午 1:10
 */
class planModel extends Model
{
    public function get_plan_by_id($pid,$pcid=null){
        $plan = $this->find($pid);
        //合并stage和mission
        $plan['stage'] = $this->get_stage_mission_by_pid($pid,$pcid);
        return $plan;
    }
    public function get_stage_mission_by_pid($pid,$pcid=null){
        $db_m = M('mission');
        $pcid = intval($pcid);

        $map = array(
            'pid'   =>  array('eq',$pid),
        );
        $stage  =   M('stage')->where($map)->order('sort ASC , id ASC')->select();
        $map = array();
        foreach($stage as $key => $value){
            $map['sid'] = $value['id'];
            //如果有pcid的话，就将任务完成情况联立查询。
            if($pcid) {
                $stage[$key]['mission'] = $db_m
                    ->table(C('DB_PREFIX') . 'mission AS m')
                    ->join(C('DB_PREFIX') . 'mission_complete AS mc ON mc.pcid=' . $pcid . ' AND mc.mid = m.id')
                    ->where('m.sid=%d', $value['id'])
                    ->field('m.id,m.name,m.info,m.sid,m.sort,mc.time')
                    ->order('sort ASC')
                    ->select();
            }
            else
                $stage[$key]['mission'] = $db_m->where($map)->select();
        }
        return $stage;
    }
}