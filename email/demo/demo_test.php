<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/3/2
 * Time: 19:15
 * Info: 通过这个样例，能更清晰的了解如何使用本产品。
 */

//首先，引入MyEmail.class.php
require '../MyEmail.class.php';

//然后，实例化对象
$email = new MyEmail();

//最后，发邮件或者管理邮件即可。
$email->addEmailTimeQueue('admin@wj2015.com.cn','对方称呼','邮件名字','内容',time()+20);//延迟20s发送
$email->addEmailTimeQueue('admin@wj2015.com.cn','对方称呼','邮件名字','内容',time(),'test_1_notice',true);//每天的这个时候，都向目标发送一封邮件， test_1_notice表示为test项目,uid=1的人发的notice邮件,通过此属性来管理邮件。
$email->delEmailTimeQueue('test_1_notice',1);//取消for=test_1_notice的定时邮件（限一封）的发送。

//此邮件发送 content_test($arr)返回的内容，content处填写函数名，系统传入含有邮件全部数据的数组，可配合for属性，达到发送动态邮件的效果,最后的 send_fail($arr) 同理，在邮件发送失败10次，舍弃邮件之后执行。
//content_test 函数所在目录需要在 Conf/mail.php中进行配置，预先加载，否则无法使用。
$email->addEmailTimeQueue('admin@wj2015.com.cn','对方称呼','邮件名字','content_test',time()+30,'test_1_dy',false,true,'send_fail');//添加动态邮件，30s后发送。

//最后结果是，添加一封20s后发送的定时邮件至定时队列。