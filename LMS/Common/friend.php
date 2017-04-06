<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/29 0029
 * Time: 上午 11:32
 */
/**
 * 处理从friend_request或friend表中提取出来的数据，并在$data['user']字段存下对方的信息！
 * @param $arr
 * @param $uid
 * @return array
 */
function merge_friend($arr,$uid){
    $data=array();
    $user=M('user');
    foreach($arr as $key => $value){
        if($value['fid']==$uid)
            $value['user']=$user->field(array('id','username','info','face'))->find($value['rid']);
        else
            $value['user']=$user->field(array('id','username','info','face'))->find($value['fid']);
        $data[]=$value;
    }
    return $data;
}
function get_friend_request($uid,$uid_2){
    $db=M('friend_request');
    $data=$db->where("(fid='%d' AND rid='%d') OR (fid='%d' AND rid='%d')",$uid,$uid_2,$uid_2,$uid)->limit(1)->select();
    return $data[0];
}
/**
 * 获取好友信息
 * @param $uid
 * @param $uid_2
 * @return mixed
 */
function get_friend($uid,$uid_2){
    $db=M('friend');
    $data=$db->where("(fid='%d' AND rid='%d') OR (fid='%d' AND rid='%d')",$uid,$uid_2,$uid_2,$uid)->limit(1)->select();
    return $data[0];
}
function get_user_friend_db($uid,$option=array(),$filed=array()){
    $map=array(
        'user.id' => array('neq',$uid),
        'fri.id'=> array('exp','is null'),
        'fri_req.id'=>array('exp','is null')
    );
    if(empty($filed)){
        $field=array('user.id','user.username','user.email','user.face','user.info');
    }
    //先合并传入的参数，这是为了先判断简单的条件。
    $map=array_merge($option,$map);
    return M('user user')->field($field)->join(C('DB_PREFIX')."friend fri ON (fri.fid={$uid} AND fri.rid=user.id) OR (fri.fid=user.id AND fri.rid=2)")->join(C('DB_PREFIX')."friend_request fri_req ON (fri_req.fid={$uid} AND fri_req.rid=user.id) OR (fri_req.fid=user.id AND fri_req.rid={$uid})")->where($map);
}
function get_friends_all($uid){
    $db=M('friend');
    return $db->where("fid='%d' OR rid='%d'",$uid,$uid)->limit(999)->select();
}
function get_friends_request_all($uid){
    $db=M('friend_request');
    return $db->where("fid='%d' OR rid='%d'",$uid,$uid)->limit(999)->select();
}
//从user表中取出的原生数据，通过此函数给数据加上一个status状态，0表示未加为好友，1表示申请中，2表示已成为好友，用于好友搜索
function merge_friend_status($uid,$users){
    //数据重组！
    $friends=get_friends_all($uid);
    $request=get_friends_request_all($uid);
    //p($friends);
    //p($request);

    $data=array();
    foreach($users as $key => $value){
        $value['status']=0;
        //如果处于申请中，则状态为2
        foreach($request as $k => $v){
            if(($v['fid']==$uid && $v['rid']==$value['id']) || ($v['fid']==$value['id'] &&$v['rid']==$uid)) {
                $value['status'] = 2;
                break;
            }
        }
        //如果是好友，则状态为1
        foreach($friends as $k => $v){
            if(($v['fid']==$uid && $v['rid']==$value['id']) || ($v['fid']==$value['id'] &&$v['rid']==$uid)) {
                $value['status'] = 1;
                break;
            }
        }
        $data[]=$value;
    }
    return $data;
}