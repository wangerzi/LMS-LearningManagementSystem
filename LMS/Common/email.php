<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/14 0014
 * Time: 下午 8:08
 */
/**
 * 数据库版的加入发送队列
 * @param $email
 * @param $name
 * @param $title
 * @param $content
 * @param $time
 * @param null $for
 * @param int $repeat
 * @return mixed
 */
function addEmailTimeQueue($email,$name,$title,$content,$time,$for=null,$repeat=0){
    if(empty($email))
        return false;
    $db=M('email_time');
    $uid=session('uid');

    //做一个优化，减少数据库写入！
    if($time<time()&&$repeat==0) {
        return addEmailQueue($uid, $email, $name, $title, $content);
    }

    $data=array(
        'uid' => $uid,
        'email' => $email,
        'title' => $title,
        'name' => $name,
        'content' => $content,
        'send_time' => $time,
        'repeat'    =>  $repeat,
        'for'   =>  $for
    );
    return $db->add($data);
}
function dealEmailTimeQueue(){
    //判断是否本地连接，并且用户需要是指定的用户。
    if(session('uid')!=C('EMAIL_UID')||$_SERVER['REMOTE_ADDR']!=C('MAIL_CLIENT_IP')){
        _404('页面不存在！');
    }

    $db=M('email_time');

    $map=array(
        'send_time'  =>  array('lt',time())
    );
    $queue=$db->where($map)->limit(C('EMAIL_TIME_MAX'))->select();

    $arr=array(
        'add' => 0,
        'count' => count($queue),//总数！
    );

    foreach ($queue as $key => $val){
        $arr['add']++;
        //添加进入马上发送的email队列中。
        if(addEmailQueue($val['uid'],$val['email'],$val['name'],$val['title'],$val['content'])) {
            $arr['add']++;
            //如果是每天重复的话，就定时time()+86400后发。
            if ($val['repeat']) {
                $tmp = array(
                    'id' => $val['id'],
                    'send_tine' => time() + 86400,
                );
                $db->save($tmp);
            } else {
                $db->delete($val['id']);
            }
        }
    }
    return $arr;
}
function addEmailQueue($uid,$email,$name,$title,$content){
    if(empty($email))
        return false;
    $db=M('email');
    $arr=array(
        'email' => $email,
        'title' => $title,
        'name' => $name,
        'content' => $content,
        'error_time'=> 0,
        'error_uid' =>$uid,
    );
    return $db->add($arr);
}
function dealEmailQueue(){
    //判断是否本地连接，并且用户需要是指定的用户。
    if(session('uid')!=C('EMAIL_UID')||$_SERVER['REMOTE_ADDR']!=C('MAIL_CLIENT_IP')){
        _404('页面不存在！');
    }
    //记录发送多少条邮件。
    $arr=array(
        'send' => 0,
        'fail' => 0,
        'del' => 0,
    );

    $db=M('email');
    $queue=$db->limit(C('EMAIL_SEND_MAX'))->select();

    foreach ($queue as $key => $val){
        //错误次数达到十次，删除
        if($val['error_time']>10){
            $arr['del']++;

            load('@/message');
            $rid=$val['error_uid'];
            //退回邮件消息！
			$content='由于未知原因，您的邮件在北京时间'.date('Y-m-d H:i:s').'发送十次后失败，邮件被退回，邮件：'.$val['title'].'---'.$val['content'];
            if($rid>0) {
                sendMessage(C('EMAIL_BACK_FID'), $rid, 'email退回通知',$content,get_email($rid));
            }
            $db->delete($val['id']);
			add_log($content,false,'mail.error.log');
            continue;
        }
        if(sendEmail($val['email'],$val['name'],$val['title'],$val['content'])){
            $content=$val['email'].':'.$val['content'];
            add_log($content,false,'mail.send.log');
            $arr['send']++;
            $db->delete($val['id']);
        }else {
            $content=$val['email'].':'.$val['content'];
            add_log($content,false,'mail.error.log');
            //发送失败，刷新error_time。
            $arr['fail']++;
            $tmp=array();
            $tmp['id']=$val['id'];
            $tmp['error_time']=$val['error_time']+1;

            $db->save($tmp);
        }
        sleep(C('MAIL_SEND_SPACE'));
    }

    return $arr;//返回发送了多少条邮件。
}

/**
 * 添加入Email时间队列！
 * @param $email
 * @param $name
 * @param $title
 * @param $content
 * @param $time
 * @return bool
 */
function addEmailTimeQueue_old($email,$name,$title,$content,$time){
    //$queue=F('EmailTimeQueue','',APP_PATH.'data/');
    $queue=include APP_PATH.'data/EmailTimeQueue.php';
    if(!is_array($queue))
        $queue=array();
    $queue[]=array(
        'uid' => session('uid'),
        'email' => $email,
        'title' => $title,
        'name' => $name,
        'content' => $content,
        'error_time'=> 0,
        'send_time' => $time
    );
    //p($queue);
    if(F('EmailTimeQueue',$queue,APP_PATH.'data/')) {
        //flock($fp,LOCK_UN);//解锁.
        return true;
    }
    else {
        //flock($fp,LOCK_UN);
        return false;
    }
}

/**
 * 监视Email时间队列！
 */
