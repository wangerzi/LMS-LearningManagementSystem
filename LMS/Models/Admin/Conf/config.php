<?php
/**
 * Created by PhpStorm.
 * User: Administrator
 * Date: 2016/11/8 0008
 * Time: 下午 3:25
 */
return array(
    'TMPL_PARSE_STRING' => array(
        '__PUBLIC__' =>'/'. __ROOT__.APP_PATH.C('APP_GROUP_PATH')."/".GROUP_NAME."/Public",
    ),
    'LOAD_EXT_CONFIG' =>'system',

    //表单令牌
  /*  'TOKEN_ON'      =>    true,  // 是否开启令牌验证 默认关闭
    'TOKEN_NAME'    =>    '__hash__',    // 令牌验证的表单隐藏字段名称，默认为__hash__
    'TOKEN_TYPE'    =>    'md5',  //令牌哈希验证规则 默认为MD5
    'TOKEN_RESET'   =>    true,  //令牌验证出错后是否重置令牌 默认为true*/
);
?>