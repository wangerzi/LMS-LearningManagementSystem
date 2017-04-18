<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/12/3 0003
 * Time: 下午 7:47
 */

/**
 * 不能直接以true,false描述的错误信息，就用这个类。
 * Class checkError
 */
class checkError{
    protected $valid = false;
    protected $message;
    public function getMessage(){
        return $this->message;
    }
    public function isValid(){
        return $this->valid;
    }
    public function __construct($valid,$message=''){
        $this->valid=$valid;
        $this->message=$message;
    }
}
/**
 * 检查字符串长度！
 * @param $str
 * @param int $min
 * @param int $max
 * @param string $name
 * @return bool|string
 */
function mb_check_stringLen($str,$min=3,$max=12,$name='名称'){
    $len=mb_strlen($str,'utf-8');
    if($len<$min||$len>$max)
        return new checkError(false, $name.'长度需要在'.$min.'到'.$max.'之间！');
    return new checkError(true);
}
function check_forbid_name($str){
    $arr=mb_split(',',C('FORBID_NAME'));
    return !in_array($str,$arr);
}

/**
 * 检查用户名是否重复
 * @param $name
 * @param int $uid
 * @return bool
 */
function checkRepeatUsername($name,$uid=0){
    $user=M('user')->field('id')->where("username='%s' AND id<>'%d'",$name,$uid)->limit(1)->select();
    //如果不是管理员，则会验证敏感用户名
    if(empty($user)&&(is_admin()||check_forbid_name($name))){
        //没有重复
       return true;
    }
    //重复了！
    return false;
}

/**
 * 检查是否符合邮箱。
 * @param $email
 * @return bool
 */
function checkEmail($email){
    if(preg_match('/^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(\.[a-zA-Z0-9_-])/',$email))
        return true;
    else
        return false;
}
function checkTime($time){
    if(preg_match('/^[0-9]{4}(\-|\/)[0-9]{1,2}(\\1)[0-9]{1,2}(|\s+[0-9]{1,2}(|:[0-9]{1,2}(|:[0-9]{1,2})))$/',$time))
        return true;
    else
        return false;
}