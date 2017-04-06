<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/18
 * Time: 2:47
 */
/**
 * 调用多项目邮件管理工具。
 * 如果要用函数的话，函数需要是公共区的函数，否则不会自动调用，或者配置mail的函数加载文件。
 * @param $email
 * @param $name
 * @param $title
 * @param $content
 * @param $time
 * @param null $for
 * @param int $repeat
 * @param bool $is_function
 * @return mixed
 */
function addEmailTimeQueue($email,$name,$title,$content,$time,$for=null,$repeat=0,$is_function=false){
    import('mail.MyEmail','./');
    $mail = new MyEmail();
    return $mail->addEmailTimeQueue($email,$name,$title,$content,$time,$for,$repeat,$is_function,null);
}
function delEmailTimeQueue($for,$limit=1){
    import('mail.MyEmail','./');
    $mail = new MyEmail();
    return $mail->delEmailTimeQueue($for,$limit);
}
?>