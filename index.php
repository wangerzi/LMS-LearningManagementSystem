<?php
//讲相对点设置到这里。
chdir(dirname(__FILE__));
// 检测PHP环境
if(version_compare(PHP_VERSION,'5.5.0','<'))  die('require PHP > 5.3.0 !');

// 开启调试模式 建议开发阶段开启 部署阶段注释或者设为false
define('APP_DEBUG',true);

//应用名称。
define("APP_NAME","测试博客");

//应用路径。
define("APP_PATH","./LMS/");

//runtime（解析）路径。
define("RUNTIME_PATH",APP_PATH."temp/");

// 引入ThinkPHP入口文件
require './ThinkPHP/ThinkPHP.php';
?>