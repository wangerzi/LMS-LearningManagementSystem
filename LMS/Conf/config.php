<?php
return array(
	//'配置项'=>'配置值'
    'APP_GROUP_LIST' => 'Index,Admin',
    'DEFAULT_GROUP' => 'Index',
    'APP_GROUP_MODE' => 1,
    'APP_GROUP_PATH' => 'Models',
    'DB_HOST' => 'localhost',
    'DB_USER' => 'root',
    'DB_PWD' => 'root',
    'DB_NAME' => 'think_lms',
    'DB_PREFIX' => 'wq_',

    //开启页面追踪。
    'SHOW_PAGE_TRACE'   =>  false,

    //日志目录
    'MINE_LOG_PATH' =>APP_PATH.'log/',
    //发送邮件模板位置。
    'MAIL_TPL' => APP_PATH.'data/mail/',

    //加载其他的配置文件
    'LOAD_EXT_CONFIG' => 'verify,mail,register,web,plan,message,account,feedback',

    //修改默认控制器为Show
    'DEFAULT_MODULE' => 'Show',


    'CSSF_STRING' => 'liDI@90s!@',
    //默认过滤函数！
    'DEFAULT_FILTER' => 'htmlspecialchars',
    //某些控制器不用登陆即可访问！
    'NO_LOGIN_ROUTE' => array(
        //主页
        'Index/Show/index',

        //登录界面
        'Index/Login/index',
        'Index/Login/logout',
        //登录界面提交
        'Index/Login/loginHandle',
        //登录界面验证码
        'Index/Login/verify',
        //检查用户名的
        'Index/Login/usernameCheck',
        //检查密码
        'Index/Login/pwdCheck',
        //检查验证码的。
        'Index/Login/verifyCheck',

        //注册页面
        'Index/Register/index',
        //注册页面提交
        'Index/Register/register',
        //注册的验证码
        'Index/Register/verify',
        //检查验证码。
        'Index/Register/verifyCheck',
        //检测用户名
        'Index/Register/checkUsername',
        //检测邮箱
        'Index/Register/checkEmail',
        //激活页面
        'Index/Register/active',
    ),
    //某些分组需要管理员权限才能访问！
    'NEED_ADMIN_GROUP' => array(
        'Admin'
    ),
    //标识是管理员的session名字。
    'ADMIN_TAG' => 'admin_tag',

    //默认ajax返回类型
    'DEFAULT_AJAX_RETURN'=> 'JSON',

	//兼容模式，否则有是有图片显示不出来
	'URL_MODEL'	=>	3,

    //用户账户相关
    'USER_BASE_PATH'    =>  APP_PATH.'data/user/',
	
	//版本信息
	'WEB_VERSION'	=>	'1.2.0.20170419_release',
);
?>
