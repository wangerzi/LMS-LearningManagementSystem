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
            if($temp[0]=='stage'){//筛选出是name=stage&n的
                /*阶段的编号！*/
                $num=$temp[1];
                $stage[]=array(
                    'name'      =>  $value,
                    //通过编号获取stage_info&n的内容
                    'info'      =>  $arr['stage_info&'.$num],
                    'power'     =>  $arr['stage_power&'.$num],
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
        $mission[]=array(
            'name'  => $value,
            'info'  => $arr['mission_info&'.$num][$key],
        );
    }
    return $mission;
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
    //注意是inner join
    $total = M('mission')->table(C('DB_PREFIX').'mission AS m')->join('INNER JOIN '.C('DB_PREFIX').'stage AS s ON s.id=m.sid AND s.pid='.intval($pid))->count();
    //p(M('mission')->getLastSql());
    return $total;
}
function plan_complete($pcid){
    $tmp = array(
        'id'            =>  $pcid,
        'complete_time' =>  0,
    );
    $db = M('plan_clone');
    $arr = $db->field('pid,uid')->find($pcid);
    //记得这个uid，如果不设置uid为plan_clone中的uid的话，以监督者进入详情不会更新完成情况。
    if(count_total_mission($arr['pid'])<=count_complete_mission($pcid,$arr['uid'])){
        $tmp['complete_time'] = M('mission_complete')->where('pcid=%d',$pcid)->max('time');
        $db->save($tmp);
        return true;//任务已完成。
    }else{
        $tmp['complete_time'] = null;
        $db->save($tmp);
        return false;
    }
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
        $data[]=$value;
    }
    return $data;
}
function clearPlanCache($uid,$pid,$pcid = null){
    /*//清理缓存文件
    clear_cache('user/'.$uid.'/Index/Plan/index/',true);//列表目录缓存删除
    //某些时候没有pid
    if(!empty($pcid)) {
        clear_cache('user/' . $uid . '/Index/Plan/detail/' . $pcid . '/', false);//详情缓存清除。
        clear_cache('user/' . $uid . '/Index/Plan/log/' . $pcid , false);//日志目录缓存
        clear_cache('user/' . $uid . '/Index/Plan/edit/' . $pcid . '/', false);//编辑目录缓存
    }*/
    //将下面所有的都清除。
    clear_cache('user/'.$uid.'/Index/Plan/',true);
    if(!empty($pcid))
        clear_cache('user/Index_Plan_share/'.$pid.'/',true);//分享目录缓存
}