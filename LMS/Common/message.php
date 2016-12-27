<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/3 0003
 * Time: 下午 8:05
 */
/**
 * 单纯发送信息，根据$email的有无自动发邮件
 * @param $fid
 * @param $rid
 * @param $title
 * @param $content
 * @param null $email
 * @return mixed
 */
function sendMessage($fid,$rid,$title,$content,$email=null){
    if($fid==$rid){
        return false;
    }
    $db=M('message');
    $data=array(
        'fid'   =>  $fid,
        'rid'   =>  $rid,
        'title' =>  $title,
        'content'=> $content,
        'time'  => time(),
        'status'=> 0,
    );
    if(!empty($email)){
        load('@/email');
        addEmailTimeQueue($email,C('WEB_NAME'),C('WEB_NAME').' 您有一条新短消息',$data['content'],0);
    }
    return $db->add($data);
}

/**
 * 获取uid的email，并根据uid的配置返回null或其email，可直接用在senMessage中的$email中。
 * @param $uid
 * @return mixed
 */
function get_email($uid){
    $user_config=M('user_config')->field('rem_message')->where("uid='%d'",$uid)->find();
    if(!$user_config['rem_message'])
        return null;
    $user=M('user')->field('email')->find($uid);
    if(empty($user))
        return null;
    return $user['email'];
}
/**
 * 处理从message表中提取出来的数据，并在$data['user']字段存下对方的信息！
 * @param $arr
 * @return array
 */
function merge_message($arr){
    $data=array();
    $user=M('user');
    foreach($arr as $key => $value){
        $value['user']=$user->field(array('id','username','info','face'))->find($value['fid']);
        $data[]=$value;
    }
    return $data;
}