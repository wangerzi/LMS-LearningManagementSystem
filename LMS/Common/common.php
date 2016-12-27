<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/9 0009
 * Time: 上午 10:04
 */
/***
 * 获取一个随机码。
 * @return string
 */
function get_uniqid(){
    return sha1(C('CSSF_STRING').time().'sdoi1io');
}

/**
 * 返回session口令
 * @return string
 */
function get_session_key(){
    return sha1(C('CSSF_STRING').$_SERVER['USER_AGENT'].session_id().'slidf');
}

/**
 * 用户登出
 */
function logout(){
    cookie('user_key',null);
    cookie('username',null);
    cookie('uid',null);
    session_unset();
    return ;
}

/**
 * 判断是否登录！
 * @return bool
 */
function is_login(){
    if(session('is_login'))
        return true;
    else
        return false;
}
function is_admin(){
    if(session(C('ADMIN_TAG')))
        return true;
    else
        return false;
}

/**
 * 计算密码
 * @param $uid
 * @param $pas
 * @return string
 */
function calc_password($pas){
    return sha1(C('CSSF_STRING').$pas.'sfi120i@');
}

/**
 * 计算用户key
 * @param $uid
 * @param $username
 * @return string
 */
function calc_user_key($uid,$username){
    return sha1(C('CSSF_STRING').$uid.$username.'sli1o29d');
}

/**
 * 打印出该数据。
 * @param $str
 */
function p($str){
    echo "<pre>";
    print_r($str);
    echo "</pre>";
}
function clearUniqid($control=null){
    $control=empty($control)?GROUP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME:$control;
    if(isset($_SESSION[$control.'_uniqid']))
        unset($_SESSION[$control.'_uniqid']);
}
/*初始化唯一标识码，在common控制器里有个相同的方法！*/
function initUniqid(&$obj,$control=null){
    $control=empty($control)?GROUP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME:$control;
    $uniqid=get_uniqid();
    session($control.'_uniqid',$uniqid);


    $obj->url_uniqid=$uniqid;
    $obj->uniqid=$uniqid;

    //快捷生成表单的字符串。
    $obj->__UNIQID__="<input type='hidden' name='uniqid' value='{$obj->uniqid}'/>";
    return true;
}

/**
 * 检查表单唯一标识码
 * @param $uniqid
 * @param $control 加密源源控制器的名称
 * @return bool
 */
function checkFormUniqid($uniqid,$control=null){
    $control=empty($control)?GROUP_NAME.'/'.MODULE_NAME.'/'.ACTION_NAME:$control;
    if($uniqid!=session($control.'_uniqid'))//解密传递的UNIQID之后，与session里的原数据作比较。
        return false;
    else
        return true;
}

/**
 * 检查URL唯一标识码
 * @param $uniqid
 * @return bool
 */
function checkUrlUniqid($uniqid,$control=null){
    return checkFormUniqid($uniqid,$control);
}
function encode($string){
    return authcode($string,'ENCODE',C('CSSF_STRING'));
}
function decode($string){
    return authcode($string,'DECODE',C('CSSF_STRING'));
}
/**
 *
 * @param string $string 原文或者密文
 * @param string $operation 操作(ENCODE | DECODE), 默认为 DECODE
 * @param string $key 密钥
 * @param int $expiry 密文有效期, 加密时候有效， 单位 秒，0 为永久有效
 * @return string 处理后的 原文或者 经过 base64_encode 处理后的密文
 * @example
 *  $a = authcode('abc', 'ENCODE', 'key');
 *  $b = authcode($a, 'DECODE', 'key'); // $b(abc)
 *
 *  $a = authcode('abc', 'ENCODE', 'key', 3600);
 *  $b = authcode('abc', 'DECODE', 'key'); // 在一个小时内，$b(abc)，否则 $b 为空
 */
function authcode($string,$operation='DECODE',$key='',$expiry=0){
    $ckey_length=4;
    $key=md5($key ? $key:"kalvin.cn");
    $keya=md5(substr($key,0,16));
    $keyb=md5(substr($key,16,16));
    $keyc=$ckey_length ? ($operation=='DECODE' ? substr($string,0,$ckey_length):substr(md5(microtime()),-$ckey_length)):'';
    $cryptkey=$keya.md5($keya.$keyc);
    $key_length=strlen($cryptkey);
    $string=$operation=='DECODE' ? base64_decode(substr($string,$ckey_length)):sprintf('%010d',$expiry ? $expiry+time():0).substr(md5($string.$keyb),0,16).$string;
    $string_length=strlen($string);
    $result='';
    $box=range(0,255);
    $rndkey=array();
    for($i=0;$i<=255;$i++){
        $rndkey[$i]=ord($cryptkey[$i%$key_length]);
    }
    for($j=$i=0;$i<256;$i++){
        $j=($j+$box[$i]+$rndkey[$i])%256;
        $tmp=$box[$i];
        $box[$i]=$box[$j];
        $box[$j]=$tmp;
    }
    for($a=$j=$i=0;$i<$string_length;$i++){
        $a=($a+1)%256;
        $j=($j+$box[$a])%256;
        $tmp=$box[$a];
        $box[$a]=$box[$j];
        $box[$j]=$tmp;
        $result.=chr(ord($string[$i]) ^ ($box[($box[$a]+$box[$j])%256]));
    }
    if($operation=='DECODE'){
        if((substr($result,0,10)==0||substr($result,0,10)-time()>0)&&substr($result,10,16)==substr(md5(substr($result,26).$keyb),0,16)){
            return substr($result,26);
        }else{
            return'';
        }
    }else{
        return $keyc.str_replace('=','',base64_encode($result));
    }
}

