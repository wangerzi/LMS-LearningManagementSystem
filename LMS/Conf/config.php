<?php
return array(
	//'配置项'=>'配置值'
    'APP_GROUP_LIST' => 'Index,Admin',
    'DEFAULT_GROUP' => 'Index',
    'APP_GROUP_MODE' => 1,
    'APP_GROUP_PATH' => 'Models',
    'DB_HOST' => 'localhost',
    'DB_USER' => '',
    'DB_PWD' => '',
    'DB_NAME' => 'think_lms',
    'DB_PREFIX' => 'wq_',

    //日志目录
    'MINE_LOG_PATH' =>APP_PATH.'log/',
    //发送邮件模板位置。
    'MAIL_TPL' => APP_PATH.'data/mail/',

    //加载其他的配置文件
    'LOAD_EXT_CONFIG' => 'verify,mail,register,web,plan,message,account',


    'CSSF_STRING' => 'liDI@90s!@',
    //默认过滤函数！
    'DEFAULT_FILTER' => 'htmlspecialchars',
    //某些控制器不用登陆即可访问！
    'NO_LOGIN_ROUTE' => array(
        //登录界面
        'Index/Login/index',
        //登录界面提交
        'Index/Login/loginHandle',
        //登录界面验证码
        'Index/Login/verify',
        //注册页面
        'Index/Register/index',
        //注册页面提交
        'Index/Register/register',
        //注册的验证码
        'Index/Register/verify',
        //激活页面
        'Index/Register/active',
        //检查页面
        'Index/Register/check',
    ),
    //某些分组需要管理员权限才能访问！
    'NEED_ADMIN_GROUP' => array(
        'Admin'
    ),
    //标识是管理员的session名字。
    'ADMIN_TAG' => 'admin_tag',

    //默认ajax返回类型
    'DEFAULT_AJAX_RETURN'=> 'JSON',

    //找朋友部分允许搜索的最大值和最小值！
    'FRIEND_SEARCH_MAX' => 19,
    'FRIEND_SEARCH_MIN' => 2,
	
	'URL_MODEL'=>3,

    //用户账户相关
    'USER_BASE_PATH'    =>  APP_PATH.'data/user/',
	
	//版本信息
	'WEB_VERSION'	=>	'1.0.0.20161227_alpha',
);
?>