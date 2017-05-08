<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2017/4/26
 * Time: 10:56
 */
return array(
    //缓存配置
    'HTML_CACHE_ON'     =>  true,
    'HTML_PATH'         =>  APP_PATH.'html',
    'HTML_CACHE_TIME'   =>  0,
    'HTML_READ_TYPE'    =>  1,  //0代表读取静态页面显示，1代表转向静态页面显示。
    'HTML_CACHE_RULES'  =>  array(
        //计划部分
        'Plan:index'    =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{type}_{p}'),
        'Plan:addPlan'  =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}',-1),//最好不要有缓存，不然容易造成添加失败！
        'Plan:detail'   =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{pcid}'),
        'Plan:log'      =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{pcid}'),
        'Plan:edit'     =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{pcid}'),
        'Plan:share'    =>  array('user/{:group}_{:module}_{:action}/{pid}'),
        'Plan:comment'  =>  array('user/{:group}_{:module}_{:action}/{pid}_{p}'),
        //好友部分
        'Friend:fate'   =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}',-1),
        'Friend:friendRequest'   =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{p}',60),
        'Friend:friendList'   =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{p}',60),
        'Friend:findHandle'   =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{p}',5),//为保证及时性
        //私信部分
        'Message:index' =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{onlyRead}_{p}',60),
        'Message:send'  =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{uid}',-1),
        //监督部分
        'Supervision:index'     =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{p}'),
        'Supervision:request'   =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{p}'),
        'Supervision:waiting'   =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{p}'),

        //后台管理
        'User:index'            =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{p}',60),
        'User:admin'            =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{p}',60),
        'Feedback:index'        =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}/{p}',60),

        //不用设置login/show-index之类的公共页面缓存，因为页面在登录和未登录时有细微差别，并且不影响
        //注意login/register等公开且有表单的页面，需要重新写入缓存
        'Login:index'   =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}',-1,''),
        'Login:forget'  =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}',-1,''),
        'Register:index'=>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}',-1,''),
        'Show:feedback' =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}',-1,''),
        '*'             =>  array('user/{$_SESSION.uid}/{:group}/{:module}/{:action}',0,'') //0表示永久缓存。
    ),
);