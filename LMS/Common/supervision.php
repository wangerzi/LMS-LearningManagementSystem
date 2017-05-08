<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/20 0020
 * Time: 下午 12:37
 */
/**
 * 统计监督的所有信息
 * @param $uid
 * @return array
 */
function count_supervision($uid)
{
    $data=array(
        'all'   =>  M('plan_clone')->where("svid='%d'", $uid)->count(),
        'request'=> M('supervision_request')->where("rid='%d' AND status=0",$uid)->count(),
        'waiting'=>  M('supervision_log')
            ->table(C('DB_PREFIX').'supervision_log AS sv')
            ->join(C('DB_PREFIX').'plan_clone AS pc ON sv.pcid=pc.id AND sv.status<>1')
            ->where("pc.svid='%d'",$uid)->count(),
    );
    return $data;
}

/**
 * 添加批量监督请求，返回发起请求的个数
 * @param $pcid
 * @param $plan "plan的详情"
 * @param $checkExist "是否检测已存在"
 * @param null $uid
 * @return int
 */
function send_supervision_requests($pcid,$friends,$plan,$uid=null,$username=null,$checkExist=true){
    if(is_null($uid))
        $uid=session('uid');
    if(is_null($username))
        $username=cookie('username');
    $db_sv_r=M('supervision_request');
    $i=0;
    //邀请好友!
    load('@/friend');
    load('@/message');
    foreach ($friends as $key=>$value){
        $rid=intval($value);
        $friend_info=get_friend($uid,$rid);
        if(empty($friend_info))
            continue;
        $arr=array(
            'fid'   =>  $uid,
            'rid'   =>  $rid,
            'pcid'  =>  $pcid,
        );
        //检查是否存在
        if($checkExist && !empty($db_sv_r->where($arr)->find()))
            continue;
        $arr['time']=time();
        $arr['status']=0;
        //添加数据
        $db_sv_r->add($arr);
        $i++;
        //清理对应用户的缓存
        clear_cache('user/'.$rid.'/Index/Supervision/request/',true);
        //发送信息
        $content=$username.'邀请你监督他（她）的学习计划 《'.$plan['name'].'》 ，'."<a href='".U(GROUP_NAME.'/Supervision/request','',true,false,true)."'>前往</a>看看吧！";
        sendMessage($uid,$rid,'系统邮件',$content,get_email($rid));
    }
    return $i;
}

/**
 * 发送一个监督申请！
 * @param $pcid
 * @param $fid
 * @param $rid
 * @return array
 */
function send_sv_request($pcid,$fid,$rid){
    $arr=array(
        'pcid'  =>  $pcid,
        'fid'   =>  $fid,
        'rid'   =>  $rid,
        'status'=>  0,
        'time'  =>  time(),
    );
    return M('supervision_request')->add($arr);
}

/**
 * 合并supervision_request表中直接取出来的数据。。
 * @param $arr
 * @return array
 */
function merge_sv_request_plan($arr){
    $data=array();
    $db_plan=M('plan');
    $db_pc=M('plan_clone');
    $db_user=M('user');
    //获取用户信息，计划信息等
    foreach($arr as $key=>$value){
        $value['user']=$db_user->field('id,username,face')->find($value['fid']);
        $value['plan_clone']=$db_pc->field('id,pid,svid')->find($value['pcid']);
        $value['plan']=$db_plan->field('id,face,name')->find($value['plan_clone']['pid']);
        $data[]=$value;
    }
    return  $data;
}

/**
 * 合并supervision_log表中直接取出来的数据。。
 * @param $arr
 * @return array
 */
function merge_sv_log_plan($arr){
    $data=array();
    $db_plan=M('plan');
    $db_pc=M('plan_clone');
    //获取用户信息，计划信息等
    foreach($arr as $key=>$value){
        $value['plan_clone']=$db_pc->field('id,pid,svid')->find($value['pcid']);
        $value['plan']=$db_plan->field('id,face,name')->find($value['plan_clone']['pid']);
        $data[]=$value;
    }
    return  $data;
}