/**
 * 在一个日志文件后添加日志.
 * @param $filename string 文件名
 * @param $send_email bool 是否发送邮件
 * @param $message string 发送消息内容
 * @return bool
 */
function add_log($message,$send_email=false,$filename='fintal.error.log'){
    $route=C('MINE_LOG_PATH').$filename;
    echo $route;

    //如果不存在则创建日志文件目录！
    if(!is_dir(C('LOG_PATH')))
        mkdir(C('LOG_PATH'),0777,true);

    if(!$fp=fopen($route,'a+'))
        return false;

    $message=date('Y-m-d h:i:s',time())."\t".APP_PATH.C('APP_GROUP_PATH').'/'.GROUP_NAME.'/'.MODULE_NAME.'   '.$message."\n";
    //给管理员发送邮件。
    if($send_email)
        addEmailTimeQueue(C('ADMIN_EMAIL'),'尊敬的管理员','网站错误报告',$message,time());
    fwrite($fp,$message);
    fclose($fp);
    return true;
}
/**
 * 用$filter对数组进行递归过滤！
 * @param $arr
 * @param string $filter
 * @return array
 */
function changeHtmlSpecialChars($arr,$filter='htmlspecialchars'){
    if(!function_exists($filter))
        return $arr;
    $temp=array();
    foreach($arr as $key => $value){
        if(is_array($value))
            $temp[$key]=changeHtmlSpecialChars($value,$filter);
        else
            $temp[$key]=$filter($value);
    }
    return $temp;
}
/**
 * 由上传原文件名字推算出对应缩略图的名字！
 * @param $filename '文件名'
 * @param $prefix '前缀'
 * @return string '真实地址'
 */
function get_thumb_file($filename,$prefix='m_'){
    return dirname($filename).'/'.$prefix.basename($filename);
}

/**
 * 返回符合规定范围的字符，不够填充，多余取部分
 * @param $str
 * @param int $min
 * @param int $max
 * @param fill string
 * @return string
 */
function pillStr($str,$min=0,$max=20,$fill='*'){
    $len=mb_strlen($str,'utf-8');
    //填充名称不够的部分，截取多出的部分
    if($len>$max){
        return mb_substr($str,0,C('PLAN_MAX_NAME'),'utf-8');
    }elseif($len<$min) {
        $t=C('PLAN_MIN_NAME')-$len;
        for($i=0;$i<$t;$i++)
            $str .= $fill;
    }
    return $str;
}

/**
 * 通过判断字符串长度截取一个字符串，如果字符串过长，则用$fill_len个$fill替换后$fill_len个字符，并返回！
 * @param $str
 * @param int $max
 * @param int $fill_len
 * @param string $fill
 * @return string
 */
function submore($str,$max=8,$fill_len=3,$fill='.'){
    //计算占位 一个汉字两个长度，一个英语一个长度， strlen=3汉字+1英语 mb_strlen=1汉字+1英语 ，相加除2，即为2汉字+1英语，即为占位数
    $len=(mb_strlen($str,'utf-8')+strlen($str))/2;
    $max*=2;//这是占位数，1个汉字，算两个占位，所以允许的占位数*2
    //p($len.':'.$max);
    if($len<$max||$len<$fill_len)
        return $str;
    $str=mb_substr($str,0,mb_strlen($str,'utf-8')-$fill_len,'utf-8');
    for($i=0;$i<$fill_len;$i++){
        $str .=$fill;
    }
    return $str;
}
function Directory( $dir,$num=0644){

    return  is_dir ( $dir ) or Directory(dirname( $dir )) and  mkdir ( $dir , $num);

}

/**
 * 获取n天零点时间戳
 * @param $day
 * @return false|int
 */
function get_time($day){
    return strtotime(date('Y-m-d',time()+80400*$day));
}

/**
 * 展示时间，支持格式自定义，在$max天之类的将会转换形态.
 * @param $time
 * @param string $format
 * @param int $max
 * @return false|string
 */
function show_time($time,$format='Y-m-d H:i:s',$max=6){
    $distance=time()-$time;

    //分类返回时间
    if($distance>86400*$max)
        return date($format,$time);

    //获取天数
    $day=round($distance/86400,0);
    return $day>0?$day.'天前 '.date('H:i',$time):'今天 '.date('H:i',$time);
}

/**
 * 计算获得的经验！
 * @param $day
 * @return int
 */
function checkoutExp($day){
    $exp=5;
    //最多叠加5次！
    for($i=1;$i<=5&&$i<$day;$i++){
        $exp+=5;
    }
    return $exp;
}

/**
 * 屏蔽部分关键字，比如邮箱信息不能完全提供出来！
 */
function hiddenKey($str,$start=3,$length=5,$replace='*'){
    $result='';
    $len=mb_strlen($str,'utf-8');
    if($len<$start)
        $result=mb_substr($str,0,$len-3).$replace.$replace.$replace;
    else{
        $result=mb_substr($str,0,$start,'utf-8');
        for($i=0;$i<$length;$i++)
            $result.=$replace;
        $result.=mb_substr($str,$start+$length,null,'utf-8');
    }
    return $result;
}

/**
 * 获取用户的扩展信息！
 * @param $uid
 * @return mixed
 */
function get_user_info($uid){
    $data=M('user_info')->field(array('fri_rej_all'))->where("uid='%d'",$uid)->limit(1)->select();
    return $data[0];
}
?>