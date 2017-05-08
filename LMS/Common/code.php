<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/6 0006
 * Time: 上午 11:12
 */
/**
 * 生成一个随机码
 * @param int $len
 * @return string
 */
function createCode($len=4){
    return mb_substr(get_uniqid(),0,$len);
}

/**
 * 保存一个验证码，默认900s(15min)的有效时间，自动去重复
 * @param $uid
 * @param $code
 * @param $control
 * @param int $continue
 * @return mixed
 */
function saveCode($uid,$code,$control=null,$continue=900){
    //自动生成一个$control。
    if($control==null){
        $control=GROUP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME;
    }
    $db=M('code');
    $data=array(
        'uid'   =>  $uid,
        'code'  =>  $code,
        'continue'=>$continue,
        'for'   =>  $control,
        'time'  =>  time(),
    );
    $map=array(
        'uid'   =>  array('eq',$uid),
        'for'   =>  array('eq',$control),
    );
    $codeArr=$db->where($map)->limit(1)->select();
    if(!empty($codeArr)){
        $codeArr[0]['time']=time();
        $codeArr[0]['code']=$code;
        $codeArr[0]['continue']=$continue;
        $db->save($codeArr[0]);
        //p($db->getLastSql());
        //p($codeArr);
        return true;
    }else
        return $db->add($data);
}

/**
 * 检查code是否存在，可选择是否删除！
 * @param $uid
 * @param $code
 * @param null $control
 * @param bool $del
 * @return int
 */
function checkCode($uid,$code,$control=null,$del=true){
    //自动生成一个$for。
    if($control==null){
        $control=GROUP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME;
    }
    $db=M('code');

    $map=array(
        'uid'   =>  array('eq',$uid),
        'code'  =>  array('eq',$code),
        'for'   =>  array('eq',$control),
    );

    $code=$db->where($map)->limit(1)->select();
    //p($db->getLastSql());
    if(empty($code))
        return 1;//代表没有找到。
    else
        $code=$code[0];

    //只能使用一次
    if($del)
        $db->delete($code['id']);
    if(time()-$code['time']>$code['continue']){
        return 2;//代表超时！
    }else{
        return 0;//代表验证成功！
    }
}

/**
 * 发送邮箱验证码
 * @param $mail
 * @param $code
 * @param string $info
 */
function sendCode($mail,$code,$info='您在北京时间{__TIME__}，进行邮箱验证，本次请求的验证码为：'){
    load('@/email');
    $content=file_get_contents(C('MAIL_TPL').'code.html');
    $content=str_replace('{__INFO__}',$info,$content);
    $content=str_replace('{__CODE__}',$code,$content);
    $content=str_replace('{__TIME__}',date('Y-m-d H:i:s',time()),$content);
    addEmailTimeQueue($mail,C('WEB_NAME'),C('WEB_NAME').'--验证码邮件',$content,time());
}

/**
 * 检查是否存在验证码
 * @param $uid
 * @param $control
 * @return bool
 */
function issetCode($uid,$control){
    //自动生成一个$for。
    if($control==null){
        $control=GROUP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME;
    }
    $db=M('code');

    $map=array(
        'uid'   =>  array('eq',$uid),
        'for'   =>  array('eq',$control),
    );

    $code=$db->where($map)->limit(1)->select();
    //p($db->getLastSql());
    if(empty($code)||$code[0]['time']+$code[0]['continue']<time())
        return false;//代表没有找到。
    else
        return true;
}

/**
 * 通过$uid和$control删除
 * @param $uid
 * @param $control
 * @return bool
 */
function deleteCode($uid,$control){
    //自动生成一个$for。
    if($control==null){
        $control=GROUP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME;
    }
    $db=M('code');

    $map=array(
        'uid'   =>  array('eq',$uid),
        'for'   =>  array('eq',$control),
    );

    return $db->where($map)->delete();
}

/**
 * 清空$uid对应的所有邮箱码，慎用！
 * @param $uid
 * @return mixed
 */
function clearCode($uid){
    $db=M('code');

    $map=array(
        'uid'   =>  array('eq',$uid),
    );

    return $db->where($map)->delete();
}