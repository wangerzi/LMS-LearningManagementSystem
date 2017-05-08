<?php

/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/1
 * Time: 18:42
 * Info: 阅读指南：
 *          建议在 ./demo/demo-test.php中先将 MyEmail::addEmailTimeQueue()函数的用法弄清，本系统即可正确使用了。
 *          ./Conf/db.php 和 ./Conf/mail.php的配置和 php.ini中pdo相关配置项的开启和 php_openssl 配置项的开启，是使用本系统的关键。
 *          本系统实现原理为：
 *              通过调用addEmailTimeQueue()函数，将定时邮件存入数据库 -- 开发者编程时调用。
 *              通过调用delEmailTimeQueue($for,$limit)管理定时邮件。
 *              通过dealEmailTimeQueue(),dealEmailQueue()来控制定时邮件->待发送区 和 待发送区->发出邮件。 -- time.php已经做好
 *              用户用 批处理或 Linux Shell 执行time.php脚本，实现定时邮件的控制（一直执行着，放在一旁即可）
 *          README.md中也蕴含有不少信息。
 */
class MyEmail
{
    private $loadedFile=array();//已经加载过的文件，避免重复加载。
    //构造函数，配置各种信息
    function __construct()
    {
        //数据库配置
        $dbConf=include "Conf/db.php";
        //邮件配置。
        $this->mailConf=include "Conf/mail.php";

        //创建PDO对象，使用时用prepare方法，将数据过滤放到MySql自己过滤。
        try {
            //预先转义 false，增加安全性，PHP版本最好在5.3.8以上，否则某些bug会危及数据库安全。
            $this->pdo = new PDO($dbConf['type'] . ':host=' . $dbConf['host'] . ';dbname=' . $dbConf['db_name'].';charset=utf8;', $dbConf['username'], $dbConf['pwd'],array(PDO::ATTR_PERSISTENT => true,PDO::ATTR_EMULATE_PREPARES => false));
        }catch (PDOException $e){
            die('Error:'.$e->getMessage());
        }
        //加载phpMailer，预先加载，避免相对定位点改变后加载失败。
        $this->includeExtFile($this->mailConf['MAIL_PHPMailer']);
    }
    /*
     * 加入发送队列
     * 如果有需要调用函数的话，函数需要在执行页面声明，否则不会自动调用。
     * @param $email                    需要送到的邮件
     * @param $name                     称呼
     * @param $title                    标题
     * @param $content                  内容/函数
     * @param $time                     发送时间的时间戳
     * @param null $for                 邮件的归属，一般是 项目代号+用户ID+用途 合并的字符串，通过这个字段可以删除未发送的 定时 邮件，就比如：LMS_1_notice 表示 LMS项目中uid=1的用户的提示邮件。
     * @param int $repeat               是否每天按照 发送时间 的时间点发送。
     * @param bool $is_function         邮件的内容是否是callback，如果 true 的话，将会以content为函数名，把邮件的所有数据以数组的形式传递。
     * @param callback $fail_callback   发送失败的回调函数名，如果不为空，则将该待发送所有字段以数组的形式传递，注：没有 is_function ,fail_callback等信息。
     * @return mixed                    添加发送成功或失败 true/false
     */
    function addEmailTimeQueue($email,$name,$title,$content,$time,$for=null,$repeat=0,$is_function=false,$fail_callback=null){
        if(empty($email) || empty($content))
            return false;

        if($time < time() && $repeat==0)
            return $this->addEmailQueue($email,$name,$title,$content,$for,$fail_callback);
        $pdo = $this->pdo;

        $data = array(
            ':email'    =>  $email,
            ':title'    =>  $title,
            ':name'     =>  $name,
            ':content'  =>  $content,
            ':time'     =>  $time,
            ':repeat'   =>  intval($repeat)%2,
            ':for'      =>  $for,
            ':is_function'=> intval($is_function)%2,
            ':fail_callback'=> $fail_callback,
        );

        //添加入数据库。
        $st=$pdo->prepare("INSERT INTO wq_email_time(`email`, `title`, `name`, `content`, `send_time`,`repeat`, `for`, `is_function`, `fail_callback`)
                    VALUES(:email,:title,:name,:content,:time,:repeat,:for,:is_function,:fail_callback);") or die('add'.print_r($pdo->errorInfo()));
        return $st->execute($data);
    }

    /**
     * 将邮件添加到待发送区（马上发送）
     * 参数含义与addEmailTimeQueue()相似，但出于安全性的考虑不能直接调用，仅供内部使用。
     * @param $email
     * @param $name
     * @param $title
     * @param $content
     * @param $for
     * @param $fail_callback
     * @return bool|int
     */
    private function addEmailQueue($email,$name,$title,$content,$for,$fail_callback){
        if(empty($email) || empty($content))
            return false;

        $pdo = $this->pdo;

        //添加入数据库。
        $st = $pdo->prepare("INSERT INTO wq_email(`email`, `title`, `name`, `content`, `for`, `fail_callback`,`error_time`)
                    VALUES(:email,:title,:name,:content,:for,:fail_callback,0)") or die(print_r($pdo->errorinfo()));
        $data = array(
            ':email'    =>  $email,
            ':name'     =>  $name,
            ':title'    =>  $title,
            ':content'  =>  $content,
            ':for'      =>  $for,
            ':fail_callback'=> $fail_callback,
        );
        return $st->execute($data);
    }
    /**
     * 解析并加载额外文件，通过字符传参的形式，仅限内部使用，已做避免重复加载的处理。
     * @param $str
     */
    private function includeExtFile($str){
        $arr = mb_split(',',$str);
        foreach($arr as $key => $value){
            if(file_exists($value) && !in_array($value,$this->loadedFile)) {
                $this->loadedFile[]=$value;
                include $value;
            }
        }
    }
    public function dealEmailQueue(){
        $pdo = $this->pdo;
        $info = $this->mailConf;
        $data = array(
            'success'   =>  0,//发送成功的。
            'error'     =>  0,//发送失败的。
            'remove'    =>  0,//移除的。
            );

        //加载额外文件。
        $this->includeExtFile($this->mailConf['MAIL_ERR_EXTRA']);

        //从数据库获取邮件。
        $emails = $pdo->query("SELECT * FROM wq_email LIMIT {$info['EMAIL_SEND_MAX']}");
        //发送邮件并进行错误处理。
        while(!!$arr = $emails->fetch()){
            //错误次数过多，移除，并执行错误处理。
            if($arr['error_time'] >= 10){
                $this->delEmailQueue($arr['id']);

                if(!empty($arr['fail_callback']) && function_exists($arr['fail_callback'])) {
                    $arr['time'] = time();
                    $arr['fail_callback']($arr);//调用callback，传入数据。
                }
                $data['remove']++;
                continue;
            }
            //发送成功或失败
            if($this->sendEmail($arr['email'],$arr['name'],$arr['title'],$arr['content']) == true) {
                $data['success']++;
                $this->delEmailQueue($arr['id']);
                sleep($info['MAIL_SEND_SPACE']);//停留配置的时间s.
            }
            else{
                $data['error']++;
                $pdo->exec("UPDATE wq_email SET error_time=error_time+1 WHERE id={$arr['id']} LIMIT 1;");
            }
        }
        return $data;
    }

    public function dealEmailTimeQueue(){
        $pdo = $this->pdo;
        $info = $this->mailConf;

        $count = $pdo->query("SELECT COUNT(id) AS num FROM wq_email_time;")->fetch();//获取总的定时邮件。
        $data = array(
            'success'   =>  0,//发送成功的。
            'count'     =>  $count['num'],//总邮件。
        );

        //加载额外的文件。
        $this->includeExtFile($this->mailConf['MAIL_CON_EXTRA']);

        //从数据库获取邮件。
        $emails = $pdo->query("SELECT * FROM wq_email_time WHERE send_time<".time()." LIMIT {$info['EMAIL_TIME_MAX']}");

        //添加入即时发送队列的SQL.
        $st = $pdo->prepare("INSERT INTO wq_email(`email`, `title`, `name`, `content`, `for`, `fail_callback`,`error_time`)
                    VALUES(:email,:title,:name,:content,:for,:fail_callback,0)");

        //删除定时邮件的SQL prepare
        $st_time = $pdo->prepare("DELETE FROM wq_email_time WHERE id=:id");
        //发送邮件并进行错误处理。
        while(!!$arr = $emails->fetch()){
            //检查$content是否是函数.
            if($arr['is_function']&&function_exists($arr['content'])){//这里主要是担心有用户写私信导致函数执行，repeat需要严格控制，最好限制只能是系统内部创建的。
                $arr['time'] = time();//执行时间。
                $content=$arr['content']($arr);
            }else{
                $content=$arr['content'];
            }
            //处理重复发邮件的情况
            if($arr['repeat']) {
                $newTime = ($arr['send_time']+86400);
                $newTime = $newTime < time()?$newTime+86400-(time()-$newTime)%86400:$newTime;//避免短时间内重复发送大量同一邮件。
                $pdo->exec("UPDATE wq_email_time SET send_time = '" . $newTime . "' WHERE id='{$arr['id']}' LIMIT 1;") or die($pdo->errorInfo());
            }
            else//否则删除。
                $st_time->execute(array(':id'=>$arr['id']));
            if(empty($content)) {
                continue;
            }
            $temp = array(
                ':email'    =>  $arr['email'],
                ':title'    =>  $arr['title'],
                ':name'     =>  $arr['name'],
                ':content'  =>  $content,
                ':for'      =>  $arr['for'],
                ':fail_callback'=> $arr['fail_callback'],
            );
            $st->execute($temp);
            $data['success']++;
        }
        return $data;
    }

    /**
     * 删除待发送区的邮件，仅限内部使用。
     * @param $id
     * @return int
     */
    private function delEmailQueue($id){
        $pdo = $this->pdo;
        $id = intval($id);
        return $pdo->exec("DELETE FROM wq_email WHERE id={$id}");
    }

    /**
     * 通过查找定时邮件队列中的for，删除邮件，可选最多删除个数。
     * @param $for      '查找的for
     * @param $limit    '最多删除个数。
     * @return mixed    '删除个数。
     */
    public function delEmailTimeQueue($for,$limit=null){
        $pdo = $this->pdo;
        $limit = intval($limit);
        if($limit > 0)
            $st = $pdo->prepare("DELETE FROM wq_email_time WHERE `for`=:for LIMIT {$limit}");
        else
            $st = $pdo->prepare("DELETE FROM wq_email_time WHERE `for`=:for");
        return $st->execute(array(':for'=>$for));
    }
    /**
     * 正宗发送邮件，使用前，需要引入phpMailer。
     * @param $to
     * @param $name
     * @param $title
     * @param $content
     * @return bool
     */
    private function sendEmail($to,$name,$title,$content){
        $conf = $this->mailConf;

        $mail=new PHPMailer();
        $mail->IsSMTP(); // 启用SMTP
        $mail->Host=$conf['MAIL_HOST']; //smtp服务器的名称（这里以QQ邮箱为例）
        $mail->SMTPAuth = $conf['MAIL_SMTPAUTH']; //启用smtp认证
        $mail->Username = $conf['MAIL_USERNAME']; //你的邮箱名
        $mail->Password = $conf['MAIL_PASSWORD'] ; //邮箱密码
        $mail->From = $conf['MAIL_FROM']; //发件人地址（也就是你的邮箱地址）
        $mail->FromName = $conf['MAIL_FROM_NAME']; //发件人姓名
        $mail->AddAddress($to,$name);
        $mail->WordWrap = 50; //设置每行字符长度
        $mail->IsHTML($conf['MAIL_IS_HTML']); // 是否HTML格式邮件
        $mail->CharSet=$conf['MAIL_CHARSET']; //设置邮件编码
        $mail->Subject =$title; //邮件主题
        $mail->Body = $content; //邮件内容
        $mail->AltBody = $conf['ALT_BODY']; //邮件正文不支持HTML的备用显示
        if($mail->Send())
            return true;
        else{
            echo $mail->ErrorInfo."<br>\n";//输出错误信息。
            return false;
        }
    }
}