function dealEmailTimeQueue_old(){
    //判断是否本地连接，并且用户需要是指定的用户。
    if(session('uid')!=C('EMAIL_UID')||$_SERVER['REMOTE_ADDR']!=C('MAIL_CLIENT_IP')){
        _404('页面不存在！');
    }
    //忽视客户端是否保持连接！
    ignore_user_abort(true);
    set_time_limit(0);
    $arr=array(
        'add' => 0,
        'count' => 0,//总数！
    );

    //$queue=F('EmailTimeQueue','',APP_PATH.'data');

    //这里不能用缓存了！
    //$queue=F('EmailTimeQueue','',APP_PATH.'data/');
    $queue=include APP_PATH.'data/EmailTimeQueue.php';
    if(!is_array($queue))
        $queue=array();

    foreach ($queue as $key => $val){
        if($val['send_time']<=time()){
            $arr['add']++;
            addEmailQueue($val['uid'],$val['email'],$val['name'],$val['title'],$val['content']);
            unset($queue[$key]);
        }
        $arr['count']++;
    }
    F('EmailTimeQueue',$queue,APP_PATH.'data/');
    return $arr;
}

/**
 * 添加入马上发送的队列！
 * @param $email
 * @param $name
 * @param $title
 * @param $content
 * @param $time
 * @return bool
 */
function addEmailQueue_old($uid,$email,$name,$title,$content){
    //$queue=F('EmailQueue','',APP_PATH.'data/');
    $queue=include APP_PATH.'data/EmailQueue.php';
    if(!is_array($queue))
        $queue=array();
    $queue[]=array(
        'uid' =>$uid,
        'email' => $email,
        'title' => $title,
        'name' => $name,
        'content' => $content,
        'error_time'=> 0,
    );
    if(F('EmailQueue',$queue,APP_PATH.'data/')) {
        return true;
    }
    else {
        return false;
    }
}

/**
 * 处理Email队列！
 */
function dealEmailQueue_old(){
    //判断是否本地连接，并且用户需要是指定的用户。
    if(session('uid')!=C('EMAIL_UID')||$_SERVER['REMOTE_ADDR']!=C('MAIL_CLIENT_IP')){
        _404('页面不存在！');
    }
    //记录发送多少条邮件。
    $arr=array(
        'send' => 0,
        'fail' => 0,
        'del' => 0,
    );

    //$queue=F('EmailQueue','',APP_PATH.'data');
    //$queue=F('EmailQueue','',APP_PATH.'data/');
    $queue=include APP_PATH.'data/EmailQueue.php';
    if(!is_array($queue))
        $queue=array();

    p($queue);
    foreach ($queue as $key => $val){
        if($val['error_time']>10){
            $arr['del']++;
            unset($queue[$key]);//删掉这条记录！
            continue;
        }
        if(sendEmail($val['email'],$val['name'],$val['title'],$val['content'])){
            $arr['send']++;
            unset($queue[$key]);//删掉这条记录！
        }else {
            $arr['fail']++;
            $val['error_time']=$val['error_time']+1;
            unset($queue[$key]);//先将其删掉。
            $queue[]=$val;//然后重新加入队列。
        }
        sleep(C('MAIL_SEND_SPACE'));
    }
    F('EmailQueue',$queue,APP_PATH.'data/');

    return $arr;//返回发送了多少条邮件。
}
function sendEmail($to,$name,$title,$content){
    import('Class.PHPMailer.PHPMailerAutoload',APP_PATH.C('APP_GROUP_PATH').'/'.GROUP_NAME,'.php');

    $mail=new PHPMailer();
    $mail->IsSMTP(); // 启用SMTP
    $mail->Host=C('MAIL_HOST'); //smtp服务器的名称（这里以QQ邮箱为例）
    $mail->SMTPAuth = C('MAIL_SMTPAUTH'); //启用smtp认证
    $mail->Username = C('MAIL_USERNAME'); //你的邮箱名
    $mail->Password = C('MAIL_PASSWORD') ; //邮箱密码
    $mail->From = C('MAIL_FROM'); //发件人地址（也就是你的邮箱地址）
    $mail->FromName = C('MAIL_FROMNAME'); //发件人姓名
    $mail->AddAddress($to,$name);
    $mail->WordWrap = 50; //设置每行字符长度
    $mail->IsHTML(C('MAIL_ISHTML')); // 是否HTML格式邮件
    $mail->CharSet=C('MAIL_CHARSET'); //设置邮件编码
    $mail->Subject =$title; //邮件主题
    $mail->Body = $content; //邮件内容
    $mail->AltBody = C('ALT_BODY'); //邮件正文不支持HTML的备用显示
    if($mail->Send())
        return true;
    else{
        return false;
    }
}
function get_file($addr,$time=3){
    if(!is_file($addr))
        return false;
    $fp=fopen($addr,'w+');
    $start=microtime(true);
    $canUse=false;
    while(microtime(true)-$start<$time){
        //echo microtime(true).':'.$start."<br/>";
        if(flock($fp,LOCK_NB)){
            $canUse=true;
            break;
        }
        usleep(100);
    }
    if($canUse)
        return $fp;
    else{
        fclose($fp);
        return false;
    }
}
?>